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
//		var host = window.location.origin + "/" + CARPETA_PROYECTO;
//		var url = host + this.servicio;
		var ai = new Ajaxv2( this.servicio, this, this.consultarResultado, "POST", parametros, contextHandler);		
		contextHandler.AddAjaxv2Object(ai); 		
		ai.GetPost(true);
	}
	
	consultarResultado(resultado)
	{
		var datos = JSON.parse(resultado);
		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
	}	
}