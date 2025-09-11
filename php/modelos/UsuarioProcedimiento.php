<?php
namespace php\modelos;

class UsuarioProcedimiento
{
  public $id;
  public $usuarioId;
  public $procedimientoId;
  public $fechaAlta;
  public $fechaCancelacion;
  public $estatus;
  public $limitarJustificaciones;
  public $limiteJustificaciones;
  
  public function __construct()
  {
     
  }
  
  
  public static function crear( $modelo ) {
      $instance = new self();
      $instance->id = $modelo->id;
      $instance->usuarioId = $modelo->usuarioId;
      $instance->procedimientoId = $modelo->procedimientoId;
      $instance->fechaAlta = $modelo->fechaAlta;
      $instance->fechaCancelacion = $modelo->fechaCancelacion;
      $instance->estatus = $modelo->estatus;
      $instance->limitarJustificaciones = $modelo->limitarJustificaciones;
      $instance->limiteJustificaciones = $modelo->limiteJustificaciones;
      return $instance;
  }
  
}
