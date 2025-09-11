<?php
    $origin = "*";
    if(isset($_SERVER['HTTP_ORIGIN']))
        $origin =$_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Credentials: true');
?>
<div class="modal fade" id="modalAlta" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document" > 
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="scrollmodalLabel">Empresa</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class='nav-tabs-custom'>
					<!-- Tabs within a box -->
					<ul class='nav nav-tabs'>
						<li class='active'><a href='#informacion-general' data-toggle='tab'>Información general</a></li>
						<li><a href='#socio-comercial' data-toggle='tab'>Socio comercial</a></li>
					</ul>
					<form id="formulario" action="#"  method="post">
					<div class='tab-content no-padding'>
						
							<div class='chart tab-pane active' id='informacion-general' style='position: relative; '>
							
                         		<div class="form-group">
            						<div class="col-sm-12 text-center">
                                        <img id="logoImage" src="php/logos_empresas/default.png" alt="Logo" class="img-responsive img-thumbnail w-25" style='width:200px' onclick="$('#file').trigger('click')"  />
                                   </div>
                                   <input type="file" id="file"  name="file" style='display:none' onchange='vista.cambiarLogo(this);' />
                              	</div> 
                                 <div class="form-group">
                                 	<div>
                                        <label for="nombreInput" class="control-label mb-1">Nombre</label>
                                        <input id="nombreInput" name="nombreInput" type="text" class="form-control" >
                                     </div>
                                  </div>       
                                  <div class="form-group">
                                  	<div>
                                        <label for="nombreCortoInput" class="control-label mb-1" >Nombre corto</label>
                                        <input id="nombreCortoInput" name="nombreCortoInput" type="text" class="form-control">
                                      </div>
                                  </div>         
                                   <div class="form-group">
                                  	 <div>
                                        <label for="telefonoInput" class="control-label mb-1" >Teléfono</label>
                                        <input id="telefonoInput" name="telefonoInput" type="text" class="form-control">
                                       </div> 
                                  </div>    
                                  <div class="form-group">   
                                   	<div>
                                  	 	<label for="tipoEmpresaSelect" class="control-label mb-1">Tipo de empresa</label>
                                  	 	<select name="tipoEmpresaSelect" id="tipoEmpresaSelect" class="form-control"></select>
                                  	 </div> 
                                  </div>   
                                  <div class="form-group">
                                  	<div>
                                        <label for="direccionInput" class="control-label mb-1">Dirección</label>
                                        <input id="direccionInput" name="direccionInput" type="text" class="form-control" aria-required="true" aria-invalid="false" >
                                      </div> 
                                  </div>   
                                  <div class="form-group">   
                                  	<div>
                                  	 	<label for="paisSelect" class="control-label mb-1">Pais</label>
                                  	 	<select name="paisSelect" id="paisSelect" class="form-control" onchange="vista.cambiarPais();"><option value="">Cargando...</option></select>
                                  	 </div> 
                                  </div> 
                                  <div class="form-group">   
                                  	 	<label for="estadoSelect" class="control-label mb-1">Estado</label>
                                  	 	<select name="estadoSelect" id="estadoSelect" class="form-control"  onchange="vista.cambiarEstado();"><option value="">Cargando...</option></select>
                                  </div> 
                                  <div class="form-group">   
                                 	 <div>
                                  	 	<label for="ciudadSelect" class="control-label mb-1">Ciudad</label>
                                  	 	<select name="ciudadSelect" id="ciudadSelect" class="form-control"><option value="">Cargando...</option></select>
                                  	 </div> 
                                  </div>    
                                  <div class="form-group">   
                                  	<div>
                                  	 	<label for="corporativoSelect" class="control-label mb-1">Corporativo</label>
                                  	 	<select name="corporativoSelect" id="corporativoSelect" class="form-control"></select>
                                  	 </div> 
                                  </div>   
                                   <div class="form-group">   
                                  	<div>
                                  	 	<label for="administradorSelect" class="control-label mb-1">Resposable de validación de evidencias (SAHA)</label>
                                  	 	<select name="administradorSelect" id="administradorSelect" class="form-control"></select>
                                  	 </div> 
                                  </div>  
                                  <div class="form-group">   
                                  	<div>
                                  	 	<label for="administradorSIVAHSelect" class="control-label mb-1">Resposable de validación de evidencias (SIVAH)</label>
                                  	 	<select name="administradorSIVAHSelect" id="administradorSIVAHSelect" class="form-control"></select>
                                  	 </div> 
                                  </div> 
                                  
                                  
                                   <div class="form-group">   
                                  	<div>
                                  	 	<label for="perfilSelect" class="control-label mb-1">Capacitación para importaciones</label>
                                  	 	<select name="perfilSelect" id="perfilSelect" class="form-control"></select>
                                  	 </div> 
                                  </div>  
                                  
                                  <div class="form-group">   
                                  	<div>
                                  	 	<label for="mesRevisionProcesosSelect" class="control-label mb-1">Mes en que inicia revisión de procesos</label>
                                  	 	 <select id="mesRevisionProcesosSelect" class="form-control" name="mesRevisionProcesosSelect" tabindex="-1" aria-hidden="true" >
                                  	 	  <option value="">--Seleccione</option>
                                           <option value="1">Enero</option>
                                            <option value="2">Febrero</option>
                                            <option value="3">Marzo</option>
                                            <option value="4">Abril</option>
                                            <option value="5">Mayo</option>
                                            <option value="6">Junio</option>
                                            <option value="7">Julio</option>
                                            <option value="8">Agosto</option>
                                            <option value="9">Septiembre</option>
                                            <option value="10">Octubre</option>
                                            <option value="11">Noviembre</option>
                                            <option value="12">Diciembre</option>
                                        </select>
                                  	 </div> 
                                  </div>  
                                  <div class="form-group">   
                                  	<div>
                                  	 	<label for="administradorProcesosSelect" class="control-label mb-1">Resposable de revisión de procesos</label>
                                  	 	<select name="administradorProcesosSelect" id="administradorProcesosSelect" class="form-control"></select>
                                  	 </div> 
                                  </div> 
                                  <div class="form-group">
                                  	<div>
                                        <label for="calificacionMinimaInput" class="control-label mb-1">Calificación mínima para capacitaciones</label>
                                        <input id="calificacionMinimaInput" name="calificacionMinimaInput" type="text" class="form-control" aria-required="true" aria-invalid="false" >
                                      </div> 
                                  </div> 
                                 
            				       <div class='form-group'>
                                      <div>
                                          <label class='control-label mb-1'>Fecha de inicio de temporada</label>
                                          <input  id='fechaInicioTemporadaInput' name='fechaInicioTemporadaInput' type='text' class='form-control' >
                                      </div>
                              		</div>
                              		
                              		<div class="form-group">
                              		<div>
                                  	 	<label for="servicioSelect" class="control-label mb-1">Servicio contratado</label>
                                  	 	<select name="servicioSelect" id="servicioSelect" class="form-control"></select>
                                  	 </div> 
                                  </div>  
                                  
                                  <div class="form-group">
                              		<div>
                                  	 	<label for="minutaAnalisisRiesgoInput" class="control-label mb-1">Minuta de Análisis de Riesgo OEA</label>
                                  	 	<!-- <select name="minutaAnalisisRiesgoSelect" id="minutaAnalisisRiesgoSelect" class="form-control"></select> -->
                                  	 	<input type="text"  class='form-control' name="minutaAnalisisRiesgoInput" id="minutaAnalisisRiesgoInput"/>
                                  	 </div> 
                                  </div>  
                                  
                                   <div class="form-group">
                                  	  <div>
                                        <label for="tokensInput" class="control-label mb-1">Tokens</label>
                                        <input id="tokensInput" name="tokensInput" type="text" class="form-control" aria-required="true" aria-invalid="false" >
                                      </div> 
                                  </div>
                                  <div class="form-group">
                                  		<label class="control-label mb-1">Activo</label>
                                 		<label class="switch switch-3d switch-success mr-3">
                                         <input id="estatusRadio" name="estatus" type="checkbox" class="switch-input" checked="true">
                                         <span class="switch-label"></span>
                                         <span class="switch-handle"></span>
                                       </label>
                                  </div>  
                    		
							</div>
    						<div class='chart tab-pane' id='socio-comercial' style='position: relative;'>
    							<div class="form-group">
                                  		<label class="control-label mb-1">Socio comercial</label>
                                 		<label class="switch switch-3d switch-success mr-3">
                                         <input id="socioComercialRadio" name="estatus" type="checkbox" class="switch-input" >
                                         <span class="switch-label"></span>
                                         <span class="switch-handle"></span>
                                       </label>
                                  </div>  
                                  <div class="form-group">
                              		<div>
                                  	 	<label for="tipoSocioComercialSelect" class="control-label mb-1">Tipo de socio comercial</label>
                                  	 	<select name="tipoSocioComercialSelect" id="tipoSocioComercialSelect" class="form-control"></select>
                                  	 </div> 
                                  </div>  
							 	
                                  <div class="form-group">
                              		<div>
                              			<label for="empresaSelect" class="control-label mb-1">Empresas</label>
                              			<div  class="input-group" style='width:100%'>
    									    <select  id="empresaSelect" class="form-control"  multiple data-placeholder="Seleccione una o varias empresas">
    											<option value="">Cargando...</option>
    										</select>
    									</div>
									</div>
								 </div>
								 <div class="form-group">
                                  		<label class="control-label mb-1">Autoevaluación</label>
                                 		<label class="switch switch-3d switch-success mr-3">
                                         <input id="autoevaluacionRadio" name="estatus" type="checkbox" class="switch-input" >
                                         <span class="switch-label"></span>
                                         <span class="switch-handle"></span>
                                       </label>
                                  </div>  
                                  <div class="form-group">
                              		<div>
                                  	 	<label for="plantillaSelect" class="control-label mb-1">Plantilla</label>
                                  	 	<select name="plantillaSelect" id="plantillaSelect" class="form-control"></select>
                                  	 </div> 
                                  </div>  
                                 <div class="form-group">
                                  		<label class="control-label mb-1">Permitir que usuario externo llene la plantilla</label>
                                 		<label class="switch switch-3d switch-success mr-3">
                                         <input id="permitirUsuarioPlantillaSelectRadio" name="estatus" type="checkbox" class="switch-input" >
                                         <span class="switch-label"></span>
                                         <span class="switch-handle"></span>
                                       </label>
                                  </div>  
                                  
                                  
                                   
    						</div>
						

						</div>
					</form>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
				<button id="guardarButton" type="submit" class="btn btn-primary" >Guardar</button>
			</div>
		</div>
	</div>
</div>