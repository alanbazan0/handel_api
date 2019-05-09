class AuditoriaVista extends Vista
{		
	constructor(ventana)
	{	
		super();
		this.ventana = ventana;
		this.presentador = new AuditoriaPresentador(this);
		//this.manejadorEventos = new ManejadorEventos();
		//this.grid = new GridReg("grid");	
		//this.validaciones = new Validaciones();
		this.modeloActual=null;
		//this.listaSecciones = new ListaSecciones("listaSecciones");
		this.listaPreguntas = new ListaPreguntasEjecucion("listaPreguntas");
		//this.listaRespuestas = new ListaRespuestas("listaRespuestas");
		this._categorias = [];
		this._estandares = [];
		this.preguntaEdicion = null;
		this.seccionEdicion = null;
		this.velocidadAnimacion = 400;
		
		this._modo = $("#modo").val()
		if(this._modo==""  || this._modo==undefined)
			this._modo = Modo.ALTA;
		
		
		
	}
	
	get modo()
	{
		return this._modo;
	}
	
	set modo(modo)
	{
		this._modo = modo;
	}
	
	set llaves(llaves)
	{
		this._llaves = llaves;
	}
	
	setModoCambio(id)
	{
		this._modo = Modo.CAMBIO;
		this._llaves = { id : id};
	}
	
	
	inicializar()
	{	
		
		this.presentador.consultarPorLlaves();
		//this.presentador.consultar();
		
		
	}
	
	get plantillaId()
	{
		return $("#plantillaId").val();
	}
	
	get auditoriaId()
	{
		return $("#auditoriaId").val();
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
//				
//				$(function () {
//					$("#fechaProgramadaInput").datepicker();
//					});
	}
	
//	crearColumnasGrid()
//	{
//		this.grid._columnas = [
//			{longitud:50, 	titulo:"",   	alias:"icono", alineacion:"D", itemRender:this.renderIcono},
//			{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"D" },
//			{longitud:200, 	titulo:"Nombre",   alias:"nombre", alineacion:"I" }, 		
//			{longitud:300, 	titulo:"Descripción",   alias:"descripcion", alineacion:"I" }, 	
//			{longitud:250, 	titulo:"Último uso",   alias:"ultimoUso", alineacion:"I" },
//			{longitud:250, 	titulo:"Fecha programada",   alias:"fechaProgramada", alineacion:"I" },
//			{longitud:250, 	titulo:"Fecha de alta",   alias:"fechaAlta", alineacion:"I" },	
//			{longitud:200, 	titulo:"Fecha de última modificación",   alias:"fechaModificacion", alineacion:"I" },
//			{longitud:100, 	titulo:"Estatus",   alias:"estatus", alineacion:"D", itemRender:this.renderEstatus}
//		]
//		
//		this.grid._origen="vista";
//		this.grid.manejadorEventos=this.manejadorEventos;
//		this.grid._colorSeleccion = COLOR_SELECCION;
//		this.grid._ajustarAltura = true;
//		this.grid._colorRenglon1 = COLOR_RENGLON1;	
//		this.grid._colorRenglon2 = COLOR_RENGLON2;	
//		this.grid._colorEncabezado1 = COLOR_ENCABEZADO1;
//		this.grid._colorEncabezado2 = COLOR_ENCABEZADO2;
//		this.grid._colorLetraEncabezado = COLOR_LETRA_ENCABEZADO;
//		this.grid._colorLetraCuerpo = COLOR_LETRA_CUERPO;
//		this.grid._regExtra=REGISTROS_EXTRA;
//		this.grid._bordesRedondeados = true;
//		this.grid._eliminarLineaVerticales=false;
//		//this.grid._presentacionGranTotal = "SI";
//		this.grid.render();		
//	}
//	
//	renderIcono(renglon, campoBase)
//	{    
//		var contenido = "";
//		var icono ="php/iconos/" + renglon.icono;
//		contenido += "<center><img src='" + icono + "' style='width:30px;height:30px;'></img></center>";
//	    return contenido;
//	}

//	btnBaja_onClick()
//	{ 
//		if(this.grid._selectedItem!=null)
//		{
//			var confirmacion = confirm("¿Esta seguro que desea eliminar el registro?")
//		    if (confirmacion)
//		    {
//		    		this.presentador.eliminar();
//		    }	
//		}
//		else
//			this.mostrarMensaje("Acción no válida","Seleccione un registro para eliminar.");
//	}
//	
//	btnAlta_onClick()
//	{
//		this.modo = "ALTA";
//		this.ocultarIndicador();
//		this.limpiarFormulario();	
//		this.mostrarFormulario();
//		$('#nombreInput').focus();
//		this.presentador.consultarCategorias();
//	
//		
//	}
//
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
//	btnEjecutar_onClick()
//	{
//		if(this.grid._selectedItem!=null)
//		{
//			var submitForm = getNewSubmitForm("auditoria.php");
//			createNewFormElement(submitForm, "plantillaId", this.grid._selectedItem.id);
//			submitForm.target= "auditoria" + Math.floor(Math.random()*10000);
//			submitForm.submit();
//		}
//	}
//	
//	btnConsulta_onClick()
//	{	
//		this.presentador.consultar();
//	}	
	
//	btnGuardarFormulario_onClick()
//	{		
//		if(this.seccionEdicion!=null)
//			this.seccionEdicion.preguntas = this.listaPreguntas.preguntas;
//		 if(this.datosValidos())
//		 {
//			if(this.modo=='ALTA')
//				this.presentador.insertar();
//			else
//				this.presentador.actualizar();
//		 }		
//		
//	}
//	
//	btnSalir_onClick()
//	{
//		var confirmacion = confirm("¿Esta seguro que desea salir?")
//	    if (confirmacion)
//	    	{
//		    	
//	    	}
//	}
	
//	btnSalirFormulario_onClick()
//	{	
//		this.confirmar("¿Esta seguro que desea salir?",this,this.cerrarVentana,null);
////		var confirmacion = confirm("¿Esta seguro que desea salir?")
////	    if (confirmacion)
////    	{
////	    	this.cerrarVentana();
////    	}
//	}	
//	
//	cerrarVentana(cerrar)
//	{
//		this.ventana.close();
//	}
	
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
		
		
		this.calcularPorcentajes();
		
		if(this._modo==Modo.CAMBIO)
		{
			this.modeloEdicion.plantillaId = this.modeloEdicion.id;
			this.modeloEdicion.auditoriaId = this.auditoriaId;
			this.modeloEdicion.seccionId = this.seccionId;
			this.presentador.consultarValores();
		}
	}
	
	cambiarSeccion(event)
	{
		var indice = $("#secciones").prop('selectedIndex');
		this.listaPreguntas.mostrarSeccion(indice);
		if(this._modo==Modo.CAMBIO)
			this.presentador.consultarValores();
	}
	
	siguiente()
	{
		this.funcion = "siguente";
		this.guardar();
	
		
	}
	
	mostrarSiguiente()
	{
		var indice = this.listaPreguntas.siguiente();
		$('#secciones').prop('selectedIndex',indice);
		if(this._modo==Modo.CAMBIO)
			this.presentador.consultarValores();
	}
	
	mostrarAnterior()
	{
		var indice = this.listaPreguntas.atras();
		$('#secciones').prop('selectedIndex',indice);
		if(this._modo==Modo.CAMBIO)
			this.presentador.consultarValores();
	}
	
	atras()
	{
		this.funcion = "atras";
		this.guardar();
		
		
	}
	
	guardar()
	{
		var campo = this.campoVacio();
		if(campo==null)
		{
			this.presentador.guardar();
		}
		else
		{
			var _this = this;
		  swal({
	            title: "¿Esta seguro de querer guardar?",
	            text: "Falta información por llenar: </br>" + campo.texto,
	            html: true,
	            type: "warning",
	            showCancelButton: true,
	            confirmButtonColor: "#ae3e9e",
	            confirmButtonText: "Si, guardar!!",
	            cancelButtonText  : "Cancelar",
	            closeOnConfirm: true
	        },
	        function(){
	        	_this.presentador.guardar();
	        });
		}
	}
	
	
	get empresaId()
	{
		
//		var preguntas = this.preguntas;
//		if(preguntas.length>0)
//		{
//			var preguntaEmpresa = preguntas[0];
//			if(preguntaEmpresa.tipo=="cat")
//				return preguntaEmpresa.valor;
//			
//		}
		return this.listaPreguntas.empresaId;
		
	}
	
	set modeloDatos(modeloDatos)
	{
		$("#referenciaDiv").show();
		$("#referenciaLabel").html("REFERENCIA: " +modeloDatos.referencia);
		for(var i=0; i < modeloDatos.preguntas.length; i++)
		{
			var pregunta = modeloDatos.preguntas[i];
			this.listaPreguntas.setValor(pregunta.seccionId, pregunta.preguntaId, pregunta.valor);
		}
	}
	
	get preguntas()
	{
		var preguntas = [];
		var seccionIndice = $("#secciones").prop("selectedIndex");
		//var seccionSeleccionada =  this.listaPreguntas.secciones[seccionIndex].id;
		if(seccionIndice>=0 && seccionIndice<   this.listaPreguntas.secciones.length)
		{
			var seccion = this.listaPreguntas.secciones[seccionIndice];
			var componentesPreguntas = seccion.componentes;
			for(var i=0;  i  < componentesPreguntas.length;i++ )
			{
				var componente = componentesPreguntas[i];
				var pregunta = componente.pregunta;
				pregunta.respuestas = componente.respuestas;
				//pregunta.respuestasNo = componente.respuestasNo;
				pregunta.valor = componente.valor;
				preguntas.push(pregunta);
			}
			
		}
		return preguntas;
	}
	
	get preguntasAuditoria()
	{
		var preguntas = [];
		var seccionIndice = $("#secciones").prop("selectedIndex");
		if(seccionIndice>=0 && seccionIndice<   this.listaPreguntas.secciones.length)
		{
			var seccion = this.listaPreguntas.secciones[seccionIndice];
			var componentesPreguntas = seccion.componentes;
			for(var i=0;  i  < componentesPreguntas.length;i++ )
			{
				var componente = componentesPreguntas[i];
				var pregunta ={ id: componente.pregunta.id, valor:  componente.valor, respuestas: componente.respuestas}; ;
				preguntas.push(pregunta);
			}
			
		}
		return preguntas;
	}
	
    get seccionId()
    {
    	return $("#secciones").val();
    }
	
	get modelo()
	{
		 var modelo = 
		 {		
			 empresaId: this.empresaId,		
			 seccionId:this.seccionId,	
			 plantillaId:this.plantillaId,	
			 preguntas: this.preguntasAuditoria
		 };
		 if(this.modo=="CAMBIO" && this.modeloEdicion!=null)
			 modelo.id = this.modeloEdicion.id;
		 return modelo;
	 }
	
	mostrarReferencia()
	{
		$("#referenciaDiv").show();
		$("#referenciaLabel").html("REFERENCIA: " +this.modeloEdicion.referencia);
	}
	
	campoVacio()
	{

		var preguntas = this.preguntas;
		for(var i=0; i < preguntas.length; i++)
		{
			var pregunta = preguntas[i];
			if(pregunta.tipo=="sn")
			{
				if(pregunta.valor=="")
					return pregunta;
//				else if (pregunta.valor=="S")
//				{
//					if(!this.contestadas(pregunta.respuestas))
//						return pregunta;
//					
//				}
//				else if (pregunta.valor=="N")
//				{
//					if(!this.contestadas(pregunta.respuestas))
//						return pregunta;
//				}
			}
			else
			{
				if(pregunta.tipo!="e")
					if(pregunta.valor=="")
						return pregunta;
			}
		}
		
		return null;
	}	
	
	contestadas(respuestas)
	{
		if(respuestas.length==0)
			return true;
		
		for(var i = 0; i < respuestas.length; i++)
		{
			var respuesta = respuestas[i];
			if(respuesta.valor!=null && respuesta.valor!="" && respuesta.valor!=0)
				return true;
		}
		return false;
	}

//	limpiarFormulario()
//	{
//		$('#nombreInput').val("");
//		$('#descripcionInput').val("");
//		$('#fechaProgramadaInput').val("");
//		$('#listaPreguntas').html("");
//		$("#tituloSeccionDiv").hide();
//		$("#botonesSuperiores").hide();
//		$("#botonesAgregarDiv").hide();
//		$("#guardarButton").hide();
//		$("#ayudaPreguntas").hide();
//	}
	
//	agregarSeccion()
//	{
//		this.listaSecciones.agregarSeccion("");
//	}
//	
//	agregarPregunta()
//	{
//		this.listaPreguntas.agregarPregunta();
//	}
//	
//	agregarTexto()
//	{
//		this.listaPreguntas.agregarTexto();
//	}
//	
//	agregarFecha()
//	{
//		this.listaPreguntas.agregarFecha();
//	}
//	
//	agregarEncabezado()
//	{
//		this.listaPreguntas.agregarEncabezado();
//	}
//	
//	agregarMapa()
//	{
//		this.listaPreguntas.agregarMapa();
//	}
//	
//	agregarRespuesta()
//	{
//		this.listaRespuestas.agregarRespuesta();
//	}
//	
//	eliminarSeccion(event, seccionId)
//	{
//		if(this.listaSecciones.secciones.length>1)
//		{
//			//this.listaSecciones.eliminarSeccion(seccionId);
//			this.confirmar("¿Desea eliminar esta sección?",this.listaSecciones,this.listaSecciones.eliminarSeccion,seccionId);
//		}
//		else
//			this.mostrarMensaje("Error","Es necesario contar al menos con una sección. ");		
//	}
//	
//	eliminarPregunta(event, preguntaId)
//	{
//		this.confirmar("¿Desea eliminar esta pregunta?",this.listaPreguntas,this.listaPreguntas.eliminarPregunta,preguntaId);
//		//this.listaPreguntas.eliminarPregunta(preguntaId);
//	}
//	
//	eliminarRespuesta(event, respuestaId)
//	{
//		this.confirmar("¿Desea eliminar esta respuesta?",this.listaRespuestas,this.listaRespuestas.eliminarRespuesta,respuestaId);
//		//this.listaRespuestas.eliminarRespuesta(respuestaId);
//	}
	
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
		
		this.calcularPorcentajes();
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
	
	calcularPorcentajes()
	{
		var preguntas = this.preguntas;
		var indicesEncabezados = [];
		for(var i=0; i < preguntas.length; i++)
		{
			var pregunta = preguntas[i];
			if(pregunta.tipo=="e")
			{
				indicesEncabezados.push(i);
			}
		}
		
		var puntuaciones = [];
		for(var i=0; i < indicesEncabezados.length; i++)
		{
			var indice = indicesEncabezados[i];
			var puntuacion = this.calcularPorcenjateEncabezado(indice);
			puntuaciones.push(puntuacion);
		}
		
		this.calcularPuntuacionSeccion(puntuaciones);
	}
	
	calcularPuntuacionSeccion(puntuaciones)
	{
		var x = 0;
		var y = 0;
		var porcentaje = 0;
		for(var i=0; i < puntuaciones.length; i++)
		{
			var puntuacion = puntuaciones[i];
			x+=puntuacion.x;
			y+=puntuacion.y;
		}
		
		if(y!=0)
			porcentaje = x / y * 100;	
		else
			porcentaje = 0;
		
		var textoPorcentaje = parseFloat(porcentaje).toFixed(2);
		var decimales = textoPorcentaje.split(".")[1];
		if(decimales=="00")
		{
			textoPorcentaje =  textoPorcentaje.split(".")[0];
		}
		
		var puntuacionTexto = x + "/" + y + " (" +  textoPorcentaje + "%)";
		
		var indice = $("#secciones").prop('selectedIndex');
		var seccionId = this.listaPreguntas.secciones[indice].id;
		$("#listaPreguntas_labelPuntuacion"  +seccionId).html(puntuacionTexto);
		
	}
	
	calcularPorcenjateEncabezado(indice)
	{
		var x = 0;
		var y = 0;
		var porcentaje = 0;
		var preguntas = this.preguntas;
		for(var i=indice+1; i < preguntas.length; i++)
		{
			var pregunta = preguntas[i];
			if(pregunta.tipo=="e")
				break;
			else
			{
				if(pregunta.tipo=="sn")
				{
					if(pregunta.valor=="S")
					{
						y+= pregunta.peso;
						var pesoRespuestas = pregunta.peso - this.listaPreguntas.getPesoRespuestas(pregunta.id);
						x+= pesoRespuestas;
					}
					else if(pregunta.valor=="N")
					{
						y+= pregunta.peso;
					}
				}
			}
		}
		var encabezado = preguntas[indice];
		
		if(y!=0)
			porcentaje = x / y * 100;	
		else
			porcentaje = 0;
		
		var textoPorcentaje = parseFloat(porcentaje).toFixed(2);
		var decimales = textoPorcentaje.split(".")[1];
		if(decimales=="00")
		{
			textoPorcentaje =  textoPorcentaje.split(".")[0];
		}
		
		var puntuacionTexto = x + "/" + y + " (" +  textoPorcentaje + "%)";
		if(encabezado.tipo=="e")
			this.listaPreguntas.setValor(this.seccionId,encabezado.id,puntuacionTexto);
		
		var puntuacion = {x: x, y : y, porcentaje : porcentaje};
		return puntuacion;
	}
	
}
var vista = new AuditoriaVista();
$(document).ready(function() 
{
	vista.inicializar();
});
