class InventarioPresentador extends CatalogoPresentador
{
	constructor(vista)
	{
		super(vista,new InventarioRepositorio());
	}

	consultar()
	{
		this.vista.mostrarIndicador();
		this._repositorio.consultarDentroInstalacion(this,this.consultarResultado,this.vista.criteriosSeleccion);
	}

	consultarEmpresasCriterio()
	{
		var repositorio = new EmpresasRepositorio(this);
		repositorio.consultar(this,this.consultarEmpresasCriterioResultado,null,true);
	}

	consultarEmpresasCriterioResultado(resultado)
	{
		if(resultado.mensajeError=="")
		{
			this.vista.empresasCriterio = resultado.valor;
			this.vista.cambiarEmpresaCriterio();
		}
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
	}

	consultarSedesCriterio()
	{
		var repositorio = new SedesRepositorio(this);
		repositorio.consultarPorEmpresa(this,this.consultarSedesCriterioResultado,this.vista.criteriosSeleccion.empresaId,true);
	}

	consultarSedesCriterioResultado(resultado)
	{
		if(resultado.mensajeError=="")
		{
			this.vista.sedesCriterio = resultado.valor;
			this.vista.consultar();
		}
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
	}

	registrarAjusteManual(contexto, funcion, inspeccionSalida)
	{
		this._repositorio.insertar(contexto, funcion, inspeccionSalida);
	}
}
