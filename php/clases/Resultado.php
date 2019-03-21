<?php
namespace php\modelos;

class Resultado
{   
    public function __construct()
    {
        $this->mensajeError = "";
        $this->codigoError=0;
        
    }
    public $mensajeError;
    public $valor;
    public $codigoError;
}
  

