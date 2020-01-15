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
        $asociados = array();
        $supervisores = array();
        $coordinadores= array();
        
        $resultado = $usuariosRepositorio->consultar(null,(object) ['tipoUsuarioId' =>  TipoUsuario::USUARIO, 'permisoSAHA' => 1],false);
        if($resultado->correcto())
            $asociados = $resultado->valor;
        else
            mensajeLog("error",$resultado->mensajeError);
        
            $resultado = $usuariosRepositorio->consultar(null,(object) ['tipoUsuarioId' =>  TipoUsuario::SUPERVISOR, 'permisoSAHA' => 1],false);
        if($resultado->correcto())
            $supervisores = $resultado->valor;
        else
            mensajeLog("error",$resultado->mensajeError);
        
            $resultado = $usuariosRepositorio->consultar(null,(object) ['tipoUsuarioId' =>  TipoUsuario::COORDINADOR, 'permisoSAHA' => 1],false);
        if($resultado->correcto())
            $coordinadores = $resultado->valor;
        else
            mensajeLog("error",$resultado->mensajeError);
        
        $resultado->valor = "";
        
        $usuarios = array_merge($asociados, $supervisores,$coordinadores);
        
        if($debug)
        {
            
            for ($i = 0; $i < count($usuarios); $i++) 
            {
                $usuario = $usuarios[$i];
                //$usuario->tipoUsuarioId = TipoUsuario::SUPERVISOR;
                //$usuario->id = 8;
                //$usuario->nombre ="Magaly";
                $usuario->nombreUsuario =  "alanbazan@apps-handel.com";
            }
            
            $usuarios = array_slice($usuarios,0,$numeroUsuarios);
            
//             $usuarios = array();
//             $resultado = $usuariosRepositorio->consultarPorLLaves((object) ['id'=>8]);
//             if($resultado->correcto())
//             {
//                 $usuario = $resultado->valor;
//                 $usuario->nombreUsuario = "alanbazan@apps-handel.com";
//                 array_push($usuarios,$usuario);
                
//             }
//             else 
//                 mensajeLog("error",$resultado->mensajeError);
            
//             $resultado = $usuariosRepositorio->consultarPorLLaves((object) ['id'=>8]);
//             if($resultado->correcto())
//             {
//                 $usuario = $resultado->valor;
//                 $usuario->nombreUsuario = "eduardo@handel-sce.com";
//                 array_push($usuarios,$usuario);
//             }
//             else
//                 mensajeLog("error",$resultado->mensajeError);
            
//             $resultado = $usuariosRepositorio->consultarPorLLaves((object) ['id'=>8]);
//             if($resultado->correcto())
//             {
//                 $usuario = $resultado->valor;
//                 $usuario->nombreUsuario = "noemi@handel-sce.com";
//                 array_push($usuarios,$usuario);
//             }
//             else
//                 mensajeLog("error",$resultado->mensajeError);
        }
            
        
        $dia = REQUEST("dia");
        if($dia==null)
            $dia = date("j");
        
         if($dia ==1 || $dia ==14  || $dia ==21 || $dia ==27 || $dia ==28)
         {
            $asunto = getAsunto($dia);
            
            $usuariosProcedimientosRepositorio = new UsuariosProcedimientosRepositorio($conexion);
            $evidenciasRepositorio = new EvidenciasRepositorio($conexion);
            
            for ($i = 0; $i < count($usuarios); $i++)
            {
                $usuario = $usuarios[$i];
              
                $contenido = getContenido($usuariosRepositorio,$usuariosProcedimientosRepositorio, $evidenciasRepositorio,$usuario, $dia);
                $mensaje="";
                if($contenido!="")
                {
                    $mensaje= file_get_contents('notificacion.html');
                    $mensaje=  str_replace("@nombre",$usuario->nombre,$mensaje);
                    $mensaje=  str_replace("@contenido",$contenido,$mensaje);
                    
                  
                    $cabecera = "From:  SAHA <noreply@apps-handel.com>\r\n";
                    $cabecera .= "Content-type: text/html; charset=UTF-8\r\n";
                    
                    $errLevel = error_reporting(E_ALL ^ E_WARNING);
                    $resultadoMail = true;
                    $resultadoMail= mail($usuario->nombreUsuario, utf8_decode($asunto), $mensaje, $cabecera);
                    error_reporting($errLevel);
                    
                    $error = error_get_last();
                    
                    if ( $error["type"] == E_WARNING)
                    {
                        $resultado->mensajeError="No se pudo enviar el correo electrónico a $usuario->nombreUsuario.  ". htmlspecialchars_decode($error["message"]) ;
                        $resultado->codigoError = 3;
                        mensajeLog("error",$resultado->mensajeError);
                    }
                    else if($resultadoMail)
                    {
                        $resultado->valor="OK";
                        mensajeLog("log_envio","Correo enviado a ".$usuario->nombreUsuario);
                    }
                    
                    sleep($tiempoEspera);
                }
          
             if($imprimirMensaje)
                echo $mensaje;
            }
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
            $asunto = "!Quedan seis días!";
            break;
        case 27:
             $asunto = "!Último día para subir evidencias a SAHA!";
            break;
        case 28:
            $asunto = "¡El reporte del mes de SAHA está listo! ";
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



function getContenido(UsuariosRepositorio $usuariosRepositorio,UsuariosProcedimientosRepositorio $usuariosProcedimientosRepositorio,EvidenciasRepositorio $evidenciasRepositorio,$usuario,$dia)
{
    $contenido ="";
    if($usuario->tipoUsuarioId == TipoUsuario::COORDINADOR)
        $contenido = getContenidoCoordinador($usuariosRepositorio,$usuariosProcedimientosRepositorio,$evidenciasRepositorio,$usuario,$dia);
    if($usuario->tipoUsuarioId == TipoUsuario::SUPERVISOR)
        $contenido = getContenidoSupervisor($usuariosRepositorio,$usuariosProcedimientosRepositorio,$evidenciasRepositorio,$usuario,$dia);
    else if($usuario->tipoUsuarioId == TipoUsuario::USUARIO)
        $contenido = getContenidoUsuario($usuariosRepositorio,$usuariosProcedimientosRepositorio,$evidenciasRepositorio,$usuario,$dia);
   
    return $contenido;
   
}

function getContenidoCoordinador(UsuariosRepositorio $usuariosRepositorio,UsuariosProcedimientosRepositorio $usuariosProcedimientosRepositorio,EvidenciasRepositorio $evidenciasRepositorio,$usuario,$dia)
{
    $contenido = "";
    switch($dia)
    {
        case 28:
            $contenido .= getTextoConLogo("Es el ultimo recordatorio del mes, este es para agradecerte el apoyo constante y adjuntar a este correo el reporte que muestra el estado de trabajo del mes de tu equipo; por favor toma unos minutos para retroalimentar a tu equipo de trabajo utilizando el sistema de mensajes de SAHA.");
            
            $contenido.="<div style='text-align:center;width:100%'>
                        <div style='text-align:left; display: inline-block; width:90%'>";
            
           
            $contenido .= "<br>".getCaricatura("https://api.apps-handel.com/images/caricatura/Little_Business_Girl-78.png",160);
            $contenido .= "<br>".getGracias();
            $contenido .= "</div>
                            </div>";
            
        break;
            
    }
    return $contenido;
}

function getContenidoUsuario(UsuariosRepositorio $usuariosRepositorio,UsuariosProcedimientosRepositorio $usuariosProcedimientosRepositorio,EvidenciasRepositorio $evidenciasRepositorio,$usuario,$dia)
{
    $contenido = "";
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
                mensajeLog("error", $resultadoPorcentajes->mensajeError);
            }
            
            
            $contenido = getTextoConLogo("¡Hola! SAHA se encuentra abierto desde este momento para recibir las evidencias del mes, es importante que tomes unos minutos para identiﬁcarlas, organizarlas y subirlas así evitando olvidar enviarlas después.");
            
            $contenido.="<div style='text-align:center;width:100%'>
                        <div style='text-align:left; display: inline-block; width:90%'>";
            
            $contenido .= "<br>Agradecemos tu colaboración para tener el mejor desempeño de la compañía con la certiﬁcación.";
            $contenido.= "<br><br><label>El mes anterior tu cumplimiento de evidencias fue $porcentajeCumplimiento%</label>";
            $contenido.= getCaricatura("https://api.apps-handel.com/images/caricatura/Little_Business_Girl-03.png",100);
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
                mensajeLog("error", $resultadoPorcentajes->mensajeError);
            }
            
            
            $contenido .= getTextoConLogo("Este es un recordatorio de SAHA, a la fecha tu cumplimiento es $porcentajeCumplimiento%, en caso de que tengas evidencias por cumplir, por favor considera que quedan menos de 2 semanas para poder enviarlas.");
            
            $contenido.="<div style='text-align:center;width:100%'>
                        <div style='text-align:left; display: inline-block; width:90%'>";
            
            
            $contenido .= "<br>".getEvidenciasEnviadas($usuario,$evidenciasRepositorio);
            $contenido .= "<br>".getEvidenciasPendientes($usuario,$usuariosProcedimientosRepositorio);
            $contenido .= "<br>".getCaricatura("https://api.apps-handel.com/images/caricatura/Little_Business_Girl-46.png",100);
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
                mensajeLog("error", $resultadoPorcentajes->mensajeError);
            }
            
            
            $contenido .= getTextoConLogo("El tiempo pasa volando; trabajo, reuniones, reportes, es fácil olvidar algunas tareas durante el mes, que SAHA no sea una de ellas.
                                Estamos en esa parte del mes que llega el recordatorio de 6 días, la fecha límite se acerca pero aún estás a tiempo de poder cumplir. ");
            
            $contenido.="<div style='text-align:center;width:100%'>
                        <div style='text-align:left; display: inline-block; width:90%'>";
            
            $contenido.= "<br><br><label>Al día de hoy tu cumplimiento es $porcentajeCumplimiento%</label>";
            $contenido .= "<br><br>".getEvidenciasEnviadas($usuario,$evidenciasRepositorio);
            $contenido .= "<br>".getEvidenciasPendientes($usuario,$usuariosProcedimientosRepositorio);
            $contenido .= "<br>".getCaricatura("https://api.apps-handel.com/images/caricatura/Little_Business_Girl-46.png",100);
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
                mensajeLog("error", $resultadoPorcentajes->mensajeError);
            }
            
            
            $contenido .= getTextoConLogo("!Hola!, en SAHA queremos que cada mes estes al 100%, por ello queremos recordarte que es el último día para subir evidencias,
                                            luego de hoy el sistema no recibirá más, es importante que tomes unos minutos para veriﬁcar las enviadas y en caso de que aún te falten, subirlas.
                                            Como siempre agradecemos tu compromiso para con este proyecto.");
            
            $contenido.="<div style='text-align:center;width:100%'>
                        <div style='text-align:left; display: inline-block; width:90%'>";
            
            $contenido.= "<br><br><label>Al día de hoy tu cumplimiento es $porcentajeCumplimiento%</label>";
            $contenido .= "<br><br>".getEvidenciasEnviadas($usuario,$evidenciasRepositorio);
            $contenido .= "<br>".getEvidenciasPendientes($usuario,$usuariosProcedimientosRepositorio);
            $contenido .= "<br>".getCaricatura("https://api.apps-handel.com/images/caricatura/Little_Business_Girl-69.png",100);
            $contenido .= "<br>".getGracias();
            
            $contenido .= "</div>
                            </div>";
            
            
            break;
    }
    
    return $contenido;   
}

function getContenidoSupervisor(UsuariosRepositorio $usuariosRepositorio,UsuariosProcedimientosRepositorio $usuariosProcedimientosRepositorio,EvidenciasRepositorio $evidenciasRepositorio,$usuario,$dia)
{
    $contenido = "";
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
                mensajeLog("error",$resultadoPorcentajes->mensajeError);
            }
            
            
            $contenido = getTextoConLogo("¡Hola! SAHA se encuentra abierto desde este momento para recibir las evidencias del mes, es importante que tomes unos minutos para identiﬁcarlas, organizarlas y subirlas así evitando olvidar enviarlas después.");
            
            $contenido.="<div style='text-align:center;width:100%'>
                        <div style='text-align:left; display: inline-block; width:90%'>";
            
            $contenido .= "<br>Agradecemos tu colaboración para tener el mejor desempeño de la compañía con la certiﬁcación.";
            $contenido.= "<br><br><label>El mes anterior tu cumplimiento de tu equipo fue ".number_format($porcentajeCumplimiento, 1, '.', '')."%</label>";
            $contenido.= getCaricatura("https://api.apps-handel.com/images/caricatura/Little_Business_Girl-03.png",100);
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
                mensajeLog("error",$resultadoPorcentajes->mensajeError);
            }
            
            $porcentajeActual = $porcentajeCumplimiento;
            
//             echo "<br>".$total;
//             echo "<br>".$cumplidas;
//             echo "<br>".$pendientes;
//             echo "<br>".$porcentajeActual;
            
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
                mensajeLog("error",$resultadoPorcentajes->mensajeError);
            }
            
           
            
            $porcentajeAnterior = $porcentajeCumplimiento;
            
            $porcentajeDiferencia = $porcentajeActual-$porcentajeAnterior;
            if($porcentajeDiferencia==0)
                $porcentajeDiferencia = "<label style='font-weight:bold;'>".number_format(abs($porcentajeDiferencia), 1, '.', '')."%</label>";
            else if($porcentajeDiferencia>0)
                $porcentajeDiferencia = "<label style='font-weight:bold;color:#017000;'>".number_format(abs($porcentajeDiferencia), 1, '.', '')."%</label> arriba";
            else
                $porcentajeDiferencia = "<label style='font-weight:bold;color:#ed220b;'>".number_format(abs($porcentajeDiferencia), 1, '.', '')."%</label> abajo";
                
            $contenido .= getTextoConLogo("Este es un recordatorio, los siguientes asociados tienen 2 semanas para subir sus evidencias de cumplimiento a SAHA. Es vital tomar las medidas necesarias para sean subidas a tiempo.
                                    <br>En este mes el cumplimiento a la fecha de tu equipo es
                                    <label style='font-weight:bold;'>".number_format($porcentajeActual, 1, '.', '')."%</label> un $porcentajeDiferencia con respecto al mes anterior. ");
            
            $contenido.="<div style='text-align:center;width:100%'>
                <div style='text-align:left; display: inline-block; width:90%'>";
            
            
            $contenido .= "<br>".getUsuarios($usuario,$usuariosRepositorio,$evidenciasRepositorio,$usuariosProcedimientosRepositorio);
            $contenido .= "<br>".getCaricatura("https://api.apps-handel.com/images/caricatura/Little_Business_Girl-46.png",100);
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
                mensajeLog("error",$resultadoPorcentajes->mensajeError);
            }
            
            $porcentajeActual = $porcentajeCumplimiento;
            
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
                mensajeLog("error",$resultadoPorcentajes->mensajeError);
            }
            
            $porcentajeAnterior = $porcentajeCumplimiento;
            
            $porcentajeDiferencia = $porcentajeActual-$porcentajeAnterior;
            if($porcentajeDiferencia==0)
                $porcentajeDiferencia = "<label style='font-weight:bold;'>".number_format(abs($porcentajeDiferencia), 1, '.', '')."%</label>";
            else if($porcentajeDiferencia>0)
                $porcentajeDiferencia = "<label style='font-weight:bold;color:#017000;'>".number_format(abs($porcentajeDiferencia), 1, '.', '')."%</label> arriba";
            else
                $porcentajeDiferencia = "<label style='font-weight:bold;color:#ed220b;'>".number_format(abs($porcentajeDiferencia), 1, '.', '')."%</label> abajo";
                    
            $contenido .= getTextoConLogo("Este es un recordatorio, los siguientes asociados tienen 6 días para subir sus evidencias de cumplimiento a SAHA. Tu apoyo es indispensable para lograr que tu equipo logre el 100% en este mes.
                                    <br>A seis días del cierre el cumplimiento a la fecha de tu equipo es <label style='font-weight:bold;'>".number_format($porcentajeActual, 1, '.', '')."%</label> un $porcentajeDiferencia con respecto al mes anterior. ");
            
            $contenido.="<div style='text-align:center;width:100%'>
                <div style='text-align:left; display: inline-block; width:90%'>";
            
            
            $contenido .= "<br>".getUsuarios($usuario,$usuariosRepositorio,$evidenciasRepositorio,$usuariosProcedimientosRepositorio);
            $contenido .= "<br>".getCaricatura("https://api.apps-handel.com/images/caricatura/Little_Business_Girl-32.png",100);
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
                mensajeLog("error",$resultadoPorcentajes->mensajeError);
            }
            
            $porcentajeActual = $porcentajeCumplimiento;
            
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
                mensajeLog("error",$resultadoPorcentajes->mensajeError);
            }
            
            $porcentajeAnterior = $porcentajeCumplimiento;
            
            $porcentajeDiferencia = $porcentajeActual-$porcentajeAnterior;
            if($porcentajeDiferencia==0)
                $porcentajeDiferencia = "<label style='font-weight:bold;'>".number_format(abs($porcentajeDiferencia), 1, '.', '')."%</label>";
            else if($porcentajeDiferencia>0)
                $porcentajeDiferencia = "<label style='font-weight:bold;color:#017000;'>".number_format(abs($porcentajeDiferencia), 1, '.', '')."%</label> arriba";
            else
                $porcentajeDiferencia = "<label style='font-weight:bold;color:#ed220b;'>".number_format(abs($porcentajeDiferencia), 1, '.', '')."%</label> abajo";
                    
            $contenido .= getTextoConLogo("Este es un recordatorio, hoy es el último día para subir evidencias a SAHA, aprovecha las últimas horas para revisar las enviadas por tu equipo y si aun tienen pendientes el tiempo se agota. Ayúdanos a mantener al 100% el cumplimiento de tu equipo. Puedes utilizar el sistema de mensajes de SAHA para motivarlos a terminar o felicitar a quien se tomó el tiempo para hacerlo.
                                    <br>Es el último día y la última oportunidad para completar las evidencias de tu equipo; a este día tu equipo tiene una entrega combinada del <label style='font-weight:bold;'>".number_format($porcentajeActual, 1, '.', '')."%</label> un $porcentajeDiferencia con respecto al mes anterior. ");
            
            $contenido.="<div style='text-align:center;width:100%'>
                <div style='text-align:left; display: inline-block; width:90%'>";
            
            
            $contenido .= "<br>".getUsuarios($usuario,$usuariosRepositorio,$evidenciasRepositorio,$usuariosProcedimientosRepositorio);
            $contenido .= "<br>".getCaricatura("https://api.apps-handel.com/images/caricatura/Little_Business_Girl-69.png",100);
            $contenido .= "<br>".getGracias();
            
            $contenido .= "</div>
                    </div>";
                    
                    
        break;
        case 28:
            $contenido .= getTextoConLogo("Es el ultimo recordatorio del mes, este es para agradecerte el apoyo constante y adjuntar a este correo el reporte que muestra el estado de trabajo del mes de tu equipo; por favor toma unos minutos para retroalimentar a tu equipo de trabajo utilizando el sistema de mensajes de SAHA.");
            
            $contenido.="<div style='text-align:center;width:100%'>
                        <div style='text-align:left; display: inline-block; width:90%'>";
            
            $contenido .= "<br>".getCaricatura("https://api.apps-handel.com/images/caricatura/Little_Business_Girl-78.png",160);
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
    $ano=  date("Y");
    $mes = date("m");
    $criteriosSeleccion = (object) ['ano' => $ano, "mes"=> $mes];
    $resultado = $repositorio->consultarEvidenciasCumplidas($usuario,$criteriosSeleccion);
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
    $ano=  date("Y");
    $mes = date("m");
    $criteriosSeleccion = (object) ['ano' => $ano, "mes"=> $mes];

    $resultado = $evidenciasRepositorio->consultarPorcentajesUsuarios($usuario,$criteriosSeleccion);
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
    
//     $resultado = $evidenciasRepositorio->consultarEvidenciasCumplidasMesActual($usuario,null);
//     $evidenciasCumplidas = array();
//     if($resultado->correcto())
//     {
//         $evidenciasCumplidas = $resultado->valor;
//     }
//     else
//         mensajeLog("error",$resultado->mensajeError);
    
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
           
     
    
    return $texto;
    
}


function mensajeLog($archivo,$error)
{
    $error = date("j/n/Y h:m:s") .":".$error;
    file_put_contents('./'.$archivo.'_'.date("j.n.Y").'.log',  utf8_decode($error) , FILE_APPEND);
    echo "<br>".utf8_decode($error);
}
    