class Validaciones
{
	updateTips(tips, t ) 
	{
	      tips
	        .text( t )
	        .addClass( "ui-state-highlight" );
	      setTimeout(function() {
	        tips.removeClass( "ui-state-highlight", 1500 );
	      }, 500 );
	}

	checkLength( o, n, min, max ,tips) 
	{
	      if ( o.val().length > max || o.val().length < min ) 
	      {
	        o.addClass( "ui-state-error" );
	        this.updateTips(tips, "La longitud de " + n + " debe ser entre " + min + " y " + max + "." );
	        o.focus();
	        return false;
	      } 
	      else 
	      {
	        return true;
	      }
	 }
	
	checkValue( o, n, tips) 
	{
	      if ( o.val() =="") 
	      {
	        o.addClass( "ui-state-error" );
	        this.updateTips(tips, "El valor de " + n + " es inválido.");
	        o.focus();
	        return false;
	      } 
	      else 
	      {
	        return true;
	      }
	 }
	 
    checkRegexp( o, regexp, n,tips ) 
    {
      if ( !( regexp.test( o.val() ) ) ) 
      {
        o.addClass( "ui-state-error" );
        this.updateTips(tips, n );
        o.focus();
        return false;
      } 
      else 
      {
        return true;
      }
    }
}