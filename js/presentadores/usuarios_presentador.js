class UsuariosPresentador extends CatalogoPresentador
{
	 constructor(vista)
	 {
		 super(vista,new UsuariosRepositorio());
	 }
	 
//	 consultar()
//	 {
//		 this.vista.mostrarIndicador();
//		 var repositorio = new UsuariosRepositorio(this);		
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
		 repositorio.consultar(this,this.consultarEmpresasResultado,null);
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
		
		 repositorio.consultarPorEmpresaSede(this,this.consultarAreasResultado,this.vista.modelo.empresaId,this.vista.modelo.sedeId);
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
		 repositorio.consultarPorEmpresaSede(this,this.consultarPuestosResultado,this.vista.modelo.empresaId,this.vista.modelo.sedeId);
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
		{
			this.vista.sedes = resultado.valor;
			this.vista.cambiarSede();
		}
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
	 
	
	 
//	
//	 insertar()
//	 {
//		 this.vista.mostrarIndicador();	
//		 var repositorio = new UsuariosRepositorio(this);			 
//		 repositorio.insertar(this,this.insertarResultado,this.vista.modelo);	
//	 }
//	 
//	 insertarResultado(resultado)
//	 {
//		this.vista.ocultarIndicador();	
//		if(resultado.mensajeError=="")
//		{	
//			this.vista.mostrarMensaje("Notificación","La información se guardó correctamente. Id: " + resultado.valor);
//			this.vista.salirFormulario();
//			this.consultar();
//		}
//		else
//			this.vista.mostrarMensaje("Error","Ocurrió un error al guardar el registro. " + resultado.mensajeError);			
//			
//	 }	
//	 
//	 actualizar()
//	 {
//		 this.vista.mostrarIndicador();	
//		 var repositorio = new UsuariosRepositorio(this);		
//		 repositorio.actualizar(this,this.actualizarResultado,this.vista.modelo);
//	 }
//	 
//	 actualizarResultado(resultado)
//	 {
//		 this.vista.ocultarIndicador();	
//		 if(resultado.mensajeError=="")
//		 {	
//			this.vista.mostrarMensaje("Notificación","La información se actualizó correctamente.");
//			this.vista.salirFormulario();
//			this.consultar();
//		 }
//		 else
//			this.vista.mostrarMensaje("Error","Ocurrió un error al actualizar el registro. " + resultado.mensajeError);			
//	 }
//	   
//	 consultarPorLlaves()
//	 {
//		 this.vista.mostrarIndicador();	
//		 var repositorio = new UsuariosRepositorio(this);		
//		 repositorio.consultarPorLlaves(this,this.consultarPorLlavesResultado,this.vista.llaves);
//	 }
//	 
//	 consultarPorLlavesResultado(resultado)
//	 {		
//		 this.vista.ocultarIndicador();	
//		 if(resultado.mensajeError=="")
//		 {
//			 this.vista.modelo = resultado.valor;
//		 }
//		 else
//			 this.vista.mostrarMensaje("Error","Ocurrió un error al consultar el registro. " + resultado.mensajeError);
//	 }
//	 
//	 eliminar()
//	 {
//		 this.vista.mostrarIndicador();	
//		 var repositorio = new UsuariosRepositorio(this);		
//		 repositorio.eliminar(this,this.eliminarResultado,this.vista.llaves);
//	 }
//	 
//	 eliminarResultado(resultado)
//	 {		
//		 this.vista.ocultarIndicador();	
//		 if(resultado.mensajeError=="")
//		 {
//			 this.vista.mostrarMensaje("Notificación","El registro se eliminó correctamente.");
//			 this.consultar();
//		 }
//		 else
//		 {
//			 if(resultado.codigoError==1451)
//				 this.vista.mostrarMensajeError("Error","No se puede eliminar el registro porque esta relacionado con otro catálogo. ") ;
//			 else
//				 this.vista.mostrarMensajeError("Error","Ocurrió un error al eliminar el registro. " + resultado.mensajeError);
//		 }
//	 }
	 

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
		}
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
	 }
	 
	
	 
}