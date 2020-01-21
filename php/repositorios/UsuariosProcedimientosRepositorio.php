<?php
namespace php\repositorios;

use php\interfaces\IUsuariosProcedimientosRepositorio;
use php\modelos\UsuarioProcedimiento;
use php\modelos\Resultado;

include '../interfaces/IUsuariosProcedimientosRepositorio.php';
include '../modelos/UsuarioProcedimiento.php';
require_once('RepositorioBase.php');
require_once('UsuariosRepositorio.php');
require_once("../clases/TipoUsuario.php");
require_once('../clases/Resultado.php');

class UsuariosProcedimientosRepositorio extends RepositorioBase implements IUsuariosProcedimientosRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = "SELECT UP.id, usuario_id,CONCAT(U.nombre,' ',U.apellido) usuarioNombre, procedimiento_id, P.nombre, IFNULL(DATE_FORMAT(UP.fecha_alta,'%d/%m/%Y'),'')fecha_alta, IFNULL(DATE_FORMAT(UP.fecha_cancelacion,'%d/%m/%Y'),'')fecha_cancelacion, UP.estatus, IFNULL(limitar_justificaciones,0),IFNULL(limite_justificaciones,0), codigo, U.apellido, U.empresa_id, U.sede_id, P.sede_id,IFNULL(DATE_FORMAT(UP.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'')fecha_modificacion 
                                FROM usuarios_procedimientos UP
                                    LEFT JOIN usuarios U ON U.id = UP.usuario_id
                                    LEFT JOIN sedes S ON S.id = U.sede_id
                                    LEFT JOIN empresas EM ON U.empresa_id = EM.id
                                    LEFT JOIN procedimientos P ON P.id = UP.procedimiento_id
                                    LEFT JOIN areas A ON A.id = U.area_id
                                    LEFT JOIN departamentos D ON D.id = U.departamento_id
                                    INNER JOIN tipos_usuario TU ON TU.id = U.tipo_usuario_id ";
    }

    public function insertar(UsuarioProcedimiento $modelo)
    {
        $resultado = $this->calcularId('id','usuarios_procedimientos');
        if($resultado->mensajeError=='')
        {
            $id = $resultado->valor;
            $consulta = "INSERT INTO usuarios_procedimientos(id, usuario_id, procedimiento_id, fecha_alta, estatus, limitar_justificaciones, limite_justificaciones)VALUES(?, ?, ?, NOW(), 1, ?, ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('iiiii', $id, $modelo->usuarioId, $modelo->procedimientoId,$modelo->limitarJustificaciones, $modelo->limiteJustificaciones))
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

    public function actualizar(UsuarioProcedimiento $modelo)
    {
        $resultado = new Resultado();
        
        $fechaCancelacion="NULL";
        if($modelo->estatus==0)
            $fechaCancelacion="NOW()";
        
        $consulta = "UPDATE usuarios_procedimientos
                     SET 
                        usuario_id = ?,
                        limitar_justificaciones =?,
                         limite_justificaciones = ?,
                        estatus = ?,
                         fecha_cancelacion = $fechaCancelacion, 
                         fecha_modificacion = NOW()
                     WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('iiiii',$modelo->usuarioId,$modelo->limitarJustificaciones,$modelo->limiteJustificaciones,$modelo->estatus,$modelo->id ))
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
                $where = $this->where($filtros);
        }
        $consulta = $this->consultaBase .$where ." order by date(UP.fecha_alta) desc";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $usuarioId, $usuarioNombre, $procedimientoId, $procedimientoNombre, $fechaAlta, $fechaCancelacion, $estatus,$limitarJustificaciones,$limiteJustificaciones,$codigo,$usuarioApellido,$empresaId, $sedeId,$procedimientoSedeId,$fechaModificacion ))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioId, $usuarioNombre, $procedimientoId, $procedimientoNombre, $fechaAlta, $fechaCancelacion, $estatus,$limitarJustificaciones,$limiteJustificaciones,$codigo,$usuarioApellido,$empresaId, $sedeId, $procedimientoSedeId,$fechaModificacion );
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
                WHERE usuario_procedimiento_id= ?
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
//                     	AND UP.id NOT IN(SELECT usuario_procedimiento_id FROM evidencias E WHERE MONTH(E.fecha_alta) = MONTH(NOW()) AND YEAR(E.fecha_alta) = YEAR(NOW()) ) " . $and . " " .
//                     	"ORDER BY U.nombre, P.nombre";

        $primerDiaMes = "$criteriosSeleccion->ano-$criteriosSeleccion->mes-1";
        $ultimoDiaMes = date("Y-m-t", strtotime($primerDiaMes));
        
//         $consulta = $this->consultaBase .
//         " WHERE UP.estatus = 1 
//                     	AND UP.id NOT IN(SELECT usuario_procedimiento_id FROM evidencias E WHERE MONTH(E.fecha_alta) = MONTH(NOW()) AND YEAR(E.fecha_alta) = YEAR(NOW()) ) " . $and . " " .
//                     	"ORDER BY  TU.orden,U.nombre, P.nombre";

        
        $primerDiaMes = "$criteriosSeleccion->ano-$criteriosSeleccion->mes-1";
        $ultimoDiaMes = date("Y-m-t", strtotime($primerDiaMes));
        
        $consulta = $this->consultaBase .
        " WHERE U.estatus = 1 AND ((UP.estatus = 1 AND UP.fecha_alta  <=  '$ultimoDiaMes') OR (UP.estatus = 0 AND MONTH(UP.fecha_alta)  <=  $criteriosSeleccion->mes AND  YEAR(UP.fecha_alta) <= $criteriosSeleccion->ano AND MONTH(UP.fecha_cancelacion) > $criteriosSeleccion->mes AND  YEAR(UP.fecha_cancelacion) >= $criteriosSeleccion->ano))
                AND UP.id NOT IN(SELECT usuario_procedimiento_id FROM evidencias E WHERE MONTH(E.fecha_alta) = $criteriosSeleccion->mes AND YEAR(E.fecha_alta) = $criteriosSeleccion->ano ) " . $and . " " .
                "ORDER BY TU.orden, U.nombre, P.nombre";
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $usuarioId, $usuarioNombre, $procedimientoId, $procedimientoNombre, $fechaAlta, $fechaCancelacion, $estatus,$limitarJustificaciones,$limiteJustificaciones,$codigo,$usuarioApellido,$empresaId, $usuarioSedeId ,$procedimientoSedeId,$fechaModificacion))
                    {
                        while($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioId, $usuarioNombre, $procedimientoId, $procedimientoNombre, $fechaAlta, $fechaCancelacion, $estatus,$limitarJustificaciones,$limiteJustificaciones,$codigo,$usuarioApellido,$empresaId, $usuarioSedeId,$procedimientoSedeId,$fechaModificacion );
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
    
    private function getFiltroEstructura($usuario)
    {
        
        $and = "";
        switch ($usuario->tipoUsuarioId)
        {
            case \TipoUsuario::USUARIO:
                $and =" AND U.id = $usuario->id";
                break;
            case \TipoUsuario::SUPERVISOR:
                $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
                $resultado = $usuariosRepositorio->consultarIdsUsuarios($usuario);
                if($resultado->correcto())
                {
                    $usuariosIds = implode(",", $resultado->valor);
                    $and =" AND U.id IN($usuariosIds)";
                }
                break;
            case \TipoUsuario::COORDINADOR:
                $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
                $resultado = $usuariosRepositorio->consultarIdsEmpresasCorporativo($usuario->empresaId);
                if($resultado->correcto())
                {
                    $empresasIds = implode(",", $resultado->valor);
                    $and =" AND EM.id IN($empresasIds)";
                }
                break;
            case \TipoUsuario::ADMINISTRADOR:
                break;
        }
        
        return $and;
    }
    public function consultarProcedimientosPendientes($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
      
        
        
        $filtros = array();
        
        $and = $this->getFiltroEstructura($usuario) ." ";
        
        
        $primerDiaMes = "$criteriosSeleccion->ano-$criteriosSeleccion->mes-1";
        $ultimoDiaMes = date("Y-m-t", strtotime($primerDiaMes));
        
        $consulta = $this->consultaBase .
        " WHERE UP.estatus = 1 AND U.estatus = 1 AND S.estatus = 1 AND EM.estatus = 1 AND A.estatus = 1 AND D.estatus = 1 AND ((UP.estatus = 1 AND UP.fecha_alta  <=  '$ultimoDiaMes') OR (UP.estatus = 0 AND MONTH(UP.fecha_alta)  <=  $criteriosSeleccion->mes AND  YEAR(UP.fecha_alta) <= $criteriosSeleccion->ano AND MONTH(UP.fecha_cancelacion) > $criteriosSeleccion->mes AND  YEAR(UP.fecha_cancelacion) >= $criteriosSeleccion->ano))
                AND UP.id NOT IN(SELECT usuario_procedimiento_id FROM evidencias E WHERE MONTH(E.fecha_alta) = $criteriosSeleccion->mes AND YEAR(E.fecha_alta) = $criteriosSeleccion->ano ) " . $and . " ";
        
        if($usuario->tipoUsuarioId==\TipoUsuario::ADMINISTRADOR)
            $consulta.="ORDER BY TU.orden, U.nombre, P.nombre";
        else
            $consulta.="ORDER BY FIELD(U.id,$usuario->id) DESC,U.nombre, P.nombre";
        
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $usuarioId, $usuarioNombre, $procedimientoId, $procedimientoNombre, $fechaAlta, $fechaCancelacion, $estatus,$limitarJustificaciones,$limiteJustificaciones,$codigo,$usuarioApellido,$empresaId, $usuarioSedeId,$procedimientoSedeId,$fechaModificacion ))
                    {
                        while($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioId, $usuarioNombre, $procedimientoId, $procedimientoNombre, $fechaAlta, $fechaCancelacion, $estatus,$limitarJustificaciones,$limiteJustificaciones,$codigo,$usuarioApellido,$empresaId, $usuarioSedeId,$procedimientoSedeId,$fechaModificacion );
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
                    if($sentencia->bind_result($id, $usuarioId, $usuarioNombre, $procedimientoId, $procedimientoNombre, $fechaAlta, $fechaCancelacion, $estatus,$limitarJustificaciones,$limiteJustificaciones,$codigo,$usuarioApellido,$empresaId, $usuarioSedeId,$procedimientoSedeId,$fechaModificacion ))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioId, $usuarioNombre, $procedimientoId, $procedimientoNombre, $fechaAlta, $fechaCancelacion, $estatus,$limitarJustificaciones,$limiteJustificaciones,$codigo,$usuarioApellido, $empresaId, $usuarioSedeId,$procedimientoSedeId ,$fechaModificacion);
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
        $consulta = "DELETE FROM usuarios_procedimientos WHERE id = ?";
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

    private function crearRegistro($id, $usuarioId, $usuarioNombre, $procedimientoId, $procedimientoNombre, $fechaAlta, $fechaCancelacion, $estatus,$limitarJustificaciones,$limiteJustificaciones, $codigo, $usuarioApellido, $empresaId, $usuarioSedeId, $procedimientoSedeId, $fechaModificacion )
    {
        $registro= (object) 
        [
            'id' => $id,
            'usuarioId' => $usuarioId,
            'usuarioNombre' => $usuarioNombre,
            'usuarioApellido' => $usuarioApellido,
            'procedimientoId' => $procedimientoId,
            'nombre' => $procedimientoNombre,
            'codigo' => $codigo,
            'fechaAlta' => $fechaAlta,
            'fechaCancelacion' => $fechaCancelacion,
            'estatus' => $estatus,
            'limitarJustificaciones' => $limitarJustificaciones,
            'limiteJustificaciones' => $limiteJustificaciones,
            'empresaId' => $empresaId,
            'usuarioSedeId' => $usuarioSedeId,
            'procedimientoSedeId' => $procedimientoSedeId,
            'fechaModificacion' => $fechaModificacion
        ];
        
        $registro->usuarioNombreCompleto = $registro->usuarioNombre ;
        $registro->fotoPerfil =  "../fotos/usuario". $registro->usuarioId .".jpg";
        if(file_exists($registro->fotoPerfil))
            $registro->fotoPerfil =  "php/fotos/usuario". $registro->usuarioId .".jpg";
        else
            $registro->fotoPerfil =  "php/fotos/default.jpg";
        return $registro;
    }
}
