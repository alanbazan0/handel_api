<?php
namespace php\repositorios;

use php\interfaces\IMinutasRepositorio;
use php\modelos\Minuta;
use php\modelos\Resultado;
use php\clases\Porcentaje;
use php\clases\AdministradorCorreo;

include '../interfaces/IMinutasRepositorio.php';
include '../modelos/Minuta.php';
require_once('RepositorioBase.php');
require_once('../clases/Resultado.php');
require_once('../clases/Porcentaje.php');
require_once('../clases/AdministradorCorreo.php');
require_once('FrasesRepositorio.php');
require_once('UsuariosRepositorio.php');

class MinutasRepositorio extends RepositorioBase implements IMinutasRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = "SELECT M.id, titulo, descripcion, IFNULL(DATE_FORMAT(M.fecha_alta,'%d/%m/%Y %H:%i:%s'),'') as fecha_alta, usuario_id, terminada, IFNULL(DATE_FORMAT(M.fecha_finalizacion,'%d/%m/%Y %H:%i:%s'),'') as fecha_finalizacion, IFNULL(DATE_FORMAT(M.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'') as fecha_modificacion, U.nombre, U.apellido,
                                (SELECT count(*) FROM minutas_tareas T WHERE T.minuta_id = M.id AND T.tipo='t') total,
                               (SELECT count(*) FROM minutas_tareas T WHERE T.minuta_id = M.id AND T.terminada=1) terminadas, acuerdos, participantes, color,
                                U.empresa_id, E.nombre 
                                FROM minutas M
                                    INNER JOIN usuarios U ON U.id = M.usuario_id
                                    INNER JOIN empresas E ON E.id = U.empresa_id";
    }

    public function insertar(Minuta $modelo,$usuario)
    {
        $resultado = $this->calcularId('id','minutas');
        if($resultado->mensajeError=='')
        {
            $id = $resultado->valor;
            
            $color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
            
            $consulta = "INSERT INTO minutas(id, titulo, descripcion, fecha_alta, usuario_id, terminada, fecha_finalizacion, fecha_modificacion, color)
                        VALUES(?, ?, ?, NOW(), ?, 0, ?, NOW(), ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('ississ', $id, $modelo->titulo,$modelo->descripcion, $usuario->id,  $modelo->fechaTermino,$color))
                {
                    if($sentencia->execute())
                        $resultado->valor = $id;
                    else
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

    public function actualizar(Minuta $modelo)
    {
        $resultado = new Resultado();
        $consulta = "UPDATE minutas
                     SET 
                         titulo = ?,
                         descripcion = ?,
                         fecha_alta = ?,
                         usuario_id = ?,
                         terminada = ?,
                         fecha_termino = ?
                     WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('sssiisi',$modelo->titulo,$modelo->descripcion, $modelo->fechaAlta, $modelo->usuarioId, $modelo->terminada, $modelo->fechaTermino ,$modelo->id ))
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

    public function consultar($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        $filtros = array();
        $where='';
        if($criteriosSeleccion!=null)
        {
            if(isset($criteriosSeleccion->titulo) && $criteriosSeleccion->titulo!="")
                array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'M','campo'=>'titulo','valor'=>$criteriosSeleccion->titulo]);
            if(isset($criteriosSeleccion->terminada) && $criteriosSeleccion->terminada!="")
                array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'M','campo'=>'terminada','valor'=>$criteriosSeleccion->terminada]);
           
        }
        
        if($usuario!=null)
        {
            $filtro = "(M.usuario_id = $usuario->id OR $usuario->id IN (SELECT usuario_id FROM minutas_usuarios MU WHERE MU.minuta_id = M.id))";
            array_push($filtros,(object)['tipo'=>'estatico','texto'=> $filtro]);
        }
        
        $where = $this->where($filtros);
        
        
        $consulta = $this->consultaBase . $where . " ORDER BY UNIX_TIMESTAMP(M.fecha_alta) desc";
        
        
        
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $descripcion, $titulo, $fechaAlta, $usuarioId, $terminada, $fechaTermino, $fechaModificacion,$usuarioNombre, $usuarioApellido, $total, $terminadas, $acuerdos, $participantes, $color, $empresaId, $empresaNombre))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id,$descripcion, $titulo,$fechaAlta, $usuarioId, $terminada, $fechaTermino,$fechaModificacion,$usuarioNombre, $usuarioApellido, $total, $terminadas, $acuerdos, $participantes, $color, $empresaId, $empresaNombre);
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

    public function consultarPorLlaves($llaves,$consultarDetalle)
    {
        $resultado = new Resultado();
        $consulta = $this->consultaBase .
        ' WHERE M.id  = ?';
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('i',$llaves->id))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $descripcion,$titulo,$fechaAlta, $usuarioId, $terminada, $fechaTermino,$fechaModificacion,$usuarioNombre, $usuarioApellido, $total, $terminadas,$acuerdos, $participantes, $color, $empresaId, $empresaNombre))
                    {
                        if($sentencia->fetch())
                        {
                            $minuta = $this->crearRegistro($id,$descripcion,$titulo, $fechaAlta, $usuarioId, $terminada, $fechaTermino, $fechaModificacion,$usuarioNombre, $usuarioApellido, $total, $terminadas,$acuerdos, $participantes, $color, $empresaId, $empresaNombre);
                            $resultado->valor = $minuta;
                            
                            $sentencia->close();
                            
                            if($consultarDetalle)
                            {
                                $resultadoTareas = $this->consultarTareas($minuta->id);
                                if($resultadoTareas->correcto())
                                {
                                    $minuta->tareas = $resultadoTareas->valor;
                                    $resultadoUsuarios = $this->consultarUsuarios($minuta->id);
                                    if($resultadoUsuarios->correcto())
                                    {
                                        $minuta->usuarios = $resultadoUsuarios->valor;
                                    }
                                    else
                                        $resultado->mensajeError = $resultadoUsuarios->mensajeError;
                                }
                                else
                                    $resultado->mensajeError = $resultadoTareas->mensajeError;
                            }
                        }
                        else
                            $resultado->mensajeError = __FUNCTION__.'. No se encontró ningún resultado.';
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__.'. Falló el enlace del resultado';
                }
                else
                    $resultado->mensajeError = __FUNCTION__.'. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__.'. Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = __FUNCTION__.'. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        return $resultado;
    }
    
    public function consultarTareaPorLlaves($llaves)
    {
        $resultado = new Resultado();
        $consulta = "SELECT M.id, M.titulo, T.id, RTRIM(T.titulo) titulo, IFNULL(DATE_FORMAT(T.fecha_alta,'%d/%m/%Y %H:%i:%s'),'') as fecha_alta, IFNULL(DATE_FORMAT(T.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'') as fecha_modificacion,
            IFNULL(DATE_FORMAT(T.fecha_compromiso,'%d/%m/%Y'),'') as fecha_compromiso,
            IFNULL(DATE_FORMAT(T.fecha_finalizacion,'%d/%m/%Y %H:%i:%s'),'') as fecha_finalizacion, T.terminada, T.usuario_id, U.nombre, U.apellido
            FROM minutas_tareas T
                INNER JOIN usuarios U ON U.id = T.usuario_id
                INNER JOIN minutas M ON M.id = T.minuta_id
             WHERE minuta_id  = ?  AND T.id = ?
            ORDER BY orden";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('ii',$llaves->minutaId,$llaves->tareaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($minutaId, $minutaTitulo, $id, $titulo, $fechaAlta, $fechaModificacion, $fechaCompromiso, $fechaFinalizacion, $terminada, $usuarioId, $usuarioNombre, $usuarioApellido))
                    {
                        if($sentencia->fetch())
                        {
                            $tarea= (object) [
                                'id' =>  $id,
                                'minutaId' => $minutaId,
                                'minutaTitulo' => $minutaTitulo,
                                'titulo' => $titulo,
                                'fechaAlta' => $fechaAlta,
                                'fechaModificacion' => $fechaModificacion,
                                'fechaCompromiso' => $fechaCompromiso,
                                'fechaFinalizacion' => $fechaFinalizacion,
                                'terminada' => $terminada,
                                'usuarioId' => $usuarioId,
                                'usuarioNombre' => $usuarioNombre,
                                'usuarioApellido' => $usuarioApellido
                            ];
                            
                            $tarea->usuarioNombreCompleto = $tarea->usuarioNombre . " " . $tarea->usuarioApellido;
                            $tarea->fotoPerfil =  "../fotos/usuario". $tarea->usuarioId .".jpg";
                            if(file_exists($tarea->fotoPerfil))
                                $tarea->fotoPerfil =  "php/fotos/usuario". $tarea->usuarioId .".jpg";
                            else
                                $tarea->fotoPerfil =  "php/fotos/default.jpg";
                           
                           $resultado->valor = $tarea;
                        }
                        else
                            $resultado->mensajeError = __FUNCTION__.'. No se encontró ningún resultado.';
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__.'. Falló el enlace del resultado';
                }
                else
                    $resultado->mensajeError = __FUNCTION__.'. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__.'. Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = __FUNCTION__.'. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }

    public function eliminar($llaves)
    {
        $this->conexion->autocommit(FALSE);
        $resultado = new Resultado();
        $resultado = $this->eliminarResponsablesMinuta($llaves->id);
        if($resultado->correcto())
        {
             $resultado = $this->eliminarTareas($llaves->id);
             if($resultado->correcto())
             {
                $consulta = "DELETE FROM minutas WHERE id = ?";
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
             }
        }
            
        if($resultado->correcto())
        {
            $this->conexion->commit();
            $resultado->valor = $llaves->id;;
        }
        else
            $this->conexion->rollback();
            
        return $resultado;
    }

    private function crearRegistro($id, $titulo,$descripcion,$fechaAlta, $usuarioId, $terminada, $fechaFinalizacion,$fechaModificacion,$usuarioNombre, $usuarioApellido, $total, $terminadas, $acuerdos, $participantes, $color, $empresaId, $empresaNombre)
    {
        $registro= (object) 
        [
            'id' => $id,
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'fechaAlta' => $fechaAlta,
            'usuarioId' => $usuarioId,
            'terminada' => $terminada,
            'fechaFinalizacion' => $fechaFinalizacion,
            'fechaModificacion' => $fechaModificacion,
            'usuarioNombre' => $usuarioNombre,
            'usuarioApellido' => $usuarioApellido,
            'total' => $total,
            'terminadas' => $terminadas,
            'acuerdos' => $acuerdos,
            'participantes' => $participantes,
            'color' => $color,
            'empresaId' => $empresaId,
            'empresaNombre' => $empresaNombre
        ];
        
        if($registro->color=="" || $registro->color==null)
            $registro->color="#000000";
        
        $registro->usuarioNombreCompleto = $registro->usuarioNombre . " " . $registro->usuarioApellido;
        $registro->fotoPerfil =  "../fotos/usuario". $registro->usuarioId .".jpg";
        if(file_exists($registro->fotoPerfil))
            $registro->fotoPerfil =  "php/fotos/usuario". $registro->usuarioId .".jpg";
        else
            $registro->fotoPerfil =  "php/fotos/default.jpg";
        
        Porcentaje::calcularPorcentaje($registro,'terminadas','total',"porcentaje");
            
        
        return $registro;
    }
    
    public function actualizarValor($minutaId, $campo, $valor)
    {
        $resultado = new Resultado();
        
        $consulta = " UPDATE minutas " .
            "SET $campo = ?, " .
            "  fecha_modificacion= NOW() " .
            "WHERE id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("si", $valor, $minutaId))
            {
                if($sentencia->execute())
                {
                    $resultado->valor=true;
                    $sentencia->close();
                }
                else
                    $resultado->mensajeError =__FUNCTION__." Falló la ejecución actualizar(" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__." Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = __FUNCTION__." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
            return $resultado;
    }
    
    private function consultarTareas($minutaId)
    {
        $resultado = new Resultado();
        $tareas = array();
        $consulta = "SELECT T.minuta_id, M.titulo minutaTitulo, RTRIM(M.color), T.id, RTRIM(T.titulo) titulo, IFNULL(DATE_FORMAT(T.fecha_alta,'%d/%m/%Y %H:%i:%s'),'') as fecha_alta, IFNULL(DATE_FORMAT(T.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'') as fecha_modificacion, 
            IFNULL(DATE_FORMAT(T.fecha_compromiso,'%d/%m/%Y'),'') as fecha_compromiso, 
            IFNULL(DATE_FORMAT(T.fecha_finalizacion,'%d/%m/%Y %H:%i:%s'),'') as fecha_finalizacion, T.terminada, T.usuario_id, U.nombre, U.apellido, 
            (SELECT count(*) FROM minutas_tareas_comentarios MTC WHERE MTC.minuta_id = T.minuta_id AND MTC.tarea_id = T.id) numeroComentarios, T.tipo
            FROM minutas_tareas T
                INNER JOIN usuarios U ON U.id = T.usuario_id 
                INNER JOIN minutas M ON M.id = T.minuta_id
             WHERE minuta_id  = ? 
            ORDER BY orden";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$minutaId))
            {
                if($sentencia->execute())
                {
                    //$valores = array();
                    if ($sentencia->bind_result($minutaId,$minutaTitulo,$minutaColor,$id, $titulo, $fechaAlta, $fechaModificacion, $fechaCompromiso, $fechaFinalizacion, $terminada, $usuarioId, $usuarioNombre, $usuarioApellido, $numeroComentarios, $tipo))
                    //if ($sentencia->bind_result($valores))
                    {
                        while($sentencia->fetch())
                        {
                            $tarea= (object) [
                                'id' =>  $id,
                                'minutaId' =>  $minutaId,
                                'minutaTitulo' =>  $minutaTitulo,
                                'minutaColor' =>  $minutaColor,
                                'titulo' => $titulo,
                                'fechaAlta' => $fechaAlta,
                                'fechaModificacion' => $fechaModificacion,
                                'fechaCompromiso' => $fechaCompromiso,
                                'fechaFinalizacion' => $fechaFinalizacion,
                                'terminada' => $terminada,
                                'usuarioId' => $usuarioId,
                                'usuarioNombre' => $usuarioNombre,
                                'usuarioApellido' => $usuarioApellido,
                                'numeroComentarios' => $numeroComentarios,
                                'tipo' => $tipo
                            ];
                            
                            /*$tarea->titulo = preg_replace( "/<br>|\n/", "", $tarea->titulo );
                            $tarea->titulo = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\x9F]/u', '', $tarea->titulo);
                            $tarea->titulo = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $tarea->titulo);
                            $tarea->titulo = preg_replace('/[\x00-\x1F\x7F-\xA0\xAD\X28]/u', '', $tarea->titulo);*/
                          
                            
                            if($tarea->minutaColor=="" || $tarea->minutaColor==null)
                                $tarea->minutaColor="#000000";
                            
                            $tarea->usuarioNombreCompleto = $tarea->usuarioNombre . " " . $tarea->usuarioApellido;
                            $tarea->fotoPerfil =  "../fotos/usuario". $tarea->usuarioId .".jpg";
                            if(file_exists($tarea->fotoPerfil))
                                $tarea->fotoPerfil =  "php/fotos/usuario". $tarea->usuarioId .".jpg";
                            else
                                $tarea->fotoPerfil =  "php/fotos/default.jpg";
                            
                            array_push($tareas,$tarea);
                        }
                        $resultado->valor = $tareas;
                        
                        $sentencia->close();
                        
                        for($i=0; $i < count($tareas);$i++)
                        {
                            $tarea = $tareas[$i];
                            $resultadoCategorias = $this->consultarResponsablesTarea($minutaId,$tarea->id);
                            if($resultadoCategorias->correcto())
                            {
                                $tarea->responsables = $resultadoCategorias->valor;
                            }
                            else
                            {
                                $resultado->mensajeError = $resultadoCategorias->mensajeError;
                                break;
                            }
                        }
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__." Falló el enlace del resultado";
                }
                else
                    $resultado->mensajeError = __FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__." Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = __FUNCTION__." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
            return $resultado;
    }
    
    public function consultarMisTareas($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $tareas = array();
        
        $filtros = array();
        $and='';
        if($criteriosSeleccion!=null)
        {
            //var_dump($criteriosSeleccion);
            if(isset($criteriosSeleccion->terminada) && $criteriosSeleccion->terminada!="")
                array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'T','campo'=>'terminada','valor'=>$criteriosSeleccion->terminada]);
                
        }
        
        
        $and = $this->and($filtros);
        
        $consulta = "SELECT M.id, M.titulo, M.color, T.id, RTRIM(T.titulo) titulo, IFNULL(DATE_FORMAT(T.fecha_alta,'%d/%m/%Y %H:%i:%s'),'') as fecha_alta, IFNULL(DATE_FORMAT(T.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'') as fecha_modificacion,
            IFNULL(DATE_FORMAT(T.fecha_compromiso,'%d/%m/%Y'),'') as fecha_compromiso,
            IFNULL(DATE_FORMAT(T.fecha_finalizacion,'%d/%m/%Y %H:%i:%s'),'') as fecha_finalizacion, T.terminada, T.usuario_id, U.nombre, U.apellido,
            (SELECT count(*) FROM minutas_tareas_comentarios MTC WHERE MTC.minuta_id = T.minuta_id AND MTC.tarea_id = T.id) numeroComentarios
            FROM minutas_tareas T
                INNER JOIN usuarios U ON U.id = T.usuario_id
                INNER JOIN minutas M ON M.id = T.minuta_id
             WHERE T.tipo = 't' AND $usuario->id IN (SELECT usuario_id FROM minutas_tareas_responsables MTR WHERE MTR.minuta_id = M.id AND MTR.tarea_id = T.id) 
            $and
            ORDER BY UNIX_TIMESTAMP(fecha_compromiso)";
        
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            //if($sentencia->bind_param("i",$usuario->id))
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    //$valores = array();
                    if ($sentencia->bind_result($minutaId, $minutaTitulo, $minutaColor, $id, $titulo, $fechaAlta, $fechaModificacion, $fechaCompromiso, $fechaFinalizacion, $terminada, $usuarioId, $usuarioNombre, $usuarioApellido, $numeroComentarios))
                    //if ($sentencia->bind_result($valores))
                    {
                        while($sentencia->fetch())
                        {
                            $tarea= (object) [
                                'minutaId' =>  $minutaId,
                                'minutaTitulo' => $minutaTitulo,
                                'minutaColor' => $minutaColor,
                                'id' =>  $id,
                                'titulo' => $titulo,
                                'fechaAlta' => $fechaAlta,
                                'fechaModificacion' => $fechaModificacion,
                                'fechaCompromiso' => $fechaCompromiso,
                                'fechaFinalizacion' => $fechaFinalizacion,
                                'terminada' => $terminada,
                                'usuarioId' => $usuarioId,
                                'usuarioNombre' => $usuarioNombre,
                                'usuarioApellido' => $usuarioApellido,
                                'numeroComentarios' => $numeroComentarios
                            ];
                            
                            if($tarea->minutaColor=="" || $tarea->minutaColor==null)
                                $tarea->minutaColor="#000000";
                            
                            $tarea->usuarioNombreCompleto = $tarea->usuarioNombre . " " . $tarea->usuarioApellido;
                            $tarea->fotoPerfil =  "../fotos/usuario". $tarea->usuarioId .".jpg";
                            if(file_exists($tarea->fotoPerfil))
                                $tarea->fotoPerfil =  "php/fotos/usuario". $tarea->usuarioId .".jpg";
                            else
                                $tarea->fotoPerfil =  "php/fotos/default.jpg";
                                
                            array_push($tareas,$tarea);
                        }
                        $resultado->valor = $tareas;
                        
                        $sentencia->close();
                        
//                         for($i=0; $i < count($tareas);$i++)
//                         {
//                             $tarea = $tareas[$i];
//                             $resultadoCategorias = $this->consultarResponsablesTarea($minutaId,$tarea->id);
//                             if($resultadoCategorias->correcto())
//                             {
//                                 $tarea->responsables = $resultadoCategorias->valor;
//                             }
//                             else
//                             {
//                                 $resultado->mensajeError = $resultadoCategorias->mensajeError;
//                                 break;
//                             }
//                         }
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__." Falló el enlace del resultado";
                }
                else
                    $resultado->mensajeError = __FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__." Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = __FUNCTION__." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
        return $resultado;
    }
    
    public function consultarTareasUsuario($usuarioId)
    {
        $resultado = new Resultado();
        $tareas = array();
        $consulta = "SELECT T.minuta_id, M.titulo, T.id, RTRIM(T.titulo) titulo, IFNULL(DATE_FORMAT(T.fecha_alta,'%d/%m/%Y %H:%i:%s'),'') as fecha_alta, IFNULL(DATE_FORMAT(T.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'') as fecha_modificacion,
            IFNULL(DATE_FORMAT(T.fecha_compromiso,'%d/%m/%Y'),'') as fecha_compromiso,
            IFNULL(DATE_FORMAT(T.fecha_finalizacion,'%d/%m/%Y %H:%i:%s'),'') as fecha_finalizacion, T.terminada, T.usuario_id, U.nombre, U.apellido,
            CASE WHEN NOW() > fecha_compromiso THEN 1 ELSE 0 END vencida  
            FROM minutas_tareas T
                INNER JOIN usuarios U ON U.id = T.usuario_id
                INNER JOIN minutas M ON M.id = T.minuta_id
             WHERE ? IN (SELECT MTR.usuario_id FROM minutas_tareas_responsables MTR WHERE MTR.minuta_id = T.minuta_id AND MTR.tarea_id = T.id )
                AND T.terminada = 1 
                AND DATEDIFF(NOW(),T.fecha_finalizacion) <= 7
            UNION
        SELECT T.minuta_id, M.titulo, T.id, RTRIM(T.titulo) titulo, IFNULL(DATE_FORMAT(T.fecha_alta,'%d/%m/%Y %H:%i:%s'),'') as fecha_alta, IFNULL(DATE_FORMAT(T.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'') as fecha_modificacion,
            IFNULL(DATE_FORMAT(T.fecha_compromiso,'%d/%m/%Y'),'') as fecha_compromiso,
            IFNULL(DATE_FORMAT(T.fecha_finalizacion,'%d/%m/%Y %H:%i:%s'),'') as fecha_finalizacion, T.terminada, T.usuario_id, U.nombre, U.apellido,
            CASE WHEN NOW() > fecha_compromiso THEN 1 ELSE 0 END vencida  
            FROM minutas_tareas T
                INNER JOIN usuarios U ON U.id = T.usuario_id
                INNER JOIN minutas M ON M.id = T.minuta_id
             WHERE ? IN (SELECT MTR.usuario_id FROM minutas_tareas_responsables MTR WHERE MTR.minuta_id = T.minuta_id AND MTR.tarea_id = T.id )
            AND T.terminada = 0 
              ORDER BY terminada desc, UNIX_TIMESTAMP(fecha_finalizacion), vencida,  UNIX_TIMESTAMP(fecha_alta),  UNIX_TIMESTAMP(fecha_compromiso) 
            ";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ii",$usuarioId,$usuarioId))
            {
                if($sentencia->execute())
                {
                    //$valores = array();
                    if ($sentencia->bind_result($minutaId, $minutaTitulo,$id, $titulo, $fechaAlta, $fechaModificacion, $fechaCompromiso, $fechaFinalizacion, $terminada, $usuarioId, $usuarioNombre, $usuarioApellido, $vencida))
                    //if ($sentencia->bind_result($valores))
                    {
                        while($sentencia->fetch())
                        {
                            $tarea= (object) [
                                'minutaId' =>  $minutaId,
                                'minutaTitulo' =>  $minutaTitulo,
                                'id' =>  $id,
                                'titulo' => $titulo,
                                'fechaAlta' => $fechaAlta,
                                'fechaModificacion' => $fechaModificacion,
                                'fechaCompromiso' => $fechaCompromiso,
                                'fechaFinalizacion' => $fechaFinalizacion,
                                'terminada' => $terminada,
                                'usuarioId' => $usuarioId,
                                'usuarioNombre' => $usuarioNombre,
                                'usuarioApellido' => $usuarioApellido,
                                'vencida' => $vencida
                            ];
                            
                            $tarea->usuarioNombreCompleto = $tarea->usuarioNombre . " " . $tarea->usuarioApellido;
                            $tarea->fotoPerfil =  "../fotos/usuario". $tarea->usuarioId .".jpg";
                            if(file_exists($tarea->fotoPerfil))
                                $tarea->fotoPerfil =  "php/fotos/usuario". $tarea->usuarioId .".jpg";
                                else
                                    $tarea->fotoPerfil =  "php/fotos/default.jpg";
                                    
                                    array_push($tareas,$tarea);
                        }
                        $resultado->valor = $tareas;
                        
                        $sentencia->close();
                        
                        for($i=0; $i < count($tareas);$i++)
                        {
                            $tarea = $tareas[$i];
                            $resultadoCategorias = $this->consultarResponsablesTarea($minutaId,$tarea->id);
                            if($resultadoCategorias->correcto())
                            {
                                $tarea->responsables = $resultadoCategorias->valor;
                            }
                            else
                            {
                                $resultado->mensajeError = $resultadoCategorias->mensajeError;
                                break;
                            }
                        }
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__." Falló el enlace del resultado";
                }
                else
                    $resultado->mensajeError = __FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__." Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = __FUNCTION__." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
            return $resultado;
    }
    
//     public function consultarTareasPendientes($usuario)
//     {
//         $resultado = new Resultado();
//         $tareas = array();
//         $consulta = "SELECT T.minuta_id, T.id, RTRIM(titulo) titulo, IFNULL(DATE_FORMAT(T.fecha_alta,'%d/%m/%Y %H:%i:%s'),'') as fecha_alta, IFNULL(DATE_FORMAT(T.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'') as fecha_modificacion,
//             IFNULL(DATE_FORMAT(T.fecha_compromiso,'%d/%m/%Y'),'') as fecha_compromiso,
//             IFNULL(DATE_FORMAT(T.fecha_finalizacion,'%d/%m/%Y %H:%i:%s'),'') as fecha_finalizacion, terminada, usuario_id, U.nombre, U.apellido
//             FROM minutas_tareas T
//                 INNER JOIN usuarios U ON U.id = T.usuario_id
//              WHERE ? IN (SELECT usuario_id FROM minutas_tareas_responsables MTR WHERE MTR.minuta_id = T.minuta_id AND MTR.tarea_id = T.id) AND T.terminada = 0
//             ORDER BY orden";
//         if($sentencia = $this->conexion->prepare($consulta))
//         {
//             if($sentencia->bind_param("i",$usuario->id))
//             {
//                 if($sentencia->execute())
//                 {
//                     //$valores = array();
//                     if ($sentencia->bind_result($minutaId,$id, $titulo, $fechaAlta, $fechaModificacion, $fechaCompromiso, $fechaFinalizacion, $terminada, $usuarioId, $usuarioNombre, $usuarioApellido))
//                     //if ($sentencia->bind_result($valores))
//                     {
//                         while($sentencia->fetch())
//                         {
//                             $tarea= (object) [
//                                 'minutaId' =>  $minutaId,
//                                 'id' =>  $id,
//                                 'titulo' => $titulo,
//                                 'fechaAlta' => $fechaAlta,
//                                 'fechaModificacion' => $fechaModificacion,
//                                 'fechaCompromiso' => $fechaCompromiso,
//                                 'fechaFinalizacion' => $fechaFinalizacion,
//                                 'terminada' => $terminada,
//                                 'usuarioId' => $usuarioId,
//                                 'usuarioNombre' => $usuarioNombre,
//                                 'usuarioApellido' => $usuarioApellido
//                             ];
                            
//                             $tarea->usuarioNombreCompleto = $tarea->usuarioNombre . " " . $tarea->usuarioApellido;
//                             $tarea->fotoPerfil =  "../fotos/usuario". $tarea->usuarioId .".jpg";
//                             if(file_exists($tarea->fotoPerfil))
//                                 $tarea->fotoPerfil =  "php/fotos/usuario". $tarea->usuarioId .".jpg";
//                             else
//                                 $tarea->fotoPerfil =  "php/fotos/default.jpg";
                                    
//                             array_push($tareas,$tarea);
//                         }
//                         $resultado->valor = $tareas;
                        
//                         $sentencia->close();
                        
// //                         for($i=0; $i < count($tareas);$i++)
// //                         {
// //                             $tarea = $tareas[$i];
// //                             $resultadoCategorias = $this->consultarResponsablesTarea($minutaId,$tarea->id);
// //                             if($resultadoCategorias->correcto())
// //                             {
// //                                 $tarea->responsables = $resultadoCategorias->valor;
// //                             }
// //                             else
// //                             {
// //                                 $resultado->mensajeError = $resultadoCategorias->mensajeError;
// //                                 break;
// //                             }
// //                         }
//                     }
//                     else
//                         $resultado->mensajeError = __FUNCTION__." Falló el enlace del resultado";
//                 }
//                 else
//                     $resultado->mensajeError = __FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
//             }
//             else
//                 $resultado->mensajeError = __FUNCTION__." Falló el enlace de parámetros";
//         }
//         else
//             $resultado->mensajeError = __FUNCTION__." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
//             return $resultado;
//     }
    
    private function consultarResponsablesTarea($minutaId,$tareaId)
    {
        $resultado = new Resultado();
        $responsables = array();
        $consulta = "SELECT MTR.id, usuario_id, U.nombre, U.apellido " .
            "FROM minutas_tareas_responsables MTR
                INNER JOIN usuarios U ON U.id = MTR.usuario_id
             WHERE minuta_id = ? AND tarea_id = ? ".
            "ORDER BY MTR.id";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            
            if($sentencia->bind_param("ii",$minutaId,$tareaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id,$usuarioId, $usuarioNombre, $usuarioApellido))
                    {
                        while($sentencia->fetch())
                        {
                            $responsable= (object) [
                                'id' =>  $id,
                                'usuarioId' =>  $usuarioId,
                                'usuarioNombre' => $usuarioNombre,
                                'usuarioApellido' => $usuarioApellido
                            ];
                            $responsable->usuarioNombreCompleto = $responsable->usuarioNombre . " " . $responsable->usuarioApellido;
                            array_push($responsables,$responsable);
                        }
                        $resultado->valor = $responsables;
                        $sentencia->close();
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__. ". Falló el enlace del resultado";
                }
                else
                    $resultado->mensajeError = __FUNCTION__. ". Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__. ". Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = __FUNCTION__. ". Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
            return $resultado;
    }
    
//     private function consultarDuenoTarea($minutaId,$tareaId)
//     {
//         $resultado = new Resultado();
//         $consulta = "SELECT usuario_id " .
//             "FROM minutas_tareas " .
//             " WHERE minuta_id = ? AND tarea_id = ? ";
//         if($sentencia = $this->conexion->prepare($consulta))
//         {
            
//             if($sentencia->bind_param("ii",$minutaId,$tareaId))
//             {
//                 if($sentencia->execute())
//                 {
//                     if ($sentencia->bind_result($id))
//                     {
//                         if($sentencia->fetch())
//                         {
//                             $dueno= (object) [
//                                 'id' =>  $id
//                             ];
//                             $resultado->valor = $dueno;
//                         }
                      
//                         $sentencia->close();
//                     }
//                     else
//                         $resultado->mensajeError = __FUNCTION__. ". Falló el enlace del resultado";
//                 }
//                 else
//                     $resultado->mensajeError = __FUNCTION__. ". Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
//             }
//             else
//                 $resultado->mensajeError = __FUNCTION__. ". Falló el enlace de parámetros";
//         }
//         else
//             $resultado->mensajeError = __FUNCTION__. ". Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
//             return $resultado;
//     }
    
    public function ordenarTareas($minutaId, $seleccion)
    {
        $resultado = new Resultado();
        
        $registros = explode("&", $seleccion);
        
        $this->conexion->autocommit(FALSE);
        
        $i = 1;
        
        $salida ="";
        foreach ($registros as $key => $value)
        {
            $ides = explode("=", $value);
            
            $id = str_replace("tareaDiv","",$ides[1]);
            
            $consulta = " UPDATE minutas_tareas " .
                "SET orden = ? " .
                "WHERE minuta_id = ? AND id = ? ";
            
            //$salida.= ";".$consulta . ";$i;$cursoId;$id";
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if( $sentencia->bind_param("iii", $i,$minutaId,$id))
                {
                    if($sentencia->execute())
                    {
                        $sentencia->close();
                    }
                    else
                    {
                        $resultado->mensajeError = __FUNCTION__." Falló la ejecución(" . $this->conexion->errno . ") " . $this->conexion->error;
                        break;
                    }
                }
                else
                {
                    $resultado->mensajeError = __FUNCTION__." Falló el enlace de parámetros";
                    break;
                }
            }
            else
            {
                $resultado->mensajeError = __FUNCTION__." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
                break;
            }
            
            $i++;
        }
        if($resultado->correcto())
        {
            $this->conexion->commit();
            $resultado->valor = true;
        }
        else
            $this->conexion->rollback();
            
            return $resultado;
    }
    
    public function actualizarValorTarea($usuario,$minutaId, $tareaId, $campo, $valor)
    {
        $resultado = new Resultado();
        $this->conexion->autocommit(FALSE);
        $finalizacion = "";
        
       
        
        if($campo=="terminada")
        {
            if($valor==1)
                $finalizacion = "fecha_finalizacion = NOW(),
                                 usuario_finalizacion_id = $usuario->id";
            else
                $finalizacion = "fecha_finalizacion = NULL,
                                     usuario_finalizacion_id = NULL";
        }
        
        
        $consulta = " UPDATE minutas_tareas 
           SET $campo = ? ,
                fecha_modificacion = NOW(),
                $finalizacion
                
            WHERE minuta_id = ? AND id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("sii", $valor, $minutaId, $tareaId))
            {
                if($sentencia->execute())
                {
                    $resultado->valor=true;
                    $sentencia->close();
                    
                    $resultado = $this->actualizarMinuta($minutaId);
                    if($resultado->correcto())
                    {
                        if($campo=="terminada" && $valor==1)
                        {
                            $resultado = $this->consultarTareaPorLlaves((object)['minutaId' => $minutaId, "tareaId" => $tareaId]);                            
                            if($resultado->correcto())
                            {
                                $tarea = $resultado->valor;
                               
                                //$resultado = $this->enviarNotificacionDuenoTareaTerminada($usuario,$minutaId, $tarea);
                            }
                        }
                    }
                    
                }
                else
                    $resultado->mensajeError =__FUNCTION__." Falló la ejecución actualizar(" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__." Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = __FUNCTION__." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
        
        if($resultado->correcto())
        {
            $this->conexion->commit();
            $resultado->valor=true;
        }
        else
            $this->conexion->rollback();
        return $resultado;
    }
    
    public function actualizarFinalizacionMinuta($minutaId, $terminada, $fechaFinalizacion)
    {
        $resultado = new Resultado();
        
        $consulta = " UPDATE minutas
           SET terminada = ? ,
                fecha_finalizacion = $fechaFinalizacion,
                fecha_modificacion = NOW()
            WHERE id = ?";
                
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("ii", $terminada,$minutaId))
            {
                if($sentencia->execute())
                {
                    $resultado->valor=true;
                    $sentencia->close();
                }
                else
                    $resultado->mensajeError =__FUNCTION__." Falló la ejecución actualizar(" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__." Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = __FUNCTION__." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
                    
        return $resultado;
    }
    
    public function consultarNumeroTareasPendientesMinuta($minutaId)
    {
        $resultado = new Resultado();
        $consulta =  "SELECT count(*) AS id FROM minutas_tareas WHERE minuta_id = ? AND terminada != 1  ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("i", $minutaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id))
                    {
                        if($sentencia->fetch())
                        {
                            $resultado->valor = $id;
                        }
                        else
                            $resultado->mensajeError =  __FUNCTION__. " No se encontró ningún resultado";
                    }
                    else
                        $resultado->mensajeError =  __FUNCTION__. " Falló el enlace del resultado";
                }
                else
                    $resultado->mensajeError =  __FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
            {
                $resultado->mensajeError =  __FUNCTION__. " Falló el enlace de parámetros";
            }
        }
        else
            $resultado->mensajeError =  __FUNCTION__." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            return $resultado;
    }   
    
    public function consultarNumeroTareasMinuta($minutaId)
    {
        $resultado = new Resultado();
        $consulta =  "SELECT count(*) AS id FROM minutas_tareas WHERE minuta_id = ?";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("i", $minutaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id))
                    {
                        if($sentencia->fetch())
                        {
                            $resultado->valor = $id;
                        }
                        else
                            $resultado->mensajeError =  __FUNCTION__. " No se encontró ningún resultado";
                    }
                    else
                        $resultado->mensajeError =  __FUNCTION__. " Falló el enlace del resultado";
                }
                else
                    $resultado->mensajeError =  __FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
            {
                $resultado->mensajeError =  __FUNCTION__. " Falló el enlace de parámetros";
            }
        }
        else
            $resultado->mensajeError =  __FUNCTION__." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            return $resultado;
    }   
    
    private function actualizarMinuta($minutaId)
    {
        $resultado = new Resultado();
        $resultado = $this->consultarNumeroTareasPendientesMinuta($minutaId);
        if($resultado->correcto())
        {
            $pendientes = $resultado->valor;
            $resultado = $this->consultarNumeroTareasMinuta($minutaId);
            if($resultado->correcto())
            {
                $tareasTotales = $resultado->valor;
                if($tareasTotales==0)
                {
                    $resultado = $this->actualizarFinalizacionMinuta($minutaId,0,"NULL");
                }
                else 
                {
                    if($pendientes==0)
                        $resultado = $this->actualizarFinalizacionMinuta($minutaId,1,"NOW()");
                    else
                        $resultado = $this->actualizarFinalizacionMinuta($minutaId,0,"NULL");
                }
            }
        }
        return $resultado;
    }
    
    public function eliminarTarea($llaves)
    {
        ini_set('max_execution_time', 300);
        $resultado = new Resultado();
        $this->conexion->autocommit(FALSE);
        $resultado = $this->eliminarResponsablesTarea($llaves->minutaId,$llaves->tareaId);
        if($resultado->correcto())
        {
            $resultado = $this->eliminarComentariosTarea($llaves->minutaId,$llaves->tareaId);
            if($resultado->correcto())
            {
                $consulta ="DELETE FROM minutas_tareas WHERE minuta_id = ? AND id = ?";
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if($sentencia->bind_param("ii",$llaves->minutaId,$llaves->tareaId))
                    {
                        if($sentencia->execute())
                        {
                            $sentencia->close();
                            $resultado = $this->actualizarMinuta($llaves->minutaId);
                        }
                        else
                        {
                            $resultado->codigoError = $this->conexion->errno;
                            $resultado->mensajeError = __FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                        }
                        
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__ ." Falló el enlace de parámetros";
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = __FUNCTION__ ." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
                    
                }
            }
        }
        if($resultado->correcto())
            $this->conexion->commit();
        else
            $this->conexion->rollback();
            
        return $resultado;
    }
    
    public function eliminarResponsablesTarea($minutaId, $tareaId)
    {
        $resultado = new Resultado();
        
        $consulta ="DELETE FROM minutas_tareas_responsables WHERE minuta_id = ? AND tarea_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ii",$minutaId,$tareaId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = __FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
                
            }
            else
                $resultado->mensajeError = __FUNCTION__ ." Falló el enlace de parámetros";
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = __FUNCTION__ ." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
        }
        return $resultado;
    }
    
    public function eliminarComentariosTarea($minutaId, $tareaId)
    {
        $resultado = new Resultado();
        
        $consulta ="DELETE FROM minutas_tareas_comentarios WHERE minuta_id = ? AND tarea_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ii",$minutaId,$tareaId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = __FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
                
            }
            else
                $resultado->mensajeError = __FUNCTION__ ." Falló el enlace de parámetros";
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = __FUNCTION__ ." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
        }
        return $resultado;
    }
    
    public function eliminarResponsablesMinuta($minutaId)
    {
        $resultado = new Resultado();
        
        $consulta ="DELETE FROM minutas_tareas_responsables WHERE minuta_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$minutaId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = __FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
                
            }
            else
                $resultado->mensajeError = __FUNCTION__ ." Falló el enlace de parámetros";
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = __FUNCTION__ ." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
        }
        return $resultado;
    }
    
    public function eliminarTareas($minutaId)
    {
        $resultado = new Resultado();
        
        $consulta ="DELETE FROM minutas_tareas WHERE minuta_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$minutaId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                    
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = __FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
                
            }
            else
                $resultado->mensajeError = __FUNCTION__ ." Falló el enlace de parámetros";
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = __FUNCTION__ ." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
        }
                
        return $resultado;
    }
    
    public function insertarTarea($minutaId, $modelo, $usuario)
    {
        $fechaCompromiso = "";
        if($modelo->fechaCompromiso!="")
        {
            $elementos = explode("/",$modelo->fechaCompromiso);
            $elementos = array_reverse($elementos);
            $fechaCompromiso = join("-",$elementos);
        }
        $this->conexion->autocommit(FALSE);
        $resultado =  $this->calcularIdTarea($minutaId, "id");
        if($resultado->correcto())
        {
            $modelo->id =  $resultado->valor;
            $resultado =  $this->calcularIdTarea($minutaId,"orden");
            if($resultado->correcto())
            {
                $orden =  $resultado->valor;
                $consulta = "INSERT INTO minutas_tareas(minuta_id, id, orden, usuario_id, fecha_alta, fecha_modificacion, terminada, titulo,fecha_compromiso, tipo) " .
                    "VALUE(?, ?, ?, ?, NOW(), NOW(), 0, ?, ?, ?)";
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if($sentencia->bind_param("iiiisss",$minutaId, $modelo->id , $orden, $usuario->id, $modelo->titulo,  $fechaCompromiso, $modelo->tipo))
                    {
                        if($sentencia->execute())
                        {
                            $sentencia->close();
                            
                            $resultado = $this->consultarUsuariosNoAsignados($minutaId,$modelo);
                            if($resultado->correcto())
                            {
                                $usuarios = $resultado->valor;
                                $resultado = $this->insertarResponsablesTarea($minutaId, $modelo);
                                if($resultado->correcto())
                                {
                                    $resultado = $this->actualizarMinuta($minutaId);
                                    if($resultado->correcto())
                                    {
                                        $resultado = $this->enviarNotificacionResponsables($usuario,$minutaId, $modelo, $usuarios);
                                    }
                                }
                            }
                        }
                        else
                        {
                            $resultado->mensajeError = __FUNCTION__.". Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                        }
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__.". Falló el enlace de parámetros";
                }
                else
                    $resultado->mensajeError = __FUNCTION__.". Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
        }
        if($resultado->correcto())
        {
            $this->conexion->commit();
            $resultado->valor = $modelo->id ;
        }
        else
            $this->conexion->rollback();
        
        return $resultado;
    }
    
    public function enviarNotificacionResponsables($usuario,$minutaId, $tarea , $usuarios)
    {
        $resultado = new Resultado();
     
        if($tarea->responsables!="" && $tarea->responsables!=null && count($usuarios)>0)
        {
            $resultado = $this->consultarPorLlaves((object)['id' => $minutaId],false);
            if($resultado->correcto())
            {
                $minuta = $resultado->valor;
                $frasesRepositorio = new FrasesRepositorio($this->conexion);
                $resultado = $frasesRepositorio->consultarAleatorio();
                if($resultado->correcto())
                {
                   
                    $frase = $resultado->valor;
                    
//                     $resultado = $this->consultarUsuariosNoAsignados($minuta,$tarea);
//                     if($resultado->correcto())
//                     {
//                         $usuarios = $resultado->valor;
                    
                        //array_push($usuarios,(object) ['nombreUsuario' => 'alanbazan@apps-handel.com','nombreCompleto' => 'Alan Bazán']);
                        
                        $nombreUsuario = $usuario->nombreCompleto;
                        $fotoPerfil = "https://api.apps-handel.com/" . $usuario->fotoPerfil;
                        $asunto  = $usuario->nombreCompleto . ": te asignó  una tarea: " . $tarea->titulo;
                        $accion = "te asignó  una tarea: ";
                        
                        $asunto="=?UTF-8?B?".base64_encode($asunto)."?=";
                        
                        $tipo = "minuta" .$minuta->id ."tarea" . $tarea->id;
                        $info = "";
                        $mensaje= file_get_contents('../plantillas_correo/notificacion_tarea.html');
                        
                        
                        $mensaje=  str_replace("@nombreUsuario",$nombreUsuario,$mensaje);
                        $mensaje=  str_replace("@accion",$accion,$mensaje);
                        $mensaje=  str_replace("@fotoPerfil",$fotoPerfil,$mensaje);
                        $mensaje=  str_replace("@nombreMinuta",$minuta->titulo,$mensaje);
                        $mensaje=  str_replace("@nombreTarea",$tarea->titulo,$mensaje);
                        $mensaje=  str_replace("@fechaVencimiento",$tarea->fechaCompromiso,$mensaje);
                        
                        $mensaje=  str_replace("@minutaId",$minuta->id,$mensaje);
                        $mensaje=  str_replace("@tareaId",$tarea->id,$mensaje);
                        $mensaje=  str_replace("@texto","",$mensaje);
                        
                        
                        $mensaje=  str_replace("@frase",$frase->texto,$mensaje);
                        $mensaje=  str_replace("@autor",$frase->autor,$mensaje);
                        
                       
                        
                        $administrador_correo = new AdministradorCorreo();
                        $resultado = $administrador_correo->enviarCorreoUsuarios($tipo,$usuarios,$asunto, $mensaje, $info, "SAHA: Tareas");
                   //}
                }
            }
        }
        return $resultado;
    }
    
   
    public function enviarNotificacionDuenoTareaTerminada($usuario,$minutaId, $tarea)
    {
        $resultado = new Resultado();
        $resultado = $this->consultarPorLlaves((object)['id' => $minutaId],false);
        $usuarios = array();
        if($resultado->correcto())
        {
            $minuta = $resultado->valor;
            $frasesRepositorio = new FrasesRepositorio($this->conexion);
            $resultado = $frasesRepositorio->consultarAleatorio();
            if($resultado->correcto())
            {
                $frase = $resultado->valor;
                $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
                $resultado =  $usuariosRepositorio->consultarPorLLaves((object)['id' => $tarea->usuarioId]);
                if($resultado->correcto())
                {
                    $dueno = $resultado->valor;
                    
              
                    array_push($usuarios,$dueno);
                    //                     $resultado = $this->consultarUsuariosNoAsignados($minuta,$tarea);
                    //                     if($resultado->correcto())
                        //                     {
                    //                         $usuarios = $resultado->valor;
                    
                    //array_push($usuarios,(object) ['nombreUsuario' => 'alanbazan@apps-handel.com','nombreCompleto' => 'Alan Bazán']);
                    
                    $nombreUsuario = $usuario->nombreCompleto;
                    $fotoPerfil = "https://api.apps-handel.com/" . $usuario->fotoPerfil;
                    $asunto  = $usuario->nombreCompleto . ": terminó una tarea que le asignaste: " . $tarea->titulo;
                    $accion = "terminó una tarea que le asignaste: ";
                    
                    $asunto="=?UTF-8?B?".base64_encode($asunto)."?=";
                    
                    $tipo = "minuta" .$minuta->id ."tarea" . $tarea->id;
                    $info = "";
                    $mensaje= file_get_contents('../plantillas_correo/notificacion_tarea.html');
                    
                   
                    
                    $mensaje=  str_replace("@nombreUsuario",$nombreUsuario,$mensaje);
                    $mensaje=  str_replace("@accion",$accion,$mensaje);
                    $mensaje=  str_replace("@fotoPerfil",$fotoPerfil,$mensaje);
                    $mensaje=  str_replace("@nombreMinuta",$minuta->titulo,$mensaje);
                    $mensaje=  str_replace("@nombreTarea",$tarea->titulo,$mensaje);
                    $mensaje=  str_replace("@fechaVencimiento",$tarea->fechaCompromiso,$mensaje);
                    
                    $mensaje=  str_replace("@minutaId",$minuta->id,$mensaje);
                    $mensaje=  str_replace("@tareaId",$tarea->id,$mensaje);
                    
                    $porcentaje = $minuta->porcentaje;
                    $mensaje=  str_replace("@texto","<br>Con ésta tarea cumplida, el avance de la minuta es $porcentaje%",$mensaje);
                    
                    
                    $mensaje=  str_replace("@frase",$frase->texto,$mensaje);
                    $mensaje=  str_replace("@autor",$frase->autor,$mensaje);
                    
                    
                    
                    $administrador_correo = new AdministradorCorreo();
                    $resultado = $administrador_correo->enviarCorreoUsuarios($tipo,$usuarios,$asunto, $mensaje, $info, "SAHA: Tareas");
                }
                //}
            }
        }
        return $resultado;
    }
    
    public function enviarNotificacionUsuariosMinuta($usuario,$minutaId, $usuarios)
    {
        $resultado = new Resultado();
        
        if(count($usuarios)>0)
        {
            $resultado = $this->consultarPorLlaves((object)['id' => $minutaId],false);
            if($resultado->correcto())
            {
                $minuta = $resultado->valor;
                $frasesRepositorio = new FrasesRepositorio($this->conexion);
                $resultado = $frasesRepositorio->consultarAleatorio();
                if($resultado->correcto())
                {
                    
                    $frase = $resultado->valor;
                    
                    $nombreUsuario = $usuario->nombreCompleto;
                    $fotoPerfil = "https://api.apps-handel.com/" . $usuario->fotoPerfil;
                    $asunto  = $usuario->nombreCompleto . ": te compartió una minuta: " . $minuta->titulo;
                    $accion = "te compartió una minuta: ";
                    
                    $asunto="=?UTF-8?B?".base64_encode($asunto)."?=";
                    
                    $tipo = "minuta" .$minuta->id;
                    $info = "";
                    $mensaje= file_get_contents('../plantillas_correo/notificacion_minuta.html');
                    
                    
                    $mensaje=  str_replace("@nombreUsuario",$nombreUsuario,$mensaje);
                    $mensaje=  str_replace("@accion",$accion,$mensaje);
                    $mensaje=  str_replace("@fotoPerfil",$fotoPerfil,$mensaje);
                    $mensaje=  str_replace("@nombreMinuta",$minuta->titulo,$mensaje);
                    //$mensaje=  str_replace("@nombreTarea","",$mensaje);
                    //$mensaje=  str_replace("@fechaVencimiento",$tarea->fechaCompromiso,$mensaje);
                    
                    $mensaje=  str_replace("@minutaId",$minuta->id,$mensaje);
                    //$mensaje=  str_replace("@tareaId",$tarea->id,$mensaje);
                    
                    
                    $mensaje=  str_replace("@frase",$frase->texto,$mensaje);
                    $mensaje=  str_replace("@autor",$frase->autor,$mensaje);
                    
                    
                    
                    $administrador_correo = new AdministradorCorreo();
                    $resultado = $administrador_correo->enviarCorreoUsuarios($tipo,$usuarios,$asunto, $mensaje, $info, "SAHA: Tareas");
                    //}
                }
            }
        }
        return $resultado;
    }
    
    public function consultarUsuariosConTareas()
    {
        $resultado = new Resultado();
        $usuarios = array();
        
        $consulta = "SELECT id, nombre, apellido, nombre_usuario
                    FROM usuarios
                    WHERE id IN(SELECT usuario_id
                    FROM minutas_tareas_responsables
                    )";
        
            
        if($sentencia = $this->conexion->prepare($consulta))
        {
            //if($sentencia->bind_param("ii",$minutaId,$tarea->id))
            //{
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombre, $apellido, $nombreUsuario))
                    {
                        
                        while($sentencia->fetch())
                        {
                            $usuario= (object) [
                                'id' =>  $id,
                                'nombre' => $nombre,
                                'apellido' => $apellido,
                                'nombreUsuario' => $nombreUsuario
                            ];
                            
                            
                            $usuario->nombreCompleto = $usuario->nombre . " " . $usuario->apellido;
                            //                             $usuario->fotoPerfil =  "../fotos/usuario". $usuario->id .".jpg";
                            //                             if(file_exists($usuario->fotoPerfil))
                            //                                 $usuario->fotoPerfil =  "php/fotos/usuario". $usuario->id .".jpg";
                            //                             else
                            //                                 $usuario->fotoPerfil =  "php/fotos/default.jpg";
                                
                            array_push($usuarios,$usuario);
                        }
                        
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__." Falló el enlace del resultado";
                }
                else
                    $resultado->mensajeError = __FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__." Falló el enlace de parámetros";
//         }
//         else
//             $resultado->mensajeError = __FUNCTION__." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
       
        $resultado->valor = $usuarios;
        return $resultado;
    }
    
    public function consultarUsuariosNoAsignados($minutaId,$tarea)
    {
        $resultado = new Resultado();
        $usuarios = array();
        $usuariosIds = "";
        if(isset($tarea->responsables) && $tarea->responsables!=null)
        {
            for ($l = 0; $l< count($tarea->responsables); $l++)
            {
                $responsable = $tarea->responsables[$l];
                $usuariosIds.= $responsable->usuarioId;
                if($l < count($tarea->responsables) -1)
                    $usuariosIds.=",";
            }
            
            $consulta = "SELECT id, nombre, apellido, nombre_usuario
                        FROM usuarios
                        WHERE id IN ($usuariosIds) AND id NOT IN(SELECT usuario_id
                        FROM minutas_tareas_responsables
                        WHERE minuta_id = ? AND tarea_id = ?)";
            
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param("ii",$minutaId,$tarea->id))
                {
                    if($sentencia->execute())
                    {
                        if ($sentencia->bind_result($id, $nombre, $apellido, $nombreUsuario))
                        {
                        
                            while($sentencia->fetch())
                            {
                                $usuario= (object) [
                                    'id' =>  $id,
                                    'nombre' => $nombre,
                                    'apellido' => $apellido,
                                    'nombreUsuario' => $nombreUsuario
                                ];
                                
                                
                                $usuario->nombreCompleto = $usuario->nombre . " " . $usuario->apellido;
    //                             $usuario->fotoPerfil =  "../fotos/usuario". $usuario->id .".jpg";
    //                             if(file_exists($usuario->fotoPerfil))
    //                                 $usuario->fotoPerfil =  "php/fotos/usuario". $usuario->id .".jpg";
    //                             else
    //                                 $usuario->fotoPerfil =  "php/fotos/default.jpg";
                                        
                                array_push($usuarios,$usuario);
                            }
                            
                            
                         
                            
                           
                            
                        }
                        else
                            $resultado->mensajeError = __FUNCTION__." Falló el enlace del resultado";
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
                else
                    $resultado->mensajeError = __FUNCTION__." Falló el enlace de parámetros";
            }
            else
                $resultado->mensajeError = __FUNCTION__." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
        }
        $resultado->valor = $usuarios;
        return $resultado;
    }
    
    public function consultarUsuariosNoAsignadosMinuta($minutaId,$usuariosCompartir)
    {
        $resultado = new Resultado();
        $usuarios = array();
        $usuariosIds = "";
        if($usuariosCompartir!=null)
        {
            for ($l = 0; $l< count($usuariosCompartir); $l++)
            {
                $responsable = $usuariosCompartir[$l];
                $usuariosIds.= $responsable->usuarioId;
                if($l < count($usuariosCompartir) -1)
                    $usuariosIds.=",";
            }
            
            $consulta = "SELECT id, nombre, apellido, nombre_usuario
                        FROM usuarios
                        WHERE id IN ($usuariosIds) AND id NOT IN(SELECT usuario_id
                        FROM minutas_usuarios
                        WHERE minuta_id = ?)";
            
            
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param("i",$minutaId))
                {
                    if($sentencia->execute())
                    {
                        if ($sentencia->bind_result($id, $nombre, $apellido, $nombreUsuario))
                        {
                            
                            while($sentencia->fetch())
                            {
                                $usuario= (object) [
                                    'id' =>  $id,
                                    'nombre' => $nombre,
                                    'apellido' => $apellido,
                                    'nombreUsuario' => $nombreUsuario
                                ];
                                
                                $usuario->nombreCompleto = $usuario->nombre . " " . $usuario->apellido;
                                array_push($usuarios,$usuario);
                            }
                            
                        }
                        else
                            $resultado->mensajeError = __FUNCTION__." Falló el enlace del resultado";
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
                else
                    $resultado->mensajeError = __FUNCTION__." Falló el enlace de parámetros";
            }
            else
                $resultado->mensajeError = __FUNCTION__." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
        }
        $resultado->valor = $usuarios;
        return $resultado;
    }
    
    public function actualizarTarea($minutaId, $modelo, $usuario)
    {
        
        $fechaCompromiso = "";
        if($modelo->fechaCompromiso!="")
        {
            $elementos = explode("/",$modelo->fechaCompromiso);
            $elementos = array_reverse($elementos);
            $fechaCompromiso = join("-",$elementos);
        }
        
        
        $this->conexion->autocommit(FALSE);
        $consulta = "UPDATE minutas_tareas
                        SET titulo = ?,
                            fecha_compromiso = ?,
                            fecha_modificacion = NOW()
                      WHERE minuta_id = ? AND id = ?";  
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ssii",$modelo->titulo,$fechaCompromiso,$minutaId, $modelo->id))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                    
                    $resultado = $this->consultarUsuariosNoAsignados($minutaId,$modelo);
                    if($resultado->correcto())
                    {
                        $usuarios = $resultado->valor;
                        //var_dump($usuarios);    
                    
                        $resultado = $this->eliminarResponsablesTarea($minutaId, $modelo->id);
                        if($resultado->correcto())
                        {
                            $resultado = $this->insertarResponsablesTarea($minutaId, $modelo);
                            if($resultado->correcto())
                            {
                                $resultado = $this->actualizarMinuta($minutaId);
                                if($resultado->correcto())
                                {
                                    $resultado = $this->enviarNotificacionResponsables($usuario,$minutaId, $modelo, $usuarios);
                                }
                            }
                        }
                    }
                    
                }
                else
                {
                    $resultado->mensajeError = __FUNCTION__.". Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
            }
            else
                $resultado->mensajeError = __FUNCTION__.". Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = __FUNCTION__.". Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
        
        if($resultado->correcto())
        {
            $this->conexion->commit();
            $resultado->valor = $modelo->id;
        }
        else
            $this->conexion->rollback();
            
            return $resultado;
    }
    
    private function insertarResponsablesTarea($minutaId,$tarea)
    {
        $resultado = new Resultado();
        
        if(isset($tarea->responsables) && $tarea->responsables!=null)
        {
            for ($l = 0; $l< count($tarea->responsables); $l++)
            {
                $responsable = $tarea->responsables[$l];
                
                $consulta = "INSERT INTO minutas_tareas_responsables(minuta_id, tarea_id, id, usuario_id) " .
                    "VALUE(?, ?, ?, ?)";
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if($sentencia->bind_param("iiii",$minutaId,$tarea->id,$responsable->id, $responsable->usuarioId))
                    {
                        if($sentencia->execute())
                        {
                            $sentencia->close();
                        }
                        else
                        {
                            $resultado->codigoError = $this->conexion->errno;
                            $resultado->mensajeError = __FUNCTION__.". Falló la ejecución: (" . $this->conexion->errno . ") " . $this->conexion->error;
                            break;
                        }
                    }
                    else
                    {
                        $resultado->mensajeError = __FUNCTION__.". Falló el enlace de parámetros";
                        break;
                    }
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = __FUNCTION__.". Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
                    break;
                }
            }
            
        }
        return $resultado;
    }
    
    public function calcularIdTarea($minutaId, $campo)
    {
        $resultado = new Resultado();
        $consulta =  "SELECT IFNULL(MAX($campo),0)+1 AS id FROM minutas_tareas WHERE minuta_id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("i", $minutaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id))
                    {
                        if($sentencia->fetch())
                        {
                            $resultado->valor = $id;
                        }
                        else
                            $resultado->mensajeError =  __FUNCTION__. " No se encontró ningún resultado";
                    }
                    else
                        $resultado->mensajeError =  __FUNCTION__. " Falló el enlace del resultado";
                }
                else
                    $resultado->mensajeError =  __FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
            {
                $resultado->mensajeError =  __FUNCTION__. " Falló el enlace de parámetros";
            }
        }
        else
            $resultado->mensajeError =  __FUNCTION__." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            return $resultado;
    }
    
    
    public function actualizarUsuarios($usuario,$minutaId, $usuarios)
    {
//         $resultado = new Resultado();
//         ini_set('max_execution_time', 300);
//         $resultado = new Resultado();
//         $this->conexion->autocommit(FALSE);
//         $resultado  = $this->eliminarUsuarios($minutaId);
//         if($resultado->correcto())
//         {
//             $resultado  = $this->insertarUsuarios($minutaId,$usuarios);
            
//             if($resultado->correcto())
//             {
//                 $resultado = $this->consultarUsuariosNoAsignadosCompartir($minutaId,$modelo);
//                 if($resultado->correcto())
//                 {
                    
//                 }
                    
//                 //$resultado = $this->enviarNotificacionUsuarios($usuario,$minutaId, $modelo, $usuarios);
//             }
//         }
//         if($resultado->correcto())
//             $this->conexion->commit();
//         else
//             $this->conexion->rollback();
//         return $resultado;

         $resultado = new Resultado();
        ini_set('max_execution_time', 300);
        $resultado = new Resultado();
        $this->conexion->autocommit(FALSE);
       
        $resultado = $this->consultarUsuariosNoAsignadosMinuta($minutaId,$usuarios);
        
        if($resultado->correcto())
        {
            $usuariosCompartir = $resultado->valor;
            $resultado  = $this->eliminarUsuarios($minutaId);
            if($resultado->correcto())
            {
                $resultado  = $this->insertarUsuarios($minutaId,$usuarios);
                if($resultado->correcto())
                {
                    $resultado = $this->actualizarMinuta($minutaId);
                    if($resultado->correcto())
                    {
                        $resultado = $this->enviarNotificacionUsuariosMinuta($usuario,$minutaId, $usuariosCompartir);
                    }
                }
            }
        }
        
        if($resultado->correcto())
            $this->conexion->commit();
        else
            $this->conexion->rollback();
        return $resultado;

    }
    
    private function eliminarUsuarios($minutaId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM minutas_usuarios WHERE minuta_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$minutaId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                    
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = __FUNCTION__ ." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
                
            }
            else
                $resultado->mensajeError = __FUNCTION__ ." Falló el enlace de parámetros";
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = __FUNCTION__ ." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
        }
        return $resultado;
    }
    
    private function insertarUsuarios($minutaId,$usuarios)
    {
        $resultado = new Resultado();
        
      
        
        for ($l = 0; $l< count($usuarios); $l++)
        {
            $usuario = $usuarios[$l];
            
            $consulta = "INSERT INTO minutas_usuarios(minuta_id, id, usuario_id) " .
                "VALUE(?, ?, ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param("iii",$minutaId,$usuario->id, $usuario->usuarioId))
                {
                    if($sentencia->execute())
                    {
                        $sentencia->close();
                    }
                    else
                    {
                        $resultado->codigoError = $this->conexion->errno;
                        $resultado->mensajeError = __FUNCTION__." Falló la ejecución: (" . $this->conexion->errno . ") " . $this->conexion->error;
                        break;
                    }
                }
                else
                {
                    $resultado->mensajeError = __FUNCTION__." Falló el enlace de parámetros";
                    break;
                }
            }
            else
            {
                $resultado->codigoError = $this->conexion->errno;
                $resultado->mensajeError = __FUNCTION__." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
                break;
            }
        }
        return $resultado;
    }
    
    
    private function consultarUsuarios($minutaId)
    {
        $resultado = new Resultado();
        $usuarios = array();
        $consulta = "SELECT id, usuario_id " .
            "FROM minutas_usuarios " .
            " WHERE minuta_id = ? ".
            "ORDER BY id";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            
            if($sentencia->bind_param("i",$minutaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id,$usuarioId))
                    {
                        while($sentencia->fetch())
                        {
                            $usuario= (object) [
                                'id' =>  $id,
                                'usuarioId' =>  $usuarioId
                            ];
                            array_push($usuarios,$usuario);
                        }
                        $resultado->valor = $usuarios;
                        $sentencia->close();
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__. " Falló el enlace del resultado";
                }
                else
                    $resultado->mensajeError = __FUNCTION__. " Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__. " Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = __FUNCTION__. " Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
            return $resultado;
    }
    
    public function consultarNumeroComentariosTarea($minutaId, $tareaId)
    {
        $resultado = new Resultado();
        $consulta =  "SELECT count(*) AS id FROM minutas_tareas_comentarios WHERE minuta_id = ? AND tarea_id = ?  ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("ii", $minutaId, $tareaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id))
                    {
                        if($sentencia->fetch())
                        {
                            $resultado->valor = $id;
                        }
                        else
                            $resultado->mensajeError =  __FUNCTION__. " No se encontró ningún resultado";
                    }
                    else
                        $resultado->mensajeError =  __FUNCTION__. " Falló el enlace del resultado";
                }
                else
                    $resultado->mensajeError =  __FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
            {
                $resultado->mensajeError =  __FUNCTION__. " Falló el enlace de parámetros";
            }
        }
        else
            $resultado->mensajeError =  __FUNCTION__." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            return $resultado;
    }   
    
    public function consultarPorcentajeAvance($minutaId)
    {
        $resultado = new Resultado();
        $resultado = $this->consultarPorLlaves( (object) ["id"=>$minutaId], false);
        if($resultado->correcto())
        {
            $minuta = $resultado->valor;
            $resultado->valor = $minuta->porcentaje;
        }
        return $resultado;
    }  
    
}
