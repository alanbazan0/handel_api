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
	
	iniciarSesion(contexto,funcion, nombreUsuario, contrasena, aplicacionId,aplicacionVersion)
	{		
		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
             url: url,
             type: 'POST',
             data: {accion : "iniciarSesion", nombreUsuario : nombreUsuario, contrasena : contrasena, aplicacionId: aplicacionId,aplicacionVersion: aplicacionVersion },
             success: function( data, textStatus, jQxhr )
             {
                 funcion.call(contexto,data);
             },
             error: function( jqXhr, textStatus, errorThrown )
             {
            	 if(textStatus=="parsererror")
            		 funcion.call(contexto,{ mensajeError : jqXhr.responseText});
            	 else
            		 funcion.call(contexto,{ mensajeError : errorThrown});
             },
             fail: function( jqXhr, textStatus, errorThrown )
             {
            	 funcion.call(contexto,{ mensajeError : errorThrown});
             }
         });
		
	
	}
	
	consultarPermisos(contexto,funcion, nombreUsuario, contrasena)
	{		
		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
             url: url,
             type: 'POST',
             data: {accion : "consultarPermisos", nombreUsuario : nombreUsuario, contrasena : contrasena },
             success: function( data, textStatus, jQxhr )
             {
                 funcion.call(contexto,data);
             },
             error: function( jqXhr, textStatus, errorThrown )
             {
            	 funcion.call(contexto,{ mensajeError : errorThrown});
             },
             fail: function( jqXhr, textStatus, errorThrown )
             {
            	 funcion.call(contexto,{ mensajeError : errorThrown});
             }
         });
		
	
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
           	 funcion.call(contexto,{ mensajeError : errorThrown});
            },
            fail: function( jqXhr, textStatus, errorThrown )
            {
           	 funcion.call(contexto,{ mensajeError : errorThrown});
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
          	 funcion.call(contexto,{ mensajeError : errorThrown});
           },
           fail: function( jqXhr, textStatus, errorThrown )
           {
          	 funcion.call(contexto,{ mensajeError : errorThrown});
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
          	 funcion.call(contexto,{ mensajeError : errorThrown});
           },
           fail: function( jqXhr, textStatus, errorThrown )
           {
          	 funcion.call(contexto,{ mensajeError : errorThrown});
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
            	 funcion.call(contexto,{ mensajeError : errorThrown});
             },
             fail: function( jqXhr, textStatus, errorThrown )
             {
            	 funcion.call(contexto,{ mensajeError : errorThrown});
             }
         });
	}
	
	consultarAdministradores(contexto,funcion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
           url: url,
           type: 'POST',
           data: {accion : "consultarAdministradores" },
           success: function( data, textStatus, jQxhr )
           {
               funcion.call(contexto,data);
           },
           error: function( jqXhr, textStatus, errorThrown )
           {
          	 funcion.call(contexto,{ mensajeError : errorThrown});
           },
           fail: function( jqXhr, textStatus, errorThrown )
           {
          	 funcion.call(contexto,{ mensajeError : errorThrown});
           }
       });
	}
}