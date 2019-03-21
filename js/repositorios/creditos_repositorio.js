class PuestosRepositorio extends Repositorio
{	
	constructor()
	{
		super("php/repositorios/Creditos.php");
	}
	
	consultarPorEmpresaSede(contexto,functionRetorno, empresaId, sedeId)
	{		
		this.contexto = contexto;
		this.functionRetorno = functionRetorno;
		
		var parametros;
		parametros = "accion=consultarPorEmpresaSede";
		parametros += "&empresaId=" + empresaId;		
		parametros += "&sedeId=" + sedeId;		
		
		var contextHandler = new AjaxContextHandler();
//		var host = window.location.origin + "/" + CARPETA_PROYECTO;
//		var url = host + this.servicio;
		var ai = new Ajaxv2( this.servicio, this, this.consultarPorEmpresaSedeResultado, "POST", parametros, contextHandler);		
		contextHandler.AddAjaxv2Object(ai); 		
		ai.GetPost(true);
	}
	
	consultarPorEmpresaSedeResultado(resultado)
	{
		var datos = JSON.parse(resultado);
		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
	}	
	
}