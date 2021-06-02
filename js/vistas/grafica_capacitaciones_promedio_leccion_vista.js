class GraficaCapacitacionesPromedioLeccionVista extends CatalogoVista
{		
	constructor(ventana)
	{	
		super(ventana);
		this.presentador = new GraficaCapacitacionesPromedioLeccionPresentador(this);
		this._urlFormulario = "html/formularios/capacitados.php";
		
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
		this.consultarDepartamentosCriterio();
		
		
		//this.consultarDepartamentosCriterio();
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
			//{longitud:200, 	titulo:"Nombre de usuario",   	alias:"nombreUsuario", alineacion:"I", classSpan:"block-email" }, 
			{longitud:200, 	titulo:"Empresa",   alias:"empresaNombre", alineacion:"I" },	
			{longitud:200, 	titulo:"Sede",   alias:"sedeNombre", alineacion:"I" },	
			//{longitud:100, 	titulo:"Puesto",   alias:"puestoNombre", alineacion:"I" },	
//			{longitud:100, 	titulo:"Area",   alias:"areaNombre", alineacion:"I" },	
			{longitud:100, 	titulo:"Departamento",   alias:"departamentoNombre", alineacion:"I" },	
			//{longitud:100, 	titulo:"Capacitación",   alias:"capacitacion", alineacion:"I", itemRenderer: this.rendererCapacitacion },
			
			{longitud:100, 	titulo:"Aprovechamiento",   alias:"porcentaje", alineacion:"I",itemRenderer: this.rendererPorcentaje },
//			{longitud:100, 	titulo:"Supervisor 1",   alias:"supervisor1Nombre", alineacion:"I" },	
//			{longitud:100, 	titulo:"Supervisor 2",   alias:"supervisor2Nombre", alineacion:"I" },	
//			{longitud:100, 	titulo:"Supervisor 3",   alias:"supervisor3Nombre", alineacion:"I" },	
			//{longitud:100, 	titulo:"Tipo de usuario",   alias:"tipoUsuarioNombre", alineacion:"I" },
//			{longitud:200, 	titulo:"Ultimo acceso",   alias:"ultimoAcceso", alineacion:"I" },			
//			{longitud:250, 	titulo:"Fecha de alta",   alias:"fechaAlta", alineacion:"I" },	
//			{longitud:200, 	titulo:"Fecha de última modificación",   alias:"fechaModificacion", alineacion:"I" },
//			{longitud:100, 	titulo:"Estatus",   alias:"estatus", alineacion:"D", itemRenderer:this.renderEstatus}

	
		]
		
//		this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-edit fa-lg'></span></button>"+
//										"<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";

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
	
//	inicializarValidacionesFormularioInspector()
//	{
//		var _this = this;
//		jQuery("#formulario").validate({
//            ignore: [],
//            errorClass: "invalid-feedback animated fadeInDown",
//            errorElement: "div",
//            errorPlacement: function(e, a) {
//                jQuery(a).parents(".form-group > div").append(e)
//            },
//            highlight: function(e) {
//                jQuery(e).closest(".form-group").removeClass("is-invalid").addClass("is-invalid")
//            },
//            success: function(e) {
//                jQuery(e).closest(".form-group").removeClass("is-invalid"), jQuery(e).remove()
//            },
//            rules: {
//            	
//                "contrasenaInput": {required: !0},
//                "nombreInput": {required: !0},
//                "apellidoInput": {required: !0},
//                "empresaSelect": {required: !0},
//                "sedeSelect": {required: !0},
//                "puestoSelect": {required: !0},
//                "areaSelect": {required: !0}
//               
//            },
//            messages: {
//            	
//            	 "contrasenaInput": "Por favor ingrese una contraseña",
//                "nombreInput": "Por favor ingrese un nombre",
//                "apellidoInput": "Por favor ingrese un apellido",
//                "empresaSelect": "Por favor seleccione una empresa",
//                "sedeSelect": "Por favor seleccione una sede",
//                "puestoSelect": "Por favor seleccione un puesto",
//                "areaSelect": "Por favor seleccione un área"
//                	
//                
//            },
//            submitHandler:function (form) {
//            	 _this.guardar();
//            }
//        });
//	}

	renderLogo(renglon, type, set)
	{    
		var fecha = new Date();
		var contenido = "";
		var icono = HANDEL_API+ "/"+renglon.fotoPerfil+"?"+fecha.getTime();
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
	
	rendererPorcentaje(renglon, type, set)
	{    
		//if(renglon.fechaUltimaCapacitacion!=null)
		//{
			var porcentajeCumplimiento = parseFloat(renglon.porcentaje);
			var label ="";
			if(porcentajeCumplimiento >= 0 && porcentajeCumplimiento < 51)
			{
				label = "text-red";
			}
			else if(porcentajeCumplimiento >= 51 && porcentajeCumplimiento < 100)
			{
				label = "text-yellow";
			}
			else if(porcentajeCumplimiento >= 100)
			{
				label = "text-green";
			}
			return "<span style='font-weight:bold' class='"+label+"'>"+porcentajeCumplimiento+"%</span>";
		//}
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
		this.consultarCursosCriterio();
	}
	
	set cursosCriterio(registros)
	{
		this.cargarOpciones('#cursoSelectCriterio', registros,"",null, "id", null, "titulo");
		this.consultarEmpresasCriterio();
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
			cursoId : $('#cursoSelectCriterio').val(),
			tipoReporte: TipoReporte.CAPACITACION_INICIADA
		 }
		 return criteriosSeleccion;
	}	
	
	crearFecha(id)
	{
		 $( "#"+id ).datepicker();
	}
	
	crearFechas()
	{
	
				
				 $(function() 
				{
					 
						$.datepicker.regional = [];
						
						$.datepicker.regional['es'] = {
								 closeText: 'Cerrar',
								 prevText: '< Ant',
								 nextText: 'Sig >',
								 currentText: 'Hoy',
								 monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
								 monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
								 dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
								 dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sáb'],
								 dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
								 weekHeader: 'Sm',
								 dateFormat: 'dd/mm/yy',
								 firstDay: 1,
								 isRTL: false,
								 showMonthAfterYear: false,
								 yearSuffix: ''
								 };
						
								 $.datepicker.setDefaults($.datepicker.regional['es']);
					 
//				    $.datepicker._updateDatepicker_original = $.datepicker._updateDatepicker;
//				    $.datepicker._updateDatepicker = function(inst) {
//				        $.datepicker._updateDatepicker_original(inst);
//				        var afterShow = this._get(inst, 'afterShow');
//				        if (afterShow)
//				            afterShow.apply((inst.input ? inst.input[0] : null));  // trigger custom callback
//				    }
				    
				    $( "#fechaInicialInputCriterio" ).datepicker();
				    
				    $( "#fechaFinalInputCriterio" ).datepicker();
				});
			 
			
//				
//				
//				var hoy = new Date();
//				var manana = new Date();
//				manana.setDate(hoy.getDate() + 1);
//				
//				var dd = manana.getDate();
//				var mm = manana.getMonth()+1; 
//				var yyyy = manana.getFullYear();
//				
//				if(dd<10) 
//				{
//				    dd='0'+dd;
//				} 
//
//				if(mm<10) 
//				{
//				    mm='0'+mm;
//				} 
//				
//				var fecha =  dd+'/'+mm+'/'+yyyy;
//				
//				$("#fechaFinalInputCriterio").val(fecha);
				
	}
	
	
	
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
		if(this.consultoGrid==false)
		{
			this.consultar();
			this.consultoGrid=true;
		}
	}
	

	cambiarEmpresaCriterio()
	{
		//this.cargandoOpciones("#departamentoSelectCriterio");
		this.consultarSedesCriterio();
	}
	
	cambiarSedeCriterio()
	{
		//this.consultarDepartamentosCriterio();
	}
	
	
	consultarSedesCriterio()
	{
		this.cargandoOpciones("#sedeSelectCriterio");
		this.presentador.consultarSedesCriterio();
	}
	
	consultarDepartamentosCriterio()
	{
		this.cargandoOpciones("#departamentoSelectCriterio");
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
	
	
	set datos(porcentajesAreas)
	{
		
//		for(var i=0; i < porcentajesAreas.length;i++)
//		{
//			var registro = porcentajesAreas[i];
//			registro.tiempo = moment("2015-01-01").startOf('day').minutes(registro.tiempoVisto).format('H:mm');
//		}
		
		am4core.ready(function() {

			// Themes begin
			//am4core.useTheme(am4themes_kelly);
			am4core.useTheme(am4themes_animated);
			// Themes end

			// Create chart instance
			var chart = am4core.create("grafica", am4charts.XYChart);
			chart.scrollbarX = new am4core.Scrollbar();
			chart.data = porcentajesAreas;

			// Create axes
			var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
			categoryAxis.dataFields.category = "nombreId";
			categoryAxis.renderer.grid.template.location = 0;
			categoryAxis.renderer.minGridDistance = 30;
			categoryAxis.renderer.labels.template.horizontalCenter = "middle";
			categoryAxis.renderer.labels.template.verticalCenter = "middle";
			categoryAxis.renderer.labels.template.rotation = 315;
			categoryAxis.tooltip.disabled = true;
			categoryAxis.renderer.minHeight = 110;
			categoryAxis.renderer.labels.template.adapter.add("textOutput", function(text) {
				  return text.replace(/ \(.*/, "");
				});
			
			let label = categoryAxis.renderer.labels.template;
			label.wrap = true;

		
			

			var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
			valueAxis.renderer.minWidth = 50;
			valueAxis.min = 0;
			//valueAxis.maxPrecision = 0;
			valueAxis.max = 100;

			// Create series
			var series = chart.series.push(new am4charts.ColumnSeries());
			series.sequencedInterpolation = true;
			series.dataFields.valueY = "porcentaje";
			series.dataFields.categoryX = "nombreId";
			series.tooltipText = "{nombre}  : {valueY}% ({correctas}/{total})";
			series.columns.template.strokeWidth = 0;

			series.tooltip.pointerOrientation = "vertical";

			series.columns.template.column.cornerRadiusTopLeft = 10;
			series.columns.template.column.cornerRadiusTopRight = 10;
			series.columns.template.column.fillOpacity = 0.8;
			
			
			

			// on hover, make corner radiuses bigger
			var hoverState = series.columns.template.column.states.create("hover");
			hoverState.properties.cornerRadiusTopLeft = 0;
			hoverState.properties.cornerRadiusTopRight = 0;
			hoverState.properties.fillOpacity = 1;

			series.columns.template.adapter.add("fill", function(fill, target) 
			{
				if (target.dataItem.valueY >= 0 && target.dataItem.valueY < 51) 
				    return am4core.color("#dd4b39");
				else if (target.dataItem.valueY >= 51 && target.dataItem.valueY < 100)
					 return am4core.color("#f39c12");
				else if (target.dataItem.valueY >= 100)
					return am4core.color("#00a65a");
				else
					return fill;
			});
			
			

			// Cursor
			chart.cursor = new am4charts.XYCursor();
			
			chart.scrollbarX = new am4core.Scrollbar();
			chart.events.on("ready", function (e) 
				{
					try
					{
						var zoom = 6;
						//if(_this.porcentajesAreas.lengh>=zoom)
						//categoryAxis.zoomToIndexes(0, zoom);
					}
					catch(e)
					{
						
					}
					
				});

			}); // end am4core.ready()


	}
}
var vista = new GraficaCapacitacionesPromedioLeccionVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});
