<?php
namespace php\interfaces;

use php\modelos\Estandar;

interface IEstandaresRepositorio
{
    public function insertar(Estandar $modelo);
    public function actualizar(Estandar $modelo);  
    public function consultar($criteriosSeleccion);  
    public function consultarPorLlaves($llaves); 
  
}

