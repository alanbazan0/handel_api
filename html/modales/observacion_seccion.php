<?php
    $origin = "*";
    if(isset($_SERVER['HTTP_ORIGIN']))
        $origin =$_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Credentials: true');
?>
<div class="modal fade" id="observacionModal" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document" style='width: 75%;'> 
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
							<label for="seccionObservacionLabel" class="control-label">Sección</label>
							<span id="seccionObservacionLabel" class="" style='display:block;font-size:13px;'></span>
						</div>
				</div>
    					
					
				<form id="observacionFormulario" action="#"  method="post">
					<div class="form-group">
						<div>
							<label for="hallazgoInput" class="control-label mb-1">Hallazgo</label>
							<textarea  class="form-control  campo"  name="hallazgoInput"  style='height:50px;resize: none;' id="hallazgoInput" ></textarea>
						</div>
					</div>
					<div class="form-group">
						<div>
							<label for="recomendacionInput" class="control-label mb-1">Recomendación</label>
							<textarea  class="form-control campo"  name="recomendacionInput"  style='height:50px;resize: none;' id="recomendacionInput" ></textarea>
						</div>
					</div>
					<div class="form-group">
						<div>
							<label for="responsableSelect" class="control-label mb-1">Responsable</label>
							<select name="responsableSelect" id="responsableSelect" class="form-control campo"></select>
						</div>
					</div>
					<div class="form-group">
						<div>
							<input class="respuesta" id="reporteCheck" type="checkbox" style="display:inline-block;width:40px">
							<label>Reporte</label>
							<input class="respuesta" id="notificacionCheck" type="checkbox" style="display:inline-block;width:40px">
							<label>Notificación</label>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal" style="font-size:13px;">Cancelar</button>
				<button id="guardarObservacionButton" type="submit" class="btn btn-primary"  style="font-size:13px;">Aceptar</button>
			</div>
		</div>
	</div>
</div>