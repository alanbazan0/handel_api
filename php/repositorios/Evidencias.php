<?php
use php\clases\AdministradorConexion;
use php\clases\JsonMapper;
use php\modelos\Evidencia;
use php\repositorios\EvidenciasRepositorio;
use php\modelos\Resultado;
use php\clases\AdministradorArchivos;
use php\clases\AdministradorCorreo;
use php\repositorios\UsuariosProcedimientosRepositorio;


error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../clases/JsonMapper.php';
include '../clases/Utilidades.php';
include '../clases/AdministradorConexion.php';
include '../clases/AdministradorArchivos.php';
include '../clases/AdministradorCorreo.php';
include '../repositorios/EvidenciasRepositorio.php';
include '../repositorios/UsuariosProcedimientosRepositorio.php';

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
    $conexion = $administrador_conexion->abrir();
    if($conexion)
    {
        $accion = REQUEST('accion');
        $repositorio = new EvidenciasRepositorio($conexion);
        switch($accion)
        {
            case 'insertar':
                $json = json_decode(REQUEST('modelo'));
                $mapper = new JsonMapper();
                $modelo = $mapper->map($json, new Evidencia());
                $resultado = insertar($modelo,$conexion,$repositorio,$diaLimite);
            break;
            case 'actualizar':
                $json = json_decode(REQUEST('modelo'));
                $mapper = new JsonMapper();
                $modelo = $mapper->map($json, new Evidencia());
                $resultado = actualizar($modelo,$conexion,$repositorio,$diaLimite);
            break;
            case 'validarEvidencia':
                $json = json_decode(REQUEST('modelo'));
                $mapper = new JsonMapper();
                $modelo = $mapper->map($json, new Evidencia());
                $resultado = $repositorio->validarEvidencia($modelo);
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
            case 'consultarEvidenciasCumplidas':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                $resultado = $repositorio->consultarEvidenciasCumplidas($usuario,$criteriosSeleccion);
            break;
            case 'consultarEvidenciasJustificacion':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
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
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                $resultado = $repositorio->consultarPorcentajesEvidencias($usuario,$criteriosSeleccion);
            break;
            case 'consultarPorcentajesEmpresas':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                $resultado = $repositorio->consultarPorcentajesEmpresas($usuario,$criteriosSeleccion);
            break;
            case 'consultarPorcentajesSedes':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                $resultado = $repositorio->consultarPorcentajesSedes($usuario,$criteriosSeleccion);
            break;
            case 'consultarPorcentajesAdministradores':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                $resultado = $repositorio->consultarPorcentajesAdministradores($usuario,$criteriosSeleccion);
            break;
            case 'consultarPorcentajesAreas':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                $resultado = $repositorio->consultarPorcentajesAreas($usuario,$criteriosSeleccion);
            break;
            case 'consultarPorcentajesUsuarios':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                $resultado = $repositorio->consultarPorcentajesUsuarios($usuario,$criteriosSeleccion);
                break;
            case 'consultarAnosMeses':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                $resultado = $repositorio->consultarAnosMeses($usuario,$criteriosSeleccion);
            break;
            case 'consultarAnos':
                session_start();
                $usuario = null;
                if(isset($_SESSION['usuario']))
                    $usuario = $_SESSION['usuario'];
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                $resultado = $repositorio->consultarAnos($usuario,$criteriosSeleccion);
            break;
            default:
                $resultado->mensajeError = 'Acción no válida';
            break;
            
        }
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


function insertar($modelo,$conexion,$repositorio,$diaLimite)
{
    $resultado = new Resultado();
    $dia = date('d');

    if($dia<=$diaLimite)
    {
        
        $usuariosProcedimientosRepositorio = new UsuariosProcedimientosRepositorio($conexion);
        $resultado =  $usuariosProcedimientosRepositorio->existeUsuarioProcedimientoEnMesActual($modelo->usuarioProcedimientoId);
        if($resultado->mensajeError=="")
        {
            if(!$resultado->valor)
            {
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
                
                if($modelo->justificacionId==null && $archivo==null)
                {
                    $resultado->mensajeError = "Si se realizó la actividad es necesario proporcionar un archivo para la evidencia.";
                    return $resultado;
                }
                
                $nombreArchivoSubido="";
                if($archivo!=null)
                {
                    $nombreArchivoSubido = $archivo["name"];
                    $nombreArchivoSubido = str_replace(" ","_",$nombreArchivoSubido);
                }
                
                
                    
                
                $conexion->autocommit(FALSE);
                $resultado = $repositorio->insertar($modelo,$nombreArchivoSubido);
                if($resultado->mensajeError=="")
                {
                    $id =  $resultado->valor;
                    if($archivo!=null)
                    {
                        $adminstradorArchivos = new AdministradorArchivos();
                        
                        $carpeta = "archivos_evidencias";
                        $nombreArchivo = "evidencia".$id."_" .$nombreArchivoSubido;
                        
                        $resultado=$adminstradorArchivos->subirArchivo($carpeta,$archivo,$nombreArchivo);
                        if($resultado->valor==$nombreArchivo)
                            $conexion->commit();
                            else
                                $conexion->rollback();
                    }
                    else
                        $conexion->commit();
                        $resultado->valor = $modelo->usuarioProcedimientoId;
                }
                
            }
            else
            {
                $resultado->mensajeError="Esta evidencia ya fue subida antes, no se permite subir evidencias repetidas. Id: " . $modelo->usuarioProcedimientoId ;
                $resultado->valor = $modelo->usuarioProcedimientoId;
                $resultado->codigoError = 3;
            }
        }
    }
    else
    {
        $resultado->mensajeError="La fecha límite para subir evidencias es el día ".$diaLimite." de cada mes.";
    }
    return $resultado;
}


function actualizar($modelo,$conexion,$repositorio,$diaLimite)
{
    $resultado = new Resultado();
    $dia = date('d');
    
    if($dia<=$diaLimite)
    {
        
        $usuariosProcedimientosRepositorio = new UsuariosProcedimientosRepositorio($conexion);
       
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
                if($resultado->valor==$nombreArchivo)
                    $conexion->commit();
                else
                    $conexion->rollback();
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