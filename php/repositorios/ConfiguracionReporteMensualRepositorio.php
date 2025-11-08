<?php
namespace php\repositorios;

use php\interfaces\IConfiguracionReporteMensualRepositorio;
use php\modelos\ConfiguracionReporteMensual;
use php\modelos\Resultado;

include '../interfaces/IConfiguracionReporteMensualRepositorio.php';
include '../modelos/ConfiguracionReporteMensual.php';
require_once('RepositorioBase.php');
require_once('../clases/Resultado.php');

class ConfiguracionReporteMensualRepositorio extends RepositorioBase implements IConfiguracionReporteMensualRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = "SELECT id, imagen_simulacros, criterios_seleccion FROM configuracion_reporte_mensual";
    }

    public function insertar(ConfiguracionReporteMensual $modelo)
    {
        $resultado = $this->calcularId('id','configuracion_reporte_mensual');
        if($resultado->mensajeError=='')
        {
            $id = $resultado->valor;
            $consulta = "INSERT INTO configuracion_reporte_mensual(id, imagen_simulacros, criterios_seleccion)VALUES(?, ?, ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('iss', $id, $modelo->imagenSimulacros, $modelo->criteriosSeleccion))
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

    public function actualizar(ConfiguracionReporteMensual $modelo)
    {
        $resultado = new Resultado();
        $consulta = "UPDATE configuracion_reporte_mensual
                     SET 
                         imagen_simulacros = ?,
                         criterios_seleccion = ?
                     WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('ssi',$modelo->imagenSimulacros, $modelo->criteriosSeleccion ,$modelo->id ))
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

    public function consultar($criteriosSeleccionn)
    {
        $resultado = new Resultado();
        $registros = array();
        $filtros = array();
        $where='';
        if($criteriosSeleccionn!=null)
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
                    if($sentencia->bind_result($id, $imagenSimulacros, $criteriosSeleccion))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $imagenSimulacros, $criteriosSeleccion);
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
                    if($sentencia->bind_result($id, $imagenSimulacros, $criteriosSeleccion))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $imagenSimulacros, $criteriosSeleccion);
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
        $consulta = "DELETE FROM configuracion_reporte_mensual WHERE id = ?";
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

    private function crearRegistro($id, $imagenSimulacros, $criteriosSeleccion)
    {
        $registro= (object) 
        [
            'id' => $id,
            'imagenSimulacros' => $imagenSimulacros,
            'criteriosSeleccion' => $criteriosSeleccion
        ];
        return $registro;
    }
}
