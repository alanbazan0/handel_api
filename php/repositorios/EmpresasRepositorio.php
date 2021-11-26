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
        $this->consultaBase = " SELECT E.id, E.nombre, IFNULL(E.nombre_corto,'') nombre_corto , E.tipo_empresa_id, T.nombre tipo_empresa, E.direccion, E.pais_id, P.nombre pais, E.estado_id, ES.nombre estado, E.ciudad_id, C.nombre ciudad, E.telefono, E.corporativo_id , IFNULL(CO.nombre,'') corporativo, E.fecha_alta, E.fecha_modificacion, E.estatus, U.id administradorId, U.nombre usuarioNombre,  U.apellido usuarioApellido, E.perfil_id,  US.id administradorIdSIVAH, US.nombre usuarioNombreSIVAH,  US.apellido usuarioApellidoSIVAH,E.mes_revision_procesos,UP.id administradorIdProcesos, UP.nombre usuarioNombreProcesos,  UP.apellido usuarioApellidoProcesos
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
        if($resultado->mensajeError=="")
        {
            $id = $resultado->valor;           
            $consulta = "INSERT INTO empresas(id, nombre, nombre_corto, tipo_empresa_id, direccion, pais_id, estado_id, ciudad_id, telefono, corporativo_id, fecha_alta, fecha_modificacion, estatus, administrador_id, perfil_id, administrador_sivah_id, mes_revision_procesos,administrador_procesos_id) " .
                        "VALUE(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?, ?, ?, ?, ?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if( $sentencia->bind_param("issisiiiisiiiiii", $id, $modelo->nombre,$modelo->nombreCorto, $modelo->tipoEmpresaId, $modelo->direccion, $modelo->paisId, $modelo->estadoId, $modelo->ciudadId, $modelo->telefono, $modelo->corporativoId, $modelo->estatus, $modelo->administradorId, $modelo->perfilId, $modelo->admintradorIdSIVAH, $modelo->mesRevisionProcesos, $modelo->admintradorIdProcesos))
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
                        administrador_procesos_id = ? " .
                     "WHERE id = ? ";    

        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("ssisiiisiiiiiiii", $modelo->nombre, $modelo->nombreCorto, $modelo->tipoEmpresaId,$modelo->direccion,$modelo->paisId,$modelo->estadoId,$modelo->ciudadId, $modelo->telefono, $modelo->corporativoId, $modelo->estatus,$modelo->administradorId,$modelo->perfilId,$modelo->administradorIdSIVAH,$modelo->mesRevisionProcesos,$modelo->administradorIdProcesos,$modelo->id ))
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
                    if ($sentencia->bind_result($id, $nombre, $nombreCorto, $tipo_empresa_id, $tipo_empresa, $direccion, $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $telefono, $corporativo_id, $corporativo, $fecha_alta, $fecha_modificacion, $estatus,$administradorId,$administradorNombre, $administradorApellido, $perfilId,$administradorIdSIVAH, $administradorNombreSIVAH, $administradorApellidoSIVAH,$mesRevisionProcesos,$administradorIdProcesos, $administradorNombreProcesos, $administradorApellidoProcesos))
                    {                    
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre,$nombreCorto, $tipo_empresa_id, $tipo_empresa, $direccion, $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $telefono, $corporativo_id, $corporativo, $fecha_alta, $fecha_modificacion, $estatus,$administradorId,$administradorNombre, $administradorApellido, $perfilId,$administradorIdSIVAH, $administradorNombreSIVAH, $administradorApellidoSIVAH,$mesRevisionProcesos,$administradorIdProcesos, $administradorNombreProcesos, $administradorApellidoProcesos);
                      
                            array_push($registros,$registro);
                        }
                        if($opcional=="true")
                        {
                            //if($usuario->tipoUsuarioId == \TipoUsuario::ADMINISTRADOR)
                            //{
                                $registro = $this->crearRegistro("", "Todas las empresas","",null, null, null, null, null, null, null, null, null, null, null, null, null, null, null,null,null,null,null,null,null,null,null,null,null,null);
                                array_unshift($registros, $registro);
                            //}
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
                    if ($sentencia->bind_result($id, $nombre,$nombreCorto, $tipo_empresa_id, $tipo_empresa, $direccion, $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $telefono,$corporativo_id, $corporativo, $fecha_alta, $fecha_modificacion, $estatus,$administradorId,$administradorNombre, $administradorApellido, $perfilId,$administradorIdSIVAH, $administradorNombreSIVAH, $administradorApellidoSIVAH,$mesRevisionProcesos,$administradorIdProcesos, $administradorNombreProcesos, $administradorApellidoProcesos))
                    {                        
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre, $nombreCorto, $tipo_empresa_id, $tipo_empresa, $direccion, $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $telefono, $corporativo_id, $corporativo, $fecha_alta, $fecha_modificacion, $estatus,$administradorId,$administradorNombre, $administradorApellido, $perfilId,$administradorIdSIVAH, $administradorNombreSIVAH, $administradorApellidoSIVAH,$mesRevisionProcesos,$administradorIdProcesos, $administradorNombreProcesos, $administradorApellidoProcesos);
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
                    if ($sentencia->bind_result($id, $nombre,$nombreCorto, $tipo_empresa_id, $tipo_empresa, $direccion, $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $telefono,$corporativo_id, $corporativo, $fecha_alta, $fecha_modificacion, $estatus,$administradorId,$administradorNombre, $administradorApellido, $perfilId,$administradorIdSIVAH, $administradorNombreSIVAH, $administradorApellidoSIVAH,$mesRevisionProcesos,$administradorIdProcesos, $administradorNombreProcesos, $administradorApellidoProcesos))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombre,$nombreCorto, $tipo_empresa_id, $tipo_empresa, $direccion, $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $telefono, $corporativo_id, $corporativo, $fecha_alta, $fecha_modificacion, $estatus,$administradorId,$administradorNombre, $administradorApellido, $perfilId,$administradorIdSIVAH, $administradorNombreSIVAH, $administradorApellidoSIVAH,$mesRevisionProcesos,$administradorIdProcesos, $administradorNombreProcesos, $administradorApellidoProcesos);
                            
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
    
    private function crearRegistro($id, $nombre,$nombreCorto, $tipo_empresa_id, $tipo_empresa, $direccion, $pais_id, $pais, $estado_id, $estado, $ciudad_id, $ciudad, $telefono,$corporativo_id, $corporativo, $fecha_alta, $fecha_modificacion, $estatus,$administradorId,$administradorNombre,$administradorApellido,$perfilId=null,$administradorIdSIVAH, $administradorNombreSIVAH, $administradorApellidoSIVAH, $mesRevisionProcesos,$administradorIdProcesos, $administradorNombreProcesos, $administradorApellidoProcesos)
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
            'administradorApellidoProcesos' => $administradorApellidoProcesos
        ];
        $registro->nodeId = $id;
        $registro->parentId = $registro->corporativoId;
        $registro->text = $registro->nodeId." - ".$registro->nombre;
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

    
}
?>