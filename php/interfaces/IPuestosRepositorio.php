<?php
namespace php\interfaces;

use php\modelos\Puesto;

interface IPuestosRepositorio
{
    public function insertar(Puesto $modelo);
    public function actualizar(Puesto $modelo);  
    public function consultar($criteriosSeleccion);  
    public function consultarPorLlaves($llaves); 
    public function consultarPorEmpresaSede($empresaId,$sedeId);
}

