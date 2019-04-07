class CatalogoPresentador
{
	 constructor(vista,repositorio)
	 {
		 this._repositorio = repositorio;
		 this.vista = vista;
	 }
	 
	 consultar()
	 {
		 this.vista.mostrarIndicador();
		 //var repositorio = new EmpresasRepositorio(this);		
		 this._repositorio.consultar(this,this.consultarResultado,this.vista.criteriosSeleccion);
	 }
	 
	 consultarResultado(resultado)
	 {
		this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
			this.vista.datos = resultado.valor;
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
	 }
	 
	 insertar()
	 {
		 this.vista.guardando = true;
		 this.vista.mostrarIndicador();	
		 this._repositorio.insertar(this,this.insertarResultado,this.vista.modelo, this.vista.logo);	
	 }
	 
	 insertarResultado(resultado)
	 {
		this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
		{	
			this.vista.mostrarMensaje("Notificación","La información se guardó correctamente. Id: " + resultado.valor);
			this.vista.salirFormulario();
			this.consultar();
		}
		else
			this.vista.mostrarMensajeError("Error","Ocurrió un error al guardar el registro. " + resultado.mensajeError);		
		 this.vista.guardando = false;
			
	 }	

	 actualizar()
	 {
		 this.vista.guardando = true;
		 this.vista.mostrarIndicador();	
		 this._repositorio.actualizar(this,this.actualizarResultado,this.vista.modelo, this.vista.logo);
	 }
	 
	 actualizarResultado(resultado)
	 {
		 this.vista.ocultarIndicador();	
		 if(resultado.mensajeError=="")
		 {	
			this.vista.mostrarMensaje("Notificación","La información se actualizó correctamente.");
			this.vista.salirFormulario();
			this.consultar();
		 }
		 else
			this.vista.mostrarMensajeError("Error","Ocurrió un error al actualizar el registro. " + resultado.mensajeError);		
		 this.vista.guardando = false;
	 }
	   
	 consultarPorLlaves()
	 {
		 this.vista.mostrarIndicador();	
		 this._repositorio.consultarPorLlaves(this,this.consultarPorLlavesResultado,this.vista.llaves);
	 }
	 
	 consultarPorLlavesResultado(resultado)
	 {		
		 this.vista.ocultarIndicador();	
		 if(resultado.mensajeError=="")
		 {
			 this.vista.modelo = resultado.valor;
		 }
		 else
			 this.vista.mostrarMensajeError("Error","Ocurrió un error al consultar el registro. " + resultado.mensajeError);
	 }
	 
	 eliminar()
	 {
		 this.vista.mostrarIndicador();	
		 this._repositorio.eliminar(this,this.eliminarResultado,this.vista.llaves);
	 }
	 
	 eliminarResultado(resultado)
	 {		
		 this.vista.ocultarIndicador();	
		 this.vista.cerrarConfirmacionEliminar();
		 if(resultado.mensajeError=="")
		 {
			
			 this.vista.mostrarMensaje("Notificación","El registro se eliminó correctamente.");
			 this.consultar();
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