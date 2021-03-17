<?php
use php\clases\AdministradorConexion;
use php\repositorios\AuditoriasRepositorio;
use php\modelos\Resultado;
use php\repositorios\EmpresasRepositorio;


require('../vendor/fpdf181/fpdf.php');
include '../clases/Utilidades.php';
include '../clases/AdministradorConexion.php';
include '../repositorios/AuditoriasRepositorio.php';
require_once('../repositorios/EmpresasRepositorio.php');
require_once("../../vendor/autoload.php");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xls\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;



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
               
                $registros  = array();
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
                $encabezados = [
                    'Punto #',
                        'Prioridad',
                        'Fecha de creación',
                        'Observaciones detectadas',
                        'Responsable',
                        '% Cumplimiento',
                        'Fecha compromiso' ,
                        'Notas & Seguimiento / Estatus',
                        'Fecha real en que se realizó'
                ];
                
                crearExcel($encabezados,$registros,$auditoria, $empresa);
                
                
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

function crearExcel($encabezados, $registros,$auditoria,$empresa)
{
    //object of the Spreadsheet class to create the excel data
    $spreadsheet = new Spreadsheet();
    
    $sheet = $spreadsheet->setActiveSheetIndex(0);
    
    crearInformacion($sheet);
    crearEncabezados($sheet,$encabezados);
    crearRegistros($sheet,$registros);
    
    
    //set style for A1,B1,C1 cells
    $cell_st =[
        'font' =>['bold' => true],
        'alignment' =>['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        'borders'=>['bottom' =>['style'=> \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM]]
    ];
    $spreadsheet->getActiveSheet()->getStyle('A1:C1')->applyFromArray($cell_st);
    
    //set columns width
    $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(16);
    $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(18);
    
    $spreadsheet->getActiveSheet()->setTitle('Simple'); //set a title for Worksheet
    
    //make object of the Xlsx class to save the excel file
    
    $fecha = substr($auditoria->fechaEjecucion,0,10);
    $nombreArchivo = "Action Tracker $empresa->nombre $fecha.xlsx";
    
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="'. $nombreArchivo.'"');
   // ob_start();
    $writer->save('php://output');
    //ob_end_clean();
}

function crearInformacion($sheet)
{
    $sheet->getColumnDimension("A")->setWidth(8); //Punto
    $sheet->getColumnDimension("B")->setWidth(10); // Prioridad
    $sheet->getColumnDimension("C")->setWidth(25); //Fecha de creacion
    $sheet->getColumnDimension("D")->setWidth(150); //Observaciones
    $sheet->getColumnDimension("E")->setWidth(40); //Responsable
    $sheet->getColumnDimension("F")->setWidth(25); //% Cumplimiento
    $sheet->getColumnDimension("G")->setWidth(25); //Fecha de compromiso
    $sheet->getColumnDimension("H")->setWidth(60); //Notas
    $sheet->getColumnDimension("I")->setWidth(25); //Fecha de finalizacion
    
    //$sheet->setCellValue('F1', 'Seguimiento de acciones');
    
   
}


function crearEncabezados($sheet, $encabezados)
{
    $renglon = 1;
    for ($i = 0; $i < count($encabezados); $i++)
    {
        $sheet->setCellValueByColumnAndRow($i + 1, $renglon, $encabezados[$i]);
    }
    
    $cell_st =[
        'font' =>['bold' => true],
        'alignment' =>['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        'borders'=>['bottom' =>['style'=> \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM]],
        
    ];
    //$sheet->getStyle("A$renglon:I$renglon")->applyFromArray($cell_st);
    
    $styleArray = array(
        'font'  => array(
            'bold'  => true,
            'color' => array('rgb' => 'FFFFFF'),
            'size'  => 12,
            'name'  => 'Verdana'
        ),
        'alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER),
        'borders' => array(
            'outline' => array(
                'borderStyle' => PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => array('argb' => '00000000'),
            ),
        ),
        'fill' => array(
            'fillType' => PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => array('argb' => 'FF4F81BD')
        )
    );
    $sheet->getStyle("A$renglon:I$renglon")->applyFromArray($styleArray);
    
    $sheet->getStyle("A$renglon:I$renglon")->getAlignment()->setWrapText(true);
    
    
    
    
    
    
}

function crearRegistros($sheet, $registros)
{
    
    $renglon = 2;
    for ($i = 0; $i < count($registros);  $i++) 
    { // row $i
        $j = 0;
        
        foreach ($registros[$i] as $k => $v) { // column $j
            $sheet->setCellValueByColumnAndRow($j + 1, ($renglon), $v);
            $j++;
        }
        
        
        $styleArray = array(
            'font'  => array(
                'bold'  => false,
                'color' => array('rgb' => '000000'),
                'size'  => 12,
                'name'  => 'Verdana'
            ),
            'alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER),
            'borders' => array(
                'outline' => array(
                    'borderStyle' => PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('argb' => '00000000'),
                ),
            ),
            'fill' => array(
                'fillType' => PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => array('argb' => 'FFFFFF')
            )
        );
        
        $sheet->getStyle("A$renglon:I$renglon")->applyFromArray($styleArray);
        
        $sheet->getStyle("A$renglon:I$renglon")->getAlignment()->setWrapText(true);
        
        $sheet->getStyle("D$renglon")->getAlignment()->setHorizontal("left");
        
        
        $renglon++;
    }
}