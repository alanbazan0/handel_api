<?php
    $origin = "*";
    if(isset($_SERVER['HTTP_ORIGIN']))
        $origin =$_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Credentials: true');
?>
<div class="modal fade" id="archivosModal" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document"> 
		<div class="modal-content">
			<div class="modal-header">
				<h6 class="modal-title" id="scrollmodalLabel">Registro de avance</h6>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				
				<div class="box-body" >
					<form id="formulario" action="#"  method="post">
						<div class="form-group">
    						<div>
    							<label for="accionLabel" class="control-label mb-1">Acción</label>
    							<span id="accionLabel" class="mb-2 " style='display:block;font-size:13px;'></span>
							</div>
						</div>
    					<div class="form-group">
    						<div>
    							<label for="cumplimientoSelect" class="control-label mb-1">Cumplimiento</label>
    							<select name="cumplimientoSelect" id="cumplimientoSelect" class="form-control campo" >
    								<option value='0'>0%</option>
    								<option value='10'>10%</option>
    								<option value='20'>20%</option>
    								<option value='30'>30%</option>
    								<option value='40'>40%</option>
    								<option value='50'>50%</option>
    								<option value='60'>60%</option>
    								<option value='70'>70%</option>
    								<option value='80'>80%</option>
    								<option value='90'>90%</option>
    								<option value='100'>100%</option>
    							</select>
    						</div>
    					</div>
    					<div class="form-group">
    						<div>
    							<label for="comentarioInput" class="control-label mb-1">Comentario</label>
    							<textarea  class="form-control  campo"  name="comentarioInput"  style='height:80px;resize: none;' id="comentarioInput" ></textarea>
    						</div>
    					</div>
    					<div class="form-group">
    						<div>
    							<label for="comentariosInput" class="control-label mb-1">Archivos</label>
    						</div>
    					</div>
<!--     					<div class="form-check"> -->
<!--                             <input type="checkbox" class="form-check-input" id="ayudaCheckBox"> -->
<!--                             <label class="form-check-label" for="ayudaCheckBox"> Necesito ayuda con esta acción</label> -->
<!--                          </div> -->
					</form>
					 <div id='archivosTabla'></div>
				</div>
            	<!-- /.chat -->
<!--             	<div class="box-footer"> -->
<!--                 	 <div id='archivosTabla'></div> -->
<!--             	</div> -->
				
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
				<button id="guardarAvanceButton" type="submit" class="btn btn-primary" >Guardar</button>
			</div>
		</div>
	</div>
</div>