<?php
    $origin = "*";
    if(isset($_SERVER['HTTP_ORIGIN']))
        $origin =$_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Credentials: true');
?>
<div class="modal fade" id="archivoModal" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabel" aria-hidden="true">
	<div class="modal-dialog " role="document" style='max-width: 90%;'> 
		<div class="modal-content" >
			<div class="modal-header">
				<h6 class="modal-title" id="scrollmodalLabel">Archivo</h6>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="form-group" id='contenedorEvidenciaImage'>
                	<div class='row'>
    					<div class="col-sm-12 text-center">
                        	<img id="evidenciaImage"  alt="Evidencia" class="img-responsive img-thumbnail " style='' onclick="vista.vistaPrevia(this)"  />
                  	 	</div>
    				</div>
    				<div class='row'>
    					<div class="col-sm-12 text-center mt-2">
                        	<button id="descargarButton"  type="button" class="btn btn-primary"><i class="fas fa-download"></i> Descargar</button>
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
                  	
				
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
<!-- 				<button id="guardarButton" type="submit" class="btn btn-primary" >Guardar</button> -->
			</div>
		</div>
	</div>
</div>