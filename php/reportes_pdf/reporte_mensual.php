<?php
use php\clases\AdministradorConexion;
use php\repositorios\EmpresasRepositorio;
require_once('../reportes_pdf/reporte_kci.php');
require_once('../clases/AdministradorConexion.php');
require_once('../repositorios/EmpresasRepositorio.php');

 class ReporteMensual extends ReporteKCI
{
    public function setImagenes($imagenSimulacros, $imagenNovedades)
    {
        $this->imagenSimulacros = $imagenSimulacros;
        $this->imagenNovedades = $imagenNovedades;
    }
    
    protected function calcularFolio()
    {
        $folio="ReporteMensual";
        if($this->empresa!=null)
            $folio.="-".$this->empresa->nombreCorto;
        $folio.="-" . $this->mes ."-". $this->ano;
            
        return $folio;
    }
    
    public function generar($usuario,$criteriosSeleccion)
    {
        $this->criteriosSeleccion = $criteriosSeleccion;
        $this->usuario = $usuario;
        if($this->criteriosSeleccion!=null)
        {
            
            $empresasRepositorio = new EmpresasRepositorio($this->conexion);
            $resultado = $empresasRepositorio->consultarPorLlaves((object)["id" => $criteriosSeleccion->empresaId]);
            if($resultado->correcto())
            {
                $this->empresa = $resultado->valor;
                
                $this->dia = date("d");
                $this->mes = date("m");
                $this->ano = date("Y");
                
                /*   $this->dia = 1;
                 $this->mes = 11;
                 $this->ano = 2024;*/
                
                if( $this->dia < 28)
                {
                    $fecha = new DateTime();
                    $fecha->setDate($this->ano,$this->mes,1);
                    $fecha->sub(new DateInterval('P1M'));
                    
                    $this->anoSAHA = $fecha->format("Y");
                    $this->mesSAHA = $fecha->format("m");
                }
                else
                {
                    $this->anoSAHA = $this->ano;
                    $this->mesSAHA = $this->mes;
                }
                
                /*01*/
                $this->portada();
                //$this->saha();
                //$this->cavi();
                //$this->sivah();
                $this->adicionales();
            }
            
        }
    }
    
    function portada()
    {
        $this->AddPage();
        $this->fondoPortada();
        
        $this->SetY(80);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->font,'B',50);
        $this->Cell(0,6,$this->texto("Reunión de comité de seguridad"),0,2,'C');
        $this->Ln();
        $this->Ln();
        $this->SetFont($this->font,'I',40);
        //$this->Cell(0,6,$this->texto("(Key compliance indicators)"),0,2,'C');
        
        $margenX = 20;
        $this->SetX($margenX);
        $this->SetY(150);
        $this->fontSizes = array(32);
        $this->fontWeights = array("B");
        $this->fontNames = array($this->font);
        $this->aligns = array("C");
        $this->widths = array($this->w - ($margenX*2));
        $this->textColors = array("#2658af");
        $this->borders = array(0);
        $this->borderColors = array("#afb2b0");
        $this->backgroundColors = array("#ffffff");
        $this->RowTransparent(array($this->texto($this->empresa->nombre)),10);
        
        
        $imagen = "../imagenes/logo_handel.png";
        $width = $this->w;
        $anchoFoto = 60;
        $x = 20;
        $y = 20;
        $this->Image($imagen,$x,$y,$anchoFoto);
        
        
        $nombreMesActual = Mes::getNombreMesActual();
        $anoActual = date("Y");
        
        $this->SetY(182);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont($this->font,'B',15);
        $this->Cell(0,6,$this->texto($nombreMesActual . ", " . $anoActual),0,2,'C');
        
        
    }
    
    public function imprimir()
    {
        $filename ="../reportes_mensuales/";
        $filename.=$this->calcularFolio();
        $filename.=".pdf";
        
        //var_dump($this->auditoria);
        
        $this->Output($filename,'F');
        
        if (file_exists($filename))
        {
            header('Location:'. $filename);
        }
    }
    
    protected function adicionales()
    {
        if($this->imagenSimulacros!="")
            $this->simulacros();
        
        $this->revisionProcesos();    
        $this->analisisRiesgos();
        
        if($this->imagenNovedades!="")
            $this->novedades();
        $this->textoNovedades();
        $this->importante();
        
        $this->actividadesPrevias();
        $this->actividadesProximas();
        $this->ayuda();
    }
    
    private function analisisRiesgos()
    {
        
        
    }
    
    private function ayuda()
    {
        $this->AddPage();
        $this->fondoPlantilla();
        //$this->tituloPagina("¡Importante!");
        $this->SetY(30);
        if(file_exists("../imagenes/caricatura/caricatura_ayuda.png"))
            $this->Image("../imagenes/caricatura/caricatura_ayuda.png", 130, 45, 100, 100);
    }
    
    private function importante()
    {
        $this->AddPage();
        $this->fondoPlantilla();
        $this->tituloPagina("¡Importante!");
        $this->SetY(30);
        if(file_exists("../imagenes/CTPAT45.png"))
            $this->Image("../imagenes/CTPAT45.png", 110, 20, 170, 140);
    }
    
    private function revisionProcesos()
    {
        $this->AddPage();
        $this->fondoPlantilla();
        $this->tituloPagina("Revisión de procesos");
        $this->subtitulo("Participa en esta actividad crítica");
        
        $this->SetY(50);
        
        $mes = date("m");
        
        //$this->SetX(70);
        
        $this->vineta(90,"En cumplimiento con la certificación es necesario realizar una revisión de cada uno de los procedimientos de la certificación\n");
        $this->vineta(90,"Recibirás un correo electrónico con instrucciones sobre cómo realizar la revisión dentro de SAHA\n");
        $this->vineta(90,"Tienes todo el mes de ".Mes::getNombre($mes)." para realizar esta actividad\n");
        $this->vineta(90,"El correo viene de noreply@apps-handel.com");
        
        if(file_exists("../imagenes/revision_procesos.png"))
            $this->Image("../imagenes/revision_procesos.png", 220, 50, 75, 75);
        
    }
    
    protected function vineta($x,$texto)
    {
      
        
        $this->fontSizes = array(19, 19);
        $this->fontWeights = array("B","");
        $this->fontNames = array("ZapfDingbats",$this->font);
        $this->aligns = array("L","FJ",);
        $this->widths = array(15, 100);
        $this->textColors = array("#000000","#000000");
        $this->borders = array(0,0);
        $this->borderColors = array("#afb2b0","#afb2b0");
        $this->backgroundColors = array("#ffffff","#ffffff");
        $this->Row2(array($this->texto(chr(108)),$this->texto($texto)),6);
       
        $this->Ln();
        
        
    }
    
    private function actividadesPrevias()
    {
        if(isset($this->criteriosSeleccion->actividadesPrevias) && $this->criteriosSeleccion->actividadesPrevias!="")
         $this->lista("¿En que estamos trabajando?","Actividades previas", $this->criteriosSeleccion->actividadesPrevias);
    }
    
    private function actividadesProximas()
    {
        if(isset($this->criteriosSeleccion->actividadesProximas) && $this->criteriosSeleccion->actividadesProximas!="")
            $this->lista("","Actividades proximas", $this->criteriosSeleccion->actividadesProximas);
    }
    
    private function textoNovedades()
    {
        if(isset($this->criteriosSeleccion->novedades) && $this->criteriosSeleccion->novedades!="")
            $this->lista("","Novedades", $this->criteriosSeleccion->novedades);
    }
    
    private function simulacros()
    {
        $this->AddPage();
        $this->fondoPlantilla();
        $this->tituloPagina("Simulacros");
        $this->SetY(30);
        if(file_exists("../".$this->imagenSimulacros))        
            $this->Image("../".$this->imagenSimulacros, 100, 20, 190, 150);
        
    }
    
    private function lista($titulo, $subtitulo, $texto)
    {
        $this->AddPage();
        $this->fondoPlantilla();
        $this->tituloPagina($titulo);
        $this->subtitulo($subtitulo);
        
        $lineas = explode("\n", $texto);
        $margenX = 20;
        $this->SetX($margenX);
        $this->SetY(60);
        for ($i = 0; $i < count($lineas); $i++) 
        {
            $this->fontSizes = array(19, 19);
            $this->fontWeights = array("B","");
            $this->fontNames = array("ZapfDingbats",$this->font);
            $this->aligns = array("L","L",);
            $this->widths = array(15, 150);
            $this->textColors = array("#000000","#000000");
            $this->borders = array(0,0);
            $this->borderColors = array("#afb2b0","#afb2b0");
            $this->backgroundColors = array("#ffffff","#ffffff");
            $this->Row2(array($this->texto(chr(108)),$this->texto($lineas[$i])),6);
            $this->Ln();
        }
        
    }
    
    private function novedades()
    {
        $this->AddPage();
        $this->fondoPlantilla();
        $this->tituloPagina("Novedades");
        $this->SetY(30);
        if(file_exists("../".$this->imagenNovedades))
            $this->Image("../".$this->imagenNovedades, 100, 20, 190, 150);
    }
}


