<?php 
include 'Resultado.php';

use php\modelos\Resultado;
class Maps
{
    public static function getMap($url){
        
        $resultado = new Resultado();
        //Target Url
        //$target = "http://maps.google.com/maps/api/staticmap?center={$address}&zoom=14&size=280x280&sensor=false&markers=icon:http://chart.apis.google.com/chart%3Fchst%3Dd_map_spin%26chld%3D1%257C0%257Cfff%257C11%257C_%257CHere|{$address}";
        
        //run curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        if (!curl_errno($ch))
        {
            $info = curl_getinfo($ch);
            $contentType= $info["content_type"];
            switch ($contentType)
            {
                case "image/png":
                    $resultado->valor = $response;
                    break;
                default:
                    $resultado->mensajeError = $response;
                    break;
                    
            }
        }
        
        
        curl_close($ch);
        
        return $resultado;
    }
    
}