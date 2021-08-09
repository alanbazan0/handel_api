class CapacitacionesVista extends CatalogoVista
{		
	constructor(ventana)
	{	
		super();
		this.ventana = ventana;
		this.presentador = new CapacitacionesPresentador(this);

		this.listaLecciones = new ListaLecciones("listaLecciones");
		
		this.listaPreguntas = new ListaPreguntas("listaPreguntas");
		this.listaPreguntas.contexto = this; 
		this.listaPreguntas.funcionCambiarCampo = this.cambiarCampoPregunta;
		
		this.listaRespuestas = new ListaRespuestas("listaRespuestas");
		this._categorias = [];
		this._estandares = [];
		this.preguntaEdicion = null;
		this._leccionSeleccionada = null;
		this.velocidadAnimacion = 400;
	}
	
	inicializar()
	{	
		var _this = this;
		$("#consultarButton").click(function(){
			_this.consultar();
		});
		
		$("#agregarButton").click(function(){
			_this.agregar();
		});
		
		
		this.crearColumnasGrid();		
		
		
		this._tarjetas = new Tarjetas("tarjetas");

		
		this._tarjetas.plantillaHtml =  `<div id='capacitacion_{{id}}' class='col-xs-12 col-sm-12 col-md-6 col-lg-3 '>
		<div class="box {{box}}"  stylee='height:150px' >
            <div class="box-header with-border">
              <h5 class='truncate' style='font-weight:bold'>{{titulo}}</h5>

              <div class="box-tools pull-right">
                <div class="btn-group">
                   <button type="button"  data-id='{{id}}' class="editar btn btn-box-tool"><i class="fas fa-cog text-blue"></i></button>
                   <button type="button" data-id='{{id}}' data-titulo="{{titulo}}" class="eliminar btn btn-box-tool"><i class="fa fa-times text-red"></i></button>
                </div>
                
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
            	<div class='row'>
            		<div class='col-md-4 col-lg-4 col-xs-4 m-l-1'>
            			<img src='`+HANDEL_API+`/php/portadas_cursos/{{portada}}' style='height:80px;width: 100%;object-fit:cover'></img>
            		</div>
            		<div class='col-md-8 col-lg-8 col-xs-8' style="padding-left:0px">
            			<div style='height:90px;' class='text-justify descripcion' ><span>{{descripcion}}</span></div>
            		</div>
             	</div>
             	<div style='font-size:12px;color:#c0c0c0;' >Creado por {{usuarioNombreCompleto}}</div>
            	<div style='font-size:12px;color:#c0c0c0;' >Última modificación: {{fechaModificacion}}
            	
            			<div class="btn-group pull-right">
                   
                   <button type="button" data-token="{{token}}" data-titulo="Nueva capacitacion" class="ejecutar btn btn-box-tool"><i class="fa fa-play text-green"></i></button>
                </div>
            	</div>
            	
            </div>
            <!-- ./box-body -->
          
            <!-- /.box-footer -->
          </div>
          <!-- /.box -->
           </div>
         `;
	

		//this.crearFecha();
		
		$("#listaLecciones").sortable({
		    axis: "y",
		    containment: "parent",
		    cursor: "move",
		   // items: "div",
		    tolerance: "pointer",
		    update: function( event, ui ) {
		    	var seleccion = $( "#listaLecciones" ).sortable( "serialize", { key: "sort" });
				_this.presentador.ordenarLecciones(seleccion);
			}
		});
	    $( "#listaLecciones" ).disableSelection();
		
//	    var _this = this;
//		$("#listaPreguntas").sortable({
//		    axis: "y",
//		    containment: "parent",
//		    cursor: "move",
//		   // items: "div",
//		    tolerance: "pointer",
//		    update: function( event, ui ) {
//		    	var seleccion = $( "#listaPreguntas" ).sortable( "serialize", { key: "sort" });
//				_this.presentador.ordenarPreguntas(seleccion);
//			}
//		});
//	    $( "#listaPreguntas" ).disableSelection();
//	    
//	    $("#listaRespuestas").sortable({
//		    axis: "y",
//		    containment: "parent",
//		    cursor: "move",
//		   // items: "div",
//		    tolerance: "pointer",
//		});
//	    $( "#listaRespuestas" ).disableSelection();
	    
	    this.sliderTiempoEstimado = $( "#tiempoEstimadoLeccionInput" ).bootstrapSlider();
	    
		this.crearEventosActualizacion();
		
		$("#tituloH").click(function()
		{
			_this.salirFormulario();
		});
		
		$("#perfilesSelect").chosen();
		
		$("#tarjetas").sortable({
		    containment: "parent",
		    cursor: "move",
		    //items: ">div",
		    tolerance: "pointer",
		    update: function( event, ui ) {
		    	var seleccion = $( "#tarjetas" ).sortable( "serialize", { key: "sort" });
				_this.presentador.ordenarCursos(seleccion);
			}
		});
	    $( "#tarjetas" ).disableSelection();
		
	    
	   
	    
	    
	    this.consultar();
	
	}
	
	
	crearEventosActualizacion()
	{
		var _this = this;
		$("#tituloInput").change(this.cambiarCampo);
		$("#tituloInput").keyup(function()
		{
			$("#tituloH").html($("#tituloInput").val());
		});
		$("#descripcionInput").change(this.cambiarCampo);
		$("#publicadoRadio").change(this.cambiarCampo);
		
		
		$("#tituloLeccionInput").change(this.cambiarCampoLeccion);
		$("#descripcionLeccionInput").change(this.cambiarCampoLeccion);
		$("#urlVideoLeccionInput").change(this.cambiarCampoLeccion);
		$("#tituloLeccionInput").keyup(function()
		{
			if(_this._leccionSeleccionada!=null)
			{
				_this._leccionSeleccionada.titulo = $("#tituloLeccionInput").val();
				_this.listaLecciones.actualizarTitulos();
			}
		});
		$("#perfilesSelect").change(this.cambiarCampo);
		
		this.sliderOriginalVal=null;
		
		this.sliderTiempoEstimado.on("slideStart",function(ev){
			this.sliderOriginalVal= _this.sliderTiempoEstimado.bootstrapSlider('getValue');
		});
		this.sliderTiempoEstimado.on("slideStop",function(ev){
			var valor  = _this.sliderTiempoEstimado.bootstrapSlider('getValue');
			if(_this.sliderOriginalVal!= valor)
			{
				var leccionId = _this._leccionSeleccionada.id;
				_this._leccionSeleccionada.tiempoEstimado = valor;
				_this.presentador.actualizarValorLeccion(leccionId,"tiempo_estimado",valor);
			}
		});
		

		
	}
	
	cambiarCampo(event)
	{
		var campo = $(event.currentTarget).attr("data-campo");
		if(campo=="perfiles")
		{
			vista.presentador.actualizarPerfiles();
		}
		else
		{
			//var valor = $(event.currentTarget).val();
			 var valor = $(event.currentTarget).is(':checked')?1:0;
			/*if(campo=="publicado")
			{
				if(valor=="on" || valor=="1")
					valor=1;
				else
					valor=0;
			}*/
			vista.presentador.actualizarValor(campo,valor);
		}
	}
	
	cambiarCampoLeccion(event)
	{
		var campo = $(event.currentTarget).attr("data-campo");
		var valor = $(event.currentTarget).val();
		vista._leccionSeleccionada[campo] = valor;
		var leccionId = vista._leccionSeleccionada.id;
		vista.presentador.actualizarValorLeccion(leccionId,campo,valor);
	}
	
	cambiarCampoPregunta(preguntaId,campo,valor)
	{
		vista.presentador.actualizarValorPregunta(preguntaId,campo,valor);
	}
	
	cambiarCampoRespuesta(preguntaId,respuestaId,campo,valor)
	{
		vista.presentador.actualizarValorRespuesta(preguntaId,respuestaId,campo,valor);
	}
	
	cambiarCampoRespuestaCorrecta(preguntaId,respuestaId,valor)
	{
		//vista.listaPreguntas.seleccionarRespuestaCorrecta(preguntaId,respuestaId);
		vista.presentador.actualizarValorRespuestaCorrecta(preguntaId,respuestaId,valor);
	}
	
	cambiarCategoriasPregunta(preguntaId,categorias)
	{
		vista.presentador.actualizarCategoriasPregunta(preguntaId,categorias);
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
                "tituloInput": {
                    required: !0
                },
                "descripcionInput": {
                    required: !0
                }
               
            },
            messages: {
                "tituloInput": "Por favor ingrese un t\u00edtulo",
                "descripcionInput": "Por favor ingrese una descripci\u00f3n"
                	
                
            },
            submitHandler:function (form) {
            	 _this.guardar();
            }
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
                },
                "descripcionInputAlta": {
                    required: !0
                }
               
            },
            messages: {
                "tituloInputAlta": "Por favor ingrese un t\u00edtulo",
                "descripcionInputAlta": "Por favor ingrese una descripci\u00f3n"
                	
                
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
	
	renderPublicado(renglon, type, set)
	{    
		var contenido = "";
		if(renglon.publicado==1)
			contenido += "<center><span class='fa fa-check fa-lg text-success'></span></center>";
		else
			contenido += "<center><span class='fa fa-times fa-lg text-danger'></span></center>";
	    return contenido;
	}
	
	crearColumnasGrid()
	{
		this.tabla._columnas = [
			{longitud:50, 	titulo:"",   	alias:"portada", alineacion:"D", itemRenderer:this.renderPortada},
			{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"D" },
			{longitud:200, 	titulo:"Título",   alias:"titulo", alineacion:"I" }, 		
			{longitud:300, 	titulo:"Descripción",   alias:"descripcion", alineacion:"I" }, 	
			{longitud:250, 	titulo:"Fecha de alta",   alias:"fechaAlta", alineacion:"I" },	
			{longitud:200, 	titulo:"Fecha de última modificación",   alias:"fechaModificacion", alineacion:"I" },
			{longitud:100, 	titulo:"Publicado",   alias:"publicado", alineacion:"D", itemRenderer:this.renderPublicado}
		]
		
		this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Vista previa'  type='button' class='ejecutar btn-circle mr-0 botones-icon btn btn-sm float-left btn-success active'><span  data-toggle='tooltip' class='fa fa-play-circle fa-lg'></span></button>"+
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
	
	renderPortada(renglon, campoBase)
	{    
		var fecha = new Date();
		var icono = HANDEL_API + "/php/portadas_cursos/" + renglon.portada+"?"+fecha.getTime();
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
		this.mostrarFormularioHTML(HANDEL_API+"/html/formularios/capacitaciones.php",this, null, function()
		{
			//mostrar
			 setTimeout(function(){
					$('#tituloInputAlta').focus();
					$('#logoImageAlta').show();
					$('#logoImageAlta').attr('src', HANDEL_API + "/php/portadas_cursos/default.png");
					_this.inicializarValidacionesFormularioAlta("formularioAlta");
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
		$('#contenidoFormularioDiv').hide();
		//$('#guardarButton').hide();
		
	}
	
	salirFormulario()
	{
		$('#principalDiv').show()	
		$('#formularioDiv').hide();
		$('#contenidoFormularioDiv').hide();
		this.consultar();
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
//	ejecutar()
//	{
//		var submitForm = getNewSubmitForm("auditoria.php","get");
//		createNewFormElement(submitForm, "cursoId", this._llaves.id);
//		submitForm.target= "auditoria" + Math.floor(Math.random()*10000);
//		submitForm.submit();
//	}
	
	
	set perfiles(perfiles)
	{
		this._perfiles = perfiles;
		this.cargarOpciones('#perfilesSelect', perfiles,"",null, null, null);
		
		
		var perfilesSeleccionados =[];
		if(this.modeloEdicion.perfiles!=undefined)
		{
			$.each(this.modeloEdicion.perfiles, function(i, p) 
			{
				perfilesSeleccionados.push(p.perfilId);
			});
		}
		
		
		$("#perfilesSelect").val(perfilesSeleccionados);
		 $('#perfilesSelect').trigger("chosen:updated");
		 //$("#perfilesSelect").chosen();
		$(".chosen-search-input").height(50);
		$(".chosen-search-input").val("");
		
		$("#perfilesSelect_chosen").css("width","100%");
		
		if(this.modo=="CAMBIO" && this.modeloEdicion!=null)
		{
			this.listaLecciones.lecciones= this.modeloEdicion.lecciones;
			if(this.listaLecciones.lecciones!=null)
				if(this.listaLecciones.lecciones.length>0)
				{
					this._leccionSeleccionada =  this.modeloEdicion.lecciones[0];
					this.mostrarLeccion(this._leccionSeleccionada);
				}
		}
		else
		{
		}
		$('#contenidoFormularioDiv').show();
	}
	
	get perfiles()
	{
		var perfiles=[];
		var perfilesSeleccionados  = $("#perfilesSelect").val();
		if(perfilesSeleccionados !=undefined)
		{
			for(var i = 0; i < perfilesSeleccionados.length ; i++)
			{
				var perfilSeleccionado = perfilesSeleccionados[i];
				var perfil = new Object();
				perfil.id = i + 1;
				perfil.perfilId = perfilSeleccionado;
				perfiles.push(perfil);
			}
		}
		return perfiles;
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
			titulo:$('#tituloInputCriterio').val()
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
		$('#tituloH').html(this.modeloEdicion.titulo);
		$('#tituloInput').val(this.modeloEdicion.titulo);
		$('#descripcionInput').val(this.modeloEdicion.descripcion);
		if(this.modeloEdicion.publicado)
			$("#publicadoRadio").prop('checked', true);
		else
			$("#publicadoRadio").prop('checked', false);
		$('#logoImage').attr('src', HANDEL_API + "/php/portadas_cursos/" + this.modeloEdicion.portada);
		$("#logoImage").show();
		this.presentador.consultarPerfiles();
		
		
	}
	
	get modelo()
	{
		 var modelo =  null;
		if(this.modo==Modo.ALTA)
		{
			 modelo = 
			 {		
				 titulo:$('#tituloInputAlta').val(),		
				 descripcion:$('#descripcionInputAlta').val(),	
			 };
		}
		 else
		{
			 modelo = 
			 {		
			     titulo:$('#tituloInput').val(),		
				 descripcion:$('#descripcionInput').val(),	
				 publicado:$('#publicadoRadio').is(':checked')?1:0// ,
			 };
		}
		 
		
		 if(this.modo=="CAMBIO" && this.modeloEdicion!=null)
			 modelo.id = this.modeloEdicion.id;
		 return modelo;
	 }
	
	set datos(datos)
	{
		var fecha = new Date();
		for (var i = 0; i < datos.length; i++) 
			datos[i].portada+="?" + fecha.getTime();
		
		this._tarjetas.registros = datos;
		$('.dropdown-toggle').dropdown();
		
//		new Dotdotdot( document.querySelector( '.descripcion' ), {
//
//		});
		
		var elementList = document.querySelectorAll('.descripcion' );
		for (var i = 0; i < elementList.length; i++) 
			new Dotdotdot( elementList[i], {});

		
		
		this.inicializarEventos();
	}
	
	inicializarEventos()
	{
		var _this = this;
		$("#tarjetas").on("click", "button.eliminar", function(e)
		{			
			var current = e.currentTarget;
			_this._llaves ={id: $(current).attr("data-id")};
			var titulo = $(current).attr("data-titulo");
			
			_this.confirmar("¿Desea eliminar esta capacitaci\u00f3n?</br></br><label>" +titulo +"</label>",this,function(cursoId)
			{
				//_this.eliminarLeccionBaseDatos();
				 _this.presentador.eliminar();
				
			},_this._llaves.id,true);
			
			//_this.eliminar("¡¡Se eliminar\u00e1 esta capacitaci\u00f3n!!\n" + titulo);
		});	
		
		$("#tarjetas").off("click", "button.editar", this.editarCapacitacion);	
		$("#tarjetas").on("click", "button.editar", this, this.editarCapacitacion);	
		
		$("#tarjetas").off("click", "button.ejecutar", this.ejecutarCapacitacion);	
		$("#tarjetas").on("click", "button.ejecutar", this, this.ejecutarCapacitacion);	
	}
	
	ejecutarCapacitacion(e)
	{
		var current = e.currentTarget;
		var token =$(current).attr("data-token");
		e.data.ejecutar(token);
	}
	
	ejecutar(token)
	{
		var submitForm = getNewSubmitForm("capacitacion.php");
		createNewFormElement(submitForm, "token", token);
		createNewFormElement(submitForm, "modo", Modo.VISTA_PREVIA);
		createNewFormElement(submitForm, "pnt", "capacitaciones.php");
		submitForm.method = "get"
		submitForm.target= "_self";
		submitForm.submit();
	}
	
	editarCapacitacion(e)
	{
		var current = e.currentTarget;
		e.data._llaves ={id: $(current).attr("data-id")};
		e.data.editar();
	}

	limpiarFormulario()
	{
		$('#formulario').trigger("reset");
		$('#formularioLeccion').trigger("reset");
		
	}
	
	agregarLeccion()
	{
		this.presentador.insertarLeccion();
	}
	
	agregarPregunta(tipo)
	{
		var tipo = "om";
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
	
	agregarRespuesta(preguntaId)
	{
		this.presentador.insertarRespuesta(preguntaId);
		//this.listaPreguntas.agregarRespuesta(preguntaId);
	}
	
	enfocarRespuesta(preguntaId, respuestaId)
	{
		this.listaPreguntas.enfocarRespuesta(preguntaId,respuestaId);
	}
	
	eliminarLeccion(event, leccionId)
	{
		if(this.listaLecciones.lecciones.length>1)
		{
			var componenteLeccion = this.listaLecciones.getComponente(leccionId);
			if(componenteLeccion!=null)
			{
				var _this = this;
				this.confirmar("¿Desea eliminar esta lección?</br></br><label>" +componenteLeccion.titulo +"</label>",this,function(leccionId)
				{
					_this._llavesLeccion = {cursoId : _this.cursoId, leccionId: leccionId};
					_this.eliminarLeccionBaseDatos();
					
				},leccionId,true);
			}
		}
		else
			this.mostrarMensajeAdvertencia("Error","Es necesario contar al menos con una lección. ");		
	}
	
	eliminarPregunta(event, preguntaId)
	{
		var componentePregunta = this.listaPreguntas.getComponente(preguntaId);
		if(componentePregunta!=null)
		{
			var _this = this;
			this.confirmar("¿Desea eliminar esta pregunta?</br></br><label>" +componentePregunta.texto +"</label>" ,this,function(preguntaId)
			{
				_this._llavesPregunta = {cursoId : _this.cursoId, leccionId: _this.leccionIdSeleccionada, preguntaId : preguntaId};
				_this.eliminarPreguntaBaseDatos();
				
			},preguntaId,true);
		}
	}
	
	eliminarRespuesta(event, preguntaId, respuestaId)
	{
		var componenteRespuesta = this.listaPreguntas.getComponenteRespuesta(preguntaId,respuestaId);
		if(componenteRespuesta!=null)
		{
			var _this = this;
			this.confirmar("¿Desea eliminar esta respuesta?</br></br><label>" +componenteRespuesta.texto +"</label>",this,function(respuestaId)
			{
				_this._llavesRespuesta = {cursoId : _this.cursoId, leccionId: _this.leccionIdSeleccionada, preguntaId : preguntaId, respuestaId: respuestaId};
				_this.eliminarRespuestaBaseDatos();
				
			},respuestaId,true);
		}
		
	}
	
	get llavesPregunta()
	{
		return this._llavesPregunta;
	}
	
	get llavesRespuesta()
	{
		return this._llavesRespuesta;
	}
	
	get llavesLeccion()
	{
		return this._llavesLeccion;
	}
	
	
	eliminarPreguntaBaseDatos()
	{
		this.presentador.eliminarPregunta();
	}
	
	eliminarRespuestaBaseDatos()
	{
		this.presentador.eliminarRespuesta();
	}
	
	
	eliminarLeccionBaseDatos()
	{
		this.presentador.eliminarLeccion();
	}
	
	

	
	
//	
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
	
	seleccionarLeccion(event, leccionId)
	{
		this._leccionSeleccionada.preguntas = this.listaPreguntas.preguntas;
		this._leccionSeleccionada = this.listaLecciones.getLeccion(leccionId);
		if(this._leccionSeleccionada!=null)
		{
			this.mostrarLeccion(this._leccionSeleccionada);
			
		}
		$("#tituloLeccionInput").focus();
	}
	
	mostrarLeccion(leccion)
	{
		this.listaLecciones.seleccionar(leccion.id);
		
		$("#tituloLeccionInput").val(leccion.titulo);
		$("#descripcionLeccionInput").val(leccion.descripcion);
		$("#urlVideoLeccionInput").val(leccion.video);
		
		this.sliderOriginalVal= leccion.tiempoEstimado;
		this.sliderTiempoEstimado.bootstrapSlider('setValue', leccion.tiempoEstimado);
		
		this.listaPreguntas.categorias = this._categorias;
		if(leccion!=null)
			this.listaPreguntas.preguntas = leccion.preguntas;
		else
			this.listaPreguntas.preguntas = [];
		
		this.actualizarNumeroPreguntas();
		
		this._leccionIdSeleccionada = leccion.id;
		this._leccionSeleccionada = leccion;
	}
	
	get leccionIdSeleccionada()
	{
		return this._leccionSeleccionada.id;
	}
	
	get cursoId()
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
		if(this._leccionSeleccionada!=null)
			this._leccionSeleccionada.preguntas = this.listaPreguntas.preguntas;
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
		$('#tituloInput').focus();				
		//this.inicializarValidacionesFormulario("formulario");
		this.presentador.consultarPorLlaves();
		//this._cursoId =id;
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
		vista.presentador.actualizarLogo();
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
	
	eliminarCapacitacion(id)
	{

	    $("#capacitacion_"+id).slideUp(500, function () {
	      //$(this.element).trigger(removedEvent);
	    	$("#capacitacion_"+id).remove();
	    });
	    
		//$("#capacitacion"+id).remove();
	}
	
	agregarPreguntaLista(tipo,valor)
	{
		this.listaPreguntas.agregar(tipo,valor);
		this.actualizarNumeroPreguntas();
	}
	
	actualizarNumeroPreguntas()
	{
		$("#numeroPreguntasH").html(this.listaPreguntas.preguntas.length);
	}
	
	async eliminarPreguntaLista(preguntaId)
	{
		await this.listaPreguntas.eliminarPregunta(preguntaId);
		this.actualizarNumeroPreguntas();
	}
	
}
var vista = new CapacitacionesVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});
