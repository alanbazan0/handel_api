<?php
namespace php\repositorios;

use php\interfaces\ITiposUsuarioRepositorio;
use php\modelos\Resultado;

include "../interfaces/ITiposUsuarioRepositorio.php";
include "../clases/TipoUsuario.php";
include "RepositorioBase.php";
require_once("../clases/Resultado.php");

class TiposUsuarioRepositorio extends RepositorioBase implements ITiposUsuarioRepositorio
{
    protected $conexion;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    } 
 
    public function consultar($usuario)
    {     
        $resultado = new Resultado();
        $registros = array();     
        if($usuario->tipoUsuarioId == \TipoUsuario::ADMINISTRADOR)
        {
            $consulta = " SELECT id, nombre " .                  
                    " FROM tipos_usuario order by orden";       
        }
        if($usuario->tipoUsuarioId == \TipoUsuario::COORDINADOR)
        {
            $consulta = " SELECT id, nombre " .
                " FROM tipos_usuario  WHERE id IN(2, 3, 4) order by orden";   
        }
        if($usuario->tipoUsuarioId == \TipoUsuario::SUPERVISOR)
        {
            $consulta = " SELECT id, nombre " .
                " FROM tipos_usuario WHERE id IN(3,4) order by orden";
        }
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->execute())
            {                
                if ($sentencia->bind_result($id, $nombre))
                {                    
                    while($row = $sentencia->fetch())
                    {
                        $registro = (object) [
                            'id' =>  $id,
                            'nombre' => $nombre                         
                        ];  
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
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;        
        
       
        return $resultado;     
    }    

    
}

