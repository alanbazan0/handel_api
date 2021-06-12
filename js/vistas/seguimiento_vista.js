class SeguimientoVista extends CatalogoVista	
{		
	constructor(ventana)
	{	
		super(ventana);
		this.presentador = new SeguimientoPresentador(this);
		this.consultoGrid = false;
		this._time =  new Date().getTime();
	}
	
	inicializar()
	{
		super.inicializar();
		this.crearTablas();
		
		var _this = this;
		
		
		$('#consultarRecomendacionesButton').click(function(){_this.consultarRecomendaciones();});
		//this.consultarEmpresasCriterio();
		
		this.consultarEstatusValidacionCriterio();
	}
	
	crearTablas()
	{
		if($("#auditoriasTabla").length!=0)
		{
			this.auditoriasTabla = new Tabla("auditoriasTabla");
			this.auditoriasTabla.columnas = [
					{longitud:50, 	titulo:"",   	alias:"icono", alineacion:"D", itemRenderer:this.renderIcono},
					{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"D" },
					//{longitud:200, 	titulo:"Plantilla",   alias:"plantillaNombre", alineacion:"I" }, 
					//{longitud:200, 	titulo:"Seguimiento iniciado",   alias:"seguimiento", alineacion:"D", itemRenderer:this.renderSeguimiento},		
					{longitud:300, 	titulo:"Empresa",   alias:"empresaNombre", alineacion:"I" }, 
					{longitud:200, 	titulo:"Sede",   alias:"sedeNombre", alineacion:"I" }, 	
					{longitud:250, 	titulo:"Fecha de auditoría",   alias:"fecha", alineacion:"C",itemRenderer:this.renderFechaAuditoria },
					//{longitud:250, 	titulo:"Hora",   alias:"hora", alineacion:"C" },
					//{longitud:250, 	titulo:"Fecha de auditoria",   alias:"fechaEjecucion", alineacion:"I" },
					{longitud:50, 	titulo:"Puntuación",   alias:"puntuacion", alineacion:"C", itemRenderer: this.rendererPuntuacion },
					{longitud:50, 	titulo:"Total de acciones recomendadas",   	alias:"recomendacionesTotal", alineacion:"C" },
					{longitud:50, 	titulo:"Acciones pendientes",   alias:"recomendacionesPendientes", alineacion:"C" },
					{longitud:50, 	titulo:"Avance",   alias:"porcentajeAvance", alineacion:"C",itemRenderer: this.rendererAvance },
					//{longitud:50, 	titulo:"Número",   alias:"contadorEmpresa", alineacion:"C" },
					//{longitud:200, 	titulo:"Referencia",   alias:"referencia", alineacion:"I" },
					
					];
					
			if(this.usuario.tipoUsuarioId == TipoUsuario.ADMINISTRADOR || this.usuario.tipoUsuarioId == TipoUsuario.COORDINADOR || this.usuario.tipoUsuarioId == TipoUsuario.SUPERVISOR)
			{
				this.auditoriasTabla.columnas.push({longitud:30, 	titulo:"",  alias:"", alineacion:"C" ,itemRenderer: this.renderReporte});
				this.auditoriasTabla.columnas.push({longitud:30, 	titulo:"",  alias:"", alineacion:"C" ,itemRenderer:this.renderExportarActionTracker});
				this.auditoriasTabla.columnas.push({longitud:30, 	titulo:"",  alias:"", alineacion:"C" ,itemRenderer: this.renderReporteSeguimiento});
			}
			this.auditoriasTabla.columnas.push({longitud:30, 	titulo:"",  alias:"", alineacion:"C" ,itemRenderer:this.renderRecomendaciones});
		
		
			//this.auditoriasTabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Ver acciones recomendadas'  type='button' class='recomendaciones btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-list fa-lg'></span></button>";

			this.auditoriasTabla.registros = [];
		}
		
		if($("#recomendacionesTabla").length!=0)
		{
			this.recomendacionesTabla = new Tabla("recomendacionesTabla");
			
			this.recomendacionesTabla.columnas = [
				//º	{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"C" },
					{longitud:70, 	titulo:"Fecha de alta",   alias:"fechaAlta", alineacion:"I" }, 
					{longitud:300, 	titulo:"Acciones",   alias:"titulo", alineacion:"I" }, 	
					{longitud:110, 	titulo:"% Cumplimiento",   alias:"cumplimiento", alineacion:"C", itemRenderer: this.renderCumplimientoRecomendacion  }, 	
					{longitud:70, 	titulo:"Fecha compromiso",   alias:"fechaVencimiento", alineacion:"C" }, 	
					//{longitud:50, 	titulo:"Número",   alias:"contadorEmpresa", alineacion:"C" },
					//{longitud:200, 	titulo:"Referencia",   alias:"referencia", alineacion:"I" },
					
					];
					
			if(this.usuario.tipoUsuarioId == TipoUsuario.ADMINISTRADOR || this.usuario.tipoUsuarioId == TipoUsuario.COORDINADOR || this.usuario.tipoUsuarioId == TipoUsuario.SUPERVISOR)
			{
				//this.recomendacionesTabla.columnas.push({longitud:40, 	titulo:"",   	alias:"logo", alineacion:"D" ,itemRenderer:this.renderFotoUsuario});
				//this.recomendacionesTabla.columnas.push({longitud:120, 	titulo:"Responsable",   alias:"usuarioNombreCompleto", alineacion:"I", itemRenderer: this.renderNombreUsuario});
				this.recomendacionesTabla.columnas.push({longitud:40, 	titulo:"",   	alias:"logo", alineacion:"D" ,itemRenderer:this.renderFotoUsuarioRecomendacion});
				this.recomendacionesTabla.columnas.push({longitud:100, 	titulo:"Usuario",   alias:"usuarioNombreCompleto", alineacion:"I",itemRenderer:this.renderNombreUsuarioRecomendacion});

			}
			
			this.recomendacionesTabla.columnas.push({longitud:30, 	titulo:"",   alias:"terminada", alineacion:"I", itemRenderer: this.renderEstatusValidacionRecomendacion}),
			this.recomendacionesTabla.columnas.push({longitud:30, 	titulo:"",   alias:"comentarios", alineacion:"I", itemRenderer:this.renderComentariosRecomendacion}),		
			this.recomendacionesTabla.columnas.push({longitud:30, 	titulo:"",  alias:"", alineacion:"I" ,itemRenderer:this.renderAvance});
			
			if(this.usuario.tipoUsuarioId == TipoUsuario.ADMINISTRADOR || this.usuario.tipoUsuarioId == TipoUsuario.COORDINADOR || this.usuario.tipoUsuarioId == TipoUsuario.SUPERVISOR)
			{
				this.recomendacionesTabla.columnas.push({longitud:30, 	titulo:"",  alias:"", alineacion:"I" ,itemRenderer:this.renderEditarRecomendacion});
			}

			this.recomendacionesTabla.textoTablaVacia = "Cargando información";
			this.recomendacionesTabla.registros = [];
			this.recomendacionesTabla.textoTablaVacia = "No hay recomendaciones";
		}
	}
	
	renderEditarRecomendacion(renglon)
	{
		return "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-edit fa-lg'></span></button>";
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
	
	getComentariosRecomendacion(renglon)
	{    
		var contenido = "";
		var comentarios ="";
		if(renglon.numeroComentarios>0)
			comentarios = "<span class='label-warning notificacion'>"+renglon.numeroComentarios+"</span>";
		contenido = "<span style='cursor:pointer;margin-left:15px;width:50px;height:30px;color:gray;' data-toggle='tooltip' data-placemen='bottom' title='Comentarios' type='button' class='comentarios text-blue'><span  data-toggle='tooltip' class='fas fa-comments fa-lg'>"+comentarios+"</span>";;
	    return contenido;
	}
	
	getEstatusValidacionRecomendacion(renglon)
	{    
		var contenido = "";
		/*
		if(renglon.validada==1)
		{
			if(renglon.terminada==1)
				contenido += "<center><span data-toggle='tooltip' data-placemen='bottom' title='Validada' class='fas fa-check-double fa-lg text-blue' style='color:green;'></span></center>";
			else
				contenido += "";    
		}
		else if(renglon.terminada==1)
			contenido += "<center><span data-toggle='tooltip' data-placemen='bottom' title='Terminada'  class='fa fa-check fa-lg ' style='color:gray;'></span></center>";
		else
			contenido += "";  */
		if(renglon.estatusValidacionId!=0)
		{
			contenido += "<center><span data-toggle='tooltip' data-placemen='bottom' title='"+renglon.estatusValidacionDescripcion+"'  class='"+renglon.estatusValidacionIcono+" fa-lg "+renglon.estatusValidacionColor+"' ></span></center>";
		
		}
	
		
		
		return contenido;
	}
	
	renderReporte(renglon, type, set)
	{    
		var contenido = "<button data-toggle='tooltip' data-placemen='bottom' title='Reporte de auditoría'  type='button' class='reporte btn-circle mr-0 botones-icon btn btn-sm  btn-danger '><span  data-toggle='tooltip' class='fas fa-file-pdf fa-lg'></span></button>";
	    return contenido;
	}
	
	renderReporteSeguimiento(renglon, type, set)
	{    
		var contenido = "<button data-toggle='tooltip' data-placemen='bottom' title='Reporte de seguimiento'  type='button' class='reporteSeguimiento btn-circle mr-0 botones-icon btn btn-sm  text-white ' style='background-color:#3b6b2a'><span  data-toggle='tooltip' class='fas fa-file-pdf fa-lg'></span></button>";
	    return contenido;
	}
	
	renderExportarActionTracker(renglon, type, set)
	{
		var contenido = "<button data-toggle='tooltip' data-placemen='bottom' title='Action Tracker'  type='button' class='exportarActionTracker btn-circle mr-0 botones-icon btn btn-sm  btn-info '><span  data-toggle='tooltip' class='fa fa-file-excel fa-lg'></span></button>";
		return contenido;
	}
	
	
	renderRecomendaciones(renglon, type, set)
	{
		var contenido = "<button data-toggle='tooltip' data-placemen='bottom' title='Ver acciones recomendadas'  type='button' class='recomendaciones btn-circle mr-0 botones-icon btn btn-sm btn-primary '><span  data-toggle='tooltip' class='fa fa-list fa-lg'></span></button>";
		return contenido;
	}
	
	renderAvance(renglon, type, set)
	{    
		var contenido = "";
		if(renglon.usuarioId == vista.usuario.id)
		{
			var fecha = new Date();
			//if($("#mesSelectCriterio").val()==  fecha.getMonth() +1 && $("#anoSelectCriterio").val() ==fecha.getFullYear() )
			contenido += "<button data-toggle='tooltip' data-placemen='bottom' title='Avance'  type='button' class='avance btn-circle mr-0 botones-icon btn btn-sm btn-info'><span  data-toggle='tooltip' class='fa fa-flag-checkered fa-lg'></span></button>";
		}
		else
		{
			if(vista.usuario.tiopUsuarioId != TipoUsuario.ADMINISTRADOR)
				contenido += "<button data-toggle='tooltip' data-placemen='bottom' title='Avance'  type='button' class='avance btn-circle mr-0 botones-icon btn btn-sm btn-light'><span  data-toggle='tooltip' class='fas fa-eye fa-lg'></span></button>";

		}
	    return contenido;
	}
		
	renderFotoUsuario(renglon, type, set)
	{    
		var contenido = "";
		var foto ="";
		if(renglon.fotoPerfil.includes("default.jpg"))
			foto = renglon.fotoPerfil;
		else
			foto = renglon.fotoPerfil+"?"+vista.time;
		if(renglon.usuarioId!=null)
		{
			var url = HANDEL_API+ "/"+foto;
			contenido += "<center><img src='" + url + "' style='width:30px;height:30px;border-radius: 50%'></img></center>";
		}		
	    return contenido;
	}
	
	
	
	renderNombreUsuario(renglon, type, set)
	{    
		if(renglon.usuarioId!=null)
		{
			return renglon.usuarioNombreCompleto;
		}		
		else
		{
			return "<small class='labelAdvertencia'><i class='fas fa-exclamation-triangle''></i> Sin asignar</small>";	
		}
	}
	
	
	inicializarEventosBotonesTabla(tbody, table, nombresCamposLlave)
	{
		super.inicializarEventosBotonesTabla(tbody, table, nombresCamposLlave);
		var _this = this;
		$(tbody).on("click", "button.recomendaciones", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

			_this._registroSeleccionado  = table.row( tr ).data();
			if (_this._registroSeleccionado != undefined)
			{
				_this._llaves = _this.copiarPropiedadesObjeto(_this._registroSeleccionado, ["id"]);
				_this.mostrarRecomendaciones();
			}
		});
	}
	
	mostrarRecomendaciones()
	{
		this.modo = "";
		$('#auditoriasSection').hide();	
		$('#recomendacionesSection').fadeIn();	
		$('#consultarButton').hide();
		$('#consultarRecomendacionesButton').show();
		this.recomendaciones = [];
		this.presentador.consultarPorLlaves();
		if(this.usuario.tipoUsuarioId == TipoUsuario.ADMINISTRADOR)
			$("#estatusValidacionCriterioSelect").val(EstatusValidacion.ENVIADA_A_VALIDACION);
	
	}
	
	mostrarAuditorias()
	{
		this.modo = "";
		$('#recomendacionesSection').hide();	
		$('#auditoriasSection').fadeIn();	
		$("#encabezadoDiv").hide();
		$('#consultarButton').show();
		$('#consultarRecomendacionesButton').hide();
		$("#tituloAuditoriaDiv").hide();
		$("#titulo").show();
		this.consultar();

	}
	
	set recomendaciones(recomendaciones)
	{
		var _this = this;
		this.recomendacionesTabla.textoTablaVacia = "No hay recomendaciones";
		this.recomendacionesTabla.registros = recomendaciones;	
		this.inicializarEventosBotonesTablaRecomendaciones("#" + this.recomendacionesTabla._id+"Table tbody",this.recomendacionesTabla.datatable.DataTable());
		
		$("#recomendacionesTabla").find(".dt-buttons").html("<div id='regresarAuditorias'  class='ml-2'><label style='cursor:pointer;'><i id='tituloI' class='salir fa fa-arrow-left' ></i> Regresar a mis auditorías</label></div>");
		$("#regresarAuditorias").click(function(){_this.mostrarAuditorias();});
	}
	
	inicializarEventosBotonesTablaRecomendaciones(tbody, table)
	{
		var _this = this;
		$(tbody).on("click", "button.avance", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

			_this._recomendacionSeleccionada  = table.row( tr ).data();
			if (_this._recomendacionSeleccionada != undefined)
			{
				_this._llavesRecomendacion = _this.copiarPropiedadesObjeto(_this._recomendacionSeleccionada, ["id"]);
				//_this._llavesRecomendacion.recomendacionId = _this._llaves.id;
				_this.mostrarAvances();
			}
		});
		
		$(tbody).on("click", "span.comentarios", function()
			{			
				 var tr = $(this).closest('tr');
				    
			    if ( $(tr).hasClass('child') ) {
			      tr = $(tr).prev();  
			    }

				_this._recomendacionSeleccionada  = table.row( tr ).data();
				if (_this._recomendacionSeleccionada != undefined)
				{
					_this._llavesRecomendacion = _this.copiarPropiedadesObjeto(_this._recomendacionSeleccionada, ["id"]);
					_this.mostrarComentariosRecomendacion();

				}
			});
		
		
		$(tbody).on("click", "button.editar", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

			_this._recomendacionSeleccionada  = table.row( tr ).data();
			if (_this._recomendacionSeleccionada != undefined)
			{
				_this._llavesRecomendacion = _this.copiarPropiedadesObjeto(_this._recomendacionSeleccionada, ["id"]);
				_this.editarRecomendacion();

			}
		});
	}
	
	editarRecomendacion()
	{
		this.modoRecomendacion = Modo.CAMBIO;
		this.mostrarFormularioRecomendacion();
	}
	
	mostrarFormularioRecomendacion()
	{
		this.mostrarFormularioHTML(HANDEL_API+"/html/formularios/recomendacion.php",this, null, function()
		{
			if(this.modoRecomendacion==Modo.CAMBIO)
				this.consultarRecomendacionPorLlaves(true);
				
			this.inicializarValidacionesFormularioRecomendacion();
		},null,"recomendacionModal","","guardarButton", function()
		{
			$("#formulario").submit();
		},function()
		{
			//this.consultarRecomendacionPorLlaves();
		});
	}
	
	
	mostrarComentariosRecomendacion()
	{
		if($("#modalAlta").length ==0)
		{
			var url = HANDEL_API + "/html/modales/comentarios_sivah.php";
			this.mostrarIndicador();
			var _this = this;
			$.post(url,{}, function(html) 
			{
				_this.ocultarIndicador();
				$("body").append(html);
				$("#modalAlta").on("hidden.bs.modal", function () 
				{
					clearInterval(_this.cometariosRecomendacionIntervalId);
					//TODO: actualizar icono de comentarios y demas
					_this.consultarRecomendacionPorLlaves(false);
					$("#modalAlta").remove();
				});
				
				$("#modalAlta").on("show.bs.modal", function () 
				{
					//_this.inicializarValidacionesComentarioEvidencia();
					$("#accionAvanceLabel").html(_this._recomendacionSeleccionada.titulo);
					$("#enviarComentarioButton").click(function () 
					{
						var comentario = $("#comentarioEvidenciaInput").val().trim();
						if(comentario!="" && comentario!=undefined)
							_this.enviarComentarioRecomendacion();
					});
					$("#comentarioEvidenciaInput").keypress(function(event){
					    var keycode = (event.keyCode ? event.keyCode : event.which);
					    if(keycode == '13')
					    {
					    	var comentario = $("#comentarioEvidenciaInput").val().trim();
							if(comentario!="" && comentario!=undefined)
								_this.enviarComentarioRecomendacion();
					    }
					});
					_this._comentariosRecomendacion = [];
					_this.consultarComentariosRecomendacion();
					_this.cometariosRecomendacionIntervalId = setInterval(_this.consultarComentariosAutomaticamente, 60000);
						
				});
			
				$("#modalAlta").modal({backdrop: 'static', keyboard: false});
			});
			
			
		}
		else
		{
			$("#modalAlta").modal({backdrop: 'static', keyboard: false});
		}
	}
	
	set comentariosRecomendacion(comentariosRecomendacion)
	{
		if(comentariosRecomendacion.length> this._comentariosRecomendacion.length)
		{
			this._comentariosRecomendacion = comentariosRecomendacion;
			var fecha = new Date();
			var html="";
			for(var i=0; i< comentariosRecomendacion.length; i++)
			{
				var comentario = comentariosRecomendacion[i];
				
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
		_this.consultarComentariosRecomendacion();
	}
	
	enviarComentarioRecomendacion()
	{
		this.presentador.enviarComentarioRecomendacion();
		$("#comentarioEvidenciaInput").val("");
	}
	
	consultarComentariosRecomendacion()
	{
		this.presentador.consultarComentariosRecomendacion();
	}
	
	get modeloCometarioRecomendacion()
	{
		var modelo =
		{
			recomendacionId: this._recomendacionSeleccionada.id,
			usuarioId: this.usuario.id,
			comentario: $("#comentarioEvidenciaInput").val()
		};
		return modelo;
	}
	
	eliminarAvance()
	{ 
		var _this = this;
		swal({
	            title: "\u00bfEst\u00E1 seguro de eliminar?",
	            text: "Se eliminar\u00e1 este avance !!",
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
	            	 setTimeout(function(){
	            		 _this.presentador.eliminarAvance();
	 	            }, 1000);
	            }
	        });
	}
	
	
	
	
	inicializarEventosBotonesTablaAvances(tbody, table)
	{
		var _this = this;
		$(tbody).on("click", "button.editar", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

			_this._avanceSeleccionado  = table.row( tr ).data();
			if (_this._avanceSeleccionado != undefined)
			{
				_this._llavesAvance= _this.copiarPropiedadesObjeto(_this._avanceSeleccionado, ["id"]);
				_this._llavesAvance.recomendacionId = _this._llavesRecomendacion.id;
				_this.mostrarFormularioAvance(Modo.CAMBIO);
			}
		});
		
		$(tbody).on("click", "button.eliminar", function()
		{
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

		    _this._avanceSeleccionado  = table.row( tr ).data();
			if (_this._avanceSeleccionado != undefined)
			{
				_this._llavesAvance = _this.copiarPropiedadesObjeto(_this._avanceSeleccionado, ["id"]);
				_this._llavesAvance.recomendacionId = _this._llavesRecomendacion.id;
				//_this._llavesRecomendacion.recomendacionId = _this._llaves.id;
				_this.eliminarAvance();
			}
		});
		
		$(tbody).on("click", "i.archivos", function()
		{
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

		    _this._avanceSeleccionado  = table.row( tr ).data();
			if (_this._avanceSeleccionado != undefined)
			{
				_this._llavesAvance = _this.copiarPropiedadesObjeto(_this._avanceSeleccionado, ["id"]);
				_this._llavesAvance.recomendacionId = _this._llavesRecomendacion.id;
				//_this._llavesRecomendacion.recomendacionId = _this._llaves.id;
				_this.mostrarFormularioAvance(Modo.CONSULTA);
			}
		});
		
	}
	
	
	
	

	renderIcono(renglon, type, set)
	{    
		var fecha = new Date();
		var icono = HANDEL_API + "/php/iconos_plantillas/" + renglon.icono+"?"+fecha.getTime();
		var contenido = "";
		contenido += "<center><img src='" + icono + "' style='width:30px;height:30px;'></img></center>";
	    return contenido;
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
	}
	
	rendererAvance(renglon, type, set)
	{    
		//if(renglon.fechaUltimaCapacitacion!=null)
		//{
			if(renglon.porcentajeAvance==undefined)
				renglon.porcentajeAvance = 0;
		
			var porcentajeCumplimiento = parseFloat(renglon.porcentajeAvance);
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
	}
	
	rendererCumplimiento(renglon, type, set)
	{    
		//if(renglon.fechaUltimaCapacitacion!=null)
		//{
			if(renglon.cumplimiento==undefined)
				renglon.cumplimiento = 0;
		
			var porcentajeCumplimiento = parseFloat(renglon.cumplimiento);
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
	}
	
	renderCumplimientoAvance(renglon, type, set)
	{  
		return "<div id='cumplimientoAvanceTabla"+renglon.id+"'>" +vista.getCumplimiento(renglon) + "</div>";
	}
	
	renderCumplimientoRecomendacion(renglon, type, set)
	{  
		return "<div id='cumplimientoRecomendacionTabla"+renglon.id+"'>" +vista.getCumplimiento(renglon) + "</div>";
	}
	
	renderFechaModificacionAvance(renglon, type, set)
	{  
		return "<div id='fechaModificacionAvanceTabla"+renglon.id+"'>"+vista.getTexto(renglon.fechaModificacion)+"</div>";
	}
	
	renderComentarioAvance(renglon, type, set)
	{  
		return "<div id='comentarioAvanceTabla"+renglon.id+"'>" + vista.getTexto(renglon.comentario)
	}
	
	renderArchivosAvance(renglon, type, set)
	{  
		return "<div id='archivosAvanceTabla"+renglon.id+"'>" + vista.getArchivosAvance(renglon) + "</div>";
	}
	
	renderEstatusValidacionRecomendacion(renglon, type, set)
	{  
		return "<div id='estatusValidacionRecomendacionTabla"+renglon.id+"'>" + vista.getEstatusValidacionRecomendacion(renglon) + "</div>";
	}
	
	renderComentariosRecomendacion(renglon, type, set)
	{  
		return "<div id='comentariosRecomendacionTabla"+renglon.id+"'>" + vista.getComentariosRecomendacion(renglon) + "</div>";
	}
	
	renderFotoUsuarioAvance(renglon, type, set)
	{    
		return "<div id=fotoUsuarioAvanceTabla"+renglon.id+">" + vista.getFotoUsuario(renglon) + "</div>";
	}
	
	renderFotoUsuarioRecomendacion(renglon, type, set)
	{    
		return "<div id=fotoUsuarioRecomendacionTabla"+renglon.id+">" + vista.getFotoUsuario(renglon) + "</div>";
	}
	
	renderNombreUsuarioAvance(renglon, type, set)
	{  
		return "<div id='nombreUsuarioAvanceTabla"+renglon.id+"'>"+vista.getTexto(renglon.usuarioNombreCompleto)+"</div>";
	}
	
	renderNombreUsuarioRecomendacion(renglon, type, set)
	{  
		return "<div id='nombreUsuarioRecomendacionTabla"+renglon.id+"'>"+vista.getTexto(renglon.usuarioNombreCompleto)+"</div>";
	}
	
	getTexto(texto)
	{
		return "<span>"+texto+"</span>";
	}
	
	
	
	getArchivosAvance(renglon)
	{
		var contenido = "";
		var tieneArchivos = renglon.archivos>0?true:false;
		if(tieneArchivos)
		{
			contenido+= "<i  class='archivos fa fa-lg fa-paperclip' style='cursor:pointer'></i>";
			contenido+="<span  class='labelArchivo'>"+renglon.archivos+"</span>";
		}
	    return contenido;
	}
	
	getFotoUsuario(renglon)
	{
		var contenido = "";
		var foto ="";
		if(renglon.fotoPerfil.includes("default.jpg"))
			foto = renglon.fotoPerfil;
		else
			foto = renglon.fotoPerfil+"?"+vista.time;
		if(renglon.usuarioId!=null)
		{
			var url = HANDEL_API+ "/"+foto;
			contenido += "<center><img  src='" + url + "' style='width:30px;height:30px;border-radius: 50%'></img></center>";
		}		
	    return contenido;
	}
	

	
	
	getCumplimiento(renglon)
	{
		if(renglon.cumplimiento==undefined)
			renglon.cumplimiento = 0;
	
		var porcentajeCumplimiento = parseFloat(renglon.cumplimiento);
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
		return "<span style='font-weight:bold;color:"+color+";' >"+porcentajeCumplimiento+"%</span>";
	}
	
	crearColumnasGrid()
	{
		
	}
	
	inicializarValidacionesFormulario()
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
            	 "empresaSelect": {required: !0},
                "sedeSelectInput": {required: !0},
                "tipoAreaSelect": {required: !0},
                "nombreInput": {required: !0}
               
            },
            messages: {
            	 "empresaSelect": "Por favor ingrese una empresa",
            	 "sedeSelect": "Por favor ingrese una sede",
            	 "tipoAreaSelect": "Por favor ingrese un tipo de area",
                "nombreInput": "Por favor ingrese un nombre"
                	
                
            },
            submitHandler:function (form) {
            	 _this.guardar();
            }
        });
	}
	
	inicializarValidacionesFormularioRecomendacion()
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
            	 "responsableSelect": {required: !0},
               
            },
            messages: {
            	 "responsableSelect": "Por favor ingrese un responsable",
                	
                
            },
            submitHandler:function (form) {
            	 _this.guardarRecomendacion();
            }
        });
	}
	
	guardarRecomendacion()
	{
		this.presentador.actualizarRecomendacion();
	}
	
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
	
	get criteriosSeleccionRecomendaciones()
	{
		 var criteriosSeleccion = 
		 {				    
			estatusValidacionId: $('#estatusValidacionCriterioSelect').val()
		 }
		 return criteriosSeleccion;
	}		
	
	set modelo(valor)
	{		
		this.modeloEdicion = valor;
		this.fechaAuditoria = this.modeloEdicion.fecha.substring(0,10);
		this.porcentajeCumplimiento = this.modeloEdicion.puntuacion;
		$('#plantillaNombreSpan').html("Evaluación a " +this.modeloEdicion.empresaNombre+ " Sede "+ this.modeloEdicion.sedeNombre);
		$("#encabezadoDiv").fadeIn();
		$("#tituloAuditoriaDiv").show();
		$("#titulo").hide();
		
		this.consultarRecomendaciones();
	}
	
	set fechaAuditoria(fechaAuditoria)
	{
		var html = "<span  style='font-weight:bold;black' >"+fechaAuditoria+"</span>";
		$("#fechaAuditoriaSpan").html(html);
	}
	
	set porcentajeCumplimiento(porcentajeCumplimiento)
	{
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
		var html = "<span  style='font-weight:bold;color:"+color+";' >"+porcentajeCumplimiento+"%</span>";
		$("#porcentajeSpan").html(html);
	}
	
	consultarRecomendaciones()
	{
		this.recomendacionesTabla.textoTablaVacia = "Cargando información";
		this.presentador.consultarRecomendaciones();
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
	 
	datosValidos()
	{
		var nombre = $("#nombreInput"),
	        empresa = $("#empresaSelect");
        
        var allFields = $( [] ).add(nombre).add(empresa);
        var tips = $( ".validateTips" );
		tips.text("");
		
		var valid = true;
		allFields.removeClass("ui-state-error");
		
	    valid = valid && this.validaciones.checkValue( nombre, "nombre", tips );
	    valid = valid && this.validaciones.checkValue( empresa, "empresa",tips );
	    
		return valid;
	}	

	limpiarFormulario()
	{
		$('#nombreInput').val("");
		this.cargandoOpciones('#empresaSelect');
	}
	
	consultarEmpresas()
	{
		this.cargandoOpciones("#empresaSelect");
		this.cargandoOpciones("#sedeSelect");
		this.presentador.consultarEmpresas();
	}
	
	consultarTiposArea()
	{
		this.cargandoOpciones("#tipoAreaSelect");
		this.presentador.consultarTiposArea();
	}

	set empresas(registros)
	{		
		this.cargarOpciones('#empresaSelect', registros, this.modo, this.modeloEdicion, 'empresaId',"");
		if(this.modo==Modo.ALTA)
			$("#empresaSelect").val($("#empresaSelectCriterio").val());
	}
	
	set tiposArea(registros)
	{		
		this.cargarOpciones('#tipoAreaSelect', registros, this.modo, this.modeloEdicion, 'tipoAreaId',"");
	}
	
	consultarEmpresasCriterio()
	{
		this.cargandoOpciones("#empresaSelectCriterio");
		this.cargandoOpciones("#sedeSelectCriterio");
		this.presentador.consultarEmpresasCriterio();
	}
	
	set empresasCriterio(registros)
	{		
		this.cargarOpciones('#empresaSelectCriterio', registros);
		//this.consultar();
	}
	
	cambiarEmpresaCriterio()
	{
		this.consultarSedesCriterio();
	}
	
	cambiarEmpresa()
	{
		this.cargandoOpciones("#sedeSelect");
		this.consultarSedes();
	}
	
	consultarSedes()
	{
		this.presentador.consultarSedes();
	}
	
	set sedes(registros)
	{
		this.cargarOpciones('#sedeSelect', registros, this.modo, this.modeloEdicion, 'sedeId',"");
		if(this.modo==Modo.ALTA)
			$("#sedeSelect").val($("#sedeSelectCriterio").val());
	}
	
	consultarSedesCriterio()
	{
		this.cargandoOpciones("#sedeSelectCriterio");
		this.presentador.consultarSedesCriterio();
	}
	
	set sedesCriterio(registros)
	{		
		this.cargarOpciones('#sedeSelectCriterio', registros);
		if(this.consultoGrid==false)
		{
			this.consultar();
			this.consultoGrid=true;
		}
	}
	
	set auditorias(auditorias)
	{
		this.auditoriasTabla.alto =  $("body").height() - 230;
		this.auditoriasTabla.registros = auditorias;	
		this.inicializarEventosTabla("#" + this.auditoriasTabla._id+"Table tbody",this.auditoriasTabla.datatable.DataTable());
		
		$("#auditoriasTabla").find(".dt-buttons").html("<label class='ml-2'>Mis auditorías</label>");
		
	}
	
	inicializarEventosTabla(tbody, table)
	{
		this.inicializarEventosBotonesTabla(tbody, table, ["id"]);
		var _this = this;
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
		
		$(tbody).on("click", "button.reporteSeguimiento", function()
		{			
			var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

			_this._registroSeleccionado  = table.row( tr ).data();
			if (_this._registroSeleccionado != undefined)
			{
				_this._llaves = _this.copiarPropiedadesObjeto(_this._registroSeleccionado, ["id"]);
				_this.imprimirReporteSeguimiento();
			}
		});
	}
	
	imprimirReporte()
	{
		var submitForm = this.getNewSubmitForm(HANDEL_API+"/php/reportes/reporte_auditoria.php");
		this.createNewFormElement(submitForm, "auditoriaId", JSON.stringify(this._llaves.id));	 
	    submitForm.target= "_blank";
	    submitForm.submit();
	}
	
	imprimirReporteSeguimiento()
	{
		var _this = this;
		var texto = "<p><strong>SIVAH va a generar el reporte de</strong></p>"+
					"<br><p>"+this._registroSeleccionado.empresaNombre+"</p>"+
					"<p>"+this._registroSeleccionado.sedeNombre+"</p>"+
					"<p>De la auditoría del "+this._registroSeleccionado.fecha.substring(0,10)+"</p>"+
					"<br><p>Este proceso demora unos segundos por la cantidad de consultas que se realizan a la base de datos.</p>"+
					"<br><p>En caso de que exista algún detalle en su reporte por favor espere unos minutos y vuelva a intentar generarlo.</p>";
		swal({
	            title: "",
	            text: texto,
				html: true,
	            type: "warning",
	            showCancelButton: true,
	            confirmButtonColor: "#3c8dbc",
	            confirmButtonText: "Aceptar",
				cancelButtonColor: "#DD6B55",
	            cancelButtonText: "Cancelar",
	            closeOnConfirm: true,
	            closeOnCancel: true,
	            showLoaderOnConfirm: true,
	        },
	        function(isConfirm)
	        {
	            if (isConfirm) 
	            {
	            	var submitForm = _this.getNewSubmitForm(HANDEL_API+"/php/reportes/reporte_seguimiento.php");
					_this.createNewFormElement(submitForm, "auditoriaId", JSON.stringify(_this._llaves.id));	 
				    submitForm.target= "_blank";
				    submitForm.submit();
	            }
	        });
		
	}
	
	exportarActionTracker()
	{
		var submitForm = this.getNewSubmitForm(HANDEL_API+"/php/excel/action_tracker.php");
		this.createNewFormElement(submitForm, "auditoriaId", JSON.stringify(this._llaves.id));	 
	    submitForm.target= "_blank";
	    submitForm.submit();
	}
	
	mostrarAvances()
	{
		var _this = this;
		this.mostrarFormularioHTML(HANDEL_API+"/html/modales/recomendaciones_avances.php",this, null, function()
		{
			$("#accionAvanceLabel").html(_this._recomendacionSeleccionada.titulo);
			$("#estatusValidacionIcono").addClass(_this._recomendacionSeleccionada.estatusValidacionIcono);
			$("#estatusValidacionIcono").addClass(_this._recomendacionSeleccionada.estatusValidacionColor);
			$("#estatusValidacionLabel").html(_this._recomendacionSeleccionada.estatusValidacionDescripcion);
			if(_this._recomendacionSeleccionada.estatusValidacionId!=EstatusValidacion.VALIDADA)
			{
				$("#registrarAvanceButton").show();
				$("#registrarAvanceButton").click(function(){_this.mostrarFormularioAvance(Modo.ALTA);});
				if(_this.usuario.tipoUsuarioId==TipoUsuario.ADMINISTRADOR && _this._recomendacionSeleccionada.cumplimiento==100)
				{
					$("#validarRecomendacionButton").show();
					$("#validarRecomendacionButton").click(function(){_this.mostrarFormularioValidacion();});
					
				}
			}
			this.crearTablaAvances();
			this.consultarAvances();
			
		},null,"avancesModal","","guardarAvanceButton", function()
		{
			
		},function()
		{
			_this.consultarRecomendacionPorLlaves(false);
		});

	}
	
	mostrarFormularioValidacion(estatus)
	{
		var _this = this;
		this.mostrarFormularioHTML(HANDEL_API+"/html/modales/validacion_recomendacion.php",this, null, function()
		{
			this.inicializarValidacionesFormularioValidacion();
			$("#accionAvanceValidacionLabel").html(_this._recomendacionSeleccionada.titulo);
			this.consultarRecomendacionValidacionPorLlaves();
			
		},null,"validacionModal","","guardarValidacionButton", function()
		{
			$("#formularioValidacion").submit();
		});
	}
	
	mostrarFormularioValidacionAlta(estatus)
	{
		var _this = this;
		this.mostrarFormularioHTML(HANDEL_API+"/html/modales/validacion_recomendacion.php",this, null, function()
		{
			this.inicializarValidacionesFormularioValidacion();
			$("#accionAvanceValidacionLabel").html(_this._recomendacionSeleccionada.titulo);
			
			//$("#estatusValidacionSelect").val(estatus);
			//this.consultarRecomendacionValidacionPorLlaves();
			this.estatusValidacionIdAlta = estatus;
			this._modeloRecomendacionValidacion={id :this._recomendacionSeleccionada.id};
			this.presentador.consultarEstatusValidacionAlta();
			$("#comentariosValidacionInput").focus();
			
		},null,"validacionModal","","guardarValidacionButton", function()
		{
			$("#formularioValidacion").submit();
		});
	}
	
	consultarEstatusValidacion()
	{
		this.cargandoOpciones("#estatusValidacionSelect");
		this.presentador.consultarEstatusValidacion();
	}
	
	consultarEstatusValidacionCriterio()
	{
		this.cargandoOpciones("#estatusValidacionCriterioSelect");
		this.presentador.consultarEstatusValidacionCriterio();
	}
	
	
	
	consultarEstatusValidacionAlta()
	{
		this.cargandoOpciones("#estatusValidacionSelect");
		this.presentador.consultarEstatusValidacionAlta();
	}
	
	set estatusValidacion(estatusValidacion)
	{
		this.cargarOpciones('#estatusValidacionSelect', estatusValidacion, Modo.CAMBIO, this._modeloRecomendacionValidacion, 'estatusValidacionId',"");
	}
	
	set estatusValidacionAlta(estatusValidacion)
	{
		this.cargarOpciones('#estatusValidacionSelect', estatusValidacion, Modo.CAMBIO, {estatusValidacionId: this.estatusValidacionIdAlta}, 'estatusValidacionId',"");
	}
	
	set estatusValidacionCriterio(estatusValidacion)
	{
		this.cargarOpciones('#estatusValidacionCriterioSelect', estatusValidacion);
		//this.cargarOpciones('#estatusValidacionCriterioSelect', estatusValidacion, "", null, 'estatusValidacionId',"");
	}
	
	validarRecomendacion()
	{
		this.presentador.validarRecomendacion();
	}
	
	consultarRecomendacionValidacionPorLlaves()
	{
		this.presentador.consultarRecomendacionValidacionPorLlaves();
	}
	
	consultarRecomendacionPorLlaves(formulario)
	{
		this._formularioRecomendacion = formulario;
		this.presentador.consultarRecomendacionPorLlaves();
	}
	
	set modeloRecomendacionValidacion(modeloRecomendacionValidacion)
	{
		this._modeloRecomendacionValidacion = modeloRecomendacionValidacion;
		$("#comentariosValidacionInput").val(this._modeloRecomendacionValidacion.comentariosValidacion);
		this.consultarEstatusValidacion();
	}
	
	get modeloRecomendacionValidacion()
	{
		 var modelo = 
		 {		
			 id:  this._modeloRecomendacionValidacion.id,
			 comentariosValidacion:$('#comentariosValidacionInput').val(),
			 estatusValidacionId:$('#estatusValidacionSelect').val(),
			
		 };
		 return modelo;
	 }
	
	mostrarFormularioAvance(modo)
	{
		var _this = this;
		this.modoAvance = modo;
		this.mostrarFormularioHTML(HANDEL_API+"/html/modales/recomendaciones_avances_archivos.php",this, null, function()
		{
			this.inicializarValidacionesFormularioAvance();
			this.crearTablaArchivos();
			switch(modo)
			{
				case Modo.ALTA:
					$("#guardarAvanceButton").show();
					$("#comentariosGroup").show();
					$("#cumplimientoGroup").show();
					$("#adjuntarArchivoButton").show();
				break;
				case Modo.CAMBIO:
					$("#guardarAvanceButton").show();
					$("#comentariosGroup").show();
					$("#cumplimientoGroup").show();
					$("#adjuntarArchivoButton").show();
					this.consultarAvancePorLlaves();
				break;
				case Modo.CONSULTA:
					this.consultarAvancePorLlaves();
				break;
			}
			
			$("#accionLabel").html(_this._recomendacionSeleccionada.titulo);
			//_this._archivosAvance = [];
			_this.archivos = [];
			_this._archivosEliminados = [];
			
			$("#adjuntarArchivoButton").click(function(){_this.adjuntarArchivo();});
			
			if(this.usuario.tipoUsuarioId==TipoUsuario.ADMINISTRADOR && this._recomendacionSeleccionada.cumplimiento==100 && _this._recomendacionSeleccionada.estatusValidacionId!=EstatusValidacion.VALIDADA)
			{
				$("#validarRecomendacionArchivosButton").show();
				$("#validarRecomendacionArchivosButton").click(function(){_this.mostrarFormularioValidacionAlta(EstatusValidacion.VALIDADA);});
				$("#rechazarRecomendacionArchivosButton").show();
				$("#rechazarRecomendacionArchivosButton").click(function(){_this.mostrarFormularioValidacionAlta(EstatusValidacion.RECHAZADA);});
			}
			
		},null,"archivosModal","","guardarAvanceButton",function()
		{
			$("#formulario").submit();
		});

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
	
	
	
	agregarArchivo(archivo)
	{
		var archivos = this.archivosTabla.registros;
		archivos.push(archivo);
		this.archivosTabla.registros = archivos;
		this.inicializarEventosBotonesTablaArchivos("#" + this.archivosTabla._id+"Table tbody",this.archivosTabla.datatable.DataTable());
	}
	
	guardarAvance()
	{
		this.presentador.guardarAvance();
	}
	
	
	consutarArchivos()
	{
		
		this.presentador.consultarArchivos();
	}
	
	actualizarAvance()
	{
		this.presentador.actualizarAvance();
	}
	
	insertarAvance()
	{
		this.presentador.insertarAvance();	
	}
	
	set modeloAvance(modeloAvance)
	{
		this._modeloAvance = modeloAvance;
		$('#cumplimientoSelect').val(this._modeloAvance.cumplimiento);
		$('#comentarioInput').val(this._modeloAvance.comentario);
		
		$('#cumplimientoAvanceTabla'+this._modeloAvance.id).html(this.getCumplimiento(this._modeloAvance));
		$('#comentarioAvanceTabla'+this._modeloAvance.id).html(this.getTexto(this._modeloAvance.comentario));
		$('#fechaModificacionAvanceTabla'+this._modeloAvance.id).html(this.getTexto(this._modeloAvance.fechaModificacion));
		$('#archivosAvanceTabla'+this._modeloAvance.id).html(this.getArchivosAvance(this._modeloAvance));
		$('#fotoUsuarioAvanceTabla'+this._modeloAvance.id).html(this.getFotoUsuario(this._modeloAvance));
		$('#nombreUsuarioAvanceTabla'+this._modeloAvance.id).html(this.getTexto(this._modeloAvance.usuarioNombreCompleto));
		
		
		this.consutarArchivos();
		
	}
	
	set modeloRecomendacion(modeloRecomendacion)
	{
		this._modeloRecomendacion = modeloRecomendacion;
		$('#cumplimientoRecomendacionTabla'+this._modeloRecomendacion.id).html(this.getCumplimiento(this._modeloRecomendacion));
		$('#fotoUsuarioRecomendacionTabla'+this._modeloRecomendacion.id).html(this.getFotoUsuario(this._modeloRecomendacion));
		$('#nombreUsuarioRecomendacionTabla'+this._modeloRecomendacion.id).html(this.getTexto(this._modeloRecomendacion.usuarioNombreCompleto));
		$('#estatusValidacionRecomendacionTabla'+this._modeloRecomendacion.id).html(this.getEstatusValidacionRecomendacion(this._modeloRecomendacion));
		$('#comentariosRecomendacionTabla'+this._modeloRecomendacion.id).html(this.getComentariosRecomendacion(this._modeloRecomendacion));
		$("#estatusValidacionIcono").attr("class","");
		$("#estatusValidacionIcono").addClass(this._modeloRecomendacion.estatusValidacionIcono);
		$("#estatusValidacionIcono").addClass(this._modeloRecomendacion.estatusValidacionColor);
		$("#estatusValidacionLabel").html(this._modeloRecomendacion.estatusValidacionDescripcion);
		
		this._recomendacionSeleccionada.cumplimiento = modeloRecomendacion.cumplimiento;
		this._recomendacionSeleccionada.estatusValidacionIcono = modeloRecomendacion.estatusValidacionIcono;
		this._recomendacionSeleccionada.estatusValidacionColor = modeloRecomendacion.estatusValidacionColor;
		this._recomendacionSeleccionada.estatusValidacionDescripcion = modeloRecomendacion.estatusValidacionDescripcion;
		this._recomendacionSeleccionada.usuarioId = modeloRecomendacion.usuarioId;
		this._recomendacionSeleccionada.usuarioNombre = modeloRecomendacion.usuarioNombre;
		this._recomendacionSeleccionada.usuarioApellido = modeloRecomendacion.usuarioApellido;
		this._recomendacionSeleccionada.usuarioNombreCompleto = modeloRecomendacion.usuarioNombreCompleto;
		
		if(this.usuario.tipoUsuarioId==TipoUsuario.ADMINISTRADOR && this._recomendacionSeleccionada.cumplimiento==100)
			$("#validarRecomendacionButton").show();
		else
			$("#validarRecomendacionButton").hide();
		if(this._formularioRecomendacion)	
			this.consultarResponsablesRecomendacion();
	}
	
	consultarResponsablesRecomendacion()
	{
		this.presentador.consultarResponsablesRecomendacion();
	}
	
	set responsablesRecomendacion(registros)
	{
		this.cargarOpciones('#responsableSelect', registros, this.modoRecomendacion, this._modeloRecomendacion, 'usuarioId',"","nombreCompleto");
		
	}
	
	get modeloAvance()
	{
		var modelo = 
		 {		
			 cumplimiento:$('#cumplimientoSelect').val(),
			 comentario:$('#comentarioInput').val(),
			 archivosEliminados : this._archivosEliminados.join(","),
			
		 };
		 if((this.modoAvance ==Modo.CAMBIO || this.modoAvance ==Modo.CONSULTA) && this._modeloAvance!=null)
			 modelo.id = this._modeloAvance.id;
		 return modelo;
	}
	
	get archivos()
	{
		var files = [];
		for(var i=0; i < this._archivos.length; i++)
		{
			var archivo = this._archivos[i];
			if(archivo.file!=null)
				files.push(archivo.file);
		}
		
		return files;
	}
	
	consultarAvancePorLlaves()
	{
		this.presentador.consultarAvancePorLlaves(this.llavesAvance.id);
	}
	
	crearTablaAvances()
	{
		this.avancesTabla = new Tabla("avancesTabla");
		this.avancesTabla.buscar = false;
		this.avancesTabla.paginacion = true;
		this.avancesTabla.alto = 250;
		this.avancesTabla.columnas = [];
		
		this.avancesTabla.columnas.push({longitud:50, 	titulo:"Fecha de alta",   alias:"fechaAlta", alineacion:"C"});
		this.avancesTabla.columnas.push({longitud:110, 	titulo:"% Cumplimiento",   alias:"cumplimiento", alineacion:"C", itemRenderer: this.renderCumplimientoAvance});
		this.avancesTabla.columnas.push({longitud:50, 	titulo:"Evidencias",   alias:"nombreArchivo", alineacion:"C", itemRenderer:this.renderArchivosAvance});
		this.avancesTabla.columnas.push({longitud:300, 	titulo:"Comentario",   alias:"comentario", alineacion:"I",itemRenderer:this.renderComentarioAvance});
		this.avancesTabla.columnas.push({longitud:100, 	titulo:"Fecha de ultima modificación",   alias:"fechaModificacion", alineacion:"I",itemRenderer:this.renderFechaModificacionAvance } );
		
		//if(this.usuario.tipoUsuarioId == TipoUsuario.ADMINISTRADOR || this.usuario.tipoUsuarioId == TipoUsuario.COORDINADOR || this.usuario.tipoUsuarioId == TipoUsuario.SUPERVISOR)
		//{
			this.avancesTabla.columnas.push({longitud:40, 	titulo:"",   	alias:"logo", alineacion:"D" ,itemRenderer:this.renderFotoUsuarioAvance});
			this.avancesTabla.columnas.push({longitud:100, 	titulo:"Usuario",   alias:"usuarioNombreCompleto", alineacion:"I",itemRenderer:this.renderNombreUsuarioAvance});

		//}
		/*this.avancesTabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-edit fa-lg'></span></button>"+
												"<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";

*/
		this.avancesTabla.columnas.push({longitud:30, 	titulo:"",  alias:"", alineacion:"C" ,itemRenderer:this.renderEditarAvance});
		this.avancesTabla.columnas.push({longitud:30, 	titulo:"",  alias:"", alineacion:"C" ,itemRenderer:this.renderEliminarAvance});

	
		this.avancesTabla.textoTablaVacia = "";
		this.avancesTabla.registros = [];
		
		
		
	}
	
	renderEditarAvance(row)
	{
		if(vista._recomendacionSeleccionada!=null && vista._recomendacionSeleccionada.estatusValidacionId!=EstatusValidacion.VALIDADA)
			return "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-edit fa-lg'></span></button>";
		else
			return "";
	}	
	
	renderEliminarAvance(row)
	{
		if(vista._recomendacionSeleccionada!=null && vista._recomendacionSeleccionada.estatusValidacionId!=EstatusValidacion.VALIDADA)
			return "<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";
		else
			return "";
	}
	
	inicializarValidacionesFormularioAvance()
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
            	 "comentarioInput": {required: true, minlength: 25},
               
            },
            messages: {
            	 "comentarioInput": "Por favor ingrese un comentario (25 caracteres mínimo)",
                
                	
                
            },
            submitHandler:function (form) {
            	 _this.guardarAvance();
            }
        });
	}
	
	inicializarValidacionesFormularioValidacion()
	{
		var _this = this;
		jQuery("#formularioValidacion").validate({
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
            	 "comentariosValidacionInput": {required: !0},
               
            },
            messages: {
            	 "comentariosValidacionInput": "Por favor ingrese un comentario",
                
                	
                
            },
            submitHandler:function (form) {
            	 _this.validarRecomendacion();
            }
        });
	}
	
	crearTablaArchivos()
	{
		this.archivosTabla = new Tabla("archivosTabla");
		this.archivosTabla.alto = 300;
		this.archivosTabla.buscar = false;
		this.archivosTabla.paginacion = false;
		this.archivosTabla.columnas = [
			{longitud:50, 	titulo:"",   alias:"nombre", alineacion:"C", itemRenderer:this.renderArchivo},
			{longitud:100, 	titulo:"",   alias:"nombre", alineacion:"I"},
			{longitud:100, 	titulo:"",   alias:"tamano", alineacion:"C",itemRenderer:this.renderTamanoArchivo},
			{longitud:100, 	titulo:"",   alias:"subido", alineacion:"C",itemRenderer:this.renderSubido}
		
		]
		if(this.modoAvance != Modo.CONSULTA)
			this.archivosTabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";

	
		
		this.archivosTabla.textoTablaVacia = "No hay archivos adjuntos";
		this.archivosTabla.textoSinRegistros= "No hay archivos adjuntos";
		this.archivosTabla.registros = [];
		
		
		
	}
	
	renderClipAvance(renglon, type, set)
	{   
		
	}
	
	renderTamanoArchivo(renglon, type, set)
	{
		return vista.getTamano(renglon.tamano);
	}
	
	renderSubido(renglon, type, set)
	{
		if(renglon.subido)
			return "<i class='fas fa-cloud-upload-alt text-secondary' data-toggle='tooltip' data-placemen='bottom' title='Almacenado'></i>";
		else
			return "<i class='fas fa-upload text-secondary' data-toggle='tooltip' data-placemen='bottom' title='Pendiente de almacenar' ></i>";
	}
	
	addCommas(nStr) { 
	    nStr += ''; 
	    var x = nStr.split('.'); 
	    var x1 = x[0]; 
	    var x2 = x.length > 1 ? '.' + x[1] : ''; 
	    var rgx = /(\d+)(\d{3})/; 
	    while (rgx.test(x1)) { 
	     x1 = x1.replace(rgx, '$1' + ',' + '$2'); 
	    } 
	    return x1 + x2; 
	} 
	
	getTamano(longitud)
	{
		if(longitud!="" && longitud!=undefined)
		{
			var tamano="";
			if(longitud<1048576)
			{
				var kb= longitud / 1024;
				kb = kb.toFixed(2);
				//tamano =  this.addCommas(kb) + " KB (" + longitud  +" bytes)";
				tamano =  this.addCommas(kb) + " KB";
			}
			else
			{
				var mb = longitud / 1048576;
				mb = mb.toFixed(2);
				//tamano =  this.addCommas(mb) + " MB (" + longitud  +" bytes)";
				tamano =  this.addCommas(mb) + " MB";
			}
			return tamano;
		}
		return "Tama\u00f1o desconocido";
	}
	
	renderArchivo(renglon, type, set)
	{   
		var contenido = "";
		var comentarios ="";
		var iconoColor = vista.getIconoArchivo(renglon.nombre);
		if(renglon.validada==1)
			comentarios = "<span class='label-success' style='position: relative;top: 6px;right: 4px;font-size: 10px;padding: 2px 3px;line-height: .9;'><i class='fas fa-check-double'></i></span>";
		contenido = "<span data-toggle='tooltip' data-placemen='bottom' title='Ver archivo' style='cursor:pointer;margin-left:15px;width:50px;height:30px'  type='button' class='archivo'><span  data-toggle='tooltip' class='"+iconoColor.icono+" fa-lg "+iconoColor.color+"'>"+comentarios+"</span>";
							
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

	consultar()
	{
		if($("#auditoriasTabla").length!=0)
			this.presentador.consultarAuditorias();
	}

	consultarAvances()
	{
		this.presentador.consultarAvancesRecomendacion();
	}
	
	
	consultarArchivos()
	{
		this.presentador.consultarArchivosAvance();
	}
	
	set avances(avances)
	{
		this.avancesTabla.textoTablaVacia = "No hay avances registrados en esta recomendación";
		this.avancesTabla.registros = avances;
		this.inicializarEventosBotonesTablaAvances("#" + this.avancesTabla._id+"Table tbody",this.avancesTabla.datatable.DataTable());
	}
	
	get llavesRecomendacion()
	{
		return this._llavesRecomendacion;
	}
	
	set archivos(archivos)
	{
		this._archivos = archivos;
		//this.archivosTabla.textoTablaVacia = "No hay archivos asociados a este registro de avance";
		for(var i=0; i < archivos.length; i++)
			archivos[i].subido = true;
		this.archivosTabla.registros = archivos;
		this.inicializarEventosBotonesTablaArchivos("#" + this.archivosTabla._id+"Table tbody",this.archivosTabla.datatable.DataTable());
	}
	
	get llavesAvance()
	{
		return this._llavesAvance;
	}
	
	
	
	inicializarEventosBotonesTablaArchivos(tbody, table)
	{
		var _this = this;
		$(tbody).on("click", "button.eliminar", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

			_this._archivoSeleccionado  = table.row( tr ).data();
			if (_this._archivoSeleccionado != undefined)
			{
				//_this._llavesArchivo= _this.copiarPropiedadesObjeto(_this._archivoSeleccionado, ["id"]);
				if(_this._archivoSeleccionado.subido)
					_this._archivosEliminados.push(_this._archivoSeleccionado.id);
				
				var row = $("#archivosTabla").find("tr[data-id="+_this._archivoSeleccionado.id+"]");
				row.fadeOut();
				setTimeout(function()
				{
					$("#file" + _this._archivoSeleccionado.id).remove();
            		 row.remove();
					ArrayUtils.removeWithValues("id",[_this._archivoSeleccionado.id],_this._archivos);
	 	         }, 1000);
				
			}
		});
		
		
		$(tbody).on("click", "span.archivo", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }
			
			_this._indiceArchivoSeleccionado = table.row( tr ).index(); 

			_this._archivoSeleccionado  = table.row( tr ).data();
			if (_this._archivoSeleccionado != undefined)
			{
				
				_this.mostrarFormularioHTML(HANDEL_API+"/html/modales/ver_archivo.php",_this, null, function()
				{
					
					_this.vistaPreviaArchivo(_this._archivoSeleccionado);
					
				},null,"archivoModal","","", function()
				{
					
				},function()
				{
					
				});
				
			}
		});
		
		
	}
	
	set guardando(guardando)
	{
		super.guardando = guardando;
		//$("#guardarAvanceButton").attr("disabled",guardando);
		//$("#guardarAvanceButton").attr("disabled",guardando);
		$(".button").attr("disabled",guardando);
	}
	
	salirModalArchivos()
	{
		$('#archivosModal').modal('hide')
	}
	
	salirModalRecomendacion()
	{
		$('#recomendacionModal').modal('hide')
	}
	
	salirFormularioValidacion()
	{
		$('#validacionModal').modal('hide')
	}
	
	salirFormularioAvances()
	{
		$('#avancesModal').modal('hide')
	}
	
	salirFormularioArchivos()
	{
		$('#archivosModal').modal('hide')
	}
	
	get time()
	{
		return this._time;
	}
	
	set progresoArchivos(progresoArchivos)
	{
		if(this._archivos.length>0)
		{
			if(progresoArchivos == 100)
				$("#archivosProgress").fadeOut();
			else
				$("#archivosProgress").show();
				
			$('#archivosProgressBar').css('width', progresoArchivos+'%').attr('aria-valuenow', progresoArchivos).html(progresoArchivos+"%");
		}
	}
	
	vistaPreviaArchivo(archivoSeleccionado)
	{
		 $("#pdf").hide();
		var _this = this;
		$("#descargarButton").click(function()
			{
			//var archivo = "evidencia"+_this.modeloEdicion.id+"_" +encodeURIComponent(_this.modeloEdicion.nombreArchivo);
			var archivo = encodeURIComponent(archivoSeleccionado.nombre);
			var url = HANDEL_API + "/php/archivos_avances/avance" + _this._avanceSeleccionado.id+"/"+archivo;
			var submitForm = _this.getNewSubmitForm(url);
		    submitForm.target= "_blank";
		    submitForm.submit();
		});
		if(archivoSeleccionado.nombre=="")
			$('#evidenciaImage').attr("src",HANDEL_API + "/images/tipos_archivo/vacio.png");
		else
		{
			try
			{
				var elementos = archivoSeleccionado.nombre.split(".");
				if(elementos.length>1)
				{
					var tipo= elementos[elementos.length-1];
					switch(tipo)
					{
						case "doc":
						case "docx":
							$("#evidenciaImage").css({'width': '50%'});
							 $('#evidenciaImage').attr('src',HANDEL_API + "/images/tipos_archivo/word.png");
						break;
						case "xls":
						case "xlsx":
							$("#evidenciaImage").css({'width': '50%'});
							 $('#evidenciaImage').attr('src',HANDEL_API + "/images/tipos_archivo/excel.png");
						break;
						case "ppt":
						case "pptx":
							$("#evidenciaImage").css({'width': '50%'});
							 $('#evidenciaImage').attr('src',HANDEL_API + "/images/tipos_archivo/power_point.png");
						break;
						case "pdf":
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
						
						
							 $("#pdf").show();
							 $('#evidenciaImage').attr('src',HANDEL_API + "/images/tipos_archivo/pdf.png");
							 
							 $('#evidenciaImage').hide(); 
							 if(archivoSeleccionado.subido)
							 {
								var archivo = encodeURIComponent(archivoSeleccionado.nombre);
								var url = HANDEL_API + "/php/archivos_avances/avance" + this._avanceSeleccionado.id+"/"+ archivo;
								this.showPDF(url);
							 }
							 else
							 {
								if(archivoSeleccionado.file.type=="application/pdf")
                			  		this.showPDF(URL.createObjectURL(archivoSeleccionado.file));
							 }
							
							 
						break;
	//					case "txt":
	//						 $('#evidenciaImage').attr('src',HANDEL_API + "/images/tipos_archivo/txt.png");
	//					break;
						default:
							$("#evidenciaImage").css({'width': '50%'});
							$('#evidenciaImage').attr('src',HANDEL_API + "/images/tipos_archivo/archivo.png");
						break;
						case "jpg":
						case "png":
						case "bmp":
							$("#evidenciaImage").css({'width': '100%'});
							if(archivoSeleccionado.subido)
							{
								var archivo = encodeURIComponent(archivoSeleccionado.nombre);
								var url = HANDEL_API + "/php/archivos_avances/avance" + this._avanceSeleccionado.id+"/"+ archivo;
								$('#evidenciaImage').attr('src',url);
							}
							else
							{
								$('#evidenciaImage').attr('src',archivoSeleccionado.result);
							}
						break;
						
						
					}
				}
				else
				{
					$('#evidenciaImage').attr('src',HANDEL_API + "/images/tipos_archivo/archivo.png");
				}
			}
			catch(e)
			{
				 $('#evidenciaImage').attr('src',HANDEL_API + "/images/tipos_archivo/archivo.png");
			}
			
			
		}
		
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
	
	get modeloRecomendacion()
	{
		var modelo = 
		 {		
			 responsableId:$('#responsableSelect').val(),
			
		 };
		 if((this.modoRecomendacion ==Modo.CAMBIO || this.modoRecomendacion ==Modo.CONSULTA) && this.modoRecomendacion!=null)
			 modelo.id = this._modeloRecomendacion.id;
		 return modelo;
	}
}
var vista = new SeguimientoVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});
