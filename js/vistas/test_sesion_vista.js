class TestSesionVista extends CatalogoVista	
{		
	constructor(ventana)
	{	
		super(ventana);
		
	}
	

	
}
var vista = new TestSesionVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});
