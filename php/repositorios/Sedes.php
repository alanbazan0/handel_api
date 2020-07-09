<?php
use php\clases\AdministradorConexion;
use php\clases\JsonMapper;
use php\modelos\Sede;
use php\repositorios\SedesRepositorio;
use php\modelos\Resultado;

error_reporting(E_ALL);
ini_set('display_errors', 1);


include '../clases/JsonMapper.php';
include '../clases/Utilidades.php';
include '../clases/AdministradorConexion.php';
include '../repositorios/SedesRepositorio.php';


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
        $repositorio = new SedesRepositorio($conexion);
        switch ($accion)
        {           
            case 'insertar':               
                $json = json_decode(REQUEST('modelo'));
                $mapper = new JsonMapper();
                $modelo = $mapper->map($json, new Sede());                   
                $resultado = $repositorio->insertar($modelo);                
            break;
            case 'actualizar':
                $json = json_decode(REQUEST('modelo'));
                $mapper = new JsonMapper();
                $modelo = $mapper->map($json, new Sede());
                $resultado = $repositorio->actualizar($modelo) ;
            break;
            case 'consultar':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                $opcional = REQUEST('opcional');
                $resultado = $repositorio->consultar($criteriosSeleccion,$opcional,$usuario);               
            break;
            case 'consultarPorLlaves':
                $llaves = json_decode(REQUEST('llaves'));
                $resultado = $repositorio->consultarPorLlaves($llaves);
            break;
            case 'consultarPorEmpresa':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $empresaId = REQUEST('empresaId');
                $opcional = REQUEST('opcional');
                $resultado = $repositorio->consultarPorEmpresa($empresaId,$opcional,$usuario);
            break;
            case 'consultarPorEmpresaUsuario':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                    $empresaId = REQUEST('empresaId');
                    $opcional = REQUEST('opcional');
               $resultado = $repositorio->consultarPorEmpresaUsuario($empresaId,$opcional,$usuario);
             break;
            case 'eliminar':
                $llaves = json_decode(REQUEST('llaves'));
                $resultado = $repositorio->eliminar($llaves);
            break;
            default:
                $resultado->mensajeError = "Acción no válida";
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
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    $administrador_conexion->cerrar($conexion);
}


