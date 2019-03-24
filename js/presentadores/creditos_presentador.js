class CreditosPresentador extends CatalogoPresentador
{
	 constructor(vista)
	 {
		 super(vista,new CreditosRepositorio());
	 }
	 
//	 consultar()
//	 {
//		 this.vista.mostrarIndicador();
//		 var repositorio = new PuestosRepositorio(this);		
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
	 
//	 insertar()
//	 {
//		 this.vista.mostrarIndicador();	
//		 var repositorio = new PuestosRepositorio(this);			 
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
//		 var repositorio = new PuestosRepositorio(this);		
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
//		 var repositorio = new PuestosRepositorio(this);		
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
//		 var repositorio = new PuestosRepositorio(this);		
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
//				 this.vista.mostrarMensaje("Error","No se puede eliminar el registro porque esta relacionado con otro catálogo. ") ;
//			 else
//				 this.vista.mostrarMensaje("Error","Ocurrió un error al eliminar el registro. " + resultado.mensajeError);
//		 }
//	 }
	 
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
		}
		else
			this.vista.mostrarMensaje("Error",resultado.mensajeError);
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
	 
}