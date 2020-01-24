class DepartamentosRepositorio extends Repositorio
{	
	constructor()
	{
		super("php/repositorios/Departamentos.php");
	}
	
	consultarPorEmpresaSede(contexto,funcion, criteriosSeleccion,opcional)
	{		
		
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
           url: url,
           type: 'POST',
           data: {accion : "consultarPorEmpresaSede",criteriosSeleccion: JSON.stringify(criteriosSeleccion), opcional: opcional},
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