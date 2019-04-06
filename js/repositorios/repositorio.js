class Repositorio
{
	constructor(servicio) 
	{
	    this.servicio = servicio;
	}

	insertar(contexto,funcion, modelo)
	{		
//		this.contexto = contexto;
//		this.functionRetorno = functionRetorno;
//		
//		var parametros;
//		parametros = "accion=insertar";
//		parametros += "&modelo=" + encodeURIComponent(JSON.stringify(modelo));	
//		
//		var contextHandler = new AjaxContextHandler();
//		var url = HANDEL_API + "/" + this.servicio;
//		var ai = new Ajaxv2( url, this, this.insertarResultado, "POST", parametros, contextHandler);		
//		contextHandler.AddAjaxv2Object(ai); 		
//		ai.GetPost(true);
		
		var url = HANDEL_API + "/" + this.servicio;
		$.post(url, {accion : "insertar", modelo: encodeURIComponent(JSON.stringify(modelo))}, function(resultado) 
		{
			funcion.call(contexto,resultado);
		});
	}
	
//	insertarResultado(resultado)
//	{
//		var datos = JSON.parse(resultado);
//		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
//	}
	

	actualizar(contexto,funcion, modelo)
	{		
//		this.contexto = contexto;
//		this.functionRetorno = functionRetorno;
//		
//		var parametros;
//		parametros = "accion=actualizar";
//		parametros += "&modelo=" + encodeURIComponent(JSON.stringify(modelo));	
//		
//		var contextHandler = new AjaxContextHandler();
//		var url = HANDEL_API + "/" + this.servicio;
//		var ai = new Ajaxv2( url, this, this.actualizarResultado, "POST", parametros, contextHandler);		
//		contextHandler.AddAjaxv2Object(ai); 		
//		ai.GetPost(true);
		var url = HANDEL_API + "/" + this.servicio;
		$.post(url, {accion : "actualizar", modelo: encodeURIComponent(JSON.stringify(modelo))}, function(resultado) 
		{
			funcion.call(contexto,resultado);
		});
	}
	
//	actualizarResultado(resultado)
//	{
//		var datos = JSON.parse(resultado);
//		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
//	}
	
	consultar(contexto,funcion, criteriosSeleccion, opcional)
	{		
//		this.contexto = contexto;
//		this.functionRetorno = functionRetorno;
//		
//		var parametros;
//		parametros = "accion=consultar";
//		parametros += "&criteriosSeleccion=" + encodeURIComponent(JSON.stringify(criteriosSeleccion));		
//		parametros += "&opcional=" + opcional;	
//		
//		var contextHandler = new AjaxContextHandler();
//		var url = HANDEL_API + "/" + this.servicio;
//		var ai = new Ajaxv2( url, this, this.consultarResultado, "POST", parametros, contextHandler);		
//		contextHandler.AddAjaxv2Object(ai); 		
//		ai.GetPost(true);
		
		var url = HANDEL_API + "/" + this.servicio;
		$.post(url, {accion : "consultar", criteriosSeleccion: JSON.stringify(criteriosSeleccion), opcional: opcional}, function(resultado) 
		{
			funcion.call(contexto,resultado);
		}).fail(function(xhr, status, error) 
	    {
			var resultado = { mensajeError : error.message};
			funcion.call(contexto,resultado);
		});
	}
	
//	consultarResultado(resultado)
//	{
//		var datos = JSON.parse(resultado);
//		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
//	}	
//	
	consultarPorLlaves(contexto,funcion, llaves)
	{		
//		this.contexto = contexto;
//		this.functionRetorno = functionRetorno;
//		
//		var parametros;
//		parametros = "accion=consultarPorLlaves";
//		parametros += "&llaves=" + encodeURIComponent(JSON.stringify(llaves));
//		var contextHandler = new AjaxContextHandler();
//		var url = HANDEL_API + "/" + this.servicio;
//		var ai = new Ajaxv2( url, this, this.consultarPorLlavesResultado, "POST", parametros, contextHandler);		
//		contextHandler.AddAjaxv2Object(ai); 		
//		ai.GetPost(true);
		var url = HANDEL_API + "/" + this.servicio;
		$.post(url, {accion : "consultarPorLlaves", llaves: encodeURIComponent(JSON.stringify(llaves))}, function(resultado) 
		{
			funcion.call(contexto,resultado);
		});
	}
	
//	consultarPorLlavesResultado(resultado)
//	{
//		var datos = JSON.parse(resultado);
//		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
//	}
//	
	
	eliminar(contexto,funcion,llaves)
	{				
//		this.contexto = contexto;
//		this.functionRetorno = functionRetorno;
//		
//		var parametros;
//		parametros = "accion=eliminar";
//		parametros += "&llaves=" + encodeURIComponent(JSON.stringify(llaves));
//		var contextHandler = new AjaxContextHandler();
//		var url = HANDEL_API + "/" + this.servicio;
//		var ai = new Ajaxv2( url, this, this.eliminarResultado, "POST", parametros, contextHandler);		
//		contextHandler.AddAjaxv2Object(ai); 		
//		ai.GetPost(true);
		var url = HANDEL_API + "/" + this.servicio;
		$.post(url, {accion : "eliminar", llaves: encodeURIComponent(JSON.stringify(llaves))}, function(resultado) 
		{
			funcion.call(contexto,resultado);
		});
	}
//	
//	eliminarResultado(resultado)
//	{
//		var datos = JSON.parse(resultado);
//		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
//	}
	
}