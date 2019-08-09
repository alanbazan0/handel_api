<?php
namespace php\repositorios;

use php\modelos\Resultado;

include "../repositorios/RepositorioBase.php";
require_once("../clases/Resultado.php");

class CamposRepositorio extends RepositorioBase
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
//         $this->consultaBase = " SELECT P.id, P.nombre, IFNULL(DATE_FORMAT(P.fecha_alta,'%d/%m/%Y %H:%i:%s'),'')fecha_alta, IFNULL(DATE_FORMAT(P.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'')fecha_modificacion " .
//             " FROM proyectos P";
           
    }
    
   
    
    public function consultarCampos($tabla)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $consulta = "select COLUMN_NAME campo,DATA_TYPE tipo," . 
		    " COLLATION_NAME intercalacion, RTRIM(IS_NULLABLE) nulo , " .
		    " COLUMN_KEY llave, " .
		    " (CASE When CHARACTER_MAXIMUM_LENGTH > 0 THEN convert(CHARACTER_MAXIMUM_LENGTH,char) ".
          " ELSE concat(convert(NUMERIC_PRECISION,char),',',convert(NUMERIC_SCALE,char)) End) tam, RTRIM(COLUMN_COMMENT) comentario FROM information_schema.columns " .
	   		" where TABLE_NAME = ?";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("s",$tabla))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($nombre, $tipo, $intercalacion, $nulo, $llave, $longitud,$comentario))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($nombre, $tipo, $intercalacion, $nulo, $llave, $longitud,$comentario);
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
                    if ($sentencia->bind_result($id, $nombre, $fechaAlta, $fechaModificacion))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre, $fechaAlta, $fechaModificacion);
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
    
    private function crearRegistro($nombre, $tipoDato, $intercalacion, $nulo, $llave, $longitud,$comentario)
    {
        $registro= (object) [
            'nombre' =>  $nombre,
            'tipoDato' => $tipoDato,            
            'intercalacion' => $intercalacion,
            'nulo' => $nulo,
            'llave' => $llave,
            'longitud' => $longitud,
            'comentario' => $comentario
        ];
        return $registro;
    }
    
   
   
    
}

