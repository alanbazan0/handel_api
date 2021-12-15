<?php
namespace php\interfaces;

use php\modelos\ProcesoRevisadoObservacion;

interface IProcesosRevisadosObservacionesRepositorio
{
    public function insertar(ProcesoRevisadoObservacion $modelo);
    public function actualizar(ProcesoRevisadoObservacion $modelo);  
    public function consultar($criteriosSeleccion);  
}

