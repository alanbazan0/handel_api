<?php
namespace php\repositorios;

use php\interfaces\IEvidenciasComentariosRepositorio;
use php\modelos\EvidenciaComentario;
use php\modelos\Resultado;

include '../interfaces/IEvidenciasComentariosRepositorio.php';
include '../modelos/EvidenciaComentario.php';
include 'RepositorioBase.php';
require_once('../clases/Resultado.php');

class EvidenciasComentariosRepositorio extends RepositorioBase implements IEvidenciasComentariosRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase =  "SELECT EC.id, usuario_id, U.nombre, U.apellido, comentario, IFNULL(DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s'),'') as fecha
                    FROM evidencias_comentarios EC
                    	INNER JOIN usuarios U ON U.id = EC.usuario_id ";
                   
    }

    public function insertar(EvidenciaComentario $modelo)
    {
        $resultado = $this->calcularId('id','evidencias_comentarios');
        if($resultado->mensajeError=='')
        {
            $id = $resultado->valor;
            $consulta = "INSERT INTO evidencias_comentarios(id, evidencia_id, usuario_id, comentario, fecha)VALUES(?, ?, ?, ?, NOW())";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('iiis', $id, $modelo->evidenciaId, $modelo->usuarioId, $modelo->comentario))
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
    
    public function actualizar(EvidenciaComentario $modelo)
    {
        $resultado = new Resultado();
        $consulta = "UPDATE evidencias_comentarios
                     SET
                         evidencia_id = ?,
                         usuario_id = ?,
                         comentario = ?,
                         fecha = ?
                     WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('iissi',$modelo->evidenciaId, $modelo->usuarioId, $modelo->comentario, $modelo->fecha ,$modelo->id ))
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
        
        $consulta = $this->consultaBase .
                        "WHERE evidencia_id = ? " .
                        "ORDER By EC.id";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$criteriosSeleccion->evidenciaId))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $usuarioId,$usuarioNombre,$usuarioApellido, $comentario, $fecha))
                    {
                        while($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioId,$usuarioNombre,$usuarioApellido, $comentario, $fecha);
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
            if($sentencia->bind_param('',$llaves->id))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result())
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioId,$usuarioNombre,$usuarioApellido, $comentario, $fecha);
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
        $consulta = "DELETE FROM evidencia_comentarios WHERE ";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('',$llaves->id))
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

    private function crearRegistro($id, $usuarioId,$usuarioNombre,$usuarioApellido, $comentario, $fecha)
    {
        $registro= (object) 
        [
            "id" => $id,
            "usuarioId" => $usuarioId,
            "usuarioNombre" => $usuarioNombre,
            "usuarioApellido" => $usuarioApellido,
            "comentario" => $comentario,
            "fecha" => $fecha,
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
