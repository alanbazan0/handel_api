class UsuariosProcedimientosVista extends CatalogoVista
{		
	constructor(ventana)
	{	
		super(ventana);
		this.presentador = new UsuariosProcedimientosPresentador(this);
		this._urlFormulario = "html/formularios/usuarios_procedimientos.php";
	}
	
	inicializar()
	{
		super.inicializar();
		
		
		this.consultarEmpresasCriterio();
	}
	
	cambiarLimitarJustificaciones()
	{
		var limitar = $('#limitarJustificacionesSwitch').is(':checked')?1:0;
		if(limitar)
			$("#limiteJustificacionesGroup").fadeIn();
		else
			$("#limiteJustificacionesGroup").fadeOut();
			
	}
	
	crearColumnasGrid()
	{
		this.tabla.columnas = [
			{longitud:200, 	titulo:"Usuario",   alias:"usuarioNombre", alineacion:"I", class: "desc" }, 
			{longitud:200, 	titulo:"Procedimiento",   alias:"nombre", alineacion:"I"}, 
			{longitud:200, 	titulo:"Fecha de alta",   alias:"fechaAlta", alineacion:"I",  },		
			{longitud:100, 	titulo:"Estatus",   alias:"estatus", alineacion:"D", itemRenderer:this.renderEstatus},
			{longitud:200, 	titulo:"Fecha de cancelación",   alias:"fechaCancelacion", alineacion:"C",itemRenderer:this.renderFechaCancelacion },
			{longitud:200, 	titulo:"Limite de justificaciones",   alias:"limiteJusiticaciones", alineacion:"C",itemRenderer:this.renderLimiteJustificaciones }	
			
		]
		
		this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-edit fa-lg'></span></button>" +
										"<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>"; 
//										"<button data-toggle='tooltip' data-placemen='bottom' title='Cancelar'  type='button' class='cancelar btn-circle mr-0 botones-icon btn btn-sm float-left btn-warning active'><span  data-toggle='tooltip' class='fa fa-ban fa-lg'></span></button>";

		this.tabla.registros = [];		
	}
	
	inicializarEventosBotonesTabla(tbody, table, nombresCamposLlave)
	{
		
		super.inicializarEventosBotonesTabla(tbody, table, nombresCamposLlave);
		var _this = this;
		$(tbody).on("click", "button.cancelar", function()
		{
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

		    _this._registroSeleccionado  = table.row( tr ).data();
			
			
			if (_this._registroSeleccionado != undefined)
			{
				_this._llaves = _this.copiarPropiedadesObjeto(_this._registroSeleccionado, ["id"]);
				
				if(_this._registroSeleccionado.estatus==1)
					_this.cancelar();
				else
					_this.mostrarMensajeAdvertencia("","Este procedimiento ya se encuentra cancelado. Fecha  " +_this._registroSeleccionado.fechaCancelacion );
			}
		});
	}
	
//	inicializarEventosBotonesTabla(tbody, table, nombresCamposLlave)
//	{
//		
//	}
	
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
                "sedeIdSelect": {
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
			$('#sedeIdSelect').attr("disabled","disabled");
			$('#usuarioIdSelect').attr("disabled","disabled");
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
			empresaId: $('#empresaSelectCriterio').val(),
			nombre:$('#nombreInputCriterio').val()
		 }
		 return criteriosSeleccion;
	}		

	set modelo(valor)
	{		
		this.modeloEdicion = valor;
		if(this.modeloEdicion.limitarJustificaciones==1)
			$("#limitarJustificacionesSwitch").prop('checked', true);
		else
			$("#limitarJustificacionesSwitch").prop('checked', false);
		this.cambiarLimitarJustificaciones();
		$('#limiteJustificacionesSelect').val(this.modeloEdicion.limiteJustificaciones);
		
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
			 sedeId:$('#sedeIdSelect').val(),
			 usuarioId:$('#usuarioIdSelect').val(),
			 procedimientoId:$('#procedimientoIdSelect').val(),
			 limitarJustificaciones:$('#limitarJustificacionesSwitch').is(':checked')?1:0,
			 limiteJustificaciones : $('#limiteJustificacionesSelect').val(),
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
		this.cargandoOpciones('#sedeIdSelect');
	}
	
	consultarEmpresas()
	{
		this.cargandoOpciones("#empresaIdSelect");
		this.cargandoOpciones("#sedeIdSelect");
		this.cargandoOpciones("#usuarioIdSelect");
		this.cargandoOpciones("#procedimientoIdSelect");
		this.presentador.consultarEmpresas();
	}
	
	set empresas(registros)
	{		
		this.cargarOpciones('#empresaIdSelect', registros, this.modo, this.modeloEdicion, 'empresaId',"");
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
		this.presentador.consultarSedesCriterio();
	}
	
	set empresasCriterio(registros)
	{		
		this.cargarOpciones('#empresaSelectCriterio', registros);
		this.consultar();
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
		this.consultarProcedimientos();
	}
	
	consultarUsuarios()
	{
		this.cargandoOpciones("#usuarioIdSelect");
		this.presentador.consultarUsuarios();
	}
	
	consultarProcedimientos()
	{
		this.cargandoOpciones("#procedimientoIdSelect");
		this.presentador.consultarProcedimientos();
	}
	
	consultarSedes()
	{
		this.cargandoOpciones("#sedeIdSelect");
		this.presentador.consultarSedes();
	}
	
	set sedes(registros)
	{		
		this.cargarOpciones('#sedeIdSelect', registros, this.modo, this.modeloEdicion, 'sedeId',"");
	}
	
	set usuarios(registros)
	{		
		this.cargarOpciones('#usuarioIdSelect', registros, this.modo, this.modeloEdicion, 'usuarioId',"","nombreCompleto");
	}
	
	set procedimientos(registros)
	{		
		this.cargarOpciones('#procedimientoIdSelect', registros, this.modo, this.modeloEdicion, 'procedimientoId',"");
	}

	
}
var vista = new UsuariosProcedimientosVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});
