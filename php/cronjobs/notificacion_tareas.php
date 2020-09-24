<?php
use php\clases\AdministradorConexion;

use php\repositorios\UsuariosRepositorio;
use php\modelos\Resultado;
use php\repositorios\UsuariosProcedimientosRepositorio;
use php\repositorios\MinutasRepositorio;
use php\repositorios\TareasComentariosRepositorio;

error_reporting(E_ALL);
ini_set('display_errors', 1);


include '../clases/JsonMapper.php';
include '../clases/Utilidades.php';
include '../configuracion.php';
include '../clases/AdministradorConexion.php';
include '../clases/AdministradorCorreo.php';
include '../modelos/Usuario.php';
require_once('../clases/TipoUsuario.php');
require_once('../clases/DiaSemana.php');
require_once('../repositorios/UsuariosRepositorio.php');
require_once('../repositorios/MinutasRepositorio.php');
require_once('../repositorios/TareasComentariosRepositorio.php');
//require_once('../reportes/reporte_evidencias.php');

$origin = "*";
if(isset($_SERVER['HTTP_ORIGIN']))
    $origin =$_SERVER['HTTP_ORIGIN'];
header('Access-Control-Allow-Origin: '.$origin);
header('Content-Type: html; charset=UTF-8');
header('Access-Control-Allow-Credentials: true');
    
$debug = false;
$imprimirMensaje = true;
$numeroUsuarios = 3;
$tiempoEspera = 10;
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
        $minutasRepositorio = new MinutasRepositorio($conexion);
        $tareasComentariosRepositorio = new TareasComentariosRepositorio($conexion);
        $usuarios = array();

        $resultado = $minutasRepositorio->consultarUsuariosConTareas();
        if($resultado->correcto())
            $usuarios = $resultado->valor;
        else
            mensajeLog("error_envio_tareas",$resultado->mensajeError);
        mensajeLog("log_envio_tareas","Total: ". count($usuarios));
    
        
        $resultado->valor = "";
        
      
        $parametroDebug= REQUEST("debug");
        if($parametroDebug=="true")
            $debug=true;
        if($debug)
        {
            $nombreUsuario= REQUEST("nombreUsuario");
            if($nombreUsuario!="")
            {
              
                $resultado = $usuariosRepositorio->consultar(null,(object) ['nombreUsuario' =>  $nombreUsuario, 'permisoSAHA' => 1],false);
                if($resultado->correcto())
                    $usuarios = $resultado->valor;
            }
          
            $enviarA= REQUEST("enviarA");
            for ($i = 0; $i < count($usuarios); $i++) 
            {
                $usuario = $usuarios[$i];
                if(isset($enviarA) && $enviarA!="")
                    $usuario->nombreUsuario = $enviarA;
            }
            $usuarios = array_slice($usuarios,0,$numeroUsuarios);
   
        }
        
        $dia = REQUEST("dia");
        if($dia==null)
            $dia = date("w");
        
         if($dia == DiaSemana::JUEVES)
         {
            $asunto = getAsunto($dia);
            
//             $usuariosProcedimientosRepositorio = new UsuariosProcedimientosRepositorio($conexion);
//             $evidenciasRepositorio = new EvidenciasRepositorio($conexion);
          
            
            for ($i = 0; $i < count($usuarios); $i++)
            {
                $usuario = $usuarios[$i];
              
                $contenido = getContenido($conexion,$usuariosRepositorio,$minutasRepositorio, $tareasComentariosRepositorio,$usuario, $dia);
                $mensaje="";
                if($contenido!="")
                {
                    $mensaje= file_get_contents('notificacion_tareas.html');
                    $mensaje=  str_replace("@nombre",$usuario->nombre,$mensaje);
                    $mensaje=  str_replace("@contenido",$contenido,$mensaje);
                    
                  
                    $cabecera = "From:  SAHA <noreply@apps-handel.com>\r\n";
                    $cabecera .= "Content-type: text/html; charset=UTF-8\r\n";
                    
                    $errLevel = error_reporting(E_ALL ^ E_WARNING);
                    $resultadoMail = true;
                    $resultadoMail= mail($usuario->nombreUsuario,$asunto, $mensaje, $cabecera);
                    error_reporting($errLevel);
                    
                    $error = error_get_last();
                    
                    if ( $error["type"] == E_WARNING)
                    {
                        $resultado->mensajeError="No se pudo enviar el correo electrónico a $usuario->nombreUsuario.  ". htmlspecialchars_decode($error["message"]) ;
                        $resultado->codigoError = 3;
                        mensajeLog("error_envio_tareas",$i. " " .$resultado->mensajeError);
                    }
                    else if($resultadoMail)
                    {
                        $resultado->valor="OK";
                        mensajeLog("log_envio_tareas","$i Correo enviado a ".$usuario->nombreUsuario);
                    }
                    
                    guardarEnvio($usuario,$asunto,$mensaje);
                    
                    sleep($tiempoEspera);
                }
          
                if($imprimirMensaje)
                    echo $mensaje;
            }
            mensajeLog("log_envio_tareas","Termimado!");
        }
    }
    
}
catch(Exception $e)
{
    $resultado->mensajeError = $e->getMessage();
    mensajeLog("error_envio_tareas",$resultado->mensajeError);
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
    $asunto =  "Seguimiento semanal de tareas SAHA";
//     switch($dia)
//     {
//         case 1:
//             $asunto = "Envía tus evidencias";
//             break;
//         case 14:
//             $asunto = "A dos semanas del cierre";
//             break;
//         case 21:
//             $asunto = "!Quedan seis días!";
//             break;
//         case 27:
//              $asunto = "!Último día para subir evidencias a SAHA!";
//             break;
//         case 28:
//             $asunto = "¡El reporte del mes de SAHA está listo! ";
//            break;
//     }
    $asunto="=?UTF-8?B?".base64_encode($asunto)."?=";
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

function getCaricatura($caricatura,$width)
{
 return " <br>
  <div style='text-align:center;width:100%'>
   <div style='text-align:center; display: inline-block; width:90%'>
	  <table style='display: inline-block;' border='0'>
		<tbody>
		  <tr>
		    <td width='200px'> <a href='https://saha.apps-handel.com'><button style='width:120px;height:40px;color:white;border-radius:20px;background-color:#f8ba00;border: none;font-weight:bold'>Ingresar</button></a></td>
		    <td  width='200px' rowspan='0'><img style='width:".$width."px;' src='$caricatura'><img></td>
		  </tr>
		</tbody>
		</table>
	</div>
 </div>";   
}



function getContenido($conexion,UsuariosRepositorio $usuariosRepositorio,MinutasRepositorio $minutasRepositorio, TareasComentariosRepositorio $tareasComentariosRepositorio,$usuario,$dia)
{
    $contenido ="";
    $contenido = getContenidoUsuario($usuariosRepositorio,$minutasRepositorio,$tareasComentariosRepositorio,$usuario,$dia);
    return $contenido;
}



function getImageLink($titulo, $link)
{
    $html ="<div style='text-align:center; display: inline-block; width:90%'>";
    $html .= "<a href='$link'><button style='width:120px;height:40px;color:white;border-radius:20px;background-color:#3c8dbc;border: none;font-weight:bold'>$titulo</button></a>";
    $html .="</div>";
    return $html;
}

function getContenidoUsuario(UsuariosRepositorio $usuariosRepositorio,MinutasRepositorio $minutasRepositorio, TareasComentariosRepositorio $tareasComentariosRepositorio,$usuario,$dia)
{
    $contenido = "";
//     $ano =  date("Y",strtotime("-1 month"));
//     $mes = date("m",strtotime("-1 month"));
    
//     $criteriosSeleccion = (object) ['ano' => $ano, "mes"=> $mes];
    
//     $resultadoPorcentajes = $evidenciasRepositorio->consultarPorcentajesEvidencias($usuario, $criteriosSeleccion);
//     $porcentajeCumplimiento = 0;
//     if($resultadoPorcentajes->correcto())
//     {
//         $enviadas = getValor("Enviadas",$resultadoPorcentajes->valor);
//         $jusitificadas = getValor("Justificadas",$resultadoPorcentajes->valor);
//         $pendientes = getValor("Pendientes",$resultadoPorcentajes->valor);
//         $total =$enviadas +  $jusitificadas + $pendientes;
//         $cumplidas = $enviadas +  $jusitificadas;
//         if($total!=0)
//             $porcentajeCumplimiento = $cumplidas *100 / $total;
            
//     }
//     else
//     {
//         mensajeLog("error_envio_tareas", $resultadoPorcentajes->mensajeError);
//     }
    
    $contenido.="Tomemos unos minutos para recordar el avance a las tareas de SAHA durante esta semana:
                <br>";
    

//     $contenido.="<div style='text-align:center;width:100%'>";"
//                 <div style='text-align:left; display: inline-block; width:90%'>";
    
//     $contenido .= "<br>Agradecemos tu colaboración para tener el mejor desempeño de la compañía con la certiﬁcación.";
//     $contenido.= "<br><br><label>El mes anterior tu cumplimiento de evidencias fue $porcentajeCumplimiento%</label>";
//     $contenido.= getCaricatura("https://api.apps-handel.com/images/caricatura/Little_Business_Girl-03.png",100);
//     $contenido.= "<br><br>PD<br><label style=' font-style: italic;'>No olvides que puedes utilizar las apps de IOS y Android para facilitar la subida de tus evidencias.</label>";
//     $contenido .= "</div>
//                     </div>";
//     break;

    
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

// function getEvidenciasEnviadas($usuario,EvidenciasRepositorio $repositorio)
// {
//     $texto="";
//     $ano=  date("Y");
//     $mes = date("m");
//     $criteriosSeleccion = (object) ['ano' => $ano, "mes"=> $mes];
//     $resultado = $repositorio->consultarEvidenciasCumplidas($usuario,$criteriosSeleccion);
//     if($resultado->correcto())
//     {
//         $evidencias = $resultado->valor;
//         if(count($evidencias)>0)
//         {
//             $texto = "<label style='text-decoration: underline; color: #157b14; font-weight:bold'>Recibimos estas evidencias:</label>";
//             $texto.="<ul>";
//             for ($i = 0; $i < count($evidencias); $i++)
//             {
//                 $evidencia = $evidencias[$i];
//                 $texto.="<li>";
//                 $texto.=$evidencia->nombre;
//                 $texto.="</li>";
//             }
//             $texto.="</ul>";
//         }
//     }
//     else
//         mensajeLog("error_envio_tareas",$resultado->mensajeError);
//     return $texto;
// }
    
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
        mensajeLog("error_envio_tareas",$resultado->mensajeError);
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
        mensajeLog("error_envio_tareas",$resultado->mensajeError);
        return $texto;
}


// function getUsuarios($usuario,UsuariosRepositorio $usuariosRepositorio,EvidenciasRepositorio $evidenciasRepositorio, UsuariosProcedimientosRepositorio $usuariosProcedimientosRepositorio)
// {
//     $texto="";
//     $asociados = array();
//     $ano=  date("Y");
//     $mes = date("m");
//     $criteriosSeleccion = (object) ['ano' => $ano, "mes"=> $mes];

//     $resultado = $evidenciasRepositorio->consultarPorcentajesUsuarios($usuario,$criteriosSeleccion);
//     if($resultado->correcto())
//     {
//         $asociados =$resultado->valor;
//     }
//     else
//         mensajeLog("error_envio_tareas",$resultado->mensajeError);
    
//     if(count($asociados)>0)
//     {
//         $texto .= "<label style=''>Detalle de evidencias:</label>";
//         $texto.= "<br>";
//         for ($i = 0; $i < count($asociados); $i++)
//         {
//             $asociado = $asociados[$i];
//             $asociado->tipoUsuarioId = TipoUsuario::USUARIO;
//             $texto.= "<br>".getEvidenciasAsociado($asociado,$evidenciasRepositorio,$usuariosProcedimientosRepositorio);
//         }    
//     }

//     return $texto;
// }

// function getEvidenciasAsociado($usuario,EvidenciasRepositorio $evidenciasRepositorio, UsuariosProcedimientosRepositorio $usuariosProcedimientosRepositorio)
// {
//     $texto ="";
    
//     $resultado = $usuariosProcedimientosRepositorio->consultarProcedimientosPendientesMesActual($usuario,null);
//     $evidenciasPendientes  = array();
 
//     if($resultado->correcto())
//     {
//         $evidenciasPendientes = $resultado->valor;
//     }
//     else
//         mensajeLog("error_envio_tareas",$resultado->mensajeError);
    
// //     $resultado = $evidenciasRepositorio->consultarEvidenciasCumplidasMesActual($usuario,null);
// //     $evidenciasCumplidas = array();
// //     if($resultado->correcto())
// //     {
// //         $evidenciasCumplidas = $resultado->valor;
// //     }
// //     else
// //         mensajeLog("error_envio_tareas",$resultado->mensajeError);
    
//      $color = "";
//      $textoEvidencias="";
//      $detalleEvidencias ="";
//      if($usuario->porcentajeCumplimiento==0)
//      {
//          $color = "#ed220b";
//          $textoEvidencias = "No ha subido ninguna de sus evidencias.";
//      }
//      if($usuario->porcentajeCumplimiento>0 && $usuario->porcentajeCumplimiento<51)
//      {
//          $color = "#ed220b";
//          if(count($evidenciasPendientes)==1)
//             $textoEvidencias .= "Tiene 1 evidencia faltante:";
//          else
//              $textoEvidencias .= "Tiene ".count($evidenciasPendientes)." evidencias faltantes:";
        
//         $detalleEvidencias= getEvidencias($evidenciasPendientes);
        
//      }
//      else if($usuario->porcentajeCumplimiento >= 51 && $usuario->porcentajeCumplimiento < 100)
//      {
//          $color = "#ff9300";
//          if(count($evidenciasPendientes)==1)
//              $textoEvidencias .= "Tiene 1 evidencia faltante:";
//              else
//                  $textoEvidencias .= "Tiene ".count($evidenciasPendientes)." evidencias faltantes:";
//          $detalleEvidencias .= getEvidencias($evidenciasPendientes);
//      }
//      else if($usuario->porcentajeCumplimiento >= 100)
//      {
//          $color = "#017000";
//          $textoEvidencias = "No tiene evidencias pendientes!";
//      }

//      $texto.= "<br>
//             <table border='0'>
//               <tr>
//               	<td style='width:25px;'><img src='https://api.apps-handel.com/$usuario->fotoPerfil' alt='' style='border-radius:50%;width:25px;'></td>
//              	<td><label style='text-decoration: underline; color: $color; font-weight:bold'>$usuario->nombreCompleto:</label>
//                 <label style='margin-left:10px;background-color:$color ;color:white; font-weight:bold;'>".number_format($usuario->porcentajeCumplimiento, 1, '.', '')."%</label>
//                 </td>
                 
//               </tr>
//               <tr>
//                 <td></td>
//               	<td>$textoEvidencias</td>
//               </tr>
//              <tr>
//                 <td></td>
//               	<td>$detalleEvidencias</td>
//               </tr>
//             </table>";
           
     
    
//     return $texto;
    
// }


function mensajeLog($archivo,$mensaje)
{
    $mensaje = date("j/n/Y h:i:s") .":".$mensaje;
    $carpeta = "envios_tareas/";
    if(!file_exists($carpeta))
        @mkdir($carpeta);
    file_put_contents($carpeta.$archivo.'_'.date("j.n.Y").'.log',  utf8_decode("\n".$mensaje) , FILE_APPEND);
    echo "<br>".utf8_decode($mensaje);
}

function guardarEnvio($usuario, $asunto, $mensaje)
{
    $carpeta = "envios_tareas/".date("j.n.Y")."/";
    if(!file_exists($carpeta))
        @mkdir($carpeta);
    
 //       $archivo = $carpeta . $usuario->nombreUsuario
    file_put_contents($carpeta.$usuario->nombreUsuario.".html",  $mensaje , FILE_TEXT);
    
}
    