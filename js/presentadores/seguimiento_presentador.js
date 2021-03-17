class SeguimientoPresentador extends CatalogoPresentador
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
	
	consultarRecomendacionesPendientesUsuario()
	{
		vista.mostrarIndicador();
		//var repositorio = new AuditoriasRepositorio();
		this._repositorio.consultarRecomendacionesPendientesUsuario(this, function(resultado){
			vista.ocultarIndicador();
			if(resultado.mensajeError=="")
			{
				vista.recomendaciones = resultado.valor;
			}
			else
				vista.mostrarMensajeError("Error",resultado.mensajeError)
		},this.vista.llaves);
	}
	
	consultarAvancesRecomendacion()	
	 {
		 this._repositorio.consultarAvancesRecomendacion(this, function(resultado)
		 {
			if(resultado.mensajeError=="")
			{
				this.vista.avances = resultado.valor;				
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 },this.vista.llavesRecomendacion);
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
	 
	actualizarAvance()
	 {
		 this.vista.guardando = true;
		 this.vista.mostrarIndicador();	
		 this._repositorio.actualizarAvance(this,function(resultado)
		 {
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {	
				this.vista.mostrarMensaje("Notificación","La información se actualizó correctamente.");
				this.vista.salirModalArchivos();
				this.consultarAvancesRecomendacion();
			 }
			 else
				this.vista.mostrarMensajeError("Error","Ocurrió un error al actualizar el registro. " + resultado.mensajeError, resultado.codigoError);		
			 setTimeout(function()
			{
				 this.vista.guardando = false;
	         }, 2000);
		 },this.vista.llavesRecomendacion.id, this.vista.modeloAvance);
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
				this.vista.mostrarMensaje("Notificación","La información se guardó correctamente. Id: " + resultado.valor);
				this.vista.salirModalArchivos();
				this.consultarAvancesRecomendacion();
			}
			else
				this.vista.mostrarMensajeError("Error","Ocurrió un error al guardar el registro. " + resultado.mensajeError, resultado.codigoError);	
			
			 setTimeout(function()
			{
				 this.vista.guardando = false;
	         }, 2000);
			
				
		 },this.vista.llavesRecomendacion.id,this.vista.modeloAvance);	
	 }
	 
	 consultarAvancePorLlaves()
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
		 },this.vista.llavesAvance);
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
	 
	 
	
	 
	
	 
}