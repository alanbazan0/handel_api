class Numero
{
	static formato(valor, decimales) 
	{
    	var re = new RegExp('^-?\\d+(?:\.\\d{0,' + (decimales || -1) + '})?');
	    return valor.toString().match(re)[0];
	}

}