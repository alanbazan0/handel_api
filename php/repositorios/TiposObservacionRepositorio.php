<?php
namespace php\repositorios;

use php\interfaces\ITiposObservacionRepositorio;
use php\modelos\TipoObservacion;
use php\modelos\Resultado;

require_once("../interfaces/ITiposObservacionRepositorio.php");
require_once( "../modelos/TipoObservacion.php");
require_once( "../clases/TipoUsuario.php");
require_once( "RepositorioBase.php");
require_once("../clases/Resultado.php");

class TiposObservacionRepositorio extends RepositorioBase implements ITiposObservacionRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = " SELECT T.id, T.descripcion " .
            " FROM tipos_observacion T ";
    }
    
    public function insertar(TipoObservacion $modelo)
    {
        $resultado =  $this->calcularId("id","tipos_observacion");
        if($resultado->mensajeError=="")
        {
            $id = $resultado->valor;
            $consulta = "INSERT INTO tipos_observacion(id, descripcion) " .
                "VALUE(?, ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if( $sentencia->bind_param("is", $id, $modelo->descripcion))
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
    
    public function actualizar(TipoObservacion $modelo)
    {
        $resultado = new Resultado();
        $consulta = " UPDATE tipos_observacion " .
            "SET descripcion = ? " .
            "WHERE id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("si", $modelo->descripcion,$modelo->id ))
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
    
    public function consultar($criteriosSeleccion,$opcional)
    {
       
        $resultado = new Resultado();
        $registros = array();
        $filtros = array();
        $where="";
//         if($criteriosSeleccion!=null)
//         {
//             if(isset($criteriosSeleccion->nombre) && $criteriosSeleccion->nombre!="")
//                 array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'A','campo'=>'nombre','valor'=>$criteriosSeleccion->nombre]);
//             if(isset($criteriosSeleccion->empresaId))
//             {
//                 if($criteriosSeleccion->empresaId!="" && $criteriosSeleccion->empresaId!=null)
//                     array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'A','campo'=>'empresa_id','valor'=>$criteriosSeleccion->empresaId]);
//             }
//             if(isset($criteriosSeleccion->sedeId))
//             {
//                 if($criteriosSeleccion->sedeId!="" && $criteriosSeleccion->sedeId!=null)
//                     array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'A','campo'=>'sede_id','valor'=>$criteriosSeleccion->sedeId]);
//             }
//              $where = $this->where($filtros);
//         }
        $consulta = $this->consultaBase;
       
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $descripcion))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $descripcion);
                            array_push($registros,$registro);
                        }
                        if($opcional=="true")
                        {
                            //if($usuario->tipoUsuarioId == \TipoUsuario::ADMINISTRADOR)
                            //{
                            $registro = $this->crearRegistro("", "Todos");
                            array_unshift($registros, $registro);
                            //}
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
                    if ($sentencia->bind_result($id, $descripcion))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $descripcion);
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
    
    private function crearRegistro($id, $descripcion)
    {
        $registro= (object) [
            'id' =>  $id,
            'nombre' => $descripcion
        ];
        return $registro;
    }
    
    public function eliminar($llaves)
    {
        $resultado = new Resultado();
        $consulta = " DELETE FROM tipos_observacion "
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

