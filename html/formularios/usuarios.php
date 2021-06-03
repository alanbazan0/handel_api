<?php
    $origin = "*";
    if(isset($_SERVER['HTTP_ORIGIN']))
        $origin =$_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Credentials: true');
?>
<div class="modal fade" id="modalAlta" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document"> 
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="scrollmodalLabel">Usuario</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form id="formulario" action="#"  method="post">
					<div class="form-group">
						<div>
							<label for="tipoUsuarioSelect" class="control-label mb-1">Tipo de usuario</label> 
							<select name="tipoUsuarioSelect" id="tipoUsuarioSelect" class="form-control" data-toggle='tooltip' data-placement='bottom' title='' onchange="vista.cambiarTipoUsuario();"></select>
						</div>
					</div>
					<div id="nombreUsuarioDiv" class="form-group">
						<div>
							<label for="nombreUsuarioInput" class="control-label mb-1">Nombre de usuario</label> 
							<input id="nombreUsuarioInput" name="nombreUsuarioInput" type="text" class="form-control">
						</div>
					</div>
					<div id="contrasenaDiv" class="form-group">
						<div>
							<label for="contrasenaInput" class="control-label mb-1">Contraseña</label>
							<input id="contrasenaInput" name="contrasenaInput" type="text" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<div>
							<label for="nombreInput" class="control-label mb-1">Nombre</label>
							<input id="nombreInput" name="nombreInput" type="text" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<div>
							<label for="apellidoInput" class="control-label mb-1">Apellido</label>
							<input id="apellidoInput" name="apellidoInput" type="text" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<div>
							<label for="numeroEmpleadoInput" class="control-label mb-1">Número de empleado</label>
							<input id="numeroEmpleadoInput" name="numeroEmpleadoInput" type="text" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<div>
							<label for="empresaSelect" class="control-label mb-1">Empresa</label>
							<select name="empresaSelect" id="empresaSelect" class="form-control" onchange="vista.cambiarEmpresa();"></select>
						</div>
					</div>
					<div class="form-group">
						<div>
							<label for="sedeSelect" class="control-label mb-1">Sede</label> 
							<select name="sedeSelect" id="sedeSelect" class="form-control" onchange="vista.cambiarSede();"></select>
						</div>
					</div>
					<div class="form-group">
						<div>
							<label for="puestoSelect" class="control-label mb-1">Puesto</label>
							<select name="puestoSelect" id="puestoSelect" class="form-control"></select>
						</div>
					</div>
					<div class="form-group">
						<div>
							<label for="departamentoSelect" class="control-label mb-1">Departamento</label> 
							<select name="departamentoSelect" id="departamentoSelect" class="form-control"></select>
						</div>
					</div>
					<div class="form-group">
						<div>
							<label for="areaSelect" class="control-label mb-1">Area</label> 
							<select name="areaSelect" id="areaSelect" class="form-control"></select>
						</div>
					</div>
					
					<div class="form-group">
						<div>
							<label for="supervisor1Select" class="control-label mb-1">Supervisor 1</label> 
							<select name="supervisor1Select" id="supervisor1Select" class="form-control"></select>
						</div>
					</div>
					<div class="form-group">
						<div>
							<label for="supervisor2Select" class="control-label mb-1">Supervisor 2</label> 
							<select name="supervisor2Select" id="supervisor2Select" class="form-control"></select>
						</div>
					</div>
					<div class="form-group">
						<div>
							<label for="supervisor3Select" class="control-label mb-1">Supervisor 3</label> 
							<select name="supervisor3Select" id="supervisor3Select" class="form-control"></select>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label mb-1">Acceso a SAHA</label> <label
							class="switch switch-3d switch-success mr-3"> <input
							id="permisoSAHARadio" name="permisoSAHA" type="checkbox"
							class="switch-input" > <span
							class="switch-label"></span> <span class="switch-handle"></span>
						</label>
					</div>
					<div class="form-group">
						<label class="control-label mb-1">Acceso a SIVAH</label> <label
							class="switch switch-3d switch-success mr-3"> <input
							id="permisoSIVAHRadio" name="permisoSIVAH" type="checkbox"
							class="switch-input" > <span
							class="switch-label"></span> <span class="switch-handle"></span>
						</label>
					</div>
					<div class="form-group">
						<label class="control-label mb-1">Acceso a 10 y 7</label> <label
							class="switch switch-3d switch-success mr-3"> <input
							id="permiso10y7Radio" name="permiso10y7" type="checkbox"
							class="switch-input" > <span
							class="switch-label"></span> <span class="switch-handle"></span>
						</label>
					</div>
					<div class="form-group">
						<label class="control-label mb-1">Acceso a CAVI</label> <label
							class="switch switch-3d switch-success mr-3"> <input
							id="permisoCAVIRadio" name="permisoCAVI" type="checkbox"   onchange="vista.cambiarpermisoCAVI();"
							class="switch-input" > <span
							class="switch-label"></span> <span class="switch-handle"></span>
						</label>
					</div>
					<div id='perfilGroup' style='display:none'>
    					<div class="form-group" >
    						<div>
    							<label for="perfilSelect" class="control-label mb-1">Perfil</label> 
    							<select name="perfilSelect" id="perfilSelect" class="form-control"></select>
    						</div>
    					</div>
    					<div class="form-group">
    							<label class="control-label mb-1">Coordinador CAVI</label> <label
    							class="switch switch-3d switch-success mr-3"> <input
    							id="recursosHumanosRadio" name="recursosHumanos" type="checkbox"
    							class="switch-input"> <span
    							class="switch-label"></span> <span class="switch-handle"></span>
    						</label>
    					</div>
					</div>
					<div class="form-group">
						<label class="control-label mb-1">Activo</label> <label
							class="switch switch-3d switch-success mr-3"> <input
							id="estatusRadio" name="estatus" type="checkbox"
							class="switch-input" checked="true"> <span
							class="switch-label"></span> <span class="switch-handle"></span>
						</label>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
				<button  id="guardarButton" type="submit" class="btn btn-primary" >Guardar</button>
			</div>
		</div>
	</div>
</div>