<?php
namespace php\repositorios;

use php\interfaces\IAuditoriasRepositorio;
use php\modelos\Auditoria;
use php\modelos\Resultado;
use php\clases\AdministradorConexion;
use php\clases\AdministradorArchivos;
use php\clases\Porcentaje;
use php\clases\Logger;
use php\modelos\RecomendacionComentario;
use php\clases\AdministradorCorreo;
use php\clases\CodigoError;


include "../interfaces/IAuditoriasRepositorio.php";
include "../modelos/Auditoria.php";
require_once("RepositorioBase.php");
require_once("../clases/Resultado.php");
require_once("../clases/Porcentaje.php");
require_once("../clases/Logger.php");

require_once("../clases/CodigoError.php");
require_once("../clases/TipoUsuario.php");
require_once("../clases/EstatusValidacion.php");
require_once('../clases/AdministradorArchivos.php');
require_once('../clases/AdministradorConexion.php');
require_once('../repositorios/RecomendacionesComentariosRepositorio.php');
require_once('../repositorios/EstatusValidacionRepositorio.php');
require_once('../repositorios/UsuariosRepositorio.php');
require_once('../clases/AdministradorCorreo.php');


class AuditoriasRepositorio extends RepositorioBase implements IAuditoriasRepositorio
{
    protected $conexion;
    protected $consultaBase;
    protected $consultaBaseRecomendaciones;
    protected $consultaBaseAvances;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = " SELECT A.id, A.plantilla_id, P.nombre, IFNULL(DATE_FORMAT(A.fecha_ejecucion,'%d/%m/%Y %H:%i:%s'),'')fecha_ejecucion, A.empresa_id, E.nombre, IFNULL(E.nombre_corto,'')nombre_corto, A.contador_empresa,tipo_auditoria_id,
                (SELECT SUM(porcentaje) / COUNT(*) as porcentaje 
                        FROM auditoria_secciones ASS
                        	INNER JOIN auditorias A1 ON A1.id = ASS.auditoria_id
                        	INNER JOIN secciones S ON S.plantilla_id = A1.plantilla_id AND S.id= ASS.seccion_id
                        WHERE S.orden>1 AND A1.id = A.id ) puntuacion, 
                A.nivel_compromiso, A.implementacion, A.verificacion,
                TE.nivel_compromiso, TE.implementacion, TE.verificacion, 
                PS.nivel_compromiso, PS.implementacion, PS.verificacion, observaciones, buenas_practicas, seguimiento, IFNULL(DATE_FORMAT(A.fecha_seguimiento,'%d/%m/%Y %H:%i:%s'),'')fecha_seguimiento,
                (SELECT count(*)
                FROM recomendaciones R
                	LEFT JOIN usuarios U ON U.id = R.responsable_id
                	LEFT JOIN empresas E1 ON E1.id = U.empresa_id
                WHERE auditoria_id = A.id) recomendacionesTotal,
            (SELECT count(*)
                FROM recomendaciones R
                	LEFT JOIN usuarios U ON U.id = R.responsable_id
                	LEFT JOIN empresas E1 ON E1.id = U.empresa_id
                WHERE auditoria_id = A.id AND R.estatus_validacion_id!=2) recomendacionesPendientes,
                 IFNULL(DATE_FORMAT(A.fecha_alta,'%d/%m/%Y %H:%i:%s'),'') AS fechaAlta,A.sede_id, S.nombre sedeNombre, IFNULL(DATE_FORMAT(A.fecha,'%d/%m/%Y'),''), A.hora, TA.nombre,
seguimiento_finalizado, IFNULL(DATE_FORMAT(A.fecha_seguimiento_finalizado,'%d/%m/%Y %H:%i:%s'),'')fecha_seguimiento_finalizado
             FROM auditorias A 
             INNER JOIN plantillas P on A.plantilla_id = P.id 
            LEFT JOIN empresas E on A.empresa_id = E.id 
            LEFT JOIN tipos_empresa TE ON TE.id = E.tipo_empresa_id
            LEFT JOIN paises PS ON PS.id = E.pais_id
            LEFT JOIN sedes S ON A.sede_id = S.id
            LEFT JOIN tipos_auditoria TA ON TA.id = A.tipo_auditoria_id";
        
        $this->consultaBaseRecomendaciones = "SELECT RC.id, edt, titulo, responsable_id, U.nombre, U.apellido,  IFNULL(DATE_FORMAT(RC.fecha_alta,'%d/%m/%Y'),'')fechaAlta, prioridad, cumplimiento,  IFNULL(DATE_FORMAT(RC.fecha_vencimiento,'%d/%m/%Y'),'')fechaVencimiento,  IFNULL(DATE_FORMAT(RC.fecha_finalizacion,'%d/%m/%Y'),'')fecha_finalizacion, terminada, estatus_validacion_id, EST.descripcion AS estatusValidacionId, IFNULL(DATE_FORMAT(RC.fecha_validacion,'%d/%m/%Y %H:%i:%s'),'')fecha_validacion, 
                            validacion_usuario_id AS validadorId, VL.nombre AS validadorNombre, VL.apellido AS validadorApellido,
                            E1.administrador_sivah_id AS administradorId, V.nombre AS administradorNombre, V.apellido AS administradorApellido,
                            E1.id AS empresaId, E1.nombre AS empresaNombre,(SELECT count(C.id) FROM recomendaciones_comentarios C WHERE C.recomendacion_id = RC.id) numeroComentarios, EST.icono, EST.color,comentarios_validacion,
                        IFNULL(DATE_FORMAT(RC.fecha_modificacion,'%d/%m/%Y'),'')fechaModificacion 
                       FROM recomendaciones RC
                       LEFT JOIN usuarios U ON U.id =  RC.responsable_id
                       LEFT JOIN tipos_usuario TU ON TU.id = U.tipo_usuario_id
                       LEFT JOIN empresas E1 ON E1.id = U.empresa_id
                       LEFT JOIN usuarios V ON V.id = E1.administrador_sivah_id
                       LEFT JOIN usuarios VL ON VL.id = RC.validacion_usuario_id
                       INNER JOIN estatus_validacion EST ON RC.estatus_validacion_id = EST.id";
        
        $this->consultaBaseAvances = "SELECT RA.id, comentario, cumplimiento, IFNULL(DATE_FORMAT(RA.fecha_alta,'%d/%m/%Y %H:%i:%s'),'')fecha_alta,IFNULL(DATE_FORMAT(RA.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'')fecha_modificacion, 
                    (SELECT COUNT(*) FROM recomendaciones_avances_archivos ARC WHERE ARC.avance_id = RA.id) archivos, 
                    RA.usuario_id, U.nombre, U.apellido
                     FROM recomendaciones_avances RA
                        LEFT JOIN usuarios U ON RA.usuario_id = U.id";
                      
        
    }
     
    
    private function calcularContadorEmpresa($empresaId)
    {
        $resultado = new Resultado();
        $consulta =  "SELECT IFNULL(MAX(contador_empresa),0)+1 AS id FROM auditorias WHERE empresa_id = ?";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("i", $empresaId))
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
                            $resultado->mensajeError = "No se encontró ningún resultado";
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
            return $resultado->valor;
    }
    
    function numeroRegistros($plantillaId, $auditoriaId, $seccionId, $preguntaId)
    {
        $resultado = new Resultado();
        $consulta =  "SELECT COUNT(*) id FROM auditoria_preguntas WHERE auditoria_id=? AND plantilla_id = ? AND seccion_id = ? AND pregunta_id = ?";
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("iiii",$auditoriaId,$plantillaId,$seccionId, $preguntaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($count))
                    {
                        if($sentencia->fetch())
                        {
                            $resultado->valor = $count;
                            $sentencia->close();
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
                $resultado->mensajeError = "Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
       return $resultado;
    }
    
    function numeroRegistrosRecomendaciones($auditoriaId,$hallazgoId)
    {
        $resultado = new Resultado();
        $consulta =  "SELECT COUNT(*) id FROM recomendaciones WHERE auditoria_id = ? AND edt=?";
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("is",$auditoriaId,$hallazgoId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($count))
                    {
                        if($sentencia->fetch())
                        {
                            $resultado->valor = $count;
                            $sentencia->close();
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
                $resultado->mensajeError = "Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            return $resultado;
    }
    
    function numeroRegistrosSecciones($plantillaId, $auditoriaId, $seccionId)
    {
        $resultado = new Resultado();
        $consulta =  "SELECT COUNT(*) id FROM auditoria_secciones WHERE auditoria_id=? AND plantilla_id = ? AND seccion_id = ? ";
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("iii",$auditoriaId,$plantillaId,$seccionId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($count))
                    {
                        if($sentencia->fetch())
                        {
                            $resultado->valor = $count;
                            $sentencia->close();
                        }
                        else
                            $resultado->mensajeError = __FUNCTION_." No se encontró ningún resultado";
                    }
                    else
                        $resultado->mensajeError = __FUNCTION_." Falló el enlace del resultado";
                }
                else
                    $resultado->mensajeError = __FUNCTION_." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION_." Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = __FUNCTION_." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            return $resultado;
    }
    
    function consultarAuditoriaAnterior($empresaId, $sedeId, $plantillaId, $auditoriaId)
    {
        $resultado = new Resultado();
        
        $consulta = "SELECT id, DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s')
                    FROM auditorias
                    WHERE empresa_id = ? 
                           AND sede_id = ?
                            AND plantilla_id = ? AND id < ?
                    ORDER BY id DESC
                    LIMIT 1";
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('iiii',$empresaId, $sedeId, $plantillaId,$auditoriaId))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id,$fecha))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = (object)["id"=>$id, "fecha"=> $fecha];
                            $resultado->valor = $registro;
                        }
                        else
                            $resultado->valor = (object)["id"=> -1, "fecha"=> ""];
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
    
    function consultarPorcentajesSecciones($auditoriaId)
    {
        $resultado = new Resultado();
        $registros = array();
        
        
        $consulta = "SELECT S.id, S.texto, SUM(puntos) / SUM(puntos_total) * 100
                FROM auditoria_preguntas AP
                	INNER JOIN preguntas P ON P.plantilla_id = AP.plantilla_id AND P.seccion_id = AP.seccion_id AND P.id = AP.pregunta_id
                    INNER JOIN secciones S ON P.plantilla_id = S.plantilla_id AND P.seccion_id = S.id
                WHERE P.tipo='e' AND auditoria_id = ?
                GROUP BY AP.seccion_id, S.texto
                LIMIT 1,100	";
            
            
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('i',$auditoriaId))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $texto,$porcentaje))
                    {
                        while($sentencia->fetch())
                        {
                            $registro= (object) [
                                'id' =>  $id,
                                'texto' =>  $texto,
                                'porcentaje' =>  $porcentaje
                               
                            ];
                            Porcentaje::formatearPorcentaje($registro, "porcentaje",2);
                                
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
    
    function consultarPorcentajesSeccionesComparativo($auditoriaId,$auditoriaAnteriorId)
    {
        $resultado = new Resultado();
        $registros = array();
        
        
        $consulta = "SELECT A.id, A.texto, A.porcentaje, B.porcentaje FROM(SELECT S.id, S.texto, SUM(puntos) / SUM(puntos_total) * 100 porcentaje
                    FROM auditoria_preguntas AP
                    	INNER JOIN preguntas P ON P.plantilla_id = AP.plantilla_id AND P.seccion_id = AP.seccion_id AND P.id = AP.pregunta_id
                        INNER JOIN secciones S ON P.plantilla_id = S.plantilla_id AND P.seccion_id = S.id
                    WHERE P.tipo='e' AND auditoria_id = ?
                    GROUP BY AP.seccion_id) A
                    INNER JOIN
                    (SELECT S.id, S.texto, 0 actual, SUM(puntos) / SUM(puntos_total) * 100 porcentaje
                    FROM auditoria_preguntas AP
                    	INNER JOIN preguntas P ON P.plantilla_id = AP.plantilla_id AND P.seccion_id = AP.seccion_id AND P.id = AP.pregunta_id
                        INNER JOIN secciones S ON P.plantilla_id = S.plantilla_id AND P.seccion_id = S.id
                    WHERE P.tipo='e' AND auditoria_id = ?
                    GROUP BY AP.seccion_id	)B
                    WHERE A.id = B.id
                     LIMIT 1,100;";
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('ii',$auditoriaId,$auditoriaAnteriorId))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $texto,$porcentajeActual, $porcentajeAnterior))
                    {
                        while($sentencia->fetch())
                        {
                            $registro= (object) [
                                'id' =>  $id,
                                'texto' =>  $texto,
                                'porcentajeActual' =>  $porcentajeActual,
                                'porcentajeAnterior' => $porcentajeAnterior
                                
                            ];
                            Porcentaje::formatearPorcentaje($registro, "porcentajeActual",2);
                            Porcentaje::formatearPorcentaje($registro, "porcentajeAnterior",2);
                            
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
    
    function consultarPuntuacionAuditoria($auditoriaId)
    {
        $resultado = new Resultado();
        $consulta =  "SELECT SUM(porcentaje) / COUNT(*) as porcentaje
                    FROM auditoria_preguntas AP
                    	INNER JOIN preguntas P ON P.plantilla_id = AP.plantilla_id AND P.seccion_id = AP.seccion_id AND P.id = AP.pregunta_id
                    WHERE P.tipo='e' AND auditoria_id = ?";
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$auditoriaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($porcentaje))
                    {
                        if($sentencia->fetch())
                        {
                            $resultado->valor = $porcentaje;
                            $sentencia->close();
                        }
                        else
                            $resultado->mensajeError = __FUNCTION__. " No se encontró ningún resultado";
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
    
    public function insertar(Auditoria $modelo)
    {
        $resultado = new Resultado();
        $this->conexion->autocommit(FALSE);
        if($modelo->id=="")
        {
            if($modelo->empresaId=="")
                $modelo->empresaId = null;
            
            if($modelo->sedeId=="")
                $modelo->sedeId = null;
            
            $fecha = "";
            if($modelo->fecha!="")
            {
                $elementos = explode("/",$modelo->fecha);
                $elementos = array_reverse($elementos);
                $fecha = join("-",$elementos);
            }
            else
                $fecha = null;
            
            $resultado =  $this->calcularId("id","auditorias");
            if($resultado->mensajeError=="")
            {
                $modelo->id = $resultado->valor;
               
                $modelo->contadorEmpresa = $this->calcularContadorEmpresa($modelo->empresaId);
                
                
                $consulta = "INSERT INTO auditorias(id, plantilla_id, fecha_ejecucion, empresa_id, contador_empresa, tipo_auditoria_id, fecha_alta, sede_id, fecha, hora) " .
                    "VALUE(?, ?, NOW(),  ?, ?, ?, NOW(), ?, ?, ?)";
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if( $sentencia->bind_param("iiiisiss", $modelo->id, $modelo->plantillaId, $modelo->empresaId, $modelo->contadorEmpresa, $modelo->tipoAuditoriaId ,$modelo->sedeId, $fecha, $modelo->hora))
                    {
                        if($sentencia->execute())
                        {
                            $sentencia->close();
                            
                            $resultado = $this->insertarDatosAuditoria($modelo);
                            if($resultado->mensajeError=="")
                            {
                                $resultado = $this->actualizarUltimoUso($modelo->plantillaId);
                              
                                if($resultado->mensajeError=="")
                                {
                                    $resultado = $this->consultarEncabezado($modelo->id);
                                    if($resultado->mensajeError=="")
                                    {
                                    }
                                   
                                }
                               
                            }
    
                        }
                        else
                        {
                            $resultado->mensajeError = "Falló la ejecución insertar(" . $this->conexion->errno . ") " . $this->conexion->error;
                        }
                    }
                    else
                    {
                        $resultado->mensajeError = "Falló el enlace de parámetros";
                    }
                }
                else
                {
                    $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
        
            }
        }
        
        if($resultado->correcto())
        {
            $this->conexion->commit();
            //$resultado->valor = $modelo->id;
            //$resultado->valor = $modelo;
        }
        else
            $this->conexion->rollback();

        return $resultado;
    }
    
    
    private function existeAuditoria($id)
    {
        $resultado = new Resultado();
        $consulta =  "SELECT COUNT(*) AS c FROM auditorias WHERE id = ?";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("i", $id))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($c))
                    {
                        if($sentencia->fetch())
                        {
                            //$resultado->valor = $C;
                            if($c>0)
                                 $resultado->valor =true;
                            else
                                $resultado->valor =false;
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
                $resultado->mensajeError = "Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
       return $resultado->valor;
    }
    
    
    
    private function eliminarRespuestasSiCategorias($plantillaId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM respuestas_si_categorias WHERE plantilla_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$plantillaId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                  
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = "Falló la ejecución eliminarRespuestasSiCategorias(" . $this->conexion->errno . ") " . $this->conexion->error;
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
    
    private function eliminarPreguntasCategorias($plantillaId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM preguntas_categorias WHERE plantilla_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$plantillaId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                    
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = "Falló la ejecución eliminarPreguntasCategorias(" . $this->conexion->errno . ") " . $this->conexion->error;
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
    
    private function eliminarRespuestasNoCategorias($plantillaId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM respuestas_no_categorias WHERE plantilla_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$plantillaId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = "Falló la ejecución eliminarRespuestasNoCategorias(" . $this->conexion->errno . ") " . $this->conexion->error;
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
    
    private function eliminarPreguntas($auditoriaId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM auditoria_preguntas WHERE auditoria_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$auditoriaId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = __FUNCTION__. ". Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
                
            }
            else
                $resultado->mensajeError = __FUNCTION__. ". Falló el enlace de parámetros";
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = __FUNCTION__. ". Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
        }
        return $resultado;
    }
    
    private function eliminarSecciones($auditoriaId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM auditoria_secciones WHERE auditoria_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$auditoriaId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError =  __FUNCTION__.". Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
                
            }
            else
                $resultado->mensajeError = __FUNCTION__.". Falló el enlace de parámetros";
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = __FUNCTION__.". Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
        }
        return $resultado;
    }
    

    private function insertarPreguntas($auditoriaId,$plantillaId,$seccionId,$preguntas)
    {
        $resultado = new Resultado();
        
        //$administradorArchivos = new AdministradorArchivos();
       
        for ($i = 0; $i <  count($preguntas); $i++)
        {
            
            $pregunta = $preguntas[$i];
            
            if(isset($pregunta->responsable))
            {
                if($pregunta->responsable=="")
                 $pregunta->responsable = null;
            }
            else
                $pregunta->responsable = null;
            
            $consulta = "UPDATE auditoria_preguntas 
                        SET valor = ?,  
                        puntos = ?, 
                        puntos_total = ?, 
                        porcentaje = ?,
                        responsable_id = ?,
                        reporte = ?,
                        notificacion = ?
                        WHERE auditoria_id=? AND plantilla_id = ? AND seccion_id = ? AND pregunta_id = ?";
            
//             if(substr( $pregunta->valor, 0, 10 ) === "data:image")
//             {
//                 $carpeta = "fotos_auditorias";
//                 $nombreArchivo = "foto_".$auditoriaId."_".$seccionId."_".$pregunta->id.".jpg";
//                 $administradorArchivos->crearBase64($pregunta->valor,$carpeta,$nombreArchivo);
//             }
            
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param("siisiiiiiii",$pregunta->valor,$pregunta->puntos, $pregunta->puntosTotal, $pregunta->porcentaje,$pregunta->responsable, $pregunta->reporte, $pregunta->notificacion,$auditoriaId,$plantillaId,$seccionId, $pregunta->id))
                {
                    if($sentencia->execute())
                    {
                        
                        //$count = $sentencia->affected_rows;
                        $sentencia->close();
                        
                        $resultado = $this->numeroRegistros($plantillaId,$auditoriaId,$seccionId, $pregunta->id);
                        if($resultado->mensajeError=="")
                        {
                            $count = $resultado->valor;

                            
                            if($count==0)
                            {
                                $consulta = "INSERT INTO auditoria_preguntas(auditoria_id, plantilla_id, seccion_id, pregunta_id, valor, puntos, puntos_total, porcentaje, responsable_id, reporte, notificacion) " .
                                    "VALUE(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                                if($sentencia = $this->conexion->prepare($consulta))
                                {
                                    if($sentencia->bind_param("iiiisiisiii",$auditoriaId,$plantillaId,$seccionId, $pregunta->id, $pregunta->valor,$pregunta->puntos, $pregunta->puntosTotal, $pregunta->porcentaje, $pregunta->responsable, $pregunta->reporte, $pregunta->notificacion))
                                    {
                                        if($sentencia->execute())
                                        {
                                            $sentencia->close();
                                        }
                                        else
                                        {
                                            $resultado->codigoError = $this->conexion->errno;
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
                                    $resultado->codigoError = $this->conexion->errno;
                                    $resultado->mensajeError = __FUNCTION__.". Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
                                    break;
                                }
                            }
                            
                        }
                        
                       
                    }
                    else
                    {
                        $resultado->codigoError = $this->conexion->errno;
                        $resultado->mensajeError = __FUNCTION__.". Falló la ejecución update (" . $this->conexion->errno . ") " . $this->conexion->error;
                        break;
                    }
                }
                else
                {
                    $resultado->mensajeError = "Falló el enlace de parámetros update";
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
        return $resultado;
    }
    
    private function insertarSeccion($auditoriaId,$plantillaId,$seccion)
    {
        $resultado = new Resultado();
    
        
        $consulta = "UPDATE auditoria_secciones " .
            "SET hallazgo = ?,  recomendacion = ?,  responsable = ?, puntos = ?, puntos_total = ?, porcentaje = ?,
                reporte = ?, notificacion = ? ".
            "WHERE auditoria_id=? AND plantilla_id = ? AND seccion_id = ?";
       
        if(isset($seccion->responsable))
        {
            if($seccion->responsable=="")
                 $seccion->responsable = null;
        }
        else 
            $seccion->responsable = null;
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ssiiisiiiii",$seccion->hallazgo,$seccion->recomendacion, $seccion->responsable, $seccion->puntos, $seccion->puntosTotal, $seccion->porcentaje,$seccion->reporte, $seccion->notificacion,$auditoriaId,$plantillaId,$seccion->id))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                    
                    $resultado = $this->numeroRegistrosSecciones($plantillaId,$auditoriaId,$seccion->id);
                    if($resultado->mensajeError=="")
                    {
                        $count = $resultado->valor;
                        
                        
                        if($count==0)
                        {
                            $consulta = "INSERT INTO auditoria_secciones(auditoria_id, plantilla_id, seccion_id, hallazgo, recomendacion, responsable,puntos, puntos_total, porcentaje, reporte, notificacion) " .
                                "VALUE(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                            if($sentencia = $this->conexion->prepare($consulta))
                            {
                                if($sentencia->bind_param("iiissiiisii",$auditoriaId,$plantillaId,$seccion->id, $seccion->hallazgo, $seccion->recomendacion, $seccion->responsable,$seccion->puntos, $seccion->puntosTotal, $seccion->porcentaje,$seccion->reporte,$seccion->notificacion))
                                {
                                    if($sentencia->execute())
                                    {
                                        $sentencia->close();
                                    }
                                    else
                                    {
                                        $resultado->codigoError = $this->conexion->errno;
                                        $resultado->mensajeError = __FUNCTION__. ". Falló la ejecución insert(" . $this->conexion->errno . ") " . $this->conexion->error;
                                    }
                                }
                                else
                                {
                                    $resultado->mensajeError = __FUNCTION__. ". Falló el enlace de parámetros insert";
                                }
                            }
                            else
                            {
                                $resultado->codigoError = $this->conexion->errno;
                                $resultado->mensajeError = __FUNCTION__. ". Falló la preparación insert: (" . $this->conexion->errno . ") " . $this->conexion->error;
                            }
                        }
                        
                    }
                    
                    
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError =  __FUNCTION__. ". Falló la ejecución update (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
            }
            else
            {
                $resultado->mensajeError =  __FUNCTION__. ". Falló el enlace de parámetros update";
            }
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError =  __FUNCTION__. ". Falló la preparación update: (" . $this->conexion->errno . ") " . $this->conexion->error;
        }
            
            
            
        return $resultado;
    }
    
    private function eliminarRespuestasSi($auditoriaId,$plantillaId,$seccionId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM auditoria_respuestas_si WHERE auditoria_id = ? AND plantilla_id = ? AND seccion_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("iii",$auditoriaId,$plantillaId,$seccionId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                    
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = "Falló la ejecución eliminarRespuestasSi(" . $this->conexion->errno . ") " . $this->conexion->error;
                }
                
            }
            else
                $resultado->mensajeError = "Falló el enlace de parámetros eliminarRespuestas";
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
        }
        return $resultado;
    }
    
    private function eliminarRespuestasNo($auditoriaId,$plantillaId,$seccionId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM auditoria_respuestas_no WHERE auditoria_id = ? AND plantilla_id = ? AND seccion_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("iii",$auditoriaId,$plantillaId,$seccionId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                    
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = "Falló la ejecución eliminarRespuestasSi(" . $this->conexion->errno . ") " . $this->conexion->error;
                }
                
            }
            else
                $resultado->mensajeError = "Falló el enlace de parámetros eliminarRespuestas";
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
        }
        return $resultado;
    }
    
    private function eliminarRespuestasNoAuditoria($auditoriaId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM auditoria_respuestas_no WHERE auditoria_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$auditoriaId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                    
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = __FUNCTION__ .". Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
                
            }
            else
                $resultado->mensajeError =  __FUNCTION__ .".Falló el enlace de parámetros ";
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError =  __FUNCTION__ .".Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
        }
        return $resultado;
    }
    
    private function eliminarRespuestasSiAuditoria($auditoriaId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM auditoria_respuestas_si WHERE auditoria_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$auditoriaId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                    
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = __FUNCTION__ .". Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
                
            }
            else
                $resultado->mensajeError =  __FUNCTION__ .".Falló el enlace de parámetros ";
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError =  __FUNCTION__ .".Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
        }
        return $resultado;
    }
    
    private function insertarRespuestasSi($auditoriaId,$plantillaId,$seccionId,$preguntas)
    {
        $resultado = new Resultado();
        
      
            
        for ($j = 0; $j < count($preguntas); $j++)
        {
            $pregunta = $preguntas[$j];
            
            for ($k = 0; $k< count($pregunta->respuestas_si); $k++)
            {
                $respuesta = $pregunta->respuestas_si[$k];
                
                if(isset($respuesta->responsable))
                {
                    if($respuesta->responsable=="")
                        $respuesta->responsable = null;
                }
                else
                    $respuesta->responsable = null;
                
                $consulta = "INSERT INTO auditoria_respuestas_si(auditoria_id, plantilla_id, seccion_id, pregunta_id, respuesta_id, valor, responsable_id, reporte, notificacion) " .
                    "VALUE(?, ?, ?, ?, ?, ?, ?, ?, ?)";
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if($sentencia->bind_param("iiiiisiii", $auditoriaId, $plantillaId, $seccionId,$pregunta->id, $respuesta->id, $respuesta->valor, $respuesta->responsable,$respuesta->reporte,  $respuesta->notificacion ))
                    {
                        if($sentencia->execute())
                        {
                            $sentencia->close();
                        }
                        else
                        {
                            $resultado->codigoError = $this->conexion->errno;
                            $resultado->mensajeError = "Falló la ejecución insertarRespuestasSi(" . $this->conexion->errno . ") " . $this->conexion->error;
                            break;
                        }
                        
                    }
                    else
                    {
                        $resultado->mensajeError = "Falló el enlace de parámetros insertarRespuestasSi";
                        break;
                    }
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = "Falló la preparación insertarRespuestasSi: (" . $this->conexion->errno . ") " . $this->conexion->error;
                    break;
                }
            }
        }
            
        
        return $resultado;
    }
    
    private function insertarRespuestasNo($auditoriaId,$plantillaId,$seccionId,$preguntas)
    {
        $resultado = new Resultado();
        
        
        
        for ($j = 0; $j < count($preguntas); $j++)
        {
            $pregunta = $preguntas[$j];
            
            for ($k = 0; $k< count($pregunta->respuestas_no); $k++)
            {
                $respuesta = $pregunta->respuestas_no[$k];
                
                if(isset($respuesta->responsable))
                {
                    if($respuesta->responsable=="")
                        $respuesta->responsable = null;
                }
                else
                    $respuesta->responsable = null;
                    
                    $consulta = "INSERT INTO auditoria_respuestas_no(auditoria_id, plantilla_id, seccion_id, pregunta_id, respuesta_id, valor, responsable_id, reporte, notificacion) " .
                        "VALUE(?, ?, ?, ?, ?, ?, ?, ?, ? )";
                    if($sentencia = $this->conexion->prepare($consulta))
                    {
                        if($sentencia->bind_param("iiiiisiii", $auditoriaId, $plantillaId, $seccionId,$pregunta->id, $respuesta->id, $respuesta->valor, $respuesta->responsable,$respuesta->reporte,  $respuesta->notificacion))
                        {
                            if($sentencia->execute())
                            {
                                $sentencia->close();
                            }
                            else
                            {
                                $resultado->codigoError = $this->conexion->errno;
                                $resultado->mensajeError = "Falló la ejecución insertarRespuestasNo(" . $this->conexion->errno . ") " . $this->conexion->error;
                                break;
                            }
                            
                        }
                        else
                        {
                            $resultado->mensajeError = "Falló el enlace de parámetros insertarRespuestasNo";
                            break;
                        }
                    }
                    else
                    {
                        $resultado->codigoError = $this->conexion->errno;
                        $resultado->mensajeError = "Falló la preparación insertarRespuestasNo: (" . $this->conexion->errno . ") " . $this->conexion->error;
                        break;
                    }
            }
        }
        
        
        return $resultado;
    }
    
    
//     private function insertarRespuestasNoCategorias($plantillaId,$secciones)
//     {
//         $resultado = new Resultado();
        
//         for ($i = 0; $i <  count($secciones); $i++)
//         {
//             $seccion = $secciones[$i];
//             for ($j = 0; $j < count($seccion->preguntas); $j++)
//             {
//                 $pregunta = $seccion->preguntas[$j];
//                 for ($k = 0; $k< count($pregunta->respuestas_no); $k++)
//                 {
//                     $respuesta = $pregunta->respuestas_no[$k];
//                     for ($l = 0; $l< count($respuesta->categorias); $l++)
//                     {
//                         $categoria = $respuesta->categorias[$l];
                        
//                         $consulta = "INSERT INTO respuestas_no_categorias(plantilla_id, seccion_id, pregunta_id, respuesta_no_id, id, categoria_id) " .
//                             "VALUE(?, ?, ?, ?, ?, ?)";
//                         if($sentencia = $this->conexion->prepare($consulta))
//                         {
//                             if($sentencia->bind_param("iiiiii",$plantillaId,$seccion->id, $pregunta->id,$respuesta->id, $categoria->id, $categoria->categoriaId))
//                             {
//                                 if($sentencia->execute())
//                                 {
//                                     $sentencia->close();
//                                 }
//                                 else
//                                 {
//                                     $resultado->codigoError = $this->conexion->errno;
//                                     $resultado->mensajeError = "Falló la ejecución: insertarRespuestasNoCategorias(" . $this->conexion->errno . ") " . $this->conexion->error;
//                                     break;
//                                 }
//                             }
//                             else
//                             {
//                                 $resultado->mensajeError = "Falló el enlace de parámetros";
//                                 break;
//                             }
//                         }
//                         else
//                         {
//                             $resultado->codigoError = $this->conexion->errno;
//                             $resultado->mensajeError = "Falló la preparación: insertarRespuestasNoCategorias(" . $this->conexion->errno . ") " . $this->conexion->error;
//                             break;
//                         }
//                     }
//                 }
//             }
//         }
//         return $resultado;
//     }

    public function actualizarUltimoUso($plantillaId)
    {
        $resultado = new Resultado();
        
            
        $consulta = " UPDATE plantillas " .
            "SET ultimo_uso = NOW() " .
            "WHERE id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("i", $plantillaId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                }
                else
                    $resultado->mensajeError = "Falló la ejecución actualizar(" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = "Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
       return $resultado;
    }
    
    public function actualizarSeguimientoIniciado($auditoriaId)
    {
        $resultado = new Resultado();
        
        $consulta = " UPDATE auditorias 
            SET seguimiento = 1,
                fecha_seguimiento = NOW()
            WHERE id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("i", $auditoriaId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                }
                else
                    $resultado->mensajeError = __FUNCTION__.". Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__.". Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = __FUNCTION__.". Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
        return $resultado;
    }
    
    public function actualizarSeguimientoFinalizado($auditoriaId)
    {
        $resultado = new Resultado();
        
        $consulta = " UPDATE auditorias
            SET seguimiento_finalizado = 1,
                fecha_seguimiento_finalizado = NOW()
            WHERE id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("i", $auditoriaId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                }
                else
                    $resultado->mensajeError = __FUNCTION__.". Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__.". Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = __FUNCTION__.". Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            return $resultado;
    }
        
    
    public function actualizar(Auditoria $modelo)
    {
        $resultado = new Resultado();
//         $datetime = null;
//         if($modelo->fechaProgramada!=null)
//         {
//             $elementos = explode('/', $modelo->fechaProgramada);
//             if(count($elementos)==3)
//             {
//                 $dia = $elementos[0];
//                 $mes = $elementos[1];
//                 $ano = $elementos[2];
//                 $datetime = date("Y-m-d H:i:s", mktime(10, 30, 0, $mes, $dia, $ano));
//             }
//         }
        
        $this->conexion->autocommit(FALSE);
        
        if($modelo->empresaId=="")
            $modelo->empresaId = null;
        
        if($modelo->sedeId=="")
            $modelo->sedeId = null;
        
        $fecha = "";
        if($modelo->fecha!="")
        {
            $elementos = explode("/",$modelo->fecha);
            $elementos = array_reverse($elementos);
            $fecha = join("-",$elementos);
        }
        else
            $fecha = null;
        
        $consulta = " UPDATE auditorias     
            SET empresa_id = ?, 
                   tipo_auditoria_id = ?,   
                    fecha_ejecucion = NOW(),
                    observaciones = ?,
                    buenas_practicas = ?,
                    sede_id = ?,
                    fecha = ?,
                    hora = ? 
            WHERE id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("isssissi", $modelo->empresaId,$modelo->tipoAuditoriaId,$modelo->observaciones,$modelo->buenasPracticas,$modelo->sedeId, $fecha, $modelo->hora,$modelo->id))
            {
                if($sentencia->execute())
                {
                    $resultado->valor=true;
                    $sentencia->close();
                    
                    $resultado = $this->consultarPuntuacionAuditoria($modelo->id);
                    if($resultado->correcto())
                    {
                        $puntuacion = $resultado->valor;
                        
                        $resultado = $this->insertarDatosAuditoria($modelo);
                        if($resultado->correcto())
                        {
                            $resultado = $this->actualizarUltimoUso($modelo->plantillaId);
                            if($resultado->correcto())
                            {
                                $resultado = $this->consultarEncabezado($modelo->id);
                                if($resultado->correcto())
                                {
                                    $auditoria = $resultado->valor;
                                    if($puntuacion!=$auditoria->puntuacion)
                                    {
                                        $this->calcularAleatorios($modelo->id,$auditoria->puntuacion);
                                    }
                                }
                                
                            }
                                
                        }
                    }
                }
                else
                    $resultado->mensajeError = "Falló la ejecución actualizar(" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else  
                $resultado->mensajeError = "Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
        
        if($resultado->correcto())
            $this->conexion->commit();
        else
           $this->conexion->rollback();
        return $resultado;
    }
    
    private function calcularAleatorios($auditoriaId,$porcentaje)
    {
        $resultado = new Resultado();
        
        $nivelCompromiso = floatval($porcentaje) + (random_int(-30, 30) / 10 );
        $implementacion = floatval($porcentaje) + (random_int(-30, 30) / 10 );
        $verificacion = floatval($porcentaje) + (random_int(-30, 30) / 10 );
        
        if($nivelCompromiso>100)
            $nivelCompromiso = 100;
        if($implementacion>100)
            $implementacion = 100;
        if($verificacion>100)
            $verificacion = 100;
        
        if($nivelCompromiso<0)
            $nivelCompromiso = 0;
        if($implementacion<0)
            $implementacion = 0;
        if($verificacion<0)
                $verificacion = 0;
        
        $consulta = "UPDATE auditorias " .
            "SET nivel_compromiso = ?,  implementacion = ?,  verificacion = ? ".
            "WHERE id=?";
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("sssi",$nivelCompromiso, $implementacion, $verificacion, $auditoriaId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                    
                   
                    
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError =  __FUNCTION__. "Falló la ejecución update (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
            }
            else
            {
                $resultado->mensajeError =  __FUNCTION__. "Falló el enlace de parámetros update";
            }
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError =  __FUNCTION__. "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
        }
        
        
        
        return $resultado;
    }
    
    public function insertarDatosAuditoria($modelo)
    {
        ini_set('max_execution_time', 300);
        $resultado =  $this->eliminarRespuestasSi($modelo->id,$modelo->plantillaId, $modelo->seccion->id);
        if($resultado->mensajeError=="")
        {
            $resultado =  $this->eliminarRespuestasNo($modelo->id,$modelo->plantillaId, $modelo->seccion->id);
            if($resultado->mensajeError=="")
            {
                $resultado = $this->insertarSeccion($modelo->id,$modelo->plantillaId, $modelo->seccion);
                if($resultado->correcto())
                {
                    $resultado =  $this->insertarPreguntas($modelo->id,$modelo->plantillaId, $modelo->seccion->id,$modelo->seccion->preguntas);
                    if($resultado->mensajeError=="")
                    {
                        $resultado =  $this->insertarRespuestasSi($modelo->id,$modelo->plantillaId, $modelo->seccion->id,$modelo->seccion->preguntas);
                        if($resultado->mensajeError=="")
                        {
                            $resultado =  $this->insertarRespuestasNo($modelo->id,$modelo->plantillaId, $modelo->seccion->id,$modelo->seccion->preguntas);
                            if($resultado->mensajeError=="")
                            {
                                
                            }
                        }
                    }
                }
            }
        }
       
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
            if(isset($criteriosSeleccion->nombre))
            {
                if($criteriosSeleccion->nombre!="")
                {
                    array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'A','campo'=>'nombre','valor'=>$criteriosSeleccion->nombre]);
                }
            }
            if(isset($criteriosSeleccion->id))
            {
                if($criteriosSeleccion->id!="")
                {
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'A','campo'=>'id','valor'=>$criteriosSeleccion->id]);
                }
            }
            $where = $this->where($filtros);
        }
        $consulta = $this->consultaBase .
        $where . " order by UNIX_TIMESTAMP(fecha_ejecucion) desc";
        
      
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id,  $plantillaId, $plantillaNombre, $fechaEjecucion, $empresaId, $empresaNombre, $empresaNombreCorto, $contadorEmpresa,$tipoAuditoriaId, $puntuacion, $nivelCompromiso, $implementacion, $verificacion,$tipoEmpresaNivelCompromiso, $tipoEmpresaImplementacion, $tipoEmpresaVerificacion, $paisNivelCompromiso, $paisImplementacion, $paisVerificacion, $observaciones, $buenasPracticas, $seguimiento, $fechaSeguimiento,$recomendacionesTotal,$recomendacionesPendientes,$fechaAlta, $sedeId, $sedeNombre, $fecha, $hora, $tipoAuditoriaNombre, $seguimientoFinalizado, $fechaSeguimientoFinalizado))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id,  $plantillaId, $plantillaNombre, $fechaEjecucion, $empresaId, $empresaNombre, $empresaNombreCorto, $contadorEmpresa,$tipoAuditoriaId, $puntuacion, $nivelCompromiso, $implementacion, $verificacion,$tipoEmpresaNivelCompromiso, $tipoEmpresaImplementacion, $tipoEmpresaVerificacion, $paisNivelCompromiso, $paisImplementacion, $paisVerificacion, $observaciones, $buenasPracticas, $seguimiento, $fechaSeguimiento, $recomendacionesTotal,$recomendacionesPendientes,$fechaAlta, $sedeId, $sedeNombre, $fecha, $hora, $tipoAuditoriaNombre,$seguimientoFinalizado, $fechaSeguimientoFinalizado);
                           
                            
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
    
    public function consultarEncabezado($auitoriaId)
    {
       
        $resultado = new Resultado();
        $consulta = $this->consultaBase .
        " WHERE A.id  = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$auitoriaId))
            {
                if($sentencia->execute())
                {
                    //if ($sentencia->bind_result($id,  $plantillaId, $plantillaNombre, $fechaEjecucion, $empresaId, $empresaNombre, $empresaNombreCorto, $contadorEmpresa,$tipoAuditoriaId,$puntuacion, $nivelCompromiso, $implementacion, $verificacion,$tipoEmpresaNivelCompromiso, $tipoEmpresaImplementacion, $tipoEmpresaVerificacion, $paisNivelCompromiso, $paisImplementacion, $paisVerificacion, $observaciones, $buenasPracticas, $seguimiento, $fechaSeguimiento,$fechaAlta, $sedeId, $sedeNombre, $fecha, $hora))
                    //if ($sentencia->bind_result($id,  $plantillaId, $plantillaNombre, $fechaEjecucion, $empresaId, $empresaNombre, $empresaNombreCorto, $contadorEmpresa,$tipoAuditoriaId, $puntuacion, $nivelCompromiso, $implementacion, $verificacion,$tipoEmpresaNivelCompromiso, $tipoEmpresaImplementacion, $tipoEmpresaVerificacion, $paisNivelCompromiso, $paisImplementacion, $paisVerificacion, $observaciones, $buenasPracticas, $seguimiento, $fechaSeguimiento,$fechaAlta, $sedeId, $sedeNombre, $fecha, $hora, $tipoAuditoriaNombre,$seguimientoFinalizado, $fechaSeguimientoFinalizado))
                    if($sentencia->bind_result($id,  $plantillaId, $plantillaNombre, $fechaEjecucion, $empresaId, $empresaNombre, $empresaNombreCorto, $contadorEmpresa,$tipoAuditoriaId, $puntuacion, $nivelCompromiso, $implementacion, $verificacion,$tipoEmpresaNivelCompromiso, $tipoEmpresaImplementacion, $tipoEmpresaVerificacion, $paisNivelCompromiso, $paisImplementacion, $paisVerificacion, $observaciones, $buenasPracticas, $seguimiento, $fechaSeguimiento,$recomendacionesTotal,$recomendacionesPendientes,$fechaAlta, $sedeId, $sedeNombre, $fecha, $hora, $tipoAuditoriaNombre,$seguimientoFinalizado, $fechaSeguimientoFinalizado))
                    {
                        if($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id,  $plantillaId, $plantillaNombre, $fechaEjecucion, $empresaId, $empresaNombre, $empresaNombreCorto, $contadorEmpresa,$tipoAuditoriaId,$puntuacion, $nivelCompromiso, $implementacion, $verificacion,$tipoEmpresaNivelCompromiso, $tipoEmpresaImplementacion, $tipoEmpresaVerificacion, $paisNivelCompromiso, $paisImplementacion, $paisVerificacion, $observaciones, $buenasPracticas, $seguimiento, $fechaSeguimiento,$recomendacionesTotal,$recomendacionesPendientes, $fechaAlta, $sedeId, $sedeNombre, $fecha, $hora, $tipoAuditoriaNombre,$seguimientoFinalizado, $fechaSeguimientoFinalizado);
                            $resultado->valor = $registro;
                        }
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
    
    public function consultarValoresSeccion($llaves)
    {
        $resultado = new Resultado();
        
        $llaves->id = $llaves->auditoriaId;
        
        $resultado = $this->consultarPorLlaves($llaves);
        if($resultado->correcto())
        {
            $resultadoPreguntas = $this->consultarPreguntas($llaves->plantillaId, $llaves->auditoriaId, $llaves->seccionId);
            if($resultadoPreguntas->correcto())
            {
                $resultadoSeccion = $this->consultarSeccionPorLlaves($llaves);
                if($resultadoSeccion->correcto())
                {
                    $resultado->valor->seccion = $resultadoSeccion->valor;
                    $resultado->valor->preguntas = $resultadoPreguntas->valor;
                }
            }
            else
                $resultado->mensajeError = $resultadoPreguntas->mensajeError;
        }
        
        
        return $resultado;
    }
    
    public function consultarValoresSecciones($llaves)
    {
        $resultado = new Resultado();
        
        $secciones = array();
        $consulta = "SELECT S.id, RTRIM(S.texto) texto, hallazgo, recomendacion, reporte, notificacion, responsable, D.id, D.nombre 
                    FROM auditoria_secciones ASS
                    	INNER JOIN secciones S ON S.plantilla_id = ASS.plantilla_id AND S.id =ASS.seccion_id
                    	LEFT JOIN usuarios U ON U.id = ASS.responsable
                    	LEFT JOIN departamentos D ON U.departamento_id = D.id
                    WHERE ASS.plantilla_id  = ? AND ASS.auditoria_id = ?
                    ORDER BY S.id";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ii",$llaves->plantillaId, $llaves->auditoriaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $texto, $hallazgo, $recomendacion, $reporte, $notificacion, $responsable, $departamentoId, $departamentoNombre))
                    {
                        while($sentencia->fetch())
                        {
                            $seccion= (object) [
                                'id' =>  $id,
                                'texto' => $texto,
                                'hallazgo' => $hallazgo,
                                'recomendacion' => $recomendacion,
                                'reporte' => $reporte,
                                'notificacion' => $notificacion,
                                'responsable' => $responsable,
                                'departamentoId' => $departamentoId,
                                'departamentoNombre' => $departamentoNombre
                            ];
                            array_push($secciones,$seccion);
                        }
                        $resultado->valor = $secciones;
                        
                        $sentencia->close();
                        
                        for($i=0; $i < count($secciones);$i++)
                        {
                            $seccion = $secciones[$i];
                            $resultadoPreguntas = $this->consultarPreguntas($llaves->plantillaId, $llaves->auditoriaId, $seccion->id);
                            if($resultadoPreguntas->mensajeError=="")
                            {
                                $seccion->preguntas = $resultadoPreguntas->valor;
                            }
                            else
                            {
                                $resultado->mensajeError = $resultadoPreguntas->mensajeError;
                                break;
                            }
                        }
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
    
    public function consultarPorLlaves($llaves)
    {
        $resultado = new Resultado();
        ini_set('max_execution_time', 300);
        $consulta = $this->consultaBase .
        " WHERE A.id  = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$llaves->id))
            {
                if($sentencia->execute())
                {
                    //if ($sentencia->bind_result($id,  $plantillaId, $plantillaNombre, $fechaEjecucion, $empresaId, $empresaNombre, $empresaNombreCorto, $contadorEmpresa,$tipoAuditoriaId,$puntuacion, $nivelCompromiso, $implementacion, $verificacion,$tipoEmpresaNivelCompromiso, $tipoEmpresaImplementacion, $tipoEmpresaVerificacion, $paisNivelCompromiso, $paisImplementacion, $paisVerificacion, $observaciones, $buenasPracticas, $seguimiento, $fechaSeguimiento))
                    if ($sentencia->bind_result($id,  $plantillaId, $plantillaNombre, $fechaEjecucion, $empresaId, $empresaNombre, $empresaNombreCorto, $contadorEmpresa,$tipoAuditoriaId, $puntuacion, $nivelCompromiso, $implementacion, $verificacion,$tipoEmpresaNivelCompromiso, $tipoEmpresaImplementacion, $tipoEmpresaVerificacion, $paisNivelCompromiso, $paisImplementacion, $paisVerificacion, $observaciones, $buenasPracticas, $seguimiento, $fechaSeguimiento,$recomendacionesTotal,$recomendacionesPendientes,$fechaAlta, $sedeId, $sedeNombre, $fecha, $hora, $tipoAuditoriaNombre,$seguimientoFinalizado, $fechaSeguimientoFinalizado))
                    {
                        if($sentencia->fetch())
                        {
                            $plantilla = $this->crearRegistro($id,  $plantillaId, $plantillaNombre, $fechaEjecucion, $empresaId, $empresaNombre, $empresaNombreCorto, $contadorEmpresa,$tipoAuditoriaId,$puntuacion, $nivelCompromiso, $implementacion, $verificacion,$tipoEmpresaNivelCompromiso, $tipoEmpresaImplementacion, $tipoEmpresaVerificacion, $paisNivelCompromiso, $paisImplementacion, $paisVerificacion,$observaciones, $buenasPracticas, $seguimiento, $fechaSeguimiento,$recomendacionesTotal,$recomendacionesPendientes, $fechaAlta, $sedeId, $sedeNombre, $fecha, $hora, $tipoAuditoriaNombre,$seguimientoFinalizado, $fechaSeguimientoFinalizado);
                           
                            
                            $resultado->valor = $plantilla;
                            
                            $sentencia->close();
                            
//                             $resultadoSecciones = $this->consultarSecciones($plantilla->id);
//                             if($resultadoSecciones->mensajeError=="")
//                             {
//                                 $plantilla->secciones = $resultadoSecciones->valor;
//                             }
//                             else
//                                 $resultado->mensajeError = $resultadoSecciones->mensajeError;
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
    
    public function consultarAvanceTerminadoRecomendacion($recomendacionId)
    {
        $resultado = new Resultado();
        ini_set('max_execution_time', 300);
        $consulta = $this->consultaBaseAvances .
        " WHERE RA.recomendacion_id = ? AND cumplimiento=100
        ORDER BY UNIX_TIMESTAMP(RA.fecha_alta) desc
        LIMIT 1";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$recomendacionId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $comentario, $cumplimiento, $fechaAlta, $fechaModificacion, $archivos, $usuarioId, $usuarioNombre, $usuarioApellido ))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistroAvance($id, $comentario, $cumplimiento, $fechaAlta, $fechaModificacion, $archivos, $usuarioId, $usuarioNombre, $usuarioApellido);
                            $resultado->valor = $registro;
                            $sentencia->close();
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
    
    public function consultarAvancePorLlaves($llaves)
    {
        $resultado = new Resultado();
        ini_set('max_execution_time', 300);
        $consulta = $this->consultaBaseAvances .
                     " WHERE RA.id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$llaves->id))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $comentario, $cumplimiento, $fechaAlta, $fechaModificacion, $archivos, $usuarioId, $usuarioNombre, $usuarioApellido ))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistroAvance($id, $comentario, $cumplimiento, $fechaAlta, $fechaModificacion, $archivos, $usuarioId, $usuarioNombre, $usuarioApellido);
//                             $registro =  (object)[
//                                 "id" => $id,
//                                 "comentario" => $comentario,
//                                 "cumplimiento" => $cumplimiento,
//                                 "fecha" => $fecha,
//                                 "archivos" => $archivos
                                
//                             ];
                            $resultado->valor = $registro;
                            
                            $sentencia->close();
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
    
    public function consultarRecomendacionPorLlaves($llaves)
    {
        $resultado = new Resultado();
        $consulta = $this->consultaBaseRecomendaciones .
                     " WHERE RC.id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$llaves->id))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id,  $edt, $titulo, $responsableId, $responsableNombre, $reponsableApellido, $fechaAlta, $prioridad, $cumplimiento, $fechaVencimiento, $fechaFinalizacion , $terminada, $estatusValidacionId,$estatusValidacionDescripcion, $fechaValidacion, $validadorId, $validadorNombre, $validadorApellido, $administradorId, $administradorNombre, $administradorApellido, $empresaId, $empresaNombre,$numeroComentarios, $estatusValidacionIcono, $estatusValidacionColor, $comentariosValidacion, $fechaModificacion))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistroRecomendacion($id, $edt, $titulo, $responsableId, $responsableNombre, $reponsableApellido, $fechaAlta, $prioridad, $cumplimiento, $fechaVencimiento, $fechaFinalizacion, $terminada, $estatusValidacionId, $estatusValidacionDescripcion,$fechaValidacion, $validadorId,$validadorNombre, $validadorApellido,$administradorId, $administradorNombre, $administradorApellido, $empresaId, $empresaNombre,$numeroComentarios, $estatusValidacionIcono, $estatusValidacionColor,$comentariosValidacion, $fechaModificacion);
                            $resultado->valor = $registro;
                            
                            $sentencia->close();
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
    
    public function consultarSeccionPorLlaves($llaves)
    {
        $resultado = new Resultado();
        ini_set('max_execution_time', 300);
        $consulta = "SELECT seccion_id, hallazgo, recomendacion, responsable, reporte, notificacion
                    FROM auditoria_secciones
                     WHERE plantilla_id = ? AND auditoria_id = ? AND seccion_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("iii",$llaves->plantillaId, $llaves->auditoriaId, $llaves->seccionId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $hallazgo, $recomendacion, $responsable,$reporte, $notificacion))
                    {
                        if($sentencia->fetch())
                        {
                            $seccion= (object) [
                                'id' =>  $id,
                                'hallazgo' => $hallazgo,
                                'recomendacion' => $recomendacion,
                                'responsable' => $responsable,
                                'reporte' => $reporte,
                                'notificacion' => $notificacion
                            ];
                            
                            $resultado->valor = $seccion;
                            
                            $sentencia->close();
                            
                        }
                        else
                            $resultado->mensajeError = __FUNCTION__. ". No se encontró ningún resultado.";
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
    
    public function consultarRecomendacionesAuditoria($auditoriaId)
    {
        $resultado = new Resultado();
        $hallazgos = array();
        
        $consulta = "SELECT H.id, titulo, responsable_id, U.nombre, U.apellido,  IFNULL(DATE_FORMAT(H.fecha_alta,'%d/%m/%Y %H:%i:%s'),'') fecha_alta, IFNULL(cumplimiento,0) cumplimiento, IFNULL(DATE_FORMAT(H.fecha_vencimiento,'%d/%m/%Y %H:%i:%s'),'') fecha_vencimiento, D.id, D.nombre
                    FROM recomendaciones H
                        LEFT JOIN usuarios U ON U.id = H.responsable_id
                        LEFT JOIN departamentos D ON D.id = U.departamento_id
                WHERE H.auditoria_id = ?";
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            
            if($sentencia->bind_param("i",$auditoriaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $titulo, $responsableId,$responsableNombre,$responsableApellido,$fechaAlta, $cumplimiento, $fechaVencimiento,$departamentoId, $departamentoNombre))
                    {
                        while($sentencia->fetch())
                        {
                            $hallazgo= (object) [
                                'id' =>  $id,
                                'titulo' => $titulo,
                                'responsableId' => $responsaleId,
                                'responsableNombre' => $responsableNombre,
                                'responsableApellido' => $responsableApellido,
                                'fechaAlta' => $fechaAlta,
                                'cumplimiento' => $cumplimiento,
                                'fechaVencimiento' =>$fechaVencimiento,
                                'departamentoId' => $departamentoId,
                                'departamentoNombre' => $departamentoNombre
                            ];
                            
                            $hallazgo->responsableNombreCompleto = $hallazgo->responsableNombre . " " . $hallazgo->responsableApellido;
                            $hallazgo->fotoPerfil =  "../fotos/usuario". $hallazgo->id .".jpg";
                            if(file_exists($hallazgo->fotoPerfil))
                                $hallazgo->fotoPerfil =  "php/fotos/usuario". $hallazgo->responsableId .".jpg";
                            else
                                $hallazgo->fotoPerfil =  "php/fotos/default.jpg";
                            
                            array_push($hallazgos,$hallazgo);
                        }
                        $resultado->valor = $hallazgos;
                        
                        $sentencia->close();
                        
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
    
    public function consultarRecomendacionesPendientes($auditoriaId)
    {
        $resultado = new Resultado();
        $hallazgos = array();
        
        $consulta = "SELECT H.id, titulo, responsable_id, U.nombre, U.apellido,  IFNULL(DATE_FORMAT(H.fecha_alta,'%d/%m/%Y %H:%i:%s'),'') fecha_alta, IFNULL(cumplimiento,0) cumplimiento, IFNULL(DATE_FORMAT(H.fecha_vencimiento,'%d/%m/%Y %H:%i:%s'),'') fecha_vencimiento, D.id, D.nombre,
                    H.estatus_validacion_id, IFNULL(EST.descripcion,'')
                    FROM recomendaciones H
                        LEFT JOIN usuarios U ON U.id = H.responsable_id
                        LEFT JOIN departamentos D ON D.id = U.departamento_id
                        INNER JOIN estatus_validacion EST ON EST.id = H.estatus_validacion_id
                WHERE H.auditoria_id = ? AND H.estatus_validacion_id != 2";
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            
            if($sentencia->bind_param("i",$auditoriaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $titulo, $responsableId,$responsableNombre,$responsableApellido,$fechaAlta, $cumplimiento, $fechaVencimiento,$departamentoId, $departamentoNombre, $estatusValidacionId, $estatusValidacionNombre))
                    {
                        while($sentencia->fetch())
                        {
                            $hallazgo= (object) [
                                'id' =>  $id,
                                'titulo' => $titulo,
                                'responsableId' => $responsaleId,
                                'responsableNombre' => $responsableNombre,
                                'responsableApellido' => $responsableApellido,
                                'fechaAlta' => $fechaAlta,
                                'cumplimiento' => $cumplimiento,
                                'fechaVencimiento' =>$fechaVencimiento,
                                'departamentoId' => $departamentoId,
                                'departamentoNombre' => $departamentoNombre,
                                'estatusValidacionId' => $estatusValidacionId,
                                'estatusValidacionNombre' => $estatusValidacionNombre
                            ];
                            
                            $hallazgo->responsableNombreCompleto = $hallazgo->responsableNombre . " " . $hallazgo->responsableApellido;
                            $hallazgo->fotoPerfil =  "../fotos/usuario". $hallazgo->id .".jpg";
                            if(file_exists($hallazgo->fotoPerfil))
                                $hallazgo->fotoPerfil =  "php/fotos/usuario". $hallazgo->responsableId .".jpg";
                                else
                                    $hallazgo->fotoPerfil =  "php/fotos/default.jpg";
                                    
                                    array_push($hallazgos,$hallazgo);
                        }
                        $resultado->valor = $hallazgos;
                        
                        $sentencia->close();
                        
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
 
    private function consultarPreguntas($plantillaId,$auditoriaId,$seccionId)
    {
        
        $resultado = new Resultado();
        $preguntas = array();
        $consulta = "SELECT A.seccion_id, pregunta_id, RTRIM(texto) texto, P.peso, RTRIM(tipo) tipo, RTRIM(hallazgo) hallazgo, RTRIM(recomendacion)recomendacion, RTRIM(practicas)practicas, RTRIM(observaciones)observaciones, RTRIM(valor) valor, responsable_id, reporte, notificacion, D.id, D.nombre departamentoNombre 
            FROM auditoria_preguntas A
            INNER JOIN preguntas P ON A.plantilla_id = P.plantilla_id AND A.seccion_id = P.seccion_id AND A.pregunta_id = P.id 
            LEFT JOIN usuarios U ON A.responsable_id = U.id
            LEFT JOIN departamentos D ON U.departamento_id = D.id
            WHERE A.plantilla_id  = ? AND A.auditoria_id = ? AND A.seccion_id = ? ";
        
       
        if($sentencia = $this->conexion->prepare($consulta))
        {
            
            if($sentencia->bind_param("iii",$plantillaId,$auditoriaId, $seccionId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($seccionId, $preguntaId, $texto, $peso, $tipo , $hallazgo, $recomendacion, $practicas, $observaciones, $valor, $responsable, $reporte, $notificacion, $departamentoId, $departamentoNombre))
                    {   
                        while($sentencia->fetch())
                        {
                            
                            $pregunta= (object) [
                                'seccionId' =>  $seccionId,
                                'preguntaId' => $preguntaId,
                                'texto' => $texto,
                                'peso' => $peso,
                                'tipo' => $tipo,
                                'hallazgo' => $hallazgo,
                                'recomendacion' => $recomendacion,
                                'practicas' => $practicas,
                                'observciones' => $observaciones,
                                'valor' => $valor,
                                'responsable' => $responsable,
                                'reporte' => $reporte,
                                'notificacion' => $notificacion,
                                'departamentoId' => $departamentoId,
                                'departamentoNombre' => $departamentoNombre
                            ];
                            array_push($preguntas,$pregunta);
                        }
                        $resultado->valor = $preguntas;
                        
                        $sentencia->close();
                       
                        for($i=0; $i < count($preguntas);$i++)
                        {
                            $pregunta = $preguntas[$i];
                            $resultadoRespuestas = $this->consultarRespuestasSi($auditoriaId,$plantillaId,$seccionId,$pregunta->preguntaId);
                            if($resultadoRespuestas->mensajeError=="")
                            {
                                $pregunta->respuestas_si = $resultadoRespuestas->valor;
                            }
                            else
                            {
                                $resultado->mensajeError = $resultadoRespuestas->mensajeError;
                                break;
                            }
                            
                            $resultadoRespuestas = $this->consultarRespuestasNo($auditoriaId,$plantillaId,$seccionId,$pregunta->preguntaId);
                            if($resultadoRespuestas->mensajeError=="")
                            {
                                $pregunta->respuestas_no = $resultadoRespuestas->valor;
                            }
                            else
                            {
                                $resultado->mensajeError = $resultadoRespuestas->mensajeError;
                                break;
                            }
                        }
                        
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
    
    private function consultarPreguntasCompletas($plantillaId,$auditoriaId,$seccionId)
    {
        
        $resultado = new Resultado();
        $preguntas = array();
        $consulta =      $consulta = "SELECT A.seccion_id, pregunta_id, RTRIM(texto) texto, peso, RTRIM(tipo) tipo, RTRIM(hallazgo) hallazgo, RTRIM(recomendacion)recomendacion, RTRIM(practicas)practicas, RTRIM(observaciones)observaciones, RTRIM(valor) valor  " .
            "FROM auditoria_preguntas A " .
            "  INNER JOIN preguntas P ON A.plantilla_id = P.plantilla_id AND A.seccion_id = P.seccion_id AND A.pregunta_id = P.id ".
            " WHERE A.plantilla_id  = ? AND A.auditoria_id = ? AND A.seccion_id = ? ";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            
            if($sentencia->bind_param("iii",$plantillaId,$auditoriaId, $seccionId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($seccionId, $preguntaId, $texto, $peso, $tipo , $hallazgo, $recomendacion, $practicas, $observaciones, $valor))
                    {
                        while($sentencia->fetch())
                        {
                            
                            $pregunta= (object) [
                                'seccionId' =>  $seccionId,
                                'preguntaId' => $preguntaId,
                                'texto' => $texto,
                                'peso' => $peso,
                                'tipo' => $tipo,
                                'hallazgo' => $hallazgo,
                                'recomendacion' => $recomendacion,
                                'practicas' => $practicas,
                                'observciones' => $observaciones,
                                'valor' => $valor
                            ];
                            array_push($preguntas,$pregunta);
                        }
                        $resultado->valor = $preguntas;
                        
                        $sentencia->close();
                        
                        for($i=0; $i < count($preguntas);$i++)
                        {
                            $pregunta = $preguntas[$i];
                            $resultadoRespuestas = $this->consultarRespuestas($auditoriaId,$plantillaId,$seccionId,$pregunta->preguntaId);
                            if($resultadoRespuestas->mensajeError=="")
                            {
                                $pregunta->respuestas = $resultadoRespuestas->valor;
                            }
                            else
                            {
                                $resultado->mensajeError = $resultadoRespuestas->mensajeError;
                                break;
                            }
                        }
                        
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
    
    private function consultarSecciones($plantillaId)
    {
        $resultado = new Resultado();
        $secciones = array();
        $consulta = "SELECT id, RTRIM(texto) texto " .
            "FROM secciones " .
            " WHERE plantilla_id  = ? ".
            "ORDER BY id";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$plantillaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $texto))
                    {
                        while($sentencia->fetch())
                        {
                            $seccion= (object) [
                                'id' =>  $id,
                                'texto' => $texto
                            ];
                            array_push($secciones,$seccion);
                        }
                        $resultado->valor = $secciones;
                        
                        $sentencia->close();
                        
                        for($i=0; $i < count($secciones);$i++)
                        {
                            $seccion = $secciones[$i];
                            $resultadoPreguntas = $this->consultarPreguntas($plantillaId,$seccion->id);
                            if($resultadoPreguntas->mensajeError=="")
                            {
                                $seccion->preguntas = $resultadoPreguntas->valor;
                            }
                            else
                            {
                                $resultado->mensajeError = $resultadoPreguntas->mensajeError;
                                break;
                            }
                        }
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
    

    
    private function consultarRespuestasSi($auditoriaId,$plantillaId,$seccionId,$preguntaId)
    {
        
        $resultado = new Resultado();
        $respuestas = array();
       /* $consulta = "SELECT respuesta_id, valor, responsable_id, reporte, notificacion, D.id, D.nombre, R.hallazgo, R.recomendacion
            FROM auditoria_respuestas_si  RS
                  INNER JOIN respuestas_si R ON RS.plantilla_id = R.plantilla_id AND RS.seccion_id = R.seccion_id AND RS.pregunta_id = R.pregunta_id
                LEFT JOIN usuarios U ON RS.responsable_id = U.id
                LEFT JOIN departamentos D ON U.departamento_id = D.id
            WHERE auditoria_id = ? AND plantilla_id  = ? AND seccion_id= ? AND pregunta_id = ? 
            ORDER BY respuesta_id";*/
        
        $consulta = "SELECT respuesta_id, valor, responsable_id, reporte, notificacion, D.id, D.nombre, R.hallazgo, R.recomendacion
            FROM auditoria_respuestas_si  RS
                    INNER JOIN respuestas_si R ON RS.plantilla_id = R.plantilla_id AND RS.seccion_id = R.seccion_id AND RS.pregunta_id = R.pregunta_id AND RS.respuesta_id = R.id
                LEFT JOIN usuarios U ON RS.responsable_id = U.id
                LEFT JOIN departamentos D ON U.departamento_id = D.id
            WHERE RS.auditoria_id = ? AND RS.plantilla_id  = ? AND RS.seccion_id= ? AND RS.pregunta_id = ?
            ORDER BY respuesta_id";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("iiii",$auditoriaId,$plantillaId,$seccionId,$preguntaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($respuestaId, $valor, $responsable, $reporte, $notificacion,$departamentoId, $departamentoNombre, $hallazgo, $recomendacion))
                    {
                        while($sentencia->fetch())
                        {
                            $respuesta= (object) [
                                'id' =>  $respuestaId,
                                'valor' => $valor,
                                'responsable' => $responsable,
                                'reporte' => $reporte,
                                'notificacion' => $notificacion,
                                'departamentoId' => $departamentoId,
                                'departamentoNombre' => $departamentoNombre,
                                'hallazgo' => $hallazgo,
                                'recomendacion' => $recomendacion
                              
                            ];
                            array_push($respuestas,$respuesta);
                        }
                        $resultado->valor = $respuestas;
                        $sentencia->close();
                        
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
    
    private function consultarRespuestasNo($auditoriaId,$plantillaId,$seccionId,$preguntaId)
    {
        
        $resultado = new Resultado();
        $respuestas = array();
        $consulta = "SELECT respuesta_id, valor, responsable_id, reporte, notificacion " .
            "FROM auditoria_respuestas_no " .
            " WHERE auditoria_id = ? AND plantilla_id  = ? AND seccion_id= ? AND pregunta_id = ? ";
        "ORDER BY respuesta_id";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("iiii",$auditoriaId,$plantillaId,$seccionId,$preguntaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($respuestaId, $valor, $responsable, $reporte, $notificacion))
                    {
                        while($sentencia->fetch())
                        {
                            $respuesta= (object) [
                                'id' =>  $respuestaId,
                                'valor' => $valor,
                                'responsable' => $responsable,
                                'reporte' => $reporte,
                                'notificacion' => $notificacion
                            ];
                            array_push($respuestas,$respuesta);
                        }
                        $resultado->valor = $respuestas;
                        $sentencia->close();
                        
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
    
    private function consultarRespuestasSiCategorias($plantillaId,$seccionId,$preguntaId,$respuestaId)
    {
        $resultado = new Resultado();
        $categorias = array();
        $consulta = "SELECT id, categoria_id " .
            "FROM respuestas_si_categorias " .
            " WHERE plantilla_id = ? AND seccion_id= ? AND pregunta_id = ? AND respuesta_si_id = ? ".
            "ORDER BY id";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            
            if($sentencia->bind_param("iiii",$plantillaId,$seccionId,$preguntaId,$respuestaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id,$categoriaId))
                    {
                        while($sentencia->fetch())
                        {
                            $categoria= (object) [
                                'id' =>  $id,
                                'categoriaId' =>  $categoriaId
                            ];
                            array_push($categorias,$categoria);
                        }
                        $resultado->valor = $categorias;
                        $sentencia->close();
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
    
    private function consultarPreguntasCategorias($plantillaId,$seccionId,$preguntaId)
    {
        $resultado = new Resultado();
        $categorias = array();
        $consulta = "SELECT id, categoria_id " .
            "FROM preguntas_categorias " .
            " WHERE plantilla_id = ? AND seccion_id= ? AND pregunta_id = ? ".
            "ORDER BY id";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            
            if($sentencia->bind_param("iii",$plantillaId,$seccionId,$preguntaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id,$categoriaId))
                    {
                        while($sentencia->fetch())
                        {
                            $categoria= (object) [
                                'id' =>  $id,
                                'categoriaId' =>  $categoriaId
                            ];
                            array_push($categorias,$categoria);
                        }
                        $resultado->valor = $categorias;
                        $sentencia->close();
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
    
//     private function consultarRespuestasNo($plantillaId,$seccionId,$preguntaId)
//     {
        
//         $resultado = new Resultado();
//         $respuestas = array();
//         $consulta = "SELECT id, RTRIM(texto) texto, IFNULL(peso,0) peso,RTRIM(hallazgo)hallazgo, RTRIM(recomendacion)recomendacion " .
//             "FROM respuestas_no " .
//             " WHERE plantilla_id  = ? AND seccion_id= ? AND pregunta_id = ? ".
//             "ORDER BY id";
//         if($sentencia = $this->conexion->prepare($consulta))
//         {
//             if($sentencia->bind_param("iii",$plantillaId,$seccionId,$preguntaId))
//             {
//                 if($sentencia->execute())
//                 {
//                     if ($sentencia->bind_result($id, $texto,$peso,$hallazgo,$recomendacion))
//                     {
//                         while($sentencia->fetch())
//                         {
//                             $respuesta= (object) [
//                                 'id' =>  $id,
//                                 'texto' => $texto,
//                                 'peso' => $peso,
//                                 'hallazgo' => $hallazgo,
//                                 'recomendacion' => $recomendacion
//                             ];
//                             array_push($respuestas,$respuesta);
//                         }
//                         $resultado->valor = $respuestas;
//                         $sentencia->close();
                        
//                         for($i=0; $i < count($respuestas);$i++)
//                         {
//                             $respuesta = $respuestas[$i];
//                             $resultadoRespuestas = $this->consultarRespuestasNoCategorias($plantillaId,$seccionId,$preguntaId,$respuesta->id);
//                             if($resultadoRespuestas->mensajeError=="")
//                             {
//                                 $respuesta->categorias = $resultadoRespuestas->valor;
//                             }
//                             else
//                             {
//                                 $resultado->mensajeError = $resultadoRespuestas->mensajeError;
//                                 break;
//                             }
//                         }
//                     }
//                     else
//                         $resultado->mensajeError = "Falló el enlace del resultado";
//                 }
//                 else
//                     $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
//             }
//             else
//                 $resultado->mensajeError = "Falló el enlace de parámetros";
//         }
//         else
//             $resultado->mensajeError = "Falló la preparación: consultarRespuestasNo(" . $this->conexion->errno . ") " . $this->conexion->error;
            
//             return $resultado;
//     }
    
    private function consultarRespuestasNoCategorias($plantillaId,$seccionId,$preguntaId,$respuestaId)
    {
        $resultado = new Resultado();
        $categorias = array();
        $consulta = "SELECT id, categoria_id " .
            "FROM respuestas_no_categorias " .
            " WHERE plantilla_id = ? AND seccion_id= ? AND pregunta_id = ? AND respuesta_no_id = ? ".
            "ORDER BY id";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            
            if($sentencia->bind_param("iiii",$plantillaId,$seccionId,$preguntaId,$respuestaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id,$categoriaId))
                    {
                        while($sentencia->fetch())
                        {
                            $categoria= (object) [
                                'id' =>  $id,
                                'categoriaId' =>  $categoriaId
                            ];
                            array_push($categorias,$categoria);
                        }
                        $resultado->valor = $categorias;
                        $sentencia->close();
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
            $resultado->mensajeError = "Falló la preparación: consultarRespuestasNoCategorias(" . $this->conexion->errno . ") " . $this->conexion->error;
            
            return $resultado;
    }
    
    private function crearRegistroAvance($id, $comentario, $cumplimiento, $fechaAlta, $fechaModificacion, $archivos, $usuarioId, $usuarioNombre, $usuarioApellido)
    {
        $registro =  (object)[
            "id" => $id,
            "comentario" => $comentario,
            "cumplimiento" => $cumplimiento,
            "fechaAlta" => $fechaAlta,
            "fechaModificacion" => $fechaModificacion,
            "archivos" => $archivos,
            "usuarioId" => $usuarioId,
            "usuarioNombre" => $usuarioNombre,
            "usuarioApellido" => $usuarioApellido
            
        ];
        
        $registro->usuarioNombreCompleto = $registro->usuarioNombre . " " . $registro->usuarioApellido;
        //$registro->nombreId =  $registro->usuarioNombreCompleto ." (".$registro->id.")";
        $registro->fotoPerfil =  "../fotos/usuario". $registro->usuarioId .".jpg";
        if(file_exists($registro->fotoPerfil))
            $registro->fotoPerfil =  "php/fotos/usuario". $registro->usuarioId .".jpg";
        else
            $registro->fotoPerfil =  "php/fotos/default.jpg";
        
        return $registro;
    }
    
    private function crearRegistroRecomendacion($id,  $edt, $titulo, $responsableId, $responsableNombre, $reponsableApellido, $fechaAlta, $prioridad, $cumplimiento, $fechaVencimiento, $fechaFinalizacion , $terminada, $estatusValidacionId, $estatusValidacionDescripcion, $fechaValidacion, $validadorId,$validadorNombre, $validadorApellido, $administradorId, $administradorNombre, $administradorApellido, $empresaId, $empresaNombre, $numeroComentarios, $estatusValidacionIcono, $estatusValidacionColor,$comentariosValidacion, $fechaModificacion)
    {
        $registro =  (object)[
            "id" => $id,
            "edt" => $edt,
            "titulo" => $titulo,
            "usuarioId" => $responsableId,
            "usuarioNombre" => $responsableNombre,
            "usuarioApellido" => $reponsableApellido,
            "fechaAlta" => $fechaAlta,
            "prioridad" => $prioridad,
            "cumplimiento" => $cumplimiento,
            "fechaVencimiento" => $fechaVencimiento,
            "fechaFinalizacion" => $fechaFinalizacion,
            "terminada" => $terminada,
            "estatusValidacionId" => $estatusValidacionId,
            "estatusValidacionDescripcion" => $estatusValidacionDescripcion,
            "estatusValidacionIcono" => $estatusValidacionIcono,
            "estatusValidacionColor" => $estatusValidacionColor,
            "fechaValidacion" => $fechaValidacion,
            'administradorId' => $administradorId,
            'administradorNombre' => $administradorNombre,
            'administradorApellido' => $administradorApellido,
            'validadorId' => $validadorId,
            'validadorNombre' => $validadorNombre,
            'validadorApellido' => $validadorApellido,
            'empresaId' => $empresaId,
            "empresaNombre" => $empresaNombre,
            "numeroComentarios" => $numeroComentarios,
            "comentariosValidacion" => $comentariosValidacion,
            "fechaModificacion" => $fechaModificacion
            
        ];
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
        
        if($registro->validadorId==null || $registro->validadorId=="")
        {
            $registro->validadorId = $registro->administradorId;
            $registro->validadorNombre = $registro->administradorNombre;
            $registro->validadorApellido = $registro->administradorApellido;
            $registro->validadorNombreCompleto = $registro->validadorNombre . " " . $registro->validadorApellido;
            $registro->validadorFotoPerfil =  "../fotos/usuario". $registro->administradorId .".jpg";
            if(file_exists($registro->validadorFotoPerfil))
                $registro->validadorFotoPerfil =  "php/fotos/usuario". $registro->administradorId .".jpg";
            else
                $registro->validadorFotoPerfil =  "php/fotos/default.jpg";
                    
        }
        else
        {
            $registro->validadorNombreCompleto = $registro->validadorNombre . " " . $registro->validadorApellido;
            $registro->validadorFotoPerfil =  "../fotos/usuario". $registro->validadorId .".jpg";
            if(file_exists($registro->validadorFotoPerfil))
                $registro->validadorFotoPerfil =  "php/fotos/usuario". $registro->validadorId .".jpg";
            else
                $registro->validadorFotoPerfil =  "php/fotos/default.jpg";
        }
        
        $registro->empresaLogo =  "../logos_empresas/logo". $registro->empresaId .".png";
        if(file_exists($registro->empresaLogo))
            $registro->empresaLogo =  "php/logos_empresas/logo". $registro->empresaId .".png";
        else
            $registro->empresaLogo =  "php/logos_empresas/default.png";
            
        return $registro;
    }
    
    private function crearRegistro($id,  $plantillaId, $plantillaNombre, $fechaEjecucion, $empresaId, $empresaNombre, $empresaNombreCorto, $contadorEmpresa, $tipoAuditoriaId, $puntuacion, $nivelCompromiso, $implementacion, $verificacion,$tipoEmpresaNivelCompromiso, $tipoEmpresaImplementacion, $tipoEmpresaVerificacion, $paisNivelCompromiso, $paisImplementacion, $paisVerificacion, $observaciones, $buenasPracticas, $seguimiento, $fechaSeguimiento, $recomendacionesTotal,$recomendacionesPendientes, $fechaAlta, $sedeId, $sedeNombre, $fecha, $hora, $tipoAuditoriaNombre,$seguimientoFinalizado, $fechaSeguimientoFinalizado)
    {
//         $archivoIcono = '../../php/iconos/icono'.$plantillaId.'.png';
//         $icono = 'default.png';
//         if(file_exists($archivoIcono))
//             $icono = 'icono'.$plantillaId.'.png';
        $archivoIcono = '../../php/iconos_plantillas/plantilla'.$id.'.png';
        $icono = 'default.png';
        if(file_exists($archivoIcono))
            $icono = 'plantilla'.$id.'.png';
        
        $fechaReferencia = substr($fecha,0,10);
        $fechaReferencia = str_replace( '/', '.', $fechaReferencia );
        
        if($empresaId==null)
            $empresaId = ".NA";
        
        if($empresaNombreCorto=="" || $empresaNombreCorto==null)
            $empresaNombreCorto = "EMP".$empresaId;
        
        //$sedeNombreCorto = "SED".$sedeId;
        $sedeNombreCorto="";
        if($sedeNombre!="" && $sedeNombre!=null)
        {
            $sedeNombreCorto  = substr($sedeNombre,0,3);
            $sedeNombreCorto = str_replace(' ', '', $sedeNombreCorto);
            $sedeNombreCorto = $this->eliminarAcentos($sedeNombreCorto);
            $sedeNombreCorto = strtoupper($sedeNombreCorto);
        }
        
        $empresaNombreCorto = str_replace(' ', '', $empresaNombreCorto);
        $empresaNombreCorto = $this->eliminarAcentos($empresaNombreCorto);
        $empresaNombreCorto = strtoupper($empresaNombreCorto);
        
        $referencia = $empresaNombreCorto . "-".$sedeNombreCorto."-".$fechaReferencia."-".$contadorEmpresa;
        
        $registro= (object) [
            'icono' => $icono,
            'id' =>  $id,               
            'plantillaId' => $plantillaId,    
            'plantillaNombre' => $plantillaNombre,  
            'fechaEjecucion' => $fechaEjecucion,
            'empresaId' => $empresaId,   
            'empresaNombre' => $empresaNombre,   
            'empresaNombreCorto' => $empresaNombreCorto,   
            'contadorEmpresa' => $contadorEmpresa ,
            'referencia' => $referencia,
            'tipoAuditoriaId' => $tipoAuditoriaId,
            'tipoAuditoriaNombre' => $tipoAuditoriaNombre,
            'puntuacion' => $puntuacion,
            'nivelCompromiso' => $nivelCompromiso,
            'implementacion' => $implementacion,
            'verificacion' => $verificacion,
            'paisNivelCompromiso' => $paisNivelCompromiso,
            'paisImplementacion' => $paisImplementacion,
            'paisVerificacion' => $paisVerificacion,
            'tipoEmpresaNivelCompromiso' => $tipoEmpresaNivelCompromiso,
            'tipoEmpresaImplementacion' => $tipoEmpresaImplementacion,
            'tipoEmpresaVerificacion' => $tipoEmpresaVerificacion,
            'observaciones' => $observaciones,
            'buenasPracticas' => $buenasPracticas,
            'seguimiento' => $seguimiento,
            'fechaSeguimiento' => $fechaSeguimiento,
            'seguimientoFinalizado' => $seguimientoFinalizado,
            'fechaSeguimientoFinalizado' => $fechaSeguimientoFinalizado,
            'recomendacionesTotal' => $recomendacionesTotal,
            'recomendacionesPendientes' => $recomendacionesPendientes,
            'fechaAlta' => $fechaAlta,
            'sedeId' => $sedeId,
            'sedeNombre' => $sedeNombre,
            'fecha' => $fecha,
            'hora' => $hora
        ];
        
        $registro->recomendacionesTerminadas = $recomendacionesTotal - $recomendacionesPendientes;
        
        Porcentaje::formatearPorcentaje($registro, "puntuacion");
        Porcentaje::calcularPorcentaje($registro,'recomendacionesTerminadas','recomendacionesTotal',"porcentajeAvance");
        
        return $registro;
    }
    
   
    
    public function eliminar($llaves)
    {
        $this->conexion->autocommit(FALSE);
        $resultado = $this->eliminarRespuestasNoAuditoria($llaves->id);
        if($resultado->correcto())
        {
            $resultado = $this->eliminarRespuestasSiAuditoria($llaves->id);
            if($resultado->correcto())
            {
                $resultado = $this->eliminarPreguntas($llaves->id);
                if($resultado->correcto())
                {
                    $resultado = $this->eliminarSecciones($llaves->id);
                    if($resultado->correcto())
                    {
                        //$sentencia->close();
                        $consulta = " DELETE FROM auditorias "
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

    
    
    public function consultarPorcentajesCumplimientoSeccion($auditoriaId)
    {
        $resultado = new Resultado();
        $llaves= (object) [
            'id' => $auditoriaId,
        ];
        $resultado = $this->consultarPorLlaves($llaves);
        if($resultado->correcto())
        {
            $plantillaId = $resultado->valor->plantillaId;
            $llaves= (object) [
                'plantillaId' => $plantillaId,
                'auditoriaId' => $auditoriaId
            ];
            $resultado = $this->consultarValoresSecciones($llaves);
        }
        return $resultado;
    }
    
    public function getHallazgos($secciones,$tipo)
    {
        $resultado = new Resultado();
        $observaciones = array();
        for ($s = 0; $s < count($secciones); $s++) 
        {
            $seccion = $secciones[$s];
            $elementos = explode(". ", $seccion->texto);
            if(count($elementos)>1)
            {
                $criterio = $elementos[1];
                for ($p = 0; $p < count($seccion->preguntas); $p++)
                {
                    $pregunta = $seccion->preguntas[$p];
                    if($pregunta->tipo=="sn")
                    {
                        switch($pregunta->valor)
                        {
                            case "S":
                                for ($r = 0; $r < count($pregunta->respuestas_si); $r++)
                                {
                                    $respuesta = $pregunta->respuestas_si[$r];
                                   
                                    if($respuesta->$tipo==1)
                                    {
                                        if($respuesta->valor==1)
                                        {
                                            $departamento = $respuesta->departamentoNombre;
                                            if($respuesta->responsable=="")
                                                $departamento = "No asignado";
                                            else if($departamento=="")
                                                $departamento = "Sin departamento";
                                            $observacion = (object)['criterio' =>$criterio, 
                                                'departamento' => $departamento, 
                                                'responsableId' => $respuesta->responsable,
                                                'hallazgo' => $respuesta->hallazgo,
                                                'recomendacion' => $respuesta->recomendacion,
                                                'seccionId' => $seccion->id,
                                                'preguntaId' => $pregunta->preguntaId,
                                                'respuestaId' => $respuesta->id
                                            ];
                                                
                                            array_push($observaciones,$observacion);
                                        }
                                    }
                                }
                            break;
                            case "N":
                                if($pregunta->$tipo==1)
                                {
                                    $departamento = $pregunta->departamentoNombre;
                                    if($pregunta->responsable=="")
                                        $departamento = "No asignado";
                                    else if($departamento=="")
                                        $departamento = "Sin departamento";
                                    $observacion = (object)['criterio' =>$criterio, 
                                        'departamento' => $departamento, 
                                        'responsableId' => $pregunta->responsable,
                                        'hallazgo' => $pregunta->hallazgo,
                                        'recomendacion' => $pregunta->recomendacion,
                                        'seccionId' => $seccion->id,
                                        'preguntaId' => $pregunta->preguntaId,
                                        'respuestaId' => null
                                    ];
                                    array_push($observaciones,$observacion);
                                }
                            break;
                        }
                    }
                }
                if($seccion->$tipo==1 && $seccion->hallazgo!="")
                 {
                     $departamento = $seccion->departamentoNombre;
                     if($seccion->responsable=="")
                         $departamento = "No asignado";
                     else if($departamento=="")
                        $departamento = "Sin departamento";
                     $observacion = (object)['criterio' =>$criterio, 
                         'departamento' => $departamento, 
                         'responsableId' => $seccion->responsable,
                         'hallazgo' => $seccion->hallazgo,
                         'recomendacion' => $seccion->recomendacion,
                         'seccionId' => $seccion->id,
                         'preguntaId' => null,
                         'respuestaId' => null
                     ];
                     array_push($observaciones,$observacion);
                 }
            }
            
        }
        $resultado->valor = $observaciones;
        return $resultado;
    }
    
    public function iniciarSeguimiento($llaves)
    {
        $resultado = new Resultado();
        $this->conexion->autocommit(FALSE);
        
        $resultado = $this->consultarPorLlaves($llaves);
        if($resultado->correcto())
        {
            $auditoria = $resultado->valor;
            if($auditoria->seguimiento!=1)
            {
                $resultado = $this->actualizarSeguimientoIniciado($llaves->id);
                if($resultado->correcto())
                {
                    $llaves= (object) [
                        'auditoriaId' =>  $llaves->id,
                        'plantillaId' =>  $llaves->plantillaId
                    ];
                    $secciones = array();
                    $resultado = $this->consultarValoresSecciones($llaves);
                    if($resultado->correcto())
                    {
                        $secciones = $resultado->valor;
                        $resultado = $this->getHallazgos($secciones,"notificacion");
                        if($resultado->correcto())
                        {
                            $hallazgos = $resultado->valor;
                            $resultado = $this->insertarRecomendaciones($llaves->auditoriaId,$hallazgos);
                            if($resultado->correcto())
                            {
                                $this->enviarNotificacionInicioSeguimiento($llaves->auditoriaId,"","","");
                            }
                        }
                    }
                    
                }
                if($resultado->correcto())
                {
                    $this->conexion->commit();
                    $resultado->valor = $llaves->auditoriaId;;
                }
                else
                    $this->conexion->rollback();
            }
            else 
            {
                $resultado->mensajeError = "Acción invalida, el seguimiento se inició previamente el " . $auditoria->fechaSeguimiento;
                $resultado->codigoError = CodigoError::ADVERTENCIA;
            }
        }
       
        return $resultado;
    }
    
    public function finalizarSeguimiento($llaves)
    {
        $resultado = new Resultado();
        $this->conexion->autocommit(FALSE);
        
        $resultado = $this->consultarPorLlaves($llaves);
        if($resultado->correcto())
        {
            $auditoria = $resultado->valor;
            if($auditoria->seguimientoFinalizado!=1)
            {
                $resultado = $this->actualizarSeguimientoFinalizado($llaves->id);
                if($resultado->correcto())
                {
                    $llaves= (object) [
                        'auditoriaId' =>  $llaves->id,
                        'plantillaId' =>  $llaves->plantillaId
                    ];
                    
                }
                if($resultado->correcto())
                {
                    $this->conexion->commit();
                    $resultado->valor = $llaves->auditoriaId;;
                }
                else
                    $this->conexion->rollback();
            }
            else
            {
                $resultado->mensajeError = "Acción invalida, el seguimiento se finalizó previamente el " . $auditoria->fechaSeguimiento;
                $resultado->codigoError = CodigoError::ADVERTENCIA;
            }
        }
        
        return $resultado;
    }
    
    public function enviarNotificacionEvidenciaValidada($recomendacionId,$usuario, $modelo)
    {
        $resultado = new Resultado();
        
        $resultado = $this->consultarRecomendacionPorLlaves((object)["id"=> $recomendacionId]);
        if($resultado->correcto())
        {
            $recomendacion = $resultado->valor;
            $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
            $resultado = $usuariosRepositorio->consultarPorLlaves((object)["id" => $recomendacion->usuarioId]);
            if($resultado->correcto())
            {
                $responsable = $resultado->valor;
                $usuarios = array();
                if($responsable!=null)
                    array_push($usuarios,$responsable);
                
                $administrador_correo = new AdministradorCorreo();
                
                
                $contenido = "<tr>
                            <td align='center' class='padding-copy' style='font-size: 25px; font-family: Helvetica, Arial, sans-serif; color: #548dd4; padding-top: 0px;'><strong>¡Hola!</strong><br></td>
                        </tr>
                        <tr>
                            <td align='center' class='padding-copy textlightStyle' style='padding: 20px 0 0 0; font-size: 16px; line-height: 25px; font-family: Helvetica, Arial, sans-serif; color: #3F3D33;'>
                            <p>El equipo de especialistas de Handel ha verificado las evidencias que enviaste para el cierre de la acción</p>
							<p><strong>$recomendacion->titulo</strong></p>";
               
                if($modelo->comentariosValidacion!="")
                {
                    $contenido.="<p>El usuario $usuario->nombreCompleto añadió el siguiente comentario:</p>
                                    <p><strong>$modelo->comentariosValidacion</strong></p>";
                    
                }
                $contenido.="<p>Puedes responder a este mensaje directamente ingresando a SIVAH (<em><a title='Accede a SIVAH' href='https://mosaico.io/sivah.apps-handel.com' style='color: #3F3D33; text-decoration: none; font-weight: bold;'>sivah.apps-handel.com</a></em>) o dando clic en el botón inferior</p>
                             <p>Para la acción validada no es necesario realizar nada más dentro del sistema SIVAH</p>   
							</td>
                        </tr>";
                
                
                $resultado = $administrador_correo->enviarNotificacionSIVAH("validacion",$usuarios,"¡Evidencia validada!","Actualización de tu auditoría","#3f9e2d","¡Tu evidencia fue validada!", $contenido,"Responder","https://sivah.apps-handel.com",false);
                if($resultado->correcto())
                {
                    //$resultado->valor = $modelo->id;
                }
            }
        }
        return $resultado;
    }
    
    public function enviarNotificacionEvidenciasPendientes($usuario)
    {
        $resultado = new Resultado();
        
        $usuarios = array();
        array_push($usuarios,$usuario);
         array_push($usuarios,(object)["nombreUsuario"=> "noemi@handel-sce.com"]);
         array_push($usuarios,(object)["nombreUsuario"=> "eduardo@handel-sce.com"]);
//         array_push($usuarios,$usuario);
                
        $administrador_correo = new AdministradorCorreo();
        
        
        
        $contenido = "<tr>
                <td align='center' class='padding-copy' style='font-size: 25px; font-family: Helvetica, Arial, sans-serif; color: #548dd4; padding-top: 0px;'><strong>¡Hola! $usuario->nombre</strong><br></td>
            </tr>
            <tr>
                <td align='center' class='padding-copy textlightStyle' style='padding: 20px 0 0 0; font-size: 16px; line-height: 25px; font-family: Helvetica, Arial, sans-serif; color: #3F3D33;'>
                <p>Se han recibido nuevas evidencias para validación en SIVAH</p>
				";
        
        $resultado = $this->consultarEvidenciasPendientesSede($usuario);
        if($resultado->correcto())
        {
           $sedes = $resultado->valor;
           for ($i = 0; $i < count($sedes); $i++) 
           {
               $sede = $sedes[$i];
               if($sede->pendientes==1)
                   $pendiente = "Evidencia pendiente";
               else
                   $pendiente = "Evidencias pendientes";
               $contenido.=  "<p><strong>$sede->empresaNombre - $sede->sedeNombre - $sede->pendientes $pendiente de validar</strong></p>";
           }
           
        }
        
        
        $contenido.="<p>Favor de verificar las evidencias en breve de sus empresas asignadas</p>
				</td>
            </tr>";
        
        
        $resultado = $administrador_correo->enviarNotificacionSIVAH("evidencias_pendientes_sivah",$usuarios,"¡Evidencias por validar!","Actualización de tus auditorías","#0a77b6","¡Evidencias por validar!", $contenido,"Llévame a SIVAH","https://sivah.apps-handel.com",true);
        if($resultado->correcto())
        {
            
        }
        return $resultado;
    }
    
    public function enviarNotificacionEvidenciasMensualUsuarios($nombreUsuario,$enviarA,$numeroUsuarios)
    {
        ini_set('max_execution_time', 0);
        $resultado = new Resultado();
        
        $usuarios = array();
        $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
//         if($nombreUsuario!="")
//         {
//             $resultado = $usuariosRepositorio->consultar(null,(object) ['nombreUsuario' =>  $nombreUsuario],false);
//         }
//         else
            $resultado = $usuariosRepositorio->consultar(null,(object) ['nombreUsuario' =>  $nombreUsuario, 'permisoSIVAH' =>  1, 'estatus' => 1],false);
            //$resultado = $this->consultarUsuariosSIVAH();
        
        if($resultado->correcto())
        {

            $usuarios = $resultado->valor;
            
            //var_dump($usuarios);
            
            $administrador_correo = new AdministradorCorreo();
            
            $usuariosAuditoria = array();
            $contador = 0;
            for ($i = 0; $i < count($usuarios); $i++) 
            {
                $usuario = $usuarios[$i];
                if($usuario->tipoUsuarioId != \TipoUsuario::ADMINISTRADOR)
                {
                    if($enviarA!=null && $enviarA!="")
                        $usuario->nombreUsuario = $enviarA;
                   
                    $resultado = $this->consultarAuditoriasUsuario($usuario);
                    if($resultado->correcto())
                    {
                        $usuario->auditorias = $resultado->valor;
                        if(count($usuario->auditorias)>0)
                        {
                            if($numeroUsuarios<=0)
                                array_push($usuariosAuditoria,$usuario);
                            else
                            {
                                if($contador < $numeroUsuarios)
                                    array_push($usuariosAuditoria,$usuario);
                                else 
                                    break;
                            }
                            $contador++;
                        }
                    }
                    else 
                        echo $resultado->mensajeError;
                }
            }
//             if($numeroUsuarios>0)
//                 $usuariosAuditoria = array_slice($usuariosAuditoria,0,$numeroUsuarios);
            
                
                
            for ($i = 0; $i < count($usuariosAuditoria); $i++) 
            {
                $usuario = $usuariosAuditoria[$i];
                $contenido = "<tr>
                                <td align='center' class='padding-copy' style='font-size: 25px; font-family: Helvetica, Arial, sans-serif; color: #548dd4; padding-top: 0px;'><strong>¡Hola $usuario->nombre!</strong><br></td>
                            </tr>
                            <tr>
                                <td align='center' class='padding-copy textlightStyle' style='padding: 20px 0 0 0; font-size: 16px; line-height: 25px; font-family: Helvetica, Arial, sans-serif; color: #3F3D33;'>
                                <p>Reporte mensual de avance de tus acciones derivados de la evaluación:</p>";
                
                
                for ($j = 0; $j < count($usuario->auditorias ); $j++)
                {
                    $auditoria = $usuario->auditorias [$j];
                    
                    $contenido.=  "<p><strong>$auditoria->empresaNombre - $auditoria->sedeNombre - $auditoria->fecha -  $auditoria->dias días transcurridos </strong></p>";
                
                }
                
                $resultado = $this->consultarNumeroEvidencias($usuario);
                if($resultado->correcto())
                {
                    $contenido.=  "<p>Tus acciones</p>";
                    $numeroEvidencias = $resultado->valor;
                    
                    $accionesTotal = $numeroEvidencias->total==1?"Acción asignada":"Acciones asignadas";
                    $accionesEnviadas =  $numeroEvidencias->enviadas==1?"Acción enviada":"Acciones enviadas";
                    $accionesValidadas =  $numeroEvidencias->validadas==1?"Acción validada":"Acciones validadas";
                    $accionesRechazadas =  $numeroEvidencias->rechazadas==1?"Acción rechazada":"Acciones rechazadas";
                    
                    $contenido.= "<p><strong>";
                    $contenido.=  "<span style='color:#000000;'>$numeroEvidencias->total $accionesTotal</span><br>";
                    $contenido.=  "<span style='color:#0775b5;'>$numeroEvidencias->enviadas $accionesEnviadas</span><br>";
                    $contenido.=  "<span style='color:#2f7020;'>$numeroEvidencias->validadas $accionesValidadas</span><br>";
                    $contenido.=  "<span style='color:#ad2f17;'>$numeroEvidencias->rechazadas $accionesRechazadas</span>";
                    $contenido.= "</strong></p>";
                }
                
                switch ($usuario->tipoUsuarioId)
                {
                    case \TipoUsuario::COORDINADOR:
                    case \TipoUsuario::SUPERVISOR:
                        $contenido.=  "<p>Tu equipo de trabajo</p>";
                        
                        $resultado = $this->consultarMiembrosEquipo($usuario);
                        if($resultado->correcto())
                        {
                            $equipo = $resultado->valor;
                            for ($j = 0; $j < count($equipo); $j++) 
                            {
                                $usuarioEquipo = $equipo[$j];
                                if($usuarioEquipo->id != $usuario->id)
                                {
                                    $resultado = $this->consultarNumeroEvidencias($usuarioEquipo);
                                    if($resultado->correcto())
                                    {
                                        $numeroEvidencias = $resultado->valor;
                                        if($numeroEvidencias->total!=0)
                                        {
                                            $accionesTotal = $numeroEvidencias->total==1?"Acción asignada":"Acciones asignadas";
                                            $accionesEnviadas =  $numeroEvidencias->enviadas==1?"Acción enviada":"Acciones enviadas";
                                            $accionesValidadas =  $numeroEvidencias->validadas==1?"Acción validada":"Acciones validadas";
                                            $accionesRechazadas =  $numeroEvidencias->rechazadas==1?"Acción rechazada":"Acciones rechazadas";
                                            
                                            $contenido.= "<p><strong>";
                                            $contenido.=  "<span style='color:#000000;'>$usuarioEquipo->nombreCompleto: </span>";
                                            $contenido.=  "<span style='color:#000000;'>$numeroEvidencias->total $accionesTotal, </span>";
                                            $contenido.=  "<span style='color:#0775b5;'>$numeroEvidencias->enviadas $accionesEnviadas, </span>";
                                            $contenido.=  "<span style='color:#2f7020;'>$numeroEvidencias->validadas $accionesValidadas, </span>";
                                            $contenido.=  "<span style='color:#ad2f17;'>$numeroEvidencias->rechazadas $accionesRechazadas</span>";
                                            $contenido.= "</strong></p>";
                                        }
                                    }
                                    else
                                        echo $resultado->mensajeError;
                                }
                            }
                        }
                        else 
                            echo $resultado->mensajeError;
                    break;
                }
                
                $contenido.=  "<p style='text-decoration: underline;'><strong>Recuerda que es importante que cierres tus hallazgos no más de 90 días después de la fecha de auditoría</strong></p>";
                
                $contenido.="<p>Ingresar a SIVAH (sivah.apps-handel.com) para verificar a detalle todas tus acciones</p>
                        				</td>
                                    </tr>";
                
                $usuariosCorreo = array();
                
                array_push($usuariosCorreo,$usuario);
                
                $asunto  = "¿Como van tus auditorías?";
                $titulo = "Actualización de tus auditorías";
                if(count($usuario->auditorias)==1)
                {
                    $asunto  = "¿Como va tu auditoría?";
                    $titulo = "Actualización de tu auditoría";
                }
               
                
                $resultado = $administrador_correo->enviarNotificacionSIVAH("evidencias_mensual_usuario",$usuariosCorreo, $asunto,$titulo,"#0775b5","Tu mes en SIVAH", $contenido,"Llévame a SIVAH","https://sivah.apps-handel.com",true, $usuario->nombre);
                if($resultado->correcto())
                {
                    Logger::log("log_envio","$i Correo enviado a ".$usuario->nombreUsuario,"envios_mensual_sivah/");
                    //$this->mensajeLog("envios_evidencias_mensual/","log_envio_evidencias_mensual","$i Correo enviado a ".$usuario->nombreUsuario);
                }
                sleep(10);
            }
           
        }
        return $resultado;
    }
    
    public function enviarNotificacionInicioSeguimiento($auditoriaId,$nombreUsuario,$enviarA,$numeroUsuarios)
    {
        ini_set('max_execution_time', 0);
        $resultado = new Resultado();
        
        if($nombreUsuario!="")
        {
            $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
            $resultado = $usuariosRepositorio->consultar(null,(object) ['nombreUsuario' =>  $nombreUsuario],false);
            
        }
        else
            $resultado = $this->consultarUsuariosAuditoria($auditoriaId);
            
            if($resultado->correcto())
            {
                $usuariosAuditoria = $resultado->valor;
                
                $administrador_correo = new AdministradorCorreo();
                
//                 $usuariosAuditoria = array();
                for ($i = 0; $i < count($usuariosAuditoria); $i++)
                {
                    $usuario = $usuariosAuditoria[$i];
                    if($enviarA!=null && $enviarA!="")
                        $usuario->nombreUsuario = $enviarA;
                    
                    $usuario->tipoUsuarioId = \TipoUsuario::USUARIO;
                    $resultado = $this->consultarAuditoriasUsuario($usuario);
                    
                    
                    if($resultado->correcto())
                    {
                        $usuario->auditorias = $resultado->valor;
//                         if(count($usuario->auditorias)>0)
//                         {
//                             array_push($usuariosAuditoria,$usuario);
//                         }
                        
                    }
                    else
                        echo $resultado->mensajeError;
                }
                if($numeroUsuarios>0)
                    $usuariosAuditoria = array_slice($usuariosAuditoria,0,$numeroUsuarios);
                
                for ($i = 0; $i < count($usuariosAuditoria); $i++)
                {
                    $usuario = $usuariosAuditoria[$i];
                    $contenido = "<tr>
                            <td align='center' class='padding-copy' style='font-size: 25px; font-family: Helvetica, Arial, sans-serif; color: #548dd4; padding-top: 0px;'><strong>¡Hola $usuario->nombre!</strong><br></td>
                        </tr>
                        <tr>
                            <td align='center' class='padding-copy textlightStyle' style='padding: 20px 0 0 0; font-size: 16px; line-height: 25px; font-family: Helvetica, Arial, sans-serif; color: #3F3D33;'>
                            <p>Derivado de la evaluación realizada en</p>";
                    
                    
                    if(count($usuario->auditorias)>0)
                    {
                        $auditoria = $usuario->auditorias [0];
                        
                        $contenido.=  "<p><strong>$auditoria->empresaNombre - $auditoria->sedeNombre - $auditoria->fecha </strong></p>";
                        
                        $resultado = $this->consultarNumeroEvidencias($usuario,$auditoriaId);
                        if($resultado->correcto())
                        {
                            $numeroEvidencias = $resultado->valor;
                            
                            $accionesTotal = $numeroEvidencias->total==1?"Te ha sido asignada $numeroEvidencias->total acción":"Te han sido asignadas $numeroEvidencias->total acciones";
                            
                            $contenido.=  "<p>$accionesTotal por realizar, a fin de solventarlas deberás proporcionar evidencias del cumplimiento (como documentos o fotografías) de cada una de las acciones, en cada caso debes demostrar que se han realizado acciones para que el hallazgo sea solucionado.</p>";
                            $contenido.=  "<p>El seguimiento se dará utilizando el sistema SIVAH (sivah.apps-handel.com) que facilitará la comunicación y recopilará las evidencias de cumplimiento. Hemos precargado tus acciones en el sistema para que puedas, desde este momento reportar avances y subir evidencias de cumplimiento. El sistema es muy intuitivo, pero recuerda que si tienes dudas puedes contactar a tu especialista asignado en Handel o ver directamente los tutoriales disponibles en el propio sistema.</p>";
                            $contenido.=  "<p>Algunas acciones pueden ser criticas para conservar tu certificación y debes prestar especial atención, para el resto de las acciones esperamos tener evidencias de cumplimiento desde este momento y hasta 90 días.</p>";
                            $contenido.=  "<p>¡Gracias por tu apoyo!</p>";
                            
                            $contenido.="</td>
                                </tr>";
                            
                            $usuariosCorreo = array();
                            
                            array_push($usuariosCorreo,$usuario);
                            
                            $asunto  = "Tus acciones asignadas en la evaluación";
                            $titulo = "Actualización de tu auditoría";
                            
                            $subtitulo = $numeroEvidencias->total==1?"$numeroEvidencias->total Acción asignada":"$numeroEvidencias->total Acciones asignadas";
                            
                            
                            $resultado = $administrador_correo->enviarNotificacionSIVAH("envios_inicio_seguimiento",$usuariosCorreo, $asunto,$titulo,"#0775b5",$subtitulo, $contenido,"Llévame a SIVAH","https://sivah.apps-handel.com",false, $usuario->nombre);
                            if($resultado->correcto())
                            {
                                Logger::log("log_envio","$i Correo enviado a ".$usuario->nombreUsuario,"envios_inicio_seguimiento/");
                                //$this->mensajeLog("envios_evidencias_mensual/","log_envio_evidencias_mensual","$i Correo enviado a ".$usuario->nombreUsuario);
                            }
                            sleep(10);
                        }
                    }
                    
                    
                    
                    
                    
                   
                }
                    
            }
            return $resultado;
    }
    
    function mensajeLog($carpeta,$archivo,$mensaje)
    {
        $mensaje = date("j/n/Y h:i:s") .":".$mensaje;
     
        if(!file_exists($carpeta))
            @mkdir($carpeta);
        file_put_contents($carpeta.$archivo.'_'.date("j.n.Y").'.log',  utf8_decode("\n".$mensaje) , FILE_APPEND);
        echo "<br>".utf8_decode($mensaje);
    }
    
    public function enviarNotificacionEvidenciaRechazada($recomendacionId,$usuario, $modelo)
    {
        $resultado = new Resultado();
        
        $resultado = $this->consultarRecomendacionPorLlaves((object)["id"=> $recomendacionId]);
        if($resultado->correcto())
        {
            $recomendacion = $resultado->valor;
            $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
            $resultado = $usuariosRepositorio->consultarPorLlaves((object)["id" => $recomendacion->usuarioId]);
            if($resultado->correcto())
            {
                $responsable = $resultado->valor;
                $usuarios = array();
                if($responsable!=null)
                    array_push($usuarios,$responsable);
                    
                    $administrador_correo = new AdministradorCorreo();
                    
                    
                    $contenido = "<tr>
                            <td align='center' class='padding-copy' style='font-size: 25px; font-family: Helvetica, Arial, sans-serif; color: #548dd4; padding-top: 0px;'><strong>¡Hola!</strong><br></td>
                        </tr>
                        <tr>
                            <td align='center' class='padding-copy textlightStyle' style='padding: 20px 0 0 0; font-size: 16px; line-height: 25px; font-family: Helvetica, Arial, sans-serif; color: #3F3D33;'>
                            <p>El equipo de especialistas de Handel ha verificado las evidencias que enviaste para el cierre de la acción</p>
							<p><strong>$recomendacion->titulo</strong></p>";
                    
                    if($modelo->comentariosValidacion!="")
                    {
                        $contenido.="<p>El usuario $usuario->nombreCompleto añadió el siguiente comentario:</p>
                                    <p><strong>$modelo->comentariosValidacion</strong></p>";
                        
                    }
                    $contenido.="<p>Puedes responder a este mensaje directamente ingresando a SIVAH (<em><a title='Accede a SIVAH' href='https://mosaico.io/sivah.apps-handel.com' style='color: #3F3D33; text-decoration: none; font-weight: bold;'>sivah.apps-handel.com</a></em>) o dando clic en el botón inferior</p>
                             <p>Para complementar o volver a enviar evidencias ingresa a SIVAH, recuerda que no se considerará terminada hasta que vuelvas a enviar información complementaria y sea validada por el equipo de especialistas de Handel</p>
							</td>
                        </tr>";
                    
                    
                    $resultado = $administrador_correo->enviarNotificacionSIVAH("validacion",$usuarios,"¡Evidencia rechazada!","Actualización de tu auditoría","#ad2f17","¡Tu evidencia fue rechazada!", $contenido,"Responder","https://sivah.apps-handel.com",false);
                    if($resultado->correcto())
                    {
                        
                    }
            }
        }
        
        
        return $resultado;
    }
    
    
    
    function insertarRecomendaciones($auditoriaId,$hallazgos)
    {
        $resultado = new Resultado();
        for ($i = 0; $i <  count($hallazgos); $i++)
        {
            $hallazgo = $hallazgos[$i];
            
            if(isset($hallazgo->responsableId))
            {
                if($hallazgo->responsableId=="")
                    $hallazgo->responsableId = null;
            }
            else
                $hallazgo->responsableId = null;
                
            $consulta = "UPDATE recomendaciones
                    SET titulo = ?,
                        responsable_id = ?,
                        fecha_modificacion = NOW()
                    WHERE auditoria_id=? AND edt = ?";
            
            $edt = $auditoriaId ."." .$hallazgo->seccionId; 
            
            if($hallazgo->preguntaId!=null)
                $edt.= "." . $hallazgo->preguntaId;
            if($hallazgo->respuestaId!=null)
                $edt.= "." . $hallazgo->respuestaId;
                    
                    
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param("siis",$hallazgo->recomendacion,$hallazgo->responsableId, $auditoriaId, $edt))
                {
                    if($sentencia->execute())
                    {
                        $sentencia->close();
                        
                        $resultado = $this->numeroRegistrosRecomendaciones($auditoriaId,$edt);
                        if($resultado->correcto())
                        {
                            $count = $resultado->valor;
                            
                            if($count==0)
                            {
                                $consulta = "INSERT INTO recomendaciones(edt, auditoria_id, seccion_id, pregunta_id, respuesta_id, responsable_id, titulo, fecha_alta, fecha_modificacion, cumplimiento, fecha_vencimiento) " .
                                    "VALUE(?, ?, ?, ?, ?, ?, ?, NOW(),NOW(), 0,  NOW() + INTERVAL 3 MONTH)";
                                if($sentencia = $this->conexion->prepare($consulta))
                                {
                                    if($sentencia->bind_param("siiiiis", $edt,$auditoriaId, $hallazgo->seccionId, $hallazgo->preguntaId,$hallazgo->respuestaId,$hallazgo->responsableId, $hallazgo->recomendacion))
                                    {
                                        if($sentencia->execute())
                                        {
                                            $sentencia->close();
                                        }
                                        else
                                        {
                                            $resultado->codigoError = $this->conexion->errno;
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
                                    $resultado->codigoError = $this->conexion->errno;
                                    $resultado->mensajeError = __FUNCTION__.". Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
                                    break;
                                }
                            }
                            
                        }
                        
                        
                    }
                    else
                    {
                        $resultado->codigoError = $this->conexion->errno;
                        $resultado->mensajeError = __FUNCTION__.". Falló la ejecución update (" . $this->conexion->errno . ") " . $this->conexion->error;
                        break;
                    }
                }
                else
                {
                    $resultado->mensajeError = "Falló el enlace de parámetros update";
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
        return $resultado;
    }
    public function consultarRecomendacionesPendientesUsuario($llaves, $criteriosSeleccion,$usuario)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $filtros = $this->getFiltros($usuario,(object)[]);
        array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'RC','campo'=>'auditoria_id','valor'=>$llaves->id]);
        
        if($criteriosSeleccion!=null)
        {
            if(isset($criteriosSeleccion->estatusValidacionId))
            {
                if($criteriosSeleccion->estatusValidacionId!="" && $criteriosSeleccion->estatusValidacionId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'RC','campo'=>'estatus_validacion_id','valor'=>$criteriosSeleccion->estatusValidacionId]);
            }
        }
        
        
        
        $where = $this->where($filtros);
        $consulta = $this->consultaBaseRecomendaciones .
                   $where .
                   "ORDER BY TU.orden, U.nombre, RC.titulo";

        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id,  $edt, $titulo, $responsableId, $responsableNombre, $reponsableApellido, $fechaAlta, $prioridad, $cumplimiento, $fechaVencimiento, $fechaFinalizacion , $terminada, $estatusValidacionId,$estatusValidacionDescripcion, $fechaValidacion, $validadorId,$validadorNombre, $validadorApellido, $administradorId,$administradorNombre, $administradorApellido, $empresaId, $empresaNombre,$numeroComentarios, $estatusValidacionIcono, $estatusValidacionColor,$comentariosValidacion, $fechaModificacion))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistroRecomendacion($id,  $edt, $titulo, $responsableId, $responsableNombre, $reponsableApellido, $fechaAlta, $prioridad, $cumplimiento, $fechaVencimiento, $fechaFinalizacion , $terminada, $estatusValidacionId,$estatusValidacionDescripcion, $fechaValidacion, $validadorId,$validadorNombre, $validadorApellido,$administradorId, $administradorNombre, $administradorApellido, $empresaId, $empresaNombre, $numeroComentarios, $estatusValidacionIcono, $estatusValidacionColor,$comentariosValidacion, $fechaModificacion);
                         
                            
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
    
    public function consultarRecomendaciones($criteriosSeleccion,$usuario)
    {
        $resultado = new Resultado();
        $registros = array();
        
       // $filtros = $this->getFiltros($usuario,(object)[]);
       $filtros = array();
       // array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'RC','campo'=>'auditoria_id','valor'=>$llaves->id]);
        
        if($criteriosSeleccion!=null)
        {
            if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'E1', 'campo'=>'id','valor'=>$criteriosSeleccion->empresaId]);
            if(isset($criteriosSeleccion->sedeId) && $criteriosSeleccion->sedeId!="")
              array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'sede_id','valor'=>$criteriosSeleccion->sedeId]);
              if(isset($criteriosSeleccion->departamentoId) && $criteriosSeleccion->departamentoId!="")
                  array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'departamento_id','valor'=>$criteriosSeleccion->departamentoId]);
                  
            if(isset($criteriosSeleccion->estatusValidacionId))
            {
                if($criteriosSeleccion->estatusValidacionId!="" && $criteriosSeleccion->estatusValidacionId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'RC','campo'=>'estatus_validacion_id','valor'=>$criteriosSeleccion->estatusValidacionId]);
            }
            if(isset($criteriosSeleccion->ano) && $criteriosSeleccion->ano!="")
                array_push($filtros,(object)['tipoDato'=>'int', 'campo'=>'YEAR(RC.fecha_alta)','valor'=>$criteriosSeleccion->ano]);
            if(isset($criteriosSeleccion->mes) && $criteriosSeleccion->mes!="")
                array_push($filtros,(object)['tipoDato'=>'int', 'campo'=>'MONTH(RC.fecha_alta)','valor'=>$criteriosSeleccion->mes]);
            if(isset($criteriosSeleccion->administradorId)  && $criteriosSeleccion->administradorId!="")
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'E1', 'campo'=>'administrador_sivah_id','valor'=>$criteriosSeleccion->administradorId]);
                    //            
        }
        
        
        
        
        $where = $this->where($filtros);
        $consulta = $this->consultaBaseRecomendaciones .
        $where .
        " ORDER BY  UNIX_TIMESTAMP(fecha_finalizacion) desc";
        
       // echo $consulta;
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id,  $edt, $titulo, $responsableId, $responsableNombre, $reponsableApellido, $fechaAlta, $prioridad, $cumplimiento, $fechaVencimiento, $fechaFinalizacion , $terminada, $estatusValidacionId,$estatusValidacionDescripcion, $fechaValidacion, $validadorId,$validadorNombre, $validadorApellido, $administradorId,$administradorNombre, $administradorApellido, $empresaId, $empresaNombre,$numeroComentarios, $estatusValidacionIcono, $estatusValidacionColor,$comentariosValidacion, $fechaModificacion))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistroRecomendacion($id,  $edt, $titulo, $responsableId, $responsableNombre, $reponsableApellido, $fechaAlta, $prioridad, $cumplimiento, $fechaVencimiento, $fechaFinalizacion , $terminada, $estatusValidacionId,$estatusValidacionDescripcion, $fechaValidacion, $validadorId,$validadorNombre, $validadorApellido,$administradorId, $administradorNombre, $administradorApellido, $empresaId, $empresaNombre, $numeroComentarios, $estatusValidacionIcono, $estatusValidacionColor,$comentariosValidacion,$fechaModificacion);
                            
                            
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
    
    public function consultarActivasPorUsuario($criteriosSeleccion, $usuario)
    {
        $resultado = new Resultado();
        $registros = array();
        $filtros = $this->getFiltros($usuario,$criteriosSeleccion);
        
        $and = $this->and($filtros);
//         if($criteriosSeleccion!=null)
//         {
//             if(isset($criteriosSeleccion->nombre))
//             {
//                 if($criteriosSeleccion->nombre!="")
//                 {
//                     array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'A','campo'=>'nombre','valor'=>$criteriosSeleccion->nombre]);
//                 }
//             }
//             if(isset($criteriosSeleccion->id))
//             {
//                 if($criteriosSeleccion->id!="")
//                 {
//                     array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'A','campo'=>'id','valor'=>$criteriosSeleccion->id]);
//                 }
//             }
            
//             $where = $this->where($filtros);
//         }
        $consulta ="SELECT * FROM (SELECT A.id, A.plantilla_id, P.nombre plantillaNombre, IFNULL(DATE_FORMAT(A.fecha_ejecucion,'%d/%m/%Y %H:%i:%s'),'')fecha_ejecucion, A.empresa_id, E.nombre, IFNULL(E.nombre_corto,'')nombre_corto, A.contador_empresa,tipo_auditoria_id,
                (SELECT SUM(porcentaje) / COUNT(*) as porcentaje 
                        FROM auditoria_secciones ASS
                        	INNER JOIN auditorias A1 ON A1.id = ASS.auditoria_id
                        	INNER JOIN secciones S ON S.plantilla_id = A1.plantilla_id AND S.id= ASS.seccion_id
                        WHERE S.orden>1 AND A1.id = A.id ) puntuacion, 
                A.nivel_compromiso, A.implementacion, A.verificacion,
                TE.nivel_compromiso tipoEmpresaNivelCompromiso, TE.implementacion tipoEmpresaImplementacion, TE.verificacion tipoEmpresaVerificacion, 
                PS.nivel_compromiso paisNivelCompromiso, PS.implementacion paisImplementacion, PS.verificacion paisVerificacion, observaciones, buenas_practicas, seguimiento, IFNULL(DATE_FORMAT(A.fecha_seguimiento,'%d/%m/%Y %H:%i:%s'),'')fecha_seguimiento,
                 (SELECT count(*)
                FROM recomendaciones R
                	LEFT JOIN usuarios U ON U.id = R.responsable_id
                	LEFT JOIN empresas E1 ON E1.id = U.empresa_id
                WHERE auditoria_id = A.id $and) recomendacionesTotal,
            (SELECT count(*)
                FROM recomendaciones R
                	LEFT JOIN usuarios U ON U.id = R.responsable_id
                	LEFT JOIN empresas E1 ON E1.id = U.empresa_id
                WHERE auditoria_id = A.id AND R.estatus_validacion_id!=2 $and) recomendacionesPendientes, 
                IFNULL(DATE_FORMAT(A.fecha_alta,'%d/%m/%Y %H:%i:%s'),'') AS fechaAlta,A.sede_id, S.nombre AS sedeNombre, IFNULL(DATE_FORMAT(A.fecha,'%d/%m/%Y'),''), A.hora, TA.nombre AS tipoAuditoriaNombre, A.seguimiento_finalizado, A.fecha_seguimiento_finalizado
             FROM auditorias A 
                 INNER JOIN plantillas P on A.plantilla_id = P.id 
                LEFT JOIN empresas E on A.empresa_id = E.id 
                LEFT JOIN tipos_empresa TE ON TE.id = E.tipo_empresa_id
                LEFT JOIN paises PS ON PS.id = E.pais_id
                LEFT JOIN sedes S ON A.sede_id = S.id
                LEFT JOIN tipos_auditoria TA ON TA.id = A.tipo_auditoria_id
            ORDER BY UNIX_TIMESTAMP(fecha) desc, hora desc
            )SB 
            WHERE recomendacionesTotal > 0";
        
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id,  $plantillaId, $plantillaNombre, $fechaEjecucion, $empresaId, $empresaNombre, $empresaNombreCorto, $contadorEmpresa,$tipoAuditoriaId, $puntuacion, $nivelCompromiso, $implementacion, $verificacion,$tipoEmpresaNivelCompromiso, $tipoEmpresaImplementacion, $tipoEmpresaVerificacion, $paisNivelCompromiso, $paisImplementacion, $paisVerificacion, $observaciones, $buenasPracticas, $seguimiento, $fechaSeguimiento, $recomendacionesTotal, $recomendacionesPendientes, $fechaAlta, $sedeId, $sedeNombre, $fecha, $hora, $tipoAuditoriaNombre,$seguimientoFinalizado, $fechaSeguimientoFinalizado))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id,  $plantillaId, $plantillaNombre, $fechaEjecucion, $empresaId, $empresaNombre, $empresaNombreCorto, $contadorEmpresa,$tipoAuditoriaId, $puntuacion, $nivelCompromiso, $implementacion, $verificacion,$tipoEmpresaNivelCompromiso, $tipoEmpresaImplementacion, $tipoEmpresaVerificacion, $paisNivelCompromiso, $paisImplementacion, $paisVerificacion, $observaciones, $buenasPracticas, $seguimiento,$fechaSeguimiento, $recomendacionesTotal,$recomendacionesPendientes,$fechaAlta, $sedeId, $sedeNombre, $fecha, $hora, $tipoAuditoriaNombre,$seguimientoFinalizado, $fechaSeguimientoFinalizado);
                            
                            
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
    
    public function getFiltros($usuario, $criteriosSeleccion)
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
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'E1', 'campo'=>'id','valor'=>$criteriosSeleccion->empresaId]);
                else
                {
                    $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
                    $resultado = $usuariosRepositorio->consultarIdsEmpresas($usuario->empresaId);
                    if($resultado->correcto())
                    {
                        $empresasIds = implode(",", $resultado->valor);
                        array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'E1', 'campo'=>'id','operador'=>'IN','valor'=>$empresasIds]);
                    }
                }
            break;
            case \TipoUsuario::ADMINISTRADOR:
                if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'EM', 'campo'=>'id','valor'=>$criteriosSeleccion->empresaId]);
            break;
            default:
                 array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'id','operador'=>'IN','valor'=>$usuario->id]);
            break;
        }
        if(isset($criteriosSeleccion->sedeId) && $criteriosSeleccion->sedeId!="")
            array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'S', 'campo'=>'id','valor'=> $criteriosSeleccion->sedeId]);
        if(isset($criteriosSeleccion->departamentoId) && $criteriosSeleccion->departamentoId!="")
            array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'D', 'campo'=>'id','valor'=> $criteriosSeleccion->departamentoId]);
        return $filtros;
    }
    
    public function consultarAvancesRecomendacion($llaves, $usuario)
    {
        $resultado = new Resultado();
        $registros = array();
        
       // $filtros = array(();
        //array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'RC','campo'=>'auditoria_id','valor'=>$llaves->id]);
        //array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'RC','campo'=>'auditoria_id','valor'=>$llaves->id]);
        //$and = $this->and($filtros);
        $consulta = $this->consultaBaseAvances .
                     " WHERE recomendacion_id = ?
                       ORDER BY RA.id";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("i", $llaves->id))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $comentario, $cumplimiento, $fechaAlta, $fechaModificacion, $archivos, $usuarioId, $usuarioNombre, $usuarioApellido ))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistroAvance($id, $comentario, $cumplimiento, $fechaAlta, $fechaModificacion, $archivos, $usuarioId, $usuarioNombre, $usuarioApellido);
                            
                            
                          
                            
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
    
    public function consultarArchivosAvance($llaves, $usuario)
    {
        $resultado = new Resultado();
        $registros = array();
        
        // $filtros = array(();
        //array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'RC','campo'=>'auditoria_id','valor'=>$llaves->id]);
        //array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'RC','campo'=>'auditoria_id','valor'=>$llaves->id]);
        //$and = $this->and($filtros);
        $consulta = "SELECT ARC.id, nombre, tamano, fecha
                       FROM recomendaciones_avances_archivos ARC
                      WHERE avance_id = ?
                       ORDER BY ARC.id";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("i", $llaves->id))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombre, $tamano, $fecha ))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro =  (object)[
                                "id" => $id,
                                "nombre" => $nombre,
                                "tamano" => $tamano,
                                "fecha" => $fecha
                                
                            ];
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
    
    public function insertarAvance($recomendacionId,$modelo,$usuario)
    {
        $this->conexion->autocommit(FALSE);
        $resultado =  $this->calcularId("id","recomendaciones_avances");
        if($resultado->mensajeError=="")
        {
            $id = $resultado->valor;
            $consulta = "INSERT INTO recomendaciones_avances(id, recomendacion_id, fecha_alta, fecha_modificacion, cumplimiento, comentario, usuario_id) " .
                "VALUE(?, ?,NOW(), NOW(), ?, ?,?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if( $sentencia->bind_param("iiisi", $id, $recomendacionId,$modelo->cumplimiento, $modelo->comentario, $usuario->id))
                {
                    if($sentencia->execute())
                    {
                        $sentencia->close();
                        $resultado = $this->actualizarRecomendacion($recomendacionId);
                    }
                    else
                        $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
                else
                    $resultado->mensajeError = "Falló el enlace de parámetros";
            }
            else
                $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
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
    
    public function insertarArchivo($avanceId,$nombre, $tamano)
    {
        $resultado =  $this->calcularId("id","recomendaciones_avances_archivos");
        if($resultado->mensajeError=="")
        {
            $id = $resultado->valor;
            $consulta = "INSERT INTO recomendaciones_avances_archivos(id, avance_id, nombre, tamano, fecha) " .
                "VALUE(?, ?, ?, ?, NOW())";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if( $sentencia->bind_param("iisi", $id, $avanceId,$nombre, $tamano))
                {
                    if($sentencia->execute())
                    {
                        $sentencia->close();
                       
                    }
                    else
                        $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
                else
                    $resultado->mensajeError = "Falló el enlace de parámetros";
            }
            else
                $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
        }
        return $resultado;
    }
    
    public function consultarUltimoAvance($recomendacionId)
    {
        $resultado = new Resultado();
        $consulta =  "SELECT cumplimiento AS cumplimiento FROM recomendaciones_avances RA WHERE recomendacion_id = ? ORDER BY id DESC LIMIT 1";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("i", $recomendacionId))
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
                            $resultado->valor =  0;
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
    
    private function actualizarRecomendacion($recomendacionId)
    {
        $resultado = new Resultado();
        $resultado = $this->consultarUltimoAvance($recomendacionId);
        if($resultado->correcto())
        {
            $avance = $resultado->valor;
            if($avance==100)
                $resultado = $this->actualizarCumplimientoRecomendacion($recomendacionId,$avance,1, 1, "NOW()");
            else
                $resultado = $this->actualizarCumplimientoRecomendacion($recomendacionId,$avance,0, 0, "NULL");
        }
        return $resultado;
        
    }
    
    public function actualizarCumplimientoRecomendacion($recomendacionId, $cumplimiento, $terminada, $estatusValidacionId, $fechaFinalizacion)
    {
        $resultado = new Resultado();
        
        $consulta = " UPDATE recomendaciones
           SET cumplimiento = ? ,
                terminada = ?,
                estatus_validacion_id = ?,
                fecha_finalizacion = $fechaFinalizacion,
                fecha_modificacion = NOW()
            WHERE id = ?";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("iiii", $cumplimiento,$terminada,$estatusValidacionId,$recomendacionId))
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
    
    
    public function actualizarAvance($recomendacionId,$modelo,$usuario)
    {
        $this->conexion->autocommit(FALSE);
        $resultado = new Resultado();
        $consulta = " UPDATE recomendaciones_avances " .
            "SET cumplimiento = ?, " .
            "  comentario = ?, " .
            "  fecha_modificacion= NOW(), " .
            " usuario_id = ? " .
            "WHERE id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("isii", $modelo->cumplimiento, $modelo->comentario,$usuario->id,$modelo->id ))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                    $resultado = $this->actualizarRecomendacion($recomendacionId);
                    if($resultado->correcto())
                    {
                        if($modelo->archivosEliminados!="")
                             $resultado = $this->eliminarArchivosAvanceIN($modelo->archivosEliminados);
                    }
                    
                }
                else
                    $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else  $resultado->mensajeError = "Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
        if($resultado->correcto())
        {
            $this->conexion->commit();
            $resultado->valor=true;
        }
        else
            $this->conexion->rollback();
        return $resultado;
    }
    
    public function actualizarDatosRecomendacion($modelo,$usuario)
    {
        $resultado = new Resultado();
        $consulta = " UPDATE recomendaciones " .
            "SET fecha_modificacion= NOW(), " .
            " responsable_id = ? " .
            "WHERE id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("ii", $modelo->responsableId, $modelo->id))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                }
                else
                    $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else  $resultado->mensajeError = "Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
        return $resultado;
    }
    
    public function eliminarAvance($llaves)
    {
        $this->conexion->autocommit(FALSE);
        $resultado = new Resultado();
        $resultado = $this->eliminarArchivosAvance($llaves->id);
        if($resultado->correcto())
        {
            $consulta = "DELETE FROM recomendaciones_avances WHERE id = ?";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('i',$llaves->id))
                {
                    if($sentencia->execute())
                    {
                        $sentencia->close();
                        $resultado = $this->actualizarRecomendacion($llaves->recomendacionId);
                        if($resultado->correcto())
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
    
    public function eliminarArchivosAvance($avanceId)
    {
        $resultado = new Resultado();
        
        $consulta ="DELETE FROM recomendaciones_avances_archivos WHERE avance_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$avanceId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                    $dirpath = dirname(getcwd());
                    $carpeta = "archivos_avances/avance" . $avanceId ;
                    $path = $dirpath.'/'.$carpeta.'/';
                    if(file_exists($path))
                    {
                        $administradorArchivos = new AdministradorArchivos();
                        $administradorArchivos->eliminarDirectorio($path);
                        
                    }
                       
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
    
    public function eliminarArchivosAvanceIN($ids)
    {
        $resultado = new Resultado();
        
        $consulta ="DELETE FROM recomendaciones_avances_archivos WHERE id IN($ids)";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            //if($sentencia->bind_param("i",$avanceId))
            //{
                if($sentencia->execute())
                {
                    $sentencia->close();
                    //TODO Eliminar archivos fisicamente
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = __FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
                
            //}
           // else
           //     $resultado->mensajeError = __FUNCTION__ ." Falló el enlace de parámetros";
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = __FUNCTION__ ." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
        }
        return $resultado;
    }
    
    public function validarRecomendacion($usuario,$modelo)
    {
        ini_set('max_execution_time', 300);
        $this->conexion->autocommit(FALSE);
        
            $resultado = new Resultado();
            $consulta = "UPDATE recomendaciones
                     SET
                         estatus_validacion_id = ?,
                         comentarios_validacion = ?,
                         fecha_modificacion = NOW(),
                         fecha_validacion = NOW(),
                         validacion_usuario_id = ?
                     WHERE id = ?";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('isii',$modelo->estatusValidacionId, $modelo->comentariosValidacion,$usuario->id,$modelo->id))
                {
                    //var_dump($modelo);
                    if($sentencia->execute())
                    {
                        $sentencia->close();
                        
                        if($modelo->estatusValidacionId==\EstatusValidacion::VALIDADA)
                        {
                            $resultado = $this->enviarNotificacionEvidenciaValidada($modelo->id, $usuario, $modelo);
                        }
                        else if($modelo->estatusValidacionId==\EstatusValidacion::RECHAZADA)
                        {
                            $resultado = $this->enviarNotificacionEvidenciaRechazada($modelo->id, $usuario, $modelo);
                        }
//                         $estatusValidacionRepositorio = new EstatusValidacionRepositorio($this->conexion);
//                         $resultado = $estatusValidacionRepositorio->consultarPorLlaves((object)["id" => $modelo->estatusValidacionId]);
//                          if($resultado->correcto())
//                          {
//                             $estatusValidacion = $resultado->valor;
                            
//                             $comentariosRepositorio = new RecomendacionesComentariosRepositorio($this->conexion);
//                             $comentario= new RecomendacionComentario();
//                             $comentario->usuarioId = $usuario->id;
//                             $comentario->recomendacionId = $modelo->id;
//                             $comentario->comentario = $estatusValidacion->nombre . ". " . $modelo->comentariosValidacion;
                            
//                             $resultado = $comentariosRepositorio->insertar($usuario,$comentario);
//                             if($resultado->correcto())
//                             {
//                                 $resultado->valor=$modelo->id;
//                             }
                            
                            
                           
                            
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
            {
                $resultado->valor = $modelo->id;
                $this->conexion->commit();
            }
            else
                $this->conexion->rollback();
            return $resultado;
    }
    
    public function consultarAdministradoresConEvidencias()
    {
        $resultado = new Resultado();
        $usuarios = array();
        
        $consulta = "SELECT E.administrador_sivah_id id, ADM.nombre, ADM.apellido, ADM.nombre_usuario
                    FROM recomendaciones R 
                    	INNER JOIN usuarios RP ON RP.id = R.responsable_id 
                        INNER JOIN empresas E ON E.id = RP.empresa_id
                        INNER JOIN sedes S ON S.id = RP.sede_id
                        INNER JOIN usuarios ADM ON E.administrador_sivah_id  = ADM.id
                    WHERE estatus_validacion_id = 1
                    GROUP BY E.administrador_sivah_id";
        
        
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
                
        $resultado->valor = $usuarios;
        return $resultado;
    }
    
    public function consultarEvidenciasPendientesSede($administrador)
    {
        $resultado = new Resultado();
        $sedes = array();
        
        $consulta = "SELECT RP.empresa_id empresaId, E.nombre empresaNombre, A.sede_id sedeId, S.nombre sedeNombre, count(*) pendientesValidacion
                    FROM recomendaciones R 
                    	INNER JOIN usuarios RP ON RP.id = R.responsable_id 
                    	INNER JOIN empresas E ON E.id = RP.empresa_id
                    	INNER JOIN auditorias A ON A.id = R.auditoria_id
                        INNER JOIN sedes S ON S.id = A.sede_id
                WHERE estatus_validacion_id = 1
                	AND E.administrador_sivah_id = ? AND A.seguimiento_finalizado!=1
                GROUP BY RP.empresa_id, E.nombre, A.sede_id, S.nombre  ";
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$administrador->id))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($empresaId, $empresaNombre, $sedeId, $sedeNombre, $pendientes))
                    {
                        while($sentencia->fetch())
                        {
                            $sede= (object) [
                                'empresaId' =>  $empresaId,
                                'empresaNombre' => $empresaNombre,
                                'sedeId' => $sedeId,
                                'sedeNombre' => $sedeNombre,
                                'pendientes' => $pendientes
                            ];
                            array_push($sedes,$sede);
                        }
                        
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__." Falló el enlace del resultado";
                }
                else
                    $resultado->mensajeError = __FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
        }
        else
            $resultado->mensajeError = __FUNCTION__." Falló el enlace de parámetros";
            
        $resultado->valor = $sedes;
        return $resultado;
    }
    
//     public function consultarUsuariosSIVAH()
//     {
//         $resultado = new Resultado();
//         $usuarios = array();
        
//         $consulta = "SELECT id, U.nombre, U.apellido, U.nombre_usuario, tipo_usuario_id, empresa_id, sede_id
//                 FROM usuarios U
//                 WHERE U.permiso_sivah = 1 AND estatus = 1 
//                 ORDER BY nombre, apellido ";
        
        
//         if($sentencia = $this->conexion->prepare($consulta))
//         {
//             //if($sentencia->bind_param("i",$administrador->id))
//             //{
//                 if($sentencia->execute())
//                 {
//                     if ($sentencia->bind_result($id, $nombre, $apellido, $nombreUsuario, $tipoUsuarioId, $empresaId, $sedeId))
//                     {
//                         while($sentencia->fetch())
//                         {
//                             $usuario= (object) [
//                                  'id' => $id,
//                                 'nombre' =>  $nombre,
//                                 'apellido' => $apellido,
//                                 'nombreUsuario' => $nombreUsuario,
//                                 'tipoUsuarioId' => $tipoUsuarioId,
//                                 'empresaId' => $empresaId,
//                                 'sedeId' => $sedeId
//                             ];
//                             array_push($usuarios,$usuario);
//                         }
//                     }
//                     else
//                         $resultado->mensajeError = __FUNCTION__." Falló el enlace del resultado";
//                 }
//                 else
//                     $resultado->mensajeError = __FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
//         }
//         else
//             $resultado->mensajeError = __FUNCTION__." Falló la preparacion";
            
//         $resultado->valor = $usuarios;
//         return $resultado;
//     }
    
    public function consultarUsuariosAuditoria($auditoriaId)
    {
        $resultado = new Resultado();
        $usuarios = array();
        
        $consulta = "SELECT U.id, U.nombre, U.apellido, U.nombre_usuario, U.tipo_usuario_id
                    FROM recomendaciones R
                        INNER JOIN usuarios U ON R.responsable_id = U.id
                WHERE U.permiso_sivah = 1 AND U.estatus = 1 AND R.auditoria_id = ?
                GROUP BY U.id, nombre, apellido,U.nombre_usuario, U.tipo_usuario_id 
                ORDER BY nombre, apellido";
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$auditoriaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombre, $apellido, $nombreUsuario, $tipoUsuarioId))
                    {
                        while($sentencia->fetch())
                        {
                            $usuario= (object) [
                                'id' => $id,
                                'nombre' =>  $nombre,
                                'apellido' => $apellido,
                                'nombreUsuario' => $nombreUsuario,
                                'tipoUsuarioId' => $tipoUsuarioId
                            ];
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
                $resultado->mensajeError = __FUNCTION__." Falló la preparacion de parametros";
        }
        else
            $resultado->mensajeError = __FUNCTION__." Falló la preparacion";
            
            $resultado->valor = $usuarios;
            return $resultado;
    }
    
    public function consultarNumeroEvidencias($usuario, $auditoriaId="")
    {
        $resultado = new Resultado();
        
        $filtroAuditoria = "";
        if($auditoriaId!="")
            $filtroAuditoria = "AND R.auditoria_id = $auditoriaId";
        
        $consulta = "SELECT (SELECT count(*)
                            FROM recomendaciones R
                            	INNER JOIN usuarios RP ON RP.id = R.responsable_id
                                INNER JOIN empresas E ON E.id = RP.empresa_id
                                INNER JOIN sedes S ON S.id = RP.sede_id
                                INNER JOIN auditorias A ON A.id = R.auditoria_id
                            WHERE responsable_id = ? $filtroAuditoria),
                         (SELECT count(*)
                            FROM recomendaciones R
                            	INNER JOIN usuarios RP ON RP.id = R.responsable_id
                                INNER JOIN empresas E ON E.id = RP.empresa_id
                                INNER JOIN sedes S ON S.id = RP.sede_id
                                INNER JOIN auditorias A ON A.id = R.auditoria_id
                            WHERE responsable_id = ? AND R.estatus_validacion_id = 1 $filtroAuditoria), 
                        (SELECT count(*)
                            FROM recomendaciones R
                            	INNER JOIN usuarios RP ON RP.id = R.responsable_id
                                INNER JOIN empresas E ON E.id = RP.empresa_id
                                INNER JOIN sedes S ON S.id = RP.sede_id
                                INNER JOIN auditorias A ON A.id = R.auditoria_id
                            WHERE responsable_id = ? AND R.estatus_validacion_id = 2 $filtroAuditoria), 
                        (SELECT count(*)
                            FROM recomendaciones R
                            	INNER JOIN usuarios RP ON RP.id = R.responsable_id
                                INNER JOIN empresas E ON E.id = RP.empresa_id
                                INNER JOIN sedes S ON S.id = RP.sede_id
                                INNER JOIN auditorias A ON A.id = R.auditoria_id
                            WHERE responsable_id = ? AND R.estatus_validacion_id = 3 $filtroAuditoria)";
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("iiii",$usuario->id,$usuario->id,$usuario->id,$usuario->id))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($total, $enviadas, $validadas, $rechazadas))
                    {
                        if($sentencia->fetch())
                        {
                            $totales= (object) [
                                'total' => $total,
                                'enviadas' => $enviadas,
                                'validadas' =>  $validadas,
                                'rechazadas' => $rechazadas
                              
                            ];
                            $resultado->valor = $totales;
                        }
                        
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__." Falló el enlace del resultado";
                }
                else
                    $resultado->mensajeError = __FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
        }
        else
            $resultado->mensajeError = __FUNCTION__." Falló el enlace de parámetros";
            
       
        return $resultado;
    }
    
    
    
    public function consultarFechaLimite($usuario, $auditoriaId)
    {
        $resultado = new Resultado();
        
        $consulta = "SELECT  IFNULL(DATE_FORMAT(fecha_vencimiento,'%d/%m/%Y'),'')fecha_vencimiento 
                FROM recomendaciones
                WHERE auditoria_id = ?
                ORDER BY UNIX_TIMESTAMP(fecha_vencimiento) DESC
                LIMIT 1";
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$auditoriaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($fecha))
                    {
                        if($sentencia->fetch())
                        {
                            $resultado->valor = $fecha;
                        }
                        
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__." Falló el enlace del resultado";
                }
                else
                    $resultado->mensajeError = __FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
        }
        else
            $resultado->mensajeError = __FUNCTION__." Falló el enlace de parámetros";
            
            
            return $resultado;
    }
    
    public function consultarAuditoriasUsuario($usuario)
    {
        $resultado = new Resultado();
        $sedes = array();
        
        $filtros = $this->getFiltros($usuario,(object)[]);
        $and = $this->and($filtros);
        
        $consulta = "SELECT R.auditoria_id, A.empresa_id empresaId, E1.nombre empresaNombre, A.sede_id sedeId, S.nombre sedeNombre, IFNULL(DATE_FORMAT(A.fecha,'%d/%m/%Y'),'') fecha, DATEDIFF(NOW(),A.fecha) dias
            FROM recomendaciones R
            	INNER JOIN usuarios U ON U.id = R.responsable_id
            	INNER JOIN auditorias A ON A.id = R.auditoria_id
            	INNER JOIN empresas E1 ON E1.id = A.empresa_id
            	INNER JOIN sedes S ON S.id = A.sede_id
            WHERE A.seguimiento_finalizado != 1
            $and
            GROUP BY R.auditoria_id, A.empresa_id, E1.nombre, A.sede_id, S.nombre
            ORDER BY R.auditoria_id";
        
         //echo $consulta; 
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id,$empresaId, $empresaNombre, $sedeId, $sedeNombre, $fecha, $dias))
                    {
                        while($sentencia->fetch())
                        {
                            $sede= (object) [
                                'id' => $id,
                                'fecha' => $fecha,
                                'empresaId' =>  $empresaId,
                                'empresaNombre' => $empresaNombre,
                                'sedeId' => $sedeId,
                                'sedeNombre' => $sedeNombre,
                                'dias' => $dias
                            ];
                            array_push($sedes,$sede);
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
            $resultado->mensajeError = __FUNCTION__." Falló la preparacion";
            
            $resultado->valor = $sedes;
        return $resultado;
    }
    
    public function consultarMiembrosEquipo($usuario)
    {
        $resultado = new Resultado();
        $miembros = array();
        
        $filtros = $this->getFiltros($usuario,(object)[]);
        $where = $this->where($filtros);
        
        $consulta = "SELECT U.id, U.nombre_usuario, U.nombre, U.apellido
            FROM usuarios U
            	INNER JOIN empresas E1 ON E1.id = U.empresa_id
            	INNER JOIN sedes S ON S.id = U.sede_id
            $where
           ORDER BY nombre, apellido";
            
            
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($this->bind_param($sentencia, $filtros))
                {
                    if($sentencia->execute())
                    {
                        if ($sentencia->bind_result($id,$nombreUsuario, $nombre, $apellido))
                        {
                            while($sentencia->fetch())
                            {
                                $miembro= (object) [
                                    'id' => $id,
                                    'nombreUsuario' => $nombreUsuario,
                                    'nombre' =>  $nombre,
                                    'apellido' => $apellido
                                ];
                                $miembro->nombreCompleto = $miembro->nombre . " " . $miembro->apellido;
                                array_push($miembros,$miembro);
                            }
                            
                        }
                        else
                            $resultado->mensajeError = __FUNCTION__." Falló el enlace del resultado";
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
            }
            else
                $resultado->mensajeError = __FUNCTION__." Falló el enlace de parámetros";
                
        $resultado->valor = $miembros;
        return $resultado;
    }
    
    function eliminarAcentos($cadena){
        
        //Reemplazamos la A y a
        $cadena = str_replace(
            array('Á', 'À', 'Â', 'Ä', 'á', 'à', 'ä', 'â', 'ª'),
            array('A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a'),
            $cadena
            );
        
        //Reemplazamos la E y e
        $cadena = str_replace(
            array('É', 'È', 'Ê', 'Ë', 'é', 'è', 'ë', 'ê'),
            array('E', 'E', 'E', 'E', 'e', 'e', 'e', 'e'),
            $cadena );
        
        //Reemplazamos la I y i
        $cadena = str_replace(
            array('Í', 'Ì', 'Ï', 'Î', 'í', 'ì', 'ï', 'î'),
            array('I', 'I', 'I', 'I', 'i', 'i', 'i', 'i'),
            $cadena );
        
        //Reemplazamos la O y o
        $cadena = str_replace(
            array('Ó', 'Ò', 'Ö', 'Ô', 'ó', 'ò', 'ö', 'ô'),
            array('O', 'O', 'O', 'O', 'o', 'o', 'o', 'o'),
            $cadena );
        
        //Reemplazamos la U y u
        $cadena = str_replace(
            array('Ú', 'Ù', 'Û', 'Ü', 'ú', 'ù', 'ü', 'û'),
            array('U', 'U', 'U', 'U', 'u', 'u', 'u', 'u'),
            $cadena );
        
        //Reemplazamos la N, n, C y c
        $cadena = str_replace(
            array('Ñ', 'ñ', 'Ç', 'ç'),
            array('N', 'n', 'C', 'c'),
            $cadena
            );
        
        return $cadena;
    }
    
    public function insertarArchivosAvance($recomendacionId, $avanceId, $archivos)
    {
        $this->conexion->autocommit(FALSE);
        $resultado = new Resultado();
        if(!empty($archivos))
        {
            //foreach  ($archivos as $archivo) 
            foreach  ($archivos['name'] as $key => $name) 
            {
                $carpeta = "archivos_avances/avance" . $avanceId ;
                if(file_exists("../".$carpeta."/") || @mkdir("../".$carpeta."/"))
                {
                    $dirpath = dirname(getcwd());
                    $path = $dirpath.'/'.$carpeta.'/'.$name;
                    move_uploaded_file($archivos['tmp_name'][$key],$path);
                    
                    if(file_exists($path))
                    {
                        $resultado = $this->insertarArchivo($avanceId, $name,$archivos['size'][$key]);
                        if($resultado->error())
                            break;
                    }
                    else
                    {
                        $resultado->mensajeError = "Error al subir archivo: " . $name;
                        break;
                    }
                }
            }
        }
//         if($resultado->correcto())
//             $resultado = $this->actualizarRecomendacion($recomendacionId);
        
        if($resultado->correcto())
        {
            $this->conexion->commit();
        }
        else
            $this->conexion->rollback();
        return $resultado;
    }
    
    function consultarNumeroRecomendacionesPorMes($mes,$ano, $auditoriaId, $puntuacion)
    {
        $resultado = new Resultado();
        $ultimoDiaMes = $this->ultimoDiaMes($mes, $ano);
        $consulta = "SELECT (SELECT count(*)
                    FROM recomendaciones
                    WHERE auditoria_id = ?) total,
                     (SELECT count(*)
                    FROM recomendaciones
                    WHERE auditoria_id = ? AND estatus_validacion_id = 2 AND fecha_finalizacion <= ? ) validadas,
                    (SELECT count(*)
                    FROM recomendaciones
                    WHERE auditoria_id = ? AND estatus_validacion_id = 1 AND fecha_finalizacion <= ?) enviadas";
                            
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('iisis',$auditoriaId, $auditoriaId, $ultimoDiaMes, $auditoriaId,$ultimoDiaMes))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($total, $validadas, $enviadas))
                    {
                        if($sentencia->fetch())
                        {
                            $registro= (object) [
                                'total' =>  $total,
                                'validadas' =>  $validadas,
                                'enviadas' =>  $enviadas
                            ];
                            
                            
                            $registro->validadasEnviadas = $registro->validadas + $registro->enviadas;
                            
                            $registro->mes = $mes;
                            $registro->ano = $ano;
                            
                            $registro->nombreMes = $this->getNombreMes($mes). " $ano";
                            
                            $validadas = $registro->validadas;
                            $validadasEnviadas = $registro->validadasEnviadas;
                            
                            $registro->porcetajeFaltante = 100 - floatval($puntuacion);
                            
                            if($registro->total!=0)
                                $registro->valorHallagzo =  floatval($registro->porcetajeFaltante)/  floatval($registro->total);
                            else
                                $registro->valorHallagzo = 0;
                                
                            $registro->avanceValidadas =  $registro->valorHallagzo * $validadas;
                            $registro->avanceValidadasEnviadas =  $registro->valorHallagzo * $validadasEnviadas;
                            
                            $registro->porcentajeValidadas = $puntuacion +  $registro->avanceValidadas;
                            $registro->porcentajeValidadasEnviadas = $puntuacion +  $registro->avanceValidadasEnviadas;
                            
                            Porcentaje::formatearPorcentaje($registro, "porcentajeValidadas");
                            Porcentaje::formatearPorcentaje($registro, "porcentajeValidadasEnviadas");
                            
                            $resultado->valor = $registro;
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
    
    function ultimoDiaMes($mes, $ano) {
        //$month = date('m');
        //$year = date('Y');
        $day = date("d", mktime(0,0,0, $mes+1, 0, $ano));
        
        return date('Y-m-d', mktime(0,0,0, $mes, $day, $ano));
    }
    
    function getNombreMes($mes)
    {
        $nombre="";
        switch($mes)
        {
            case 1:
                $nombre = "Enero";
                break;
            case 2:
                $nombre = "Febrero";
                break;
            case 3:
                $nombre = "Marzo";
                break;
            case 4:
                $nombre = "Abril";
                break;
            case 5:
                $nombre = "Mayo";
                break;
            case 6:
                $nombre = "Junio";
                break;
            case 7:
                $nombre = "Julio";
                break;
            case 8:
                $nombre = "Agosto";
                break;
            case 9:
                $nombre = "Septiembre";
                break;
            case 10:
                $nombre = "Octubre";
                break;
            case 11:
                $nombre = "Noviembre";
                break;
            case 12:
                $nombre = "Diciembre";
                break;
        }
        return $nombre;
    }
    
    function consultarHallazgosDepartamento($auditoriaId)
    {
        $resultado = new Resultado();
        $registros = array();
        
        
        $consulta = "SELECT D.id, CASE WHEN D.nombre IS NULL THEN 'Sin asignar' ELSE D.nombre END nombre, count(*) hallazgos,
                    (SELECT count(*) 
                    FROM recomendaciones R1
                    	INNER JOIN usuarios U1 ON U1.id = R1.responsable_id
                        INNER JOIN departamentos D1 ON U1.departamento_id = D1.id
                        WHERE R1.auditoria_id = R.auditoria_id AND D1.id = D.id AND R1.estatus_validacion_id=2) validados,
                        (SELECT count(*) 
                    FROM recomendaciones R1
                    	INNER JOIN usuarios U1 ON U1.id = R1.responsable_id
                        INNER JOIN departamentos D1 ON U1.departamento_id = D1.id
                        WHERE R1.auditoria_id = R.auditoria_id AND D1.id = D.id AND R1.estatus_validacion_id=1) procesoValidacion
                    FROM recomendaciones R
                    	INNER JOIN usuarios U ON U.id = R.responsable_id
                        INNER JOIN departamentos D ON U.departamento_id = D.id
                    WHERE auditoria_id = ?
                    GROUP BY D.id, D.nombre
                    ORDER BY nombre";
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('i',$auditoriaId))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre,$total, $validadas, $proceso))
                    {
                        while($sentencia->fetch())
                        {
                            $registro= (object) [
                                'id' =>  $id,
                                'nombre' =>  $nombre,
                                'total' =>  $total,
                                'validadas' =>  $validadas,
                                'proceso' =>  $proceso
                                
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
    
    function consultarHallazgosUsuarios($auditoriaId)
    {
        $resultado = new Resultado();
        $registros = array();
        
        
        $consulta = "SELECT U.id, U.nombre, U.apellido, count(*) hallazgos,
                    (SELECT count(*)
                    FROM recomendaciones R1
                        WHERE R1.auditoria_id = R.auditoria_id AND R1.responsable_id = U.id AND R1.estatus_validacion_id=2) validados,
                        (SELECT count(*)
                    FROM recomendaciones R1
                        WHERE R1.auditoria_id = R.auditoria_id AND R1.responsable_id = U.id AND R1.estatus_validacion_id=1) procesoValidacion
                    FROM recomendaciones R
                    	INNER JOIN usuarios U ON U.id = R.responsable_id
                    WHERE auditoria_id = ?
                    GROUP BY U.id, U.nombre, U.apellido
                    ORDER BY U.nombre";
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('i',$auditoriaId))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre, $apellido, $total, $validadas, $proceso))
                    {
                        while($sentencia->fetch())
                        {
                            $registro= (object) [
                                'id' =>  $id,
                                'nombre' =>  $nombre,
                                'apellido' => $apellido,
                                'total' =>  $total,
                                'validadas' =>  $validadas,
                                'proceso' =>  $proceso
                                
                                
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
    
    public function consultarAnos($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $filtros = array();
        //$filtros = $this->getFiltrosN($usuario,$criteriosSeleccion,false);
        //$where = $this->where($filtros);
        
        $consulta = "SELECT YEAR(E.fecha_alta) ano
                    FROM recomendaciones E
                       ";
        
        //$consulta .= $where;
        
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
    
    
}

