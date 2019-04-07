class SedesVista extends CatalogoVista
{		
	constructor()
	{	
		super();
		this.presentador = new SedesPresentador(this);
		this._urlFormulario = "html/formularios/sedes.html";
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
			{longitud:200, 	titulo:"Nombre",   alias:"nombre", alineacion:"I", class: "desc" }, 	
			{longitud:200, 	titulo:"Nombre corto",   alias:"nombreCorto", alineacion:"I"}, 
			{longitud:200, 	titulo:"Empresa",   alias:"empresaNombre", alineacion:"I" },	
			//{longitud:200, 	titulo:"Dirección",   alias:"direccion", alineacion:"I" }, 
			//{longitud:200, 	titulo:"País",   alias:"pais", alineacion:"I" },
		//	{longitud:200, 	titulo:"Estado",   alias:"estado", alineacion:"I" },
			//{longitud:200, 	titulo:"Ciudad",   alias:"ciudad", alineacion:"I" },
			{longitud:250, 	titulo:"Fecha de alta",   alias:"fechaAlta", alineacion:"I" },	
			{longitud:200, 	titulo:"Fecha de última modificación",   alias:"fechaModificacion", alineacion:"I" },
			{longitud:100, 	titulo:"Estatus",   alias:"estatus", alineacion:"D", itemRenderer:this.renderEstatus}
		]
	
		this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-edit fa-lg'></span></button>"+
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
            	 "empresaSelect": {required: !0},
                "nombreInput": {required: !0},
                "nombreCortoInput": {required: !0},
                "direccionInput": {required: !0},
                "tipoEmpresaSelect": {required: !0},
                "paisSelect": {required: !0},
                "estadoSelect": {required: !0},
                "ciudadSelect": {required: !0
                }
               
            },
            messages: {
            	 "empresaSelect": "Por favor ingrese un nombre",
                "nombreInput": "Por favor ingrese un nombre",
                "nombreCortoInput": "Por favor ingrese un nombre corto",
                "direccionInput": "Por favor ingrese una dirección",
                "tipoEmpresaSelect": "Por favor ingrese un tipo de empresa",
                "paisSelect": "Por favor ingrese un país",
                "estadoSelect": "Por favor ingrese un estado",
                "ciudadSelect": "Por favor ingrese una ciudad"
                	
                
            },
            submitHandler:function (form) {
            	 _this.guardar();
            }
        });
	}
//	
//	rendererBotones(registro)
//	{
//		var html="";
//		if(this._usuario!=null)
//		{
//			if(this._usuario.tipoUsuarioId == TipoUsuario.SUPERUSUARIO)
//			{
//				html+="<button class='item' data-toggle='tooltip' data-placement='top' title='Historial de creditos' style='background-color:#d3d60f;cursor:pointer' onclick='vista.verCreditos("+registro.id+")'>";
//				html+="<i class='fas fa-calendar-check-o' style='color:#ffffff;'></i>";
//				html+="</button>";
//			}
//		}
//		return html;
//	}
	
	
	
	agregar()
	{
		super.agregar();
		this.consultarEmpresas();
		this.consultarPaises();
	}
	
	editar(id)
	{
		super.editar(id);
		$('#nombreInput').focus();
	}
	
	
	set modelo(valor)
	{		
		this.modeloEdicion = valor;
		$('#nombreInput').val(this.modeloEdicion.nombre);
		$('#nombreCortoInput').val(this.modeloEdicion.nombreCorto);
		$('#direccionInput').val(this.modeloEdicion.direccion);
		$("input[name=estatus][value=" + this.modeloEdicion.estatus + "]").prop('checked', true);
		this.consultarEmpresas();
		this.consultarPaises();
	}
	
	get modelo()
	{
		 var modelo = 
		 {		
			 nombre:$('#nombreInput').val(),
			 nombreCorto:$('#nombreCortoInput').val(),
			 direccion:$('#direccionInput').val(),
			 empresaId:$('#empresaSelect').val(),
			 paisId:$('#paisSelect').val(),
			 estadoId:$('#estadoSelect').val(),
			 ciudadId:$('#ciudadSelect').val(),
			 estatus:$('#estatusRadio').is(':checked')?1:0
		 };
		 if(this.modo=="CAMBIO" && this.modeloEdicion!=null)
			 modelo.id = this.modeloEdicion.id;
		 return modelo;
	 }
	 

	
	datosValidos()
	{
		var nombre = $("#nombreInput"),
		nombreCorto = $("#nombreCortoInput"),
			direccion = $("#direccionInput"),
	        empresa = $("#empresaSelect"),
	        pais = $("#paisSelect"),
			estado = $("#estadoSelect"),
			ciudad = $("#ciudadSelect");
        
        var allFields = $( [] ).add(nombre).add(nombreCorto).add(direccion).add(empresa).add(pais).add(estado).add(ciudad);
        var tips = $( ".validateTips" );
		tips.text("");
		
		var valid = true;
		allFields.removeClass("ui-state-error");
		
		valid = valid && this.validaciones.checkValue( empresa, "empresa",tips );
		valid = valid && this.validaciones.checkValue( nombre, "nombre", tips );	
	    valid = valid && this.validaciones.checkValue( nombreCorto, "nombre corto", tips );	
	    valid = valid && this.validaciones.checkValue( direccion, "dirección", tips );
	    valid = valid && this.validaciones.checkValue( pais, "país", tips );
	    valid = valid && this.validaciones.checkValue( estado, "estado", tips );
	    valid = valid && this.validaciones.checkValue( ciudad, "ciudad", tips );
	    
		return valid;
	}	

	limpiarFormulario()
	{
		$('#nombreInput').val("");
		$('#nombreCortoInput').val("");
		$('#direccionInput').val("");
		this.cargandoOpciones('#empresaSelect');
		this.cargandoOpciones('#paisSelect');
		this.cargandoOpciones('#estadoSelect');
		this.cargandoOpciones('#ciudadSelect');
	}
	
	consultarEmpresas()
	{
		this.cargandoOpciones("#empresaSelect");
		this.presentador.consultarEmpresas();
	}
	
	consultarEmpresasCriterio()
	{
		this.cargandoOpciones("#empresaSelectCriterio");
		this.presentador.consultarEmpresasCriterio();
	}
	
	set empresas(registros)
	{		
		this.cargarOpciones('#empresaSelect', registros, this.modo, this.modeloEdicion, 'empresaId',"");
	}
	
	set empresasCriterio(registros)
	{		
		this.cargarOpciones('#empresaSelectCriterio', registros);
		this.consultar();
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

	consultarPaises()
	{
		this.cargandoOpciones("#paisSelect");
		this.presentador.consultarPaises();
	}
	
	set paises(registros)
	{		
		this.cargarOpciones('#paisSelect', registros, this.modo, this.modeloEdicion, 'paisId',"");
		
	}
	
	cambiarPais()
	{
		this.cargandoOpciones("#estadoSelect");
		this.cargandoOpciones("#ciudadSelect");
		this.consultarEstados();
	}
	
	consultarEstados()
	{
		this.presentador.consultarEstados();
	}
	
	set estados(registros)
	{	
		this.cargarOpciones('#estadoSelect', registros, this.modo, this.modeloEdicion, 'estadoId',"");
	}
	
	
	cambiarEstado()
	{
		this.consultarCiudades();
	}
	
	consultarCiudades()
	{
		this.cargandoOpciones("#ciudadSelect");
		this.presentador.consultarCiudades();
	}
	
	set ciudades(registros)
	{		
		
		this.cargarOpciones('#ciudadSelect', registros, this.modo, this.modeloEdicion, 'ciudadId',"");
	}
	
	verCreditos(sedeId)
	{
		var submitForm = this.getNewSubmitForm("creditos.php");
		this.createNewFormElement(submitForm, "sedeId", sedeId);	
	    submitForm.target= "_self";
	    submitForm.submit();
	}
	
}

var vista = new SedesVista(this);	
$(document).ready(function() 
{
	vista.inicializar();
});