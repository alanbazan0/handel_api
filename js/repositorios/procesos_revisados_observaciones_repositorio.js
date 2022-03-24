class ProcesosRevisadosObservacionesRepositorio extends Repositorio
{	
	
	constructor()
	{
		super("php/repositorios/ProcesosRevisadosObservaciones.php");
	}
	
	actualizarEstatusValidacion(contexto,funcion,procesoRevisadoId, observacionId,estatusValidacionId, comentario)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "actualizarEstatusValidacion",procesoRevisadoId:procesoRevisadoId, observacionId: observacionId, estatusValidacionId:estatusValidacionId, "comentario": comentario },
	       success: function( data, textStatus, jQxhr )
	       {
	           funcion.call(contexto,data);
	       },
	       error: function( jqXhr, textStatus, errorThrown )
	       {
	      	 funcion.call(contexto,{ mensajeError : errorThrown + "." +jqXhr.responseText});
	       },
	       fail: function( jqXhr, textStatus, errorThrown )
	       {
	      	 funcion.call(contexto,{ mensajeError : errorThrown+ "." +jqXhr.responseText});
	       }
	   });
	}
	

}