class EmpresasPresentador extends CatalogoPresentador
{
	 constructor(vista)
	 {
		super(vista,new EmpresasRepositorio());
	 }
	 
//	 consultar()
//	 {
//		 this.vista.mostrarIndicador();
//		 var repositorio = new EmpresasRepositorio(this);		
//		 repositorio.consultar(this,this.consultarResultado,this.vista.criteriosSeleccion);
//	 }
//	 
//	 consultarResultado(resultado)
//	 {
//		this.vista.ocultarIndicador();	
//		if(resultado.mensajeError=="")
//			this.vista.datos = resultado.valor;
//		else
//			this.vista.mostrarMensaje("Error",resultado.mensajeError);
//		
//	 }
	 
	 consultarEmpresas()	
	 {
		 //this.vista.mostrarIndicador();
		 var repositorio = new EmpresasRepositorio(this);		
		 var criteriosSeleccion = {nombre:""};
		 repositorio.consultar(this,this.consultarEmpresasResultado,criteriosSeleccion);
	 }
	 
	 consultarEmpresasResultado(resultado)
	 {
		//this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
		{
			this.vista.empresas = resultado.valor;
			this.vista.cambiarEmpresa();
			
		}
		else
			this.vista.mostrarMensaje("Error",resultado.mensajeError);
		
	 }
	 
	 consultarTiposUsuario()	
	 {
		 //this.vista.mostrarIndicador();
		 var repositorio = new TiposUsuarioRepositorio(this);				 
		 repositorio.consultar(this,this.consultarTiposUsuarioResultado);
	 }
	 
	 consultarTiposUsuarioResultado(resultado)
	 {
		//this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
		{
			this.vista.tiposUsuario = resultado.valor;			
		}
		else
			this.vista.mostrarMensaje("Error",resultado.mensajeError);
		
	 }
	 
	 consultarAreas()	
	 {
		// this.vista.mostrarIndicador();
		 var repositorio = new AreasRepositorio(this);	
		 repositorio.consultarPorEmpresa(this,this.consultarAreasResultado,this.vista.modelo.empresaId);
	 }
	 
	 consultarAreasResultado(resultado)
	 {
		//this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
			this.vista.areas = resultado.valor;
		else
			this.vista.mostrarMensaje("Error",resultado.mensajeError);
	 }
	 
	 consultarPuestos()	
	 {
		// this.vista.mostrarIndicador();
		 var repositorio = new PuestosRepositorio(this);	
		 repositorio.consultarPorEmpresa(this,this.consultarPuestosResultado,this.vista.modelo.empresaId);
	 }
	 
	 consultarPuestosResultado(resultado)
	 {
		//this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
			this.vista.puestos = resultado.valor;
		else
			this.vista.mostrarMensaje("Error",resultado.mensajeError);
		
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
			this.vista.sedes = resultado.valor;
		else
			this.vista.mostrarMensaje("Error",resultado.mensajeError);
		
	 }
	 
	 consultarSupervisores()	
	 {
		// this.vista.mostrarIndicador();
		 var repositorio = new UsuariosRepositorio(this);	
		 repositorio.consultarSupervisoresPorEmpresa(this,this.consultarSupervisoresPorEmpresaResultado,this.vista.modelo.empresaId,this.vista.modelo.id);
	 }
	 
	 consultarSupervisoresPorEmpresaResultado(resultado)
	 {
		//this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
		{
			this.vista.supervisores1 = resultado.valor;
			this.vista.supervisores2 = resultado.valor;
			this.vista.supervisores3 = resultado.valor;
		}
		else
			this.vista.mostrarMensaje("Error",resultado.mensajeError);
		
	 }
	 
	
	 
	
	
	 
	 consultarTiposEmpresa()	
	 {
		 var repositorio = new TiposEmpresaRepositorio(this);		
		 repositorio.consultar(this,this.consultarTiposEmpresaResultado,null);
	 }
	 
	 consultarTiposEmpresaResultado(resultado)
	 {
		if(resultado.mensajeError=="")
		{
			this.vista.tiposEmpresa = resultado.valor;		
		}
		else
			this.vista.mostrarMensaje("Error",resultado.mensajeError);
	 }
	 
	 consultarPaises()	
	 {
		 var repositorio = new PaisesRepositorio(this);		
		 repositorio.consultar(this,this.consultarPaisesResultado,null);
	 }
	 
	 consultarPaisesResultado(resultado)
	 {
		if(resultado.mensajeError=="")
		{
			this.vista.paises = resultado.valor;
			this.vista.cambiarPais();			
		}
		else
			this.vista.mostrarMensaje("Error",resultado.mensajeError);
		
	 }
	 
	 consultarEstados()	
	 {
		 var repositorio = new EstadosRepositorio(this);		
		 repositorio.consultarPorPais(this,this.consultarEstadosResultado,this.vista.modelo.paisId);
	 }
	 
	 consultarEstadosResultado(resultado)
	 {
		if(resultado.mensajeError=="")
		{
			this.vista.estados = resultado.valor;
			this.vista.cambiarEstado();			
		}
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);		
	 }
	 
	 consultarCiudades()	
	 {
		 var repositorio = new CiudadesRepositorio(this);		
		 repositorio.consultarPorPaisEstado(this,this.consultarCiudadesResultado,this.vista.modelo.paisId,this.vista.modelo.estadoId);
	 }
	 
	 consultarCiudadesResultado(resultado)
	 {
		if(resultado.mensajeError=="")
		{
			this.vista.ciudades = resultado.valor;
					
		}
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);		
	 }
	 
	 consultarCorporativos()	
	 {
		 var repositorio = new EmpresasRepositorio(this);		
		 repositorio.consultarCorporativos(this,this.consultarCorporativosResultado,this.vista.modelo.id);
	 }
	 
	 consultarCorporativosResultado(resultado)
	 {
		if(resultado.mensajeError=="")
		{
			this.vista.corporativos = resultado.valor;		
		}
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
	 }
	 
	 consultarAdministradores()	
	 {
		 var repositorio = new UsuariosRepositorio(this);		
		 repositorio.consultarAdministradores(this,this.consultarAdministradoresResultado);
	 }
	 
	 consultarAdministradoresResultado(resultado)
	 {
		if(resultado.mensajeError=="")
		{
			this.vista.administradores = resultado.valor;		
		}
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
	 }
	 
}