class ReportesEvidenciasVista extends CatalogoVista
{		
	constructor()
	{	
		super();
		this.presentador = new ReportesEvidenciasPresentador(this);
		this._urlFormulario = "html/formularios/paises.php";
	}
	
	inicializar()
	{
		this.tabla.textoTablaVacia = "No hay reportes disponibles";
		this.crearColumnasGrid();
		
		this.consultoGrid = false;
		
		var _this = this;
		$("#consultarButton").click(function(){
			_this.consultar();
		});
		
	
		
		if(this.usuario.tipoUsuarioId == TipoUsuario.ADMINISTRADOR)
		{
			$("#criteriosRow").show();
			this.consultarEmpresasCriterio();
		}	
		else	
			this.consultar();
	}
	
//	consultarAnosMeses()
//	{
//		this.presentador.consultarAnosMeses();
//	}
	
//	onLoad()
//	{			
//		this.crearColumnasGrid();		
//		this.presentador.consultar();
//	}
	
	crearColumnasGrid()
	{
		
		this.tabla.columnas = [
			{longitud:250, 	titulo:"Mes",   alias:"mesNombre", alineacion:"I" },	
			{longitud:200, 	titulo:"Año",   alias:"ano", alineacion:"I" },
			{longitud:100, 	titulo:"",   alias:"", alineacion:"I", itemRenderer: this.renderBotonImprimir}
			
		]
		
		//this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Imprimir'  type='button' class='imprimir btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fas fa-file-pdf fa-lg'></span></button>";
		

		this.tabla.registros = [];
	}
	
	renderBotonImprimir(renglon, type, set)
	{    
		var	html = "";
		if(renglon.mensajeError=="")
			html += "<button data-toggle='tooltip' data-placemen='bottom' title='Imprimir'  type='button' class='imprimir btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fas fa-file-pdf fa-lg'></span></button>";
		else
			html += "<span>" +renglon.mensajeError +"</span>";
		return html;
	}
	
	inicializarEventosBotonesTabla(tbody, table, nombresCamposLlave)
	{
		var _this = this;
		$(tbody).on("click", "button.imprimir", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

			_this._registroSeleccionado  = table.row( tr ).data();
			if (_this._registroSeleccionado != undefined)
			{
				_this._llaves = _this.copiarPropiedadesObjeto(_this._registroSeleccionado, ["id"]);
				_this.imprimirReporte();
			}
		});
	}
	
	
	
	imprimirReporte()
	{
		var submitForm = this.getNewSubmitForm(HANDEL_API+"/php/reportes/reporte_evidencias.php");
		if(this.usuario.tipoUsuarioId == TipoUsuario.ADMINISTRADOR)
		{
			var usuarioSeleccionado =$("#usuarioSelectCriterio").val();
			this.createNewFormElement(submitForm, "usuarioId", usuarioSeleccionado);
		}
		else
			this.createNewFormElement(submitForm, "usuarioId", this.usuario.id);	 
		this.createNewFormElement(submitForm, "ano", this._registroSeleccionado.ano);	 
		this.createNewFormElement(submitForm, "mes", this._registroSeleccionado.mes);	 
		submitForm.target= "_blank";
	    submitForm.submit();
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
	
	get criteriosSeleccion()
	{
		 var criteriosSeleccion = 
		 {				    
			empresaId: $('#empresaSelectCriterio').val(),
			sedeId: $('#sedeSelectCriterio').val(),
		 }
		 return criteriosSeleccion;
	}		
	
	
	set modelo(valor)
	{		
		this.modeloEdicion = valor;
		$('#nombreInput').val(this.modeloEdicion.nombre);
		$("input[name=estatus][value=" + this.modeloEdicion.estatus + "]").prop('checked', true);
		//this.consultarEmpresas();
	}
	
	get modelo()
	{
		 var modelo = 
		 {		
			 nombre:$('#nombreInput').val(),			 
			 estatus:$('#estatusRadio').is(':checked')?1:0
		 };
		 if(this.modo=="CAMBIO" && this.modeloEdicion!=null)
			 modelo.id = this.modeloEdicion.id;
		 return modelo;
	 }
	 


	limpiarFormulario()
	{
		$('#nombreInput').val("");
		//this.cargandoOpciones('#empresaSelect');
	}
	
	
	consultarEmpresasCriterio()
	{
		this.cargandoOpciones("#empresaSelectCriterio");
		this.cargandoOpciones("#sedeSelectCriterio");
		this.cargandoOpciones("#usuarioSelectCriterio");
		this.presentador.consultarEmpresasCriterio();
	}
	
	set empresasCriterio(registros)
	{		
		this.cargarOpciones('#empresaSelectCriterio', registros);
	}
	
	cambiarEmpresa()
	{
		this.consultarSedes();
	}
	
	cambiarEmpresaCriterio()
	{
		this.consultarSedesCriterio();
	}
	

	consultarSedesCriterio()
	{
		this.cargandoOpciones("#sedeSelectCriterio");
		this.cargandoOpciones("#usuarioSelectCriterio");
		this.presentador.consultarSedesCriterio();
	}
	
	
	
	set sedesCriterio(registros)
	{		
		this.cargarOpciones('#sedeSelectCriterio', registros);
		
	}
	
	consultarUsuariosCriterio()
	{
		this.cargandoOpciones("#usuarioSelectCriterio");
		this.presentador.consultarUsuariosCriterio();
	}
	
	set usuariosCriterio(registros)
	{		
		this.cargarOpciones('#usuarioSelectCriterio', registros,"", null, "id", null, "nombreCompleto",true);
		if(this.consultoGrid==false)
		{
			this.consultar();
			this.consultoGrid=true;
		}
	}
	
	cambiarSedeCriterio()
	{
		this.cargandoOpciones("#usuarioSelectCriterio");
		this.consultarUsuariosCriterio();
	}

	set datos(datos)
	{
		if(this.usuario.tipoUsuarioId == TipoUsuario.ADMINISTRADOR)
		{
			
			var usuarioSeleccionado =$("#usuarioSelectCriterio").val();
			if(usuarioSeleccionado!="")
			{
				var opcionId = "usuarioSelectCriteriooption" + usuarioSeleccionado;
				var usuario = $("#usuarioSelectCriterio option[id='"+opcionId+"']").data("data");
				if(usuario!=null)
				{
					var fecha = new Date();
					$("#usuarioRow").show();
					$("#usuarioImg").attr("src",HANDEL_API + "/" +usuario.fotoPerfil+"?"+fecha.getTime());
					$("#usuarioSpan").html(usuario.nombreCompleto);
					if(usuario.tipoUsuarioId == TipoUsuario.SUPERVISOR)
						$("#tipoUsuarioLabel").html("Supervisor:");
					else
						$("#tipoUsuarioLabel").html("Coordinador:");
					super.datos = datos;
				}
			}
		}
		else
			super.datos = datos;
	}
	

	
}
var vista = new ReportesEvidenciasVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});

