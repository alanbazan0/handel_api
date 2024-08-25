<?php

namespace php\clases;
use Exception;
use php\modelos\Resultado;
use php\repositorios\UsuariosRepositorio;

class AdministradorCorreo
{
    public function enviarCorreoBienvenida($usuario)
    {
        $resultado = new Resultado();
        
        try
        {
            $mensaje= file_get_contents('../plantillas_correo/bienvenida.html');
            $mensaje=  str_replace("@nombre",$usuario->nombre,$mensaje);
            $mensaje=  str_replace("@correoElectronico",$usuario->nombreUsuario,$mensaje);
            $mensaje=  str_replace("@contrasena",$usuario->contrasena,$mensaje);
            
            $accesos = "";
            
            if($usuario->permisoSAHA==1)
            {
                $accesos.= $this->contenido("SAHA te permite subir evidencias y estar al tanto de la certificación en tu empresa.","https://saha.apps-handel.com","https://api.apps-handel.com/images/logoSAHA.png");
            }
            if($usuario->permisoSIVAH==1)
            {
                $accesos.= $this->contenido("SIVAH te permite verificar el estado de la empresa tomando en cuenta la útlima auditoría presencial disponible y subir evidencias de tu cumplimiento para mantener actualizado la lista de pendientes y conocer el avance de la certificación.","https://sivah.apps-handel.com","https://api.apps-handel.com/images/logoSIVAH.png");
            }
            if($usuario->permiso10y7==1)
            {
                $accesos.= $this->contenido("10y7 te permite tener control de las inspecciones de seguridad y visualizar estadisticas generales de las mismas.","https://10y7.apps-handel.com","https://api.apps-handel.com/images/logo10y7.png");
            }
            
            $mensaje=  str_replace("@accesos",$accesos,$mensaje);
            
            $cabecera = "From:  APPS HANDEL <noreply@apps-handel.com>\r\n"; //Remitente
            $cabecera .= "Bcc: bitacora_correo@apps-handel.com\r\n";
            $cabecera .= "Content-type: text/html; charset=UTF-8\r\n";
            
            $asunto = utf8_decode("¡Bienvenido a Apps Handel!");
            $resultadoMail= mail($usuario->nombreUsuario, $asunto, $mensaje, $cabecera);
            
            
            if($resultadoMail)
            {
                $resultado->valor=$usuario->id;
            }
            else
                $resultado->mensajeError="No se pudo enviar el correo electrónico, intente mas tarde.";
                
                
        }
        catch (Exception $e)
        {
            throw $e;
            
        }
        return $resultado;
    }
    
    public function enviarCorreoRecuperacion($usuario)
    {
        $resultado = new Resultado();
        
        try
        {
            $mensaje= file_get_contents('../plantillas_correo/recuperar_cuenta.html');
            $mensaje=  str_replace("@nombre",$usuario->nombre,$mensaje);
            $mensaje=  str_replace("@correoElectronico",$usuario->nombreUsuario,$mensaje);
            $mensaje=  str_replace("@contrasena",$usuario->contrasena,$mensaje);
            
            
            $cabecera = "From:  APPS HANDEL <noreply@apps-handel.com>\r\n"; //Remitente
            $cabecera .= "Bcc: bitacora_correo@apps-handel.com\r\n";
            $cabecera .= "Content-type: text/html; charset=UTF-8\r\n";
            
            $asunto = utf8_decode("¡Recuperación de cuenta Apps Handel!");
            $resultadoMail= mail($usuario->nombreUsuario, $asunto, $mensaje, $cabecera);
            
            
            if($resultadoMail)
            {
                $resultado->valor=$mensaje;
            }
            else
                $resultado->mensajeError="No se pudo enviar el correo electrónico, intente mas tarde.";
                
                
        }
        catch (Exception $e)
        {
            throw $e;
            
        }
        return $resultado;
    }
    
    private function contenido($texto, $url, $logo)
    {
        $fecha = new \DateTime();
        $time = $fecha->getTimestamp();
        $html=" <br>
        <div style='text-align:center;width:100%'>
        <div style='text-align:center; display: inline-block; width:80%'>
        <table>
        <tr>
        <td>
        <a href='$url'> <img src='$logo?$time' style='width:100px'></img></a>
        </td>
        <td>
        <label style=''>$texto</label>
        </td>
        </tr>
        </table>
        </div>
        </div>";
        return $html;
    }

    public function enviarNotificacionMensaje($tipo,$usuario, $usuarios, $asuntoCorreo, $titulo, $contenido, $url, $info)
    {
       // $usuarios = array();
        //array_push($usuarios,(object) ['nombreUsuario' => 'alanbazan@apps-handel.com','nombreCompleto' => 'Alan Bazán']);
     
        
        $mensaje= file_get_contents('../plantillas_correo/tema_nuevo.html');
        
        $caricatura = "https://api.apps-handel.com/images/caricatura/Bonus_Shapes_and_Backgounds-12.png";
        
        $mensaje=  str_replace("@texto",$titulo,$mensaje);
        $mensaje=  str_replace("@url",$url,$mensaje);
        $mensaje=  str_replace("@mensaje",$contenido,$mensaje);
        $mensaje=  str_replace("@caricatura",$caricatura,$mensaje);
        
        return  $this->enviarCorreoUsuarios($tipo,$usuarios,$asuntoCorreo, $mensaje, $info);
    }
    

    
    public function enviarNotificacionSIVAH($tipo, $usuarios, $asunto,$tituloDerecho, $colorTitulo, $titulo, $contenido, $textoBoton, $urlBoton, $imprimir)
    {
        // $usuarios = array();
        //array_push($usuarios,(object) ['nombreUsuario' => 'alanbazan@apps-handel.com','nombreCompleto' => 'Alan Bazán']);
        
        $asuntoCorreo="=?UTF-8?B?".base64_encode($asunto)."?=";
        
        $mensaje= file_get_contents('../plantillas_correo/tema_nuevo_sivah.html');
        
        $mensaje=  str_replace("@tituloDerecho",$tituloDerecho,$mensaje);
        $mensaje=  str_replace("@titulo",$titulo,$mensaje);
        $mensaje=  str_replace("@contenido",$contenido,$mensaje);
        $mensaje=  str_replace("@colorTitulo",$colorTitulo,$mensaje);
        $mensaje=  str_replace("@textoBoton",$textoBoton,$mensaje);
        $mensaje=  str_replace("@urlBoton",$urlBoton,$mensaje);
        //$mensaje=  str_replace("@nombre",$nombre,$mensaje);
        
        
        return  $this->enviarCorreoUsuarios($tipo,$usuarios,$asuntoCorreo, $mensaje, "", "SIVAH",$imprimir);
    }
    
    public function enviarNotificacionTarea($usuario, $usuarios, $minuta, $tarea)
    {
        // $usuarios = array();
        //array_push($usuarios,(object) ['nombreUsuario' => 'alanbazan@apps-handel.com','nombreCompleto' => 'Alan Bazán']);
        
        $nombreUsuario = $usuario->nombreCompleto;
        $fotoPerfil = "https://api.apps-handel.com/" . $usuario->fotoPerfil;
        $asunto  = $usuario->nombreCompleto . ": te asignó  una tarea: " . $tarea->titulo;
        //  $asuntoCorreo = html_entity_decode($asunto);
        
        $asunto="=?UTF-8?B?".base64_encode($asunto)."?=";
        
        $tipo = "minuta" .$minuta->id ."tarea" . $tarea->id;
        $info = "";
        $mensaje= file_get_contents('../plantillas_correo/notificacion_tarea.html');
        
        
     //   $caricatura = "https://api.apps-handel.com/images/caricatura/Bonus_Shapes_and_Backgounds-12.png";
        
        $mensaje=  str_replace("@nombreUsuario",$nombreUsuario,$mensaje);
        $mensaje=  str_replace("@fotoPerfil",$fotoPerfil,$mensaje);
        $mensaje=  str_replace("@nombreMinuta",$minuta->titulo,$mensaje);
        $mensaje=  str_replace("@nombreTarea",$tarea->titulo,$mensaje);
        
        return  $this->enviarCorreoUsuarios($tipo,$usuarios,$asunto, $mensaje, $info, "SAHA: Tareas");
    }
    
    public function enviarNotificacionComentarioTarea($usuario, $usuarios, $tarea, $comentario)
    {
        
        $nombreUsuario = $usuario->nombreCompleto;
        $fotoPerfil = "https://api.apps-handel.com/" . $usuario->fotoPerfil;
        $asunto  = $usuario->nombreCompleto . ": hizo un comentario en tarea " . $tarea->titulo;
        
        $asunto="=?UTF-8?B?".base64_encode($asunto)."?=";
        
        $tipo = "minuta" .$$tarea->minutaId ."tarea" . $tarea->id;
        $info = "";
        $mensaje= file_get_contents('../plantillas_correo/notificacion_comentario_tarea.html');
        
        //$titulo = "El usuario $usuario->nombreCompleto ha comentado en la conversación sobre la tarea: <label style='font-weight:bold'> $tarea->minutaTitulo - $tarea->titulo</label>";
        
        
        //   $caricatura = "https://api.apps-handel.com/images/caricatura/Bonus_Shapes_and_Backgounds-12.png";
        
        $mensaje=  str_replace("@nombreUsuario",$nombreUsuario,$mensaje);
        $mensaje=  str_replace("@fotoPerfil",$fotoPerfil,$mensaje);
        $mensaje=  str_replace("@nombreMinuta",$tarea->minutaTitulo,$mensaje);
        $mensaje=  str_replace("@nombreTarea",$tarea->titulo,$mensaje);
        
        return  $this->enviarCorreoUsuarios($tipo,$usuarios,$asunto, $mensaje, $info, "SAHA: Tareas");
    }
    
    
//     public function enviarNotificacionComentario($usuario, $usuarios, $evidencia, $comentario)
//     {
//         $mensaje= file_get_contents('../plantillas_correo/notificacion_comentario.html');
        
        
//         $texto = "Recibiste un mensaje de $usuario->nombreCompleto en conversación sobre la evidencia: <label style='font-weight:bold'> $evidencia->nombre</label>";
//         $caricatura = "https://api.apps-handel.com/images/caricatura/Bonus_Shapes_and_Backgounds-12.png";
        
//         $mensaje=  str_replace("@texto",$texto,$mensaje);
//         $mensaje=  str_replace("@evidenciaId",$evidencia->id,$mensaje);
//         $mensaje=  str_replace("@mensaje",$comentario,$mensaje);
//         $mensaje=  str_replace("@caricatura",$caricatura,$mensaje);
        
// //         $usuarios = array(); 
// //         array_push($usuarios, $usuarioDestino);
        
        
//         return  $this->enviarCorreoUsuarios($usuarios,utf8_decode("SAHA: " . $usuario->nombreCompleto . ": hizo un comentario en evidencia " . $evidencia->nombre), $mensaje);
//     }
    
    public function enviarCorreoNotificacion($correo,$asunto,$mensaje)
    {
        $resultado = new Resultado();
        $cabecera = "From:  SAHA <noreply@apps-handel.com>\r\n";
        $cabecera .= "Bcc: bitacora_correo@apps-handel.com\r\n";
        $cabecera .= "Content-type: text/html; charset=UTF-8\r\n";
        $errLevel = error_reporting(E_ALL ^ E_WARNING);
        $resultadoMail = true;
        $resultadoMail= mail($correo, $asunto, $mensaje, $cabecera);
        error_reporting($errLevel);
        
        $error = error_get_last();
        
        if ($error!=null && $error["type"] == E_WARNING)
        {
            $resultado->mensajeError="No se pudo enviar el correo electrónico." . htmlspecialchars_decode($error["message"]) ;
            $resultado->codigoError = 3;
            
        }
        else if($resultadoMail)
        {
            $resultado->valor="OK";
        }
        return $resultado;
    }
    
    
    public function enviarCorreoUsuarios($tipo,$usuarios, $asunto, $mensaje, $info, $de=null,$imprimir=false,$filename=null)
    {
       
       
        $resultado = new Resultado();
       
        
        $correos="";
        for ($i = 0; $i < count($usuarios); $i++) 
        {
            $usuario = $usuarios[$i];
            $correos.= $usuario->nombreUsuario;
            if($i <  count($usuarios) -1 )
                $correos.=", ";
        }
        
        $correos.=", bitacora_correo@apps-handel.com";
        
        if($de==null)
            $de= "SAHA";
        
       
        
       
            
        if (file_exists($filename))
        {
            
           
            // Email body content
            //$handle = fopen($filename, "r");  // set the file handle only for reading the file
            //$content = fread($handle, $size); // reading the file
            //fclose($handle);                  // close upon completion
            
            //$encoded_content = chunk_split(base64_encode($content));
            
            $fp =    @fopen($filename,"rb");
            $data =  @fread($fp,filesize($filename));
            
            @fclose($fp);
            $encoded_content = chunk_split(base64_encode($data)); 
            
            $boundary = md5("random");
            
            
            $cabecera = "MIME-Version: 1.0\r\n"; // Defining the MIME version
            $cabecera .= "From:  $de <noreply@apps-handel.com>\r\n";
            $cabecera .= "Bcc: $correos\r\n";
            $cabecera .= "Content-Type: multipart/mixed;"; // Defining Content-Type
            $cabecera .= "boundary = $boundary\r\n";
            // Headers for attachment
            
            
            
           
           
            $body = "--$boundary\r\n";
            //$body .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
            $body .= "Content-Type: text/html; charset=\"UTF-8\"\n";
            $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $body .= chunk_split(base64_encode($mensaje));
            
            $body .= "--$boundary\r\n";
            $body .="Content-Type: application/pdf; name=".basename($filename)."\r\n";
            $body .="Content-Disposition: attachment; filename=".basename($filename)."\r\n";
            $body .="Content-Transfer-Encoding: base64\r\n";
            $body .="X-Attachment-Id: ".rand(1000, 99999)."\r\n\r\n";
            $body .= $encoded_content; // Attaching the encoded file with email
            
           
 //           echo $mensaje."<br>";
//             echo $cabecera."<br>";
           // echo $mensaje."<br>";
            
            //$mensaje  = "";
            //echo $mensaje."<br>";
            
            $errLevel = error_reporting(E_ALL ^ E_WARNING);
            $resultadoMail = false;
            $resultadoMail= mail("noreply@apps-handel.com", $asunto, $body, $cabecera );
            
        }
        else
        {
            
         
            $cabecera = "Content-type: text/html; charset=UTF-8\r\n";
            $cabecera .= "From:  $de <noreply@apps-handel.com>\r\n";
            $cabecera .= "Bcc: $correos\r\n";
            
            $errLevel = error_reporting(E_ALL ^ E_WARNING);
            $resultadoMail = false;
            $resultadoMail= mail("noreply@apps-handel.com", $asunto, $mensaje, $cabecera );
        }
       
        
        //$correos = "alanbazan@apps-handel.com, alanbazan@hotmail.com, alanbazan0@gmail.com";
        
        error_reporting($errLevel);
        
        $error = error_get_last();
        
        if($error!=null)
        {
        }
        
        if ($error!=null && $error["type"] == E_WARNING)
        {
            $resultado->mensajeError="No se pudo enviar el correo electrónico." . htmlspecialchars_decode($error["message"]) ;
            $resultado->codigoError = 3;
            
            $this->mensajeLog($tipo."_error", $correos,$mensaje, $info);
        }
        else if($resultadoMail)
        {
            $resultado->valor="OK";
            $this->mensajeLog($tipo,$correos, $mensaje, $info);
        }
        else
        {
            $resultado->mensajeError="No se pudo enviar el correo electrónico." . htmlspecialchars_decode($error["message"]) ;
            $resultado->codigoError = 3;
            
            $this->mensajeLog($tipo."_error", $correos,$mensaje, $info);
        }
        //var_dump($error);
       // var_dump($resultado);
        if($imprimir)
            echo $mensaje;
        
        return $resultado;
    }
    
    private $correoContacto = "mail@handel-sce.com";
    //private $correoContacto = "alanbazan@apps-handel.com";
    public function enviarContacto($nombre,$correoElectronico, $telefono, $textoMensaje)
    {
        $resultado = new Resultado();
        

        
        $cabecera = "From: SAHA <noreply@apps-handel.com>\r\n"; //Remitente
        $cabecera .= "Bcc: bitacora_correo@apps-handel.com\r\n";
        $cabecera .= "Content-type: text/html; charset=UTF-8\r\n";
        
        $asunto = "Contacto desde app SAHA: " . $nombre ;

        $informacion= "Se ha recibido una solicitud de informacióndesde la app de SAHA  con los siguientes datos:";
        $informacion .= "<br><label style='font-weight:bold'>Nombre:</label> $nombre"; 
        $informacion .= "<br><label style='font-weight:bold'>Correo electrónico:</label> $correoElectronico";
        $informacion .= "<br><label style='font-weight:bold'>Teléfono:</label> $telefono";
        
        $contenido="";
        $contenido .= $this->getTextoConLogo($informacion);        
        $contenido.="<div style='text-align:center;width:100%'>
                        <div style='text-align:left; display: inline-block; width:90%'>";
        
        $contenido.="<label style='font-weight:bold'>Mensaje:</label><br>";
        $contenido.=$textoMensaje;
        
        $contenido .= "<br>".$this->getCaricatura("https://api.apps-handel.com/images/caricatura/Bonus_Shapes_and_Backgounds-12.png",100);
        $contenido .= "</div>
                        </div>";
        
        $mensaje= file_get_contents('../plantillas_correo/contacto.html');
        $mensaje=  str_replace("@contenido",$contenido,$mensaje);
        
        $resultadoMail= mail($this->correoContacto, $asunto, $mensaje, $cabecera);
        
        if($resultadoMail)
        {
            $resultado->Valor="OK";
        }
        else
            $resultado->MensajeError="No se pudo enviar el correo electrónico, intente mas tarde.";
            
            return $resultado;
    }
    
    public function contactar10y7($nombre,$empresa, $giro, $correoElectronico,$telefono)
    {
        $resultado = new Resultado();
        
        
        
        $cabecera = "From: 10y7 <noreply@apps-handel.com>\r\n"; //Remitente
        $cabecera .= "Bcc: bitacora_correo@apps-handel.com\r\n";
        $cabecera .= "Content-type: text/html; charset=UTF-8\r\n";
        
        $asunto = "Contacto desde aplicación: " . $nombre ;
        
        $informacion= "Se ha recibido una solicitud de información desde la app de 10y7 con los siguientes datos:";
        $informacion .= "<br><label style='font-weight:bold'>Nombre:</label> $nombre";
        $informacion .= "<br><label style='font-weight:bold'>Empresa:</label> $empresa";
        $informacion .= "<br><label style='font-weight:bold'>Giro:</label> $giro";
        $informacion .= "<br><label style='font-weight:bold'>Correo electrónico:</label> $correoElectronico";
        $informacion .= "<br><label style='font-weight:bold'>Teléfono:</label> $telefono";
        
        $contenido="";
        $contenido .= $this->getTexto($informacion);
        $contenido.="<div style='text-align:center;width:100%'>
                        <div style='text-align:left; display: inline-block; width:90%'>";
        
//         $contenido.="<label style='font-weight:bold'>Mensaje:</label><br>";
//         $contenido.=$textoMensaje;
        
        //$contenido .= "<br>".$this->getCaricatura("https://api.apps-handel.com/images/caricatura/Bonus_Shapes_and_Backgounds-12.png",100);
        $contenido .= "</div>
                        </div>";
        
        $mensaje= file_get_contents('../plantillas_correo/contacto.html');
        $mensaje=  str_replace("@contenido",$contenido,$mensaje);
        
        $resultadoMail= mail($this->correoContacto, $asunto, $mensaje, $cabecera);
        //$resultadoMail= mail("alanbazan@apps-handel.com", $asunto, $mensaje, $cabecera);
        
        if($resultadoMail)
        {
            $resultado->Valor="OK";
        }
        else
            $resultado->MensajeError="No se pudo enviar el correo electrónico, intente mas tarde.";
            
            return $resultado;
    }
    
    
    
//     public function enviarNotificacionPorDia($usuarios)
//     {
//         $resultado = new Resultado();
        
//         try
//         {
           
                
                
//         }
//         catch (Exception $e)
//         {
//             file_put_contents('./log_'.date("j.n.Y").'.log',  $e->getMessage() , FILE_APPEND);
//             throw $e;
            
//         }
//         return $resultado;
//     }
    
    function mensajeLog($archivo, $correos, $mensaje, $info)   
    {
        $ano = date("Y");
        $mes = date("m");
        $dia = date("j");
        $carpeta = "logs/correos/$ano/$mes/$dia";
        if(file_exists("../".$carpeta."/") || @mkdir("../".$carpeta."/",0777,true))
        {
            
            $mensaje = date("j/m/Y H:i:s") .":".$mensaje;
            $mensaje.= "\n" . $info;
            $mensaje.= "\n" . $correos;
            file_put_contents("../".$carpeta."/".$archivo."_".date("j.m.Y_H.i.s").".html",  utf8_decode("\n".$mensaje) , FILE_APPEND);
        }
        //echo "<br>".utf8_decode($mensaje);
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
    
    function getTexto($texto)
    {
        return "<div style='text-align:center;width:100%'>
        <div style='text-align:center; display: inline-block; width:90%'>
         $texto
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
    		    <td  width='200px' rowspan='0'><img style='width:".$width."px;' src='$caricatura'><img></td>
    		  </tr>
    		</tbody>
    		</table>
    	</div>
     </div>";
    }
    
    public function enviarNotificacionRevision($tipo,$usuario, $usuarios, $procesoRevisado, $contenido, $boton ,$asunto)
    {
        $resultado = new Resultado();
        
        
        //$accion = " ha comentado en la conversación sobre la observacion: ";
        
        $asunto="=?UTF-8?B?".base64_encode($asunto)."?=";
        
       
        $info = "";
        $mensaje= file_get_contents('../plantillas_correo/notificacion_comentario_observacion.html');
        
        $ano = date("Y");
        
        
        //         $mensaje=  str_replace("@nombreProcedimiento",$procesoRevisado->nombre,$mensaje);
        //         $mensaje=  str_replace("@nombreUsuario",$nombreUsuario,$mensaje);
        //         $mensaje=  str_replace("@texto",$observacion->comentario,$mensaje);
        //         $mensaje = str_replace("@url", $url, $mensaje);
        
        $mensaje=  str_replace("@ano",$ano,$mensaje);
        $mensaje = str_replace("@contenido", $contenido, $mensaje);
        $mensaje = str_replace("@boton", $boton, $mensaje);
        //$mensaje=  str_replace("@fotoPerfil",$fotoPerfil,$mensaje);
        //$mensaje=  str_replace("@nombreMinuta",$tarea->minutaTitulo,$mensaje);
        //$mensaje=  str_replace("@nombreTarea",$tarea->titulo,$mensaje);
        //$mensaje=  str_replace("@fechaVencimiento",$tarea->fechaCompromiso,$mensaje);
        
        //$mensaje=  str_replace("@minutaId",$tarea->minutaId,$mensaje);
        //$mensaje=  str_replace("@tareaId",$tarea->id,$mensaje);
        
        
        //$mensaje=  str_replace("@frase",$frase->texto,$mensaje);
        //$mensaje=  str_replace("@autor",$frase->autor,$mensaje);
        
        
        
        //$administrador_correo = new AdministradorCorreo();
        $resultado = $this->enviarCorreoUsuarios($tipo,$usuarios,$asunto, $mensaje, $info, "SAHA: Revision de procesos");
        //}
        return $resultado;
    }
    
    
}
