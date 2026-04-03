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
              <h5 class='modal-title' id='scrollmodalLabel'>Asignar proceso a usuario</h5>
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
                   <hr style='border-top: 1px solid #3c8dbc;'>
                   <label class="control-label text-primary" style='text-align:center'><i class="fa fa-user"></i> USUARIO</label>
                  <div class='form-group'>
                      <div>
                          <label class='control-label mb-1'>Sede</label>
                          <select id='sedeIdSelectUsuario' name='sedeIdSelectUsuario' onchange="vista.cambiarSede();" class='form-control'></select>
                      </div>
                  </div>
                    <div class='form-group'>
                      <div>
                          <label class='control-label mb-1'>Usuario</label>
                          <select id='usuarioIdSelect' name='usuarioIdSelect' class='form-control'></select>
                      </div>
                  </div>
                    <hr style='border-top: 1px solid #3c8dbc;'>
                   <label class="control-label text-primary" style='text-align:center'><i class="fa fa-file-text-o"></i> PROCESO</label>
                  <!--  <div class='form-group'>
                      <div>
                          <label class='control-label mb-1'>Sede</label>
                          <select id='sedeIdSelectProcedimiento' name='sedeIdSelectProcedimiento' onchange="vista.cambiarSedeProcedimiento();" class='form-control'></select>
                      </div>
                  </div> -->
                    <div class='form-group'>
                      <div>
                          <label class='control-label mb-1'>Proceso</label>
                          <select id='procedimientoIdSelect' name='procedimientoIdSelect'  class='form-control'></select>
                      </div>
                  </div>
<!--                  <div class="form-group"> -->
<!--                   		<label class="control-label mb-1">Limitar número de justificaciones permitidas</label> -->
<!--                  		<label class="switch switch-3d switch-success mr-3"> -->
<!--                          <input id="limitarJustificacionesSwitch" name="estatus" type="checkbox" class="switch-input"   onchange='vista.cambiarLimitarJustificaciones()'> -->
<!--                          <span class="switch-label"></span> -->
<!--                          <span class="switch-handle"></span> -->
<!--                        </label> -->
<!--                   </div>  	 -->
                  <div class='form-group' id='limiteJustificacionesGroup' style='display:none'>
                      <div>
                          <label class='control-label mb-1'>Límite de justificaciones</label>
                          <select id='limiteJustificacionesSelect' name='limiteJustificacionesSelect'  class='form-control'>
                          		<option>0</option>
								<option>1</option>
								<option>2</option>
								<option>3</option>
								<option>4</option>
								<option>5</option>
								<option>6</option>
								<option>7</option>
								<option>8</option>
								<option>9</option>
								<option>10</option>
								<option>11</option>
								<option>12</option>
							</select>
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
                 
              </form>
          </div>
          <div class='modal-footer'>
              <button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancelar</button>
              <button  id='guardarButton' type='submit' class='btn btn-primary' >Guardar</button>
          </div>
      </div>
  </div>
</div>
