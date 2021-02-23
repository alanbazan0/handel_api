<?php
use php\clases\AdministradorConexion;
use php\repositorios\AuditoriasRepositorio;
use php\modelos\Resultado;
use php\repositorios\EmpresasRepositorio;


require('../vendor/fpdf181/fpdf.php');
include '../clases/Utilidades.php';
include '../clases/AdministradorConexion.php';
include '../repositorios/AuditoriasRepositorio.php';
include '../repositorios/EmpresasRepositorio.php';
require_once("../vendor/simplexlsx/src/SimpleXLSXGen.php");

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
        
        if($resultado->correcto())
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
            $hallazgos = array();
            $repositorio = new AuditoriasRepositorio($conexion);
            $resultado = $repositorio->consultarRecomendaciones($auditoria->id);
            if($resultado->correcto())
            {
                $hallazgos = $resultado->valor;
                $registros = [
                        ['Punto #', 
                        'Prioridad', 
                        'Fecha de creación', 
                        'Observaciones detectadas',
                        'Responsable', 
                        '% Cumplimiento', 
                        'Fecha compromiso' , 
                        'Notas & Seguimiento / Estatus', 
                        'Fecha real en que se realizó']
                    ];
                
                $punto =  1;
                for($i=0; $i < count($hallazgos); $i++)
                {
                    $hallazgo = $hallazgos[$i];
                    $registro  = [$punto,
                        '',
                        substr($hallazgo->fechaAlta,0,10),
                        $hallazgo->titulo,
                        $hallazgo->responsableNombreCompleto,
                        $hallazgo->cumplimiento. '%',
                        substr($hallazgo->fechaVencimiento,0,10), 
                        '', 
                        ''];
                    
                    array_push($registros,$registro);
                    $punto++;
                }
                
               /* $data = [
                    ['ISBN', 'title', 'author', 'publisher', 'ctry' ],
                    [618260307, 'The Hobbit', 'J. R. R. Tolkien', 'Houghton Mifflin', 'USA'],
                    [908606664, 'Slinky Malinki', 'Lynley Dodd', 'Mallinson Rendel', 'NZ']
                ];
                
                */
                $fecha = substr($auditoria->fechaEjecucion,0,10);
                $xlsx = SimpleXLSXGen::fromArray( $registros );
                $xlsx->downloadAs("Action Tracker $empresa->nombre $fecha.xlsx");
            }
            
            
           /* $pdf = new PDF();
            $pdf->setEmpresa($empresa);
            $pdf->setModelo($auditoria);
            $pdf->setSecciones($secciones);
            $pdf->setConexion($conexion);
            $pdf->generar();
            $pdf->imprimir();*/
            
            
            
            
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

