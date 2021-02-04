class AuditoriasPresentador extends CatalogoPresentador
{
	 constructor(vista)
	 {
		 super(vista, new AuditoriasRepositorio());
	 }

	 consultarPorLlaves()
	 {
		 this.vista.mostrarIndicador();	
		 this._repositorio.consultarPorLlaves(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				this.vista.modelo = resultado.valor;
			 }
			 
		 },this.vista.llaves);
	 }
	 
}