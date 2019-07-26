class UsuariosRepositorio extends Repositorio
{	
	constructor()
	{
		super("php/repositorios/Usuarios.php");
		$.ajaxSetup({
			  xhrFields: {
			    withCredentials: true
			  }
			});
	}
	
	iniciarSesion(contexto,funcion, nombreUsuario, contrasena, aplicacionId)
	{		
		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
             url: url,
             type: 'POST',
             data: {accion : "iniciarSesion", nombreUsuario : nombreUsuario, contrasena : contrasena, aplicacionId: aplicacionId },
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
	
	iniciarSesionResultado(resultado)
	{
		var datos = JSON.parse(resultado);
		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
	}	
	
	cerrarSesion(contexto,funcion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
            url: url,
            type: 'POST',
            data: {accion : "cerrarSesion" },
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
	
	consultarSupervisoresPorEmpresa(contexto,funcion, empresaId, usuarioId)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
           url: url,
           type: 'POST',
           data: {accion : "consultarSupervisoresPorEmpresa", empresaId : empresaId, usuarioId : usuarioId },
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
	
	consultarPorEmpresaSede(contexto,funcion, empresaId, sedeId)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
           url: url,
           type: 'POST',
           data: {accion : "consultarPorEmpresaSede", empresaId : empresaId, sedeId : sedeId },
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
	
	
	subirFotoPerfil(contexto, funcion,archivo)
	{
		var data = new FormData();
		data.append("accion", "subirFotoPerfil");
    	data.append("file", archivo );
    	var url = HANDEL_API + "/" + this.servicio;
    	 $.ajax({
             url: url,
             type: 'POST',
             method: 'POST',
             cache: false,
             contentType: false,
             processData: false,
             data: data,
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