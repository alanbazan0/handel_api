<?php
namespace php\interfaces;

use php\modelos\Inspeccion;

interface IInspeccionesRepositorio
{
    public function insertar(Inspeccion $modelo);
    public function actualizar(Inspeccion $modelo);  
    public function consultar($criteriosSeleccion);  
    public function consultarPorLlaves($llaves); 
}

