<?php
namespace php\repositorios;

use php\clases\AdministradorCorreo;
use php\clases\Porcentaje;
use php\interfaces\IProcesosRevisadosRepositorio;
use php\modelos\Evidencia;
use php\modelos\Resultado;
use php\modelos\EvidenciaComentario;
use php\modelos\ProcesoRevisado;
use php\modelos\EstatusValidacionProceso;

require_once('../interfaces/IProcesosRevisadosRepositorio.php');
require_once('../modelos/ProcesoRevisado.php');
require_once('RepositorioBase.php');
require_once('UsuariosRepositorio.php');
require_once("../clases/TipoUsuario.php");
require_once("../clases/EstatusRevision.php");
require_once("../clases/EstatusValidacionProceso.php");
require_once("../clases/TipoReporteEvidencias.php");
require_once('../clases/Resultado.php');
require_once('../clases/Porcentaje.php');
require_once('../repositorios/EstatusValidacionProcesosRepositorio.php');
require_once('../repositorios/EvidenciasComentariosRepositorio.php');

class ProcesosRevisadosRepositorio extends RepositorioBase implements IProcesosRevisadosRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = "SELECT E.id, usuario_proceso_id, IFNULL(DATE_FORMAT(E.fecha_alta,'%d/%m/%Y %H:%i:%s'),'') as fecha, P.nombre, P.codigo,
                                0 numeroComentarios, U.nombre, U.apellido, S.id, S.nombre, EM.id, EM.nombre, U.id, validada, comentarios_validacion, EM.administrador_id, V.nombre administradorNombre, V.apellido administradorApellido,E.validacion_usuario_id validadorId, VL.nombre validadorNombre, VL.apellido validadorApellido, UP.proceso_id,
                                E.estatus_validacion_id, EV.descripcion, EV.icono, EV.color, E.estatus_revision_id, ER.descripcion, ER.icono, ER.color,  IFNULL(DATE_FORMAT( E.fecha_validacion,'%d/%m/%Y %H:%i:%s'),'') as fechaValidacion,
                                (SELECT count(O.id) FROM procesos_revisados_observaciones O WHERE O.proceso_revisado_id = E.id) numeroObservaciones, U.nombre_usuario
                                FROM procesos_revisados E
                                	INNER JOIN  usuarios_procesos UP ON UP.id = E.usuario_proceso_id
                                	INNER JOIN usuarios U ON U.id = UP.usuario_id
                                	LEFT JOIN sedes S ON S.id = U.sede_id
                                	LEFT JOIN empresas EM ON EM.id = S.empresa_id
                                	INNER JOIN procesos P ON P.id = UP.proceso_id
                                	LEFT JOIN usuarios V ON V.id = EM.administrador_id
                                	LEFT JOIN usuarios VL ON VL.id = E.validacion_usuario_id
                                    LEFT JOIN estatus_validacion_procesos EV ON E.estatus_validacion_id = EV.id
                                    INNER JOIN estatus_revision ER ON ER.id = E.estatus_revision_id
                                ";
    }

    public function insertar($usuario,ProcesoRevisado $modelo)
    {
        $resultado = $this->calcularId('id','procesos_revisados');
        if($resultado->mensajeError=='')
        {
            $id = $resultado->valor;
            $consulta = "INSERT INTO procesos_revisados(id, usuario_proceso_id, usuario_id,estatus_revision_id,fecha_alta, fecha_modificacion, validada, estatus_validacion_id)VALUES(?, ?, ?, ?, NOW(), NOW(), 0, ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('iiiii', $id, $modelo->usuarioProcesoId, $usuario->id, $modelo->estatusRevisionId, $modelo->estatusValidacionId))
                {
                    if($sentencia->execute())
                         $resultado->valor = $id;
                    else
                        $resultado->mensajeError = __FUNCTION__. '. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
                }
                else
                    $resultado->mensajeError = __FUNCTION__. '. Falló el enlace de parámetros';
            }
            else
                $resultado->mensajeError = __FUNCTION__. '. Falló la preparación: (' . $this->conexion->errno . ') ' .$this->conexion->error;
        }
        return $resultado;
    }
    
    
    function reportarSinCambios($usuario,$usuarioProcesoId)
    {
        $procesoRevisado = new ProcesoRevisado();
        $procesoRevisado->usuarioProcesoId = $usuarioProcesoId;
        $procesoRevisado->estatusRevisionId = \EstatusRevision::NO_HUBO_CAMBIOS;
        $procesoRevisado->estatusValidacionId = \EstatusValidacionProceso::EN_PROCESO_DE_ANALISIS;
         $dia = date("d");
         $mes = date("m");
         $ano = date("Y");
         $resultado = $this->consultarPorUsuarioProceso($usuarioProcesoId, $dia, $mes, $ano);
         if($resultado->correcto())
         {
             if($resultado->valor==null)
             {
                $resultado = $this->insertar($usuario,$procesoRevisado);
                if($resultado->correcto())
                {
                    
                }
             }
         }
        return $resultado;
    }
    
    function guardarObservaciones($usuario,$usuarioProcesoId,$observaciones)
    {
        $procesoRevisado = new ProcesoRevisado();
        $procesoRevisado->usuarioProcesoId = $usuarioProcesoId;
        $procesoRevisado->estatusRevisionId = \EstatusRevision::OBSERVACIONES;
        $procesoRevisado->estatusValidacionId = \EstatusValidacionProceso::EN_PROCESO_DE_ANALISIS;
        $this->conexion->autocommit(FALSE);
        $dia = date("d");
        $mes = date("m");
        $ano = date("Y");
        $resultado = $this->consultarPorUsuarioProceso($usuarioProcesoId, $dia, $mes, $ano);
        if($resultado->correcto())
        {
            if($resultado->valor==null)
            {
                $resultado = $this->insertar($usuario,$procesoRevisado);
                
                if($resultado->correcto())
                {
                    $procesoRevisadoId = $resultado->valor;
                    if($observaciones!=null)
                    {
                        for ($k = 0; $k< count($observaciones); $k++)
                        {
                            $observacion = $observaciones[$k];
                            $observacionId = $k + 1;
                                
        //                     var_dump($procesoRevisadoId);
        //                     var_dump($observacion);
                            $consulta = "INSERT INTO procesos_revisados_observaciones(proceso_revisado_id, id, tipo_observacion_id, seccion, descripcion, fecha_alta, fecha_modificacion) " .
                                "VALUE(?, ?, ?, ?, ?, NOW(), NOW())";
                            if($sentencia = $this->conexion->prepare($consulta))
                            {
                                if($sentencia->bind_param("iiiss", $procesoRevisadoId, $observacionId, $observacion->tipoObservacionId, $observacion->seccion, $observacion->descripcion))
                                {
                                    if($sentencia->execute())
                                    {
                                        $sentencia->close();
                                    }
                                    else
                                    {
                                        $resultado->codigoError = $this->conexion->errno;
                                        $resultado->mensajeError = "Falló la ejecución(" . $this->conexion->errno . ") " . $this->conexion->error;
                                        break;
                                    }
                                    
                                }
                                else
                                {
                                    $resultado->mensajeError = "Falló el enlace de parámetros";
                                    break;
                                }
                            }
                            else
                            {
                                $resultado->codigoError = $this->conexion->errno;
                                $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
                                break;
                            }
                        }
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
    
    public function calcularObservacionId($usuarioProcesoId)
    {
        $resultado = new Resultado();
        $consulta =  "SELECT IFNULL(MAX(id),0)+1 AS id FROM procesos_revisados_observaciones WHERE proceso_revisado_id = $usuarioProcesoId";
        
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
    
    function guardarObservacion($usuario,$usuarioProcesoId,$observacion,$procesoRevisadoId)
    {
        $this->conexion->autocommit(FALSE);
        $continuar = true;
        if($procesoRevisadoId=="")
        {
            $procesoRevisado = new ProcesoRevisado();
            $procesoRevisado->usuarioProcesoId = $usuarioProcesoId;
            $procesoRevisado->estatusRevisionId = \EstatusRevision::OBSERVACIONES;
            $procesoRevisado->estatusValidacionId = \EstatusValidacionProceso::EN_PROCESO_DE_ANALISIS;
            
            $resultado = $this->insertar($usuario,$procesoRevisado);
            if($resultado->correcto())
                $procesoRevisadoId = $resultado->valor;
            else
                $continuar = false;
        }
        
        if($continuar)
        {
           
            $resultado = $this->calcularObservacionId($usuarioProcesoId);
            if($resultado->correcto())
            {
                $observacionId = $resultado->valor;
            //                     var_dump($procesoRevisadoId);
            //                     var_dump($observacion);
                $consulta = "INSERT INTO procesos_revisados_observaciones(proceso_revisado_id, id, tipo_observacion_id, seccion, descripcion, fecha_alta, fecha_modificacion) " .
                    "VALUE(?, ?, ?, ?, ?, NOW(), NOW())";
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if($sentencia->bind_param("iiiss", $procesoRevisadoId, $observacionId, $observacion->tipoObservacionId, $observacion->seccion, $observacion->descripcion))
                    {
                        if($sentencia->execute())
                        {
                            $sentencia->close();
                        }
                        else
                        {
                            $resultado->codigoError = $this->conexion->errno;
                            $resultado->mensajeError = "Falló la ejecución(" . $this->conexion->errno . ") " . $this->conexion->error;
                        }
                        
                    }
                    else
                    {
                        $resultado->mensajeError = "Falló el enlace de parámetros";
                    }
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
            }
        }
        if($resultado->correcto())
        {
            $this->conexion->commit();
            $resultado->valor = $procesoRevisadoId;
        }
        else
            $this->conexion->rollback();
            return $resultado;
    }
    
    
    public function enviarNotificacionCambioEstatus($procesoRevisado,$usuario, $estatusValidacion)
    {
        $resultado = new Resultado();
       
                
        //TEST
        $usuarios = array();
        //array_push($usuarios, (object)["nombreUsuario"=>"alanbazan@apps-handel.com","nombre"=>"Alan"]);
        array_push($usuarios, (object)["nombreUsuario"=>$procesoRevisado->nombreUsuario,"nombre"=>$procesoRevisado->usuarioNombre]);
        
        $contenido = "<p style='font-size: 14px; line-height: 140%;'><strong>¡Actualización importante!</strong></p>
                    <p style='font-size: 14px; line-height: 140%;'>&nbsp;</p>
                    <p style='font-size: 14px; line-height: 140%;'>¡Tienes un nuevo mensaje en SAHA!,
                    Es en referencia al proceso de revisión de procedimientos, en particular a tus observaciones o dudas del procedimiento:</p>
                    <p style='font-size: 14px; line-height: 140%;'>&nbsp;</p>
                    <p style='font-size: 14px; line-height: 140%;'><strong>$procesoRevisado->nombre</strong></p>
                    <p style='font-size: 14px; line-height: 140%;'>&nbsp;</p>
                    <p style='font-size: 14px; line-height: 140%;'>El estado actual de tu solicitud es: </p>
                    <p style='font-size: 14px; line-height: 140%;'>&nbsp;</p>
                    <p style='font-size: 14px; line-height: 140%;'><strong>$estatusValidacion->nombre</strong></p>
                    <p style='font-size: 14px; line-height: 140%;'>&nbsp;</p>
                    <p style='font-size: 14px; line-height: 140%;'>Si consideras que esta respuesta es satisfactoria, este es el final de la conversación sobre este proceso. Si necesitas información adicional puedes responder desde el botón que aparece un poco más abajo.</p>";
        
        $url = "https://saha.apps-handel.com/revision.php?id=$procesoRevisado->id";
        
        $boton = "<a href='$url' target='_blank' style='box-sizing: border-box;display: inline-block;font-family:arial,helvetica,sans-serif;text-decoration: none;-webkit-text-size-adjust: none;text-align: center;color: #ffffff; background-color: #0396a6; border-radius: 4px;-webkit-border-radius: 4px; -moz-border-radius: 4px; width:auto; max-width:100%; overflow-wrap: break-word; word-break: break-word; word-wrap:break-word; mso-border-alt: none;'>
        <span style='display:block;padding:10px 20px;line-height:120%;'><strong>Responder</strong></span>
        </a>";
        
        //$nombreUsuario = $usuario->nombreCompleto;
        $asunto  = "Cambio el estado de tu solicitud: " . $estatusValidacion->nombre;
        $tipo = "procesoRevisadoEstatus" .$procesoRevisado->id;
        
        $administradorCorreo = new  AdministradorCorreo();
        $resultado = $administradorCorreo->enviarNotificacionRevision($tipo,$usuario,$usuarios,$procesoRevisado,$contenido,$boton, $asunto);
        if($resultado->correcto())
        {
            $resultado->valor = $procesoRevisado->id;
        }
        return $resultado;
    }
    
    
    
    
    public function numeroEvidenciasJustificadasAnoActual($usuarioProcedimientoId)
    {
        $resultado = new Resultado();
        $resultado->valor = false;
        
        $consulta = "SELECT count(id) id
                        FROM evidencias E 
                        WHERE justificacion_id IS NOT NULL AND usuario_procedimiento_id = ? 
                            AND YEAR(E.fecha_alta) = YEAR(NOW())";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$usuarioProcedimientoId))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($count))
                    {
                        if($sentencia->fetch())
                        {
                            $resultado->valor =$count;
                        }
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__. '. Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = __FUNCTION__. '. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__. '. Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = __FUNCTION__. '. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
//     public function numeroEvidenciasJustificadasMesActual($usuario,$criteriosSeleccion)
//     {
//         $resultado = new Resultado();
//         $resultado->valor = 0;
//         $filtros = $this->getFiltrosUsuario($usuario, $criteriosSeleccion,false);
        
        
//         $consulta = "SELECT count(*) numero
//                     FROM evidencias E
//                         INNER JOIN usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
//                         INNER JOIN usuarios U ON U.id = UP.usuario_id
//                     WHERE U.estatus = 1 AND MONTH(E.fecha_alta) = $criteriosSeleccion->mes AND YEAR(E.fecha_alta) = $criteriosSeleccion->ano AND justificacion_id IS NOT NULL 
//                     ";
//         $consulta.=  $this->and($filtros);
        
//         if($sentencia = $this->conexion->prepare($consulta))
//         {
//             if($this->bind_param($sentencia, $filtros))
//             {
//                 if($sentencia->execute())
//                 {
//                     if($sentencia->bind_result($count))
//                     {
//                         if($sentencia->fetch())
//                         {
//                             $resultado->valor =$count;
//                         }
//                     }
//                     else
//                         $resultado->mensajeError = __FUNCTION__. '. Falló el enlace del resultado.';
//                 }
//                 else
//                     $resultado->mensajeError = __FUNCTION__. '. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
//             }
//             else
//                 $resultado->mensajeError = __FUNCTION__. '. Falló el enlace de parámetros';
//         }
//         else
//             $resultado->mensajeError = __FUNCTION__. '. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
//         return $resultado;
//     }
    
//     public function numeroEvidenciasEnviadasMesActual($usuario,$criteriosSeleccion)
//     {
//         $resultado = new Resultado();
//         $resultado->valor = 0;
//         $filtros = $this->getFiltrosUsuario($usuario, $criteriosSeleccion,false);
//         $consulta = "SELECT count(*) numero
//                     FROM evidencias E
//                         INNER JOIN usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
//                         INNER JOIN usuarios U ON U.id = UP.usuario_id
//                     WHERE U.estatus = 1 AND MONTH(E.fecha_alta) = MONTH(NOW()) AND YEAR(E.fecha_alta) = YEAR(NOW()) AND justificacion_id IS NULL
//                     ";
//         $consulta.=  $this->and($filtros);
        
//         if($sentencia = $this->conexion->prepare($consulta))
//         {
//             if($this->bind_param($sentencia, $filtros))
//             {
//                 if($sentencia->execute())
//                 {
//                     if($sentencia->bind_result($count))
//                     {
//                         if($sentencia->fetch())
//                         {
//                             $resultado->valor =$count;
//                         }
//                     }
//                     else
//                         $resultado->mensajeError = __FUNCTION__. '. Falló el enlace del resultado.';
//                 }
//                 else
//                     $resultado->mensajeError = __FUNCTION__. '. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
//             }
//             else
//                 $resultado->mensajeError = __FUNCTION__. '. Falló el enlace de parámetros';
//         }
//         else
//             $resultado->mensajeError = __FUNCTION__. '. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
//             return $resultado;
//     }
    
//     public function numeroEvidenciasPendientesMesActual($usuario,$criteriosSeleccion)
//     {
//         $resultado = new Resultado();
//         $resultado->valor = 0;
//         $filtros = array();
//         $filtros = $this->getFiltrosUsuario($usuario, $criteriosSeleccion,false);
//         $consulta = "SELECT count(*)
//                     FROM usuarios_procedimientos UP
//                     	INNER JOIN procedimientos P ON P.id = UP.procedimiento_id
//                         INNER JOIN usuarios U ON U.id = UP.usuario_id
//                     WHERE UP.estatus = 1 
//                     	AND UP.id NOT IN(SELECT usuario_procedimiento_id FROM evidencias E WHERE MONTH(E.fecha_alta) = MONTH(NOW()) AND YEAR(E.fecha_alta) = YEAR(NOW()) ) ";
      
//         $consulta.=  $this->and($filtros);
        
//         if($sentencia = $this->conexion->prepare($consulta))
//         {
//             if($this->bind_param($sentencia, $filtros))
//             {
//                 if($sentencia->execute())
//                 {
//                     if($sentencia->bind_result($count))
//                     {
//                         if($sentencia->fetch())
//                         {
//                             $resultado->valor =$count;
//                         }
//                     }
//                     else
//                         $resultado->mensajeError = __FUNCTION__. '. Falló el enlace del resultado.';
//                 }
//                 else
//                     $resultado->mensajeError = __FUNCTION__. '. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
//             }
//             else
//                 $resultado->mensajeError = __FUNCTION__. '. Falló el enlace de parámetros';
//         }
//         else
//             $resultado->mensajeError = __FUNCTION__. '. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
//             return $resultado;
//     }
    
    public function consultarProcesosEnviados($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        
        $registros = array();
        
        //$filtros = array();
        $filtros = $this->getFiltrosN($usuario,$criteriosSeleccion,true);
        $where = $this->where($filtros);
        
        //UP.usuario_id = ? AND
        
        $consulta =  $this->consultaBase . $where . " " .
            " ORDER BY FIELD(U.id,$usuario->id) DESC,U.nombre, P.nombre";
        
        //var_dump($consulta);
        
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $usuarioProcedimientoId, $fecha,$nombre,$codigo,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId,$validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido,$validadorId, $validadorNombre, $validadorApellido, $procesoId,$estatusValidacionId, $estatusValidacionDescripcion, $estatusValidacionIcono, $estatusValidacionColor,$estatusRevisionId, $estatusRevisionDescripcion, $estatusRevisionIcono, $estatusRevisionColor, $fechaValidacion,$numeroObservaciones, $nombreUsuario))
                    {
                        while($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioProcedimientoId, $fecha,$nombre,$codigo,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId,$validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido,$validadorId, $validadorNombre, $validadorApellido, $procesoId,$estatusValidacionId, $estatusValidacionDescripcion, $estatusValidacionIcono, $estatusValidacionColor,$estatusRevisionId, $estatusRevisionDescripcion, $estatusRevisionIcono, $estatusRevisionColor, $fechaValidacion,$numeroObservaciones, $nombreUsuario);
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__. '. Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = __FUNCTION__. '. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__. '. Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = __FUNCTION__. '. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
    public function consultarEvidenciasJustificadas($usuario,$mes, $ano)
    {
        $resultado = new Resultado();
        
        $registros = array();
        $criteriosSeleccion = (object)["ano" => $ano,"mes" => $mes];
        //$filtros = array();
        $filtros = $this->getFiltrosN($usuario,$criteriosSeleccion,true);
        
        array_push($filtros,(object)['tipo'=>'estatico','texto'=>'E.justificacion_id is not null']);
        
        
        $where = $this->where($filtros);
        
        //UP.usuario_id = ? AND
        
        $consulta =  "SELECT U.id, U.nombre, U.apellido,P.id, P.nombre,J.id, J.nombre, 
                    (
                    	SELECT count(*)
                    	FROM evidencias E1
                    	INNER JOIN usuarios_procedimientos UP1 ON UP1.id = E1.usuario_procedimiento_id 
                    	INNER JOIN procedimientos P1 ON P1.id = UP1.procedimiento_id 
                        WHERE UP1.usuario_id = UP.usuario_id AND P1.id = P.id AND E1.justificacion_id is not null 
                        AND YEAR(E1.fecha_alta) = $criteriosSeleccion->ano
                    ) count
                    FROM evidencias E 
                    INNER JOIN usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id 
                    INNER JOIN usuarios U ON U.id = UP.usuario_id 
                    LEFT JOIN sedes S ON S.id = U.sede_id 
                    LEFT JOIN empresas EM ON EM.id = S.empresa_id 
                    INNER JOIN procedimientos P ON P.id = UP.procedimiento_id 
                    LEFT JOIN justificaciones J ON J.id = E.justificacion_id 
                    LEFT JOIN usuarios V ON V.id = EM.administrador_id 
                    LEFT JOIN usuarios VL ON VL.id = E.validacion_usuario_id " . 
                        $where . " " .
                    "GROUP BY U.id, U.nombre, U.apellido,P.id, P.nombre, J.id, J.nombre " .        
                    "ORDER BY U.nombre, U.apellido, P.nombre";
        
       // var_dump($consulta);
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($usuarioId, $usuarioNombre, $usuarioApellido, $procedimientoId, $procedimientoNombre,$justificacionId, $justificacionNombre, $numeroJustificaciones ))
                    {
                        while($sentencia->fetch())
                        {
                            $registro = (object)
                            [
                                "usuarioId" => $usuarioId,
                                "usuarioNombre" => $usuarioNombre,
                                "usuarioApellido" => $usuarioApellido,
                                "procedimientoId" => $procedimientoId,
                                "procedimientoNombre" => $procedimientoNombre,
                                "justificacionId" => $justificacionId,
                                "justificacionNombre" => $justificacionNombre,
                                "numeroJustificaciones" => $numeroJustificaciones
                            ];
                            
                            $registro->usuarioNombreCompleto = $registro->usuarioNombre . " " . $registro->usuarioApellido;
                            
                            //$registro = $this->crearRegistro($usuarioId, $usuarioNombre, $usuarioApellido, $evidenciaId, $evidenciaNombre,$justificacionId, $justificacionNombre);
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__. '. Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = __FUNCTION__. '. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__. '. Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = __FUNCTION__. '. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
//     public function consultarEvidencias($criteriosSeleccion)
//     {
//         $resultado = new Resultado();
        
//         $registros = array();
        
//         $filtros = array();
        
        
        
//         $and="";
//         if($criteriosSeleccion!=null)
//         {
//             if(isset($criteriosSeleccion->administradorId))
//                 array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'EM', 'campo'=>'administrador_id','valor'=>$criteriosSeleccion->administradorId]);
//             if(isset($criteriosSeleccion->validada))
//                 array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'E', 'campo'=>'validada','valor'=> $criteriosSeleccion->validada]);
//             if(isset($criteriosSeleccion->usuarioId))
//                 array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'E', 'campo'=>'usuario_id','valor'=> $criteriosSeleccion->usuarioId]);
//         }
        
     
      
        
//         $and = $this->and($filtros);
        
//         $consulta =  $this->consultaBase .
//         " WHERE  MONTH(E.fecha_alta) = $criteriosSeleccion->mes AND YEAR(E.fecha_alta) = $criteriosSeleccion->ano " . $and ." " .
//         "ORDER BY codigo";
        
//         if($sentencia = $this->conexion->prepare($consulta))
//         {
//             if($this->bind_param($sentencia, $filtros))
//             {
//                 if($sentencia->execute())
//                 {
//                     if($sentencia->bind_result($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId,$validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido,$validadorId, $validadorNombre, $validadorApellido, $procedimientoId,$estatusValidacionId, $estatusValidacionDescripcion, $estatusValidacionIcono, $estatusValidacionColor,$estatusRevisionId, $estatusRevisionDescripcion, $estatusRevisionIcono, $estatusRevisionColor, $fechaValidacion,$numeroObservaciones, $nombreUsuario))
//                     {
//                         while($sentencia->fetch())
//                         {
//                             $registro = $this->crearRegistro($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId, $validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido,$validadorId, $validadorNombre, $validadorApellido, $procedimientoId,$estatusValidacionId, $estatusValidacionDescripcion, $estatusValidacionIcono, $estatusValidacionColor,$estatusRevisionId, $estatusRevisionDescripcion, $estatusRevisionIcono, $estatusRevisionColor, $fechaValidacion,$numeroObservaciones, $nombreUsuario);
//                             array_push($registros,$registro);
//                         }
//                         $resultado->valor = $registros;
//                     }
//                     else
//                         $resultado->mensajeError = __FUNCTION__. '. Falló el enlace del resultado.';
//                 }
//                 else
//                     $resultado->mensajeError = __FUNCTION__. '. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
//             }
//             else
//                 $resultado->mensajeError = __FUNCTION__. '. Falló el enlace de parámetros';
//         }
//         else
//             $resultado->mensajeError = __FUNCTION__. '. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
//             return $resultado;
//     }
    
//     public function consultarEvidenciasAnualUsuario($usuario,$criteriosSeleccion)
//     {
//         $resultado = new Resultado();
        
//         $meses = array();
        
//         $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
//         $resultado = $usuariosRepositorio->consultarPorLLaves( (object) ['id' => $criteriosSeleccion->usuarioId]);
//         if($resultado->correcto())
//         {
//             $usuario = $resultado->valor;
//             $usuariosProcedimientosRepositorio = new UsuariosProcedimientosRepositorio($this->conexion);
//             for ($i = 1; $i <= 12; $i++)
//             {
//                 $criteriosSeleccionMes= (object) [
//                     'ano' =>  $criteriosSeleccion->ano,
//                     'mes' =>  $i
//                 ];
                
//                 $resultadoCumplidasMes = $this->consultarEvidenciasCumplidas($usuario, $criteriosSeleccionMes);
//                 if($resultadoCumplidasMes->correcto())
//                 {
//                     $resultadoPendientesMes = $usuariosProcedimientosRepositorio->consultarProcedimientosPendientes($usuario, $criteriosSeleccionMes);
//                     if($resultadoPendientesMes->correcto())
//                     {
//                         $mes= (object) [
//                             'mes' =>  $i,
//                             'usuarioId' => $usuario->id,
//                             'cumplidas' =>  $resultadoCumplidasMes->valor,
//                             'pendientes' =>  $resultadoPendientesMes->valor
//                         ];
//                         array_push($meses,$mes);
//                     }
//                 }
//             }
//             $resultado->valor = $meses;
//         }
        
       
        
        
        
        
//        // $filtros = array();
        
// //         $and="";
// //         if($criteriosSeleccion!=null)
// //         {
// //             if(isset($criteriosSeleccion->usuarioId))
// //                 array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'id','valor'=> $criteriosSeleccion->usuarioId]);
// //         }
// //         $where = $this->where($filtros);
        
        
// //         $columnasMeses ="";
// //         for ($i = 1; $i <= 12; $i++) 
// //         {
// //             $columnasMeses
// //         }
        
        
// //         $consulta = "SELECT P.id, P.nombre, $columnasMeses
// //                     FROM usuarios_procedimientos UP
// //                     	INNER JOIN usuarios U ON U.id = UP.usuario_id
// //                     	INNER JOIN procedimientos P ON P.id = UP.procedimiento_id" .
// //                 $where .
// //                 "  ORDER BY P.nombre";
        
// //         if($sentencia = $this->conexion->prepare($consulta))
// //         {
// //             if($this->bind_param($sentencia, $filtros))
// //             {
// //                 if($sentencia->execute())
// //                 {
// //                     if($sentencia->bind_result($id, $nombre))
// //                     {
// //                         while($sentencia->fetch())
// //                         {
// //                             $registro= (object) [
// //                                 'id' =>  $id,
// //                                 'nombre' =>  $nombre
// //                             ];
// //                             array_push($registros,$registro);
// //                         }
// //                         $resultado->valor = $registros;
// //                     }
// //                     else
// //                         $resultado->mensajeError = __FUNCTION__. '. Falló el enlace del resultado.';
// //                 }
// //                 else
// //                     $resultado->mensajeError = __FUNCTION__. '. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
// //             }
// //             else
// //                 $resultado->mensajeError = __FUNCTION__. '. Falló el enlace de parámetros';
// //         }
// //         else
// //             $resultado->mensajeError = __FUNCTION__. '. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
//             return $resultado;
//     }
    
    
//     public function consultarPorcentajesEvidencias($usuario,$criteriosSeleccion)
//     {
        
//         $resultado = new Resultado();
//         $filtros = $this->getFiltrosN($usuario,$criteriosSeleccion,false);
//         $and = $this->and($filtros);
//         $consulta = "SELECT SUM(justificadas)justificadas, SUM(enviadas)enviadas, SUM(pendientes)pendientes ".
//             "\nFROM(" .
//             $this->getConsultaEvidenciasBase($usuario,$criteriosSeleccion,$and)  .
//             "\n) AS A ";
      
            
            
//             if($sentencia = $this->conexion->prepare($consulta))
//             {
//                 if($this->bind_param($sentencia, $filtros))
//                 {
//                     if($sentencia->execute())
//                     {
//                         if($sentencia->bind_result($justificadas, $enviadas, $pendientes))
//                         {
//                             if($sentencia->fetch())
//                             {
//                                 $porcentajes = array();
//                                 array_push($porcentajes,(object)['nombre'=>'Enviadas','valor'=>intval($enviadas)]);
//                                 array_push($porcentajes,(object)['nombre'=>'Pendientes','valor'=>intval($pendientes)]);
//                                 array_push($porcentajes,(object)['nombre'=>'Justificadas','valor'=>intval($justificadas)]);
                                
//                                 $resultado->valor = $porcentajes;
//                             }
//                         }
//                         else
//                             $resultado->mensajeError = __FUNCTION__. '. Falló el enlace del resultado.';
//                     }
//                     else
//                         $resultado->mensajeError = __FUNCTION__. '. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
//                 }
//                 else
//                     $resultado->mensajeError = __FUNCTION__. '. Falló el enlace de parámetros';
//             }
//             else
//                 $resultado->mensajeError = __FUNCTION__. '. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
//                 return $resultado;
//     }
    
    
    public function consultarPorcentajesEmpresas($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        $filtros = $this->getFiltrosN($usuario,$criteriosSeleccion,false);
        $and = $this->and($filtros);
        $consulta = "SELECT empresaId, empresaNombre, empresaNombreCorto, SUM(justificadas)justificadas, SUM(enviadas)enviadas, SUM(pendientes)pendientes ".
            "\nFROM(" .
            $this->getConsultaEvidenciasBase($usuario,$criteriosSeleccion,$and)  .
            "\n) AS A " .
            "\nGROUP BY empresaId,empresaNombre,empresaNombreCorto" .
            "\nORDER BY empresaNombre";
            
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre,$nombreCorto, $justificadas, $enviadas, $pendientes))
                    {
                        while($sentencia->fetch())
                        {
                            $registro= (object) [
                                'id' =>  $id,
                                'nombre' =>  $nombre,
                                'nombreCorto' =>  $nombreCorto,
                                'justificadas' =>  $justificadas,
                                'enviadas' =>  $enviadas,
                                'pendientes' =>  $pendientes
                            ];
                            
                          
                            
                            $this->calcularPorcentaje($registro);
                            
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__. '. Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = __FUNCTION__. '. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__. '. Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = __FUNCTION__. '. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        return $resultado;
    }
    
    private function calcularPorcentaje(&$registro)
    {
        $registro->total = $registro->justificadas + $registro->enviadas + $registro->pendientes;
        $registro->cumplidas =$registro->justificadas + $registro->enviadas;
        $registro->porcentajeCumplimiento  = 0;
        if($registro->total!=0)
        {
            $registro->porcentajeCumplimiento = $registro->cumplidas  * 100 / $registro->total;
            //$registro->porcentajeCumplimiento = number_format($registro->porcentajeCumplimiento, 1, '.', '');
            
            $registro->porcentajeCumplimiento = bcdiv($registro->porcentajeCumplimiento, '1', 1);
            
            list($enteros, $decimales) = explode(".", $registro->porcentajeCumplimiento);
            if($decimales=="0")
                $registro->porcentajeCumplimiento = str_replace(".$decimales","",$registro->porcentajeCumplimiento);
            
        }
        $registro->cero = 0;
        Porcentaje::calcularPorcentaje($registro,'justificadas','total',"porcentajeJustificadas",2);
        Porcentaje::calcularPorcentaje($registro,'enviadas','total',"porcentajeEnviadas",2);
    }
    
    public function consultarPorcentajesSedes($usuario,$criteriosSeleccion)
    {
        
        $resultado = new Resultado();
        $registros = array();
        $filtros = $this->getFiltrosN($usuario,$criteriosSeleccion,false);
        $and = $this->and($filtros);
        $consulta = "SELECT sedeId, sedeNombre, sedeNombreCorto, SUM(justificadas)justificadas, SUM(enviadas)enviadas, SUM(pendientes)pendientes ".
            "\nFROM(" .
            $this->getConsultaEvidenciasBase($usuario,$criteriosSeleccion,$and)  .
            "\n) AS A " .
            "\nGROUP BY sedeId,sedeNombre,sedeNombreCorto";
            "\nORDER BY sedeNombre";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre,$nombreCorto, $justificadas, $enviadas, $pendientes))
                    {
                        while($sentencia->fetch())
                        {
                            $registro= (object) [
                                'id' =>  $id,
                                'nombre' =>  $nombre,
                                'nombreId' =>  $nombre ." (".$id.")",
                                'nombreCorto' =>  $nombreCorto,
                                'justificadas' =>  $justificadas,
                                'enviadas' =>  $enviadas,
                                'pendientes' =>  $pendientes
                            ];
                            
                            $this->calcularPorcentaje($registro);
//                             $registro->total = $registro->justificadas + $registro->enviadas + $registro->pendientes;
//                             $registro->cumplidas =$registro->justificadas + $registro->enviadas;
//                             $registro->porcentajeCumplimiento = 0;
//                             if($registro->total!=0)
//                             {
//                                 $registro->porcentajeCumplimiento = $registro->cumplidas * 100 / $registro->total;
//                                 $registro->porcentajeCumplimiento = number_format($registro->porcentajeCumplimiento, 1, '.', '');
//                             }
                            
                            
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__. '. Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = __FUNCTION__. '. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__. '. Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = __FUNCTION__. '. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
    private function getFiltros($criteriosSeleccion)
    {
        $filtros = array();
        if($criteriosSeleccion!=null)
        {
            if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'EM', 'campo'=>'id','valor'=>$criteriosSeleccion->empresaId]);
            if(isset($criteriosSeleccion->sedeId) && $criteriosSeleccion->sedeId!="")
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'S', 'campo'=>'id','valor'=> $criteriosSeleccion->sedeId]);
            if(isset($criteriosSeleccion->departamentoId) && $criteriosSeleccion->departamentoId!="")
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'D', 'campo'=>'id','valor'=> $criteriosSeleccion->departamentoId]);
        }
        return $filtros;
    }
    
    
    
    public function consultarPorcentajesAreas($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $filtros = $this->getFiltrosN($usuario, $criteriosSeleccion,false);
        $and = $this->and($filtros);
        
        $consulta = "SELECT departamentoId, departamentoNombre,SUM(justificadas)justificadas, SUM(enviadas)enviadas, SUM(pendientes)pendientes ".
                    "\nFROM(" .
                   $this->getConsultaEvidenciasBase($usuario,$criteriosSeleccion,$and)  .
                   "\n) AS A " .
                   "\nGROUP BY departamentoId,departamentoNombre";
                   "\nORDER BY departamentoNombre";
                   
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
          // if($sentencia->bind_param('i',$usuario->empresaId))
           if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre,$justificadas, $enviadas, $pendientes))
                    {
                        while($sentencia->fetch())
                        {
                            $registro= (object) [
                                'id' =>  $id,
                                'nombre' =>  $nombre,
                                'nombreId' =>  $nombre ." (".$id.")",
                                'justificadas' =>  $justificadas,
                                'enviadas' =>  $enviadas,
                                'pendientes' =>  $pendientes
                            ];
                            
//                             $registro->total = $registro->justificadas + $registro->enviadas + $registro->pendientes;
//                             $registro->cumplidas =$registro->justificadas + $registro->enviadas;
//                             $registro->porcentajeCumplimiento  = 0;
//                             if($registro->total!=0)
//                             {
//                                 $registro->porcentajeCumplimiento = $registro->cumplidas  * 100 / $registro->total;
//                                 $registro->porcentajeCumplimiento = number_format($registro->porcentajeCumplimiento, 1, '.', '');
//                             }
                            $this->calcularPorcentaje($registro);
                            
                            
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__. '. Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = __FUNCTION__. '. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__. '. Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = __FUNCTION__. '. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        return $resultado;
    }
    
   
    
    public function getFiltroEstructura($usuario)
    {
        $filtros = array();
        switch ($usuario->tipoUsuarioId)
        {
            case \TipoUsuario::USUARIO:
                //$and =" AND U.id = $usuario->id";
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'id','operador'=>'IN','valor'=>$usuario->id]);
            break;
            case \TipoUsuario::SUPERVISOR:
                $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
                $resultado = $usuariosRepositorio->consultarIdsUsuarios($usuario);
                if($resultado->correcto())
                {
                    $usuariosIds = implode(",", $resultado->valor);
                    //$and =" AND U.id IN($usuariosIds)";
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'id','operador'=>'IN','valor'=>$usuariosIds]);
                }
            break;
            case \TipoUsuario::COORDINADOR:
                $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
                $resultado = $usuariosRepositorio->consultarIdsEmpresas($usuario->empresaId);
                if($resultado->correcto())
                {
                    $empresasIds = implode(",", $resultado->valor);
                    //$and =" AND EM.id IN($empresasIds)";
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'EM', 'campo'=>'id','operador'=>'IN','valor'=>$empresasIds]);
                }
            break;
            case \TipoUsuario::ADMINISTRADOR:
            break;
        }
        return $filtros;
    }
    
    public function getConsultaEvidenciasBase($usuario,$criteriosSeleccion,$and)
    {
      
        
        $campos = array();
        array_push($campos,(object)['tabla'=>'U','campo'=>'id','alias'=>'id']);
        array_push($campos,(object)['tabla'=>'U','campo'=>'nombre','alias'=>'nombre']);
        array_push($campos,(object)['tabla'=>'U','campo'=>'nombre_usuario','alias'=>'nombreUsuario']);
        array_push($campos,(object)['tabla'=>'U','campo'=>'apellido','alias'=>'apellido']);
        array_push($campos,(object)['tabla'=>'U','campo'=>'tipo_usuario_id','alias'=>'tipoUsuarioId']);
        array_push($campos,(object)['tabla'=>'EM','campo'=>'id','alias'=>'empresaId']);
        array_push($campos,(object)['tabla'=>'EM','campo'=>'nombre','alias'=>'empresaNombre']);
        array_push($campos,(object)['tabla'=>'EM','campo'=>'nombre_corto','alias'=>'empresaNombreCorto']);
        array_push($campos,(object)['tabla'=>'S','campo'=>'id','alias'=>'sedeId']);
        array_push($campos,(object)['tabla'=>'S','campo'=>'nombre','alias'=>'sedeNombre']);
        array_push($campos,(object)['tabla'=>'S','campo'=>'nombre_corto','alias'=>'sedeNombreCorto']);
        array_push($campos,(object)['tabla'=>'A','campo'=>'id','alias'=>'areaId']);
        array_push($campos,(object)['tabla'=>'A','campo'=>'nombre','alias'=>'areaNombre']);
        array_push($campos,(object)['tabla'=>'D','campo'=>'id','alias'=>'departamentoId']);
        array_push($campos,(object)['tabla'=>'D','campo'=>'nombre','alias'=>'departamentoNombre']);
        
        $primerDiaMes = "$criteriosSeleccion->ano-$criteriosSeleccion->mes-1";
        $ultimoDiaMes = date("Y-m-t", strtotime($primerDiaMes));
        $filtroAno1 = "";
        $filtroMes1 = "";
        $filtroAno2 = "";
        $filtroMes2 = "";
        if(isset($criteriosSeleccion->ano))
        {
            $filtroAno1 = "AND YEAR(E1.fecha_alta) = $criteriosSeleccion->ano";
            $filtroAno2 = "AND YEAR(E2.fecha_alta) = $criteriosSeleccion->ano";
        }
        if(isset($criteriosSeleccion->mes))
        {
            $filtroMes1 = "AND MONTH(E1.fecha_alta) = $criteriosSeleccion->mes";
            $filtroMes2 = "AND MONTH(E2.fecha_alta) = $criteriosSeleccion->mes";
        }
        
        $select = $this->selectAlias($campos);
        $consulta = $select.",
                    (
                    	SELECT count(*) numero
                    	FROM evidencias E1
                    		INNER JOIN usuarios_procedimientos UP1 ON UP1.id = E1.usuario_procedimiento_id
                    		INNER JOIN usuarios U1 ON U1.id = UP1.usuario_id
                    		INNER JOIN sedes S1 ON S1.id = U1.sede_id
                    		INNER JOIN empresas EM1 ON EM1.id = S1.empresa_id
                    		LEFT JOIN areas A1 ON A1.id = U1.area_id
                            INNER JOIN departamentos D1 ON D1.id = U1.departamento_id
                    	WHERE justificacion_id IS NOT NULL AND EM1.id = EM.id AND A1.id = A.id AND D1.id = D.id AND U1.id = U.id  $filtroAno1 $filtroMes1
                    ) justificadas,
                    (
                    	SELECT count(*) numero
                    	FROM evidencias E1
                    		INNER JOIN usuarios_procedimientos UP1 ON UP1.id = E1.usuario_procedimiento_id
                    		INNER JOIN usuarios U1 ON U1.id = UP1.usuario_id
                    		INNER JOIN sedes S1 ON S1.id = U1.sede_id
                    		INNER JOIN empresas EM1 ON EM1.id = S1.empresa_id
                    		LEFT JOIN areas A1 ON A1.id = U1.area_id
                            INNER JOIN departamentos D1 ON D1.id = U1.departamento_id
                    	WHERE justificacion_id IS NULL AND EM1.id = EM.id AND A1.id = A.id AND D1.id = D.id AND U1.id = U.id $filtroAno1 $filtroMes1
                    ) enviadas,
                    (
                    	SELECT count(*)
                    	FROM usuarios_procedimientos UP1
                    		INNER JOIN procedimientos P1 ON P1.id = UP1.procedimiento_id
                    		INNER JOIN usuarios U1 ON U1.id = UP1.usuario_id
                    		INNER JOIN sedes S1 ON S1.id = U1.sede_id
                    		INNER JOIN empresas EM1 ON EM1.id = S1.empresa_id
                    		LEFT JOIN areas A1 ON A1.id = U1.area_id
                           INNER JOIN departamentos D1 ON D1.id = U1.departamento_id
                    	 WHERE P1.estatus = 1 AND ((UP1.estatus = 1 AND UP1.fecha_alta  <=  '$ultimoDiaMes') OR (UP1.estatus = 0 AND MONTH(UP1.fecha_alta)  <=  $criteriosSeleccion->mes AND  YEAR(UP1.fecha_alta) <= $criteriosSeleccion->ano AND MONTH(UP1.fecha_cancelacion) > $criteriosSeleccion->mes AND  YEAR(UP1.fecha_cancelacion) >= $criteriosSeleccion->ano))
                                AND EM1.id = EM.id AND A1.id = A.id AND D1.id = D.id AND U1.id = U.id 
                    		AND UP1.id NOT IN(
                    				SELECT usuario_procedimiento_id
                    				FROM evidencias E2
                    					INNER JOIN usuarios_procedimientos UP2 ON UP2.id = E2.usuario_procedimiento_id
                    					INNER JOIN usuarios U2 ON U2.id = UP2.usuario_id
                    					INNER JOIN sedes S2 ON S2.id = U2.sede_id
                    					INNER JOIN empresas EM2 ON EM2.id = S2.empresa_id
                    					LEFT JOIN areas A2 ON A2.id = U2.area_id
                                        INNER JOIN departamentos D2 ON D2.id = U2.departamento_id
                    				WHERE  EM2.id = EM1.id AND A2.id = A1.id AND D2.id = D1.id AND U1.id = U.id  $filtroAno2 $filtroMes2
                    				)
                    				
                    )pendientes
                    FROM usuarios U
                    	INNER JOIN usuarios_procedimientos UP ON U.id = UP.usuario_id
                    	INNER JOIN sedes S ON S.id = U.sede_id
                    	INNER JOIN empresas EM ON EM.id = S.empresa_id
                    	LEFT JOIN areas A ON A.id = U.area_id
                        INNER JOIN departamentos D ON D.id = U.departamento_id
                    WHERE UP.estatus = 1 
                        AND U.estatus = 1 
                        AND S.estatus = 1 
                        AND EM.estatus = 1 
                        AND A.estatus = 1
                        AND D.estatus = 1
                        AND U.permiso_saha = 1
                  ";
      
        $consulta .=  $and . " ";
        
        $consulta .="\n".$this->groupBy($campos);

        return $consulta;
    }
    
    public function getFiltrosN($usuario, $criteriosSeleccion, $agregarFiltrosFecha)
    {
        $filtros = array();
        switch ($usuario->tipoUsuarioId)
        {
            case \TipoUsuario::USUARIO:
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'id','operador'=>'IN','valor'=>$usuario->id]);
                break;
            case \TipoUsuario::SUPERVISOR:
                if(isset($criteriosSeleccion->usuarioId) && $criteriosSeleccion->usuarioId!="")
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'id','valor'=>$criteriosSeleccion->usuarioId]);
                else
                {
                    $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
                    $resultado = $usuariosRepositorio->consultarIdsUsuarios($usuario);
                    if($resultado->correcto())
                    {
                        $usuariosIds = implode(",", $resultado->valor);
                        array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'id','operador'=>'IN','valor'=>$usuariosIds]);
                    }
                }
            break;
            case \TipoUsuario::COORDINADOR:
                if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'EM', 'campo'=>'id','valor'=>$criteriosSeleccion->empresaId]);
                else
                {
                    $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
                    $resultado = $usuariosRepositorio->consultarIdsEmpresas($usuario->empresaId);
                    if($resultado->correcto())
                    {
                        $empresasIds = implode(",", $resultado->valor);
                        array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'EM', 'campo'=>'id','operador'=>'IN','valor'=>$empresasIds]);
                    }
                }
                break;
            case \TipoUsuario::ADMINISTRADOR:
                if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'EM', 'campo'=>'id','valor'=>$criteriosSeleccion->empresaId]);
                
            break;
        }
        if(isset($criteriosSeleccion->sedeId) && $criteriosSeleccion->sedeId!="")
            array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'S', 'campo'=>'id','valor'=> $criteriosSeleccion->sedeId]);
        if(isset($criteriosSeleccion->departamentoId) && $criteriosSeleccion->departamentoId!="")
            array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'D', 'campo'=>'id','valor'=> $criteriosSeleccion->departamentoId]);
        if($agregarFiltrosFecha)
        {
            if(isset($criteriosSeleccion->ano)  && $criteriosSeleccion->ano!="")
                array_push($filtros,(object)['tipoDato'=>'int','campo'=>'YEAR(E.fecha_alta)','valor'=>$criteriosSeleccion->ano]);
            if(isset($criteriosSeleccion->mes)  && $criteriosSeleccion->mes!="")
                array_push($filtros,(object)['tipoDato'=>'int','campo'=>'MONTH(E.fecha_alta)','valor'=>$criteriosSeleccion->mes]);
        }
            
        return $filtros;
    }
    
    public function consultarPorcentajesUsuarios($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
//         $filtros = $this->getFiltros($criteriosSeleccion);
//         $and = $this->and($filtros);
        $filtros = $this->getFiltrosN($usuario, $criteriosSeleccion,false);
        $and = $this->and($filtros);
        $consulta = "SELECT id, nombre, nombreUsuario,apellido, tipoUsuarioId, empresaId, empresaNombre, sedeId, sedeNombre, areaId, areaNombre, departamentoId, departamentoNombre, SUM(justificadas)justificadas, SUM(enviadas)enviadas, SUM(pendientes)pendientes ".
            "\nFROM(" .
            $this->getConsultaEvidenciasBase($usuario,$criteriosSeleccion,$and)  .
            "\n) AS A " .
            "\nGROUP BY id, nombre, nombreUsuario,apellido, tipoUsuarioId, empresaId, empresaNombre, sedeId, sedeNombre, areaId, areaNombre, departamentoId, departamentoNombre".
            "\nORDER BY  FIELD(id,$usuario->id) DESC, nombre,apellido";
        
            
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre, $nombreUsuario, $apellido, $tipoUsuarioId, $empreasaId,$empresaNombre, $sedeId, $sedeNombre, $areaId, $areaNombre, $departamentoId, $departamentoNombre, $justificadas, $enviadas, $pendientes))
                    {
                        while($sentencia->fetch())
                        {
                            
                            $registro= (object) [
                                'id' =>  $id,
                                'nombreUsuario' => $nombreUsuario,
                                'nombre' =>  $nombre,
                                'apellido' =>  $apellido,
                                'tipoUsuarioId' =>  $tipoUsuarioId,
                                'empresaId' =>  $empreasaId,
                                'empresaNombre' =>  $empresaNombre,
                                'sedeId' =>  $sedeId,
                                'sedeNombre' =>  $sedeNombre,
                                'areaId' =>  $areaId,
                                'areaNombre' =>  $areaNombre,
                                'departamentoId' =>  $departamentoId,
                                'departamentoNombre' =>  $departamentoNombre,
                                'justificadas' =>  $justificadas,
                                'enviadas' =>  $enviadas,
                                'pendientes' =>  $pendientes
                            ];
                            
                          
                            
                            $this->calcularPorcentaje($registro);
                            
                            $registro->nombreCompleto = $registro->nombre . " " . $registro->apellido;
                            $registro->nombreId =  $registro->nombreCompleto ." (".$registro->id.")";
                            $registro->fotoPerfil =  "../fotos/usuario". $registro->id .".jpg";
                            if(file_exists($registro->fotoPerfil))
                                $registro->fotoPerfil =  "php/fotos/usuario". $registro->id .".jpg";
                            else
                                $registro->fotoPerfil =  "php/fotos/default.jpg";
                                
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__.'. Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = __FUNCTION__.' .Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__.'. Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = __FUNCTION__.'. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        return $resultado;
    }
   
    
//     public function consultarPorcentajes($campos,$usuario,$criteriosSeleccion)
//     {
//         $resultado = new Resultado();
//         $registros = array();
        
//         $filtros = array();
        
//         $consulta = $this->getConsultaEvidenciasBase($usuario,$criteriosSeleccion);
//         $consulta .= $this->getFiltroEstructura($usuario) ." ";
        
//         $consulta .="\n".$this->groupBy($campos);
//         $consulta .= "\n".$this->orderBy($campos);
        
        
//         if($sentencia = $this->conexion->prepare($consulta))
//         {
//             if($this->bind_param($sentencia, $filtros))
//             {
//                 if($sentencia->execute())
//                 {
//                     if($sentencia->bind_result($id, $nombre, $nombreUsuario, $apellido, $tipoUsuarioId, $empreasaId,$empresaNombre, $sedeId, $sedeNombre, $areaId, $areaNombre, $departamentoId, $departamentoNombre, $justificadas, $enviadas, $pendientes))
//                     {
//                         while($sentencia->fetch())
//                         {
                            
//                             $registro= (object) [
//                                 'id' =>  $id,
//                                 'nombreUsuario' => $nombreUsuario,
//                                 'nombre' =>  $nombre,
//                                 'apellido' =>  $apellido,
//                                 'tipoUsuarioId' =>  $tipoUsuarioId,
//                                 'empresaId' =>  $empreasaId,
//                                 'empresaNombre' =>  $empresaNombre,
//                                 'sedeId' =>  $sedeId,
//                                 'sedeNombre' =>  $sedeNombre,
//                                 'areaId' =>  $areaId,
//                                 'areaNombre' =>  $areaNombre,
//                                 'departamentoId' =>  $departamentoId,
//                                 'departamentoNombre' =>  $departamentoNombre,
//                                 'justificadas' =>  $justificadas,
//                                 'enviadas' =>  $enviadas,
//                                 'pendientes' =>  $pendientes
//                             ];
                            
//                             $registro->total = $registro->justificadas + $registro->enviadas + $registro->pendientes;
//                             $registro->cumplidas =$registro->justificadas + $registro->enviadas;
//                             $registro->porcentajeCumplimiento  = 0;
//                             if($registro->total !=0)
//                             {
//                                 $registro->porcentajeCumplimiento = $registro->cumplidas  * 100 / $registro->total ;
//                                 $registro->porcentajeCumplimiento = number_format($registro->porcentajeCumplimiento, 1, '.', '');
//                             }
                            
//                             $registro->nombreCompleto = $registro->usuarioNombre . " " . $registro->apellido;
//                             $registro->nombreId =  $registro->nombreCompleto ." (".$registro->id.")";
//                             $registro->fotoPerfil =  "../fotos/usuario". $registro->id .".jpg";
//                             if(file_exists($registro->fotoPerfil))
//                                 $registro->fotoPerfil =  "php/fotos/usuario". $registro->id .".jpg";
//                             else
//                                 $registro->fotoPerfil =  "php/fotos/default.jpg";
                                    
                                    
//                             array_push($registros,$registro);
//                         }
//                         $resultado->valor = $registros;
//                     }
//                     else
//                         $resultado->mensajeError = __FUNCTION__.'. Falló el enlace del resultado.';
//                 }
//                 else
//                     $resultado->mensajeError = __FUNCTION__.' .Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
//             }
//             else
//                 $resultado->mensajeError = __FUNCTION__.'. Falló el enlace de parámetros';
//         }
//         else
//             $resultado->mensajeError = __FUNCTION__.'. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
//             return $resultado;
//     }
    
    public function consultarAnosMeses($criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
        $resultado = $usuariosRepositorio->consultarPorLLaves((object) ["id"=>$criteriosSeleccion->supervisorCoordinadorId]);
        if($resultado->correcto())
        {
            $usuario = $resultado->valor;
            $filtros = $this->getFiltrosN($usuario,$criteriosSeleccion,false);
            $where = $this->where($filtros);
            
            $consulta = "SELECT YEAR(E.fecha_alta) ano, MONTH(E.fecha_alta) mes
                    FROM evidencias E
                        INNER JOIN usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
                        INNER JOIN usuarios U ON U.id = UP.usuario_id
                        INNER JOIN sedes S ON S.id = U.sede_id
                        INNER JOIN empresas EM ON EM.id = S.empresa_id
                        INNER JOIN departamentos D ON D.id = U.departamento_id";
            
            $consulta .= $where;
            
            $consulta.=" GROUP BY ano, mes
                    ORDER BY ano desc, mes desc";
            
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($this->bind_param($sentencia, $filtros))
                {
                    if($sentencia->execute())
                    {
                        if($sentencia->bind_result($ano, $mes))
                        {
                            while($sentencia->fetch())
                            {
                                $mesNombre = $this->getNombreMes($mes);
                                $registro= (object) [
                                    'ano' =>  $ano,
                                    'mes' =>  $mes,
                                    'mesNombre' =>  $mesNombre
                                ];
                                array_push($registros,$registro);
                            }
                            $resultado->valor = $registros;
                        }
                        else
                            $resultado->mensajeError = __FUNCTION__. '. Falló el enlace del resultado.';
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__. '. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
                }
                else
                    $resultado->mensajeError = __FUNCTION__. '. Falló el enlace de parámetros';
            }
            else
                $resultado->mensajeError = __FUNCTION__. '. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        }
        
        return $resultado;
    }
    
    public function consultarAnos($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $filtros = $this->getFiltrosN($usuario,$criteriosSeleccion,false);
        $where = $this->where($filtros);
        
        $consulta = "SELECT YEAR(E.fecha_alta) ano
                    FROM procesos_revisados E
                        INNER JOIN  usuarios_procesos UP ON UP.id = E.usuario_proceso_id
                        INNER JOIN usuarios U ON U.id = UP.usuario_id
                        LEFT JOIN sedes S ON S.id = U.sede_id
                        LEFT JOIN empresas EM ON EM.id = S.empresa_id ";
        
        $consulta .= $where;
        
        $consulta.=" GROUP BY ano
                    ORDER BY ano";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($ano))
                    {
                        while($sentencia->fetch())
                        {
                            $registro= (object) [
                                'id' =>  $ano,
                                'nombre' =>  $ano
                            ];
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__. '. Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = __FUNCTION__. '. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__. '. Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = __FUNCTION__. '. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
    private function getNombreMes($mes)
    {
        $nombreMes="";
        switch ($mes)
        {
            case 1: $nombreMes = "Enero"; break;
            case 2: $nombreMes = "Febrero"; break;
            case 3: $nombreMes = "Marzo"; break;
            case 4: $nombreMes = "Abril"; break;
            case 5: $nombreMes = "Mayo"; break;
            case 6: $nombreMes = "Junio"; break;
            case 7: $nombreMes = "Julio"; break;
            case 8: $nombreMes = "Agosto"; break;
            case 9: $nombreMes = "Septiembre"; break;
            case 10: $nombreMes = "Octubre"; break;
            case 11: $nombreMes = "Noviembre"; break;
            case 12: $nombreMes = "Diciembre"; break;
         }
         return $nombreMes;
    }
    
    public function consultarUsuariosConProcedimientosAsignados($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $filtros =  array();
        
        $consulta = "SELECT U.id,U.nombre, U.apellido, U.tipo_usuario_id
                    FROM usuarios_procedimientos UP
                    	LEFT JOIN usuarios U ON U.id = UP.usuario_id
                    	LEFT JOIN sedes S ON S.id = U.sede_id
                    	LEFT JOIN empresas EM ON EM.id = S.empresa_id
                    	LEFT JOIN areas A ON A.id = U.area_id
                    WHERE  UP.estatus = 1 AND EM.id = ? AND A.id = ? 
                    GROUP BY U.id, U.nombre";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('ii',$usuario->empresaId,$usuario->areaId))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre, $apellido, $tipoUsuarioId))
                    {
                        while($sentencia->fetch())
                        {
                            $registro= (object) [
                                'id' =>  $id,
                                'nombre' =>  $nombre,
                                'apellido' =>  $apellido,
                                'tipoUsuarioId' => $tipoUsuarioId
                            ];
                            
                            
                            $registro->nombreCompleto = $registro->nombre . " " . $registro->apellido;
                            $registro->fotoPerfil =  "../fotos/usuario". $registro->id .".jpg";
                            if(file_exists($registro->fotoPerfil))
                                $registro->fotoPerfil =  "php/fotos/usuario". $registro->id .".jpg";
                            else
                                $registro->fotoPerfil =  "php/fotos/default.jpg";
                                
                                    
                                    array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__. '. Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = __FUNCTION__. '. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__. '. Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = __FUNCTION__. '. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
    public function consultarPorcentajesAdministradores($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $filtros =  array();
        
        $consulta = "SELECT V.id, V.nombre, V.apellido,
                 (
                	SELECT count(*) numero
                	FROM evidencias E1
                		INNER JOIN usuarios_procedimientos UP1 ON UP1.id = E1.usuario_procedimiento_id
                		INNER JOIN usuarios U1 ON U1.id = UP1.usuario_id
                		LEFT JOIN sedes S1 ON S1.id = U1.sede_id
                		LEFT JOIN empresas EM1 ON EM1.id = S1.empresa_id
                		LEFT JOIN usuarios V1 ON V1.id = EM1.administrador_id
                	WHERE MONTH(E1.fecha_alta) = MONTH(E.fecha_alta)  AND YEAR(E1.fecha_alta) = YEAR(E.fecha_alta) AND E1.validada=1 AND V1.id = V.id
                ) validadas,
                (		SELECT count(*) numero
                	FROM evidencias E1
                		INNER JOIN usuarios_procedimientos UP1 ON UP1.id = E1.usuario_procedimiento_id
                		INNER JOIN usuarios U1 ON U1.id = UP1.usuario_id
                		LEFT JOIN sedes S1 ON S1.id = U1.sede_id
                		LEFT JOIN empresas EM1 ON EM1.id = S1.empresa_id
                		LEFT JOIN usuarios V1 ON V1.id = EM1.administrador_id
                	WHERE MONTH(E1.fecha_alta) = MONTH(E.fecha_alta)  AND YEAR(E1.fecha_alta) = YEAR(E.fecha_alta) AND E1.validada=0 AND V1.id = V.id
                ) noValidadas 
                FROM evidencias E
                	INNER JOIN  usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
                	INNER JOIN usuarios U ON U.id = UP.usuario_id
                	LEFT JOIN sedes S ON S.id = U.sede_id
                	LEFT JOIN empresas EM ON EM.id = S.empresa_id
                	INNER JOIN procedimientos P ON P.id = UP.procedimiento_id
                	LEFT JOIN justificaciones J ON J.id = E.justificacion_id
                	LEFT JOIN usuarios V ON V.id = EM.administrador_id
                WHERE MONTH(E.fecha_alta) =$criteriosSeleccion->mes AND YEAR(E.fecha_alta) = $criteriosSeleccion->ano AND V.id  IS NOT NULL 
                GROUP BY V.id, V.nombre, V.apellido";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre,$apellido, $validadas, $noValidadas))
                    {
                        while($sentencia->fetch())
                        {
                            $registro= (object) [
                                'id' =>  $id,
                                'nombre' =>  $nombre,
                                'apellido' =>  $apellido,
                                'validadas' =>  $validadas,
                                'noValidadas' =>  $noValidadas
                            ];
                            
                            $registro->total = $registro->validadas + $registro->noValidadas;
                            $cumplimieto =$registro->validadas;
                            $registro->porcentajeCumplimiento = 0;
                            if($registro->total!=0)
                            {
                               $registro->porcentajeCumplimiento = $cumplimieto * 100 / $registro->total;
                               $registro->porcentajeCumplimiento = bcdiv($registro->porcentajeCumplimiento, '1', 1);
                               list($enteros, $decimales) = explode(".", $registro->porcentajeCumplimiento);
                               if($decimales=="0")
                                   $registro->porcentajeCumplimiento = str_replace(".$decimales","",$registro->porcentajeCumplimiento);
                               //$registro->porcentajeCumplimiento = number_format($registro->porcentajeCumplimiento, 1, '.', '');
                            }
                               
                            
                            $registro->nombreCompleto = $registro->nombre . " " . $registro->apellido;
                            $registro->fotoPerfil =  "../fotos/usuario". $registro->id .".jpg";
                            if(file_exists($registro->fotoPerfil))
                                $registro->fotoPerfil =  "php/fotos/usuario". $registro->id .".jpg";
                            else
                                $registro->fotoPerfil =  "php/fotos/default.jpg";
                                    
                            
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__. '. Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = __FUNCTION__. '. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__. '. Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = __FUNCTION__. '. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
    private function getFiltrosUsuario($usuario,$criteriosSeleccion,$agregarCriteriosSeleccion)
    {
        $filtros = array();
        switch ($usuario->tipoUsuarioId)
        {
            case \TipoUsuario::USUARIO:
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'UP', 'campo'=>'usuario_id','valor'=>$usuario->id]);
            break;
            case \TipoUsuario::SUPERVISOR:
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'empresa_id','valor'=>$usuario->empresaId]);
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'sede_id','valor'=>$usuario->sedeId]);
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'area_id','valor'=>$usuario->areaId]);
            break;
            case \TipoUsuario::COORDINADOR:
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'empresa_id','valor'=>$usuario->empresaId]);
            break;
            case \TipoUsuario::ADMINISTRADOR:
                if($criteriosSeleccion!=null)
                {
                    if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
                        array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'empresa_id','valor'=>$criteriosSeleccion->empresaId]);
                    if(isset($criteriosSeleccion->sedeId) && $criteriosSeleccion->sedeId!="")
                        array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'sede_id','valor'=>$criteriosSeleccion->sedeId]);
                        if(isset($criteriosSeleccion->areaId) && $criteriosSeleccion->areaId!="")
                        array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'area_id','valor'=>$criteriosSeleccion->areaId]);
                }
            break;
        }
        if($criteriosSeleccion!=null && $agregarCriteriosSeleccion)
        {
            if(isset($criteriosSeleccion->ano)  && $criteriosSeleccion->ano!="")
                array_push($filtros,(object)['tipoDato'=>'int','campo'=>'YEAR(E.fecha_alta)','valor'=>$criteriosSeleccion->ano]);
            if(isset($criteriosSeleccion->mes)  && $criteriosSeleccion->mes!="")
                array_push($filtros,(object)['tipoDato'=>'int','campo'=>'MONTH(E.fecha_alta)','valor'=>$criteriosSeleccion->mes]);
        }
        
        return $filtros;
    }
    
    public function consultarEvidenciasJustificacion($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
//         $filtros = $this->getFiltrosUsuario($usuario, $criteriosSeleccion,true);
        
//         $where = $this->where($filtros);
        $filtros = $this->getFiltrosN($usuario,$criteriosSeleccion,true);
        $where = $this->and($filtros);
        $consulta = "SELECT justificacion_id, J.nombre, count(justificacion_id) valor
            FROM evidencias E
            		INNER JOIN usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
            		INNER JOIN procedimientos P ON P.id = UP.procedimiento_id
                    INNER JOIN justificaciones J ON J.id = E.justificacion_id
                    INNER JOIN usuarios U ON U.id = UP.usuario_id
                    INNER JOIN sedes S ON S.id = U.sede_id
                    INNER JOIN empresas EM ON EM.id = S.empresa_id
                     $where ";
       $consulta.= "GROUP BY justificacion_id, J.nombre";
       
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre,$valor))
                    {
                        while($sentencia->fetch())
                        {
                            $procedimiento= (object) [
                                'id' =>  $id,
                                'nombre' =>  $nombre,
                                'valor' =>  $valor
                            ];
                            array_push($registros,$procedimiento);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__. '. Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = __FUNCTION__. '. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__. '. Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = __FUNCTION__. '. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
   
    
    

    public function actualizar(ProcesoRevisado $modelo,$nombreArchivoSubido)
    {
//         if($modelo->justificacionId=="")
//             $modelo->justificacionId=null;
        $resultado = new Resultado();
      
//         $consulta = "UPDATE evidencias
//                      SET 
//                          realizo_actividad = ?,
//                          justificacion_id = ?,
//                          comentarios = ?,
//                          fecha_modificacion = NOW(),
//                          nombre_archivo = ?
//                      WHERE id = ?";
//         if($sentencia = $this->conexion->prepare($consulta))
//         {
//             if($sentencia->bind_param('iisss',$modelo->realizoActividad, $modelo->justificacionId, $modelo->comentarios,$nombreArchivoSubido,$modelo->id))
//             {
//                 if($sentencia->execute())
//                 {
//                         $resultado->valor=true;
//                 }
//                 else
//                     $resultado->mensajeError = __FUNCTION__. '. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
//             }
//             else
//                 $resultado->mensajeError = __FUNCTION__. '. Falló el enlace de parámetros';
//         }
//         else
//             $resultado->mensajeError = __FUNCTION__. '. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        
      
            
        return $resultado;
    }
    
    public function validarEvidencia($usuario,Evidencia $modelo)
    {
        ini_set('max_execution_time', 300);
        $this->conexion->autocommit(FALSE);
        
        if($modelo->justificacionId=="")
            $modelo->justificacionId=null;
            $resultado = new Resultado();
            $consulta = "UPDATE evidencias
                     SET
                         validada = ?,
                         comentarios_validacion = ?,
                         fecha_modificacion = NOW(),
                         validacion_usuario_id = ?
                     WHERE id = ?";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('isii',$modelo->validada, $modelo->comentariosValidacion,$usuario->id,$modelo->id))
                {
                    if($sentencia->execute())
                    {
                        
                        $comentariosRepositorio = new EvidenciasComentariosRepositorio($this->conexion);
                        $comentario= new EvidenciaComentario();
                        $comentario->usuarioId = $usuario->id;
                        $comentario->evidenciaId = $modelo->id;
                        $comentario->comentario =  $modelo->comentariosValidacion;
                        
                        $resultado = $comentariosRepositorio->insertar($usuario,$comentario);
                        if($resultado->correcto())
                        {
                            $resultado->valor=$modelo->id;
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
            $this->conexion->commit();
        else
            $this->conexion->rollback();
        return $resultado;
    }
    
    public function validarSinCambios($usuario, $ids)
    {
        ini_set('max_execution_time', 0);
        $this->conexion->autocommit(FALSE);
        
            $resultado = new Resultado();
            $estatusId= \EstatusValidacionProceso::AUTORIZADO;
            $consulta = "UPDATE procesos_revisados
                     SET
                         estatus_validacion_id = $estatusId,
                         comentarios_validacion = '',
                         fecha_modificacion = NOW(),
                         fecha_validacion = NOW(),
                         validacion_usuario_id = ?
                     WHERE id IN ($ids)";
            
            //var_dump($consulta);
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('i',$usuario->id))
                {
                    if($sentencia->execute())
                    {
                        
//                         $comentariosRepositorio = new EvidenciasComentariosRepositorio($this->conexion);
//                         $comentario= new EvidenciaComentario();
//                         $comentario->usuarioId = $usuario->id;
//                         $comentario->evidenciaId = $modelo->id;
//                         $comentario->comentario =  $modelo->comentariosValidacion;
                        
//                         $resultado = $comentariosRepositorio->insertar($usuario,$comentario);
//                         if($resultado->correcto())
//                         {
//                             $resultado->valor=$modelo->id;
//                         }
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
                $this->conexion->commit();
            else
                $this->conexion->rollback();
            return $resultado;
    }
    
    public function actualizarEstatusValidacionProceso($usuario, $procesoRevisadoId, $estatusValidacionId)
    {
        ini_set('max_execution_time', 0);
        $this->conexion->autocommit(FALSE);
        
        $resultado = new Resultado();
        $consulta = "UPDATE procesos_revisados
                     SET
                         estatus_validacion_id = ?,
                         fecha_modificacion = NOW(),
                         fecha_validacion = NOW(),
                         validacion_usuario_id = ?
                     WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('iii',$estatusValidacionId, $usuario->id, $procesoRevisadoId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                    $resultado = $this->calcularId('id','historial_procesos_revisados');
                    if($resultado->correcto())
                    {
                        $id = $resultado->valor;
                        //echo $id;
                        $consulta = "INSERT INTO historial_procesos_revisados(id, proceso_revisado_id, fecha_validacion, estatus_validacion_id, usuario_validador_id) " .
                            "VALUE(?, ?, NOW(), ?, ?)";
                        if($sentencia = $this->conexion->prepare($consulta))
                        {
                            if($sentencia->bind_param("iiii", $id, $procesoRevisadoId, $estatusValidacionId, $usuario->id))
                            {
                                if($sentencia->execute())
                                {
                                    $resultado = $this->consultarPorLlaves((object)["id"=>$procesoRevisadoId]);
                                    if($resultado->correcto())
                                    {
                                        $procesoRevisado = $resultado->valor;
                                        $repositorio = new EstatusValidacionProcesosRepositorio($this->conexion);
                                        $resultado = $repositorio->consultarPorLlaves((object)["id" => $estatusValidacionId]);
                                        if($resultado->correcto())
                                        {
                                            $estatusValidacion = $resultado->valor;
                                            $resultado = $this->enviarNotificacionCambioEstatus($procesoRevisado, $usuario, $estatusValidacion);
                                            
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
            $this->conexion->commit();
        else
            $this->conexion->rollback();
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

//                 if(isset($criteriosSeleccion->validada) && $criteriosSeleccion->validada!="")
//                     array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'E', 'campo'=>'validada','valor'=> $criteriosSeleccion->validada]);
//                 if(isset($criteriosSeleccion->justificada) && $criteriosSeleccion->justificada!="")
//                 {
//                     if($criteriosSeleccion->justificada==1)
//                         array_push($filtros,(object)['tipo'=>'estatico','texto'=>'E.justificacion_id is not null']);
//                     else  if($criteriosSeleccion->justificada==0)
//                         array_push($filtros,(object)['tipo'=>'estatico','texto'=>'E.justificacion_id is null']);
//                 }
                if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'empresa_id','valor'=>$criteriosSeleccion->empresaId]);
                if(isset($criteriosSeleccion->sedeId) && $criteriosSeleccion->sedeId!="")
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'sede_id','valor'=>$criteriosSeleccion->sedeId]);
                if(isset($criteriosSeleccion->areaId) && $criteriosSeleccion->areaId!="")
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'area_id','valor'=>$criteriosSeleccion->areaId]);
                if(isset($criteriosSeleccion->usuarioId) && $criteriosSeleccion->usuarioId!="")
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'UP', 'campo'=>'usuario_id','valor'=>$criteriosSeleccion->usuarioId]);
                if(isset($criteriosSeleccion->administradorId)  && $criteriosSeleccion->administradorId!="")
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'EM', 'campo'=>'administrador_id','valor'=>$criteriosSeleccion->administradorId]);
//                 if(isset($criteriosSeleccion->validada) && $criteriosSeleccion->validada!="")
//                     array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'E', 'campo'=>'validada','valor'=> $criteriosSeleccion->validada]);
//                 if(isset($criteriosSeleccion->justificada)  && $criteriosSeleccion->justificada!="")
//                     array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'E', 'campo'=>'justificada','valor'=> $criteriosSeleccion->justificada]);
                if(isset($criteriosSeleccion->mes)  && $criteriosSeleccion->mes!="")
                    array_push($filtros,(object)['tipoDato'=>'int','campo'=>'MONTH(E.fecha_alta)','valor'=> $criteriosSeleccion->mes]);
                if(isset($criteriosSeleccion->ano)  && $criteriosSeleccion->ano!="")
                    array_push($filtros,(object)['tipoDato'=>'int','campo'=>'YEAR(E.fecha_alta)','valor'=> $criteriosSeleccion->ano]);
                if(isset($criteriosSeleccion->departamentoId) && $criteriosSeleccion->departamentoId!="")
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'departamento_id','valor'=>$criteriosSeleccion->departamentoId]);
                if(isset($criteriosSeleccion->estatusValidacionId) && $criteriosSeleccion->estatusValidacionId!="")
                    //array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'E', 'campo'=>'estatus_validacion_id','valor'=>$criteriosSeleccion->estatusValidacionId]);
                    array_push($filtros,(object)['tipo'=>'estatico','texto'=>"(estatus_validacion_id = $criteriosSeleccion->estatusValidacionId OR EXISTS(SELECT * FROM procesos_revisados_observaciones PRO1 WHERE PRO1.proceso_revisado_id = E.id AND PRO1.estatus_validacion_id = $criteriosSeleccion->estatusValidacionId))"]);
               
            }
            
            
            
            
            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'EM','campo'=>'estatus','valor'=>1]);
            
            $where = $this->where($filtros);
            
            $consulta =  $this->consultaBase . $where .
            " order by UNIX_TIMESTAMP(E.fecha_alta) desc";
            
             //var_dump($consulta);
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($this->bind_param($sentencia, $filtros))
                {
                    if($sentencia->execute())
                    {
                        if($sentencia->bind_result($id, $usuarioProcedimientoId, $fecha,$nombre,$codigo,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId,$validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido,$validadorId, $validadorNombre, $validadorApellido, $procesoId,$estatusValidacionId, $estatusValidacionDescripcion, $estatusValidacionIcono, $estatusValidacionColor,$estatusRevisionId, $estatusRevisionDescripcion, $estatusRevisionIcono, $estatusRevisionColor, $fechaValidacion,$numeroObservaciones, $nombreUsuario))
                        {
                            while($sentencia->fetch())
                            {
                                $registro = $this->crearRegistro($id, $usuarioProcedimientoId, $fecha,$nombre,$codigo,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId,$validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido,$validadorId, $validadorNombre, $validadorApellido, $procesoId,$estatusValidacionId, $estatusValidacionDescripcion, $estatusValidacionIcono, $estatusValidacionColor,$estatusRevisionId, $estatusRevisionDescripcion, $estatusRevisionIcono, $estatusRevisionColor, $fechaValidacion,$numeroObservaciones, $nombreUsuario);
                                array_push($registros,$registro);
                            }
                            $resultado->valor = $registros;
                        }
                        else
                            $resultado->mensajeError = __FUNCTION__. '. Falló el enlace del resultado.';
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__. '. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
                }
                else
                    $resultado->mensajeError = __FUNCTION__. '. Falló el enlace de parámetros';
            }
            else
                $resultado->mensajeError = __FUNCTION__. '. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
          return $resultado;
        

    }
    
    public function consultarProcesosAgrupados($usuario,$criteriosSeleccion)
    {
        
        $resultado = new Resultado();
        
        $registros = array();
        
        $filtros = array();
        
        $filtros = $this->getFiltrosN($usuario,$criteriosSeleccion,true);
        $where = $this->where($filtros);
        
//         $where="";
//         if($criteriosSeleccion!=null)
//         {
            
//             if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
//                 array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'empresa_id','valor'=>$criteriosSeleccion->empresaId]);
//             if(isset($criteriosSeleccion->sedeId) && $criteriosSeleccion->sedeId!="")
//                 array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'sede_id','valor'=>$criteriosSeleccion->sedeId]);
//             if(isset($criteriosSeleccion->usuarioId) && $criteriosSeleccion->usuarioId!="")
//                 array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'UP', 'campo'=>'usuario_id','valor'=>$criteriosSeleccion->usuarioId]);
//             if(isset($criteriosSeleccion->administradorId)  && $criteriosSeleccion->administradorId!="")
//                 array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'EM', 'campo'=>'administrador_id','valor'=>$criteriosSeleccion->administradorId]);
//             if(isset($criteriosSeleccion->mes)  && $criteriosSeleccion->mes!="")
//                 array_push($filtros,(object)['tipoDato'=>'int','campo'=>'MONTH(PR.fecha_alta)','valor'=> $criteriosSeleccion->mes]);
//             if(isset($criteriosSeleccion->ano)  && $criteriosSeleccion->ano!="")
//                 array_push($filtros,(object)['tipoDato'=>'int','campo'=>'YEAR(PR.fecha_alta)','valor'=> $criteriosSeleccion->ano]);
//             if(isset($criteriosSeleccion->departamentoId) && $criteriosSeleccion->departamentoId!="")
//                 array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'departamento_id','valor'=>$criteriosSeleccion->departamentoId]);
//             if(isset($criteriosSeleccion->estatusValidacionId) && $criteriosSeleccion->estatusValidacionId!="")
//                 array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'E', 'campo'=>'estatus_validacion_id','valor'=>$criteriosSeleccion->estatusValidacionId]);
                                                        
//         }
        
        
//         $where = $this->where($filtros);
        
        $consulta = "SELECT EM.id empresaId, EM.nombre empresaNombre, P.id procesoId, P.nombre procesoNombre, P.ruta_archivo carpeta,
                    (
                    	SELECT GROUP_CONCAT(DISTINCT CONCAT(U1.nombre,' ',U1.apellido)  SEPARATOR ', ')
                        FROM usuarios_procesos UP1
                    		INNER JOIN usuarios U1 ON U1.id = UP1.usuario_id
                    	WHERE UP1.proceso_id = P.id
                    ) usuarios
                    FROM appshand_saha.procesos_revisados PR
                    	INNER JOIN usuarios_procesos UP ON UP.id = PR.usuario_proceso_id
                        INNER JOIN procesos P ON P.id = UP.proceso_id
                        INNER JOIN empresas EM ON EM.id = P.empresa_id
                        INNER JOIN sedes S ON S.id = P.sede_id
                        INNER JOIN usuarios U ON U.id = UP.usuario_id
                        INNER JOIN departamentos D ON D.id = U.departamento_id
                    $where
                    GROUP BY P.id, P.nombre
                    ORDER BY P.nombre";
           
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($empresaId, $empresaNombre, $id, $nombre, $rutaArchivo, $usuarios))
                    {
                        while($sentencia->fetch())
                        {
                            $registro = (object)
                            [
                                "empresaId"=> $empresaId,
                                "empresaNombre" => $empresaNombre,
                                "id" => $id,
                                "nombre" => $nombre,
                                "rutaArchivo" => $rutaArchivo,
                                "usuarios" => $usuarios
                                              
                            ];
                            //$registro = $this->crearRegistro($empresaId, $empresaNombre, $procesoId, $procesoNombre, $carpeta, $usuarios);
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__. '. Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = __FUNCTION__. '. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__. '. Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = __FUNCTION__. '. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        return $resultado;
    }

    public function consultarPorLlaves($llaves)
    {
        $resultado = new Resultado();
        $consulta = $this->consultaBase .
        ' WHERE E.id  = ?';
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('s',$llaves->id))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $usuarioProcedimientoId, $fecha,$nombre,$codigo,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId,$validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido,$validadorId, $validadorNombre, $validadorApellido, $procesoId,$estatusValidacionId, $estatusValidacionDescripcion, $estatusValidacionIcono, $estatusValidacionColor,$estatusRevisionId, $estatusRevisionDescripcion, $estatusRevisionIcono, $estatusRevisionColor, $fechaValidacion,$numeroObservaciones, $nombreUsuario))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioProcedimientoId, $fecha,$nombre,$codigo,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId,$validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido,$validadorId, $validadorNombre, $validadorApellido, $procesoId,$estatusValidacionId, $estatusValidacionDescripcion, $estatusValidacionIcono, $estatusValidacionColor,$estatusRevisionId, $estatusRevisionDescripcion, $estatusRevisionIcono, $estatusRevisionColor, $fechaValidacion,$numeroObservaciones, $nombreUsuario);
                            $resultado->valor = $registro;
                        }
                        else
                            $resultado->mensajeError = __FUNCTION__. ' No se encontró ningún resultado.';
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__. ' Falló el enlace del resultado';
                }
                else
                    $resultado->mensajeError = __FUNCTION__. ' Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__. ' Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = __FUNCTION__. ' Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        return $resultado;
    }

    public function consultarPorUsuarioProceso($usuarioProcesoId, $dia, $mes, $ano)
    {
        $resultado = new Resultado();
        $consulta = $this->consultaBase .
        ' WHERE E.usuario_proceso_id  = ? AND DAY(E.fecha_alta) = ? AND MONTH(E.fecha_alta) = ? AND YEAR(E.fecha_alta) = ?';
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('siii',$usuarioProcesoId, $dia, $mes, $ano))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $usuarioProcedimientoId, $fecha,$nombre,$codigo,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId,$validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido,$validadorId, $validadorNombre, $validadorApellido, $procesoId,$estatusValidacionId, $estatusValidacionDescripcion, $estatusValidacionIcono, $estatusValidacionColor,$estatusRevisionId, $estatusRevisionDescripcion, $estatusRevisionIcono, $estatusRevisionColor, $fechaValidacion,$numeroObservaciones, $nombreUsuario))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioProcedimientoId, $fecha,$nombre,$codigo,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId,$validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido,$validadorId, $validadorNombre, $validadorApellido, $procesoId,$estatusValidacionId, $estatusValidacionDescripcion, $estatusValidacionIcono, $estatusValidacionColor,$estatusRevisionId, $estatusRevisionDescripcion, $estatusRevisionIcono, $estatusRevisionColor, $fechaValidacion,$numeroObservaciones, $nombreUsuario);
                            $resultado->valor = $registro;
                        }
                       
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__. ' Falló el enlace del resultado';
                }
                else
                    $resultado->mensajeError = __FUNCTION__. ' Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__. ' Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = __FUNCTION__. ' Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
    
    public function eliminar($llaves)
    {
        $resultado = new Resultado();
        $consulta = "DELETE FROM evidencias WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('s',$llaves->id))
            {
                if($sentencia->execute())
                {
                    $resultado->valor = $llaves->id;
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = __FUNCTION__. '. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
                }
            }
            else
                $resultado->mensajeError = __FUNCTION__. '. Falló el enlace de parámetros';
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = __FUNCTION__. '. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        }
        return $resultado;
    }

    private function crearRegistro($id, $usuarioProcedimientoId, $fecha,$nombre,$codigo,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId,$validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido,$validadorId, $validadorNombre, $validadorApellido, $procesoId, $estatusValidacionId, $estatusValidacionDescripcion, $estatusValidacionIcono, $estatusValidacionColor, $estatusRevisionId, $estatusRevisionDescripcion, $estatusRevisionIcono, $estatusRevisionColor, $fechaValidacion, $numeroObservaciones, $nombreUsuario)
    {
        $registro= (object) 
        [
            'id' => $id,
            'usuarioProcedimientoId' => $usuarioProcedimientoId,
//             'realizoActividad' => $realizoActividad,
//             'justificacionId' => $justificacionId,
//             'justificacionNombre' => $justificacionNombre,
//             'comentarios' => $comentarios,
            'fecha' => $fecha,
            'nombre' => $nombre,
            'procesoId' => $procesoId,
            'procesoNombre' => $nombre,
           // 'nombreArchivo' => $nombreArchivo,
            'codigo' => $codigo,
            'numeroComentarios' => $numeroComentarios,
            'nombreUsuario' => $nombreUsuario,
            'usuarioNombre' => $usuarioNombre,
            'usuarioApellido' => $usuarioApellido,
            'sedeId' => $sedeId,
            'sedeNombre' => $sedeNombre,
            'empresaId' => $empresaId,
            'empresaNombre' => $empresaNombre,
            'usuarioId' => $usuarioId,
            'validada' => $validada,
            'comentariosValidacion' => $comentariosValidacion,
            'administradorId' => $administradorId,
            'administradorNombre' => $administradorNombre,
            'administradorApellido' => $administradorApellido,
            'validadorId' => $validadorId,
            'validadorNombre' => $validadorNombre,
            'validadorApellido' => $validadorApellido,
            'tipo' => "cumplida",
            'estatusValidacionId' => $estatusValidacionId, 
            'estatusValidacionDescripcion' => $estatusValidacionDescripcion, 
            'estatusValidacionIcono' => $estatusValidacionIcono, 
            'estatusValidacionColor' => $estatusValidacionColor,
            'estatusRevisionId' => $estatusRevisionId,
            'estatusRevisionDescripcion' => $estatusRevisionDescripcion,
            'estatusRevisionIcono' => $estatusRevisionIcono,
            'estatusRevisionColor' => $estatusRevisionColor,
            'fechaValidacion' => $fechaValidacion,
            'numeroObservaciones' => $numeroObservaciones
            
        ];
        
//         if($registro->justificacionId!=null)
//             $registro->justificada = 1;
//         else
//             $registro->justificada = 0;
            
        
        $registro->usuarioNombreCompleto = $registro->usuarioNombre . " " . $registro->usuarioApellido;
        $registro->fotoPerfil =  "../fotos/usuario". $registro->usuarioId .".jpg";
        if(file_exists($registro->fotoPerfil))
            $registro->fotoPerfil =  "php/fotos/usuario". $registro->usuarioId .".jpg";
        else
            $registro->fotoPerfil =  "php/fotos/default.jpg";
        
            
        $registro->administradorNombreCompleto = $registro->administradorNombre . " " . $registro->administradorApellido;
        $registro->administradorFotoPerfil =  "../fotos/usuario". $registro->administradorId .".jpg";
        if(file_exists($registro->administradorFotoPerfil))
            $registro->administradorFotoPerfil =  "php/fotos/usuario". $registro->administradorId .".jpg";
        else
            $registro->administradorFotoPerfil =  "php/fotos/default.jpg";
    

            $registro->validadorNombreCompleto = $registro->validadorNombre . " " . $registro->validadorApellido;
            $registro->validadorFotoPerfil =  "../fotos/usuario". $registro->validadorId .".jpg";
            if(file_exists($registro->validadorFotoPerfil))
                $registro->validadorFotoPerfil =  "php/fotos/usuario". $registro->validadorId .".jpg";
                else
                    $registro->validadorFotoPerfil =  "php/fotos/default.jpg";
            
        
        $registro->empresaLogo =  "../logos_empresas/logo". $registro->empresaId .".png";
        if(file_exists($registro->empresaLogo))
            $registro->empresaLogo =  "php/logos_empresas/logo". $registro->empresaId .".png";
        else
            $registro->empresaLogo =  "php/logos_empresas/default.png";
        
        
        return $registro;
    }
    
    public function numeroEvidenciasJustificadas($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $resultado->valor = 0;
        $filtros = $this->getFiltrosUsuario($usuario, $criteriosSeleccion,true);
        
       
        
//         $consulta = "SELECT count(*) numero
//                     FROM evidencias E
//                         INNER JOIN usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
//                         INNER JOIN usuarios U ON U.id = UP.usuario_id
//                     WHERE justificacion_id IS NOT NULL AND U.estatus = 1 ";}

        $consulta = "SELECT count(*) numero
                    FROM evidencias E
                        INNER JOIN usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
                        INNER JOIN usuarios U ON U.id = UP.usuario_id
                    WHERE U.estatus= 1
                        AND justificacion_id IS NOT NULL ";

        $consulta.=  $this->and($filtros);
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($count))
                    {
                        if($sentencia->fetch())
                        {
                            $resultado->valor =$count;
                        }
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__. '. Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = __FUNCTION__. '. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__. '. Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = __FUNCTION__. '. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
    public function numeroEvidenciasEnviadas($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $resultado->valor = 0;
        $filtros = $this->getFiltrosUsuario($usuario, $criteriosSeleccion,true);
        $consulta = "SELECT count(*) numero
                    FROM evidencias E
                        INNER JOIN usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
                        INNER JOIN usuarios U ON U.id = UP.usuario_id
                    WHERE justificacion_id IS NULL AND U.estatus = 1 ";
        $consulta.=  $this->and($filtros);
        
      
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($count))
                    {
                        if($sentencia->fetch())
                        {
                            $resultado->valor =$count;
                        }
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__. '. Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = __FUNCTION__. '. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__. '. Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = __FUNCTION__. '. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
    public function numeroEvidenciasPendientes($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $primerDiaMes = "$criteriosSeleccion->ano-$criteriosSeleccion->mes-1";
        $ultimoDiaMes = date("Y-m-t", strtotime($primerDiaMes));
        $resultado->valor = 0;
        $filtros = array();
        $filtros = $this->getFiltrosUsuario($usuario, null,false);
        
//         $consulta = "SELECT count(*)
//                     FROM usuarios_procedimientos UP
//                     	INNER JOIN procedimientos P ON P.id = UP.procedimiento_id
//                         INNER JOIN usuarios U ON U.id = UP.usuario_id
//                     WHERE UP.estatus = 1  
//                     	AND UP.id NOT IN(SELECT usuario_procedimiento_id FROM evidencias E WHERE MONTH(E.fecha_alta) = $criteriosSeleccion->mes AND YEAR(E.fecha_alta) = $criteriosSeleccion->ano) ";
        
        $consulta = "SELECT count(*)
                    FROM usuarios_procedimientos UP
                    	INNER JOIN procedimientos P ON P.id = UP.procedimiento_id
                        INNER JOIN usuarios U ON U.id = UP.usuario_id
                    WHERE U.estatus = 1 AND ((UP.estatus = 1 AND UP.fecha_alta  <=  '$ultimoDiaMes') OR (UP.estatus = 0 AND MONTH(UP.fecha_alta)  <=  $criteriosSeleccion->mes AND  YEAR(UP.fecha_alta) <= $criteriosSeleccion->ano AND MONTH(UP.fecha_cancelacion) > $criteriosSeleccion->mes AND  YEAR(UP.fecha_cancelacion) >= $criteriosSeleccion->ano))
                            AND UP.id NOT IN(SELECT usuario_procedimiento_id FROM evidencias E WHERE MONTH(E.fecha_alta) = $criteriosSeleccion->mes AND YEAR(E.fecha_alta) = $criteriosSeleccion->ano) ";
        
        
        $consulta.=  $this->and($filtros);
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($count))
                    {
                        if($sentencia->fetch())
                        {
                            $resultado->valor =$count;
                        }
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__. '. Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = __FUNCTION__. '. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__. '. Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = __FUNCTION__. '. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
    public function consultarProcesosSinCambios($procesoId)
    {
        $resultado = new Resultado();
        
        $registros = array();
        
        
        
        $consulta =  "SELECT  IFNULL(DATE_FORMAT( P.fecha_alta,'%d/%m/%Y'),'') fecha, U.id, U.nombre, U.apellido, ER.descripcion estatus
                    FROM procesos_revisados PR
                    	INNER JOIN usuarios_procesos UP ON UP.id = PR.usuario_proceso_id
                        INNER JOIN usuarios U ON U.id = UP.usuario_id
                        INNER JOIN procesos P ON P.id = UP.proceso_id
                        INNER JOIN estatus_revision ER ON ER.id = PR.estatus_revision_id
                    WHERE ER.id = ? AND P.id = ?
                    ORDER BY UNIX_TIMESTAMP(PR.fecha_alta) 	";
        
        //var_dump($consulta);
        
        $estatusId = \EstatusRevision::NO_HUBO_CAMBIOS;
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('ii',$estatusId,$procesoId))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($fecha, $usuarioId, $usuarioNombre, $usuarioApellido, $estatusRevisionNombre ))
                    {
                        while($sentencia->fetch())
                        {
                            $registro = (object) [
                                "fecha" => $fecha,
                                "usuarioId" => $usuarioId,
                                "usuarioNombre" => $usuarioNombre,
                                "usuarioApellido" => $usuarioApellido,
                                "estatusRevisionNombre" => $estatusRevisionNombre
                            ];
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__. '. Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = __FUNCTION__. '. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__. '. Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = __FUNCTION__. '. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        return $resultado;
    }
    
    public function consultarHistorialCambios($procesoId)
    {
        $resultado = new Resultado();
        
        $registros = array();
        
        
        
        $consulta =  "SELECT IFNULL(DATE_FORMAT(HPR.fecha_validacion,'%d/%m/%Y %H:%i:%s'),'') as fecha, U.nombre, U.apellido,PRO.seccion, TOB.descripcion tipo, ER.descripcion estado, V.id, V.nombre, V.apellido
                    FROM historial_procesos_revisados_observaciones HPR
                    	INNER JOIN procesos_revisados PR ON PR.id = HPR.proceso_revisado_id
                        INNER JOIN procesos_revisados_observaciones PRO ON PRO.proceso_revisado_id = PR.id AND PRO.id = HPR.observacion_id
                        INNER JOIN usuarios_procesos UP ON UP.id = PR.usuario_proceso_id 
                    	INNER JOIN procesos P ON P.id = UP.proceso_id
                        INNER JOIN usuarios U ON U.id = UP.usuario_id
                        INNER JOIN tipos_observacion TOB ON TOB.id = PRO.tipo_observacion_id
                        INNER JOIN estatus_validacion_procesos ER ON ER.id = HPR.estatus_validacion_id
                        INNER JOIN usuarios V ON V.id = HPR.usuario_validador_id
                    WHERE P.id = ?	";
                            
        //var_dump($consulta);
        
        //$estatusId = \EstatusRevision::NO_HUBO_CAMBIOS;
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('i',$procesoId))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($fecha, $usuarioNombre, $usuarioApellido, $seccion, $tipoObservacion, $estatusValidacionDescripcion, $validadorId, $validadorNombre, $validadorApellido ))
                    {
                        while($sentencia->fetch())
                        {
                            $registro = (object) [
                                "fecha" => $fecha,
                                "usuarioNombre" => $usuarioNombre,
                                "usuarioApellido" => $usuarioApellido,
                                "seccion" => $seccion,
                                "tipoObservacionNombre" => $tipoObservacion,
                                "estatusValidacionDescripcion" => $estatusValidacionDescripcion,
                                "validadorId" => $validadorId,
                                "validadorNombre" => $validadorNombre,
                                "validadorApellido" => $validadorApellido
                            ];
                            $registro->usuarioNombreCompleto = $registro->usuarioNombre . " " . $registro->usuarioApellido;
                            $registro->validadorNombreCompleto = $registro->validadorNombre . " " . $registro->validadorApellido;
                            array_push($registros,$registro);
                           
                        }
                        $sentencia->close();
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__. '. Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = __FUNCTION__. '. Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__. '. Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = __FUNCTION__. '. Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
   
    
}
?>