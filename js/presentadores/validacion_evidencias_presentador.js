class ValidacionEvidenciasPresentador extends CatalogoPresentador
{
	 constructor(vista)
	 {
		 super(vista,new AuditoriasRepositorio());
	 }
	 
//	 consultar()
//	 {
//		 this.vista.mostrarIndicador();
//		 var repositorio = new AreasRepositorio(this);		
//		 repositorio.consultar(this,this.consultarResultado,this.vista.criteriosSeleccion);
//	 }
//	 
//	 consultarResultado(resultado)
//	 {
//		this.vista.ocultarIndicador();	
//		if(resultado.mensajeError=="")
//			this.vista.datos = resultado.valor;
//		else
//			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
//		
//	 }
	 
	 consultarEmpresas()	
	 {
		 var repositorio = new EmpresasRepositorio(this);		
		 repositorio.consultar(this,this.consultarEmpresasResultado,null);
	 }
	 
	 consultarEmpresasResultado(resultado)
	 {
		if(resultado.mensajeError=="")
		{
			this.vista.empresas = resultado.valor;
			this.vista.cambiarEmpresa();
			
		}
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
	 }
	 
	 consultarSedes()	
	 {
		 //this.vista.mostrarIndicador();
		 var repositorio = new SedesRepositorio(this);	
		 repositorio.consultarPorEmpresa(this,this.consultarSedesResultado,this.vista.modelo.empresaId);
	 }
	 
	 consultarSedesResultado(resultado)
	 {
		//this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
		{
			this.vista.sedes = resultado.valor;
//			this.vista.cambiarSede();
		}
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
	 }
	 
	 
	 consultarEmpresasCriterio()	
	 {
		 var repositorio = new EmpresasRepositorio(this);		
		 repositorio.consultar(this,this.consultarEmpresasCriterioResultado,null,true);
	 }
	 
	 consultarEmpresasCriterioResultado(resultado)
	 {
		if(resultado.mensajeError=="")
		{
			this.vista.empresasCriterio = resultado.valor;
			this.vista.cambiarEmpresaCriterio();
		}
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
	 }
	
	 
	 
	 consultarSedesCriterio()	
	 {
		 var repositorio = new SedesRepositorio(this);		
		 repositorio.consultarPorEmpresa(this,this.consultarSedesCriterioResultado,this.vista.criteriosSeleccion.empresaId,true);
	 }
	 
	 consultarSedesCriterioResultado(resultado)
	 {
		if(resultado.mensajeError=="")
		{
			this.vista.sedesCriterio = resultado.valor;				
		}
		else
			this.vista.mostrarMensaje("Error",resultado.mensajeError);
	 }

	consultarAuditorias()
	{
		vista.mostrarIndicador();
		var repositorio = new AuditoriasRepositorio();
		repositorio.consultarActivasPorUsuario(this, function(resultado){
			vista.ocultarIndicador();
			if(resultado.mensajeError=="")
			{
				vista.auditorias = resultado.valor;
			}
			else
				vista.mostrarMensajeError("Error",resultado.mensajeError)
		});
	}	
	
	consultarRecomendaciones()
	{
		vista.mostrarIndicador();
		//var repositorio = new AuditoriasRepositorio();
		this._repositorio.consultarRecomendaciones(this, function(resultado){
			vista.ocultarIndicador();
			if(resultado.mensajeError=="")
			{
				vista.recomendaciones = resultado.valor;
			}
			else
				vista.mostrarMensajeError("Error",resultado.mensajeError)
		},this.vista.criteriosSeleccionRecomendaciones);
	}
	
	consultarAvancesRecomendacion()	
	 {
		 this.vista.mostrarIndicador();	
		 this._repositorio.consultarAvancesRecomendacion(this, function(resultado)
		 {
			this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
			{
				this.vista.avances = resultado.valor;				
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 },this.vista.llavesRecomendacion);
	 }

	consultarArchivos()	
	 {
		 this.vista.mostrarIndicador();	
		 this._repositorio.consultarArchivosAvance(this, function(resultado)
		 {
			this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
			{
				this.vista.archivos = resultado.valor;				
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 },{id:this.vista.modeloAvance.id});
	 }

	consultarArchivosAvance()	
	 {
		 this._repositorio.consultarArchivosAvance(this, function(resultado)
		 {
			if(resultado.mensajeError=="")
			{
				this.vista.archivos = resultado.valor;				
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 },this.vista.llavesAvance);
	 }

	consultarEstatusValidacion()	
	 {
		var repositorio = new EstatusValidacionRepositorio();
		 repositorio.consultar(this, function(resultado)
		 {
			if(resultado.mensajeError=="")
			{
				this.vista.estatusValidacion = resultado.valor;				
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 });
	 }

	consultarEstatusValidacionCriterio()	
	 {
		var repositorio = new EstatusValidacionRepositorio();
		 repositorio.consultar(this, function(resultado)
		 {
			if(resultado.mensajeError=="")
			{
				this.vista.estatusValidacionCriterio = resultado.valor;				
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 },null,true);
	 }

	consultarEstatusValidacionAlta()	
	 {
		var repositorio = new EstatusValidacionRepositorio();
		 repositorio.consultar(this, function(resultado)
		 {
			if(resultado.mensajeError=="")
			{
				this.vista.estatusValidacionAlta = resultado.valor;				
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 });
	 }


	 
	actualizarAvance()
	 {
		 this.vista.guardando = true;
		 this.vista.mostrarIndicador();	
		 this._repositorio.actualizarAvance(this,function(resultado)
		 {
			 this.vista.ocultarIndicador();	
			 this.vista.guardando = false;
			 if(resultado.mensajeError=="")
			 {	
				this.subirArchivos(this.vista.modeloAvance.id);
			 }
			 else
				this.vista.mostrarMensajeError("Error","Ocurrió un error al actualizar el registro. " + resultado.mensajeError, resultado.codigoError);		
				
			
		 },this.vista.llavesRecomendacion.id, this.vista.modeloAvance);
	 }

	subirArchivos(avanceId)
	{
		 this.vista.guardando = true;
		 this.vista.mostrarIndicador();	
		 this._repositorio.subirArchivosAvance(this, function(resultado)
		 {
			this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
			{	
				this.vista.mostrarMensaje("Notificación","La información se actualizó correctamente.");
				this.vista.salirModalArchivos();
				if(this.vista.modoAvance==Modo.CAMBIO)	
					this.consultarAvancePorLlaves(avanceId);
				else
					this.consultarAvancesRecomendacion();
					
				this.consultarRecomendacionPorLlaves();
			}
			else
				this.vista.mostrarMensajeError("Error","Ocurrió un error al subir los archivos. " + resultado.mensajeError, resultado.codigoError);	
			
			 setTimeout(function()
			{
				 this.vista.guardando = false;
	         }, 2000);
			
				
		 }, function(event)
		{
			var porcentaje = event.loaded / event.total * 100;
			this.vista.progresoArchivos =  parseInt(porcentaje);
			
		},this.vista.llavesRecomendacion.id,avanceId, this.vista.archivos);
	}
	 

	insertarAvance()
	 {
		 this.vista.guardando = true;
		 this.vista.mostrarIndicador();	
		 this._repositorio.insertarAvance(this, function(resultado)
		 {
			this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
			{	
				this.vista.modeloAvance.id = resultado.valor;
				this.subirArchivos(resultado.valor);
			
			}
			else
				this.vista.mostrarMensajeError("Error","Ocurrió un error al guardar el registro. " + resultado.mensajeError, resultado.codigoError);	
			
			
				
		 },this.vista.llavesRecomendacion.id,this.vista.modeloAvance);	
	 }
	 
	 consultarAvancePorLlaves(avanceId)
	 {
		 this.vista.mostrarIndicador();	
		 this._repositorio.consultarAvancePorLlaves(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				 this.vista.modeloAvance = resultado.valor;
			 }
			 else
				 this.vista.mostrarMensajeError("Error","Ocurrió un error al consultar el registro. " + resultado.mensajeError, resultado.codigoError);
		 },{id: avanceId});
	 }
	 
	 
	eliminarAvance()
	 {
		 this.vista.mostrarIndicador();	
		 this._repositorio.eliminarAvance(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 this.vista.cerrarConfirmacionEliminar();
			 if(resultado.mensajeError=="")
			 {
				
				 this.vista.mostrarMensaje("Notificación","El avance se eliminó correctamente.");
				 this.consultarAvancesRecomendacion();
				 this.consultarRecomendacionPorLlaves();
			 }
			 else
			 {
				 if(resultado.codigoError==1451)
					 this.vista.mostrarMensajeAdvertencia("Error","No se puede eliminar el registro porque esta relacionado con otro catálogo. ") ;
				 else
					 this.vista.mostrarMensajeError("Error","Ocurrió un error al eliminar el registro. " + resultado.mensajeError, resultado.codigoError);
			 }
		 },this.vista.llavesAvance);
	 }
	 
	 
	consultarRecomendacionValidacionPorLlaves()
	{
		this.vista.mostrarIndicador();	
		 this._repositorio.consultarRecomendacionPorLlaves(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				 this.vista.modeloRecomendacionValidacion = resultado.valor;
			 }
			 else
				 this.vista.mostrarMensajeError("Error","Ocurrió un error al consultar el registro. " + resultado.mensajeError, resultado.codigoError);
		 },this.vista.llavesRecomendacion);
	}
	
	consultarRecomendacionPorLlaves()
	{
		this.vista.mostrarIndicador();	
		 this._repositorio.consultarRecomendacionPorLlaves(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				 this.vista.modeloRecomendacion = resultado.valor;
			 }
			 else
				 this.vista.mostrarMensajeError("Error","Ocurrió un error al consultar el registro. " + resultado.mensajeError, resultado.codigoError);
		 },this.vista.llavesRecomendacion);
	}
	 
	validarRecomendacion()
	 {
		 this.vista.guardando = true;
		 this.vista.mostrarIndicador();	
		var modelo = this.vista._modeloRecomendacionValidacion;	
		 this._repositorio.validarRecomendacion(this,function(resultado)
		 {
			this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
			{	
				/*this.vista.mostrarMensaje("Notificación","Guardado. Id: " + resultado.valor);
				this.vista.salirFormularioValidacion();
				this.vista.salirFormularioArchivos();
				this.vista.salirFormularioAvances();
				this.vista.consultarRecomendaciones();*/
				this.vista.actualizarRecomendacion(modelo);
				if(!this.vista.mostrarSiguienteEvidenciaValidacion())
				{
					this.vista.mostrarMensaje("","Validación terminada");
					this.vista.salirFormularioArchivos();
					
				}
				this.vista.mostrarMensaje(modelo.titulo, "Validada correctamente");
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
	 
	consultarComentariosRecomendacion()
	 {
		// this.vista.mostrarIndicador();
		 var repositorio = new RecomendacionesComentariosRepositorio(this);		
		 repositorio.consultar(this, function(resultado)
		 {
			//this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
				this.vista.comentariosRecomendacion = resultado.valor;
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
			
		 },{recomendacionId: this.vista._recomendacionSeleccionada.id});
	 }
	
	 
	enviarComentarioRecomendacion()
	 {
		 if(this.vista.modeloCometarioRecomendacion.comentario!="" && this.vista.modeloCometarioRecomendacion.comentario!=undefined)
		 {
			 var repositorio = new RecomendacionesComentariosRepositorio(this);		
			 repositorio.insertar(this,	 function(resultado)
			 {
				this.vista.ocultarIndicador();	
				if(resultado.mensajeError=="")
					this.vista.consultarComentariosRecomendacion();
				else
					this.vista.mostrarMensajeError("Error",resultado.mensajeError);
				
			 },this.vista.modeloCometarioRecomendacion);
		}
	 }
	
	 guardarAvance()
	{
		if(this.vista.modeloAvance.cumplimiento==100 && this.vista.archivos.length==0)
			this.vista.mostrarMensajeAdvertencia("Advertencia","Es necesario adjuntar archivos de evidencias");
		else
		{
			if(this.vista.modoAvance==Modo.CAMBIO)	
				this.actualizarAvance();
			else
				this.insertarAvance();
		}
	}

	consultarResponsablesRecomendacion()
	{
		var repositorio = new UsuariosRepositorio();
		 repositorio.consultarUsuariosCorportarivoPorEmpresaSIVAH(this,function(resultado)
		 {		
			 //vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				 this.vista.responsablesRecomendacion = resultado.valor;
			 }
			 else
			 {
				 this.vista.mostrarMensajeError("Error", resultado.mensajeError, resultado.codigoError);
			 }
		 },vista._registroSeleccionado.empresaId);
	}
	 
	actualizarRecomendacion()
	{
		this.vista.guardando = true;
		 this.vista.mostrarIndicador();	
		 this._repositorio.actualizarRecomendacion(this,function(resultado)
		 {
			 this.vista.ocultarIndicador();	
			this.vista.guardando = false;
			 if(resultado.mensajeError=="")
			 {	
				this.vista.mostrarMensaje("Notificación","La información se actualizó correctamente.");
				this.vista.salirModalRecomendacion();
				this.consultarRecomendacionPorLlaves();
			 }
			 else
				this.vista.mostrarMensajeError("Error","Ocurrió un error al actualizar el registro. " + resultado.mensajeError, resultado.codigoError);		
			
		 },this.vista.modeloRecomendacion);
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

	consultarAvanceTerminado()	
	 {
		 this._repositorio.consultarAvanceTerminadoRecomendacion(this, function(resultado)
		 {
			if(resultado.mensajeError=="")
			{
				this.vista.modeloAvance = resultado.valor;
				//this.consultarArchivos(resultado.valor.id)
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
			
		 },this.vista._recomendacionSeleccionada.id);
	 }
	 
}