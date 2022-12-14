class ReportePersonalCapacitacionesPresentador extends CatalogoPresentador
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
			this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		
	 }
	 
	 consultarDepartamentos()	
	 {
		 var repositorio = new DepartamentosRepositorio(this);		
		 repositorio.consultar(this, function(resultado)
		 {
			if(resultado.mensajeError=="")
			{
				this.vista.departamentos = resultado.valor;
				this.vista.cambiarDepartamento();
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
			
		 },null,true);
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
	 
	 
	 consultarSedesCriterio()	
	 {
		 var repositorio = new SedesRepositorio(this);		
		 repositorio.consultarPorEmpresa(this, function(resultado)
		 {
			if(resultado.mensajeError=="")
			{
				this.vista.sedesCriterio = resultado.valor;			
				this.vista.cambiarSedeCriterio();
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 }
		,this.vista.criteriosSeleccion.empresaId,true);
	 }
	 
	 consultarDepartamentosCriterio()	
	 {
		 var repositorio = new DepartamentosRepositorio(this);		
		 repositorio.consultar(this, function(resultado)
		 {
			if(resultado.mensajeError=="")
			{
				this.vista.departamentosCriterio = resultado.valor;
				this.vista.cambiarDepartamentoCriterio();
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
			
		 },{empresaId: this.vista.criteriosSeleccion.empresaId, sedeId: this.vista.criteriosSeleccion.sedeId },true);
	 }

 	consultarUsuariosCriterio()	
	 {
		 var repositorio = new UsuariosRepositorio(this);		
		 repositorio.consultarSimple(this, function(resultado)
		 {
			if(resultado.mensajeError=="")
			{
				this.vista.usuariosCriterio = resultado.valor;
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
			
		 },{empresaId: this.vista.criteriosSeleccion.empresaId, sedeId: this.vista.criteriosSeleccion.sedeId, departamentoId: this.vista.criteriosSeleccion.departamentoId },true);
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
	 
	

	
	 
	 consultarCursosCriterio()	
	 {
		 var repositorio = new CapacitacionesRepositorio(this);		
		 repositorio.consultarCriterio(this, function(resultado)
		 {
			if(resultado.mensajeError=="")
			{
				this.vista.cursosCriterio = resultado.valor;			
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 },null, true);
	 }
	 
	 
	 consultar()	
	 {
		this.vista.mostrarIndicador();	
		 var repositorio = new CapacitacionesRepositorio(this);		
		 repositorio.consultarCapacitacionesTomadas(this, function(resultado)
		 {
			this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
			{
				this.vista.datos = resultado.valor;			
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 }
		,this.vista.criteriosSeleccion);
	 }
	 
	 eliminarCursoUsuario()	
	 {
		this.vista.mostrarIndicador();	
		 var repositorio = new CapacitacionesRepositorio(this);		
		 repositorio.eliminarCursoUsuario(this, function(resultado)
		 {
			this.vista.cerrarConfirmacionEliminar();
			this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
			{
				 this.vista.mostrarMensaje("Notificación","Los resultados se eliminaron correctamente.");
				 this.consultar();
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 }
		,this.vista.llaves);
	 }
	 
	 consultarLeccionesCapacitacionUsuario()	
	 {
		 this.vista.mostrarIndicador();	
		 var repositorio = new CapacitacionesRepositorio(this);
		 repositorio.consultarUsuarioCursoLecciones(this, function(resultado)
		 {
			this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
			{
				this.vista.lecciones = resultado.valor;				
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 },this.vista.registroSeleccionado.usuarioId, vista.registroSeleccionado.cursoId);
	 }
	 
	eliminarUsuarioCapacitacionLeccion()
	 {
		 this.vista.mostrarIndicador();	
		 var repositorio = new CapacitacionesRepositorio(this);
		 repositorio.eliminarUsuarioCapacitacionLeccion(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 this.vista.cerrarConfirmacionEliminar();
			 if(resultado.mensajeError=="")
			 {
				
				 this.vista.mostrarMensaje("Notificación","La lección se eliminó correctamente.");
				 this.consultarLeccionesCapacitacionUsuario();
				 this.consultarUsuarioCapacitacion();
			 }
			 else
			 {
				 if(resultado.codigoError==1451)
					 this.vista.mostrarMensajeAdvertencia("Error","No se puede eliminar el registro porque esta relacionado con otro catálogo. ") ;
				 else
					 this.vista.mostrarMensajeError("Error","Ocurrió un error al eliminar el registro. " + resultado.mensajeError, resultado.codigoError);
			 }
		 },this.vista.leccionSeleccionada.usuarioId, vista.leccionSeleccionada.cursoId, this.vista.leccionSeleccionada.leccionId);
	 }
	 
	 
	consultarUsuarioCapacitacion()
	{
		this.vista.mostrarIndicador();	
		var repositorio = new CapacitacionesRepositorio(this);
		 repositorio.consultarUsuarioCapacitacion(this,function(resultado)
		 {		
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {
				 this.vista.modeloCapacitacion = resultado.valor;
			 }
			 else
				 this.vista.mostrarMensajeError("Error","Ocurrió un error al consultar el registro. " + resultado.mensajeError, resultado.codigoError);
		 },this.vista.registroSeleccionado.usuarioId, vista.registroSeleccionado.cursoId);
	}
	 
}