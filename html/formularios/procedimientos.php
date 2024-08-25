<?php
$origin = '*';
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
             <!--  <h5 class='modal-title' id='scrollmodalLabel'></h5> -->
              <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                  <span aria-hidden='true'>&times;</span>
              </button>
          </div>
          <div class='modal-body'>
           <div class='row'>
                    <section class='col-lg-12 connectedSortable'>
                    	<div class='nav-tabs-custom'>
                        	<!-- Tabs within a box -->
                        	<ul id='minutasNav' class='nav nav-tabs pull-left'>
                              <li class='active'><a href='#evidencia' data-toggle='tab'>Evidencia</a></li>
                        	  <li ><a href='#certificaciones' data-toggle='tab'>Certificaciones</a></li>
                        	</ul>
                        	<div class='tab-content'>
                              <div class='chart tab-pane active' id='evidencia' style='position: relative; '>
                               <form id='formulario' action='#'  method='post'>
                                   <div class='form-group'>
                                          <div>
                                              <label class='control-label mb-1'>Empresa</label>
                                              <select id='empresaIdSelect' name='empresaIdSelect' onchange="vista.cambiarEmpresa();" class='form-control'></select>
                                          </div>
                                      </div>
                                      <div class='form-group'>
                                          <div>
                                              <label class='control-label mb-1'>Sede</label>
                                              <select id='sedeIdSelect' name='sedeIdSelect' class='form-control'></select>
                                          </div>
                                      </div>
                                      <div class='form-group'>
                                          <div>
                                              <label class='control-label mb-1'>Código</label>
                                              <input  id='codigoInput' name='codigoInput' type='text' class='form-control'>
                                          </div>
                                      </div>
                                      <div class='form-group'>
                                          <div>
                                              <label class='control-label mb-1'>Nombre</label>
                                              <input  id='nombreInput' name='nombreInput' type='text' class='form-control'>
                                          </div>
                                      </div>
                                      <div class='form-group'>
                                          <div>
                                              <label class='control-label mb-1'>Descripción</label>
                                              <input  id='descripcionInput' name='descripcionInput' type='text' class='form-control'>
                                          </div>
                                      </div>
                                      <div class='form-group'>
                                          <div>
                                              <label class='control-label mb-1'>Ruta archivo</label>
                                              <input  id='rutaArchivoInput' placeholder="http://" name='rutaArchivoInput' type='text' class='form-control'>
                                          </div>
                                      </div>
                                     <div class="form-group">
                    						<label class="control-label mb-1">Activo</label> <label
                    							class="switch switch-3d switch-success mr-3"> <input
                    							id="estatusRadio" name="estatus" type="checkbox"
                    							class="switch-input" checked="true"> <span
                    							class="switch-label"></span> <span class="switch-handle"></span>
                    						</label>
                    					</div>
                                  </form>
                              </div>
                              
                              <div class='chart tab-pane' id='certificaciones' style='position: relative; '>
                              		<ul id="certificacionesUl">
                              		</ul>
                              </div>
                           </div>
                        </div>
                    </section>
                </div>
             
          </div>
          <div class='modal-footer'>
              <button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancelar</button>
              <button  id='guardarButton' type='submit' class='btn btn-primary' >Guardar</button>
          </div>
      </div>
  </div>
</div>
