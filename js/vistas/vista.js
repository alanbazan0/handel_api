class Vista
{
	constructor() 
	{
		this._usuario = null;
	}
	
	inicializar()
	{
		$("body").data("_this",this);
		if($("#enviarMensajeLink").length>0)
			$("#enviarMensajeLink").click(this.enviarMensajeLinkClick);
		this.toastr = null;
		this.toastrData = null;
		this.actualizarSesion();	
		
		//this.inicializarSesion();
		this.inicializarMoment();




	}
	
	inicializarMoment()
	{
		try
		{
			moment.updateLocale('es', {
			    relativeTime : {
			        future: "in %s",
			        past:   "%s",
			        s: function (number, withoutSuffix, key, isFuture){
			            return '00:' + (number<10 ? '0':'') + number + ' min';
			        },
			        m:  "1 min",
			        mm: function (number, withoutSuffix, key, isFuture){
			            return (number<10 ? '0':'') + number + ' min';
			        },
			        h:  "1 hr",
			        hh: "%d hr",
			        d:  "1 d",
			        dd: "%d d",
			        M:  "a m",
			        MM: "%d m",
			        y:  "1 a",
			        yy: "%d a"
			    }
			});
		}
		catch(e)
		{
			
		}
	}
	
	actualizarSesion()
		{
			var _this = this;
			var minutos = 5;
			var time = minutos * 60000;
			 setTimeout(
			        function ()
			        {
			        $.ajax({
			           url:  HANDEL_API  + '/php/actualizar_sesion.php',
			           cache: false,
			           complete: function (respuesta) 
			           {
			        	   _this.actualizarSesion();
			           }
			        });
			    },
			    time
			);
		}
	
	/*inicializarSesion()
	{
		var segundosVerificacion = 60;
		this._tiempoVerificacion = 1000 * segundosVerificacion; // ping every 60 seconds
		this._tiempoAdvertencia = 120; // warning at 2 mins left
		
		$(document) // various events that can reset idle time
			.on("mousemove", this.actualizarSesion)
			    .on("click", this.actualizarSesion)
			    .on("keydown", this.actualizarSesion)
			    .children("body")
			    .on("scroll", this.actualizarSesion);

		this.iniciarVerificacion();
		
			
	}*/
	/*
	iniciarVerificacion()
	{
		//this.detenerConteo();
		if(vista._verificacionInterval==null)
			vista._verificacionInterval =setInterval(vista.verificarSesion, vista._tiempoVerificacion);
	}
	
	detenerVerificacion()
	{
		if(vista._verificacionInterval!=null)
		{
			clearInterval(vista._verificacionInterval); 
			vista._verificacionInterva = null;
		}
	}
	*/
	
	/*
	verificarSesion() 
	{
		console.log("Verificando sesion");
		  $.ajax({
	       url:  HANDEL_API  + '/php/verificar_sesion.php',
	       cache: false,
	       complete: function (respuesta) 
	       {
			
				vista.tiempoSesion = respuesta.responseText;
	           	if(vista.tiempoSesion>0 && vista.tiempoSesion<=vista._tiempoAdvertencia)
				{
					vista.detenerVerificacion();
					vista.mostrarAdvertenciaSesion();
					vista.iniciarConteo()
					console.log("Sesion activa. Tiempo restante." + vista.tiempoSesion );
				}
	   		 	else if(vista.tiempoSesion=="")
				{
					vista.detenerVerificacion();
					vista.salirSesionCaducada();
					
				}
				else if(vista.tiempoSesion<=0)
				{  
					vista.detenerVerificacion();
					vista.salirSesionCaducada();
				}
				else if(vista.tiempoSesion>vista._tiempoAdvertencia)
				{
					console.log("Sesion activa. Tiempo restante." + vista.tiempoSesion );
				}
	       }
		});

   
	}
*/
/*
 mostrarAdvertenciaSesion() {
	
	
	vista.tiempoRestante = vista._tiempoAdvertencia;
	swal({
            title: "Advertencia",
			html: true,
            text: "La sesión caducara en <span id='tiempoSesionSpan'>" +vista.convertirMMSS(vista.tiempoRestante) + "</span>,¿Desea continuar?",
            type: "warning",
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Continuar",
            closeOnConfirm: true,
            showLoaderOnConfirm: true,
        },
        function(isConfirm)
        {
            if (isConfirm) 
            {
            	vista.actualizarSesion();
		 		vista.detenerConteo();
				
            }
			else
				vista.cerrarSesion();
        });

		

	}
	
	iniciarConteo()
	{
		if(vista._conteoInterval==null)
		{
			vista._conteoInterval = setInterval(function()
			{
				if(vista.tiempoRestante>0)
				{
					vista.tiempoRestante--;
					var tiempo = vista.convertirMMSS(vista.tiempoRestante);
					$("#tiempoSesionSpan").html(tiempo);
				}
				else 
				{
					//vista.detenerConteo();
					vista.cerrarSesion();
				}
			}, 1000);
		}
	}

	detenerConteo()
	{
		if(vista._conteoInterval!=null)
		{
			clearInterval(vista._conteoInterval);
			vista._conteoInterval = null;
		}
	}
*/
	/*actualizarSesion() 
	{
	  	$.ajax({
		       url:  HANDEL_API  + '/php/actualizar_sesion.php',
		       cache: false,
		       complete: function (respuesta) 
		       {
					vista.tiempoSesion = respuesta.responseText;
			   		vista.iniciarVerificacion();
					console.log("Sesión reiniciada. Tiempo restante.." +vista.tiempoSesion );
		       }
		    });
	}*/
	/*
		actualizarSesion()
		{
			var _this = this;
			 $.ajax({
	           url:  HANDEL_API  + '/php/actualizar_sesion.php',
	           cache: false,
	           complete: function (respuesta) 
	           {
	        	   	_this.iniciarVerificacion();
					_this.detenerConteo();
	           }	
	        });
		}
		*/
	salir()
	{
		var _this = this;
		swal({
	            title: "\u00bfEst\u00E1 seguro de salir?",
	            text: "Se perderan los cambios no guardados !!",
	            type: "warning",
	            showCancelButton: true,
	            confirmButtonColor: "#DD6B55",
	            confirmButtonText: "Si, Salir!!",
	            cancelButtonText: "Cancelar",
	            closeOnConfirm: false,
	            closeOnCancel: true,
	            showLoaderOnConfirm: true,
	        },
	        function(isConfirm)
	        {
	            if (isConfirm) 
	            {
	            	 setTimeout(function(){
	            		window.close();
	 	            }, 1000);
	            }
				//else
				
				//	vista.actualizarSesion();
	        });
			
			
	}
	
	cerrar()
	{
		 setTimeout(function(){
			window.close();
	    }, 1000);
	}
	
	salirSesionCaducada()
	{
		console.log("Sesion caducada.");
		 //vista.detenerVerificacion();
		//vista.detenerConteo();
		/*swal({
	            title: "Sesión caducada",
	            text: "Inicie sesión",
	            type: "error",
	            confirmButtonColor: "#DD6B55",
	            confirmButtonText: "Aceptar",
	            closeOnConfirm: true,
	            showLoaderOnConfirm: true,
	        },
	        function(isConfirm)
	        {
			
				vista.cerrarSesion();
	        });*/

		vista.cerrarSesion();
			
	}
	
	set usuario(usuario)
	{
		this._usuario = usuario;
	}
	
	get usuario()
	{
		var json = $("body").attr("data-usuario");
		var usuario = JSON.parse(json);
		return usuario;
		
	}
	
	getFecha(fecha)
	{
		fecha = fecha.split('/').reverse().join('/');
		return fecha; 
	}
	
	getFechaYMD(fecha)
	{
		fecha = fecha.split('/').reverse().join('-');
		//fecha = fecha.replace("/","-");
		return fecha; 
	}
	
	mostrarIndicador()
	{
		$('#indicador').css('z-index', 10000);	
		$('#indicador').show();				
	}
	
	set cargando(cargando)
	{
		this._cargando = cargando;
		if(cargando)
			this.mostrarIndicador();
		else
			this.ocultarIndicador();
	}
	
	get cargando()
	{
		return this._cargando;
	}
	
	ocultarIndicador()
	{		
		$('#indicador').hide();
	}
	
	cambiarFotoPerfil()
	{
		$('#perfilFile').trigger('click');
	}
	
	
	
	
	getNewSubmitForm(url, method)
	{
		var form = document.createElement('form');
	    document.body.appendChild(form);
	    form.action = url;
	    if(method!=undefined)
	    	form.method = "get";
	    else
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
	
	mostrarMensajeError(titulo, mensaje, codigoError)
	{
		if(codigoError==5000)
		{
			 var _this = this;
		
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
				 setTimeout(() => 
				{
					_this.salirSesionCaducada();
				}, 3000);
		}
		else
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
	
	mostrarMensajeAdvertencia(titulo,mensaje,data)
	{
		this.toastr = toastr.warning(mensaje,titulo,{
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
		        "tapToDismiss": false,
		        onclick: this.clickNotificacion,
		        data : data

		    });
		this.toastrData = data;
	}
	
	clickNotificacion(event)
	{
		
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
		$("#imgFotoPefil1").attr('src',HANDEL_API+ "/php/fotos/" + fotoPerfil+"?"+fecha.getTime());
		$("#imgFotoPefil2").attr('src',HANDEL_API+"/php/fotos/" + fotoPerfil+"?"+fecha.getTime());
		$("#imgFotoPefil3").attr('src',HANDEL_API+"/php/fotos/" + fotoPerfil+"?"+fecha.getTime());
		
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
	 
	 
		mostrarEnviarMensaje()
		{
			this._mensajeAreaId = "";
			if($("#modalAlta").length ==0)
			{
				var url = HANDEL_API + "/html/modales/enviar_mensaje.php";
				this.mostrarIndicador();
				var _this = this;
				$.post(url,{}, function(html) 
				{
					_this.ocultarIndicador();
					$("body").append(html);
					//$("#mensajeInput").wysihtml5();
					$('#mensajeInput').wysihtml5({
						  toolbar: {
						    "font-styles": true, // Font styling, e.g. h1, h2, etc.
						    "emphasis": true, // Italics, bold, etc.
						    "lists": true, // (Un)ordered lists, e.g. Bullets, Numbers.
						    "html": false, // Button which allows you to edit the generated HTML.
						    "link": false, // Button to insert a link.
						    "image": false, // Button to insert an image.
						    "color": false, // Button to change color of font
						    "blockquote": true, // Blockquote
						    "size": "sm" // options are xs, sm, lg
						  }
						});
					$("#modalAlta").on("hidden.bs.modal", function () {
						$("#modalAlta").remove();
					});
					
					$("#modalAlta").on("show.bs.modal", function () 
					{
						_this.inicializarValidacionesMensaje();
						$("#guardarButton").click(function () 
						{
							 $("#formulario").submit();
						});
						$("#mensajeInput").keypress(function(event){
						    var keycode = (event.keyCode ? event.keyCode : event.which);
						    if(keycode == '13')
						    {
						    	 $("#formulario").submit();
						    }
						});
						
						_this.consultarEmpresasMensaje();
					
						
					});
				
					
				
					
				
					
					$("#modalAlta").modal({backdrop: 'static', keyboard: false});
				});
			}
			else
			{
				$("#modalAlta").modal({backdrop: 'static', keyboard: false});
			}
		}
		
		inicializarValidacionesMensaje()
		{
			var _this = this;
			jQuery("#formulario").validate({
	            ignore: [],
	            errorClass: "invalid-feedback animated fadeInDown",
	            errorElement: "div",
	            errorPlacement: function(e, a) {
	                jQuery(a).parents(".form-group > div").append(e)
	            },
	            highlight: function(e) {
	                jQuery(e).closest(".form-group").removeClass("is-invalid").addClass("is-invalid")
	            },
	            success: function(e) {
	                jQuery(e).closest(".form-group").removeClass("is-invalid"), jQuery(e).remove()
	            },
	            rules: {
	                "asuntoInput": {
	                    required: !0
	                },
	                "mensajeInput": {
	                    required: !0
	                }
	            },
	            messages: {
	                "asuntoInput": "Por favor ingrese un asunto",
	                "mensajeInput": "Por favor ingrese un mensaje"
	               
	                	
	                
	            },
	            submitHandler:function (form) {
	            	 _this.enviarMensaje();
	            }
	        });
		}
		
		
		enviarMensajeLinkClick(event)
		{
			var _this = $("body").data("_this");
			_this.mostrarEnviarMensaje();
		}
		
		enviarMensaje()
		{
			
			this.presentador.enviarMensaje();
			$("#mensajeInput").val("");
		}
		
		get modeloMensaje()
		{
			var modelo =
			{
				empresaId : $("#empresaSelectMensaje").val(),
				sedeId : $("#sedeSelectMensaje").val(),
				departamentoId : $("#departamentoSelectMensaje").val(),
				usuarioId : $("#usuarioSelectMensaje").val(),
				asunto: $("#asuntoInput").val(),
				mensaje: $("#mensajeInput").val()
			};
			return modelo;
		}
		
		mensajeEnviado()
		{
			$("#modalAlta").modal('hide');
			this.mostrarMensaje("","El mensaje fue enviado.")
		}
		
		consultarEmpresasMensaje()
		{
			this.cargandoOpciones("#empresaSelectMensaje");
			this.cargandoOpciones("#sedeSelectMensaje");
			this.cargandoOpciones("#usuarioSelect");
			this.presentador.consultarEmpresasMensaje();
		}
		
		consultarDepartamentosMensaje()
		{
			this.cargandoOpciones("#departamentoSelectMensaje");
			this.presentador.consultarDepartamentosMensaje();
		}
		
		set empresasMensaje(registros)
		{		
			this.cargarOpciones('#empresaSelectMensaje', registros);
		}
		
		set departamentosMensaje(registros)
		{		
			this.cargarOpciones('#departamentoSelectMensaje', registros);
		}
		
		set sedesMensaje(registros)
		{		
			this.cargarOpciones('#sedeSelectMensaje', registros);
		}
		
		set areasMensaje(registros)
		{		
			this.cargarOpciones('#areaSelectMensaje', registros);
			if(this._mensajeAreaId=="")
			{
				if(this.usuario.tipoUsuarioId == TipoUsuario.SUPERVISOR)
				{
					this._mensajeAreaId = this.usuario.areaId;
					$('#areaSelectMensaje').val(this._mensajeAreaId);
				}
			}
		}
		
		set usuariosMensaje(registros)
		{		
			this.cargarOpciones('#usuarioSelectMensaje', registros, null, null, null, null,  "nombreCompleto");
		}
		
		cambiarEmpresaMensaje()
		{
			this.cargandoOpciones("#sedeSelectMensaje");
			this.cargandoOpciones("#areaSelectMensaje");
			this.cargandoOpciones("#usuarioSelectMensaje");
			this.consultarSedesMensaje();
		}
		
		cambiarDepartamentoMensaje()
		{
			this.cargandoOpciones("#usuarioSelectMensaje");
			this.consultarUsuariosMensaje();
		}
		
		cambiarSedeMensaje()
		{
			this.cargandoOpciones("#departamentoSelectMensaje");
			this.cargandoOpciones("#usuarioSelectMensaje");
			this.consultarDepartamentosMensaje();
			//this.consultarAreasMensaje();
		}
		
		cambiarAreaMensaje()
		{
			this.cargandoOpciones("#usuarioSelectMensaje");
			this.consultarUsuariosMensaje();
		}
		
		
		consultarSedesMensaje()
		{
			this.cargandoOpciones("#sedeSelectMensaje");
			this.presentador.consultarSedesMensaje();
		}
		
		consultarAreasMensaje()
		{
			this.cargandoOpciones("#areaSelectMensaje");
			this.presentador.consultarAreasMensaje();
		}
		
		consultarUsuariosMensaje()
		{
			this.cargandoOpciones("#usuariosSelectMensaje");
			this.presentador.consultarUsuariosMensaje();
		}
		
		get criteriosSeleccionMensaje()
		{
			 var criteriosSeleccion = 
			 {				    
				empresaId: $('#empresaSelectMensaje').val(),
				sedeId: $('#sedeSelectMensaje').val(),
				departamentoId: $('#departamentoSelectMensaje').val(),
				usuarioId: $('#usuarioSelectMensaje').val()
			 }
			 return criteriosSeleccion;
		}		
		
		buscarPorValor(arreglo, propiedad, valor)
		{
			var indice = -1;
			for(var i=0; i< arreglo.length; i++)
			{
				var elemento = arreglo[i];
				var valorElemento = elemento[propiedad];
				if(valorElemento==valor)
				{
					return elemento;
				}
			}
			return null;
		}
		
		mostrarFormularioHTML(url,contexto,funcionConsultarPorLlaves, functionConsultarCombos,functionInicializarValidacionesHTML,id, formulario, guardarButton, funcionGuardar, funcionCerrar)
		{
			var modal = id;
			if(modal==undefined || modal=="")
				modal = "modalAlta";
			if(formulario==undefined || modal =="")
				formulario="formulario";
			if(guardarButton==undefined || guardarButton=="")
				guardarButton="guardarButton";
			if($("#"+modal).length ==0)
			{
				this.renderizarFormularioHTML(url,contexto,funcionConsultarPorLlaves, functionConsultarCombos,functionInicializarValidacionesHTML,modal,formulario, guardarButton,funcionGuardar,funcionCerrar);
			}
			else
			{
				$("#"+modal).modal({backdrop: 'static', keyboard: false});
				
			}
		}
		
		renderizarFormularioHTML(url,contexto,funcionConsultarPorLlaves, functionConsultarCombos,functionInicializarValidacionesHTML,modal,formulario,guardarButton, funcionGuardar, funcionCerrar)
		{
			//var url = this._urlFormulario;
			this.mostrarIndicador();
			var _this = this;
			$.post(url,{}, function(html) 
			{
				_this.ocultarIndicador();
				$("body").append(html);
				$("#"+modal).on("hidden.bs.modal", function () {
					$("#"+modal).remove();
					if(funcionCerrar!=null)
						funcionCerrar.call(contexto);
				});
				
				$("#"+modal).on("show.bs.modal", function () {
					
					if(_this.modo != Modo.ALTA)
					{	
						if(funcionConsultarPorLlaves!=null)
							funcionConsultarPorLlaves.call(contexto);
					}
					if(functionConsultarCombos!=null)
						functionConsultarCombos.call(contexto);
				});
			
				
				if(functionInicializarValidacionesHTML!=null)
					functionInicializarValidacionesHTML.call(contexto);
				
				
				$("#"+guardarButton).click(function () {
					if(funcionGuardar!=null)
						funcionGuardar.call(contexto);
				});
				
				$("#"+modal).modal({backdrop: 'static', keyboard: false});
			});
		}
		
		cargandoOpciones(select)
		{
			$(select).empty();
			$(select).append('<option value="">Cargando...</option>');

		}
		
		cargarOpciones(select, registros, modo, modeloEdicion, campo, texto, campoNombre,asignarData)
		{
			$(select).empty();
			if(texto!=null)
			{
				if(texto=="")
					$(select).append($('<option></option>').val("").html("-Seleccione"));
				else 
					$(select).append($('<option></option>').val("").html(texto));
			}
			
			var id = select.replace("#","");
			$.each(registros, function(i, p) 
			{
				var nombre = p.nombre;
				if(campoNombre!=undefined)
					nombre = p[campoNombre];
				
			
				
				 $(select).append($("<option id='"+id+"option"+p.id+"'></option>").val(p.id).html(nombre));
				 
				 if(asignarData)
					 $("#"+id+"option"+p.id).data("data",p);
				 
			});
			if(modo==Modo.CAMBIO && modeloEdicion!=null)
			{
				var id = modeloEdicion[campo];
				$(select).val(id);
				var selected = $(select +" option[value='"+id+"']");
				if(selected.length==0)
				{	
					if(texto=="")
					{
						$(select).prop('selectedIndex',0);
					}
						
				}
				
			}
		}
		
	
		get aplicacionId()
		{
			return $("body").attr("data-aplicacionId");
		}
		
		get aplicacionVersion()
		{
			return $("body").attr("data-aplicacionVersion");
		}
		
		getFechaMDA(fecha)
		{
			var elementos = fecha.split(" ");
			if(elementos.length==2)
			{
				var fecha = elementos[0];
				var hora = elementos[1];
				elementos = fecha.split("/");
				if(elementos.length==3)
				{
					var dia = elementos[0];
					var mes = elementos[1];
					var ano = elementos[2];
					
					var f = mes + "/" + dia + "/" + ano + " " + hora;
					return f;
					
				}
			}
			return "";
		}
		
		habilitarExportacionExcel()
		{
			var _this = this;
			var buttonCommon = {
			   text:      '<i class="fa fa-file-excel-o"></i> Exportar',
		        exportOptions: {
		        	 modifier: {
	                        selected: null
	                    },
		            format: {
		                body: function ( data, row, column, node ) 
		                {
		                	if(column==ArrayUtils.indexWithValues("alias",["logo"],_this.tabla.columnas))
		                	{
		                		return "";
		                	} 
		                	else if(column==ArrayUtils.indexWithValues("alias",["estatus"],_this.tabla.columnas))
		                	{
	                		  if(data.includes("fa-check"))
		                		   return "Activo";
		                	   else
		                		   return "Inactivo";
		                	}
		                	else if(node.innerHTML.includes("button"))
		                		return "";
							else if(node.innerHTML.includes("<label>"))
								return "";
		                	return data;
		                 
		                }
		            }
		        }
		    };
		 
		


		 this.tabla.botones =  {
			      buttons: [
			    	  $.extend( true, {}, buttonCommon, {
			                extend: 'excel',"className": 'btn btn-success' 
			            } ),
			               ],
			       dom: {
					  button: {
					  className: 'btn'
				         }
			       }
		 };
		}
		
		convertirMMSS(valor)
		{
			var sec_num = parseInt(valor); // don't forget the second param
		    var hours   = Math.floor(sec_num / 3600);
		    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
		    var seconds = sec_num - (hours * 3600) - (minutes * 60);
		
		    if (hours   < 10) {hours   = "0"+hours;}
		    if (minutes < 10) {minutes = "0"+minutes;}
		    if (seconds < 10) {seconds = "0"+seconds;}
		    return minutes+':'+seconds;
		}
		
		cerrarConfirmacionEliminar()
		{
			swal.close();
		}
		
		get tema()
		{
			var style = getComputedStyle(document.body);
			var theme = {};
			
			theme.primary = style.getPropertyValue('--primary');
			theme.secondary = style.getPropertyValue('--secondary');
			theme.success = style.getPropertyValue('--success');
			theme.info = style.getPropertyValue('--info');
			theme.warning = style.getPropertyValue('--warning');
			theme.danger = style.getPropertyValue('--danger');
			theme.light = style.getPropertyValue('--light');
			theme.dark = style.getPropertyValue('--dark');	
			
			return theme;
		}
		
		cerrarModal(modal)
		{
			$("#"+modal).modal("hide");
		}
		
		mostrarDocumentos()
		{
			if(this.usuario.urlDocumentos!="" && this.usuario.urlDocumentos!=undefined)
			{
				var _this = this;
				swal({
			            title: "Advertencia",
			            text: "Estas a punto de acceder a una nube segura en la que se encuentra tu información, se abrirá en otra ventana de tu navegador dejando a SAHA abierto. \n\n Recuerda que esta información es solo para uso personal y no debes compartir con personal no autorizado, al hacer clic en aceptar te comprometes al buen uso de esta información",
			            type: "warning",
			            showCancelButton: true,
			            confirmButtonColor: "#DD6B55",
			            confirmButtonText: "Si, Acceder",
			            cancelButtonText: "Cancelar",
			            closeOnConfirm: true,
			            closeOnCancel: true,
			            showLoaderOnConfirm: true,
			        },
			        function(isConfirm)
			        {
			            if (isConfirm) 
			            {
			            	 setTimeout(function(){
			            		var submitForm = _this.getNewSubmitForm(_this.usuario.urlDocumentos,"get");
							    submitForm.target= "_blank";
							    submitForm.submit();
			 	            }, 500);
			            }
						//else
						
						//	vista.actualizarSesion();
			        });
				
			}
			else
				this.mostrarMensaje("","No hay documentos asignados");
		}
		
		
	
}