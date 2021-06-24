<?php
use php\clases\AdministradorConexion;
use php\repositorios\UsuariosRepositorio;
use php\repositorios\EvidenciasRepositorio;
use php\reportes\ReporteBase;


require_once('../vendor/fpdf181/fpdf.php');
require_once ('../clases/Utilidades.php');
require_once('../clases/AdministradorConexion.php');
require_once('../repositorios/EmpresasRepositorio.php');
require_once('../repositorios/UsuariosRepositorio.php');
require_once('../repositorios/EvidenciasRepositorio.php');
require_once('../highcharts/highchartutils.php');
require_once('../reportes/reporte_base.php');

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

class PDF extends ReporteBase
{
    //private $font = "Helvetica";
    private $modelo;
    private $empresa;
    private $secciones;
    
    private $conexion;
    private $usuario;
    private $mes;
    private $ano;
    
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
    
//     public function setEmpresa($empresa)
//     {
//         $this->empresa = $empresa;
//     }
    
//     public function setUsuario($usuario)
//     {
//         $this->usuario = $usuario;
//     }
    
//     public function setSecciones($secciones)
//     {
//         $this->secciones = $secciones;
//     }
    
    
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
    
    function getNombreMes($mes)
    {
        $nombre="";
        switch($mes)
        {
            case 1:
                $nombre = "Enero";
            break;
            case 2:
                $nombre = "Febrero";
            break;
            case 3:
                $nombre = "Marzo";
            break;
            case 4:
                $nombre = "Abril";
            break;
            case 5:
                $nombre = "Mayo";
            break;
            case 6:
                $nombre = "Junio";
            break;
            case 7:
                $nombre = "Julio";
            break;
            case 8:
                $nombre = "Agosto";
            break;
            case 9:
                $nombre = "Septiembre";
            break;
            case 10:
                $nombre = "Octubre";
            break;
            case 11:
                $nombre = "Noviembre";
            break;
            case 12:
                $nombre = "Diciembre";
            break;
        }
        return $nombre;
    }
    
    function Header()
    {
        $this->SetLineWidth(1);
        $this->SetDrawColor(63, 103, 151);
        $y = 20;
        $this->Line(10, $y, 210-10, $y);
        
        
        $logo = "../imagenes/logo_handel.jpg";
        $this->Image($logo,8,5,30,0,'','http://handel-sce.com');
        
        $this->SetY(12);
        $this->SetX(40);
        
        $mesAno = $this->getNombreMes($this->mes). " " . $this->ano;
        
        $titulo="";
        if($this->usuario->tipoUsuarioId==TipoUsuario::COORDINADOR)
            $titulo = "REPORTE DE COORDINADOR";
        else  if($this->usuario->tipoUsuarioId==TipoUsuario::SUPERVISOR)
            $titulo = "REPORTE DE SUPERVISOR";
        
        $borde = 0;
        $altoLinea = 7;
        $this->SetLeftMargin(20);
        $this->SetFont($this->font,'I',10);
        $this->SetTextColor(130,130,130);
        $this->Cell(140, $altoLinea, $this->texto($titulo ." - " . $mesAno ), $borde, 0, 'R');
        $this->SetFont($this->font,'B',11);
        //$this->SetTextColor(63,103,151);
        $this->Cell(20, $altoLinea, $this->texto("Pag. " .  $this->PageNo()), $borde, 0, 'C');
        
//         $this->SetLineWidth(0.5 );
//         $this->SetDrawColor(63, 103, 151);
//         $x = 180;
//         $this->Line($x, 13, $x, 18);
    }
    
    function Footer()
    {
        $this->SetLineWidth(1);
        $this->SetDrawColor(63, 103, 151);
        $y = 275;
        $this->Line(10, $y, 210-10, $y);
        
        $logo = "../imagenes/telefono.png";
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
        $this->Cell(120, $altoLinea, $this->texto("Prohibida la reproducción total o parcial de este documento por escrito de Handel,"), $borde, 0, 'R');
        
        $this->Ln();
        $this->SetLeftMargin(20);
        $this->SetFont($this->font,'',8);
        $this->SetTextColor(130,130,130);
        $this->Cell(60, $altoLinea ,'871 7508682 / 871 688 7317',$borde);
        $this->SetFont($this->font,'I',8);
        $this->SetTextColor(130,130,130);
        $this->Cell(120, $altoLinea, $this->texto("Servicios de Consultoría Especializada S.C."), $borde, 0, 'R');
        
        $this->Ln();
        $this->SetLeftMargin(20);
        $this->SetFont($this->font,'',8);
        $this->SetTextColor(0,0,127);
        $this->Cell(60, $altoLinea ,'mail@handel-sce.com',$borde,'','',false, "mailto:mail@handel-sce.com");
        $this->SetFont($this->font,'I',8);
        $this->SetTextColor(130,130,130);
        $this->Cell(120, $altoLinea, $this->texto(""), $borde, 0, 'R');
       
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
        $folio="";
        if($this->usuario->tipoUsuarioId==TipoUsuario::COORDINADOR)
            $folio="C";
        else if($this->usuario->tipoUsuarioId==TipoUsuario::SUPERVISOR)
            $folio="S";
            
        
        $folio .= $this->usuario->id ."_".$this->mes."_".$this->ano;
                
//         $fecha = substr($this->modelo->fechaEjecucion,0,10);
//         list($dia, $mes, $ano) = explode("/", $fecha);
//         $folio.=$dia.$mes.$ano;
                
        //$folio.="-".$this->modelo->contadorEmpresa;
        return $folio;
    }
    
    public function generar($conexion,$usuarioId, $mes, $ano)
    {
        $this->conexion = $conexion;
        
        ini_set('max_execution_time', 1000);
        ini_set('memory_limit', '50M');
        set_time_limit(0);
        
        $repositorio = new UsuariosRepositorio($conexion);
       
        
        $llaves= (object) [
            'id' =>  $usuarioId
        ];
        
        
        $resultado = $repositorio->consultarPorLlaves($llaves);
        
        
        
        if($resultado->mensajeError=="")
        {
            $this->usuario =  $resultado->valor;
            
            $this->usuario->corporativo = $repositorio->esCoordinadorCorporativo($this->usuario);
            
            
            
            $this->mes = $mes;
            $this->ano = $ano;
            $this->SetFont($this->font,'',20);
                      
            $this->encabezado();
           $this->aviso();
           $this->introduccion();
            
            
            if($this->usuario->tipoUsuarioId==TipoUsuario::COORDINADOR)
            {
               $this->graficasCoordinador();
            }
            else  if($this->usuario->tipoUsuarioId==TipoUsuario::SUPERVISOR)
            {
                $this->graficasSupervisor();
            }
                $this->evidenciasJustificadas();    
        }
        
    }
    
    private function evidenciasJustificadas()
    {
        $this->AddPage();
        $this->titulo("Resumen de justificaciones del mes");
        $this->Ln();
        $this->SetY(32);
        $borde = 1;
        
        $this->cMargin = 1;
        $this->SetLeftMargin(20);
        $this->fontSizes = array(9, 9, 9, 9, 9);
        $this->fontWeights = array("B","B","B","B","B");
        $this->aligns = array("C","C","C","C","C");
        $this->widths = array(15, 35, 50, 50, 20);
        $this->textColors = array("#000000","#000000","#000000","#000000","#000000");
        $this->borders = array(1,1,1,1,1);
        $this->borderColors = array("#afb2b0","#afb2b0","#afb2b0","#afb2b0","#afb2b0");
        $this->backgroundColors = array("#bdc1bf","#bdc1bf","#bdc1bf","#bdc1bf","#bdc1bf");
        //  $this->SetFillColor(189, 193, 191);
        $this->renglon(array("Item","Usuario","Evidencia",$this->texto("Justificación indicada en este mes"),$this->texto("No. de veces justificadas " . $this->ano)),5);
       
        $this->fontWeights = array("B","","","","");
        $this->aligns = array("C","L","L","L","C");
        
        $repositorio = new EvidenciasRepositorio($this->conexion);
        
     
        $resultado = $repositorio->consultarEvidenciasJustificadas($this->usuario,$this->mes,$this->ano);
        if($resultado->correcto())
        {
            $registros = $resultado->valor;
            for($i = 0; $i < count($registros); $i++)
            {
                $registro = $registros[$i];
                $color = "";
                if($i%2==0)
                    $color = "#ffffff";
                else
                    $color = "#f5f5f5";
                $this->backgroundColors = array("#e6e6e6",$color,$color,$color,$color);
                    
                $this->renglon(array($i+1,$this->texto($registro->usuarioNombreCompleto),$this->texto($registro->procedimientoNombre),$this->texto($registro->justificacionNombre),$registro->numeroJustificaciones),5);
            }
        }
    }
    
    private function graficasCoordinador()
    {
        $pdfWidth = 190;
        $this->AddPage();
        $this->SetLeftMargin(20);
        $this->SetRightMargin(20);
        
        $criteriosSeleccion= (object) [
            'mes' =>  $this->mes,
            'ano' =>  $this->ano,
        ];
        
       
        
        $fecha = new DateTime();
        $fecha->setDate($this->ano,$this->mes,1);
        $fecha->sub(new DateInterval('P1M'));
        
        $anoAnterior = $fecha->format("Y");
        $mesAnterior = $fecha->format("m");
        $criteriosSeleccionAnterior= (object) [
            'mes' =>   (int)$mesAnterior,
            'ano' => (int)$anoAnterior,
        ];
        
        $nombreMes = ucfirst($this->getNombreMes($this->mes));
        $nombreMesAnterior = ucfirst($this->getNombreMes($mesAnterior));
        
       
      
        
        $repositorio = new EvidenciasRepositorio($this->conexion);
        
        if($this->usuario->corporativo)
        {
            $chartWidth = 80;
            $resultado= $repositorio->consultarPorcentajesEvidencias($this->usuario, $criteriosSeleccion);
            if($resultado->correcto())
            {
                $porcentajesMes = $resultado->valor;
                $resultado= $repositorio->consultarPorcentajesEvidencias($this->usuario, $criteriosSeleccionAnterior);
                if($resultado->correcto())
                {
                    $porcentajesMesAnterior = $resultado->valor;
                    $meses = array();
                    
                    $mes = (object) [];
                    $mes->mes = $criteriosSeleccion->mes;
                    $mes->nombreMes = $this->getNombreMes($criteriosSeleccion->mes);
                    $mes->enviadas = $porcentajesMes[0]->valor;
                    $mes->pendientes = $porcentajesMes[1]->valor;
                    $mes->justificadas = $porcentajesMes[2]->valor;
                    $mes->total =  $mes->enviadas +  $mes->pendientes + $mes->justificadas;
                    $mes->porcentajeEnviadas = format($mes->enviadas * 100 /  $mes->total);
                    $mes->porcentajePendientes = format($mes->pendientes * 100 /  $mes->total);
                    $mes->porcentajeJustificadas = format($mes->justificadas * 100 /  $mes->total);
                    array_push($meses, $mes);
                    
                    $mes = (object) [];
                    $mes->mes = $criteriosSeleccionAnterior->mes;
                    $mes->nombreMes = $this->getNombreMes($criteriosSeleccionAnterior->mes);
                    $mes->enviadas = $porcentajesMesAnterior[0]->valor;
                    $mes->pendientes = $porcentajesMesAnterior[1]->valor;
                    $mes->justificadas = $porcentajesMesAnterior[2]->valor;
                    $mes->total =  $mes->enviadas +  $mes->pendientes + $mes->justificadas;
                    $mes->porcentajeEnviadas = format($mes->enviadas * 100 /  $mes->total);
                    $mes->porcentajePendientes = format($mes->pendientes * 100 /  $mes->total);
                    $mes->porcentajeJustificadas = format($mes->justificadas * 100 /  $mes->total);
                    array_push($meses, $mes);
                    
                    //var_dump($meses);
                    
                    $image = graficaBarrasMesActualAnterior("Porcentaje de cumplimiento global del área",'','Cumplimiento global',$meses,"nombreMes","porcentajeCumplimiento",true,100);
                    if($image!='')
                        $this->Image($image,20 ,40, $chartWidth);
                }
                
            }
            
            
            $resultado = $repositorio->consultarPorcentajesEmpresas($this->usuario, $criteriosSeleccion);
            if($resultado->correcto())
            {
                $porcentajes = $resultado->valor;
                $colores = [ '#00a1ff', '#60d836', '#f8ba00'];
                $image = toColumnChart("Porcentaje de cumplimiento por empresa <br>($nombreMes)",'','Areas',$porcentajes,"nombre","porcentajeCumplimiento",$colores,false,100);
                if($image!='')
                    $this->Image($image,110, 40, $chartWidth);
            }
        }
        else 
        {
            $chartWidth = 115;
            $resultado = $repositorio->consultarPorcentajesEvidencias($this->usuario, $criteriosSeleccion);
            if($resultado->correcto())
            {
                $porcentajes = $resultado->valor;
                $colores = [ "#00a65a", "#dd4b39", "#f39c12"];
                $image = toPieChartWithLabels("Porcentaje de cumplimiento global del área <br>($nombreMes)",'Porcentaje','Areas',$porcentajes,"nombre","valor",$colores,20);
                if($image!='')
                    $this->Image($image,0 ,40, $chartWidth);
            }
            
            $resultado = $repositorio->consultarPorcentajesEvidencias($this->usuario, $criteriosSeleccionAnterior);
            if($resultado->correcto())
            {
                $porcentajes = $resultado->valor;
                $colores = [ "#00a65a", "#dd4b39", "#f39c12"];
                $image = toPieChartWithLabels("Porcentaje de cumplimiento global del área <br>($nombreMesAnterior)",'Porcentaje','Areas',$porcentajes,"nombre","valor",$colores,20);
                if($image!='')
                    $this->Image($image, 95 ,40,$chartWidth);
            }
        }
        
       
        
        $this->SetLeftMargin(20);
        $this->SetX(0);
        $this->SetY(120);
        $this->SetFont($this->font,'I',9);
        $this->Cell(0, 10, $this->texto("Es aconsejable mantener el porcentaje de justificaciones (gráfica amarilla) en no más del 15%"),0,1,'C',1);
        
        
        $chartWidth= $this->w - 40;
        $resultado = $repositorio->consultarPorcentajesAreas($this->usuario, $criteriosSeleccion);
        if($resultado->correcto())
        {
            $porcentajes = $resultado->valor;
            $colores = [ '#00a1ff', '#60d836', '#f8ba00'];
            //$image = toColumnChart("Cumplimiento por departamento <br>($nombreMes)",'','Areas',$porcentajes,"nombre","porcentajeCumplimiento",$colores,false,100);
            $image = $this->graficaBarrasDepartamentos("Porcentaje de cumplimiento por departamento <br>($nombreMes)",'','Areas',$porcentajes,"nombre");
            if($image!='')
                $this->Image($image,$this->w/2 -$chartWidth/2 ,140, $chartWidth);
        }
        
        $this->AddPage();
        
        
        $pdfWidth = $this->GetPageWidth();
        $chartWidth= 170;
        $resultado = $repositorio->consultarPorcentajesUsuarios($this->usuario, $criteriosSeleccion);
        if($resultado->correcto())
        {
            $porcentajes = $resultado->valor;
            $colores = [ '#00a1ff', '#60d836', '#f8ba00'];
            //$image = toColumnChart("Porcentaje de cumplimiento <br>($nombreMes)",'','Usuarios',$porcentajes,"nombreCompleto","porcentajeCumplimiento",$colores,false,100);
            $image = $this->graficaBarrasUsuarios("Porcentaje de cumplimiento por usuario <br>($nombreMes)",'','Usuarios',$porcentajes,"nombreCompleto");
            if($image!='')
                $this->Image($image,$pdfWidth/2 -$chartWidth/2 ,25, $chartWidth);
        }
        
        
      
        $meses = array();
        for($i = 1; $i <= 12; $i++)
        {
            $mes = (object) [];
            $mes->mes = $i;
            $mes->nombreMes = $this->getNombreMes($i);
            
            $criteriosSeleccion= (object) [
                'mes' =>  $i,
                'ano' =>  $this->ano,
            ];
            
            
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
        $pdfWidth = $this->GetPageWidth();
        $chartWidth= 170;
        $colores = [ '#00a1ff', '#60d836', '#f8ba00'];
        $image = toLineChart("Nivel de riesgo anual <br>($this->ano)",'','Cumplimiento global',$meses,"nombreMes","porcentajeCumplimiento",$colores,true,100,$this->mes);
        if($image!='')
            $this->Image($image,$pdfWidth/2 -$chartWidth/2 ,135, $chartWidth);
      
        
      
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
//                         'crop'=>false,
//                         'overflow' =>'none',
//                         "inside"=> false,
                         'color'=> 'black',
                        'style'=> (object)
                        [
                            'fontSize' => 10,
                            'textOutline' => '0px'
                        ],
//                         'rotation' => $rotacion,
//                         'format'=>"{point.y:.1f} %",
                       // 'format'=>"{point.y} %",
                        'verticalAlign' => 'bottom'
                       
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
    
    private function graficasSupervisor()
    {
        $pdfWidth = 190;
        $this->AddPage();
     
        $criteriosSeleccion= (object) [
            'mes' =>  $this->mes,
            'ano' =>  $this->ano,
        ];
        
        $fecha = new DateTime();
        $fecha->setDate($this->ano,$this->mes,1);
        $fecha->sub(new DateInterval('P1M'));
        
        $anoAnterior = $fecha->format("Y");
        $mesAnterior = $fecha->format("m");
        $criteriosSeleccionAnterior= (object) [
            'mes' =>   (int)$mesAnterior,
            'ano' => (int)$anoAnterior,
        ];
        
        $nombreMes = ucfirst($this->getNombreMes($this->mes));
        $nombreMesAnterior = ucfirst($this->getNombreMes($mesAnterior));
        
        $chartWidth = 120;
        
        $repositorio = new EvidenciasRepositorio($this->conexion);
        
        $resultado = $repositorio->consultarPorcentajesEvidencias($this->usuario, $criteriosSeleccion);
        if($resultado->correcto())
        {
            $porcentajes = $resultado->valor;
            $colores = [ "#00a65a", "#dd4b39", "#f39c12"];
            $image = toPieChartWithLabels("Cumplimiento global del área <br>($nombreMes)",'Porcentaje','Areas',$porcentajes,"nombre","valor",$colores,20);
            if($image!='')
                $this->Image($image,0 ,40, $chartWidth);
        }
        
        $resultado = $repositorio->consultarPorcentajesEvidencias($this->usuario, $criteriosSeleccionAnterior);
        if($resultado->correcto())
        {
            $porcentajes = $resultado->valor;
            $colores = [ "#00a65a", "#dd4b39", "#f39c12"];
            $image = toPieChartWithLabels("Cumplimiento global del área <br>($nombreMesAnterior)",'Porcentaje','Areas',$porcentajes,"nombre","valor",$colores,20);
            if($image!='')
                $this->Image($image, 95 ,40,$chartWidth);
        }
        
        $this->SetX(0);
        $this->SetY(120);
        $this->SetFont($this->font,'I',9);
        $this->Cell(0, 10, $this->texto("Es aconsejable mantener el porcentaje de justificaciones (gráfica amarilla) en no más del 15%"),0,1,'C',1);
        
        
        $chartWidth= 100;
        $resultado = $repositorio->consultarPorcentajesUsuarios($this->usuario, $criteriosSeleccion);
        if($resultado->correcto())
        {
            $porcentajes = $resultado->valor;
            $colores = [ '#00a1ff', '#60d836', '#f8ba00'];
            //$image = toColumnChart("Porcentaje de cumplimiento <br>($nombreMes)",'','Usuarios',$porcentajes,"nombreCompleto","porcentajeCumplimiento",$colores,false,100);
            $image = $this->graficaBarrasUsuarios("Porcentaje de cumplimiento por usuario <br>($nombreMes)",'','Usuarios',$porcentajes,"nombreCompleto");
            
            if($image!='')
                $this->Image($image,$this->w/2 -$chartWidth/2 ,130, $chartWidth);
        }
        
        $resultado = $repositorio->consultarPorcentajesUsuarios($this->usuario, $criteriosSeleccionAnterior);
        if($resultado->correcto())
        {
            $porcentajes = $resultado->valor;
            $colores = [ '#00a1ff', '#60d836', '#f8ba00'];
            //$image = toColumnChart("Porcentaje de cumplimiento  <br>($nombreMesAnterior)",'','Usuarios',$porcentajes,"nombreCompleto","porcentajeCumplimiento",$colores,false,100);
            $image = $this->graficaBarrasUsuarios("Porcentaje de cumplimiento por usuario <br>($nombreMesAnterior)",'','Usuarios',$porcentajes,"nombreCompleto");
            
            if($image!='')
                $this->Image($image,$this->w/2 -$chartWidth/2 ,200, $chartWidth);
        }
        
        $this->AddPage();
        
        $chartWidth= 170;
        $pdfWidth = $this->GetPageWidth();
        
        $fecha = new DateTime();
        $fecha->setDate($this->ano,$this->mes,1);
        $mesActual = (int)$fecha->format("m");
        
        $usuarios = array();
        for($i = 1; $i <= $mesActual; $i++)
        {
            $criteriosSeleccion= (object) [
                'ano' =>  $this->ano,
                'mes' =>  $i,
            ];
            $resultado = $repositorio->consultarPorcentajesUsuarios($this->usuario, $criteriosSeleccion);
            if($resultado->correcto())
            {
                for ($j = 0; $j < count($resultado->valor); $j++) 
                {
                    $usuario = $this->getUsuario($resultado->valor[$j]->id,$usuarios);
                    if($usuario==null)
                    {
                        $nuevoUsuario= (object) [
                            'id' =>  $resultado->valor[$j]->id,
                            'nombreCompleto' =>  $resultado->valor[$j]->nombreCompleto,
                            'porcentajeCumplimiento' =>  $resultado->valor[$j]->porcentajeCumplimiento,
                        ];
                        array_push($usuarios, $nuevoUsuario);
                    }
                    else
                    {
                        $usuario->porcentajeCumplimiento += $resultado->valor[$j]->porcentajeCumplimiento;
                    }
                }
               
            }
        }
        for($i = 0; $i < count($usuarios); $i++)
        {
            $usuario =$usuarios[$i];
            $usuario->porcentajeCumplimiento = $usuario->porcentajeCumplimiento/$mesActual;
            $usuario->porcentajeCumplimiento =  number_format( $usuario->porcentajeCumplimiento, 1, '.', '');
        }
        
        
        $colores = [ '#00a1ff', '#60d836', '#f8ba00'];
        $image = $this->graficaBarrasUsuarios("Porcentaje de cumplimiento en el año <br>($this->ano)",'','Usuarios',$porcentajes,"nombreCompleto");
        
        //$image = toColumnChart("Porcentaje de cumplimiento en el año <br>($this->ano)",'','Usuarios',$usuarios,"nombreCompleto","porcentajeCumplimiento",$colores,false,100);
        if($image!='')
            $this->Image($image,$this->w/2 -$chartWidth/2 ,30, $chartWidth);
    
        
        $fecha = new DateTime();
      
        $meses = array();
        for($i = 1; $i <= 12; $i++)
        {
            $mes = (object) [];
            $mes->mes = $i;
            $mes->nombreMes = $this->getNombreMes($i);
            
            $criteriosSeleccion= (object) [
                'mes' =>  $i,
                'ano' =>  $this->ano,
            ];
            
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
       
        
        $colores = [ '#00a1ff', '#60d836', '#f8ba00'];
        $image = toLineChart("Nivel de riesgo anual <br>($this->ano)",'','Cumplimiento global',$meses,"nombreMes","porcentajeCumplimiento",$colores,true,100,$this->mes);
        if($image!='')
            $this->Image($image,$pdfWidth/2 -$chartWidth/2 ,140, $chartWidth);
        
            
      
        
    }
    
    private function getUsuario($usuarioId,$usuarios)
    {
        for($i = 0; $i < count($usuarios); $i++)
        {
            $usuario =$usuarios[$i];
            if($usuario->id == $usuarioId)
                return $usuario;
            
        }
        return null;
    }
    
    public function guardar()
    {
        $filename ="../reportes_evidencia/";
        $filename.=$this->calcularFolio();
        $filename.=".pdf";
        
        //var_dump($this->modelo);
        
        $this->Output($filename,'F');
    }
    
    public function abrir()
    {
        $filename ="../reportes_evidencia/";
        $filename.=$this->calcularFolio();
        $filename.=".pdf";
        if(file_exists($filename))
        {
           header('Location: '.$filename);
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
    
    function aviso()
    {
        $this->AddPage();
       // $this->SetY(20);
       // $this->SetX(20);
       
        $this->SetY(145);
        
        $borde = 0;
        $w1 = 85;
        $w2 = 85;
        
        //$this->imprimirTituloHoja("II. Metodología");
        
        $this->Ln();
        $this->SetLeftMargin(30);
        $this->SetFont($this->font, '', 10);
        $this->SetFillColor(242, 242, 242);
        $this->SetTextColor(130,130,130);
        //$this->cMargin=10;
        //Print 2 Cells
        $tamanoLinea = 6;
        $this->Ln();
        
        $this->Cell(150,$tamanoLinea,'',$borde,1,'L',0);
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(150,$tamanoLinea,$this->texto('Aviso'),$borde,1,'L',0);
        //$this->Cell(150,$tamanoLinea,'',$borde,1,'L',0);
        $this->SetFont($this->font, '', 10);
        $this->SetFont($this->font, 'I', 10);
        $this->Cell(150,$tamanoLinea,$this->texto('     El presente reporte incluye un resumen de las evidencias entregadas por cada'),$borde,1,'FJ',0);
        $this->Cell(150,$tamanoLinea,$this->texto('persona involucrada en la certificación utilizando el sistema SAHA, es importante que la'),$borde,1,'FJ',0);
        $this->Cell(150,$tamanoLinea,$this->texto('alta gerencia tenga disponible esta información a fin de que se promueva la activa'),$borde,1,'FJ',0);
        $this->Cell(150,$tamanoLinea,$this->texto('participación del equipo de trabajo a fin de mantener un estándar que garantice el objetivo'),$borde,1,'FJ',0);
        $this->Cell(150,$tamanoLinea,$this->texto('de la certificación.'),$borde,1,'L',0);
        
        $this->Ln();
        
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(150,$tamanoLinea,$this->texto('Aviso de privacidad'),$borde,1,'L',0);
        $this->SetFont($this->font, 'I', 10);
        //$this->Cell(150,$tamanoLinea,'',$borde,1,'L',0);
        $this->Cell(150,$tamanoLinea,$this->texto('     Las evidencias subidas a SAHA son protegidas por nuestro aviso de privacidad entendiendo'),$borde,1,'FJ',0);
        $this->Cell(150,$tamanoLinea,$this->texto('que las mismas son utilizadas exclusivamente para realizar una evaluación en el entorno'),$borde,1,'FJ',0);
        $this->Cell(150,$tamanoLinea,$this->texto('de la certificación. El cliente y sus trabajadores aceptan que el contenido del mismo es'),$borde,1,'FJ',0);
        $this->Cell(150,$tamanoLinea,$this->texto('para efectos de evaluación y trabajo sensible dentro de la organización por lo que se prohibe'),$borde,1,'FJ',0);
        $this->Cell(150,$tamanoLinea,$this->texto('su libre distribución a personal que no corresponda al equipo de trabajo de Handel o de su propia'),$borde,1,'FJ',0);
        $this->Cell(150,$tamanoLinea,$this->texto('compañia.'),$borde,1,'L',0);
        
      
    }
    
    function introduccion()
    {
        $this->AddPage();
        // $this->SetY(20);
        // $this->SetX(20);
        $borde = 0;
        $w1 = 85;
        $w2 = 85;
        
        //$this->imprimirTituloHoja("II. Metodología");
        
        $this->Ln();
        $this->SetLeftMargin(30);
        $this->SetFont($this->font, '', 10);
        $this->SetFillColor(242, 242, 242);
        $this->SetTextColor(0,0,0);
        //$this->cMargin=10;
        //Print 2 Cells
        $tamanoLinea = 6;
        
        
        $titulo="";
        if($this->usuario->tipoUsuarioId==TipoUsuario::COORDINADOR)
            $titulo = "coordinación";
        else  if($this->usuario->tipoUsuarioId==TipoUsuario::SUPERVISOR)
            $titulo = "supervisor";
        
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(150,$tamanoLinea,$this->texto('Introducción'),$borde,1,'L',0);
        $this->Cell(150,$tamanoLinea,'',$borde,1,'L',0);
        $this->SetFont($this->font, '', 10);
        $this->Cell(150,$tamanoLinea,$this->texto('     El presente reporte incluye las estadísticas de entrega de evidencia por parte del'),$borde,1,'FJ',0);
        $this->Cell(150,$tamanoLinea,$this->texto('equipo de trabajo de la certificación en su compañia, pretendemos sea una guía que muestre'),$borde,1,'FJ',0);
        $this->Cell(150,$tamanoLinea,$this->texto('de manera rápida y gráfica el estado actual de la certifiación en el mes que se indica.'),$borde,1,'FJ',0);
        $this->Cell(150,$tamanoLinea,$this->texto('En Handel es importante para nosotros el que estos reportes nos sirvan para identificar áreas'),$borde,1,'FJ',0);
        $this->Cell(150,$tamanoLinea,$this->texto("de oportunidad. El reporte de $titulo se sugiere sea revisado periódiamente en las"),$borde,1,'FJ',0);
        $this->Cell(150,$tamanoLinea,$this->texto('sesiones de comité de seguridad y derivado de ello se realicen las mejoras que sean necesarias.'),$borde,1,'FJ',0);
        
        $this->Ln();
        $this->Cell(150,$tamanoLinea,$this->texto('     Hemos incluido en este reporte las gráficas suficientes para que en un solo documento se'),$borde,1,'FJ',0);
        $this->Cell(150,$tamanoLinea,$this->texto('tenga disponible toda la información pertinente, si desea gráficas adicionales puede'),$borde,1,'FJ',0);
        $this->Cell(150,$tamanoLinea,$this->texto('verificarlas en el menú de gráficas de SAHA desde su propia cuenta asignada.'),$borde,1,'L',0);
        
        $this->Ln();
        $this->Cell(150,$tamanoLinea,$this->texto('     Nuestro equipo de trabajo espera que esta información le sea de utilidad y adecuada a sus'),$borde,1,'FJ',0);
        $this->Cell(150,$tamanoLinea,$this->texto('requerimientos y que obtenga  de el los parámetros necesarios para mantener la tranquilidad'),$borde,1,'FJ',0);
        $this->Cell(150,$tamanoLinea,$this->texto('de que su certificación esta operando como fue diseñada. Esperamos distrute este reporte tanto'),$borde,1,'L',0);
        $this->Cell(150,$tamanoLinea,$this->texto('como nosotros elaborándolo.'),$borde,1,'L',0);
        
        $this->Ln();
        $this->Cell(150,$tamanoLinea,$this->texto('¡Gracias por su preferencia!'),$borde,1,'L',0);
        
        $this->Ln();
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(150,$tamanoLinea,$this->texto('El equipo de trabajo de Handel Consultoria'),$borde,1,'C',0);
    }
    
//     function graficosCompania()
//     {
//         $this->AddPage();
// //         $this->SetY(20);
// //         $this->SetX(20);

//         $this->imprimirTituloHoja("III. Gráficos de la compañia");
//         $borde = 0;
//         $w1 = 60;
//         $w2 = 110;
        
// //         //Titulo metodologia
// //         $this->SetTextColor(63,103,151);
// //         $this->SetDrawColor(118, 159, 209);
        
// //         $this->SetLeftMargin(20);
// //         $this->SetFont($this->font, '', 10);
// //         $this->Cell(170, 10,$this->texto("III. Gráficos de la compañia"), 'B', 0, 'L');
        
//         $this->Ln();
//         $this->Ln();
//         $this->cMargin=5;
//         $this->SetFillColor(242, 242, 242);
//         $this->SetLeftMargin(20);
//         $this->SetTextColor(0, 0, 0);
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell($w1, 10,$this->texto("Resultados en su compañia"), $borde, 0, 'L',1);
//         $this->SetFont($this->font, '', 10);
//         $this->Cell($w2, 10, $this->texto("Es el resultado de la evaluación realizada por Händel"), $borde, 0, 'L',1);
       
//         $this->Ln();
//         $this->cMargin=5;
//         $this->SetFillColor(242, 242, 242);
//         $this->SetLeftMargin(20);
//         $this->SetTextColor(0, 0, 0);
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell($w1, 10,$this->texto("País"), $borde, 0, 'L',1);
//         $this->SetFont($this->font, '', 10);
//         $this->Cell($w2, 10, $this->texto("Puntaje promedio de empresas evaluadas en el país"), $borde, 0, 'L',1);
        
//         $this->Ln();
//         $this->cMargin=5;
//         $this->SetFillColor(242, 242, 242);
//         $this->SetLeftMargin(20);
//         $this->SetTextColor(0, 0, 0);
//         $this->SetFont($this->font, 'B', 10);
//         $this->Cell($w1, 10,$this->texto("Sector de la industria"), $borde, 0, 'L',1);
//         $this->SetFont($this->font, '', 10);
//         $this->Cell($w2, 10, $this->texto("Puntaje promedio de empresas evaluadas en el sector especifco "), $borde, 0, 'L',1);
       
//     }
    
//     function imprimirTituloHoja($titulo)
//     {
//         $this->SetY(20);
//         $this->SetX(20);
//         $this->SetTextColor(63,103,151);
//         $this->SetDrawColor(118, 159, 209);
//         $this->SetLeftMargin(20);
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(170, 10,$this->texto($titulo), 'B', 0, 'L');
//     }
    
//     function aplicacionResultados()
//     {
//         $this->AddPage();
//         $this->imprimirTituloHoja("IV. Aplicación de resultados");

//         $borde = 0;
//         $this->Ln();
//         $this->Ln();
//         $this->cMargin=10;
//         $this->SetFillColor(242, 242, 242);
//         $this->SetLeftMargin(20);
//         $this->SetTextColor(0, 0, 0);
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(170, 6,$this->texto("En esta seccion aparecerá un comparativo de las gráficas conforme se avance en el"), $borde, 1, 'FJ',1);
//         $this->Cell(170, 6,$this->texto("paquete de mantenimiento contratado con Händel SCE."), $borde, 1, 'L',1);
        
       
//     }
    
//     function comparacionGlobal()
//     {
//         $this->AddPage();
//         $this->imprimirTituloHoja("V. Comparación global de referencia");
        
//         $borde = 0;
//         $this->Ln();
//         $this->Ln();
//         $this->cMargin=10;
//         $this->SetFillColor(242, 242, 242);
//         $this->SetLeftMargin(20);
//         $this->SetTextColor(0, 0, 0);
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(170, 6,$this->texto("Ilustra el estado actual de la compañía en los puntos básicos de seguridad del programa"), $borde, 1, 'FJ',1);
//         $this->Cell(170, 6,$this->texto("C-TPAT	referente a empresas de transporte."), $borde, 1, 'L',1);
        
//     }
    
    
//     function observaciones()
//     {
//         $this->AddPage();
//         $this->imprimirTituloHoja("VI. Observaciones, acciones y recomendaciones");
        
//         $borde = 0;
//         $this->Ln();
//         $this->Ln();
//         $this->cMargin=10;
//         $this->SetFillColor(242, 242, 242);
//         $this->SetLeftMargin(20);
//         $this->SetTextColor(0, 0, 0);
//         $this->SetFont($this->font, '', 10);
//         $this->Cell(170, 6,$this->texto("Se	identifican los aspectos encontrados durante la inspección realizada."), $borde, 1, 'L',1);
        
//     }
    
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
        $this->Cell(170, 6,$this->texto("Se	listan a continuación incidentes menores observados durante	la visita de inspección."), $borde, 1, 'L',1);
        
    }
    
    
    
    function encabezado()
    {
        $this->AddPage();
        $this->SetLeftMargin(20);
        $this->SetRightMargin(20);
        $imagen = "../imagenes/logo_saha.png";
        $anchoFoto = 100;
        $x = (210/2) - ($anchoFoto/2);
        $y = 45;
        $this->Image($imagen,$x,$y,$anchoFoto);
        
        //$mesAno = $this->getNombreMes($this->mes). " " . $this->ano;
        
        $this->SetY(100);
        //$this->SetX(20);
        $this->SetFont($this->font,'B',15);
        $this->SetTextColor(255, 255, 255);
        $this->SetFillColor(8, 13, 66);
        
        $titulo="";
        if($this->usuario->tipoUsuarioId==TipoUsuario::COORDINADOR)
        {
            $titulo = "REPORTE DE COORDINADOR";
            if($this->usuario->corporativo)
                $titulo.= " CORPORATIVO";
        }
        else  if($this->usuario->tipoUsuarioId==TipoUsuario::SUPERVISOR)
            $titulo = "REPORTE DE SUPERVISOR";
        $this->Cell(0, 15, $this->texto($titulo),0,1,'C',1);       

        $borde = 0;
        $w1 = 50;
        $w2 = 100; 
        
        $tipoUsuario = "";
        if($this->usuario->tipoUsuarioId == TipoUsuario::SUPERVISOR)
            $tipoUsuario = "Supervisor";
        else
            $tipoUsuario = "Coordinador";
        //Coordinador
        $this->Ln();
        $this->SetLeftMargin(30);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->font, 'B', 10);
        $this->Cell($w1, 10,$this->texto($tipoUsuario), $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell($w2, 10, $this->texto($this->usuario->nombreCompleto), $borde, 0, 'L');
        //Empresa
        $this->Ln();
        $this->SetLeftMargin(30);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->font, 'B', 10);
        $this->Cell($w1, 10,$this->texto("Compañia:"), $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell($w2, 10, $this->texto($this->usuario->empresaNombre), $borde, 0, 'L');
        
        $this->Ln();
        $this->SetLeftMargin(30);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->font, 'B', 10);
        $this->Cell($w1, 10,$this->texto("Mes:"), $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell($w2, 10, $this->texto($this->getNombreMes($this->mes)), $borde, 0, 'L');
       
        //Fecha
        $this->Ln();
        $this->SetLeftMargin(30);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->font, 'B', 10);
        $this->Cell($w1, 10,$this->texto("Año"), $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell($w2, 10, $this->texto($this->ano), $borde, 0, 'L');
        
        
      
        
        $this->SetDrawColor(130,130,130);
        $y = 140;
        $this->Line(30, $y, 210-30, $y);
        $y = 150;
        $this->Line(30, $y, 210-30, $y);
        $y = 160;
        $this->Line(30, $y, 210-30, $y);
        
        //Puntuacion
        
        $repositorio = new EvidenciasRepositorio($this->conexion);
        $criteriosSeleccion= (object) [
            'mes' =>  $this->mes,
            'ano' =>  $this->ano,
        ];
        
        $borde = 0;
        $this->SetLeftMargin(10);
        $this->SetX(0);
        $resultado = $repositorio->consultarPorcentajesEvidencias($this->usuario, $criteriosSeleccion);
        if($resultado->correcto())
        {
            $porcentajes = $resultado->valor;
            $total = $porcentajes[0]->valor + $porcentajes[1]->valor +$porcentajes[2]->valor; 
            $cumplimiento = $porcentajes[0]->valor + $porcentajes[2]->valor;
            $porcentajeCumplimiento = 0;
            if($total!=0)
                $porcentajeCumplimiento = $cumplimiento * 100 / $total;
            
            //$porcentajeCumplimiento=    number_format($porcentajeCumplimiento, 1, '.', '');
            $porcentajeCumplimiento = bcdiv($porcentajeCumplimiento, '1', 1);
            
            list($enteros, $decimales) = explode(".", $porcentajeCumplimiento);
            if($decimales=="0")
                $porcentajeCumplimiento = str_replace(".$decimales","",$porcentajeCumplimiento);
            
            $this->SetY(200);
            $this->SetFont($this->font, 'B', 15);
            $this->Cell(0, 10, $this->texto("$porcentajeCumplimiento% de cumplimiento en el mes"), $borde, 0, 'C');
        }
        
        $this->SetFont($this->font, '', 12);
        $resultado = $repositorio->consultarPorcentajesEmpresas($this->usuario, $criteriosSeleccion);
        if($resultado->correcto())
        {
            $empresas = $resultado->valor;
            
            for($i = 0; $i < count($empresas); $i++)
            {
                $empresa = $empresas[$i];
                $this->Ln();
                $this->Cell(0, 10, $this->texto($empresa->nombre." ".$empresa->porcentajeCumplimiento ."%"), $borde, 0, 'C');
            }
        }
       
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
            'color' => "#00a65a"
        ];
        
        $newRow2= (object) [
            'name' =>  $row->$xField,
            'y' => (float)$row->porcentajePendientes,
            'color' => "#f39c12"
            
        ];
        
        $newRow3= (object) [
            'name' =>  $row->$xField,
            'y' => (float)$row->porcentajeJustificadas,
            'color' => "#dd4b39"
            
            
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
            (object) ['name' => "Enviadas", 'data' => $data1,  'showInLegend' => $showInLegend, "color"=>"#60d836"],
            (object) ['name' => "Pendientes", 'data' => $data2,  'showInLegend' => $showInLegend, "color"=>"#fe2500"],
            (object) ['name' => "Justificadas", 'data' => $data3,  'showInLegend' => $showInLegend, "color"=>"#f9c320"]
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

function format($valor)
{
    $valor = bcdiv($valor, '1', 1);
    
    list($enteros, $decimales) = explode(".", $valor);
    if($decimales=="0")
        $valor = str_replace(".$decimales","",$valor);
    return $valor;
}


$administrador_conexion = new AdministradorConexion();
try
{
    $conexion = $administrador_conexion->abrir();
    if($conexion)
    {
        $usuarioId = REQUEST('usuarioId');
        $mes =(int) REQUEST('mes');
        $ano =(int) REQUEST('ano');
        if($usuarioId!="")
        {
            if($mes!=0 && $ano!=0)
            {
                 $pdf = new PDF();
                 $pdf->AliasNbPages();
                 $pdf->generar($conexion,$usuarioId,$mes,$ano);
                 $pdf->guardar();
                  $pdf->abrir();
            }
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
?>