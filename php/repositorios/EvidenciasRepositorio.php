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
        $this->consultaBase = "SELECT id, RTRIM(usuario_procedimiento_id) as usuario_procedimiento_id, realizo_actividad, justificacion_id, comentarios, IFNULL(DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s'),'') as fecha FROM evidencias";
    }

    public function insertar(Evidencia $modelo)
    {
        if($modelo->justificacionId=="")
            $modelo->justificacionId=null;
        $resultado = $this->calcularId('id','evidencias');
        if($resultado->mensajeError=='')
        {
            $id = $resultado->valor;
            $consulta = "INSERT INTO evidencias(id, usuario_procedimiento_id, realizo_actividad, justificacion_id, comentarios, fecha)VALUES(?, ?, ?, ?, ?, NOW())";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('ssiis', $id, $modelo->usuarioProcedimientoId, $modelo->realizoActividad, $modelo->justificacionId, $modelo->comentarios))
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

    public function actualizar(Evidencia $modelo)
    {
        $resultado = new Resultado();
        $consulta = "UPDATE evidencias
                     SET 
                         usuario_procedimiento_id = ?,
                         realizo_actividad = ?,
                         justificacion_id = ?,
                         comentarios = ?,
                         fecha = ?
                     WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('siisss',$modelo->usuarioProcedimientoId, $modelo->realizoActividad, $modelo->justificacionId, $modelo->comentarios, $modelo->fecha ,$modelo->id ))
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
                    if($sentencia->bind_result($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha);
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
        ' WHERE id  = ?';
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('s',$llaves->id))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha);
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

    private function crearRegistro($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha)
    {
        $registro= (object) 
        [
            'id' => $id,
            'usuarioProcedimientoId' => $usuarioProcedimientoId,
            'realizoActividad' => $realizoActividad,
            'justificacionId' => $justificacionId,
            'comentarios' => $comentarios,
            'fecha' => $fecha
        ];
        return $registro;
    }
}
