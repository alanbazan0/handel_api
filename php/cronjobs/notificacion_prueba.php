<?php
use php\clases\AdministradorConexion;

use php\repositorios\UsuariosRepositorio;
use php\modelos\Resultado;
use php\repositorios\EvidenciasRepositorio;
use php\repositorios\UsuariosProcedimientosRepositorio;

error_reporting(E_ALL);
ini_set('display_errors', 1);


include '../clases/JsonMapper.php';
include '../clases/Utilidades.php';
include '../configuracion.php';
include '../clases/AdministradorConexion.php';
include '../clases/AdministradorCorreo.php';
include '../modelos/Usuario.php';
require_once('../clases/TipoUsuario.php');
include '../repositorios/UsuariosRepositorio.php';
include '../repositorios/EvidenciasRepositorio.php';
include '../repositorios/UsuariosProcedimientosRepositorio.php';


$origin = "*";
if(isset($_SERVER['HTTP_ORIGIN']))
    $origin =$_SERVER['HTTP_ORIGIN'];
header('Access-Control-Allow-Origin: '.$origin);
header('Content-Type: html; charset=UTF-8');
header('Access-Control-Allow-Credentials: true');
    
$debug = false;
$imprimierMensaje = false;
$administrador_conexion = new AdministradorConexion();
$resultado = new Resultado();
$conexion=null;
try
{
    $conexion = $administrador_conexion->abrir();
    if($conexion)
    {
        ini_set('max_execution_time', 300);
        $usuariosRepositorio = new UsuariosRepositorio($conexion);
        $usuarios = array();
        $resultado = $usuariosRepositorio->consultar(null);
        if($resultado->correcto())
            $usuarios = $resultado->valor;
        else
            mensajeLog("error",$resultado->mensajeError);
        
        $resultado->valor = "";
        
       
        
        if($debug)
        {
            $usuarios = array();
            $resultado = $usuariosRepositorio->consultarPorLLaves((object) ['id'=>8]);
            if($resultado->correcto())
            {
                $usuario = $resultado->valor;
                $usuario->nombreUsuario = "alanbazan@apps-handel.com";
                array_push($usuarios,$usuario);
            }
            else 
                mensajeLog("error",$resultado->mensajeError);
            
            $resultado = $usuariosRepositorio->consultarPorLLaves((object) ['id'=>8]);
            if($resultado->correcto())
            {
                $usuario = $resultado->valor;
                $usuario->nombreUsuario = "eduardo@handel-sce.com";
                array_push($usuarios,$usuario);
            }
            else
                mensajeLog("error",$resultado->mensajeError);
        }
            
     
        
       $asunto ="¡Verificacion de correos eléctronico SAHA!";
        
        for ($i = 0; $i < count($usuarios); $i++)
        {
            $usuario = $usuarios[$i];
          
            $contenido = getContenido($usuario);
            
            $mensaje= file_get_contents('notificacion_prueba.html');
            $mensaje=  str_replace("@nombre",$usuario->nombre,$mensaje);
            $mensaje=  str_replace("@contenido",$contenido,$mensaje);
            
          
                $cabecera = "From:  SAHA <noreply@apps-handel.com>\r\n";
                $cabecera .= "Bcc: bitacora_correo@apps-handel.com\r\n";
                $cabecera .= "Content-type: text/html; charset=UTF-8\r\n";
                
                $errLevel = error_reporting(E_ALL ^ E_WARNING);
                $resultadoMail = true;
                $resultadoMail= mail($usuario->nombreUsuario, utf8_decode($asunto), $mensaje, $cabecera);
                error_reporting($errLevel);
                
                $error = error_get_last();
                
                if ( $error["type"] == E_WARNING)
                {
                    $resultado->mensajeError="[$i] No se pudo enviar el correo electrónico a $usuario->nombreUsuario.  ". htmlspecialchars_decode($error["message"]) ;
                    $resultado->codigoError = 3;
                    mensajeLog("error",$resultado->mensajeError);
                }
                else if($resultadoMail)
                {
                    $resultado->valor="OK";
                    mensajeLog("log_envio","[$i] Correo enviado a ".$usuario->nombreUsuario);
                }
            
          
              if($imprimierMensaje)
                  echo $mensaje;
        }
    }
    
}
catch(Exception $e)
{
    $resultado->mensajeError = $e->getMessage();
    mensajeLog("error",$resultado->mensajeError);
}
finally
{
    $administrador_conexion->cerrar($conexion);
//     if($resultado!=null)
//     {
//         $json = json_encode($resultado, JSON_UNESCAPED_UNICODE);
//         if (FALSE === $json)
//             echo '{"mensajeError":"' .json_last_error_msg() . '"}';
//             else
//                 echo $json;
//     }
   
}

function getAsunto($dia)
{
    switch($dia)
    {
        case 1:
            $asunto = "Envía tus evidencias";
            break;
        case 14:
            $asunto = "A dos semanas del cierre";
            break;
        case 21:
            $asunto = "!Quedan seis días! ";
            break;
        case 27:
             $asunto = " !Último día para subir evidencias a SAHA! ";
            break;
        default:
            $asunto = "!Recordatorio de evidencias! ";
            break;
    }
    return $asunto;
}

function getTextoConLogo($texto)
{
    return "<div style='text-align:center;width:100%'>
    <div style='text-align:center; display: inline-block; width:90%'>
    <table> 
    <tr>
    <td>
    <a href='http://saha.apps-handel.com'> <img src='https://api.apps-handel.com/images/logoSAHA.png' style='width:120px'></img></a>
    </td>
    <td>
    $texto
    </td>
    </tr>
    </table>
    </div>
    </div>";
}

function getCaricatura($caricatura)
{
 return " <br>
  <div style='text-align:center;width:100%'>
   <div style='text-align:center; display: inline-block; width:90%'>
	  <table style='display: inline-block;' border='0'>
		<tbody>
		  <tr>
		    <td  width='200px' rowspan='0'><img style='width:100px;' src='$caricatura'><img></td>
		  </tr>
		</tbody>
		</table>
	</div>
 </div>";   
}



function getContenido($usuario)
{
    $contenido ="";
            
    $contenido = getTextoConLogo("¡Hola! Estamos verificando las cuentas de correo electrónico.<br> No es necesario que realizar una acción.<br> Solo queremos asegurarnos de que la cuenta<label style='font-weight:bold'> $usuario->nombreUsuario</label> existe.");
    
    $contenido.="<div style='text-align:center;width:100%'>
                <div style='text-align:left; display: inline-block; width:90%'>";
    
    $contenido.= getCaricatura("https://api.apps-handel.com/images/caricatura/Little_Business_Girl-03.png");
    $contenido.= getGracias();
    $contenido .= "</div>
                    </div>";
    return $contenido;
   
}

function getGracias()
{
    return  "<div style='text-align:center;width:100%'>
 	 <div style='text-align:center; display: inline-block; width:90%'>
		<div style='text-align:center'>
			<label style='font-weight:bold;color:#004D7F;'>¡Gracias por tu colaboración!</label>
		</div>
	 </div>
 </div>";
}

function getValor($nombre,$arreglo)
{
    for ($i = 0; $i < count($arreglo); $i++) 
    {
        $elemento = $arreglo[$i];
        if($elemento->nombre==$nombre)
            return $elemento->valor;
    }
    return 0;
}

function getEvidenciasEnviadas($usuario,EvidenciasRepositorio $repositorio)
{
    $texto="";
    $resultado = $repositorio->consultarEvidenciasCumplidasMesActual($usuario,null);
    if($resultado->correcto())
    {
        $evidencias = $resultado->valor;
        if(count($evidencias)>0)
        {
            $texto = "<label style='text-decoration: underline; color: #157b14; font-weight:bold'>Recibimos estas evidencias:</label>";
            $texto.="<ul>";
            for ($i = 0; $i < count($evidencias); $i++)
            {
                $evidencia = $evidencias[$i];
                $texto.="<li>";
                $texto.=$evidencia->nombre;
                $texto.="</li>";
            }
            $texto.="</ul>";
        }
    }
    else
        mensajeLog("error",$resultado->mensajeError);
    return $texto;
}
    
function getEvidenciasPendientes($usuario,UsuariosProcedimientosRepositorio $repositorio)
{
    $texto="";
    $resultado = $repositorio->consultarProcedimientosPendientesMesActual($usuario,null);
    if($resultado->correcto())
    {
        $evidencias = $resultado->valor;
        if(count($evidencias)>0)
        {
            $texto = "<label style='text-decoration: underline; color: #b7200a; font-weight:bold'>Nuestros registros indican estas evidencias pendientes:</label>";
            $texto.="<ul>";
            for ($i = 0; $i < count($evidencias); $i++)
            {
                $evidencia = $evidencias[$i];
                $texto.="<li>";
                $texto.=$evidencia->nombre;
                $texto.="</li>";
            }
            $texto.="</ul>";
        }
    }
    else
        mensajeLog("error",$resultado->mensajeError);
   return $texto;
}

function getEvidencias($evidencias)
{
    $texto="";
    if(count($evidencias)>0)
    {
        $texto.="<ul>";
        for ($i = 0; $i < count($evidencias); $i++)
        {
            $evidencia = $evidencias[$i];
            $texto.="<li>";
            $texto.=$evidencia->nombre;
            $texto.="</li>";
        }
        $texto.="</ul>";
    }
    return $texto;
}

function getEvidenciasPendientesUsuario($usuario,UsuariosProcedimientosRepositorio $repositorio)
{
    $texto="";
    $resultado = $repositorio->consultarProcedimientosPendientesMesActual($usuario,null);
    if($resultado->correcto())
    {
        $evidencias = $resultado->valor;
        if(count($evidencias)>0)
        {
            $texto = "<label style='text-decoration: underline; color: #b7200a; font-weight:bold'>Nuestros registros indican estas evidencias pendientes:</label>";
            $texto.="<ul>";
            for ($i = 0; $i < count($evidencias); $i++)
            {
                $evidencia = $evidencias[$i];
                $texto.="<li>";
                $texto.=$evidencia->nombre;
                $texto.="</li>";
            }
            $texto.="</ul>";
        }
    }
    else
        mensajeLog("error",$resultado->mensajeError);
        return $texto;
}


function getUsuarios($usuario,UsuariosRepositorio $usuariosRepositorio,EvidenciasRepositorio $evidenciasRepositorio, UsuariosProcedimientosRepositorio $usuariosProcedimientosRepositorio)
{
    $texto="";
    $asociados = array();


    $resultado = $evidenciasRepositorio->consultarPorcentajesUsuariosMesActual($usuario,null);
    if($resultado->correcto())
    {
        $asociados =$resultado->valor;
    }
    else
        mensajeLog("error",$resultado->mensajeError);
    
    if(count($asociados)>0)
    {
        $texto .= "<label style=''>Detalle de evidencias:</label>";
        $texto.= "<br>";
        for ($i = 0; $i < count($asociados); $i++)
        {
            $asociado = $asociados[$i];
            $asociado->tipoUsuarioId = TipoUsuario::USUARIO;
            $texto.= "<br>".getEvidenciasAsociado($asociado,$evidenciasRepositorio,$usuariosProcedimientosRepositorio);
        }    
    }

    return $texto;
}

function getEvidenciasAsociado($usuario,EvidenciasRepositorio $evidenciasRepositorio, UsuariosProcedimientosRepositorio $usuariosProcedimientosRepositorio)
{
    $texto ="";
    
    $resultado = $usuariosProcedimientosRepositorio->consultarProcedimientosPendientesMesActual($usuario,null);
    $evidenciasPendientes  = array();
 
    if($resultado->correcto())
    {
        $evidenciasPendientes = $resultado->valor;
    }
    else
        mensajeLog("error",$resultado->mensajeError);
    
    $resultado = $evidenciasRepositorio->consultarEvidenciasCumplidasMesActual($usuario,null);
    $evidenciasCumplidas = array();
    if($resultado->correcto())
    {
        $evidenciasCumplidas = $resultado->valor;
    }
    else
        mensajeLog("error",$resultado->mensajeError);
    
     $color = "";
     $textoEvidencias="";
     $detalleEvidencias ="";
     if($usuario->porcentajeCumplimiento==0)
     {
         $color = "#ed220b";
         $textoEvidencias = "No ha subido ninguna de sus evidencias.";
     }
     if($usuario->porcentajeCumplimiento>0 && $usuario->porcentajeCumplimiento<51)
     {
         $color = "#ed220b";
         if(count($evidenciasPendientes)==1)
            $textoEvidencias .= "Tiene 1 evidencia faltante:";
         else
             $textoEvidencias .= "Tiene ".count($evidenciasPendientes)." evidencias faltantes:";
        
        $detalleEvidencias= getEvidencias($evidenciasPendientes);
        
     }
     else if($usuario->porcentajeCumplimiento >= 51 && $usuario->porcentajeCumplimiento < 100)
     {
         $color = "#ff9300";
         if(count($evidenciasPendientes)==1)
             $textoEvidencias .= "Tiene 1 evidencia faltante:";
             else
                 $textoEvidencias .= "Tiene ".count($evidenciasPendientes)." evidencias faltantes:";
         $detalleEvidencias .= getEvidencias($evidenciasPendientes);
     }
     else if($usuario->porcentajeCumplimiento >= 100)
     {
         $color = "#017000";
         $textoEvidencias = "No tiene evidencias pendientes!";
     }

     $texto.= "<br>
            <table border='0'>
              <tr>
              	<td style='width:25px;'><img src='https://api.apps-handel.com/$usuario->fotoPerfil' alt='' style='border-radius:50%;width:25px;'></td>
             	<td><label style='text-decoration: underline; color: $color; font-weight:bold'>$usuario->nombreCompleto:</label>
                <label style='margin-left:10px;background-color:$color ;color:white; font-weight:bold;'>".number_format($usuario->porcentajeCumplimiento, 1, '.', '')."%</label>
                </td>
                 
              </tr>
              <tr>
                <td></td>
              	<td>$textoEvidencias</td>
              </tr>
             <tr>
                <td></td>
              	<td>$detalleEvidencias</td>
              </tr>
            </table>";
           
     
     
//     $texto.= "<br><label style='text-decoration: underline; color: $color; font-weight:bold'>$usuario->nombreCompleto:</label>
//             <label style='margin-left:10px;background-color:$color ;color:white; font-weight:bold;'>".number_format($usuario->porcentajeCumplimiento, 1, '.', '')."%</label>";
    //$texto.=$textoEvidencias;
//     $texto.="<ul>";
    
//     $texto.="</ul>";
    
    return $texto;
    
}


function mensajeLog($archivo,$error)
{
    $error = date("j/n/Y h:m:s") .":".$error;
    file_put_contents('./'.$archivo.'_'.date("j.n.Y").'.log',  utf8_decode($error) , FILE_APPEND);
    echo "<br>".utf8_decode($error);
}
    