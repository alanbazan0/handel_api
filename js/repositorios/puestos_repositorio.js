class PuestosRepositorio extends Repositorio
{	
	constructor()
	{
		super("php/repositorios/Puestos.php");
	}
	
	consultarPorEmpresaSede(contexto,funcion, empresaId, sedeId)
	{		
//		this.contexto = contexto;
//		this.functionRetorno = functionRetorno;
//		
//		var parametros;
//		parametros = "accion=consultarPorEmpresaSede";
//		parametros += "&empresaId=" + empresaId;		
//		parametros += "&sedeId=" + sedeId;		
//		
//		var contextHandler = new AjaxContextHandler();
//		var url = HANDEL_API + "/" + this.servicio;
//		var ai = new Ajaxv2( url, this, this.consultarPorEmpresaSedeResultado, "POST", parametros, contextHandler);		
//		contextHandler.AddAjaxv2Object(ai); 		
//		ai.GetPost(true);
//		var url = HANDEL_API + "/" + this.servicio;
//		$.post(url, {accion : "consultarPorEmpresaSede", empresaId: empresaId, sedeId : sedeId}, function(resultado) 
//		{
//			funcion.call(contexto,resultado);
//		});
		
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
	
//	consultarPorEmpresaSedeResultado(resultado)
//	{
//		var datos = JSON.parse(resultado);
//		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
//	}	
	
}