class ReportePersonalVista extends CatalogoVista
{		
	constructor(ventana)
	{	
		super(ventana);
		this.presentador = new ReportePersonalPresentador(this);
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
		this.consultarDepartamentosCriterio();
		
		this.crearFechas();
		
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
			
			{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"D" ,itemRenderer:this.renderLogo},
			{longitud:200, 	titulo:"Nombre",   alias:"nombre", alineacion:"I",class: "desc" }, 
			{longitud:200, 	titulo:"Apellido",   alias:"apellido", alineacion:"I",class: "desc" },
			{longitud:100, 	titulo:"Número de empleado",   alias:"numeroEmpleado", alineacion:"I" },
			{longitud:200, 	titulo:"Nombre de usuario",   	alias:"nombreUsuario", alineacion:"I", classSpan:"block-email" }, 
			{longitud:200, 	titulo:"Empresa",   alias:"empresaNombre", alineacion:"I" },	
			{longitud:200, 	titulo:"Sede",   alias:"sedeNombre", alineacion:"I" },	
			//{longitud:100, 	titulo:"Puesto",   alias:"puestoNombre", alineacion:"I" },	
//			{longitud:100, 	titulo:"Area",   alias:"areaNombre", alineacion:"I" },	
			{longitud:100, 	titulo:"Departamento",   alias:"departamentoNombre", alineacion:"I" },	
			{longitud:100, 	titulo:"Capacitación",   alias:"capacitacion", alineacion:"I", itemRenderer: this.rendererCapacitacion },
			{longitud:100, 	titulo:"Fecha última capacitación",   alias:"fechaUltimaCapacitacion", alineacion:"I"},
			{longitud:100, 	titulo:"Aprovechamiento",   alias:"porcentaje", alineacion:"C",itemRenderer: this.rendererPorcentaje },
			{longitud:100, 	titulo:"Preguntas correctas",   alias:"correctas", alineacion:"C"},
			{longitud:100, 	titulo:"Total de preguntas",   alias:"total", alineacion:"C" },
			{longitud:100, 	titulo:"Total de preguntas contestadas",   alias:"preguntasContestadas", alineacion:"C" },
			{longitud:100, 	titulo:"Avance",   alias:"porcentajeAvance", alineacion:"C",itemRenderer: this.rendererPorcentajeAvance },
			{longitud:100, 	titulo:"Capacitaciones completadas",   alias:"capacitacionesTerminadas", alineacion:"C"},
			{longitud:100, 	titulo:"Total de capacitaciones",   alias:"totalCapacitaciones", alineacion:"C" },
			{longitud:100, 	titulo:"Completado",   alias:"capacitacionesCompletadas", alineacion:"I", itemRenderer: this.rendererTermino},
//			{longitud:100, 	titulo:"Supervisor 1",   alias:"supervisor1Nombre", alineacion:"I" },	
//			{longitud:100, 	titulo:"Supervisor 2",   alias:"supervisor2Nombre", alineacion:"I" },	
//			{longitud:100, 	titulo:"Supervisor 3",   alias:"supervisor3Nombre", alineacion:"I" },	
			//{longitud:100, 	titulo:"Tipo de usuario",   alias:"tipoUsuarioNombre", alineacion:"I" },
//			{longitud:200, 	titulo:"Ultimo acceso",   alias:"ultimoAcceso", alineacion:"I" },			
//			{longitud:250, 	titulo:"Fecha de alta",   alias:"fechaAlta", alineacion:"I" },	
//			{longitud:200, 	titulo:"Fecha de última modificación",   alias:"fechaModificacion", alineacion:"I" },
//			{longitud:100, 	titulo:"Estatus",   alias:"estatus", alineacion:"D", itemRenderer:this.renderEstatus}

	
		];
		
		var _this = this;
		
		 var buttonCommon = {
				   text:      '<i class="fa fa-file-excel-o"></i> Exportar',
			        exportOptions: {
			            format: {
			                body: function ( data, row, column, node ) {
			                	if(column==ArrayUtils.indexWithValues("alias",["logo"],_this.tabla.columnas))
			                	{
			                		return "";
			                	}
			                	else if(column==ArrayUtils.indexWithValues("alias",["capacitacion"],_this.tabla.columnas))
				                {
			                	   if(data.includes("fa-check"))
			                		   return "Si";
			                	   else
			                		   return "No";
				                }
			                	else if(column==ArrayUtils.indexWithValues("alias",["porcentaje"],_this.tabla.columnas))
				                {
			                	  return node.innerText;
				                }
			                	else if(column==ArrayUtils.indexWithValues("alias",["porcentajeAvance"],_this.tabla.columnas))
				                {
			                	  return node.innerText;
				                }
			                	else if(column==ArrayUtils.indexWithValues("alias",["capacitacionesCompletadas"],_this.tabla.columnas))
				                {
			                	   if(data.includes("fa-check"))
			                		   return "Si";
			                	   else
			                		   return "No";
				                }
			                	else
			                	   return data;
			                }
			            }
			        }
			    };
		
		
		 
//		 this.tabla.botones= [
//	            $.extend( true, {}, buttonCommon, {
//	                extend: 'excel',"className": 'btn btn-success' 
//	            } )
//	        ];
//		 
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
		var icono = HANDEL_API+ "/"+renglon.fotoPerfil+"?"+vista.time;
		contenido += "<center><img src='" + icono + "' style='width:30px;height:30px;border-radius:50%'></img></center>";
	    return contenido;
	}
	
	rendererCapacitacion(renglon, type, set)
	{    
		if(renglon.fechaUltimaCapacitacion!=null)
			return "<center><i class='fas fa-check text-green'></i></center>";
	    return "<center><i class='fas fa-times text-red'></i></center>";;
	}
	
//	rendererCapacitacion(renglon, type, set)
//	{    
//		if(renglon.fechaUltimaCapacitacion!=null)
//			return "<center><i class='fas fa-check text-green'></i></center>";
//	    return "";
//	}
//	
	rendererTermino(renglon, type, set)
	{    
		if(renglon.fechaUltimaCapacitacion!=null)
		{
			if(renglon.porcentajeAvance ==100 )
				return "<center><i class='fas fa-check text-green'></i></center>";
			else
				return "<center><i class='fas fa-times text-red'></i></center>";
		}
	    return "";
	}
	
	rendererPorcentaje(renglon, type, set)
	{    
		if(renglon.fechaUltimaCapacitacion!=null)
		{
			var cantidad = renglon.correctas + " / " + renglon.total;
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
			return "<span data-toggle='tooltip' data-placemen='bottom' title='"+cantidad+"' style='font-weight:bold' class='"+label+"'>"+porcentajeCumplimiento+"%</span>";
		}
		return "";
	}
	
	rendererPorcentajeAvance(renglon, type, set)
	{    
		if(renglon.fechaUltimaCapacitacion!=null)
		{
			var cantidad = renglon.capacitacionesTerminadas + " / " + renglon.totalCapacitaciones;
			var porcentajeCumplimiento = parseFloat(renglon.porcentajeAvance);
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
			return "<span data-toggle='tooltip' data-placemen='bottom' title='"+cantidad+"' style='font-weight:bold' class='"+label+"'>"+porcentajeCumplimiento+"%</span>";
		}
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
		//this.consultarPerfilesCriterio();
		this.consultarCursosCriterio();
	}
	
	set perfilesCriterio(registros)
	{
		this.cargarOpciones('#perfilSelectCriterio', registros);
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
			tipoReporte:$('#tipoReporteSelectCriterio').val(),
			fechaInicial: this._fechaInicial,
			fechaFinal: this._fechaFinal
		 }
		 return criteriosSeleccion;
	}	
	
	crearFecha(id)
	{
		 $( "#"+id ).datepicker();
	}
	
	crearFechas()
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
	
	consultarPerfilesCriterio()
	{
		this.cargandoOpciones("#perfilSelectCriterio");
		this.presentador.consultarPerfilesCriterio();
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
			if(this.usuario.tipoUsuarioId != TipoUsuario.ADMINISTRADOR)
			{
				this.consultar();
				this.consultoGrid=true;
			}
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
	
}
var vista = new ReportePersonalVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});
