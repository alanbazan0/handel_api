<?php
namespace php\interfaces;

use php\modelos\Curso;

interface ICursosRepositorio
{
    public function insertar($usuario,Curso $modelo);
    public function actualizar(Curso $modelo);  
    
    public function consultarPorLlaves($usuario,$llaves); 
    public function consultar($criteriosSeleccion);  
    public function eliminar($llaves);
}

