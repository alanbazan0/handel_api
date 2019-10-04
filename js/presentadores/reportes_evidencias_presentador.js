class ReportesEvidenciasPresentador extends CatalogoPresentador
{
	 constructor(vista)
	 {
		 super(vista,new EvidenciasRepositorio());
	 }
	 
	 consultarAnosMeses()
	 {
		 this.vista.mostrarIndicador();
		 this._repositorio.consultarAnosMeses(this,function(resultado)
		 {
			 this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
				this.vista.datos = resultado.valor;
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 });
	 }
	 
	 
	 
}