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
		<div class="modal-content" >
			<div class="modal-header">
				<h5 class="modal-title" id="scrollmodalLabel">Mensaje</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				
<!-- 				<div class="box-body chat" id="chatbox" > -->
	
<!-- 				</div> -->

				<div class="box box-widget">
					<div class="box-header with-border">
						<div id='usuarioDiv' class="user-block">
							
						</div>
						<!-- /.user-block -->
						<div class="box-tools">
							
						</div>
						<!-- /.box-tools -->
					</div>
					<!-- /.box-header -->
					<div class="box-body">
						<div id='mensajeDiv'>
						
						</div>
						<span id="numeroComentariosSpan"  class="pull-right text-muted"></span>
					</div>
					<!-- /.box-body -->
					<div id="comentariosDiv" class="box-footer box-comments">
						
					</div>
					<!-- /.box-footer -->
					<div class="box-footer">
<!-- 						<form action="#" method="post"> -->
							<img id="fotoPerfilComentarioImg" class="img-responsive img-circle img-sm"
								src="" alt="Alt Text">
							<!-- .img-push is used to add margin to elements next to floating images -->
							<div class="img-push">
<!-- 								<input  id="comentarioInput"  type="text" class="form-control input-sm" -->
<!-- 									placeholder="Ingrese un comentario"> -->
                                     <div class="input-group">
                                    			<input id="comentarioInput" class="form-control" placeholder="Ingrese un comentario...">
                                    	
                                    		<div class="input-group-btn">
                                    		  <button id="enviarComentarioButton" type="button" class="btn btn-success"><i class="fa fa-send"></i></button>
                                    		</div>
                                	 	 </div>
							</div>
<!-- 						</form> -->
					</div>
					<!-- /.box-footer -->
				</div>

				
				
            	<!-- /.chat -->
<!--             	<div class="box-footer"> -->
<!--                 	  <div class="input-group"> -->
<!--                 	  	<div> -->
<!--                 			<input id="comentarioInput" class="form-control" placeholder="Ingrese un comentario..."> -->
<!--                 		</div> -->
                	
<!--                 		<div class="input-group-btn"> -->
<!--                 		  <button id="enviarComentarioButton" type="button" class="btn btn-success"><i class="fa fa-send"></i></button> -->
<!--                 		</div> -->
<!--             	 	 </div> -->
<!--             	</div> -->
            	
				
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
<!-- 				<button id="guardarButton" type="submit" class="btn btn-primary" >Guardar</button> -->
			</div>
		</div>
	</div>
</div>