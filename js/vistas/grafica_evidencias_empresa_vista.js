class GraficaEvidenciasEmpresaVista extends CatalogoVista
{		
	constructor()
	{	
		super();
		this.presentador = new GraficaEvidenciasEmpresaPresentador(this);
	}
	
	inicializar()
	{
		$("body").data("_this",this);
		//this.crearTablas();
		

		var _this = this;
		$("#consultarButton").click(function(){
			_this.consultar();
		});
		
		if($("#enviarMensajeLink").length>0)
			$("#enviarMensajeLink").click(this.enviarMensajeLinkClick)
		
		this.consultarEmpresasCriterio();
		
		
	}
	
	consultarAnos()
	{
		this.presentador.consultarAnos();
	}
	
	set anos(anos)
	{
		var fecha = new Date();
		
		if(anos.length==0 || !ArrayUtils.existsWithValues("id",[fecha.getFullYear()],anos))
			anos.push({id:fecha.getFullYear(), nombre:fecha.getFullYear()});
		this.cargarOpciones('#anoSelectCriterio', anos);
		
		$("#mesSelectCriterio").val(fecha.getMonth()+1);
		$("#anoSelectCriterio").val(fecha.getFullYear());
		
		$("#criteriosSeleccionDiv").show();
		
		
		
		this.consultar();
		
	}

	
	
	consultarCombos()
	{
		this.consultarEmpresas();
		this.consultarPaises();
	}
	
	
	
	
	set modelo(valor)
	{		
		this.modeloEdicion = valor;
		$('#nombreInput').val(this.modeloEdicion.nombre);
		$('#nombreCortoInput').val(this.modeloEdicion.nombreCorto);
		$('#direccionInput').val(this.modeloEdicion.direccion);
		$("input[name=estatus][value=" + this.modeloEdicion.estatus + "]").prop('checked', true);
		this.consultarCombos();
	}
	
	consultarEmpresasCriterio()
	{
		this.cargandoOpciones("#empresaSelectCriterio");
		this.presentador.consultarEmpresasCriterio();
	}
	
	
	
	set empresasCriterio(registros)
	{		
		this.cargarOpciones('#empresaSelectCriterio', registros);
		this.consultarAnos();
	}
	
	get criteriosSeleccion()
	{
		var criteriosSeleccion = 
		{
			empresaId:  $('#empresaSelectCriterio').val(),
			mes:  $('#mesSelectCriterio').val(),
			ano: $('#anoSelectCriterio').val()
		};
		return criteriosSeleccion;
	}

	set porcentajesEmpresas(porcentajesAreas)
	{
		am4core.ready(function() {

			// Themes begin
			//am4core.useTheme(am4themes_kelly);
			am4core.useTheme(am4themes_animated);
			// Themes end

			// Create chart instance
			var chart = am4core.create("empresasChart", am4charts.XYChart);
			chart.scrollbarX = new am4core.Scrollbar();
			chart.data = porcentajesAreas;

			// Create axes
			var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
			categoryAxis.dataFields.category = "nombre";
			categoryAxis.renderer.grid.template.location = 0;
			categoryAxis.renderer.minGridDistance = 30;
			categoryAxis.renderer.labels.template.horizontalCenter = "middle";
			categoryAxis.renderer.labels.template.verticalCenter = "middle";
			categoryAxis.renderer.labels.template.rotation = 315;
			categoryAxis.tooltip.disabled = true;
			categoryAxis.renderer.minHeight = 110;
			
			let label = categoryAxis.renderer.labels.template;
			label.wrap = true;
			label.maxWidth = 120;
			
			
			

			var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
			valueAxis.renderer.minWidth = 50;
			valueAxis.min = 0;
			valueAxis.max = 100;

			EvidenciasSeries.crear(chart,"nombre","nombre");
			

			// Cursor
			chart.cursor = new am4charts.XYCursor();
			
			chart.scrollbarX = new am4core.Scrollbar();
			chart.events.on("ready", function (e) 
				{
					try
					{
						//categoryAxis.zoomToIndexes(0, 5);
					}
					catch(e)
					{
						
					}
					
				});

			}); // end am4core.ready()


		

	}
	
	
}

var vista = new GraficaEvidenciasEmpresaVista(this);	
$(document).ready(function() 
{
	vista.inicializar();
});