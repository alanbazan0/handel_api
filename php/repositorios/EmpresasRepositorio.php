<?php
namespace php\repositorios;

use php\interfaces\IEmpresasRepositorio;
use php\modelos\Empresa;
use php\modelos\Resultado;

include "../interfaces/IEmpresasRepositorio.php";
include "../modelos/Empresa.php";
require_once("RepositorioBase.php");
include "../clases/TipoUsuario.php";
require_once("../clases/Resultado.php");

class EmpresasRepositorio extends RepositorioBase implements IEmpresasRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = " SELECT E.id, E.nombre, IFNULL(E.nombre_corto,'') nombre_corto , E.tipo_empresa_id, T.nombre tipo_empresa, E.direccion, E.pais_id, P.nombre pais, E.estado_id, ES.nombre estado, E.ciudad_id, C.nombre ciudad, E.telefono, E.corporativo_id , IFNULL(CO.nombre,'') corporativo, E.fecha_alta, E.fecha_modificacion, E.estatus " .
                            " FROM empresas E " .
                            "   LEFT JOIN tipos_empresa T ON T.id = E.tipo_empresa_id " .
                            "   LEFT JOIN paises P ON P.id = E.pais_id " .
                            "   LEFT JOIN estados ES ON ES.id = E.estado_id " .
                            "   LEFT JOIN ciudades C ON C.id = E.ciudad_id " .
                            "   LEFT JOIN empresas CO ON CO.id = E.corporativo_id";
    } 
 
    public function insertar(Empresa $modelo)
    {        
        $resultado =  $this->calcularId("id","empresas");
        if($modelo->corporativoId=="")
            $modelo->corporativoId=null;
        if($resultado->mensajeError=="")
        {
            $id = $resultado->valor;           
            $consulta = "INSERT INTO empresas(id, nombre, nombre_corto, tipo_empresa_id, direccion, pais_id, estado_id, ciudad_id, telefono, corporativo_id, fecha_alta, fecha_modificacion, estatus) " .
                        "VALUE(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if( $sentencia->bind_param("issisiiiisi", $id, $modelo->nombre,$modelo->nombreCorto, $modelo->tipoEmpresaId, $modelo->direccion, $modelo->paisId, $modelo->estadoId, $modelo->ciudadId, $modelo->telefono, $modelo->corporativoId, $modelo->estatus))
                {
                    if($sentencia->execute())       
                        $resultado->valor = $id;
                    else    
                        $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;                       
                }
                else
                    $resultado->mensajeError = "Falló el enlace de parámetros";   
            }
            else
                $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;   
        }   
        return $resultado;
    }
    
    public function actualizar(Empresa $modelo)
    {
        if($modelo->corporativoId=="")
            $modelo->corporativoId=null;
        $resultado = new Resultado();
        $consulta = " UPDATE empresas " .
                     "SET nombre = ?, " .
                     " nombre_corto = ?, " .
                     " tipo_empresa_id = ?, " .
                     "  direccion = ?, ".
                     "  pais_id = ? , " .
                     "  estado_id = ? , " .
                     "  ciudad_id = ? , " .
                     "  telefono = ?, " .
                     "  corporativo_id = ?, " .
                     "  estatus = ?, " .
                     "  fecha_modificacion= NOW() " .
                     "WHERE id = ? ";    

        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("ssisiiisiii", $modelo->nombre, $modelo->nombreCorto, $modelo->tipoEmpresaId,$modelo->direccion,$modelo->paisId,$modelo->estadoId,$modelo->ciudadId, $modelo->telefono, $modelo->corporativoId, $modelo->estatus,$modelo->id ))
            {
                if($sentencia->execute())
                {
                    $resultado->valor=true;
                }
                else
                    $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else  $resultado->mensajeError = "Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
        return $resultado;
    }    
  
    public function consultar($criteriosSeleccion,$opcional, $usuario)
    {     
        $resultado = new Resultado();
        $registros = array(); 
        
        $filtros = array(); 
        $where="";
        if($criteriosSeleccion!=null)
        {
            if(isset($criteriosSeleccion->nombre))
                array_push($filtros,(object)['tipoDato'=>'varchar','tabla' => 'E', 'campo'=>'nombre','valor'=>$criteriosSeleccion->nombre]);
        }
        if($usuario!=null)
        {
            if($usuario->tipoUsuarioId == \TipoUsuario::SUPERVISOR || $usuario->tipoUsuarioId == \TipoUsuario::COORDINADOR)
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'E', 'campo'=>'id','valor'=>$usuario->empresaId]);
        }
        
        $where = $this->where($filtros);
        
        $consulta = $this->consultaBase .
                 $where . " order by E.nombre";      
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {                
                    if ($sentencia->bind_result($id, $nombre, $nombreCorto, $tipo_empresa_id, $tipo_empresa, $direccion, $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $telefono, $corporativo_id, $corporativo, $fecha_alta, $fecha_modificacion, $estatus))
                    {                    
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre,$nombreCorto, $tipo_empresa_id, $tipo_empresa, $direccion, $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $telefono, $corporativo_id, $corporativo, $fecha_alta, $fecha_modificacion, $estatus);
                      
                            array_push($registros,$registro);
                        }
                        if($opcional=="true")
                        {
                            if($usuario->tipoUsuarioId == \TipoUsuario::ADMINISTRADOR)
                            {
                                $registro = $this->crearRegistro("", "Todas las empresas","",null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
                                array_unshift($registros, $registro);
                            }
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
        $consulta = $this->consultaBase .
        " WHERE E.id  = ?";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$llaves->id))
            {
                if($sentencia->execute())
                {                    
                    if ($sentencia->bind_result($id, $nombre,$nombreCorto, $tipo_empresa_id, $tipo_empresa, $direccion, $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $telefono,$corporativo_id, $corporativo, $fecha_alta, $fecha_modificacion, $estatus))
                    {                        
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre, $nombreCorto, $tipo_empresa_id, $tipo_empresa, $direccion, $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $telefono, $corporativo_id, $corporativo, $fecha_alta, $fecha_modificacion, $estatus);
                            $resultado->valor = $registro;
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
    
    public function consultarCorporativo($empresaId)
    {
        $resultado = new Resultado();
        $consulta = $this->consultaBase .
        " WHERE E.id != ?";
        $registros = array(); 
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$empresaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombre,$nombreCorto, $tipo_empresa_id, $tipo_empresa, $direccion, $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $telefono,$corporativo_id, $corporativo, $fecha_alta, $fecha_modificacion, $estatus))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre,$nombreCorto, $tipo_empresa_id, $tipo_empresa, $direccion, $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $telefono, $corporativo_id, $corporativo, $fecha_alta, $fecha_modificacion, $estatus);
                            
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros; 
                        
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
    
    private function crearRegistro($id, $nombre,$nombreCorto, $tipo_empresa_id, $tipo_empresa, $direccion, $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $telefono,$corporativo_id, $corporativo, $fecha_alta, $fecha_modificacion, $estatus)
    {
        $archivoIcono = '../../php/logos_empresas/logo'.$id.'.png';
        $icono = 'default.png';
        if(file_exists($archivoIcono))
            $icono = 'logo'.$id.'.png';
        
            
        $registro = (object) [
            'id' =>  $id,
            'nombre' => $nombre,
            'nombreCorto' => $nombreCorto,
            'icono' => $icono,
            'tipoEmpresaId' => $tipo_empresa_id,
            'tipoEmpresa' => $tipo_empresa,
            'direccion' => $direccion,
            'paisId' => $pais_id,
            'pais' => $pais,
            'estadoId' => $estado_id,
            'estado' => $estado,
            'ciudadId' => $ciudad_id,
            'ciudad' => $ciudad,
            'telefono' => $telefono,
            'corporativoId' => $corporativo_id,
            'corporativo' => $corporativo,
            'fechaAlta' => $fecha_alta,
            'fechaModificacion' => $fecha_modificacion,
            'estatus' => $estatus            
        ];
        return $registro;
    }
    
    public function eliminar($llaves)
    {
        $resultado = new Resultado();
        $consulta = " DELETE FROM empresas "
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
                $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
                $resultado->codigoError = $this->conexion->errno;
            }
            return $resultado;
    }

    
}

