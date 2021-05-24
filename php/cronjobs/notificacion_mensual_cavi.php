<?php
use php\clases\AdministradorConexion;

use php\modelos\Resultado;
use php\repositorios\CursosRepositorio;
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
require_once('../repositorios/CursosRepositorio.php');
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
    $dia = REQUEST("dia");
    if($dia==null)
        $dia = date("j");
        
    $mes =  intval(date("m"));
    $ano = intval(date("Y"));
    $ultimoDia = Mes::getUltimoDia($mes, $ano);
    if($dia == $ultimoDia)
    {
        $conexion = $administrador_conexion->abrir();
        if($conexion)
        {
            $repositorio = new CursosRepositorio($conexion);
            $nombreUsuario= REQUEST("nombreUsuario");
            $enviarA= REQUEST("enviarA");
            $numeroUsuarios = REQUEST("numeroUsuarios");
            $empresaId = REQUEST("empresaId");
                
            $resultado= $repositorio->enviarNotificacionMensual($nombreUsuario,$enviarA,$numeroUsuarios,$empresaId);
            if($resultado->correcto())
            {
                echo "<br>CORRECTO";
                echo "<br>Usuarios: " . $resultado->valor->usuarios;
                echo "<br>Usuarios filtrados: " . $resultado->valor->usuariosFiltrados;
                echo "<br>Enviados: " . $resultado->valor->enviados; 
            }
            else
                echo "<br>ERROR: ". $resultado->mensajeError;
        }
    }   
    
}
catch(Exception $e)
{
    $resultado->mensajeError = $e->getMessage();
    Logger::log("log_envio",$resultado->mensajeError,"envios_mensual_cavi/");
}
finally
{
    $administrador_conexion->cerrar($conexion);
   
}
    