class SedesRepositorio extends Repositorio
{	
	constructor()
	{
		super("php/repositorios/Sedes.php");
	}
	
	consultarPorEmpresa(contexto,funcion, empresaId, opcional)
	{		
//		this.contexto = contexto;
//		this.functionRetorno = functionRetorno;
//		
//		var parametros;
//		parametros = "accion=consultarPorEmpresa";
//		parametros += "&empresaId=" + empresaId;		
//		parametros += "&opcional=" + opcional;	
//		
//		var contextHandler = new AjaxContextHandler();
//		var url = HANDEL_API + "/" + this.servicio;
//		var ai = new Ajaxv2( url, this, this.consultarPorEmpresaResultado, "POST", parametros, contextHandler);		
//		contextHandler.AddAjaxv2Object(ai); 		
//		ai.GetPost(true);
		
//		var url = HANDEL_API + "/" + this.servicio;
//		$.post(url, {accion : "consultarPorEmpresa", empresaId : empresaId, opcional : opcional}, function(resultado) 
//		{
//			funcion.call(contexto,resultado);
//		});
		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarPorEmpresa", empresaId : empresaId, opcional : opcional},
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
	
	consultarPorEmpresaUsuario(contexto,funcion, empresaId, opcional)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		   $.ajax({
	       url: url,
	       type: 'POST',
	       data: {accion : "consultarPorEmpresaUsuario", empresaId : empresaId, opcional : opcional},
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
//	
//	consultarPorEmpresaResultado(resultado)
//	{
//		var datos = JSON.parse(resultado);
//		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
//	}	
}