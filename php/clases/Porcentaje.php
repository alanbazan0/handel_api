<?php
namespace php\clases;

class Porcentaje
{
    public static function calcularPorcentaje(&$registro,$campo,$campoTotal,$campoPorcentaje)
    {
        $total = $registro->$campoTotal;
        $cumplido =$registro->$campo;
        $registro->$campoPorcentaje  = 0;
        if($total!=0)
        {
            $registro->$campoPorcentaje = $cumplido  * 100 / $total;
            
            $registro->$campoPorcentaje = bcdiv($registro->$campoPorcentaje, '1', 1);
            
            list($enteros, $decimales) = explode(".", $registro->$campoPorcentaje);
            if($decimales=="0")
                $registro->$campoPorcentaje = str_replace(".$decimales","",$registro->$campoPorcentaje);
                
        }
        else
            $registro->$campoPorcentaje = 0;
    }
}

