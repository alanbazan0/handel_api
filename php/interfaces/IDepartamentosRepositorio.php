<?php
namespace php\interfaces;

use php\modelos\Departamento;

interface IDepartamentosRepositorio
{
    public function insertar(Departamento $modelo);
    public function actualizar(Departamento $modelo);
    public function consultarPorLlaves($id);
    public function consultar($criteriosSeleccion,$opcional);
    public function eliminar($llaves);
}
