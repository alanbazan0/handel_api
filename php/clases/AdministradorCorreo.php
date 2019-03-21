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
            $mensaje= file_get_contents('../plantillas_correo/bienvenida.html');
            $mensaje=  str_replace("&lt;Nombre&gt;",$usuario->nombre,$mensaje);
            $mensaje=  str_replace("&lt;Contraseña&gt;",$usuario->contrasena,$mensaje);
            $mensaje=  str_replace("&lt;User&gt;",$usuario->nombreUsuario,$mensaje);
            
            
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
    
    
    
}
