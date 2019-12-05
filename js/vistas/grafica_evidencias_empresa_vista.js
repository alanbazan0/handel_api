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
		
		if(anos.length==0)
		{
			anos.push({id:fecha.getFullYear(), nombre:fecha.getFullYear()})
			
		}
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
			categoryAxis.renderer.labels.template.horizontalCenter = "right";
			categoryAxis.renderer.labels.template.verticalCenter = "middle";
			//categoryAxis.renderer.labels.template.rotation = 270;
			categoryAxis.tooltip.disabled = true;
			categoryAxis.renderer.minHeight = 110;
			
			let label = categoryAxis.renderer.labels.template;
			label.wrap = true;
			label.maxWidth = 120;
			
			
			

			var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
			valueAxis.renderer.minWidth = 50;
			valueAxis.max = 100;

			// Create series
			var series = chart.series.push(new am4charts.ColumnSeries());
			series.sequencedInterpolation = true;
			series.dataFields.valueY = "porcentajeCumplimiento";
			series.dataFields.categoryX = "nombre";
			series.tooltipText = "[{categoryX}: bold]{valueY}%[/]";
			series.columns.template.strokeWidth = 0;

			series.tooltip.pointerOrientation = "vertical";

			series.columns.template.column.cornerRadiusTopLeft = 10;
			series.columns.template.column.cornerRadiusTopRight = 10;
			series.columns.template.column.fillOpacity = 0.8;
			
			
			

			// on hover, make corner radiuses bigger
			var hoverState = series.columns.template.column.states.create("hover");
			hoverState.properties.cornerRadiusTopLeft = 0;
			hoverState.properties.cornerRadiusTopRight = 0;
			hoverState.properties.fillOpacity = 1;

			series.columns.template.adapter.add("fill", function(fill, target) 
			{
				if (target.dataItem.valueY >= 0 && target.dataItem.valueY < 51) 
				    return am4core.color("#dd4b39");
				else if (target.dataItem.valueY >= 51 && target.dataItem.valueY < 100)
					 return am4core.color("#f39c12");
				else if (target.dataItem.valueY >= 100)
					return am4core.color("#00a65a");
				else
					return fill;
			});
			
			

			// Cursor
			chart.cursor = new am4charts.XYCursor();
			
			chart.scrollbarX = new am4core.Scrollbar();
			chart.events.on("ready", function (e) 
				{
					try
					{
						categoryAxis.zoomToIndexes(0, 5);
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