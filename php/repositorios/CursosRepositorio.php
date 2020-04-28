<?php
namespace php\repositorios;

use php\interfaces\ICursosRepositorio;
use php\modelos\Curso;
use php\modelos\Resultado;
use php\clases\AdministradorConexion;

include "../interfaces/ICursosRepositorio.php";
include "../modelos/Curso.php";
include "RepositorioBase.php";
require_once("../clases/Resultado.php");
require_once('../clases/AdministradorConexion.php');

class CursosRepositorio extends RepositorioBase implements ICursosRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = " SELECT C.id, IFNULL(C.titulo,''), IFNULL(C.descripcion,''), IFNULL(DATE_FORMAT(C.fecha_alta,'%d/%m/%Y %H:%i:%s'),'')fecha_alta, IFNULL(DATE_FORMAT(C.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'')fecha_modificacion, IFNULL(C.publicado,0), U.id, U.nombre, U.apellido " .
            " FROM cursos C
                INNER JOIN usuarios U ON C.usuario_id = U.id ";
           
    }
    
    public function insertar($usuario,Curso $modelo)
    {
        $this->conexion->autocommit(FALSE);
        $resultado =  $this->calcularId("id","cursos");
        if($resultado->mensajeError=="")
        {
            $modelo->id = $resultado->valor;
            $consulta = "INSERT INTO cursos(id, titulo, descripcion, fecha_alta, fecha_modificacion, publicado, usuario_id) " .
                "VALUE(?, ?, ?,  NOW(), NOW(), 0,?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if( $sentencia->bind_param("issi", $modelo->id, $modelo->titulo,$modelo->descripcion,$usuario->id))
                {
                    if($sentencia->execute())
                    {
                        $sentencia->close();
                        
                         $resultado = $this->insertarLeccion($modelo->id,"Sin título");
                         if($resultado->mensajeError=="")
                         {
                            $resultado->valor = $modelo->id;
                            $this->conexion->commit();
                         }
                    }
                    else
                    {
                        $resultado->mensajeError = "Falló la ejecución insertar(" . $this->conexion->errno . ") " . $this->conexion->error;
                    }
                }
                else
                    $resultado->mensajeError = "Falló el enlace de parámetros";
            }
            else
                $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
    
           
           
        }
            
        return $resultado;
    }
    
    
    public function insertarPregunta($cursoId, $leccionId, $tipo)
    {
        $resultado =  $this->calcularIdPregunta($cursoId, $leccionId, "id");
        if($resultado->correcto())
        {
            $id =  $resultado->valor;
            $resultado =  $this->calcularIdPregunta($cursoId, $leccionId,"orden");
            if($resultado->correcto())
            {
                $orden =  $resultado->valor;
                $consulta = "INSERT INTO cursos_preguntas(curso_id, leccion_id, id, orden,texto,tipo) " .
                            "VALUE(?, ?, ?, ?, '','om')";
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if($sentencia->bind_param("iiii",$cursoId,$leccionId, $id, $orden))
                    {
                        if($sentencia->execute())
                        {
                            $sentencia->close();
                            $resultado->valor = $id;
                        }
                        else
                        {
                            $resultado->mensajeError = __FUNCTION__ ." Falló la ejecución insertar(" . $this->conexion->errno . ") " . $this->conexion->error;
                        }
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__ ." Falló el enlace de parámetros";
                }
                else
                    $resultado->mensajeError = __FUNCTION__ ." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
        }
        
        return $resultado;
    }
    
    public function insertarLeccion($cursoId, $texto)
    {
        $resultado =  $this->calcularIdLeccion($cursoId, "id");
        if($resultado->correcto())
        {
            $id =  $resultado->valor;
            $resultado =  $this->calcularIdLeccion($cursoId,"orden");
            if($resultado->correcto())
            {
                $orden =  $resultado->valor;
                $consulta = "INSERT INTO cursos_lecciones(curso_id, id, orden, titulo) " .
                    "VALUE(?, ?, ?, ?)";
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if($sentencia->bind_param("iiis",$cursoId, $id, $orden, $texto))
                    {
                        if($sentencia->execute())
                        {
                            $sentencia->close();
                            $resultado->valor = $id;
                        }
                        else
                        {
                            $resultado->mensajeError = "Falló la ejecución insertar(" . $this->conexion->errno . ") " . $this->conexion->error;
                        }
                    }
                    else
                        $resultado->mensajeError = "Falló el enlace de parámetros";
                }
                else
                    $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
        }
        
        return $resultado;
    }
    
    private function eliminarRespuestas($cursoId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM cursos_respuestas WHERE curso_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$cursoId))
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
    
    private function eliminarRespuestasSiPregunta($cursoId,$leccionId,$preguntaId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM respuestas_si WHERE plantilla_id = ? AND seccion_id = ? AND pregunta_id = ?";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("iii",$cursoId, $leccionId, $preguntaId))
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
    
    private function eliminarRespuestasSiSeccion($cursoId,$leccionId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM respuestas_si WHERE plantilla_id = ? AND seccion_id = ?";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ii",$cursoId, $leccionId))
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
                $resultado->mensajeError = __FUNCTION__." Falló el enlace de parámetros";
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = __FUNCTION__." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
        }
        return $resultado;
    }
    
    private function eliminarRespuestasNoSeccion($cursoId,$leccionId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM respuestas_no WHERE plantilla_id = ? AND seccion_id = ?";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ii",$cursoId, $leccionId))
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
                $resultado->mensajeError = __FUNCTION__." Falló el enlace de parámetros";
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = __FUNCTION__." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
        }
        return $resultado;
    }
    
    private function eliminarRespuestasNoPregunta($cursoId,$leccionId,$preguntaId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM respuestas_no WHERE plantilla_id = ? AND seccion_id = ? AND pregunta_id = ?";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("iii",$cursoId, $leccionId, $preguntaId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = "Falló la ejecución eliminarRespuestasNoPregunta(" . $this->conexion->errno . ") " . $this->conexion->error;
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
    
    private function eliminarRespuestasNo($cursoId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM respuestas_no WHERE plantilla_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$cursoId))
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
    
    private function eliminarRespuestasSiCategorias($cursoId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM respuestas_si_categorias WHERE plantilla_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$cursoId))
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
    
    private function eliminarRespuestasSiCategoriasPregunta($cursoId, $leccionId, $preguntaId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM respuestas_si_categorias WHERE plantilla_id = ? AND seccion_id = ? AND pregunta_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("iii",$cursoId, $leccionId, $preguntaId))
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
    
    private function eliminarRespuestasNoCategoriasPregunta($cursoId, $leccionId, $preguntaId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM respuestas_no_categorias WHERE plantilla_id = ? AND seccion_id = ? AND pregunta_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("iii",$cursoId, $leccionId, $preguntaId))
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
    
    private function eliminarPreguntasCategorias($cursoId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM preguntas_categorias WHERE plantilla_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$cursoId))
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
    
    private function eliminarPerfiles($cursoId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM cursos_perfiles WHERE curso_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$cursoId))
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
    
    private function eliminarRespuestasNoCategorias($cursoId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM respuestas_no_categorias WHERE plantilla_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$cursoId))
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
    
    private function eliminarPreguntas($cursoId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM cursos_preguntas WHERE curso_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$cursoId))
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
    
    private function eliminarPreguntasLeccion($cursoId,$leccionId)
    {
        $resultado = new Resultado();
        
        $resultado = $this->eliminarRespuestasSiSeccion($cursoId,$leccionId);
        if($resultado->correcto())
        {
            $resultado = $this->eliminarRespuestasNoSeccion($cursoId,$leccionId);
            if($resultado->correcto())
            {
                $consulta ="DELETE FROM cursos_preguntas WHERE curso_id = ? and leccion_id = ?";
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if($sentencia->bind_param("ii",$cursoId,$leccionId))
                    {
                        if($sentencia->execute())
                        {
                            $sentencia->close();
                        }
                        else
                        {
                            $resultado->codigoError = $this->conexion->errno;
                            $resultado->mensajeError = __FUNCTION__ ." Falló la ejecución eliminarPreguntas(" . $this->conexion->errno . ") " . $this->conexion->error;
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
        
        
        return $resultado;
    }
    
    private function eliminarLecciones($cursoId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM cursos_lecciones WHERE curso_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$cursoId))
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
    
    private function insertarSecciones($cursoId,$secciones)
    {
        
        $resultado = new Resultado();
      
        
        for ($i = 0; $i <  count($secciones); $i++)
        {
            $seccion= $secciones[$i];
          
            $consulta = "INSERT INTO secciones(plantilla_id, id, texto ) " .
                "VALUE(?, ?, ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
              
                if($sentencia->bind_param("iis",$cursoId,$seccion->id, $seccion->texto))
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
    
    private function insertarPreguntas($cursoId,$secciones)
    {
       
        $resultado = new Resultado();
        
        for ($i = 0; $i <  count($secciones); $i++)
        {
            $seccion = $secciones[$i];
            
            for ($j = 0; $j < count($seccion->preguntas); $j++)
            {
                $pregunta = $seccion->preguntas[$j];
                $consulta = "INSERT INTO preguntas(plantilla_id, seccion_id, id, texto, hallazgo, recomendacion, colapsado, tipo, practicas, observaciones, peso) " .
                    "VALUE(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if($sentencia->bind_param("iiisssisssi",$cursoId,$seccion->id, $pregunta->id, $pregunta->texto, $pregunta->hallazgo, $pregunta->recomendacion, $pregunta->colapsado, $pregunta->tipo,$pregunta->practicas, $pregunta->observaciones, $pregunta->peso))
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
        
        
        return $resultado;
    }
    
    private function insertarRespuestasSi($cursoId,$secciones)
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
                        if($sentencia->bind_param("iiiisiss",$cursoId,$seccion->id, $pregunta->id,$respuesta->id, $respuesta->texto,$respuesta->peso,$respuesta->hallazgo,$respuesta->recomendacion))
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
    
    private function insertarRespuestasNo($cursoId,$secciones)
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
                        if($sentencia->bind_param("iiiisiss",$cursoId,$seccion->id, $pregunta->id,$respuesta->id, $respuesta->texto,$respuesta->peso,$respuesta->hallazgo,$respuesta->recomendacion))
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
    
    private function insertarRespuestasSiCategorias($cursoId,$secciones)
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
                            if($sentencia->bind_param("iiiiii",$cursoId,$seccion->id, $pregunta->id,$respuesta->id, $categoria->id, $categoria->categoriaId))
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
    
    private function insertarRespuestasSiCategoriasPregunta($cursoId,$leccionId, $preguntaId,$respuestas)
    {
        $resultado = new Resultado();
        
        for ($k = 0; $k< count($respuestas); $k++)
        {
            $respuesta =$respuestas[$k];
            for ($l = 0; $l< count($respuesta->categorias); $l++)
            {
                $categoria = $respuesta->categorias[$l];
                
                $consulta = "INSERT INTO respuestas_si_categorias(plantilla_id, seccion_id, pregunta_id, respuesta_si_id, id, categoria_id) " .
                    "VALUE(?, ?, ?, ?, ?, ?)";
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if($sentencia->bind_param("iiiiii",$cursoId,$leccionId, $preguntaId,$respuesta->id, $categoria->id, $categoria->categoriaId))
                    {
                        if($sentencia->execute())
                        {
                            $sentencia->close();
                        }
                        else
                        {
                            $resultado->codigoError = $this->conexion->errno;
                            $resultado->mensajeError = "Falló la ejecución insertarRespuestasSiCategoriasPregunta(" . $this->conexion->errno . ") " . $this->conexion->error;
                            break;
                        }
                    }
                    else
                    {
                        $resultado->mensajeError = "Falló el enlace de parámetros insertarRespuestasSiCategoriasPregunta";
                        break;
                    }
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = "Falló la preparación insertarRespuestasSiCategoriasPregunta: (" . $this->conexion->errno . ") " . $this->conexion->error;
                    break;
                }
            }
        }
        return $resultado;
    }
    
    private function insertarRespuestasNoCategoriasPregunta($cursoId,$leccionId, $preguntaId,$respuestas)
    {
        $resultado = new Resultado();
        
        for ($k = 0; $k< count($respuestas); $k++)
        {
            $respuesta =$respuestas[$k];
            for ($l = 0; $l< count($respuesta->categorias); $l++)
            {
                $categoria = $respuesta->categorias[$l];
                
                $consulta = "INSERT INTO respuestas_no_categorias(plantilla_id, seccion_id, pregunta_id, respuesta_si_id, id, categoria_id) " .
                    "VALUE(?, ?, ?, ?, ?, ?)";
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if($sentencia->bind_param("iiiiii",$cursoId,$leccionId, $preguntaId,$respuesta->id, $categoria->id, $categoria->categoriaId))
                    {
                        if($sentencia->execute())
                        {
                            $sentencia->close();
                        }
                        else
                        {
                            $resultado->codigoError = $this->conexion->errno;
                            $resultado->mensajeError = "Falló la ejecución insertarRespuestasNoCategoriasPregunta(" . $this->conexion->errno . ") " . $this->conexion->error;
                            break;
                        }
                    }
                    else
                    {
                        $resultado->mensajeError = "Falló el enlace de parámetros insertarRespuestasNoCategoriasPregunta";
                        break;
                    }
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = "Falló la preparación insertarRespuestasSiCategoriasPregunta: (" . $this->conexion->errno . ") " . $this->conexion->error;
                    break;
                }
            }
        }
        return $resultado;
    }
    
    private function insertarRespuestasNoCategorias($cursoId,$secciones)
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
                            if($sentencia->bind_param("iiiiii",$cursoId,$seccion->id, $pregunta->id,$respuesta->id, $categoria->id, $categoria->categoriaId))
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
    
    private function insertarPreguntasCategorias($cursoId,$secciones)
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
                            if($sentencia->bind_param("iiiii",$cursoId,$seccion->id, $pregunta->id,$categoria->id, $categoria->categoriaId))
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
    
    private function insertarPerfiles($cursoId,$perfiles)
    {
        $resultado = new Resultado();
        
        for ($l = 0; $l< count($perfiles); $l++)
        {
            $perfil = $perfiles[$l];
            
            $consulta = "INSERT INTO cursos_perfiles(curso_id, id, perfil_id) " .
                "VALUE(?, ?, ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param("iii",$cursoId,$perfil->id, $perfil->perfilId))
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
    
    
    private function insertarCategoriasPregunta($cursoId,$leccionId, $preguntaId, $categorias)
    {
        $resultado = new Resultado();
        
        for ($l = 0; $l< count($categorias); $l++)
        {
            $categoria = $categorias[$l];
            
            $consulta = "INSERT INTO preguntas_categorias(plantilla_id, seccion_id, pregunta_id, id, categoria_id) " .
                "VALUE(?, ?, ?, ?, ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param("iiiii",$cursoId,$leccionId, $preguntaId,$categoria->id, $categoria->categoriaId))
                {
                    if($sentencia->execute())
                    {
                        $sentencia->close();
                    }
                    else
                    {
                        $resultado->codigoError = $this->conexion->errno;
                        $resultado->mensajeError = __FUNCTION__ ." Falló la ejecución: (" . $this->conexion->errno . ") " . $this->conexion->error;
                        break;
                    }
                }
                else
                {
                    $resultado->mensajeError = __FUNCTION__ ." Falló el enlace de parámetros";
                    break;
                }
            }
            else
            {
                $resultado->codigoError = $this->conexion->errno;
                $resultado->mensajeError = __FUNCTION__ ." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
                break;
            }
        }
        
        return $resultado;
    }
    
    
    
    public function actualizar(Curso $modelo)
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
        
        $consulta = " UPDATE plantillas " .
            "SET nombre = ?, " .      
            "  descripcion = ?, " .
            "  fecha_programada = ?, " .
            "  estatus = ?, " .
            "  fecha_modificacion= NOW() " .
            "WHERE id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("sssii", $modelo->nombre,$modelo->descripcion,$datetime, $modelo->estatus,$modelo->id ))
            {
                if($sentencia->execute())
                {
                    $resultado->valor=true;
                    $sentencia->close();
                    
                    $resultado = $this->insertarDatosPlantilla($modelo);
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
    
    public function actualizarValor($cursoId, $campo, $valor)
    {
        $resultado = new Resultado();

//         if($campo=="publicado")
//         {
//             if($valor=="on")
//                 $valor = 1;
//             else
//                 $valor = 0;
//         }
        
        
        $consulta = " UPDATE cursos " .
            "SET $campo = ?, " .
            "  fecha_modificacion= NOW() " .
            "WHERE id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("si", $valor, $cursoId))
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
    
    public function actualizarValorPregunta($cursoId, $leccionId, $preguntaId, $campo, $valor)
    {
        $resultado = new Resultado();
        $consulta = " UPDATE cursos_preguntas " .
            "SET $campo = ? " .
            "WHERE curso_id = ? AND leccion_id = ? AND id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("siii", $valor, $cursoId, $leccionId, $preguntaId ))
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
    
    public function actualizarCategoriasPregunta($cursoId, $leccionId, $preguntaId, $categorias)
    {
        ini_set('max_execution_time', 300);
        $resultado = new Resultado();
        $this->conexion->autocommit(FALSE);
        $resultado = $this->eliminarPerfiles($cursoId, $leccionId, $preguntaId);
        if($resultado->correcto())
        {
            $resultado = $this->insertarCategoriasPregunta($cursoId, $leccionId, $preguntaId, $categorias);
        }
        if($resultado->correcto())
            $this->conexion->commit();
        else
            $this->conexion->rollback();
        
    }
    
    public function actualizarValorLeccion($cursoId, $leccionId, $campo, $valor)
    {
        $resultado = new Resultado();
        $consulta = " UPDATE cursos_lecciones " .
            "SET $campo = ? " .
            "WHERE curso_id = ? AND id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("sii", $valor, $cursoId, $leccionId))
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
    
    
    public function actualizarPerfiles($cursoId, $perfiles)
    {
        $resultado = new Resultado();
        ini_set('max_execution_time', 300);
        $resultado = new Resultado();
        $this->conexion->autocommit(FALSE);
        $resultado  = $this->eliminarPerfiles($cursoId);
        if($resultado->correcto())
        {
            $resultado  = $this->insertarPerfiles($cursoId,$perfiles);
        }
        if($resultado->correcto())
             $this->conexion->commit();
        else
            $this->conexion->rollback();
        return $resultado;
    }
    
    public function insertarDatosPlantilla($modelo)
    {
        $resultado = $this->eliminarRespuestasNoCategorias($modelo->id);
        if($resultado->mensajeError=="")
        {
            $resultado = $this->eliminarRespuestasNo($modelo->id);
            if($resultado->mensajeError=="")
            {
                $resultado = $this->eliminarRespuestasSiCategorias($modelo->id);
                if($resultado->mensajeError=="")
                {
                    $resultado = $this->eliminarRespuestas($modelo->id);
                    if($resultado->mensajeError=="")
                    {
                        $resultado = $this->eliminarPreguntasCategorias($modelo->id);
                        if($resultado->mensajeError=="")
                        {
                            $resultado = $this->eliminarPreguntas($modelo->id);
                            if($resultado->mensajeError=="")
                            {
                                $resultado = $this->eliminarLecciones($modelo->id);
                                if($resultado->mensajeError=="")
                                {
                                    $resultado =  $this->insertarSecciones($modelo->id,$modelo->secciones);
                                    if($resultado->mensajeError=="")
                                    {
                                        $resultado =  $this->insertarPreguntas($modelo->id,$modelo->secciones);
                                        if($resultado->mensajeError=="")
                                        {
                                            $resultado =  $this->insertarPreguntasCategorias($modelo->id,$modelo->secciones);
                                            if($resultado->mensajeError=="")
                                            {
                                                $resultado =  $this->insertarRespuestasSi($modelo->id,$modelo->secciones);
                                                if($resultado->mensajeError=="")
                                                {
                                                    $resultado =  $this->insertarRespuestasSiCategorias($modelo->id,$modelo->secciones);
                                                    if($resultado->mensajeError=="")
                                                    {
                                                        $resultado =  $this->insertarRespuestasNo($modelo->id,$modelo->secciones);
                                                        if($resultado->mensajeError=="")
                                                        {
                                                            $resultado =  $this->insertarRespuestasNoCategorias($modelo->id,$modelo->secciones);
                                                            if($resultado->mensajeError=="")
                                                                $this->conexion->commit();
                                                                else
                                                                    $this->conexion->rollback();
                                                        }
                                                       
                                                    }
                                                }
                                                else
                                                    $this->conexion->rollback();
                                            }
                                            else
                                                $this->conexion->rollback();
                                        }
                                        else
                                            $this->conexion->rollback();
                                    }
                                    else
                                        $this->conexion->rollback();
                                }
                                else
                                    $this->conexion->rollback();
                            }
                            else
                                $this->conexion->rollback();
                        }
                        else
                            $this->conexion->rollback();
                    }
                    else
                        $this->conexion->rollback();
                }
                else
                    $this->conexion->rollback();
            }
            else
                $this->conexion->rollback();
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
            if(isset($criteriosSeleccion->titulo))
            {
                if($criteriosSeleccion->titulo!="")
                {
                    array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'C','campo'=>'titulo','valor'=>$criteriosSeleccion->titulo]);
                }
            }
            $where = $this->where($filtros);
        }
        $consulta = $this->consultaBase .
        $where . " order by UNIX_TIMESTAMP(C.fecha_alta) desc";
        
      
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombre, $descripcion, $fechaAlta, $fechaModificacion, $publicado, $usuarioId, $usuarioNombre, $usuarioApellido))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre, $descripcion, $fechaAlta, $fechaModificacion, $publicado, $usuarioId, $usuarioNombre, $usuarioApellido);
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
        ini_set('max_execution_time', 300);
        $consulta = $this->consultaBase .
        " WHERE C.id  = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$llaves->id))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombre, $descripcion, $fechaAlta, $fechaModificacion, $publicado, $usuarioId, $usuarioNombre, $usuarioApellido))
                    {
                        if($sentencia->fetch())
                        {
                            $curso = $this->crearRegistro($id, $nombre, $descripcion, $fechaAlta, $fechaModificacion, $publicado, $usuarioId, $usuarioNombre, $usuarioApellido);
                            
                            $resultado->valor = $curso;
                            
                            $sentencia->close();
                            
                            $resultadoSecciones = $this->consultarLecciones($curso->id);
                            if($resultadoSecciones->mensajeError=="")
                            {
                                $curso->lecciones = $resultadoSecciones->valor;
                                $resultadoPerfiles = $this->consultarPerfiles($curso->id);
                                if($resultadoPerfiles->correcto())
                                {
                                    $curso->perfiles = $resultadoPerfiles->valor;
                                }
                                else 
                                    $resultado->mensajeError = $resultadoPerfiles->mensajeError;
                            }
                            else
                                $resultado->mensajeError = $resultadoSecciones->mensajeError;
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
    
 
    private function consultarPreguntas($cursoId,$leccionId)
    {
        
        $resultado = new Resultado();
        $preguntas = array();
        $consulta = "SELECT id, RTRIM(texto) texto, tipo " .
                     "FROM cursos_preguntas " .        
                    " WHERE curso_id  = ? AND leccion_id = ? ".
                    "ORDER BY orden";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            
            if($sentencia->bind_param("ii",$cursoId,$leccionId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $texto, $tipo))
                    {
                       
                        while($sentencia->fetch())
                        {
                           
                            $pregunta= (object) [
                                'id' =>  $id,
                                'texto' => $texto,
                                'tipo' => $tipo
                                
                            ];
                            array_push($preguntas,$pregunta);
                        }
                        $resultado->valor = $preguntas;
                        
                        $sentencia->close();
                        
                        for($i=0; $i < count($preguntas);$i++)
                        {
                            $pregunta = $preguntas[$i];
                            $resultadoRespuestas = $this->consultarRespuestas($cursoId,$leccionId,$pregunta->id);
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
    
    private function consultarLecciones($cursoId)
    {
        $resultado = new Resultado();
        $lecciones = array();
        $consulta = "SELECT id, RTRIM(titulo) titulo, RTRIM(descripcion) descripcion, RTRIM(video) video " .
            "FROM cursos_lecciones " .
            " WHERE curso_id  = ? ".
            "ORDER BY orden";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$cursoId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $texto, $descripcion, $video))
                    {
                        while($sentencia->fetch())
                        {
                            $leccion= (object) [
                                'id' =>  $id,
                                'titulo' => $texto,
                                'descripcion' => $descripcion,
                                'video' => $video
                            ];
                            array_push($lecciones,$leccion);
                        }
                        $resultado->valor = $lecciones;
                        
                        $sentencia->close();
                        
                        for($i=0; $i < count($lecciones);$i++)
                        {
                            $leccion = $lecciones[$i];
                            $resultadoPreguntas = $this->consultarPreguntas($cursoId,$leccion->id);
                            if($resultadoPreguntas->mensajeError=="")
                            {
                                $leccion->preguntas = $resultadoPreguntas->valor;
                            }
                            else
                            {
                                $resultado->mensajeError = $resultadoPreguntas->mensajeError;
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
    
    private function consultarRespuestas($cursoId,$leccionId,$preguntaId)
    {
       
        $resultado = new Resultado();
        $respuestas = array();
        $consulta = "SELECT id, RTRIM(texto) texto, IFNULL(correcta,0) correcta " .
            "FROM cursos_respuestas " .
            " WHERE curso_id  = ? AND leccion_id= ? AND pregunta_id = ? ".
            "ORDER BY orden";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("iii",$cursoId,$leccionId,$preguntaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $texto,$correcta))
                    {
                        while($sentencia->fetch())
                        {
                            $respuesta= (object) [
                                'id' =>  $id,
                                'texto' => $texto,
                                'correcta' => $correcta,
                            ];
                            array_push($respuestas,$respuesta);
                        }
                        $resultado->valor = $respuestas;
                        $sentencia->close();
                       
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
    
    private function consultarRespuestasSiCategorias($cursoId,$leccionId,$preguntaId,$respuestaId)
    {
        $resultado = new Resultado();
        $categorias = array();
        $consulta = "SELECT id, categoria_id " .
            "FROM respuestas_si_categorias " .
            " WHERE plantilla_id = ? AND seccion_id= ? AND pregunta_id = ? AND respuesta_si_id = ? ".
            "ORDER BY id";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            
            if($sentencia->bind_param("iiii",$cursoId,$leccionId,$preguntaId,$respuestaId))
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
    
    private function consultarPerfiles($cursoId)
    {
        $resultado = new Resultado();
        $perfiles = array();
        $consulta = "SELECT id, perfil_id " .
            "FROM cursos_perfiles " .
            " WHERE curso_id = ? ".
            "ORDER BY id";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            
            if($sentencia->bind_param("i",$cursoId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id,$perfilId))
                    {
                        while($sentencia->fetch())
                        {
                            $perfil= (object) [
                                'id' =>  $id,
                                'perfilId' =>  $perfilId
                            ];
                            array_push($perfiles,$perfil);
                        }
                        $resultado->valor = $perfiles;
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
    
    private function consultarRespuestasNo($cursoId,$leccionId,$preguntaId)
    {
        
        $resultado = new Resultado();
        $respuestas = array();
        $consulta = "SELECT id, RTRIM(texto) texto, IFNULL(peso,0) peso,RTRIM(hallazgo)hallazgo, RTRIM(recomendacion)recomendacion " .
            "FROM respuestas_no " .
            " WHERE plantilla_id  = ? AND seccion_id= ? AND pregunta_id = ? ".
            "ORDER BY id";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("iii",$cursoId,$leccionId,$preguntaId))
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
                            $resultadoRespuestas = $this->consultarRespuestasNoCategorias($cursoId,$leccionId,$preguntaId,$respuesta->id);
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
    
    private function consultarRespuestasNoCategorias($cursoId,$leccionId,$preguntaId,$respuestaId)
    {
        $resultado = new Resultado();
        $categorias = array();
        $consulta = "SELECT id, categoria_id " .
            "FROM respuestas_no_categorias " .
            " WHERE plantilla_id = ? AND seccion_id= ? AND pregunta_id = ? AND respuesta_no_id = ? ".
            "ORDER BY id";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            
            if($sentencia->bind_param("iiii",$cursoId,$leccionId,$preguntaId,$respuestaId))
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
    
    private function crearRegistro($id, $titulo, $descripcion, $fechaAlta, $fechaModificacion, $publicado, $usuarioId, $usuarioNombre, $usuarioApellido)
    {

        $archivoIcono = '../../php/portadas_cursos/curso'.$id.'.png';
        $portada = 'default.png';
        if(file_exists($archivoIcono))
            $portada = 'curso'.$id.'.png';
        
        $registro= (object) [
            'id' =>  $id,               
            'titulo' => $titulo,     
            'portada' => $portada,
            'descripcion' => $descripcion,
            'fechaAlta' => $fechaAlta,
            'fechaModificacion' => $fechaModificacion,
            'publicado' => $publicado,
            'usuarioId' => $usuarioId,
            'usuarioNombre' => $usuarioNombre,
            'usuarioApellido' => $usuarioApellido
        ];
        
        $registro->usuarioNombreCompleto = $registro->usuarioNombre . " " . $registro->usuarioApellido;
        $registro->fotoPerfil =  "../fotos/usuario". $registro->usuarioId .".jpg";
        if(file_exists($registro->fotoPerfil))
            $registro->fotoPerfil =  "php/fotos/usuario". $registro->usuarioId .".jpg";
        else
            $registro->fotoPerfil =  "php/fotos/default.jpg";
        
        if($registro->publicado==1)
            $registro->box = "box-success";
        else
            $registro->box = "box-warning";
        return $registro;
    }
    
    public function eliminarPregunta($llaves)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM cursos_preguntas WHERE curso_id = ? AND leccion_id = ? AND id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("iii",$llaves->cursoId,$llaves->leccionId,$llaves->preguntaId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = __FUNCTION__." Falló la ejecución eliminarPreguntas(" . $this->conexion->errno . ") " . $this->conexion->error;
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
    
    public function eliminarRespuesta($llaves)
    {
        $resultado = new Resultado();
         
        
        $consulta ="DELETE FROM cursos_respuestas WHERE curso_id = ? AND leccion_id = ? AND pregunta_id = ? AND id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("iiii",$llaves->cursoId,$llaves->leccionId,$llaves->preguntaId,$llaves->respuestaId))
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
    
    public function eliminarLeccion($llaves)
    {
        ini_set('max_execution_time', 300);
        $resultado = new Resultado();
        $this->conexion->autocommit(FALSE);
        
        $resultado = $this->eliminarPreguntasLeccion($llaves->cursoId,$llaves->leccionId);
        if($resultado->correcto())
        {
            $consulta ="DELETE FROM cursos_lecciones WHERE curso_id = ? AND id = ?";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param("ii",$llaves->cursoId,$llaves->leccionId))
                {
                    if($sentencia->execute())
                    {
                        $sentencia->close();
                    }
                    else
                    {
                        $resultado->codigoError = $this->conexion->errno;
                        $resultado->mensajeError = __FUNCTION__." Falló la ejecución eliminarPreguntas(" . $this->conexion->errno . ") " . $this->conexion->error;
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
        if($resultado->correcto())
            $this->conexion->commit();
        else
            $this->conexion->rollback();
       
        return $resultado;
    }
    
    public function eliminar($llaves)
    {
        ini_set('max_execution_time', 300);
        $resultado = new Resultado();
        $this->conexion->autocommit(FALSE);
       
        $resultado = $this->eliminarPerfiles($llaves->id);
        if($resultado->correcto())
        {
            $resultado = $this->eliminarRespuestas($llaves->id);
            if($resultado->correcto())
            {
                $resultado = $this->eliminarPreguntas($llaves->id);
                if($resultado->correcto())
                {
                    $resultado = $this->eliminarLecciones($llaves->id);
                    if($resultado->correcto())
                    {
                        $consulta = " DELETE FROM cursos "
                            . "  WHERE id  = ? ";
                            if($sentencia = $this->conexion->prepare($consulta))
                            {
                                if($sentencia->bind_param("i",$llaves->id))
                                {
                                    if($sentencia->execute())
                                    {
                                        //var_dump($llaves);
                                        $resultado->valor = $llaves->id;
                                        //var_dump($resultado);
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
            $this->conexion->commit();
        else
            $this->conexion->rollback();
        return $resultado;
    }
    
    public function guardarRespuestasSi($cursoId, $leccionId, $preguntaId, $respuestas)
    {
        ini_set('max_execution_time', 300);
        $resultado = new Resultado();
        $this->conexion->autocommit(FALSE);
        
        //echo $cursoId."-".$leccionId."-".$preguntaId;
        
        $resultado = $this->eliminarRespuestasSiCategoriasPregunta($cursoId,$leccionId,$preguntaId);
        if($resultado->mensajeError=="")
        {
            $resultado = $this->eliminarRespuestasSiPregunta($cursoId,$leccionId,$preguntaId);
            if($resultado->mensajeError=="")
            {
                $resultado =  $this->insertarRespuestasSiPregunta($cursoId,$leccionId,$preguntaId,$respuestas);
                if($resultado->mensajeError=="")
                {
                    $resultado =  $this->insertarRespuestasSiCategoriasPregunta($cursoId,$leccionId,$preguntaId,$respuestas);
                    if($resultado->mensajeError=="")
                    {
                       $this->conexion->commit();
                    }
                }
                else
                    $this->conexion->rollback();
            }
            else
                $this->conexion->rollback();
        }
        else
            $this->conexion->rollback();
       
        return $resultado;
    }
    
    private function insertarRespuestasSiPregunta($cursoId,$leccionId, $preguntaId,$respuestas)
    {
        $resultado = new Resultado();
       
     
                
        for ($k = 0; $k< count($respuestas); $k++)
        {
            $respuesta = $respuestas[$k];
            
            $consulta = "INSERT INTO respuestas_si(plantilla_id, seccion_id, pregunta_id, id, texto, peso, hallazgo, recomendacion) " .
                "VALUE(?, ?, ?, ?, ?, ?, ?, ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param("iiiisiss",$cursoId,$leccionId, $preguntaId,$respuesta->id, $respuesta->texto,$respuesta->peso,$respuesta->hallazgo,$respuesta->recomendacion))
                {
                    if($sentencia->execute())
                    {
                        $sentencia->close();
                    }
                    else
                    {
                        $resultado->codigoError = $this->conexion->errno;
                        $resultado->mensajeError = "Falló la ejecución insertarRespuestasSiPregunta(" . $this->conexion->errno . ") " . $this->conexion->error;
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
    
    private function insertarRespuestasNoPregunta($cursoId,$leccionId, $preguntaId,$respuestas)
    {
        $resultado = new Resultado();
        
        
        
        for ($k = 0; $k< count($respuestas); $k++)
        {
            $respuesta = $respuestas[$k];
            
            $consulta = "INSERT INTO respuestas_no(plantilla_id, seccion_id, pregunta_id, id, texto, peso, hallazgo, recomendacion) " .
                "VALUE(?, ?, ?, ?, ?, ?, ?, ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param("iiiisiss",$cursoId,$leccionId, $preguntaId,$respuesta->id, $respuesta->texto,$respuesta->peso,$respuesta->hallazgo,$respuesta->recomendacion))
                {
                    if($sentencia->execute())
                    {
                        $sentencia->close();
                    }
                    else
                    {
                        $resultado->codigoError = $this->conexion->errno;
                        $resultado->mensajeError = "Falló la ejecución insertarRespuestasSiPregunta(" . $this->conexion->errno . ") " . $this->conexion->error;
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
    
    public function guardarRespuestasNo($cursoId, $leccionId, $preguntaId, $respuestas)
    {
        ini_set('max_execution_time', 300);
        $resultado = new Resultado();
        $this->conexion->autocommit(FALSE);
        
        //echo $cursoId."-".$leccionId."-".$preguntaId;
        
        $resultado = $this->eliminarRespuestasNoCategoriasPregunta($cursoId,$leccionId,$preguntaId);
        if($resultado->mensajeError=="")
        {
            $resultado = $this->eliminarRespuestasNoPregunta($cursoId,$leccionId,$preguntaId);
            if($resultado->mensajeError=="")
            {
                $resultado =  $this->insertarRespuestasNoPregunta($cursoId,$leccionId,$preguntaId,$respuestas);
                if($resultado->mensajeError=="")
                {
                    $resultado =  $this->insertarRespuestasNoCategoriasPregunta($cursoId,$leccionId,$preguntaId,$respuestas);
                    if($resultado->mensajeError=="")
                    {
                        $this->conexion->commit();
                    }
                }
                else
                    $this->conexion->rollback();
            }
            else
                $this->conexion->rollback();
        }
        else
            $this->conexion->rollback();
            
            return $resultado;
    }
    
    public function ordenarPreguntas($cursoId, $leccionId, $seleccion)
    {
        $resultado = new Resultado();
        
        $registros = explode("&", $seleccion);
        
        $this->conexion->autocommit(FALSE);
        
        $i = 1;
        foreach ($registros as $key => $value) 
        {
            $ides = explode("=", $value);
            //$database->SQLUpdate("bookmarks", "orden", $i, "id",(int)$ides[1]);
            
            $id = str_replace("preguntaDiv","",$ides[1]);
            
            //echo "valor=$id;";
            
            $consulta = " UPDATE cursos_preguntas " .
                "SET orden = ? " .
                "WHERE curso_id = ? AND leccion_id = ? AND id = ? ";
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if( $sentencia->bind_param("iiii", $i,$cursoId, $leccionId, $id))
                {
                    if($sentencia->execute())
                    {
                        $sentencia->close();
                    }
                    else
                    {
                        $resultado->mensajeError = "Falló la ejecución actualizar(" . $this->conexion->errno . ") " . $this->conexion->error;
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
                $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
                break;
            }
            
            $i++;
        }
        if($resultado->correcto())
            $this->conexion->commit();
         else
            $this->conexion->rollback();
                
        return $resultado;
    }
    
  
    public function calcularIdPregunta($cursoId, $leccionId, $campo)
    {
        $resultado = new Resultado();
        $consulta =  "SELECT IFNULL(MAX($campo),0)+1 AS id FROM cursos_preguntas WHERE curso_id = ? AND leccion_id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("ii", $cursoId, $leccionId))
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
    
    public function calcularIdLeccion($cursoId, $campo)
    {
        $resultado = new Resultado();
        $consulta =  "SELECT IFNULL(MAX($campo),0)+1 AS id FROM cursos_lecciones WHERE curso_id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("i", $cursoId))  
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
    
    public function ordenarLecciones($cursoId, $seleccion)
    {
        $resultado = new Resultado();
        
        $registros = explode("&", $seleccion);
        
        $this->conexion->autocommit(FALSE);
        
        $i = 1;
        
        $salida ="";
        foreach ($registros as $key => $value)
        {
            $ides = explode("=", $value);
            
            $id = str_replace("seccionDiv","",$ides[1]);
            
            $consulta = " UPDATE cursos_lecciones " .
                "SET orden = ? " .
                "WHERE curso_id = ? AND id = ? ";
            
           //$salida.= ";".$consulta . ";$i;$cursoId;$id";
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if( $sentencia->bind_param("iii", $i,$cursoId,$id))
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
    
    
}

