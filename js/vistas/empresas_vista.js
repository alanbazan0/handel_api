
class EmpresasVista extends CatalogoVista
{		
	constructor()
	{	
		super();
		this.presentador = new EmpresasPresentador(this);
		this._urlFormulario = "html/formularios/empresas.php";
		
	}
	
	inicializar()
	{
		super.inicializar();
		var _this = this;
		$("#estructuraButton").click(function(){
			_this.consultarEstructura();
		});
	}
	
	consultarEstructura()
	{
		this.presentador.consultarEstructura();
	}
	
	consultarOrganigrama()
	{
		this.presentador.consultarOrganigrama();
	}
	
	set organigrama(organigrama)
	{
		this.mostrarFormularioHTML(HANDEL_API+"/html/modales/organigrama_empresa.php",this, null, 
				function()
				{
					 $('#treeDiv').treeview({
				          color: "#000000",
				          selectable: false,
				          selectedBackColor : "#B0BED9",
				          expandIcon: 'fa fa-chevron-right',
				          collapseIcon: 'fa fa-chevron-down',
				          nodeIcon: 'fa fa-user',
				          data: organigrama,
				          itemRenderer:this.itemRendererOrganigrama
				        });
					
				},null,"estructuraModal","","");
	}
	
	set estructura(estructura)
	{
		this.mostrarFormularioHTML(HANDEL_API+"/html/modales/estructura_empresas.php",this, null, 
				function()
				{
					
					 $('#treeDiv').treeview({
				          color: "#000000",
				          selectable: false,
				          selectedBackColor : "#B0BED9",
				          expandIcon: 'fa fa-chevron-right',
				          collapseIcon: 'fa fa-chevron-down',
				          nodeIcon: 'fa fa-building',
				          data: estructura,
				          itemRenderer:this.itemRendererRelacion
				        });
					
				},null,"estructuraModal","","");
	}
	
	itemRendererRelacion(node)
	{
		var html = "";
//		if(node.parentId!=undefined)
//			html += "<button  style='display:inline-block;' data-nodeId = '"+node.nodeId+"' data-tablaNombre='"+node.nombre+"' data-placemen='bottom' title='Eliminar' type='button' class='eliminar float-right botones-icon btn btn-sm btn-danger active' ><span class='ti-trash'></span></button>";
//			
//		html += "<button  style='display:inline-block;' data-nodeId = '"+node.nodeId+"' data-tablaNombre='"+node.nombre+"' data-placemen='bottom' title='Agregar tabla' type='button' class='relacionar float-right botones-icon btn btn-sm btn-info active' ><span class='fa fa-plus'></span></button>";
//	
//		html += "<button  style='display:inline-block;' data-nodeId = '"+node.nodeId+"' data-tablaNombre='"+node.nombre+"' data-placemen='bottom' title='Datos' type='button' class='datos float-right botones-icon btn btn-sm btn-warning active' ><span class='fa fa-database'></span></button>";
//		
//		if(node.parentId!=undefined)
//			html +=	"<button  style='display:inline-block;' data-nodeId = '"+node.nodeId+"' data-tablaNombre='"+node.nombre+"' data-placemen='bottom' title='Relacionar'  type='button' class='campos float-right botones-icon btn btn-sm btn-success active' ><span class='fas fa-project-diagram'></span></button>";
//		
		
	
		return html;
	}
	

	itemRendererOrganigrama(node)
	{
		var html = "";
		var fecha = new Date();
		var icono = HANDEL_API+ "/"+node.fotoPerfil+"?"+fecha.getTime();
//		if(node.parentId!=undefined)
//			html += "<button  style='display:inline-block;' data-nodeId = '"+node.nodeId+"' data-tablaNombre='"+node.nombre+"' data-placemen='bottom' title='Eliminar' type='button' class='eliminar float-right botones-icon btn btn-sm btn-danger active' ><span class='ti-trash'></span></button>";
//			
		//html += "<img class='float-left' src='" + icono + "' style='width:25px;height:25px;border-radius:50%'></img>";
//	
//		html += "<button  style='display:inline-block;' data-nodeId = '"+node.nodeId+"' data-tablaNombre='"+node.nombre+"' data-placemen='bottom' title='Datos' type='button' class='datos float-right botones-icon btn btn-sm btn-warning active' ><span class='fa fa-database'></span></button>";
//		
//		if(node.parentId!=undefined)
//			html +=	"<button  style='display:inline-block;' data-nodeId = '"+node.nodeId+"' data-tablaNombre='"+node.nombre+"' data-placemen='bottom' title='Relacionar'  type='button' class='campos float-right botones-icon btn btn-sm btn-success active' ><span class='fas fa-project-diagram'></span></button>";
//		
		
	
		return html;
	}
	
	
	crearColumnasGrid()
	{
		this.tabla.columnas = [
			{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"D" },
			{longitud:50, 	titulo:"",   	alias:"logo", alineacion:"D" ,itemRenderer:this.renderLogo},
			{longitud:200, 	titulo:"Nombre",   	alias:"nombre", alineacion:"I" }, 
			{longitud:200, 	titulo:"Nombre corto",   	alias:"nombreCorto", alineacion:"I" }, 
			{longitud:200, 	titulo:"Teléfono",   alias:"telefono", alineacion:"I" }, 	
			{longitud:200, 	titulo:"Tipo de empresa",   alias:"tipoEmpresa", alineacion:"I" }, 
			{longitud:200, 	titulo:"Dirección",   alias:"direccion", alineacion:"I" }, 
			{longitud:200, 	titulo:"País",   alias:"pais", alineacion:"I" },
			{longitud:200, 	titulo:"Estado",   alias:"estado", alineacion:"I" },
			{longitud:200, 	titulo:"Ciudad",   alias:"ciudad", alineacion:"I" },
			{longitud:200, 	titulo:"Corporativo",   alias:"corporativo", alineacion:"I" },				
			{longitud:250, 	titulo:"Fecha de alta",   alias:"fechaAlta", alineacion:"I" },	
			{longitud:200, 	titulo:"Fecha de última modificación",   alias:"fechaModificacion", alineacion:"I" },
			{longitud:100, 	titulo:"Estatus",   alias:"estatus", alineacion:"D", itemRenderer:this.renderEstatus}
		]
		
		this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-edit fa-lg'></span></button>"+
									"<button data-toggle='tooltip' data-placemen='bottom' title='Organigrama'  type='button' class='organigrama btn-circle mr-0 botones-icon btn btn-sm float-left btn-primary active'><span  data-toggle='tooltip' class='fas fa-project-diagram'></span></button>"+
									"<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";
	
		this.tabla.registros = [];

	}
	
	inicializarEventosBotonesTabla(tbody, table, nombresCamposLlave)
	{
		super.inicializarEventosBotonesTabla(tbody, table, nombresCamposLlave);
		var _this = this;
		$(tbody).on("click", "button.organigrama", function()
		{			
			 var tr = $(this).closest('tr');
			    
		    if ( $(tr).hasClass('child') ) {
		      tr = $(tr).prev();  
		    }

			_this._registroSeleccionado  = table.row( tr ).data();
			if (_this._registroSeleccionado != undefined)
			{
				_this._llaves = _this.copiarPropiedadesObjeto(_this._registroSeleccionado, ["id"]);
				_this.consultarOrganigrama();
			}
		});
	}
	
//	datosValidos()
//	{
//		var nombre = $("#nombreInput"),
//		 nombreCorto = $("#nombreCortoInput"),
//			direccion = $("#direccionInput"),
//			telefono = $("#telefonoInput"),
//			tipoEmpresa = $("#tipoEmpresaSelect"),
//			pais = $("#paisSelect"),
//			estado = $("#estadoSelect"),
//			ciudad = $("#ciudadSelect");		
//        
//        var allFields = $( [] ).add(nombre).add(nombreCorto).add(direccion).add(telefono).add(tipoEmpresa).add(pais).add(estado).add(ciudad);
//        var tips = $( ".validateTips" );
//		tips.text("");
//		
//		var valid = true;
//		allFields.removeClass("ui-state-error");
//		
//		valid = valid && this.validaciones.checkValue( nombre, "nombre", tips );	
//		valid = valid && this.validaciones.checkValue( nombreCorto, "nombre corto", tips );	
//		valid = valid && this.validaciones.checkValue( telefono, "teléfono", tips );
//		valid = valid && this.validaciones.checkValue( tipoEmpresa, "tipo de empresa", tips );
//	    valid = valid && this.validaciones.checkValue( direccion, "dirección", tips );
//	    valid = valid && this.validaciones.checkValue( pais, "país", tips );
//	    valid = valid && this.validaciones.checkValue( estado, "estado", tips );
//	    valid = valid && this.validaciones.checkValue( ciudad, "ciudad", tips );
//	   
//	    
//		return valid;
//	}	
	
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
                },
                "nombreCortoInput": {
                    required: !0
                },
                "direccionInput": {
                    required: !0
                },
                "telefonoInput": {
                    required: !0
                },
                "tipoEmpresaSelect": {
                    required: !0
                },
                "paisSelect": {
                    required: !0
                },
                "estadoSelect": {
                    required: !0
                },
                "ciudadSelect": {
                    required: !0
                }
               
            },
            messages: {
                "nombreInput": "Por favor ingrese un nombre",
                "nombreCortoInput": "Por favor ingrese un nombre corto",
                "direccionInput": "Por favor ingrese una dirección",
                "telefonoInput": "Por favor ingrese un teléfono",
                "tipoEmpresaSelect": "Por favor ingrese un tipo de empresa",
                "paisSelect": "Por favor ingrese un país",
                "estadoSelect": "Por favor ingrese un estao",
                "ciudadSelect": "Por favor ingrese una ciudad"
                	
                
            },
            submitHandler:function (form) {
            	 _this.guardar();
            }
        });
	}
	
	renderLogo(renglon, type, set)
	{    
		var fecha = new Date();
		var contenido = "";
		var icono = HANDEL_API+ "/php/logos_empresas/" + renglon.icono+"?"+fecha.getTime();
		contenido += "<center><img src='" + icono + "' style='width:30px;height:30px;'></img></center>";
	    return contenido;
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
	
	

	
	agregar()
	{
		super.agregar();
		
		
	}
	
	consultarCombos()
	{
		this.consultarTiposEmpresa();
		this.consultarPaises();
		this.consultarCorporativos();
		this.consultarAdministradores();
		this.consultarAdministradoresSIVAH();
		this.consultarAdministradoresProcesos();
		this.consultarPerfiles();
		$("#calificacionMinimaInput").change(this.cambiarCalificacionMinima);
		
	}
	
	cambiarCalificacionMinima(event)
	{
		var valor = $(event.currentTarget).val();
		valor = parseInt(valor);
		if(isNaN(valor))
			valor = 0;
		$(event.currentTarget).val(valor);
	}
	
	editar(id)
	{
		super.editar(id);
		$('#logoImage').hide();
		$('#nombreInput').focus();				

				
	}
	
//	consultar()
//	{	
//		this.presentador.consultar();
//	}	
	
//	guardar()
//	{		
//		 if(this.datosValidos())
//		 {
//			if(this.modo=='ALTA')
//				this.presentador.insertar();
//			else
//				this.presentador.actualizar();
//		 }		
//		
//	}
//	
//	btnSalir_onClick()
//	{
//		var confirmacion = confirm("¿Esta seguro que desea salir?")
//	    if (confirmacion)
//	    	{
//		    	
//	    	}
//	}
//	
//	btnSalirFormulario_onClick()
//	{		
//		this.salirFormulario();
//	}	

//	get llaves()
//	{
////		var llaves =
////		{
////			id:this.grid._selectedItem.id	
////		}
////		return llaves;
//		return this._llaves;
//	}
	
//	get criteriosSeleccion()
//	{
//		 var criteriosSeleccion = 
//		 {				    
//			nombre:$('#nombreInputCriterio').val()
//			
//		 }
//		 return criteriosSeleccion;
//	}		

//	set datos(datos)
//	{
//		this.tabla.registros = datos;	
//		this.tabla.renderizar();
//	}
	
	set modelo(valor)
	{		
		this.modeloEdicion = valor;
		$('#nombreInput').val(this.modeloEdicion.nombre);
		$('#nombreCortoInput').val(this.modeloEdicion.nombreCorto);
		$('#direccionInput').val(this.modeloEdicion.direccion);
		$('#telefonoInput').val(this.modeloEdicion.telefono);	
		$('#mesRevisionProcesosSelect').val(this.modeloEdicion.mesRevisionProcesos);		
		//$("input[name=estatus][value=" + this.modeloEdicion.estatus + "]").prop('checked', true);
		
		if(this.modeloEdicion.estatus==1)
			$("#estatusRadio").prop('checked', true);
		else
			$("#estatusRadio").prop('checked', false);
		
		$('#logoImage').attr('src', HANDEL_API + "/php/logos_empresas/" + this.modeloEdicion.icono);
		$('#logoImage').show();
		
		$('#calificacionMinimaInput').val(this.modeloEdicion.calificacionMinima);
		$('#fechaInicioTemporada').val(this.modeloEdicion.fechaInicioTemporada);
		this.consultarCombos();
		this.consultarCombos();
	}
	
	get modelo()
	{
		 var modelo = 
		 {		
			 nombre:$('#nombreInput').val(),
			 nombreCorto:$('#nombreCortoInput').val(),
			 tipoEmpresaId:$('#tipoEmpresaSelect').val(),
			 direccion:$('#direccionInput').val(),
			 telefono:$('#telefonoInput').val(),
			 paisId:$('#paisSelect').val(),
			 estadoId:$('#estadoSelect').val(),
			 ciudadId:$('#ciudadSelect').val(),
			 corporativoId:$('#corporativoSelect').val(),
			 administradorId:$('#administradorSelect').val(),
		 	 administradorIdSIVAH:$('#administradorSIVAHSelect').val(),
	 		 mesRevisionProcesos:$('#mesRevisionProcesosSelect').val(),
			 administradorIdProcesos:$('#administradorProcesosSelect').val(),
			 perfilId:$('#perfilSelect').val(),
			 estatus:$('#estatusRadio').is(':checked')?1:0,
			 calificacionMinima:$('#calificacionMinimaInput').val(),
			 fechaInicioTemporada:$('#fechaInicioTemporadaInput').val()
		 };
		 if(this.modo=="CAMBIO" && this.modeloEdicion!=null)
			 modelo.id = this.modeloEdicion.id;
		 return modelo;
	 }
	 
	
	

	limpiarFormulario()
	{
		$('#file').val("");
		$('#logoImage').attr("src","php/logos_empresas/default.png");
		$('#nombreInput').val("");
		$('#nombreCortoInput').val("");
		$('#direccionInput').val("");
		$('#telefonoInput').val("");
		this.cargandoOpciones('#tipoEmpresaSelect');
		this.cargandoOpciones('#paisSelect');
		this.cargandoOpciones('#estadoSelect');
		this.cargandoOpciones('#ciudadSelect');
		this.cargandoOpciones('#corporativoSelect');
		this.cargandoOpciones('#administradorSelect');
		$('#fechaInicioTemporada').val("");
	}
	
	consultarAdministradores()
	{
		this.cargandoOpciones("#administradorSelect");
		this.presentador.consultarAdministradores();
	}
	
	consultarAdministradoresSIVAH()
	{
		this.cargandoOpciones("#administradorSIVAHSelect");
		this.presentador.consultarAdministradoresSIVAH();
	}
	
	consultarAdministradoresProcesos()
	{
		this.cargandoOpciones("#administradorProcesosSelect");
		this.presentador.consultarAdministradoresProcesos();
	}
	
	consultarPerfiles()
	{
		this.cargandoOpciones("#perfilSelect");
		this.presentador.consultarPerfiles();
	}
	
	consultarCorporativos()
	{
		this.cargandoOpciones("#corporativoSelect");
		this.presentador.consultarCorporativos();
	}
	
	consultarTiposEmpresa()
	{
		this.cargandoOpciones("#tipoEmpresaSelect");
		this.presentador.consultarTiposEmpresa();
	}	
	
	set tiposEmpresa(registros)
	{	
		this.cargarOpciones('#tipoEmpresaSelect', registros, this.modo, this.modeloEdicion, 'tipoEmpresaId');
		
	}

	consultarPaises()
	{
		this.cargandoOpciones("#paisSelect");
		this.presentador.consultarPaises();
	}
	
	set paises(registros)
	{	
		this.cargarOpciones('#paisSelect', registros, this.modo, this.modeloEdicion, 'paisId',"");
		
	}
	
	cambiarPais()
	{
		this.cargandoOpciones("#estadoSelect");
		this.cargandoOpciones("#ciudadSelect");
		this.consultarEstados();
	}
	
	consultarEstados()
	{
		
		this.presentador.consultarEstados();
	}
	
	set estados(registros)
	{	
		this.cargarOpciones('#estadoSelect', registros, this.modo, this.modeloEdicion, 'estadoId',"");
		
	}
	
	set perfiles(registros)
	{	
		this.cargarOpciones('#perfilSelect', registros, this.modo, this.modeloEdicion, 'perfilId',"");
		
	}
	
	
	cambiarEstado()
	{
		
		this.consultarCiudades();
	}
	
	consultarCiudades()
	{
		this.cargandoOpciones("#ciudadSelect");
		this.presentador.consultarCiudades();
	}
	
	set ciudades(registros)
	{
		this.cargarOpciones('#ciudadSelect', registros, this.modo, this.modeloEdicion, 'ciudadId',"");
	}
	
	set corporativos(registros)
	{
		this.cargarOpciones('#corporativoSelect', registros, this.modo, this.modeloEdicion, 'corporativoId',"");
	}
	
	set administradores(registros)
	{
		this.cargarOpciones('#administradorSelect', registros, this.modo, this.modeloEdicion, 'administradorId',"","nombreCompleto");
	}
	
	set administradoresSIVAH(registros)
	{
		this.cargarOpciones('#administradorSIVAHSelect', registros, this.modo, this.modeloEdicion, 'administradorIdSIVAH',"","nombreCompleto");
	}
	
	set administradoresProcesos(registros)
	{
		this.cargarOpciones('#administradorProcesosSelect', registros, this.modo, this.modeloEdicion, 'administradorIdProcesos',"","nombreCompleto");
	}
	
}
var vista = new EmpresasVista();
$(document).ready(function() 
{
	vista.inicializar();
});
