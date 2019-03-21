class CambiarFotoPerfilPresentador
{
	 constructor(vista)
	 {
		this.vista = vista; 
	 }
	 
	 subirFotoPerfil()
	 {
		 this.vista.mostrarIndicador();
		 var repositorio = new UsuariosRepositorio(this);		
		 repositorio.subirFotoPerfil(this,this.subirFotoPerfilResultado,this.vista.archivo);
	 }
	 
	 subirFotoPerfilResultado(resultado)
	 {
		this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
		{
			this.vista.fotoPerfil = resultado.valor;
		}
		else
			this.vista.mostrarMensaje("Error",resultado.mensajeError);
		
	 }
	
	 
}