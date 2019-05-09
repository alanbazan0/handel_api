class AuditoriasRepositorio extends Repositorio
{	
	constructor()
	{
		super("/php/repositorios/Auditorias.php");
	}
	
	consultarValoresSeccion(contexto,funcion, llaves)
	{		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
           url: url,
           type: 'POST',
           data: {accion : "consultarValoresSeccion",llaves: JSON.stringify(llaves)},
           success: function( data, textStatus, jQxhr )
           {
               funcion.call(contexto,data);
           },
           error: function( jqXhr, textStatus, errorThrown )
           {
          	 funcion.call(contexto,{ mensajeError : textStatus});
           },
           fail: function( jqXhr, textStatus, errorThrown )
           {
          	 funcion.call(contexto,{ mensajeError : textStatus});
           }
       });
	}
	
//	insertarAuditoria(contexto,functionRetorno,empresaId, plantillaId, seccionId, preguntas)
//	{		
//		this.contexto = contexto;
//		this.functionRetorno = functionRetorno;
//		
//		var parametros;
//		parametros = "accion=insertar";
//		parametros += "&empresaId=" + empresaId;	
//		parametros += "&plantillaId=" + plantillaId;	
//		parametros += "&seccionId=" + seccionId;	
//		parametros += "&preguntas=" + encodeURIComponent(JSON.stringify(preguntas));	
//		
//		var contextHandler = new AjaxContextHandler();
//		var host = window.location.origin + "/" + CARPETA_PROYECTO;	
//		var url = host + this.servicio;
//		var ai = new Ajaxv2(url, this, this.insertarAuditoriaResultado, "POST", parametros, contextHandler);		
//		contextHandler.AddAjaxv2Object(ai); 		
//		ai.GetPost(true);
//	}
//	
//	insertarAuditoriaResultado(resultado)
//	{
//		var datos = JSON.parse(resultado);
//		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
//	}
//	
//
//	actualizarAuditoria(contexto,functionRetorno, empresaId, plantillaId, seccionId, preguntas)
//	{		
//		this.contexto = contexto;
//		this.functionRetorno = functionRetorno;
//		
//		var parametros;
//		parametros = "accion=actualizaar";
//		parametros += "&empresaId=" + empresaId;	
//		parametros += "&plantillaId=" + plantillaId;	
//		parametros += "&seccionId=" + seccionId;	
//		parametros += "&preguntas=" + encodeURIComponent(JSON.stringify(preguntas));	
//		
//		var contextHandler = new AjaxContextHandler();
//		var host = window.location.origin + "/" + CARPETA_PROYECTO;	
//		var url = host + this.servicio;
//		var ai = new Ajaxv2(url, this, this.actualizarAuditoriaResultado, "POST", parametros, contextHandler);		
//		contextHandler.AddAjaxv2Object(ai); 		
//		ai.GetPost(true);
//	}
//	
//	actualizarAuditoriaResultado(resultado)
//	{
//		var datos = JSON.parse(resultado);
//		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
//	}
}