<?php
use php\clases\AdministradorConexion;
use php\clases\JsonMapper;
use php\modelos\Resultado;
use php\repositorios\TareasComentariosRepositorio;
use php\modelos\TareaComentario;
use php\clases\CodigoError;

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../clases/JsonMapper.php';
include '../clases/Utilidades.php';
require_once ('../clases/CodigoError.php');
include '../clases/AdministradorConexion.php';
include '../repositorios/TareasComentariosRepositorio.php';

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
    session_start();
    $usuario = null;
    if(isset($_SESSION['usuario']))
        $usuario = $_SESSION['usuario'];
    if($usuario!=null)
    {
        $conexion = $administrador_conexion->abrir();
        if($conexion)
        {
            $accion = REQUEST('accion');
            $repositorio = new TareasComentariosRepositorio($conexion);
            switch($accion)
            {
                case 'insertar':
                    $json = json_decode(REQUEST('modelo'));
                    $mapper = new JsonMapper();
                    $modelo = $mapper->map($json, new TareaComentario());
                    $resultado = $repositorio->insertar($usuario,$modelo);
                break;
                case 'actualizar':
                    $json = json_decode(REQUEST('modelo'));
                    $mapper = new JsonMapper();
                    $modelo = $mapper->map($json, new TareaComentario());
                    $resultado = $repositorio->actualizar($modelo) ;
                break;
                case 'consultar':
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultar($criteriosSeleccion);
                break;
                case 'consultarPorLlaves':
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->consultarPorLlaves($llaves);
                break;
                case 'eliminar':
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->eliminar($llaves);
                break;
                default:
                    $resultado->mensajeError = 'Acción no válida';
                break;
            }
        }
    }
    else
    {
        $resultado->mensajeError = "La sesión caducó. Inicie sesión e intente de nuevo.";
        $resultado->codigoError = CodigoError::SESION_CADUCADA;
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
