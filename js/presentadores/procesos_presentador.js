class ProcesosPresentador extends CatalogoPresentador
{ 
	 constructor(vista)
	 {
		 super(vista,new ProcesosRepositorio());
	 }
	 

	 
	 consultarEmpresas()	
	 {
		 var repositorio = new EmpresasRepositorio(this);		
		 repositorio.consultar(this,this.consultarEmpresasResultado,{estatus:1});
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
		 repositorio.consultar(this,this.consultarEmpresasCriterioResultado,{estatus:1},true);
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
			//this.vista.cambiarSedeCriterio();
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
	 
	 subirArchivo(llaves, archivo)
	 {
		  this.vista.mostrarIndicador();
		 this._repositorio.adjuntarArchivo(this,function(resultado)
		 {
			  this.vista.ocultarIndicador();
			if(resultado.mensajeError=="")
			{
				this.vista.mostrarMensaje("Notificación","Se adjuntó el archivo " + resultado.valor.archivo);
				this.vista.marcarArchivoSubido(resultado.valor.id, resultado.valor.archivo);			
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 },llaves, archivo);
		 
	 }
	 
}