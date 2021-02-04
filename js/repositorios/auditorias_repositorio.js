class AuditoriasRepositorio extends Repositorio
{	
	constructor()
	{
		super("/php/repositorios/Auditorias.php");
	}
	
	consultarValoresSeccion(contexto,funcion, llaves)
	{		
		var llavesString = JSON.stringify(llaves);
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
           url: url,
           type: 'POST',
           data: {accion : "consultarValoresSeccion",llaves: llavesString},
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
	
	iniciarSeguimiento(contexto,funcion,llaves)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "iniciarSeguimiento",llaves: JSON.stringify(llaves)},
          success: function( data, textStatus, jQxhr )
          {
              funcion.call(contexto,data);
          },
          error: function( jqXhr, textStatus, errorThrown )
          {
        	  if(textStatus=="parsererror")
      	   			funcion.call(contexto,{ mensajeError : jqXhr.responseText});
         		else
         			funcion.call(contexto,{ mensajeError : textStatus});
          },
          fail: function( jqXhr, textStatus, errorThrown )
          {
         	 funcion.call(contexto,{ mensajeError : textStatus});
          }
      });
	}
	
	consultarSeguimiento(contexto,funcion,auditoriaId)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "consultarSeguimiento",auditoriaId: auditoriaId},
          success: function( data, textStatus, jQxhr )
          {
              funcion.call(contexto,data);
          },
          error: function( jqXhr, textStatus, errorThrown )
          {
        	  if(textStatus=="parsererror")
      	   			funcion.call(contexto,{ mensajeError : jqXhr.responseText});
         		else
         			funcion.call(contexto,{ mensajeError : textStatus});
          },
          fail: function( jqXhr, textStatus, errorThrown )
          {
         	 funcion.call(contexto,{ mensajeError : textStatus});
          }
      });
	}
}