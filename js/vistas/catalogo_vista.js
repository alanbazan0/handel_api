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
		var _this = this;
		$("#consultarButton").click(function(){
			_this.consultar();
		});
		
		$("#agregarButton").click(function(){
			_this.agregar();
		});
		
		
		this.crearColumnasGrid();		
		this.presentador.consultar();
	}
//	
//	inicializarEliminar()
//	{
//		$('#modalEliminar').on('click', '.btn-danger', function(e) 
//		{
//			var presentador= $('#modalEliminar').find('#buttonEliminar').data('presentador');
//			$('#modalEliminar').modal('hide')
//			presentador.eliminar();
//			  
//		});
//	}

	
	editar(id)
	{
		this._llaves =
		{
			id:id	
		};			
		this.modo = Modo.CAMBIO;
		this.limpiarFormulario();	
		this.mostrarFormulario();
		if(this.presentador!=null)
			this.presentador.consultarPorLlaves();
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
		if(this.presentador!=null)
			this.presentador.consultar();
	}	
	
	crearColumnasGrid()
	{
		
	}
	
	eliminar()
	{ 
		var _this = this;
//		 swal({
//	            title: "Confirmación",
//	            text: "¿Esta seguro que desea eliminar el registro?",
//	            type: "warning",
//	            html :true,
//	            showCancelButton: true,
//	            confirmButtonColor: "#ae3e9e",
//	            cancelButtonText  : "Cancelar",
//	            confirmButtonText: "Si, eliminar !!",
//	            closeOnConfirm: true
//	        },
//	        function()
//	        {
//	        	_this.presentador.eliminar();
//	        });
		 swal({
	            title: "\u00bfEst\u00E1 seguro de eliminar?",
	            text: "Se eliminar\u00e1 este registro !!",
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
	
	cerrarConfirmacionEliminar()
	{
		swal.close();
	}
	
	get llaves()
	{
		return this._llaves;
	}
	
	
//	renderEstatus(renglon, campoBase)
//	{    
//		var contenido = "";
//		if(renglon.estatus==1)
//			contenido += "<span class='status--process'>Activo</span>";
//		else
//			contenido += "<span class='status--denied'>Inactivo</span>";
//	    return contenido;
//	}
	
	renderEstatus(renglon, type, set)
	{    
		var contenido = "";
		if(renglon.estatus==1)
			contenido += "<center><span class='fa fa-check fa-lg text-success'></span></center>";
		else
			contenido += "<center><span class='fa fa-close fa-lg text-danger'></span></center>";
	    return contenido;
	}
	
	
	 
	mostrarFormulario()
	{
		if($("#modalAlta").length ==0)
		{
			this.renderizarFormulario();
		}
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
	
	cargandoOpciones(select)
	{
		$(select).empty();
		$(select).append('<option value="">Cargando...</option>');

	}
	
	cargarOpciones(select, registros, modo, modeloEdicion, campo, texto)
	{
		$(select).empty();
		if(texto!=null)
		{
			if(texto=="")
				$(select).append($('<option></option>').val("").html("-Seleccione"));
			else 
				$(select).append($('<option></option>').val("").html(texto));
		}
		$.each(registros, function(i, p) 
		{
		    $(select).append($('<option></option>').val(p.id).html(p.nombre));
		});
		if(modo==Modo.CAMBIO && modeloEdicion!=null)
		{
			var id = modeloEdicion[campo];
			$(select).val(id);
		}
	}

}
