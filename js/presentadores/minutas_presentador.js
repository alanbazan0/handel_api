class MinutasPresentador extends CatalogoPresentador
{
	 constructor(vista)
	 {
		 super(vista,new MinutasRepositorio());
	 }
	 
	 actualizarValor(campo,valor)
	 {
		 if(campo!=undefined)
		{
			 this.vista.mostrarIndicador();	
			 this._repositorio.actualizarValor(this,function(resultado)
			 {		
				 this.vista.ocultarIndicador();	
				 if(resultado.mensajeError=="")
				 {
					 this.vista.mostrarMensaje("","Guardado.");
				 }
				 else
				 {
					 this.vista.mostrarMensajeError("Error", resultado.mensajeError);
				 }
			 },this.vista.minutaId, campo, valor);
		}
	 }
	 
	 insertarResultado(resultado)
	 {
		this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
		{	
			this.vista.mostrarMensaje("Notificación","La información se guardó correctamente. Id: " + resultado.valor);
			this.vista.salirFormularioAlta();
			this.vista._llaves = {id : resultado.valor};
			this.vista._registroSeleccionado.id = resultado.valor;
			this.vista.editar();
		}
		else
			this.vista.mostrarMensajeError("Error","Ocurrió un error al guardar el registro. " + resultado.mensajeError);	
		
		 setTimeout(function()
		{
			 this.vista.guardando = false;
         }, 2000);
	 }	
	 
	 ordenarTareas(seleccion)
	 {
		 this.vista.mostrarIndicador();
		 this._repositorio.ordenarTareas(this, function(resultado)
		 {
				this.vista.ocultarIndicador();	
				if(resultado.mensajeError=="")
				{
					this.vista.mostrarMensaje("","Guardado.");
				}
				else
					this.vista.mostrarMensajeError("Error",resultado.mensajeError);
				
		 },this.vista.minutaId,seleccion);
	 }
	 
	 
	 actualizarValorTarea(tareaId, campo,valor)
	 {
		 if(campo!=undefined)
		{
			 this.vista.mostrarIndicador();	
			 this._repositorio.actualizarValorTarea(this,function(resultado)
			 {		
				 this.vista.ocultarIndicador();	
				 if(resultado.mensajeError=="")
				 {
					 this.vista.mostrarMensaje("","Guardado.");
				 }
				 else
				 {
					 this.vista.mostrarMensajeError("Error", resultado.mensajeError);
				 }
			 },this.vista.minutaId, tareaId, campo, valor);
		}
	 }
	 
	 eliminarTarea()
	 {
		 this.vista.mostrarIndicador();	
		 var tareaId = this.vista.llavesTarea.tareaId;
		 this._repositorio.eliminarTarea(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 this.vista.cerrarConfirmacionEliminar();
			 if(resultado.mensajeError=="")
			 {
				 this.vista.mostrarMensaje("","Guardado.");
				 var tareas =  this.vista.listaTareas.tareas;
				 var indice = this.vista.listaTareas.getIndice(tareaId);
				
				 this.vista.listaTareas.eliminar(tareaId);
//				 if(indice==0)
//				 {
//					 var seccionSeleccionada =  this.vista.listaLecciones.lecciones[indice+1];
//					 this.vista.seleccionarLeccion(null,seccionSeleccionada.id);
//				 }
//				 else
//				 {
//					 var seccionSeleccionada =  this.vista.listaLecciones.lecciones[indice-1];
//					 this.vista.seleccionarLeccion(null,seccionSeleccionada.id);
//				 }
				
				 //TODO: consultar seccion
				 //this.consultar();
			 }
			 else
			 {
				 if(resultado.codigoError==1451)
					 this.vista.mostrarMensajeAdvertencia("Error","No se puede eliminar la tarea porque esta relacionada con otro catálogo. ") ;
				 else
					 this.vista.mostrarMensajeError("Error","Ocurrió un error al eliminar la tarea. " + resultado.mensajeError);
			 }
		 },this.vista.llavesTarea);
	 }
	 
	 insertarTarea(modelo)
	 {
		 //var titulo = "";
		 this.vista.mostrarIndicador();	
		 this._repositorio.insertarTarea(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				 this.vista.mostrarMensaje("","Guardado.");
				 this.vista.listaTareas.eliminarBorrador();
				 this.vista.listaTareas.agregar(resultado.valor,modelo);
				 this.vista.mostrarBotonAgregar();
				 this.vista.agregarTarea();
				 //this.vista.seleccionarLeccion(null, resultado.valor);
			 }
			 else
			 {
				 this.vista.mostrarMensajeError("Error", resultado.mensajeError);
			 }
		 },this.vista.minutaId,modelo);
	 }
	 
	 actualizarTarea(modelo)
	 {
		 this.vista.mostrarIndicador();	
		 this._repositorio.actualizarTarea(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				 this.vista.mostrarMensaje("","Guardado.");
				 this.vista.listaTareas.actualizar(modelo);
				 this.vista.listaTareas.cancelarEdicion();
				 //this.vista.listaTareas.agregar(resultado.valor,modelo);
				 //this.vista.mostrarBotonAgregar();
				 //this.vista.agregarTarea();
				 //this.vista.seleccionarLeccion(null, resultado.valor);
			 }
			 else
			 {
				 this.vista.mostrarMensajeError("Error", resultado.mensajeError);
			 }
		 },this.vista.minutaId,modelo);
	 }
	 
}