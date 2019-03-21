class CatalogoVista extends Vista
{
	constructor(ventana) 
	{
		super(ventana);
		this.presentador = null;
		this._llaves = null;
		this.modo = Modo.ALTA;
		this.tabla = new Tabla(this,"#tabla");	
		this.validaciones = new Validaciones();
		this.modeloActual=null;
	}
	
	onLoad()
	{
		this.inicializarEliminar();
		this.crearColumnasGrid();		
		this.presentador.consultar();
	}
	
	inicializarEliminar()
	{
		$('#modalEliminar').on('click', '.btn-danger', function(e) 
		{
			var presentador= $('#modalEliminar').find('#buttonEliminar').data('presentador');
			$('#modalEliminar').modal('hide')
			presentador.eliminar();
			  
		});
	}

	
	editar(id)
	{
		this._llaves =
		{
			id:id	
		};			
		this.modo = Modo.CAMBIO;
		this.limpiarFormulario();	
		this.mostrarFormulario();
		if(this.presentador!=null)
			this.presentador.consultarPorLlaves();
	}
	
	agregar()
	{
		this.modo = Modo.ALTA;
		this.ocultarIndicador();
		this.limpiarFormulario();	
		this.mostrarFormulario();
	}
	
	limpiarFormulario()
	{
		
	}

	set datos(datos)
	{
		this.tabla.registros = datos;	
		this.tabla.renderizar();
	}
	
	get criteriosSeleccion()
	{
		 var criteriosSeleccion = 
		 {				    
			nombre:$('#nombreInputCriterio').val()
		 }
		 return criteriosSeleccion;
	}		
	
	consultar()
	{	
		if(this.presentador!=null)
			this.presentador.consultar();
	}	
	
	crearColumnasGrid()
	{
		
	}
	
	eliminar(id)
	{ 
		this._llaves =
		{
			id:id	
		};
		$('#modalEliminar').find('#buttonEliminar').data('presentador', this.presentador);
		$('#modalEliminar').modal('show');
	}
	
	get llaves()
	{
		return this._llaves;
	}
	
	
	renderEstatus(renglon, campoBase)
	{    
		var contenido = "";
		if(renglon.estatus==1)
			contenido += "<span class='status--process'>Activo</span>";
		else
			contenido += "<span class='status--denied'>Inactivo</span>";
	    return contenido;
	}
	
	
	 
	mostrarFormulario()
	{
		$('#modalAlta').modal('show')
	}
		
	salirFormulario()
	{
		$('#modalAlta').modal('hide')
	}
	
	datosValidos()
	{
		return true;
	}
	
	guardar()
	{		
		if(this.presentador!=null)
		{
			 if(this.datosValidos())
			 {
				if(this.modo==Modo.ALTA)
					this.presentador.insertar();
				else
					this.presentador.actualizar();
			 }		
		}
	}
	
	cargandoOpciones(select)
	{
		$(select).empty();
		$(select).append('<option value="">Cargando...</option>');

	}
	
	cargarOpciones(select, registros, modo, modeloEdicion, campo, texto)
	{
		$(select).empty();
		if(texto!=null)
		{
			if(texto=="")
				$(select).append($('<option></option>').val("").html("-Seleccione"));
			else 
				$(select).append($('<option></option>').val("").html(texto));
		}
		$.each(registros, function(i, p) 
		{
		    $(select).append($('<option></option>').val(p.id).html(p.nombre));
		});
		if(modo==Modo.CAMBIO && modeloEdicion!=null)
		{
			var id = modeloEdicion[campo];
			$(select).val(id);
		}
	}

}
