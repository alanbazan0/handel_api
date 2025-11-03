<?php
use php\clases\AdministradorConexion;
require_once('../reportes_pdf/reporte_kci.php');
require_once('../clases/AdministradorConexion.php');


function format($valor)
{
    $valor = bcdiv($valor, '1', 1);
    
    list($enteros, $decimales) = explode(".", $valor);
    if($decimales=="0")
        $valor = str_replace(".$decimales","",$valor);
        return $valor;
}

$conexion = null;
$administrador_conexion = new AdministradorConexion();
try
{
    $conexion = $administrador_conexion->abrir();
    if($conexion)
    {
        $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
        session_start();
        $usuario = null;
        if(isset($_SESSION['usuario']))
        {
            $usuario = $_SESSION['usuario'];
            /* if($usuario->tipoUsuarioId == TipoUsuario::ADMINISTRADOR)
            {
                $repositorio = new UsuariosRepositorio($conexion);
                $resultado = $repositorio->consultarCoordinador($criteriosSeleccion->empresaId);
                if($resultado->correcto())
                {
                    $usuario = $resultado->valor;*/
                    $reporte =new ReporteKCI();
                    if($reporte!=null)
                    {
                        $reporte->setConexion($conexion);
                        $reporte->AliasNbPages();
                        $reporte->generar($usuario,$criteriosSeleccion);
                       $reporte->imprimir();
                    }
            /*    }
                else 
                    echo $resultado->mensajeError;
            }
            else
                echo "Solo un administrador puede generar este reporte";*/
        }
        else
            echo "Sesión caducada";
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


