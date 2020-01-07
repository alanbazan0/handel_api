<?php
namespace php\repositorios;

use php\modelos\Resultado;
use php\interfaces\IHistorialAccesoRepositorio;

require_once("RepositorioBase.php");
include "../interfaces/IHistorialAccesoRepositorio.php";
require_once("../clases/Resultado.php");

class HistorialAccesoRepositorio extends RepositorioBase implements IHistorialAccesoRepositorio
{
    protected $conexion;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = " SELECT H.id, H.nombre_usuario,  IFNULL(DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s'),'')fecha, ip, aplicacion_id, aplicacion_version, referer, user_agent, U.id, U.nombre, U.apellido
                                FROM historial_acceso H
                                    INNER JOIN usuarios U ON U.nombre_usuario = H.nombre_usuario ";
    } 
 
    public function consultar($criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        $filtros = array();
        $where="";
        if($criteriosSeleccion!=null)
        {
            if(isset($criteriosSeleccion->nombreUsuario))
            {
                if($criteriosSeleccion->nombreUsuario!="")
                {
                    array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'H','campo'=>'nombre_usuario','valor'=>$criteriosSeleccion->nombreUsuario]);
                }
            }
            if(isset($criteriosSeleccion->empresaId))
            {
                if($criteriosSeleccion->empresaId!="" && $criteriosSeleccion->empresaId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'empresa_id','valor'=>$criteriosSeleccion->empresaId]);
            }
            if(isset($criteriosSeleccion->sedeId))
            {
                if($criteriosSeleccion->sedeId!="" && $criteriosSeleccion->sedeId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'sede_id','valor'=>$criteriosSeleccion->sedeId]);
            }
            if(isset($criteriosSeleccion->areaId))
            {
                if($criteriosSeleccion->areaId!="" && $criteriosSeleccion->areaId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'area_id','valor'=>$criteriosSeleccion->areaId]);
            }
            if(isset($criteriosSeleccion->ano))
            {
                if($criteriosSeleccion->ano!="" && $criteriosSeleccion->ano!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','campo'=>'YEAR(H.fecha)','valor'=>$criteriosSeleccion->ano]);
            }
            if(isset($criteriosSeleccion->mes))
            {
                if($criteriosSeleccion->mes!="" && $criteriosSeleccion->mes!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','campo'=>'MONTH(H.fecha)','valor'=>$criteriosSeleccion->mes]);
            }
            $where = $this->where($filtros);
        }
        $consulta = $this->consultaBase .
        $where . " order by UNIX_TIMESTAMP(H.fecha) desc";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id,$nombreUsuaro,$fecha, $ip, $aplicacionId, $aplicacionVersion, $referer, $userAgent,$usuarioId, $usuarioNombre, $usuarioApellido))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id,$nombreUsuaro,$fecha, $ip, $aplicacionId, $aplicacionVersion, $referer, $userAgent,$usuarioId, $usuarioNombre, $usuarioApellido);
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
    
    public function insertar($nombreUsuario,$aplicacionId,$aplicacionVersion)
    {        
        $ip = GET_IP();
        $referer = GET_REFERER();
        $userAgent = GET_USER_AGENT();
        
        $resultado =  $this->calcularId("id","historial_acceso");
        
        if($resultado->mensajeError=="")
        {
            $id = $resultado->valor;           
            ;
            $consulta = "INSERT INTO historial_acceso(id, nombre_usuario, ip, referer, user_agent, fecha, aplicacion_id, aplicacion_version) " .
                        "VALUE(?, ?, ?, ?, ?, NOW(),?, ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if( $sentencia->bind_param("issssss", $id,$nombreUsuario,$ip,$referer,$userAgent,$aplicacionId,$aplicacionVersion))
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
    
    private function crearRegistro($id,$nombreUsuaro,$fecha, $ip, $aplicacionId, $aplicacionVersion, $referer, $userAgent,$usuarioId, $usuarioNombre, $usuarioApellido)
    {
        $registro= (object) [
            'id' => $id,
            'nombreUsuario' =>  $nombreUsuaro,
            'fecha' => $fecha,
            'ip' => $ip,
            'aplicacionId' => $aplicacionId,
            'aplicacionVersion' => $aplicacionVersion,
            'referer' => $referer,
            'userAgent' => $userAgent,
            'usuarioId' => $usuarioId,
            'usuarioNombre' => $usuarioNombre,
            'usuarioApellido' => $usuarioApellido
        ];
        
        $registro->nombreCompleto = $registro->usuarioNombre . " " . $registro->usuarioApellido;
        $registro->fotoPerfil =  "../fotos/usuario". $registro->usuarioId .".jpg";
        if(file_exists($registro->fotoPerfil))
            $registro->fotoPerfil =  "php/fotos/usuario". $registro->usuarioId .".jpg";
        else
            $registro->fotoPerfil =  "php/fotos/default.jpg";
                    
        return $registro;
    }
    
    public function consultarAnos($criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
       // $filtros = $this->getFiltrosUsuario($usuario, $criteriosSeleccion,false);
        
        $consulta = "SELECT YEAR(H.fecha) ano
                    FROM historial_acceso H ";
        
       // $consulta .= $this->where($filtros);
        
        $consulta.=" GROUP BY ano
                    ORDER BY ano";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
//             if($this->bind_param($sentencia, $filtros))
//             {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($ano))
                    {
                        while($sentencia->fetch())
                        {
                            $registro= (object) [
                                'id' =>  $ano,
                                'nombre' =>  $ano
                            ];
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace del resultado.';
//                 }
//                 else
//                     $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
     
}

