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
        $inspectorId = \TipoUsuario::INSPECTOR;
        $this->consultaBase = " SELECT (SELECT COUNT(*) FROM usuarios) as usuarios, ".
	                           "(SELECT COUNT(*) FROM empresas) as empresas, ".
	                           "(SELECT COUNT(*) FROM tipos_empresa) as tiposEmpresa, ".
	                           "(SELECT COUNT(*) FROM sedes) as sedes, ".
	                           "(SELECT COUNT(*) FROM puestos) as puestos,".
                                "(SELECT COUNT(*) FROM areas) as areas,".
                                "(SELECT COUNT(*) FROM justificaciones) as justificaciones,".
                                "(SELECT COUNT(*) FROM certificaciones) as certificaciones,".
                                "(SELECT COUNT(*) FROM inspecciones) as inspecciones,".
                                "(SELECT COUNT(*) FROM procedimientos) as procedimientos," .
                                "(SELECT COUNT(*) FROM usuarios_procedimientos) as usuariosProcedimientos,".
                                "(SELECT COUNT(*) FROM usuarios where  tipo_usuario_id=$inspectorId) as inspectores," .
                                "(SELECT COUNT(*) FROM evidencias) as evidencias";
                              
        
        
        
    }
    
    
    
    public function consultarContadores($usuario)
    {
        $resultado = new Resultado();
        $filtros = array();
        $inspectorId = \TipoUsuario::INSPECTOR;
         if($usuario->tipoUsuarioId == \TipoUsuario::ADMINISTRADOR)
             $consulta = $this->consultaBase;
         else  if($usuario->tipoUsuarioId == \TipoUsuario::COORDINADOR)
         {
            
             $consulta = " SELECT (SELECT COUNT(*) FROM usuarios WHERE empresa_id = ?) as usuarios, ".
                 "0 as empresas, ".
                 "0 as tiposEmpresa, ".
                 "0 as sedes, ".
                 "0 as puestos,".
                 "0 as areas,".
                 "0 as justificaciones,".
                 "0 as certificaciones,".
                 "(SELECT COUNT(*) FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id WHERE S.empresa_id = ?) as inspecciones,".
                 "0 as procedimientos,".
                 "0 as usuariosProcedimientos,".
                 "(SELECT COUNT(*) FROM usuarios WHERE empresa_id = ? AND tipo_usuario_id=$inspectorId) as inspectores," .
                 "(SELECT COUNT(*) FROM evidencias) as evidencias";
             
             array_push($filtros,(object)['tipoDato'=>'int','valor'=> $usuario->empresaId]);
             array_push($filtros,(object)['tipoDato'=>'int','valor'=> $usuario->empresaId]);
             array_push($filtros,(object)['tipoDato'=>'int','valor'=> $usuario->empresaId]);
            
         }
         else
         {
             
             $consulta = " SELECT (SELECT COUNT(*) FROM usuarios WHERE empresa_id = ?) as usuarios, ".
                 "0 as empresas, ".
                 "0 as tiposEmpresa, ".
                 "0 as sedes, ".
                 "0 as puestos,".
                 "0 as areas,".
                 "0 as justificaciones,".
                 "0 as certificaciones,".
                 "(SELECT COUNT(*) FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id WHERE S.empresa_id = ? and I.sede_id = ?) as inspecciones,".
                 "0 as procedimientos,".
                 "0 as usuariosProcedimientos,".
                 "(SELECT COUNT(*) FROM usuarios WHERE empresa_id = ? AND sede_id = ? AND tipo_usuario_id=$inspectorId) as inspectores,".
                 "(SELECT COUNT(*) FROM evidencias) as evidencias";
             
             array_push($filtros,(object)['tipoDato'=>'int','valor'=> $usuario->empresaId]);
             array_push($filtros,(object)['tipoDato'=>'int','valor'=> $usuario->empresaId]);
             array_push($filtros,(object)['tipoDato'=>'int','valor'=> $usuario->sedeId]);
             array_push($filtros,(object)['tipoDato'=>'int','valor'=> $usuario->empresaId]);
             array_push($filtros,(object)['tipoDato'=>'int','valor'=> $usuario->sedeId]);
             
         }
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($usuarios, $empresas, $tiposEmpresa, $sedes, $puestos, $areas, $justificaciones, $certificaciones, $inspecciones, $procedimientos, $usuariosProcedimientos, $inspectores, $evidencias ))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($usuarios, $empresas,$tiposEmpresa, $sedes, $puestos, $areas, $justificaciones, $certificaciones, $inspecciones, $procedimientos,$usuariosProcedimientos,$inspectores, $evidencias );
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
    
    
    private function crearRegistro($usuarios, $empresas, $tiposEmpresa, $sedes, $puestos, $areas, $justificaciones, $certificaciones, $inspecciones,$procedimientos,$usuariosProcedimientos,$inspectores,$evidencias)
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
            'inspecciones' => $inspecciones,
            'procedimientos' => $procedimientos,
            'usuariosProcedimientos' => $usuariosProcedimientos,
            'inspectores' => $inspectores,
            'evidencias' => $evidencias
        ];
        return $registro;
    }
    
    
}

