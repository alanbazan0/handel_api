class GraficaEvidenciasSedePresentador extends CatalogoPresentador
{
	 constructor(vista)
	 {
		 super(vista,new EvidenciasRepositorio());
	 }
	 
	 consultar()
	 {
		 this.vista.mostrarIndicador();
		 this._repositorio.consultarPorcentajesSedes(this,function(resultado)
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
				this.vista.cambiarEmpresaCriterio();
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
			
		 },null,true);
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
			}
			else
				this.vista.mostrarMensaje("Error",resultado.mensajeError);
		 }
		,this.vista.criteriosSeleccion.empresaId,true);
	 }
	 
	
	 
	
	 
}