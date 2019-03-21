class CambiarFotoPerfilVista extends Vista
{		
	constructor(ventana)
	{	
		super();
		this.ventana = ventana;
		this.presentador = new CambiarFotoPerfilPresentador(this);
	}
	
	onLoad()
	{			
		
	}
	
	set fotoPerfil(fotoPerfil)
	{
		var d = new Date();
		$("#foto").attr('src',"php/fotos/" + fotoPerfil+"?"+d.getTime());
		
		if(parent!=null)
		{
			var img = parent.document.getElementById("imgFotoPerfilMenu");
			var $img = $(img);
			$img.attr('src',"php/fotos/" + fotoPerfil+"?"+d.getTime());
		}
		
	}
	
	cambiarImagen()
	{
		this.subirFotoPerfil();
		
	}
	
	subirFotoPerfil()
	{
		this.presentador.subirFotoPerfil();
	}
	
	get archivo()
	{
		var contenedorArchivos = $("#file") ;
		if(contenedorArchivos.length>0)
		{
			if(contenedorArchivos[0].files.length>0)
				return contenedorArchivos[0].files[0];
		}
		return null;
	}

	

	
}
var vista = new CambiarFotoPerfilVista(this);

