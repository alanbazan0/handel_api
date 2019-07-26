class EvidenciasRepositorio extends Repositorio
{	
	constructor()
	{
		super("php/repositorios/Evidencias.php");
	}
	
	insertar(contexto,funcionResultado, modelo, fotoEvidencia)
	{		
		var data = new FormData();
		data.append("accion", "insertar");
		data.append("modelo", JSON.stringify(modelo));
    	data.append("file", fotoEvidencia );
    	var url = HANDEL_API + "/" + this.servicio;
        var xhr = new XMLHttpRequest();
        xhr.open( 'POST', url, true );
		xhr.onreadystatechange = function ( resultado ) 
		{
		    if (this.readyState == 4 && this.status == 200) 
		    {
		    	var datos = null;
		    	try 
		    	{
		    		datos = JSON.parse(resultado.target.response);
		    		funcionResultado.call(contexto,datos);
				} 
		    	catch (e) 
				{
		    		datos = new Object();
		    		datos.mensajeError = resultado.target.response;
		    		funcionResultado.call(contexto,datos);
				}
		    
		    	
		    }
		};
		xhr.send( data );  
	}
}