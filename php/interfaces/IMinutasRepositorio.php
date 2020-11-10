<?php
namespace php\interfaces;

use php\modelos\Minuta;

interface IMinutasRepositorio
{
    public function insertar(Minuta $modelo,$usuario);
    public function actualizar(Minuta $modelo);
    public function consultarPorLlaves($id,$consultarDetalle);
    public function consultar($usuario,$criteriosSeleccion);
    public function eliminar($llaves);
}
