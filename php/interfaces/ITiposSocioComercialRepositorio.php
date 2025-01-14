<?php
namespace php\interfaces;

use php\modelos\TipoSocioComercial;

interface ITiposSocioComercialRepositorio
{
    public function insertar(TipoSocioComercial $modelo);
    public function actualizar(TipoSocioComercial $modelo);
    public function consultarPorLlaves($id);
    public function consultar($criteriosSeleccion);
    public function eliminar($llaves);
}
