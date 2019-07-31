<?php
namespace php\repositorios;

use php\interfaces\IEvidenciasRepositorio;
use php\modelos\Evidencia;
use php\modelos\Resultado;

include '../interfaces/IEvidenciasRepositorio.php';
include '../modelos/Evidencia.php';
include 'RepositorioBase.php';
require_once('../clases/Resultado.php');

class EvidenciasRepositorio extends RepositorioBase implements IEvidenciasRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = "SELECT E.id, usuario_procedimiento_id, realizo_actividad, justificacion_id, comentarios, IFNULL(DATE_FORMAT(E.fecha_alta,'%d/%m/%Y %H:%i:%s'),'') as fecha, P.nombre, nombre_archivo, P.codigo, J.nombre,
                                (SELECT count(C.id) FROM evidencias_comentarios C WHERE C.evidencia_id = E.id) numeroComentarios   
                               FROM evidencias E
                            		INNER JOIN  usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
                            		INNER JOIN procedimientos P ON P.id = UP.procedimiento_id
                                    LEFT JOIN justificaciones J ON J.id = E.justificacion_id
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
    
    public function consultarEvidenciasCumplidasMesActual($usuarioId)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $consulta =  $this->consultaBase .
        " WHERE UP.usuario_id = ? AND MONTH(E.fecha_alta) = MONTH(NOW())" .
        "ORDER BY codigo";
//         $consulta = "SELECT E.id, usuario_procedimiento_id, codigo, P.nombre, IFNULL(DATE_FORMAT(fecha ,'%d/%m/%Y %H:%i:%s'),'')fecha, justificacion_id
//             FROM evidencias E
//             		INNER JOIN  usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
//             		INNER JOIN procedimientos P ON P.id = UP.procedimiento_id
//             WHERE UP.usuario_id = ? AND MONTH(fecha) = MONTH(NOW())
//             ORDER BY codigo";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$usuarioId))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios))
                    {
                        while($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios);
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
    
    public function consultarEvidenciasJustificacionMesActual($usuarioId)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $consulta = "SELECT justificacion_id, J.nombre, count(justificacion_id) valor
            FROM evidencias E
            		INNER JOIN  usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
            		INNER JOIN procedimientos P ON P.id = UP.procedimiento_id
                    INNER JOIN justificaciones J ON J.id = E.justificacion_id
            WHERE UP.usuario_id = ? AND MONTH(E.fecha_alta) = MONTH(NOW())
            GROUP BY justificacion_id, J.nombre";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$usuarioId))
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
                    if($sentencia->bind_result($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios);
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
                    if($sentencia->bind_result($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios);
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

    private function crearRegistro($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios)
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
            'numeroComentarios' => $numeroComentarios
        ];
        return $registro;
    }
}
