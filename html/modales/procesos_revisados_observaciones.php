<?php
    $origin = "*";
    if(isset($_SERVER['HTTP_ORIGIN']))
        $origin =$_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Credentials: true');
?>
<div class="modal fade" id="observacionesModal" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document" style='width: 85%;'> 
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="scrollmodalLabel">Observaciones</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				
					<div class="form-group">
						<div>
							<label for="procesoLabel" class="control-label">Proceso</label>
							<span id="procesoLabel" class="" style='display:block;font-size:13px;'></span>
						</div>
					</div>
    					
					<div >
    					<div class="form-group">
        						<div>
        							<label for="" class="control-label mb-1">Observaciones</label>
        							<div id='archivosProgress' class="progress" style='display:none'>
                                      <div id='archivosProgressBar' class="progress-bar" role="progressbar" style="width: 25%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">25%</div>
                                    </div>
        							<button id='agregarObservacionButton' type="button" class='btn btn-primary pull-right'><i class='fas fa-plus'></i> Agregar</button>
                                   
        						</div>
        					</div>
    					 <div id='observacionesTabla'></div>
					</div>
				
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
				<button id="guardarObservacionesButton" type="submit" style="display:none" class="btn btn-primary" >Guardar</button>
			</div>
		</div>
	</div>
</div>