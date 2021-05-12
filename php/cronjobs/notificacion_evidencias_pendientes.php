<?php
use php\clases\AdministradorConexion;

use php\repositorios\UsuariosRepositorio;
use php\modelos\Resultado;
use php\repositorios\UsuariosProcedimientosRepositorio;
use php\repositorios\MinutasRepositorio;
use php\repositorios\FrasesRepositorio;
use php\repositorios\TareasComentariosRepositorio;
use php\repositorios\AuditoriasRepositorio;

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
        ini_set('max_execution_time', 300);
        $usuariosRepositorio = new UsuariosRepositorio($conexion);
       // $minutasRepositorio = new MinutasRepositorio($conexion);
        $auditoriasRepositorio = new AuditoriasRepositorio($conexion);
        $usuarios = array();

        $resultado = $auditoriasRepositorio->consultarAdministradoresConEvidencias();
        if($resultado->correcto())
            $usuarios = $resultado->valor;
        else
            mensajeLog("error_envio_evidencias_pendientes",$resultado->mensajeError);
        mensajeLog("log_envio_evidencias_pendientes","Total: ". count($usuarios));
    
        
        $resultado->valor = "";
        
      
        $parametroDebug= REQUEST("debug");
        if($parametroDebug=="true")
            $debug=true;
        if($debug)
        {
            $nombreUsuario= REQUEST("nombreUsuario");
            if($nombreUsuario!="")
            {
              
                $resultado = $usuariosRepositorio->consultar(null,(object) ['nombreUsuario' =>  $nombreUsuario],false);
                if($resultado->correcto())
                    $usuarios = $resultado->valor;
            }
          
            $enviarA= REQUEST("enviarA");
            for ($i = 0; $i < count($usuarios); $i++) 
            {
                $usuario = $usuarios[$i];
                if(isset($enviarA) && $enviarA!="")
                    $usuario->nombreUsuario = $enviarA;
            }
            $usuarios = array_slice($usuarios,0,$numeroUsuarios);
   
        }
        
        $dia = REQUEST("dia");
        if($dia==null)
            $dia = date("w");
        
         if($dia == DiaSemana::VIERNES)
         {
            //$asunto = getAsunto($dia);
            for ($i = 0; $i < count($usuarios); $i++)
            {
                $usuario = $usuarios[$i];
                $resultado = $auditoriasRepositorio->enviarNotificacionEvidenciasPendientes($usuario);
                if($resultado->correcto())
                    mensajeLog("log_envio_evidencias_pendientes","$i Correo enviado a ".$usuario->nombreUsuario);
                sleep($tiempoEspera);
            }
            mensajeLog("log_envio_evidencias_pendientes","Termimado!");
        }
    }
    
}
catch(Exception $e)
{
    $resultado->mensajeError = $e->getMessage();
    mensajeLog("log_envio_evidencias_pendientes",$resultado->mensajeError);
}
finally
{
    $administrador_conexion->cerrar($conexion);
   
}


function mensajeLog($archivo,$mensaje)
{
    $mensaje = date("j/n/Y h:i:s") .":".$mensaje;
    $carpeta = "envios_evidencias_pendientes/";
    if(!file_exists($carpeta))
        @mkdir($carpeta);
    file_put_contents($carpeta.$archivo.'_'.date("j.n.Y").'.log',  utf8_decode("\n".$mensaje) , FILE_APPEND);
    echo "<br>".utf8_decode($mensaje);
}

function guardarEnvio($usuario, $asunto, $mensaje)
{
    $carpeta = "envios_evidencias_pendientes/".date("j.n.Y")."/";
    if(!file_exists($carpeta))
        @mkdir($carpeta);
    
 //       $archivo = $carpeta . $usuario->nombreUsuario
    file_put_contents($carpeta.$usuario->nombreUsuario.".html",  $mensaje , FILE_TEXT);
    
}
    