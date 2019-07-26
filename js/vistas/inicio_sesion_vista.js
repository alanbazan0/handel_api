class InicioSesionVista extends Vista
{
	constructor(ventana)
	{	
		super();
		this.ventana = ventana;
		this.presentador = new InicioSesionPresentador(this);
		//this.validaciones = new Validaciones();
	}
	
	inicializar()
	{
		
		this.inicializarValidacionesCuenta();
	}
	
	get aplicacionId()
	{
		return $("body").attr("data-aplicacionId");
	}
	
	inicializarValidacionesCuenta()
	{
        jQuery("#inicioSesionForm").validate({
            ignore: [],
            errorClass: "invalid-feedback animated fadeInDown",
            errorElement: "div",
            errorPlacement: function(e, a) {
                jQuery(a).parents(".form-group > div").append(e)
            },
            highlight: function(e) {
                jQuery(e).closest(".form-group").removeClass("is-invalid").addClass("is-invalid")
            },
            success: function(e) {
                jQuery(e).closest(".form-group").removeClass("is-invalid"), jQuery(e).remove()
            },
            rules: {
                "nombreUsuarioInput": {
                    required: !0,
                    email: !0
                },
                "contrasenaInput": {
                    required: !0
                }
            },
            messages: {
                "nombreUsuarioInput": "Por favor ingrese una direcci\xF3n de correo electr\xF3nico v\xE1lida",
                "contrasenaInput": {
                    required: "Por favor ingrese una contrase\xF1a"
                }
            },
            submitHandler:function (form) {
            	 vista.iniciarSesion();
            }
        });
	}
	
	iniciarSesion()
	{
		this.presentador.iniciarSesion();
	}
	
//	mostrarTip(mensaje)
//	{
//
//		
//	}
	
	mostrarMenu(usuario)
	{
		var submitForm = this.getNewSubmitForm("panel.php");
		this.createNewFormElement(submitForm, "usuario", JSON.stringify(usuario));	 
	    submitForm.target= "_self";
	    submitForm.submit();
		
	}
	
//	datosValidos()
//	{
//		var emailRegex = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
//        
//		var nombreUsuario = $("#nombreUsuarioInput"),
//			contrasena = $("#contrasenaInput");
//        
//        var allFields = $( [] ).add(nombreUsuario).add(contrasena);
//        var tips = $( ".validateTips" );
//		tips.text("");
//		
//		var valid = true;
//		allFields.removeClass("ui-state-error");
//		
//		valid = valid && this.validaciones.checkValue( nombreUsuario, "nombre de usuario", tips );		
//	    valid = valid && this.validaciones.checkRegexp( nombreUsuario, emailRegex, "ejemplo. contacto@handel-sce.net",tips );
//	    valid = valid && this.validaciones.checkValue( contrasena, "contraseña", tips );
//		
//		return valid;
//	}	
	
	get nombreUsuario()
	{
		return $("#nombreUsuarioInput").val();
	}
	
	get contrasena()
	{
		return $("#contrasenaInput").val();
	}
	
//	mostrarIndicador()
//	{
//		$('#indicador').show();				
//	}
//	
//	ocultarIndicador()
//	{		
//		$('#indicador').hide();
//	}
}
var vista = new InicioSesionVista();
$(document).ready(function() 
{
	vista.inicializar();
});