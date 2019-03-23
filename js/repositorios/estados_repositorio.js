class EstadosRepositorio extends Repositorio
{	
	
	constructor()
	{
		super("php/repositorios/Estados.php");
	}
	
	
	consultarPorPais(contexto,functionRetorno, paisId)
	{		
		this.contexto = contexto;
		this.functionRetorno = functionRetorno;
		
		var parametros;
		parametros = "accion=consultarPorPais";
		parametros += "&paisId=" + paisId;		
		
		var contextHandler = new AjaxContextHandler();
		var url = HANDEL_API + "/" + this.servicio;
		var ai = new Ajaxv2( url, this, this.consultarPorPaisResultado, "POST", parametros, contextHandler);		
		contextHandler.AddAjaxv2Object(ai); 		
		ai.GetPost(true);
	}
	
	consultarPorPaisResultado(resultado)
	{
		var datos = JSON.parse(resultado);
		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
	}	
	
	
	
	

}