<?php
use php\clases\AdministradorConexion;
use php\clases\JsonMapper;
use php\modelos\UsuarioFormato;
use php\repositorios\UsuariosFormatosRepositorio;
use php\modelos\Resultado;

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../clases/JsonMapper.php';
include '../clases/Utilidades.php';
include '../clases/AdministradorConexion.php';
include '../repositorios/UsuariosFormatosRepositorio.php';

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
        $repositorio = new UsuariosFormatosRepositorio($conexion);
        switch($accion)
        {
            case 'insertar':
                $json = json_decode(REQUEST('modelo'));
                $mapper = new JsonMapper();
                $modelo = $mapper->map($json, new UsuarioFormato());
                $resultado = $repositorio->insertar($modelo);
            break;
            case 'actualizar':
                $json = json_decode(REQUEST('modelo'));
                $mapper = new JsonMapper();
                $modelo = $mapper->map($json, new UsuarioFormato());
                $resultado = $repositorio->actualizar($modelo) ;
            break;
            case 'consultar':
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                $resultado = $repositorio->consultar($criteriosSeleccion);
            break;
            case 'consultarManualSeguridad':
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $resultado = $repositorio->consultarManualSeguridad($usuario,$criteriosSeleccion);
            break;
            case 'consultarPorLlaves':
                $llaves = json_decode(REQUEST('llaves'));
                $resultado = $repositorio->consultarPorLlaves($llaves);
            break;
            case 'eliminar':
                $llaves = json_decode(REQUEST('llaves'));
                $resultado = $repositorio->eliminar($llaves);
            break;
            case 'consultarProcedimientosPendientesMesActual':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                $resultado = $repositorio->consultarProcedimientosPendientesMesActual($usuario,$criteriosSeleccion);
            break;
           
            case 'consultarProcesosPendientes':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarProcesosPendientes($usuario,$criteriosSeleccion);
            break;
            case 'consultarAvanceUsuarios':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                $resultado = $repositorio->consultarAvanceUsuarios($usuario,$criteriosSeleccion);
            break;    
            case 'consultarAvanceDepartamentos':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                $resultado = $repositorio->consultarAvanceDepartamentos($usuario,$criteriosSeleccion);
            break;    
            case 'consultarAvanceEmpresas':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                $resultado = $repositorio->consultarAvanceEmpresas($usuario,$criteriosSeleccion);
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
