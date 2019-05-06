<?php
namespace php\interfaces;

use php\modelos\Auditoria;

interface IAuditoriasRepositorio
{
    public function insertar(Auditoria $modelo);
    public function actualizar(Auditoria $modelo);  
    
    public function consultarPorLlaves($id); 
    public function consultar($criteriosSeleccion);  
    public function eliminar($llaves);
}

