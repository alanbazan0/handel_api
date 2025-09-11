class UsuariosVista extends CatalogoVista
{		
	constructor(ventana)
	{	
		super(ventana);
		this.presentador = new UsuariosPresentador(this);
		this._urlFormulario = "html/formularios/usuarios.php";
		var fecha = new Date();
		this._time = fecha.getTime();
	}
	
	get time()
	{
		return this._time;
	}

	inicializar()
	{
		super.inicializar(false);
		this.consultarEmpresasCriterio();
		
	}
	
	clickNotificacion(event)
	{	
		if(vista.toastrData!=null)
		{
			 if(vista.toastrData.codigoError==1451)
			 {
				vista.mostrarFormularioHTML(HANDEL_API+"/html/modales/usuario_relaciones.php",this, null, 
				function()
				{
					 var catalogos = vista.toastrData.valor.valor;
					 
					 var relaciones =  [];
					 
					 for(var i= 0; i < catalogos.length;  i++)
					 {
						 var catalogo = catalogos[i];
						 relaciones.push({catalogo: catalogo.nombre, numeroRegistros: catalogo.registros.length});
					 }
					 
					 this._relacionesTabla = new Tabla("relacionesTabla");
					 this._relacionesTabla.buscar = false;
					 this._relacionesTabla.paginacion = false;
					 this._relacionesTabla.columnas = [
						{longitud:50, 	titulo:"Catálogo",   alias:"catalogo", alineacion:"C"} ,
						{longitud:300, 	titulo:"Número de registros",   alias:"numeroRegistros", alineacion:"I" } ,
					]
					this._relacionesTabla.textoTablaVacia = "";
					this._relacionesTabla.registros = relaciones;
				});
			 }
		}
	}
	
	
	crearColumnasGrid()
	{
		this.tabla.columnas = [
			
			{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"D" },
			{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"D" ,itemRenderer:this.renderLogo},
			{longitud:200, 	titulo:"Nombre",   alias:"nombre", alineacion:"I",class: "desc" }, 
			{longitud:200, 	titulo:"Apellido",   alias:"apellido", alineacion:"I",class: "desc" }, 
			{longitud:200, 	titulo:"Nombre de usuario",   	alias:"nombreUsuario", alineacion:"I", classSpan:"block-email" }, 
			{longitud:100, 	titulo:"Número de empleado",   alias:"numeroEmpleado", alineacion:"I" },	
			{longitud:200, 	titulo:"Empresa",   alias:"empresaNombre", alineacion:"I" },	
			{longitud:200, 	titulo:"Sede",   alias:"sedeNombre", alineacion:"I" },	
			{longitud:100, 	titulo:"Puesto",   alias:"puestoNombre", alineacion:"I" },	
			{longitud:100, 	titulo:"Departamento",   alias:"departamentoNombre", alineacion:"I" },	
			{longitud:100, 	titulo:"Area",   alias:"areaNombre", alineacion:"I" },	
			{longitud:100, 	titulo:"Supervisor 1",   alias:"supervisor1Nombre", alineacion:"I" },	
			{longitud:100, 	titulo:"Supervisor 2",   alias:"supervisor2Nombre", alineacion:"I" },	
			{longitud:100, 	titulo:"Supervisor 3",   alias:"supervisor3Nombre", alineacion:"I" },	
			{longitud:100, 	titulo:"Tipo de usuario",   alias:"tipoUsuarioNombre", alineacion:"I" },
			{longitud:200, 	titulo:"Ultimo acceso",   alias:"ultimoAcceso", alineacion:"I",itemRenderer:this.renderUltimoAcceso },			
			{longitud:250, 	titulo:"Fecha de alta",   alias:"fechaAlta", alineacion:"I" },	
			{longitud:200, 	titulo:"Fecha de última modificación",   alias:"fechaModificacion", alineacion:"I" },
			{longitud:100, 	titulo:"Estatus",   alias:"estatus", alineacion:"D", itemRenderer:this.renderEstatus},
			{longitud:100, 	titulo:"SAHA",   alias:"permisoSAHA", alineacion:"D", itemRenderer:this.renderPermisoSAHA},
			{longitud:100, 	titulo:"SIVAH",   alias:"permisoSIVAH", alineacion:"D", itemRenderer:this.renderPermisoSIVAH},
			{longitud:100, 	titulo:"10 Y 7",   alias:"permiso10y7", alineacion:"D", itemRenderer:this.renderPermiso10y7},
			{longitud:100, 	titulo:"CAVI",   alias:"permisoCAVI", alineacion:"D", itemRenderer:this.renderpermisoCAVI},
			{longitud:100, 	titulo:"Perfil",   alias:"perfilNombre", alineacion:"I" }	
	
		]
		
		this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-edit fa-lg'></span></button>"+
										"<button data-toggle='tooltip' data-placemen='bottom' title='Reenviar correo de bienvenida'  type='button' class='reenviar btn-circle mr-0 botones-icon btn btn-sm float-left btn-success active'><span  data-toggle='tooltip' class='fa fa-envelope fa-lg'></span></button>" +
										"<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";

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
            	 "tipoUsuarioSelect": {required: !0},
                "nombreUsuarioInput": {required: !0},
                "contrasenaInput": {required: !0},
                "nombreInput": {required: !0},
                "apellidoInput": {required: !0},
                "empresaSelect": {required: !0},
                "sedeSelect": {required: !0},
                "puestoSelect": {required: !0},
                "departamentoSelect": {required: !0}
               
            },
            messages: {
            	 "tipoUsuarioSelect": "Por favor seleccione un tipo de usuario",
            	 "nombreUsuarioInput": "Por favor ingrese un nombre de usuario",
            	 "contrasenaInput": "Por favor ingrese una contraseña",
                "nombreInput": "Por favor ingrese un nombre",
                "apellidoInput": "Por favor ingrese un apellido",
                "empresaSelect": "Por favor seleccione una empresa",
                "sedeSelect": "Por favor seleccione una sede",
                "puestoSelect": "Por favor seleccione un puesto",
                "departamentoSelect": "Por favor seleccione un departamento"
                	
                
            },
            submitHandler:function (form) {
            	 _this.guardar();
            }
        });
	}
	
	inicializarEventosBotonesTabla(tbody, table, nombresCamposLlave)
	{
		var _this = this;
		super.inicializarEventosBotonesTabla(tbody, table, nombresCamposLlave);
		$(tbody).on("click", "button.reenviar", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

			_this._registroSeleccionado  = table.row( tr ).data();
			if (_this._registroSeleccionado != undefined)
			{
				_this._llaves = _this.copiarPropiedadesObjeto(_this._registroSeleccionado, ["id"]);
				_this.reenviarCorreo();
			}
		});
		$(tbody).on("click", "a.historial", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

			_this._registroSeleccionado  = table.row( tr ).data();
			if (_this._registroSeleccionado != undefined)
			{
				_this._llaves = _this.copiarPropiedadesObjeto(_this._registroSeleccionado, ["id"]);
				_this.mostrarHistorialAcceso();
			}
		});
	}
	
//	get registroSeleccionado()
//	{
//		var usuario = {
//				nombreUsuario =  this._registroSeleccionado.nombreUsuario,
//				nombre =  this._registroSeleccionado.nombre,
//				contrasena : this._registroSeleccionado.contrasena
//		}
//		return usuario;
//	}
	
	reenviarCorreo()
	{
		this.presentador.reenviarCorreo();
	}
	
	mostrarHistorialAcceso()
	{
		this.mostrarFormularioHTML(HANDEL_API+"/html/modales/historial_acceso.php",this, null, this.consultarCombosHistorial,null,"historialModal","","");

	}
	

	consultarCombosHistorial()
	{
		this.crearTablaHistorial();
	}
	
	crearTablaHistorial()
	{
		this._historialTabla = new Tabla("historialTabla");
		this._historialTabla.buscar = false;
		this._historialTabla.paginacion = false;
		this._historialTabla.columnas = [
			{longitud:50, 	titulo:"Fecha",   alias:"fecha", alineacion:"C"} ,
			{longitud:300, 	titulo:"Aplicación",   alias:"aplicacionId", alineacion:"I" } ,
			{longitud:300, 	titulo:"Versión",   alias:"aplicacionVersion", alineacion:"I" } ,
			{longitud:300, 	titulo:"IP",   alias:"ip", alineacion:"I"},
			{longitud:300, 	titulo:"Referer",   alias:"referer", alineacion:"I" } ,
			{longitud:300, 	titulo:"User Agent",   alias:"userAgent", alineacion:"I" } 
		
		]
		
	
		this._historialTabla.textoTablaVacia = "No hay historial de acceso";
		this._historialTabla.registros = [];
		
		this.consultarHistorialAcceso();
	}
	
	consultarHistorialAcceso()
	{
		this.presentador.consultarHistorialAcceso();
	}
	
	inicializarValidacionesFormularioInspector()
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
            	 "tipoUsuarioSelect": {required: !0},
                "contrasenaInput": {required: !0},
                "nombreInput": {required: !0},
                "apellidoInput": {required: !0},
                "empresaSelect": {required: !0},
                "sedeSelect": {required: !0},
                "puestoSelect": {required: !0},
                "areaSelect": {required: !0},
 				"departamentoSelect": {required: !0}


               
            },
            messages: {
            	 "tipoUsuarioSelect": "Por favor seleccione un tipo de usuario",
            	 "contrasenaInput": "Por favor ingrese una contraseña",
                "nombreInput": "Por favor ingrese un nombre",
                "apellidoInput": "Por favor ingrese un apellido",
                "empresaSelect": "Por favor seleccione una empresa",
                "sedeSelect": "Por favor seleccione una sede",
                "puestoSelect": "Por favor seleccione un puesto",
                "areaSelect": "Por favor seleccione un área",
				"departamentoSelect": "Por favor seleccione un departamento"
                	
                
            },
            submitHandler:function (form) {
            	 _this.guardar();
            }
        });
	}
	
	inicializarValidacionesFormularioCapacitado()
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
            	 "tipoUsuarioSelect": {required: !0},
                "contrasenaInput": {required: !0},
                "nombreInput": {required: !0},
                "apellidoInput": {required: !0},
                "empresaSelect": {required: !0},
                "sedeSelect": {required: !0},
                "puestoSelect": {required: !0},
                "perfilSelect": {required: !0},
				"departamentoSelect": {required: !0}
               
            },
            messages: {
            	 "tipoUsuarioSelect": "Por favor seleccione un tipo de usuario",
            	 "contrasenaInput": "Por favor ingrese una contraseña",
                "nombreInput": "Por favor ingrese un nombre",
                "apellidoInput": "Por favor ingrese un apellido",
                "empresaSelect": "Por favor seleccione una empresa",
                "sedeSelect": "Por favor seleccione una sede",
                "puestoSelect": "Por favor seleccione un puesto",
                "perfilSelect": "Por favor seleccione un perfil",
				"departamentoSelect": "Por favor seleccione un departamento"
                	
                
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
		contenido += "<center><img src='" + icono + "' style='width:30px;height:30px;'></img></center>";
	    return contenido;
	}
	
	renderUltimoAcceso(renglon, type, set)
	{    
		var fecha = new Date();
		var contenido = "";
		contenido += "<center><a href='#' class='historial'>"+renglon.ultimoAcceso+"</a></center>";
	    return contenido;
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
	
	renderpermisoCAVI(renglon, type, set)
	{    
		var contenido = "";
		if(renglon.permisoCAVI==1)
			contenido += "<center><span class='fa fa-check fa-lg text-success'></span></center>";
		else
			contenido += "<center><span class='fa fa-close fa-lg text-danger'></span></center>";
	    return contenido;
	}
	
	agregar()
	{
		super.agregar();
	
		
	}
	
	consultarCombos()
	{
		this.consultarTiposUsuario();
		this.consultarEmpresas();
		this.consultarDepartamentos();
		this.consultarPerfiles();
		this.consultarTiposSocioComercial();
		
	}
	
	consultarTiposSocioComercial()
	{
		
		this.cargandoOpciones("#tipoSocioComercialSelect");
		this.presentador.consultarTiposSocioComercial();
	}
	
	
	set tiposSocioComercial(registros)
	{
		//this.cargarOpciones('#tipoSocioComercialSelect', registros);
		
		var select ="#tipoSocioComercialSelect";
		
		$(select).empty();
		$.each(registros, function(i, p) 
		{
		    $(select).append($('<option></option>').val(p.id).html(p.nombre));
		});
		
		if(this.modeloEdicion!=null)
		{
			var sociosComercialesSeleccionados =[];
			if(this.modeloEdicion.tiposSocioComercial!=undefined)
			{
				$.each(this.modeloEdicion.tiposSocioComercial, function(i, p) 
				{
					sociosComercialesSeleccionados.push(p.tipoSocioComercialId);
				});
			}
			
			$(select).val(sociosComercialesSeleccionados);
		}
		
		$('#tipoSocioComercialSelect').select2();
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
		
		if(this.modeloEdicion.estatus==1)
			$("#estatusRadio").prop('checked', true);
		else
			$("#estatusRadio").prop('checked', false);
			
		$('#numeroEmpleadoInput').val(this.modeloEdicion.numeroEmpleado);
		
		if(this.modeloEdicion.permisoSAHA==1)
			$("#permisoSAHARadio").prop('checked', true);
		else
			$("#permisoSAHARadio").prop('checked', false);
		
		if(this.modeloEdicion.permisoSIVAH==1)
			$("#permisoSIVAHRadio").prop('checked', true);
		else
			$("#permisoSIVAHRadio").prop('checked', false);
		
		if(this.modeloEdicion.permiso10y7==1)
			$("#permiso10y7Radio").prop('checked', true);
		else
			$("#permiso10y7Radio").prop('checked', false);
		this.consultarCombos();
		
		if(this.modeloEdicion.permisoCAVI==1)
			$("#permisoCAVIRadio").prop('checked', true);
		else
			$("#permisoCAVIRadio").prop('checked', false);
		
		if(this.modeloEdicion.recursosHumanos==1)
			$("#recursosHumanosRadio").prop('checked', true);
		else
			$("#recursosHumanosRadio").prop('checked', false);
			
		if(this.modeloEdicion.verificador==1)
			$("#verificadorRadio").prop('checked', true);
		else
			$("#verificadorRadio").prop('checked', false);
		
		if(this.modeloEdicion.visualizarAuditoriasSociosComerciales==1)
			$("#auditoriasSociosComercialesRadio").prop('checked', true);
		else
			$("#auditoriasSociosComercialesRadio").prop('checked', false);
		
		this.cambiarPermisoCAVI();
		this.cambiarPermisoSAHA();
		
		$('#urlDocumentosInput').val(this.modeloEdicion.urlDocumentos);
		
		this.reemplazaUsuarioId = valor.reemplazaUsuarioId;
		 
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
			 departamentoId:$('#departamentoSelect').val(),
			 supervisor1Id:$('#supervisor1Select').val(),
			 supervisor2Id:$('#supervisor2Select').val(),
			 supervisor3Id:$('#supervisor3Select').val(),
			 tipoUsuarioId:$('#tipoUsuarioSelect').val(),
			 estatus:$('#estatusRadio').is(':checked')?1:0,
			 permisoSAHA:$('#permisoSAHARadio').is(':checked')?1:0,
			 permisoSIVAH:$('#permisoSIVAHRadio').is(':checked')?1:0,
		 	 permiso10y7:$('#permiso10y7Radio').is(':checked')?1:0,
		 	 permisoCAVI:$('#permisoCAVIRadio').is(':checked')?1:0,
		     perfilId:$('#perfilSelect').val(),
		     recursosHumanos:$('#recursosHumanosRadio').is(':checked')?1:0,
		     numeroEmpleado:$('#numeroEmpleadoInput').val(),
		     verificador:$('#verificadorRadio').is(':checked')?1:0,
		     urlDocumentos:$('#urlDocumentosInput').val(),
		     visualizarAuditoriasSociosComerciales:$('#auditoriasSociosComercialesRadio').is(':checked')?1:0,
		     tiposSocioComercial: this.tiposSocioComercial,
		     reemplazaUsuarioId: $("#reemplazaUsuarioSelect").val()
		 };
		 if(this.modo=="CAMBIO" && this.modeloEdicion!=null)
			 modelo.id = this.modeloEdicion.id;
		 return modelo;
	 }
	 
	get tiposSocioComercial()
	{
		var sociosComerciales=[];
		var sociosComercialesSeleccionados  = $("#tipoSocioComercialSelect").val();
		if(sociosComercialesSeleccionados !=undefined)
		{
			for(var i = 0; i < sociosComercialesSeleccionados.length ; i++)
			{
				var socioComercialSeleccionado = sociosComercialesSeleccionados[i];
				var socioComercial = new Object();
				//responsable.id = i + 1;
				socioComercial.tipoSocioComercialId = socioComercialSeleccionado;
				sociosComerciales.push(socioComercial);
			}
		}
		return sociosComerciales;
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
		this.cargandoOpciones('#reemplazaUsuarioSelect');
		
	}
	
	consultarEmpresas()
	{
		this.cargandoOpciones("#empresaSelect");
		this.presentador.consultarEmpresas();
	}
	
	consultarDepartamentos()
	{
		this.cargandoOpciones("#departamentoSelect");
		this.presentador.consultarDepartamentos();
	}
	
	consultarPerfiles()
	{
		this.cargandoOpciones("#perfilSelect");
		this.presentador.consultarPerfiles();
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
		this.cargarOpciones('#empresaSelect', registros, this.modo, this.modeloEdicion, 'empresaId',"");
		if(this.modo==Modo.ALTA)
			$("#empresaSelect").val($("#empresaSelectCriterio").val());
	}
	
	set departamentos(registros)
	{		
		this.cargarOpciones('#departamentoSelect', registros, this.modo, this.modeloEdicion, 'departamentoId',"");
//		if(this.modo==Modo.ALTA)
//			$("#empresaSelect").val($("#empresaSelectCriterio").val());
	}
	
	set perfiles(registros)
	{		
		this.cargarOpciones('#perfilSelect', registros, this.modo, this.modeloEdicion, 'perfilId',"");
	}
	
	cambiarEmpresa()
	{
		this.cargandoOpciones("#sedeSelect");
		this.cargandoOpciones("#puestoSelect");
		this.cargandoOpciones("#areaSelect");
		this.cargandoOpciones("#supervisor1Select");
		this.cargandoOpciones("#supervisor2Select");
		this.cargandoOpciones("#supervisor3Select");
		this.cargandoOpciones("#reemplazaUsuarioSelect");
	
		this.consultarSedes();
		
		//this.consultarPuestos();
		this.consultarSupervisores();
		this.consultarUsuariosReemplaza();
	}
	
	cambiarPermisoCAVI()
	{
		 var permisoCAVI=$('#permisoCAVIRadio').is(':checked')?1:0;
		if(permisoCAVI)
			$('#perfilGroup').fadeIn();
		else
			$('#perfilGroup').fadeOut();
	}
	
	cambiarPermisoSAHA()
	{
		 var permisoSAHA=$('#permisoSAHARadio').is(':checked')?1:0;
		if(permisoSAHA)
			$('#verificadorGroup').fadeIn();
		else
			$('#verificadorGroup').fadeOut();
	}
	
	cambiarTipoUsuario()
	{
		var tipo = $('#tipoUsuarioSelect').val();
		
		var validator = $("#formulario").validate();
		validator.destroy();
		
		if(tipo==TipoUsuario.INSPECTOR)
		{
//			if(this.modo==Modo.ALTA)
//			{
//				$("#permiso10y7Radio").prop('checked', true);
//			}
			$('#nombreUsuarioDiv').hide();
			if($('#contrasenaInput').val()=="")
			{
				var contrasena = this.generarContrasenaNumerica(4);
				$('#contrasenaInput').val(contrasena);
			}
			this.inicializarValidacionesFormularioInspector();
		}
		else if(tipo==TipoUsuario.CAPACITADO)
		{
//			if(this.modo==Modo.ALTA)
//			{
//				$("#permisoCAVIRadio").prop('checked', true);
//			}
			//$('#nombreUsuarioDiv').hide();
			if($('#contrasenaInput').val()=="")
			{
				var contrasena = this.generarContrasenaNumerica(4);
				$('#contrasenaInput').val(contrasena);
			}
			this.inicializarValidacionesFormularioCapacitado();
		}
		else
		{
			$('#nombreUsuarioDiv').show();
			if($('#contrasenaInput').val()=="")
			{
				var contrasena = this.generarContrasena(10);
				$('#contrasenaInput').val(contrasena);
			}
			this.inicializarValidacionesFormulario();
		}
		
		
		var ayudaTipoUsuario = this.getAyudaTipoUsuario(tipo);
		$("#tipoUsuarioSelect").attr("data-original-title",ayudaTipoUsuario);
		$("#tipoUsuarioSelect").tooltip();
	}
	
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
		if(tipo==TipoUsuario.ADMINISTRADOR)
		{
			ayuda  ="El administrador tiene el control completo de la plataforma.";
		}
		else if(tipo==TipoUsuario.USUARIO)
		{
			ayuda  ="Un usuario es el trabajador que subirá las evidencias en SAHA";
		}
		else if(tipo==TipoUsuario.INSPECTOR)
		{
			ayuda  ="Un inspector es el trabajador que realizará las inspecciones en 10y7.";
		}
		else if(tipo==TipoUsuario.CAPACITADO)
		{
			ayuda  ="Un capacitado es el trabajador que tomará las capacitaciones en CAVI.";
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
		if(this.modo==Modo.ALTA)
			$("#sedeSelect").val($("#sedeSelectCriterio").val());
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
	
	consultarUsuariosReemplaza()
	{
		this.presentador.consultarUsuariosReemplaza();	
	}
	
	set supervisores1(registros)
	{
		this.cargarSupervisores('#supervisor1Select', registros, this.modo, this.modeloEdicion, 'supervisor1Id',"");

	}
	
	set reemplazaUsuario(registros)
	{
		this.cargarSupervisores('#reemplazaUsuarioSelect', registros, this.modo, this.modeloEdicion, 'reemplazaUsuarioId',"");

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
			nombre:$('#nombreInputCriterio').val()
		 }
		 return criteriosSeleccion;
	}	
	
	consultarEmpresasCriterio()
	{
		this.cargandoOpciones("#empresaSelectCriterio");
		this.presentador.consultarEmpresasCriterio();
	}
	
	set empresasCriterio(registros)
	{		
		this.cargarOpciones('#empresaSelectCriterio', registros);
		//this.consultar();
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
			var selected = $(select +" option[value='"+id+"']");
			if(selected.length==0)
			{	
				if(texto=="")
				{
					$(select).prop('selectedIndex',0);
				}
					
			}
			
		}
	}
	
	cambiarEmpresaCriterio()
	{
		this.consultarSedesCriterio();
	}
	
	consultarSedesCriterio()
	{
		this.cargandoOpciones("#sedeSelectCriterio");
		this.presentador.consultarSedesCriterio();
	}
	
	set sedesCriterio(registros)
	{		
		this.cargarOpciones('#sedeSelectCriterio', registros);
		/*if(this.consultoGrid==false)
		{
			this.consultar();
			this.consultoGrid=true;
		}*/
	}
	
	set historialAcceso(historialAcceso)
	{
		this._historialTabla.registros = historialAcceso;
	}
	
}
var vista = new UsuariosVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});
