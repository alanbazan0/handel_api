<?php
namespace php\interfaces;

use php\modelos\Estado;

interface IEstadosRepositorio
{
    public function insertar(Estado $modelo);
    public function actualizar(Estado $modelo);  
    public function consultar($criteriosSeleccion);  
    public function consultarPorLlaves($llaves); 
    public function consultarPorPais($paisId);
}

