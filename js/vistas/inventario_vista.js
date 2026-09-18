class InventarioVista extends CatalogoVista
{
	constructor()
	{
		super();
		this.presentador = new InventarioPresentador(this);
		this.consultoGrid = false;
		this.registroEntradaSeleccionado = null;
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
		this.inicializarEventosAjusteManual();
		this.consultarEmpresasCriterio();
	}

	crearColumnasGrid()
	{
		this.tabla.columnas = [
			{longitud:100, 	titulo:"Fecha de entrada",   	alias:"fechaEntrada", 		alineacion:"I" },
			{longitud:200, 	titulo:"Empresa",   			alias:"empresaNombre", 		alineacion:"I" },
			{longitud:180, 	titulo:"Nombre del conductor", 	alias:"chofer", 			alineacion:"I" },
			{longitud:150, 	titulo:"Tipo de unidad",   		alias:"tipoCaja", 			alineacion:"I" },
			{longitud:120, 	titulo:"Placas tractor",   		alias:"placasTractor", 		alineacion:"I" },
			{longitud:120, 	titulo:"Placas caja",   		alias:"placasCaja", 		alineacion:"I" },
			{longitud:120, 	titulo:"Número económico",   	alias:"numeroTractor", 		alineacion:"I" },
			{longitud:120, 	titulo:"Número de remolque",   alias:"numeroContenedor", 	alineacion:"I" },
			{longitud:120, 	titulo:"Número de sello",   	alias:"sello", 				alineacion:"I" },
			{longitud:120, 	titulo:"Factura",   			alias:"factura", 			alineacion:"I" },
			{longitud:120, 	titulo:"Carta Porte",   		alias:"manifiesto", 		alineacion:"I" },
			{longitud:120, 	titulo:"Acciones",   			alias:"inspeccionId", 		alineacion:"C", itemRenderer:this.rendererAccionAjusteManual }
		];

		var botonExportarComun = {
			text: '<i class="fa fa-file-excel-o"></i> Exportar',
			exportOptions: {
				columns: ':not(:last-child)',
				format: {
					body: function(data, row, column, node)
					{
						return data == null ? "" : data;
					}
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

	rendererAccionAjusteManual(registro)
	{
		return "<button type='button' class='btn btn-sm btn-warning btn-ajuste-manual' data-inspeccion-id='" + registro.inspeccionId + "'>Ajuste manual</button>";
	}

	inicializarEventosAjusteManual()
	{
		var _this = this;
		$(document).on('click', '.btn-ajuste-manual', function(){
			var inspeccionId = $(this).data('inspeccion-id');
			var registro = _this.tabla.registros.find(function(r){ return r.inspeccionId == inspeccionId; });
			if(registro!=undefined)
				_this.abrirModalAjusteManual(registro);
		});
	}

	abrirModalAjusteManual(registroEntrada)
	{
		this.registroEntradaSeleccionado = registroEntrada;
		$('#ajusteFechaSalidaInput').val(this.getFechaHoraActualLocal());
		$('#ajusteMotivoInput').val('');
		$('#modalAjusteManual').modal('show');
	}

	getFechaHoraActualLocal()
	{
		var ahora = new Date();
		var offsetMinutos = ahora.getTimezoneOffset();
		var fechaLocal = new Date(ahora.getTime() - (offsetMinutos * 60000));
		return fechaLocal.toISOString().slice(0, 16);
	}

	guardarAjusteManual()
	{
		var entrada = this.registroEntradaSeleccionado;
		if(entrada==null)
			return;

		var fechaSalida = $('#ajusteFechaSalidaInput').val();
		var motivo = $('#ajusteMotivoInput').val();

		if(fechaSalida=="")
		{
			this.mostrarMensajeError("Error","Indica la fecha y hora de salida.");
			return;
		}
		if(motivo=="")
		{
			this.mostrarMensajeError("Error","Indica el motivo del ajuste manual.");
			return;
		}

		var sinNulos = function(valor){ return valor==null ? "" : valor; };

		var inspeccionSalida = {
			sedeId: entrada.sedeId,
			entradaSalida: "Salida",
			fechaInspeccion: fechaSalida.replace('T',' ') + ':00',
			chofer: sinNulos(entrada.chofer),
			transportista: sinNulos(entrada.transportista),
			numeroTractor: sinNulos(entrada.numeroTractor),
			numeroContenedor: sinNulos(entrada.numeroContenedor),
			placasTractor: sinNulos(entrada.placasTractor),
			placasCaja: sinNulos(entrada.placasCaja),
			colorTractor: sinNulos(entrada.colorTractor),
			colorCaja: sinNulos(entrada.colorCaja),
			tipoCaja: sinNulos(entrada.tipoCaja),
			sello: sinNulos(entrada.sello),
			manifiesto: sinNulos(entrada.manifiesto),
			factura: sinNulos(entrada.factura),
			tipoInspeccionId: entrada.tipoInspeccionId,
			usuarioId: 0,
			ajusteManual: 1,
			ajusteManualMotivo: motivo
		};

		this.mostrarIndicador();
		this.presentador.registrarAjusteManual(this, this.guardarAjusteManualResultado, inspeccionSalida);
	}

	guardarAjusteManualResultado(resultado)
	{
		this.ocultarIndicador();
		if(resultado.mensajeError=="")
		{
			$('#modalAjusteManual').modal('hide');
			this.mostrarMensaje("Notificación","El ajuste manual se registró correctamente.");
			this.registroEntradaSeleccionado = null;
			this.consultar();
		}
		else
			this.mostrarMensajeError("Error",resultado.mensajeError);
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
		this.consultarSedesCriterio();
	}

	consultarSedesCriterio()
	{
		this.cargandoOpciones("#sedeSelectCriterio");
		this.presentador.consultarSedesCriterio();
	}

	set sedesCriterio(registros)
	{
		this.cargarOpciones('#sedeSelectCriterio', registros);
		if(this.consultoGrid==false)
		{
			this.consultar();
			this.consultoGrid=true;
		}
	}
}
var vista = new InventarioVista();
$(document).ready(function()
{
	vista.inicializar();
});
