<?php
use php\clases\AdministradorConexion;
use php\clases\AdministradorArchivos;
use php\clases\Token;
require_once('../reportes_pdf/reporte_mensual.php');
require_once('../clases/AdministradorConexion.php');
require_once('../clases/Token.php');

session_start();
$usuario = null;
if(isset($_SESSION['usuario']))
{
    $usuario = $_SESSION['usuario'];
    $conexion = null;
    $administrador_conexion = new AdministradorConexion();
    try
    {
        $conexion = $administrador_conexion->abrir();
        if($conexion)
        {
          
            $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
            $archivoSimulacros = FILES("fileSimulacros");
            $archivoNovedades = FILES("fileNovedades");
            
            $adminstradorArchivos = new AdministradorArchivos();
            
            $carpeta = "archivos_temporales";
            $imagenSimulacros = "";
            $imagenNovedades = "";
            
           
            if($archivoSimulacros!=null)
            {
                $imagenSimulacros = Token::crear() . "." . $adminstradorArchivos->getExtension($archivoSimulacros["type"]);
                $resultado=$adminstradorArchivos->subirImagen($carpeta,$archivoSimulacros,$imagenSimulacros);
                if($resultado->correcto())
                    $imagenSimulacros = $carpeta . "/" . $imagenSimulacros;
                else
                    echo $resultado->mensajeError;
            }
            
            if($archivoNovedades!=null)
            {
                $imagenNovedades = Token::crear() . "." . $adminstradorArchivos->getExtension($archivoNovedades["type"]);
                $resultado=$adminstradorArchivos->subirImagen($carpeta,$archivoNovedades,$imagenNovedades);
                if($resultado->correcto())
                    $imagenNovedades = $carpeta . "/" . $imagenNovedades;
                else
                    echo $resultado->mensajeError;
            }
            
            $reporte =new ReporteMensual();
            if($reporte!=null)
            {
                $reporte->setImagenes($imagenSimulacros,$imagenNovedades);
                $reporte->setConexion($conexion);
                $reporte->AliasNbPages();
                $reporte->generar($usuario,$criteriosSeleccion);
                $reporte->imprimir();
            }
            
           
            $adminstradorArchivos->eliminarArchivo($imagenSimulacros);
            $adminstradorArchivos->eliminarArchivo($imagenNovedades);
            
        }
    }
    catch(Exception $e)
    {
        echo  $e->getMessage();
    }
    finally
    {
        $administrador_conexion->cerrar($conexion);
    }

}
else
    echo "Sesión caducada";


