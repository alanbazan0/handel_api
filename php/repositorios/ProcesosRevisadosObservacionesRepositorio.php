<?php
namespace php\repositorios;

use php\interfaces\IProcesosRevisadosObservacionesRepositorio;
use php\modelos\ProcesoRevisadoObservacion;
use php\modelos\Resultado;
use php\clases\AdministradorCorreo;
use php\modelos\ObservacionComentario;

include "../interfaces/IProcesosRevisadosObservacionesRepositorio.php";
include "../modelos/ProcesoRevisadoObservacion.php";
require_once("../clases/TipoUsuario.php");
require_once( "RepositorioBase.php");
require_once('../repositorios/EstatusValidacionProcesosRepositorio.php');
require_once("../clases/Resultado.php");
require_once("../clases/AdministradorCorreo.php");
require_once("../clases/EstatusValidacionProceso.php");
require_once("../repositorios/ProcesosRevisadosRepositorio.php");
require_once("../modelos/ObservacionComentario.php");
require_once("../repositorios/ObservacionesComentariosRepositorio.php");


class ProcesosRevisadosObservacionesRepositorio extends RepositorioBase implements IProcesosRevisadosObservacionesRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = " SELECT PRO.id, tipo_observacion_id, TOB.descripcion, PRO.descripcion, archivo, seccion, IFNULL(DATE_FORMAT(PRO.fecha_alta,'%d/%m/%Y %H:%i:%s'),'')fecha_alta,IFNULL(DATE_FORMAT(PRO.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'')fecha_modificacion, IFNULL(DATE_FORMAT(PRO.fecha_validacion,'%d/%m/%Y %H:%i:%s'),'')fecha_validacion, PRO.validacion_usuario_id,U.nombre, U.apellido, comentario_validacion,
                                (SELECT count(*) FROM procesos_revisados_observaciones_comentarios PROC WHERE PROC.proceso_revisado_id = PRO.proceso_revisado_id AND PROC.observacion_id = PRO.id)numeroComentarios, P.nombre, PRO.estatus_validacion_id, EV.descripcion, U1.nombre, U1.apellido, EV.icono, EV.color, U1.id, U1.nombre_usuario, seccion_admin, descripcion_admin
                            FROM procesos_revisados_observaciones PRO
                            	INNER JOIN tipos_observacion TOB ON PRO.tipo_observacion_id = TOB.id 
                                INNER JOIN procesos_revisados PR ON PRO.proceso_revisado_id = PR.id
                                LEFT JOIN usuarios U ON U.id = PRO.validacion_usuario_id
                                INNER JOIN usuarios_procesos UP ON UP.id = PR.usuario_proceso_id 
                                INNER JOIN procesos P ON P.id = UP.proceso_id
                                LEFT JOIN estatus_validacion_procesos EV ON EV.id = PRO.estatus_validacion_id 
                                INNER JOIN usuarios U1 ON U1.id = UP.usuario_id";
    }
    
    public function insertar($usuario,ProcesoRevisadoObservacion $modelo)
    {
        $resultado =  $this->calcularIdObservacion($modelo->procesoRevisadoId);
        if($resultado->mensajeError=="")
        {
            $id = $resultado->valor;
            $modelo->id = $id;
            $consulta = "INSERT INTO procesos_revisados_observaciones(proceso_revisado_id, id, tipo_observacion_id, descripcion, seccion, fecha_alta, fecha_modificacion,estatus_validacion_id) " .
                "VALUE(?, ?, ?, ?, ?, NOW(), NOW(), ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                $estatusId = \EstatusValidacionProceso::EN_PROCESO_DE_ANALISIS;
                if( $sentencia->bind_param("iiissi",$modelo->procesoRevisadoId,$id, $modelo->tipoObservacionId, $modelo->descripcion, $modelo->seccion, $estatusId))
                {
                    if($sentencia->execute())
                    {
                        if($usuario->tipoUsuarioId == \TipoUsuario::ADMINISTRADOR)
                        {
                            $repositorio = new ProcesosRevisadosRepositorio($this->conexion);
                            $resultado = $repositorio->consultarPorLlaves((object)["id" => $modelo->procesoRevisadoId]);
                            if($resultado->correcto())
                            {
                                $procesoRevisado = $resultado->valor;
                                $resultado = $this->enviarNotificacionObservacionAgregada($procesoRevisado,$modelo, $usuario);
                            }
                        }
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__. ". Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
                else
                    $resultado->mensajeError = __FUNCTION__. ". Falló el enlace de parámetros";
            }
            else
                $resultado->mensajeError = __FUNCTION__. ". Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
        }
        return $resultado;
    }
    
    public function actualizar(ProcesoRevisadoObservacion $modelo)
    {
        $resultado = new Resultado();
        $consulta = " UPDATE areas " .
            "SET nombre = ?, " .
            "  empresa_id = ?, " .
            "  sede_id = ?, " .
            "  estatus = ?, " .
            "  tipo_area_id = ?, " .
            "  fecha_modificacion= NOW() " .
            "WHERE id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("siiiii", $modelo->nombre, $modelo->empresaId,  $modelo->sedeId, $modelo->estatus,$modelo->tipoAreaId ,$modelo->id ))
            {
                if($sentencia->execute())
                {
                    $resultado->valor=true;
                }
                else
                    $resultado->mensajeError = __FUNCTION__. ". Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else  $resultado->mensajeError = __FUNCTION__. ". Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = __FUNCTION__. ". Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            return $resultado;
    }
    
    public function consultar($criteriosSeleccion)
    {
       
        $resultado = new Resultado();
        $registros = array();
        $filtros = array();
        $where="";
        if($criteriosSeleccion!=null)
        {
            if(isset($criteriosSeleccion->procesoId))
            {
                if($criteriosSeleccion->procesoId!="" && $criteriosSeleccion->procesoId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'P','campo'=>'id','valor'=>$criteriosSeleccion->procesoId]);
            }
            if(isset($criteriosSeleccion->procesoRevisadoId))
            {
                if($criteriosSeleccion->procesoRevisadoId!="" && $criteriosSeleccion->procesoRevisadoId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'PRO','campo'=>'proceso_revisado_id','valor'=>$criteriosSeleccion->procesoRevisadoId]);
            }
            if(isset($criteriosSeleccion->estatusValidacionId))
            {
                if($criteriosSeleccion->estatusValidacionId!="" && $criteriosSeleccion->estatusValidacionId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'EV','campo'=>'id','valor'=>$criteriosSeleccion->estatusValidacionId]);
            }
            $where = $this->where($filtros);
        }
        $consulta = $this->consultaBase .
        $where . " ORDER BY PRO.id";     
        
        //var_dump($consulta);
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $tipoObservacionId, $tipoObservacionDescripcion, $descripcion, $archivo, $seccion, $fechaAlta, $fechaModificacion, $fechaValidacion, $validadorId, $validadorNombre, $validadorApellido, $comentarioValidacion,$numeroComentarios,$procesoNombre, $estatusValidacionId, $estatusValidacionDescripcion, $usuarioNombre, $usuarioApellido,$estatusValidacionIcono, $estatusValidacionColor, $usuarioId, $nombreUsuario,$seccionAdmin, $descripcionAdmin))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $tipoObservacionId, $tipoObservacionDescripcion, $descripcion, $archivo, $seccion, $fechaAlta, $fechaModificacion, $fechaValidacion, $validadorId, $validadorNombre, $validadorApellido, $comentarioValidacion,$numeroComentarios,$procesoNombre,$estatusValidacionId, $estatusValidacionDescripcion, $usuarioNombre, $usuarioApellido,$estatusValidacionIcono, $estatusValidacionColor, $usuarioId, $nombreUsuario,$seccionAdmin, $descripcionAdmin);
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = "Falló el enlace del resultado.";
                }
                else
                    $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = "Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
            
            return $resultado;
    }
    
    public function consultarPorLlaves($llaves)
    {
        $resultado = new Resultado();
        $consulta = $this->consultaBase .
        " WHERE PRO.proceso_revisado_id  = ? AND PRO.id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ii",$llaves->procesoRevisadoId, $llaves->id))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $tipoObservacionId, $tipoObservacionDescripcion, $descripcion, $archivo, $seccion, $fechaAlta, $fechaModificacion, $fechaValidacion, $validadorId, $validadorNombre, $validadorApellido, $comentarioValidacion,$numeroComentarios,$procesoNombre,$estatusValidacionId, $estatusValidacionDescripcion, $usuarioNombre, $usuarioApellido, $estatusValidacionIcono, $estatusValidacionColor, $usuarioId, $nombreUsuario, $seccionAdmin, $descripcionAdmin))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $tipoObservacionId, $tipoObservacionDescripcion, $descripcion, $archivo, $seccion, $fechaAlta, $fechaModificacion, $fechaValidacion, $validadorId, $validadorNombre, $validadorApellido, $comentarioValidacion,$numeroComentarios,$procesoNombre,$estatusValidacionId, $estatusValidacionDescripcion, $usuarioNombre, $usuarioApellido,$estatusValidacionIcono, $estatusValidacionColor, $usuarioId, $nombreUsuario,$seccionAdmin,$descripcionAdmin);
                            $resultado->valor = $registro;
                        }
                        else
                            $resultado->mensajeError = "No se encontró ningún resultado.";
                    }
                    else
                        $resultado->mensajeError = "Falló el enlace del resultado";
                }
                else
                    $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = "Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            return $resultado;
    }
    
    private function crearRegistro($id, $tipoObservacionId, $tipoObservacionNombre, $descripcion, $archivo, $seccion, $fechaAlta, $fechaModificacion, $fechaValidacion, $validadorId, $validadorNombre, $validadorApellido, $comentarioValidacion, $numeroComentarios, $procesoNombre,$estatusValidacionId, $estatusValidacionDescripcion, $usuarioNombre, $usuarioApellido, $estatusValidacionIcono, $estatusValidacionColor, $usuarioId, $nombreUsuario, $seccionAdmin, $descripcionAdmin)
    {
        $registro= (object) [
            'id' =>  $id,
            'tipoObservacionId' => $tipoObservacionId,
            'tipoObservacionNombre' => $tipoObservacionNombre,
            'descripcion' => $descripcion,
            'archivo' => $archivo,
            'seccion' => $seccion,
            'fechaAlta' => $fechaAlta,
            'fechaModificacion' => $fechaModificacion,
            'fechaValidacion' => $fechaValidacion,
            'validadorId' => $validadorId,
            'validadorNombre' => $validadorNombre,
            'validadorApellido' => $validadorApellido,
            'comentarioValidacion' => $comentarioValidacion,
            'numeroComentarios' => $numeroComentarios,
            'procesoNombre' => $procesoNombre,
            'estatusValidacionId' => $estatusValidacionId,
            'estatusValidacionDescripcion' => $estatusValidacionDescripcion,
            "usuarioId" => $usuarioId,
            "nombreUsuario" => $nombreUsuario,
            "usuarioNombre" => $usuarioNombre,
            "usuarioApellido" => $usuarioApellido,
            "estatusValidacionIcono" => $estatusValidacionIcono,
            "estatusValidacionColor" => $estatusValidacionColor,
            "seccionAdmin" => $seccionAdmin,
            "descripcionAdmin" => $descripcionAdmin
        ];
        
        $registro->usuarioNombreCompleto = $registro->usuarioNombre . " " . $registro->usuarioApellido;
        $registro->validadorNombreCompleto = $registro->validadorNombre . " " . $registro->validadorApellido;
        $registro->validadorFotoPerfil =  "../fotos/usuario". $registro->validadorId .".jpg";
        if(file_exists($registro->validadorFotoPerfil))
            $registro->validadorFotoPerfil =  "php/fotos/usuario". $registro->validadorId .".jpg";
        else
            $registro->validadorFotoPerfil =  "php/fotos/default.jpg";
        return $registro;
    }
    
    public function consultarPorEmpresa($empresaId)
    {
        $resultado = new Resultado();
        $registros = array();
        $consulta = $this->consultaBase .
        " WHERE A.empresa_id  = ? order by A.nombre";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$empresaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $tipoObservacionId, $tipoObservacionDescripcion, $descripcion, $archivo, $seccion, $fechaAlta, $fechaModificacion, $fechaValidacion, $validadorId, $validadorNombre, $validadorApellido, $comentarioValidacion,$numeroComentarios,$estatusValidacionIcono, $estatusValidacionColor, $usuarioId, $nombreUsuario,$seccionAdmin, $descripcionAdmin))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $tipoObservacionId, $tipoObservacionDescripcion, $descripcion, $archivo, $seccion, $fechaAlta, $fechaModificacion, $fechaValidacion, $validadorId, $validadorNombre, $validadorApellido, $comentarioValidacion,$numeroComentarios,$estatusValidacionIcono, $estatusValidacionColor, $usuarioId, $nombreUsuario,$seccionAdmin, $descripcionAdmin);
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = "Falló el enlace del resultado";
                }
                else
                    $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = "Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            return $resultado;
    }
    
    public function eliminar($llaves)
    {
        $resultado = new Resultado();
        $consulta = " DELETE FROM areas "
            . "  WHERE id  = ? ";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param("i",$llaves->id))
                {
                    if($sentencia->execute())
                    {
                        $resultado->valor = $llaves->id;
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
    
    public function calcularIdObservacion($procesoRevisadoId)
    {
        $resultado = new Resultado();
        $consulta =  "SELECT IFNULL(MAX(id),0)+1 AS id FROM procesos_revisados_observaciones where proceso_revisado_id = $procesoRevisadoId";
        
        if($sentencia = $this->conexion->prepare($consulta))
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
            $resultado->mensajeError =  __FUNCTION__." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            return $resultado;
    }
    
    public function actualizarEstatusValidacion($usuario, $procesoRevisadoId, $observacionId, $estatusValidacionId, $comentario, $seccion, $descripcion)
    {
        ini_set('max_execution_time', 0);
        $this->conexion->autocommit(FALSE);
        
        $resultado = new Resultado();
        $consulta = "UPDATE procesos_revisados_observaciones
                     SET
                         estatus_validacion_id = ?,
                         fecha_modificacion = NOW(),
                         fecha_validacion = NOW(),
                         validacion_usuario_id = ?,
                         seccion_admin = ?, 
                         descripcion_admin = ?
                     WHERE proceso_revisado_id = ?
                            AND id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('iissii',$estatusValidacionId, $usuario->id, $seccion, $descripcion, $procesoRevisadoId, $observacionId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                    $resultado = $this->calcularId('id','historial_procesos_revisados_observaciones');
                    if($resultado->correcto())
                    {
                        $id = $resultado->valor;
                        $consulta = "INSERT INTO historial_procesos_revisados_observaciones(id, proceso_revisado_id, observacion_id, fecha_validacion, estatus_validacion_id, usuario_validador_id) " .
                            "VALUE(?, ?, ?, NOW(), ?, ?)";
                        if($sentencia = $this->conexion->prepare($consulta))
                        {
                            if($sentencia->bind_param("iiiii", $id, $procesoRevisadoId, $observacionId, $estatusValidacionId, $usuario->id))
                            {
                                if($sentencia->execute())
                                {
                                    if($comentario!="")
                                    {
                                        $repositorio = new ObservacionesComentariosRepositorio($this->conexion);
                                        $modeloComentario = new ObservacionComentario();
                                        $modeloComentario->comentario = $comentario;
                                        $modeloComentario->procesoRevisadoId = $procesoRevisadoId;
                                        $modeloComentario->observacionId = $observacionId;
                                        $resultado = $repositorio->insertar($usuario, $modeloComentario);
                                    }
                                        
                                    if($resultado->correcto())
                                    {
                                        $resultado = $this->consultarPorLlaves((object)["procesoRevisadoId"=>$procesoRevisadoId, "id" => $observacionId]);
                                    
                                        if($resultado->correcto())
                                        {
                                            $observacion = $resultado->valor;
                                            $repositorio = new EstatusValidacionProcesosRepositorio($this->conexion);
                                            $resultado = $repositorio->consultarPorLlaves((object)["id" => $estatusValidacionId]);
                                            if($resultado->correcto())
                                            {
                                                $estatusValidacion = $resultado->valor;
                                                $resultado = $this->enviarNotificacionCambioEstatus($procesoRevisadoId,$observacion, $usuario, $estatusValidacion);
                                                if($resultado->correcto())
                                                {
                                                   
                                                       
                                                }
                                            }
                                           
                                        }
                                    }
                                    
                                }
                                else
                                {
                                    $resultado->codigoError = $this->conexion->errno;
                                    $resultado->mensajeError = __FUNCTION__ . "Falló la ejecución(" . $this->conexion->errno . ") " . $this->conexion->error;
                                }
                                
                            }
                            else
                            {
                                $resultado->mensajeError = __FUNCTION__ ."Falló el enlace de parámetros";
                            }
                        }
                        else
                        {
                            $resultado->codigoError = $this->conexion->errno;
                            $resultado->mensajeError = __FUNCTION__ ."Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
                        }
                    }
                    
                }
                else
                    $resultado->mensajeError = __FUNCTION__ .' Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__ .' Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = __FUNCTION__ .' Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            
        if($resultado->correcto())
        {
            $resultado->valor = $observacion;
            $this->conexion->commit();
        }
        else
            $this->conexion->rollback();
        return $resultado;
    }
    
    public function enviarNotificacionObservacionAgregada($procesoRevisado,$observacion,$usuario)
    {
        $resultado = new Resultado();
        
        
        $usuarios = array();
        //TEST
        array_push($usuarios, (object)["nombreUsuario"=>"alanbazan@apps-handel.com","nombre"=>"Alan"]);
        
        //array_push($usuarios, (object)["nombreUsuario"=>$procesoRevisado->nombreUsuario,"nombre"=>$procesoRevisado->usuarioNombre]);
        
        $contenido = "<p style='font-size: 14px; line-height: 140%;'><strong>¡Actualización importante!</strong></p>
                    <p style='font-size: 14px; line-height: 140%;'>&nbsp;</p>
                    <p style='font-size: 14px; line-height: 140%;'>¡Tienes un nuevo mensaje en SAHA!,
                    Es en referencia al proceso de revisión de procedimientos, en particular a tus observaciones o dudas del procedimiento:</p>
                    <p style='font-size: 14px; line-height: 140%;'>&nbsp;</p>
                    <p style='font-size: 14px; line-height: 140%;'><strong>$procesoRevisado->procesoNombre</strong></p>
                    $usuario->nombreCompleto agregó la observación:</p>
                    <p style='font-size: 14px; line-height: 140%;'>&nbsp;</p>
                    <p style='font-size: 14px; line-height: 140%;'><strong>$observacion->descripcion</strong></p>
                    <p style='font-size: 14px; line-height: 140%;'>&nbsp;</p>
                    <p style='font-size: 14px; line-height: 140%;'>Si necesitas información adicional puedes responder desde el botón que aparece un poco más abajo.</p>";
        
        $url = "https://saha.apps-handel.com/revision.php?id=$procesoRevisado->id"."_".$observacion->id;
        
        $boton = "<a href='$url' target='_blank' style='box-sizing: border-box;display: inline-block;font-family:arial,helvetica,sans-serif;text-decoration: none;-webkit-text-size-adjust: none;text-align: center;color: #ffffff; background-color: #0396a6; border-radius: 4px;-webkit-border-radius: 4px; -moz-border-radius: 4px; width:auto; max-width:100%; overflow-wrap: break-word; word-break: break-word; word-wrap:break-word; mso-border-alt: none;'>
        <span style='display:block;padding:10px 20px;line-height:120%;'><strong>Responder</strong></span>
        </a>";
        
        //$nombreUsuario = $usuario->nombreCompleto;
        $asunto  = "Se agregó una observación ";
        $tipo = "agregacionObservacion$procesoRevisado->id" ."_".$observacion->id;
        
        $administradorCorreo = new  AdministradorCorreo();
        $resultado = $administradorCorreo->enviarNotificacionRevision($tipo,$usuario,$usuarios,$observacion,$contenido,$boton, $asunto);
        if($resultado->correcto())
        {
            $resultado->valor = $observacion;
        }
        return $resultado;
    }
    
    public function enviarNotificacionCambioEstatus($procesoRevisadoId,$observacion,$usuario, $estatusValidacion)
    {
        $resultado = new Resultado();
        
        
        $usuarios = array();
        //TEST
        //array_push($usuarios, (object)["nombreUsuario"=>"alanbazan@apps-handel.com","nombre"=>"Alan"]);
        
        array_push($usuarios, (object)["nombreUsuario"=>$observacion->nombreUsuario,"nombre"=>$observacion->usuarioNombre]);
        
        $contenido = "<p style='font-size: 14px; line-height: 140%;'><strong>¡Actualización importante!</strong></p>
                    <p style='font-size: 14px; line-height: 140%;'>&nbsp;</p>
                    <p style='font-size: 14px; line-height: 140%;'>¡Tienes un nuevo mensaje en SAHA!,
                    Es en referencia al proceso de revisión de procedimientos, en particular a tus observaciones o dudas del procedimiento:</p>
                    <p style='font-size: 14px; line-height: 140%;'>&nbsp;</p>
                    <p style='font-size: 14px; line-height: 140%;'><strong>$observacion->procesoNombre</strong></p>
                    Observación:</p>
                    <p style='font-size: 14px; line-height: 140%;'>&nbsp;</p>
                    <p style='font-size: 14px; line-height: 140%;'><strong>$observacion->descripcion</strong></p>
                    <p style='font-size: 14px; line-height: 140%;'>&nbsp;</p>
                    <p style='font-size: 14px; line-height: 140%;'>El estado actual de tu solicitud es: </p>
                    <p style='font-size: 14px; line-height: 140%;'>&nbsp;</p>
                    <p style='font-size: 14px; line-height: 140%;'><strong>$estatusValidacion->nombre</strong></p>
                    <p style='font-size: 14px; line-height: 140%;'>&nbsp;</p>
                    <p style='font-size: 14px; line-height: 140%;'>Si consideras que esta respuesta es satisfactoria, este es el final de la conversación sobre este proceso. Si necesitas información adicional puedes responder desde el botón que aparece un poco más abajo.</p>";
        
        $url = "https://saha.apps-handel.com/revision.php?id=$procesoRevisadoId"."_".$observacion->id;
        
        $boton = "<a href='$url' target='_blank' style='box-sizing: border-box;display: inline-block;font-family:arial,helvetica,sans-serif;text-decoration: none;-webkit-text-size-adjust: none;text-align: center;color: #ffffff; background-color: #0396a6; border-radius: 4px;-webkit-border-radius: 4px; -moz-border-radius: 4px; width:auto; max-width:100%; overflow-wrap: break-word; word-break: break-word; word-wrap:break-word; mso-border-alt: none;'>
        <span style='display:block;padding:10px 20px;line-height:120%;'><strong>Responder</strong></span>
        </a>";
        
        //$nombreUsuario = $usuario->nombreCompleto;
        $asunto  = "Cambio el estado de tu solicitud: " . $estatusValidacion->nombre;
        $tipo = "observacionEstatus$procesoRevisadoId" ."_".$observacion->id;
        
        $administradorCorreo = new  AdministradorCorreo();
        $resultado = $administradorCorreo->enviarNotificacionRevision($tipo,$usuario,$usuarios,$observacion,$contenido,$boton, $asunto);
        if($resultado->correcto())
        {
            $resultado->valor = $observacion;
        }
        return $resultado;
    }
    
}

