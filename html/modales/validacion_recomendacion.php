<?php
    $origin = "*";
    if(isset($_SERVER['HTTP_ORIGIN']))
        $origin =$_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Credentials: true');
?>
<div class="modal fade" id="validacionModal" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document"> 
		<div class="modal-content">
			<div class="modal-header">
				<h6 class="modal-title" id="scrollmodalLabel">Validar</h6>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				
				<div class="box-body" >
					<form id='formulario'>
    					<div class="form-group">
        						<div>
        							<label for="accionAvanceValidacionLabel" class="control-label mb-1">Acción</label>
        							<span id="accionAvanceValidacionLabel" class="mb-2 " style='display:block;font-size:13px;'></span>
    					</div>
    					</div>
    					<div class="form-group">
        						<div>
        							<label for="estatusValidacionSelect" class="control-label mb-1">Estatus de validación</label>
        							<select name="estatusValidacionSelect" id="estatusValidacionSelect" class="form-control campo" >
        								
        								
        							</select>
        						</div>
        					</div>
    					<div id='comentariosPredefinidosDiv'>
    					</div>
    					
    					<div class="form-group">
    						<div>
    							<label for="comentariosValidacionInput" class="control-label mb-1">Comentarios de Handel</label>
    							<textarea id="comentariosValidacionInput" name="comentariosValidacionInput" class="form-control" rows="3" style="resize:none;height:100px"></textarea>
    						</div>
    					</div>
					</form>
				</div>
            	<!-- /.chat -->
            	<div class="box-footer">
                
            	</div>
				
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
 				<button id="guardarValidacionButton" type="submit" class="btn btn-primary" >Guardar</button> 
			</div>
		</div>
	</div>
</div>