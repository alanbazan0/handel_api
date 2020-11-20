<?php
namespace php\repositorios;

use php\interfaces\ITareasComentariosRepositorio;
use php\modelos\TareaComentario;
use php\modelos\Resultado;
use php\clases\AdministradorCorreo;

include '../interfaces/ITareasComentariosRepositorio.php';
include '../modelos/TareaComentario.php';
require_once('RepositorioBase.php');
require_once('MinutasRepositorio.php');
require_once('../clases/Resultado.php');

class TareasComentariosRepositorio extends RepositorioBase implements ITareasComentariosRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = "SELECT MTC.id, minuta_id, tarea_id, usuario_id, comentario, IFNULL(DATE_FORMAT(fecha,'%m/%d/%Y %H:%i:%s'),'')  as fecha , U.nombre, U.apellido
                            FROM minutas_tareas_comentarios MTC
                                INNER JOIN usuarios U ON U.id = MTC.usuario_id ";
    }

    public function insertar($usuario,TareaComentario $modelo)
    {
        $this->conexion->autocommit(FALSE);
        $resultado = $this->calcularId('id','minutas_tareas_comentarios');
        if($resultado->mensajeError=='')
        {
            $id = $resultado->valor;
            $consulta = "INSERT INTO minutas_tareas_comentarios(id, minuta_id, tarea_id, usuario_id, comentario, fecha)VALUES(?, ?, ?, ?, ?, NOW())";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('iiiis', $id, $modelo->minutaId, $modelo->tareaId, $modelo->usuarioId, $modelo->comentario))
                {
                    if($sentencia->execute())
                    {
                        $llaves= (object)
                        [
                            'minutaId'=> $modelo->minutaId,
                            'tareaId'=> $modelo->tareaId
                        ];
                        $sentencia->close();
                        $repositorio = new MinutasRepositorio($this->conexion);
                        $resultado = $repositorio->consultarTareaPorLlaves($llaves);
                        if($resultado->correcto())
                        {
                            $tarea =  $resultado->valor;
                            $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
                            $resultado = $this->consultaUsuariosComentario($tarea->minutaId,$tarea->id);
                            if($resultado->correcto())
                            {
                                $usuarios = $resultado->valor;
                                
                                if(!$usuariosRepositorio->existeUsuarioArreglo($tarea->usuarioId,$usuarios))
                                {
                                    $resultado = $usuariosRepositorio->consultarPorLLaves((object)["id"=>$tarea->usuarioId]);
                                    if($resultado->correcto())
                                        array_push($usuarios, $resultado->valor);
                                }
                                
                                if($usuario->supervisor1Id!=null)
                                {
                                    if(!$usuariosRepositorio->existeUsuarioArreglo($usuario->supervisor1Id,$usuarios))
                                    {
                                        $resultado = $usuariosRepositorio->consultarPorLLaves((object)["id"=>$usuario->supervisor1Id]);
                                        if($resultado->correcto())
                                            array_push($usuarios, $resultado->valor);
                                    }
                                }
                                
                                
                                $usuariosRepositorio->eliminarUsuarioArreglo($usuario->id,$usuarios);
                                
                                $resultado = $this->enviarNotificacionComentario($usuario,$usuarios,$tarea, $modelo->comentario);
                                if($resultado->correcto())
                                { 
                                    $resultado->valor = $modelo->id;
                                }
                            }
                        }
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
    
    public function enviarNotificacionComentario($usuario,$usuarios, $tarea, $comentario)
    {
        $resultado = new Resultado();
        
        $frasesRepositorio = new FrasesRepositorio($this->conexion);
        $resultado = $frasesRepositorio->consultarAleatorio();
        if($resultado->correcto())
        {
            
            $frase = $resultado->valor;
            
            $nombreUsuario = $usuario->nombreCompleto;
            $fotoPerfil = "https://api.apps-handel.com/" . $usuario->fotoPerfil;
            $asunto  = $usuario->nombreCompleto . " hizo un comentario en tarea: " . $tarea->titulo;
            $accion = " ha comentado en la conversación sobre la tarea: ";
            
            $asunto="=?UTF-8?B?".base64_encode($asunto)."?=";
            
            $tipo = "minuta" .$tarea->minutaId ."tarea" . $tarea->id;
            $info = "";
            $mensaje= file_get_contents('../plantillas_correo/notificacion_comentario_tarea.html');
            
            
            $mensaje=  str_replace("@nombreUsuario",$nombreUsuario,$mensaje);
            $mensaje=  str_replace("@accion",$accion,$mensaje);
            $mensaje=  str_replace("@fotoPerfil",$fotoPerfil,$mensaje);
            $mensaje=  str_replace("@nombreMinuta",$tarea->minutaTitulo,$mensaje);
            $mensaje=  str_replace("@nombreTarea",$tarea->titulo,$mensaje);
            $mensaje=  str_replace("@fechaVencimiento",$tarea->fechaCompromiso,$mensaje);
            
            $mensaje=  str_replace("@minutaId",$tarea->minutaId,$mensaje);
            $mensaje=  str_replace("@tareaId",$tarea->id,$mensaje);
            $mensaje=  str_replace("@texto",$comentario,$mensaje);
            
            
            $mensaje=  str_replace("@frase",$frase->texto,$mensaje);
            $mensaje=  str_replace("@autor",$frase->autor,$mensaje);
            
            
            
            $administrador_correo = new AdministradorCorreo();
            $resultado = $administrador_correo->enviarCorreoUsuarios($tipo,$usuarios,$asunto, $mensaje, $info, "SAHA: Tareas");
            //}
        }
        return $resultado;
    }
    

    public function actualizar(TareaComentario $modelo)
    {
        $resultado = new Resultado();
        $consulta = "UPDATE minutas_tareas_comentarios
                     SET 
                         minuta_id = ?,
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
            if(isset($criteriosSeleccion->minutaId))
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'MTC', 'campo'=>'minuta_id','valor'=>$criteriosSeleccion->minutaId]);
            if(isset($criteriosSeleccion->tareaId))
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'MTC', 'campo'=>'tarea_id','valor'=>$criteriosSeleccion->tareaId]);
            $where = $this->where($filtros);
        }
        $consulta = $this->consultaBase . $where;
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $minutaId, $tareaId, $usuarioId, $comentario, $fecha,$usuarionNombre, $usuarioApellido))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $minutaId, $tareaId, $usuarioId, $comentario, $fecha,$usuarionNombre, $usuarioApellido);
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
        $consulta = "DELETE FROM minutas_tareas_comentarios WHERE id = ?";
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

    private function crearRegistro($id, $minutaId, $tareaId, $usuarioId, $comentario, $fecha, $usuarioNombre, $usuarioApellido)
    {
        $registro= (object) 
        [
            'id' => $id,
            'minutaId' => $minutaId,
            'tareaId' => $tareaId,
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
    
    public function consultaUsuariosComentario($minutaId, $tareaId)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $consulta = "SELECT U.id, U.nombre, U.apellido, U.nombre_usuario
            FROM minutas_tareas_comentarios EC
                INNER JOIN usuarios U ON U.id = EC.usuario_id
        WHERE minuta_id = ? AND tarea_id = ?
        GROUP BY   U.id, U.nombre, U.apellido, U.nombre_usuario ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ii",$minutaId,$tareaId))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre, $apellido, $nombreUsuario))
                    {
                        while($sentencia->fetch())
                        {
                            $registro= (object)
                            [
                                "id" => $id,
                                "nombre" => $nombre,
                                "apellido" => $apellido,
                                "nombreUsuario" => $nombreUsuario
                            ];
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
    
}
