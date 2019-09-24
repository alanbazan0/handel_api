<?php
use php\clases\AdministradorConexion;

use php\repositorios\UsuariosRepositorio;
use php\modelos\Resultado;
use php\repositorios\TiposUsuarioRepositorio;

error_reporting(E_ALL);
ini_set('display_errors', 1);


include '../clases/JsonMapper.php';
include '../clases/Utilidades.php';
include '../configuracion.php';
include '../clases/AdministradorConexion.php';
include '../clases/AdministradorCorreo.php';
include '../modelos/Usuario.php';
include '../clases/TipoUsuario.php';
include '../repositorios/UsuariosRepositorio.php';




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
        $repositorio = new UsuariosRepositorio($conexion);
        $usuarios = array();
        $resultado = $repositorio->consultar((object) ['tipoUsuarioId' =>  TipoUsuario::SUPERVISOR]);
        array_push($usuarios,$resultado->valor);
        $resultado = $repositorio->consultar((object) ['tipoUsuarioId' =>  TipoUsuario::COORDINADOR]);
        array_push($usuarios,$resultado->valor);
        
        $resultado->valor = "";
        
        $usuarios = array();
        array_push($usuarios,(object) ['nombreUsuario' => 'alanbazan@apps-handel.com','nombreCompleto' => 'Alan Bazán']);
        array_push($usuarios,(object) ['nombreUsuario' => 'eduardo@handel-sce.com','nombreCompleto' => 'Eduardo']);
        
        $dia = REQUEST("dia");
        if($dia==null)
            $dia = date("j");
        $titulo ="";
        $texto ="";
        $asunto ="";
        $caricatura = "";
        $pie = "";
        
        
        switch($dia)
        {
            case 28:
                $asunto = " ¡El reporte del mes de SAHA está listo!";
                $caricatura = "https://api.apps-handel.com/images/caricatura/Little_Business_Girl-78.png";
                $texto = "Es el ultimo recordatorio del mes, este es para agradecerte el apoyo constante y adjuntar a este correo el reporte que muestra el estado de trabajo del mes de tu equipo; por favor toma unos minutos para retroalimentar a tu equipo de trabajo utilizando el sistema de mensajes de SAHA.";
                $pie =  "<label style='font-weight:bold;color:#004D7F;'>¡Gracias por tu colaboracion!<label>";
            break;
           
        }
        
        
        for ($i = 0; $i < count($usuarios); $i++)
        {
            $usuario = $usuarios[$i];
            
            $mensaje= file_get_contents('notificacion_supervisor_coordinador.html');
            $mensaje=  str_replace("@nombre",$usuario->nombreCompleto,$mensaje);
            $mensaje=  str_replace("@titulo",$titulo,$mensaje);
            $mensaje=  str_replace("@texto",$texto,$mensaje);
            $mensaje=  str_replace("@caricatura",$caricatura,$mensaje);
            $mensaje=  str_replace("@pie",$pie,$mensaje);
            
            $cabecera = "From:  SAHA <noreply@apps-handel.com>\r\n"; //Remitente
            $cabecera .= "Content-type: text/html; charset=UTF-8\r\n";
            
            $errLevel = error_reporting(E_ALL ^ E_WARNING);
            $resultadoMail = true;
            $resultadoMail= mail($usuario->nombreUsuario, utf8_decode($asunto), $mensaje, $cabecera);
            error_reporting($errLevel);
            
            $error = error_get_last();
            
            if ( $error["type"] == E_WARNING)
            {
                $resultado->mensajeError="No se pudo enviar el correo electrónico." . htmlspecialchars_decode($error["message"]) ;
                $resultado->codigoError = 3;
                file_put_contents('./log_'.date("j.n.Y").'.log',  $resultado->mensajeError , FILE_APPEND);
            }
            else if($resultadoMail)
            {
                $resultado->valor="OK";
            }
          
        }
    }
    
}
catch(Exception $e)
{
    $resultado->mensajeError = $e->getMessage();
    file_put_contents('./log_'.date("j.n.Y").'.log',  $resultado->mensajeError , FILE_APPEND);
}
finally
{
    $administrador_conexion->cerrar($conexion);
    if($resultado!=null)
    {
        $json = json_encode($resultado, JSON_UNESCAPED_UNICODE);
        if (FALSE === $json)
            echo '{"mensajeError":"' .json_last_error_msg() . '"}';
            else
                echo $json;
    }
   
}
    
    
    