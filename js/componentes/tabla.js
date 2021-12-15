class Tabla
{
	constructor(id)
	{
		//this._contexto = contexto;
		this._id = id;
		this._columnas = [];
		this._registros = [];
		this._funcionEliminar = null;
		this.rendererBotones = null;
		this._editar = true;
		this._eliminar = true;
		this._contenidoAdicional = null;
		this._anchoContenidoAdicional = 200;
		this._alto =  $("body").height() - 350 ;
		this._buscar = true;
		this._paginacion = true;
		this._textoTablaVacia = "Ning&uacute;n dato disponible en esta tabla";
		this._textoSinRegistros = "No se encontraron resultados";
		this._ajustarColumnas = false;
		this._botones = [];
		this._campoId = "id";
		this.tamanoPagina= 100;
	}
	
	set botones(botones)
	{
		this._botones = botones;
	}
	
	
	refrescar()
	{
		this.registros = this.registros;
	}
	
	set textoTablaVacia(textoTablaVacia)
	{
		this._textoTablaVacia = textoTablaVacia;
	}
	
	set textoSinRegistros(textoSinRegistros)
	{
		this._textoSinRegistros = textoSinRegistros;	
	}
	
	set ajustarColumnas(ajustarColumnas)
	{
		this._ajustarColumnas = ajustarColumnas;
	}
	
	set paginacion(paginacion)
	{
		this._paginacion = paginacion;
	}
	
	set buscar(buscar)
	{
		this._buscar = buscar;
	}
	
	set alto(alto)
	{
		this._alto = alto;
	}
	
	set editar(editar)
	{
		this._editar = editar;
	}
	
	set eliminar(eliminar)
	{
		this._eliminar = eliminar;
	}
	
	set funcionEliminar(funcionEliminar)
	{
		this._funcionEliminar = funcionEliminar;
	}
	
	set columnas(columnas)
	{
		this._columnas = columnas;
	}
	
	get columnas()
	{
		return this._columnas;
	}
	
	set anchoContenidoAdicional(anchoContenidoAdicional)
	{
		this._anchoContenidoAdicional = anchoContenidoAdicional;
	}
	
	set registros(registros)
	{
		
		this._registros = registros;
		var table = this.datatable.DataTable();
		var datatable = $("#" + this._id);
		this._filtro =  $("#" + this._id + "Table_filter").find("input").val(); 
		//recomendacionesTablaTable_filter
		var scrollBody = datatable.find('.dataTables_scrollBody');
		//var scrollBody = $('.dataTables_scrollBody');
		var scrollTop = scrollBody.scrollTop();
		var scroller = table.scroller();
		var scrollPos = scroller.pixelsToRow(scrollTop);
		this.renderizar();
		setTimeout(function(){$($.fn.dataTable.tables(true)).DataTable().columns.adjust();}, 1000);
		if(scrollPos !=null)
			table.draw().scroller.toPosition(this.scrollPos,false);
		$("#" + this._id + "Table_filter").find("input").val(this._filtro); 	
		var e = $.Event('keyup');
		e.keyCode= 13; // enter
		$("#" + this._id + "Table_filter").find("input").trigger(e);
	}
	
	get registros()
	{
		return this._registros;
	}
	
	renderizar()
	{
//		$(this._id).html("");	
//		var html="";
		this.renderizarTabla();
		this.renderizarRegistros();
//		$(this._id).html(html);
		$('[data-toggle="tooltip"]').tooltip({
		    trigger : 'hover',
		    container: 'body'
		}) 
		
		$(".paginate_button").attr("href","#");
		
	}
	
	get columnasDataTable()
	{
		var columnas = [];
		for(var i=0; i< this._columnas.length; i++)
		{
			var columna = this._columnas[i];
			
			var columnaDataTable  =null;
			if(columna.itemRenderer!=null)
				columnaDataTable = {data: columna.itemRenderer};
			else
				columnaDataTable = {data: columna.alias};
		
			columnas.push(columnaDataTable);
			
		}
		if(this._contenidoAdicional!=null && this._registros.length>0)
			columnas.push({defaultContent: this._contenidoAdicional });
		return columnas;
		
	}
	
	get definicionColumnasDataTable()
	{
		var columnas = [];
		for(var i=0; i< this._columnas.length; i++)
		{
			var columna = this._columnas[i];
			var alineacion ="";
			if(columna.alineacion=="I")
				alineacion ="dt-body-left";
			else if(columna.alineacion=="C")
				alineacion ="dt-body-center";
			else if(columna.alineacion=="D")
				alineacion ="dt-body-right";
			var columnaDataTable = {orderable: false, targets : i, className : alineacion +" forceWidth"};
			
			if(columna.longitud!=undefined)
				columnaDataTable.width = columna.longitud +"px";
		
			columnas.push(columnaDataTable);
			
		}
		
		if(this._contenidoAdicional!=null && this._registros.length>0)
		{
			var definicionContenidoAdicional = {orderable: false, targets : this._columnas.length, className : "dt-body-left"};
			if(this._anchoContenidoAdicional!=null)
				definicionContenidoAdicional.width = this._anchoContenidoAdicional +"px";
			columnas.push(definicionContenidoAdicional);
		}
		return columnas;
		
	}
	
	get datatable()
	{
		return $('#'+this._id+"Table");	
	}
	
	set campoId(campoId)
	{
		this._campoId = campoId;
	}
	
	renderizarRegistros()
	{
		var _this = this;
		
		
		$('#'+this._id+"Table").DataTable( {
			  data: this._registros,
	 			
			  "drawCallback": function( settings ) {
				  $(".paginate_button").attr("href","#");
			    },
			    "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
			    	$(nRow).attr('data-id',aData[_this._campoId]);
			    	return nRow;
			    	},
			    dom: 'Bfrtip',
			    "info":true,
		        "searching":this._buscar,
		        "pageLength": this.tamanoPagina,
				"destroy":true,
				"responsive":{details: true},
			    "select":true,
			    "paging":this._paginacion,
			    scrollY: this._alto,
			    "autoWidth":true,
			    "ordering":false,
				columns: this.columnasDataTable,
				buttons :this._botones,
				columnDefs: this.definicionColumnasDataTable,
				fixedColumns: this._ajustarColumnas,
			    "language": {	         	 
				 	select: {
				            rows: "%d registros seleccionados"
				        },
					"sProcessing":     "Procesando...",
					"sLengthMenu":     "Mostrar _MENU_ registros",
					"sZeroRecords":    this._textoSinRegistros,
					"sEmptyTable":     this._textoTablaVacia,
					"sInfo":           "Del _START_ al _END_ de  _TOTAL_ registros",
					"sInfoEmpty":      "Del 0 al 0 de 0 registros",
					"sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
					"sInfoPostFix":    "",
					"sSearch":         "Buscar:",
					"sUrl":            "",
					"sInfoThousands":  ",",
					"sLoadingRecords": "Cargando...",
					"oPaginate": {
					    "sFirst":    "Primero",
					    "sLast":     "Último",
					    "sNext":     "Siguiente",
					    "sPrevious": "Anterior"
					},
					"oAria": {
					    "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
					    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
					}
				 },
			   
			  } );
		
		$('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
			   $($.fn.dataTable.tables(true)).DataTable()
			      .columns.adjust();
			});
	
//		$('#'+this._id+"Table").on( 'page.dt', function () 
//		{
//			$(".paginate_button").attr("href","#");
//		} );
		
		
	}
	
//	renderizarRegistro(registro)
//	{
//		var html="";
//		for(var i=0; i< this._columnas.length; i++)
//		{
//			var columna = this._columnas[i];
//			var dato = registro[columna.alias];
//			var attr="";
//			if(columna.class!=undefined)
//				attr="class='"+columna.class+"'";
//			var attrSpan="";
//			if(columna.classSpan!=undefined)
//				attrSpan="class='"+columna.classSpan+"'";
//			if(columna.itemRenderer!=undefined)
//			{
//				if(this._contexto!=null)
//				{
//					var renderer = columna.itemRenderer.call( this._contexto, registro, columna);
//					html+="<td "+attr+"><span "+attrSpan+">"+ renderer +"</span></td>";
//				}
//			}
//			else
//				html+="<td "+attr+"><span "+attrSpan+">"+ dato +"</span></td>";
//		}
//		
//		html+="<td>";
//		html+="<div class='table-data-feature'>";
//
//		if(this._editar)
//		{
//			html+="<button class='item' data-toggle='tooltip' data-placement='top' title='Editar' style='background-color:#4e6db4;cursor:pointer' onclick='vista.editar("+registro.id+")'>";
//			html+="<i class='zmdi zmdi-edit' style='color:#ffffff;'></i>";
//			html+="</button>";
//		}
//		
//		if(this._eliminar)
//		{
//			html+="<button class='item' data-toggle='tooltip' data-placement='top' title='Eliminar' style='background-color:#b30132;cursor:pointer'onclick='vista.eliminar("+registro.id+")'>";
//			html+="<i class='zmdi zmdi-delete' style='color:#ffffff'></i>";
//			html+="</button>";
//		}
//		
//		if(this._contexto!=null)
//		{
//			if(this.rendererBotones!=null)
//				html += this.rendererBotones.call( this._contexto, registro);
//		}
//		
////		html+="<button class='item' data-toggle='tooltip' data-placement='top' title='' data-original-title='More'>";
////		html+="<i class='zmdi zmdi-more'></i>";
////		html+="</button>";
//		html+="</div>";
//		html+="</td>";
//		
//		return html;
//	}
	
	
	set contenidoAdicional(contenidoAdicional)
	{
		this._contenidoAdicional = contenidoAdicional;
	}
	
	
	renderizarTabla()
	{
		var html="<table id='"+this._id+"Table' class='table table-striped'>";
		html+="<thead>";
		html+="<tr>"; 
		
		for(var i=0; i< this._columnas.length; i++)
		{
			var columna = this._columnas[i];
		
			html+="<th>"+ columna.titulo +"</th>";
		}
		if(this._contenidoAdicional!=null && this._registros.length>0)
			html+="<th></th>";

		html+="</tr>";
		html+="</thead>";
		html+="</table>";
		
		$("#"+this._id).html(html);	
		
		
	}


}