<?php
use php\clases\AdministradorConexion;
use php\clases\AdministradorArchivos;
use php\clases\JsonMapper;
use php\modelos\Usuario;

use php\repositorios\UsuariosRepositorio;
use php\repositorios\HistorialAccesoRepositorio;
use php\modelos\Resultado;
use php\clases\AdministradorCorreo;

error_reporting(E_ALL);
ini_set('display_errors', 1);


include '../clases/JsonMapper.php';
include '../clases/Utilidades.php';
include '../clases/AdministradorConexion.php';
include '../clases/AdministradorArchivos.php';
include '../clases/AdministradorCorreo.php';
include '../modelos/Usuario.php';
include '../clases/TipoUsuario.php';
include '../repositorios/UsuariosRepositorio.php';
include "../repositorios/HistorialAccesoRepositorio.php";


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
        $repositorio = new UsuariosRepositorio($conexion);
        switch ($accion)
        {           
            case 'insertar':
                $json = json_decode(REQUEST('modelo'));
                $mapper = new JsonMapper();
                $modelo = $mapper->map($json, new Usuario());            
                $resultado = $repositorio->insertar($modelo);     
                if($resultado->mensajeError=="")
                {
                    $administrador_correo = new AdministradorCorreo();
                    if($modelo->nombreUsuario!="" && $modelo->nombreUsuario!=null)
                        $administrador_correo->enviarCorreoBienvenida($modelo);
                }
            break;
            case 'actualizar':
                $json = json_decode(REQUEST('modelo'));
                $mapper = new JsonMapper();
                $modelo = $mapper->map($json, new Usuario());                
                $resultado = $repositorio->actualizar($modelo) ;
            break;            
            case 'eliminar':
                $llaves = json_decode(REQUEST('llaves'));
                $resultado = $repositorio->eliminar($llaves);
            break;
            case 'consultarSupervisoresPorEmpresa':
                $empresaId = REQUEST('empresaId');
                $usuarioId = REQUEST('usuarioId');
                $resultado = $repositorio->consultarSupervisoresPorEmpresa($empresaId,$usuarioId);              
            break;
            case 'consultarPorLlaves':
                $llaves = json_decode(REQUEST('llaves'));
                $resultado = $repositorio->consultarPorLlaves($llaves);
            break;
            case 'consultar':
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                $resultado = $repositorio->consultar($criteriosSeleccion);               
            break;
            case 'iniciarSesion':
                session_start();
                $nombreUsuario = REQUEST('nombreUsuario');
                $contrasena = REQUEST('contrasena');
                $resultado = $repositorio->consultarUsuario($nombreUsuario,$contrasena);
                if($resultado->valor!=null)
                {
                    if($resultado->valor->tipoUsuarioId == TipoUsuario::ADMINISTRADOR || $resultado->valor->tipoUsuarioId == TipoUsuario::COORDINADOR || $resultado->valor->tipoUsuarioId == TipoUsuario::SUPERVISOR)
                    {
                        $_SESSION['usuario']=$resultado->valor; 
                        $historialAccesoRepositorio = new HistorialAccesoRepositorio($conexion);
                        $ip = GET_IP();
                        $historialAccesoRepositorio->insertar($nombreUsuario,$ip);
                    }
                    else
                    {
                        $resultado->valor = null;
                        $resultado->mensajeError="El acceso a la plataforma en linea esta restringido a usuarios autorizados, si necesita ingresar para realizar cambios por favor solicite los cambios con su supervisor autorizado";
                        unset($_SESSION['usuario']);
                    }
                }
                else 
                    unset($_SESSION['usuario']);
            break;
            case "cerrarSesion":
                session_start();
                unset($_SESSION['usuario']);
            break;
            case "consultarSesion":
                session_start();
                if(isset($_SESSION['usuario']))
                    $resultado->valor=$_SESSION['usuario'];
            break;
            case "subirFotoPerfil":
                session_start();
                if(isset($_SESSION['usuario']))
                {
                    $usuario = $_SESSION['usuario'];
//                     $adminstradorArchivos = new AdministradorArchivos();
//                     $archivo = FILES("file");
//                     $carpeta = "../fotos/";
//                     $nombreArchivo = "perfil".$usuario->id.".jpg";
//                     $resultado = $adminstradorArchivos->subir($carpeta,$archivo,$nombreArchivo);
                    
                    $adminstradorArchivos = new AdministradorArchivos();
                    $archivo = FILES("file");
                    // $carpeta = "../logos_empresas/";
                    $carpeta = "fotos";
                    $nombreArchivo = "perfil".$usuario->id.".png";
                    $resultado=$adminstradorArchivos->subir($carpeta,$archivo,$nombreArchivo);
                }
               
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


