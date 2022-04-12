class RevisionProcesosPresentador extends CatalogoPresentador
{
	 constructor(vista)
	 {
		 super(vista,new ProcesosRevisadosRepositorio());
	 }

	 
	 consultarEmpresasCriterio()	
	 {
		 var repositorio = new EmpresasRepositorio(this);		
		 repositorio.consultar(this, function(resultado)
		 {
			if(resultado.mensajeError=="")
			{
				this.vista.empresasCriterio = resultado.valor;
				this.vista.cambiarEmpresaCriterio();
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError,resultado.codigoError);
			
		 },{estatus:1},true);
	 }

 	consultarAdministradoresCriterio()	
	 {
		 var repositorio = new UsuariosRepositorio(this);		
		 repositorio.consultar(this, function(resultado)
		 {
			if(resultado.mensajeError=="")
			{
				this.vista.administradoresCriterio = resultado.valor;
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError,resultado.codigoError);
			
		 },{estatus:1, tipoUsuarioId : TipoUsuario.ADMINISTRADOR},true);
	 }
	 
	
	 
	 
	 consultarAnos()
	 {
		 this.vista.mostrarIndicador();
		 this._repositorio.consultarAnos(this,function(resultado)
		 {
			 this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
				this.vista.anos = resultado.valor;
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError,resultado.codigoError);
		 });
	 }
	 
	 
	 consultarSedesCriterio()	
	 {
		 var repositorio = new SedesRepositorio(this);		
		 repositorio.consultarPorEmpresa(this, function(resultado)
		 {
			if(resultado.mensajeError=="")
			{
				this.vista.sedesCriterio = resultado.valor;			
				this.vista.cambiarSedeCriterio();
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError,resultado.codigoError);
		 }
		,this.vista.criteriosSeleccion.empresaId,true);
	 }
	 
	 consultarDepartamentosCriterio()	
	 {
		 var repositorio = new DepartamentosRepositorio(this);		
		 repositorio.consultar(this, function(resultado)
		 {
			if(resultado.mensajeError=="")
			{
				this.vista.departamentosCriterio = resultado.valor;			
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError,resultado.codigoError);
		 }
		,null,true);
	 }

	validarSinCambios(ids)
	{
		vista.cargando = true;
		 this._repositorio.validarSinCambios(this, function(resultado)
		 {
			vista.cargando = false;
			if(resultado.mensajeError=="")
			{
				this.vista.eliminarValidadas(ids);			
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError,resultado.codigoError);
		 }
		,ids);
	}
	
	consultarComentariosEvidencia()
	 {
		// this.vista.mostrarIndicador();
		 var repositorio = new EvidenciasComentariosRepositorio(this);		
		 repositorio.consultar(this,function(resultado)
		 {
			//this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
				this.vista.comentariosEvidencia = resultado.valor;
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError,resultado.codigoError);
			
		 } ,{evidenciaId: this.vista.evidenciaSeleccionada.id});
	 }
	
	 enviarComentarioEvidencia()
	 {
		 if(this.vista.modeloCometarioEvidencia.comentario!="" && this.vista.modeloCometarioEvidencia.comentario!=undefined)
		 {
			 var repositorio = new EvidenciasComentariosRepositorio(this);		
			 repositorio.insertar(this, function(resultado)
			 {
				this.vista.ocultarIndicador();	
				if(resultado.mensajeError=="")
					this.vista.consultarComentariosEvidencia();
				else
					this.vista.mostrarMensajeError("Error",resultado.mensajeError,resultado.codigoError);
				
			 },this.vista.modeloCometarioEvidencia);
		}
	 }
	
	consultarComentariosPredefinidos()
	 {
		 this.vista.mostrarIndicador();
		 var repositorio = new ComentariosPredefinidosRepositorio(this);		
		 repositorio.consultar(this,function(resultado)
		 {
			 this.vista.ocultarIndicador();	
				if(resultado.mensajeError=="")
					this.vista.comentariosPredefinidos = resultado.valor;
				else
					this.vista.mostrarMensajeError("Error",resultado.mensajeError,resultado.codigoError);
		 });
	 }
	 
	 validarEvidencia(cerrar)
	 {
		 this.vista.guardando = true;
		 this.vista.mostrarIndicador();	
		 var  respositorio = new EvidenciasRepositorio();
		 var modelo = this.vista.modeloValidacion;
		 respositorio.validarEvidencia(this, function(resultado)
		 {
			this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
			{	
				//this.vista.mostrarMensaje("Notificación","Guardado. Id: " + resultado.valor);
				this.vista.actualizarEvidencia(modelo);
				if(cerrar)
				{
					this.vista.salirFormulario();
				}
				else
				{
					if(!this.vista.mostrarSiguienteEvidenciaValidacion())
					{
						this.vista.mostrarMensaje("","Validación terminada");
						this.vista.salirFormulario();
						
					}
				}
				this.vista.mostrarMensaje(modelo.nombre, "Validada correctamente");
			
			}
			else
			{
				this.vista.mostrarMensajeError("Error","No se guardo la información. " + resultado.mensajeError,resultado.codigoError);	
					
			}
			
			 setTimeout(function()
			{
				 this.vista.guardando = false;
	       }, 2000);
			
				
		 }	,modelo);	
	 }
	 
	consultarEstatusValidacionProcesos()
	 {
		 this.vista.mostrarIndicador();
		 var repositorio = new EstatusValidacionProcesosRepositorio(this);		
		 repositorio.consultar(this,function(resultado)
		 {
			 this.vista.ocultarIndicador();	
				if(resultado.mensajeError=="")
					this.vista.estatusValidacionProcesos = resultado.valor;
				else
					this.vista.mostrarMensajeError("Error",resultado.mensajeError,resultado.codigoError);
		 },{},true);
	 }

	consultarProcesoRevisadoPorLlaves(llaves)
	{
		this.vista.mostrarIndicador();	
		 this._repositorio.consultarPorLlaves(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				 this.vista.modeloProceso = resultado.valor;
                 this.vista.mostrarFormularioRevision(resultado.valor);					
				if(resultado.valor.estatusRevisionId == EstatusRevision.OBSERVACIONES)
				 	this.consultarObservaciones();
			 }
			 else
				 this.vista.mostrarMensajeError("Error","Ocurrió un error al consultar el registro. " + resultado.mensajeError, resultado.codigoError);
		 },llaves);
	}
	
	consultarObservacionPorLlavesValidacion(llaves)
	{
		this.vista.mostrarIndicador();	
		var repositorio = new ProcesosRevisadosObservacionesRepositorio();
		 repositorio.consultarPorLlaves(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				 this.vista.modeloObservacion = resultado.valor;
                 this.vista.mostrarFormularioRevisionObservacion(resultado.valor);					
			 }
			 else
				 this.vista.mostrarMensajeError("Error","Ocurrió un error al consultar el registro. " + resultado.mensajeError, resultado.codigoError);
		 },llaves);
	}
	
	consultarObservaciones()
	 {
		 this.vista.mostrarIndicador();
		var repositorio = new ProcesosRevisadosObservacionesRepositorio();
		 repositorio.consultar(this,function(resultado)
		 {
			 this.vista.ocultarIndicador();	
				if(resultado.mensajeError=="")
					this.vista.observaciones = resultado.valor;
				else
					this.vista.mostrarMensajeError("Error",resultado.mensajeError,resultado.codigoError);
		 },{procesoRevisadoId: this.vista._procesoSeleccionado.id});
	 }

	actualizarEstatusValidacionProceso(estatusValidacionId, comentario)
	{
		this.vista.mostrarIndicador();	
		 this._repositorio.actualizarEstatusValidacionProceso(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				 this.vista.cerrarModal("validacionComentarioModal");
				 this.vista.cerrarModal("procesoModal");
				 this.vista.mostrarMensaje("Notificación","Guardado");
				 this.vista.modeloProceso = resultado.valor;
			 }
			 else
				 this.vista.mostrarMensajeError(resultado.mensajeError, resultado.codigoError);
		 },this.vista._procesoSeleccionado.id, estatusValidacionId, comentario);
	}
	
	actualizarEstatusValidacionObservacion(estatusValidacionId, comentario, seccion, descripcion)
	{
		this.vista.mostrarIndicador();	
		var repositorio = new ProcesosRevisadosObservacionesRepositorio();
		 repositorio.actualizarEstatusValidacion(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				this.vista.cerrarModal("validacionComentarioModal");
				 this.vista.cerrarModal("observacionModal");
				 this.vista.mostrarMensaje("Notificación","Guardado");
				 this.vista.modeloObservacion = resultado.valor;
			 }
			 else
				 this.vista.mostrarMensajeError(resultado.mensajeError, resultado.codigoError);
		 },this.vista._procesoSeleccionado.id,this.vista._observacionSeleccionada.id, estatusValidacionId,comentario, seccion, descripcion);
	}
	
	consultarComentariosObservacion()
	 {
		// this.vista.mostrarIndicador();
		 var repositorio = new ObservacionesComentariosRepositorio(this);		
		 repositorio.consultar(this, function(resultado)
		 {
			//this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
				this.vista.comentariosObservacion = resultado.valor;
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError,resultado.codigoError);
			
		 },{procesoRevisadoId: this.vista._procesoSeleccionado.id, observacionId: this.vista._observacionSeleccionada.id});
	 }
	
	 
	enviarComentarioObservacion()
	 {
		 if(this.vista.modeloComentarioObservacion.comentario!="" && this.vista.modeloComentarioObservacion.comentario!=undefined)
		 {
			 var repositorio = new ObservacionesComentariosRepositorio(this);		
			 repositorio.insertar(this,	 function(resultado)
			 {
				this.vista.ocultarIndicador();	
				if(resultado.mensajeError=="")
					this.vista.consultarComentariosObservacion();
				else
					this.vista.mostrarMensajeError("Error",resultado.mensajeError,resultado.codigoError);
				
			 },this.vista.modeloComentarioObservacion);
		}
	 }

	consultarObservacionPorLlaves()
	{
		this.vista.mostrarIndicador();
		var repositorio = new ProcesosRevisadosObservacionesRepositorio();	
		 repositorio.consultarPorLlaves(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				 this.vista.modeloObservacion = resultado.valor;
			 }
			 else
				 this.vista.mostrarMensajeError("Error","Ocurrió un error al consultar el registro. " + resultado.mensajeError, resultado.codigoError);
		 },this.vista.llavesObservacion);
	}
	
	consultarTiposObservacion()
	 {
		 this.vista.mostrarIndicador();
		 var repositorio = new TiposObservacionRepositorio(this);		
		 repositorio.consultar(this,function(resultado)
		{
			this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
				this.vista.tiposObservacion = resultado.valor;
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		});
	 }

	guardarObservacionNueva(observacion)
	 {
		 this.vista.mostrarIndicador();	
		this.vista.guardando = true;
		 observacion.procesoRevisadoId = this.vista._procesoSeleccionado.id;
		var repositorio = new ProcesosRevisadosObservacionesRepositorio();
		 repositorio.insertar(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 //this.vista.cerrarConfirmacionEliminar();
			 if(resultado.mensajeError=="")
			 {
				this.vista.guardando = false;
				 this.vista.cerrarModal("observacionModal");
				 this.vista.mostrarMensaje("Notificación","Guardado.");
				 this.consultarObservaciones();
				 //this.vista.eliminarProceso(usuarioProceso);
			 }
			 else
			 {
				 this.vista.mostrarMensajeError("Error",resultado.mensajeError, resultado.codigoError);
			 }
		 },observacion);
	 }

	 
	 
}