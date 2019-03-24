class UsuariosVista extends CatalogoVista
{		
	constructor(ventana)
	{	
		super(ventana);
		this.presentador = new UsuariosPresentador(this);
		
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
			{longitud:200, 	titulo:"Nombre",   alias:"nombre", alineacion:"I",class: "desc" }, 
			{longitud:200, 	titulo:"Apellido",   alias:"apellido", alineacion:"I",class: "desc" }, 
//			{longitud:200, 	titulo:"Empresa",   alias:"empresaNombre", alineacion:"I" },	
//			{longitud:200, 	titulo:"Sede",   alias:"sedeNombre", alineacion:"I" },	
//			{longitud:100, 	titulo:"Puesto",   alias:"puestoNombre", alineacion:"I" },	
//			{longitud:100, 	titulo:"Area",   alias:"areaNombre", alineacion:"I" },	
//			{longitud:100, 	titulo:"Supervisor 1",   alias:"supervisor1Nombre", alineacion:"I" },	
//			{longitud:100, 	titulo:"Supervisor 2",   alias:"supervisor2Nombre", alineacion:"I" },	
//			{longitud:100, 	titulo:"Supervisor 3",   alias:"supervisor3Nombre", alineacion:"I" },	
			{longitud:100, 	titulo:"Tipo de usuario",   alias:"tipoUsuarioNombre", alineacion:"I" },
//			{longitud:200, 	titulo:"Ultimo acceso",   alias:"ultimoAcceso", alineacion:"I" },			
			{longitud:250, 	titulo:"Fecha de alta",   alias:"fechaAlta", alineacion:"I" },	
			{longitud:200, 	titulo:"Fecha de última modificación",   alias:"fechaModificacion", alineacion:"I" },
			{longitud:100, 	titulo:"Estatus",   alias:"estatus", alineacion:"D", itemRenderer:this.renderEstatus}
		]
		
		this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-edit fa-lg'></span></button>"+
										"<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";

		this.tabla.registros = [];	
	}

	agregar()
	{
		super.agregar();
		$('#nombreUsuarioInput').focus();
		
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
		this.consultarTiposUsuario();
		this.consultarEmpresas();
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
			 estatus:$('#estatusRadio').is(':checked')?1:0
		 };
		 if(this.modo=="CAMBIO" && this.modeloEdicion!=null)
			 modelo.id = this.modeloEdicion.id;
		 return modelo;
	 }
	 
	generarContrasena(longitud)
	{
	  var caracteres = "abcdefghijkmnpqrtuvwxyzABCDEFGHIJKLMNPQRTUVWXYZ0123456789!$%&/*+-";
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
	
	datosValidos()
	{
		var emailRegex = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
        
		var nombreUsuario = $("#nombreUsuarioInput"),
			contrasena = $("#contrasenaInput"),
			nombre = $("#nombreInput"),			
	        empresa = $("#empresaSelect"),
	        area = $("#areaSelect"),
	        puesto = $("#puestoSelect"),	       
	        tipoUsuario = $("#tipoUsuarioSelect");
        
        var allFields = $( [] ).add(nombreUsuario).add(contrasena).add(nombre).add(empresa).add(area).add(puesto).add(tipoUsuario);
        var tips = $( ".validateTips" );
		tips.text("");
		
		var valid = true;
		allFields.removeClass("ui-state-error");
		
		var tipo = $('#tipoUsuarioSelect').val();
		if(tipo!=TipoUsuario.INSPECTOR)
		{
			valid = valid && this.validaciones.checkValue( nombreUsuario, "nombre de usuario", tips );		
			valid = valid && this.validaciones.checkRegexp( nombreUsuario, emailRegex, "ejemplo. contacto@handel-sce.net",tips );
		}
	    valid = valid && this.validaciones.checkValue( contrasena, "contraseña", tips );
	    valid = valid && this.validaciones.checkValue( nombre, "nombre", tips );
	    //valid = valid && this.validaciones.checkValue( apellido, "apellido", tips );
	    valid = valid && this.validaciones.checkValue( tipoUsuario, "tipo de usuario",tips );
	    valid = valid && this.validaciones.checkValue( empresa, "empresa",tips );
	   // valid = valid && this.validaciones.checkValue( sede, "sede",tips );
	    valid = valid && this.validaciones.checkValue( puesto, "puesto",tips );
	    valid = valid && this.validaciones.checkValue( area, "area",tips );
	    
	    
		return valid;
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
		this.cargarOpciones('#empresaSelect', registros, this.modo, this.modeloEdicion, 'empresaId',"");
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
		if(tipo==TipoUsuario.INSPECTOR)
		{
			$('#nombreUsuarioDiv').hide();
			//$('#contrasenaDiv').hide();
			var contrasena = this.generarContrasenaNumerica(4);
			
			$('#contrasenaInput').val(contrasena);
		}
		else
		{
			$('#nombreUsuarioDiv').show();
			var contrasena = this.generarContrasena(10);
			$('#contrasenaInput').val(contrasena);
			
			//$('#contrasenaDiv').show();
		}
		var ayudaTipoUsuario = this.getAyudaTipoUsuario(tipo);
		$("#tipoUsuarioSelect").attr("data-original-title",ayudaTipoUsuario);
		$('[data-toggle="tooltip"]').tooltip("hide");
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
	
}
var vista = new UsuariosVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});
