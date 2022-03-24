<?php
    $origin = "*";
    if(isset($_SERVER['HTTP_ORIGIN']))
        $origin =$_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Credentials: true');
?>
<div class="modal fade" id="validacionComentarioModal" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabel" aria-hidden="true">
	<div class="modal-dialog " role="document"> 
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="scrollmodalLabel">Cambiar estatus</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="box-header with-border" id="headerBox" style='display:none'>
						
						<!-- /.box-tools -->
				</div>
				
				<div class="box-body chat" >
					<div class="form-group">
						<div>
							<label for="procesoValidacionLabel" class="control-label">Proceso</label>
							<span id="procesoValidacionLabel" class="" style='display:block;font-size:13px;'></span>
						</div>
					</div>
					
					<div class="form-group" id="seccionValidacionObservacionDiv" style="display:none">
						<div>
							<label for="seccionValidacionObservacionInput" class="control-label">Sección</label>
							<input id="seccionValidacionObservacionInput" class="form-control" placeholder="">
						</div>
					</div>
					
					 <div class="form-group"  id="descripcionValidacionDiv" style="display:none">
    					 <div>
    					 	<label for="descripcionValidacionInput" class="control-label">Descripción</label>
                            <textarea id="descripcionValidacionInput" name='descripcionValidacionInput' class="form-control" style="height: 150px;resize: none;" spellcheck="false"></textarea>
                        </div>
                 	 </div>
	
					<div class="form-group">
                	  	<div>
                	  		<label for="comentarioValidacionInput" class="control-label">Comentario</label>	
                			<input id="comentarioValidacionInput" class="form-control" placeholder="">
                		</div>
            	 	 </div>
				</div>
            	
				
			</div>
			<div class="modal-footer">
				<button id="validacionComentarioButton" type="button" class="btn btn-secondary" data-dismiss="modal"></button>
<!-- 				<button id="guardarButton" type="submit" class="btn btn-primary" >Guardar</button> -->
			</div>
		</div>
	</div>
</div>