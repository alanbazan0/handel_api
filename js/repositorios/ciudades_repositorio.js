class CiudadesRepositorio extends Repositorio
{	
	
	constructor()
	{
		super("php/repositorios/Ciudades.php");
	}
	
	
	consultarPorPaisEstado(contexto,funcion, paisId, estadoId)
	{		
//		this.contexto = contexto;
//		this.functionRetorno = functionRetorno;
//		
//		var parametros;
//		parametros = "accion=consultarPorPaisEstado";
//		parametros += "&paisId=" + paisId;	
//		parametros += "&estadoId=" + estadoId;
//		
//		var contextHandler = new AjaxContextHandler();
//		var url = HANDEL_API + "/" + this.servicio;
//		var ai = new Ajaxv2( url, this, this.consultarPorPaisEstadoResultado, "POST", parametros, contextHandler);		
//		contextHandler.AddAjaxv2Object(ai); 		
//		ai.GetPost(true);
		var url = HANDEL_API + "/" + this.servicio;
		$.post(url, {accion : "consultarPorPaisEstado", paisId : paisId, estadoId: estadoId}, function(resultado) 
		{
			funcion.call(contexto,resultado);
		});
	}
	
//	consultarPorPaisEstadoResultado(resultado)
//	{
//		var datos = JSON.parse(resultado);
//		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
//	}	
//	
	
	
	

}