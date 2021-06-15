<?php
namespace php\clases;

class Porcentaje
{
    public static function calcularPorcentaje(&$registro,$campo,$campoTotal,$campoPorcentaje,$numeroDecimales = 1)
    {
        $total = $registro->$campoTotal;
        $cumplido =$registro->$campo;
        $registro->$campoPorcentaje  = 0;
        if($total!=0)
        {
            $registro->$campoPorcentaje = $cumplido  * 100 / $total;
            
            $registro->$campoPorcentaje = bcdiv($registro->$campoPorcentaje, '1', $numeroDecimales);
            
            list($enteros, $decimales) = explode(".", $registro->$campoPorcentaje);
            if($decimales=="0")
                $registro->$campoPorcentaje = str_replace(".$decimales","",$registro->$campoPorcentaje);
                
        }
        else
            $registro->$campoPorcentaje = 0;
    }
    
    public static function formatearPorcentaje(&$registro,$campoPorcentaje, $numeroDecimales=1)
    {
            
        $registro->$campoPorcentaje = bcdiv($registro->$campoPorcentaje, '1', $numeroDecimales);
        
        list($enteros, $decimales) = explode(".", $registro->$campoPorcentaje);
        if($decimales=="0")
            $registro->$campoPorcentaje = str_replace(".$decimales","",$registro->$campoPorcentaje);
                
    }
    
    public static function formatear($valor, $numeroDecimales=1)
    {
        $valor = bcdiv($valor, '1', $numeroDecimales);
        
        list($enteros, $decimales) = explode(".", $valor);
        if($decimales=="0")
            $valor = str_replace(".$decimales","",$valor);
        
         return $valor;
            
    }
}

