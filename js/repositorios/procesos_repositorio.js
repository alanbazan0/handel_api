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
	
	consultarPorEmpresa(contexto,funcion, empresaId)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarPorEmpresa", empresaId: empresaId},
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
	
	copiarProcesos(contexto,funcion, empresaIdOrigen, sedeIdOrigen, procedimientos, empresaIdDestino, sedeIdDestino)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "copiarProcesos", empresaIdOrigen: empresaIdOrigen, sedeIdOrigen : sedeIdOrigen, procedimientos:  JSON.stringify(procedimientos), empresaIdDestino: empresaIdDestino, sedeIdDestino: sedeIdDestino},
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
	
	adjuntarArchivo(contexto,funcion, llaves, archivo)
	{		
		var data = new FormData();
		data.append("accion", "adjuntar");
		data.append("llaves", JSON.stringify(llaves));
		if(archivo!=undefined)
    		data.append("file", archivo );
    	var url = HANDEL_API + "/" + this.servicio;
        var xhr = new XMLHttpRequest();
        xhr.open( 'POST', url, true );
		xhr.onreadystatechange = function ( resultado ) 
		{
		    if (this.readyState == 4 && this.status == 200) 
		    {
		    	var datos = JSON.parse(resultado.target.response);
		    	funcion.call(contexto,datos);
		    }
		};
		xhr.send( data ); 
	}
	
	eliminarArchivo(contexto,funcion,llaves)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "eliminarArchivo",llaves: JSON.stringify(llaves)},
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