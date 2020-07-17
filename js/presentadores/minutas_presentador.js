class MinutasPresentador extends CatalogoPresentador
{
	 constructor(vista)
	 {
		 super(vista,new MinutasRepositorio());
	 }
	 
	 actualizarValor(campo,valor)
	 {
		 if(campo!=undefined)
		{
			 this.vista.mostrarIndicador();	
			 this._repositorio.actualizarValor(this,function(resultado)
			 {		
				 this.vista.ocultarIndicador();	
				 if(resultado.mensajeError=="")
				 {
					 this.vista.mostrarMensaje("","Guardado.");
				 }
				 else
				 {
					 this.vista.mostrarMensajeError("Error", resultado.mensajeError);
				 }
			 },this.vista.minutaId, campo, valor);
		}
	 }
	 
	 insertarResultado(resultado)
	 {
		this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
		{	
			this.vista.mostrarMensaje("Notificación","La información se guardó correctamente. Id: " + resultado.valor);
			this.vista.salirFormularioAlta();
			this.vista._llaves = {id : resultado.valor};
			this.vista.editar();
		}
		else
			this.vista.mostrarMensajeError("Error","Ocurrió un error al guardar el registro. " + resultado.mensajeError);	
		
		 setTimeout(function()
		{
			 this.vista.guardando = false;
         }, 2000);
	 }	
	 
	 
}