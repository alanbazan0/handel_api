class InicioSesionPresentador
{
	 constructor(vista)
	 {
		this.vista = vista; 
	 }
	 
	 iniciarSesion()
	 {
		 this.vista.mostrarIndicador();
		 var repositorio = new UsuariosRepositorio(this);		
		 repositorio.iniciarSesion(this,this.iniciarSesionResultado,this.vista.nombreUsuario,this.vista.contrasena);
	 }
	 
	 iniciarSesionResultado(resultado)
	 {
		this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
			this.vista.mostrarMenu(resultado.valor);
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
	 }
	 
	
	
	 
}