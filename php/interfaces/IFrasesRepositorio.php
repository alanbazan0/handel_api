<?php
namespace php\interfaces;

use php\modelos\Frase;

interface IFrasesRepositorio
{
    public function insertar(Frase $modelo);
    public function actualizar(Frase $modelo);
    public function consultarPorLlaves($id);
    public function consultar($criteriosSeleccion);
    public function eliminar($llaves);
}
