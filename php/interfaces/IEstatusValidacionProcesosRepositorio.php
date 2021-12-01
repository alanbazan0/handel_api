<?php
namespace php\interfaces;

use php\modelos\EstatusValidacionProceso;

interface IEstatusValidacionProcesosRepositorio
{
    public function insertar(EstatusValidacionProceso $modelo);
    public function actualizar(EstatusValidacionProceso $modelo);  
    public function consultar($criteriosSeleccion,$opcional);  
    public function consultarPorLlaves($llaves); 
}

