<?php
use php\clases\AdministradorConexion;
use php\clases\AdministradorArchivos;
use php\clases\JsonMapper;
use php\modelos\Plantilla;
use php\repositorios\PlantillasRepositorio;
use php\modelos\Resultado;

error_reporting(E_ALL);
ini_set('display_errors', 1);


include '../clases/JsonMapper.php';
include '../clases/Utilidades.php';
include '../clases/AdministradorConexion.php';
include '../clases/AdministradorArchivos.php';
include '../repositorios/PlantillasRepositorio.php';


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
        $repositorio = new PlantillasRepositorio($conexion);
        switch ($accion)
        {           
            case 'insertar':               
                $json = json_decode(REQUEST('modelo'));
                $mapper = new JsonMapper();
                $modelo = $mapper->map($json, new Plantilla());                   
                $resultado = $repositorio->insertar($modelo);         
                if($resultado->mensajeError=="")
                {
                    $id =  $resultado->valor;
                    $adminstradorArchivos = new AdministradorArchivos();
                    $archivo = FILES("file");
                    $carpeta = "iconos_plantillas";
                    $nombreArchivo = "plantilla".$modelo->id.".png";
                    $resultado=$adminstradorArchivos->subirImagen($carpeta,$archivo,$nombreArchivo);
                    $resultado->valor = $id;
                }
            break;
            case 'actualizar':
                $json = json_decode(REQUEST('modelo'));
                $mapper = new JsonMapper();
                $modelo = $mapper->map($json, new Plantilla());
                $resultado = $repositorio->actualizar($modelo) ;
                if($resultado->mensajeError=="")
                {
                    $id =  $resultado->valor;
                    $adminstradorArchivos = new AdministradorArchivos();
                    $archivo = FILES("file");
                    $carpeta = "iconos_plantillas";
                    $nombreArchivo = "plantilla".$modelo->id.".png";
                    $resultado=$adminstradorArchivos->subirImagen($carpeta,$archivo,$nombreArchivo);
                    $resultado->valor = $id;
                }
            break;
            case 'consultar':
                $criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));
                $resultado = $repositorio->consultar($criteriosSeleccion);               
            break;
            case 'ordenarPreguntas':
                $plantillaId = REQUEST('plantillaId');
                $seccionId = REQUEST('seccionId');
                $seleccion = REQUEST("seleccion");
                $resultado = $repositorio->ordenarPreguntas($plantillaId,$seccionId,$seleccion);        
            break;
            case 'consultarPorLlaves':
                
                $llaves = json_decode(REQUEST('llaves'));
                $resultado = $repositorio->consultarPorLlaves($llaves);
            break;          
            case 'eliminarPregunta':
                $llaves = json_decode(REQUEST('llaves'));
                $resultado = $repositorio->eliminarPregunta($llaves);
                if($resultado->mensajeError=="")
                {
                   //TODO: Eliminar valores de preguntas en la ejecucion
                }
            break;
            case 'eliminarSeccion':
                $llaves = json_decode(REQUEST('llaves'));
                $resultado = $repositorio->eliminarSeccion($llaves);
                if($resultado->mensajeError=="")
                {
                    //TODO: Eliminar valores de seccion en la ejecucion
                }
                break;
            case 'insertarPregunta':
                $plantillaId = REQUEST('plantillaId');
                $seccionId = REQUEST('seccionId');
                $tipo = REQUEST('tipo');
                $resultado = $repositorio->insertarPregunta($plantillaId, $seccionId,  $tipo);
            break;
            case 'actualizarValor':
                $plantillaId = REQUEST('plantillaId');
                $campo = REQUEST('campo');
                $valor = REQUEST('valor');
                $resultado = $repositorio->actualizarValor($plantillaId, $campo,  $valor);
            break;
            case 'insertarSeccion':
                $plantillaId = REQUEST('plantillaId');
                $resultado = $repositorio->insertarSeccion($plantillaId,"");
           break;
            case 'eliminar':
                $llaves = json_decode(REQUEST('llaves'));
                $resultado = $repositorio->eliminar($llaves);
                if($resultado->mensajeError=="")
                {
                    $adminstradorArchivos = new AdministradorArchivos();
                    $carpeta = "iconos_plantillas";
                    $nombreArchivo = "plantilla".$llaves->id.".png";
                    $resultado=$adminstradorArchivos->eliminar($carpeta,$nombreArchivo);
                }
                
            break;
            case 'copiar':
                $llaves = json_decode(REQUEST('llaves'));
                $resultado = $repositorio->copiar($llaves);
                if($resultado->correcto())
                {
                    $id =  $resultado->valor;
                    $adminstradorArchivos = new AdministradorArchivos();
                    $carpeta = "iconos_plantillas";
                    $nombreArchivo = "plantilla".$llaves->id.".png";
                    $nombreArchivoCopia = "plantilla".$id.".png";
                    $resultado=$adminstradorArchivos->copiar($carpeta,$nombreArchivo,$nombreArchivoCopia);
                    $resultado->valor = $id;
                }
            break;
            case 'guardarRespuestasSi':
                $plantillaId = REQUEST('plantillaId');
                $seccionId = REQUEST('seccionId');
                $preguntaId = REQUEST('preguntaId');
                $json = json_decode(REQUEST('respuestas'));
                $mapper = new JsonMapper();
                $respuestas = $mapper->mapArray($json, array());
                $resultado = $repositorio->guardarRespuestasSi($plantillaId,$seccionId,$preguntaId,$respuestas);
            break;
            case 'guardarRespuestasNo':
                $plantillaId = REQUEST('plantillaId');
                $seccionId = REQUEST('seccionId');
                $preguntaId = REQUEST('preguntaId');
                $json = json_decode(REQUEST('respuestas'));
                $mapper = new JsonMapper();
                $respuestas = $mapper->mapArray($json, array());
                $resultado = $repositorio->guardarRespuestasNo($plantillaId,$seccionId,$preguntaId,$respuestas);
            break;
            case 'ordenarSecciones':
                $plantillaId = REQUEST('plantillaId');
                $seleccion = REQUEST("seleccion");
                $resultado = $repositorio->ordenarSecciones($plantillaId,$seleccion);
            break;
            case 'actualizarValorPregunta':
                $plantillaId = REQUEST('plantillaId');
                $seccionId = REQUEST('seccionId');
                $preguntaId = REQUEST('preguntaId');
                $campo = REQUEST('campo');
                $valor = REQUEST('valor');
                $resultado = $repositorio->actualizarValorPregunta($plantillaId, $seccionId, $preguntaId, $campo,  $valor);
            break;
            case 'actualizarCategoriasPregunta':
                $plantillaId = REQUEST('plantillaId');
                $seccionId = REQUEST('seccionId');
                $preguntaId = REQUEST('preguntaId');
                $mapper = new JsonMapper();
                $categorias = $mapper->mapArray(json_decode(REQUEST('categorias')), array());
                $resultado = $repositorio->actualizarCategoriasPregunta($plantillaId, $seccionId, $preguntaId, $categorias);
            break;
            case 'actualizarValorSeccion':
                $plantillaId = REQUEST('plantillaId');
                $seccionId = REQUEST('seccionId');
                $campo = REQUEST('campo');
                $valor = REQUEST('valor');
                $resultado = $repositorio->actualizarValorSeccion($plantillaId, $seccionId, $campo,  $valor);
            break;
            default:
                $resultado->mensajeError = "Acción no válida";
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


