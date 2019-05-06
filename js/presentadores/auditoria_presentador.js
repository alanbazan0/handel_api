class AuditoriaPresentador extends CatalogoPresentador
{
	 constructor(vista)
	 {
		 super(vista, new AuditoriasRepositorio());
	 }
	 
	 
	guardar()
	{
		 if(this.vista.modo==Modo.ALTA)
			 this.insertar();
		 else
			 this.actualizar();
	
	}
	
	insertar()
	{
		 this.vista.mostrarIndicador();	
		 //var repositorio = new AuditoriasRepositorio(this);		
		 this._repositorio.insertar(this,this.insertarResultado,this.vista.modelo);
	}
	
	insertarResultado(resultado)
	 {
		 this.vista.ocultarIndicador();	
		 if(resultado.mensajeError=="")
		 {
			this.vista.modo = Modo.CAMBIO;
			this.vista.modeloEdicion= resultado.valor;
			this.vista.mostrarReferencia();
			if(this.vista.funcion="siguente")
				this.vista.mostrarSiguiente();
			else
				this.vista.mostrarAnterior();
			this.vista.mostrarMensaje("Notificación","Guardado. ") ;
		 }
		 else
		 {
			 if(resultado.codigoError==1451)
				 this.vista.mostrarMensajeError("Error","No se puede eliminar el registro porque esta relacionado con otro catálogo. ") ;
			 else
				 this.vista.mostrarMensajeError("Error","Ocurrió un error al eliminar el registro. " + resultado.mensajeError);
		 }
	 }
	
	actualizar()
	{
		 this.vista.mostrarIndicador();	
		 //var repositorio = new AuditoriasRepositorio(this);		
		 this._repositorio.actualizar(this,this.actualizarResultado,this.vista.modelo);
	}
	
	actualizarResultado(resultado)
	 {
		 this.vista.ocultarIndicador();	
		 if(resultado.mensajeError=="")
		 {
			if(this.vista.funcion="siguente")
				this.vista.mostrarSiguiente();
			else
				this.vista.mostrarAnterior();
			this.vista.mostrarMensaje("Notificación","Guardado. ") ;
		 }
		 else
		 {
			 if(resultado.codigoError==1451)
				 this.vista.mostrarMensajeError("Error","No se puede eliminar el registro porque esta relacionado con otro catálogo. ") ;
			 else
				 this.vista.mostrarMensajeError("Error","Ocurrió un error al eliminar el registro. " + resultado.mensajeError);
		 }
	 }
	 
}