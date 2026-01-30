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
				 this.vista.mostrarMensajeAdvertencia("Error","No se puede eliminar el registro esta relacionado con otro catálogo. Más información...", resultado) ;
			 else
				 this.vista.mostrarMensajeError("Error","Ocurrió un error al eliminar el registro. " + resultado.mensajeError);
		 }
	 }
	 consultarEmpresas()	
	 {
		 //this.vista.mostrarIndicador();
		 var repositorio = new EmpresasRepositorio(this);		
		 var criteriosSeleccion = {nombre:""};
		 repositorio.consultar(this,this.consultarEmpresasResultado,{estatus:1});
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
	 
	 consultarPerfiles()	
	 {
		 var repositorio = new PerfilesRepositorio(this);		
		 repositorio.consultar(this, function(resultado)
		 {
			if(resultado.mensajeError=="")
			{
				this.vista.perfiles = resultado.valor;
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
		if(this.vista.modelo.empresaId!="")
		{
		 var repositorio = new UsuariosRepositorio(this);	
		 repositorio.consultarSupervisoresPorEmpresa(this,this.consultarSupervisoresPorEmpresaResultado,this.vista.modelo.empresaId,this.vista.modelo.id);
		}
		else
		{
			var supervisores = [];
			this.vista.supervisores1 = supervisores;
			this.vista.supervisores2 = supervisores;
			this.vista.supervisores3 = supervisores;
		}
	 }
	 
	 consultarUsuariosReemplaza()	
	 {
		if(this.vista.modelo.empresaId!="")
		{
		 	var repositorio = new UsuariosRepositorio(this);	
		 	repositorio.consultarSimple(this,function(resultado){
				 
				if(resultado.mensajeError=="")
				{
					this.vista.reemplazaUsuario = resultado.valor;
				}
				else
					this.vista.mostrarMensajeError("Error",resultado.mensajeError);
						 
			 },{empresaId: this.vista.modelo.empresaId, estatus: 1});
		}
		else
		{
			var usuarios = [];
			this.vista.remplazaUsuarioSelect = usuarios;
		}
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
	 
	

	 consultarEmpresasCriterio()	
	 {
		 var repositorio = new EmpresasRepositorio(this);		
		 repositorio.consultar(this,this.consultarEmpresasCriterioResultado,{estatus:1},true);
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
	 
	 consultarTiposSocioComercial()	
	 {
		 var repositorio = new TiposSocioComercialRepositorio(this);		
		 repositorio.consultar(this, function(resultado)
		 {
			if(resultado.mensajeError=="")
			{
				this.vista.tiposSocioComercial = resultado.valor;
				//this.vista.cambiarEmpresaCriterio();
			}
			else
				this.vista.mostrarMensajeError("Error",resultado.mensajeError);
			
		 },{estatus:1},true);
	 }
	
	 insertarResultado(resultado)
	 {
		this.vista.ocultarIndicador();	
		if(resultado.mensajeError=="")
		{	
			this.vista.mostrarMensaje("Notificación","La información se guardó correctamente. Id: " + resultado.valor);
			this.vista.salirFormulario();
		}
		else
			this.vista.mostrarMensajeError("Error","Ocurrió un error al guardar el registro. " + resultado.mensajeError, resultado.codigoError);	
		
		 setTimeout(function()
		{
			 this.vista.guardando = false;
         }, 2000);
		
			
	 }	
	 
	 actualizar()
	 {
		 var reemplazarUsuario = false;
		 if(this.vista.modelo.reemplazaUsuarioId!="" && this.vista.modelo.reemplazaUsuarioId!=undefined)
		 	reemplazarUsuario= this.vista.modelo.reemplazaUsuarioId == this.vista.reemplazaUsuarioId ? false : true;
		 this.vista.guardando = true;
		 this.vista.mostrarIndicador();	
		 var _this = this;
		 this._repositorio.actualizar(this, function(resultado)
		 {
			 this.vista.ocultarIndicador();	
			 if(resultado.mensajeError=="")
			 {	
				 var notificacion = "La información se actualizó correctamente.";
				if(reemplazarUsuario)
					this.reemplazarUsuario(notificacion, this.vista.modelo.reemplazaUsuarioId, this.vista.modelo.id);
				else
				{
					this.vista.mostrarMensaje("Notificación",notificacion);
					this.vista.salirFormulario();
					//this.consultar();
				}
			 }
			 else
				this.vista.mostrarMensajeError("Error","Ocurrió un error al actualizar el registro. " + resultado.mensajeError, resultado.codigoError);		
			 setTimeout(function()
			{
				 this.vista.guardando = false;
	         }, 2000);
		 },this.vista.modelo, this.vista.logo);
	 }
	 
	 insertar()
	 {
		 var reemplazarUsuario = false;
		 if(this.vista.modelo.reemplazaUsuarioId!="" && this.vista.modelo.reemplazaUsuarioId!=undefined)
		 	reemplazarUsuario= this.vista.modelo.reemplazaUsuarioId == this.vista.reemplazaUsuarioId ? false : true;
		 this.vista.guardando = true;
		 this.vista.mostrarIndicador();	
		 this._repositorio.insertar(this,function(resultado)
		 {
			this.vista.ocultarIndicador();	
			if(resultado.mensajeError=="")
			{	
				var notificacion = "La información se guardó correctamente. Id: " + resultado.valor.id + ", tokens restantes: " + resultado.valor.tokens;
				if(reemplazarUsuario)
					this.reemplazarUsuario(notificacion, this.vista.modelo.reemplazaUsuarioId, resultado.valor);
				else
				{
					this.vista.mostrarMensaje("Notificación",notificacion);
					this.vista.salirFormulario();
				}
			}
			else
				this.vista.mostrarMensajeError("Error","Ocurrió un error al guardar el registro. " + resultado.mensajeError, resultado.codigoError);	
			
			 setTimeout(function()
			{
				 this.vista.guardando = false;
	         }, 2000);
			
				
		 },this.vista.modelo, this.vista.logoAlta);	
	 }
	 
	 
	 
	 reemplazarUsuario(notificacion, origenUsuarioId, destinoUsuarioId)
	 {
		 var _this = this;
		swal({
            title: "Advertencia",
            text: "Se detectó un reemplazo de usuario, se reemplazara el usuario " + $("#reemplazaUsuarioSelect option:selected").text() + " por " +  this.vista.modelo.nombre + " " + this.vista.modelo.apellido ,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Si, Continuar",
            cancelButtonText: "Cancelar",
            closeOnConfirm: true,
            closeOnCancel: true,
            showLoaderOnConfirm: true,
        },
        function(isConfirm)
        {
            if (isConfirm) 
            {
            	 setTimeout(function(){
					 this.vista.mostrarIndicador();	
            		 _this._repositorio.reemplazarUsuario(this, function(resultado)
					 {
						 this.vista.ocultarIndicador();	
						if(resultado.mensajeError=="")
						{
							
							_this.mostrarResumenReemplazo(resultado.valor,this.vista.modelo.nombre+ " " + this.vista.modelo.apellido);
							
							
							
							_this.vista.mostrarMensaje("Notificación","Se reemplazó el usuario correctamente");
							_this.vista.salirFormulario();
							//_this.consultar();
						}	
						 
					 }, origenUsuarioId, destinoUsuarioId);
 	            }, 500);
            }
            else
            {
				_this.vista.mostrarMensaje("Notificación",notificacion);
				_this.vista.salirFormulario();
				_this.consultar();
              	_this.vista.mostrarMensajeAdvertencia("","Se canceló el reemplazo de usuario");
            }
			
        });
	 }
	 
	 mostrarResumenReemplazo(resumen, usuarioNombre)
	 {
 		var html = "<striong>Se generó la siguiente información para el usuario " + usuarioNombre+"</strong>";
 		
 		html +="<div style=' height: 250px;'>";
		html +="<div class='div-scroll' style='height: 240px;overflow:scroll;'>";
		if(resumen.procedimientos.length > 0)
		{
	 		html+="</br><strong>Evidencias:</strong><br>";
			for(var i = 0; i < resumen.procedimientos.length; i++)
			{
				var procedimiento = resumen.procedimientos[i];
				html+="<br>"+ procedimiento.nombre;
			}
		}
		
		if(resumen.procedimientos.length > 0)
		{
			html+="</br></br><strong>Procesos:</strong><br>";
			for(var i = 0; i < resumen.procesos.length; i++)
			{
				var proceso = resumen.procesos[i];
				html+="<br>"+ proceso.nombre;
			}
		}
		
		if(resumen.tareas.length > 0)
		{
			html+="</br></br><strong>Tareas:</strong><br>";
			for(var i = 0; i < resumen.tareas.length; i++)
			{
				var proceso = resumen.tareas[i];
				html+="<br>"+ proceso.titulo;
			}
		}
		
		if(resumen.hallazgos.length > 0)
		{
			html+="</br></br><strong>Hallazgos:</strong><br>";
			for(var i = 0; i < resumen.hallazgos.length; i++)
			{
				var proceso = resumen.hallazgos[i];
				html+="<br>"+ proceso.titulo;
			}
		}
		
		if(resumen.supervisados.length > 0)
		{
			html+="</br></br><strong>Personal:</strong><br>";
			for(var i = 0; i < resumen.supervisados.length; i++)
			{
				var proceso = resumen.supervisados[i];
				html+="<br>"+ proceso.nombreCompleto;
			}
		}
		
		html+="</div>";
		html+="</div>";
		
		swal({
            title: "",
            text: html,
			html: true,
            type: "success",
            showCancelButton: false,
            confirmButtonColor: "#3c8dbc",
            confirmButtonText: "Aceptar",
			cancelButtonColor: "#DD6B55",
            closeOnConfirm: true,
            closeOnCancel: true,
        },
        function(isConfirm)
        {
            
        });
		 
	 }
	 
	
	 
}