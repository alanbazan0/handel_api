class PuestosVista extends CatalogoVista
{		
	constructor(ventana)
	{	
		super(ventana);
		this.presentador = new PuestosPresentador(this);
		this._urlFormulario = "html/formularios/puestos.html";
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
	
	agregar()
	{
		super.agregar();
		$('#nombreInput').focus();
		this.consultarEmpresas();
		
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
			nombre:$('#nombreInputCriterio').val()
		 }
		 return criteriosSeleccion;
	}		

	set modelo(valor)
	{		
		this.modeloEdicion = valor;
		$('#nombreInput').val(this.modeloEdicion.nombre);
		$("input[name=estatus][value=" + this.modeloEdicion.estatus + "]").prop('checked', true);
		this.consultarEmpresas();
	}
	
	get modelo()
	{
		 var modelo = 
		 {		
			 nombre:$('#nombreInput').val(),
			 empresaId:$('#empresaSelect').val(),
			 sedeId:$('#sedeSelect').val(),
			 estatus:$('#estatusRadio').is(':checked')?1:0
		 };
		 if(this.modo=="CAMBIO" && this.modeloEdicion!=null)
			 modelo.id = this.modeloEdicion.id;
		 return modelo;
	 }
	 
	datosValidos()
	{
		var nombre = $("#nombreInput"),
	        empresa = $("#empresaSelect"),
	        sede = $("#sedeSelect");
        
        var allFields = $( [] ).add(nombre).add(empresa).add(sede);
        var tips = $( ".validateTips" );
		tips.text("");
		
		var valid = true;
		allFields.removeClass("ui-state-error");
		
	    valid = valid && this.validaciones.checkValue( nombre, "nombre", tips );
	    valid = valid && this.validaciones.checkValue( empresa, "empresa",tips );
	    valid = valid && this.validaciones.checkValue( sede, "sede",tips );
	    
		return valid;
	}	

	limpiarFormulario()
	{
		$('#nombreInput').val("");
		this.cargandoOpciones('#empresaSelect');
		this.cargandoOpciones('#sedeSelect');
	}
	
	consultarEmpresas()
	{
		this.cargandoOpciones("#empresaSelect");
		this.cargandoOpciones("#sedeSelect");
		this.presentador.consultarEmpresas();
	}
	
	set empresas(registros)
	{		
		this.cargarOpciones('#empresaSelect', registros, this.modo, this.modeloEdicion, 'empresaId',"");
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
	
	cambiarEmpresa()
	{
		this.consultarSedes();
	}
	
	consultarSedes()
	{
		this.cargandoOpciones("#sedeSelect");
		this.presentador.consultarSedes();
	}
	
	set sedes(registros)
	{		
		this.cargarOpciones('#sedeSelect', registros, this.modo, this.modeloEdicion, 'sedeId',"");
	}

	
}
var vista = new PuestosVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});
