class CreditosVista extends CatalogoVista
{		
	constructor(ventana)
	{	
		super(ventana);
		this.presentador = new CreditosPresentador(this);
		this.consultoGrid = false;
	}
	
	onLoad()
	{
		this.inicializarEliminar();
		this.crearColumnasGrid();
		this.consultarEmpresasCriterio();
	}
	
	crearColumnasGrid()
	{
		this.tabla.columnas = [
			{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"D" },
			{longitud:200, 	titulo:"Empresa",   alias:"empresaNombre", alineacion:"I" },		
			{longitud:200, 	titulo:"Sede",   alias:"sedeNombre", alineacion:"I" },		
			{longitud:250, 	titulo:"Creditos",   alias:"creditos", alineacion:"I" },
			{longitud:250, 	titulo:"Fecha de activacion",   alias:"fechaActivacion", alineacion:"I" },	
			{longitud:200, 	titulo:"Fecha de vencimiento",   alias:"fechaVencimiento", alineacion:"I" }
		]
		
		this.tabla.renderizar();		
	}
	
	renderReactivacion(renglon, campoBase)
	{    
		var contenido = "";
		if(renglon.reactivacionAutomatica==1)
			contenido += "<span class='status--process'>Activo</span>";
		else
			contenido += "<span class='status--denied'>Inactivo</span>";
	    return contenido;
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
			sedeId: $('#sedeSelectCriterio').val()
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
		this.cargandoOpciones("#sedeSelectCriterio");
		this.presentador.consultarEmpresasCriterio();
	}
	
	set empresasCriterio(registros)
	{		
		this.cargarOpciones('#empresaSelectCriterio', registros);
		//this.consultar();
	}
	
	cambiarEmpresa()
	{
		this.consultarSedes();
	}
	
	cambiarEmpresaCriterio()
	{
		this.consultarSedesCriterio();
	}
	
	consultarSedes()
	{
		this.cargandoOpciones("#sedeSelect");
		this.presentador.consultarSedes();
	}
	
	consultarSedesCriterio()
	{
		this.cargandoOpciones("#sedeSelectCriterio");
		this.presentador.consultarSedesCriterio();
	}
	
	set sedes(registros)
	{		
		this.cargarOpciones('#sedeSelect', registros, this.modo, this.modeloEdicion, 'sedeId',"");
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
var vista = new CreditosVista(this);

