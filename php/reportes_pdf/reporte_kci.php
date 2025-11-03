<?php
use php\clases\AdministradorConexion;
use php\repositorios\AuditoriasRepositorio;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use php\repositorios\EmpresasRepositorio;
use php\repositorios\SedesRepositorio;
use php\repositorios\CursosRepositorio;
use php\clases\Porcentaje;
use php\clases\GeneradorColores;
use php\repositorios\EvidenciasRepositorio;
use php\reportes\ReporteBase;
use php\repositorios\UsuariosRepositorio;
use php\clases\ArrayUtils;

require('../vendor/fpdf181/fpdf.php');
include '../clases/Utilidades.php';
require_once('../clases/AdministradorConexion.php');
require_once('../repositorios/EmpresasRepositorio.php');
require_once('../repositorios/AuditoriasRepositorio.php');
require_once('../clases/Porcentaje.php');
require_once('../clases/GeneradorColores.php');
require_once('../repositorios/SedesRepositorio.php');
require_once('../repositorios/CursosRepositorio.php');
require_once('../highcharts/highchartutils.php');
require_once('../reportes/reporte_base.php');
require_once('../clases/ArrayUtils.php');


class ReporteKCI extends ReporteBase
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
        $this->FontSize = 12;
        
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
        /*$this->SetLeftMargin($this->margen);
         $this->SetRightMargin($this->margen);
         
         $this->SetTextColor(0,0,0);
         $this->SetY(-20);
         $borde = 0;
         $this->SetFont($this->font, 'I', 9);
         $anchoColumna = ($this->w - ($this->margen * 2)) / 3;
         
         $this->Cell(0, 4, $this->texto("Prohibida la reproducción total o parcial"), $borde, 1, 'C');
         $this->SetFont($this->font, 'U', 9);
         $this->Cell($anchoColumna, 4, "saha.apps-handel.com", $borde, 0, 'L', false,"https://saha.apps-handel.com");
         $this->SetFont($this->font, 'I', 9);
         $this->Cell($anchoColumna, 4, $this->texto("sin autorización expresa de Handel Consultoría"), $borde, 0, 'C');
         $this->Cell($anchoColumna, 4, "Hoja ". $this->PageNo().' de {nb}', $borde, 0, 'R');*/
    }
    
    
    
    function Header()
    {
        /*$this->SetLeftMargin($this->margen);
         $this->SetRightMargin($this->margen);
         
         $borde = 0;
         $this->SetTextColor(0,0,0);
         $this->SetFont($this->font,'I',9);
         
         $fecha = new DateTime();
         $fecha = $fecha->format("d/m/Y");
         
         $empresaNombre = $this->empresa->nombre;
         $anchoColumna = ($this->w - ($this->margen * 2)) / 2;
         $this->Cell($anchoColumna, 8, $this->texto("Reporte de capacitación CAVI para $empresaNombre"), $borde, 0, 'L');
         $this->Cell($anchoColumna, 4, "Reporte de Coordinador", $borde, 0, 'R');
         
         $this->Ln();
         $fechaInicial = "";//$this->criteriosSeleccion->fechaInicial;
         $fechaFinal = "";//$this->criteriosSeleccion->fechaFinal;
         $this->Cell($anchoColumna, 8, "Periodo $fechaInicial a $fechaFinal", $borde, 0, 'L');
         $this->Cell($anchoColumna, 4, "emitido el $fecha", $borde, 0, 'R');
         
         $logoAlto = 6;
         $logoX = $this->w / 2 - $logoAlto / 2;
         $logoY = 11;
         $logo = "../imagenes/favicon_cavi.png";
         if (file_exists($logo))
         $this->Image($logo,$logoX,$logoY,$logoAlto,0,'','');*/
    }
    
    private function linea($y, $r, $g, $b, $w)
    {
        $this->SetLineWidth($w);
        $this->SetDrawColor($r, $g, $b);
        $width = $this->w;
        $this->Line(100, $y, $width-$this->margen, $y);
    }
    
    protected function calcularFolio()
    {
        $folio="KCI";
        if($this->empresa!=null)
            $folio.="-".$this->empresa->nombreCorto;
        $folio.="-" . $this->mes ."-". $this->ano;
           
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
                
                $textColorHex = $this->textColors[$i];
                $rgb = $this->toRGB($textColorHex);
                $this->SetTextColor($rgb->r, $rgb->g, $rgb->b);
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
    
    function RowTransparent($data, $height)
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
                //$this->Rect($x,$y,$w,$h,"F");
                
                if($this->borders[$i]==1)
                {
                    $colorHex = $this->borderColors[$i];
                    $rgb = $this->toRGB($colorHex);
                    $this->SetDrawColor($rgb->r, $rgb->g, $rgb->b);
                    $this->Rect($x,$y,$w,$h,"D");
                }
                
                $textColorHex = $this->textColors[$i];
                $rgb = $this->toRGB($textColorHex);
                $this->SetTextColor($rgb->r, $rgb->g, $rgb->b);
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
    
    function fondoPortada()
    {
        $imagen = "../imagenes/portada.001.png";
        $width = $this->w;
        $anchoFoto = 120;
        $x = ($width/2) - ($anchoFoto/2);
        $y = 30;
        $this->Image($imagen,0,0,$this->w, $this->h);
        
    }
    
    protected function fondoPlantilla()
    {
        $imagen = "../imagenes/plantilla.001.png";
        $width = $this->w;
        $anchoFoto = 120;
        $x = ($width/2) - ($anchoFoto/2);
        $y = 30;
        $this->Image($imagen,0,0,$this->w, $this->h);
    }
    
    function portada()
    {
        $this->AddPage();
        $this->fondoPortada();
        
        $this->SetY(80);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->font,'B',50);
        $this->Cell(0,6,$this->texto("Indicadores de cumplimiento"),0,2,'C');
        $this->Ln();
        $this->Ln();
        $this->SetFont($this->font,'I',40);
        $this->Cell(0,6,$this->texto("(Key compliance indicators)"),0,2,'C');
        
        /* $
         $this->SetTextColor(38,88,175);
         $this->SetFont($this->font,'B',32);
         $this->Cell(0,6,$this->texto($this->empresa->nombre),0,2,'C');*/
        
        $margenX = 20;
        $this->SetX($margenX);
        $this->SetY(150);
        $this->fontSizes = array(32);
        $this->fontWeights = array("B");
        $this->fontNames = array($this->font);
        $this->aligns = array("C");
        $this->widths = array($this->w - ($margenX*2));
        $this->textColors = array("#2658af");
        $this->borders = array(0);
        $this->borderColors = array("#afb2b0");
        $this->backgroundColors = array("#ffffff");
        $this->RowTransparent(array($this->texto($this->empresa->nombre)),10);
        
        
        $imagen = "../imagenes/logo_handel.png";
        $width = $this->w;
        $anchoFoto = 60;
        $x = 20;
        $y = 20;
        $this->Image($imagen,$x,$y,$anchoFoto);
        
        
        $nombreMesActual = Mes::getNombreMesActual();
        $anoActual = date("Y");
        
        $this->SetY(182);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->font,'B',15);
        $this->Cell(0,6,$this->texto($nombreMesActual . ", " . $anoActual),0,2,'C');
        
        
    }
    
    protected function subtitulo($texto)
    {
        /* $this->SetY(10);
         $this->SetLeftMargin(100);
         $this->SetTextColor(0, 0, 0);
         $this->SetFont($this->font,'B',20);
         $this->Cell(0,6,$this->texto($subtitulo),0,2,'C');*/
        $this->textoCentrado($texto, 10, 0, 0, 0, 'B', 20);
    }
    
    function textoCentrado($texto, $y, $r, $g, $b, $fontStyle, $fontSize)
    {
        $this->SetY($y);
        $this->SetLeftMargin(100);
        $this->SetTextColor($r, $g, $b);
        $this->SetFont($this->font,$fontStyle,$fontSize);
        $this->Cell(0,6,$this->texto($texto),0,2,'C');
    }
    
    function temasReunion()
    {
        $this->AddPage();
        $this->fondoPlantilla();
        $this->tituloPagina("Orden del día");
        
        $this->subtitulo("Temas de reunión");
        
        
        $this->SetY(30);
        
        $this->textoVineta("Resultados SAHA");
        $this->Ln();
        $this->textoVineta("Avances CAVI");
        $this->Ln();
        $this->textoVineta("Avances SIVAH");
        
        $this->Image("../imagenes/caricatura/caricatura01.png", 225, 130, 70);
    }
    
    protected function textoVineta($texto)
    {
        $this->SetX(120);
        $this->SetFont("ZapfDingbats", 'B', 20);
        $this->Cell(10,10,$this->texto(chr(108)),0,0,"L");
        $this->SetFont($this->font, '', 18);
        $this->Cell(50,10,$this->texto($texto),0,0,'L');
    }
    
    function cumplimientoGlobalSAHA()
    {
        $this->AddPage();
        $this->fondoPlantilla();
        $this->tituloPagina("Resultados SAHA");
        $this->logoApp("../imagenes/logo_saha02.png", 30);
        
        
        
        $pdfWidth = 190;
        $this->SetLeftMargin(20);
        $this->SetRightMargin(20);
        
        $criteriosSeleccion= (object) [
            'mes' =>  $this->mesSAHA,
            'ano' =>  $this->anoSAHA,
            "empresaId" => $this->empresa->id
        ];
        
        //var_dump($criteriosSeleccion);
        //var_dump($this->usuario);
        
        $fecha = new DateTime();
        $fecha->setDate($this->anoSAHA,$this->mesSAHA,1);
        $fecha->sub(new DateInterval('P1M'));
        
        $anoAnterior = $fecha->format("Y");
        $mesAnterior = $fecha->format("m");
        $criteriosSeleccionAnterior= (object) [
            'mes' =>   $mesAnterior,
            'ano' => $anoAnterior,
            "empresaId" => $this->empresa->id
        ];
        
        $nombreMes = ucfirst(Mes::getNombre($this->mesSAHA));
        $nombreMesAnterior = ucfirst(Mes::getNombre($mesAnterior));
        
        $this->subtitulo("Porcentaje global $nombreMesAnterior $anoAnterior - $nombreMes $this->anoSAHA");
        
        $repositorio = new EvidenciasRepositorio($this->conexion);
        
        $chartWidth = 100;
        $resultado = $repositorio->consultarPorcentajesEvidencias($this->usuario, $criteriosSeleccion);
        if($resultado->correcto())
        {
            $porcentajes = $resultado->valor;
            //var_dump($porcentajes);
            $colores = [ "#00a65a", "#dd4b39", "#f39c12"];
            $image = toPieChartWithLabels("Porcentaje de cumplimiento global del área <br>($nombreMes)",'Porcentaje','Areas',$porcentajes,"nombre","valor",$colores,20);
            if($image!='')
                $this->Image($image,95 ,60, $chartWidth);
        }
        
        $resultado = $repositorio->consultarPorcentajesEvidencias($this->usuario, $criteriosSeleccionAnterior);
        if($resultado->correcto())
        {
            $porcentajes = $resultado->valor;
            $colores = [ "#00a65a", "#dd4b39", "#f39c12"];
            $image = toPieChartWithLabels("Porcentaje de cumplimiento global del área <br>($nombreMesAnterior)",'Porcentaje','Areas',$porcentajes,"nombre","valor",$colores,20);
            if($image!='')
                $this->Image($image, 190 ,60,$chartWidth);
        }
        
        $this->SetY(170);
        $this->SetLeftMargin(200);
        $this->SetFont($this->font, '', 10);
        $this->Cell(50,6,$this->texto("Supervisores y coordinadores reciben un reporte detallado el día 28 de cada mes"),0,2,'C');
        $this->SetTextColor(173,8,46);
        $this->Cell(50,6,$this->texto("Se recomienda no tener más de un 15% de evidencias justificadas"),0,2,'C');
        
        //var_dump($criteriosSeleccion);
        //var_dump($criteriosSeleccionAnterior);
    }
    
    function cumplimientoUsuarioSAHA()
    {
        $this->AddPage();
        $this->fondoPlantilla();
        $this->tituloPagina("Resultados SAHA");
        $this->subtitulo("Cumplimiento por usuario");
        $this->logoApp("../imagenes/logo_saha02.png", 30);
        
        $criteriosSeleccion= (object) [
            'mes' =>  $this->mesSAHA,
            'ano' =>  $this->anoSAHA,
            "empresaId" => $this->empresa->id
        ];
        $nombreMes = ucfirst(Mes::getNombre($this->mesSAHA));
        $pdfWidth = $this->GetPageWidth();
        $chartWidth= 170;
        $repositorio = new EvidenciasRepositorio($this->conexion);
        $resultado = $repositorio->consultarPorcentajesUsuarios($this->usuario, $criteriosSeleccion);
        if($resultado->correcto())
        {
            $porcentajes = $resultado->valor;
            $colores = [ '#00a1ff', '#60d836', '#f8ba00'];
            //$image = toColumnChart("Porcentaje de cumplimiento <br>($nombreMes)",'','Usuarios',$porcentajes,"nombreCompleto","porcentajeCumplimiento",$colores,false,100);
            $image = $this->graficaBarrasUsuarios("Porcentaje de cumplimiento por usuario <br>($nombreMes)",'','Usuarios',$porcentajes,"nombreCompleto");
            if($image!='')
                $this->Image($image,100 ,50, $chartWidth);
        }
        
        $this->Image("../imagenes/caricatura/caricatura02.png", 50, 120, 50);
    }
    
    function cumplimientoDepartamentoSAHA()
    {
        $this->AddPage();
        $this->fondoPlantilla();
        $this->tituloPagina("Resultados SAHA");
        $this->subtitulo("Cumplimiento por departamento");
        $this->logoApp("../imagenes/logo_saha02.png", 30);
        
        $criteriosSeleccion= (object) [
            'mes' =>  $this->mesSAHA,
            'ano' =>  $this->anoSAHA,
            "empresaId" => $this->empresa->id
        ];
        $nombreMes = ucfirst(Mes::getNombre($this->mesSAHA));
        $chartWidth= 170;
        $repositorio = new EvidenciasRepositorio($this->conexion);
        
        $chartWidth= 170;
        $resultado = $repositorio->consultarPorcentajesAreas($this->usuario, $criteriosSeleccion);
        if($resultado->correcto())
        {
            $porcentajes = $resultado->valor;
            $image = $this->graficaBarrasDepartamentos("Porcentaje de cumplimiento por departamento <br>($nombreMes)",'','Areas',$porcentajes,"nombre");
            if($image!='')
                $this->Image($image, 100 ,50, $chartWidth);
        }
        
        $this->Image("../imagenes/caricatura/caricatura02.png", 50, 120, 50);
    }
    
    function cumplimientoSedeSAHA()
    {
        $this->AddPage();
        $this->fondoPlantilla();
        $this->tituloPagina("Resultados SAHA");
        $this->subtitulo("Cumplimiento por sede");
        $this->logoApp("../imagenes/logo_saha02.png", 30);
        
        $criteriosSeleccion= (object) [
            'mes' =>  $this->mesSAHA,
            'ano' =>  $this->anoSAHA,
            "empresaId" => $this->empresa->id
        ];
        $nombreMes = ucfirst(Mes::getNombre($this->mesSAHA));
        $chartWidth= 170;
        $repositorio = new EvidenciasRepositorio($this->conexion);
        
        $chartWidth= 170;
        $resultado = $repositorio->consultarPorcentajesSedes($this->usuario, $criteriosSeleccion);
        if($resultado->correcto())
        {
            $porcentajes = $resultado->valor;
            $image = $this->graficaBarrasDepartamentos("Porcentaje de cumplimiento por sede <br>($nombreMes)",'','Sedes',$porcentajes,"nombre");
            if($image!='')
                $this->Image($image, 100 ,50, $chartWidth);
        }
        
        $this->Image("../imagenes/caricatura/caricatura02.png", 50, 120, 50);
    }
    
    function graficaBarrasDepartamentos($title, $yTitle, $serieTitle, $rows, $xField)
    {
        $showInLegend = true;
        
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
                'y' => (float)$row->porcentajeEnviadas,
                // 'color' => "#3c8dbc"
            ];
            
            $newRow2= (object) [
                'name' =>  $row->$xField,
                'y' => (float)$row->porcentajeJustificadas,
                //'color' => "#f39c12"
            ];
            
            //             $newRow3= (object) [
            //                 'name' =>  $row->$xField,
            //                 'y' => (float)$row->proceso,
            //                 'color' => "#919191"
            //             ];
            
            array_push($categories, $row->$xField);
            array_push($data1, $newRow1);
            array_push($data2, $newRow2);
            // array_push($data3, $newRow3);
        }
        
        $yAxis = (object) [ 'title' => (object) [ 'text'=> $yTitle]];
        
        $yAxis->min= 0;
        $yAxis->max= 100;
        $yAxis->tickInterval= 10;
        
        
        
        $highchart = (object)
        [
            'chart' => (object) [ 'type' => "column"],
            'title' => (object) [ 'text'=> $title],
            'credits' => (object) ['enabled' => false],
            'xAxis' => (object) [ 'categories' => $categories],
            'plotOptions' => (object)
            [
                'column'=> (object)[
                    'stacking' => 'normal',
                    'dataLabels'=>(object)
                    [
                        'enabled'=>true,
                        //'crop'=>false,
                        //'overflow' =>'none',
                        //"inside"=> false,
                        'color'=> 'black',
                        'style'=> (object)
                        [
                            'fontSize' => 10,
                            'textOutline' => '0px'
                        ],
                        'verticalAlign' => 'bottom'
                        // 'format'=>"{point.y:.1f} %"
                    ]
                ]
            ],
            'yAxis' => $yAxis,
            'series' => array(
                (object) ['name' => "Justificadas", 'data' => $data2,  'showInLegend' => $showInLegend, "color"=>"#f39c12"],
                (object) ['name' => "Enviadas", 'data' => $data1,  'showInLegend' => $showInLegend, "color"=>"#00a65a"],
                //(object) ['name' => "En proceso de validación", 'data' => $data3,  'showInLegend' => $showInLegend, "color"=>"#919191"]
            )
        ];
        
        //         $data= (object) [
        //             'async' =>  true,
        //             'type' => 'image/jpeg',
        //             'width' => 1080,
        //             'options' => $highchart
        //         ];
        
        //         $options = array(
        //             'http' => array(
        //                 'method'  => 'POST',
            //                 'content' => json_encode( $data ),
            //                 'header'=>  "Content-Type: application/json\r\n" .
            //                 "Accept: application/json\r\n"
            //             )
            //         );
            
        //         $url = EXPORT_HIGHCHARTS_SERVER;
        
        //         $context  = stream_context_create( $options );
        
        
        
        //         $result = file_get_contents( $url, false, $context );
        
        //         $charturl='';
        //         if ($result === FALSE)
        //         {
            
            //         }
            //         else
                //         {
                //             $charturl = $url . $result;
                
                //         }
                //         return $charturl;
                $chartURL = getHightchartsURL($highchart);
                return $chartURL;
                
                //  return 'ok';
                
    }
    
    
    function graficaBarrasUsuarios($title, $yTitle, $serieTitle, $rows, $xField)
    {
        $showInLegend = true;
        
        $categories = array();
        $data = array();
        
        $data = array();
        
        $data1 = array();
        $data2 = array();
        $data3 = array();
        
        $fecha = new DateTime();
        $mesActual = $this->mesSAHA;
        
        for ($i = 0; $i < count($rows); $i++)
        {
            $row = $rows[$i];
            
            
            $newRow1= (object) [
                'name' =>  $row->$xField,
                'y' => (float)$row->porcentajeEnviadas,
            ];
            
            $newRow2= (object) [
                'name' =>  $row->$xField,
                'y' => (float)$row->porcentajeJustificadas,
            ];
            
            
            array_push($categories, $row->$xField);
            array_push($data1, $newRow1);
            array_push($data2, $newRow2);
            // array_push($data3, $newRow3);
        }
        
        $yAxis = (object) [ 'title' => (object) [ 'text'=> $yTitle]];
        
        $yAxis->min= 0;
        $yAxis->max= 100;
        $yAxis->tickInterval= 10;
        
        
        
        $rotacion = 0;
        if(count($rows)>=10)
            $rotacion = -90;
            
            
            
            $highchart = (object)
            [
                'chart' => (object) [ 'type' => "column"],
                'title' => (object) [ 'text'=> $title],
                'credits' => (object) ['enabled' => false],
                'xAxis' => (object) [ 'categories' => $categories],
                'plotOptions' => (object)
                [
                    'column'=> (object)[
                        'stacking' => 'normal',
                        'dataLabels'=>(object)
                        [
                            'enabled'=>true,
                            'color'=> 'black',
                            'style'=> (object)
                            [
                                'fontSize' => 10,
                                'textOutline' => '0px'
                            ],
                            'verticalAlign' => 'bottom'
                            
                        ]
                    ]
                ],
                'yAxis' => $yAxis,
                'series' => array(
                    (object) ['name' => "Justificadas", 'data' => $data2,  'showInLegend' => $showInLegend, "color"=>"#f39c12"],
                    (object) ['name' => "Enviadas", 'data' => $data1,  'showInLegend' => $showInLegend, "color"=>"#00a65a"],
                )
            ];
            
            $chartURL = getHightchartsURL($highchart);
            return $chartURL;
            
            
    }
    
    function nivelRiesgoSAHA()
    {
        $this->AddPage();
        $this->fondoPlantilla();
        $this->tituloPagina("Resultados SAHA");
        $this->subtitulo("Nivel de riesgo");
        $this->logoApp("../imagenes/logo_saha02.png", 30);
        
        $meses = array();
        $repositorio = new EvidenciasRepositorio($this->conexion);
        
        if($this->mesSAHA == 1 && $this->ano != $this->anoSAHA)
            $limite = 12;
            else
                $limite = $this->mesSAHA;
                
                for($i = 1; $i <= $limite; $i++)
                {
                    $mes = (object) [];
                    $mes->mes = $i;
                    $mes->nombreMes = Mes::getNombre($i);
                    
                    $criteriosSeleccion= (object) [
                        'mes' =>  $i,
                        'ano' =>  $this->anoSAHA,
                        "empresaId" => $this->empresa->id
                    ];
                    
                    // $camposGroupBy = array();
                    //array_push($camposGroupBy,(object)['tabla'=>'EM','campo'=>'id','alias'=>'empresaId']);
                    $resultado = $repositorio->consultarPorcentajesEvidencias($this->usuario, $criteriosSeleccion);
                    if($resultado->correcto())
                    {
                        $mes->enviadas = $resultado->valor[0]->valor;
                        $mes->pedientes = $resultado->valor[1]->valor;
                        $mes->justificadas = $resultado->valor[2]->valor;
                        
                        $total =  $mes->enviadas +  $mes->pedientes +   $mes->justificadas;
                        $cumplidas = $mes->enviadas +  $mes->justificadas;
                        $porcentajeCumplimiento = 0;
                        if($total!=0)
                            $porcentajeCumplimiento = $cumplidas * 100 / $total;
                            
                            $porcentajeCumplimientoEnviadas = 0;
                            if($total!=0)
                                $porcentajeCumplimientoEnviadas = $mes->enviadas * 100 / $total;
                                
                                $mes->porcentajeCumplimiento=    number_format($porcentajeCumplimiento, 1, '.', '');
                                $mes->porcentajeCumplimientoEnviadas =  number_format($porcentajeCumplimientoEnviadas, 1, '.', '');
                                
                                
                    }
                    
                    array_push($meses, $mes);
                    
                }
                if($this->mesSAHA == 1 && $this->ano != $this->anoSAHA)
                {
                    
                }
                else
                {
                    for($i = $this->mesSAHA + 1; $i <= 12 ; $i++)
                    {
                        $mes = (object)[
                            "porcentajeCumplimiento" =>  null,
                            "porcentajeCumplimientoEnviadas" => null
                        ];
                        $mes->nombreMes = Mes::getNombre($i);
                        $mes->mes = $i;
                        array_push($meses, $mes);
                    }
                }
                $pdfWidth = $this->GetPageWidth();
                $chartWidth= 170;
                $colores = [ '#00a1ff', '#60d836', '#f8ba00'];
                $image = toLineChart("Nivel de riesgo anual <br>($this->anoSAHA)",'','Cumplimiento global',$meses,"nombreMes","porcentajeCumplimiento",$colores,true,100,12);
                if($image!='')
                    $this->Image($image,100 ,50, $chartWidth);
                    
                    $this->Image("../imagenes/caricatura/caricatura03.png", 35, 119, 67);
    }
    
    protected function tituloPagina($titulo)
    {
        
        $this->SetLeftMargin(5);
        $this->SetX(0);
        $this->SetY($this->h/2 - 10);
        $this->fontSizes = array(32);
        $this->fontWeights = array("B");
        $this->fontNames = array($this->font);
        $this->aligns = array("C");
        $this->widths = array(80);
        $this->textColors = array("#ffffff");
        $this->borders = array(0);
        $this->backgroundColors = array("#ffffff");
        $this->RowTransparent(array($this->texto($titulo)),10);
        
        
    }
    
    
    
    
    
    
    
    public function generar($usuario,$criteriosSeleccion)
    {
        $this->criteriosSeleccion = $criteriosSeleccion;
        $this->usuario = $usuario;
        if($this->criteriosSeleccion!=null)
        {
            
            $empresasRepositorio = new EmpresasRepositorio($this->conexion);
            $resultado = $empresasRepositorio->consultarPorLlaves((object)["id" => $criteriosSeleccion->empresaId]);
            if($resultado->correcto())
            {
                $this->empresa = $resultado->valor;
                
                $this->dia = date("d");
                $this->mes = date("m");
                $this->ano = date("Y");
                
                /*   $this->dia = 1;
                 $this->mes = 11;
                 $this->ano = 2024;*/
                
                if( $this->dia < 28)
                {
                    $fecha = new DateTime();
                    $fecha->setDate($this->ano,$this->mes,1);
                    $fecha->sub(new DateInterval('P1M'));
                    
                    $this->anoSAHA = $fecha->format("Y");
                    $this->mesSAHA = $fecha->format("m");
                }
                else
                {
                    $this->anoSAHA = $this->ano;
                    $this->mesSAHA = $this->mes;
                }
                
                /*01*/
                $this->portada();
                $this->saha();
                $this->cavi();
                $this->sivah();
            }
            
        }
    }
    
    
    
    function avanceSedeCAVI($colores)
    {
        $this->AddPage();
        $this->fondoPlantilla();
        $this->tituloPagina("Resultados CAVI");
        $this->subtitulo("Avance CAVI por sede");
        $this->logoApp("../imagenes/triangulo_cavi.png", 30);
        $this->Image("../imagenes/caricatura/caricatura05.png", 50, 135, 50);
        
        $criteriosSeleccion= (object) [
            'fechaInicial' =>  $this->empresa->fechaInicioTemporada,
            'fechaFinal' =>  date("d/m/Y"),
            "empresaId" => $this->empresa->id
        ];
        $chartWidth= 170;
        $repositorio = new CursosRepositorio($this->conexion);
        $criteriosSeleccion->tipoReporte = TipoReporte::TODOS;
        $resultado = $repositorio->consultarResultadosSedes($this->usuario, $criteriosSeleccion);
        if($resultado->correcto())
        {
            $registros = $resultado->valor;
            
            $image = toColumnChartSerieColors("",'','',$registros,"nombre","porcentajeAvance",$colores,false,100,false,"{point.y:.1f} %");
            if($image!='')
                $this->Image($image, 100 ,50, $chartWidth);
                
                
        }
        else
            $this->textoCentrado($resultado->mensajeError, 0, 0, 0, 0, 'B', 12);
            
            
    }
    
    function logoApp($logo, $width)
    {
        $this->Image($logo, 75, 0, $width);
    }
    
    function aprovechamientoDepartamentoCAVI($colores)
    {
        $this->AddPage();
        $this->fondoPlantilla();
        $this->tituloPagina("Resultados CAVI");
        $this->subtitulo("Aprovechamiento CAVI por departamento");
        $this->logoApp("../imagenes/triangulo_cavi.png", 30);
        
        $this->Image("../imagenes/caricatura/caricatura02.png", 50, 120, 50);
        
        $criteriosSeleccion= (object) [
            'fechaInicial' =>  $this->empresa->fechaInicioTemporada,
            'fechaFinal' =>  date("d/m/Y"),
            "empresaId" => $this->empresa->id
        ];
        $chartWidth= 170;
        $repositorio = new CursosRepositorio($this->conexion);
        $this->criteriosSeleccion->tipoReporte = TipoReporte::CAPACITACION_INICIADA;
        $resultado = $repositorio->consultarResultadosDepartamentos($this->usuario, $criteriosSeleccion);
        if($resultado->correcto())
        {
            $registros = $resultado->valor;
            $image = toColumnChartSerieColors("",'','',$registros,"nombre","porcentaje",$colores,false,100,false,"{point.y:.1f} %");
            if($image!='')
                $this->Image($image, 100 ,50, $chartWidth);
                
                
        }
        
        
    }
    
    function avanceDepartamentoCAVI($colores)
    {
        $this->AddPage();
        $this->fondoPlantilla();
        $this->tituloPagina("Resultados CAVI");
        $this->subtitulo("Avance CAVI por departamento");
        $this->logoApp("../imagenes/triangulo_cavi.png", 30);
        $this->Image("../imagenes/caricatura/caricatura06.png", 50, 135, 50);
        
        $criteriosSeleccion= (object) [
            'fechaInicial' =>  $this->empresa->fechaInicioTemporada,
            'fechaFinal' =>  date("d/m/Y"),
            "empresaId" => $this->empresa->id
        ];
        $chartWidth= 170;
        $repositorio = new CursosRepositorio($this->conexion);
        $criteriosSeleccion->tipoReporte = TipoReporte::TODOS;
        $resultado = $repositorio->consultarResultadosDepartamentos($this->usuario, $criteriosSeleccion);
        if($resultado->correcto())
        {
            $registros = $resultado->valor;
            
            $image = toColumnChartSerieColors("",'','',$registros,"nombre","porcentajeAvance",$colores,false,100,false,"{point.y:.1f} %");
            if($image!='')
                $this->Image($image, 100 ,50, $chartWidth);
                
                
        }
        
        
    }
    
    function avanceCierreAuditoriaSIVAH()
    {
        $this->AddPage();
        $this->fondoPlantilla();
        $this->tituloPagina("Seguimiento de auditoría");
        $this->subtitulo("Avance de cierre de auditoría");
        $this->logoApp("../imagenes/logoSIVAH.png", 30);
        $this->Image("../imagenes/caricatura/caricatura07.png", 50, 120, 45);
        
        $imageWidth = 100;
        $this->Image("../imagenes/portada_seguimiento.jpg", ($this->w / 2) - ($imageWidth / 2) + 33, 50, $imageWidth);
        
        
        
        
        
        
        $fecha = new DateTime();
        $fecha = $fecha->format("d/m/Y H:i");
        $this->SetFont($this->font,'',9);
        
        $this->SetY(30);
        $this->SetFont($this->font,'I',11);
        $this->Cell(0,6,$this->texto("Reporte generado el: $fecha"),0,2,'R');
        //$this->Cell(0,6,$this->texto( "Avance a la fecha: $porcentaje%"),0,2,'R');
        
        $this->SetY(155);
        $this->SetFont($this->font,'',11);
        //$this->Cell(0,6,$this->texto("Calificación de auditoría actualizada:"),0,2,'R');
        //$this->Cell(0,6,$this->texto("Incluyendo validadas: $calificacionValidadas%"),0,2,'R');
        //$this->Cell(0,6,$this->texto("Incluyendo validadas y enviadas: $calificacionValidadasEnviadas%"),0,2,'R');
        
        $this->textoCentrado($this->empresa->nombre, 130, 0, 0, 0, 'B', 11);
        
        $this->linea(45,127,127,127,0.3);
        
        $this->linea(178,127,127,127,0.3);
        
        
        
        
        /* $id = $this->auditoria->id;
        
        $this->SetTextColor(0,0,0);
        $this->SetY(177);
        $borde = 0;
        $anchoColumna = ($this->w - ($this->margen * 2)) / 3;
        $this->SetFont($this->font, 'I', 9);
        $this->Cell(0, 8, $this->texto("Fecha de auditoría: ").$this->texto($fechaAuditoria) , $borde, 0, 'L');*/
    }
    
    function avanceGlobalCAVI()
    {
        $this->AddPage();
        $this->fondoPlantilla();
        $this->tituloPagina("CAVI");
        $this->subtitulo("Avance global");
        $this->logoApp("../imagenes/triangulo_cavi.png", 30);
        $this->Image("../imagenes/caricatura/caricatura04.png", 50, 120, 50);
        
        $imageWidth = 100;
        $this->Image("../imagenes/logoCAVI.png", ($this->w / 2) - ($imageWidth / 2) + 33, 30, $imageWidth);
        
        
        $this->SetFillColor(120,132,68);
        $rectangleWidth = 140;
        $this->Rect(($this->w / 2) - ($rectangleWidth / 2) + 35, 70, $rectangleWidth, 30, 'F');
        
        $this->textoCentrado("Reporte de capacitación virtual", 75, 255, 255, 255, 'B', 15);
        $this->textoCentrado($this->empresa->fechaInicioTemporada . " - " . date("d/m/Y"), 85, 255, 255, 255, '', 12);
        
        
        $this->textoCentrado($this->empresa->nombre, 130, 0, 0, 0, 'B', 11);
        
        $criteriosSeleccion= (object) [
            'fechaInicial' =>  $this->empresa->fechaInicioTemporada,
            'fechaFinal' =>  date("d/m/Y"),
            "empresaId" => $this->empresa->id
        ];
        
        
        $repositorio = new CursosRepositorio($this->conexion);
        $criteriosSeleccionAprovechamiento = clone $criteriosSeleccion;
        $criteriosSeleccionAprovechamiento->tipoReporte = TipoReporte::CAPACITACION_INICIADA;
        $resultado = $repositorio->consultarResultados($this->usuario, $criteriosSeleccionAprovechamiento);
        $aprovechamiento=0;
        if($resultado->correcto())
        {
            if(count($resultado->valor)>0)
            {
                $registro = $resultado->valor[0];
                $aprovechamiento=  $registro->porcentaje;
            }
        }
        
        $criteriosSeleccionAvance = clone $criteriosSeleccion;
        $criteriosSeleccionAvance->tipoReporte = TipoReporte::TODOS;
        $resultado = $repositorio->consultarResultados($this->usuario, $criteriosSeleccionAvance);
        $avance=0;
        if($resultado->correcto())
        {
            if(count($resultado->valor)>0)
            {
                $registro = $resultado->valor[0];
                $avance=  $registro->porcentajeAvance;
            }
        }
        
        
        $this->textoCentrado("Avance a la fecha: $avance%", 140, 0, 0, 0, '', 11);
        $this->textoCentrado("Aprovechamiento a la fecha: $aprovechamiento%", 150, 0, 0, 0, '', 11);
        
        
        
    }
    
    function saha()
    {
        /* 02 */
        $this->temasReunion();
        /* 03 */
        $this->cumplimientoGlobalSAHA();
        /* 04 */
        $this->cumplimientoSedeSAHA();
        /* 05 */
        $this->cumplimientoDepartamentoSAHA();
        /* 06 */
        $this->cumplimientoUsuarioSAHA();
        /* 07 */
        $this->nivelRiesgoSAHA();
    }
    
    function cavi()
    {
        $colores = GeneradorColores::generar(100);
        /* 08 */
        $this->avanceGlobalCAVI();
        $this->avanceSedeCAVI($colores);
        $this->avanceDepartamentoCAVI($colores);
        $this->aprovechamientoDepartamentoCAVI($colores);
        
    }
    
    function sivah()
    {
        $this->avanceCierreAuditoriaSIVAH();
        
        $repositorio = new AuditoriasRepositorio($this->conexion);
        $resultado = $repositorio->consultarAuditoriasRecientesEmpresa($this->empresa->id);
        if($resultado->correcto())
        {
            $auditorias = $resultado->valor;
            
            $hallazgosPendientes = $this->avanceCierreAuditoriaSedeSIVAH($auditorias);
            $this->avanceCierreAuditoriaDepartamentoSIVAH($auditorias,$hallazgosPendientes);
            $this->avanceCierreAuditoriaUsuarioSIVAH($auditorias,$hallazgosPendientes);
        }
        
    }
    
    function avanceCierreAuditoriaSedeSIVAH($auditorias)
    {
        $hallazgosPendientes = 0;
        $this->AddPage();
        $this->fondoPlantilla();
        $this->tituloPagina("Seguimiento de auditoría");
        $this->subtitulo("Avance de cierre de auditoría por sede");
        $this->logoApp("../imagenes/logoSIVAH.png", 30);
        $this->Image("../imagenes/caricatura/caricatura05.png", 50, 135, 50);
        
        $repositorio = new AuditoriasRepositorio($this->conexion);
        //$sedes = array();
        $resultado = $repositorio->consultarAuditoriasRecientesEmpresa($this->empresa->id);
        if($resultado->correcto())
        {
            $auditorias = $resultado->valor;
            $resultado = $repositorio->consultarAuditorias($auditorias);
            if($resultado->correcto())
            {
                $auditorias = $resultado->valor;
                
                $chartWidth = 170;
                $image = $this->graficaBarrasAvanceAuditoria("",'','',$auditorias,"sedeNombre",true);
                if($image!='')
                    $this->Image($image, 100 ,50, $chartWidth);
                    
                    $hallazgosPendientes = $this->calcularHallazgosPendientes($auditorias);
                    $this->hallazgosPendientes($hallazgosPendientes);
            }
            
            
        }
        else
            echo $resultado->mensajeError;
            /* $chartWidth = 170;
             $repositorio = new AuditoriasRepositorio($this->conexion);
             $resultado = $repositorio->consultarHallazgosSede($this->auditoria->id);
             if($resultado->correcto())
             {
             $hallazgos = $resultado->valor;
             $image = $this->graficaBarrasAvance("",'','',$hallazgos,"nombreCompleto",true);
             if($image!='')
             $this->Image($image, 100 ,50, $chartWidth);
             
             }*/
             return $hallazgosPendientes;
             
    }
    
    function hallazgosPendientes($hallazgos)
    {
        $this->SetY(170);
        $this->SetLeftMargin(230);
        $this->SetFont($this->font, '', 10);
        
        $texto = "";
        if($hallazgos == 1)
            $texto = $hallazgos . " Hallazgo pendiente";
            else
                $texto = $hallazgos . " Hallazgos pendientes";
                
                $this->Cell(50,6,$this->texto($texto),0,2,'C');
    }
    
    function calcularHallazgosPendientes($auditorias)
    {
        $suma = 0;
        for($i = 0; $i < count($auditorias); $i++)
        {
            $auditoria = $auditorias[$i];
            $suma += $auditoria->recomendacionesPendientes;
        }
        return $suma;
    }
    
    function avanceCierreAuditoriaDepartamentoSIVAH($auditorias,$hallazgosPendientes)
    {
        $this->AddPage();
        $this->fondoPlantilla();
        $this->tituloPagina("Seguimiento de auditoría");
        $this->subtitulo("Avance de cierre de auditoría por departamento");
        $this->logoApp("../imagenes/logoSIVAH.png", 30);
        $this->Image("../imagenes/caricatura/caricatura02.png", 50, 120, 50);
        
        $chartWidth = 170;
        $repositorio = new AuditoriasRepositorio($this->conexion);
        $hallazgos = array();
        for($i = 0; $i < count($auditorias); $i++)
        {
            $auditoria = $auditorias[$i];
            $resultado = $repositorio->consultarHallazgosDepartamento($auditoria->id);
            if($resultado->correcto())
            {
                $hallazgos = array_merge($hallazgos, $resultado->valor);
            }
            // echo $auditoria->id.",";
        }
        
        //total,validadas,proceso
        //$hallazgos = ArrayUtils::filter($hallazgos, "nombre","Sistemas");
        // echo "count: " . count($hallazgos);
        $hallazgos = ArrayUtils::groupBySUM("nombre", "total,validadas,proceso", $hallazgos);
        //$hallazgos = ArrayUtils::groupBySUM("nombre", "total,validadas,proceso", $hallazgos);
        $hallazgos = ArrayUtils::orderBy($hallazgos);
        // $hallazgos = $this->orderByAuditoriayNombre($hallazgos);
        
        // var_dump($hallazgos);
        
        
        
        $image = $this->graficaBarrasAvance("",'','',$hallazgos,"nombre",true);
        if($image!='')
            $this->Image($image, 100 ,50, $chartWidth);
            
            $this->hallazgosPendientes($hallazgosPendientes);
    }
    
    static function orderByAuditoriayNombre($records)
    {
        $getName = function ($registro) {
            return $registro->auditoriaYNombre;
        };
        $names = array_map($getName, $records);
        sort($names);
        $sorted = [];
        foreach ($names as $name) {
            foreach ($records as $record) {
                if ($record->auditoriaYNombre === $name) {
                    $sorted[] = $record;
                    break;
                }
            }
        }
        return $sorted;
    }
    
    static function ordernarPorNombre($a, $b) {
        return strcmp(strtolower($a->nombre), strtolower($b->nombre));
    }
    
    
    
    
    function avanceCierreAuditoriaUsuarioSIVAH($auditorias,$hallazgosPendientes)
    {
        $this->AddPage();
        $this->fondoPlantilla();
        $this->tituloPagina("Seguimiento de auditoría");
        $this->subtitulo("Avance de cierre de auditoría por usuario");
        $this->logoApp("../imagenes/logoSIVAH.png", 30);
        $this->Image("../imagenes/caricatura/caricatura02.png", 50, 120, 50);
        
        $chartWidth = 170;
        $repositorio = new AuditoriasRepositorio($this->conexion);
        $hallazgos = array();
        for($i = 0; $i < count($auditorias); $i++)
        {
            $auditoria = $auditorias[$i];
            $resultado = $repositorio->consultarHallazgosUsuarios($auditoria->id);
            if($resultado->correcto())
            {
                $hallazgos = array_merge($hallazgos, $resultado->valor);
            }
        }
        //$hallazgos = ArrayUtils::filter($hallazgos, "nombre","Andrés");
        $hallazgos = ArrayUtils::groupBySUM("nombreCompleto", "total,validadas,proceso", $hallazgos);
        $hallazgos = ArrayUtils::orderByNombreCompleto($hallazgos);
        
        
        $image = $this->graficaBarrasAvance("",'','',$hallazgos,"nombreCompleto",true);
        if($image!='')
            $this->Image($image, 100 ,50, $chartWidth);
            
            // echo json_encode($hallazgos, JSON_UNESCAPED_UNICODE);
            $this->hallazgosPendientes($hallazgosPendientes);
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
            /* 'plotOptions' => (object)
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
             ],*/
            'plotOptions' => (object)
            [
                
                'series' => (object)
                [
                    'dataLabels'=>(object)
                    [
                        'enabled'=>true,
                        'format' => "{point.y}",
                        'color'=> 'black',
                        'style'=> (object)
                        [
                            'fontSize' => 10,
                            'textOutline' => '0px'
                        ],
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
        
        $chartURL = getHightchartsURL($highchart);
        return $chartURL;
        
        //  return 'ok';
        
    }
    
    function graficaBarrasAvanceAuditoria($title, $yTitle, $serieTitle, $rows, $xField, $showInLegend)
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
                'y' => (float)$row->puntuacion,
                'color' => "#0a9ffa"
            ];
            
            $newRow2= (object) [
                'name' =>  $row->$xField,
                'y' => (float)$row->porcentajeAvance,
                'color' => "#78d34b"
            ];
            
            
            
            array_push($categories, $row->$xField);
            array_push($data1, $newRow1);
            array_push($data2, $newRow2);
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
                        'format' => "{point.y:.1f} %",
                        'color'=> 'black',
                        'style'=> (object)
                        [
                            'fontSize' => 10,
                            'textOutline' => '0px'
                        ],
                    ]
                ]
            ],
            'yAxis' => $yAxis,
            'series' => array(
                (object) ['name' => "Puntuación", 'data' => $data1,  'showInLegend' => $showInLegend, "color"=>"#0a9ffa"],
                (object) ['name' => "Avance", 'data' => $data2,  'showInLegend' => $showInLegend, "color"=>"#78d34b"]
            )
        ];
        
        $chartURL = getHightchartsURL($highchart);
        return $chartURL;
        
        //  return 'ok';
        
    }
    
    function graficaBarrasMesActualAnterior($title, $yTitle, $serieTitle, $rows, $xField, $yField, $showInLegend,$max)
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
                'y' => (float)$row->porcentajeEnviadas,
                'color' => "#05a65a"
            ];
            
            $newRow2= (object) [
                'name' =>  $row->$xField,
                'y' => (float)$row->porcentajePendientes,
                'color' => "#fe2500"
                
            ];
            
            $newRow3= (object) [
                'name' =>  $row->$xField,
                'y' => (float)$row->porcentajeJustificadas,
                'color' => "#f39c13"
                
                
            ];
            
            array_push($categories, $row->$xField);
            array_push($data1, $newRow1);
            array_push($data2, $newRow2);
            array_push($data3, $newRow3);
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
                        "inside"=> true,
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
                (object) ['name' => "Enviadas", 'data' => $data1,  'showInLegend' => $showInLegend, "color"=>"#05a65a"],
                (object) ['name' => "Pendientes", 'data' => $data2,  'showInLegend' => $showInLegend, "color"=>"#fe2500"],
                (object) ['name' => "Justificadas", 'data' => $data3,  'showInLegend' => $showInLegend, "color"=>"#f39c13"]
            )
        ];
        $chartURL = getHightchartsURL($highchart);
        return $chartURL;
        
    }
    
    function graficaComparativoAvanceAprovechamiento($title, $yTitle, $serieTitle,$serieTitleAnterior, $rows, $xField, $yFieldActual, $yFieldAnterior, $colors, $showInLegend,$max)
    {
        $categories = array();
        $data1 = array();
        $data2 = array();
        
        for ($i = 0; $i < count($rows); $i++)
        {
            $row = $rows[$i];
            $category = $row->$xField;
            //$value = (float)$row->$yField;
            
            /* if($value>=0 && $value<51)
             $color="#dd4b39";
             else if($value>=51 &&   $value <100)
             $color="#f39c12";
             else iF($value>=100)
             $color="#00a65a";*/
             
             
             
             $newRow1= (object) [
                 'name' =>  $category,
                 'y' => floatval($row->$yFieldActual)
                 
             ];
             
             $newRow2= (object) [
                 'name' =>  $category,
                 'y' => floatval($row->$yFieldAnterior)
             ];
             
             array_push($categories, $category);
             array_push($data1, $newRow1);
             array_push($data2, $newRow2);
        }
        
        $yAxis = (object) [ 'title' => (object) [ 'text'=> $yTitle, 'align' => 'high'], 'labels' => (object) [ 'overflow'=> 'justify']];
        if($max>0)
        {
            $yAxis->min= 0;
            $yAxis->max= $max;
            $yAxis->tickInterval= 10;
            
        }
        
        $highchart = (object)
        [
            'chart' => (object) [ 'type' => "bar"],
            'title' => (object) [ 'text'=> $title],
            'credits' => (object) ['enabled' => false],
            'xAxis' => (object) [ 'categories' => $categories, "tickLength"=> 10],
            'plotOptions' => (object)
            [
            //                 'bar'=> (object)[
                //                     'dataLabels'=>(object)
                //                     [
                    //                         'enabled'=>true,
                    //                         'crop'=>false,
                    //                         'overflow' =>'none',
                    //                         "inside"=> false,
                    //                         'color'=> 'black',
                    //                         'borderColor' => 'black',
                    //                         'style'=> (object)
                    //                         [
                        //                             'fontSize' => 10,
                        //                             //'textOutline' => '1px'
                        //                         ],
                    //                         'format'=>"{point.y:.1f} %"
                        //                     ]
                    //                 ],
        'series' => (object)
        [
            'dataLabels'=>(object)
            [
                'enabled'=>true,
                'format' => "{point.y:.1f} %",
                'color'=> 'black',
                'style'=> (object)
                [
                    'fontSize' => 10,
                    'textOutline' => '0px'
                ],
            ]
        ]
        ],
        'yAxis' => $yAxis,
        'series' => array(
            (object) ['name' => $serieTitle, 'data' => $data1, 'color' => "#159df6",  'showInLegend' => true],
            (object) ['name' => $serieTitleAnterior, 'data' => $data2, 'color' => "#87ce58", 'showInLegend' => true]
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
            ),
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            )
        );
        
        $url = EXPORT_HIGHCHARTS_SERVER;
        
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
        $filename ="../reportes_kci/";
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
    
    
    
    function campo($ancho,$texto,$valor)
    {
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
    
    
    public $tablewidths;
    public $aligns;
    public $columnFonts;
    public $footerset;
    
    //     fu
    
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
