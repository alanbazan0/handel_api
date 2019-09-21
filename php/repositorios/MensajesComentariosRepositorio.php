<?php
namespace php\repositorios;

use php\interfaces\IMensajesComentariosRepositorio;
use php\modelos\MensajeComentario;
use php\modelos\Resultado;

include '../interfaces/IMensajesComentariosRepositorio.php';
include '../modelos/MensajeComentario.php';
include 'RepositorioBase.php';
require_once('../clases/Resultado.php');

class MensajesComentariosRepositorio extends RepositorioBase implements IMensajesComentariosRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = "SELECT MC.id, mensaje_id, usuario_id, comentario, IFNULL(DATE_FORMAT(fecha,'%m/%d/%Y %H:%i:%s'),'')  as fecha , U.nombre, U.apellido
                            FROM mensajes_comentarios MC
                                INNER JOIN usuarios U ON U.id = MC.usuario_id ";
    }

    public function insertar(MensajeComentario $modelo)
    {
        $resultado = $this->calcularId('id','mensajes_comentarios');
        if($resultado->mensajeError=='')
        {
            $id = $resultado->valor;
            $consulta = "INSERT INTO mensajes_comentarios(id, mensaje_id, usuario_id, comentario, fecha)VALUES(?, ?, ?, ?, NOW())";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('iiis', $id, $modelo->mensajeId, $modelo->usuarioId, $modelo->comentario))
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

    public function actualizar(MensajeComentario $modelo)
    {
        $resultado = new Resultado();
        $consulta = "UPDATE mensajes_comentarios
                     SET 
                         mensaje_id = ?,
                         usuario_id = ?,
                         comentario = ?,
                         fecha = ?
                     WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('iissi',$modelo->mensajeId, $modelo->usuarioId, $modelo->comentario, $modelo->fecha ,$modelo->id ))
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
            if(isset($criteriosSeleccion->mensajeId))
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'MC', 'campo'=>'mensaje_id','valor'=>$criteriosSeleccion->mensajeId]);
            $where = $this->where($filtros);
        }
        $consulta = $this->consultaBase . $where;
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $mensajeId, $usuarioId, $comentario, $fecha,$usuarionNombre, $usuarioApellido))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $mensajeId, $usuarioId, $comentario, $fecha,$usuarionNombre, $usuarioApellido);
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
                    if($sentencia->bind_result($id, $mensajeId, $usuarioId, $comentario, $fecha, $usuarionNombre, $usuarioApellido))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $mensajeId, $usuarioId, $comentario, $fecha,$usuarionNombre, $usuarioApellido);
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
        $consulta = "DELETE FROM mensajes_comentarios WHERE id = ?";
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

    private function crearRegistro($id, $mensajeId, $usuarioId, $comentario, $fecha, $usuarioNombre, $usuarioApellido)
    {
        $registro= (object) 
        [
            'id' => $id,
            'mensajeId' => $mensajeId,
            'usuarioId' => $usuarioId,
            "usuarioNombre" => $usuarioNombre,
            "usuarioApellido" => $usuarioApellido,
            'comentario' => $comentario,
            'fecha' => $fecha
        ];
        
        $registro->usuarioNombreCompleto = $registro->usuarioNombre . " " . $registro->usuarioApellido;
        $registro->fotoPerfil =  "../fotos/usuario". $registro->usuarioId .".jpg";
        if(file_exists($registro->fotoPerfil))
            $registro->fotoPerfil =  "php/fotos/usuario". $registro->usuarioId .".jpg";
        else
            $registro->fotoPerfil =  "php/fotos/default.jpg";
        
        return $registro;
    }
}
