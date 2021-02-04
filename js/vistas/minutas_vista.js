class MinutasVista extends CatalogoVista
{		
	constructor()
	{	
		super();
		this.presentador = new MinutasPresentador(this);
		this._urlFormulario = "html/formularios/minutas.php";
		
		this.listaTareas = new ListaTareas("listaTareas");
		this.listaTareasPendientes = new ListaTareas("listaTareasPendientes");
		this.listaTareasPendientes.editar = false;
		this.listaTareasPendientes.eliminar = false;
		this.listaTareasPendientes.mover = false;
		this.listaTareasPendientes.minuta = true;
		this.listaTareasPendientes.removerTerminada = true;
		
		this.listaTareasTerminadas = new ListaTareas("listaTareasTerminadas");
		this._time = new Date().getTime();
		
		this._consultoMinutas = false;
		this._consultoTareasPendientes = false;
		this._consultoTareasTerminadas = false;

		this._colores = [
			"#cdd9c5",
			"#e7d1c8",
			"#d3e0e1",
			"#dcdcd1",
			"#ecede7",
			"#9e927f",
			"#bec3d9",
			"#f3b885",
			"#c8bec2",
			"#a9e5e3",
			"#b5bcc3",
			"#c39297",
			"#ffffff",
			"#f8e9b2",
			"#b8bdd2",
			"#f7dfed",
			"#d2cbc1",
			"#dbdcde"];
		
		var fecha = new Date();
		this._time = fecha.getTime();
		
		$("#crearMinutaLink").click(function(){
			$('.nav-tabs a[href="#minutas"]').tab('show');
		});
		
		$('#colorPickerGroup').colorpicker();
		
		var _this = this;
		$("#generarPDF1Button").click(function(){
			_this._llaves = {id: _this.modeloEdicion.id};
			_this.imprimirReporte();
		});
		$("#generarPDF2Button").click(function(){
			_this._llaves = {id: _this.modeloEdicion.id};
			_this.imprimirReporte();
		});
	}
	
	
//	get time()
//	{
//		return this._time;
//	}
	
	inicializar()
	{
		var _this = this;

		$("#consultarButton").click(function(){
			_this.consultar();
		});
		
		$("#consultarTareasButton").click(function(){
			_this.consultarMisTareas();
		});
		
		$("#agregarButton").click(function(){
			_this.agregar();
		});
		
		
		this.crearColumnasGrid();		
		
		
//		if(this.minutaIdParametro=="" && this.tareaIdParametro=="")
//			this.consultar();
//		else if(this.minutaIdParametro=="" && this.tareaIdParametro=="")
//			this.mostrarTareaParametro();
		
		

		$(".salir").click(function()
		{
			_this.salirFormulario();
		});
		
		
		this.crearEventosActualizacion();
		
		
		$("#listaTareas").sortable({
		    axis: "y",
		    containment: "parent",
		    cursor: "move",
		   // items: "div",
		    tolerance: "pointer",
		    update: function( event, ui ) {
		    	var seleccion = $( "#listaTareas" ).sortable( "serialize", { key: "sort" });
				_this.presentador.ordenarTareas(seleccion);
			}
		});
	    $( "#listaTareas" ).disableSelection();
	    
//	   
//	    $("#agregarButton").click(function()
//				{
//					alert('Esta función esta en desarrollo');
//				});
	    
	    $("#editarUsuariosCompartirButton").click(function()
		{
	    	$("#groupCompartirUsuarioVisualizacion").hide();
			$("#groupCompartirUsuarioEdicion").fadeIn();
		});
	    
	    $("#usuariosCompartirCancelarButton").click(function()
		{
	    	$("#groupCompartirUsuarioVisualizacion").fadeIn();
			$("#groupCompartirUsuarioEdicion").hide();
				
		});
	    
	    $("#usuariosCompartirGuardarButton").click(function()
		{
	    	$("#groupCompartirUsuarioVisualizacion").fadeIn();
			$("#groupCompartirUsuarioEdicion").hide();
				
		});
	    
	    $("#usuariosCompartirSelect").chosen();
	    
	    if(this.minutaIdParametro!=0 && this.tareaIdParametro!=0)
			this.mostrarTareaParametro();
		else if(this.minutaIdParametro!=0)
			this.mostrarMinutaParametro();
		else
			this.consultarMisTareas();
	    
	    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
	    	  var target = $(e.target).attr("href") // activated tab
	    	  switch(target)
	    	  {
	    	  	case "#minutas":
	    	  		if(!_this._consultoMinutas)
	    	  			_this.consultar();	
	    		break;
	    	  	case "#tareas-pendientes":
	    	  		//if(!_this._consultoTareasPendientes)
	    	  			_this.consultarMisTareas();	
	    		break;
	    	  }
	    	});
	    
	   $("#buscarTareaInput").on("keyup", function() {
	        var value = $(this).val().toLowerCase();
	        $("#listaTareasPendientes li").filter(function() {
	          $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
	        });
	      });
	    
	}
	
	consultar()
	{
		this._consultoMinutas = true;
		super.consultar();
	}
	
	consultarMisTareas()
	{
		this._consultoTareasPendientes = true;
		this.presentador.consultarMisTareas();
		
		this.consultarNumeroMensajesNoLeidos();
		this.consultarTareasPendientes();
	}
	
//	editarUsuariosCompartir()
//	{
//		$("#groupCompartirUsuarioVisualizacion").hide();
//		$("#groupCompartirUsuarioEdicion").fadeIn();
//	}
	
	get time()
	{
		return this._time;
	}
	
	mostrarTareaParametro()
	{
		this._registroSeleccionado = {id : this.minutaIdParametro};
		this.editarTareaFormulario(this.listaTareas, this._registroSeleccionado.id, this.tareaIdParametro);
	}
	
	mostrarMinutaParametro()
	{
		this._llaves = {id : this.minutaIdParametro};
		this._registroSeleccionado = {id : this.minutaIdParametro};
		this.editar();
	}
	
	
	get minutaIdParametro()
	{
		var id = $("body").attr("data-id");
		var elementos = id.split("_");
		if(elementos.length>0)
		{
			return elementos[0];
		}
		return 0;
	}
	
	get tareaIdParametro()
	{
		var id = $("body").attr("data-id");
		var elementos = id.split("_");
		if(elementos.length==2)
		{
			return elementos[1];
		}
		return 0;
	}
	
	
	
	crearEventosActualizacion()
	{
		var _this = this;
		$("#tituloInput").change(this.cambiarCampo);
		$("#descripcionInput").change(this.cambiarCampo);
		$("#usuariosCompartirSelect").change(this.cambiarCampo);
		$("#tituloInput").keyup(function()
		{
			$("#tituloH").html($("#tituloInput").val());
		});
		$("#acuerdosInput").change(this.cambiarCampo);
		$("#participantesInput").change(this.cambiarCampo);
		$("#colorInput").change(this.cambiarCampo);
		
	}
	
	cambiarCampo(event)
	{
		var campo = $(event.currentTarget).attr("data-campo");
		if(campo=="usuarios")
		{
			vista.presentador.actualizarUsuarios();
		}
		else if(campo=="color")
		{
			var valor = $(event.currentTarget).val();
			var color = $(event.currentTarget).data("color");
			if(valor!=color)
			{
				 $(event.currentTarget).data("color",valor);
				vista.presentador.actualizarValor(campo,valor);
			}
		}
		else
		{
			var valor = $(event.currentTarget).val();
			vista.presentador.actualizarValor(campo,valor);
		}
	}
	
//	onLoad()
//	{			
//		this.crearColumnasGrid();		
//		this.presentador.consultar();
//	}
	
	crearColumnasGrid()
	{
		this.tabla.columnas = [
			{longitud:30, 	titulo:"",   alias:"terminada", alineacion:"I", itemRenderer: this.renderTerminada},
			{longitud:40, 	titulo:"Id",   	alias:"id", alineacion:"D" },
			{longitud:200, 	titulo:"Título",   alias:"titulo", alineacion:"I" },
			{longitud:300, 	titulo:"Descripción",   alias:"descripcion", alineacion:"I" },
			{longitud:50, 	titulo:"Color de etiqueta",   alias:"color", alineacion:"C", itemRenderer: this.renderColor },
			{longitud:50, 	titulo:"Avance",   alias:"titulo", alineacion:"C", itemRenderer: this.rendererPorcentaje },
			{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"D" ,itemRenderer:this.renderLogo},
			{longitud:200, 	titulo:"Usuario que creó" ,   alias:"usuarioNombreCompleto", alineacion:"I",class: "desc" }, 
			{longitud:250, 	titulo:"Fecha de alta",   alias:"fechaAlta", alineacion:"I" },	
			{longitud:200, 	titulo:"Fecha de última modificación",   alias:"fechaModificacion", alineacion:"I" },
			{longitud:100, 	titulo:"Fecha de terminación",   alias:"fechaFinalizacion", alineacion:"I"}
		]
		
		
		
		this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='PDF'  type='button' class='imprimir btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active' style='background-color:#d62929'><span  data-toggle='tooltip' class='fa fa-file-pdf-o fa-lg'></span></button>" +
		
		"<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-edit fa-lg'></span></button>"+
		"<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";

		this.tabla.registros = [];
	}
	
	inicializarEventosBotonesTabla(tbody, table, nombresCamposLlave)
	{
		super.inicializarEventosBotonesTabla(tbody, table, nombresCamposLlave);
		var _this = this;
		$(tbody).on("click", "button.imprimir", function()
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
	}
	
	imprimirReporte()
	{
		var submitForm = this.getNewSubmitForm(HANDEL_API+"/php/reportes/reporte_minuta.php");
		this.createNewFormElement(submitForm, "minutaId", this._llaves.id);	 
	    submitForm.target= "_blank";
	    submitForm.submit();
	}
	
	renderLogo(renglon, type, set)
	{    
		var fecha = new Date();
		var contenido = "";
		var icono = HANDEL_API+ "/"+renglon.fotoPerfil+"?"+vista.time;
		contenido += "<center><img src='" + icono + "' style='width:30px;height:30px;border-radius:50%'></img></center>";
	    return contenido;
	}
	
	rendererPorcentaje(renglon, type, set)
	{    
		//if(renglon.fechaUltimaCapacitacion!=null)
		//{
			if(renglon.porcentaje==undefined)
				renglon.porcentaje = 0;
		
			var cantidad = renglon.terminadas + " / " + renglon.total;
			var porcentajeCumplimiento = parseFloat(renglon.porcentaje);
			var label ="";
			if(porcentajeCumplimiento >= 0 && porcentajeCumplimiento < 51)
			{
				label = "text-red";
			}
			else if(porcentajeCumplimiento >= 51 && porcentajeCumplimiento < 100)
			{
				label = "text-yellow";
			}
			else if(porcentajeCumplimiento >= 100)
			{
				label = "text-green";
			}
			return "<span data-toggle='tooltip' data-placemen='bottom' title='"+cantidad+"'  style='font-weight:bold' class='"+label+"'>"+porcentajeCumplimiento+"%</span>";
		//}
		return "";
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
                "nombreInput": {
                    required: !0
                }
            },
            messages: {
                "nombreInput": "Por favor ingrese un nombre"
                	
                
            },
            submitHandler:function (form) {
            	 _this.guardar();
            }
        });
	}
	
	renderTerminada(renglon, campoBase)
	{    
		var contenido = "";
		if(renglon.terminada==1)
			contenido += "<center><span class='fa fa-check fa-lg text-green' ></span></center>";
		else
			contenido += "";//;"<center><span class='fa fa-check fa-lg text-green' ></span></center>";	    
		return contenido;
	}
	
	renderColor(renglon, campoBase)
	{    
		var contenido = "";
		contenido += "<center><div style='background-color:"+renglon.color+"; width: 20px; height: 20px; border-radius: 3px;'></center>";
		return contenido;
	}
	

	
	get criteriosSeleccion()
	{
		 var criteriosSeleccion = 
		 {				    
			titulo:$('#tituloInputCriterio').val(),
			terminada: $('#terminadaSelectCriterio').val()
		 }
		 return criteriosSeleccion;
	}		
	
	get criteriosSeleccionTareas()
	{
		 var criteriosSeleccion = 
		 {				    
			terminada: $('#tareaTerminadaSelectCriterio').val()
		 }
		 return criteriosSeleccion;
	}		


	set modelo(valor)
	{		
		this.modeloEdicion = valor;
		$('#tituloH').html(this.modeloEdicion.titulo);
		$('#avanceH').html("("+this.modeloEdicion.porcentaje + " % de avance)");
		$('#tituloInput').val(this.modeloEdicion.titulo);
		$('#descripcionInput').val(this.modeloEdicion.descripcion);
		$("input[name=estatus][value=" + this.modeloEdicion.estatus + "]").prop('checked', true);
		
		
		
		$('#tareasSectionContenido').fadeIn();	
		$("#agregarTareaButton").show();
		
		
		var fecha = new Date();
		var foto = HANDEL_API + "/" + this.modeloEdicion.fotoPerfil+"?"+fecha.getTime();
		
		
//		var fotoPerfil = HANDEL_API + "/" + this.modeloEdicion.fotoPerfil+"?"+fecha.getTime();
//		$("#fotoPerfilComentarioImg").attr("src",fotoPerfil);
		
		moment.locale('es') ;
		var fechaAlta = this.getFechaMDA(this.modeloEdicion.fechaAlta);
		
		var fecha = moment(fechaAlta,"MM/DD/YYYY h:mm:ss");
		
		var html = "<img class='img-circle' src='"+foto+"' "+
					"alt='User Image'> <span class='username'><a href='#'>" + this.modeloEdicion.usuarioNombreCompleto +"</a>" +
					"</span> <span class='description' title='"+this.modeloEdicion.fechaAlta+"'  >Creada" +
					"- " + fecha.fromNow() + "</span>";
		$("#usuarioDiv").html(html);
		
		$('#acuerdosInput').val(this.modeloEdicion.acuerdos);
		$('#participantesInput').val(this.modeloEdicion.participantes);
		$("#colorInput").val(this.modeloEdicion.color);
		var i = $('.input-group-addon').find("i");
		i.css('backgroundColor',this.modeloEdicion.color);
		$("#colorInput").data("color",this.modeloEdicion.color);
		
		
		this.consultarResponsables();
		
		
		
		
	}
	
	consultarResponsables()
	{
		this.presentador.consultarResponsables();
	}
	
	set responsables(responsables)
	{
//		this._responsables = responsables;
//		
		this._responsables = responsables;
		for(var i=0; i < this._responsables.length; i++)
		{
			var responsable = this._responsables[i];
			if(responsable.id == this.usuario.id)
				responsable.nombreCompleto = responsable.nombreCompleto + " (Yo)";
			else if(responsable.tipoUsuarioId == TipoUsuario.ADMINISTRADOR)
				responsable.nombreCompleto +=" (Handel)"

				
		}
		
		
		
		
		
//		this.cargarOpciones('#usuariosCompartirSelect', responsables,"",null, null, null);
		
		
		
		
		
	
		
		if(this.modo=="CAMBIO" && this.modeloEdicion!=null)
		{
			this.listaTareas.responsables = this._responsables;
			this.listaTareas.tareas= this.modeloEdicion.tareas;
		}
		else
		{
			
		}
		
		this.usuariosCompartir = responsables;
		
		
		//this.presentador.consultarPorLlaves();

	}
	
	set usuariosCompartir(responsables)
	{
		
		var usuarios = ArrayUtils.copy(responsables);
		
		eliminarPorValor(usuarios,"id",this.usuario.id);
		
		var select = "#usuariosCompartirSelect";
		$(select).empty();
		var fecha = new Date();
		$.each(usuarios, function(i, p) 
		{
			var fotoPerfil = HANDEL_API+ "/"+p.fotoPerfil+"?"+fecha.getTime();
			var nombre  = p.nombreCompleto;
		    $(select).append($('<option data-img-src="'+fotoPerfil+'"></option>').val(p.id).html(nombre));
		});
		
		var usuariosSeleccionados =[];
		if(this.modeloEdicion.usuarios!=undefined)
		{
			$.each(this.modeloEdicion.usuarios, function(i, p) 
			{
				usuariosSeleccionados.push(p.usuarioId);
			});
		}
		
		$("#usuariosCompartirSelect").val(usuariosSeleccionados);
		 $('#usuariosCompartirSelect').trigger("chosen:updated");
		 //$("#perfilesSelect").chosen();
		$(".chosen-search-input").height(50);
		$(".chosen-search-input").val("");
		
		$("#usuariosCompartirSelect_chosen").css("width","100%");
		
		if(this.modeloEdicion.usuarioId == this.usuario.id)
			$("#compartirGroup").fadeIn();
		
//		var responsablesSeleccionados =[];
//		if(this.registro.responsables!=undefined)
//		{
//			
//			$.each(this.registro.responsables, function(i, p) 
//			{
//				responsablesSeleccionados.push(p.usuarioId);
//			});
//		}
//		
//		$(select).val(responsablesSeleccionados);
//		$(select).chosen();
//		$(select+"_chosen").width("100%");
	}
	
	
	get modelo()
	{
		 var modelo = 
		 {		
			 titulo:$('#tituloInputAlta').val(),		
			 descripcion:$('#descripcionInputAlta').val(),	
			 estatus:$('#estatusRadio').is(':checked')?1:0
		 };
		 if(this.modo=="CAMBIO" && this.modeloEdicion!=null)
			 modelo.id = this.modeloEdicion.id;
		 return modelo;
	 }
	 


	limpiarFormulario()
	{
		$("#compartirGroup").hide();
		$('#formulario').trigger("reset");
	}
	
	
	editar(id)
	{
		this.modo = "CAMBIO";
		this.limpiarFormulario();	
		this.mostrarFormulario();
		$('#tituloInput').focus();				
		//this.inicializarValidacionesFormulario("formulario");
		this.listaTareas.tareas = [];
		
		this.presentador.consultarPorLlaves();

		//this._cursoId =id;
	}
	
	editarTareaFormulario(listaTareas,minutaId,tareaId)
	{
		this.modo = "CAMBIO";
		this._tareaId = tareaId;
		this.mostrarFormularioTarea(listaTareas,minutaId, tareaId);
	}
	
	get tareaId()
	{
		return this._tareaId;
	}
	
	
	
	mostrarFormularioTarea(listaTareas,minutaId, tareaId)
	{
		var _this = this;
		if($("#modalAlta").length ==0)
		{
			var url = HANDEL_API + "/html/modales/tarea.php";
			this.mostrarIndicador();
			var _this = this;
			$.post(url,{}, function(html) 
			{
				_this.ocultarIndicador();
				$("body").append(html);
				$("#modalAlta").on("hidden.bs.modal", function () {
					clearInterval(_this.cometariosIntervalId);
					$("#modalAlta").remove();
					_this._tareaEdicionFormulario = null;
					_this.consultarNumeroComentariosTarea(listaTareas,minutaId, tareaId);
//					if($("#minutasSection").is(":visible"))
//						_this.consultar();
					
				});
				
				$("#modalAlta").on("show.bs.modal", function () 
				{
					_this._llavesTarea = {minutaId : minutaId, tareaId: tareaId};
					_this.consultarTareaPorLlaves();
					
					var fotoPerfil = HANDEL_API + "/" + _this.usuario.fotoPerfil+"?"+_this.time;
					$("#fotoPerfilComentarioImg").attr("src",fotoPerfil);
					
					moment.locale('es') ;
					
					$("#enviarComentarioButton").click(function () 
					{
						var comentario = $("#comentarioInput").val().trim();
						if(comentario!="")
							_this.enviarComentario();
					});
					$("#comentarioInput").keypress(function(event){
					    var keycode = (event.keyCode ? event.keyCode : event.which);
					    if(keycode == '13')
					    {
					    	var comentario = $("#comentarioInput").val().trim();
							if(comentario!="")
								_this.enviarComentario();
					    }
					});
					_this._comentarios = [];
					_this.consultarComentarios();
					_this.cometariosIntervalId = setInterval(_this.consultarComentariosAutomaticamente, 20000);
					
					setInterval(function(){$("#comentarioInput").focus();},2000);
					
				});
				
			
			
				
				$("#modalAlta").modal({backdrop: 'static', keyboard: false});
			});
		}
		else
		{
			$("#modalAlta").modal({backdrop: 'static', keyboard: false});
		}
	}
	
	consultarComentariosAutomaticamente()
	{
		var _this  = $("body").data("_this");
		vista.consultarComentarios();
	}
	
	get llavesTarea()
	{
		return this._llavesTarea;
	}
	
	consultarTareaPorLlaves()
	{
		this.presentador.consultarTareaPorLlaves();
	}
	
	actualizar(componente,modelo)
	{
		
		componente.listaTareas.actualizar(modelo,true);
		componente.listaTareas.cancelarEdicion();
		 
		 if(this._tareaEdicionFormulario!=null)
		{
			 this._tareaEdicionFormulario.modelo = modelo;
			 this._tareaEdicionFormulario.cancelarEdicion();
			
			 
		}
	}
	
	set tarea(tarea)
	{
//		$("#contenidoTarea").show();
//		$("#tituloTarea").html(tarea.titulo);
//		this.consultarComentariosTarea();
		this._tareaEdicionFormulario = new Tarea(this,"formulario_tareaDiv",tarea,this._responsables,false,false,false,false);
		this._tareaEdicionFormulario.renderizar();
		//html+=this.crearContenedor(registro);
		//this.componentes.push(componente);
		
		
		
	}
	
	
	
	consultarComentarios()
	{
		this.presentador.consultarComentarios();
	}
	
	get minutaId()
	{
		return this._registroSeleccionado.id;
	}
	
	agregar()
	{
		this.modo = Modo.ALTA;
		this.ocultarIndicador();
		this.mostrarFormularioAlta();
		//$('#nombreInput').focus();
		//this.inicializarValidacionesFormulario();
	}
	
	mostrarFormularioAlta()
	{
		var _this = this;
		this.mostrarFormularioHTML(HANDEL_API+"/html/formularios/minutas.php",this, null, function()
		{
			//mostrar
			 setTimeout(function(){
					$('#tituloInputAlta').focus();
//					$('#logoImageAlta').show();
//					$('#logoImageAlta').attr('src', HANDEL_API + "/php/portadas_cursos/default.png");
					_this.inicializarValidacionesFormularioAlta("formularioAlta");
	            }, 1000);
			 
			 
			
		},null,"","","guardarButtonAlta",function()
		{
			//guardar
			$("#formularioAlta").submit();
			//_this.insertar();
			
		});
	}

	
	inicializarValidacionesFormularioAlta(formulario)
	{
		var _this = this;
		jQuery("#" +formulario).validate({
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
                "tituloInputAlta": {
                    required: !0
                }
               
            },
            messages: {
                "tituloInputAlta": "Por favor ingrese un t\u00edtulo",
                	
                
            },
            submitHandler:function (form) {
            	 _this.guardar();
            }
        });
	}
	
	mostrarFormulario()
	{
		$('#minutasSection').hide();	
		$('#tareasSectionContenido').hide();	
		$('#tareasSection').show();
		//$('#guardarButton').hide();
		
	}
	
	salirFormulario()
	{
		$('#minutasSection').show()
		$('#tareasSectionContenido').hide();	
		$('#tareasSection').hide();
		$("#compartirGroup").hide();
		//this.consultar();
		var target = $("ul#minutasNav li.active").find("a").attr("href");
    	  switch(target)
    	  {
    	  	case "#minutas":
    	  		//if(!this._consultoMinutas)
    	  			this.consultar();	
    		break;
    	  	case "#tareas-pendientes":
    	  		//if(!_this._consultoTareasPendientes)
    	  			this.consultarMisTareas();	
    		break;
    	  }
	}
	
	salirFormularioAlta()
	{
		$('#modalAlta').modal('hide')
	}
	
	eliminar(texto)
	{ 
		if(texto==undefined)
			texto ="Se eliminar\u00e1 la minuta<br><label>"+this._registroSeleccionado.titulo+"</label>";
		var _this = this;
		swal({
	            title: "\u00bfEst\u00E1 seguro de eliminar?",
	            text: texto,
	            html: true,
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
	            		 _this.presentador.eliminar();
	 	            }, 1000);
	            }
	        });
	}
	
	cambiarCampoTarea(componente,tarea,tareaId,campo,valor)
	{
		vista.presentador.actualizarValorTarea(componente,tarea,tareaId,campo,valor);
	}
	
	eliminarTarea(event, minutaId, tareaId)
	{
		var _this = this;
		var componenteTarea = this.listaTareas.getComponente(minutaId, tareaId);
		if(componenteTarea!=null)
		{
			var _this = this;
			this.confirmar("¿Desea eliminar esta tarea?</br></br><label>" +componenteTarea.titulo +"</label>",this,function(tareaId)
			{
				_this._llavesTarea = {minutaId : minutaId, tareaId: tareaId};
				_this.eliminarTareaBaseDatos();
				
			},tareaId,true);
		}
	}

	confirmar(textoDialogo,contexto,funcion,parametro,html)
	{
		var _this = this;
		swal({
	            title: "",
	            text: textoDialogo,
	            type: "warning",
	            html: html,
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
	            	 setTimeout(function()
	            			 {
	            			funcion.call(contexto,parametro);
	            			swal.close();
	 	            }, 1000);
	            }
	        });
	}
	
	eliminarTareaBaseDatos()
	{
		this.presentador.eliminarTarea();
	}
	
	get llavesTarea()
	{
		return this._llavesTarea;
	}

	agregarTarea()
	{
	
		this.listaTareas.cancelarEdicion();
		this.listaTareas.agregarBorrador();
		$("#agregarTareaButton").hide();
		
	}
	
	insertarTarea(modelo)
	{
		this.presentador.insertarTarea(modelo);
	}
	
	actualizarTarea(componente,modelo)
	{
		this.presentador.actualizarTarea(componente,modelo);
	}
	
	
	mostrarBotonAgregar()
	{
		$("#agregarTareaButton").fadeIn();
	}
	
	
	eliminarBorrador()
	{
		this.listaTareas.eliminarBorrador();
		$("#agregarTareaButton").fadeIn();
	}
	
	editarTarea(event,id)
	{
		this.listaTareas.editar(id);
	}
	
	set comentarios(comentarios)
	{
		var numero="";
		if(comentarios.length==1)
			numero = "1 comentario";
		else
			numero = comentarios.length+" comentarios";
		$("#numeroComentariosSpan").html(numero);
		if(comentarios.length> this._comentarios.length)
		{
			this._comentarios= comentarios;
			
			var usuariosColores = this.asignarColoresUsuarios();
		
			var html="";
			for(var i=0; i< comentarios.length; i++)
			{
				var fecha = new Date();
				var comentario = comentarios[i];
				var foto = HANDEL_API + "/" + comentario.fotoPerfil+"?"+this.time;

				var color = this.buscarPorValor(usuariosColores,"usuarioId",comentario.usuarioId);
				
				
				moment.locale('es') ;
				var fechaComentario = moment(comentario.fecha);
				
				html+="<div class='box-comment' data-usuarioId='"+comentario.usuarioId+"' style='background-color:"+color.color+";padding:5px;'>" +
				"<img class='img-circle img-sm' src='"+foto+"' alt='User Image'>" +
				"<div class='comment-text'>" +
					"<span class='username'> "+comentario.usuarioNombreCompleto+" <span class='text-muted pull-right'>"+fechaComentario.fromNow()+"</span>" +
					"</span>" + comentario.comentario +
				"</div>" +
				"</div>";
				
			}
			$("#comentariosDiv").html(html);
		}	
	}
	

	asignarColoresUsuarios()
	{
		var usuariosColores = []; 
		var indiceColor = 0;
		for(var i=0; i < this._comentarios.length; i++)
		{
			var comentario = this._comentarios[i];
			var usuarioId = comentario.usuarioId;
			var usuario = this.buscarPorValor(usuariosColores,"usuarioId",usuarioId);
			if(usuario==null)
			{
				var color = "#ffffff";
				if(indiceColor < this._colores.length)
					color = this._colores[indiceColor];
				usuariosColores.push({usuarioId: usuarioId, color: color});
				indiceColor++;
			}
				
		}
		return usuariosColores;
	}
	
	enviarComentario()
	{
		this.presentador.enviarComentario();
		$("#comentarioInput").val("");
	}
	
	
	get modeloComentario()
	{
		var modelo =
		{
			minutaId: this._llavesTarea.minutaId,
			tareaId : this._llavesTarea.tareaId,
			usuarioId: this.usuario.id,
			comentario: $("#comentarioInput").val()
		};
		return modelo;
	}
	
	get usuarios()
	{
		var usuarios=[];
		var usuariosSeleccionados  = $("#usuariosCompartirSelect").val();
		if(usuariosSeleccionados !=undefined)
		{
			for(var i = 0; i < usuariosSeleccionados.length ; i++)
			{
				var usuarioSeleccionado = usuariosSeleccionados[i];
				var usuario = new Object();
				usuario.id = i + 1;
				usuario.usuarioId = usuarioSeleccionado;
				usuarios.push(usuario);
			}
		}
		return usuarios;
	}
	
	set misTareasPendientes(misTareasPendientes)
	{
		this.listaTareasPendientes.tareas= misTareasPendientes;
		/*if(misTareasPendientes.length==0)
		{
			var mostrar = $("tareaTerminadaSelectCriterio").val();
			if(mostrar=="0")
				$("#tareasPendientesDiv").fadeIn();
		}
		else
			$("#tareasPendientesDiv").hide();*/
			
	}
	
	set tareasPendientes(tareasPendientes)
	{
		super.tareasPendientes = tareasPendientes;
		
		if(tareasPendientes.length==0)
		{
			var mostrar = $("#tareaTerminadaSelectCriterio").val();
			if(mostrar=="0")
				$("#tareasPendientesDiv").fadeIn();
		}
		else
			$("#tareasPendientesDiv").hide();
	}
	
	consultarNumeroComentariosTarea(listaTareas,minutaId, tareaId)
	{
		this.listaTareasActual = listaTareas;
		this.presentador.consultarNumeroComentariosTarea(minutaId, tareaId);
	}
	
	setNumeroComentariosTarea(minutaId, tareaId, numeroComentarios)
	{
		this.listaTareasActual.setNumeroComentariosTarea(minutaId,tareaId, numeroComentarios);
	}
	
	set porcentajeAvance(porcentaje){
		$('#avanceH').html("("+porcentaje + " % de avance)");
	}
	
}
var vista = new MinutasVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});

