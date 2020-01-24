<?php
namespace php\repositorios;



use php\interfaces\IUsuariosRepositorio;
use php\modelos\Usuario;
use php\modelos\Resultado;
use php\modelos\UsuarioProcedimiento;


require_once("../interfaces/IUsuariosRepositorio.php");


require_once("RepositorioBase.php");
require_once("../clases/Resultado.php");
require_once("EmpresasRepositorio.php");
require_once("UsuariosProcedimientosRepositorio.php");

class UsuariosRepositorio extends RepositorioBase implements IUsuariosRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = "SELECT U.id, U.nombre_usuario, U.contrasena contrasena,U.nombre, U.apellido, E.id empresaId, IFNULL(E.nombre,'') empresa, S.id sedeId, IFNULL(S.nombre,'') sede, P.id puestoId, IFNULL(P.nombre,'') puesto, A.id areaId, IFNULL(A.nombre,'') area, T.id tipoUsuarioId, T.nombre tipo_usuario, SU1.id supervisor1Id, CONCAT(IFNULL(SU1.nombre,''),' ',IFNULL(SU1.apellido,'')) supervisor1,SU2.id supervisor2Id,CONCAT(IFNULL(SU2.nombre,''),' ',IFNULL(SU2.apellido,'')) supervisor2,SU3.id supervisor3Id, CONCAT(IFNULL(SU3.nombre,''),' ',IFNULL(SU3.apellido,'')) supervisor3, IFNULL(DATE_FORMAT(U.fecha_alta,'%d/%m/%Y %H:%i:%s'),'') fecha_alta,  IFNULL(DATE_FORMAT(U.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'')fecha_modificacion,IFNULL((SELECT IFNULL(DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s'),'') as fecha FROM historial_acceso WHERE nombre_usuario= U.nombre_usuario ORDER BY id DESC LIMIT 1),'') ultimo_acceso, U.estatus, E.tipo_empresa_id, A.tipo_area_id, U.permiso_saha,U.permiso_sivah,U.permiso_10y7, U.departamento_id, D.nombre as departamentoNombre " .
                             "FROM usuarios U " .
                             "  LEFT JOIN empresas E ON U.empresa_id=E.id ".
                             "  LEFT JOIN sedes S ON U.sede_id = S.id " .
                             "  LEFT JOIN puestos P ON U.puesto_id = P.id " .
                             "  LEFT JOIN areas A ON U.area_id = A.id " .
                             "  LEFT JOIN tipos_usuario T ON U.tipo_usuario_id = T.id " .
                             "  LEFT JOIN usuarios SU1 ON U.supervisor1_id = SU1.id " .
                             "  LEFT JOIN usuarios SU2 ON U.supervisor2_id = SU2.id " .
                             "  LEFT JOIN usuarios SU3 ON U.supervisor3_id = SU3.id ".
                             "  LEFT JOIN departamentos D ON D.id = U.departamento_id";
    }    
   
    public function insertar(Usuario $modelo)
    {            
       
        $resultado =  $this->calcularId("id","usuarios");
        if($modelo->supervisor1Id=="")
            $modelo->supervisor1Id=null;
        if($modelo->supervisor2Id=="")
            $modelo->supervisor2Id=null;
        if($modelo->supervisor3Id=="")
            $modelo->supervisor3Id=null;
        if($modelo->areaId=="")
            $modelo->areaId=null;
        
            
        if($resultado->mensajeError=="")
        {
            $id = $resultado->valor;
            $modelo->id = $id;
           
            
            if($modelo->tipoUsuarioId==\TipoUsuario::INSPECTOR)
            {
                $modelo->nombreUsuario = "inspector".$id;
            }
            
            $consulta = " INSERT INTO usuarios "
                        . " (id, "
                        . " nombre_usuario, "
                        . " contrasena, "    
                        . " nombre, "
                        . " apellido, "
                        . " empresa_id, "
                        . " sede_id, "
                        . " puesto_id, "
                        . " area_id, "
                        . " tipo_usuario_id, "
                        . " supervisor1_id, "
                        . " supervisor2_id, "
                        . " supervisor3_id, "
                        . " fecha_alta, "
                        . " fecha_modificacion, "
                        . " estatus, "
                        . " permiso_saha, "
                        . " permiso_sivah, "
                        . " permiso_10y7, "
                        . " departamento_id ) "
                        . " VALUE(?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW(),?,?,?,?,?) ";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if( $sentencia->bind_param("issssiiiiiiiiiiiii",
                    $id, 
                    $modelo->nombreUsuario,
                    $modelo->contrasena,
                    $modelo->nombre,
                    $modelo->apellido,
                    $modelo->empresaId,
                    $modelo->sedeId,
                    $modelo->puestoId,
                    $modelo->areaId,
                    $modelo->tipoUsuarioId,
                    $modelo->supervisor1Id,
                    $modelo->supervisor2Id,
                    $modelo->supervisor3Id,
                    $modelo->estatus,
                    $modelo->permisoSAHA,
                    $modelo->permisoSIVAH,
                    $modelo->permiso10y7,
                    $modelo->departamentoId))
                {
                    if(!$sentencia->execute())              
                    {
                        $resultado->codigoError = $this->conexion->errno;
                        if($resultado->codigoError==1062)
                            $resultado->mensajeError ="Ya existe un usuario " . $modelo->nombreUsuario . ", intente con otro nombre.";
                            
                        else
                            $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;      
                    }
                }
                else
                    $resultado->mensajeError = "Falló el enlace de parámetros";   
            }
            else
                $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;   
        }   
        return $resultado;
    }
    
    public function eliminar($llaves)
    {
        $resultado = new Resultado();
        $this->conexion->autocommit(FALSE);
        
        $resultado = $this->desvincularSupervisor($llaves);
        if($resultado->correcto())
        {
            $resultado = $this->desvincularAdministrador($llaves);
            if($resultado->correcto())
            {
                $resultado = $this->desvincularResponsableRespuestaAuditoria($llaves);
                if($resultado->correcto())
                {
                    $resultado = $this->eliminarMensajesLeidos($llaves);
                    if($resultado->correcto())
                    {
                        $resultado = $this->eliminarMensajesEnviados($llaves);
                        if($resultado->correcto())
                        {
                            $resultado = $this->eliminarUsuario($llaves);
                            if($resultado->correcto())
                                $this->conexion->commit();
                            else
                            {
                                if($resultado->codigoError==1451)
                                {
                                    $resultado->valor = $this->consultarRelaciones($llaves);
                                }
                                $this->conexion->rollback();
                            }
                        }
                        else
                            $this->conexion->rollback();
                    }
                    else
                        $this->conexion->rollback();
                }
                else
                    $this->conexion->rollback();
            }
            else
                $this->conexion->rollback();
           
        }
        else
            $this->conexion->rollback();
        return $resultado;
    }
    
    public function consultarRelaciones($llaves)
    {
        $resultado = new Resultado();
        $relaciones = array();
       
        $usuariosProcedimientosRepositorio = new UsuariosProcedimientosRepositorio($this->conexion);
        $resultado = $usuariosProcedimientosRepositorio->consultar((object) ['usuarioId' => $llaves->id]);
        if($resultado->correcto())
        {
            array_push($relaciones,(object)['nombre'=>'Usuarios/Procedimientos','registros'=>$resultado->valor]);
        }
      
        $resultado->valor = $relaciones;
        return $resultado;
    }
    
    public function desvincularSupervisor($llaves)
    {
        $resultado = new Resultado();
        
        $consulta = " UPDATE usuarios " .
            "SET supervisor1_id = NULL, " .
            "  supervisor2_id = NULL, " .
            "  supervisor3_id = NULL " .
            "WHERE supervisor1_id = ? OR supervisor2_id = ? OR supervisor3_id = ?";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("iii", $llaves->id,$llaves->id,$llaves->id))
            {
                if($sentencia->execute())
                {
                    $resultado->valor=true;
                    $sentencia->close();
                }
                else
                    $resultado->mensajeError = "Falló la ejecución actualizar(" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = "Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
        
        return $resultado;
    }
    
    public function desvincularAdministrador($llaves)
    {
        $resultado = new Resultado();
        
        $consulta = " UPDATE empresas " .
            "SET administrador_id = NULL " .
            "WHERE administrador_id = ?";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("i", $llaves->id))
            {
                if($sentencia->execute())
                {
                    $resultado->valor=true;
                    $sentencia->close();
                }
                else
                    $resultado->mensajeError = "Falló la ejecución actualizar(" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = "Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
            return $resultado;
    }
    
    public function desvincularResponsableRespuestaAuditoria($llaves)
    {
        $resultado = new Resultado();
        
        $consulta = " UPDATE auditoria_respuestas " .
            "SET responsable_id = NULL " .
            "WHERE responsable_id = ?";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("i", $llaves->id))
            {
                if($sentencia->execute())
                {
                    $resultado->valor=true;
                    $sentencia->close();
                }
                else
                    $resultado->mensajeError = "Falló la ejecución actualizar(" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = "Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
            return $resultado;
    }
    
    public function eliminarMensajesLeidos($llaves)
    {
        $resultado = new Resultado();
        
        $consulta = " DELETE FROM mensajes_leidos " .
            "WHERE mensaje_id IN (SELECT id FROM mensajes WHERE usuario_id = ?) OR usuario_id = ?";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("ii", $llaves->id,$llaves->id))
            {
                if($sentencia->execute())
                {
                    $resultado->valor=$sentencia->affected_rows;
                    
                    
                   
                  
                    $sentencia->close();
                }
                else
                    $resultado->mensajeError = __FUNCTION__." Falló la ejecución actualizar(" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__." Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = __FUNCTION__." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
        return $resultado;
    }
    
    public function eliminarMensajesEnviados($llaves)
    {
        $resultado = new Resultado();
        
        $consulta = " DELETE FROM mensajes " .
            "WHERE usuario_id = ?";
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("i", $llaves->id))
            {
                if($sentencia->execute())
                {
                    $resultado->valor=$sentencia->affected_rows;
                    $sentencia->close();
                }
                else
                    $resultado->mensajeError = __FUNCTION__." Falló la ejecución actualizar(" . $this->conexion->errno . ") " . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__." Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = __FUNCTION__." Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            
            return $resultado;
    }
    
    public function eliminarUsuario($llaves)
    {
        $resultado = new Resultado();
        $consulta = " DELETE FROM usuarios "
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

    public function actualizar(Usuario $modelo)
    {     
        if($modelo->supervisor1Id=="")
            $modelo->supervisor1Id=null;
        if($modelo->supervisor2Id=="")
            $modelo->supervisor2Id=null;
        if($modelo->supervisor3Id=="")
            $modelo->supervisor3Id=null;
        if($modelo->areaId=="")
            $modelo->areaId=null;
        $resultado = new Resultado();
        $consulta = " UPDATE usuarios " .
                    "SET nombre_usuario = ?, " .
                    " contrasena = ?, " .
                    " nombre = ?, " .
                    " apellido = ?, " .
                    " empresa_id = ?, " .
                    " sede_id = ?, " .
                    " puesto_id = ?, " .
                    " area_id = ?, " .
                    " tipo_usuario_id = ?, " .
                    " supervisor1_id = ?, " .    
                    " supervisor2_id = ?, " .  
                    " supervisor3_id = ?, " .  
                    " fecha_modificacion=NOW(), " .
                    " estatus = ?, " .
                    " permiso_saha = ?, " .
                    " permiso_sivah = ?, " .
                    " permiso_10y7 = ?, " .
                    " departamento_id = ? " .
                    "WHERE id = ?";    
                        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ssssiiiiiiiiiiiiii",
                $modelo->nombreUsuario, 
                $modelo->contrasena,
                $modelo->nombre,
                $modelo->apellido,
                $modelo->empresaId,
                $modelo->sedeId,
                $modelo->puestoId,
                $modelo->areaId,
                $modelo->tipoUsuarioId,
                $modelo->supervisor1Id,
                $modelo->supervisor2Id,
                $modelo->supervisor3Id,
                $modelo->estatus,
                $modelo->permisoSAHA,
                $modelo->permisoSIVAH,
                $modelo->permiso10y7,
                $modelo->departamentoId,
                $modelo->id))
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
    
    private function crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id, $supervisor2, $supervisor3Id, $supervisor3, $fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus, $tipoEmpresaId, $tipoAreaId, $permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre)
    {
        $registro= (object) [
            'id' =>  $id,
            'nombreUsuario' => $nombreUsuario,
            'contrasena' => $contrasena,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'empresaId' => $empresaId,
            'empresaNombre' => $empresa,
            'sedeId' => $sedeId,
            'sedeNombre' => $sede,
            'puestoId' => $puestoId,
            'puestoNombre' => $puesto,
            'areaId' => $areaId,
            'areaNombre' => $area,
            'tipoUsuarioId' => $tipoUsuarioId,
            'tipoUsuarioNombre' => $tipoUsuario,
            'supervisor1Id' => $supervisor1Id,
            'supervisor1Nombre' => $supervisor1,
            'supervisor2Id' => $supervisor2Id,
            'supervisor2Nombre' => $supervisor2,
            'supervisor3Id' => $supervisor3Id,
            'supervisor3Nombre' => $supervisor3,
            'ultimoAcceso' => $ultimoAcceso,
            'fechaAlta' => $fechaAlta,
            'fechaModificacion' => $fechaModificacion,
            'estatus' => $estatus,
            'tipoEmpresaId' => $tipoEmpresaId,
            'tipoAreaId' => $tipoAreaId,
            'permisoSAHA' => $permisoSAHA,
            'permisoSIVAH' => $permisoSIVAH,
            'permiso10y7' => $permiso10y7,
            'departamentoId' => $departamentoId,
            'departamentoNombre' => $departamentoNombre
        ];
       
        
        $registro->nombreCompleto = $registro->nombre . " " . $registro->apellido;
        $registro->fotoPerfil =  "../fotos/usuario". $registro->id .".jpg";
        if(file_exists($registro->fotoPerfil))
            $registro->fotoPerfil =  "php/fotos/usuario". $registro->id .".jpg";
        else
             $registro->fotoPerfil =  "php/fotos/default.jpg";
        
         $registro->nodeId = $id;
         $registro->parentId = $registro->supervisor1Id;
         $registro->text = $registro->nodeId." - ".$registro->nombreCompleto;
        
        return $registro;
    }
    
    
    public function consultar($usuario,$criteriosSeleccion,$opcional)
    {     
        $resultado = new Resultado();
        $registros = array();     
       
        $filtros = array();
        $where="";
        if($criteriosSeleccion!=null)
        {
            if(isset($criteriosSeleccion->nombre))
                array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'U','campo'=>'nombre','valor'=>$criteriosSeleccion->nombre]);
            if(isset($criteriosSeleccion->apellido))
                array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'U','campo'=>'apellido','valor'=>$criteriosSeleccion->apellido]);
            if(isset($criteriosSeleccion->empresaId))
            {
                if($criteriosSeleccion->empresaId!="" && $criteriosSeleccion->empresaId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'empresa_id','valor'=>$criteriosSeleccion->empresaId]);
            }
            if(isset($criteriosSeleccion->nombreUsuario))
            {
                if($criteriosSeleccion->nombreUsuario!="" && $criteriosSeleccion->nombreUsuario!=null)
                    array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'U','campo'=>'nombre_usuario','valor'=>$criteriosSeleccion->nombreUsuario]);
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
            if(isset($criteriosSeleccion->tipoUsuarioId))
            {
                if($criteriosSeleccion->tipoUsuarioId!="" && $criteriosSeleccion->tipoUsuarioId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'tipo_usuario_id','valor'=>$criteriosSeleccion->tipoUsuarioId]);
            }
            if(isset($criteriosSeleccion->usuarioId))
            {
                if($criteriosSeleccion->usuarioId!="" && $criteriosSeleccion->usuarioId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'id','valor'=>$criteriosSeleccion->usuarioId]);
            }
            if(isset($criteriosSeleccion->permisoSAHA))
            {
                if($criteriosSeleccion->permisoSAHA!="" && $criteriosSeleccion->permisoSAHA!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'permiso_saha','valor'=>$criteriosSeleccion->permisoSAHA]);
            }
            $where = $this->where($filtros);
        }
        
        $consulta =  $this->consultaBase .
                    $where .
                     " ORDER BY U.nombre";
        
     

        if($sentencia = $this->conexion->prepare($consulta))
        {
            //if($sentencia->bind_param("ss",$criteriosSeleccion->nombre,$criteriosSeleccion->apellido))
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {                
                    if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre)  )
                    {                    
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre);
                            array_push($registros,$registro);
                        }
                        if($opcional=="true")
                        {
                            if($usuario->tipoUsuarioId == \TipoUsuario::ADMINISTRADOR)
                            {
                                $registro = $this->crearRegistro("", "Todos los usuarios", null, null, null,null, null, null, null, null, null, null, null, null, null, null, null,null, null,null, null,null, null, null, null,null, null,null, null, null);
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
    
    public function consultarAdministradores()
    {
        $resultado = new Resultado();
        $registros = array();
        
        $filtros = array();
        $where="";
//         if($criteriosSeleccion!=null)
//         {
//             if(isset($criteriosSeleccion->nombre))
//                 array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'U','campo'=>'nombre','valor'=>$criteriosSeleccion->nombre]);
//             if(isset($criteriosSeleccion->apellido))
//                 array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'U','campo'=>'apellido','valor'=>$criteriosSeleccion->apellido]);
//                 if(isset($criteriosSeleccion->empresaId))
//                 {
//                     if($criteriosSeleccion->empresaId!="" && $criteriosSeleccion->empresaId!=null)
//                         array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'empresa_id','valor'=>$criteriosSeleccion->empresaId]);
//                 }
//                 if(isset($criteriosSeleccion->sedeId))
//                 {
//                     if($criteriosSeleccion->sedeId!="" && $criteriosSeleccion->sedeId!=null)
//                         array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'sede_id','valor'=>$criteriosSeleccion->sedeId]);
//                 }
//                 if(isset($criteriosSeleccion->tipoUsuarioId))
//                 {
//                     if($criteriosSeleccion->tipoUsuarioId!="" && $criteriosSeleccion->tipoUsuarioId!=null)
//                         array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'tipo_usuario_id','valor'=>$criteriosSeleccion->tipoUsuarioId]);
//                 }
//                 $where = $this->where($filtros);
//         }

        array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'tipo_usuario_id','valor'=> \TipoUsuario::ADMINISTRADOR]);
        $where = $this->where($filtros);
        
        $consulta =  $this->consultaBase .
        $where .
        " ORDER BY U.nombre";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            //if($sentencia->bind_param("ss",$criteriosSeleccion->nombre,$criteriosSeleccion->apellido))
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre)  )
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre);
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
    
    
    public function consultarSupervisoresPorEmpresa($empresaId,$usuarioId)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $resultado = $this->consultarIdsEmpresasCorporativo($empresaId);
       if($resultado->correcto())
       {
           $empresasIds = implode(",", $resultado->valor);
           
           $consulta =   $this->consultaBase .
           " WHERE U.empresa_id IN ($empresasIds)  " .
           " AND U.id != ? AND (U.tipo_usuario_id = 5 OR U.tipo_usuario_id = 2)  order by U.nombre, U.apellido";
           
           
           if($sentencia = $this->conexion->prepare($consulta))
           {
               if($sentencia->bind_param("i",$usuarioId))
               {
                   if($sentencia->execute())
                   {
                       if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre)  )
                       {
                           while($row = $sentencia->fetch())
                           {
                               $registro = $this->crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre);
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
       }
       
        return $resultado;
    }   
    
    public function consultarUsuariosPorEmpresa($empresaId)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $resultado = $this->consultarIdsEmpresasCorporativo($empresaId);
        if($resultado->correcto())
        {
            $empresasIds = implode(",", $resultado->valor);
            
            $consulta =   $this->consultaBase .
            " WHERE U.empresa_id IN ($empresasIds)  " .
            " order by U.nombre, U.apellido";
            
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre)  )
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre);
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
                $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
        }
        
        return $resultado;
    }   
    
    public function consultarPorEmpresaSede($usuario,$empresaId,$sedeId,$opcional)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $consulta =   $this->consultaBase .
        " WHERE U.empresa_id = ? " .
        " AND U.sede_id = ? ";
       
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ii",$empresaId,$sedeId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre)  )
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre);
                            array_push($registros,$registro);
                        }
                        if($opcional=="true")
                        {
                            if($usuario->tipoUsuarioId == \TipoUsuario::ADMINISTRADOR)
                            {
                                $registro = $this->crearRegistro("", "Todos los usuarios", null, null, null,null, null, null, null, null, null, null, null, null, null, null, null,null, null,null, null,null, null, null, null,null, null,null, null, null);
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
    
    public function consultarPorLLaves($llaves)
    {
        $resultado = new Resultado();
        
        $consulta =   $this->consultaBase .
        " WHERE U.id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$llaves->id))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre)  )
                    {
                        if($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre);
                            $resultado->valor = $registro;
                        }
                       
                      
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
    
    public function consultarPorCorreoElectronico($correoElectronico)
    {
        $resultado = new Resultado();
        
        $consulta =   $this->consultaBase .
        " WHERE U.nombre_usuario = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("s",$correoElectronico))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre)  )
                    {
                        if($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre);
                            $resultado->valor = $registro;
                        }
                        else
                            $resultado->mensajeError ="No se encontró ninguna cuenta asociada a este correo electrónico";
                      
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

    public function consultarUsuario($nombreUsuario,$contrasena)
    {
        $resultado = new Resultado();       
        $consulta =   $this->consultaBase .
                    " WHERE U.nombre_usuario = ? AND U.contrasena = ?";
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ss",$nombreUsuario,$contrasena))
            {
                if($sentencia->execute())
                {                    
                    if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,  $supervisor2Id, $supervisor2, $supervisor3Id, $supervisor3, $fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre)  )
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario,$contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre);
                            
                            $resultado->valor = $registro;
                        }
                        else
                            $resultado->mensajeError = "La combinación de usuario y contraseña es incorrecta.";
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

    public function consultarPorEmpresaSedeArea($empresaId, $sedeId, $areaId, $opcional,$usuario)
    {
        $resultado = new Resultado();
        $registros = array();
        $filtros = array();
        $where="";
        
        array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'E','campo'=>'id','valor'=>$empresaId]);
        array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'S','campo'=>'id','valor'=>$sedeId]);
        array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'A','campo'=>'id','valor'=>$areaId]);
        $where = $this->where($filtros);
        
        $consulta = $this->consultaBase .
        $where;
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombreUsuario,$contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre))
                    {
                        while($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario,$contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre);
                            array_push($registros,$registro);
                        }
                        if($opcional=="true")
                        {
                            $registro = $this->crearRegistro("", null,null, "Todos los usuarios", null,null, null, null, null, null, null, null, null, null, null, null, null,null, null,null, null,null, null, null, null,null, null,null, null, null,null,null);
                            array_unshift($registros, $registro);
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
    
    public function consultarPorEmpresaSedeDepartamento($empresaId, $sedeId, $departamentoId, $opcional,$usuario)
    {
        $resultado = new Resultado();
        $registros = array();
        $filtros = array();
        $where="";
        
        array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'E','campo'=>'id','valor'=>$empresaId]);
        array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'S','campo'=>'id','valor'=>$sedeId]);
        array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'D','campo'=>'id','valor'=>$departamentoId]);
        $where = $this->where($filtros);
        
        $consulta = $this->consultaBase .
        $where;
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombreUsuario,$contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre))
                    {
                        while($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario,$contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre);
                            array_push($registros,$registro);
                        }
                        if($opcional=="true")
                        {
                            $registro = $this->crearRegistro("", null,null, "Todos los usuarios", null,null, null, null, null, null, null, null, null, null, null, null, null,null, null,null, null,null, null, null, null,null, null,null, null, null,null,null);
                            array_unshift($registros, $registro);
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
    
    public function consultarEstructura($empresaId)
    {
        $resultado = $this->consultarUsuariosPorEmpresa($empresaId);
        if($resultado->correcto())
        {
            $resultado->valor = $this->crearEstructura($resultado->valor);
        }
        return $resultado;
    }
    
    public function consultarIdsUsuarios($usuario)
    {
        $resultado = new Resultado();
        $ids = array();
        $nodeId = $usuario->id;
        if($nodeId!="")
        {
            $resultado = $this->consultarEstructura($usuario->empresaId);
            if($resultado->correcto())
            {
                $estructura = $resultado->valor;
                $raiz = $this->buscarNodo($nodeId,$estructura);
                if($raiz!=null)
                {
                    array_push($ids, $raiz->nodeId);
                    $this->agregarUsuariosId($ids,$raiz);
                    $resultado->valor = $ids;
                }
                //echo $nodo->id;
//                 $raiz = $this->getRaiz($nodo);
//                 if($raiz!=null)
//                 {
//                     array_push($ids, $raiz->nodeId);
//                     $this->agregarEmpresasId($ids,$raiz);
//                     $resultado->valor = $ids;
//                 }
//                 else
//                 {
//                     $resultado->mensajeError="No se encontró la raiz de la empresa $nodo->text";
//                     $resultado->valor = null;
//                 }
            }
        }
        else
        {
            $resultado->mensajeError="No se encontró la empresa $nodoId";
            $resultado->valor = null;
        }
        return $resultado;
    }
    
    public function consultarIdsEmpresasCorporativo($nodoId)
    {
        $resultado = new Resultado();
        $ids = array();
        if($nodoId!="")
        {
            $empresasRepositorio = new EmpresasRepositorio($this->conexion);
            $resultado = $empresasRepositorio->consultarEstructura(true);
            if($resultado->correcto())
            {
                $estructura = $resultado->valor;
                $nodo = $this->buscarNodo($nodoId,$estructura);
                $raiz = $this->getRaiz($nodo);
                if($raiz!=null)
                {
                    array_push($ids, $raiz->nodeId);
                    $this->agregarEmpresasId($ids,$raiz);
                    $resultado->valor = $ids;
                }
                else
                {
                    $resultado->mensajeError="No se encontró la raiz de la empresa $nodo->text";
                    $resultado->valor = null;
                }
            }
        }
        else
        {
            $resultado->mensajeError="No se encontró la empresa $nodoId";
            $resultado->valor = null;
        }
        return $resultado;
    }
    
    
    public function consultarIdsEmpresas($nodoId)
    {
        $resultado = new Resultado();
        $ids = array();
        if($nodoId!="")
        {
            $empresasRepositorio = new EmpresasRepositorio($this->conexion);
            $resultado = $empresasRepositorio->consultarEstructura(true);
            if($resultado->correcto())
            {
                $estructura = $resultado->valor;
                $nodo = $this->buscarNodo($nodoId,$estructura);
                if($nodo!=null)
                {
                    array_push($ids, $nodo->nodeId);
                    $this->agregarEmpresasId($ids,$nodo);
                    $resultado->valor = $ids;
                }
                else
                {
                    $resultado->mensajeError="No se encontró la raiz de la empresa $nodo->text";
                    $resultado->valor = null;
                }
            }
        }
        else
        {
            $resultado->mensajeError="No se encontró la empresa $nodoId";
            $resultado->valor = null;
        }
        return $resultado;
    }
    
    private function agregarEmpresasId(&$ids,$nodo)
    {
        if(isset($nodo->nodes))
        {
            for ($i = 0; $i < count($nodo->nodes); $i++)
            {
                $nodoHijo =  $nodo->nodes[$i];
                array_push($ids, $nodoHijo->nodeId);
                $this->agregarEmpresasId($ids,$nodoHijo);
            }
        }
    }
    
    private function agregarUsuariosId(&$ids,$nodo)
    {
        if(isset($nodo->nodes))
        {
            for ($i = 0; $i < count($nodo->nodes); $i++)
            {
                $nodoHijo =  $nodo->nodes[$i];
                array_push($ids, $nodoHijo->nodeId);
                $this->agregarUsuariosId($ids,$nodoHijo);
            }
        }
    }
    
    private function getRaiz($nodo)
    {
        if($nodo->parent!=null)
        {
            return $this->getRaiz($nodo->parent);
        }
        else 
            return $nodo;
    }
    
    private function buscarNodo($nodeId,$estructura)
    {
        for ($i = 0; $i < count($estructura); $i++)
        {
            $nodo =  $estructura[$i];
            if($nodo->nodeId==$nodeId)
                return $nodo;
            else
            {
               if(isset($nodo->nodes))
               {
                    $nodo = $this->buscarNodo($nodeId, $nodo->nodes);
                    if($nodo!=null)
                        return $nodo;
               }
            }
        }
        return null;
    }
    
   
    
    private function crearEstructura($lista)
    {
        $estructura = array();
        for ($i = 0; $i < count($lista); $i++)
        {
            $nodo = $lista[$i];
            if($nodo->parentId==null || $nodo->parentId==0)
            {
                array_push($estructura,$nodo);
                $this->crearNodos($nodo, $lista);
            }
        }
        return $estructura;
    }
    
    private function crearNodos($nodoPadre, $lista)
    {
        for ($i = 0; $i < count($lista); $i++)
        {
            $nodoHijo = $lista[$i];
            
            if($nodoHijo->parentId == $nodoPadre->nodeId)
            {
                if(!isset($nodoPadre->nodes))
                    $nodoPadre->nodes = array();
                    array_push($nodoPadre->nodes ,$nodoHijo);
                    $this->crearNodos($nodoHijo, $lista);
            }
        }
    }
    
    
}

