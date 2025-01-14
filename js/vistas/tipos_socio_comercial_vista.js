
class TiposSocioComercialVista extends CatalogoVista
{	
	constructor()
 	{
    	super();
     	this.presentador = new TiposSocioComercialPresentador(this);
    	this._urlFormulario = 'html/formularios/tipos_socio_comercial.php';
 	}

	crearColumnasGrid() 
	{
		this.tabla.columnas = [
			{ longitud: 50, titulo: "Id", alias: "id", alineacion: "D" },
			{ longitud: 200, titulo: "Nombre", alias: "nombre", alineacion: "I" },
			{ longitud: 200, titulo: "Fecha alta", alias: "fechaAlta", alineacion: "I" },
			{ longitud: 200, titulo: "Fecha modificacion", alias: "fechaModificacion", alineacion: "I" },
			{ longitud: 200, titulo: "Estatus", alias: "estatus", alineacion: "D", itemRenderer: this.renderEstatus }];


		this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-edit fa-lg'></span></button>" +
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
	}
	
	get modelo()
	{
		 var modelo = 
		 {		
			 nombre:$('#nombreInput').val(),
			 estatus:$('#estatusRadio').is(':checked')?1:0
		 };
		 if(this.modo=="CAMBIO" && this.modeloEdicion!=null)
			 modelo.id = this.modeloEdicion.id;
		 return modelo;
	}
	 
	limpiarFormulario()
	{
		$('#nombreInput').val("");
	}



}
var vista = new TiposSocioComercialVista(this);
$(document).ready(function()
{
  vista.inicializar();
});
