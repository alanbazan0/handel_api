<?php
use php\clases\AdministradorConexion;

use php\repositorios\UsuariosRepositorio;
use php\modelos\Resultado;
use php\repositorios\EvidenciasRepositorio;
use php\repositorios\UsuariosProcedimientosRepositorio;
use php\repositorios\CorreosRepositorio;
use php\modelos\Correo;

error_reporting(E_ALL);
ini_set('display_errors', 1);


include '../clases/JsonMapper.php';
include '../clases/Utilidades.php';
include '../configuracion.php';
include '../clases/AdministradorConexion.php';
include '../clases/AdministradorCorreo.php';
include '../clases/EstatusCorreo.php';
include '../modelos/Usuario.php';
require_once('../clases/TipoUsuario.php');
require_once('../repositorios/UsuariosRepositorio.php');
require_once('../repositorios/CorreosRepositorio.php');
require_once('../repositorios/UsuariosProcedimientosRepositorio.php');
//require_once('../reportes/reporte_evidencias.php');

$origin = "*";
if(isset($_SERVER['HTTP_ORIGIN']))
    $origin =$_SERVER['HTTP_ORIGIN'];
header('Access-Control-Allow-Origin: '.$origin);
header('Content-Type: html; charset=UTF-8');
header('Access-Control-Allow-Credentials: true');

ini_set('max_execution_time', 500);

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
        
        $usuariosRepositorio = new UsuariosRepositorio($conexion);
        $asociados = array();
        $supervisores = array();
        $coordinadores= array();
        
        $tipoUsuarioId = REQUEST("tipoUsuarioId");
        if($tipoUsuarioId==null || $tipoUsuarioId=="")
        {
            $resultado = $usuariosRepositorio->consultar(null,(object) ['tipoUsuarioId' =>  TipoUsuario::USUARIO, 'permisoSAHA' => 1, 'estatus' => 1],false);
            if($resultado->correcto())
                $asociados = $resultado->valor;
            else
                mensajeLog("error",$resultado->mensajeError);
                    
            $resultado = $usuariosRepositorio->consultar(null,(object) ['tipoUsuarioId' =>  TipoUsuario::SUPERVISOR, 'permisoSAHA' => 1, 'estatus' => 1],false);
            if($resultado->correcto())
                $supervisores = $resultado->valor;
            else
                mensajeLog("error",$resultado->mensajeError);
                
            $resultado = $usuariosRepositorio->consultar(null,(object) ['tipoUsuarioId' =>  TipoUsuario::COORDINADOR, 'permisoSAHA' => 1, 'estatus' => 1],false);
            if($resultado->correcto())
                $coordinadores = $resultado->valor;
            else
                mensajeLog("error",$resultado->mensajeError);
            
            
            $usuarios = array_merge($asociados, $supervisores,$coordinadores);
            mensajeLog("log_envio","Usuarios: ". count($asociados));
            mensajeLog("log_envio","Supervisores: ". count($supervisores));
            mensajeLog("log_envio","Coordinadores: ". count($coordinadores));
            mensajeLog("log_envio","Total: ". count($usuarios));
        }
        else 
        {
            $resultado = $usuariosRepositorio->consultar(null,(object) ['tipoUsuarioId' =>  $tipoUsuarioId, 'permisoSAHA' => 1, 'estatus' => 1],false);
            if($resultado->correcto())
                $asociados = $resultado->valor;
            else
                mensajeLog("error",$resultado->mensajeError);
            
            $usuarios = $asociados;
            
            mensajeLog("log_envio","Tipo usuario: ". $tipoUsuarioId);
            mensajeLog("log_envio","Total: ". count($usuarios));
        }
        
        $resultado->valor = "";
      
        $parametroDebug= REQUEST("debug");
        if($parametroDebug=="true")
            $debug=true;
        if($debug)
        {
            $nombreUsuario= REQUEST("nombreUsuario");
            if($nombreUsuario!="")
            {
                $resultado = $usuariosRepositorio->consultar(null,(object) ['nombreUsuario' =>  $nombreUsuario, 'permisoSAHA' => 1, 'estatus' => 1],false);
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
            
            $numeroUsuarios= REQUEST("numeroUsuarios");
            if($numeroUsuarios!=0)
                $usuarios = array_slice($usuarios,0,$numeroUsuarios);
          
            mensajeLog("log_envio","Total a enviar: ". count($usuarios));

        }
        
        $repositorio = new CorreosRepositorio($conexion);
        $resultado = $repositorio->eliminarTodo("saha");
        if($resultado->correcto())
        {
            for ($i = 0; $i < count($usuarios); $i++) 
            {
                $usuario = $usuarios[$i];
                $correo = new Correo();
                $correo->usuarioId = $usuario->id;
                $correo->tipo = "saha";
                $correo->estatus = EstatusCorreo::CREADO;
                $resultado = $repositorio->insertar($correo);
                if($resultado->error())
                {
                    mensajeLog("log_envio","Mensaje error: ". $resultado->mensajeError);
                    break;
                }
            }
        }
        else
        {
            mensajeLog("log_envio","Mensaje error: ". $resultado->mensajeError);
        }
    }
}
catch(Exception $e)
{
    $resultado->mensajeError = $e->getMessage();
    mensajeLog("error",$resultado->mensajeError);
}
finally
{
    $administrador_conexion->cerrar($conexion);

   
}

function mensajeLog($archivo,$mensaje)
{
    $mensaje = date("j/n/Y h:i:s") .":".$mensaje;
    file_put_contents('./logs/'.$archivo.'_'.date("j.n.Y").'.log',  utf8_decode("\n".$mensaje) , FILE_APPEND);
    echo "<br>".utf8_decode($mensaje);
}

function guardarEnvio($usuario, $asunto, $mensaje)
{
    $carpeta = "envios/".date("j.n.Y")."/";
    if(!file_exists($carpeta))
        @mkdir($carpeta);
    
 //       $archivo = $carpeta . $usuario->nombreUsuario
    file_put_contents($carpeta.$usuario->nombreUsuario.".html",  $mensaje , FILE_TEXT);
    
}
    