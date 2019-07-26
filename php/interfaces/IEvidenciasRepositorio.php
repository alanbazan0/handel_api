<?php
namespace php\interfaces;

use php\modelos\Evidencia;

interface IEvidenciasRepositorio
{
    public function insertar(Evidencia $modelo);
    public function actualizar(Evidencia $modelo);
    public function consultarPorLlaves($id);
    public function consultar($criteriosSeleccion);
    public function eliminar($llaves);
}
