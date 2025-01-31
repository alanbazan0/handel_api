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
            $secciones = array();
            $repositorio = new AuditoriasRepositorio($conexion);
            $resultado = $repositorio->consultarValoresSecciones($llaves);
            if($resultado->correcto())
            {
                $secciones = $resultado->valor;
                $resultado = $repositorio->getHallazgos($secciones,"notificacion");
                if($resultado->correcto())
                {
                    $observaciones = $resultado->valor;
                    $registros  = array();
                    $punto =  1;
                    for($i = 0; $i < count($observaciones); $i++)
                    {
                        $observacion = $observaciones[$i];
                        $registro  = [$punto,
                            $observacion->hallazgo,
                            '',
                            '',
                            '',
                            ''];
                            
                            array_push($registros,$registro);
                            $punto++;
                    }
                    $encabezados = [
                        'Punto #',
                        'Observación',
                        'Plan de Acción',
                        'Fecha compromiso de realizacíon',
                        'Responsable de realización',
                        'Firma del responsable'
                    ];
                    
                    crearExcel($encabezados,$registros,$auditoria, $empresa);
                    
                    
                }
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
    //$spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray($cell_st);
    
    //set columns width
    //$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(16);
    //$spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(18);
    
    $spreadsheet->getActiveSheet()->setTitle('Plan de acción'); //set a title for Worksheet
    
    //make object of the Xlsx class to save the excel file
    
    $fecha = substr($auditoria->fechaEjecucion,0,10);
    $nombreArchivo = "Plan de acción $empresa->nombre $fecha.xlsx";
    
   $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="'. $nombreArchivo.'"');
    $writer->save('php://output');
    
   
}

function crearInformacion($sheet)
{
    $sheet->getColumnDimension("A")->setWidth(8); //Punto
    $sheet->getColumnDimension("B")->setWidth(150); // Observacion
    $sheet->getColumnDimension("C")->setWidth(50); //Plan de acción
    $sheet->getColumnDimension("D")->setWidth(25); //Fecha compromiso de realizacion
    $sheet->getColumnDimension("E")->setWidth(40); //Responsable de realizacion
    $sheet->getColumnDimension("F")->setWidth(40); //Firma de responsable
 
    
   
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
    $sheet->getStyle("A$renglon:F$renglon")->applyFromArray($styleArray);
    
    $sheet->getStyle("A$renglon:F$renglon")->getAlignment()->setWrapText(true);
    
    
    
    
    
    
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
        
        $sheet->getStyle("A$renglon:F$renglon")->applyFromArray($styleArray);
        
        $sheet->getStyle("A$renglon:F$renglon")->getAlignment()->setWrapText(true);
        
        $sheet->getStyle("B$renglon")->getAlignment()->setHorizontal("left");
        
        
        $renglon++;
    }
}