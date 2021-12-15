<?php
namespace php\repositorios;

use php\interfaces\IProcesosRevisadosObservacionesRepositorio;
use php\modelos\ProcesoRevisadoObservacion;
use php\modelos\Resultado;

include "../interfaces/IProcesosRevisadosObservacionesRepositorio.php";
include "../modelos/ProcesoRevisadoObservacion.php";
include "../clases/TipoUsuario.php";
include "RepositorioBase.php";
require_once("../clases/Resultado.php");

class ProcesosRevisadosObservacionesRepositorio extends RepositorioBase implements IProcesosRevisadosObservacionesRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = " SELECT PRO.id, tipo_observacion_id, TOB.descripcion, PRO.descripcion, archivo, seccion, IFNULL(DATE_FORMAT(PRO.fecha_alta,'%d/%m/%Y %H:%i:%s'),'')fecha_alta,IFNULL(DATE_FORMAT(PRO.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'')fecha_modificacion, IFNULL(DATE_FORMAT(PRO.fecha_validacion,'%d/%m/%Y %H:%i:%s'),'')fecha_validacion, usuario_validador_id,U.nombre, U.apellido, comentario_validacion
                            FROM procesos_revisados_observaciones PRO
                            	INNER JOIN tipos_observacion TOB ON PRO.tipo_observacion_id = TOB.id 
                                LEFT JOIN usuarios U ON U.id = PRO.usuario_validador_id ";
    }
    
    public function insertar(ProcesoRevisadoObservacion $modelo)
    {
        $resultado =  $this->calcularIdObservacion($modelo->procesoRevisadoId);
        if($resultado->mensajeError=="")
        {
            $id = $resultado->valor;
            $consulta = "INSERT INTO procesos_revisados_observaciones(proceso_revisado_id, id, tipo_observacion_id, descripcion, seccion, fecha_alta, fecha_modificacion) " .
                "VALUE(?, ?, ?, ?, ?, NOW(), NOW())";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if( $sentencia->bind_param("iiiss",$modelo->procesoRevisadoId,$id, $modelo->tipoObservacionId, $modelo->descripcion, $modelo->seccion))
                {
                    if(!$sentencia->execute())
                        $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
                else
                    $resultado->mensajeError = "Falló el enlace de parámetros";
            }
            else
                $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
        }
        return $resultado;
    }
    
    public function actualizar(ProcesoRevisadoObservacion $modelo)
    {
        $resultado = new Resultado();
        $consulta = " UPDATE areas " .
            "SET nombre = ?, " .
            "  empresa_id = ?, " .
            "  sede_id = ?, " .
            "  estatus = ?, " .
            "  tipo_area_id = ?, " .
            "  fecha_modificacion= NOW() " .
            "WHERE id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("siiiii", $modelo->nombre, $modelo->empresaId,  $modelo->sedeId, $modelo->estatus,$modelo->tipoAreaId ,$modelo->id ))
            {
                if($sentencia->execute())
                {
                    $resultado->valor=true;
                }
                else
                    $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else  $resultado->mensajeError = "Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            return $resultado;
    }
    
    public function consultar($criteriosSeleccion)
    {
       
        $resultado = new Resultado();
        $registros = array();
        $filtros = array();
        $where="";
        if($criteriosSeleccion!=null)
        {
            if(isset($criteriosSeleccion->procesoRevisadoId))
            {
                if($criteriosSeleccion->procesoRevisadoId!="" && $criteriosSeleccion->procesoRevisadoId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'PRO','campo'=>'proceso_revisado_id','valor'=>$criteriosSeleccion->procesoRevisadoId]);
            }
            $where = $this->where($filtros);
        }
        $consulta = $this->consultaBase .
        $where . " order by PRO.id";     
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $tipoObservacionId, $tipoObservacionDescripcion, $descripcion, $archivo, $seccion, $fechaAlta, $fechaModificacion, $fechaValidacion, $validadorId, $validadorNombre, $validadorApellido, $comentarioValidacion))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $tipoObservacionId, $tipoObservacionDescripcion, $descripcion, $archivo, $seccion, $fechaAlta, $fechaModificacion, $fechaValidacion, $validadorId, $validadorNombre, $validadorApellido, $comentarioValidacion );
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = "Falló el enlace del resultado.";
                }
                else
                    $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = "Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
            
            return $resultado;
    }
    
    public function consultarPorLlaves($llaves)
    {
        $resultado = new Resultado();
        $consulta = $this->consultaBase .
        " WHERE A.id  = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$llaves->id))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombre, $empresaId, $empresaNombre, $sedeId, $sedeNombre, $fechaAlta, $fechaModificacion, $estatus, $tipoAreaId))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre,$empresaId, $empresaNombre,$sedeId, $sedeNombre, $fechaAlta, $fechaModificacion, $estatus,$tipoAreaId);
                            $resultado->valor = $registro;
                        }
                        else
                            $resultado->mensajeError = "No se encontró ningún resultado.";
                    }
                    else
                        $resultado->mensajeError = "Falló el enlace del resultado";
                }
                else
                    $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = "Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            return $resultado;
    }
    
    private function crearRegistro($id, $tipoObservacionId, $tipoObservacionNombre, $descripcion, $archivo, $seccion, $fechaAlta, $fechaModificacion, $fechaValidacion, $validadorId, $validadorNombre, $validadorApellido, $comentarioValidacion)
    {
        $registro= (object) [
            'id' =>  $id,
            'tipoObservacionId' => $tipoObservacionId,
            'tipoObservacionNombre' => $tipoObservacionNombre,
            'descripcion' => $descripcion,
            'archivo' => $archivo,
            'seccion' => $seccion,
            'fechaAlta' => $fechaAlta,
            'fechaModificacion' => $fechaModificacion,
            'fechaValidacion' => $fechaValidacion,
            'validadorId' => $validadorId,
            'validadorNombre' => $validadorNombre,
            'validadorApellido' => $validadorApellido,
            'comentarioValidacion' => $comentarioValidacion
        ];
        return $registro;
    }
    
    public function consultarPorEmpresa($empresaId)
    {
        $resultado = new Resultado();
        $registros = array();
        $consulta = $this->consultaBase .
        " WHERE A.empresa_id  = ? order by A.nombre";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$empresaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombre, $empresaId, $empresaNombre, $sedeId, $sedeNombre, $fechaAlta, $fechaModificacion, $estatus, $tipoAreaId))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre,$empresaId, $empresaNombre,$sedeId, $sedeNombre, $fechaAlta, $fechaModificacion, $estatus,$tipoAreaId);
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = "Falló el enlace del resultado";
                }
                else
                    $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = "Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            return $resultado;
    }
    
    public function consultarPorEmpresaSede($empresaId, $sedeId,$opcional,$usuario)
    {
        $resultado = new Resultado();
        $registros = array();
        $filtros = array();
        $where="";
//         if($usuario->tipoUsuarioId == \TipoUsuario::SUPERUSUARIO)
//         {
            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'A','campo'=>'empresa_id','valor'=>$empresaId]);
            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'A','campo'=>'sede_id','valor'=>$sedeId]);
//         }
//         else  if($usuario->tipoUsuarioId == \TipoUsuario::ADMINISTRADOR_CORPORATIVO)
//             array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'S','campo'=>'empresa_id','valor'=>$empresaId]);
//         else  if($usuario->tipoUsuarioId == \TipoUsuario::ADMINISTRADOR)
//         {
//             array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'A','campo'=>'empresa_id','valor'=>$usuario->empresaId]);
//             array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'A','campo'=>'sede_id','valor'=>$usuario->sedeId]);
//             //array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'A','campo'=>'id','valor'=>$usuario->areaId]);
//         }
            
        $where = $this->where($filtros);
        
        $consulta = $this->consultaBase .
        $where . " order by A.nombre";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombre, $empresaId, $empresaNombre, $sedeId, $sedeNombre, $fechaAlta, $fechaModificacion, $estatus, $tipoAreaId))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre,$empresaId, $empresaNombre,$sedeId, $sedeNombre, $fechaAlta, $fechaModificacion, $estatus,$tipoAreaId);
                            array_push($registros,$registro);
                        }
                        if($opcional=="true")
                        {
                            //if($usuario->tipoUsuarioId == \TipoUsuario::SUPERUSUARIO || $usuario->tipoUsuarioId == \TipoUsuario::ADMINISTRADOR_CORPORATIVO  )
                            //{
                            $registro = $this->crearRegistro("", "Todas las areas",null, null,null, null, null, null, null,null);
                                array_unshift($registros, $registro);
                            //}
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = "Falló el enlace del resultado";
                }
                else
                    $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = "Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            return $resultado;
    }
    
    public function eliminar($llaves)
    {
        $resultado = new Resultado();
        $consulta = " DELETE FROM areas "
            . "  WHERE id  = ? ";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param("i",$llaves->id))
                {
                    if($sentencia->execute())
                    {
                        $resultado->valor = $llaves->id;
                    }
                    else
                    {
                        $resultado->codigoError = $this->conexion->errno;
                        $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                    }
                }
                else
                    $resultado->mensajeError = "Falló el enlace de parámetros";
            }
            else
            {
                $resultado->codigoError = $this->conexion->errno;
                $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
              
            }
                
           return $resultado;
    }
    
    public function calcularIdObservacion($procesoRevisadoId)
    {
        $resultado = new Resultado();
        $consulta =  "SELECT IFNULL(MAX(id),0)+1 AS id FROM procesos_revisados_observaciones where proceso_revisado_id = $procesoRevisadoId";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->execute())
            {
                if ($sentencia->bind_result($id))
                {
                    if($sentencia->fetch())
                    {
                        $resultado->valor = $id;
                    }
                    else
                        $resultado->mensajeError =  __FUNCTION__. " No se encontró ningún resultado";
                }
                else
                    $resultado->mensajeError =  __FUNCTION__. " Falló el enlace del resultado";
            }
            else
                $resultado->mensajeError =  __FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
        }
        else
            $resultado->mensajeError =  __FUNCTION__." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            return $resultado;
    }
    
}

