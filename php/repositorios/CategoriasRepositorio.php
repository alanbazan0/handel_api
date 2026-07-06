<?php
namespace php\repositorios;

use php\interfaces\ICategoriasRepositorio;
use php\modelos\Categoria;
use php\modelos\Resultado;

include "../interfaces/ICategoriasRepositorio.php";
include "../modelos/Categoria.php";
include "RepositorioBase.php";
require_once("../clases/Resultado.php");

class CategoriasRepositorio extends RepositorioBase implements ICategoriasRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = " SELECT C.id, C.nombre, C.fecha_alta, C.fecha_modificacion, C.estatus " .
            " FROM categorias C";
           
    }
    
    public function insertar(Categoria $modelo)
    {
        $resultado =  $this->calcularId("id","catetorias");
        if($resultado->mensajeError=="")
        {
            $id = $resultado->valor;
            $consulta = "INSERT INTO categorias(id, nombre, fecha_alta, fecha_modificacion, estatus) " .
                "VALUE(?, ?, NOW(), NOW(), ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if( $sentencia->bind_param("isi", $id, $modelo->nombre, $modelo->estatus))
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
    
    public function actualizar(Categoria $modelo)
    {
        $resultado = new Resultado();
        $consulta = " UPDATE categorias " .
            "SET nombre = ?, " .            
            "  estatus = ?, " .
            "  fecha_modificacion= NOW() " .
            "WHERE id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("sii", $modelo->nombre, $modelo->estatus,$modelo->id ))
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
                array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'C','campo'=>'nombre','valor'=>$criteriosSeleccion->nombre]);
                $where = $this->where($filtros);
        }
        $consulta = $this->consultaBase .
        $where . " ORDER BY nombre";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombre, $fechaAlta, $fechaModificacion, $estatus))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre,$fechaAlta, $fechaModificacion, $estatus);
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
        " WHERE C.id  = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$llaves->id))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombre, $fechaAlta, $fechaModificacion, $estatus))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre, $fechaAlta, $fechaModificacion, $estatus);
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
    
    private function crearRegistro($id, $nombre, $fechaAlta, $fechaModificacion, $estatus)
    {
        $registro= (object) [
            'id' =>  $id,
            'nombre' => $nombre,            
            'fechaAlta' => $fechaAlta,
            'fechaModificacion' => $fechaModificacion,
            'estatus' => $estatus
        ];
        return $registro;
    }
    
   
    
    public function eliminar($llaves)
    {
        $resultado = new Resultado();
        $consulta = " DELETE FROM categorias "
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

