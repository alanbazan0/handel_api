<?php
namespace php\interfaces;


use php\modelos\ObservacionComentario;

interface IObservacionesComentariosRepositorio
{
    public function insertar($usuario,ObservacionComentario $modelo);
    public function actualizar(ObservacionComentario $modelo);
    public function consultarPorLlaves($id);
    public function consultar($criteriosSeleccion);
    public function eliminar($llaves);
}
