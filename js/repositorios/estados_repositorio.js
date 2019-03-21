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
//		var host = window.location.origin + "/" + CARPETA_PROYECTO;
//		var url = host + this.servicio;
		var ai = new Ajaxv2( this.servicio, this, this.consultarPorPaisResultado, "POST", parametros, contextHandler);		
		contextHandler.AddAjaxv2Object(ai); 		
		ai.GetPost(true);
	}
	
	consultarPorPaisResultado(resultado)
	{
		var datos = JSON.parse(resultado);
		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
	}	
	
	
	
	

}