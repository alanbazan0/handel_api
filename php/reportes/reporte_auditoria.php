<?php
use php\clases\AdministradorConexion;
use php\repositorios\AuditoriasRepositorio;
use php\modelos\Resultado;


require('../vendor/fpdf181/fpdf.php');
include '../clases/Utilidades.php';
include '../clases/AdministradorConexion.php';
include '../repositorios/AuditoriasRepositorio.php';
class PDF extends FPDF
{
    private $font = "Helvetica";
    private $modelo;
    
    function setModelo($modelo)
    {
        $this->modelo = $modelo;
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
        $this->AddPage();
        $this->imprimirEncabezado();
//         $this->imprimirTitulo();
//         $this->imprimirSubtitulo();
//         $this->imprimirInspector();
//         if($this->modelo->tipoInspeccionId==1)
//             $this->imprimirInformacionTransporte17();
//         else  if($this->modelo->tipoInspeccionId==2)
//             $this->imprimirInformacionTransporte7();
//         else  if($this->modelo->tipoInspeccionId==3)
//             $this->imprimirInformacionTransporte10();
                    
                    
//         if(count($this->modelo->puntos1)>0)
//              $this->imprimirInspeccionTractor();
//         if(count($this->modelo->puntos2)>0)
//         {
//             $this->AddPage();
//             $this->imprimirInspeccionContenedor();
//         }
//         $this->AddPage();
//         $this->imprimirFotos();
                        
                        
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
    
    function imprimirEncabezado()
    {
        $imagen = "../imagenes/encabezado_sivah.jpg";
        $anchoFoto = 120;
        $x = (210/2) - ($anchoFoto/2);
        $y = 30;
        $this->Image($imagen,$x,$y,$anchoFoto);
//         $empresaId = $this->modelo->empresaId;
//         $folio = strtoupper($this->calcularFolio());
//         //$area = strtoupper($this->modelo->areaNombre);
//         $fecha= $this->modelo->fechaEjecucion;
        
//         $logo = "../logos_empresas/logo$empresaId.png";
//         if (file_exists($logo))
//             $this->Image($logo,10,12,40,0,'','http://apps-handel.com');
//             else
//                 $this->Image("default.png",10,12,40,0,'','http://apps-handel.com');
//         $this->SetLeftMargin(45);
//         $this->SetFontSize(11);
        
//         $this->Cell(150,10,$this->texto("FOLIO: $folio"),0,1,'R');
//         $this->Cell(150,10,$this->texto("AREA: "),0,1,'R');
//         $this->Cell(150,10,$this->texto("FECHA DE EMBARQUE: $fecha"),0,1,'R');
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
        $entrada_salida= $this->modelo->entradaSalida;
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
        $inspectorNombre = strtoupper($this->modelo->inspectorNombre);
        //$fechaInicio = $this->formatoFecha($this->modelo->fechaInspeccion);
        //$fechaFinalizacion= $this->formatoFecha($this->modelo->fechaFinalizacion);
        
        $fechaInicio = $this->modelo->fechaInspeccion;
        $fechaFinalizacion= $this->modelo->fechaFinalizacion;
        
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
    
    function imprimirInformacionTransporte17()
    {
        $transportista = strtoupper($this->modelo->transportista);
        $chofer = strtoupper($this->modelo->chofer);
        $numeroTractor = $this->modelo->numeroTractor;
        $numeroCaja = $this->modelo->numeroCaja;
        $colorTractor = strtoupper($this->modelo->colorTractor);
        $colorCaja = strtoupper($this->modelo->colorCaja);
        $numeroContenedor = $this->modelo->numeroContenedor;
        $tipoCaja = strtoupper($this->modelo->tipoCaja);
        $sello =  $this->modelo->sello;
        $selloViajero =  $this->modelo->selloViajero;
        $alto = $this->modelo->alto;
        $ancho = $this->modelo->ancho;
        $profundidad = $this->modelo->profundidad;
        
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
        $y = 80;
        $this->Line(10, $y, 210-10, $y);
        
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
        
        $this->Cell(0,8,$this->texto("Vehículo "),$borde,1,'C');
        $this->SetDrawColor(191,191,191);
        $y = 93;
        $this->Line(10, $y, 210-10, $y);
        
        
        $this->SetLeftMargin(20);
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(30, 8, "No. Tractor:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto($numeroTractor), $borde, 0, 'L');
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(30, 8, "No. Caja:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto($numeroCaja), $borde, 0, 'L');
        
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(30, 8, "Placas Tractor:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto("580AT4"), $borde, 0, 'L');
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(30, 8, "Placas Caja:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto("P425905"), $borde, 0, 'L');
        
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(30, 8, "Color Tractor:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto($colorTractor), $borde, 0, 'L');
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(30, 8, "Color Caja:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto($colorCaja), $borde, 0, 'L');
        
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(30, 8, "No. Contenedor:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto($numeroContenedor), $borde, 0, 'L');
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(30, 8, "Tipo Caja:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto($tipoCaja), $borde, 0, 'L');
        
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(30, 8, "Sello:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto($sello), $borde, 0, 'L');
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(30, 8, "Sello viajero:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto($selloViajero), $borde, 0, 'L');
        
        $this->SetLeftMargin(10);
        $this->SetFont($this->font, 'B', 13);
        $this->Ln();
        $this->SetFillColor(242, 242, 242);
        $this->Cell(0,8,$this->texto("DIMENSIONES DEL CONTENEDOR"),$borde,2,'C',1);
        
        $this->SetLeftMargin(20);
        $this->Ln();
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
        $y = 142;
        $this->Line(10, $y, 210-10, $y);
        
        $this->Ln();
        $this->SetFont($this->font, 'I', 8);
        $leyenda = "Las medidas interiores del contenedor no se muestran cuando el contenedor se encontraba sellado al momento de hacer la inspección";
        $this->Cell(170, 8, $this->texto($leyenda), $borde, 0, 'C');
                    
                    
    }
    
    function imprimirInformacionTransporte7()
    {
        $transportista = strtoupper($this->modelo->transportista);
        $chofer = strtoupper($this->modelo->chofer);
        $numeroTractor = $this->modelo->numeroTractor;
        $numeroCaja = $this->modelo->numeroCaja;
        $colorTractor = strtoupper($this->modelo->colorTractor);
        $colorCaja = strtoupper($this->modelo->colorCaja);
        $numeroContenedor = $this->modelo->numeroContenedor;
        $tipoCaja = strtoupper($this->modelo->tipoCaja);
        $sello =  $this->modelo->sello;
        $selloViajero =  $this->modelo->selloViajero;
        $alto = $this->modelo->alto;
        $ancho = $this->modelo->ancho;
        $profundidad = $this->modelo->profundidad;
        
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
        $y = 80;
        $this->Line(10, $y, 210-10, $y);
        
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
        
        $this->Cell(0,8,$this->texto("Vehículo "),$borde,1,'C');
        $this->SetDrawColor(191,191,191);
        $y = 93;
        $this->Line(10, $y, 210-10, $y);
        
        
        $this->SetLeftMargin(20);
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(30, 8, "No. Caja:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto($numeroCaja), $borde, 0, 'L');
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(30, 8, "Tipo caja:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto($tipoCaja), $borde, 0, 'L');
        
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(30, 8, "Color caja:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto($colorCaja), $borde, 0, 'L');
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(30, 8, "Sello colocado:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto(""), $borde, 0, 'L');
        
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(30, 8, "Sello retirado:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto(""), $borde, 0, 'L');
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(30, 8, $this->texto("Inspección aleatoria:"), $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto(""), $borde, 0, 'L');
        
        
        $this->SetLeftMargin(10);
        $this->SetFont($this->font, 'B', 13);
        $this->Ln();
        $this->SetFillColor(242, 242, 242);
        $this->Cell(0,8,$this->texto("DIMENSIONES DEL CONTENEDOR"),$borde,2,'C',1);
        
        $this->SetLeftMargin(20);
        $this->Ln();
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
        $y = 126;
        $this->Line(10, $y, 210-10, $y);
        
        $this->Ln();
        $this->SetFont($this->font, 'I', 8);
        $leyenda = "Las medidas interiores del contenedor no se muestran cuando el contenedor se encontraba sellado al momento de hacer la inspección";
        $this->Cell(170, 8, $this->texto($leyenda), $borde, 0, 'C');
                    
                    
    }
    
    function imprimirInformacionTransporte10()
    {
        $transportista = strtoupper($this->modelo->transportista);
        $chofer = strtoupper($this->modelo->chofer);
        $numeroTractor = $this->modelo->numeroTractor;
        $numeroCaja = $this->modelo->numeroCaja;
        $colorTractor = strtoupper($this->modelo->colorTractor);
        $colorCaja = strtoupper($this->modelo->colorCaja);
        $numeroContenedor = $this->modelo->numeroContenedor;
        $tipoCaja = strtoupper($this->modelo->tipoCaja);
        $sello =  $this->modelo->sello;
        $selloViajero =  $this->modelo->selloViajero;
        $alto = $this->modelo->alto;
        $ancho = $this->modelo->ancho;
        $profundidad = $this->modelo->profundidad;
        
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
        $y = 80;
        $this->Line(10, $y, 210-10, $y);
        
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
        
        $this->Cell(0,8,$this->texto("Vehículo "),$borde,1,'C');
        $this->SetDrawColor(191,191,191);
        $y = 93;
        $this->Line(10, $y, 210-10, $y);
        
        
        $this->SetLeftMargin(20);
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(30, 8, "No. Tractor:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto($numeroTractor), $borde, 0, 'L');
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(30, 8, "Sello viajero:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto($selloViajero), $borde, 0, 'L');
        
        $this->Ln();
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(30, 8, "Placas Tractor:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto("580AT4"), $borde, 0, 'L');
        $this->SetFont($this->font, 'B', 10);
        $this->Cell(30, 8, "Color Tractor:", $borde, 0, 'L');
        $this->SetFont($this->font, '', 10);
        $this->Cell(55, 8, $this->texto($colorTractor), $borde, 0, 'L');
        
//         $this->SetLeftMargin(10);
//         $this->SetFont($this->font, 'B', 13);
//         $this->Ln();
//         $this->SetFillColor(242, 242, 242);
//         $this->Cell(0,8,$this->texto("DIMENSIONES DEL CONTENEDOR"),$borde,2,'C',1);
        
//         $this->SetLeftMargin(20);
//         $this->Ln();
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
//         $y = 118;
//         $this->Line(10, $y, 210-10, $y);
        
//         $this->Ln();
//         $this->SetFont($this->font, 'I', 8);
//         $leyenda = "Las medidas interiores del contenedor no se muestran cuando el contenedor se encontraba sellado al momento de hacer la inspección";
//         $this->Cell(170, 8, $this->texto($leyenda), $borde, 0, 'C');
                    
                    
    }
    
    function imprimirInspeccionTractor()
    {
        $borde = 0;
        $anchoColumna1 = 48;
        $anchoColumna2 = 30;
        $anchoColumna3 = 112;
        
        $this->SetLeftMargin(10);
        $this->SetFont($this->font, 'B', 13);
        $this->Ln();
        $this->SetFillColor(242, 242, 242);
        $this->Cell(0,8,$this->texto("INSPECCIÓN DE TRACTOR"),$borde,2,'C',1);
        
        
        $this->SetFont($this->font, 'B', 10);
        $this->Cell($anchoColumna1, 8, $this->texto("Descripción"), $borde, 0, 'C', 1);
        $this->Cell($anchoColumna2, 8, "Resultado", $borde, 0, 'C', 1);
        $this->Cell($anchoColumna3, 8, "Observaciones", $borde, 0, 'C', 1);
        
        for($i = 0 ; $i < count($this->modelo->puntos1); $i++)
        {
            $punto = $this->modelo->puntos1[$i];
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
            
            
            $this->Cell($anchoColumna2, 8, $resultado, $borde, 0, 'C');
            $this->SetFont($this->font, '', 9);
            $this->Cell($anchoColumna3, 8, $observaciones, $borde, 0, 'L');
        }
        
        $this->SetDrawColor(0,0,0);
        $y = 173;
       // $this->Line(10, $y, 210-10, $y);
        
    }
    
    function imprimirInspeccionContenedor()
    {
        $borde = 0;
        $anchoColumna1 = 48;
        $anchoColumna2 = 30;
        $anchoColumna3 = 112;
        
        $this->SetLeftMargin(10);
        $this->SetFont($this->font, 'B', 13);
        $this->Ln();
        $this->SetFillColor(242, 242, 242);
        $this->Cell(0,8,$this->texto("INSPECCIÓN DE CONTENEDOR"),$borde,2,'C',1);
        
        $this->SetFont($this->font, 'B', 10);
        $this->Cell($anchoColumna1, 8, $this->texto("Descripción"), $borde, 0, 'C', 1);
        $this->Cell($anchoColumna2, 8, "Resultado", $borde, 0, 'C', 1);
        $this->Cell($anchoColumna3, 8, "Observaciones", $borde, 0, 'C', 1);
        
        for($i = 0 ; $i < count($this->modelo->puntos2); $i++)
        {
            $punto = $this->modelo->puntos2[$i];
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
            
            
            $this->Cell($anchoColumna2, 8, $resultado, $borde, 0, 'C');
            $this->SetFont($this->font, '', 9);
            $this->Cell($anchoColumna3, 8, $observaciones, $borde, 0, 'L');
        }
        
        $this->SetDrawColor(0,0,0);
        $y = 26;
        $this->Line(10, $y, 210-10, $y);
        
        
    }
    
    function imprimirFotos()
    {
        $seccionesFotos = array();
        for($i = 0 ; $i < count($this->modelo->puntos1); $i++)
        {
            $punto = $this->modelo->puntos1[$i];;
            $seccion = $this->crearSeccionFotos($this->modelo->id,$punto);
            if(count($seccion->fotos)>0)
                array_push($seccionesFotos,$seccion);
        }
        
        $borde = 0;
        
        
        
        $anchoFoto = 50;
        $altoFoto = $anchoFoto * 40 / 30;
        $separacionX =  5;
        $separacionY = 20;
        
        $yFotos = $separacionY;
        
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
                $yFotos = $separacionY;
                $this->AddPage();
            }
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
             $pdf = new PDF();
             $pdf->setModelo($resultado->valor);
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


