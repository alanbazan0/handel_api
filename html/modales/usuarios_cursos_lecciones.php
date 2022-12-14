<?php
    $origin = "*";
    if(isset($_SERVER['HTTP_ORIGIN']))
        $origin =$_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Credentials: true');
?>
<div class="modal fade" id="leccionesModal" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document" style='max-width: 95%;'> 
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="scrollmodalLabel">Lecciones</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				
				<div class="box-body" >
				<div class="form-group">
    						<div>
    							<label for="usuarioLabel" class="control-label mb-1">Usuario</label>
    							<span id="usuarioLabel" class="mb-2 " style='display:block;font-size:13px;'></span>
							</div>
					</div>
					<div class="form-group">
    						<div>
    							<label for="capacitacionLabel" class="control-label mb-1">Capacitación</label>
    							<span id="capacitacionLabel" class="mb-2 " style='display:block;font-size:13px;'></span>
							</div>
					</div>
				</div>
            	<!-- /.chat -->
            	<div class="box-footer">
                	 <div id='leccionesTabla'></div>
            	</div>
				
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
				
			</div>
		</div>
	</div>
</div>