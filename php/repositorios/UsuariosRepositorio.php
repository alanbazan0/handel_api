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
require_once("UsuariosProcesosRepositorio.php");
require_once("ProcedimientosRepositorio.php");
require_once("ProcesosRepositorio.php");
require_once("MinutasRepositorio.php");
require_once("AuditoriasRepositorio.php");
require_once("../vendor/simplexlsx/src/SimpleXLSX.php");

class UsuariosRepositorio extends RepositorioBase implements IUsuariosRepositorio
{
    protected $conexion;
    protected $consultaBase;
    protected $consultaSimple;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = "SELECT U.id, U.nombre_usuario, U.contrasena contrasena,U.nombre, U.apellido, E.id empresaId, IFNULL(E.nombre,'') empresa, S.id sedeId, IFNULL(S.nombre,'') sede, P.id puestoId, IFNULL(P.nombre,'') puesto, A.id areaId, IFNULL(A.nombre,'') area, T.id tipoUsuarioId, T.nombre tipo_usuario, SU1.id supervisor1Id, CONCAT(IFNULL(SU1.nombre,''),' ',IFNULL(SU1.apellido,'')) supervisor1,SU2.id supervisor2Id,CONCAT(IFNULL(SU2.nombre,''),' ',IFNULL(SU2.apellido,'')) supervisor2,SU3.id supervisor3Id, CONCAT(IFNULL(SU3.nombre,''),' ',IFNULL(SU3.apellido,'')) supervisor3, IFNULL(DATE_FORMAT(U.fecha_alta,'%d/%m/%Y %H:%i:%s'),'') fecha_alta,  IFNULL(DATE_FORMAT(U.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'')fecha_modificacion,IFNULL((SELECT IFNULL(DATE_FORMAT(fecha,'%d/%m/%Y %H:%i:%s'),'') as fecha FROM historial_acceso WHERE nombre_usuario= U.nombre_usuario ORDER BY id DESC LIMIT 1),'') ultimo_acceso, U.estatus, E.tipo_empresa_id, A.tipo_area_id, U.permiso_saha,U.permiso_sivah,U.permiso_10y7, U.departamento_id, D.nombre as departamentoNombre, U.permiso_cavi, U.perfil_id, PR.nombre, U.recursos_humanos, U.numero_empleado,E.estatus empresaEstatus, U.verificador, U.url_documentos, U.visualizar_socios_comerciales, E.servicio_id, U.reemplaza_usuario_id 
                             FROM usuarios U 
                               LEFT JOIN empresas E ON U.empresa_id=E.id
                               LEFT JOIN sedes S ON U.sede_id = S.id 
                               LEFT JOIN puestos P ON U.puesto_id = P.id 
                               LEFT JOIN areas A ON U.area_id = A.id 
                               LEFT JOIN tipos_usuario T ON U.tipo_usuario_id = T.id 
                               LEFT JOIN usuarios SU1 ON U.supervisor1_id = SU1.id 
                               LEFT JOIN usuarios SU2 ON U.supervisor2_id = SU2.id 
                               LEFT JOIN usuarios SU3 ON U.supervisor3_id = SU3.id 
                               LEFT JOIN departamentos D ON D.id = U.departamento_id
                               LEFT JOIN perfiles PR ON PR.id = U.perfil_id";
       
        $this->consultaBaseSimple = "SELECT U.id,U.nombre, U.apellido
                             FROM usuarios U
                               LEFT JOIN empresas E ON U.empresa_id=E.id
                               LEFT JOIN sedes S ON U.sede_id = S.id
                               LEFT JOIN puestos P ON U.puesto_id = P.id
                               LEFT JOIN areas A ON U.area_id = A.id
                               LEFT JOIN tipos_usuario T ON U.tipo_usuario_id = T.id
                               LEFT JOIN usuarios SU1 ON U.supervisor1_id = SU1.id
                               LEFT JOIN usuarios SU2 ON U.supervisor2_id = SU2.id
                               LEFT JOIN usuarios SU3 ON U.supervisor3_id = SU3.id
                               LEFT JOIN departamentos D ON D.id = U.departamento_id
                               LEFT JOIN perfiles PR ON PR.id = U.perfil_id";
    }    
    
    private function usuarioValido($usuario,$modelo)
    {
        $resultado = new Resultado();
        if($modelo->tipoUsuarioId == \TipoUsuario::INSPECTOR)
        {
            $resultado = $this->consultar($usuario,(object)["empresaId" => $modelo->empresaId, "tipoUsuarioId" => \TipoUsuario::INSPECTOR , "contrasena" => $modelo->contrasena], "");
            if($resultado->correcto())
            {
                 $usuarios = count($resultado->valor);
                 if($usuarios>0)
                 {
                    $resultado->mensajeError = "El PIN $modelo->contrasena ya esta siendo utilizado por otro usuario dentro de la empresa, ingrese uno diferente";   
                 }
                 
            }
        }
        return $resultado;
    }
    
    public function insertar($usuario,Usuario $modelo, $preventCommit = true)
    {
        
        $resultado = new Resultado();
        
        if($preventCommit)
            $this->conexion->autocommit(FALSE);
        
        $repositorio = new EmpresasRepositorio($this->conexion);
        
        $resultado = $repositorio->consultarPorLlaves((object)["id" => $modelo->empresaId ]);
        
        if($resultado->correcto())
        {
            $empresa = $resultado->valor;
            
            if($empresa->tokens > 0)
            {
                $resultado = $this->insertarUsuario($usuario, $modelo);
                if($resultado->correcto())
                {
                    $usuarioId = $resultado->valor;
                    $resultado = $repositorio->descontarTokens($empresa->id);     
                    if($resultado->correcto())
                    {
                        $resultado->valor = (object)["id"=>$usuarioId, "tokens" => $empresa->tokens - 1];;
                    }
                    
                }
            }
            else 
            {
                $resultado->mensajeError = "El número de créditos disponibles para tu empresa ha llegado a cero, para dar de alta más usuarios necesitas contactar a tu especialista en Handel que con gusto te informará del proceso para adquirir más tokens";
            }
        }
            
        if($preventCommit)
        {
            if($resultado->correcto())
                $this->conexion->commit();
            else
                $this->conexion->rollback();
        }
           
        
        return $resultado;
    }
   
   
    public function insertarUsuario($usuario,Usuario $modelo)
    {            
       
        $resultado = new Resultado();
        $resultado = $this->usuarioValido($usuario,$modelo);
        if($resultado->correcto())
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
            if($modelo->puestoId=="")
                $modelo->puestoId=null;
            if($modelo->numeroEmpleado=="")
                $modelo->numeroEmpleado=null;
            if($modelo->verificador=="")
                $modelo->verificador=null;
            if($modelo->reemplazaUsuarioId=="")
                $modelo->reemplazaUsuarioId=null;
            if($modelo->departamentoId=="")
                $modelo->departamentoId=null;
            
            $this->ajustarCampos($modelo);
                    
                
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
                            . " departamento_id, permiso_cavi, perfil_id, recursos_humanos,numero_empleado,verificador,url_documentos,visualizar_socios_comerciales,reemplaza_usuario_id) "
                            . " VALUE(?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW(),?,?,?,?,?,?,?,?,?,?,?,?,?) ";
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if( $sentencia->bind_param("issssiiiiiiiiiiiiiiiiiisii",
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
                        $modelo->departamentoId,
                        $modelo->permisoCAVI,
                        $modelo->perfilId,
                        $modelo->recursosHumanos,
                        $modelo->numeroEmpleado,
                        $modelo->verificador,
                        $modelo->urlDocumentos,
                        $modelo->visualizarAuditoriasSociosComerciales,
                        $modelo->reemplazaUsuarioId))
                    {
                        if($sentencia->execute())          
                        {
                            $sentencia->close();
                            $resultado = $this->insertarTiposSocioComercial($modelo);
                            $resultado->valor = $id;
                        }
                        else
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
                            $resultado = $this->eliminarTiposSocioComercial($llaves->id);
                            if($resultado->correcto())
                            {
                                $resultado = $this->eliminarUsuario($llaves);
                                if($resultado->correcto())
                                {
                                    
                                }
                                else
                                {
                                    if($resultado->codigoError==1451)
                                    {
                                        $resultado->valor = $this->consultarRelaciones($llaves);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        if($resultado->correcto())    
            $this->conexion->commit();
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
        
//         $repositorio = new CursosRepositorio($this->conexion);
//         $resultado = $repositorio->consultar((object) ['usuarioId' => $llaves->id]);
//         if($resultado->correcto())
//         {
//             array_push($relaciones,(object)['nombre'=>'Capacitaciones','registros'=>$resultado->valor]);
//         }
      
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
    
    private function desactivarUsuario($usuarioId)
    {
        $resultado = new Resultado();
        $consulta = " UPDATE usuarios 
            SET estatus = 0, fecha_modificacion = NOW()
            WHERE id = ?";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$usuarioId))
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
        if($modelo->verificador=="")
            $modelo->verificador=null;
        if($modelo->reemplazaUsuarioId=="")
            $modelo->reemplazaUsuarioId=null;
        if($modelo->departamentoId=="")
            $modelo->departamentoId=null;
        
         $this->ajustarCampos($modelo);
            
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
                    " departamento_id = ?, " .
                    " permiso_cavi = ?," .
                    " perfil_id = ?, " .
                    " recursos_humanos = ?, " .
                    " numero_empleado = ? ," .
                    " verificador = ?, 
                      url_documentos = ?,
                    visualizar_socios_comerciales = ?,
                    reemplaza_usuario_id = ? " .
                    "WHERE id = ?";    
                        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ssssiiiiiiiiiiiiiiiiiisiii",
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
                $modelo->permisoCAVI,
                $modelo->perfilId,
                $modelo->recursosHumanos,
                $modelo->numeroEmpleado,
                $modelo->verificador,
                $modelo->urlDocumentos,
                $modelo->visualizarAuditoriasSociosComerciales,
                $modelo->reemplazaUsuarioId,
                $modelo->id))
            {
               if($sentencia->execute())
               {
                   $sentencia->close();
                   $resultado = $this->eliminarTiposSocioComercial($modelo->id);
                   if($resultado->correcto())
                   {
                       $resultado = $this->insertarTiposSocioComercial($modelo);
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
    
    public function eliminarTiposSocioComercial($usuarioId)
    {
        $resultado = new Resultado();
        
        $consulta ="DELETE FROM usuarios_tipos_socio_comercial WHERE usuario_id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$usuarioId))
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
    
    private function insertarTiposSocioComercial($usuario)
    {
        $resultado = new Resultado();
        
        if(isset($usuario->tiposSocioComercial) && $usuario->tiposSocioComercial!=null)
        {
            for ($l = 0; $l< count($usuario->tiposSocioComercial); $l++)
            {
                $tipoSocioComercial = $usuario->tiposSocioComercial[$l];
                
                $consulta = "INSERT INTO usuarios_tipos_socio_comercial(usuario_id, tipo_socio_comercial_id) " .
                    "VALUE(?, ?)";
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if($sentencia->bind_param("ii",$usuario->id, $tipoSocioComercial->tipoSocioComercialId))
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
    
    public function actualizarNombreApellido($modelo)
    {
        $this->ajustarCampos($modelo);
        
        $resultado = new Resultado();
        $consulta = " UPDATE usuarios " .
                    "SET nombre_usuario = ?, " .
                    " nombre = ?, " .
                    " apellido = ? " .
                    "WHERE id = ?";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("sssi",
                $modelo->nombreUsuario,
                $modelo->nombre,
                $modelo->apellido,
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
    
    private function crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id, $supervisor2, $supervisor3Id, $supervisor3, $fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus, $tipoEmpresaId, $tipoAreaId, $permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos, $visualizarAuditoriasSociosComerciales=null, $servicio=null, $reemplazaUsuarioId=null)
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
            'departamentoNombre' => $departamentoNombre,
            'permisoCAVI' => $permisoCAVI,
            'perfilId' => $perfilId,
            'perfilNombre' => $perfilNombre,
            'recursosHumanos' => $recursosHumanos,
            'numeroEmpleado' => $numeroEmpleado,
            'empresaEstatus' => $empresaEstatus,
            'verificador' => $verificador,
            'urlDocumentos' => $urlDocumentos,
            'visualizarAuditoriasSociosComerciales' => $visualizarAuditoriasSociosComerciales,
            'servicioId' => $servicio,
            'reemplazaUsuarioId' => $reemplazaUsuarioId
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
    
   
    public function getFiltroEstructura($usuario,$criteriosSeleccion)
    {
        $filtros = array();
        
        if($usuario!=null)
        {
            if($usuario->recursosHumanos==1)
            {
                if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
                {
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'empresa_id','valor'=>$criteriosSeleccion->empresaId]);
                }
                else
                {
                    //$usuariosRepositorio = new UsuariosRepositorio($this->conexion);
                    $resultado = $this->consultarIdsEmpresas($usuario->empresaId);
                    if($resultado->correcto())
                    {
                        $empresasIds = implode(",", $resultado->valor);
                        array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'empresa_id','operador'=>'IN','valor'=>$empresasIds]);
                    }
                }
            }
            else
            {
                switch ($usuario->tipoUsuarioId)
                {
                    case \TipoUsuario::SUPERVISOR:
                        //$usuariosRepositorio = new UsuariosRepositorio($this->conexion);
                        $resultado = $this->consultarIdsUsuarios($usuario);
                        if($resultado->correcto())
                        {
                            $usuariosIds = implode(",", $resultado->valor);
                            //$and =" AND U.id IN($usuariosIds)";
                            array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'id','operador'=>'IN','valor'=>$usuariosIds]);
                        }
                    break;
                    case \TipoUsuario::COORDINADOR:
                        if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
                        {
                            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'empresa_id','valor'=>$criteriosSeleccion->empresaId]);
                        }
                        else
                        {
                            //$usuariosRepositorio = new UsuariosRepositorio($this->conexion);
                            $resultado = $this->consultarIdsEmpresas($usuario->empresaId);
                            if($resultado->correcto())
                            {
                                $empresasIds = implode(",", $resultado->valor);
                                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'empresa_id','operador'=>'IN','valor'=>$empresasIds]);
                            }
                        }
                    break;
                        
                        
                    case \TipoUsuario::ADMINISTRADOR:
                        if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
                        {
                            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'empresa_id','valor'=>$criteriosSeleccion->empresaId]);
                        }
                        
                    break;
                    
                    default:
                        if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
                        {
                            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'empresa_id','valor'=>$criteriosSeleccion->empresaId]);
                        }
                        else
                        {
                           // if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
                            //{
                                array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'empresa_id','valor'=>9999999999]);
                            //}
                            
                        }
                        
                    break;
                }
            }
        }
        else
        {
            if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
            {
                array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'empresa_id','valor'=>$criteriosSeleccion->empresaId]);
            }
        }
        return $filtros;
    }
    
    public function consultar($usuario,$criteriosSeleccion,$opcional)
    {     
        $resultado = new Resultado();
        $registros = array();     
       
        $filtros = $this->getFiltroEstructura($usuario, $criteriosSeleccion);
        $where="";
        if($criteriosSeleccion!=null)
        {
            if(isset($criteriosSeleccion->nombre))
                array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'U','campo'=>'nombre','valor'=>$criteriosSeleccion->nombre]);
            if(isset($criteriosSeleccion->apellido))
                array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'U','campo'=>'apellido','valor'=>$criteriosSeleccion->apellido]);
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
            if(isset($criteriosSeleccion->departamentoId))
            {
                if($criteriosSeleccion->departamentoId!="" && $criteriosSeleccion->departamentoId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'departamento_id','valor'=>$criteriosSeleccion->departamentoId]);
            }
            if(isset($criteriosSeleccion->perfilId))
            {
                if($criteriosSeleccion->perfilId!="" && $criteriosSeleccion->perfilId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'perfil_id','valor'=>$criteriosSeleccion->perfilId]);
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
            if(isset($criteriosSeleccion->permisoSIVAH))
            {
                if($criteriosSeleccion->permisoSIVAH!="" && $criteriosSeleccion->permisoSIVAH!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'permiso_sivah','valor'=>$criteriosSeleccion->permisoSIVAH]);
            }
            if(isset($criteriosSeleccion->permiso10y7))
            {
                if($criteriosSeleccion->permiso10y7!="" && $criteriosSeleccion->permiso10y7!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'permiso_10y7','valor'=>$criteriosSeleccion->permiso10y7]);
            }
            if(isset($criteriosSeleccion->permisoCAVI))
            {
                if($criteriosSeleccion->permisoCAVI!="" && $criteriosSeleccion->permisoCAVI!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'permiso_cavi','valor'=>$criteriosSeleccion->permisoCAVI]);
            }
            if(isset($criteriosSeleccion->estatus))
            {
                if($criteriosSeleccion->estatus!="" && $criteriosSeleccion->estatus!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'estatus','valor'=>$criteriosSeleccion->estatus]);
            }
            if(isset($criteriosSeleccion->supervisor1Id))
            {
                if($criteriosSeleccion->supervisor1Id!="" && $criteriosSeleccion->supervisor1Id!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'supervisor1_id','valor'=>$criteriosSeleccion->supervisor1Id]);
            }
            if(isset($criteriosSeleccion->contrasena))
            {
                if($criteriosSeleccion->contrasena!="" && $criteriosSeleccion->contrasena!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'contrasena','valor'=>$criteriosSeleccion->contrasena]);
            }
            if(isset($criteriosSeleccion->visualizarSociosComerciales))
            {
                if($criteriosSeleccion->visualizarSociosComerciales!="" && $criteriosSeleccion->visualizarSociosComerciales!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'visualizar_socios_comerciales','valor'=>$criteriosSeleccion->visualizarSociosComerciales]);
            }
            
            
            //$where = $this->where($filtros);
        }
        array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'E','campo'=>'estatus','valor'=>1]);
        $where = $this->where($filtros);
        $consulta =  $this->consultaBase .
                    $where .
                     " ORDER BY U.nombre, U.apellido";
        
        //var_dump($consulta);

        if($sentencia = $this->conexion->prepare($consulta))
        {
            //if($sentencia->bind_param("ss",$criteriosSeleccion->nombre,$criteriosSeleccion->apellido))
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {                
                    if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre, $permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales, $servicio,$reemplazaUsuarioId))
                    {                    
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales, $servicio,$reemplazaUsuarioId);
                            array_push($registros,$registro);
                        }
                        if($opcional=="true")
                        {
                            if($usuario->tipoUsuarioId == \TipoUsuario::ADMINISTRADOR)
                            {
                                $registro = $this->crearRegistro("", "", null, "Todos los usuarios", null,null, null, null, null, null, null, null, null, null, null, null, null,null, null,null, null,null, null, null, null,null, null,null, null, null,null,null,null,null,null,null,null,null,null,null);
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
    
    public function consultarSimple($usuario,$criteriosSeleccion,$opcional)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $filtros = $this->getFiltroEstructura($usuario, $criteriosSeleccion);
        $where="";
        if($criteriosSeleccion!=null)
        {
            if(isset($criteriosSeleccion->nombre))
                array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'U','campo'=>'nombre','valor'=>$criteriosSeleccion->nombre]);
                if(isset($criteriosSeleccion->apellido))
                    array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'U','campo'=>'apellido','valor'=>$criteriosSeleccion->apellido]);
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
                    if(isset($criteriosSeleccion->departamentoId))
                    {
                        if($criteriosSeleccion->departamentoId!="" && $criteriosSeleccion->departamentoId!=null)
                            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'departamento_id','valor'=>$criteriosSeleccion->departamentoId]);
                    }
                    if(isset($criteriosSeleccion->perfilId))
                    {
                        if($criteriosSeleccion->perfilId!="" && $criteriosSeleccion->perfilId!=null)
                            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'perfil_id','valor'=>$criteriosSeleccion->perfilId]);
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
                    if(isset($criteriosSeleccion->permisoSIVAH))
                    {
                        if($criteriosSeleccion->permisoSIVAH!="" && $criteriosSeleccion->permisoSIVAH!=null)
                            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'permiso_sivah','valor'=>$criteriosSeleccion->permisoSIVAH]);
                    }
                    if(isset($criteriosSeleccion->permiso10y7))
                    {
                        if($criteriosSeleccion->permiso10y7!="" && $criteriosSeleccion->permiso10y7!=null)
                            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'permiso_10y7','valor'=>$criteriosSeleccion->permiso10y7]);
                    }
                    if(isset($criteriosSeleccion->permisoCAVI))
                    {
                        if($criteriosSeleccion->permisoCAVI!="" && $criteriosSeleccion->permisoCAVI!=null)
                            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'permiso_cavi','valor'=>$criteriosSeleccion->permisoCAVI]);
                    }
                    if(isset($criteriosSeleccion->estatus))
                    {
                        if($criteriosSeleccion->estatus!="" && $criteriosSeleccion->estatus!=null)
                            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'estatus','valor'=>$criteriosSeleccion->estatus]);
                    }
                    if(isset($criteriosSeleccion->supervisor1Id))
                    {
                        if($criteriosSeleccion->supervisor1Id!="" && $criteriosSeleccion->supervisor1Id!=null)
                            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'supervisor1_id','valor'=>$criteriosSeleccion->supervisor1Id]);
                    }
                    $where = $this->where($filtros);
        }
        
        $consulta = $this->consultaBaseSimple .
        $where .
        " ORDER BY nombre, apellido";
        
        //var_dump($consulta);
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            //if($sentencia->bind_param("ss",$criteriosSeleccion->nombre,$criteriosSeleccion->apellido))
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombre, $apellido)  )
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = (object)[ 
                                "id" => $id,
                                "nombre" => $nombre,
                                "apellido" => $apellido
                            ];
                            $registro->nombreCompleto = $registro->nombre . " " . $registro->apellido;
                            array_push($registros,$registro);
                        }
                        if($opcional=="true")
                        {
                            //if($usuario->tipoUsuarioId == \TipoUsuario::ADMINISTRADOR)
                            //{
                                $registro = (object)[
                                    "id" => "",
                                    "nombreCompleto" => "Todos los usuarios",
                                    "apellido" => ""
                                ];
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
    
    public function consultarUsuarios($usuariosIds)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $where="";
        $filtros = array();
        array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'id','operador'=>'IN','valor'=>$usuariosIds]);
        $where = $this->where($filtros);
    
        
        $consulta = $this->consultaBaseSimple .
        $where .
        " ORDER BY nombre, apellido";
        
        //var_dump($consulta);
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            //if($sentencia->bind_param("ss",$criteriosSeleccion->nombre,$criteriosSeleccion->apellido))
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombre, $apellido)  )
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = (object)[
                                "id" => $id,
                                "nombre" => $nombre,
                                "apellido" => $apellido
                            ];
                            $registro->nombreCompleto = $registro->nombre . " " . $registro->apellido;
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
    
    
    public function consultarPorPermiso($usuario,$criteriosSeleccion,$opcional)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $filtros = $this->getFiltrosN($usuario, $criteriosSeleccion);
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
                    if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre, $permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId)  )
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId);
                            array_push($registros,$registro);
                        }
                        if($opcional=="true")
                        {
                            if($usuario->tipoUsuarioId == \TipoUsuario::ADMINISTRADOR)
                            {
                                $registro = $this->crearRegistro("", "Todos los usuarios", null, null, null,null, null, null, null, null, null, null, null, null, null, null, null,null, null,null, null,null, null, null, null,null, null,null, null, null,null,null,null,null,null);
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
        array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'estatus','valor'=> 1]);
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
                    if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId)  )
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId);
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
        
        if(!isset($usuarioId))
            $usuarioId=0;
        
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
                       if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId)  )
                       {
                           while($row = $sentencia->fetch())
                           {
                               $registro = $this->crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId);
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
    
    public function consultarUsuariosCorportarivoYAdministradores($usuario)
    {
        $resultado = new Resultado();
        $registros = array();
        
        if($usuario->tipoUsuarioId==\TipoUsuario::ADMINISTRADOR)
        {
            $consulta =   $this->consultaBase .
            " WHERE U.permiso_saha = 1 " .
            " order by U.nombre, U.apellido";
        }
        else 
        {
            $resultado = $this->consultarIdsEmpresasCorporativo($usuario->empresaId);
            if($resultado->correcto())
            {
                $empresasIds = implode(",", $resultado->valor);
                
                $consulta =   $this->consultaBase .
                " WHERE (U.empresa_id IN ($empresasIds)  AND U.permiso_saha = 1) " .
                " OR U.tipo_usuario_id = 1  order by U.nombre, U.apellido";
            }
        }
            
//         $resultado = $this->consultarIdsEmpresasCorporativo($usuario->empresaId);
//         if($resultado->correcto())
//         {
//             $empresasIds = implode(",", $resultado->valor);
            
//             $consulta =   $this->consultaBase .
//             " WHERE U.empresa_id IN ($empresasIds)  " .
//             " OR U.tipo_usuario_id = 1  order by U.nombre, U.apellido";
            
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
//                 if($sentencia->bind_param("i",$usuarioId))
//                 {
                    if($sentencia->execute())
                    {
                        if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId)  )
                        {
                            while($row = $sentencia->fetch())
                            {
                                $registro = $this->crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId);
                                array_push($registros,$registro);
                            }
                            $resultado->valor = $registros;
                        }
                        else
                            $resultado->mensajeError = "Falló el enlace del resultado.";
                    }
                    else
                        $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
//                 }
//                 else
//                     $resultado->mensajeError = "Falló el enlace de parámetros";
            }
            else
                $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
        //}
        return $resultado;
    }   
    
    public function consultarUsuariosCorportarivoYAdministradoresPorEmpresa($empresaId)
    {
        $resultado = new Resultado();
        $registros = array();
       
        $resultado = $this->consultarIdsEmpresasCorporativo($empresaId);
        if($resultado->correcto())
        {
            $empresasIds = implode(",", $resultado->valor);
            
            $consulta =   $this->consultaBase .
            " WHERE (U.empresa_id IN ($empresasIds) AND U.estatus = 1) " .
            " OR (U.tipo_usuario_id = 1 AND U.estatus = 1)
                
            ORDER BY U.nombre, U.apellido";
        }
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->execute())
            {
                if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId))
                {
                    while($row = $sentencia->fetch())
                    {
                        $registro = $this->crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId);
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
            return $resultado;
    }   
    
    public function consultarUsuariosCorportarivoYAdministradoresPorEmpresaSIVAH($empresaId)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $resultado = $this->consultarIdsEmpresasCorporativo($empresaId);
        if($resultado->correcto())
        {
            $empresasIds = implode(",", $resultado->valor);
            
            $consulta =   $this->consultaBase .
            " WHERE (U.empresa_id IN ($empresasIds) AND U.permiso_sivah=1) " .
            " OR U.tipo_usuario_id = 1  
            order by U.nombre, U.apellido";
         
        
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId);
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
    
    public function consultarUsuariosCorportarivoPorEmpresaSIVAH($empresaId)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $resultado = $this->consultarIdsEmpresasCorporativo($empresaId);
        if($resultado->correcto())
        {
            $empresasIds = implode(",", $resultado->valor);
            
            $consulta =   $this->consultaBase .
            " WHERE (U.empresa_id IN ($empresasIds) AND U.permiso_sivah=1)
            order by U.nombre, U.apellido";
        }
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->execute())
            {
                if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId)  )
                {
                    while($row = $sentencia->fetch())
                    {
                        $registro = $this->crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId);
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
                    if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId)  )
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId);
                            array_push($registros,$registro);
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
                $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
        }
        
        return $resultado;
    }   
    
    public function consultarPorEmpresaSede($usuario,$empresaId,$sedeId,$opcional)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $consulta =   $this->consultaBase .
        " WHERE U.empresa_id = ? 
        AND U.sede_id = ?
        ORDER BY U.nombre, U.apellido";
       
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ii",$empresaId,$sedeId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId)  )
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId);
                            array_push($registros,$registro);
                        }
                        if($opcional=="true")
                        {
                            if($usuario->tipoUsuarioId == \TipoUsuario::ADMINISTRADOR)
                            {
                                $registro = $this->crearRegistro("", "Todos los usuarios", null, null, null,null, null, null, null, null, null, null, null, null, null, null, null,null, null,null, null,null, null, null, null,null, null,null, null, null,null,null,null,null,null);
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
    
    public function consultarUsuariosAplicacion10y7($empresaId,$sedeId)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $consulta =   $this->consultaBase .
        " WHERE U.estatus = 1  
            AND (U.tipo_usuario_id = 2 OR U.tipo_usuario_id = 3 OR U.tipo_usuario_id = 4 OR  U.tipo_usuario_id = 5)
            AND U.empresa_id = ? 
            AND U.sede_id = ? 
        ORDER BY U.nombre, U.apellido";
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ii",$empresaId,$sedeId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId)  )
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$reemplazaUsuarioId);
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
                    if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId)  )
                    {
                        if($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId);
                            $sentencia->close();
                            
                            $resultadoTipoSocioComercial = $this->consultarTiposSocioComercial($llaves->id);
                            if($resultadoTipoSocioComercial->correcto())
                            {
                                $registro->tiposSocioComercial = $resultadoTipoSocioComercial->valor;
                            }
                            else
                            {
                                $resultado->mensajeError = $resultadoTipoSocioComercial->mensajeError;
                            }
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
    
    private function consultarTiposSocioComercial($usuarioId)
    {
        $resultado = new Resultado();
        $sociosComerciales = array();
        $consulta = "SELECT UTSC.id, UTSC.tipo_socio_comercial_id, TSC.nombre " .
            "FROM usuarios_tipos_socio_comercial UTSC
                INNER JOIN tipos_socio_comercial TSC ON TSC.id = UTSC.tipo_socio_comercial_id
             WHERE UTSC.usuario_id = ? ".
             "ORDER BY UTSC.id";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            
            if($sentencia->bind_param("i",$usuarioId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id,$tipoSocioComercialId, $tipoSociocomercialNombre))
                    {
                        while($sentencia->fetch())
                        {
                            $socioComercial= (object) [
                                'id' =>  $id,
                                'tipoSocioComercialId' =>  $tipoSocioComercialId,
                                'tipoSociocomercialNombre' => $tipoSociocomercialNombre
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
                    if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId)  )
                    {
                        if($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId);
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
        $nombreUsuario = trim($nombreUsuario);
        $contrasena = trim($contrasena);
        $resultado = new Resultado();       
        $consulta =   $this->consultaBase .
                    " WHERE TRIM(U.nombre_usuario) = ? AND TRIM(U.contrasena) = ? ";
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ss",$nombreUsuario,$contrasena))
            {
                if($sentencia->execute())
                {                    
                    if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,  $supervisor2Id, $supervisor2, $supervisor3Id, $supervisor3, $fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario,$contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId);
                            
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
                    if ($sentencia->bind_result($id, $nombreUsuario,$contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId))
                    {
                        while($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario,$contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId);
                            array_push($registros,$registro);
                        }
                        if($opcional=="true")
                        {
                            $registro = $this->crearRegistro("", null,null, "Todos los usuarios", null,null, null, null, null, null, null, null, null, null, null, null, null,null, null,null, null,null, null, null, null,null, null,null, null, null,null,null,null,null,null,null,null);
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
        
        //if($empresaId!="")
            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'E','campo'=>'id','valor'=>$empresaId]);
        if($sedeId!="")
            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'S','campo'=>'id','valor'=>$sedeId]);
        if($departamentoId!="")
            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'D','campo'=>'id','valor'=>$departamentoId]);
        
        
        
        $where = $this->where($filtros);
        
        $consulta = $this->consultaBase .
        $where . " ORDER BY U.nombre, U.apellido ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombreUsuario,$contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId))
                    {
                        while($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario,$contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId);
                            array_push($registros,$registro);
                        }
                        if($opcional=="true")
                        {
                            $registro = $this->crearRegistro("", null,null, "Todos los usuarios", null,null, null, null, null, null, null, null, null, null, null, null, null,null, null,null, null,null, null, null, null,null, null,null, null, null,null,null, null, null, null,null,null,null,null,null);
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
    
    public function getFiltrosN($usuario, $criteriosSeleccion)
    {
        $filtros = array();
        switch ($usuario->tipoUsuarioId)
        {
            case \TipoUsuario::USUARIO:
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'id','operador'=>'IN','valor'=>$usuario->id]);
            break;
            case \TipoUsuario::SUPERVISOR:
                if(isset($criteriosSeleccion->usuarioId) && $criteriosSeleccion->usuarioId!="")
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'id','valor'=>$criteriosSeleccion->usuarioId]);
                    else
                    {
                        //$usuariosRepositorio = new UsuariosRepositorio($this->conexion);
                        $resultado = $this->consultarIdsUsuarios($usuario);
                        if($resultado->correcto())
                        {
                            $usuariosIds = implode(",", $resultado->valor);
                            array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'id','operador'=>'IN','valor'=>$usuariosIds]);
                        }
                    }
                    if(isset($criteriosSeleccion->usuarioId) && $criteriosSeleccion->usuarioId!="")
                        array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'id','valor'=> $criteriosSeleccion->usuarioId]);
            break;
            case \TipoUsuario::COORDINADOR:
                    if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
                        array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'E', 'campo'=>'id','valor'=>$criteriosSeleccion->empresaId]);
                    else
                    {
                       // $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
                        $resultado = $this->consultarIdsEmpresas($usuario->empresaId);
                        if($resultado->correcto())
                        {
                            $empresasIds = implode(",", $resultado->valor);
                            array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'E', 'campo'=>'id','operador'=>'IN','valor'=>$empresasIds]);
                        }
                    }
                    if(isset($criteriosSeleccion->usuarioId) && $criteriosSeleccion->usuarioId!="")
                        array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'id','valor'=> $criteriosSeleccion->usuarioId]);
            break;
            case \TipoUsuario::ADMINISTRADOR:
                if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'E', 'campo'=>'id','valor'=>$criteriosSeleccion->empresaId]);
                if(isset($criteriosSeleccion->usuarioId) && $criteriosSeleccion->usuarioId!="")
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'id','valor'=> $criteriosSeleccion->usuarioId]);
            break;
        }
        if(isset($criteriosSeleccion->sedeId) && $criteriosSeleccion->sedeId!="")
            array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'S', 'campo'=>'id','valor'=> $criteriosSeleccion->sedeId]);
        if(isset($criteriosSeleccion->departamentoId) && $criteriosSeleccion->departamentoId!="")
            array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'D', 'campo'=>'id','valor'=> $criteriosSeleccion->departamentoId]);
        if(isset($criteriosSeleccion->tipoUsuarioId) && $criteriosSeleccion->tipoUsuarioId!="" && $criteriosSeleccion->tipoUsuarioId!=null)
            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'tipo_usuario_id','valor'=>$criteriosSeleccion->tipoUsuarioId]);
        if(isset($criteriosSeleccion->permisoSAHA) && $criteriosSeleccion->permisoSAHA!="" && $criteriosSeleccion->permisoSAHA!=null)
           array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'permiso_saha','valor'=>$criteriosSeleccion->permisoSAHA]);
        if(isset($criteriosSeleccion->permisoSIVAH) && $criteriosSeleccion->permisoSIVAH!="" && $criteriosSeleccion->permisoSIVAH!=null)
           array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'permiso_sivah','valor'=>$criteriosSeleccion->permisoSIVAH]);
            
        return $filtros;
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
                else
                {
                    array_push($ids, $nodeId);
                    $resultado->valor = $ids;
                }
            }
        }
        else
        {
            $resultado->mensajeError="No se encontró el registro $nodeId";
            $resultado->valor = null;
        }
        return $resultado;
    }
    
    public function esCoordinadorCorporativo($usuario)
    {
        $empresasRepositorio = new EmpresasRepositorio($this->conexion);
        $resultado = $empresasRepositorio->consultarEstructura(true);
        if($resultado->correcto())
        {
            $estructura = $resultado->valor;
            $nodo = $this->buscarNodo($usuario->empresaId,$estructura);
            if($nodo!=null)
            {
                if($nodo->parent==null && isset($nodo->nodes))
                {
                    if(count($nodo->nodes)>0)
                        return true;
                }
            }
        }
        return false;
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
    
    public function existeUsuarioArreglo($usuarioId,$usuarios)
    {
        for ($i = 0; $i < count($usuarios); $i++)
        {
            $usuario = $usuarios[$i];
            if($usuario->id == $usuarioId)
                return true;
        }
        return false;
    }
    
    public function getIndiceArreglo($usuarioId,$usuarios)
    {
        for ($i = 0; $i < count($usuarios); $i++)
        {
            $usuario = $usuarios[$i];
            if($usuario->id == $usuarioId)
                return $i;
        }
        return -1;
    }
    
    public function eliminarUsuarioArreglo($usuarioId,&$usuarios)
    {
        $indice = $this->getIndiceArreglo($usuarioId,$usuarios);
        if($indice>=0 &&  $indice< count($usuarios))
        {
            array_splice($usuarios, $indice, 1);
        }
    }
    
    public function importarUsuarios($usuarioImportacion,$empresaId, $sedeId, $departamentoId,$perfilId,$supervisor1Id, $carpeta, $nombreArchivo)
    {
        $resultadoFinal = new Resultado();
        ini_set('max_execution_time', 300);
       // $this->conexion->autocommit(FALSE);
        
        if($supervisor1Id=="")
            $supervisor1Id=null;
        
        if($perfilId=="")
            $perfilId=null;
               
        $resultados = array();    
        
        if($xlsx = \SimpleXLSX::parse("../".$carpeta."/" .$nombreArchivo)) 
        {
            $i = 0;
            $registros = 0;
            foreach ($xlsx->rows() as $elt) 
            {
                if($i>=5) // empieza en renglon 6
                {
                    $resultado =  $this->calcularId("id","usuarios");
                    if($resultado->correcto())
                    {
                        $id = $resultado->valor;
                        
                        $usuario = new Usuario();
                        $usuario->empresaId = $empresaId;
                        $usuario->sedeId = $sedeId;
                        $usuario->departamentoId = $departamentoId;
                        $usuario->perfilId = $perfilId;
                        $usuario->supervisor1Id= $supervisor1Id;
                        
                        $usuario->numeroEmpleado = $elt[0];
                        
                        $usuario->nombre = mb_convert_case($elt[1], MB_CASE_TITLE, "UTF-8");
                        $usuario->apellido = mb_convert_case($elt[2], MB_CASE_TITLE, "UTF-8");
                        $usuario->nombreCompleto = $usuario->nombre . " " .  $usuario->apellido;
                        
                        $nombres = explode(' ',trim($this->quitarTildes($usuario->nombre)));
                        $nombre ="";
                        if(count($nombres)>0)
                        {
                            $nombre = strtolower($nombres[0]);
                            $nombre = $nombre[0];
                        }
                        
                        $apellidos = explode(' ',trim($this->quitarTildes($usuario->apellido)));
                        $apellido ="";
                        if(count($apellidos)>0)
                        {
                            $apellido = strtolower($apellidos[0]);
                        }
                    
                        $usuario->nombreUsuario = $nombre.$apellido.$id ;
                        $usuario->contrasena = rand(1000,9999); 
                        $usuario->tipoUsuarioId = \TipoUsuario::CAPACITADO;
                        $usuario->estatus = 1;
                        $usuario->permisoCAVI = 1;
                        
                        $criteriosSeleccion= (object) [
                            'empresaId' =>  $empresaId,
                            'nombre' => $usuario->nombre,
                            'apellido' => $usuario->apellido,
                            'tipoUsuarioId' => \TipoUsuario::CAPACITADO
                            ];
                        
                        
                        
                        $resultado = $this->consultar($usuario, $criteriosSeleccion, false);
                        if($resultado->correcto())
                        {
                            $usuarioEntrontrados  = $resultado->valor;
                            if(count($usuarioEntrontrados)==0)
                            {
                                $resultado = $this->insertar($usuarioImportacion,$usuario,true);
                                if($resultado->correcto())
                                {
                                    $registros++;
                                    array_push($resultados, (object)["usuario"=> $usuario, "existe" => true, "resultado" => $resultado]);
                                }
                                else
                                {
                                    array_push($resultados, (object)["usuario"=> $usuario, "existe" => true, "resultado" => $resultado]);
                                    break;
                                }
                            }
                            else
                            {
                                array_push($resultados, (object)["usuario"=> $usuario, "existe" => true]);
                            }
                                
                        }
                    }
                }
                $i++;
            }
            $empresasRepositorio = new EmpresasRepositorio($this->conexion);
            $resultadoEmpresa = $empresasRepositorio->consultarPorLlaves((object)["id" => $empresaId ]);
            
            $tokensRestantes = 0;
            if($resultadoEmpresa->correcto())
            {
                $empresa = $resultadoEmpresa->valor;
                $tokensRestantes = $empresa->tokens;
            }
            
            $resultadoFinal = new Resultado();
            $resultadoFinal->mensajeError = $resultado->mensajeError;
            $resultadoFinal->valor = (object) ["insertados" => $registros, "resultados" => $resultados, "tokensRestantes" => $tokensRestantes];
        }
        else
        {
            $resultadoFinal->mensajeError =  \SimpleXLSX::parseError();
            
        }
        
       /* if($resultado->correcto())
            $this->conexion->commit();
        else
            $this->conexion->rollback();*/
            
        return $resultadoFinal;    
    }
    
    public function importar($usuarioImportacion,$empresaId, $sedeId, $departamentoId,$perfilId,$supervisor1Id, $carpeta, $nombreArchivo)
    {
        /*$resultado = new Resultado();
        $repositorio = new EmpresasRepositorio($this->conexion);
        
        $resultado = $repositorio->consultarPorLlaves((object)["id" => $empresaId ]);
        
        if($resultado->correcto())
        {
            $empresa = $resultado->valor;
            
            if($empresa->tokens > 0)
            {
                $resultado = $this->importarUsuarios($usuarioImportacion, $empresaId, $sedeId, $departamentoId, $perfilId, $supervisor1Id, $carpeta, $nombreArchivo);
            }
            else
            {
                $resultado->mensajeError = "El número de créditos disponibles para tu empresa ha llegado a cero, para dar de alta más usuarios necesitas contactar a tu especialista en Handel que con gusto te informará del proceso para adquirir más tokens";
            }
        }
        return $resultado;*/
        $resultado = $this->importarUsuarios($usuarioImportacion, $empresaId, $sedeId, $departamentoId, $perfilId, $supervisor1Id, $carpeta, $nombreArchivo);
        
        return $resultado;
    }
    
  
    function quitarTildes($cadena) {
        //$cadena = utf8_decode($cadena);
        $no_permitidas= array ("á","é","í","ó","ú","Á","É","Í","Ó","Ú","ñ","À","Ã","Ì","Ò","Ù","Ã™","Ã ","Ã¨","Ã¬","Ã²","Ã¹","ç","Ç","Ã¢","ê","Ã®","Ã´","Ã»","Ã‚","ÃŠ","ÃŽ","Ã”","Ã›","ü","Ã¶","Ã–","Ã¯","Ã¤","«","Ò","Ã","Ã„","Ã‹");
        $permitidas= array ("a","e","i","o","u","A","E","I","O","U","n","N","A","E","I","O","U","a","e","i","o","u","c","C","a","e","i","o","u","A","E","I","O","U","u","o","O","i","a","e","U","I","A","E");
        $texto = str_replace($no_permitidas, $permitidas ,$cadena);
        return $texto;
    }
    
    public function actualizarPerfil($usuariosIds, $perfilId)
    {
        $resultado = new Resultado();
        if($usuariosIds!="" && $usuariosIds!=null)
        {
            $consulta = " UPDATE usuarios " .
                "SET perfil_id = ? " .
                "WHERE id IN($usuariosIds)";
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param("i",$perfilId))
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
        }
        else
            $resultado->mensajeError = "No se selecciono ningun usuario";
            
            return $resultado;
    }
    
    function ajustarCampos($modelo)
    {
        $modelo->nombre = trim(mb_convert_case($modelo->nombre, MB_CASE_TITLE, "UTF-8"));
        $modelo->apellido = trim(mb_convert_case($modelo->apellido, MB_CASE_TITLE, "UTF-8"));
        $modelo->nombreUsuario = trim(strtolower($modelo->nombreUsuario));
        $modelo->nombre  = str_replace("  ", " ",  $modelo->nombre );
        $modelo->apellido  = str_replace("  ", " ",  $modelo->apellido );
    }
    
    public function ajustarCamposUsuarios($usuario)
    {
        $resultado = $this->consultar($usuario, (object)[], false);
        if($resultado->correcto())
        {
            $registros = $resultado->valor;
            for($i = 0; $i < count($registros); $i++)
            {
                $usuario = $registros[$i];
                
                //if($usuario->id == 1439 )
                //{
                $this->ajustarCampos($usuario);
                echo "\n".$usuario->id . " " . $usuario->nombre . " " . $usuario->apellido . " " . $usuario->nombreUsuario;
                
                $resultado = $this->actualizarNombreApellido($usuario);  
                if($resultado->correcto())
                    echo " OK";
                else 
                    echo " ". $resultado->mensajeError;
                //}
                
            }
        }
    }
    
    public function reemplazarUsuario($usuario, $origenUsuarioId, $destinoUsuarioId)
    {
        $resultado = new Resultado();
        
        if($origenUsuarioId!="" && $origenUsuarioId!=null && $destinoUsuarioId!="" && $destinoUsuarioId!=null)
        {
        
            $copia = (object)["origenUsuarioId"=>$origenUsuarioId,"destinoUsuarioId"=>$destinoUsuarioId];
            
            $this->conexion->autocommit(FALSE);
            
            $repositorio = new UsuariosProcedimientosRepositorio($this->conexion);
            $resultado = $repositorio->consultar((object)["usuarioId"=> $origenUsuarioId, "estatus" => "1"]);
            
            if($resultado->correcto())
            {
                $procedimientos = $resultado->valor;   
                $copia->procedimientos = $procedimientos;
                //$procedimientosRepositorio = new ProcedimientosRepositorio($this->conexion);
                $resultado = $repositorio->copiarProcedimientosUsuario($destinoUsuarioId, $procedimientos);
                
            }
    
            if($resultado->correcto())
            {
                $repositorio = new UsuariosProcesosRepositorio($this->conexion);
                $resultado = $repositorio->consultar((object)["usuarioId"=> $origenUsuarioId, "estatus" => "1"]);
                if($resultado->correcto())
                {
                    $procesos = $resultado->valor;
                    $copia->procesos = $procesos;
                    $resultado = $repositorio->copiarProcesosUsuario($destinoUsuarioId, $procesos);
                }
            }
            
            if($resultado->correcto())
            {
                $repositorio = new MinutasRepositorio($this->conexion);
                $resultado = $repositorio->remplazarUsuarioTareas($origenUsuarioId,$destinoUsuarioId);
                if($resultado->correcto())
                {
                    $tareas = $resultado->valor;
                    $copia->tareas = $tareas;
                }   
            }
            
            if($resultado->correcto())
            {
                $repositorio = new AuditoriasRepositorio($this->conexion);
                $resultado = $repositorio->reemplazarUsuarioHallazgos($origenUsuarioId, $destinoUsuarioId);
                if($resultado->correcto())
                {
                    $hallazgos = $resultado->valor;
                    $copia->hallazgos = $hallazgos;
                }  
            }
            
            if($resultado->correcto())
            {
                $resultado = $this->desactivarUsuario($origenUsuarioId);
            }
            
            if($resultado->correcto())
            {
                $resultado->valor = $copia;
                $this->conexion->commit();
            }
            else
                $this->conexion->rollback();
        }
        
        return $resultado;
    }
    
    
    public function consultarCoordinador($empresaId)
    {
        $resultado = new Resultado();
        $registros = array();
        
       
        $consulta =  $this->consultaBase .
                      " WHERE U.tipo_usuario_id = 2 
                            AND E.id = ? 
                     ORDER BY U.nombre, U.apellido
                     LIMIT 1";
        
        //var_dump($consulta);
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            //if($sentencia->bind_param("ss",$criteriosSeleccion->nombre,$criteriosSeleccion->apellido))
            if($sentencia->bind_param("i",$empresaId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre, $permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales, $servicio,$reemplazaUsuarioId))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7,$departamentoId, $departamentoNombre,$permisoCAVI, $perfilId, $perfilNombre, $recursosHumanos, $numeroEmpleado, $empresaEstatus, $verificador, $urlDocumentos,$visualizarAuditoriasSociosComerciales,$servicio,$reemplazaUsuarioId);
                            $resultado->valor = $registro;
                        }
                        $sentencia->close();
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
}
?>