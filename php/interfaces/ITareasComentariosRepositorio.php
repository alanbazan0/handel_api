<?php
namespace php\interfaces;


use php\modelos\TareaComentario;

interface ITareasComentariosRepositorio
{
    public function insertar(TareaComentario $modelo);
    public function actualizar(TareaComentario $modelo);
    public function consultarPorLlaves($id);
    public function consultar($criteriosSeleccion);
    public function eliminar($llaves);
}
