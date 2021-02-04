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
	}
	
	inicializar()
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
		this.consultar();
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
		
		$.datepicker.setDefaults($.datepicker.regional['es']);
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
			
			$("#modalAlta").on("show.bs.modal", function () {
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
	
	

	
}
