<?php
    $origin = "*";
    if(isset($_SERVER['HTTP_ORIGIN']))
        $origin =$_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Credentials: true');
?>
<div class="modal fade" id="modalAlta" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document" style='width:95%'> 
		<div class="modal-content">
			<div class="modal-header">
				<h5 id='tituloModalAlta' class="modal-title" id="scrollmodalLabel">Subir evidencia</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				
                        
			
				<form id="formulario" action="#"  method="post">
					<div class="form-group" style='display:none'>
						<div>
							<label for="usuarioProcedimientoId" class="control-label mb-1">Usuario procedimiento id</label>
							<input id="usuarioProcedimientoId" name="usuarioProcedimientoId" type="text" class="form-control" disabled>
						</div>
					</div>
					<div class="form-group">
						<div>
							<label for="usuarioNombreInput" class="control-label mb-1">Nombre</label>
							<input id="usuarioNombreInput" name="usuarioNombreInput" type="text" class="form-control" disabled>
						</div>
					</div>
					<div class="form-group">
						<div>
							<label for="empresaNombreInput" class="control-label mb-1">Empresa</label>
							<input id="empresaNombreInput" name="empresaNombreInput" type="text" class="form-control" disabled>
						</div>
					</div>
					<div class="form-group">
						<div>
							<label for="prodecimientoNombreInput" class="control-label mb-1">Evidencia</label>
							<input id="prodecimientoNombreInput" name="prodecimientoNombreInput" type="text" class="form-control" disabled>
						</div>
					</div>
					
					<div class="form-group">
						<label class="control-label mb-1">Se realizó la actividad</label> <label
							class="switch switch-3d switch-success mr-3"> <input
							id="realizoActividadCheck" name="estatus" type="checkbox" onchange='vista.cambiarRealizoActividad()'
							class="switch-input" checked="true"> <span
							class="switch-label"></span> <span class="switch-handle"></span>
						</label>
					</div>
					<div class="form-group" id='contenedorEvidenciaImage'>
						<div id='botonesDiv' class='row text-center m-2'>
							<button id="borrarArchivoEvidenciaButton"  type="button" style='display:none' class="btn btn-danger"><i class="fa fa-unlink"></i> Borrar</button>
							<button id="adjuntarArchivoEvidenciaButton" type="button" onclick="$('#file').trigger('click')" class="btn btn-success"><i class="fa fa-paperclip"></i> Adjuntar</button>
						</div>
						<div class='row'>
							<div class="col-sm-12 text-center">
                            	<img id="evidenciaImage"  alt="Evidencia" class="img-responsive img-thumbnail w-100" style='width:100%' onclick="vista.vistaPrevia(this)"  />
                      	 	</div>
						</div>
						
                       <div>
                       		<input type="file" id="file"  name="file" style='display:none' onchange='vista.cambiarArchivoEvidencia(this);' />
                       </div>
                  	</div> 
                  	<div id='pdf' class="form-group" >
                  		<div id="pdf-contents" style='text-align:center'>
                    		<canvas id="pdf-canvas" style='width:600px;' width="600"></canvas>
                    	</div>
                    	
                    	<div id='botonesPDF' class='row text-center m-2' style='display:none'>
							<button id="pdf-prev"  type="button" class="btn btn-info"><i class="fa fa-arrow-left"></i> Anterior</button>
							<button id="pdf-next" type="button" class="btn btn-info">Siguiente <i class="fa fa-arrow-right"></i></button>
						</div>
                  	</div> 
                  	
					<div class="form-group">
						<div>
							<label for="justificacionSelect" class="control-label mb-1">No se adjunto por</label> 
							<select name="justificacionSelect" id="justificacionSelect" class="form-control" disabled></select>
						</div>
					</div>
					
					<div class="form-group">
						<div>
							<label for="comentariosInput" class="control-label mb-1">Comentarios</label>
							<textarea id="comentariosInput" name="comentariosInput" class="form-control" rows="3" style="resize:none;height:100px"></textarea>
						</div>
					</div>

					
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
				<button id="guardarButton" type="submit" class="btn btn-primary" ><i class='fa fa-upload'></i> Subir</button>
			</div>
		</div>
	</div>
</div>