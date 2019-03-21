<?php
namespace php\interfaces;

use php\modelos\Area;

interface IAreasRepositorio
{
    public function insertar(Area $modelo);
    public function actualizar(Area $modelo);  
    public function consultar($criteriosSeleccion);  
    public function consultarPorLlaves($llaves); 
    public function consultarPorEmpresa($empresaId);
    public function consultarPorEmpresaSede($empresaId, $sedeId, $opcional, $usuario);
}

