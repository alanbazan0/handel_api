class UsuariosFormatosRepositorio extends Repositorio
{	
	constructor()
	{
		super("php/repositorios/UsuariosFormatos.php");
	}
	
	
	/*consultarProcedimientosPendientesMesActual(contexto,funcion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarProcedimientosPendientesMesActual"},
	       success: function( data, textStatus, jQxhr )
	       {
	           funcion.call(contexto,data);
	       },
	       error: function( jqXhr, textStatus, errorThrown )
	       {
	      	 funcion.call(contexto,{ mensajeError : errorThrown});
	       },
	       fail: function( jqXhr, textStatus, errorThrown )
	       {
	      	 funcion.call(contexto,{ mensajeError : errorThrown});
	       }
	   });
	}
	*/
	consultarProcesosPendientes(contexto,funcion, criteriosSeleccion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarProcesosPendientes",  criteriosSeleccion: JSON.stringify(criteriosSeleccion)},
	       success: function( data, textStatus, jQxhr )
	       {
	           funcion.call(contexto,data);
	       },
	       error: function( jqXhr, textStatus, errorThrown )
	       {
	      	 funcion.call(contexto, { mensajeError: Repositorio.getError(jqXhr,textStatus) });
	       },
	       fail: function( jqXhr, textStatus, errorThrown )
	       {
	      	 funcion.call(contexto, { mensajeError: Repositorio.getError(jqXhr,textStatus) });
	       }
	   });
	}
	
	consultarAvanceUsuarios(contexto,funcion, criteriosSeleccion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarAvanceUsuarios",  criteriosSeleccion: JSON.stringify(criteriosSeleccion)},
	       success: function( data, textStatus, jQxhr )
	       {
	           funcion.call(contexto,data);
	       },
	       error: function( jqXhr, textStatus, errorThrown )
	       {
	      	 funcion.call(contexto, { mensajeError: Repositorio.getError(jqXhr,textStatus) });
	       },
	       fail: function( jqXhr, textStatus, errorThrown )
	       {
	      	 funcion.call(contexto, { mensajeError: Repositorio.getError(jqXhr,textStatus) });
	       }
	   });
	}
	
	consultarAvanceDepartamentos(contexto,funcion, criteriosSeleccion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarAvanceDepartamentos",  criteriosSeleccion: JSON.stringify(criteriosSeleccion)},
	       success: function( data, textStatus, jQxhr )
	       {
	           funcion.call(contexto,data);
	       },
	       error: function( jqXhr, textStatus, errorThrown )
	       {
	      	 funcion.call(contexto, { mensajeError: Repositorio.getError(jqXhr,textStatus) });
	       },
	       fail: function( jqXhr, textStatus, errorThrown )
	       {
	      	 funcion.call(contexto, { mensajeError: Repositorio.getError(jqXhr,textStatus) });
	       }
	   });
	}
	
	
	consultarAvanceEmpresas(contexto,funcion, criteriosSeleccion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarAvanceEmpresas",  criteriosSeleccion: JSON.stringify(criteriosSeleccion)},
	       success: function( data, textStatus, jQxhr )
	       {
	           funcion.call(contexto,data);
	       },
	       error: function( jqXhr, textStatus, errorThrown )
	       {
	      	 funcion.call(contexto, { mensajeError: Repositorio.getError(jqXhr,textStatus) });
	       },
	       fail: function( jqXhr, textStatus, errorThrown )
	       {
	      	 funcion.call(contexto, { mensajeError: Repositorio.getError(jqXhr,textStatus) });
	       }
	   });
	}
	
	
	consultarManualSeguridad(contexto,funcion, criteriosSeleccion)
	{
		var url = HANDEL_API + "/" + this.servicio;
		$.ajax({
			url: url,
			type: 'POST',
			data: { accion: "consultarManualSeguridad", criteriosSeleccion: JSON.stringify(criteriosSeleccion) },
			success: function(data, textStatus, jQxhr) {
				funcion.call(contexto, data);
			},
			error: function(jqXhr, textStatus, errorThrown) {
				funcion.call(contexto, { mensajeError: Repositorio.getError(jqXhr,textStatus) });
			},
			fail: function(jqXhr, textStatus, errorThrown) {
				funcion.call(contexto, { mensajeError: Repositorio.getError(jqXhr,textStatus) });
			}
		});
	}
	
	consultarManualSeguridadAgrupados(contexto,funcion, criteriosSeleccion)
	{
		var url = HANDEL_API + "/" + this.servicio;
		$.ajax({
			url: url,
			type: 'POST',
			data: { accion: "consultarManualSeguridadAgrupados", criteriosSeleccion: JSON.stringify(criteriosSeleccion) },
			success: function(data, textStatus, jQxhr) {
				funcion.call(contexto, data);
			},
			error: function(jqXhr, textStatus, errorThrown) {
				funcion.call(contexto, { mensajeError: Repositorio.getError(jqXhr,textStatus) });
			},
			fail: function(jqXhr, textStatus, errorThrown) {
				funcion.call(contexto, { mensajeError: Repositorio.getError(jqXhr,textStatus) });
			}
		});
	}
	
	
	
	

}