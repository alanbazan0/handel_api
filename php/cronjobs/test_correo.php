<?php
use php\clases\AdministradorConexion;

use php\repositorios\UsuariosRepositorio;
use php\modelos\Resultado;
use php\repositorios\EvidenciasRepositorio;
use php\repositorios\UsuariosProcedimientosRepositorio;

error_reporting(E_ALL);
ini_set('display_errors', 1);


include '../clases/JsonMapper.php';
include '../clases/Utilidades.php';
include '../configuracion.php';
include '../clases/AdministradorConexion.php';
include '../clases/AdministradorCorreo.php';
include '../modelos/Usuario.php';
require_once('../clases/TipoUsuario.php');
require_once('../repositorios/UsuariosRepositorio.php');
require_once('../repositorios/EvidenciasRepositorio.php');
require_once('../repositorios/UsuariosProcedimientosRepositorio.php');
//require_once('../reportes/reporte_evidencias.php');

$origin = "*";
if(isset($_SERVER['HTTP_ORIGIN']))
    $origin =$_SERVER['HTTP_ORIGIN'];
header('Access-Control-Allow-Origin: '.$origin);
header('Content-Type: html; charset=UTF-8');
header('Access-Control-Allow-Credentials: true');
    
try
{
    $cabecera = "From:  SAHA <noreply@apps-handel.com>\r\n";
    $cabecera .= "Content-type: text/html; charset=UTF-8\r\n";
    $correoElectronico = "alanbazan@apps-handel.com";   
    $errLevel = error_reporting(E_ALL ^ E_WARNING);
    $resultadoMail = true;
    $resultadoMail= mail($correoElectronico,"Test correo", "Correo de prueba", $cabecera);
    error_reporting($errLevel);
    
    $error = error_get_last();
    
    if ( $error["type"] == E_WARNING)
    {
        echo "No se pudo enviar el correo electrónico a $correoElectronico.  ". htmlspecialchars_decode($error["message"]) ;
    }
    else if($resultadoMail)
    {
        echo "Correo enviado a ".$correoElectronico;
    }
    
}
catch(Exception $e)
{
    
}
finally
{


}
    