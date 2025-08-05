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
		 repositorio.consultarPorLlaves(this, function(resultado)
				 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				 this.vista.modelo = resultado.valor;
			 }
			 else
				 this.vista.mostrarMensajeError("Error","Ocurrió un error al consultar el registro. " + resultado.mensajeError);
		 },this.vista.llaves);
	 }
	 
	
	
	 consultarPlantillaId()
	 {
		 this.vista.mostrarIndicador();	
		 var repositorio = new AuditoriasRepositorio();
		 repositorio.consultarPorLlaves(this,function(resultado)
		 {
			 if(resultado.mensajeError=="")
				 this.vista.plantillaId = resultado.valor.plantillaId;
			 else
				 this.vista.mostrarMensajeError("Error","Ocurrió un error al consultar el registro. " + resultado.mensajeError);
		 },{id:this.vista.auditoriaId});
	 }
	 
	 consultarUsuariosSeccion()
	 {
		if(this.vista.empresaId!="")
		{
		 this.vista.mostrarIndicador();	
		 var repositorio = new UsuariosRepositorio(this);		
		 repositorio.consultarUsuariosCorportarivoYAdministradoresPorEmpresa(this, function(resultado)
		 {
			this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
			{
				this.vista.usuariosSeccion = resultado.valor;			
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 }
		,this.vista.empresaId);	
		}
		
		/*repositorio.consultar(this,function(resultado)
		{
			this.vista.usuariosSeccion = resultado.valor;
		},{empresaId: this.vista.empresaId});*/arguments
		
		
		//},{empresaId: this.vista.empresaId, permisoSIVAH: "1"});
	 }
	 
	 consultarUsuariosResultado(resultado)
	 {
		this.usuarios = resultado.valor;	
		for(var i=0; i< this._pregunta.respuestas_si.length; i++)
		{
			var respuesta = this._pregunta.respuestas_si[i];
			var idRespuesta = this.viewport + "Pregunta"+ this._pregunta.id+"Respuesta"+ respuesta.id;
			var select = idRespuesta+"selectResponsable";
			this.cargarOpcionesUsuario("#"+select, resultado.valor,null,"usuarioId");	
			var responsableId = $("#"+select).data("responsableId");
			if(responsableId!=null)
				if(responsableId!="")
					$("#"+select).val(responsableId);
				
		}
	 }

	iniciarSeguimiento()
	{
	 	var llaves ={plantillaId: this.vista.modeloEdicion.plantillaId,
			 	id: this.vista.modeloEdicion.id
	 			};
		this.vista.mostrarIndicador();	
		 this._repositorio.iniciarSeguimiento(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
		 	 this.vista.cerrarConfirmacionEliminar();
			 if(resultado.mensajeError=="")
			 {
				 this.vista.mostrarMensaje("Notificación","Se inició el seguimiento correctamente.");
				 this.vista.cerrar();
			 }
			 else
			 {
				if(resultado.codigoError == 5001)
					this.vista.mostrarMensajeAdvertencia("Advertencia",resultado.mensajeError);
				else
				 	this.vista.mostrarMensajeError("Error",resultado.mensajeError, resultado.codigoError);
			 }
		 },llaves);
	}
	
	finalizarSeguimiento()
	{
	 	var llaves ={plantillaId: this.vista.modeloEdicion.plantillaId,
			 	id: this.vista.modeloEdicion.id
	 			};
		this.vista.mostrarIndicador();	
		 this._repositorio.finalizarSeguimiento(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
		 	 this.vista.cerrarConfirmacionEliminar();
			 if(resultado.mensajeError=="")
			 {
				 this.vista.mostrarMensaje("Notificación","Se finalizó el seguimiento correctamente.");
				 this.vista.cerrar();
			 }
			 else
			 {
				if(resultado.codigoError == 5001)
					this.vista.mostrarMensajeAdvertencia("Advertencia",resultado.mensajeError);
				else
				 	this.vista.mostrarMensajeError("Error",resultado.mensajeError, resultado.codigoError);
			 }
		 },llaves);
	}
	
	 consultarUsuariosCorreo()
	 {
		this.vista.mostrarIndicador();	
		 var repositorio = new UsuariosRepositorio(this);		
		 repositorio.consultarUsuariosCorportarivoYAdministradoresPorEmpresa(this, function(resultado)
		 {
			this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
			{
				this.vista.usuariosCorreo = resultado.valor;			
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 }
		 //);
		,this.vista.empresaId);
	}
	
	consultarHallazgosSecciones()
	{
		 var llaves ={plantillaId: this.vista.modeloEdicion.plantillaId,
				 	id: this.vista.modeloEdicion.id};
		this.vista.mostrarIndicador();	
		 var repositorio = new AuditoriasRepositorio(this);		
		 repositorio.consultarHallazgosSecciones(this, function(resultado)
		 {
			this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
			{
				this.vista.hallazgosSecciones = resultado.valor;			
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 }
		,llaves);
	}
	
	enviarCorreoXRay()
	{
		this.vista.mostrarIndicador();	
		 var repositorio = new AuditoriasRepositorio(this);		
		 repositorio.enviarCorreoXRay(this, function(resultado)
		 {
			this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
			{
				this.vista.mostrarMensaje("Notificación","Enviado");
				this.vista.cerrarModal("correoXRayModal");
				//this.vista.usuariosCorreo = resultado.valor;			
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 }
		,this.vista.auditoriaId, this.vista.plantillaId, this.vista.referencia, this.vista.hallazgos, this.vista.usuariosCorreo, this.vista.usuarioXRay, this.vista.empresaId, this.vista.sedeId, this.vista.fecha);
	}
	
	notificarCliente()
	{
		this.vista.mostrarIndicador();	
		 var repositorio = new AuditoriasRepositorio(this);		
		 repositorio.enviarNotificacionCliente(this, function(resultado)
		 {
			this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
			{
				if(resultado.valor.sociosComerciales.length==0)
				{
					his.vista.mostrarMensajeAdvertencia("Advertencia","No fue enviada la notificación, la emmpresa " + resultado.valor.empresaNombre + "no tiene socios comerciales configurados");
				}
				else
				{
					this.vista.mostrarMensaje("Notificación","Enviado");
					 window.close();
				 }
				//this.vista.cerrarModal("correoXRayModal");
				//this.vista.usuariosCorreo = resultado.valor;			
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 }
		,this.vista.auditoriaId);
	}
}