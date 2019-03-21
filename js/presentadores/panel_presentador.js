class PanelPresentador
{
	constructor(vista)
	{
		this.vista = vista;
	}
	
	consultar()
	 {
		 this.vista.mostrarIndicador();
		 var repositorio = new IndicadoresRepositorio(this);		
		 repositorio.consultar(this,this.consultarResultado);
	 }
	 
	 consultarResultado(resultado)
	 {
		this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
			this.vista.indicadores = resultado.valor;
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
	 }
	 
	 consultarInspeccionesEmpresa()
	 {
		 this.vista.mostrarIndicador();
		 var repositorio = new InspeccionesRepositorio(this);		
		 repositorio.consultarInspeccionesEmpresa(this,this.consultarInspeccionesEmpresaResultado);
	 }
	 
	 consultarInspeccionesEmpresaResultado(resultado)
	 {
		this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
			this.vista.inspeccionesEmpresa = resultado.valor;
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
	 }
	 
	 consultarInspeccionesMes()
	 {
		 this.vista.mostrarIndicador();
		 var repositorio = new InspeccionesRepositorio(this);		
		 repositorio.consultarInspeccionesMes(this,this.consultarInspeccionesMesResultado);
	 }
	 
	 consultarInspeccionesMesResultado(resultado)
	 {
		this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
			this.vista.inspeccionesMes = resultado.valor;
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
	 }
	 
	 consultarInspeccionesSede()
	 {
		 this.vista.mostrarIndicador();
		 var repositorio = new InspeccionesRepositorio(this);		
		 repositorio.consultarInspeccionesSede(this,this.consultarInspeccionesSedeResultado);
	 }
	 
	 consultarInspeccionesSedeResultado(resultado)
	 {
		this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
			this.vista.inspeccionesSede= resultado.valor;
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
	 }
	 
}