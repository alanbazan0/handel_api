<?php
    $origin = "*";
    if(isset($_SERVER['HTTP_ORIGIN']))
        $origin =$_SERVER['HTTP_ORIGIN'];
    header('Access-Control-Allow-Origin: '.$origin);
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Credentials: true');
?>
<div class="modal fade" id="reporteModal" tabindex="-1" role="dialog" aria-labelledby="scrollmodalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document"> 
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="scrollmodalLabel">Diploma</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				
				<div class="box-body" >
					<form id='reporteFormulario'>
    					<div class="form-group">
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
        							<label for="empresaSelectDiploma" class="control-label mb-1">Empresa</label>
        							<select name="empresaSelectDiploma" id="empresaSelectDiploma" onchange="vista.cambiarEmpresaDiploma();" class="form-control campo" ><option>Cargando...</option></select>
        						</div>
    					</div>
    					<div class="form-group">
        						<div>
        							<label for="sedeSelectDiploma" class="control-label mb-1">Sede</label>
        							<select name="sedeSelectDiploma" id="sedeSelectDiploma" onchange="vista.cambiarSedeDiploma();" class="form-control campo" ><option>Cargando...</option></select>
        						</div>
    					</div>
    					<div class="form-group">
        						<div>
        							<label for="departamentoSelectDiploma" class="control-label mb-1">Departamento</label>
        							<select name="departamentoSelectDiploma" id="departamentoSelectDiploma" onchange="vista.cambiarDepartamentoDiploma();" class="form-control campo" ><option>Cargando...</option></select>
        						</div>
    					</div>
    					<div class="form-group">
        						<div>
        							<label for="usuarioSelectDiploma" class="control-label mb-1">Usuario</label>
        							<select name="usuarioSelectDiploma" id="usuarioSelectDiploma" class="form-control campo" ><option>Cargando...</option></select>
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
 				<button id="imprimirButton" type="submit" class="btn btn-primary" >Ver diploma</button> 
			</div>
		</div>
	</div>
</div>