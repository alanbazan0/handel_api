<?php
    $origin = "*";
    if(isset($_SERVER['HTTP_ORIGIN']))
        $origin =$_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Credentials: true');
?>
<div class="modal fade" id="xRayModal" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document" style='max-width: 90%;'> 
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="scrollmodalLabel">X-Ray</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
			<div style="  margin: 5px 5px 5px 5px;">
					 <div class="table-data__tool-left">
                    <div class="rs-select2--light" style="width:200px">
                    		<div>
                      	 	<label for="usuariosXRaySelect" class="control-label">Usuario</label>
                      	 	<select name="usuariosXRaySelect" id="usuariosXRaySelect" class="form-control" ><option value="">Cargando...</option></select>
                      	 </div> 
                    </div>
                    <button id="consultarRecomendacionesButton" class="btn btn-info">
                    <i class="fa fa-filter"></i> Filtrar</button>
                      <button id="exportarButton" class="btn btn-danger">  
             			 <i class="fa fa-file-pdf"></i> Exportar</button>
         			  <button id="correoButton" class="btn btn-primary"> 
           			 <i class="fa fa-envelope"></i> Enviar por correo</button> 
                     </div>
                </div>
			
				<div id='recomendacionesTabla'></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal" style="font-size:13px;">Cerrar</button>
			</div>
		</div>
	</div>
</div>