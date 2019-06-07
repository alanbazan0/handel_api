<?php
use php\clases\AdministradorConexion;
use php\repositorios\InspeccionesRepositorio;
use php\modelos\Resultado;


require('../vendor/fpdf181/fpdf.php');
include '../clases/Utilidades.php';
include '../clases/AdministradorConexion.php';
include '../repositorios/InspeccionesRepositorio.php';
class PDF extends FPDF
{
    private $font = "Helvetica";
    private $inspeccion;
    
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
        $folio = strtoupper($this->calcularFolio());
        $area = strtoupper($this->inspeccion->areaNombre);
        $fecha= $this->inspeccion->fechaInspeccion;
        
        $this->SetLineWidth(1);
        $this->SetDrawColor(102,102,255);
        $y = 20;
        $this->Line(10, $y, 210-10, $y);
        
        $folio = strtoupper($this->calcularFolio());
        
        $logo = "../logos_empresas/logo$empresaId.png";
        $this->Image($logo,8,5,20,0,'','');
        
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
        if($this->inspeccion->tipoAreaId==1)
            $folio.="C";
            else  if($this->inspeccion->tipoAreaId==2)
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
    
    public function generar()
    {
        $this->SetFont($this->font,'',20);
        $this->AddPage();
        $this->imprimirEncabezado();
        $this->imprimirTitulo();
        $this->imprimirSubtitulo();
        $this->imprimirInspector();
        if($this->inspeccion->tipoInspeccionId==1)
            $this->imprimirInformacionTransporte17();
        else  if($this->inspeccion->tipoInspeccionId==2)
            $this->imprimirInformacionTransporte7();
        else  if($this->inspeccion->tipoInspeccionId==3)
            $this->imprimirInformacionTransporte10();
                    
                    
        if(count($this->inspeccion->puntos1)>0)
             $this->imprimirInspeccionTractor();
        if(count($this->inspeccion->puntos2)>0)
        {
            $this->AddPage();
            $this->imprimirInspeccionContenedor();
        }
        $this->AddPage();
        $this->imprimirInformacionEmbarque();
        $this->AddPage();
        $this->imprimirFotos();
        $this->AddPage();
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
        
        $this->Cell(150,10,$this->texto("FOLIO: $folio"),0,1,'R');
        $this->Cell(150,10,$this->texto("AREA: $area"),0,1,'R');
        $this->Cell(150,10,$this->texto("FECHA DE EMBARQUE: $fecha"),0,1,'R');
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
    
    function imprimirInformacionTransporte17()
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
        $y = 95;
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
        $y = 144;
        $this->Line(10, $y, 210-10, $y);
        
        $this->Ln();
        $this->SetFont($this->font, 'I', 8);
        $leyenda = "Las medidas interiores del contenedor no se muestran cuando el contenedor se encontraba sellado al momento de hacer la inspección";
        $this->Cell(170, 8, $this->texto($leyenda), $borde, 0, 'C');
                    
                    
    }
    
    function imprimirInformacionTransporte7()
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
        $y = 97;
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
        $y = 128;
        $this->Line(10, $y, 210-10, $y);
        
        $this->Ln();
        $this->SetFont($this->font, 'I', 8);
        $leyenda = "Las medidas interiores del contenedor no se muestran cuando el contenedor se encontraba sellado al momento de hacer la inspección";
        $this->Cell(170, 8, $this->texto($leyenda), $borde, 0, 'C');
                    
                    
    }
    
    function imprimirInformacionTransporte10()
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
        
        for($i = 0 ; $i < count($this->inspeccion->puntos1); $i++)
        {
            $punto = $this->inspeccion->puntos1[$i];
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
        
        for($i = 0 ; $i < count($this->inspeccion->puntos2); $i++)
        {
            $punto = $this->inspeccion->puntos2[$i];
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
        for($i = 0 ; $i < count($this->inspeccion->puntos1); $i++)
        {
            $punto = $this->inspeccion->puntos1[$i];;
            $seccion = $this->crearSeccionFotos($this->inspeccion->id,$punto);
            if(count($seccion->fotos)>0)
                array_push($seccionesFotos,$seccion);
        }
        
        $borde = 0;
        
        
        
        $anchoFoto = 50;
        $altoFoto = $anchoFoto * 40 / 30;
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
                $yFotos = $separacionY;
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
        
        $this->agregarFoto($this->inspeccion->id,"firma_chofer","FIRMA CHOFER",$fotos);
        $this->agregarFoto($this->inspeccion->id,"firma_inspector","FIRMA INSPECTOR",$fotos);
        
        //var_dump($fotos);
        
        
        $borde = 0;
        
        $anchoFoto = 50;
        $altoFoto = $anchoFoto * 40 / 30;
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
        $repositorio = new InspeccionesRepositorio($conexion);
        $inspeccionId = REQUEST('inspeccionId');
        
        $llaves= (object) [
            'id' =>  $inspeccionId
        ];
        
        
        $resultado = $repositorio->consultarPorLlaves($llaves);
        
        if($resultado->mensajeError=="")
        {
            $pdf = new PDF();
            $pdf->setInspeccion($resultado->valor);
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


