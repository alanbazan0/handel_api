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

	consultar()
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
	 
	
	 
}