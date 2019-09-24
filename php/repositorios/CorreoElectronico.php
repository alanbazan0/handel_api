<?php
use php\clases\AdministradorCorreo;
use php\modelos\Resultado;

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../clases/JsonMapper.php';
include '../clases/Utilidades.php';
include '../clases/Resultado.php';
include '../clases/AdministradorCorreo.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');

$resultado = new Resultado();
try
{
    $accion = REQUEST('accion');
    
    switch ($accion)
    {        
        case 'enviar':   
            $nombre = REQUEST('nombre');
            $correoElectronico = REQUEST('correoElectronico');
            $telefono = REQUEST('telefono');
            $mensaje = REQUEST('mensaje');
            $adminstradorCorreo = new AdministradorCorreo();
            $resultado = $adminstradorCorreo->enviarContacto($nombre,$correoElectronico, $telefono, $mensaje);
          
        break;
       
        default:
            $resultado->mensajeError = "Acción no válida";
        break;
    }
        
}
catch(Exception $e)
{
    $resultado->MensajeError = $e->getMessage();
}
finally
{
    if($resultado!=null)
        echo json_encode($resultado);
}


