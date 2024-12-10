class CapacitacionPresentador extends CatalogoPresentador
{
	 constructor(vista)
	 {
		 super(vista, new CapacitacionesRepositorio());
	 }
	 
	 consultarPorToken()
	 {
		 this.vista.mostrarIndicador();	
		 this._repositorio.consultarPorToken(this, function(resultado)
				 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				 this.vista.modelo = resultado.valor;
			 }
			 else
				 this.vista.mostrarCapacitacionInvalida();
		 }
		 ,this.vista.token);
	 }
	 
	 consultarPorTokenSinPreguntas()
	 {
		 this.vista.mostrarIndicador();	
		 this._repositorio.consultarPorTokenSinPreguntas(this, function(resultado)
				 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				 this.vista.modelo = resultado.valor;
			 }
			 else
				 this.vista.mostrarCapacitacionInvalida();
		 }
		 ,this.vista.token);
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
	
	consultarPreguntaAleatoria(seccionId)
	{
		 this.vista.mostrarIndicador();	
		 this._repositorio.consultarPreguntaAleatoria(this, function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 this.vista.guardando = false;
			 if(resultado.mensajeError=="")
			 {
				
				if(this.vista.leccionIdSeleccionada == resultado.valor.leccionId)
				{
					this.vista.mostrarPregunta(resultado.valor.leccionId,resultado.valor.pregunta, resultado.valor.numeroPreguntasRestantes,resultado.valor.numeroPreguntasContestadas);
					
				}
			 }
			 else
				 this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 }
		 ,this.vista.cursoId, seccionId, this.vista.modo);
	}
	
	terminarLeccionCurso(leccionId)
	{
		this.vista.mostrarIndicador();	
		 this._repositorio.terminarLeccionCurso(this, function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 this.vista.guardando = false;
			 if(resultado.mensajeError=="")
			 {
				this.evaluarCalificacion(leccionId,resultado.valor);
			 }
			 else
				 this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 }
		 ,this.vista.cursoId, leccionId);
	}
	
	evaluarCalificacion(leccionId,valor)
	{
		if(valor!=null)
		{
			if(parseFloat(valor.porcentaje) < parseFloat(valor.calificacionMinima))
			{
				vista.mostrarMensajeCalificacionInferior(valor.titulo,valor.porcentaje);
			}
			else
			{
				this.vista.listaLecciones.terminarLeccion(leccionId);
				var html=this.vista.textoLeccionTerminada;
 				$("#divBotonesPreguntas").html(html);
			}
		}
	}
	
	actualizarDuracionLeccion(leccionId,duracion)
	{
		 this.vista.mostrarIndicador();	
		 this._repositorio.actualizarDuracionLeccion(this, function(resultado)
				 {		
			 this.vista.ocultarIndicador();	
			 this.vista.guardando = false;
			 if(resultado.mensajeError=="")
			 {
				
//				 if(this.vista.leccionIdSeleccionada == resultado.valor.leccionId)
//					 this.vista.mostrarPregunta(resultado.valor.leccionId,resultado.valor.pregunta, resultado.valor.numeroPreguntasRestantes,resultado.valor.numeroPreguntasContestadas);
			 }
			 else
				 this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 }
		 ,this.vista.cursoId, leccionId,duracion);
	}
	 
	
	
	guardarLeccionUsuario(leccionId,duracion)
	{
		 this.vista.mostrarIndicador();	
		 this._repositorio.guardarLeccionUsuario(this, function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				 //this.vista.mostrarPregunta(resultado.valor.pregunta, resultado.valor.numeroPreguntasRestantes,resultado.valor.numeroPreguntasContestadas);
			 }
			 else
				 this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 }
		 ,this.vista.cursoId, leccionId, duracion);
	}
	 
	guardarPreguntaUsuario(preguntaId, respuestaId)
	{
		 this.vista.mostrarIndicador();	
		 this.vista.guardando = true;
		 this._repositorio.guardarPreguntaUsuario(this, function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			
			 if(resultado.mensajeError=="")
			 {
				 this.consultarPreguntaAleatoria(this.vista.leccionIdSeleccionada);
				 //this.vista.mostrarPregunta(resultado.valor.pregunta, resultado.valor.numeroPreguntasRestantes,resultado.valor.numeroPreguntasContestadas);
			 }
			 else
				 this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 }
		 ,this.vista.cursoId, this.vista.leccionIdSeleccionada,preguntaId,respuestaId );
	}
	
	eliminarIntentoLeccion(calificacion, volverAVer)
	{
		this.vista.mostrarIndicador();	
		this._repositorio.eliminarUsuarioCapacitacionLeccion(this, function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
			 	this.vista.cerrarIntento(this.vista._leccionIdSeleccionada,volverAVer);
			 }
			 else
				 this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 }
		 ,this.vista.usuario.id, this.vista.cursoId, this.vista._leccionIdSeleccionada,calificacion);
	
	} 
}