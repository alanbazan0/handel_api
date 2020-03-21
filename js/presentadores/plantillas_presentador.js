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

	 ordenarPreguntas(seleccion)
	 {
		 this.vista.mostrarIndicador();
		 var repositorio = new PlantillasRepositorio(this);		
		 repositorio.ordenarPreguntas(this, function(resultado)
		 {
				this.vista.ocultarIndicador();	
				if(resultado.mensajeError=="")
				{
					this.vista.mostrarMensaje("","Guardado.");
				}
				else
					this.vista.mostrarMensajeError("Error",resultado.mensajeError);
				
		 },this.vista.plantillaId, this.vista.seccionIdSeleccionada,seleccion);
	 }
	 
	 eliminarPregunta()
	 {
		 this.vista.mostrarIndicador();	
		 var preguntaId = this.vista.llavesPregunta.preguntaId;
		 this._repositorio.eliminarPregunta(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 this.vista.cerrarConfirmacionEliminar();
			 if(resultado.mensajeError=="")
			 {
				 this.vista.mostrarMensaje("","Guardado.");
				 this.vista.listaPreguntas.eliminarPregunta(preguntaId);
				 //TODO: consultar seccion
				 //this.consultar();
			 }
			 else
			 {
				 if(resultado.codigoError==1451)
					 this.vista.mostrarMensajeAdvertencia("Error","No se puede eliminar la pregunta porque esta relacionada con otro catálogo. ") ;
				 else
					 this.vista.mostrarMensajeError("Error","Ocurrió un error al eliminar la pregunta. " + resultado.mensajeError);
			 }
		 },this.vista.llavesPregunta);
	 }
	 
	 eliminarSeccion()
	 {
		 this.vista.mostrarIndicador();	
		 var seccionId = this.vista.llavesSeccion.seccionId;
		 this._repositorio.eliminarSeccion(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 this.vista.cerrarConfirmacionEliminar();
			 if(resultado.mensajeError=="")
			 {
				 this.vista.mostrarMensaje("","Guardado.");
				 this.vista.listaSecciones.eliminarSeccion(seccionId);
				 //TODO: consultar seccion
				 //this.consultar();
			 }
			 else
			 {
				 if(resultado.codigoError==1451)
					 this.vista.mostrarMensajeAdvertencia("Error","No se puede eliminar la sección porque esta relacionada con otro catálogo. ") ;
				 else
					 this.vista.mostrarMensajeError("Error","Ocurrió un error al eliminar la sección. " + resultado.mensajeError);
			 }
		 },this.vista.llavesSeccion);
	 }
	 
	 insertarPregunta(tipo)
	 {
		 this.vista.mostrarIndicador();	
		 //var preguntaId = this.vista.llavesPregunta.preguntaId;
		 this._repositorio.insertarPregunta(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 //this.vista.cerrarConfirmacionEliminar();
			 if(resultado.mensajeError=="")
			 {
				 this.vista.mostrarMensaje("","Guardado.");
				 this.vista.listaPreguntas.agregar(tipo,resultado.valor);
			 }
			 else
			 {
				 this.vista.mostrarMensajeError("Error", resultado.mensajeError);
			 }
		 },this.vista.plantillaId, this.vista.seccionIdSeleccionada, tipo);
	 }
	 
	 insertarResultado(resultado)
	 {
		this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
		{	
			this.vista.mostrarMensaje("Notificación","La información se guardó correctamente. Id: " + resultado.valor);
			this.vista.salirFormularioAlta();
			this.vista._llaves = {id : resultado.valor};
			this.vista.editar();
		}
		else
			this.vista.mostrarMensajeError("Error","Ocurrió un error al guardar el registro. " + resultado.mensajeError);	
		
		 setTimeout(function()
		{
			 this.vista.guardando = false;
         }, 2000);
	 }	
	
	 
	 insertarSeccion()
	 {
		 this.vista.mostrarIndicador();	
		 this._repositorio.insertarSeccion(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				 this.vista.mostrarMensaje("","Guardado.");
				 this.vista.listaSecciones.agregarSeccion("",resultado.valor);
			 }
			 else
			 {
				 this.vista.mostrarMensajeError("Error", resultado.mensajeError);
			 }
		 },this.vista.plantillaId);
	 }
	 
	 actualizarValor(campo,valor)
	 {
		 if(campo!=undefined)
		{
			 this.vista.mostrarIndicador();	
			 //var preguntaId = this.vista.llavesPregunta.preguntaId;
			 this._repositorio.actualizarValor(this,function(resultado)
			 {		
				 this.vista.ocultarIndicador();	
				 //this.vista.cerrarConfirmacionEliminar();
				 if(resultado.mensajeError=="")
				 {
					 this.vista.mostrarMensaje("","Guardado.");
				 }
				 else
				 {
					 this.vista.mostrarMensajeError("Error", resultado.mensajeError);
				 }
			 },this.vista.plantillaId, campo, valor);
		}
	 }
	 
	 actualizarValorPregunta(preguntaId,campo,valor)
	 {
		 if(campo!=undefined)
		{
			 this.vista.mostrarIndicador();	
			 this._repositorio.actualizarValorPregunta(this,function(resultado)
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
			 },this.vista.plantillaId, this.vista.seccionIdSeleccionada, preguntaId, campo, valor);
		}
	 }
	 
	 actualizarValorSeccion(seccionId,campo,valor)
	 {
		 if(campo!=undefined)
		{
			 this.vista.mostrarIndicador();	
			 this._repositorio.actualizarValorSeccion(this,function(resultado)
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
			 },this.vista.plantillaId, seccionId, campo, valor);
		}
	 }
	
	 ordenarSecciones(seleccion)
	 {
		 this.vista.mostrarIndicador();
		 var repositorio = new PlantillasRepositorio(this);		
		 repositorio.ordenarSecciones(this, function(resultado)
		 {
				this.vista.ocultarIndicador();	
				if(resultado.mensajeError=="")
				{
					this.vista.mostrarMensaje("","Guardado.");
				}
				else
					this.vista.mostrarMensajeError("Error",resultado.mensajeError);
				
		 },this.vista.plantillaId,seleccion);
	 }
	 
}