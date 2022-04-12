class RevisionProcesosSeries
{
	static crear(chart,categoryX, name)
	{
		// Create serie
			var series = chart.series.push(new am4charts.ColumnSeries());
			series.stacked = false;
			series.sequencedInterpolation = true;
			series.dataFields.valueY = "pendientes";
			series.dataFields.categoryX =categoryX;
			series.tooltipText = "Asignados: {valueY}";
			series.columns.template.strokeWidth = 0;
			series.tooltip.pointerOrientation = "vertical";
			series.columns.template.column.cornerRadiusTopLeft = 10;
			series.columns.template.column.cornerRadiusTopRight = 10;
			series.columns.template.column.fillOpacity = 0.8;
			series.columns.template.adapter.add("fill", function(fill, target) 
			{
				 return am4core.color("#4377c2");
			});
			
		var series = chart.series.push(new am4charts.ColumnSeries());
			series.stacked = false;
			series.sequencedInterpolation = true;
			series.dataFields.valueY = "revisados";
			series.dataFields.categoryX = categoryX;
			series.tooltipText = "Revisados : {valueY}";
			series.columns.template.strokeWidth = 0;
			series.tooltip.pointerOrientation = "vertical";
			series.columns.template.column.cornerRadiusTopLeft = 10;
			series.columns.template.column.cornerRadiusTopRight = 10;
			series.columns.template.column.fillOpacity = 0.8;
			series.columns.template.adapter.add("fill", function(fill, target) 
			{
				return am4core.color("#fb7435");
			});
			
			
			
	}
}