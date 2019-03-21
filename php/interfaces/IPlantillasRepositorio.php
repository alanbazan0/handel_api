<?php
namespace php\interfaces;

use php\modelos\Plantilla;

interface IPlantillasRepositorio
{
    public function insertar(Plantilla $modelo);
    public function actualizar(Plantilla $modelo);  
    
    public function consultarPorLlaves($id); 
    public function consultar($criteriosSeleccion);  
    public function eliminar($llaves);
}

