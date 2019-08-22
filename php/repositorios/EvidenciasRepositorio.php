<?php
namespace php\repositorios;

use php\interfaces\IEvidenciasRepositorio;
use php\modelos\Evidencia;
use php\modelos\Resultado;

include '../interfaces/IEvidenciasRepositorio.php';
include '../modelos/Evidencia.php';
include 'RepositorioBase.php';
//require_once("../clases/TipoUsuario.php");
require_once('../clases/Resultado.php');

class EvidenciasRepositorio extends RepositorioBase implements IEvidenciasRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = "SELECT E.id, usuario_procedimiento_id, realizo_actividad, justificacion_id, comentarios, IFNULL(DATE_FORMAT(E.fecha_alta,'%d/%m/%Y %H:%i:%s'),'') as fecha, P.nombre, nombre_archivo, P.codigo, J.nombre,
                                (SELECT count(C.id) FROM evidencias_comentarios C WHERE C.evidencia_id = E.id) numeroComentarios, U.nombre, U.apellido, S.id, S.nombre, EM.id, EM.nombre, U.id, validada, comentarios_validacion, EM.administrador_id, V.nombre administradorNombre, V.apellido administradorApellido   
                               FROM evidencias E
                            		INNER JOIN  usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
                                    INNER JOIN usuarios U ON U.id = UP.usuario_id
                                    LEFT JOIN sedes S ON S.id = U.sede_id
                                    LEFT JOIN empresas EM ON EM.id = S.empresa_id
                            		INNER JOIN procedimientos P ON P.id = UP.procedimiento_id
                                    LEFT JOIN justificaciones J ON J.id = E.justificacion_id
                                    LEFT JOIN usuarios V ON V.id = EM.administrador_id
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
    
    public function numeroEvidenciasJustificadasMesActual($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $resultado->valor = 0;
        $filtros = $this->getFiltrosUsuario($usuario, $criteriosSeleccion);
        
        
        $consulta = "SELECT count(*) numero
                    FROM evidencias E
                        INNER JOIN usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
                        INNER JOIN usuarios U ON U.id = UP.usuario_id
                    WHERE MONTH(E.fecha_alta) = MONTH(NOW()) AND YEAR(E.fecha_alta) = YEAR(NOW()) AND justificacion_id IS NOT NULL 
                    ";
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
    
    public function numeroEvidenciasEnviadasMesActual($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $resultado->valor = 0;
        $filtros = $this->getFiltrosUsuario($usuario, $criteriosSeleccion);
        $consulta = "SELECT count(*) numero
                    FROM evidencias E
                        INNER JOIN usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
                        INNER JOIN usuarios U ON U.id = UP.usuario_id
                    WHERE MONTH(E.fecha_alta) = MONTH(NOW()) AND YEAR(E.fecha_alta) = YEAR(NOW()) AND justificacion_id IS NULL
                    ";
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
    
    public function numeroEvidenciasPendientesMesActual($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $resultado->valor = 0;
        $filtros = array();
        $filtros = $this->getFiltrosUsuario($usuario, $criteriosSeleccion);
        $consulta = "SELECT count(*)
                    FROM usuarios_procedimientos UP
                    	INNER JOIN procedimientos P ON P.id = UP.procedimiento_id
                        INNER JOIN usuarios U ON U.id = UP.usuario_id
                    WHERE UP.estatus = 1
                    	AND UP.id NOT IN(SELECT usuario_procedimiento_id FROM evidencias E WHERE MONTH(E.fecha_alta) = MONTH(NOW()) AND YEAR(E.fecha_alta) = YEAR(NOW()) ) ";
      
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
    
    
    public function consultarEvidenciasCumplidasMesActual($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        
        $registros = array();
        
        $filtros = $this->getFiltrosUsuario($usuario, $criteriosSeleccion);
        
        $and = $this->and($filtros);
        
        //UP.usuario_id = ? AND 
        
        $consulta =  $this->consultaBase .
        " WHERE MONTH(E.fecha_alta) = MONTH(NOW()) AND YEAR(E.fecha_alta) = YEAR(NOW()) " . $and ." " .
        "ORDER BY codigo";

        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId,$validada, $comentariosValidacion, $administradorId, $administradorNombre, $administradorApellido))
                    {
                        while($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId, $validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido);
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
    
    public function consultarEvidenciasMesActual($criteriosSeleccion)
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
        " WHERE  MONTH(E.fecha_alta) = MONTH(NOW()) AND YEAR(E.fecha_alta) = YEAR(NOW()) " . $and ." " .
        "ORDER BY codigo";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId,$validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido))
                    {
                        while($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId, $validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido);
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
    
    public function consultarPorcentajesEvidenciasMesActual($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        
        $justificadas = $this->numeroEvidenciasJustificadasMesActual($usuario,$criteriosSeleccion);
        $enviadas =  $this->numeroEvidenciasEnviadasMesActual($usuario,$criteriosSeleccion);
        $pendientes =  $this->numeroEvidenciasPendientesMesActual($usuario,$criteriosSeleccion);
        
        $porcentajes = array();
        array_push($porcentajes,(object)['nombre'=>'Enviadas','valor'=>$enviadas->valor]);
        array_push($porcentajes,(object)['nombre'=>'Pendientes','valor'=>$pendientes->valor]);
        array_push($porcentajes,(object)['nombre'=>'Justificadas','valor'=>$justificadas->valor]);
      
       
        $resultado->valor = $porcentajes;

        return $resultado;
    }
    
    private function getFiltrosUsuario($usuario,$criteriosSeleccion)
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
        }
        return $filtros;
    }
    
    public function consultarEvidenciasJustificacionMesActual($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $filtros = $this->getFiltrosUsuario($usuario, $criteriosSeleccion);
        
        $and = $this->and($filtros);
        
        
        $consulta = "SELECT justificacion_id, J.nombre, count(justificacion_id) valor
            FROM evidencias E
            		INNER JOIN  usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
            		INNER JOIN procedimientos P ON P.id = UP.procedimiento_id
                    INNER JOIN justificaciones J ON J.id = E.justificacion_id
                    INNER JOIN usuarios U ON U.id = UP.usuario_id
            WHERE MONTH(E.fecha_alta) = MONTH(NOW()) ". $and . " ".
            "GROUP BY justificacion_id, J.nombre";
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
    
    public function validarEvidencia(Evidencia $modelo)
    {
        if($modelo->justificacionId=="")
            $modelo->justificacionId=null;
            $resultado = new Resultado();
            $consulta = "UPDATE evidencias
                     SET
                         validada = ?,
                         comentarios_validacion = ?,
                         fecha_modificacion = NOW()
                     WHERE id = ?";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('isi',$modelo->validada, $modelo->comentariosValidacion,$modelo->id))
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
        $where='';
        if($criteriosSeleccion!=null)
        {
            $where = $this->where($filtros);
        }
        $consulta = $this->consultaBase . $where;
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId, $validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId, $validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido);
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
                    if($sentencia->bind_result($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId, $validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId, $validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido);
                            $resultado->valor = $registro;
                        }
                        else
                            $resultado->mensajeError = 'No se encontró ningún resultado.';
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace del resultado';
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

    private function crearRegistro($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId, $validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido)
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
            'administradorApellido' => $administradorApellido
            
            
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
        
        
        return $registro;
    }
}
