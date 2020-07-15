<?php
namespace php\interfaces;

use php\modelos\Minuta;

interface IMinutasRepositorio
{
    public function insertar(Minuta $modelo);
    public function actualizar(Minuta $modelo);
    public function consultarPorLlaves($id);
    public function consultar($criteriosSeleccion);
    public function eliminar($llaves);
}
