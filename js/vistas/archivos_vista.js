class ArchivosVista extends CatalogoVista
{		
	constructor()
	{	
		super();
		this.presentador = new ArchivosPresentador(this);
		//this._urlFormulario = "html/formularios/paises.php";
	}
	

	
	crearColumnasGrid()
	{
		this.tabla.columnas = [
			{longitud:200, 	titulo:"Nombre",   alias:"nombre", alineacion:"I" }, 	
			{longitud:50, 	titulo:"Tamaño",   alias:"tamano", alineacion:"C" , itemRenderer: this.rendererTamano}, 	
			{longitud:250, 	titulo:"Fecha",   alias:"fecha", alineacion:"I" },	
		]
		
		//this.tabla.contenidoAdicional = "<button data-toggle='tooltip' data-placemen='bottom' title='Eliminar'  type='button' class='eliminar btn-circle mr-0 botones-icon btn btn-sm float-left btn-danger active'><span  data-toggle='tooltip' class='fa fa-minus-circle fa-lg'></span></button>";

		this.tabla.registros = [];
		
		this.inicializarAnio();
		
		var _this = this;
		$("#eliminarButton").click(function(){
			_this.eliminarArchivos();
		});
	}
	
	eliminarArchivos()
	{
		var numeroArchivos = $("#eliminarButton").attr("data-numeroArchivos") ;
		var bytes = $("#eliminarButton").attr("data-bytes") ;
		
		var texto = "";
		if(numeroArchivos == 1)
			texto ="Se eliminar\u00e1 " + numeroArchivos + " archivo (" + this.formatBytes(bytes,2)+")";
		else
			texto="Se eliminar\u00e1n " + numeroArchivos + " archivos (" + this.formatBytes(bytes,2)+")";
			
		var _this = this;
		swal({
            title: "\u00bfEst\u00E1 seguro de eliminar?",
            text: texto,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Si, eliminar!!",
            cancelButtonText: "No",
            closeOnConfirm: false,
            closeOnCancel: true,
            showLoaderOnConfirm: true,
        },
        function(isConfirm)
        {
            if (isConfirm) 
            {
            	 setTimeout(function(){
            		 _this.presentador.eliminarArchivos();
 	            }, 1000);
            }
        });
		
	}
	
	
	
	inicializarAnio()
	{
		var anos = [];
		var fecha = new Date();
		for(var i = 2017; i <= fecha.getFullYear(); i++)
			anos.push({id:i, nombre:i});
		this.cargarOpciones('#anoSelectCriterio', anos);
		
		$("#mesSelectCriterio").val(fecha.getMonth()+1);
		$("#anoSelectCriterio").val(fecha.getFullYear());
	}
	
	formatBytes(bytes, decimals = 2) {
	    if (!+bytes) return '0 Bytes'
	
	    const k = 1024
	    const dm = decimals < 0 ? 0 : decimals
	    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB']
	
	    const i = Math.floor(Math.log(bytes) / Math.log(k))
	
	    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`
	}
	
	rendererTamano(renglon, type, set)
	{    
		return "<span  style='font-weight:bold;' >"+vista.formatBytes(renglon.tamano,2)+"</span>";
	}
	
	rendererImplementacion(renglon, type, set)
	{    
		return "<span  style='font-weight:bold;' >"+renglon.implementacion+"%</span>";
	}
	
	rendererVerificacion(renglon, type, set)
	{    
		return "<span  style='font-weight:bold;' >"+renglon.verificacion+"%</span>";
	}
	
	
	
	get criteriosSeleccion()
	{
		 var criteriosSeleccion = 
		 {				    
			archivos: $('#archivosSelectCriterio').val(),
			mes: $("#mesSelectCriterio").val(),
			ano: $("#anoSelectCriterio").val(),
			numeroArchivos : $("#eliminarButton").attr("data-numeroArchivos")
			
		 }
		 return criteriosSeleccion;
	}		
	
	
	set datos(datos)
	{
		super.datos = datos;
		
		var bytes = 0;
		for(var i = 0; i < datos.length; i++)
			bytes+= datos[i].tamano;
		
		var texto = "";
		if(datos.length==1)
			texto = datos.length + " archivo";
		else
			texto = datos.length + " archivos";
		$("#eliminarTexto").html("Eliminar " + texto + " ("+this.formatBytes(bytes,2)+")");
		if(datos.length==0)
			$("#eliminarButton").hide();
		else
			$("#eliminarButton").show();
		
		$("#eliminarButton").attr("data-numeroArchivos",datos.length);
		$("#eliminarButton").attr("data-bytes",bytes);
	}

	
}
var vista = new ArchivosVista(this);
$(document).ready(function() 
{
	vista.inicializar();
});

