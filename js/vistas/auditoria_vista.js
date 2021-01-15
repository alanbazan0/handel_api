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
		
		this._auditoria = {};
		
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
		
		this.consultarPlantillaId();
	
		//this.presentador.consultar();
		
	}
	
	consultarPlantillaId()
	{
		if(this._modo==Modo.CAMBIO)
			this.presentador.consultarPlantillaId();
		else
			this.plantillaId = $("#plantillaId").val();
	}
	
	set plantillaId(plantillaId)
	{
		this._plantillaId = plantillaId;
		this.presentador.consultarPorLlaves();
	}
	
	get plantillaId()
	{
		return this._plantillaId;
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
			id:this._plantillaId	
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
		
		 $(document).attr("title", this.modeloEdicion.nombre);

		$('#titulo').html(this.modeloEdicion.nombre);
		//this.presentador.consultarCategorias();
		
		this.listaPreguntas.secciones = this.modeloEdicion.secciones;
		
		$("#contenedor").show();
		
		$('#secciones').show();
		$('#secciones').empty();
		$.each(this.modeloEdicion.secciones, function(i, p) {
		    $('#secciones').append($('<option></option>').val(p.id).html(p.texto));
		});
		
		
		
		if(this._modo==Modo.CAMBIO)
		{
			this.modeloEdicion.plantillaId = this.modeloEdicion.id;
			this.modeloEdicion.id = this.auditoriaId;
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
	
	mostrarObservaciones()
	{
		var _this = this;
		this.mostrarFormularioHTML(HANDEL_API+"/html/formularios/observaciones_seccion.php",this, null, function()
		{
			var seccionActual = _this.listaPreguntas.seccionActual;
			$("#hallazgoInput").val(seccionActual.hallazgo);
			$("#recomendacionInput").val(seccionActual.recomendacion);
			//$("#responsableSelect").val(seccionActual.responsable);
			
				
			var tipoAuditoriaId = this.tipoAuditoriaId;
			if(tipoAuditoriaId=="AI")	
			{
				$("#notificacionCheck").prop('checked', true);
				$("#reporteCheck").prop('checked', true);
			}
			else
			{
				if(seccionActual.reporte)
					$("#reporteCheck").prop('checked', true);
				else
					$("#reporteCheck").prop('checked', false);
				if(seccionActual.notificacion)
					$("#notificacionCheck").prop('checked', true);
				else
					$("#notificacionCheck").prop('checked', false);
			}
			
			_this.consultarUsuariosSeccion();
			 
			
		},null,"","","guardarButton",function()
		{
			var seccionActual = _this.listaPreguntas.seccionActual;
			seccionActual.hallazgo = $("#hallazgoInput").val();
			seccionActual.recomendacion = $("#recomendacionInput").val();
			seccionActual.responsable = $("#responsableSelect").val();
			seccionActual.reporte =  $("#reporteCheck").is(':checked')?1:0;
			seccionActual.notificacion =  $("#notificacionCheck").is(':checked')?1:0;
			$("#modalAlta").modal('hide');
		});
	}
	
	mostrarObservacionesGenerales()
	{
		var _this = this;
		this.mostrarFormularioHTML(HANDEL_API+"/html/formularios/observaciones_generales.php",this, null, function()
		{
			$("#observacionesInput").val(this._auditoria.observaciones);
			
		},null,"","","guardarButton",function()
		{
			_this._auditoria.observaciones = $("#observacionesInput").val();
			$("#modalAlta").modal('hide');
		});
	}
	
	mostrarBuenasPracticas()
	{
		var _this = this;
		this.mostrarFormularioHTML(HANDEL_API+"/html/formularios/buenas_practicas.php",this, null, function()
		{
			$("#buenasPracticasInput").val(this._auditoria.buenasPracticas);
			
		},null,"","","guardarButton",function()
		{
			_this._auditoria.buenasPracticas = $("#buenasPracticasInput").val();
			$("#modalAlta").modal('hide');
		});
	}
	
	consultarUsuariosSeccion()
	{
		this.cargandoOpciones("#responsableSelect");
		this.presentador.consultarUsuariosSeccion();
		
	}
	
	mostrarSiguiente()
	{
		var indice = this.listaPreguntas.siguiente();
		$('#secciones').prop('selectedIndex',indice);
		if(this._modo==Modo.CAMBIO)
			this.presentador.consultarValores();
	}
	
	mostrarFinalizacion()
	{
		 swal({
	            title: "Auditoria terminada",
	            text: "",
	            html: true,
	            type: "success",
	            showCancelButton: true,
	            confirmButtonColor: "#ae3e9e",
	            confirmButtonText: "Salir de auditoria",
	            cancelButtonText  : "No salir",
	            closeOnConfirm: true
	        },
	        function(){
	        	window.close();
	        });
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
	

	finalizar()
	{
		this.funcion = "finalizar";
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
		return this.listaPreguntas.empresaId;
		
	}
	
	get tipoAuditoriaId()
	{
		return this.listaPreguntas.tipoAuditoriaId;
	}
	
	set modeloDatos(modeloDatos)
	{
		this._auditoria = modeloDatos;
		$("#referenciaDiv").show();
		if(modeloDatos!=null)
		{
			this.listaPreguntas.empresaId = modeloDatos.empresaId;
			this.listaPreguntas.tipoAuditoriaId = modeloDatos.tipoAuditoriaId;
			$("#referenciaLabel").html(modeloDatos.referencia);
			this.listaPreguntas.seccionActual = modeloDatos.seccion;
			if( modeloDatos.preguntas!=null)
			{
				for(var i=0; i < modeloDatos.preguntas.length; i++)
				{
					var pregunta = modeloDatos.preguntas[i];
					this.listaPreguntas.setValor(pregunta.seccionId, pregunta.preguntaId, pregunta.valor, pregunta.responsable, pregunta.reporte, pregunta.notificacion);
		//			if(pregunta.tipo="sn")
		//			{
		//				//if(pregunta.valor=="S")
						this.listaPreguntas.setValoresRespuestasSi(pregunta.seccionId, pregunta.preguntaId, pregunta.respuestas_si);
						//else if(pregunta.valor=="N")
						this.listaPreguntas.setValoresRespuestasNo(pregunta.seccionId, pregunta.preguntaId, pregunta.respuestas_no);
					//}
				}
			}
			
			
		}

		this.calcularPorcentajes();
	}
	
	get seccionSeleccionada()
	{
		var seccionIndice = $("#secciones").prop("selectedIndex");
		if(seccionIndice>=0 && seccionIndice<   this.listaPreguntas.secciones.length)
		{
			var seccion = this.listaPreguntas.secciones[seccionIndice];
			return seccion;
		}
		return null;
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
				var pregunta =  Object.assign({}, componente.pregunta);
//				pregunta.respuestas_si = [];
//				pregunta.respuestas_no = [];
				//if(pregunta.valor=="S")
				pregunta.respuestas_si = componente.respuestasSi;
				//else if(pregunta.valor=="N")
				pregunta.respuestas_no = componente.respuestasNo;
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
				if(componente.pregunta.tipo!="cat")
				{
					var pregunta ={ id: componente.pregunta.id, 
									valor:  componente.valor,
									puntos : componente.pregunta.puntos,
									puntosTotal : componente.pregunta.puntosTotal,
									porcentaje : componente.pregunta.porcentaje,
									respuestas_si: componente.respuestasSi,
									respuestas_no: componente.respuestasNo,
									responsable : componente.responsable,
									reporte: componente.reporte,
									notificacion: componente.notificacion
									}; 
					preguntas.push(pregunta);
				}
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
			 tipoAuditoriaId: this.tipoAuditoriaId,		
			 seccionId:this.seccionId,	
			 plantillaId:this.plantillaId,	
			 seccion: this.seccionActual,
			 observaciones : this._auditoria.observaciones,
			 buenasPracticas : this._auditoria.buenasPracticas
			 //preguntas: this.preguntasAuditoria
		 };
		 if(this.modo=="CAMBIO" && this.modeloEdicion!=null)
			 modelo.id = this.modeloEdicion.id;
		 return modelo;
	 }
	
	get seccionActual()
	{
		var seccionActual = this.listaPreguntas.seccionActual;;
		var seccion =  {
			id : seccionActual.id,
			hallazgo : seccionActual.hallazgo,
			recomendacion: seccionActual.recomendacion,
			responsable :  seccionActual.responsable,
			notificacion :  seccionActual.notificacion,
			reporte :  seccionActual.reporte,
			puntos :  seccionActual.puntos,
			puntosTotal :  seccionActual.puntosTotal,
			porcentaje :  seccionActual.porcentaje,
			preguntas : this.preguntasAuditoria
		}
		return seccion;
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
	
//	guardarRespuestas()
//	{
//		if(this.preguntaEdicion!=null)
//		{
//			var tipo = $('#ventanaRespuestasContenedor').data("tipo");
//			if(tipo=="SI")
//				this.preguntaEdicion.respuestas_si = this.listaRespuestas.respuestas;
//			else
//				this.preguntaEdicion.respuestas_no = this.listaRespuestas.respuestas;
//		}
//			
//			
//		$('#ventanaRespuestasContenedor').fadeOut(this.velocidadAnimacion);
//	}
	
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
			var puntuacion = this.calcularPorcenjateEncabezado(indice,this.seccionSeleccionada);
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
		//if(indice!=0)
		$("#listaPreguntas_labelPuntuacion"  +seccionId).html(puntuacionTexto);
		
		this.listaPreguntas.secciones[indice].puntos = x;
		this.listaPreguntas.secciones[indice].puntosTotal = y;
		this.listaPreguntas.secciones[indice].porcentaje = textoPorcentaje;
		//else
		//	$("#listaPreguntas_labelPuntuacion"  +seccionId).html("");
		
	}
	
	calcularPorcenjateEncabezado(indice, seccion)
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
						var pesoRespuestasSeleccionadas = this.listaPreguntas.getPesoRespuestas(seccion,pregunta.id);
						var pesoRespuestas = pregunta.peso - pesoRespuestasSeleccionadas;
						x+= pesoRespuestas;
					}
					else if(pregunta.valor=="N" || pregunta.valor=="" || pregunta.valor==undefined)
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
		{
			//var encabezado = ArrayUtils.searchWithValues("seccionId, id",[this.seccionId, encabezado.id], this.listaPreguntas.preguntas);
			var pregunta = this.listaPreguntas.getPregunta(this.seccionId, encabezado.id);
			if(pregunta!=null)
			{
				pregunta.puntos = x;
				pregunta.puntosTotal = y;
				pregunta.porcentaje = porcentaje;
			}
			this.listaPreguntas.setValor(this.seccionId,encabezado.id,puntuacionTexto);
		}
		
		var puntuacion = {x: x, y : y, porcentaje : porcentaje};
		return puntuacion;
	}
	
	set usuariosSeccion(usuarios)
	{
		//this.cargarOpciones("#resposableSelect", usuarios,null,"usuarioId");	
		this.cargarOpciones("#responsableSelect", usuarios,Modo.CAMBIO, this.listaPreguntas.seccionActual, "responsable", "", "nombreCompleto")
//		var responsableId =this.listaPreguntas.seccionActual.responsable;
//		if(responsableId!=null)
//			if(responsableId!="")
//				$("#resposableSelect").val(responsableId);
	}
	
}
var vista = new AuditoriaVista();
$(document).ready(function() 
{
	vista.inicializar();
});
