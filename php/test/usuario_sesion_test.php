<?php
use php\clases\AdministradorConexion;
use php\modelos\Usuario;

use php\repositorios\UsuariosRepositorio;
use php\modelos\Resultado;

error_reporting(E_ALL);
ini_set('display_errors', 1);


include '../clases/AdministradorConexion.php';
include '../repositorios/UsuariosRepositorio.php';




$origin = "*";
if(isset($_SERVER['HTTP_ORIGIN']))
    $origin =$_SERVER['HTTP_ORIGIN'];
header('Access-Control-Allow-Origin: '.$origin);
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Credentials: true');
    
    
    // if($session_cookie_domain!="")
        //     ini_set('session.cookie_domain', $session_cookie_domain);

$administrador_conexion = new AdministradorConexion();
$resultado = new Resultado();
$conexion=null;
try
{
    $conexion = $administrador_conexion->abrir();
    if($conexion)
    {
        $repositorio = new UsuariosRepositorio($conexion);
        $resultado = $repositorio->consultarPorCorreoElectronico("alanbazan@hotmail.com");
        if($resultado->correcto())
        {
            session_start();
            echo "USUARIO LEIDO DESDE LA BASE DE DATOS\n";
            $usuario = $resultado->valor;
            $usuario->contrasena= "*****";
            $usuario->correoElectronico= "*****";
            var_dump($usuario);
           
            echo "GUARDANDO USUARIO EN VARIABLE DE SESION: ";
           
            $_SESSION['usuario']=$usuario;
            echo "OK\n";
           
            echo "CONSULTANDO USUARIO GUARDADO EN VARIABLE DE SESION: ";
            $usuario = null;
            if(isset($_SESSION['usuario']))
            {
                echo "OK\n";
                $usuario = $_SESSION['usuario'];
            }
            else 
                echo "ERROR\n";
            var_dump($usuario);
           
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
        $json = json_encode($resultado, JSON_PRETTY_PRINT);
        if (FALSE === $json)
            echo '{"mensajeError":"' .json_last_error_msg() . '"}';
            else
                echo $json;
    }
    $administrador_conexion->cerrar($conexion);
}
