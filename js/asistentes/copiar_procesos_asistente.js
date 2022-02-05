class CopiarProcesosAsistente
{
	constructor()
	{
		var date = new Date();
		this._modal = "copiarProcedimientosModal" + date.getTime();
		this._contexto = null;
		this._funcion = null;
		this._numeroRegistros = 0;
		this._desplegados = [];
		this._tamanoPagina = 100;
		this._paginacion = { paginaActual:0, tamanoPagina :this._tamanoPagina };
		this.iconoSeleccionado = null;
		this._registros = [];
		this._registrosAplicacion = [];
		this._registrosVersiones = [];
		this._registroCamposVersiones = [];
		this._registrosCheckCampos=[];
		this._registrosTemporal = [];
		this._registrosCheck = [];
		this._dependenciasHijo =[];
		this._banderaModal = 0;
		this._contadorCheck = 0;
		this._totalCheck = 0;
		this._campoDependenciaId = undefined;
		this._valorDependencia = undefined ;
//		this._botonanterior = null;
//		this._aplicacionIndicador ="";
//		this._versionIndicador ="";
		this._estatus = 1;
//		this._iconosModal = new IconosModal();
		this._colores = ["#8eb15a","#ac94ea","#27AE60","#C0392B","#5e8251","#568dd5","#f5ad5f","#935b1a","#568dd5","#fa6f57","#b79a74","#34495e","#e67e22","#ecf0f1","#bdc3c7","#95a5a6","#7f8c8d"];
		
		
		
	}
	
	mostrar(contexto, funcion)
	{
		this._contexto = contexto;
		this._funcion = funcion;
		if($("#"+this._modal).length ==0)
		{
			this.renderizarModal();
			
		}
		$("#"+this._modal).modal({backdrop: 'static', keyboard: false});
	}
	
	set numeroRegistros(numeroRegistros)
	{
		var _this = this;
		this._numeroRegistros = numeroRegistros;
		var numeroPaginas = Math.ceil(this._numeroRegistros /this._paginacion.tamanoPagina);

	}
	set banderaModal(valor){
		this._banderaModal = valor;
	}
	get banderaModal(){
		return this._banderaModal;
	}
	ocultar()
	{
		$("#"+this._modal).modal('hide');
		
	}
	
	set desplegados(desplegados)
	{
		this._desplegados = desplegados;
		this.renderizarAsistente();
		
		var indice = 0;
		this._desplegadoSeleccionado = this._desplegados[0];
		
		var card = $("#"+this._modal +"IconosDiv").find("div");
		card.removeClass("card-activo");
		$("#"+this._modal+"card"+indice).addClass("card-activo");

		this.renderizarPropiedades();
	}
	

	
	renderizarModal()
	{

		var html="";
		html+="<div  class='modal fade' id='"+this._modal +"' tabindex='-1' role='dialog' aria-labelledby='"+this._modal +"Titulo' aria-hidden='true'>";
		html+="	<div class='modal-dialog modal-dialog-scrollable modal-lg' role='document' >";
		html+="		<div class='modal-content'>";
		html+="			<div class='modal-header'>";
		html+="				<h5 class='modal-title' id='"+this._modal +"Titulo'>Copiar procesos</h5>";
		html+="				<button type='button' class='close' data-dismiss='modal' aria-label='Close'>";
		html+="					<span aria-hidden='true'>&times;</span>";
		html+="				</button>";
		html+="			</div>";
		html+="			<div id='"+this._modal +"body' class='modal-body'>";
		
		html+="			</div>";

		
		html+="		</div>";
		html+="	</div>";
		html+="</div>";
		$("body").append(html);
		
		var _this = this;
		
		
		$("#"+this._modal).on("show.bs.modal", function () 
		{
			_this.consultar();
		});

		
		$("#"+this._modal).on('hidden.bs.modal', function (e) 
		{ 
			$("#"+_this._modal).remove();
		});
		
	}
	
	renderizarAsistente()
	{
		var html="";
		html+="<div  id='formularioWizard'>";
		html+= this.agregarStepsPredeterminados();
		
		html+="</div>";
		
		$("#"+this._modal +"body").html(html);
		
		$("#empresaIdOrigenSelect").change(this.cambiarEmpresaOrigen);
		$("#empresaIdOrigenSelect").data("_this",this);
		$("#empresaIdDestinoSelect").change(this.cambiarEmpresaDestino);
		$("#empresaIdDestinoSelect").data("_this",this);
		
		this.inicializarValidaciones();
		
		$("#"+this._modal +"wizard").smartWizard({
			enableFinishButton: false,
			keyNavigation: false,
			hideButtonsOnDisabled: true,
			labelNext:'Siguiente', 
		    labelPrevious:'Anterior', 
		    labelFinish:'Copiar',    
		    onShowStep: this.onShowStep,
		    onFinish:this.onFinish,
		    buttonOrder: ['prev', 'next']
		});

		$('.buttonNext').addClass('btn btn-success active');
		$('.buttonPrevious').addClass('btn btn-info active');
		$('.buttonFinish').addClass('btn btn-primary active');
		
		$("#"+this._modal + "step2").data("_this",this);
		$("#"+this._modal + "step3").data("_this",this);
		
		$('.loader').hide();
		$('.msgBox').hide();
		
		$('[data-toggle="tooltip"]').tooltip();
		
	
	}
	
	onShowStep(obj,context)
	{
		var _this= obj.data("_this");
		if(context.fromStep==1 && context.toStep==2)
		{
			
		
			
			var  mensajeError="";
			if($("#empresaIdOrigenSelect").val()=="")
				mensajeError =  "Por favor ingrese una empresa origen";
			else if($("#sedeIdOrigenSelect").val()=="")
				mensajeError = "Por favor ingrese una sede origen";
        	
    		if(mensajeError!="")
    		{
    			swal({
    	            title: "Error",
    	            text: mensajeError,
    	            type: "warning",
    	            confirmButtonColor: "#DD6B55",
    	            confirmButtonText: "Cerrar",
    	            closeOnConfirm: true
    		        },
    		        function(isConfirm)
    		        {
    		        	$("#"+_this._modal +"wizard").smartWizard('goToStep', 1);
    		        });
    		}
    		else
    			_this.consultarProcesos();
		}
		else if(context.toStep==3)
		{
			var procedimientosSeleccionados = _this.procedimientosSeleccionados;
			
			if(procedimientosSeleccionados.length==0)
			{
				swal({
    	            title: "Error",
    	            text: "No hay procesos seleccionados, seleccione al menos uno para continuar",
    	            type: "warning",
    	            confirmButtonColor: "#DD6B55",
    	            confirmButtonText: "Cerrar",
    	            closeOnConfirm: true
    		        },
    		        function(isConfirm)
    		        {
    		        	$("#"+_this._modal +"wizard").smartWizard('goToStep', 2);
    		        });
			}
		}
	}
	
	get procedimientosSeleccionados()
	{
		var procedimientosSeleccionados= [];
		var children = $("#procedimientosCheckboxes").children();
		for(var i=0 ; i < children.length; i++)
		{
			var div = children[i];
			var checkboxes = $(div).find("input");
			if(checkboxes.length==1)
			{
				var check = checkboxes[0];
				var checked = $(check).prop("checked");
				if(checked)
				{
					var procedimiento = $(check).data("procedimiento");
					procedimientosSeleccionados.push(procedimiento);
				}
			}
		}
		return procedimientosSeleccionados;
	}
	
	consultarProcesos()
	{
		var empresaId = $("#empresaIdOrigenSelect").val();
		var sedeId = $("#sedeIdOrigenSelect").val();
		var repositorio = new ProcesosRepositorio();
		this._contexto.cargando = true;
		repositorio.consultarPorEmpresaSede(this,function(resultado)
		{
			this._contexto.cargando = false;
			if(resultado.mensajeError=="")
			{
				this.procesos = resultado.valor;
			}
			else
				this._contexto.mostrarMensajeError("Error",resultado.mensajeError);
		},empresaId, sedeId);
	}
	
	
	
	set procesos(procedimientos)
	{
		if(procedimientos.length>0)
		{
			var html="<div class='row'>";
			html+="<button id='deseleccionarButton' class='btn btn-secondary  pull-right' type='button'>Deselecionar</button>";
			html+="<button id='seleccionarTodosButton' class='btn btn-primary pull-right' type='button'>Seleccionar todos</button>";
			html+="</div>"
			html+="<div id='procedimientosCheckboxes'>"	
			html+="</div>"
			$("#contenedorProcedimientos").html(html);
			
			
			for(var i=0; i < procedimientos.length;  i++)
			{
				var procedimiento = procedimientos[i];
				var html="<div class='form-check  form-check-inline'>";
				html+="<input id='procedimientoCheck"+procedimiento.id+"' class='procedimiento form-check-input'  type='checkbox' >";
				html+=" <label class='form-check-label' for='procedimientoCheck"+procedimiento.id+"'>"+procedimiento.nombre+"</label>";
				html+="</div>";
				$("#procedimientosCheckboxes").append(html);
				$("#procedimientoCheck" + procedimiento.id).data("procedimiento",procedimiento);
				$("#procedimientoCheck" + procedimiento.id).change(this.procedimientoClick);
			}
			
			$("#seleccionarTodosButton").click(function(){
				
				$(".procedimiento").prop("checked",true);
			});
			
			$("#deseleccionarButton").click(function(){
				$(".procedimiento").prop("checked",false);
			});
			
		}
		else
		{
			swal({
	            title: "Error",
	            text: "No exiten procesos en esta sede, seleccione una sede diferente",
	            type: "warning",
	            confirmButtonColor: "#DD6B55",
	            confirmButtonText: "Cerrar",
	            closeOnConfirm: true
		        },
		        function(isConfirm)
		        {
		        	$("#"+_this._modal +"wizard").smartWizard('goToStep', 1);
		        });
		}
		
		
		
		$(".stepContainer").css("height","auto");
	}
	
	procedimientoClick(event)
	{
//		var comentario =$("#"+event.currentTarget.id).data("comentario");
//		if($("#"+event.currentTarget.id).is(':checked'))
//		{
//			var texto = $("#comentariosValidacionInput").val();
//			texto+= comentario.texto + ". ";
//			 $("#comentariosValidacionInput").val(texto);
//			
//		}
//		else
//		{
//			var texto = $("#comentariosValidacionInput").val();
//			texto = texto.replace(comentario.texto+ ". ","");
//			 $("#comentariosValidacionInput").val(texto);
//		}
	}
	
	
	onFinish(objs, context)
	 {
		
			$("#formulario").submit();
			
		
		
	 }
	
	
	renderizarDesplegados()
	{
		var html = "";
		
		for(var i=0; i < this._desplegados.length; i++)
		{
			var desplegado = this._desplegados[i];

			html+="<div id='"+this._modal+"card"+i+"' class='column-store-card'>";
			html+="	<div class='column-store-card-icon-bg' style='background-color:"+desplegado.color+";text-align:center;line-height:115px;'>";
			html+="<i style='font-size:50px;' id='"+this._modal+"Icono"+i+"' class='"+desplegado.icono+" text-white fa-7x'></i>";
			html+="</div>";
			html+="		<div class='column-store-card-text higher'>";
			html+="			<div class='column-store-card-title'>"+desplegado.nombre+"</div>";
			html+="			<div class='column-store-card-subtitle'>"+desplegado.descripcion+"</div>";
			html+="		</div>";
			html+="</div>";
		}
		
		return html;
	}
	
	agregarStepsPredeterminados(){
		var html = "";
		html+="<form id='formulario' action='#' method='post'>";
		html+="<div id='"+this._modal +"wizard' class='form_wizard wizard_horizontal'>";
		html+="  <ul class='wizard_steps'>";
		html+="	<li>";
		html+="	  <a href='#step-1'>";
		html+="		<span class='step_no'>1</span>";
		html+="		<span class='step_descr'>Origen<br />";
		html+="		<small>Seleccione empresa y sede origen</small>";
		html+="		</span>";
		html+="	  </a>";
		html+="	</li>";
		html+="	<li>";
		html+="	  <a id='"+this._modal +"step2' href='#step-2'>";
		html+="		<span class='step_no'>2</span>";
		html+="		<span class='step_descr'>Procesos<br />";
		html+="	    <small>Seleccione los procesos que desea copiar</small>";
		html+="		</span>";
		
		html+="	  </a>";
		html+="	</li>";
		html+="	<li>";
		html+="	  <a id='"+this._modal +"step3' href='#step-3'>";
		html+="		<span class='step_no'>3</span>";
		html+="		<span class='step_descr'>Destino<br />";
		html+="	    <small>Seleccione empresa y sede destino</small>";
		html+="		</span>";
		html+="	  </a>";
		html+="	</li>";
		html+="  </ul>";
		
		html+="<div id='step-1'>";
	
		html+="<div class='form-group'>";
		html+="<div>";
		html+="<label class='control-label mb-1'>Empresa</label>";
		html+="<select id='empresaIdOrigenSelect' name='empresaIdOrigenSelect' class='form-control'></select>";
		html+="</div>";
		html+="</div>";
		html+="<div class='form-group'>";
		html+="<div>";
		html+="<label class='control-label mb-1'>Sede</label>";
		html+="<select id='sedeIdOrigenSelect' name='sedeIdOrigenSelect' class='form-control'></select>";
		html+="</div>";
		html+="</div>";
		html+=" </div>";
		
		html+="  <div id='step-2'>";
		html+="<div id='contenedorProcedimientos'></div>";
		html+="  </div>";
		
	

		html+="  <div id='step-3'>";
		
		html+="<div class='form-group'>";
		html+="<div>";
		html+="<label class='control-label mb-1'>Empresa</label>";
		html+="<select id='empresaIdDestinoSelect' name='empresaIdOrigenSelect' class='form-control'></select>";
		html+="</div>";
		html+="</div>";
		html+="<div class='form-group'>";
		html+="<div>";
		html+="<label class='control-label mb-1'>Sede</label>";
		html+="<select id='sedeIdDestinoSelect' name='sedeIdOrigenSelect' class='form-control'></select>";
		html+="</div>";
		html+="</div>";
	
		
		html+="  </div>";
		
		html+="</div>";
		html+="</form>";
		return html;
	}
	
	
//	campoSeleccionado(event){
//		var _this =$("#"+event.currentTarget.id).data("_this");
//		_this.consultarValoresCampoSeleccionado();
//	}
	
	consultarCamposId(){
		/**********Consultara el campoId para mostrar en el select******************/
		this._contexto.cargando = true;
		var repositorio = new CamposRepositorio();
		repositorio.consultarCamposReferenciados(this,this.consultarCamposIdResultado, this.criteriosCampoId, null);
	}
	
	
	
	
	
	
	set modelo(modelo)
	{
		this._desplegadoSeleccionado = {id: modelo.desplegadoId};
		this._modelo = modelo;
		this._campoIdSeleccionado = modelo.id;
		this._propiedadesHijos = [];//modelo.getPropiedadesHijos;
		this._propiedadPadreBorrar = [];
		this.renderizarPropiedades();
		this.titulo = this._modelo.titulo;
		this.cargarPropiedades();
	}
	
	get modelo()
	{
		var modelo = 
		{	
			titulo : $("#"+this._modal+"tituloInput").val(),
			desplegadoId :this._desplegadoSeleccionado.id,
			propiedades : this.propiedades,
			propiedadesHijos:this._propiedadesHijos,
			propiedadPadreBorrar:this._propiedadPadreBorrar
		};
		if(this._modo==Modo.CAMBIO && this._modelo!=null){
			 modelo.id = this._modelo.id;
		     if(this._modelo != undefined && this._modelo.versionIdReferencia != null){
		    	 modelo.versionIdReferencia = this._modelo.versionIdReferencia;
			     modelo.campoIdReferencia = this._modelo.campoIdReferencia;
		     }
		}
		return modelo;
	}
	
	
	cerrarConfirmar()
	{
		swal.close();
	}
	



	inicializarValidaciones()
	{
		var _this = this;
		jQuery("#formulario").validate({
            ignore: [],
            errorClass: "invalid-feedback animated fadeInDown",
            errorElement: "div",
            errorPlacement: function(e, a) {
                jQuery(a).parents(".form-group > div").append(e)
            },
            highlight: function(e) {
                jQuery(e).closest(".form-group").removeClass("is-invalid").addClass("is-invalid")
            },
            success: function(e) {
                jQuery(e).closest(".form-group").removeClass("is-invalid"), jQuery(e).remove()
            },
            rules: {
            	 "empresaIdOrigenSelect": {
                     required: !0
                 },
                "sedeIdOrigenSelect": {
                    required: !0
                },
                "empresaIdDestinoSelect": {
                    required: !0
                },
               "sedeIdDestinoSelect": {
                   required: !0
               }
            },
            messages: {
            	 "empresaIdOrigenSelect": "Por favor ingrese una empresa origen",
                "sedeIdOrigenSelect": "Por favor ingrese una sede origen",
                "empresaIdDestinoSelect": "Por favor ingrese una empresa destino",
                "sedeIdDestinoSelect": "Por favor ingrese una sede destino"
                	
                
            },
            
            submitHandler:function (form) {
            	 _this.copiar();
            }
        });
				
	}
	
	copiar()
	{
		
		var  mensajeError="";
		if($("#empresaIdDestinoSelect").val()=="")
			mensajeError =  "Por favor ingrese una empresa destino";
		else if($("#sedeIdDestinoSelect").val()=="")
			mensajeError = "Por favor ingrese una sede destino";
		else if($("#sedeIdOrigenSelect").val()==$("#sedeIdDestinoSelect").val())
			mensajeError = "Debe seleccionar sedes distintas para el origen y destino";
    	
		if(mensajeError!="")
		{
			swal({
	            title: "Error",
	            text: mensajeError,
	            type: "warning",
	            confirmButtonColor: "#DD6B55",
	            confirmButtonText: "Cerrar",
	            closeOnConfirm: true
		        },
		        function(isConfirm)
		        {
		        	//$("#"+_this._modal +"wizard").smartWizard('goToStep', 1);
		        });
		}
		else
			this.copiarProcedimientos();

	}
	
	copiarProcedimientos()
	{
		var empresaIdOrigen =$("#empresaIdOrigenSelect").val();
		var sedeIdOrigen =$("#sedeIdOrigenSelect").val();
		var empresaIdDestino =$("#empresaIdDestinoSelect").val();
		var sedeIdDestino =$("#sedeIdDestinoSelect").val();
		
		var repositorio = new ProcesosRepositorio();
		this._contexto.cargando = true;
		repositorio.copiarProcesos(this,this.copiarProcedimientosResultado,empresaIdOrigen, sedeIdOrigen,this.procedimientosSeleccionados,empresaIdDestino,sedeIdDestino);
	}
	
	copiarProcedimientosResultado(resultado)
	{
		this._contexto.cargando = false;
		if(resultado.mensajeError=="")
		{
			$("#"+this._modal).modal('hide');
			this._contexto.mostrarMensaje("Notificación","Los procedimientos se copiaron correctamente.");
		}
		else
			this._contexto.mostrarMensajeError("Error",resultado.mensajeError);
	}
	
//	getMensajeError()
//	{
//		var  mensajeError="";
//		if($("#empresaIdOrigenSelect-error").length >0)
//			mensajeError =  $("#empresaIdOrigenSelect-error").html();
//		else if($("#sedeIdOrigenSelect-error").length >0)
//			mensajeError =  $("#sedeIdOrigenSelect-error").html();
//		else if($("#empresaIdDestinoSelect-error").length >0)
//			mensajeError =  $("#empresaIdDestinoSelect-error").html();
//		else if($("#sedeIdDestinoSelect-error").length >0)
//			mensajeError =  $("#sedeIdDestinoSelect-error").html();
//		
//		return mensajeError;
//	}
	
	
//	guardar()
//	{
//		if($("#"+this._modal+"tituloInput").val() != ""){
//			if(this._contexto!=null &&  this._funcion!=null){
//				this._funcion.call(this._contexto,this.modelo);
//			}
//		}
//		else{
//			  this._contexto.mostrarMensajeAdvertencia("favor de colocar un registro valido en el titulo");
//		}
//	}
	
	
	
	

	
	consultar()
	{
//		this._contexto.cargando = true;
//		var repositorio = new CamposRepositorio();
//		repositorio.consultarDesplegado(this,this.consultarResultado, this.criteriosSeleccion, this._paginacion);
		this.consultarEmpresas();
	}
	
	
	consultarEmpresas()
	{
		this._contexto.cargandoOpciones("#empresaIdOrigenSelect");
		this._contexto.cargandoOpciones("#empresaIdDestinoSelect");
		var repositorio = new EmpresasRepositorio();
		repositorio.consultar(this,this.consultarEmpresasResultado);
	}
	
	consultarEmpresasResultado(resultado)
	{
		this._contexto.cargando = false;
		if(resultado.mensajeError=="")
		{
			this.empresas = resultado.valor;
		}
		else
			this._contexto.mostrarMensajeError("Error",resultado.mensajeError);
	}
	
	
	consultarSedesOrigen()
	{
		this._contexto.cargandoOpciones("#sedeIdOrigenSelect");
		var repositorio = new SedesRepositorio();
		var  empresaId = $("#empresaIdOrigenSelect").val();
		repositorio.consultarPorEmpresa(this,this.consultarSedesOrigenResultado,empresaId);
	}
	
	consultarSedesOrigenResultado(resultado)
	{
		this._contexto.cargando = false;
		if(resultado.mensajeError=="")
		{
			this.sedesOrigen = resultado.valor;
		}
		else
			this._contexto.mostrarMensajeError("Error",resultado.mensajeError);
	}
	
	
	consultarSedesDestino()
	{
		this._contexto.cargandoOpciones("#sedeIdDestinoSelect");
		var repositorio = new SedesRepositorio();
		var  empresaId = $("#empresaIdDestinoSelect").val();
		repositorio.consultarPorEmpresa(this,this.consultarSedesDestinoResultado,empresaId);
	}
	
	consultarSedesDestinoResultado(resultado)
	{
		this._contexto.cargando = false;
		if(resultado.mensajeError=="")
		{
			this.sedesDestino = resultado.valor;
		}
		else
			this._contexto.mostrarMensajeError("Error",resultado.mensajeError);
	}
	
	set empresas(registros)
	{		
		this.renderizarAsistente();
		
		this._contexto.cargarOpciones('#empresaIdOrigenSelect', registros, this.modo, this.modeloEdicion, 'empresaId',"");
		this._contexto.cargarOpciones('#empresaIdDestinoSelect', registros, this.modo, this.modeloEdicion, 'empresaId',"");
		
		this.consultarSedesOrigen();
		this.consultarSedesDestino();
	}
	
	cambiarEmpresaOrigen()
	{
		var _this =$("#"+event.currentTarget.id).data("_this");
		_this._contexto.cargandoOpciones("#sedeIdOrigenSelect");
		_this.consultarSedesOrigen();
	}
	
	cambiarEmpresaDestino()
	{
		var _this =$("#"+event.currentTarget.id).data("_this");
		_this._contexto.cargandoOpciones("#sedeIdDestinoSelect");
		_this.consultarSedesDestino();
	}
	
	set sedesOrigen(registros)
	{		
		this._contexto.cargarOpciones('#sedeIdOrigenSelect', registros, this.modo, this.modeloEdicion, 'sedeId',"");
	}
	
	set sedesDestino(registros)
	{		
		this._contexto.cargarOpciones('#sedeIdDestinoSelect', registros, this.modo, this.modeloEdicion, 'sedeId',"");
	}

	get paginacion(){
		return null;
	}	

	
}
