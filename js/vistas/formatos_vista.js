class FormatosVista extends CatalogoVista
{		
	constructor(ventana)
	{	
		super(ventana);
		this.presentador = new FormatosPresentador(this);
		this._urlFormulario = "html/formularios/formatos.php";
	}
	
	inicializar()
	{
		var _this = this;
		super.inicializar();
		this.consultarEmpresasCriterio();
		
		$("#copiarButton").click(function(){
			_this.iniciarCopia();
		});
		
		$("#file").on("change",function(event)
		{

			var file = event.currentTarget.files[0];
			_this.presentador.subirArchivo(_this._llaves,file);
		
		
		});
		
	}
	
	iniciarCopia()
	{
		this._copiarProcedimientosModal = new CopiarFormatosAsistente();
		this._copiarProcedimientosModal.mostrar(this, this.copiar);
	}
	
	copiar()
	{
		
	}
	
	crearColumnasGrid()
	{
		this.tabla.columnas = [
			{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"D" },
			//{longitud:200, 	titulo:"Código",   alias:"codigo", alineacion:"I"}, 
			{longitud:200, 	titulo:"Nombre",   alias:"nombre", alineacion:"I", class: "desc" }, 
			//{longitud:200, 	titulo:"Descripción",   alias:"descripcion", alineacion:"I"}, 
			{longitud:200, 	titulo:"Empresa",   alias:"empresaNombre", alineacion:"I" },		
			{longitud:200, 	titulo:"Sede",   alias:"sedeNombre", alineacion:"I" },		
			{longitud:200, 	titulo:"Sección en manual",alias:"rutaArchivo", alineacion:"I"},
			{longitud:200, 	titulo:"Archivo",alias:"nombreArchivo", alineacion:"I", itemRenderer:this.renderArchivos},		
			{longitud:250, 	titulo:"Fecha de alta",   alias:"fechaAlta", alineacion:"I" },	
			{longitud:200, 	titulo:"Fecha de última modificación",   alias:"fechaModificacion", alineacion:"I" },
			{longitud:100, 	titulo:"Estatus",   alias:"estatus", alineacion:"D", itemRenderer:this.renderEstatus}
		]
		
		this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Adjuntar Archivo'  type='button' class='adjuntar btn-circle mr-0 botones-icon btn btn-sm float-left btn-success active'><span  data-toggle='tooltip' class='fa fa-upload fa-lg'></span></button>" +
		"<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar Archivo'  type='button' class='eliminarArchivo btn-circle mr-0 botones-icon btn btn-sm float-left btn-warning active'><span  data-toggle='tooltip' class='fa fa-trash fa-lg'></span></button>"+
		"<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-edit fa-lg'></span></button>"+
		"<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";

		this.tabla.registros = [];		
	}
	
	renderArchivos(renglon, type, set)
	{  
		return "<div id='archivo"+renglon.id+"'>" + vista.getArchivos(renglon) + "</div>";
	}
	
	getArchivos(renglon)
	{
		var contenido = "";
		var tieneArchivos = renglon.archivo!=""?true:false;
		if(tieneArchivos)
		{
			contenido = this.getContenidoArchivo(renglon.archivo);
		
		}
	    return contenido;
	}
	
	getContenidoArchivo(nombreArchivo)
	{
		var contenido = "";
		contenido = "<div class='archivo' data-toggle='tooltip' data-placemen='bottom' title='"+nombreArchivo+"'>";
		contenido+= "<i  class='archivos fa fa-lg fa-paperclip' style='cursor:pointer'></i>";
		contenido+="<span  class='labelArchivo'>1</span>";
		contenido+="</div>";
		return contenido;
	}
	
	/*renderArchivo(renglon, type, set)
	{    
		var contenido = "";
		if(renglon.rutaArchivo!=null)
		contenido += "<a href='"+renglon.rutaArchivo+"' target='_blank'>"+renglon.rutaArchivo+"</a>";
	    return contenido;
	}*/
	
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
			sedeId: $('#sedeSelectCriterio').val(),
			nombre:$('#nombreInputCriterio').val()
		 }
		 return criteriosSeleccion;
	}		

	set modelo(valor)
	{		
		this.modeloEdicion = valor;
		$('#nombreInput').val(this.modeloEdicion.nombre);
		//$('#codigoInput').val(this.modeloEdicion.codigo);
		//$('#descripcionInput').val(this.modeloEdicion.descripcion);
		$('#rutaArchivoInput').val(this.modeloEdicion.rutaArchivo);
		if(this.modeloEdicion.estatus==1)
			$("#estatusRadio").prop('checked', true);
		else
			$("#estatusRadio").prop('checked', false);
		
		if(this.modeloEdicion.oea==1)
			$("#oeaCheck").prop('checked', true);
		else
			$("#oeaCheck").prop('checked', false);	
		
		if(this.modeloEdicion.ctpat==1)
			$("#ctpatCheck").prop('checked', true);
		else
			$("#ctpatCheck").prop('checked', false);	
			
		if(this.modeloEdicion.wrap==1)
			$("#wrapCheck").prop('checked', true);
		else
			$("#wrapCheck").prop('checked', false);	
			
		if(this.modeloEdicion.ipm==1)
			$("#ipmCheck").prop('checked', true);
		else
			$("#ipmCheck").prop('checked', false);	
			
		this.consultarCombos();
	}
	
	get modelo()
	{
		 var modelo = 
		 {		
			 empresaId:$('#empresaIdSelect').val(),
			 sedeId:$('#sedeIdSelect').val(),
			 nombre:$('#nombreInput').val(),
			 rutaArchivo: $('#rutaArchivoInput').val(),
			 oea: $('#oeaCheck').is(':checked')?1:0,
			 ctpat: $('#ctpatCheck').is(':checked')?1:0,
			 wrap: $('#wrapCheck').is(':checked')?1:0,
			 ipm: $('#ipmCheck').is(':checked')?1:0,
			 estatus:$('#estatusRadio').is(':checked')?1:0
		 };
		 if(this.modo=="CAMBIO" && this.modeloEdicion!=null)
			 modelo.id = this.modeloEdicion.id;
		 return modelo;
	 }
	 
	datosValidos()
	{
		var nombre = $("#nombreInput"),
	        empresa = $("#empresaSelect"),
	        sede = $("#sedeSelect");
        
        var allFields = $( [] ).add(nombre).add(empresa).add(sede);
        var tips = $( ".validateTips" );
		tips.text("");
		
		var valid = true;
		allFields.removeClass("ui-state-error");
		
	    valid = valid && this.validaciones.checkValue( nombre, "nombre", tips );
	    valid = valid && this.validaciones.checkValue( empresa, "empresa",tips );
	    valid = valid && this.validaciones.checkValue( sede, "sede",tips );
	    
		return valid;
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
		//this.consultar();
	}
	
	set sedesCriterio(registros)
	{		
		this.cargarOpciones('#sedeSelectCriterio', registros);
	}
	
	
	cambiarEmpresa()
	{
		this.consultarSedes();
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
	
	inicializarEventosBotonesTabla(tbody, table, nombresCamposLlave)
	{
		var _this = this;
		super.inicializarEventosBotonesTabla(tbody, table, nombresCamposLlave);
		$(tbody).on("click", "button.adjuntar", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }
		    

			_this._registroSeleccionado  = table.row( tr ).data();
			if (_this._registroSeleccionado != undefined)
			{
				_this._llaves = _this.copiarPropiedadesObjeto(_this._registroSeleccionado, ["id"]);
				_this.adjuntar();
			}
		});

		$(tbody).on("click", "div.archivo", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }
			
			_this._indiceArchivoSeleccionado = table.row( tr ).index(); 

			_this._registroSeleccionado  = table.row( tr ).data();
			if (_this._registroSeleccionado != undefined)
			{
				if(_this._registroSeleccionado.archivo!="")
				{
					var vistaPrevia = new VistaPreviaArchivo();
					vistaPrevia.visualizar(_this, "php/archivos_formatos", _this._registroSeleccionado.id, _this._registroSeleccionado.archivo);
				}
				else
					_this.mostrarMensajeAdvertencia("","Para visualizar archivos es necesario guardar la información.")
			}
		});
		
		$(tbody).on("click", "button.eliminarArchivo", function()
		{
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

		    _this._registroSeleccionado  = table.row( tr ).data();
			
			
			if (_this._registroSeleccionado != undefined)
			{
				_this._llaves = _this.copiarPropiedadesObjeto(_this._registroSeleccionado, ["id"]);
				_this.eliminarArchivo();
			}
		});
		
		
		
	}
	
	eliminarArchivo()
	{ 
		var _this = this;
		swal({
	            title: "\u00bfEst\u00E1 seguro de eliminar el archivo?",
	            text: "",
	            type: "warning",
	            showCancelButton: true,
	            confirmButtonColor: "#DD6B55",
	            confirmButtonText: "Si, eliminar!!",
	            cancelButtonText: "No",
	            closeOnConfirm: true,
	            closeOnCancel: true,
	            showLoaderOnConfirm: true,
	        },
	        function(isConfirm)
	        {
	            if (isConfirm) 
	            {
	            	 setTimeout(function(){
	            		 _this.presentador.eliminarArchivo();
	 	            }, 1000);
	            }
	        });
	}

	adjuntar()
	{
		var _this = this;
		if(this._registroSeleccionado.archivo=="")
   			$("#file").trigger("click");
   		else
   		{
			swal({
	            title: "",
	            text: "El registro ya tiene un archivo adjunto, ¿Desea reemplazar el archivo?",
	            type: "warning",
	            showCancelButton: true,
	            confirmButtonColor: "#DD6B55",
	            confirmButtonText: "Si, reemplazar!!",
	            cancelButtonText: "No",
	            closeOnConfirm: true,
	            closeOnCancel: true,
	            showLoaderOnConfirm: true,
	        },
	        function(isConfirm)
	        {
	            if (isConfirm) 
	            {
	            	 setTimeout(function(){
	            		 $("#file").trigger("click");
	 	            }, 500);
	            }
	        });	   
	  	}
	}
	
	marcarArchivoSubido(id, archivo)
	{
		var tieneArchivos = archivo!=""?true:false;
		var div = $("#archivo" + id);
		var contenido = "";
		if(tieneArchivos)
		{
			contenido = this.getContenidoArchivo(archivo);
		}
		div.html(contenido);
		
		this._registroSeleccionado.archivo = archivo;
		//var contenido = "<div class='archivo' data-toggle='tooltip' data-placemen='bottom' title='"+archivo+"'>";
		//contenido+= "<i  class='archivos fa fa-lg fa-paperclip' style='cursor:pointer'></i>";
		//contenido+="<span  class='labelArchivo' >1</span>";
		//contenido+="</div>";
	}
	
	
	
	
}
var vista = new FormatosVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});
