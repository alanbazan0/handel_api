<?php
use php\clases\AdministradorArchivos;
use php\clases\JsonMapper;
use php\modelos\Resultado;
use php\clases\Logger;

error_reporting(E_ALL);
ini_set('display_errors', 1);


include '../clases/JsonMapper.php';
include '../clases/AdministradorArchivos.php';
include '../clases/Resultado.php';
include '../clases/Logger.php';

$origin = "*";
if(isset($_SERVER['HTTP_ORIGIN']))
    $origin =$_SERVER['HTTP_ORIGIN'];
header('Access-Control-Allow-Origin: '.$origin);
header('Content-Type: html; charset=UTF-8');
header('Access-Control-Allow-Credentials: true');
   
$resultados = array();
try
{
    $administradorArchivos = new AdministradorArchivos();
    array_push($resultados,$administradorArchivos->eliminarTodo("trash/fotos_inspecciones", true));
    //array_push($resultados,$administradorArchivos->eliminarArchivosAntiguos("diplomas", 60, true));
}
catch(Exception $e)
{
    
}
finally
{
    if($resultados!=null)
    {
        $json = json_encode($resultados, JSON_UNESCAPED_UNICODE);
        if (FALSE === $json)
            echo '{"mensajeError":"' .json_last_error_msg() . '"}';
        else
            echo $json;
            
        Logger::log("log_eliminacion",$json,"logs_eliminacion_automatica/");
    }
}
    