class RevisionProcesosVista extends CatalogoVista
{		
	constructor()
	{	
		super();
		this.presentador = new RevisionProcesosPresentador(this);
		var fecha = new Date();
		this._time = fecha.getTime();
	}
	
	
	get time()
	{
		return this._time;
	}

	
	inicializar()
	{
		this.crearColumnasGrid();
		$("body").data("_this",this);
		//this.crearTablas();
		

		var _this = this;
		$("#consultarButton").click(function(){
			_this.consultar();
		});
		
		/*$("#consultarButtonPendiente").click(function(){
			_this.consultar();
		});*/
		
		if($("#enviarMensajeLink").length>0)
			$("#enviarMensajeLink").click(this.enviarMensajeLinkClick)
		
		this.consultoGrid = false;
		this.consultarAnos();
		
		
		//$("#estadoJusSelectCriterio").val(0)
		$("#validarJustificadasButton").click(function(){
			_this.validarJustificadas();
		});
		
	}
	
	crearColumnasGrid()
	{
		this.tabla.columnas = [
			{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"I" },
			{longitud:300, 	titulo:"Nombre",   	alias:"nombre", alineacion:"I" },
			//{longitud:200, 	titulo:"Código",   	alias:"codigo", alineacion:"I" },
			{longitud:200, 	titulo:"Fecha",   	alias:"fecha", alineacion:"I" },
			{longitud:30, 	titulo:"Tipo solicitud",   alias:"estatusRevisionNombre", alineacion:"C", itemRenderer:this.renderEstatusRevision},
			{longitud:30, 	titulo:"Estado de solicitud",   alias:"estatusValidacionNombre", alineacion:"C", itemRenderer:this.renderEstatusValidacion},
			//{longitud:100, 	titulo:"Evidencia",   alias:"nombreArchivo", alineacion:"C", itemRenderer:this.renderArchivo},
			{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"I" ,itemRenderer:this.renderFotoUsuario},
			{longitud:100, 	titulo:"Usuario",   alias:"usuarioNombreCompleto", alineacion:"I"},
			//{longitud:100, 	titulo:"Comentarios",   alias:"comentarios", alineacion:"I", itemRenderer:this.renderComentarios},
			{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"I" ,itemRenderer:this.renderLogoEmpresa},
			{longitud:100, 	titulo:"Empresa",   alias:"empresaNombre", alineacion:"I"},
			//{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"I" ,itemRenderer:this.renderFotoAdministrador},
			//	{longitud:100, 	titulo:"Administrador responsable",   alias:"administradorNombreCompleto", alineacion:"I",itemRenderer:this.renderNombreAdministrador},
			{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"I" ,itemRenderer:this.renderFotoValidador},
			{longitud:100, 	titulo:"Usuario que validó",   alias:"validadorNombreCompleto", alineacion:"I",itemRenderer:this.renderNombreValidador},
			{longitud:200, 	titulo:"Fecha validación",   	alias:"fechaValidacion", alineacion:"I", itemRenderer: this.renderFechaValidacion },
			//{longitud:100, 	titulo:"Comentarios",   alias:"comentarios", alineacion:"I", itemRenderer:this.renderComentarios},
			//{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"I" },
			//{longitud:50, 	titulo:"Código",   	alias:"codigo", alineacion:"I" }
			
		];
		
		this.tabla.columnas.push({longitud:30, 	titulo:"",  alias:"", alineacion:"I" ,itemRenderer:this.renderRevisar});
	
		
//		this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-edit fa-lg'></span></button>"+
//									"<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";

		this.tabla.registros = [];
	}
	
	renderRevisar(renglon, type, set)
	{    
		var contenido = "";
		//if(renglon.usuarioId == vista.usuario.id)
		//{
			var fecha = new Date();
			if($("#anoSelectCriterio").val() ==fecha.getFullYear() )
				contenido += "<button data-toggle='tooltip' data-placemen='bottom' title='Revisar'  type='button' class='revisar btn-circle mr-0 botones-icon btn btn-sm float-right btn-success'><span  data-toggle='tooltip' class='fa fa-check fa-lg'></span></button>";
		//<}
	    return contenido;
	}
	
	
	renderFotoAdministrador(renglon, type, set)
	{    
		var fecha = new Date();
		var contenido = "";
		if(renglon.administradorId!=null)
		{
			var icono = HANDEL_API+ "/"+renglon.administradorFotoPerfil+"?"+vista.time;
			contenido += "<center><img src='" + icono + "' style='width:30px;height:30px;border-radius: 50%'></img></center>";
		}
	    return contenido;
	}
	
	renderNombreValidador(renglon, type, set)
	{    
		return "<div id='nombreValidadorTabla"+renglon.id+"'>" + vista.getNombreValidador(renglon) + "</div>";
	
		
	}
	
	getNombreValidador(renglon)
	{
		var fecha = new Date();
		var contenido = "";
		if(renglon.validadorId!=null)
			contenido = renglon.validadorNombreCompleto;
		else
			contenido ="";
	    return contenido;
	}
	
	renderFechaValidacion(renglon, type, set)
	{    
		return "<div id='fechaValidacionTabla"+renglon.id+"'>" + vista.getFechaValidacion(renglon) + "</div>";
	
		
	}
	
	getFechaValidacion(renglon)
	{
		return renglon.fechaValidacion;
	}
	
	
	renderNombreAdministrador(renglon, type, set)
	{    
		return "<div id='nombreAdministradorTabla"+renglon.id+"'>" + vista.getNombreAdministrador(renglon) + "</div>";
		
	}
	
	
	
	getNombreAdministrador(renglon)
	{
		var fecha = new Date();
		var contenido = "";
		if(renglon.administradorId!=null)
			contenido = renglon.administradorNombreCompleto;
		
	    return contenido;
	}
	
	renderFotoValidador(renglon, type, set)
	{    
		return "<div id='fotoValidadorTabla"+renglon.id+"'>" + vista.getFotoValidador(renglon) + "</div>";
	}
	
	getFotoValidador(renglon)
	{
		var fecha = new Date();
		var contenido = "";
		if(renglon.validadorId!=null)
		{
			var icono = HANDEL_API+ "/"+renglon.validadorFotoPerfil+"?"+vista.time;
			contenido += "<center><img src='" + icono + "' style='width:30px;height:30px;border-radius: 50%'></img></center>";
		}
	    return contenido;
	}
	
	
	
	
	renderLogoEmpresa(renglon, type, set)
	{    
		var fecha = new Date();
		var contenido = "";
		var icono = HANDEL_API+ "/"+renglon.empresaLogo+"?"+fecha.getTime();
		contenido += "<center><img src='" + icono + "' style='width:30px;height:30px;border-radius: 50%'></img></center>";
	    return contenido;
	}
	
	renderEstatusValidacion(renglon, type, set)
	{    
		return "<div id='estatusValidacionTabla"+renglon.id+"'>" + vista.getEstatusValidacion(renglon) + "</div>";
	}
	
	
	renderEstatusRevision(renglon, type, set)
	{    
		return "<div id='estatusRevisionTabla"+renglon.id+"'>" + vista.getEstatusRevision(renglon) + "</div>";
	}
	
	
	/*renderComentarios(renglon, type, set)
	{    
		var contenido = "";
		var comentarios ="";
		if(renglon.numeroComentarios>0)
			comentarios = "<span class='label-warning notificacion'>"+renglon.numeroComentarios+"</span>";
		contenido = "<center><span style='cursor:pointer;margin-left:15px;width:50px;height:30px' data-toggle='tooltip' data-placemen='bottom' title='Comentarios'  type='button' class='comentarios text-aqua'><span  data-toggle='tooltip' class='fas fa-comments fa-lg'>"+comentarios+"</span></center>";;
	    return contenido;
	}*/
	
	renderComentarios(renglon, type, set)
	{  
		return "<div id='comentariosEvidenciaTabla"+renglon.id+"'>" + vista.getComentariosEvidencia(renglon) + "</div>";
	}
	
	
	renderArchivo(renglon, type, set)
	{   
		return "<div id='archivoEvidenciaTabla"+renglon.id+"'>" + vista.getArchivoEvidencia(renglon) + "</div>";
		/*var contenido = "";
		var comentarios ="";
		if(renglon.justificacionId==null)
		{
			var iconoColor = vista.getIconoArchivo(renglon.nombreArchivo);
			if(renglon.validada==1)
				comentarios = "<span class='label-success' style='position: relative;top: 6px;right: 4px;font-size: 10px;padding: 2px 3px;line-height: .9;'><i class='fas fa-check-double'></i></span>";
			contenido = "<center><span style='cursor:pointer;margin-left:15px;width:50px;height:30px' data-toggle='tooltip' data-placemen='bottom' title='Evidencia'  type='button' class='archivo'><span  data-toggle='tooltip' class='"+iconoColor.icono+" fa-lg "+iconoColor.color+"'>"+comentarios+"</span></center>";
		}
		else
		{
			if(renglon.validada==1)
				comentarios = "<span class='label-success' style='position: relative;top: 6px;right: 4px;font-size: 10px;padding: 2px 3px;line-height: .9;'><i class='fas fa-check-double'></i></span>";
			contenido = "<center>"+comentarios+"</center>";
	
		}
	    return contenido;*/
	}
	
	getIconoArchivo(nombre)
	{
		var iconoColor = new Object();
		if(nombre=="")
		{
			iconoColor.icono = "fa fa-file";
			iconoColor.color ="text-black";
		}
		else
		{
			try
			{
				var elementos = nombre.split(".");
				if(elementos.length>1)
				{
					var tipo= elementos[elementos.length-1];
					switch(tipo)
					{
						case "doc":
						case "docx":
							iconoColor.icono = "fa fa-file-word";
							iconoColor.color ="text-blue";
						break;
						case "xls":
						case "xlsx":
							iconoColor.icono = "fa fa-file-excel";
							iconoColor.color ="text-green";
						break;
						case "ppt":
						case "pptx":
						case "ppsx":
							iconoColor.icono = "fa fa-file-powerpoint";
							iconoColor.color ="text-red";
						break;
						case "pdf":
							iconoColor.icono = "fa fa-file-pdf";
							iconoColor.color ="text-red";
						break;
	//					case "txt":
	//						 $('#evidenciaImage').attr('src',HANDEL_API + "/images/tipos_archivo/txt.png");
	//					break;
						default:
							iconoColor.icono = "fa fa-file";
							iconoColor.color ="text-black";
						break;
						case "jpg":
						case "png":
						case "bmp":
							iconoColor.icono = "fa fa-image";
							iconoColor.color ="text-orange";
						break;
						
						
					}
				}
				else
				{
					iconoColor.icono = "fa fa-file";
					iconoColor.color ="text-black";
				}
			}
			catch(e)
			{
				iconoColor.icono = "fa fa-file";
				iconoColor.color ="text-black";
			}
			
		}
		return iconoColor;
	}
	
	renderFotoUsuario(renglon, type, set)
	{    
		var fecha = new Date();
		var contenido = "";
		var icono = HANDEL_API+ "/"+renglon.fotoPerfil+"?"+vista.time;
		contenido += "<center><img src='" + icono + "' style='width:30px;height:30px;border-radius: 50%'></img></center>";
	    return contenido;
	}
	

	renderLogo(renglon, type, set)
	{    
		var fecha = new Date();
		var contenido = "";
		var icono = HANDEL_API+ "/"+renglon.fotoPerfil+"?"+vista.time;
		contenido += "<center><img src='" + icono + "' style='width:30px;height:30px;'></img></center>";
	    return contenido;
	}
	
	consultarAnos()
	{
		this.presentador.consultarAnos();
	}
	
	set anos(anos)
	{
		var fecha = new Date();
		
		if(anos.length==0)
		{
			anos.push({id:fecha.getFullYear(), nombre:fecha.getFullYear()})
			
		}
		this.cargarOpciones('#anoSelectCriterio', anos);
		
		$("#mesSelectCriterio").val(fecha.getMonth()+1);
		$("#anoSelectCriterio").val(fecha.getFullYear());
		
		
		this.consultarEmpresasCriterio();
		
		
		//this.consultar();
		
	}

	cambiarEmpresaCriterio()
	{
		this.consultarSedesCriterio();
	}

	cambiarSedeCriterio()
	{
		
	}

	
	consultarEmpresasCriterio()
	{
		this.cargandoOpciones("#empresaSelectCriterio");
		this.presentador.consultarEmpresasCriterio();
	}
	
	consultarSedesCriterio()
	{
		this.cargandoOpciones("#sedeSelectCriterio");
		this.presentador.consultarSedesCriterio();
	}
	
	consultarDepartamentosCriterio()
	{
		this.cargandoOpciones("#departamentoSelectCriterio");
		this.presentador.consultarDepartamentosCriterio();
	}
	
	set sedesCriterio(registros)
	{		
		this.cargarOpciones('#sedeSelectCriterio', registros);
	}
	
	set departamentosCriterio(registros)
	{		
		this.cargarOpciones('#departamentoSelectCriterio', registros);
		this.presentador.consultarAdministradoresCriterio();
	}
	
	set administradoresCriterio(registros)
	{
		this.cargarOpciones('#administradorSelectCriterio', registros, null, null, null, null,  "nombreCompleto");
		//TODO: descomentar
		//$("#administradorSelectCriterio").val(this.usuario.id);
		this.consultarEstatusValidacionProcesos();
		
	}
	
	consultarEstatusValidacionProcesos()
	{
		this.presentador.consultarEstatusValidacionProcesos();
	}	
	
	set estatusValidacionProcesos(registros)
	{
		this.cargarOpciones('#estadoValidacionSelectCriterio', registros);
		//TODO: descomentar
		//$("#estadoValidacionSelectCriterio").val(EstatusValidacionProceso.EN_PROCESO_DE_ANALISIS);
		if(this.consultoGrid==false)
		{
			this.consultar();
			this.consultoGrid=true;
		}
	}

	
	
	set empresasCriterio(registros)
	{		
		this.cargarOpciones('#empresaSelectCriterio', registros);
		this.consultarDepartamentosCriterio();
	}
	
	get criteriosSeleccion()
	{
		
		
		var criteriosSeleccion = 
		{
			empresaId:  $('#empresaSelectCriterio').val(),
			sedeId:  $('#sedeSelectCriterio').val(),
			departamentoId:  $('#departamentoSelectCriterio').val(),
			mes:  $('#mesSelectCriterio').val(),
			ano: $('#anoSelectCriterio').val(),
			administradorId : $('#administradorSelectCriterio').val(),
			estatusValidacionId : $('#estadoValidacionSelectCriterio').val()
			
		};
		
		
		
		return criteriosSeleccion;
	}

	set porcentajesUsuarios(porcentajesAreas)
	{
		am4core.ready(function() {

			// Themes begin
			//am4core.useTheme(am4themes_kelly);
			am4core.useTheme(am4themes_animated);
			// Themes end

			// Create chart instance
			var chart = am4core.create("empresasChart", am4charts.XYChart);
			chart.scrollbarX = new am4core.Scrollbar();
			chart.data = porcentajesAreas;

			// Create axes
			var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
			categoryAxis.dataFields.category = "nombreId";
			categoryAxis.renderer.grid.template.location = 0;
			categoryAxis.renderer.minGridDistance = 30;
			categoryAxis.renderer.labels.template.horizontalCenter = "middle";
			categoryAxis.renderer.labels.template.verticalCenter = "middle";
			categoryAxis.renderer.labels.template.rotation = 315;
			categoryAxis.tooltip.disabled = true;
			categoryAxis.renderer.minHeight = 110;
			categoryAxis.renderer.labels.template.adapter.add("textOutput", function(text) {
				  return text.replace(/ \(.*/, "");
				});
			
			let label = categoryAxis.renderer.labels.template;
			label.wrap = true;

		
			

			var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
			valueAxis.renderer.minWidth = 50;
			valueAxis.min = 0;
			valueAxis.max = 100;

			// Create series
			var series = chart.series.push(new am4charts.ColumnSeries());
			series.sequencedInterpolation = true;
			series.dataFields.valueY = "porcentajeCumplimiento";
			series.dataFields.categoryX = "nombreId";
			series.tooltipText = "{nombreCompleto} ({nombreUsuario}) : {valueY}% ({cumplidas}/{total})";
			series.columns.template.strokeWidth = 0;

			series.tooltip.pointerOrientation = "vertical";

			series.columns.template.column.cornerRadiusTopLeft = 10;
			series.columns.template.column.cornerRadiusTopRight = 10;
			series.columns.template.column.fillOpacity = 0.8;
			
			
			

			// on hover, make corner radiuses bigger
			var hoverState = series.columns.template.column.states.create("hover");
			hoverState.properties.cornerRadiusTopLeft = 0;
			hoverState.properties.cornerRadiusTopRight = 0;
			hoverState.properties.fillOpacity = 1;

			series.columns.template.adapter.add("fill", function(fill, target) 
			{
				if (target.dataItem.valueY >= 0 && target.dataItem.valueY < 51) 
				    return am4core.color("#dd4b39");
				else if (target.dataItem.valueY >= 51 && target.dataItem.valueY < 100)
					 return am4core.color("#f39c12");
				else if (target.dataItem.valueY >= 100)
					return am4core.color("#00a65a");
				else
					return fill;
			});
			
			

			// Cursor
			chart.cursor = new am4charts.XYCursor();
			
			chart.scrollbarX = new am4core.Scrollbar();
			chart.events.on("ready", function (e) 
				{
					try
					{
						var zoom = 6;
						//if(_this.porcentajesAreas.lengh>=zoom)
						//categoryAxis.zoomToIndexes(0, zoom);
					}
					catch(e)
					{
						
					}
					
				});

			}); // end am4core.ready()


		

	}
	
	cambiarEstado()
	{
		
		
	}
	
	set datos(datos)
	{
		super.datos = datos;
		var justificadasSinValidar = ArrayUtils.filterWithValues("validada,justificada",[0,1],datos);
		if(justificadasSinValidar.length>0)
		{
			$("#justificadasSpan").html("("+justificadasSinValidar.length+")");
			$("#validarJustificadasButton").fadeIn();
		}
		else
			$("#validarJustificadasButton").fadeOut();
		this._criteriosSeleccionValidada = this.criteriosSeleccion.validada;
	}
	
	validarJustificadas()
	{
		var justificadasSinValidar = ArrayUtils.filterWithValues("validada,justificada",[0,1],this.tabla.registros);
		var ids = ArrayUtils.join(justificadasSinValidar,"id");
		this.presentador.validarJustificadas(ids);
	}
	
	eliminarValidadas(ids)
	{
		var registros = ids.split(",");
		for(var i=0; i < registros.length; i++)
		{
			var id = registros[i];
			var row = $("#tabla").find("tr[data-id="+id+"]");
			if(row!=null)
				row.remove();
		}
		$("#validarJustificadasButton").fadeOut();
		var texto = "";
		if(registros.length==1)
			texto = "validó 1 evidencia";
		else
			texto = "validaron "+registros.length+" evidencias";
		this.mostrarMensaje("","Se "+ texto +" justificadas correctamente")
	}
	
	set cargando(cargando)
	{
		super.cargando = cargando;
		if(cargando)
			$("#validarJustificadasButton").attr("disabled",true);
		else
			$("#validarJustificadasButton").attr("disabled",false);
	}
	
	inicializarEventosBotonesTabla(tbody, table, nombresCamposLlave)
	{
		var _this = this;
		super.inicializarEventosBotonesTabla(tbody, table, nombresCamposLlave);
		$(tbody).on("click", "button.revisar", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

			_this._procesoSeleccionado  = table.row( tr ).data();
			
			
			if (_this._procesoSeleccionado != undefined)
			{
				//_this._llaves = _this.copiarPropiedadesObjeto(_this._procesoSeleccionado, ["id"]);
				if(_this.usuario.tipoUsuarioId==TipoUsuario.ADMINISTRADOR)
				{
					_this._llavesProceso = _this.copiarPropiedadesObjeto(_this._procesoSeleccionado, ["id"]);
					_this.mostrarFormularioRevision();
				}
				/*else if(_this.usuario.tipoUsuarioId==TipoUsuario.COORDINADOR || this.usuario.tipoUsuarioId==TipoUsuario.SUPERVISOR)
				{
					_this.modo = Modo.CONSULTA
					_this.mostrarFormularioEvidencia(_this._evidenciaSeleccionada);
				}*/
			
			}
		});
		
		$(tbody).on("click", "span.comentarios", function()
			{			
				 var tr = $(this).closest('tr');
				    
			    if ( $(tr).hasClass('child') ) {
			      tr = $(tr).prev();  
			    }
				
				_this._evidenciaSeleccionada  = table.row( tr ).data();
				if (_this._evidenciaSeleccionada != undefined)
				{
					_this._llaves = _this.copiarPropiedadesObjeto(_this._evidenciaSeleccionada, ["id"]);
					_this.mostrarComentariosEvidencia();

				}
			});
	}
	
	get llavesProceso()
	{
		return this._llavesProceso;	
	}
	
	mostrarFormularioRevision()
	{
		var _this = this;
		this.mostrarFormularioHTML(HANDEL_API+"/html/modales/proceso_revisado_validacion.php",this, null, function()
		{
			//this.inicializarValidacionesFormularioValidacion();
			$("#procesoLabel").html(_this._procesoSeleccionado.nombre);
			$("#estatusRevisionIcono").addClass(_this._procesoSeleccionado.estatusRevisionIcono);
			$("#estatusRevisionIcono").addClass(_this._procesoSeleccionado.estatusRevisionColor);
			$("#estatusRevisionLabel").html(_this._procesoSeleccionado.estatusRevisionDescripcion);
			$("#estatusValidacionIcono").addClass(_this._procesoSeleccionado.estatusValidacionIcono);
			$("#estatusValidacionIcono").addClass(_this._procesoSeleccionado.estatusValidacionColor);
			$("#estatusValidacionLabel").html(_this._procesoSeleccionado.estatusValidacionDescripcion);
			if(_this._procesoSeleccionado.estatusRevisionId == EstatusRevision.OBSERVACIONES)
			{
				$("#observacionesLabel").fadeIn();				
				this.crearTablaObservaciones();
			}
			this.crearEventosBotonesValidacion();
			this.consultarProcesoRevisadoPorLlaves();
			
		},null,"procesoModal","","guardarValidacionButton", function()
		{
			$("#formularioValidacion").submit();
		});
	}
	
	crearEventosBotonesValidacion()
	{
		var _this = this;
		$("#verificacionButton").click(function () 
		{
			_this.actualizarEstatusValidacionProceso(EstatusValidacionProceso.EN_VERIFICACION);
		});
		$("#autorizadoButton").click(function () 
		{
			_this.actualizarEstatusValidacionProceso(EstatusValidacionProceso.AUTORIZADO);
		});
		$("#rechazadoButton").click(function () 
		{
			_this.actualizarEstatusValidacionProceso(EstatusValidacionProceso.RECHAZADO);
		});
		$("#respondioButton").click(function () 
		{
			_this.actualizarEstatusValidacionProceso(EstatusValidacionProceso.RESPONDIO);
		});
	}
	
	actualizarEstatusValidacionProceso(estatusValidacionId)
	{
		this.presentador.actualizarEstatusValidacionProceso(estatusValidacionId);
	}
	
	
	
	validarObservaciones(observaciones, estatusValidacionId)
	{
		this.presentador.validarObservaciones(observaciones, estatusValidacionId);
	}
	
	
	mostrarComentariosEvidencia()
	{
		if($("#modalAlta").length ==0)
		{
			var url = HANDEL_API + "/html/modales/comentarios.php";
			this.mostrarIndicador();
			var _this = this;
			$.post(url,{}, function(html) 
			{
				_this.ocultarIndicador();
				$("body").append(html);
				$("#modalAlta").on("hidden.bs.modal", function () 
				{
					clearInterval(_this.cometariosEvidenciaIntervalId);
					_this.consultarEvidenciaPorLlaves(false);
					$("#modalAlta").remove();
				});
				
				$("#modalAlta").on("show.bs.modal", function () 
				{
					//_this.inicializarValidacionesComentarioEvidencia();
					$("#enviarComentarioButton").click(function () 
					{
						var comentario = $("#comentarioEvidenciaInput").val().trim();
						if(comentario!="" && comentario!=undefined)
							_this.enviarComentarioEvidencia();
					});
					$("#comentarioEvidenciaInput").keypress(function(event){
					    var keycode = (event.keyCode ? event.keyCode : event.which);
					    if(keycode == '13')
					    {
					    	var comentario = $("#comentarioEvidenciaInput").val().trim();
							if(comentario!="" && comentario!=undefined)
								_this.enviarComentarioEvidencia();
					    }
					});
					_this._comentariosEvidencia = [];
					_this.consultarComentariosEvidencia();
					_this.cometariosEvidenciaIntervalId = setInterval(_this.consultarComentariosAutomaticamente, 60000);
						
				});
			
				$("#modalAlta").modal({backdrop: 'static', keyboard: false});
			});
		}
		else
		{
			$("#modalAlta").modal({backdrop: 'static', keyboard: false});
		}
	}
	
	consultarProcesoRevisadoPorLlaves()
	{
		this.presentador.consultarProcesoRevisadoPorLlaves();
	}
	 
	
	
	enviarComentarioEvidencia()
	{
		this.presentador.enviarComentarioEvidencia();
		$("#comentarioEvidenciaInput").val("");
	}
	

	
	get modeloCometarioEvidencia()
	{
		var modelo =
		{
			evidenciaId: this._evidenciaSeleccionada.id,
			usuarioId: this.usuario.id,
			comentario: $("#comentarioEvidenciaInput").val()
		};
		return modelo;
	}
	
	consultarComentariosEvidencia()
	{
		this.presentador.consultarComentariosEvidencia();
	}
	
	consultarComentariosAutomaticamente()
	{
		var _this  = $("body").data("_this");
		_this.consultarComentariosEvidencia();
	}
	
	get evidenciaSeleccionada()
	{
		return this._evidenciaSeleccionada;
	}
	
	set comentariosEvidencia(comentariosEvidencia)
	{
		if(comentariosEvidencia.length> this._comentariosEvidencia.length)
		{
			this._comentariosEvidencia = comentariosEvidencia;
			var fecha = new Date();
			var html="";
			for(var i=0; i< comentariosEvidencia.length; i++)
			{
				var comentario = comentariosEvidencia[i];
				
				var foto ="";
				if(comentario.fotoPerfil.includes("default.jpg"))
					foto = comentario.fotoPerfil;
				else
					foto = comentario.fotoPerfil+"?"+vista.time;
				
				
				//var foto = HANDEL_API + "/" + comentario.fotoPerfil+"?"+fecha.getTime();
				var url = HANDEL_API + "/" + foto;
				html+="<div class='item'>" +
						"<img src='"+url+"' alt='user image' class='offline'> " +
						"<p class='message'>" +
						"  <a href='#' class='name'>" +
						"	<small class='text-muted pull-right'><i class='fa fa-clock-o'></i> "+comentario.fecha +"</small>" + comentario.usuarioNombreCompleto +
						"  </a>" + comentario.comentario + 
						"</p>" +
					  "</div>";
			}
			$("#chatbox").html(html);
		}	
	}
	
	consultarEvidenciaPorLlaves(formulario)
	{
		this._formularioEvidencias = formulario;
		this.presentador.consultarEvidenciaPorLlaves();
	}
	
	
	set modeloEvidencia(modeloEvidencia)
	{
		this._modeloEvidencia = modeloEvidencia;
		//$('#cumplimientoObservacionTabla'+this._modeloEvidencia.id).html(this.getCumplimiento(this._modeloEvidencia));
		//$('#fotoUsuarioObservacionTabla'+this._modeloEvidencia.id).html(this.getFotoUsuario(this._modeloEvidencia));
		//$('#nombreUsuarioObservacionTabla'+this._modeloEvidencia.id).html(this.getTexto(this._modeloEvidencia.usuarioNombreCompleto));
		//$('#estatusValidacionObservacionTabla'+this._modeloEvidencia.id).html(this.getEstatusValidacionObservacion(this._modeloEvidencia));
		$('#comentariosEvidenciaTabla'+this._modeloEvidencia.id).html(this.getComentariosEvidencia(this._modeloEvidencia));
		//$("#estatusValidacionIcono").attr("class","");
		//$("#estatusValidacionIcono").addClass(this._modeloEvidencia.estatusValidacionIcono);
		//$("#estatusValidacionIcono").addClass(this._modeloEvidencia.estatusValidacionColor);
		//$("#estatusValidacionLabel").html(this._modeloEvidencia.estatusValidacionDescripcion);
		
		/*this._evidenciaSeleccionada.cumplimiento = modeloEvidencia.cumplimiento;
		this._evidenciaSeleccionada.estatusValidacionIcono = modeloEvidencia.estatusValidacionIcono;
		this._evidenciaSeleccionada.estatusValidacionColor = modeloEvidencia.estatusValidacionColor;
		this._evidenciaSeleccionada.estatusValidacionDescripcion = modeloEvidencia.estatusValidacionDescripcion;
		this._evidenciaSeleccionada.usuarioId = modeloEvidencia.usuarioId;
		this._evidenciaSeleccionada.usuarioNombre = modeloEvidencia.usuarioNombre;
		this._evidenciaSeleccionada.usuarioApellido = modeloEvidencia.usuarioApellido;
		this._evidenciaSeleccionada.usuarioNombreCompleto = modeloEvidencia.usuarioNombreCompleto;
		
		if(this.usuario.tipoUsuarioId==TipoUsuario.ADMINISTRADOR && this._evidenciaSeleccionada.cumplimiento==100)
			$("#validarObservacionButton").show();
		else
			$("#validarObservacionButton").hide();
		if(this._formularioObservacion)	
			this.consultarResponsablesObservacion();*/
	}
	
	getComentariosEvidencia(renglon)
	{    
		var contenido = "";
		var comentarios ="";
		if(renglon.numeroComentarios>0)
			comentarios = "<span class='label-warning notificacion'>"+renglon.numeroComentarios+"</span>";
		contenido = "<center><span style='cursor:pointer;margin-left:15px;width:50px;height:30px;color:gray;' data-toggle='tooltip' data-placemen='bottom' title='Comentarios' type='button' class='comentarios text-blue'><span  data-toggle='tooltip' class='fas fa-comments fa-lg'>"+comentarios+"</span></center>";
	    return contenido;
	}
	
	
	mostrarFormularioEvidencia(procedimiento)
	{
		if($("#modalAlta").length ==0)
		{
			var url = HANDEL_API + "/html/formularios/validacion_evidencia_saha.php";
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
					
					_this.__PAGE_RENDERING_IN_PROGRESS = 0;
					_this.__CANVAS = $('#pdf-canvas').get(0);
					_this.__CANVAS_CTX = _this.__CANVAS.getContext('2d');
					
					// Previous page of the PDF
					$("#pdf-prev").on('click', function() {
						if(_this.__CURRENT_PAGE != 1)
							_this.showPage(--_this.__CURRENT_PAGE);
					});

					// Next page of the PDF
					$("#pdf-next").on('click', function() {
						if(_this.__CURRENT_PAGE != _this.__TOTAL_PAGES)
							_this.showPage(++_this.__CURRENT_PAGE);
					});
					
					if(_this.modo == Modo.CAMBIO ||_this.modo == Modo.CONSULTA)
					{	
						if(_this.presentador!=null)
							_this.presentador.consultarPorLlaves();
					}
					else
						_this.consultarCamposEvidencia(procedimiento);
					
						
				});
				
				if(_this.modo == Modo.CONSULTA)
				{
					$("#tituloModalAlta").html("Evidencia");
					$("#realizoActividadCheck").attr("disabled",true);
					$("#botonesDiv").hide();
					$("#justificacionSelect").attr("disabled",true);
					$("#comentariosInput").attr("disabled",true);
					$("#guardarButton").hide();
					$("#guardarButton").hide();
				}
			
				
				_this.inicializarValidacionesEvidencia();
				
				
				$("#guardarButton").click(function () 
				{
					
					 $("#formulario").submit();
				});
				
				//$("#borrarArchivoEvidenciaButton").click(function(){
				//	_this.borrarArchivoEvidencia();
				//});
				
				
				$("#modalAlta").modal({backdrop: 'static', keyboard: false});
			});
		}
		else
		{
			$("#modalAlta").modal({backdrop: 'static', keyboard: false});
		}
	}
	
	showPage(page_no) {
		this.__PAGE_RENDERING_IN_PROGRESS = 1;
		this.__CURRENT_PAGE = page_no;
		
		var _this = this;

		// Disable Prev & Next buttons while page is being loaded
		$("#pdf-next, #pdf-prev").attr('disabled', 'disabled');

		// While page is being rendered hide the canvas and show a loading message
		$("#pdf-canvas").hide();
		$("#page-loader").show();

		// Update current page in HTML
		$("#pdf-current-page").text(page_no);
		
		// Fetch the page
		this.__PDF_DOC.getPage(page_no).then(function(page) {
			// As the canvas is of a fixed width we need to set the scale of the viewport accordingly
			var scale_required = _this.__CANVAS.width / page.getViewport(1).width;

			// Get viewport of the page at required scale
			var viewport = page.getViewport(scale_required);

			// Set canvas height
			_this.__CANVAS.height = viewport.height;

			var renderContext = {
				canvasContext: _this.__CANVAS_CTX,
				viewport: viewport
			};
			
			// Render the page contents in the canvas
			page.render(renderContext).then(function() {
				_this.__PAGE_RENDERING_IN_PROGRESS = 0;

				// Re-enable Prev & Next buttons
				$("#pdf-next, #pdf-prev").removeAttr('disabled');

				// Show the canvas and hide the page loader
				$("#pdf-canvas").show();
				$("#page-loader").hide();
			});
		});
	}
	
	inicializarValidacionesEvidencia()
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
               
               
            },
            messages: {
               
                	
                
            },
            submitHandler:function (form) {
            	 _this.guardar();
            }
        });
	}
	
	mostrarFormularioValidacionEvidencia()
	{
		this._evidencias = ArrayUtils.filterWithValues("validada",[0],this.tabla.registros);
		this._evidenciaSeleccionadaIndice  = ArrayUtils.getIndexWithValues("id",[this._evidenciaSeleccionada.id],this._evidencias);
		
		if($("#modalAlta").length ==0)
		{
			
			var url = HANDEL_API + "/html/formularios/validacion_evidencias.php";
			this.mostrarIndicador();
			var _this = this;
			$.post(url,{}, function(html) 
			{
//				 $('#evidenciaImage')
//				    .wrap('<span style="display:inline-block"></span>')
//				    .css('display', 'block')
//				    .parent()
//				    .zoom();
				 
				_this.ocultarIndicador();
				$("body").append(html);
				$("#modalAlta").on("hidden.bs.modal", function () 
				{
				
					$("#modalAlta").remove();
				});
				
				$("#modalAlta").on("show.bs.modal", function () 
				{
					_this.__PAGE_RENDERING_IN_PROGRESS = 0;
					_this.__CANVAS = $('#pdf-canvas').get(0);
					_this.__CANVAS_CTX = _this.__CANVAS.getContext('2d');
					
					// Previous page of the PDF
					$("#pdf-prev").on('click', function() {
						if(_this.__CURRENT_PAGE != 1)
							_this.showPage(--_this.__CURRENT_PAGE);
					});

					// Next page of the PDF
					$("#pdf-next").on('click', function() {
						if(_this.__CURRENT_PAGE != _this.__TOTAL_PAGES)
							_this.showPage(++_this.__CURRENT_PAGE);
					});
					
					$("#guardarButton").unbind();
					$("#guardarButton").click(function () 
					{
						_this.validarEvidencia(true);
					});
					
					$("#guardarSiguienteButton").click(function () 
					{
						_this.validarEvidencia(false);
					
						
						
					});
					
					$("#descargarEvidenciaButton").click(function () 
					{
						var archivo = "evidencia"+_this.modeloEdicion.id+"_" +encodeURIComponent(_this.modeloEdicion.nombreArchivo);
						var url = HANDEL_API + "/php/archivos_evidencias/" + archivo;
						var submitForm = _this.getNewSubmitForm(url);
					    submitForm.target= "_blank";
					    submitForm.submit();
					});
							
					if(_this._evidenciaSeleccionadaIndice!=-1)
					{
						var evidencia = _this._evidencias[_this._evidenciaSeleccionadaIndice];
						_this.mostrarEvidenciaValidacion(evidencia);
						
					}
					else
						_this.mostrarEvidenciaValidacion(_this._evidenciaSeleccionada);
					
				});
			
				$("#modalAlta").modal({backdrop: 'static', keyboard: false});
			});
		}
		else
		{
			$("#modalAlta").modal({backdrop: 'static', keyboard: false});
		}
	}
	
	mostrarSiguienteEvidenciaValidacion()
	{
		this._evidenciaSeleccionadaIndice++;
		if(this._evidenciaSeleccionadaIndice < this._evidencias.length)
		{
			var evidencia = this._evidencias[this._evidenciaSeleccionadaIndice];
			this.modeloValidacion = evidencia;
			$("#modalAlta").animate({ scrollTop: 0 }, "slow");
			return true;
		}
		return false;
	}
	
	mostrarEvidenciaValidacion(evidencia)
	{
		this.modeloValidacion = evidencia;
		
	}
	
	set modeloValidacion(valor)
	{		
		this.modeloEdicion = valor;
		
		//this.modeloEdicion.cambioArchivo = false;
		//$("#usuarioProcedimientoId").val(this._modeloEdicion.usuarioProcedimientoId);
		$("#fechaAltaInput").val(this.modeloEdicion.fecha);
		$("#usuarioNombreInput").val(this.modeloEdicion.usuarioNombreCompleto);
		$("#empresaNombreInput").val(this.modeloEdicion.empresaNombre);
		$("#sedeNombreInput").val(this.modeloEdicion.sedeNombre);
		
		if(this.modeloEdicion.realizoActividad)
			$("#realizoActividadCheck").prop('checked', true);
		else
			$("#realizoActividadCheck").prop('checked', false);
		if(this.modeloEdicion.justificacionId!=null)
			$('#justificacionSelect').val(this.modeloEdicion.justificacionId);
		$('#comentariosInput').val(this.modeloEdicion.comentarios);
		
		//this.cambiarRealizoActividad();
		
		$("#prodecimientoNombreInput").val(this.modeloEdicion.nombre);
		$('#evidenciaImage').attr("src",HANDEL_API + "/images/tipos_archivo/vacio.png");
		//this.consultarJustificacionesEvidencia();
		
		if(this.modeloEdicion.justificacionId!=null)
			$("#justificacionInput").val(this.modeloEdicion.justificacionNombre) 
			
		if(this.modeloEdicion.validada)
			$("#validadaCheck").prop('checked', true);
		else
			$("#validadaCheck").prop('checked', false);
		
		$("#comentariosValidacionInput").val(this.modeloEdicion.comentariosValidacion);
    		
    	this.vistaPreviaArchivo(this.modeloEdicion.nombreArchivo);
    	
    
    	this.consultarComentariosPredefinidos();

		if(this._evidenciaSeleccionadaIndice == this._evidencias.length - 1)
			$("#guardarSiguienteButton").hide();
			
	}
	
	getUrlOfficeOnline(url)
	{
		var urlOffice =  "https://view.officeapps.live.com/op/embed.aspx?src="+url; 
		return urlOffice;				
	}
	
	vistaPreviaArchivo(nombre)
	{
		 $("#pdf").hide();
		$("#officeDiv").hide();
		$("#contenedorEvidenciaImage").hide();
		if(nombre=="")
			$('#evidenciaImage').attr("src",HANDEL_API + "/images/tipos_archivo/vacio.png");
		else
		{
			try
			{
				var elementos = nombre.split(".");
				if(elementos.length>1)
				{
					var tipo= elementos[elementos.length-1];
					var archivo = "evidencia"+this.modeloEdicion.id+"_" + encodeURIComponent(nombre);
					var url = HANDEL_API + "/php/archivos_evidencias/" + archivo;
					switch(tipo)
					{
						case "doc":
						case "docx":
							$("#officeDiv").show();
							url = this.getUrlOfficeOnline(url);  
							$("#officeIframe").attr("src",url);
							
							/*$('#evidenciaImage').width("100px");
							 $('#evidenciaImage').attr('src',HANDEL_API + "/images/tipos_archivo/word.png");
							$("#contenedorEvidenciaImage").fadeIn()*/
						break;
						case "xls":
						case "xlsx":
							// $("#officeDiv").show();
							/*$('#evidenciaImage').width("100px");
							 $('#evidenciaImage').attr('src',HANDEL_API + "/images/tipos_archivo/excel.png");
							$("#contenedorEvidenciaImage").fadeIn();*/
							$("#officeDiv").show();
							//$('#evidenciaImage').hide(); 
							url = this.getUrlOfficeOnline(url);  
							$("#officeIframe").attr("src",url);
						break;
						case "ppt":
						case "pptx":
						case "ppsx":
						 	//$("#officeDiv").show();
							/*$('#evidenciaImage').width("100px");
							 $('#evidenciaImage').attr('src',HANDEL_API + "/images/tipos_archivo/power_point.png");
							$("#contenedorEvidenciaImage").fadeIn();*/
							$("#officeDiv").show();
							//$('#evidenciaImage').hide(); 
							url = this.getUrlOfficeOnline(url);  
							$("#officeIframe").attr("src",url);
						break;
						case "pdf":
						// $("#pdf").show();
							$("#officeDiv").show();
							//$('#evidenciaImage').hide(); 
							 //$('#evidenciaImage').attr('src',HANDEL_API + "/images/tipos_archivo/pdf.png");
							 
							 
							 
							
							//var url = HANDEL_API + "/php/archivos_evidencias/" + archivo;
							//this.showPDF(url);
							$("#officeIframe").attr("src",url);
							 
						break;
	//					case "txt":
	//						 $('#evidenciaImage').attr('src',HANDEL_API + "/images/tipos_archivo/txt.png");
	//					break;
						default:
							$('#contenedorEvidenciaImage').show();
							$('#evidenciaImage').width("100px");
							$('#evidenciaImage').attr('src',HANDEL_API + "/images/tipos_archivo/archivo.png");
						break;
						case "jpg":
						case "png":
						case "bmp":
							$('#contenedorEvidenciaImage').show();
							$('#evidenciaImage').width("100%");
							$('#evidenciaImage').attr('src',url);
						break;
						
						
					}
				}
				else
				{
					$('#contenedorEvidenciaImage').show();
					$('#evidenciaImage').width("100px");
					$('#evidenciaImage').attr('src',HANDEL_API + "/images/tipos_archivo/archivo.png");
				}
			}
			catch(e)
			{
				$('#contenedorEvidenciaImage').show();
				$('#evidenciaImage').width("100px");
				 $('#evidenciaImage').attr('src',HANDEL_API + "/images/tipos_archivo/archivo.png");
			}
			
		}
	}
	
	consultarComentariosPredefinidos()
	{
		this.presentador.consultarComentariosPredefinidos();
	}
	
	set comentariosPredefinidos(comentarios)
	{
		$("#comentariosPredefinidosDiv").html("");
		for(var i=0; i < comentarios.length;i++)
		{
			var comentario = comentarios[i];
			
			var texto = $("#comentariosValidacionInput").val();
			var checked="";
			if(texto.includes(comentario.texto))
				checked="checked";
			var html="<div class='form-check  form-check-inline'>";
			html+="<input id='comentarioPredefinido"+comentario.id+"' class='form-check-input'  type='checkbox' "+checked+">";
			html+=" <label class='form-check-label' for='comentarioPredefinido"+comentario.id+"'>"+comentario.texto+"</label>";
			html+="</div>";
			$("#comentariosPredefinidosDiv").append(html);
			$("#comentarioPredefinido" + comentario.id).data("comentario",comentario);
			$("#comentarioPredefinido" + comentario.id).change(this.comentarioPredefinidoClick);
			
			
		
				
		}
	}
	
	comentarioPredefinidoClick(event)
	{
		var comentario =$("#"+event.currentTarget.id).data("comentario");
		if($("#"+event.currentTarget.id).is(':checked'))
		{
			var texto = $("#comentariosValidacionInput").val();
			texto+= comentario.texto + ". ";
			 $("#comentariosValidacionInput").val(texto);
			
		}
		else
		{
			var texto = $("#comentariosValidacionInput").val();
			texto = texto.replace(comentario.texto+ ". ","");
			 $("#comentariosValidacionInput").val(texto);
		}
	}
	
	validarEvidencia(cerrar)
	{
		this.presentador.validarEvidencia(cerrar);
	}
	
	set guardando(guardando)
	{
		if(guardando)
		{
			$("#guardarButton").attr("disabled",true);
			$("#guardarSiguienteButton").attr("disabled",true);
		}
		else
		{
			$("#guardarButton").attr("disabled",false);
			$("#guardarSiguienteButton").attr("disabled",false);
			
		}
	}
	
	get modeloValidacion()
	{
		 var modelo = 
		 {		
			 id:  this.modeloEdicion.id,
			 comentariosValidacion:$('#comentariosValidacionInput').val(),
			 validada:$('#validadaCheck').is(':checked')?1:0,
			 nombre : $("#prodecimientoNombreInput").val(),
			 justificada : this._evidenciaSeleccionada.justificada,
			 nombreArchivo : this._evidenciaSeleccionada.nombreArchivo
			
		 };
		 return modelo;
	 }

	borrarEvidencia(id)
	{
		var row = $("#tabla").find("tr[data-id="+id+"]");
		if(row!=null)
			row.remove();
	}
	
	actualizarEvidencia(modelo)
	{
		if(this.ocultar(modelo))
			this.borrarEvidencia(modelo.id);
		else 
			this.actualizarEstatus(modelo);
	}
	
	actualizarEstatus(modelo)
	{
		$('#estatusValidacionTabla'+modelo.id).html(this.getEstatusValidacion(modelo));
	}
	
	getEstatusValidacion(renglon)
	{
		var contenido ="";
		if(renglon.estatusValidacionId!=0)
		{
			contenido += "<center><span data-toggle='tooltip' data-placemen='bottom' title='"+renglon.estatusValidacionDescripcion+"'  class='"+renglon.estatusValidacionIcono+" fa-lg "+renglon.estatusValidacionColor+"' ></span></center>";
		
		}
		return contenido;
	}
	
	getEstatusRevision(renglon)
	{
		var contenido ="";
		if(renglon.estatusRevisionId!=0)
		{
			contenido += "<center><span data-toggle='tooltip' data-placemen='bottom' title='"+renglon.estatusRevisionDescripcion+"'  class='"+renglon.estatusRevisionIcono+" fa-lg "+renglon.estatusRevisionColor+"' ></span></center>";
		
		}
		return contenido;
	}
	
	
	
	getArchivoEvidencia(modelo)
	{
		var contenido = "";
		var comentarios ="";
		if(modelo.justificada==0)
		{
			var iconoColor = vista.getIconoArchivo(modelo.nombreArchivo);
			if(modelo.validada==1)
				comentarios = "<span class='label-success' style='position: relative;top: 6px;right: 4px;font-size: 10px;padding: 2px 3px;line-height: .9;'><i class='fas fa-check-double'></i></span>";
			contenido = "<center><span style='cursor:pointer;margin-left:15px;width:50px;height:30px' data-toggle='tooltip' data-placemen='bottom' title='Evidencia'  type='button' class='archivo'><span  data-toggle='tooltip' class='"+iconoColor.icono+" fa-lg "+iconoColor.color+"'>"+comentarios+"</span></center>";
		}
		else
		{
			if(modelo.validada==1)
				comentarios = "<span class='label-success' style='position: relative;top: 6px;right: 4px;font-size: 10px;padding: 2px 3px;line-height: .9;'><i class='fas fa-check-double'></i></span>";
			contenido = "<center>"+comentarios+"</center>";
	
		}
	    return contenido;
	}
	
	ocultar(modelo)
	{
		var o = false;
		if(this._criteriosSeleccionValidada=="")
			o = false;
		else
		{
			var validada = parseInt(this._criteriosSeleccionValidada);
			switch(validada)
			{
				case 1:
					if(modelo.validada)
						o = false;
					else 
						o = true;
				break;
				case 0:
					if(modelo.validada)
						o = true;
					else
						o = false;
				break;	
			}
		}
		
		return o;
	}
	
	
	set modeloProceso(modeloProceso)
	{
		this._modeloProceso = modeloProceso;
		$("#estatusValidacionIcono").attr("class","");
		$("#estatusValidacionIcono").addClass(this._modeloProceso.estatusValidacionIcono);
		$("#estatusValidacionIcono").addClass(this._modeloProceso.estatusValidacionColor);
		$("#estatusValidacionLabel").html(this._modeloProceso.estatusValidacionNombre);
		//$('#comentariosObservacionTabla'+this._modeloProceso.id).html(this.getComentariosObservacion(this._modeloProceso));

		
		this._procesoSeleccionado.estatusValidacionIcono = this._modeloProceso.estatusValidacionIcono;
		this._procesoSeleccionado.estatusValidacionColor = this._modeloProceso.estatusValidacionColor;
		this._procesoSeleccionado.estatusValidacionDescripcion = this._modeloProceso.estatusValidacionDescripcion;
		
		$("#estatusValidacionTabla"+this._modeloProceso.id).html(this.getEstatusValidacion(this._modeloProceso ));
		$("#fotoValidadorTabla"+this._modeloProceso.id).html(this.getFotoValidador(this._modeloProceso ));
		$("#nombreValidadorTabla"+this._modeloProceso.id).html(this.getNombreValidador(this._modeloProceso ));
		$("#fechaValidacionTabla"+this._modeloProceso.id).html(this.getFechaValidacion(this._modeloProceso ));
		
	}
	
	set observaciones(observaciones)
	{
		this.observacionesTabla.registros = observaciones;
		this.inicializarEventosBotonesTablaObservaciones("#" + this.observacionesTabla._id+"Table tbody",this.observacionesTabla.datatable.DataTable());
	
	}
	
	inicializarEventosBotonesTablaObservaciones(tbody, table)
	{
		var _this = this;
		$(tbody).on("click", "span.comentarios", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

			_this._observacionSeleccionada  = table.row( tr ).data();
			if (_this._observacionSeleccionada != undefined)
			{
				_this._llavesObservacion = _this.copiarPropiedadesObjeto(_this._observacionSeleccionada, ["id"]);
				_this._llavesObservacion.procesoRevisadoId = _this._procesoSeleccionado.id;
				_this.mostrarComentariosObservacion();

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
			{longitud:100, 	titulo:"Tipo",   alias:"tipoObservacionNombre", alineacion:"I"},
			{longitud:200, 	titulo:"Sección",   alias:"seccion", alineacion:"I"},
			{longitud:300, 	titulo:"Descripción",   alias:"descripcion", alineacion:"I"},
			
			//{longitud:100, 	titulo:"",   alias:"tamano", alineacion:"C",itemRenderer:this.renderTamanoArchivo},
			//{longitud:100, 	titulo:"",   alias:"subido", alineacion:"C",itemRenderer:this.renderSubido}
		
		];
		this.observacionesTabla.columnas.push({longitud:30, 	titulo:"",   alias:"comentarios", alineacion:"I", itemRenderer:this.renderComentariosObservacion}),		
	
/*			this.observacionesTabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-1 botones-icon btn btn-sm float-left btn-success active'><span  data-toggle='tooltip' class='fas fa-pencil-alt fa-lg'></span></button>"+
													"<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";
*/
	
		
		this.observacionesTabla.textoTablaVacia = "No hay observaciones";
		this.observacionesTabla.textoSinRegistros= "No hay observaciones";
		this.observacionesTabla.registros = [];
		
		
		
	}
	
	renderComentariosObservacion(renglon, type, set)
	{  
		return "<div id='comentariosObservacionTabla"+renglon.id+"'>" + vista.getComentariosObservacion(renglon) + "</div>";
	}
	
	getComentariosObservacion(renglon)
	{    
		var contenido = "";
		var comentarios ="";
		if(renglon.numeroComentarios>0)
			comentarios = "<span class='label-warning notificacion'>"+renglon.numeroComentarios+"</span>";
		contenido = "<span style='cursor:pointer;margin-left:15px;width:50px;height:30px;color:gray;' data-toggle='tooltip' data-placemen='bottom' title='Comentarios' type='button' class='comentarios text-blue'><span  data-toggle='tooltip' class='fas fa-comments fa-lg'>"+comentarios+"</span>";;
	    return contenido;
	}
	
	mostrarComentariosObservacion()
	{
		if($("#modalAlta").length ==0)
		{
			var url = HANDEL_API + "/html/modales/comentarios.php";
			this.mostrarIndicador();
			var _this = this;
			$.post(url,{}, function(html) 
			{
				_this.ocultarIndicador();
				$("body").append(html);
				$("#modalAlta").on("hidden.bs.modal", function () 
				{
					clearInterval(_this.cometariosObservacionIntervalId);
					//TODO: actualizar icono de comentarios y demas
					_this.consultarObservacionPorLlaves(false);
					$("#modalAlta").remove();
				});
				
				$("#modalAlta").on("show.bs.modal", function () 
				{
					//_this.inicializarValidacionesComentarioEvidencia();
					$("#accionAvanceLabel").html(_this._observacionSeleccionada.descripcion);
					$("#enviarComentarioButton").click(function () 
					{
						var comentario = $("#comentarioEvidenciaInput").val().trim();
						if(comentario!="" && comentario!=undefined)
							_this.enviarComentarioObservacion();
					});
					$("#comentarioEvidenciaInput").keypress(function(event){
					    var keycode = (event.keyCode ? event.keyCode : event.which);
					    if(keycode == '13')
					    {
					    	var comentario = $("#comentarioEvidenciaInput").val().trim();
							if(comentario!="" && comentario!=undefined)
								_this.enviarComentarioObservacion();
					    }
					});
					_this._comentariosObservacion = [];
					_this.consultarComentariosObservacion();
					_this.cometariosObservacionIntervalId = setInterval(_this.consultarComentariosAutomaticamente, 60000);
						
				});
			
				$("#modalAlta").modal({backdrop: 'static', keyboard: false});
			});
			
			
		}
		else
		{
			$("#modalAlta").modal({backdrop: 'static', keyboard: false});
		}
	}
	
	set comentariosObservacion(comentariosObservacion)
	{
		if(comentariosObservacion.length> this._comentariosObservacion.length)
		{
			this._comentariosObservacion = comentariosObservacion;
			var fecha = new Date();
			var html="";
			for(var i=0; i< comentariosObservacion.length; i++)
			{
				var comentario = comentariosObservacion[i];
				
				var foto ="";
				if(comentario.fotoPerfil.includes("default.jpg"))
					foto = comentario.fotoPerfil;
				else
					foto = comentario.fotoPerfil+"?"+vista.time;
				
				
				//var foto = HANDEL_API + "/" + comentario.fotoPerfil+"?"+fecha.getTime();
				var url = HANDEL_API + "/" + foto;
				html+="<div class='item'>" +
						"<img src='"+url+"' alt='user image' class='online' > " +
						"<p class='message'>" +
						"  <a href='#' class='name'>" +
						"	<small class='text-muted pull-right'><i class='fa fa-clock-o'></i> "+comentario.fecha +"</small>" + comentario.usuarioNombreCompleto +
						"  </a>" + comentario.comentario + 
						"</p>" +
					  "</div>";
			}
			$("#chatbox").html(html);
		}	
	}
	
	consultarComentariosAutomaticamente()
	{
		var _this  = $("body").data("_this");
		_this.consultarComentariosObservacion();
	}
	
	enviarComentarioObservacion()
	{
		this.presentador.enviarComentarioObservacion();
		$("#comentarioEvidenciaInput").val("");
	}
	
	consultarComentariosObservacion()
	{
		this.presentador.consultarComentariosObservacion();
	}
	
	get modeloComentarioObservacion()
	{
		var modelo =
		{
			procesoRevisadoId : this._procesoSeleccionado.id,
			observacionId: this._observacionSeleccionada.id,
			usuarioId: this.usuario.id,
			comentario: $("#comentarioEvidenciaInput").val()
		};
		return modelo;
	}
	
	consultarObservacionPorLlaves(formulario)
	{
		this._formularioObservacion = formulario;
		this.presentador.consultarObservacionPorLlaves();
	}
	
	get llavesObservacion()
	{
		return this._llavesObservacion;
	}
	
	set modeloObservacion(modeloObservacion)
	{
		this._modeloObservacion = modeloObservacion;
		//$('#cumplimientoRecomendacionTabla'+this._modeloObservacion.id).html(this.getCumplimiento(this._modeloRecomendacion));
		//$('#fotoUsuarioRecomendacionTabla'+this._modeloObservacion.id).html(this.getFotoUsuario(this._modeloRecomendacion));
		//$('#nombreUsuarioRecomendacionTabla'+this._modeloObservacion.id).html(this.getTexto(this._modeloRecomendacion.usuarioNombreCompleto));
		//$('#estatusValidacionRecomendacionTabla'+this._modeloObservacion.id).html(this.getEstatusValidacionRecomendacion(this._modeloRecomendacion));
		$('#comentariosObservacionTabla'+this._modeloObservacion.id).html(this.getComentariosObservacion(this._modeloObservacion));
		/*$("#estatusValidacionIcono").attr("class","");
		$("#estatusValidacionIcono").addClass(this._modeloObservacion.estatusValidacionIcono);
		$("#estatusValidacionIcono").addClass(this._modeloObservacion.estatusValidacionColor);
		$("#estatusValidacionLabel").html(this._modeloObservacion.estatusValidacionDescripcion);
		
		this._recomendacionSeleccionada.cumplimiento = modeloRecomendacion.cumplimiento;
		this._recomendacionSeleccionada.estatusValidacionIcono = modeloRecomendacion.estatusValidacionIcono;
		this._recomendacionSeleccionada.estatusValidacionColor = modeloRecomendacion.estatusValidacionColor;
		this._recomendacionSeleccionada.estatusValidacionDescripcion = modeloRecomendacion.estatusValidacionDescripcion;
		this._recomendacionSeleccionada.usuarioId = modeloRecomendacion.usuarioId;
		this._recomendacionSeleccionada.usuarioNombre = modeloRecomendacion.usuarioNombre;
		this._recomendacionSeleccionada.usuarioApellido = modeloRecomendacion.usuarioApellido;
		this._recomendacionSeleccionada.usuarioNombreCompleto = modeloRecomendacion.usuarioNombreCompleto;
		*/
		/*if(this.usuario.tipoUsuarioId==TipoUsuario.ADMINISTRADOR && this._recomendacionSeleccionada.cumplimiento==100)
			$("#validarRecomendacionButton").show();
		else
			$("#validarRecomendacionButton").hide();
		if(this._formularioRecomendacion)	
			this.consultarResponsablesRecomendacion();*/
	}
	
}

var vista = new RevisionProcesosVista(this);	
$(document).ready(function() 
{
	vista.inicializar();
});