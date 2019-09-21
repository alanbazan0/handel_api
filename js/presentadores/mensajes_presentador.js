class MensajesPresentador extends CatalogoPresentador
{
	 constructor(vista)
	 {
		 super(vista,new MensajesRepositorio());
	 }
	 
	 marcarMensajeComoLeido()
	 {
		 this._repositorio.marcarComoLeido(this,this.marcarMensajeComoLeidoResultado,this.vista.mensajeSeleccionado.id);
	 }
	 
	 marcarMensajeComoLeidoResultado(resultado)
	 {
		 if(resultado.mensajeError=="")
		 {	
			 this.vista.marcarMensaje(resultado.valor);
		 }
		 else
			this.vista.mostrarMensajeError("Error","No se pudo marcar como leido. " + resultado.mensajeError);		
	 }
	 
	 
	 consultarComentarios()
	 {
		// this.vista.mostrarIndicador();
		 var repositorio = new MensajesComentariosRepositorio(this);		
		 repositorio.consultar(this,this.consultarComentariosResultado,{mensajeId: this.vista.mensajeSeleccionado.id});
	 }
	
	 
	 consultarComentariosResultado(resultado)
	 {
		//this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
			this.vista.comentarios = resultado.valor;
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
	 }
	 
	 enviarComentario()
	 {
		 //this.vista.mostrarIndicador();
		 var repositorio = new MensajesComentariosRepositorio(this);		
		 repositorio.insertar(this,this.enviarComentarioResultado,this.vista.modeloComentario);
	 }
	
	 
	 enviarComentarioResultado(resultado)
	 {
		this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
			this.vista.consultarComentarios();
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
	 }
	 
	 
}