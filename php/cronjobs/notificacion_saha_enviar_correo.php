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
include '../modelos/Usuario.php';
require_once('../clases/TipoUsuario.php');
require_once('../repositorios/UsuariosRepositorio.php');
require_once('../repositorios/EvidenciasRepositorio.php');
require_once('../repositorios/CorreosRepositorio.php');
//require_once('../reportes/reporte_evidencias.php');

$origin = "*";
if(isset($_SERVER['HTTP_ORIGIN']))
    $origin =$_SERVER['HTTP_ORIGIN'];
header('Access-Control-Allow-Origin: '.$origin);
header('Content-Type: html; charset=UTF-8');
header('Access-Control-Allow-Credentials: true');

ini_set('max_execution_time', 500);

$imprimirMensaje = false;
$numeroUsuarios = 3;
$administrador_conexion = new AdministradorConexion();
$resultado = new Resultado();
$conexion=null;
$enviados = 0;
$noEnviados = 0;
$tamanoLote = 60;
$pausaCorreo = 10;
$guardarEnvio = false;

try
{
    $conexion = $administrador_conexion->abrir();
    if($conexion)
    {
        
        $tamanoLote= REQUEST("tamanoLote");
        if($tamanoLote=="" || $tamanoLote==null)
            $tamanoLote = 1;
        mensajeLog("notificacion_saha_enviar_correo","##----------------------------- EJECUCION -----------------------------##");
            
        $correos = array();
        $repositorio = new CorreosRepositorio($conexion);
        $criteriosSeleccion = (object)["estatus"=>EstatusCorreo::PROCESADO, "tipo" => "saha"];
        $resultado = $repositorio->consultar($criteriosSeleccion, $tamanoLote);
        if($resultado->correcto())
        {
            $correos = $resultado->valor;
        }
        else
            mensajeLog("notificacion_saha_enviar_correo",$resultado->mensajeError);
        
        
        $numeroUsuarios= REQUEST("numeroUsuarios");
        if($numeroUsuarios!=0 && $numeroUsuarios!="")
            $correos = array_slice($correos,0,$numeroUsuarios);
        
        $enviarA= REQUEST("enviarA");
        for ($i = 0; $i < count($correos); $i++)
        {
            $correo = $correos[$i];
            $correo->nombreUsuarioOriginal = $correo->nombreUsuario;
            if(isset($enviarA) && $enviarA!="")
                $correo->nombreUsuario = $enviarA;
        }
      
        mensajeLog("notificacion_saha_enviar_correo","Total a generar: ". count($correos));

        
        $horaServidor = (int) date("H");
        
        $horaInicial = (int) REQUEST("horaInicial");
        $horaFinal = (int) REQUEST("horaFinal");
        $dia = REQUEST("dia");
        if($dia==null)
            $dia = date("j");
        
        mensajeLog("notificacion_saha_enviar_correo","Dia: ". $dia);
            
        if((count($correos) > 0) &&( $dia ==1 || $dia ==14  || $dia ==21 || $dia ==27 || $dia ==28) && $horaServidor >= $horaInicial && $horaServidor <= $horaFinal)
         {
            //$asunto = getAsunto($dia);
            
            //$usuariosProcedimientosRepositorio = new UsuariosProcedimientosRepositorio($conexion);
            //$evidenciasRepositorio = new EvidenciasRepositorio($conexion);
            
            //$fileContent = file_get_contents('notificacion.html');
            
            for ($i = 0; $i < count($correos); $i++)
            {
                $correo = $correos[$i];
                
                $resultado = $repositorio->procesando($correo->id);
                if(!$resultado->correcto())
                {
                    mensajeLog("notificacion_saha_enviar_correo",$resultado->mensajeError);
                    break;
                }
                  
               // $contenido = $correo->mensaje;
                //$asunto = $correo->asunto;
                //$contenido = "PRUEBA ENVIO SAHA";
                if($correo->mensaje!="")
                {
                   
                    $errLevel = error_reporting(E_ALL ^ E_WARNING);
                    $resultadoMail = true;
                    $resultadoMail= mail($correo->nombreUsuario,$correo->asunto, $correo->mensaje, $correo->cabecera);
                    error_reporting($errLevel);
                    
                    $error = error_get_last();
                    
                    if ($error!=null &&  $error["type"] == E_WARNING)
                    {
                        $resultado->mensajeError="No se pudo enviar el correo electrónico a $correo->nombreUsuario.  ". htmlspecialchars_decode($error["message"]) ;
                        $resultado->codigoError = 3;
                        mensajeLog("error",$i. " " .$resultado->mensajeError);
                        $repositorio->noEnviado($correo->id, $resultado->mensajeError);
                    }
                    else if($resultadoMail)
                    {
                        $resultado->valor="OK";
                        mensajeLog("notificacion_saha_enviar_correo",($i +1) . " Correo de $correo->nombreUsuarioOriginal enviado a ".$correo->nombreUsuario);
                        $enviados++;
                        $repositorio->enviado($correo->id);
                    }
                    
                   
                    
                    if($guardarEnvio)
                        guardarEnvio($correo,$correo->asunto,$correo->mensaje);
                    
                   
                }
                else
                {
                    $mensaje = "Contenido vacio ".$correo->nombreUsuario;
                    mensajeLog("notificacion_saha_enviar_correo",$mensaje);
                    $resultado = $repositorio->noEnviado($correo->id, $mensaje);
                    if(!$resultado->correcto())
                    {
                        mensajeLog("notificacion_saha_enviar_correo",$resultado->mensajeError);
                        break;
                    }
                    $noEnviados++;
                }
          
                if($imprimirMensaje)
                    echo $mensaje;
                sleep($pausaCorreo);
               
            }
            mensajeLog("notificacion_saha_enviar_correo","Enviados: " .$enviados);
            mensajeLog("notificacion_saha_enviar_correo","No enviados: " .$noEnviados);
            mensajeLog("notificacion_saha_enviar_correo","Termimado!");
        }
        else
            mensajeLog("notificacion_saha_enviar_correo","No ejecutado, hora servidor: $horaServidor, hora inicial = $horaInicial, hora final = $horaFinal ");
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
    $imprimeLogs = true;
    if($imprimeLogs)
    {
        $mensaje = date("j/n/Y h:i:s") .":".$mensaje;
        file_put_contents('./logs/'.$archivo.'_'.date("j.n.Y").'.log',  utf8_decode("\n".$mensaje) , FILE_APPEND);
        echo "<br>".utf8_decode($mensaje);
    }
}

function guardarEnvio($usuario, $asunto, $mensaje)
{
    $carpeta = "envios/".date("j.n.Y")."/";
    if(!file_exists($carpeta))
        @mkdir($carpeta);
    
 //       $archivo = $carpeta . $usuario->nombreUsuario
    file_put_contents($carpeta.$usuario->nombreUsuario.".html",  $mensaje , FILE_TEXT);
    
}
    