<?php
    $origin = "*";
    if(isset($_SERVER['HTTP_ORIGIN']))
        $origin =$_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Credentials: true');
?>
<div class="modal fade" id="observacionModal" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document" > 
		<div class="modal-content"  >
			<div class="modal-header">
				<h5 class="modal-title" id="scrollmodalLabel">Observación</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				
			
    			<form id="observacionFormulario" action="#"  method="post">
    				<div class="form-group">
						<div>
							<label for="procesoObservacionLabel" class="control-label">Proceso</label>
							<span id="procesoObservacionLabel" class="" style='display:block;font-size:13px;'></span>
						</div>
					</div>
    				<div class="form-group">
    					<div>
                      	 	<label for="tipoObservacionSelect" class="control-label">Tipo</label>
                      	 	<select name="tipoObservacionSelect" id="tipoObservacionSelect" class="form-control" ><option value="">Cargando...</option></select>
                      	 </div> 
                  	</div>
                  	
                  	 <div class="form-group">
    					 <div>
    					 	<label for="seccionObservacionInput" class="control-label">Sección</label>
                            <input id="seccionObservacionInput" name='seccionObservacionInput' class="form-control" >
                        </div>
                 	 </div>
                  	
    				 <div class="form-group">
    					 <div>
    					 	<label for="descripcionObservacionInput" class="control-label">Descripción</label>
                            <textarea id="descripcionObservacionInput" name='descripcionObservacionInput' class="form-control" style="height: 150px;resize: none;" spellcheck="false"></textarea>
                        </div>
                 	 </div>
<!--                  	 <div class="form-group"> -->
<!--                  	 	 <div> -->
<!--                      	 	<label  class="control-label">Archivo</label> -->
<!--                      	 	 <button  id="guardarObservacionButton" type="button" class="btn btn-success">Adjuntar</button> -->
<!--                  	  	 </div> -->
<!--                  	  </div> -->
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
				 <button  id="guardarObservacionButton" type="button" class="btn btn-primary">Aceptar</button>
<!-- 				<button id="guardarButton" type="submit" class="btn btn-primary" >Guardar</button> -->
			</div>
		</div>
	</div>
</div>