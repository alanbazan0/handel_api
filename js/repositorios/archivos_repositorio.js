class ArchivosRepositorio extends Repositorio
{	
	constructor()
	{
		super("php/repositorios/Archivos.php");
	}
	
	eliminarArchivos(contexto, funcion, criteriosSeleccion)
	{
		var url = HANDEL_API + "/" + this.servicio;
		 $.ajax({
            url: url,
            type: 'POST',
            data: {accion : "eliminarArchivos", criteriosSeleccion: JSON.stringify(criteriosSeleccion)},
            success: function( data, textStatus, jQxhr )
            {
                funcion.call(contexto,data);
            },
            error: function( jqXhr, textStatus, errorThrown )
            {
            	if(textStatus=="parsererror")
        	   		funcion.call(contexto,{ mensajeError : jqXhr.responseText});
           		else
           			funcion.call(contexto,{ mensajeError : textStatus});
            },
            fail: function( jqXhr, textStatus, errorThrown )
            {
           	 funcion.call(contexto,{ mensajeError : textStatus});
            }
        });
	}
}