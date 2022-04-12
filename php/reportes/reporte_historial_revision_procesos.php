<?php
use php\clases\AdministradorConexion;
use php\modelos\Resultado;
use php\repositorios\ProcesosRepositorio;
use php\clases\Porcentaje;
use php\repositorios\ProcesosRevisadosRepositorio;
use php\repositorios\ProcesosRevisadosObservacionesRepositorio;


require('../vendor/fpdf181/fpdf.php');
include '../clases/Utilidades.php';
include '../clases/AdministradorConexion.php';
include '../repositorios/ProcesosRepositorio.php';
include '../repositorios/ProcesosRevisadosRepositorio.php';
include '../repositorios/ProcesosRevisadosObservacionesRepositorio.php';

abstract class PDF extends FPDF
{
    protected $font = "Helvetica";
    protected $proceso;
    protected $margen = 5;
    
    public function __construct()
    {
        parent::__construct("L","mm","A4");
    }
    
    function setProceso($proceso)
    {
        $this->proceso = $proceso;
    }
    function setConexion($conexion)
    {
        $this->conexion = $conexion;
    }
    
    function Footer()
    {
        /*$borde = 0;
        $this->SetFont($this->font, '', 9);
        $this->SetY(-10);
        $this->SetTextColor(0,0,0);
        $this->Cell(80, 8, $this->texto("Inspección realizada mediante App 10 y 7"), $borde, 0, 'C');
        $this->SetTextColor(0,0,127);
        $this->Cell(80, 8 ,'http://www.handel-sce.com/',$borde,'','',false, "http://www.handel-sce.com/");
        $this->SetTextColor(0,0,0);
        $this->Cell(30, 8,"Hoja ". $this->PageNo().' de {nb}', $borde, 0, 'C');*/
        
        $fecha = substr($this->proceso->fechaAlta,0,10);
        list($dia, $mes, $ano) = explode("/", $fecha);
        $fechaInicio.=$dia."/".$mes."/".$ano;
        $id = $this->proceso->id;
        
        $fecha = new DateTime();
        $fecha = $fecha->format("d/m/Y H:i");
        
        $this->SetTextColor(0,0,0);
        $this->SetY(-10);
        $this->SetX(0);
        $this->SetLeftMargin($this->margen);
        $borde = 0;
        $anchoColumna = ($this->w - ($this->margen * 2)) / 3;
        $this->SetFont($this->font, 'I', 9);
        $this->Cell($anchoColumna, 8, $this->texto("Reporte generado por SAHA, Revisión de procedimientos"), $borde, 0, 'L');
        $this->Cell($anchoColumna, 8, "Generado el: $fecha", $borde, 0, 'C');
        $this->Cell($anchoColumna, 8, "Hoja ". $this->PageNo().' de {nb}', $borde, 0, 'R');
        
        //$this->linea(200, 60, 141, 188, 1);
        
        
    }
    
    
    
    function Header()
    {
//         $empresaId = $this->proceso->empresaId;
//         $empresaNombre = $this->proceso->empresaNombre;
//         $folio = strtoupper($this->calcularFolio());
//         //$area = strtoupper($this->inspeccion->areaNombre);
        
//         $logoX = 5;
//         $logoY = 1;
//         $logoAlto = 6;
//         $logo = "../logos_empresas/logo$empresaId.png";
//         if (file_exists($logo))
//             $this->Image($logo,$logoX,$logoY,$logoAlto,0,'','');
//         else 
//         {
//             $logo = "../logos_empresas/default.png";
//             if (file_exists($logo))
//                 $this->Image($logo,$logoX,$logoY,$logoAlto,0,'','');
//         }
        
//         $this->SetTextColor(0,0,0);
//         $this->SetY(0);
//         $this->SetX(13);
//         $this->SetFont($this->font,'I',9);
//         $this->Cell(160, 8, $this->texto($empresaNombre), 0, 0, 'L');
        
//         //linea 
//         $this->linea(8, 60, 141, 188,1);
        
//         $fecha = new DateTime();
//         $fecha = $fecha->format("d/m/Y H:i");
       
//         //$fecha = substr($this->proceso->fechaModificacion,0,16);
//         //list($dia, $mes, $ano) = explode("/", $fecha);
//         //$fecha.=$dia."/".$mes."/".$ano;
        
//         $this->SetX(0);
//         $this->SetLeftMargin(5);
//         $borde = 0;
//         $anchoColumna = ($this->w - ($this->margen * 2)) / 3;
//         $this->SetFont($this->font, 'I', 9);
//         $this->Cell($anchoColumna, 8, "", $borde, 0, 'L');
//         $this->Cell($anchoColumna, 8, $this->texto("Historial de revisión"), $borde, 0, 'C');
//         $this->Cell($anchoColumna, 8, "Fecha: $fecha", $borde, 0, 'R');
        
       
            
          
    }
    
    private function linea($y, $r, $g, $b, $w)
    {
        $this->SetLineWidth($w);
        $this->SetDrawColor($r, $g, $b);
        $width = $this->w;
        $this->Line($this->margen, $y, $width-$this->margen, $y);
    }
    
    private function calcularFolio()
    {
        $folio ="HRP". $this->proceso->id;
        
       /* $fecha = substr($this->proceso->fechaAlta,0,10);
        list($dia, $mes, $ano) = explode("/", $fecha);
        $folio.=$dia.$mes.$ano;*/
        /*$folio.=$this->proceso->empresaNombreCorto;
        $folio.=$this->proceso->sedeNombreCorto;
        if($this->proceso->tipoAreaId==2)
            $folio.="C";
            else  if($this->proceso->tipoAreaId==3)
                $folio.="E";
                
                $fecha = substr($this->proceso->fechaproceso,0,10);
                list($dia, $mes, $ano) = explode("/", $fecha);
                $folio.=$dia.$mes.$ano;
                
                if($this->proceso->numeroCaja!="")
                    $folio.="C".$this->proceso->numeroCaja ;
                    else
                        $folio.="T".$this->proceso->numeroTractor;
                        return $folio;*/
         return $folio;
    }
    
    
    function Row($data,$borde,$lineHeight)
    {
        //Calculate the height of the row
        $nb=0;
        
        for($i=0;$i<count($data);$i++)
            $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
            $h=$lineHeight*$nb;
            //Issue a page break first if needed
            $this->CheckPageBreak($h);
            //Draw the cells of the row
            for($i=0;$i<count($data);$i++)
            {
                $w=$this->widths[$i];
                $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
                //Save the current position
                $x=$this->GetX();
                $y=$this->GetY();
                //Draw the border
                if($borde)
                    $this->Rect($x,$y,$w,$h);
                
                if($data[$i]=="@checked")
                {
                    $data[$i] =  chr(52);
                    $this->SetFont("ZapfDingbats", $this->fontWeights[$i], $this->fontSizes[$i]);
                }
                else if($data[$i]=="@unchecked")
                {
                    $data[$i] =  chr(54);
                    $this->SetFont("ZapfDingbats", $this->fontWeights[$i], $this->fontSizes[$i]);
                }
                else    
                    $this->SetFont($this->fontNames[$i],$this->fontWeights[$i],$this->fontSizes[$i]);
                //Print the text
                //$this->MultiCell($w,5,$this->texto($data[$i]),0,$a);
                $this->MultiCell($w,$lineHeight,$this->texto($data[$i]),$borde,$a);
                //Put the position to the right of the cell
                $this->SetXY($x+$w,$y);
                
                
                
            }
            //Go to the next line
            $this->Ln($h);
    }
    
    function Row2($data, $height)
    {
        //Calculate the height of the row
        $nb=0;
        for($i=0;$i<count($data);$i++)
            $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
            $h=$height*$nb;
            //Issue a page break first if needed
            $this->CheckPageBreak($h);
            //Draw the cells of the row
            for($i=0;$i<count($data);$i++)
            {
                $w=$this->widths[$i];
                $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
                //Save the current position
                $x=$this->GetX();
                $y=$this->GetY();
                
                $colorHex = $this->backgroundColors[$i];
                $rgb = $this->toRGB($colorHex);
                
                //Draw background
                $this->SetFillColor($rgb->r, $rgb->g, $rgb->b);
                $this->Rect($x,$y,$w,$h,"F");
                
                if($this->borders[$i]==1)
                {
                    $colorHex = $this->borderColors[$i];
                    $rgb = $this->toRGB($colorHex);
                    $this->SetDrawColor($rgb->r, $rgb->g, $rgb->b);
                    $this->Rect($x,$y,$w,$h,"D");
                }
                
                //$this->SetFillColor($rgb->red, $rgb->green, $rgb->blue);
                
                $this->SetFont($this->fontNames[$i],$this->fontWeights[$i],$this->fontSizes[$i]);
                //Print the text
                $this->MultiCell($w,$height,$data[$i],0,$a);
                //Put the position to the right of the cell
                $this->SetXY($x+$w,$y);
            }
            
            //Go to the next line
            $this->Ln($h);
    }
    
    function toRGB($hex)
    {
        $values = str_replace( '#', '', $hex );
        $split = str_split($values, 2);
        $r = hexdec($split[0]);
        $g = hexdec($split[1]);
        $b = hexdec($split[2]);
        return  (object)["r"=> $r, "g" => $g, "b" => $b];
    }
    
    function CheckPageBreak($h)
    {
        //If the height h would cause an overflow, add a new page immediately
        if($this->GetY()+$h>$this->PageBreakTrigger)
        {
            $this->AddPage($this->CurOrientation);
            $this->SetY(15);
            //$this->SetX($this->margen);
            $this->SetLeftMargin($this->margen);
        }
    }
    
    function NbLines($w,$txt)
    {
        //Computes the number of lines a MultiCell of width w will take
        $cw=&$this->CurrentFont['cw'];
        if($w==0)
            $w=$this->w-$this->rMargin-$this->x;
            $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
            $s=str_replace("\r",'',$txt);
            $nb=strlen($s);
            if($nb>0 and $s[$nb-1]=="\n")
                $nb--;
                $sep=-1;
                $i=0;
                $j=0;
                $l=0;
                $nl=1;
                while($i<$nb)
                {
                    $c=$s[$i];
                    if($c=="\n")
                    {
                        $i++;
                        $sep=-1;
                        $j=$i;
                        $l=0;
                        $nl++;
                        continue;
                    }
                    if($c==' ')
                        $sep=$i;
                        $l+=$cw[$c];
                        if($l>$wmax)
                        {
                            if($sep==-1)
                            {
                                if($i==$j)
                                    $i++;
                            }
                            else
                                $i=$sep+1;
                                $sep=-1;
                                $j=$i;
                                $l=0;
                                $nl++;
                        }
                        else
                            $i++;
                }
                return $nl;
    }
  
    
    
    public function generar()
    {
        $this->SetFont($this->font,'',20);
        $this->AddPage();
        $this->titulo();
        $this->empresaSede();
        $this->proceso();
        $this->procesosSinCambios();
        $this->registroDeCambiosSolicitados();
        $this->historialCambios();
    }
    
    
    private function procesosSinCambios()
    {
        $this->Ln();
        $this->Ln();
        //$this->SetY(16);
        //$this->SetLeftMargin(5);
        $this->SetFont($this->font,'B',12);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0,4,$this->texto("Registro de revisiones realizadas por usuarios y en los que no se solicitaron cambios:"),0,2,'L');
        $repositorio = new ProcesosRevisadosRepositorio($this->conexion);
        $resultado = $repositorio->consultarProcesosSinCambios($this->proceso->id);
        if($resultado->correcto())
        {
            $this->Ln();
            $procesos = $resultado->valor;
            $borde = 1;
            $this->fontSizes = array(11, 11, 11, 11);
            $this->fontWeights = array("","","","");
            $this->aligns = array("C","C","C","C");
            $this->widths = array(30, 50, 50, 50);
            $this->textColors = array("#000000","#000000","#000000","#000000");
            $this->borders = array(1,1,1,1);
            $this->borderColors = array("#afb2b0","#afb2b0","#afb2b0","#afb2b0");
            $this->backgroundColors = array("#ffffff","#ffffff","#ffffff","#ffffff");
            $this->Row2(array("Fecha","Nombre","Apellido","Resultado"),5);
            
            //$this->fontWeights = array("","","","");
            //$this->aligns = array("C","C","C","C");
            
            
            for($i = 0; $i < count($procesos); $i++)
            {
                $proceso = $procesos[$i];
                //             $color = "";
                //             if($i%2==0)
                $color = "#ffffff";
                    //             else
                        //                 $color = "#f5f5f5";
                $this->borders = array(1,1,1,1,1);
                $this->backgroundColors = array($color,$color,$color,$color,$color);
                $this->fontWeights = array("","","","","");
                //$titulo = $this->texto(trim($tarea->titulo));
                $this->Row2(array($proceso->fecha,$this->texto($proceso->usuarioNombre),$this->texto($proceso->usuarioApellido), $this->texto($proceso->estatusRevisionNombre)),8);
                            
            }
        }
        
    }
    
    private function registroDeCambiosSolicitados()
    {
        $this->Ln();
        $this->Ln();
        $this->SetFont($this->font,'B',12);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0,4,$this->texto("Registro de cambios autorizados:"),0,2,'L');
        $repositorio = new ProcesosRevisadosObservacionesRepositorio($this->conexion);
        $resultado = $repositorio->consultar((object)["procesoId" => $this->proceso->id,"estatusValidacionId"=>EstatusValidacionProceso::AUTORIZADO]);
        if($resultado->correcto())
        {
            $observaciones = $resultado->valor;
            $this->Ln();
            $borde = 1;
            $this->fontSizes = array(11, 11, 11, 11, 11, 11, 11, 11);
            $this->fontWeights = array("","","","","","","","");
            $this->aligns = array("C","C","C","C","C","C","C","C");
            $this->widths = array(30, 30, 30, 40, 27, 40, 40, 40);
            $this->textColors = array("#000000","#000000","#000000","#000000","#000000","#000000","#000000","#000000");
            $this->borders = array(1,1,1,1,1,1,1,1);
            $this->borderColors = array("#afb2b0","#afb2b0","#afb2b0","#afb2b0","#afb2b0","#afb2b0","#afb2b0","#afb2b0");
            $this->backgroundColors = array("#ffffff","#ffffff","#ffffff","#ffffff","#ffffff","#ffffff","#ffffff","#ffffff");
            $this->Row2(array("Fecha","Nombre","Apellido","Comentario",$this->texto("Sección"),"Tipo","Estado", "Revisado por"),5);
            
            
            for($i = 0; $i < count($observaciones); $i++)
            {
                $observacion = $observaciones[$i];
                //$color = "#ffffff";
                //$this->borders = array(1,1,1,1,1,1,1,1);
                //$this->backgroundColors = array($color,$color,$color,$color,$color);
                //$this->fontWeights = array("","","","","");
                $fecha = substr($observacion->fechaAlta, 0,10);
                $this->Row2(array($fecha,$this->texto($observacion->usuarioNombre),$this->texto($observacion->usuarioApellido), $this->texto($observacion->descripcionAdmin),$this->texto($observacion->seccionAdmin),$this->texto($observacion->tipoObservacionNombre),$this->texto($observacion->estatusValidacionDescripcion),$this->texto($observacion->validadorNombreCompleto)),8);
                
            }
        }
        
    }
    
    private function historialCambios()
    {
        $this->AddPage();
        $this->SetLeftMargin(10);
        $this->SetFont($this->font,'B',12);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0,4,$this->texto("Histórico de revisiones y cambios:"),0,2,'L');
        $repositorio = new ProcesosRevisadosRepositorio($this->conexion);
        $resultado = $repositorio->consultarHistorialCambios($this->proceso->id);
        if($resultado->correcto())
        {
            $observaciones = $resultado->valor;
            $this->Ln();
            $borde = 1;
            //277
            $this->fontSizes = array(11, 11, 11, 11, 11, 11, 11);
            $this->fontWeights = array("","","","","","","");
            $this->aligns = array("C","C","C","C","C","C","C");
            $this->widths = array(50, 30, 30, 40, 47, 40, 40);
            $this->textColors = array("#000000","#000000","#000000","#000000","#000000","#000000","#000000");
            $this->borders = array(1,1,1,1,1,1,1);
            $this->borderColors = array("#afb2b0","#afb2b0","#afb2b0","#afb2b0","#afb2b0","#afb2b0","#afb2b0");
            $this->backgroundColors = array("#ffffff","#ffffff","#ffffff","#ffffff","#ffffff","#ffffff","#ffffff");
            $this->Row2(array("Fecha","Nombre","Apellido",$this->texto("Sección"),"Tipo","Estado", "Revisado por"),5);
            
            
            for($i = 0; $i < count($observaciones); $i++)
            {
                $observacion = $observaciones[$i];
                //$color = "#ffffff";
                //$this->borders = array(1,1,1,1,1,1,1,1);
                //$this->backgroundColors = array($color,$color,$color,$color,$color);
                //$this->fontWeights = array("","","","","");
               // $fecha = substr($observacion->fecha, 0,10);
                $fecha = $observacion->fecha;
                $this->Row2(array($fecha,$this->texto($observacion->usuarioNombre),$this->texto($observacion->usuarioApellido),$this->texto($observacion->seccion),$this->texto($observacion->tipoObservacionNombre),$this->texto($observacion->estatusValidacionDescripcion),$this->texto($observacion->validadorNombreCompleto)),8);
                
            }
        }
        
    }
    
    
    function MultiCellRow($cells, $height, $data)
    {
        $x = $this->GetX();
        $y = $this->GetY();
        $maxheight = 0;
        
        for ($i = 0; $i < $cells; $i++) {
            $width = $this->widths[$i];
            $this->MultiCell($width, $height, $data[$i]);
            if ($this->GetY() - $y > $maxheight) $maxheight = $this->GetY() - $y;
            $this->SetXY($x + ($width * ($i + 1)), $y);
        }
        
        for ($i = 0; $i < $cells + 1; $i++) {
            $width = $this->widths[$i];
            $this->Line($x + $width * $i, $y, $x + $width * $i, $y + $maxheight);
        }
        
        $this->Line($x, $y, $x + $width * $cells, $y);
        $this->Line($x, $y + $maxheight, $x + $width * $cells, $y + $maxheight);
    }
    
    
    public function imprimir()
    {
        $filename ="../reportes_historial_revision_proceso/";
        $filename.=$this->calcularFolio();
        $filename.=".pdf";
        
        //var_dump($this->proceso);
        
        $this->Output($filename,'F');
        
        if (file_exists($filename))
        {
            header('Location:'. $filename);
        }
    }
    
    
    function titulo()
    {
        $this->Ln();
        //$this->SetY(16);
        //$this->SetLeftMargin(5);
        $this->SetFont($this->font,'B',25);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0,10,$this->texto("Historial de revisión"),0,2,'C');
    }
    
    function empresaSede()
    {
        $this->Ln();
        //$this->SetY(16);
        //$this->SetLeftMargin(5);
        $this->SetFont($this->font,'B',14);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0,4,$this->texto("Empresa: " . $this->proceso->empresaNombre),0,2,'L');
        $this->Ln();
        $this->Cell(0,4,$this->texto("Sede: " .$this->proceso->sedeNombre),0,2,'L');
    }
    
    function proceso()
    {
        $this->Ln();
        $this->Ln();
        //$this->SetY(16);
        //$this->SetLeftMargin(5);
        $this->SetFont($this->font,'BU',20);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0,4,$this->texto($this->proceso->nombre),0,2,'L');
    }
    
    
    function subtitulo($texto)
    {
        /*$this->Ln();
        $this->SetFont($this->font,'B',14);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0,0,$this->texto($texto),0,2);
        $y = $this->GetY();
        $this->linea($y + 3, 0, 0, 0, 0.5);*/
        $borde = 0;
        $this->SetLeftMargin(5);
        $this->SetFont($this->font,'B',16);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(30, 6, $this->texto($texto), $borde, 0, 'L');
        
        $this->linea($this->GetY() + 6, 0, 0, 0, 0.3);
       
    }
  
    function formatoFecha($fecha)
    {
        $f = substr($fecha,0,10);
        $hora = substr($fecha,11,5);
        list($ano, $mes, $dia) = explode("-", $f);
        $fecha = "$dia/$mes/$ano $hora";
        return $fecha;
    }
    
    
    
    public $tablewidths;
    public $aligns;
    public $columnFonts;
    public $footerset;
    
    
    
    
    function correctImageOrientation($filename) {
        
        if (function_exists('exif_read_data')) {
            $exif = exif_read_data($filename);
            if($exif && isset($exif['Orientation'])) {
                $orientation = $exif['Orientation'];
                if($orientation != 1){
                    $img = imagecreatefromjpeg($filename);
                    $deg = 0;
                    switch ($orientation) {
                        case 3:
                            $deg = 180;
                            break;
                        case 6:
                            $deg = 270;
                            break;
                        case 8:
                            $deg = 90;
                            break;
                    }
                    if ($deg) {
                        $img = imagerotate($img, $deg, 0);
                    }
                    // then rewrite the rotated image back to the disk as $filename
                    imagejpeg($img, $filename, 95);
                } // if there is some rotation necessary
            } // if have the exif orientation info
        } // if function exists
    }
    
   
    
    function texto($texto)
    {
       // return iconv('UTF-8', 'windows-1252', $texto);
        //return iconv('UTF-8', 'windows-1252', html_entity_decode($texto));
        //$t = mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
        //$t = mb_convert_encoding($texto, 'windows-1252', 'UTF-8');
        
        $texto = str_replace("\xe2\x80\xa8", '', $texto);
        $texto = str_replace("\xe2\x80\xa9", '', $texto);
        
        $texto = iconv('UTF-8', 'windows-1252', $texto);
        
        
        
        return $texto;
    }
    
 
}



class Reporteproceso extends PDF
{
  
   
    
   
    
}


class ReporteFabrica
{
    public function crear()
    {
        $reporte = new Reporteproceso();
        return $reporte;
    }
}


$administrador_conexion = new AdministradorConexion();
try
{
    $conexion = $administrador_conexion->abrir();
    if($conexion)
    {
        $repositorio = new ProcesosRepositorio($conexion);
        $procesoId = REQUEST('procesoId');
        
        $llaves= (object) [
            'id' =>  $procesoId
        ];
        
        $resultado = $repositorio->consultarPorLlaves($llaves,true);
        
        if($resultado->correcto())
        {
            $proceso = $resultado->valor;
            $reporteFabrica = new ReporteFabrica();
            $reporte = $reporteFabrica->crear();
            if($reporte!=null)
            {
                $reporte->setProceso($proceso);
                $reporte->setConexion($conexion);
                $reporte->AliasNbPages();
                $reporte->generar();
                $reporte->imprimir();
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


