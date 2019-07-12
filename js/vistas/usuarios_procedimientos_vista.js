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
	
	crearColumnasGrid()
	{
		this.tabla.columnas = [
			{longitud:200, 	titulo:"Usuario",   alias:"usuarioNombre", alineacion:"I", class: "desc" }, 
			{longitud:200, 	titulo:"Procedimiento",   alias:"procedimientoNombre", alineacion:"I"}, 
			{longitud:200, 	titulo:"Fecha de alta",   alias:"fechaAlta", alineacion:"I",  },		
			{longitud:100, 	titulo:"Estatus",   alias:"estatus", alineacion:"D", itemRenderer:this.renderEstatus},
			{longitud:200, 	titulo:"Fecha de cancelación",   alias:"fechaCancelacion", alineacion:"I",itemRenderer:this.rendeFechaCancelacion }	
			
		]
		
		this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";

		this.tabla.registros = [];		
	}
	
	rendeFechaCancelacion(renglon, type, set)
	{    
		var contenido = "";
		if(renglon.estatus==1)
			contenido += "";
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
//		$('#nombreInput').val(this.modeloEdicion.nombre);
//		$('#codigoInput').val(this.modeloEdicion.codigo);
//		$('#descripcionInput').val(this.modeloEdicion.descripcion);
//		$('#rutaArchivoInput').val(this.modeloEdicion.rutaArchivo);
//		if(this.modeloEdicion.estatus==1)
//			$("#estatusRadio").prop('checked', true);
//		else
//			$("#estatusRadio").prop('checked', false);
		this.consultarCombos();
	}
	
	get modelo()
	{
		 var modelo = 
		 {		
			 empresaId:$('#empresaIdSelect').val(),
			 sedeId:$('#sedeIdSelect').val(),
			 usuarioId:$('#usuarioIdSelect').val(),
			 procedimientoId:$('#procedimientoIdSelect').val()
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
