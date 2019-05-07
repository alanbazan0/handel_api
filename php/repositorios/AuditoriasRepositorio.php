<?php
namespace php\repositorios;

use php\interfaces\IAuditoriasRepositorio;
use php\modelos\Auditoria;
use php\modelos\Resultado;
use php\clases\AdministradorConexion;

include "../interfaces/IAuditoriasRepositorio.php";
include "../modelos/Auditoria.php";
include "RepositorioBase.php";
require_once("../clases/Resultado.php");
require_once('../clases/AdministradorConexion.php');

class AuditoriasRepositorio extends RepositorioBase implements IAuditoriasRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = " SELECT A.id, A.plantilla_id, P.nombre, IFNULL(DATE_FORMAT(A.fecha_ejecucion,'%d/%m/%Y %H:%i:%s'),'')fecha_ejecucion, A.empresa_id, E.nombre, IFNULL(E.nombre_corto,'')nombre_corto, A.contador_empresa " .
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
                              
                                $resultado = $this->consultarEncabezado($modelo->id);
                                if($resultado->mensajeError=="")
                                {
                                    $this->conexion->commit();
                                   
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
        
//         if($this->existeAuditoria($modelo->id))
//         {
//              $resultado = $this->insertarDatosAuditoria($modelo);
//              if($resultado->mensajeError=="")
//                 $resultado->valor = $modelo->id;
//         }
//         else
//         {
            
//         }
            
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
    
    private function eliminarRespuestasSi($plantillaId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM respuestas_si WHERE plantilla_id = ?";
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
                    $resultado->mensajeError = "Falló la ejecución eliminarRespuestasSi(" . $this->conexion->errno . ") " . $this->conexion->error;
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
    
    private function eliminarRespuestasNo($plantillaId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM respuestas_no WHERE plantilla_id = ?";
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
                    $resultado->mensajeError = "Falló la ejecución eliminarRespuestasNo(" . $this->conexion->errno . ") " . $this->conexion->error;
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
    
    private function insertarSecciones($plantillaId,$secciones)
    {
        
        $resultado = new Resultado();
      
        
        for ($i = 0; $i <  count($secciones); $i++)
        {
            $seccion= $secciones[$i];
          
            $consulta = "INSERT INTO secciones(plantilla_id, id, texto ) " .
                "VALUE(?, ?, ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
              
                if($sentencia->bind_param("iis",$plantillaId,$seccion->id, $seccion->texto))
                {
                    if($sentencia->execute())
                    {
                        $resultado->valor =$seccion->id;
                        $sentencia->close();
                        
                    }
                    else
                    {
                        $resultado->codigoError = $this->conexion->errno;
                        $resultado->mensajeError = "Falló la ejecución insertarSecciones(" . $this->conexion->errno . ") " . $this->conexion->error;
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
        
        
        return $resultado;
    }
    
    private function insertarPreguntas($auditoriaId,$plantillaId,$seccionId,$preguntas)
    {
        $resultado = new Resultado();
        for ($i = 0; $i <  count($preguntas); $i++)
        {
            $pregunta = $preguntas[$i];
            $consulta = "INSERT INTO auditoria_preguntas(auditoria_id, plantilla_id, seccion_id, pregunta_id, valor) " .
                "VALUE(?, ?, ?, ?, ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param("iiiis",$auditoriaId,$plantillaId,$seccionId, $pregunta->id, $pregunta->valor))
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
        return $resultado;
    }
    
    private function insertarRespuestas($plantillaId,$secciones)
    {
        $resultado = new Resultado();
        
        for ($i = 0; $i <  count($secciones); $i++)
        {
            $seccion = $secciones[$i];
            
            for ($j = 0; $j < count($seccion->preguntas); $j++)
            {
                $pregunta = $seccion->preguntas[$j];
                
                for ($k = 0; $k< count($pregunta->respuestas_si); $k++)
                {
                    $respuesta = $pregunta->respuestas_si[$k];
                    
                    $consulta = "INSERT INTO respuestas_si(plantilla_id, seccion_id, pregunta_id, id, texto, peso, hallazgo, recomendacion) " .
                        "VALUE(?, ?, ?, ?, ?, ?, ?, ?)";
                    if($sentencia = $this->conexion->prepare($consulta))
                    {
                        if($sentencia->bind_param("iiiisiss",$plantillaId,$seccion->id, $pregunta->id,$respuesta->id, $respuesta->texto,$respuesta->peso,$respuesta->hallazgo,$respuesta->recomendacion))
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
        return $resultado;
    }
    
    private function insertarRespuestasNo($plantillaId,$secciones)
    {
        $resultado = new Resultado();
        
        for ($i = 0; $i <  count($secciones); $i++)
        {
            $seccion = $secciones[$i];
            
            for ($j = 0; $j < count($seccion->preguntas); $j++)
            {
                $pregunta = $seccion->preguntas[$j];
                
                for ($k = 0; $k< count($pregunta->respuestas_no); $k++)
                {
                    $respuesta = $pregunta->respuestas_no[$k];
                    
                    $consulta = "INSERT INTO respuestas_no(plantilla_id, seccion_id, pregunta_id, id, texto, peso, hallazgo, recomendacion) " .
                        "VALUE(?, ?, ?, ?, ?, ?, ?, ?)";
                    if($sentencia = $this->conexion->prepare($consulta))
                    {
                        if($sentencia->bind_param("iiiisiss",$plantillaId,$seccion->id, $pregunta->id,$respuesta->id, $respuesta->texto,$respuesta->peso,$respuesta->hallazgo,$respuesta->recomendacion))
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
                            $resultado->mensajeError = "Falló el enlace de parámetros";
                            break;
                        }
                    }
                    else
                    {
                        $resultado->codigoError = $this->conexion->errno;
                        $resultado->mensajeError = "Falló la preparación: insertarRespuestasNo(" . $this->conexion->errno . ") " . $this->conexion->error;
                        break;
                    }
                }
            }
            
        }
        return $resultado;
    }
    
    private function insertarRespuestasSiCategorias($plantillaId,$secciones)
    {
        $resultado = new Resultado();
        
        for ($i = 0; $i <  count($secciones); $i++)
        {
            $seccion = $secciones[$i];
            for ($j = 0; $j < count($seccion->preguntas); $j++)
            {
                $pregunta = $seccion->preguntas[$j];
                for ($k = 0; $k< count($pregunta->respuestas_si); $k++)
                {
                    $respuesta = $pregunta->respuestas_si[$k];
                    for ($l = 0; $l< count($respuesta->categorias); $l++)
                    {
                        $categoria = $respuesta->categorias[$l];
                        
                        $consulta = "INSERT INTO respuestas_si_categorias(plantilla_id, seccion_id, pregunta_id, respuesta_si_id, id, categoria_id) " .
                            "VALUE(?, ?, ?, ?, ?, ?)";
                        if($sentencia = $this->conexion->prepare($consulta))
                        {
                            if($sentencia->bind_param("iiiiii",$plantillaId,$seccion->id, $pregunta->id,$respuesta->id, $categoria->id, $categoria->categoriaId))
                            {
                                if($sentencia->execute())
                                {
                                    $sentencia->close();
                                }
                                else
                                {
                                    $resultado->codigoError = $this->conexion->errno;
                                    $resultado->mensajeError = "Falló la ejecución insertarRespuestasSiCategorias(" . $this->conexion->errno . ") " . $this->conexion->error;
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
        return $resultado;
    }
    
    private function insertarRespuestasNoCategorias($plantillaId,$secciones)
    {
        $resultado = new Resultado();
        
        for ($i = 0; $i <  count($secciones); $i++)
        {
            $seccion = $secciones[$i];
            for ($j = 0; $j < count($seccion->preguntas); $j++)
            {
                $pregunta = $seccion->preguntas[$j];
                for ($k = 0; $k< count($pregunta->respuestas_no); $k++)
                {
                    $respuesta = $pregunta->respuestas_no[$k];
                    for ($l = 0; $l< count($respuesta->categorias); $l++)
                    {
                        $categoria = $respuesta->categorias[$l];
                        
                        $consulta = "INSERT INTO respuestas_no_categorias(plantilla_id, seccion_id, pregunta_id, respuesta_no_id, id, categoria_id) " .
                            "VALUE(?, ?, ?, ?, ?, ?)";
                        if($sentencia = $this->conexion->prepare($consulta))
                        {
                            if($sentencia->bind_param("iiiiii",$plantillaId,$seccion->id, $pregunta->id,$respuesta->id, $categoria->id, $categoria->categoriaId))
                            {
                                if($sentencia->execute())
                                {
                                    $sentencia->close();
                                }
                                else
                                {
                                    $resultado->codigoError = $this->conexion->errno;
                                    $resultado->mensajeError = "Falló la ejecución: insertarRespuestasNoCategorias(" . $this->conexion->errno . ") " . $this->conexion->error;
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
                            $resultado->mensajeError = "Falló la preparación: insertarRespuestasNoCategorias(" . $this->conexion->errno . ") " . $this->conexion->error;
                            break;
                        }
                    }
                }
            }
        }
        return $resultado;
    }
    
    private function insertarPreguntasCategorias($plantillaId,$secciones)
    {
        $resultado = new Resultado();
        
        for ($i = 0; $i <  count($secciones); $i++)
        {
            $seccion = $secciones[$i];
            for ($j = 0; $j < count($seccion->preguntas); $j++)
            {
                $pregunta = $seccion->preguntas[$j];
                if(isset($pregunta->categorias))
                {
                    for ($l = 0; $l< count($pregunta->categorias); $l++)
                    {
                        $categoria = $pregunta->categorias[$l];
                        
                        $consulta = "INSERT INTO preguntas_categorias(plantilla_id, seccion_id, pregunta_id, id, categoria_id) " .
                            "VALUE(?, ?, ?, ?, ?)";
                        if($sentencia = $this->conexion->prepare($consulta))
                        {
                            if($sentencia->bind_param("iiiii",$plantillaId,$seccion->id, $pregunta->id,$categoria->id, $categoria->categoriaId))
                            {
                                if($sentencia->execute())
                                {
                                    $sentencia->close();
                                }
                                else
                                {
                                    $resultado->codigoError = $this->conexion->errno;
                                    $resultado->mensajeError = "Falló la ejecución: insertarPreguntasCategorias(" . $this->conexion->errno . ") " . $this->conexion->error;
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
                            $resultado->mensajeError = "Falló la preparación: insertarRespuestasNoCategorias(" . $this->conexion->errno . ") " . $this->conexion->error;
                            break;
                        }
                    }
                }
            }
            
        }
        return $resultado;
    }
    
    
    public function actualizar(Auditoria $modelo)
    {
        $resultado = new Resultado();
        $datetime = null;
        if($modelo->fechaProgramada!=null)
        {
            $elementos = explode('/', $modelo->fechaProgramada);
            if(count($elementos)==3)
            {
                $dia = $elementos[0];
                $mes = $elementos[1];
                $ano = $elementos[2];
                $datetime = date("Y-m-d H:i:s", mktime(10, 30, 0, $mes, $dia, $ano));
            }
        }
        
        $this->conexion->autocommit(FALSE);
        
        $consulta = " UPDATE auditorias " .
            "SET empresa_id = ? " .      
            "WHERE id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("ii", $modelo->empresaId,$modelo->id))
            {
                if($sentencia->execute())
                {
                    $resultado->valor=true;
                    $sentencia->close();
                    
                    $resultado = $this->insertarDatosAuditoria($modelo);
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
        $resultado =  $this->insertarPreguntas($modelo->id,$modelo->plantillaId, $modelo->seccionId,$modelo->preguntas);
        if($resultado->mensajeError=="")
        {
//             $resultado =  $this->insertarRespuestas($modelo->id,$modelo->secciones);
//             if($resultado->mensajeError=="")
//             {
//                 $this->conexion->commit();
//             }
//             else
//                 $this->conexion->rollback();
        }
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
            if(isset($criteriosSeleccion->nombre))
            {
                if($criteriosSeleccion->nombre!="")
                {
                    array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'A','campo'=>'nombre','valor'=>$criteriosSeleccion->nombre]);
                }
            }
            $where = $this->where($filtros);
        }
        $consulta = $this->consultaBase .
        $where;
        
      
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id,  $plantillaId, $plantillaNombre, $fechaEjecucion, $empresaId, $empresaNombre, $empresaNombreCorto, $contadorEmpresa))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id,  $plantillaId, $plantillaNombre, $fechaEjecucion, $empresaId, $empresaNombre, $empresaNombreCorto, $contadorEmpresa);
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
                    if ($sentencia->bind_result($id,  $plantillaId, $plantillaNombre, $fechaEjecucion, $empresaId, $empresaNombre, $empresaNombreCorto, $contadorEmpresa))
                    {
                        if($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id,  $plantillaId, $plantillaNombre, $fechaEjecucion, $empresaId, $empresaNombre, $empresaNombreCorto, $contadorEmpresa);
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
                    if ($sentencia->bind_result($id,  $plantillaId, $plantillaNombre, $fechaEjecucion, $empresaId, $empresaNombre, $empresaNombreCorto, $contadorEmpresa))
                    {
                        if($sentencia->fetch())
                        {
                            $plantilla = $this->crearRegistro($id,  $plantillaId, $plantillaNombre, $fechaEjecucion, $empresaId, $empresaNombre, $empresaNombreCorto, $contadorEmpresa);
                           
                            
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
    
 
    private function consultarPreguntas($plantillaId,$seccionId)
    {
        
        $resultado = new Resultado();
        $preguntas = array();
        $consulta = "SELECT id, RTRIM(texto) texto, IFNULL(hallazgo,'') hallazgo, IFNULL(recomendacion,'') recomendacion, tipo, IFNULL(colapsado,0) colapsado,IFNULL(practicas,'') practicas,IFNULL(observaciones,'') observaciones, IFNULL(peso,0) peso  " .
                     "FROM preguntas " .        
                    " WHERE plantilla_id  = ? AND seccion_id = ? ".
                    "ORDER BY id";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            
            if($sentencia->bind_param("ii",$plantillaId,$seccionId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $texto, $hallazgo, $recomendacion, $tipo, $colapsado, $practicas, $observaciones, $peso))
                    {
                       
                        while($sentencia->fetch())
                        {
                           
                            $pregunta= (object) [
                                'id' =>  $id,
                                'texto' => $texto,
                                'hallazgo' => $hallazgo,
                                'recomendacion' => $recomendacion,
                                'tipo' => $tipo,
                                'colapsado' => $colapsado,
                                'practicas' => $practicas,
                                'observaciones' => $observaciones,
                                'peso' => $peso 
                                
                            ];
                            array_push($preguntas,$pregunta);
                        }
                        $resultado->valor = $preguntas;
                        
                        $sentencia->close();
                        for($i=0; $i < count($preguntas);$i++)
                        {
                            $pregunta = $preguntas[$i];
                            $resultadoRespuestas = $this->consultarPreguntasCategorias($plantillaId,$seccionId,$pregunta->id);
                            if($resultadoRespuestas->mensajeError=="")
                            {
                                $pregunta->categorias = $resultadoRespuestas->valor;
                            }
                            else
                            {
                                $resultado->mensajeError = $resultadoRespuestas->mensajeError;
                                break;
                            }
                        }
                        for($i=0; $i < count($preguntas);$i++)
                        {
                            $pregunta = $preguntas[$i];
                            $resultadoRespuestas = $this->consultarRespuestasSi($plantillaId,$seccionId,$pregunta->id);
                            if($resultadoRespuestas->mensajeError=="")
                            {
                                $pregunta->respuestas_si = $resultadoRespuestas->valor;
                            }
                            else
                            {
                                $resultado->mensajeError = $resultadoRespuestas->mensajeError;
                                break;
                            }
                        }
                        for($i=0; $i < count($preguntas);$i++)
                        {
                            $pregunta = $preguntas[$i];
                            $resultadoRespuestas = $this->consultarRespuestasNo($plantillaId,$seccionId,$pregunta->id);
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
    
    private function consultarRespuestasSi($plantillaId,$seccionId,$preguntaId)
    {
       
        $resultado = new Resultado();
        $respuestas = array();
        $consulta = "SELECT id, RTRIM(texto) texto, IFNULL(peso,0) peso, RTRIM(IFNULL(hallazgo,''))hallazgo, RTRIM(IFNULL(recomendacion,''))recomendacion " .
            "FROM respuestas_si " .
            " WHERE plantilla_id  = ? AND seccion_id= ? AND pregunta_id = ? ".
            "ORDER BY id";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("iii",$plantillaId,$seccionId,$preguntaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $texto,$peso,$hallazgo,$recomendacion))
                    {
                        while($sentencia->fetch())
                        {
                            $respuesta= (object) [
                                'id' =>  $id,
                                'texto' => $texto,
                                'peso' => $peso,
                                'hallazgo' => $hallazgo,
                                'recomendacion' => $recomendacion
                            ];
                            array_push($respuestas,$respuesta);
                        }
                        $resultado->valor = $respuestas;
                        $sentencia->close();
                        
                        for($i=0; $i < count($respuestas);$i++)
                        {
                            $respuesta = $respuestas[$i];
                            $resultadoRespuestas = $this->consultarRespuestasSiCategorias($plantillaId,$seccionId,$preguntaId,$respuesta->id);
                            if($resultadoRespuestas->mensajeError=="")
                            {
                                $respuesta->categorias = $resultadoRespuestas->valor;
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
    
    private function consultarRespuestasNo($plantillaId,$seccionId,$preguntaId)
    {
        
        $resultado = new Resultado();
        $respuestas = array();
        $consulta = "SELECT id, RTRIM(texto) texto, IFNULL(peso,0) peso,RTRIM(hallazgo)hallazgo, RTRIM(recomendacion)recomendacion " .
            "FROM respuestas_no " .
            " WHERE plantilla_id  = ? AND seccion_id= ? AND pregunta_id = ? ".
            "ORDER BY id";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("iii",$plantillaId,$seccionId,$preguntaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $texto,$peso,$hallazgo,$recomendacion))
                    {
                        while($sentencia->fetch())
                        {
                            $respuesta= (object) [
                                'id' =>  $id,
                                'texto' => $texto,
                                'peso' => $peso,
                                'hallazgo' => $hallazgo,
                                'recomendacion' => $recomendacion
                            ];
                            array_push($respuestas,$respuesta);
                        }
                        $resultado->valor = $respuestas;
                        $sentencia->close();
                        
                        for($i=0; $i < count($respuestas);$i++)
                        {
                            $respuesta = $respuestas[$i];
                            $resultadoRespuestas = $this->consultarRespuestasNoCategorias($plantillaId,$seccionId,$preguntaId,$respuesta->id);
                            if($resultadoRespuestas->mensajeError=="")
                            {
                                $respuesta->categorias = $resultadoRespuestas->valor;
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
            $resultado->mensajeError = "Falló la preparación: consultarRespuestasNo(" . $this->conexion->errno . ") " . $this->conexion->error;
            
            return $resultado;
    }
    
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
    
    private function crearRegistro($id,  $plantillaId, $plantillaNombre, $fechaEjecucion, $empresaId, $empresaNombre, $empresaNombreCorto, $contadorEmpresa)
    {
        $archivoIcono = '../../php/iconos/icono'.$plantillaId.'.png';
        $icono = 'default.png';
        if(file_exists($archivoIcono))
            $icono = 'icono'.$plantillaId.'.png';
        
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
            'referencia' => $referencia
            
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
    
}

