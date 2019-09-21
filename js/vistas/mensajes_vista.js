class MensajesVista extends CatalogoVista	
{		
	constructor(ventana)
	{	
		super(ventana);
		this.presentador = new MensajesPresentador(this);
		this.consultoGrid = false;
		//this._tablaMensajes = new Mensajes("tabla");
		
		this._colores = [
			"#cdd9c5",
			"#e7d1c8",
			"#d3e0e1",
			"#dcdcd1",
			"#ecede7",
			"#9e927f",
			"#bec3d9",
			"#f3b885",
			"#c8bec2",
			"#a9e5e3",
			"#b5bcc3",
			"#c39297",
			"#ffffff",
			"#f8e9b2",
			"#b8bdd2",
			"#f7dfed",
			"#d2cbc1",
			"#dbdcde"];
		
	}
	
	inicializar()
	{
		$("body").data("_this",this);
		this.tabla.ocultarEncabezados = true;
		this.tabla.textoTablaVacia = "No hay mensajes";
		super.inicializar();
		
		this._mensajeIdParametro = "";
	
	}
	
	get mensajeId()
	{
		return $("body").attr("data-mensajeId");
	}
	

	inicializarEventosBotonesTabla(tbody, table, nombresCamposLlave)
	{
		var _this = this;
		$(tbody).on("click", "span.mensaje", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

		    _this.mensajeSeleccionado  = table.row( tr ).data();
			if (_this.mensajeSeleccionado != undefined)
			{
				_this.mostrarModalMensaje();
			
			}
		});
		$(tbody).on("click", "button.leer", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

		    _this.mensajeSeleccionado  = table.row( tr ).data();
			if (_this.mensajeSeleccionado != undefined)
			{
				_this.mostrarModalMensaje();
			
			}
		});
		
	}
	
	
	mostrarModalMensaje()
	{
		var _this = this;
		if($("#modalAlta").length ==0)
		{
			var url = HANDEL_API + "/html/modales/ver_mensaje.php";
			this.mostrarIndicador();
			var _this = this;
			$.post(url,{}, function(html) 
			{
				_this.ocultarIndicador();
				$("body").append(html);
				$("#modalAlta").on("hidden.bs.modal", function () {
					clearInterval(_this.cometariosIntervalId);
					$("#modalAlta").remove();
				});
				
				$("#modalAlta").on("show.bs.modal", function () 
				{
					var fecha = new Date();
					var foto = HANDEL_API + "/" + _this.mensajeSeleccionado.fotoPerfil+"?"+fecha.getTime();
					
					
					var fotoPerfil = HANDEL_API + "/" + _this.usuario.fotoPerfil+"?"+fecha.getTime();
					$("#fotoPerfilComentarioImg").attr("src",fotoPerfil);
					
					moment.locale('es') ;
					var fecha = moment(_this.mensajeSeleccionado.fecha);
					
					var html = "<img class='img-circle' src='"+foto+"' "+
								"alt='User Image'> <span class='username'><a href='#'>" + _this.mensajeSeleccionado.usuarioNombreCompleto +"</a>" +
								"</span>  <span class='description' style='font-size: 15px;font-weight:bold;color:#000000'> " + _this.mensajeSeleccionado.asunto +"</span><span class='description'>Publicado" +
								"- " + fecha.fromNow() + "</span>";
					$("#usuarioDiv").html(html);
					$("#mensajeDiv").html(_this.mensajeSeleccionado.mensaje);
					
					
					if(_this.mensajeSeleccionado.leido==0)
						_this.marcarMensajeComoLeido();
					
					$("#enviarComentarioButton").click(function () 
					{
						var comentario = $("#comentarioInput").val().trim();
						if(comentario!="")
							_this.enviarComentario();
					});
					$("#comentarioInput").keypress(function(event){
					    var keycode = (event.keyCode ? event.keyCode : event.which);
					    if(keycode == '13')
					    {
					    	var comentario = $("#comentarioInput").val().trim();
							if(comentario!="")
								_this.enviarComentario();
					    }
					});
					_this._comentarios = [];
					_this.consultarComentarios();
					_this.cometariosIntervalId = setInterval(_this.consultarComentariosAutomaticamente, 20000);
					
					
				});
			
				
				$("#modalAlta").modal({backdrop: 'static', keyboard: false});
			});
		}
		else
		{
			$("#modalAlta").modal({backdrop: 'static', keyboard: false});
		}
	}
	
	
	consultarComentariosAutomaticamente()
	{
		var _this  = $("body").data("_this");
		_this.consultarComentarios();
	}
	
	consultarComentarios()
	{
		this.presentador.consultarComentarios();
	}
	
	marcarMensajeComoLeido()
	{
		this.presentador.marcarMensajeComoLeido();
	}
	
	set comentarios(comentarios)
	{
		var numero="";
		if(comentarios.length==1)
			numero = "1 comentario";
		else
			numero = comentarios.length+" comentarios";
		$("#numeroComentariosSpan").html(numero);
		if(comentarios.length> this._comentarios.length)
		{
			this._comentarios= comentarios;
			
			var usuariosColores = this.asignarColoresUsuarios();
		
			var html="";
			for(var i=0; i< comentarios.length; i++)
			{
				var fecha = new Date();
				var comentario = comentarios[i];
				var foto = HANDEL_API + "/" + comentario.fotoPerfil+"?"+fecha.getTime();

				var color = this.buscarPorValor(usuariosColores,"usuarioId",comentario.usuarioId);
				
				
				moment.locale('es') ;
				var fechaComentario = moment(comentario.fecha);
				
				html+="<div class='box-comment' data-usuarioId='"+comentario.usuarioId+"' style='background-color:"+color.color+";padding:5px;'>" +
				"<img class='img-circle img-sm' src='"+foto+"' alt='User Image'>" +
				"<div class='comment-text'>" +
					"<span class='username'> "+comentario.usuarioNombreCompleto+" <span class='text-muted pull-right'>"+fechaComentario.fromNow()+"</span>" +
					"</span>" + comentario.comentario +
				"</div>" +
				"</div>";
				
			}
			$("#comentariosDiv").html(html);
		}	
	}
	
	asignarColoresUsuarios()
	{
		var usuariosColores = []; 
		var indiceColor = 0;
		for(var i=0; i < this._comentarios.length; i++)
		{
			var comentario = this._comentarios[i];
			var usuarioId = comentario.usuarioId;
			var usuario = this.buscarPorValor(usuariosColores,"usuarioId",usuarioId);
			if(usuario==null)
			{
				var color = "#ffffff";
				if(indiceColor < this._colores.length)
					color = this._colores[indiceColor];
				usuariosColores.push({usuarioId: usuarioId, color: color});
				indiceColor++;
			}
				
		}
		return usuariosColores;
	}
	
	marcarMensaje(mensajeId)
	{
		var mensajeSpan = "mensajeSpan" + mensajeId;
		$("#"+mensajeSpan).css("font-weight","normal");
		var mensajeLeidoCenter = "mensajeLeidoCenter" + mensajeId;
		$("#"+mensajeLeidoCenter).html("<span class='fas fa-check-double fa-lg text-info'></span>");
		this.mensajeSeleccionado.leido = 1;
		this.consultarNumeroMensajesNoLeidos();
	}
	
	crearColumnasGrid()
	{
		this.tabla._columnas = [
//			{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"D" },
			{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"D" ,itemRenderer:this.renderLogo},
			{longitud:120, 	titulo:"Usuario",   alias:"usuarioNombreCompleto", alineacion:"I",itemRenderer:this.renderNombre}, 
			{longitud:150, 	titulo:"Asunto",   alias:"asunto", alineacion:"I",itemRenderer:this.renderAsunto}, 
			//{longitud:200, 	titulo:"Mensaje",   alias:"mensaje", alineacion:"I",itemRenderer:this.renderMensaje}, 
			{longitud:200, 	titulo:"",   alias:"leido", alineacion:"I",itemRenderer:this.renderLeido}, 
			{longitud:200, 	titulo:"Fecha",   alias:"fecha", alineacion:"I" ,itemRenderer:this.renderFecha}		
			
		]
		
		this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Ver'  type='button' class='leer btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-comments fa-lg'></span></button>";
//									"<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";

		this.tabla.registros = [];
	}
	
	renderLogo(renglon, type, set)
	{    
		var fecha = new Date();
		var contenido = "";
		var icono = HANDEL_API+ "/"+renglon.fotoPerfil+"?"+fecha.getTime();
		contenido += "<center><img src='" + icono + "' class='user-image' style='width:30px;height:30px;border-radius:50%'></img></center>";
	    return contenido;
	}
	
	renderNombre(renglon, type, set)
	{    
		var contenido = "";
		contenido += "<span class='text-primary'>"+renglon.usuarioNombreCompleto+"</span>";
	    return contenido;
	}
	
	renderFecha(renglon, type, set)
	{    
		moment.locale('es') ;
		
		var fechaActual = new Date();
		
		var fecha = moment(renglon.fecha);
		
		var contenido = "";
		contenido += "<span class='text-black' style='font-weight: normal'>"+fecha.fromNow()+"</span>";
	    return contenido;
	}
	
	renderMensaje(renglon, type, set)
	{   
		var maximo = 100;
		var mensaje = "";
		if(renglon.mensaje.length>maximo)
			mensaje = renglon.mensaje.substring(0, maximo) + "...";
		else
			mensaje = renglon.mensaje;
	
		
		var weight = "normal";
		if(renglon.leido==0)
			weight = "bold";
		
		var id = "mensajeSpan" + renglon.id;
		var contenido = "";
		contenido += "<span id='"+id+"' style='cursor:pointer;font-weight: "+weight+"' class='mensaje text-black'>"+mensaje+"</span>";
	    return contenido;
	}
	
	renderAsunto(renglon, type, set)
	{   
		var maximo = 100;
		var mensaje = "";
		if(renglon.asunto.length>maximo)
			mensaje = renglon.asunto.substring(0, maximo) + "...";
		else
			mensaje = renglon.asunto;
		
		
		if(mensaje=="")
			mensaje = "(Sin asunto)";
		
		var weight = "normal";
		if(renglon.leido==0)
			weight = "bold";
		
		var id = "mensajeSpan" + renglon.id;
		var contenido = "";
		contenido += "<span id='"+id+"' style='cursor:pointer;font-weight: "+weight+"' class='mensaje text-black'>"+mensaje+"</span>";
	    return contenido;
	}
	
	renderLeido(renglon, type, set)
	{   
		var id = "mensajeLeidoCenter" + renglon.id;
		var contenido = "";
		if(renglon.leido==1)
			contenido += "<center id='"+id+"'><span class='fas fa-check-double fa-lg text-info'></span></center>";
		else
			contenido += "<center id='"+id+"'><span></span></center>";
	    return contenido;
	}

	consultarCombos()
	{
		 setTimeout(function (){
			 $('#nombreInput').focus();
		    }, 1000);
		
		this.consultarEmpresas();
		this.consultarTiposArea();
	}
	
	editar(id)
	{
		super.editar(id);
		
		$('#nombreInput').focus();
	}
	
	get criteriosSeleccion()
	{
		 var criteriosSeleccion = 
		 {				    
			empresaId: $('#empresaSelectCriterio').val(),
			sedeId: $('#sedeSelectCriterio').val(),
			nombre:$('#nombreInputCriterio').val()
		 }
		 return criteriosSeleccion;
	}		
	
	set modelo(valor)
	{		
		this.modeloEdicion = valor;
		$('#nombreInput').val(this.modeloEdicion.nombre);
		$("input[name=estatus][value=" + this.modeloEdicion.estatus + "]").prop('checked', true);
		this.consultarCombos();
	}
	
	get modelo()
	{
		 var modelo = 
		 {		
			 nombre:$('#nombreInput').val(),
			 empresaId:$('#empresaSelect').val(),
			 sedeId:$('#sedeSelect').val(),
			 tipoAreaId:$('#tipoAreaSelect').val(),
			 estatus:$('#estatusRadio').is(':checked')?1:0
		 };
		 if(this.modo=="CAMBIO" && this.modeloEdicion!=null)
			 modelo.id = this.modeloEdicion.id;
		 return modelo;
	 }

	limpiarFormulario()
	{
		$('#nombreInput').val("");
		this.cargandoOpciones('#empresaSelect');
	}
	

	mensajeEnviado()
	{
		$("#modalAlta").modal('hide');
		this.mostrarMensaje("","El mensaje fue enviado.")
		this.consultar();
	}
	
	enviarComentario()
	{
		this.presentador.enviarComentario();
		$("#comentarioInput").val("");
	}
	
	get modeloComentario()
	{
		var modelo =
		{
			mensajeId: this.mensajeSeleccionado.id,
			usuarioId: this.usuario.id,
			comentario: $("#comentarioInput").val()
		};
		return modelo;
	}
	
	set datos(datos)
	{
		super.datos = datos;
		if(this._mensajeIdParametro=="")
		{
			this._mensajeIdParametro = this.mensajeId;
			this.mensajeSeleccionado = this.buscarPorValor(datos,"id",this._mensajeIdParametro);
			if (this.mensajeSeleccionado != undefined)
			{
				this.mostrarModalMensaje();
			}
		}
	}

	
}
var vista = new MensajesVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});
