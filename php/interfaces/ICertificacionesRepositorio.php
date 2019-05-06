<?php
namespace php\interfaces;

use php\modelos\Certificacion;

interface ICertificacionesRepositorio
{
    public function insertar(Certificacion $modelo);
    public function actualizar(Certificacion $modelo);  
    
    public function consultarPorLlaves($id); 
    public function consultar($criteriosSeleccion);  
    public function eliminar($llaves);
}

