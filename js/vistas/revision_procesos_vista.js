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
		$("#validarSinCambiosButton").click(function(){
			_this.validarSinCambios();
		});
		
		if(this.procesoRevisadoIdParametro!=0 && this.observacionIdParametro!=0)
			this.mostrarObservacionParametro();
		else if(this.procesoRevisadoIdParametro!=0)
			this.mostrarProcesoRevisadoParametro();
			
		$("#historialButton").click(function(){
			var submitForm = _this.getNewSubmitForm("historial_revision_procesos.php");
	   		submitForm.target= "_blank";
	    	submitForm.submit();
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
		var color ="btn-success";
		var titulo = "Revisar ";
		if(renglon.estatusRevisionId == EstatusRevision.NO_HUBO_CAMBIOS)
		{
			color="btn-warning";
			titulo+=", no hubo cambios";
		}	
		else
		{
			if(renglon.numeroObservaciones==1)
				titulo+=" 1 observación";
			else 
			 titulo+=" "+ renglon.numeroObservaciones + " observaciones";
		}
		//contenido += "<button data-toggle='tooltip' data-placemen='bottom' title='"+titulo+"'  type='button' class='revisar btn-circle mr-0 botones-icon btn btn-sm float-right "+color+"'><span  data-toggle='tooltip' class='fa fa-check fa-lg'></span></button>";
		
		//var contenido = "";
		var observaciones ="";
		if(renglon.numeroObservaciones>0)
			observaciones = "<span class='label-warning notificacion'>"+renglon.numeroObservaciones+"</span>";
		contenido = "<button style='width:40px;' data-toggle='tooltip' data-placemen='bottom' title='"+titulo+"'  type='button' class='revisar btn-circle mr-0 botones-icon btn btn-sm float-right "+color+"'><i  class='fas fa-check fa-lg'></i>"+observaciones+"</button>";;	
	    return contenido;
	}
	
	renderRevisarObservacion(renglon, type, set)
	{    
		var contenido = "";
		contenido += "<button data-toggle='tooltip' data-placemen='bottom' title='Revisar'  type='button' class='revisar btn-circle mr-0 botones-icon btn btn-sm float-right btn-success'><span  data-toggle='tooltip' class='fa fa-check fa-lg'></span></button>";
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
	
	renderNombreValidadorObservacion(renglon, type, set)
	{    
		return "<div id='nombreValidadorObservacionTabla"+renglon.id+"'>" + vista.getNombreValidador(renglon) + "</div>";
	
		
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
	
	renderFechaValidacionObservacion(renglon, type, set)
	{    
		return "<div id='fechaValidacionObservacionTabla"+renglon.id+"'>" + vista.getFechaValidacion(renglon) + "</div>";
	
		
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
		var icono = HANDEL_API+ "/"+renglon.empresaLogo+"?"+vista.time;
		contenido += "<center><img src='" + icono + "' style='width:30px;height:30px;border-radius: 50%'></img></center>";
	    return contenido;
	}
	
	renderEstatusValidacion(renglon, type, set)
	{    
		return "<div id='estatusValidacionTabla"+renglon.id+"'>" + vista.getEstatusValidacion(renglon) + "</div>";
	}
	
	renderEstatusValidacionObservacion(renglon, type, set)
	{    
		return "<div id='estatusValidacionTablaObservacion"+renglon.id+"'>" + vista.getEstatusValidacion(renglon) + "</div>";
	}
	
	renderSeccionValidacionObservacion(renglon, type, set)
	{    
		return "<div id='seccionValidacionObservacionTabla"+renglon.id+"'>" + vista.getSeccionValidacion(renglon) + "</div>";
	}
	
	renderDescripcionValidacionObservacion(renglon, type, set)
	{    
		return "<div id='descripcionValidacionObservacionTabla"+renglon.id+"'>" + vista.getDescripcionValidacion(renglon) + "</div>";
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
		
		if(ArrayUtils.searchWithValues("id",[fecha.getFullYear()],anos)==null)
		{
			anos.push({id:fecha.getFullYear(), nombre:fecha.getFullYear()})
			
		}
		
		anos.unshift({id:"", nombre:"Todos los años"})
		
		
		this.cargarOpciones('#anoSelectCriterio', anos);
		
		//$("#mesSelectCriterio").val(fecha.getMonth()+1);
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
		$("#administradorSelectCriterio").val(this.usuario.id);
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
		$("#estadoValidacionSelectCriterio").val(EstatusValidacionProceso.EN_PROCESO_DE_ANALISIS);
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
		var sinCambiosSinValidar = ArrayUtils.filterWithValues("estatusValidacionId,estatusRevisionId",[EstatusValidacionProceso.EN_PROCESO_DE_ANALISIS,EstatusRevision.NO_HUBO_CAMBIOS],datos);
		if(sinCambiosSinValidar.length>0)
		{
			$("#sinCambiosSpan").html("("+sinCambiosSinValidar.length+")");
			$("#validarSinCambiosButton").fadeIn();
		}
		else
			$("#validarSinCambiosButton").fadeOut();
		this._criteriosSeleccionValidada = this.criteriosSeleccion.validada;
	}
	
	validarSinCambios()
	{
		var sinCambiosSinValidar = ArrayUtils.filterWithValues("estatusValidacionId,estatusRevisionId",[EstatusValidacionProceso.EN_PROCESO_DE_ANALISIS,EstatusRevision.NO_HUBO_CAMBIOS],this.tabla.registros);
		var ids = ArrayUtils.join(sinCambiosSinValidar,"id");
		this.presentador.validarSinCambios(ids);
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
		$("#validarSinCambiosButton").fadeOut();
		var texto = "";
		if(registros.length==1)
			texto = "validó 1 proceso";
		else
			texto = "validaron "+registros.length+" procesos";
		this.mostrarMensaje("","Se "+ texto +" marcados sin cambios correctamente")
	}
	
	set cargando(cargando)
	{
		super.cargando = cargando;
		if(cargando)
			$("#validarSinCambiosButton").attr("disabled",true);
		else
			$("#validarSinCambiosButton").attr("disabled",false);
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
					_this.consultarProcesoRevisadoPorLlaves(_this._llavesProceso);
					//_this.mostrarFormularioRevision();
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
	
	mostrarFormularioRevision(proceso)
	{
		
		var _this = this;
		this.mostrarFormularioHTML(HANDEL_API+"/html/modales/proceso_revisado_validacion.php",this, null, function()
		{
			//this.inicializarValidacionesFormularioValidacion();
			$("#procesoLabel").html(proceso.nombre);
			$("#estatusRevisionIcono").addClass(proceso.estatusRevisionIcono);
			$("#estatusRevisionIcono").addClass(proceso.estatusRevisionColor);
			$("#estatusRevisionLabel").html(proceso.estatusRevisionDescripcion);
			$("#estatusValidacionIcono").addClass(proceso.estatusValidacionIcono);
			$("#estatusValidacionIcono").addClass(proceso.estatusValidacionColor);
			$("#estatusValidacionLabel").html(proceso.estatusValidacionDescripcion);
			if(proceso.estatusRevisionId == EstatusRevision.OBSERVACIONES)
			{
				$("#observacionesLabel").fadeIn();	
				$("#agregarObservacionButton").fadeIn();			
				this.crearTablaObservaciones();
			}
			else
			{
				$("#agregarObservacionButton").fadeOut();
				$("#verificacionButton").fadeIn();	
				$("#autorizadoButton").fadeIn();	
				$("#rechazadoButton").fadeIn();	
				$("#respondioButton").fadeIn();	
				$("#estatusValidacionDiv").fadeIn();	
				
			}
				
			this.crearEventosBotonesValidacion();
			
			$("#agregarObservacionButton").click(function(){_this.mostrarObservacion(Modo.ALTA,proceso);});
			
			
		},null,"procesoModal","","guardarValidacionButton", function()
		{
			$("#formularioValidacion").submit();
		});
	}
	
	
	mostrarObservacion(modo,usuarioProceso, observacion)
	{
		var _this = this;
		this.modoObservacion = modo;
		this.mostrarFormularioHTML(HANDEL_API+"/html/modales/proceso_revisado_observacion.php",this, null, function()
		{
			$("#procesoObservacionLabel").html(usuarioProceso.nombre);
			if(modo==Modo.CAMBIO)
				_this.modeloObservacion = observacion;
			this.inicializarValidacionesObservacion();
			_this.consultarTiposObservacion();
			
			
		},null,"observacionModal","","guardarObservacionButton",function()
		{
			$("#observacionFormulario").submit();
		});	
		
	}
	
	consultarTiposObservacion()
	{
		this.presentador.consultarTiposObservacion();
	}
	
	set tiposObservacion(registros)
	{		
		//this.cargarOpciones('#tipoObservacionSelect', registros);
		this.cargarOpciones('#tipoObservacionSelect', registros, this.modoObservacion, this._observacionSeleccionada, 'tipoObservacionId',"");
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
                "tipoObservacionSelect": {
                    required: !0
                },
                "seccionObservacionInput": {
                    required: !0
                },
                "descripcionObservacionInput": {
                    required: !0
                }
            },
            messages: {
                "tipoObservacionSelect": "Por favor seleccione un tipo",
                "seccionObservacionInput": "Por favor ingrese una sección",
                "descripcionObservacionInput": "Por favor ingrese una descripción"
                	
                
            },
            submitHandler:function (form) {
            	 _this.guardarObservacion();
            }
        });
	}
	
	
	guardarObservacion()
	{	
		var observacion = this.modeloObservacion;
		this.guardarObservacionNueva(observacion);
		
	}
	
	
	crearEventosBotonesValidacion()
	{
		var _this = this;
		$("#verificacionButton").click(function () 
		{
			_this.actualizarEstatusValidacionProceso(this,EstatusValidacionProceso.EN_VERIFICACION);
		});
		$("#autorizadoButton").click(function () 
		{
			_this.actualizarEstatusValidacionProceso(this,EstatusValidacionProceso.AUTORIZADO);
		});
		$("#rechazadoButton").click(function () 
		{
			_this.actualizarEstatusValidacionProceso(this,EstatusValidacionProceso.RECHAZADO);
		});
		$("#respondioButton").click(function () 
		{
			_this.actualizarEstatusValidacionProceso(this,EstatusValidacionProceso.RESPONDIO);
		});
	}
	
	
	
	crearEventosBotonesValidacionObservacion()
	{
		var _this = this;
		$("#verificacionObservacionButton").click(function () 
		{
			_this.actualizarEstatusValidacionObservacion(this,EstatusValidacionProceso.EN_VERIFICACION);
		});
		$("#autorizadoObservacionButton").click(function () 
		{
			_this.actualizarEstatusValidacionObservacion(this,EstatusValidacionProceso.AUTORIZADO);
		});
		$("#rechazadoObservacionButton").click(function () 
		{
			_this.actualizarEstatusValidacionObservacion(this,EstatusValidacionProceso.RECHAZADO);
		});
		$("#respondioObservacionButton").click(function () 
		{
			_this.actualizarEstatusValidacionObservacion(this,EstatusValidacionProceso.RESPONDIO);
		});
	}
	
	actualizarEstatusValidacionObservacion(boton,estatusValidacionId)
	{
		var _this = this;
		this.mostrarFormularioHTML(HANDEL_API+"/html/modales/cambio_estatus_validacion.php",this, null, function()
		{
			$("#procesoValidacionLabel").html(_this._procesoSeleccionado.nombre);
			if(estatusValidacionId==EstatusValidacionProceso.AUTORIZADO)
			{
				$("#seccionValidacionDiv").show();
				$("#descripcionValidacionDiv").show();
				
				if(_this._observacionSeleccionada.seccionAdmin!="")
					$("#seccionValidacionInput").val(_this._observacionSeleccionada.seccionAdmin);
				else
					$("#seccionValidacionInput").val(_this._observacionSeleccionada.seccion);
					
				if(_this._observacionSeleccionada.descripcionAdmin!="")	
					$("#descripcionValidacionInput").val(_this._observacionSeleccionada.descripcionAdmin);
				else
					$("#descripcionValidacionInput").val(_this._observacionSeleccionada.descripcion);
			}
			$("#validacionComentarioButton").html($(boton).html());
			$("#validacionComentarioButton").prop("style", $(boton).attr("style")).addClass($(boton).attr("class"));
			
		},null,"validacionComentarioModal","","validacionComentarioButton", function()
		{
			var comentario = $("#comentarioValidacionInput").val();
			var seccion = $("#seccionValidacionInput").val();
			var descripcion = $("#descripcionValidacionInput").val();
			
			this.presentador.actualizarEstatusValidacionObservacion(estatusValidacionId,comentario,seccion, descripcion);
		});
	}
	
	actualizarEstatusValidacionProceso(boton,estatusValidacionId)
	{
		/*this.mostrarFormularioHTML(HANDEL_API+"/html/modales/cambio_estatus_validacion.php",this, null, function()
		{
			$("#validacionComentarioButton").html($(boton).html());
			$("#validacionComentarioButton").prop("style", $(boton).attr("style")).addClass($(boton).attr("class"));
			
		},null,"validacionComentarioModal","","validacionComentarioButton", function()
		{
			var comentario = $("#comentarioValidacionInput").val();*/
			this.presentador.actualizarEstatusValidacionProceso(estatusValidacionId,comentario);
		/*});*/
		
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
	
	consultarProcesoRevisadoPorLlaves(llaves)
	{
		this.presentador.consultarProcesoRevisadoPorLlaves(llaves);
	}
	
	consultarObservacionPorLlavesValidacion(llaves)
	{
		this.presentador.consultarObservacionPorLlavesValidacion(llaves);
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
	
	
	
	getComentariosEvidencia(renglon)
	{    
		var contenido = "";
		var comentarios ="";
		if(renglon.numeroComentarios>0)
			comentarios = "<span class='label-warning notificacion'>"+renglon.numeroComentarios+"</span>";
		contenido = "<center><span style='cursor:pointer;margin-left:15px;width:50px;height:30px;color:gray;' data-toggle='tooltip' data-placemen='bottom' title='Comentarios' type='button' class='comentarios text-blue'><span  data-toggle='tooltip' class='fas fa-comments fa-lg'>"+comentarios+"</span></center>";
	    return contenido;
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
			if(renglon.estatusRevisionId != EstatusRevision.OBSERVACIONES)
				contenido += "<center><span data-toggle='tooltip' data-placemen='bottom' title='"+renglon.estatusValidacionDescripcion+"'  class='"+renglon.estatusValidacionIcono+" fa-lg "+renglon.estatusValidacionColor+"' ></span></center>";
			else
				contenido +="<center><span data-toggle='tooltip' data-placemen='bottom' title='Revise el estado de solicitud de cada observación'>NA</span></center>";
		}
		return contenido;
	}
	
	getSeccionValidacion(renglon)
	{
		var contenido ="";
		if(renglon.seccionAdmin=="" || renglon.seccionAdmin==null)
			contenido = renglon.seccion;
		else if(renglon.seccion==renglon.seccionAdmin)
			contenido = renglon.seccion;
		else
			contenido = renglon.seccion + " => " + renglon.seccionAdmin;
		return contenido;
	}
	
	getDescripcionValidacion(renglon)
	{
		var contenido ="";
		
		if(renglon.descripcionAdmin=="" || renglon.descripcionAdmin==null)
			contenido = renglon.descripcion;
		else if(renglon.descripcion==renglon.descripcionAdmin)
			contenido = renglon.descripcion;
		else
			contenido = renglon.descripcion + " => " + renglon.descripcionAdmin;
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
		
		$(tbody).on("click", "button.revisar", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

			_this._observacionSeleccionada  = table.row( tr ).data();
			
			
			if (_this._observacionSeleccionada != undefined)
			{
				//_this._llaves = _this.copiarPropiedadesObjeto(_this._procesoSeleccionado, ["id"]);
				//if(_this.usuario.tipoUsuarioId==TipoUsuario.ADMINISTRADOR)
				//{
				_this._llavesObservacion = _this.copiarPropiedadesObjeto(_this._observacionSeleccionada, ["id"]);
				_this._llavesObservacion.procesoRevisadoId = _this._procesoSeleccionado.id;
				_this.consultarObservacionPorLlavesValidacion(_this._llavesObservacion);
					//_this.mostrarFormularioRevision();
				//}
				/*else if(_this.usuario.tipoUsuarioId==TipoUsuario.COORDINADOR || this.usuario.tipoUsuarioId==TipoUsuario.SUPERVISOR)
				{
					_this.modo = Modo.CONSULTA
					_this.mostrarFormularioEvidencia(_this._evidenciaSeleccionada);
				}*/
			
			}
		});
	}
	
	crearTablaObservaciones()
	{
		this.observacionesTabla = new Tabla("observacionesTabla");
		this.observacionesTabla.alto = 240;
		this.observacionesTabla.buscar = false;
		this.observacionesTabla.paginacion = false;
		this.observacionesTabla.columnas = [
			{longitud:100, 	titulo:"Tipo",   alias:"tipoObservacionNombre", alineacion:"I"},
			{longitud:200, 	titulo:"Sección",   alias:"seccion", alineacion:"I", itemRenderer:this.renderSeccionValidacionObservacion},
			{longitud:300, 	titulo:"Descripción",   alias:"descripcion", alineacion:"I", itemRenderer:this.renderDescripcionValidacionObservacion},
			{longitud:30, 	titulo:"Estado de solicitud",   alias:"estatusValidacionNombre", alineacion:"C", itemRenderer:this.renderEstatusValidacionObservacion},
			{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"I" ,itemRenderer:this.renderFotoValidador},
			{longitud:100, 	titulo:"Usuario que validó",   alias:"validadorNombreCompleto", alineacion:"I",itemRenderer:this.renderNombreValidadorObservacion},
			{longitud:200, 	titulo:"Fecha validación",   	alias:"fechaValidacion", alineacion:"I", itemRenderer: this.renderFechaValidacionObservacion },
	
			//{longitud:100, 	titulo:"",   alias:"tamano", alineacion:"C",itemRenderer:this.renderTamanoArchivo},
			//{longitud:100, 	titulo:"",   alias:"subido", alineacion:"C",itemRenderer:this.renderSubido}
		
		];
		this.observacionesTabla.columnas.push({longitud:30, 	titulo:"",   alias:"comentarios", alineacion:"I", itemRenderer:this.renderComentariosObservacion});	
		this.observacionesTabla.columnas.push({longitud:30, 	titulo:"",  alias:"", alineacion:"I" ,itemRenderer:this.renderRevisarObservacion});
	
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
	
	mostrarFormularioRevisionObservacion(observacion)
	{
		var _this = this;
		this.mostrarFormularioHTML(HANDEL_API+"/html/modales/revisar_observacion.php",this, null, function()
		{
			//this.inicializarValidacionesFormularioValidacion();
			$("#procesoObservacionLabel").html(_this._modeloProceso.nombre);
			$("#observacionLabel").html(observacion.descripcion);
			$("#estatusValidacionObservacionIcono").addClass(observacion.estatusValidacionIcono);
			$("#estatusValidacionObservacionIcono").addClass(observacion.estatusValidacionColor);
			//$("#estatusRevisionObservacionLabel").html(observacion.estatusRevisionDescripcion);
			$("#estatusValidacionObservacionLabel").html(observacion.estatusValidacionDescripcion);
			
				
			this.crearEventosBotonesValidacionObservacion();
			
			
		},null,"observacionModal","","guardarValidacionButton", function()
		{
			//$("#formularioValidacion").submit();
		});
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
					if(_this._observacionSeleccionada!=null)
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
					_this.consultarObservacionPorLlaves(false);
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
	
	get modeloObservacion()
	{
		 var modelo = 
		 {		
			 tipoObservacionNombre: $( "#tipoObservacionSelect option:selected" ).text(),
			 tipoObservacionId:$('#tipoObservacionSelect').val(),
		 	 seccion:$('#seccionObservacionInput').val(),
			 descripcion : $("#descripcionObservacionInput").val(),
			
		 };
		 return modelo;
	 }
	
	set modeloObservacion(modeloObservacion)
	{
		this._modeloObservacion = modeloObservacion;
		//$('#cumplimientoRecomendacionTabla'+this._modeloObservacion.id).html(this.getCumplimiento(this._modeloRecomendacion));
		//$('#fotoUsuarioRecomendacionTabla'+this._modeloObservacion.id).html(this.getFotoUsuario(this._modeloRecomendacion));
		//$('#nombreUsuarioRecomendacionTabla'+this._modeloObservacion.id).html(this.getTexto(this._modeloRecomendacion.usuarioNombreCompleto));
		//$('#estatusValidacionRecomendacionTabla'+this._modeloObservacion.id).html(this.getEstatusValidacionRecomendacion(this._modeloRecomendacion));
		$('#comentariosObservacionTabla'+this._modeloObservacion.id).html(this.getComentariosObservacion(this._modeloObservacion));
		$('#fotoValidadorTabla'+this._modeloObservacion.id).html(this.getFotoValidador(this._modeloObservacion));
		$('#nombreValidadorObservacionTabla'+this._modeloObservacion.id).html(this.getNombreValidador(this._modeloObservacion));
		$('#fechaValidacionObservacionTabla'+this._modeloObservacion.id).html(this.getFechaValidacion(this._modeloObservacion));
		$('#seccionValidacionObservacionTabla'+this._modeloObservacion.id).html(this.getSeccionValidacion(this._modeloObservacion));
		$('#descripcionValidacionObservacionTabla'+this._modeloObservacion.id).html(this.getDescripcionValidacion(this._modeloObservacion));
		
	
		
		this._observacionSeleccionada.estatusValidacionIcono = this._modeloObservacion.estatusValidacionIcono;
		this._observacionSeleccionada.estatusValidacionColor = this._modeloObservacion.estatusValidacionColor;
		this._observacionSeleccionada.estatusValidacionDescripcion = this._modeloObservacion.estatusValidacionDescripcion;
		
		$("#estatusValidacionTablaObservacion"+this._modeloObservacion.id).html(this.getEstatusValidacion(this._modeloObservacion ));
			
		$("#headerBox").fadeIn();
		var html = `<div class="form-group">
						<div>
							<label class="control-label">Proceso</label>
							<span  class="" style='display:block;font-size:13px;'>`+ modeloObservacion.procesoNombre +`</span>
						</div>
					</div>
					<div class="form-group">
						<div>
							<label class="control-label">Observación</label>
							<span  class="" style='display:block;font-size:13px;'>`+ modeloObservacion.descripcion +`</span>
						</div>
					</div>
					`;
		
		//<div><label>" ++ "</label>";
		$("#headerBox").html(html);
			
	}
	
	get procesoRevisadoIdParametro()
	{
		var id = $("body").attr("data-id");
		var elementos = id.split("_");
		if(elementos.length>0)
		{
			return elementos[0];
		}
		return 0;
	}
	
	get observacionIdParametro()
	{
		var id = $("body").attr("data-id");
		var elementos = id.split("_");
		if(elementos.length==2)
		{
			return elementos[1];
		}
		return 0;
	}
	
	mostrarObservacionParametro()
	{
		this._procesoSeleccionado = {id : this.procesoRevisadoIdParametro};
		this._observacionSeleccionada = {id : this.observacionIdParametro};
		this._llavesObservacion = {procesoRevisadoId:this.procesoRevisadoIdParametro, 
									id : this.observacionIdParametro};
							
		this.mostrarComentariosObservacion();//this.editarTareaFormulario(this.listaTareas, this._registroSeleccionado.id, this.tareaIdParametro);
	}
	
	mostrarProcesoRevisadoParametro()
	{
		this._procesoSeleccionado = {id : this.procesoRevisadoIdParametro};
		this._llavesProceso = {id : this.procesoRevisadoIdParametro};
		this.consultarProcesoRevisadoPorLlaves(this._llavesProceso);
	}
	
	guardarObservacionNueva(observacion)
	{
		this.presentador.guardarObservacionNueva(observacion);
	}
	
	set guardando(guardando)
	{
		super.guardando = guardando;
		$("#guardarObservacionButton").attr("disabled",guardando);
		
	}
	
}

var vista = new RevisionProcesosVista(this);	
$(document).ready(function() 
{
	vista.inicializar();
});