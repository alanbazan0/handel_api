class Mensajes
{
	constructor(id)
	{
		this._id = id;
		this._columnas = [];
		this._mensajes = [];
	}
	
	set columnas(columnas)
	{
		this._columnas = columnas;
	}
	
	set mensajes(mensajes)
	{
		this._mensajes = mensajes;
		this.renderizar();
	}
	
	renderizar()
	{
		
		var html = "<table id='"+this._id+"Table' class='table table-hover table-striped'>" +
				"<tbody>";
		
		html+=this.renderizarMensajes();
		
		
		html+="</tbody>"+
				"</table>";
		
		$("#"+this._id).html(html);		
	}
	
	renderizarMensajes()
	{
		var html = "";
		for(var i=0 ; i < this._mensajes.length; i++)
		{
			var mensaje = this._mensajes[i];
			html+="<tr>";
			html+= this.renderizarColumnas(mensaje);
			html+="</tr>";
		}
	}
	
	renderizarColumnas(mensaje)
	{
		var html="<td><input type='checkbox'></td>" +
		"<td class='mailbox-star'><a href='#'><i class='fa fa-star text-yellow'></i></a></td>" +
		"<td class='mailbox-name'><a href='read-mail.html'>Alexander Pierce</a></td>" +
		"<td class='mailbox-subject'><b>AdminLTE 2.0 Issue</b> - Trying to find a solution to this problem..." +
		"</td>" +
		"<td class='mailbox-attachment'></td>" +
		"<td class='mailbox-date'>5 mins ago</td>";
		return html;
	}
	
}