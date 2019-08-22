<?php
namespace php\interfaces;

use php\modelos\Mensaje;

interface IMensajesRepositorio
{
    public function insertar(Mensaje $modelo);
    public function actualizar(Mensaje $modelo);
    public function consultarPorLlaves($id);
    public function consultar($criteriosSeleccion,$usuario);
    public function eliminar($llaves);
}
