<?php
namespace php\interfaces;

use php\modelos\UsuarioProceso;

interface IUsuariosProcesosRepositorio
{
    public function insertar(UsuarioProceso $modelo);
    public function actualizar(UsuarioProceso $modelo);
    public function consultarPorLlaves($id);
    public function consultar($criteriosSeleccion);
    public function eliminar($llaves);
}
