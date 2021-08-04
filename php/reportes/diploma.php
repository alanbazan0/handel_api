<?php
use php\clases\AdministradorConexion;
use php\modelos\Resultado;
use php\repositorios\AuditoriasRepositorio;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use php\repositorios\EmpresasRepositorio;
use php\repositorios\UsuariosRepositorio;
use php\repositorios\SedesRepositorio;
use php\repositorios\CursosRepositorio;
use php\clases\Porcentaje;
use php\clases\GeneradorColores;


require('../vendor/fpdf181/fpdf.php');
include '../clases/Utilidades.php';
include '../clases/AdministradorConexion.php';
require_once('../repositorios/EmpresasRepositorio.php');
require_once('../clases/Porcentaje.php');
require_once('../clases/GeneradorColores.php');
require_once('../repositorios/SedesRepositorio.php');
require_once('../repositorios/CursosRepositorio.php');
require_once('../repositorios/UsuariosRepositorio.php');
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
        
        $this->AddFont('SummerFestival','','SummerFestival-Regular.php');
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
        
    }
    
    
    
    function Header()
    {
        
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
        $folio.="Diploma";
//         if($this->empresa!=null)
//             $folio.="-".$this->empresa->nombreCorto;
//         if($this->sede!=null)
//             $folio.="-".$this->sede->nombreCorto;
        
        
        if($this->usuario!=null)
            $folio.="-". str_replace(" ","-",$this->usuarioDiploma->nombreCompleto);
                
        if($this->criteriosSeleccion->fechaInicial!=null && $this->criteriosSeleccion->fechaFinal!=null)
        {
            list($dia, $mes, $ano) = explode("/", $this->criteriosSeleccion->fechaInicial);
            $folio.="-".$dia.$mes.$ano;
            list($dia, $mes, $ano) = explode("/", $this->criteriosSeleccion->fechaFinal);
            $folio.="-".$dia.$mes.$ano;
        }
                
                //list($dia, $mes, $ano) = explode("/", $thisfecha);
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
    
                            
function portada()
{
    $this->AddPage();
    $imagen = "../imagenes/diploma_cavi1.png";
    $this->Image($imagen,0,0,$this->w,$this->h);
}


                            
private function subtitulo($texto)
{
    $this->SetFont($this->font,'B',15);
    $this->SetTextColor(0,0,0);
    $this->Cell(0,8,$this->texto($texto),0,2,'C');
}
                            
                            
public function generar($usuario,$criteriosSeleccion)
{
    $this->criteriosSeleccion = $criteriosSeleccion;
    $this->usuario = $usuario;
    if($this->criteriosSeleccion!=null)
    {
        $empresasRepositorio = new EmpresasRepositorio($this->conexion);
        $sedesRepositorio = new SedesRepositorio($this->conexion);
        $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
        
        $resultado = $empresasRepositorio->consultarPorLlaves((object)["id" => $criteriosSeleccion->empresaId]);
        if($resultado->correcto())
            $this->empresa = $resultado->valor;
            
        $resultado = $sedesRepositorio->consultarPorLlaves((object)["id" => $criteriosSeleccion->sedeId]);
        if($resultado->correcto())
            $this->sede = $resultado->valor;
        
        $resultado = $usuariosRepositorio->consultarPorLlaves((object)["id" => $criteriosSeleccion->usuarioId]);
        if($resultado->correcto())
            $this->usuarioDiploma = $resultado->valor;
       
           // var_dump($criteriosSeleccion);
                
        $colores = GeneradorColores::generar(100);
        
        $this->portada();
        $this->usuario();
       
    }
}

function usuario()
{
    $this->SetY(80);
    $tamanoLinea = 8;
    $borde = 0;
    $this->SetFont('SummerFestival','',70);
    $this->SetTextColor(0,0,0);
    $this->Cell(0,$tamanoLinea,$this->texto($this->usuarioDiploma->nombreCompleto),$borde,1,'C');
    
}

function periodo()
{
    
}


static function compartarPorcentaje($a, $b)
{
    if ($b->porcentaje == $a->porcentaje)
        return strcmp($b->porcentajeAvance, $a->porcentajeAvance);
        return strcmp($b->porcentaje, $a->porcentaje);
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
    $filename ="../diplomas/";
    
    if(!file_exists($filename))
        mkdir($filename);
    
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



class ReporteCapacitaciones extends PDF
{
    
    
    
    
    
}


class ReporteFabrica
{
    public function crear()
    {
        $reporte = new ReporteCapacitaciones();
        return $reporte;
    }
}


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
            $usuario = $_SESSION['usuario'];
        $reporteFabrica = new ReporteFabrica();
        $reporte = $reporteFabrica->crear();
        if($reporte!=null)
        {
            $reporte->setConexion($conexion);
            $reporte->AliasNbPages();
            $reporte->generar($usuario,$criteriosSeleccion);
            $reporte->imprimir();
            
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


