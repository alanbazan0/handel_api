class ReportePersonalCapacitacionesVista extends CatalogoVista
{		
	constructor(ventana)
	{	
		super(ventana);
		this.presentador = new ReportePersonalCapacitacionesPresentador(this);
		this._urlFormulario = "html/formularios/capacitados.php";
		var fecha = new Date();
		this._time = fecha.getTime();
		
	}
	
	get time()
	{
		return this._time;
	}

	inicializar()
	{
		var _this = this;
		$("#consultarButton").click(function(){
			_this.consultar();
		});
		
		$("#agregarButton").click(function(){
			_this.agregar();
		});
		
		$("#subirFormatoButton").click(function(){
			_this.iniciarSubirFormato();
		});
		
		//this.crearFechas();
		
		//this.crearFecha("fechaInicialInputCriterio");
		
		this.crearColumnasGrid();		
		
		this.consultoGrid = false;
		
		
		this.crearFechas();
		
		this.consultarEmpresasCriterio();
		this.consultarCursosCriterio();
		
		 this.inicializarMoment();
	    this.consultarLeccionesReprobadas();
	}
	

	iniciarSubirFormato()
	{
		this._subirFormatoModal = new SubirFormatoAsistente();
		this._subirFormatoModal.mostrar(this, this.subirFormato);
	}
	
	subirFormato()
	{
		
	}
	
	
	crearColumnasGrid()
	{
		this.tabla.columnas = [
			
//			{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"D" },
			
			{longitud:200, 	titulo:"Capacitacion",   alias:"titulo", alineacion:"I",class: "desc" }, 
			{longitud:100, 	titulo:"Fecha inicio",   alias:"fechaInicial", alineacion:"I"},
			{longitud:100, 	titulo:"Completada",   alias:"terminado", alineacion:"I",itemRenderer: this.rendererTerminado},
			{longitud:100, 	titulo:"Fecha termino",   alias:"fechaFinal", alineacion:"I"},
			{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"D" ,itemRenderer:this.renderLogo},
			{longitud:200, 	titulo:"Nombre",   alias:"nombre", alineacion:"I",class: "desc" }, 
			{longitud:200, 	titulo:"Apellido",   alias:"apellido", alineacion:"I",class: "desc" },
			{longitud:100, 	titulo:"Número de empleado",   alias:"numeroEmpleado", alineacion:"I" },
			//¡{longitud:200, 	titulo:"Nombre de usuario",   	alias:"nombreUsuario", alineacion:"I", classSpan:"block-email" }, 
			{longitud:200, 	titulo:"Empresa",   alias:"empresaNombre", alineacion:"I" },	
			{longitud:200, 	titulo:"Sede",   alias:"sedeNombre", alineacion:"I" },	
			//{longitud:100, 	titulo:"Puesto",   alias:"puestoNombre", alineacion:"I" },	
//			{longitud:100, 	titulo:"Area",   alias:"areaNombre", alineacion:"I" },	
			{longitud:100, 	titulo:"Departamento",   alias:"departamentoNombre", alineacion:"I" },	
			//{longitud:100, 	titulo:"Capacitación",   alias:"capacitacion", alineacion:"I", itemRenderer: this.rendererCapacitacion },
			
			{longitud:100, 	titulo:"Aprovechamiento",   alias:"porcentaje", alineacion:"C",itemRenderer: this.renderPorcentaje },
			{longitud:100, 	titulo:"Preguntas correctas",   alias:"correctas", alineacion:"C"},
			{longitud:100, 	titulo:"Total de preguntas",   alias:"total", alineacion:"C" },
			{longitud:100, 	titulo:"Total de preguntas contestadas",   alias:"preguntasContestadas", alineacion:"C" },
//			{longitud:100, 	titulo:"Supervisor 1",   alias:"supervisor1Nombre", alineacion:"I" },	
//			{longitud:100, 	titulo:"Supervisor 2",   alias:"supervisor2Nombre", alineacion:"I" },	
//			{longitud:100, 	titulo:"Supervisor 3",   alias:"supervisor3Nombre", alineacion:"I" },	
			//{longitud:100, 	titulo:"Tipo de usuario",   alias:"tipoUsuarioNombre", alineacion:"I" },
//			{longitud:200, 	titulo:"Ultimo acceso",   alias:"ultimoAcceso", alineacion:"I" },			
//			{longitud:250, 	titulo:"Fecha de alta",   alias:"fechaAlta", alineacion:"I" },	
//			{longitud:200, 	titulo:"Fecha de última modificación",   alias:"fechaModificacion", alineacion:"I" },
//			{longitud:100, 	titulo:"Estatus",   alias:"estatus", alineacion:"D", itemRenderer:this.renderEstatus}

	
		]
		
		if(this.usuario.tipoUsuarioId == TipoUsuario.ADMINISTRADOR || this.usuario.recursosHumanos==1)
		{
			this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Lecciones'  type='button' class='lecciones btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-list fa-lg'></span></button>"+
			"<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar calificación de capacitación'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";
		}
		var _this = this;
		
		var datatable = this.tabla.datatable.DataTable();

		 var buttonCommon = {
				   text:      '<i class="fa fa-file-excel-o"></i> Exportar',
			        exportOptions: {
			        	 modifier: {
		                        selected: null
		                    },
			            format: {
			                body: function ( data, row, column, node ) 
			                {
			                	if(column==ArrayUtils.indexWithValues("alias",["logo"],_this.tabla.columnas))
			                	{
			                		return "";
			                	} 
			                	else if(column==ArrayUtils.indexWithValues("alias",["terminado"],_this.tabla.columnas))
			                	{
		                		  if(data.includes("fa-check"))
			                		   return "Si";
			                	   else
			                		   return "No";
			                	}
								else if(column==ArrayUtils.indexWithValues("alias",["porcentaje"],_this.tabla.columnas))
			                	{
									var registro = _this.tabla.registros[row];
									if(registro!=null)
										return registro.porcentaje+"%";
			                	}
			                	else if(node.innerHTML.includes("button"))
			                		return "";
			                	return data;
			                 
			                }
			            }
			        }
			    };

		 this.tabla.botones =  {
			      buttons: [
			    	  $.extend( true, {}, buttonCommon, {
			                extend: 'excel',"className": 'btn btn-success' 
			            } ),
			               ],
			       dom: {
					  button: {
					  className: 'btn'
				         }
			       }
		 };
	
		this.tabla.registros = [];	
	}
	
	
	
	inicializarValidacionesFormulario()
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
            	
                "contrasenaInput": {required: !0},
                "nombreInput": {required: !0},
                "apellidoInput": {required: !0},
                "empresaSelect": {required: !0},
                "sedeSelect": {required: !0},
                "puestoSelect": {required: !0},
                //"areaSelect": {required: !0}
               
            },
            messages: {
            	
            	 "contrasenaInput": "Por favor ingrese una contraseña",
                "nombreInput": "Por favor ingrese un nombre",
                "apellidoInput": "Por favor ingrese un apellido",
                "empresaSelect": "Por favor seleccione una empresa",
                "sedeSelect": "Por favor seleccione una sede",
                "puestoSelect": "Por favor seleccione un puesto",
              //  "areaSelect": "Por favor seleccione un área"
                	
                
            },
            submitHandler:function (form) {
            	 _this.guardar();
            }
        });
	}

	renderLogo(renglon, type, set)
	{    
		var fecha = new Date();
		var contenido = "";
		var icono = HANDEL_API+ "/"+renglon.fotoPerfil+"?"+vista.time;
		contenido += "<center><img src='" + icono + "' style='width:30px;height:30px;border-radius:50%'></img></center>";
	    return contenido;
	}
	
	rendererCapacitacion(renglon, type, set)
	{    
		if(renglon.fechaUltimaCapacitacion!=null)
			return "<center><i class='fas fa-check text-green'></i></center>";
	    return "";
	}
	
	rendererTerminado(renglon, type, set)
	{    
		if(renglon.terminado==1)
			return "<center><i class='fas fa-check text-green'></i></center>";
	    return "";
	}
	
	
	
	renderPermisoSAHA(renglon, type, set)
	{    
		var contenido = "";
		if(renglon.permisoSAHA==1)
			contenido += "<center><span class='fa fa-check fa-lg text-success'></span></center>";
		else
			contenido += "<center><span class='fa fa-close fa-lg text-danger'></span></center>";
	    return contenido;
	}
	
	renderPermisoSIVAH(renglon, type, set)
	{    
		var contenido = "";
		if(renglon.permisoSIVAH==1)
			contenido += "<center><span class='fa fa-check fa-lg text-success'></span></center>";
		else
			contenido += "<center><span class='fa fa-close fa-lg text-danger'></span></center>";
	    return contenido;
	}
	
	renderPermiso10y7(renglon, type, set)
	{    
		var contenido = "";
		if(renglon.permiso10y7==1)
			contenido += "<center><span class='fa fa-check fa-lg text-success'></span></center>";
		else
			contenido += "<center><span class='fa fa-close fa-lg text-danger'></span></center>";
	    return contenido;
	}
	
	agregar()
	{
		super.agregar();
		//$('#nombreUsuarioInput').focus();
		
		
	}
	
	consultarCombos()
	{
		
		//this.consultarTiposUsuario();
//		var validator = $("#formulario").validate();
//		validator.destroy();
		
		//$('#nombreUsuarioDiv').hide();
		if($('#contrasenaInput').val()=="")
		{
			var contrasena = this.generarContrasenaNumerica(4);
			$('#contrasenaInput').val(contrasena);
		}
		//this.inicializarValidacionesFormularioInspector();
		this.consultarEmpresas();
		this.consultarDepartamentos();
	}
	
	consultarDepartamentos()
	{
		this.cargandoOpciones("#departamentoSelect");
		this.presentador.consultarDepartamentos();
	}
	

	set departamentos(registros)
	{		
		this.cargarOpciones('#departamentoSelect', registros, this.modo, this.modeloEdicion, 'departamentoId',"");
	}
	
	set departamentosCriterio(registros)
	{		
		this.cargarOpciones('#departamentoSelectCriterio', registros);
		
	}
	
	set usuariosCriterio(registros)
	{		
		this.cargarOpciones('#usuarioSelectCriterio', registros, "", null, "",null, "nombreCompleto");
		/*if(this.consultoGrid==false)
		{
			this.consultar();
			this.consultoGrid=true;
		}*/
	}
	
	set cursosCriterio(registros)
	{
		this.cargarOpciones('#cursoSelectCriterio', registros,"",null, "id", null, "titulo");
		//this.consultarEmpresasCriterio();
	}
	
	editar(id)
	{
		super.editar(id);
		$('#nombreUsuarioInput').focus();
	}
	
	set modelo(valor)
	{		
		this.modeloEdicion = valor;
		$('#nombreUsuarioInput').val(this.modeloEdicion.nombreUsuario);
		$('#contrasenaInput').val(this.modeloEdicion.contrasena);
		$('#nombreInput').val(this.modeloEdicion.nombre);
		$('#apellidoInput').val(this.modeloEdicion.apellido);
		$("input[name=estatus][value=" + this.modeloEdicion.estatus + "]").prop('checked', true);
//		if(this.modeloEdicion.permisoSAHA)
//			$("#permisoSAHARadio").prop('checked', true);
//		else
//			$("#permisoSAHARadio").prop('checked', false);
//		if(this.modeloEdicion.permisoSIVAH)
//			$("#permisoSIVAHRadio").prop('checked', true);
//		else
//			$("#permisoSIVAHRadio").prop('checked', false);
//		if(this.modeloEdicion.permiso10y7)
//			$("#permiso10y7Radio").prop('checked', true);
//		else
//			$("#permiso10y7Radio").prop('checked', false);
		this.consultarCombos();
	}
	
	get modelo()
	{
		 var modelo = 
		 {		
			 nombreUsuario:$('#nombreUsuarioInput').val(),
			 contrasena:$('#contrasenaInput').val(),
			 nombre:$('#nombreInput').val(),
			 apellido:$('#apellidoInput').val(),
			 empresaId:$('#empresaSelect').val(),
			 sedeId:$('#sedeSelect').val(),
			 puestoId:$('#puestoSelect').val(),
			 areaId:$('#areaSelect').val(),
			 supervisor1Id:$('#supervisor1Select').val(),
			 supervisor2Id:$('#supervisor2Select').val(),
			 supervisor3Id:$('#supervisor3Select').val(),
			 tipoUsuarioId: TipoUsuario.CAPACITADO,
			 estatus:$('#estatusRadio').is(':checked')?1:0,
			 permisoSAHA:0,
			 permisoSIVAH:0,
		 	 permiso10y7:1
		 };
		 if(this.modo=="CAMBIO" && this.modeloEdicion!=null)
			 modelo.id = this.modeloEdicion.id;
		 return modelo;
	 }
	 
	generarContrasena(longitud)
	{
	  var caracteres = "abcdefghijkmnpqrtuvwxyzABCDEFGHIJKLMNPQRTUVWXYZ0123456789";
	  var contraseña = "";
	  for (var i=0; i<longitud; i++) contraseña += caracteres.charAt(Math.floor(Math.random()*caracteres.length));
	  	return contraseña;
	}
	
	generarContrasenaNumerica(longitud)
	{
	  var caracteres ="0123456789";
	  var contraseña = "";
	  for (var i=0; i<longitud; i++) contraseña += caracteres.charAt(Math.floor(Math.random()*caracteres.length));
	  	return contraseña;
	}


	limpiarFormulario()
	{
		$('#nombreUsuarioInput').val("");
		$('#contrasenaInput').val("");
		$('#nombreInput').val("");
		$('#apellidoInput').val("");
		this.cargandoOpciones('#tipoUsuarioSelect');
		this.cargandoOpciones('#empresaSelect');
		this.cargandoOpciones('#sedeSelect');
		this.cargandoOpciones('#puestoSelect');
		this.cargandoOpciones('#areaSelect');
		this.cargandoOpciones('#supervisor1Select');
		this.cargandoOpciones('#supervisor2Select');
		this.cargandoOpciones('#supervisor3Select');
	}
	
	consultarEmpresas()
	{
		this.cargandoOpciones("#empresaSelect");
		this.presentador.consultarEmpresas();
	}

	consultarTiposUsuario()
	{
		this.cargandoOpciones("#tipoUsuarioSelect");
		this.presentador.consultarTiposUsuario();
	}
	
	set tiposUsuario(registros)
	{
		this.cargarOpciones('#tipoUsuarioSelect', registros, this.modo, this.modeloEdicion, 'tipoUsuarioId',"");
	}
	
	set empresas(registros)
	{		
		this.cargarOpciones('#empresaSelect', registros, this.modo, this.modeloEdicion, 'empresaId',null);
	}
	
	cambiarEmpresa()
	{
		this.cargandoOpciones("#sedeSelect");
		this.cargandoOpciones("#puestoSelect");
		this.cargandoOpciones("#areaSelect");
		this.cargandoOpciones("#supervisor1Select");
		this.cargandoOpciones("#supervisor2Select");
		this.cargandoOpciones("#supervisor3Select");
	
		this.consultarSedes();
		
		//this.consultarPuestos();
		this.consultarSupervisores();
		
	}
	
	cambiarNombre()
	{
		var nombre = this.removeAccents($("#nombreInput").val()).trim();
		var apellido = this.removeAccents($("#apellidoInput").val()).trim();
		var empresaId = $("#empresaSelect").val();
		var nombreUsuario = nombre.toLowerCase();
		
		if(apellido!="")
			nombreUsuario += "." + apellido.toLowerCase() ;
		
		nombreUsuario+="." +empresaId;
		$("#nombreUsuarioInput").val(nombreUsuario);
	}
	
	removeAccents (str) {
		  return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
		} 
	
//	cambiarTipoUsuario()
//	{
//		var tipo = $('#tipoUsuarioSelect').val();
//		
//		var validator = $("#formulario").validate();
//		validator.destroy();
//		
//		if(tipo==TipoUsuario.INSPECTOR)
//		{
//			$('#nombreUsuarioDiv').hide();
//			if($('#contrasenaInput').val()=="")
//			{
//				var contrasena = this.generarContrasenaNumerica(4);
//				$('#contrasenaInput').val(contrasena);
//			}
//			this.inicializarValidacionesFormularioInspector();
//		}
//		else
//		{
//			$('#nombreUsuarioDiv').show();
//			if($('#contrasenaInput').val()=="")
//			{
//				var contrasena = this.generarContrasena(10);
//				$('#contrasenaInput').val(contrasena);
//			}
//			this.inicializarValidacionesFormulario();
//		}
//		var ayudaTipoUsuario = this.getAyudaTipoUsuario(tipo);
//		$("#tipoUsuarioSelect").attr("data-original-title",ayudaTipoUsuario);
//		$('[data-toggle="tooltip"]').tooltip("hide");
//	}
//	
	clearValidation(formElement){
		 //Internal $.validator is exposed through $(form).validate()
		 var validator = $(formElement).validate();
		 //Iterate through named elements inside of the form, and mark them as error free
		 $('[name]',formElement).each(function(){
		   validator.successList.push(this);//mark as error free
		   validator.showErrors();//remove error messages if present
		 });
		 validator.resetForm();//remove error class on name elements and clear history
		 validator.reset();//remove all error and success data
		}
	
	getAyudaTipoUsuario(tipo)
	{
		var ayuda="";
		if(tipo==TipoUsuario.ADMINISTRADOR_CORPORATIVO)
		{
			ayuda = "El administrador corporativo es un usuario que puede ver información de todas las sedes de la compañia."
		}
		else if(tipo==TipoUsuario.ADMINISTRADOR)
		{
			ayuda  ="El administrador es responsable de una sede o equipo de trabajo y puede añadir o eliminar inspectores en la plataforma web.";
		}
		else if(tipo==TipoUsuario.USUARIO)
		{
			ayuda  ="El usuario aplicación se define para abrir la aplicación directamente en la tablet e indica en que area estará la tablet (como por ejemplo caseta de vigilancia, embarques, etc.)";
		}
		else if(tipo==TipoUsuario.INSPECTOR)
		{
			ayuda  ="Un inspector es el trabajador que realizará las inspecciones.";
		}
		return ayuda;
	}
	
	cambiarSede()
	{
		this.cargandoOpciones("#puestoSelect");
		this.cargandoOpciones("#areaSelect");
		this.consultarPuestos();
		this.consultarAreas();
	}
	
	consultarSedes()
	{
		this.presentador.consultarSedes();
	}
	
	set sedes(registros)
	{
		this.cargarOpciones('#sedeSelect', registros, this.modo, this.modeloEdicion, 'sedeId',"");
	}
	
	consultarAreas()
	{
		this.presentador.consultarAreas();
	}
	
	set areas(registros)
	{
		this.cargarOpciones('#areaSelect', registros, this.modo, this.modeloEdicion, 'areaId',"");
	}
	
	consultarPuestos()
	{
		this.presentador.consultarPuestos();
	}
	
	set puestos(registros)
	{
		this.cargarOpciones('#puestoSelect', registros, this.modo, this.modeloEdicion, 'puestoId',"");

	}
	
	consultarSupervisores()
	{
		this.presentador.consultarSupervisores();
	}
	
	set supervisores1(registros)
	{
		this.cargarSupervisores('#supervisor1Select', registros, this.modo, this.modeloEdicion, 'supervisor1Id',"");

	}
	
	set supervisores2(registros)
	{
		this.cargarSupervisores('#supervisor2Select', registros, this.modo, this.modeloEdicion, 'supervisor2Id',"");
	}
	
	set supervisores3(registros)
	{
		this.cargarSupervisores('#supervisor3Select', registros, this.modo, this.modeloEdicion, 'supervisor3Id',"");
	}
	
	get criteriosSeleccion()
	{
		 var criteriosSeleccion = 
		 {				    
			empresaId: $('#empresaSelectCriterio').val(),
			sedeId: $('#sedeSelectCriterio').val(),
			departamentoId: $('#departamentoSelectCriterio').val(),
			usuarioId : $('#usuarioSelectCriterio').val(),
			cursoId : $('#cursoSelectCriterio').val(),
			tipoReporte:$('#tipoReporteSelectCriterio').val(),
			fechaInicial: this._fechaInicial,
			fechaFinal: this._fechaFinal,
			filtrarPerfil: $('#filtrarPerfilSelectCriterio').val()=="true"?true:false
		 }
		 return criteriosSeleccion;
	}	
	
	crearFecha(id)
	{
		 $( "#"+id ).datepicker();
	}
	
	/*crearFechas()
	{
		var _this = this;
			moment.locale('es') ;
			var start = moment().subtract(1, 'years');
    		var end = moment();	

		 function cb(start, end) {
				_this._fechaInicial = start.format('DD/MM/YYYY');
				_this._fechaFinal = end.format('DD/MM/YYYY');
		       	$('#daterange-btn span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'))
		    }

			$('#daterange-btn').daterangepicker(
		      {
			// drops: 'up',
				drops: 'auto',
				//opens: 'center',
		        ranges   : {
		          'Histórico'       : ["01/08/2020", moment()],
		          'Ultimo año'   : [moment().subtract(1, 'year'), moment()],
		          'Ultimo semestre' : [moment().subtract(6, 'month'), moment()],
		          'Ultimo trimestre': [moment().subtract(3, 'month'), moment()],
		          'Este mes'  : [moment().startOf('month'), moment().endOf('month')],
		          'Mes pasado'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
		        },
		        startDate: start,
		        endDate  : end,
				locale: {
				    "customRangeLabel": "Rango",
					"cancelLabel" : "Cancelar"
				  },
		      },
		      cb
		    );
			 
			cb(start,end);
				
	}*/
	
	
	
	consultarEmpresasCriterio()
	{
		this.cargandoOpciones("#empresaSelectCriterio");
		this.presentador.consultarEmpresasCriterio();
	}
	
	consultarCursosCriterio()
	{
		this.cargandoOpciones("#cursoSelectCriterio");
		this.presentador.consultarCursosCriterio();
	}
	
	set empresasCriterio(registros)
	{		
		this.cargarOpciones('#empresaSelectCriterio', registros);
		//this.consultar();
	}
	
	set sedesCriterio(registros)
	{		
		this.cargarOpciones('#sedeSelectCriterio', registros);
	
	}
	

	cambiarEmpresaCriterio()
	{
		//this.cargandoOpciones("#departamentoSelectCriterio");
		this.consultarSedesCriterio();
		this.consultarInicioTemporadaEmpresa();
	}
	
	cambiarSedeCriterio()
	{
		this.consultarDepartamentosCriterio();
	}
	
	
	cambiarDepartamentoCriterio()
	{
		this.consultarUsuariosCriterio();
	}
	
	consultarUsuariosCriterio()
	{
		this.cargandoOpciones("#usuarioSelectCriterio");
		this.presentador.consultarUsuariosCriterio();
	}
	
	
	consultarSedesCriterio()
	{
		this.cargandoOpciones("#sedeSelectCriterio");
		this.presentador.consultarSedesCriterio();
	}
	
	consultarDepartamentosCriterio()
	{
		this.cargandoOpciones("#departamentoSelectCriterio");
		this.cargandoOpciones("#usuarioSelectCriterio");
		this.presentador.consultarDepartamentosCriterio();
	}
	
	
	
	cargarSupervisores(select, registros, modo, modeloEdicion, campo, texto)
	{
		$(select).empty();
		if(texto!=null)
		{
			if(texto=="")
				$(select).append($('<option></option>').val("").html("-Seleccione"));
			else 
				$(select).append($('<option></option>').val("").html(texto));
		}
		$.each(registros, function(i, p) 
		{
		    $(select).append($('<option></option>').val(p.id).html(p.nombre + " " + p.apellido));
		});
		if(modo==Modo.CAMBIO && modeloEdicion!=null)
		{
			var id = modeloEdicion[campo];
			$(select).val(id);
		}
	}
	
	eliminar(texto)
	{ 
		if(texto==undefined)
			texto ="Se eliminar\u00e1n los resultados de la capacitación <label>" +this._registroSeleccionado.titulo +"</label> para el usuario <label>" + this._registroSeleccionado.nombreCompleto +"</label>";
		var _this = this;
		swal({
	            title: "\u00bfEst\u00E1 seguro de eliminar?",
	            text: texto,
	            html : true,
	            type: "warning",
	            showCancelButton: true,
	            confirmButtonColor: "#DD6B55",
	            confirmButtonText: "Si, eliminar!!",
	            cancelButtonText: "No",
	            closeOnConfirm: false,
	            closeOnCancel: true,
	            showLoaderOnConfirm: true,
	        },
	        function(isConfirm)
	        {
	            if (isConfirm) 
	            {
	            	 setTimeout(function(){
	            		 _this.presentador.eliminarCursoUsuario();
	 	            }, 1000);
	            }
	        });
	}
	
	inicializarEventosBotonesTabla(tbody, table, nombresCamposLlave)
	{
		var _this = this;
		$(tbody).on("click", "button.eliminar", function()
		{
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

		    _this._registroSeleccionado  = table.row( tr ).data();
			
			
			if (_this._registroSeleccionado != undefined)
			{
				_this._llaves = _this.copiarPropiedadesObjeto(_this._registroSeleccionado, ["usuarioId", "cursoId"]);
				_this.eliminar();
			}
		});
		
		$(tbody).on("click", "button.lecciones", function()
		{
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

		    _this._registroSeleccionado  = table.row( tr ).data();
			
			
			if (_this._registroSeleccionado != undefined)
			{
				_this._llaves = _this.copiarPropiedadesObjeto(_this._registroSeleccionado, ["usuarioId", "cursoId"]);
				_this.mostrarLecciones();
			}
		});
		
	}
	
	mostrarLecciones()
	{
		var _this = this;
		this.mostrarFormularioHTML(HANDEL_API+"/html/modales/usuarios_cursos_lecciones.php",this, null, function()
		{
			$("#usuarioLabel").html(_this._registroSeleccionado.nombreCompleto);
			$("#capacitacionLabel").html(_this._registroSeleccionado.titulo);
			/*$("#estatusValidacionIcono").addClass(_this._recomendacionSeleccionada.estatusValidacionIcono);
			$("#estatusValidacionIcono").addClass(_this._recomendacionSeleccionada.estatusValidacionColor);
			$("#estatusValidacionLabel").html(_this._recomendacionSeleccionada.estatusValidacionDescripcion);
			if(_this._recomendacionSeleccionada.estatusValidacionId!=EstatusValidacion.VALIDADA)
			{
				$("#registrarAvanceButton").show();
				$("#registrarAvanceButton").click(function(){_this.mostrarFormularioAvance(Modo.ALTA);});
				if(_this.usuario.tipoUsuarioId==TipoUsuario.ADMINISTRADOR && _this._recomendacionSeleccionada.cumplimiento==100)
				{
					$("#validarRecomendacionButton").show();
					$("#validarRecomendacionButton").click(function(){_this.mostrarFormularioValidacion();});
					
				}
			}*/
			this.crearTablaLecciones();
			this.consultarLeccionesCapacitacionUsuario();
			
		},null,"leccionesModal","","", function()
		{
			
		},function()
		{
			//_this.consultarRecomendacionPorLlaves(false);
		});
	}
	
	crearTablaLecciones()
	{
		this.leccionesTabla = new Tabla("leccionesTabla");
		this.leccionesTabla.buscar = false;
		this.leccionesTabla.paginacion = true;
		this.leccionesTabla.alto = 250;
		this.leccionesTabla.columnas = [];
		
		//this.leccionesTabla.columnas.push({longitud:50, 	titulo:"Fecha de alta",   alias:"fechaAlta", alineacion:"C"});
		this.leccionesTabla.columnas.push({longitud:300, 	titulo:"Titulo",   alias:"titulo", alineacion:"L" });
		this.leccionesTabla.columnas.push({longitud:100, 	titulo:"Aprovechamiento",   alias:"porcentaje", alineacion:"C", itemRenderer:this.renderAprovechamiento});
		//this.leccionesTabla.columnas.push({longitud:300, 	titulo:"Comentario",   alias:"comentario", alineacion:"I",itemRenderer:this.renderComentarioAvance});
		//this.leccionesTabla.columnas.push({longitud:100, 	titulo:"Fecha de ultima modificación",   alias:"fechaModificacion", alineacion:"I",itemRenderer:this.renderFechaModificacionAvance } );
		
		//if(this.usuario.tipoUsuarioId == TipoUsuario.ADMINISTRADOR || this.usuario.tipoUsuarioId == TipoUsuario.COORDINADOR || this.usuario.tipoUsuarioId == TipoUsuario.SUPERVISOR)
		//{
		//	this.leccionesTabla.columnas.push({longitud:40, 	titulo:"",   	alias:"logo", alineacion:"D" ,itemRenderer:this.renderFotoUsuarioAvance});
		//	this.leccionesTabla.columnas.push({longitud:100, 	titulo:"Usuario",   alias:"usuarioNombreCompleto", alineacion:"I",itemRenderer:this.renderNombreUsuarioAvance});

		//}
		/*this.leccionesTabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-edit fa-lg'></span></button>"+
												"<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";

*/
		//this.leccionesTabla.columnas.push({longitud:30, 	titulo:"",  alias:"", alineacion:"C" ,itemRenderer:this.renderEditarAvance});
		this.leccionesTabla.columnas.push({longitud:30, 	titulo:"",  alias:"", alineacion:"C" ,itemRenderer:this.renderEliminarLeccion});

	
		this.leccionesTabla.textoTablaVacia = "";
		this.leccionesTabla.registros = [];
		
		
		
	}
	
	getAprovechamiento(renglon)
	{
		if(renglon.porcentaje==undefined)
			renglon.porcentaje = 0;
	
		var porcentajeAprovechamiento = parseFloat(renglon.porcentaje);
		var color ="";
		if(porcentajeAprovechamiento <= 70)
		{
			color = "red";
		}
		else if(porcentajeAprovechamiento > 70 && porcentajeAprovechamiento <=80)
		{
			color = "#e9a13d";
		}
		else if(porcentajeAprovechamiento > 80)
		{
			color = "green";
		}
		return "<span style='font-weight:bold;color:"+color+";' >"+porcentajeAprovechamiento+"%</span>";
	}
	
	renderAprovechamiento(renglon, type, set)
	{  
		return "<div id='cumplimientoAvanceTabla"+renglon.usuarioId+"_"+renglon.cursoId+"'>" +vista.getAprovechamiento(renglon) + "</div>";
	}
	
	renderPorcentaje(renglon, type, set)
	{  
		return "<div id='porcentajeTabla"+renglon.usuarioId+"_"+renglon.cursoId+"'>" +vista.getPorcentaje(renglon) + "</div>";
	}
	
	getPorcentaje(renglon)
	{    
		var porcentajeAprovechamiento = parseFloat(renglon.porcentaje);
		var label ="";
		if(porcentajeAprovechamiento >= 0 && porcentajeAprovechamiento < 51)
		{
			label = "text-red";
		}
		else if(porcentajeAprovechamiento >= 51 && porcentajeAprovechamiento < 100)
		{
			label = "text-yellow";
		}
		else if(porcentajeAprovechamiento >= 100)
		{
			label = "text-green";
		}
		var preguntas = renglon.correctas + "/" + renglon.total;
		return "<span data-toggle='tooltip' data-placemen='bottom' title='"+preguntas+"' style='font-weight:bold' class='"+label+"'>"+porcentajeAprovechamiento+"%</span>";
	}
	
	renderEliminarLeccion()
	{
		//if(vista._recomendacionSeleccionada!=null)
			return "<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";
		//else
		//	return "";
	}
	
	consultarLeccionesCapacitacionUsuario()
	{
		this.presentador.consultarLeccionesCapacitacionUsuario();
	}
	
	set lecciones(lecciones)
	{
		this.leccionesTabla.textoTablaVacia = "";
		this.leccionesTabla.registros = lecciones;
		this.inicializarEventosBotonesTablaLecciones("#" + this.leccionesTabla._id+"Table tbody",this.leccionesTabla.datatable.DataTable());
	}
	
	
	inicializarEventosBotonesTablaLecciones(tbody, table)
	{
		var _this = this;
		
		$(tbody).on("click", "button.eliminar", function()
		{
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

		    _this._leccionSeleccionada  = table.row( tr ).data();
			if (_this._leccionSeleccionada != undefined)
			{
				_this._llavesLeccion = _this.copiarPropiedadesObjeto(_this._leccionSeleccionada, ["id"]);
				//_this._llavesLeccion.recomendacionId = _this._llavesRecomendacion.id;
				//_this._llavesRecomendacion.recomendacionId = _this._llaves.id;
				_this.eliminarUsuarioCapacitacionLeccion();
			}
		});
		
		
	}
	
	
	eliminarUsuarioCapacitacionLeccion()
	{ 
		var _this = this;
		swal({
	            title: "\u00bfEst\u00E1 seguro de eliminar?",
	            text: "Se eliminar\u00e1 esta lecci\u00f3n!!</br><strong>"+ _this.leccionSeleccionada.titulo +"</strong>",
	            html:true,
	            type: "warning",
	            showCancelButton: true,
	            confirmButtonColor: "#DD6B55",
	            confirmButtonText: "Si, eliminar!!",
	            cancelButtonText: "No",
	            closeOnConfirm: false,
	            closeOnCancel: true,
	            showLoaderOnConfirm: true,
	        },
	        function(isConfirm)
	        {
	            if (isConfirm) 
	            {
	            	 setTimeout(function(){
	            		 _this.presentador.eliminarUsuarioCapacitacionLeccion();
	 	            }, 1000);
	            }
	        });
	}
	
	get registroSeleccionado()
	{
		return this._registroSeleccionado;
	}
	
	get leccionSeleccionada()
	{
		return this._leccionSeleccionada;
	}
	
	
	set modeloCapacitacion(modeloCapacitacion)
	{
		//modeloCapacitacion.porcentaje = 10;
		this._modeloCapacitacion = modeloCapacitacion;
		var id= '#porcentajeTabla'+this._modeloCapacitacion.usuarioId+"_"+this._modeloCapacitacion.cursoId;
		var texto = this.getPorcentaje(this._modeloCapacitacion);
		$(id).html(texto);
		this._registroSeleccionado.porcentaje = modeloCapacitacion.porcentaje;
		
	}
	
	consultarInicioTemporadaEmpresa()
	{
		this.presentador.consultarInicioTemporadaEmpresa();
	}
	
	
	
}
var vista = new ReportePersonalCapacitacionesVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});
