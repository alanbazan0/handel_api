class GraficaInspeccionesAreaPresentador extends CatalogoPresentador
{
	 constructor(vista)
	 {
		 super(vista,new InspeccionesRepositorio());
	 }
	 
	 consultar()
	 {
		 //super.consultar();
		 this.vista.mostrarIndicador();
		 this._repositorio.consultarInspeccionesArea(this,this.consultarResultado,this.vista.criteriosSeleccion);
	 }
	 
	 consultarResultado(resultado)
	 {
		this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
			this.vista.datos = resultado.valor;
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
	 }
	 
	 
	 consultarEmpresas()	
	 {
		 var repositorio = new EmpresasRepositorio(this);		
		 repositorio.consultar(this,this.consultarEmpresasResultado,null);
	 }
	 
	 consultarEmpresasResultado(resultado)
	 {
		if(resultado.mensajeError=="")
		{
			this.vista.empresas = resultado.valor;
			this.vista.cambiarEmpresa();
			
		}
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
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
	 
	 consultarSedes()	
	 {
		 var repositorio = new SedesRepositorio(this);		
		 repositorio.consultarPorEmpresa(this,this.consultarSedesResultado,this.vista.modelo.empresaId);
	 }
	 
	 consultarSedesResultado(resultado)
	 {
		if(resultado.mensajeError=="")
		{
			this.vista.sedes = resultado.valor;				
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
			this.vista.cambiarSedeCriterio();
		}
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
	 }
	 
	 consultarAreasCriterio()	
	 {
		 var repositorio = new AreasRepositorio(this);	
		
		 repositorio.consultarPorEmpresaSede(this,this.consultarAreasCriterioResultado,this.vista.criteriosSeleccion.empresaId,this.vista.criteriosSeleccion.sedeId,true);
	 }
	 
	 consultarAreasCriterioResultado(resultado)
	 {
		if(resultado.mensajeError=="")
			this.vista.areasCriterio = resultado.valor;
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
	 }
	 
}