<?php
namespace php\interfaces;

use php\modelos\Formato;

interface IFormatosRepositorio
{
    public function insertar(Formato $modelo);
    public function actualizar(Formato $modelo);
    public function consultarPorLlaves($id);
    public function consultar($criteriosSeleccion);
    public function eliminar($llaves);
}
