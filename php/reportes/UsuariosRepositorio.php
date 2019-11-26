<?php
namespace php\repositorios;
class UsuariosRepositorio
{
    protected $conexion;
    protected $consultaBase;
    public function __construct($conexion)
    {
        $this->conexion = $conexion;
        $this->consultaBase = "SELECT U.Us_id, Us_mail,  `Us_contrasena` , Emp_nombre, Emp_apellido_paterno, Em_id, Es_id, Ep_id, Ea_id, up_opcion, us_fecha_alta, emp_estatus, Emp_celular, Emp_tel_empresa_ext " .
					"FROM Usuarios U " .
					"INNER JOIN Empleado E ON U.Emp_id = E.Emp_id " .
					"INNER JOIN Usuario_permiso P ON P.Us_id = U.Us_id"; 

    }
    
    public function generarInserts($limite, $nuevoId)
    {
		echo "\n/*Usuarios*/";
		$consulta = $this->consultaBase . " WHERE U.Us_id>=$limite";
        if($sentencia = $this->conexion->prepare($consulta))
        {
			if($sentencia->execute())
			{
				if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido, $empresaId, $sedeId, $puestoId, $areaId, $tipoUsuarioId, $fechaAlta, $estatus, $telefono, $extension  ))
				{
					while($row = $sentencia->fetch())
					{
						$id= $nuevoId;
						echo "\n";
						$estatusNumero = $estatus=="A"?1:0;
						$tipoUsuarioN = 0;
						if($tipoUsuarioId==1)
							$tipoUsuarioN = 1;
						else if($tipoUsuarioId==2)
							$tipoUsuarioN = 2;
						else if($tipoUsuarioId==3)
							$tipoUsuarioN = 4;
						else if($tipoUsuarioId==4)
							$tipoUsuarioN = 5;
						
					   echo 'INSERT INTO usuarios(id, nombre_usuario, contrasena, nombre, apellido, empresa_id, sede_id, puesto_id, area_id, tipo_usuario_id, telefono, extension, fecha_alta, fecha_modificacion, estatus, permiso_saha) ' .
								'VALUES('.$id.',"'.$nombreUsuario.'","'.$contrasena.'","'.utf8_decode($nombre).'","'.utf8_decode($apellido).'",'.$empresaId.','.$sedeId.','.$puestoId.','.$areaId.','.$tipoUsuarioN.',"'.$telefono.'","'.$extension.'","' .$fechaAlta.'",NOW(),'.$estatusNumero.',1);';
								
						$nuevoId++;		
					}
				}
				else
					echo "nFalló el enlace del resultado.";
			}
			else
				echo "nFalló la ejecución";
        }
        else
            echo "\nFalló la preparación";
    }
	
	 public function generarUpdates($limite)
    {
		echo "\n/*Usuarios*/";
		
		$consulta = $this->consultaBase . " WHERE U.Us_id <=$limite";
        if($sentencia = $this->conexion->prepare($consulta))
        {
			if($sentencia->execute())
			{
				if ($sentencia->bind_result($id, $nombreUsuario, $contrasena, $nombre, $apellido, $empresaId, $sedeId, $puestoId, $areaId, $tipoUsuarioId, $fechaAlta, $estatus, $telefono, $extension  ))
				{
					while($row = $sentencia->fetch())
					{
						echo "\n";
						
						
					    $query= "UPDATE usuarios SET nombre_usuario = '$nombreUsuario',contrasena = '$contrasena',	nombre='$nombre', apellido='$apellido', telefono='$telefono', extension='$extension', fecha_modificacion = NOW()	WHERE id = $id;";
								
								
						echo $query;		
					}
				}
				else
					echo "nFalló el enlace del resultado.";
			}
			else
				echo "nFalló la ejecución";
        }
        else
            echo "\nFalló la preparación";
    }
}

