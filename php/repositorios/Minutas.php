<?php
use php\clases\AdministradorConexion;
use php\clases\JsonMapper;
use php\modelos\Minuta;
use php\repositorios\MinutasRepositorio;
use php\modelos\Resultado;
use php\modelos\Tarea;

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../clases/JsonMapper.php';
include '../clases/Utilidades.php';
include '../clases/AdministradorConexion.php';
include '../repositorios/MinutasRepositorio.php';
include '../modelos/Tarea.php';

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
    session_start();
    $usuario = null;
    if(isset($_SESSION['usuario']))
        $usuario = $_SESSION['usuario'];
    if($usuario!=null)
    {
        if($conexion)
        {
            $accion = REQUEST('accion');
            $repositorio = new MinutasRepositorio($conexion);
            switch($accion)
            {
                case 'insertar':
                  
                    $json = json_decode(REQUEST('modelo'));
                    $mapper = new JsonMapper();
                    $modelo = $mapper->map($json, new Minuta());
                    $resultado = $repositorio->insertar($modelo,$usuario);
                break;
                case 'actualizar':
                    $json = json_decode(REQUEST('modelo'));
                    $mapper = new JsonMapper();
                    $modelo = $mapper->map($json, new Minuta());
                    $resultado = $repositorio->actualizar($modelo) ;
                break;
                case 'consultar':
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultar($criteriosSeleccion);
                break;
                case 'consultarTareasPendientes':
                    $resultado = $repositorio->consultarTareasPendientes($usuario);
                    break;
                case 'consultarPorLlaves':
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->consultarPorLlaves($llaves);
                break;
                case 'consultarTareaPorLlaves':
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->consultarTareaPorLlaves($llaves);
                break;
                case 'eliminar':
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->eliminar($llaves);
                break;
                case 'actualizarValor':
                    $minutaId = REQUEST('minutaId');
                    $campo = REQUEST('campo');
                    $valor = REQUEST('valor');
                    $resultado = $repositorio->actualizarValor($minutaId, $campo,  $valor);
                break;
                case 'ordenarTareas':
                    $minutaId = REQUEST('minutaId');
                    $seleccion = REQUEST("seleccion");
                    $resultado = $repositorio->ordenarTareas($minutaId,$seleccion);
                break;
                
                case 'actualizarValorTarea':
                    $minutaId = REQUEST('minutaId');
                    $tareaId = REQUEST('tareaId');
                    $campo = REQUEST('campo');
                    $valor = REQUEST('valor');
                    $resultado = $repositorio->actualizarValorTarea($minutaId, $tareaId, $campo,  $valor);
                break;
                case 'eliminarTarea':
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->eliminarTarea($llaves);
                break;
                case 'insertarTarea':
    //                 session_start();
    //                 $usuario = null;
    //                 if(isset($_SESSION['usuario']))
    //                     $usuario = $_SESSION['usuario'];
                    $minutaId = REQUEST('minutaId');
                    $json = json_decode(REQUEST('modelo'));
                    $mapper = new JsonMapper();
                    $modelo = $mapper->map($json, new Tarea());
                    $resultado = $repositorio->insertarTarea($minutaId,$modelo,$usuario);
                break;
                case 'actualizarTarea':
    //                 session_start();
    //                 $usuario = null;
    //                 if(isset($_SESSION['usuario']))
    //                     $usuario = $_SESSION['usuario'];
                    $minutaId = REQUEST('minutaId');
                    $json = json_decode(REQUEST('modelo'));
                    $mapper = new JsonMapper();
                    $modelo = $mapper->map($json, new Tarea());
                    $resultado = $repositorio->actualizarTarea($minutaId,$modelo,$usuario);
                break;
                default:
                    $resultado->mensajeError = 'Acción no implementada';
                break;
            }
        }
    }
    else
    {
        $resultado->mensajeError = "La sesión caducó. Inicie sesión e intente de nuevo.";
        $resultado->codigoError = "sesion_caducada";
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
