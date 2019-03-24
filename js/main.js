(function ($) {
  // USE STRICT
  "use strict";

  // Select 2
  try {

	 $('select').empty();
	 $('select').append('<option value="">Cargando...</option>');

  } catch (error) {
    console.log(error);
  }


})(jQuery);