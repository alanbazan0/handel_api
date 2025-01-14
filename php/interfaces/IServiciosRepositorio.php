<?php
namespace php\interfaces;

use php\modelos\Servicio;

interface IServiciosRepositorio
{
    public function insertar(Servicio $modelo);
    public function actualizar(Servicio $modelo);
    public function consultarPorLlaves($id);
    public function consultar($criteriosSeleccion);
    public function eliminar($llaves);
}
