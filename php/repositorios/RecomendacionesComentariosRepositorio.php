<?php
namespace php\repositorios;

use php\interfaces\IRecomendacionesComentariosRepositorio;
use php\modelos\RecomendacionComentario;
use php\modelos\Resultado;
use php\clases\AdministradorCorreo;

require_once('../interfaces/IRecomendacionesComentariosRepositorio.php');
require_once('../modelos/RecomendacionComentario.php');
require_once('RepositorioBase.php');
require_once('../repositorios/AuditoriasRepositorio.php');
require_once('../clases/Resultado.php');
require_once('../clases/AdministradorCorreo.php');

class RecomendacionesComentariosRepositorio extends RepositorioBase implements IRecomendacionesComentariosRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase =  "SELECT EC.id, usuario_id, U.nombre, U.apellido, comentario, IFNULL(DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s'),'') as fecha
                    FROM recomendaciones_comentarios EC
                    	INNER JOIN usuarios U ON U.id = EC.usuario_id ";
                   
    }
    
    public function consultaUsuariosComentario($recomendacionId)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $consulta = "SELECT U.id, U.nombre, U.apellido, U.nombre_usuario 
            FROM recomendaciones_comentarios EC 
                INNER JOIN usuarios U ON U.id = EC.usuario_id 
        WHERE recomendacion_id = ?
        GROUP BY   U.id, U.nombre, U.apellido, U.nombre_usuario ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$recomendacionId))
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

    public function insertar($usuario,RecomendacionComentario $modelo)
    {
        $resultado = new Resultado();
        if(isset($modelo->comentario) && $modelo->comentario!="" )
        {
            $resultado = $this->calcularId('id','recomendaciones_comentarios');
            if($resultado->correcto())
            {
                $modelo->id = $resultado->valor;
                $consulta = "INSERT INTO recomendaciones_comentarios(id, recomendacion_id, usuario_id, comentario, fecha)VALUES(?, ?, ?, ?, NOW())";
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if($sentencia->bind_param('iiis',  $modelo->id, $modelo->recomendacionId, $modelo->usuarioId, $modelo->comentario))
                    {
                        if($sentencia->execute())
                        {
                            $llaves= (object)
                            [
                                'id'=> $modelo->recomendacionId
                            ];
                            //$repositorio = new EvidenciasRepositorio($this->conexion);
                            $repositorio = new AuditoriasRepositorio($this->conexion);
                            //$resultado = $repositorio->consultarPorLlaves($llaves);
                            $resultado = $repositorio->consultarRecomendacionPorLlaves($llaves);
                            if($resultado->correcto())
                            {
                               
                                $recomendacion =  $resultado->valor;
                                
                                $resultado = $this->enviarNotificacionComentario($recomendacion,$usuario, $modelo);
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
    
   
    
    public function enviarNotificacionComentario($recomendacion,$usuario, $modelo)
    {
        $resultado = new Resultado();
        $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
        $resultado = $this->consultaUsuariosComentario($recomendacion->id);
        if($resultado->correcto())
        {
            $usuarios = $resultado->valor;
            
            if($recomendacion->usuarioId!=null)
            {
                if(!$usuariosRepositorio->existeUsuarioArreglo($recomendacion->usuarioId,$usuarios))
                {
                    $resultado = $usuariosRepositorio->consultarPorLLaves((object)["id"=>$recomendacion->usuarioId]);
                    if($resultado->correcto())
                        array_push($usuarios, $resultado->valor);
                }
            }
            if($recomendacion->administradorId!=null)
            {
                if(!$usuariosRepositorio->existeUsuarioArreglo($recomendacion->administradorId,$usuarios))
                {
                    $resultado = $usuariosRepositorio->consultarPorLLaves((object)["id"=>$recomendacion->administradorId]);
                    if($resultado->correcto())
                        array_push($usuarios, $resultado->valor);
                }
            }
            
            $usuariosRepositorio->eliminarUsuarioArreglo($usuario->id,$usuarios);
            
            $info =  "";
            //TEST
            
            //$usuarios = array();
            //array_push($usuarios, (object)["nombreUsuario"=>"alanbazan@apps-handel.com","nombre"=>"Alan"]);
            
            $administrador_correo = new AdministradorCorreo();
            
            
            //$asunto  = "SIVAH: " . $usuario->nombreCompleto . ": hizo un comentario en acción " . $recomendacion->titulo;
            
            //$asuntoCorreo = html_entity_decode($asunto);
            
            $tipo = "comentario_recomendacion" . $modelo->id;
            
            $url = "https://sivah.apps-handel.com/menu.php?p=seguimiento&comentarioId= $modelo->id";
            
            $contenido = "<tr>
                            <td align='center' class='padding-copy' style='font-size: 25px; font-family: Helvetica, Arial, sans-serif; color: #548dd4; padding-top: 0px;'><strong>¡Hola!</strong><br></td>
                        </tr>
                        <tr>
                            <td align='center' class='padding-copy textlightStyle' style='padding: 20px 0 0 0; font-size: 16px; line-height: 25px; font-family: Helvetica, Arial, sans-serif; color: #3F3D33;'>
                            <p>El usuario $usuario->nombreCompleto; ha comentado en la conversación sobre la acción:</p>
							<p><strong>$recomendacion->titulo</strong></p>
							<p><br>El comentario es:</p>
							<p><strong>$modelo->comentario</strong></p>
							<p>Puedes responder a este mensaje directamente ingresando a SIVAH (<em><a title='Accede a SIVAH' href='https://mosaico.io/sivah.apps-handel.com' style='color: #3F3D33; text-decoration: none; font-weight: bold;'>sivah.apps-handel.com</a></em>) o dando clic en el botón inferior</p>
							</td>
                        </tr>";
            
            
            $resultado = $administrador_correo->enviarNotificacionSIVAH($tipo,$usuarios,"Nuevo mensaje en SIVAH","#3F3D33","¡Recibiste un mensaje!", $contenido);
            if($resultado->correcto())
            {
                $resultado->valor = $modelo->id;
            }
        }
        return $resultado;
    }
    
    public function actualizar(RecomendacionComentario $modelo)
    {
        $resultado = new Resultado();
        $consulta = "UPDATE recomendaciones_comentarios
                     SET
                         recomendacion_id = ?,
                         usuario_id = ?,
                         comentario = ?,
                         fecha = ?
                     WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('iissi',$modelo->recomendacionId, $modelo->usuarioId, $modelo->comentario, $modelo->fecha ,$modelo->id ))
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
                        "WHERE recomendacion_id = ? " .
                        "ORDER By EC.id";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$criteriosSeleccion->recomendacionId))
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
