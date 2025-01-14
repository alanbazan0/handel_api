class FrasesVista extends CatalogoVista
{		
	constructor()
	{	
		super();
		this.presentador = new FrasesPresentador(this);
		this._urlFormulario = "html/formularios/frases.php";
	}
	
	crearColumnasGrid()
	{
		this.tabla.columnas = [
			{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"D" },
			{longitud:200, 	titulo:"Frase",   alias:"texto", alineacion:"I" }, 			
			{longitud:200, 	titulo:"Autor",   alias:"autor", alineacion:"I" }, 		
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
			texto:$('#textoInputCriterio').val()
		 }
		 return criteriosSeleccion;
	}		

	set modelo(valor)
	{		
		this.modeloEdicion = valor;
		$('#textoInput').val(this.modeloEdicion.texto);
		$('#autorInput').val(this.modeloEdicion.autor);
	}
	
	get modelo()
	{
		 var modelo = 
		 {		
			 texto:$('#textoInput').val(),			 
			 autor:$('#autorInput').val()
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
var vista = new FrasesVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});

