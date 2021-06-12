<?php
namespace php\interfaces;

use php\modelos\Proceso;

interface IProcesosRepositorio
{
    public function insertar(Proceso $modelo);
    public function actualizar(Proceso $modelo);
    public function consultarPorLlaves($id);
    public function consultar($criteriosSeleccion);
    public function eliminar($llaves);
}
