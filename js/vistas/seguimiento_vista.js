class SeguimientoVista extends CatalogoVista	
{		
	constructor(ventana)
	{	
		super(ventana);
		this.presentador = new SeguimientoPresentador(this);
		//this._urlFormulario = "html/formularios/areas.php";
		this.consultoGrid = false;
		//this.recomendacionesTabla = new Tabla("recomendacionesTabla");	
	}
	
	inicializar()
	{
		super.inicializar();
		this.crearTablas();
		
		var _this = this;
		$("#tituloAuditoriaDiv").click(function(){_this.mostrarAuditorias();});
		//this.consultarEmpresasCriterio();
	}
	
	crearTablas()
	{
		if($("#auditoriasTabla").length!=0)
		{
			this.auditoriasTabla = new Tabla("auditoriasTabla");
			this.auditoriasTabla.columnas = [
					{longitud:50, 	titulo:"",   	alias:"icono", alineacion:"D", itemRenderer:this.renderIcono},
					{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"D" },
					{longitud:200, 	titulo:"Plantilla",   alias:"plantillaNombre", alineacion:"I" }, 
					//{longitud:200, 	titulo:"Seguimiento iniciado",   alias:"seguimiento", alineacion:"D", itemRenderer:this.renderSeguimiento},		
					{longitud:300, 	titulo:"Empresa",   alias:"empresaNombre", alineacion:"I" }, 	
					{longitud:250, 	titulo:"Fecha de auditoria",   alias:"fechaEjecucion", alineacion:"I" },
					{longitud:50, 	titulo:"Puntuación",   alias:"puntuacion", alineacion:"C", itemRenderer: this.rendererPuntuacion },
					{longitud:50, 	titulo:"Total de acciones recomendadas",   	alias:"recomendacionesTotal", alineacion:"C" },
					{longitud:50, 	titulo:"Acciones pendientes",   alias:"recomendacionesPendientes", alineacion:"C" },
					//{longitud:50, 	titulo:"Número",   alias:"contadorEmpresa", alineacion:"C" },
					//{longitud:200, 	titulo:"Referencia",   alias:"referencia", alineacion:"I" },
					
					];
		
			this.auditoriasTabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Ver acciones recomendadas'  type='button' class='recomendaciones btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-list fa-lg'></span></button>";

			this.auditoriasTabla.registros = [];
		}
		
		if($("#recomendacionesTabla").length!=0)
		{
			this.recomendacionesTabla = new Tabla("recomendacionesTabla");
			
			this.recomendacionesTabla.columnas = [
				//º	{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"C" },
					{longitud:80, 	titulo:"Fecha de creación",   alias:"fechaAlta", alineacion:"I" }, 
					{longitud:400, 	titulo:"Acciones",   alias:"titulo", alineacion:"I" }, 	
					{longitud:110, 	titulo:"% Cumplimiento",   alias:"cumplimiento", alineacion:"C", itemRenderer: this.rendererCumplimiento  }, 	
					{longitud:80, 	titulo:"Fecha compromiso",   alias:"fechaVencimiento", alineacion:"I" }, 	
					//{longitud:50, 	titulo:"Número",   alias:"contadorEmpresa", alineacion:"C" },
					//{longitud:200, 	titulo:"Referencia",   alias:"referencia", alineacion:"I" },
					
					];
					
			if(this.usuario.tipoUsuarioId == TipoUsuario.ADMINISTRADOR || this.usuario.tipoUsuarioId == TipoUsuario.COORDINADOR || this.usuario.tipoUsuarioId == TipoUsuario.SUPERVISOR)
			{
				this.recomendacionesTabla.columnas.push({longitud:50, 	titulo:"",   	alias:"logo", alineacion:"D" ,itemRenderer:this.renderFotoUsuario});
				this.recomendacionesTabla.columnas.push({longitud:100, 	titulo:"Usuario",   alias:"usuarioNombreCompleto", alineacion:"I"});
			}
			
					
			this.recomendacionesTabla.columnas.push({longitud:50, 	titulo:"",  alias:"", alineacion:"I" ,itemRenderer:this.renderAvance});

			this.recomendacionesTabla.textoTablaVacia = "Cargando información";
			this.recomendacionesTabla.registros = [];
			this.recomendacionesTabla.textoTablaVacia = "No hay recomendaciones";
		}
	}
	
	renderAvance(renglon, type, set)
	{    
		var contenido = "";
		if(renglon.usuarioId == vista.usuario.id)
		{
			var fecha = new Date();
			//if($("#mesSelectCriterio").val()==  fecha.getMonth() +1 && $("#anoSelectCriterio").val() ==fecha.getFullYear() )
			contenido += "<button data-toggle='tooltip' data-placemen='bottom' title='Avance'  type='button' class='avance btn-circle mr-0 botones-icon btn btn-sm float-right btn-info active'><span  data-toggle='tooltip' class='fa fa-flag-checkered fa-lg'></span></button>";
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
		
		var url = HANDEL_API+ "/"+foto;
		contenido += "<center><img src='" + url + "' style='width:30px;height:30px;border-radius: 50%'></img></center>";
	    return contenido;
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
		this.recomendaciones = [];
		this.presentador.consultarPorLlaves();

	}
	
	mostrarAuditorias()
	{
		this.modo = "";
		$('#recomendacionesSection').hide();	
		$('#auditoriasSection').fadeIn();	
		$("#encabezadoDiv").hide();
		$('#consultarButton').show();
		$("#tituloAuditoriaDiv").hide();
		this.consultar();

	}
	
	set recomendaciones(recomendaciones)
	{
		this.recomendacionesTabla.textoTablaVacia = "No hay recomendaciones";
		this.recomendacionesTabla.registros = recomendaciones;	
		this.inicializarEventosBotonesTablaRecomendaciones("#" + this.recomendacionesTabla._id+"Table tbody",this.recomendacionesTabla.datatable.DataTable());
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
				_this.mostraAvances();
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
				_this.mostrarFormularioAvance();
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
		return "";
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
		return "";
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
	
	get criteriosSeleccion()
	{
		 var criteriosSeleccion = 
		 {				    
			empresaId: $('#empresaSelectCriterio').val(),
			sedeId: $('#sedeSelectCriterio').val(),
			nombre:$('#nombreInputCriterio').val()
		 }
		 return criteriosSeleccion;
	}		
	
	set modelo(valor)
	{		
		this.modeloEdicion = valor;
		$('#fechaAuditoriaSpan').html(this.modeloEdicion.fechaEjecucion.substring(0,10));
		$("#porcentajeSpan").html(this.modeloEdicion.puntuacion + "%");
		$('#plantillaNombreSpan').html(this.modeloEdicion.plantillaNombre);
		$("#encabezadoDiv").fadeIn();
		$("#tituloAuditoriaDiv").show();
		
		this.consultarRecomendacionesPendientesUsuario();
	}
	
	consultarRecomendacionesPendientesUsuario()
	{
		this.recomendacionesTabla.textoTablaVacia = "Cargando información";
		this.presentador.consultarRecomendacionesPendientesUsuario();
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
		this.auditoriasTabla.registros = auditorias;	
		this.inicializarEventosTabla("#" + this.auditoriasTabla._id+"Table tbody",this.auditoriasTabla.datatable.DataTable());
	}
	
	mostraAvances()
	{
		var _this = this;
		this.mostrarFormularioHTML(HANDEL_API+"/html/modales/recomendaciones_avances.php",this, null, function()
		{
			this.crearTablaAvances();
			
		},null,"avancesModal","","");

	}
	
	mostrarFormularioAvance()
	{
		//var _this = this;
		this.mostrarFormularioHTML(HANDEL_API+"/html/modales/recomendaciones_avances_archivos.php",this, null, function()
		{
			this.crearTablaArchivos();
			
		},null,"archivosModal","","");

	}
	
	crearTablaAvances()
	{
		this.avancesTabla = new Tabla("avancesTabla");
		this.avancesTabla.buscar = false;
		this.avancesTabla.paginacion = false;
		this.avancesTabla.columnas = [
			{longitud:50, 	titulo:"Fecha",   alias:"fecha", alineacion:"C"} ,
			{longitud:300, 	titulo:"% Cumplimiento",   alias:"cumplimiento", alineacion:"C", itemRenderer: this.rendererCumplimiento } ,
			{longitud:50, 	titulo:"Evidencias",   alias:"nombreArchivo", alineacion:"C", itemRenderer:this.renderClip},
			{longitud:300, 	titulo:"Comentarios",   alias:"comentario", alineacion:"I" } ,
		
		]
		this.avancesTabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-edit fa-lg'></span></button>";

	
		this.avancesTabla.textoTablaVacia = "";
		this.avancesTabla.registros = [];
		
		
		this.consultarAvances();
	}
	
	crearTablaArchivos()
	{
		this.archivosTabla = new Tabla("archivosTabla");
		this.archivosTabla.buscar = false;
		this.archivosTabla.paginacion = false;
		this.archivosTabla.columnas = [
			{longitud:50, 	titulo:"",   alias:"nombre", alineacion:"C", itemRenderer:this.renderArchivo},
			{longitud:100, 	titulo:"Evidencia",   alias:"nombre", alineacion:"I"},
		
		]
		this.archivosTabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-edit fa-lg'></span></button>";

	
		this.archivosTabla.textoTablaVacia = "";
		this.archivosTabla.registros = [];
		
		
		this.consultarArchivos();
	}
	
	renderClip(renglon, type, set)
	{   
		var contenido = "";
		var tieneArchivos = renglon.archivos>0?true:false;
		if(tieneArchivos)
		{
			var html = "<i id='"+renglon.id+"clip' class='fa fa-lg fa-paperclip' style='cursor:pointer'></i>";
			html+="<span  class='labelArchivo'>"+renglon.archivos+"</span>";
			return html;
		}
	    return contenido;
	}
	
	renderArchivo(renglon, type, set)
	{   
		var contenido = "";
		var comentarios ="";
		var iconoColor = vista.getIconoArchivo(renglon.nombre);
		if(renglon.validada==1)
			comentarios = "<span class='label-success' style='position: relative;top: 6px;right: 4px;font-size: 10px;padding: 2px 3px;line-height: .9;'><i class='fas fa-check-double'></i></span>";
		contenido = "<span style='cursor:pointer;margin-left:15px;width:50px;height:30px' data-toggle='tooltip' data-placemen='bottom' title='Evidencia'  type='button' class='archivo'><span  data-toggle='tooltip' class='"+iconoColor.icono+" fa-lg "+iconoColor.color+"'>"+comentarios+"</span>";
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
		this.archivosTabla.textoTablaVacia = "No hay archivos asociados a este registro de avance";
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
		$(tbody).on("click", "button.editar", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

			_this._archivoSeleccionado  = table.row( tr ).data();
			if (_this._archivoSeleccionado != undefined)
			{
				_this._llavesArchivo= _this.copiarPropiedadesObjeto(_this._archivoSeleccionado, ["id"]);
				//_this.mostrarFormularioAvance();
			}
		});
		
	}
}
var vista = new SeguimientoVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});
