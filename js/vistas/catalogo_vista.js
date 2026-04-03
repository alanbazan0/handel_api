class CatalogoVista extends Vista
{
	constructor() 
	{
		super();
		this.presentador = null;
		this._llaves = null;
		this.modo = Modo.ALTA;
		this.tabla = new Tabla("tabla");	
		this.modeloActual=null;
		this._urlFormulario = "";
		var fecha = new Date();
		this._time = fecha.getTime();
		this._inicioTemporadaConfigurada = false;
	}
	
	get time()
	{
		return this._time;
	}
	
	inicializar(consultarTabla)
	{
		super.inicializar();
		
	
		
		this.inicializarFechas();
				
		var _this = this;
		$("#consultarButton").click(function(){
			_this.consultar();
		});
		
		$("#agregarButton").click(function(){
			_this.agregar();
		});
		
		
		
		this.crearColumnasGrid();	
		
		if(consultarTabla==undefined)
			consultarTabla = true;
		
		if(consultarTabla)	
			this.consultar();
			
		this.consultarLeccionesReprobadas();
	}
	
	
	
	inicializarFechas()
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
				 
		try
		{
				 
			$.fn.datepicker.dates['es'] = {
				days: ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"],
				daysShort: ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"],
				daysMin: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
				months: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
				monthsShort: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
				today: "Hoy",
				monthsTitle: "Meses",
				clear: "Borrar",
				weekStart: 1,
				format: "dd/mm/yyyy"
			};
		}
		catch(e)
		{
			
		}	
		
		$.datepicker.setDefaults($.datepicker.regional['es']);
	}
	
	formatoFecha(fecha)
	{
		if(fecha!=undefined)
		{
			var elementos = fecha.split("/");
			if(elementos.length == 3)
			{
				var formato = elementos[2] + "-" + elementos[1] + "-" + elementos[0];
				return formato;
			}
		}
		return "";
	}
	
	editar()
	{
		this.modo = Modo.CAMBIO;
		this.mostrarFormulario();
	}
	
	agregar()
	{
		this.modo = Modo.ALTA;
		this.ocultarIndicador();
		this.mostrarFormulario();
	}

	set datos(datos)
	{
		this.tabla.registros = datos;	
		this.inicializarEventosTabla("#" + this.tabla._id+"Table tbody",this.tabla.datatable.DataTable());
	}
	
	inicializarEventosTabla(tbody, table)
	{
		this.inicializarEventosBotonesTabla(tbody, table, ["id"]);
	}
	
	inicializarEventosBotonesTabla(tbody, table, nombresCamposLlave)
	{
		var _this = this;
		$(tbody).on("click", "button.editar", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }
		    

			_this._registroSeleccionado  = table.row( tr ).data();
			if (_this._registroSeleccionado != undefined)
			{
				_this._llaves = _this.copiarPropiedadesObjeto(_this._registroSeleccionado, ["id"]);
				_this.editar();
			}
		});

		$(tbody).on("click", "button.eliminar", function()
		{
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

		    _this._registroSeleccionado  = table.row( tr ).data();
			
			
			if (_this._registroSeleccionado != undefined)
			{
				_this._llaves = _this.copiarPropiedadesObjeto(_this._registroSeleccionado, ["id"]);
				_this.eliminar();
			}
		});
		
	}
	
	copiarPropiedadesObjeto(objeto, propiedades)
	{
		var copia = new Object();
		for (var  i  =  0; i  < propiedades.length; i++) 
		{  	
			var propiedad = propiedades[i];
			if(propiedad in objeto )
				copia[propiedad] = objeto[propiedad];
		}
		return copia;
	}
	
	get criteriosSeleccion()
	{
		 var criteriosSeleccion = 
		 {				    
			nombre:$('#nombreInputCriterio').val()
		 }
		 return criteriosSeleccion;
	}		
	
	consultar()
	{	
		
		this.consultarNumeroMensajesNoLeidos();
		this.consultarTareasPendientes();
		
		if(this.presentador!=null)
		{
			$("#tablaTabla_processing").show();
			this.presentador.consultar();
		}
	}	
	
	consultarNumeroMensajesNoLeidos()
	{
		if($("#mensajesLi").length>0)
		{
			this.presentador.consultarNumeroMensajesNoLeidos();
		}
	}
	
	consultarTareasPendientes()
	{
		if($("#tareasPendientesSpan").length>0)
		{
			this.presentador.consultarTareasPendientes();
		}
	}
	
	consultarLeccionesReprobadas()
	{
		if($("#leccionesReprobadasLi").length>0)
		{
			this.presentador.consultarLeccionesReprobadas();
		}
	}
	
	set tareasPendientes(tareasPendientes)
	{
		if(tareasPendientes.length>0)
		{
			$("#tareasPendientesSpan").html(tareasPendientes.length);
			
			var html="";
			
			if(tareasPendientes.length==1)
			{
				html+="<li class='header'>"+tareasPendientes.length+" tarea pendiente</li>"+
					 "	<li>"+
					"	<ul class='menu'>";
			}
			else
			{
				html+="<li class='header'>"+tareasPendientes.length+" tareas pendientes</li>"+
					 "	<li>"+
					"	<ul class='menu'>";
			}
		
			
			for(var i=0; i < tareasPendientes.length; i++)
			{
				var tarea = tareasPendientes[i];
				var id = "tareaLink"+tarea.minutaId+"_"+tarea.id;
				html+="	  <li>"+
				"		<a id='"+id+"' href='#'>";
				
				
//				if(procedimiento.usuarioId == this.usuario.id)
//					html+="<i class='fa fa-upload text-aqua'></i> ";
				
				
				html+=tarea.titulo +
				"		</a>"+
				"	  </li>";
			}
			
			
			
			html+="	</ul>"+
			"	</li>";
			
			html+=" <li class='footer'><a href='minutas.php'>Ver todas</a></li>";
			
			$("#tareasPendientesUl").html(html);	
			
			
			for(var i=0; i < tareasPendientes.length; i++)
			{
				var tarea = tareasPendientes[i];
				var id = "tareaLink"+tarea.minutaId+"_"+tarea.id;
				$("#"+id).data("tarea",tarea);
				$("#"+id).data("_this",this);
				$("#"+id).click(this.tareaClick);
			}
			
		}
		else
		{
			$("#tareasPendientesSpan").html("");
			
			var html="";
			html+="<li class='header'>No hay tareas pendientes</li>"+
				 "	<li>";
			$("#tareasPendientesUl").html(html);	
		}
	}
	
	set numeroMensajesNoLeidos(numeroMensajesNoLeidos)
	{
		if(numeroMensajesNoLeidos>0)
		{
			var html = "<small id='mensajesSmall' class='label pull-right bg-yellow'>"+numeroMensajesNoLeidos+"</small>";
			$("#mensajesNotificacionSpan").html(html)
			
			$("#notificacionMensajesSpan").html(numeroMensajesNoLeidos)
			
			var html="";
			if(numeroMensajesNoLeidos==1)
			{
				html+="<li class='header'>"+
						"<a id='mensajesLink' href='#'>" +
						"Tienes "+numeroMensajesNoLeidos+" mensaje "+
						"</a>" +
						"</li>";
			}
			else
			{
				html+="<li class='header'>Tienes "+numeroMensajesNoLeidos+" mensajes</li>";
			}
			
			html+=" <li class='footer'><a href='mensajes.php'>Ver todos</a></li>";
			
			$("#notificacionMensajesUl").html(html);	
			
			$("#mensajesLink").click(this.mensajesClick);
			
		}
		else
		{
			$("#mensajesNotificacionSpan").html("");
			$("#notificacionMensajesSpan").html("")
		}
	}
	
	mensajesClick(event)
	{
		var _this = $("body").data("_this");
		var url = "mensajes.php";
		var submitForm = _this.getNewSubmitForm(url);
	    submitForm.target= "_self";
	    submitForm.submit();
	}
	
	tareaClick(event)
	{
		//var _this = $("body").data("_this");
		var tarea = $(event.currentTarget).data("tarea");
		var url = "minutas.php?id="+tarea.minutaId+"_"+tarea.id;
		var submitForm = vista.getNewSubmitForm(url);
	    submitForm.target= "_self";
	    submitForm.submit();
	}
	
	
	crearColumnasGrid()
	{
		
	}
	
	eliminar(texto)
	{ 
		if(texto==undefined)
			texto ="Se eliminar\u00e1 este registro !!";
		var _this = this;
		swal({
	            title: "\u00bfEst\u00E1 seguro de eliminar?",
	            text: texto,
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
	
	
	
	get llaves()
	{
		return this._llaves;
	}
	
	
	renderEstatus(renglon, type, set)
	{    
		var contenido = "";
		if(renglon.estatus==1)
			contenido += "<center><span class='fa fa-check fa-lg text-success'></span></center>";
		else
			contenido += "<center><span class='fa fa-times fa-lg text-danger'></span></center>";
	    return contenido;
	}
	
	mostrarFormulario()
	{
		if($("#modalAlta").length ==0)
		{
			this.renderizarFormulario();
		}
		else
		{
			$("#modalAlta").modal({backdrop: 'static', keyboard: false});
			
		}
	}
	
	consultarCombos()
	{
		
	}
	
	renderizarFormulario()
	{
		var url = HANDEL_API + "/" + this._urlFormulario;
		this.mostrarIndicador();
		var _this = this;
		$.post(url,{}, function(html) 
		{
			_this.ocultarIndicador();
			$("body").append(html);
			$("#modalAlta").on("hidden.bs.modal", function () {
				$("#modalAlta").remove();
			});
			
			$("#modalAlta").on("shown.bs.modal", function () {
				$('#nombreInput').focus();
				if(_this.modo == Modo.CAMBIO)
				{	
					if(_this.presentador!=null)
						_this.presentador.consultarPorLlaves();
				}
				else
					_this.consultarCombos();
			});
		
			
			_this.inicializarValidacionesFormulario();
			
			$("#logoImage").attr("src",HANDEL_API + "/php/logos_empresas/default.png")
			
			$("#guardarButton").click(function () {
				 $("#formulario").submit();
			});
			
			$("#modalAlta").modal({backdrop: 'static', keyboard: false});
		});
	}
	
	inicializarValidacionesFormulario()
	{
		
	}
	
	set guardando(guardando)
	{
		if(guardando)
		{
			$("#guardarButton").attr("disabled",true);
			$("#guardarButtonAlta").attr("disabled",true);
		}
		else
		{
			$("#guardarButton").attr("disabled",false);
			$("#guardarButtonAlta").attr("disabled",false);
			
		}
	}
		
	salirFormulario()
	{
		$('#modalAlta').modal('hide')
	}
	
	datosValidos()
	{
		return true;
	}
	
	guardar()
	{		
		if(this.presentador!=null)
		{
			if(this.modo==Modo.ALTA)
				this.presentador.insertar();
			else
				this.presentador.actualizar();
		}
	}
	
	crearFechas(fechaInicioTemporada)
	{
		var _this = this;
		moment.locale('es') ;
		
		
		var start = moment().subtract(1, 'years');
		var end = moment();	
		
		/*
		
		 ranges   : {
		          'Histórico'       : ["01/08/2020", moment()],
		          'Este año'   : [moment().startOf('year'),, moment()],
		          'Ultimo año'   : [moment().subtract(1, 'year'), moment()],
		          'Ultimo semestre' : [moment().subtract(6, 'month'), moment()],
		          'Ultimo trimestre': [moment().subtract(3, 'month'), moment()],
		          'Este mes'  : [moment().startOf('month'), moment().endOf('month')],
		          'Mes pasado'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
		
		*/

		
		    
	    var ranges = {
	          'Histórico'       : ["01/08/2020", moment()],
	          'Este año'   : [moment().startOf('year'), moment()],
	          'Ultimo año'   : [moment().subtract(1, 'year'), moment()],
	          'Ultimo semestre' : [moment().subtract(6, 'month'), moment()],
	          'Ultimo trimestre': [moment().subtract(3, 'month'), moment()],
	          'Este mes'  : [moment().startOf('month'), moment().endOf('month')],
	          'Mes pasado'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
	        };
	        
	   //	const fecha = moment(fechaInicioTemporada, "DD/MM-YYYY", true);
	     
	    if(fechaInicioTemporada!=null && fechaInicioTemporada!="" && fechaInicioTemporada!="00/00/0000")
	    //if(fecha.isValid())
	    {
			this._inicioTemporadaConfigurada = true;
			start = moment(fechaInicioTemporada, "DD-MM-YYYY");
			end = moment();	
			
			ranges = {
			  'Esta temporada' : [fechaInicioTemporada, moment()],	
	          'Histórico'       : [moment("01/08/2020", "DD-MM-YYYY"), moment()],
	           'Este año'   : [moment().startOf('year'),, moment()],
	          'Ultimo año'   : [moment().subtract(1, 'year'), moment()],
	          'Ultimo semestre' : [moment().subtract(6, 'month'), moment()],
	          'Ultimo trimestre': [moment().subtract(3, 'month'), moment()],
	          'Este mes'  : [moment().startOf('month'), moment().endOf('month')],
	          'Mes pasado'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
	        };
			
		}
		else
		{
			this._inicioTemporadaConfigurada = false;
			start = moment().subtract(1, 'years');
			end = moment();	
			
		  	ranges = {
			  'Esta temporada' : [moment().startOf('year'), moment()],	
	          'Histórico'       : [moment("01/08/2020", "DD-MM-YYYY"), moment()],
	            'Este año'   : [moment().startOf('year'),, moment()],
	          'Ultimo año'   : [moment().subtract(1, 'year'), moment()],
	          'Ultimo semestre' : [moment().subtract(6, 'month'), moment()],
	          'Ultimo trimestre': [moment().subtract(3, 'month'), moment()],
	          'Este mes'  : [moment().startOf('month'), moment().endOf('month')],
	          'Mes pasado'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
	        };
		}
	    	

		$('#daterange-btn').daterangepicker(
	      {
		// drops: 'up',
			drops: 'auto',
			//opens: 'center',
	        ranges   : ranges,
	        startDate: start,
	        endDate  : end,
			locale: {
			    "customRangeLabel": "Rango",
				"cancelLabel" : "Cancelar"
			  },
	      },
	      cb
	    );
		 
		 function cb(start, end) {
			 
			 
		
			 
			_this._fechaInicial = start.format('DD/MM/YYYY');
			_this._fechaFinal = end.format('DD/MM/YYYY');
	       	$('#daterange-btn span').html(start.format('D MMMM YYYY') + ' - ' + end.format('D MMMM YYYY'))
	       	
	       	if(this!=null)
	       	{
		       	var title = this.chosenLabel;
		       	if(title == "Esta temporada"  && !_this._inicioTemporadaConfigurada)
		       		_this.mostrarMensajeAdvertencia("Advertencia","No se ha definido una fecha de inicio de temporada, vea con su especialista asignado en Handel para que la registre, se mostrarara del 1 de Enero a la fecha");
       		}
   			//_this.title =  $("div.ranges").find("li.active").html();
	    }
	     
		cb(start,end);
				
	}
	
	set leccionesReprobadas(lecciones)
	{
		if(lecciones.length>0)
		{
			//$("#leccionesReprobadasNumeroSpan").html(lecciones.length);
			//$("#leccionesReprobadasMensajeLi").html("Tienes " +lecciones.length +" notificaciones");
			
			var html = "";
			
			for(var i = 0; i < lecciones.length; i++)
			{
				html += new Notificacion().renderizar( lecciones[i]);
			}
			
			$("#leccionesReprobadasUl").html(html);
			
		}
	}
	
	set notificacionesNoLeidas(notificacionesNoLeidas)
	{
		if(notificacionesNoLeidas>0)
		{
			$("#leccionesReprobadasNumeroSpan").html(notificacionesNoLeidas);
			//$("#leccionesReprobadasMensajeLi").html("Tienes " +notificacionesNoLeidas +" notificaciones");
		}	
		else
		{
			$("#leccionesReprobadasNumeroSpan").html("");
			//$("#leccionesReprobadasMensajeLi").html("Tienes " +notificacionesNoLeidas +" notificaciones");
		}	
	}
	
	leerNotificaciones()
	{
		var html = $("#leccionesReprobadasNumeroSpan").html();
		if(html!="")
			this.presentador.leerNotificaciones();
	}
	
	set inicioTemporadaEmpresa(valor)
	{
		//if(valor!="")
		this.crearFechas(valor);
	}
	
	
}
