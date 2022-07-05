<?php
use php\clases\AdministradorConexion;
use php\modelos\Resultado;
use php\repositorios\AuditoriasRepositorio;
use php\clases\Porcentaje;
use php\clases\AdministradorCorreo;
use php\repositorios\UsuariosProcesosRepositorio;
use php\repositorios\UsuariosRepositorio;
use php\repositorios\EmpresasRepositorio;
use php\repositorios\SedesRepositorio;

require('../vendor/fpdf181/fpdf.php');
include '../clases/Utilidades.php';
include '../clases/AdministradorConexion.php';
include '../repositorios/AuditoriasRepositorio.php';
require_once('../repositorios/SedesRepositorio.php');
require_once('../clases/AdministradorCorreo.php');


    
abstract class PDF extends FPDF
{
    protected $font = "Helvetica";
    protected $minuta;
    protected $margen = 5;
    
    public function __construct()
    {
        parent::__construct("L","mm","A4");
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
        $this->Cell($anchoColumna, 8, "", $borde, 0, 'L');
        $this->Cell($anchoColumna, 8, "https://sivah.apps-handel.com", $borde, 0, 'C', false,"https://sivah.apps-handel.com");
        $this->Cell($anchoColumna, 8, "Hoja ". $this->PageNo().' de {nb}', $borde, 0, 'R');
        
        $this->linea(200, 60, 141, 188, 1);
        
        
    }
    
    
    
    function Header()
    {
        $empresaId = $this->minuta->empresaId;
        $empresaNombre = $this->minuta->empresaNombre;
        $folio = strtoupper($this->calcularFolio());
        
        $logoX = 5;
        $logoY = 1;
        $logoAlto = 6;
        $logo = "../logos_empresas/logo$empresaId.png";
        if (file_exists($logo))
            $this->Image($logo,$logoX,$logoY,$logoAlto,0,'','');
        else 
        {
//             $logo = "../logos_empresas/default.png";
//             if (file_exists($logo))
//                 $this->Image($logo,$logoX,$logoY,$logoAlto,0,'','');
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
        $this->Cell($anchoColumna, 8, "X-Ray $this->referencia", $borde, 0, 'C');
        $this->Cell($anchoColumna, 8, "Fecha: $fecha", $borde, 0, 'R');
        
       
            
          
    }
    
    private function linea($y, $r, $g, $b, $w)
    {
        $this->SetLineWidth($w);
        $this->SetDrawColor($r, $g, $b);
        $width = $this->w;
        $this->Line($this->margen, $y, $width-$this->margen, $y);
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
    
  
    
    
    function titulo($titulo)
    {
        $this->Ln();
        //$this->SetY(16);
        //$this->SetLeftMargin(5);
        $this->SetFont($this->font,'B',18);
        $this->SetTextColor(60, 141, 188);
        $this->Cell(0,0,$this->texto($titulo),0,2,'C');
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
    
    

    
    public $tablewidths;
    public $aligns;
    public $columnFonts;
    public $footerset;
    
    
    protected  function calcularFolio()
    {
        $folio ="ReportePreliminar". str_replace(" ","",$this->usuarioXRay->nombreCompleto);
        //$folio =  "ReportePreliminar".$this->usuarioXRay->nombreCompleto;
        return $folio;
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



class XRay extends PDF
{

    function setConexion($conexion)
    {
        $this->conexion = $conexion;
    }
    
    function setAuditoria($auditoria)
    {
        $this->auditoria = $auditoria;
    }
    
    function setHallazgos($hallazgos)
    {
        $this->hallazgos = $hallazgos;
    }
    
    function setReferencia($referencia)
    {
        $this->referencia = $referencia;
    }
    
    function setUsuariosCorreo($usuariosCorreo)
    {
        $this->usuariosCorreo = $usuariosCorreo;
    }
    
    
    function setEnviarCorreo($enviarCorreo)
    {
        $this->enviarCorreo = $enviarCorreo;
    }
    
    function setAsuntoCorreo($asuntoCorreo)
    {
        $this->asuntoCorreo = $asuntoCorreo;
    }
    
    function setUsuarioXRay($usuarioXRay)
    {
        $this->usuarioXRay = $usuarioXRay;
    }
    
    function setEmpresa($empresa)
    {
        $this->empresa = $empresa;
    }
    
    
    function setSede($sede)
    {
        $this->sede = $sede;
    }
    
    function setFecha($fecha)
    {
        $this->fecha = $fecha;
    }
    
    
    public function generar()
    {
        $this->SetFont($this->font,'',20);
        $this->AddPage();
        //$this->titulo($this->referencia);
        
//         $this->SetY(20);
//         $this->subtitulo("Datos generales:");
//         $this->Ln();
//         $this->SetY($this->GetY()+1);
//         $this->campo(35,"Fecha de registro: ", substr($this->auditoria->fechaAlta,0,10));
//         $this->Ln();
//         $this->campo(35,"Nombre: ",$this->auditoria->titulo);
//         $this->Ln();
//         $this->campo(35,"Descripción: ",$this->auditoria->descripcion);
        $this->Ln();
        $this->Ln();
      
        
        $this->subtitulo("Hallazgos:");
        $this->Ln();
        $this->Ln();
        $this->hallazgos();
        $this->Ln();
        
        
        
        /*
         if(trim($this->minuta->acuerdos)!="")
         {
         $this->Ln();
         $this->subtitulo("Acuerdos y Acciones:");
         $this->acuerdos();
         }*/
        
        
        
    }
    
    public function imprimir()
    {
        $resultado = new Resultado();
        $filename ="../reportes_xray/";
        $filename.=$this->calcularFolio();
        $filename.=".pdf";
        
        
        $this->Output($filename,'F');
       
        if (file_exists($filename))
        {
            if($this->enviarCorreo=="true")
                $resultado = $this->enviarCorreo($filename,$this->usuariosCorreo);
            else
                header('Location:'. $filename);
            
        }
        return $resultado;
    }
    
    private function enviarCorreo($filename, $usuarios)
   {
       $numeroAcciones = 0;
       $nombreCompleto = $this->usuarioXRay->nombreCompleto;
       $empresa = $this->empresa->nombre;
       $sede = $this->sede->nombre;
       $fecha = $this->fecha;
        
      if($this->hallazgos!=null)
           $numeroAcciones= count($this->hallazgos);
       
       $asunto="=?UTF-8?B?".base64_encode("Resultado preliminar de evaluación para ".$this->usuarioXRay->nombreCompleto)."?=";
       
       $tipo = "xray" .$this->auditoriaId;
       $info = "tipo";
       $mensaje= file_get_contents('../plantillas_correo/xray.html');
       //$mensaje = "XRay test";
       //$usuarios = array();
       //array_push($usuarios,(object) ['nombreUsuario' => 'alanbazan@apps-handel.com','nombreCompleto' => 'Alan Bazán']);
      
      $contenido = "<tr>
                <td align='center' class='padding-copy' style='font-size: 25px; font-family: Helvetica, Arial, sans-serif; color: #548dd4; padding-top: 0px;'><strong>¡Hola! $nombreCompleto</strong><br></td>
            </tr>
             <tr>
                <td align='center' class='padding-copy textlightStyle' style='padding: 20px 0 0 0; font-size: 16px; line-height: 25px; font-family: Helvetica, Arial, sans-serif; color: #3F3D33;'>
                    Derivado de la evaluación realizada en
                </td>
            </tr>
             <tr>
                <td align='center' class='padding-copy textlightStyle' style='padding: 20px 0 0 0; font-size: 16px; line-height: 25px; font-family: Helvetica, Arial, sans-serif; color: #3F3D33;'>
                  <strong>$empresa - $sede - $fecha </strong>
                </td>
            </tr>
            <tr>
                <td align='center' class='padding-copy textlightStyle' style='padding: 20px 0 0 0; font-size: 16px; line-height: 25px; font-family: Helvetica, Arial, sans-serif; color: #3F3D33;'>
                   <p>
                       Te han sido asignadas en forma preliminar $numeroAcciones acciones, es posible que mientras se continúa la auditoría el número de acciones final podría ser diferente.
                       Al final de la auditoría recibirás una notificación con el número de acciones definitiva que te será asignado, así como las instrucciones especificas, la forma
                       en que se llevará el seguimiento y las fechas compromiso para resolverlas.
                    </p>
                    <p>
                        Esta notificación es el comprobante formal de las situaciones fuera de norma o recomendaciones que se identificaron y comentaron durante la auditoría, si crees
                        que una o más acciones no corresponden a tus funciones y deben ser asignadas a alguien más, por favor notificalo ahora a tu especialista en Handel o Supervisor
                        para que sea reasignada.
                    </p>
                    <p>
                        Adjunto a este correo encontrarás el listado de las acciones preliminares y las recomendaciones para solventarlas, es importante que lo verifiques y tengas presente.
                        Durante el cierre de la auditoría se comentarán las más importantes y el resultado de todos los departamentos.
                    </p>
                    <p>
                       Una copia de este correo será enviada a tu supervisor a fin de que se encuentre enterado.
                    </p>
                 </td>
            </tr>"
      ;
      
      $mensaje=  str_replace("@tituloDerecho","Resultado preliminar de tu evaluación",$mensaje);
      $mensaje=  str_replace("@titulo","$numeroAcciones Acciones asignadas",$mensaje);
      $mensaje=  str_replace("@contenido",$contenido,$mensaje);
      $mensaje=  str_replace("@colorTitulo","#0775b5",$mensaje);
      $mensaje=  str_replace("@textoBoton","¡Llévame a SIVAH!",$mensaje);
      $mensaje=  str_replace("@urlBoton","https://sivah.apps-handel.com",$mensaje);
       
       
       
       $administrador_correo = new AdministradorCorreo();
       $resultado = $administrador_correo->enviarCorreoUsuarios($tipo,$usuarios,$asunto, $mensaje, $info, "SIVAH: XRay",false,$filename);
       return $resultado;
   }
    
    
    private function hallazgos()
    {
        $borde = 1;
        $this->fontSizes = array(11, 11, 11, 11, 11, 11);
        $this->fontWeights = array("B","B","B","B","B","B");
        $this->aligns = array("C","C","C","C","C","C");
        $this->widths = array(15, 90, 90, 36, 28, 28);
        $this->textColors = array("#000000","#000000","#000000","#000000","#000000","#000000");
        $this->borders = array(1,1,1,1,1);
        $this->borderColors = array("#afb2b0","#afb2b0","#afb2b0","#afb2b0","#afb2b0","#afb2b0");
        $this->backgroundColors = array("#bdc1bf","#bdc1bf","#bdc1bf","#bdc1bf","#bdc1bf","#bdc1bf");
        $this->fontNames = array($this->font, $this->font, $this->font,$this->font,$this->font,$this->font);
        $this->Row2(array("Item","Hallazgo",$this->texto("Recomendación"),"Responsable","Reporte",$this->texto("Notificación")),5);
        
        $this->fontWeights = array("B","","","","","");
        $this->aligns = array("C","L","L","C","C","C");
        
       // $this->calcularPorcentajesEncabezados();
        
        $item = 0;
        if($this->hallazgos!=null)
        for($i = 0; $i < count($this->hallazgos); $i++)
        {
            $hallazgo = $this->hallazgos[$i];
            $color = "";
            if($i%2==0)
                $color = "#ffffff";
            else
                $color = "#f5f5f5";
                    
            $item++;
            
            $this->borders = array(1,1,1,1,1,1);
            $this->backgroundColors = array("#e6e6e6",$color,$color,$color,$color,$color);
            $this->fontWeights = array("B","","","","","");
            
            $reporte = "";
            if($hallazgo->reporte == 1)
                $reporte =  "@checked";
            else
                $reporte =  "@unchecked";
            
            $notificacion = "";
            if($hallazgo->notificacion == 1)
                $notificacion =  "@checked";
            else
                $notificacion =  "@unchecked";
           
            $this->Row2(array($item,$this->texto($hallazgo->hallazgo),$this->texto($hallazgo->recomendacion),$this->texto($hallazgo->responsableNombreCompleto), $reporte, $notificacion),8);
                        
        }
    }
    
   
}

$origin = "*";
if(isset($_SERVER['HTTP_ORIGIN']))
    $origin =$_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Content-Type: application/json; charset=UTF-8');
    header('Access-Control-Allow-Credentials: true');



$administrador_conexion = new AdministradorConexion();
$resultado =  new Resultado();
try
{
    $conexion = $administrador_conexion->abrir();
    if($conexion)
    {
       
        $repositorio = new AuditoriasRepositorio($conexion);
        //$auditoriaId = REQUEST('auditoriaId');
        //$plantillaId = REQUEST('plantillaId');
        $referencia = REQUEST('referencia');
        $hallazgos = json_decode(REQUEST('hallazgos'));
        $enviarCorreo = REQUEST('enviarCorreo');
        $asuntoCorreo = REQUEST('asuntoCorreo');
        $usuarios = json_decode(REQUEST('usuarios'));
        $usuarioXRay = REQUEST('usuarioXRay');
        $empresaId = REQUEST('empresaId');
        $sedeId = REQUEST('sedeId');
        $fecha = REQUEST('fecha');
        
        $repositorio = new UsuariosRepositorio($conexion);
        $resultado = $repositorio->consultarPorLLaves((object)["id" => $usuarioXRay]);
        if($resultado->correcto())
        {
           
            $usuario = $resultado->valor;
            $repositorio = new EmpresasRepositorio($conexion);
            $resultado = $repositorio->consultarPorLLaves((object)["id" => $empresaId]);
            if($resultado->correcto())
            {
                $empresa = $resultado->valor;
                $repositorio = new SedesRepositorio($conexion);
                $resultado = $repositorio->consultarPorLLaves((object)["id" => $sedeId]);
                if($resultado->correcto())
                {
                    $sede = $resultado->valor;
                    $reporte = new XRay();
                    $reporte->setConexion($conexion);
                    $reporte->setHallazgos($hallazgos);
                    $reporte->setReferencia($referencia);
                    $reporte->setEnviarCorreo($enviarCorreo);
                    $reporte->setUsuariosCorreo($usuarios);
                    $reporte->setAsuntoCorreo($asuntoCorreo);
                    $reporte->setUsuarioXRay($usuario);
                    $reporte->setEmpresa($empresa);
                    $reporte->setSede($sede);
                    $reporte->setFecha($fecha);
                    $reporte->AliasNbPages();
                    $reporte->generar();
                    $resultado = $reporte->imprimir();
                }
                else 
                    $resultado->mensajeError = $resultado->mensajeError . " sede: ". $sedeId;
            }
            else
                $resultado->mensajeError = $resultado->mensajeError . " empresa: ". $empresaId;
        }
        else
            $resultado->mensajeError = $resultado->mensajeError . " usuario: ". $usuarioXRay;
      
    }
}
catch(Exception $e)
{
    echo  $e->getMessage();
}
finally
{
    $administrador_conexion->cerrar($conexion);
    if($resultado!=null)
    {
        $json = json_encode($resultado, JSON_UNESCAPED_UNICODE);
        if (FALSE === $json)
            echo '{"mensajeError":"' .json_last_error_msg() . '"}';
            else
                echo $json;
    }
}


