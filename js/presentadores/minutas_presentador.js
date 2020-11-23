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
					 this.vista.mostrarMensajeError("Error", resultado.mensajeError, resultado.codigoError);
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
			this.vista._registroSeleccionado = {id : resultado.valor};
			this.vista.editar();
		}
		else
			this.vista.mostrarMensajeError("Error","Ocurrió un error al guardar el registro. " + resultado.mensajeError, resultado.codigoError);	
		
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
					this.vista.mostrarMensajeError("Error",resultado.mensajeError, resultado.codigoError);
				
		 },this.vista.minutaId,seleccion);
	 }
	 
	 
	 actualizarValorTarea(componente,tarea,tareaId, campo,valor)
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
					 this.vista.actualizar(componente,tarea);
					 
					 if(campo=="terminada")
					 {
						if(componente.listaTareas.removerTerminada) 
						{
							componente.listaTareas.eliminarTarea(tarea.minutaId, tarea.id);
						}
						this.consultarPorcentajeAvance();
					 }
				 }
				 else
				 {
					 this.vista.mostrarMensajeError("Error", resultado.mensajeError, resultado.codigoError);
				 }
			 },tarea.minutaId, tarea.id, campo, valor);
		}
	 }

	consultarPorcentajeAvance()
	{
		 this._repositorio.consultarPorcentajeAvance(this,function(resultado)
		 {		
			 if(resultado.mensajeError=="")
			 {
				 this.vista.porcentajeAvance = resultado.valor;
			 }
			 else
			 {
				 this.vista.mostrarMensajeError("Error", resultado.mensajeError, resultado.codigoError);
			 }
		 },this.vista.minutaId);
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
				 //var indice = this.vista.listaTareas.getIndice(tareaId);
				
				 this.vista.listaTareas.eliminarTarea(this.vista.minutaId, tareaId);
			 }
			 else
			 {
				 if(resultado.codigoError==1451)
					 this.vista.mostrarMensajeAdvertencia("Error","No se puede eliminar la tarea porque esta relacionada con otro catálogo. ") ;
				 else
					 this.vista.mostrarMensajeError("Error","Ocurrió un error al eliminar la tarea. " + resultado.mensajeError, resultado.codigoError);
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
				 this.vista.listaTareas.agregar(this.vista.minutaId,resultado.valor,modelo);
				 this.vista.mostrarBotonAgregar();
				 this.vista.agregarTarea();
				 //this.vista.seleccionarLeccion(null, resultado.valor);
			 }
			 else
			 {
				 this.vista.mostrarMensajeError("Error", resultado.mensajeError, resultado.codigoError);
			 }
		 },this.vista.minutaId,modelo);
	 }
	 
	 actualizarTarea(componente,modelo)
	 {
		 this.vista.mostrarIndicador();	
		 this._repositorio.actualizarTarea(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				 this.vista.mostrarMensaje("","Guardado.");
//				 this.vista.listaTareas.actualizar(modelo);
//				 this.vista.listaTareas.cancelarEdicion();
				 this.vista.actualizar(componente,modelo);
			 }
			 else
			 {
				 this.vista.listaTareas.activarBotonGuardar();
				 this.vista.mostrarMensajeError("Error", resultado.mensajeError, resultado.codigoError);
			 }
		 },this.vista.minutaId,modelo);
	 }
	 
	 consultarResponsables()
	 {
		 this.vista.mostrarIndicador();
		 var  reposiorio = new UsuariosRepositorio();
		 reposiorio.consultarUsuariosCorportarivoYAdministradores(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				 this.vista.responsables = resultado.valor;
			 }
			 else
			 {
				 this.vista.mostrarMensajeError("Error", resultado.mensajeError, resultado.codigoError);
			 }
		 });
	 }
	 
	 consultarTareaPorLlaves()
	 {
		 this.vista.mostrarIndicador();
		 var  reposiorio = new MinutasRepositorio();
		 reposiorio.consultarTareaPorLlaves(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				 this.vista.tarea = resultado.valor;
			 }
			 else
			 {
				 this.vista.mostrarMensajeError("Error", resultado.mensajeError, resultado.codigoError);
			 }
		 }, this.vista.llavesTarea);
	 }
	 
	 consultarComentarios()
	 {
		// this.vista.mostrarIndicador();
		 var repositorio = new TareasComentariosRepositorio(this);		
		 repositorio.consultar(this, function(resultado)
		 {
				//this.vista.ocultarIndicador();	
				if(resultado.mensajeError=="")
					this.vista.comentarios = resultado.valor;
				else
					this.vista.mostrarMensajeError("Error",resultado.mensajeError, resultado.codigoError);
				
		 },this.vista.llavesTarea);
	 }
	
	 enviarComentario()
	 {
		 //this.vista.mostrarIndicador();
		 var repositorio = new TareasComentariosRepositorio(this);		
		 repositorio.insertar(this, function(resultado)
		 {
			this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
				this.vista.consultarComentarios();
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError, resultado.codigoError);
				
		 },this.vista.modeloComentario);
	 }
	
	 
	 actualizarUsuarios()
	 {
		 this.vista.mostrarIndicador();	
		 this._repositorio.actualizarUsuarios(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				 this.vista.mostrarMensaje("","Guardado.");
			 }
			 else
			 {
				 this.vista.mostrarMensajeError("Error", resultado.mensajeError, resultado.codigoError);
			 }
		 },this.vista.minutaId, this.vista.usuarios);
	 }
	 
	 consultarMisTareasPendientes()
	 {
		// this.vista.mostrarIndicador();
		 var repositorio = new MinutasRepositorio(this);		
		 repositorio.consultarMisTareas(this,function(resultado){
			 if(resultado.mensajeError=="")
			{
				this.vista.misTareasPendientes = resultado.valor;
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError, resultado.codigoError);
		 },{"terminada":0});
	 }
	 
	 consultarNumeroComentariosTarea(minutaId, tareaId)
	 {
		 var repositorio = new MinutasRepositorio(this);		
		 repositorio.consultarNumeroComentariosTarea(this,function(resultado){
			 if(resultado.mensajeError=="")
			{
				 this.vista.setNumeroComentariosTarea(minutaId, tareaId, resultado.valor);
				//this.vista.misTareasPendientes = resultado.valor;
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError, resultado.codigoError);
		 }, minutaId, tareaId);
	 }
	
	 
}