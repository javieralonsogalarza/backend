$(function(){
    $(".btn-nav").on("click", function(){
        const ul = $(".navigation ul");
        if(ul.hasClass("show")){
            ul.removeClass("show");
        }else{
            ul.addClass("show");
        }
    });
});

function onSubmit(token) 
{
    $("span.validate").text(""); 

    var Success = true;

    const nombres = $('input[name=nombres]').val();
    const apellidos = $("input[name=apellidos]").val(); 
    const correo = $("input[name=email]").val(); 
    const celular = $("input[name=celular]").val();
    const mensaje = $("textarea[name=mensaje]").val(); 

    const patternEmail = /^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i;

    if(nombres.trim() == ""){
        Success = false;
        $("span.validate-nombres").text("Ingrese nombres válido"); 
    }

    if(apellidos.trim() == ""){
        Success = false;
        $("span.validate-apellidos").text("Ingrese apellidos válido"); 
    }

    if(celular.trim() == ""){
        Success = false;
        $("span.validate-celular").text("Ingrese un número de celular válido");
    }else if(celular.trim() != "" && celular.trim().length < 6){
        Success = false;
        $("span.validate-celular").text("Ingrese un número de celular de al menos 6 digitos");
    }else if(celular.trim() != "" && celular.trim().length > 15){
        Success = false;
        $("span.validate-celular").text("Ingrese un número de celular máximo 15 digitos");
    }

    if(correo.trim() == "" || !patternEmail.test(correo)){
        Success = false;
        $("span.validate-email").text("Ingrese un E-mail válido");
    }

    if(mensaje.trim() == ""){
        Success = false;
        $("span.validate-mensaje").text("Ingrese un mensaje");
    }


    if(Success)
    {
        const formData = new FormData();
        formData.append('_token', $("meta[name=csrf-token]").attr("content"));
        formData.append('nombres', nombres);
        formData.append('apellidos', apellidos);
        formData.append('celular', celular);
        formData.append('email', correo);
        formData.append('mensaje', mensaje);
        formData.append('g-recaptcha-response', token);

        $.ajax({
            type: "POST",
            url: "/contactanos",
            data: formData,
            cache: false,
            processData: false,
            contentType: false,
            beforeSend: function(){
                $(".submit > button").html("Enviando Mensaje... <i class='fa fa-circle-o-notch fa-spin fa-xs fa-fw'></i>")
                $(".submit > button").prop("disabled", true);
            },
            complete: function(){
                $(".submit > button").text("Enviar");
                $(".submit > button").prop("disabled", false);
                $("form")[0].reset();
            },
            success: function(result){
                if(result.Success){
                    $(".form_content_success").removeClass('hidden');
                    $("form").hide();
                    setTimeout(function(){
                        $(".form_content_success").addClass('hidden');
                        $("form").show();
                    }, 5000);
                }else{
                    alert(result.Message);
                }
            },
            error: function(error){
                alert("Ocurrió un error al enviar el formulario, por favor contáctanos por Whatsapp");
            } 
        });
    }
}