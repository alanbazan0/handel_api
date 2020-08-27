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
				<div class="box-header">
                  <i class="ion ion-clipboard"></i>
    
                  <h3 class="box-title">Tarea</h3>
    
                </div>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<ul class='sortable todo-list ui-sortable' style='background:white; '>
 					<li id='formulario_tareaDiv'>
 					</li>
				</ul>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
<!-- 				<button id="guardarButton" type="submit" class="btn btn-primary" >Guardar</button> -->
			</div>
		</div>
	</div>
</div>