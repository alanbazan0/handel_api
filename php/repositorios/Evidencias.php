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
                
                $usuariosProcedimientosRepositorio = new UsuariosProcedimientosRepositorio($conexion);
                $resultado =  $usuariosProcedimientosRepositorio->existeUsuarioProcedimientoEnMesActual($modelo->usuarioProcedimientoId);
                if($resultado->mensajeError=="")
                {
                    if(!$resultado->valor)
                    {
                        $archivo = FILES("file");
                        
                        $resultado = $repositorio->insertar($modelo);
                        if($resultado->mensajeError=="")
                        {
                            $id =  $resultado->valor;
                            $adminstradorArchivos = new AdministradorArchivos();
                            
                            $carpeta = "../fotos_evidencias/";
                            $nombreArchivo = "evidencia".$modelo->id.".png";
                            
                            $resultado=$adminstradorArchivos->subir($carpeta,$archivo,$nombreArchivo);
                            
//                             if($resultado->mensajeError=="")
//                             {
//                                 $adminstradorCorreo = new AdministradorCorreo();
                                
//                                 $nombreArchivoSubido = "";
//                                 if($archivo!=null)
//                                     $nombreArchivoSubido = $archivo["name"];
                                    
//                                 $resultado = $adminstradorCorreo->enviarNotificacionEvidenciaRecibida($modelo->nombreUsuario, $modelo->nombreCompleto, $modelo->nombreProcedimiento,$nombreArchivoSubido);
                                
//                                 $resultado->valor = $modelo->usuarioProcedimientoId;
//                             }
                            
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
                
                
            break;
            case 'actualizar':
                $json = json_decode(REQUEST('modelo'));
                $mapper = new JsonMapper();
                $modelo = $mapper->map($json, new Evidencia());
                $resultado = $repositorio->actualizar($modelo) ;
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
            case 'consultarEvidenciasCumplidasMesActual':
                $usuarioId = REQUEST('usuarioId');
                $resultado = $repositorio->consultarEvidenciasCumplidasMesActual($usuarioId);
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
