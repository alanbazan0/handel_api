<?php
use php\clases\AdministradorConexion;
use php\repositorios\EmpresasRepositorio;
use php\repositorios\UsuariosProcedimientosRepositorio;
use php\repositorios\UsuariosProcesosRepositorio;
use php\clases\Token;
use php\clases\AdministradorArchivos;
use php\repositorios\MinutasRepositorio;

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
                $this->saha();
                $this->cavi();
                $this->sivah();
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
    
    protected function tituloPagina($titulo, $y)
    {
        
        $this->SetLeftMargin(5);
        $this->SetX(0);
        $this->SetY($this->h/2 - $y);
        $this->fontSizes = array(28);
        $this->fontWeights = array("B");
        $this->fontNames = array($this->font);
        $this->aligns = array("C");
        $this->widths = array(80);
        $this->textColors = array("#ffffff");
        $this->borders = array(0);
        $this->backgroundColors = array("#ffffff");
        $this->RowTransparent(array($this->texto($titulo)),10);
        
        
    }
    
    private function avanceRevisionProcesos($titulo)
    {
        $this->AddPage();
        $this->fondoPlantilla();
        $this->tituloPagina($titulo,30);
        
        $repositorio = new UsuariosProcesosRepositorio($this->conexion);
        $administradorArchivos = new AdministradorArchivos();
        $resultado = $repositorio->consultarAvanceUsuarios($this->usuario,$this->criteriosSeleccion);
        if($resultado->correcto())
        {
            $usuarios = $resultado->valor;
            $image = $repositorio->graficaAvance($usuarios,"nombreCompleto","");
            if($image!="")
            {
                $fecha = date_create();
                $nombreArchivo = Token::getToken(30).date_timestamp_get($fecha).".jpeg";
                file_put_contents("../archivos_temporales/".$nombreArchivo, file_get_contents($image));
                if(file_exists("../archivos_temporales/".$nombreArchivo))
                {
                    $this->Image("../archivos_temporales/".$nombreArchivo, 105, 15, 160, 160);
                    $administradorArchivos->eliminar("archivos_temporales", $nombreArchivo);
                }
            }
        }
    }
    
    private function analisisRiesgos()
    {
        if($this->empresa->analisisRiesgoMinutaId!=null && $this->empresa->analisisRiesgoMinutaId!="")
        {
            $this->AddPage();
            $this->fondoPlantilla();
            $this->tituloPagina("Análisis de riesgo OEA",20);
            
            $repositorio = new MinutasRepositorio($this->conexion);
            $administradorArchivos = new AdministradorArchivos();
            $resultado = $repositorio->consultarTareasUsuarios($this->empresa->analisisRiesgoMinutaId);
            if($resultado->correcto())
            {
                $usuarios = $resultado->valor;
                $image = $repositorio->graficaAvance($usuarios,"usuarioNombreCompleto","");
                if($image!="")
                {
                    $fecha = date_create();
                    $nombreArchivo = Token::getToken(30).date_timestamp_get($fecha).".jpeg";
                    file_put_contents("../archivos_temporales/".$nombreArchivo, file_get_contents($image));
                    if(file_exists("../archivos_temporales/".$nombreArchivo))
                    {
                        $this->Image("../archivos_temporales/".$nombreArchivo, 105, 15, 160, 160);
                        $administradorArchivos->eliminar("archivos_temporales", $nombreArchivo);
                    }
                }
                //$analisisRiesgo = "Se verifica el avance del Análisis de Riesgo OEA (ISO 31010) En el cual se desglosa el avance de personal que cuenta con acciones asignadas en la minuta: ";
                
                /*for ($i = 0; $i < count($usuarios); $i++)
                {
                    $usuarioMinuta = $usuarios[$i];
                    $textoAsignadas = $usuarioMinuta->asignadas == 1 ? "acción asignada" : "acciones asignadas";
                    $textoTerminadas = $usuarioMinuta->terminadas == 1 ? "acción concluida" : "acciones concluidas";
                    
                    $analisisRiesgo .= $usuarioMinuta->usuarioNombreCompleto. " " . $usuarioMinuta->asignadas . " " . $textoAsignadas . " - " . $usuarioMinuta->terminadas . " "  .$textoTerminadas ;
                    
                    if($i <  count($usuarios) - 1)
                        $analisisRiesgo .= ", ";
                }
                
                $analisisRiesgo .= ". Se solicita a todos los usuarios verificar las tareas asignadas en el análisis de riesgo e indicar en el globo de mensaje de la tarea el estado del mismo así como indicar las acciones que se encuentran cerradas marcarlas como terminadas.";
                */
                    
            }
        }
        
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
        $this->tituloPagina("¡Importante!",10);
        $this->SetY(30);
        if(file_exists("../imagenes/CTPAT45.png"))
            $this->Image("../imagenes/CTPAT45.png", 110, 20, 170, 140);
    }
    
    private function revisionProcesos()
    {
        $mes = (int) date("m");
        
        
        if($this->empresa->mesRevisionProcesos == $mes)
        {
            $this->criteriosSeleccion->ano = date("Y");
            $this->criteriosSeleccion->mes = date("m");
            $this->avanceRevisionProcesos("Avance de revisión de procedimientos");
        }
        else if($this->empresa->mesRevisionProcesos + 1 == $mes)
        {
            $this->criteriosSeleccion->ano = date("Y");
            $this->criteriosSeleccion->mes = date("m");
            $this->avanceRevisionProcesos("¿Como concluye la revisión de procesos?");
        }
        else
        {
            $m = $this->empresa->mesRevisionProcesos - 1;
            if($m > 0)
            {
                if($m == $mes)
                    $this->textoRevisionProcesos();
            }
            else
            {
                if($mes == 12)
                {
                    $this->textoRevisionProcesos();
                }
            }
            
        }
    }
    
    private function textoRevisionProcesos()
    {
        $this->AddPage();
        $this->fondoPlantilla();
        $this->tituloPagina("Revisión de procesos",10);
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
        $this->tituloPagina("Simulacros",10);
        $this->SetY(30);
        if(file_exists("../".$this->imagenSimulacros))        
            $this->Image("../".$this->imagenSimulacros, 100, 20, 190, 150);
        
    }
    
    private function lista($titulo, $subtitulo, $texto)
    {
        $this->AddPage();
        $this->fondoPlantilla();
        $this->tituloPagina($titulo,10);
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
        $this->tituloPagina("Novedades",10);
        $this->SetY(30);
        if(file_exists("../".$this->imagenNovedades))
            $this->Image("../".$this->imagenNovedades, 100, 20, 190, 150);
    }
    
    protected function temasReunion()
    {
        $this->AddPage();
        $this->fondoPlantilla();
        $this->tituloPagina("Orden del día",0);
        
        $this->subtitulo("Temas de reunión");
        
        
        $this->SetY(30);
        
        $this->textoVineta("Resultados SAHA");
        $this->Ln();
        $this->textoVineta("Avances CAVI");
        $this->Ln();
        $this->textoVineta("Avances SIVAH");
        $this->Ln();
        $this->textoVineta("Simulacros");
        $this->Ln();
        $this->textoVineta("Novedades");
        $this->Ln();
        $this->textoVineta("Avisos");
        $this->Ln();
        $this->textoVineta("Actividades previas y próximas");
        $this->Ln();
        $this->textoVineta("Varios");
        
        $this->Image("../imagenes/caricatura/caricatura01.png", 225, 130, 70);
    }
}


