<?php
namespace php\repositorios;

use php\interfaces\ITiposSocioComercialRepositorio;
use php\modelos\TipoSocioComercial;
use php\modelos\Resultado;

include '../interfaces/ITiposSocioComercialRepositorio.php';
include '../modelos/TipoSocioComercial.php';
include 'RepositorioBase.php';
require_once('../clases/Resultado.php');

class TiposSocioComercialRepositorio extends RepositorioBase implements ITiposSocioComercialRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = "SELECT id, RTRIM(nombre) as nombre, estatus, IFNULL(DATE_FORMAT(fecha_alta,'%d/%m/%Y %H:%i:%s'),'') as fecha_alta, IFNULL(DATE_FORMAT(fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'') as fecha_modificacion FROM tipos_socio_comercial TSC";
    }

    public function insertar(TipoSocioComercial $modelo)
    {
        $resultado = $this->calcularId('id','tipos_socio_comercial');
        if($resultado->mensajeError=='')
        {
            $id = $resultado->valor;
            $consulta = "INSERT INTO tipos_socio_comercial(id, nombre, estatus, fecha_alta, fecha_modificacion)VALUES(?, ?, ?, NOW(), NOW())";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('isi', $id, $modelo->nombre, $modelo->estatus))
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

    public function actualizar(TipoSocioComercial $modelo)
    {
        $resultado = new Resultado();
        $consulta = "UPDATE tipos_socio_comercial
                     SET 
                         nombre = ?,
                         estatus = ?,
                         fecha_modificacion = NOW()
                     WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('sii',$modelo->nombre, $modelo->estatus, $modelo->id ))
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
            if(isset($criteriosSeleccion->nombre) && $criteriosSeleccion->nombre!="")
                array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'TSC','campo'=>'nombre','valor'=>$criteriosSeleccion->nombre]);
            $where = $this->where($filtros);
        }
        $consulta = $this->consultaBase . $where.
        "\nORDER BY nombre";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre, $estatus, $fechaAlta, $fechaModificacion))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre, $estatus, $fechaAlta, $fechaModificacion);
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
                    if($sentencia->bind_result($id, $nombre, $estatus, $fechaAlta, $fechaModificacion))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre, $estatus, $fechaAlta, $fechaModificacion);
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
        $consulta = "DELETE FROM tipos_socio_comercial WHERE id = ?";
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

    private function crearRegistro($id, $nombre, $estatus, $fechaAlta, $fechaModificacion)
    {
        $registro= (object) 
        [
            'id' => $id,
            'nombre' => $nombre,
            'estatus' => $estatus,
            'fechaAlta' => $fechaAlta,
            'fechaModificacion' => $fechaModificacion
        ];
        return $registro;
    }
}
