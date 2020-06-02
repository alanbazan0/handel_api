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
					 this.vista.mostrarPregunta(resultado.valor.leccionId,resultado.valor.pregunta, resultado.valor.numeroPreguntasRestantes,resultado.valor.numeroPreguntasContestadas);
			 }
			 else
				 this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 }
		 ,this.vista.cursoId, seccionId, this.vista.modo);
	}
	 
	
	
	guardarLeccionUsuario()
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
		 ,this.vista.cursoId, this.vista.leccionIdSeleccionada);
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
	 
}