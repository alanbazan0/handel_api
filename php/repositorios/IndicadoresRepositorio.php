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
        $this->consultaBase = " SELECT (SELECT COUNT(*) FROM usuarios) as usuarios, ".
	                           "(SELECT COUNT(*) FROM empresas) as empresas, ".
	                           "(SELECT COUNT(*) FROM tipos_empresa) as tiposEmpresa, ".
	                           "(SELECT COUNT(*) FROM sedes) as sedes, ".
	                           "(SELECT COUNT(*) FROM puestos) as puestos,".
                                "(SELECT COUNT(*) FROM areas) as areas,".
                                "(SELECT COUNT(*) FROM justificaciones) as justificaciones,".
                                "(SELECT COUNT(*) FROM certificaciones) as certificaciones,".
                                "(SELECT COUNT(*) FROM inspecciones) as inspecciones";
                              
        
        
        
    }
    
    
    
    public function consultarContadores($usuario)
    {
        $resultado = new Resultado();
        $filtros = array();

         if($usuario->tipoUsuarioId == \TipoUsuario::ADMINISTRADOR)
             $consulta = $this->consultaBase;
         else
         {
             $consulta =  " SELECT (SELECT COUNT(*) FROM usuarios WHERE empresa_id = ?) as usuarios, ".
                 "(SELECT COUNT(*) FROM empresas WHERE id = ?) as empresas, ".
                 "(SELECT COUNT(*) FROM tipos_empresa) as tiposEmpresa, ".
                 "(SELECT COUNT(*) FROM sedes WHERE empresa_id = ?) as sedes, ".
                 "(SELECT COUNT(*) FROM puestos WHERE empresa_id = ?) as puestos,".
                 "(SELECT COUNT(*) FROM areas WHERE empresa_id = ?) as areas,".
                 "(SELECT COUNT(*) FROM inspecciones WHERE empresa_id = ?) as inspecciones";
               
             
             
             array_push($filtros,(object)['tipoDato'=>'int','valor'=> $usuario->empresaId]);
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
                    if ($sentencia->bind_result($usuarios, $empresas, $tiposEmpresa, $sedes, $puestos, $areas, $justificaciones, $certificaciones, $inspecciones ))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($usuarios, $empresas,$tiposEmpresa, $sedes, $puestos, $areas, $justificaciones, $certificaciones, $inspecciones);
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
    
    
    private function crearRegistro($usuarios, $empresas, $tiposEmpresa, $sedes, $puestos, $areas, $justificaciones, $certificaciones, $inspecciones)
    {
        $registro= (object) [
            'usuarios' =>  $usuarios,
            'empresas' => $empresas,
            'tiposEmpresa' => $tiposEmpresa,
            'sedes' => $sedes,
            'puestos' => $puestos,
            'areas' => $areas,
            'justificaciones' => $justificaciones,
            'certificaciones' => $certificaciones,
            'inspecciones' => $inspecciones
        ];
        return $registro;
    }
    
    
}

