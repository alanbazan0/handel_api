class TiposEmpresaVista
{		
	constructor(ventana)
	{	
		this.ventana = ventana;
		this.presentador = new TiposEmpresaPresentador(this);
		this.manejadorEventos = new ManejadorEventos();
		this.grid = new GridReg("grid");	
		this.validaciones = new Validaciones();
		this.modeloActual=null;
	}
	
//	onLoad()
//	{			
//		this.crearColumnasGrid();		
//		this.presentador.consultar();
//		//this.mostrarFormulario();
//	}
	
	crearColumnasGrid()
	{
		this.tabla.columnas = [
			{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"D" },
			{longitud:200, 	titulo:"Nombre",   alias:"nombre", alineacion:"I" }, 					
			{longitud:250, 	titulo:"Fecha de alta",   alias:"fechaAlta", alineacion:"I" },	
			{longitud:200, 	titulo:"Fecha de última modificación",   alias:"fechaModificacion", alineacion:"I" },
			{longitud:100, 	titulo:"Estatus",   alias:"estatus", alineacion:"D", itemRenderer:this.renderEstatus}
		]
		
		this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-edit fa-lg'></span></button>"+
										"<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";

		this.tabla.registros = [];	
	}
	
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
	
//	btnAlta_onClick()
//	{
//		this.modo = "ALTA";
//		this.ocultarIndicador();
//		this.limpiarFormulario();	
//		this.mostrarFormulario();
//		$('#nombreInput').focus();
//		//this.consultarEmpresas();
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
	
	get llaves()
	{
		var llaves =
		{
			id:this.grid._selectedItem.id	
		}
		return llaves;
	}
	
	
	get criteriosSeleccion()
	{
		 var criteriosSeleccion = 
		 {				    
			nombre:$('#nombreInputCriterio').val()
		 }
		 return criteriosSeleccion;
	}		

	set datos(valor)
	{
		this.grid._dataProvider = valor;	
		this.grid.render();
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
			 estatus:$('input[name=estatus]:checked').val()
		 };
		 if(this.modo=="CAMBIO" && this.modeloEdicion!=null)
			 modelo.id = this.modeloEdicion.id;
		 return modelo;
	 }
//	 
//	mostrarMensaje(titulo, mensaje)
//	{
//		$('#dialogo').prop('title', titulo);
//	    $('#dialogo').html(mensaje);
//	    $('#dialogo').dialog({   
//	     autoOpen: false,   
//	     modal: true   
//	    });
//	    $('#dialogo').dialog('open');
//	}
//	
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
var vista = new TiposEmpresaVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});
