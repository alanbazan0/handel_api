<?php
use php\clases\AdministradorConexion;
use php\clases\JsonMapper;
use php\modelos\Evidencia;
use php\repositorios\ProcesosRevisadosRepositorio;
use php\modelos\Resultado;
use php\clases\AdministradorArchivos;
use php\clases\AdministradorCorreo;
use php\repositorios\UsuariosProcesosRepositorio;
use php\repositorios\UsuariosRepositorio;
use php\clases\CodigoError;


error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../clases/JsonMapper.php';
include '../clases/Utilidades.php';
include '../clases/CodigoError.php';
include '../clases/AdministradorConexion.php';
include '../clases/AdministradorArchivos.php';
include '../clases/AdministradorCorreo.php';
include '../repositorios/ProcesosRevisadosRepositorio.php';
require_once('../repositorios/UsuariosProcesosRepositorio.php');

$origin = "*";
if(isset($_SERVER['HTTP_ORIGIN']))
  $origin =$_SERVER['HTTP_ORIGIN'];
header('Access-Control-Allow-Origin: '.$origin);
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Credentials: true');

$administrador_conexion = new AdministradorConexion();
$resultado = new Resultado();
$conexion=null;
//$diaLimite = 27;
//PRUEBAS 31
$diaLimite = 27;
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
            $repositorio = new ProcesosRevisadosRepositorio($conexion);
            switch($accion)
            {
                case 'insertar':
//                     $json = json_decode(REQUEST('modelo'));
//                     $mapper = new JsonMapper();
//                     $modelo = $mapper->map($json, new Evidencia());
//                     $resultado = $repositorio->insertar($modelo);
                break;
                case 'reportarSinCambios':
                    $usuarioProcesoId = REQUEST('usuarioProcesoId');
                    $resultado = $repositorio->reportarSinCambios($usuario,$usuarioProcesoId);
                break;
                case 'guardarObservaciones':
                    $usuarioProcesoId = REQUEST('usuarioProcesoId');
                    $observaciones = json_decode(REQUEST('observaciones'));
                    $resultado = $repositorio->guardarObservaciones($usuario,$usuarioProcesoId,$observaciones);
                break;
                case 'actualizar':
                    $json = json_decode(REQUEST('modelo'));
                    $mapper = new JsonMapper();
                    $modelo = $mapper->map($json, new Evidencia());
                    $resultado = actualizar($modelo,$conexion,$repositorio,$diaLimite);
                break;
                case 'validarEvidencia':
    //                 session_start();
    //                 $usuario = null;
    //                 if(isset($_SESSION['usuario']))
    //                     $usuario = $_SESSION['usuario'];
                    $json = json_decode(REQUEST('modelo'));
                    $mapper = new JsonMapper();
                    $modelo = $mapper->map($json, new Evidencia());
                    $resultado = $repositorio->validarEvidencia($usuario,$modelo);
                    if($resultado->correcto())
                    {
    
                    }
                break;
                case 'consultar':
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultar($criteriosSeleccion);
                break;
                case 'consultarPorLlaves':
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->consultarPorLlaves($llaves);
                break;
                case 'eliminar':
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->eliminar($llaves);
                break;
                case 'consultarProcesosEnviados':
    //                 session_start();
    //                 $usuario = null;
    //                 if(isset($_SESSION['usuario']))
    //                     $usuario = $_SESSION['usuario'];
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarProcesosEnviados($usuario,$criteriosSeleccion);
                break;
                case 'consultarEvidenciasJustificacion':
    //                 session_start();
    //                 $usuario = null;
    //                 if(isset($_SESSION['usuario']))
    //                     $usuario = $_SESSION['usuario'];
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarEvidenciasJustificacion($usuario,$criteriosSeleccion);
                break;
                case 'consultarComentariosEvidencia':
                    $evidenciaId = REQUEST('evidenciaId');
                    $resultado = $repositorio->consultarComentariosEvidencia($evidenciaId);
                break;
                case 'consultarEvidencias':
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarEvidencias($criteriosSeleccion);
                break;
                case 'consultarPorcentajesEvidencias':
    //                 session_start();
    //                 $usuario = null;
    //                 if(isset($_SESSION['usuario']))
    //                     $usuario = $_SESSION['usuario'];
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarPorcentajesEvidencias($usuario,$criteriosSeleccion);
                break;
                case 'consultarPorcentajesEmpresas':
    //                 session_start();
    //                 $usuario = null;
    //                 if(isset($_SESSION['usuario']))
    //                     $usuario = $_SESSION['usuario'];
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarPorcentajesEmpresas($usuario,$criteriosSeleccion);
                break;
                case 'consultarPorcentajesSedes':
    //                 session_start();
    //                 $usuario = null;
    //                 if(isset($_SESSION['usuario']))
    //                     $usuario = $_SESSION['usuario'];
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarPorcentajesSedes($usuario,$criteriosSeleccion);
                break;
                case 'consultarPorcentajesAdministradores':
    //                 session_start();
    //                 $usuario = null;
    //                 if(isset($_SESSION['usuario']))
    //                     $usuario = $_SESSION['usuario'];
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarPorcentajesAdministradores($usuario,$criteriosSeleccion);
                break;
                case 'consultarPorcentajesAreas':
    //                 session_start();
    //                 $usuario = null;
    //                 if(isset($_SESSION['usuario']))
    //                     $usuario = $_SESSION['usuario'];
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarPorcentajesAreas($usuario,$criteriosSeleccion);
                break;
                case 'consultarPorcentajesUsuarios':
    //                 session_start();
    //                 $usuario = null;
    //                 if(isset($_SESSION['usuario']))
    //                     $usuario = $_SESSION['usuario'];
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarPorcentajesUsuarios($usuario,$criteriosSeleccion);
                    break;
                case 'consultarAnosMeses':
    //                 session_start();
    //                 $usuario = null;
    //                 if(isset($_SESSION['usuario']))
    //                     $usuario = $_SESSION['usuario'];
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarAnosMeses($criteriosSeleccion);
                break;
                case 'consultarAnos':
    //                 session_start();
    //                 $usuario = null;
    //                 if(isset($_SESSION['usuario']))
    //                     $usuario = $_SESSION['usuario'];
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarAnos($usuario,$criteriosSeleccion);
                break;
                case 'consultarEvidenciasAnualUsuario':
    //                 session_start();
    //                 $usuario = null;
    //                 if(isset($_SESSION['usuario']))
    //                     $usuario = $_SESSION['usuario'];
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarEvidenciasAnualUsuario($usuario,$criteriosSeleccion);
                 break;
                case 'validarJustificadas':
                    //                 session_start();
                    //                 $usuario = null;
                    //                 if(isset($_SESSION['usuario']))
                    //                     $usuario = $_SESSION['usuario'];
                    $ids =  REQUEST('ids');
                    $mapper = new JsonMapper();
                    $resultado = $repositorio->validarJustificadas($usuario,$ids);
                    if($resultado->correcto())
                    {
                        
                    }
                    break;
                case 'actualizarEstatusValidacionProceso':
                    $procesoRevisadoId = REQUEST('procesoRevisadoId');
                    $estatusValidacionId = REQUEST('estatusValidacionId');
                    $resultado = $repositorio->actualizarEstatusValidacionProceso($usuario,$procesoRevisadoId,$estatusValidacionId);
                break;
                default:
                    $resultado->mensajeError = 'Acción no implementada';
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
        $json = json_encode($resultado, JSON_UNESCAPED_UNICODE);
        if (FALSE === $json)
            echo '{"mensajeError":"' .json_last_error_msg() . '"}';
        else
            echo $json;
    }
    $administrador_conexion->cerrar($conexion);
}



function actualizar($modelo,$conexion,$repositorio,$diaLimite)
{
    $resultado = new Resultado();
    $dia = date('d');
    
    if($dia<=$diaLimite)
    {
        
        $usuariosProcedimientosRepositorio = new UsuariosProcesosRepositorio($conexion);
       
        if($modelo->justificacionId!=null)
        {
            $llaves= (object) [
                'id' =>  $modelo->usuarioProcedimientoId
            ];
            
            $resultado = $usuariosProcedimientosRepositorio->consultarPorLlaves($llaves);
            if($resultado->mensajeError=="")
            {
                $procedimiento = $resultado->valor;
                if($procedimiento->limitarJustificaciones==1)
                {
                    $resultado  = $repositorio->numeroEvidenciasJustificadasAnoActual($modelo->usuarioProcedimientoId);
                    if($resultado->mensajeError=="")
                    {
                        $numeroEvidenciasJustificadasAno = $resultado->valor;
                        if($numeroEvidenciasJustificadasAno  >= $procedimiento->limiteJustificaciones)
                        {
                            $resultado->mensajeError = "No se puede justificar porque se excedió el número máximo de justificaciones por año de esta evidencia." .
                                "Ésta evidencia solo se puede justificar " .$procedimiento->limiteJustificaciones. " veces el este año";
                            return $resultado;
                        }
                        
                    }
                    else
                        return $resultado;
                }
                
            }
            else
                return $resultado;
        }
        
        $archivo = FILES("file");
        
        
        if($archivo==null)
        {
            if($modelo->realizoActividad==1)
            {
                if($modelo->cambioArchivo)
                {
                    $resultado->mensajeError = "Si se realizó la actividad es necesario proporcionar un archivo para la evidencia.";
                    return $resultado;
                }
            }
        }
//         if($modelo->realizoActividad==1 && $archivo==null)
//         {
//             if($modelo->cambioArchivo)
//             {
//                 $resultado->mensajeError = "Si se realizó la actividad es necesario proporcionar un archivo para la evidencia.";
//                 return $resultado;
//             }
//         }
        
        $nombreArchivoSubido="";
        if($archivo!=null)
        {
            $nombreArchivoSubido = $archivo["name"];
            $nombreArchivoSubido = str_replace(" ","_",$nombreArchivoSubido);
        }
        else
        {
            if(!$modelo->cambioArchivo)
                $nombreArchivoSubido = $modelo->nombreArchivo;
            $nombreArchivoSubido = str_replace(" ","_",$nombreArchivoSubido);
        }
        
        $conexion->autocommit(FALSE);
        $resultado = $repositorio->actualizar($modelo,$nombreArchivoSubido);
        if($resultado->mensajeError=="")
        {
            $id =  $resultado->valor;
            if($archivo!=null)
            {
                $adminstradorArchivos = new AdministradorArchivos();
                
                $carpeta = "archivos_evidencias";
                $nombreArchivo = "evidencia".$modelo->id."_" .$nombreArchivoSubido;
                
                $resultado=$adminstradorArchivos->subirArchivo($carpeta,$archivo,$nombreArchivo);
                if($resultado->valor==true)
                    $conexion->commit();
                else
                {
                    $resultado->mensajeError="No se pudo subir el archivo. Intente de nuevo mas tarde.";
                    $conexion->rollback();
                }
                    
            }
            else
                $conexion->commit();
           $resultado->valor = $modelo->usuarioProcedimientoId;
        }
                
            
    }
    else
    {
        $resultado->mensajeError="La fecha límite para subir evidencias es el día ".$diaLimite." de cada mes.";
    }
    return $resultado;
}