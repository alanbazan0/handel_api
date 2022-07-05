<?php
    $origin = "*";
    if(isset($_SERVER['HTTP_ORIGIN']))
        $origin =$_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Credentials: true');
?>
<div class="modal fade" id="correoXRayModal" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document" style='max-width: 78%;'> 
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="scrollmodalLabel">Enviar X-Ray por correo</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
					<div class="form-group">
    						<div>
    							<label for="usuarioXRay" class="control-label mb-1">Usuario</label>
							  	<span  id = "usuarioXRaySpan"></span>
                            
							</div>
					</div>
					<div class="form-group">
    						<div>
    							<label for="usuariosCorreoSelect" class="control-label mb-1">Para</label>
    							  	<select class='form-control selectpicker' data-placeholder='' style='margin:0px;height:100px;width:100%' id='usuariosCorreoSelect' multiple  data-campo="usuarios"></select>
                            
							</div>
					</div>
<!-- 					<div class="form-group"> -->
<!-- 					<div> -->
<!--                 		<input id="asuntoCorreoXRayInput" name=""asuntoCorreoXRayInput"" class="form-control" placeholder="Asunto:" value="XRay"> -->
<!--                 	</div> -->
<!--               	</div> -->
<!-- 				 <div class="form-group"> 
					 <div> 
                        <textarea id="mensajeInput" name='mensajeInput' class="form-control" style="height: 300px" spellcheck="false">
<!--                         </textarea> -->
<!--                     </div> -->
<!--              	 </div> -->
			</div>
			<div class="modal-footer">
			 <button id="enviarCorreoXRayButton"  type="button" class="btn btn-primary" style='display:none'><i class="fa fa-paper-plane" aria-hidden="true"></i> Enviar</button>
				<button  type="button" class="btn btn-secondary" data-dismiss="modal" style="font-size:13px;">Cerrar</button>
			</div>
		</div>
	</div>
</div>