class CiudadesRepositorio extends Repositorio
{	
	
	constructor()
	{
		super("php/repositorios/Ciudades.php");
	}
	
	
	consultarPorPaisEstado(contexto,functionRetorno, paisId, estadoId)
	{		
		this.contexto = contexto;
		this.functionRetorno = functionRetorno;
		
		var parametros;
		parametros = "accion=consultarPorPaisEstado";
		parametros += "&paisId=" + paisId;	
		parametros += "&estadoId=" + estadoId;
		
		var contextHandler = new AjaxContextHandler();
//		var host = window.location.origin + "/" + CARPETA_PROYECTO;
//		var url = host + this.servicio;
		var ai = new Ajaxv2( this.servicio, this, this.consultarPorPaisEstadoResultado, "POST", parametros, contextHandler);		
		contextHandler.AddAjaxv2Object(ai); 		
		ai.GetPost(true);
	}
	
	consultarPorPaisEstadoResultado(resultado)
	{
		var datos = JSON.parse(resultado);
		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
	}	
	
	
	
	

}