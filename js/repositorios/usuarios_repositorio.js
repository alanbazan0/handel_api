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
	

	consultarEstructura(contexto,funcion,empresaId)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
            url: url,
            type: 'POST',
            data: {accion : "consultarEstructura",empresaId:empresaId},
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
	
	consultarUsuariosCorportarivoYAdministradores(contexto,funcion)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
           url: url,
           type: 'POST',
           data: {accion : "consultarUsuariosCorportarivoYAdministradores"},
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
	
	consultarUsuariosCorportarivoYAdministradoresPorEmpresa(contexto,funcion, empresaId)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
           url: url,
           type: 'POST',
           data: {accion : "consultarUsuariosCorportarivoYAdministradoresPorEmpresa", empresaId: empresaId},
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
	
	consultarUsuariosCorportarivoYAdministradoresPorEmpresaSIVAH(contexto,funcion, empresaId)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
           url: url,
           type: 'POST',
           data: {accion : "consultarUsuariosCorportarivoYAdministradoresPorEmpresaSIVAH", empresaId: empresaId},
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
	
	
	
	consultarUsuariosCorportarivoPorEmpresaSIVAH(contexto,funcion, empresaId)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
           url: url,
           type: 'POST',
           data: {accion : "consultarUsuariosCorportarivoPorEmpresaSIVAH", empresaId: empresaId},
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
	
	consultarUsuariosCorportarivoPorEmpresaSAHA(contexto,funcion, empresaId)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
           url: url,
           type: 'POST',
           data: {accion : "consultarUsuariosCorportarivoPorEmpresaSAHA", empresaId: empresaId},
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
	
	
	
	consultarPorEmpresaSede(contexto,funcion, empresaId, sedeId, opcional)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
           url: url,
           type: 'POST',
           data: {accion : "consultarPorEmpresaSede", empresaId : empresaId, sedeId : sedeId, opcional:opcional},
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
	
	reenviarCorreo(contexto,funcion, modelo)
	{		
		var modeloString = JSON.stringify(modelo);
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
	        url: url,
	        type: 'POST',
	        data: {accion : "reenviarCorreo",llaves: modeloString},
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
	
	consultarPorEmpresaSedeArea(contexto,funcion, empresaId, sedeId, areaId, opcional)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
           url: url,
           type: 'POST',
           data: {accion : "consultarPorEmpresaSedeArea",empresaId : empresaId, sedeId: sedeId, areaId: areaId, opcional : opcional},
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
	
	consultarPorEmpresaSedeDepartamento(contexto,funcion, empresaId, sedeId, departamentoId, opcional)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
           url: url,
           type: 'POST',
           data: {accion : "consultarPorEmpresaSedeDepartamento",empresaId : empresaId, sedeId: sedeId, departamentoId: departamentoId, opcional : opcional},
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
	
	recuperar(contexto,funcion, correoElectronico)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
	        url: url,
	        type: 'POST',
	        data: {accion : "recuperar",correoElectronico: correoElectronico},
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
	
	importarUsuarios(contexto,funcionResultado, empresaId, sedeId, departamentoId, perfilId,supervisor1Id, archivo)
	{		
		var data = new FormData();
		data.append("accion", "importar");
		data.append("empresaId", empresaId);
		data.append("sedeId", sedeId);
		data.append("departamentoId", departamentoId);
		data.append("perfilId", perfilId);
		data.append("supervisor1Id", supervisor1Id);
    	data.append("file", archivo );
    	var url = HANDEL_API + "/" + this.servicio;
        var xhr = new XMLHttpRequest();
        xhr.open( 'POST', url, true );
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
	
	actualizarPerfil(contexto,funcion, usuariosIds, perfilId)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
	        url: url,
	        type: 'POST',
	        data: {accion : "actualizarPerfil",usuariosIds: usuariosIds,perfilId:perfilId },
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
	
	reemplazarUsuario(contexto,funcion, origenUsuarioId, destinoUsuarioId)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
	        url: url,
	        type: 'POST',
	        data: {accion : "reemplazarUsuario",origenUsuarioId: origenUsuarioId, destinoUsuarioId:destinoUsuarioId },
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