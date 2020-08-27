class EvidenciasRepositorio extends Repositorio
{	
	constructor()
	{
		super("php/repositorios/Evidencias.php");
	}
	
	insertar(contexto,funcionResultado, modelo, fotoEvidencia)
	{		
		var data = new FormData();
		data.append("accion", "insertar");
		data.append("modelo", JSON.stringify(modelo));
    	data.append("file", fotoEvidencia );
    	var url = HANDEL_API + "/" + this.servicio;
        var xhr = new XMLHttpRequest();
        xhr.open( 'POST', url, true );
        xhr.withCredentials = true;
		xhr.onreadystatechange = function ( resultado ) 
		{
		    if (this.readyState == 4 && this.status == 200) 
		    {
		    	var datos = null;
		    	try 
		    	{
		    		datos = JSON.parse(resultado.target.response);
		    		funcionResultado.call(contexto,datos);
				} 
		    	catch (e) 
				{
		    		datos = new Object();
		    		datos.mensajeError = resultado.target.response;
		    		funcionResultado.call(contexto,datos);
				}
		    
		    	
		    }
		};
		xhr.send( data );  
	}
	
	actualizar(contexto,funcionResultado, modelo, fotoEvidencia)
	{		
		var data = new FormData();
		data.append("accion", "actualizar");
		data.append("modelo", JSON.stringify(modelo));
    	data.append("file", fotoEvidencia );
    	var url = HANDEL_API + "/" + this.servicio;
        var xhr = new XMLHttpRequest();
        xhr.open( 'POST', url, true );
        xhr.withCredentials = true;
		xhr.onreadystatechange = function ( resultado ) 
		{
		    if (this.readyState == 4 && this.status == 200) 
		    {
		    	var datos = null;
		    	try 
		    	{
		    		datos = JSON.parse(resultado.target.response);
		    		funcionResultado.call(contexto,datos);
				} 
		    	catch (e) 
				{
		    		datos = new Object();
		    		datos.mensajeError = resultado.target.response;
		    		funcionResultado.call(contexto,datos);
				}
		    
		    	
		    }
		};
		xhr.send( data );  
	}
	
//	validarEvidencia(contexto,funcionResultado, modelo)
//	{		
//		var data = new FormData();
//		data.append("accion", "validarEvidencia");
//		data.append("modelo", JSON.stringify(modelo));
//    	var url = HANDEL_API + "/" + this.servicio;
//        var xhr = new XMLHttpRequest();
//        xhr.open( 'POST', url, true );
//		xhr.onreadystatechange = function ( resultado ) 
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
//		xhr.send( data );  
//	}
	
	
	validarEvidencia(contexto,funcion, modelo)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "validarEvidencia",modelo: JSON.stringify(modelo)},
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
	
	consultarComentariosEvidencia(contexto,funcion, evidenciaId)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarComentariosEvidencia", evidenciaId: evidenciaId},
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
	
	consultarEvidenciasCumplidas(contexto,funcion, criteriosSeleccion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarEvidenciasCumplidas",criteriosSeleccion:JSON.stringify(criteriosSeleccion)},
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
	
	consultarEvidenciasJustificacion(contexto,funcion, criteriosSeleccion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarEvidenciasJustificacion", criteriosSeleccion:JSON.stringify(criteriosSeleccion)},
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
	
	consultarEvidencias(contexto,funcion,criteriosSeleccion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarEvidencias",criteriosSeleccion:JSON.stringify(criteriosSeleccion)},
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
	
	consultarPorcentajesEvidencias(contexto,funcion,criteriosSeleccion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarPorcentajesEvidencias",criteriosSeleccion:JSON.stringify(criteriosSeleccion)},
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
	
	consultarPorcentajesEmpresas(contexto,funcion,criteriosSeleccion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarPorcentajesEmpresas",criteriosSeleccion:JSON.stringify(criteriosSeleccion)},
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
	
	consultarPorcentajesSedes(contexto,funcion,criteriosSeleccion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarPorcentajesSedes",criteriosSeleccion:JSON.stringify(criteriosSeleccion)},
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
	
	consultarPorcentajesAdministradores(contexto,funcion,criteriosSeleccion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarPorcentajesAdministradores",criteriosSeleccion:JSON.stringify(criteriosSeleccion)},
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
	
	consultarPorcentajesAreas(contexto,funcion,criteriosSeleccion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarPorcentajesAreas",criteriosSeleccion:JSON.stringify(criteriosSeleccion)},
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
	
	consultarPorcentajesUsuarios(contexto,funcion,criteriosSeleccion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarPorcentajesUsuarios",criteriosSeleccion:JSON.stringify(criteriosSeleccion)},
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
	
	consultarAnosMeses(contexto,funcion,criteriosSeleccion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarAnosMeses",criteriosSeleccion:JSON.stringify(criteriosSeleccion)},
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
	
	consultarEvidenciasAnualUsuario(contexto,funcion,criteriosSeleccion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarEvidenciasAnualUsuario",criteriosSeleccion:JSON.stringify(criteriosSeleccion)},
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