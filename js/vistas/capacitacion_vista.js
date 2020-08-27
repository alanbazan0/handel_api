class CapacitacionVista extends CatalogoVista
{		
	constructor(ventana)
	{	
		super();
		this.ventana = ventana;
		this.presentador = new CapacitacionPresentador(this);

		this.listaLecciones = new ListaLeccionesEjecucion("listaLecciones");
		
		this.listaPreguntas = new ListaPreguntasEjecucion("listaPreguntas");
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
		this.modo =  $("body").attr("data-modo");
		var _this = this;
		this.actualizarSesion();	
		$("#tituloH").click(function()
		{
			_this.salirFormulario();
		});
		this.consultarCursoId();
		//super.inicializar();
		
		this.crearVideo();
	
		
		
	
	}
	
	crearVideo()
	{
		var _this = this;
		var options = { controlBar: {
	        CurrentTimeDisplay: true,
	        DurationDisplay: true
	    }};
		this._player = videojs('video', options, function onPlayerReady() {
			  videojs.log('Your player is ready!');
			  
			  $(".vjs-big-play-button").off('click',_this.clickPlay);
			  $(".vjs-big-play-button").on('click',_this,_this.clickPlay);
			  $(".vjs-progress-holder").hide();
			  //$(".vjs-remaining-time").hide();
			 
			  this.on('ended', function() 
			  {
				  _this.consultarPreguntaAleatoria();
			  });
			  
			  	var myPlayer = this;
			  	
		  	 myPlayer.on("loadedmetadata", function(event) {
			    	var duracion = parseInt(myPlayer.duration());
			    	_this.actualizarDuracionLeccion(duracion);
			    });
		  	 
			    myPlayer.on("timeupdate", function(event) {
			    	var remainingTime = myPlayer.remainingTime();
			    	var minutes = Math.floor(remainingTime / 60);   
			    	var seconds = Math.floor(remainingTime - minutes * 60);
			    	var x = minutes;
			    	var y = seconds < 10 ? "0" + seconds : seconds;
			    	$(".vjs-remaining-time").html("-"+ x + ":" + y);
				    });
			    

			    var currentTime = 0;

			    myPlayer.on("seeking", function(event) {
			      if (currentTime != myPlayer.currentTime()) {
			        myPlayer.currentTime(currentTime);
			      }
			    });

			    myPlayer.on("seeked", function(event) {
			      if (currentTime != myPlayer.currentTime()) {
			        myPlayer.currentTime(currentTime);
			      }
			    });
			    

			    setInterval(function() {
			      if (!myPlayer.paused()) {
			        currentTime = myPlayer.currentTime();
			      }
			    }, 1000);
			  
			});
		
		$('.video-js').bind('contextmenu',function() { return false; });
		
		 $('.vjs-control-bar').css("display","none");
		
		$('.full-vid').hover(function() {
			 $('.vjs-control-bar').attr("style","");
	    },function() {
	    	 $('.vjs-control-bar').css("display","none");
	    });
		
		
	}
	
	
//	mostrarCapacitaciones()
//	{
//		this._tarjetas = new Tarjetas("tarjetas");
//
//		
//		this._tarjetas.plantillaHtml =  `<div id='capacitacion{{id}}' class='col-xs-12 col-sm-12 col-md-6 col-lg-3 '>
//		<div class="box {{box}}"  stylee='height:150px' >
//            <div class="box-header with-border">
//              <h5 class='truncate' style='font-weight:bold'>{{titulo}}</h5>
//
//              <div class="box-tools pull-right">
//                <div class="btn-group">
//                   <button type="button"  data-id='{{id}}' class="editar btn btn-box-tool"><i class="fas fa-cog text-blue"></i></button>
//                   <button type="button" data-id='{{id}}' data-titulo="{{titulo}}" class="eliminar btn btn-box-tool"><i class="fa fa-times text-red"></i></button>
//                </div>
//                
//              </div>
//            </div>
//            <!-- /.box-header -->
//            <div class="box-body">
//            	<div class='row'>
//            		<div class='col-md-4 col-lg-4 col-xs-4 m-l-1'>
//            			<img src='`+HANDEL_API+`/php/portadas_cursos/{{portada}}' style='height:80px;width: 100%;object-fit:cover'></img>
//            		</div>
//            		<div class='col-md-8 col-lg-8 col-xs-8' style="padding-left:0px">
//            			<div style='height:90px;' class='text-justify descripcion' ><span>{{descripcion}}</span></div>
//            		</div>
//             	</div>
//             	<div style='font-size:12px;color:#c0c0c0;' >Creado por {{usuarioNombreCompleto}}</div>
//            	<div style='font-size:12px;color:#c0c0c0;' >Última modificación: {{fechaModificacion}}
//            	
//            			<div class="btn-group pull-right">
//                   
//                   <button type="button" data-id="15" data-titulo="Nueva capacitacion" class="ejecutar btn btn-box-tool"><i class="fa fa-play text-green"></i></button>
//                </div>
//            	</div>
//            	
//            </div>
//            <!-- ./box-body -->
//          
//            <!-- /.box-footer -->
//          </div>
//          <!-- /.box -->
//           </div>
//         `;
//	
//
////		
////		$("#listaLecciones").sortable({
////		    axis: "y",
////		    containment: "parent",
////		    cursor: "move",
////		   // items: "div",
////		    tolerance: "pointer",
////		    update: function( event, ui ) {
////		    	var seleccion = $( "#listaLecciones" ).sortable( "serialize", { key: "sort" });
////				_this.presentador.ordenarLecciones(seleccion);
////			}
////		});
////	    $( "#listaLecciones" ).disableSelection();
//		
//
//		$("#tituloH").click(function()
//		{
//			_this.salirFormulario();
//		});
//	}
	
	consultarCursoId()
	{
		var tokenEdicion = $("body").attr("data-tokenEdicion");
		if(tokenEdicion !=undefined &&  tokenEdicion!="")
			this.presentador.consultarToken();
		else
		{
			var token = $("body").attr("data-token");
			if(token!=undefined && token!="")
				this.token =  token;
			else
			{
				this.mostrarCapacitacionInvalida();
			}
		}
			
	}
	

	
	mostrarCapacitacionInvalida()
	{
		var _this = this;
		swal({
            title: "Algo salió mal",
            text: "Ocurrió un error al intentar ingresar a la capacitación.",
            type: "warning",
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Salir",
            closeOnConfirm: false,
            closeOnCancel: true,
            showLoaderOnConfirm: true,
        },
        function(isConfirm)
        {
            if (isConfirm) 
            {
            	 setTimeout(function(){
            		 _this.salirFormulario();
 	            }, 1000);
            }
        });
	}
	
	set token(token)
	{
		this._token = token;
		
		if(this.modo=="VP")
			this.presentador.consultarPorToken();
		else
			this.presentador.consultarPorTokenSinPreguntas();
	}
	
	get token()
	{
		return this._token;
	}
	

	
	set guardando(guardando)
	{
		if(guardando)
		{
			$("#guardarButton").hide();
			$("#siguienteButton").hide();
			$("#finalizarButton").hide();
		}
		else
		{
			$("#guardarButton").show();
			$("#siguienteButton").show();
			$("#finalizarButton").show();
			
		}
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
		var pantalla = $("body").attr("data-pantalla");
		if(pantalla!=null)
		{
			var submitForm = getNewSubmitForm(pantalla);
			submitForm.method = "get"
			submitForm.target= "_self";
			submitForm.submit();	
		}
	
	}
	
//	salirFormularioAlta()
//	{
//		$('#modalAlta').modal('hide')
//	}

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
//		var submitForm = getNewSubmitForm("auditoria.php","post");
//		createNewFormElement(submitForm, "cursoId", this._llaves.id);
//		submitForm.target= "auditoria" + Math.floor(Math.random()*10000);
//		submitForm.submit();
//	}
	
//	
//	set perfiles(perfiles)
//	{
//		this._perfiles = perfiles;
//		this.cargarOpciones('#perfilesSelect', perfiles,"",null, null, null);
//		
//		
//		var perfilesSeleccionados =[];
//		if(this.modeloEdicion.perfiles!=undefined)
//		{
//			$.each(this.modeloEdicion.perfiles, function(i, p) 
//			{
//				perfilesSeleccionados.push(p.perfilId);
//			});
//		}
//		
//		$("#perfilesSelect").val(perfilesSeleccionados);
//		$("#perfilesSelect").chosen();
//		$(".chosen-search-input").height(50);
//		$(".chosen-search-input").val("");
//		
//		$("#perfilesSelect_chosen").css("width","100%");
//		
//		if(this.modeloEdicion!=null)
//		{
//			this.listaLecciones.lecciones= this.modeloEdicion.lecciones;
//			if(this.listaLecciones.lecciones!=null)
//				if(this.listaLecciones.lecciones.length>0)
//				{
//					this._leccionSeleccionada =  this.modeloEdicion.lecciones[0];
//					this.mostrarLeccion(this._leccionSeleccionada);
//				}
//		}
//		else
//		{
//		}
//		$('#contenidoFormularioDiv').show();
//	}
//	
//	get perfiles()
//	{
//		var perfiles=[];
//		var perfilesSeleccionados  = $("#perfilesSelect").val();
//		if(perfilesSeleccionados !=undefined)
//		{
//			for(var i = 0; i < perfilesSeleccionados.length ; i++)
//			{
//				var perfilSeleccionado = perfilesSeleccionados[i];
//				var perfil = new Object();
//				perfil.id = i + 1;
//				perfil.perfilId = perfilSeleccionado;
//				perfiles.push(perfil);
//			}
//		}
//		return perfiles;
//	}
//	

//	get llaves()
//	{
//		var llaves =
//		{
//			id:this.grid._selectedItem.id	
//		}
//		return llaves;
//	}
	
//	get criteriosSeleccion()
//	{
//		 var criteriosSeleccion = 
//		 {				    
//			titulo:$('#tituloInputCriterio').val()
//		 }
//		 return criteriosSeleccion;
//	}		

//	set datos(valor)
//	{
//		this.grid._dataProvider = valor;	
//		this.grid.render();
//	}
	
	
	set modelo(valor)
	{		
		this.mostrarFormulario();
		
		
		this.modeloEdicion = valor;
		
		var titulo = this.modeloEdicion.titulo;
		if(this.modo==Modo.VISTA_PREVIA)
		{
			$('#tituloVistraPreviaH').html("¡VISTA PRELIMINAR!");
		}
		
		
		$('#tituloH').html(titulo);
		$('#tituloInput').val(this.modeloEdicion.titulo);
		var title = $(document).prop('title');
		$(document).prop('title', title + " " + this.modeloEdicion.titulo);
		
		var descripcion = this.modeloEdicion.descripcion;
		descripcion = descripcion.replace(/\r?\n/g, '<br />');
		
		$('#descripcionLabel').html(descripcion);
//		if(this.modeloEdicion.publicado)
//			$("#publicadoRadio").prop('checked', true);
//		else
//			$("#publicadoRadio").prop('checked', false);
		$('#logoImage').attr('src', HANDEL_API + "/php/portadas_cursos/" + this.modeloEdicion.portada);
		$("#logoImage").show();
//		this.presentador.consultarPerfiles();
		
		if(this.modeloEdicion!=null)
		{
			this.listaLecciones.modo = this.modo;
			this.listaLecciones.lecciones= this.modeloEdicion.lecciones;
			if(this.listaLecciones.lecciones!=null)
				if(this.listaLecciones.lecciones.length>0)
				{
					this._leccionSeleccionada =  this.getLeccionSinCompletar(this.modeloEdicion.lecciones);
					if(this._leccionSeleccionada==null)
						this._leccionSeleccionada= this.modeloEdicion.lecciones[0];
					this.mostrarLeccion(this._leccionSeleccionada);
				}
				else
				{
					 $("#ayudaLecciones").html(this.textoAyudaLecciones);
				}
		}
		else
		{
			
		}
		$('#contenidoFormularioDiv').show();
		
		
	}
	
	getLeccionSinCompletar(lecciones)
	{
		for(var i=0; i < lecciones.length; i++)
		{
			var leccion = lecciones[i];
			if(leccion.terminado!=1)
				return leccion;
		}
		return null;
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

	limpiarFormulario()
	{
		$('#formulario').trigger("reset");
		$('#formularioLeccion').trigger("reset");
		
	}


	seleccionarLeccion(event, leccionId)
	{
		this._leccionSeleccionada.preguntas = this.listaPreguntas.preguntas;
		this._leccionSeleccionada = this.listaLecciones.getLeccion(leccionId);
		if(this._leccionSeleccionada!=null)
		{
			this.mostrarLeccion(this._leccionSeleccionada);
			
		}
		//$("#tituloLeccionInput").focus();
	}
	
	mostrarLeccion(leccion)
	{
		if(leccion!=null)
		{
			$("#preguntasDiv").hide();
			if(leccion.id!=this._leccionIdSeleccionada)
			{
				
			
				this.listaLecciones.seleccionar(leccion.id);
				
				$("#tituloLeccionLabel").html(leccion.titulo);
				
				var descripcion = leccion.descripcion;
				if(descripcion!=null)
					descripcion = descripcion.replace(/\r?\n/g, '<br />');
				else
					descripcion ="";
				
				$("#descripcionLeccionLabel").html(descripcion);
				$("#tiempoEstimadoLeccionLabel").html("Esta lección y su cuestionario le tomará aproximadamente "+leccion.tiempoEstimado+ " minutos");
				
				
				
				 $("#ayudaVideo").html("");
				this.listaPreguntas.mostrarPregunta();
				
				var _this = this;
				
				 if(leccion.video!=null)
				 {
					 $("#ayudaVideo").html("");
					 this._player.show();
					  _this.vsgLoadVideo(this._player,leccion.video);
				 }
				 else
				 {
					 //var html = "";
					
				    $("#ayudaVideo").html(this.textoAyudaVideo);
					 this._player.hide();
				 }
			
				this._leccionIdSeleccionada = leccion.id;
				this._leccionSeleccionada = leccion;
				
				if(this.modo==Modo.VISTA_PREVIA)
				{
	//				if(leccion.terminado==1)
	//					 $(".vjs-big-play-button").hide();
	//				else
	//					 $(".vjs-big-play-button").show();
					
					$("#divBotonesPreguntas").html("");
					this.consultarPreguntaAleatoria();
					
				}
				else
				{
					if(leccion.terminado==1)
				    {
						 var html = this.textoLeccionTerminada;
						 $("#preguntasDiv").show();
						 $("#divBotonesPreguntas").html(html);
				    }
					else
						$("#divBotonesPreguntas").html("");
				}
				
			}
		}
	}
	
	clickPlay(event)
	{
	
		 event.data.guardarLeccionUsuario();
	}
	
	guardarLeccionUsuario()
	{
		if(this.modo!=Modo.VISTA_PREVIA)
			this.presentador.guardarLeccionUsuario();
	}
	
	consultarPreguntaAleatoria()
	{
		this.presentador.consultarPreguntaAleatoria(this._leccionIdSeleccionada);
	}
	
	actualizarDuracionLeccion(duracion)
	{
		this.presentador.actualizarDuracionLeccion(this._leccionIdSeleccionada,duracion);
	}
	
	mostrarPregunta(leccionId, pregunta, numeroPreguntasRestantes,numeroPreguntasContestadas)
	{
		$("#preguntasDiv").fadeIn(this.velocidadAnimacion);
		
		this.listaPreguntas.mostrarPregunta(pregunta,numeroPreguntasRestantes);

		 var html="";
		 $("#ayudaPreguntas").html("");
		 if(numeroPreguntasRestantes==0 && numeroPreguntasContestadas==0)
		 {
			 if(this.modo==Modo.VISTA_PREVIA) 
				 html+="<span class='text-danger' style='font-size:16px'>No olvides agregar preguntas a la lección</span>"
			 else
				html+="<span class='text-danger' style='font-size:16px'>No hay preguntas disponibles en la lección</span>"
		 }
		 else
		 {
			if(pregunta!=null)
			{
				$("#ayudaPreguntas").html(this.textoAyudaPreguntas);
				var disabled = ""
				if(this.modo==Modo.VISTA_PREVIA) 
					 disabled = "disabled";
				 if(numeroPreguntasRestantes>1)
					 html+="<button id='siguienteButton' type='button' class='btn btn-primary' style='float:right' onclick='vista.siguiente();' "+disabled+" >Siguiente</button>";
				 else
					 html+="<button id='finalizarButton' type='button' class='btn btn-success'  style='float:right'  onclick='vista.finalizar();' "+disabled+" >Finalizar</button>";
			}
			else
			{
				this.listaLecciones.terminarLeccion(leccionId);
				html+=this.textoLeccionTerminada;
			}
		 }
		
		 $("#divBotonesPreguntas").html(html);
	}

	get textoLeccionTerminada()
	{
		//return "<span class='text-green' style='font-size:16px'>¡Lección completa! Nos vemos en la siguiente lección "+this.usuario.nombre+". ¡Buena suerte!</span>"
		this._leccionSeleccionada =  this.getLeccionSinCompletar(this.modeloEdicion.lecciones);
		var texto="";
		if(this._leccionSeleccionada!=null)
			texto = "lección";
		else
			texto = "capacitación";
			
		var html="";
		html="<div class='text-center;' style='background-color:#154D3A;width:100%'>";
		html+="<h3 style='padding:10px;color:white;font-size:17px;text-align:center'>¡Lección completa! Nos vemos en la siguiente "+ texto+" "+this.usuario.nombre+". ¡Buena suerte!</h3>";
		html+="</div>";
		html+="<div class='text-center;' style='width:100%'>";
		html+="<button id='finalizarCapacitacionButton' type='button' class='btn btn-success'   style='float:right'  onclick='vista.salirFormulario();' ><i class='fas fa-undo'></i> Regresar al menú de capacitación</button>";
		if(this._leccionSeleccionada!=null)
			html+="<button id='siguienteLeccionButton' type='button' class='btn btn-primary'  style='float:right;margin-right:5px;' onclick='vista.siguienteLeccion();'><i class='fas fa-arrow-right'></i> Ir a la siguiente lección</button>";
		html+="</div>";
		return html;
	}
	
	siguienteLeccion()
	{
		this._leccionSeleccionada =  this.getLeccionSinCompletar(this.modeloEdicion.lecciones);
		this.mostrarLeccion(this._leccionSeleccionada);
	}
	
	get textoAyudaVideo()
	{
		var html ="";
		 if(this.modo==Modo.VISTA_PREVIA) 
			 html+="<span class='text-red' style='font-size:16px'>No olvides agregar un video a la lección</span>";
		 else
			html+="<span class='text-red' style='font-size:16px'>No hay video disponible en la lección</span>";
		return html;
				
	}
	
	get textoAyudaPreguntas()
	{
		var html ="";
		html="<div class='text-center;' style='background-color:#F27200;width:100%'>";
		html+="<h3 style='padding:10px;color:white;font-size:17px'>Para completar tu lección responde a estas preguntas</h3>";
		html+="</div>";
		return html;
				
	}
	
	
	get textoAyudaLecciones()
	{
		var html ="";
		 if(this.modo==Modo.VISTA_PREVIA) 
			 html+="<span class='text-red' style='font-size:16px'>No olvides agregar lecciones a la capacitación</span>";
		 else
			html+="<span class='text-red' style='font-size:16px'>No hay lecciones disponibles en la capacitación</span>";
		return html;
				
	}
	
	ytVidId(url) {
		  //var p = /^(?:https?:\/\/)?(?:www\.)?youtube\.com\/watch\?(?=.*v=((\w|-){11}))(?:\S+)?$/;
		  var p = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
		  return (url.match(p)) ? RegExp.$1 : false;
		}

		/**/
	getId(url) {
		  var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
		  var match = url.match(regExp);

		  if (match && match[2].length == 11) {
		    return match[2];
		  } else {
		    return 'error';
		  }
		}
	
	vsgLoadVideo(vgsPlayer,vidURL, poster) {

		  if (this.ytVidId(vidURL) !== false) {
		    ext = "youtube"
		    console.log('Youtube');

		    // alert(getId(vidURL)) // Youtube video ID
		    var yvID = this.getId(vidURL);
		    vidURL = "https://www.youtube.com/watch?v="+yvID;
		    
		   // vgsPlayer.src({ "techOrder": ["youtube"], "sources": [{ "type": "video/youtube", "src": "https://www.youtube.com/watch?v=iRusbYIyRNI"}] });

		  } else {

		    //$("#vid1 iframe, #vid1 .vjs-iframe-blocker").remove();

		    if (!ext) ext = "mp4";
		    var ext = vidURL.split('.').pop();
		    
		   
		  }
		  
		  vgsPlayer.src({
			    //"techOrder": ['youtube'],
			    "type": "video/" + ext,
			    "src": vidURL
					//"youtube": { "iv_load_policy": 3 }
			  });

		  console.log(ext);

		 
		  if (poster) vgsPlayer.poster(poster);
		  //vgsPlayer.play();

		}
	
	get leccionIdSeleccionada()
	{
		return this._leccionSeleccionada.id;
	}
	
	get cursoId()
	{
		return this.modeloEdicion.id;
	}
	

	get preguntaIdSeleccionada()
	{
		return this._preguntaIdSeleccionada;
	}
	siguiente()
	{
		this.funcion = "siguente";
		this.guardar();
	}
	
	anterior()
	{
		this.funcion = "anterior";
		this.guardar();
	}
	

	finalizar()
	{
		this.funcion = "finalizar";
		this.guardar();
	}
	
	guardar()
	{
		console.log("guardando...");
		var preguntaActual = this.listaPreguntas.preguntaActual;
		if(preguntaActual.respuestaId!=null && preguntaActual.respuestaId!="")
		{
			this.presentador.guardarPreguntaUsuario(preguntaActual.registro.id,preguntaActual.respuestaId);
		}
		else
		{
			var _this = this;
		  swal({
	            title: "Para continuar",
	            text: "Seleccione una respuesta: </br>",
	            html: true,
	            type: "warning",
	            confirmButtonColor: "#32C2CD",
	            confirmButtonText: "Cerrar",
	            closeOnConfirm: true
	        },
	        function(){
	        });
		}
	}
	
	
	
	
}
var vista = new CapacitacionVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});
