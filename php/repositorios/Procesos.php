<?php
use php\clases\AdministradorConexion;

use php\clases\JsonMapper;
use php\modelos\Proceso;
use php\repositorios\ProcesosRepositorio;
use php\modelos\Resultado;

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../clases/JsonMapper.php';
include '../clases/Utilidades.php';
include '../clases/AdministradorConexion.php';
include '../repositorios/ProcesosRepositorio.php';
include '../clases/AdministradorArchivos.php';

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
        $repositorio = new ProcesosRepositorio($conexion);
        switch($accion)
        {
            case 'insertar':
                $json = json_decode(REQUEST('modelo'));
                $mapper = new JsonMapper();
                $modelo = $mapper->map($json, new Proceso());
                $resultado = $repositorio->insertar($modelo);
            break;
            case 'copiarProcesos':
                $empresaIdOrigen = REQUEST('empresaIdOrigen');
                $sedeIdOrigen = REQUEST('sedeIdOrigen');
                $empresaIdDestino = REQUEST('empresaIdDestino');
                $sedeIdDestino = REQUEST('sedeIdDestino');
                $json = json_decode(REQUEST('procedimientos'));
                $mapper = new JsonMapper();
                $procedimientos = $mapper->mapArray($json, array());
                $resultado = $repositorio->copiarProcesos($empresaIdOrigen, $sedeIdOrigen, $procedimientos, $empresaIdDestino,$sedeIdDestino);
            break;
            case 'actualizar':
                $json = json_decode(REQUEST('modelo'));
                $mapper = new JsonMapper();
                $modelo = $mapper->map($json, new Proceso());
                $resultado = $repositorio->actualizar($modelo) ;
            break;
            case 'consultar':
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                $resultado = $repositorio->consultar($criteriosSeleccion);
            break;
            case 'consultarPorEmpresaSede':
                $empresaId = REQUEST('empresaId');
                $sedeId = REQUEST('sedeId');
                $resultado = $repositorio->consultarPorEmpresaSede($empresaId,$sedeId);
            break;
            case 'consultarPorLlaves':
                $llaves = json_decode(REQUEST('llaves'));
                $resultado = $repositorio->consultarPorLlaves($llaves);
            break;
            case 'eliminar':
                $llaves = json_decode(REQUEST('llaves'));
                $resultado = $repositorio->eliminar($llaves);
            break;
            case 'adjuntar':
                $llaves = json_decode(REQUEST('llaves'));
                $archivo = FILES("file");
                $resultado = $repositorio->adjuntarArchivo($llaves,$archivo);
                
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
