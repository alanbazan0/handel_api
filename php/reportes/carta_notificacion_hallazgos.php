<?php
use php\clases\AdministradorConexion;
use php\repositorios\AuditoriasRepositorio;
use php\modelos\Resultado;
use php\repositorios\EmpresasRepositorio;
require('write_tag.php');

require_once('../vendor/fpdf181/fpdf.php');
include '../clases/Utilidades.php';
include '../clases/Maps.php';
include '../clases/AdministradorConexion.php';
include '../repositorios/AuditoriasRepositorio.php';
require_once('../repositorios/EmpresasRepositorio.php');
include '../clases/Mes.php';

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



class PDF extends PDF_WriteTag
{
    private $fontSize = 11;
    private $font = "Helvetica";
    private $modelo;
    private $empresa;
    private $secciones;
    private $conexion;
    private $apiKey = "AIzaSyA3YhSwuW4LsOwW60WD1MekhIf8n_uGAK0";
   // private $apiKey = "AIzaSyB0xfZC35A5kb5qr8HR7uya8KrZf5OyER0";
    
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
    
    public function setEmpresaUsuario($empresa)
    {
        $this->empresaUsuario = $empresa;
    }
    
    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;
    }
    
    public function setModelo($modelo)
    {
        $this->modelo = $modelo;
    }
    
    public function setSecciones($secciones)
    {
        $this->secciones = $secciones;
    }
    
    public function setConexion($conexion)
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
    
    function Header()
    {
        $folio = strtoupper($this->calcularFolio());
        
        $logo = "";
        $logo = "../logos_empresas/logo". $this->usuario->empresaId.".png";
        if(file_exists($logo))
        {
            $this->Image($logo,20,5,20,0);
        }
        else
        {
            $logo = "../imagenes/logo_handel.jpg";
            $this->Image($logo,20,5,20,0);
        }
    }
    
    function Footer()
    {
       
    }
    
    private function calcularFolio()
    {
        $folio ="";
      /*  $folio ="";
        $folio.=$this->modelo->empresaNombreCorto;
                
        $fecha = substr($this->modelo->fechaEjecucion,0,10);
        list($dia, $mes, $ano) = explode("/", $fecha);
        $folio.=$dia.$mes.$ano;
                
        $folio.="-".$this->modelo->contadorEmpresa;*/
        
        $folio.=$this->modelo->referencia ."-NH";
        return $folio;
    }
    
    function inicializarEstilos()
    {
        $this->SetStyle("p",$this->font,"N",$this->fontSize,"0,0,0");
        $this->SetStyle("vb",$this->font,"B",$this->fontSize,"0,0,0");
        $this->SetStyle("link",$this->font,"U",0,"0,0,255");
        
        $this->SetStyle("h1","times","N",18,"102,0,102",0);
        $this->SetStyle("a","times","BU",9,"0,0,255");
        $this->SetStyle("pers","times","I",0,"255,0,0");
    }
    
    public function generar()
    {
        $this->SetFont($this->font,'',$this->fontSize);
      
       
        $this->inicializarEstilos(); 
        $this->portada();
        $this->firmas();
        $this->seguimiento();
        
        
        
    }
    
    function seguimiento()
    {
        
    }
    
    function firmas()
    {
        $this->Ln();
        $this->Ln(); 
        $this->SetX(15);
        $this->cMargin = 1;
        $this->SetLeftMargin(20);
        $this->fontSizes = array($this->fontSize, $this->fontSize);
        $this->fontWeights = array("","");
        $this->fontNames = array($this->font, $this->font);
        $this->aligns = array("C","C");
        $this->widths = array(85,85);
        $this->textColors = array("#000000","#000000");
        $this->borders = array(0,0);
        $this->borderColors = array("#afb2b0","#afb2b0");
        $this->backgroundColors = array("#ffffff","#ffffff");
        //  $this->SetFillColor(189, 193, 191);
      
        $y = $this->GetY();
        $limite = 250;
        if($y < $limite)
            $this->SetY($limite);
        
        $this->Row2(array("Atentamente:","Firma de Recibido:"),5);
        $this->Ln(); 
        $this->Ln(); 
        $this->Row2(array("Nombre y Firma del Representante de " . $this->texto($this->empresaUsuario->nombre), $this->texto("Nombre y Firma del Representante de la compañia que se visitó")),5);
    }
    
    
    public function imprimir()
    {
        $filename ="../cartas_notificacion_hallazgos/";
        $filename.=$this->calcularFolio();
        $filename.=".pdf";
        
        
       $this->Output($filename,'F');
        
        if (file_exists($filename))
        {
            header('Location:'. $filename);
        }
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
    
    
    
    function observaciones()
    {
        
      
        
        $this->SetFont($this->font, '', $this->fontSize);
        $this->SetLeftMargin(40);
        
        $this->Ln();
        $this->Ln(); 
        $borde = 0;
        
        $this->cMargin = 1;
        //$this->SetLeftMargin(10);
        $this->fontSizes = array($this->fontSize);
        $this->fontWeights = array("");
        $this->fontNames = array($this->font);
        $this->aligns = array("L");
        $this->widths = array(150);
        $this->textColors = array("#000000");
        $this->borders = array($borde);
        $this->borderColors = array("#afb2b0");
        $this->backgroundColors = array("#ffffff");
      
        
        $repositorio = new AuditoriasRepositorio($this->conexion);
        $resultado = $repositorio->getHallazgos($this->secciones,"notificacion");
        if($resultado->correcto())
        {
            $observaciones = $resultado->valor;
            for($i = 0; $i < count($observaciones); $i++)
            {
                $observacion = $observaciones[$i];
                //$this->backgroundColors = array("#e6e6e6",$color,$color,$color,$color);
                $this->SetX(30);
                $this->Row2(array(chr(149) . " " . $this->texto($observacion->hallazgo)),5);
            }
        }
       
       
        
    }
    
    function CheckPageBreak($h,$text)
    {
        //If the height h would cause an overflow, add a new page immediately
        if($this->GetY()+$h>$this->PageBreakTrigger)
        {
            $this->AddPage($this->CurOrientation);
 
            
            $this->SetY(25);
            //var_dump($text);
            if(strpos($text, "".chr(149)))
            {
              
                $this->SetX(50);
                $this->SetLeftMargin(20);
            }
            else
            {
                $this->SetX(30);
                $this->SetLeftMargin(20);
            }
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
    
    
    
    function fechaATexto($fecha)
    {
        list($dia, $mes, $ano) = explode("/", $fecha);
        $fecha = $dia ." de " . Mes::getNombre($mes) ." del " .$ano;
        return $fecha;
    }
    
    
    function portada()
    {
        $this->AddPage();
        
        $borde = 0;
        $altoLinea = 7;
        
        $this->Ln();
        $this->Ln();
        $this->SetTextColor(0,0,0);
        $this->SetFont($this->font,'',$this->fontSize);
        
        $this->SetLeftMargin(20);
        $this->SetRightMargin(20);
        $this->Cell(0, $altoLinea, $this->texto($this->modelo->sedeNombre. " a ". $this->fechaATexto($this->modelo->fecha)), $borde, 0, 'R');
       
        $this->Ln();
        $this->Ln();        
        
        //var_dump($this->modelo);
        
        $this->SetFont($this->font,'B', $this->fontSize);
        $this->Cell(0, $altoLinea, $this->texto("A quien corresponda:"),0,0,'L');       
        $this->Ln(); 
        $this->Ln(); 
        
        $txt="<p>Por medio de la presente hacemos de su conocimiento las observaciones detectadas derivadas de nuestra visita realizada el día ".$this->fechaATexto($this->modelo->fecha)." por parte de: <vb>Handel Servicios de Consultoria 
Especializada</vb> como representante de la Compañia <vb>".$this->empresaUsuario->nombre.".</vb></p>";
        $this->SetFont($this->font,'', $this->fontSize);
        $this->SetLineWidth(0.1);
        $this->WriteTag(0,$altoLinea,$this->texto($txt),0,"J");
        
        
        $this->Ln(); 
        $this->SetFont($this->font,'', $this->fontSize);
        $this->SetY($this->GetY()+ $altoLinea);
        $this->Cell(0, $altoLinea, $this->texto("Las observaciones detectadas son las siguientes"),0,0,'L');     
        $this->observaciones();
        
        $this->Ln(); 
        $txt = "<p>Agradecemos de antemano nos informen cuando hayan sido completadas y concreatadas las observaciones señaladas (Informe con fotografías) vía correo electrónico a: <link>".$this->usuario->nombreUsuario."</link> con firma y notificación por el Representante Legal de su Compañia,  en un plazo no mayor a 6 meses a partir de la fecha señalada en el presente documento.</p>";
        $this->SetFont($this->font,'', $this->fontSize);
        $this->SetLineWidth(0.1);
        $this->SetX(20);
        $this->WriteTag(0,$altoLinea,$this->texto($txt),0,"J");
        
      
        $this->Ln();
        $this->Ln();
        $this->SetX(0);
        
        $this->SetLeftMargin(20);
        $this->SetRightMargin(20);
        $this->SetFont($this->font,'', $this->fontSize);
        $this->SetY($this->GetY()+ $altoLinea);
        $this->Cell(0, $altoLinea, $this->texto("Sin mas por el momento agradecemos la atención a la presente"),0,0,'L');    
    }
    

    function formatoFecha($fecha)
    {
        $f = substr($fecha,0,10);
        $hora = substr($fecha,11,5);
        list($ano, $mes, $dia) = explode("-", $f);
        $fecha = "$dia/$mes/$ano $hora";
        return $fecha;
    }
    
    function Row2($data, $height)
    {
        //Calculate the height of the row
        $nb=0;
        for($j=0;$j<count($data);$j++)
            $nb=max($nb,$this->NbLines($this->widths[$j],$data[$j]));
        $h=$height*$nb;
            //Issue a page break first if needed
            $this->CheckPageBreak($h,$data[0]);
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

session_start();
$usuario = null;
if(isset($_SESSION['usuario']))
    $usuario = $_SESSION['usuario'];
 
if($usuario!= null)
{

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
                
                $empresaUsuario = null;
                if($usuario->empresaId!="")
                {
                    $repositorio = new EmpresasRepositorio($conexion);
                    $llaves= (object) [
                        'id' =>  $usuario->empresaId
                    ];
                    $resultado = $repositorio->consultarPorLlaves($llaves);
                    if($resultado->mensajeError=="")
                        $empresaUsuario = $resultado->valor;
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
                 $pdf->setEmpresaUsuario($empresaUsuario);
                 $pdf->setUsuario($usuario);
                 $pdf->setModelo($auditoria);
                 $pdf->setSecciones($secciones);
                 $pdf->setConexion($conexion);
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
}


