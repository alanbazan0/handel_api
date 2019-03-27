class Vista
{
	constructor() 
	{
		this._usuario = null;
	}
	
	inicializar()
	{
		
	}
	
	set usuario(usuario)
	{
		this._usuario = usuario;
	}
	
	getFecha(fecha)
	{
		fecha = fecha.split('/').reverse().join('/');
		return fecha; 
	}
	
	mostrarIndicador()
	{
		$('#indicador').show();				
	}
	
	ocultarIndicador()
	{		
		$('#indicador').hide();
	}
	
	cambiarFotoPerfil()
	{
		$('#perfilFile').trigger('click');
	}
	
	
	
	
	getNewSubmitForm(url)
	{
		var form = document.createElement('form');
	    document.body.appendChild(form);
	    form.action = url;
	    form.method = "post";
	   // form.target = "_blank";
	    return form;
	}

	createNewFormElement(formInput, elementName, elementValue) 
	{
		var input = document.createElement('input');
		input.id = elementName;
		input.name = elementName;
		input.value = elementValue;
		input.style.display = 'none';
		formInput.appendChild(input);
		return input;
	}

	mostrarNotificacion(tipo, mensaje)
	{
		$.notify({
			// options
			icon: 'glyphicon glyphicon-warning-sign',
			title: "",
			message: mensaje,
			//url: 'https://github.com/mouse0270/bootstrap-notify',
			target: '_blank'
		},{
			// settings
			element: 'body',
			position: null,
			type: tipo,
			allow_dismiss: true,
			newest_on_top: false,
			showProgressbar: false,
			placement: {
				from: "bottom",
				align: "center"
			},
			offset: 20,
			spacing: 10,
			z_index: 9031,
			delay: 2000,
			timer: 1000,
			url_target: '_blank',
			mouse_over: null,
			animate: {
				enter: 'animated fadeInDown',
				exit: 'animated fadeOutUp'
			},
			onShow: null,
			onShown: null,
			onClose: null,
			onClosed: null,
			icon_type: 'class',
			template: '<div data-notify="container" class="col-xs-11 col-sm-3 alert alert-{0}" role="alert">' +
				'<button type="button" aria-hidden="true" class="close" data-notify="dismiss">×</button>' +
				'<span data-notify="icon"></span> ' +
				'<span data-notify="title">{1}</span> ' +
				'<span data-notify="message">{2}</span>' +
				'<div class="progress" data-notify="progressbar">' +
					'<div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
				'</div>' +
				'<a href="{3}" target="{4}" data-notify="url"></a>' +
			'</div>' 
		});
	}
	
	cerrarSesion()
	{
		this.mostrarIndicador();
		var repositorio = new UsuariosRepositorio(this);		
		 repositorio.cerrarSesion(this,this.cerrarSesionResultado);
	}
	
	 cerrarSesionResultado(resultado)
	 {
		 this.ocultarIndicador();
		 if(resultado.mensajeError=="")
		 {
			 window.location.replace("inicio_sesion.php");
		 }
	 }
	
	mostrarMensajeError(titulo, mensaje)
	{
		 toastr.error(mensaje,titulo,{
		        "positionClass": "toast-bottom-right",
		        timeOut: 5000,
		        "closeButton": true,
		        "debug": false,
		        "newestOnTop": true,
		        "progressBar": true,
		        "preventDuplicates": true,
		        "onclick": null,
		        "showDuration": "300",
		        "hideDuration": "1000",
		        "extendedTimeOut": "1000",
		        "showEasing": "swing",
		        "hideEasing": "linear",
		        "showMethod": "fadeIn",
		        "hideMethod": "fadeOut",
		        "tapToDismiss": false

		    });
	}
	
	mostrarMensaje(titulo, mensaje)
	{
		toastr.success(mensaje, titulo,{
	        "positionClass": "toast-bottom-right",
	        timeOut: 5000,
	        "closeButton": true,
	        "debug": false,
	        "newestOnTop": true,
	        "progressBar": true,
	        "preventDuplicates": true,
	        "onclick": null,
	        "showDuration": "300",
	        "hideDuration": "1000",
	        "extendedTimeOut": "1000",
	        "showEasing": "swing",
	        "hideEasing": "linear",
	        "showMethod": "fadeIn",
	        "hideMethod": "fadeOut",
	        "tapToDismiss": false

	    });
	}
	
	mostrarMensajeAdvertencia(mensaje,titulo)
	{
		 toastr.warning(mensaje,titulo,{
		        "positionClass": "toast-bottom-right",
		        timeOut: 5000,
		        "closeButton": true,
		        "debug": false,
		        "newestOnTop": true,
		        "progressBar": true,
		        "preventDuplicates": true,
		        "onclick": null,
		        "showDuration": "300",
		        "hideDuration": "1000",
		        "extendedTimeOut": "1000",
		        "showEasing": "swing",
		        "hideEasing": "linear",
		        "showMethod": "fadeIn",
		        "hideMethod": "fadeOut",
		        "tapToDismiss": false

		    });
	}
	
	get fotoPerfil()
	{
		var contenedorArchivos = $("#perfilFile") ;
		if(contenedorArchivos.length>0)
		{
			if(contenedorArchivos[0].files.length>0)
				return contenedorArchivos[0].files[0];
		}
		return null;
	}
	
	set fotoPerfil(fotoPerfil)
	{
		var fecha = new Date();
		$("#imgFotoPefil1").attr('src',"php/fotos/" + fotoPerfil+"?"+fecha.getTime());
		$("#imgFotoPefil2").attr('src',"php/fotos/" + fotoPerfil+"?"+fecha.getTime());
		
		$("#menuPerfil").removeClass("show-dropdown");
	}
	
	subirFotoPerfil()
	{
		this.mostrarIndicador();
		var repositorio = new UsuariosRepositorio(this);		
		repositorio.subirFotoPerfil(this,this.subirFotoPerfilResultado,this.fotoPerfil);
	}
	

	 subirFotoPerfilResultado(resultado)
	 {
		this.ocultarIndicador();	
		if(resultado.mensajeError=="")
		{
			this.fotoPerfil = resultado.valor;
		}
		else
			this.mostrarMensajeError("Error",resultado.mensajeError);
		
	 }
	
	 getLista(campo,registros)
	{
		var lista = [];
		for(var i=0; i< registros.length; i++)
		{
			var registro = registros[i];
			var valor = registro[campo];
			if(valor==undefined)
				valor = "";
			lista.push(valor);
		}
		return lista;
	}
	 
	 mostrarIndicadorGrafica(grafica)
		{
		 if(grafica)
			grafica.showLoading({
			    text : "",
			    effect : "spin",
			    textStyle : {
			        fontSize : 20
			    }
			});
		}
	
}