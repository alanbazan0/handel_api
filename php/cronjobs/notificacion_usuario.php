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
        array_push($usuarios,(object) ['id'=>261,'nombreUsuario' => 'alanbazan@apps-handel.com','nombre' => 'Alan', 'tipoUsuarioId' => TipoUsuario::USUARIO]);
       // array_push($usuarios,(object) ['nombreUsuario' => 'eduardo@handel-sce.com','nombreCompleto' => 'Eduardo']);
        
        $dia = REQUEST("dia");
        if($dia==null)
            $dia = date("j");
//         $titulo ="¡Recordatorio de evidencias!";
//         $texto ="";
//         $asunto ="¡Recordatorio de evidencias!";
//         $caricatura = "https://api.apps-handel.com/images/caricatura/Bonus_Shapes_and_Backgounds-05.png";
//         $diaLimite = 27;
        //$dia = 1;
        
        
     
      
        
       $asunto = getAsunto($dia);
        
        $usuariosProcedimientosRepositorio = new UsuariosProcedimientosRepositorio($conexion);
        $evidenciasRepositorio = new EvidenciasRepositorio($conexion);
        
        for ($i = 0; $i < count($usuarios); $i++)
        {
            $usuario = $usuarios[$i];
          
            $contenido = getContenido($usuariosProcedimientosRepositorio, $evidenciasRepositorio,$usuario, $dia);
            
            $mensaje= file_get_contents('notificacion_usuario.html');
            $mensaje=  str_replace("@nombre",$usuario->nombre,$mensaje);
            $mensaje=  str_replace("@contenido",$contenido,$mensaje);
            
//             $cabecera = "From:  SAHA <noreply@apps-handel.com>\r\n";
//             $cabecera .= "Content-type: text/html; charset=UTF-8\r\n";
            
//             $errLevel = error_reporting(E_ALL ^ E_WARNING);
//             $resultadoMail = true;
//             $resultadoMail= mail($usuario->nombreUsuario, utf8_decode($asunto), $mensaje, $cabecera);
//             error_reporting($errLevel);
            
//             $error = error_get_last();
            
//             if ( $error["type"] == E_WARNING)
//             {
//                 $resultado->mensajeError="No se pudo enviar el correo electrónico." . htmlspecialchars_decode($error["message"]) ;
//                 $resultado->codigoError = 3;
//                 file_put_contents('./log_'.date("j.n.Y").'.log',  $resultado->mensajeError , FILE_APPEND);
//             }
//             else if($resultadoMail)
//             {
//                 $resultado->valor="OK";
//             }
          
            echo $mensaje;
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
//             $caricatura = "https://api.apps-handel.com/images/caricatura/Little_Business_Girl-03.png";
//             $texto = "¡Hola! SAHA se encuentra abierto desde este momento para recibir las evidencias del mes, es importante que tomes unos minutos para identiﬁcarlas, organizarlas y subirlas así evitando olvidar subirlas después.";
            
            break;
        case 14:
            $asunto = "A dos semanas del cierre";
//             $caricatura = "https://api.apps-handel.com/images/caricatura/Little_Business_Girl-46.png";
//             $texto = "Este es un recordatorio de SAHA, en caso de que tengas evidencias por cumplir, por favor considera que quedan menos de 2 semanas para poder enviarlas";
//             $pie =  "<label style='font-weight:bold;color:#004D7F;'>¡Gracias por tu colaboración!</label>";
            break;
        case 21:
            $asunto = "!Quedan seis días! ";
//             $caricatura = "https://api.apps-handel.com/images/caricatura/Little_Business_Girl-32.png";
//             $texto = "El tiempo pasa volando; trabajo, reuniones, reportes, es fácil olvidar algunas tareas durante el mes, que SAHA no sea una de ellas. Estamos en esa parte del mes que llega el recordatorio de 6 días, la fecha límite se acerca pero aún estas a tiempo de poder cumplir";
//             $pie =  "<label style='font-weight:bold;color:#004D7F;'>¡Gracias por tu colaboración!</label>";
            break;
        case 27:
             $asunto = " !Último día para subir evidencias a SAHA! ";
//             $caricatura = "https://api.apps-handel.com/images/caricatura/Little_Business_Girl-46.png";
//             $texto = '!Hola!, en SAHA queremos que cada mes estes al 100%, por ello queremos recordarte que es el ultimo día para subir evidencias, luego de hoy el sistema no recibirá más evidencias, es importante que tomes unos minutos para veriﬁcar las evidencias enviadas y en caso de que aun te falten evidencias subirlas. Como siempre agradecemos tu compromiso para con este proyecto.';
//             $pie =  "Es el ultimo día y la ultima oportunidad para completar la subida de evidencias de tu equipo.
//                         <br>
//                         <br>
//                         <label style='font-weight:bold;color:#004D7F;'>¡Gracias por tu colaboración!</label>";
            break;
        default:
            $asunto = "!Recordatorio de evidencias! ";
//             $diasRestantes = $diaLimite-$dia+1;
//             $caricatura = "https://api.apps-handel.com/images/caricatura/Little_Business_Girl-46.png";
//             $texto ="Este es un recordatorio de que tienes $diasRestantes días para subir tus evidencias de cumplimiento a SAHA, es importante tomes unos minutos para subir";
//             $pie ="";
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
		    <td width='200px'> <a href='https://saha.apps-handel.com'><button style='width:120px;height:40px;color:white;border-radius:20px;background-color:#f8ba00;border: none;font-weight:bold'>Ingresar</button></a></td>
		    <td  width='200px' rowspan='0'><img style='width:100px;' src='$caricatura'><img></td>
		  </tr>
		</tbody>
		</table>
	</div>
 </div>";   
}

function getTexto(UsuariosProcedimientosRepositorio $usuariosProcedimientosRepositorio,EvidenciasRepositorio $evidenciasRepositorio,$usuario,$dia)
{
    $contenido ="";
    switch($dia)
    {
        case 1:
            
            
            $anoAnterior =  date("Y",strtotime("-1 month"));
            $mesAnterior = date("m",strtotime("-1 month"));
            //             $anoAnterior =2019;
            //             $mesAnterior = 9;
            $criteriosSeleccion = (object) ['ano' => $anoAnterior, "mes"=> $mesAnterior];
            
            $resultadoPorcentajes = $evidenciasRepositorio->consultarPorcentajesEvidencias($usuario, $criteriosSeleccion);
            if($resultadoPorcentajes->correcto())
            {
                $enviadas = getValor("Enviadas",$resultadoPorcentajes->valor);
                $jusitificadas = getValor("Justificadas",$resultadoPorcentajes->valor);
                $pendientes = getValor("Pendientes",$resultadoPorcentajes->valor);
                $total =$enviadas +  $jusitificadas + $pendientes;
                $cumplidas = $enviadas +  $jusitificadas;
                $porcentajeCumplimiento =0;
                if($total!=0)
                    $porcentajeCumplimiento = $cumplidas *100 / $total;
                    $contenido.= "<br><br>
                              <label>El mes anterior tu cumplimiento de evidencias fue: $porcentajeCumplimiento%</label>";
                    
                    
            }
            
          
            
            $contenido = "Este es un recordatorio de SAHA, a la fecha tu cumplimiento es <cumplimiento>%, en caso de que tengas evidencias por cumplir, por favor considera que quedan menos de 2 semanas para poder enviarlas";
            
            
            
            break;
        default:
           
        break;
    }
    return $contenido;
    
}


function getContenido(UsuariosProcedimientosRepositorio $usuariosProcedimientosRepositorio,EvidenciasRepositorio $evidenciasRepositorio,$usuario,$dia)
{
    $contenido ="";
    
   
    switch($dia)
    {
        case 1:
            
         
            
            $ano =  date("Y",strtotime("-1 month"));
            $mes = date("m",strtotime("-1 month"));
            
            $criteriosSeleccion = (object) ['ano' => $ano, "mes"=> $mes];
            
            $resultadoPorcentajes = $evidenciasRepositorio->consultarPorcentajesEvidencias($usuario, $criteriosSeleccion);
            $porcentajeCumplimiento = 0;
            if($resultadoPorcentajes->correcto())
            {
                $enviadas = getValor("Enviadas",$resultadoPorcentajes->valor);
                $jusitificadas = getValor("Justificadas",$resultadoPorcentajes->valor);
                $pendientes = getValor("Pendientes",$resultadoPorcentajes->valor);
                $total =$enviadas +  $jusitificadas + $pendientes;
                $cumplidas = $enviadas +  $jusitificadas;
                if($total!=0)
                     $porcentajeCumplimiento = $cumplidas *100 / $total;
               
            }
            else 
            {
                echo $resultadoPorcentajes->mensajeError;
                file_put_contents('./log_'.date("j.n.Y").'.log',  $resultadoPorcentajes->mensajeError , FILE_APPEND);
            }
            
            
            $contenido = getTextoConLogo("¡Hola! SAHA se encuentra abierto desde este momento para recibir las evidencias del mes, es importante que tomes unos minutos para identiﬁcarlas, organizarlas y subirlas así evitando olvidar subirlas después.");
            
            $contenido.="<div style='text-align:center;width:100%'>
                        <div style='text-align:left; display: inline-block; width:90%'>";
            
            $contenido .= "<br>Agradecemos tu colaboración para tener el mejor desempeño de la compañía con la certiﬁcación.";
            $contenido.= "<br><br><label>El mes anterior tu cumplimiento de evidencias fue $porcentajeCumplimiento%</label>";
            $contenido.= getCaricatura("https://api.apps-handel.com/images/caricatura/Little_Business_Girl-03.png");
            $contenido.= "<br><br>PD<br><label style=' font-style: italic;'>No olvides que puedes utilizar las apps de IOS y Android para facilitar la subida de tus evidencias.</label>";
            $contenido .= "</div>
                            </div>";
        break;
        case 14:
            $ano=  date("Y");
            $mes = date("m");
            $criteriosSeleccion = (object) ['ano' => $ano, "mes"=> $mes];
            $resultadoPorcentajes = $evidenciasRepositorio->consultarPorcentajesEvidencias($usuario, $criteriosSeleccion);
            $porcentajeCumplimiento = 0;
            if($resultadoPorcentajes->correcto())
            {
                $enviadas = getValor("Enviadas",$resultadoPorcentajes->valor);
                $jusitificadas = getValor("Justificadas",$resultadoPorcentajes->valor);
                $pendientes = getValor("Pendientes",$resultadoPorcentajes->valor);
                $total =$enviadas +  $jusitificadas + $pendientes;
                $cumplidas = $enviadas +  $jusitificadas;
                if($total!=0)
                    $porcentajeCumplimiento = $cumplidas *100 / $total;
                    
            }
            else
            {
                echo $resultadoPorcentajes->mensajeError;
                file_put_contents('./log_'.date("j.n.Y").'.log',  $resultadoPorcentajes->mensajeError , FILE_APPEND);
            }
            
            
            $contenido .= getTextoConLogo("Este es un recordatorio de SAHA, a la fecha tu cumplimiento es $porcentajeCumplimiento%, en caso de que tengas evidencias por cumplir, por favor considera que quedan menos de 2 semanas para poder enviarlas.");
            
            $contenido.="<div style='text-align:center;width:100%'>
                        <div style='text-align:left; display: inline-block; width:90%'>";
            
            
            $contenido .= "<br>".getEvidenciasEnviadas($usuario,$evidenciasRepositorio);
            $contenido .= "<br>".getEvidenciasPendientes($usuario,$usuariosProcedimientosRepositorio);
            $contenido .= "<br>".getCaricatura("https://api.apps-handel.com/images/caricatura/Little_Business_Girl-46.png");
            $contenido .= "<br>".getGracias();
            
            $contenido .= "</div>
                            </div>";
            
        break;
        
        case 21:
            $ano=  date("Y");
            $mes = date("m");
            $criteriosSeleccion = (object) ['ano' => $ano, "mes"=> $mes];
            $resultadoPorcentajes = $evidenciasRepositorio->consultarPorcentajesEvidencias($usuario, $criteriosSeleccion);
            $porcentajeCumplimiento = 0;
            if($resultadoPorcentajes->correcto())
            {
                $enviadas = getValor("Enviadas",$resultadoPorcentajes->valor);
                $jusitificadas = getValor("Justificadas",$resultadoPorcentajes->valor);
                $pendientes = getValor("Pendientes",$resultadoPorcentajes->valor);
                $total =$enviadas +  $jusitificadas + $pendientes;
                $cumplidas = $enviadas +  $jusitificadas;
                if($total!=0)
                    $porcentajeCumplimiento = $cumplidas *100 / $total;
                    
            }
            else
            {
                echo $resultadoPorcentajes->mensajeError;
                file_put_contents('./log_'.date("j.n.Y").'.log',  $resultadoPorcentajes->mensajeError , FILE_APPEND);
            }
            
            
            $contenido .= getTextoConLogo("El tiempo pasa volando; trabajo, reuniones, reportes, es fácil olvidar algunas tareas durante el mes, que SAHA no sea una de ellas. Estamos en esa parte del mes que llega el recordatorio de 6 días, la fecha limite se acerca pero aun estas a tiempo de poder cumplir. ");
            
            $contenido.="<div style='text-align:center;width:100%'>
                        <div style='text-align:left; display: inline-block; width:90%'>";
            
            $contenido.= "<br><br><label>Al día de hoy tu cumplimiento es $porcentajeCumplimiento%</label>";
            $contenido .= "<br><br>".getEvidenciasEnviadas($usuario,$evidenciasRepositorio);
            $contenido .= "<br>".getEvidenciasPendientes($usuario,$usuariosProcedimientosRepositorio);
            $contenido .= "<br>".getCaricatura("https://api.apps-handel.com/images/caricatura/Little_Business_Girl-46.png");
            $contenido .= "<br>".getGracias();
            
            $contenido .= "</div>
                            </div>";
            
            
            break;
        case 27:
            $ano=  date("Y");
            $mes = date("m");
            $criteriosSeleccion = (object) ['ano' => $ano, "mes"=> $mes];
            $resultadoPorcentajes = $evidenciasRepositorio->consultarPorcentajesEvidencias($usuario, $criteriosSeleccion);
            $porcentajeCumplimiento = 0;
            if($resultadoPorcentajes->correcto())
            {
                $enviadas = getValor("Enviadas",$resultadoPorcentajes->valor);
                $jusitificadas = getValor("Justificadas",$resultadoPorcentajes->valor);
                $pendientes = getValor("Pendientes",$resultadoPorcentajes->valor);
                $total =$enviadas +  $jusitificadas + $pendientes;
                $cumplidas = $enviadas +  $jusitificadas;
                if($total!=0)
                    $porcentajeCumplimiento = $cumplidas *100 / $total;
                    
            }
            else
            {
                echo $resultadoPorcentajes->mensajeError;
                file_put_contents('./log_'.date("j.n.Y").'.log',  $resultadoPorcentajes->mensajeError , FILE_APPEND);
            }
            
            
            $contenido .= getTextoConLogo("!Hola!, en SAHA queremos que cada mes estes al 100%, por ello queremos recordarte que es el último día para subir evidencias, luego de hoy el sistema no recibirá más evidencias, es importante que tomes unos minutos para veriﬁcar las evidencias enviadas y en caso de que aún te falten evidencias subirlas. Como siempre agradecemos tu compromiso para con este proyecto.");
            
            $contenido.="<div style='text-align:center;width:100%'>
                        <div style='text-align:left; display: inline-block; width:90%'>";
            
            $contenido.= "<br><br><label>Al día de hoy tu cumplimiento es $porcentajeCumplimiento%</label>";
            $contenido .= "<br><br>".getEvidenciasEnviadas($usuario,$evidenciasRepositorio);
            $contenido .= "<br>".getEvidenciasPendientes($usuario,$usuariosProcedimientosRepositorio);
            $contenido .= "<br>".getCaricatura("https://api.apps-handel.com/images/caricatura/Little_Business_Girl-46.png");
            $contenido .= "<br>".getGracias();
            
            $contenido .= "</div>
                            </div>";
            
            
            break;
    }
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
        file_put_contents('./log_'.date("j.n.Y").'.log',  $resultado->mensajeError , FILE_APPEND);
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
        file_put_contents('./log_'.date("j.n.Y").'.log',  $resultado->mensajeError , FILE_APPEND);
        return $texto;
}
    