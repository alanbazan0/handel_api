<?php
class Mes
{
    public static function getFechaUltimoDia($mes, $ano) 
    {
        $day = date("d", mktime(0,0,0, $mes+1, 0, $ano));
        return date('Y-m-d', mktime(0,0,0, $mes, $day, $ano));
    }
    
    public static function getUltimoDia($mes, $ano)
    {
        $day = date("d", mktime(0,0,0, $mes+1, 0, $ano));
        return $day;
    }
    
    
    public static function getNombre($mes)
    {
        $nombre="";
        switch($mes)
        {
            case 1:
                $nombre = "Enero";
                break;
            case 2:
                $nombre = "Febrero";
                break;
            case 3:
                $nombre = "Marzo";
                break;
            case 4:
                $nombre = "Abril";
                break;
            case 5:
                $nombre = "Mayo";
                break;
            case 6:
                $nombre = "Junio";
                break;
            case 7:
                $nombre = "Julio";
                break;
            case 8:
                $nombre = "Agosto";
                break;
            case 9:
                $nombre = "Septiembre";
                break;
            case 10:
                $nombre = "Octubre";
                break;
            case 11:
                $nombre = "Noviembre";
                break;
            case 12:
                $nombre = "Diciembre";
                break;
        }
        return $nombre;
    }
   
    
}