<?php
use php\clases\AdministradorConexion;

use php\repositorios\UsuariosRepositorio;
use php\modelos\Resultado;
use php\repositorios\UsuariosProcedimientosRepositorio;
use php\repositorios\MinutasRepositorio;
use php\repositorios\FrasesRepositorio;
use php\repositorios\TareasComentariosRepositorio;
use php\repositorios\AuditoriasRepositorio;
use php\clases\Logger;

error_reporting(E_ALL);
ini_set('display_errors', 1);


include '../clases/JsonMapper.php';
include '../clases/Utilidades.php';
include '../configuracion.php';
include '../clases/AdministradorConexion.php';
include '../clases/AdministradorCorreo.php';
include '../modelos/Usuario.php';
require_once('../clases/TipoUsuario.php');
require_once('../clases/DiaSemana.php');
require_once("../clases/Logger.php");
require_once('../repositorios/UsuariosRepositorio.php');
require_once('../repositorios/AuditoriasRepositorio.php');
require_once('../repositorios/MinutasRepositorio.php');
require_once('../repositorios/TareasComentariosRepositorio.php');
require_once('../repositorios/FrasesRepositorio.php');
//require_once('../reportes/reporte_evidencias.php');

$origin = "*";
if(isset($_SERVER['HTTP_ORIGIN']))
    $origin =$_SERVER['HTTP_ORIGIN'];
header('Access-Control-Allow-Origin: '.$origin);
header('Content-Type: html; charset=UTF-8');
header('Access-Control-Allow-Credentials: true');
    
$debug = false;
$imprimirMensaje = true;
$numeroUsuarios = 3;
$tiempoEspera = 10;
$administrador_conexion = new AdministradorConexion();
$resultado = new Resultado();
$conexion=null;
try
{
    $conexion = $administrador_conexion->abrir();
    if($conexion)
    {
        $auditoriasRepositorio = new AuditoriasRepositorio($conexion);
        
        $nombreUsuario= REQUEST("nombreUsuario");
        $enviarA= REQUEST("enviarA");
        $dia = REQUEST("dia");
        $numeroUsuarios = REQUEST("numeroUsuarios");
        
        if($dia==null)
            $dia = date("j");
        
         if($dia == 1)
         {
            //$asunto = getAsunto($dia);
             $resultado= $auditoriasRepositorio->enviarNotificacionEvidenciasMensualUsuarios($nombreUsuario,$enviarA,$numeroUsuarios);
//             for ($i = 0; $i < count($usuarios); $i++)
//             {
//                 $usuario = $usuarios[$i];
//                 $resultado = 
//                 if($resultado->correcto())
//                     mensajeLog("log_envio_evidencias_pendientes","$i Correo enviado a ".$usuario->nombreUsuario);
//                 sleep($tiempoEspera);
//             }
//             mensajeLog("log_envio_evidencias_pendientes","Termimado!");
        }
    }
    
}
catch(Exception $e)
{
    $resultado->mensajeError = $e->getMessage();
    Logger::log("log_envio",$resultado->mensajeError,"envios_mensual_sivah/");
}
finally
{
    $administrador_conexion->cerrar($conexion);
   
}


// function guardarEnvio($usuario, $asunto, $mensaje)
// {
//     $carpeta = "envios_evidencias_pendientes/".date("j.n.Y")."/";
//     if(!file_exists($carpeta))
//         @mkdir($carpeta);
    
//  //       $archivo = $carpeta . $usuario->nombreUsuario
//     file_put_contents($carpeta.$usuario->nombreUsuario.".html",  $mensaje , FILE_TEXT);
    
// }
    