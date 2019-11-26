class ReportesEvidenciasVista extends CatalogoVista
{		
	constructor()
	{	
		super();
		this.presentador = new ReportesEvidenciasPresentador(this);
		this._urlFormulario = "html/formularios/paises.php";
	}
	
	inicializar()
	{
		this.tabla.textoTablaVacia = "No hay reportes disponibles";
		this.crearColumnasGrid();
		
		
		var _this = this;
		$("#consultarButton").click(function(){
			_this.consultar();
		});
		
		this.consultar();
	}
	
//	consultarAnosMeses()
//	{
//		this.presentador.consultarAnosMeses();
//	}
	
//	onLoad()
//	{			
//		this.crearColumnasGrid();		
//		this.presentador.consultar();
//	}
	
	crearColumnasGrid()
	{
		
		this.tabla.columnas = [
			{longitud:250, 	titulo:"Mes",   alias:"mesNombre", alineacion:"I" },	
			{longitud:200, 	titulo:"Año",   alias:"ano", alineacion:"I" },
			{longitud:100, 	titulo:"",   alias:"", alineacion:"I", itemRenderer: this.renderBotonImprimir}
			
		]
		
		//this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Imprimir'  type='button' class='imprimir btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fas fa-file-pdf fa-lg'></span></button>";
		

		this.tabla.registros = [];
	}
	
	renderBotonImprimir(renglon, type, set)
	{    
		var	html = "";
		if(renglon.mensajeError=="")
			html += "<button data-toggle='tooltip' data-placemen='bottom' title='Imprimir'  type='button' class='imprimir btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fas fa-file-pdf fa-lg'></span></button>";
		else
			html += "<span>" +renglon.mensajeError +"</span>";
		return html;
	}
	
	inicializarEventosBotonesTabla(tbody, table, nombresCamposLlave)
	{
		var _this = this;
		$(tbody).on("click", "button.imprimir", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

			_this._registroSeleccionado  = table.row( tr ).data();
			if (_this._registroSeleccionado != undefined)
			{
				_this._llaves = _this.copiarPropiedadesObjeto(_this._registroSeleccionado, ["id"]);
				_this.imprimirReporte();
			}
		});
	}
	
	imprimirReporte()
	{
		var reporte = "";
			
		var submitForm = this.getNewSubmitForm(HANDEL_API+"/php/reportes/reporte_evidencias.php" + reporte);
		this.createNewFormElement(submitForm, "usuarioId", JSON.stringify(this.usuario.id));	 
		this.createNewFormElement(submitForm, "ano", JSON.stringify(this._registroSeleccionado.ano));	 
		this.createNewFormElement(submitForm, "mes", JSON.stringify(this._registroSeleccionado.mes));	 
	    submitForm.target= "_blank";
	    submitForm.submit();
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
		$("input[name=estatus][value=" + this.modeloEdicion.estatus + "]").prop('checked', true);
		//this.consultarEmpresas();
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
		//this.cargandoOpciones('#empresaSelect');
	}
	
	
	
	

	
}
var vista = new ReportesEvidenciasVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});

