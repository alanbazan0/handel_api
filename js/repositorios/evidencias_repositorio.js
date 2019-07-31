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
	      	 funcion.call(contexto,{ mensajeError : textStatus});
	       },
	       fail: function( jqXhr, textStatus, errorThrown )
	       {
	      	 funcion.call(contexto,{ mensajeError : textStatus});
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
	      	 funcion.call(contexto,{ mensajeError : textStatus});
	       },
	       fail: function( jqXhr, textStatus, errorThrown )
	       {
	      	 funcion.call(contexto,{ mensajeError : textStatus});
	       }
	   });
	}
	
	
}