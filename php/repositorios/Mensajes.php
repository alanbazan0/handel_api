<?php
use php\clases\AdministradorConexion;
use php\clases\JsonMapper;
use php\modelos\Mensaje;
use php\repositorios\MensajesRepositorio;
use php\modelos\Resultado;
use php\repositorios\UsuariosRepositorio;
use php\clases\AdministradorCorreo;

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../clases/JsonMapper.php';
include '../clases/Utilidades.php';
include '../clases/AdministradorConexion.php';
include '../repositorios/UsuariosRepositorio.php';
include '../repositorios/MensajesRepositorio.php';
include '../clases/AdministradorCorreo.php';

$origin = "*";
if(isset($_SERVER['HTTP_ORIGIN']))
  $origin =$_SERVER['HTTP_ORIGIN'];
header('Access-Control-Allow-Origin: '.$origin);
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Credentials: true');

$administrador_conexion = new AdministradorConexion();
$resultado = new Resultado();
$conexion=null;
try
{
    $conexion = $administrador_conexion->abrir();
    if($conexion)
    {
        $accion = REQUEST('accion');
        $repositorio = new MensajesRepositorio($conexion);
        switch($accion)
        {
            case 'insertar':
                
                $json = json_decode(REQUEST('modelo'));
                $mapper = new JsonMapper();
                $modelo = $mapper->map($json, new Mensaje());
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $resultado = $repositorio->insertar($modelo,$usuario);
                if($resultado->correcto())
                {
                    $modelo->id = $resultado->valor;
                    $usuariosRepositorio = new UsuariosRepositorio($conexion);
                    $resultado = $usuariosRepositorio->consultar($modelo,null,false);
                    if($resultado->correcto())
                    {
                        $administrador_correo = new AdministradorCorreo();
                        $resultado = $administrador_correo->enviarNotificacionMensaje($usuario,$resultado->valor,$modelo);
                    }
                }
                
            break;
            case 'actualizar':
                $json = json_decode(REQUEST('modelo'));
                $mapper = new JsonMapper();
                $modelo = $mapper->map($json, new Mensaje());
                $resultado = $repositorio->actualizar($modelo) ;
            break;
            case 'consultar':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                $resultado = $repositorio->consultar($criteriosSeleccion,$usuario);
            break;
            case 'marcarComoLeido':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $mensajeId = REQUEST('mensajeId');
                $resultado = $repositorio->marcarComoLeido($mensajeId,$usuario);
            break;
            case 'consultarPorLlaves':
                $llaves = json_decode(REQUEST('llaves'));
                $resultado = $repositorio->consultarPorLlaves($llaves);
            break;
            case 'eliminar':
                $llaves = json_decode(REQUEST('llaves'));
                $resultado = $repositorio->eliminar($llaves);
            break;
            case 'consultarNumeroMensajesNoLeidos':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $resultado = $repositorio->consultarNumeroMensajesNoLeidos($usuario);
            break;
            default:
                $resultado->mensajeError = 'Acción no válida';
            break;
        }
    }
}
catch(Exception $e)
{
    $resultado->mensajeError = $e->getMessage();
}
finally
{
    if($resultado!=null)
    {
        $json = json_encode($resultado, JSON_UNESCAPED_UNICODE);
        if (FALSE === $json)
            echo '{"mensajeError":"' .json_last_error_msg() . '"}';
        else
            echo $json;
    }
    $administrador_conexion->cerrar($conexion);
}
