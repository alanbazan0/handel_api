class PlantillasVista extends CatalogoVista
{		
	constructor(ventana)
	{	
		super();
		this.ventana = ventana;
		this.presentador = new PlantillasPresentador(this);

		this.listaSecciones = new ListaSecciones("listaSecciones");
		
		this.listaPreguntas = new ListaPreguntas("listaPreguntas");
		this.listaPreguntas.contexto = this; 
		this.listaPreguntas.funcionCambiarCampo = this.cambiarCampoPregunta;
		
		this.listaRespuestas = new ListaRespuestas("listaRespuestas");
		this._categorias = [];
		this._estandares = [];
		this.preguntaEdicion = null;
		this.seccionEdicion = null;
		this.velocidadAnimacion = 400;
	}
	
	inicializar()
	{	
		super.inicializar();
		//PRUEBAS
		//this.mostrarFormulario();
		//this.consultarPreguntas();
		//FIN PRUEBAS
//		this.crearColumnasGrid();		
//		this.presentador.consultar();
		
		this.crearFecha();
		
		$("#listaSecciones").sortable({
		    axis: "y",
		    containment: "parent",
		    cursor: "move",
		   // items: "div",
		    tolerance: "pointer",
		    update: function( event, ui ) {
		    	var seleccion = $( "#listaSecciones" ).sortable( "serialize", { key: "sort" });
				_this.presentador.ordenarSecciones(seleccion);
			}
		});
	    $( "#listaPreguntas" ).disableSelection();
		
	    var _this = this;
		$("#listaPreguntas").sortable({
		    axis: "y",
		    containment: "parent",
		    cursor: "move",
		   // items: "div",
		    tolerance: "pointer",
		    update: function( event, ui ) {
		    	var seleccion = $( "#listaPreguntas" ).sortable( "serialize", { key: "sort" });
				_this.presentador.ordenarPreguntas(seleccion);
			}
		});
	    $( "#listaPreguntas" ).disableSelection();
	    
	    $("#listaRespuestas").sortable({
		    axis: "y",
		    containment: "parent",
		    cursor: "move",
		   // items: "div",
		    tolerance: "pointer",
		});
	    $( "#listaRespuestas" ).disableSelection();
	    
		this.crearEventosActualizacion();
	}
	
	
	crearEventosActualizacion()
	{
		$("#nombreInput").change(this.cambiarCampo);
		$("#descripcionInput").change(this.cambiarCampo);
		$("#fechaProgramadaInput").change(this.cambiarCampo);
		$("#estatusRadio").change(this.cambiarCampo);
	}
	
	cambiarCampo(event)
	{
		var campo = $(event.currentTarget).attr("data-campo");
		var valor = $(event.currentTarget).val();
		if(campo=="estatus")
		{
			valor = $(event.currentTarget).is(':checked')?1:0;
		}
		
		vista.presentador.actualizarValor(campo,valor);
	}
	
	cambiarCampoPregunta(preguntaId,campo,valor)
	{
		vista.presentador.actualizarValorPregunta(preguntaId,campo,valor);
	}
	
	cambiarCampoSeccion(seccionId,campo,valor)
	{
		vista.presentador.actualizarValorSeccion(seccionId,campo,valor);
	}
	
	inicializarValidacionesFormulario(formulario)
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
                "nombreInput": {
                    required: !0
                },
                "descripcionInput": {
                    required: !0
                }
               
            },
            messages: {
                "nombreInput": "Por favor ingrese un nombre",
                "descripcionInput": "Por favor ingrese una descripci\u00f3n"
                	
                
            },
            submitHandler:function (form) {
            	 _this.guardar();
            }
        });
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
				
				$(function () {
					$("#fechaProgramadaInput").datepicker();
					});
	}
	
	crearColumnasGrid()
	{
		this.tabla._columnas = [
			{longitud:50, 	titulo:"",   	alias:"icono", alineacion:"D", itemRenderer:this.renderIcono},
			{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"D" },
			{longitud:200, 	titulo:"Nombre",   alias:"nombre", alineacion:"I" }, 		
			{longitud:300, 	titulo:"Descripción",   alias:"descripcion", alineacion:"I" }, 	
			{longitud:250, 	titulo:"Último uso",   alias:"ultimoUso", alineacion:"I" },
			{longitud:250, 	titulo:"Fecha programada",   alias:"fechaProgramada", alineacion:"I" },
			{longitud:250, 	titulo:"Fecha de alta",   alias:"fechaAlta", alineacion:"I" },	
			{longitud:200, 	titulo:"Fecha de última modificación",   alias:"fechaModificacion", alineacion:"I" },
			{longitud:100, 	titulo:"Estatus",   alias:"estatus", alineacion:"D", itemRenderer:this.renderEstatus}
		]
		
		this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Ejecutar'  type='button' class='ejecutar btn-circle mr-0 botones-icon btn btn-sm float-left btn-success active'><span  data-toggle='tooltip' class='fa fa-play-circle fa-lg'></span></button>"+
		 								"<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-edit fa-lg'></span></button>"+
		 								"<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";

		
//		this.grid._origen="vista";
//		this.grid.manejadorEventos=this.manejadorEventos;
//		this.grid._colorSeleccion = COLOR_SELECCION;
//		this.grid._ajustarAltura = true;
//		this.grid._colorRenglon1 = COLOR_RENGLON1;	
//		this.grid._colorRenglon2 = COLOR_RENGLON2;	
//		this.grid._colorEncabezado1 = COLOR_ENCABEZADO1;
//		this.grid._colorEncabezado2 = COLOR_ENCABEZADO2;
//		this.grid._colorLetraEncabezado = COLOR_LETRA_ENCABEZADO;
//		this.grid._colorLetraCuerpo = COLOR_LETRA_CUERPO;
//		this.grid._regExtra=REGISTROS_EXTRA;
//		this.grid._bordesRedondeados = true;
//		this.grid._eliminarLineaVerticales=false;
//		//this.grid._presentacionGranTotal = "SI";
//		this.grid.render();		
		this.tabla.registros = [];
	}
	
	renderIcono(renglon, campoBase)
	{    
		var fecha = new Date();
		var icono = HANDEL_API + "/php/iconos_plantillas/" + renglon.icono+"?"+fecha.getTime();
		var contenido = "";
		contenido += "<center><img src='" + icono + "' style='width:30px;height:30px;'></img></center>";
	    return contenido;
	}
	
	inicializarEventosTabla(tbody, table)
	{
		this.inicializarEventosBotonesTabla(tbody, table, ["id"]);
		var _this = this;
		$(tbody).on("click", "button.ejecutar", function()
		{			
			var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

			_this._registroSeleccionado  = table.row( tr ).data();
			if (_this._registroSeleccionado != undefined)
			{
				_this._llaves = _this.copiarPropiedadesObjeto(_this._registroSeleccionado, ["id"]);
				_this.ejecutar();
			}
		});
	}

//	agregar()
//	{
//		
//		this.modo = "ALTA";
//		this.ocultarIndicador();
//		this.limpiarFormulario();	
//		this.mostrarFormulario();
//		$('#nombreInput').focus();
//		this.inicializarValidacionesFormulario();
//		this.presentador.consultarCategorias();
//		$('#logoImage').attr("src",HANDEL_API + "/php/iconos_plantillas/default.png");
//	}
	
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
		this.mostrarFormularioHTML(HANDEL_API+"/html/formularios/plantillas.php",this, null, function()
		{
			//mostrar
			 setTimeout(function(){
					$('#nombreInputAlta').focus();
					$('#logoImageAlta').show();
					$('#logoImageAlta').attr('src', HANDEL_API + "/php/iconos_plantillas/default.png");
					$("#fechaProgramadaInputAlta").datepicker();
					_this.inicializarValidacionesFormulario("formularioAlta");
	            }, 1000);
			 
			 
			
		},null,"","","guardarButtonAlta",function()
		{
			//guardar
			$("#formularioAlta").submit();
			//_this.insertar();
			
		});
	}
	
//	btnAlta_onClick()
//	{
//		this.modo = "ALTA";
//		this.ocultarIndicador();
//		this.limpiarFormulario();	
//		this.mostrarFormulario();
//		$('#nombreInput').focus();
//		this.presentador.consultarCategorias();
//	
//		
//	}
//	
	
	set guardando(guardando)
	{
		if(guardando)
			$("#guardarButton").hide();
		else
			$("#guardarButton").show();       
	}
	
	btnGuardarFormulario_onClick()
	{		
		$("#formulario").submit();
//		 if(this.datosValidos())
//		 {
//			if(this.modo=='ALTA')
//				this.presentador.insertar();
//			else
//				this.presentador.actualizar();
//		 }		
		
		
		
	}
	
	

//	btnSalir_onClick()
//	{
//		var confirmacion = confirm("¿Esta seguro que desea salir?")
//	    if (confirmacion)
//	    	{
//		    	
//	    	}
//	}
	
	btnSalirFormulario_onClick()
	{		
		this.salirFormulario();
		this.consultar();
	}	

	mostrarFormulario()
	{
		$('#principalDiv').hide();	
		$('#formularioDiv').show();
		//$('#guardarButton').hide();
		
	}
	
	salirFormulario()
	{
		$('#principalDiv').show()	
		$('#formularioDiv').hide();
	}
	
	salirFormularioAlta()
	{
		$('#modalAlta').modal('hide')
	}

//	btnCambio_onClick()
//	{
//		if(this.grid._selectedItem!=null)
//		{			
//			this.modo = "CAMBIO";
//			this.limpiarFormulario();	
//			this.mostrarFormulario();
//			$('#nombreInput').focus();				
//			this.presentador.consultarPorLlaves();
//			
//		}
//		else
//			this.mostrarMensaje("Acción no válida","Seleccione un registro para modificar.");
//				
//	}
//	
	ejecutar()
	{
		var submitForm = getNewSubmitForm("auditoria.php","post");
		createNewFormElement(submitForm, "plantillaId", this._llaves.id);
		submitForm.target= "auditoria" + Math.floor(Math.random()*10000);
		submitForm.submit();
	}
	
//	btnConsulta_onClick()
//	{	
//		this.presentador.consultar();
//	}	
//	
//	btnGuardarFormulario_onClick()
//	{		
//		if(this.seccionEdicion!=null)
//			this.seccionEdicion.preguntas = this.listaPreguntas.preguntas;
//		 if(this.datosValidos())
//		 {
//			if(this.modo=='ALTA')
//				this.presentador.insertar();
//			else
//				this.presentador.actualizar();
//		 }		
//		
//	}
//	
//	btnSalir_onClick()
//	{
//		var confirmacion = confirm("¿Esta seguro que desea salir?")
//	    if (confirmacion)
//	    	{
//		    	
//	    	}
//	}
//	
//	btnSalirFormulario_onClick()
//	{		
//		this.salirFormulario();
//	}	
	
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

//	get llaves()
//	{
//		var llaves =
//		{
//			id:this.grid._selectedItem.id	
//		}
//		return llaves;
//	}
	
	get criteriosSeleccion()
	{
		 var criteriosSeleccion = 
		 {				    
			nombre:$('#nombreInputCriterio').val()
		 }
		 return criteriosSeleccion;
	}		

//	set datos(valor)
//	{
//		this.grid._dataProvider = valor;	
//		this.grid.render();
//	}
	
	set modelo(valor)
	{		
		this.modeloEdicion = valor;
		$('#nombreInput').val(this.modeloEdicion.nombre);
		$('#descripcionInput').val(this.modeloEdicion.descripcion);
		$('#fechaProgramadaInput').val(this.modeloEdicion.fechaProgramada);
		if(this.modeloEdicion.estatus)
			$("#estatusRadio").prop('checked', true);
		else
			$("#estatusRadio").prop('checked', false);
		$('#logoImage').attr('src', HANDEL_API + "/php/iconos_plantillas/" + this.modeloEdicion.icono);
		
		this.presentador.consultarCategorias();
	}
	
	get modelo()
	{
		 var modelo =  null;
		if(this.modo==Modo.ALTA)
		{
			 modelo = 
			 {		
				 nombre:$('#nombreInputAlta').val(),		
				 descripcion:$('#descripcionInputAlta').val(),	
				 fechaProgramada:$('#fechaProgramadaInputAlta').val(),	
				 estatus:$('#estatusRadioAlta').is(':checked')?1:0// ,
				 // secciones: this.listaSecciones.secciones
			 };
		}
		 else
		{
			 modelo = 
			 {		
				 nombre:$('#nombreInput').val(),		
				 descripcion:$('#descripcionInput').val(),	
				 fechaProgramada:$('#fechaProgramadaInput').val(),	
				 estatus:$('#estatusRadio').is(':checked')?1:0// ,
				 // secciones: this.listaSecciones.secciones
			 };
		}
		 
		
		 if(this.modo=="CAMBIO" && this.modeloEdicion!=null)
			 modelo.id = this.modeloEdicion.id;
		 return modelo;
	 }
	
	datosValidos()
	{
		var nombre = $("#nombreInput"),
			descripcion = $("#descripcionInput"),
			fechaProgramada = $("#fechaProgramadaInput");
	        
        
        var allFields = $( [] ).add(nombre).add(descripcion).add(fechaProgramada);
        var tips = $( ".validateTips" );
		tips.text("");
		
		var valid = true;
		allFields.removeClass("ui-state-error");
		
	    valid = valid && this.validaciones.checkValue( nombre, "nombre", tips );
	    valid = valid && this.validaciones.checkValue( descripcion, "descripción", tips );
	   
		return valid;
	}	

	limpiarFormulario()
	{
		$('#nombreInput').val("");
		$('#descripcionInput').val("");
		$('#fechaProgramadaInput').val("");
		$('#listaPreguntas').html("");
		$("#tituloSeccionDiv").hide();
		$("#botonesSuperiores").hide();
		$("#botonesAgregarDiv").hide();
		$("#guardarButton").hide();
		$("#ayudaPreguntas").hide();
	}
	
	agregarSeccion()
	{
		//this.listaSecciones.agregarSeccion("");
		this.presentador.insertarSeccion();
	}
	
	agregarPregunta(tipo)
	{
		this.presentador.insertarPregunta(tipo);
		//this.listaPreguntas.agregarPregunta();
	}
	
	agregarTexto()
	{
		this.listaPreguntas.agregarTexto();
	}
	
	agregarHora()
	{
		this.listaPreguntas.agregarHora();
	}
	
	agregarFoto()
	{
		this.listaPreguntas.agregarFoto();
	}
	
	agregarFecha()
	{
		this.listaPreguntas.agregarFecha();
	}
	
	agregarEncabezado()
	{
		this.listaPreguntas.agregarEncabezado();
	}
	
	agregarMapa()
	{
		this.listaPreguntas.agregarMapa();
	}
	
	agregarRespuesta()
	{
		this.listaRespuestas.agregarRespuesta();
	}
	
	eliminarSeccion(event, seccionId)
	{
		if(this.listaSecciones.secciones.length>1)
		{
			//this.listaSecciones.eliminarSeccion(seccionId);
			//this.confirmar("¿Desea eliminar esta sección?",this.listaSecciones,this.listaSecciones.eliminarSeccion,seccionId);
			var _this = this;d
			this.confirmar("¿Desea eliminar esta sección?",this,function(seccionId)
			{
				_this._llavesSeccion = {plantillaId : _this.plantillaId, seccionId: seccionId};
				_this.eliminarSeccionBaseDatos();
				
			},seccionId);
		}
		else
			this.mostrarMensajeAdvertencia("Error","Es necesario contar al menos con una sección. ");		
	}
	
	eliminarPregunta(event, preguntaId)
	{
		//this.confirmar("¿Desea eliminar esta pregunta?",this.listaPreguntas,this.listaPreguntas.eliminarPregunta,preguntaId);
		var _this = this;
		this.confirmar("¿Desea eliminar esta pregunta?",this,function(preguntaId)
		{
			_this._llavesPregunta = {plantillaId : _this.plantillaId, seccionId: _this.seccionIdSeleccionada, preguntaId : preguntaId};
			_this.eliminarPreguntaBaseDatos();
			
		},preguntaId);
	}
	
	get llavesPregunta()
	{
		return this._llavesPregunta;
	}
	
	get llavesSeccion()
	{
		return this._llavesSeccion;
	}
	
	
	eliminarPreguntaBaseDatos()
	{
		this.presentador.eliminarPregunta();
	}
	
	eliminarSeccionBaseDatos()
	{
		this.presentador.eliminarSeccion();
	}
	
	eliminarRespuesta(event, respuestaId)
	{
		this.confirmar("¿Desea eliminar esta respuesta?",this.listaRespuestas,this.listaRespuestas.eliminarRespuesta,respuestaId);
		//this.listaRespuestas.eliminarRespuesta(respuestaId);
	}
	
	
//	
	confirmar(textoDialogo,contexto,funcion,parametro)
	{
		var _this = this;
		swal({
	            title: "",
	            text: textoDialogo,
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
	            	 setTimeout(function()
	            			 {
	            			funcion.call(contexto,parametro);
	            			swal.close();
	 	            }, 1000);
	            }
	        });
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
		this.listaPreguntas.categorias = this._categorias;
		if(seccion!=null)
			this.listaPreguntas.preguntas = seccion.preguntas;
		else
			this.listaPreguntas.preguntas = [];
		
		this._seccionIdSeleccionada = seccion;
	}
	
	get seccionIdSeleccionada()
	{
		return this._seccionIdSeleccionada.id;
	}
	
	get plantillaId()
	{
		return this._llaves.id; 
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
		this.listaRespuestas.categoriasPregunta = this.preguntaEdicion.categorias;
		this.listaRespuestas.categorias = this._categorias;
		this.listaRespuestas.respuestas = this.preguntaEdicion.respuestas_si;
		
		this._preguntaIdSeleccionada = preguntaId;
	}
	
	editarRespuestasNo(preguntaId)
	{
		$("#tituloRespuestas").val("Respuestas No");
		$('#ventanaRespuestasContenedor').data( "tipo", "NO" );
		$('#ventanaRespuestasContenedor').fadeIn( this.velocidadAnimacion );
		this.preguntaEdicion = this.listaPreguntas.getPregunta(preguntaId);
		this.listaRespuestas.categorias = this._categorias;
		this.listaRespuestas.respuestas = this.preguntaEdicion.respuestas_no;
		
		this._preguntaIdSeleccionada = preguntaId;
	}
	
	get preguntaIdSeleccionada()
	{
		return this._preguntaIdSeleccionada;
	}
	
	guardar()
	{		
		if(this.seccionEdicion!=null)
			this.seccionEdicion.preguntas = this.listaPreguntas.preguntas;
		if(this.presentador!=null)
		{
			if(this.modo==Modo.ALTA)
				this.presentador.insertar();
			else
				this.presentador.actualizar();
		}
	}
	
	cerrarRespuestas()
	{
		$('#ventanaRespuestasContenedor').fadeOut(this.velocidadAnimacion);
	}
	
	guardarRespuestas()
	{
		if(this.preguntaEdicion!=null)
		{
			var tipo = $('#ventanaRespuestasContenedor').data("tipo");
			if(tipo=="SI")
			{
				this.preguntaEdicion.respuestas_si = this.listaRespuestas.respuestas;
				var peso = this.listaPreguntas.getPeso(this.preguntaEdicion.id);
				//if(peso==0)
				//{
					this.listaPreguntas.setPeso(this.preguntaEdicion.id,this.preguntaEdicion.respuestas_si.length+1);
				//}
				if(this.modo==Modo.CAMBIO)
					this.presentador.guardarRespuestasSi();
			}
			else
			{
				this.preguntaEdicion.respuestas_no = this.listaRespuestas.respuestas;
				if(this.modo==Modo.CAMBIO)
					this.presentador.guardarRespuestasNo();
			}
		}
			
			
		$('#ventanaRespuestasContenedor').fadeOut(this.velocidadAnimacion);
	}
	
	get respuestas()
	{
		return this.listaRespuestas.respuestas;
	}
	
	editar(id)
	{
		this.modo = "CAMBIO";
		this.limpiarFormulario();	
		this.mostrarFormulario();
		$('#nombreInput').focus();				
		this.inicializarValidacionesFormulario("formulario");
		this.presentador.consultarPorLlaves();
		//this._plantillaId =id;
	}
	
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
	
	cambiarLogo(input)
	{
		if (input.files && input.files[0]) 
		{
            var reader = new FileReader();

            reader.onload = function (e)
            {
                $('#logoImage').attr('src', e.target.result);
            };
            reader.readAsDataURL(input.files[0]);
        }
	}
	
	cambiarLogoAlta(input)
	{
		if (input.files && input.files[0]) 
		{
            var reader = new FileReader();

            reader.onload = function (e)
            {
                $('#logoImageAlta').attr('src', e.target.result);
            };
            reader.readAsDataURL(input.files[0]);
        }
	}
	
	get logo()
	{
		var contenedorArchivos = $("#file") ;
		if(contenedorArchivos.length>0)
		{
			if(contenedorArchivos[0].files.length>0)
				return contenedorArchivos[0].files[0];
		}
		return null;	
		
	}
	
	get logoAlta()
	{
		var contenedorArchivos = $("#fileAlta") ;
		if(contenedorArchivos.length>0)
		{
			if(contenedorArchivos[0].files.length>0)
				return contenedorArchivos[0].files[0];
		}
		return null;	
		
	}
	
	
}
var vista = new PlantillasVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});
