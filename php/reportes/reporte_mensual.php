<?php
use php\clases\AdministradorConexion;
use php\clases\AdministradorArchivos;
use php\clases\Token;
use php\repositorios\ConfiguracionReporteMensualRepositorio;
use php\modelos\ConfiguracionReporteMensual;
require_once('../reportes_pdf/reporte_mensual.php');
require_once('../clases/AdministradorConexion.php');
require_once('../repositorios/ConfiguracionReporteMensualRepositorio.php');
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
            
            $repositorio = new ConfiguracionReporteMensualRepositorio($conexion);
            $modelo = new ConfiguracionReporteMensual();
            $modelo->id = 1;
            $modelo->criteriosSeleccion =  json_encode($criteriosSeleccion, JSON_UNESCAPED_UNICODE);
            $repositorio->actualizar($modelo);
            
           
            
            $adminstradorArchivos = new AdministradorArchivos();
            
            $carpeta = "archivos_temporales";
            $imagenSimulacros = "";
            $imagenNovedades = "";
            
           
            if($archivoSimulacros!=null)
            {
                $extension = $adminstradorArchivos->getExtension($archivoSimulacros["type"]);
                $nombreArchivo = Token::crear() . "." . $extension;
                
                
                $resultado=$adminstradorArchivos->subirImagen($carpeta,$archivoSimulacros,$nombreArchivo);
                if($resultado->correcto())
                    $imagenSimulacros = $carpeta . "/" . $nombreArchivo;
                else
                    echo $resultado->mensajeError;
                    
            }
            
            if($archivoNovedades!=null)
            {
                $extension = $adminstradorArchivos->getExtension($archivoNovedades["type"]);
                $nombreArchivo = Token::crear() . "." . $extension;
                $resultado=$adminstradorArchivos->subirImagen($carpeta,$archivoNovedades,$nombreArchivo);
                if($resultado->correcto())
                    $imagenNovedades = $carpeta . "/" . $nombreArchivo;
                else
                    echo $resultado->mensajeError;
                
                $carpetaConfiguracion = "../archivos_configuracion_reporte_mensual/novedades/";
                array_map('unlink', array_filter((array) glob($carpetaConfiguracion."*")));
                $destino = $carpetaConfiguracion. $criteriosSeleccion->fotoNovedades;
                //echo $destino;
                if (copy("../".$imagenNovedades, $destino))
                {
                    
                }
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


