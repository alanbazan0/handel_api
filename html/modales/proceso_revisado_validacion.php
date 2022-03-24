<?php
    $origin = "*";
    if(isset($_SERVER['HTTP_ORIGIN']))
        $origin =$_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Credentials: true');
?>
<div class="modal fade" id="procesoModal" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabel" aria-hidden="true">
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
					
					<div class="form-group">
    						<div>
    							<label for="estatusRevisionIcono" class="control-label mb-1">Tipo de solicitud</label>
    							<i id='estatusRevisionIcono'></i>
    							<span id="estatusRevisionLabel" class="mb-2 " style='font-size:13px;'></span>
							</div>
					</div>
    					
					<div class="form-group" id="estatusValidacionDiv" style='display:none;'>
    						<div>
    							<label for="estatusValidacionIcono" class="control-label mb-1">Estado de solicitud</label>
    							<i id='estatusValidacionIcono'></i>
    							<span id="estatusValidacionLabel" class="mb-2 " style='font-size:13px;'></span>
							</div>
					</div>
    					
					<div >
    					<div class="form-group">
        						<div>
        							<label id='observacionesLabel' for="" class="control-label mb-1" style='display:none;'>Observaciones</label>
        							<div id='archivosProgress' class="progress" style='display:none'>
                                      <div id='archivosProgressBar' class="progress-bar" role="progressbar" style="width: 25%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">25%</div>
                                    </div>
                                   <button id='agregarObservacionButton' type="button" class='btn btn-primary pull-right'><i class='fas fa-plus'></i> Agregar</button>
        						</div>
        					</div>
    					 <div id='observacionesTabla'></div>
					</div>
				
			</div>
			<div i class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
				<div id="validacionFooter" style='display:none'>
				<button id="verificacionButton"  class='btn btn-warning float-right'><i class='far fa-pause-circle'></i> En verificación</button>
				<button id="autorizadoButton"   class='btn btn-success float-right'><i class='far fa-check-circle'></i> Autorizado</button>
				<button id="rechazadoButton"  class='btn btn-danger float-right'><i class='far fa-times-circle'></i> Rechazado</button>
				<button id="respondioButton"   class='btn btn-primary float-right'><i class='far fa-smile-wink'></i> Respondió</button>
				</div>
			</div>
		</div>
	</div>
</div>