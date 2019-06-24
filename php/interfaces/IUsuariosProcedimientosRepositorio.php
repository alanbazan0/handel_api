<?php
namespace php\interfaces;

use php\modelos\UsuarioProcedimiento;

interface IUsuariosProcedimientosRepositorio
{
    public function insertar(UsuarioProcedimiento $modelo);
    public function actualizar(UsuarioProcedimiento $modelo);
    public function consultarPorLlaves($id);
    public function consultar($criteriosSeleccion);
    public function eliminar($llaves);
}
