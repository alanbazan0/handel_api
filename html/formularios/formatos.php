<?php
    $origin = "*";
    if(isset($_SERVER['HTTP_ORIGIN']))
        $origin =$_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Credentials: true');
?>
<div class='modal fade' id='modalAlta' tabindex='-1' role='dialog' aria-labelledby='scrollmodalLabel' aria-hidden='true'>
  <div class='modal-dialog modal-lg' role='document'> 
      <div class='modal-content'>
          <div class='modal-header'>
              <h5 class='modal-title' id='scrollmodalLabel'>Proceso</h5>
              <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                  <span aria-hidden='true'>&times;</span>
              </button>
          </div>
          <div class='modal-body'>
              <form id='formulario' action='#'  method='post'>
               <div class='form-group'>
                      <div>
                          <label class='control-label mb-1'>Empresa</label>
                          <select id='empresaIdSelect' name='empresaIdSelect' onchange="vista.cambiarEmpresa();" class='form-control'></select>
                      </div>
                  </div>
                  <!-- <div class='form-group'>
                      <div>
                          <label class='control-label mb-1'>Sede</label>
                          <select id='sedeIdSelect' name='sedeIdSelect' class='form-control'></select>
                      </div>
                  </div> -->
                 
                  <div class='form-group'>
                      <div>
                          <label class='control-label mb-1'>Nombre</label>
                          <input  id='nombreInput' name='nombreInput' type='text' class='form-control'>
                      </div>
                  </div>
                  <div class='form-group'>
                      <div>
                          <label class='control-label mb-1'>Sección en manual</label>
                          <input  id='rutaArchivoInput' placeholder="" name='rutaArchivoInput' type='text' class='form-control'>
                      </div>
                  </div>
                  
                  <div class='form-group'>
                      <div>
                          <label class='control-label mb-1'>Aplica en certificación</label>
                           <div class="checkbox">
                              <label>
                                <input type="checkbox" id="oeaCheck"> OEA
                              </label>
                            </div>
                            <div class="checkbox">
                              <label>
                                <input type="checkbox" id="ctpatCheck"> CTPAT
                              </label>
                            </div>
                            <div class="checkbox">
                              <label>
                                <input type="checkbox" id="wrapCheck"> WRAP
                              </label>
                            </div>
                            <div class="checkbox">
                              <label>
                                <input type="checkbox" id="ipmCheck"> IPM
                              </label>
                            </div>
                         </div>
                  </div>
<!--                   <div class='form-group'> -->
<!--                       <div> -->
<!--                           <label class='control-label mb-1'>Descripción</label> -->
<!--                           <input  id='descripcionInput' name='descripcionInput' type='text' class='form-control'> -->
<!--                       </div> -->
<!--                   </div> -->
<!--                   <div class='form-group'> -->
<!--                       <div> -->
<!--                           <label class='control-label mb-1'>Ruta archivo</label> -->
<!--                           <input  id='rutaArchivoInput' placeholder="http://" name='rutaArchivoInput' type='text' class='form-control'> -->
<!--                       </div> -->
<!--                   </div> -->

 <div class="form-group" style='position:relative; z-index:99999' >
                             	<label class="control-label mb-1">Usuarios</label>
                              	<select class='form-control selectpicker' data-placeholder='' style='margin:0px;height:40px;width:100%' id='usuariosCompartirSelect' multiple  data-campo="usuarios"></select>
                 </div>
                 <div class="form-group">
						<label class="control-label mb-1">Activo</label> <label
							class="switch switch-3d switch-success mr-3"> <input
							id="estatusRadio" name="estatus" type="checkbox"
							class="switch-input" checked="true"> <span
							class="switch-label"></span> <span class="switch-handle"></span>
						</label>
						
				</div>
				
			<!--  	<div class="form-group">
    				<div>
						<button id='adjuntarArchivoButton' type="button" class='btn btn-primary pull-let' onclick="$('#file').trigger('click')"><i class='fas fa-paperclip'></i> Adjuntar archivo</button>
                       <input type="file" id="file"  name="file" style='display:none' onchange='vista.cambiarLogo(this);' />
    				</div> 
				</div> 
				<div class="form-group">
					<div class="col-sm-12 text-center">
                        <img id="logoImage" src="php/logos_empresas/default.png" alt="Logo" class="img-responsive img-thumbnail w-25" style='width:200px' onclick="$('#file').trigger('click')"  />
                   </div>
              	</div> -->
              </form>
          </div>
          <div class='modal-footer'>
              <button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancelar</button>
              <button  id='guardarButton' type='submit' class='btn btn-primary' >Guardar</button>
          </div>
      </div>
  </div>
</div>