<?php
    $origin = "*";
    if(isset($_SERVER['HTTP_ORIGIN']))
        $origin =$_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Credentials: true');
?>
<div class="modal fade" id="observacionModal" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document" > 
		<div class="modal-content" >
			<div class="modal-header">
				<h5 class="modal-title" id="scrollmodalLabel">Observación</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
    			<div class="box-body row">
                       	<form id="observacionFormulario" action="#"  method="post" class="col-lg-4 col-md-4 col-xs-12">
        				<div class="form-group">
    						<div>
    							<label for="procesoObservacionLabel" class="control-label">Proceso</label>
    							<span id="procesoObservacionLabel" class="" style='display:block;font-size:13px;'></span>
    						</div>
    					</div>
        				<div class="form-group">
        					<div>
                          	 	<label for="tipoObservacionSelect" class="control-label">Tipo</label>
                          	 	<select name="tipoObservacionSelect" id="tipoObservacionSelect" class="form-control" ><option value="">Cargando...</option></select>
                          	 </div> 
                      	</div>
                      	
                      	 <div class="form-group">
        					 <div>
        					 	<label for="seccionObservacionInput" class="control-label">Sección</label>
                                <input id="seccionObservacionInput" name='seccionObservacionInput' class="form-control" >
                            </div>
                     	 </div>
                      	
        				 <div class="form-group">
        					 <div>
        					 	<label for="descripcionObservacionInput" class="control-label">Descripción</label>
                                <textarea id="descripcionObservacionInput" name='descripcionObservacionInput' class="form-control" style="height: 150px;resize: none;" spellcheck="false"></textarea>
                            </div>
                     	 </div>
    				</form>
                      <div class="col-lg-8 col-md-8 col-xs-12" id="contenedorArchivo" style='display:none;'>
                       
                       	<div class="form-group" id='contenedorEvidenciaImage'>
                        	<div class='row'>
            					<div class="col-sm-12 text-center">
                                	<img id="evidenciaImage"  alt="Archivo" class="img-responsive img-thumbnail " style='' onclick="vista.vistaPrevia(this)"  />
                          	 	</div>
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
                      	<div class='row m-1'>
                          	<div id='officeDiv' class="form-group col-12" style='display:none;' >
                          		<iframe id='officeIframe' src=" frameborder="0" style='width:100%;height:500px;'>
        						</iframe>
                          	</div> 
                      	</div>
                      	<div class='row'>
        					<div class="col-sm-12 text-center mt-2">
                            	<button id="descargarButton"  type="button" class="btn btn-primary"><i class="fas fa-download"></i> Descargar</button>
                      	 	</div>
        				</div>
                       
                      </div>
            	</div>
            </div>
    			
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
				 <button  id="guardarObservacionButton" type="button" class="btn btn-primary">Aceptar</button>
			</div>
		</div>
	</div>
</div>