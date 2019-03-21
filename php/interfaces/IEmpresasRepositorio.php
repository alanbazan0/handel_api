<?php
namespace php\interfaces;

use php\modelos\Empresa;

interface IEmpresasRepositorio
{
    public function insertar(Empresa $modelo);
    public function actualizar(Empresa $modelo);  
    
    public function consultarPorLlaves($id); 
    public function consultar($criteriosSeleccion,$opcional,$usuario);     
    public function consultarCorporativo($empresaId);
}

