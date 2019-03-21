class MenuVista
{
	constructor(ventana)
	{	
		this.ventana = ventana;
		this.presentador = new MenuPresentador(this);
		
	}
	
	load()
	{
		$('#main-menu').bind('resize', function(){
			 $('.settings').css('background', function(){
		    	    var width = $('.main-menu').width();
		    	    if (width <= 55)
		    	   	 	return	"#87581c";
		    	    else
		    	    		return  "#FF0000";
		    	    		//return	"url( ../../assets/logo_hamburger.png)";
		    	      
		    	});
        });
		
		this.crearMenuContextual();
		var url = $("#frameContainer").attr("data-url");
		this.abrirUrl(url);
	}
	
	actualizarLogo()
	{
	      $('.settings').css('background', function(){
	    	    var width = $('.main-menu').width();
	    	    if (width <= 55)
	    	   	 	return	"#87581c";
	    	    else
	    	    		return  "#FF0000";
	    	    		//return	"url( ../../assets/logo_hamburger.png)";
	    	      
	    	});
	}
	
	abrirUrl(url)
	{
		if(url!=this.urlActual)
		{
			var iframe = $("#main_iframe");
			iframe.prop('src', url)
			this.urlActual = url;
		}
	}
	
	iniciarSesion()
	{
		this.ventana.location.replace("inicio_sesion.php");
	}
	
	
	cerrarSesion()
	{
		this.presentador.cerrarSesion();
	}
	cambiarFotoPerfil()
	{
		this.abrirUrl('cambiar_foto_perfil.php');
	}
	cambiarContrasena()
	{
		this.abrirUrl('cambiar_contrasena.php');
	}
	
	mostrarIndicador()
	{
		$('#indicador').show();				
	}
	
	crearMenuContextual()
	{
		//For example we are defining menu in object. You can also define it on Ul list. See on documentation.
		var menu = [{
		        name: 'cambiar foto de perfil',		       
		        icon: 'fa fa-user-circle fa-lg',
		        title: 'cambiar foto de perfil',
		        data: this,
		        fun: function (data) 
		        {
		        		data.data.cambiarFotoPerfil();
		        }
		    }, {
		        name: 'cambiar contraseña',
		        icon: 'fa fa-key fa-lg',
		        title: 'cambiar contraseña',
		        data: this,
		        fun: function (data) 
		        {
		        		data.data.cambiarContrasena();
		        }
		    }, {
		        name: 'cerrar sesión',
		        icon: 'fa fa-sign-out fa-lg',
		        title: 'cerrar sesión',
		        data: this,
		        fun: function (data) 
		        {
		        		data.data.cerrarSesion();
		        }
		        
		    } , {
		        name: 'cancelar',
		        icon: 'fa fa-times-circle fa-lg',
		        title: 'cancelar',
		        fun: function () {
		            
		        }
		    }];
		 
		//Calling context menu
		 $('.testButton').contextMenu(menu);
	}
}
var menuVista = new MenuVista(this);
