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
	      	 funcion.call(contexto,{ mensajeError : errorThrown});
	       },
	       fail: function( jqXhr, textStatus, errorThrown )
	       {
	      	 funcion.call(contexto,{ mensajeError : errorThrown});
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
	      	 funcion.call(contexto,{ mensajeError : errorThrown});
	       },
	       fail: function( jqXhr, textStatus, errorThrown )
	       {
	      	 funcion.call(contexto,{ mensajeError : errorThrown});
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
	      	 funcion.call(contexto,{ mensajeError : errorThrown});
	       },
	       fail: function( jqXhr, textStatus, errorThrown )
	       {
	      	 funcion.call(contexto,{ mensajeError : errorThrown});
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
	      	 funcion.call(contexto,{ mensajeError : errorThrown});
	       },
	       fail: function( jqXhr, textStatus, errorThrown )
	       {
	      	 funcion.call(contexto,{ mensajeError : errorThrown});
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
				funcion.call(contexto, { mensajeError: errorThrown });
			},
			fail: function(jqXhr, textStatus, errorThrown) {
				funcion.call(contexto, { mensajeError: errorThrown });
			}
		});
	}
	
	
	
	

}