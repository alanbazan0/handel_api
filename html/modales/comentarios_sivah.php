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
				<h6 class="modal-title" id="scrollmodalLabel">Comentarios</h6>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
			
    			<div class="box-body">
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
    					
				</div>
				
				<div class="box-body chat" id="chatbox">
	
				</div>
            	<!-- /.chat -->
            	<div class="box-footer">
                	  <div class="input-group">
                		<input id="comentarioEvidenciaInput" class="form-control" placeholder="Ingrese un comentario...">
                		 <div class="input-group-append">
                		  <button id="enviarComentarioButton" type="button" class="btn btn-success"><i class="fa fa-send"></i></button>
                		</div>
            	 	 </div>
            	 	 
<!--                     <div class="input-group mb-3"> -->
<!--                       <input type="text" class="form-control" placeholder="Recipient's username" aria-label="Recipient's username" aria-describedby="basic-addon2"> -->
<!--                       <div class="input-group-append"> -->
<!-- <!--                         <span class="input-group-text" id="basic-addon2">@example.com</span> --> 
<!--                         <button id="enviarComentarioButton" type="button" class="btn btn-success"><i class="fa fa-send"></i></button> -->
<!--                       </div> -->
<!--                     </div> -->
            	</div>
				
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
<!-- 				<button id="guardarButton" type="submit" class="btn btn-primary" >Guardar</button> -->
			</div>
		</div>
	</div>
</div>