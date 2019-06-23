<?php
namespace php\interfaces;

use php\modelos\Procedimiento;

interface IProcedimientosRepositorio
{
    public function insertar(Procedimiento $modelo);
    public function actualizar(Procedimiento $modelo);
    public function consultarPorLlaves($id);
    public function consultar($criteriosSeleccion);
    public function eliminar($llaves);
}
