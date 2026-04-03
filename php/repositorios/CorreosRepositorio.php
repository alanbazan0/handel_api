<?php
namespace php\repositorios;

use php\interfaces\ICorreosRepositorio;
use php\modelos\Correo;
use php\modelos\Resultado;

include '../interfaces/ICorreosRepositorio.php';
include '../modelos/Correo.php';
require_once('RepositorioBase.php');
require_once('../clases/Resultado.php');
require_once('../clases/EstatusCorreo.php');

class CorreosRepositorio extends RepositorioBase implements ICorreosRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = "SELECT C.id, usuario_id, asunto, mensaje, C.estatus, IFNULL(DATE_FORMAT(fecha_envio,'%d/%m/%Y %H:%i:%s'),'') as fecha_envio, IFNULL(DATE_FORMAT(C.fecha_alta,'%d/%m/%Y %H:%i:%s'),'') as fecha_alta, C.tipo, U.nombre, U.apellido, U.nombre_usuario, U.tipo_usuario_id, cabecera, U.recursos_humanos, U.empresa_id, C.fecha_modificacion
                    FROM correos C
                    	INNER JOIN usuarios U ON U.id = C.usuario_id";
    }

    public function insertar(Correo $modelo)
    {
        $resultado = $this->calcularId('id','correos');
        if($resultado->mensajeError=='')
        {
            $id = $resultado->valor;
            $consulta = "INSERT INTO correos(id, usuario_id, asunto, mensaje, estatus, fecha_envio, fecha_alta, tipo, fecha_inicio_procesamiento, fecha_finalizacion_procesamiento, fecha_modificacion)VALUES(?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, NOW())";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('iississss', $id, $modelo->usuarioId, $modelo->asunto, $modelo->mensaje, $modelo->estatus, $modelo->fechaEnvio, $modelo->tipo, $modelo->fechaInicioProcesamiento, $modelo->fechaFinalizacionProcesamiento))
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
    
    public function procesando($id)
    {
        $resultado = new Resultado();
        $consulta = "UPDATE correos
                     SET
                         estatus = ?,
                         fecha_inicio_procesamiento = NOW(),
                         fecha_modificacion = NOW()
                     WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            $estatus =  \EstatusCorreo::PROCESANDO;
            if($sentencia->bind_param('ii', $estatus, $id ))
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
    
    public function enviado($id)
    {
        $resultado = new Resultado();
        $consulta = "UPDATE correos
                     SET
                         estatus = ?,
                         fecha_envio = NOW(),
                         fecha_modificacion = NOW()
                     WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            $estatus =  \EstatusCorreo::ENVIADO;
            if($sentencia->bind_param('ii', $estatus, $id ))
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
    
    public function noEnviado($id, $mensaje)
    {
        $resultado = new Resultado();
        $consulta = "UPDATE correos
                     SET
                         estatus = ?,
                         mensaje = ?,
                         fecha_modificacion = NOW()
                     WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            $estatus =  \EstatusCorreo::NO_ENVIADO;
            if($sentencia->bind_param('isi', $estatus, $mensaje, $id ))
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
    
    public function omitido($id, $error)
    {
        $resultado = new Resultado();
        $consulta = "UPDATE correos
                     SET
                         estatus = ?,
                         error = ?,
                         fecha_finalizacion_procesamiento = NOW(),
                         fecha_modificacion = NOW()
                     WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            $estatus =  \EstatusCorreo::OMITIDO;
            if($sentencia->bind_param('isi', $estatus, $error, $id ))
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
    
    public function procesado($id, $asunto, $contenido, $cabecera)
    {
        $resultado = new Resultado();
        $consulta = "UPDATE correos
                     SET
                         estatus = ?,
                         asunto = ?,
                         mensaje = ?,
                         cabecera = ?,
                         fecha_finalizacion_procesamiento = NOW(),
                         fecha_modificacion = NOW()
                     WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            $estatus = \EstatusCorreo::PROCESADO;
            if($sentencia->bind_param('isssi', $estatus, $asunto, $contenido, $cabecera, $id ))
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

    public function actualizar(Correo $modelo)
    {
        $resultado = new Resultado();
        $consulta = "UPDATE correos
                     SET 
                         usuario_id = ?,
                         asunto = ?,
                         mensaje = ?,
                         estatus = ?,
                         fecha_envio = ?,
                         fecha_alta = ?,
                         fecha_modificacion = NOW()
                     WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('ississi',$modelo->usuarioId, $modelo->asunto, $modelo->mensaje, $modelo->estatus, $modelo->fechaEnvio, $modelo->fechaAlta ,$modelo->id ))
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

    public function consultar($criteriosSeleccion, $limit)
    {
        $resultado = new Resultado();
        $registros = array();
        $filtros = array();
        $where='';
        if($criteriosSeleccion!=null)
        {
            if($criteriosSeleccion->estatus!="")
                array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'C','campo'=>'estatus','valor'=>$criteriosSeleccion->estatus]);
           
            if(isset($criteriosSeleccion->tipo))
                array_push($filtros,(object)['tipoDato'=>'varchar','tabla' => 'C', 'campo'=>'tipo','valor'=>$criteriosSeleccion->tipo]);
            
            if(isset($criteriosSeleccion->usuarioId) && $criteriosSeleccion->usuarioId!="")
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'C', 'campo'=>'usuario_id','valor'=>$criteriosSeleccion->usuarioId]);
                    
            $where = $this->where($filtros);
        }
        $consulta = $this->consultaBase . $where . " ORDER BY C.id LIMIT $limit";
        
        //var_dump($criteriosSeleccion);
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $usuarioId, $asunto, $mensaje, $estatus, $fechaEnvio, $fechaAlta, $tipo, $usuarioNombre, $usuarioApellido, $nombreUsuario, $tipoUsuarioId, $cabecera, $recursosHumanos, $empresaId, $fechaModificacion))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioId, $asunto, $mensaje, $estatus, $fechaEnvio, $fechaAlta, $tipo,$usuarioNombre, $usuarioApellido, $nombreUsuario, $tipoUsuarioId, $cabecera, $recursosHumanos, $empresaId, $fechaModificacion);
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
                    if($sentencia->bind_result($id, $usuarioId, $asunto, $mensaje, $estatus, $fechaEnvio, $fechaAlta))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioId, $asunto, $mensaje, $estatus, $fechaEnvio, $fechaAlta);
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
        $consulta = "DELETE FROM correos WHERE id = ?";
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

    public function eliminarTodo($tipo)
    {
        $resultado = new Resultado();
        $consulta = " DELETE FROM correos "
        . "  WHERE tipo  = ? ";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("s",$tipo))
            {
                if($sentencia->execute())
                {
                    
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
    
    
    private function crearRegistro($id, $usuarioId, $asunto, $mensaje, $estatus, $fechaEnvio, $fechaAlta, $tipo, $usuarioNombre, $usuarioApellido, $nombreUsuario, $tipoUsuarioId, $cabecera, $recursosHumanos, $empresaId, $fechaModificacion)
    {
        $registro= (object) 
        [
            'id' => $id,
            'usuarioId' => $usuarioId,
            'asunto' => $asunto,
            'mensaje' => $mensaje,
            'estatus' => $estatus,
            'fechaEnvio' => $fechaEnvio,
            'fechaAlta' => $fechaAlta,
            'tipo' => $tipo,
            'nombre' => $usuarioNombre,
            'apellido' => $usuarioApellido,
            'nombreUsuario' =>$nombreUsuario,
            'tipoUsuarioId' => $tipoUsuarioId,
            'cabecera' => $cabecera,
            'recursosHumanos' => $recursosHumanos,
            'empresaId' => $empresaId,
            'fechaModificacion' => $fechaModificacion
        ];
        return $registro;
    }
    
    public function reiniciarColgados()
    {
        $resultado = new Resultado();
        $estatus = \EstatusCorreo::CREADO;
        $consulta = "UPDATE correos
                SET estatus = ?, fecha_modificacion = NOW()
                WHERE estatus = 2 AND fecha_inicio_procesamiento IS NOT NULL
                  AND TIMESTAMPDIFF(MINUTE, fecha_inicio_procesamiento, NOW()) >= 10";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('i',$estatus))
            {
                if($sentencia->execute())
                {
                    //$resultado->valor = $llaves->id;
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
}
