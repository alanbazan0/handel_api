<?php
namespace php\clases;
use php\modelos\Resultado;

class AdministradorArchivos
{
    public function crearBase64($base64, $carpeta, $nombreArchivo)
    {
        $resultado = new Resultado();
       //echo $base64;
        $foto = base64_decode($base64);
        
        list($type, $data) = explode(';', $base64);
        list(, $data)      = explode(',', $base64);
        $data = base64_decode($data);
        
        
        
        $file = fopen("../".$carpeta."/" .$nombreArchivo, 'wb');
        fwrite($file, $data);
        fclose($file);
        
        $filetext = fopen("../".$carpeta."/base64.t", 'wb');
        fwrite($filetext, $base64);
        fclose($filetext);
        
        
        return $resultado;
    }
    
    public function subir($carpeta,$archivo,$nombreArchivo)
    {
        $resultado = new Resultado();
        if($archivo!=null)
        {
            if(file_exists("../".$carpeta."/") || @mkdir("../".$carpeta."/"))
            {
                
                $file_name = $archivo['name'];
                $file_size =$archivo['size'];
                $file_tmp =$archivo['tmp_name'];
                $file_type=$archivo['type'];
                
                if($file_type!='image/jpeg' && $file_type!='image/png')
                {
                    $resultado->mensajeError = "Extension no permitida, por favor seleccione un archivo JPEG o PNG";
                }
                else  if($file_size > 2097152)
                {
                    $resultado->mensajeError ='El archivo no debe ser mayor 2 MB';
                }
                else
                {
                  
                    $dirpath = dirname(getcwd());
                    move_uploaded_file($file_tmp,$dirpath.'/'.$carpeta.'/'.$nombreArchivo);
                    $resultado->valor =$nombreArchivo;
                }
                
            }
            else 
                $resultado->mensajeError = "No existe la carpeta destino.";
        }
//         else
//             $resultado->mensajeError = "No se recibió ningun archivo";
        return $resultado;
    }
    
    
    
}

