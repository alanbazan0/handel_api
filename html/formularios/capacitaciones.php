<?php
    $origin = "*";
    if(isset($_SERVER['HTTP_ORIGIN']))
        $origin =$_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Credentials: true');
?>
<div class="modal fade" id="modalAlta" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg " role="document"> 
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="scrollmodalLabel">Capacitación</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form id="formularioAlta" action="#"  method="post">
				<div class="row">
					<div class='col-lg-6'>
						<div class="form-group">
						<div class="col-sm-12 text-center">
                            <img id="logoImageAlta"  alt="Logo" class="img-responsive img-thumbnail w-25" style='width:300px;display:none;cursor:pointer' onclick="$('#fileAlta').trigger('click')"  />
                       </div>
                       <input type="file" id="fileAlta"  name="file" style='display:none' onchange='vista.cambiarLogoAlta(this);' />
                  		</div> 
					</div>
					<div class='col-lg-6'>
						
    		 			<div class="form-group">
                         	<div>
                                <label for="tituloInputAlta" class="control-label mb-1">Título</label>
                                <input id="tituloInputAlta" name="tituloInputAlta" type="text" class="form-control" >
                             </div>
                         </div>      
                         <div class="form-group">
                         	<div>
                                <label for="descripcionInputAlta" class="control-label mb-1">Descripción</label>
                                <textarea  class="form-control"  name="descripcionInput"  style='height:70px;resize: none;' id="descripcionInputAlta"></textarea>
    <!--                             <input id="descripcionInputAlta" name="descripcionInput" type="text" class="form-control" > -->
                             </div>
                         </div>   
    					</div>
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
