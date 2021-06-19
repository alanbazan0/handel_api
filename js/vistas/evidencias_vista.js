class EvidenciasVista extends CatalogoVista
{		
	constructor()
	{	
		super();
		this.presentador = new EvidenciasPresentador(this);
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
		
		if($("#enviarMensajeLink").length>0)
			$("#enviarMensajeLink").click(this.enviarMensajeLinkClick)
		
		this.consultoGrid = false;
		this.consultarAnos();
		
		$("#estadoValidacionSelectCriterio").val(0)
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
			{longitud:30, 	titulo:"Justificada",   alias:"justificada", alineacion:"C", itemRenderer:this.renderJustificada},
			{longitud:100, 	titulo:"Evidencia",   alias:"nombreArchivo", alineacion:"C", itemRenderer:this.renderArchivo},
			{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"I" ,itemRenderer:this.renderFotoUsuario},
			{longitud:100, 	titulo:"Usuario",   alias:"usuarioNombreCompleto", alineacion:"I"},
			{longitud:100, 	titulo:"Comentarios",   alias:"comentarios", alineacion:"I", itemRenderer:this.renderComentarios},
			{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"I" ,itemRenderer:this.renderLogoEmpresa},
			{longitud:100, 	titulo:"Empresa",   alias:"empresaNombre", alineacion:"I"},
			//{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"I" ,itemRenderer:this.renderFotoAdministrador},
			//	{longitud:100, 	titulo:"Administrador responsable",   alias:"administradorNombreCompleto", alineacion:"I",itemRenderer:this.renderNombreAdministrador},
			{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"I" ,itemRenderer:this.renderFotoValidador},
			{longitud:100, 	titulo:"Usuario que validó",   alias:"validadorNombreCompleto", alineacion:"I",itemRenderer:this.renderNombreValidador},
			//{longitud:100, 	titulo:"Comentarios",   alias:"comentarios", alineacion:"I", itemRenderer:this.renderComentarios},
			//{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"I" },
			//{longitud:50, 	titulo:"Código",   	alias:"codigo", alineacion:"I" }
			
		];
		
		
//		this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-edit fa-lg'></span></button>"+
//									"<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";

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
		return "<div id='comentariosRecomendacionTabla"+renglon.id+"'>" + vista.getComentariosEvidencia(renglon) + "</div>";
	}
	
	
	renderArchivo(renglon, type, set)
	{   
		var contenido = "";
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
	    return contenido;
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
		//this.cargarOpciones('#administradorSelectCriterio', registros,"nombreCompleto");
		this.cargarOpciones('#administradorSelectCriterio', registros, null, null, null, null,  "nombreCompleto");
		$("#administradorSelectCriterio").val(this.usuario.id);
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
			validada : $('#estadoValidacionSelectCriterio').val(),
			justificada : $('#estadoJustificacionSelectCriterio').val()
			
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
		$(tbody).on("click", "span.archivo", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

			_this._evidenciaSeleccionada  = table.row( tr ).data();
			if (_this._evidenciaSeleccionada != undefined)
			{
				_this._llaves = _this.copiarPropiedadesObjeto(_this._evidenciaSeleccionada, ["id"]);
				if(_this.usuario.tipoUsuarioId==TipoUsuario.ADMINISTRADOR)
				{
					_this.mostrarFormularioValidacionEvidencia();
				}
				else if(_this.usuario.tipoUsuarioId==TipoUsuario.COORDINADOR || this.usuario.tipoUsuarioId==TipoUsuario.SUPERVISOR)
				{
					_this.modo = Modo.CONSULTA
					_this.mostrarFormularioEvidencia(_this._evidenciaSeleccionada);
				}
			
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
		$('#comentariosRecomendacionTabla'+this._modeloEvidencia.id).html(this.getComentariosEvidencia(this._modeloEvidencia));
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
		contenido = "<span style='cursor:pointer;margin-left:15px;width:50px;height:30px;color:gray;' data-toggle='tooltip' data-placemen='bottom' title='Comentarios' type='button' class='comentarios text-blue'><span  data-toggle='tooltip' class='fas fa-comments fa-lg'>"+comentarios+"</span>";;
	    return contenido;
	}
	
	
}

var vista = new EvidenciasVista(this);	
$(document).ready(function() 
{
	vista.inicializar();
});