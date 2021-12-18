<?php
    $origin = "*";
    if(isset($_SERVER['HTTP_ORIGIN']))
        $origin =$_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Credentials: true');
?>
<div class="modal fade" id="avancesModal" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document" style='max-width: 95%;'> 
		<div class="modal-content">
			<div class="modal-header">
				<h6 class="modal-title" id="scrollmodalLabel">Avance</h6>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				
				<div class="box-body" >
					<div class="form-group">
    						<div>
    							<label for="accionAvanceLabel" class="control-label mb-1">Acción</label>
    							<span id="accionAvanceLabel" class="mb-2 " style='display:block;font-size:13px;'></span>
							</div>
					</div>
					<div class="form-group">
					
    						<div>
    							
    							<i id='estatusValidacionIcono'></i>
    							<label id="estatusValidacionLabel" class="mb-2 " style='font-size:13px;'></label>
							</div>
					</div>
					<button id="validarRecomendacionButton" style='display:none' class='btn btn-success float-right'><i class='fas fa-check-double'></i> Validar</button>
					<button id="registrarAvanceButton"  style='display:none' class='btn btn-primary float-right'><i class='fa fa-plus'></i> Registrar avance</button>
				</div>
            	<!-- /.chat -->
            	<div class="box-footer">
                	 <div id='avancesTabla'></div>
            	</div>
				
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
				
			</div>
		</div>
	</div>
</div>