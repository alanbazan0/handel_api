class UsuariosPresentador extends CatalogoPresentador
{
	 constructor(vista)
	 {
		 super(vista,new UsuariosRepositorio());
	 }
	 
	 eliminarResultado(resultado)
	 {		
		 this.vista.ocultarIndicador();	
		 this.vista.cerrarConfirmacionEliminar();
		 if(resultado.mensajeError=="")
		 {
			
			 this.vista.mostrarMensaje("Notificación","El registro se eliminó correctamente.");
			 this.consultar();
		 }
		 else
		 {
			 if(resultado.codigoError==1451)
				 this.vista.mostrarMensajeError("Error","No se puede eliminar el registro porque esta relacionado con otro catálogo. ") ;
			 else
				 this.vista.mostrarMensajeError("Error","Ocurrió un error al eliminar el registro. " + resultado.mensajeError);
		 }
	 }
	 consultarEmpresas()	
	 {
		 //this.vista.mostrarIndicador();
		 var repositorio = new EmpresasRepositorio(this);		
		 var criteriosSeleccion = {nombre:""};
		 repositorio.consultar(this,this.consultarEmpresasResultado,null);
	 }
	 
	 consultarDepartamentos()	
	 {
		 var repositorio = new DepartamentosRepositorio(this);		
		 repositorio.consultar(this, function(resultado)
		 {
			if(resultado.mensajeError=="")
			{
				this.vista.departamentos = resultado.valor;
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
			
		 },null);
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
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
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
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
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
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
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
			this.vista.cambiarSede();
		}
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
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
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
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
			this.vista.cambiarEmpresaCriterio();
		}
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
	 }
	 
	 reenviarCorreo()
	 {
		 this.vista.mostrarIndicador();
		 var repositorio = new UsuariosRepositorio(this);		
		 repositorio.reenviarCorreo(this,function(resultado)
		 {
			 this.vista.ocultarIndicador();	
				if(resultado.mensajeError=="")
					this.vista.mostrarMensaje("","El correo fue reenviado.");
				else
					this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 }, this.vista.llaves);
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
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
	 }
	 
	 consultarHistorialAcceso()	
	 {
		 var repositorio = new HistorialAccesoRepositorio(this);		
		 repositorio.consultar(this, function(resultado)
		 {
			if(resultado.mensajeError=="")
			{
				this.vista.historialAcceso = resultado.valor;				
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 },{nombreUsuario:this.vista._registroSeleccionado.nombreUsuario});
	 }
	 
	
	
	 
}