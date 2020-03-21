<?php
namespace php\repositorios;

use php\interfaces\IAuditoriasRepositorio;
use php\modelos\Auditoria;
use php\modelos\Resultado;
use php\clases\AdministradorConexion;
use php\clases\AdministradorArchivos;

include "../interfaces/IAuditoriasRepositorio.php";
include "../modelos/Auditoria.php";
include "RepositorioBase.php";
require_once("../clases/Resultado.php");
require_once('../clases/AdministradorArchivos.php');
require_once('../clases/AdministradorConexion.php');

class AuditoriasRepositorio extends RepositorioBase implements IAuditoriasRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = " SELECT A.id, A.plantilla_id, P.nombre, IFNULL(DATE_FORMAT(A.fecha_ejecucion,'%d/%m/%Y %H:%i:%s'),'')fecha_ejecucion, A.empresa_id, E.nombre, IFNULL(E.nombre_corto,'')nombre_corto, A.contador_empresa,tipo_auditoria_id " .
            " FROM auditorias A " .
            " INNER JOIN plantillas P on A.plantilla_id = P.id  " .
            " LEFT JOIN empresas E on A.empresa_id = E.id ";
           
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
    
    public function insertar(Auditoria $modelo)
    {
        $resultado = new Resultado();
        $this->conexion->autocommit(FALSE);
        if($modelo->id=="")
        {
            if($modelo->empresaId=="")
                $modelo->empresaId = NULL;
            
            $resultado =  $this->calcularId("id","auditorias");
            if($resultado->mensajeError=="")
            {
                $modelo->id = $resultado->valor;
               
                $modelo->contadorEmpresa = $this->calcularContadorEmpresa($modelo->empresaId);
                
                
                $consulta = "INSERT INTO auditorias(id, plantilla_id, fecha_ejecucion, empresa_id, contador_empresa) " .
                    "VALUE(?, ?, NOW(),  ?, ?)";
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if( $sentencia->bind_param("iiii", $modelo->id, $modelo->plantillaId, $modelo->empresaId, $modelo->contadorEmpresa ))
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
                                        $this->conexion->commit();
                                    }
                                   
                                }
                                else
                                    $this->conexion->rollback();
                               
                            }
    
                        }
                        else
                        {
                            $resultado->mensajeError = "Falló la ejecución insertar(" . $this->conexion->errno . ") " . $this->conexion->error;
                            $this->conexion->rollback();
                        }
                    }
                    else
                    {
                        $resultado->mensajeError = "Falló el enlace de parámetros";
                        $this->conexion->rollback();
                    }
                }
                else
                {
                    $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
                    $this->conexion->rollback();
                }
        
            }
        }

        return $resultado;
    }
    
//     private function consultarReferencia($auditoriaId)
//     {
//         $consulta = "SELECT E.id,   IFNULL(E.nombre_corto,'')nombre_corto, A.contador_empresa,IFNULL(DATE_FORMAT(A.fecha_ejecucion,'%d/%m/%Y %H:%i:%s'),'')fecha_ejecucion " .
//             " FROM auditorias A " .
//             " INNER JOIN plantillas P on A.plantilla_id = P.id  " .
//             " INNER JOIN empresas E on A.empresa_id = E.id " .
//             "WHERE A.id = ?";
//         if($sentencia = $this->conexion->prepare($consulta))
//         {
//             if($sentencia->bind_param("i",$auditoriaId))
//             {
//                 if($sentencia->execute())
//                 {
//                     if ($sentencia->bind_result($empresaId,$empresaNombreCorto, $contadorEmpresa, $fechaEjecucion))
//                     {
//                         if($sentencia->fetch())
//                         {
//                             $fecha = substr($fechaEjecucion,0,10);
//                             $fecha = str_replace( '/', '.', $fecha );
                            
//                             if($empresaNombreCorto=="" || $empresaNombreCorto==null)
//                                 $empresaNombreCorto = "EMP".$empresaId;
                            
//                             $referencia = $empresaNombreCorto . "-".$fecha."-".$contadorEmpresa;
//                             return $referencia;
//                         }
//                     }
//                 }
//             }
//         }
//         return "";
                            
//     }
    
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
    
    private function eliminarPreguntas($plantillaId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM preguntas WHERE plantilla_id = ?";
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
                    $resultado->mensajeError = "Falló la ejecución eliminarPreguntas(" . $this->conexion->errno . ") " . $this->conexion->error;
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
    
    private function eliminarSecciones($plantillaId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM secciones WHERE plantilla_id = ?";
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
                    $resultado->mensajeError = "Falló la ejecución eliminarSecciones(" . $this->conexion->errno . ") " . $this->conexion->error;
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
    
//     private function insertarSecciones($plantillaId,$secciones)
//     {
        
//         $resultado = new Resultado();
      
        
//         for ($i = 0; $i <  count($secciones); $i++)
//         {
//             $seccion= $secciones[$i];
          
//             $consulta = "INSERT INTO secciones(plantilla_id, id, texto ) " .
//                 "VALUE(?, ?, ?)";
//             if($sentencia = $this->conexion->prepare($consulta))
//             {
              
//                 if($sentencia->bind_param("iis",$plantillaId,$seccion->id, $seccion->texto))
//                 {
//                     if($sentencia->execute())
//                     {
//                         $resultado->valor =$seccion->id;
//                         $sentencia->close();
                        
//                     }
//                     else
//                     {
//                         $resultado->codigoError = $this->conexion->errno;
//                         $resultado->mensajeError = "Falló la ejecución insertarSecciones(" . $this->conexion->errno . ") " . $this->conexion->error;
//                         break;
//                     }
                    
//                 }
//                 else
//                 {
//                     $resultado->mensajeError = "Falló el enlace de parámetros";
//                     break;
//                 }
//             }
//             else
//             {
//                 $resultado->codigoError = $this->conexion->errno;
//                 $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
//                 break;
//             }
//         }
        
        
//         return $resultado;
//     }
    
    private function insertarPreguntas($auditoriaId,$plantillaId,$seccionId,$preguntas)
    {
        $resultado = new Resultado();
        
        //$administradorArchivos = new AdministradorArchivos();
       
        for ($i = 0; $i <  count($preguntas); $i++)
        {
            
            $pregunta = $preguntas[$i];
            
            $consulta = "UPDATE auditoria_preguntas " .
                        "SET valor = ?,  puntos = ?, puntos_total = ?, porcentaje = ? ". 
                        "WHERE auditoria_id=? AND plantilla_id = ? AND seccion_id = ? AND pregunta_id = ?";
            
//             if(substr( $pregunta->valor, 0, 10 ) === "data:image")
//             {
//                 $carpeta = "fotos_auditorias";
//                 $nombreArchivo = "foto_".$auditoriaId."_".$pregunta->id.".jpg";
//                 $administradorArchivos->crearBase64($pregunta->valor,$carpeta,$nombreArchivo);
//             }
            
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param("siisiiii",$pregunta->valor,$pregunta->puntos, $pregunta->puntosTotal, $pregunta->porcentaje,$auditoriaId,$plantillaId,$seccionId, $pregunta->id))
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
                                $consulta = "INSERT INTO auditoria_preguntas(auditoria_id, plantilla_id, seccion_id, pregunta_id, valor, puntos, puntos_total, porcentaje) " .
                                    "VALUE(?, ?, ?, ?, ?, ?, ?, ?)";
                                if($sentencia = $this->conexion->prepare($consulta))
                                {
                                    if($sentencia->bind_param("iiiisiis",$auditoriaId,$plantillaId,$seccionId, $pregunta->id, $pregunta->valor,$pregunta->puntos, $pregunta->puntosTotal, $pregunta->porcentaje))
                                    {
                                        if($sentencia->execute())
                                        {
                                            $sentencia->close();
                                        }
                                        else
                                        {
                                            $resultado->codigoError = $this->conexion->errno;
                                            $resultado->mensajeError = "Falló la ejecución insertarPreguntas(" . $this->conexion->errno . ") " . $this->conexion->error;
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
                    else
                    {
                        $resultado->codigoError = $this->conexion->errno;
                        $resultado->mensajeError = "Falló la ejecución update insertarPreguntas(" . $this->conexion->errno . ") " . $this->conexion->error;
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
    
//     private function insertarRespuestasNo($plantillaId,$secciones)
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
                    
//                     $consulta = "INSERT INTO respuestas_no(plantilla_id, seccion_id, pregunta_id, id, texto, peso, hallazgo, recomendacion) " .
//                         "VALUE(?, ?, ?, ?, ?, ?, ?, ?)";
//                     if($sentencia = $this->conexion->prepare($consulta))
//                     {
//                         if($sentencia->bind_param("iiiisiss",$plantillaId,$seccion->id, $pregunta->id,$respuesta->id, $respuesta->texto,$respuesta->peso,$respuesta->hallazgo,$respuesta->recomendacion))
//                         {
//                             if($sentencia->execute())
//                             {
//                                 $sentencia->close();
//                             }
//                             else
//                             {
//                                 $resultado->codigoError = $this->conexion->errno;
//                                 $resultado->mensajeError = "Falló la ejecución insertarRespuestasNo(" . $this->conexion->errno . ") " . $this->conexion->error;
//                                 break;
//                             }
                            
//                         }
//                         else
//                         {
//                             $resultado->mensajeError = "Falló el enlace de parámetros";
//                             break;
//                         }
//                     }
//                     else
//                     {
//                         $resultado->codigoError = $this->conexion->errno;
//                         $resultado->mensajeError = "Falló la preparación: insertarRespuestasNo(" . $this->conexion->errno . ") " . $this->conexion->error;
//                         break;
//                     }
//                 }
//             }
            
//         }
//         return $resultado;
//     }
    
//     private function insertarRespuestasSiCategorias($plantillaId,$secciones)
//     {
//         $resultado = new Resultado();
        
//         for ($i = 0; $i <  count($secciones); $i++)
//         {
//             $seccion = $secciones[$i];
//             for ($j = 0; $j < count($seccion->preguntas); $j++)
//             {
//                 $pregunta = $seccion->preguntas[$j];
//                 for ($k = 0; $k< count($pregunta->respuestas_si); $k++)
//                 {
//                     $respuesta = $pregunta->respuestas_si[$k];
//                     for ($l = 0; $l< count($respuesta->categorias); $l++)
//                     {
//                         $categoria = $respuesta->categorias[$l];
                        
//                         $consulta = "INSERT INTO respuestas_si_categorias(plantilla_id, seccion_id, pregunta_id, respuesta_si_id, id, categoria_id) " .
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
//                                     $resultado->mensajeError = "Falló la ejecución insertarRespuestasSiCategorias(" . $this->conexion->errno . ") " . $this->conexion->error;
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
//                             $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
//                             break;
//                         }
//                     }
//                 }
//             }
//         }
//         return $resultado;
//     }
    
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
        
        $consulta = " UPDATE auditorias 
            SET empresa_id = ?, 
                   tipo_auditoria_id = ?,   
              fecha_ejecucion = NOW() 
            WHERE id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("isi", $modelo->empresaId,$modelo->tipoAuditoriaId,$modelo->id))
            {
                if($sentencia->execute())
                {
                    $resultado->valor=true;
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
                                $this->conexion->commit();
                            }
                            
                        }
                        else
                            $this->conexion->rollback();
                            
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
        
        return $resultado;
    }
    
    public function insertarDatosAuditoria($modelo)
    {
        ini_set('max_execution_time', 300);
        $resultado =  $this->eliminarRespuestasSi($modelo->id,$modelo->plantillaId, $modelo->seccionId);
        if($resultado->mensajeError=="")
        {
            $resultado =  $this->eliminarRespuestasNo($modelo->id,$modelo->plantillaId, $modelo->seccionId);
            if($resultado->mensajeError=="")
            {
                $resultado =  $this->insertarPreguntas($modelo->id,$modelo->plantillaId, $modelo->seccionId,$modelo->preguntas);
                if($resultado->mensajeError=="")
                {
                    $resultado =  $this->insertarRespuestasSi($modelo->id,$modelo->plantillaId, $modelo->seccionId,$modelo->preguntas);
                    if($resultado->mensajeError=="")
                    {
                        $resultado =  $this->insertarRespuestasNo($modelo->id,$modelo->plantillaId, $modelo->seccionId,$modelo->preguntas);
                        if($resultado->mensajeError=="")
                        {
                            
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
        $where . " order by fecha_ejecucion desc";
        
      
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id,  $plantillaId, $plantillaNombre, $fechaEjecucion, $empresaId, $empresaNombre, $empresaNombreCorto, $contadorEmpresa,$tipoAuditoriaId))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id,  $plantillaId, $plantillaNombre, $fechaEjecucion, $empresaId, $empresaNombre, $empresaNombreCorto, $contadorEmpresa,$tipoAuditoriaId);
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
                    if ($sentencia->bind_result($id,  $plantillaId, $plantillaNombre, $fechaEjecucion, $empresaId, $empresaNombre, $empresaNombreCorto, $contadorEmpresa,$tipoAuditoriaId))
                    {
                        if($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id,  $plantillaId, $plantillaNombre, $fechaEjecucion, $empresaId, $empresaNombre, $empresaNombreCorto, $contadorEmpresa,$tipoAuditoriaId);
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
        if($resultado->mensajeError=="")
        {
            $resultadoPreguntas = $this->consultarPreguntas($llaves->plantillaId, $llaves->auditoriaId, $llaves->seccionId);
            if($resultado->mensajeError=="")
            {
                $resultado->valor->preguntas = $resultadoPreguntas->valor;
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
        $consulta = "SELECT id, RTRIM(texto) texto " .
            "FROM secciones " .
            " WHERE plantilla_id  = ? ".
            "ORDER BY id";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$llaves->plantillaId))
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
                    if ($sentencia->bind_result($id,  $plantillaId, $plantillaNombre, $fechaEjecucion, $empresaId, $empresaNombre, $empresaNombreCorto, $contadorEmpresa,$tipoAuditoriaId))
                    {
                        if($sentencia->fetch())
                        {
                            $plantilla = $this->crearRegistro($id,  $plantillaId, $plantillaNombre, $fechaEjecucion, $empresaId, $empresaNombre, $empresaNombreCorto, $contadorEmpresa,$tipoAuditoriaId);
                           
                            
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
    
   
 
    private function consultarPreguntas($plantillaId,$auditoriaId,$seccionId)
    {
        
        $resultado = new Resultado();
        $preguntas = array();
        $consulta = "SELECT A.seccion_id, pregunta_id, RTRIM(texto) texto, P.peso, RTRIM(tipo) tipo, RTRIM(hallazgo) hallazgo, RTRIM(recomendacion)recomendacion, RTRIM(practicas)practicas, RTRIM(observaciones)observaciones, RTRIM(valor) valor  " .
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
    
//     private function consultarRespuestasSi($plantillaId,$seccionId,$preguntaId)
//     {
       
//         $resultado = new Resultado();
//         $respuestas = array();
//         $consulta = "SELECT id, RTRIM(texto) texto, IFNULL(peso,0) peso, RTRIM(IFNULL(hallazgo,''))hallazgo, RTRIM(IFNULL(recomendacion,''))recomendacion " .
//             "FROM respuestas_si " .
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
//                             $resultadoRespuestas = $this->consultarRespuestasSiCategorias($plantillaId,$seccionId,$preguntaId,$respuesta->id);
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
//             $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
//       return $resultado;
//     }
    
    private function consultarRespuestasSi($auditoriaId,$plantillaId,$seccionId,$preguntaId)
    {
        
        $resultado = new Resultado();
        $respuestas = array();
        $consulta = "SELECT respuesta_id, valor, responsable_id, reporte, notificacion " .
            "FROM auditoria_respuestas_si " .
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
    
    private function crearRegistro($id,  $plantillaId, $plantillaNombre, $fechaEjecucion, $empresaId, $empresaNombre, $empresaNombreCorto, $contadorEmpresa, $tipoAuditoriaId)
    {
//         $archivoIcono = '../../php/iconos/icono'.$plantillaId.'.png';
//         $icono = 'default.png';
//         if(file_exists($archivoIcono))
//             $icono = 'icono'.$plantillaId.'.png';
        $archivoIcono = '../../php/iconos_plantillas/plantilla'.$id.'.png';
        $icono = 'default.png';
        if(file_exists($archivoIcono))
            $icono = 'plantilla'.$id.'.png';
        
        $fecha = substr($fechaEjecucion,0,10);
        $fecha = str_replace( '/', '.', $fecha );
        
        if($empresaId==null)
            $empresaId = ".NA";
        
        if($empresaNombreCorto=="" || $empresaNombreCorto==null)
            $empresaNombreCorto = "EMP".$empresaId;
        
        $referencia = $empresaNombreCorto . "-".$fecha."-".$contadorEmpresa;
        
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
            'tipoAuditoriaId' => $tipoAuditoriaId
            
        ];
        
        return $registro;
    }
    
    public function eliminar($llaves)
    {
        $resultado = $this->eliminarRespuestasNoCategorias($llaves->id);
        if($resultado->mensajeError=="")
        {
            $resultado = $this->eliminarRespuestasNo($llaves->id);
            if($resultado->mensajeError=="")
            {
                $resultado = $this->eliminarRespuestasSiCategorias($llaves->id);
                if($resultado->mensajeError=="")
                {
                    $resultado = $this->eliminarRespuestasSi($llaves->id);
                    if($resultado->mensajeError=="")
                    {
                        
                        $resultado = $this->eliminarPreguntas($llaves->id);
                        if($resultado->mensajeError=="")
                        {
                            $resultado = $this->eliminarSecciones($llaves->id);
                            if($resultado->mensajeError=="")
                            {
                                //$sentencia->close();
                                $consulta = " DELETE FROM plantillas "
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
            }
        }
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
    
    
}

