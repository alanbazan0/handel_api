class AuditoriaVista extends Vista
{		
	constructor(ventana)
	{	
		super();
		this.ventana = ventana;
		this.presentador = new AuditoriaPresentador(this);
		this.manejadorEventos = new ManejadorEventos();
		this.grid = new GridReg("grid");	
		this.validaciones = new Validaciones();
		this.modeloActual=null;
		//this.listaSecciones = new ListaSecciones("listaSecciones");
		this.listaPreguntas = new ListaPreguntasEjecucion("listaPreguntas");
		//this.listaRespuestas = new ListaRespuestas("listaRespuestas");
		this._categorias = [];
		this._estandares = [];
		this.preguntaEdicion = null;
		this.seccionEdicion = null;
		this.velocidadAnimacion = 400;
	}
//	
//	onLoad()
//	{	
//		
//		this.presentador.consultarPorLlaves();
//		//this.presentador.consultar();
//		
//		
//	}
	
	get plantillaId()
	{
		return $("#plantillaId").val();
	}
	
	crearFecha()
	{
		$.datepicker.regional['es'] = {
				 closeText: 'Cerrar',
				 prevText: '< Ant',
				 nextText: 'Sig >',
				 currentText: 'Hoy',
				 monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
				 monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
				 dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
				 dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sáb'],
				 dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
				 weekHeader: 'Sm',
				 dateFormat: 'dd/mm/yy',
				 firstDay: 1,
				 isRTL: false,
				 showMonthAfterYear: false,
				 yearSuffix: ''
				 };
		
				 $.datepicker.setDefaults($.datepicker.regional['es']);
				
				$(function () {
					$("#fechaProgramadaInput").datepicker();
					});
	}
	
	crearColumnasGrid()
	{
		this.tabla.columnas = [
			{longitud:50, 	titulo:"",   	alias:"icono", alineacion:"D", itemRenderer:this.renderIcono},
			{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"D" },
			{longitud:200, 	titulo:"Nombre",   alias:"nombre", alineacion:"I" }, 		
			{longitud:300, 	titulo:"Descripción",   alias:"descripcion", alineacion:"I" }, 	
			{longitud:250, 	titulo:"Último uso",   alias:"ultimoUso", alineacion:"I" },
			{longitud:250, 	titulo:"Fecha programada",   alias:"fechaProgramada", alineacion:"I" },
			{longitud:250, 	titulo:"Fecha de alta",   alias:"fechaAlta", alineacion:"I" },	
			{longitud:200, 	titulo:"Fecha de última modificación",   alias:"fechaModificacion", alineacion:"I" },
			{longitud:100, 	titulo:"Estatus",   alias:"estatus", alineacion:"D", itemRenderer:this.renderEstatus}
		]
		
		this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-edit fa-lg'></span></button>"+
									"<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";

		this.tabla.registros = [];
	}
	
	renderIcono(renglon, campoBase)
	{    
		var contenido = "";
		var icono ="php/iconos/" + renglon.icono;
		contenido += "<center><img src='" + icono + "' style='width:30px;height:30px;'></img></center>";
	    return contenido;
	}

	btnBaja_onClick()
	{ 
		if(this.grid._selectedItem!=null)
		{
			var confirmacion = confirm("¿Esta seguro que desea eliminar el registro?")
		    if (confirmacion)
		    {
		    		this.presentador.eliminar();
		    }	
		}
		else
			this.mostrarMensaje("Acción no válida","Seleccione un registro para eliminar.");
	}
	
	btnAlta_onClick()
	{
		this.modo = "ALTA";
		this.ocultarIndicador();
		this.limpiarFormulario();	
		this.mostrarFormulario();
		$('#nombreInput').focus();
		this.presentador.consultarCategorias();
	
		
	}

	btnCambio_onClick()
	{
		if(this.grid._selectedItem!=null)
		{			
			this.modo = "CAMBIO";
			this.limpiarFormulario();	
			this.mostrarFormulario();
			$('#nombreInput').focus();				
			this.presentador.consultarPorLlaves();
			
		}
		else
			this.mostrarMensaje("Acción no válida","Seleccione un registro para modificar.");
				
	}
	
	btnEjecutar_onClick()
	{
		if(this.grid._selectedItem!=null)
		{
			var submitForm = getNewSubmitForm("auditoria.php");
			createNewFormElement(submitForm, "plantillaId", this.grid._selectedItem.id);
			submitForm.target= "auditoria" + Math.floor(Math.random()*10000);
			submitForm.submit();
		}
	}
	
	btnConsulta_onClick()
	{	
		this.presentador.consultar();
	}	
	
	btnGuardarFormulario_onClick()
	{		
		if(this.seccionEdicion!=null)
			this.seccionEdicion.preguntas = this.listaPreguntas.preguntas;
		 if(this.datosValidos())
		 {
			if(this.modo=='ALTA')
				this.presentador.insertar();
			else
				this.presentador.actualizar();
		 }		
		
	}
	
	btnSalir_onClick()
	{
		var confirmacion = confirm("¿Esta seguro que desea salir?")
	    if (confirmacion)
	    	{
		    	
	    	}
	}
	
	btnSalirFormulario_onClick()
	{	
		this.confirmar("¿Esta seguro que desea salir?",this,this.cerrarVentana,null);
//		var confirmacion = confirm("¿Esta seguro que desea salir?")
//	    if (confirmacion)
//    	{
//	    	this.cerrarVentana();
//    	}
	}	
	
	cerrarVentana(cerrar)
	{
		this.ventana.close();
	}
	
	set categorias(valor)
	{
		this._categorias = valor;
		
		$('#guardarButton').fadeIn(this.velocidadAnimacion);
		$('#botonesAgregarDiv').fadeIn(this.velocidadAnimacion);
		
		if(this.modo=="CAMBIO" && this.modeloEdicion!=null)
		{
			this.listaSecciones.secciones= this.modeloEdicion.secciones;
			if(this.listaSecciones.secciones!=null)
				if(this.listaSecciones.secciones.length>0)
				{
					this.seccionEdicion =  this.modeloEdicion.secciones[0];
					this.mostrarSeccion(this.seccionEdicion);
				}
		}
		else
		{
			this.listaSecciones.secciones = [];
			this.listaSecciones.agregarSeccion("Sección 1");
			if(this.listaSecciones.secciones!=null)
				if(this.listaSecciones.secciones.length>0)
				{
					this.seccionEdicion = this.listaSecciones.secciones[0];
					this.mostrarSeccion(this.seccionEdicion);
				}
		}
	}

	get llaves()
	{
		var llaves =
		{
			id:$("#plantillaId").val()	
		}
		return llaves;
	}
	
	get criteriosSeleccion()
	{
		 var criteriosSeleccion = 
		 {				    
			nombre:$('#nombreInputCriterio').val()
		 }
		 return criteriosSeleccion;
	}		

	set datos(valor)
	{
		this.grid._dataProvider = valor;	
		this.grid.render();
	}
	
	set modelo(valor)
	{		
		this.modeloEdicion = valor;
		//$('#nombreInput').val(this.modeloEdicion.nombre);
		//$('#descripcionInput').val(this.modeloEdicion.descripcion);
		//$('#fechaProgramadaInput').val(this.modeloEdicion.fechaProgramada);
		//$("input[name=estatus][value=" + this.modeloEdicion.estatus + "]").prop('checked', true);
		
		$('#titulo').html(this.modeloEdicion.nombre);
		//this.presentador.consultarCategorias();
		
		this.listaPreguntas.secciones = this.modeloEdicion.secciones;
		
		$("#contenedor").show();
		
		$('#secciones').show();
		$('#secciones').empty();
		$.each(this.modeloEdicion.secciones, function(i, p) {
		    $('#secciones').append($('<option></option>').val(p.id).html(p.texto));
		});
		
		
		
		//var h = $('#listaPreguntas').height();
	}
	
	cambiarSeccion(event)
	{
		var indice = $("#secciones").prop('selectedIndex');
		this.listaPreguntas.mostrarSeccion(indice);
		//$('#panel').height(this.listaPreguntas.altura + 300);
	}
	
	siguiente()
	{
		var indice = this.listaPreguntas.siguiente();
		$('#secciones').prop('selectedIndex',indice);
	}
	
	atras()
	{
		var indice = this.listaPreguntas.atras();
		$('#secciones').prop('selectedIndex',indice);
	}
	
	guardar()
	{
		alert("guardar...");
	}
	
	get modelo()
	{
		 var modelo = 
		 {		
			 nombre:$('#nombreInput').val(),		
			 descripcion:$('#descripcionInput').val(),	
			 fechaProgramada:$('#fechaProgramadaInput').val(),	
			 estatus:$('input[name=estatus]:checked').val(),
			 secciones: this.listaSecciones.secciones
		 };
		 if(this.modo=="CAMBIO" && this.modeloEdicion!=null)
			 modelo.id = this.modeloEdicion.id;
		 return modelo;
	 }
	
	datosValidos()
	{
		var nombre = $("#nombreInput"),
			descripcion = $("#descripcionInput"),
			fechaProgramada = $("#fechaProgramadaInput");
	        
        
        var allFields = $( [] ).add(nombre).add(descripcion).add(fechaProgramada);
        var tips = $( ".validateTips" );
		tips.text("");
		
		var valid = true;
		allFields.removeClass("ui-state-error");
		
	    valid = valid && this.validaciones.checkValue( nombre, "nombre", tips );
	    valid = valid && this.validaciones.checkValue( descripcion, "descripción", tips );
	   
		return valid;
	}	

	limpiarFormulario()
	{
		$('#nombreInput').val("");
		$('#descripcionInput').val("");
		$('#fechaProgramadaInput').val("");
		$('#listaPreguntas').html("");
		$("#tituloSeccionDiv").hide();
		$("#botonesSuperiores").hide();
		$("#botonesAgregarDiv").hide();
		$("#guardarButton").hide();
		$("#ayudaPreguntas").hide();
	}
	
	agregarSeccion()
	{
		this.listaSecciones.agregarSeccion("");
	}
	
	agregarPregunta()
	{
		this.listaPreguntas.agregarPregunta();
	}
	
	agregarTexto()
	{
		this.listaPreguntas.agregarTexto();
	}
	
	agregarFecha()
	{
		this.listaPreguntas.agregarFecha();
	}
	
	agregarEncabezado()
	{
		this.listaPreguntas.agregarEncabezado();
	}
	
	agregarMapa()
	{
		this.listaPreguntas.agregarMapa();
	}
	
	agregarRespuesta()
	{
		this.listaRespuestas.agregarRespuesta();
	}
	
	eliminarSeccion(event, seccionId)
	{
		if(this.listaSecciones.secciones.length>1)
		{
			//this.listaSecciones.eliminarSeccion(seccionId);
			this.confirmar("¿Desea eliminar esta sección?",this.listaSecciones,this.listaSecciones.eliminarSeccion,seccionId);
		}
		else
			this.mostrarMensaje("Error","Es necesario contar al menos con una sección. ");		
	}
	
	eliminarPregunta(event, preguntaId)
	{
		this.confirmar("¿Desea eliminar esta pregunta?",this.listaPreguntas,this.listaPreguntas.eliminarPregunta,preguntaId);
		//this.listaPreguntas.eliminarPregunta(preguntaId);
	}
	
	eliminarRespuesta(event, respuestaId)
	{
		this.confirmar("¿Desea eliminar esta respuesta?",this.listaRespuestas,this.listaRespuestas.eliminarRespuesta,respuestaId);
		//this.listaRespuestas.eliminarRespuesta(respuestaId);
	}
	
	confirmar(textoDialogo,contexto,funcion,parametro)
	{
		$('#dialogo').prop('title', 'Confirmación');
		$('#dialogo').html(textoDialogo);
		$('#dialogo').data('contexto', contexto);
		$('#dialogo').data('funcion', funcion);	
		$('#dialogo').data('parametro', parametro);
		
		$('#dialogo').dialog({			
			autoOpen: false,			
			modal: true,				
			width: 340,			
			height: 150,
			//dialogClass: 'dialogo',				
			buttons:[{
			        text: "Cancelar",				       
			        click: function () {
			            $(this).dialog( "close" );
			        },
			
			    }, 
			    {
			        text: "Aceptar",
			        click: function () 
			        {
			        	var contexto = $(this).data('contexto');
			           	var funcion = $(this).data('funcion');
			           	var parametro = $(this).data('parametro');
			           	funcion.call(contexto,parametro);
//			           	 escenario.consultaPanelesConLimite(1000);
			        	
			           	  $(this).dialog( "close" );
			        },
			    }]
		});
		$('#dialogo').dialog('open');
	}
	
	seleccionarSeccion(event, seccionId)
	{
		this.seccionEdicion.preguntas = this.listaPreguntas.preguntas;
		this.seccionEdicion = this.listaSecciones.getSeccion(seccionId);
		if(this.seccionEdicion!=null)
		{
			this.cerrarSecciones();
			
		}
	}
	
	mostrarSeccion(seccion)
	{
		$("#tituloSeccionDiv").html(seccion.texto);
		$("#tituloSeccionDiv").show();
		$("#botonesSuperiores").show();
		
		if(seccion!=null)
			this.listaPreguntas.preguntas = seccion.preguntas;
		else
			this.listaPreguntas.preguntas = [];
	}
	
	
	
	cancelarRespuestas()
	{
		$('#ventanaRespuestasContenedor').hide();
	}
	
	editarRespuestasSi(preguntaId)
	{
		$("#tituloRespuestas").val("Respuestas Si");
		$('#ventanaRespuestasContenedor').data( "tipo", "SI" );
		$('#ventanaRespuestasContenedor').fadeIn( this.velocidadAnimacion );
	
		this.preguntaEdicion = this.listaPreguntas.getPregunta(preguntaId);
		this.listaRespuestas.categorias = this._categorias;
		this.listaRespuestas.respuestas = this.preguntaEdicion.respuestas_si;
	}
	
	editarRespuestasNo(preguntaId)
	{
		$("#tituloRespuestas").val("Respuestas No");
		$('#ventanaRespuestasContenedor').data( "tipo", "NO" );
		$('#ventanaRespuestasContenedor').fadeIn( this.velocidadAnimacion );
		this.preguntaEdicion = this.listaPreguntas.getPregunta(preguntaId);
		this.listaRespuestas.categorias = this._categorias;
		this.listaRespuestas.respuestas = this.preguntaEdicion.respuestas_no;
	}
	
	guardarRespuestas()
	{
		if(this.preguntaEdicion!=null)
		{
			var tipo = $('#ventanaRespuestasContenedor').data("tipo");
			if(tipo=="SI")
				this.preguntaEdicion.respuestas_si = this.listaRespuestas.respuestas;
			else
				this.preguntaEdicion.respuestas_no = this.listaRespuestas.respuestas;
		}
			
			
		$('#ventanaRespuestasContenedor').fadeOut(this.velocidadAnimacion);
	}
	
	editarSecciones()
	{
		//$('#ventanaSeccionesContenedor').show();
		$('#ventanaSeccionesContenedor').fadeIn( this.velocidadAnimacion );
	}
	
	cerrarSecciones()
	{
		this.listaSecciones.actualizarTitulos();
		this.seccionEdicion = this.listaSecciones.getSeccion(this.seccionEdicion.id);
		if(this.seccionEdicion!=null)
			this.mostrarSeccion(this.seccionEdicion);
		else
		{
			if(this.listaSecciones.secciones.length>0)
			{
				this.seccionEdicion =  this.listaSecciones.secciones[0];
				this.mostrarSeccion(this.seccionEdicion);
			}
			else
			{
				this.seccionEdicion = null;
				this.mostrarSeccion(this.seccionEdicion);
			}
		}
		$('#ventanaSeccionesContenedor').fadeOut( this.velocidadAnimacion );
	}
	
	cerrarCamara()
	{
		$("#ventanaCamaraContenedor").fadeOut(500);
	}
	
	cerrarMapa()
	{
		$('#modalMapa').modal("hide");
	}
}
var vista = new AuditoriaVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});
