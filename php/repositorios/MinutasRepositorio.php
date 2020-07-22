<?php
namespace php\repositorios;

use php\interfaces\IMinutasRepositorio;
use php\modelos\Minuta;
use php\modelos\Resultado;
use php\clases\Porcentaje;

include '../interfaces/IMinutasRepositorio.php';
include '../modelos/Minuta.php';
include 'RepositorioBase.php';
require_once('../clases/Resultado.php');
require_once('../clases/Porcentaje.php');

class MinutasRepositorio extends RepositorioBase implements IMinutasRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = "SELECT M.id, titulo, IFNULL(DATE_FORMAT(M.fecha_alta,'%d/%m/%Y %H:%i:%s'),'') as fecha_alta, usuario_id, terminada, IFNULL(DATE_FORMAT(M.fecha_finalizacion,'%d/%m/%Y %H:%i:%s'),'') as fecha_finalizacion, IFNULL(DATE_FORMAT(M.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'') as fecha_modificacion, U.nombre, U.apellido,
                                (SELECT count(*) FROM minutas_tareas T WHERE T.minuta_id = M.id) total,
                               (SELECT count(*) FROM minutas_tareas T WHERE T.minuta_id = M.id AND T.terminada=1) terminadas
                                FROM minutas M
                                    INNER JOIN usuarios U ON U.id = M.usuario_id";
    }

    public function insertar(Minuta $modelo,$usuario)
    {
        $resultado = $this->calcularId('id','minutas');
        if($resultado->mensajeError=='')
        {
            $id = $resultado->valor;
            
            $consulta = "INSERT INTO minutas(id, titulo, fecha_alta, usuario_id, terminada, fecha_finalizacion, fecha_modificacion)
                        VALUES(?, ?, NOW(), ?, 0, ?, NOW())";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('isis', $id, $modelo->titulo, $usuario->id,  $modelo->fechaTermino))
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

    public function actualizar(Minuta $modelo)
    {
        $resultado = new Resultado();
        $consulta = "UPDATE minutas
                     SET 
                         titulo = ?,
                         fecha_alta = ?,
                         usuario_id = ?,
                         terminada = ?,
                         fecha_termino = ?
                     WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('ssiisi',$modelo->titulo, $modelo->fechaAlta, $modelo->usuarioId, $modelo->terminada, $modelo->fechaTermino ,$modelo->id ))
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
            if(isset($criteriosSeleccion->titulo))
                array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'M','campo'=>'titulo','valor'=>$criteriosSeleccion->titulo]);
            $where = $this->where($filtros);
        }
        $consulta = $this->consultaBase . $where . " ORDER BY UNIX_TIMESTAMP(M.fecha_alta) desc";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $titulo, $fechaAlta, $usuarioId, $terminada, $fechaTermino, $fechaModificacion,$usuarioNombre, $usuarioApellido, $total, $terminadas))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $titulo, $fechaAlta, $usuarioId, $terminada, $fechaTermino,$fechaModificacion,$usuarioNombre, $usuarioApellido, $total, $terminadas);
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
        ' WHERE M.id  = ?';
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('i',$llaves->id))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $titulo, $fechaAlta, $usuarioId, $terminada, $fechaTermino,$fechaModificacion,$usuarioNombre, $usuarioApellido, $total, $terminadas))
                    {
                        if($sentencia->fetch())
                        {
                            $minuta = $this->crearRegistro($id, $titulo, $fechaAlta, $usuarioId, $terminada, $fechaTermino, $fechaModificacion,$usuarioNombre, $usuarioApellido, $total, $terminadas);
                            $resultado->valor = $minuta;
                            
                            $sentencia->close();
                            
                            $resultadoTareas = $this->consultarTareas($minuta->id);
                            if($resultadoTareas->correcto())
                            {
                                $minuta->tareas = $resultadoTareas->valor;
                            }
                            else
                                $resultado->mensajeError = $resultadoTareas->mensajeError;
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
            
        if($resultado->correcto())
        {
            $this->conexion->commit();
            $resultado->valor = $llaves->id;;
        }
        else
            $this->conexion->rollback();
            
        return $resultado;
    }

    private function crearRegistro($id, $titulo, $fechaAlta, $usuarioId, $terminada, $fechaFinalizacion,$fechaModificacion,$usuarioNombre, $usuarioApellido, $total, $terminadas)
    {
        $registro= (object) 
        [
            'id' => $id,
            'titulo' => $titulo,
            'fechaAlta' => $fechaAlta,
            'usuarioId' => $usuarioId,
            'terminada' => $terminada,
            'fechaFinalizacion' => $fechaFinalizacion,
            'fechaModificacion' => $fechaModificacion,
            'usuarioNombre' => $usuarioNombre,
            'usuarioApellido' => $usuarioApellido,
            'total' => $total,
            'terminadas' => $terminadas
        ];
        
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
        $consulta = "SELECT T.id, RTRIM(titulo) titulo, IFNULL(DATE_FORMAT(T.fecha_alta,'%d/%m/%Y %H:%i:%s'),'') as fecha_alta, IFNULL(DATE_FORMAT(T.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'') as fecha_modificacion, 
            IFNULL(DATE_FORMAT(T.fecha_entrega,'%d/%m/%Y %H:%i:%s'),'') as fecha_entrega, 
            IFNULL(DATE_FORMAT(T.fecha_finalizacion,'%d/%m/%Y %H:%i:%s'),'') as fecha_finalizacion, terminada, usuario_id, U.nombre, U.apellido 
            FROM minutas_tareas T
                INNER JOIN usuarios U ON U.id = T.usuario_id 
             WHERE minuta_id  = ? 
            ORDER BY orden";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$minutaId))
            {
                if($sentencia->execute())
                {
                    //$valores = array();
                    if ($sentencia->bind_result($id, $titulo, $fechaAlta, $fechaModificacion, $fechaEntrega, $fechaFinalizacion, $terminada, $usuarioId, $usuarioNombre, $usuarioApellido))
                    //if ($sentencia->bind_result($valores))
                    {
                        while($sentencia->fetch())
                        {
                            $tarea= (object) [
                                'id' =>  $id,
                                'titulo' => $titulo,
                                'fechaAlta' => $fechaAlta,
                                'fechaModificacion' => $fechaModificacion,
                                'fechaEntrega' => $fechaEntrega,
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
                            
                            array_push($tareas,$tarea);
                        }
                        $resultado->valor = $tareas;
                        
                        $sentencia->close();
                        
                        for($i=0; $i < count($tareas);$i++)
                        {
                            $tarea = $tareas[$i];
//                             $resultadoPreguntas = $this->consultarPreguntas($cursoId,$leccion->id);
//                             if($resultadoPreguntas->mensajeError=="")
//                             {
//                                 $leccion->preguntas = $resultadoPreguntas->valor;
//                             }
//                             else
//                             {
//                                 $resultado->mensajeError = $resultadoPreguntas->mensajeError;
//                                 break;
//                             }
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
    
    public function actualizarValorTarea($minutaId, $tareaId, $campo, $valor)
    {
        $resultado = new Resultado();
        $this->conexion->autocommit(FALSE);
        $fechaFinalizacion = "";
        if($campo=="terminada")
        {
            if($valor==1)
                $fechaFinalizacion = "fecha_finalizacion = NOW(),";
            else
                $fechaFinalizacion = "fecha_finalizacion = NULL,";
        }
        
        $consulta = " UPDATE minutas_tareas 
           SET $campo = ? ,
                $fechaFinalizacion
                fecha_modificacion = NOW()
            WHERE minuta_id = ? AND id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("sii", $valor, $minutaId, $tareaId))
            {
                if($sentencia->execute())
                {
                    $resultado->valor=true;
                    $sentencia->close();
                    
//                     $resultado = $this->consultarNumeroTareasPendientesMinuta($minutaId);
//                     if($resultado->correcto())
//                     {
//                         $pendientes = $resultado->valor;
//                         if($pendientes==0)
//                             $resultado = $this->actualizarFinalizacionMinuta($minutaId,1,"NOW()");
//                         else
//                             $resultado = $this->actualizarFinalizacionMinuta($minutaId,0,"NULL");
//                     }
                    $resultado = $this->actualizarMinuta($minutaId);
                    
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
    
    private function actualizarMinuta($minutaId)
    {
        $resultado = new Resultado();
        $resultado = $this->consultarNumeroTareasPendientesMinuta($minutaId);
        if($resultado->correcto())
        {
            $pendientes = $resultado->valor;
            if($pendientes==0)
                $resultado = $this->actualizarFinalizacionMinuta($minutaId,1,"NOW()");
            else
                $resultado = $this->actualizarFinalizacionMinuta($minutaId,0,"NULL");
        }
        return $resultado;
    }
    
    public function eliminarTarea($llaves)
    {
        ini_set('max_execution_time', 300);
        $resultado = new Resultado();
        $this->conexion->autocommit(FALSE);
        
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
        if($resultado->correcto())
            $this->conexion->commit();
        else
            $this->conexion->rollback();
            
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
        $this->conexion->autocommit(FALSE);
        $resultado =  $this->calcularIdTarea($minutaId, "id");
        if($resultado->correcto())
        {
            $id =  $resultado->valor;
            $resultado =  $this->calcularIdTarea($minutaId,"orden");
            if($resultado->correcto())
            {
                $orden =  $resultado->valor;
                $consulta = "INSERT INTO minutas_tareas(minuta_id, id, orden, usuario_id, fecha_alta, fecha_modificacion, terminada, titulo) " .
                    "VALUE(?, ?, ?, ?, NOW(), NOW(), 0, ?)";
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if($sentencia->bind_param("iiiis",$minutaId, $id, $orden, $usuario->id, $modelo->titulo))
                    {
                        if($sentencia->execute())
                        {
                            $sentencia->close();
                           
                            
                            $resultado = $this->actualizarMinuta($minutaId);
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
            $resultado->valor = $id;
        }
        else
            $this->conexion->rollback();
        
        return $resultado;
    }
    
    public function actualizarTarea($minutaId, $modelo, $usuario)
    {
        $this->conexion->autocommit(FALSE);
        $consulta = "UPDATE minutas_tareas
                        SET titulo = ?,
                            fecha_modificacion = NOW()
                      WHERE minuta_id = ? AND id = ?";  
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("sii",$modelo->titulo,$minutaId, $modelo->id))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                    
                    $resultado = $this->actualizarMinuta($minutaId);
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
    
    
    
}
