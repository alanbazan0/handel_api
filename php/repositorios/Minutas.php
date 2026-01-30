<?php
use php\clases\AdministradorConexion;
use php\clases\JsonMapper;
use php\modelos\Minuta;
use php\repositorios\MinutasRepositorio;
use php\modelos\Resultado;
use php\modelos\Tarea;
use php\clases\CodigoError;

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../clases/JsonMapper.php';
include '../clases/Utilidades.php';
include '../clases/AdministradorConexion.php';
include '../repositorios/MinutasRepositorio.php';
include '../modelos/Tarea.php';
require_once ('../clases/CodigoError.php');


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
                    $resultado = $repositorio->consultar($usuario,$criteriosSeleccion);
                break;
                case 'consultarTodas':
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarTodas($usuario,$criteriosSeleccion);
                break;
                case 'consultarMisTareas':
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarMisTareas($usuario,$criteriosSeleccion);
                break;
//                 case 'consultarTareasPendientes':
//                     $resultado = $repositorio->consultarTareasPendientes($usuario);
//                 break;
                case 'consultarPorLlaves':
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->consultarPorLlaves($llaves,true);
                break;
                case 'consultarEncabezadoPorLlaves':
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->consultarPorLlaves($llaves,false);
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
                    $resultado = $repositorio->actualizarValorTarea($usuario,$minutaId, $tareaId, $campo,  $valor);
                break;
                case 'actualizarUsuarios':
                    $minutaId = REQUEST('minutaId');
                    $mapper = new JsonMapper();
                    $usuarios = $mapper->mapArray(json_decode(REQUEST('usuarios')), array());
                    $resultado = $repositorio->actualizarUsuarios($usuario,$minutaId, $usuarios);
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
                case 'consultarNumeroComentariosTarea':
                    $minutaId = REQUEST('minutaId');
                    $tareaId = REQUEST('tareaId');
                    $resultado = $repositorio->consultarNumeroComentariosTarea($minutaId, $tareaId);
                break;
                case 'consultarPorcentajeAvance':
                    $minutaId = REQUEST('minutaId');
                    $resultado = $repositorio->consultarPorcentajeAvance($minutaId);
                break;
                case 'copiar':
                    $llaves = json_decode(REQUEST('llaves'));
                    $titulo = REQUEST('titulo');
                    $resultado = $repositorio->copiar($usuario,$llaves,$titulo);
                break;
                case 'generarAutoMinuta':
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->generarAutoMinuta($usuario,$criteriosSeleccion);
                break;
                case 'consultarTareasAsignadas':
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarTareasAsignadas($usuario,$criteriosSeleccion);
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
