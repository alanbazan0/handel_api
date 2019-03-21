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



header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');

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
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                $resultado = $repositorio->consultar($criteriosSeleccion);               
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
                
                $resultado = $repositorio->consultarInspeccionesEmpresa();
            break;
            case 'consultarInspeccionesMes':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                {
                    $usuario = $_SESSION['usuario'];
                    $resultado = $repositorio->consultarInspeccionesMes($usuario);
                }
            break;
            case 'consultarInspeccionesSede':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                {
                    $usuario = $_SESSION['usuario'];
                    $resultado = $repositorio->consultarInspeccionesSede($usuario);
                }
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


