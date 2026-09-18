<?php
use php\clases\AdministradorConexion;
use php\clases\JsonMapper;
use php\modelos\Inspeccion;
use php\repositorios\InspeccionesRepositorio;
use php\modelos\Resultado;

error_reporting(E_ALL);
ini_set('display_errors', 1);


include '../clases/JsonMapper.php';
include '../clases/Utilidades.php';
include '../clases/AdministradorConexion.php';
include '../repositorios/InspeccionesRepositorio.php';



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
        $repositorio = new InspeccionesRepositorio($conexion);
        switch ($accion)
        {           
            case 'insertar':        
                $jsonModelo = REQUEST('modelo');
                if($jsonModelo!=null && $jsonModelo!="")
                {
                    $json = json_decode($jsonModelo);
                    if($json!=null)
                    {
                        $mapper = new JsonMapper();
                        $modelo = $mapper->map($json, new Inspeccion());        
                    
                        $resultado = $repositorio->insertar($modelo);  
                    }
                    else 
                        $resultado->mensajeError ="No se pudo decodificar modelo con json_decode";
                }
                else
                    $resultado->mensajeError ="No se recibió el parametro 'modelo'";
            break;
            case 'actualizar':
                $json = json_decode(REQUEST('modelo'));
                $mapper = new JsonMapper();
                $modelo = $mapper->map($json, new Inspeccion());
                $resultado = $repositorio->actualizar($modelo) ;
            break;
            case 'consultar':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                $resultado = $repositorio->consultar($usuario,$criteriosSeleccion);               
            break;
            case 'consultarPorcentajeAleatorias':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                $resultado = $repositorio->consultarPorcentajeAleatorias($usuario,$criteriosSeleccion);
            break;
            case 'consultarDentroInstalacion':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                $resultado = $repositorio->consultarDentroInstalacion($usuario,$criteriosSeleccion);
            break;
            case 'consultarAjustesManuales':
                $sedeId = REQUEST('sedeId');
                $resultado = $repositorio->consultarAjustesManuales($sedeId);
            break;
            case 'consultarPorLlaves':
                $llaves = json_decode(REQUEST('llaves'));
                $resultado = $repositorio->consultarPorLlaves($llaves);
            break;
            case 'consultarPorEmpresaSede':
                $empresaId = REQUEST('empresaId');
                $sedeId = REQUEST('sedeId');
                $resultado = $repositorio->consultarPorEmpresaSede($empresaId,$sedeId);
            break;
            case 'eliminar':
                $llaves = json_decode(REQUEST('llaves'));
                $resultado = $repositorio->eliminar($llaves);
            break;
            case 'consultarInspeccionesEmpresa':
                session_start();
                $usuario = null;
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                if(isset($_SESSION['usuario']))
                {
                    $usuario = $_SESSION['usuario'];
                    $resultado = $repositorio->consultarInspeccionesEmpresa($usuario,$criteriosSeleccion);
                }
               
            break;
            case 'consultarInspeccionesMes':
                session_start();
                $usuario = null;
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                if(isset($_SESSION['usuario']))
                {
                    $usuario = $_SESSION['usuario'];
                    $resultado = $repositorio->consultarInspeccionesMes($usuario,$criteriosSeleccion);
                }
            break;
            case 'consultarInspeccionesHora':
                session_start();
                $usuario = null;
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                if(isset($_SESSION['usuario']))
                {
                    $usuario = $_SESSION['usuario'];
                    $resultado = $repositorio->consultarInspeccionesHora($usuario,$criteriosSeleccion);
                }
                break;
            case 'consultarInspeccionesSede':
                session_start();
                $usuario = null;
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                if(isset($_SESSION['usuario']))
                {
                    $usuario = $_SESSION['usuario'];
                    $resultado = $repositorio->consultarInspeccionesSede($usuario,$criteriosSeleccion);
                }
                break;
            case 'consultarInspeccionesArea':
                session_start();
                $usuario = null;
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                if(isset($_SESSION['usuario']))
                {
                    $usuario = $_SESSION['usuario'];
                    $resultado = $repositorio->consultarInspeccionesArea($usuario,$criteriosSeleccion);
                }
            break;
            case 'consultarInspeccionesInspector':
                session_start();
                $usuario = null;
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                if(isset($_SESSION['usuario']))
                {
                    $usuario = $_SESSION['usuario'];
                    $resultado = $repositorio->consultarInspeccionesInspector($usuario,$criteriosSeleccion);
                }
            break;
            case 'consultarTipoIncidentes':
                session_start();
                $usuario = null;
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                if(isset($_SESSION['usuario']))
                {
                    $usuario = $_SESSION['usuario'];
                    $resultado = $repositorio->consultarTipoIncidentes($usuario,$criteriosSeleccion);
                }
            break;
            case 'consultarTiempoPromedioInspeccionInspector':
                session_start();
                $usuario = null;
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                if(isset($_SESSION['usuario']))
                {
                    $usuario = $_SESSION['usuario'];
                    $resultado = $repositorio->consultarTiempoPromedioInspeccionInspector($usuario,$criteriosSeleccion);
                }
                break;
            case 'consultarAnos':
                $resultado = $repositorio->consultarAnos();
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
    {
        $json = json_encode($resultado, JSON_UNESCAPED_UNICODE);
        if (FALSE === $json)
            echo '{"mensajeError":"' .json_last_error_msg() . '"}';
            else
                echo $json;
    }
    $administrador_conexion->cerrar($conexion);
}


