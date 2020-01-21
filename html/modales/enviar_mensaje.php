<?php
    $origin = "*";
    if(isset($_SERVER['HTTP_ORIGIN']))
        $origin =$_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Credentials: true');
?>
<div class="modal fade" id="modalAlta" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document" style='width:80%'> 
		<div class="modal-content" >
			<div class="modal-header">
				<h5 class="modal-title" id="scrollmodalLabel">Enviar mensaje</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
			<form id="formulario" action="#"  method="post">
				<div class="form-group col-sm-6">
					<div>
                  	 	<label for="empresaSelectMensaje" class="control-label">Empresa</label>
                  	 	<select name="empresaSelectMensaje" id="empresaSelectMensaje" class="form-control" onchange="vista.cambiarEmpresaMensaje();"><option value="">Cargando...</option></select>
                  	 </div> 
              	</div>
              	<div class="form-group col-sm-6">
					<div>
                  	 	<label for="sedeSelectMensaje" class="control-label">Sede</label>
                  	 	<select name="sedeSelectMensaje" id="sedeSelectMensaje" class="form-control" onchange="vista.cambiarSedeMensaje();"><option value="">Cargando...</option></select>
                  	 </div> 
              	</div>
              	<div class="form-group col-sm-6">
					<div>
                  	 	<label for="departamentoSelectMensaje" class="control-label mb-1">Departamento</label>
                  	 	<select name="departamentoSelectMensaje" id="departamentoSelectMensaje" class="form-control" onchange="vista.cambiarDepartamentoMensaje();"><option value="">Cargando...</option></select>
                  	 </div> 
              	</div>
              	<div class="form-group col-sm-6">
					<div>
                  	 	<label for="usuarSelectMensaje" class="control-label mb-1">Usuario</label>
                  	 	<select name="usuarSelectMensaje" id="usuarioSelectMensaje" class="form-control"><option value="">Cargando...</option></select>
                  	 </div> 
              	</div>
              	
				<div class="form-group">
					<div>
                		<input id="asuntoInput" name="asuntoInput" class="form-control" placeholder="Asunto:">
                	</div>
              	</div>
				 <div class="form-group">
					 <div>
                        <textarea id="mensajeInput" name='mensajeInput' class="form-control" style="height: 300px" spellcheck="false">
                        </textarea>
                    </div>
             	 </div>
			
            	<!-- /.chat -->
<!--             	<div class="box-footer"> -->
<!--                 	  <div class="input-group"> -->
<!--                 	  	<div> -->
<!--                 			<input id="mensajeInput" class="form-control" placeholder="Ingrese un mensaje..."> -->
<!--                 		</div> -->
                	
<!--                 		<div class="input-group-btn"> -->
<!--                 		  <button id="enviarButton" type="button" class="btn btn-success"><i class="fa fa-send"></i></button> -->
<!--                 		</div> -->
<!--             	 	 </div> -->
<!--             	</div> -->
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
				 <button  id="guardarButton" type="button" class="btn btn-primary"><i class="fa fa-send"></i> Enviar</button>
<!-- 				<button id="guardarButton" type="submit" class="btn btn-primary" >Guardar</button> -->
			</div>
		</div>
	</div>
</div>