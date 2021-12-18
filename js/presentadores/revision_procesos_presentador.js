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
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
			
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
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
			
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
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
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
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
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
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 }
		,null,true);
	 }

	validarJustificadas(ids)
	{
		vista.cargando = true;
		 this._repositorio.validarJustificadas(this, function(resultado)
		 {
			vista.cargando = false;
			if(resultado.mensajeError=="")
			{
				this.vista.eliminarValidadas(ids);			
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
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
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
			
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
					this.vista.mostrarMensajeError("Error",resultado.mensajeError);
				
			 },this.vista.modeloCometarioEvidencia);
		}
	 }
	
	consultarEvidenciaPorLlaves()
	{
		this.vista.mostrarIndicador();	
		 this._repositorio.consultarPorLlaves(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				 this.vista.modeloEvidencia = resultado.valor;
			 }
			 else
				 this.vista.mostrarMensajeError("Error","Ocurrió un error al consultar el registro. " + resultado.mensajeError, resultado.codigoError);
		 },this.vista.llaves);
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
					this.vista.mostrarMensajeError("Error",resultado.mensajeError);
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
				this.vista.mostrarMensajeError("Error","No se guardo la información. " + resultado.mensajeError);	
					
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
					this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 },{},true);
	 }

	consultarProcesoRevisadoPorLlaves()
	{
		this.vista.mostrarIndicador();	
		 this._repositorio.consultarPorLlaves(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				 this.vista.modeloProceso = resultado.valor;
				if(this.vista._procesoSeleccionado.estatusRevisionId == EstatusRevision.OBSERVACIONES)
				 	this.consultarObservaciones();
			 }
			 else
				 this.vista.mostrarMensajeError("Error","Ocurrió un error al consultar el registro. " + resultado.mensajeError, resultado.codigoError);
		 },this.vista.llavesProceso);
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
					this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 },{procesoRevisadoId: this.vista._procesoSeleccionado.id});
	 }

	actualizarEstatusValidacionProceso(estatusValidacionId)
	{
		this.vista.mostrarIndicador();	
		 this._repositorio.actualizarEstatusValidacionProceso(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				 //this.vista.modeloProceso = resultado.valor;
				 this.vista.cerrarModal("procesoModal");
				 this.vista.mostrarMensaje("Notificación","Guardado");
				 this.vista.modeloProceso = resultado.valor;
			 }
			 else
				 this.vista.mostrarMensajeError(resultado.mensajeError, resultado.codigoError);
		 },this.vista._procesoSeleccionado.id, estatusValidacionId);
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
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
			
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
					this.vista.mostrarMensajeError("Error",resultado.mensajeError);
				
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
	
	 
}