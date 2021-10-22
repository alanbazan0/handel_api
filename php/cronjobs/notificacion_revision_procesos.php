<?php
use php\clases\AdministradorConexion;

use php\modelos\Resultado;
use php\repositorios\UsuariosProcesosRepositorio;
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
require_once('../clases/Mes.php');
require_once("../clases/Logger.php");
require_once('../repositorios/UsuariosProcesosRepositorio.php');

$origin = "*";
if(isset($_SERVER['HTTP_ORIGIN']))
    $origin =$_SERVER['HTTP_ORIGIN'];
header('Access-Control-Allow-Origin: '.$origin);
header('Content-Type: html; charset=UTF-8');
header('Access-Control-Allow-Credentials: true');
    
$debug = false;
$numeroUsuarios = 3;
$administrador_conexion = new AdministradorConexion();
$resultado = new Resultado();
$conexion=null;
try
{
    $conexion = $administrador_conexion->abrir();
    if($conexion)
    {
        $repositorio = new UsuariosProcesosRepositorio($conexion);
        
        $nombreUsuario= REQUEST("nombreUsuario");
        $enviarA= REQUEST("enviarA");
        $dia = REQUEST("dia");
        $numeroUsuarios = REQUEST("numeroUsuarios");
        
        if($dia==null)
            $dia = date("j");
        
        $diaSemana = REQUEST("diaSemana");
        if($diaSemana==null)
            $diaSemana = date("w");
            
        $mes =  intval(date("m"));
        $ano = intval(date("Y"));
        $ultimoDia = Mes::getUltimoDia($mes, $ano);
        
       
        
       
         if($dia == 1)
             $resultado= $repositorio->enviarNotificacionRevision1($nombreUsuario,$enviarA,$numeroUsuarios);
         else if($diaSemana == DiaSemana::VIERNES && $dia != $ultimoDia)
             $resultado= $repositorio->enviarNotificacionRevision3($nombreUsuario,$enviarA,$numeroUsuarios);
         else if($dia == $ultimoDia)
             $resultado= $repositorio->enviarNotificacionRevision3($nombreUsuario,$enviarA,$numeroUsuarios);
    }
    
}
catch(Exception $e)
{
    $resultado->mensajeError = $e->getMessage();
    Logger::log("log_envio",$resultado->mensajeError,"revision_procesos/");
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
    