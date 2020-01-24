<?php
namespace php\repositorios;

use php\interfaces\IEvidenciasRepositorio;
use php\modelos\Evidencia;
use php\modelos\Resultado;

require_once('../interfaces/IEvidenciasRepositorio.php');
require_once('../modelos/Evidencia.php');
require_once('RepositorioBase.php');
require_once('UsuariosRepositorio.php');
require_once("../clases/TipoUsuario.php");
require_once('../clases/Resultado.php');

class EvidenciasRepositorio extends RepositorioBase implements IEvidenciasRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = "SELECT E.id, usuario_procedimiento_id, realizo_actividad, justificacion_id, comentarios, IFNULL(DATE_FORMAT(E.fecha_alta,'%d/%m/%Y %H:%i:%s'),'') as fecha, P.nombre, nombre_archivo, P.codigo, J.nombre,
                                (SELECT count(C.id) FROM evidencias_comentarios C WHERE C.evidencia_id = E.id) numeroComentarios, U.nombre, U.apellido, S.id, S.nombre, EM.id, EM.nombre, U.id, validada, comentarios_validacion, EM.administrador_id, V.nombre administradorNombre, V.apellido administradorApellido,E.validacion_usuario_id validadorId, VL.nombre validadorNombre, VL.apellido validadorApellido   
                               FROM evidencias E
                            		INNER JOIN  usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
                                    INNER JOIN usuarios U ON U.id = UP.usuario_id
                                    LEFT JOIN sedes S ON S.id = U.sede_id
                                    LEFT JOIN empresas EM ON EM.id = S.empresa_id
                            		INNER JOIN procedimientos P ON P.id = UP.procedimiento_id
                                    LEFT JOIN justificaciones J ON J.id = E.justificacion_id
                                    LEFT JOIN usuarios V ON V.id = EM.administrador_id
                                    LEFT JOIN usuarios VL ON VL.id = E.validacion_usuario_id
                                ";
    }

    public function insertar(Evidencia $modelo,$nombreArchivoSubido)
    {
        if($modelo->justificacionId=="")
            $modelo->justificacionId=null;
        $resultado = $this->calcularId('id','evidencias');
        if($resultado->mensajeError=='')
        {
            $id = $resultado->valor;
            $consulta = "INSERT INTO evidencias(id, usuario_procedimiento_id, realizo_actividad, justificacion_id, comentarios, fecha_alta, fecha_modificacion, nombre_archivo)VALUES(?, ?, ?, ?, ?, NOW(),NOW(),?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('ssiiss', $id, $modelo->usuarioProcedimientoId, $modelo->realizoActividad, $modelo->justificacionId, $modelo->comentarios,$nombreArchivoSubido))
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
    
    public function numeroEvidenciasJustificadasAnoActual($usuarioProcedimientoId)
    {
        $resultado = new Resultado();
        $resultado->valor = false;
        
        $consulta = "SELECT count(id) id
                        FROM evidencias E 
                        WHERE justificacion_id IS NOT NULL AND usuario_procedimiento_id = ? 
                            AND YEAR(E.fecha_alta) = YEAR(NOW())";
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
                            $resultado->valor =$count;
                        }
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
    
//     public function numeroEvidenciasJustificadasMesActual($usuario,$criteriosSeleccion)
//     {
//         $resultado = new Resultado();
//         $resultado->valor = 0;
//         $filtros = $this->getFiltrosUsuario($usuario, $criteriosSeleccion,false);
        
        
//         $consulta = "SELECT count(*) numero
//                     FROM evidencias E
//                         INNER JOIN usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
//                         INNER JOIN usuarios U ON U.id = UP.usuario_id
//                     WHERE U.estatus = 1 AND MONTH(E.fecha_alta) = $criteriosSeleccion->mes AND YEAR(E.fecha_alta) = $criteriosSeleccion->ano AND justificacion_id IS NOT NULL 
//                     ";
//         $consulta.=  $this->and($filtros);
        
//         if($sentencia = $this->conexion->prepare($consulta))
//         {
//             if($this->bind_param($sentencia, $filtros))
//             {
//                 if($sentencia->execute())
//                 {
//                     if($sentencia->bind_result($count))
//                     {
//                         if($sentencia->fetch())
//                         {
//                             $resultado->valor =$count;
//                         }
//                     }
//                     else
//                         $resultado->mensajeError = 'Falló el enlace del resultado.';
//                 }
//                 else
//                     $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
//             }
//             else
//                 $resultado->mensajeError = 'Falló el enlace de parámetros';
//         }
//         else
//             $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
//         return $resultado;
//     }
    
//     public function numeroEvidenciasEnviadasMesActual($usuario,$criteriosSeleccion)
//     {
//         $resultado = new Resultado();
//         $resultado->valor = 0;
//         $filtros = $this->getFiltrosUsuario($usuario, $criteriosSeleccion,false);
//         $consulta = "SELECT count(*) numero
//                     FROM evidencias E
//                         INNER JOIN usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
//                         INNER JOIN usuarios U ON U.id = UP.usuario_id
//                     WHERE U.estatus = 1 AND MONTH(E.fecha_alta) = MONTH(NOW()) AND YEAR(E.fecha_alta) = YEAR(NOW()) AND justificacion_id IS NULL
//                     ";
//         $consulta.=  $this->and($filtros);
        
//         if($sentencia = $this->conexion->prepare($consulta))
//         {
//             if($this->bind_param($sentencia, $filtros))
//             {
//                 if($sentencia->execute())
//                 {
//                     if($sentencia->bind_result($count))
//                     {
//                         if($sentencia->fetch())
//                         {
//                             $resultado->valor =$count;
//                         }
//                     }
//                     else
//                         $resultado->mensajeError = 'Falló el enlace del resultado.';
//                 }
//                 else
//                     $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
//             }
//             else
//                 $resultado->mensajeError = 'Falló el enlace de parámetros';
//         }
//         else
//             $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
//             return $resultado;
//     }
    
//     public function numeroEvidenciasPendientesMesActual($usuario,$criteriosSeleccion)
//     {
//         $resultado = new Resultado();
//         $resultado->valor = 0;
//         $filtros = array();
//         $filtros = $this->getFiltrosUsuario($usuario, $criteriosSeleccion,false);
//         $consulta = "SELECT count(*)
//                     FROM usuarios_procedimientos UP
//                     	INNER JOIN procedimientos P ON P.id = UP.procedimiento_id
//                         INNER JOIN usuarios U ON U.id = UP.usuario_id
//                     WHERE UP.estatus = 1 
//                     	AND UP.id NOT IN(SELECT usuario_procedimiento_id FROM evidencias E WHERE MONTH(E.fecha_alta) = MONTH(NOW()) AND YEAR(E.fecha_alta) = YEAR(NOW()) ) ";
      
//         $consulta.=  $this->and($filtros);
        
//         if($sentencia = $this->conexion->prepare($consulta))
//         {
//             if($this->bind_param($sentencia, $filtros))
//             {
//                 if($sentencia->execute())
//                 {
//                     if($sentencia->bind_result($count))
//                     {
//                         if($sentencia->fetch())
//                         {
//                             $resultado->valor =$count;
//                         }
//                     }
//                     else
//                         $resultado->mensajeError = 'Falló el enlace del resultado.';
//                 }
//                 else
//                     $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
//             }
//             else
//                 $resultado->mensajeError = 'Falló el enlace de parámetros';
//         }
//         else
//             $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
//             return $resultado;
//     }
    
    
    public function consultarEvidenciasCumplidas($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        
        $registros = array();
        
        //$filtros = array();
        $filtros = $this->getFiltrosN($usuario,$criteriosSeleccion,true);
        $where = $this->where($filtros);
        
        //UP.usuario_id = ? AND 
        
        $consulta =  $this->consultaBase . $where . " " .
        "ORDER BY codigo";
        
       

        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId,$validada, $comentariosValidacion, $administradorId, $administradorNombre, $administradorApellido,$validadorId, $validadorNombre, $validadorApellido))
                    {
                        while($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId, $validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido,$validadorId, $validadorNombre, $validadorApellido);
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
    
    public function consultarEvidencias($criteriosSeleccion)
    {
        $resultado = new Resultado();
        
        $registros = array();
        
        $filtros = array();
        
        
        
        $and="";
        if($criteriosSeleccion!=null)
        {
            if(isset($criteriosSeleccion->administradorId))
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'EM', 'campo'=>'administrador_id','valor'=>$criteriosSeleccion->administradorId]);
            if(isset($criteriosSeleccion->validada))
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'E', 'campo'=>'validada','valor'=> $criteriosSeleccion->validada]);
            if(isset($criteriosSeleccion->usuarioId))
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'E', 'campo'=>'usuario_id','valor'=> $criteriosSeleccion->usuarioId]);
        }
        
     
      
        
        $and = $this->and($filtros);
        
        $consulta =  $this->consultaBase .
        " WHERE  MONTH(E.fecha_alta) = $criteriosSeleccion->mes AND YEAR(E.fecha_alta) = $criteriosSeleccion->ano " . $and ." " .
        "ORDER BY codigo";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId,$validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido,$validadorId, $validadorNombre, $validadorApellido))
                    {
                        while($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId, $validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido,$validadorId, $validadorNombre, $validadorApellido);
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
    
    
    public function consultarPorcentajesEvidencias($usuario,$criteriosSeleccion)
    {
        
        $resultado = new Resultado();
        $filtros = $this->getFiltrosN($usuario,$criteriosSeleccion,false);
        $and = $this->and($filtros);
        $consulta = "SELECT SUM(justificadas)justificadas, SUM(enviadas)enviadas, SUM(pendientes)pendientes ".
            "\nFROM(" .
            $this->getConsultaEvidenciasBase($usuario,$criteriosSeleccion,$and)  .
            "\n) AS A ";
      
            
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($this->bind_param($sentencia, $filtros))
                {
                    if($sentencia->execute())
                    {
                        if($sentencia->bind_result($justificadas, $enviadas, $pendientes))
                        {
                            if($sentencia->fetch())
                            {
                                $porcentajes = array();
                                array_push($porcentajes,(object)['nombre'=>'Enviadas','valor'=>intval($enviadas)]);
                                array_push($porcentajes,(object)['nombre'=>'Pendientes','valor'=>intval($pendientes)]);
                                array_push($porcentajes,(object)['nombre'=>'Justificadas','valor'=>intval($justificadas)]);
                                
                                $resultado->valor = $porcentajes;
                            }
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
    
    
    public function consultarPorcentajesEmpresas($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        $filtros = $this->getFiltrosN($usuario,$criteriosSeleccion,false);
        $and = $this->and($filtros);
        $consulta = "SELECT empresaId, empresaNombre, empresaNombreCorto, SUM(justificadas)justificadas, SUM(enviadas)enviadas, SUM(pendientes)pendientes ".
            "\nFROM(" .
            $this->getConsultaEvidenciasBase($usuario,$criteriosSeleccion,$and)  .
            "\n) AS A " .
            "\nGROUP BY empresaId,empresaNombre,empresaNombreCorto" .
            "\nORDER BY empresaNombre";
            
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre,$nombreCorto, $justificadas, $enviadas, $pendientes))
                    {
                        while($sentencia->fetch())
                        {
                            $registro= (object) [
                                'id' =>  $id,
                                'nombre' =>  $nombre,
                                'nombreCorto' =>  $nombreCorto,
                                'justificadas' =>  $justificadas,
                                'enviadas' =>  $enviadas,
                                'pendientes' =>  $pendientes
                            ];
                            
                            $this->calcularPorcentaje($registro);
                            
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
    
    private function calcularPorcentaje(&$registro)
    {
        $registro->total = $registro->justificadas + $registro->enviadas + $registro->pendientes;
        $registro->cumplidas =$registro->justificadas + $registro->enviadas;
        $registro->porcentajeCumplimiento  = 0;
        if($registro->total!=0)
        {
            $registro->porcentajeCumplimiento = $registro->cumplidas  * 100 / $registro->total;
            $registro->porcentajeCumplimiento = number_format($registro->porcentajeCumplimiento, 1, '.', '');
        }
    }
    
    public function consultarPorcentajesSedes($usuario,$criteriosSeleccion)
    {
        
        $resultado = new Resultado();
        $registros = array();
        $filtros = $this->getFiltrosN($usuario,$criteriosSeleccion,false);
        $and = $this->and($filtros);
        $consulta = "SELECT sedeId, sedeNombre, sedeNombreCorto, SUM(justificadas)justificadas, SUM(enviadas)enviadas, SUM(pendientes)pendientes ".
            "\nFROM(" .
            $this->getConsultaEvidenciasBase($usuario,$criteriosSeleccion,$and)  .
            "\n) AS A " .
            "\nGROUP BY sedeId,sedeNombre,sedeNombreCorto";
            "\nORDER BY sedeNombre";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre,$nombreCorto, $justificadas, $enviadas, $pendientes))
                    {
                        while($sentencia->fetch())
                        {
                            $registro= (object) [
                                'id' =>  $id,
                                'nombre' =>  $nombre,
                                'nombreId' =>  $nombre ." (".$id.")",
                                'nombreCorto' =>  $nombreCorto,
                                'justificadas' =>  $justificadas,
                                'enviadas' =>  $enviadas,
                                'pendientes' =>  $pendientes
                            ];
                            $this->calcularPorcentaje($registro);
//                             $registro->total = $registro->justificadas + $registro->enviadas + $registro->pendientes;
//                             $registro->cumplidas =$registro->justificadas + $registro->enviadas;
//                             $registro->porcentajeCumplimiento = 0;
//                             if($registro->total!=0)
//                             {
//                                 $registro->porcentajeCumplimiento = $registro->cumplidas * 100 / $registro->total;
//                                 $registro->porcentajeCumplimiento = number_format($registro->porcentajeCumplimiento, 1, '.', '');
//                             }
                            
                            
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
    
    private function getFiltros($criteriosSeleccion)
    {
        $filtros = array();
        if($criteriosSeleccion!=null)
        {
            if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'EM', 'campo'=>'id','valor'=>$criteriosSeleccion->empresaId]);
            if(isset($criteriosSeleccion->sedeId) && $criteriosSeleccion->sedeId!="")
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'S', 'campo'=>'id','valor'=> $criteriosSeleccion->sedeId]);
            if(isset($criteriosSeleccion->departamentoId) && $criteriosSeleccion->departamentoId!="")
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'D', 'campo'=>'id','valor'=> $criteriosSeleccion->departamentoId]);
        }
        return $filtros;
    }
    
    
    
    public function consultarPorcentajesAreas($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $filtros = $this->getFiltrosN($usuario, $criteriosSeleccion,false);
        $and = $this->and($filtros);
        
        $consulta = "SELECT departamentoId, departamentoNombre,SUM(justificadas)justificadas, SUM(enviadas)enviadas, SUM(pendientes)pendientes ".
                    "\nFROM(" .
                   $this->getConsultaEvidenciasBase($usuario,$criteriosSeleccion,$and)  .
                   "\n) AS A " .
                   "\nGROUP BY departamentoId,departamentoNombre";
                   "\nORDER BY departamentoNombre";
                   
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
          // if($sentencia->bind_param('i',$usuario->empresaId))
           if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre,$justificadas, $enviadas, $pendientes))
                    {
                        while($sentencia->fetch())
                        {
                            $registro= (object) [
                                'id' =>  $id,
                                'nombre' =>  $nombre,
                                'nombreId' =>  $nombre ." (".$id.")",
                                'justificadas' =>  $justificadas,
                                'enviadas' =>  $enviadas,
                                'pendientes' =>  $pendientes
                            ];
                            
//                             $registro->total = $registro->justificadas + $registro->enviadas + $registro->pendientes;
//                             $registro->cumplidas =$registro->justificadas + $registro->enviadas;
//                             $registro->porcentajeCumplimiento  = 0;
//                             if($registro->total!=0)
//                             {
//                                 $registro->porcentajeCumplimiento = $registro->cumplidas  * 100 / $registro->total;
//                                 $registro->porcentajeCumplimiento = number_format($registro->porcentajeCumplimiento, 1, '.', '');
//                             }
                            $this->calcularPorcentaje($registro);
                            
                            
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
    
   
    
    public function getFiltroEstructura($usuario)
    {
        $filtros = array();
        switch ($usuario->tipoUsuarioId)
        {
            case \TipoUsuario::USUARIO:
                //$and =" AND U.id = $usuario->id";
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'id','operador'=>'IN','valor'=>$usuario->id]);
            break;
            case \TipoUsuario::SUPERVISOR:
                $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
                $resultado = $usuariosRepositorio->consultarIdsUsuarios($usuario);
                if($resultado->correcto())
                {
                    $usuariosIds = implode(",", $resultado->valor);
                    //$and =" AND U.id IN($usuariosIds)";
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'id','operador'=>'IN','valor'=>$usuariosIds]);
                }
            break;
            case \TipoUsuario::COORDINADOR:
                $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
                $resultado = $usuariosRepositorio->consultarIdsEmpresas($usuario->empresaId);
                if($resultado->correcto())
                {
                    $empresasIds = implode(",", $resultado->valor);
                    //$and =" AND EM.id IN($empresasIds)";
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'EM', 'campo'=>'id','operador'=>'IN','valor'=>$empresasIds]);
                }
            break;
            case \TipoUsuario::ADMINISTRADOR:
            break;
        }
        return $filtros;
    }
    
    public function getConsultaEvidenciasBase($usuario,$criteriosSeleccion,$and)
    {
      
        
        $campos = array();
        array_push($campos,(object)['tabla'=>'U','campo'=>'id','alias'=>'id']);
        array_push($campos,(object)['tabla'=>'U','campo'=>'nombre','alias'=>'nombre']);
        array_push($campos,(object)['tabla'=>'U','campo'=>'nombre_usuario','alias'=>'nombreUsuario']);
        array_push($campos,(object)['tabla'=>'U','campo'=>'apellido','alias'=>'apellido']);
        array_push($campos,(object)['tabla'=>'U','campo'=>'tipo_usuario_id','alias'=>'tipoUsuarioId']);
        array_push($campos,(object)['tabla'=>'EM','campo'=>'id','alias'=>'empresaId']);
        array_push($campos,(object)['tabla'=>'EM','campo'=>'nombre','alias'=>'empresaNombre']);
        array_push($campos,(object)['tabla'=>'EM','campo'=>'nombre_corto','alias'=>'empresaNombreCorto']);
        array_push($campos,(object)['tabla'=>'S','campo'=>'id','alias'=>'sedeId']);
        array_push($campos,(object)['tabla'=>'S','campo'=>'nombre','alias'=>'sedeNombre']);
        array_push($campos,(object)['tabla'=>'S','campo'=>'nombre_corto','alias'=>'sedeNombreCorto']);
        array_push($campos,(object)['tabla'=>'A','campo'=>'id','alias'=>'areaId']);
        array_push($campos,(object)['tabla'=>'A','campo'=>'nombre','alias'=>'areaNombre']);
        array_push($campos,(object)['tabla'=>'D','campo'=>'id','alias'=>'departamentoId']);
        array_push($campos,(object)['tabla'=>'D','campo'=>'nombre','alias'=>'departamentoNombre']);
        
        $primerDiaMes = "$criteriosSeleccion->ano-$criteriosSeleccion->mes-1";
        $ultimoDiaMes = date("Y-m-t", strtotime($primerDiaMes));
        $filtroAno1 = "";
        $filtroMes1 = "";
        $filtroAno2 = "";
        $filtroMes2 = "";
        if(isset($criteriosSeleccion->ano))
        {
            $filtroAno1 = "AND YEAR(E1.fecha_alta) = $criteriosSeleccion->ano";
            $filtroAno2 = "AND YEAR(E2.fecha_alta) = $criteriosSeleccion->ano";
        }
        if(isset($criteriosSeleccion->mes))
        {
            $filtroMes1 = "AND MONTH(E1.fecha_alta) = $criteriosSeleccion->mes";
            $filtroMes2 = "AND MONTH(E2.fecha_alta) = $criteriosSeleccion->mes";
        }
        
        $select = $this->selectAlias($campos);
        $consulta = $select.",
                    (
                    	SELECT count(*) numero
                    	FROM evidencias E1
                    		INNER JOIN usuarios_procedimientos UP1 ON UP1.id = E1.usuario_procedimiento_id
                    		INNER JOIN usuarios U1 ON U1.id = UP1.usuario_id
                    		INNER JOIN sedes S1 ON S1.id = U1.sede_id
                    		INNER JOIN empresas EM1 ON EM1.id = S1.empresa_id
                    		INNER JOIN areas A1 ON A1.id = U1.area_id
                            INNER JOIN departamentos D1 ON D1.id = U1.departamento_id
                    	WHERE justificacion_id IS NOT NULL AND EM1.id = EM.id AND A1.id = A.id AND D1.id = D.id AND U1.id = U.id  $filtroAno1 $filtroMes1
                    ) justificadas,
                    (
                    	SELECT count(*) numero
                    	FROM evidencias E1
                    		INNER JOIN usuarios_procedimientos UP1 ON UP1.id = E1.usuario_procedimiento_id
                    		INNER JOIN usuarios U1 ON U1.id = UP1.usuario_id
                    		INNER JOIN sedes S1 ON S1.id = U1.sede_id
                    		INNER JOIN empresas EM1 ON EM1.id = S1.empresa_id
                    		INNER JOIN areas A1 ON A1.id = U1.area_id
                            INNER JOIN departamentos D1 ON D1.id = U1.departamento_id
                    	WHERE justificacion_id IS NULL AND EM1.id = EM.id AND A1.id = A.id AND D1.id = D.id AND U1.id = U.id $filtroAno1 $filtroMes1
                    ) enviadas,
                    (
                    	SELECT count(*)
                    	FROM usuarios_procedimientos UP1
                    		INNER JOIN procedimientos P1 ON P1.id = UP1.procedimiento_id
                    		INNER JOIN usuarios U1 ON U1.id = UP1.usuario_id
                    		INNER JOIN sedes S1 ON S1.id = U1.sede_id
                    		INNER JOIN empresas EM1 ON EM1.id = S1.empresa_id
                    		INNER JOIN areas A1 ON A1.id = U1.area_id
                           INNER JOIN departamentos D1 ON D1.id = U1.departamento_id
                    	 WHERE ((UP1.estatus = 1 AND UP1.fecha_alta  <=  '$ultimoDiaMes') OR (UP1.estatus = 0 AND MONTH(UP1.fecha_alta)  <=  $criteriosSeleccion->mes AND  YEAR(UP1.fecha_alta) <= $criteriosSeleccion->ano AND MONTH(UP1.fecha_cancelacion) > $criteriosSeleccion->mes AND  YEAR(UP1.fecha_cancelacion) >= $criteriosSeleccion->ano))
                                AND EM1.id = EM.id AND A1.id = A.id AND D1.id = D.id AND U1.id = U.id 
                    		AND UP1.id NOT IN(
                    				SELECT usuario_procedimiento_id
                    				FROM evidencias E2
                    					INNER JOIN usuarios_procedimientos UP2 ON UP2.id = E2.usuario_procedimiento_id
                    					INNER JOIN usuarios U2 ON U2.id = UP2.usuario_id
                    					INNER JOIN sedes S2 ON S2.id = U2.sede_id
                    					INNER JOIN empresas EM2 ON EM2.id = S2.empresa_id
                    					INNER JOIN areas A2 ON A2.id = U2.area_id
                                        INNER JOIN departamentos D2 ON D2.id = U2.departamento_id
                    				WHERE  EM2.id = EM1.id AND A2.id = A1.id AND D2.id = D1.id AND U1.id = U.id  $filtroAno2 $filtroMes2
                    				)
                    				
                    )pendientes
                    FROM usuarios U
                    	INNER JOIN usuarios_procedimientos UP ON U.id = UP.usuario_id
                    	INNER JOIN sedes S ON S.id = U.sede_id
                    	INNER JOIN empresas EM ON EM.id = S.empresa_id
                    	INNER JOIN areas A ON A.id = U.area_id
                        INNER JOIN departamentos D ON D.id = U.departamento_id
                    WHERE UP.estatus = 1 
                        AND U.estatus = 1 
                        AND S.estatus = 1 
                        AND EM.estatus = 1 
                        AND A.estatus = 1
                        AND D.estatus = 1
                        AND U.permiso_saha = 1
                  ";
      
        $consulta .=  $and . " ";
        
        $consulta .="\n".$this->groupBy($campos);

        return $consulta;
    }
    
    public function getFiltrosN($usuario, $criteriosSeleccion, $agregarFiltrosFecha)
    {
        $filtros = array();
        switch ($usuario->tipoUsuarioId)
        {
            case \TipoUsuario::USUARIO:
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'id','operador'=>'IN','valor'=>$usuario->id]);
                break;
            case \TipoUsuario::SUPERVISOR:
                if(isset($criteriosSeleccion->usuarioId) && $criteriosSeleccion->usuarioId!="")
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'id','valor'=>$criteriosSeleccion->usuarioId]);
                else
                {
                    $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
                    $resultado = $usuariosRepositorio->consultarIdsUsuarios($usuario);
                    if($resultado->correcto())
                    {
                        $usuariosIds = implode(",", $resultado->valor);
                        array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'id','operador'=>'IN','valor'=>$usuariosIds]);
                    }
                }
            break;
            case \TipoUsuario::COORDINADOR:
                if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'EM', 'campo'=>'id','valor'=>$criteriosSeleccion->empresaId]);
                else
                {
                    $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
                    $resultado = $usuariosRepositorio->consultarIdsEmpresas($usuario->empresaId);
                    if($resultado->correcto())
                    {
                        $empresasIds = implode(",", $resultado->valor);
                        array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'EM', 'campo'=>'id','operador'=>'IN','valor'=>$empresasIds]);
                    }
                }
                break;
            case \TipoUsuario::ADMINISTRADOR:
                if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'EM', 'campo'=>'id','valor'=>$criteriosSeleccion->empresaId]);
                
            break;
        }
        if(isset($criteriosSeleccion->sedeId) && $criteriosSeleccion->sedeId!="")
            array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'S', 'campo'=>'id','valor'=> $criteriosSeleccion->sedeId]);
        if(isset($criteriosSeleccion->departamentoId) && $criteriosSeleccion->departamentoId!="")
            array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'D', 'campo'=>'id','valor'=> $criteriosSeleccion->departamentoId]);
        if($agregarFiltrosFecha)
        {
            if(isset($criteriosSeleccion->ano)  && $criteriosSeleccion->ano!="")
                array_push($filtros,(object)['tipoDato'=>'int','campo'=>'YEAR(E.fecha_alta)','valor'=>$criteriosSeleccion->ano]);
            if(isset($criteriosSeleccion->mes)  && $criteriosSeleccion->mes!="")
                array_push($filtros,(object)['tipoDato'=>'int','campo'=>'MONTH(E.fecha_alta)','valor'=>$criteriosSeleccion->mes]);
        }
            
        return $filtros;
    }
    
    public function consultarPorcentajesUsuarios($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
//         $filtros = $this->getFiltros($criteriosSeleccion);
//         $and = $this->and($filtros);
        $filtros = $this->getFiltrosN($usuario, $criteriosSeleccion,false);
        $and = $this->and($filtros);
        $consulta = "SELECT id, nombre, nombreUsuario,apellido, tipoUsuarioId, empresaId, empresaNombre, sedeId, sedeNombre, areaId, areaNombre, departamentoId, departamentoNombre, SUM(justificadas)justificadas, SUM(enviadas)enviadas, SUM(pendientes)pendientes ".
            "\nFROM(" .
            $this->getConsultaEvidenciasBase($usuario,$criteriosSeleccion,$and)  .
            "\n) AS A " .
            "\nGROUP BY id, nombre, nombreUsuario,apellido, tipoUsuarioId, empresaId, empresaNombre, sedeId, sedeNombre, areaId, areaNombre, departamentoId, departamentoNombre".
            "\nORDER BY  FIELD(id,$usuario->id) DESC, nombre,apellido";
        
            
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre, $nombreUsuario, $apellido, $tipoUsuarioId, $empreasaId,$empresaNombre, $sedeId, $sedeNombre, $areaId, $areaNombre, $departamentoId, $departamentoNombre, $justificadas, $enviadas, $pendientes))
                    {
                        while($sentencia->fetch())
                        {
                            
                            $registro= (object) [
                                'id' =>  $id,
                                'nombreUsuario' => $nombreUsuario,
                                'nombre' =>  $nombre,
                                'apellido' =>  $apellido,
                                'tipoUsuarioId' =>  $tipoUsuarioId,
                                'empresaId' =>  $empreasaId,
                                'empresaNombre' =>  $empresaNombre,
                                'sedeId' =>  $sedeId,
                                'sedeNombre' =>  $sedeNombre,
                                'areaId' =>  $areaId,
                                'areaNombre' =>  $areaNombre,
                                'departamentoId' =>  $departamentoId,
                                'departamentoNombre' =>  $departamentoNombre,
                                'justificadas' =>  $justificadas,
                                'enviadas' =>  $enviadas,
                                'pendientes' =>  $pendientes
                            ];
                            
                            $this->calcularPorcentaje($registro);
                            
                            $registro->nombreCompleto = $registro->nombre . " " . $registro->apellido;
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
                        $resultado->mensajeError = __FUNCTION__.'. Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = __FUNCTION__.' .Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__.'. Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = __FUNCTION__.'. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        return $resultado;
    }
   
    
//     public function consultarPorcentajes($campos,$usuario,$criteriosSeleccion)
//     {
//         $resultado = new Resultado();
//         $registros = array();
        
//         $filtros = array();
        
//         $consulta = $this->getConsultaEvidenciasBase($usuario,$criteriosSeleccion);
//         $consulta .= $this->getFiltroEstructura($usuario) ." ";
        
//         $consulta .="\n".$this->groupBy($campos);
//         $consulta .= "\n".$this->orderBy($campos);
        
        
//         if($sentencia = $this->conexion->prepare($consulta))
//         {
//             if($this->bind_param($sentencia, $filtros))
//             {
//                 if($sentencia->execute())
//                 {
//                     if($sentencia->bind_result($id, $nombre, $nombreUsuario, $apellido, $tipoUsuarioId, $empreasaId,$empresaNombre, $sedeId, $sedeNombre, $areaId, $areaNombre, $departamentoId, $departamentoNombre, $justificadas, $enviadas, $pendientes))
//                     {
//                         while($sentencia->fetch())
//                         {
                            
//                             $registro= (object) [
//                                 'id' =>  $id,
//                                 'nombreUsuario' => $nombreUsuario,
//                                 'nombre' =>  $nombre,
//                                 'apellido' =>  $apellido,
//                                 'tipoUsuarioId' =>  $tipoUsuarioId,
//                                 'empresaId' =>  $empreasaId,
//                                 'empresaNombre' =>  $empresaNombre,
//                                 'sedeId' =>  $sedeId,
//                                 'sedeNombre' =>  $sedeNombre,
//                                 'areaId' =>  $areaId,
//                                 'areaNombre' =>  $areaNombre,
//                                 'departamentoId' =>  $departamentoId,
//                                 'departamentoNombre' =>  $departamentoNombre,
//                                 'justificadas' =>  $justificadas,
//                                 'enviadas' =>  $enviadas,
//                                 'pendientes' =>  $pendientes
//                             ];
                            
//                             $registro->total = $registro->justificadas + $registro->enviadas + $registro->pendientes;
//                             $registro->cumplidas =$registro->justificadas + $registro->enviadas;
//                             $registro->porcentajeCumplimiento  = 0;
//                             if($registro->total !=0)
//                             {
//                                 $registro->porcentajeCumplimiento = $registro->cumplidas  * 100 / $registro->total ;
//                                 $registro->porcentajeCumplimiento = number_format($registro->porcentajeCumplimiento, 1, '.', '');
//                             }
                            
//                             $registro->nombreCompleto = $registro->usuarioNombre . " " . $registro->apellido;
//                             $registro->nombreId =  $registro->nombreCompleto ." (".$registro->id.")";
//                             $registro->fotoPerfil =  "../fotos/usuario". $registro->id .".jpg";
//                             if(file_exists($registro->fotoPerfil))
//                                 $registro->fotoPerfil =  "php/fotos/usuario". $registro->id .".jpg";
//                             else
//                                 $registro->fotoPerfil =  "php/fotos/default.jpg";
                                    
                                    
//                             array_push($registros,$registro);
//                         }
//                         $resultado->valor = $registros;
//                     }
//                     else
//                         $resultado->mensajeError = __FUNCTION__.'. Falló el enlace del resultado.';
//                 }
//                 else
//                     $resultado->mensajeError = __FUNCTION__.' .Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
//             }
//             else
//                 $resultado->mensajeError = __FUNCTION__.'. Falló el enlace de parámetros';
//         }
//         else
//             $resultado->mensajeError = __FUNCTION__.'. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
//             return $resultado;
//     }
    
    public function consultarAnosMeses($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $filtros = $this->getFiltrosN($usuario,$criteriosSeleccion,false);
        $where = $this->where($filtros);
        
        $consulta = "SELECT YEAR(E.fecha_alta) ano, MONTH(E.fecha_alta) mes
                    FROM evidencias E
                        INNER JOIN  usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
                        INNER JOIN usuarios U ON U.id = UP.usuario_id
                        INNER JOIN sedes S ON S.id = U.sede_id
                        INNER JOIN empresas EM ON EM.id = S.empresa_id 
                        INNER JOIN departamentos D ON D.id = U.departamento_id";

        $consulta .= $where;
        
        $consulta.=" GROUP BY ano, mes
                    ORDER BY ano desc, mes desc";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($ano, $mes))
                    {
                        while($sentencia->fetch())
                        {
                            $mesNombre = $this->getNombreMes($mes);
                            $registro= (object) [
                                'ano' =>  $ano,
                                'mes' =>  $mes,
                                'mesNombre' =>  $mesNombre
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
    
    public function consultarAnos($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $filtros = $this->getFiltrosN($usuario,$criteriosSeleccion,false);
        $where = $this->where($filtros);
        
        $consulta = "SELECT YEAR(E.fecha_alta) ano
                    FROM evidencias E
                        INNER JOIN  usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
                        INNER JOIN usuarios U ON U.id = UP.usuario_id
                        LEFT JOIN sedes S ON S.id = U.sede_id
                        LEFT JOIN empresas EM ON EM.id = S.empresa_id ";
        
        $consulta .= $where;
        
        $consulta.=" GROUP BY ano
                    ORDER BY ano";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($ano))
                    {
                        while($sentencia->fetch())
                        {
                            $registro= (object) [
                                'id' =>  $ano,
                                'nombre' =>  $ano
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
    
    private function getNombreMes($mes)
    {
        $nombreMes="";
        switch ($mes)
        {
            case 1: $nombreMes = "Enero"; break;
            case 2: $nombreMes = "Febrero"; break;
            case 3: $nombreMes = "Marzo"; break;
            case 4: $nombreMes = "Abril"; break;
            case 5: $nombreMes = "Mayo"; break;
            case 6: $nombreMes = "Junio"; break;
            case 7: $nombreMes = "Julio"; break;
            case 8: $nombreMes = "Agosto"; break;
            case 9: $nombreMes = "Septiembre"; break;
            case 10: $nombreMes = "Octubre"; break;
            case 11: $nombreMes = "Noviembre"; break;
            case 12: $nombreMes = "Diciembre"; break;
         }
         return $nombreMes;
    }
    
    public function consultarUsuariosConProcedimientosAsignados($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $filtros =  array();
        
        $consulta = "SELECT U.id,U.nombre, U.apellido, U.tipo_usuario_id
                    FROM usuarios_procedimientos UP
                    	LEFT JOIN usuarios U ON U.id = UP.usuario_id
                    	LEFT JOIN sedes S ON S.id = U.sede_id
                    	LEFT JOIN empresas EM ON EM.id = S.empresa_id
                    	LEFT JOIN areas A ON A.id = U.area_id
                    WHERE  UP.estatus = 1 AND EM.id = ? AND A.id = ? 
                    GROUP BY U.id, U.nombre";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('ii',$usuario->empresaId,$usuario->areaId))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre, $apellido, $tipoUsuarioId))
                    {
                        while($sentencia->fetch())
                        {
                            $registro= (object) [
                                'id' =>  $id,
                                'nombre' =>  $nombre,
                                'apellido' =>  $apellido,
                                'tipoUsuarioId' => $tipoUsuarioId
                            ];
                            
                            
                            $registro->nombreCompleto = $registro->nombre . " " . $registro->apellido;
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
    
    public function consultarPorcentajesAdministradores($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $filtros =  array();
        
        $consulta = "SELECT V.id, V.nombre, V.apellido,
                 (
                	SELECT count(*) numero
                	FROM evidencias E1
                		INNER JOIN usuarios_procedimientos UP1 ON UP1.id = E1.usuario_procedimiento_id
                		INNER JOIN usuarios U1 ON U1.id = UP1.usuario_id
                		LEFT JOIN sedes S1 ON S1.id = U1.sede_id
                		LEFT JOIN empresas EM1 ON EM1.id = S1.empresa_id
                		LEFT JOIN usuarios V1 ON V1.id = EM1.administrador_id
                	WHERE MONTH(E1.fecha_alta) = MONTH(E.fecha_alta)  AND YEAR(E1.fecha_alta) = YEAR(E.fecha_alta) AND E1.validada=1 AND V1.id = V.id
                ) validadas,
                (		SELECT count(*) numero
                	FROM evidencias E1
                		INNER JOIN usuarios_procedimientos UP1 ON UP1.id = E1.usuario_procedimiento_id
                		INNER JOIN usuarios U1 ON U1.id = UP1.usuario_id
                		LEFT JOIN sedes S1 ON S1.id = U1.sede_id
                		LEFT JOIN empresas EM1 ON EM1.id = S1.empresa_id
                		LEFT JOIN usuarios V1 ON V1.id = EM1.administrador_id
                	WHERE MONTH(E1.fecha_alta) = MONTH(E.fecha_alta)  AND YEAR(E1.fecha_alta) = YEAR(E.fecha_alta) AND E1.validada=0 AND V1.id = V.id
                ) noValidadas 
                FROM evidencias E
                	INNER JOIN  usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
                	INNER JOIN usuarios U ON U.id = UP.usuario_id
                	LEFT JOIN sedes S ON S.id = U.sede_id
                	LEFT JOIN empresas EM ON EM.id = S.empresa_id
                	INNER JOIN procedimientos P ON P.id = UP.procedimiento_id
                	LEFT JOIN justificaciones J ON J.id = E.justificacion_id
                	LEFT JOIN usuarios V ON V.id = EM.administrador_id
                WHERE MONTH(E.fecha_alta) =$criteriosSeleccion->mes AND YEAR(E.fecha_alta) = $criteriosSeleccion->ano AND V.id  IS NOT NULL 
                GROUP BY V.id, V.nombre, V.apellido";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre,$apellido, $validadas, $noValidadas))
                    {
                        while($sentencia->fetch())
                        {
                            $registro= (object) [
                                'id' =>  $id,
                                'nombre' =>  $nombre,
                                'apellido' =>  $apellido,
                                'validadas' =>  $validadas,
                                'noValidadas' =>  $noValidadas
                            ];
                            
                            $registro->total = $registro->validadas + $registro->noValidadas;
                            $cumplimieto =$registro->validadas;
                            $registro->porcentajeCumplimiento = 0;
                            if($registro->total!=0)
                            {
                               $registro->porcentajeCumplimiento = $cumplimieto * 100 / $registro->total;
                               $registro->porcentajeCumplimiento = number_format($registro->porcentajeCumplimiento, 1, '.', '');
                            }
                               
                            
                            $registro->nombreCompleto = $registro->nombre . " " . $registro->apellido;
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
    
    private function getFiltrosUsuario($usuario,$criteriosSeleccion,$agregarCriteriosSeleccion)
    {
        $filtros = array();
        switch ($usuario->tipoUsuarioId)
        {
            case \TipoUsuario::USUARIO:
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'UP', 'campo'=>'usuario_id','valor'=>$usuario->id]);
            break;
            case \TipoUsuario::SUPERVISOR:
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'empresa_id','valor'=>$usuario->empresaId]);
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'sede_id','valor'=>$usuario->sedeId]);
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'area_id','valor'=>$usuario->areaId]);
            break;
            case \TipoUsuario::COORDINADOR:
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'empresa_id','valor'=>$usuario->empresaId]);
            break;
            case \TipoUsuario::ADMINISTRADOR:
                if($criteriosSeleccion!=null)
                {
                    if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
                        array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'empresa_id','valor'=>$criteriosSeleccion->empresaId]);
                    if(isset($criteriosSeleccion->sedeId) && $criteriosSeleccion->sedeId!="")
                        array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'sede_id','valor'=>$criteriosSeleccion->sedeId]);
                        if(isset($criteriosSeleccion->areaId) && $criteriosSeleccion->areaId!="")
                        array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'area_id','valor'=>$criteriosSeleccion->areaId]);
                }
            break;
        }
        if($criteriosSeleccion!=null && $agregarCriteriosSeleccion)
        {
            if(isset($criteriosSeleccion->ano)  && $criteriosSeleccion->ano!="")
                array_push($filtros,(object)['tipoDato'=>'int','campo'=>'YEAR(E.fecha_alta)','valor'=>$criteriosSeleccion->ano]);
            if(isset($criteriosSeleccion->mes)  && $criteriosSeleccion->mes!="")
                array_push($filtros,(object)['tipoDato'=>'int','campo'=>'MONTH(E.fecha_alta)','valor'=>$criteriosSeleccion->mes]);
        }
        
        return $filtros;
    }
    
    public function consultarEvidenciasJustificacion($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
//         $filtros = $this->getFiltrosUsuario($usuario, $criteriosSeleccion,true);
        
//         $where = $this->where($filtros);
        $filtros = $this->getFiltrosN($usuario,$criteriosSeleccion,true);
        $where = $this->and($filtros);
        $consulta = "SELECT justificacion_id, J.nombre, count(justificacion_id) valor
            FROM evidencias E
            		INNER JOIN usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
            		INNER JOIN procedimientos P ON P.id = UP.procedimiento_id
                    INNER JOIN justificaciones J ON J.id = E.justificacion_id
                    INNER JOIN usuarios U ON U.id = UP.usuario_id
                    INNER JOIN sedes S ON S.id = U.sede_id
                    INNER JOIN empresas EM ON EM.id = S.empresa_id
                     $where ";
       $consulta.= "GROUP BY justificacion_id, J.nombre";
       
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre,$valor))
                    {
                        while($sentencia->fetch())
                        {
                            $procedimiento= (object) [
                                'id' =>  $id,
                                'nombre' =>  $nombre,
                                'valor' =>  $valor
                            ];
                            array_push($registros,$procedimiento);
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
    
   
    
    

    public function actualizar(Evidencia $modelo,$nombreArchivoSubido)
    {
        if($modelo->justificacionId=="")
            $modelo->justificacionId=null;
        $resultado = new Resultado();
        $consulta = "UPDATE evidencias
                     SET 
                         realizo_actividad = ?,
                         justificacion_id = ?,
                         comentarios = ?,
                         fecha_modificacion = NOW(),
                         nombre_archivo = ?
                     WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('iisss',$modelo->realizoActividad, $modelo->justificacionId, $modelo->comentarios,$nombreArchivoSubido,$modelo->id))
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
    
    public function validarEvidencia($usuario,Evidencia $modelo)
    {
        if($modelo->justificacionId=="")
            $modelo->justificacionId=null;
            $resultado = new Resultado();
            $consulta = "UPDATE evidencias
                     SET
                         validada = ?,
                         comentarios_validacion = ?,
                         fecha_modificacion = NOW(),
                         validacion_usuario_id = ?
                     WHERE id = ?";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('isii',$modelo->validada, $modelo->comentariosValidacion,$usuario->id,$modelo->id))
                {
                    if($sentencia->execute())
                    {
                        $resultado->valor=$modelo->id;
                       
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
            
            
            
            $where="";
            if($criteriosSeleccion!=null)
            {
               
                if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'empresa_id','valor'=>$criteriosSeleccion->empresaId]);
                if(isset($criteriosSeleccion->sedeId) && $criteriosSeleccion->sedeId!="")
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'sede_id','valor'=>$criteriosSeleccion->sedeId]);
                if(isset($criteriosSeleccion->areaId) && $criteriosSeleccion->areaId!="")
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'area_id','valor'=>$criteriosSeleccion->areaId]);
                if(isset($criteriosSeleccion->usuarioId) && $criteriosSeleccion->usuarioId!="")
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'UP', 'campo'=>'usuario_id','valor'=>$criteriosSeleccion->usuarioId]);
                if(isset($criteriosSeleccion->administradorId)  && $criteriosSeleccion->administradorId!="")
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'EM', 'campo'=>'administrador_id','valor'=>$criteriosSeleccion->administradorId]);
                if(isset($criteriosSeleccion->validada)  && $criteriosSeleccion->validada!="")
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'E', 'campo'=>'validada','valor'=> $criteriosSeleccion->validada]);
                if(isset($criteriosSeleccion->mes)  && $criteriosSeleccion->mes!="")
                    array_push($filtros,(object)['tipoDato'=>'int','campo'=>'MONTH(E.fecha_alta)','valor'=> $criteriosSeleccion->mes]);
                if(isset($criteriosSeleccion->ano)  && $criteriosSeleccion->ano!="")
                    array_push($filtros,(object)['tipoDato'=>'int','campo'=>'YEAR(E.fecha_alta)','valor'=> $criteriosSeleccion->ano]);
            }
            
            
            
            
            $where = $this->where($filtros);
            
            $consulta =  $this->consultaBase . $where .
            " order by UNIX_TIMESTAMP(E.fecha_alta) desc";
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($this->bind_param($sentencia, $filtros))
                {
                    if($sentencia->execute())
                    {
                        if($sentencia->bind_result($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId,$validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido,$validadorId, $validadorNombre, $validadorApellido))
                        {
                            while($sentencia->fetch())
                            {
                                $registro = $this->crearRegistro($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId, $validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido,$validadorId, $validadorNombre, $validadorApellido);
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
        ' WHERE E.id  = ?';
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('s',$llaves->id))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId, $validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido,$validadorId, $validadorNombre, $validadorApellido))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId, $validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido,$validadorId, $validadorNombre, $validadorApellido);
                            $resultado->valor = $registro;
                        }
                        else
                            $resultado->mensajeError = __FUNCTION__. ' No se encontró ningún resultado.';
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
            $resultado->mensajeError = __FUNCTION__. ' Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        return $resultado;
    }

    public function eliminar($llaves)
    {
        $resultado = new Resultado();
        $consulta = "DELETE FROM evidencias WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('s',$llaves->id))
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

    private function crearRegistro($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId, $validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido,$validadorId, $validadorNombre, $validadorApellido)
    {
        $registro= (object) 
        [
            'id' => $id,
            'usuarioProcedimientoId' => $usuarioProcedimientoId,
            'realizoActividad' => $realizoActividad,
            'justificacionId' => $justificacionId,
            'justificacionNombre' => $justificacionNombre,
            'comentarios' => $comentarios,
            'fecha' => $fecha,
            'nombre' => $nombre,
            'nombreArchivo' => $nombreArchivo,
            'codigo' => $codigo,
            'numeroComentarios' => $numeroComentarios,
            'usuarioNombre' => $usuarioNombre,
            'usuarioApellido' => $usuarioApellido,
            'sedeId' => $sedeId,
            'sedeNombre' => $sedeNombre,
            'empresaId' => $empresaId,
            'empresaNombre' => $empresaNombre,
            'usuarioId' => $usuarioId,
            'validada' => $validada,
            'comentariosValidacion' => $comentariosValidacion,
            'administradorId' => $administradorId,
            'administradorNombre' => $administradorNombre,
            'administradorApellido' => $administradorApellido,
            'validadorId' => $validadorId,
            'validadorNombre' => $validadorNombre,
            'validadorApellido' => $validadorApellido
            
            
        ];
        
        $registro->usuarioNombreCompleto = $registro->usuarioNombre . " " . $registro->usuarioApellido;
        $registro->fotoPerfil =  "../fotos/usuario". $registro->usuarioId .".jpg";
        if(file_exists($registro->fotoPerfil))
            $registro->fotoPerfil =  "php/fotos/usuario". $registro->usuarioId .".jpg";
        else
            $registro->fotoPerfil =  "php/fotos/default.jpg";
        
            
        $registro->administradorNombreCompleto = $registro->administradorNombre . " " . $registro->administradorApellido;
        $registro->administradorFotoPerfil =  "../fotos/usuario". $registro->administradorId .".jpg";
        if(file_exists($registro->administradorFotoPerfil))
            $registro->administradorFotoPerfil =  "php/fotos/usuario". $registro->administradorId .".jpg";
        else
            $registro->administradorFotoPerfil =  "php/fotos/default.jpg";
    
        if($registro->validada==1) 
        {
            if($registro->validadorId==null || $registro->validadorId=="")
            {
                $registro->validadorId = $registro->administradorId;
                $registro->validadorNombre = $registro->administradorNombre;
                $registro->validadorApellido = $registro->administradorApellido;
                $registro->validadorNombreCompleto = $registro->administradorNombre . " " . $registro->administradorApellido;
                $registro->validadorFotoPerfil =  "../fotos/usuario". $registro->administradorId .".jpg";
                if(file_exists($registro->validadorFotoPerfil))
                    $registro->validadorFotoPerfil =  "php/fotos/usuario". $registro->administradorId .".jpg";
                else
                    $registro->validadorFotoPerfil =  "php/fotos/default.jpg";
              
            }
            else
            {
                $registro->validadorNombreCompleto = $registro->validadorNombre . " " . $registro->validadorApellido;
                $registro->validadorFotoPerfil =  "../fotos/usuario". $registro->validadorId .".jpg";
                if(file_exists($registro->validadorFotoPerfil))
                    $registro->validadorFotoPerfil =  "php/fotos/usuario". $registro->validadorId .".jpg";
                    else
                        $registro->validadorFotoPerfil =  "php/fotos/default.jpg";
            }
        }
       
             
            
        
        $registro->empresaLogo =  "../logos_empresas/logo". $registro->empresaId .".png";
        if(file_exists($registro->empresaLogo))
            $registro->empresaLogo =  "php/logos_empresas/logo". $registro->empresaId .".png";
        else
            $registro->empresaLogo =  "php/logos_empresas/default.png";
        
        
        return $registro;
    }
    
    public function numeroEvidenciasJustificadas($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $resultado->valor = 0;
        $filtros = $this->getFiltrosUsuario($usuario, $criteriosSeleccion,true);
        
       
        
//         $consulta = "SELECT count(*) numero
//                     FROM evidencias E
//                         INNER JOIN usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
//                         INNER JOIN usuarios U ON U.id = UP.usuario_id
//                     WHERE justificacion_id IS NOT NULL AND U.estatus = 1 ";}

        $consulta = "SELECT count(*) numero
                    FROM evidencias E
                        INNER JOIN usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
                        INNER JOIN usuarios U ON U.id = UP.usuario_id
                    WHERE U.estatus= 1
                        AND justificacion_id IS NOT NULL ";

        $consulta.=  $this->and($filtros);
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($count))
                    {
                        if($sentencia->fetch())
                        {
                            $resultado->valor =$count;
                        }
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
    
    public function numeroEvidenciasEnviadas($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $resultado->valor = 0;
        $filtros = $this->getFiltrosUsuario($usuario, $criteriosSeleccion,true);
        $consulta = "SELECT count(*) numero
                    FROM evidencias E
                        INNER JOIN usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
                        INNER JOIN usuarios U ON U.id = UP.usuario_id
                    WHERE justificacion_id IS NULL AND U.estatus = 1 ";
        $consulta.=  $this->and($filtros);
        
      
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($count))
                    {
                        if($sentencia->fetch())
                        {
                            $resultado->valor =$count;
                        }
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
    
    public function numeroEvidenciasPendientes($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $primerDiaMes = "$criteriosSeleccion->ano-$criteriosSeleccion->mes-1";
        $ultimoDiaMes = date("Y-m-t", strtotime($primerDiaMes));
        $resultado->valor = 0;
        $filtros = array();
        $filtros = $this->getFiltrosUsuario($usuario, null,false);
        
//         $consulta = "SELECT count(*)
//                     FROM usuarios_procedimientos UP
//                     	INNER JOIN procedimientos P ON P.id = UP.procedimiento_id
//                         INNER JOIN usuarios U ON U.id = UP.usuario_id
//                     WHERE UP.estatus = 1  
//                     	AND UP.id NOT IN(SELECT usuario_procedimiento_id FROM evidencias E WHERE MONTH(E.fecha_alta) = $criteriosSeleccion->mes AND YEAR(E.fecha_alta) = $criteriosSeleccion->ano) ";
        
        $consulta = "SELECT count(*)
                    FROM usuarios_procedimientos UP
                    	INNER JOIN procedimientos P ON P.id = UP.procedimiento_id
                        INNER JOIN usuarios U ON U.id = UP.usuario_id
                    WHERE U.estatus = 1 AND ((UP.estatus = 1 AND UP.fecha_alta  <=  '$ultimoDiaMes') OR (UP.estatus = 0 AND MONTH(UP.fecha_alta)  <=  $criteriosSeleccion->mes AND  YEAR(UP.fecha_alta) <= $criteriosSeleccion->ano AND MONTH(UP.fecha_cancelacion) > $criteriosSeleccion->mes AND  YEAR(UP.fecha_cancelacion) >= $criteriosSeleccion->ano))
                            AND UP.id NOT IN(SELECT usuario_procedimiento_id FROM evidencias E WHERE MONTH(E.fecha_alta) = $criteriosSeleccion->mes AND YEAR(E.fecha_alta) = $criteriosSeleccion->ano) ";
        
        
        $consulta.=  $this->and($filtros);
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($count))
                    {
                        if($sentencia->fetch())
                        {
                            $resultado->valor =$count;
                        }
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
    
}
