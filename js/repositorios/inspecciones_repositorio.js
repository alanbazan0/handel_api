class InspeccionesRepositorio extends Repositorio
{	
	constructor()
	{
		super("php/repositorios/Inspecciones.php");
	}
	
	consultarPorEmpresaSede(contexto,funcion, empresaId, sedeId)
	{		
		var url = HANDEL_API + "/" + this.servicio;
			$.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarPorEmpresaSede", empresaId : empresaId, sedeId: sedeId},
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
	
	consultarInspeccionesEmpresa(contexto,funcion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
			$.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarInspeccionesEmpresa"},
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
	
	consultarInspeccionesMes(contexto,funcion)
	{		
		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarInspeccionesMes"},
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
	
	consultarInspeccionesSede(contexto,funcion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarInspeccionesSede"},
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
	
	consultarInspeccionesArea(contexto,funcion,criteriosSeleccion)
	{		
		var criteriosSeleccionString = JSON.stringify(criteriosSeleccion);
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarInspeccionesArea", criteriosSeleccion : criteriosSeleccionString},
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