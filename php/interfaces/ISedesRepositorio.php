<?php
namespace php\interfaces;

use php\modelos\Sede;

interface ISedesRepositorio
{
    public function insertar(Sede $modelo);
    public function actualizar(Sede $modelo);  
    
   
    public function consultar($criteriosSeleccion);  
    public function consultarPorLlaves($llaves); 
    public function consultarPorEmpresa($empresaId,$opcional, $usuario);
}

