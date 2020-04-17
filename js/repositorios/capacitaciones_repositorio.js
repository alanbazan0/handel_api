class CapacitacionesRepositorio extends Repositorio
{	
	constructor()
	{
		super("/php/repositorios/Cursos.php");
	}
	
	guardarRespuestasSi(contexto,funcion,plantillaId, seccionId, preguntaId, respuestas)
	{		
		var respuestasString =  JSON.stringify(respuestas);
		var url =  HANDEL_API + "/" +this.servicio;
		 $.ajax({
	        url: url,
	        type: 'POST',
	        data: {accion : "guardarRespuestasSi", plantillaId: plantillaId, seccionId: seccionId, preguntaId : preguntaId, respuestas:respuestasString},
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
	
	guardarRespuestasNo(contexto,funcion,plantillaId, seccionId, preguntaId, respuestas)
	{		
		var respuestasString =  JSON.stringify(respuestas);
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
	        url: url,
	        type: 'POST',
	        data: {accion : "guardarRespuestasNo", plantillaId: plantillaId, seccionId: seccionId, preguntaId : preguntaId, respuestas:respuestasString},
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
	
	insertar(contexto,funcion, modelo, logo)
	{		
	//		var data = new FormData();
	//		data.append("accion", "insertar");
	//		data.append("modelo", JSON.stringify(modelo));
	//    	data.append("file", logo );
	//    	var url = HANDEL_API + "/" + this.servicio;
//        var xhr = new XMLHttpRequest();
//        xhr.widthCredentials = true;
//        xhr.open( 'POST', url, true );
//        xhr.onreadystatechange = function ( resultado ) 
//		{
//		    if (this.readyState == 4 && this.status == 200) 
//		    {
//		    	var datos = null;
//		    	try 
//		    	{
//		    		datos = JSON.parse(resultado.target.response);
//		    		funcionResultado.call(contexto,datos);
//				} 
//		    	catch (e) 
//				{
//		    		datos = new Object();
//		    		datos.mensajeError = resultado.target.response;
//		    		funcionResultado.call(contexto,datos);
//				}
//		    
//		    	
//		    }
//		};
//
//		xhr.send( data );  
		
		var data = new FormData();
		data.append("accion", "insertar");
		data.append("modelo", JSON.stringify(modelo));
		data.append("file", logo );
    	var url = HANDEL_API + "/" + this.servicio;
    	$.ajax({
            url: url,
            data: data,
            processData: false,
            contentType: false,
            type: 'POST',
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
	
	actualizar(contexto,funcionResultado, modelo, logo)
	{		
		var data = new FormData();
		data.append("accion", "actualizar");
		data.append("modelo", JSON.stringify(modelo));
    	data.append("file", logo );
    	var url = HANDEL_API + "/" + this.servicio;
        var xhr = new XMLHttpRequest();
        xhr.open( 'POST',url, true );
		xhr.onreadystatechange = function ( resultado ) 
		{
		    if (this.readyState == 4 && this.status == 200) 
		    {
		    	var datos = JSON.parse(resultado.target.response);
		    	funcionResultado.call(contexto,datos);
		    }
		};
		xhr.send( data );  
	}
	
	ordenarPreguntas(contexto,funcion,plantillaId, seccionId, seleccion)
	{		
		var respuestasString =  JSON.stringify(seleccion);
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
	        url: url,
	        type: 'POST',
	        data: {accion : "ordenarPreguntas", 'plantillaId':plantillaId,'seccionId':seccionId,'seleccion':seleccion},
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
	
	eliminarPregunta(contexto,funcion,llaves)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "eliminarPregunta",llaves: JSON.stringify(llaves)},
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
	
	insertarPregunta(contexto,funcion,plantillaId, seccionId, tipo)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "insertarPregunta",plantillaId: plantillaId,seccionId: seccionId,tipo: tipo},
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
	

	insertarLeccion(contexto,funcion,cursoId, titulo)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "insertarLeccion",cursoId: cursoId, titulo: titulo},
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
	
	eliminarLeccion(contexto,funcion,llaves)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "eliminarLeccion",llaves: JSON.stringify(llaves)},
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
	
	actualizarValor(contexto,funcion,cursoId, campo, valor)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "actualizarValor",cursoId: cursoId, campo: campo, valor: valor},
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
	
	actualizarLogo(contexto,funcion,cursoId,logo)
	{				
		var data = new FormData();
		data.append("accion", "actualizarLogo");
		data.append("cursoId", cursoId);
		data.append("file", logo );
    	var url = HANDEL_API + "/" + this.servicio;
    	$.ajax({
            url: url,
            data: data,
            processData: false,
            contentType: false,
            type: 'POST',
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
	
	
	ordenarLecciones(contexto,funcion,cursoId, seleccion)
	{		
		var respuestasString =  JSON.stringify(seleccion);
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
	        url: url,
	        type: 'POST',
	        data: {accion : "ordenarLecciones", 'cursoId':cursoId,'seleccion':seleccion},
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
	        	 if(textStatus=="parsererror")
	      	   			funcion.call(contexto,{ mensajeError : jqXhr.responseText});
	         		else
	         			funcion.call(contexto,{ mensajeError : textStatus});
	        }
	    });
	}
	
	actualizarValorPregunta(contexto,funcion,plantillaId, seccionId, preguntaId, campo, valor)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "actualizarValorPregunta",plantillaId: plantillaId, seccionId: seccionId, preguntaId, preguntaId, campo: campo, valor: valor},
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
	
	actualizarCategoriasPregunta(contexto,funcion,plantillaId, seccionId, preguntaId, categorias)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "actualizarCategoriasPregunta",plantillaId: plantillaId, seccionId: seccionId, preguntaId, preguntaId, categorias:  JSON.stringify(categorias)},
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
	
	actualizarValorLeccion(contexto,funcion,cursoId, leccionId, campo, valor)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "actualizarValorLeccion",cursoId: cursoId, leccionId: leccionId, campo: campo, valor: valor},
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
	
	actualizarPerfiles(contexto,funcion,cursoId, perfiles)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "actualizarPerfiles",cursoId: cursoId, perfiles:JSON.stringify(perfiles)},
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