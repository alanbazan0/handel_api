<?php
namespace php\interfaces;

use php\modelos\Credito;

interface ICreditosRepositorio
{
    public function insertar(Credito $modelo);
    public function actualizar(Credito $modelo);  
    public function consultar($criteriosSeleccion);  
    public function consultarPorLlaves($llaves); 
    public function consultarPorEmpresaSede($empresaId,$sedeId);
}

