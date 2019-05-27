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
			if(this.vista.funcion=="siguente")
				this.vista.mostrarSiguiente();
			else if(this.vista.funcion=="atras")
				this.vista.mostrarAnterior();
			else if(this.vista.funcion=="finalizar")
				this.vista.mostrarFinalizacion();
			this.vista.mostrarMensaje("Guardado"," Referencia: " + this.vista.modeloEdicion.referencia) ;
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
			this.vista.modeloEdicion= resultado.valor;
			this.vista.mostrarReferencia();
			if(this.vista.funcion=="siguente")
				this.vista.mostrarSiguiente();
			else if(this.vista.funcion=="atras")
				this.vista.mostrarAnterior();
			else if(this.vista.funcion=="finalizar")
				this.vista.mostrarFinalizacion();
				
			this.vista.mostrarMensaje("Actualización"," Referencia: " + this.vista.modeloEdicion.referencia) ;
		 }
		 else
		 {
			 if(resultado.codigoError==1451)
				 this.vista.mostrarMensajeError("Error","No se puede eliminar el registro porque esta relacionado con otro catálogo. ") ;
			 else
				 this.vista.mostrarMensajeError("Error","Ocurrió un error al eliminar el registro. " + resultado.mensajeError);
		 }
	 }
	
	consultarValores()
	{
		 this.vista.mostrarIndicador();	
		 var repositorio = new AuditoriasRepositorio();
		 var llaves ={plantillaId: this.vista.modeloEdicion.plantillaId,
				 	auditoriaId: this.vista.modeloEdicion.id,
				 	seccionId: this.vista.seccionId
		 			};
		 repositorio.consultarValoresSeccion(this,this.consultarValoresResultado,llaves);
	}
	
	consultarValoresResultado(resultado)
	 {		
		 this.vista.ocultarIndicador();	
		 if(resultado.mensajeError=="")
		 {
			 this.vista.modeloDatos = resultado.valor;
		 }
		 else
			 this.vista.mostrarMensajeError("Error",resultado.mensajeError);
	 }
	
	 consultarPorLlaves()
	 {
		 this.vista.mostrarIndicador();	
		 var repositorio = new PlantillasRepositorio();
		 repositorio.consultarPorLlaves(this,this.consultarPorLlavesResultado,this.vista.llaves);
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
	

	 
}