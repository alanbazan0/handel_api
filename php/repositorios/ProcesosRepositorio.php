<?php
namespace php\repositorios;

use php\interfaces\IProcesosRepositorio;
use php\modelos\Proceso;
use php\modelos\Resultado;

include '../interfaces/IProcesosRepositorio.php';
include '../modelos/Proceso.php';
require_once('RepositorioBase.php');
require_once('../clases/Resultado.php');

class ProcesosRepositorio extends RepositorioBase implements IProcesosRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = "SELECT P.id, RTRIM(codigo) as codigo, RTRIM(P.nombre) as nombre, RTRIM(P.descripcion) as descripcion, RTRIM(ruta_archivo) as ruta_archivo, P.empresa_id, E.nombre, P.sede_id,S.nombre,IFNULL(DATE_FORMAT(P.fecha_alta,'%d/%m/%Y %H:%i:%s'),'')fecha_alta, IFNULL(DATE_FORMAT(P.fecha_modificacion,'%d/%m/%Y %H:%i:%s'),'')fecha_modificacion, P.estatus, oea, ctpat, wrap, ipm 
                            FROM procesos P
                                LEFT JOIN empresas E ON E.id = P.empresa_id
                                LEFT JOIN sedes S ON S.id = P.sede_id";
    }

    public function insertar(Proceso $modelo)
    {
        $resultado = $this->calcularId('id','procesos');
        if($resultado->mensajeError=='')
        {
            $id = $resultado->valor;
            $consulta = "INSERT INTO procesos(id, codigo, nombre, descripcion, ruta_archivo, empresa_id, sede_id, fecha_alta, fecha_modificacion, estatus, oea, ctpat, wrap, ipm)VALUES(?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?,?,?,?,?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($sentencia->bind_param('issssiiiiiii', $id, $modelo->codigo, $modelo->nombre, $modelo->descripcion, $modelo->rutaArchivo, $modelo->empresaId, $modelo->sedeId, $modelo->estatus, $modelo->oea, $modelo->ctpat, $modelo->wrap, $modelo->ipm))
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
    
    public function copiarProcesos($empresaIdOrigen, $sedeIdOrigen, $procedimientos, $empresaIdDetino, $sedeIdDestino)
    {
        $resultado = new Resultado();
        
        $this->conexion->autocommit(FALSE);
        
        for($i = 0; $i < count($procedimientos); $i++)
        {
            $procedimiento = $procedimientos[$i];
            $resultado = $this->calcularId('id','procesos');
            if($resultado->mensajeError=='')
            {
                $id = $resultado->valor;
                $consulta = "INSERT INTO procesos(id, codigo, nombre, descripcion, ruta_archivo, empresa_id, sede_id, fecha_alta, fecha_modificacion, estatus)VALUES(?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), 1)";
                if($sentencia = $this->conexion->prepare($consulta))
                {
                    if($sentencia->bind_param('issssii', $id, $procedimiento->codigo, $procedimiento->nombre, $procedimiento->descripcion, $procedimiento->rutaArchivo, $empresaIdDetino, $sedeIdDestino))
                    {
                        if(!$sentencia->execute())
                        {
                            $resultado->mensajeError = 'Falló la ejecución (' . $this->conexion->errno . ') ' . $this->conexion->error;
                            break;   
                        }
                    }
                    else
                    {
                        $resultado->mensajeError = 'Falló el enlace de parámetros';
                        break;
                    }
                        
                }
                else
                {
                    $resultado->mensajeError = 'Falló la preparación: (' . $this->conexion->errno . ') ' .$this->conexion->error;
                    break;
                }
                
            }
        }
        
        if($resultado->correcto())
            $this->conexion->commit();
        
        return $resultado;
    }

    public function actualizar(Proceso $modelo)
    {
        $resultado = new Resultado();
        $consulta = "UPDATE procesos
                     SET 
                         codigo = ?,
                         nombre = ?,
                         descripcion = ?,
                         ruta_archivo = ?,
                         empresa_id = ?,
                         sede_id = ?,
                         fecha_modificacion = NOW(),
                         estatus = ?,
                        oea = ?,
                        ctpat = ?,
                        wrap = ?,
                        ipm = ?
                     WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('ssssiiiiiiii',$modelo->codigo, $modelo->nombre, $modelo->descripcion, $modelo->rutaArchivo, $modelo->empresaId, $modelo->sedeId ,$modelo->estatus,$modelo->oea, $modelo->ctpat, $modelo->wrap, $modelo->ipm,$modelo->id  ))
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

    public function consultar($criteriosSeleccion)
    {
        ini_set('max_execution_time', 300);
        $resultado = new Resultado();
        $registros = array();
        $filtros = array();
        $where='';
        if($criteriosSeleccion!=null)
        {
            if(isset($criteriosSeleccion->nombre))
                array_push($filtros,(object)['tipoDato'=>'varchar','tabla'=>'P','campo'=>'nombre','valor'=>$criteriosSeleccion->nombre]);
            if(isset($criteriosSeleccion->empresaId))
            {
                if($criteriosSeleccion->empresaId!="" && $criteriosSeleccion->empresaId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'P','campo'=>'empresa_id','valor'=>$criteriosSeleccion->empresaId]);
            }
            if(isset($criteriosSeleccion->sedeId))
            {
                if($criteriosSeleccion->sedeId!="" && $criteriosSeleccion->sedeId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'P','campo'=>'sede_id','valor'=>$criteriosSeleccion->sedeId]);
            }
           
        }
        array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'E','campo'=>'estatus','valor'=>1]);
        $where = $this->where($filtros);
        $consulta = $this->consultaBase . $where;
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $codigo, $nombre, $descripcion, $rutaArchivo, $empresaId, $empresaNombre,$sedeId, $sedeNombre, $fechaAlta, $fechaModificacion, $estatus, $oea, $ctpat, $wrap, $ipm))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $codigo, $nombre, $descripcion, $rutaArchivo, $empresaId, $empresaNombre,$sedeId, $sedeNombre,$fechaAlta, $fechaModificacion, $estatus, $oea, $ctpat, $wrap, $ipm);
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
    
    public function consultarPorEmpresaSede($empresaId,$sedeId)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $consulta =   $this->consultaBase .
        " WHERE P.empresa_id = ? " .
        " AND P.sede_id = ? ORDER BY P.nombre";
        
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("ii",$empresaId,$sedeId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $codigo, $nombre, $descripcion, $rutaArchivo, $empresaId, $empresaNombre,$sedeId, $sedeNombre, $fechaAlta, $fechaModificacion, $estatus, $oea, $ctpat, $wrap, $ipm)  )
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $codigo, $nombre, $descripcion, $rutaArchivo, $empresaId, $empresaNombre,$sedeId, $sedeNombre, $fechaAlta, $fechaModificacion, $estatus, $oea, $ctpat, $wrap, $ipm);
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

    public function consultarPorLlaves($llaves)
    {
        $resultado = new Resultado();
        $consulta = $this->consultaBase .
        ' WHERE P.id  = ?';
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('i',$llaves->id))
            {
                if($sentencia->execute())
                {
                    if($sentencia->bind_result($id, $codigo, $nombre, $descripcion, $rutaArchivo, $empresaId, $empresaNombre,$sedeId, $sedeNombre, $fechaAlta, $fechaModificacion, $estatus, $oea, $ctpat, $wrap, $ipm))
                    {
                        if($sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $codigo, $nombre, $descripcion, $rutaArchivo, $empresaId, $empresaNombre,$sedeId, $sedeNombre, $fechaAlta, $fechaModificacion, $estatus, $oea, $ctpat, $wrap, $ipm);
                            $resultado->valor = $registro;
                        }
                        else
                            $resultado->mensajeError = 'No se encontró ningún resultado.';
                    }
                    else
                        $resultado->mensajeError = 'Falló el enlace del resultado';
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

    public function eliminar($llaves)
    {
        $resultado = new Resultado();
        $consulta = "DELETE FROM procesos WHERE id = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param('i',$llaves->id))
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

    private function crearRegistro($id, $codigo, $nombre, $descripcion, $rutaArchivo, $empresaId, $empresaNombre,$sedeId, $sedeNombre, $fechaAlta, $fechaModificacion, $estatus, $oea, $ctpat, $wrap, $ipm)
    {
        $registro= (object) 
        [
            'id' => $id,
            'codigo' => $codigo,
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'rutaArchivo' => $rutaArchivo,
            'empresaId' => $empresaId,
            'empresaNombre' => $empresaNombre,
            'sedeId' => $sedeId,
            'sedeNombre' => $sedeNombre,
            'fechaAlta' => $fechaAlta,
            'fechaModificacion' => $fechaModificacion,
            'estatus' => $estatus,
            'oea' => $oea,
            'ctpat' => $ctpat,
            'wrap' => $wrap,
            'ipm' => $ipm
        ];
        return $registro;
    }
    
    public function getFiltrosN($usuario, $criteriosSeleccion, $agregarFiltrosFecha)
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
                    $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
                    $resultado = $usuariosRepositorio->consultarIdsUsuarios($usuario);
                    if($resultado->correcto())
                    {
                        $usuariosIds = implode(",", $resultado->valor);
                        array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'U', 'campo'=>'id','operador'=>'IN','valor'=>$usuariosIds]);
                    }
                }
                break;
            case \TipoUsuario::COORDINADOR:
                if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'EM', 'campo'=>'id','valor'=>$criteriosSeleccion->empresaId]);
                else
                {
                    $usuariosRepositorio = new UsuariosRepositorio($this->conexion);
                    $resultado = $usuariosRepositorio->consultarIdsEmpresas($usuario->empresaId);
                    if($resultado->correcto())
                    {
                        $empresasIds = implode(",", $resultado->valor);
                        array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'EM', 'campo'=>'id','operador'=>'IN','valor'=>$empresasIds]);
                    }
                }
            break;
            case \TipoUsuario::ADMINISTRADOR:
                if(isset($criteriosSeleccion->empresaId) && $criteriosSeleccion->empresaId!="")
                    array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'EM', 'campo'=>'id','valor'=>$criteriosSeleccion->empresaId]);
                    
            break;
        }
        if(isset($criteriosSeleccion->sedeId) && $criteriosSeleccion->sedeId!="")
            array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'S', 'campo'=>'id','valor'=> $criteriosSeleccion->sedeId]);
        if(isset($criteriosSeleccion->departamentoId) && $criteriosSeleccion->departamentoId!="")
            array_push($filtros,(object)['tipoDato'=>'int','tabla' => 'D', 'campo'=>'id','valor'=> $criteriosSeleccion->departamentoId]);
        if($agregarFiltrosFecha)
        {
            if(isset($criteriosSeleccion->ano)  && $criteriosSeleccion->ano!="")
                array_push($filtros,(object)['tipoDato'=>'int','campo'=>'YEAR(E.fecha_alta)','valor'=>$criteriosSeleccion->ano]);
            if(isset($criteriosSeleccion->mes)  && $criteriosSeleccion->mes!="")
                array_push($filtros,(object)['tipoDato'=>'int','campo'=>'MONTH(E.fecha_alta)','valor'=>$criteriosSeleccion->mes]);
        }
        return $filtros;
    }
}
