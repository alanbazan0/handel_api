<?php
use php\clases\AdministradorConexion;
use php\modelos\Resultado;
use php\repositorios\AuditoriasRepositorio;
use PhpOffice\PhpSpreadsheet\Shared\Date;


require('../vendor/fpdf181/fpdf.php');
include '../clases/Utilidades.php';
include '../clases/AdministradorConexion.php';
include '../repositorios/AuditoriasRepositorio.php';
require_once('../highcharts/highchartutils.php');

abstract class PDF extends FPDF
{
    protected $font = "Helvetica";
    protected $auditoria;
    protected $margen = 25;
    protected $conexion;
    
    public function __construct()
    {
        parent::__construct("L","mm","A4");
        $this->SetLeftMargin($this->margen);
        $this->SetRightMargin($this->margen);
    }
    
    function setAuditoria($auditoria)
    {
        $this->auditoria = $auditoria;
    }
    
    function setConexion($conexion)
    {
        $this->conexion = $conexion;
    }
    
    function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        $k=$this->k;
        if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak())
        {
            $x=$this->x;
            $ws=$this->ws;
            if($ws>0)
            {
                $this->ws=0;
                $this->_out('0 Tw');
            }
            $this->AddPage($this->CurOrientation);
            $this->x=$x;
            if($ws>0)
            {
                $this->ws=$ws;
                $this->_out(sprintf('%.3F Tw',$ws*$k));
            }
        }
        if($w==0)
            $w=$this->w-$this->rMargin-$this->x;
            $s='';
            if($fill || $border==1)
            {
                if($fill)
                    $op=($border==1) ? 'B' : 'f';
                    else
                        $op='S';
                        $s=sprintf('%.2F %.2F %.2F %.2F re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
            }
            if(is_string($border))
            {
                $x=$this->x;
                $y=$this->y;
                if(is_int(strpos($border,'L')))
                    $s.=sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
                    if(is_int(strpos($border,'T')))
                        $s.=sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
                        if(is_int(strpos($border,'R')))
                            $s.=sprintf('%.2F %.2F m %.2F %.2F l S ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
                            if(is_int(strpos($border,'B')))
                                $s.=sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
            }
            if($txt!='')
            {
                if($align=='R')
                    $dx=$w-$this->cMargin-$this->GetStringWidth($txt);
                    elseif($align=='C')
                    $dx=($w-$this->GetStringWidth($txt))/2;
                    elseif($align=='FJ')
                    {
                        //Set word spacing
                        $wmax=($w-2*$this->cMargin);
                        $this->ws=($wmax-$this->GetStringWidth($txt))/substr_count($txt,' ');
                        $this->_out(sprintf('%.3F Tw',$this->ws*$this->k));
                        $dx=$this->cMargin;
                    }
                    else
                        $dx=$this->cMargin;
                        $txt=str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));
                        if($this->ColorFlag)
                            $s.='q '.$this->TextColor.' ';
                            $s.=sprintf('BT %.2F %.2F Td (%s) Tj ET',($this->x+$dx)*$k,($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k,$txt);
                            if($this->underline)
                                $s.=' '.$this->_dounderline($this->x+$dx,$this->y+.5*$h+.3*$this->FontSize,$txt);
                                if($this->ColorFlag)
                                    $s.=' Q';
                                    if($link)
                                    {
                                        if($align=='FJ')
                                            $wlink=$wmax;
                                            else
                                                $wlink=$this->GetStringWidth($txt);
                                                $this->Link($this->x+$dx,$this->y+.5*$h-.5*$this->FontSize,$wlink,$this->FontSize,$link);
                                    }
            }
            if($s)
                $this->_out($s);
                if($align=='FJ')
                {
                    //Remove word spacing
                    $this->_out('0 Tw');
                    $this->ws=0;
                }
                $this->lasth=$h;
                if($ln>0)
                {
                    $this->y+=$h;
                    if($ln==1)
                        $this->x=$this->lMargin;
                }
                else
                    $this->x+=$w;
    }
    
    function Footer()
    {
        $this->SetLeftMargin($this->margen);
        $this->SetRightMargin($this->margen);
        
        $fecha = substr($this->auditoria->fecha,0,10);
        list($dia, $mes, $ano) = explode("/", $fecha);
        
        setlocale(LC_TIME,"es_ES");
        $tiempo = gmmktime(12,0,0,$mes,$dia,$ano);
        
        $fechaAuditoria = strftime("%A, %d de %B de %Y",$tiempo);
        $fechaAuditoria = utf8_encode($fechaAuditoria);
        //$fechaInicio.=$dia."/".$mes."/".$ano;
        
       
        $id = $this->auditoria->id;
        
        $this->SetTextColor(0,0,0);
        $this->SetY(-20);
        //$this->SetX(0);
        $borde = 0;
        //$this->rMargin = 0;
        //$anchoColumna = ($this->w - ($this->margen * 2)) / 3;
        $anchoColumna = ($this->w - ($this->margen * 2)) / 3;
        $this->SetFont($this->font, 'I', 9);
        $this->Cell($anchoColumna, 8, $this->texto("Fecha de auditoría: ").$this->texto($fechaAuditoria) , $borde, 0, 'L');
        $this->Cell($anchoColumna, 8, "sivah.apps-handel.com", $borde, 0, 'C', false,"https://sivah.apps-handel.com");
        $this->Cell($anchoColumna, 8, "Hoja ". $this->PageNo().' de {nb}', $borde, 0, 'R');
        
        $this->linea($this->h-20, 43,110,28, 0.6);
        
        
    }
    
    
    
    function Header()
    {
        $this->SetLeftMargin($this->margen);
        $this->SetRightMargin($this->margen);
        
        $empresaId = $this->auditoria->empresaId;
        $empresaNombre = $this->auditoria->empresaNombre;
        $sedeNombre = $this->auditoria->sedeNombre;
        //$folio = strtoupper($this->calcularFolio());
        //$area = strtoupper($this->inspeccion->areaNombre);
        
       
        $borde = 0;
        $this->SetTextColor(0,0,0);
        //this->SetY(10);
        //$this->SetX(13);
        $this->SetFont($this->font,'I',9);
        
        
        //linea 
       
        
        $fecha = new DateTime();
        $fecha = $fecha->format("d/m/Y H:i");
       
        //$fecha = substr($this->auditoria->fechaModificacion,0,16);
        //list($dia, $mes, $ano) = explode("/", $fecha);
        //$fecha.=$dia."/".$mes."/".$ano;
        
       // $this->SetX(0);
        
       
        $anchoColumna = ($this->w - ($this->margen * 2)) / 2;
        //$this->SetFont($this->font, 'I', 9);
        //$this->Cell($anchoColumna, 8, "", $borde, 0, 'L');
       //$this->Cell($anchoColumna, 8, "SAHA Tareas", $borde, 0, 'C');
        $this->Cell(8, 8, "", $borde, 0, 'L');
        $this->Cell($anchoColumna, 8, $this->texto("$empresaNombre - $sedeNombre"), $borde, 0, 'L');
        $this->Cell($anchoColumna-8, 4, "Reporte generado el: $fecha", $borde, 0, 'R');
        
        $this->Ln();
        $porcentaje = $this->auditoria->porcentajeValidadas;
        $width = ($this->w - ($this->margen * 2)) ;
        $this->Cell($width, 4, "Avance a la fecha: $porcentaje%", $borde, 0, 'R');
        
        $this->linea(20,43,110,28,0.6);
        
        $logoX = 25;
        $logoY = 11;
        $logoAlto = 6;
        $logo = "../logos_empresas/logo$empresaId.png";
        if (file_exists($logo))
            $this->Image($logo,$logoX,$logoY,$logoAlto,0,'','');
        else
        {
            $logo = "../logos_empresas/default.png";
            if (file_exists($logo))
                $this->Image($logo,$logoX,$logoY,$logoAlto,0,'','');
        }
            
          
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
       // $folio ="Seguimiento". $this->auditoria->id;
        $folio.="SEG.".$this->auditoria->referencia;
       /* $fecha = substr($this->auditoria->fechaAlta,0,10);
        list($dia, $mes, $ano) = explode("/", $fecha);
        $folio.=$dia.$mes.$ano;*/
        /*$folio.=$this->auditoria->empresaNombreCorto;
        $folio.=$this->auditoria->sedeNombreCorto;
        if($this->auditoria->tipoAreaId==2)
            $folio.="C";
            else  if($this->auditoria->tipoAreaId==3)
                $folio.="E";
                
                $fecha = substr($this->auditoria->fechaauditoria,0,10);
                list($dia, $mes, $ano) = explode("/", $fecha);
                $folio.=$dia.$mes.$ano;
                
                if($this->auditoria->numeroCaja!="")
                    $folio.="C".$this->auditoria->numeroCaja ;
                    else
                        $folio.="T".$this->auditoria->numeroTractor;
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
            $this->SetY(25);
            $this->SetX($this->margen);
            //$this->SetLeftMargin(5);
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
    
    protected function imprimirPuntosTabla($puntos,$borde)
    {
        
      
        for($i = 0 ; $i < count($puntos); $i++)
        {
            $punto = $puntos[$i];
            $descripcion = $punto->id .". " . $punto->descripcion;
            
            $resultado = "";
            if($punto->resultado == "S")
            {
               // $this->SetFont("ZapfDingbats", '', 9);
                $resultado =  "@checked";
            }
            else if($punto->resultado == "N")
            {
                //  $this->SetFont("ZapfDingbats", '', 9);
                $resultado =  "@unchecked";
            }
            else
            {
                // $this->SetFont($this->font, '', 9);
                $resultado =  "N/A";
            }
            
            $observaciones = $punto->observaciones;
            if(substr( $observaciones, 0, 1 ) === ",")
            {
                $observaciones = substr($observaciones, 1);
            }
            
            $observaciones = trim($observaciones);
            $observaciones=str_replace("\r",'',$observaciones);
            $observaciones=str_replace("\n",'',$observaciones);
            
            //$data[] = array($descripcion,$resultado, $observaciones);
            
            $this->Row(array($descripcion,$resultado,$observaciones),$borde,8);
        }
      //  $this->SetLeftMargin(10);
        //$this->SetTopMargin(0);
       // $this->morepagestable($data);
    }
    protected function imprimirPuntos3($titulo, $puntos)
    {
        $borde = 1;
        $anchoColumna1 = 48;
        $anchoColumna2 = 30;
        $anchoColumna3 = 112;
        
        $this->SetLeftMargin(10);
        $this->SetFont($this->font, 'B', 13);
        $this->Ln();
        $this->SetFillColor(242, 242, 242);
        $this->Cell(0,8,$this->texto($titulo),$borde,2,'C',1);
        
        
        $this->SetFont($this->font, 'B', 10);
        $this->Cell($anchoColumna1, 8, $this->texto("Descripción"), $borde, 0, 'C', 1);
        $this->Cell($anchoColumna2, 8, "Resultado", $borde, 0, 'C', 1);
        $this->Cell($anchoColumna3, 8, "Observaciones", $borde, 0, 'C', 1);
        $altoColumna = 6.5;
        $page_height = 286.93;
        for($i = 0 ; $i < count($puntos); $i++)
        {
            $punto = $puntos[$i];
            $descripcion = $punto->id .". " . $punto->descripcion;
            $observaciones = $punto->observaciones;
            
            $y = $this->GetY();
            //$space_left=$page_height-($this->GetY()); // space left on page
            if ( $y > $page_height) {
                $this->SetLeftMargin(10);
                $this->AddPage(); // page break
            }
            
            $this->Ln();
            $this->SetFont($this->font, '', 9);
            $this->Cell($anchoColumna1, $altoColumna, $this->texto($descripcion), $borde, 0, 'L');
            
            
            $resultado = "";
            if($punto->resultado == "S")
            {
                $this->SetFont("ZapfDingbats", '', 9);
                $resultado =  chr(52);
            }
            else if($punto->resultado == "N")
            {
                $this->SetFont("ZapfDingbats", '', 9);
                $resultado =  chr(54);
            }
            else
            {
                $this->SetFont($this->font, '', 9);
                $resultado =  "N/A";
            }
            
            if(substr( $observaciones, 0, 1 ) === ",")
            {
                $observaciones = substr($observaciones, 1);
            }
            
          
            $this->Cell($anchoColumna2, $altoColumna, $resultado, $borde, 0, 'C');
            $this->SetFont($this->font, '', 9);
            $this->MultiCell($anchoColumna3, $altoColumna, $this->texto($observaciones), $borde);
        }
        
        
        // $this->Line(10, $y, 210-10, $y);
    }
    
    function portada()
    {
        $this->AddPage();
       
        $this->SetY(35);
        $this->SetFont($this->font,'B',13);
        $this->SetTextColor(0, 0, 0);
       // $this->Cell(0, 15, $this->texto("Reporte de Seguimiento SIVAH"),0,1,'L',1);
        $this->Cell(0,8,$this->texto("Reporte de Seguimiento SIVAH"),0,2,'C');
        
        $imagen = "../imagenes/portada_seguimiento.jpg";
        //$width = ($this->w - ($this->margen * 2)) ;
        $width = $this->w;
        $anchoFoto = 140;
        $x = ($width/2) - ($anchoFoto/2);
        $y = 50;
        $this->Image($imagen,$x,$y,$anchoFoto);
        
        $calificacionValidadas = $this->auditoria->porcentajeValidadas;
        $calificacionValidadasEnviadas = $this->auditoria->porcentajeValidadasEnviadas;
        $this->SetFont($this->font,'',11);
        $this->SetY(155);
        $this->Cell(0,6,$this->texto("Calificación de auditoría actualizada:"),0,2,'R');
        $this->Cell(0,6,$this->texto("Incluyendo validadas: $calificacionValidadas%"),0,2,'R');
        $this->Cell(0,6,$this->texto("Incluyendo validadas y enviadas: $calificacionValidadasEnviadas%"),0,2,'R');
        //$this->Cell(0, 4, $this->texto("Calificación de auditoría actualizada: $calificacion",0, 0, 'R');
//         $this->SetY(135);
//         $this->SetX(65);
//         $this->SetFont($this->font,'B',15);
//         $this->SetTextColor(255, 255, 255);
//         $this->SetFillColor(63, 103, 151);
       
        
    }
    
    public function licencia()
    {
        $this->AddPage();
        $this->SetY(25);
        $this->subtitulo("Licencia de uso");
        $this->linea($this->GetY() + 4, 71,151,194, 0.5);
        $tamanoLinea = 8;
        $borde = 0;
        $this->SetFont($this->font,'',11);
        
        $this->SetY($this->GetY() + 10);
        $this->Cell(0,$tamanoLinea,$this->texto('Este documento incluye el avance a la fecha de generación respecto de la auditoría practicada en su instalación, los registros se'),$borde,1,'FJ');
        $this->Cell(0,$tamanoLinea,$this->texto('generan en forma automatizada por el sistema e incluyen las evidencias presentadas para el cierre de los hallazgos hasta la fecha'),$borde,1,'FJ');
        $this->Cell(0,$tamanoLinea,$this->texto('de generación del reporte. Es posible que las últimas evidencias continúen aun en validación por parte del equipo de Handel,'),$borde,1,'FJ');
        $this->Cell(0,$tamanoLinea,$this->texto('considere que estas gráficas podrían ser ajustadas en el caso de que una evidencia sea rechazada por no cumplir con lo solicitado'),$borde,1,'FJ');
        $this->Cell(0,$tamanoLinea,$this->texto('en el seguimiento.'),$borde,1,'L');
        
        $this->Ln();
        
        $this->Cell(0,$tamanoLinea,$this->texto('Puede verificar el reporte original de la auditoría o el seguimiento directamente en la plataforma SIVAH ingresando a'),$borde,1,'FJ');
        $this->SetFont($this->font,'U',11);
        $this->Cell(0, 8, "www.sivah.apps-handel.com", $borde, 0, 'L', false,"https://sivah.apps-handel.com");
        $this->SetFont($this->font,'',11);
        
        $this->Ln();
        $this->Ln();
        $this->Cell(0,$tamanoLinea,$this->texto('Si tiene dudas de la información contenida en este reporte puede contactar directamente a su especialista en Handel Consultoría.'),$borde,1,'FJ');
        
        $this->Ln();
        $this->Cell(0,$tamanoLinea,$this->texto('En caso de no estar de acuerdo en alguna de las condiciones, el lector tiene la obligación de dejar de leer inmediatamente el'),$borde,1,'FJ');
        $this->Cell(0,$tamanoLinea,$this->texto('reporte y dar aviso a su especialista asignado de Handel Consultoría o mediante el correo electrónico mail@handel-sce.com.'),$borde,1,'L');
        
        
        
    }
    
    private function subtitulo($texto)
    {
        $this->SetFont($this->font,'B',13);
        $this->Cell(0,8,$this->texto($texto),0,2,'L');
    }
    
    private function distribucion()
    {
        $this->AddPage();
        $this->SetY(25);
        $this->subtitulo("Distribución de hallazgos");
        
        $chartWidth = 120;
        $repositorio = new AuditoriasRepositorio($this->conexion);
        $resultado = $repositorio->consultarHallazgosDepartamento($this->auditoria->id);
        if($resultado->correcto())
        {
            $hallazgos = $resultado->valor;
            $coloresBase = [ "#78d34b", "#f6ba36", "#0c9cfb","#e13d47","#ca3675","#6a666a","#958b34","#81bede"];
            $colores  =  $this->generarColores($coloresBase,count($hallazgos));
            $image = toPieChartWithLabels("",'','',$hallazgos,"nombre","total",$colores,20);
            if($image!='')
                $this->Image($image,30 ,60, $chartWidth);
            
            $image = toColumnChartColors("",'Hallazgos','',$hallazgos,"nombre","total",$colores,false,0);
            if($image!='')
                $this->Image($image,150, 60, $chartWidth);
        }
    }
    
    function generarColor()
    {
        $color = '#';
        $colorHexLighter = array("9","A","B","C","D","E","F" );
        for($x=0; $x < 6; $x++):
        $color .= $colorHexLighter[array_rand($colorHexLighter, 1)]  ;
        endfor;
        return substr($color, 0, 7);
    }
    
    function generarColores($colores,$n)
    {
        $limite = $n -  count($colores);
        for ($i = 0; $i < $limite; $i++) 
        {
            $color = $this->generarColor();
            array_push($colores,$color);
        }
        return $colores;
    }
    
    public function generar()
    {
        $this->SetFont($this->font,'',20);
      
        $this->portada();
        $this->licencia();
        $this->distribucion();
        $this->avanceFecha();
        $this->cierreHallazgosMes();
        $this->cierre();
        $this->tareas();
    }
    
    private function cierre()
    {
        $this->AddPage();
        $this->SetY(25);
        $this->subtitulo("Cierre de hallazgos por día");
        
        $chartWidth = 220;
        
        $fechas = array();
        $fecha = substr($this->auditoria->fecha,0,10);
        list($dia, $mes, $ano) = explode("/", $fecha);
        
        $fecha = new DateTime();
        $fecha->setDate($ano,$mes,1);
        array_push($fechas,$fecha);

        $fecha = new DateTime();
        $fecha->setDate($ano,$mes,1);
        $fecha->add(new DateInterval('P1M'));
        array_push($fechas,$fecha);
        
        $fecha = new DateTime();
        $fecha->setDate($ano,$mes,1);
        $fecha->add(new DateInterval('P2M'));
        array_push($fechas,$fecha);
        
        $fecha = new DateTime();
        $fecha->setDate($ano,$mes,1);
        $fecha->add(new DateInterval('P3M'));
        array_push($fechas,$fecha);
        
        $repositorio = new AuditoriasRepositorio($this->conexion);
        $registros = array();
        $validadas = 0;
        $validadasEnviadas = 0;
        
        //var_dump($fechas);
        
        for ($i = 0; $i < count($fechas); $i++) 
        {
            $fecha = $fechas[$i];
            $mes =  intval($fecha->format("m"));
            $ano = intval($fecha->format("Y"));
            
            
            $resultado = $repositorio->consultarNumeroRecomendacionesPorMes($mes,$ano,$this->auditoria->id,$this->auditoria->puntuacion);
            if($resultado->correcto())
            {
                $registro = $resultado->valor;
//                 $registro->mes = $mes;
//                 $registro->ano = $ano;
                
//                 $registro->nombreMes = $this->getNombreMes($mes). " $ano";
                
//                 $validadas = $registro->validadas;
//                 $validadasEnviadas = $registro->validadasEnviadas;
                
//                 $registro->porcetajeFaltante = 100 - floatval($this->auditoria->puntuacion);
                
//                 if($registro->total!=0)
//                     $registro->valorHallagzo =  floatval($registro->porcetajeFaltante)/  floatval($registro->total);
//                 else 
//                     $registro->valorHallagzo = 0;
                
//                 $registro->avanceValidadas =  $registro->valorHallagzo * $validadas;
//                 $registro->avanceValidadasEnviadas =  $registro->valorHallagzo * $validadasEnviadas;
                
//                 $registro->porcentajeValidadas = $this->auditoria->puntuacion +  $registro->avanceValidadas;
//                 $registro->porcentajeValidadasEnviadas = $this->auditoria->puntuacion +  $registro->avanceValidadasEnviadas;
                
                array_push($registros,$registro);
            }
        }
        
        $pdfWidth = $this->GetPageWidth();
        $chartWidth= 220;
        $colores = [ '#00a1ff', '#60d836', '#f8ba00'];
        $image = $this->graficaCierre("",'',$registros,"nombreMes",$colores,true,100,$this->mes);
        if($image!='')
            $this->Image($image,$pdfWidth/2 -$chartWidth/2 ,40, $chartWidth);
        
    }
    
   
    
    function graficaCierre($title, $yTitle, $rows, $xField,$colors, $showInLegend,$max,$mes)
    {
        $categories = array();
        $data = array();
        
        $data = array();
        
        $data1 = array();
        
        $fecha = new DateTime();
        $mesActual = (int)$fecha->format("m");
        
        for ($i = 0; $i < count($rows); $i++)
        {
            $row = $rows[$i];
            
            $newRow= (object) [
                'name' =>  $row->$xField,
                'y' => (float)$row->porcentajeValidadas,
                'color' => "#00a1ff"
                
            ];
            
            $newRow1= (object) [
                'name' =>  $row->$xField,
                'y' => (float)$row->porcentajeValidadasEnviadas,
                'color' => "#00a65a"
            ];
            
//             $newRow2= (object) [
//                 'name' =>  $row->$xField,
//                 'y' => 70,
//                 'color' => "#f39c12"
//             ];
            
//             $newRow3= (object) [
//                 'name' =>  $row->$xField,
//                 'y' => 50,
//                 'color' => "#dd4b39"
//             ];
            
            array_push($categories, $row->$xField);
            if($row->mes<=$mesActual)
            {
                array_push($data, $newRow);
                array_push($data1, $newRow1);
            }
//                 array_push($data2, $newRow2);
//                 array_push($data3, $newRow3);
        }
        
        $yAxis = (object) [ 'title' => (object) [ 'text'=> $yTitle]];
        if($max>0)
        {
            $yAxis->min= 0;
            $yAxis->max= $max;
            $yAxis->tickInterval= 10;
        }
        
        $highchart = (object)
        [
            'chart' => (object) [ 'type' => "line"],
            'title' => (object) [ 'text'=> $title],
            'credits' => (object) ['enabled' => false],
            'xAxis' => (object) [ 'categories' => $categories, 'startOnTick' => true],
            'plotOptions' => (object)
            [
                'series'=> (object)[
                    'lineWidth'=>5,
                   // 'pointPlacement' =>  "on"
                ]
            ],
            'yAxis' => $yAxis,
            'series' => array(
                (object) ['name' => "Calificación incluyendo validadas", 'data' => $data,  'showInLegend' => $showInLegend],
                (object) ['name' => "Calificación validadas + enviadas", 'data' => $data1,  'showInLegend' => $showInLegend, "color"=>"#60d836"],
            )
        ];
        
        $data= (object) [
            'async' =>  true,
            'type' => 'image/jpeg',
            'width' => 1080,
            'options' => $highchart
        ];
        
        $options = array(
            'http' => array(
                'method'  => 'POST',
                'content' => json_encode( $data ),
                'header'=>  "Content-Type: application/json\r\n" .
                "Accept: application/json\r\n"
            )
        );
        
        $url = 'https://export.highcharts.com/';
        
        $context  = stream_context_create( $options );
        
        $result = file_get_contents( $url, false, $context );
        
        $charturl='';
        if ($result === FALSE)
        {
            
        }
        else
        {
            $charturl = $url . $result;
            
        }
        return $charturl;
        
        //  return 'ok';
        
    }
   
    
    private function avanceFecha()
    {
        $this->AddPage();
        $this->SetY(25);
        $this->subtitulo("Avance a la fecha");
        
       
        $pdfWidth = $this->GetPageWidth();
        $chartWidth = 220;
        $repositorio = new AuditoriasRepositorio($this->conexion);
        $resultado = $repositorio->consultarHallazgosDepartamento($this->auditoria->id);
        if($resultado->correcto())
        {
            $hallazgos = $resultado->valor;
            $image = $this->graficaBarrasAvance("",'','',$hallazgos,"nombre",true);
            if($image!='')
                $this->Image($image,$pdfWidth/2 -$chartWidth/2 ,40, $chartWidth);
                
        }
    }
    
    private function cierreHallazgosMes()
    {
        $this->AddPage();
        $this->SetY(25);
        $this->subtitulo("Cierre de hallazgos por mes");
        
        
        $pdfWidth = $this->GetPageWidth();
        $chartWidth = 220;
        $repositorio = new AuditoriasRepositorio($this->conexion);
        $resultado = $repositorio->consultarHallazgosUsuarios($this->auditoria->id);
        if($resultado->correcto())
        {
            $hallazgos = $resultado->valor;
            $image = $this->graficaBarrasAvance("",'','',$hallazgos,"nombreCompleto",true);
            if($image!='')
                $this->Image($image,$pdfWidth/2 -$chartWidth/2 ,40, $chartWidth);
                
        }
    }
    
    function graficaBarrasAvance($title, $yTitle, $serieTitle, $rows, $xField, $showInLegend)
    {
        $categories = array();
        $data = array();
        
        $data = array();
        
        $data1 = array();
        $data2 = array();
        $data3 = array();
        
        $fecha = new DateTime();
        $mesActual = (int)$fecha->format("m");
        
        for ($i = 0; $i < count($rows); $i++)
        {
            $row = $rows[$i];
            
            //         $newRow= (object) [
            //             'name' =>  $row->$xField,
            //             'y' => (float)$row->cumplidas,
            //             'color' => "#00a1ff"
            
            //         ];
            
            $newRow1= (object) [
                'name' =>  $row->$xField,
                'y' => (float)$row->total,
                'color' => "#0a9ffa"
            ];
            
            $newRow2= (object) [
                'name' =>  $row->$xField,
                'y' => (float)$row->validadas,
                'color' => "#78d34b"
            ];
            
            $newRow3= (object) [
                'name' =>  $row->$xField,
                'y' => (float)$row->proceso,
                'color' => "#919191"
            ];
            
            array_push($categories, $row->$xField);
            array_push($data1, $newRow1);
            array_push($data2, $newRow2);
            array_push($data3, $newRow3);
        }
        
        $yAxis = (object) [ 'title' => (object) [ 'text'=> $yTitle]];
       
        
        
        
        
        $highchart = (object)
        [
            'chart' => (object) [ 'type' => "column"],
            'title' => (object) [ 'text'=> $title],
            'credits' => (object) ['enabled' => false],
            'xAxis' => (object) [ 'categories' => $categories],
            'plotOptions' => (object)
            [
                'column'=> (object)[
                    'dataLabels'=>(object)
                    [
                        'enabled'=>true,
                        'crop'=>false,
                        'overflow' =>'none',
                        "inside"=> false,
                        'color'=> 'black',
                        'style'=> (object)
                        [
                            'fontSize' => 10,
                            'textOutline' => '0px'
                        ]
                    ]
                ]
            ],
            'yAxis' => $yAxis,
            'series' => array(
                (object) ['name' => "Encontrados", 'data' => $data1,  'showInLegend' => $showInLegend, "color"=>"#0a9ffa"],
                (object) ['name' => "Validadas", 'data' => $data2,  'showInLegend' => $showInLegend, "color"=>"#78d34b"],
                (object) ['name' => "En proceso de validación", 'data' => $data3,  'showInLegend' => $showInLegend, "color"=>"#919191"]
            )
        ];
        
        $data= (object) [
            'async' =>  true,
            'type' => 'image/jpeg',
            'width' => 1080,
            'options' => $highchart
        ];
        
        $options = array(
            'http' => array(
                'method'  => 'POST',
                'content' => json_encode( $data ),
                'header'=>  "Content-Type: application/json\r\n" .
                "Accept: application/json\r\n"
            )
        );
        
        $url = 'https://export.highcharts.com/';
        
        $context  = stream_context_create( $options );
        
        
        
        $result = file_get_contents( $url, false, $context );
        
        $charturl='';
        if ($result === FALSE)
        {
            
        }
        else
        {
            $charturl = $url . $result;
            
        }
        return $charturl;
        
        //  return 'ok';
        
    }
    
    private function tareas()
    {
        $this->AddPage();
        $this->SetY(25);
        $this->subtitulo("Tareas pendientes");
        $this->SetY(35);
        
        $this->fontSizes = array(10, 10, 10, 10, 10,10);
        $this->fontWeights = array("B","B","B","B","B","B");
        $this->aligns = array("C","C","C","C","C","C");
        $this->widths = array(15, 110, 28, 30, 34, 30);
        $this->textColors = array("#000000","#000000","#000000","#000000","#000000","#000000");
        $this->borders = array(1,1,1,1,1,1);
        $this->borderColors = array("#afb2b0","#afb2b0","#afb2b0","#afb2b0","#afb2b0","#afb2b0");
        $this->backgroundColors = array("#bdc1bf","#bdc1bf","#bdc1bf","#bdc1bf","#bdc1bf","#bdc1bf");
        $this->Row2(array("ID","Tarea asignada","Avance a la fecha","Estatus","Responsable","Departamento"),5);
        
        $this->fontWeights = array("B","","","","","");
        $this->aligns = array("C","L","C","L","L","L");
       
        $repositorio = new AuditoriasRepositorio($this->conexion);
        $resultado = $repositorio->consultarRecomendacionesPendientes($this->auditoria->id);
        if($resultado->correcto())
        {
            $recomendaciones = $resultado->valor;
            for($i = 0; $i < count($recomendaciones); $i++)
            {
                $color = "";
                if($i%2==0)
                    $color = "#ffffff";
                else
                    $color = "#f5f5f5";
                    $this->backgroundColors = array("#e6e6e6",$color,$color,$color,$color,$color);
                $tarea = $recomendaciones[$i];
                
                $this->Row2(array($tarea->id,$this->texto($tarea->titulo),$tarea->cumplimiento."%",$this->texto($tarea->estatusValidacionNombre),$this->texto($tarea->responsableNombreCompleto), $this->texto($tarea->departamentoNombre)),8);
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
        $filename ="../reportes_seguimiento/";
        $filename.=$this->calcularFolio();
        $filename.=".pdf";
        
        //var_dump($this->auditoria);
        
        $this->Output($filename,'F');
        
        if (file_exists($filename))
        {
            header('Location:'. $filename);
        }
    }
    
    function avance()
    {
        $borde = 0;
        $avance = $this->auditoria->porcentaje;
        $this->SetTextColor(0,0,0);
        $this->SetFont($this->font,'I',9);
        $this->Ln();
        $this->SetX(0);
        $this->SetMargins(5,5,5);
        $this->Cell(0, 8, "Avance: $avance%", $borde, 0, 'R');
    }
    
    function titulo()
    {
        $this->Ln();
        //$this->SetY(16);
        //$this->SetLeftMargin(5);
        $this->SetFont($this->font,'B',18);
        $this->SetTextColor(60, 141, 188);
        $this->Cell(0,0,$this->texto("auditoria DE REUNIÓN"),0,2,'C');
    }
    
    
//     function subtitulo($texto)
//     {
//         /*$this->Ln();
//         $this->SetFont($this->font,'B',14);
//         $this->SetTextColor(0, 0, 0);
//         $this->Cell(0,0,$this->texto($texto),0,2);
//         $y = $this->GetY();
//         $this->linea($y + 3, 0, 0, 0, 0.5);*/
//         $borde = 0;
//         $this->SetLeftMargin(5);
//         $this->SetFont($this->font,'B',16);
//         $this->SetTextColor(0, 0, 0);
//         $this->Cell(30, 6, $this->texto($texto), $borde, 0, 'L');
        
//         $this->linea($this->GetY() + 6, 0, 0, 0, 0.3);
       
//     }
    
    function campo($ancho,$texto,$valor)
    {
        /*$this->Ln();
         $this->SetFont($this->font,'B',14);
         $this->SetTextColor(0, 0, 0);
         $this->Cell(0,0,$this->texto($texto),0,2);
         $y = $this->GetY();
         $this->linea($y + 3, 0, 0, 0, 0.5);*/
        $borde = 0  ;
        $this->SetLeftMargin(5);
        $this->SetFont($this->font,'',11);
        $this->SetTextColor(0, 0, 0);
        $this->Cell($ancho, 6, $this->texto($texto), $borde, 0, 'L');
        $this->Cell($this->w - $ancho - ($this->margen *  2), 6, $this->texto($valor), $borde, 0, 'L');
        
        
    }
    
    function formatoFecha($fecha)
    {
        $f = substr($fecha,0,10);
        $hora = substr($fecha,11,5);
        list($ano, $mes, $dia) = explode("-", $f);
        $fecha = "$dia/$mes/$ano $hora";
        return $fecha;
    }
    
    
    function imprimirInspector()
    {
        $inspectorNombre = strtoupper($this->auditoria->inspectorNombre);
        //$fechaInicio = $this->formatoFecha($this->auditoria->fechaauditoria);
        //$fechaFinalizacion= $this->formatoFecha($this->auditoria->fechaFinalizacion);
        
        $fechaInicio = $this->auditoria->fechaauditoria;
        $fechaFinalizacion= $this->auditoria->fechaFinalizacion;
        
        $borde = 0;
        $this->SetLeftMargin(10);
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(30, 6, "Montacarguista /", $borde, 0, 'L');
        $this->Cell(100, 6, "", $borde, 0, 'L');
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(15, 6, "Inicio:", $borde, 0, 'R');
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 6, $fechaInicio, $borde, 0, 'R');
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(30, 6, $this->texto("Inspector"), $borde, 0, 'L');
        $this->SetFont($this->font,'',10);
        $this->Cell(100, 6, $this->texto($inspectorNombre), $borde, 0, 'C');
        $this->SetFont($this->font,'B',10);
        $this->Cell(15, 6, "Fin:", $borde, 0, 'R');
        $this->SetFont($this->font  ,'', 10);
        $this->Cell(45, 6, $fechaFinalizacion, $borde, 0, 'R');
        
    }
    

    
    function imprimirInformacionEmbarque()
    {
        
        $borde = 0;
        $this->SetLeftMargin(10);
        $this->SetFont($this->font, 'B', 13);
        $this->Ln();
        $this->Ln();
        $this->SetFillColor(242, 242, 242);
        $this->Cell(0,8,$this->texto("INFORMACIÓN DE EMBARQUE"),$borde,2,'C',1);
        
        $ancho1 = 5;
        $ancho2 = 60;
        $ancho3 = 70;
        
        $this->SetFont($this->font, '', 10);
        $this->Cell($ancho1, 8, "1.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, "Apertura de embarque en turno:", $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->auditoria->turnoInicio), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "2.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, "Destino:", $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->auditoria->destino), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "3.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Número de orden:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->auditoria->numeroOrden), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "4.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Piezas:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->auditoria->piezas), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "5.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Bultos:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->auditoria->bultos), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "6.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Peso:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->auditoria->peso), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "7.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Otras mercancias:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->auditoria->otrasMercancias), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "8.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Manifiesto:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->auditoria->manifiesto), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "9.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Sello colocado:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->auditoria->selloColocado), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "10.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Inspector de cierre de embarque:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->auditoria->inspectorTerminaNombre), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "11.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Cierre de embarque en turno:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->auditoria->turnoFin), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "12.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Factura:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->auditoria->factura), $borde, 0, 'L' );
        
       
    }
    
//     function imprimirauditoriaTractor()
//     {
//         $borde = 0;
//         $anchoColumna1 = 48;
//         $anchoColumna2 = 30;
//         $anchoColumna3 = 112;
        
//         $this->SetLeftMargin(10);
//         $this->SetFont($this->font, 'B', 13);
//         $this->Ln();
//         $this->SetFillColor(242, 242, 242);
//         $this->Cell(0,8,$this->texto("INSPECCIÓN DE TRACTOR"),$borde,2,'C',1);
        
        
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell($anchoColumna1, 8, $this->texto("Descripción"), $borde, 0, 'C', 1);
//         $this->Cell($anchoColumna2, 8, "Resultado", $borde, 0, 'C', 1);
//         $this->Cell($anchoColumna3, 8, "Observaciones", $borde, 0, 'C', 1);
//         $altoColumna = 6.5;
//         for($i = 0 ; $i < count($this->auditoria->puntos1); $i++)
//         {
//             $punto = $this->auditoria->puntos1[$i];
//             $descripcion = $punto->id .". " . $punto->descripcion;
//             $observaciones = $punto->observaciones;
            
//             $this->Ln();
//             $this->SetFont($this->font, '', 9);
//             $this->Cell($anchoColumna1, 8, $this->texto($descripcion), $borde, 0, 'L');
            
//             $resultado = "";
//             if($punto->resultado == "S")
//             {
//                 $this->SetFont("ZapfDingbats", '', 9);
//                 $resultado =  chr(52);
//             }
//             else if($punto->resultado == "N")
//             {
//                 $this->SetFont("ZapfDingbats", '', 9);
//                 $resultado =  chr(54);
//             }
//             else
//             {
//                 $this->SetFont($this->font, '', 9);
//                 $resultado =  "N/A";
//             }
            
//             if(substr( $observaciones, 0, 1 ) === ",")
//             {
//                 $observaciones = substr($observaciones, 1);
//             }
            
//             $this->Cell($anchoColumna2, 8, $resultado, $borde, 0, 'C');
//             $this->SetFont($this->font, '', 9);
//             $this->Cell($anchoColumna3, $altoColumna, $this->texto($observaciones), $borde, 0, 'L');
//         }
        
//         $this->SetDrawColor(0,0,0);
//         $y = 173;
//        // $this->Line(10, $y, 210-10, $y);
        
//     }
    
//     function imprimirauditoriaContenedor()
//     {
        
        
//         $borde = 0;
//         $anchoColumna1 = 48;
//         $anchoColumna2 = 30;
//         $anchoColumna3 = 112;
        
//         $this->SetLeftMargin(10);
//         $this->SetFont($this->font, 'B', 13);
//         $this->Ln();
//         $this->Ln();
//         $this->SetFillColor(242, 242, 242);
//         $this->Cell(0,8,$this->texto("INSPECCIÓN DE CONTENEDOR"),$borde,2,'C',1);
        
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell($anchoColumna1, 8, $this->texto("Descripción"), $borde, 0, 'C', 1);
//         $this->Cell($anchoColumna2, 8, "Resultado", $borde, 0, 'C', 1);
//         $this->Cell($anchoColumna3, 8, "Observaciones", $borde, 0, 'C', 1);
        
//         for($i = 0 ; $i < count($this->auditoria->puntos2); $i++)
//         {
//             $punto = $this->auditoria->puntos2[$i];
//             $descripcion = $punto->id .". " . $punto->descripcion;
//             $observaciones = $punto->observaciones;
            
//             $this->Ln();
//             $this->SetFont($this->font, '', 9);
//             $this->Cell($anchoColumna1, 8, $this->texto($descripcion), $borde, 0, 'L');
            
//             $resultado = "";
//             if($punto->resultado == "S")
//             {
//                 $this->SetFont("ZapfDingbats", '', 9);
//                 $resultado =  chr(52);
//             }
//             else if($punto->resultado == "N")
//             {
//                 $this->SetFont("ZapfDingbats", '', 9);
//                 $resultado =  chr(54);
//             }
//             else
//             {
//                 $this->SetFont($this->font, '', 9);
//                 $resultado =  "N/A";
//             }
            
//             if(substr( $observaciones, 0, 1 ) === ",")
//             {
//                 $observaciones = substr($observaciones, 1); 
//             }
            
//             $this->Cell($anchoColumna2, 8, $resultado, $borde, 0, 'C');
//             $this->SetFont($this->font, '', 9);
//             $this->MultiCell($anchoColumna3, 8, $this->texto($observaciones), $borde,  'L');
//         }
        
//         $this->SetDrawColor(0,0,0);
//         $y = 34;
//         //$this->Line(10, $y, 210-10, $y);
        
        
//     }
    
    function imprimirFotos()
    {
      
        
        $seccionesFotos = array();
        for($i = 0 ; $i < count($this->auditoria->puntos); $i++)
        {
            $punto = $this->auditoria->puntos[$i];;
            $seccion = $this->crearSeccionFotos($this->auditoria->id,$punto);
            if(count($seccion->fotos)>0)
                array_push($seccionesFotos,$seccion);
        }
        
//         for($i = 0 ; $i < count($this->auditoria->puntos2); $i++)
//         {
//             $punto = $this->auditoria->puntos2[$i];;
//             $seccion = $this->crearSeccionFotos($this->auditoria->id,$punto);
//             if(count($seccion->fotos)>0)
//                 array_push($seccionesFotos,$seccion);
//         }
        
        if(count($seccionesFotos)>0)
            $this->AddPage();
        
        $borde = 0;
        
        
        
        $anchoFoto = 50;
        //$altoFoto = $anchoFoto * 40 / 30;
        $altoFoto = $anchoFoto * 0.75;
        $separacionX =  5;
        $separacionY = 20;
        
        $yFotos = $separacionY + 15;
        
        for($i = 0 ; $i < count($seccionesFotos); $i++)
        {
            $seccion = $seccionesFotos[$i];
            $titulo = $seccion->id .". ".$seccion->descripcion;
            
            $this->SetXY(0, $yFotos - $separacionY);
            $this->SetLeftMargin(10);
            $this->SetFont($this->font, 'B', 13);
            $this->Ln();
            $this->SetFillColor(242, 242, 242);
            $this->Cell(0,8,$this->texto($titulo),$borde,2,'C',0);
            
            
            $numeroFotos = count($seccion->fotos);
            $anchoTotal = (($numeroFotos-1) * $separacionX) + $numeroFotos * $anchoFoto;
            $xFoto = 10 + (95 - $anchoTotal/2);
            for($j = 0 ; $j < $numeroFotos; $j++)
            {
                $foto = $seccion->fotos[$j];
                
                $this->correctImageOrientation($foto);
                
                $this->Image($foto,$xFoto,$yFotos,$anchoFoto,$altoFoto);
                $xFoto = $xFoto + $anchoFoto +  $separacionX;
            }
            $yFotos = $yFotos + $separacionY + $altoFoto;
            if(($i+1) % 3 ==0 && $i<count($seccionesFotos)-1)
            {
                $yFotos = $separacionY + 15;
                $this->AddPage();
            }
        }
        
        
        
    }
    
    function agregarFoto($auditoriaId, $nombreArchivo ,$titulo,&$fotos)
    {
        $archivo = "../fotos_auditoriaes/".$auditoriaId ."_" . $nombreArchivo.".jpg";
        if (file_exists($archivo))
        {
            $foto= (object) [
                'titulo' => $titulo,
                'archivo' => $archivo
            ];
            array_push($fotos,$foto);
        }
    }
    public $tablewidths;
    public $aligns;
    public $columnFonts;
    public $footerset;
    
//     function morepagestable($datas, $lineheight=8) {
//         // some things to set and 'remember'
//         $l = $this->lMargin;
//         $startheight = $h = $this->GetY();
//         $startpage = $currpage = $maxpage = $this->page;
        
//         // calculate the whole width
//         $fullwidth = 0;
//         foreach($this->tablewidths AS $width) {
//             $fullwidth += $width;
//         }
        
//         // Now let's start to write the table
//         foreach($datas AS $row => $data) {
//             $this->page = $currpage;
//             // write the horizontal borders
//             $this->Line($l,$h,$fullwidth+$l,$h);
//             // write the content and remember the height of the highest col
//             foreach($data AS $col => $txt) 
//             {
//                 $this->page = $currpage;
                
                
//                 if($h<10)
//                     $h = 30;
                
                
//                 $this->SetXY($l,$h);
//                 $this->SetFont($this->columnFonts[$col], '', 9);
//                 $this->MultiCell($this->tablewidths[$col],$lineheight,$this->texto($txt),1,$this->aligns[$col]);
//                 $l += $this->tablewidths[$col];
                
//                 if(!isset($tmpheight[$row.'-'.$this->page]))
//                     $tmpheight[$row.'-'.$this->page] = 0;
//                 if($tmpheight[$row.'-'.$this->page] < $this->GetY()) 
//                 {
//                     $tmpheight[$row.'-'.$this->page] = $this->GetY();
//                 }
//                 if($this->page > $maxpage)
//                     $maxpage = $this->page;
//             }
            
//             // get the height we were in the last used page
//             $h = $tmpheight[$row.'-'.$maxpage];
//             // set the "pointer" to the left margin
//             $l = $this->lMargin;
//             // set the $currpage to the last page
//             $currpage = $maxpage;
//         }
//         // draw the borders
//         // we start adding a horizontal line on the last page
//         $this->page = $maxpage;
//         $this->Line($l,$h,$fullwidth+$l,$h);
//         // now we start at the top of the document and walk down
//         for($i = $startpage; $i <= $maxpage; $i++) {
//             $this->page = $i;
//             $l = $this->lMargin;
//             $t  = ($i == $startpage) ? $startheight : $this->tMargin;
//             $lh = ($i == $maxpage)   ? $h : $this->h-$this->bMargin;
//             $this->Line($l,$t,$l,$lh);
//             foreach($this->tablewidths AS $width) {
//                 $l += $width;
//                 $this->Line($l,$t,$l,$lh);
//             }
//         }
//         // set it to the last page, if not it'll cause some problems
//         $this->page = $maxpage;
//     }
    
//     function _beginpage($orientation, $size, $rotation) {
//         $this->page++;
//         if(!isset($this->pages[$this->page])) // solves the problem of overwriting a page if it already exists
//             $this->pages[$this->page] = '';
//             $this->state = 2;
//             $this->x = $this->lMargin;
//             $this->y = $this->tMargin;
//             $this->FontFamily = '';
//             // Check page size and orientation
//             if($orientation=='')
//                 $orientation = $this->DefOrientation;
//                 else
//                     $orientation = strtoupper($orientation[0]);
//                     if($size=='')
//                         $size = $this->DefPageSize;
//                         else
//                             $size = $this->_getpagesize($size);
//                             if($orientation!=$this->CurOrientation || $size[0]!=$this->CurPageSize[0] || $size[1]!=$this->CurPageSize[1])
//                             {
//                                 // New size or orientation
//                                 if($orientation=='P')
//                                 {
//                                     $this->w = $size[0];
//                                     $this->h = $size[1];
//                                 }
//                                 else
//                                 {
//                                     $this->w = $size[1];
//                                     $this->h = $size[0];
//                                 }
//                                 $this->wPt = $this->w*$this->k;
//                                 $this->hPt = $this->h*$this->k;
//                                 $this->PageBreakTrigger = $this->h-$this->bMargin;
//                                 $this->CurOrientation = $orientation;
//                                 $this->CurPageSize = $size;
//                             }
//                             if($orientation!=$this->DefOrientation || $size[0]!=$this->DefPageSize[0] || $size[1]!=$this->DefPageSize[1])
//                                 $this->PageInfo[$this->page]['size'] = array($this->wPt, $this->hPt);
//                                 if($rotation!=0)
//                                 {
//                                     if($rotation%90!=0)
//                                         $this->Error('Incorrect rotation value: '.$rotation);
//                                         $this->CurRotation = $rotation;
//                                         $this->PageInfo[$this->page]['rotation'] = $rotation;
//                                 }
//     }
    
    
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
    
    function crearSeccionFotos($auditoriaId, $punto)
    {
        $fotos = array();
        $foto = "../fotos_auditoriaes/".$auditoriaId ."_" . $punto->id ."_1.jpg";
        if (file_exists($foto))
            array_push($fotos,$foto);
            $foto = "../fotos_auditoriaes/".$auditoriaId ."_" . $punto->id ."_2.jpg";
            if (file_exists($foto))
                array_push($fotos,$foto);
                $foto = "../fotos_auditoriaes/".$auditoriaId ."_" . $punto->id ."_3.jpg";
                if (file_exists($foto))
                    array_push($fotos,$foto);
                    $seccion= (object) [
                        'id' =>  $punto->id,
                        'descripcion' => $punto->descripcion,
                        'fotos' => $fotos
                    ];
                    return $seccion;
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
    
    protected function getPuntos($puntosId)
    {
        $puntos = array();
        for($i=0; $i < count($puntosId); $i++)
        {
            $id = $puntosId[$i];
            $punto = $this->getPunto($id, $this->auditoria->puntos);
            if($punto!=null)
                array_push($puntos, $punto);
        }
        return $puntos;
    }
    
    protected function getPunto($id,$puntos)
    {
        for($i=0; $i < count($puntos); $i++)
        {
            $punto = $puntos[$i];
            if($punto->id == $id )
                return $punto;
        }
        return null;
    }
}



class ReporteSeguimiento extends PDF
{
   
   
    
   
    
}


class ReporteFabrica
{
    public function crear()
    {
        $reporte = new ReporteSeguimiento();
        return $reporte;
    }
}


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
        
        $resultado = $repositorio->consultarPorLlaves($llaves,true);
        
        if($resultado->correcto())
        {
            $auditoria = $resultado->valor;
            $reporteFabrica = new ReporteFabrica();
            $reporte = $reporteFabrica->crear();
            if($reporte!=null)
            {
                $mes =  intval(date("m"));
                $ano = intval(date("Y"));
                
                
                $resultado = $repositorio->consultarNumeroRecomendacionesPorMes($mes,$ano,$auditoriaId,$auditoria->puntuacion);
                if($resultado->correcto())
                {
                    $auditoria->porcentajeValidadas = $resultado->valor->porcentajeValidadas;
                    $auditoria->porcentajeValidadasEnviadas = $resultado->valor->porcentajeValidadasEnviadas;
                    $reporte->setConexion($conexion);
                    $reporte->setAuditoria($auditoria);
                    $reporte->AliasNbPages();
                    $reporte->generar();
                    $reporte->imprimir();
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


