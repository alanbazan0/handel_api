<?php
namespace php\clases;

class Logger
{
    public static function log($archivo,$texto)
    {
        $mensaje = date("j/n/Y h:i:s") .":".$texto;
        $carpeta = "logs/";
        if(!file_exists($carpeta))
            @mkdir($carpeta);
        file_put_contents($carpeta.$archivo.'_'.date("j.n.Y").'.log',  utf8_decode("\n".$mensaje) , FILE_APPEND);
    }
}

