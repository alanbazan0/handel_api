class UsuariosVista extends CatalogoVista
{		
	constructor(ventana)
	{	
		super(ventana);
		this.presentador = new UsuariosPresentador(this);
		this._urlFormulario = "html/formularios/usuarios.php";
		
	}

	inicializar()
	{
		super.inicializar();
		this.consultarEmpresasCriterio();
	}
	
	
	
	crearColumnasGrid()
	{
		this.tabla.columnas = [
			
			{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"D" },
			{longitud:200, 	titulo:"Nombre de usuario",   	alias:"nombreUsuario", alineacion:"I", classSpan:"block-email" }, 
			{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"D" ,itemRenderer:this.renderLogo},
			{longitud:200, 	titulo:"Nombre",   alias:"nombre", alineacion:"I",class: "desc" }, 
			{longitud:200, 	titulo:"Apellido",   alias:"apellido", alineacion:"I",class: "desc" }, 
			{longitud:200, 	titulo:"Empresa",   alias:"empresaNombre", alineacion:"I" },	
			{longitud:200, 	titulo:"Sede",   alias:"sedeNombre", alineacion:"I" },	
			{longitud:100, 	titulo:"Puesto",   alias:"puestoNombre", alineacion:"I" },	
			{longitud:100, 	titulo:"Area",   alias:"areaNombre", alineacion:"I" },	
			{longitud:100, 	titulo:"Supervisor 1",   alias:"supervisor1Nombre", alineacion:"I" },	
			{longitud:100, 	titulo:"Supervisor 2",   alias:"supervisor2Nombre", alineacion:"I" },	
			{longitud:100, 	titulo:"Supervisor 3",   alias:"supervisor3Nombre", alineacion:"I" },	
			{longitud:100, 	titulo:"Tipo de usuario",   alias:"tipoUsuarioNombre", alineacion:"I" },
			{longitud:200, 	titulo:"Ultimo acceso",   alias:"ultimoAcceso", alineacion:"I" },			
			{longitud:250, 	titulo:"Fecha de alta",   alias:"fechaAlta", alineacion:"I" },	
			{longitud:200, 	titulo:"Fecha de última modificación",   alias:"fechaModificacion", alineacion:"I" },
			{longitud:100, 	titulo:"Estatus",   alias:"estatus", alineacion:"D", itemRenderer:this.renderEstatus},
			{longitud:100, 	titulo:"SAHA",   alias:"permisoSAHA", alineacion:"D", itemRenderer:this.renderPermisoSAHA},
			{longitud:100, 	titulo:"SIVAH",   alias:"permisoSIVAH", alineacion:"D", itemRenderer:this.renderPermisoSIVAH},
			{longitud:100, 	titulo:"10 Y 7",   alias:"permiso10y7", alineacion:"D", itemRenderer:this.renderPermiso10y7}
	
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
                "areaSelect": {required: !0}
               
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
                "areaSelect": "Por favor seleccione un área"
                	
                
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
                "areaSelect": {required: !0}
               
            },
            messages: {
            	 "tipoUsuarioSelect": "Por favor seleccione un tipo de usuario",
            	 "contrasenaInput": "Por favor ingrese una contraseña",
                "nombreInput": "Por favor ingrese un nombre",
                "apellidoInput": "Por favor ingrese un apellido",
                "empresaSelect": "Por favor seleccione una empresa",
                "sedeSelect": "Por favor seleccione una sede",
                "puestoSelect": "Por favor seleccione un puesto",
                "areaSelect": "Por favor seleccione un área"
                	
                
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
		var icono = HANDEL_API+ "/"+renglon.fotoPerfil+"?"+fecha.getTime();
		contenido += "<center><img src='" + icono + "' style='width:30px;height:30px;'></img></center>";
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
	
	agregar()
	{
		super.agregar();
		//$('#nombreUsuarioInput').focus();
		
		
	}
	
	consultarCombos()
	{
		
		this.consultarTiposUsuario();
		this.consultarEmpresas();
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
		if(this.modeloEdicion.permisoSAHA)
			$("#permisoSAHARadio").prop('checked', true);
		else
			$("#permisoSAHARadio").prop('checked', false);
		if(this.modeloEdicion.permisoSIVAH)
			$("#permisoSIVAHRadio").prop('checked', true);
		else
			$("#permisoSIVAHRadio").prop('checked', false);
		if(this.modeloEdicion.permiso10y7)
			$("#permiso10y7Radio").prop('checked', true);
		else
			$("#permiso10y7Radio").prop('checked', false);
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
			 tipoUsuarioId:$('#tipoUsuarioSelect').val(),
			 estatus:$('#estatusRadio').is(':checked')?1:0,
			 permisoSAHA:$('#permisoSAHARadio').is(':checked')?1:0,
			 permisoSIVAH:$('#permisoSIVAHRadio').is(':checked')?1:0,
		 	 permiso10y7:$('#permiso10y7Radio').is(':checked')?1:0
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
//	
//	datosValidos()
//	{
//		var emailRegex = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
//        
//		var nombreUsuario = $("#nombreUsuarioInput"),
//			contrasena = $("#contrasenaInput"),
//			nombre = $("#nombreInput"),			
//	        empresa = $("#empresaSelect"),
//	        area = $("#areaSelect"),
//	        puesto = $("#puestoSelect"),	       
//	        tipoUsuario = $("#tipoUsuarioSelect");
//        
//        var allFields = $( [] ).add(nombreUsuario).add(contrasena).add(nombre).add(empresa).add(area).add(puesto).add(tipoUsuario);
//        var tips = $( ".validateTips" );
//		tips.text("");
//		
//		var valid = true;
//		allFields.removeClass("ui-state-error");
//		
//		var tipo = $('#tipoUsuarioSelect').val();
//		if(tipo!=TipoUsuario.INSPECTOR)
//		{
//			valid = valid && this.validaciones.checkValue( nombreUsuario, "nombre de usuario", tips );		
//			valid = valid && this.validaciones.checkRegexp( nombreUsuario, emailRegex, "ejemplo. contacto@handel-sce.net",tips );
//		}
//	    valid = valid && this.validaciones.checkValue( contrasena, "contraseña", tips );
//	    valid = valid && this.validaciones.checkValue( nombre, "nombre", tips );
//	    //valid = valid && this.validaciones.checkValue( apellido, "apellido", tips );
//	    valid = valid && this.validaciones.checkValue( tipoUsuario, "tipo de usuario",tips );
//	    valid = valid && this.validaciones.checkValue( empresa, "empresa",tips );
//	   // valid = valid && this.validaciones.checkValue( sede, "sede",tips );
//	    valid = valid && this.validaciones.checkValue( puesto, "puesto",tips );
//	    valid = valid && this.validaciones.checkValue( area, "area",tips );
//	    
//	    
//		return valid;
//	}	

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
		this.cargarOpciones('#empresaSelect', registros, this.modo, this.modeloEdicion, 'empresaId',"");
		if(this.modo==Modo.ALTA)
			$("#empresaSelect").val($("#empresaSelectCriterio").val());
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
	
	cambiarTipoUsuario()
	{
		var tipo = $('#tipoUsuarioSelect').val();
		
		var validator = $("#formulario").validate();
		validator.destroy();
		
		if(tipo==TipoUsuario.INSPECTOR)
		{
			$('#nombreUsuarioDiv').hide();
			if($('#contrasenaInput').val()=="")
			{
				var contrasena = this.generarContrasenaNumerica(4);
				$('#contrasenaInput').val(contrasena);
			}
			this.inicializarValidacionesFormularioInspector();
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
		$('[data-toggle="tooltip"]').tooltip("hide");
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
		this.consultar();
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
		if(this.consultoGrid==false)
		{
			this.consultar();
			this.consultoGrid=true;
		}
	}
	
}
var vista = new UsuariosVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});
