class NotificacionesVista extends CatalogoVista
{		
	constructor(ventana)
	{	
		super();
		this.ventana = ventana;
		this.presentador = new NotificacionesPresentador(this);
		
		
	}
	
	 set notificaciones(notificaciones)
	 {
		//$("#leccionesReprobadasNumeroSpan").html(lecciones.length);
		//$("#leccionesReprobadasMensajeLi").html("Tienes " +lecciones.length +" notificaciones");
		
		var html = "";
		
		for(var i = 0; i < notificaciones.length; i++)
		{
			html += new Notificacion().renderizar( notificaciones[i]);
		}
		
		$("#cursosContestando").html(html);
		
	 }


	
	
}
var vista = new NotificacionesVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});
