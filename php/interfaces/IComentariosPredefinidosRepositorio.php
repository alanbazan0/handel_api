<?php
namespace php\interfaces;

use php\modelos\ComentarioPredefinido;

interface IComentariosPredefinidosRepositorio
{
    public function insertar(ComentarioPredefinido $modelo);
    public function actualizar(ComentarioPredefinido $modelo);
    public function consultarPorLlaves($id);
    public function consultar($criteriosSeleccion);
    public function eliminar($llaves);
}
