<?php
namespace php\interfaces;

use php\modelos\Categoria;

interface ICategoriasRepositorio
{
    public function insertar(Categoria $modelo);
    public function actualizar(Categoria $modelo);  
    public function consultar($criteriosSeleccion);  
    public function consultarPorLlaves($llaves); 
  
}

