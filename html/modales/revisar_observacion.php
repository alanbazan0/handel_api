<?php
    $origin = "*";
    if(isset($_SERVER['HTTP_ORIGIN']))
        $origin =$_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Credentials: true');
?>
<div class="modal fade" id="observacionModal" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document" style='width: 80%;'> 
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="scrollmodalLabel">Revisar observación</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				
					<div class="form-group">
						<div>
							<label for="procesoObservacionLabel" class="control-label">Proceso</label>
							<span id="procesoObservacionLabel" class="" style='display:block;font-size:13px;'></span>
						</div>
					</div>
    					
    					
    				<div class="form-group">
						<div>
							<label for="observacionLabel" class="control-label">Observación</label>
							<span id="observacionLabel" class="" style='display:block;font-size:13px;'></span>
						</div>
					</div>
					
						
					<div class="form-group" id="estatusValidacionObservacionDiv">
    						<div>
    							<label for="estatusValidacionObservacionIcono" class="control-label mb-1">Estado de solicitud</label>
    							<i id='estatusValidacionObservacionIcono'></i>
    							<span id="estatusValidacionObservacionLabel" class="mb-2 " style='font-size:13px;'></span>
							</div>
					</div>
					
				
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
				<button id="verificacionObservacionButton"  class='btn btn-warning float-right'><i class='far fa-pause-circle'></i> En verificación</button>
					<button id="autorizadoObservacionButton"   class='btn btn-success float-right'><i class='far fa-check-circle'></i> Autorizado</button>
					<button id="rechazadoObservacionButton"  class='btn btn-danger float-right'><i class='far fa-times-circle'></i> Rechazado</button>
					<button id="respondioObservacionButton"   class='btn btn-primary float-right'><i class='far fa-smile-wink'></i> Respondió</button>
			</div>
		</div>
	</div>
</div>