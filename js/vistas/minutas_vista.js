class MinutasVista extends CatalogoVista
{		
	constructor()
	{	
		super();
		this.presentador = new MinutasPresentador(this);
		this._urlFormulario = "html/formularios/minutas.php";
		
		this.listaTareas = new ListaTareas("listaTareas");
	}
	
	inicializar()
	{
		var _this = this;
		super.inicializar();
		$("#tituloH").click(function()
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
	    
//	    $("#agregarButton").click(function()
//				{
//					alert('Esta función esta en desarrollo');
//				});
	}
	
	crearEventosActualizacion()
	{
		var _this = this;
		$("#tituloInput").change(this.cambiarCampo);
		$("#tituloInput").keyup(function()
		{
			$("#tituloH").html($("#tituloInput").val());
		});
		
	}
	
	cambiarCampo(event)
	{
		var campo = $(event.currentTarget).attr("data-campo");
		var valor = $(event.currentTarget).val();
		vista.presentador.actualizarValor(campo,valor);
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
			{longitud:200, 	titulo:"Titulo",   alias:"titulo", alineacion:"I" },
			{longitud:50, 	titulo:"Avance",   alias:"titulo", alineacion:"C", itemRenderer: this.rendererPorcentaje },
			{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"D" ,itemRenderer:this.renderLogo},
			{longitud:200, 	titulo:"Usuario que creó" ,   alias:"usuarioNombreCompleto", alineacion:"I",class: "desc" }, 
			{longitud:250, 	titulo:"Fecha de alta",   alias:"fechaAlta", alineacion:"I" },	
			{longitud:200, 	titulo:"Fecha de última modificación",   alias:"fechaModificacion", alineacion:"I" },
			{longitud:100, 	titulo:"Fecha de finalización",   alias:"fechaFinalizacion", alineacion:"I"}
		]
		
		this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-edit fa-lg'></span></button>"+
		"<button data-toggle='tooltip' data-placemen='bottom' t¡itle='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";

		this.tabla.registros = [];
	}
	
	renderLogo(renglon, type, set)
	{    
		var fecha = new Date();
		var contenido = "";
		var icono = HANDEL_API+ "/"+renglon.fotoPerfil+"?"+fecha.getTime();
		contenido += "<center><img src='" + icono + "' style='width:30px;height:30px;border-radius:50%'></img></center>";
	    return contenido;
	}
	
	rendererPorcentaje(renglon, type, set)
	{    
		//if(renglon.fechaUltimaCapacitacion!=null)
		//{
			if(renglon.porcentaje==undefined)
				renglon.porcentaje = 0;
		
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
			return "<span style='font-weight:bold' class='"+label+"'>"+porcentajeCumplimiento+"%</span>";
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
	

	
	get criteriosSeleccion()
	{
		 var criteriosSeleccion = 
		 {				    
			titulo:$('#tituloInputCriterio').val()
		 }
		 return criteriosSeleccion;
	}		

	set modelo(valor)
	{		
		this.modeloEdicion = valor;
		$('#tituloH').html(this.modeloEdicion.titulo);
		$('#tituloInput').val(this.modeloEdicion.titulo);
		$("input[name=estatus][value=" + this.modeloEdicion.estatus + "]").prop('checked', true);
		
		if(this.modo=="CAMBIO" && this.modeloEdicion!=null)
		{
			this.listaTareas.tareas= this.modeloEdicion.tareas;
		}
		else
		{
			
		}
		
		$('#tareasSectionContenido').fadeIn();	
		
		//this.consultarEmpresas();
	}
	
	get modelo()
	{
		 var modelo = 
		 {		
			 titulo:$('#tituloInputAlta').val(),			 
			 estatus:$('#estatusRadio').is(':checked')?1:0
		 };
		 if(this.modo=="CAMBIO" && this.modeloEdicion!=null)
			 modelo.id = this.modeloEdicion.id;
		 return modelo;
	 }
	 


	limpiarFormulario()
	{
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
		this.consultar();
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
	
	cambiarCampoTarea(tareaId,campo,valor)
	{
		vista.presentador.actualizarValorTarea(tareaId,campo,valor);
	}
	
	eliminarTarea(event, tareaId)
	{
		var _this = this;
		var componenteTarea = this.listaTareas.getComponente(tareaId);
		if(componenteTarea!=null)
		{
			var _this = this;
			this.confirmar("¿Desea eliminar esta tarea?</br></br><label>" +componenteTarea.titulo +"</label>",this,function(tareaId)
			{
				_this._llavesTarea = {minutaId : _this.minutaId, tareaId: tareaId};
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
		this.presentador.insertarTarea();
	}
	
}
var vista = new MinutasVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});

