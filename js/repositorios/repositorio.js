class Repositorio
{
	constructor(servicio) 
	{
	    this.servicio = servicio;
	    $.ajaxSetup({
			  xhrFields: {
			    withCredentials: true
			  }
			});
	}

	insertar(contexto,funcion, modelo)
	{		
		var modeloString = JSON.stringify(modelo);
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
	        url: url,
	        type: 'POST',
	        data: {accion : "insertar",modelo: modeloString},
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
	
//	insertarResultado(resultado)
//	{
//		var datos = JSON.parse(resultado);
//		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
//	}
	

	actualizar(contexto,funcion, modelo)
	{		
		var modeloString = JSON.stringify(modelo);
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
         url: url,
         type: 'POST',
         data: {accion : "actualizar",modelo: modeloString},
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
	
//	actualizarResultado(resultado)
//	{
//		var datos = JSON.parse(resultado);
//		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
//	}
	
	consultar(contexto,funcion, criteriosSeleccion, opcional)
	{		

		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
            url: url,
            type: 'POST',
            data: {accion : "consultar", criteriosSeleccion: JSON.stringify(criteriosSeleccion), opcional: opcional},
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
	
//	consultarResultado(resultado)
//	{
//		var datos = JSON.parse(resultado);
//		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
//	}	
//	
	consultarPorLlaves(contexto,funcion, llaves)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
           url: url,
           type: 'POST',
           data: {accion : "consultarPorLlaves",llaves: JSON.stringify(llaves)},
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
	
//	consultarPorLlavesResultado(resultado)
//	{
//		var datos = JSON.parse(resultado);
//		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
//	}
//	
	
	eliminar(contexto,funcion,llaves)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "eliminar",llaves: JSON.stringify(llaves)},
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
	
	copiar(contexto,funcion,llaves)
	{				
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
          url: url,
          type: 'POST',
          data: {accion : "copiar",llaves: JSON.stringify(llaves)},
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
//	
//	eliminarResultado(resultado)
//	{
//		var datos = JSON.parse(resultado);
//		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
//	}
	
}