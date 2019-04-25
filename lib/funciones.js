
function eliminarPorValor(arreglo, propiedad, valor)
{
	var indice = -1;
	for(var i=0; i< arreglo.length; i++)
	{
		var elemento = arreglo[i];
		var valorElemento = elemento[propiedad];
		if(valorElemento==valor)
		{
			indice = i;
			break;
		}
	}
	if(indice!=-1)
		arreglo.splice(indice, 1);

}

function getMax(arreglo,propiedad)
{
	var max = 0;
	if(arreglo!=null)
		if(arreglo.length>0)
		{
			var elemento = arreglo[0];
			max = elemento[propiedad];
			for(var i =1;i<arreglo.length;i++)
			{
				elemento = arreglo[i];
				if(elemento[propiedad]>max)
					max = elemento[propiedad];
			} 	
		}				
	return max;
}

function sleep(ms) {
	  return new Promise(resolve => setTimeout(resolve, ms));
	}

var velocidadFade = 500;