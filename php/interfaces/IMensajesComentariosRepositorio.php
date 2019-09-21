<?php
namespace php\interfaces;

use php\modelos\MensajeComentario;

interface IMensajesComentariosRepositorio
{
    public function insertar(MensajeComentario $modelo);
    public function actualizar(MensajeComentario $modelo);
    public function consultarPorLlaves($id);
    public function consultar($criteriosSeleccion);
    public function eliminar($llaves);
}
