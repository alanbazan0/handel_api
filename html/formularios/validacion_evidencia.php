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
				<h5 class="modal-title" id="scrollmodalLabel">Validación de evidencia</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form id="formulario" action="#"  method="post">
					<div class="form-group" style='display:none'>
						<div>
							<label for="evidenciaIdInput" class="control-label mb-1">Usuario procedimiento id</label>
							<input id="evidenciaIdInput" name="evidenciaIdInput" type="text" class="form-control" disabled>
						</div>
					</div>
					<div class="form-group" style='display:none'>
						<div>
							<label for="usuarioProcedimientoId" class="control-label mb-1">Usuario procedimiento id</label>
							<input id="usuarioProcedimientoId" name="usuarioProcedimientoId" type="text" class="form-control" disabled>
						</div>
					</div>
					<div class="form-group">
						<div>
							<label for="usuarioNombreInput" class="control-label mb-1">Usuario</label>
							<input id="usuarioNombreInput" name="usuarioNombreInput" type="text" class="form-control" disabled>
						</div>
					</div>
					<div class="form-group">
						<div>
							<label for="fechaAltaInput" class="control-label mb-1">Fecha de subida</label>
							<input id="fechaAltaInput" name="fechaAltaInput" type="text" class="form-control" disabled>
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
							<label for="sedeNombreInput" class="control-label mb-1">Sede</label>
							<input id="sedeNombreInput" name="sedeNombreInput" type="text" class="form-control" disabled>
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
							class="switch-input" disabled> <span
							class="switch-label"></span> <span class="switch-handle"></span>
						</label>
					</div>
					<div class="form-group" id='contenedorEvidenciaImage' style='display:none'>
						<div class='row'>
							<div class="col-sm-12 text-center">
                            	<img id="evidenciaImage" src="images/tipos_archivo/vacio.png" alt="Evidencia" class="img-responsive img-thumbnail w-25" style='width:50%' onclick="vista.vistaPrevia(this)"  />
                      	 	</div>
						</div>
						
                       <div>
                       		<input type="file" id="file"  name="file" style='display:none' onchange='vista.cambiarArchivoEvidencia(this);' />
                       </div>
                       <div class='row text-center m-2'>
							<button id="descargarEvidenciaButton"  type="button" class="btn btn-success"><i class="fa fa-download"></i> Descargar</button>
						</div>
                  	</div> 
                  	<div id='pdf' class="form-group" >
                  		<div id="pdf-contents" style='text-align:center'>
                    		<canvas id="pdf-canvas" style='width:600px;' width="600"></canvas>
                    	</div>
                    	
                    	<div id='botonesPDF' class='row text-center m-2' style='display:none;'>
							<button id="pdf-prev"  type="button" class="btn btn-info"><i class="fa fa-arrow-left"></i> Anterior</button>
							<button id="pdf-next" type="button" class="btn btn-info">Siguiente <i class="fa fa-arrow-right"></i></button>
						</div>
                  	</div> 
					<div class="form-group hidden">
						<div>
							<label for="justificacionInput" class="control-label mb-1">No se adjunto por</label> 
							<input name="justificacionInput" id="justificacionSelect" class="form-control" disabled></input>
						</div>
					</div>
					
					
					<div class="form-group">
						<div>
							<label for="comentariosInput" class="control-label mb-1">Comentarios de usuario</label>
							<textarea id="comentariosInput" name="comentariosInput" class="form-control" rows="3" style="resize:none;height:100px" disabled></textarea>
						</div>
					</div>
					
					
					<div class="form-group">
						<label class="control-label mb-1">Validación de información</label> <label
							class="switch switch-3d switch-success mr-3"> <input
							id="validadaCheck" name="estatus" type="checkbox" 
							class="switch-input" > <span
							class="switch-label"></span> <span class="switch-handle"></span>
						</label>
					</div>
					
					
					<div id='comentariosPredefinidosDiv'>
					</div>
					
					<div class="form-group">
						<div>
							<label for="comentariosValidacionInput" class="control-label mb-1">Comentarios de Handel</label>
							<textarea id="comentariosValidacionInput" name="comentariosInput" class="form-control" rows="3" style="resize:none;height:100px"></textarea>
						</div>
					</div>

					
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
				<button id="guardarButton" type="submit" class="btn btn-primary" ><i class='fa fa-save'></i> Guardar</button>
			</div>
		</div>
	</div>
</div>