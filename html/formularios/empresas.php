<?php
    $origin = "*";
    if(isset($_SERVER['HTTP_ORIGIN']))
        $origin =$_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Credentials: true');
?>
<div class="modal fade" id="modalAlta" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document" > 
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="scrollmodalLabel">Empresa</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form id="formulario" action="#"  method="post">
             		<div class="form-group">
						<div class="col-sm-12 text-center">
                            <img id="logoImage" src="php/logos_empresas/default.png" alt="Logo" class="img-responsive img-thumbnail w-25" style='width:200px' onclick="$('#file').trigger('click')"  />
                       </div>
                       <input type="file" id="file"  name="file" style='display:none' onchange='vista.cambiarLogo(this);' />
                  	</div> 
                     <div class="form-group">
                     	<div>
                            <label for="nombreInput" class="control-label mb-1">Nombre</label>
                            <input id="nombreInput" name="nombreInput" type="text" class="form-control" >
                         </div>
                      </div>       
                      <div class="form-group">
                      	<div>
                            <label for="nombreCortoInput" class="control-label mb-1" >Nombre corto</label>
                            <input id="nombreCortoInput" name="nombreCortoInput" type="text" class="form-control">
                          </div>
                      </div>         
                       <div class="form-group">
                      	 <div>
                            <label for="telefonoInput" class="control-label mb-1" >Teléfono</label>
                            <input id="telefonoInput" name="telefonoInput" type="text" class="form-control">
                           </div> 
                      </div>    
                      <div class="form-group">   
                       	<div>
                      	 	<label for="tipoEmpresaSelect" class="control-label mb-1">Tipo de empresa</label>
                      	 	<select name="tipoEmpresaSelect" id="tipoEmpresaSelect" class="form-control"></select>
                      	 </div> 
                      </div>   
                      <div class="form-group">
                      	<div>
                            <label for="direccionInput" class="control-label mb-1">Dirección</label>
                            <input id="direccionInput" name="direccionInput" type="text" class="form-control" aria-required="true" aria-invalid="false" >
                          </div> 
                      </div>   
                      <div class="form-group">   
                      	<div>
                      	 	<label for="paisSelect" class="control-label mb-1">Pais</label>
                      	 	<select name="paisSelect" id="paisSelect" class="form-control" onchange="vista.cambiarPais();"><option value="">Cargando...</option></select>
                      	 </div> 
                      </div> 
                      <div class="form-group">   
                      	 	<label for="estadoSelect" class="control-label mb-1">Estado</label>
                      	 	<select name="estadoSelect" id="estadoSelect" class="form-control"  onchange="vista.cambiarEstado();"><option value="">Cargando...</option></select>
                      </div> 
                      <div class="form-group">   
                     	 <div>
                      	 	<label for="ciudadSelect" class="control-label mb-1">Ciudad</label>
                      	 	<select name="ciudadSelect" id="ciudadSelect" class="form-control"><option value="">Cargando...</option></select>
                      	 </div> 
                      </div>    
                      <div class="form-group">   
                      	<div>
                      	 	<label for="corporativoSelect" class="control-label mb-1">Corporativo</label>
                      	 	<select name="corporativoSelect" id="corporativoSelect" class="form-control"></select>
                      	 </div> 
                      </div>   
                       <div class="form-group">   
                      	<div>
                      	 	<label for="administradorSelect" class="control-label mb-1">Resposable de validación de evidencias (SAHA)</label>
                      	 	<select name="administradorSelect" id="administradorSelect" class="form-control"></select>
                      	 </div> 
                      </div>  
                       <div class="form-group">   
                      	<div>
                      	 	<label for="perfilSelect" class="control-label mb-1">Capacitación para importaciones</label>
                      	 	<select name="perfilSelect" id="perfilSelect" class="form-control"></select>
                      	 </div> 
                      </div>  
                      <div class="form-group">
                      		<label class="control-label mb-1">Activo</label>
                     		<label class="switch switch-3d switch-success mr-3">
                             <input id="estatusRadio" name="estatus" type="checkbox" class="switch-input" checked="true">
                             <span class="switch-label"></span>
                             <span class="switch-handle"></span>
                           </label>
                      </div>  
        		</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
				<button id="guardarButton" type="submit" class="btn btn-primary" >Guardar</button>
			</div>
		</div>
	</div>
</div>