class GraficaInspeccionesHoraVista extends CatalogoVista
{		
	constructor(ventana)
	{	
		super(ventana);
		this.presentador = new GraficaInspeccionesHoraPresentador(this);
		this.consulto = false;
		
	}
	
	inicializar()
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
		
		var _this = this;
		$("#consultarButton").click(function(){
			_this.consultar();
		});
		this.crearFechas();
		this.consultarEmpresasCriterio();
	}
	
	set datos(datos)
	{
		this.consulto = true;
		am4core.ready(function() {

			// Themes begin
			am4core.useTheme(am4themes_animated);
			// Themes end

			// Create chart instance
			var chart = am4core.create("grafica", am4charts.XYChart);
			chart.scrollbarX = new am4core.Scrollbar();
			chart.data = datos;

			// Create axes
			var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
			categoryAxis.dataFields.category = "nombre";
			categoryAxis.renderer.grid.template.location = 0;
			categoryAxis.renderer.minGridDistance = 30;
			categoryAxis.renderer.labels.template.horizontalCenter = "right";
			categoryAxis.renderer.labels.template.verticalCenter = "middle";
			categoryAxis.renderer.labels.template.rotation = 0;
			categoryAxis.tooltip.disabled = true;
			categoryAxis.renderer.minHeight = 110;

			var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
			valueAxis.renderer.minWidth = 50;

			// Create series
			var series = chart.series.push(new am4charts.ColumnSeries());
			series.sequencedInterpolation = true;
			series.dataFields.valueY = "valor";
			series.dataFields.categoryX = "nombre";
			series.tooltipText = "[{categoryX}: bold]{valueY}[/]";
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

			series.columns.template.adapter.add("fill", function(fill, target) {
			  return chart.colors.getIndex(target.dataItem.index);
			});

			// Cursor
			chart.cursor = new am4charts.XYCursor();

			}); // end am4core.ready()
	}
	
	crearFechas()
	{
		 $(function() 
		{
		    $.datepicker._updateDatepicker_original = $.datepicker._updateDatepicker;
		    $.datepicker._updateDatepicker = function(inst) {
		        $.datepicker._updateDatepicker_original(inst);
		        var afterShow = this._get(inst, 'afterShow');
		        if (afterShow)
		            afterShow.apply((inst.input ? inst.input[0] : null));  // trigger custom callback
		    }
		    
		    $( "#fechaInputCriterio" ).datepicker({ 
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
		
		var dd = hoy.getDate();
		var mm = hoy.getMonth()+1; 
		var yyyy = hoy.getFullYear();
		
		if(dd<10) 
		{
		    dd='0'+dd;
		} 
	
		if(mm<10) 
		{
		    mm='0'+mm;
		} 
		
		var fecha =  dd+'/'+mm+'/'+yyyy;
		
		$("#fechaInputCriterio").val(fecha);
				
	}

	get criteriosSeleccion()
	{
		 var criteriosSeleccion = 
		 {				    
			empresaId: $('#empresaSelectCriterio').val(),
			sedeId: $('#sedeSelectCriterio').val(),
			areaId: $('#areaSelectCriterio').val(),
			fecha: this.getFecha($('#fechaInputCriterio').val())
			//fechaFinal: this.getFecha($('#fechaFinalInputCriterio').val()),
		 }
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
		this.cargandoOpciones("#areaSelect");
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
		//agregar en ultimo criterio
		if(this.consulto==false)
		{
			this.consultar();
			this.consultoGrid=true;
		}
	}
	
}
var vista = new GraficaInspeccionesHoraVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});
