<?php
namespace php\repositorios;

use php\interfaces\IObservacionesComentariosRepositorio;
use php\modelos\ObservacionComentario;
use php\modelos\Resultado;
use php\clases\AdministradorCorreo;

include '../interfaces/IObservacionesComentariosRepositorio.php';
include '../modelos/ObservacionComentario.php';
require_once('RepositorioBase.php');
require_once('ProcesosRevisadosRepositorio.php');
require_once('MinutasRepositorio.php');
require_once('../clases/Resultado.php');

class ObservacionesComentariosRepositorio extends RepositorioBase implements IObservacionesComentariosRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = "SELECT MTC.id, proceso_revisado_id, observacion_id, usuario_id, comentario, IFNULL(DATE_FORMAT(fecha,'%m/%d/%Y %H:%i:%s'),'')  as fecha , U.nombre, U.apellido
                            FROM procesos_revisados_observaciones_comentarios MTC
                                INNER JOIN usuarios U ON U.id = MTC.usuario_id ";
    }

    public function insertar($usuario,ObservacionComentario $modelo)
    {
        $this->conexion->autocommit(FALSE);
        $resultado = $this->calcularId('id','procesos_revisados_observaciones_comentarios');
        if($resultado->mensajeError=='')
        {
            $id = $resultado->valor;
            $consulta = "INSERT INTO procesos_revisados_observaciones_comentarios(id, proceso_revisado_id, observacion_id, usuario_id, comentario, fecha)VALUES(?, ?, ?, ?, ?, NOW())";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('iiiis', $id, $modelo->procesoRevisadoId, $modelo->observacionId, $modelo->usuarioId, $modelo->comentario))
                {
                    if($sentencia->execute())
                    {
                        $llaves= (object)
                        [
                            'id'=> $modelo->procesoRevisadoId,
                        ];
                        $sentencia->close();
                        $repositorio = new ProcesosRevisadosRepositorio($this->conexion);
                        $resultado = $repositorio->consultarPorLlaves($llaves);
                        if($resultado->correcto())
                        {
                            $procesoRevisado =  $resultado->valor;
                            //$resultado = $repositorio->consultar
                            $resultado = $this->enviarNotificacionComentario($procesoRevisado,$usuario, $modelo);
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
    
    
    public function enviarNotificacionComentario($procesoRevisado,$usuario, $modelo)
    {
        $resultado = new Resultado();
        $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
        $resultado = $this->consultaUsuariosComentario($procesoRevisado->id, $modelo->observacionId);
        
        
        if($resultado->correcto())
        {
            $usuarios = $resultado->valor;
            
            if($procesoRevisado->usuarioId!=null)
            {
                if(!$usuariosRepositorio->existeUsuarioArreglo($procesoRevisado->usuarioId,$usuarios))
                {
                    $resultado = $usuariosRepositorio->consultarPorLLaves((object)["id"=>$procesoRevisado->usuarioId]);
                    if($resultado->correcto())
                        array_push($usuarios, $resultado->valor);
                }
            }
            if($procesoRevisado->administradorId!=null)
            {
                if(!$usuariosRepositorio->existeUsuarioArreglo($procesoRevisado->administradorId,$usuarios))
                {
                    $resultado = $usuariosRepositorio->consultarPorLLaves((object)["id"=>$procesoRevisado->administradorId]);
                    if($resultado->correcto())
                        array_push($usuarios, $resultado->valor);
                }
            }
            
            $usuariosRepositorio->eliminarUsuarioArreglo($usuario->id,$usuarios);
            
            //TEST
            //$usuarios = array();
            //array_push($usuarios, (object)["nombreUsuario"=>"alanbazan@apps-handel.com","nombre"=>"Alan"]);
            
            
            $contenido = "<p style='font-size: 14px; line-height: 140%;'>¡Tienes un nuevo mensaje en SAHA!,
                        Es en referencia al proceso de revisión de procedimientos, en particular a tus observaciones o dudas del procedimiento:</p>
                        <p style='font-size: 14px; line-height: 140%;'>&nbsp;</p>
                        <p style='font-size: 14px; line-height: 140%;'><strong>$procesoRevisado->nombre</strong></p>
                        <p style='font-size: 14px; line-height: 140%;'>&nbsp;</p>
                        <p style='font-size: 14px; line-height: 140%;'>El mensaje redactado por <strong>$usuario->nombreCompleto</strong> es:</p>
                        <p style='font-size: 14px; line-height: 140%;'>&nbsp;</p>
                        <p style='font-size: 14px; line-height: 140%;'>$modelo->comentario</p>
                        <p style='font-size: 14px; line-height: 140%;'>&nbsp;</p>
                        <p style='font-size: 14px; line-height: 140%;'>Si consideras que esta respuesta es satisfactoria, este es el final de la conversación sobre este proceso. Si necesitas información adicional puedes responder desde el botón que aparece un poco más abajo.</p>";
            
            $url = "https://saha.apps-handel.com/revision.php?id=$modelo->procesoRevisadoId"."_".$modelo->observacionId;
            
            $boton = "<a href='$url' target='_blank' style='box-sizing: border-box;display: inline-block;font-family:arial,helvetica,sans-serif;text-decoration: none;-webkit-text-size-adjust: none;text-align: center;color: #ffffff; background-color: #0396a6; border-radius: 4px;-webkit-border-radius: 4px; -moz-border-radius: 4px; width:auto; max-width:100%; overflow-wrap: break-word; word-break: break-word; word-wrap:break-word; mso-border-alt: none;'>
            <span style='display:block;padding:10px 20px;line-height:120%;'><strong>Responder</strong></span>
            </a>";
            
            //$nombreUsuario = $usuario->nombreCompleto;
            $asunto  = $usuario->nombreCompleto . " hizo un comentario en el proceso: " . $procesoRevisado->nombre;
            $tipo = "procesoRevisado" .$modelo->procesoRevisadoId ."observacion" . $modelo->observacionId;
            
            $administradorCorreo = new  AdministradorCorreo();
            $resultado = $administradorCorreo->enviarNotificacionRevision($tipo,$usuario,$usuarios,$procesoRevisado,$contenido,$boton, $asunto);
            if($resultado->correcto())
            {
                $resultado->valor = $modelo->observacionId;
            }
        }
        return $resultado;
    }
    
    
    
    public function actualizar(ObservacionComentario $modelo)
    {
        $resultado = new Resultado();
        $consulta = "UPDATE procesos_revisados_observaciones_comentarios
                     SET 
                         proceso_revisado_id = ?,
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
            if(isset($criteriosSeleccion->procesoRevisadoId))
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'MTC', 'campo'=>'proceso_revisado_id','valor'=>$criteriosSeleccion->procesoRevisadoId]);
            if(isset($criteriosSeleccion->observacionId))
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'MTC', 'campo'=>'observacion_id','valor'=>$criteriosSeleccion->observacionId]);
            $where = $this->where($filtros);
        }
        $consulta = $this->consultaBase . $where;
        
        //var_dump($consulta);
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $procesoRevisadoId, $observacionId, $usuarioId, $comentario, $fecha,$usuarionNombre, $usuarioApellido))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $procesoRevisadoId, $observacionId, $usuarioId, $comentario, $fecha,$usuarionNombre, $usuarioApellido);
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
        $consulta = "DELETE FROM procesos_revisados_observaciones_comentarios WHERE id = ?";
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

    private function crearRegistro($id, $procesoRevisadoId, $observacionId, $usuarioId, $comentario, $fecha, $usuarioNombre, $usuarioApellido)
    {
        $registro= (object) 
        [
            'id' => $id,
            'procesoRevisadoId' => $procesoRevisadoId,
            'observacionId' => $observacionId,
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
    
    public function consultaUsuariosComentario($procesoRevisadoId, $observacionId)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $consulta = "SELECT U.id, U.nombre, U.apellido, U.nombre_usuario
            FROM procesos_revisados_observaciones_comentarios EC
                INNER JOIN usuarios U ON U.id = EC.usuario_id
        WHERE proceso_revisado_id = ? AND observacion_id = ?
        GROUP BY   U.id, U.nombre, U.apellido, U.nombre_usuario ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ii",$procesoRevisadoId,$observacionId))
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
