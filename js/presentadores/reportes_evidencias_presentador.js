class ReportesEvidenciasPresentador extends CatalogoPresentador
{
	 constructor(vista)
	 {
		 super(vista,new EvidenciasRepositorio());
	 }
	 
	 consultar()
	 {
		if(this.vista.criteriosSeleccion.supervisorCoordinadorId!=null)
		{
			 this.vista.mostrarIndicador();
			 this._repositorio.consultarAnosMeses(this,function(resultado)
			 {
				 this.vista.ocultarIndicador();	
				if(resultado.mensajeError=="")
				{
					this.validarMeses(resultado.valor);
					this.vista.datos = resultado.valor;
				}
				else
					this.vista.mostrarMensajeError("Error",resultado.mensajeError);
			 },{supervisorCoordinadorId: this.vista.criteriosSeleccion.supervisorCoordinadorId});
		}
		else
			vista.mostrarMensajeAdvertencia("Advertencia","Seleccione un coordinador o supervisor")
	 }
	 
	 validarMeses(anoMes)
	 {
		 var validos = [];
		 var fecha = new Date();
		 var mes = fecha.getMonth() + 1;
		 var ano = fecha.getFullYear();
		 var dia = fecha.getDate();
		 for (var i = 0; i < anoMes.length; i++) 
		 {
			 var elemento = anoMes[i];
			 if(elemento.mes == mes && elemento.ano == ano)
			 {	
				 if(dia>=28)
				 {
					 elemento.mensajeError = "";
				 }
				 else
					 elemento.mensajeError = "Disponible apartir del dia 28";
			 }
			 else
				 elemento.mensajeError = "";
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
			
		 },null,false);
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
				this.vista.mostrarMensaje("Error",resultado.mensajeError);
		 },this.vista.criteriosSeleccion.empresaId,false);
	 }
	 
	 
	 consultarUsuariosCriterio()	
	 {
		 var repositorio = new UsuariosRepositorio(this);		
		 repositorio.consultarPorEmpresaSede(this, function(resultado)
		 {
			if(resultado.mensajeError=="")
			{
				var coordinadoresSupervisores = this.getCoordinadoresSupervisores(resultado.valor);
				this.vista.usuariosCriterio = coordinadoresSupervisores;				
			}
			else
				this.vista.mostrarMensaje("Error",resultado.mensajeError);
		 },this.vista.criteriosSeleccion.empresaId,this.vista.criteriosSeleccion.sedeId,false);
	 }
	 
	 getCoordinadoresSupervisores(usuarios)
	 {
		var coordinadoresSupervisores = [];
		for(var i=0; i< usuarios.length; i++)
		{
			var usuario = usuarios[i];
			if(usuario.tipoUsuarioId == TipoUsuario.COORDINADOR || usuario.tipoUsuarioId == TipoUsuario.SUPERVISOR)
				coordinadoresSupervisores.push(usuario);
		}
		
		return coordinadoresSupervisores;
	 }
	 
	 
}