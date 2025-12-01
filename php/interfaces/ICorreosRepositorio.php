<?php
namespace php\interfaces;

use php\modelos\Correo;

interface ICorreosRepositorio
{
    public function insertar(Correo $modelo);
    public function actualizar(Correo $modelo);
    public function consultarPorLlaves($id);
    public function consultar($criteriosSeleccion, $limit);
    public function eliminar($llaves);
}
