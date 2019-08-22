<?php
namespace php\repositorios;



use php\interfaces\IUsuariosRepositorio;
use php\modelos\Usuario;
use php\modelos\Resultado;


include "../interfaces/IUsuariosRepositorio.php";


require_once("RepositorioBase.php");
require_once("../clases/Resultado.php");

class UsuariosRepositorio extends RepositorioBase implements IUsuariosRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = "SELECT U.id, U.nombre_usuario, U.contrasena contrasena,U.nombre, U.apellido, E.id empresaId, IFNULL(E.nombre,'') empresa, S.id sedeId, IFNULL(S.nombre,'') sede, P.id puestoId, IFNULL(P.nombre,'') puesto, A.id areaId, IFNULL(A.nombre,'') area, T.id tipoUsuarioId, T.nombre tipo_usuario, SU1.id supervisor1Id, CONCAT(IFNULL(SU1.nombre,''),' ',IFNULL(SU1.apellido,'')) supervisor1,SU2.id supervisor2Id,CONCAT(IFNULL(SU2.nombre,''),' ',IFNULL(SU2.apellido,'')) supervisor2,SU3.id supervisor3Id, CONCAT(IFNULL(SU3.nombre,''),' ',IFNULL(SU3.apellido,'')) supervisor3, U.fecha_alta, U.fecha_modificacion,IFNULL((SELECT fecha FROM historial_acceso WHERE nombre_usuario= U.nombre_usuario ORDER BY id DESC LIMIT 1),'') ultimo_acceso, U.estatus, E.tipo_empresa_id, A.tipo_area_id, U.permiso_saha,U.permiso_sivah,U.permiso_10y7 " .
                             "FROM usuarios U " .
                             "  LEFT JOIN empresas E ON U.empresa_id=E.id ".
                             "  LEFT JOIN sedes S ON U.sede_id = S.id " .
                             "  LEFT JOIN puestos P ON U.puesto_id = P.id " .
                             "  LEFT JOIN areas A ON U.area_id = A.id " .
                             "  LEFT JOIN tipos_usuario T ON U.tipo_usuario_id = T.id " .
                             "  LEFT JOIN usuarios SU1 ON U.supervisor1_id = SU1.id " .
                             "  LEFT JOIN usuarios SU2 ON U.supervisor2_id = SU2.id " .
                             "  LEFT JOIN usuarios SU3 ON U.supervisor3_id = SU3.id ";
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
        
            
            
        if($resultado->mensajeError=="")
        {
            $id = $resultado->valor;
            
           
            
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
                        . " permiso_10y7) "
                        . " VALUE(?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW(),?,?,?,?) ";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if( $sentencia->bind_param("issssiiiiiiiiiiii",
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
                    $modelo->permiso10y7))
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
                    " permiso_10y7 = ? " .
                    "WHERE id = ?";    
                        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ssssiiiiiiiiiiiii",
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
    
    private function crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id, $supervisor2, $supervisor3Id, $supervisor3, $fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus, $tipoEmpresaId, $tipoAreaId, $permisoSAHA, $permisoSIVAH, $permiso10y7)
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
        ];
        
        $registro->nombreCompleto = $registro->nombre . " " . $registro->apellido;
        $registro->fotoPerfil =  "../fotos/usuario". $registro->id .".jpg";
        if(file_exists($registro->fotoPerfil))
            $registro->fotoPerfil =  "php/fotos/usuario". $registro->id .".jpg";
        else
             $registro->fotoPerfil =  "php/fotos/default.jpg";
        
        return $registro;
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
                array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'U','campo'=>'nombre','valor'=>$criteriosSeleccion->nombre]);
            if(isset($criteriosSeleccion->apellido))
                array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'U','campo'=>'apellido','valor'=>$criteriosSeleccion->apellido]);
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
            if(isset($criteriosSeleccion->tipoUsuarioId))
            {
                if($criteriosSeleccion->tipoUsuarioId!="" && $criteriosSeleccion->tipoUsuarioId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'U','campo'=>'tipo_usuario_id','valor'=>$criteriosSeleccion->tipoUsuarioId]);
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
                    if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7)  )
                    {                    
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7);
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
                    if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7)  )
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7);
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
        
        $consulta =   $this->consultaBase .
                      " WHERE U.empresa_id = ? " .
                      " AND U.id != ? "  .
                      "UNION " .
                      $this->consultaBase .
                      " WHERE U.empresa_id = (SELECT corporativo_id FROM empresas CORP WHERE CORP.id = ?) " .
                      " AND U.id != ? ";
 
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("iiii",$empresaId,$usuarioId,$empresaId,$usuarioId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7)  )
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7);
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
    
    public function consultarPorEmpresaSede($empresaId,$sedeId)
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
                    if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7)  )
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7);
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
        $registros = array();
        
        $consulta =   $this->consultaBase .
        " WHERE U.id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$llaves->id))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1, $supervisor2Id,$supervisor2, $supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7)  )
                    {
                        if($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7);
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
                    if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,  $supervisor2Id, $supervisor2, $supervisor3Id, $supervisor3, $fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7)  )
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $nombreUsuario,$contrasena, $nombre, $apellido,$empresaId, $empresa, $sedeId, $sede, $puestoId, $puesto, $areaId, $area, $tipoUsuarioId, $tipoUsuario, $supervisor1Id, $supervisor1,$supervisor2Id, $supervisor2,$supervisor3Id, $supervisor3,$fechaAlta, $fechaModificacion, $ultimoAcceso, $estatus,$tipoEmpresaId, $tipoAreaId,$permisoSAHA, $permisoSIVAH, $permiso10y7);
                            
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

    
}

