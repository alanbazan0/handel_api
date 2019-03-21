class IndicadoresRepositorio extends Repositorio
{	
	constructor()
	{
		super("php/repositorios/Indicadores.php");
	}
//	
//	consultarPorEmpresa(contexto,functionRetorno, empresaId)
//	{		
//		this.contexto = contexto;
//		this.functionRetorno = functionRetorno;
//		
//		var parametros;
//		parametros = "accion=consultarPorEmpresa";
//		parametros += "&empresaId=" + empresaId;		
//		
//		var contextHandler = new AjaxContextHandler();
//		var host = window.location.origin + "/" + CARPETA_PROYECTO;
//		var url = host + this.servicio;
//		var ai = new Ajaxv2(url, this, this.consultarPorEmpresaResultado, "POST", parametros, contextHandler);		
//		contextHandler.AddAjaxv2Object(ai); 		
//		ai.GetPost(true);
//	}
//	
//	consultarPorEmpresaResultado(resultado)
//	{
//		var datos = JSON.parse(resultado);
//		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
//	}	
}