class Tarjetas
{
	constructor(id)
	{
		this._id = id;
	
	}
	
	set plantillaHtml(plantillaHtml)
	{
		this._plantillaHtml = plantillaHtml;
	}
	
	set registros(registros)
	{
		this._registros = registros;
		if(this._registros!=null)
			this.renderizar();
	}
	
	renderizar()
	{
		var html = "<ul style='display: flex;  flex-wrap: wrap;  list-style: none;  margin: 0;  padding: 0;'>";
		for(var i=0; i < this._registros.length; i++)
		{
			var registro = this._registros[i];
			var plantilla = Handlebars.compile(this._plantillaHtml);
			html+="<li style='display: inline-block;    vertical-align: top;'>";
			//html+="<div class='box'>";
			html+= plantilla(registro);
			//html+="<div>";
			html+="</li>";
		
		}
		html+="</ul>";
		$("#"+this._id).html(html);
	}
	

}