class EstadosRepositorio extends Repositorio
{	
	
	constructor()
	{
		super("php/repositorios/Estados.php");
	}
	
	
	consultarPorPais(contexto,funcion, paisId)
	{		
//		this.contexto = contexto;
//		this.functionRetorno = functionRetorno;
//		
//		var parametros;
//		parametros = "accion=consultarPorPais";
//		parametros += "&paisId=" + paisId;		
//		
//		var contextHandler = new AjaxContextHandler();
//		var url = HANDEL_API + "/" + this.servicio;
//		var ai = new Ajaxv2( url, this, this.consultarPorPaisResultado, "POST", parametros, contextHandler);		
//		contextHandler.AddAjaxv2Object(ai); 		
//		ai.GetPost(true);
		var url = HANDEL_API + "/" + this.servicio;
		$.post(url, {accion : "consultarPorPais", paisId : paisId}, function(resultado) 
		{
			funcion.call(contexto,resultado);
		});
	}
//	
//	consultarPorPaisResultado(resultado)
//	{
//		var datos = JSON.parse(resultado);
//		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
//	}	
	
	
	
	

}