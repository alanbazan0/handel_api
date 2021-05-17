class PaisesVista extends CatalogoVista
{		
	constructor()
	{	
		super();
		this.presentador = new PaisesPresentador(this);
		this._urlFormulario = "html/formularios/paises.php";
	}
	

	
	crearColumnasGrid()
	{
		this.tabla.columnas = [
			{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"D" },
			{longitud:200, 	titulo:"Nombre",   alias:"nombre", alineacion:"I" }, 	
			{longitud:50, 	titulo:"Nivel de compromiso",   alias:"nivelCompromiso", alineacion:"C" , itemRenderer: this.rendererNivelCompomiso}, 	
			{longitud:50, 	titulo:"Implementación",   alias:"implementacion", alineacion:"C" , itemRenderer: this.rendererImplementacion }, 	
			{longitud:50, 	titulo:"Verificación",   alias:"verificacion", alineacion:"C" , itemRenderer: this.rendererVerificacion  }, 				
			{longitud:250, 	titulo:"Fecha de alta",   alias:"fechaAlta", alineacion:"I" },	
			{longitud:200, 	titulo:"Fecha de última modificación",   alias:"fechaModificacion", alineacion:"I" },
			{longitud:100, 	titulo:"Estatus",   alias:"estatus", alineacion:"D", itemRenderer:this.renderEstatus}
		]
		
		this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-edit fa-lg'></span></button>"+
		"<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";

		this.tabla.registros = [];
	}
	
	rendererNivelCompomiso(renglon, type, set)
	{    
		return "<span  style='font-weight:bold;' >"+renglon.nivelCompromiso+"%</span>";
	}
	
	rendererImplementacion(renglon, type, set)
	{    
		return "<span  style='font-weight:bold;' >"+renglon.implementacion+"%</span>";
	}
	
	rendererVerificacion(renglon, type, set)
	{    
		return "<span  style='font-weight:bold;' >"+renglon.verificacion+"%</span>";
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
                "nombreInput": {
                    required: !0
                }
            },
            messages: {
                "nombreInput": "Por favor ingrese un nombre"
                	
                
            },
            submitHandler:function (form) {
            	 _this.guardar();
            }
        });
	}
	
	
	get criteriosSeleccion()
	{
		 var criteriosSeleccion = 
		 {				    
			nombre:$('#nombreInputCriterio').val()
		 }
		 return criteriosSeleccion;
	}		
	
	set modelo(valor)
	{		
		this.modeloEdicion = valor;
		$('#nombreInput').val(this.modeloEdicion.nombre);
		if(this.modeloEdicion.estatus==1)
			$("#estatusRadio").prop('checked', true);
		else
			$("#estatusRadio").prop('checked', false);
		$('#nivelCompromisoInput').val(this.modeloEdicion.nivelCompromiso);
		$('#implementacionInput').val(this.modeloEdicion.implementacion);
		$('#verificacionInput').val(this.modeloEdicion.verificacion);
	}
	
	get modelo()
	{
		 var modelo = 
		 {		
			 nombre:$('#nombreInput').val(),			 
			 estatus:$('#estatusRadio').is(':checked')?1:0,
			nivelCompromiso: $('#nivelCompromisoInput').val(),
			implementacion: $('#implementacionInput').val(),
			verificacion: $('#verificacionInput').val()
		 };
		 if(this.modo=="CAMBIO" && this.modeloEdicion!=null)
			 modelo.id = this.modeloEdicion.id;
		 return modelo;
	 }
	 
	
	datosValidos()
	{
		var nombre = $("#nombreInput");
	        
        
        var allFields = $( [] ).add(nombre);
        var tips = $( ".validateTips" );
		tips.text("");
		
		var valid = true;
		allFields.removeClass("ui-state-error");
		
	    valid = valid && this.validaciones.checkValue( nombre, "nombre", tips );
	   
		return valid;
	}	

	limpiarFormulario()
	{
		$('#nombreInput').val("");
	}
	
	
	
	

	
}
var vista = new PaisesVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});

