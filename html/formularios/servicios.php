<?php
$origin = "*";
if(isset($_SERVER['HTTP_ORIGIN']))
    $origin =$_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Credentials: true');
    ?>
<div class="modal fade" id="modalAlta" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document"> 
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="scrollmodalLabel">Servicios</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form id="formulario" action="#" method="post">
					<div class="form-group">
						<div>
							<label for="nombreInput" class="control-label mb-1">Nombre</label> 
							<input id="nombreInput" name="nombreInput" class="form-control" type="text" maxlength="50" /> 
						</div>
					</div>
					<div class="form-group">
						<label class="control-label mb-1">Activo</label> <label
							class="switch switch-3d switch-success mr-3"> <input
							id="estatusRadio" name="estatus" type="checkbox"
							class="switch-input" checked="true"> <span
							class="switch-label"></span> <span class="switch-handle"></span>
						</label>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
				<button id="guardarButton" type="submit" class="btn btn-primary" >Guardar</button>
			</div>
		</div>
	</div>
</div>