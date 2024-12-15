<?php
use php\clases\AdministradorConexion;
use php\repositorios\AuditoriasRepositorio;
require_once('../repositorios/AuditoriasRepositorio.php');
require_once('../clases/AdministradorConexion.php');

$conexion = null;
$administrador_conexion = new AdministradorConexion();
try
{
    $conexion = $administrador_conexion->abrir();
    if($conexion)
    {
        $repositorio = new AuditoriasRepositorio($conexion);
        $resultado = $repositorio->consultarAuditoriasRecientesEmpresa(5);
        if($resultado->correcto())
        {
            for($i = 0; $i < count($resultado->valor); $i++)
            {
                echo $resultado->valor[$i]->id;
                if($i < count($resultado->valor) - 1)
                    echo ", ";
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