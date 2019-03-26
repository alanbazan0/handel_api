class TiposUsuarioRepositorio 
{	
	constructor()
	{
		this.servicio = "php/repositorios/TiposUsuario.php";
	}
	
	consultar(contexto,functionRetorno)
	{		
		this.contexto = contexto;
		this.functionRetorno = functionRetorno;
		
		var parametros;
		parametros = "accion=consultar";		
		
		var contextHandler = new AjaxContextHandler();
		var url = HANDEL_API + "/" + this.servicio;
		var ai = new Ajaxv2( url, this, this.consultarResultado, "POST", parametros, contextHandler);		
		contextHandler.AddAjaxv2Object(ai); 		
		ai.GetPost(true);
	}
	
	consultarResultado(resultado)
	{
		var datos = JSON.parse(resultado);
		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
	}	
}