<?php
namespace php\repositorios;

use php\interfaces\IEvidenciasComentariosRepositorio;
use php\modelos\EvidenciaComentario;
use php\modelos\Resultado;
use php\clases\AdministradorCorreo;

require_once('../interfaces/IEvidenciasComentariosRepositorio.php');
require_once('../modelos/EvidenciaComentario.php');
require_once('RepositorioBase.php');
require_once('../repositorios/EvidenciasRepositorio.php');
require_once('../clases/Resultado.php');
require_once('../clases/AdministradorCorreo.php');

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
    
    public function consultaUsuariosComentario($evidenciaId)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $consulta = "SELECT U.id, U.nombre, U.apellido, U.nombre_usuario 
            FROM evidencias_comentarios EC 
                INNER JOIN usuarios U ON U.id = EC.usuario_id 
        WHERE evidencia_id = ?
        GROUP BY   U.id, U.nombre, U.apellido, U.nombre_usuario ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$evidenciaId))
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

    public function insertar($usuario,EvidenciaComentario $modelo)
    {
        $resultado = new Resultado();
        if(isset($modelo->comentario) && $modelo->comentario!="" )
        {
            $resultado = $this->calcularId('id','evidencias_comentarios');
            if($resultado->correcto())
            {
                $modelo->id = $resultado->valor;
                $consulta = "INSERT INTO evidencias_comentarios(id, evidencia_id, usuario_id, comentario, fecha)VALUES(?, ?, ?, ?, NOW())";
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if($sentencia->bind_param('iiis',  $modelo->id, $modelo->evidenciaId, $modelo->usuarioId, $modelo->comentario))
                    {
                        if($sentencia->execute())
                        {
                            $llaves= (object)
                            [
                                'id'=> $modelo->evidenciaId
                            ];
                            $repositorio = new EvidenciasRepositorio($this->conexion);
                            $resultado = $repositorio->consultarPorLlaves($llaves);
                            if($resultado->correcto())
                            {
                                $evidencia =  $resultado->valor;
                                $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
                                $resultado = $this->consultaUsuariosComentario($evidencia->id);
                                if($resultado->correcto())
                                {
                                    $usuarios = $resultado->valor;
                                    
                                    if(!$usuariosRepositorio->existeUsuarioArreglo($evidencia->usuarioId,$usuarios))
                                    {
                                        $resultado = $usuariosRepositorio->consultarPorLLaves((object)["id"=>$evidencia->usuarioId]);
                                        if($resultado->correcto())
                                            array_push($usuarios, $resultado->valor);
                                    }
                                    if(!$usuariosRepositorio->existeUsuarioArreglo($evidencia->administradorId,$usuarios))
                                    {
                                        $resultado = $usuariosRepositorio->consultarPorLLaves((object)["id"=>$evidencia->administradorId]);
                                        if($resultado->correcto())
                                            array_push($usuarios, $resultado->valor);
                                    }
                                    
                                    $usuariosRepositorio->eliminarUsuarioArreglo($usuario->id,$usuarios);
                                    
                                    $info =  "";//var_export($evidencia, true);
                                        
                                    $administrador_correo = new AdministradorCorreo();
                                    $titulo = "El usuario $usuario->nombreCompleto ha comentado en la conversación sobre la evidencia: <label style='font-weight:bold'> $evidencia->nombre</label>";
                                    $url = "https://saha.apps-handel.com/panel.php?comentarioId= $modelo->id";
                                    $asunto  = "SAHA: " . $usuario->nombreCompleto . ": hizo un comentario en evidencia " . $evidencia->nombre;
                                    //$asuntoCorreo = utf8_decode($asunto);
                                    $asuntoCorreo = html_entity_decode($asunto);
                                   // $asuntoCorreo = "=?ISO-8859-1?B?".base64_encode($asunto)."=?=";
                                    $tipo = "comentario" . $modelo->id;
                                    $resultado = $administrador_correo->enviarNotificacionMensaje($tipo,$usuario,$usuarios,$asuntoCorreo,$titulo,$modelo->comentario,$url, $info);
                                    if($resultado->correcto())
                                    {
                                        $resultado->valor = $modelo->id;
                                    }
                                }
                            }
                            
                        }
                        else
                            $resultado->mensajeError =  __FUNCTION__ . ' Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__ . ' Falló el enlace de parámetros';
                }
                else
                    $resultado->mensajeError = __FUNCTION__ . ' Falló la preparación: (' . $this->conexion->errno . ') ' .$this->conexion->error;
            }
        }
//         else 
//             $resultado->mensajeError = __FUNCTION__ . "El comnentario esta vacío";
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
