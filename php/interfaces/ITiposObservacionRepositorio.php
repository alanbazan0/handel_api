<?php
namespace php\interfaces;

use php\modelos\TipoObservacion;

interface ITiposObservacionRepositorio
{
    public function insertar(TipoObservacion $modelo);
    public function actualizar(TipoObservacion $modelo);  
    public function consultar($criteriosSeleccion,$opcional);  
    public function consultarPorLlaves($llaves); 
}

