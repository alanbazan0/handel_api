class PlantillasRepositorio extends Repositorio
{	
	constructor()
	{
		super("/php/repositorios/Plantillas.php");
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
}