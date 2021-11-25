class ProcesosRepositorio extends Repositorio
{	
	constructor()
	{
		super("php/repositorios/Procesos.php");
	}
	
	consultarPorEmpresaSede(contexto,funcion, empresaId, sedeId)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarPorEmpresaSede", empresaId: empresaId, sedeId : sedeId},
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
	
	copiarProcedimientos(contexto,funcion, empresaIdOrigen, sedeIdOrigen, procedimientos, empresaIdDestino, sedeIdDestino)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "copiarProcedimientos", empresaIdOrigen: empresaIdOrigen, sedeIdOrigen : sedeIdOrigen, procedimientos:  JSON.stringify(procedimientos), empresaIdDestino: empresaIdDestino, sedeIdDestino: sedeIdDestino},
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