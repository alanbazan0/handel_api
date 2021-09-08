<?php
namespace php\interfaces;

use php\modelos\Usuario;

interface IUsuariosRepositorio
{
    public function insertar($usuario,Usuario $modelo);
    public function actualizar(Usuario $modelo);    
   // public function consultarPorLlaves($id); 
    public function consultar($usuario,$criteriosSeleccion,$opcional);   
}

