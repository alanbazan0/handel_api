<?php
use php\clases\AdministradorConexion;
use php\clases\JsonMapper;
use php\repositorios\CamposRepositorio;
use php\modelos\Resultado;

error_reporting(E_ALL);
ini_set('display_errors', 1);


include '../clases/JsonMapper.php';
include '../clases/Utilidades.php';
include '../clases/AdministradorConexion.php';
include 'CamposRepositorio.php';



// $origin = "*";
// if(isset($_SERVER['HTTP_ORIGIN']))
//     $origin =$_SERVER['HTTP_ORIGIN'];
// header('Access-Control-Allow-Origin: '.$origin);
// header('Content-Type: application/json; charset=UTF-8');
// header('Access-Control-Allow-Credentials: true');

$administrador_conexion = new AdministradorConexion();
$resultado = new Resultado();
$conexion=null;
try
{
    $conexion = $administrador_conexion->abrir();
    if($conexion)
    {
        $tabla = REQUEST('tabla');
        $singular = REQUEST('singular');
        $repositorio = new CamposRepositorio($conexion);
        
        $resultado = $repositorio->consultarCampos($tabla);
        if($resultado->mensajeError=="")
        {
            $campos = $resultado->valor;
            $html = generarFormulario($tabla,$singular,$campos);
            crearArchivo("codigos/$tabla/html/formularios", strtolower($tabla).".php",$html);
            //echo $html;
            
            division();
            
            $html = generarModelo($tabla,$singular,$campos);
            crearArchivo("codigos/$tabla/php/modelos",upperCamelCase($singular).".php",$html);
            //echo $html;
            
            division();
          
            $html = generarInterface($tabla,$singular,$campos);
            crearArchivo("codigos/$tabla/php/interfaces","I".upperCamelCase($tabla)."Repositorio.php",$html);
            //echo $html;
            
            division();
            
            $html = generarRepositorio($tabla,$singular,$campos);
            crearArchivo("codigos//$tabla/php/repositorios",upperCamelCase($tabla)."Repositorio.php",$html);
           // echo $html;
            
            division();
            
            $html = generarControlador($tabla,$singular,$campos);
            crearArchivo("codigos/$tabla/php/repositorios",upperCamelCase($tabla).".php",$html);
          //  echo $html;
            
            crearZipDescargar($tabla,$singular);
          
        }
    }
    
}
catch(Exception $e)
{
    $resultado->mensajeError = $e->getMessage();
}
finally
{
//     if($resultado!=null)
//     {
//         $json = json_encode($resultado, JSON_UNESCAPED_UNICODE);
//         if (FALSE === $json)
//             echo '{"mensajeError":"' .json_last_error_msg() . '"}';
//             else
//                 echo $json;
//     }
    $administrador_conexion->cerrar($conexion);
}

function crearZipDescargar($tabla, $singular)
{
    $zip = new ZipArchive();
    $filename = "./$tabla.zip";
    if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
        exit("cannot open <$filename>\n");
    }
    $dir = "codigos/$tabla/";
    
    createZip($zip,$dir);
    
    
    $zip->close();
    
    if (file_exists($filename)) {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="'.basename($filename).'"');
        header('Content-Length: ' . filesize($filename));
        header('Expires: 0');
        header('Cache-Control: private');
        header('Pragma: private');
        ob_clean();
        flush();
        $handle = fopen($filename, "rb");
        fpassthru($handle);
        fclose($handle);
        
    }
    
    
}

function createZip($zip,$dir){
    if (is_dir($dir)){
        
        if ($dh = opendir($dir)){
            while (($file = readdir($dh)) !== false){
                
                // If file
                if (is_file($dir.$file)) {
                    if($file != '' && $file != '.' && $file != '..'){
                        
                        $zip->addFile($dir.$file);
                    }
                }else{
                    // If directory
                    if(is_dir($dir.$file) ){
                        
                        if($file != '' && $file != '.' && $file != '..'){
                            
                            // Add empty directory
                            $zip->addEmptyDir($dir.$file);
                            
                            $folder = $dir.$file.'/';
                            
                            // Read data of the folder
                            createZip($zip,$folder);
                        }
                    }
                    
                }
                
            }
            closedir($dh);
        }
    }
}

function guardarArchivo($carpeta,$archivo, $texto)
{
    $nombre_archivo = $carpeta . "\\" . $archivo;
    if($archivo = fopen($nombre_archivo, "w"))
    {
        if(fwrite($archivo, $texto))
        {
            echo "\nEl archivo se guardo correctamente.";
        }
        else
        {
            echo "Ha habido un problema al crear el archivo";
        }
        
        fclose($archivo);
    } 
    if (file_exists($nombre_archivo))
    {
        header('Location:'. $nombre_archivo);
    }
}

function division()
{
    echo "\n--------------------------------------------------------------------------------------------------------";
}

function generarSelect($tabla,$campos)
{
    $sentencia = "SELECT ";
    for($i = 0 ; $i < count($campos); $i++ )
    {
        $campo = $campos[$i];
        $textoCampo = "";
        switch($campo->tipoDato)
        {
            case "varchar":
                $textoCampo = "RTRIM($campo->nombre) as $campo->nombre";
            break;
            case "datetime":
                $textoCampo = "IFNULL(DATE_FORMAT($campo->nombre,'%d/%m/%Y %H:%i:%s'),'') as $campo->nombre";
            break;
            default:
                 $textoCampo = $campo->nombre;
            break;
        }
        $sentencia.= $textoCampo;
        if($i < count($campos)-1)
            $sentencia.=", ";
    }
    $sentencia.=" FROM $tabla";
    return $sentencia;
}

function generarCamposSelectLowerCase($campos)
{
    $camposSelect = "";
    for($i = 0 ; $i < count($campos); $i++ )
    {
        $campo = $campos[$i];
        $camposSelect.= "$".lowerCamelCase($campo->nombre);
        if($i < count($campos) -1)
            $camposSelect.=", ";
    }
    return $camposSelect;
}

function generarCamposSelectLowerCaseObject($campos)
{
    $camposSelect = "";
    for($i = 0 ; $i < count($campos); $i++ )
    {
        $campo = $campos[$i];
        $camposSelect.= "'".lowerCamelCase($campo->nombre)."' => $".lowerCamelCase($campo->nombre);
        if($i < count($campos) -1)
        {
            $camposSelect.=",\n            ";
        }
    }
    return $camposSelect;
}

function getCamposLLave($campos)
{
    $camposLlave = array();
    for($i = 0 ; $i < count($campos); $i++ )
    {
        $campo = $campos[$i];
        if($campo->llave=="PRI")
            array_push($camposLlave,$campo);
    }
    return $camposLlave;
}

function getCamposNoLLave($campos)
{
    $camposNoLlave = array();
    for($i = 0 ; $i < count($campos); $i++ )
    {
        $campo = $campos[$i];
        if($campo->llave!="PRI")
            array_push($camposNoLlave,$campo);
    }
    return $camposNoLlave;
}

function generarInsert($tabla,$campos)
{
    $textoCampos ="";
    $parametros ="";
    for($i = 0 ; $i < count($campos); $i++ )
    {
        $campo = $campos[$i];
        $textoCampos.= $campo->nombre;
        $parametros.="?";
        if($i < count($campos)-1)
        {
            $textoCampos.=", ";
            $parametros.=", ";
        }
    }
    $sentencia = "INSERT INTO $tabla($textoCampos)VALUES($parametros)";
    return $sentencia;
}

function getCamposModelo($campos)
{
    $textoCampos ="";
    for($i = 0 ; $i < count($campos); $i++ )
    {
        $campo = $campos[$i];
        $textoCampos.= "\$modelo->".lowerCamelCase($campo->nombre);
        if($i < count($campos)-1)
        {
            $textoCampos.=", ";
        }
    }
    return $textoCampos;
}

function getTiposDato($campos)
{
    $tipos ="";
    for($i = 0 ; $i < count($campos); $i++ )
    {
        $campo = $campos[$i];
        $tipo = "i";
        switch($campo->tipoDato)
        {
            case "varchar":
              $tipo ="s";
                break;
            case "datetime":
                $tipo ="s";
                break;
            case "int":
            case "bigint":
            case "tinyint":
                $tipo ="i";
            break;
            default:
                $tipo ="s";
            break;
        }
        $tipos.= $tipo;
       
    }
    return $tipos;
}

function generarUpdate($tabla,$camposLlave, $camposNoLlave)
{
    $camposNoLlaveTexto="";
    for($i = 0 ; $i < count($camposNoLlave); $i++ )
    {
        $campo = $camposNoLlave[$i];
        $camposNoLlaveTexto.="$campo->nombre = ?";
        if($i < count($camposNoLlave) -1)
            $camposNoLlaveTexto.=",\n                         ";
    }
    
    $camposLlaveTexto="";
    for($i = 0 ; $i < count($camposLlave); $i++ )
    {
        $campo = $camposLlave[$i];
        $camposLlaveTexto.="$campo->nombre = ?";
        if($i < count($camposLlave) -1)
            $camposLlaveTexto.=",\n    ";
    }
    
    $sentencia = "UPDATE $tabla";
    $sentencia.="\n                     SET ";
    $sentencia.="\n                         $camposNoLlaveTexto";
    $sentencia.="\n                     WHERE $camposLlaveTexto";
    return $sentencia;
}

function generarDelete($tabla, $camposLlave)
{
    $camposLlaveTexto="";
    for($i = 0 ; $i < count($camposLlave); $i++ )
    {
        $campo = $camposLlave[$i];
        $camposLlaveTexto.="$campo->nombre = ?";
        if($i < count($camposLlave) -1)
            $camposLlaveTexto.=" AND ";
    }
    $sentencia = "DELETE FROM $tabla WHERE $camposLlaveTexto";
    return $sentencia;
}

function generarRepositorio($tabla,$singular,$campos)
{
    $clase = upperCamelCase($singular);
    
    $select = generarSelect($tabla,$campos);
    $campoSelectLowerCase = generarCamposSelectLowerCase($campos);
    $campoSelectLowerCaseObject = generarCamposSelectLowerCaseObject($campos);
    $insert = generarInsert($tabla, $campos);
    
    $listaCamposLlave = getCamposLLave($campos);
    $listaCamposNoLlave =  getCamposNoLLave($campos);
    $tiposDato = getTiposDato($campos);
    
    $camposNoLlaveModelo = getCamposModelo($listaCamposNoLlave);
    
    $update = generarUpdate($tabla,$listaCamposLlave,$listaCamposNoLlave);
    
    $delete = generarDelete($tabla,$listaCamposLlave);
   
    $tiposDatoUpdate = getTiposDato($listaCamposNoLlave) .getTiposDato($listaCamposLlave);
  
    $filtros = "";
    
    $tiposDatoLlave =  getTiposDato($listaCamposLlave);
    $camposLlave  = "\$llaves->id";
    
   
    
    
    $codigo ="";
    $codigo.="<?php";
    $codigo.="\nnamespace php\\repositorios;";
    $codigo.="\n";
    $codigo.="\nuse php\interfaces\I".upperCamelCase($tabla)."Repositorio;";
    $codigo.="\nuse php\modelos\\".$clase.";";
    $codigo.="\nuse php\modelos\Resultado;";
    $codigo.="\n";
    $codigo.="\ninclude '../interfaces/I".upperCamelCase($tabla)."Repositorio.php';";
    $codigo.="\ninclude '../modelos/$clase.php';";
    $codigo.="\ninclude 'RepositorioBase.php';";
    $codigo.="\nrequire_once('../clases/Resultado.php');";
    $codigo.="\n";
    $codigo.="\nclass ".upperCamelCase($tabla)."Repositorio extends RepositorioBase implements I".upperCamelCase($tabla)."Repositorio";
    $codigo.="\n{";
    $codigo.="\n    protected \$conexion;";
    $codigo.="\n    protected \$consultaBase;";
    $codigo.="\n    public function __construct(\$conexion)";
    $codigo.="\n    {";
    $codigo.="\n        \$this->conexion = \$conexion;";
    $codigo.="\n        \$this->consultaBase = \"$select\";";
    $codigo.="\n    }";
    $codigo.="\n";
    $codigo.="\n    public function insertar($clase \$modelo)";
    $codigo.="\n    {";
    $codigo.="\n        \$resultado = \$this->calcularId('id','$tabla');";
    $codigo.="\n        if(\$resultado->mensajeError=='')";
    $codigo.="\n        {";
    $codigo.="\n            \$id = \$resultado->valor;";
    $codigo.="\n            \$consulta = \"$insert\";";
    $codigo.="\n            if(\$sentencia = \$this->conexion->prepare(\$consulta))";
    $codigo.="\n            {";
    $codigo.="\n                if(\$sentencia->bind_param('$tiposDato', \$id, $camposNoLlaveModelo))";
    $codigo.="\n                {";
    $codigo.="\n                    if(!\$sentencia->execute())";
    $codigo.="\n                        \$resultado->mensajeError = 'Falló la ejecución (' . \$this->conexion->errno . ') ' . \$this->conexion->error;";
    $codigo.="\n                }";
    $codigo.="\n                else";
    $codigo.="\n                    \$resultado->mensajeError = 'Falló el enlace de parámetros';";
    $codigo.="\n            }";
    $codigo.="\n            else";
    $codigo.="\n                \$resultado->mensajeError = 'Falló la preparación: (' . \$this->conexion->errno . ') ' .\$this->conexion->error;";
    $codigo.="\n        }";
    $codigo.="\n        return \$resultado;";
    $codigo.="\n    }";
    $codigo.="\n"; 
    $codigo.="\n    public function actualizar($clase \$modelo)";
    $codigo.="\n    {";
    $codigo.="\n        \$resultado = new Resultado();";
    $codigo.="\n        \$consulta = \"$update\";";
    $codigo.="\n        if(\$sentencia = \$this->conexion->prepare(\$consulta))";
    $codigo.="\n        {";
    $codigo.="\n            if(\$sentencia->bind_param('$tiposDatoUpdate',$camposNoLlaveModelo ,\$modelo->id ))";
    $codigo.="\n            {";
    $codigo.="\n                if(\$sentencia->execute())";
    $codigo.="\n                {";
    $codigo.="\n                    \$resultado->valor=true;";
    $codigo.="\n                }";
    $codigo.="\n                else";
    $codigo.="\n                    \$resultado->mensajeError = 'Falló la ejecución (' . \$this->conexion->errno . ') ' . \$this->conexion->error;";
    $codigo.="\n            }";
    $codigo.="\n            else"; 
    $codigo.="\n                \$resultado->mensajeError = 'Falló el enlace de parámetros';";
    $codigo.="\n        }";
    $codigo.="\n        else";
    $codigo.="\n            \$resultado->mensajeError = 'Falló la preparación: (' . \$this->conexion->errno . ') ' . \$this->conexion->error;";
    $codigo.="\n        return \$resultado;";
    $codigo.="\n    }";
    $codigo.="\n";
    $codigo.="\n    public function consultar(\$criteriosSeleccion)";
    $codigo.="\n    {";
    $codigo.="\n        \$resultado = new Resultado();";
    $codigo.="\n        \$registros = array();";
    $codigo.="\n        \$filtros = array();";
    $codigo.="\n        \$where='';";
    $codigo.="\n        if(\$criteriosSeleccion!=null)";
    $codigo.="\n        {";
    $codigo.=$filtros;       
    $codigo.="\n            \$where = \$this->where(\$filtros);";
    $codigo.="\n        }";
    $codigo.="\n        \$consulta = \$this->consultaBase . \$where;";
    $codigo.="\n        if(\$sentencia = \$this->conexion->prepare(\$consulta))";
    $codigo.="\n        {";
    $codigo.="\n            if(\$this->bind_param(\$sentencia, \$filtros))";
    $codigo.="\n            {";
    $codigo.="\n                if(\$sentencia->execute())";
    $codigo.="\n                {";
    $codigo.="\n                    if(\$sentencia->bind_result($campoSelectLowerCase))";
    $codigo.="\n                    {";
    $codigo.="\n                        while(\$row = \$sentencia->fetch())";
    $codigo.="\n                        {";
    $codigo.="\n                            \$registro = \$this->crearRegistro($campoSelectLowerCase);";
    $codigo.="\n                            array_push(\$registros,\$registro);";
    $codigo.="\n                        }";
    $codigo.="\n                        \$resultado->valor = \$registros;";
    $codigo.="\n                    }";
    $codigo.="\n                    else";
    $codigo.="\n                        \$resultado->mensajeError = 'Falló el enlace del resultado.';";
    $codigo.="\n                }";
    $codigo.="\n                else";
    $codigo.="\n                    \$resultado->mensajeError = 'Falló la ejecución (' . \$this->conexion->errno . ') ' . \$this->conexion->error;";
    $codigo.="\n            }";
    $codigo.="\n            else";
    $codigo.="\n                \$resultado->mensajeError = 'Falló el enlace de parámetros';";
    $codigo.="\n        }";
    $codigo.="\n        else";
    $codigo.="\n            \$resultado->mensajeError = 'Falló la preparación: (' . \$this->conexion->errno . ') ' . \$this->conexion->error;";
    $codigo.="\n        return \$resultado;";
    $codigo.="\n    }";
    $codigo.="\n"; 
    $codigo.="\n    public function consultarPorLlaves(\$llaves)";
    $codigo.="\n    {";
    $codigo.="\n        \$resultado = new Resultado();";
    $codigo.="\n        \$consulta = \$this->consultaBase .";
    $codigo.="\n        ' WHERE id  = ?';";
    $codigo.="\n        if(\$sentencia = \$this->conexion->prepare(\$consulta))";
    $codigo.="\n        {";
    $codigo.="\n            if(\$sentencia->bind_param('$tiposDatoLlave',$camposLlave))";
    $codigo.="\n            {";
    $codigo.="\n                if(\$sentencia->execute())";
    $codigo.="\n                {";
    $codigo.="\n                    if(\$sentencia->bind_result($campoSelectLowerCase))";
    $codigo.="\n                    {";
    $codigo.="\n                        if(\$sentencia->fetch())";
    $codigo.="\n                        {";
    $codigo.="\n                            \$registro = \$this->crearRegistro($campoSelectLowerCase);";
    $codigo.="\n                            \$resultado->valor = \$registro;";
    $codigo.="\n                        }";
    $codigo.="\n                        else";
    $codigo.="\n                            \$resultado->mensajeError = 'No se encontró ningún resultado.';";
    $codigo.="\n                    }";
    $codigo.="\n                    else";
    $codigo.="\n                        \$resultado->mensajeError = 'Falló el enlace del resultado';";
    $codigo.="\n                }";
    $codigo.="\n                else";
    $codigo.="\n                    \$resultado->mensajeError = 'Falló la ejecución (' . \$this->conexion->errno . ') ' . \$this->conexion->error;";
    $codigo.="\n            }";
    $codigo.="\n            else";
    $codigo.="\n                \$resultado->mensajeError = 'Falló el enlace de parámetros';";
    $codigo.="\n        }";
    $codigo.="\n        else";
    $codigo.="\n            \$resultado->mensajeError = 'Falló la preparación: (' . \$this->conexion->errno . ') ' . \$this->conexion->error;";
    $codigo.="\n        return \$resultado;";
    $codigo.="\n    }";
    $codigo.="\n"; 
    $codigo.="\n    public function eliminar(\$llaves)";
    $codigo.="\n    {";
    $codigo.="\n        \$resultado = new Resultado();";
    $codigo.="\n        \$consulta = \"$delete\";";
    $codigo.="\n        if(\$sentencia = \$this->conexion->prepare(\$consulta))";
    $codigo.="\n        {";
    $codigo.="\n            if(\$sentencia->bind_param('$tiposDatoLlave',$camposLlave))";
    $codigo.="\n            {";
    $codigo.="\n                if(\$sentencia->execute())";
    $codigo.="\n                {";
    $codigo.="\n                    \$resultado->valor = \$llaves->id;";
    $codigo.="\n                }";
    $codigo.="\n                else";
    $codigo.="\n                {";
    $codigo.="\n                    \$resultado->codigoError = \$this->conexion->errno;";
    $codigo.="\n                    \$resultado->mensajeError = 'Falló la ejecución (' . \$this->conexion->errno . ') ' . \$this->conexion->error;";
    $codigo.="\n                }";
    $codigo.="\n            }";
    $codigo.="\n            else";
    $codigo.="\n                \$resultado->mensajeError = 'Falló el enlace de parámetros';";
    $codigo.="\n        }";
    $codigo.="\n        else";
    $codigo.="\n        {";
    $codigo.="\n            \$resultado->codigoError = \$this->conexion->errno;";
    $codigo.="\n            \$resultado->mensajeError = 'Falló la preparación: (' . \$this->conexion->errno . ') ' . \$this->conexion->error;";
    $codigo.="\n        }";
    $codigo.="\n        return \$resultado;";
    $codigo.="\n    }";
    $codigo.="\n"; 
    $codigo.="\n    private function crearRegistro($campoSelectLowerCase)";
    $codigo.="\n    {";
    $codigo.="\n        \$registro= (object) ";
    $codigo.="\n        ["; 
    $codigo.="\n            $campoSelectLowerCaseObject";
    $codigo.="\n        ];";
    $codigo.="\n        return \$registro;";
    $codigo.="\n    }";
    
    $codigo.="\n}";
    return $codigo;
}

function generarControlador($tabla,$singular,$campos)
{
    $clase = upperCamelCase($singular);
    $codigo ="";
    $codigo.="<?php";
    $codigo.="\nuse php\clases\AdministradorConexion;";
    $codigo.="\nuse php\clases\JsonMapper;";
    $codigo.="\nuse php\modelos\\".$clase.";";
    $codigo.="\nuse php\\repositorios\\".upperCamelCase($tabla)."Repositorio;";
    $codigo.="\nuse php\modelos\Resultado;";
    $codigo.="\n";
    $codigo.="\nerror_reporting(E_ALL);";
    $codigo.="\nini_set('display_errors', 1);";
    $codigo.="\n";
    $codigo.="\ninclude '../clases/JsonMapper.php';";
    $codigo.="\ninclude '../clases/Utilidades.php';";
    $codigo.="\ninclude '../clases/AdministradorConexion.php';";
    $codigo.="\ninclude '../repositorios/".upperCamelCase($tabla)."Repositorio.php';";
    $codigo.="\n";
    $codigo.="\n\$origin = \"*\";";
    $codigo.="\nif(isset(\$_SERVER['HTTP_ORIGIN']))";
    $codigo.="\n  \$origin =\$_SERVER['HTTP_ORIGIN'];";
    $codigo.="\nheader('Access-Control-Allow-Origin: '.\$origin);";
    $codigo.="\nheader('Content-Type: application/json; charset=UTF-8');";
    $codigo.="\nheader('Access-Control-Allow-Credentials: true');";
    $codigo.="\n";
    $codigo.="\n\$administrador_conexion = new AdministradorConexion();";
    $codigo.="\n\$resultado = new Resultado();";
    $codigo.="\n\$conexion=null;";
    $codigo.="\ntry";
    $codigo.="\n{";
    $codigo.="\n    \$conexion = \$administrador_conexion->abrir();";
    $codigo.="\n    if(\$conexion)";
    $codigo.="\n    {";
    $codigo.="\n        \$accion = REQUEST('accion');";
    $codigo.="\n        \$repositorio = new ".upperCamelCase($tabla)."Repositorio(\$conexion);";
    $codigo.="\n        switch(\$accion)";
    $codigo.="\n        {";
    $codigo.="\n            case 'insertar':";
    $codigo.="\n                \$json = json_decode(REQUEST('modelo'));";
    $codigo.="\n                \$mapper = new JsonMapper();";
    $codigo.="\n                \$modelo = \$mapper->map(\$json, new $clase());";
    $codigo.="\n                \$resultado = \$repositorio->insertar(\$modelo);";
    $codigo.="\n            break;";
    $codigo.="\n            case 'actualizar':";
    $codigo.="\n                \$json = json_decode(REQUEST('modelo'));";
    $codigo.="\n                \$mapper = new JsonMapper();";
    $codigo.="\n                \$modelo = \$mapper->map(\$json, new $clase());";
    $codigo.="\n                \$resultado = \$repositorio->actualizar(\$modelo) ;";
    $codigo.="\n            break;";
    $codigo.="\n            case 'consultar':";
    $codigo.="\n                \$criteriosSeleccion = json_decode(REQUEST('criteriosSeleccion'));";
    $codigo.="\n                \$resultado = \$repositorio->consultar(\$criteriosSeleccion);";
    $codigo.="\n            break;";
    $codigo.="\n            case 'consultarPorLlaves':";
    $codigo.="\n                \$llaves = json_decode(REQUEST('llaves'));";
    $codigo.="\n                \$resultado = \$repositorio->consultarPorLlaves(\$llaves);";
    $codigo.="\n            break;";
    $codigo.="\n            case 'eliminar':";
    $codigo.="\n                \$llaves = json_decode(REQUEST('llaves'));";
    $codigo.="\n                \$resultado = \$repositorio->eliminar(\$llaves);";
    $codigo.="\n            break;";
    $codigo.="\n            default:";
    $codigo.="\n                \$resultado->mensajeError = 'Acción no válida';";
    $codigo.="\n            break;";
    $codigo.="\n        }";
    $codigo.="\n    }";
    $codigo.="\n}"; 
    $codigo.="\ncatch(Exception \$e)";
    $codigo.="\n{";
    $codigo.="\n    \$resultado->mensajeError = \$e->getMessage();";
    $codigo.="\n}";
    $codigo.="\nfinally";
    $codigo.="\n{";
    $codigo.="\n    if(\$resultado!=null)";
    $codigo.="\n    {";
    $codigo.="\n        \$json = json_encode(\$resultado, JSON_UNESCAPED_UNICODE);";
    $codigo.="\n        if (FALSE === \$json)";
    $codigo.="\n            echo '{\"mensajeError\":\"' .json_last_error_msg() . '\"}';";
    $codigo.="\n        else";
    $codigo.="\n            echo \$json;";
    $codigo.="\n    }";
    $codigo.="\n    \$administrador_conexion->cerrar(\$conexion);";
    $codigo.="\n}";
    return $codigo;
        
        
}

function crearArchivo($carpeta,$archivo,$texto)
{
  
    crearCarpeta($carpeta);
    if (file_exists($carpeta)) 
    {
        $nombreArchivo = $carpeta.'/'.$archivo;
        $file = fopen($nombreArchivo, "w");
        fwrite($file, $texto . PHP_EOL);
        fclose($file);
    }
}

function crearCarpeta($carpeta)
{
    if (!file_exists($carpeta)) 
    {
        mkdir($carpeta, 0777, true);
    }
}

function generarInterface($tabla,$singular,$campos)
{
    $clase = upperCamelCase($singular);
    $codigo = "<?php";
    $codigo.="\nnamespace php\interfaces;";
    $codigo.="\n";
    $codigo.="\nuse php\modelos\\$clase;";
    $codigo.="\n";
    $codigo.="\ninterface I".upperCamelCase($tabla)."Repositorio";
    $codigo.="\n{";
    $codigo.="\n    public function insertar($clase \$modelo);";
    $codigo.="\n    public function actualizar($clase \$modelo);";
    $codigo.="\n    public function consultarPorLlaves(\$id);";
    $codigo.="\n    public function consultar(\$criteriosSeleccion);";
    $codigo.="\n    public function eliminar(\$llaves);";
    $codigo.="\n}";
    return $codigo;
}

function generarModelo($tabla,$singular,$campos)
{
    $clase = upperCamelCase($singular);
    $codigo = "<?php";
    $codigo.="\nnamespace php\modelos;";
    $codigo.="\n";
    $codigo.="\nclass $clase";
    $codigo.="\n{";
    for($i = 0 ; $i < count($campos); $i++ )
    {
        $campo = $campos[$i];
        $codigo.="\n  public $".lowerCamelCase($campo->nombre).";";
    }
    $codigo.="\n}";
    return $codigo;
}

function generarFormulario($tabla,$singular,$campos)
{
    $titulo = ucwords($singular);
    $html="<?php";
    $html.="\n\$origin = '*';";
    $html.="\nif(isset(\$_SERVER['HTTP_ORIGIN']))";
    $html.="\n    \$origin =\$_SERVER['HTTP_ORIGIN'];";
    $html.="\nheader('Access-Control-Allow-Origin: '.\$origin);";
    $html.="\nheader('Content-Type: text/html; charset=utf-8');";
    $html.="\nheader('Access-Control-Allow-Credentials: true');";
    $html.="\n?>";
    $html.="\n<div class='modal fade' id='modalAlta' tabindex='-1' role='dialog' aria-labelledby='scrollmodalLabel' aria-hidden='true'>";
    $html.="\n  <div class='modal-dialog modal-lg' role='document'> ";
    $html.="\n      <div class='modal-content'>";
    $html.="\n          <div class='modal-header'>";
    $html.="\n              <h5 class='modal-title' id='scrollmodalLabel'>$titulo</h5>";
    $html.="\n              <button type='button' class='close' data-dismiss='modal' aria-label='Close'>";
    $html.="\n                  <span aria-hidden='true'>&times;</span>";
    $html.="\n              </button>";
    $html.="\n          </div>";
    $html.="\n          <div class='modal-body'>";
    $html.="\n              <form id='formulario' action='#'  method='post'>";
    $html.= generarCamposFormulario($campos);
    $html.="\n              </form>";
    $html.="\n          </div>";
    $html.="\n          <div class='modal-footer'>";
    $html.="\n              <button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancelar</button>";
    $html.="\n              <button  id='guardarButton' type='submit' class='btn btn-primary' >Guardar</button>";
    $html.="\n          </div>";
    $html.="\n      </div>";
    $html.="\n  </div>";
    $html.="\n</div>";
   return $html;
}

function generarCamposFormulario($campos)
{
    $html = "";
    for($i = 0 ; $i < count($campos); $i++ )
    {
        $campo = $campos[$i];
        
        if($campo->llave!="PRI")
        {
            
            $titulo ="";
            if($campo->comentario!="")
            {
                $titulo = $campo->comentario;
            }
            else
            {
                $titulo = str_replace("_"," ",$campo->nombre);
                $titulo = ucfirst($titulo);
            }
            
            $nombreComponente = lowerCamelCase($campo->nombre);
            
            $html.="\n                  <div class='form-group'>";  
            $html.="\n                      <div>";
            $html.="\n                          <label class='control-label mb-1'>$titulo</label>";
            
            if($campo->llave=="MUL")
                $html.="\n                          <select id='".$nombreComponente."Select' name='".$nombreComponente."Select' class='form-control'></select>";
            else 
                $html.="\n                          <input  id='".$nombreComponente."Input' name='".$nombreComponente."Input' type='text' class='form-control'>";
                
            $html.="\n                      </div>";
            $html.="\n                  </div>";  
        }
        
    }
    return $html;
}

function sangria($texto,$nivel)
{
    $texto = 0;
    for($i = 0 ; $i < $nivel; $i++ )
    {
        
    }
    
}


function lowerCamelCase($nombre)
{
    $palabras = explode("_",$nombre);
    $variable = "";
    for($i = 0 ; $i < count($palabras); $i++ )
    {
        $palabra = $palabras[$i];
        if($i==0)
            $variable.= $palabra;
        else
            $variable.= ucfirst($palabra);
    }
    return $variable;
}



function upperCamelCase($nombre)
{
    $palabras = explode("_",$nombre);
    $variable = "";
    for($i = 0 ; $i < count($palabras); $i++ )
    {
        $palabra = $palabras[$i];
        $variable.= ucfirst($palabra);
    }
    return $variable;
}

