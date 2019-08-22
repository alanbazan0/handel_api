class PlantillasPresentador extends CatalogoPresentador
{
	 constructor(vista)
	 {
		 super(vista, new PlantillasRepositorio());
	 }
	 
	 guardarRespuestasSi()
	 {
		 this.vista.mostrarIndicador();
		 var repositorio = new PlantillasRepositorio(this);		
		 repositorio.guardarRespuestasSi(this,this.guardarRespuestasSiResultado,this.vista.plantillaId,this.vista.seccionIdSeleccionada,this.vista.preguntaIdSeleccionada,this.vista.respuestas);
	 }
	 
	 guardarRespuestasSiResultado(resultado)
	 {
		this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
		{
			this.vista.mostrarMensaje("Notificación","Guardado.")
		}
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
	 }
	 
	 guardarRespuestasNo()
	 {
		 this.vista.mostrarIndicador();
		 var repositorio = new PlantillasRepositorio(this);		
		 repositorio.guardarRespuestasNo(this,this.guardarRespuestasNoResultado,this.vista.plantillaId,this.vista.seccionIdSeleccionada,this.vista.preguntaIdSeleccionada,this.vista.respuestas);
	 }
	 
	 guardarRespuestasNoResultado(resultado)
	 {
		this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
		{
			this.vista.mostrarMensaje("Notificación","Guardado.")
		}
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
	 }
	 
	 consultarCategorias()
	 {
		 this.vista.mostrarIndicador();
		 var repositorio = new CategoriasRepositorio(this);		
		 repositorio.consultar(this,this.consultarCategoriasResultado,null);
	 }
	 
	 consultarCategoriasResultado(resultado)
	 {
		this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
			this.vista.categorias = resultado.valor;
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
	 }
	 
	 consultarEstandares()
	 {
		 this.vista.mostrarIndicador();
		 var repositorio = new EstandaresRepositorio(this);		
		 repositorio.consultar(this,this.consultarEstandaresResultado,null);
	 }
	 
	 consultarEstandaresResultado(resultado)
	 {
		this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
			this.vista.estandares = resultado.valor;
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
	 }
	 
//	 insertar()
//	 {
//		 this.vista.mostrarIndicador();	
//		 var repositorio = new PlantillasRepositorio(this);			 
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
//			this.vista.mostrarMensajeError("Error","Ocurrió un error al guardar el registro. " + resultado.mensajeError);			
//			
//	 }	
	 
//	 actualizar()
//	 {
//		 this.vista.mostrarIndicador();	
//		 var repositorio = new PlantillasRepositorio(this);		
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
//			this.vista.mostrarMensajeError("Error","Ocurrió un error al actualizar el registro. " + resultado.mensajeError);			
//	 }
//	   
//	 consultarPorLlaves()
//	 {
//		 this.vista.mostrarIndicador();	
//		 var repositorio = new PlantillasRepositorio(this);		
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
//			 this.vista.mostrarMensajeError("Error","Ocurrió un error al consultar el registro. " + resultado.mensajeError);
//	 }
	 
//	 eliminar()
//	 {
//		 this.vista.mostrarIndicador();	
//		 var repositorio = new PlantillasRepositorio(this);		
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
//				 this.vista.mostrarMensajeError("Error","No se puede eliminar el registro porque esta relacionado con otro catálogo. ") ;
//			 else
//				 this.vista.mostrarMensajeError("Error","Ocurrió un error al eliminar el registro. " + resultado.mensajeError);
//		 }
//	 }
	 
	
	 
}