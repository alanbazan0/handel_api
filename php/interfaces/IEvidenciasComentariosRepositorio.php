<?php
namespace php\interfaces;

use php\modelos\EvidenciaComentario;

interface IEvidenciasComentariosRepositorio
{
    public function insertar(EvidenciaComentario $modelo);
    public function actualizar(EvidenciaComentario $modelo);
    public function consultarPorLlaves($id);
    public function consultar($criteriosSeleccion);
    public function eliminar($llaves);
}
