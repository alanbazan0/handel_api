<?php
use php\clases\AdministradorConexion;

use php\modelos\Resultado;
use php\repositorios\UsuariosProcesosRepositorio;
use php\clases\Logger;
use php\repositorios\ProcesosRevisadosRepositorio;

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
require_once('../repositorios/ProcesosRevisadosRepositorio.php');

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
$tiempoEspera = 10;
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
      
            
        $mes =  intval(date("m"));
        $ano = intval(date("Y"));
        $ultimoDia = Mes::getUltimoDia($mes, $ano);
      
         if($dia == $ultimoDia)
         {
             $resultado = $repositorio->consultarUsuariosProcesosPendientes($nombreUsuario,$numeroUsuarios);
             if($resultado->correcto())
             {
                 $usuarios = $resultado->valor;
                 for ($i = 0; $i < count($usuarios); $i++)
                 {
                     $usuario = $usuarios[$i];
                     if(isset($enviarA) && $enviarA!="")
                         $usuario->nombreUsuario = $enviarA;
                 }
                 if($numeroUsuarios!=0)
                     $usuarios = array_slice($usuarios,0,$numeroUsuarios);
                     
                     $procesosRevisadosRepositorio = new ProcesosRevisadosRepositorio($conexion);
                     //   var_dump($usuarios);
                     for ($i = 0; $i < count($usuarios); $i++)
                     {
                         $usuario = $usuarios[$i];
                         $procesos = array();
                         if($usuario->mesRevision!="")
                         {
                             $revisar = false;
                             
                             if($usuario->mesRevision<12)
                             {
                                if($usuario->mesRevision + 1 == $mes)
                                     $revisar = true;
                             }
                             else 
                             {
                                 if($mes = 1)
                                     $revisar = true;
                             }
                             
                             if($revisar)
                             {
                                 $criteriosSeleccion = (object)["ano" => $ano];
                                 $tipoUsuarioId = $usuario->tipoUsuarioId;
                                 $usuario->tipoUsuarioId = \TipoUsuario::USUARIO;
                                 $resultado = $repositorio->consultarProcesosPendientes($usuario, $criteriosSeleccion);
                                 $usuario->tipoUsuarioId = $tipoUsuarioId;
                                
                                 if($resultado->correcto())
                                 {
                                     $procesos = $resultado->valor;
                                     for($j = 0; $j < count($procesos); $j++)
                                     {
                                         $proceso = $procesos[$j];
                                         //var_dump($proceso);
                                         $resultado = $procesosRevisadosRepositorio->reportarSinCambios($usuario, $proceso->id);
                                         if($resultado->error())
                                             break;
                                     }
                                     if($resultado->error())
                                         break;
                                 }
                                echo $usuario->id . ". ".$usuario->usuarioNombreCompleto . ": " . count($procesos) . " procesos terminados."; 
                             }
                             else 
                                 echo $usuario->id . ". ".$usuario->usuarioNombreCompleto .": No es mes de terminacion automatica";
                         }
                     }
                     Logger::log("log_revision_automatica_procesos","Termimado!");
             }
             else
             {
                 echo $resultado->mensajeError;
                 Logger::log("log_revision_automatica_procesos",$resultado->mensajeError);
             }
         }
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
    