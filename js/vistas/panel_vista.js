class PanelVista extends Vista
{
	constructor(ventana)
	{
		super(ventana);
		this.presentador = new PanelPresentador(this);
		this.inspeccionesEmpresaChart = null;
		this.inspeccionesMesChart = null;
		this.inspeccionesSedeChart = null;
	}
	
	onLoad(usuario)
	{
		if(document.getElementById("inspeccionesEmpresaChart"))
			this.inspeccionesEmpresaChart = echarts.init(document.getElementById("inspeccionesEmpresaChart"));
		if(document.getElementById("inspeccionesMesChart"))
			this.inspeccionesMesChart = echarts.init(document.getElementById("inspeccionesMesChart"));
		if(document.getElementById("inspeccionesSedeChart"))
			this.inspeccionesSedeChart = echarts.init(document.getElementById("inspeccionesSedeChart"));
		this.consultar();
//		this.inspeccionesEmpresa = [];
//		this.inspeccionesSede = [];
//		this.inspeccionesMes = [];
		
		
	}
	
	consultar()
	{
		this.presentador.consultar();
//		this.mostrarIndicadorGrafica(this.inspeccionesEmpresaChart);
//		this.mostrarIndicadorGrafica(this.inspeccionesMesChart);
//		this.mostrarIndicadorGrafica(this.inspeccionesSedeChart);
		
		this.consultarInspeccionesEmpresa();
		this.consultarInspeccionesMes();
		this.consultarInspeccionesSede();
	}
	
	consultarInspeccionesEmpresa()
	{
		 var chart = document.getElementById("inspeccionesEmpresaChart");
		 if(chart)
			 this.presentador.consultarInspeccionesEmpresa();
	}
	
	consultarInspeccionesMes()
	{
		 var chart = document.getElementById("inspeccionesMesChart");
		 if(chart)
			 this.presentador.consultarInspeccionesMes();
	}
	
	consultarInspeccionesSede()
	{
		 var chart = document.getElementById("inspeccionesSedeChart");
		 if(chart)
			 this.presentador.consultarInspeccionesSede();
	}
	
	set indicadores(indicadores)
	{
		$("#empresas").html(indicadores.empresas);
		$("#sedes").html(indicadores.sedes);
		$("#usuarios").html(indicadores.usuarios);
		$("#puestos").html(indicadores.puestos);
		$("#areas").html(indicadores.areas);
		$("#inspecciones").html(indicadores.inspecciones);
	}

	abrirOpcion(opcion)
	{
		var submitForm = this.getNewSubmitForm(opcion);
		//this.createNewFormElement(submitForm, "usuario", JSON.stringify(usuario));	 
	    submitForm.target= "_self";
	    submitForm.submit();
	}
	
	set inspeccionesEmpresa(inspecciones)
	{
		
		try {
			var nombres = this.getLista("nombre",inspecciones);
		    var valores = this.getLista("valor",inspecciones);
		    //bar chart
//		    var ctx = document.getElementById("inspeccionesEmpresaChart");
//		    if (ctx) {
//		      ctx.height = 80;
//		      var myChart = new Chart(ctx, {
//		        type: 'bar',
//		        defaultFontFamily: 'Poppins',
//		        data: {
//		          labels: nombres,
//		          datasets: [
//		            {
//		              label: "Inspecciones",
//		              data: valores,
//		              borderColor: "rgba(0, 123, 255, 0.9)",
//		              borderWidth: "0",
//		              backgroundColor: "rgba(0, 123, 255, 0.5)",
//		              fontFamily: "Poppins"
//		            }
//		          ]
//		        },
//		        options: {
//		          legend: {
//		            position: 'top',
//		            labels: {
//		              fontFamily: 'Poppins'
//		            }
//
//		          },
//		          scales: {
//		            xAxes: [{
//		              ticks: {
//		                fontFamily: "Poppins"
//
//		              }
//		            }],
//		            yAxes: [{
//		              ticks: {
//		                beginAtZero: true,
//		                fontFamily: "Poppins"
//		              }
//		            }]
//		          }
//		        }
//		      });
//		    }


		    var app = {};
		    var option = {
		        color: ['#ffdd1a'],
		        tooltip : {
		            trigger: 'axis'
		        },
		        legend: {
		            data:['Inspecciones']
		        },
		        calculable : true,
		        xAxis : [
		            {
		                type : 'category',
		                data : nombres
		            }
		        ],
		        yAxis : [
		            {
		                type : 'value'
		            }
		        ],
		        series : [
		            {
		                name:'Inspecciones',
		                type:'bar',
		                data:valores
		            }
		        ]
		    };

		    if (option && typeof option === "object") 
		    {
		    	this.inspeccionesEmpresaChart.hideLoading();
		    	this.inspeccionesEmpresaChart.setOption(option, false);
		    }

		  } catch (error) {
		    console.log(error);
		  }
	}
	
	set inspeccionesSede(inspecciones)
	{
		try {
			var nombres = this.getLista("nombre",inspecciones);
		    var valores = this.getLista("valor",inspecciones);
//		    var ctx = document.getElementById("inspeccionesSedeChart");
//		    if (ctx) {
//		      ctx.height = 80;
//		      var myChart = new Chart(ctx, {
//		        type: 'bar',
//		        defaultFontFamily: 'Poppins',
//		        data: {
//		          labels: nombres,
//		          datasets: [
//		            {
//		              label: "Inspecciones",
//		              data: valores,
//		              borderColor: "rgba(0, 123, 255, 0.9)",
//		              borderWidth: "0",
//		              backgroundColor: "rgba(0, 123, 255, 0.5)",
//		              fontFamily: "Poppins"
//		            }
//		          ]
//		        },
//		        options: {
//		          legend: {
//		            position: 'top',
//		            labels: {
//		              fontFamily: 'Poppins'
//		            }
//
//		          },
//		          scales: {
//		            xAxes: [{
//		              ticks: {
//		                fontFamily: "Poppins"
//
//		              }
//		            }],
//		            yAxes: [{
//		              ticks: {
//		                beginAtZero: true,
//		                fontFamily: "Poppins"
//		              }
//		            }]
//		          }
//		        }
//		      });
//		    }
		    var app = {};
		    var option = {
		        color: ['#00cc00'],
		        tooltip : {
		            trigger: 'axis'
		        },
		        legend: {
		            data:['Inspecciones']
		        },
		        calculable : true,
		        xAxis : [
		            {
		                type : 'category',
		                data : nombres
		            }
		        ],
		        yAxis : [
		            {
		                type : 'value'
		            }
		        ],
		        series : [
		            {
		                name:'Inspecciones',
		                type:'bar',
		                data:valores
		            }
		        ]
		    };

		    if (option && typeof option === "object") 
		    {
		    	this.inspeccionesSedeChart.hideLoading();
		    	this.inspeccionesSedeChart.setOption(option, false);
		    }

		  } catch (error) {
		    console.log(error);
		  }
	}
	
	set inspeccionesMes(inspecciones)
	{
		try {
			var nombres = this.getLista("nombre",inspecciones);
		    var valores = this.getLista("valor",inspecciones);
//		    var ctx = document.getElementById("inspeccionesMesChart");
//		    if (ctx) {
//		      ctx.height = 80;
//		      var myChart = new Chart(ctx, {
//		        type: 'bar',
//		        defaultFontFamily: 'Poppins',
//		        data: {
//		          labels: nombres,
//		          datasets: [
//		            {
//		              label: "Inspecciones",
//		              data: valores,
//		              borderColor: "rgba(0, 123, 255, 0.9)",
//		              borderWidth: "0",
//		              backgroundColor: "rgba(0, 123, 255, 0.5)",
//		              fontFamily: "Poppins"
//		            }
//		          ]
//		        },
//		        options: {
//		          legend: {
//		            position: 'top',
//		            labels: {
//		              fontFamily: 'Poppins'
//		            }
//
//		          },
//		          scales: {
//		            xAxes: [{
//		              ticks: {
//		                fontFamily: "Poppins"
//
//		              }
//		            }],
//		            yAxes: [{
//		              ticks: {
//		                beginAtZero: true,
//		                fontFamily: "Poppins"
//		              }
//		            }]
//		          }
//		        }
//		      });
//		    }
		    var app = {};
		    var option = {
		        color: ['#e67300'],
		        tooltip : {
		            trigger: 'axis'
		        },
		        legend: {
		            data:['Inspecciones']
		        },
		        calculable : true,
		        xAxis : [
		            {
		                type : 'category',
		                data : nombres
		            }
		        ],
		        yAxis : [
		            {
		                type : 'value'
		            }
		        ],
		        series : [
		            {
		                name:'Inspecciones',
		                type:'bar',
		                data:valores
		            }
		        ]
		    };

		    if (option && typeof option === "object") 
		    {
		    	this.inspeccionesMesChart.hideLoading();
		    	this.inspeccionesMesChart.setOption(option, false);
		    }

		  } catch (error) {
		    console.log(error);
		  }
	}
}

var vista = new PanelVista(this);