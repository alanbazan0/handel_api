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
				<h5 class="modal-title" id="scrollmodalLabel">Buenas prácticas</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form id="formulario" action="#"  method="post">
					<div class="form-group">
						<div>
							<label for="buenasPracticasInput" class="control-label mb-1">Coloca una línea por cada buena práctica</label>
							<textarea  class="form-control  campo"  name="buenasPracticasInput"  style='height:300px;resize: none;' id="buenasPracticasInput" ></textarea>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal" style="font-size:13px;">Cancelar</button>
				<button id="guardarButton" type="submit" class="btn btn-primary"  style="font-size:13px;">Aceptar</button>
			</div>
		</div>
	</div>
</div>