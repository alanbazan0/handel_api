class ManualSeguridadVista extends CatalogoVista
{		
	constructor(ventana)
	{	
		super(ventana);
		this.presentador = new ManualSeguridadPresentador(this);
		this._urlFormulario = "html/formularios/usuarios_procesos.php";
		this.formatosTabla = new Tabla("formatosTabla");	
	}
	
	inicializar()
	{
		super.inicializar(false);
		
		
		
		
		if(this.usuario.tipoUsuarioId == TipoUsuario.ADMINISTRADOR)
		{
			$("#criteriosSeleccionDiv").show();
			this.consultarEmpresasCriterio();
		}
		else
			this.consultar();
	}
	
	
	
	crearColumnasGrid()
	{
		
		
		if(this.usuario.tipoUsuarioId == TipoUsuario.ADMINISTRADOR)
		{
			
			this.tabla.columnas = [
				//{longitud:70, 	titulo:"Id",   alias:"id", alineacion:"D", class: "desc" }, 
				{longitud:70, 	titulo:"Id proceso",   alias:"procedimientoId", alineacion:"D", class: "desc" }, 
				{longitud:500, 	titulo:"Proceso",   alias:"nombre", alineacion:"I", itemRenderer: this.renderNombre}, 
				{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"I" ,itemRenderer:this.renderLogoEmpresa},
				{longitud:200, 	titulo:"Empresa",   alias:"empresaNombre", alineacion:"I" },		
				{longitud:200, 	titulo:"Sede",   alias:"sedeNombre", alineacion:"I" },		
				{longitud:200, 	titulo:"Sección en manual",alias:"rutaArchivo", alineacion:"I"},
				{longitud:200, 	titulo:"Archivo",alias:"nombreArchivo", alineacion:"I", itemRenderer:this.renderArchivos},	
				{longitud:100, 	titulo:"Fecha de alta",   alias:"fechaAlta", alineacion:"I"  },		
				{longitud:100, 	titulo:"Fecha de última modificación",   alias:"fechaModificacion", alineacion:"I" },
			//	{longitud:100, 	titulo:"Estatus",   alias:"estatus", alineacion:"D", itemRenderer:this.renderEstatus},
				//{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"I" ,itemRenderer:this.renderFotoUsuario},
				{longitud:250, 	titulo:"Usuarios",   alias:"usuariosNombres", alineacion:"I"}
			];
			
			this.formatosTabla.columnas = [
				//{longitud:70, 	titulo:"Id",   alias:"id", alineacion:"D", class: "desc" }, 
				{longitud:70, 	titulo:"Id formato",   alias:"formatoId", alineacion:"D", class: "desc" }, 
				{longitud:500, 	titulo:"Formato",   alias:"nombre", alineacion:"I", itemRenderer: this.renderFormatoNombre}, 
				{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"I" ,itemRenderer:this.renderLogoEmpresa},
				{longitud:200, 	titulo:"Empresa",   alias:"empresaNombre", alineacion:"I" },		
				{longitud:200, 	titulo:"Sede",   alias:"sedeNombre", alineacion:"I" },		
				{longitud:200, 	titulo:"Sección en manual",alias:"rutaArchivo", alineacion:"I"},
				{longitud:200, 	titulo:"Archivo",alias:"nombreArchivo", alineacion:"I", itemRenderer:this.renderArchivos},	
				{longitud:100, 	titulo:"Fecha de alta",   alias:"fechaAlta", alineacion:"I"  },		
				{longitud:100, 	titulo:"Fecha de última modificación",   alias:"fechaModificacion", alineacion:"I" },
				//{longitud:100, 	titulo:"Estatus",   alias:"estatus", alineacion:"D", itemRenderer:this.renderEstatus}
				{longitud:100, 	titulo:"Usuarios",   alias:"usuariosNombres", alineacion:"I" }
				
			];
			
		}
		else
		{
			this.tabla.columnas = [
				{longitud:70, 	titulo:"Id",   alias:"id", alineacion:"D", class: "desc" }, 
				{longitud:70, 	titulo:"Id proceso",   alias:"procedimientoId", alineacion:"D", class: "desc" }, 
				{longitud:500, 	titulo:"Proceso",   alias:"nombre", alineacion:"I", itemRenderer: this.renderNombre}, 
				{longitud:200, 	titulo:"Sección en manual",alias:"rutaArchivo", alineacion:"I"},
				{longitud:100, 	titulo:"Fecha de alta",   alias:"fechaAlta", alineacion:"I"  },		
				{longitud:100, 	titulo:"Fecha de última modificación",   alias:"fechaModificacion", alineacion:"I" },
				{longitud:100, 	titulo:"Estatus",   alias:"estatus", alineacion:"D", itemRenderer:this.renderEstatus}
				
			];
			
			this.tabla.columnas.push({longitud:30, 	titulo:"",  alias:"", alineacion:"I" ,itemRenderer:this.renderSinCambios});
			this.tabla.columnas.push({longitud:30, 	titulo:"",  alias:"", alineacion:"I" ,itemRenderer:this.renderObservacion});
			
			this.formatosTabla.columnas = [
				{longitud:70, 	titulo:"Id",   alias:"id", alineacion:"D", class: "desc" }, 
				{longitud:70, 	titulo:"Id formato",   alias:"formatoId", alineacion:"D", class: "desc" }, 
				{longitud:500, 	titulo:"Formato",   alias:"nombre", alineacion:"I", itemRenderer: this.renderFormatoNombre}, 
				{longitud:200, 	titulo:"Sección en manual",alias:"rutaArchivo", alineacion:"I"},
				{longitud:100, 	titulo:"Fecha de alta",   alias:"fechaAlta", alineacion:"I"  },		
				{longitud:100, 	titulo:"Fecha de última modificación",   alias:"fechaModificacion", alineacion:"I" },
				{longitud:100, 	titulo:"Estatus",   alias:"estatus", alineacion:"D", itemRenderer:this.renderEstatus}
				
			];
			
		}
		
		this.habilitarExportacionExcel(this.tabla, this, this.renderExcelTabla,[0,1,3,4,5,7,8],"Manual de seguridad - Procesos");
		
		this.habilitarExportacionExcel(this.formatosTabla, this, this.renderExcelTabla,[0,1,3,4,5,7,8],"Manual de seguridad - Formatos");

		
		this.tabla.registros = [];	
		this.formatosTabla.registros = [];	
	}
	
	renderExcelTabla(tabla, data, row, column, node )
	{
		if(column==ArrayUtils.indexWithValues("alias",["estatus"],tabla.columnas))
    	{
		  if(data.includes("fa-check"))
    		   return "Activo";
    	   else
    		   return "Inactivo";
    	}
    	else if(column==ArrayUtils.indexWithValues("alias",["nombre"],tabla.columnas))
    	{
			if(data.includes("div"))
			{
				var nombre = $(data).attr("data-nombre");
			 	 return nombre;
		 	}
		 	else
		 		return data;
    	}
    	
    	else if(node.innerHTML.includes("button"))
    		return "";
		else if(node.innerHTML.includes("<label>"))
			return "";
    	return data;
	}
	
	renderArchivos(renglon, type, set)
	{  
		return "<div id='archivo"+renglon.id+"'>" + vista.getArchivos(renglon) + "</div>";
	}
	
	renderUsuarios(renglon, type, set)
	{
		return renglon.usuariosIds;
	}
	
	getArchivos(renglon)
	{
		var contenido = "";
		var tieneArchivos = renglon.archivo!=""?true:false;
		if(tieneArchivos)
		{
			contenido = this.getContenidoArchivo(renglon.archivo);
		
		}
	    return contenido;
	}
	
	getContenidoArchivo(nombreArchivo)
	{
		var contenido = "";
		contenido = "<div class='archivo' data-toggle='tooltip' data-placemen='bottom' title='"+nombreArchivo+"'>";
		contenido+= "<i  class='archivos fa fa-lg fa-paperclip' style='cursor:pointer'></i>";
		contenido+="<span  class='labelArchivo'>1</span>";
		contenido+="</div>";
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
	
	
	renderNombre(renglon, type, set)
	{    
		var contenido = "";
		if(renglon.archivo != "" && renglon.archivo != null)
		{
			contenido = "<div class='archivo' data-nombre='"+renglon.nombre+"'><a href='#' onclick='event.preventDefault();'>"+renglon.nombre+"</a></div>";
		}
		else
			contenido = renglon.nombre;
	    return contenido;
	}
	
	renderFormatoNombre(renglon, type, set)
	{    
		var contenido = "";
		if(renglon.archivo != "" && renglon.archivo != null)
		{
			contenido = "<div class='archivo' data-nombre='"+renglon.nombre+"'><a href='#' onclick='event.preventDefault();'>"+renglon.nombre+"</a></div>";
		}
		else
			contenido = renglon.nombre;
	    return contenido;
	}
	
	renderSinCambios(renglon, type, set)
	{    
		var contenido = "";
		if(renglon.usuarioId == vista.usuario.id)
		{
			var fecha = new Date();
			//if($("#anoSelectCriterioPendiente").val() ==fecha.getFullYear() )
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
			//if($("#anoSelectCriterioPendiente").val() ==fecha.getFullYear() )
				contenido += "<button data-toggle='tooltip' data-placemen='bottom' title='Reportar una observación'  type='button' class='observaciones btn-circle mr-0 botones-icon btn btn-sm float-right btn-info active'><span  data-toggle='tooltip' class='fa fa-info-circle fa-lg'></span></button>";
		}
	    return contenido;
	}
	
	renderFotoPerfil(renglon, type, set)
	{    
		var fecha = new Date();
		var contenido = "";
		var icono = HANDEL_API+ "/"+renglon.fotoPerfil+"?"+vista.time;
		contenido += "<center><img src='" + icono + "' style='width:30px;height:30px;'></img></center>";
	    return contenido;
	}
	
	
	
	cancelar()
	{ 
		var _this = this;
		swal({
	            title: "¿\u00bfEst\u00E1 seguro de cancelar?",
	            text: "¡¡Se cancelar\u00e1 este procedimiento !!",
	            type: "warning",
	            showCancelButton: true,
	            confirmButtonColor: "#DD6B55",
	            confirmButtonText: "Si, cancelar!!",
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
	            		 _this.presentador.cancelar();
	 	            }, 1000);
	            }
	        });
	}
	
	renderLimiteJustificaciones(renglon, type, set)
	{    
		var contenido = "";
		if(renglon.limitarJustificaciones==1)
		{
			if(renglon.limiteJustificaciones==0)
				contenido +="No se puede justificar";
			else
				contenido += renglon.limiteJustificaciones;
		}
		else
			contenido += "ilimitadas";
	    return contenido;
	}
	
	renderFechaCancelacion(renglon, type, set)
	{    
		var contenido = "";
		if(renglon.estatus==1)
			contenido += "<label>-</label>";
		else
			contenido += renglon.fechaCancelacion;
	    return contenido;
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
            	 "empresaIdSelect": {
                     required: !0
                 },
                "codigoInput": {
                    required: !0
                },
                "sedeIdSelectUsuario": {
                    required: !0
                },
                "sedeIdSelectProcedimiento": {
                    required: !0
                },
                "nombreInput": {
                    required: !0
                },
               
            },
            messages: {
            	 "empresaIdSelect": "Por favor ingrese una empresa",
                "codigoInput": "Por favor ingrese un c\u00f3digo",
                "sedeIdSelect": "Por favor ingrese una sede",
                "nombreInput": "Por favor ingrese un nombre"
                	
                
            },
            submitHandler:function (form) {
            	 _this.guardar();
            }
        });
	}
	
	agregar()
	{
		super.agregar();
		//$('#nombreInput').focus();
		
		
	}
	
	consultarCombos()
	{
		if(this.modo==Modo.CAMBIO)
		{
			$('#empresaIdSelect').attr("disabled","disabled");
			//$('#sedeIdSelect').attr("disabled","disabled");
			//$('#usuarioIdSelect').attr("disabled","disabled");
			$('#sedeIdSelectProcedimiento').attr("disabled","disabled");
			$('#procedimientoIdSelect').attr("disabled","disabled");
		}
			
		this.consultarEmpresas();
	}
	
	editar(id)
	{
		super.editar(id);
		$('#nombreInput').focus();
	}
	
	get criteriosSeleccion()
	{
		var criteriosSeleccion = 
		{
			empresaId:  $('#empresaSelectCriterio').val(),
			sedeId:  $('#sedeSelectCriterio').val(),
			departamentoId:  $('#departamentoSelectCriterio').val(),
			usuarioId:  $('#usuarioSelectCriterio').val(),
			estatus:  $('#estatusSelectCriterio').val(),
		};
		return criteriosSeleccion;
	}
	set modelo(valor)
	{		
		this.modeloEdicion = valor;
		
		
		if(this.modeloEdicion.estatus==1)
			$("#estatusRadio").prop('checked', true);
		else
			$("#estatusRadio").prop('checked', false);
	
		
		this.consultarCombos();
	}
	
	get modelo()
	{
		 var modelo = 
		 {		
			 empresaId:$('#empresaIdSelect').val(),
			 sedeIdUsuario:$('#sedeIdSelectUsuario').val(),
			 sedeIdProcedimiento:$('#sedeIdSelectProcedimiento').val(),
			 usuarioId:$('#usuarioIdSelect').val(),
			 procedimientoId:$('#procedimientoIdSelect').val(),
			 estatus:$('#estatusRadio').is(':checked')?1:0
		 };
		 if(this.modo=="CAMBIO" && this.modeloEdicion!=null)
			 modelo.id = this.modeloEdicion.id;
		 return modelo;
	 }
	 


	limpiarFormulario()
	{
		$('#nombreInput').val("");
		this.cargandoOpciones('#empresaIdSelect');
		this.cargandoOpciones('#sedeIdSelectUsuario');
		this.cargandoOpciones('#sedeIdSelectProcedimiento');
	}
	
	consultarEmpresas()
	{
		this.cargandoOpciones("#empresaIdSelect");
		this.cargandoOpciones("#sedeIdSelectUsuario");
		this.cargandoOpciones("#sedeIdSelectProcedimiento");
		this.cargandoOpciones("#usuarioIdSelect");
		this.cargandoOpciones("#procedimientoIdSelect");
		this.presentador.consultarEmpresas();
	}
	
	set empresas(registros)
	{		
		this.cargarOpciones('#empresaIdSelect', registros, this.modo, this.modeloEdicion, 'empresaId',"");
		if(this.modo==Modo.ALTA)
		{
			if(this.criteriosSeleccion.empresaId!="")
				$("#empresaIdSelect").val(this.criteriosSeleccion.empresaId);
		}
	}
	
	consultarEmpresasCriterio()
	{
		this.cargandoOpciones("#empresaSelectCriterio");
		this.cargandoOpciones("#sedeSelectCriterio");
		this.presentador.consultarEmpresasCriterio();
	}
	
	cambiarEmpresaCriterio()
	{
		this.cargandoOpciones("#areaSelectCriterio");
		this.consultarSedesCriterio();
	}
	
	consultarSedesCriterio()
	{
		this.cargandoOpciones("#sedeSelectCriterio");
		this.cargandoOpciones("#departamentoSelectCriterio");
		this.presentador.consultarSedesCriterio();
	}
	
	
	consultarDepartamentosCriterio()
	{
		this.cargandoOpciones("#departamentoSelectCriterio");
		this.presentador.consultarDepartamentosCriterio();
	}
	

	consultarUsuariosCriterio()
	{
		this.cargandoOpciones("#usuarioSelectCriterio");
		this.presentador.consultarUsuariosCriterio();
	}
	
	set departamentosCriterio(registros)
	{		
		this.cargarOpciones('#departamentoSelectCriterio', registros);
	}
	
	set empresasCriterio(registros)
	{		
		this.cargarOpciones('#empresaSelectCriterio', registros);
		//this.consultar();
	}
	
	set sedesCriterio(registros)
	{		
		this.cargarOpciones('#sedeSelectCriterio', registros);
	}
	
	
	cambiarEmpresa()
	{
		this.consultarSedes();
	}
	
	cambiarSede()
	{
		this.consultarUsuarios();
	}
	
	cambiarSedeProcedimiento()
	{
		this.consultarProcesos();
	}
	
	consultarUsuarios()
	{
		this.cargandoOpciones("#usuarioIdSelect");
		this.presentador.consultarUsuarios();
	}
	
	consultarProcesos()
	{
		this.cargandoOpciones("#procedimientoIdSelect");
		this.presentador.consultarProcesos();
	}
	
	consultarSedes()
	{
		this.cargandoOpciones("#sedeIdSelectUsuario");
		this.cargandoOpciones("#sedeIdSelectProcedimiento");
		this.presentador.consultarSedes();
	}
	
	set sedes(registros)
	{		
		this.cargarOpciones('#sedeIdSelectUsuario', registros, this.modo, this.modeloEdicion, 'usuarioSedeId',"");
		this.cargarOpciones('#sedeIdSelectProcedimiento', registros, this.modo, this.modeloEdicion, 'procedimientoSedeId',"");
	}
	
	set usuarios(registros)
	{		
		this.cargarOpciones('#usuarioIdSelect', registros, this.modo, this.modeloEdicion, 'usuarioId',"","nombreCompleto");
	}
	
	set procedimientos(registros)
	{		
		this.cargarOpciones('#procedimientoIdSelect', registros, this.modo, this.modeloEdicion, 'procedimientoId',"");
	}

	renderArchivos(renglon, type, set)
	{  
		return "<div id='archivo"+renglon.id+"'>" + vista.getArchivos(renglon) + "</div>";
	}
	
	getArchivos(renglon)
	{
		var contenido = "";
		var tieneArchivos = renglon.archivo!=""?true:false;
		if(tieneArchivos)
		{
			contenido = this.getContenidoArchivo(renglon.archivo);
		
		}
	    return contenido;
	}
	
	getContenidoArchivo(nombreArchivo)
	{
		var contenido = "";
		if (nombreArchivo == null || nombreArchivo == "")
			return contenido;
		contenido = "<div class='archivo' data-toggle='tooltip' data-placemen='bottom' title='"+nombreArchivo+"'>";
		contenido+= "<i  class='archivos fa fa-lg fa-paperclip' style='cursor:pointer'></i>";
		contenido+="<span  class='labelArchivo'>1</span>";
		contenido+="</div>";
		return contenido;
	}
	
	
	inicializarEventosBotonesTabla(tbody, table, nombresCamposLlave)
	{
		super.inicializarEventosBotonesTabla(tbody, table, nombresCamposLlave);
		var _this = this;
		$(tbody).on("click", "div.archivo", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }
			
			_this._indiceArchivoSeleccionado = table.row( tr ).index(); 

			_this._registroSeleccionado  = table.row( tr ).data();
			if (_this._registroSeleccionado != undefined)
			{
				if(_this._registroSeleccionado.archivo!="")
				{
					var vistaPrevia = new VistaPreviaArchivo();
					
					
					var usuariosAsignados = "";
					if(_this._registroSeleccionado.fotosPerfil!=undefined)
					{
						if(_this._registroSeleccionado.fotosPerfil.length==1)
							usuariosAsignados = "1 usuario asignado: " + _this._registroSeleccionado.usuariosNombres;
						else
							usuariosAsignados = _this._registroSeleccionado.fotosPerfil.length + " usuarios asignados: " + _this._registroSeleccionado.usuariosNombres;
					}
					vistaPrevia.visualizar(_this, "php/archivos_procesos", _this._registroSeleccionado.procedimientoId, _this._registroSeleccionado.archivo, _this._registroSeleccionado.nombre, usuariosAsignados);
				}
				else
					_this.mostrarMensajeAdvertencia("","Para visualizar archivos es necesario guardar la información.")
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
				_this.reportarSinCambios(this._usuarioProcesoSeleccionado);
			
			}
		});
		
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
		
	}
	
	mostrarObservaciones(usuarioProceso)
	{
		var _this = this;
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
			$("#closeButton").click(function(){_this.mostrarAdvertenciaObservaciones();});
			$("#cancelarButton").click(function(){_this.mostrarAdvertenciaObservaciones();});
			$("#cancelarButton").html("Cancelar");
			_this.cambiosObservaciones = false;
			
			
		},null,"observacionesModal","","guardarObservacionesButton",function()
		{
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
		this.mostrarFormularioHTML(HANDEL_API+"/html/modales/proceso_revisado_observacion_archivo.php",this, null, function()
		{
			$("#observacionModal").find(".modal-dialog").addClass("modal-lg").css("width","90%");
			if(usuarioProceso.archivo!="" && usuarioProceso.archivo!=null)
			{
				$("#contenedorArchivo").show();
				$("#nombreArchivoSpan").html(usuarioProceso.archivo);
				var vistaPrevia = new VistaPreviaArchivo();
				vistaPrevia.vistaPreviaArchivo(_this, "php/archivos_procesos", usuarioProceso.procedimientoId, usuarioProceso.archivo);

			}
			else
			{
				$("#sinArchivoContenedor").show();
			}
				
			
				
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
			this.cambiosObservaciones = true;
		}
		
	}
	
	guardarObservacionNueva(observacion)
	{
		this.presentador.guardarObservacionNueva(observacion);
	}
	
	reportarSinCambios(usuarioProceso)
	{
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
	
	mostrarAdvertenciaObservaciones()
	{
		var _this = this;
		if(_this.cambiosObservaciones)
		{
			swal({
		            title: "\u00bfEst\u00E1 seguro de cerrar sin guardar?",
		            text: "Hay cambios sin guardar",
		            type: "warning",
		            showCancelButton: true,
		            confirmButtonColor: "#DD6B55",
		            confirmButtonText: "Si, cerrar!!",
		            cancelButtonText: "No",
		            closeOnConfirm: false,
		            closeOnCancel: true,
		            showLoaderOnConfirm: true,
		        },
		        function(isConfirm)
		        {
		            if (isConfirm) 
		            {
						$("#observacionesModal").modal("hide");
						_this.cambiosObservaciones = false;
						swal.close();
		            	
		            }
		        });
		}
		else
			$("#observacionesModal").modal("hide");
	}
	
	consultarTiposObservacion()
	{
		this.presentador.consultarTiposObservacion();
	}
	
	set tiposObservacion(registros)
	{		
		this.cargarOpciones('#tipoObservacionSelect', registros, this.modoObservacion, this._observacionSeleccionada, 'tipoObservacionId',"");
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
	
	eliminarProceso(usuarioProceso)
	{
		var row = $("#procedimientosPendientesTabla").find("tr[data-id="+usuarioProceso.id+"]");
		row.fadeOut();
		setTimeout(function()
		{
    		 row.remove();
         }, 1000);
	}
	
	set datosFormatos(datos)
	{
		this.formatosTabla.registros = datos;	
		this.inicializarEventosFormatosTabla("#" + this.formatosTabla._id+"Table tbody",this.formatosTabla.datatable.DataTable());
	}
	
	inicializarEventosFormatosTabla(tbody, table, nombresCamposLlave)
	{
		//super.inicializarEventosBotonesTabla(tbody, table, nombresCamposLlave);
		var _this = this;
		$(tbody).on("click", "div.archivo", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }
			
			_this._indiceArchivoSeleccionado = table.row( tr ).index(); 

			_this._registroSeleccionado  = table.row( tr ).data();
			if (_this._registroSeleccionado != undefined)
			{
				if(_this._registroSeleccionado.archivo!="")
				{
					var vistaPrevia = new VistaPreviaArchivo();
					
					var usuariosAsignados = "";
					if(_this._registroSeleccionado.fotosPerfil!=undefined)
					{
						if(_this._registroSeleccionado.fotosPerfil.length==1)
							usuariosAsignados = "1 usuario asignado: " + _this._registroSeleccionado.usuariosNombres;
						else
							usuariosAsignados = _this._registroSeleccionado.fotosPerfil.length + " usuarios asignados: " + _this._registroSeleccionado.usuariosNombres;
					}
					vistaPrevia.visualizar(_this, "php/archivos_formatos", _this._registroSeleccionado.formatoId, _this._registroSeleccionado.archivo, _this._registroSeleccionado.nombre, usuariosAsignados);
				}
			}
		});
		
	
		
	}
	
	cambiarSedeCriterio()
	{
		this.consultarDepartamentosCriterio();
	}
	
	cambiarDepartamentoCriterio()
	{
		this.consultarUsuariosCriterio();
	}
	
	set usuariosCriterio(registros)
	{		
		//this.cargarOpciones('#usuarioSelectCriterio', registros);
		this.cargarOpciones('#usuarioSelectCriterio', registros, null, null, null, null,  "nombreCompleto");
//		if(this.consultoGrid==false)
//		{
//			this.consultar();
//			this.consultoGrid=true;
//		}	
	}
	
	renderFotoUsuario(renglon, type, set)
	{    
		var fecha = new Date();
		var contenido = "";
		var icono = HANDEL_API+ "/"+renglon.fotoPerfil+"?"+vista.time;
		contenido += "<center><img src='" + icono + "' style='width:30px;height:30px;border-radius: 50%'></img></center>";
	    return contenido;
	}
	
}
var vista = new ManualSeguridadVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});
