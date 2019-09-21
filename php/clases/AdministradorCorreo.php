<?php

namespace php\clases;
use Exception;
use php\modelos\Resultado;

class AdministradorCorreo
{
    public function enviarCorreoBienvenida($usuario)
    {
        $resultado = new Resultado();
        
        try
        {
            $mensaje= file_get_contents('../plantillas_correo/bienvenida_handel_v2.html');
            $mensaje=  str_replace("@nombre",$usuario->nombre,$mensaje);
            $mensaje=  str_replace("@usuario",$usuario->nombreUsuario,$mensaje);
            $mensaje=  str_replace("@contraseña",$usuario->contrasena,$mensaje);
            
            $accesos = "";
            
            if($usuario->permisoSAHA==1)
            {
                $accesos.= $this->contenido("SAHA te permite subir evidencias y estar al tanto de la certificación en tu empresa","https://saha.apps-handel.com");
            }
            if($usuario->permisoSIVAH==1)
            {
                $accesos.= $this->contenido("SIVAH te permite verificar el estado de la empresa tomando en cuenta la útlima auditoría presencial disponible y subir evidencias de tu cumplimiento para mantener actualizado la lista de pendientes y conocer el avance de la certificación","https://sivah.apps-handel.com");
            }
            if($usuario->permiso10y7==1)
            {
                $accesos.= $this->contenido("10y7 te permite tener control de las inspecciones de seguridad y visualizar estadisticas generales de las mismas","https://10y7.apps-handel.com");
            }
            
            $accesos.="<table>";
            $accesos.="<tr>";
            $accesos.="<td width='auto' valign='middle' align='left' bgcolor='#ffc000' style='text-align: center; font-weight: normal; padding: 6px; padding-left: 18px; padding-right: 18px; background-color: #ffc000; color: #3f3f3f; font-size: 13px; font-family: Arial, Helvetica, sans-serif; border-radius: 4px;'><a style='text-decoration: none; font-weight: normal; color: #3f3f3f; font-size: 13px; font-family: Arial, Helvetica, sans-serif;' target='_new' href='https://apps-handel.com'><strong>Ingresar</strong></a></td>";
            $accesos.="</tr>";
            $accesos.="</table>";
            
            $mensaje=  str_replace("@accesos",$accesos,$mensaje);
            
            
            
            $cabecera = "From: noreply@apps-handel.com\r\n"; //Remitente
            $cabecera .= "Content-type: text/html; charset=UTF-8\r\n";
            
            $asunto = $usuario->nombre .", Bienvenido a apps-handel.com";
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
    
    private function contenido($texto, $url)
    {
        $html="";
        $html.="<tr>";
        $html.="<td>";
        $html.="<br/>";
        $html.="<span style='color: #3f3f3f; font-size: 12px; font-family: Arial, Helvetica, sans-serif;'>$texto</span>";
        $html.="</td>";
        $html.="</tr>";
        
//         $html.="<tr>";
//         $html.="<td width='auto' valign='middle' align='left' bgcolor='#ffc000' style='text-align: center; font-weight: normal; padding: 6px; padding-left: 18px; padding-right: 18px; background-color: #ffc000; color: #3f3f3f; font-size: 13px; font-family: Arial, Helvetica, sans-serif; border-radius: 4px;'><a style='text-decoration: none; font-weight: normal; color: #3f3f3f; font-size: 13px; font-family: Arial, Helvetica, sans-serif;' target='_new' href='$url'><strong>Ingresar</strong></a></td>";
//         $html.="</tr>";
        return $html;
    }
    
    public function enviarNotificacionEvidenciaRecibida($correo,$nombre,$procedimientoNombre,$nombreArchivo)
    {
        $resultado = new Resultado();
        
        $asunto = "Recordatorio de Evidencias";
        //Cuerpo
        $mensaje="<html><body><meta http-equiv='Content-Type' content='text/html; charset=utf-8'><center>";
        $mensaje.="<table class='vb-outer' width='100%' cellpadding='0' border='0' cellspacing='0'  id='ko_titleBlock_4'><tbody><tr><td class='vb-outer' align='center' valign='top'  style='padding-left: 9px;padding-right: 9px;background-color: #1f497d;'>";
        $mensaje.="<div class='oldwebkit' style='max-width: 570px;'><table width='570' border='0' cellpadding='0' cellspacing='9' class='vb-container halfpad' bgcolor='#ffffff' style='border-collapse: separate;border-spacing: 9px;padding-left: 9px;padding-right: 9px;width: 100%;max-width: 570px;background-color: #fff;'><tbody><tr><td bgcolor='#ffffff' align='center' style='background-color: #ffffff; font-size: 22px; font-family: Arial, Helvetica, sans-serif; color: #3f3f3f; text-align: center;'>";
        $mensaje.="<span>Notificación SAHA</span>";
        $mensaje.="</td>";
        $mensaje.="</tr></tbody></table></div>";
        $mensaje.="</td>";
        $mensaje.="</tr></tbody></table><table class='vb-outer' width='100%' cellpadding='0' border='0' cellspacing='0' bgcolor='#1f497d' style='background-color: #1f497d;' id='ko_sideArticleBlock_3'><tbody><tr><td class='vb-outer' align='center' valign='top' bgcolor='#1f497d' style='padding-left: 9px;padding-right: 9px;background-color: #1f497d;'>";
        $mensaje.="<!--[if (gte mso 9)|(lte ie 8)]><table align='center' border='0' cellspacing='0' cellpadding='0' width='570'><tr><td align='center' valign='top'<![endif]-->";
        $mensaje.="<div class='oldwebkit' style='max-width: 570px;'>";
        $mensaje.="<table width='570' border='0' cellpadding='0' cellspacing='9' class='vb-row fullpad' bgcolor='#ffffff' style='border-collapse: separate;border-spacing: 9px;width: 100%;max-width: 570px;background-color: #fff;'><tbody><tr><td align='center' class='mobile-row' valign='top' style='font-size: 0;'>";
        $mensaje.="<!--[if (gte mso 9)|(lte ie 8)]><table align='center' border='0' cellspacing='0' cellpadding='0' width='552'><tr><![endif]-->";
        $mensaje.="<!--[if (gte mso 9)|(lte ie 8)]><td align='left' valign='top' width='184'><![endif]-->";
        $mensaje.="<div class='mobile-full' style='display: inline-block; max-width: 184px; vertical-align: top; width: 100%;'>";
        $mensaje.="<table class='vb-content' border='0' cellspacing='9' cellpadding='0' width='184' align='left' style='border-collapse: separate;width: 100%;'><tbody><tr><td width='100%' valign='top' align='left' class='links-color'>";
        $mensaje.="<img border='0' hspace='0' vspace='0' width='166' class='mobile-full' alt='' style='border: 0px;display: block;vertical-align: top;width: 100%;height: auto;max-width: 166px;' src='https://mosaico.io/srv/f-w3cgnv6/img?src=https%3A%2F%2Fmosaico.io%2Ffiles%2Fw3cgnv6%2FSAHA166130.png&amp;method=resize&amp;params=166%2Cnull'></td>";
        $mensaje.="</tr></tbody></table></div><!--[if (gte mso 9)|(lte ie 8)]></td>";
        $mensaje.="<![endif]--><!--[if (gte mso 9)|(lte ie 8)]>";
        $mensaje.="<td align='left' valign='top' width='368'>";
        $mensaje.="<![endif]--><div class='mobile-full' style='display: inline-block; max-width: 368px; vertical-align: top; width: 100%;'>";
        $mensaje.="<table class='vb-content' border='0' cellspacing='9' cellpadding='0' width='368' align='left' style='border-collapse: separate;width: 100%;'><tbody><tr><td style='font-size: 18px; font-family: Arial, Helvetica, sans-serif; color: #3f3f3f; text-align: left;'>";
        
        
        $mensaje.="<span style='color: #3f3f3f;'>Hola ".$nombre."</span>";
        $mensaje.="</td>";
        $mensaje.="</tr><tr><td align='left' class='long-text links-color' style='text-align: left; font-size: 13px; font-family: Arial, Helvetica, sans-serif; color: #3f3f3f;'><p style='margin: 1em 0px;margin-top: 0px;'>Han sido subidas las evidencias correspondientes, y han sido mandadas para su revisión.<br></p><p style='margin: 1em 0px;'>";
        $mensaje.="\n\nEvidencia: ";
        $mensaje.=$procedimientoNombre;
        $mensaje.="\n\nArchivo: ";
        $mensaje.=$nombreArchivo;
        
        $mensaje.="</p><p style='margin: 1em 0px;margin-bottom: 0px;'>¡Gracias por tu colaboración!</p></td>";
        $mensaje.="</tr><tr><td valign='top'>";
        $mensaje.="<table cellpadding='0' border='0' align='left' cellspacing='0' class='mobile-full' style='padding-top: 4px;'><tbody><tr><td width='auto' valign='middle' bgcolor='#ffc000' align='center' height='26' style='font-size: 13px; font-family: Arial, Helvetica, sans-serif; text-align: center; color: #000000; font-weight: normal; padding-left: 18px; padding-right: 18px; background-color: #ffc000; border-radius: 4px;'>";
        $mensaje.="<a style='text-decoration: none; color: #000000; font-weight: normal;' target='_new' href='http://www.saha.handel-sce.net'>Ingresar a <strong>SAHA</strong></a>";
        $mensaje.="</td>";
        $mensaje.="</tr></tbody></table></td>";
        $mensaje.="</tr></tbody></table></div><!--[if (gte mso 9)|(lte ie 8)]></td>";
        $mensaje.="<![endif]-->";
        $mensaje.="<!--[if (gte mso 9)|(lte ie 8)]></tr></table><![endif]-->";
        $mensaje.="</td>";
        $mensaje.="</tr></tbody></table></div>";
        $mensaje.="<!--[if (gte mso 9)|(lte ie 8)]></td></tr></table><![endif]-->";
        $mensaje.="</td>";
        $mensaje.="</tr></tbody></table><table class='vb-outer' width='100%' cellpadding='0' border='0' cellspacing='0' bgcolor='#1f497d' style='background-color: #1f497d;' id='ko_hrBlock_5'><tbody><tr><td class='vb-outer' align='center' valign='top' bgcolor='#1f497d' style='padding-left: 9px;padding-right: 9px;background-color: #1f497d;'>";
        $mensaje.="<!--[if (gte mso 9)|(lte ie 8)]><table align='center' border='0' cellspacing='0' cellpadding='0' width='570'><tr><td align='center' valign='top'><![endif]-->";
        $mensaje.="<div class='oldwebkit' style='max-width: 570px;'>";
        $mensaje.="<table width='570' border='0' cellpadding='0' cellspacing='9' class='vb-container halfpad'bgcolor='#ffffff' style='border-collapse: separate;border-spacing: 9px;padding-left: 9px;padding-right: 9px;width: 100%;max-width: 570px;background-color: #fff;'><tbody><tr><td valign='top' bgcolor='#ffffff' align='center' style='background-color: #ffffff;'>";
        $mensaje.="<table width='100%' cellspacing='0' cellpadding='0' border='0' style='width: 100%;'><tbody><tr><td width='100%' height='1' style='font-size: 1px; line-height: 1px; width: 100%; background-color: #3f3f3f;'> </td>";
        $mensaje.="</tr></tbody></table></td>";
        $mensaje.="</tr></tbody></table></div>";
        $mensaje.="<!--[if (gte mso 9)|(lte ie 8)]></td></tr></table><![endif]-->";
        $mensaje.="</td>";
        $mensaje."</tr></tbody></table><!-- footerBlock --><table width='100%' cellpadding='0' border='0' cellspacing='0' bgcolor='#953734' style='background-color: #953734;' id='ko_footerBlock_2'><tbody><tr><td align='center' valign='top' bgcolor='#953734' style='background-color: #953734;'>";
        $mensaje.="<!--[if (gte mso 9)|(lte ie 8)]><table align='center' border='0' cellspacing='0' cellpadding='0' width='570'><tr><td align='center' valign='top'><![endif]-->";
        $mensaje.="<!--[if (gte mso 9)|(lte ie 8)]></td></tr></table><![endif]-->";
        $mensaje.="</td>";
        $mensaje.="</tr></tbody></table><!-- /footerBlock --></center>";
        $mensaje.="</body></html>";
        
        
        $cabecera = "From: saha.noreply@handel-sce.com\r\n"; //Remitente
      //  $cabecera .= "Bcc: contacto@gmail.com\r\n"; //Copia oculta
        $cabecera .= "Content-type: text/html; charset=UTF-8\r\n";
        
        //$correo = "alanbazan@hotmail.com";
        // Enviar mail
      //  $resultadoMail= mail($correo, $asunto, $mensaje, $cabecera);
        
        $errLevel = error_reporting(E_ALL ^ E_WARNING);
        $resultadoMail= mail($correo, $asunto, $mensaje, $cabecera);
        error_reporting($errLevel);
        
        $error = error_get_last();
        
        if ( $error["type"] == E_WARNING)
        {
            $resultado->mensajeError="Se registró la evidencia, pero no se pudo enviar el correo electrónico." . htmlspecialchars_decode($error["message"]) ;
            $resultado->codigoError = 3;
        }
        else if($resultadoMail)
        {
            $resultado->valor="OK";
        }
        
//         if($resultadoMail)
//         {
//             $resultado->valor="OK";
//         }
//         else
//             $resultado->mensajeError="Se registró la evidencia, pero no se pudo enviar el correo electrónico.";
            
        return $resultado;
    }
    
    public function enviarNotificacionMensaje($usuario, $usuarios, $modeloMensaje)
    {
        $mensaje= file_get_contents('../plantillas_correo/plantilla.html');
        
        $mensaje=  str_replace("@asunto",$usuario->nombreCompleto . ": " . $modeloMensaje->asunto,$mensaje);
        
        $contenido = $modeloMensaje->mensaje;
        $contenido.="<br>";
        $contenido.="<table>";
        $contenido.="<tr>";
        $contenido.="<td width='auto' valign='middle' align='left' bgcolor='#ffc000' style='text-align: center; font-weight: normal; padding: 6px; padding-left: 18px; padding-right: 18px; background-color: #ffc000; color: #3f3f3f; font-size: 13px; font-family: Arial, Helvetica, sans-serif; border-radius: 4px;'><a style='text-decoration: none; font-weight: normal; color: #3f3f3f; font-size: 13px; font-family: Arial, Helvetica, sans-serif;' target='_new' href='https://saha.apps-handel.com/mensajes.php?mensajeId=$modeloMensaje->id'><strong>Ingresar</strong></a></td>";
        $contenido.="</tr>";
        $contenido.="</table>";
        
        $mensaje=  str_replace("@mensaje",$contenido,$mensaje);
      
        
        return  $this->enviarCorreoUsuarios($usuarios,utf8_decode("SAHA: " . $usuario->nombreCompleto . ": " . $modeloMensaje->asunto), $mensaje);
    }
    
    public function enviarCorreoUsuarios($usuarios, $asunto, $mensaje)
    {
        $resultado = new Resultado();
        $cabecera = "From: noreply@apps-handel.com\r\n";
       // $cabecera .= "Bcc: contacto@gmail.com\r\n"; 
        $cabecera .= "Content-type: text/html; charset=UTF-8\r\n";
        
        $correos="";
        for ($i = 0; $i < count($usuarios); $i++) 
        {
            $usuario = $usuarios[$i];
            $correos.= $usuario->nombreUsuario;
            if($i <  count($usuarios) -1 )
                $correos.=", ";
        
        }
        
        
        
        //$correos = "alanbazan@apps-handel.com, alanbazan@hotmail.com, alanbazan0@gmail.com";
        
        $errLevel = error_reporting(E_ALL ^ E_WARNING);
        $resultadoMail = true;
        $resultadoMail= mail($correos, $asunto, $mensaje, $cabecera);
        error_reporting($errLevel);
        
        $error = error_get_last();
        
        if ( $error["type"] == E_WARNING)
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
    
    
}
