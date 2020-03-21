<?php
    $origin = "*";
    if(isset($_SERVER['HTTP_ORIGIN']))
        $origin =$_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Credentials: true');
?>
<div class="modal fade" id="modalAlta" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabel" aria-hidden="true">
	<div class="modal-dialog " role="document"> 
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="scrollmodalLabel">Plantilla</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form id="formularioAlta" action="#"  method="post">
					<div class="form-group">
						<div class="col-sm-12 text-center">
                            <img id="logoImageAlta"  alt="Logo" class="img-responsive img-thumbnail w-25" style='width:50px;display:none;' onclick="$('#fileAlta').trigger('click')"  />
                       </div>
                       <input type="file" id="fileAlta"  name="file" style='display:none' onchange='vista.cambiarLogoAlta(this);' />
                  	</div> 
		 			<div class="form-group">
                     	<div>
                            <label for="nombreInputAlta" class="control-label mb-1">Nombre</label>
                            <input id="nombreInputAlta" name="nombreInput" type="text" class="form-control" >
                         </div>
                     </div>      
                     <div class="form-group">
                     	<div>
                            <label for="descripcionInputAlta" class="control-label mb-1">Descripción</label>
                            <textarea  class="form-control"  name="descripcionInput"  style='height:70px;resize: none;' id="descripcionInputAlta"></textarea>
<!--                             <input id="descripcionInputAlta" name="descripcionInput" type="text" class="form-control" > -->
                         </div>
                     </div>     
                      <div class="form-group">
                     	<div>
                            <label for="fechaProgramadaInputAlta" class="control-label mb-1">Fecha de liberación</label>
                            <input id="fechaProgramadaInputAlta" name="fechaProgramadaInput" type="text" class="form-control" >
                         </div>
                     </div>    
                      <div class="form-group">
                      		<label class="control-label mb-1">Activo</label>
                     		<label class="switch switch-3d switch-success mr-3">
                             <input id="estatusRadioAlta" name="estatus" type="checkbox" class="switch-input" checked="true">
                             <span class="switch-label"></span>
                             <span class="switch-handle"></span>
                           </label>
                      </div>  
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
				<button id="guardarButtonAlta" type="submit" class="btn btn-primary" >Guardar</button>
			</div>
		</div>
	</div>
</div>
