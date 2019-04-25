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
            $mensaje= file_get_contents('../plantillas_correo/bienvenida_handel.html');
            $mensaje=  str_replace("&lt;Nombre&gt;",$usuario->nombre,$mensaje);
            $mensaje=  str_replace("&lt;contraseña&gt;",$usuario->contrasena,$mensaje);
            $mensaje=  str_replace("&lt;usuario&gt;",$usuario->nombreUsuario,$mensaje);
            
            $accesos = "";
            
            if($usuario->permisoSAHA==1)
            {
                $accesos.= $this->contenidoSAHA();
            }
            if($usuario->permisoSIVAH==1)
            {
                $accesos.= $this->contenidoSIVAH();
            }
            if($usuario->permiso10y7==1)
            {
                $accesos.= $this->contenido10y7();
            }
            
            $mensaje=  str_replace("@ACCESOS",$accesos,$mensaje);
            
            
            $cabecera = "From: noreply@apps-handel.com\r\n"; //Remitente
            $cabecera .= "Content-type: text/html; charset=UTF-8\r\n";
            
            $asunto = $usuario->nombre .", Bienvenido a 10y7 ";
            $resultadoMail= mail($usuario->nombreUsuario, $asunto, $mensaje, $cabecera);
            
            
            if($resultadoMail)
            {
                //$resultado->valor="OK";
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
    
    private function contenidoSAHA()
    {
        $html="";
        $html.="<tr><td class='long-text links-color' width='100%' valign='top' align='left' style='font-weight: normal; color: #3f3f3f; font-size: 13px; font-family: Arial, Helvetica, sans-serif; text-align: left; line-height: normal;'>";
        $html.="<p style='margin: 1em 0px;'>SAHA te permite subir evidencias y estar al tanto de la certificación en tu empresa&nbsp;</p>";
        $html.="</td>";
        $html.="</tr>";
        $html.="<tr>";
        $html.="<td valign='top' align='left'><table";
        $html.="role='presentation' cellpadding='6' border='0'";
        $html.="    align='left' cellspacing='0'";
        $html.="        style='border-spacing: 0; mso-padding-alt: 6px 6px 6px 6px; padding-top: 4px;'>";
        $html.="        <tbody>";
        $html.="        <tr>";
        $html.="        <td width='auto' valign='middle' align='left'";
        $html.="            bgcolor='#ffc000'";
        $html.="                style='text-align: center; font-weight: normal; padding: 6px; padding-left: 18px; padding-right: 18px; background-color: #ffc000; color: #3f3f3f; font-size: 13px; font-family: Arial, Helvetica, sans-serif; border-radius: 4px;'><a";
        $html.="                style='text-decoration: none; font-weight: normal; color: #3f3f3f; font-size: 13px; font-family: Arial, Helvetica, sans-serif;'";
        $html.="                    target='_new' href='https://saha-apps-handel.com'><strong>Acceder";
        $html.="                     a SAHA</strong></a></td>";
        $html.="                    </tr>";
        $html.="                    </tbody>";
        $html.="                    </table></td>";
        $html.="                    </tr>";
        return $html;
    }
    
    private function contenidoSIVAH()
    {
        $html="";
        $html.="<tr><td class='long-text links-color' width='100%' valign='top' align='left' style='font-weight: normal; color: #3f3f3f; font-size: 13px; font-family: Arial, Helvetica, sans-serif; text-align: left; line-height: normal;'>";
        $html.="<p style='margin: 1em 0px;'>SIVAH te permite verificar el estado de la empresa tomando en cuenta la útlima auditoría presencial disponible y subir evidencias de tu cumplimiento para mantener actualizado la lista de pendientes y conocer el avance de la certificación&nbsp;</p>";
        $html.="</td>";
        $html.="</tr>";
        $html.="<tr>";
        $html.="<td valign='top' align='left'><table";
        $html.="role='presentation' cellpadding='6' border='0'";
        $html.="    align='left' cellspacing='0'";
        $html.="        style='border-spacing: 0; mso-padding-alt: 6px 6px 6px 6px; padding-top: 4px;'>";
        $html.="        <tbody>";
        $html.="        <tr>";
        $html.="        <td width='auto' valign='middle' align='left'";
        $html.="            bgcolor='#ffc000'";
        $html.="                style='text-align: center; font-weight: normal; padding: 6px; padding-left: 18px; padding-right: 18px; background-color: #ffc000; color: #3f3f3f; font-size: 13px; font-family: Arial, Helvetica, sans-serif; border-radius: 4px;'><a";
        $html.="                style='text-decoration: none; font-weight: normal; color: #3f3f3f; font-size: 13px; font-family: Arial, Helvetica, sans-serif;'";
        $html.="                    target='_new' href='https://sivah.apps-handel.com'><strong>Acceder";
        $html.="                     a SAHA</strong></a></td>";
        $html.="                    </tr>";
        $html.="                    </tbody>";
        $html.="                    </table></td>";
        $html.="                    </tr>";
        return $html;
    }
    
    private function contenido10y7()
    {
        $html="";
        $html.="<tr><td class='long-text links-color' width='100%' valign='top' align='left' style='font-weight: normal; color: #3f3f3f; font-size: 13px; font-family: Arial, Helvetica, sans-serif; text-align: left; line-height: normal;'>";
        $html.="<p style='margin: 1em 0px;'>10y7 te permite tener control de las inspecciones de seguridad y visualizar estadisticas generales de las mismas&nbsp;</p>";
        $html.="</td>";
        $html.="</tr>";
        $html.="<tr>";
        $html.="<td valign='top' align='left'><table";
        $html.="role='presentation' cellpadding='6' border='0'";
        $html.="    align='left' cellspacing='0'";
        $html.="        style='border-spacing: 0; mso-padding-alt: 6px 6px 6px 6px; padding-top: 4px;'>";
        $html.="        <tbody>";
        $html.="        <tr>";
        $html.="        <td width='auto' valign='middle' align='left'";
        $html.="            bgcolor='#ffc000'";
        $html.="                style='text-align: center; font-weight: normal; padding: 6px; padding-left: 18px; padding-right: 18px; background-color: #ffc000; color: #3f3f3f; font-size: 13px; font-family: Arial, Helvetica, sans-serif; border-radius: 4px;'><a";
        $html.="                style='text-decoration: none; font-weight: normal; color: #3f3f3f; font-size: 13px; font-family: Arial, Helvetica, sans-serif;'";
        $html.="                    target='_new' href='https://10y7.apps-handel.com'><strong>Acceder";
        $html.="                     a SAHA</strong></a></td>";
        $html.="                    </tr>";
        $html.="                    </tbody>";
        $html.="                    </table></td>";
        $html.="                    </tr>";
        return $html;
    }
    
}
