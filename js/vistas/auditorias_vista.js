class AuditoriasVista extends CatalogoVista
{		
	constructor(ventana)
	{	
		super();
		this.ventana = ventana;
		this.presentador = new AuditoriasPresentador(this);
		
		//this.grid = new Tabla("grid");	
		//this.manejadorEventos = new ManejadorEventos();
		//this.grid = new GridReg("grid");	
		//this.validaciones = new Validaciones();
		//this.modeloActual=null;
//		this.listaSecciones = new ListaSecciones("listaSecciones");
//		this.listaPreguntas = new ListaPreguntas("listaPreguntas");
//		this.listaRespuestas = new ListaRespuestas("listaRespuestas");
//		this._categorias = [];
//		this._estandares = [];
//		this.preguntaEdicion = null;
//		this.seccionEdicion = null;
//		this.velocidadAnimacion = 400;
		
		
	}
	
	inicializar()
	{	
		super.inicializar();
		
		
		this.crearFecha();
		
		
		$(window).data("_this",this);
		$(window).focusin(this.enfocarVentana);

	}
	
	enfocarVentana()
	{
		var _this = $(window).data("_this");
		if(_this._registroSeleccionado !=null)
		{
			if(_this.ejecutandoAuditoriaId!="")
			{
				_this.ejecutandoAuditoriaId = "";
				_this.consultarPorLlaves();
			}
		}
	}

	consultarPorLlaves()
	{
		this.presentador.consultarPorLlaves();
	}
	
	
	inicializarEventosTabla(tbody, table)
	{
		this.inicializarEventosBotonesTabla(tbody, table, ["id"]);
		var _this = this;
		$(tbody).on("click", "button.ejecutar", function()
		{			
			var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

			_this._registroSeleccionado  = table.row( tr ).data();
			if (_this._registroSeleccionado != undefined)
			{
				_this._llaves = _this.copiarPropiedadesObjeto(_this._registroSeleccionado, ["id","plantillaId"]);
				_this.ejecutar();
			}
		});
		$(tbody).on("click", "button.reporte", function()
		{			
			var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

			_this._registroSeleccionado  = table.row( tr ).data();
			if (_this._registroSeleccionado != undefined)
			{
				_this._llaves = _this.copiarPropiedadesObjeto(_this._registroSeleccionado, ["id"]);
				_this.imprimirReporte();
			}
		});
		
		$(tbody).on("click", "button.exportarActionTracker", function()
		{			
			var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

			_this._registroSeleccionado  = table.row( tr ).data();
			if (_this._registroSeleccionado != undefined)
			{
				_this._llaves = _this.copiarPropiedadesObjeto(_this._registroSeleccionado, ["id"]);
				_this.exportarActionTracker();
			}
		});
	}
	
	
	
	ejecutar()
	{
		var submitForm = this.getNewSubmitForm("auditoria.php?auditoriaId="+this._llaves.id+"&modo="+Modo.CAMBIO,"get");
		//this.createNewFormElement(submitForm, "plantillaId", this._llaves.plantillaId);
		this.createNewFormElement(submitForm, "auditoriaId", this._llaves.id);
		this.createNewFormElement(submitForm, "modo", Modo.CAMBIO);
		submitForm.target= "auditoria" + Math.floor(Math.random()*10000);
		submitForm.submit();
		this.ejecutandoAuditoriaId = this._llaves.id;
	}
	
	imprimirReporte()
	{
		var submitForm = this.getNewSubmitForm(HANDEL_API+"/php/reportes/reporte_auditoria.php");
		this.createNewFormElement(submitForm, "auditoriaId", JSON.stringify(this._llaves.id));	 
	    submitForm.target= "_blank";
	    submitForm.submit();
	}
	
	exportarActionTracker()
	{
		var submitForm = this.getNewSubmitForm(HANDEL_API+"/php/excel/action_tracker.php");
		this.createNewFormElement(submitForm, "auditoriaId", JSON.stringify(this._llaves.id));	 
	    submitForm.target= "_blank";
	    submitForm.submit();
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
			{longitud:200, 	titulo:"Plantilla",   alias:"plantillaNombre", alineacion:"I" }, 
			{longitud:200, 	titulo:"Seguimiento iniciado",   alias:"seguimiento", alineacion:"D", itemRenderer:this.renderSeguimiento},		
			{longitud:200, 	titulo:"Seguimiento finalizado",   alias:"seguimientoFinalizado", alineacion:"D", itemRenderer:this.renderSeguimientoFinalizado},		
			{longitud:300, 	titulo:"Empresa",   alias:"empresaNombre", alineacion:"I" }, 	
			{longitud:300, 	titulo:"Sede",   alias:"sedeNombre", alineacion:"I" }, 	
			{longitud:200, 	titulo:"Tipo de auditoría",   alias:"tipoAuditoriaNombre", alineacion:"I" }, 
			{longitud:250, 	titulo:"Fecha de auditoría",   alias:"fecha", alineacion:"C",itemRenderer:this.renderFechaAuditoria },
			//{longitud:250, 	titulo:"Hora",   alias:"hora", alineacion:"C" },
			{longitud:250, 	titulo:"Fecha de última ejecución",   alias:"fechaEjecucion", alineacion:"I" },
			{longitud:50, 	titulo:"Puntuacion",   alias:"puntuacion", alineacion:"C", itemRenderer: this.rendererPuntuacion },
			{longitud:50, 	titulo:"Número",   alias:"contadorEmpresa", alineacion:"C" },
			{longitud:200, 	titulo:"Referencia",   alias:"referencia", alineacion:"I" },
			{longitud:50, 	titulo:"",  alias:"", alineacion:"I" ,itemRenderer:this.renderExportarActionTracker}
			
		]
		
		this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Ejecutar'  type='button' class='ejecutar btn-circle mr-0 botones-icon btn btn-sm float-left btn-success active'><span  data-toggle='tooltip' class='fa fa-play-circle fa-lg'></span></button>" +
		 								"<button data-toggle='tooltip' data-placemen='bottom' title='Reporte'  type='button' class='reporte btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fas fa-file-pdf fa-lg'></span></button>" +
									"<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";

			
		this.tabla.registros = [];
	}
	
	renderSeguimiento(renglon, type, set)
	{    
		var id = "seguimientoCenter" + renglon.id;
		var contenido = "";
		if(renglon.seguimiento==1)
			contenido += "<center id='"+id+"'><span class='fa fa-check fa-lg text-success'></span> "+renglon.fechaSeguimiento+"</center>";
		else
			contenido += "<center id='a"+id+"'></center>";
	    return contenido;
	}
	
	renderSeguimientoFinalizado(renglon, type, set)
	{    
		var id = "seguimientoFinalizadoCenter" + renglon.id;
		var contenido = "";
		if(renglon.seguimientoFinalizado==1)
			contenido += "<center id='"+id+"'><span class='fa fa-check fa-lg text-success'></span> "+renglon.fechaSeguimientoFinalizado+"</center>";
		else
			contenido += "<center id='a"+id+"'></center>";
	    return contenido;
	}
	
	renderFechaAuditoria(renglon)
	{
		var fecha = "";
		if(renglon.fecha!=null)
			fecha+= renglon.fecha;
		if(renglon.hora!=null)
			fecha+= " " +renglon.hora;
		return fecha; 
	}
	
	
	rendererPuntuacion(renglon, type, set)
	{    
		//if(renglon.fechaUltimaCapacitacion!=null)
		//{
			if(renglon.puntuacion==undefined)
				renglon.puntuacion = 0;
		
			var porcentajeCumplimiento = parseFloat(renglon.puntuacion);
			var color ="";
			if(porcentajeCumplimiento <= 70)
			{
				color = "red";
			}
			else if(porcentajeCumplimiento > 70 && porcentajeCumplimiento <=80)
			{
				color = "#e9a13d";
			}
			else if(porcentajeCumplimiento > 80)
			{
				color = "green";
			}
			return "<span  style='font-weight:bold;color:"+color+";' >"+porcentajeCumplimiento+"%</span>";
		//}
		return "";
	}
	
	renderIcono(renglon, type, set)
	{    
		var fecha = new Date();
		var icono = HANDEL_API + "/php/iconos_plantillas/" + renglon.icono+"?"+fecha.getTime();
		var contenido = "";
		contenido += "<center><img src='" + icono + "' style='width:30px;height:30px;'></img></center>";
	    return contenido;
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

//	btnCambio_onClick()
//	{
//		if(this.grid._selectedItem!=null)
//		{			
//			this.modo = "CAMBIO";
//			this.limpiarFormulario();	
//			this.mostrarFormulario();
//			$('#nombreInput').focus();				
//			this.presentador.consultarPorLlaves();
//			
//		}
//		else
//			this.mostrarMensaje("Acción no válida","Seleccione un registro para modificar.");
//				
//	}
//	
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

	/*get llaves()
	{
		var llaves =
		{
			id:this.grid._selectedItem.id	
		}
		return llaves;
	}*/
	
	get criteriosSeleccion()
	{
		 var criteriosSeleccion = 
		 {				    
			nombre:$('#nombreInputCriterio').val()
		 }
		 return criteriosSeleccion;
	}		

//	set datos(valor)
//	{
//		this.grid._dataProvider = valor;	
//		this.grid.render();
//	}
	
	set modelo(valor)
	{		
		this.modeloEdicion = valor;
		$('#nombreInput').val(this.modeloEdicion.nombre);
		$('#descripcionInput').val(this.modeloEdicion.descripcion);
		$('#fechaProgramadaInput').val(this.modeloEdicion.fechaProgramada);
		$("input[name=estatus][value=" + this.modeloEdicion.estatus + "]").prop('checked', true);
		
		this.presentador.consultarCategorias();
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
	
	agregarHora()
	{
		this.listaPreguntas.agregarHora();
	}
	
	agregarFoto()
	{
		this.listaPreguntas.agregarFoto();
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
//	
//	confirmar(textoDialogo,contexto,funcion,parametro)
//	{
//		$('#dialogo').prop('title', 'Confirmación');
//		$('#dialogo').html(textoDialogo);
//		$('#dialogo').data('contexto', contexto);
//		$('#dialogo').data('funcion', funcion);	
//		$('#dialogo').data('parametro', parametro);
//		
//		$('#dialogo').dialog({			
//			autoOpen: false,			
//			modal: true,				
//			width: 340,			
//			height: 140,
//			//dialogClass: 'dialogo',				
//			buttons:[{
//			        text: "Cancelar",				       
//			        click: function () {
//			            $(this).dialog( "close" );
//			        },
//			
//			    }, 
//			    {
//			        text: "Aceptar",
//			        click: function () 
//			        {
//			        	var contexto = $(this).data('contexto');
//			           	var funcion = $(this).data('funcion');
//			           	var parametro = $(this).data('parametro');
//			           	funcion.call(contexto,parametro);
////			           	 escenario.consultaPanelesConLimite(1000);
//			        	
//			           	  $(this).dialog( "close" );
//			        },
//			    }]
//		});
//		$('#dialogo').dialog('open');
//	}
	
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
		this.listaPreguntas.categorias = this._categorias;
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
			{
				this.preguntaEdicion.respuestas_si = this.listaRespuestas.respuestas;
				var peso = this.listaPreguntas.getPeso(this.preguntaEdicion.id);
				var pesoRespuestas = 0;
				for(var i=0; i < this.preguntaEdicion.respuestas_si.length; i++)
				{
					var respuesta = this.preguntaEdicion.respuestas_si[i];
					
					pesoRespuestas+=parseInt(respuesta.peso);
				}
				this.listaPreguntas.setPeso(this.preguntaEdicion.id,pesoRespuestas+1);
				
				//if(peso==0)
				//{
				//	this.listaPreguntas.setPeso(this.preguntaEdicion.id,this.preguntaEdicion.respuestas_si.length+1);
				//}
			}
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
	
	set modelo(modelo)
	{
		if(this._registroSeleccionado)
		{
			this._registroSeleccionado.seguimiento = modelo.seguimiento;
			this._registroSeleccionado.fechaSeguimiento = modelo.fechaSeguimiento;
			
			this._registroSeleccionado.seguimientoFinalizado = modelo.seguimientoFinalizado;
			this._registroSeleccionado.fechaSeguimientoFinalizado = modelo.fechaSeguimientoFinalizado;
			
			var id = "seguimientoCenter" + this._registroSeleccionado.id;
			var contenido = "";
			if(this._registroSeleccionado.seguimiento==1)
				contenido = "<span class='fa fa-check fa-lg text-success'></span> "+this._registroSeleccionado.fechaSeguimiento;
			else
				contenido = "";
		    $("#"+ id).html(contenido);

			var id = "seguimientoCenterFinalizado" + this._registroSeleccionado.id;
			var contenido = "";
			if(this._registroSeleccionado.seguimientoFinalizado==1)
				contenido = "<span class='fa fa-check fa-lg text-success'></span> "+this._registroSeleccionado.fechaSeguimientoFinalizado;
			else
				contenido = "";
		    $("#"+ id).html(contenido);
		}	
	}
	
	renderExportarActionTracker(renglon, type, set)
	{    
		var contenido = "";
		if(renglon.seguimiento == 1)
		{
			contenido += "<button data-toggle='tooltip' data-placemen='bottom' title='Action Tracker'  type='button' class='exportarActionTracker btn-circle mr-0 botones-icon btn btn-sm float-right btn-info active'><span  data-toggle='tooltip' class='fa fa-file-excel fa-lg'></span></button>";
		}
	    return contenido;
	}
	
	
}
var vista = new AuditoriasVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});


