class ImportarUsuariosAsistente
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
//	
//	set desplegados(desplegados)
//	{
//		this._desplegados = desplegados;
//		this.renderizarAsistente();
//		
//		var indice = 0;
//		this._desplegadoSeleccionado = this._desplegados[0];
//		
//		var card = $("#"+this._modal +"IconosDiv").find("div");
//		card.removeClass("card-activo");
//		$("#"+this._modal+"card"+indice).addClass("card-activo");
//
//		this.renderizarPropiedades();
//	}
//	

	
	renderizarModal()
	{

		var html="";
		html+="<div  class='modal fade' id='"+this._modal +"' tabindex='-1' role='dialog' aria-labelledby='"+this._modal +"Titulo' aria-hidden='true'>";
		html+="	<div class='modal-dialog modal-dialog-scrollable modal-lg' role='document' >";
		html+="		<div class='modal-content'>";
		html+="			<div class='modal-header'>";
		html+="				<h5 class='modal-title' id='"+this._modal +"Titulo'>Importar usuarios</h5>";
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
		    labelFinish:'Importar',    
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
		
		$('#file').change(this.cambiarArchivo);
		
	
	}
	
	get archivo()
	{
//		var contenedorArchivos = $("#file") ;
//		if(contenedorArchivos.length>0)
//		{
//			if(contenedorArchivos[0].files.length>0)
//				return contenedorArchivos[0].files[0];
//		}
//		return null;
		return $('#archivoSeleccionadoLabel').data("archivo");
	}
	
	cambiarArchivo(event)
	{
		var _this = this;
		if (event.currentTarget.files && event.currentTarget.files[0]) 
		{
			var archivo = event.currentTarget.files[0];
            var reader = new FileReader();

            reader.onload = function (e)
            {
            	vista.cargando = false;
            	if(archivo.type=='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            	{
            		$('#archivoSeleccionadoLabel').html(archivo.name);
            		$('#archivoSeleccionadoLabel').data("archivo",archivo);
            	}
            	else
            	{
            		$('#archivoSeleccionadoLabel').html("");
            		$('#archivoSeleccionadoLabel').data("archivo",null);
            		vista.mostrarMensajeError("Error","Verifique que el tipo de archivo sea correcto");
            	}
            	
                
            };
            reader.readAsDataURL(event.currentTarget.files[0]);
            vista.cargando = true;
        }
	}
	
	
	onShowStep(obj,context)
	{
		var _this= obj.data("_this");
		if(context.fromStep==1 && context.toStep==2)
		{
			var  mensajeError="";
			if($("#empresaIdOrigenSelect").val()=="")
				mensajeError =  "Por favor ingrese una empresa";
			else if($("#sedeIdOrigenSelect").val()=="")
				mensajeError = "Por favor ingrese una sede";
			else if($("#departamentoIdOrigenSelect").val()=="")
				mensajeError = "Por favor ingrese una departamento";
        	
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
//    		else
//    			_this.consultarProcedimientos();
		}
		else if(context.toStep==3)
		{
//			
		}
	}
//	
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
	
	agregarStepsPredeterminados(){
		var html = "";
		html+="<form id='formulario' action='#' method='post'>";
		html+="<div id='"+this._modal +"wizard' class='form_wizard wizard_horizontal'>";
		html+="  <ul class='wizard_steps'>";
		html+="	<li>";
		html+="	  <a href='#step-1'>";
		html+="		<span class='step_no'>1</span>";
		html+="		<span class='step_descr'>Datos básicos<br />";
		html+="		<small>Seleccione empresa, sede y departamento</small>";
		html+="		</span>";
		html+="	  </a>";
		html+="	</li>";
		html+="	<li>";
		html+="	  <a id='"+this._modal +"step2' href='#step-2'>";
		html+="		<span class='step_no'>2</span>";
		html+="		<span class='step_descr'>Formato de importación<br />";
		html+="	    <small>Descargue el formato para importación</small>";
		html+="		</span>";
		
		html+="	  </a>";
		html+="	</li>";
		html+="	<li>";
		html+="	  <a id='"+this._modal +"step3' href='#step-3'>";
		html+="		<span class='step_no'>3</span>";
		html+="		<span class='step_descr'>Importar<br />";
		html+="	    <small>Importe la información capturada</small>";
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
		
		html+="<div class='form-group'>";
		html+="<div>";
		html+="<label class='control-label mb-1'>Departamento</label>";
		html+="<select id='departamentoIdOrigenSelect' name='departamentoIdOrigenSelect' class='form-control'></select>";
		html+="</div>";
		html+="</div>";
		
		html+=" </div>";
		
		html+="  <div id='step-2'>";
		html+="<div id='contenedorProcedimientos'>";
		html+="<ul>";
		html+="<li>1. Descargue el formato de importación</li>";
		html+="</ul>";
		html+="<div class='col text-center'>";
		html+="<a href='formatos/CAVI - Formato de captura de usuarios.xlsx' target='_blank'><button id='descargarButton' class='btn btn-primary' type='button'><i class='fas fa-download'></i> Descargar formato</button></a>";
		html+="</div>";
		
		html+="<ul>";
		html+="<li>2. Abra el archivo descargado</li>";
		html+="<li>3. Capture los datos solicitados en el formato</li>";
		html+="<li>4. Guarde los cambios</li>";
		html+="<li>5. Presione siguiente</li>";
		html+="</ul>";
		
		
		
		html+="</div>";
		html+="  </div>";
		
	

		html+="  <div id='step-3'>";
		

		
		html+="<ul>";
		html+="<li>1. Seleccione el formato de importación con los datos capturados</li>";
		html+="</ul>";
		html+="<div class='col text-center'>";
		html+="<button id='subirButton' class='btn btn-primary'  accept='.xlsx' type='button' onclick='$(\"#file\").trigger(\"click\")'><i class='fas fa-upload'></i> Seleccionar formato</button>";
		html+="<input type='file' id='file'  name='file' style='display:none' />";
		html+="</div>";
		html+="<br>";
		html+="<div class='col text-center'>";
		html+="<label id='archivoSeleccionadoLabel'></label>";
		html+="</div>";
		
		html+="<ul>";
		html+="<li>2. Presione importar</li>";
		html+="</ul>";
		
		html+="</div>";
		
		html+="</form>";
		return html;
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
            	 _this.importar();
            }
        });
				
	}
	
	importar()
	{
		
		var  mensajeError="";
//		if($("#empresaIdDestinoSelect").val()=="")
//			mensajeError =  "Por favor ingrese una empresa destino";
//		else if($("#sedeIdDestinoSelect").val()=="")
//			mensajeError = "Por favor ingrese una sede destino";
//		else if($("#departamentoIdDestinoSelect").val()=="")
//			mensajeError = "Por favor ingrese una sede destino";
		
		if(this.archivo==null)
			mensajeError = "Seleccione un archivo";
		
		if(mensajeError=="")
			this.importarUsuarios();
		else
			this._contexto.mostrarMensaje("Error",mensajeError);
    	
//		if(mensajeError!="")
//		{
//			swal({
//	            title: "Error",
//	            text: mensajeError,
//	            type: "warning",
//	            confirmButtonColor: "#DD6B55",
//	            confirmButtonText: "Cerrar",
//	            closeOnConfirm: true
//		        },
//		        function(isConfirm)
//		        {
//		        	//$("#"+_this._modal +"wizard").smartWizard('goToStep', 1);
//		        });
//		}
//		else
//			this.importarUsuarios();

	}
	
	importarUsuarios()
	{
		var empresaId=$("#empresaIdOrigenSelect").val();
		
		var empresa = ArrayUtils.searchWithValues("id",[empresaId],this._empresas); 
		
		var sedeId=$("#sedeIdOrigenSelect").val();
		var departamentoId=$("#departamentoIdOrigenSelect").val();
		this._contexto.mostrarIndicador();
		var _this = this;
		var repositorio = new UsuariosRepositorio();
		repositorio.importarUsuarios(this,function(resultado)
		{
			_this._contexto.ocultarIndicador();
			if(resultado.mensajeError=="")
			{
				var texto="";
				if(resultado.valor==0)
					texto = "\nNo se detectaron usuario nuevos";
				if(resultado.valor==1)
					texto = "\n"+resultado.valor + " usuario importado.";
				else
					texto = "\n"+resultado.valor + " usuarios importados.";
				swal({
		            title: "Terminado",
		            text: "La importación se realizó correctamente. " + texto,
		            type: "success",
		            confirmButtonColor: "#DD6B55",
		            confirmButtonText: "Cerrar",
		            closeOnConfirm: true
			        },
			        function(isConfirm)
			        {
			        	$("#"+_this._modal).modal("hide");
			        	_this._contexto.consultar();
			        	//$("#"+_this._modal +"wizard").smartWizard('goToStep', 1);
			        });
			}
			else
				_this._contexto.mostrarMensajeError("Error",resultado.mensajeError);
		},empresaId, sedeId, departamentoId, empresa.perfilId, this.archivo);

	}
	
	copiarProcedimientos()
	{
		var empresaIdOrigen =$("#empresaIdOrigenSelect").val();
		var sedeIdOrigen =$("#sedeIdOrigenSelect").val();
		var empresaIdDestino =$("#empresaIdDestinoSelect").val();
		var sedeIdDestino =$("#sedeIdDestinoSelect").val();
		
		var repositorio = new ProcedimientosRepositorio();
		this._contexto.cargando = true;
		repositorio.copiarProcedimientos(this,this.copiarProcedimientosResultado,empresaIdOrigen, sedeIdOrigen,this.procedimientosSeleccionados,empresaIdDestino,sedeIdDestino);
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
		this.consultarDepartamentos();
	}
	
	
	consultarEmpresas()
	{
		this._contexto.cargandoOpciones("#empresaIdOrigenSelect");
		this._contexto.cargandoOpciones("#empresaIdDestinoSelect");
		var repositorio = new EmpresasRepositorio();
		repositorio.consultar(this,this.consultarEmpresasResultado,null,false);
	}
	

	consultarDepartamentos()
	{
		this._contexto.cargandoOpciones("#departamentoIdOrigenSelect");
		var repositorio = new DepartamentosRepositorio();
		repositorio.consultar(this,function(resultado)
		{
			this._contexto.cargando = false;
			if(resultado.mensajeError=="")
			{
				this.departamentos = resultado.valor;
			}
			else
				this._contexto.mostrarMensajeError("Error",resultado.mensajeError);
		},null,false);
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
		this._empresas = registros;
		
		this.renderizarAsistente();
		
		this._contexto.cargarOpciones('#empresaIdOrigenSelect', registros);
		//this._contexto.cargarOpciones('#empresaIdDestinoSelect', registros, this.modo, this.modeloEdicion, 'empresaId',"");
		
		this.consultarSedesOrigen();
		//this.consultarSedesDestino();
	}
	
	set departamentos(registros)
	{		
		//this.renderizarAsistente();
		
		this._contexto.cargarOpciones('#departamentoIdOrigenSelect', registros);
		//this._contexto.cargarOpciones('#empresaIdDestinoSelect', registros, this.modo, this.modeloEdicion, 'empresaId',"");
		
		//this.consultarSedesOrigen();
		//this.consultarSedesDestino();
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
		this._contexto.cargarOpciones('#sedeIdOrigenSelect', registros);
	}
	
	set sedesDestino(registros)
	{		
		this._contexto.cargarOpciones('#sedeIdDestinoSelect', registros);
	}

	get paginacion(){
		return null;
	}	

	
}
