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
	
	consultarActivasPorUsuario(contexto,funcion)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "consultarActivasPorUsuario"},
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
	
	consultarRecomendacionesPendientesUsuario(contexto,funcion,llaves)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "consultarRecomendacionesPendientesUsuario", llaves : JSON.stringify(llaves)},
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
	
	consultarAvancesRecomendacion(contexto,funcion,llaves)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "consultarAvancesRecomendacion", llaves : JSON.stringify(llaves)},
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
	
	consultarArchivosAvance(contexto,funcion,llaves)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "consultarArchivosAvance", llaves : JSON.stringify(llaves)},
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
	
	actualizarAvance(contexto,funcion,recomendacionId, modelo)
	{		
		var modeloString = JSON.stringify(modelo);
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
         url: url,
         type: 'POST',
         data: {accion : "actualizarAvance",modelo: modeloString, recomendacionId: recomendacionId},
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
	
	insertarAvance(contexto,funcion, recomendacionId, modelo)
	{		
		var modeloString = JSON.stringify(modelo);
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
	        url: url,
	        type: 'POST',
	        data: {accion : "insertarAvance",modelo: modeloString, recomendacionId: recomendacionId},
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
	
	consultarAvancePorLlaves(contexto,funcion, llaves)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
           url: url,
           type: 'POST',
           data: {accion : "consultarAvancePorLlaves",llaves: JSON.stringify(llaves)},
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
	
	eliminarAvance(contexto,funcion,llaves)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "eliminarAvance",llaves: JSON.stringify(llaves)},
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