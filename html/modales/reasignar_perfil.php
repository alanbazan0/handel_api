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
				<h5 class="modal-title" id="scrollmodalLabel">Reasignar perfil</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				
				<div class="box-body" >
					<form id='reporteFormulario'>
						<span class="control-label mb-1">Criterios seleccionados</span>
    					<div class="form-group">
        						<div>
        							<label for="empresaInputReasignar" class="control-label mb-1">Empresa</label>
        							<input name="empresaInputReasignar" id="empresaInputReasignar" disabled  class="form-control campo" ></input>
        						</div>
    					</div>
    					<div class="form-group">
        						<div>
        							<label for="sedeInputReasignar" class="control-label mb-1">Sede</label>
        							<input name="sedeInputReasignar" id="sedeInputReasignar" disabled class="form-control campo" ></input>
        						</div>
    					</div>
    					<div class="form-group">
        						<div>
        							<label for="departamentoInputReasignar" class="control-label mb-1">Departamento</label>
        							<input name="departamentoInputReasignar" id="departamentoInputReasignar"  disabled class="form-control campo" ></input>
        						</div>
    					</div>
    					<div class="form-group">
        						<div>
        							<label for="perfilInputReasignar" class="control-label mb-1">Perfil</label>
        							<input name="perfilInputReasignar" id="perfilInputReasignar"  disabled class="form-control campo" ></input>        						</div>
    					</div>
    					<span class="control-label mb-1">Usuarios que cumplen los criterios: </span><span id='usuariosReaginarSpan'></span>
    					<div class="form-group">
        						<div>
        							<label for="perfilNuevoSelectReasignar" class="control-label mb-1">Reasginar perfil</label>
        							<select name="perfilNuevoSelectReasignar" id="perfilNuevoSelectReasignar"  class="form-control campo" ><option>Cargando...</option></select>
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
 				<button id="imprimirButton" type="submit" class="btn btn-primary" >Reasignar</button> 
			</div>
		</div>
	</div>
</div>