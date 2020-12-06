<?php
namespace php\repositorios;

use php\interfaces\ITiposEmpresaRepositorio;
use php\modelos\TipoEmpresa;
use php\modelos\Resultado;
use php\clases\Porcentaje;

include "../interfaces/ITiposEmpresaRepositorio.php";
include "../modelos/TipoEmpresa.php";
include "RepositorioBase.php";
require_once("../clases/Resultado.php");
require_once("../clases/Porcentaje.php");

class TiposEmpresaRepositorio extends RepositorioBase implements ITiposEmpresaRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = " SELECT T.id, T.nombre, T.fecha_alta, T.fecha_modificacion, T.estatus,T.nivel_compromiso, T.implementacion, T.verificacion " .
            " FROM tipos_empresa T";
           
    }
    
    public function insertar(TipoEmpresa $modelo)
    {
        $resultado =  $this->calcularId("id","tipos_empresa");
        if($resultado->mensajeError=="")
        {
            $id = $resultado->valor;
            $consulta = "INSERT INTO tipos_empresa(id, nombre, fecha_alta, fecha_modificacion, estatus, nivel_compromiso, implementacion, verificacion) " .
                "VALUE(?, ?, NOW(), NOW(), ?, ?, ?, ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if( $sentencia->bind_param("isisss", $id, $modelo->nombre, $modelo->estatus, $modelo->nivelCompromiso, $modelo->implementacion, $modelo->verificacion))
                {
                    if(!$sentencia->execute())
                        $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
                else
                    $resultado->mensajeError = "Falló el enlace de parámetros";
            }
            else
                $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
        }
        return $resultado;
    }
    
    public function actualizar(TipoEmpresa $modelo)
    {
        $resultado = new Resultado();
        $consulta = " UPDATE tipos_empresa 
            SET nombre = ?, 
              estatus = ?, 
             nivel_compromiso = ?,
            implementacion = ?,
            verificacion = ?,
              fecha_modificacion= NOW() 
            WHERE id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("sisssi", $modelo->nombre, $modelo->estatus,$modelo->nivelCompromiso, $modelo->implementacion, $modelo->verificacion,$modelo->id ))
            {
                if($sentencia->execute())
                {
                    $resultado->valor=true;
                }
                else
                    $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else  $resultado->mensajeError = "Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
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
            if(isset($criteriosSeleccion->nombre))
                array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'T','campo'=>'nombre','valor'=>$criteriosSeleccion->nombre]);
                $where = $this->where($filtros);
        }
        $consulta = $this->consultaBase .
        $where . " order by nombre";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombre, $fechaAlta, $fechaModificacion, $estatus, $nivelCompromiso, $implementacion, $verificacion))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre,$fechaAlta, $fechaModificacion, $estatus, $nivelCompromiso, $implementacion, $verificacion);
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = "Falló el enlace del resultado.";
                }
                else
                    $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = "Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
            
            return $resultado;
    }
    
    public function consultarPorLlaves($llaves)
    {
        $resultado = new Resultado();
        $consulta = $this->consultaBase .
        " WHERE T.id  = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$llaves->id))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombre, $fechaAlta, $fechaModificacion, $estatus, $nivelCompromiso, $implementacion, $verificacion))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre, $fechaAlta, $fechaModificacion, $estatus, $nivelCompromiso, $implementacion, $verificacion);
                            $resultado->valor = $registro;
                        }
                        else
                            $resultado->mensajeError = "No se encontró ningún resultado.";
                    }
                    else
                        $resultado->mensajeError = "Falló el enlace del resultado";
                }
                else
                    $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = "Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            return $resultado;
    }
    
    private function crearRegistro($id, $nombre, $fechaAlta, $fechaModificacion, $estatus, $nivelCompromiso, $implementacion, $verificacion)
    {
        $registro= (object) [
            'id' =>  $id,
            'nombre' => $nombre,            
            'fechaAlta' => $fechaAlta,
            'fechaModificacion' => $fechaModificacion,
            'estatus' => $estatus,
            'nivelCompromiso' => $nivelCompromiso,
            'implementacion' => $implementacion,
            'verificacion' => $verificacion
        ];
        
        Porcentaje::formatearPorcentaje($registro, "nivelCompromiso");
        Porcentaje::formatearPorcentaje($registro, "implementacion");
        Porcentaje::formatearPorcentaje($registro, "verificacion");
        
        return $registro;
    }
    
   
    
    public function eliminar($llaves)
    {
        $resultado = new Resultado();
        $consulta = " DELETE FROM tipos_empresa "
            . "  WHERE id  = ? ";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param("i",$llaves->id))
                {
                    if($sentencia->execute())
                    {
                        $resultado->valor = $llaves->id;
                    }
                    else
                    {
                        $resultado->codigoError = $this->conexion->errno;
                        $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                    }
                }
                else
                    $resultado->mensajeError = "Falló el enlace de parámetros";
            }
            else
            {
                $resultado->codigoError = $this->conexion->errno;
                $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
              
            }
                
           return $resultado;
    }
    
}

