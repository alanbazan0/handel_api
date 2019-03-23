class EmpresasRepositorio extends Repositorio
{	
	constructor()
	{
		super("php/repositorios/Empresas.php");
	}
	
	insertar(contexto,funcionResultado, modelo, logo)
	{		
		var data = new FormData();
		data.append("accion", "insertar");
		data.append("modelo", JSON.stringify(modelo));
    	data.append("file", logo );
    	var url = HANDEL_API + "/" + this.servicio;
        var xhr = new XMLHttpRequest();
        xhr.open( 'POST', url, true );
		xhr.onreadystatechange = function ( resultado ) 
		{
		    if (this.readyState == 4 && this.status == 200) 
		    {
		    	var datos = JSON.parse(resultado.target.response);
		    	funcionResultado.call(contexto,datos);
		    }
		};
		xhr.send( data );  
	}
	
	actualizar(contexto,funcionResultado, modelo, logo)
	{		
		var data = new FormData();
		data.append("accion", "actualizar");
		data.append("modelo", JSON.stringify(modelo));
    	data.append("file", logo );
    	var url = HANDEL_API + "/" + this.servicio;
        var xhr = new XMLHttpRequest();
        xhr.open( 'POST',url, true );
		xhr.onreadystatechange = function ( resultado ) 
		{
		    if (this.readyState == 4 && this.status == 200) 
		    {
		    	var datos = JSON.parse(resultado.target.response);
		    	funcionResultado.call(contexto,datos);
		    }
		};
		xhr.send( data );  
	}
	
	

	consultarCorporativos(contexto,functionRetorno, empresaId)
	{		
		this.contexto = contexto;
		this.functionRetorno = functionRetorno;
		
		var parametros;
		parametros = "accion=consultarCorporativos";
		parametros += "&empresaId=" + empresaId;	
		
		
		var contextHandler = new AjaxContextHandler();
		var url = HANDEL_API + "/" + this.servicio;
		var ai = new Ajaxv2( url, this, this.consultarCorporativosResultado, "POST", parametros, contextHandler);		
		contextHandler.AddAjaxv2Object(ai); 		
		ai.GetPost(true);
	}
	
	consultarCorporativosResultado(resultado)
	{
		var datos = JSON.parse(resultado);
		this.functionRetorno.call(this.contexto,JSON.parse(resultado));
	}	
}