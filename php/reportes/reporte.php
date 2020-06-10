<?php
use php\clases\AdministradorConexion;
use php\repositorios\InspeccionesRepositorio;
use php\modelos\Resultado;


require('../vendor/fpdf181/fpdf.php');
include '../clases/Utilidades.php';
include '../clases/AdministradorConexion.php';
include '../repositorios/InspeccionesRepositorio.php';
abstract class PDF extends FPDF
{
    protected $font = "Helvetica";
    protected $inspeccion;
    
    function setInspeccion($inspeccion)
    {
        $this->inspeccion = $inspeccion;
    }
    
    function Footer()
    {
        $borde = 0;
        $this->SetFont($this->font, '', 9);
        $this->SetY(-10);
        $this->SetTextColor(0,0,0);
        $this->Cell(80, 8, $this->texto("Inspección realizada mediante App 10 y 7"), $borde, 0, 'C');
        $this->SetTextColor(0,0,127);
        $this->Cell(80, 8 ,'http://www.handel-sce.com/',$borde,'','',false, "http://www.handel-sce.com/");
        $this->SetTextColor(0,0,0);
        $this->Cell(30, 8,"Hoja ". $this->PageNo().' de {nb}', $borde, 0, 'C');
        
    }
    
    
    
    function Header()
    {
        $empresaId = $this->inspeccion->empresaId;
        $empresaNombre = $this->inspeccion->empresaNombre;
        $folio = strtoupper($this->calcularFolio());
        $area = strtoupper($this->inspeccion->areaNombre);
        $fecha= $this->inspeccion->fechaInspeccion;
        
        $this->SetLineWidth(1);
        $this->SetDrawColor(102,102,255);
        $y = 20;
        $this->Line(10, $y, 210-10, $y);
        
        $folio = strtoupper($this->calcularFolio());
        
        $logo = "../logos_empresas/logo$empresaId.png";
        if (file_exists($logo))
            $this->Image($logo,8,5,20,0,'','');
        else 
        {
            $this->SetX(10);
            $this->SetFont($this->font,'I',10);
            $this->Cell(160, 8, $this->texto($empresaNombre), 0, 0, 'L');
        }
        
        $this->SetY(12);
        $this->SetX(40);
        
        $borde = 0;
        $altoLinea = 7;
        $this->SetLeftMargin(20);
        $this->SetFont($this->font,'I',10);
        $this->SetTextColor(130,130,130);
        $this->Cell(160, $altoLinea, $this->texto("Folio :  " . $folio), $borde, 0, 'R');
        $this->SetFont($this->font,'B',13);
        $this->SetTextColor(63,103,151);
        //$this->Cell(20, $altoLinea, $this->texto(" " .  $this->PageNo()), $borde, 0, 'L');
        
//         $this->SetLineWidth(0.5 );
//         $this->SetDrawColor(118, 159, 209);
//         $x = 180;
//         $this->Line($x, 13, $x, 18);
    }
    
    private function calcularFolio()
    {
        $folio ="";
        $folio.=$this->inspeccion->empresaNombreCorto;
        $folio.=$this->inspeccion->sedeNombreCorto;
        if($this->inspeccion->tipoAreaId==2)
            $folio.="C";
            else  if($this->inspeccion->tipoAreaId==3)
                $folio.="E";
                
                $fecha = substr($this->inspeccion->fechaInspeccion,0,10);
                list($dia, $mes, $ano) = explode("/", $fecha);
                $folio.=$dia.$mes.$ano;
                
                if($this->inspeccion->numeroCaja!="")
                    $folio.="C".$this->inspeccion->numeroCaja ;
                    else
                        $folio.="T".$this->inspeccion->numeroTractor;
                        return $folio;
    }
    
    abstract protected function imprimirContenido();
    abstract protected function imprimirPuntosInspeccion();
    
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
    
    function CheckPageBreak($h)
    {
        //If the height h would cause an overflow, add a new page immediately
        if($this->GetY()+$h>$this->PageBreakTrigger)
        {
            $this->AddPage($this->CurOrientation);
            $this->SetY(22);
            $this->SetX(0);
            $this->SetLeftMargin(10);
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
        $this->imprimirEncabezado();
        $this->imprimirTitulo();
        $this->imprimirSubtitulo();
        $this->imprimirInspector();
        
        $this->imprimirContenido();
        //$this->imprimirPuntosInspeccion();
        
//contenido
//         if($this->inspeccion->tipoInspeccionId==17)
//             $this->imprimirInformacionTransporte17();
//         else  if($this->inspeccion->tipoInspeccionId==7)
//             $this->imprimirInformacionTransporte7();
//         else  if($this->inspeccion->tipoInspeccionId==10)
//             $this->imprimirInformacionTransporte10();
                    
//puntos                    
//         if(count($this->inspeccion->puntos1)>0)
//              $this->imprimirInspeccionTractor();
        
//         if(count($this->inspeccion->puntos2)>0)
//         {
//             $this->AddPage();
//             $this->imprimirInspeccionContenedor();
//         }
        
//embarque
//         if($this->inspeccion->tipoInspeccionId==7)
//         {
//             $this->AddPage();
//             $this->imprimirInformacionEmbarque();
//         }
     
        $this->imprimirFotos();
       
        $this->imprimirFotosHallazgos();
                        
    }
    
    public function imprimir()
    {
        $filename ="../reportes_inspeccion/";
        $filename.=$this->calcularFolio();
        $filename.=".pdf";
        
        //var_dump($this->inspeccion);
        
        $this->Output($filename,'F');
        
        if (file_exists($filename))
        {
            header('Location:'. $filename);
        }
    }
    
    function imprimirEncabezado()
    {
        $empresaId = $this->inspeccion->empresaId;
        $folio = strtoupper($this->calcularFolio());
        $area = strtoupper($this->inspeccion->areaNombre);
        $fecha= $this->inspeccion->fechaInspeccion;
        
//         $logo = "../logos_empresas/logo$empresaId.png";
//         if (file_exists($logo))
//             $this->Image($logo,10,12,40,0,'','http://www.fpdf.org');
//             else
//                 $this->Image("default.png",10,12,40,0,'','http://www.fpdf.org');
        $this->SetLeftMargin(45);
        $this->SetFontSize(11);
        
        $this->Cell(150,8,$this->texto("FOLIO: $folio"),0,1,'R');
        $this->Cell(150,8,$this->texto("AREA: $area"),0,1,'R');
        $this->Cell(150,8,$this->texto("FECHA DE EMBARQUE: $fecha"),0,1,'R');
    }
    
    function imprimirTitulo()
    {
        $this->SetLeftMargin(10);
        $this->SetFont($this->font,'B',14);
        $this->Ln();
        $this->Cell(0,0,$this->texto("INSPECCIÓN DE VEHICULOS DE CARGA"),0,2,'C');
    }
    
    
    function imprimirSubtitulo()
    {
        $entrada_salida= $this->inspeccion->entradaSalida;
        if($entrada_salida=="")
            $entrada_salida="ENTRADA";
            $this->SetFont($this->font  ,'',11);
            $this->Ln();
            $this->Cell(0,10,$this->texto("(".strtoupper($entrada_salida) ." DE UNIDAD)"),0,2,'C');
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
        $inspectorNombre = strtoupper($this->inspeccion->inspectorNombre);
        //$fechaInicio = $this->formatoFecha($this->inspeccion->fechaInspeccion);
        //$fechaFinalizacion= $this->formatoFecha($this->inspeccion->fechaFinalizacion);
        
        $fechaInicio = $this->inspeccion->fechaInspeccion;
        $fechaFinalizacion= $this->inspeccion->fechaFinalizacion;
        
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
    
//     function imprimirInformacionTransporte17()
//     {
//         $transportista = strtoupper($this->inspeccion->transportista);
//         $chofer = strtoupper($this->inspeccion->chofer);
//         $numeroTractor = $this->inspeccion->numeroTractor;
//         $numeroCaja = $this->inspeccion->numeroCaja;
//         $colorTractor = strtoupper($this->inspeccion->colorTractor);
//         $colorCaja = strtoupper($this->inspeccion->colorCaja);
//         $numeroContenedor = $this->inspeccion->numeroContenedor;
//         $tipoCaja = strtoupper($this->inspeccion->tipoCaja);
//         $sello =  $this->inspeccion->sello;
//         $selloViajero =  $this->inspeccion->selloViajero;
//         $alto = $this->inspeccion->alto;
//         $ancho = $this->inspeccion->ancho;
//         $profundidad = $this->inspeccion->profundidad;
//         $placasTractor =  $this->inspeccion->placasTractor;
//         $placasCaja =  $this->inspeccion->placasCaja;
        
//         if($alto=="")
//             $alto = "-";
            
//         if($ancho=="")
//             $ancho = "-";
            
//         if($profundidad=="")
//             $profundidad = "-";
        
//         $borde = 0;
//         $this->SetLeftMargin(10);
//         $this->SetFont($this->font, 'B', 13);
//         $this->Ln();
//         $this->SetFillColor(242, 242, 242);
//         $this->Cell(0,8,$this->texto("INFORMACIÓN DE TRANSPORTE"),$borde,2,'C',1);
        
//         $this->SetDrawColor(0,0,0);
//         $y = 82;
//         //$this->Line(10, $y, 210-10, $y);
        
//         $this->SetLeftMargin(20);
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(30, 6, "Transportista", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(55, 6, $this->texto($transportista), $borde, 0, 'L');
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(30, 6, "Chofer", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(55, 6, $this->texto($chofer), $borde, 0, 'L');
        
//         $this->SetLeftMargin(10);
//         $this->Ln();
//         $this->SetFont($this->font, 'B', 11);
        
//         $this->Cell(0,8,$this->texto("Vehículo "),$borde,1,'C');
//         $this->SetDrawColor(191,191,191);
//         $y = 87;
//         $this->Line(10, $y, 210-10, $y);
        
        
//         $this->SetLeftMargin(20);
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(40, 8, "No. Tractor:", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(45, 8, $this->texto($numeroTractor), $borde, 0, 'L');
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(40, 8, "No. Caja:", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(45, 8, $this->texto($numeroCaja), $borde, 0, 'L');
        
//         $this->Ln();
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(40, 8, "Placas Tractor:", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(45, 8, $this->texto($placasTractor), $borde, 0, 'L');
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(40, 8, "Placas Caja:", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(45, 8, $this->texto($placasCaja), $borde, 0, 'L');
        
//         $this->Ln();
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(40, 8, "Color Tractor:", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(45, 8, $this->texto($colorTractor), $borde, 0, 'L');
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(40, 8, "Color Caja:", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(45, 8, $this->texto($colorCaja), $borde, 0, 'L');
        
//         $this->Ln();
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(40, 8, "No. Contenedor:", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(45, 8, $this->texto($numeroContenedor), $borde, 0, 'L');
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(40, 8, "Tipo Caja:", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(45, 8, $this->texto($tipoCaja), $borde, 0, 'L');
        
//         $this->Ln();
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(40, 8, "Sello:", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(45, 8, $this->texto($sello), $borde, 0, 'L');
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(40, 8, "Sello viajero:", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(45, 8, $this->texto($selloViajero), $borde, 0, 'L');
        
//         //INSPECION ALEATORIA
//         $this->Ln();
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(40, 8, $this->texto("Inspección aleatoria:"), $borde, 0, 'L');
//         $inspeccionAleatorio = $this->inspeccion->inspectorAleatorioId==null?"NO":"SI";
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(45, 8, $this->texto($inspeccionAleatorio), $borde, 0, 'L');
//         //INSPECTORALEAORIO
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(40, 8, $this->texto("Inspector aleatorio:"), $borde, 0, 'L');
//         $inspectorAleatorio = $this->inspeccion->inspectorAleatorioNombre==""?"NA":$this->inspeccion->inspectorAleatorioNombre;
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(45, 8, $this->texto($inspectorAleatorio), $borde, 0, 'L');
        
//         $this->Ln();
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(110, 8, $this->texto("La caja o contenedor está libre de contaminantes agrícolas:"), $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(55, 8, $this->texto($this->inspeccion->tieneSelloImpreso), $borde, 0, 'L');
//         $this->Ln();
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(110, 8, $this->texto("La unidad está libre de contaminantes agrícolas:"), $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(55, 8, $this->texto($this->inspeccion->cajaLibreObjetosOrganicos), $borde, 0, 'L');
        
//         $this->SetLeftMargin(10);
//         $this->SetFont($this->font, 'B', 13);
//         $this->Ln();
//         $this->SetFillColor(242, 242, 242);
//         $this->Cell(0,8,$this->texto("DIMENSIONES DEL CONTENEDOR"),$borde,2,'C',1);
        
//         $this->SetLeftMargin(20);
//         //$this->Ln();
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(28.33, 8, "Alto:", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(28.33, 8, $this->texto($alto), $borde, 0, 'L');
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(28.33, 8, "Ancho:", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(28.33, 8, $this->texto($ancho), $borde, 0, 'L');
        
//         //$this->Ln();
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(28.33, 8, "Profundidad:", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(28.33, 8, $this->texto($profundidad), $borde, 0, 'L');
        
//         $this->SetDrawColor(0,0,0);
//         $y = 144;
//         //$this->Line(10, $y, 210-10, $y);
        
//         $this->Ln();
//         $this->SetFont($this->font, 'I', 8);
//         $leyenda = "Las medidas interiores del contenedor no se muestran cuando el contenedor se encontraba sellado al momento de hacer la inspección";
//         $this->Cell(170, 8, $this->texto($leyenda), $borde, 0, 'C');
                    
                    
//     }
    
//     function imprimirInformacionTransporte7()
//     {
//         $transportista = strtoupper($this->inspeccion->transportista);
//         $chofer = strtoupper($this->inspeccion->chofer);
//         $numeroTractor = $this->inspeccion->numeroTractor;
//         $numeroCaja = $this->inspeccion->numeroCaja;
//         $colorTractor = strtoupper($this->inspeccion->colorTractor);
//         $colorCaja = strtoupper($this->inspeccion->colorCaja);
//         $numeroContenedor = $this->inspeccion->numeroContenedor;
//         $tipoCaja = strtoupper($this->inspeccion->tipoCaja);
//         $sello =  $this->inspeccion->sello;
//         $selloViajero =  $this->inspeccion->selloViajero;
//         $alto = $this->inspeccion->alto;
//         $ancho = $this->inspeccion->ancho;
//         $profundidad = $this->inspeccion->profundidad;
//         $selloColocado = $this->inspeccion->selloColocado;
        
//         if($alto=="")
//             $alto = "-";
            
//         if($ancho=="")
//             $ancho = "-";
            
//         if($profundidad=="")
//             $profundidad = "-";
                    
//         $borde = 0;
//         $this->SetLeftMargin(10);
//         $this->SetFont($this->font, 'B', 13);
//         $this->Ln();
//         $this->SetFillColor(242, 242, 242);
//         $this->Cell(0,8,$this->texto("INFORMACIÓN DE TRANSPORTE"),$borde,2,'C',1);
        
//         $this->SetDrawColor(0,0,0);
//         $y = 82;
//         //$this->Line(10, $y, 210-10, $y);
        
//         $this->SetLeftMargin(20);
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(30, 6, "Transportista", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(55, 6, $this->texto($transportista), $borde, 0, 'L');
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(30, 6, "Chofer", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(55, 6, $this->texto($chofer), $borde, 0, 'L');
        
//         $this->SetLeftMargin(10);
//         $this->Ln();
//         $this->SetFont($this->font, 'B', 11);
        
//         $this->Cell(0,8,$this->texto("Vehículo "),$borde,1,'C');
//         $this->SetDrawColor(191,191,191);
//         $y = 87;
//         $this->Line(10, $y, 210-10, $y);
        
        
//         $this->SetLeftMargin(20);
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(40, 8, "No. Caja:", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(45, 8, $this->texto($numeroCaja), $borde, 0, 'L');
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(40, 8, "Tipo caja:", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(45, 8, $this->texto($tipoCaja), $borde, 0, 'L');
        
//         $this->Ln();
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(40, 8, "Color caja:", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(45, 8, $this->texto($colorCaja), $borde, 0, 'L');
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(40, 8, "Sello colocado:", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(45, 8, $this->texto($selloColocado), $borde, 0, 'L');
        
//         //INSPECION ALEATORIA
//         $this->Ln();
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(40, 8, $this->texto("Inspección aleatoria:"), $borde, 0, 'L');
//         $inspeccionAleatorio = $this->inspeccion->inspectorAleatorioId==null?"NO":"SI";
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(45, 8, $this->texto($inspeccionAleatorio), $borde, 0, 'L');
//         //INSPECTORALEAORIO
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(40, 8, $this->texto("Inspector aleatorio:"), $borde, 0, 'L');
//         $inspectorAleatorio = $this->inspeccion->inspectorAleatorioNombre==""?"NA":$this->inspeccion->inspectorAleatorioNombre;
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(45, 8, $this->texto($inspectorAleatorio), $borde, 0, 'L');
        
//         $this->Ln();
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(110, 8, $this->texto("La tarima tiene impreso el sello de tratamiento de fumigación:"), $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(55, 8, $this->texto($this->inspeccion->tieneSelloImpreso), $borde, 0, 'L');
//         $this->Ln();
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(110, 8, $this->texto("La caja o contenedor está libre de contaminantes agrícolas:"), $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(55, 8, $this->texto($this->inspeccion->cajaLibreObjetosOrganicos), $borde, 0, 'L');
        
//         $this->SetLeftMargin(10);
//         $this->SetFont($this->font, 'B', 13);
//         $this->Ln();
//         $this->SetFillColor(242, 242, 242);
//         $this->Cell(0,8,$this->texto("DIMENSIONES DEL CONTENEDOR"),$borde,2,'C',1);
        
//         $this->SetLeftMargin(20);
//         //$this->Ln();
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(28.33, 8, "Alto:", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(28.33, 8, $this->texto($alto), $borde, 0, 'L');
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(28.33, 8, "Ancho:", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(28.33, 8, $this->texto($ancho), $borde, 0, 'L');
        
//         //$this->Ln();
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(28.33, 8, "Profundidad:", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(28.33, 8, $this->texto($profundidad), $borde, 0, 'L');
        
//         $this->SetDrawColor(0,0,0);
//         $y = 144;
//        // $this->Line(10, $y, 210-10, $y);
        
//         $this->Ln();
//         $this->SetFont($this->font, 'I', 8);
//         $leyenda = "Las medidas interiores del contenedor no se muestran cuando el contenedor se encontraba sellado al momento de hacer la inspección";
//         $this->Cell(170, 8, $this->texto($leyenda), $borde, 0, 'C');
                    
                    
//     }
    
//     function imprimirInformacionTransporte10()
//     {
//         $transportista = strtoupper($this->inspeccion->transportista);
//         $chofer = strtoupper($this->inspeccion->chofer);
//         $numeroTractor = $this->inspeccion->numeroTractor;
//         $numeroCaja = $this->inspeccion->numeroCaja;
//         $colorTractor = strtoupper($this->inspeccion->colorTractor);
//         $colorCaja = strtoupper($this->inspeccion->colorCaja);
//         $numeroContenedor = $this->inspeccion->numeroContenedor;
//         $tipoCaja = strtoupper($this->inspeccion->tipoCaja);
//         $sello =  $this->inspeccion->sello;
//         $selloViajero =  $this->inspeccion->selloViajero;
//         $alto = $this->inspeccion->alto;
//         $ancho = $this->inspeccion->ancho;
//         $profundidad = $this->inspeccion->profundidad;
//         $placasTractor =  $this->inspeccion->placasTractor;
        
//         if($alto=="")
//             $alto = "-";
            
//         if($ancho=="")
//             $ancho = "-";
    
//         if($profundidad=="")
//             $profundidad = "-";
        
//         $borde = 0;
//         $this->SetLeftMargin(10);
//         $this->SetFont($this->font, 'B', 13);
//         $this->Ln();
//         $this->SetFillColor(242, 242, 242);
//         $this->Cell(0,8,$this->texto("INFORMACIÓN DE TRANSPORTE"),$borde,2,'C',1);
        
//         $this->SetDrawColor(0,0,0);
//         $y = 82;
//         //$this->Line(10, $y, 210-10, $y);
        
//         $this->SetLeftMargin(20);
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(30, 6, "Transportista", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(55, 6, $this->texto($transportista), $borde, 0, 'L');
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(30, 6, "Chofer", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(55, 6, $this->texto($chofer), $borde, 0, 'L');
        
//         $this->SetLeftMargin(10);
//         $this->Ln();
//         $this->SetFont($this->font, 'B', 11);
        
//         $this->Cell(0,8,$this->texto("Vehículo "),$borde,1,'C');
//         $this->SetDrawColor(191,191,191);
//         $y = 87;
//         $this->Line(10, $y, 210-10, $y);
        
        
//         $this->SetLeftMargin(20);
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(40, 8, "No. Tractor:", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(45, 8, $this->texto($numeroTractor), $borde, 0, 'L');
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(40, 8, "Sello viajero:", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(45, 8, $this->texto($selloViajero), $borde, 0, 'L');
        
      
        
//         $this->Ln();
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(40, 8, "Placas Tractor:", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(45, 8, $this->texto($placasTractor), $borde, 0, 'L');
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(40, 8, "Color Tractor:", $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(45, 8, $this->texto($colorTractor), $borde, 0, 'L');
        
        
//         //INSPECION ALEATORIA
//         $this->Ln();
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(40, 8, $this->texto("Inspección aleatoria:"), $borde, 0, 'L');
//         $inspeccionAleatorio = $this->inspeccion->inspectorAleatorioId==null?"NO":"SI";
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(45, 8, $this->texto($inspeccionAleatorio), $borde, 0, 'L');
//         //INSPECTORALEAORIO
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(40, 8, $this->texto("Inspector aleatorio:"), $borde, 0, 'L');
//         $inspectorAleatorio = $this->inspeccion->inspectorAleatorioNombre==""?"NA":$this->inspeccion->inspectorAleatorioNombre;
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(45, 8, $this->texto($inspectorAleatorio), $borde, 0, 'L');
        
        
//         $this->Ln();
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell(110, 8, $this->texto("La unidad está libre de contaminantes agrícolas:"), $borde, 0, 'L');
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(55, 8, $this->texto($this->inspeccion->cajaLibreObjetosOrganicos), $borde, 0, 'L');

                    
//     }
    
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
        $this->Cell($ancho3, 8, $this->texto($this->inspeccion->turnoInicio), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "2.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, "Destino:", $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->inspeccion->destino), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "3.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Número de orden:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->inspeccion->numeroOrden), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "4.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Piezas:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->inspeccion->piezas), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "5.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Bultos:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->inspeccion->bultos), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "6.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Peso:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->inspeccion->peso), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "7.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Otras mercancias:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->inspeccion->otrasMercancias), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "8.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Manifiesto:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->inspeccion->manifiesto), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "9.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Sello colocado:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->inspeccion->selloColocado), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "10.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Inspector de cierre de embarque:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->inspeccion->inspectorTerminaNombre), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "11.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Cierre de embarque en turno:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->inspeccion->turnoFin), $borde, 0, 'L' );
        $this->Ln();
        $this->Cell($ancho1, 8, "12.", $borde, 0, 'R' );
        $this->Cell($ancho2, 8, $this->texto("Factura:"), $borde, 0, 'L' );
        $this->Cell($ancho3, 8, $this->texto($this->inspeccion->factura), $borde, 0, 'L' );
        
       
    }
    
//     function imprimirInspeccionTractor()
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
//         for($i = 0 ; $i < count($this->inspeccion->puntos1); $i++)
//         {
//             $punto = $this->inspeccion->puntos1[$i];
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
    
//     function imprimirInspeccionContenedor()
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
        
//         for($i = 0 ; $i < count($this->inspeccion->puntos2); $i++)
//         {
//             $punto = $this->inspeccion->puntos2[$i];
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
        for($i = 0 ; $i < count($this->inspeccion->puntos); $i++)
        {
            $punto = $this->inspeccion->puntos[$i];;
            $seccion = $this->crearSeccionFotos($this->inspeccion->id,$punto);
            if(count($seccion->fotos)>0)
                array_push($seccionesFotos,$seccion);
        }
        
//         for($i = 0 ; $i < count($this->inspeccion->puntos2); $i++)
//         {
//             $punto = $this->inspeccion->puntos2[$i];;
//             $seccion = $this->crearSeccionFotos($this->inspeccion->id,$punto);
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
    
    function agregarFoto($inspeccionId, $nombreArchivo ,$titulo,&$fotos)
    {
        $archivo = "../fotos_inspecciones/".$inspeccionId ."_" . $nombreArchivo.".jpg";
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
        $this->agregarFoto($this->inspeccion->id,"sello","SELLO",$fotos);
        $this->agregarFoto($this->inspeccion->id,"sellocaja","SELLO CAJA",$fotos);
        $this->agregarFoto($this->inspeccion->id,"placatractor","PLACA TRACTOR",$fotos);
        $this->agregarFoto($this->inspeccion->id,"placacontenedor","PLACA CONTENEDOR",$fotos);
        $this->agregarFoto($this->inspeccion->id,"licchofer","LICENCIA CHOFER",$fotos);
        
        $this->agregarFoto($this->inspeccion->id,"cajavacia","CAJA VACIA",$fotos);
        $this->agregarFoto($this->inspeccion->id,"cajafinal","CAJA FINAL",$fotos);
        $this->agregarFoto($this->inspeccion->id,"cajacerrada","CAJA CERRADA",$fotos);
        
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
        $this->agregarFoto($this->inspeccion->id,"firma_chofer","FIRMA CHOFER",$fotos);
        $this->agregarFoto($this->inspeccion->id,"firma_inspector","FIRMA INSPECTOR",$fotos);
        if($this->inspeccion->inspectorAleatorioId!=null)
        {
            //$nombre = $this->inspeccion->inspectorAleatorioNombre;
            $nombre="";
            $this->agregarFoto($this->inspeccion->id,"firma_aleatoria","INSPECTOR ALEATORIO " .$nombre ,$fotos);
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
    
    function crearSeccionFotos($inspeccionId, $punto)
    {
        $fotos = array();
        $foto = "../fotos_inspecciones/".$inspeccionId ."_" . $punto->id ."_1.jpg";
        if (file_exists($foto))
            array_push($fotos,$foto);
            $foto = "../fotos_inspecciones/".$inspeccionId ."_" . $punto->id ."_2.jpg";
            if (file_exists($foto))
                array_push($fotos,$foto);
                $foto = "../fotos_inspecciones/".$inspeccionId ."_" . $punto->id ."_3.jpg";
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
        return iconv('UTF-8', 'windows-1252', $texto);
    }
    
    protected function getPuntos($puntosId)
    {
        $puntos = array();
        for($i=0; $i < count($puntosId); $i++)
        {
            $id = $puntosId[$i];
            $punto = $this->getPunto($id, $this->inspeccion->puntos);
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


class Reporte7 extends PDF
{
    protected function imprimirContenido()
    {
        $transportista = strtoupper($this->inspeccion->transportista);
        $chofer = strtoupper($this->inspeccion->chofer);
        $numeroTractor = $this->inspeccion->numeroTractor;
        $numeroCaja = $this->inspeccion->numeroCaja;
        $colorTractor = strtoupper($this->inspeccion->colorTractor);
        $colorCaja = strtoupper($this->inspeccion->colorCaja);
        $numeroContenedor = $this->inspeccion->numeroContenedor;
        $tipoCaja = strtoupper($this->inspeccion->tipoCaja);
        $sello =  $this->inspeccion->sello;
        $selloViajero =  $this->inspeccion->selloViajero;
        $alto = $this->inspeccion->alto;
        $ancho = $this->inspeccion->ancho;
        $profundidad = $this->inspeccion->profundidad;
        $selloColocado = $this->inspeccion->selloColocado;
        
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
        
        $this->Cell(0,8,$this->texto("Caja / Contenedor "),$borde,1,'C');
        $this->SetDrawColor(191,191,191);
        $y = 87;
        $this->Line(10, $y, 210-10, $y);
        
        
        $this->SetLeftMargin(20);
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, "No. Caja:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($numeroCaja), $borde, 0, 'L');
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(45, 8, "Tipo caja:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(40, 8, $this->texto($tipoCaja), $borde, 0, 'L');
        
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, "Color caja:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($colorCaja), $borde, 0, 'L');
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(45, 8, "Placa caja/contenedor :", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(40, 8, $this->texto($this->inspeccion->placasCaja), $borde, 0, 'L');
        
        //INSPECION ALEATORIA
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, $this->texto("Inspección aleatoria:"), $borde, 0, 'L');
        $inspeccionAleatorio = $this->inspeccion->inspectorAleatorioId==null?"NO":"SI";
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($inspeccionAleatorio), $borde, 0, 'L');
        //INSPECTORALEAORIO
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(45, 8, $this->texto("Inspector aleatorio:"), $borde, 0, 'L');
        $inspectorAleatorio = $this->inspeccion->inspectorAleatorioNombre==""?"NA":$this->inspeccion->inspectorAleatorioNombre;
        $this->SetFont($this->font, '', 10);
        $this->Cell(40, 8, $this->texto($inspectorAleatorio), $borde, 0, 'L');
        
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(110, 8, $this->texto("La tarima tiene impreso el sello de tratamiento de fumigación:"), $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto($this->inspeccion->tieneSelloImpreso), $borde, 0, 'L');
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(110, 8, $this->texto("La caja o contenedor está libre de contaminantes agrícolas:"), $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto($this->inspeccion->cajaLibreObjetosOrganicos), $borde, 0, 'L');
        
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
        // $this->Line(10, $y, 210-10, $y);
        
        $this->Ln();
        $this->SetFont($this->font, 'I', 8);
        $leyenda = "Las medidas interiores del contenedor no se muestran cuando el contenedor se encontraba sellado al momento de hacer la inspección";
        $this->Cell(170, 8, $this->texto($leyenda), $borde, 0, 'C');
        
        
        $this->imprimirPuntosInspeccion();
        
        $this->AddPage();
        $this->imprimirInformacionEmbarque();
        
    }
    
    protected function imprimirPuntosInspeccion() 
    {
       //this->imprimirPuntos("INSPECCIÓN DE TRACTOR",$this->inspeccion->puntos);
       $borde = 0;
       $anchoColumna1 = 50;
       $anchoColumna2 = 30;
       $anchoColumna3 = 110;
       
       $this->SetLeftMargin(10);
       $this->SetFont($this->font, 'B', 13);
       $this->Ln();
       $this->SetFillColor(242, 242, 242);
       $this->Cell(0,8,$this->texto("INSPECCIÓN DE CONTENEDOR"),$borde,2,'C',1);
       
       
       $this->SetFont($this->font, 'B', 10);
       $this->Cell($anchoColumna1, 8, $this->texto("Descripción"), $borde, 0, 'C', 1);
       $this->Cell($anchoColumna2, 8, "Resultado", $borde, 0, 'C', 1);
       $this->Cell($anchoColumna3, 8, "Observaciones", $borde, 0, 'C', 1);
       $this->Ln();
       
       $this->widths = array($anchoColumna1, $anchoColumna2, $anchoColumna3);
       $this->aligns = array("I", "C", "I");
       $this->fontNames = array($this->font, $this->font, $this->font);
       $this->fontWeights =  array('', "B", '');
       $this->fontSizes =  array(9, 10, 9);
       $this->imprimirPuntosTabla($this->inspeccion->puntos,$borde);
    }
}

class Reporte10 extends PDF
{
    
    protected function imprimirContenido()
    {
        $transportista = strtoupper($this->inspeccion->transportista);
        $chofer = strtoupper($this->inspeccion->chofer);
        $numeroTractor = $this->inspeccion->numeroTractor;
        $numeroCaja = $this->inspeccion->numeroCaja;
        $colorTractor = strtoupper($this->inspeccion->colorTractor);
        $colorCaja = strtoupper($this->inspeccion->colorCaja);
        $numeroContenedor = $this->inspeccion->numeroContenedor;
        $tipoCaja = strtoupper($this->inspeccion->tipoCaja);
        $sello =  $this->inspeccion->sello;
        $selloViajero =  $this->inspeccion->selloViajero;
        $alto = $this->inspeccion->alto;
        $ancho = $this->inspeccion->ancho;
        $profundidad = $this->inspeccion->profundidad;
        $placasTractor =  $this->inspeccion->placasTractor;
        
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
        $this->Cell(40, 8, "Sello viajero:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($selloViajero), $borde, 0, 'L');
        
        
        
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, "Placas Tractor:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($placasTractor), $borde, 0, 'L');
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, "Color Tractor:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($colorTractor), $borde, 0, 'L');
        
        //ORDEN -FACTURA
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, $this->texto("Número de orden:"), $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($this->inspeccion->numeroOrden), $borde, 0, 'L');
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, "Factura:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($this->inspeccion->factura), $borde, 0, 'L');
        
        //INSPECION ALEATORIA - INSPECTOR ALEAORIO
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, $this->texto("Inspección aleatoria:"), $borde, 0, 'L');
        $inspeccionAleatorio = $this->inspeccion->inspectorAleatorioId==null?"NO":"SI";
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($inspeccionAleatorio), $borde, 0, 'L');
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, $this->texto("Inspector aleatorio:"), $borde, 0, 'L');
        $inspectorAleatorio = $this->inspeccion->inspectorAleatorioNombre==""?"NA":$this->inspeccion->inspectorAleatorioNombre;
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($inspectorAleatorio), $borde, 0, 'L');
        
        
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(110, 8, $this->texto("La unidad está libre de contaminantes agrícolas:"), $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto($this->inspeccion->cajaLibreObjetosOrganicos), $borde, 0, 'L');
        
        $this->imprimirPuntosInspeccion();
    }
    
    protected function imprimirPuntosInspeccion()
    {
        //$this->imprimirPuntos("INSPECCIÓN DE CONTENEDOR",$this->inspeccion->puntos);
        $borde = 0;
        $anchoColumna1 = 50;
        $anchoColumna2 = 30;
        $anchoColumna3 = 110;
        
        $this->SetLeftMargin(10);
        $this->SetFont($this->font, 'B', 13);
        $this->Ln();
        $this->SetFillColor(242, 242, 242);
        $this->Cell(0,8,$this->texto("INSPECCIÓN DE TRACTOR"),$borde,2,'C',1);
        
        
        $this->SetFont($this->font, 'B', 10);
        $this->Cell($anchoColumna1, 8, $this->texto("Descripción"), $borde, 0, 'C', 1);
        $this->Cell($anchoColumna2, 8, "Resultado", $borde, 0, 'C', 1);
        $this->Cell($anchoColumna3, 8, "Observaciones", $borde, 0, 'C', 1);
        $this->Ln();
        
        $this->widths = array($anchoColumna1, $anchoColumna2, $anchoColumna3);
        $this->aligns = array("I", "C", "I");
        $this->fontNames = array($this->font, $this->font, $this->font);
        $this->fontWeights =  array('', "B", '');
        $this->fontSizes =  array(9, 10, 9);
        $this->imprimirPuntosTabla($this->inspeccion->puntos,$borde);
    }
}

class Reporte17 extends PDF
{
    protected function imprimirContenido()
    {
        $transportista = strtoupper($this->inspeccion->transportista);
        $chofer = strtoupper($this->inspeccion->chofer);
        $numeroTractor = $this->inspeccion->numeroTractor;
        $numeroCaja = $this->inspeccion->numeroCaja;
        $colorTractor = strtoupper($this->inspeccion->colorTractor);
        $colorCaja = strtoupper($this->inspeccion->colorCaja);
        $numeroContenedor = $this->inspeccion->numeroContenedor;
        $tipoCaja = strtoupper($this->inspeccion->tipoCaja);
        $sello =  $this->inspeccion->sello;
        $selloViajero =  $this->inspeccion->selloViajero;
        $alto = $this->inspeccion->alto;
        $ancho = $this->inspeccion->ancho;
        $profundidad = $this->inspeccion->profundidad;
        $placasTractor =  $this->inspeccion->placasTractor;
        $placasCaja =  $this->inspeccion->placasCaja;
        
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
        $this->Cell(45, 8, $this->texto($this->inspeccion->numeroOrden), $borde, 0, 'L');
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, "Factura:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($this->inspeccion->factura), $borde, 0, 'L');
        
        //INSPECION ALEATORIA - INSPECTORA LEAORIO
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, $this->texto("Inspección aleatoria:"), $borde, 0, 'L');
        $inspeccionAleatorio = $this->inspeccion->inspectorAleatorioId==null?"NO":"SI";
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($inspeccionAleatorio), $borde, 0, 'L');
      
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, $this->texto("Inspector aleatorio:"), $borde, 0, 'L');
        $inspectorAleatorio = $this->inspeccion->inspectorAleatorioNombre==""?"NA":$this->inspeccion->inspectorAleatorioNombre;
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($inspectorAleatorio), $borde, 0, 'L');
        
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(110, 8, $this->texto("La caja o contenedor está libre de contaminantes agrícolas:"), $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto($this->inspeccion->tieneSelloImpreso), $borde, 0, 'L');
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(110, 8, $this->texto("La unidad está libre de contaminantes agrícolas:"), $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto($this->inspeccion->cajaLibreObjetosOrganicos), $borde, 0, 'L');
        
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
        
        $this->imprimirPuntosInspeccion();
    }
    
    protected function imprimirPuntosInspeccion()
    {
        $borde = 0;
        $anchoColumna1 = 50;
        $anchoColumna2 = 30;
        $anchoColumna3 = 110;
        
        $this->SetLeftMargin(10);
        $this->SetFont($this->font, 'B', 13);
        $this->Ln();
        $this->SetFillColor(242, 242, 242);
        $this->Cell(0,8,$this->texto("INSPECCIÓN DE TRACTOR"),$borde,2,'C',1);
        $this->SetFont($this->font, 'B', 10);
        $this->Cell($anchoColumna1, 8, $this->texto("Descripción"), $borde, 0, 'C', 1);
        $this->Cell($anchoColumna2, 8, "Resultado", $borde, 0, 'C', 1);
        $this->Cell($anchoColumna3, 8, "Observaciones", $borde, 0, 'C', 1);
        $this->Ln();
        
        $this->widths = array($anchoColumna1, $anchoColumna2, $anchoColumna3);
        $this->aligns = array("I", "C", "I");
        $this->fontNames = array($this->font, $this->font, $this->font);
        $this->fontWeights =  array('', "B", '');
        $this->fontSizes =  array(9, 10, 9);
        $this->imprimirPuntosTabla($this->getPuntos(array(1,2,3,4,5,6,7,8,9,10,11,22)),$borde);
        
        $this->SetLeftMargin(10);
        $this->SetFont($this->font, 'B', 13);
        $this->Ln();
        $this->SetFillColor(242, 242, 242);
        $this->Cell(0,8,$this->texto("INSPECCIÓN DE CONTENEDOR"),$borde,2,'C',1);
        $this->SetFont($this->font, 'B', 10);
        $this->Cell($anchoColumna1, 8, $this->texto("Descripción"), $borde, 0, 'C', 1);
        $this->Cell($anchoColumna2, 8, "Resultado", $borde, 0, 'C', 1);
        $this->Cell($anchoColumna3, 8, "Observaciones", $borde, 0, 'C', 1);
        $this->Ln();
        
        $this->widths = array($anchoColumna1, $anchoColumna2, $anchoColumna3);
        $this->aligns = array("I", "C", "I");
        $this->fontNames = array($this->font, $this->font, $this->font);
        $this->fontWeights =  array('', "B", '');
        $this->fontSizes =  array(9, 10, 9);
        $this->imprimirPuntosTabla($this->getPuntos(array(12,13,14,15,16,17,18,19,20,21)),$borde);
    }
    
   
    
   
    
}


class ReporteFabrica
{
    public function crear($tipoInspeccionId)
    {
        $reporte = null;
        switch($tipoInspeccionId)
        {
            case 7:
                $reporte = new Reporte7();
            break;
            case 10:
                $reporte = new Reporte10();
            break;
            case 17:
                $reporte = new Reporte17();
            break;
        }
        return $reporte;
    }
}


$administrador_conexion = new AdministradorConexion();
try
{
    $conexion = $administrador_conexion->abrir();
    if($conexion)
    {
        $repositorio = new InspeccionesRepositorio($conexion);
        $inspeccionId = REQUEST('inspeccionId');
        
        $llaves= (object) [
            'id' =>  $inspeccionId
        ];
        
        $resultado = $repositorio->consultarPorLlaves($llaves);
        
        if($resultado->correcto())
        {
            $inspeccion = $resultado->valor;
            $reporteFabrica = new ReporteFabrica();
            $reporte = $reporteFabrica->crear($inspeccion->tipoInspeccionId);
            if($reporte!=null)
            {
                //$pdf = new PDF();
                $reporte->setInspeccion($resultado->valor);
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


