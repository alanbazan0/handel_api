<?php
namespace php\repositorios;

use php\interfaces\IPaisesRepositorio;
use php\modelos\Pais;
use php\modelos\Resultado;
use php\clases\Porcentaje;

include "../interfaces/IPaisesRepositorio.php";
include "../modelos/Pais.php";
include "RepositorioBase.php";
require_once '../clases/Porcentaje.php';
require_once("../clases/Resultado.php");

class PaisesRepositorio extends RepositorioBase implements IPaisesRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = " SELECT P.id, P.nombre, P.fecha_alta, P.fecha_modificacion, P.estatus, P.nivel_compromiso, P.implementacion, P.verificacion " .
            " FROM paises P";
           
    }
    
    public function insertar(Pais $modelo)
    {
        $resultado =  $this->calcularId("id","paises");
        if($resultado->mensajeError=="")
        {
            $id = $resultado->valor;
            $consulta = "INSERT INTO paises(id, nombre, fecha_alta, fecha_modificacion, estatus, nivel_compromiso, implementacion, verificacion) " .
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
    
    public function actualizar(Pais $modelo)
    {
        $resultado = new Resultado();
        $consulta = " UPDATE paises 
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
                array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'P','campo'=>'nombre','valor'=>$criteriosSeleccion->nombre]);
                $where = $this->where($filtros);
        }
        $consulta = $this->consultaBase .
        $where;
        
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
        " WHERE P.id  = ?";
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
        $consulta = " DELETE FROM paises "
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

