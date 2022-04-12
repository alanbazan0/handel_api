class GraficaRevisionProcesosEmpresaPresentador extends CatalogoPresentador
{
	 constructor(vista)
	 {
		 super(vista,new UsuariosProcesosRepositorio());
	 }
	 
	 consultar()
	 {
		 this.vista.mostrarIndicador();
		 this._repositorio.consultarAvanceEmpresas(this,function(resultado)
		 {
			 this.vista.ocultarIndicador();	
				if(resultado.mensajeError=="")
					this.vista.porcentajesEmpresas = resultado.valor;
				else
					this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 },this.vista.criteriosSeleccion);
	 }

	 
	 consultarEmpresasCriterio()	
	 {
		 var repositorio = new EmpresasRepositorio(this);		
		 repositorio.consultar(this, function(resultado)
		 {
			if(resultado.mensajeError=="")
			{
				this.vista.empresasCriterio = resultado.valor;
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
			
		 },{estatus:1},true);
	 }
	 
	
	 
	 
	 consultarAnos()
	 {
		var repositorio = new ProcesosRevisadosRepositorio();
		 this.vista.mostrarIndicador();
		 repositorio.consultarAnos(this,function(resultado)
		 {
			 this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
				this.vista.anos = resultado.valor;
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 });
	 }
	 
	
	 
}