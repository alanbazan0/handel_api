<?php
namespace php\repositorios;

use php\interfaces\ISedesRepositorio;
use php\modelos\Sede;
use php\modelos\Resultado;

include "../interfaces/ISedesRepositorio.php";
include "../modelos/Sede.php";
include "../clases/TipoUsuario.php";
include "RepositorioBase.php";
require_once("UsuariosRepositorio.php");
require_once("../clases/Resultado.php");

class SedesRepositorio extends RepositorioBase implements ISedesRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = " SELECT S.id, S.nombre, IFNULL(S.nombre_corto,''), IFNULL(S.direccion,''), E.id empresaId, E.nombre empresaNombre, S.pais_id, P.nombre pais, S.estado_id, ES.nombre estado, S.ciudad_id, C.nombre ciudad, S.fecha_alta, S.fecha_modificacion, S.estatus " .
                                " FROM sedes S " .
                                "   INNER JOIN empresas E on E.id = S.empresa_id ".                             
                                "   LEFT JOIN paises P ON P.id = S.pais_id " .
                                "   LEFT JOIN estados ES ON ES.id = S.estado_id " .
                                "   LEFT JOIN ciudades C ON C.id = S.ciudad_id ";
    } 
 
    public function insertar(Sede $modelo)
    {        
        $resultado =  $this->calcularId("id","sedes");
        if($resultado->mensajeError=="")
        {
            $id = $resultado->valor;           
            $consulta = "INSERT INTO sedes(id, nombre, nombre_corto, direccion, empresa_id, pais_id, estado_id, ciudad_id, fecha_alta, fecha_modificacion, estatus) " .
                        "VALUE(?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if( $sentencia->bind_param("isssiiiii", $id, $modelo->nombre,$modelo->nombreCorto, $modelo->direccion,$modelo->empresaId, $modelo->paisId, $modelo->estadoId, $modelo->ciudadId, $modelo->estatus))
                {
                    if(!$sentencia->execute())                
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
    
    public function actualizar(Sede $modelo)
    {
        $resultado = new Resultado();
        $consulta = " UPDATE sedes " .
                     "SET nombre = ?, " .
                     "  nombre_corto = ?, ".
                     "  direccion = ?, ".
                     "  empresa_id = ?, " .
                     "  pais_id = ? , " .
                     "  estado_id = ? , " .
                     "  ciudad_id = ? , " .
                     "  estatus = ?, " .
                     "  fecha_modificacion= NOW() " .
                     "WHERE id = ? ";    

        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("sssiiiiii", $modelo->nombre,$modelo->nombreCorto, $modelo->direccion, $modelo->empresaId, $modelo->paisId, $modelo->estadoId, $modelo->ciudadId, $modelo->estatus,$modelo->id ))
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
  
    public function consultar($criteriosSeleccion)
    {     
        $resultado = new Resultado();
        $registros = array();     
        $filtros = array();
        $where="";
        if($criteriosSeleccion!=null)
        {
            if(isset($criteriosSeleccion->nombre))
                array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'S','campo'=>'nombre','valor'=>$criteriosSeleccion->nombre]);
            if(isset($criteriosSeleccion->empresaId))
            {
                if($criteriosSeleccion->empresaId!="" && $criteriosSeleccion->empresaId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'S','campo'=>'empresa_id','valor'=>$criteriosSeleccion->empresaId]);
            }
            $where = $this->where($filtros);
        }
        $consulta = $this->consultaBase .
        $where . " order by S.nombre";     
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {                
                    if ($sentencia->bind_result($id, $nombre, $nombreCorto, $direccion, $empresaId, $empresaNombre, $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad,$fechaAlta, $fechaModificacion, $estatus))
                    {                    
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre,$nombreCorto, $direccion,$empresaId, $empresaNombre,$pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $fechaAlta, $fechaModificacion, $estatus);
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
        $consulta = $this->consultaBase .
                    " WHERE S.id  = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$llaves->id))
            {
                if($sentencia->execute())
                {                    
                    if ($sentencia->bind_result($id, $nombre, $nombreCorto,$direccion,$empresaId, $empresaNombre,$pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad,  $fechaAlta, $fechaModificacion, $estatus))
                    {                        
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre,$nombreCorto, $direccion,$empresaId, $empresaNombre, $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $fechaAlta, $fechaModificacion, $estatus);
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
    
    private function crearRegistro($id, $nombre, $nombreCorto, $direccion, $empresaId, $empresaNombre, $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $fechaAlta, $fechaModificacion, $estatus)
    {
        $registro= (object) [
            'id' =>  $id,
            'nombre' => $nombre,
            'nombreCorto' => $nombreCorto,
            'direccion' => $direccion,
            'empresaId' => $empresaId,
            'empresaNombre' => $empresaNombre,
            'paisId' => $pais_id,
            'pais' => $pais,
            'estadoId' => $estado_id,
            'estado' => $estado,
            'ciudadId' => $ciudad_id,
            'ciudad' => $ciudad,
            'fechaAlta' => $fechaAlta,
            'fechaModificacion' => $fechaModificacion,
            'estatus' => $estatus
        ];
        return $registro;
    }
    
    public function consultarPorEmpresaUsuario($empresaId,$opcional,$usuario)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $filtros = array();
        $where="";
        if($usuario->tipoUsuarioId == \TipoUsuario::ADMINISTRADOR)
        {
            if($empresaId!="")
                array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'S','campo'=>'empresa_id','valor'=>$empresaId]);
        }
        else 
        {
            if($empresaId!="")
                array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'S','campo'=>'empresa_id','valor'=>$empresaId]);
            else
            {
                $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
                $resultado = $usuariosRepositorio->consultarIdsEmpresas($usuario->empresaId);
                if($resultado->correcto())
                {
                    $empresasIds = implode(",", $resultado->valor);
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'E', 'campo'=>'id','operador'=>'IN','valor'=>$empresasIds]);
                }
            }
            
        }
        
//         else  if($usuario->tipoUsuarioId == \TipoUsuario::COORDINADOR)
//             array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'S','campo'=>'empresa_id','valor'=>$empresaId]);
//         else  if($usuario->tipoUsuarioId == \TipoUsuario::SUPERVISOR)
//         {
//             array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'S','campo'=>'empresa_id','valor'=>$usuario->empresaId]);
//             array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'S','campo'=>'id','valor'=>$usuario->sedeId]);
//         }
//         else if($usuario->recursosHumanos==1)
//             array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'S','campo'=>'empresa_id','valor'=>$empresaId]);
        
        $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
        $resultado = $usuariosRepositorio->consultarIdsEmpresas($usuario->empresaId);
        if($resultado->correcto())
        {
            $empresasIds = implode(",", $resultado->valor);
            array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'E', 'campo'=>'id','operador'=>'IN','valor'=>$empresasIds]);
        }
        
        
        $where = $this->where($filtros);
                
                //         $consulta = $this->consultaBase .
                //                   " WHERE S.empresa_id  = ?";
                $consulta = $this->consultaBase .
                $where. " ORDER BY S.nombre";
                
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if($this->bind_param($sentencia, $filtros))
                    {
                        if($sentencia->execute())
                        {
                            if ($sentencia->bind_result($id, $nombre, $nombreCorto, $direccion,$empresaId, $empresaNombre,  $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $fechaAlta, $fechaModificacion, $estatus))
                            {
                                while($row = $sentencia->fetch())
                                {
                                    $registro = $this->crearRegistro($id, $nombre, $nombreCorto, $direccion,$empresaId, $empresaNombre, $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $fechaAlta, $fechaModificacion, $estatus);
                                    array_push($registros,$registro);
                                }
                                if($opcional=="true")
                                {
                                    if($usuario->tipoUsuarioId == \TipoUsuario::ADMINISTRADOR || $usuario->tipoUsuarioId == \TipoUsuario::COORDINADOR  )
                                    {
                                        $registro = $this->crearRegistro("", "Todas las sedes",null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
                                        array_unshift($registros, $registro);
                                    }
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
    
    public function consultarPorEmpresa($empresaId,$opcional,$usuario)
    {
        $resultado = new Resultado();
        $registros = array();    
        
        $filtros = array(); 
        $where="";
        if($usuario->tipoUsuarioId == \TipoUsuario::ADMINISTRADOR)
        {
//             if($opcional=="true")
//             {
//                 if($empresaId!="")
//                     array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'S','campo'=>'empresa_id','valor'=>$empresaId]);
//             }
//             else
//             {
//                 array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'S','campo'=>'empresa_id','valor'=>$empresaId]);
//             }
                array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'S','campo'=>'empresa_id','valor'=>$empresaId]);
              
        }
        else  if($usuario->tipoUsuarioId == \TipoUsuario::COORDINADOR)
                array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'S','campo'=>'empresa_id','valor'=>$empresaId]);
        else  if($usuario->tipoUsuarioId == \TipoUsuario::SUPERVISOR)
        {
            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'S','campo'=>'empresa_id','valor'=>$usuario->empresaId]);
            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'S','campo'=>'id','valor'=>$usuario->sedeId]);
        }
        else if($usuario->recursosHumanos==1)
            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'S','campo'=>'empresa_id','valor'=>$empresaId]);
        $where = $this->where($filtros);
        
//         $consulta = $this->consultaBase .
//                   " WHERE S.empresa_id  = ?";
        $consulta = $this->consultaBase .
                    $where. " ORDER BY S.nombre";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombre, $nombreCorto, $direccion,$empresaId, $empresaNombre,  $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $fechaAlta, $fechaModificacion, $estatus))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre, $nombreCorto, $direccion,$empresaId, $empresaNombre, $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $fechaAlta, $fechaModificacion, $estatus);
                            array_push($registros,$registro);
                        }
                        if($opcional=="true")
                        {
                            if($usuario->tipoUsuarioId == \TipoUsuario::ADMINISTRADOR || $usuario->tipoUsuarioId == \TipoUsuario::COORDINADOR || $usuario->recursosHumanos==1  )
                            {
                                $registro = $this->crearRegistro("", "Todas las sedes",null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
                                array_unshift($registros, $registro);
                            }
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
    
    public function eliminar($llaves)
    {
        $resultado = new Resultado();
        $consulta = " DELETE FROM sedes "
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

