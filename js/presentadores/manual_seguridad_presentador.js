class ManualSeguridadPresentador extends CatalogoPresentador
{ 
	 constructor(vista)
	 {
		 super(vista,new UsuariosProcesosRepositorio());
	 }
	 

	 
	 consultarEmpresas()	
	 {
		 var repositorio = new EmpresasRepositorio(this);		
		 repositorio.consultar(this,this.consultarEmpresasResultado,{estatus:1});
	 }
	 
	 consultarEmpresasResultado(resultado)
	 {
		if(resultado.mensajeError=="")
		{
			this.vista.empresas = resultado.valor;
			this.vista.cambiarEmpresa();
			
		}
		else
			this.vista.mostrarMensaje("Error",resultado.mensajeError);
		
	 }
	 
	 consultarEmpresasCriterio()	
	 {
		 var repositorio = new EmpresasRepositorio(this);		
		 repositorio.consultar(this,this.consultarEmpresasCriterioResultado,{estatus:1},true);
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
			//this.vista.cambiarSedeCriterio();
		}
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
	 }

	 
	 consultarSedes()	
	 {
		 var repositorio = new SedesRepositorio(this);		
		 repositorio.consultarPorEmpresa(this,this.consultarSedesResultado,this.vista.modelo.empresaId);
	 }
	 
	 consultarSedesResultado(resultado)
	 {
		if(resultado.mensajeError=="")
		{
			this.vista.sedes = resultado.valor;		
			this.vista.cambiarSede();
			this.vista.cambiarSedeProcedimiento();
		}
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
	 }
	 
	 consultarUsuarios()	
	 {
		 var repositorio = new UsuariosRepositorio(this);		
		 repositorio.consultarPorEmpresaSede(this,this.consultarUsuariosResultado,this.vista.modelo.empresaId,this.vista.modelo.sedeIdUsuario);
	 }
	 
	 consultarUsuariosResultado(resultado)
	 {
		if(resultado.mensajeError=="")
		{
			this.vista.usuarios = resultado.valor;				
		}
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
	 }
	 
	 consultarProcesos()	
	 {
		 var repositorio = new ProcesosRepositorio(this);		
		 repositorio.consultarPorEmpresaSede(this,this.consultarProcedimientosResultado,this.vista.modelo.empresaId,this.vista.modelo.sedeIdProcedimiento);
	 }
	 
	 consultarProcedimientosResultado(resultado)
	 {
		if(resultado.mensajeError=="")
		{
			this.vista.procedimientos = resultado.valor;				
		}
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
	 }
	 
	 consultar()
	 {
		this.vista.mostrarIndicador();	
	   	this._repositorio.consultarManualSeguridad(this,function(resultado){
			this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
			{
				this.vista.datos = resultado.valor;				
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
			
		},this.vista.criteriosSeleccion);
		
		var formatosRepositorio = new UsuariosFormatosRepositorio(this);
		formatosRepositorio.consultarManualSeguridad(this,function(resultado){
			this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
			{
				this.vista.datosFormatos = resultado.valor;				
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
			
		},this.vista.criteriosSeleccion);
	 }
	 
	 reportarSinCambios(usuarioProceso)
	 {
		 this.vista.mostrarIndicador();	
		 var repositorio = new ProcesosRevisadosRepositorio();
		 repositorio.reportarSinCambios(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 this.vista.cerrarConfirmacionEliminar();
			 if(resultado.mensajeError=="")
			 {
				 this.vista.mostrarMensaje("Notificación","Guardado.");
			 }
			 else
			 {
				 this.vista.mostrarMensajeError("Error",resultado.mensajeError, resultado.codigoError);
			 }
		 },usuarioProceso.id);
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
	 
	 guardarObservaciones(usuarioProceso, observaciones)
	{
		this.vista.mostrarIndicador();	
		 var repositorio = new ProcesosRevisadosRepositorio();
		 repositorio.guardarObservaciones(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 //this.vista.cerrarConfirmacionEliminar();
			 if(resultado.mensajeError=="")
			 {
				 this.vista.cerrarModal("observacionesModal");
				 this.vista.mostrarMensaje("Notificación","Guardado.");
				 this.vista.eliminarProceso(usuarioProceso);
			 }
			 else
			 {
				 this.vista.mostrarMensajeError("Error",resultado.mensajeError, resultado.codigoError);
			 }
		 },usuarioProceso.id,observaciones);
	}

	confirmarEliminarObservacion(observacion, tr, indice)
	{
		var _this = this;
		swal({
	            title: "\u00bfEst\u00E1 seguro de eliminar?",
	            text: "Se eliminar\u00e1 este registro !!",
	            type: "warning",
	            showCancelButton: true,
	            confirmButtonColor: "#DD6B55",
	            confirmButtonText: "Si, eliminar!!",
	            cancelButtonText: "No",
	            closeOnConfirm: false,
	            closeOnCancel: true,
	            showLoaderOnConfirm: true,
	        },
	        function(isConfirm)
	        {
	            if (isConfirm) 
	            {
					_this._observaciones.slice(indice,1);
					
					swal.close();
					tr.fadeOut();
	            	 setTimeout(function()
					{
						
						tr.remove();
						
	            		 //_this.presentador.eliminar();
	 	            }, 1000);
	            }
	        });
	}
	 
	 
	 
}