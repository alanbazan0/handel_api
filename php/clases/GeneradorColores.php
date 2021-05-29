<?php
namespace php\clases;

class GeneradorColores
{
    public static function generar($numeroColores)
    {
        $coloresBase = [ "#0c9cfb","#78d34b","#6a666a", "#f6ba36","#e13d47","#ca3675","#958b34","#81bede"];
        $colores  =  GeneradorColores::generarColores($coloresBase,100);
        return $colores;
    }
    
    private static function generarColores($colores,$n)
    {
        $limite = $n -  count($colores);
        for ($i = 0; $i < $limite; $i++)
        {
            $color = GeneradorColores::generarColor();
            array_push($colores,$color);
        }
        return $colores;
    }
    
    private static function generarColor()
    {
        $color = '#';
        $colorHexLighter = array("9","A","B","C","D","E","F" );
        for($x=0; $x < 6; $x++):
        $color .= $colorHexLighter[array_rand($colorHexLighter, 1)]  ;
        endfor;
        return substr($color, 0, 7);
    }
}

