class AreasVista extends CatalogoVista	
{		
	constructor(ventana)
	{	
		super(ventana);
		this.presentador = new AreasPresentador(this);
		this._urlFormulario = "html/formularios/areas.php";
		this.consultoGrid = false;
		
	}
	
	inicializar()
	{
		super.inicializar();
		this.consultarEmpresasCriterio();
	}
	
//	onLoad()
//	{
//		this.inicializarEliminar();
//		this.crearColumnasGrid();
//		this.consultarEmpresasCriterio();
//	}
	
	crearColumnasGrid()
	{
		this.tabla._columnas = [
			{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"D" },
			{longitud:200, 	titulo:"Nombre",   alias:"nombre", alineacion:"I", class:"desc" }, 
			{longitud:200, 	titulo:"Empresa",   alias:"empresaNombre", alineacion:"I" },		
			{longitud:200, 	titulo:"Sede",   alias:"sedeNombre", alineacion:"I" },		
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
                "sedeSelectInput": {required: !0},
                "tipoAreaSelect": {required: !0},
                "nombreInput": {required: !0}
               
            },
            messages: {
            	 "empresaSelect": "Por favor ingrese una empresa",
            	 "sedeSelect": "Por favor ingrese una sede",
            	 "tipoAreaSelect": "Por favor ingrese un tipo de area",
                "nombreInput": "Por favor ingrese un nombre"
                	
                
            },
            submitHandler:function (form) {
            	 _this.guardar();
            }
        });
	}
	
//	agregar()
//	{
//		super.agregar();
//		
//		
//	}
	
	consultarCombos()
	{
		 setTimeout(function (){
			 $('#nombreInput').focus();
		    }, 1000);
		
		this.consultarEmpresas();
		this.consultarTiposArea();
		
	}
	
	editar(id)
	{
		super.editar(id);
		
		$('#nombreInput').focus();
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
	
	set modelo(valor)
	{		
		this.modeloEdicion = valor;
		$('#nombreInput').val(this.modeloEdicion.nombre);
		$("input[name=estatus][value=" + this.modeloEdicion.estatus + "]").prop('checked', true);
		this.consultarCombos();
	}
	
	get modelo()
	{
		 var modelo = 
		 {		
			 nombre:$('#nombreInput').val(),
			 empresaId:$('#empresaSelect').val(),
			 sedeId:$('#sedeSelect').val(),
			 tipoAreaId:$('#tipoAreaSelect').val(),
			 estatus:$('#estatusRadio').is(':checked')?1:0
		 };
		 if(this.modo=="CAMBIO" && this.modeloEdicion!=null)
			 modelo.id = this.modeloEdicion.id;
		 return modelo;
	 }
	 
	datosValidos()
	{
		var nombre = $("#nombreInput"),
	        empresa = $("#empresaSelect");
        
        var allFields = $( [] ).add(nombre).add(empresa);
        var tips = $( ".validateTips" );
		tips.text("");
		
		var valid = true;
		allFields.removeClass("ui-state-error");
		
	    valid = valid && this.validaciones.checkValue( nombre, "nombre", tips );
	    valid = valid && this.validaciones.checkValue( empresa, "empresa",tips );
	    
		return valid;
	}	

	limpiarFormulario()
	{
		$('#nombreInput').val("");
		this.cargandoOpciones('#empresaSelect');
	}
	
	consultarEmpresas()
	{
		this.cargandoOpciones("#empresaSelect");
		this.cargandoOpciones("#sedeSelect");
		this.presentador.consultarEmpresas();
	}
	
	consultarTiposArea()
	{
		this.cargandoOpciones("#tipoAreaSelect");
		this.presentador.consultarTiposArea();
	}

	set empresas(registros)
	{		
		this.cargarOpciones('#empresaSelect', registros, this.modo, this.modeloEdicion, 'empresaId',"");
		if(this.modo==Modo.ALTA)
			$("#empresaSelect").val($("#empresaSelectCriterio").val());
	}
	
	set tiposArea(registros)
	{		
		this.cargarOpciones('#tipoAreaSelect', registros, this.modo, this.modeloEdicion, 'tipoAreaId',"");
	}
	
	consultarEmpresasCriterio()
	{
		this.cargandoOpciones("#empresaSelectCriterio");
		this.cargandoOpciones("#sedeSelectCriterio");
		this.presentador.consultarEmpresasCriterio();
	}
	
	set empresasCriterio(registros)
	{		
		this.cargarOpciones('#empresaSelectCriterio', registros);
		//this.consultar();
	}
	
	cambiarEmpresaCriterio()
	{
		this.consultarSedesCriterio();
	}
	
	cambiarEmpresa()
	{
		this.cargandoOpciones("#sedeSelect");
		this.consultarSedes();
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
var vista = new AreasVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});
