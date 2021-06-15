class EvidenciasSeries
{
	static crear(chart,categoryX, name)
	{
		var series = chart.series.push(new am4charts.ColumnSeries());
			series.stacked = true;
			series.sequencedInterpolation = true;
			series.dataFields.valueY = "porcentajeEnviadas";
			series.dataFields.categoryX = categoryX;
			series.tooltipText = "Enviadas : {valueY}% ({enviadas})";
			series.columns.template.strokeWidth = 0;
			series.tooltip.pointerOrientation = "vertical";
			
			series.columns.template.column.fillOpacity = 0.8;
			series.columns.template.adapter.add("fill", function(fill, target) 
			{
				return am4core.color("#00a65a");
			});
			
			// Create serie
			var series = chart.series.push(new am4charts.ColumnSeries());
			series.stacked = true;
			series.sequencedInterpolation = true;
			series.dataFields.valueY = "porcentajeJustificadas";
			series.dataFields.categoryX =categoryX;
			series.tooltipText = "Justificadas: {valueY}% ({justificadas})";
			series.columns.template.strokeWidth = 0;
			series.tooltip.pointerOrientation = "vertical";
			series.columns.template.column.cornerRadiusTopLeft = 10;
			series.columns.template.column.cornerRadiusTopRight = 10;
			series.columns.template.column.fillOpacity = 0.8;
			series.columns.template.adapter.add("fill", function(fill, target) 
			{
				//if (target.dataItem.valueY >= 0 && target.dataItem.valueY < 51) 
				//    return am4core.color("#dd4b39");
				//else if (target.dataItem.valueY >= 51 && target.dataItem.valueY < 100)
					 return am4core.color("#f39c12");
				//else if (target.dataItem.valueY >= 100)
				//	return am4core.color("#00a65a");
				//else
				//	return fill;
			});
			
			var series = chart.series.push(new am4charts.ColumnSeries());
			series.stacked = true;
			series.sequencedInterpolation = true;
			series.dataFields.valueY = "cero";
			series.dataFields.categoryX = categoryX;
			series.tooltipText = "{"+name+"}: {porcentajeCumplimiento}% ({cumplidas}/{total})";
			series.columns.template.strokeWidth = 0;
			series.tooltip.pointerOrientation = "vertical";
			series.columns.template.column.cornerRadiusTopLeft = 10;
			series.columns.template.column.cornerRadiusTopRight = 10;
			series.columns.template.column.fillOpacity = 0.8;
			series.columns.template.adapter.add("fill", function(fill, target) 
			{
				//if (target.dataItem.valueY >= 0 && target.dataItem.valueY < 51) 
				//    return am4core.color("#dd4b39");
				//else if (target.dataItem.valueY >= 51 && target.dataItem.valueY < 100)
					 return am4core.color("#3c8dbc");
				//else if (target.dataItem.valueY >= 100)
				//	return am4core.color("#00a65a");
				//else
				//	return fill;
			});
	}
}