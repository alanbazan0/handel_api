class SolicitudRevisionProcesosVista extends CatalogoVista
{		
	constructor()
	{	
		super();
		this.presentador = new SolicitudRevisionProcesosPresentador(this);
		var fecha = new Date();
		this._time = fecha.getTime();
	}
	
	
	get time()
	{
		return this._time;
	}

	
	inicializar()
	{
		this.crearTablas();
		$("body").data("_this",this);
		//this.crearTablas();
		

		var _this = this;
		$("#consultarButtonPendientes").click(function(){
			_this.consultarProcesosPendientes();
		});
		
		$("#consultarButton").click(function(){
			_this.consultarProcesosEnviados();
		});
		
		if($("#enviarMensajeLink").length>0)
			$("#enviarMensajeLink").click(this.enviarMensajeLinkClick)
		
		this._consultoPendientes = false;
		this._consultoEnviados = false;
		this.consultarAnos();
		
		
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    	  var target = $(e.target).attr("href") // activated tab
    	  switch(target)
    	  {
    	  	case "#pendientes":
    	  		if(!_this._consultoPendientes)
    	  			_this.consultar();	
    		break;
    	  	case "#enviados":
    	  		if(!_this._consultoEnviados)
    	  			_this.consultarProcesosEnviados();	
    		break;
			
    	  }
    	});
		//this.consultar();
		
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
	
	consultar()
	{
		//if(this.procedimientosPendientesTabla!=null)
		this.presentador.consultarProcesosPendientes();	
	}
	
	crearTablas()
	{
		if($("#procedimientosPendientesTabla").length!=0)
		{
			this.procedimientosPendientesTabla = new Tabla("procedimientosPendientesTabla");	
			this.procedimientosPendientesTabla.textoTablaVacia = "No hay procesos pendientes por revisar";
			this.procedimientosPendientesTabla.alto = 350;
			if(this.usuario.tipoUsuarioId == TipoUsuario.USUARIO)
			{
				this.procedimientosPendientesTabla.columnas = [
					{longitud:200, 	titulo:"Nombre",   	alias:"nombre", alineacion:"I" }, 
					//{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"I" },
					//{longitud:50, 	titulo:"Código",   	alias:"codigo", alineacion:"I" }
				]
			}
			else
			{
				this.procedimientosPendientesTabla.columnas = [
					{longitud:100, 	titulo:"Id",   	alias:"id", alineacion:"I" },
					{longitud:200, 	titulo:"Nombre",   	alias:"nombre", alineacion:"I" },
					{longitud:200, 	titulo:"Carpeta en cloud",   	alias:"rutaArchivo", alineacion:"I" },
					{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"D" ,itemRenderer:this.renderFotoUsuario},
					{longitud:100, 	titulo:"Usuario",   alias:"usuarioNombreCompleto", alineacion:"I"},
					//{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"I" },
					//{longitud:50, 	titulo:"Código",   	alias:"codigo", alineacion:"I" }
				]
			}
			
			procedimientosPendientesTabla.contenidoAdicional="";
			
//			if(this.usuario.tipoUsuarioId == TipoUsuario.USUARIO)
//				this.procedimientosPendientesTabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Subir'  type='button' class='subir btn-circle mr-0 botones-icon btn btn-sm float-right btn-info active'><span  data-toggle='tooltip' class='fa fa-upload fa-lg'></span></button>";
			//if(this.usuario.tipoUsuarioId == TipoUsuario.USUARIO || this.usuario.tipoUsuarioId == TipoUsuario.SUPERVISOR || this.usuario.tipoUsuarioId == TipoUsuario.COORDINADOR)
			//{
			this.procedimientosPendientesTabla.columnas.push({longitud:30, 	titulo:"",  alias:"", alineacion:"I" ,itemRenderer:this.renderSinCambios});
			this.procedimientosPendientesTabla.columnas.push({longitud:30, 	titulo:"",  alias:"", alineacion:"I" ,itemRenderer:this.renderObservacion});
			//}

			this.procedimientosPendientesTabla.registros = [];
		}
		
		if($("#procedimientosCumplidosTabla").length!=0)
		{
			this.procedimientosCumplidosTabla = new Tabla("procedimientosCumplidosTabla");	
			this.procedimientosCumplidosTabla.textoTablaVacia = "No hay procesos enviados a revision";
			this.procedimientosCumplidosTabla.alto = 350;
			
			this.procedimientosCumplidosTabla.columnas = [
			{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"I" },
			{longitud:300, 	titulo:"Nombre",   	alias:"nombre", alineacion:"I" },
			{longitud:200, 	titulo:"Fecha de envío",   	alias:"fecha", alineacion:"I" },
			{longitud:30, 	titulo:"Tipo solicitud",   alias:"estatusRevisionNombre", alineacion:"C", itemRenderer:this.renderEstatusRevision},
			{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"I" ,itemRenderer:this.renderFotoUsuario},
			{longitud:100, 	titulo:"Usuario",   alias:"usuarioNombreCompleto", alineacion:"I"},
			{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"I" ,itemRenderer:this.renderLogoEmpresa},
			{longitud:100, 	titulo:"Empresa",   alias:"empresaNombre", alineacion:"I"},
			{longitud:30, 	titulo:"Estado de solicitud",   alias:"estatusValidacionNombre", alineacion:"C", itemRenderer:this.renderEstatusValidacion},
			{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"I" ,itemRenderer:this.renderFotoValidador},
			{longitud:100, 	titulo:"Usuario que validó",   alias:"validadorNombreCompleto", alineacion:"I",itemRenderer:this.renderNombreValidador},
			{longitud:200, 	titulo:"Fecha de validación",   	alias:"fechaValidacion", alineacion:"I" },
			
			//{longitud:100, 	titulo:"Comentarios",   alias:"comentarios", alineacion:"I", itemRenderer:this.renderComentarios},
			//{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"I" },
			//{longitud:50, 	titulo:"Código",   	alias:"codigo", alineacion:"I" }
			
		];
		
		this.procedimientosCumplidosTabla.columnas.push({longitud:30, 	titulo:"",  alias:"", alineacion:"I" ,itemRenderer:this.renderObservacionEnviado});
	
		
//		this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-edit fa-lg'></span></button>"+
//									"<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";

//			if(this.usuario.tipoUsuarioId == TipoUsuario.USUARIO)
//				this.procedimientosCumplidosTabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-1 botones-icon btn btn-sm float-left btn-success active'><span  data-toggle='tooltip' class='fas fa-pencil-alt fa-lg'></span></button>";
		

			this.procedimientosCumplidosTabla.registros = [];
		}
		
		
		
	}
	
	renderSinCambios(renglon, type, set)
	{    
		var contenido = "";
		if(renglon.usuarioId == vista.usuario.id)
		{
			var fecha = new Date();
			if($("#anoSelectCriterioPendiente").val() ==fecha.getFullYear() )
				contenido += "<button data-toggle='tooltip' data-placemen='bottom' title='Revisé el procedimiento y no hubo cambios'  type='button' class='sinCambios btn-circle mr-0 botones-icon btn btn-sm float-right btn-success'><span  data-toggle='tooltip' class='fa fa-check fa-lg'></span></button>";
		}
	    return contenido;
	}
	
	renderObservacion(renglon, type, set)
	{    
		var contenido = "";
		if(renglon.usuarioId == vista.usuario.id)
		{
			var fecha = new Date();
			if($("#anoSelectCriterioPendiente").val() ==fecha.getFullYear() )
				contenido += "<button data-toggle='tooltip' data-placemen='bottom' title='Reportar una observación'  type='button' class='observaciones btn-circle mr-0 botones-icon btn btn-sm float-right btn-info active'><span  data-toggle='tooltip' class='fa fa-info-circle fa-lg'></span></button>";
		}
	    return contenido;
	}
	
	renderObservacionEnviado(renglon, type, set)
	{    
		var contenido = "";
		if(renglon.estatusRevisionId == EstatusRevision.OBSERVACIONES)
		{
			var fecha = new Date();
			if($("#anoSelectCriterio").val() ==fecha.getFullYear() )
				contenido += "<button data-toggle='tooltip' data-placemen='bottom' title='Observaciones'  type='button' class='observaciones btn-circle mr-0 botones-icon btn btn-sm float-right btn-info active'><span  data-toggle='tooltip' class='fa fa-info-circle fa-lg'></span></button>";
		}
	    return contenido;
	}
	
	
	crearColumnasGrid()
	{
		this.tabla.columnas = [
			{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"I" },
			{longitud:300, 	titulo:"Nombre",   	alias:"nombre", alineacion:"I" },
			//{longitud:200, 	titulo:"Código",   	alias:"codigo", alineacion:"I" },
			{longitud:200, 	titulo:"Carpeta en cloud",   	alias:"fecha", alineacion:"I" },
			{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"I" ,itemRenderer:this.renderFotoUsuario},
			{longitud:100, 	titulo:"Usuario",   alias:"usuarioNombreCompleto", alineacion:"I"},
			//{longitud:100, 	titulo:"Comentarios",   alias:"comentarios", alineacion:"I", itemRenderer:this.renderComentarios},
			
		];
		
		
		this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-edit fa-lg'></span></button>"+
									"<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";

		this.tabla.registros = [];
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
	
	renderNombreAdministrador(renglon, type, set)
	{    
		var fecha = new Date();
		var contenido = "";
		if(renglon.administradorId!=null)
			contenido = renglon.administradorNombreCompleto;
		else
			contenido ="<span class='label label-danger'>No asignado</span>";
	    return contenido;
	}
	
	renderFotoValidador(renglon, type, set)
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
	
	renderNombreValidador(renglon, type, set)
	{    
		var fecha = new Date();
		var contenido = "";
		if(renglon.validadorId!=null)
			contenido = renglon.validadorNombreCompleto;
		else
			contenido ="";
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
	
	renderJustificada(renglon, type, set)
	{    
		var contenido = "";
		if(renglon.justificacionId!=null)
			contenido += "<center><span data-toggle='tooltip' title='"+renglon.justificacionNombre+"' data-placemen='bottom' class='archivo fa fa-check fa-lg text-blue' style='cursor:pointer'>"+
			 			"</span></center>";
		else
			contenido += "";
	    return contenido;
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
		
		this.cargarOpciones('#anoSelectCriterioPendiente', anos);
		anos.unshift({id:"", nombre:"Todos los años"})
		$("#anoSelectCriterioPendiente").val(fecha.getFullYear());
		
		this.cargarOpciones('#anoSelectCriterio', anos);
		
		
		
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
		//this.cargarOpciones('#administradorSelectCriterio', registros,"nombreCompleto");
		this.cargarOpciones('#administradorSelectCriterio', registros, null, null, null, null,  "nombreCompleto");
		$("#administradorSelectCriterio").val(this.usuario.id);
		this.consultarEstatusValidacionProcesos();
		
	}

	consultarProcesosEnviados()
	{
		this._consultoEnviados = true;
		this.presentador.consultarProcesosEnviados();
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
			//empresaId:  $('#empresaSelectCriterio').val(),
			//sedeId:  $('#sedeSelectCriterio').val(),
			//departamentoId:  $('#departamentoSelectCriterio').val(),
			//mes:  $('#mesSelectCriterio').val(),
			ano: $('#anoSelectCriterioPendiente').val(),
			//administradorId : $('#administradorSelectCriterio').val(),
			//validada : $('#estadoValidacionSelectCriterio').val(),
			//justificada : $('#estadoJustificacionSelectCriterio').val()
			
		};
		
		
		
		return criteriosSeleccion;
	}
	
	get criteriosSeleccionEnviados()
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
	
	inicializarEventosBotonesTablaEnviados(tbody, table, nombresCamposLlave)
	{
		var _this = this;
		$(tbody).on("click", "button.observaciones", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

			_this._procesoSeleccionado  = table.row( tr ).data();
			
			
			if (_this._procesoSeleccionado != undefined)
			{
					_this._llavesProceso = _this.copiarPropiedadesObjeto(_this._procesoSeleccionado, ["id"]);
					_this.consultarProcesoRevisadoPorLlaves(_this._llavesProceso);
					//_this.mostrarFormularioObservaciones();
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
		//$('#cumplimientoRecomendacionTabla'+this._modeloEvidencia.id).html(this.getCumplimiento(this._modeloEvidencia));
		//$('#fotoUsuarioRecomendacionTabla'+this._modeloEvidencia.id).html(this.getFotoUsuario(this._modeloEvidencia));
		//$('#nombreUsuarioRecomendacionTabla'+this._modeloEvidencia.id).html(this.getTexto(this._modeloEvidencia.usuarioNombreCompleto));
		//$('#estatusValidacionRecomendacionTabla'+this._modeloEvidencia.id).html(this.getEstatusValidacionRecomendacion(this._modeloEvidencia));
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
			$("#validarRecomendacionButton").show();
		else
			$("#validarRecomendacionButton").hide();
		if(this._formularioRecomendacion)	
			this.consultarResponsablesRecomendacion();*/
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

	set modeloObservacion(observacion)
	{
		$('#seccionObservacionInput').val(observacion.seccion);
		$('#descripcionObservacionInput').val(observacion.descripcion);
		
		$("#headerBox").fadeIn();
		var html = `<div class="form-group">
						<div>
							<label class="control-label">Proceso</label>
							<span  class="" style='display:block;font-size:13px;'>`+ observacion.procesoNombre +`</span>
						</div>
					</div>
					<div class="form-group">
						<div>
							<label class="control-label">Observación</label>
							<span  class="" style='display:block;font-size:13px;'>`+ observacion.descripcion +`</span>
						</div>
					</div>
					`;
		
		$("#headerBox").html(html);
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
		$('#archivoEvidenciaTabla'+modelo.id).html(this.getArchivoEvidencia(modelo));
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
	
	showPDF(pdf_url) 
	{
		var _this = this;
		$("#pdf-loader").show();

		PDFJS.getDocument({ url: pdf_url }).then(function(pdf_doc) 
		{
			_this.__PDF_DOC = pdf_doc;
			_this.__TOTAL_PAGES = _this.__PDF_DOC.numPages;
			
			$("#pdf").show();
			
			// Hide the pdf loader and show pdf container in HTML
			$("#pdf-loader").hide();
			$("#pdf-contents").show();
			$("#pdf-total-pages").text(_this.__TOTAL_PAGES);

			// Show the first page
			_this.showPage(1);
		}).catch(function(error) {
			// If error re-show the upload button
			$("#pdf-loader").hide();
			$("#upload-button").show();
			
			$("#pdf").hide();
			
			 $('#evidenciaImage').show();
			 $('#evidenciaImage').attr('src',HANDEL_API + "/images/tipos_archivo/pdf.png");
			
			_this.mostrarMensajeError("Error",error.message);
		});;
	}
	
	set procesosPendientes(procedimientosPendientes)
	{
		if(this.procedimientosPendientesTabla!=null)
		{
			this.procedimientosPendientesTabla.registros = procedimientosPendientes;
			this.inicializarEventosBotonesTablaProcedimientosPendientes("#" + this.procedimientosPendientesTabla._id+"Table tbody",this.procedimientosPendientesTabla.datatable.DataTable(),["id"]);
		}
		//this.consultarEvidenciasCumplidas();
	}
	
	inicializarEventosBotonesTablaProcedimientosPendientes(tbody, table, nombresCamposLlave)
	{
		var _this = this;
		$(tbody).on("click", "button.observaciones", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

			_this._usuarioProcesoSeleccionado  = table.row( tr ).data();
			if (_this._usuarioProcesoSeleccionado != undefined)
			{
				_this.modo = Modo.ALTA;
				_this._usuarioProcesoSeleccionado.usuarioProcedimientoId = _this._usuarioProcesoSeleccionado.id;
				_this.mostrarObservaciones(_this._usuarioProcesoSeleccionado);
			
			}
		});
		
		$(tbody).on("click", "button.sinCambios", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

			this._usuarioProcesoSeleccionado  = table.row( tr ).data();
			if (this._usuarioProcesoSeleccionado != undefined)
			{
				//_this.modo = Modo.ALTA;
				//usuarioProceso.usuarioProcesoId = usuarioProceso.id;
				_this.reportarSinCambios(this._usuarioProcesoSeleccionado);
			
			}
		});
		
	}
	
	mostrarObservaciones(usuarioProceso)
	{
		var _this = this;
		//this.modoObserv = modo;
		this.mostrarFormularioHTML(HANDEL_API+"/html/modales/procesos_revisados_observaciones.php",this, null, function()
		{
			//this.inicializarValidacionesFormularioAvance();
			this.crearTablaObservaciones(true);
			$("#guardarObservacionesButton").fadeIn();
			$("#procesoLabel").html(usuarioProceso.nombre);
			//_this._archivosAvance = [];
			_this._observaciones = [];
			_this._archivosEliminados = [];
			
			$("#agregarObservacionButton").click(function(){_this.mostrarObservacion(Modo.ALTA,usuarioProceso);});
			
			
			
		},null,"observacionesModal","","guardarObservacionesButton",function()
		{
			//$("#formulario").submit();
			_this.guardarObservaciones(usuarioProceso, this._observaciones);
		});

	}
	
	guardarObservaciones(usuarioProceso, observaciones)
	{
		this.presentador.guardarObservaciones(usuarioProceso, observaciones);
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
		if(this.modo==Modo.CAMBIO)
		{
			//$("#observacionModal").modal("hide");
			this.guardarObservacionNueva(observacion);
		}
		else
		{
			if(this.modoObservacion==Modo.ALTA)
			{
				if(this._observaciones==null)
					this._observaciones =[];
					
				observacion.fechaAlta =  moment().format("DD/MM/YYYY hh:mm:ss");
				this._observaciones.push(observacion);
			}
			else
			{
				this._observacionSeleccionada.tipoObservacionId = observacion.tipoObservacionId;
				this._observacionSeleccionada.tipoObservacionNombre = observacion.tipoObservacionNombre;
				this._observacionSeleccionada.seccion = observacion.seccion;
				this._observacionSeleccionada.descripcion = observacion.descripcion;
			}
			$("#observacionModal").modal("hide");
			this.observacionesTabla.registros = this._observaciones;
			this.inicializarEventosBotonesTablaObservaciones("#" + this.observacionesTabla._id+"Table tbody",this.observacionesTabla.datatable.DataTable());
		}
	}
	
	guardarObservacionNueva(observacion)
	{
		this.presentador.guardarObservacionNueva(observacion);
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
				_this.mostrarObservacion(Modo.CAMBIO, _this._usuarioProcesoSeleccionado, _this._observacionSeleccionada)
			
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
				//_this.modo = Modo.ALTA;
				//usuarioProceso.usuarioProcesoId = usuarioProceso.id;
				_this.confirmarEliminarObservacion(this._observacionSeleccionada,tr,indice);
			
			}
		});
		
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
					_this._observaciones.slice(indice,1);
					
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
	
	consultarTiposObservacion()
	{
		this.presentador.consultarTiposObservacion();
	}
	
	adjuntarArchivo()
	{
		var _this = this;
		this._archivoActual = {id: ArrayUtils.getMax(this._archivos,"id")+1};
		if($("#file"+this._archivoActual.id).length==0)
			$("#archivosModal").append("<input type='file' id='file"+this._archivoActual.id+"' name='file"+this._archivoActual.id+"' style='display:none' />");
		
		$("#file"+this._archivoActual.id).on("change",{archivo:this._archivoActual},function(event)
		{
			 var reader = new FileReader();

            
				var archivo = event.data.archivo;
				var target = event.target || event.srcElement;
				if (target.value.length == 0) 
				{
					$("#file"+archivo.id).remove();
				}
				else
				{
					
					archivo.file = event.currentTarget.files[0];
					archivo.result = target.result;
					archivo.subido = false;
					archivo.nombre = archivo.file.name;
					archivo.tamano = archivo.file.size;
					_this.agregarArchivo(archivo);
					
					reader.onload = function (e)
            		{
						archivo.result = e.target.result;
					}
					reader.readAsDataURL(archivo.file);
				}
			
		});
   		$("#file"+_this._archivoActual.id).trigger("click");
		
	}
	
	
	reportarSinCambios(usuarioProceso)
	{
		//if(texto==undefined)
		//	texto ="Se eliminar\u00e1 este registro !!";
		var _this = this;
		swal({
	            title:"	",
	            text:  "Vamos a registrar el procedimiento <strong>"+usuarioProceso.nombre+"</strong> como revisado y sin cambios.",
	            html: true,
				type: "warning",
	            showCancelButton: true,
	            confirmButtonColor: "#DD6B55",
	            confirmButtonText: "Aceptar",
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
	            		 _this.presentador.reportarSinCambios(usuarioProceso);
	 	            }, 1000);
	            }
	        });	
	}
	
	eliminarProceso(usuarioProceso)
	{
		var row = $("#procedimientosPendientesTabla").find("tr[data-id="+usuarioProceso.id+"]");
		row.fadeOut();
		setTimeout(function()
		{
    		 row.remove();
         }, 1000);
	}
	
	
	mostrarFormularioEvidencia(procedimiento)
	{
		if($("#modalAlta").length ==0)
		{
			var url = HANDEL_API + "/html/formularios/evidencias.php";
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
				
//				$("#logoImage").attr("src",HANDEL_API + "/php/logos_empresas/default.png")
				
				$("#guardarButton").click(function () 
				{
					
					 $("#formulario").submit();
				});
				
				$("#borrarArchivoEvidenciaButton").click(function(){
					_this.borrarArchivoEvidencia();
				});
				
				
				$("#modalAlta").modal({backdrop: 'static', keyboard: false});
			});
		}
		else
		{
			$("#modalAlta").modal({backdrop: 'static', keyboard: false});
		}
	}
	
	crearTablaObservaciones(editar)
	{
		this.observacionesTabla = new Tabla("observacionesTabla");
		this.observacionesTabla.alto = 250;
		this.observacionesTabla.buscar = false;
		this.observacionesTabla.paginacion = false;
		this.observacionesTabla.columnas = [
			{longitud:100, 	titulo:"Tipo",   alias:"tipoObservacionNombre", alineacion:"I"},
			{longitud:200, 	titulo:"Sección",   alias:"seccion", alineacion:"I"},
			{longitud:200, 	titulo:"Fecha",   alias:"fechaAlta", alineacion:"I"},
			{longitud:300, 	titulo:"Descripción",   alias:"descripcion", alineacion:"I"},
			//{longitud:100, 	titulo:"",   alias:"tamano", alineacion:"C",itemRenderer:this.renderTamanoArchivo},
			//{longitud:100, 	titulo:"",   alias:"subido", alineacion:"C",itemRenderer:this.renderSubido}
		
		]
		if(this.modo == Modo.CAMBIO)
			this.observacionesTabla.columnas.push({longitud:30, 	titulo:"",   alias:"comentarios", alineacion:"I", itemRenderer:this.renderComentariosObservacion});	
	
		if(editar)
			this.observacionesTabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-1 botones-icon btn btn-sm float-left btn-success active'><span  data-toggle='tooltip' class='fas fa-pencil-alt fa-lg'></span></button>"+
													"<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";

	
		
		this.observacionesTabla.textoTablaVacia = "No hay observaciones";
		this.observacionesTabla.textoSinRegistros= "No hay observaciones";
		this.observacionesTabla.registros = [];
		
		
		
	}
	
	set tiposObservacion(registros)
	{		
		//this.cargarOpciones('#tipoObservacionSelect', registros);
		this.cargarOpciones('#tipoObservacionSelect', registros, this.modoObservacion, this._observacionSeleccionada, 'tipoObservacionId',"");
	}
	
	set procesosEnviados(procesosEnviados)
	{
		this.procedimientosCumplidosTabla.registros = procesosEnviados;
		this.inicializarEventosBotonesTablaEnviados("#" + this.procedimientosCumplidosTabla._id+"Table tbody",this.procedimientosCumplidosTabla.datatable.DataTable(),["id"]);

	}
	
	renderEstatusValidacion(renglon, type, set)
	{    
		return "<div id='estatusValidacionTabla"+renglon.id+"'>" + vista.getEstatusValidacion(renglon) + "</div>";
	}
	
	
	renderEstatusRevision(renglon, type, set)
	{    
		return "<div id='estatusRevisionTabla"+renglon.id+"'>" + vista.getEstatusRevision(renglon) + "</div>";
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
	
	consultarEstatusValidacionProcesos()
	{
		this.presentador.consultarEstatusValidacionProcesos();
	}	
	
	set estatusValidacionProcesos(registros)
	{
		ArrayUtils.removeWithValues("id",[EstatusValidacionProceso.PENDIENTE], registros);
		this.cargarOpciones('#estadoValidacionSelectCriterio', registros);
		//$("#estadoValidacionSelectCriterio").val(EstatusValidacionProceso.EN_PROCESO_DE_ANALISIS);
		if(!this._consultoPendientes)
		{
			this.consultarProcesosPendientes();
			//this.consultarProcesosEnviados();
			
		}
	}
	
	consultarProcesosPendientes()
	{
		this._consultoPendientes=true;
		this.consultar();	
	}
	
	mostrarFormularioObservaciones(proceso)
	{
		var _this = this;
		this.mostrarFormularioHTML(HANDEL_API+"/html/modales/procesos_revisados_observaciones.php",this, null, function()
		{
			//this.inicializarValidacionesFormularioValidacion();
			_this.modo = Modo.CAMBIO;
			$("#procesoLabel").html(proceso.nombre);
			this.crearTablaObservaciones(false);
			//this.crearEventosBotonesValidacion();
			//this.consultarProcesoRevisadoPorLlaves();
			
			$("#agregarObservacionButton").click(function(){_this.mostrarObservacion(Modo.ALTA,_this._procesoSeleccionado);});
			
		},null,"observacionesModal","","guardarObservacionesButton",function()
		{
			$("#formularioValidacion").submit();
		});
	}
	
	
	consultarProcesoRevisadoPorLlaves(llaves)
	{
		this.presentador.consultarProcesoRevisadoPorLlaves(llaves);
	}
	 
	
	
	set modeloProceso(modeloProceso)
	{
		this._modeloProceso = modeloProceso;
		$("#estatusValidacionIcono").attr("class","");
		$("#estatusValidacionIcono").addClass(this._modeloProceso.estatusValidacionIcono);
		$("#estatusValidacionIcono").addClass(this._modeloProceso.estatusValidacionColor);
		$("#estatusValidacionLabel").html(this._modeloProceso.estatusValidacionNombre);
		
		this._procesoSeleccionado.estatusValidacionIcono = this._modeloProceso.estatusValidacionIcono;
		this._procesoSeleccionado.estatusValidacionColor = this._modeloProceso.estatusValidacionColor;
		this._procesoSeleccionado.estatusValidacionDescripcion = this._modeloProceso.estatusValidacionDescripcion;
		
	}
	
	set observaciones(observaciones)
	{
		this.observacionesTabla.registros = observaciones;
			this.inicializarEventosBotonesTablaObservaciones("#" + this.observacionesTabla._id+"Table tbody",this.observacionesTabla.datatable.DataTable());
	
	}
	
	/*
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
	}*/
	
	get llavesProceso()
	{
		return this._llavesProceso;	
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
					_this.consultarObservacionPorLlaves();
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
}

var vista = new SolicitudRevisionProcesosVista(this);	
$(document).ready(function() 
{
	vista.inicializar();
});