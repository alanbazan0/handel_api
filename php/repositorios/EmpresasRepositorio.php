<?php
namespace php\repositorios;

use php\interfaces\IEmpresasRepositorio;
use php\modelos\Empresa;
use php\modelos\Resultado;

require_once("../interfaces/IEmpresasRepositorio.php");
require_once("../modelos/Empresa.php");
require_once("RepositorioBase.php");
require_once("../clases/TipoUsuario.php");
require_once("../clases/Resultado.php");
require_once("UsuariosRepositorio.php");

class EmpresasRepositorio extends RepositorioBase implements IEmpresasRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = " SELECT E.id, E.nombre, IFNULL(E.nombre_corto,'') nombre_corto , E.tipo_empresa_id, T.nombre tipo_empresa, E.direccion, E.pais_id, P.nombre pais, E.estado_id, ES.nombre estado, E.ciudad_id, C.nombre ciudad, E.telefono, E.corporativo_id , IFNULL(CO.nombre,'') corporativo, IFNULL(DATE_FORMAT(E.fecha_alta,'%d/%m/%Y %H:%i:%s'),'')fecha_alta, IFNULL(DATE_FORMAT(E.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'')fecha_modificacion, E.estatus, U.id administradorId, U.nombre usuarioNombre,  U.apellido usuarioApellido, E.perfil_id,  US.id administradorIdSIVAH, US.nombre usuarioNombreSIVAH,  US.apellido usuarioApellidoSIVAH,E.mes_revision_procesos,UP.id administradorIdProcesos, UP.nombre usuarioNombreProcesos,  UP.apellido usuarioApellidoProcesos, E.calificacion_minima, IFNULL(DATE_FORMAT( E.fecha_inicio_temporada,'%d/%m/%Y'),'')fecha_inicio_temporada,
                                E.socio_comercial, E.tipo_socio_comercial_id, E.servicio_id,E.autoevaluacion, E.plantilla_id, E.permitir_usuario_plantilla, E.analisis_riesgo_minuta_id, E.tokens
                             FROM empresas E 
                               LEFT JOIN tipos_empresa T ON T.id = E.tipo_empresa_id 
                               LEFT JOIN paises P ON P.id = E.pais_id 
                               LEFT JOIN estados ES ON ES.id = E.estado_id 
                               LEFT JOIN ciudades C ON C.id = E.ciudad_id 
                               LEFT JOIN empresas CO ON CO.id = E.corporativo_id
                               LEFT JOIN usuarios U ON U.id = E.administrador_id
                               LEFT JOIN usuarios US ON US.id = E.administrador_sivah_id
                               LEFT JOIN usuarios UP ON UP.id = E.administrador_procesos_id";
    } 
 
    public function insertar(Empresa $modelo)
    {        
        $resultado =  $this->calcularId("id","empresas");
        if($modelo->corporativoId=="")
            $modelo->corporativoId=null;
        if($modelo->administradorId=="")
            $modelo->administradorId=null;
        if($modelo->perfilId=="")
            $modelo->perfilId=null;
        if($modelo->administradorIdSIVAH=="")
            $modelo->administradorIdSIVAH=null;
        if($modelo->mesRevisionProcesos=="")
            $modelo->mesRevisionProcesos=null;
        if($modelo->administradorIdProcesos=="")
            $modelo->administradorIdProcesos=null;
        if($modelo->tipoSocioComercialId=="")
            $modelo->tipoSocioComercialId=null;
        if($modelo->servicioId=="")
            $modelo->servicioId=null;
        if($modelo->plantillaId=="")
            $modelo->plantillaId=null;
        if($modelo->analisisRiesgoMinutaId=="")
            $modelo->analisisRiesgoMinutaId=null;
        if($modelo->tokens=="")
            $modelo->tokens=0;
        if($resultado->mensajeError=="")
        {
            $id = $resultado->valor;           
            $consulta = "INSERT INTO empresas(id, nombre, nombre_corto, tipo_empresa_id, direccion, pais_id, estado_id, ciudad_id, telefono, corporativo_id, fecha_alta, fecha_modificacion, estatus, administrador_id, perfil_id, administrador_sivah_id, mes_revision_procesos,administrador_procesos_id, calificacion_minima, fecha_inicio_temporada, socio_comercial, tipo_socio_comercial_id, servicio_id, autoevaluacion, plantilla_id, permitir_usuario_plantilla,analisis_riesgo_minuta_id, tokens) " .
                        "VALUE(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if( $sentencia->bind_param("issisiiiisiiiiiiisiiiiiiii", $id, $modelo->nombre,$modelo->nombreCorto, $modelo->tipoEmpresaId, $modelo->direccion, $modelo->paisId, $modelo->estadoId, $modelo->ciudadId, $modelo->telefono, $modelo->corporativoId, $modelo->estatus, $modelo->administradorId, $modelo->perfilId, $modelo->administradorIdSIVAH, $modelo->mesRevisionProcesos, $modelo->admintradorIdProcesos, $modelo->calificacionMinima, $modelo->fechaInicioTemporada, $modelo->socioComercial, $modelo->tipoSocioComercialId, $modelo->servicioId, $modelo->autoevaluacion, $modelo->plantillaId, $modelo->permitirUsuarioPlantilla, $modelo->analisisRiesgoMinutaId, $modelo->tokens))
                {
                    if($sentencia->execute())       
                    {
                        $sentencia->close();
                        $resultado = $this->insertarSociosComerciales($modelo);
                        $resultado->valor = $id;
                    }
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
    
    public function eliminarSociosComerciales($empresaId)
    {
        $resultado = new Resultado();
        
        $consulta ="DELETE FROM empresas_socios_comerciales WHERE empresa_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$empresaId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = __FUNCTION__." Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                }
                
            }
            else
                $resultado->mensajeError = __FUNCTION__ ." Falló el enlace de parámetros";
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = __FUNCTION__ ." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
        }
        return $resultado;
    }
    
    private function insertarSociosComerciales($empresa)
    {
        $resultado = new Resultado();
        
        if(isset($empresa->sociosComerciales) && $empresa->sociosComerciales!=null)
        {
            for ($l = 0; $l< count($empresa->sociosComerciales); $l++)
            {
                $socioComercial = $empresa->sociosComerciales[$l];
                
                $consulta = "INSERT INTO empresas_socios_comerciales(empresa_id, socio_comercial_id) " .
                    "VALUE(?, ?)";
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if($sentencia->bind_param("ii",$empresa->id, $socioComercial->socioComercialId))
                    {
                        if($sentencia->execute())
                        {
                            $sentencia->close();
                        }
                        else
                        {
                            $resultado->codigoError = $this->conexion->errno;
                            $resultado->mensajeError = __FUNCTION__.". Falló la ejecución: (" . $this->conexion->errno . ") " . $this->conexion->error;
                            break;
                        }
                    }
                    else
                    {
                        $resultado->mensajeError = __FUNCTION__.". Falló el enlace de parámetros";
                        break;
                    }
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = __FUNCTION__.". Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
                    break;
                }
            }
            
        }
        return $resultado;
    }
    
    public function actualizar(Empresa $modelo)
    {
        if($modelo->corporativoId=="")
            $modelo->corporativoId=null;
        if($modelo->administradorId=="")
            $modelo->administradorId=null;
        if($modelo->perfilId=="")
            $modelo->perfilId=null;
        if($modelo->administradorIdSIVAH=="")
            $modelo->administradorIdSIVAH=null;
        if($modelo->administradorIdProcesos=="")
            $modelo->administradorIdProcesos=null;
        if($modelo->mesRevisionProcesos=="")
            $modelo->mesRevisionProcesos=null;
        if($modelo->tipoSocioComercialId=="")
            $modelo->tipoSocioComercialId=null;
        if($modelo->servicioId=="")
            $modelo->servicioId=null;
        if($modelo->plantillaId=="")
            $modelo->plantillaId=null;
        if($modelo->plantillaId=="")
            $modelo->plantillaId=null;
        if($modelo->analisisRiesgoMinutaId=="")
            $modelo->analisisRiesgoMinutaId=null;
        if($modelo->tokens=="")
            $modelo->tokens=0;
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
                     "  administrador_id = ?, " .
                     "  fecha_modificacion= NOW(), " .
                     "  perfil_id = ? , 
                        administrador_sivah_id = ?, 
                        mes_revision_procesos = ?,
                        administrador_procesos_id = ?, 
                        calificacion_minima = ?,
                        fecha_inicio_temporada = ?,
                        socio_comercial = ?,
                        tipo_socio_comercial_id = ?,
                        servicio_id = ?,
                        autoevaluacion = ?,
                        plantilla_id = ?,
                        permitir_usuario_plantilla = ?,
                        analisis_riesgo_minuta_id = ?,
                        tokens = ?
                     WHERE id = ? ";    

        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("ssisiiisiiiiiiiisiiiiiiiii", $modelo->nombre, $modelo->nombreCorto, $modelo->tipoEmpresaId,$modelo->direccion,$modelo->paisId,$modelo->estadoId,$modelo->ciudadId, $modelo->telefono, $modelo->corporativoId, $modelo->estatus,$modelo->administradorId,$modelo->perfilId,$modelo->administradorIdSIVAH,$modelo->mesRevisionProcesos,$modelo->administradorIdProcesos,$modelo->calificacionMinima,$modelo->fechaInicioTemporada, $modelo->socioComercial, $modelo->tipoSocioComercialId, $modelo->servicioId, $modelo->autoevaluacion, $modelo->plantillaId, $modelo->permitirUsuarioPlantilla, $modelo->analisisRiesgoMinutaId, $modelo->tokens, $modelo->id ))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
                    $resultado = $this->eliminarSociosComerciales($modelo->id);
                    if($resultado->correcto())
                    {
                        $resultado = $this->insertarSociosComerciales($modelo);
                        if($resultado->correcto())
                        {
                            $resultado->valor=true;
                        }
                    }
                    
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
            if(isset($criteriosSeleccion->estatus) && $criteriosSeleccion->estatus!="")
                array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'E','campo'=>'estatus','valor'=>$criteriosSeleccion->estatus]);
            if(isset($criteriosSeleccion->nombre))
                array_push($filtros,(object)['tipoDato'=>'varchar','tabla' => 'E', 'campo'=>'nombre','valor'=>$criteriosSeleccion->nombre]);
           
                
        }
        if($usuario!=null)
        {
            if($usuario->recursosHumanos==1)
            {
                if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
                {
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'E','campo'=>'id','valor'=>$criteriosSeleccion->empresaId]);
                }
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
            else if($usuario->tipoUsuarioId == \TipoUsuario::SUPERVISOR || $usuario->tipoUsuarioId == \TipoUsuario::COORDINADOR)
            {
                $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
                $resultado = $usuariosRepositorio->consultarIdsEmpresas($usuario->empresaId);
                if($resultado->correcto())
                {
                    $empresasIds = implode(",", $resultado->valor);
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'E', 'campo'=>'id','operador'=>'IN','valor'=>$empresasIds]);
                }
            }
            else if($usuario->tipoUsuarioId!=\TipoUsuario::ADMINISTRADOR)
            {
                array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'E','campo'=>'id','valor'=>$usuario->empresaId]);
                
            }
            
        }
        
        //$this->filtrarDesactivados($filtros, $criteriosSeleccion);
        
        $where = $this->where($filtros);
        
        $consulta = $this->consultaBase .
                 $where . " order by E.nombre";      
        
      //  echo $consulta;
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {                
                    if ($sentencia->bind_result($id, $nombre, $nombreCorto, $tipo_empresa_id, $tipo_empresa, $direccion, $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $telefono, $corporativo_id, $corporativo, $fecha_alta, $fecha_modificacion, $estatus,$administradorId,$administradorNombre, $administradorApellido, $perfilId,$administradorIdSIVAH, $administradorNombreSIVAH, $administradorApellidoSIVAH,$mesRevisionProcesos,$administradorIdProcesos, $administradorNombreProcesos, $administradorApellidoProcesos,$calificacionMinima, $fechaInicioTemporada,  $socioComercial, $tipoSocioComercialId, $servicioId, $autoevaluacion, $plantillaId, $permitirUsuarioPlantilla, $analisisRiesgoMinutaId,$tokens))
                    {                    
                        while($row = $sentencia->fetch()) 
                        {
                            $registro = $this->crearRegistro($id, $nombre,$nombreCorto, $tipo_empresa_id, $tipo_empresa, $direccion, $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $telefono, $corporativo_id, $corporativo, $fecha_alta, $fecha_modificacion, $estatus,$administradorId,$administradorNombre, $administradorApellido, $perfilId,$administradorIdSIVAH, $administradorNombreSIVAH, $administradorApellidoSIVAH,$mesRevisionProcesos,$administradorIdProcesos, $administradorNombreProcesos, $administradorApellidoProcesos,$calificacionMinima, $fechaInicioTemporada, $socioComercial, $tipoSocioComercialId, $servicioId, $autoevaluacion, $plantillaId, $permitirUsuarioPlantilla, $analisisRiesgoMinutaId,$tokens);
                      
                            array_push($registros,$registro);
                        }
                        if($opcional=="true")
                        {
                            if($usuario->tipoUsuarioId == \TipoUsuario::ADMINISTRADOR || $usuario->tipoUsuarioId == \TipoUsuario::COORDINADOR || $usuario->recursosHumanos==1)
                            {
                                $registro = $this->crearRegistro("", "Todas las empresas","",null, null, null, null, null, null, null, null, null, null, null, null, null, null, null,null,null,null,null,null,null,null,null,null,null,null,null,null);
                                array_unshift($registros, $registro);
                            }
                        }
                        $sentencia->close();
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
                    if ($sentencia->bind_result($id, $nombre,$nombreCorto, $tipo_empresa_id, $tipo_empresa, $direccion, $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $telefono,$corporativo_id, $corporativo, $fecha_alta, $fecha_modificacion, $estatus,$administradorId,$administradorNombre, $administradorApellido, $perfilId,$administradorIdSIVAH, $administradorNombreSIVAH, $administradorApellidoSIVAH,$mesRevisionProcesos,$administradorIdProcesos, $administradorNombreProcesos, $administradorApellidoProcesos,$calificacionMinima,$fechaInicioTemporada,$socioComercial, $tipoSocioComercialId, $servicioId, $autoevaluacion, $plantillaId, $permitirUsuarioPlantilla, $analisisRiesgoMinutaId,$tokens))
                    {                        
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre, $nombreCorto, $tipo_empresa_id, $tipo_empresa, $direccion, $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $telefono, $corporativo_id, $corporativo, $fecha_alta, $fecha_modificacion, $estatus,$administradorId,$administradorNombre, $administradorApellido, $perfilId,$administradorIdSIVAH, $administradorNombreSIVAH, $administradorApellidoSIVAH,$mesRevisionProcesos,$administradorIdProcesos, $administradorNombreProcesos, $administradorApellidoProcesos,$calificacionMinima,$fechaInicioTemporada,$socioComercial, $tipoSocioComercialId, $servicioId, $autoevaluacion, $plantillaId, $permitirUsuarioPlantilla, $analisisRiesgoMinutaId,$tokens);
                            
                            $sentencia->close();
                            
                            $resultadoSociosComerciales = $this->consultarSociosComerciales($id);
                            if($resultadoSociosComerciales->correcto())
                            {
                                $registro->sociosComerciales = $resultadoSociosComerciales->valor;
                            }
                            else
                            {
                                $resultado->mensajeError = $resultadoSociosComerciales->mensajeError;
                            }
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
        " WHERE E.id != ? order by E.nombre";
        $registros = array(); 
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$empresaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombre,$nombreCorto, $tipo_empresa_id, $tipo_empresa, $direccion, $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $telefono,$corporativo_id, $corporativo, $fecha_alta, $fecha_modificacion, $estatus,$administradorId,$administradorNombre, $administradorApellido, $perfilId,$administradorIdSIVAH, $administradorNombreSIVAH, $administradorApellidoSIVAH,$mesRevisionProcesos,$administradorIdProcesos, $administradorNombreProcesos, $administradorApellidoProcesos,$calificacionMinima, $fechaInicioTemporada, $socioComercial, $tipoSocioComercialId, $servicioId, $autoevaluacion, $plantillaId, $permitirUsuarioPlantilla, $analisisRiesgoMinutaId,$tokens))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre,$nombreCorto, $tipo_empresa_id, $tipo_empresa, $direccion, $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $telefono, $corporativo_id, $corporativo, $fecha_alta, $fecha_modificacion, $estatus,$administradorId,$administradorNombre, $administradorApellido, $perfilId,$administradorIdSIVAH, $administradorNombreSIVAH, $administradorApellidoSIVAH,$mesRevisionProcesos,$administradorIdProcesos, $administradorNombreProcesos, $administradorApellidoProcesos,$calificacionMinima, $fechaInicioTemporada, $socioComercial, $tipoSocioComercialId, $servicioId, $autoevaluacion, $plantillaId, $permitirUsuarioPlantilla, $analisisRiesgoMinutaId,$tokens);
                            
                           
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
    
    private function consultarSociosComerciales($empresaId)
    {
        $resultado = new Resultado();
        $sociosComerciales = array();
        $consulta = "SELECT ESC.id, socio_comercial_id, E.nombre " .
            "FROM empresas_socios_comerciales ESC
                INNER JOIN empresas E ON E.id = ESC.socio_comercial_id
             WHERE empresa_id = ? ".
             "ORDER BY ESC.id";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            
            if($sentencia->bind_param("i",$empresaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id,$empresaId, $empresaNombre))
                    {
                        while($sentencia->fetch())
                        {
                            $socioComercial= (object) [
                                'id' =>  $id,
                                'empresaId' =>  $empresaId,
                                'empresaNombre' => $empresaNombre
                            ];
                            array_push($sociosComerciales,$socioComercial);
                        }
                        $resultado->valor = $sociosComerciales;
                        $sentencia->close();
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__. ". Falló el enlace del resultado";
                }
                else
                    $resultado->mensajeError = __FUNCTION__. ". Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__. ". Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = __FUNCTION__. ". Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
        return $resultado;
    }
    
    private function crearRegistro($id, $nombre,$nombreCorto, $tipo_empresa_id, $tipo_empresa, $direccion, $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $telefono,$corporativo_id, $corporativo, $fecha_alta, $fecha_modificacion, $estatus,$administradorId,$administradorNombre,$administradorApellido,$perfilId=null,$administradorIdSIVAH=null, $administradorNombreSIVAH=null, $administradorApellidoSIVAH=null, $mesRevisionProcesos=null,$administradorIdProcesos=null, $administradorNombreProcesos=null, $administradorApellidoProcesos=null, $calificacionMinima=null, $fechaInicioTemporada=null, $socioComercial=null, $tipoSocioComercialId=null, $servicioId=null, $autoevaluacion=null, $plantillaId=null, $permitirUsuarioPlantilla=null, $analisisRiesgoMinutaId=null, $tokens=null)
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
            'administradorId' => $administradorId,
            'administradorNombre' => $administradorNombre,
            'administradorApellido' => $administradorApellido,
            'estatus' => $estatus,
            'perfilId' => $perfilId,
            'administradorIdSIVAH' => $administradorIdSIVAH,
            'administradorNombreSIVAH' => $administradorNombreSIVAH,
            'administradorApellidoSIVAH' => $administradorApellidoSIVAH,
            'mesRevisionProcesos' => $mesRevisionProcesos,
            'administradorIdProcesos' => $administradorIdProcesos,
            'administradorNombreProcesos' => $administradorNombreProcesos,
            'administradorApellidoProcesos' => $administradorApellidoProcesos,
            'calificacionMinima' => $calificacionMinima,
            'fechaInicioTemporada' => $fechaInicioTemporada,
            'socioComercial' => $socioComercial, 
            'tipoSocioComercialId' => $tipoSocioComercialId, 
            'servicioId' => $servicioId, 
            'autoevaluacion' => $autoevaluacion, 
            'plantillaId' => $plantillaId, 
            'permitirUsuarioPlantilla' => $permitirUsuarioPlantilla,
            'analisisRiesgoMinutaId' => $analisisRiesgoMinutaId,
            'tokens' => $tokens
        ];
        $registro->nodeId = $id;
        $registro->parentId = $registro->corporativoId;
        $registro->text = $registro->nodeId." - ".$registro->nombre;
        return $registro;
    }
    
    public function eliminar($llaves)
    {
        $this->conexion->autocommit(FALSE);
        $resultado = new Resultado();
        
        $resultado = $this->eliminarSociosComerciales($llaves->id);
        if($resultado->correcto())
        {
            $consulta = " DELETE FROM empresas "
                . "  WHERE id  = ? ";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param("i",$llaves->id))
                {
                    if($sentencia->execute())
                    {
                        $sentencia->close();
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
        }
        
        if($resultado->correcto())
            $this->conexion->commit();
        else
            $this->conexion->rollback();
        return $resultado;
    }
    
    public function consultarEstructura($referenciaPadre)
    {
        $resultado = $this->consultar(null, false, null);
        if($resultado->correcto())
        {
            $resultado->valor = $this->crearEstructura($resultado->valor,$referenciaPadre);
        }
        return $resultado;
    }
    
   
    
    private function crearEstructura($lista,$referenciaPadre)
    {
        $estructura = array();
        for ($i = 0; $i < count($lista); $i++) 
        {
            $nodo = $lista[$i];    
            if($nodo->parentId==null || $nodo->parentId==0)
            {
                if($referenciaPadre)
                    $nodo->parent = null;
                array_push($estructura,$nodo);
                $this->crearNodos($nodo, $lista,$referenciaPadre);
            }   
        }
        return $estructura;
    }
    
    private function crearNodos($nodoPadre, $lista,$referenciaPadre)
    {
        for ($i = 0; $i < count($lista); $i++) 
        {
            $nodoHijo = $lista[$i]; 
           
            if($nodoHijo->parentId == $nodoPadre->nodeId)
            {
                if(!isset($nodoPadre->nodes))
                    $nodoPadre->nodes = array();
                if($referenciaPadre)
                    $nodoHijo->parent = $nodoPadre;
                array_push($nodoPadre->nodes ,$nodoHijo);
                $this->crearNodos($nodoHijo, $lista,$referenciaPadre); 
            }
        }
    }
    
    public function consultarInicioTemporadaEmpresa($empresaId)
    {
        $resultado = new Resultado();
        
        if($empresaId != "")
        {
            $consulta = "SELECT IFNULL(DATE_FORMAT(E.fecha_inicio_temporada,'%d/%m/%Y'),'')fecha_inicio_temporada 
                        FROM empresas E 
                        WHERE E.id  = ?";
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param("i",$empresaId))
                {
                    if($sentencia->execute())
                    {
                        if ($sentencia->bind_result($fechaInicioTemporada))
                        {
                            if($sentencia->fetch())
                            {
                                $resultado->valor = $fechaInicioTemporada;
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
        }
        else
            $resultado->valor = "";
        return $resultado;
        
    }
    
    
    public function descontarTokens($empresaId)
    {
        $resultado = new Resultado();
        $consulta = " UPDATE empresas " .
                     "SET 
                        tokens = tokens - 1
                     WHERE id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("i", $empresaId))
            {
                if($sentencia->execute())
                {
                    $sentencia->close();
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

    
}
?>