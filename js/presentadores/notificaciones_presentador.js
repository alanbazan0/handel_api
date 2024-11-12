class NotificacionesPresentador extends CatalogoPresentador
{
	 constructor(vista)
	 {
		 super(vista, new CapacitacionesRepositorio());
	 }
	 
	 consultar()
	 {
		 this.consultarNotificaciones();
		 
	 }
	 
	 consultarNotificaciones()
	 {
		 this.vista.mostrarIndicador();
		var repositorio = new CapacitacionesRepositorio(this);		
		 repositorio.consultarLeccionesReprobadas(this,function(resultado){
			 if(resultado.mensajeError=="")
			{
				 this.vista.ocultarIndicador();
				this.vista.notificaciones = resultado.valor;
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError, resultado.codigoError);
		 });
	 }
	 
	
	 
	
}