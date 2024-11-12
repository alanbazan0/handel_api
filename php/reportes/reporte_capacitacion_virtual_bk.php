<?php
use php\clases\AdministradorConexion;
use php\modelos\Resultado;
use php\repositorios\AuditoriasRepositorio;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use php\repositorios\EmpresasRepositorio;
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
        
        $this->SetTextColor(0,0,0);
        $this->SetY(-20);
        $borde = 0;
        $this->SetFont($this->font, 'I', 9);
        $anchoColumna = ($this->w - ($this->margen * 2)) / 3;
        
        $this->Cell(0, 4, $this->texto("Prohibida la reproducción total o parcial"), $borde, 1, 'C');
        $this->SetFont($this->font, 'U', 9);
        $this->Cell($anchoColumna, 4, "cavi.apps-handel.com", $borde, 0, 'L', false,"https://cavi.apps-handel.com");
        $this->SetFont($this->font, 'I', 9);
        $this->Cell($anchoColumna, 4, $this->texto("sin autorización expresa de Handel Consultoría"), $borde, 0, 'C');
        $this->Cell($anchoColumna, 4, "Hoja ". $this->PageNo().' de {nb}', $borde, 0, 'R');
    }
    
    
    
    function Header()
    {
        $this->SetLeftMargin($this->margen);
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
        $fechaInicial = $this->criteriosSeleccion->fechaInicial;
        $fechaFinal = $this->criteriosSeleccion->fechaFinal;
        $this->Cell($anchoColumna, 8, "Periodo $fechaInicial a $fechaFinal", $borde, 0, 'L');
        $this->Cell($anchoColumna, 4, "emitido el $fecha", $borde, 0, 'R');
        
        $logoAlto = 6;
        $logoX = $this->w / 2 - $logoAlto / 2;
        $logoY = 11;
        $logo = "../imagenes/favicon_cavi.png";
        if (file_exists($logo))
            $this->Image($logo,$logoX,$logoY,$logoAlto,0,'','');
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
        $folio.="RCV";
        if($this->empresa!=null)
            $folio.="-".$this->empresa->nombreCorto;
        if($this->sede!=null)
          $folio.="-".$this->sede->nombreCorto;
        
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
    
//     protected function imprimirPuntosTabla($puntos,$borde)
//     {
        
      
//         for($i = 0 ; $i < count($puntos); $i++)
//         {
//             $punto = $puntos[$i];
//             $descripcion = $punto->id .". " . $punto->descripcion;
            
//             $resultado = "";
//             if($punto->resultado == "S")
//             {
//                // $this->SetFont("ZapfDingbats", '', 9);
//                 $resultado =  "@checked";
//             }
//             else if($punto->resultado == "N")
//             {
//                 //  $this->SetFont("ZapfDingbats", '', 9);
//                 $resultado =  "@unchecked";
//             }
//             else
//             {
//                 // $this->SetFont($this->font, '', 9);
//                 $resultado =  "N/A";
//             }
            
//             $observaciones = $punto->observaciones;
//             if(substr( $observaciones, 0, 1 ) === ",")
//             {
//                 $observaciones = substr($observaciones, 1);
//             }
            
//             $observaciones = trim($observaciones);
//             $observaciones=str_replace("\r",'',$observaciones);
//             $observaciones=str_replace("\n",'',$observaciones);
            
//             //$data[] = array($descripcion,$resultado, $observaciones);
            
//             $this->Row(array($descripcion,$resultado,$observaciones),$borde,8);
//         }
//       //  $this->SetLeftMargin(10);
//         //$this->SetTopMargin(0);
//        // $this->morepagestable($data);
//     }
//     protected function imprimirPuntos3($titulo, $puntos)
//     {
//         $borde = 1;
//         $anchoColumna1 = 48;
//         $anchoColumna2 = 30;
//         $anchoColumna3 = 112;
        
//         $this->SetLeftMargin(10);
//         $this->SetFont($this->font, 'B', 13);
//         $this->Ln();
//         $this->SetFillColor(242, 242, 242);
//         $this->Cell(0,8,$this->texto($titulo),$borde,2,'C',1);
        
        
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell($anchoColumna1, 8, $this->texto("Descripción"), $borde, 0, 'C', 1);
//         $this->Cell($anchoColumna2, 8, "Resultado", $borde, 0, 'C', 1);
//         $this->Cell($anchoColumna3, 8, "Observaciones", $borde, 0, 'C', 1);
//         $altoColumna = 6.5;
//         $page_height = 286.93;
//         for($i = 0 ; $i < count($puntos); $i++)
//         {
//             $punto = $puntos[$i];
//             $descripcion = $punto->id .". " . $punto->descripcion;
//             $observaciones = $punto->observaciones;
            
//             $y = $this->GetY();
//             //$space_left=$page_height-($this->GetY()); // space left on page
//             if ( $y > $page_height) {
//                 $this->SetLeftMargin(10);
//                 $this->AddPage(); // page break
//             }
            
//             $this->Ln();
//             $this->SetFont($this->font, '', 9);
//             $this->Cell($anchoColumna1, $altoColumna, $this->texto($descripcion), $borde, 0, 'L');
            
            
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
            
          
//             $this->Cell($anchoColumna2, $altoColumna, $resultado, $borde, 0, 'C');
//             $this->SetFont($this->font, '', 9);
//             $this->MultiCell($anchoColumna3, $altoColumna, $this->texto($observaciones), $borde);
//         }
        
        
//         // $this->Line(10, $y, 210-10, $y);
//     }
    
    function portada()
    {
        $this->AddPage();
       
        $this->SetY(35);
        $this->SetFont($this->font,'B',13);
        $this->SetTextColor(0, 0, 0);
        
        $imagen = "../imagenes/logoCAVI.png";
        $width = $this->w;
        $anchoFoto = 120;
        $x = ($width/2) - ($anchoFoto/2);
        $y = 30;
        $this->Image($imagen,$x,$y,$anchoFoto);
        
        
        $this->SetY(80);
        $this->SetFillColor(113,129,71);
        $this->SetDrawColor(113,129,71);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont($this->font,'',20);
        $this->Cell(0,5,"",1,2,'C',1);
        $this->Cell(0,10,$this->texto("Reporte de capacitación virtual"),1,2,'C',1);
        $this->SetFont($this->font,'',12);
        $this->Cell(0,6,$this->texto(""),1,2,'C',1);
        $this->SetFont($this->font,'I',12);
        $fechas="";
        if($this->criteriosSeleccion->fechaInicial!=null && $this->criteriosSeleccion->fechaFinal!=null)
           $fechas = $this->criteriosSeleccion->fechaInicial ." - ". $this->criteriosSeleccion->fechaFinal;
        $this->Cell(0,6,$fechas,1,2,'C',1);
        $this->Cell(0,5,"",1,2,'C',1);
        
        $this->Ln();
         $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->font,'',12);
        $this->Cell(0,6,$this->texto($this->empresa->nombre),0,2,'C');
        $sedeNombre = "Todas las sedes";
        iF($this->sede!=null)
            $sedeNombre = $this->sede->nombre;
        $this->Cell(0,6,$this->texto($sedeNombre),0,2,'C');
        
        $repositorio = new CursosRepositorio($this->conexion);
        $criteriosSeleccionAprovechamiento = clone $this->criteriosSeleccion;
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
        
        $criteriosSeleccionAvance = clone $this->criteriosSeleccion;
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
        
        //var_dump($this->criteriosSeleccion);
        
        $this->Ln();
        $this->Cell(0,6,$this->texto("Avance a la fecha: $avance%"),0,2,'C');
        $this->Cell(0,6,$this->texto("Aprovechamiento a la fecha: $aprovechamiento%"),0,2,'C');
       
        
        $imagen = "../imagenes/mundo_verde.png";
        $anchoFoto = 20;
        $y = 156;
        $x = 30;
        $this->Image($imagen,$x,$y,$anchoFoto);
        
        $hojas = 0;
        $arboles = 0;
        $litros = 0;
        
        $resultado = $repositorio->consultarVideosVistosEmpresa($this->criteriosSeleccion->empresaId);
        if($resultado->correcto())
        {
            $registro = $resultado->valor;
            $videosVistos = $registro->vistos;
            $hojas = $videosVistos;
            $litros = $videosVistos * 0.2612;
            $arboles = $videosVistos * 0.000063;
            
            $arboles = Porcentaje::formatear($arboles,4);
            $litros = Porcentaje::formatear($litros,2);
        }
        
        $this->Ln();
        $this->Ln();
        $this->SetFont($this->font,'',9);
        $this->SetTextColor(50,110,36);
        $this->SetX(50);
        $this->Cell(0,4,$this->texto("Beneficio ecológico de usar CAVI:"),0,2,'L');
        $this->Cell(0,4,$this->texto("Su compañía ha ahorrado a la fecha $hojas hojas para evaluar la eficiencia de los aprendizajes."),0,2,'L');
        $this->Cell(0,4,$this->texto("Como consecuencia se ha avanzado en salvar $arboles árboles y $litros litros de agua en el proceso."),0,2,'L');
        
    }
    
    public function aviso()
    {
        $this->AddPage();
        $this->SetY(90);
        //$this->subtitulo("Aviso");
        $tamanoLinea = 8;
        $borde = 0;
        $this->SetFont($this->font,'B',11);
        $this->SetTextColor(145,145,145);
        $this->Cell(0,$tamanoLinea,$this->texto('Aviso'),$borde,1,'L');
        $this->SetFont($this->font,'',11);
        $this->Cell(0,$tamanoLinea,$this->texto('El presente reporte incluye un resumen de la capacitación entre el personal registrado en nuestro sistema de'),$borde,1,'FJ');
        $this->Cell(0,$tamanoLinea,$this->texto('capacitación virtual CAVI, es importante que la alta gerencia tenga disponible esta información a fin de que se promueva'),$borde,1,'FJ');
        $this->Cell(0,$tamanoLinea,$this->texto('la activa participación del equipo de trabajo a fin de mantener un estándar que garantice el objetivo de la certificación.'),$borde,1,'FJ');
        //$this->Cell(0,$tamanoLinea,$this->texto('en el seguimiento.'),$borde,1,'L');
        
        $this->Ln();
        $this->SetFont($this->font,'B',11);
        $this->Cell(0,$tamanoLinea,$this->texto('Aviso de privacidad'),$borde,1,'L');
        $this->SetFont($this->font,'',11);
        $this->Cell(0,$tamanoLinea,$this->texto('La información personal contenida en el reporte es protegida por nuestro aviso de privacidad entendiendo que las'),$borde,1,'FJ');
        $this->Cell(0,$tamanoLinea,$this->texto('mismas son utilizadas exclusivamente para realizar una evaluación en el entorno de la certificación. El cliente y sus'),$borde,1,'FJ');
        $this->Cell(0,$tamanoLinea,$this->texto('trabajadores aceptan que el contenido del mismo es para efectos de capacitación, evaluación y trabajo sensible dentro'),$borde,1,'FJ');
        $this->Cell(0,$tamanoLinea,$this->texto('de la organización por lo que se prohibe su libre distribución a personal que no corresponda al equipo de trabajo de'),$borde,1,'FJ');
        $this->Cell(0,$tamanoLinea,$this->texto('Handel o de su propia compañía.'),$borde,1,'L');
        
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
            $resultado = $empresasRepositorio->consultarPorLlaves((object)["id" => $criteriosSeleccion->empresaId]);
            if($resultado->correcto())
                $this->empresa = $resultado->valor;
            
            $resultado = $sedesRepositorio->consultarPorLlaves((object)["id" => $criteriosSeleccion->sedeId]);
            if($resultado->correcto())
                $this->sede = $resultado->valor;
            
//             $coloresBase = [ "#0c9cfb","#78d34b","#6a666a", "#f6ba36","#e13d47","#ca3675","#958b34","#81bede"];
//             $colores  =  $this->generarColores($coloresBase,100);
            
            $colores = GeneradorColores::generar(100);
            
             $this->portada();
            
              $this->aviso();
              $this->introduccion();
             $this->comparativaAvanceAprovechamiento();
            
              $this->avanceDepartamentos($colores);
              $this->aprovechamientoDepartamentos($colores);
              $this->usuariosDepartamento($colores);
              $this->resumenCapacitaciones();
              $this->mejoresAprovechamiento();
        }
    }
    
    private function usuariosDepartamento($colores)
    {
        $this->AddPage();
        $this->SetY(25);
        $this->subtitulo("Usuarios por departamento del período");
        $this->Ln();
        
        $chartWidth = 120;
        $repositorio = new CursosRepositorio($this->conexion);
        $this->criteriosSeleccion->tipoReporte = TipoReporte::TODOS;
        $resultado = $repositorio->consultarUsuariosDepartamento($this->usuario, $this->criteriosSeleccion);
        if($resultado->correcto())
        {
            $registros = $resultado->valor;
                
            $image = toColumnChartSerieColors("",'','',$registros,"nombre","numeroUsuarios",$colores,false,0);
            if($image!='')
                $this->Image($image,30, 60, $chartWidth);
            
            $image = toPieChartWithLabels("",'','',$registros,"nombre","numeroUsuarios",$colores,20);
            if($image!='')
                $this->Image($image,150 ,60, $chartWidth);
        }
    }
    
    
    private function avanceDepartamentos($colores)
    {
        $this->AddPage();
        $this->SetY(25);
        $this->subtitulo("Avance por departamento del período");
        $this->Ln();
        
        $chartWidth = 220;
        $repositorio = new CursosRepositorio($this->conexion);
        $this->criteriosSeleccion->tipoReporte = TipoReporte::TODOS;
        $resultado = $repositorio->consultarResultadosDepartamentos($this->usuario, $this->criteriosSeleccion);
        if($resultado->correcto())
        {
            $registros = $resultado->valor;
           
            $image = toColumnChartSerieColors("",'','',$registros,"nombre","porcentajeAvance",$colores,false,100,false,"{point.y:.1f} %");
            if($image!='')
                $this->Image($image,$this->w/2 -$chartWidth/2 ,40, $chartWidth);
                
                
        }
    }
    
    private function aprovechamientoDepartamentos($colores)
    {
        $this->AddPage();
        $this->SetY(25);
        $this->subtitulo("Aprovechamiento por departamento del período");
        $this->Ln();
        
        $chartWidth = 220;
        $repositorio = new CursosRepositorio($this->conexion);
        $this->criteriosSeleccion->tipoReporte = TipoReporte::CAPACITACION_INICIADA;
        $resultado = $repositorio->consultarResultadosDepartamentos($this->usuario, $this->criteriosSeleccion);
        if($resultado->correcto())
        {
            $registros = $resultado->valor;
            $image = toColumnChartSerieColors("",'','',$registros,"nombre","porcentaje",$colores,false,100,false,"{point.y:.1f} %");
            if($image!='')
                $this->Image($image,$this->w/2 -$chartWidth/2 ,40, $chartWidth);
                
                
        }
    }
    
    
    private function comparativaAvanceAprovechamiento()
    {
        $this->AddPage();
        $this->SetY(25);
        $this->subtitulo("Comparativa de Avance y Aprovechamiento");
        $this->Ln();
        
        $this->SetX(0);
        $y = 80;
        $pdfWidth = $this->w;
        $chartWidth = 220;
        $colores = [ '#00a1ff', '#60d836', '#f8ba00'];
        
        $avancePeriodo = 0;
        $aprovechamientoPeriodo=0;
        $avanceActual=0;
        $aprovechamientoActual=0;
        
        $criteriosSeleccionActual= clone $this->criteriosSeleccion;
        $fechaUltimoDia = Mes::getUltimoDiaMesActual();
        $criteriosSeleccionActual->fechaFinal =$fechaUltimoDia;
        list($dia, $mes, $ano) = explode("/",  $criteriosSeleccionActual->fechaFinal);
        $criteriosSeleccionActual->fechaInicial = "1/$mes/$ano";
        
        $repositorio = new CursosRepositorio($this->conexion);
        
        $this->criteriosSeleccion->tipoReporte = TipoReporte::TODOS;
        $resultado = $repositorio->consultarResultados($this->usuario, $this->criteriosSeleccion);
        if($resultado->correcto())
        {
            if(count($resultado->valor)>0)
            {
                $registro = $resultado->valor[0];
                $avancePeriodo=  $registro->porcentajeAvance;
            }
        }
        else 
            var_dump($resultado->mensajeError);
        $criteriosSeleccionActual->tipoReporte = TipoReporte::TODOS;
        $resultado = $repositorio->consultarResultados($this->usuario, $criteriosSeleccionActual);
        if($resultado->correcto())
        {
            if(count($resultado->valor)>0)
            {
                $registro = $resultado->valor[0];
                $avanceActual=  $registro->porcentajeAvance;
            }
        }
        else
            var_dump($resultado->mensajeError);
        $this->criteriosSeleccion->tipoReporte = TipoReporte::CAPACITACION_INICIADA;
        $resultado = $repositorio->consultarResultados($this->usuario, $this->criteriosSeleccion);
        if($resultado->correcto())
        {
            if(count($resultado->valor)>0)
            {
                $registro = $resultado->valor[0];
                $aprovechamientoPeriodo=  $registro->porcentaje;
            }
        }
        else
            var_dump($resultado->mensajeError);
        $criteriosSeleccionActual->tipoReporte = TipoReporte::CAPACITACION_INICIADA;
        $resultado = $repositorio->consultarResultados($this->usuario,$criteriosSeleccionActual);
        if($resultado->correcto())
        {
            if(count($resultado->valor)>0)
            {
                $registro = $resultado->valor[0];
                $aprovechamientoActual = $registro->porcentajeAvance;
            }
        }
        else
            var_dump($resultado->mensajeError);
       
                
                
//                 $criteriosSeleccionActual->fechaFinal="";
//                 $criteriosSeleccionActual->fechaInicial ="";
                
                //var_dump($criteriosSeleccionActual);
                //$fechaAnterior = substr($resultado->valor->fecha,0,10);
//                 $resultado = $repositorio->consultarResultados($this->usuario, $criteriosSeleccionActual);
//                 if($resultado->correcto())
//                 {
//                     if(count($resultado->valor)>0)
//                     {
//         $registro = $resultado->valor[0];
//         $avanceActual=  $registro->porcentajeAvance;
//         $aprovechamientoActual= $registro->porcentaje;
        
        $porcentajes = array();
        array_push($porcentajes,(object)["nombre"=>"Avance", "periodo"=> $avancePeriodo, "actual" =>$avanceActual]);
        array_push($porcentajes,(object)["nombre"=>"Aprovechamiento", "periodo"=> $aprovechamientoPeriodo, "actual" =>$aprovechamientoActual]);
        
        //var_dump($porcentajes);
    
        $image = $this->graficaComparativoAvanceAprovechamiento("",'',"Este mes","Periodo seleccionado",$porcentajes,"nombre","actual","periodo",$colores,false,100);
        if($image!='')
            $this->Image($image,$pdfWidth/2 -$chartWidth/2 ,40, $chartWidth);
//                     }
//                 }
            
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
    
    
    private function introduccion()
    {
        $this->AddPage();
        $this->SetY(25);
        $this->subtitulo("Introducción");
        $this->Ln();
        
        $tamanoLinea = 6;
        $borde = 0;
        $this->SetFont($this->font,'',11);
        $this->SetTextColor(0,0,0);
        $this->Cell(0,$tamanoLinea,$this->texto('El equipo de trabajo de Handel Consultoría agradece su interés en mantener a su personal capacitado, es para nosotros'),$borde,1,'FJ');
        $this->Cell(0,$tamanoLinea,$this->texto('motivo de orgullo el poder apoyarle en este proceso, como sabes una capacitación eficiente coadyuvará en un mejor entorno de'),$borde,1,'FJ');
        $this->Cell(0,$tamanoLinea,$this->texto('trabajo con personal enfocado en sus actividades y desempeñándose de acuerdo a el perfil que se considera deseable de acuerdo'),$borde,1,'FJ');
        $this->Cell(0,$tamanoLinea,$this->texto('al puesto. Hemos desarrollado cada capacitación en base a los requerimientos de la certificación OEA (Operador Económico'),$borde,1,'FJ');
        $this->Cell(0,$tamanoLinea,$this->texto('Autorizado) y CTPAT (Customs Trade Partnership Against Terrorism) ponderando siempre el sentido humano. Cada usuario recibe'),$borde,1,'FJ');
        $this->Cell(0,$tamanoLinea,$this->texto('una capacitación personalizada en base a funciones específicas y en ese sentido es también evaluado individualmente,'),$borde,1,'FJ');
        $this->Cell(0,$tamanoLinea,$this->texto('agradecemos que en caso de que un usuario cambie sus actividades contacta a tu especialista asignado en Handel a fin de verificar'),$borde,1,'FJ');
        $this->Cell(0,$tamanoLinea,$this->texto('que el usuario continúa teniendo vigente su formación. Aun y cuando hemos pensado en todos los detalles para hacer de CAVI un'),$borde,1,'FJ');
        $this->Cell(0,$tamanoLinea,$this->texto('entorno intuitivo y fácil de usar, reconocemos que la mejor forma de mejorar es mediante una visión de comunidad, por ello'),$borde,1,'FJ');
        $this->Cell(0,$tamanoLinea,$this->texto('estaremos atentos a tus sugerencias de mejora; buscamos que CAVI sea una herramienta de productividad que facilite la formación'),$borde,1,'FJ');
        $this->Cell(0,$tamanoLinea,$this->texto('en un entorno profesional, intuitivo y entretenido.'),$borde,1,'L');
        $this->Ln();
        
        $this->SetFont($this->font,'B',11);
        $this->Cell(0,$tamanoLinea,$this->texto('¡Gracias por tu interés y cooperación!'),$borde,1,'R');
        $this->Ln();
        $this->SetTextColor(173,47,23);
        $this->Cell(0,$tamanoLinea,$this->texto('El equipo de trabajo de Handel Consultoría'),$borde,1,'R');
        
            
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
    
    
    private function resumenCapacitaciones()
    {
        $this->AddPage();
        $this->SetY(25);
        $this->subtitulo("Resumen de capacitaciones del periodo");
        $this->SetY(35);
        
        $this->fontSizes = array(10, 10, 10, 10, 10, 10);
        $this->fontWeights = array("B","B","B","B","B","B");
        $this->aligns = array("C","C","C","C","C","C");
        $this->widths = array(15, 90, 34.25, 34.25, 34.25, 34.25);
        $this->textColors = array("#ffffff","#ffffff","#ffffff","#ffffff","#ffffff","#ffffff");
        $this->borders = array(1,1,1,1,1,1,1);
        $this->borderColors = array("#afb2b0","#afb2b0","#afb2b0","#afb2b0","#afb2b0","#afb2b0");
        $this->backgroundColors = array("#718147","#718147","#718147","#718147","#718147","#718147");
        $this->Row2(array("Item",$this->texto("Capacitación"),"Usuarios inscritos","Usuarios con aprovechamiento 100%","Usuarios con avance 100%","Aprovechamiento promedio"),4);
        
        $this->fontWeights = array("B","","","","","");
        $this->aligns = array("C","L","C","C","C","C");
        $this->textColors = array("#000000","#000000","#000000","#000000","#000000","#000000");
        
        $repositorio = new CursosRepositorio($this->conexion);
        $resultado = $repositorio->consultarResumenCapacitaciones($this->usuario, $this->criteriosSeleccion);
        if($resultado->correcto())
        {
            $registros = $resultado->valor;
            
            //var_dump($registros);
            
            for($i = 0; $i < count($registros); $i++)
            {
                $color = "";
                if($i%2==0)
                    $color = "#ffffff";
                else
                    $color = "#f5f5f5";
                $this->backgroundColors = array("#e6e6e6",$color,$color,$color,$color,$color);
                $registro = $registros[$i];
                $criteriosSeleccionCapacitacion =  clone $this->criteriosSeleccion;
                $criteriosSeleccionCapacitacion->cursoId = $registro->id;
                $criteriosSeleccionCapacitacion->tipoReporte = TipoReporte::CAPACITACION_INICIADA;
                $resultado = $repositorio->consultarResultadosUsuarios($this->usuario, $criteriosSeleccionCapacitacion);
                
                if($resultado->correcto())
                {
                    $usuarios = $resultado->valor;
                    $usuariosAvance100 = 0;
                    $usuariosAprovechamiento100 = 0;
                    $registro->porcentaje = 0;
                    $suma = 0;
                    for ($j = 0; $j < count($usuarios); $j++) 
                    {
                        if($usuarios[$j]->porcentajeAvance==100)
                            $usuariosAvance100++;
                        if($usuarios[$j]->porcentaje==100)
                            $usuariosAprovechamiento100++;
                        $suma+=$usuarios[$j]->porcentaje;
                    }
                    $registro->usuariosAvance100 = $usuariosAvance100;
                    $registro->usuariosAprovechamiento100 = $usuariosAprovechamiento100;
                    if(count($usuarios)!=0)
                        $registro->porcentaje = $suma / count($usuarios);
                    
                    Porcentaje::formatearPorcentaje($registro, "porcentaje");
                }
                else 
                    var_dump($resultado->mensajeError);
              
                
                
                $id = $i+1;
                $this->Row2(array($id,$this->texto($registro->titulo),$this->texto($registro->numeroUsuariosInscritos),$this->texto($registro->usuariosAprovechamiento100),$this->texto($registro->usuariosAvance100),$registro->porcentaje."%"),8);
            }
        }
        
    }
    
    private function mejoresAprovechamiento()
    {
        $this->AddPage();
        $this->SetY(25);
        $this->subtitulo("Top 10: Los mejores en Aprovechamiento del periodo");
        $this->SetY(35);
        
        $this->fontSizes = array(10, 10, 10, 10, 10,10);
        $this->fontWeights = array("B","B","B","B","B","B");
        $this->aligns = array("C","C","C","C","C","C");
        $this->widths = array(15, 56, 56, 52, 34, 34);
        $this->textColors = array("#ffffff","#ffffff","#ffffff","#ffffff","#ffffff","#ffffff");
        $this->borders = array(1,1,1,1,1,1);
        $this->borderColors = array("#afb2b0","#afb2b0","#afb2b0","#afb2b0","#afb2b0","#afb2b0");
        $this->backgroundColors = array("#718147","#718147","#718147","#718147","#718147","#718147");
        $this->Row2(array("Item","Nombre","Apellido","Departamento","Avance","Aprovechamiento"),4);
        
        $this->fontWeights = array("B","","","","","");
        $this->aligns = array("C","L","L","L","C","C");
        $this->textColors = array("#000000","#000000","#000000","#000000","#000000","#000000");
       
        $repositorio = new CursosRepositorio($this->conexion);
        $resultado = $repositorio->consultarResultadosUsuarios($this->usuario, $this->criteriosSeleccion);
        if($resultado->correcto())
        {
            $registros = $resultado->valor;
            
            usort($registros, array("PDF", "compartarPorcentaje"));
            $limite = 10;
            
            for($i = 0; $i < count($registros) && $i < $limite; $i++)
            {
                $registro = $registros[$i];
                if($registro->porcentaje>=80)
                {
                    $color = "";
                    if($i%2==0)
                        $color = "#ffffff";
                    else
                        $color = "#f5f5f5";
                    $this->backgroundColors = array("#e6e6e6",$color,$color,$color,$color,$color);
                    
                    $id = $i+1;
                    $this->Row2(array($id,$this->texto($registro->nombre),$this->texto($registro->apellido),$this->texto($registro->departamentoNombre),$registro->porcentajeAvance, $registro->porcentaje),8);
                    
                }
            }
        }
     
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
        $filename ="../reportes_capacitaciones/";
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


