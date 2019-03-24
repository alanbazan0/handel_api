<?php
namespace php\repositorios;

use php\interfaces\IAreasRepositorio;
use php\modelos\Area;
use php\modelos\Resultado;

include "../interfaces/IAreasRepositorio.php";
include "../modelos/Area.php";
include "../clases/TipoUsuario.php";
include "RepositorioBase.php";
require_once("../clases/Resultado.php");

class AreasRepositorio extends RepositorioBase implements IAreasRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = " SELECT A.id, A.nombre, E.id empresaId, E.nombre empresaNombre, S.id, S.nombre sedeNombre, A.fecha_alta, A.fecha_modificacion, A.estatus, tipo_area_id " .
            " FROM areas A " .
            "   LEFT JOIN empresas E on E.id = A.empresa_id " .
            "   LEFT JOIN sedes S on S.id = A.sede_id ";
    }
    
    public function insertar(Area $modelo)
    {
        $resultado =  $this->calcularId("id","areas");
        if($resultado->mensajeError=="")
        {
            $id = $resultado->valor;
            $consulta = "INSERT INTO areas(id, nombre, empresa_id, sede_id, fecha_alta, fecha_modificacion, estatus, tipo_area_id) " .
                "VALUE(?, ?, ?, ?, NOW(), NOW(), ?, ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if( $sentencia->bind_param("isiiii", $id, $modelo->nombre,$modelo->empresaId, $modelo->sedeId, $modelo->estatus, $modelo->tipoAreaId))
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
    
    public function actualizar(Area $modelo)
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
            if(isset($criteriosSeleccion->nombre))
                array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'A','campo'=>'nombre','valor'=>$criteriosSeleccion->nombre]);
            if(isset($criteriosSeleccion->empresaId))
            {
                if($criteriosSeleccion->empresaId!="" && $criteriosSeleccion->empresaId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'A','campo'=>'empresa_id','valor'=>$criteriosSeleccion->empresaId]);
            }
            if(isset($criteriosSeleccion->sedeId))
            {
                if($criteriosSeleccion->sedeId!="" && $criteriosSeleccion->sedeId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'A','campo'=>'sede_id','valor'=>$criteriosSeleccion->sedeId]);
            }
             $where = $this->where($filtros);
        }
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
    
    private function crearRegistro($id, $nombre, $empresaId, $empresaNombre, $sedeId, $sedeNombre, $fechaAlta, $fechaModificacion, $estatus, $tipoAreaId)
    {
        $registro= (object) [
            'id' =>  $id,
            'nombre' => $nombre,
            'empresaId' => $empresaId,
            'empresaNombre' => $empresaNombre,
            'sedeId' => $sedeId,
            'sedeNombre' => $sedeNombre,
            'fechaAlta' => $fechaAlta,
            'fechaModificacion' => $fechaModificacion,
            'estatus' => $estatus,
            'tipoAreaId' => $tipoAreaId
        ];
        return $registro;
    }
    
    public function consultarPorEmpresa($empresaId)
    {
        $resultado = new Resultado();
        $registros = array();
        $consulta = $this->consultaBase .
        " WHERE A.empresa_id  = ?";
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
        $where;
        
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
    
}

