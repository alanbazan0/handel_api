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
		this.consultarAnosMeses();
		
		var _this = this;
		$("#consultarButton").click(function(){
			_this.consultar();
		});
	}
	
	consultarAnosMeses()
	{
		this.presentador.consultarAnosMeses();
	}
	
//	onLoad()
//	{			
//		this.crearColumnasGrid();		
//		this.presentador.consultar();
//	}
	
	crearColumnasGrid()
	{
		
		this.tabla.columnas = [
			{longitud:250, 	titulo:"Mes",   alias:"mesNombre", alineacion:"I" },	
			{longitud:200, 	titulo:"Año",   alias:"ano", alineacion:"I" } 					
			
		]
		
		this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Imprimir'  type='button' class='imprimir btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fas fa-file-pdf fa-lg'></span></button>";
		

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
//	
//	renderEstatus(renglon, campoBase)
//	{    
//		var contenido = "";
//		if(renglon.estatus==1)
//			contenido += "<center><span class='fa "+ ICONO_ACTIVO +" fa-lg' style='color:"+COLOR_ACTIVO+"'></span></center>";
//		else
//			contenido += "<center><span class='fa "+ ICONO_INACTIVO+" fa-lg' style='color:"+COLOR_INACTIVO+"'></span></center>";
//	    return contenido;
//	}
	
	
//	mostrarIndicador()
//	{
//		$('#indicador').show();				
//	}
//	
//	ocultarIndicador()
//	{		
//		$('#indicador').hide();
//	}
//	
//	btnBaja_onClick()
//	{ 
//		if(this.grid._selectedItem!=null)
//		{
//			var confirmacion = confirm("¿Esta seguro que desea eliminar el registro?")
//		    if (confirmacion)
//		    {
//		    		this.presentador.eliminar();
//		    }	
//		}
//		else
//			this.mostrarMensaje("Acción no válida","Seleccione un registro para eliminar.");
//	}
//	
//	btnAlta_onClick()
//	{
//		this.modo = "ALTA";
//		this.ocultarIndicador();
//		this.limpiarFormulario();	
//		this.mostrarFormulario();
//		$('#nombreInput').focus();
//		
//	}
//	
//	btnCambio_onClick()
//	{
//		if(this.grid._selectedItem!=null)
//		{			
//			this.modo = "CAMBIO";
//			this.limpiarFormulario();	
//			this.mostrarFormulario();
//			$('#nombreInput').focus();				
//			this.presentador.consultarPorLlaves();
//		}
//		else
//			this.mostrarMensaje("Acción no válida","Seleccione un registro para modificar.");
//				
//	}
//	
//	btnConsulta_onClick()
//	{	
//		this.presentador.consultar();
//	}	
//	
//	btnGuardarFormulario_onClick()
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
//	
//	get llaves()
//	{
//		var llaves =
//		{
//			id:this.grid._selectedItem.id	
//		}
//		return llaves;
//	}
//	
	
	get criteriosSeleccion()
	{
		 var criteriosSeleccion = 
		 {				    
			nombre:$('#nombreInputCriterio').val()
		 }
		 return criteriosSeleccion;
	}		

//	set datos(valor)
//	{
//		this.grid._dataProvider = valor;	
//		this.grid.render();
//	}
//	
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
	 

	
//	mostrarFormulario()
//	{
//		$('#principalDiv').hide();	
//		$('#formularioDiv').show();
//	}
//	
//	salirFormulario()
//	{
//		$('#principalDiv').show()	
//		$('#formularioDiv').hide();
//	}
//	
	
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
		//this.cargandoOpciones('#empresaSelect');
	}
	
	
	
	

	
}
var vista = new ReportesEvidenciasVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});

