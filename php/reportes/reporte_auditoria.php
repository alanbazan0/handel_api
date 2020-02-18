<?php
use php\clases\AdministradorConexion;
use php\repositorios\AuditoriasRepositorio;
use php\modelos\Resultado;
use php\repositorios\EmpresasRepositorio;


require('../vendor/fpdf181/fpdf.php');
include '../clases/Utilidades.php';
include '../clases/AdministradorConexion.php';
include '../repositorios/AuditoriasRepositorio.php';
include '../repositorios/EmpresasRepositorio.php';
require_once('../highcharts/highchartutils.php');

class VariableStream
{
    private $varname;
    private $position;
    
    function stream_open($path, $mode, $options, &$opened_path)
    {
        $url = parse_url($path);
        $this->varname = $url['host'];
        if(!isset($GLOBALS[$this->varname]))
        {
            trigger_error('Global variable '.$this->varname.' does not exist', E_USER_WARNING);
            return false;
        }
        $this->position = 0;
        return true;
    }
    
    function stream_read($count)
    {
        $ret = substr($GLOBALS[$this->varname], $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }
    
    function stream_eof()
    {
        return $this->position >= strlen($GLOBALS[$this->varname]);
    }
    
    function stream_tell()
    {
        return $this->position;
    }
    
    function stream_seek($offset, $whence)
    {
        if($whence==SEEK_SET)
        {
            $this->position = $offset;
            return true;
        }
        return false;
    }
    
    function stream_stat()
    {
        return array();
    }
}

class PDF extends FPDF
{
    private $font = "Helvetica";
    private $modelo;
    private $empresa;
    private $secciones;
    
    function __construct($orientation='P', $unit='mm', $format='A4')
    {
        parent::__construct($orientation, $unit, $format);
        // Register var stream protocol
        stream_wrapper_register('var', 'VariableStream');
    }
    
    
    function MemImage($data, $x=null, $y=null, $w=0, $h=0, $link='')
    {
        // Display the image contained in $data
        $v = 'img'.md5($data);
        $GLOBALS[$v] = $data;
        $a = getimagesize('var://'.$v);
        if(!$a)
            $this->Error('Invalid image data');
            $type = substr(strstr($a['mime'],'/'),1);
            $this->Image('var://'.$v, $x, $y, $w, $h, $type, $link);
            unset($GLOBALS[$v]);
    }
    
    function GDImage($im, $x=null, $y=null, $w=0, $h=0, $link='')
    {
        // Display the GD image associated with $im
        ob_start();
        imagepng($im);
        $data = ob_get_clean();
        $this->MemImage($data, $x, $y, $w, $h, $link);
    }
    
    public function setEmpresa($empresa)
    {
        $this->empresa = $empresa;
    }
    
    public function setModelo($modelo)
    {
        $this->modelo = $modelo;
    }
    
    public function setSecciones($secciones)
    {
        $this->secciones = $secciones;
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
    
    function Header()
    {
        $this->SetLineWidth(1);
        $this->SetDrawColor(197,93,90);
        $y = 20;
        $this->Line(10, $y, 210-10, $y);
        
        $folio = strtoupper($this->calcularFolio());
        
        $logo = "../imagenes/logo_handel.jpg";
        $this->Image($logo,8,5,30,0,'','http://handel-sce.com');
        
        $this->SetY(12);
        $this->SetX(40);
        
        $borde = 0;
        $altoLinea = 7;
        $this->SetLeftMargin(20);
        $this->SetFont($this->font,'I',10);
        $this->SetTextColor(130,130,130);
        $this->Cell(140, $altoLinea, $this->texto("Reporte de evaluación :  " . $folio), $borde, 0, 'R');
        $this->SetFont($this->font,'B',13);
        $this->SetTextColor(63,103,151);
        $this->Cell(20, $altoLinea, $this->texto(" " .  $this->PageNo()), $borde, 0, 'L');
        
        $this->SetLineWidth(0.5 );
        $this->SetDrawColor(118, 159, 209);
        $x = 180;
        $this->Line($x, 13, $x, 18);
    }
    
    function Footer()
    {
        $this->SetLineWidth(1);
        $this->SetDrawColor(197,93,90);
        $y = 275;
        $this->Line(10, $y, 210-10, $y);
        
        $logo = "../imagenes/telefono_naranja.png";
        $this->Image($logo,10,278,10,0,'','http://apps-handel.com');
        $this->SetY(-19);
        $this->SetX(20);
         
        $borde = 0;
        $altoLinea = 3;
        $this->SetLeftMargin(20);
        $this->SetFont($this->font,'',8);
        $this->SetTextColor(0,0,127);
        $this->Cell(60, $altoLinea ,'http://www.handel-sce.com/',$borde,'','',false, "http://www.handel-sce.com/");
        $this->SetFont($this->font,'I',8);
        $this->SetTextColor(130,130,130);
        $this->Cell(120, $altoLinea, $this->texto("©Handel SCE 2019. Todos los derechos reservados. La información"), $borde, 0, 'R');
        
        $this->Ln();
        $this->SetLeftMargin(20);
        $this->SetFont($this->font,'',8);
        $this->SetTextColor(130,130,130);
        $this->Cell(60, $altoLinea ,'871 7508682 / 871 688 7317',$borde);
        $this->SetFont($this->font,'I',8);
        $this->SetTextColor(130,130,130);
        $this->Cell(120, $altoLinea, $this->texto("contenida	en este documento es confidencial y no podrá ser"), $borde, 0, 'R');
        
        $this->Ln();
        $this->SetLeftMargin(20);
        $this->SetFont($this->font,'',8);
        $this->SetTextColor(0,0,127);
        $this->Cell(60, $altoLinea ,'mail@handel-sce.com',$borde,'','',false, "mailto:mail@handel-sce.com");
        $this->SetFont($this->font,'I',8);
        $this->SetTextColor(130,130,130);
        $this->Cell(120, $altoLinea, $this->texto("revelada para cualquier propósito diferente a los indicados por los involucrados."), $borde, 0, 'R');
       
//         $this->SetFont($this->font, '', 9);
//         $this->SetY(-20);
//         $this->SetX(13);
//         $this->SetTextColor(0,0,127);
//         $this->Cell(80, 8 ,'http://www.handel-sce.com/',$borde,'','',false, "http://www.handel-sce.com/");
//         //$this->SetTextColor(0,0,0);
//        // $this->Cell(80, 8, $this->texto("Inspección realizada mediante App 10 y 7"), $borde, 0, 'C');
//         //$this->SetTextColor(0,0,127);
//         $this->Cell(80, 8 ,'http://www.handel-sce.com/',$borde,'','',false, "http://www.handel-sce.com/");
//         $this->SetTextColor(0,0,0);
//         $this->Cell(30, 8,"Hoja ". $this->PageNo().' de {nb}', $borde, 0, 'C');
    }
    
    private function calcularFolio()
    {
        $folio ="";
        $folio.=$this->modelo->empresaNombreCorto;
                
        $fecha = substr($this->modelo->fechaEjecucion,0,10);
        list($dia, $mes, $ano) = explode("/", $fecha);
        $folio.=$dia.$mes.$ano;
                
        $folio.="-".$this->modelo->contadorEmpresa;
        return $folio;
    }
    
    public function generar()
    {
        $this->SetFont($this->font,'',20);
      
        $this->encabezado();
        $this->datosGenerales();
        $this->metodologia();
        $this->graficosCompania();
        $this->aplicacionResultados();
        $this->comparacionGlobal();
        $this->observaciones();
        $this->incidencias();
                        
    }
    
    public function imprimir()
    {
        $filename ="../reportes_inspeccion/";
        $filename.=$this->calcularFolio();
        $filename.=".pdf";
        
        //var_dump($this->modelo);
        
        $this->Output($filename,'F');
        
        if (file_exists($filename))
        {
            header('Location:'. $filename);
        }
    }
    
    function datosGenerales()
    {
        $this->AddPage();
        
       // $this->SetY(20);
        //$this->SetX(20);
        $borde = 'B';
        $w1 = 85;
        $w2 = 85;

        $this->imprimirTituloHoja("I. Datos generales");
        
     
        $this->Ln();
        
        //Compañia
        $this->Ln();
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->font, 'B', 10);
        $this->Cell($w1, 10,$this->texto("Compañia:"), $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell($w2, 10, $this->texto($this->empresa!=null?$this->empresa->nombre:"-"), $borde, 0, 'L');
        
        $paisNombre ="NO ASIGNADO";
        $estadoNombre ="NO ASIGNADO";
        $ciudadNombre ="NO ASIGNADO";
        if($this->empresa!=null)
        {
            if($this->empresa->pais!=null)
                $paisNombre = $this->empresa->pais;
            if($this->empresa->estado!=null)
                $estadoNombre = $this->empresa->estado;
            if($this->empresa->ciudad!=null)
                $ciudadNombre = $this->empresa->ciudad;
        }
        
        //Pais
        $this->Ln();
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->font, 'B', 10);
        $this->Cell($w1, 10,$this->texto("País:"), $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell($w2, 10, $this->texto($paisNombre), $borde, 0, 'L');
        
        //Estado
        $this->Ln();
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->font, 'B', 10);
        $this->Cell($w1, 10,$this->texto("Estado"), $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell($w2, 10, $this->texto($estadoNombre), $borde, 0, 'L');
        
        //Ciudad
        $this->Ln();
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->font, 'B', 10);
        $this->Cell($w1, 10,$this->texto("Ciudad"), $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell($w2, 10, $this->texto($ciudadNombre), $borde, 0, 'L');
        
        //campos de primera página
       if(count($this->secciones)>0)
       {
           $seccion = $this->secciones[0];
            for($i = 0; $i < count($seccion->preguntas); $i++)
            {
                 $pregunta = $seccion->preguntas[$i];
                 if($pregunta->tipo=="e")
                 {
                     $this->Ln();
                     $this->SetTextColor(0, 0, 0);
                     $this->SetFillColor(242, 242, 242);
                     $this->SetFont($this->font, 'B', 10);
                     $this->Cell(170, 10,$this->texto($pregunta->texto), $borde, 0, 'L',1);
                    
                 }
                 else if($pregunta->tipo=="m")
                 {
                     $altoFoto = 50;
                     if(!$this->cabeComponente($altoFoto+10))
                     {
                         $this->AddPage();
                     }
                     $this->Ln();
                     $this->SetTextColor(0, 0, 0);
                     $this->SetFont($this->font, 'B', 10);
                     $this->Cell($w1, 10,$this->texto($pregunta->texto), $borde, 0, 'L');
                     $this->SetFont($this->font, '', 10);
                     $this->Cell($w2, 10, $this->texto($pregunta->valor), $borde, 0, 'L');
                     
                     if($pregunta->valor!="")
                     {
                         list($lat, $lng) = explode(",", $pregunta->valor);
                         $lat =   str_replace('lat:','',$lat);
                         $lng =   str_replace('lng:','',$lng);
                         
                         $imagen = "http://maps.googleapis.com/maps/api/staticmap?zoom=13&size=400x200&maptype=roadmap&markers=color:red|label:Ubicación|$lat,$lng&key=AIzaSyB7dydU6J78km_U76v44CHP5M3vol2igM8";
                         
                         
                         $this->setY($this->GetY() + 15,$altoFoto,null);
                         $logo = file_get_contents($imagen);
                         
                         if($logo!=null)
                            $this->MemImage($logo, 50, null);
                     }  
                     
                   
                 }
                 else if($pregunta->tipo=="ft")
                 {
                  
                     $altoFoto = 50;
                     
                     if(!$this->cabeComponente($altoFoto+10))
                     {
                        $this->AddPage();
                     }
                     $this->Ln();
                     $this->SetTextColor(0, 0, 0);
                     $this->SetFont($this->font, 'B', 10);
                     $this->Cell($w1, 10,$this->texto($pregunta->texto), $borde, 0, 'L');
                     $this->SetFont($this->font, '', 10);
                     $this->Cell($w2, 10, "", $borde, 0, 'L');
                     
                     $dataPieces = explode(',',$pregunta->valor);
                     $encodedImg = $dataPieces[1];
                     $decodedImg = base64_decode($encodedImg);
                     if( $decodedImg!==false )
                     {
                         $x = (210/2) - ($altoFoto/2);
                         $this->SetY($this->GetY()+15);
                         $this->MemImage($decodedImg, $x, null, $altoFoto,$altoFoto);
                     }
                 }
                 else
                 {
                     $this->Ln();
                     $this->SetTextColor(0, 0, 0);
                     $this->SetFont($this->font, 'B', 10);
                     $this->Cell($w1, 10,$this->texto($pregunta->texto), $borde, 0, 'L');
                     $this->SetFont($this->font, '', 10);
                     $this->Cell($w2, 10, $this->texto($pregunta->valor), $borde, 0, 'L');
                 }
            }
       }
        
        
    }
    
    function cabeComponente($alto)
    {
        $y = $this->GetY();
        $margen = 30;
        $maxY = $this->GetPageHeight() - $margen;
        
        //echo $maxY;
        
        if($y+$alto < $maxY)
            return true;
        else 
            return false;
    }
    
    function metodologia()
    {
        $this->AddPage();
       // $this->SetY(20);
       // $this->SetX(20);
        $borde = 0;
        $w1 = 85;
        $w2 = 85;
        
        //Titulo metodologia
//         $this->SetTextColor(63,103,151);
//         $this->SetDrawColor(118, 159, 209);
//         $this->SetLeftMargin(20);
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(170, 10,$this->texto("II. Metodología"), 'B', 0, 'L');

        $this->imprimirTituloHoja("II. Metodología");
        
        $this->Ln();
        $this->SetLeftMargin(30);
        $this->SetFont($this->font, '', 10);
        $this->SetFillColor(242, 242, 242);
        $this->SetTextColor(0,0,0);
        //$this->cMargin=10;
        //Print 2 Cells
        $tamanoLinea = 6;
        $this->Ln();
        $this->Cell(150,$tamanoLinea,$this->texto('    Esta autoevaluación fue diseñada utilizando como referencia las guías de seguridad'),$borde,1,'FJ',1);
        $this->Cell(150,$tamanoLinea,$this->texto('del	programa C-TPAT	(Custom	Trade Partnership Against Terrorism) en	sus	requisitos'),$borde,1,'FJ',1);
        $this->Cell(150,$tamanoLinea,$this->texto('mínimos	de	seguridad, las gráficas de referencia fueron tomadas de evaluaciones en'),$borde,1,'FJ',1);
        $this->Cell(150,$tamanoLinea,$this->texto('empresas similares en rama a la	suya según se indica individualmente. Los porcentajes'),$borde,1,'FJ',1);
        $this->Cell(150,$tamanoLinea,$this->texto('que	aparecen son aproximados en	base a la auditoría	realizada.'),$borde,1,'L',1);

        $this->Cell(150,$tamanoLinea,'',$borde,1,'L',1);
        $this->Cell(150,$tamanoLinea,$this->texto('En este punto se indica su estado en tres aspectos:'),$borde,1,'L',1);
        
        $this->Cell(150,$tamanoLinea,'',$borde,1,'L',1);
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(150,$tamanoLinea,$this->texto('Nivel de compromiso	y manejo de	amenazas'),$borde,1,'L',1);
        
        $this->Cell(150,$tamanoLinea,'',$borde,1,'L',1);
        $this->SetFont($this->font, '', 10);
        $this->Cell(150,$tamanoLinea,$this->texto('    Mide el conocimiento y manejo de amenazas de las iniciativas de	seguridad y	su'),$borde,1,'FJ',1);
        $this->Cell(150,$tamanoLinea,$this->texto('compromiso en integrarlas en la	operación diaria así como la asignación	de'),$borde,1,'FJ',1);
        $this->Cell(150,$tamanoLinea,$this->texto('responsabilidades desde la alta administración buscando la mejora continua.'),$borde,1,'L',1);
        
        $this->Cell(150,$tamanoLinea,'',$borde,1,'L',1);
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(150,$tamanoLinea,$this->texto('Implementación'),$borde,1,'L',1);
        
        $this->Cell(150,$tamanoLinea,'',$borde,1,'L',1);
        $this->SetFont($this->font, '', 10);
        $this->Cell(150,$tamanoLinea,$this->texto('    Mide el nivel de implementación de aspectos críticos de los criterios de seguridad en'),$borde,1,'FJ',1);
        $this->Cell(150,$tamanoLinea,$this->texto('la cadena de suministro.'),$borde,1,'L',1);
        
        $this->Cell(150,$tamanoLinea,'',$borde,1,'L',1);
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(150,$tamanoLinea,$this->texto('Verificación y mejora continúa'),$borde,1,'L',1);
        
        $this->Cell(150,$tamanoLinea,'',$borde,1,'L',1);
        $this->SetFont($this->font, '', 10);
        $this->Cell(150,$tamanoLinea,$this->texto('   Mide la existencia de un proceso para evaluar de forma regular las prácticas de la'),$borde,1,'FJ',1);
        $this->Cell(150,$tamanoLinea,$this->texto('cadena de suministro así como las políticas de ajuste contra los requerimientos recién'),$borde,1,'FJ',1);
        $this->Cell(150,$tamanoLinea,$this->texto('establecidos al hacer mejoras según sea necesario. '),$borde,1,'L',1);
    }
    
    function graficosCompania()
    {
        $this->AddPage();
//         $this->SetY(20);
//         $this->SetX(20);

        $this->imprimirTituloHoja("III. Gráficos de la compañia");
        $borde = 0;
        $w1 = 60;
        $w2 = 110;
        
//         //Titulo metodologia
//         $this->SetTextColor(63,103,151);
//         $this->SetDrawColor(118, 159, 209);
        
//         $this->SetLeftMargin(20);
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(170, 10,$this->texto("III. Gráficos de la compañia"), 'B', 0, 'L');
        
        $this->Ln();
        $this->Ln();
        $this->cMargin=5;
        $this->SetFillColor(242, 242, 242);
        $this->SetLeftMargin(20);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->font, 'B', 10);
        $this->Cell($w1, 10,$this->texto("Resultados en su compañia"), $borde, 0, 'L',1);
        $this->SetFont($this->font, '', 10);
        $this->Cell($w2, 10, $this->texto("Es el resultado de la evaluación realizada por Händel"), $borde, 0, 'L',1);
       
        $this->Ln();
        $this->cMargin=5;
        $this->SetFillColor(242, 242, 242);
        $this->SetLeftMargin(20);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->font, 'B', 10);
        $this->Cell($w1, 10,$this->texto("País"), $borde, 0, 'L',1);
        $this->SetFont($this->font, '', 10);
        $this->Cell($w2, 10, $this->texto("Puntaje promedio de empresas evaluadas en el país"), $borde, 0, 'L',1);
        
        $this->Ln();
        $this->cMargin=5;
        $this->SetFillColor(242, 242, 242);
        $this->SetLeftMargin(20);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->font, 'B', 10);
        $this->Cell($w1, 10,$this->texto("Sector de la industria"), $borde, 0, 'L',1);
        $this->SetFont($this->font, '', 10);
        $this->Cell($w2, 10, $this->texto("Puntaje promedio de empresas evaluadas en el sector especifco "), $borde, 0, 'L',1);
       
    }
    
    function imprimirTituloHoja($titulo)
    {
        $this->SetY(20);
        $this->SetX(20);
        $this->SetTextColor(63,103,151);
        $this->SetDrawColor(118, 159, 209);
        $this->SetLeftMargin(20);
        $this->SetFont($this->font, '', 10);
        $this->Cell(170, 10,$this->texto($titulo), 'B', 0, 'L');
    }
    
    function aplicacionResultados()
    {
        $this->AddPage();
        $this->imprimirTituloHoja("IV. Aplicación de resultados");

        $borde = 0;
        $this->Ln();
        $this->Ln();
        $this->cMargin=10;
        $this->SetFillColor(242, 242, 242);
        $this->SetLeftMargin(20);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->font, '', 10);
        $this->Cell(170, 6,$this->texto("En esta seccion aparecerá un comparativo de las gráficas conforme se avance en el"), $borde, 1, 'FJ',1);
        $this->Cell(170, 6,$this->texto("paquete de mantenimiento contratado con Händel SCE."), $borde, 1, 'L',1);
        
       
    }
    
    function comparacionGlobal()
    {
        $this->AddPage();
        $this->imprimirTituloHoja("V. Comparación global de referencia");
        
        $borde = 0;
        $this->Ln();
        $this->Ln();
        $this->cMargin=10;
        $this->SetFillColor(242, 242, 242);
        $this->SetLeftMargin(20);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->font, '', 10);
        $this->Cell(170, 6,$this->texto("Ilustra el estado actual de la compañía en los puntos básicos de seguridad del programa"), $borde, 1, 'FJ',1);
        $this->Cell(170, 6,$this->texto("C-TPAT	referente a empresas de transporte."), $borde, 1, 'L',1);
        
    }
    
    
    function observaciones()
    {
        $this->AddPage();
        $this->imprimirTituloHoja("VI. Observaciones, acciones y recomendaciones");
        
        $borde = 0;
        $this->Ln();
        $this->Ln();
        $this->cMargin=10;
        $this->SetFillColor(242, 242, 242);
        $this->SetLeftMargin(20);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->font, '', 10);
        $this->Cell(170, 6,$this->texto("Se	identifican los aspectos encontrados durante la inspección realizada."), $borde, 1, 'L',1);
        
    }
    
    function incidencias()
    {
        $this->AddPage();
        $this->imprimirTituloHoja("VII. Incidencias y observaciones varias");
        
        $borde = 0;
        $this->Ln();
        $this->Ln();
        $this->cMargin=10;
        $this->SetFillColor(242, 242, 242);
        $this->SetLeftMargin(20);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->font, '', 10);
        $this->Cell(170, 6,$this->texto("Se	listan a continuación incidentes menores	observados durante	la visita de inspección."), $borde, 1, 'L',1);
        
    }
    
    
    
    function encabezado()
    {
        $this->AddPage();
        $imagen = "../imagenes/encabezado_sivah.jpg";
        $anchoFoto = 120;
        $x = (210/2) - ($anchoFoto/2);
        $y = 30;
        $this->Image($imagen,$x,$y,$anchoFoto);
        
        $this->SetY(135);
        $this->SetX(65);
        $this->SetFont($this->font,'B',15);
        $this->SetTextColor(255, 255, 255);
        $this->SetFillColor(63, 103, 151);
        $this->Cell(130, 15, $this->texto("Reporte de Auditoría"),0,1,'L',1);       

        $borde = 0;
        $w1 = 50;
        $w2 = 100; 
        //Numero de registro
        $this->Ln();
        $this->SetLeftMargin(30);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->font, 'B', 10);
        $this->Cell($w1, 10,$this->texto("Referencia:"), $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell($w2, 10, $this->texto($this->modelo->referencia), $borde, 0, 'L');
        //Empresa
        $this->Ln();
        $this->SetLeftMargin(30);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->font, 'B', 10);
        $this->Cell($w1, 10,$this->texto("Compañia:"), $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell($w2, 10, $this->texto($this->modelo->empresaNombre), $borde, 0, 'L');
        //Fecha
        $this->Ln();
        $this->SetLeftMargin(30);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->font, 'B', 10);
        $this->Cell($w1, 10,$this->texto("Fecha de auditoría"), $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell($w2, 10, $this->texto($this->modelo->fechaEjecucion), $borde, 0, 'L');
        
        $this->SetDrawColor(130,130,130);
        $y = 175;
        $this->Line(30, $y, 210-30, $y);
        $y = 185;
        $this->Line(30, $y, 210-30, $y);
        $y = 195;
        $this->Line(30, $y, 210-30, $y);
        
        //Puntuacion
        $puntuacion = 0;
        $this->Ln();
        $this->Ln();
        $this->SetLeftMargin(30);
        $this->SetFont($this->font, '', 10);
        $this->Cell($w1, 10,$this->texto("Puntuación total: "), $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell($w2, 10, $this->texto($puntuacion  . "%"), $borde, 0, 'L');
        
       
        $this->Ln();
        $this->SetLeftMargin(30);
        $this->SetFont($this->font, '', 10);
        $this->Cell(45, 10,$this->texto("Evaluación de riesgo"), $borde, 0, 'L');
        $this->SetFont($this->font, '', 9);
        $this->Cell(35, 10, $this->texto("0-70 Alto"), $borde, 0, 'C');
        $this->Cell(35, 10, $this->texto("71-80 Medio"), $borde, 0, 'C');
        $this->Cell(35, 10, $this->texto("81-100 Bajo"), $borde, 0, 'C');
      
        $w = 5;
        $y = 217.5;
        $this->Image("../imagenes/circulo_rojo.png",78,$y,$w,0);
        $this->Image("../imagenes/circulo_amarillo.png",110,$y,$w,0);
        $this->Image("../imagenes/circulo_verde.png",146,$y,$w,0);
       
        
        $this->SetDrawColor(118, 159, 209);
        $y = 240;
        $this->Line(30, $y, 210-30, $y);
    }
    

    function formatoFecha($fecha)
    {
        $f = substr($fecha,0,10);
        $hora = substr($fecha,11,5);
        list($ano, $mes, $dia) = explode("-", $f);
        $fecha = "$dia/$mes/$ano $hora";
        return $fecha;
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
    
    function texto($texto)
    {
        return iconv('UTF-8', 'windows-1252', $texto);
    }
}


$administrador_conexion = new AdministradorConexion();
try
{
    $conexion = $administrador_conexion->abrir();
    if($conexion)
    {
        $repositorio = new AuditoriasRepositorio($conexion);
        $inspeccionId = REQUEST('auditoriaId');
        
        $llaves= (object) [
            'id' =>  $inspeccionId
        ];
        
        
        $resultado = $repositorio->consultarPorLlaves($llaves);
        
      
        
        if($resultado->mensajeError=="")
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
            if($resultado->mensajeError=="")
            {
                $secciones = $resultado->valor;
            }
            
            
             $pdf = new PDF();
             $pdf->setEmpresa($empresa);
             $pdf->setModelo($auditoria);
             $pdf->setSecciones($secciones);
             $pdf->AliasNbPages();
             $pdf->generar();
             $pdf->imprimir();
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


