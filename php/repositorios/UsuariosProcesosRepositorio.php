<?php
namespace php\repositorios;

use Mes;
use php\interfaces\IUsuariosProcesosRepositorio;
use php\modelos\UsuarioProceso;
use php\modelos\Resultado;
use php\clases\AdministradorCorreo;
use php\clases\Token;
use php\clases\Porcentaje;

include '../interfaces/IUsuariosProcesosRepositorio.php';
include '../modelos/UsuarioProceso.php';
require_once('RepositorioBase.php');
require_once('ProcesosRepositorio.php');
require_once('UsuariosRepositorio.php');
require_once("../clases/TipoUsuario.php");
require_once("../clases/Token.php");
require_once('../highcharts/highchartutils.php');
require_once('../clases/Resultado.php');
define('ROOTPATH', __DIR__);
class UsuariosProcesosRepositorio extends RepositorioBase implements IUsuariosProcesosRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = "SELECT UP.id, usuario_id usuarioId,U.nombre usuarioNombre, proceso_id, P.nombre, IFNULL(DATE_FORMAT(UP.fecha_alta,'%d/%m/%Y'),'')fecha_alta, IFNULL(DATE_FORMAT(UP.fecha_cancelacion,'%d/%m/%Y'),'')fecha_cancelacion, UP.estatus,codigo, U.apellido usuarioApellido, U.empresa_id empresaId, U.sede_id sedeId, P.sede_id procedimientoSedeId,IFNULL(DATE_FORMAT(UP.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'')fecha_modificacion,P.ruta_archivo, U.nombre_usuario nombreUsuario, U.tipo_usuario_id tipoUsuarioId,EM.mes_revision_procesos mesRevision, EM.administrador_procesos_id administradorId, UA.nombre administradorNombre, UA.apellido administradorApellido, UA.nombre_usuario administradorNombreUsuario 
                                FROM usuarios_procesos UP
                                    LEFT JOIN usuarios U ON U.id = UP.usuario_id
                                    LEFT JOIN sedes S ON S.id = U.sede_id
                                    LEFT JOIN empresas EM ON U.empresa_id = EM.id
                                    LEFT JOIN procesos P ON P.id = UP.proceso_id
                                    LEFT JOIN areas A ON A.id = U.area_id
                                    LEFT JOIN departamentos D ON D.id = U.departamento_id
                                    INNER JOIN tipos_usuario TU ON TU.id = U.tipo_usuario_id
                                    INNER JOIN usuarios UA ON UA.id = EM.administrador_procesos_id ";
        
        $this->consultaBasePendientesRevisados = "SELECT * FROM(SELECT UP.id usuario_proceso_id,U.empresa_id,EM.nombre, U.sede_id, S.nombre, usuario_id ,U.nombre,U.apellido,
                                                proceso_id, P.nombre, IFNULL(DATE_FORMAT(UP.fecha_alta,'%d/%m/%Y'),'')fecha_alta,IFNULL(DATE_FORMAT(UP.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'')fecha_modificacion,P.ruta_archivo,
                                                null  
                                                FROM usuarios_procesos UP
                                                	LEFT JOIN usuarios U ON U.id = UP.usuario_id
                                                	LEFT JOIN sedes S ON S.id = U.sede_id
                                                	LEFT JOIN empresas EM ON U.empresa_id = EM.id
                                                	LEFT JOIN procesos P ON P.id = UP.proceso_id
                                                	LEFT JOIN areas A ON A.id = U.area_id
                                                	LEFT JOIN departamentos D ON D.id = U.departamento_id
                                                	INNER JOIN tipos_usuario TU ON TU.id = U.tipo_usuario_id 
                                                UNION    
                                SELECT usuario_proceso_id, U.empresa_id, EM.nombre, U.sede_id, S.nombre, U.id, U.nombre, U.apellido , 
                                P.id, P.nombre, IFNULL(DATE_FORMAT(E.fecha_alta,'%d/%m/%Y %H:%i:%s'),'') as fecha,IFNULL(DATE_FORMAT(E.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'') as fecha, P.ruta_archivo,
                                E.estatus_revision_id
                                FROM procesos_revisados E
                                	INNER JOIN  usuarios_procesos UP ON UP.id = E.usuario_proceso_id
                                	INNER JOIN usuarios U ON U.id = UP.usuario_id
                                	LEFT JOIN sedes S ON S.id = U.sede_id
                                	LEFT JOIN empresas EM ON EM.id = S.empresa_id
                                	INNER JOIN procesos P ON P.id = UP.proceso_id
                                	LEFT JOIN usuarios V ON V.id = EM.administrador_id
                                	LEFT JOIN usuarios VL ON VL.id = E.validacion_usuario_id
                                    INNER JOIN estatus_validacion_procesos EV ON E.estatus_validacion_id = EV.id
                                    INNER JOIN estatus_revision ER ON ER.id = E.estatus_revision_id
                                )A";
    }

    public function insertar(UsuarioProceso $modelo)
    {
        $resultado = $this->calcularId('id','usuarios_procesos');
        if($resultado->mensajeError=='')
        {
            $id = $resultado->valor;
            $consulta = "INSERT INTO usuarios_procesos(id, usuario_id, proceso_id, fecha_alta, fecha_modificacion,estatus)VALUES(?, ?, ?, NOW(),NOW(), 1)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('iii', $id, $modelo->usuarioId, $modelo->procedimientoId))
                {
                    if(!$sentencia->execute())
                        $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
                }
                else
                    $resultado->mensajeError = 'Falló el enlace de parámetros';
            }
            else
                $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' .$this->conexion->error;
        }
        return $resultado;
    }

    public function actualizar(UsuarioProceso $modelo)
    {
        $resultado = new Resultado();
        
        $fechaCancelacion="NULL";
        if($modelo->estatus==0)
            $fechaCancelacion="NOW()";
        
        $consulta = "UPDATE usuarios_procesos
                     SET 
                        usuario_id = ?,
                        estatus = ?,
                         fecha_cancelacion = $fechaCancelacion, 
                         fecha_modificacion = NOW()
                     WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('iii',$modelo->usuarioId,$modelo->estatus,$modelo->id ))
            {
                if($sentencia->execute())
                {
                    $resultado->valor=true;
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        return $resultado;
    }

    public function consultar($criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        $filtros = array();
        $where='';
        if($criteriosSeleccion!=null)
        {
            if(isset($criteriosSeleccion->nombre))
                array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'U','campo'=>'nombre','valor'=>$criteriosSeleccion->nombre]);
            if(isset($criteriosSeleccion->empresaId))
            {
                if($criteriosSeleccion->empresaId!="" && $criteriosSeleccion->empresaId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'empresa_id','valor'=>$criteriosSeleccion->empresaId]);
            }
            if(isset($criteriosSeleccion->sedeId))
            {
                if($criteriosSeleccion->sedeId!="" && $criteriosSeleccion->sedeId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'sede_id','valor'=>$criteriosSeleccion->sedeId]);
            }
            if(isset($criteriosSeleccion->usuarioId))
            {
                if($criteriosSeleccion->usuarioId!="" && $criteriosSeleccion->usuarioId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'id','valor'=>$criteriosSeleccion->usuarioId]);
            }
            if(isset($criteriosSeleccion->estatus))
            {
                if($criteriosSeleccion->estatus!="" && $criteriosSeleccion->estatus!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'UP','campo'=>'estatus','valor'=>$criteriosSeleccion->estatus]);
            }
            if(isset($criteriosSeleccion->usuarioEstatus))
            {
                if($criteriosSeleccion->usuarioEstatus!="" && $criteriosSeleccion->usuarioEstatus!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'estatus','valor'=>$criteriosSeleccion->usuarioEstatus]);
            }
            if(isset($criteriosSeleccion->nombreUsuario))
            {
                if($criteriosSeleccion->nombreUsuario!="" && $criteriosSeleccion->nombreUsuario!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'nombreUsuario','valor'=>$criteriosSeleccion->nombreUsuario]);
            }
            $where = $this->where($filtros);
        }
        $consulta = $this->consultaBase .$where ." order by date(UP.fecha_alta) desc";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $usuarioId, $usuarioNombre, $procedimientoId, $procedimientoNombre, $fechaAlta, $fechaCancelacion, $estatus,$codigo,$usuarioApellido,$empresaId, $sedeId,$procedimientoSedeId,$fechaModificacion,$rutaArchivo,$nombreUsuario,$tipoUsuarioId,$mesRevision,$administradorId, $administradorNombre, $administradorApellido, $administradorNombreUsuario ))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioId, $usuarioNombre, $procedimientoId, $procedimientoNombre, $fechaAlta, $fechaCancelacion, $estatus,$codigo,$usuarioApellido,$empresaId, $sedeId, $procedimientoSedeId,$fechaModificacion,$rutaArchivo,$nombreUsuario,$tipoUsuarioId,$mesRevision,$administradorId, $administradorNombre, $administradorApellido, $administradorNombreUsuario );
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__.' Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = __FUNCTION__.' Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__.' Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = __FUNCTION__.' Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        return $resultado;
    }
    
    
    
    public function existeUsuarioProcedimientoEnMesActual($usuarioProcedimientoId)
    {
        $resultado = new Resultado();
        $resultado->valor = false;
        
        $consulta = "SELECT count(*) 
                FROM evidencias E 
                WHERE usuario_proceso_id= ?
                	AND MONTH(fecha_alta) = MONTH(NOW()) AND YEAR(fecha_alta) = YEAR(NOW())";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$usuarioProcedimientoId))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($count))
                    {
                        if($sentencia->fetch())
                        {
                           if($count>0)
                               $resultado->valor = true;
                        }
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__. ' Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = __FUNCTION__. ' Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__. ' Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = __FUNCTION__. ' Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        return $resultado;
    }
    
    private function getFiltrosUsuario($usuario,$criteriosSeleccion)
    {
        $filtros = array();
        switch ($usuario->tipoUsuarioId)
        {
            case \TipoUsuario::USUARIO:
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'id','valor'=>$usuario->id]);
                break;
            case \TipoUsuario::SUPERVISOR:
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'empresa_id','valor'=>$usuario->empresaId]);
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'sede_id','valor'=>$usuario->sedeId]);
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'area_id','valor'=>$usuario->areaId]);
                break;
            case \TipoUsuario::COORDINADOR:
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'empresa_id','valor'=>$usuario->empresaId]);
                break;
        }
       
        return $filtros;
    }
    
    public function consultarProcedimientosPendientesMesActual($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $criteriosSeleccion= (object)
        [
            'mes' => date('m'),
            'ano' =>  date('Y')
        ];
        
        $filtros  = $this->getFiltrosUsuario($usuario, $criteriosSeleccion);
        $and = $this->and($filtros);

//         $consulta = $this->consultaBase .
//                    " WHERE UP.estatus = 1 AND (U.tipo_usuario_id=4 OR U.tipo_usuario_id=5) 
//                     	AND UP.id NOT IN(SELECT usuario_proceso_id FROM evidencias E WHERE MONTH(E.fecha_alta) = MONTH(NOW()) AND YEAR(E.fecha_alta) = YEAR(NOW()) ) " . $and . " " .
//                     	"ORDER BY U.nombre, P.nombre";

        $primerDiaMes = "$criteriosSeleccion->ano-$criteriosSeleccion->mes-1";
        $ultimoDiaMes = date("Y-m-t", strtotime($primerDiaMes));
        
//         $consulta = $this->consultaBase .
//         " WHERE UP.estatus = 1 
//                     	AND UP.id NOT IN(SELECT usuario_proceso_id FROM evidencias E WHERE MONTH(E.fecha_alta) = MONTH(NOW()) AND YEAR(E.fecha_alta) = YEAR(NOW()) ) " . $and . " " .
//                     	"ORDER BY  TU.orden,U.nombre, P.nombre";

        
        $primerDiaMes = "$criteriosSeleccion->ano-$criteriosSeleccion->mes-1";
        $ultimoDiaMes = date("Y-m-t", strtotime($primerDiaMes));
        
        $consulta = $this->consultaBase .
        " WHERE UP.estatus = 1 
                AND U.estatus = 1 
                AND S.estatus = 1 
                AND EM.estatus = 1 
                AND A.estatus = 1 
                AND D.estatus = 1 
                AND U.permiso_saha = 1
                AND P.estatus = 1
                AND ((UP.estatus = 1 AND UP.fecha_alta  <=  '$ultimoDiaMes') OR (UP.estatus = 0 AND MONTH(UP.fecha_alta)  <=  $criteriosSeleccion->mes AND  YEAR(UP.fecha_alta) <= $criteriosSeleccion->ano AND MONTH(UP.fecha_cancelacion) > $criteriosSeleccion->mes AND  YEAR(UP.fecha_cancelacion) >= $criteriosSeleccion->ano))
                AND UP.id NOT IN(SELECT usuario_proceso_id FROM evidencias E WHERE MONTH(E.fecha_alta) = $criteriosSeleccion->mes AND YEAR(E.fecha_alta) = $criteriosSeleccion->ano ) " . $and . " " .
                "ORDER BY TU.orden, U.nombre, P.nombre";
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $usuarioId, $usuarioNombre, $procedimientoId, $procedimientoNombre, $fechaAlta, $fechaCancelacion, $estatus,$codigo,$usuarioApellido,$empresaId, $usuarioSedeId ,$procedimientoSedeId,$fechaModificacion,$rutaArchivo,$nombreUsuario,$tipoUsuarioId,$mesRevision))
                    {
                        while($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioId, $usuarioNombre, $procedimientoId, $procedimientoNombre, $fechaAlta, $fechaCancelacion, $estatus,$codigo,$usuarioApellido,$empresaId, $usuarioSedeId,$procedimientoSedeId,$fechaModificacion,$rutaArchivo,$nombreUsuario,$tipoUsuarioId,$mesRevision );
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
//     private function getFiltroEstructura($usuario)
//     {
        
//         $and = "";
//         switch ($usuario->tipoUsuarioId)
//         {
//             case \TipoUsuario::USUARIO:
//                 $and =" AND U.id = $usuario->id";
//                 break;
//             case \TipoUsuario::SUPERVISOR:
//                 $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
//                 $resultado = $usuariosRepositorio->consultarIdsUsuarios($usuario);
//                 if($resultado->correcto())
//                 {
//                     $usuariosIds = implode(",", $resultado->valor);
//                     $and =" AND U.id IN($usuariosIds)";
//                 }
//                 break;
//             case \TipoUsuario::COORDINADOR:
//                 $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
//                 $resultado = $usuariosRepositorio->consultarIdsEmpresasCorporativo($usuario->empresaId);
//                 if($resultado->correcto())
//                 {
//                     $empresasIds = implode(",", $resultado->valor);
//                     $and =" AND EM.id IN($empresasIds)";
//                 }
//                 break;
//             case \TipoUsuario::ADMINISTRADOR:
//                 break;
//         }
        
//         return $and;
//     }
    public function consultarProcesosPendientes($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
      
        
        
        $filtros = array();
        
        
        
        $procesosRepositorio = new ProcesosRepositorio($this->conexion);
        $filtros = $procesosRepositorio->getFiltrosN($usuario,$criteriosSeleccion,false);
        $and = $this->and($filtros);
        
        
        
       // $primerDiaAno = "$criteriosSeleccion->ano-1-1";
       // $ultimoDiaMes = date("Y-m-t", strtotime($primerDiaMes));
        //$ultimoDiaAno = "$criteriosSeleccion->ano-12-31";
        
        $consulta = $this->consultaBase .
        " WHERE UP.estatus = 1 
                AND U.estatus = 1 
                AND S.estatus = 1 
                AND EM.estatus = 1 
                AND A.estatus = 1 
                AND D.estatus = 1 
                AND U.permiso_saha = 1
                AND P.estatus = 1
                AND UP.estatus = 1 
                AND UP.id NOT IN(SELECT usuario_proceso_id FROM procesos_revisados E WHERE YEAR(E.fecha_alta) = $criteriosSeleccion->ano ) " . $and . " ";
        
//         if($usuario->tipoUsuarioId==\TipoUsuario::ADMINISTRADOR)
//             $consulta.=" ORDER BY TU.orden, U.nombre, P.nombre";
//         else
            $consulta.=" ORDER BY FIELD(U.id,$usuario->id) DESC,U.nombre, P.nombre";
            
            
        
       //var_dump($consulta);
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $usuarioId, $usuarioNombre, $procedimientoId, $procedimientoNombre, $fechaAlta, $fechaCancelacion, $estatus,$codigo,$usuarioApellido,$empresaId, $usuarioSedeId,$procedimientoSedeId,$fechaModificacion, $rutaArchivo,$nombreUsuario,$tipoUsuarioId,$mesRevision,$administradorId, $administradorNombre, $administradorApellido, $administradorNombreUsuario ))
                    {
                        while($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioId, $usuarioNombre, $procedimientoId, $procedimientoNombre, $fechaAlta, $fechaCancelacion, $estatus,$codigo,$usuarioApellido,$empresaId, $usuarioSedeId,$procedimientoSedeId,$fechaModificacion,$rutaArchivo,$nombreUsuario,$tipoUsuarioId,$mesRevision,$administradorId, $administradorNombre, $administradorApellido, $administradorNombreUsuario );
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
    public function consultarProcesosUsuarios($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        
        $filtros = array();
        
        
        
        $procesosRepositorio = new ProcesosRepositorio($this->conexion);
        $filtros = $procesosRepositorio->getFiltrosN($usuario,$criteriosSeleccion,false);
        $and = $this->and($filtros);
        
        
        $primerDiaAno = "$criteriosSeleccion->ano-1-1";
        // $ultimoDiaMes = date("Y-m-t", strtotime($primerDiaMes));
        $ultimoDiaAno = "$criteriosSeleccion->ano-12-31";
        
        $consulta = $this->consultaBase .
        " WHERE UP.estatus = 1
                AND U.estatus = 1
                AND S.estatus = 1
                AND EM.estatus = 1
                AND A.estatus = 1
                AND D.estatus = 1
                AND U.permiso_saha = 1
                AND P.estatus = 1
                AND UP.estatus = 1
                AND UP.id NOT IN(SELECT usuario_proceso_id FROM procesos_revisados E WHERE YEAR(E.fecha_alta) = $criteriosSeleccion->ano ) " . $and . " ";
        
       
            $consulta.=" ORDER BY FIELD(U.id,$usuario->id) DESC,U.nombre, P.nombre";
            
            
            
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($this->bind_param($sentencia, $filtros))
                {
                    if($sentencia->execute())
                    {
                        if($sentencia->bind_result($id, $usuarioId, $usuarioNombre, $procedimientoId, $procedimientoNombre, $fechaAlta, $fechaCancelacion, $estatus,$codigo,$usuarioApellido,$empresaId, $usuarioSedeId,$procedimientoSedeId,$fechaModificacion, $rutaArchivo ,$nombreUsuario,$tipoUsuarioId,$mesRevision,$administradorId, $administradorNombre, $administradorApellido, $administradorNombreUsuario))
                        {
                            while($sentencia->fetch())
                            {
                                $registro = $this->crearRegistro($id, $usuarioId, $usuarioNombre, $procedimientoId, $procedimientoNombre, $fechaAlta, $fechaCancelacion, $estatus,$codigo,$usuarioApellido,$empresaId, $usuarioSedeId,$procedimientoSedeId,$fechaModificacion,$rutaArchivo,$nombreUsuario,$tipoUsuarioId,$mesRevision,$administradorId, $administradorNombre, $administradorApellido, $administradorNombreUsuario);
                                array_push($registros,$registro);
                            }
                            $resultado->valor = $registros;
                        }
                        else
                            $resultado->mensajeError = 'Falló el enlace del resultado.';
                    }
                    else
                        $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
                }
                else
                    $resultado->mensajeError = 'Falló el enlace de parámetros';
            }
            else
                $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
                return $resultado;
    }
    
    /* public function consultarProcesosPendientes($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        
        
        $filtros = array();
        
        
        
        $procesosRepositorio = new ProcesosRepositorio($this->conexion);
        $filtros = $procesosRepositorio->getFiltrosN($usuario,$criteriosSeleccion,false);
        $and = $this->and($filtros);
        
        
        // $primerDiaAno = "$criteriosSeleccion->ano-1-1";
        // $ultimoDiaMes = date("Y-m-t", strtotime($primerDiaMes));
        //$ultimoDiaAno = "$criteriosSeleccion->ano-12-31";
        
        $consulta = $this->consultaBase .
        " WHERE UP.estatus = 1
                AND U.estatus = 1
                AND S.estatus = 1
                AND EM.estatus = 1
                AND A.estatus = 1
                AND D.estatus = 1
                AND U.permiso_saha = 1
                AND P.estatus = 1
                AND UP.estatus = 1
                AND UP.id NOT IN(SELECT usuario_proceso_id FROM procesos_revisados E WHERE YEAR(E.fecha_alta) = $criteriosSeleccion->ano ) " . $and . " ";
        
        //         if($usuario->tipoUsuarioId==\TipoUsuario::ADMINISTRADOR)
            //             $consulta.=" ORDER BY TU.orden, U.nombre, P.nombre";
            //         else
            $consulta.=" ORDER BY FIELD(U.id,$usuario->id) DESC,U.nombre, P.nombre";
            
            //var_dump($consulta);
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($this->bind_param($sentencia, $filtros))
                {
                    if($sentencia->execute())
                    {
                        if($sentencia->bind_result($id, $usuarioId, $usuarioNombre, $procedimientoId, $procedimientoNombre, $fechaAlta, $fechaCancelacion, $estatus,$codigo,$usuarioApellido,$empresaId, $usuarioSedeId,$procedimientoSedeId,$fechaModificacion, $rutaArchivo,$nombreUsuario,$tipoUsuarioId,$mesRevision ))
                        {
                            while($sentencia->fetch())
                            {
                                $registro = $this->crearRegistro($id, $usuarioId, $usuarioNombre, $procedimientoId, $procedimientoNombre, $fechaAlta, $fechaCancelacion, $estatus,$codigo,$usuarioApellido,$empresaId, $usuarioSedeId,$procedimientoSedeId,$fechaModificacion,$rutaArchivo,$nombreUsuario,$tipoUsuarioId,$mesRevision );
                                array_push($registros,$registro);
                            }
                            $resultado->valor = $registros;
                        }
                        else
                            $resultado->mensajeError = 'Falló el enlace del resultado.';
                    }
                    else
                        $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
                }
                else
                    $resultado->mensajeError = 'Falló el enlace de parámetros';
            }
            else
                $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
                return $resultado;
    }
     */
         
    private function getConsultarAvanceusuarios($usuario, $criteriosSeleccion, $andEmpresa)
    {
        $consulta= "SELECT U.id, U.nombre, U.apellido, U.nombre_usuario, U.empresa_id empresaId, EM.nombre empresaNombre, U.sede_id sedeId, S.nombre sedeNombre, U.departamento_id departamentoId, D.nombre departamentoNombre,
                    (SELECT count(*) numeroProcesos
                    FROM usuarios_procesos UP1
                        LEFT JOIN usuarios U1 ON U1.id = UP1.usuario_id
                        LEFT JOIN sedes S1 ON S1.id = U1.sede_id
                        LEFT JOIN empresas EM1 ON U1.empresa_id = EM1.id
                        LEFT JOIN procesos P1 ON P1.id = UP1.proceso_id
                        LEFT JOIN areas A1 ON A1.id = U1.area_id
                        LEFT JOIN departamentos D1 ON D1.id = U1.departamento_id
                        LEFT JOIN tipos_usuario TU1 ON TU1.id = U1.tipo_usuario_id
                    WHERE U1.id = U.id
                            AND UP1.estatus = 1
                            AND U1.estatus = 1
                            AND S1.estatus = 1
                            AND EM1.estatus = 1
                            AND A1.estatus = 1
                            AND D1.estatus = 1
                            AND U1.permiso_saha = 1
                            AND P1.estatus = 1
                            
                            ) pendientes,
                    (SELECT count(*)
                        FROM procesos_revisados PR1
                            INNER JOIN usuarios_procesos UP1 ON UP1.id = PR1.usuario_proceso_id
                                LEFT JOIN usuarios U1 ON U1.id = UP1.usuario_id
                                LEFT JOIN sedes S1 ON S1.id = U1.sede_id
                                LEFT JOIN empresas EM1 ON U1.empresa_id = EM1.id
                                LEFT JOIN procesos P1 ON P1.id = UP1.proceso_id
                                LEFT JOIN areas A1 ON A1.id = U1.area_id
                                LEFT JOIN departamentos D1 ON D1.id = U1.departamento_id
                            WHERE U1.id = U.id
                            AND UP1.estatus = 1
                            AND U1.estatus = 1
                            AND S1.estatus = 1
                            AND EM1.estatus = 1
                            AND A1.estatus = 1
                            AND D1.estatus = 1
                            AND U1.permiso_saha = 1
                            AND P1.estatus = 1
                    ) revisados
                    FROM usuarios_procesos UP
                        INNER JOIN usuarios U ON U.id = UP.usuario_id
                        LEFT JOIN empresas EM ON EM.id = U.empresa_id
                        LEFT JOIN sedes S ON S.id = U.sede_id
                        LEFT JOIN departamentos D ON D.id = U.departamento_id
                    WHERE U.estatus = 1
                            AND EM.estatus = 1
                            AND S.estatus = 1
                            AND D.estatus = 1
                            AND UP.estatus = 1
                            AND U.permiso_saha = 1
                            $andEmpresa
                    GROUP BY U.nombre, U.apellido
                    ORDER BY U.nombre, U.apellido";
                           // echo $consulta;
        return $consulta;
        
       
    }
            
    public function consultarAvanceUsuarios($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        
        $filtros = array();
        
        
        //$tipoUsuarioId = $usuario->tipoUsuarioId;
        //$usuario->tipoUsuarioId = \TipoUsuario::COORDINADOR;
        $procesosRepositorio = new ProcesosRepositorio($this->conexion);
        $filtros = $procesosRepositorio->getFiltrosN($usuario,$criteriosSeleccion,false);
        //$usuario->tipoUsuarioId = $tipoUsuarioId;
        $andEmpresa = $this->and($filtros);
        
        
        $primerDiaAno = "$criteriosSeleccion->ano-1-1";
        // $ultimoDiaMes = date("Y-m-t", strtotime($primerDiaMes));
        $ultimoDiaAno = "$criteriosSeleccion->ano-12-31";
        
        $consulta = $this->getConsultarAvanceUsuarios($usuario,$criteriosSeleccion,$andEmpresa);
                    
        //var_dump($consulta);
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre, $apellido, $nombreUsuario, $empresaId, $empresaNombre, $sedeId, $sedeNombre, $departamentoId, $departamentoNombre, $pendientes, $revisados))
                    {
                        while($sentencia->fetch())
                        {
                            $registro = (object)
                            [
                               "id" => $id, 
                                "nombre" => $nombre,
                                "apellido" => $apellido,
                                "nombreUsuario" => $nombreUsuario,
                                "pendientes" => $pendientes,
                                "revisados" => $revisados,
                                "empresaId" => $empresaId,
                                "empresaNombre" => $empresaNombre,
                                "sedeId" => $sedeId,
                                "sedeNombre" => $sedeNombre,
                                "departamentoId" => $departamentoId,
                                "departamentoNombre" => $departamentoNombre
                                            
                            ];
                            
                            $registro->nombreCompleto = $registro->nombre . " " .$registro->apellido;
                            $registro->nombreId =  $registro->nombreCompleto ." (".$registro->id.")";
                            $registro->fotoPerfil =  "../fotos/usuario". $registro->id .".jpg";
                            if(file_exists($registro->fotoPerfil))
                                $registro->fotoPerfil =  "php/fotos/usuario". $registro->id .".jpg";
                            else
                                $registro->fotoPerfil =  "php/fotos/default.jpg";
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
   
    
    public function consultarAvanceDepartamentos($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        
        $filtros = array();
        
        
        //$tipoUsuarioId = $usuario->tipoUsuarioId;
        //$usuario->tipoUsuarioId = \TipoUsuario::COORDINADOR;
        $procesosRepositorio = new ProcesosRepositorio($this->conexion);
        $filtros = $procesosRepositorio->getFiltrosN($usuario,$criteriosSeleccion,false);
        //$usuario->tipoUsuarioId = $tipoUsuarioId;
        $andEmpresa = $this->and($filtros);
        
        
        $primerDiaAno = "$criteriosSeleccion->ano-1-1";
        // $ultimoDiaMes = date("Y-m-t", strtotime($primerDiaMes));
        $ultimoDiaAno = "$criteriosSeleccion->ano-12-31";
        
        
        $consulta = "SELECT departamentoId, departamentoNombre, SUM(pendientes)pendientes, SUM(revisados )revisados
                    FROM (". $this->getConsultarAvanceUsuarios($usuario,$criteriosSeleccion,$andEmpresa) .
                    ")A
                    GROUP BY departamentoId, departamentoNombre
                    ORDER BY departamentoNombre ";
        
        //var_dump($consulta);
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre, $pendientes, $revisados))
                    {
                        while($sentencia->fetch())
                        {
                            $registro = (object)
                            [
                                "id" => $id,
                                "nombre" => $nombre,
                                "pendientes" => $pendientes,
                                "revisados" => $revisados
                                
                            ];
                            $registro->nombreId =  $registro->nombre." (".$registro->id.")";
                        array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
    public function consultarAvanceEmpresas($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        
        $filtros = array();
        
        
        //$tipoUsuarioId = $usuario->tipoUsuarioId;
        //$usuario->tipoUsuarioId = \TipoUsuario::COORDINADOR;
        $procesosRepositorio = new ProcesosRepositorio($this->conexion);
        $filtros = $procesosRepositorio->getFiltrosN($usuario,$criteriosSeleccion,false);
        //$usuario->tipoUsuarioId = $tipoUsuarioId;
        $andEmpresa = $this->and($filtros);
        
        
        $primerDiaAno = "$criteriosSeleccion->ano-1-1";
        // $ultimoDiaMes = date("Y-m-t", strtotime($primerDiaMes));
        $ultimoDiaAno = "$criteriosSeleccion->ano-12-31";
        
        
        $consulta = "SELECT empresaId, empresaNombre, SUM(pendientes)pendientes, SUM(revisados )revisados
                    FROM (". $this->getConsultarAvanceUsuarios($usuario,$criteriosSeleccion,$andEmpresa) .
                    ")A
                    GROUP BY empresaId, empresaNombre
                    ORDER BY empresaNombre ";
        
        //var_dump($consulta);
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre, $pendientes, $revisados))
                    {
                        while($sentencia->fetch())
                        {
                            $registro = (object)
                            [
                                "id" => $id,
                                "nombre" => $nombre,
                                "pendientes" => $pendientes,
                                "revisados" => $revisados
                                
                            ];
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
    
    
    
   
    

    public function consultarPorLlaves($llaves)
    {
        $resultado = new Resultado();
        $consulta = $this->consultaBase .
        ' WHERE UP.id  = ?';
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('i',$llaves->id))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $usuarioId, $usuarioNombre, $procedimientoId, $procedimientoNombre, $fechaAlta, $fechaCancelacion, $estatus,$codigo,$usuarioApellido,$empresaId, $usuarioSedeId,$procedimientoSedeId,$fechaModificacion, $rutaArchivo,$nombreUsuario,$tipoUsuarioId,$mesRevision,$administradorId, $administradorNombre, $administradorApellido, $administradorNombreUsuario ))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioId, $usuarioNombre, $procedimientoId, $procedimientoNombre, $fechaAlta, $fechaCancelacion, $estatus,$codigo,$usuarioApellido, $empresaId, $usuarioSedeId,$procedimientoSedeId ,$fechaModificacion, $rutaArchivo,$nombreUsuario,$tipoUsuarioId,$mesRevision,$administradorId, $administradorNombre, $administradorApellido, $administradorNombreUsuario);
                            $resultado->valor = $registro;
                        }
                        else
                            $resultado->mensajeError = "No se existe el procedimiento. Id = $llaves->id";
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__. ' Falló el enlace del resultado';
                }
                else
                    $resultado->mensajeError = __FUNCTION__. ' Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__. ' Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        return $resultado;
    }

    public function eliminar($llaves)
    {
        $resultado = new Resultado();
        $consulta = "DELETE FROM usuarios_procesos WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('i',$llaves->id))
            {
                if($sentencia->execute())
                {
                    $resultado->valor = $llaves->id;
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
                }
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        }
        return $resultado;
    }

    private function crearRegistro($id, $usuarioId, $usuarioNombre, $procedimientoId, $procedimientoNombre, $fechaAlta, $fechaCancelacion, $estatus, $codigo, $usuarioApellido, $empresaId, $usuarioSedeId, $procedimientoSedeId, $fechaModificacion,$rutaArchivo ,$nombreUsuario, $tipoUsuarioId,  $mesRevision,$administradorId, $administradorNombre, $administradorApellido, $administradorNombreUsuario)
    {
        $registro= (object) 
        [
            'id' => $id,
            'usuarioId' => $usuarioId,
            'usuarioNombre' => $usuarioNombre,
            'usuarioApellido' => $usuarioApellido,
            'procedimientoId' => $procedimientoId,
            'nombre' => $procedimientoNombre,
            'procedimientoNombre' => $procedimientoNombre,
            'codigo' => $codigo,
            'fechaAlta' => $fechaAlta,
            'fechaCancelacion' => $fechaCancelacion,
            'estatus' => $estatus,
            'empresaId' => $empresaId,
            'usuarioSedeId' => $usuarioSedeId,
            'procedimientoSedeId' => $procedimientoSedeId,
            'fechaModificacion' => $fechaModificacion,
            'tipo' => "pendiente",
            'rutaArchivo' => $rutaArchivo,
            'nombreUsuario' => $nombreUsuario,
            'tipoUsuarioId' => $tipoUsuarioId,
            'mesRevision' => $mesRevision,
            'administradorId' => $administradorId,
            'administradorNombre' => $administradorNombre,
            'administradorApellido' => $administradorApellido,
            'administradorNombreUsuario' => $administradorNombreUsuario
        ];
        
        $registro->usuarioNombreCompleto = $registro->usuarioNombre . " " .$registro->usuarioApellido;
        $registro->fotoPerfil =  "../fotos/usuario". $registro->usuarioId .".jpg";
        if(file_exists($registro->fotoPerfil))
            $registro->fotoPerfil =  "php/fotos/usuario". $registro->usuarioId .".jpg";
        else
            $registro->fotoPerfil =  "php/fotos/default.jpg";
        return $registro;
    }
    
    public function consultarUsuariosProcesosPendientes($nombreUsuario, $numeroUsuarios)
    {
        $resultado = new Resultado();
        $registros = array();
        $filtros = array();
        $where='';
        if(isset($nombreUsuario))
        {
            if($nombreUsuario!="" && $nombreUsuario!=null)
                array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'U','campo'=>'nombre_usuario','valor'=>$nombreUsuario]);
        }

        $and = $this->and($filtros);
        
        $ano = date("Y");
        
        
        $consulta =  "SELECT usuarioId, usuarioNombre, usuarioApellido, nombreUsuario, tipoUsuarioId, mesRevision, empresaId, sedeId, administradorId, administradorNombre, administradorApellido, administradorNombreUsuario
            FROM(
           $this->consultaBase 
         WHERE UP.estatus = 1
                AND U.estatus = 1
                AND S.estatus = 1
                AND EM.estatus = 1
                AND A.estatus = 1
                AND D.estatus = 1
                AND U.permiso_saha = 1
                AND P.estatus = 1
                AND UP.estatus = 1
                AND UP.id NOT IN(SELECT usuario_proceso_id FROM procesos_revisados E WHERE YEAR(E.fecha_alta) = $ano )  
                $and 
          ORDER BY U.nombre, U.apellido)A 
            GROUP BY usuarioId, usuarioNombre,usuarioApellido, nombreUsuario,tipoUsuarioId, mesRevision, empresaId, sedeId";
        
                
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $usuarioNombre, $usuarioApellido, $nombreUsuario, $tipoUsuarioId,$mesRevision,$empresaId, $sedeId, $administradorId, $administradorNombre, $administradorApellido, $administradorNombreUsuario ))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = (object)[
                                "id" => $id,
                                "nombre" => $usuarioNombre,
                                "apellido" => $usuarioApellido,
                                "nombreUsuario" => $nombreUsuario,
                                'tipoUsuarioId' => $tipoUsuarioId,
                                'mesRevision' => $mesRevision,
                                'empresaId' => $empresaId,
                                'sedeId' => $sedeId,
                                'administradorId'=> $administradorId,
                                'administradorNombre'=> $administradorNombre,
                                'administradorApellido'=> $administradorApellido,
                                'administradorNombreUsuario'=> $administradorNombreUsuario,
                            ];
                            $registro->usuarioNombreCompleto = $registro->nombre . " " . $registro->apellido;
                            $registro->fotoPerfil =  "../fotos/usuario". $registro->id .".jpg";
                            if(file_exists($registro->fotoPerfil))
                                $registro->fotoPerfil =  "php/fotos/usuario". $registro->id .".jpg";
                            else
                                $registro->fotoPerfil =  "php/fotos/default.jpg";
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__.' Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = __FUNCTION__.' Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__.' Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = __FUNCTION__.' Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        return $resultado;
    }
    
    public function enviarNotificacionRevision1($usuario,$procesos,$ano, $administrador)
    {
        $resultado = new Resultado();
        ini_set('max_execution_time', 0);
      
        $imprimirMensaje = true;
        
        $numeroProcesos = $this->getNumeroProcesos($procesos);
        $listaProcesos = $this->getListaProcesos($procesos);
        
        
        
        $ultimoDia = Mes::getUltimoDia(date("m"),date("Y"));
        $diaActual = date("d");
        $numeroDias = $ultimoDia - $diaActual;
        $dias = "";
        if($numeroDias==1)
            $dias = "Te queda $numeroDias día";
        else 
            $dias = "Te quedan $numeroDias días";
        
        $fechaLimite = Mes::getUltimoDiaMesActual();
        
        $mensaje= file_get_contents('../plantillas_correo/revision_procesos1.html');
        $mensaje=  str_replace("@usuarioNombre",$usuario->nombre,$mensaje);
        $mensaje=  str_replace("@numeroProcesos",$numeroProcesos,$mensaje);
        $mensaje=  str_replace("@listaProcesos",$listaProcesos,$mensaje);
        $mensaje=  str_replace("@ano",$ano,$mensaje);
        $mensaje=  str_replace("@dias",$dias,$mensaje);
        $mensaje=  str_replace("@fechaLimite",$fechaLimite,$mensaje);
        //$mensaje = str_replace("@contenido", $contenido, $mensaje);
        //$mensaje = str_replace("@boton", $boton, $mensaje);
        $usuarios = $this->getUsuariosCorreo($usuario,$administrador);
        $administradorCorreo = new AdministradorCorreo();
        $asunto="=?UTF-8?B?".base64_encode("¡Iniciamos la revisión anual de procesos!")."?=";
        $de="=?UTF-8?B?".base64_encode("SAHA - Revisión de procesos")."?=";
        $resultado =  $administradorCorreo->enviarCorreoUsuarios("procesos_pendientes_revision1",$usuarios,$asunto, $mensaje, "", $de,$imprimirMensaje);
        if($resultado->correcto())
        {
            
        }
        
        return $resultado;
    }
    
    private function getUsuariosCorreo($usuario, $administrador)
    {
        $usuarios = array();
        array_push($usuarios, $usuario);
        array_push($usuarios, $administrador);
        array_push($usuarios, (object)["nombreUsuario" => "eduardo@handel-sce.com"]);
        array_push($usuarios, (object)["nombreUsuario" => "noemi@handel-sce.com"]);
        //array_push($usuarios, (object)["nombreUsuario" => "contact@alanbazan.com.mx"]);
        return $usuarios;
    }
    
    public function enviarNotificacionRevision2($usuario,$procesos,$ano,$criteriosSeleccion,$administrador)
    {
        $resultado = new Resultado();
        ini_set('max_execution_time', 0);
        
        $imprimirMensaje = true;
      
        $mensaje= file_get_contents('../plantillas_correo/revision_procesos2.html');
        $mensaje=  str_replace("@usuarioNombre",$usuario->nombre,$mensaje);
        $mensaje=  str_replace("@ano",$ano,$mensaje);
        //$procesos = array();
        if(count($procesos)>0)
        {
            $numeroProcesos = $this->getNumeroProcesos($procesos);
            $listaProcesos = $this->getListaProcesos($procesos);
            $contenidoProcesos="<p style='font-size: 14px; line-height: 140%; text-align: justify;'>De acuerdo a nuestros registros, tienes $numeroProcesos para verificar, ya sea porque los tienes asignados directamente o porque en forma indirecta participas. Estos son los procesos:</p>
                                $listaProcesos
                                <p style='font-size: 14px; line-height: 140%; text-align: justify;'>&nbsp;</p>
                                <p style='font-size: 14px; line-height: 140%; text-align: left;'>Puedes consultar los documentos en la carpeta indicada utilizando tu cuenta de <a rel='noopener' href='http://www.cloud-handel.com' target='_blank'>www.cloud-handel.com</a>.</p>
                                <p style='font-size: 14px; line-height: 140%; text-align: left;'>&nbsp;</p>
                              ";
            
                            
            $mensaje=  str_replace("@contenidoProcesos",$contenidoProcesos,$mensaje);
        }
        else 
        {
            $contenidoProcesos=  "<p style='font-size: 14px; line-height: 140%; text-align: left;'>Nuestros registros indican que ya verificaste todos tus procedimientos. &iexcl;Gracias por el apoyo!</p>";
            $mensaje=  str_replace("@contenidoProcesos",$contenidoProcesos,$mensaje);
        }
       // else
          //  $mensaje= file_get_contents('../plantillas_correo/revision_procesos2.html');
        
       $tipoUsuarioId = $usuario->tipoUsuarioId;
       $usuario->tipoUsuarioId = \TipoUsuario::COORDINADOR;
       $resultado = $this->consultarAvanceUsuarios($usuario,$criteriosSeleccion);
       $usuario->tipoUsuarioId = $tipoUsuarioId;
       if($resultado->correcto())
       {
           $usuarios = $resultado->valor;
           $avance = "<ol>";
           for ($i = 0; $i < count($usuarios); $i++) {
               $usr = $usuarios[$i];
               $avance.="<li>(id=$usr->id) $usr->nombreCompleto (pendientes=$usr->pendientes) (enviados=$usr->revisados)</li>";
           }
           
           $avance .= "</ol>";
           
           
           //$url = $this->graficaAvanceUsuarios($usuarios);
           //$colores = [ '#00a1ff', '#60d836', '#f8ba00'];
           //$image = toColumnChart("Porcentaje de cumplimiento <br>($nombreMes)",'','Usuarios',$porcentajes,"nombreCompleto","porcentajeCumplimiento",$colores,false,100);
           $image = $this->graficaAvance($usuarios,"nombreCompleto","Avance de revisión de procedimientos");
           if($image!="")
           {
               $fecha = date_create();
               $nombreArchivo =  Token::getToken(30).date_timestamp_get($fecha).".jpeg";
                file_put_contents("../graficas_correos/".$nombreArchivo, file_get_contents($image));
                $url = getHandelAPI(). "/php/graficas_correos/$nombreArchivo";
                $mensaje=  str_replace("@avanceUsuarios",$url,$mensaje);
           }
           else
               $mensaje=  str_replace("@avanceUsuarios","",$mensaje);
           
           $mensaje=  str_replace("@avance","",$mensaje);
       }
       else 
           $mensaje=  str_replace("@avance",$resultado->mensajeError,$mensaje);
       
     
           $ultimoDia = Mes::getUltimoDia(date("m"),date("Y"));
           $diaActual = date("d");
           $numeroDias = $ultimoDia - $diaActual;
           $dias = "";
           if($numeroDias==1)
                $dias = "Te queda $numeroDias día";
           else
               $dias = "Te quedan $numeroDias días";
           
        $mensaje = str_replace("@dias", $dias, $mensaje);
        //$mensaje = str_replace("@contenido", $contenido, $mensaje);
        //$mensaje = str_replace("@boton", $boton, $mensaje);
        $usuarios = $this->getUsuariosCorreo($usuario,$administrador);
        $administradorCorreo = new AdministradorCorreo();
        $asunto="=?UTF-8?B?".base64_encode("Reporte de avance de revisión de procesos")."?=";
        $de="=?UTF-8?B?".base64_encode("SAHA - Revisión de procesos")."?=";
        $resultado =  $administradorCorreo->enviarCorreoUsuarios("procesos_pendientes_revision2",$usuarios,$asunto, $mensaje, "", $de,$imprimirMensaje);
        if($resultado->correcto())
        {
            
        }
        
        return $resultado;
    }
    
    function graficaAvance($rows, $xField, $titulo)
    {
        $showInLegend = true;
        
        $categories = array();
        $data = array();
        
        $data = array();
        
        $data1 = array();
        $data2 = array();
        $data3 = array();
        
        //$fecha = new DateTime();
       // $mesActual = (int)$fecha->format("m");
        
        for ($i = 0; $i < count($rows); $i++)
        {
            $row = $rows[$i];
            
            //         $newRow= (object) [
            //             'name' =>  $row->$xField,
            //             'y' => (float)$row->cumplidas,
            //             'color' => "#00a1ff"
            
                //         ];
            
            $newRow1= (object) [
                'name' =>  $row->$xField,
                'y' => (float)$row->revisados,
                // 'color' => "#3c8dbc"
            ];
            
            $newRow2= (object) [
                'name' =>  $row->$xField,
                'y' => (float)$row->pendientes,
                //'color' => "#f39c12"
            ];
            
            //             $newRow3= (object) [
            //                 'name' =>  $row->$xField,
            //                 'y' => (float)$row->proceso,
            //                 'color' => "#919191"
            //             ];
            
            array_push($categories, $row->$xField);
            array_push($data1, $newRow1);
            array_push($data2, $newRow2);
            // array_push($data3, $newRow3);
        }
        
        $yAxis = (object) [ 'title' => (object) [ 'text'=> ""]];
        
//         $yAxis->min= 0;
//         $yAxis->max= 100;
//         $yAxis->tickInterval= 10;
        
        
        
        $rotacion = 0;
        if(count($rows)>=10)
            $rotacion = -90;
            
            
            
            $highchart = (object)
            [
                'chart' => (object) [ 'type' => "column"],
                'title' => (object) [ 'text'=> $titulo],
                'credits' => (object) ['enabled' => false],
                'xAxis' => (object) [ 'categories' => $categories],
                'plotOptions' => (object)
                [
                    'column'=> (object)[
                       // 'stacking' => 'normal',
                        'dataLabels'=>(object)
                        [
                            'enabled'=>true,
                            //                         'crop'=>false,
                        //                         'overflow' =>'none',
                        //                         "inside"=> false,
                            'color'=> 'black',
                            'style'=> (object)
                            [
                                'fontSize' => 10,
                                'textOutline' => '0px'
                            ],
                            //                         'rotation' => $rotacion,
            //                         'format'=>"{point.y:.1f} %",
                            // 'format'=>"{point.y} %",
                            'verticalAlign' => 'bottom'
                            
                        ]
                    ]
                ],
                'yAxis' => $yAxis,
                'series' => array(
                    (object) ['name' => "Asignados", 'data' => $data2,  'showInLegend' => $showInLegend, "color"=>"#4575c3"],
                    (object) ['name' => "Revisados", 'data' => $data1,  'showInLegend' => $showInLegend, "color"=>"#fb7535"],
                    //(object) ['name' => "En proceso de validación", 'data' => $data3,  'showInLegend' => $showInLegend, "color"=>"#919191"]
                )
            ];
            
            $chartURL = getHightchartsURL($highchart);
            return $chartURL;
            
            //  return 'ok';
            
    }
    
    private function getNumeroProcesos($procesos)
    {
        $numeroProcesos="";
        if(count($procesos)==1)
            $numeroProcesos = " 1 proceso asignado ";
        else
            $numeroProcesos =  count($procesos) ." procesos asignados ";
        return $numeroProcesos;
    }
    
    private function getListaProcesos($procesos)
    {
        $listaProcesos = "<ol>";
        for ($i = 0; $i < count($procesos); $i++) {
            $proceso = $procesos[$i];
            $listaProcesos.="<li style='font-size: 14px; text-align: left;'>$proceso->nombre
            - Localizado en carpeta: $proceso->rutaArchivo</li>";
        }
        
        $listaProcesos.="</ol>";
        return $listaProcesos;
    }
    
    public function enviarNotificacionRevision3($usuario,$procesos,$ano,$criteriosSeleccion,$administrador)
    {
        $resultado = new Resultado();
        ini_set('max_execution_time', 0);
        
        $imprimirMensaje = true;
        
        $mensaje= file_get_contents('../plantillas_correo/revision_procesos3.html');
        $mensaje=  str_replace("@usuarioNombre",$usuario->nombre,$mensaje);
        $mensaje=  str_replace("@ano",$ano,$mensaje);
        //$procesos = array();
        if(count($procesos)>0)
        {
            $numeroProcesos = $this->getNumeroProcesos($procesos);
            $listaProcesos = $this->getListaProcesos($procesos);
            $contenidoProcesos="<p style='font-size: 14px; line-height: 140%; text-align: justify;'>De acuerdo a nuestros registros, tienes $numeroProcesos para verificar, ya sea porque los tienes asignados directamente o porque en forma indirecta participas. Estos son los procesos:</p>
                                $listaProcesos
                                <p style='font-size: 14px; line-height: 140%; text-align: justify;'>&nbsp;</p>
                                <p style='font-size: 14px; line-height: 140%; text-align: left;'>Puedes consultar los documentos en la carpeta indicada utilizando tu cuenta de <a rel='noopener' href='http://www.cloud-handel.com' target='_blank'>www.cloud-handel.com</a>.</p>
                                <p style='font-size: 14px; line-height: 140%; text-align: left;'>&nbsp;</p>
                              ";
                                
                                
                                $mensaje=  str_replace("@contenidoProcesos",$contenidoProcesos,$mensaje);
        }
        else
        {
            $contenidoProcesos=  "<p style='font-size: 14px; line-height: 140%; text-align: left;'>Nuestros registros indican que ya verificaste todos tus procedimientos. &iexcl;Gracias por el apoyo!</p>";
            $mensaje=  str_replace("@contenidoProcesos",$contenidoProcesos,$mensaje);
        }
        // else
        //  $mensaje= file_get_contents('../plantillas_correo/revision_procesos2.html');
        $tipoUsuarioId = $usuario->tipoUsuarioId;
        $usuario->tipoUsuarioId = \TipoUsuario::COORDINADOR;
        $resultado = $this->consultarAvanceUsuarios($usuario,$criteriosSeleccion);
        $usuario->tipoUsuarioId = $tipoUsuarioId;
        if($resultado->correcto())
        {
            $usuarios = $resultado->valor;
            $avance = "<ol>";
            for ($i = 0; $i < count($usuarios); $i++) {
                $usr = $usuarios[$i];
                $avance.="<li>(id=$usr->id) $usr->nombreCompleto (pendientes=$usr->pendientes) (enviados=$usr->revisados)</li>";
            }
            
            $avance .= "</ol>";
            
            
            //$url = $this->graficaAvanceUsuarios($usuarios);
            //$colores = [ '#00a1ff', '#60d836', '#f8ba00'];
            //$image = toColumnChart("Porcentaje de cumplimiento <br>($nombreMes)",'','Usuarios',$porcentajes,"nombreCompleto","porcentajeCumplimiento",$colores,false,100);
            $image = $this->graficaAvance($usuarios,"nombreCompleto","Avance de revisión de procedimientos");
            if($image!="")
            {
                $fecha = date_create();
                $nombreArchivo =  Token::getToken(30).date_timestamp_get($fecha).".jpeg";
                file_put_contents("../graficas_correos/".$nombreArchivo, file_get_contents($image));
                $url = getHandelAPI(). "/php/graficas_correos/$nombreArchivo";
                $mensaje=  str_replace("@avanceUsuarios",$url,$mensaje);
            }
            else
                $mensaje=  str_replace("@avanceUsuarios","",$mensaje);
                
            $resultado = $this->consultarAvanceDepartamentos($usuario,$criteriosSeleccion);
            if($resultado->correcto())
            {
                $departamentos = $resultado->valor;
                $porcentaje = $this->getPocentajeAvance($departamentos);
                $mensaje=  str_replace("@porcentaje",$porcentaje,$mensaje);
                
                $image = $this->graficaAvance($departamentos,"nombre","Avance de revisión de procedimientos");
                if($image!="")
                {
                    $fecha = date_create();
                    $nombreArchivo =  Token::getToken(30).date_timestamp_get($fecha).".jpeg";
                    file_put_contents("../graficas_correos/".$nombreArchivo, file_get_contents($image));
                    $url = getHandelAPI(). "/php/graficas_correos/$nombreArchivo";
                    $mensaje=  str_replace("@avanceDepartamentos",$url,$mensaje);
                }
                else
                    $mensaje=  str_replace("@avanceDepartamentos","",$mensaje);
                
                    
            }
            
        }
        else
            $mensaje=  str_replace("@avance",$resultado->mensajeError,$mensaje);
            
            
            
            $ultimoDia = Mes::getUltimoDia(date("m"),date("Y"));
            $diaActual = date("d");
            $siguienteAno = date("Y") + 1;
            $diasAno = 365;
            if($siguienteAno % 4 == 0)
                $diasAno = 366;
            $numeroDias =  $diasAno - $diaActual;
            $dias = "$numeroDias días";
                    
            $mensaje = str_replace("@dias", $dias, $mensaje);
            
            
            //$mensaje = str_replace("@contenido", $contenido, $mensaje);
            //$mensaje = str_replace("@boton", $boton, $mensaje);
            $usuarios = $this->getUsuariosCorreo($usuario,$administrador);
            $administradorCorreo = new AdministradorCorreo();
            $asunto="=?UTF-8?B?".base64_encode("¿Cómo concluyó la revisión de procesos?")."?=";
            $de="=?UTF-8?B?".base64_encode("SAHA - Revisión de procesos")."?=";
            $resultado =  $administradorCorreo->enviarCorreoUsuarios("procesos_pendientes_revision2",$usuarios,$asunto, $mensaje, "", $de,$imprimirMensaje);
            if($resultado->correcto())
            {
                
            }
            
            return $resultado;
    }
    
    private function getPocentajeAvance($departamentos)
    {
        $pendientes = 0;
        $revisados = 0;
        
        for ($i = 0; $i < count($departamentos); $i++) {
            $departamento = $departamentos[$i];
            $pendientes+=$departamento->pendientes;
            $revisados+= $departamento->revisados;
        }
        
        $porcentaje = $revisados / $pendientes * 100;
        
        $porcentaje = Porcentaje::formatear($porcentaje,2);
        
        return $porcentaje;
    }
}
