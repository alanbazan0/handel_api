class UsuariosProcedimientosRepositorio extends Repositorio
{	
	constructor()
	{
		super("php/repositorios/UsuariosProcedimientos.php");
	}
	
	
	consultarProcedimientosPendientesMesActual(contexto,funcion, usuarioId)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarProcedimientosPendientesMesActual", usuarioId: usuarioId},
	       success: function( data, textStatus, jQxhr )
	       {
	           funcion.call(contexto,data);
	       },
	       error: function( jqXhr, textStatus, errorThrown )
	       {
	      	 funcion.call(contexto,{ mensajeError : textStatus});
	       },
	       fail: function( jqXhr, textStatus, errorThrown )
	       {
	      	 funcion.call(contexto,{ mensajeError : textStatus});
	       }
	   });
	}
	
	
	consultarProcedimientosCumplidosMesActual(contexto,funcion, usuarioId)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarProcedimientosCumplidosMesActual", usuarioId: usuarioId},
	       success: function( data, textStatus, jQxhr )
	       {
	           funcion.call(contexto,data);
	       },
	       error: function( jqXhr, textStatus, errorThrown )
	       {
	      	 funcion.call(contexto,{ mensajeError : textStatus});
	       },
	       fail: function( jqXhr, textStatus, errorThrown )
	       {
	      	 funcion.call(contexto,{ mensajeError : textStatus});
	       }
	   });
	}
	

}