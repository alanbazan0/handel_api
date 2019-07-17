<?php
namespace php\repositorios;

use php\interfaces\IInspeccionesRepositorio;
use php\modelos\Inspeccion;
use php\modelos\Resultado;

include "../interfaces/IInspeccionesReporitorio.php";
include "../modelos/Inspeccion.php";
include "../modelos/Punto.php";
include "../clases/TipoUsuario.php";
include "RepositorioBase.php";
require_once("../clases/Resultado.php");

class InspeccionesRepositorio extends RepositorioBase implements IInspeccionesRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        
        $this->conexion = $conexion;
        $this->consultaBase = " SELECT I.id, E.id empresaId, E.nombre empresaNombre, I.id sedeId, S.nombre sedeNombre, usuario_id usuarioId, US.nombre usuarioNombre, inspector_id inspectorId, CONCAT(INS.nombre,' ', INS.apellido) inspectorNombre, IFNULL(DATE_FORMAT(fecha_inspeccion ,'%d/%m/%Y %H:%i:%s'),'')fechaInspeccion, I.area_id, A.nombre, numero_caja numeroCaja, E.nombre_corto, S.nombre_corto, A.tipo_area_id, TA.nombre, IFNULL(DATE_FORMAT(fecha_finalizacion ,'%d/%m/%Y %H:%i:%s'),'')fechaFinalizacion, transportista, chofer, numero_tractor, placas_tractor, placas_caja, color_tractor, color_caja, numero_contenedor, tipo_caja, sello, sello_viajero, alto,  ancho, profundidad,  entrada_salida, TI.id, TI.descripcion,destino, numero_orden,piezas, bultos, peso, otras_mercancias, turno_inicio, turno_fin, fecha_subida, manifiesto, inspector_termina, CONCAT(INST.nombre,' ', INST.apellido) , sello_colocado " .
            " FROM inspecciones I " .
            "   LEFT JOIN sedes S ON S.id = I.sede_id " .
            "   LEFT JOIN empresas E ON E.id = S.empresa_id " .
            "   LEFT JOIN areas A ON A.id = I.area_id " .
            "   LEFT JOIN tipos_area TA ON A.tipo_area_id = TA.id " .
            "   LEFT JOIN usuarios US ON US.id = I.usuario_id " .
            "   LEFT JOIN usuarios INS ON INS.id = I.inspector_id " .
            "   LEFT JOIN usuarios INST ON INST.id = I.inspector_termina " .
            "   INNER JOIN tipo_inspeccion TI ON I.tipo_inspeccion_id = TI.id";
    }
    
    public function insertar(Inspeccion $modelo)
    {
        $resultado =  $this->calcularId("id","inspecciones");
        if($resultado->mensajeError=="")
        {
            $modelo->id = $resultado->valor;
            $consulta = "INSERT INTO inspecciones(id, sede_id, usuario_id, inspector_id, area_id, fecha_inspeccion, fecha_finalizacion, numero_caja,  transportista, chofer, numero_tractor, placas_tractor, placas_caja, color_tractor, color_caja, numero_contenedor, tipo_caja, sello, sello_viajero, alto, ancho, profundidad, entrada_salida, tipo_inspeccion_id, fecha_inicio,tablet_id,destino, numero_orden,piezas, bultos, peso, otras_mercancias, turno_inicio, turno_fin, fecha_subida,manifiesto, inspector_termina, sello_colocado ) " .
                "VALUE(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? , ?, NOW(),?,?,?)";
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if( $sentencia->bind_param("iiiiisssssissssssssiiisisisssssssssis", $modelo->id, $modelo->sedeId,$modelo->usuarioId, $modelo->inspectorId, $modelo->areaId, $modelo->fechaInspeccion, $modelo->fechaFinalizacion, $modelo->numeroCaja, $modelo->transportista, $modelo->chofer, $modelo->numeroTractor, $modelo->placasTractor, $modelo->placasCaja, $modelo->colorTractor, $modelo->colorCaja, $modelo->numeroContenedor, $modelo->tipoCaja, $modelo->sello, $modelo->selloViajero,$modelo->alto, $modelo->ancho, $modelo->profundidad, $modelo->entradaSalida, $modelo->tipoInspeccionId, $modelo->fechaInicio, $modelo->tabletId,$modelo->destino,$modelo->numeroOrden, $modelo->piezas, $modelo->bultos, $modelo->peso, $modelo->otrasMercancias, $modelo->turnoInicio, $modelo->turnoFin, $modelo->manifiesto, $modelo->inspectorTermina, $modelo->selloColocado ))
                {
                    if($sentencia->execute())
                    {
                        $sentencia->close();
                        
                        $resultado = $this->insertarPuntosInspeccion($modelo);
                        if($resultado->mensajeError=="")
                        {
                           
                            $resultado = $this->subirFotos($modelo);
                            if($resultado->mensajeError=="")
                            {
                                $resultado->valor = $modelo->id;
                                $this->conexion->commit();
                            }
                            else
                                $this->conexion->rollback();
                        }
                        else
                            $this->conexion->rollback();
                    }
                    else
                    {
                        $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                        $this->conexion->rollback();
                    }
                }
                else
                {
                    $resultado->mensajeError = "Falló el enlace de parámetros";
                    $this->conexion->rollback();
                }
            }
            else
            {
                $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
                $this->conexion->rollback();
            }
        }
        return $resultado;
    }
    
    private function subirFotos($inspeccion)
    {
        $resultado = new Resultado();
        
        if(isset($inspeccion->puntos))
        {
            for ($i = 0; $i < count($inspeccion->puntos); $i++)
            {
                $punto = $inspeccion->puntos[$i];
                if(isset($punto->fotos))
                {
                    for ($j = 0; $j < count($punto->fotos); $j++)
                    {
                        $fotoBase64 = $punto->fotos[$j];
                        $foto = base64_decode($fotoBase64);
                        
                        $numeroFoto = $j + 1;
                        $id = $inspeccion->id . "_" .$punto->id  . "_" . $numeroFoto .".jpg"; 
                        
                        $archivo = '../fotos_inspecciones/'.$id;
                        
                        if(file_exists($archivo))
                            unlink($archivo);
                        
                        $file = fopen($archivo, 'wb');
                        fwrite($file, $foto);
                        fclose($file);
                    }
                }
            }
        }
        
        if(isset($inspeccion->fotos))
        {
            for ($j = 0; $j < count($inspeccion->fotos); $j++)
            {
                $fotoBase64 = $inspeccion->fotos[$j]->base64;
                $nombre = $inspeccion->fotos[$j]->nombre;
                $foto = base64_decode($fotoBase64);
                
                $id = $inspeccion->id . "_" .$nombre.".jpg";
                
                $file = fopen('../fotos_inspecciones/' .$id, 'wb');
                fwrite($file, $foto);
                fclose($file);
            }
        }
        
        return $resultado;
    }
    
    private function insertarPuntosInspeccion($inspeccion)
    {
        $resultado = new Resultado();
        
      
        if(isset($inspeccion->puntos))
		{
			for ($i = 0; $i < count($inspeccion->puntos); $i++)
			{
			    $punto = $inspeccion->puntos[$i];
				
				$consulta = "INSERT INTO inspecciones_puntos(inspeccion_id, punto_inspeccion_id, resultado, observaciones) " .
					"VALUE(?, ?, ?, ?)";
				if($sentencia = $this->conexion->prepare($consulta))
				{
					if($sentencia->bind_param("iiss",$inspeccion->id,$punto->id, $punto->resultado, $punto->observaciones))
					{
						if($sentencia->execute())
						{
							$sentencia->close();
						}
						else
						{
							$resultado->codigoError = $this->conexion->errno;
							$resultado->mensajeError = "Falló la ejecución insertarPuntosInspeccion(" . $this->conexion->errno . ") " . $this->conexion->error;
							break;
						}
						
					}
					else
					{
						$resultado->mensajeError = "Falló el enlace de parámetros insertarPuntosInspeccion";
						break;
					}
				}
				else
				{
					$resultado->codigoError = $this->conexion->errno;
					$resultado->mensajeError = "Falló la preparación: insertarPuntosInspeccion(" . $this->conexion->errno . ") " . $this->conexion->error;
					break;
				}
			}
        }
        return $resultado;
    }
    
    
    public function actualizar(Inspeccion $modelo)
    {
        $resultado = new Resultado();
        $consulta = " UPDATE puestos " .
            "SET nombre = ?, " .
            "  empresa_id = ?, " .
            "  sede_id = ?, " .
            "  estatus = ?, " .
            "  fecha_modificacion= NOW() " .
            "WHERE id = ? ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if( $sentencia->bind_param("siiii", $modelo->nombre, $modelo->empresaId,$modelo->sedeId, $modelo->estatus,$modelo->id ))
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
    
    public function consultar($criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        $filtros = array();
        $where="";
        if($criteriosSeleccion!=null)
        {
            if(isset($criteriosSeleccion->empresaId))
            {
                if($criteriosSeleccion->empresaId!="" && $criteriosSeleccion->empresaId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'S','campo'=>'empresa_id','valor'=>$criteriosSeleccion->empresaId]);
            }
            if(isset($criteriosSeleccion->sedeId))
            {
                if($criteriosSeleccion->sedeId!="" && $criteriosSeleccion->sedeId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'I','campo'=>'sede_id','valor'=>$criteriosSeleccion->sedeId]);
            }
            if(isset($criteriosSeleccion->areaId))
            {
                if($criteriosSeleccion->areaId!="" && $criteriosSeleccion->areaId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'I','campo'=>'area_id','valor'=>$criteriosSeleccion->areaId]);
            }
            if(isset($criteriosSeleccion->fechaInicial))
            {
                if($criteriosSeleccion->fechaInicial!="" && $criteriosSeleccion->fechaInicial!=null)
                    array_push($filtros,(object)['tipoDato'=>'date','operador'=>'>=','tabla'=>'I','campo'=>'fecha_inspeccion','valor'=>$criteriosSeleccion->fechaInicial]);
            }
            if(isset($criteriosSeleccion->fechaFinal))
            {
                if($criteriosSeleccion->fechaFinal!="" && $criteriosSeleccion->fechaFinal!=null)
                    array_push($filtros,(object)['tipoDato'=>'date','operador'=>'<=','tabla'=>'I','campo'=>'fecha_inspeccion','valor'=>$criteriosSeleccion->fechaFinal]);
            }
            if(isset($criteriosSeleccion->numeroCaja))
            {
                if($criteriosSeleccion->numeroCaja!="" && $criteriosSeleccion->numeroCaja!=null)
                    array_push($filtros,(object)['tipoDato'=>'varchar','tabla' => 'I', 'campo'=>'numero_caja','valor'=>$criteriosSeleccion->numeroCaja]);
            }
            $where = $this->where($filtros);
        }
        $consulta = $this->consultaBase .
        $where . " ORDER BY I.id";
        
       //echo $consulta;
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $empresaId, $empresaNombre, $sedeId, $sedeNombre, $usuarioId, $usuarioNombre, $inspectorId, $inspectorNombre, $fechaInspeccion, $areaId,$areaNombre, $numeroCaja, $empresaNombreCorto, $sedeNombreCorto, $tipoAreaId, $tipoAreaNombre, $fechaFinalizacion,$transportista, $chofer, $numeroTractor, $placasTractor, $placasCaja, $colorTractor, $colorCaja, $numeroContenedor, $tipoCaja, $sello, $selloViajero, $alto, $ancho, $profundidad, $entrada_salida, $tipoInspeccionId, $tipoInspeccionDescripcion,$destino, $numeroOrden, $piezas, $bultos, $peso, $otrasMercancias, $turnoInicio, $turnoFin, $fechaSubida, $manifiesto, $inspectorTerminaId,$inspectorTerminaNombre, $selloColocado))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro = $this->crearRegistro($id, $empresaId, $empresaNombre, $sedeId, $sedeNombre, $usuarioId, $usuarioNombre, $inspectorId, $inspectorNombre, $fechaInspeccion, $areaId, $areaNombre,$numeroCaja, $empresaNombreCorto, $sedeNombreCorto,$tipoAreaId, $tipoAreaNombre,$fechaFinalizacion,$transportista, $chofer, $numeroTractor, $placasTractor, $placasCaja, $colorTractor, $colorCaja, $numeroContenedor, $tipoCaja, $sello, $selloViajero,  $alto, $ancho, $profundidad, $entrada_salida,$tipoInspeccionId, $tipoInspeccionDescripcion,$destino, $numeroOrden, $piezas, $bultos, $peso, $otrasMercancias, $turnoInicio, $turnoFin, $fechaSubida, $manifiesto, $inspectorTerminaId,$inspectorTerminaNombre, $selloColocado);
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
        " WHERE I.id  = ?";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$llaves->id))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $empresaId, $empresaNombre, $sedeId, $sedeNombre, $usuarioId, $usuarioNombre, $inspectorId, $inspectorNombre, $fechaInspeccion, $areaId,$areaNombre, $numeroCaja, $empresaNombreCorto, $sedeNombreCorto,$tipoAreaId, $tipoAreaNombre,$fechaFinalizacion,$transportista, $chofer, $numeroTractor, $placasTractor, $placasCaja, $colorTractor, $colorCaja, $numeroContenedor, $tipoCaja, $sello, $selloViajero, $alto, $ancho, $profundidad, $entrada_salida,$tipoInspeccionId, $tipoInspeccionDescripcion,$destino, $numeroOrden, $piezas, $bultos, $peso, $otrasMercancias, $turnoInicio, $turnoFin, $fechaSubida,$manifiesto, $inspectorTerminaId,$inspectorTerminaNombre, $selloColocado))
                    {
                        if($sentencia->fetch())
                        {
                            $inspeccion = $this->crearRegistro($id, $empresaId, $empresaNombre, $sedeId, $sedeNombre, $usuarioId, $usuarioNombre, $inspectorId, $inspectorNombre, $fechaInspeccion, $areaId, $areaNombre, $numeroCaja, $empresaNombreCorto, $sedeNombreCorto,$tipoAreaId, $tipoAreaNombre,$fechaFinalizacion,$transportista, $chofer, $numeroTractor, $placasTractor, $placasCaja, $colorTractor, $colorCaja, $numeroContenedor, $tipoCaja, $sello, $selloViajero, $alto, $ancho, $profundidad, $entrada_salida,$tipoInspeccionId, $tipoInspeccionDescripcion,$destino, $numeroOrden, $piezas, $bultos, $peso, $otrasMercancias, $turnoInicio, $turnoFin, $fechaSubida,$manifiesto, $inspectorTerminaId,$inspectorTerminaNombre, $selloColocado);
                            $resultado->valor = $inspeccion;
                            
                            $sentencia->close();
                            
                           
                            
                            $resultadoPuntos = $this->consultarPuntos1($inspeccion->id);
                            if($resultadoPuntos->mensajeError=="")
                            {
                                $inspeccion->puntos1 = $resultadoPuntos->valor;
                                $resultadoPuntos = $this->consultarPuntos2($inspeccion->id);
                                if($resultadoPuntos->mensajeError=="")
                                {
                                    $inspeccion->puntos2 = $resultadoPuntos->valor;
                                }
                                else
                                    $resultado->mensajeError = $resultadoPuntos->mensajeError;
                            }
                            else
                                $resultado->mensajeError = $resultadoPuntos->mensajeError;
                            
                            
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
    
    private function crearRegistro($id, $empresaId, $empresaNombre, $sedeId, $sedeNombre, $usuarioId, $usuarioNombre, $inspectorId, $inspectorNombre, $fechaInspeccion, $areaId, $areaNombre, $numeroCaja, $empresaNombreCorto, $sedeNombreCorto,$tipoAreaId, $tipoAreaNombre,$fechaFinalizacion,$transportista, $chofer, $numeroTractor, $placasTractor, $placasCaja, $colorTractor, $colorCaja, $numeroContenedor, $tipoCaja, $sello, $selloViajero, $alto, $ancho, $profundidad, $entrada_salida,$tipoInspeccionId, $tipoInspeccionDescripcion,$destino, $numeroOrden, $piezas, $bultos, $peso, $otrasMercancias, $turnoInicio, $turnoFin, $fechaSubida,$manifiesto, $inspectorTerminaId,$inspectorTerminaNombre, $selloColocado)
    {
        $registro= (object) [
            'id' =>  $id,
            'empresaId' => $empresaId,
            'empresaNombre' => $empresaNombre,
            'empresaNombreCorto' => $empresaNombreCorto,
            'sedeId' => $sedeId,
            'sedeNombre' => $sedeNombre,
            'sedeNombreCorto' => $sedeNombreCorto,
            'usuarioId' => $usuarioId,
            'usuarioNombre' => $usuarioNombre,
            'inspectorId' => $inspectorId,
            'inspectorNombre' => $inspectorNombre,
            'fechaInspeccion' => $fechaInspeccion,
            'areaId' => $areaId,
            'areaNombre' => $areaNombre,
            'tipoAreaId' => $tipoAreaId,
            'tipoAreaNombre' => $tipoAreaNombre,
            'numeroCaja' => $numeroCaja,
            'fechaFinalizacion' => $fechaFinalizacion,
            'transportista' =>  $transportista, 
            'chofer' => $chofer, 
            'numeroTractor' => $numeroTractor, 
            'placasTractor' => $placasTractor, 
            'placasCaja' => $placasCaja, 
            'colorTractor' => $colorTractor,
            'colorCaja' => $colorCaja, 
            'numeroContenedor' => $numeroContenedor, 
            'tipoCaja' => $tipoCaja, 
            'sello' => $sello, 
            'selloViajero' => $selloViajero,
            'alto' => $alto,
            'ancho' => $ancho,
            'profundidad' => $profundidad,
            'entradaSalida' => $entrada_salida,
            'tipoInspeccionId' => $tipoInspeccionId,
            'tipoInspeccionDescripcion' => $tipoInspeccionDescripcion,
            'destino' =>$destino, 
            'numeroOrden' =>$numeroOrden, 
            'piezas' =>$piezas, 
            'bultos' =>$bultos, 
            'peso' =>$peso, 
            'otrasMercancias' =>$otrasMercancias, 
            'turnoInicio' =>$turnoInicio, 
            'turnoFin' =>$turnoFin, 
            'fechaSubida' =>$fechaSubida,
            'manifiesto' =>$manifiesto, 
            'inspectorTerminaId' =>$inspectorTerminaId,
            'inspectorTerminaNombre' =>$inspectorTerminaNombre, 
            'selloColocado' =>$selloColocado
            
        ];
        return $registro;
    }
    
//     public function consultarPorEmpresaSede($empresaId, $sedeId)
//     {
//         $resultado = new Resultado();
//         $registros = array();
//         $consulta = $this->consultaBase .
//         " WHERE P.empresa_id  = ?" .
//         "   AND P.sede_id = ?";
//         if($sentencia = $this->conexion->prepare($consulta))
//         {
//             if($sentencia->bind_param("ii",$empresaId,$sedeId))
//             {
//                 if($sentencia->execute())
//                 {
//                     if ($sentencia->bind_result($id, $nombre,$empresaId, $empresaNombre,$sedeId, $sedeNombre, $fechaAlta, $fechaModificacion, $estatus))
//                     {
//                         while($row = $sentencia->fetch())
//                         {
//                             $registro = $this->crearRegistro($id, $nombre,$empresaId, $empresaNombre,$sedeId, $sedeNombre, $fechaAlta, $fechaModificacion, $estatus);
//                             array_push($registros,$registro);
//                         }
//                         $resultado->valor = $registros;
//                     }
//                     else
//                         $resultado->mensajeError = "Falló el enlace del resultado";
//                 }
//                 else
//                     $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
//             }
//             else
//                 $resultado->mensajeError = "Falló el enlace de parámetros";
//         }
//         else
//             $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
//             return $resultado;
//     }
    
    public function eliminar($llaves)
    {
        $resultado = new Resultado();
        $consulta = " DELETE FROM inspecciones "
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
    
    public function consultarInspeccionesEmpresa($usuario, $criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        $consulta = "SELECT E.id, E.nombre  nombre, count(I.id) valor " .
                    " FROM inspecciones I " .
                    "   INNER JOIN sedes S ON S.id = I.sede_id " .
                    "   INNER JOIN empresas E ON E.id = S.empresa_id " .
                    "GROUP BY E.id, E.nombre "  .
                    "ORDER BY E.nombre";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
//             if($sentencia->bind_param("ii",$empresaId,$sedeId))
            //{
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombre,$valor))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro= (object) [
                                'id' =>  $id,
                                'nombre' => $nombre,
                                'valor' => $valor
                            ];
                            array_push($registros,$registro);
                        }
                        $resultado->valor = $registros;
                    }
                    else
                        $resultado->mensajeError = "Falló el enlace del resultado";
                }
                else
                    $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
//             }
//             else
//                 $resultado->mensajeError = "Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            return $resultado;
    }
    
    public function consultarInspeccionesMes($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        if($usuario->empresaId=="")
            $usuario->empresaId = -1;
         if($usuario->sedeId=="")
             $usuario->sedeId = "-1";
        
        
        $filtro = "";

            
        $ano = date("Y");
        if($criteriosSeleccion!=null)
        {
            if(isset($criteriosSeleccion->ano))
                $ano = $criteriosSeleccion->ano;
            if(isset($criteriosSeleccion->empresaId))
            {
                if($criteriosSeleccion->empresaId!="" && $criteriosSeleccion->empresaId!=null)
                    $filtro .= " AND S.empresa_id = $criteriosSeleccion->empresaId";
            }
            if(isset($criteriosSeleccion->sedeId))
            {
                if($criteriosSeleccion->sedeId!="" && $criteriosSeleccion->sedeId!=null)
                    $filtro .= " AND S.id = $criteriosSeleccion->sedeId";
            }
            if(isset($criteriosSeleccion->areaId))
            {
                if($criteriosSeleccion->areaId!="" && $criteriosSeleccion->areaId!=null)
                    $filtro .= " AND I.area_id = $criteriosSeleccion->areaId";
            }
        }
        else       {
            if($usuario->tipoUsuarioId == \TipoUsuario::ADMINISTRADOR) 
                $filtro = "";
            else if($usuario->tipoUsuarioId == \TipoUsuario::COORDINADOR)
                $filtro = " AND S.empresa_id = $usuario->empresaId";
            else if($usuario->tipoUsuarioId == \TipoUsuario::SUPERVISOR) 
                $filtro = " AND S.empresa_id = $usuario->empresaId  AND sede_id = $usuario->sedeId";
            
        }
           
            
        $consulta = "SELECT 1, 'Ene' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE YEAR(fecha_inspeccion)=$ano AND MONTH(fecha_inspeccion) = 1 $filtro) valor  UNION " .
            "SELECT 2, 'Feb' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE YEAR(fecha_inspeccion)=$ano AND MONTH(fecha_inspeccion) = 2 $filtro) valor UNION " .
            "SELECT 3, 'Mar' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE YEAR(fecha_inspeccion)=$ano AND MONTH(fecha_inspeccion) = 3 $filtro) valor UNION " .
            "SELECT 4, 'Abr' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE YEAR(fecha_inspeccion)=$ano AND MONTH(fecha_inspeccion) = 4 $filtro) valor UNION " .
            "SELECT 5, 'May' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE YEAR(fecha_inspeccion)=$ano AND MONTH(fecha_inspeccion) = 5 $filtro) valor UNION " .
            "SELECT 6, 'Jun' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE YEAR(fecha_inspeccion)=$ano AND MONTH(fecha_inspeccion) = 6 $filtro) valor UNION " .
            "SELECT 7, 'Jul' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE YEAR(fecha_inspeccion)=$ano AND MONTH(fecha_inspeccion) = 7 $filtro) valor UNION " .
            "SELECT 8, 'Ago' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE YEAR(fecha_inspeccion)=$ano AND MONTH(fecha_inspeccion) = 8 $filtro) valor UNION " .
            "SELECT 9, 'Sep' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE YEAR(fecha_inspeccion)=$ano AND MONTH(fecha_inspeccion) = 9 $filtro) valor UNION " .
            "SELECT 10, 'Oct' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE YEAR(fecha_inspeccion)=$ano AND MONTH(fecha_inspeccion) = 10 $filtro) valor UNION " .
            "SELECT 11, 'Nov' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE YEAR(fecha_inspeccion)=$ano AND MONTH(fecha_inspeccion) = 11 $filtro) valor UNION " .
            "SELECT 12, 'Dic' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE YEAR(fecha_inspeccion)=$ano AND MONTH(fecha_inspeccion) = 12 $filtro) valor  ";
          
        if($sentencia = $this->conexion->prepare($consulta))
        {
            //             if($sentencia->bind_param("ii",$empresaId,$sedeId))
            //{
            if($sentencia->execute())
            {
                if ($sentencia->bind_result($id, $nombre,$valor))
                {
                    while($row = $sentencia->fetch())
                    {
                        $registro= (object) [
                            'id' =>  $id,
                            'nombre' => $nombre,
                            'valor' => $valor
                        ];
                        array_push($registros,$registro);
                    }
                    $resultado->valor = $registros;
                }
                else
                    $resultado->mensajeError = "Falló el enlace del resultado";
            }
            else
                $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                //             }
            //             else
                //                 $resultado->mensajeError = "Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
            return $resultado;
    }
    
    public function consultarInspeccionesHora($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
       
                
                
        $filtro = "";
        
        
        $ano = date("Y");
        if($criteriosSeleccion!=null)
        {
            if(isset($criteriosSeleccion->ano))
                $ano = $criteriosSeleccion->ano;
                if(isset($criteriosSeleccion->empresaId))
                {
                    if($criteriosSeleccion->empresaId!="" && $criteriosSeleccion->empresaId!=null)
                        $filtro .= " AND S.empresa_id = $criteriosSeleccion->empresaId";
                }
                if(isset($criteriosSeleccion->sedeId))
                {
                    if($criteriosSeleccion->sedeId!="" && $criteriosSeleccion->sedeId!=null)
                        $filtro .= " AND S.id = $criteriosSeleccion->sedeId";
                }
                if(isset($criteriosSeleccion->areaId))
                {
                    if($criteriosSeleccion->areaId!="" && $criteriosSeleccion->areaId!=null)
                        $filtro .= " AND I.area_id = $criteriosSeleccion->areaId";
                }
                if(isset($criteriosSeleccion->fecha))
                {
                    if($criteriosSeleccion->fecha!="" && $criteriosSeleccion->fecha!=null)
                    {
                        list($ano, $mes, $dia) = explode("/", $criteriosSeleccion->fecha);
                        //$fecha = $ano."-".$mes."-".$dia;
                        $filtro .= " AND YEAR(fecha_inspeccion) = $ano AND MONTH(fecha_inspeccion) = $mes AND DAY(fecha_inspeccion) = $dia ";
                    }
                }
        }
      
                
        $consulta = "SELECT 0, '0' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE HOUR(fecha_inspeccion) = 0 $filtro) valor  UNION " .
        "SELECT 1, '1' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE HOUR(fecha_inspeccion) = 1 $filtro) valor  UNION " .
        "SELECT 2, '2' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE HOUR(fecha_inspeccion) = 2 $filtro) valor  UNION " .
        "SELECT 3, '3' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE HOUR(fecha_inspeccion) = 3 $filtro) valor  UNION " .
        "SELECT 4, '4' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE HOUR(fecha_inspeccion) = 4 $filtro) valor  UNION " .
        "SELECT 5, '5' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE HOUR(fecha_inspeccion) = 5 $filtro) valor  UNION " .
        "SELECT 6, '6' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE HOUR(fecha_inspeccion) = 6 $filtro) valor  UNION " .
        "SELECT 7, '7' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE HOUR(fecha_inspeccion) = 7 $filtro) valor  UNION " .
        "SELECT 8, '8' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE HOUR(fecha_inspeccion) = 8 $filtro) valor  UNION " .
        "SELECT 9, '9' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE HOUR(fecha_inspeccion) = 9 $filtro) valor  UNION " .
        "SELECT 10, '10' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE HOUR(fecha_inspeccion) = 10 $filtro) valor  UNION " .
        "SELECT 11, '11' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE HOUR(fecha_inspeccion) = 11 $filtro) valor  UNION " .
        "SELECT 12, '12' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE HOUR(fecha_inspeccion) = 12 $filtro) valor  UNION " .
        "SELECT 13, '13' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE HOUR(fecha_inspeccion) = 13 $filtro) valor  UNION " .
        "SELECT 14, '14' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE HOUR(fecha_inspeccion) = 14 $filtro) valor  UNION " .
        "SELECT 15, '15' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE HOUR(fecha_inspeccion) = 15 $filtro) valor  UNION " .
        "SELECT 16, '16' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE HOUR(fecha_inspeccion) = 16 $filtro) valor  UNION " .
        "SELECT 17, '17' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE HOUR(fecha_inspeccion) = 17 $filtro) valor  UNION " .
        "SELECT 18, '18' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE HOUR(fecha_inspeccion) = 18 $filtro) valor  UNION " .
        "SELECT 19, '19' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE HOUR(fecha_inspeccion) = 19 $filtro) valor  UNION " .
        "SELECT 20, '20' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE HOUR(fecha_inspeccion) = 20 $filtro) valor  UNION " .
        "SELECT 21, '21' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE HOUR(fecha_inspeccion) = 21 $filtro) valor  UNION " .
        "SELECT 22, '22' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE HOUR(fecha_inspeccion) = 22 $filtro) valor  UNION " .
        "SELECT 23, '23' nombre, (SELECT count(*) valor FROM inspecciones I INNER JOIN sedes S ON S.id = I.sede_id INNER JOIN empresas E ON S.empresa_id = E.id WHERE HOUR(fecha_inspeccion) = 23 $filtro) valor ";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            //             if($sentencia->bind_param("ii",$empresaId,$sedeId))
            //{
            if($sentencia->execute())
            {
                if ($sentencia->bind_result($id, $nombre,$valor))
                {
                    while($row = $sentencia->fetch())
                    {
                        $registro= (object) [
                            'id' =>  $id,
                            'nombre' => $nombre,
                            'valor' => $valor
                        ];
                        array_push($registros,$registro);
                    }
                    $resultado->valor = $registros;
                }
                else
                    $resultado->mensajeError = "Falló el enlace del resultado";
            }
            else
                $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
                //             }
            //             else
            //                 $resultado->mensajeError = "Falló el enlace de parámetros";
        }
        else
            $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
        return $resultado;
    }
    
    public function consultarInspeccionesSede($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
//         $filtro = "";
//         if($usuario->tipoUsuarioId == \TipoUsuario::ADMINISTRADOR)
//             $filtro = " AND empresa_id = $usuario->empresaId  AND sede_id = $usuario->sedeId";
//             else
//                 if($usuario->tipoUsuarioId == \TipoUsuario::ADMINISTRADOR_CORPORATIVO)
//                     $filtro = " AND empresa_id = $usuario->empresaId";
                
        $filtros = array();
        $where="";

        if($usuario->tipoUsuarioId == \TipoUsuario::ADMINISTRADOR)
        {
            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'S','campo'=>'empresa_id','valor'=>$usuario->empresaId]);
            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'I','campo'=>'sede_id','valor'=>$usuario->sedeId]);
         
        }
        else if($usuario->tipoUsuarioId == \TipoUsuario::COORDINADOR)
        {
            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'S','campo'=>'empresa_id','valor'=>$usuario->empresaId]);
        }
        else if($usuario->tipoUsuarioId == \TipoUsuario::SUPERVISOR)
        {
            array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'S','campo'=>'empresa_id','valor'=>$usuario->empresaId]);
        }
        $where = $this->where($filtros);
        
        $consulta = "SELECT S.id, S.nombre  nombre, count(I.id) valor " .
            " FROM inspecciones I " .
            "   INNER JOIN sedes S ON S.id = I.sede_id " .
            "   INNER JOIN empresas E ON E.id = S.empresa_id " .
            $where .
            "GROUP BY S.id, S.nombre "  .
            "ORDER BY S.nombre";
        
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($this->bind_param($sentencia, $filtros))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($id, $nombre,$valor))
                    {
                        while($row = $sentencia->fetch())
                        {
                            $registro= (object) [
                                'id' =>  $id,
                                'nombre' => $nombre,
                                'valor' => $valor
                            ];
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
    
    public function consultarInspeccionesArea($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $filtros = array();
        $where="";
        
        if($criteriosSeleccion!=null)
        {
            if(isset($criteriosSeleccion->empresaId))
            {
                if($criteriosSeleccion->empresaId!="" && $criteriosSeleccion->empresaId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'S','campo'=>'empresa_id','valor'=>$criteriosSeleccion->empresaId]);
            }
            if(isset($criteriosSeleccion->sedeId))
            {
                if($criteriosSeleccion->sedeId!="" && $criteriosSeleccion->sedeId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'I','campo'=>'sede_id','valor'=>$criteriosSeleccion->sedeId]);
            }
            if(isset($criteriosSeleccion->areaId))
            {
                if($criteriosSeleccion->areaId!="" && $criteriosSeleccion->areaId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'I','campo'=>'area_id','valor'=>$criteriosSeleccion->areaId]);
            }
            if(isset($criteriosSeleccion->fechaInicial))
            {
                if($criteriosSeleccion->fechaInicial!="" && $criteriosSeleccion->fechaInicial!=null)
                    array_push($filtros,(object)['tipoDato'=>'date','operador'=>'>=','tabla'=>'I','campo'=>'fecha_inspeccion','valor'=>$criteriosSeleccion->fechaInicial]);
            }
            if(isset($criteriosSeleccion->fechaFinal))
            {
                if($criteriosSeleccion->fechaFinal!="" && $criteriosSeleccion->fechaFinal!=null)
                    array_push($filtros,(object)['tipoDato'=>'date','operador'=>'<=','tabla'=>'I','campo'=>'fecha_inspeccion','valor'=>$criteriosSeleccion->fechaFinal]);
            }
            $where = $this->where($filtros);
        }
        //$where = $this->where($filtros);
        
        $consulta = "SELECT A.id, A.nombre  nombre, count(I.id) valor " .
            " FROM inspecciones I " .
            "   INNER JOIN sedes S ON S.id = I.sede_id " .
            "   INNER JOIN areas A ON A.id = I.area_id " .
            "   INNER JOIN empresas E ON E.id = S.empresa_id " .
            $where .
            "GROUP BY A.id, A.nombre "  .
            "ORDER BY A.nombre";
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($this->bind_param($sentencia, $filtros))
                {
                    if($sentencia->execute())
                    {
                        if ($sentencia->bind_result($id, $nombre,$valor))
                        {
                            while($row = $sentencia->fetch())
                            {
                                $registro= (object) [
                                    'id' =>  $id,
                                    'nombre' => $nombre,
                                    'valor' => $valor
                                ];
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
    
    public function consultarAnos()
    {
        $resultado = new Resultado();
        $registros = array();
        
        
        $consulta = "SELECT YEAR(fecha_inspeccion) id, YEAR(fecha_inspeccion) nombre " .
            " FROM inspecciones I " .
            "GROUP BY YEAR(fecha_inspeccion) "  .
            "ORDER BY YEAR(fecha_inspeccion)";
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
//                 if($this->bind_param($sentencia, $filtros))
//                 {
                    if($sentencia->execute())
                    {
                        if ($sentencia->bind_result($id,$nombre))
                        {
                            while($row = $sentencia->fetch())
                            {
                                $registro= (object) [
                                    'id' =>  $id,
                                    'nombre' => $nombre
                                ];
                                array_push($registros,$registro);
                            }
                            $resultado->valor = $registros;
                        }
                        else
                            $resultado->mensajeError = "Falló el enlace del resultado";
                    }
                    else
                        $resultado->mensajeError = "Falló la ejecución (" . $this->conexion->errno . ") " . $this->conexion->error;
//                 }
//                 else
//                     $resultado->mensajeError = "Falló el enlace de parámetros";
            }
            else
                $resultado->mensajeError = "Falló la preparación: (" . $this->conexion->errno . ") " . $this->conexion->error;
                return $resultado;
    }
    
    public function consultarInspeccionesInspector($usuario,$criteriosSeleccion)
    {
        $resultado = new Resultado();
        $registros = array();
        
        $filtros = array();
        $where="";
        
        if($criteriosSeleccion!=null)
        {
            if(isset($criteriosSeleccion->empresaId))
            {
                if($criteriosSeleccion->empresaId!="" && $criteriosSeleccion->empresaId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'S','campo'=>'empresa_id','valor'=>$criteriosSeleccion->empresaId]);
            }
            if(isset($criteriosSeleccion->sedeId))
            {
                if($criteriosSeleccion->sedeId!="" && $criteriosSeleccion->sedeId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'I','campo'=>'sede_id','valor'=>$criteriosSeleccion->sedeId]);
            }
            if(isset($criteriosSeleccion->areaId))
            {
                if($criteriosSeleccion->areaId!="" && $criteriosSeleccion->areaId!=null)
                    array_push($filtros,(object)['tipoDato'=>'int','tabla'=>'I','campo'=>'area_id','valor'=>$criteriosSeleccion->areaId]);
            }
            if(isset($criteriosSeleccion->fechaInicial))
            {
                if($criteriosSeleccion->fechaInicial!="" && $criteriosSeleccion->fechaInicial!=null)
                    array_push($filtros,(object)['tipoDato'=>'date','operador'=>'>=','tabla'=>'I','campo'=>'fecha_inspeccion','valor'=>$criteriosSeleccion->fechaInicial]);
            }
            if(isset($criteriosSeleccion->fechaFinal))
            {
                if($criteriosSeleccion->fechaFinal!="" && $criteriosSeleccion->fechaFinal!=null)
                    array_push($filtros,(object)['tipoDato'=>'date','operador'=>'<=','tabla'=>'I','campo'=>'fecha_inspeccion','valor'=>$criteriosSeleccion->fechaFinal]);
            }
            $where = $this->where($filtros);
        }
        //$where = $this->where($filtros);
        
        $consulta = "SELECT U.id, CONCAT(U.nombre,' ', U.apellido)  nombre, count(I.id) valor " .
            " FROM inspecciones I " .
            "   INNER JOIN sedes S ON S.id = I.sede_id " .
            "   INNER JOIN usuarios U ON U.id = I.inspector_id " .
            "   INNER JOIN empresas E ON E.id = S.empresa_id " .
            $where .
            "GROUP BY U.id, U.nombre "  .
            "ORDER BY U.nombre";
            
            if($sentencia = $this->conexion->prepare($consulta))
            {
                if($this->bind_param($sentencia, $filtros))
                {
                    if($sentencia->execute())
                    {
                        if ($sentencia->bind_result($id, $nombre,$valor))
                        {
                            while($row = $sentencia->fetch())
                            {
                                $registro= (object) [
                                    'id' =>  $id,
                                    'nombre' => $nombre,
                                    'valor' => $valor
                                ];
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
    
    
    private function consultarPuntos1($inspeccionId)
    {
       
        $resultado = new Resultado();
        $puntos = array();
        $consulta = "SELECT punto_inspeccion_id, RTRIM(PI.descripcion)descripcion, RTRIM(resultado) resultado, IFNULL(RTRIM(observaciones),'') observaciones " .
            "FROM inspecciones_puntos IP " .
            "   INNER JOIN puntos_inspeccion PI ON IP.punto_inspeccion_id = PI.id " .
            " WHERE inspeccion_id  = ? AND punto_inspeccion_id>=1 AND punto_inspeccion_id <= 10 ".
            "ORDER BY punto_inspeccion_id";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$inspeccionId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($puntoInspeccionId, $descripcion, $resultadoPunto, $observaciones))
                    {
                       
                        while($sentencia->fetch())
                        {
                          
                            $punto= (object) [
                                'id' =>  $puntoInspeccionId,
                                'descripcion' =>  $descripcion,
                                'resultado' => $resultadoPunto,
                                'observaciones' => $observaciones,
                            ];
                            array_push($puntos,$punto);
                        }
                        $resultado->valor = $puntos;
                        
                        $sentencia->close();
                        
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
    
    private function consultarPuntos2($inspeccionId)
    {
        
        $resultado = new Resultado();
        $puntos = array();
        $consulta = "SELECT punto_inspeccion_id, RTRIM(PI.descripcion)descripcion, RTRIM(resultado) resultado, IFNULL(RTRIM(observaciones),'') observaciones " .
            "FROM inspecciones_puntos IP " .
            "   INNER JOIN puntos_inspeccion PI ON IP.punto_inspeccion_id = PI.id " .
            " WHERE inspeccion_id  = ? AND punto_inspeccion_id>=11 ".
            "ORDER BY punto_inspeccion_id";
        if($sentencia = $this->conexion->prepare($consulta))
        {
            if($sentencia->bind_param("i",$inspeccionId))
            {
                if($sentencia->execute())
                {
                    if ($sentencia->bind_result($puntoInspeccionId, $descripcion, $resultadoPunto, $observaciones))
                    {
                        
                        while($sentencia->fetch())
                        {
                            
                            $punto= (object) [
                                'id' =>  $puntoInspeccionId,
                                'descripcion' =>  $descripcion,
                                'resultado' => $resultadoPunto,
                                'observaciones' => $observaciones,
                            ];
                            array_push($puntos,$punto);
                        }
                        $resultado->valor = $puntos;
                        
                        $sentencia->close();
                        
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

