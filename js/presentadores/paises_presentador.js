class PaisesPresentador extends CatalogoPresentador
{
	 constructor(vista)
	 {
		 super(vista,new PaisesRepositorio());
	 }
	 
//	 consultar()
//	 {
//		 this.vista.mostrarIndicador();
//		 var repositorio = new PaisesRepositorio(this);		
//		 repositorio.consultar(this,this.consultarResultado,this.vista.criteriosSeleccion);
//	 }
//	 
//	 consultarResultado(resultado)
//	 {
//		this.vista.ocultarIndicador();	
//		if(resultado.mensajeError=="")
//			this.vista.datos = resultado.valor;
//		else
//			this.vista.mostrarMensaje("Error",resultado.mensajeError);
//		
//	 }
//	 
//	 insertar()
//	 {
//		 this.vista.mostrarIndicador();	
//		 var repositorio = new PaisesRepositorio(this);			 
//		 repositorio.insertar(this,this.insertarResultado,this.vista.modelo);	
//	 }
//	 
//	 insertarResultado(resultado)
//	 {
//		this.vista.ocultarIndicador();	
//		if(resultado.mensajeError=="")
//		{	
//			this.vista.mostrarMensaje("Notificación","La información se guardó correctamente. Id: " + resultado.valor);
//			this.vista.salirFormulario();
//			this.consultar();
//		}
//		else
//			this.vista.mostrarMensaje("Error","Ocurrió un error al guardar el registro. " + resultado.mensajeError);			
//			
//	 }	
//	 
//	 actualizar()
//	 {
//		 this.vista.mostrarIndicador();	
//		 var repositorio = new PaisesRepositorio(this);		
//		 repositorio.actualizar(this,this.actualizarResultado,this.vista.modelo);
//	 }
//	 
//	 actualizarResultado(resultado)
//	 {
//		 this.vista.ocultarIndicador();	
//		 if(resultado.mensajeError=="")
//		 {	
//			this.vista.mostrarMensaje("Notificación","La información se actualizó correctamente.");
//			this.vista.salirFormulario();
//			this.consultar();
//		 }
//		 else
//			this.vista.mostrarMensaje("Error","Ocurrió un error al actualizar el registro. " + resultado.mensajeError);			
//	 }
//	   
//	 consultarPorLlaves()
//	 {
//		 this.vista.mostrarIndicador();	
//		 var repositorio = new PaisesRepositorio(this);		
//		 repositorio.consultarPorLlaves(this,this.consultarPorLlavesResultado,this.vista.llaves);
//	 }
//	 
//	 consultarPorLlavesResultado(resultado)
//	 {		
//		 this.vista.ocultarIndicador();	
//		 if(resultado.mensajeError=="")
//		 {
//			 this.vista.modelo = resultado.valor;
//		 }
//		 else
//			 this.vista.mostrarMensaje("Error","Ocurrió un error al consultar el registro. " + resultado.mensajeError);
//	 }
//	 
//	 eliminar()
//	 {
//		 this.vista.mostrarIndicador();	
//		 var repositorio = new PaisesRepositorio(this);		
//		 repositorio.eliminar(this,this.eliminarResultado,this.vista.llaves);
//	 }
//	 
//	 eliminarResultado(resultado)
//	 {		
//		 this.vista.ocultarIndicador();	
//		 if(resultado.mensajeError=="")
//		 {
//			 this.vista.mostrarMensaje("Notificación","El registro se eliminó correctamente.");
//			 this.consultar();
//		 }
//		 else
//		 {
//			 if(resultado.codigoError==1451)
//				 this.vista.mostrarMensaje("Error","No se puede eliminar el registro porque esta relacionado con otro catálogo. ") ;
//			 else
//				 this.vista.mostrarMensaje("Error","Ocurrió un error al eliminar el registro. " + resultado.mensajeError);
//		 }
//	 }
//	 
	
	 
}