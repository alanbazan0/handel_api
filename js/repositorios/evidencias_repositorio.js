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
	
	validarEvidencia(contexto,funcionResultado, modelo)
	{		
		var data = new FormData();
		data.append("accion", "validarEvidencia");
		data.append("modelo", JSON.stringify(modelo));
    	var url = HANDEL_API + "/" + this.servicio;
        var xhr = new XMLHttpRequest();
        xhr.open( 'POST', url, true );
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
	
	consultarEvidenciasCumplidasMesActual(contexto,funcion, usuarioId)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarEvidenciasCumplidasMesActual", usuarioId: usuarioId},
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
	
	consultarEvidenciasJustificacionMesActual(contexto,funcion, usuarioId)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarEvidenciasJustificacionMesActual", usuarioId: usuarioId},
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
	
	consultarEvidenciasMesActual(contexto,funcion,criteriosSeleccion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarEvidenciasMesActual",criteriosSeleccion:JSON.stringify(criteriosSeleccion)},
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
	
	consultarPorcentajesEvidenciasMesActual(contexto,funcion,criteriosSeleccion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarPorcentajesEvidenciasMesActual",criteriosSeleccion:JSON.stringify(criteriosSeleccion)},
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
	
	consultarPorcentajesEmpresasMesActual(contexto,funcion,criteriosSeleccion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarPorcentajesEmpresasMesActual",criteriosSeleccion:JSON.stringify(criteriosSeleccion)},
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
	
	consultarPorcentajesAdministradoresMesActual(contexto,funcion,criteriosSeleccion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarPorcentajesAdministradoresMesActual",criteriosSeleccion:JSON.stringify(criteriosSeleccion)},
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
	
	consultarPorcentajesAreasMesActual(contexto,funcion,criteriosSeleccion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarPorcentajesAreasMesActual",criteriosSeleccion:JSON.stringify(criteriosSeleccion)},
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
	
	consultarPorcentajesUsuariosMesActual(contexto,funcion,criteriosSeleccion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarPorcentajesUsuariosMesActual",criteriosSeleccion:JSON.stringify(criteriosSeleccion)},
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