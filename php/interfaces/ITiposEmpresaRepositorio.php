<?php
namespace php\interfaces;

use php\modelos\TipoEmpresa;

interface ITiposEmpresaRepositorio
{
    public function insertar(TipoEmpresa $modelo);
    public function actualizar(TipoEmpresa $modelo);  
    
    public function consultarPorLlaves($id); 
    public function consultar($criteriosSeleccion);  
    public function eliminar($llaves);
}

