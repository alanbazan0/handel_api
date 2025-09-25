<?php
namespace php\interfaces;

use php\modelos\UsuarioFormato;

interface IUsuariosFormatosRepositorio
{
    public function insertar(UsuarioFormato $modelo);
    public function actualizar(UsuarioFormato $modelo);
    public function consultarPorLlaves($id);
    public function consultar($criteriosSeleccion);
    public function eliminar($llaves);
}
