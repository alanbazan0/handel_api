class CapacitacionesRepositorio extends Repositorio
{	
	constructor()
	{
		super("/php/repositorios/Cursos.php");
	}
	
	guardarRespuestasSi(contexto,funcion,cursoId, leccionId, preguntaId, respuestas)
	{		
		var respuestasString =  JSON.stringify(respuestas);
		var url =  HANDEL_API + "/" +this.servicio;
		 $.ajax({
	        url: url,
	        type: 'POST',
	        data: {accion : "guardarRespuestasSi", cursoId: cursoId, leccionId: leccionId, preguntaId : preguntaId, respuestas:respuestasString},
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
	
	guardarRespuestasNo(contexto,funcion,cursoId, leccionId, preguntaId, respuestas)
	{		
		var respuestasString =  JSON.stringify(respuestas);
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
	        url: url,
	        type: 'POST',
	        data: {accion : "guardarRespuestasNo", cursoId: cursoId, leccionId: leccionId, preguntaId : preguntaId, respuestas:respuestasString},
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
	
	ordenarPreguntas(contexto,funcion,cursoId, leccionId, seleccion)
	{		
		var respuestasString =  JSON.stringify(seleccion);
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
	        url: url,
	        type: 'POST',
	        data: {accion : "ordenarPreguntas", 'cursoId':cursoId,'leccionId':leccionId,'seleccion':seleccion},
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
	
	ordenarCursos(contexto,funcion, seleccion)
	{		
		var respuestasString =  JSON.stringify(seleccion);
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
	        url: url,
	        type: 'POST',
	        data: {accion : "ordenarCursos",'seleccion':seleccion},
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
	
	eliminarCursoUsuario(contexto,funcion,llaves)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "eliminarCursoUsuario",llaves: JSON.stringify(llaves)},
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
	
	eliminarRespuesta(contexto,funcion,llaves)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "eliminarRespuesta",llaves: JSON.stringify(llaves)},
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
	
	insertarPregunta(contexto,funcion,cursoId, leccionId, tipo)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "insertarPregunta",cursoId: cursoId,leccionId: leccionId,tipo: tipo},
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
	
	insertarRespuesta(contexto,funcion,cursoId, leccionId, preguntaId, respuesta)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "insertarRespuesta",cursoId: cursoId,leccionId: leccionId,preguntaId: preguntaId},
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
	
	insertarRespuestaCorrecta(contexto,funcion,cursoId, leccionId, preguntaId, respuesta)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "insertarRespuestaCorrecta",cursoId: cursoId,leccionId: leccionId,preguntaId: preguntaId},
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
	
	actualizarValorPregunta(contexto,funcion,cursoId, leccionId, preguntaId, campo, valor)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "actualizarValorPregunta",cursoId: cursoId, leccionId: leccionId, preguntaId, preguntaId, campo: campo, valor: valor},
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
	
	actualizarValorRespuesta(contexto,funcion,cursoId, leccionId, preguntaId, respuestaId, campo, valor)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "actualizarValorRespuesta",cursoId: cursoId, leccionId: leccionId, preguntaId, preguntaId, respuestaId: respuestaId, campo: campo, valor: valor},
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
	
	actualizarValorRespuestaCorrecta(contexto,funcion,cursoId, leccionId, preguntaId, respuestaId, valor)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "actualizarValorRespuestaCorrecta",cursoId: cursoId, leccionId: leccionId, preguntaId, preguntaId, respuestaId: respuestaId, valor: valor},
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
	
	actualizarCategoriasPregunta(contexto,funcion,cursoId, leccionId, preguntaId, categorias)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "actualizarCategoriasPregunta",cursoId: cursoId, leccionId: leccionId, preguntaId, preguntaId, categorias:  JSON.stringify(categorias)},
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
	
	consultarPorToken(contexto,funcion, token)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
           url: url,
           type: 'POST',
           data: {accion : "consultarPorToken",token: token},
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
	
	consultarPorTokenSinPreguntas(contexto,funcion, token)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
           url: url,
           type: 'POST',
           data: {accion : "consultarPorTokenSinPreguntas",token: token},
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
	
	consultarCursosPendientes(contexto,funcion, criteriosSeleccion, opcional)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
            url: url,
            type: 'POST',
            data: {accion : "consultarCursosPendientes", criteriosSeleccion: JSON.stringify(criteriosSeleccion), opcional: opcional},
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
	
	consultarCursosContestando(contexto,funcion, criteriosSeleccion, opcional)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
            url: url,
            type: 'POST',
            data: {accion : "consultarCursosContestando", criteriosSeleccion: JSON.stringify(criteriosSeleccion), opcional: opcional},
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
	
	consultarCursosTerminados(contexto,funcion, criteriosSeleccion, opcional)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
            url: url,
            type: 'POST',
            data: {accion : "consultarCursosTerminados", criteriosSeleccion: JSON.stringify(criteriosSeleccion), opcional: opcional},
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
	
	
	consultarPreguntaAleatoria(contexto,funcion,cursoId, leccionId, modo)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "consultarPreguntaAleatoria",cursoId: cursoId, leccionId: leccionId, modo: modo},
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
	
	actualizarDuracionLeccion(contexto,funcion,cursoId, leccionId, duracion)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "actualizarDuracionLeccion",cursoId: cursoId, leccionId: leccionId, duracion: duracion},
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
	
	guardarLeccionUsuario(contexto,funcion,cursoId, leccionId, duracion)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "guardarLeccionUsuario",cursoId: cursoId, leccionId: leccionId, duracion: duracion},
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
	
	guardarPreguntaUsuario(contexto,funcion,cursoId, leccionId, preguntaId, respuestaId)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "guardarPreguntaUsuario",cursoId: cursoId, leccionId: leccionId, preguntaId : preguntaId, respuestaId : respuestaId},
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
	
	consultarAvanceUsuario(contexto,funcion)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "consultarAvanceUsuario"},
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
	
	
	consultarAprovechamientoUsuario(contexto,funcion)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "consultarAprovechamientoUsuario"},
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
	
	consultarVideosVistosUsuario(contexto,funcion)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "consultarVideosVistosUsuario"},
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
	
	consultarDiasCapacitacionUsuario(contexto,funcion)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "consultarDiasCapacitacionUsuario"},
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
	
	consultarResultadosUsuarios(contexto,funcion, criteriosSeleccion)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "consultarResultadosUsuarios",criteriosSeleccion: JSON.stringify(criteriosSeleccion)},
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
	
	consultarAvanceUsuarios(contexto,funcion, criteriosSeleccion)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "consultarAvanceUsuarios",criteriosSeleccion: JSON.stringify(criteriosSeleccion)},
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
	
	consultarTiempoUsuarios(contexto,funcion, criteriosSeleccion)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "consultarTiempoUsuarios",criteriosSeleccion: JSON.stringify(criteriosSeleccion)},
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
	
	
	
	consultarResultadosDepartamentos(contexto,funcion, criteriosSeleccion)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "consultarResultadosDepartamentos",criteriosSeleccion: JSON.stringify(criteriosSeleccion)},
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
	
	consultarAvanceDepartamentos(contexto,funcion, criteriosSeleccion)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "consultarAvanceDepartamentos",criteriosSeleccion: JSON.stringify(criteriosSeleccion)},
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
	
	consultarCriterio(contexto,funcion, criteriosSeleccion, opcional)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "consultarCriterio",criteriosSeleccion: JSON.stringify(criteriosSeleccion), opcional: opcional},
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
	
	consultarCapacitacionesTomadas(contexto,funcion, criteriosSeleccion)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "consultarCapacitacionesTomadas",criteriosSeleccion: JSON.stringify(criteriosSeleccion)},
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
	
	consultarLeccionesTomadas(contexto,funcion, criteriosSeleccion)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "consultarLeccionesTomadas",criteriosSeleccion: JSON.stringify(criteriosSeleccion)},
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
	

	consultarTareaPorLlaves(contexto,funcion,llaves)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "consultarTareaPorLlaves", llaves:JSON.stringify(llaves)},
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