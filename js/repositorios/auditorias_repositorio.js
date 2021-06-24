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
	
	finalizarSeguimiento(contexto,funcion,llaves)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "finalizarSeguimiento",llaves: JSON.stringify(llaves)},
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
	
	consultarRecomendacionesPendientesUsuario(contexto,funcion,llaves,criteriosSeleccion)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "consultarRecomendacionesPendientesUsuario", llaves : JSON.stringify(llaves),criteriosSeleccion : JSON.stringify(criteriosSeleccion)},
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
	
	
	consultarRecomendacionPorLlaves(contexto,funcion, llaves)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
           url: url,
           type: 'POST',
           data: {accion : "consultarRecomendacionPorLlaves",llaves: JSON.stringify(llaves)},
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
	
	validarRecomendacion(contexto,funcion, modelo)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "validarRecomendacion",modelo: JSON.stringify(modelo)},
	       success: function( data, textStatus, jQxhr )
	       {
	           funcion.call(contexto,data);
	       },
	       error: function( jqXhr, textStatus, errorThrown )
	       {
	      	 funcion.call(contexto,{ mensajeError : errorThrown+ "." +jqXhr.responseText});
	       },
	       fail: function( jqXhr, textStatus, errorThrown )
	       {
	      	 funcion.call(contexto,{ mensajeError : errorThrown+ "." +jqXhr.responseText});
	       }
	   });
	}
	
	subirArchivosAvance(contexto,funcion, funcionProgreso, recomendacionId, avanceId, archivos)
	{		
		var data = new FormData();
		data.append("accion", "subirArchivosAvance");
		data.append("recomendacionId", recomendacionId);
		data.append("avanceId", avanceId);
    	//data.append("file", fotoEvidencia );

		for(var i=0; i < archivos.length; i++) 
		{
			var archivo = archivos[i];
	        data.append("file[]", archivo);
		}
		
    	var url = HANDEL_API + "/" + this.servicio;
        var xhr = new XMLHttpRequest();
        xhr.open( 'POST', url, true );
        xhr.withCredentials = true;
		xhr.upload.addEventListener('progress',function(event)
		{
			if(event.lengthComputable)
			{
				
			}
			else
			{
				
			}
			funcionProgreso.call(contexto, event);
		});
		xhr.onreadystatechange = function ( resultado ) 
		{
		    if (this.readyState == 4 && this.status == 200) 
		    {
		    	var datos = null;
		    	try 
		    	{
		    		datos = JSON.parse(resultado.target.response);
		    		funcion.call(contexto,datos);
				} 
		    	catch (e) 
				{
		    		datos = new Object();
		    		datos.mensajeError = resultado.target.response;
		    		funcion.call(contexto,datos);
				}
		    	
		    }
		};
		xhr.send( data );  
	}
	
	actualizarRecomendacion(contexto,funcion,modelo)
	{		
		var modeloString = JSON.stringify(modelo);
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
         url: url,
         type: 'POST',
         data: {accion : "actualizarRecomendacion",modelo: modeloString},
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
	
	consultarAnos(contexto,funcion,criteriosSeleccion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarAnos",criteriosSeleccion:JSON.stringify(criteriosSeleccion)},
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