<?php
namespace php\interfaces;

use php\modelos\Pais;

interface IPaisesRepositorio
{
    public function insertar(Pais $modelo);
    public function actualizar(Pais $modelo);  
    
    public function consultarPorLlaves($id); 
    public function consultar($criteriosSeleccion);  
    public function eliminar($llaves);
}

