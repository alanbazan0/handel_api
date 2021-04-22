<?php
namespace php\interfaces;

use php\modelos\EstatusValidacion;

interface IEstatusValidacionRepositorio
{
    public function insertar(EstatusValidacion $modelo);
    public function actualizar(EstatusValidacion $modelo);  
    public function consultar($criteriosSeleccion);  
    public function consultarPorLlaves($llaves); 
}

