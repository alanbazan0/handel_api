<?php
$origin = "*";
if(isset($_SERVER['HTTP_ORIGIN']))
    $origin =$_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Credentials: true');
    ?>
<div class="modal fade" id="reporteModal" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document"> 
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="scrollmodalLabel">Reporte Mensual</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				
				<div class="box-body" >
					<form id='reporteFormulario'>
    					<div class="form-group" style='display:none;'>
                            <label>Fechas:</label>
                            <div class="input-group" style='width:100%'>
                              <button type="button" class="btn btn-default" style='width:100%' id="daterange-btn">
                                <span>
                                  <i class="fa fa-calendar"></i> Seleccionar
                                </span>
                                <i class="fa fa-caret-down"></i>
                              </button>
                            </div>
                          </div>
    					<div class="form-group">
    						<div>
    							<label for="empresaSelectReporte" class="control-label mb-1">Empresa</label>
    							<select name="empresaSelectReporte" id="empresaSelectReporte" onchange="vista.cambiarEmpresaReporte();" class="form-control campo" ></select>
    						</div>
    					</div>
    					<div class="form-group" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab" tabindex="0">
                   			<button type="button" class="btn btn-primary" onclick="vista.adjuntarFotosSimulacros();"><i class="fa fa-picture-o"></i> Adjuntar imagen simulacros </button>
            				<div class="row">
                				<input type="file" id="fileSimulacros" multiple style="display:none" accept=".jpg, .jpeg, .png"/>
                				
                					<div class='container-xl'>
                                        <div class='row' id='fotosSimulacros'>
                                        
                                        </div>
                                    </div>
            				</div>
                      
                      </div>
                      <div class="form-group" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab" tabindex="0">
                     	 <button type="button" class="btn btn-primary" onclick="vista.adjuntarFotosNovedades();"><i class="fa fa-picture-o"></i> Adjuntar imagen novedades </button>
            				<div class="row">
                				<input type="file" id="fileNovedades" multiple style="display:none" accept=".jpg, .jpeg, .png"/>
                				
                					<div class='container-xl'>
                                        <div class='row' id='fotosNovedades'>
                                        
                                        </div>
                                    </div>
            				</div>
                      </div>
    					<div class="form-group">
    						<div>
    							<label for="actividadesPreviasInput" class="control-label mb-1">Actividades previas</label>
    							<textarea id="actividadesPreviasInput" class="form-control campo" style='height:130px;resize: none;'></textarea>
    						</div>
    					</div>
    					<div class="form-group">
    						<div>
    							<label for="actividadesProximasInput" class="control-label mb-1">Actividades proximas</label>
    							<textarea id="actividadesProximasInput" class="form-control campo" style='height:130px;resize: none;'></textarea>
    						</div>
    					</div>
    					<div class="form-group">
    						<div>
    							<label for="novedadesInput" class="control-label mb-1">Novedades</label>
    							<textarea id="novedadesInput" class="form-control campo" style='height:130px;resize: none;'></textarea>
    						</div>
    					</div>
    					
    					
					</form>
				</div>
		            	<!-- /.chat -->
            	<div class="box-footer">
                
            	</div>
				
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
 				<button id="imprimirButton" type="submit" class="btn btn-primary" >Ver reporte</button> 
			</div>
		</div>
	</div>
</div>