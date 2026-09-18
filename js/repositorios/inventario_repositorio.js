class InventarioRepositorio extends Repositorio
{
	constructor()
	{
		super("php/repositorios/Inspecciones.php");
	}

	consultarDentroInstalacion(contexto, funcion, criteriosSeleccion)
	{
		var criteriosSeleccionString = JSON.stringify(criteriosSeleccion);
		var url = HANDEL_API + "/" + this.servicio;
		$.ajax({
			url: url,
			type: 'POST',
			data: { accion: "consultarDentroInstalacion", criteriosSeleccion: criteriosSeleccionString },
			success: function(data, textStatus, jQxhr)
			{
				funcion.call(contexto, data);
			},
			error: function(jqXhr, textStatus, errorThrown)
			{
				funcion.call(contexto, { mensajeError: textStatus });
			},
			fail: function(jqXhr, textStatus, errorThrown)
			{
				funcion.call(contexto, { mensajeError: textStatus });
			}
		});
	}
}
