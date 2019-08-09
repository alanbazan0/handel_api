<?php
namespace php\repositorios;

use php\interfaces\IUsuariosProcedimientosRepositorio;
use php\modelos\UsuarioProcedimiento;
use php\modelos\Resultado;

include '../interfaces/IUsuariosProcedimientosRepositorio.php';
include '../modelos/UsuarioProcedimiento.php';
require_once('RepositorioBase.php');
include "../clases/TipoUsuario.php";
require_once('../clases/Resultado.php');

class UsuariosProcedimientosRepositorio extends RepositorioBase implements IUsuariosProcedimientosRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = "SELECT UP.id, usuario_id,CONCAT(U.nombre,' ',U.apellido) usuarioNombre, procedimiento_id, P.nombre, IFNULL(DATE_FORMAT(UP.fecha_alta,'%d/%m/%Y'),'')fecha_alta, IFNULL(DATE_FORMAT(UP.fecha_cancelacion,'%d/%m/%Y'),'')fecha_cancelacion, UP.estatus, limitar_justificaciones, limite_justificaciones 
                                FROM usuarios_procedimientos UP
                                    LEFT JOIN usuarios U ON U.id = UP.usuario_id
                                    LEFT JOIN procedimientos P ON P.id = UP.procedimiento_id
                                ";
    }

    public function insertar(UsuarioProcedimiento $modelo)
    {
        $resultado = $this->calcularId('id','usuarios_procedimientos');
        if($resultado->mensajeError=='')
        {
            $id = $resultado->valor;
            $consulta = "INSERT INTO usuarios_procedimientos(id, usuario_id, procedimiento_id, fecha_alta, estatus, limitar_justificaciones, limite_justificaciones)VALUES(?, ?, ?, NOW(), 1, ?, ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('iiiii', $id, $modelo->usuarioId, $modelo->procedimientoId,$modelo->limitarJustificaciones, $modelo->limiteJustificaciones))
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

    public function actualizar(UsuarioProcedimiento $modelo)
    {
        $resultado = new Resultado();
        $consulta = "UPDATE usuarios_procedimientos
                     SET 
                         usuario_id = ?,
                         procedimiento_id = ?,
                         fecha_alta = ?,
                         fecha_cancelacion = ?,
                         estatus = ?
                     WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('iissii',$modelo->usuarioId, $modelo->procedimientoId, $modelo->fechaAlta, $modelo->fechaCancelacion, $modelo->estatus ,$modelo->id ))
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

    public function consultar($criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        $filtros = array();
        $where='';
        if($criteriosSeleccion!=null)
        {
            if(isset($criteriosSeleccion->nombre))
                array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'U','campo'=>'nombre','valor'=>$criteriosSeleccion->nombre]);
                if(isset($criteriosSeleccion->empresaId))
                {
                    if($criteriosSeleccion->empresaId!="" && $criteriosSeleccion->empresaId!=null)
                        array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'empresa_id','valor'=>$criteriosSeleccion->empresaId]);
                }
                if(isset($criteriosSeleccion->sedeId))
                {
                    if($criteriosSeleccion->sedeId!="" && $criteriosSeleccion->sedeId!=null)
                        array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'sede_id','valor'=>$criteriosSeleccion->sedeId]);
                }
                $where = $this->where($filtros);
        }
        $consulta = $this->consultaBase . $where . " order by U.fecha_alta desc";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $usuarioId, $usuarioNombre, $procedimientoId, $procedimientoNombre, $fechaAlta, $fechaCancelacion, $estatus,$limitarJustificaciones,$limiteJustificaciones ))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioId, $usuarioNombre, $procedimientoId, $procedimientoNombre, $fechaAlta, $fechaCancelacion, $estatus,$limitarJustificaciones,$limiteJustificaciones );
                            array_push($registros,$registro);
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
    
    public function existeUsuarioProcedimientoEnMesActual($usuarioProcedimientoId)
    {
        $resultado = new Resultado();
        $resultado->valor = false;
        
        $consulta = "SELECT count(*) 
                FROM evidencias E 
                WHERE usuario_procedimiento_id= ?
                	AND MONTH(fecha_alta) = MONTH(NOW())";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$usuarioProcedimientoId))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($count))
                    {
                        if($sentencia->fetch())
                        {
                           if($count>0)
                               $resultado->valor = true;
                        }
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
    
    public function consultarProcedimientosPendientesMesActual($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $filtros = array();
        $and="";
        if($criteriosSeleccion!=null)
        {
        }
        if($usuario!=null)
        {
            if($usuario->tipoUsuarioId == \TipoUsuario::SUPERVISOR || $usuario->tipoUsuarioId == \TipoUsuario::COORDINADOR || $usuario->tipoUsuarioId == \TipoUsuario::USUARIO)
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'id','valor'=>$usuario->id]);
        }
        
        $and = $this->and($filtros);
       
        $consulta = "SELECT UP.id, codigo, P.nombre
                    FROM usuarios_procedimientos UP
                    	INNER JOIN procedimientos P ON P.id = UP.procedimiento_id
                        INNER JOIN usuarios U ON U.id = UP.usuario_id
                    WHERE UP.estatus = 1 
                    	AND UP.id NOT IN(SELECT usuario_procedimiento_id FROM evidencias E WHERE MONTH(E.fecha_alta) = MONTH(NOW())) " . $and . " " .
                    "ORDER BY codigo";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $codigo,$nombre))
                    {
                        while($sentencia->fetch())
                        {
                            $procedimiento= (object) [
                                'id' =>  $id,
                                'codigo' =>  $codigo,
                                'nombre' =>  $nombre
                            ];
                            array_push($registros,$procedimiento);
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
        ' WHERE UP.id  = ?';
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('i',$llaves->id))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $usuarioId, $usuarioNombre, $procedimientoId, $procedimientoNombre, $fechaAlta, $fechaCancelacion, $estatus,$limitarJustificaciones,$limiteJustificaciones ))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioId, $usuarioNombre, $procedimientoId, $procedimientoNombre, $fechaAlta, $fechaCancelacion, $estatus,$limitarJustificaciones,$limiteJustificaciones );
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
        $consulta = "DELETE FROM usuarios_procedimientos WHERE id = ?";
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

    private function crearRegistro($id, $usuarioId, $usuarioNombre, $procedimientoId, $procedimientoNombre, $fechaAlta, $fechaCancelacion, $estatus,$limitarJustificaciones,$limiteJustificaciones )
    {
        $registro= (object) 
        [
            'id' => $id,
            'usuarioId' => $usuarioId,
            'usuarioNombre' => $usuarioNombre,
            'procedimientoId' => $procedimientoId,
            'procedimientoNombre' => $procedimientoNombre,
            'fechaAlta' => $fechaAlta,
            'fechaCancelacion' => $fechaCancelacion,
            'estatus' => $estatus,
            'limitarJustificaciones' => $limitarJustificaciones,
            'limiteJustificaciones' => $limiteJustificaciones
        ];
        return $registro;
    }
}
