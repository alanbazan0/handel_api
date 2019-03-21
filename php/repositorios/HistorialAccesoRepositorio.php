<?php
namespace php\repositorios;

use php\modelos\Resultado;
use php\interfaces\IHistorialAccesoRepositorio;

require_once("RepositorioBase.php");
include "../interfaces/IHistorialAccesoRepositorio.php";
require_once("../clases/Resultado.php");

class HistorialAccesoRepositorio extends RepositorioBase implements IHistorialAccesoRepositorio
{
    protected $conexion;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    } 
 
    public function insertar($nombreUsuario,$ip)
    {        
        $resultado =  $this->calcularId("id","historial_acceso");
        
        if($resultado->mensajeError=="")
        {
            $id = $resultado->valor;           
            ;
            $consulta = "INSERT INTO historial_acceso(id, nombre_usuario, ip, fecha) " .
                        "VALUE(?, ?, ?, NOW())";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if( $sentencia->bind_param("iss", $id, $nombreUsuario,$ip))
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
     
}

