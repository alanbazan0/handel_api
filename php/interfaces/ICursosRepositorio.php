<?php
namespace php\interfaces;

use php\modelos\Curso;

interface ICursosRepositorio
{
    public function insertar(Curso $modelo);
    public function actualizar(Curso $modelo);  
    
    public function consultarPorLlaves($id); 
    public function consultar($criteriosSeleccion);  
    public function eliminar($llaves);
}

