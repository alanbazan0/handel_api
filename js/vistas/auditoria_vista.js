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
		this.listaPreguntas.contexto = this;
		this.listaPreguntas.funcionConsultarUsuarios = this.consutarUsuarios;
		//this.listaRespuestas = new ListaRespuestas("listaRespuestas");
		this._categorias = [];
		this._estandares = [];
		this.preguntaEdicion = null;
		this.seccionEdicion = null;
		this.velocidadAnimacion = 400;
		this._selectizeSecciones = null;
		this._presenciaInterval = null;
		this._registroInterval  = null;
		
		this._modo = $("#modo").val()
		if(this._modo==""  || this._modo==undefined)
			this._modo = Modo.ALTA;
		
		
		this._auditoria = {};
		
		var fecha = new Date();
		this._time = fecha.getTime();	
	}
	
	get time()
	{
		return this._time;
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
		
		this.crearSelectSecciones(null);
		this.iniciarPollingPresencia();
		
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
		this.detenerPollingPresencia();
		this.iniciarPollingPresencia();
		if(this._modo==Modo.CAMBIO)
			this.presentador.consultarValores();
		else
			this.calcularPorcentajes();
		
	}
	
	siguiente()
	{
		this.funcion = "siguente";
		this.guardar();
	}
	
	mostrarXRay()
	{
		//var _this = this;
		this.mostrarFormularioHTML(HANDEL_API+"/html/modales/xray.php",this, null, function()
		{
			var _this = this;
			$("#exportarButton").click(function(){
				_this.exportarXRay();
			});
			
			$("#correoButton").click(function(){
				_this.mostrarEnviarCorreoXRay();
			});
			
			
			
			 $("#consultarRecomendacionesButton").click(function(){
				_this.filtrarRecomendaciones();
			});
			 this.crearTablaRecomendaciones();
			 this.consultarHallazgosSecciones();
			
		},null,"xRayModal","","guardarButton",function()
		{
			
		});
	}
	
	consultarHallazgosSecciones()
	{
		this.presentador.consultarHallazgosSecciones();
	}
	
	set hallazgosSecciones(hallazgos)
	{
		
		this._hallazgosSecciones =  hallazgos;
		
		var usuarios = this.getUsuarios(this._hallazgosSecciones);
		this.cargarOpciones("#usuariosXRaySelect", usuarios,"", null, "id", null, "nombreCompleto",false)
		
		this.filtrarRecomendaciones();
	}
	
	filtrarRecomendaciones()
	{
		var _this = this;
		var usuarioId = $("#usuariosXRaySelect").val();
		var recomendaciones = [];
		if(usuarioId != "" && usuarioId != "-1")
		{
			recomendaciones = this._hallazgosSecciones;
			recomendaciones = ArrayUtils.filterWithValues("responsableId",[usuarioId],recomendaciones);
		}
		else if(usuarioId == "-1")
		{
			recomendaciones = this._hallazgosSecciones;
			recomendaciones = ArrayUtils.filterWithValues("responsableId",[undefined],recomendaciones);
		}
		else
			recomendaciones = this._hallazgosSecciones;
		this.mostrarIndicador();
		this.hallazgosTabla.registros = recomendaciones;
	
		 setTimeout(function()
			{
				
				_this.ocultarIndicador();
            }, 1000);
          
        this._usuarioXRay = usuarioId;
	}
	
	mostrarEnviarCorreoXRay()
	{
		var _this = this;
		this.mostrarFormularioHTML(HANDEL_API+"/html/modales/correo_xray.php",this, null, function()
		{
			if(this.hallazgosTabla.registros.length > 0)
			{
				
				$("#usuarioXRaySpan").html(this.hallazgosTabla.registros[0].responsableNombreCompleto);
			}
			
			$("#enviarCorreoXRayButton").click(function()
			{
				_this.enviarCorreoXRay();
			});
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
					
			if(this._usuariosCorreo==null)
				this.consultarUsuariosCorreo();
			else
				this.usuariosCorreo = this._usuariosCorreo;
			
		},null,"correoXRayModal","","guardarButton",function()
		{
			
		});
	}
	
	consultarUsuariosCorreo()
	{
		this.presentador.consultarUsuariosCorreo();
	}
	
	crearTablaRecomendaciones()
	{
		this.hallazgosTabla = new Tabla("recomendacionesTabla");
		this.hallazgosTabla.alto = 250;
		this.hallazgosTabla.buscar = false;
		this.hallazgosTabla.paginacion = false;
		this.hallazgosTabla.columnas = [
			{longitud:200, 	titulo:"Hallazgo",   alias:"hallazgo", alineacion:"I"},
			{longitud:200, 	titulo:"Recomendación",   alias:"recomendacion", alineacion:"I"},
			//{longitud:200, 	titulo:"Sección",   alias:"texto", alineacion:"I"},
			{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"D" ,itemRenderer:this.renderLogoResponsable},
			{longitud:200, 	titulo:"Responsable",   alias:"responsableNombreCompleto", alineacion:"I",class: "desc" }, 
			{longitud:50, 	titulo:"Reporte",   alias:"reporte", alineacion:"C", itemRenderer:this.renderReporte},
			{longitud:50, 	titulo:"Notificación",   alias:"notificacion", alineacion:"C", itemRenderer:this.renderNotificacion},
		
		]
	
		/*this.hallazgosTabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-1 botones-icon btn btn-sm float-left btn-success active'><span  data-toggle='tooltip' class='fas fa-pencil-alt fa-lg'></span></button>"+
												"<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";

*/
		
		this.hallazgosTabla.textoTablaVacia = "No hay hallazgos";
		this.hallazgosTabla.textoSinRegistros= "No hay hallazgos";
		this.hallazgosTabla.registros = [];
		
		//this._recomendacionesXRay = this.getRecomendaciones();
		//var usuarios = this.getUsuarios(this._recomendacionesXRay);
		//this.cargarOpciones("#usuariosXRaySelect", usuarios,"", null, "id", null, "nombreCompleto",false)
		//this.filtrarRecomendaciones();
		
		//this.hallazgosTabla.registros = this._recomendacionesXRay;
	}
	
	getUsuarios(recomendaciones)
	{
		var usuarios = [];
		for(var i = 0; i < recomendaciones.length; i++)
		{
			var recomendacion = recomendaciones[i];
			if(!ArrayUtils.existsWithValues( "id",[recomendacion.responsableId], usuarios))
			{
				var usuario = {
					id : recomendacion.responsableId,
					nombreCompleto : recomendacion.responsableNombreCompleto.trim()
				};
				
				if(usuario.id != "" && usuario.id != null)
					usuarios.push(usuario);
			}
		}
		/*usuarios = ArrayUtils.sortBy("nombreCompleto", usuarios);
		usuarios.splice(0, 0, {id:"", nombreCompleto:"Todos"});
		
		var sinAsignar = ArrayUtils.searchWithValues( "id",[null], recomendaciones);
		if(sinAsignar!=null)
			usuarios.splice(1, 0, {id:-1, nombreCompleto:"Sin asignar"});
		*/
		return usuarios;
	}
	
	getRecomendaciones()
	{
		var recomendaciones =[];
		for(var i=0; i < this.listaPreguntas.secciones.length; i++)
		{
			var seccion = this.listaPreguntas.secciones[i];
			if(seccion.observacionesSeccion!=undefined)
			{
				for(var o = 0; o < seccion.observacionesSeccion.length; o++)
				{
					var observacion = seccion.observacionesSeccion[o];
					var recomendacion ={
						hallazgo: observacion.hallazgo,
						recomendacion: observacion.recomendacion,
						reporte: observacion.reporte,
						notificacion: observacion.notificacion,
						responsableId  : observacion.responsableId,
						responsableNombre  : observacion.responsableNombre,
						responsableApellido  : observacion.responsableApellido,
						responsableNombreCompleto  : observacion.responsableNombreCompleto,
						departamentoNombre  : observacion.departamentoNombre,
						fotoPerfil  : observacion.fotoPerfil,
					};
					if(recomendacion.hallazgo!="" && recomendacion.recomendacion!="")
						recomendaciones.push(recomendacion);
				}
			}
		}	
		
		var preguntasTodas = this.preguntasTodas;
		
		for(var p = 0; p < preguntasTodas.length; p++)
		{
			var pregunta = preguntasTodas[p];
			if(pregunta.tipo == "sn")
			{
				var seccion = this.listaPreguntas.getSeccion(pregunta.seccionId);
				var componentes = seccion.componentes;
				var componente = ArrayUtils.searchWithValues("_pregunta.id",[pregunta.id], componentes);
				if(componente!=null)
				{
					var modeloPregunta = componente._pregunta;
					if(pregunta.valor == "S")
					{
						for(var r = 0; r < pregunta.respuestas_si.length; r++)
						{
							var respuesta = pregunta.respuestas_si[r];
							if(respuesta.valor == 1)
							{
								var modeloRespuesta = ArrayUtils.searchWithValues("id",[respuesta.id],modeloPregunta.respuestas_si);
								var usuario = ArrayUtils.searchWithValues("id",[respuesta.responsable],this._usuarios);
								var recomendacion = {
									hallazgo: modeloRespuesta.hallazgo,
									recomendacion: modeloRespuesta.recomendacion,
									reporte: respuesta.reporte,
									notificacion: respuesta.notificacion,
									responsableId  : respuesta.responsable
									
								};
								if(usuario!=null)
								{
									recomendacion.responsableNombreCompleto  = usuario.nombreCompleto;
									recomendacion.fotoPerfil  = usuario.fotoPerfil;
								}
								else
								{
									recomendacion.responsableNombreCompleto  = "";
									recomendacion.fotoPerfil  = "";
									recomendacion.responsableId = null;
								}
								if(recomendacion.hallazgo!="" && recomendacion.recomendacion!="")
									recomendaciones.push(recomendacion);
							}
						}
					}
					else
					{
						var usuario = ArrayUtils.searchWithValues("id",[pregunta.responsable],this._usuarios);
						var recomendacion ={
							hallazgo: modeloPregunta.hallazgo,
							recomendacion: modeloPregunta.recomendacion,
							reporte: pregunta.reporte,
							notificacion: pregunta.notificacion,
							responsableId  : pregunta.responsable
						};
						if(usuario!=null)
						{
							recomendacion.responsableNombreCompleto  = usuario.nombreCompleto;
							recomendacion.fotoPerfil  = usuario.fotoPerfil;
						}
						else
						{
							recomendacion.responsableNombreCompleto  = "";
							recomendacion.fotoPerfil  = "";
							recomendacion.responsableId = null;
						}
						if(recomendacion.hallazgo!="" && recomendacion.recomendacion!="")
							recomendaciones.push(recomendacion);	
					}
				}
			}				
		}
	 	return recomendaciones;
	}
	
	mostrarObservaciones()
	{
		var _this = this;
		this.mostrarFormularioHTML(HANDEL_API+"/html/modales/observaciones_seccion.php",this, null, function()
		{
			var seccionActual = _this.listaPreguntas.seccionActual;
			if(seccionActual.observacionesSeccion==null)
			 	seccionActual.observacionesSeccion = [];
			$("#seccionLabel").html(seccionActual.texto);
			this.crearTablaObservaciones(true);
			this.observacionesTabla.registros = seccionActual.observacionesSeccion;
			
				this.inicializarEventosBotonesTablaObservaciones("#" + this.observacionesTabla._id+"Table tbody",this.observacionesTabla.datatable.DataTable());

			$("#agregarObservacionButton").click(function(){_this.mostrarObservacion(Modo.ALTA,seccionActual);});
	
			_this.calcularPorcentajes();
			$('#modalAlta').on('hide.bs.modal', function (e) {
			  	_this.calcularPorcentajes();
			});
			
			/*$("#hallazgoInput").val(seccionActual.hallazgo);
			$("#recomendacionInput").val(seccionActual.recomendacion);
			
				
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
			
			_this.consultarUsuariosSeccion();*/
			 
			
		},null,"","","guardarButton",function()
		{
			var seccionActual = _this.listaPreguntas.seccionActual;
			seccionActual.hallazgo = $("#hallazgoInput").val();
			seccionActual.recomendacion = $("#recomendacionInput").val();
			seccionActual.responsable = $("#responsableSelect").val();
			seccionActual.reporte =  $("#reporteCheck").is(':checked')?1:0;
			seccionActual.notificacion =  $("#notificacionCheck").is(':checked')?1:0;
			seccionActual.valor =  $("#valorObservacionInput").val();
			//seccionActual.observaciones = _this.observacionesSeccion;
			$("#modalAlta").modal('hide');
			//_this.calcularPorcentajes();
		});
	}
	
	mostrarObservacion(modo,seccionActual, observacion)
	{
		var _this = this;
		this.modoObservacion = modo;
		this.mostrarFormularioHTML(HANDEL_API+"/html/modales/observacion_seccion.php",this, null, function()
		{
			$("#seccionObservacionLabel").html(seccionActual.texto);
			this.inicializarValidacionesObservacion();
			if(modo==Modo.CAMBIO)
				_this.modeloObservacion = observacion;
			
			this.consultarUsuariosSeccion();
		
			$("#valorObservacionInput").on("keyup",function(){	
				
				_this.actualizarPuntosObservacion(seccionActual);
			});
			$("#valorObservacionInput").on("change",function(){	
				
				_this.actualizarPuntosObservacion(seccionActual);
			});
			
			
			_this.calcularPorcentajes();
			_this.actualizarPuntosObservacion(seccionActual);
			
		},null,"observacionModal","","guardarObservacionButton",function()
		{
			$("#observacionFormulario").submit();
		});	
		
	}
	
	actualizarPuntosObservacion(seccionActual)
	{
		var val = $("#valorObservacionInput").val();
		
		var totalProvisional = seccionActual.puntosTotal;
		if(val!="") 
			totalProvisional+=parseInt(val);
		
		var porcentaje = 0;
		if(totalProvisional!=0)
			porcentaje = seccionActual.puntos / totalProvisional * 100;	
		else
			porcentaje = 0;
		
		var textoPorcentaje = parseFloat(porcentaje).toFixed(2);
		var decimales = textoPorcentaje.split(".")[1];
		if(decimales=="00")
		{
			textoPorcentaje =  textoPorcentaje.split(".")[0];
		}	
			
		$("#puntuacionPreliminarObservacion").html(seccionActual.puntos + "/" + totalProvisional +  " (" + textoPorcentaje+"%)");
	}
	
	inicializarValidacionesObservacion()
	{
		var _this = this;
		jQuery("#observacionFormulario").validate({
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
                "hallazgoInput": {
                    required: !0
                },
                "recomendacionInput": {
                    required: !0
                }
                
            },
            messages: {
                "hallazgoInput": "Por favor ingrese un hallazgo",
                "recomendacionInput": "Por favor ingrese una recomendación"
                	
                
            },
            submitHandler:function (form) {
            	 _this.guardarObservacion();
            }
        });
	}
	
	set modeloObservacion(observacion)
	{
		this._modeloObservacion = observacion;
		if(modo==Modo.ALTA)
		{
			var tipoAuditoriaId = this.tipoAuditoriaId;
			if(tipoAuditoriaId=="AI")	
			{
				$("#notificacionCheck").prop('checked', true);
				$("#reporteCheck").prop('checked', true);
				//$("#valorObservacionInput").val(observacion.valor);
			}
		}
		else
		{
			 $("#hallazgoInput").val(observacion.hallazgo);
		 	$("#recomendacionInput").val(observacion.recomendacion);
			if(observacion.reporte)
				$("#reporteCheck").prop('checked', true);
			else
				$("#reporteCheck").prop('checked', false);
			if(observacion.notificacion)
				$("#notificacionCheck").prop('checked', true);
			else
				$("#notificacionCheck").prop('checked', false);
		}
		
	}
	
	get modeloObservacion()
	{
		 var modelo = 
		 {		
			hallazgo : $("#hallazgoInput").val(),
			recomendacion:  $("#recomendacionInput").val(),
			reporte :  $("#reporteCheck").is(':checked')?1:0,
			notificacion :  $("#notificacionCheck").is(':checked')?1:0,
			valor:  $("#valorObservacionInput").val()
		 };
		var usuario = $( "#responsableSelect option:selected" ).data("data");
		if(usuario!=null)
		{
			modelo.responsableId = usuario.id;
			modelo.responsableNombreCompleto = usuario.nombreCompleto;
			modelo.fotoPerfil = usuario.fotoPerfil;
		}
		else
		{
			modelo.responsableId = null;
			modelo.responsableNombreCompleto = "";
			modelo.fotoPerfil = null;
		}
		 return modelo;
	 }
	
	guardarObservacion()
	{	
		var observacion = this.modeloObservacion;
	
		var seccionActual = this.listaPreguntas.seccionActual;
		if(this.modoObservacion==Modo.ALTA)
		{
			if(seccionActual.observacionesSeccion==null)
				seccionActual.observacionesSeccion =[];
				
			seccionActual.observacionesSeccion.push(observacion);
		}
		else
		{
			this._observacionSeleccionada.hallazgo = observacion.hallazgo;
			this._observacionSeleccionada.recomendacion = observacion.recomendacion;
			this._observacionSeleccionada.responsableId = observacion.responsableId;
			this._observacionSeleccionada.responsableNombreCompleto = observacion.responsableNombreCompleto;
			this._observacionSeleccionada.reporte = observacion.reporte;
			this._observacionSeleccionada.notificacion = observacion.notificacion;
			this._observacionSeleccionada.valor = observacion.valor;
		}
		$("#observacionModal").modal("hide");
		this.observacionesTabla.registros = seccionActual.observacionesSeccion;
		this.inicializarEventosBotonesTablaObservaciones("#" + this.observacionesTabla._id+"Table tbody",this.observacionesTabla.datatable.DataTable());
		this.cambiosObservaciones = true;
		
	}
	
	inicializarEventosBotonesTablaObservaciones(tbody, table, nombresCamposLlave)
	{
		var _this = this;
		$(tbody).on("click", "button.editar", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

			_this._observacionSeleccionada  = table.row( tr ).data();
			if (_this._observacionSeleccionada != undefined)
			{
				var seccionActual = _this.listaPreguntas.seccionActual;
				_this.mostrarObservacion(Modo.CAMBIO, seccionActual, _this._observacionSeleccionada)
			
			}
		});
		
		$(tbody).on("click", "button.eliminar", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

			this._observacionSeleccionada  = table.row( tr ).data();
			var indice = table.row(tr).index();
			if (this._observacionSeleccionada != undefined)
			{
				_this.confirmarEliminarObservacion(this._observacionSeleccionada,tr,indice);
			
			}
		});
		
	}
	
	crearTablaObservaciones()
	{
		this.observacionesTabla = new Tabla("observacionesTabla");
		this.observacionesTabla.alto = 250;
		this.observacionesTabla.buscar = false;
		this.observacionesTabla.paginacion = false;
		this.observacionesTabla.columnas = [
			{longitud:200, 	titulo:"Hallazgo",   alias:"hallazgo", alineacion:"I"},
			{longitud:200, 	titulo:"Recomendación",   alias:"recomendacion", alineacion:"I"},
			{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"D" ,itemRenderer:this.renderLogoResponsable},
			{longitud:200, 	titulo:"Responsable",   alias:"responsableNombreCompleto", alineacion:"I",class: "desc" }, 
			{longitud:50, 	titulo:"Reporte",   alias:"reporte", alineacion:"C", itemRenderer:this.renderReporte},
			{longitud:50, 	titulo:"Notificación",   alias:"notificacion", alineacion:"C", itemRenderer:this.renderNotificacion},
			{longitud:50, 	titulo:"Valor",   alias:"valor", alineacion:"D", itemRenderer:this.renderValor},
		
		]
	
		this.observacionesTabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-1 botones-icon btn btn-sm float-left btn-success active'><span  data-toggle='tooltip' class='fas fa-pencil-alt fa-lg'></span></button>"+
												"<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";


		
		this.observacionesTabla.textoTablaVacia = "No hay observaciones";
		this.observacionesTabla.textoSinRegistros= "No hay observaciones";
		this.observacionesTabla.registros = [];
		
		
		
	}
	
	confirmarEliminarObservacion(observacion, tr, indice)
	{
		var _this = this;
		swal({
	            title: "\u00bfEst\u00E1 seguro de eliminar?",
	            text: "Se eliminar\u00e1 este registro !!",
	            type: "warning",
	            showCancelButton: true,
	            confirmButtonColor: "#DD6B55",
	            confirmButtonText: "Si, eliminar!!",
	            cancelButtonText: "No",
	            closeOnConfirm: false,
	            closeOnCancel: true,
	            showLoaderOnConfirm: true,
	        },
	        function(isConfirm)
	        {
	            if (isConfirm) 
	            {
					_this.listaPreguntas.seccionActual.observacionesSeccion.splice(indice,1);
				
					swal.close();
					tr.fadeOut();
	            	 setTimeout(function()
					{
						
						tr.remove();
						
	            		 //_this.presentador.eliminar();
	 	            }, 1000);
	            }
	        });
	}
	
	renderLogoResponsable(renglon, type, set)
	{    
		var fecha = new Date();
		var contenido = "";
		if(renglon.responsableId!=undefined && renglon.responsableId!="")
		{
		var icono = HANDEL_API+ "/"+renglon.fotoPerfil+"?"+vista.time;
		contenido += "<center><img src='" + icono + "' style='width:30px;height:30px;'></img></center>";
			
		}
	    return contenido;
	}
	
	renderNotificacion(renglon, type, set)
	{    
		var contenido = "";
		if(renglon.notificacion==1)
			contenido += "<center><i class='fa fa-check text-success'></i></center>";
		else
			contenido += "<center><i class='fa fa-times text-danger'></i></center>";
	    return contenido;
	}
	
	renderValor(renglon, type, set)
	{    
		var contenido = "";
		if(renglon.valor!=null)
			contenido += renglon.valor;
	    return contenido;
	}
	
	renderReporte(renglon, type, set)
	{    
		var contenido = "";
		if(renglon.reporte==1)
			contenido += "<center><i class='fa fa-check text-success'></i></center>";
		else
			contenido += "<center><i class='fa fa-times text-danger'></i></center>";
	    return contenido;
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
		/* swal({
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
	        });*/
	        
	        
	        
	 	var _this = this;
	 	$(document).off('click', '.noSalirButton');
	    $(document).on('click', '.noSalirButton', function() {
	        //nothing
	    });
	    $(document).off('click', '.salirButton');
	    $(document).on('click', '.salirButton', function() {
			this.detenerPollingPresencia();
	        window.close();
	        
	        
	    });
	    $(document).off('click', '.notificarButton');
	    $(document).on('click', '.notificarButton', function() {
	        _this.notificarCliente();
	         //window.close();
	    });
	    
	    var html =  "<br>" +
		            '<button type="button" role="button" tabindex="0" class="noSalirButton customSwalBtn" style="background-color:#C1C1C1">No salir</button>' +
		            '<button type="button" role="button" tabindex="0" class="salirButton customSwalBtn" style="background-color:#DD6B55">Salir de auditoría</button>';
		            
		if(this.modeloEdicion.tipoAuditoriaId == TipoAuditoria.SOCIO_COMERCIAL)
		     html+='<button type="button" role="button" tabindex="0" class="notificarButton customSwalBtn" style="background-color:#60a15f">Notificar a cliente y salir de auditoría</button>';
	        
	        
        swal({
	        title: "Auditoría terminada",
	        html: true,
	        text: html ,
	        showCancelButton: false,
	        showConfirmButton: false
	    });
		    
		    
		    
		  
	}
	
	notificarCliente()
	{
		this.presentador.notificarCliente();
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
		return this.listaPreguntas.getValorCampo("empresaId");
		
	}
	
	get tipoAuditoriaId()
	{
		return this.listaPreguntas.getValorCampo("tipoAuditoriaId");
	}
	
	get sedeId()
	{
		return this.listaPreguntas.getValorCampo("sedeId");
	}
	
	get fecha()
	{
		return this.listaPreguntas.getValorCampo("fecha");
	}
	
	get hora()
	{
		return this.listaPreguntas.getValorCampo("hora");
	}
	
	set modeloDatos(modeloDatos)
	{
		this._auditoria = modeloDatos;
		$("#referenciaDiv").fadeIn();
		$("#xrayButton").fadeIn();
		if(modeloDatos!=null)
		{
			this.listaPreguntas.auditoria = modeloDatos;
			//this.listaPreguntas.empresaId = modeloDatos.empresaId;
			this.listaPreguntas.setValorCampo("empresaId",modeloDatos.empresaId);
			this.listaPreguntas.setValorCampo("tipoAuditoriaId",modeloDatos.tipoAuditoriaId);
			//this.listaPreguntas.setValorCampo("sedeId",modeloDatos.sedeId);
			this.listaPreguntas.setValorCampo("fecha",modeloDatos.fecha);
			this.listaPreguntas.setValorCampo("hora",modeloDatos.hora);
			
			var _this = this;
			var componenteSede = this.listaPreguntas.getComponenteCampoId("sedeId");
			componenteSede.empresaId = modeloDatos.empresaId;
			if(componenteSede!=null)
				componenteSede.consultar(this,function(){
						_this.listaPreguntas.setValorCampo("sedeId",modeloDatos.sedeId);
				});	
			//this.listaPreguntas.tipoAuditoriaId = modeloDatos.tipoAuditoriaId;
			/*this.listaPreguntas.sedeId = modeloDatos.sedeId;
			this.listaPreguntas.fecha = modeloDatos.fecha;
			this.listaPreguntas.hora = modeloDatos.hora;*/
			$("#referenciaLabel").html(modeloDatos.referencia);
			this.listaPreguntas.seccionActual = modeloDatos.seccion;
			this.listaPreguntas.seccionActual.observacionesSeccion = modeloDatos.observacionesSeccion;
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
				if(componente.pregunta.tipo!="cat" && componente.pregunta.campoId==null)
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
									notificacion: componente.notificacion,
									tipo: componente.pregunta.tipo
									}; 
					preguntas.push(pregunta);
				}
			}
			
		}
		return preguntas;
	}
	
	
	get preguntasTodas()
	{
		var preguntas = [];
	//	var seccionIndice = $("#secciones").prop("selectedIndex");
		for(var seccionIndice=0;seccionIndice<   this.listaPreguntas.secciones.length;  seccionIndice++)
		{
			var seccion = this.listaPreguntas.secciones[seccionIndice];
			var componentesPreguntas = seccion.componentes;
			for(var i=0;  i  < componentesPreguntas.length;i++ )
			{
				var componente = componentesPreguntas[i];
				if(componente.pregunta.tipo!="cat" && componente.pregunta.campoId==null)
				{
					var pregunta ={ 
									seccionId : seccion.id,
									id: componente.pregunta.id, 
									valor:  componente.valor,
									puntos : componente.pregunta.puntos,
									puntosTotal : componente.pregunta.puntosTotal,
									porcentaje : componente.pregunta.porcentaje,
									respuestas_si: componente.respuestasSi,
									respuestas_no: componente.respuestasNo,
									responsable : componente.responsable,
									reporte: componente.reporte,
									notificacion: componente.notificacion,
									tipo: componente.pregunta.tipo
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
			 //empresaId: this.empresaId,	
			empresaId: this.listaPreguntas.getValorCampo("empresaId"),
			tipoAuditoriaId: this.listaPreguntas.getValorCampo("tipoAuditoriaId"),
			// tipoAuditoriaId: this.tipoAuditoriaId,		
			 seccionId:this.seccionId,	
			 plantillaId:this.plantillaId,	
			 seccion: this.seccionActual,
			 observaciones : this._auditoria.observaciones,
			 buenasPracticas : this._auditoria.buenasPracticas,
			sedeId: this.listaPreguntas.getValorCampo("sedeId"),
			fecha: this.listaPreguntas.getValorCampo("fecha"),
			hora: this.listaPreguntas.getValorCampo("hora"),
		 	// sedeId: this.sedeId,	
	 		//fecha: this.fecha,	
 			//hora: this.hora
			 //preguntas: this.preguntasAuditoria
		 };
		 if(this.modo=="CAMBIO" && this.modeloEdicion!=null)
			 modelo.id = this.modeloEdicion.id;
		
		 if(modelo.observaciones==undefined)
			modelo.observaciones = "";
			
		 if(modelo.buenasPracticas==undefined)
			modelo.buenasPracticas = "";
		
		if(modelo.empresaId==undefined)
			modelo.empresaId = "";	
			
		 if(modelo.sedeId==undefined)
			modelo.sedeId = "";
		
		if(modelo.fecha==undefined)
			modelo.fecha = "";
			
		if(modelo.hora==undefined)
			modelo.hora = "";
		
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
			preguntas : this.preguntasAuditoria,
			observaciones : seccionActual.observacionesSeccion
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
		this.calcularPreguntasSinContestar();
	}
	
	
	
	calcularPreguntasSinContestar()
	{
		var preguntasSinContestar = 0;
		
		var preguntas = this.preguntas;
		for(var i=0; i < preguntas.length; i++)
		{
			var pregunta = preguntas[i];
			if(pregunta.tipo=="sn")
			{
				if(pregunta.valor=="")
				{
					preguntasSinContestar++;	
				}
				else
					contestadas++;
			}
		}
		var indice = $("#secciones").prop('selectedIndex');
		var seccionId = this.listaPreguntas.secciones[indice].id;
		
		var html = "";
		if(preguntasSinContestar>0)
		{
			var textoPreguntas = "";
			if(preguntasSinContestar==1)
				textoPreguntas="pregunta";
			else
				textoPreguntas="preguntas";
		 	html = "<small class='labelAdvertencia'><i class='fas fa-exclamation-triangle'></i> "+preguntasSinContestar+ " "+ textoPreguntas +" sin contestar</small>";
        }
		
		$("#preguntasSinContestarDiv"+seccionId).html(html);
		
		var sn = ArrayUtils.filterWithValues("tipo",["sn"],preguntas);
		var contestadas = 0;
		for(var i=0; i < sn.length; i++)
		{	
			var pregunta = sn[i];
			if(pregunta.valor!="")
				contestadas++;
		}
		var contador = ArrayUtils.searchWithValues("seccionId",[seccionId],this._contadoresPreguntas);
		if(contador!=null)
		{
			contador.contestadas = contestadas;
		//var contadores = [{id:seccionId, total: sn.length, contestadas: contestadas}];
			this.contadoresPreguntas = this._contadoresPreguntas;
		}
		
	}
	
	calcularPuntuacionSeccion(puntuaciones)
	{
		var indice = $("#secciones").prop('selectedIndex');
		var seccionId = this.listaPreguntas.secciones[indice].id;

		var x = 0;
		var y = 0;
		var porcentaje = 0;
		for(var i=0; i < puntuaciones.length; i++)
		{
			var puntuacion = puntuaciones[i];
			x+=puntuacion.x;
			y+=puntuacion.y;
		}
		
		var seccionActual = this.listaPreguntas.seccionActual;
		var totalObservaciones = this.getTotalObservacionesSeccion(seccionActual.observacionesSeccion);
		y+=totalObservaciones;
		
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
		
		var puntuacionTexto ="<span id='preguntasSinContestarDiv"+seccionId+"'></span>" + x + "/" + y + " (" +  textoPorcentaje + "%)";
		
		
		$("#puntuacionPreliminarObservacion").html(puntuacionTexto);
		$("#puntuacionPreliminarObservaciones").html(puntuacionTexto);
		$("#listaPreguntas_labelPuntuacion"  +seccionId).html(puntuacionTexto);
		
		this.listaPreguntas.secciones[indice].puntos = x;
		this.listaPreguntas.secciones[indice].puntosTotal = y;
		this.listaPreguntas.secciones[indice].porcentaje = textoPorcentaje;
		
	}
	
	getTotalObservacionesSeccion(observacionesSeccion)
	{
		var totalObservaciones = 0;
		if(observacionesSeccion)
		{
			for(var oi = 0; oi < observacionesSeccion.length; oi++)
			{
				var obs = observacionesSeccion[oi];
				var v = parseInt(obs.valor);
				if(!isNaN(v) && v > 0)
					totalObservaciones += v;
			}
		}
		return totalObservaciones;
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
				//pregunta.preguntasSinContestar = preguntasSinContestar;
			}
			this.listaPreguntas.setValor(this.seccionId,encabezado.id,puntuacionTexto);
		}
		
		var puntuacion = {x: x, y : y, porcentaje : porcentaje};
		return puntuacion;
	}
	
	set usuariosSeccion(usuarios)
	{
		
		//this.cargarOpciones("#resposableSelect", usuarios,null,"usuarioId");	
		this.cargarOpciones("#responsableSelect", usuarios,Modo.CAMBIO, this._modeloObservacion, "responsableId", "", "nombreCompleto",true)
//		var responsableId =this.listaPreguntas.seccionActual.responsable;
//		if(responsableId!=null)
//			if(responsableId!="")
//				$("#resposableSelect").val(responsableId);
	}
	
	consutarUsuarios(usuarios)
	{
		this._usuarios = usuarios;
	}
	
	iniciarSeguimiento()
	{
		var texto ="Se iniciar\u00e1 el seguimiento de esta auditoría";
		var _this = this;
		swal({
	            title: "\u00bfEst\u00E1 seguro?",
	            text: texto,
	            type: "warning",
	            showCancelButton: true,
	            confirmButtonColor: "#DD6B55",
	            confirmButtonText: "Si, iniciar seguimiento!!",
	            cancelButtonText: "No",
	            closeOnConfirm: false,
	            closeOnCancel: true,
	            showLoaderOnConfirm: true,
	        },
	        function(isConfirm)
	        {
	            if (isConfirm) 
	            {
	            	 setTimeout(function(){
	            		 _this.presentador.iniciarSeguimiento();
	 	            }, 1000);
	            }
	        });
	}
	
	finalizarSeguimiento()
	{
		var texto ="Se finalizar\u00e1 el seguimiento de esta auditoría";
		var _this = this;
		swal({
	            title: "\u00bfEst\u00E1 seguro?",
	            text: texto,
	            type: "warning",
	            showCancelButton: true,
	            confirmButtonColor: "#DD6B55",
	            confirmButtonText: "Si, finalizar seguimiento!!",
	            cancelButtonText: "No",
	            closeOnConfirm: false,
	            closeOnCancel: true,
	            showLoaderOnConfirm: true,
	        },
	        function(isConfirm)
	        {
	            if (isConfirm) 
	            {
	            	 setTimeout(function(){
	            		 _this.presentador.finalizarSeguimiento();
	 	            }, 1000);
	            }
	        });
	}
	
	exportarXRay()
	{
		var submitForm = this.getNewSubmitForm(HANDEL_API+"/php/pdf/xray.php");
		//this.createNewFormElement(submitForm, "auditoriaId",this._auditoria.id);	
		//this.createNewFormElement(submitForm, "plantillaId",this._auditoria.plantillaId);	
		this.createNewFormElement(submitForm, "referencia",this._auditoria.referencia);	 
		this.createNewFormElement(submitForm, "hallazgos", JSON.stringify(this.hallazgosTabla.registros));
		this.createNewFormElement(submitForm, "usuarioXRay",this.usuarioXRay);
		this.createNewFormElement(submitForm, "empresaId", this.empresaId);
		this.createNewFormElement(submitForm, "sedeId", this.sedeId);
		this.createNewFormElement(submitForm, "fecha", this.fecha);
	    submitForm.target= "_blank";
	    submitForm.submit();
	}
	
	
	enviarCorreoXRay()
	{
		this.presentador.enviarCorreoXRay();
	}
	
	set usuariosCorreo(usuariosCorreo)
	{
		var _this = this;
		this._usuariosCorreo = usuariosCorreo;
		$("#usuariosCorreoSelect").empty();
		$("#usuariosCorreoSelect").chosen();
		$.each(this._usuariosCorreo, function(i, p) 
		{
			var fotoPerfil = HANDEL_API+ "/"+p.fotoPerfil+"?"+_this.time;
		    $("#usuariosCorreoSelect").append($('<option data-img-src="'+fotoPerfil+'"></option>').val(p.id).html(p.nombreCompleto));
		});
		
		
		//$('#usuariosCorreoSelect').trigger("chosen:updated");
		$(".chosen-search-input").height(50);
		$(".chosen-search-input").val("");
		
		$("#usuariosCorreoSelect_chosen").css("width","100%");
		
		//$("#usuariosCorreoSelect_chosen").css("width","100%");
		
		var usuarios =[];
		usuarios.push(this._usuarioXRay);
		
		var usuario = ArrayUtils.searchWithValues("id",[this._usuarioXRay], this._usuariosCorreo);
		if(usuario!=null)
		{
			if(usuario.supervisor1Id!=null)
				usuarios.push(usuario.supervisor1Id);
		}
		
		
		$("#usuariosCorreoSelect").val(usuarios);
		
	 	$('#usuariosCorreoSelect').trigger("chosen:updated");
		
		$('#enviarCorreoXRayButton').fadeIn();

	}
	
	get asuntoCorreo()
	{
		return $('#asuntoCorreoXRayInput').val();
	}
	
	get usuariosCorreo()
	{
		var usuariosCorreo = [];
		var usuarios = $('#usuariosCorreoSelect').val();
		for(var i=0; i < usuarios.length; i++)
		{
			var usuarioId = usuarios[i];
			var usuario = ArrayUtils.searchWithValues("id",[usuarioId], this._usuariosCorreo);
			if(usuario!=null)
			{
				usuariosCorreo.push({
					nombreUsuario: usuario.nombreUsuario,
					nombreCompleto: usuario.nombreCompleto	
				});
			}
		}
		return usuariosCorreo;
	}
	
	get hallazgos()
	{
		return this.hallazgosTabla.registros;
	}
	
	get referencia()
	{
		return this._auditoria.referencia;
	}
	
	get usuarioXRay()
	{
		return this._usuarioXRay;
	}
	
	consultarContadoresPreguntasPorSeccion()
	{
		this.presentador.consultarContadoresPreguntasPorSeccion();
	}
	
	set contadoresPreguntas(valor)
	{
		this.crearSelectSecciones(valor);
	}
	
	crearSelectSecciones(contadores)
	{
		this._contadoresPreguntas = contadores;
		$('#secciones').select2({
			dropdownAutoWidth: false, // importante
  			width: 'resolve',
		  	templateResult: this.formatOption,
		 	 templateSelection: this.formatOption,
		  	escapeMarkup: m => m
		});
	}
	
	getContadoresSeccion(id)
	{
		if(this._contadoresPreguntas!=null)
		{
			for(var i = 0; i < this._contadoresPreguntas.length; i++)
			{
				if(id == this._contadoresPreguntas[i].seccionId)
					return this._contadoresPreguntas[i]; 
			}
		}
		return null;
	}	
		
	getColorByValue(value) 
	{
		var contador = vista.getContadoresSeccion(value);
		if(contador!=null)
		{
			if(contador.contestadas == contador.total)
				return "#2ecc71"; // verde
			else if(contador.contestadas > 0 && contador.contestadas < contador.total)
				return "#f1c40f"; // amarillo
			else  if(contador.contestadas == 0) 
				return "#e74c3c"; // rojo
		}
		else
			return "#ccc";
		
	}

	formatOption(option) {
	  if (!option.id) 
	  	return option.text;
	
	  var color = vista.getColorByValue(option.id);
	
	  return $(`
	    <div style="display:flex;align-items:center;gap:8px;">
	      <div style="
	        width:12px;
	        height:12px;
	        border-radius:3px;
	        background:${color};
	      "></div>
	      <span>${option.text}</span>
	    </div>
	  `);
	}
	
	iniciarPollingPresencia()
	{
		var _this = this;
		// Registrar presencia inmediatamente al abrir sección
		this.presentador.registrarPresencia();
		// Consultar quiénes más están presentes
		this.presentador.consultarPresencia();

		// Renovar presencia cada 5s (umbral server = 15s → 3 ventanas de gracia)
		this._registroInterval = setInterval(function()
		{
			_this.presentador.registrarPresencia();
		}, 5000);

		// Actualizar avatares cada 5s (tiempo real más fluido)
		this._presenciaInterval = setInterval(function()
		{
			_this.presentador.consultarPresencia();
		}, 5000);

		// Registrar beforeunload/pagehide una sola vez con sendBeacon.
		// sendBeacon garantiza que el POST llegue aunque la pestaña cierre,
		// a diferencia de $.ajax que es cancelado por el browser.
		if (!this._beforeUnloadBound)
		{
			this._beforeUnloadHandler = function()
			{
				try
				{
					var llaves = {
						auditoriaId: _this.auditoriaId,
						seccionId:   _this.seccionId
					};
					var url = HANDEL_API + "/php/repositorios/Auditorias.php";
					var fd = new FormData();
					fd.append("accion", "desregistrarPresencia");
					fd.append("llaves", JSON.stringify(llaves));
					if (navigator.sendBeacon)
					{
						navigator.sendBeacon(url, fd);
					}
					else
					{
						// Fallback: XHR síncrono (deprecated pero funciona al cerrar)
						var xhr = new XMLHttpRequest();
						xhr.open("POST", url, false);
						xhr.send(fd);
					}
				} catch(e) { }
			};
			window.addEventListener("beforeunload", this._beforeUnloadHandler);
			window.addEventListener("pagehide",     this._beforeUnloadHandler);
			// visibilitychange también — cuando el usuario cambia de pestaña/app
			document.addEventListener("visibilitychange", function() {
				if (document.visibilityState === "hidden") _this._beforeUnloadHandler();
			});
			this._beforeUnloadBound = true;
		}
	}

	detenerPollingPresencia()
	{
		// Desregistrar antes de detener el polling
		try { this.presentador.desregistrarPresencia(); } catch(e) { }

		if(this._presenciaInterval != null)
		{
			clearInterval(this._presenciaInterval);
			this._presenciaInterval = null;
		}
		if(this._registroInterval != null)
		{
			clearInterval(this._registroInterval);
			this._registroInterval = null;
		}
	}
	
	set presencia(usuarios)
	{
		var _this = this;
		var $contenedor = $("#presenciaAvatares");

		if (!usuarios || usuarios.length === 0)
		{
			$contenedor.empty();
			$("#presenciaDiv").hide();
			return;
		}

		// ANTI-PARPADEO: diff inteligente en vez de empty + re-render.
		// 1) Construir map { usuarioId: u } excluyendo al propio usuario
		var nuevosIds = {};
		$.each(usuarios, function(i, u)
		{
			if (_this.usuario && _this.usuario.id == u.usuarioId) return;
			nuevosIds[u.usuarioId] = u;
		});

		// 2) Remover items existentes (avatar + nombre) que ya no están en la nueva lista
		$contenedor.find(".presencia-item").each(function()
		{
			var id = $(this).attr("data-usuario-id");
			if (!nuevosIds[id]) $(this).remove();
		});

		// 3) Agregar items NUEVOS (avatar + nombre) que no estén en el DOM
		$.each(nuevosIds, function(id, u)
		{
			var existente = $contenedor.find(".presencia-item[data-usuario-id=\u0027" + id + "\u0027]");
			if (existente.length > 0) return;

			var nombreCorto = u.nombre ? String(u.nombre).split(" ")[0] : "";

			var $avatar = $("<img>")
				.addClass("presencia-avatar")
				.attr("src", HANDEL_API + "/" + u.fotoPerfil)
				.attr("title", u.nombre);

			var $nombre = $("<span>")
				.addClass("presencia-nombre")
				.text(nombreCorto)
				.attr("title", u.nombre);

			var $item = $("<div>")
				.addClass("presencia-item")
				.attr("data-usuario-id", id)
				.append($avatar)
				.append($nombre);

			$contenedor.append($item);
		});

		if ($contenedor.children().length === 0)
			$("#presenciaDiv").hide();
		else
			$("#presenciaDiv").show();
	}

		_obtenerIniciales(nombre)
	{
		if(!nombre) return '?';
		var partes = nombre.trim().split(' ');
		var ini = partes[0].charAt(0).toUpperCase();
		if(partes.length > 1) ini += partes[partes.length - 1].charAt(0).toUpperCase();
		return ini;
	}
	
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
	            		_this.detenerPollingPresencia();
	 	            }, 1000);
	            }
				
	        });
			
			
	}
	
}
var vista = new AuditoriaVista();
$(document).ready(function() 
{
	vista.inicializar();
});
