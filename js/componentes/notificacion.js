class Notificacion
{
	
	renderizar(registro)
	{
		
		var plantillaHtml = `<div  data-nombre="{{nombre}}" data-extension="{{extension}}" data-subida={{subida}} class="col-12 mb-4">
								<div class="box box-solid">
								   
								    <div class="box-body">
								   
								     <div class="pull-left" style='margin-right:10px;'>
								   		 <i class='fa fa-warning text-yellow'></i> 
				                        <img src="`+HANDEL_API+`/{{fotoPerfil}}?`+this.time+`" class="img-circle" style='width:50px;height:50px;' alt="">
				                      </div>
								       El usuario <strong>{{usuarioNombreCompleto}}</strong> no aprobó la lección <strong>{{leccionTitulo}}</strong> El usuario ya fue notificado, el intento borrado y puede tomar de nueva cuenta la lección.
								       <h4 class='pull-right'>
				                        <small><i class="fa fa-clock-o"></i>{{fecha}}</small>
				                      </h4> 
								    </div>
								</div>

	                        </div>
	                    </div>`;
	                    
	    	registro.fecha = this.calcularFechaRelativa(registro.fecha);
                            
			var plantilla = Handlebars.compile(plantillaHtml);
			var html = plantilla(registro);
			return html;
	}
	
	calcularFechaRelativa(fecha)
	{
		var m1 = moment(fecha,"YYYY-MM-DD hh:mm:ss");
		return m1.fromNow();
		//return "10 min";
	}
}