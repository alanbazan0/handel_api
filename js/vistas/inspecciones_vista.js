class InspeccionesVista extends CatalogoVista
{		
	constructor(ventana)
	{	
		super(ventana);
		this.presentador = new InspeccionesPresentador(this);
		this.consultoGrid = false;
		
	}
	
	onLoad()
	{
		//this.tabla = new Tabla(this,"#tabla");	
		//this.tabla.rendererBotones = this.rendererBotones;
		//this.tabla.editar = false;
		//this.tabla.eliminar = false;
		this.inspeccionesTabla = $('#inspeccionesTabla');
		this.crearFechas();
		this.inicializarEliminar();
		this.crearColumnasGrid();
		this.consultarEmpresasCriterio();
		
	}
	
	crearColumnasGrid()
	{
		this.tabla.columnas = [
			{longitud:50, 	titulo:"Id",   	alias:"id", alineacion:"D" },
			{longitud:200, 	titulo:"Empresa",   alias:"empresaNombre", alineacion:"I" },		
			{longitud:200, 	titulo:"Sede",   alias:"sedeNombre", alineacion:"I" },
			{longitud:200, 	titulo:"Area",   alias:"areaNombre", alineacion:"I" },		
			{longitud:200, 	titulo:"Número caja",   alias:"numeroCaja", alineacion:"I" },		
			{longitud:250, 	titulo:"Fecha de inspección",   alias:"fechaInspeccion", alineacion:"I" }
		]
		
		this.tabla.renderizar();		
	}
	
	crearFechas()
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
				
				 $(function() 
				{
				    $.datepicker._updateDatepicker_original = $.datepicker._updateDatepicker;
				    $.datepicker._updateDatepicker = function(inst) {
				        $.datepicker._updateDatepicker_original(inst);
				        var afterShow = this._get(inst, 'afterShow');
				        if (afterShow)
				            afterShow.apply((inst.input ? inst.input[0] : null));  // trigger custom callback
				    }
				    
				    $( "#fechaInicialInputCriterio" ).datepicker({ 
				      afterShow : function(inst) 
				      {
				    		var div = $("#ui-datepicker-div");
				    		var a = div.find("a");
				    		if(a!=null)
					    	  a.attr("href","#");
				      },
				    });
				    
				    $( "#fechaFinalInputCriterio" ).datepicker({ 
					      afterShow : function(inst) 
					      {
					    		var div = $("#ui-datepicker-div");
					    		var a = div.find("a");
					    		if(a!=null)
						    	  a.attr("href","#");
					      },
					    });
				});
			 
			
				
				
				var hoy = new Date();
				var manana = new Date();
				manana.setDate(hoy.getDate() + 1);
				
				var dd = manana.getDate();
				var mm = manana.getMonth()+1; 
				var yyyy = manana.getFullYear();
				
				if(dd<10) 
				{
				    dd='0'+dd;
				} 

				if(mm<10) 
				{
				    mm='0'+mm;
				} 
				
				var fecha =  dd+'/'+mm+'/'+yyyy;
				
				$("#fechaFinalInputCriterio").val(fecha);
				
	}
	
	rendererBotones(registro)
	{
		var html="";
//		if(this._usuario!=null)
//		{
//			if(this._usuario.tipoUsuarioId == TipoUsuario.SUPERUSUARIO)
//			{
				html+="<button class='item' data-toggle='tooltip' data-placement='top' title='Reporte' style='background-color:#d62929;cursor:pointer' onclick='vista.imprimirReporte("+registro.id+")'>";
				html+="<i class='fas fa-file-pdf' style='color:#ffffff;'></i>";
				html+="</button>";
			//}
	//	}
		return html;
	}
	
	imprimirReporte(id)
	{
		var submitForm = this.getNewSubmitForm("php/reportes/reporte.php");
		this.createNewFormElement(submitForm, "inspeccionId", JSON.stringify(id));	 
	    submitForm.target= "_blank";
	    submitForm.submit();
	}
	
	agregar()
	{
		super.agregar();
		$('#nombreInput').focus();
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
			areaId: $('#areaSelectCriterio').val(),
			fechaInicial: this.getFecha($('#fechaInicialInputCriterio').val()),
			fechaFinal: this.getFecha($('#fechaFinalInputCriterio').val()),
			numeroCaja: this.getFecha($('#numeroCajaInputCriterio').val())
		 }
		 return criteriosSeleccion;
	}		

	set modelo(valor)
	{		
		this.modeloEdicion = valor;
		$('#nombreInput').val(this.modeloEdicion.nombre);
		$("input[name=estatus][value=" + this.modeloEdicion.estatus + "]").prop('checked', true);
		this.consultarEmpresas();
	}
	
	get modelo()
	{
		 var modelo = 
		 {		
			 nombre:$('#nombreInput').val(),
			 empresaId:$('#empresaSelect').val(),
			 sedeId:$('#sedeSelect').val(),
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
		this.cargandoOpciones('#empresaSelect');
		this.cargandoOpciones('#sedeSelect');
	}
	
	consultarEmpresas()
	{
		this.cargandoOpciones("#empresaSelect");
		this.cargandoOpciones("#sedeSelect");
		this.presentador.consultarEmpresas();
	}
	
	set empresas(registros)
	{		
		this.cargarOpciones('#empresaSelect', registros, this.modo, this.modeloEdicion, 'empresaId',"");
	}
	
	consultarEmpresasCriterio()
	{
		this.cargandoOpciones("#empresaSelectCriterio");
		this.cargandoOpciones("#sedeSelectCriterio");
		this.presentador.consultarEmpresasCriterio();
	}
	
	set empresasCriterio(registros)
	{		
		this.cargarOpciones('#empresaSelectCriterio', registros);
		//this.consultar();
	}
	
//	cambiarEmpresa()
//	{
//		
//		this.consultarSedes();
//	}
	
	cambiarEmpresaCriterio()
	{
		this.cargandoOpciones("#areaSelectCriterio");
		this.consultarSedesCriterio();
	}
	
	cambiarSedeCriterio()
	{
		this.cargandoOpciones("#areaSelect");
		this.consultarAreasCriterio();
	}
	
	consultarSedes()
	{
		this.cargandoOpciones("#sedeSelect");
		this.presentador.consultarSedes();
	}
	
	consultarSedesCriterio()
	{
		this.cargandoOpciones("#sedeSelectCriterio");
		this.presentador.consultarSedesCriterio();
	}
	
	consultarAreasCriterio()
	{
		this.cargandoOpciones("#areaSelectCriterio");
		this.presentador.consultarAreasCriterio();
	}
	
//	set sedes(registros)
//	{		
//		this.cargarOpciones('#sedeSelect', registros, this.modo, this.modeloEdicion, 'sedeId',"");
//	}

	set sedesCriterio(registros)
	{		
		this.cargarOpciones('#sedeSelectCriterio', registros);
//		if(this.consultoGrid==false)
//		{
//			this.consultar();
//			this.consultoGrid=true;
//		}
	}
	
	set areasCriterio(registros)
	{		
		this.cargarOpciones('#areaSelectCriterio', registros);
		if(this.consultoGrid==false)
		{
			this.consultar();
			this.consultoGrid=true;
		}
	}
	
	consultar()
	{	
		this.configurarGrid([]);
		$("#inspeccionesTabla_processing").show();
		super.consultar();
	}	
	
	set datos(datos)
	{
		this.configurarGrid(datos);
	}
	
	configurarGrid(inspecciones)
	{
		this.inspeccionesTabla.DataTable(
				{
					data: inspecciones,
					"searching": true,
					"destroy": true,
					scrollY: 350,
					"responsive":{details: true},
					"ordering": false,
					"select": true,
					"lengthMenu": [50],
					"processing": true,
					"language":
					{
						"sProcessing": "Procesando...",
						"sLengthMenu": "Mostrar _MENU_ registros",
						"sZeroRecords": "No se encontraron resultados",
						"sEmptyTable": "Ning&uacute;n dato disponible con estos criterios",
						"sInfo": "Del _START_ al _END_ de  _TOTAL_ registros",
						"sInfoEmpty":  "Del 0 al 0 de 0 registros",
						"sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
						"sInfoPostFix": "",
						"sSearch": "Buscar:",
						"sUrl": "",
						"sInfoThousands": ",",
						"sLoadingRecords": "Cargando...",
						"oPaginate":
						{
							"sFirst": "Primero",
							"sLast": "Último",
							"sNext": "Siguiente",
							"sPrevious": "Anterior"
						},
						"oAria":
						{
							"sSortAscending": ": Activar para ordenar la columna de manera ascendente",
							"sSortDescending": ": Activar para ordenar la columna de manera descendente"
						}
					},
					"columns": [
					
						{ "data": "id" },
						{ "data": "empresaNombre" },
						{ "data": "sedeNombre" },
						{ "data": "areaNombre" },
						{ "data": "numeroCaja" },
						{ "data": "fechaInspeccion" },
						{ "defaultContent": 
							"<button class='imprimir btn btn-sm float-left btn-success active' type='button' data-toggle='tooltip' data-placement='top' title='Reporte' style='background-color:#d62929;cursor:pointer' >" +
							"<i class='fas fa-file-pdf' style='color:#ffffff;'></i>" +
							"</button>" 
				  		}

					 
					

					 ],
					"order": [[1, 'asc']],
					 "columnDefs": [				  	
						  	{ "orderable": false, "targets": 0, "className": 'dt-body-left'},
						  	{ "orderable": false, "targets": 1, "className": 'dt-body-left' },
						  	{ "orderable": false, "targets": 2, "className": 'dt-body-left' },
							{ "orderable": false, "targets": 3, "className": 'dt-body-left' },
						  	{ "orderable": false, "targets": 4, "className": 'dt-body-left'},
						  	{ "orderable": false, "targets": 5, "className": 'dt-body-left'},
							{ "orderable": false, "targets": 6, "className": 'dt-body-left'}
						  ]
				});
		var table = this.inspeccionesTabla.DataTable();
		$("#inspeccionesTabla tbody").on("click", "button.imprimir", function(){
			
			var data;
			data = table.row($(this).parents("tr")).data();

			if (data == undefined){
				data = vista.inspeccionesTabla.DataTable().row( { selected: true } ).data();
			}
			if (data != undefined)
			{
				vista.imprimirReporte(data.id);
//				vista.selectedCambio = data;
//				vista.selected = data;
//				vista.mostrarOcultarAlta('CAMBIO');
				//vista.abrirConfiguracion( data );
			}
		});
		$('[data-toggle="tooltip"]').tooltip();
	}

	
}
var vista = new InspeccionesVista(this);

