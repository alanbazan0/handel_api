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
        $this->SetLeftMargin(5);
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
        //$this->SetY(16);
        //$this->SetLeftMargin(5);
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
                $this->Row2(array($fecha,$this->texto($observacion->usuarioNombre),$this->texto($observacion->usuarioApellido), $this->texto($observacion->descripcion),$this->texto($observacion->seccion),$this->texto($observacion->tipoObservacionNombre),$this->texto($observacion->estatusValidacionDescripcion),$this->texto($observacion->validadorNombreCompleto)),8);
                
            }
        }
        
    }
    
    private function historialCambios()
    {
        $this->Ln();
        $this->Ln();
        //$this->SetY(16);
        //$this->SetLeftMargin(5);
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
            $this->fontSizes = array(11, 11, 11, 11, 11, 11, 11);
            $this->fontWeights = array("","","","","","","");
            $this->aligns = array("C","C","C","C","C","C","C");
            $this->widths = array(30, 30, 30, 40, 27, 40, 40);
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
                $fecha = substr($observacion->fecha, 0,10);
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
    
    function avance()
    {
        $borde = 0;
        $avance = $this->proceso->porcentaje;
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
        $inspectorNombre = strtoupper($this->proceso->inspectorNombre);
        //$fechaInicio = $this->formatoFecha($this->proceso->fechaproceso);
        //$fechaFinalizacion= $this->formatoFecha($this->proceso->fechaFinalizacion);
        
        $fechaInicio = $this->proceso->fechaproceso;
        $fechaFinalizacion= $this->proceso->fechaFinalizacion;
        
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
    

    
    
    function imprimirFotos()
    {
      
        
        $seccionesFotos = array();
        for($i = 0 ; $i < count($this->proceso->puntos); $i++)
        {
            $punto = $this->proceso->puntos[$i];;
            $seccion = $this->crearSeccionFotos($this->proceso->id,$punto);
            if(count($seccion->fotos)>0)
                array_push($seccionesFotos,$seccion);
        }
        
//         for($i = 0 ; $i < count($this->proceso->puntos2); $i++)
//         {
//             $punto = $this->proceso->puntos2[$i];;
//             $seccion = $this->crearSeccionFotos($this->proceso->id,$punto);
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
    
    function agregarFoto($procesoId, $nombreArchivo ,$titulo,&$fotos)
    {
        $archivo = "../fotos_procesoes/".$procesoId ."_" . $nombreArchivo.".jpg";
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
        $this->agregarFoto($this->proceso->id,"sello","SELLO",$fotos);
        $this->agregarFoto($this->proceso->id,"sellocaja","SELLO CAJA",$fotos);
        $this->agregarFoto($this->proceso->id,"placatractor","PLACA TRACTOR",$fotos);
        $this->agregarFoto($this->proceso->id,"placacontenedor","PLACA CONTENEDOR",$fotos);
        $this->agregarFoto($this->proceso->id,"licchofer","LICENCIA CHOFER",$fotos);
        
        $this->agregarFoto($this->proceso->id,"cajavacia","CAJA VACIA",$fotos);
        $this->agregarFoto($this->proceso->id,"cajafinal","CAJA FINAL",$fotos);
        $this->agregarFoto($this->proceso->id,"cajacerrada","CAJA CERRADA",$fotos);
        
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
        $this->agregarFoto($this->proceso->id,"firma_chofer","FIRMA CHOFER",$fotos);
        $this->agregarFoto($this->proceso->id,"firma_inspector","FIRMA INSPECTOR",$fotos);
        if($this->proceso->inspectorAleatorioId!=null)
        {
            //$nombre = $this->proceso->inspectorAleatorioNombre;
            $nombre="";
            $this->agregarFoto($this->proceso->id,"firma_aleatoria","INSPECTOR ALEATORIO " .$nombre ,$fotos);
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
    
    function calcularPorcentajesEncabezados()
    {
        $tareas = $this->proceso->tareas;
        $indicesEncabezados = [];
        for($i=0; $i < count($tareas); $i++)
        {
            $tarea = $tareas[$i];
            if($tarea->tipo=="e")
            {
                array_push($indicesEncabezados,$i);
            }
        }
        
        for($i=0; $i < count($indicesEncabezados); $i++)
        {
            $indice = $indicesEncabezados[$i];
            $this->calcularPorcenjateEncabezado($indice,$tareas);
        }
        
    }
   
    
    
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
    
    function crearSeccionFotos($procesoId, $punto)
    {
        $fotos = array();
        $foto = "../fotos_procesoes/".$procesoId ."_" . $punto->id ."_1.jpg";
        if (file_exists($foto))
            array_push($fotos,$foto);
            $foto = "../fotos_procesoes/".$procesoId ."_" . $punto->id ."_2.jpg";
            if (file_exists($foto))
                array_push($fotos,$foto);
                $foto = "../fotos_procesoes/".$procesoId ."_" . $punto->id ."_3.jpg";
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
            $punto = $this->getPunto($id, $this->proceso->puntos);
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



class Reporteproceso extends PDF
{
    protected function imprimirContenido()
    {
        $transportista = strtoupper($this->proceso->transportista);
        $chofer = strtoupper($this->proceso->chofer);
        $numeroTractor = $this->proceso->numeroTractor;
        $numeroCaja = $this->proceso->numeroCaja;
        $colorTractor = strtoupper($this->proceso->colorTractor);
        $colorCaja = strtoupper($this->proceso->colorCaja);
        $numeroContenedor = $this->proceso->numeroContenedor;
        $tipoCaja = strtoupper($this->proceso->tipoCaja);
        $sello =  $this->proceso->sello;
        $selloViajero =  $this->proceso->selloViajero;
        $alto = $this->proceso->alto;
        $ancho = $this->proceso->ancho;
        $profundidad = $this->proceso->profundidad;
        $placasTractor =  $this->proceso->placasTractor;
        $placasCaja =  $this->proceso->placasCaja;
        
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
        $this->Cell(45, 8, $this->texto($this->proceso->numeroOrden), $borde, 0, 'L');
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, "Factura:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($this->proceso->factura), $borde, 0, 'L');
        
        //INSPECION ALEATORIA - INSPECTORA LEAORIO
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, $this->texto("Inspección aleatoria:"), $borde, 0, 'L');
        $procesoAleatorio = $this->proceso->inspectorAleatorioId==null?"NO":"SI";
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($procesoAleatorio), $borde, 0, 'L');
      
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(40, 8, $this->texto("Inspector aleatorio:"), $borde, 0, 'L');
        $inspectorAleatorio = $this->proceso->inspectorAleatorioNombre==""?"NA":$this->proceso->inspectorAleatorioNombre;
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 8, $this->texto($inspectorAleatorio), $borde, 0, 'L');
        
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(110, 8, $this->texto("La caja o contenedor está libre de contaminantes agrícolas:"), $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto($this->proceso->tieneSelloImpreso), $borde, 0, 'L');
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(110, 8, $this->texto("La unidad está libre de contaminantes agrícolas:"), $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto($this->proceso->cajaLibreObjetosOrganicos), $borde, 0, 'L');
        
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
        
        $this->imprimirPuntosproceso();
    }
    
    
   
    
   
    
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


