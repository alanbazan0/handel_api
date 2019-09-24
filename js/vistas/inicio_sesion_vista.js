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
		$("#olvideContrasenaLink").click(this.olvideContrasena);
		this.inicializarValidacionesCuenta();
	}
	
	olvideContrasena(event)
	{
		 swal({
	            title: "Recuperar contrase\xF1a",
	            text: "Ingrese su correo electr\xF3nico",
	            type: "input",
	            showCancelButton: true,
	            closeOnConfirm: false,
	            animation: "slide-from-top",
	            cancelButtonText: "Cancelar",
	            confirmButtonText: "Recuperar",
	            showLoaderOnConfirm: true,
	            inputPlaceholder: "ejemplo@apps-handel.com"
	            	
	        },
	        function(inputValue){
	            if (inputValue === false) return false;
	            if (inputValue === "") {
	                swal.showInputError("Es necesario ingresar el correo electr\xF3nico!");
	                return false
	            }
	            vista.correoRecuperar = inputValue;
	            setTimeout(function(){
	            	vista.recuperarCuenta();
	            }, 2000);
	            

	        });
	}
	
	recuperarCuenta()
	{
		this.presentador.recuperarCuenta();
	}
	
	get url()
	{
		return $("body").attr("data-url");
	}
	
	get aplicacionId()
	{
		return $("body").attr("data-aplicacionId");
	}
	
	get aplicacionVersion()
	{
		return $("body").attr("data-aplicacionVersion");
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
		var url = "panel.php"
		if(this.url!="" && this.url!=undefined)
			url = this.url;
		
		var submitForm = this.getNewSubmitForm(url);
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
	
	mostrarRecuperacionCompletada()
	{
		swal({
			  title: "Recuperaci\xF3n completada",
			  text: "Revise su bandeja de entrada y siga las instrucciones para recuperar su cuenta. Si no lo recibe de inmediato, por favor espere o verifique que el correo no haya sido enviado a su carpeta de correo no deseado o SPAM.",
			  type: "success",
			  confirmButtonText: "Ok",
			  timer: this._tiempoAlerta,
			  closeOnConfirm: true
			},
			function(){
			});
	}
	
	mostrarRecuperacionIncorrecta(mensajeError)
	{
		swal({
			  title: "No se pudo recuperar la cuenta",
			  text: mensajeError,
			  type: "error",
			  confirmButtonText: "Ok",
			  timer: this._tiempoAlerta,
			  closeOnConfirm: true
			},
			function(){
				
			});
	}
	
	set correoRecuperar(correoRecuperar)
	{
		this._correoRecuperar = correoRecuperar;
	}
	
	get correoRecuperar()
	{
		return this. _correoRecuperar;
	}
}
var vista = new InicioSesionVista();
$(document).ready(function() 
{
	vista.inicializar();
});