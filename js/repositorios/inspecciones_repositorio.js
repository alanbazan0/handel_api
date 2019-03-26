class InspeccionesRepositorio extends Repositorio
{	
	constructor()
	{
		super("php/repositorios/Inspecciones.php");
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
		var url = HANDEL_API + "/" + this.servicio;
		var ai = new Ajaxv2(url, this, this.consultarPorEmpresaSedeResultado, "POST", parametros, contextHandler);		
		contextHandler.AddAjaxv2Object(ai); 		
		ai.GetPost(true);
	}
	
	consultarPorEmpresaSedeResultado(resultado)
	{
		var datos = JSON.parse(resultado);
		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
	}	
	
	consultarInspeccionesEmpresa(contexto,functionRetorno)
	{		
		this.contexto = contexto;
		this.functionRetorno = functionRetorno;
		
		var parametros;
		parametros = "accion=consultarInspeccionesEmpresa";
		
		var contextHandler = new AjaxContextHandler();
		var url = HANDEL_API + "/" + this.servicio;
		var ai = new Ajaxv2(url, this, this.consultarInspeccionesEmpresaResultado, "POST", parametros, contextHandler);		
		contextHandler.AddAjaxv2Object(ai); 		
		ai.GetPost(true);
	}
	
	consultarInspeccionesEmpresaResultado(resultado)
	{
		var datos = JSON.parse(resultado);
		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
	}	
	
	consultarInspeccionesMes(contexto,functionRetorno)
	{		
		this.contexto = contexto;
		this.functionRetorno = functionRetorno;
		
		var parametros;
		parametros = "accion=consultarInspeccionesMes";
		
		var contextHandler = new AjaxContextHandler();
		var url = HANDEL_API + "/" + this.servicio;
		var ai = new Ajaxv2(url, this, this.consultarInspeccionesMesResultado, "POST", parametros, contextHandler);		
		contextHandler.AddAjaxv2Object(ai); 		
		ai.GetPost(true);
	}
	
	consultarInspeccionesMesResultado(resultado)
	{
		var datos = JSON.parse(resultado);
		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
	}	
	
	consultarInspeccionesSede(contexto,functionRetorno)
	{		
		this.contexto = contexto;
		this.functionRetorno = functionRetorno;
		
		var parametros;
		parametros = "accion=consultarInspeccionesSede";
		
		var contextHandler = new AjaxContextHandler();
		var url = HANDEL_API + "/" + this.servicio;
		var ai = new Ajaxv2(url, this, this.consultarInspeccionesSedeResultado, "POST", parametros, contextHandler);		
		contextHandler.AddAjaxv2Object(ai); 		
		ai.GetPost(true);
	}
	
	consultarInspeccionesSedeResultado(resultado)
	{
		var datos = JSON.parse(resultado);
		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
	}	
	
}