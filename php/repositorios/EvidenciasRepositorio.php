<?php
namespace php\repositorios;

use php\interfaces\IEvidenciasRepositorio;
use php\modelos\Evidencia;
use php\modelos\Resultado;

include '../interfaces/IEvidenciasRepositorio.php';
include '../modelos/Evidencia.php';
require_once('RepositorioBase.php');
require_once("../clases/TipoUsuario.php");
require_once('../clases/Resultado.php');

class EvidenciasRepositorio extends RepositorioBase implements IEvidenciasRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = "SELECT E.id, usuario_procedimiento_id, realizo_actividad, justificacion_id, comentarios, IFNULL(DATE_FORMAT(E.fecha_alta,'%d/%m/%Y %H:%i:%s'),'') as fecha, P.nombre, nombre_archivo, P.codigo, J.nombre,
                                (SELECT count(C.id) FROM evidencias_comentarios C WHERE C.evidencia_id = E.id) numeroComentarios, U.nombre, U.apellido, S.id, S.nombre, EM.id, EM.nombre, U.id, validada, comentarios_validacion, EM.administrador_id, V.nombre administradorNombre, V.apellido administradorApellido   
                               FROM evidencias E
                            		INNER JOIN  usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
                                    INNER JOIN usuarios U ON U.id = UP.usuario_id
                                    LEFT JOIN sedes S ON S.id = U.sede_id
                                    LEFT JOIN empresas EM ON EM.id = S.empresa_id
                            		INNER JOIN procedimientos P ON P.id = UP.procedimiento_id
                                    LEFT JOIN justificaciones J ON J.id = E.justificacion_id
                                    LEFT JOIN usuarios V ON V.id = EM.administrador_id
                                ";
    }

    public function insertar(Evidencia $modelo,$nombreArchivoSubido)
    {
        if($modelo->justificacionId=="")
            $modelo->justificacionId=null;
        $resultado = $this->calcularId('id','evidencias');
        if($resultado->mensajeError=='')
        {
            $id = $resultado->valor;
            $consulta = "INSERT INTO evidencias(id, usuario_procedimiento_id, realizo_actividad, justificacion_id, comentarios, fecha_alta, fecha_modificacion, nombre_archivo)VALUES(?, ?, ?, ?, ?, NOW(),NOW(),?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('ssiiss', $id, $modelo->usuarioProcedimientoId, $modelo->realizoActividad, $modelo->justificacionId, $modelo->comentarios,$nombreArchivoSubido))
                {
                    if(!$sentencia->execute())
                        $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
                }
                else
                    $resultado->mensajeError = 'Falló el enlace de parámetros';
            }
            else
                $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' .$this->conexion->error;
        }
        return $resultado;
    }
    
    public function numeroEvidenciasJustificadasAnoActual($usuarioProcedimientoId)
    {
        $resultado = new Resultado();
        $resultado->valor = false;
        
        $consulta = "SELECT count(id) id
                        FROM evidencias E 
                        WHERE justificacion_id IS NOT NULL AND usuario_procedimiento_id = ? 
                            AND YEAR(E.fecha_alta) = YEAR(NOW())";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$usuarioProcedimientoId))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($count))
                    {
                        if($sentencia->fetch())
                        {
                            $resultado->valor =$count;
                        }
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
    public function numeroEvidenciasJustificadasMesActual($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $resultado->valor = 0;
        $filtros = $this->getFiltrosUsuario($usuario, $criteriosSeleccion,false);
        
        
        $consulta = "SELECT count(*) numero
                    FROM evidencias E
                        INNER JOIN usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
                        INNER JOIN usuarios U ON U.id = UP.usuario_id
                    WHERE (U.tipo_usuario_id=4 OR U.tipo_usuario_id=5) AND MONTH(E.fecha_alta) = $criteriosSeleccion->mes AND YEAR(E.fecha_alta) = $criteriosSeleccion->ano AND justificacion_id IS NOT NULL 
                    ";
        $consulta.=  $this->and($filtros);
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($count))
                    {
                        if($sentencia->fetch())
                        {
                            $resultado->valor =$count;
                        }
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        return $resultado;
    }
    
    public function numeroEvidenciasEnviadasMesActual($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $resultado->valor = 0;
        $filtros = $this->getFiltrosUsuario($usuario, $criteriosSeleccion,false);
        $consulta = "SELECT count(*) numero
                    FROM evidencias E
                        INNER JOIN usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
                        INNER JOIN usuarios U ON U.id = UP.usuario_id
                    WHERE (U.tipo_usuario_id=4 OR U.tipo_usuario_id=5)  AND MONTH(E.fecha_alta) = MONTH(NOW()) AND YEAR(E.fecha_alta) = YEAR(NOW()) AND justificacion_id IS NULL
                    ";
        $consulta.=  $this->and($filtros);
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($count))
                    {
                        if($sentencia->fetch())
                        {
                            $resultado->valor =$count;
                        }
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
    public function numeroEvidenciasPendientesMesActual($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $resultado->valor = 0;
        $filtros = array();
        $filtros = $this->getFiltrosUsuario($usuario, $criteriosSeleccion,false);
        $consulta = "SELECT count(*)
                    FROM usuarios_procedimientos UP
                    	INNER JOIN procedimientos P ON P.id = UP.procedimiento_id
                        INNER JOIN usuarios U ON U.id = UP.usuario_id
                    WHERE UP.estatus = 1 AND (U.tipo_usuario_id=4 OR U.tipo_usuario_id=5)  
                    	AND UP.id NOT IN(SELECT usuario_procedimiento_id FROM evidencias E WHERE MONTH(E.fecha_alta) = MONTH(NOW()) AND YEAR(E.fecha_alta) = YEAR(NOW()) ) ";
      
        $consulta.=  $this->and($filtros);
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($count))
                    {
                        if($sentencia->fetch())
                        {
                            $resultado->valor =$count;
                        }
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
    
    public function consultarEvidenciasCumplidas($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        
        $registros = array();
        
        $filtros = $this->getFiltrosUsuario($usuario, $criteriosSeleccion,false);
        
        $and = $this->and($filtros);
        
        //UP.usuario_id = ? AND 
        
        $consulta =  $this->consultaBase .
        " WHERE MONTH(E.fecha_alta) = $criteriosSeleccion->mes AND YEAR(E.fecha_alta) = $criteriosSeleccion->ano " . $and ." " .
        "ORDER BY codigo";

        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId,$validada, $comentariosValidacion, $administradorId, $administradorNombre, $administradorApellido))
                    {
                        while($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId, $validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido);
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
    public function consultarEvidencias($criteriosSeleccion)
    {
        $resultado = new Resultado();
        
        $registros = array();
        
        $filtros = array();
        
        
        $and="";
        if($criteriosSeleccion!=null)
        {
            if(isset($criteriosSeleccion->administradorId))
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'EM', 'campo'=>'administrador_id','valor'=>$criteriosSeleccion->administradorId]);
            if(isset($criteriosSeleccion->validada))
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'E', 'campo'=>'validada','valor'=> $criteriosSeleccion->validada]);
            if(isset($criteriosSeleccion->usuarioId))
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'E', 'campo'=>'usuario_id','valor'=> $criteriosSeleccion->usuarioId]);
        }
        
     
      
        
        $and = $this->and($filtros);
        
        $consulta =  $this->consultaBase .
        " WHERE  MONTH(E.fecha_alta) = $criteriosSeleccion->mes AND YEAR(E.fecha_alta) = $criteriosSeleccion->ano " . $and ." " .
        "ORDER BY codigo";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId,$validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido))
                    {
                        while($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId, $validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido);
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
//     public function consultarPorcentajesEvidencias($usuario,$criteriosSeleccion)
//     {
//         $resultado = new Resultado();
        
//         $justificadas = $this->numeroEvidenciasJustificadasMesActual($usuario,$criteriosSeleccion);
//         $enviadas =  $this->numeroEvidenciasEnviadasMesActual($usuario,$criteriosSeleccion);
//         $pendientes =  $this->numeroEvidenciasPendientesMesActual($usuario,$criteriosSeleccion);
        
//         $porcentajes = array();
//         array_push($porcentajes,(object)['nombre'=>'Enviadas','valor'=>$enviadas->valor]);
//         array_push($porcentajes,(object)['nombre'=>'Pendientes','valor'=>$pendientes->valor]);
//         array_push($porcentajes,(object)['nombre'=>'Justificadas','valor'=>$justificadas->valor]);
      
       
//         $resultado->valor = $porcentajes;

//         return $resultado;
//     }
    
    public function consultarPorcentajesEvidencias($usuario,$criteriosSeleccion)
    {
        
      
        
        $resultado = new Resultado();
        
        $justificadas = $this->numeroEvidenciasJustificadas($usuario,$criteriosSeleccion);
        $enviadas =  $this->numeroEvidenciasEnviadas($usuario,$criteriosSeleccion);
        $pendientes =  $this->numeroEvidenciasPendientes($usuario,$criteriosSeleccion);
        
        $porcentajes = array();
        array_push($porcentajes,(object)['nombre'=>'Enviadas','valor'=>$enviadas->valor]);
        array_push($porcentajes,(object)['nombre'=>'Pendientes','valor'=>$pendientes->valor]);
        array_push($porcentajes,(object)['nombre'=>'Justificadas','valor'=>$justificadas->valor]);
        
        
        $resultado->valor = $porcentajes;
        
        return $resultado;
    }
    
    
    public function consultarPorcentajesEmpresas($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $filtros =  array();
        
        $consulta = "SELECT EM.id,EM.nombre , EM.nombre_corto,
                    (
                    	SELECT count(*) numero
                    	FROM evidencias E1
                    		INNER JOIN usuarios_procedimientos UP1 ON UP1.id = E1.usuario_procedimiento_id
                    		INNER JOIN usuarios U1 ON U1.id = UP1.usuario_id
                            LEFT JOIN sedes S1 ON S1.id = U1.sede_id
                    		LEFT JOIN empresas EM1 ON EM1.id = S1.empresa_id
                    	WHERE MONTH(E1.fecha_alta) = MONTH(E.fecha_alta)  AND YEAR(E1.fecha_alta) = YEAR(E.fecha_alta) AND justificacion_id IS NOT NULL AND EM1.id = EM.id
                    ) justificadas,
                    (
                    	SELECT count(*) numero
                    	FROM evidencias E1
                    		INNER JOIN usuarios_procedimientos UP1 ON UP1.id = E1.usuario_procedimiento_id
                    		INNER JOIN usuarios U1 ON U1.id = UP1.usuario_id
                            LEFT JOIN sedes S1 ON S1.id = U1.sede_id
                    		LEFT JOIN empresas EM1 ON EM1.id = S1.empresa_id
                    	WHERE MONTH(E1.fecha_alta) = MONTH(E.fecha_alta)  AND YEAR(E1.fecha_alta) = YEAR(E.fecha_alta) AND justificacion_id IS NULL AND EM1.id = EM.id
                    ) enviadas,
                    (
                    	SELECT count(*)
                    	FROM usuarios_procedimientos UP1
                    		INNER JOIN procedimientos P1 ON P1.id = UP1.procedimiento_id
                    		INNER JOIN usuarios U1 ON U1.id = UP1.usuario_id
                    		LEFT JOIN sedes S1 ON S1.id = U1.sede_id
                    		LEFT JOIN empresas EM1 ON EM1.id = S1.empresa_id
                    	WHERE UP1.estatus = 1 AND EM1.id = EM.id
                    		AND UP1.id NOT IN(
                    				SELECT usuario_procedimiento_id 
                                    FROM evidencias E2 
                    					INNER JOIN usuarios_procedimientos UP2 ON UP2.id = E2.usuario_procedimiento_id
                    					INNER JOIN usuarios U2 ON U2.id = UP2.usuario_id
                    					LEFT JOIN sedes S2 ON S2.id = U2.sede_id
                    					LEFT JOIN empresas EM2 ON EM2.id = S2.empresa_id
                                    WHERE MONTH(E2.fecha_alta) = MONTH(E.fecha_alta)  AND YEAR(E2.fecha_alta) = YEAR(E.fecha_alta) AND EM2.id = EM.id
                                    ) 
                    		
                    )pendientes
                    FROM evidencias E
                    	INNER JOIN  usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
                    	INNER JOIN usuarios U ON U.id = UP.usuario_id
                    	LEFT JOIN sedes S ON S.id = U.sede_id
                    	LEFT JOIN empresas EM ON EM.id = S.empresa_id
                    WHERE MONTH(E.fecha_alta) = $criteriosSeleccion->mes AND YEAR(E.fecha_alta) = $criteriosSeleccion->ano
                    GROUP BY EM.id, EM.nombre";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre,$nombreCorto, $justificadas, $enviadas, $pendientes))
                    {
                        while($sentencia->fetch())
                        {
                            $registro= (object) [
                                'id' =>  $id,
                                'nombre' =>  $nombre,
                                'nombreCorto' =>  $nombreCorto,
                                'justificadas' =>  $justificadas,
                                'enviadas' =>  $enviadas,
                                'pendientes' =>  $pendientes
                            ];
                            
                            $total = $registro->justificadas + $registro->enviadas + $registro->pendientes;
                            $cumplimieto = $registro->justificadas + $registro->enviadas;

                            $registro->cumplidas =$registro->justificadas + $registro->enviadas;
                            $registro->porcentajeCumplimiento = $cumplimieto * 100 / $total;
                            
                            
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        return $resultado;
    }
    
    public function consultarPorcentajesAreas($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $filtros =  array();
        
        $consulta = "SELECT U.area_id,A.nombre,
                    (
                    	SELECT count(*) numero
                    	FROM evidencias E1
                    		INNER JOIN usuarios_procedimientos UP1 ON UP1.id = E1.usuario_procedimiento_id
                    		INNER JOIN usuarios U1 ON U1.id = UP1.usuario_id
                    		INNER JOIN sedes S1 ON S1.id = U1.sede_id
                    		INNER JOIN empresas EM1 ON EM1.id = S1.empresa_id
                    		INNER JOIN areas A1 ON A1.id = U1.area_id
                    	WHERE MONTH(E1.fecha_alta) = $criteriosSeleccion->mes  AND YEAR(E1.fecha_alta) = $criteriosSeleccion->ano AND justificacion_id IS NOT NULL AND EM1.id = EM.id AND A1.id = A.id
                    ) justificadas,
                    (
                    	SELECT count(*) numero
                    	FROM evidencias E1
                    		INNER JOIN usuarios_procedimientos UP1 ON UP1.id = E1.usuario_procedimiento_id
                    		INNER JOIN usuarios U1 ON U1.id = UP1.usuario_id
                    		INNER JOIN sedes S1 ON S1.id = U1.sede_id
                    		INNER JOIN empresas EM1 ON EM1.id = S1.empresa_id
                    		INNER JOIN areas A1 ON A1.id = U1.area_id
                    	WHERE MONTH(E1.fecha_alta) = $criteriosSeleccion->mes  AND YEAR(E1.fecha_alta) = $criteriosSeleccion->mes AND justificacion_id IS NULL AND EM1.id = EM.id AND A1.id = A.id
                    ) enviadas,
                    (
                    	SELECT count(*)
                    	FROM usuarios_procedimientos UP1
                    		INNER JOIN procedimientos P1 ON P1.id = UP1.procedimiento_id
                    		INNER JOIN usuarios U1 ON U1.id = UP1.usuario_id
                    		INNER JOIN sedes S1 ON S1.id = U1.sede_id
                    		INNER JOIN empresas EM1 ON EM1.id = S1.empresa_id
                            INNER JOIN areas A1 ON A1.id = U1.area_id
                    	WHERE UP1.estatus = 1 AND EM1.id = EM.id AND A1.id = A.id
                    		AND UP1.id NOT IN(
                    				SELECT usuario_procedimiento_id
                    				FROM evidencias E2
                    					INNER JOIN usuarios_procedimientos UP2 ON UP2.id = E2.usuario_procedimiento_id
                    					INNER JOIN usuarios U2 ON U2.id = UP2.usuario_id
                    					INNER JOIN sedes S2 ON S2.id = U2.sede_id
                    					INNER JOIN empresas EM2 ON EM2.id = S2.empresa_id
                                        INNER JOIN areas A2 ON A2.id = U2.area_id
                    				WHERE MONTH(E2.fecha_alta) =$criteriosSeleccion->mes  AND YEAR(E2.fecha_alta) =$criteriosSeleccion->ano AND EM2.id = EM1.id AND A2.id = A1.id
                    				)
                    
                    )pendientes
                    FROM usuarios_procedimientos UP 
                    	LEFT JOIN usuarios U ON U.id = UP.usuario_id
                    	LEFT JOIN sedes S ON S.id = U.sede_id
                    	LEFT JOIN empresas EM ON EM.id = S.empresa_id
                        LEFT JOIN areas A ON A.id = U.area_id
                    WHERE  EM.id = ?
                    GROUP BY U.area_id, A.nombre";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('i',$usuario->empresaId))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre,$justificadas, $enviadas, $pendientes))
                    {
                        while($sentencia->fetch())
                        {
                            $registro= (object) [
                                'id' =>  $id,
                                'nombre' =>  $nombre,
                                'justificadas' =>  $justificadas,
                                'enviadas' =>  $enviadas,
                                'pendientes' =>  $pendientes
                            ];
                            
                            $total = $registro->justificadas + $registro->enviadas + $registro->pendientes;
                            $cumplimieto = $registro->justificadas + $registro->enviadas;
                            
                            $registro->cumplidas =$registro->justificadas + $registro->enviadas;
                            $registro->porcentajeCumplimiento = $cumplimieto * 100 / $total;
                            
                            
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
    public function consultarPorcentajesUsuarios($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $filtros =  array();
        
        $consulta = "SELECT U.id,U.nombre, U.apellido,U.tipo_usuario_id, U.empresa_id, U.sede_id, U.area_id,
                    (
                    	SELECT count(*) numero
                    	FROM evidencias E1
                    		INNER JOIN usuarios_procedimientos UP1 ON UP1.id = E1.usuario_procedimiento_id
                    		INNER JOIN usuarios U1 ON U1.id = UP1.usuario_id
                    		INNER JOIN sedes S1 ON S1.id = U1.sede_id
                    		INNER JOIN empresas EM1 ON EM1.id = S1.empresa_id
                    		INNER JOIN areas A1 ON A1.id = U1.area_id
                    	WHERE MONTH(E1.fecha_alta) = $criteriosSeleccion->mes  AND YEAR(E1.fecha_alta) = $criteriosSeleccion->ano AND justificacion_id IS NOT NULL AND EM1.id = EM.id AND A1.id = A.id AND U1.id = U.id
                    ) justificadas,
                    (
                    	SELECT count(*) numero
                    	FROM evidencias E1
                    		INNER JOIN usuarios_procedimientos UP1 ON UP1.id = E1.usuario_procedimiento_id
                    		INNER JOIN usuarios U1 ON U1.id = UP1.usuario_id
                    		INNER JOIN sedes S1 ON S1.id = U1.sede_id
                    		INNER JOIN empresas EM1 ON EM1.id = S1.empresa_id
                    		INNER JOIN areas A1 ON A1.id = U1.area_id
                    	WHERE MONTH(E1.fecha_alta) = $criteriosSeleccion->mes AND YEAR(E1.fecha_alta) = $criteriosSeleccion->ano AND justificacion_id IS NULL AND EM1.id = EM.id AND A1.id = A.id AND U1.id = U.id
                    ) enviadas,
                    (
                    	SELECT count(*)
                    	FROM usuarios_procedimientos UP1
                    		INNER JOIN procedimientos P1 ON P1.id = UP1.procedimiento_id
                    		INNER JOIN usuarios U1 ON U1.id = UP1.usuario_id
                    		INNER JOIN sedes S1 ON S1.id = U1.sede_id
                    		INNER JOIN empresas EM1 ON EM1.id = S1.empresa_id
                    		INNER JOIN areas A1 ON A1.id = U1.area_id
                    	WHERE UP1.estatus = 1 AND EM1.id = EM.id AND A1.id = A.id AND U1.id = U.id
                    		AND UP1.id NOT IN(
                    				SELECT usuario_procedimiento_id
                    				FROM evidencias E2
                    					INNER JOIN usuarios_procedimientos UP2 ON UP2.id = E2.usuario_procedimiento_id
                    					INNER JOIN usuarios U2 ON U2.id = UP2.usuario_id
                    					INNER JOIN sedes S2 ON S2.id = U2.sede_id
                    					INNER JOIN empresas EM2 ON EM2.id = S2.empresa_id
                    					INNER JOIN areas A2 ON A2.id = U2.area_id
                    				WHERE MONTH(E2.fecha_alta) = $criteriosSeleccion->mes AND YEAR(E2.fecha_alta) = $criteriosSeleccion->ano AND EM2.id = EM1.id AND A2.id = A1.id AND U1.id = U.id
                    				)
                    
                    )pendientes
                    FROM usuarios_procedimientos UP
                    	LEFT JOIN usuarios U ON U.id = UP.usuario_id
                    	LEFT JOIN sedes S ON S.id = U.sede_id
                    	LEFT JOIN empresas EM ON EM.id = S.empresa_id
                    	LEFT JOIN areas A ON A.id = U.area_id
                    WHERE  EM.id = ? AND A.id = ? AND (U.tipo_usuario_id=4 OR U.tipo_usuario_id=5)
                    GROUP BY U.id, U.nombre";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('ii',$usuario->empresaId,$usuario->areaId))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre, $apellido, $tipoUsuarioId, $empreasaId, $sedeId, $areaId, $justificadas, $enviadas, $pendientes))
                    {
                        while($sentencia->fetch())
                        {
                            $registro= (object) [
                                'id' =>  $id,
                                'nombre' =>  $nombre,
                                'apellido' =>  $apellido,
                                'tipoUsuarioId' =>  $tipoUsuarioId,
                                'empresaId' =>  $empreasaId,
                                'sedeId' =>  $sedeId,
                                'areaId' =>  $areaId,
                                'justificadas' =>  $justificadas,
                                'enviadas' =>  $enviadas,
                                'pendientes' =>  $pendientes
                            ];
                            
                            $total = $registro->justificadas + $registro->enviadas + $registro->pendientes;
                            $cumplimieto = $registro->justificadas + $registro->enviadas;
                            
                            $registro->cumplidas =$registro->justificadas + $registro->enviadas;
                            $registro->porcentajeCumplimiento = $cumplimieto * 100 / $total;
                            
                            
                            $registro->nombreCompleto = $registro->nombre . " " . $registro->apellido;
                            $registro->fotoPerfil =  "../fotos/usuario". $registro->id .".jpg";
                            if(file_exists($registro->fotoPerfil))
                                $registro->fotoPerfil =  "php/fotos/usuario". $registro->id .".jpg";
                            else
                                $registro->fotoPerfil =  "php/fotos/default.jpg";
                            
                            
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
    public function consultarAnosMeses($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $filtros = $this->getFiltrosUsuario($usuario, $criteriosSeleccion,false);
        
        $consulta = "SELECT YEAR(E.fecha_alta) ano, MONTH(E.fecha_alta) mes
                    FROM evidencias E
                        INNER JOIN  usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
                        INNER JOIN usuarios U ON U.id = UP.usuario_id
                        LEFT JOIN sedes S ON S.id = U.sede_id
                        LEFT JOIN empresas EM ON EM.id = S.empresa_id ";

        $consulta .= $this->where($filtros);
        
        $consulta.=" GROUP BY ano, mes
                    ORDER BY ano, mes";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($ano, $mes))
                    {
                        while($sentencia->fetch())
                        {
                            $mesNombre = $this->getNombreMes($mes);
                            $registro= (object) [
                                'ano' =>  $ano,
                                'mes' =>  $mes,
                                'mesNombre' =>  $mesNombre
                            ];
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
    public function consultarAnos($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $filtros = $this->getFiltrosUsuario($usuario, $criteriosSeleccion,false);
        
        $consulta = "SELECT YEAR(E.fecha_alta) ano
                    FROM evidencias E
                        INNER JOIN  usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
                        INNER JOIN usuarios U ON U.id = UP.usuario_id
                        LEFT JOIN sedes S ON S.id = U.sede_id
                        LEFT JOIN empresas EM ON EM.id = S.empresa_id ";
        
        $consulta .= $this->where($filtros);
        
        $consulta.=" GROUP BY ano
                    ORDER BY ano";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
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
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
    private function getNombreMes($mes)
    {
        $nombreMes="";
        switch ($mes)
        {
            case 1: $nombreMes = "Enero"; break;
            case 2: $nombreMes = "Febrero"; break;
            case 3: $nombreMes = "Marzo"; break;
            case 4: $nombreMes = "Abril"; break;
            case 5: $nombreMes = "Mayo"; break;
            case 6: $nombreMes = "Junio"; break;
            case 7: $nombreMes = "Julio"; break;
            case 8: $nombreMes = "Agosto"; break;
            case 9: $nombreMes = "Septiembre"; break;
            case 10: $nombreMes = "Octubre"; break;
            case 11: $nombreMes = "Noviembre"; break;
            case 12: $nombreMes = "Diciembre"; break;
         }
         return $nombreMes;
    }
    
    public function consultarUsuariosConProcedimientosAsignados($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $filtros =  array();
        
        $consulta = "SELECT U.id,U.nombre, U.apellido, U.tipo_usuario_id
                    FROM usuarios_procedimientos UP
                    	LEFT JOIN usuarios U ON U.id = UP.usuario_id
                    	LEFT JOIN sedes S ON S.id = U.sede_id
                    	LEFT JOIN empresas EM ON EM.id = S.empresa_id
                    	LEFT JOIN areas A ON A.id = U.area_id
                    WHERE  EM.id = ? AND A.id = ? AND (U.tipo_usuario_id=4 OR U.tipo_usuario_id=5)
                    GROUP BY U.id, U.nombre";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('ii',$usuario->empresaId,$usuario->areaId))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre, $apellido, $tipoUsuarioId))
                    {
                        while($sentencia->fetch())
                        {
                            $registro= (object) [
                                'id' =>  $id,
                                'nombre' =>  $nombre,
                                'apellido' =>  $apellido,
                                'tipoUsuarioId' => $tipoUsuarioId
                            ];
                            
                            
                            $registro->nombreCompleto = $registro->nombre . " " . $registro->apellido;
                            $registro->fotoPerfil =  "../fotos/usuario". $registro->id .".jpg";
                            if(file_exists($registro->fotoPerfil))
                                $registro->fotoPerfil =  "php/fotos/usuario". $registro->id .".jpg";
                            else
                                $registro->fotoPerfil =  "php/fotos/default.jpg";
                                
                                    
                                    array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
    public function consultarPorcentajesAdministradores($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $filtros =  array();
        
        $consulta = "SELECT V.id, V.nombre, V.apellido,
                 (
                	SELECT count(*) numero
                	FROM evidencias E1
                		INNER JOIN usuarios_procedimientos UP1 ON UP1.id = E1.usuario_procedimiento_id
                		INNER JOIN usuarios U1 ON U1.id = UP1.usuario_id
                		LEFT JOIN sedes S1 ON S1.id = U1.sede_id
                		LEFT JOIN empresas EM1 ON EM1.id = S1.empresa_id
                		LEFT JOIN usuarios V1 ON V1.id = EM1.administrador_id
                	WHERE MONTH(E1.fecha_alta) = MONTH(E.fecha_alta)  AND YEAR(E1.fecha_alta) = YEAR(E.fecha_alta) AND E1.validada=1 AND V1.id = V.id
                ) validadas,
                (		SELECT count(*) numero
                	FROM evidencias E1
                		INNER JOIN usuarios_procedimientos UP1 ON UP1.id = E1.usuario_procedimiento_id
                		INNER JOIN usuarios U1 ON U1.id = UP1.usuario_id
                		LEFT JOIN sedes S1 ON S1.id = U1.sede_id
                		LEFT JOIN empresas EM1 ON EM1.id = S1.empresa_id
                		LEFT JOIN usuarios V1 ON V1.id = EM1.administrador_id
                	WHERE MONTH(E1.fecha_alta) = MONTH(E.fecha_alta)  AND YEAR(E1.fecha_alta) = YEAR(E.fecha_alta) AND E1.validada=0 AND V1.id = V.id
                ) noValidadas 
                FROM evidencias E
                	INNER JOIN  usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
                	INNER JOIN usuarios U ON U.id = UP.usuario_id
                	LEFT JOIN sedes S ON S.id = U.sede_id
                	LEFT JOIN empresas EM ON EM.id = S.empresa_id
                	INNER JOIN procedimientos P ON P.id = UP.procedimiento_id
                	LEFT JOIN justificaciones J ON J.id = E.justificacion_id
                	LEFT JOIN usuarios V ON V.id = EM.administrador_id
                WHERE MONTH(E.fecha_alta) =$criteriosSeleccion->mes AND YEAR(E.fecha_alta) = $criteriosSeleccion->ano AND V.id  IS NOT NULL 
                GROUP BY V.id, V.nombre, V.apellido";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre,$apellido, $validadas, $noValidadas))
                    {
                        while($sentencia->fetch())
                        {
                            $registro= (object) [
                                'id' =>  $id,
                                'nombre' =>  $nombre,
                                'apellido' =>  $apellido,
                                'validadas' =>  $validadas,
                                'noValidadas' =>  $noValidadas
                            ];
                            
                            $registro->total = $registro->validadas + $registro->noValidadas;
                            $cumplimieto =$registro->validadas;
                            
                            if($registro->total!=0)
                                $registro->porcentajeCumplimiento = $cumplimieto * 100 / $registro->total;
                            else
                                $registro->porcentajeCumplimiento = 0;
                            
                            $registro->nombreCompleto = $registro->nombre . " " . $registro->apellido;
                            $registro->fotoPerfil =  "../fotos/usuario". $registro->id .".jpg";
                            if(file_exists($registro->fotoPerfil))
                                $registro->fotoPerfil =  "php/fotos/usuario". $registro->id .".jpg";
                            else
                                $registro->fotoPerfil =  "php/fotos/default.jpg";
                                    
                            
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
    private function getFiltrosUsuario($usuario,$criteriosSeleccion,$agregarCriteriosSeleccion)
    {
        $filtros = array();
        switch ($usuario->tipoUsuarioId)
        {
            case \TipoUsuario::USUARIO:
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'UP', 'campo'=>'usuario_id','valor'=>$usuario->id]);
            break;
            case \TipoUsuario::SUPERVISOR:
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'empresa_id','valor'=>$usuario->empresaId]);
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'sede_id','valor'=>$usuario->sedeId]);
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'area_id','valor'=>$usuario->areaId]);
            break;
            case \TipoUsuario::COORDINADOR:
                array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'empresa_id','valor'=>$usuario->empresaId]);
            break;
        }
        if($criteriosSeleccion!=null)
        {
            if(isset($criteriosSeleccion->ano))
                array_push($filtros,(object)['tipoDato'=>'int','campo'=>'YEAR(E.fecha_alta)','valor'=>$criteriosSeleccion->ano]);
            if(isset($criteriosSeleccion->mes))
                array_push($filtros,(object)['tipoDato'=>'int','campo'=>'MONTH(E.fecha_alta)','valor'=>$criteriosSeleccion->mes]);
        }
        
        return $filtros;
    }
    
    public function consultarEvidenciasJustificacion($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $filtros = $this->getFiltrosUsuario($usuario, $criteriosSeleccion,true);
        
        $and = $this->and($filtros);
        
        
        $consulta = "SELECT justificacion_id, J.nombre, count(justificacion_id) valor
            FROM evidencias E
            		INNER JOIN usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
            		INNER JOIN procedimientos P ON P.id = UP.procedimiento_id
                    INNER JOIN justificaciones J ON J.id = E.justificacion_id
                    INNER JOIN usuarios U ON U.id = UP.usuario_id
            WHERE MONTH(E.fecha_alta) = $criteriosSeleccion->mes AND YEAR(E.fecha_alta) = $criteriosSeleccion->ano ". $and . " ".
            "GROUP BY justificacion_id, J.nombre";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $nombre,$valor))
                    {
                        while($sentencia->fetch())
                        {
                            $procedimiento= (object) [
                                'id' =>  $id,
                                'nombre' =>  $nombre,
                                'valor' =>  $valor
                            ];
                            array_push($registros,$procedimiento);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
   
    
    

    public function actualizar(Evidencia $modelo,$nombreArchivoSubido)
    {
        if($modelo->justificacionId=="")
            $modelo->justificacionId=null;
        $resultado = new Resultado();
        $consulta = "UPDATE evidencias
                     SET 
                         realizo_actividad = ?,
                         justificacion_id = ?,
                         comentarios = ?,
                         fecha_modificacion = NOW(),
                         nombre_archivo = ?
                     WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('iisss',$modelo->realizoActividad, $modelo->justificacionId, $modelo->comentarios,$nombreArchivoSubido,$modelo->id))
            {
                if($sentencia->execute())
                {
                    $resultado->valor=true;
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        return $resultado;
    }
    
    public function validarEvidencia(Evidencia $modelo)
    {
        if($modelo->justificacionId=="")
            $modelo->justificacionId=null;
            $resultado = new Resultado();
            $consulta = "UPDATE evidencias
                     SET
                         validada = ?,
                         comentarios_validacion = ?,
                         fecha_modificacion = NOW()
                     WHERE id = ?";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('isi',$modelo->validada, $modelo->comentariosValidacion,$modelo->id))
                {
                    if($sentencia->execute())
                    {
                        $resultado->valor=$modelo->id;
                    }
                    else
                        $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
                }
                else
                    $resultado->mensajeError = 'Falló el enlace de parámetros';
            }
            else
                $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
                return $resultado;
    }

    public function consultar($criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        $filtros = array();
        $where='';
        if($criteriosSeleccion!=null)
        {
            $where = $this->where($filtros);
        }
        $consulta = $this->consultaBase . $where;
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId, $validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId, $validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido);
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        return $resultado;
    }

    public function consultarPorLlaves($llaves)
    {
        $resultado = new Resultado();
        $consulta = $this->consultaBase .
        ' WHERE E.id  = ?';
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('s',$llaves->id))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId, $validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId, $validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido);
                            $resultado->valor = $registro;
                        }
                        else
                            $resultado->mensajeError = __FUNCTION__. ' No se encontró ningún resultado.';
                    }
                    else
                        $resultado->mensajeError = __FUNCTION__. ' Falló el enlace del resultado';
                }
                else
                    $resultado->mensajeError = __FUNCTION__. ' Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = __FUNCTION__. ' Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = __FUNCTION__. ' Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        return $resultado;
    }

    public function eliminar($llaves)
    {
        $resultado = new Resultado();
        $consulta = "DELETE FROM evidencias WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('s',$llaves->id))
            {
                if($sentencia->execute())
                {
                    $resultado->valor = $llaves->id;
                }
                else
                {
                    $resultado->codigoError = $this->conexion->errno;
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
                }
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
        {
            $resultado->codigoError = $this->conexion->errno;
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
        }
        return $resultado;
    }

    private function crearRegistro($id, $usuarioProcedimientoId, $realizoActividad, $justificacionId, $comentarios, $fecha,$nombre,$nombreArchivo,$codigo,$justificacionNombre,$numeroComentarios,$usuarioNombre,$usuarioApellido,$sedeId,$sedeNombre,$empresaId,$empresaNombre,$usuarioId, $validada, $comentariosValidacion,$administradorId, $administradorNombre, $administradorApellido)
    {
        $registro= (object) 
        [
            'id' => $id,
            'usuarioProcedimientoId' => $usuarioProcedimientoId,
            'realizoActividad' => $realizoActividad,
            'justificacionId' => $justificacionId,
            'justificacionNombre' => $justificacionNombre,
            'comentarios' => $comentarios,
            'fecha' => $fecha,
            'nombre' => $nombre,
            'nombreArchivo' => $nombreArchivo,
            'codigo' => $codigo,
            'numeroComentarios' => $numeroComentarios,
            'usuarioNombre' => $usuarioNombre,
            'usuarioApellido' => $usuarioApellido,
            'sedeId' => $sedeId,
            'sedeNombre' => $sedeNombre,
            'empresaId' => $empresaId,
            'empresaNombre' => $empresaNombre,
            'usuarioId' => $usuarioId,
            'validada' => $validada,
            'comentariosValidacion' => $comentariosValidacion,
            'administradorId' => $administradorId,
            'administradorNombre' => $administradorNombre,
            'administradorApellido' => $administradorApellido
            
            
        ];
        
        $registro->usuarioNombreCompleto = $registro->usuarioNombre . " " . $registro->usuarioApellido;
        $registro->fotoPerfil =  "../fotos/usuario". $registro->usuarioId .".jpg";
        if(file_exists($registro->fotoPerfil))
            $registro->fotoPerfil =  "php/fotos/usuario". $registro->usuarioId .".jpg";
        else
            $registro->fotoPerfil =  "php/fotos/default.jpg";
        
            
        $registro->administradorNombreCompleto = $registro->administradorNombre . " " . $registro->administradorApellido;
        $registro->administradorFotoPerfil =  "../fotos/usuario". $registro->administradorId .".jpg";
        if(file_exists($registro->administradorFotoPerfil))
            $registro->administradorFotoPerfil =  "php/fotos/usuario". $registro->administradorId .".jpg";
        else
            $registro->administradorFotoPerfil =  "php/fotos/default.jpg";
        
        $registro->empresaLogo =  "../logos_empresas/logo". $registro->empresaId .".png";
        if(file_exists($registro->empresaLogo))
            $registro->empresaLogo =  "php/logos_empresas/logo". $registro->empresaId .".png";
        else
            $registro->empresaLogo =  "php/logos_empresas/default.png";
        
        
        return $registro;
    }
    
    public function numeroEvidenciasJustificadas($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $resultado->valor = 0;
        $filtros = $this->getFiltrosUsuario($usuario, $criteriosSeleccion,true);
        
       
        
        $consulta = "SELECT count(*) numero
                    FROM evidencias E
                        INNER JOIN usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
                        INNER JOIN usuarios U ON U.id = UP.usuario_id
                    WHERE justificacion_id IS NOT NULL
                    ";
        $consulta.=  $this->and($filtros);
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($count))
                    {
                        if($sentencia->fetch())
                        {
                            $resultado->valor =$count;
                        }
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
    public function numeroEvidenciasEnviadas($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $resultado->valor = 0;
        $filtros = $this->getFiltrosUsuario($usuario, $criteriosSeleccion,true);
        $consulta = "SELECT count(*) numero
                    FROM evidencias E
                        INNER JOIN usuarios_procedimientos UP ON UP.id = E.usuario_procedimiento_id
                        INNER JOIN usuarios U ON U.id = UP.usuario_id
                    WHERE justificacion_id IS NULL
                    ";
        $consulta.=  $this->and($filtros);
        
      
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($count))
                    {
                        if($sentencia->fetch())
                        {
                            $resultado->valor =$count;
                        }
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
    public function numeroEvidenciasPendientes($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $resultado->valor = 0;
        $filtros = array();
        $filtros = $this->getFiltrosUsuario($usuario, null,false);
        
        $consulta = "SELECT count(*)
                    FROM usuarios_procedimientos UP
                    	INNER JOIN procedimientos P ON P.id = UP.procedimiento_id
                        INNER JOIN usuarios U ON U.id = UP.usuario_id
                    WHERE UP.estatus = 1  AND (U.tipo_usuario_id=4 OR U.tipo_usuario_id=5)  
                    	AND UP.id NOT IN(SELECT usuario_procedimiento_id FROM evidencias E WHERE MONTH(E.fecha_alta) = $criteriosSeleccion->mes AND YEAR(E.fecha_alta) = $criteriosSeleccion->ano) ";
        
        $consulta.=  $this->and($filtros);
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($count))
                    {
                        if($sentencia->fetch())
                        {
                            $resultado->valor =$count;
                        }
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace del resultado.';
                }
                else
                    $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
            }
            else
                $resultado->mensajeError = 'Falló el enlace de parámetros';
        }
        else
            $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' . $this->conexion->error;
            return $resultado;
    }
    
}
