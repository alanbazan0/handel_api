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
        $resultado = $repositorio->consultar((object) ['tipoUsuarioId' =>  TipoUsuario::USUARIO]);
        array_push($usuarios,$resultado->valor);
//         $resultado = $repositorio->consultar((object) ['tipoUsuarioId' =>  TipoUsuario::SUPERVISOR]);
//         array_push($usuarios,$resultado->valor);
        
        $resultado->valor = "";
        
        $usuarios = array();
        array_push($usuarios,(object) ['nombreUsuario' => 'alanbazan@apps-handel.com','nombreCompleto' => 'Alan Bazán']);
        array_push($usuarios,(object) ['nombreUsuario' => 'eduardo@handel-sce.com','nombreCompleto' => 'Eduardo']);
        
        $dia = REQUEST("dia");
        if($dia==null)
            $dia = date("j");
        $titulo ="¡Recordatorio de evidencias!";
        $texto ="";
        $asunto ="¡Recordatorio de evidencias!";
        $caricatura = "https://api.apps-handel.com/images/caricatura/Bonus_Shapes_and_Backgounds-05.png";
        $diaLimite = 27;
        //$dia = 1;
        
        
     
        
        
        switch($dia)
        {
            case 1:
                $asunto = "Envía tus evidencias";
                $caricatura = "https://api.apps-handel.com/images/caricatura/Little_Business_Girl-03.png";
                $texto = "¡Hola! SAHA se encuentra abierto desde este momento para recibir las evidencias del mes, es importante que tomes unos minutos para identiﬁcarlas, organizarlas y subirlas así evitando olvidar subirlas después.";
                $pie =  " Agradecemos tu colaboración para tener el mejor desempeño de la compañía con la certiﬁcación.
                        <br>
                        <br>
                        PD
                        <br>
                        <label style=' font-style: italic;'>No olvides que puedes utilizar las apps de IOS y Android para facilitar la subida de tus evidencias.</label>";
            break;
            case 14:
                $asunto = "A dos semanas del cierre";
                $caricatura = "https://api.apps-handel.com/images/caricatura/Little_Business_Girl-46.png";
                $texto = "Este es un recordatorio de SAHA, en caso de que tengas evidencias por cumplir, por favor considera que quedan menos de 2 semanas para poder enviarlas";
                $pie =  "<label style='font-weight:bold;color:#004D7F;'>¡Gracias por tu colaboracion!</label>";
            break;
            case 21:
                $asunto = "!Quedan seis días! ";
                $caricatura = "https://api.apps-handel.com/images/caricatura/Little_Business_Girl-32.png";
                $texto = "El tiempo pasa volando; trabajo, reuniones, reportes, es fácil olvidar algunas tareas durante el mes, que SAHA no sea una de ellas. Estamos en esa parte del mes que llega el recordatorio de 6 días, la fecha limite se acerca pero aun estas a tiempo de poder cumpli";
                $pie =  "<label style='font-weight:bold;color:#004D7F;'>¡Gracias por tu colaboracion!</label>";
            break;
            case 27:
                $asunto = " !Ultimo día para subir evidencias a SAHA! ";
                $caricatura = "https://api.apps-handel.com/images/caricatura/Little_Business_Girl-46.png";
                $texto = 'Este es un recordatorio, hoy es el ultimo día para subir evidencias a SAHA, aprovecha las ultimas horas para revisar las evidencias enviadas por tu equipo y si aun tienen evidencias pendientes el tiempo se agota. Ayúdanos a mantener al 100% las evidencias de tu equipo. Puedes utilizar el sistema de mensajes de SAHA para motivar a tu equipo a terminar o felicitar a quien se tomó el tiempo para hacerlo a tiempo';
                $pie =  "Es el ultimo día y la ultima oportunidad para completar la subida de evidencias de tu equipo.
                        <br>
                        <br>
                        <label style='font-weight:bold;color:#004D7F;'>¡Gracias por tu colaboracion!</label>";
            break;
            default:
                $asunto = "!Recordatorio de evidencias! ";
                $diasRestantes = $diaLimite-$dia+1;
                $caricatura = "https://api.apps-handel.com/images/caricatura/Little_Business_Girl-46.png";
                $texto ="Este es un recordatorio de que tienes $diasRestantes días para subir tus evidencias de cumplimiento a SAHA, es importante tomes unos minutos para subir";
                $pie ="";
            break;    
        }
        
        
        for ($i = 0; $i < count($usuarios); $i++)
        {
            $usuario = $usuarios[$i];
            
            $mensaje= file_get_contents('notificacion_usuario.html');
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
    
    
    