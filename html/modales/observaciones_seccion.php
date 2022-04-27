<?php
    $origin = "*";
    if(isset($_SERVER['HTTP_ORIGIN']))
        $origin =$_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Credentials: true');
?>
<div class="modal fade" id="modalAlta" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document" style='max-width: 90%;'> 
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
							<label for="seccionLabel" class="control-label">Sección</label>
							<span id="seccionLabel" class="" style='display:block;font-size:13px;'></span>
						</div>
					</div>
    					
					<div >
    					<div class="form-group">
        						<div align="right">
        							
        							<button id='agregarObservacionButton' type="button" class='btn btn-primary pull-right'><i class='fas fa-plus'></i> Agregar</button>
                                   
        						</div>
        					</div>
    					 <div id='observacionesTabla'></div>
				</div>
				
			</div>
			<div class="modal-footer">
				<!-- <button type="button" class="btn btn-secondary" data-dismiss="modal" style="font-size:13px;">Cancelar</button> -->
				<button id="guardarButton" type="submit" class="btn btn-primary"  style="font-size:13px;">Cerrar</button>
			</div>
		</div>
	</div>
</div>