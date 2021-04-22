<?php
namespace php\interfaces;

use php\modelos\RecomendacionComentario;

interface IRecomendacionesComentariosRepositorio
{
    public function insertar($usuario,RecomendacionComentario $modelo);
    public function actualizar(RecomendacionComentario $modelo);
    public function consultarPorLlaves($id);
    public function consultar($criteriosSeleccion);
    public function eliminar($llaves);
}
