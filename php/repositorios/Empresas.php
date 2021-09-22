<?php
use php\clases\AdministradorConexion;
use php\clases\JsonMapper;
use php\modelos\Empresa;
use php\repositorios\EmpresasRepositorio;
use php\modelos\Resultado;
use php\clases\AdministradorArchivos;
use php\clases\AdministradorCorreo;

error_reporting(E_ALL);
ini_set('display_errors', 1);


include '../clases/JsonMapper.php';
include '../clases/Utilidades.php';
include '../clases/AdministradorConexion.php';
include '../clases/AdministradorArchivos.php';
include '../repositorios/EmpresasRepositorio.php';



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
        $repositorio = new EmpresasRepositorio($conexion);
        switch ($accion)
        {           
            case 'insertar':               
                $json = json_decode(REQUEST('modelo'));
                $mapper = new JsonMapper();
                $modelo = $mapper->map($json, new Empresa());     
              
                $resultado = $repositorio->insertar($modelo);      
              
                if($resultado->mensajeError=="")
                {
                    $id =  $resultado->valor;
                    $adminstradorArchivos = new AdministradorArchivos();
                    $archivo = FILES("file");
                    $carpeta = "logos_empresas";
                    $nombreArchivo = "logo".$modelo->id.".png";
                    $resultado=$adminstradorArchivos->subirImagen($carpeta,$archivo,$nombreArchivo);
                    $resultado->valor = $id;
                }
            break;
            case 'actualizar':
                $json = json_decode(REQUEST('modelo'));
                $mapper = new JsonMapper();
                $modelo = $mapper->map($json, new Empresa());
                $resultado = $repositorio->actualizar($modelo) ;
                if($resultado->mensajeError=="")
                {
                    
                    $adminstradorArchivos = new AdministradorArchivos();
                    $archivo = FILES("file");
                   // $carpeta = "../logos_empresas/";
                    $carpeta = "logos_empresas";
                    $nombreArchivo = "logo".$modelo->id.".png";
                    $resultado=$adminstradorArchivos->subirImagen($carpeta,$archivo,$nombreArchivo);
                }
            break;
            case 'consultarPorLlaves':
                $llaves = json_decode(REQUEST('llaves'));
                $resultado = $repositorio->consultarPorLlaves($llaves);              
            break;
            case 'consultarCorporativos':
                $empresaId = REQUEST('empresaId');
                $resultado = $repositorio->consultarCorporativo($empresaId);
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
            case 'consultarEstructura':
                $resultado = $repositorio->consultarEstructura(false);
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
    {
        $json = json_encode($resultado, JSON_UNESCAPED_UNICODE);
        if (FALSE === $json)
            echo '{"mensajeError":"' .json_last_error_msg() . '"}';
            else
                echo $json;
    }
    $administrador_conexion->cerrar($conexion);
}


