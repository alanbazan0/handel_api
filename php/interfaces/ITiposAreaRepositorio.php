<?php
namespace php\interfaces;

use php\modelos\TipoArea;

interface ITiposAreaRepositorio
{
    public function insertar(TipoArea $modelo);
    public function actualizar(TipoArea $modelo);  
    public function consultar($criteriosSeleccion);  
    public function consultarPorLlaves($llaves); 
    public function consultarPorEmpresa($empresaId);
}

