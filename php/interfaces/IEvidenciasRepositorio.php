<?php
namespace php\interfaces;

use php\modelos\Evidencia;

interface IEvidenciasRepositorio
{
    public function insertar(Evidencia $modelo,$nombreArchivoSubido);
    public function actualizar(Evidencia $modelo,$nombreArchivoSubido);
    public function consultarPorLlaves($id);
    public function consultar($usuario,$criteriosSeleccion);
    public function eliminar($llaves);
}
