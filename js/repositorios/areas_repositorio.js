class AreasRepositorio extends Repositorio
{	
	
	constructor()
	{
		super("php/repositorios/Areas.php");
	}
	
	
	consultarPorEmpresa(contexto,functionRetorno, empresaId)
	{		
		this.contexto = contexto;
		this.functionRetorno = functionRetorno;
		
		var parametros;
		parametros = "accion=consultarPorEmpresa";
		parametros += "&empresaId=" + empresaId;		
		
		var contextHandler = new AjaxContextHandler();
//		var host = window.location.origin + "/" + CARPETA_PROYECTO;
//		var url = host + this.servicio;
		var ai = new Ajaxv2( this.servicio, this, this.consultarPorEmpresaResultado, "POST", parametros, contextHandler);		
		contextHandler.AddAjaxv2Object(ai); 		
		ai.GetPost(true);
	}
	
	consultarPorEmpresaResultado(resultado)
	{
		var datos = JSON.parse(resultado);
		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
	}	
	
	consultarPorEmpresaSede(contexto,functionRetorno, empresaId, sedeId, opcional)
	{		
		this.contexto = contexto;
		this.functionRetorno = functionRetorno;
		
		var parametros;
		parametros = "accion=consultarPorEmpresaSede";
		parametros += "&empresaId=" + empresaId;		
		parametros += "&sedeId=" + sedeId;		
		parametros += "&opcional=" + opcional;	
		
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