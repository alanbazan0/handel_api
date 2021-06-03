class ReporteEvidenciasAnualUsuarioPresentador extends CatalogoPresentador
{
	 constructor(vista)
	 {
		 super(vista,new EvidenciasRepositorio());
	 }
	 
	 consultar()
	 {
		 this.vista.mostrarIndicador();
		 this._repositorio.consultarEvidenciasAnualUsuario(this,function(resultado)
		 {
			 this.vista.ocultarIndicador();	
				if(resultado.mensajeError=="")
					this.vista.datos = this.transformarDatos(resultado.valor);
				else
					this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 },this.vista.criteriosSeleccion);
	 }
	 
	 transformarDatos(datos)
	 {
		 var transformados = [];
		 var todas = [];
		 for(var i= 0; i < datos.length; i++)
		 {
			 var mes = datos[i];
			 todas = todas.concat(mes.cumplidas);
			 todas = todas.concat(mes.pendientes);
		 }
		 
		 var procedimientos = ArrayUtils.groupBy("procedimientoId,procedimientoNombre",todas);
		 for(var i= 0; i < procedimientos.length; i++)
		 {
			 var procedimiento = procedimientos[i];
			 this.calcularMeses(procedimiento,datos);
		 }
		 
		 return procedimientos;
	 }
	 
	 calcularMeses(procedimiento, meses)
	 {
		 for(var i=0; i < meses.length;i++)
		 {
			 var mes = meses[i];
			 var campoMes = "mes" +mes.mes; 
			 var cumplida = ArrayUtils.searchWithValues("procedimientoId",[procedimiento.procedimientoId],mes.cumplidas);
			 if(cumplida!=null)
			 {
				 var campoCumplida = "evidencia" +mes.mes;
				 procedimiento[campoCumplida] = cumplida;
				 if(cumplida.justificacionId!=null)
					 procedimiento[campoMes] = "J";
				 else
					 procedimiento[campoMes] = "S";
			 }
			 else
			 {
				 var pendiente = ArrayUtils.searchWithValues("procedimientoId",[procedimiento.procedimientoId],mes.pendientes);
				 if(pendiente!=null)
					 procedimiento[campoMes] = "N";
				 else
					 procedimiento[campoMes] = "-";
					 
			 }
		 }
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
			
		 },{estatus:1},false);
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
				this.vista.cambiarSedeCriterio();
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 }
		,this.vista.criteriosSeleccion.empresaId,false);
	 }
	 
	 consultarDepartamentosCriterio()	
	 {
		 var repositorio = new DepartamentosRepositorio(this);		
		 repositorio.consultarPorEmpresaSede(this, function(resultado)
		 {
			if(resultado.mensajeError=="")
			{
				this.vista.departamentosCriterio = resultado.valor;			
				this.vista.cambiarDepartamentoCriterio();
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 }
		,{empresaId:this.vista.criteriosSeleccion.empresaId,sedeId:this.vista.criteriosSeleccion.sedeId},false);
	 }
	 
	 
	 consultarUsuariosCriterio()	
	 {
		 var repositorio = new UsuariosRepositorio(this);		
		 repositorio.consultarPorEmpresaSedeDepartamento(this, function(resultado)
		 {
			if(resultado.mensajeError=="")
			{
				this.vista.usuariosCriterio = resultado.valor;			
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
		 }
		,this.vista.criteriosSeleccion.empresaId,this.vista.criteriosSeleccion.sedeId,this.vista.criteriosSeleccion.departamentoId,false);
	 }
	 
	
	 
	
	 
}