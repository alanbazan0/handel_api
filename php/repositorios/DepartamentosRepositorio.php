<?php
namespace php\repositorios;

use php\interfaces\IDepartamentosRepositorio;
use php\modelos\Departamento;
use php\modelos\Resultado;

include '../interfaces/IDepartamentosRepositorio.php';
include '../modelos/Departamento.php';
include 'RepositorioBase.php';
require_once('../clases/Resultado.php');

class DepartamentosRepositorio extends RepositorioBase implements IDepartamentosRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = "SELECT id, nombre,IFNULL(DATE_FORMAT(fecha_alta,'%d/%m/%Y %H:%i:%s'),'') fecha_alta,IFNULL(DATE_FORMAT(fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'') fecha_modificacion,estatus FROM departamentos D";
    }

    public function insertar(Departamento $modelo)
    {
        $resultado = $this->calcularId('id','departamentos');
        if($resultado->mensajeError=='')
        {
            $id = $resultado->valor;
            $consulta = "INSERT INTO departamentos(id, nombre,estatus, fecha_alta, fecha_modificacion)VALUES(?, ?, ?, NOW(), NOW())";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('isi', $id, $modelo->nombre, $modelo->estatus))
                {
                    if(!$sentencia->execute())
                        $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
                }
                else
                    $resultado->mensajeError = 'Falló el enlace de parámetros';
            }
            else
                $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' .$this->conexion->error;
        }
        return $resultado;
    }

    public function actualizar(Departamento $modelo)
    {
        $resultado = new Resultado();
        $consulta = "UPDATE departamentos
                     SET 
                         nombre = ?,
                         estatus = ?,
                        fecha_modificacion = NOW()
                     WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('sii',$modelo->nombre ,$modelo->estatus,$modelo->id ))
            {
                if($sentencia->execute())
                {
                    $resultado->valor=true;
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        return $resultado;
    }

    public function consultar($criteriosSeleccion,$opcional)
    {
        $resultado = new Resultado();
        $registros = array();
        $filtros = array();
        $where='';
        if($criteriosSeleccion!=null)
        {
            if(isset($criteriosSeleccion->nombre))
            {
                if($criteriosSeleccion->nombre!="" && $criteriosSeleccion->nombre!=null)
                   array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'D','campo'=>'nombre','valor'=>$criteriosSeleccion->nombre]);
            }
            $where = $this->where($filtros);
        }
        $consulta = $this->consultaBase .
        $where . " order by A.nombre";   
        $consulta = $this->consultaBase . $where . " order by nombre";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre,$fechaAlta, $fechaModificacion, $estatus))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre,$fechaAlta, $fechaModificacion, $estatus);
                            array_push($registros,$registro);
                        }
                        if($opcional=="true")
                        {
//                             if($usuario->tipoUsuarioId == \TipoUsuario::ADMINISTRADOR)
//                             {
                            $registro = $this->crearRegistro("", "Todos los departamentos","",null, null, null);
                            array_unshift($registros, $registro);
                           // }
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        return $resultado;
    }

    public function consultarPorLlaves($llaves)
    {
        $resultado = new Resultado();
        $consulta = $this->consultaBase .
        ' WHERE id  = ?';
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('i',$llaves->id))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre, $fechaAlta, $fechaModificacion, $estatus))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre,$fechaAlta, $fechaModificacion, $estatus);
                            $resultado->valor = $registro;
                        }
                        else
                            $resultado->mensajeError = 'No se encontró ningún resultado.';
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace del resultado';
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        return $resultado;
    }

    public function eliminar($llaves)
    {
        $resultado = new Resultado();
        $consulta = "DELETE FROM departamentos WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('i',$llaves->id))
            {
                if($sentencia->execute())
                {
                    $resultado->valor = $llaves->id;
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
                }
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        }
        return $resultado;
    }

    private function crearRegistro($id, $nombre,$fechaAlta, $fechaModificacion, $estatus)
    {
        $registro= (object) 
        [
            'id' => $id,
            'nombre' => $nombre,
            'fechaAlta' => $fechaAlta,
            'fechaModificacion' => $fechaModificacion,
            'estatus' => $estatus
        ];
        return $registro;
    }
}
