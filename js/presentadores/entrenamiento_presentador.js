class EntrenamientoPresentador extends CatalogoPresentador
{
	 constructor(vista)
	 {
		 super(vista, new CapacitacionesRepositorio());
	 }
	 
	 guardarRespuestasSi()
	 {
		 this.vista.mostrarIndicador();
		 var repositorio = new CapacitacionesRepositorio(this);		
		 repositorio.guardarRespuestasSi(this,this.guardarRespuestasSiResultado,this.vista.cursoId,this.vista.leccionIdSeleccionada,this.vista.preguntaIdSeleccionada,this.vista.respuestas);
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
		 var repositorio = new CapacitacionesRepositorio(this);		
		 repositorio.guardarRespuestasNo(this,this.guardarRespuestasNoResultado,this.vista.cursoId,this.vista.leccionIdSeleccionada,this.vista.preguntaIdSeleccionada,this.vista.respuestas);
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
	 
	 consultarPerfiles()
	 {
		 this.vista.mostrarIndicador();
		 var repositorio = new PerfilesRepositorio(this);		
		 repositorio.consultar(this, function(resultado)
		 {
			this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
				this.vista.perfiles = resultado.valor;
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
		 },null);
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
		 var repositorio = new CapacitacionesRepositorio(this);		
		 repositorio.ordenarPreguntas(this, function(resultado)
		 {
				this.vista.ocultarIndicador();	
				if(resultado.mensajeError=="")
				{
					this.vista.mostrarMensaje("","Guardado.");
				}
				else
					this.vista.mostrarMensajeError("Error",resultado.mensajeError);
				
		 },this.vista.cursoId, this.vista.leccionIdSeleccionada,seleccion);
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
	 
	 eliminarRespuesta()
	 {
		 this.vista.mostrarIndicador();	
		 var preguntaId = this.vista.llavesRespuesta.preguntaId;
		 var respuestaId = this.vista.llavesRespuesta.respuestaId;
		 this._repositorio.eliminarRespuesta(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 this.vista.cerrarConfirmacionEliminar();
			 if(resultado.mensajeError=="")
			 {
				 this.vista.mostrarMensaje("","Guardado.");
				 this.vista.listaPreguntas.eliminarRespuesta(preguntaId, respuestaId);
			 }
			 else
			 {
				 if(resultado.codigoError==1451)
					 this.vista.mostrarMensajeAdvertencia("Error","No se puede eliminar la respuesta porque esta relacionada con otro catálogo. ") ;
				 else
					 this.vista.mostrarMensajeError("Error","Ocurrió un error al eliminar la respuesta. " + resultado.mensajeError);
			 }
		 },this.vista.llavesRespuesta);
	 }
	 
	 eliminarLeccion()
	 {
		 this.vista.mostrarIndicador();	
		 var leccionId = this.vista.llavesLeccion.leccionId;
		 this._repositorio.eliminarLeccion(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 this.vista.cerrarConfirmacionEliminar();
			 if(resultado.mensajeError=="")
			 {
				 this.vista.mostrarMensaje("","Guardado.");
				 var lecciones =  this.vista.listaLecciones.lecciones;
				 var indice = this.vista.listaLecciones.getIndice(leccionId);
				
				 this.vista.listaLecciones.eliminarLeccion(leccionId);
				 if(indice==0)
				 {
					 var seccionSeleccionada =  this.vista.listaLecciones.lecciones[indice+1];
					 this.vista.seleccionarLeccion(null,seccionSeleccionada.id);
				 }
				 else
				 {
					 var seccionSeleccionada =  this.vista.listaLecciones.lecciones[indice-1];
					 this.vista.seleccionarLeccion(null,seccionSeleccionada.id);
				 }
				
				 //TODO: consultar seccion
				 //this.consultar();
			 }
			 else
			 {
				 if(resultado.codigoError==1451)
					 this.vista.mostrarMensajeAdvertencia("Error","No se puede eliminar la lección porque esta relacionada con otro catálogo. ") ;
				 else
					 this.vista.mostrarMensajeError("Error","Ocurrió un error al eliminar la lección. " + resultado.mensajeError);
			 }
		 },this.vista.llavesLeccion);
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
		 },this.vista.cursoId, this.vista.leccionIdSeleccionada, tipo);
	 }
	 
	 insertarRespuesta(preguntaId)
	 {
		 this.vista.mostrarIndicador();	
		 this._repositorio.insertarRespuesta(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				 this.vista.mostrarMensaje("","Guardado.");
				 this.vista.listaPreguntas.agregarRespuesta(preguntaId,resultado.valor);
			 }
			 else
			 {
				 this.vista.mostrarMensajeError("Error", resultado.mensajeError);
			 }
		 },this.vista.cursoId, this.vista.leccionIdSeleccionada, preguntaId);
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
//			this.vista.consultar();
		}
		else
			this.vista.mostrarMensajeError("Error","Ocurrió un error al guardar el registro. " + resultado.mensajeError);	
		
		 setTimeout(function()
		{
			 this.vista.guardando = false;
         }, 2000);
	 }	
	
	 
	 insertarLeccion()
	 {
		 var titulo = "Sin título";
		 this.vista.mostrarIndicador();	
		 this._repositorio.insertarLeccion(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				 this.vista.mostrarMensaje("","Guardado.");
				 this.vista.listaLecciones.agregarSeccion(titulo,resultado.valor);
				 this.vista.seleccionarLeccion(null, resultado.valor);
			 }
			 else
			 {
				 this.vista.mostrarMensajeError("Error", resultado.mensajeError);
			 }
		 },this.vista.cursoId, titulo);
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
			 },this.vista.cursoId, campo, valor);
		}
	 }
	 
	 actualizarLogo()
	 {
		 if(this.vista.logo!=null)
		{
			 this.vista.mostrarIndicador();	
			 this._repositorio.actualizarLogo(this,function(resultado)
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
			 },this.vista.cursoId, vista.logo);
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
			 },this.vista.cursoId, this.vista.leccionIdSeleccionada, preguntaId, campo, valor);
		}
	 }
	 
	 actualizarValorRespuesta(preguntaId,respuestaId, campo,valor)
	 {
		 if(campo!=undefined)
		{
			 this.vista.mostrarIndicador();	
			 this._repositorio.actualizarValorRespuesta(this,function(resultado)
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
			 },this.vista.cursoId, this.vista.leccionIdSeleccionada, preguntaId, respuestaId, campo, valor);
		}
	 }
	 
	 actualizarValorRespuestaCorrecta(preguntaId,respuestaId,valor)
	 {
		 this.vista.mostrarIndicador();	
		 this._repositorio.actualizarValorRespuestaCorrecta(this,function(resultado)
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
		 },this.vista.cursoId, this.vista.leccionIdSeleccionada, preguntaId, respuestaId, valor);
	 }
	 
	 actualizarCategoriasPregunta(preguntaId,categorias)
	 {
		 this.vista.mostrarIndicador();	
		 this._repositorio.actualizarCategoriasPregunta(this,function(resultado)
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
		 },this.vista.cursoId, this.vista.leccionIdSeleccionada, preguntaId, categorias);
	 }
	 
	 actualizarValorLeccion(leccionId,campo,valor)
	 {
		 if(campo!=undefined)
		{
			 this.vista.mostrarIndicador();	
			 this._repositorio.actualizarValorLeccion(this,function(resultado)
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
			 },this.vista.cursoId, leccionId, campo, valor);
		}
	 }
	 
	 actualizarPerfiles()
	 {
		 this.vista.mostrarIndicador();	
		 this._repositorio.actualizarPerfiles(this,function(resultado)
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
		 },this.vista.cursoId, this.vista.perfiles);
	 }
	
	 ordenarLecciones(seleccion)
	 {
		 this.vista.mostrarIndicador();
		 var repositorio = new CapacitacionesRepositorio(this);		
		 repositorio.ordenarLecciones(this, function(resultado)
		 {
				this.vista.ocultarIndicador();	
				if(resultado.mensajeError=="")
				{
					this.vista.mostrarMensaje("","Guardado.");
				}
				else
					this.vista.mostrarMensajeError("Error",resultado.mensajeError);
				
		 },this.vista.cursoId,seleccion);
	 }
	 
	 eliminarResultado(resultado)
	 {		
		 this.vista.ocultarIndicador();	
		 this.vista.cerrarConfirmacionEliminar();
		 if(resultado.mensajeError=="")
		 {
			 this.vista.mostrarMensaje("Notificación","La capacitación se eliminó correctamente.");
			 this.vista.eliminarCapacitacion(resultado.valor);
			 //this.consultar();
		 }
		 else
		 {
			 if(resultado.codigoError==1451)
				 this.vista.mostrarMensajeAdvertencia("Error","No se puede eliminar la capacitación porque esta relacionado con otro catálogo. ") ;
			 else
				 this.vista.mostrarMensajeError("Error","Ocurrió un error al eliminar la capacitación. " + resultado.mensajeError);
		 }
	 }
	 
	 consultar()
	 {
		 this.consultarCursosContestando();
		 this.consultarCursosPendientes();
		 this.consultarCursosTerminados();
	 }
	 
	 consultarCursosContestando()
	 {
		 this.vista.mostrarIndicador();
		 this._repositorio.consultarCursosContestando(this, function(resultado)
		 {
			this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
				this.vista.cursosContestando = resultado.valor;
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
				
		 },this.vista.criteriosSeleccion);
	 }
	 
	 consultarCursosPendientes()
	 {
		 this.vista.mostrarIndicador();
		 this._repositorio.consultarCursosPendientes(this, function(resultado)
		 {
			this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
				this.vista.cursosPendientes = resultado.valor;
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
					
		 },this.vista.criteriosSeleccion);
	 }
	 
	 consultarCursosTerminados()
	 {
		 this.vista.mostrarIndicador();
		 this._repositorio.consultarCursosTerminados(this, function(resultado)
		 {
				this.vista.ocultarIndicador();	
				if(resultado.mensajeError=="")
					this.vista.cursosTerminados = resultado.valor;
				else
					this.vista.mostrarMensajeError("Error",resultado.mensajeError);
					
		 },this.vista.criteriosSeleccion);
	 }
	 
	 consultarAvanceUsuario()
	 {
		 this.vista.mostrarIndicador();
		 this._repositorio.consultarAvanceUsuario(this, function(resultado)
		 {
				this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
				this.vista.avance = resultado.valor;
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
					
		 });
	 }
	 
	 consultarAprovechamientoUsuario()
	 {
		 this.vista.mostrarIndicador();
		 this._repositorio.consultarAprovechamientoUsuario(this, function(resultado)
		 {
				this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
				this.vista.aprovechamiento = resultado.valor;
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
					
		 });
	 }
	 
	 consultarVideosVistosUsuario()
	 {
		 this.vista.mostrarIndicador();
		 this._repositorio.consultarVideosVistosUsuario(this, function(resultado)
		 {
				this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
				this.vista.videosVistos = resultado.valor;
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
					
		 });
	 }
	 
	 consultarDiasCapacitacionUsuario()
	 {
		 this.vista.mostrarIndicador();
		 this._repositorio.consultarDiasCapacitacionUsuario(this, function(resultado)
		 {
				this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
				this.vista.diasCapacitacion = resultado.valor;
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
					
		 });
	 }
	 
	 consultarEmpresasReporte()	
	 {
		 var repositorio = new EmpresasRepositorio(this);		
		 repositorio.consultar(this, function(resultado)
		 {
			if(resultado.mensajeError=="")
			{
				this.vista.empresasReporte = resultado.valor;
				this.vista.cambiarEmpresaReporte();
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
			
		 },null,false);
	 }

	 consultarSedesReporte()	
	 {
		 var repositorio = new SedesRepositorio(this);		
		 repositorio.consultarPorEmpresa(this, function(resultado)
		 {
			if(resultado.mensajeError=="")
			{
				this.vista.sedesReporte = resultado.valor;			
				//this.vista.cambiarSedeCriterio();
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 }
		,this.vista.criteriosSeleccionReporte.empresaId,true);
	 }
	 
	 
	
}