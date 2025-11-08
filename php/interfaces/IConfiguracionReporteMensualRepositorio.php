<?php
namespace php\interfaces;

use php\modelos\ConfiguracionReporteMensual;

interface IConfiguracionReporteMensualRepositorio
{
    public function insertar(ConfiguracionReporteMensual $modelo);
    public function actualizar(ConfiguracionReporteMensual $modelo);
    public function consultarPorLlaves($id);
    public function consultar($criteriosSeleccion);
    public function eliminar($llaves);
}
