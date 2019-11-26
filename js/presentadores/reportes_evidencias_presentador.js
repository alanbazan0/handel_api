class ReportesEvidenciasPresentador extends CatalogoPresentador
{
	 constructor(vista)
	 {
		 super(vista,new EvidenciasRepositorio());
	 }
	 
	 consultar()
	 {
		 this.vista.mostrarIndicador();
		 this._repositorio.consultarAnosMeses(this,function(resultado)
		 {
			 this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
			{
				this.validarMeses(resultado.valor);
				this.vista.datos = resultado.valor;
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 });
	 }
	 
	 validarMeses(anoMes)
	 {
		 var validos = [];
		 var fecha = new Date();
		 var mes = fecha.getMonth() + 1;
		 var ano = fecha.getFullYear();
		 var dia = fecha.getDate();
		 for (var i = 0; i < anoMes.length; i++) 
		 {
			 var elemento = anoMes[i];
			 if(elemento.mes == mes && elemento.ano == ano)
			 {	
				 if(dia>=28)
				 {
					 elemento.mensajeError = "";
				 }
				 else
					 elemento.mensajeError = "Disponible apartir del dia 28";
			 }
			 else
				 elemento.mensajeError = "";
		 }
	 }
	 
	 
}