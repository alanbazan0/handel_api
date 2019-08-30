class MensajesVista extends CatalogoVista	
{		
	constructor(ventana)
	{	
		super(ventana);
		this.presentador = new MensajesPresentador(this);
		this.consultoGrid = false;
		//this._tablaMensajes = new Mensajes("tabla");
		
	}
	
	inicializar()
	{
		this.tabla.ocultarEncabezados = true;
		this.tabla.textoTablaVacia = "No hay mensajes";
		super.inicializar();
	
		//this.consultarEmpresasCriterio();
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
					$("#modalAlta").remove();
				});
				
				$("#modalAlta").on("show.bs.modal", function () 
				{
					var fecha = new Date();
					var foto = HANDEL_API + "/" + _this.mensajeSeleccionado.fotoPerfil+"?"+fecha.getTime();
					
					moment.locale('es') ;
					
					
					var fecha = moment(_this.mensajeSeleccionado.fecha);
					
					var html="<div class='item'>" +
					"<img src='"+foto+"' alt='user image' class='offline'> " +
					"<p class='message'>" +
					"  <a href='#' class='name'>" +
					"	<small class='text-muted pull-right'><i class='fa fa-clock-o'></i> "+fecha.fromNow() +"</small>" + _this.mensajeSeleccionado.usuarioNombreCompleto +
					"  </a> <label style='font-weight:bold'> " + _this.mensajeSeleccionado.asunto +"</label>"  +
					"<br>" + _this.mensajeSeleccionado.mensaje +
 					"</p>" +
				  "</div>";
					$("#chatbox").html(html);
					
					if(_this.mensajeSeleccionado.leido==0)
						_this.marcarMensajeComoLeido();
					
					
				});
			
				
				$("#modalAlta").modal({backdrop: 'static', keyboard: false});
			});
		}
		else
		{
			$("#modalAlta").modal({backdrop: 'static', keyboard: false});
		}
	}
	
	marcarMensajeComoLeido()
	{
		this.presentador.marcarMensajeComoLeido();
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
	
//	onLoad()
//	{
//		this.inicializarEliminar();
//		this.crearColumnasGrid();
//		this.consultarEmpresasCriterio();
//	}
//	
//	set datos(datos)
//	{
//		this.tabla.registros = datos;	
//		this.inicializarEventosTabla("#" + this.tabla._id+"Table tbody",this.tabla.datatable.DataTable());
//	}
	
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
	
//	inicializarValidacionesFormulario()
//	{
//		var _this = this;
//		jQuery("#formulario").validate({
//            ignore: [],
//            errorClass: "invalid-feedback animated fadeInDown",
//            errorElement: "div",
//            errorPlacement: function(e, a) {
//                jQuery(a).parents(".form-group > div").append(e)
//            },
//            highlight: function(e) {
//                jQuery(e).closest(".form-group").removeClass("is-invalid").addClass("is-invalid")
//            },
//            success: function(e) {
//                jQuery(e).closest(".form-group").removeClass("is-invalid"), jQuery(e).remove()
//            },
//            rules: {
//            	 "empresaSelect": {required: !0},
//                "sedeSelectInput": {required: !0},
//                "tipoAreaSelect": {required: !0},
//                "nombreInput": {required: !0}
//               
//            },
//            messages: {
//            	 "empresaSelect": "Por favor ingrese una empresa",
//            	 "sedeSelect": "Por favor ingrese una sede",
//            	 "tipoAreaSelect": "Por favor ingrese un tipo de area",
//                "nombreInput": "Por favor ingrese un nombre"
//                	
//                
//            },
//            submitHandler:function (form) {
//            	 _this.guardar();
//            }
//        });
//	}
//	
//	agregar()
//	{
//		super.agregar();
//		
//		
//	}
	
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
	
//	consultarEmpresas()
//	{
//		this.cargandoOpciones("#empresaSelect");
//		this.cargandoOpciones("#sedeSelect");
//		this.presentador.consultarEmpresas();
//	}
//	
//	consultarTiposArea()
//	{
//		this.cargandoOpciones("#tipoAreaSelect");
//		this.presentador.consultarTiposArea();
//	}

//	set empresas(registros)
//	{		
//		this.cargarOpciones('#empresaSelect', registros, this.modo, this.modeloEdicion, 'empresaId',"");
//	}
//	
//	set tiposArea(registros)
//	{		
//		this.cargarOpciones('#tipoAreaSelect', registros, this.modo, this.modeloEdicion, 'tipoAreaId',"");
//	}
//	
//	consultarEmpresasCriterio()
//	{
//		this.cargandoOpciones("#empresaSelectCriterio");
//		this.cargandoOpciones("#sedeSelectCriterio");
//		this.presentador.consultarEmpresasCriterio();
//	}
	
//	set empresasCriterio(registros)
//	{		
//		this.cargarOpciones('#empresaSelectCriterio', registros);
//		//this.consultar();
//	}
//	
//	cambiarEmpresaCriterio()
//	{
//		this.consultarSedesCriterio();
//	}
//	
//	cambiarEmpresa()
//	{
//		this.cargandoOpciones("#sedeSelect");
//		this.consultarSedes();
//	}
	
//	consultarSedes()
//	{
//		this.presentador.consultarSedes();
//	}
//	
//	set sedes(registros)
//	{
//		this.cargarOpciones('#sedeSelect', registros, this.modo, this.modeloEdicion, 'sedeId',"");
//	}
//	
//	consultarSedesCriterio()
//	{
//		this.cargandoOpciones("#sedeSelectCriterio");
//		this.presentador.consultarSedesCriterio();
//	}
//	
//	set sedesCriterio(registros)
//	{		
//		this.cargarOpciones('#sedeSelectCriterio', registros);
//		if(this.consultoGrid==false)
//		{
//			this.consultar();
//			this.consultoGrid=true;
//		}
//	}
	
	mensajeEnviado()
	{
		$("#modalAlta").modal('hide');
		this.mostrarMensaje("","El mensaje fue enviado.")
		this.consultar();
	}

	
}
var vista = new MensajesVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});
