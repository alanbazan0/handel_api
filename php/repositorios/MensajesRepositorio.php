<?php
namespace php\repositorios;

use php\interfaces\IMensajesRepositorio;
use php\modelos\Mensaje;
use php\modelos\Resultado;
use php\clases\AdministradorCorreo;

include '../interfaces/IMensajesRepositorio.php';
include '../modelos/Mensaje.php';
require_once('RepositorioBase.php');
require_once('../clases/Resultado.php');

class MensajesRepositorio extends RepositorioBase implements IMensajesRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = "SELECT M.id, mensaje, IFNULL(DATE_FORMAT(fecha,'%m/%d/%Y %H:%i:%s'),'') as fecha, usuario_id, U.nombre as usuarioNombre, U.apellido,
                                EXISTS(SELECT mensaje_id FROM mensajes_leidos ML WHERE ML.mensaje_id = M.id AND usuario_id = ?) leido, IFNULL(asunto,'') asunto
                            FROM mensajes M
                                INNER JOIN usuarios U ON U.id = M.usuario_id ";
    }

    public function insertar(Mensaje $modelo,$usuario)
    {
        $this->conexion->autocommit(FALSE);
        $resultado = $this->calcularId('id','mensajes');
        if($resultado->mensajeError=='')
        {
            $id = $resultado->valor;
            
            if($modelo->empresaId=="")
                $modelo->empresaId=null;
            if($modelo->sedeId=="")
                $modelo->sedeId=null;
            if($modelo->departamentoId=="")
                $modelo->departamentoId=null;
            if($modelo->usuarioId=="")
                $modelo->usuarioId=null;
            
            $consulta = "INSERT INTO mensajes(id, mensaje, usuario_id,fecha,asunto, compartir_empresa_id, compartir_sede_id, compartir_departamento_id, compartir_usuario_id)VALUES(?, ?, ?,NOW(),?,?,?,?,?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('isisiiii', $id, $modelo->mensaje,$usuario->id,$modelo->asunto,$modelo->empresaId,$modelo->sedeId,$modelo->departamentoId,$modelo->usuarioId))
                {
                    if($sentencia->execute())
                    {
                        if($resultado->correcto())
                        {
                            $modelo->id = $resultado->valor;
                            $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
                            $criteriosSeleccion = (object) ['permisoSAHA' => 1];
                            if($modelo->empresaId!=null && $modelo->empresaId!="")
                                $criteriosSeleccion->empresaId = $modelo->empresaId;
                            if($modelo->sedeId!=null && $modelo->sedeId!="")
                                $criteriosSeleccion->sedeId = $modelo->sedeId;
                            if($modelo->departamentoId!=null && $modelo->departamentoId!="")
                                $criteriosSeleccion->departamentoId = $modelo->departamentoId;
                            if($modelo->usuarioId!=null && $modelo->usuarioId!="")
                                $criteriosSeleccion->usuarioId = $modelo->usuarioId;
                            //$resultado = $usuariosRepositorio->consultar($modelo,$criteriosSeleccion,false);
                            $resultado = $usuariosRepositorio->consultarPorPermiso($usuario,$criteriosSeleccion,false);
                            if($resultado->correcto())
                            {
                                $usuarios = $resultado->valor;
                                
                                $info = "";// var_export($criteriosSeleccion, true);
                                
                                $administrador_correo = new AdministradorCorreo();
                                $titulo = "El usuario $usuario->nombreCompleto ha creado un nuevo tema de discusión en SAHA, el nuevo tema es: <label style='font-weight:bold'> $modelo->asunto</label>";
                                $url = "https://saha.apps-handel.com/mensajes.php?mensajeId=$modelo->id";
                                $asuntoCorreo = utf8_decode("SAHA: " . $usuario->nombreCompleto . ": " . $modelo->asunto);
                                $tipo = "mensaje" .$modelo->id;
                                $resultado = $administrador_correo->enviarNotificacionMensaje($tipo,$usuario,$usuarios,$asuntoCorreo,$titulo,$modelo->mensaje,$url,$info);
                            }
                        }
                        
                        $resultado->valor = $id;
                    }
                    else
                        $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
                }
                else
                    $resultado->mensajeError = 'Falló el enlace de parámetros';
            }
            else
                $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' .$this->conexion->error;
        }
        if($resultado->correcto())
            $this->conexion->commit(); 
        else    
            $this->conexion->rollback();
       
        return $resultado;
    }

    public function actualizar(Mensaje $modelo)
    {
        $resultado = new Resultado();
        $consulta = "UPDATE mensajes
                     SET 
                         mensaje = ?,
                         fecha = ?,
                         asunto = ?
                     WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('sssi',$modelo->mensaje, $modelo->fecha ,$modelo->asunto,$modelo->id ))
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
    
    public function marcarComoLeido($mensajeId,$usuario)
    {
        $resultado = $this->existeMensajeLeido($mensajeId,$usuario->id);
        if($resultado->mensajeError=='')
        {
            if(!$resultado->valor)
            {
                $consulta = "INSERT INTO mensajes_leidos(mensaje_id,usuario_id,fecha )VALUES(?, ?,NOW())";
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if($sentencia->bind_param('ii', $mensajeId,$usuario->id))
                    {
                        if(!$sentencia->execute())
                            $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
                        else 
                            $resultado->valor = $mensajeId;
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace de parámetros';
                }
                else
                    $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' .$this->conexion->error;
            }
        }
        return $resultado;
    }
    
    public function mensajeLeido($mensajeId,Mensaje $modelo)
    {
        $resultado = new Resultado();
        $consulta = "UPDATE mensajes
                     SET
                         mensaje = ?,
                         fecha = ?
                     WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('ssi',$modelo->mensaje, $modelo->fecha ,$modelo->id ))
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
    
    public function consultarNumeroMensajesNoLeidos($usuario)
    {
        $resultado = new Resultado();
        
        
        $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
        $resultado = $usuariosRepositorio->consultarIdsEmpresas($usuario->empresaId);
        if($resultado->correcto())
        {
            $empresasIds = implode(",", $resultado->valor);
            
            $consulta = "SELECT COUNT(*)
                    FROM mensajes M
                        INNER JOIN usuarios U ON U.id = M.usuario_id 
                    WHERE M.id NOT IN(SELECT mensaje_id FROM mensajes_leidos ML WHERE ML.usuario_id = ?)
                            AND ((((U.tipo_usuario_id = 1 AND (compartir_empresa_id is null OR compartir_empresa_id = ?)) or (U.tipo_usuario_id != 1 AND U.empresa_id IN ($empresasIds)))
             	            AND (compartir_sede_id is null OR compartir_sede_id = ?)
                            AND (compartir_departamento_id is null OR compartir_departamento_id = ?)
                            AND (compartir_usuario_id is null OR compartir_usuario_id = ? )) or usuario_id=?)  ";
            
           
            
            
            $resultado->valor = 0;
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param("iiiiii", $usuario->id,$usuario->empresaId,$usuario->sedeId,$usuario->departamentoId,$usuario->id,$usuario->id))
                {
                    if($sentencia->execute())
                    {
                        if($sentencia->bind_result($numeroMensajes))
                        {
                            if($sentencia->fetch())
                            {
                                $resultado->valor = $numeroMensajes;
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
        }
        return $resultado;
    }
    

    public function consultar($criteriosSeleccion,$usuario)
    {
        $resultado = new Resultado();
        $registros = array();
//         $where= "WHERE (compartir_empresa_id is null OR compartir_empresa_id = ?)
//             	AND (compartir_sede_id is null OR compartir_sede_id = ?)
//                 AND (compartir_departamento_id is null OR compartir_departamento_id = ?)
//                 AND (compartir_usuario_id is null OR compartir_usuario_id = ? OR usuario_id= ?)";

        $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
        $resultado = $usuariosRepositorio->consultarIdsEmpresas($usuario->empresaId);
        if($resultado->correcto())
        {
            $empresasIds = implode(",", $resultado->valor);
            
            $where= "WHERE ( ((tipo_usuario_id = 1 AND (compartir_empresa_id is null OR compartir_empresa_id = ?)) or (tipo_usuario_id != 1 AND U.empresa_id IN ($empresasIds)))
             	AND (compartir_sede_id is null OR compartir_sede_id = ?)
                 AND (compartir_departamento_id is null OR compartir_departamento_id = ?)
                 AND (compartir_usuario_id is null OR compartir_usuario_id = ?)) OR usuario_id = ?";
            
            
            $consulta = $this->consultaBase . $where . ' ORDER BY UNIX_TIMESTAMP(fecha) desc';
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('iiiiii',$usuario->id,$usuario->empresaId,$usuario->sedeId,$usuario->departamentoId,$usuario->id,$usuario->id))
                {
                    if($sentencia->execute())
                    {
                        if($sentencia->bind_result($id, $mensaje, $fecha,$usuarioId, $usuarioNombre, $usuarioApellido,$leido,$asunto))
                        {
                            while($row = $sentencia->fetch())
                            {
                                $registro = $this->crearRegistro($id, $mensaje, $fecha,$usuarioId, $usuarioNombre, $usuarioApellido,$leido,$asunto);
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
            
        }

        
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
                    if($sentencia->bind_result($id, $mensaje, $fecha,$usuarioId, $usuarioNombre, $usuarioApellido,$leido,$asunto))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $mensaje, $fecha,$usuarioId, $usuarioNombre, $usuarioApellido,$leido,$asunto);
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
        $consulta = "DELETE FROM mensajes WHERE id = ?";
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

    private function crearRegistro($id, $mensaje, $fecha, $usuarioId, $usuarioNombre, $usuarioApellido,$leido,$asunto)
    {
        $registro= (object) 
        [
            'id' => $id,
            'mensaje' => $mensaje,
            'fecha' => $fecha,
            'usuarioId' => $usuarioId,
            'usuarioNombre' => $usuarioNombre,
            'usuarioApellido' => $usuarioApellido,
            'leido' => $leido,
            'asunto' => $asunto
        ];
        
        $registro->usuarioNombreCompleto = $registro->usuarioNombre . " " . $registro->usuarioApellido;
        $registro->fotoPerfil =  "../fotos/usuario". $registro->usuarioId .".jpg";
        if(file_exists($registro->fotoPerfil))
            $registro->fotoPerfil =  "php/fotos/usuario". $registro->usuarioId .".jpg";
        else
            $registro->fotoPerfil =  "php/fotos/default.jpg";
        
        return $registro;
    }
    
    public function existeMensajeLeido($mensajeId, $usuarioId)
    {
        $resultado = new Resultado();
        $resultado->valor =false;
        $consulta =  "SELECT COUNT(*) FROM mensajes_leidos WHERE mensaje_id = ? AND usuario_id = ?";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('ii', $mensajeId,$usuario->id))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($count))
                    {
                        if($sentencia->fetch())
                        {
                            if($count>0)
                              $resultado->valor =true;
                        }
                        else
                            $resultado->mensajeError = "No se encontró ningún resultado";
                    }
                    else
                        $resultado->mensajeError = "Falló el enlace del resultado";
                }
                else
                    $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            return $resultado;
    }
    
}
