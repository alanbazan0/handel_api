<?php
namespace php\interfaces;

use php\modelos\ProcesoRevisado;

interface IProcesosRevisadosRepositorio
{
    public function insertar($usuario,ProcesoRevisado $modelo);
    public function actualizar(ProcesoRevisado $modelo,$nombreArchivoSubido);
    public function consultarPorLlaves($id);
    public function consultar($criteriosSeleccion);
    public function eliminar($llaves);
}
