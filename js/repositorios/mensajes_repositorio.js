class MensajesRepositorio extends Repositorio
{	
	constructor()
	{
		super("php/repositorios/Mensajes.php");
	}
	
	
	consultarNumeroMensajesNoLeidos(contexto,funcion, usuarioId)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
	        url: url,
	        type: 'POST',
	        data: {accion : "consultarNumeroMensajesNoLeidos",usuarioId: usuarioId},
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
	
	marcarComoLeido(contexto,funcion, mensajeId)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
	        url: url,
	        type: 'POST',
	        data: {accion : "marcarComoLeido",mensajeId: mensajeId},
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
	
	consultarMensajesUsuario(contexto,funcion, usuarioId)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
	        url: url,
	        type: 'POST',
	        data: {accion : "consultarMensajesUsuario",usuarioId: usuarioId},
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