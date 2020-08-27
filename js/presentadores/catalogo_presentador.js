class CatalogoPresentador
{
	 constructor(vista,repositorio)
	 {
		 this._repositorio = repositorio;
		 this.vista = vista;
	 }
	 
	 consultar()
	 {
		 this.vista.mostrarIndicador();
		 //var repositorio = new EmpresasRepositorio(this);		
		 this._repositorio.consultar(this,this.consultarResultado,this.vista.criteriosSeleccion);
	 }
	 
	 consultarResultado(resultado)
	 {
		this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
			this.vista.datos = resultado.valor;
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
	 }
	 
	 insertar()
	 {
		 this.vista.guardando = true;
		 this.vista.mostrarIndicador();	
		 this._repositorio.insertar(this,this.insertarResultado,this.vista.modelo, this.vista.logoAlta);	
	 }
	 
	 insertarResultado(resultado)
	 {
		this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
		{	
			this.vista.mostrarMensaje("Notificación","La información se guardó correctamente. Id: " + resultado.valor);
			this.vista.salirFormulario();
			this.consultar();
		}
		else
			this.vista.mostrarMensajeError("Error","Ocurrió un error al guardar el registro. " + resultado.mensajeError);	
		
		 setTimeout(function()
		{
			 this.vista.guardando = false;
         }, 2000);
		
			
	 }	

	 actualizar()
	 {
		 this.vista.guardando = true;
		 this.vista.mostrarIndicador();	
		 this._repositorio.actualizar(this,this.actualizarResultado,this.vista.modelo, this.vista.logo);
	 }
	 
	 actualizarResultado(resultado)
	 {
		 this.vista.ocultarIndicador();	
		 if(resultado.mensajeError=="")
		 {	
			this.vista.mostrarMensaje("Notificación","La información se actualizó correctamente.");
			this.vista.salirFormulario();
			this.consultar();
		 }
		 else
			this.vista.mostrarMensajeError("Error","Ocurrió un error al actualizar el registro. " + resultado.mensajeError);		
		 setTimeout(function()
		{
			 this.vista.guardando = false;
         }, 2000);
	 }
	 
	 consultarNumeroMensajesNoLeidos()
	 {
		// this.vista.mostrarIndicador();
		 var repositorio = new MensajesRepositorio(this);		
		 repositorio.consultarNumeroMensajesNoLeidos(this,this.consultarNumeroMensajesNoLeidosResultado);
	 }
	
	 
	 consultarNumeroMensajesNoLeidosResultado(resultado)
	 {
		//this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
			this.vista.numeroMensajesNoLeidos = resultado.valor;
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
	 }
	   
	 consultarPorLlaves()
	 {
		 this.vista.mostrarIndicador();	
		 this._repositorio.consultarPorLlaves(this,this.consultarPorLlavesResultado,this.vista.llaves);
	 }
	 
	 consultarPorLlavesResultado(resultado)
	 {		
		 this.vista.ocultarIndicador();	
		 if(resultado.mensajeError=="")
		 {
			 this.vista.modelo = resultado.valor;
		 }
		 else
			 this.vista.mostrarMensajeError("Error","Ocurrió un error al consultar el registro. " + resultado.mensajeError);
	 }
	 
	 eliminar()
	 {
		 this.vista.mostrarIndicador();	
		 this._repositorio.eliminar(this,this.eliminarResultado,this.vista.llaves);
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
				 this.vista.mostrarMensajeAdvertencia("Error","No se puede eliminar el registro porque esta relacionado con otro catálogo. ") ;
			 else
				 this.vista.mostrarMensajeError("Error","Ocurrió un error al eliminar el registro. " + resultado.mensajeError);
		 }
	 }
	 
	 enviarMensaje()
	 {
		 this.vista.guardando = true;
		 this.vista.mostrarIndicador();
		 var repositorio = new MensajesRepositorio(this);		
		 repositorio.insertar(this,this.enviarMensajeResultado,this.vista.modeloMensaje);
	 }
	
	 
	 enviarMensajeResultado(resultado)
	 {
		this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
		{
			this.vista.mensajeEnviado();
		}
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
		 setTimeout(function()
		{
			 this.vista.guardando = false;
         }, 2000);
		
	 }
	 
	 consultarEmpresasMensaje()	
	 {
		 var repositorio = new EmpresasRepositorio(this);		
		 repositorio.consultar(this,this.consultarEmpresasMensajeResultado,null,true);
	 }
	 
	 consultarEmpresasMensajeResultado(resultado)
	 {
		if(resultado.mensajeError=="")
		{
			this.vista.empresasMensaje = resultado.valor;
			this.vista.cambiarEmpresaMensaje();
		}
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
	 }
	 
	 consultarDepartamentosMensaje()	
	 {
//		 var repositorio = new DepartamentosRepositorio(this);		
//		 repositorio.consultar(this,function(resultado)
//		 {
//			if(resultado.mensajeError=="")
//			{
//				this.vista.departamentosMensaje = resultado.valor;
//				this.vista.cambiarDepartamentoMensaje();
//			}
//			else
//				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
//			
//		 },null,true);
		 var repositorio = new DepartamentosRepositorio(this);		
		 repositorio.consultarPorEmpresaSede(this, function(resultado)
		 {
			if(resultado.mensajeError=="")
			{
				this.vista.departamentosMensaje = resultado.valor;		
				this.vista.cambiarDepartamentoMensaje();
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 }
		,{empresaId:this.vista.modeloMensaje.empresaId,sedeId:this.vista.modeloMensaje.sedeId},true);
	 }
	 
	 
	 
	 consultarSedesMensaje()	
	 {
		 var repositorio = new SedesRepositorio(this);		
		 repositorio.consultarPorEmpresa(this,this.consultarSedesMensajeResultado,this.vista.criteriosSeleccionMensaje.empresaId,true);
	 }
	 
	 consultarSedesMensajeResultado(resultado)
	 {
		if(resultado.mensajeError=="")
		{
			this.vista.sedesMensaje = resultado.valor;		
			this.vista.cambiarSedeMensaje();
		}
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
	 }
	 
//	 consultarAreasMensaje()	
//	 {
//		 var repositorio = new AreasRepositorio(this);	
//		 repositorio.consultarPorEmpresaSede(this,this.consultarAreasMensajeResultado,this.vista.criteriosSeleccionMensaje.empresaId,this.vista.criteriosSeleccionMensaje.sedeId,true);
//	 }
//	 
//	 consultarAreasMensajeResultado(resultado)
//	 {
//		if(resultado.mensajeError=="")
//		{
//			this.vista.areasMensaje = resultado.valor;
//			this.vista.cambiarAreaMensaje();
//		}
//		else
//			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
//	 }
	 
	 consultarUsuariosMensaje()	
	 {
		 var repositorio = new UsuariosRepositorio(this);	
		 repositorio.consultarPorEmpresaSedeDepartamento(this,this.consultarUsuariosMensajeResultado,this.vista.criteriosSeleccionMensaje.empresaId,this.vista.criteriosSeleccionMensaje.sedeId,this.vista.criteriosSeleccionMensaje.departamentoId,true);
	 }
	 
	 consultarUsuariosMensajeResultado(resultado)
	 {
		if(resultado.mensajeError=="")
		{
			this.vista.usuariosMensaje = resultado.valor;
		}
		else
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
	 }
	 
	 consultarTareasPendientes()
	 {
		// this.vista.mostrarIndicador();
		 var repositorio = new MinutasRepositorio(this);		
		 repositorio.consultarTareasPendientes(this,function(resultado){
			 if(resultado.mensajeError=="")
			{
				this.vista.tareasPendientes = resultado.valor;
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 });
	 }
	 
	 
}