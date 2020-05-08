class CapacitacionPresentador extends CatalogoPresentador
{
	 constructor(vista)
	 {
		 super(vista, new CapacitacionesRepositorio());
	 }
	 
	 consultarPorToken()
	 {
		 this.vista.mostrarIndicador();	
		 this._repositorio.consultarPorToken(this, function(resultado)
				 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				 this.vista.modelo = resultado.valor;
			 }
			 else
				 this.vista.mostrarCapacitacionInvalida();
		 }
		 ,this.vista.token);
	 }
	 
	 consultarPerfiles()
	 {
		 this.vista.mostrarIndicador();
		 var repositorio = new PerfilesRepositorio(this);		
		 repositorio.consultar(this, function(resultado)
		 {
			this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
				this.vista.perfiles = resultado.valor;
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
		 },null);
	 }
	
	
	 
	 
	 
}