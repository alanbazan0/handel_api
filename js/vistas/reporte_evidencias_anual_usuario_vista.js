class ReporteEvidenciasAnualUsuarioVista extends CatalogoVista
{		
	constructor()
	{	
		super();
		this.presentador = new ReporteEvidenciasAnualUsuarioPresentador(this);
	}
	
	inicializar()
	{
		this.crearColumnasGrid();
		$("body").data("_this",this);
		//this.crearTablas();
		

		var _this = this;
		$("#consultarButton").click(function(){
			_this.consultar();
		});
		
		if($("#enviarMensajeLink").length>0)
			$("#enviarMensajeLink").click(this.enviarMensajeLinkClick)
		
		this.consultoGrid = false;
		this.consultarAnos();
		
		
		
	}
	
	crearColumnasGrid()
	{
		 var buttonCommon = {
				   text:      '<i class="fa fa-file-excel-o"></i>',
			        exportOptions: {
			            format: {
			                body: function ( data, row, column, node ) {
			                   if(column==0)
			                	   return data;
			                   else
				               {
			                	   var html = $.parseHTML( data );
			                	   var span = $(html).find("span");
			                	   var title = span.attr("title");
			                	   return title;
				               }
			                }
			            }
			        }
			    };
		 
//		this.tabla.botones = [{
//            extend:    'excelHtml5',
//            text:      '<i class="fa fa-file-excel-o"></i>',
//            titleAttr: 'Excel'
//        }];
		 
		 this.tabla.botones= [
	            $.extend( true, {}, buttonCommon, {
	                extend: 'excelHtml5'
	            } )
	        ];
		 
		this.tabla.columnas = [
			//{longitud:50, 	titulo:"Id",   	alias:"procedimientoId", alineacion:"I" },
			{longitud:200, 	titulo:"Evidencia",   	alias:"procedimientoNombre", alineacion:"I" },
			{longitud:100, 	titulo:"Enero", alias:"mes1", alineacion:"I",itemRenderer: function(renglon, type, set){return vista.renderMes(renglon,1);} },
			{longitud:100, 	titulo:"Febrero", alias:"mes2", alineacion:"I",itemRenderer: function(renglon, type, set){return vista.renderMes(renglon,2);} },
			{longitud:100, 	titulo:"Marzo", alias:"mes3", alineacion:"I" ,itemRenderer: function(renglon, type, set){return vista.renderMes(renglon,3);} },
			{longitud:100, 	titulo:"Abril",alias:"mes4", alineacion:"I",itemRenderer: function(renglon, type, set){return vista.renderMes(renglon,4);} },
			{longitud:100, 	titulo:"Mayo", alias:"mes5", alineacion:"I" ,itemRenderer: function(renglon, type, set){return vista.renderMes(renglon,5);} },
			{longitud:100, 	titulo:"Junio",alias:"mes6", alineacion:"I",itemRenderer: function(renglon, type, set){return vista.renderMes(renglon,6);} },
			{longitud:100, 	titulo:"Julio",alias:"mes7", alineacion:"I" ,itemRenderer: function(renglon, type, set){return vista.renderMes(renglon,7);} },
			{longitud:100, 	titulo:"Agosto",alias:"mes8", alineacion:"I",itemRenderer: function(renglon, type, set){return vista.renderMes(renglon,8);} },
			{longitud:100, 	titulo:"Septiembre",alias:"mes9", alineacion:"I" ,itemRenderer: function(renglon, type, set){return vista.renderMes(renglon,9);} },
			{longitud:100, 	titulo:"Octubre",alias:"mes10", alineacion:"I" ,itemRenderer: function(renglon, type, set){return vista.renderMes(renglon,10);} },
			{longitud:100, 	titulo:"Noviembre",alias:"mes11", alineacion:"I" ,itemRenderer: function(renglon, type, set){return vista.renderMes(renglon,11);} },
			{longitud:100, 	titulo:"Diciembre",alias:"mes12", alineacion:"I",itemRenderer: function(renglon, type, set){return vista.renderMes(renglon,12);} },
			
		];
		
		
//		this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Editar'  type='button' class='editar btn-circle mr-0 botones-icon btn btn-sm float-left btn-info active'><span  data-toggle='tooltip' class='fa fa-edit fa-lg'></span></button>"+
//									"<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";

		this.tabla.registros = [];
	}
	

	renderMes(renglon,mes)
	{
		var campoMes ="mes" + mes;
		var valorMes = renglon[campoMes];
		var contenido = "";
		if(valorMes=="S")
			contenido += "<center><span data-toggle='tooltip' title='Si' data-placemen='bottom' class='archivo fas fa-check fa-lg' style='color:#60d836;cursor:pointer'></span></center>";
		else if(valorMes=="N")
			contenido += "<center><span data-toggle='tooltip' title='No' data-placemen='bottom' class='archivo fas fa-times fa-lg' style='color:#fe2500;cursor:pointer'></span></center>";
		else  if(valorMes=="J")
		{
			var justificacion ="";
			var campoEvidencia = "evidencia" +mes;
			var evidecia = renglon[campoEvidencia];
			if(evidecia!=undefined)
				justificacion = evidecia.justificacionNombre;
			contenido += "<center><span data-toggle='tooltip' title='"+justificacion+"' data-placemen='bottom' class='archivo fas fa-circle fa-lg' style='color:#f9c320;cursor:pointer'></span></center>";
		}
		else
			contenido ="";
		return contenido;
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
		
		
		this.consultarEmpresasCriterio();
		
		//this.consultar();
		
	}

	cambiarEmpresaCriterio()
	{
		this.consultarSedesCriterio();
	}

	cambiarSedeCriterio()
	{
		this.consultarDepartamentosCriterio();
	}
	
	cambiarDepartamentoCriterio()
	{
		this.consultarUsuariosCriterio();
	}

	
	consultarEmpresasCriterio()
	{
		this.cargandoOpciones("#empresaSelectCriterio");
		this.presentador.consultarEmpresasCriterio();
	}
	
	consultarSedesCriterio()
	{
		this.cargandoOpciones("#sedeSelectCriterio");
		this.cargandoOpciones("#departamentoSelectCriterio");
		this.presentador.consultarSedesCriterio();
	}
	
	consultarDepartamentosCriterio()
	{
		this.cargandoOpciones("#departamentoSelectCriterio");
		this.presentador.consultarDepartamentosCriterio();
	}
	

	consultarUsuariosCriterio()
	{
		this.cargandoOpciones("#usuarioSelectCriterio");
		this.presentador.consultarUsuariosCriterio();
	}
	
	set sedesCriterio(registros)
	{		
		this.cargarOpciones('#sedeSelectCriterio', registros);
	}
	
	set departamentosCriterio(registros)
	{		
		this.cargarOpciones('#departamentoSelectCriterio', registros);
//		if(this.consultoGrid==false)
//		{
//			this.consultar();
//			this.consultoGrid=true;
//		}
	}

	
	set usuariosCriterio(registros)
	{		
		//this.cargarOpciones('#usuarioSelectCriterio', registros);
		this.cargarOpciones('#usuarioSelectCriterio', registros, null, null, null, null,  "nombreCompleto");
//		if(this.consultoGrid==false)
//		{
//			this.consultar();
//			this.consultoGrid=true;
//		}	
	}
	
	set empresasCriterio(registros)
	{		
		this.cargarOpciones('#empresaSelectCriterio', registros);
	}
	
	get criteriosSeleccion()
	{
		var criteriosSeleccion = 
		{
			empresaId:  $('#empresaSelectCriterio').val(),
			sedeId:  $('#sedeSelectCriterio').val(),
			departamentoId:  $('#departamentoSelectCriterio').val(),
			usuarioId:  $('#usuarioSelectCriterio').val(),
			ano: $('#anoSelectCriterio').val()
		};
		return criteriosSeleccion;
	}

	set porcentajesUsuarios(porcentajesAreas)
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
			categoryAxis.dataFields.category = "nombreId";
			categoryAxis.renderer.grid.template.location = 0;
			categoryAxis.renderer.minGridDistance = 30;
			categoryAxis.renderer.labels.template.horizontalCenter = "middle";
			categoryAxis.renderer.labels.template.verticalCenter = "middle";
			categoryAxis.renderer.labels.template.rotation = 315;
			categoryAxis.tooltip.disabled = true;
			categoryAxis.renderer.minHeight = 110;
			categoryAxis.renderer.labels.template.adapter.add("textOutput", function(text) {
				  return text.replace(/ \(.*/, "");
				});
			
			let label = categoryAxis.renderer.labels.template;
			label.wrap = true;

		
			

			var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
			valueAxis.renderer.minWidth = 50;
			valueAxis.min = 0;
			valueAxis.max = 100;

			// Create series
			var series = chart.series.push(new am4charts.ColumnSeries());
			series.sequencedInterpolation = true;
			series.dataFields.valueY = "porcentajeCumplimiento";
			series.dataFields.categoryX = "nombreId";
			series.tooltipText = "{nombreCompleto} ({nombreUsuario}) : {valueY}% ({cumplidas}/{total})";
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
						var zoom = 6;
						//if(_this.porcentajesAreas.lengh>=zoom)
						//categoryAxis.zoomToIndexes(0, zoom);
					}
					catch(e)
					{
						
					}
					
				});

			}); // end am4core.ready()


		

	}
	
	
	
}

var vista = new ReporteEvidenciasAnualUsuarioVista(this);	
$(document).ready(function() 
{
	vista.inicializar();
});