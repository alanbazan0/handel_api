<?php
namespace php\interfaces;

use php\modelos\Perfil;

interface IPerfilesRepositorio
{
    public function insertar(Perfil $modelo);
    public function actualizar(Perfil $modelo);
    public function consultarPorLlaves($id);
    public function consultar($criteriosSeleccion);
    public function eliminar($llaves);
}
