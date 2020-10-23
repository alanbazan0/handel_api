<?php
namespace php\repositorios;

use php\interfaces\ICursosRepositorio;
use php\modelos\Curso;
use php\modelos\Resultado;
use php\clases\Token;
use php\clases\Logger;

include "../interfaces/ICursosRepositorio.php";
include "../modelos/Curso.php";
include "RepositorioBase.php";
require_once("../clases/Resultado.php");
require_once("../clases/Token.php");
require_once("../clases/Logger.php");
require_once("UsuariosRepositorio.php");
require_once('../clases/AdministradorConexion.php');

class CursosRepositorio extends RepositorioBase implements ICursosRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = " SELECT C.id, IFNULL(C.titulo,''), IFNULL(C.descripcion,''), IFNULL(DATE_FORMAT(C.fecha_alta,'%d/%m/%Y %H:%i:%s'),'')fecha_alta, IFNULL(DATE_FORMAT(C.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'')fecha_modificacion, IFNULL(C.publicado,0), U.id, U.nombre, U.apellido, C.token,
                (SELECT count(*) FROM cursos_lecciones CL WHERE CL.curso_id = C.id) numero_lecciones, 0 numero_lecciones_terminadas
             FROM cursos C
                INNER JOIN usuarios U ON C.usuario_id = U.id ";
           
    }
    
    public function insertar($usuario,Curso $modelo)
    {
        $this->conexion->autocommit(FALSE);
        $resultado =  $this->calcularId("id","cursos");
        if($resultado->mensajeError=="")
        {
            $modelo->id = $resultado->valor;
            $consulta = "INSERT INTO cursos(id, titulo, descripcion, fecha_alta, fecha_modificacion, publicado, usuario_id, token) " .
                "VALUE(?, ?, ?,  NOW(), NOW(), 0, ?, ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                $token = Token::crear();
                if( $sentencia->bind_param("issis", $modelo->id, $modelo->titulo,$modelo->descripcion,$usuario->id, $token))
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
    
    public function insertarRespuesta($cursoId, $leccionId, $preguntaId)
    {
        $resultado =  $this->calcularIdRespuesta($cursoId, $leccionId, $preguntaId, "id");
        if($resultado->correcto())
        {
            $id =  $resultado->valor;
            $resultado =  $this->calcularIdRespuesta($cursoId, $leccionId,$preguntaId, "orden");
            if($resultado->correcto())
            {
                $orden =  $resultado->valor;
                $consulta = "INSERT INTO cursos_respuestas(curso_id, leccion_id, pregunta_id, id, orden,texto) " .
                    "VALUE(?, ?, ?, ?, ?, '')";
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if($sentencia->bind_param("iiiii",$cursoId,$leccionId, $preguntaId, $id, $orden))
                    {
                        if($sentencia->execute())
                        {
                            $sentencia->close();
                            $resultado->valor = $id;
                        }
                        else
                        {
                            $resultado->mensajeError = __FUNCTION__ ." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
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
    
    public function insertarRespuestaCorrecta($cursoId, $leccionId, $preguntaId)
    {
        $resultado =  $this->calcularIdRespuesta($cursoId, $leccionId, $preguntaId, "id");
        if($resultado->correcto())
        {
            $id =  $resultado->valor;
            $resultado =  $this->calcularIdRespuesta($cursoId, $leccionId,$preguntaId, "orden");
            if($resultado->correcto())
            {
                $orden =  $resultado->valor;
                $consulta = "INSERT INTO cursos_respuestas(curso_id, leccion_id, pregunta_id, id, orden,texto, correcta) " .
                    "VALUE(?, ?, ?, ?, ?, '', 1)";
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if($sentencia->bind_param("iiiii",$cursoId,$leccionId, $preguntaId, $id, $orden))
                    {
                        if($sentencia->execute())
                        {
                            $sentencia->close();
                            $resultado->valor = $id;
                        }
                        else
                        {
                            $resultado->mensajeError = __FUNCTION__ ." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
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
                    $resultado->mensajeError = __FUNCTION__. ". Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
                
            }
            else
                $resultado->mensajeError = __FUNCTION__. "Falló el enlace de parámetros";
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
        }
        return $resultado;
    }
    
    private function eliminarPreguntasEjecucion($cursoId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM usuarios_cursos_lecciones_preguntas WHERE curso_id = ?";
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
                    $resultado->mensajeError = __FUNCTION__.".Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
                
            }
            else
                $resultado->mensajeError = __FUNCTION__.".Falló el enlace de parámetros";
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = __FUNCTION__.".Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
        }
        return $resultado;
    }
    
    private function eliminarPreguntasUsuarioEjecucion($usuarioId, $cursoId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM usuarios_cursos_lecciones_preguntas WHERE usuario_id =  ? AND curso_id = ? ";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ii",$usuarioId,$cursoId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = __FUNCTION__.".Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
                
            }
            else
                $resultado->mensajeError = __FUNCTION__.".Falló el enlace de parámetros";
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = __FUNCTION__.".Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
        }
        return $resultado;
    }
    
    private function eliminarLeccionesEjecucion($cursoId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM usuarios_cursos_lecciones WHERE curso_id = ?";
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
                    $resultado->mensajeError = __FUNCTION__.". Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
                
            }
            else
                $resultado->mensajeError = __FUNCTION__.".Falló el enlace de parámetros";
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = __FUNCTION__.".Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
        }
        return $resultado;
    }
    
    private function eliminarLeccionesUsuarioEjecucion($usuarioId,$cursoId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM usuarios_cursos_lecciones WHERE usuario_id = ? AND curso_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ii",$usuarioId,$cursoId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = __FUNCTION__.". Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
                
            }
            else
                $resultado->mensajeError = __FUNCTION__.".Falló el enlace de parámetros";
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = __FUNCTION__.".Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
        }
        return $resultado;
    }
    
    private function eliminarCursosEjecucion($cursoId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM usuarios_cursos WHERE curso_id = ?";
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
                    $resultado->mensajeError = __FUNCTION__.". Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
                
            }
            else
                $resultado->mensajeError = __FUNCTION__.".Falló el enlace de parámetros";
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = __FUNCTION__.".Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
        }
        return $resultado;
    }
    
    
    private function eliminarCursoUsuarioEjecucion($usuarioId, $cursoId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM usuarios_cursos WHERE usuario_id = ? AND curso_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ii",$usuarioId,$cursoId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = __FUNCTION__.". Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
                
            }
            else
                $resultado->mensajeError = __FUNCTION__.".Falló el enlace de parámetros";
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = __FUNCTION__.".Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
        }
        return $resultado;
    }
    
  
    
    
    
    private function eliminarRespuestasLeccion($cursoId,$leccionId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM cursos_respuestas WHERE curso_id = ? AND leccion_id = ?";
        
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
    
    private function eliminarRespuestasPregunta($cursoId,$leccionId,$preguntaId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM cursos_respuestas WHERE curso_id = ? AND leccion_id = ? AND pregunta_id = ?";
        
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
    
    private function eliminarPreguntaEjecucion($cursoId,$leccionId,$preguntaId)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM usuarios_cursos_lecciones_preguntas WHERE curso_id = ? AND leccion_id = ? AND pregunta_id = ?";
        
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
    
//     private function token()
//     {
//         if (function_exists('com_create_token') === true)
//         {
//             return trim(com_create_token(), '{}');
//         }
        
//         return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
//     }
    
    
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
        
        $resultado = $this->eliminarRespuestasLeccion($cursoId,$leccionId);
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
        
        
        return $resultado;
    }
    
    private function eliminarPreguntasLeccionEjecucion($cursoId,$leccionId)
    {
        $resultado = new Resultado();
        
        $consulta ="DELETE FROM usuarios_cursos_lecciones_preguntas WHERE curso_id = ? and leccion_id = ?";
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
                            $resultado->mensajeError = __FUNCTION__. " Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                            break;
                        }
                        
                    }
                    else
                    {
                        $resultado->mensajeError = __FUNCTION__. " Falló el enlace de parámetros";
                        break;
                    }
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = __FUNCTION__. " Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
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
    
    public function actualizarValorRespuesta($cursoId, $leccionId, $preguntaId, $respuestaId, $campo, $valor)
    {
        $resultado = new Resultado();
        $consulta = " UPDATE cursos_respuestas " .
            "SET $campo = ? " .
            "WHERE curso_id = ? AND leccion_id = ? AND pregunta_id = ? AND id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("siiii", $valor, $cursoId, $leccionId, $preguntaId, $respuestaId ))
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
    
    public function actualizarValorRespuestaCorrecta($cursoId, $leccionId, $preguntaId, $respuestaId, $valor)
    {
        $resultado = new Resultado();
        $this->conexion->autocommit(FALSE);
        
        $consulta = " UPDATE cursos_respuestas " .
            "SET correcta = 0 " .
            "WHERE curso_id = ? AND leccion_id = ? AND pregunta_id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("iii", $cursoId, $leccionId, $preguntaId ))
            {
                if($sentencia->execute())
                {
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
        
            
       if($resultado->correcto())
       {
           $consulta = " UPDATE cursos_respuestas " .
               "SET correcta = ? " .
               "WHERE curso_id = ? AND leccion_id = ? AND pregunta_id = ? AND id = ? ";
           
           if($sentencia = $this->conexion->prepare($consulta))
           {
               if( $sentencia->bind_param("iiiii",$valor, $cursoId, $leccionId, $preguntaId, $respuestaId ))
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
       }
            
        if($resultado->correcto())
            $this->conexion->commit();
        else
            $this->conexion->rollback();
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
        
        
        
        $consulta =" SELECT C.id, IFNULL(C.titulo,''), IFNULL(C.descripcion,''), IFNULL(DATE_FORMAT(C.fecha_alta,'%d/%m/%Y %H:%i:%s'),'')fecha_alta, IFNULL(DATE_FORMAT(C.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'')fecha_modificacion, IFNULL(C.publicado,0), U.id, U.nombre, U.apellido, C.token,
                (SELECT count(*) FROM cursos_lecciones CL WHERE CL.curso_id = C.id) numero_lecciones, 0 numero_lecciones_terminadas, orden
             FROM cursos C
                INNER JOIN usuarios U ON C.usuario_id = U.id " .
        $where . " order by orden";
            
      
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombre, $descripcion, $fechaAlta, $fechaModificacion, $publicado, $usuarioId, $usuarioNombre, $usuarioApellido, $token,$numeroLecciones,$numeroLeccionesTerminadas, $orden))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre, $descripcion, $fechaAlta, $fechaModificacion, $publicado, $usuarioId, $usuarioNombre, $usuarioApellido,$token,$numeroLecciones,$numeroLeccionesTerminadas, $orden);
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
    
    public function consultarCriterio($criteriosSeleccion,$opcional)
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
        
        
        
        $consulta =" SELECT C.id, IFNULL(C.titulo,''), IFNULL(C.descripcion,''), IFNULL(DATE_FORMAT(C.fecha_alta,'%d/%m/%Y %H:%i:%s'),'')fecha_alta, IFNULL(DATE_FORMAT(C.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'')fecha_modificacion, IFNULL(C.publicado,0), U.id, U.nombre, U.apellido, C.token,
                (SELECT count(*) FROM cursos_lecciones CL WHERE CL.curso_id = C.id) numero_lecciones, 0 numero_lecciones_terminadas
             FROM cursos C
                INNER JOIN usuarios U ON C.usuario_id = U.id " .
                $where . " order by titulo";
                
                
                
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if($this->bind_param($sentencia, $filtros))
                    {
                        if($sentencia->execute())
                        {
                            if ($sentencia->bind_result($id, $nombre, $descripcion, $fechaAlta, $fechaModificacion, $publicado, $usuarioId, $usuarioNombre, $usuarioApellido, $token,$numeroLecciones,$numeroLeccionesTerminadas))
                            {
                                while($row = $sentencia->fetch())
                                {
                                    $registro = $this->crearRegistro($id, $nombre, $descripcion, $fechaAlta, $fechaModificacion, $publicado, $usuarioId, $usuarioNombre, $usuarioApellido,$token,$numeroLecciones,$numeroLeccionesTerminadas);
                                    array_push($registros,$registro);
                                }
                                if($opcional=="true")
                                {
                                    $registro = $this->crearRegistro("", "Todas las capacitaciones",null, null, null, null, null, null, null,null,null,null);
                                    array_unshift($registros, $registro);
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
    
    
    public function consultarCursosContestando($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        //$filtros = array();
        $and="";
//         if($criteriosSeleccion!=null)
//         {
//             if(isset($criteriosSeleccion->titulo))
//             {
//                 if($criteriosSeleccion->titulo!="")
//                 {
//                     array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'C','campo'=>'titulo','valor'=>$criteriosSeleccion->titulo]);
//                 }
//             }
//             $and= $this->and($filtros);
//         }
        
        $consulta = "SELECT C.id, IFNULL(C.titulo,''), IFNULL(C.descripcion,''), IFNULL(DATE_FORMAT(C.fecha_alta,'%d/%m/%Y %H:%i:%s'),'')fecha_alta, IFNULL(DATE_FORMAT(C.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'')fecha_modificacion, IFNULL(C.publicado,0), U.id, U.nombre, U.apellido, C.token,
                    (SELECT count(*) FROM cursos_lecciones CL WHERE CL.curso_id = C.id) numero_lecciones, 
                     (SELECT count(*) FROM usuarios_cursos_lecciones UCL WHERE UCL.curso_id = C.id AND UCL.usuario_id = ? AND UCL.terminado = 1) numero_lecciones_terminadas,
                     (SELECT sum(tiempo_estimado) FROM cursos_lecciones CL WHERE CL.curso_id = C.id) tiempo_estimado
                        FROM cursos C
                    INNER JOIN usuarios U ON C.usuario_id = U.id 
                    INNER JOIN usuarios_cursos UC ON UC.curso_id = C.id AND UC.usuario_id = ?
                    WHERE C.id IN(SELECT curso_id FROM usuarios_cursos UC WHERE UC.usuario_id = ? AND UC.terminado=0) " ;
                    
        $consulta.= $and . " order by orden";
        
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("iii", $usuario->id, $usuario->id, $usuario->id))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombre, $descripcion, $fechaAlta, $fechaModificacion, $publicado, $usuarioId, $usuarioNombre, $usuarioApellido, $token,$numeroLecciones,$numeroLeccionesTerminadas, $tiempoEstimado))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre, $descripcion, $fechaAlta, $fechaModificacion, $publicado, $usuarioId, $usuarioNombre, $usuarioApellido,$token,$numeroLecciones,$numeroLeccionesTerminadas, $tiempoEstimado);
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
    
    public function consultarAvanceUsuario($usuario)
    {
        $resultado = new Resultado();
       
        
//         $consulta = "SELECT 
//                         (
//                             SELECT count(*)	
//                             FROM cursos C
//                             WHERE  ? IN(SELECT perfil_id FROM cursos_perfiles CP WHERE CP.curso_id = C.id) AND publicado = 1
//                         )total,
//                         (
//                             SELECT count(*)
//                             FROM cursos C
//                             WHERE C.id IN(SELECT curso_id 
//                                         FROM usuarios_cursos UC
//                                             INNER JOIN cursos C1 ON UC.curso_id = C1.id   
//                                         WHERE UC.usuario_id = ? AND UC.terminado=1 AND C1.publicado = 1)
//                         )terminados" ;
        
        $consulta = "SELECT
                        (
                            SELECT count(*)
                            FROM cursos C
                                INNER JOIN cursos_preguntas CPR ON CPR.curso_id = C.id
                            WHERE  ? IN(SELECT perfil_id FROM cursos_perfiles CP WHERE CP.curso_id = C.id) AND C.publicado = 1
                        )total,
                        (
                           SELECT count(*)
                            FROM usuarios_cursos_lecciones_preguntas P
                            	INNER JOIN cursos_respuestas R ON R.curso_id = P.curso_id AND R.leccion_id = P.leccion_id AND R.pregunta_id = P.pregunta_id AND R.id = P.respuesta_id
                                INNER JOIN cursos C ON R.curso_id = C.id
                            WHERE P.usuario_id = ? AND C.publicado = 1
                        )terminados" ;
        
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ii",  $usuario->perfilId, $usuario->id))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($total,$terminados))
                    {
                        if($row = $sentencia->fetch())
                        {
                            $registro= (object) [
                                'total' =>  $total,
                                'terminados' => $terminados,
                                
                            ];
                            
                            $this->calcularPorcentaje($registro,'terminados','total',"porcentaje");
                            
                            $resultado->valor = $registro;
                        }
                        
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__." Falló el enlace del resultado.";
                }
                else
                    $resultado->mensajeError =__FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__." Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = __FUNCTION__." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
        return $resultado;
    }
    
    public function consultarAprovechamientoUsuario($usuario)
    {
        $resultado = new Resultado();
        
        
//         $consulta = "SELECT
//                         (
//                             SELECT count(*)
//                             FROM cursos C
//                                 INNER JOIN cursos_preguntas CPR ON CPR.curso_id = C.id 
//                             WHERE  ? IN(SELECT perfil_id FROM cursos_perfiles CP WHERE CP.curso_id = C.id)  AND C.publicado = 1
//                         )total,
//                         (
//                            SELECT count(*)
//                             FROM usuarios_cursos_lecciones_preguntas P
//                             	INNER JOIN cursos_respuestas R ON R.curso_id = P.curso_id AND R.leccion_id = P.leccion_id AND R.pregunta_id = P.pregunta_id AND R.id = P.respuesta_id
//                                 INNER JOIN cursos C ON R.curso_id = C.id
//                             WHERE P.usuario_id = ? 
//                                 AND R.correcta=1
//                                 AND C.publicado = 1
//                         )correctas" ;
        
        
        $consulta = "SELECT
                        (
                        	SELECT count(*)
                        	FROM cursos_preguntas CPR 
                        		INNER JOIN cursos C ON CPR.curso_id = C.id 
                                INNER JOIN usuarios_cursos UC ON UC.curso_id = C.id AND UC.usuario_id= ?
                        	WHERE C.publicado = 1
                        		AND UC.terminado = 1
                        )total,
                        (
                           SELECT count(*)
                        	FROM usuarios_cursos_lecciones_preguntas P
                        		INNER JOIN cursos_respuestas R ON R.curso_id = P.curso_id AND R.leccion_id = P.leccion_id AND R.pregunta_id = P.pregunta_id AND R.id = P.respuesta_id
                        		INNER JOIN cursos C ON R.curso_id = C.id
                                INNER JOIN usuarios_cursos UC ON UC.curso_id = C.id AND UC.usuario_id= P.usuario_id
                        	WHERE P.usuario_id = ? 
                        		AND R.correcta=1
                        		AND C.publicado = 1
                                AND UC.terminado = 1 
                                
                        )correctas" ;
        
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ii",$usuario->id, $usuario->id))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($total,$correctas))
                    {
                        if($row = $sentencia->fetch())
                        {
                            $registro= (object) [
                                'total' =>  $total,
                                'correctas' => $correctas,
                                
                            ];
                            
                            $this->calcularPorcentaje($registro,'correctas','total',"porcentaje");
                            
                            $resultado->valor = $registro;
                        }
                        
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__." Falló el enlace del resultado.";
                }
                else
                    $resultado->mensajeError =__FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__." Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = __FUNCTION__." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
            return $resultado;
    }
    
    public function consultarVideosVistosUsuario($usuario)
    {
        $resultado = new Resultado();
        
        
        $consulta = "SELECT(
                            SELECT count(*)
                            FROM cursos C
                                INNER JOIN cursos_lecciones CL ON CL.curso_id = C.id 
                            WHERE  ? IN(SELECT perfil_id FROM cursos_perfiles CP WHERE CP.curso_id = C.id)
                        )total, count(*)vistos, SUM(duracion) minutos
                    FROM usuarios_cursos_lecciones L
                    WHERE L.usuario_id = ? 
                        AND L.visto=1" ;
        
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ii",  $usuario->perfilId,$usuario->id))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($total,$vistos,$minutos))
                    {
                        if($row = $sentencia->fetch())
                        {
                            $registro= (object) [
                                'total' =>  $total,
                                'vistos' =>  $vistos,
                                'minutos' => $minutos,
                                
                            ];
                            
                            $this->calcularPorcentaje($registro,'vistos','total',"porcentaje");
                            
                            $resultado->valor = $registro;
                        }
                        
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__." Falló el enlace del resultado.";
                }
                else
                    $resultado->mensajeError =__FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__." Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = __FUNCTION__." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
            return $resultado;
    }
    
    public function consultarDiasCapacitacionUsuario($usuario)
    {
        $resultado = new Resultado();
        
        
        $consulta = "SELECT DATEDIFF(NOW(),UC.fecha_modificacion) diasUltimaCapacitacion,DATE_FORMAT(NOW(),'%d') diasMes,DATE_FORMAT(UC.fecha_modificacion,'%d')diaUltima,DATE_FORMAT(UC.fecha_modificacion,'%m')mesUltima,DATE_FORMAT(UC.fecha_modificacion,'%Y')anoUltima,DATE_FORMAT(NOW(),'%m')mesActual,DATE_FORMAT(NOW(),'%Y')anoActual
                    FROM usuarios_cursos UC
                    WHERE UC.usuario_id = ? 
                    ORDER BY UNIX_TIMESTAMP(UC.fecha_modificacion) desc
                    LIMIT 1 " ;
        
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i", $usuario->id))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($diasUltimaCapacitacion,$diasMes,$diaUltima,$mesUltima,$anoUltima, $mesActual,$anoActual))
                    {
                        if($row = $sentencia->fetch())
                        {
                            $registro= (object) [
                                'diasUltimaCapacitacion' =>  $diasUltimaCapacitacion,
                              
                            ];
                            
                            if($registro->diasUltimaCapacitacion==0)
                            {
                                $registro->diasMesSinCapacitacion=0;
                            }
                            else if($mesUltima == $mesActual && $anoUltima== $anoActual)
                            {
                                $registro->diasMesSinCapacitacion= $diasUltimaCapacitacion;
                            }
                            else
                                $registro->diasMesSinCapacitacion = $diasMes;
                            
                            $date = new \DateTime('now');
                            $date->modify('last day of this month');
                            $registro->total = $date->format('d');
                            $this->calcularPorcentaje($registro,'diasMesSinCapacitacion','total',"porcentaje");
                            
                            $resultado->valor = $registro;
                        }
                        
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__." Falló el enlace del resultado.";
                }
                else
                    $resultado->mensajeError =__FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__." Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = __FUNCTION__." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
            return $resultado;
    }
    
    private function calcularPorcentaje(&$registro,$campo,$campoTotal,$campoPorcentaje)
    {
        $total = $registro->$campoTotal;
        $cumplido =$registro->$campo;
        $registro->$campoPorcentaje  = 0;
        if($total!=0)
        {
            $registro->$campoPorcentaje = $cumplido  * 100 / $total;
            
            $registro->$campoPorcentaje = bcdiv($registro->$campoPorcentaje, '1', 1);
            
            list($enteros, $decimales) = explode(".", $registro->$campoPorcentaje);
            if($decimales=="0")
                $registro->$campoPorcentaje = str_replace(".$decimales","",$registro->$campoPorcentaje);
                
        }
        else
            $registro->$campoPorcentaje = 0;
    }
    
    
    public function consultarCursosPendientes($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        $filtros = array();
        $and="";
        if($criteriosSeleccion!=null)
        {
            if(isset($criteriosSeleccion->titulo))
            {
                if($criteriosSeleccion->titulo!="")
                {
                    array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'C','campo'=>'titulo','valor'=>$criteriosSeleccion->titulo]);
                }
            }
            $and= $this->and($filtros);
        }
        
        $consulta = "SELECT C.id, IFNULL(C.titulo,''), IFNULL(C.descripcion,''), IFNULL(DATE_FORMAT(C.fecha_alta,'%d/%m/%Y %H:%i:%s'),'')fecha_alta, IFNULL(DATE_FORMAT(C.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'')fecha_modificacion, IFNULL(C.publicado,0), U.id, U.nombre, U.apellido, C.token,
                    (SELECT count(*) FROM cursos_lecciones CL WHERE CL.curso_id = C.id) numero_lecciones,
                     (SELECT count(*) FROM usuarios_cursos_lecciones UCL WHERE UCL.curso_id = C.id AND UCL.usuario_id = ?  AND UCL.terminado = 1) numero_lecciones_terminadas,
                    (SELECT sum(tiempo_estimado) FROM cursos_lecciones CL WHERE CL.curso_id = C.id) tiempo_estimado
                        FROM cursos C
                    INNER JOIN usuarios U ON C.usuario_id = U.id
                    WHERE C.id NOT IN(SELECT curso_id FROM usuarios_cursos UC WHERE UC.usuario_id = ?) 
                            AND ? IN(SELECT perfil_id FROM cursos_perfiles CP WHERE CP.curso_id = C.id) 
                            AND C.publicado = 1 " ;
        
        $consulta.= $and . " order by orden";
        
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("iii",  $usuario->id, $usuario->id ,$usuario->perfilId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombre, $descripcion, $fechaAlta, $fechaModificacion, $publicado, $usuarioId, $usuarioNombre, $usuarioApellido, $token,$numeroLecciones,$numeroLeccionesTerminadas,$tiempoEstimado))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre, $descripcion, $fechaAlta, $fechaModificacion, $publicado, $usuarioId, $usuarioNombre, $usuarioApellido,$token,$numeroLecciones,$numeroLeccionesTerminadas,$tiempoEstimado);
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
    
    public function consultarCursosTerminados($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        //$filtros = array();
        //$and="";
//         if($criteriosSeleccion!=null)
//         {
//             if(isset($criteriosSeleccion->titulo))
//             {
//                 if($criteriosSeleccion->titulo!="")
//                 {
//                     array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'C','campo'=>'titulo','valor'=>$criteriosSeleccion->titulo]);
//                 }
//             }
//             $and= $this->and($filtros);
//         }
        
        $consulta = "SELECT C.id, IFNULL(C.titulo,''), IFNULL(C.descripcion,''), IFNULL(DATE_FORMAT(C.fecha_alta,'%d/%m/%Y %H:%i:%s'),'')fecha_alta, IFNULL(DATE_FORMAT(C.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'')fecha_modificacion, IFNULL(C.publicado,0), U.id, U.nombre, U.apellido, C.token,
                    (SELECT count(*) FROM cursos_lecciones CL WHERE CL.curso_id = C.id) numero_lecciones,  
                     (SELECT count(*) FROM usuarios_cursos_lecciones UCL WHERE UCL.curso_id = C.id AND UCL.usuario_id = ?  AND UCL.terminado = 1) numero_lecciones_terminadas
                        FROM cursos C
                    INNER JOIN usuarios U ON C.usuario_id = U.id
                    INNER JOIN usuarios_cursos UC ON UC.curso_id = C.id AND UC.usuario_id =  ?
                    WHERE C.id IN(SELECT curso_id FROM usuarios_cursos UC WHERE UC.usuario_id = ? AND UC.terminado=1) 
                    ORDER  BY orden";
        
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("iii", $usuario->id,$usuario->id,$usuario->id))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombre, $descripcion, $fechaAlta, $fechaModificacion, $publicado, $usuarioId, $usuarioNombre, $usuarioApellido, $token,$numeroLecciones,$numeroLeccionesTerminadas))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre, $descripcion, $fechaAlta, $fechaModificacion, $publicado, $usuarioId, $usuarioNombre, $usuarioApellido,$token,$numeroLecciones,$numeroLeccionesTerminadas);
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
    
    
    
    public function consultarPorLlaves($usuario,$llaves)
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
                    if ($sentencia->bind_result($id, $nombre, $descripcion, $fechaAlta, $fechaModificacion, $publicado, $usuarioId, $usuarioNombre, $usuarioApellido,$token,$numeroLecciones,$numeroLeccionesTerminadas))
                    {
                        if($sentencia->fetch())
                        {
                            $curso = $this->crearRegistro($id, $nombre, $descripcion, $fechaAlta, $fechaModificacion, $publicado, $usuarioId, $usuarioNombre, $usuarioApellido, $token,$numeroLecciones,$numeroLeccionesTerminadas);
                            
                            $resultado->valor = $curso;
                            
                            $sentencia->close();
                            
                            $resultadoSecciones = $this->consultarLecciones($usuario,$curso->id);
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
    
    public function consultarPorTokenSinPreguntas($usuario,$token)
    {
        $resultado = $this->consultarPorToken($usuario,$token);
        if($resultado->correcto())
        {
            $curso = $resultado->valor;
            for($i=0; $i < count($curso->lecciones);$i++)
            {
                $leccion = $curso->lecciones[$i];
                $leccion->preguntas = null;
            }
        }
        return $resultado;
    }
    
    
    public function terminarLeccion($usuarioId, $cursoId, $leccionId)
    {
        $resultado = new Resultado();
            $consulta = "UPDATE usuarios_cursos_lecciones 
                SET fecha_final = NOW(), terminado = 1,  fecha_modificacion = NOW()
                WHERE usuario_id=? AND curso_id = ? AND leccion_id = ?";
            
            
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("iii",$usuarioId,$cursoId, $leccionId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = __FUNCTION__ . " Falló la ejecución update (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
            }
            else
            {
                $resultado->mensajeError = __FUNCTION__ . " Falló el enlace de parámetros update";
            }
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = __FUNCTION__ . " Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
        }
            
    
        return  $resultado;
    }
    
    public function terminarCurso($usuarioId, $cursoId)
    {
        $resultado = new Resultado();
        $consulta = "UPDATE usuarios_cursos
                SET fecha_final = NOW(), terminado = 1, fecha_modificacion= NOW()
                WHERE usuario_id=? AND curso_id = ?";
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ii",$usuarioId,$cursoId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = __FUNCTION__ . " Falló la ejecución update (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
            }
            else
            {
                $resultado->mensajeError = __FUNCTION__ . " Falló el enlace de parámetros update";
            }
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = __FUNCTION__ . " Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
        }
        
        
        return  $resultado;
    }
    
    public function actualizarCurso($usuarioId, $cursoId)
    {
        $resultado = new Resultado();
        $consulta = "UPDATE usuarios_cursos
                SET fecha_modificacion= NOW()
                WHERE usuario_id=? AND curso_id = ?";
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ii",$usuarioId,$cursoId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = __FUNCTION__ . " Falló la ejecución update (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
            }
            else
            {
                $resultado->mensajeError = __FUNCTION__ . " Falló el enlace de parámetros update";
            }
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = __FUNCTION__ . " Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
        }
        
        
        return  $resultado;
    }
    
    
    public function guardarLeccionUsuario($usuario, $cursoId, $leccionId, $duracion)
    {
        $resultado = new Resultado();
        $this->conexion->autocommit(FALSE);
        $resultado = $this->guardarCursoUsuario($usuario, $cursoId, $leccionId);
        
        if($resultado->correcto())
        {

            $consulta = "UPDATE usuarios_cursos_lecciones 
                SET fecha_modificacion = NOW(),
                     duracion = ? 
                WHERE usuario_id=? AND curso_id = ? AND leccion_id = ?";
            
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param("siii",$duracion,$usuario->id,$cursoId, $leccionId))
                {
                    if($sentencia->execute())
                    {
                        $sentencia->close();
                        
                        $resultado = $this->numeroRegistrosLeccionesUsuario($usuario->id,$cursoId,$leccionId);
                        if($resultado->correcto())
                        {
                            $count = $resultado->valor;
                            
                            if($count==0)
                            {
                                $consulta = "INSERT INTO usuarios_cursos_lecciones(usuario_id,curso_id, leccion_id, fecha_inicial, fecha_modificacion, terminado, duracion) " .
                                    "VALUE(?, ?, ?,  NOW(), NOW(), 0, ?)";
                                if($sentencia = $this->conexion->prepare($consulta))
                                {
                                    if($sentencia->bind_param("iiis",$usuario->id,$cursoId,$leccionId, $duracion))
                                    {
                                        if($sentencia->execute())
                                        {
                                            $sentencia->close();
                                        }
                                        else
                                        {
                                            $resultado->codigoError = $this->conexion->errno;
                                            $resultado->mensajeError = __FUNCTION__ . " Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                                        }
                                    }
                                    else
                                    {
                                        $resultado->mensajeError = __FUNCTION__ . "Falló el enlace de parámetros";
                                    }
                                }
                                else
                                {
                                    $resultado->codigoError = $this->conexion->errno;
                                    $resultado->mensajeError = __FUNCTION__ . "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
                                }
                            }
                            
                        }
                        
                    }
                    else
                    {
                        $resultado->codigoError = $this->conexion->errno;
                        $resultado->mensajeError = __FUNCTION__ . "Falló la ejecución update (" . $this->conexion->errno . ") " . $this->conexion->error;
                    }
                }
                else
                {
                    $resultado->mensajeError = "Falló el enlace de parámetros update";
                }
            }
            else
            {
                $resultado->codigoError = $this->conexion->errno;
                $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            
            
        }
        if($resultado->correcto())
            $this->conexion->commit();
        else
            $this->conexion->rollback();
       return  $resultado;
    }
    
    public function guardarPreguntaUsuario($usuario, $cursoId, $leccionId, $preguntaId, $respuestaId)
    {
//         Logger::log("guardarPreguntaUsuario",  "-----------------------------------------------------------------------");
//         Logger::log("guardarPreguntaUsuario",  "Guardando pregunta del usuario");
//         Logger::log("guardarPreguntaUsuario",  "usuarioId: $usuario->id; cursoId: $cursoId, leccionId: $leccionId, preguntaId: $preguntaId, respuestaId: $respuestaId;");
        
        $resultado = new Resultado();
        $this->conexion->autocommit(FALSE);
        
        $consulta = "INSERT INTO usuarios_cursos_lecciones_preguntas(usuario_id,curso_id, leccion_id, pregunta_id, respuesta_id, fecha_alta) " .
            "VALUE(?, ?, ?, ?, ?,  NOW())";
        if($sentencia = $this->conexion->prepare($consulta))
        {
//            Logger::log("guardarPreguntaUsuario",  "enlazando parametros: usuarioId: $usuario->id; cursoId: $cursoId, leccionId: $leccionId, preguntaId: $preguntaId, respuestaId: $respuestaId;");
            if($sentencia->bind_param("iiiii",$usuario->id,$cursoId,$leccionId,$preguntaId,$respuestaId))
            {
//                 Logger::log("guardarPreguntaUsuario",  "parametros enlazados: usuarioId: $usuario->id; cursoId: $cursoId, leccionId: $leccionId, preguntaId: $preguntaId, respuestaId: $respuestaId;");
//                 Logger::log("guardarPreguntaUsuario",  "insertando pregunta: usuarioId: $usuario->id; cursoId: $cursoId, leccionId: $leccionId, preguntaId: $preguntaId, respuestaId: $respuestaId;");
                if($sentencia->execute())
                {
//                     Logger::log("guardarPreguntaUsuario",  "inserto pregunta");
                    $sentencia->close();
                    
//                     Logger::log("guardarPreguntaUsuario",  "insertando pregunta: usuarioId: $usuario->id; cursoId: $cursoId");
                    $resultado = $this->actualizarCurso($usuario->id,$cursoId);
                    if($resultado->correcto())
                    {
//                         Logger::log("guardarPreguntaUsuario",  "actualizo curso");
//                         Logger::log("guardarPreguntaUsuario",  "calculando numero de preguntas restantes: usuarioId: $usuario->id; cursoId: $cursoId, leccionId: $leccionId");
                        $resultado = $this->calcularNumeroPreguntasRestantes($usuario->id,$cursoId,$leccionId);
                        if($resultado->correcto())
                        {
                            $numeroPreguntasRestantes= $resultado->valor;
//                             Logger::log("guardarPreguntaUsuario",  "numero de preguntas restantes = $numeroPreguntasRestantes");
                            if($numeroPreguntasRestantes<=0)
                            {
//                                 Logger::log("guardarPreguntaUsuario",  "terminando leccion: usuarioId: $usuario->id; cursoId: $cursoId, leccionId: $leccionId");
                                $resultado = $this->terminarLeccion($usuario->id, $cursoId, $leccionId);
                                if($resultado->correcto())
                                {
//                                     Logger::log("guardarPreguntaUsuario",  "LECCION TERMINADA: usuarioId: $usuario->id; cursoId: $cursoId, leccionId: $leccionId");
//                                     Logger::log("guardarPreguntaUsuario",  "calculando numero de lecciones restantes: usuarioId: $usuario->id; cursoId: $cursoId");
                                    $resultado = $this->calcularNumeroLeccionesRestantes($usuario->id,$cursoId);
                                    if($resultado->correcto())
                                    {
                                        $numeroLeccionesRestantes= $resultado->valor;
//                                         Logger::log("guardarPreguntaUsuario",  "numero de lecciones restantes = $numeroLeccionesRestantes");
                                        if($numeroLeccionesRestantes<=0)
                                        {
//                                             Logger::log("guardarPreguntaUsuario",  "terminando curso: usuarioId: $usuario->id; cursoId: $cursoId");
                                            $resultado = $this->terminarCurso($usuario->id, $cursoId);
                                            if($resultado->correcto())
                                            {
//                                                 Logger::log("guardarPreguntaUsuario",  "CURSO TERMINADO: usuarioId: $usuario->id; cursoId: $cursoId");
                                                
                                            }
                                            else
                                            {
//                                                 Logger::log("guardarPreguntaUsuario",  "ERROR: No termino el curso: $resultado->mensajeError");
                                            }
                                                
                                        }
                                        else 
                                        {
//                                             Logger::log("guardarPreguntaUsuario",  "Curso aun no termina, quedan lecciones, numero de lecciones restantes = $numeroLeccionesRestantes");
                                            
                                        }
                                    }
                                    else
                                    {
//                                         Logger::log("guardarPreguntaUsuario",  "ERROR: No se pudo calcular el numero de lecciones restantes: $resultado->mensajeError");
                                    }
                                }
                                 else
                                 {
//                                      Logger::log("guardarPreguntaUsuario",  "ERROR: No termino la leccion: $resultado->mensajeError");
                                 }
                                     
                                
                            }
                            else
                            {
//                                 Logger::log("guardarPreguntaUsuario",  "Leccion aun no termina, quedan preguntas, numero de preguntas restantes = $numeroPreguntasRestantes");
                                
                            }
                        }
                        else
                        {
//                             Logger::log("guardarPreguntaUsuario",  "ERROR: No se pudo calcular el numero de preguntas restantes: $resultado->mensajeError");
                        }
                    }
                    else
                    {
//                         Logger::log("guardarPreguntaUsuario",  "ERROR: No se actualizo el curso: $resultado->mensajeError");
                    }
                    
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = __FUNCTION__ . " Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
//                     Logger::log("guardarPreguntaUsuario",  "ERROR: $resultado->mensajeError");
                }
            }
            else
            {
                $resultado->mensajeError = __FUNCTION__ . "Falló el enlace de parámetros";
//                 Logger::log("guardarPreguntaUsuario",  "ERROR: $resultado->mensajeError");
                
            }
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = __FUNCTION__ . "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
//             Logger::log("guardarPreguntaUsuario",  "ERROR: $resultado->mensajeError");
            
        }
        
        if($resultado->correcto())
        {
            $this->conexion->commit();
//             Logger::log("guardarPreguntaUsuario",  "commit pregunta: usuarioId: $usuario->id; cursoId: $cursoId, leccionId: $leccionId, preguntaId: $preguntaId, respuestaId: $respuestaId;");
        }
        else
        {
            $this->conexion->rollback();
//             Logger::log("guardarPreguntaUsuario",  "rollback pregunta: usuarioId: $usuario->id; cursoId: $cursoId, leccionId: $leccionId, preguntaId: $preguntaId, respuestaId: $respuestaId;");
            
        }
        return  $resultado;
    }
    
    public function guardarCursoUsuario($usuario, $cursoId)
    {
        $resultado = new Resultado();
        $consulta = "UPDATE usuarios_cursos " .
            "SET fecha_modificacion = NOW() ".
            "WHERE usuario_id=? AND curso_id = ?";
            
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param("ii",$usuario->id,$cursoId))
                {
                    if($sentencia->execute())
                    {
                        $sentencia->close();
                        
                        
                        $resultado = $this->numeroRegistrosCursosUsuario($usuario->id,$cursoId);
                        if($resultado->mensajeError=="")
                        {
                            $count = $resultado->valor;
                            
                            if($count==0)
                            {
                                $consulta = "INSERT INTO usuarios_cursos(usuario_id,curso_id, fecha_inicial, token, terminado, fecha_modificacion) " .
                                    "VALUE(?, ?, NOW(), ?, 0, NOW())";
                                if($sentencia = $this->conexion->prepare($consulta))
                                {
                                    $token = Token::crear();
                                    if($sentencia->bind_param("iis",$usuario->id,$cursoId,$token))
                                    {
                                        if($sentencia->execute())
                                        {
                                            $sentencia->close();
                                        }
                                        else
                                        {
                                            $resultado->codigoError = $this->conexion->errno;
                                            $resultado->mensajeError = __FUNCTION__. " Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                                        }
                                    }
                                    else
                                    {
                                        $resultado->mensajeError = __FUNCTION__. " Falló el enlace de parámetros";
                                    }
                                }
                                else
                                {
                                    $resultado->codigoError = $this->conexion->errno;
                                    $resultado->mensajeError =__FUNCTION__. " Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
                                }
                            }
                            
                        }
                        
                        
                    }
                    else
                    {
                        $resultado->codigoError = $this->conexion->errno;
                        $resultado->mensajeError = __FUNCTION__. " Falló la ejecución update (" . $this->conexion->errno . ") " . $this->conexion->error;
                    }
                }
                else
                {
                    $resultado->mensajeError = __FUNCTION__. " Falló el enlace de parámetros update";
                }
            }
            else
            {
                $resultado->codigoError = $this->conexion->errno;
                $resultado->mensajeError = __FUNCTION__. " Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
        return $resultado;
    }
    
    function numeroRegistrosCursosUsuario($usuarioId, $cursoId)
    {
        $resultado = new Resultado();
        $consulta =  "SELECT COUNT(*) id FROM usuarios_cursos WHERE usuario_id=? AND curso_id = ?";
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ii",$usuarioId,$cursoId))
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
    
    function numeroRegistrosLeccionesUsuario($usuarioId, $cursoId, $leccionId)
    {
        $resultado = new Resultado();
        $consulta =  "SELECT COUNT(*) id FROM usuarios_cursos_lecciones WHERE usuario_id = ? AND curso_id = ? AND leccion_id = ?";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("iii",$usuarioId,$cursoId,$leccionId))
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
    
//     public function guardarPregunta($usuario, $cursoId, $leccionId, $preguntaId, $respuestaId)
//     {
//         $this->conexion->autocommit(FALSE);
//         $resultado = $this->guardarLeccion($usuario, $cursoId, $leccionId);
//         if($resultado->correcto())
//         {
            
//         }
//         if($resultado->correcto())
//             $this->conexion->commit();
//         else
//             $this->conexion->rollback();
//     }
    
//     public function guardarLeccion($usuario, $cursoId, $leccionId)
//     {
//         $consulta = "UPDATE usuarios_cursos_lecciones " .
//             "SET valor = ?,  puntos = ?, puntos_total = ?, porcentaje = ? ".
//             "WHERE usuario_id=? AND curso_id = ? AND leccion_id";
        
            
//         if($sentencia = $this->conexion->prepare($consulta))
//         {
//             if($sentencia->bind_param("siisiiii",$pregunta->valor,$pregunta->puntos, $pregunta->puntosTotal, $pregunta->porcentaje,$auditoriaId,$plantillaId,$seccionId, $pregunta->id))
//             {
//                 if($sentencia->execute())
//                 {
                    
//                     //$count = $sentencia->affected_rows;
//                     $sentencia->close();
                    
//                     $resultado = $this->numeroRegistros($plantillaId,$auditoriaId,$seccionId, $pregunta->id);
//                     if($resultado->mensajeError=="")
//                     {
//                         $count = $resultado->valor;
                        
                        
//                         if($count==0)
//                         {
//                             $consulta = "INSERT INTO auditoria_preguntas(auditoria_id, plantilla_id, seccion_id, pregunta_id, valor, puntos, puntos_total, porcentaje) " .
//                                 "VALUE(?, ?, ?, ?, ?, ?, ?, ?)";
//                             if($sentencia = $this->conexion->prepare($consulta))
//                             {
//                                 if($sentencia->bind_param("iiiisiis",$auditoriaId,$plantillaId,$seccionId, $pregunta->id, $pregunta->valor,$pregunta->puntos, $pregunta->puntosTotal, $pregunta->porcentaje))
//                                 {
//                                     if($sentencia->execute())
//                                     {
//                                         $sentencia->close();
//                                     }
//                                     else
//                                     {
//                                         $resultado->codigoError = $this->conexion->errno;
//                                         $resultado->mensajeError = "Falló la ejecución insertarPreguntas(" . $this->conexion->errno . ") " . $this->conexion->error;
//                                         break;
//                                     }
//                                 }
//                                 else
//                                 {
//                                     $resultado->mensajeError = "Falló el enlace de parámetros";
//                                 }
//                             }
//                             else
//                             {
//                                 $resultado->codigoError = $this->conexion->errno;
//                                 $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
//                             }
//                         }
//                     }
                    
//                 }
//                 else
//                 {
//                     $resultado->codigoError = $this->conexion->errno;
//                     $resultado->mensajeError = "Falló la ejecución update insertarPreguntas(" . $this->conexion->errno . ") " . $this->conexion->error;
//                 }
//             }
//             else
//             {
//                 $resultado->mensajeError = "Falló el enlace de parámetros update";
//             }
//         }
//         else
//         {
//             $resultado->codigoError = $this->conexion->errno;
//             $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
//         }
            
//     }

    public function actualizarDuracionLeccion($usuario, $cursoId, $leccionId, $duracion)
    {
        $resultado = new Resultado();
        
        $consulta = " UPDATE usuarios_cursos_lecciones " .
            "SET duracion = ?, " .
            "  fecha_modificacion= NOW() " .
            "WHERE usuario_id = ? AND curso_id = ? AND leccion_id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("iiii", $duracion,$usuario->id,$cursoId,$leccionId ))
            {
                if($sentencia->execute())
                {
                    $resultado->valor=true;
                    $sentencia->close();
                    
                    $resultado = $this->actualizarCurso($usuario->id,$cursoId);
                    if($resultado->correcto())
                    {
                        
                    }
                    
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
    
    public function consultarPreguntaAleatoria($usuario, $cursoId, $leccionId, $modo)
    {
        $resultado = new Resultado();
        
        ini_set('max_execution_time', 300);
        
        $consulta = " UPDATE usuarios_cursos_lecciones " .
            "SET visto = 1, " .
            "  fecha_modificacion= NOW() " .
            "WHERE usuario_id = ? AND curso_id = ? AND leccion_id = ? ";
        
        //echo $usuario->id."_".$cursoId."_".$leccionId;
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("iii", $usuario->id,$cursoId,$leccionId ))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                    
                    $usuarioId=0;
                    if($modo=="VP")
                        $usuarioId = 999999999;
                    else
                        $usuarioId = $usuario->id;
                            
                    $consulta = "SELECT id, RTRIM(texto) texto, tipo
                     FROM cursos_preguntas CP
                     WHERE curso_id  = ? AND leccion_id = ?
                    		AND CP.id NOT IN(SELECT pregunta_id FROM usuarios_cursos_lecciones_preguntas CPE WHERE CPE.curso_id = CP.curso_id AND CPE.leccion_id = CP.leccion_id AND CPE.usuario_id = ?)
                     ORDER BY rand()
                      LIMIT 1";
                            
                    if($sentencia = $this->conexion->prepare($consulta))
                    {
                        
                        if($sentencia->bind_param("iii",$cursoId,$leccionId, $usuarioId))
                        {
                            if($sentencia->execute())
                            {
                                if ($sentencia->bind_result($id, $texto, $tipo))
                                {
                                    
                                    $preguntaAleatoria = null;
                                    if($sentencia->fetch())
                                    {
                                        $preguntaAleatoria= (object) [
                                            'id' =>  $id,
                                            'texto' => $texto,
                                            'tipo' => $tipo
                                            
                                        ];
                                        // array_push($preguntas, $pregunta);
                                    }
                                    
                                    
                                    $sentencia->close();
                                    
                                    if($preguntaAleatoria!=null)
                                    {
                                        $resultadoRespuestas = $this->consultarRespuestas($cursoId,$leccionId,$preguntaAleatoria->id);
                                        if($resultadoRespuestas->mensajeError=="")
                                        {
                                            $preguntaAleatoria->respuestas = $resultadoRespuestas->valor;
                                        }
                                        else
                                        {
                                            $resultado->mensajeError = $resultadoRespuestas->mensajeError;
                                        }
                                    }
                                    
                                    $resultado = $this->calcularNumeroPreguntasRestantes($usuarioId,$cursoId,$leccionId);
                                    if($resultado->correcto())
                                    {
                                        $numeroPreguntasRestantes= $resultado->valor;
                                        $resultado = $this->calcularNumeroPreguntasContestadas($usuarioId,$cursoId,$leccionId);
                                        if($resultado->correcto())
                                        {
                                            $numeroPreguntasContestadas= $resultado->valor;
                                            $resultado->valor = (object) [
                                                'pregunta' =>  $preguntaAleatoria,
                                                'numeroPreguntasRestantes' => $numeroPreguntasRestantes,
                                                'numeroPreguntasContestadas' => $numeroPreguntasContestadas,
                                                'leccionId' => $leccionId
                                                
                                            ];
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
    
    
    
    public function consultarPorToken($usuario,$token)
    {
        $resultado = new Resultado();
        ini_set('max_execution_time', 300);
        $consulta = $this->consultaBase .
        " WHERE C.token  = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("s",$token))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombre, $descripcion, $fechaAlta, $fechaModificacion, $publicado, $usuarioId, $usuarioNombre, $usuarioApellido,$token,$numeroLecciones,$numeroLeccionesTerminadas))
                    {
                        if($sentencia->fetch())
                        {
                            $curso = $this->crearRegistro($id, $nombre, $descripcion, $fechaAlta, $fechaModificacion, $publicado, $usuarioId, $usuarioNombre, $usuarioApellido, $token,$numeroLecciones,$numeroLeccionesTerminadas);
                            
                            $resultado->valor = $curso;
                            
                            $sentencia->close();
                            
                            $resultadoSecciones = $this->consultarLecciones($usuario,$curso->id);
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
    
    private function consultarLecciones($usuario,$cursoId)
    {
        $resultado = new Resultado();
        $lecciones = array();
        $consulta = "SELECT id, RTRIM(titulo) titulo, RTRIM(descripcion) descripcion, RTRIM(video) video, IFNULL((SELECT terminado FROM usuarios_cursos_lecciones UCL WHERE UCL.curso_id = CL.curso_id AND UCL.leccion_id = CL.id AND usuario_id = ?),0), tiempo_estimado " .
            "FROM cursos_lecciones CL" .
            " WHERE curso_id  = ? ".
            "ORDER BY orden";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ii",$usuario->id,$cursoId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $texto, $descripcion, $video, $terminado, $tiempoEstimado))
                    {
                        while($sentencia->fetch())
                        {
                            $leccion= (object) [
                                'id' =>  $id,
                                'titulo' => $texto,
                                'descripcion' => $descripcion,
                                'video' => $video, 
                                'terminado' => $terminado,
                                'tiempoEstimado' => $tiempoEstimado
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
    
    private function crearRegistro($id, $titulo, $descripcion, $fechaAlta, $fechaModificacion, $publicado, $usuarioId, $usuarioNombre, $usuarioApellido,$token,$numeroLecciones,$numeroLeccionesTerminadas, $tiempoEstimado=0)
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
            'usuarioApellido' => $usuarioApellido,
            'token'=> $token,
            'numeroLecciones' => $numeroLecciones,
            'numeroLeccionesTerminadas' => $numeroLeccionesTerminadas,
            'tiempoEstimado' => $tiempoEstimado
        ];
        
      
        $registro->porcentajeCumplimiento  = 0;
        if($registro->numeroLecciones!=0)
        {
            $registro->porcentajeCumplimiento = $registro->numeroLeccionesTerminadas  * 100 / $registro->numeroLecciones;
            //$registro->porcentajeCumplimiento = number_format($registro->porcentajeCumplimiento, 1, '.', '');
            
            $registro->porcentajeCumplimiento = bcdiv($registro->porcentajeCumplimiento, '1', 1);
            
            list($enteros, $decimales) = explode(".", $registro->porcentajeCumplimiento);
            if($decimales=="0")
                $registro->porcentajeCumplimiento = str_replace(".$decimales","",$registro->porcentajeCumplimiento);
                
        }
        
            
        
        if($registro->porcentajeCumplimiento>=100)
        {
            $registro->bgTerminadas =  "bg-green";
            $registro->progressBar =  "progress-bar-green";
        }
        else if($registro->porcentajeCumplimiento>0)
        {
            $registro->bgTerminadas =  "bg-yellow";
            $registro->progressBar =  "progress-bar-green";
        }
        else 
            $registro->bgTerminadas =  "bg-red";
        
        if($numeroLecciones==1)
            $registro->textoLecciones =  "Lección";
        else
            $registro->textoLecciones = "Lecciones";
        
        if($numeroLeccionesTerminadas==1)
            $registro->textoLeccionesTerminadas =  "Completada ($registro->porcentajeCumplimiento%)";
        else
            $registro->textoLeccionesTerminadas = "Completadas ($registro->porcentajeCumplimiento%)";
        
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
        $this->conexion->autocommit(FALSE);
        
        $resultado = $this->eliminarPreguntaEjecucion($llaves->cursoId, $llaves->leccionId, $llaves->preguntaId);
        if($resultado->correcto())
        {
            $resultado = $this->eliminarRespuestasPregunta($llaves->cursoId, $llaves->leccionId, $llaves->preguntaId);
            if($resultado->correcto())
            {
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
    
    public function eliminarRespuesta($llaves)
    {
        $resultado = new Resultado();
        
        $resultado = $this->eliminarRespuestaEjecucion($llaves);
        if($resultado->correcto())
        {
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
        }
        return $resultado;
    }
    
    public function eliminarRespuestaEjecucion($llaves)
    {
        $resultado = new Resultado();
        $consulta ="DELETE FROM usuarios_cursos_lecciones_preguntas WHERE curso_id = ? AND leccion_id = ? AND pregunta_id = ? AND respuesta_id = ?";
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
        
        $resultado = $this->eliminarPreguntasLeccionEjecucion($llaves->cursoId,$llaves->leccionId);
        if($resultado->correcto())
        {
                   
            $resultado = $this->eliminarLeccionEjecucion($llaves->cursoId,$llaves->leccionId);
            if($resultado->correcto())
            {
                
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
            }
        }
        if($resultado->correcto())
            $this->conexion->commit();
        else
            $this->conexion->rollback();
       
        return $resultado;
    }
    
    public function eliminarLeccionEjecucion($cursoId, $leccionId)
    {
        ini_set('max_execution_time', 300);
        $resultado = new Resultado();
        $this->conexion->autocommit(FALSE);
        
        $resultado = $this->eliminarPreguntasLeccion($cursoId,$leccionId);
        if($resultado->correcto())
        {
            $consulta ="DELETE FROM usuarios_cursos_lecciones WHERE curso_id = ? AND leccion_id = ?";
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
            $resultado = $this->eliminarPreguntasEjecucion($llaves->id);
            if($resultado->correcto())
            {
                $resultado = $this->eliminarLeccionesEjecucion($llaves->id);
                if($resultado->correcto())
                {
                    $resultado = $this->eliminarCursosEjecucion($llaves->id);
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
                }
            }
        }
        if($resultado->correcto())
            $this->conexion->commit();
        else
            $this->conexion->rollback();
        return $resultado;
    }
    
    public function eliminarCursoUsuario($llaves)
    {
        ini_set('max_execution_time', 300);
        $resultado = new Resultado();
        $this->conexion->autocommit(FALSE);
        
        $resultado = $this->eliminarPreguntasUsuarioEjecucion($llaves->usuarioId, $llaves->cursoId);
        if($resultado->correcto())
        {
            $resultado = $this->eliminarLeccionesUsuarioEjecucion($llaves->usuarioId, $llaves->cursoId);
            if($resultado->correcto())
            {
                $resultado = $this->eliminarCursoUsuarioEjecucion($llaves->usuarioId, $llaves->cursoId);
                if($resultado->correcto())
                {
                }
            }
        }
        if($resultado->correcto())
            $this->conexion->commit();
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
    
    public function ordenarCursos($seleccion)
    {
        $resultado = new Resultado();
        
        $registros = explode("&", $seleccion);
        
        $this->conexion->autocommit(FALSE);
        
        $i = 1;
        foreach ($registros as $key => $value)
        {
            $ides = explode("=", $value);
            
            $id = $ides[1];
            
            
            $consulta = " UPDATE cursos " .
                "SET orden = ? " .
                "WHERE id = ? ";
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if( $sentencia->bind_param("ii", $i,$id))
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
    
    public function calcularIdRespuesta($cursoId, $leccionId, $preguntaId, $campo)
    {
        $resultado = new Resultado();
        $consulta =  "SELECT IFNULL(MAX($campo),0)+1 AS id FROM cursos_respuestas WHERE curso_id = ? AND leccion_id = ? AND pregunta_id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("iii", $cursoId, $leccionId,$preguntaId))
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
    
    public function calcularNumeroPreguntasRestantes($usuarioId,$cursoId, $leccionId)
    {
        $resultado = new Resultado();
        $consulta = "SELECT count(*)
                     FROM cursos_preguntas CP
                     WHERE curso_id  = ? AND leccion_id = ?
                    		AND CP.id NOT IN(SELECT pregunta_id FROM usuarios_cursos_lecciones_preguntas CPE WHERE CPE.curso_id = CP.curso_id AND CPE.leccion_id = CP.leccion_id AND CPE.usuario_id = ?)
                      ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("iii", $cursoId, $leccionId,$usuarioId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($count))
                    {
                        if($sentencia->fetch())
                        {
                            $resultado->valor = $count;
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
    
    public function calcularNumeroLeccionesRestantes($usuarioId,$cursoId)
    {
        $resultado = new Resultado();
        $consulta = "SELECT count(*)
                     FROM cursos_lecciones CL
                     WHERE curso_id  = ? 
                    		AND CL.id NOT IN(SELECT leccion_id FROM usuarios_cursos_lecciones CCL WHERE CCL.curso_id = CL.curso_id AND CCL.usuario_id = ? AND CCL.terminado = 1)
                      ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("ii", $cursoId,$usuarioId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($count))
                    {
                        if($sentencia->fetch())
                        {
                            $resultado->valor = $count;
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
    
    public function calcularNumeroPreguntasContestadas($usuarioId,$cursoId, $leccionId)
    {
        $resultado = new Resultado();
        $consulta = "SELECT count(*)
                     FROM cursos_preguntas CP
                     WHERE curso_id  = ? AND leccion_id = ?
                    		AND CP.id IN(SELECT pregunta_id FROM usuarios_cursos_lecciones_preguntas CPE WHERE CPE.curso_id = CP.curso_id AND CPE.leccion_id = CP.leccion_id AND CPE.usuario_id = ?)
                      ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("iii", $cursoId, $leccionId, $usuarioId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($count))
                    {
                        if($sentencia->fetch())
                        {
                            $resultado->valor = $count;
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
    
    private function getConsultaBase($filtros,$filtroCapacitacion,$criteriosSeleccion,$usuario)
    {
        //$filtros = $this->getFiltroEstructura($usuario,$criteriosSeleccion);
        $consulta = "SELECT * 
            from(
            SELECT U.id as id, U.nombre_usuario as nombreUsuario, U.contrasena contrasena,U.nombre, U.apellido, E.id empresaId, IFNULL(E.nombre,'') empresa, S.id sedeId, IFNULL(S.nombre,'') sede, P.id puestoId, IFNULL(P.nombre,'') puesto, A.id areaId, IFNULL(A.nombre,'') area, T.id tipoUsuarioId, T.nombre tipo_usuario, SU1.id supervisor1Id, CONCAT(IFNULL(SU1.nombre,''),' ',IFNULL(SU1.apellido,'')) supervisor1,SU2.id supervisor2Id,CONCAT(IFNULL(SU2.nombre,''),' ',IFNULL(SU2.apellido,'')) supervisor2,SU3.id supervisor3Id, CONCAT(IFNULL(SU3.nombre,''),' ',IFNULL(SU3.apellido,'')) supervisor3, IFNULL(DATE_FORMAT(U.fecha_alta,'%d/%m/%Y %H:%i:%s'),'') fecha_alta,  IFNULL(DATE_FORMAT(U.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'')fecha_modificacion,IFNULL((SELECT IFNULL(DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s'),'') as fecha FROM historial_acceso WHERE nombre_usuario= U.nombre_usuario ORDER BY id DESC LIMIT 1),'') ultimo_acceso, U.estatus, E.tipo_empresa_id, A.tipo_area_id, U.permiso_saha,U.permiso_sivah,U.permiso_10y7, U.departamento_id departamentoId, D.nombre as departamentoNombre, U.permiso_cavih, U.perfil_id, PR.nombre perfilNombre, U.recursos_humanos recursosHumanos, 
            (SELECT count(*)
                        	FROM cursos_preguntas CPR 
                        		INNER JOIN cursos C ON CPR.curso_id = C.id 
                                INNER JOIN usuarios_cursos UC ON UC.curso_id = C.id 
                        	WHERE C.publicado = 1 AND UC.usuario_id=  U.id
                        		AND UC.terminado = 1 $filtroCapacitacion
                )total,
                (
                    SELECT count(*)
                    FROM usuarios_cursos_lecciones_preguntas P
                    INNER JOIN cursos C on C.id = P.curso_id
                    INNER JOIN usuarios_cursos UC ON UC.curso_id = C.id AND UC.usuario_id= P.usuario_id                    
                    INNER JOIN cursos_respuestas R ON R.curso_id = P.curso_id AND R.leccion_id = P.leccion_id AND R.pregunta_id = P.pregunta_id AND R.id = P.respuesta_id
                    WHERE P.usuario_id = U.id
                    AND R.correcta=1  AND UC.terminado=1 $filtroCapacitacion
                )correctas,
                (SELECT IFNULL(DATE_FORMAT(UC.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'')
                    FROM usuarios_cursos UC
                    INNER JOIN cursos C on C.id = UC.curso_id
                    WHERE UC.usuario_id = U.id  $filtroCapacitacion
                    ORDER BY UNIX_TIMESTAMP(UC.fecha_modificacion) desc
                    LIMIT 1)fechaUltimaCapacitacion,
                (SELECT count(*)
                FROM cursos C
                WHERE  U.perfil_id IN(SELECT perfil_id FROM cursos_perfiles CP WHERE CP.curso_id = C.id) AND C.publicado = 1
                )capacitacionesTotal,
                (
                   SELECT count(*)
                    FROM usuarios_cursos C
                    WHERE  U.perfil_id IN(SELECT perfil_id FROM cursos_perfiles CP WHERE CP.curso_id = C.curso_id) AND C.terminado = 1 AND C.usuario_id = U.id
                    
                )capacitacionesTerminadas,
                 (
                    SELECT count(*)
                    FROM usuarios_cursos_lecciones UCL
                        INNER JOIN cursos C on C.id = UCL.curso_id
                    WHERE UCL.usuario_id = U.id
                    AND UCL.visto=1  $filtroCapacitacion
                )videosVistos,
                (
                    SELECT SUM(duracion)
                    FROM usuarios_cursos_lecciones UCL
                        INNER JOIN cursos C on C.id = UCL.curso_id
                    WHERE UCL.usuario_id = U.id
                    AND UCL.visto=1  $filtroCapacitacion
                )tiempoVisto
            FROM usuarios U
              INNER JOIN empresas E ON U.empresa_id=E.id
              INNER JOIN sedes S ON U.sede_id = S.id
              INNER JOIN departamentos D ON D.id = U.departamento_id
              LEFT JOIN puestos P ON U.puesto_id = P.id
              LEFT JOIN areas A ON U.area_id = A.id
              LEFT JOIN tipos_usuario T ON U.tipo_usuario_id = T.id
              LEFT JOIN usuarios SU1 ON U.supervisor1_id = SU1.id
              LEFT JOIN usuarios SU2 ON U.supervisor2_id = SU2.id
              LEFT JOIN usuarios SU3 ON U.supervisor3_id = SU3.id
              LEFT JOIN perfiles PR ON PR.id = U.perfil_id 
        WHERE U.permiso_cavih = 1 ";
        
        $consulta.= $this->and($filtros);
        
        $consulta.=")consulta ";
        
        $filtrosSub = array();
        if(isset($criteriosSeleccion->tipoReporte) && $criteriosSeleccion->tipoReporte!="")
        {
            switch($criteriosSeleccion->tipoReporte)
            {
                case 1:
                    array_push($filtrosSub,(object)['tipo'=>'estatico','texto'=>'fechaUltimaCapacitacion is not null']);
                    //$consulta.=" where fechaUltimaCapacitacion is not null ";
                    break;
                    
                case 0:
                    array_push($filtrosSub,(object)['tipo'=>'estatico','texto'=>'fechaUltimaCapacitacion is  null']);
                    break;
            }
        }
        
        $consulta.= $this->where($filtrosSub);
        
        return $consulta;
    }
  
    
    public function consultarResultadosUsuarios($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        $filtros = $this->getFiltroEstructura($usuario,$criteriosSeleccion);
        
        $filtroCapacitacion ="";
        if(isset($criteriosSeleccion->cursoId) && $criteriosSeleccion->cursoId!="")
            $filtroCapacitacion = " AND C.id = $criteriosSeleccion->cursoId ";
        
        $consulta = $this->getConsultaBase($filtros,$filtroCapacitacion,$criteriosSeleccion,$usuario);
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre, $permisoCAVIH, $perfilId, $perfilNombre, $recursosHumanos, $total,$correctas,$fechaUltimaCapacitacion,$totalCapacitaciones,$capacitacionesTerminadas,$videosVistos, $tiempoVisto)  )
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro= (object) [
                                'id' =>  $id,
                                'nombreUsuario' => $nombreUsuario,
                                'contrasena' => $contrasena,
                                'nombre' => $nombre,
                                'apellido' => $apellido,
                                'empresaId' => $empresaId,
                                'empresaNombre' => $empresa,
                                'sedeId' => $sedeId,
                                'sedeNombre' => $sede,
                                'puestoId' => $puestoId,
                                'puestoNombre' => $puesto,
                                'areaId' => $areaId,
                                'areaNombre' => $area,
                                'tipoUsuarioId' => $tipoUsuarioId,
                                'tipoUsuarioNombre' => $tipoUsuario,
                                'supervisor1Id' => $supervisor1Id,
                                'supervisor1Nombre' => $supervisor1,
                                'supervisor2Id' => $supervisor2Id,
                                'supervisor2Nombre' => $supervisor2,
                                'supervisor3Id' => $supervisor3Id,
                                'supervisor3Nombre' => $supervisor3,
                                'ultimoAcceso' => $ultimoAcceso,
                                'fechaAlta' => $fechaAlta,
                                'fechaModificacion' => $fechaModificacion,
                                'estatus' => $estatus,
                                'tipoEmpresaId' => $tipoEmpresaId,
                                'tipoAreaId' => $tipoAreaId,
                                'permisoSAHA' => $permisoSAHA,
                                'permisoSIVAH' => $permisoSIVAH,
                                'permiso10y7' => $permiso10y7,
                                'departamentoId' => $departamentoId,
                                'departamentoNombre' => $departamentoNombre,
                                'permisoCAVIH' => $permisoCAVIH,
                                'perfilId' => $perfilId,
                                'perfilNombre' => $perfilNombre,
                                'recursosHumanos' => $recursosHumanos,
                                'total' => $total,
                                'correctas' => $correctas,
                                'fechaUltimaCapacitacion' => $fechaUltimaCapacitacion,
                                'totalCapacitaciones' => $totalCapacitaciones,
                                'capacitacionesTerminadas' => $capacitacionesTerminadas,
                                'videosVistos' => $videosVistos,
                                'tiempoVisto' => $tiempoVisto
                            ];
                            
                            
                            $registro->nombreCompleto = $registro->nombre . " " . $registro->apellido;
                            $registro->fotoPerfil =  "../fotos/usuario". $registro->id .".jpg";
                            if(file_exists($registro->fotoPerfil))
                                $registro->fotoPerfil =  "php/fotos/usuario". $registro->id .".jpg";
                            else
                                $registro->fotoPerfil =  "php/fotos/default.jpg";
                                    
                            $registro->nodeId = $id;
                            $registro->parentId = $registro->supervisor1Id;
                            $registro->text = $registro->nodeId." - ".$registro->nombreCompleto;
                            $registro->nombreId =  $registro->nombreCompleto ." (".$registro->id.")";
                            $this->calcularPorcentaje($registro,'correctas','total',"porcentaje");
                            
                            $this->calcularPorcentaje($registro,'capacitacionesTerminadas','totalCapacitaciones',"porcentajeAvance");
                            
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
    
    public function consultarTiempoUsuarios($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        $filtros = $this->getFiltroEstructura($usuario,$criteriosSeleccion);
        
        $filtroCapacitacion ="";
        if(isset($criteriosSeleccion->cursoId) && $criteriosSeleccion->cursoId!="")
            $filtroCapacitacion = " AND C.id = $criteriosSeleccion->cursoId ";
            
            
            $consulta = "SELECT id, nombreUsuario, nombre, apellido, videosVistos, tiempoVisto ".
                "\nFROM(" . $this->getConsultaBase($filtros,$filtroCapacitacion,$criteriosSeleccion,$usuario).
                "\n) AS A " .
                "\nORDER BY nombre, apellido";
            
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($this->bind_param($sentencia, $filtros))
                {
                    if($sentencia->execute())
                    {
                        if ($sentencia->bind_result($id, $nombreUsuario, $nombre, $apellido, $videosVistos, $tiempoVisto)  )
                        {
                            while($row = $sentencia->fetch())
                            {
                                $registro= (object) [
                                    'id' =>  $id,
                                    'nombreUsuario' => $nombreUsuario,
                                    'nombre' => $nombre,
                                    'apellido' => $apellido,
                                    'videosVistos' => $videosVistos,
                                    'tiempoVisto' => $tiempoVisto,
                                ];
                                
                                
                                $registro->nombreCompleto = $registro->nombre . " " . $registro->apellido;
                                $registro->fotoPerfil =  "../fotos/usuario". $registro->id .".jpg";
                                if(file_exists($registro->fotoPerfil))
                                    $registro->fotoPerfil =  "php/fotos/usuario". $registro->id .".jpg";
                                else
                                    $registro->fotoPerfil =  "php/fotos/default.jpg";
                                        
//                                     $registro->nodeId = $id;
//                                     $registro->parentId = $registro->supervisor1Id;
//                                     $registro->text = $registro->nodeId." - ".$registro->nombreCompleto;
                                     $registro->nombreId =  $registro->nombreCompleto ." (".$registro->id.")";
//                                     $this->calcularPorcentaje($registro,'correctas','total',"porcentaje");
                                        
//                                         $this->calcularPorcentaje($registro,'capacitacionesTerminadas','totalCapacitaciones',"porcentajeAvance");
                                        
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
    
    public function consultarAvanceUsuarios($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        $filtros = $this->getFiltroEstructura($usuario,$criteriosSeleccion);
        
        $filtroCapacitacion ="";
        if(isset($criteriosSeleccion->cursoId) && $criteriosSeleccion->cursoId!="")
            $filtroCapacitacion = " AND C.id = $criteriosSeleccion->cursoId ";
            
            $consulta = $this->getConsultaBase($filtros,$filtroCapacitacion,$criteriosSeleccion,$usuario);
            
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($this->bind_param($sentencia, $filtros))
                {
                    if($sentencia->execute())
                    {
                        if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre, $permisoCAVIH, $perfilId, $perfilNombre, $recursosHumanos, $total,$correctas,$fechaUltimaCapacitacion,$totalCapacitaciones,$capacitacionesTerminadas,$videosVistos, $tiempoVisto)  )
                        {
                            while($row = $sentencia->fetch())
                            {
                                $registro= (object) [
                                    'id' =>  $id,
                                    'nombreUsuario' => $nombreUsuario,
                                    'contrasena' => $contrasena,
                                    'nombre' => $nombre,
                                    'apellido' => $apellido,
                                    'empresaId' => $empresaId,
                                    'empresaNombre' => $empresa,
                                    'sedeId' => $sedeId,
                                    'sedeNombre' => $sede,
                                    'puestoId' => $puestoId,
                                    'puestoNombre' => $puesto,
                                    'areaId' => $areaId,
                                    'areaNombre' => $area,
                                    'tipoUsuarioId' => $tipoUsuarioId,
                                    'tipoUsuarioNombre' => $tipoUsuario,
                                    'supervisor1Id' => $supervisor1Id,
                                    'supervisor1Nombre' => $supervisor1,
                                    'supervisor2Id' => $supervisor2Id,
                                    'supervisor2Nombre' => $supervisor2,
                                    'supervisor3Id' => $supervisor3Id,
                                    'supervisor3Nombre' => $supervisor3,
                                    'ultimoAcceso' => $ultimoAcceso,
                                    'fechaAlta' => $fechaAlta,
                                    'fechaModificacion' => $fechaModificacion,
                                    'estatus' => $estatus,
                                    'tipoEmpresaId' => $tipoEmpresaId,
                                    'tipoAreaId' => $tipoAreaId,
                                    'permisoSAHA' => $permisoSAHA,
                                    'permisoSIVAH' => $permisoSIVAH,
                                    'permiso10y7' => $permiso10y7,
                                    'departamentoId' => $departamentoId,
                                    'departamentoNombre' => $departamentoNombre,
                                    'permisoCAVIH' => $permisoCAVIH,
                                    'perfilId' => $perfilId,
                                    'perfilNombre' => $perfilNombre,
                                    'recursosHumanos' => $recursosHumanos,
                                    'total' => $total,
                                    'correctas' => $correctas,
                                    'fechaUltimaCapacitacion' => $fechaUltimaCapacitacion,
                                    'totalCapacitaciones' => $totalCapacitaciones,
                                    'capacitacionesTerminadas' => $capacitacionesTerminadas,
                                    'videosVistos' => $videosVistos,
                                    'tiempoVisto' => $tiempoVisto
                                ];
                                
                                
                                $registro->nombreCompleto = $registro->nombre . " " . $registro->apellido;
                                $registro->fotoPerfil =  "../fotos/usuario". $registro->id .".jpg";
                                if(file_exists($registro->fotoPerfil))
                                    $registro->fotoPerfil =  "php/fotos/usuario". $registro->id .".jpg";
                                else
                                    $registro->fotoPerfil =  "php/fotos/default.jpg";
                                        
                                $registro->nodeId = $id;
                                $registro->parentId = $registro->supervisor1Id;
                                $registro->text = $registro->nodeId." - ".$registro->nombreCompleto;
                                $registro->nombreId =  $registro->nombreCompleto ." (".$registro->id.")";
                                //$this->calcularPorcentaje($registro,'correctas','total',"porcentaje");
                                
                                $this->calcularPorcentaje($registro,'capacitacionesTerminadas','totalCapacitaciones',"porcentaje");
                                
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
    
    public function consultarResultadosDepartamentos($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        $filtros = $this->getFiltroEstructura($usuario,$criteriosSeleccion);
        
        $filtroCapacitacion ="";
        if(isset($criteriosSeleccion->cursoId) && $criteriosSeleccion->cursoId!="")
            $filtroCapacitacion = " AND C.id = $criteriosSeleccion->cursoId ";
            
            $consulta = "SELECT departamentoId, departamentoNombre,SUM(correctas)correctas, SUM(total)total ".
                "\nFROM(" . $this->getConsultaBase($filtros,$filtroCapacitacion,$criteriosSeleccion,$usuario).
                "\n) AS A " .
                "\nGROUP BY departamentoId,departamentoNombre";
                "\nORDER BY departamentoNombre";
            
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($this->bind_param($sentencia, $filtros))
                {
                    if($sentencia->execute())
                    {
                        if ($sentencia->bind_result($id, $nombre, $correctas, $total)  )
                        {
                            while($row = $sentencia->fetch())
                            {
                                $registro= (object) [
                                    'id' =>  $id,
                                    'nombre' => $nombre,
                                    'correctas' => $correctas,
                                    'total' => $total
                                ];
                                
                                $registro->nombreId =  $registro->nombre." (".$registro->id.")";
                                $this->calcularPorcentaje($registro,'correctas','total',"porcentaje");
                                
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
    
    public function consultarAvanceDepartamentos($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        $filtros = $this->getFiltroEstructura($usuario,$criteriosSeleccion);
        
        $filtroCapacitacion ="";
        if(isset($criteriosSeleccion->cursoId) && $criteriosSeleccion->cursoId!="")
            $filtroCapacitacion = " AND C.id = $criteriosSeleccion->cursoId ";
            
            $consulta = "SELECT departamentoId, departamentoNombre,SUM(capacitacionesTerminadas)capacitacionesTerminadas, SUM(capacitacionesTotal)capacitacionesTotal ".
                "\nFROM(" . $this->getConsultaBase($filtros,$filtroCapacitacion,$criteriosSeleccion,$usuario).
                "\n) AS A " .
                "\nGROUP BY departamentoId,departamentoNombre";
            "\nORDER BY departamentoNombre";
            
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($this->bind_param($sentencia, $filtros))
                {
                    if($sentencia->execute())
                    {
                        if ($sentencia->bind_result($id, $nombre, $correctas, $total)  )
                        {
                            while($row = $sentencia->fetch())
                            {
                                $registro= (object) [
                                    'id' =>  $id,
                                    'nombre' => $nombre,
                                    'correctas' => $correctas,
                                    'total' => $total
                                ];
                                
                                $registro->nombreId =  $registro->nombre." (".$registro->id.")";
                                $this->calcularPorcentaje($registro,'correctas','total',"porcentaje");
                                
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
    
    public function consultarCapacitacionesTomadas($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        $filtros = $this->getFiltroEstructura($usuario,$criteriosSeleccion);
        
        if(isset($criteriosSeleccion->cursoId) && $criteriosSeleccion->cursoId!="")
            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'UC1','campo'=>'curso_id','valor'=>$criteriosSeleccion->cursoId]);
//         $filtroCapacitacion ="";
//         if(isset($criteriosSeleccion->cursoId) && $criteriosSeleccion->cursoId!="")
//             $filtroCapacitacion = " AND C.id = $criteriosSeleccion->cursoId ";
            
            
            //$consulta = "SELECT * from(
            $consulta = "SELECT U.id as id, U.nombre_usuario as nombreUsuario, U.contrasena contrasena,U.nombre, U.apellido, E.id empresaId, IFNULL(E.nombre,'') empresa, S.id sedeId, IFNULL(S.nombre,'') sede, P.id puestoId, IFNULL(P.nombre,'') puesto, A.id areaId, IFNULL(A.nombre,'') area, T.id tipoUsuarioId, T.nombre tipo_usuario, SU1.id supervisor1Id, CONCAT(IFNULL(SU1.nombre,''),' ',IFNULL(SU1.apellido,'')) supervisor1,SU2.id supervisor2Id,CONCAT(IFNULL(SU2.nombre,''),' ',IFNULL(SU2.apellido,'')) supervisor2,SU3.id supervisor3Id, CONCAT(IFNULL(SU3.nombre,''),' ',IFNULL(SU3.apellido,'')) supervisor3, IFNULL(DATE_FORMAT(U.fecha_alta,'%d/%m/%Y %H:%i:%s'),'') fecha_alta,  IFNULL(DATE_FORMAT(U.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'')fecha_modificacion,IFNULL((SELECT IFNULL(DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s'),'') as fecha FROM historial_acceso WHERE nombre_usuario= U.nombre_usuario ORDER BY id DESC LIMIT 1),'') ultimo_acceso, U.estatus, E.tipo_empresa_id, A.tipo_area_id, U.permiso_saha,U.permiso_sivah,U.permiso_10y7, U.departamento_id, D.nombre as departamentoNombre, U.permiso_cavih, U.perfil_id, PR.nombre perfilNombre, U.recursos_humanos recursosHumanos, " .
                "(SELECT count(*)
                FROM cursos C
                    INNER JOIN cursos_preguntas CPR ON CPR.curso_id = C.id
                WHERE  U.perfil_id IN(SELECT perfil_id FROM cursos_perfiles CP WHERE CP.curso_id = C.id)  AND C.id = UC1.curso_id
                )total,
                (
                    SELECT count(*)
                    FROM usuarios_cursos_lecciones_preguntas P
                    INNER JOIN cursos C on C.id = P.curso_id
                    INNER JOIN cursos_respuestas R ON R.curso_id = P.curso_id AND R.leccion_id = P.leccion_id AND R.pregunta_id = P.pregunta_id AND R.id = P.respuesta_id
                    WHERE P.usuario_id = U.id
                    AND R.correcta=1  AND C.id = UC1.curso_id
                )correctas, UC1.fecha_inicial, CR.titulo, UC1.terminado, UC1. fecha_final, UC1.curso_id
              FROM usuarios_cursos UC1  
                  INNER JOIN cursos CR ON CR.id = UC1.curso_id
              LEFT JOIN usuarios U ON UC1.usuario_id = U.id
              LEFT JOIN empresas E ON U.empresa_id=E.id
              LEFT JOIN sedes S ON U.sede_id = S.id
              LEFT JOIN puestos P ON U.puesto_id = P.id
              LEFT JOIN areas A ON U.area_id = A.id
              LEFT JOIN tipos_usuario T ON U.tipo_usuario_id = T.id
              LEFT JOIN usuarios SU1 ON U.supervisor1_id = SU1.id
              LEFT JOIN usuarios SU2 ON U.supervisor2_id = SU2.id
              LEFT JOIN usuarios SU3 ON U.supervisor3_id = SU3.id
              LEFT JOIN departamentos D ON D.id = U.departamento_id
              LEFT JOIN perfiles PR ON PR.id = U.perfil_id ";
            
            $consulta.= $this->where($filtros) . " order by UNIX_TIMESTAMP(UC1.fecha_inicial) desc";
            
            //$consulta.=")consulta ";
            
            
//             $filtrosSub = array();
//             if(isset($criteriosSeleccion->tipoReporte) && $criteriosSeleccion->tipoReporte!="")
//             {
//                 switch($criteriosSeleccion->tipoReporte)
//                 {
//                     case 1:
//                         array_push($filtrosSub,(object)['tipo'=>'estatico','texto'=>'fechaUltimaCapacitacion is not null']);
//                         //$consulta.=" where fechaUltimaCapacitacion is not null ";
//                         break;
                        
//                     case 0:
//                         array_push($filtrosSub,(object)['tipo'=>'estatico','texto'=>'fechaUltimaCapacitacion is  null']);
//                         break;
//                 }
//             }
            
            //         if(isset($criteriosSeleccion->fechaInicial) && $criteriosSeleccion->fechaInicial!="")
                //             array_push($filtrosSub,(object)['tipo'=>'estatico','texto'=>"fechaUltimaCapacitacion >= '$criteriosSeleccion->fechaInicial'"]);
                //             if(isset($criteriosSeleccion->fechaFinal) && $criteriosSeleccion->fechaFinal!="")
                    //             array_push($filtrosSub,(object)['tipo'=>'estatico','texto'=>"fechaUltimaCapacitacion <= '$criteriosSeleccion->fechaFinal'"]);
                
               // $consulta.= $this->where($filtrosSub);
                
                //echo $consulta;
                
                
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if($this->bind_param($sentencia, $filtros))
                    {
                        if($sentencia->execute())
                        {
                            if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre, $permisoCAVIH, $perfilId, $perfilNombre, $recursosHumanos, $total,$correctas,$fechaInicial, $titulo, $terminado, $fechaFinal, $cursoId)  )
                            {
                                while($row = $sentencia->fetch())
                                {
                                    $registro= (object) [
                                        'id' =>  $id,
                                        'usuarioId' =>  $id,
                                        'nombreUsuario' => $nombreUsuario,
                                        'contrasena' => $contrasena,
                                        'nombre' => $nombre,
                                        'apellido' => $apellido,
                                        'empresaId' => $empresaId,
                                        'empresaNombre' => $empresa,
                                        'sedeId' => $sedeId,
                                        'sedeNombre' => $sede,
                                        'puestoId' => $puestoId,
                                        'puestoNombre' => $puesto,
                                        'areaId' => $areaId,
                                        'areaNombre' => $area,
                                        'tipoUsuarioId' => $tipoUsuarioId,
                                        'tipoUsuarioNombre' => $tipoUsuario,
                                        'supervisor1Id' => $supervisor1Id,
                                        'supervisor1Nombre' => $supervisor1,
                                        'supervisor2Id' => $supervisor2Id,
                                        'supervisor2Nombre' => $supervisor2,
                                        'supervisor3Id' => $supervisor3Id,
                                        'supervisor3Nombre' => $supervisor3,
                                        'ultimoAcceso' => $ultimoAcceso,
                                        'fechaAlta' => $fechaAlta,
                                        'fechaModificacion' => $fechaModificacion,
                                        'estatus' => $estatus,
                                        'tipoEmpresaId' => $tipoEmpresaId,
                                        'tipoAreaId' => $tipoAreaId,
                                        'permisoSAHA' => $permisoSAHA,
                                        'permisoSIVAH' => $permisoSIVAH,
                                        'permiso10y7' => $permiso10y7,
                                        'departamentoId' => $departamentoId,
                                        'departamentoNombre' => $departamentoNombre,
                                        'permisoCAVIH' => $permisoCAVIH,
                                        'perfilId' => $perfilId,
                                        'perfilNombre' => $perfilNombre,
                                        'recursosHumanos' => $recursosHumanos,
                                        'total' => $total,
                                        'correctas' => $correctas,
                                        'fechaInicial' => $fechaInicial,
                                        'titulo' => $titulo,
                                        'terminado' => $terminado,
                                        'fechaFinal' => $fechaFinal,
                                        'cursoId' => $cursoId
                                    ];
                                    
                                    
                                    $registro->nombreCompleto = $registro->nombre . " " . $registro->apellido;
                                    $registro->fotoPerfil =  "../fotos/usuario". $registro->id .".jpg";
                                    if(file_exists($registro->fotoPerfil))
                                        $registro->fotoPerfil =  "php/fotos/usuario". $registro->id .".jpg";
                                        else
                                            $registro->fotoPerfil =  "php/fotos/default.jpg";
                                            
                                            $registro->nodeId = $id;
                                            $registro->parentId = $registro->supervisor1Id;
                                            $registro->text = $registro->nodeId." - ".$registro->nombreCompleto;
                                            
                                            $this->calcularPorcentaje($registro,'correctas','total',"porcentaje");
                                            
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
    
    public function consultarLeccionesTomadas($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        $filtros = $this->getFiltroEstructura($usuario,$criteriosSeleccion);
        
        $filtroCapacitacion ="";
        if(isset($criteriosSeleccion->cursoId) && $criteriosSeleccion->cursoId!="")
                $filtroCapacitacion = " AND C.id = $criteriosSeleccion->cursoId ";

        
        if(isset($criteriosSeleccion->cursoId) && $criteriosSeleccion->cursoId!="")
            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'UC1','campo'=>'curso_id','valor'=>$criteriosSeleccion->cursoId]);
            
            $consulta = "SELECT id, titulo, SUM(total), SUM(correctas) FROM(".
                "SELECT L.id, L.titulo, " .
            "(SELECT count(*)
                FROM cursos C
                INNER JOIN cursos_preguntas CPR ON CPR.curso_id = C.id 
                WHERE  U.perfil_id IN(SELECT perfil_id FROM cursos_perfiles CP WHERE CP.curso_id = C.id)  AND C.id = UC1.curso_id AND CPR.leccion_id = UCL.leccion_id $filtroCapacitacion  
                )total,
                (
                    SELECT count(*)
                    FROM usuarios_cursos_lecciones_preguntas P
                    INNER JOIN cursos C on C.id = P.curso_id
                    INNER JOIN cursos_respuestas R ON R.curso_id = P.curso_id AND R.leccion_id = P.leccion_id AND R.pregunta_id = P.pregunta_id AND R.id = P.respuesta_id
                    WHERE P.usuario_id = U.id
                    AND R.correcta=1  AND C.id = UC1.curso_id AND P.leccion_id =UCL.leccion_id $filtroCapacitacion
                )correctas
              FROM usuarios_cursos_lecciones UCL
                  INNER JOIN cursos_lecciones L ON L.curso_id = UCL.curso_id AND L.id = UCL.leccion_id
                  INNER JOIN usuarios_cursos UC1 ON UC1.curso_id = UCL.curso_id
                  INNER JOIN cursos CR ON CR.id = UC1.curso_id
                  LEFT JOIN usuarios U ON UC1.usuario_id = U.id
                  LEFT JOIN empresas E ON U.empresa_id=E.id
                  LEFT JOIN sedes S ON U.sede_id = S.id
                  LEFT JOIN tipos_usuario T ON U.tipo_usuario_id = T.id
                  LEFT JOIN departamentos D ON D.id = U.departamento_id
                  LEFT JOIN perfiles PR ON PR.id = U.perfil_id ";
            
            $consulta.= $this->where($filtros) .
            ") consulta " .
            " group by id,titulo " .
            " order by titulo ";
            
                
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if($this->bind_param($sentencia, $filtros))
                    {
                        if($sentencia->execute())
                        {
                            if ($sentencia->bind_result($id, $leccionTitulo, $total,  $correctas)  )
                            {
                                while($row = $sentencia->fetch())
                                {
                                    $registro= (object) [
                                        'id' =>  $id,
                                        'nombre' =>  $leccionTitulo,
                                        'total' =>  $total,
                                        'correctas' =>  $correctas,
                                        
                                    ];
                                    
                                    $registro->nombreId =  $registro->nombre ." (".$registro->id.")";
                                    $this->calcularPorcentaje($registro,'correctas','total',"porcentaje");
                                    
//                                     $registro->nombreCompleto = $registro->nombre . " " . $registro->apellido;
//                                     $registro->fotoPerfil =  "../fotos/usuario". $registro->id .".jpg";
//                                     if(file_exists($registro->fotoPerfil))
//                                         $registro->fotoPerfil =  "php/fotos/usuario". $registro->id .".jpg";
//                                         else
//                                             $registro->fotoPerfil =  "php/fotos/default.jpg";
                                            
//                                             $registro->nodeId = $id;
//                                             $registro->parentId = $registro->supervisor1Id;
//                                             $registro->text = $registro->nodeId." - ".$registro->nombreCompleto;
                                            
//                                             $this->calcularPorcentaje($registro,'correctas','total',"porcentaje");
                                            
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
    
    public function getFiltroEstructura($usuario,$criteriosSeleccion)
    {
        $filtros = array();
        
        if($usuario->recursosHumanos==1)
        {
            if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
            {
                array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'empresa_id','valor'=>$criteriosSeleccion->empresaId]);
            }
            else
            {
                $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
                $resultado = $usuariosRepositorio->consultarIdsEmpresas($usuario->empresaId);
                if($resultado->correcto())
                {
                    $empresasIds = implode(",", $resultado->valor);
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'empresa_id','operador'=>'IN','valor'=>$empresasIds]);
                }
            }
        }
        else
        {
            switch ($usuario->tipoUsuarioId)
            {
                case \TipoUsuario::SUPERVISOR:
//                     if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
//                     {
//                         array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'empresa_id','valor'=>$criteriosSeleccion->empresaId]);
//                     }
//                     else
//                     {
//                         $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
//                         $resultado = $usuariosRepositorio->consultarIdsEmpresas($usuario->empresaId);
//                         if($resultado->correcto())
//                         {
//                             $empresasIds = implode(",", $resultado->valor);
//                             array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'empresa_id','operador'=>'IN','valor'=>$empresasIds]);
//                         }
//                     }
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
                    if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
                    {
                        array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'empresa_id','valor'=>$criteriosSeleccion->empresaId]);
                    }
                    else
                    {
                        $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
                        $resultado = $usuariosRepositorio->consultarIdsEmpresas($usuario->empresaId);
                        if($resultado->correcto())
                        {
                            $empresasIds = implode(",", $resultado->valor);
                            array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'empresa_id','operador'=>'IN','valor'=>$empresasIds]);
                        }
                    }
                break;
               
                
                case \TipoUsuario::ADMINISTRADOR:
                    if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
                    {
                        array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'empresa_id','valor'=>$criteriosSeleccion->empresaId]);
                    }
                    
                break;
            }
        }
        if(isset($criteriosSeleccion->sedeId) && $criteriosSeleccion->sedeId!="")
        {
            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'sede_id','valor'=>$criteriosSeleccion->sedeId]);
        }
        if(isset($criteriosSeleccion->departamentoId) && $criteriosSeleccion->departamentoId!="")
        {
            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'departamento_id','valor'=>$criteriosSeleccion->departamentoId]);
        }
        
        
        return $filtros;
    }
    
    
}

