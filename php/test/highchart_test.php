<?php
use php\repositorios\AuditoriasRepositorio;
use php\clases\AdministradorConexion;
use php\repositorios\EmpresasRepositorio;
include '../repositorios/AuditoriasRepositorio.php';
require_once('../clases/AdministradorConexion.php');
require_once('../repositorios/EmpresasRepositorio.php');

$administrador_conexion = new AdministradorConexion();
try
{
    $conexion = $administrador_conexion->abrir();
    if($conexion)
    {
        $repositorio = new AuditoriasRepositorio($conexion);
        $auditoriaId = REQUEST('auditoriaId');
        
        $llaves= (object) [
            'id' =>  $auditoriaId
        ];
        
        
        $resultado = $repositorio->consultarPorLlaves($llaves);
        
        
        
        if($resultado->mensajeError=="")
        {
            $auditoria = $resultado->valor;
            $empresa = null;
            if($auditoria->empresaId!="")
            {
                $repositorio = new EmpresasRepositorio($conexion);
                $llaves= (object) [
                    'id' =>  $auditoria->empresaId
                ];
                $resultado = $repositorio->consultarPorLlaves($llaves);
                if($resultado->mensajeError=="")
                    $empresa = $resultado->valor;
            }
            
            $llaves= (object) [
                'auditoriaId' =>  $auditoria->id,
                'plantillaId' =>  $auditoria->plantillaId
            ];
            $secciones = array();
            $repositorio = new AuditoriasRepositorio($conexion);
            $resultado = $repositorio->consultarValoresSecciones($llaves);
            if($resultado->mensajeError=="")
            {
                $secciones = $resultado->valor;
            }
           
        }
        else
            echo $resultado->mensajeError;
            
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
