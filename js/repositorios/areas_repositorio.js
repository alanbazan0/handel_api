class AreasRepositorio extends Repositorio
{	
	
	constructor()
	{
		super("php/repositorios/Areas.php");
	}
	
	
	consultarPorEmpresa(contexto,funcion, empresaId)
	{		

		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
            url: url,
            type: 'POST',
            data: {accion : "consultarPorEmpresa",empresaId : empresaId },
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
	

	consultarPorEmpresaSede(contexto,funcion, empresaId, sedeId, opcional)
	{		
		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
           url: url,
           type: 'POST',
           data: {accion : "consultarPorEmpresaSede",empresaId : empresaId, sedeId: sedeId, opcional : opcional},
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
	
	
	
	
	

}