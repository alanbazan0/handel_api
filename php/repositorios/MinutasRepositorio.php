<?php
namespace php\repositorios;

use php\interfaces\IMinutasRepositorio;
use php\modelos\Minuta;
use php\modelos\Resultado;

include '../interfaces/IMinutasRepositorio.php';
include '../modelos/Minuta.php';
include 'RepositorioBase.php';
require_once('../clases/Resultado.php');

class MinutasRepositorio extends RepositorioBase implements IMinutasRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = "SELECT id, titulo, IFNULL(DATE_FORMAT(fecha_alta,'%d/%m/%Y %H:%i:%s'),'') as fecha_alta, usuario_id, terminada, IFNULL(DATE_FORMAT(fecha_termino,'%d/%m/%Y %H:%i:%s'),'') as fecha_termino, IFNULL(DATE_FORMAT(fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'') as fecha_modificacion 
                                FROM minutas M";
    }

    public function insertar(Minuta $modelo)
    {
        $resultado = $this->calcularId('id','minutas');
        if($resultado->mensajeError=='')
        {
            $id = $resultado->valor;
            $consulta = "INSERT INTO minutas(id, titulo, fecha_alta, usuario_id, terminada, fecha_termino)VALUES(?, ?, ?, ?, ?, ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('issiis', $id, $modelo->titulo, $modelo->fechaAlta, $modelo->usuarioId, $modelo->terminada, $modelo->fechaTermino))
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

    public function actualizar(Minuta $modelo)
    {
        $resultado = new Resultado();
        $consulta = "UPDATE minutas
                     SET 
                         titulo = ?,
                         fecha_alta = ?,
                         usuario_id = ?,
                         terminada = ?,
                         fecha_termino = ?
                     WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('ssiisi',$modelo->titulo, $modelo->fechaAlta, $modelo->usuarioId, $modelo->terminada, $modelo->fechaTermino ,$modelo->id ))
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
        $consulta = $this->consultaBase . $where . " ORDER BY UNIX_TIMESTAMP(M.fecha_alta) desc";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $titulo, $fechaAlta, $usuarioId, $terminada, $fechaTermino, $fechaModificacion))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $titulo, $fechaAlta, $usuarioId, $terminada, $fechaTermino,$fechaModificacion);
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
            if($sentencia->bind_param('i',$llaves->id))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $titulo, $fechaAlta, $usuarioId, $terminada, $fechaTermino,$fechaModificacion))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $titulo, $fechaAlta, $usuarioId, $terminada, $fechaTermino, $fechaModificacion);
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
        $consulta = "DELETE FROM minutas WHERE id = ?";
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

    private function crearRegistro($id, $titulo, $fechaAlta, $usuarioId, $terminada, $fechaTermino,$fechaModificacion)
    {
        $registro= (object) 
        [
            'id' => $id,
            'titulo' => $titulo,
            'fechaAlta' => $fechaAlta,
            'usuarioId' => $usuarioId,
            'terminada' => $terminada,
            'fechaTermino' => $fechaTermino,
            'fechaModificacion' => $fechaModificacion
        ];
        return $registro;
    }
}
