class EmpresasVista extends CatalogoVista
{		
	constructor()
	{	
		super();
		this.presentador = new EmpresasPresentador(this);
		this._urlFormulario = "html/formularios/empresas.php";
		
	}
	
	
	crearColumnasGrid()
	{
		this.tabla.columnas = [
			{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"D" },
			{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"D" ,itemRenderer:this.renderLogo},
			{longitud:200, 	titulo:"Nombre",   	alias:"nombre", alineacion:"I" }, 
			{longitud:200, 	titulo:"Teléfono",   alias:"telefono", alineacion:"I" }, 	
			{longitud:200, 	titulo:"Tipo de empresa",   alias:"tipoEmpresa", alineacion:"I" }, 
			{longitud:200, 	titulo:"Dirección",   alias:"direccion", alineacion:"I" }, 
			{longitud:200, 	titulo:"País",   alias:"pais", alineacion:"I" },
			{longitud:200, 	titulo:"Estado",   alias:"estado", alineacion:"I" },
			{longitud:200, 	titulo:"Ciudad",   alias:"ciudad", alineacion:"I" },
			{longitud:200, 	titulo:"Corporativo",   alias:"corporativo", alineacion:"I" },				
			{longitud:250, 	titulo:"Fecha de alta",   alias:"fechaAlta", alineacion:"I" },	
			{longitud:200, 	titulo:"Fecha de última modificación",   alias:"fechaModificacion", alineacion:"I" },
			{longitud:100, 	titulo:"Estatus",   alias:"estatus", alineacion:"D", itemRenderer:this.renderEstatus}
		]
		
		this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-edit fa-lg'></span></button>"+
								"<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";
	
		this.tabla.registros = [];

	}
	
//	datosValidos()
//	{
//		var nombre = $("#nombreInput"),
//		 nombreCorto = $("#nombreCortoInput"),
//			direccion = $("#direccionInput"),
//			telefono = $("#telefonoInput"),
//			tipoEmpresa = $("#tipoEmpresaSelect"),
//			pais = $("#paisSelect"),
//			estado = $("#estadoSelect"),
//			ciudad = $("#ciudadSelect");		
//        
//        var allFields = $( [] ).add(nombre).add(nombreCorto).add(direccion).add(telefono).add(tipoEmpresa).add(pais).add(estado).add(ciudad);
//        var tips = $( ".validateTips" );
//		tips.text("");
//		
//		var valid = true;
//		allFields.removeClass("ui-state-error");
//		
//		valid = valid && this.validaciones.checkValue( nombre, "nombre", tips );	
//		valid = valid && this.validaciones.checkValue( nombreCorto, "nombre corto", tips );	
//		valid = valid && this.validaciones.checkValue( telefono, "teléfono", tips );
//		valid = valid && this.validaciones.checkValue( tipoEmpresa, "tipo de empresa", tips );
//	    valid = valid && this.validaciones.checkValue( direccion, "dirección", tips );
//	    valid = valid && this.validaciones.checkValue( pais, "país", tips );
//	    valid = valid && this.validaciones.checkValue( estado, "estado", tips );
//	    valid = valid && this.validaciones.checkValue( ciudad, "ciudad", tips );
//	   
//	    
//		return valid;
//	}	
	
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
                "nombreInput": {
                    required: !0
                },
                "nombreCortoInput": {
                    required: !0
                },
                "direccionInput": {
                    required: !0
                },
                "telefonoInput": {
                    required: !0
                },
                "tipoEmpresaSelect": {
                    required: !0
                },
                "paisSelect": {
                    required: !0
                },
                "estadoSelect": {
                    required: !0
                },
                "ciudadSelect": {
                    required: !0
                }
               
            },
            messages: {
                "nombreInput": "Por favor ingrese un nombre",
                "nombreCortoInput": "Por favor ingrese un nombre corto",
                "direccionInput": "Por favor ingrese una dirección",
                "telefonoInput": "Por favor ingrese un teléfono",
                "tipoEmpresaSelect": "Por favor ingrese un tipo de empresa",
                "paisSelect": "Por favor ingrese un país",
                "estadoSelect": "Por favor ingrese un estao",
                "ciudadSelect": "Por favor ingrese una ciudad"
                	
                
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
		var icono = HANDEL_API+ "/php/logos_empresas/" + renglon.icono+"?"+fecha.getTime();
		contenido += "<center><img src='" + icono + "' style='width:30px;height:30px;'></img></center>";
	    return contenido;
	}
	
	cambiarLogo(input)
	{
		if (input.files && input.files[0]) 
		{
            var reader = new FileReader();

            reader.onload = function (e)
            {
                $('#logoImage').attr('src', e.target.result);
            };
            reader.readAsDataURL(input.files[0]);
        }
	}
	
	get logo()
	{
		var contenedorArchivos = $("#file") ;
		if(contenedorArchivos.length>0)
		{
			if(contenedorArchivos[0].files.length>0)
				return contenedorArchivos[0].files[0];
		}
		return null;
	}
	
	

	
	agregar()
	{
		super.agregar();
		
		
	}
	
	consultarCombos()
	{
		this.consultarTiposEmpresa();
		this.consultarPaises();
		this.consultarCorporativos();
		this.consultarAdministradores();
	}
	
	editar(id)
	{
		super.editar(id);
		$('#logoImage').hide();
		$('#nombreInput').focus();				

				
	}
	
//	consultar()
//	{	
//		this.presentador.consultar();
//	}	
	
//	guardar()
//	{		
//		 if(this.datosValidos())
//		 {
//			if(this.modo=='ALTA')
//				this.presentador.insertar();
//			else
//				this.presentador.actualizar();
//		 }		
//		
//	}
//	
//	btnSalir_onClick()
//	{
//		var confirmacion = confirm("¿Esta seguro que desea salir?")
//	    if (confirmacion)
//	    	{
//		    	
//	    	}
//	}
//	
//	btnSalirFormulario_onClick()
//	{		
//		this.salirFormulario();
//	}	

//	get llaves()
//	{
////		var llaves =
////		{
////			id:this.grid._selectedItem.id	
////		}
////		return llaves;
//		return this._llaves;
//	}
	
//	get criteriosSeleccion()
//	{
//		 var criteriosSeleccion = 
//		 {				    
//			nombre:$('#nombreInputCriterio').val()
//			
//		 }
//		 return criteriosSeleccion;
//	}		

//	set datos(datos)
//	{
//		this.tabla.registros = datos;	
//		this.tabla.renderizar();
//	}
	
	set modelo(valor)
	{		
		this.modeloEdicion = valor;
		$('#nombreInput').val(this.modeloEdicion.nombre);
		$('#nombreCortoInput').val(this.modeloEdicion.nombreCorto);
		$('#direccionInput').val(this.modeloEdicion.direccion);
		$('#telefonoInput').val(this.modeloEdicion.telefono);		
		$("input[name=estatus][value=" + this.modeloEdicion.estatus + "]").prop('checked', true);
		$('#logoImage').attr('src', HANDEL_API + "/php/logos_empresas/" + this.modeloEdicion.icono);
		$('#logoImage').show();
		this.consultarCombos();
	}
	
	get modelo()
	{
		 var modelo = 
		 {		
			 nombre:$('#nombreInput').val(),
			 nombreCorto:$('#nombreCortoInput').val(),
			 tipoEmpresaId:$('#tipoEmpresaSelect').val(),
			 direccion:$('#direccionInput').val(),
			 telefono:$('#telefonoInput').val(),
			 paisId:$('#paisSelect').val(),
			 estadoId:$('#estadoSelect').val(),
			 ciudadId:$('#ciudadSelect').val(),
			 corporativoId:$('#corporativoSelect').val(),
			 administradorId:$('#administradorSelect').val(),
			 estatus:$('#estatusRadio').is(':checked')?1:0
		 };
		 if(this.modo=="CAMBIO" && this.modeloEdicion!=null)
			 modelo.id = this.modeloEdicion.id;
		 return modelo;
	 }
	 
	
	

	limpiarFormulario()
	{
		$('#file').val("");
		$('#logoImage').attr("src","php/logos_empresas/default.png");
		$('#nombreInput').val("");
		$('#nombreCortoInput').val("");
		$('#direccionInput').val("");
		$('#telefonoInput').val("");
		this.cargandoOpciones('#tipoEmpresaSelect');
		this.cargandoOpciones('#paisSelect');
		this.cargandoOpciones('#estadoSelect');
		this.cargandoOpciones('#ciudadSelect');
		this.cargandoOpciones('#corporativoSelect');
		this.cargandoOpciones('#administradorSelect');
	}
	
	consultarAdministradores()
	{
		this.cargandoOpciones("#administradorSelect");
		this.presentador.consultarAdministradores();
	}
	
	consultarCorporativos()
	{
		this.cargandoOpciones("#corporativoSelect");
		this.presentador.consultarCorporativos();
	}
	
	consultarTiposEmpresa()
	{
		this.cargandoOpciones("#tipoEmpresaSelect");
		this.presentador.consultarTiposEmpresa();
	}	
	
	set tiposEmpresa(registros)
	{	
		this.cargarOpciones('#tipoEmpresaSelect', registros, this.modo, this.modeloEdicion, 'tipoEmpresaId');
		
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
	
	set corporativos(registros)
	{
		this.cargarOpciones('#corporativoSelect', registros, this.modo, this.modeloEdicion, 'corporativoId',"");
	}
	
	set administradores(registros)
	{
		this.cargarOpciones('#administradorSelect', registros, this.modo, this.modeloEdicion, 'administradorId',"","nombreCompleto");
	}
	
}
var vista = new EmpresasVista();
$(document).ready(function() 
{
	vista.inicializar();
});
