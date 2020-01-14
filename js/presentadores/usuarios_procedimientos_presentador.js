class UsuariosProcedimientosPresentador extends CatalogoPresentador
{ 
	 constructor(vista)
	 {
		 super(vista,new UsuariosProcedimientosRepositorio());
	 }
	 

	 
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
			this.vista.mostrarMensaje("Error",resultado.mensajeError);
		
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
			this.vista.mostrarMensaje("Error",resultado.mensajeError);
		
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
			this.vista.mostrarMensaje("Error",resultado.mensajeError);
		
	 }
	 
	 consultarProcedimientos()	
	 {
		 var repositorio = new ProcedimientosRepositorio(this);		
		 repositorio.consultarPorEmpresaSede(this,this.consultarProcedimientosResultado,this.vista.modelo.empresaId,this.vista.modelo.sedeIdProcedimiento);
	 }
	 
	 consultarProcedimientosResultado(resultado)
	 {
		if(resultado.mensajeError=="")
		{
			this.vista.procedimientos = resultado.valor;				
		}
		else
			this.vista.mostrarMensaje("Error",resultado.mensajeError);
		
	 }
	 
}