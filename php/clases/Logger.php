<?php
namespace php\clases;

//require_once("../clases/Directorio.php");
class Logger
{
    public static function log($archivo,$texto,$carpeta='logs/')
    {
        $mensaje = date("j/n/Y h:i:s") .":".$texto;
        if(!file_exists($carpeta))
            @mkdir($carpeta);
        file_put_contents($carpeta.$archivo.'_'.date("j.n.Y").'.log',  utf8_decode("\n".$mensaje) , FILE_APPEND);
    }
    
//     public static function archivo($archivo, $extension, $texto,$carpeta='logs/')
//     {
//         if(!file_exists($carpeta))
//         {
//             echo "OK";
//             mkdir($carpeta, 0777, true);
//         }
//         file_put_contents($carpeta."/".$archivo.'_'.date("j.n.Y").".$extension",  utf8_decode("\n".$texto) , FILE_APPEND);
//     }
   
}

