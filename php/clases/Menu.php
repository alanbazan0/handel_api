<?php
namespace php\clases;

use TipoUsuario;

include "php/clases/TipoUsuario.php";


class Menu 
{
    private function crearOpcion($titulo,$url,$icono)
    {
        $opcion="<li><a href='#' onClick='menuVista.abrirUrl(\"".$url."\");'><i class='fa ".$icono." fa-lg' ></i><span class='nav-text'>".$titulo."</span></a></li>";
        return $opcion;
    }
    
    private function renderizarOpcionSecundaria($titulo,$url)
    {
        $opcion="<li><a href='$url'>$titulo</a></li>";
        return $opcion;
    }
    
    private function renderizarOpcion($titulo,$url,$icono)
    {
        $opcion=" <li><a href='$url'><i class='$icono' style='width:25px;'></i> <span>$titulo</span></a></li>";
        return $opcion;
    }
    
//     private function renderizarOpcionSimple($titulo,$url,$icono)
//     {
//         $opcion=" <li><a href='$url'><i class='$icono'></i> <span>$titulo</span></a></li>";
// //         $opcion.="<li>";
// //         $opcion.="<a href='$url'>";
// //         $opcion.="<i class='$icono'></i>$titulo</a>";
// //         $opcion.="</li>";
//         return $opcion;
//     }
    
  
    
    private function renderizarCatalogosAdministrador($estiloLista)
    {
        $opciones="";
        $opciones.="<li class='has-sub'>";
        $opciones.="<a class='js-arrow' href='#'> ";
        $opciones.="<i class='fas fa-table'></i>Catálogos</a> ";
        $opciones.="<ul class='$estiloLista list-unstyled js-sub-list'> ";
        $opciones.=$this->renderizarOpcion("Areas","areas.php","fas fa-cubes");
        $opciones.=$this->renderizarOpcion("Puestos","puestos.php","fas fa-male");
        $opciones.=$this->renderizarOpcion("Usuarios","usuarios.php","fa fa-users");
        $opciones.="</ul>";
        $opciones.="</li>";
        
        $opciones.="<li class='has-sub'>";
        $opciones.="<a class='js-arrow' href='#'> ";
        $opciones.="<i class='fa fa-file-alt'></i>Reportes</a> ";
        $opciones.="<ul class='$estiloLista list-unstyled js-sub-list'> ";
        $opciones.=$this->renderizarOpcion("Inspecciones","inspecciones.php","fa fa-file-pdf");
        $opciones.="</ul>";
        $opciones.="</li>";
        
        return $opciones;
    }
    
    private function renderizarCatalogosAdministradorCorporativo($estiloLista)
    {
        $opciones="";
        $opciones.="<li class='has-sub'>";
        $opciones.="<a class='js-arrow' href='#'> ";
        $opciones.="<i class='fas fa-table'></i>Catálogos</a> ";
        $opciones.="<ul class='$estiloLista list-unstyled js-sub-list'> ";
        $opciones.=$this->renderizarOpcion("Sedes","sedes.php","fas fa-list");
        $opciones.=$this->renderizarOpcion("Areas","areas.php","fas fa-cubes");
        $opciones.=$this->renderizarOpcion("Puestos","puestos.php","fas fa-male");
        $opciones.=$this->renderizarOpcion("Usuarios","usuarios.php","fa fa-users");
        $opciones.="</ul>";
        $opciones.="</li>";
        
//         $opciones.="<li class='has-sub'>";
//         $opciones.="<a class='js-arrow' href='#'> ";
//         $opciones.="<i class='fa fa-file-alt'></i>Reportes</a> ";
//         $opciones.="<ul class='$estiloLista list-unstyled js-sub-list'> ";
//         $opciones.=$this->renderizarOpcion("Inspecciones","inspecciones.php","fa fa-file-pdf");
//         $opciones.="</ul>";
//         $opciones.="</li>";
        return $opciones;
    }
    
    private function renderizarCatalogosSuperUsuario($estiloLista)
    {
        $opciones="";
        $opciones.="<li class='treeview'>";
        $opciones.="<a href='#'>";
        $opciones.="<i class='fa fa-table'></i> <span>Catálogos</span>";
        $opciones.="<span class='pull-right-container'>";
        $opciones.="<i class='fa fa-angle-left pull-right'></i>";
        $opciones.="</span>";
        $opciones.="</a>";
        $opciones.="<ul class='treeview-menu'>";
        $opciones.=$this->renderizarOpcion("Tipos de empresa","tipos_empresa.php","fa fa-building-o");
        $opciones.=$this->renderizarOpcion("Empresas","empresas.php","fa fa-building");
        $opciones.=$this->renderizarOpcion("Sedes","sedes.php","fa fa-cubes");
        $opciones.=$this->renderizarOpcion("Puestos","puestos.php","fa fa-book");
        $opciones.=$this->renderizarOpcion("Areas","areas.php","fa fa-puzzle-piece");
        $opciones.=$this->renderizarOpcion("Usuarios","usuarios.php","fa fa-users");
        $opciones.=$this->renderizarOpcion("Justificaciones","justificaciones.php","fa fa-flag-o");
       // $opciones.=$this->renderizarOpcion("Asociados","asociados.php","fa fa-male");
        $opciones.=$this->renderizarOpcion("Certificaciones","certificaciones.php","fa fa-certificate");
        $opciones.="</ul>";
        $opciones.="</li>";
        $opciones.="<li>";
        
        return $opciones;
    }
    
    public function renderizarIndicadoresSuperUsuario()
    {
        $indicadores="";
        $indicadores.=$this->renderizarIndicador("Tipos de empresa", "tiposEmpresa", "tipos_empresa.php", "#FF7F50", "fa fa-building-o");
        $indicadores.=$this->renderizarIndicador("Empresas", "empresas", "empresas.php", "#00b26f", "fa fa-building");
        $indicadores.=$this->renderizarIndicador("Sedes", "sedes", "sedes.php", "#00b5e9", "fa fa-cubes");
        $indicadores.=$this->renderizarIndicador("Puestos", "puestos", "puestos.php", "#fa4251", "fa fa-book");
        $indicadores.=$this->renderizarIndicador("Areas", "areas", "areas.php", "#7842fa", "fa fa-puzzle-piece");
        $indicadores.=$this->renderizarIndicador("Usuarios", "usuarios", "usuarios.php", " #ff8300", "fa fa-users");
        $indicadores.=$this->renderizarIndicador("Justificaciones", "justificaciones", "justificaciones.php", "#DB7093", "fa fa-flag-o");
        //$indicadores.=$this->renderizarIndicador("Asociados", "asociados", "asociados.php", "#9ACD32", "fa fa-male");
        $indicadores.=$this->renderizarIndicador("Certificaciones", "certificaciones", "certificaciones.php", "#8FBC8F", "fa fa-certificate");
        $indicadores.=$this->renderizarIndicador("Procedimientos", "procedimientos", "procedimientosb.php", "#6495ED", "fa fa-file-text-o");
        $indicadores.=$this->renderizarIndicador("Evidencias", "evidencias", "evidencias.php", "#DEB887", "fa fa-upload");
        return $indicadores;
    }
    
    public function renderizarIndicadoresAdministrador()
    {
        $indicadores="";
        $indicadores.=$this->renderizarIndicador("Areas", "areas", "areas.php", "#7842fa", "fas fa-cubes");
        $indicadores.=$this->renderizarIndicador("Puestos", "puestos", "puestos.php", "#fa4251", "fas fa-male");
        $indicadores.=$this->renderizarIndicador("Usuarios", "usuarios", "usuarios.php", " #ff8300", "fa fa-users");
        return $indicadores;
    }
    
    public function renderizarIndicadoresAdministradorCorporativo()
    {
        $indicadores="";
        $indicadores.=$this->renderizarIndicador("Sedes", "sedes", "sedes.php", "#00b5e9", "fas fa-list");
        $indicadores.=$this->renderizarIndicador("Areas", "areas", "areas.php", "#7842fa", "fas fa-cubes");
        $indicadores.=$this->renderizarIndicador("Puestos", "puestos", "puestos.php", "#fa4251", "fas fa-male");
        $indicadores.=$this->renderizarIndicador("Usuarios", "usuarios", "usuarios.php", " #ff8300", "fa fa-users");
        return $indicadores;
    }
    
    public function renderizarIndicadores($tipoUsuarioId)
    {
        $indicadores = "";
        if($tipoUsuarioId== TipoUsuario::SUPERUSUARIO)
            $indicadores.= $this->renderizarIndicadoresSuperUsuario();
        else if($tipoUsuarioId== TipoUsuario::ADMINISTRADOR_CORPORATIVO)
            $indicadores.= $this->renderizarIndicadoresAdministradorCorporativo();
           else if($tipoUsuarioId== TipoUsuario::ADMINISTRADOR)
                $indicadores.= $this->renderizarIndicadoresAdministrador();
        echo $indicadores;
    }
    
    public function renderizarGraficas($tipoUsuarioId)
    {
        $graficas = "";
        if($tipoUsuarioId== TipoUsuario::SUPERUSUARIO)
            $graficas = $this->renderizarGraficasSuperUsuario();
        else if($tipoUsuarioId== TipoUsuario::ADMINISTRADOR_CORPORATIVO || $tipoUsuarioId== TipoUsuario::ADMINISTRADOR)
            $graficas = $this->renderizarGraficasAdministrador();
        echo $graficas;
    }
    
    private function renderizarGraficasSuperUsuario()
    {
        $graficas = $this->renderizarGrafinicaCreditosEmpresa("col-md-6");
        $graficas .= $this->renderizarGraficaCreditosMes("col-md-6");
        return $graficas;
    }
    
    private function renderizarGraficaCreditosEmpresa($col)
    {
        $grafica ="";
        $grafica.="<div class='$col' >";
        $grafica.="<div class='au-card m-b-30'>";
        $grafica.="<div class='au-card-inner'>";
        $grafica.="<h3 class='title-2 m-b-40'>Inspecciones por empresa</h3>";
        
        $grafica.="<div id='inspeccionesEmpresaChart' style='height: 200px'></div>";
        //$grafica.="<canvas id='inspeccionesEmpresaChart' height='400' ></canvas>";
        $grafica.="</div>";
        $grafica.="</div>";
        $grafica.="</div>";
        return $grafica;
    }
    
    private function renderizarGraficasAdministrador()
    {
        $graficas = "";
        $graficas .= $this->renderizarGraficaCreditosSede("col-md-6");
        $graficas .= $this->renderizarGraficaCreditosMes("col-md-6");
        return $graficas;
    }
    
    private function renderizarGraficaCreditosSede($col)
    {
        $grafica ="";
        $grafica.="<div class='$col'>";
        $grafica.="<div class='au-card m-b-30'>";
        $grafica.="<div class='au-card-inner'>";
        $grafica.="<h3 class='title-2 m-b-40'>Inspecciones por sede</h3>";
//         $grafica.="<canvas id='inspeccionesSedeChart' ></canvas>";
        $grafica.="<div id='inspeccionesSedeChart' style='height: 200px'></div>";
        $grafica.="</div>";
        $grafica.="</div>";
        $grafica.="</div>";
        return $grafica;
    }
    
    private function renderizarGraficaCreditosMes($col)
    {
        $grafica ="";
        $grafica.="<div class='$col'>";
        $grafica.="<div class='au-card m-b-30'>";
        $grafica.="<div class='au-card-inner'>";
        $grafica.="<h3 class='title-2 m-b-40'>Inspecciones por mes</h3>";
//         $grafica.="<canvas id='inspeccionesMesChart' ></canvas>";
        $grafica.="<div id='inspeccionesMesChart' style='height: 200px'></div>";
        $grafica.="</div>";
        $grafica.="</div>";
        $grafica.="</div>";
        return $grafica;
    }
    
    private function renderizarIndicador($titulo, $id, $url, $color, $icono)
    {
//         $indicador ="<div class='col'>";
//         $indicador.="<div class='statistic__item' style='cursor:pointer;background-color:$color' onclick='vista.abrirOpcion(\"$url\");'>";
//         $indicador.="<h2 id='$id' class='number'>0</h2>";
//         $indicador.="<span class='desc'>$titulo</span>";
//         $indicador.="<div class='icon'>";
//         $indicador.="<i class='$icono'></i>";
//         $indicador.="</div>";
//         $indicador.="</div>";
//         $indicador.="</div>";
        $indicador="";
        $indicador.="<div class='col-lg-3 col-xs-6'>";
        $indicador.="<!-- small box -->";
        $indicador.="<div class='small-box bg-aqua' >";
        $indicador.="<div class='inner' style='cursor:pointer;background-color:$color'>";
        $indicador.="<h3>0</h3>";
        $indicador.="<p>$titulo</p>";
        $indicador.="</div>";
        $indicador.="<div class='icon'>";
        $indicador.="<i class='$icono'></i>";
        $indicador.="</div>";
        $indicador.="<a href='$url' class='small-box-footer'>Mas información <i class='fa fa-arrow-circle-right'></i></a>";
        $indicador.="</div>";
        $indicador.="</div>";
        return $indicador;
    }
   
    
    public function renderizar($tipoUsuarioId)
    {
        $menu = "<li class='header'>Menú</li>";
        $menu.=$this->renderizarOpcion("Panel de control", "panel.php", "fas fa-tachometer-alt");
        if($tipoUsuarioId== TipoUsuario::SUPERUSUARIO)
            $menu.= $this->renderizarCatalogosSuperUsuario("");
        else if($tipoUsuarioId== TipoUsuario::ADMINISTRADOR_CORPORATIVO)
                $menu.= $this->renderizarCatalogosAdministradorCorporativo("");
        else if($tipoUsuarioId== TipoUsuario::ADMINISTRADOR)
            $menu.= $this->renderizarCatalogosAdministrador("");
        echo $menu;
    }
    
    public function renderizarFotoPerfilIzquierda($usuario)
    {
        $fecha = new \DateTime();
        $time = $fecha->getTimestamp();
        $html="";
        $html.="<div class='pull-left image'>";
        $html.="<img src='$usuario->fotoPerfil?$time' class='img-circle' alt='User Image'>";
        $html.="</div>";
        $html.="<div class='pull-left info'>";
        $html.="<p>$usuario->nombreCompleto</p>";
        $html.="<a href='#'><i class='fa fa-circle text-success'></i> En linea</a>";
        $html.="</div>";
        echo $html;
    }
    
    public function renderizarFotoPerfilSuperior($usuario)
    {
        $fecha = new \DateTime();
        $time = $fecha->getTimestamp();
        $html="";
        $html.="<!-- User Account: style can be found in dropdown.less -->";
        $html.="<li class='dropdown user user-menu'>";
        $html.="<a href='#' class='dropdown-toggle' data-toggle='dropdown'>";
        $html.="<img src='$usuario->fotoPerfil?$time' class='user-image' alt='User Image'>";
        $html.="<span class='hidden-xs'>$usuario->nombreCompleto</span>";
        $html.="</a>";
        $html.="<ul class='dropdown-menu'>";
        $html.="<!-- User image -->";
        $html.="<li class='user-header'>";
        $html.="<img src='$usuario->fotoPerfil?$time' class='img-circle' alt='User Image'>";
        $html.="<p>";
        $html.= $usuario->nombreCompleto ." - ". $usuario->tipoUsuarioNombre;
        $html.="<small>Member since Nov. 2012</small>";
        $html.="</p>";
        $html.="</li>";
        $html.="<!-- Menu Body -->";
//         $html.="<li class='user-body'>";
//         $html.="<div class='row'>";
//         $html.="<div class='col-xs-4 text-center'>";
//         $html.="<a href='#'>Followers</a>";
//         $html.="</div>";
//         $html.="<div class='col-xs-4 text-center'>";
//         $html.="<a href='#'>Sales</a>";
//         $html.="</div>";
//         $html.="<div class='col-xs-4 text-center'>";
//         $html.="<a href='#'>Friends</a>";
//         $html.="</div>";
//         $html.="</div>";
//         $html.="<!-- /.row -->";
//         $html.="</li>";
        $html.="<!-- Menu Footer-->";
        $html.="<li class='user-footer'>";
        $html.="<div class='pull-left'>";
        $html.="<a href='#' class='btn btn-default btn-flat'>Perfil</a>";
        $html.="</div>";
        $html.="<div class='pull-right'>";
        $html.="<a href='#' class='btn btn-default btn-flat'>Cerrar sesión</a>";
        $html.="</div>";
        $html.="</li>";
        $html.="</ul>";
        $html.="</li>";
        echo $html;
    }
    
    public function renderizarFotoPerfil($usuario)
    {
        $fecha = new \DateTime();
        $time = $fecha->getTimestamp();
        $html="";
        $html.="<div id='menuPerfil' class='account-item clearfix js-item-menu'>";
        $html.="    <div class='image'>";
        $html.="        <img id='imgFotoPefil1' src='$usuario->fotoPerfil?$time' alt='$usuario->nombreCompleto' />";
        $html.="    </div>";
        $html.="    <div class='content'>";
        $html.="        <a class='js-acc-btn' href='#'>$usuario->nombreCompleto</a>";
        $html.="    </div>";
        $html.="    <div class='account-dropdown js-dropdown'>";
        $html.="        <div class='info clearfix'><div class='info clearfix'>";
        $html.="            <div class='image'>";
        $html.="                <a href='#'>";
        $html.="                    <img id='imgFotoPefil2' src='$usuario->fotoPerfil' alt='$usuario->nombreCompleto' />";
        $html.="                </a>";
        $html.="            </div>";
        $html.="            <div class='content'>";
        $html.="                <h5 class='name'>";
        $html.="                    <a href='#'>$usuario->nombreCompleto</a>";
        $html.="                </h5>";
        $html.="                <span class='email'>$usuario->nombreUsuario</span>";
        $html.="            </div>";
        $html.="        </div>";
        $html.="        <div class='account-dropdown__body'>";
        $html.="            <div class='account-dropdown__item'>";
        $html.="                <a href='#' onclick='vista.cambiarFotoPerfil();'>";
        $html.="                <i class='zmdi zmdi-face'></i>Foto de perfil</a>";
        $html.="                <input type='file' id='perfilFile'  name='file' style='display:none' onchange='vista.subirFotoPerfil(this);'";
        $html.="            </div>";
        $html.="        </div>";
        $html.="        <div class='account-dropdown__footer'>";
        $html.="            <a href='#' onclick='vista.cerrarSesion();'>";
        $html.="            <i class='zmdi zmdi-power'></i>Cerrar sesión</a>";
        $html.="        </div>";
        $html.="    </div>";
        $html.="</div>";
        //$html= file_get_contents('php/plantillas_html/foto_perfil.html');
        echo $html;
    }
    
    public function getUsuario($usuario)
    {
        $usuarioJavascript= (object) [
            'tipoUsuarioId' => $usuario->tipoUsuarioId
        ];
        return $usuarioJavascript;
    }
    
}