<?php
namespace php\repositorios;

use DateTime;
use DateInterval;
use TipoReporte;

use php\interfaces\IMinutasRepositorio;
use php\modelos\Minuta;
use php\modelos\Resultado;
use php\clases\Porcentaje;
use php\clases\AdministradorCorreo;
use php\clases\ArrayUtils;

include '../interfaces/IMinutasRepositorio.php';
include '../modelos/Minuta.php';
require_once('RepositorioBase.php');
require_once('AuditoriasRepositorio.php');
require_once('../clases/Resultado.php');
require_once('../clases/Porcentaje.php');
require_once('../clases/ArrayUtils.php');
require_once('../clases/AdministradorCorreo.php');
require_once('FrasesRepositorio.php');
require_once('UsuariosRepositorio.php');
require_once('../clases/Mes.php');
require_once('TareasComentariosRepositorio.php');
require_once('CursosRepositorio.php');

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
                                U.empresa_id, E.nombre, plantilla, M.plantilla_id, numero_plantilla
                                FROM minutas M
                                    INNER JOIN usuarios U ON U.id = M.usuario_id
                                    INNER JOIN empresas E ON E.id = U.empresa_id";
    }

    public function insertar(Minuta $modelo,$usuario)
    {
        if($modelo->plantillaId=="")
            $modelo->plantillaId=null;
        
        $resultado = $this->calcularId('id','minutas');
        if($resultado->mensajeError=='')
        {
            $id = $resultado->valor;
            $numeroPlantilla = 0;
            if($modelo->plantilla==1)
            {
                $resultado = $this->calcularId('numero_plantilla','minutas');
                if($resultado->mensajeError=='')
                {
                    $numeroPlantilla = $resultado->valor;
                }
            }
            
            
            $color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
            
            $consulta = "INSERT INTO minutas(id, titulo, descripcion, fecha_alta, usuario_id, terminada, fecha_finalizacion, fecha_modificacion, color, plantilla, plantilla_id, numero_plantilla)
                        VALUES(?, ?, ?, NOW(), ?, 0, ?, NOW(), ?, ?, ?, ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('ississiii', $id, $modelo->titulo,$modelo->descripcion, $usuario->id,  $modelo->fechaTermino,$color, $modelo->plantilla, $modelo->plantillaId, $numeroPlantilla))
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
            if(isset($criteriosSeleccion->plantilla) && $criteriosSeleccion->plantilla!="")
                array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'M','campo'=>'plantilla','valor'=>$criteriosSeleccion-> plantilla]);
           
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
                    if($sentencia->bind_result($id, $descripcion, $titulo, $fechaAlta, $usuarioId, $terminada, $fechaTermino, $fechaModificacion,$usuarioNombre, $usuarioApellido, $total, $terminadas, $acuerdos, $participantes, $color, $empresaId, $empresaNombre, $plantilla, $plantillaId, $numeroPlantilla))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id,$descripcion, $titulo,$fechaAlta, $usuarioId, $terminada, $fechaTermino,$fechaModificacion,$usuarioNombre, $usuarioApellido, $total, $terminadas, $acuerdos, $participantes, $color, $empresaId, $empresaNombre, $plantilla, $plantillaId, $numeroPlantilla);
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
    
    public function consultarTodas($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        $filtros = array();
        $where='';
      
        
        $consulta = $this->consultaBase . $where . " ORDER BY titulo";
        
        
        
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $descripcion, $titulo, $fechaAlta, $usuarioId, $terminada, $fechaTermino, $fechaModificacion,$usuarioNombre, $usuarioApellido, $total, $terminadas, $acuerdos, $participantes, $color, $empresaId, $empresaNombre, $plantilla, $plantillaId, $numeroPlantilla))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id,$descripcion, $titulo,$fechaAlta, $usuarioId, $terminada, $fechaTermino,$fechaModificacion,$usuarioNombre, $usuarioApellido, $total, $terminadas, $acuerdos, $participantes, $color, $empresaId, $empresaNombre, $plantilla, $plantillaId, $numeroPlantilla);
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
                    if($sentencia->bind_result($id, $descripcion,$titulo,$fechaAlta, $usuarioId, $terminada, $fechaTermino,$fechaModificacion,$usuarioNombre, $usuarioApellido, $total, $terminadas,$acuerdos, $participantes, $color, $empresaId, $empresaNombre, $plantilla, $plantillaId, $numeroPlantilla))
                    {
                        if($sentencia->fetch())
                        {
                            $minuta = $this->crearRegistro($id,$descripcion,$titulo, $fechaAlta, $usuarioId, $terminada, $fechaTermino, $fechaModificacion,$usuarioNombre, $usuarioApellido, $total, $terminadas,$acuerdos, $participantes, $color, $empresaId, $empresaNombre, $plantilla, $plantillaId, $numeroPlantilla);
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
            $resultado = $this->eliminarUsuarios($llaves->id);
            if($resultado->correcto())
            {
                 $resultado = $this->eliminarComentariosMinuta($llaves->id);
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

    private function crearRegistro($id, $titulo,$descripcion,$fechaAlta, $usuarioId, $terminada, $fechaFinalizacion,$fechaModificacion,$usuarioNombre, $usuarioApellido, $total, $terminadas, $acuerdos, $participantes, $color, $empresaId, $empresaNombre, $plantilla, $plantillaId, $numeroPlantilla)
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
            'empresaNombre' => $empresaNombre,
            'plantilla' => $plantilla,
            'plantillaId' => $plantillaId,
            'numeroPlantilla' => $numeroPlantilla
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
    
    public function asignarUsuario($minutaId, $tareaId, $id, $usuarioId)
    {
        $resultado = new Resultado();
        
        $consulta = " UPDATE minutas_tareas_responsables 
            SET usuario_id = ?
            WHERE minuta_id = ?
                AND tarea_id = ?
                AND id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("iiii", $usuarioId, $minutaId, $tareaId, $id))
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
            (SELECT count(*) FROM minutas_tareas_comentarios MTC WHERE MTC.minuta_id = T.minuta_id AND MTC.tarea_id = T.id) numeroComentarios, T.tipo, orden, usuario_finalizacion_id
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
                    if ($sentencia->bind_result($minutaId,$minutaTitulo,$minutaColor,$id, $titulo, $fechaAlta, $fechaModificacion, $fechaCompromiso, $fechaFinalizacion, $terminada, $usuarioId, $usuarioNombre, $usuarioApellido, $numeroComentarios, $tipo, $orden, $usuarioFinalizacionId))
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
                                'tipo' => $tipo,
                                'orden' => $orden,
                                'usuarioFinalizacionId' => $usuarioFinalizacionId
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
    
    public function consultarTareasUsuarios($minutaId)
    {
        $resultado = new Resultado();
        $tareas = array();
        $consulta = "SELECT  UT.usuario_id, U.nombre, U.apellido,
                    (
                    	SELECT COUNT(*)
                    	FROM minutas_tareas T1
                    		INNER JOIN minutas M1 ON M1.id = T1.minuta_id
                    		INNER JOIN minutas_tareas_responsables UT1 ON UT1.minuta_id = T1.minuta_id AND UT1.tarea_id = T1.id
                    		INNER JOIN usuarios U1 ON UT1.usuario_id = U1.id
                    	WHERE T1.tipo = 'T' 
                    	AND T1.minuta_id  = T.minuta_id
                        AND U1.id = U.id) asignadas,
                    (
                    	SELECT COUNT(*)
                    	FROM minutas_tareas T1
                    		INNER JOIN minutas M1 ON M1.id = T1.minuta_id
                    		INNER JOIN minutas_tareas_responsables UT1 ON UT1.minuta_id = T1.minuta_id AND UT1.tarea_id = T1.id
                    		INNER JOIN usuarios U1 ON UT1.usuario_id = U1.id
                    	WHERE T1.tipo = 'T' 
                    	AND T1.minuta_id  = T.minuta_id
                        AND U1.id = U.id
                    	AND T1.terminada = 1) terminadas
                    FROM minutas_tareas T
                    	INNER JOIN minutas M ON M.id = T.minuta_id
                        INNER JOIN minutas_tareas_responsables UT ON UT.minuta_id = T.minuta_id AND UT.tarea_id = T.id
                        INNER JOIN usuarios U ON UT.usuario_id = U.id
                    WHERE tipo = 'T'
                    	AND T.minuta_id  = ?
                    GROUP BY UT.usuario_id, U.nombre, U.apellido
                    ORDER BY U.nombre, U.apellido";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$minutaId))
            {
                if($sentencia->execute())
                {
                    //$valores = array();
                    if ($sentencia->bind_result($usuarioId, $usuarioNombre, $usuarioApellido, $asignadas, $terminadas))
                    //if ($sentencia->bind_result($valores))
                    {
                        while($sentencia->fetch())
                        {
                            $tarea= (object) [
                                'usuarioId' => $usuarioId,
                                'usuarioNombre' => $usuarioNombre,
                                'usuarioApellido' => $usuarioApellido,
                                'asignadas' => $asignadas,
                                'terminadas' => $terminadas,
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
    
    public function remplazarUsuarioTareas($origenUsuarioId,$destinoUsuarioId)
    {
        $resultado = new Resultado();
        $usuario = (object)["id" => $origenUsuarioId];
        $criteriosSeleccion = (object)["terminada" => "0"];
        $resultado = $this->consultarMisTareas($usuario, $criteriosSeleccion);
        $tareas = array();
        if($resultado->correcto())
        {
            $tareas = $resultado->valor;
            $resultado = $this->consultarRegistrosResponsables($origenUsuarioId);
            if($resultado->correcto())
            {
                $registros = $resultado->valor;
                for($i = 0; $i < count($registros); $i++)
                {
                    $registro = $registros[$i];
                    $resultado = $this->asignarUsuario($registro->minutaId, $registro->tareaId, $registro->id, $destinoUsuarioId);
                    if($resultado->error())
                    {
                        break;
                    }
                }
                $resultado->valor = $tareas;
            }
        }
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
             WHERE T.tipo = 't' AND M.plantilla!=1 AND $usuario->id IN (SELECT usuario_id FROM minutas_tareas_responsables MTR WHERE MTR.minuta_id = M.id AND MTR.tarea_id = T.id) 
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
    
    private function consultarRegistrosResponsables($usuarioId)
    {
        $resultado = new Resultado();
        $responsables = array();
        $consulta = "SELECT R.minuta_id, R.tarea_id, R.id 
                    FROM minutas_tareas_responsables R
                    	INNER JOIN minutas_tareas T ON T.id = R.tarea_id AND T.minuta_id = R.minuta_id
                     WHERE T.terminada = 0 AND R.usuario_id = ? ";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            
            if($sentencia->bind_param("i",$usuarioId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($minutaId, $tareaId, $id))
                    {
                        while($sentencia->fetch())
                        {
                            $responsable= (object) [
                                'minutaId' =>  $minutaId,
                                'tareaId' =>  $tareaId,
                                'id' => $id,
                            ];
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
                               
                                $resultado = $this->enviarNotificacionDuenoTareaTerminada($usuario,$minutaId, $tarea);
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
        $consulta =  "SELECT count(*) AS id FROM minutas_tareas WHERE minuta_id = ? AND terminada != 1 AND tipo='t'  ";
        
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
        $consulta =  "SELECT count(*) AS id FROM minutas_tareas WHERE minuta_id = ? AND tipo='t'";
        
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
    
    public function eliminarComentariosMinuta($minutaId)
    {
        $resultado = new Resultado();
        
        $consulta ="DELETE FROM minutas_tareas_comentarios WHERE minuta_id = ?";
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
        if($modelo->tipo=="" || $modelo->tipo==null)
            $modelo->tipo = "t";
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
                        $texto= file_get_contents('../plantillas_correo/notificacion_tarea.html');
                        
                        
                        $texto=  str_replace("@nombreUsuario",$nombreUsuario,$texto);
                        $texto=  str_replace("@accion",$accion,$texto);
                        $texto=  str_replace("@fotoPerfil",$fotoPerfil,$texto);
                        $texto=  str_replace("@nombreMinuta",$minuta->titulo,$texto);
                        $texto=  str_replace("@nombreTarea",$tarea->titulo,$texto);
                        $texto=  str_replace("@fechaVencimiento",$tarea->fechaCompromiso,$texto);
                        
                        $texto=  str_replace("@minutaId",$minuta->id,$texto);
                        $texto=  str_replace("@tareaId",$tarea->id,$texto);
                        $texto=  str_replace("@texto","",$texto);
                        
                        
                        $texto=  str_replace("@frase",$frase->texto,$texto);
                        $texto=  str_replace("@autor",$frase->autor,$texto);
                        
                       
                        
                        $administrador_correo = new AdministradorCorreo();
                        $resultado = $administrador_correo->enviarCorreoUsuarios($tipo,$usuarios,$asunto, $texto, $info, "SAHA: Tareas");
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
                    $texto= file_get_contents('../plantillas_correo/notificacion_tarea.html');
                    
                   
                    
                    $texto=  str_replace("@nombreUsuario",$nombreUsuario,$texto);
                    $texto=  str_replace("@accion",$accion,$texto);
                    $texto=  str_replace("@fotoPerfil",$fotoPerfil,$texto);
                    $texto=  str_replace("@nombreMinuta",$minuta->titulo,$texto);
                    $texto=  str_replace("@nombreTarea",$tarea->titulo,$texto);
                    $texto=  str_replace("@fechaVencimiento",$tarea->fechaCompromiso,$texto);
                    
                    $texto=  str_replace("@minutaId",$minuta->id,$texto);
                    $texto=  str_replace("@tareaId",$tarea->id,$texto);
                    
                    $porcentaje = $minuta->porcentaje;
                    $texto=  str_replace("@texto","<br>Con ésta tarea cumplida, el avance de la minuta es $porcentaje%",$texto);
                    
                    
                    $texto=  str_replace("@frase",$frase->texto,$texto);
                    $texto=  str_replace("@autor",$frase->autor,$texto);
                    
                    
                    
                    $administrador_correo = new AdministradorCorreo();
                    $resultado = $administrador_correo->enviarCorreoUsuarios($tipo,$usuarios,$asunto, $texto, $info, "SAHA: Tareas");
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
                    $texto= file_get_contents('../plantillas_correo/notificacion_minuta.html');
                    
                    
                    $texto=  str_replace("@nombreUsuario",$nombreUsuario,$texto);
                    $texto=  str_replace("@accion",$accion,$texto);
                    $texto=  str_replace("@fotoPerfil",$fotoPerfil,$texto);
                    $texto=  str_replace("@nombreMinuta",$minuta->titulo,$texto);
                    //$texto=  str_replace("@nombreTarea","",$texto);
                    //$texto=  str_replace("@fechaVencimiento",$tarea->fechaCompromiso,$texto);
                    
                    $texto=  str_replace("@minutaId",$minuta->id,$texto);
                    //$texto=  str_replace("@tareaId",$tarea->id,$texto);
                    
                    
                    $texto=  str_replace("@frase",$frase->texto,$texto);
                    $texto=  str_replace("@autor",$frase->autor,$texto);
                    
                    
                    
                    $administrador_correo = new AdministradorCorreo();
                    $resultado = $administrador_correo->enviarCorreoUsuarios($tipo,$usuarios,$asunto, $texto, $info, "SAHA: Tareas");
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
    
    function copiarUsuarios($usuario,$minutaId, $usuarios)
    {
           
        $resultado = new Resultado();
        
        $resultado = $this->consultarUsuariosNoAsignadosMinuta($minutaId,$usuarios);
        
        if($resultado->correcto())
        {
            $usuariosCompartir = $resultado->valor;
//             $resultado  = $this->eliminarUsuarios($minutaId);
//             if($resultado->correcto())
//             {
                $resultado  = $this->insertarUsuarios($minutaId,$usuarios);
                if($resultado->correcto())
                {
                    $resultado = $this->actualizarMinuta($minutaId);
                    if($resultado->correcto())
                    {
                        $resultado = $this->enviarNotificacionUsuariosMinuta($usuario,$minutaId, $usuariosCompartir);
                    }
                }
            //}
        }
        return $resultado;
            
    }
    
    function copiarTareas($usuario,$minutaId, $tareas, $fechaAltaMinuta)
    {
        $tareasComentariosRepositorio = new TareasComentariosRepositorio($this->conexion);
        for ($i = 0; $i < count($tareas); $i++) 
        {
            $modelo = $tareas[$i];
            
            
            if($modelo->tipo=="" || $modelo->tipo==null)
                $modelo->tipo = "t";
            
            $fechaCompromiso = "";
            
            if($modelo->tipo=="t")
            {
                if(isset($modelo->fechaCompromiso))
                {
                    if($modelo->fechaCompromiso!="" && $modelo->fechaCompromiso!=null)
                    {
                        if($modelo->terminada==1)
                        {
                            $elementos = explode("/",$modelo->fechaCompromiso);
                            $elementos = array_reverse($elementos);
                            $fechaCompromiso = join("-",$elementos);
                        }
                        else
                        {
                            $fechaAltaMinuta = substr($fechaAltaMinuta, 0, 10);
                            $fechaAltaMinutaDate = date_create_from_format("d/m/Y",$fechaAltaMinuta);
                            $fechaCompromisoTareaDate = date_create_from_format("d/m/Y",$modelo->fechaCompromiso);
                            
                            $diff = (array) date_diff($fechaAltaMinutaDate, $fechaCompromisoTareaDate);
                            $diasDiferencia = $diff["days"];
                            
                           
                            
                            if($diasDiferencia>=0)
                            {
                                $diaActual = date_create_from_format('Y-m-d', date('Y-m-d'));
                                if($diasDiferencia>0)
                                    date_add($diaActual, date_interval_create_from_date_string("$diasDiferencia days"));
                                $fechaCompromiso = date_format($diaActual, "Y-m-d");
                                
                             //   var_dump($diaActual);
                            }
                            
                            
                        }
                    }
                }
            }
                
            $fechaFinalizacion = "";
            if($modelo->fechaFinalizacion!="")
            {
                $elementos = explode(" ",$modelo->fechaFinalizacion);
                
                $fecha = $elementos[0];
                $hora = $elementos[1];
                $elementosFecha = explode("/",$fecha);
                $elementosFecha = array_reverse($elementosFecha);
                $fechaFinalizacion = join("-",$elementosFecha). " " . $hora;
                
                
            }
//             $resultado =  $this->calcularIdTarea($minutaId, "id");
//             if($resultado->correcto())
//             {
//                 $modelo->id =  $resultado->valor;
//                 $resultado =  $this->calcularIdTarea($minutaId,"orden");
//                 if($resultado->correcto())
//                 {
//                     $orden =  $resultado->valor;
                    $consulta = "INSERT INTO minutas_tareas(minuta_id, id, orden, usuario_id, fecha_alta, fecha_modificacion, terminada, titulo,fecha_compromiso, tipo, fecha_finalizacion, usuario_finalizacion_id) " .
                        "VALUE(?, ?, ?, ?, NOW(), NOW(), ?, ?, ?, ?, ?, ?)";
                    if($sentencia = $this->conexion->prepare($consulta))
                    {
                        if($sentencia->bind_param("iiiiissssi",$minutaId, $modelo->id , $modelo->orden, $usuario->id, $modelo->terminada, $modelo->titulo,  $fechaCompromiso, $modelo->tipo, $fechaFinalizacion, $modelo->usuarioFinalizacionId))
                        {
                            if($sentencia->execute())
                            {
                                $sentencia->close();
                                $resultado = $tareasComentariosRepositorio->consultar((object)["minutaId"=>$modelo->minutaId, "tareaId" => $modelo->id]);
                                if($resultado->correcto())
                                {
                                    $comentarios = $resultado->valor;
                                    $resultado = $this->copiarComentarios($minutaId, $modelo->id, $comentarios);
                                    if($resultado->correcto())
                                    {
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
                                }
                            }
                            else
                            {
                                $resultado->mensajeError = __FUNCTION__.". Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
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
                        $resultado->mensajeError = __FUNCTION__.". Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
                        break;
                    }
//                 }
//             }
                   
        }
        return $resultado;
        
    }
    
    private function copiarComentarios($minutaId, $tareaId, $comentarios)
    {
        $resultado = new Resultado();
        //$repositorio = new TareasComentariosRepositorio($this->conexion);
        for ($i = 0; $i < count($comentarios); $i++)
        {
            $modelo = $comentarios[$i];
            $resultado = $this->calcularId('id','minutas_tareas_comentarios');
            if($resultado->correcto())
            {
                $id = $resultado->valor;
                $consulta = "INSERT INTO minutas_tareas_comentarios(id, minuta_id, tarea_id, usuario_id, comentario, fecha)VALUES(?, ?, ?, ?, ?, NOW())";
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if($sentencia->bind_param('iiiis', $id, $minutaId, $tareaId, $modelo->usuarioId, $modelo->comentario))
                    {
                        if($sentencia->execute())
                        {
                            $sentencia->close();
//                             $llaves= (object)
//                             [
//                                 'minutaId'=> $modelo->minutaId,
//                                 'tareaId'=> $modelo->tareaId
//                             ];
//                             $repositorio = new MinutasRepositorio($this->conexion);
//                             $resultado = $repositorio->consultarTareaPorLlaves($llaves);
//                             if($resultado->correcto())
//                             {
//                                 $tarea =  $resultado->valor;
//                                 $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
//                                 $resultado = $this->consultaUsuariosComentario($tarea->minutaId,$tarea->id);
//                                 if($resultado->correcto())
//                                 {
//                                     $usuarios = $resultado->valor;
                                    
//                                     if(!$usuariosRepositorio->existeUsuarioArreglo($tarea->usuarioId,$usuarios))
//                                     {
//                                         $resultado = $usuariosRepositorio->consultarPorLLaves((object)["id"=>$tarea->usuarioId]);
//                                         if($resultado->correcto())
//                                             array_push($usuarios, $resultado->valor);
//                                     }
                                    
//                                     if($usuario->supervisor1Id!=null)
//                                     {
//                                         if(!$usuariosRepositorio->existeUsuarioArreglo($usuario->supervisor1Id,$usuarios))
//                                         {
//                                             $resultado = $usuariosRepositorio->consultarPorLLaves((object)["id"=>$usuario->supervisor1Id]);
//                                             if($resultado->correcto())
//                                                 array_push($usuarios, $resultado->valor);
//                                         }
//                                     }
                                    
                                    
//                                     $usuariosRepositorio->eliminarUsuarioArreglo($usuario->id,$usuarios);
                                    
//                                     $resultado = $this->enviarNotificacionComentario($usuario,$usuarios,$tarea, $modelo->comentario);
//                                     if($resultado->correcto())
//                                     {
//                                         $resultado->valor = $modelo->id;
//                                     }
//                                 }
//                             }
                        }
                        else
                        {
                            $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
                            break;
                        }
                    }
                    else
                    {
                        $resultado->mensajeError = 'Falló el enlace de parámetros';
                        break;
                    }
                }
                else
                {
                    $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' .$this->conexion->error;
                    break;
                }
            }
        }
        
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
    
    public function copiar($usuario,$llaves,$titulo)
    {
        ini_set('max_execution_time', 300);
        $this->conexion->autocommit(FALSE);
        
        $resultado = $this->consultarPorLlaves($llaves,true);
        if($resultado->correcto())
        {
            $modelo = $resultado->valor;
            $modelo->titulo = $titulo;
            $resultado = $this->copiarEncabezado($usuario,$modelo);
            if($resultado->correcto())
            {
                $copia = $resultado->valor;
                $resultado = $this->copiarUsuarios($usuario,$copia->id, $modelo->usuarios);
                if($resultado->correcto())
                {
                    $resultado =  $this->copiarTareas($usuario,$copia->id, $modelo->tareas, $modelo->fechaAlta);
                     if($resultado->correcto())
                     {
                         $resultado->valor = $copia->id;
                     }
                 }
            }
        }
        
        
        if($resultado->correcto())
        {
            $this->conexion->commit();
            
        }
        else
            $this->conexion->rollback();
            return $resultado;
    }
    
    public function copiarEncabezado($usuario,$modelo)
    {
        $resultado =  $this->calcularId("id","minutas");
        if($resultado->mensajeError=="")
        {
            $fechaFinalizacion = null;
            if($modelo->fechaFinalizacion!=null)
            {
                $elementos = explode('/', $modelo->fechaFinalizacion);
                if(count($elementos)==3)
                {
                    $dia = $elementos[0];
                    $mes = $elementos[1];
                    $ano = $elementos[2];
                    $fechaFinalizacion = date("Y-m-d H:i:s", mktime(10, 30, 0, $mes, $dia, $ano));
                }
            }
            $plantillaId = $modelo->id;
            $modelo->id = $resultado->valor;
            $consulta = "INSERT INTO minutas(id, titulo, descripcion, fecha_alta, fecha_modificacion, usuario_id, terminada, fecha_finalizacion, acuerdos, participantes, color, plantilla, plantilla_id) " .
                "VALUE(?, ?, ?, NOW(), NOW(), ?, ?, ?, ?, ?, ?, 0, ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if( $sentencia->bind_param("issiissssi", $modelo->id, $modelo->titulo,$modelo->descripcion, $usuario->id, $modelo->terminada, $fechaFinalizacion, $modelo->acuerdos, $modelo->participantes, $modelo->color, $plantillaId))
                {
                    if($sentencia->execute())
                    {
                        $sentencia->close();
                        $resultado->valor = $modelo;
                    }
                    else
                    {
                        $resultado->mensajeError = __FUNCTION__. ". Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                    }
                }
                else
                    $resultado->mensajeError = __FUNCTION__. ". Falló el enlace de parámetros";
            }
            else
                $resultado->mensajeError = __FUNCTION__. ". Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
                
        }
        return $resultado;
    }
    
    public function generarAutoMinuta($usuario, $criteriosSeleccion)
    {
        $resultado = new Resultado();
        
        $texto= file_get_contents('../plantillas_texto/minuta_del_mes.txt');
        
        $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
        $resultado = $usuariosRepositorio->consultarPorLLaves((object)["id"=>$criteriosSeleccion->administradorId]);
        if($resultado->correcto())
        {
            
            $inicial = substr($resultado->valor->nombre,0,1) . ".";
            $nombreAdministrador = $inicial . " " . $resultado->valor->apellido;
            
           
            
            $dia = date("d");
            $mes = date("m");
            $ano = date("Y");
            
            $anoSAHA = 0;
            $mesSAHA = 0;
            if($dia < 28)
            {
                $fecha = new DateTime();
                $fecha->setDate($ano,$mes,1);
                $fecha->sub(new DateInterval('P1M'));
                
                $anoSAHA = $fecha->format("Y");
                $mesSAHA = $fecha->format("m");
            }
            else
            {
                $anoSAHA = $ano;
                $mesSAHA = $mes;
            }
            
            
            
            $fecha = new DateTime();
            $fecha->setDate($anoSAHA,$mesSAHA,1);
            $fecha->sub(new DateInterval('P1M'));
            
            $anoAnteriorSAHA = $fecha->format("Y");
            $mesAnterior = $fecha->format("m");
            
            $nombreMes = \Mes::getNombre($mesSAHA);
            $nombreMesAnteriorSAHA = \Mes::getNombre($mesAnterior);
            
            $fechaReporte = new DateTime();
            $fechaReporte->setDate($ano,$mes,1);
            $nombreMesReporte = \Mes::getNombre($mes);
            
            $fechaReporteAnterior = new DateTime();
            $fechaReporteAnterior->setDate($ano,$mes,1);
            $fechaReporteAnterior->sub(new DateInterval('P1M'));
            $anoReporteAnterior = $fechaReporteAnterior->format("Y");
            $mesReporteAnterior = $fechaReporteAnterior->format("m");
            $nombreMesReporteAnterior = \Mes::getNombre($mesReporteAnterior);
            
         
            
            $criteriosSeleccionActual= (object) [
                'mes' =>  $mesSAHA,
                'ano' =>  $anoSAHA,
                "empresaId" => $criteriosSeleccion->empresaId
            ];
            
            $fecha = new DateTime();
            $fecha->setDate($anoSAHA,$mesSAHA,1);
            $fecha->sub(new DateInterval('P1M'));
            
            $anoAnteriorSAHA = $fecha->format("Y");
            $mesAnterior = $fecha->format("m");
            $criteriosSeleccionAnterior= (object) [
                'mes' =>   $mesAnterior,
                'ano' => $anoAnteriorSAHA,
                "empresaId" => $criteriosSeleccion->empresaId
            ];
            
            $repositorioEmpresas = new EmpresasRepositorio($this->conexion);
            $resultado  = $repositorioEmpresas->consultarPorLlaves((object)["id" => $criteriosSeleccion->empresaId]);
            if($resultado->correcto())
            {
                $empresa = $resultado->valor;
            
                $texto = $this->resultadoGlobalSAHA($texto, $empresa, $usuario, $criteriosSeleccionActual, $criteriosSeleccionAnterior);
                $texto = $this->resultadoSedesSAHA($texto, $usuario, $criteriosSeleccionActual, $criteriosSeleccionAnterior);
                $texto = $this->resultadoDepartamentosSAHA($texto, $usuario, $criteriosSeleccionActual, $criteriosSeleccionAnterior);
                $texto = $this->resultadoUsuariosSAHA($texto, $usuario, $criteriosSeleccionActual, $criteriosSeleccionAnterior);
                $texto = $this->resultadoGlobalCAVI($texto, $empresa, $usuario, $criteriosSeleccion);
                $texto = $this->resultadoSedesCAVI($texto, $usuario, $criteriosSeleccion);
                $texto = $this->resultadoDepartamentosCAVI($texto, $usuario, $criteriosSeleccion);
                $texto = $this->resultadosSIVAH($texto, $usuario, $criteriosSeleccion);
                $texto = $this->resultadoAnalisisRiesgo($texto,$empresa, $usuario, $criteriosSeleccion);
                
                
                $texto=  str_replace("@nombreMesReportado",$nombreMesReporte . " " . $ano,$texto);
                $texto=  str_replace("@nombreMesActual",$nombreMes . " " . $anoSAHA,$texto);
                $texto=  str_replace("@nombreMesAnteriorSAHA",$nombreMesAnteriorSAHA. " ". $anoAnteriorSAHA,$texto);
                $texto=  str_replace("@nombreMesReporteAnterior",$nombreMesReporteAnterior. " ". $anoReporteAnterior,$texto);
                $texto=  str_replace("@administrador",$nombreAdministrador,$texto);
                
                
                
                $resultado->valor = $texto;
            }
        }
        
       
        
        return $resultado;
    }
    
    function resultadoAnalisisRiesgo($texto, $empresa, $usuario, $criteriosSeleccion)
    {
        $analisisRiesgo="";
       
        if($empresa->analisisRiesgoMinutaId!=null && $empresa->analisisRiesgoMinutaId!="")
        {
            $resultado = $this->consultarTareasUsuarios($empresa->analisisRiesgoMinutaId);
            if($resultado->correcto())
            {
                $usuarios = $resultado->valor;
                $analisisRiesgo = "Se verifica el avance del Análisis de Riesgo OEA (ISO 31010) En el cual se desglosa el avance de personal que cuenta con acciones asignadas en la minuta: ";
                
                for ($i = 0; $i < count($usuarios); $i++) 
                {
                    $usuarioMinuta = $usuarios[$i];
                    $textoAsignadas = $usuarioMinuta->asignadas == 1 ? "acción asignada" : "acciones asignadas";
                    $textoTerminadas = $usuarioMinuta->terminadas == 1 ? "acción concluida" : "acciones concluidas";
                    
                    $analisisRiesgo .= $usuarioMinuta->usuarioNombreCompleto. " " . $usuarioMinuta->asignadas . " " . $textoAsignadas . " - " . $usuarioMinuta->terminadas . " "  .$textoTerminadas ;
                    
                    if($i <  count($usuarios) - 1)
                        $analisisRiesgo .= ", ";
                }
                
                $analisisRiesgo .= ". Se solicita a todos los usuarios verificar las tareas asignadas en el análisis de riesgo e indicar en el globo de mensaje de la tarea el estado del mismo así como indicar las acciones que se encuentran cerradas marcarlas como terminadas.";
                
            }
        }
        $texto=  str_replace("@analisisRiesgo",$analisisRiesgo,$texto);
        return $texto;
    }
    
    public function graficaAvance($rows, $xField, $titulo)
    {
        $showInLegend = true;
        
        $categories = array();
        $data = array();
        
        $data = array();
        
        $data1 = array();
        $data2 = array();
        $data3 = array();
        
        //$fecha = new DateTime();
        // $mesActual = (int)$fecha->format("m");
        
        for ($i = 0; $i < count($rows); $i++)
        {
            $row = $rows[$i];
            
           
            
            $newRow1= (object) [
                'name' =>  $row->$xField,
                'y' => (float)$row->asignadas,
                // 'color' => "#3c8dbc"
            ];
            
            $newRow2= (object) [
                'name' =>  $row->$xField,
                'y' => (float)$row->terminadas,
                //'color' => "#f39c12"
            ];
         
            
            array_push($categories, $row->$xField);
            array_push($data1, $newRow1);
            array_push($data2, $newRow2);
            // array_push($data3, $newRow3);
        }
        
        $yAxis = (object) [ 'title' => (object) [ 'text'=> ""]];
        
        //         $yAxis->min= 0;
        //         $yAxis->max= 100;
        //         $yAxis->tickInterval= 10;
        
        
        
        $rotacion = 0;
        if(count($rows)>=10)
            $rotacion = -90;
            
            
            
            $highchart = (object)
            [
                'chart' => (object) [ 'type' => "column"],
                'title' => (object) [ 'text'=> $titulo],
                'credits' => (object) ['enabled' => false],
                'xAxis' => (object) [ 'categories' => $categories],
                'plotOptions' => (object)
                [
                    'column'=> (object)[
                        // 'stacking' => 'normal',
                        'dataLabels'=>(object)
                        [
                            'enabled'=>true,
                            //                         'crop'=>false,
                            //                         'overflow' =>'none',
                            //                         "inside"=> false,
                            'color'=> 'black',
                            'style'=> (object)
                            [
                                'fontSize' => 10,
                                'textOutline' => '0px'
                            ],
                            //                         'rotation' => $rotacion,
                            //                         'format'=>"{point.y:.1f} %",
                            // 'format'=>"{point.y} %",
                            'verticalAlign' => 'bottom'
                            
                        ]
                    ]
                ],
                'yAxis' => $yAxis,
                'series' => array(
                    (object) ['name' => "Asignados", 'data' => $data1,  'showInLegend' => $showInLegend, "color"=>"#4575c3"],
                    (object) ['name' => "Terminadas", 'data' => $data2,  'showInLegend' => $showInLegend, "color"=>"#fb7535"],
                    //(object) ['name' => "En proceso de validación", 'data' => $data3,  'showInLegend' => $showInLegend, "color"=>"#919191"]
                )
            ];
            
            $chartURL = getHightchartsURL($highchart);
            return $chartURL;
            
            //  return 'ok';
            
    }
    
    function resultadoGlobalCAVI($texto, $empresa, $usuario, $criteriosSeleccion)
    {
        
        $criteriosSeleccion= (object) [
            'fechaInicial' =>  $empresa->fechaInicioTemporada,
            'fechaFinal' =>  date("d/m/Y"),
            "empresaId" => $empresa->id
        ];
        
        $repositorio = new CursosRepositorio($this->conexion);
        $criteriosSeleccionAprovechamiento = clone $criteriosSeleccion;
        $criteriosSeleccionAprovechamiento->tipoReporte = TipoReporte::CAPACITACION_INICIADA;
        $resultado = $repositorio->consultarResultados($usuario, $criteriosSeleccionAprovechamiento);
        $aprovechamiento=0;
        if($resultado->correcto())
        {
            if(count($resultado->valor)>0)
            {
                $registro = $resultado->valor[0];
                $aprovechamiento=  $registro->porcentaje;
            }
        }
        
        $criteriosSeleccionAvance = clone $criteriosSeleccion;
        $criteriosSeleccionAvance->tipoReporte = TipoReporte::TODOS;
        $resultado = $repositorio->consultarResultados($usuario, $criteriosSeleccionAvance);
        $avance=0;
        if($resultado->correcto())
        {
            if(count($resultado->valor)>0)
            {
                $registro = $resultado->valor[0];
                $avance=  $registro->porcentajeAvance;
            }
        }
        
        $texto=  str_replace("@avanceCAVI",$avance,$texto);
        $texto=  str_replace("@aprovechamientoCAVI",$aprovechamiento,$texto);
        return $texto;
    }
    
    
    function resultadoSedesSAHA($texto, $usuario, $criteriosSeleccionActual, $criteriosSeleccionAnterior)
    {
        $repositorio = new EvidenciasRepositorio($this->conexion);
        $resultado = $repositorio->consultarPorcentajesSedes($usuario, $criteriosSeleccionActual);
        if($resultado->correcto())
        {
            $sedes = $resultado->valor;
            $resultadoSedes = "";
            for($i = 0; $i < count($sedes); $i++)
            {
                $sede = $sedes[$i];
                $resultadoSedes .= $sede->nombre . " - " . $sede->porcentajeCumplimiento . "% de cumplimiento con " . $sede->porcentajeJustificadas . "% Justificadas";
                
                if($i <  count($sedes) - 1)
                    $resultadoSedes .= ", ";
            }
            $texto=  str_replace("@resultadoSedesSAHA",$resultadoSedes,$texto);
        }
        else
            $texto=  str_replace("@resultadoSedesSAHA",$resultado->mensajeError,$texto);
        return $texto;
    }
    
    function resultadoSedesCAVI($texto, $usuario, $criteriosSeleccion)
    {
        $repositorio = new CursosRepositorio($this->conexion);
        $criteriosSeleccion->tipoReporte = TipoReporte::TODOS;
        $resultado = $repositorio->consultarResultadosSedes($usuario, $criteriosSeleccion);
        if($resultado->correcto())
        {
            $sedes = $resultado->valor;
            $resultadoSedes = "";
            for($i = 0; $i < count($sedes); $i++)
            {
                $sede = $sedes[$i];
                $resultadoSedes .= $sede->nombre . " - " . $sede->porcentajeAvance . "%";
                
                if($i <  count($sedes) - 1)
                    $resultadoSedes .= ", ";
            }
            $texto=  str_replace("@resultadoSedesCAVI",$resultadoSedes,$texto);
        }
        else
            $texto=  str_replace("@resultadoSedesCAVI",$resultado->mensajeError,$texto);
        return $texto;
    }
    
    function resultadosSIVAH($texto, $usuario, $criteriosSeleccion)
    {
        $repositorio = new AuditoriasRepositorio($this->conexion);
        $resultado = $repositorio->consultarAuditoriasRecientesEmpresa($criteriosSeleccion->empresaId);
        if($resultado->correcto())
        {
            $auditorias = $resultado->valor;
            if(count($auditorias)>0)
            {
                $resultado = $repositorio->consultarAuditorias($auditorias);
                if($resultado->correcto())
                {
                    $auditorias = $resultado->valor;
                    if(count($auditorias)>0)
                    {
                        $textoSIVAH = "@administrador comenta los resultados de las auditorías recientes incluyendo los porcentajes obtenidos en la auditoría y el avance de cierre, en resumen se tiene: @resultadoSedesSIVAH
Se verifica el avance de cierre de auditoría por departamento con los siguientes resultados: @resultadoDepartamentosSIVAH
@administrador verifica y comenta con comité de seguridad la gráfica de avance de cierre de auditoría por usuario, donde se observan usuarios que tienen hallazgos asignados derivados de la auditoría y su cierre: @resultadoUsuariosSIVAH
@resultadoUsuarios100SIVAH"; 
                        $texto=  str_replace("@resultadoSIVAH",$textoSIVAH,$texto);
                        
                        $texto = $this->resultadoSedesSIVAH($texto, $usuario, $criteriosSeleccion, $auditorias);
                        $texto = $this->resultadoDepartamentosSIVAH($texto, $usuario, $criteriosSeleccion, $auditorias);
                        $texto = $this->resultadoUsuariosSIVAH($texto, $usuario, $criteriosSeleccion, $auditorias);
                    }
                    else
                        $texto=  str_replace("@resultadoSIVAH","",$texto);
                }
                else
                    $texto=  str_replace("@resultadoSIVAH","consultarAuditorias ". $resultado->mensajeError,$texto);
            }
            else
                $texto=  str_replace("@resultadoSIVAH","",$texto);
        }
        else 
            $texto=  str_replace("@resultadoSIVAH","consultarAuditoriasRecientesEmpresa " .$resultado->mensajeError,$texto);
        return $texto;
       
    }
    
    function resultadoSedesSIVAH($texto, $usuario, $criteriosSeleccion, $auditorias)
    {
        $resultadoSedes = "";
        for($i = 0; $i < count($auditorias); $i++)
        {
            $sede = $auditorias[$i];
            $resultadoSedes .= $sede->sedeNombre . " auditado en ". $sede->fecha ." - Puntuación " . $sede->puntuacion . "% y un ". $sede->porcentajeAvance ."% de avance en el cierre de auditoría";
            
            if($i <  count($auditorias) - 1)
                $resultadoSedes .= ", ";
        }
        $texto=  str_replace("@resultadoSedesSIVAH",$resultadoSedes,$texto);
        return $texto;
    }
    
    function resultadoDepartamentosSIVAH($texto, $usuario, $criteriosSeleccion, $auditorias)
    {
       
        $repositorio = new AuditoriasRepositorio($this->conexion);
        $departamentos = array();
        for($i = 0; $i < count($auditorias); $i++)
        {
            $auditoria = $auditorias[$i];
            $resultado = $repositorio->consultarHallazgosDepartamento($auditoria->id);
          
            if($resultado->correcto())
            {
                $departamentos = array_merge($departamentos, $resultado->valor);
            }
        }
        
        $departamentos = ArrayUtils::groupBySUM("nombre", "total,validadas,proceso", $departamentos);
        $departamentos =  ArrayUtils::orderBy($departamentos);
        
        $resultadoDepartamentos = "";
        for($j = 0; $j < count($departamentos); $j++)
        {
            $departamento = $departamentos[$j];
            $textoHallazgos = $departamento->total == 1 ? "hallazgo" : "hallazgos";
            $textoValidadas = $departamento->validadas == 1 ? "se tiene ". $departamento->validadas . " hallazgo validado" : "se tienen ". $departamento->validadas . " hallazgos validados";
            $textoProceso = $departamento->proceso == 0 ? " sin evidencias en proceso de validación": " así como " . $departamento->proceso . " en proceso de validación";
            $resultadoDepartamentos .= $departamento->nombre . " - " . $departamento->total . " " . $textoHallazgos . " de los cuales " . $textoValidadas .  $textoProceso;
            if($j <  count($departamentos) - 1)
                $resultadoDepartamentos .= ", ";
        }
        
        if(count($auditorias) > 1)
        {
            $resultadoDepartamentos .= ". estos resultados engloban los hallazgos de " . count($auditorias) . " sedes auditadas.";
        }
            
        
        $texto=  str_replace("@resultadoDepartamentosSIVAH",$resultadoDepartamentos,$texto);
        return $texto;
    }
    
    function resultadoUsuariosSIVAH($texto, $usuario, $criteriosSeleccion, $auditorias)
    {
        
        $repositorio = new AuditoriasRepositorio($this->conexion);
        $usuarios = array();
        for($i = 0; $i < count($auditorias); $i++)
        {
            $auditoria = $auditorias[$i];
            $resultado = $repositorio->consultarHallazgosUsuarios($auditoria->id);
            
            if($resultado->correcto())
            {
                $usuarios = array_merge($usuarios, $resultado->valor);
            }
        }
        
        $usuarios = ArrayUtils::groupBySUM("nombreCompleto,departamentoNombre", "total,validadas,proceso", $usuarios);
        $usuarios = ArrayUtils::orderByNombreCompleto($usuarios);
        
        $resultadoUsuarios = "";
        $usuarios100 = array();
        for($j = 0; $j < count($usuarios); $j++)
        {
            $usuarioSIVAH = $usuarios[$j];
            $textoHallazgos = $usuarioSIVAH->total == 1 ? $usuarioSIVAH->total . " hallazgo encontrado" :$usuarioSIVAH->total . " hallazgos encontrados";
            $textoValidados = $usuarioSIVAH->validadas == 1 ? " y ". $usuarioSIVAH->validadas . " validado" :  " y ". $usuarioSIVAH->validadas . " validados";
            $resultadoUsuarios .= $usuarioSIVAH->nombreCompleto . " - " . $textoHallazgos . $textoValidados;
            
            if($j <  count($usuarios) - 1)
                $resultadoUsuarios .= ", ";
            
            if($usuarioSIVAH->total > 0 && $usuarioSIVAH->total == $usuarioSIVAH->validadas)   
                array_push($usuarios100, $usuarioSIVAH);
        }
        
       
        
        $texto=  str_replace("@resultadoUsuariosSIVAH",$resultadoUsuarios,$texto);
        
        $resultadoUsuarios100 = "";
        if(count($usuarios100) > 0)
        {
            $resultadoUsuarios100 = "@administrador comenta con comité de seguridad la gráfica de avance de cierre de auditoría por usuario, donde se observan usuarios que concluyeron con el cierre de auditoría: ";
            for($j = 0; $j < count($usuarios100); $j++)
            {
                $usuarioSIVAH = $usuarios100[$j];
                $resultadoUsuarios100 .= $usuarioSIVAH->nombreCompleto . " - " . $usuarioSIVAH->departamentoNombre;
                if($j <  count($usuarios100) - 1)
                    $resultadoUsuarios100 .= ", ";
                
            }
        }
        $texto=  str_replace("@resultadoUsuarios100SIVAH",$resultadoUsuarios100,$texto);
        
        
        return $texto;
    }
    
    function resultadoDepartamentosCAVI($texto, $usuario, $criteriosSeleccion)
    {
        $repositorio = new CursosRepositorio($this->conexion);
        $criteriosSeleccion->tipoReporte = TipoReporte::TODOS;
        $resultado = $repositorio->consultarResultadosDepartamentos($usuario, $criteriosSeleccion);
        if($resultado->correcto())
        {
            $registros = $resultado->valor;
            $contenido = "";
            for($i = 0; $i < count($registros); $i++)
            {
                $registro = $registros[$i];
                $contenido .= $registro->nombre . " - " . $registro->porcentajeAvance . "%";
                
                if($i <  count($registros) - 1)
                    $contenido .= ", ";
            }
            $texto=  str_replace("@resultadoDepartamentosCAVI",$contenido,$texto);
        }
        else
          $texto=  str_replace("@resultadoDepartamentosCAVI",$resultado->mensajeError,$texto);
        return $texto;
    }
    
    function resultadoDepartamentosSAHA($texto, $usuario, $criteriosSeleccionActual, $criteriosSeleccionAnterior)
    {
        $repositorio = new EvidenciasRepositorio($this->conexion);
        $resultado = $repositorio->consultarPorcentajesAreas($usuario, $criteriosSeleccionActual);
        if($resultado->correcto())
        {
            $departamentos = $resultado->valor;
            $resultadoDepartamentos = "";
            for($i = 0; $i < count($departamentos); $i++)
            {
                $departamento = $departamentos[$i];
                $resultadoDepartamentos .= $departamento->nombre . " - " . $departamento->porcentajeCumplimiento . "% de cumplimiento con " . $departamento->porcentajeJustificadas . "% Justificadas";
                
                if($i <  count($departamentos) - 1)
                    $resultadoDepartamentos .= ", ";
            }
            $texto=  str_replace("@resultadoDepartamentosSAHA",$resultadoDepartamentos,$texto);
        }
        else
            $texto=  str_replace("@resultadoDepartamentosSAHA",$resultado->mensajeError,$texto);
        return $texto;
    }
    
    function resultadoUsuariosSAHA($texto, $usuario, $criteriosSeleccionActual, $criteriosSeleccionAnterior)
    {
        $repositorio = new EvidenciasRepositorio($this->conexion);
        $resultado = $repositorio->consultarPorcentajesUsuarios($usuario, $criteriosSeleccionActual);
        if($resultado->correcto())
        {
            $usuarios = $resultado->valor;
            
            $usuariosPendientes = array();
            for($i = 0; $i < count($usuarios); $i++)
            {
                $usuarioSAHA = $usuarios[$i];
                if($usuarioSAHA->porcentajeCumplimiento != 100)
                    array_push($usuariosPendientes, $usuarioSAHA);
            }
            
            $resultadoUsuarios = "";
            if(count($usuariosPendientes)>0)
            {
                $resultadoUsuarios = "Los siguientes usuarios no enviaron o justificaron sus evidencias para llegar al 100% - ";
                for($i = 0; $i < count($usuariosPendientes); $i++)
                {
                    $usuarioSAHA = $usuariosPendientes[$i];
                    $resultadoUsuarios .= $usuarioSAHA->nombreCompleto . " - " . $usuarioSAHA->porcentajePendientes . "% de evidencias faltantes";
                    
                    if($i <  count($usuariosPendientes) - 1)
                        $resultadoUsuarios .= ", ";
                }
            }
            else 
            {
                $inicial = substr($usuario->nombre,0,1) . ".";
                $nombreAdministrador = $inicial . " " . $usuario->apellido;
                
                $texto=  str_replace("@administrador",$nombreAdministrador,$texto);
                $resultadoUsuarios = "En esta ocasión todos los usuarios presentaron en tiempo y forma sus evidencias, ". $nombreAdministrador ." felicita al equipo de trabajo y solicita se mantenga el esfuerzo.";
            }
            
            
            $texto=  str_replace("@resultadoUsuariosPendientesSAHA",$resultadoUsuarios,$texto);
        }
        else
            $texto=  str_replace("@resultadoUsuariosPendientesSAHA",$resultado->mensajeError,$texto);
            return $texto;
    }
    
  
    
    function resultadoGlobalSAHA($texto, $empresa, $usuario, $criteriosSeleccionActual, $criteriosSeleccionAnterior)
    {
        $repositorio = new EvidenciasRepositorio($this->conexion);
        
        
        $texto=  str_replace("@nombreEmpresa",$empresa->nombre,$texto);
        
        $resultado = $repositorio->consultarPorcentajesEvidencias($usuario, $criteriosSeleccionActual);
        $porcentajesActual = null;
        $porcentajesAnterior = null;
        if($resultado->correcto())
        {
            $porcentajesActual = (object)[
                "enviadas" => $resultado->valor[0]->valor + $resultado->valor[2]->valor,
                "pendientes" => $resultado->valor[1]->valor,
                "justificadas" => $resultado->valor[2]->valor
            ];
            $porcentajesActual->total = $porcentajesActual->enviadas + $porcentajesActual->pendientes;
            
            Porcentaje::calcularPorcentaje($porcentajesActual, "enviadas", "total", "porcentajeEnviadas");
            Porcentaje::calcularPorcentaje($porcentajesActual, "justificadas", "total", "porcentajeJustificadas");
            
            $texto=  str_replace("@enviadasActual",$porcentajesActual->porcentajeEnviadas,$texto);
            $texto=  str_replace("@justificadasActual",$porcentajesActual->porcentajeJustificadas,$texto);
        }
        
        $resultado = $repositorio->consultarPorcentajesEvidencias($usuario, $criteriosSeleccionAnterior);
        if($resultado->correcto())
        {
            $porcentajesAnterior = (object)[
                "enviadas" => $resultado->valor[0]->valor + $resultado->valor[2]->valor,
                "pendientes" => $resultado->valor[1]->valor,
                "justificadas" => $resultado->valor[2]->valor
            ];
            $porcentajesAnterior->total = $porcentajesAnterior->enviadas + $porcentajesAnterior->pendientes;
            
            Porcentaje::calcularPorcentaje($porcentajesAnterior, "enviadas", "total", "porcentajeEnviadas");
            Porcentaje::calcularPorcentaje($porcentajesAnterior, "justificadas", "total", "porcentajeJustificadas");
            
            $texto=  str_replace("@enviadasAnterior",$porcentajesAnterior->porcentajeEnviadas,$texto);
            $texto=  str_replace("@justificadasAnterior",$porcentajesAnterior->porcentajeJustificadas,$texto);
        }
        
        $mejorPeorMes = "";
        if($porcentajesActual->porcentajeEnviadas > $porcentajesAnterior->porcentajeEnviadas)
            $mejorPeorMes = " se tuvo un mejor mes";
        else if($porcentajesActual->porcentajeEnviadas < $porcentajesAnterior->porcentajeEnviadas)
            $mejorPeorMes = " se tuvo un peor mes";
        else 
            $mejorPeorMes = " se tuvo un mes similar";
                
        $texto=  str_replace("@mejorPeorMes",$mejorPeorMes,$texto);
                
                
        $conclusionSAHA = "";
        if($porcentajesActual->porcentajeJustificadas > 15)
            $conclusionSAHA = "Se recuerda a Comité de seguridad que a fin de mantener operándonosla adecuadamente el sistema de gestion de la certificación es recomendación del Equipo Handel que las justificaciones se mantengan debajo de 15%, se solicita hacer lo posible a fin de reducir las justificaciones dentro de SAHA.";
        else if($porcentajesActual->porcentajeEnviadas == 100)
            $conclusionSAHA = "Se felicita a comité de seguridad por el cumplimiento del mes y se solicita la ayuda continua para mantener ese resultado de 100% de entregas en los siguientes meses";
        else
            $conclusionSAHA = "Dado que el cumplimiento del mes fue inferior al 100% se solicita a comité de seguridad el apoyo a fin de conseguir en los meses siguientes el 100% de cumplimiento";
        $texto=  str_replace("@conclusionSAHA",$conclusionSAHA,$texto);
        
        $nivelRiesgoSAHA = "";
        if($porcentajesActual->porcentajeEnviadas <= 70)
            $nivelRiesgoSAHA = "alto";
        if($porcentajesActual->porcentajeEnviadas >= 71 && $porcentajesActual->porcentajeEnviadas <= 85)
            $nivelRiesgoSAHA = "medio";
        else
            $nivelRiesgoSAHA = "bajo";
        
        $texto=  str_replace("@nivelRiesgoSAHA",$nivelRiesgoSAHA,$texto);
        
        
        return $texto;
    }
    
}
