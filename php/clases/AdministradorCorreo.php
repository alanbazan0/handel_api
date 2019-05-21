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
        
        $html.="<tr>";
        $html.="<td width='auto' valign='middle' align='left' bgcolor='#ffc000' style='text-align: center; font-weight: normal; padding: 6px; padding-left: 18px; padding-right: 18px; background-color: #ffc000; color: #3f3f3f; font-size: 13px; font-family: Arial, Helvetica, sans-serif; border-radius: 4px;'><a style='text-decoration: none; font-weight: normal; color: #3f3f3f; font-size: 13px; font-family: Arial, Helvetica, sans-serif;' target='_new' href='$url'><strong>Ingresar</strong></a></td>";
        $html.="</tr>";
        return $html;
    }
    
//     private function contenidoSIVAH()
//     {
//         $html="";
//         $html.="<tr>";
//         $html.="<td>";
//         $html.="<br/>";
//         $html.="<span style='color: #3f3f3f; font-size: 12px; font-family: Arial, Helvetica, sans-serif;'></span>";
//         $html.="</td>";
//         $html.="</tr>";
//         return $html;
//     }
    
//     private function contenido10y7()
//     {
//         $html="";
//         $html.="<tr>";
//         $html.="<td>";
//         $html.="<br/>";
//         $html.="<span style='color: #3f3f3f; font-size: 12px; font-family: Arial, Helvetica, sans-serif;'></span>";
//         $html.="</td>";
//         $html.="</tr>";
//         return $html;
//     }
    
}
