class BitacoraControlAccesoVista extends CatalogoVista
{
	constructor()
	{
		super();
		this.presentador = new BitacoraControlAccesoPresentador(this);
		this.consultoGrid = false;
	}

	inicializar()
	{
		this.inicializarFechas();

		var _this = this;
		$("#consultarButton").click(function(){
			_this.consultar();
		});

		this.crearColumnasGrid();
		this.crearFechas();
		this.consultarEmpresasCriterio();
	}

	crearColumnasGrid()
	{
		this.tabla.columnas = [
			{longitud:90, 	titulo:"Fecha",   				alias:"fechaInspeccion", 	alineacion:"I", itemRenderer:this.rendererFecha },
			{longitud:90, 	titulo:"Tipo",   				alias:"entradaSalida", 		alineacion:"C", itemRenderer:this.rendererTipo },
			{longitud:90, 	titulo:"Hora de entrada",   	alias:"entradaSalida", 		alineacion:"C", itemRenderer:this.rendererHoraEntrada },
			{longitud:90, 	titulo:"Hora de salida",   		alias:"entradaSalida", 		alineacion:"C", itemRenderer:this.rendererHoraSalida },
			{longitud:200, 	titulo:"Empresa",   			alias:"empresaNombre", 		alineacion:"I" },
			{longitud:180, 	titulo:"Nombre del conductor", 	alias:"chofer", 			alineacion:"I" },
			{longitud:150, 	titulo:"Tipo de unidad",   		alias:"tipoCaja", 			alineacion:"I" },
			{longitud:120, 	titulo:"Placas tractor",   		alias:"placasTractor", 		alineacion:"I" },
			{longitud:120, 	titulo:"Placas caja",   		alias:"placasCaja", 		alineacion:"I" },
			{longitud:120, 	titulo:"Número económico",   	alias:"numeroTractor", 		alineacion:"I" },
			{longitud:120, 	titulo:"Número de remolque",   alias:"numeroContenedor", 	alineacion:"I" },
			{longitud:120, 	titulo:"Número de sello",   	alias:"sello", 				alineacion:"I" },
			{longitud:120, 	titulo:"Factura",   			alias:"factura", 			alineacion:"I" },
			{longitud:120, 	titulo:"Carta Porte",   		alias:"manifiesto", 		alineacion:"I" }
		];

		var _this = this;

		var botonExportarComun = {
			text: '<i class="fa fa-file-excel-o"></i> Exportar',
			exportOptions: {
				format: {
					body: function(data, row, column, node)
					{
						return data == null ? "" : data;
					}
				},
				customizeData: function(data)
				{
					data.body.reverse();
				}
			}
		};

		this.tabla.botones = {
			buttons: [
				$.extend(true, {}, botonExportarComun, {
					extend: 'excel', className: 'btn btn-success'
				}),
				$.extend(true, {}, botonExportarComun, {
					extend: 'pdf', className: 'btn btn-danger', text: '<i class="fa fa-file-pdf-o"></i> Exportar', orientation: 'landscape', pageSize: 'LEGAL'
				})
			],
			dom: {
				button: {
					className: 'btn'
				}
			}
		};

		this.tabla.registros = [];
	}

	rendererFecha(registro)
	{
		if(registro.fechaInspeccion==null || registro.fechaInspeccion=="")
			return "";
		return registro.fechaInspeccion.split(' ')[0];
	}

	rendererTipo(registro)
	{
		if(registro.entradaSalida==null || registro.entradaSalida=="")
			return "";
		var entradaSalida = registro.entradaSalida.toString().toLowerCase();
		if(entradaSalida.indexOf("entrada")!=-1)
			return "Entrada";
		if(entradaSalida.indexOf("salida")!=-1)
			return "Salida";
		return registro.entradaSalida;
	}

	rendererHoraEntrada(registro)
	{
		return BitacoraControlAccesoVista.rendererHora(registro,"entrada");
	}

	rendererHoraSalida(registro)
	{
		return BitacoraControlAccesoVista.rendererHora(registro,"salida");
	}

	static rendererHora(registro,tipo)
	{
		var entradaSalida = registro.entradaSalida==null ? "" : registro.entradaSalida.toString().toLowerCase();
		if(entradaSalida.indexOf(tipo)==-1)
			return "";
		if(registro.fechaInspeccion==null || registro.fechaInspeccion=="")
			return "";
		var partes = registro.fechaInspeccion.split(' ');
		return partes.length>1 ? partes[1] : "";
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
					afterShow.apply((inst.input ? inst.input[0] : null));
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
			dd='0'+dd;

		if(mm<10)
			mm='0'+mm;

		var fecha =  dd+'/'+mm+'/'+yyyy;
		var fechaInicial = '01/'+mm+'/'+yyyy;

		$("#fechaFinalInputCriterio").val(fecha);
		$("#fechaInicialInputCriterio").val(fechaInicial);
	}

	get criteriosSeleccion()
	{
		var criteriosSeleccion =
		{
			empresaId: $('#empresaSelectCriterio').val(),
			sedeId: $('#sedeSelectCriterio').val(),
			areaId: $('#areaSelectCriterio').val(),
			fechaInicial: this.getFecha($('#fechaInicialInputCriterio').val()),
			fechaFinal: this.getFecha($('#fechaFinalInputCriterio').val())
		};
		return criteriosSeleccion;
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
	}

	cambiarEmpresaCriterio()
	{
		this.cargandoOpciones("#areaSelectCriterio");
		this.consultarSedesCriterio();
	}

	cambiarSedeCriterio()
	{
		this.cargandoOpciones("#areaSelectCriterio");
		this.consultarAreasCriterio();
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

	set sedesCriterio(registros)
	{
		this.cargarOpciones('#sedeSelectCriterio', registros);
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
}
var vista = new BitacoraControlAccesoVista();
$(document).ready(function()
{
	vista.inicializar();
});
