<?php
namespace php\interfaces;

use php\modelos\Ciudad;

interface ICiudadesRepositorio
{
    public function insertar(Ciudad $modelo);
    public function actualizar(Ciudad $modelo);  
    public function consultar($criteriosSeleccion);  
    public function consultarPorLlaves($llaves); 
    public function consultarPorPaisEstado($paisId,$estadoId);
}

