<?php
use php\clases\AdministradorConexion;
use php\repositorios\ProcedimientosRepositorio;

require_once('../repositorios/ProcedimientosRepositorio.php');
require_once('../clases/AdministradorConexion.php');

$conexion = null;
$administrador_conexion = new AdministradorConexion();
try
{
    $conexion = $administrador_conexion->abrir();
    if($conexion)
    {
        $repositorio = new ProcedimientosRepositorio($conexion);
        $resultado = $repositorio->consultar((object)[]);
        if($resultado->correcto())
        {
            $certificaciones = array();
            array_push($certificaciones, (object)["certificacionId" => 1]);
            for($i = 0; $i < count($resultado->valor); $i++)
            {
               $repositorio->insertarCertificaciones($resultado->valor[$i]->id, $certificaciones);
            }
        }
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