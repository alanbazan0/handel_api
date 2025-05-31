<?php
namespace php\repositorios;

use Exception;
use php\modelos\Resultado;

require_once("../clases/Resultado.php");

class ArchivosRepositorio 
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
           
    }
    
    public function consultar($criteriosSeleccion)
    {
        $resultado = new Resultado();
        $carpeta = "";
        if($criteriosSeleccion!=null)
        {
            $carpeta = $this->getCarpeta($criteriosSeleccion->archivos);
            if($carpeta!="")
            {
                $archivos = $this->getArchivos($carpeta,$criteriosSeleccion,true);
               // print_r($archivos);
                $resultado->valor = $archivos;
            }
        }
        
        
        return $resultado;
    }
    
    public function eliminarArchivos($criteriosSeleccion)
    {
        $resultado = new Resultado();
        $carpeta = "";
        if($criteriosSeleccion!=null)
        {
            $carpeta = $this->getCarpeta($criteriosSeleccion->archivos);
            if($carpeta!="")
            {
               $archivos = $this->getArchivos($carpeta,$criteriosSeleccion,true);
               if(count($archivos) == $criteriosSeleccion->numeroArchivos)
               {
                   try 
                   {
                        for($i = 0; $i < count($archivos); $i++)
                        {
                            $archivo = $archivos[$i];
                            unlink($archivo->path);
                        }
                        $resultado->valor =  count($archivos);
                      
                   } 
                   catch (Exception $e) 
                   {
                       $resultado->mensajeError = $e->getMessage();
                   }
               }
            }
        }
        return $resultado;
    }
    
    private function getArchivos($carpeta,$criteriosSeleccion,$recursive)
    {
        //$archivos = scandir($carpeta, SCANDIR_SORT_DESCENDING);
        $archivos = preg_grep('/^([^.])/', scandir($carpeta,SCANDIR_SORT_DESCENDING));
         
        $listaArchivos = array();
        foreach ($archivos as $archivo)
        {
            $filePath = $carpeta . '/' . $archivo;
            if (is_file($filePath))
            {
                $mes = date ("m", filemtime($filePath));
                $ano = date ("Y", filemtime($filePath));
                if(($ano == $criteriosSeleccion->ano && $mes == $criteriosSeleccion->mes) || ($ano == $criteriosSeleccion->ano && $criteriosSeleccion->mes == "0"))
                {
                    $registro = (object)[
                        "nombre" => $archivo,
                        "tamano" => filesize($filePath),
                        "fecha" => date ("d/m/Y H:i:s", filemtime($filePath)),
                        "path" => $filePath
                    ];
                    array_push($listaArchivos, $registro);
                }
            }
            else  if(is_dir($filePath) && $recursive)
            {
                //echo "PATH",$filePath;
                $archivosCarpeta = $this->getArchivos($filePath, $criteriosSeleccion,false);
                $listaArchivos = array_merge($listaArchivos,$archivosCarpeta);
            }
           
            
        }
       // var_dump($archivos);
        return $listaArchivos;
    }
   
    private function getCarpeta($archivos)
    {
        $carpeta = "";
        switch($archivos)
        {
            case "evidencias_saha":
                $carpeta = "../../php/archivos_evidencias";
            break;
            case "reportes_evidencia":
                $carpeta = "../../php/reportes_evidencia";
            break;
            case "reportes_auditoria":
                $carpeta = "../../php/reportes_auditoria";
            break;
            case "reportes_auditoria":
                $carpeta = "../../php/reportes_auditoria";
            break;
            case "fotos_inspecciones":
                $carpeta = "../../php/fotos_inspecciones";
            break;
            case "evidencias_sivah":
                $carpeta = "../../php/archivos_avances";
            break;
        }
        return $carpeta;
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

