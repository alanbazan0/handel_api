<?php
namespace php\repositorios;

use php\interfaces\IIndicadoresRepositorio;
use php\modelos\Resultado;

include "../interfaces/IIndicadoresRepositorio.php";
include "RepositorioBase.php";
include "../clases/TipoUsuario.php";
require_once("../clases/Resultado.php");

class IndicadoresRepositorio extends RepositorioBase implements IIndicadoresRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = " SELECT (SELECT COUNT(*) FROM appshand_dys.usuarios) as usuarios, ".
	                           "(SELECT COUNT(*) FROM appshand_dys.empresas) as empresas, ".
	                           "(SELECT COUNT(*) FROM appshand_dys.sedes) as sedes, ".
	                           "(SELECT COUNT(*) FROM appshand_dys.puestos) as puestos,".
                                "(SELECT COUNT(*) FROM appshand_dys.areas) as areas," .
                                "(SELECT COUNT(*) FROM appshand_dys.inspecciones) as inspecciones ";
        
        
        
    }
    
    
    
    public function consultarContadores($usuario)
    {
        $resultado = new Resultado();
        $filtros = array();

         if($usuario->tipoUsuarioId == \TipoUsuario::SUPERUSUARIO)
             $consulta = $this->consultaBase;
         else
         {
             $consulta =  " SELECT (SELECT COUNT(*) FROM appshand_dys.usuarios WHERE empresa_id = ?) as usuarios, ".
                 "(SELECT COUNT(*) FROM appshand_dys.empresas WHERE id = ?) as empresas, ".
                 "(SELECT COUNT(*) FROM appshand_dys.sedes WHERE empresa_id = ?) as sedes, ".
                 "(SELECT COUNT(*) FROM appshand_dys.puestos WHERE empresa_id = ?) as puestos,".
                 "(SELECT COUNT(*) FROM appshand_dys.areas WHERE empresa_id = ?) as areas," .
                 "(SELECT COUNT(*) FROM appshand_dys.inspecciones I INNER JOIN sedes S ON I.sede_id = S.id  WHERE S.empresa_id = ?) as inspecciones ";
             
             
             array_push($filtros,(object)['tipoDato'=>'int','valor'=> $usuario->empresaId]);
             array_push($filtros,(object)['tipoDato'=>'int','valor'=> $usuario->empresaId]);
             array_push($filtros,(object)['tipoDato'=>'int','valor'=> $usuario->empresaId]);
             array_push($filtros,(object)['tipoDato'=>'int','valor'=> $usuario->empresaId]);
             array_push($filtros,(object)['tipoDato'=>'int','valor'=> $usuario->empresaId]);
             array_push($filtros,(object)['tipoDato'=>'int','valor'=> $usuario->empresaId]);
         }
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($usuarios, $empresas, $sedes, $puestos, $areas, $inspecciones ))
                    {
                        if($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($usuarios, $empresas, $sedes, $puestos, $areas,$inspecciones);
                            //array_push($registros,$registro);
                        }
                        $resultado->valor = $registro;
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
    
    
    private function crearRegistro($usuarios, $empresas, $sedes, $puestos, $areas, $inspecciones)
    {
        $registro= (object) [
            'usuarios' =>  $usuarios,
            'empresas' => $empresas,
            'sedes' => $sedes,
            'puestos' => $puestos,
            'areas' => $areas,
            'inspecciones' => $inspecciones
        ];
        return $registro;
    }
    
    
}

