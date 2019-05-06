<?php
namespace php\interfaces;

use php\modelos\Justificacion;

interface IJustificacionesRepositorio
{
    public function insertar(Justificacion $modelo);
    public function actualizar(Justificacion $modelo);  
    
    public function consultarPorLlaves($id); 
    public function consultar($criteriosSeleccion);  
    public function eliminar($llaves);
}

