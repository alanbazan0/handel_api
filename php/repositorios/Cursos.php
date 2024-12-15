<?php
use php\clases\AdministradorConexion;
use php\clases\AdministradorArchivos;
use php\clases\JsonMapper;
use php\modelos\Curso;
use php\repositorios\CursosRepositorio;
use php\modelos\Resultado;
use php\clases\CodigoError;

error_reporting(E_ALL);
ini_set('display_errors', 1);


include '../clases/JsonMapper.php';
include '../clases/Utilidades.php';
include '../clases/CodigoError.php';
include '../clases/AdministradorConexion.php';
include '../clases/AdministradorArchivos.php';
include '../repositorios/CursosRepositorio.php';


$origin = "*";
if(isset($_SERVER['HTTP_ORIGIN']))
    $origin =$_SERVER['HTTP_ORIGIN'];
header('Access-Control-Allow-Origin: '.$origin);
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Credentials: true');

$administrador_conexion = new AdministradorConexion();
$resultado = new Resultado();
$conexion=null;
try
{
    session_start();
    $usuario = null;
    if(isset($_SESSION['usuario']))
    $usuario = $_SESSION['usuario'];
    if($usuario!=null)
    {
        $conexion = $administrador_conexion->abrir();
        if($conexion)
        {
            $accion = REQUEST('accion');
            $repositorio = new CursosRepositorio($conexion);
            switch ($accion)
            {           
                case 'insertar':               
//                     session_start();
//                     $usuario = null;
//                     if(isset($_SESSION['usuario']))
//                         $usuario = $_SESSION['usuario'];
                    $json = json_decode(REQUEST('modelo'));
                    $mapper = new JsonMapper();
                    $modelo = $mapper->map($json, new Curso());                   
                    $resultado = $repositorio->insertar($usuario,$modelo);         
                    if($resultado->mensajeError=="")
                    {
                        $id =  $resultado->valor;
                        $adminstradorArchivos = new AdministradorArchivos();
                        $archivo = FILES("file");
                        if($archivo!=null)
                        {
                            $carpeta = "portadas_cursos";
                            $nombreArchivo = "curso".$modelo->id.".png";
                            $resultado=$adminstradorArchivos->subirImagen($carpeta,$archivo,$nombreArchivo);
                        }
                        $resultado->valor = $id;
                    }
                break;
                case 'actualizar':
                    $json = json_decode(REQUEST('modelo'));
                    $mapper = new JsonMapper();
                    $modelo = $mapper->map($json, new Curso());
                    $resultado = $repositorio->actualizar($modelo) ;
                    if($resultado->mensajeError=="")
                    {
                        $id =  $resultado->valor;
                        $adminstradorArchivos = new AdministradorArchivos();
                        $archivo = FILES("file");
                        $carpeta = "portadas_cursos";
                        $nombreArchivo = "curso".$modelo->id.".png";
                        $resultado=$adminstradorArchivos->subirImagen($carpeta,$archivo,$nombreArchivo);
                        $resultado->valor = $id;
                    }
                break;
                case 'consultar':
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultar($criteriosSeleccion);               
                break;
                case 'consultarCriterio':
                    $opcional = REQUEST('opcional');
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarCriterio($criteriosSeleccion,$opcional);
                break;
                case 'consultarCursosContestando':
//                     session_start();
//                     $usuario = null;
//                     if(isset($_SESSION['usuario']))
//                         $usuario = $_SESSION['usuario'];
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarCursosContestando($usuario,$criteriosSeleccion);
                break;
                case 'terminarLeccionCurso':
                    $cursoId = REQUEST('cursoId');
                    $leccionId = REQUEST('leccionId');
                    $resultado = $repositorio->terminarLeccionCurso($usuario,$cursoId, $leccionId);
                break;
                case 'consultarCursosPendientes':
//                     session_start();
//                     $usuario = null;
//                     if(isset($_SESSION['usuario']))
//                         $usuario = $_SESSION['usuario'];
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarCursosPendientes($usuario,$criteriosSeleccion);
                break;
                case 'consultarCursosTerminados':
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarCursosTerminados($usuario,$criteriosSeleccion);
                break;
                case 'consultarCursosTerminadosDiploma':
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarCursosTerminadosDiploma($usuario,$criteriosSeleccion);
                    break;
                    
                case 'ordenarPreguntas':
                    $cursoId = REQUEST('cursoId');
                    $leccionId = REQUEST('leccionId');
                    $seleccion = REQUEST("seleccion");
                    $resultado = $repositorio->ordenarPreguntas($cursoId,$leccionId,$seleccion);        
                break;
                case 'ordenarCursos':
                    $seleccion = REQUEST("seleccion");
                    $resultado = $repositorio->ordenarCursos($seleccion);
                break;
                case 'consultarPorLlaves':
//                     session_start();
//                     $usuario = null;
//                     if(isset($_SESSION['usuario']))
//                         $usuario = $_SESSION['usuario'];
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->consultarPorLlaves($usuario,$llaves);
                break;   
                case 'consultarPorTokenSinPreguntas':
//                     session_start();
//                     $usuario = null;
//                     if(isset($_SESSION['usuario']))
//                         $usuario = $_SESSION['usuario'];
                    $token = REQUEST('token');
                    $resultado = $repositorio->consultarPorTokenSinPreguntas($usuario,$token);
                break;
                case 'consultarPreguntaAleatoria':
//                     session_start();
//                     $usuario = null;
//                     if(isset($_SESSION['usuario']))
//                         $usuario = $_SESSION['usuario'];
                    $cursoId = REQUEST('cursoId');
                    $leccionId = REQUEST('leccionId');
                    $modo = REQUEST('modo');
                    $resultado = $repositorio->consultarPreguntaAleatoria($usuario, $cursoId, $leccionId, $modo);
                break;
                case 'actualizarDuracionLeccion':
//                     session_start();
//                     $usuario = null;
//                     if(isset($_SESSION['usuario']))
//                          $usuario = $_SESSION['usuario'];
                    $cursoId = REQUEST('cursoId');
                    $leccionId = REQUEST('leccionId');
                    $duracion = REQUEST('duracion');
                    $resultado = $repositorio->actualizarDuracionLeccion($usuario, $cursoId, $leccionId, $duracion);
                break;
                case 'guardarLeccionUsuario':
//                     session_start();
//                     $usuario = null;
//                     if(isset($_SESSION['usuario']))
//                         $usuario = $_SESSION['usuario'];
                    $cursoId = REQUEST('cursoId');
                    $leccionId = REQUEST('leccionId');
                    $duracion = REQUEST('duracion');
                    $resultado = $repositorio->guardarLeccionUsuario($usuario, $cursoId, $leccionId,$duracion);
                break;
                case 'guardarPreguntaUsuario':
//                     session_start();
//                     $usuario = null;
//                     if(isset($_SESSION['usuario']))
//                         $usuario = $_SESSION['usuario'];
                    $cursoId = REQUEST('cursoId');
                    $leccionId = REQUEST('leccionId');
                    $preguntaId = REQUEST('preguntaId');
                    $respuestaId = REQUEST('respuestaId');
                    $resultado = $repositorio->guardarPreguntaUsuario($usuario, $cursoId, $leccionId,$preguntaId, $respuestaId);
                break;
                case 'consultarPorToken':
//                     session_start();
//                     $usuario = null;
//                     if(isset($_SESSION['usuario']))
//                         $usuario = $_SESSION['usuario'];
                    $token = REQUEST('token');
                    $resultado = $repositorio->consultarPorToken($usuario,$token);
                 break;   
                case 'eliminarPregunta':
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->eliminarPregunta($llaves);
                    if($resultado->mensajeError=="")
                    {
                       //TODO: Eliminar valores de preguntas en la ejecucion
                    }
                break;
                case 'eliminarRespuesta':
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->eliminarRespuesta($llaves);
                    if($resultado->mensajeError=="")
                    {
                        //TODO: Eliminar valores de respuestas en la ejecucion
                    }
                    break;
                case 'eliminarLeccion':
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->eliminarLeccion($llaves);
    //                 if($resultado->correcto())
    //                 {
    //                     //TODO: Eliminar valores de seccion en la ejecucion
    //                     $resultado = $repositorio->eliminarLeccionEjecucion($llaves);
    //                 }
                    break;
                case 'insertarPregunta':
                    $cursoId = REQUEST('cursoId');
                    $leccionId = REQUEST('leccionId');
                    $tipo = REQUEST('tipo');
                    $resultado = $repositorio->insertarPregunta($cursoId, $leccionId,  $tipo);
                break;
                case 'insertarRespuesta':
                    $cursoId = REQUEST('cursoId');
                    $leccionId = REQUEST('leccionId');
                    $preguntaId = REQUEST('preguntaId');
                    $resultado = $repositorio->insertarRespuesta($cursoId, $leccionId,  $preguntaId);
                break;
                case 'insertarRespuestaCorrecta':
                    $cursoId = REQUEST('cursoId');
                    $leccionId = REQUEST('leccionId');
                    $preguntaId = REQUEST('preguntaId');
                    $resultado = $repositorio->insertarRespuestaCorrecta($cursoId, $leccionId,  $preguntaId);
                break;
                case 'actualizarValor':
                    $cursoId = REQUEST('cursoId');
                    $campo = REQUEST('campo');
                    $valor = REQUEST('valor');
                    $resultado = $repositorio->actualizarValor($cursoId, $campo,  $valor);
                break;
                case 'actualizarLogo':
                    $cursoId = REQUEST('cursoId');
                    $adminstradorArchivos = new AdministradorArchivos();
                    $archivo = FILES("file");
                    if($archivo!=null)
                    {
                        $carpeta = "portadas_cursos";
                        $nombreArchivo = "curso".$cursoId.".png";
                        $resultado=$adminstradorArchivos->subirImagen($carpeta,$archivo,$nombreArchivo);
                        $resultado->valor = $cursoId;
                    }
                break;
                case 'insertarLeccion':
                    $cursoId = REQUEST('cursoId');
                    $titulo = REQUEST('titulo');
                    $resultado = $repositorio->insertarLeccion($cursoId,$titulo);
               break;
                case 'eliminar':
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->eliminar($llaves);
                   
                    if($resultado->mensajeError=="")
                    {
                        $adminstradorArchivos = new AdministradorArchivos();
                        $carpeta = "portadas_cursos";
                        $nombreArchivo = "curso".$llaves->id.".png";
                        $adminstradorArchivos->eliminar($carpeta,$nombreArchivo);
                    }
                   
                break;
                case 'eliminarCursoUsuario':
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->eliminarCursoUsuario($llaves);
                break;
                case 'guardarRespuestasNo':
                    $cursoId = REQUEST('cursoId');
                    $leccionId = REQUEST('leccionId');
                    $preguntaId = REQUEST('preguntaId');
                    $json = json_decode(REQUEST('respuestas'));
                    $mapper = new JsonMapper();
                    $respuestas = $mapper->mapArray($json, array());
                    $resultado = $repositorio->guardarRespuestasNo($cursoId,$leccionId,$preguntaId,$respuestas);
                break;
                case 'ordenarLecciones':
                    $cursoId = REQUEST('cursoId');
                    $seleccion = REQUEST("seleccion");
                    $resultado = $repositorio->ordenarLecciones($cursoId,$seleccion);
                break;
                case 'actualizarValorPregunta':
                    $cursoId = REQUEST('cursoId');
                    $leccionId = REQUEST('leccionId');
                    $preguntaId = REQUEST('preguntaId');
                    $campo = REQUEST('campo');
                    $valor = REQUEST('valor');
                    $resultado = $repositorio->actualizarValorPregunta($cursoId, $leccionId, $preguntaId, $campo,  $valor);
                break;
                case 'actualizarValorRespuesta':
                    $cursoId = REQUEST('cursoId');
                    $leccionId = REQUEST('leccionId');
                    $preguntaId = REQUEST('preguntaId');
                    $respuestaId = REQUEST('respuestaId');
                    $campo = REQUEST('campo');
                    $valor = REQUEST('valor');
                    $resultado = $repositorio->actualizarValorRespuesta($cursoId, $leccionId, $preguntaId, $respuestaId, $campo,  $valor);
                break;
                case 'actualizarValorRespuestaCorrecta':
                    $cursoId = REQUEST('cursoId');
                    $leccionId = REQUEST('leccionId');
                    $preguntaId = REQUEST('preguntaId');
                    $respuestaId = REQUEST('respuestaId');
                    $valor = REQUEST('valor');
                    $resultado = $repositorio->actualizarValorRespuestaCorrecta($cursoId, $leccionId, $preguntaId, $respuestaId, $valor);
                break;
                case 'actualizarCategoriasPregunta':
                    $cursoId = REQUEST('cursoId');
                    $leccionId = REQUEST('leccionId');
                    $preguntaId = REQUEST('preguntaId');
                    $mapper = new JsonMapper();
                    $categorias = $mapper->mapArray(json_decode(REQUEST('categorias')), array());
                    $resultado = $repositorio->actualizarCategoriasPregunta($cursoId, $leccionId, $preguntaId, $categorias);
                break;
                case 'actualizarValorLeccion':
                    $cursoId = REQUEST('cursoId');
                    $leccionId = REQUEST('leccionId');
                    $campo = REQUEST('campo');
                    $valor = REQUEST('valor');
                    $resultado = $repositorio->actualizarValorLeccion($cursoId, $leccionId, $campo,  $valor);
                break;
                case 'actualizarPerfiles':
                    $cursoId = REQUEST('cursoId');
                    $mapper = new JsonMapper();
                    $perfiles = $mapper->mapArray(json_decode(REQUEST('perfiles')), array());
                    $resultado = $repositorio->actualizarPerfiles($cursoId, $perfiles);
                break;
                case 'consultarAvanceUsuario':
                    $resultado = $repositorio->consultarAvanceUsuario($usuario);
                break;
                case 'consultarAprovechamientoUsuario':
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarAprovechamientoUsuario($usuario,$criteriosSeleccion);
                break;
                case 'consultarVideosVistosUsuario':
                    $resultado = $repositorio->consultarVideosVistosUsuario($usuario);
                break;
                case 'consultarDiasCapacitacionUsuario':
                    $resultado = $repositorio->consultarDiasCapacitacionUsuario($usuario);
                break;
                case 'consultarResultadosUsuarios':
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarResultadosUsuarios($usuario,$criteriosSeleccion);
                break;
                case 'consultarTiempoUsuarios':
                     $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                     $resultado = $repositorio->consultarTiempoUsuarios($usuario,$criteriosSeleccion);
                break;
                case 'consultarCapacitacionesTomadas':
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarCapacitacionesTomadas($usuario,$criteriosSeleccion);
                break;
                case 'consultarAvanceCapacitaciones':
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarAvanceCapacitaciones($usuario,$criteriosSeleccion);
                break;
                case 'consultarLeccionesTomadas':
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarLeccionesTomadas($usuario,$criteriosSeleccion);
                break;
                case 'consultarResultadosDepartamentos':
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarResultadosDepartamentos($usuario,$criteriosSeleccion);
                break;
                case 'consultarResultadosSedes':
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarResultadosSedes($usuario,$criteriosSeleccion);
                break;
                case 'consultarAvanceDepartamentos':
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarAvanceDepartamentos($usuario,$criteriosSeleccion);
                break;
                case 'consultarAvanceUsuarios':
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarAvanceUsuarios($usuario,$criteriosSeleccion);
                break;
                case 'consultarUsuarioCursoLecciones':
                    $usuarioId = REQUEST('usuarioId');
                    $cursoId = REQUEST('cursoId');
                    $resultado = $repositorio->consultarUsuarioCursoLecciones($usuarioId, $cursoId);
                break;
                case 'eliminarUsuarioCapacitacionLeccion':
                    $usuarioId = REQUEST('usuarioId');
                    $cursoId = REQUEST('cursoId');
                    $leccionId = REQUEST('leccionId');
                    $calificacion = REQUEST('calificacion');
                    $resultado = $repositorio->eliminarUsuarioCapacitacionLeccion($usuarioId, $cursoId, $leccionId,$calificacion);
                break;
                case 'consultarUsuarioCapacitacion':
                    $usuarioId = REQUEST('usuarioId');
                    $cursoId = REQUEST('cursoId');
                    $resultado = $repositorio->consultarUsuarioCapacitacion($usuarioId, $cursoId);
                break;
                case 'consultarLeccionesReprobadas':
                    $limite = REQUEST('limite');
                    $resultado = $repositorio->consultarLeccionesReprobadas($usuario,$limite);
                break;
                case 'consultarNumeroNotificacionesNoLeidas':
                    $resultado = $repositorio->consultarNumeroNotificacionesNoLeidas($usuario);
                break;
                case 'consultarNotificacionesNoLeidas':
                    $resultado = $repositorio->consultarNotificacionesNoLeidas($usuario);
                break;
                case 'leerNotificaciones':
                    $resultado = $repositorio->leerNotificaciones($usuario);
                break;
                case 'consultarLeccionesReprobadasSinTerminar':
                    $resultado = $repositorio->consultarLeccionesReprobadasSinTerminar($usuario);
                break;
                default:
                    $resultado->mensajeError = "Acción no válida";
                break;
                
            }
        }
    }
    else
    {
        $resultado->mensajeError = "La sesión caducó. Inicie sesión e intente de nuevo.";
        $resultado->codigoError = CodigoError::SESION_CADUCADA;
    }
    
}
catch(Exception $e)
{   
    $resultado->mensajeError = $e->getMessage();
}
finally
{
    if($resultado!=null)
    {
        $json = json_encode($resultado, JSON_PRETTY_PRINT);
        if (FALSE === $json)
            echo '{"mensajeError":"' .json_last_error_msg() . '"}';
            else
                echo $json;
    }
    $administrador_conexion->cerrar($conexion);
}


