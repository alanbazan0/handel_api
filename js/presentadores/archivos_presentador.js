class ArchivosPresentador extends CatalogoPresentador
{
	 constructor(vista)
	 {
		 super(vista,new ArchivosRepositorio());
	 }
	 
	eliminarArchivos()
	{
		 this.vista.mostrarIndicador();
		 this._repositorio.eliminarArchivos(this,function(resultado)
		 {
			this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
			{
				this.consultar();
				swal.close();
				if(resultado.valor==1)
					this.vista.mostrarMensaje("Aviso","Se eliminó " + resultado.valor + " archivo");
				else
					this.vista.mostrarMensaje("Aviso","Se eliminaron " + resultado.valor + " archivos");
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
		 },this.vista.criteriosSeleccion);
	}
	 
}