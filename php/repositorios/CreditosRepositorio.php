<?php
namespace php\repositorios;

use php\interfaces\ICreditosRepositorio;
use php\modelos\Credito;
use php\modelos\Resultado;

include "../interfaces/ICreditosRepositorio.php";
include "../modelos/Credito.php";
include "RepositorioBase.php";
require_once("../clases/Resultado.php");

class CreditosRepositorio extends RepositorioBase implements ICreditosRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = " SELECT C.id,C.id empresaId, E.nombre empresaNombre, C.id sedeId, S.nombre sedeNombre, C.creditos, C.fecha_activacion, C.fecha_vencimiento " .
            " FROM creditos_historico C " .
            "   INNER JOIN empresas E ON E.id = C.empresa_id " .
            "   LEFT JOIN sedes S ON S.id = C.sede_id";
    }
    
    public function insertar(Credito $modelo)
    {
        $resultado =  $this->calcularId("id","puestos");
        if($resultado->mensajeError=="")
        {
            $id = $resultado->valor;
            $consulta = "INSERT INTO puestos(id, nombre, empresa_id, sede_id, fecha_alta, fecha_modificacion, estatus) " .
                "VALUE(?, ?, ?, ?, NOW(), NOW(), ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if( $sentencia->bind_param("isiii", $id, $modelo->nombre,$modelo->empresaId, $modelo->sedeId, $modelo->estatus))
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
    
    public function actualizar(Credito $modelo)
    {
        $resultado = new Resultado();
        $consulta = " UPDATE puestos " .
            "SET nombre = ?, " .
            "  empresa_id = ?, " .
            "  sede_id = ?, " .
            "  estatus = ?, " .
            "  fecha_modificacion= NOW() " .
            "WHERE id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("siiii", $modelo->nombre, $modelo->empresaId,$modelo->sedeId, $modelo->estatus,$modelo->id ))
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
            if(isset($criteriosSeleccion->empresaId))
            {
                if($criteriosSeleccion->empresaId!="" && $criteriosSeleccion->empresaId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'C','campo'=>'empresa_id','valor'=>$criteriosSeleccion->empresaId]);
            }
            if(isset($criteriosSeleccion->sedeId))
            {
                if($criteriosSeleccion->sedeId!="" && $criteriosSeleccion->sedeId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'C','campo'=>'sede_id','valor'=>$criteriosSeleccion->sedeId]);
            }
            $where = $this->where($filtros);
        }
        $consulta = $this->consultaBase .
        $where . " ORDER BY C.id";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    //C.id,C.id empresaId, E.nombre empresaNombre, C.id sedeId, S.nombre sedeNombre, C.fecha_activacion, P.fecha_vencimiento,C.activacion_automatica, C.estatus
                    if ($sentencia->bind_result($id, $empresaId, $empresaNombre,$sedeId, $sedeNombre, $creditos, $fechaActivacion, $fechaVencimiento))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $empresaId, $empresaNombre,$sedeId, $sedeNombre,$creditos, $fechaActivacion, $fechaVencimiento);
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
        " WHERE P.id  = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$llaves->id))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombre,$empresaId, $empresaNombre,$sedeId, $sedeNombre, $fechaAlta, $fechaModificacion, $estatus))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre,$empresaId, $empresaNombre, $sedeId, $sedeNombre, $fechaAlta, $fechaModificacion, $estatus);
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
    
    private function crearRegistro($id, $empresaId, $empresaNombre,$sedeId, $sedeNombre, $creditos,$fechaActivacion, $fechaVencimiento)
    {
        $registro= (object) [
            'id' =>  $id,
            'empresaId' => $empresaId,
            'empresaNombre' => $empresaNombre,
            'sedeId' => $sedeId,
            'sedeNombre' => $sedeNombre,
            'creditos' => $creditos,
            'fechaActivacion' => $fechaActivacion,
            'fechaVencimiento' => $fechaVencimiento
        ];
        return $registro;
    }
    
    public function consultarPorEmpresaSede($empresaId, $sedeId)
    {
        $resultado = new Resultado();
        $registros = array();
        $consulta = $this->consultaBase .
        " WHERE P.empresa_id  = ?" .
        "   AND P.sede_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ii",$empresaId,$sedeId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombre,$empresaId, $empresaNombre,$sedeId, $sedeNombre, $fechaAlta, $fechaModificacion, $estatus))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre,$empresaId, $empresaNombre,$sedeId, $sedeNombre, $fechaAlta, $fechaModificacion, $estatus);
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
    
    public function eliminar($llaves)
    {
        $resultado = new Resultado();
        $consulta = " DELETE FROM puestos "
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
                $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
                $resultado->codigoError = $this->conexion->errno;
            }
                
        return $resultado;
    }
    
    
}

