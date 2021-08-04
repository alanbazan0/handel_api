<?php
use php\clases\AdministradorConexion;
use php\clases\CodigoError;
use php\clases\JsonMapper;
use php\modelos\Auditoria;
use php\modelos\Avance;
use php\modelos\Recomendacion;
use php\repositorios\AuditoriasRepositorio;
use php\modelos\Resultado;

error_reporting(E_ALL);
ini_set('display_errors', 1);


include '../clases/JsonMapper.php';
include '../clases/Utilidades.php';
include '../clases/CodigoError.php';
include '../clases/AdministradorConexion.php';
include '../modelos/Avance.php';
include '../modelos/Recomendacion.php';
include '../repositorios/AuditoriasRepositorio.php';



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
            $repositorio = new AuditoriasRepositorio($conexion);
            switch ($accion)
            {           
                case 'insertar':               
                    $json = json_decode(REQUEST('modelo'));
                    $mapper = new JsonMapper();
                    $modelo = $mapper->map($json, new Auditoria());         
                    $resultado = $repositorio->insertar($modelo);                
                break;
                case 'actualizar':
                    $json = json_decode(REQUEST('modelo'));
                    $mapper = new JsonMapper();
                    $modelo = $mapper->map($json, new Auditoria());
                    $resultado = $repositorio->actualizar($modelo) ;
                break;
                case 'consultar':
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultar($criteriosSeleccion);               
                break;
                case 'consultarActivasPorUsuario':
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarActivasPorUsuario($criteriosSeleccion,$usuario);
                break;
                case 'consultarRecomendacionesPendientesUsuario':
                    $llaves = json_decode(REQUEST('llaves'));
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarRecomendacionesPendientesUsuario($llaves,$criteriosSeleccion,$usuario);
                break;
                case 'consultarRecomendaciones':
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarRecomendaciones($criteriosSeleccion,$usuario);
                break;
                case 'consultarAvancesRecomendacion':
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->consultarAvancesRecomendacion($llaves,$usuario);
                break;
                case 'consultarArchivosAvance':
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->consultarArchivosAvance($llaves,$usuario);
                break;
                case 'consultarPorLlaves':
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->consultarPorLlaves($llaves);
                break;    
                case 'consultarPlantillaId':
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->consultarPorLlaves($llaves);
                break;
                case 'consultarValoresSeccion':
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->consultarValoresSeccion($llaves);
                break;     
                case 'consultarValoresSecciones':
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->consultarValoresSecciones($llaves);
                break;     
                case 'consultarPorcentajesCumplimientoSeccion':
                    $auditoriaId = REQUEST('auditoriaId');
                    $resultado = $repositorio->consultarPorcentajesCumplimientoSeccion($auditoriaId);
                break;     
                case 'eliminar':
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->eliminar($llaves);
                break;
                case 'iniciarSeguimiento':
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->iniciarSeguimiento($llaves);
                break;
                case 'finalizarSeguimiento':
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->finalizarSeguimiento($llaves);
                break;
                case 'consultarSeguimiento':
                    $auditoriaId = REQUEST('auditoriaId');
                    $resultado = $repositorio->consultarSeguimiento($auditoriaId);
                break;
                case 'insertarAvance':
                    $recomendacionId = REQUEST('recomendacionId');
                    $json = json_decode(REQUEST('modelo'));
                    $mapper = new JsonMapper();
                    $modelo = $mapper->map($json, new Avance());
                    $resultado = $repositorio->insertarAvance($recomendacionId,$modelo,$usuario);
                break;
                case 'actualizarAvance':
                    $recomendacionId = REQUEST('recomendacionId');
                    $json = json_decode(REQUEST('modelo'));
                    $mapper = new JsonMapper();
                    $modelo = $mapper->map($json, new Avance());
                    $resultado = $repositorio->actualizarAvance($recomendacionId,$modelo,$usuario) ;
                break;
                case 'consultarAvanceTerminadoRecomendacion':
                    $recomendacionId = REQUEST('recomendacionId');
                    $resultado = $repositorio->consultarAvanceTerminadoRecomendacion($recomendacionId) ;
                break;
                case 'actualizarRecomendacion':
                    $json = json_decode(REQUEST('modelo'));
                    $mapper = new JsonMapper();
                    $modelo = $mapper->map($json, new Recomendacion());
                    $resultado = $repositorio->actualizarDatosRecomendacion($modelo,$usuario) ;
                break;
                case 'consultarAvancePorLlaves':
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->consultarAvancePorLlaves($llaves);
                break; 
                case 'consultarRecomendacionPorLlaves':
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->consultarRecomendacionPorLlaves($llaves);
                break; 
                case 'eliminarAvance':
                    $llaves = json_decode(REQUEST('llaves'));
                    $resultado = $repositorio->eliminarAvance($llaves);
                break;
                case 'validarRecomendacion':
                    $json = json_decode(REQUEST('modelo'));
                    $mapper = new JsonMapper();
                    $modelo = $mapper->map($json, new Recomendacion());
                    $resultado = $repositorio->validarRecomendacion($usuario,$modelo);
                    if($resultado->correcto())
                    {
                        
                    }
                break;
                case 'subirArchivosAvance':
                    $recomendacionId =  REQUEST('recomendacionId');
                    $avanceId =  REQUEST('avanceId');
                    $archivos = FILES('file');
                    $resultado = $repositorio->insertarArchivosAvance($recomendacionId,$avanceId,$archivos);
                break;
                case 'enviarNotificacionInicioSeguimiento':
                    $auditoriaId = REQUEST("auditoriaId");
                    $nombreUsuario= REQUEST("nombreUsuario");
                    $enviarA= REQUEST("enviarA");
                    $numeroUsuarios = REQUEST("numeroUsuarios");
                    $resultado = $repositorio->enviarNotificacionInicioSeguimiento($auditoriaId,$nombreUsuario,$enviarA,$numeroUsuarios);
                break;
                case 'consultarAnos':
                    $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                    $resultado = $repositorio->consultarAnos($usuario,$criteriosSeleccion);
                break;
                default:
                    $resultado->mensajeError = "Acción no implementada";
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
    $administrador_conexion->cerrar($conexion);
    if($resultado!=null)
    {
        $json = json_encode($resultado, JSON_UNESCAPED_UNICODE);
        if (FALSE === $json)
            echo '{"mensajeError":"' .json_last_error_msg() . '"}';
            else
                echo $json;
    }
    
}


