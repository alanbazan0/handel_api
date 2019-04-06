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
	
	iniciarSesion(contexto,funcion, nombreUsuario, contrasena)
	{		
//		this.contexto = contexto;
//		this.functionRetorno = funcion;
//		
//		var parametros;
//		parametros = "accion=iniciarSesion";
//		parametros += "&nombreUsuario=" + encodeURIComponent(nombreUsuario);		
//		parametros += "&contrasena=" + encodeURIComponent(contrasena);		
//		
//		var contextHandler = new AjaxContextHandler();
//		var url = HANDEL_API + "/" + this.servicio;
//		var ai = new Ajaxv2( url , this, this.iniciarSesionResultado, "POST", parametros, contextHandler);		
//		contextHandler.AddAjaxv2Object(ai); 		
//		ai.GetPost(true);
		
//		var url = HANDEL_API + "/" + this.servicio;
//		$.post(url, {accion : "iniciarSesion", nombreUsuario : nombreUsuario, contrasena : contrasena }, function(resultado) 
//		{
//			funcion.call(contexto,resultado);
//		});
		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
             url: url,
             type: 'POST',
             data: {accion : "iniciarSesion", nombreUsuario : nombreUsuario, contrasena : contrasena },
             success: function( data, textStatus, jQxhr )
             {
               //  $('#response pre').html( data );
                 	
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
//		this.contexto = contexto;
//		this.functionRetorno = functionRetorno;
//		
//		var parametros;
//		parametros = "accion=cerrarSesion";
//		
//		var contextHandler = new AjaxContextHandler();
//		var url = HANDEL_API + "/" + this.servicio;
//		var ai = new Ajaxv2( url, this, this.cerrarSesionResultado, "POST", parametros, contextHandler);		
//		contextHandler.AddAjaxv2Object(ai); 		
//		ai.GetPost(true);
		var url = HANDEL_API + "/" + this.servicio;
		$.post(url, {accion : "cerrarSesion" }, function(resultado) 
		{
			//funcion.call(contexto,resultado);
			funcion.call(contexto,resultado);
		});
	}
	
//	cerrarSesionResultado(resultado)
//	{
//		var datos = JSON.parse(resultado);
//		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
//	}	
	
	consultarSupervisoresPorEmpresa(contexto,funcion, empresaId, usuarioId)
	{		
//		this.contexto = contexto;
//		this.functionRetorno = functionRetorno;
//		
//		var parametros;
//		parametros = "accion=consultarSupervisoresPorEmpresa";
//		parametros += "&empresaId=" + empresaId;	
//		parametros += "&usuarioId=" + usuarioId;
//		
//		var contextHandler = new AjaxContextHandler();
//		var url = HANDEL_API + "/" + this.servicio;
//		var ai = new Ajaxv2( url, this, this.consultarSupervisoresPorEmpresaResultado, "POST", parametros, contextHandler);		
//		contextHandler.AddAjaxv2Object(ai); 		
//		ai.GetPost(true);
		var url = HANDEL_API + "/" + this.servicio;
		$.post(url, {accion : "consultarSupervisoresPorEmpresa", empresaId : empresaId, usuarioId : usuarioId }, function(resultado) 
		{
			//funcion.call(contexto,resultado);
			funcion.call(contexto,resultado);
		});
	}
	
//	consultarSupervisoresPorEmpresaResultado(resultado)
//	{
//		var datos = JSON.parse(resultado);
//		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
//	}	
	
	
	subirFotoPerfil(contexto, funcionResultado,archivo)
	{
		var data = new FormData();
		data.append("accion", "subirFotoPerfil");
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
}