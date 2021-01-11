<?php
use php\clases\AdministradorConexion;
use php\modelos\Resultado;
use php\repositorios\MinutasRepositorio;


require('../vendor/fpdf181/fpdf.php');
include '../clases/Utilidades.php';
include '../clases/AdministradorConexion.php';
include '../repositorios/MinutasRepositorio.php';

abstract class PDF extends FPDF
{
    protected $font = "Helvetica";
    protected $minuta;
    protected $margen = 5;
    
    public function __construct()
    {
        parent::__construct("L","mm","A4");
    }
    
    function setMinuta($minuta)
    {
        $this->minuta = $minuta;
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
        
        $fecha = substr($this->minuta->fechaAlta,0,10);
        list($dia, $mes, $ano) = explode("/", $fecha);
        $fechaInicio.=$dia."/".$mes."/".$ano;
        $id = $this->minuta->id;
        
        $this->SetTextColor(0,0,0);
        $this->SetY(-10);
        $this->SetX(0);
        $this->SetLeftMargin(5);
        $borde = 0;
        $anchoColumna = ($this->w - ($this->margen * 2)) / 3;
        $this->SetFont($this->font, 'I', 9);
        $this->Cell($anchoColumna, 8, "Fecha de inicio de minuta: $fechaInicio", $borde, 0, 'L');
        $this->Cell($anchoColumna, 8, "https://saha.apps-handel.com/minutas.php", $borde, 0, 'C', false,"https://saha.apps-handel.com/minutas.php?id=$id");
        $this->Cell($anchoColumna, 8, "Hoja ". $this->PageNo().' de {nb}', $borde, 0, 'R');
        
        $this->linea(200, 60, 141, 188, 1);
        
        
    }
    
    
    
    function Header()
    {
        $empresaId = $this->minuta->empresaId;
        $empresaNombre = $this->minuta->empresaNombre;
        $folio = strtoupper($this->calcularFolio());
        //$area = strtoupper($this->inspeccion->areaNombre);
        
        $logoX = 5;
        $logoY = 1;
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
        
        $this->SetTextColor(0,0,0);
        $this->SetY(0);
        $this->SetX(13);
        $this->SetFont($this->font,'I',9);
        $this->Cell(160, 8, $this->texto($empresaNombre), 0, 0, 'L');
        
        //linea 
        $this->linea(8, 60, 141, 188,1);
        
        $fecha = new DateTime();
        $fecha = $fecha->format("d/m/Y H:i");
       
        //$fecha = substr($this->minuta->fechaModificacion,0,16);
        //list($dia, $mes, $ano) = explode("/", $fecha);
        //$fecha.=$dia."/".$mes."/".$ano;
        
        $this->SetX(0);
        $this->SetLeftMargin(5);
        $borde = 0;
        $anchoColumna = ($this->w - ($this->margen * 2)) / 3;
        $this->SetFont($this->font, 'I', 9);
        $this->Cell($anchoColumna, 8, "", $borde, 0, 'L');
        $this->Cell($anchoColumna, 8, "SAHA Tareas", $borde, 0, 'C');
        $this->Cell($anchoColumna, 8, "Reporte actualizado: $fecha", $borde, 0, 'R');
        
       
            
          
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
        $folio ="SAHATareas". $this->minuta->id;
        
       /* $fecha = substr($this->minuta->fechaAlta,0,10);
        list($dia, $mes, $ano) = explode("/", $fecha);
        $folio.=$dia.$mes.$ano;*/
        /*$folio.=$this->minuta->empresaNombreCorto;
        $folio.=$this->minuta->sedeNombreCorto;
        if($this->minuta->tipoAreaId==2)
            $folio.="C";
            else  if($this->minuta->tipoAreaId==3)
                $folio.="E";
                
                $fecha = substr($this->minuta->fechaminuta,0,10);
                list($dia, $mes, $ano) = explode("/", $fecha);
                $folio.=$dia.$mes.$ano;
                
                if($this->minuta->numeroCaja!="")
                    $folio.="C".$this->minuta->numeroCaja ;
                    else
                        $folio.="T".$this->minuta->numeroTractor;
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
            $this->SetX(0);
            $this->SetLeftMargin(5);
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
    
    protected function imprimirPuntos($titulo, $puntos)
    {
        $borde = 0;
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
        for($i = 0 ; $i < count($puntos); $i++)
        {
            $punto = $puntos[$i];
            $descripcion = $punto->id .". " . $punto->descripcion;
            $observaciones = $punto->observaciones;
            
            $this->Ln();
            $this->SetFont($this->font, '', 9);
            $this->Cell($anchoColumna1, 8, $this->texto($descripcion), $borde, 0, 'L');
            
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
            
            $this->Cell($anchoColumna2, 8, $resultado, $borde, 0, 'C');
            $this->SetFont($this->font, '', 9);
            $this->Cell($anchoColumna3, $altoColumna, $this->texto($observaciones), $borde, 0, 'L');
        }
        
        $this->SetDrawColor(0,0,0);
        $y = 173;
        // $this->Line(10, $y, 210-10, $y);
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
    
    public function generar()
    {
        $this->SetFont($this->font,'',20);
        $this->AddPage();
        $this->avance();
        $this->titulo();
        
        $this->SetY(20);
        $this->subtitulo("Datos generales:");
        $this->Ln();
        $this->SetY($this->GetY()+1);
        $this->campo(35,"Fecha de registro: ", substr($this->minuta->fechaAlta,0,10));
        $this->Ln();
        $this->campo(35,"Nombre: ",$this->minuta->titulo);
        $this->Ln();
        $this->campo(35,"Descripción: ",$this->minuta->descripcion);
        $this->Ln();
        $this->Ln();
      
        $this->subtitulo("Participantes:");
        $this->participantes(3,7);
        $this->Ln();
        $this->Ln();
        
        $this->subtitulo("Tareas:");
        $this->Ln();
        $this->Ln();
        $this->tareas();
        $this->Ln();
        
        $this->subtitulo("Acuerdos y Acciones:");
        $this->acuerdos();
        
        $this->Ln();
        $this->firma();
        
        /*
        if(trim($this->minuta->acuerdos)!="")
        {
            $this->Ln();
            $this->subtitulo("Acuerdos y Acciones:");
            $this->acuerdos();
        }*/
        
      
            
    }
    
    private function firma()
    {
        $logoX = 5;
        $logoY = $this->GetY();
        $logoAlto = 10;
        $logo = "../imagenes/world.png";
        $this->Image($logo,$logoX,$logoY,$logoAlto,0,'','');
       
        
        $this->SetTextColor(1, 113, 0);
        
        $this->fontSizes = array(8);
        $this->fontWeights = array("B");
        $this->aligns = array("J");
        $this->widths = array(275);
        $this->textColors = array("#017100");
        $this->borders = array(0);
        $this->backgroundColors = array("#ffffff");
        
        //$this->SetY(0);
        $this->SetX(15);
        $texto = "En Handel Consultoría nos emociona crear herramientas para tu productividad, tenemos también un sincero compromiso con la ecología y nos entusiasma dejar un mundo mejor a las nuevas generaciones, por ello te invitamos a imprimir este reporte solamente si lo consideras indispensable, agradecemos tu colaboración para preservar el ambiente";
        //$this->Cell(160, 8, $this->texto($texto), 0, 0, 'L');
        
        $this->Row2(array($this->texto($texto)),5);
        
    }
    
    private function tareas()
    {
        $borde = 1;
        $this->fontSizes = array(11, 11, 11, 11, 11);
        $this->fontWeights = array("B","B","B","B","B");
        $this->aligns = array("C","C","C","C","C");
        $this->widths = array(15, 162, 50, 30, 30);
        $this->textColors = array("#000000","#000000","#000000","#000000","#000000");
        $this->borders = array(1,1,1,1,1);
        $this->borderColors = array("#afb2b0","#afb2b0","#afb2b0","#afb2b0","#afb2b0");
        $this->backgroundColors = array("#bdc1bf","#bdc1bf","#bdc1bf","#bdc1bf","#bdc1bf");
      //  $this->SetFillColor(189, 193, 191);
        $this->Row2(array("Item","Tarea","Asignado a","Fecha compromiso",$this->texto("Fecha terminación")),5);
        
        $this->fontWeights = array("B","","","","");
        $this->aligns = array("C","L","L","C","C");
       
        for($i = 0; $i < count($this->minuta->tareas); $i++)
        {
            $color = "";
            if($i%2==0)
                $color = "#ffffff";
            else
                $color = "#f5f5f5";
            $this->backgroundColors = array("#e6e6e6",$color,$color,$color,$color);
            $tarea = $this->minuta->tareas[$i];
            $fechaCompromiso = substr($tarea->fechaCompromiso,0,10);
            $fechaTerminacion = substr($tarea->fechaFinalizacion,0,10);
            
            
            $responsables = "";
            for($j = 0; $j < count($tarea->responsables); $j++)
            {
                $responsable = $tarea->responsables[$j];
                $responsables.=$responsable->usuarioNombreCompleto;
                if($j < count($tarea->responsables)-1)
                    $responsables.=", ";
            }
            
            $titulo = $this->texto(trim($tarea->titulo));
            $this->Row2(array($i+1,$titulo,$this->texto($responsables),$fechaCompromiso, $fechaTerminacion),8);
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
    
    private function acuerdos()
    {
        $this->fontWeights = array("","","","","");
        $this->SetX($this->margen);
        $this->SetLeftMargin(5);
        $this->SetY($this->GetY()+7);
        $w = $this->w - ($this->margen*2);
        $y = $this->GetY();
        
        $separator = "\r\n";
        $line = strtok($this->minuta->acuerdos, $separator);
        
        $indice = 1;
        $columna = 0;
       // $maximoElementos = $columnas * $maximoRenglones;
        $altoColumna = 6.5;
        $elementos = 0;
        $page_height = $this->h;
        $borde =0; 
        $this->fontSizes = array(11);
        $this->aligns = array("L");
        $this->widths = array($w);
        if(trim($this->minuta->acuerdos)!="")
        {
            while ($line !== false)
            {
                if(trim($line)!="")
                {
                    $elementos++;
                    
                    $y = $this->GetY();
                    //$space_left=$page_height-($this->GetY()); // space left on page
                    if ( $y > $page_height) {
                        $this->SetLeftMargin(10);
                        $this->AddPage(); // page break
                    }
                
                //$this->participante("$indice. $line",$w, $altoColumna);
                    $this->Row(array("$indice. $line"),$borde,8);
               
                    $indice++;
                }
                $line = strtok( $separator );
                
            }
        }
        else
            $this->Row(array("No se registraron acuerdos"),$borde,8);
    }
    
    private function participantes($columnas,$maximoRenglones)
    {
        $this->SetX($this->margen);
        $this->SetLeftMargin(5);
        $this->SetY($this->GetY()+7);
        $w = $this->w/$columnas - $this->margen;
        $y = $this->GetY();
        
        $separator = "\r\n";
        $line = strtok($this->minuta->participantes, $separator);
        
        $indice = 1;
        $columna = 0;
        $maximoElementos = $columnas * $maximoRenglones;
        $altoColumna = 6.5;
        $elementos = 0;
        
        if(trim($this->minuta->participantes)!="")
        {
            while ($line !== false) 
            {
                $elementos++;
                if($indice <= $maximoElementos)
                {
                    $this->SetX(($columna * $w)  + $this->margen); 
                    $this->participante("$indice. $line",$w, $altoColumna);
                    if($indice % $maximoRenglones==0)
                    {
                       $columna++;
                       $this->SetY($y);
                    }
                    
                    $line = strtok( $separator );
                    $indice++;
                }
                else
                    break;
            }
            if($elementos<$maximoRenglones)
                $alto = $altoColumna * ($elementos-1);
            else
                $alto = $altoColumna * ($maximoRenglones - 1);
        
        }
        else
            $this->participante("No se registraron participantes",$w, $altoColumna);
        
        $this->SetY($y + $alto);
      /*  $this->participante("Alan Hernández Bazán",$w);
        $this->participante("Alan Abisai Hernández Guerrero",$w);
        $this->participante("Alexis Andre Hernández Guerrero",$w);*/
    }
    
    private function participante($nombre,$w, $altoColumna)
    {
        $borde = 0;
       
        //$w = $this->w/3;
        $this->SetFont($this->font,'',11);
        $this->SetTextColor(0, 0, 0);
        $this->MultiCell($w, $altoColumna, $this->texto($nombre), $borde);
    }
    
    public function imprimir()
    {
        $filename ="../reportes_minuta/";
        $filename.=$this->calcularFolio();
        $filename.=".pdf";
        
        //var_dump($this->minuta);
        
        $this->Output($filename,'F');
        
        if (file_exists($filename))
        {
            header('Location:'. $filename);
        }
    }
    
    function avance()
    {
        $borde = 0;
        $avance = $this->minuta->porcentaje;
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
        $this->Cell(0,0,$this->texto("MINUTA DE REUNIÓN"),0,2,'C');
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
        $inspectorNombre = strtoupper($this->minuta->inspectorNombre);
        //$fechaInicio = $this->formatoFecha($this->minuta->fechaminuta);
        //$fechaFinalizacion= $this->formatoFecha($this->minuta->fechaFinalizacion);
        
        $fechaInicio = $this->minuta->fechaminuta;
        $fechaFinalizacion= $this->minuta->fechaFinalizacion;
        
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
        $this->Cell($ancho3, 8, $this->texto($this->minuta->turnoInicio), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "2.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, "Destino:", $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->minuta->destino), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "3.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Número de orden:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->minuta->numeroOrden), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "4.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Piezas:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->minuta->piezas), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "5.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Bultos:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->minuta->bultos), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "6.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Peso:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->minuta->peso), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "7.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Otras mercancias:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->minuta->otrasMercancias), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "8.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Manifiesto:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->minuta->manifiesto), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "9.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Sello colocado:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->minuta->selloColocado), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "10.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Inspector de cierre de embarque:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->minuta->inspectorTerminaNombre), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "11.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Cierre de embarque en turno:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->minuta->turnoFin), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "12.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Factura:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->minuta->factura), $borde, 0, 'L' );
        
       
    }
    
//     function imprimirminutaTractor()
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
//         for($i = 0 ; $i < count($this->minuta->puntos1); $i++)
//         {
//             $punto = $this->minuta->puntos1[$i];
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
    
//     function imprimirminutaContenedor()
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
        
//         for($i = 0 ; $i < count($this->minuta->puntos2); $i++)
//         {
//             $punto = $this->minuta->puntos2[$i];
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
        for($i = 0 ; $i < count($this->minuta->puntos); $i++)
        {
            $punto = $this->minuta->puntos[$i];;
            $seccion = $this->crearSeccionFotos($this->minuta->id,$punto);
            if(count($seccion->fotos)>0)
                array_push($seccionesFotos,$seccion);
        }
        
//         for($i = 0 ; $i < count($this->minuta->puntos2); $i++)
//         {
//             $punto = $this->minuta->puntos2[$i];;
//             $seccion = $this->crearSeccionFotos($this->minuta->id,$punto);
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
    
    function agregarFoto($minutaId, $nombreArchivo ,$titulo,&$fotos)
    {
        $archivo = "../fotos_minutaes/".$minutaId ."_" . $nombreArchivo.".jpg";
        if (file_exists($archivo))
        {
            $foto= (object) [
                'titulo' => $titulo,
                'archivo' => $archivo
            ];
            array_push($fotos,$foto);
        }
    }
    
    function imprimirFotosHallazgos()
    {
      
        
        $fotos = array();
        $this->agregarFoto($this->minuta->id,"sello","SELLO",$fotos);
        $this->agregarFoto($this->minuta->id,"sellocaja","SELLO CAJA",$fotos);
        $this->agregarFoto($this->minuta->id,"placatractor","PLACA TRACTOR",$fotos);
        $this->agregarFoto($this->minuta->id,"placacontenedor","PLACA CONTENEDOR",$fotos);
        $this->agregarFoto($this->minuta->id,"licchofer","LICENCIA CHOFER",$fotos);
        
        $this->agregarFoto($this->minuta->id,"cajavacia","CAJA VACIA",$fotos);
        $this->agregarFoto($this->minuta->id,"cajafinal","CAJA FINAL",$fotos);
        $this->agregarFoto($this->minuta->id,"cajacerrada","CAJA CERRADA",$fotos);
        
        if(count($fotos)>0)
        {
            $this->AddPage();
          
            
            //var_dump($fotos);
            
            
            $borde = 0;
            
            $anchoFoto = 50;
           // $altoFoto = $anchoFoto * 40 / 30;
            $altoFoto = $anchoFoto * 0.75;  
            $separacionX =  5;
            $separacionY = 20;
            
            $yFotos = $separacionY + 15;
            
            $xFoto = 25;
            
           
            
          
            for($i = 0 ; $i < count($fotos); $i++)
            {
                $foto = $fotos[$i];
                 $this->SetXY($xFoto, $yFotos - $separacionY);
                 $this->SetLeftMargin(10);
                 $this->SetFont($this->font, 'B', 13);
                 $this->Ln();
                 $this->SetXY($xFoto, $yFotos - $separacionY +10);
                 $this->SetFillColor(242, 242, 242);
                 $this->Cell($anchoFoto,8,$this->texto($foto->titulo),$borde,2,'C');
                 $this->correctImageOrientation($foto->archivo);
                 $this->Image($foto->archivo,$xFoto,$yFotos,$anchoFoto,$altoFoto);
                 $xFoto+=$separacionX + $anchoFoto;
                 if(($i+1)%3==0)
                 {
                     $xFoto = 25;
                     $yFotos+=$altoFoto +15;
                 }
            }
        }
        
       
//         $borde = 0;
        
//         $anchoFoto = 60;
//         //$altoFoto = $anchoFoto * 40 / 30;
//         $altoFoto = $anchoFoto * 0.75;  
//         $separacionX =  25;
//         $separacionY = 20;
        
//         $yFotos = $separacionY + 15;
        
//         $xFoto = 25;
        
        //$this->AddPage();
        
        $fotos = array();
        $this->agregarFoto($this->minuta->id,"firma_chofer","FIRMA CHOFER",$fotos);
        $this->agregarFoto($this->minuta->id,"firma_inspector","FIRMA INSPECTOR",$fotos);
        if($this->minuta->inspectorAleatorioId!=null)
        {
            //$nombre = $this->minuta->inspectorAleatorioNombre;
            $nombre="";
            $this->agregarFoto($this->minuta->id,"firma_aleatoria","INSPECTOR ALEATORIO " .$nombre ,$fotos);
        }
        if(count($fotos)>0)
        {
            $this->AddPage();
            
            $borde = 0;
            
            $anchoFoto = 50;
            // $altoFoto = $anchoFoto * 40 / 30;
            $altoFoto = $anchoFoto * 0.75;
            $separacionX =  5;
            $separacionY = 20;
            
            $yFotos = $separacionY + 15;
            
            $xFoto = 25;
            
            for($i = 0 ; $i < count($fotos); $i++)
            {
    //             $foto = $fotos[$i];
    //             $this->SetXY($xFoto, $yFotos - $separacionY);
    //             $this->SetLeftMargin(10);
    //             $this->SetFont($this->font, 'B', 13);
    //             $this->Ln();
    //             $this->SetXY($xFoto, $yFotos - $separacionY +10);
    //             $this->SetFillColor(242, 242, 242);
    //             $this->Cell($anchoFoto,8,$this->texto($foto->titulo),$borde,2,'C');
    //             $this->correctImageOrientation($foto->archivo);
    //             $this->Image($foto->archivo,$xFoto,$yFotos,$anchoFoto,$altoFoto);
    //             $xFoto+=$separacionX + $anchoFoto;
    //             if(($i+1)%3==0)
    //             {
    //                 $xFoto = 25;
    //                 $yFotos+=$altoFoto +15;
    //             }
                $foto = $fotos[$i];
                $this->SetXY($xFoto, $yFotos - $separacionY);
                $this->SetLeftMargin(10);
                $this->SetFont($this->font, 'B', 13);
                $this->Ln();
                $this->SetXY($xFoto, $yFotos - $separacionY +$altoFoto + 20);
                $this->SetFillColor(242, 242, 242);
                $this->Cell($anchoFoto,8,$this->texto($foto->titulo),$borde,2,'C');
                $this->correctImageOrientation($foto->archivo);
                $this->Image($foto->archivo,$xFoto,$yFotos,$anchoFoto,$altoFoto);
                
                $this->SetLineWidth(0.5);
                $this->SetDrawColor(0,0,0);
                $y = $altoFoto + 30;
                $this->Line($xFoto, $y, $xFoto + $anchoFoto, $y);
                
                $xFoto+=$separacionX + $anchoFoto;
                if(($i+1)%3==0)
                {
                    $xFoto = 25;
                    $yFotos+=$altoFoto +15;
                }
            }
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
    
    function crearSeccionFotos($minutaId, $punto)
    {
        $fotos = array();
        $foto = "../fotos_minutaes/".$minutaId ."_" . $punto->id ."_1.jpg";
        if (file_exists($foto))
            array_push($fotos,$foto);
            $foto = "../fotos_minutaes/".$minutaId ."_" . $punto->id ."_2.jpg";
            if (file_exists($foto))
                array_push($fotos,$foto);
                $foto = "../fotos_minutaes/".$minutaId ."_" . $punto->id ."_3.jpg";
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
            $punto = $this->getPunto($id, $this->minuta->puntos);
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



class ReporteMinuta extends PDF
{
    protected function imprimirContenido()
    {
        $transportista = strtoupper($this->minuta->transportista);
        $chofer = strtoupper($this->minuta->chofer);
        $numeroTractor = $this->minuta->numeroTractor;
        $numeroCaja = $this->minuta->numeroCaja;
        $colorTractor = strtoupper($this->minuta->colorTractor);
        $colorCaja = strtoupper($this->minuta->colorCaja);
        $numeroContenedor = $this->minuta->numeroContenedor;
        $tipoCaja = strtoupper($this->minuta->tipoCaja);
        $sello =  $this->minuta->sello;
        $selloViajero =  $this->minuta->selloViajero;
        $alto = $this->minuta->alto;
        $ancho = $this->minuta->ancho;
        $profundidad = $this->minuta->profundidad;
        $placasTractor =  $this->minuta->placasTractor;
        $placasCaja =  $this->minuta->placasCaja;
        
        if($alto=="")
            $alto = "-";
            
        if($ancho=="")
            $ancho = "-";
        
        if($profundidad=="")
            $profundidad = "-";
            
        $borde = 0;
        $this->SetLeftMargin(10);
        $this->SetFont($this->font, 'B', 13);
        $this->Ln();
        $this->SetFillColor(242, 242, 242);
        $this->Cell(0,8,$this->texto("INFORMACIÓN DE TRANSPORTE"),$borde,2,'C',1);
        
        $this->SetDrawColor(0,0,0);
        $y = 82;
        //$this->Line(10, $y, 210-10, $y);
        
        $this->SetLeftMargin(20);
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(30, 6, "Transportista", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 6, $this->texto($transportista), $borde, 0, 'L');
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(30, 6, "Chofer", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 6, $this->texto($chofer), $borde, 0, 'L');
        
        $this->SetLeftMargin(10);
        $this->Ln();
        $this->SetFont($this->font, 'B', 11);
        
        $this->Cell(0,8,$this->texto("Vehículo"),$borde,1,'C');
        $this->SetDrawColor(191,191,191);
        $y = 87;
        $this->Line(10, $y, 210-10, $y);
        
        
        $this->SetLeftMargin(20);
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, "No. Tractor:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($numeroTractor), $borde, 0, 'L');
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, "No. Caja:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($numeroCaja), $borde, 0, 'L');
        
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, "Placas Tractor:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($placasTractor), $borde, 0, 'L');
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, "Placas Caja:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($placasCaja), $borde, 0, 'L');
        
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, "Color Tractor:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($colorTractor), $borde, 0, 'L');
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, "Color Caja:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($colorCaja), $borde, 0, 'L');
        
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, "No. Contenedor:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($numeroContenedor), $borde, 0, 'L');
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, "Tipo Caja:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($tipoCaja), $borde, 0, 'L');
        
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, "Sello:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($sello), $borde, 0, 'L');
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, "Sello viajero:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($selloViajero), $borde, 0, 'L');
        
        //ORDEN -FACTURA
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, $this->texto("Número de orden:"), $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($this->minuta->numeroOrden), $borde, 0, 'L');
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, "Factura:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($this->minuta->factura), $borde, 0, 'L');
        
        //INSPECION ALEATORIA - INSPECTORA LEAORIO
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, $this->texto("Inspección aleatoria:"), $borde, 0, 'L');
        $minutaAleatorio = $this->minuta->inspectorAleatorioId==null?"NO":"SI";
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($minutaAleatorio), $borde, 0, 'L');
      
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, $this->texto("Inspector aleatorio:"), $borde, 0, 'L');
        $inspectorAleatorio = $this->minuta->inspectorAleatorioNombre==""?"NA":$this->minuta->inspectorAleatorioNombre;
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($inspectorAleatorio), $borde, 0, 'L');
        
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(110, 8, $this->texto("La caja o contenedor está libre de contaminantes agrícolas:"), $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto($this->minuta->tieneSelloImpreso), $borde, 0, 'L');
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(110, 8, $this->texto("La unidad está libre de contaminantes agrícolas:"), $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto($this->minuta->cajaLibreObjetosOrganicos), $borde, 0, 'L');
        
        $this->SetLeftMargin(10);
        $this->SetFont($this->font, 'B', 13);
        $this->Ln();
        $this->SetFillColor(242, 242, 242);
        $this->Cell(0,8,$this->texto("DIMENSIONES DEL CONTENEDOR"),$borde,2,'C',1);
        
        $this->SetLeftMargin(20);
        //$this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(28.33, 8, "Alto:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(28.33, 8, $this->texto($alto), $borde, 0, 'L');
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(28.33, 8, "Ancho:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(28.33, 8, $this->texto($ancho), $borde, 0, 'L');
        
        //$this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(28.33, 8, "Profundidad:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(28.33, 8, $this->texto($profundidad), $borde, 0, 'L');
        
        $this->SetDrawColor(0,0,0);
        $y = 144;
        //$this->Line(10, $y, 210-10, $y);
        
        $this->Ln();
        $this->SetFont($this->font, 'I', 8);
        $leyenda = "Las medidas interiores del contenedor no se muestran cuando el contenedor se encontraba sellado al momento de hacer la inspección";
        $this->Cell(170, 8, $this->texto($leyenda), $borde, 0, 'C');
        
        $this->imprimirPuntosminuta();
    }
    
    
   
    
   
    
}


class ReporteFabrica
{
    public function crear()
    {
        $reporte = new ReporteMinuta();
        return $reporte;
    }
}


$administrador_conexion = new AdministradorConexion();
try
{
    $conexion = $administrador_conexion->abrir();
    if($conexion)
    {
        $repositorio = new MinutasRepositorio($conexion);
        $minutaId = REQUEST('minutaId');
        
        $llaves= (object) [
            'id' =>  $minutaId
        ];
        
        $resultado = $repositorio->consultarPorLlaves($llaves,true);
        
        if($resultado->correcto())
        {
            $minuta = $resultado->valor;
            $reporteFabrica = new ReporteFabrica();
            $reporte = $reporteFabrica->crear();
            if($reporte!=null)
            {
                $reporte->setMinuta($minuta);
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


