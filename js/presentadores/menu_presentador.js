class MenuPresentador
{
	 constructor(vista)
	 {
		this.vista = vista; 
	 }
	 
	 cerrarSesion()
	 {
		 
		 var repositorio = new UsuariosRepositorio(this);		
		 repositorio.cerrarSesion(this,this.cerrarSesionResultado);
	 }
	 
	 cerrarSesionResultado(resultado)
	 {
		 if(resultado.mensajeError=="")
		 {
		 	vista.iniciarSesion();
		 }
	 }
	 
	 consultarOpcionesMenu()
	 {
		 //this.vista.mostrarIndicador();
		 //var repositorio = new UsuariosRepositorio(this);		
		 //repositorio.iniciarSesion(this,this.consultarOpcionesMenuResultado,this.vista.nombreUsuario,this.vista.contrasena);
	 }
	 
	 consultarOpcionesMenuResultado(resultado)
	 {
		/*this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
			this.vista.mostrarMenu();
		else
			this.vista.mostrarTip(resultado.mensajeError);*/
		
	 }
	 
	
	
	 
}