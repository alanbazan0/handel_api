<?php
namespace php\clases;
use Complex\Exception;
use php\modelos\Resultado;

class AdministradorArchivos
{
    
    public function eliminar($carpeta, $nombreArchivo)
    {
        $archivo = "../".$carpeta . "/" . $nombreArchivo;
        if(file_exists($archivo)) 
            unlink($archivo); 
    }
    
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
    
    public function subirImagen($carpeta,$archivo,$nombreArchivo)
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
                
                if($file_type!='image/jpeg' && $file_type!='image/png' && $file_type!='image/gif')
                {
                    $resultado->mensajeError = "Extension no permitida, por favor seleccione un archivo JPEG, PNG o GIF";
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
        return $resultado;
    }
    
    public function eliminarDirectorio($dir) {
        if (!file_exists($dir)) return true;
        if (!is_dir($dir) || is_link($dir)) return unlink($dir);
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') continue;
            if (!$this->eliminarDirectorio($dir . "/" . $item)) {
                chmod($dir . "/" . $item, 0777);
                if (!$this->eliminarDirectorio($dir . "/" . $item)) return false;
            };
        }
        return rmdir($dir);
    }
    
    public function subirArchivo($carpeta,$archivo,$nombreArchivo)
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
                
//                 if($file_type!='image/jpeg' && $file_type!='image/png')
//                 {
//                     $resultado->mensajeError = "Extension no permitida, por favor seleccione un archivo JPEG o PNG";
//                 }
//                 else  if($file_size > 2097152)
//                 {
//                     $resultado->mensajeError ='El archivo no debe ser mayor 2 MB';
//                 }
//                 else
//                 {
                    
                    $dirpath = dirname(getcwd());
                    $path = $dirpath.'/'.$carpeta.'/'.$nombreArchivo;
                    move_uploaded_file($file_tmp,$path);
                    
                    if(file_exists($path))
                        $resultado->valor =true;
                    
                   
                //}
                
            }
            else
                $resultado->mensajeError = "No existe la carpeta destino.";
        }
        else
            $resultado->mensajeError = "No se recibió ningún archivo";
        return $resultado;
    }
    
    public function copiar($carpeta, $nombreArchivo, $nombreArchivoCopia)
    {
        $resultado = new Resultado();
        $origen = "../" .$carpeta . "/" . $nombreArchivo;
        $destino = "../".  $carpeta . "/" . $nombreArchivoCopia;
        if(file_exists($origen))
            copy($origen, $destino);
        
        return $resultado;
    }
    
    public function eliminarTodo($nombreCarpeta, $eliminar)
    {
        $directorio = "../".$nombreCarpeta."/";
        $resultado = new Resultado();
        
        if(file_exists($directorio))
        {
            $archivosEliminados = array();
            $archivos = glob($directorio . '*');
            //$threshold = strtotime('-'.$dias.' days');
            
            
            foreach ($archivos as $archivo)
            {
                //$archivoNuevo = str_replace($nombreCarpeta,"trash/".$nombreCarpeta,$archivo);
                $fecha = date("F d Y H:i:s.", filemtime($archivo));
                if($eliminar)
                {
                    if(is_dir($archivo))
                        $this->eliminarDirectorio($archivo);
                    else
                        unlink($archivo);
                }
                array_push($archivosEliminados, (object)["archivo"=> $archivo, "fecha" => $fecha]);
                
                
            }
            $resultado->valor = (object)[ "directorio" => $directorio,
                "numeroArchivos" => count($archivos),
                "numeroArchivosEliminados" => count($archivosEliminados),
                "archivosEliminados" => $archivosEliminados ];
        }
        else
            $resultado->mensajeError = "No existe el directorio " .  $directorio;
        
        return $resultado;
    }
    
    public function eliminarArchivosAntiguos($nombreCarpeta, $dias, $eliminar)
    {
        $directorio = "../".$nombreCarpeta."/";
        $resultado = new Resultado();
        
        if(file_exists($directorio))
        {
            $archivosEliminados = array();
            $archivos = glob($directorio . '*');
            //$threshold = strtotime('-'.$dias.' days');
            
            $max_age = $dias * 86400;
            $limit = time() - $max_age;
            
            foreach ($archivos as $archivo)
            {
                $archivoNuevo = str_replace($nombreCarpeta,"trash/".$nombreCarpeta,$archivo);
                $fecha = date("F d Y H:i:s.", filemtime($archivo));
                $filetime = filemtime($archivo);
                if ($filetime < $limit)
                {
                    if($eliminar)
                    {
                        if (!file_exists($archivoNuevo) && is_dir($archivoNuevo)) {
                            
                            mkdir($archivoNuevo, 0777, true);
                        } 
                        rename($archivo,$archivoNuevo);
                    }
                    array_push($archivosEliminados, (object)["archivo"=> $archivo,"filetime" => $filetime, "limit" => $limit, "archivoNuevo" =>$archivoNuevo, "fecha" => $fecha]);
                    
                }
                
            }
            $resultado->valor = (object)[ "directorio" => $directorio,
                "numeroArchivos" => count($archivos),
                "numeroArchivosEliminados" => count($archivosEliminados),
                "archivosEliminados" => $archivosEliminados ];
        }
        else
            $resultado->mensajeError = "No existe el directorio " .  $directorio;
        
        return $resultado;
    }
    
}

