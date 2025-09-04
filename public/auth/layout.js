
try {
    window.addEventListener("submit", function (e) {
        const form = e.target;
        if (form.getAttribute("enctype") === "multipart/form-data") {
            if (form.dataset.ajax) {
                e.preventDefault();
                e.stopImmediatePropagation();
                $(form).find("input[type=text]").each(function () {
                    if (this.inputmask)
                        this.inputmask._valueSet(this.inputmask.unmaskedvalue(), true);
                });
                const xhr = new XMLHttpRequest();
                xhr.open(form.method, form.action);

                xhr.addEventListener("loadend",
                    function () {
                        if (form.getAttribute("data-ajax-loading") !== null &&
                            form.getAttribute("data-ajax-loading") !== "")
                            document.getElementById(form.getAttribute("data-ajax-loading").substr(1)).style
                                .display = "none";

                        if (form.getAttribute("data-ajax-complete") !== null &&
                            form.getAttribute("data-ajax-complete") !== "")
                            window[form.getAttribute("data-ajax-complete")].apply(this, []);
                    });

                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200)
                            window[form.getAttribute("data-ajax-success")].apply(this,
                                [JSON.parse(xhr.responseText)]);
                        else
                            window[form.getAttribute("data-ajax-failure")].apply(this, [xhr.status]);
                    }
                };

                const confirm = form.getAttribute("data-ajax-confirm");

                if (confirm) {
                    Swal.fire({
                        icon: 'question',
                        title: "Confirmacion",
                        text: confirm,
                        type: "warning",
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Si, Confirmar',
                        cancelButtonText: 'Cancelar',
                        showCancelButton: true,
                        closeOnConfirm: false,
                        showLoaderOnConfirm: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            if (form.getAttribute("data-ajax-begin") !== null &&
                                form.getAttribute("data-ajax-begin") !== "")
                                window[form.getAttribute("data-ajax-begin")].apply(this, []);
                            xhr.send(new FormData(form));
                        }
                    });
                } else {
                    if (form.getAttribute("data-ajax-loading") !== null &&
                        form.getAttribute("data-ajax-loading") !== "")
                        document.getElementById(form.getAttribute("data-ajax-loading").substr(1)).style.display =
                            "block";

                    if (form.getAttribute("data-ajax-begin") !== null && form.getAttribute("data-ajax-begin") !== "")
                        window[form.getAttribute("data-ajax-begin")].apply(this, []);

                    xhr.send(new FormData(form));
                }
            }
        }
    }, true);
} catch (err) { console.log(err); }

moment.locale('es');

$(function () {


    $(document).on("shown.bs.modal", ".modal", function (event) {
        var zIndex = 1040 + (10 * $(".modal:visible").length);
        $(this).css("z-index", zIndex);
        setTimeout(function () {
            $(".modal-backdrop").not(".modal-stack").css("z-index", zIndex - 1).addClass("modal-stack");
        }, 0);

        $("body").css("overflow", "hidden");
    });

    $(document).on("hidden.bs.modal", ".modal", function (event) {
        if ($(".modal.fade.show").length === 0) {
            $("body").css("overflow", "auto");
        }
    });

    /*$(document).on("keyup keypress", "form", function (e) {
        const keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
            e.preventDefault();
            return false;
        }
    });*/

    $.ajaxSetup({ cache: false });

    Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });

});

invocarVista = function(url, onHiddenView){
    $.ajax({
        url: url,
        type: "GET",
        dataType: "html",
        cache: false,
        success: function (data) {
            if (onHiddenView) onHiddenView(data);
        },
        beforeSend: function () {
            $("#loading").show();
        },
        complete: function () {
            $("#loading").hide();
        }
    });
}

invocarModal = function(url, onHiddenModal) {
    $.ajax({
        url: url,
        type: "GET",
        dataType: "html",
        cache: false,
        success: function (data) {
            const $modal = $("<div class='parent'>").append(data);
            $modal.find(">.modal").on("hidden.bs.modal", function () {
                if (onHiddenModal) onHiddenModal($(this));
                $(this).parent().remove();
            });
            $modal.find(">.modal").modal("show");

            $("body").append($modal);
        },
        beforeSend: function () {
            $("#loading").show();
        },
        complete: function () {
            $("#loading").hide();
        }
    });
}

onSuccessForm = function(data, $form, $modal, onSucess, ResetForm) {
    if($form != null)
        $form.find("span[data-valmsg-for]").text("");

    if (data.Success === true) {
        if(!ResetForm){ $form.trigger("reset"); }
        if($modal){$modal.attr("data-reload", "true");}
        Toast.fire({icon: 'success', title: data.Message != null ? data.Message : "Registro/Actualización guardado correctamente"});
        if ($modal) $modal.modal("hide");
        if (onSucess) onSucess(data);
    }else {
        if (data.Errors) {
            $.each(data.Errors,
                function (i, item) {
                    if($form != null) {
                        if ($form.find("span[data-valmsg-for=" + i + "]").length !== 0)
                            $form.find("span[data-valmsg-for=" + i + "]").text(item[0]);
                    }
                });
        }
        Toast.fire({icon: 'error', title: data.Message != null ? data.Message: "Algo salió mal, por favor verifique los campos ingresados."});
    }
}

onFailureForm = function() {
    Toast.fire({icon: 'error', title: 'Algo salió mal, hubo un error al guardar.'});
}

showView = function($list, $frm, data){
    $list.addClass("hidden");
    $frm.removeClass("hidden");
    $frm.find(".container-fluid").html("").append(data);
}

resetView = function($list, $frm, $dataTable){
    $list.removeClass("hidden");
    $frm.addClass("hidden");
    $frm.find(".container-fluid").html("");
    if($dataTable) {$dataTable.ajax.reload(null, false);}
}

confirmAjax = function(url, parameters, type, msg, msgSuccess, onSuccess, onErrors, doble=false) {
    Swal.fire({
        icon: 'question',
        title: "Confirmación",
        text: msg ? msg : "¿Está seguro de realizar esta acción?",
        type: "warning",
        confirmButtonColor: '#d33',
        confirmButtonText: 'Si, Confirmar',
        cancelButtonText: 'Cancelar',
        showCancelButton: true,
        closeOnConfirm: false,
        showLoaderOnConfirm: true
    }).then((result) => {
        console.log(result);
        console.log(doble);
        if (result.isConfirmed) {
            if (doble) {
                Swal.fire({
                    icon: 'question',
                    title: "Confirmación Final",
                    text: "¿Está realmente seguro de que quiere continuar? Esta acción no se puede deshacer.",
                    type: "warning",
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Si, Confirmar',
                    cancelButtonText: 'Cancelar',
                    showCancelButton: true,
                    closeOnConfirm: false,
                    showLoaderOnConfirm: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        actionAjax(url, parameters, type, onSuccess, true, msgSuccess, onErrors);
                    }
                });
            } else {
                actionAjax(url, parameters, type, onSuccess, true, msgSuccess, onErrors);
            }
        }
    });
}

actionAjaxEspecial = function(url, parameters, type, onSuccess, isToConfirm, msgSuccess, onErrors) {
    $.ajax({
        url: url,
        data: parameters,
        type: type != null ? type : "POST",
        cache: false,
        processData: false,
        contentType: false,
        success: function (data) {
            if (isToConfirm === true) {
                if (data.Success === true) {
                    Toast.fire({icon: 'success', title: msgSuccess ? msgSuccess : 'Proceso realizado Correctamente'});
                    if (onSuccess) onSuccess(data);
                } else {
                    // Comprobar si hay sugerencias en la respuesta
                    if (data.sugerencias && data.sugerencias.length > 0) {
                        // Construir el modal de sugerencias
                        let contenidoSugerencias = '<div class="sugerencias-container" style="text-align:left;max-height:400px;overflow-y:auto;">';
                        contenidoSugerencias += '<h4>Sugerencias para formar grupos completos:</h4>';
                        contenidoSugerencias += '<ul style="padding-left:20px;">';
                        
                        data.sugerencias.forEach(sugerencia => {
                            switch(sugerencia.tipo) {
                                case 'cambio_zona':
                                    contenidoSugerencias += `<li>Asignar a <strong>${sugerencia.jugador}</strong> de la zona ${sugerencia.zona_actual || ''} a la zona <strong>${sugerencia.zona_sugerida}</strong></li>`;
                                    break;
                                case 'fusion_grupos':
                                    contenidoSugerencias += `<li>Fusionar <strong>${sugerencia.grupo1}</strong> y <strong>${sugerencia.grupo2}</strong> que comparten la zona ${sugerencia.zona}</li>`;
                                    break;
                                case 'mover_jugadores':
                                    contenidoSugerencias += `<li>Mover a <strong>${sugerencia.jugadores}</strong> del ${sugerencia.grupo_origen} al ${sugerencia.grupo_destino}</li>`;
                                    break;
                                case 'agregar_jugadores':
                                    contenidoSugerencias += `<li>Agregar <strong>${sugerencia.cantidad}</strong> jugador(es) adicional(es) para completar todos los grupos</li>`;
                                    break;
                                case 'mover':
                                    contenidoSugerencias += `<li>Mover a <strong>${sugerencia.jugador}</strong> del grupo ${sugerencia.desde_grupo} (zona ${sugerencia.desde_zona}) a ${sugerencia.hacia}</li>`;
                                    break;
                                case 'asignar_zona':
                                    contenidoSugerencias += `<li>Asignar a <strong>${sugerencia.jugador}</strong> la zona ${sugerencia.zona_sugerida}</li>`;
                                    break;
                                default:
                                    contenidoSugerencias += `<li>${sugerencia.mensaje}</li>`;
                            }
                        });
                        
                        contenidoSugerencias += '</ul>';
                        contenidoSugerencias += '<p class="mt-3">Para crear los grupos correctamente, aplique estos cambios y vuelva a intentarlo.</p>';
                        contenidoSugerencias += '</div>';
                        
                        // Mostrar el modal con las sugerencias
                        Swal.fire({
                            title: 'No se pudieron formar grupos completos',
                            html: contenidoSugerencias,
                            icon: 'warning',
                            confirmButtonText: 'Aceptar',
                            width: '600px'
                        });
                        
                        // Llamar al callback de error personalizado si existe
                        if (onErrors) onErrors(data);
                    } else {
                        // Si no hay sugerencias, mostrar el mensaje de error normal
                        if (onErrors) onErrors(data);
                        else Toast.fire({icon: 'error', title: data.Message});
                    }
                }
            } else {
                // Procesar respuestas no confirmadas normalmente
                if (onSuccess) onSuccess(data);
            }
        },
        beforeSend: function () {
            $("#loading").show();
            //if (isToConfirm !== true) $("#loading").show();
        },
        complete: function () {
            $("#loading").hide();
            //if (isToConfirm !== true) $("#loading").hide();
        }
    });
}

actionAjax = function(url, parameters, type, onSuccess, isToConfirm, msgSuccess, onErrors) {
    $.ajax({
        url: url,
        data: parameters,
        type: type != null ? type : "POST",
        cache: false,
        processData: false,
        contentType: false,
        success: function (data) {
            if (isToConfirm === true) {
                if (data.Success === true) {
                    Toast.fire({icon: 'success', title: msgSuccess ? msgSuccess : 'Proceso realizado Correctamente'});
                    if (onSuccess) onSuccess(data);
                } else {
                    if (onErrors) onErrors(data);
                    else Toast.fire({icon: 'error', title: data.Message});
                }
            } else {
                if (onSuccess) onSuccess(data);
            }
        },
        beforeSend: function () {
            $("#loading").show();
            //if (isToConfirm !== true) $("#loading").show();
        },
        complete: function () {
            $("#loading").hide();
            //if (isToConfirm !== true) $("#loading").hide();
        }
    });
}

function createModal(title, body, onHidden) {
    const template = `<div id="myModal" class="modal fade" role="dialog">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">${title}</h4>
                          </div>
                          <div class="modal-body">
                            ${body}
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                          </div>
                        </div>
                      </div>
                    </div>`;

    const $modal = $(template);
    $modal.on("hidden.bs.modal", function () {
        $modal.remove();
        if (onHidden) onHidden();
    });

    $modal.modal("show");
}

function getDate() {
    const now = new Date();
    const primerDia = new Date(now.getFullYear(), now.getMonth(), 1);
    const ultimoDia = new Date(now.getFullYear(), now.getMonth() + 1, 0);
    return {
        Now: now,
        FirstDay: primerDia,
        LastDay: ultimoDia
    };
}

function isNumberKey(evt) {
    var charCode = (evt.which) ? evt.which : event.keyCode;
    if(charCode > 31 && (charCode < 48 || charCode > 57))
        return false;

    return true;
}

agregarCommaMillions = (data) => {
    var str = data.toString().split('.');
    if (str[0].length >= 4) {
        str[0] = str[0].replace(/(\d)(?=(\d{3})+$)/g, '$1,');
    }
    return str.join('.');
}

function getFormatDate(date) {
    const array = date.split("/");
    const f = new Date(array[2], array[1] - 1, array[0]);
    return f.format("yyyy-mm-dd");
}

readImage = function(input, img) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.attr('src', e.target.result);
        };
        reader.readAsDataURL(input.files[0]);
    }else{
        img.attr('src', '/upload/image/default.png');
    }
}

cascadingDropDownLoad = function($dropDown, urlRequest, parameters, selected, onComplete) {
    actionAjax(urlRequest, parameters, "GET", function (data) {
        if(data.data){
            $dropDown.html("");
            $dropDown.append(`<option value="">Seleccione</option>`);
            $.each(data.data, function (i, e) {
                var max = Math.max.apply(Math, data.data.map(function (o) { return o.id; }));
                $dropDown.append(`<option value="${e.id}" ${selected && max === e.id ? "selected" : ""}>${e.nombre}</option>`);
            });

            if(onComplete) onComplete();
        }
    });
}

$.fn.DataTable.ext.type.search.string = function (data) {
return ! data ? '' : (typeof data === 'string' ? data
.replace( /έ/g, 'ε').replace( /ύ/g, 'υ').replace( /ό/g, 'ο').replace( /ώ/g, 'ω')
.replace( /ά/g, 'α').replace( /ί/g, 'ι').replace( /ή/g, 'η').replace( /\n/g, ' ' ).replace( /[áÁ]/g, 'a' ).replace( /[éÉ]/g, 'e' )
.replace( /[íÍ]/g, 'i' ).replace( /[óÓ]/g, 'o' ).replace( /[úÚ]/g, 'u' ).replace( /ê/g, 'e' ).replace( /î/g, 'i' ).replace( /ô/g, 'o' ).replace( /è/g, 'e' )
.replace( /ï/g, 'i' ).replace( /ü/g, 'u' ).replace( /ã/g, 'a' ).replace( /õ/g, 'o' ).replace( /ç/g, 'c' ).replace( /ì/g, 'i' ) : data);
};

function removeAccents(data) {
    return data.replace(/έ/g, 'ε').replace(/ύ/g, 'υ').replace(/ό/g, 'ο').replace(/ώ/g, 'ω').replace(/ά/g, 'α')
        .replace(/ί/g, 'ι').replace(/ή/g, 'η').replace(/\n/g, ' ').replace(/[çÇ]/g, 'c').replace(/[Ãã]/g, 'a')
        .replace(/[Ẽẽ]/g, 'e').replace(/[ĨĨ]/g, 'i').replace(/[Õõ]/g, 'o').replace(/[Ũũ]/g, 'u').replace(/[áÁ]/g, 'a')
        .replace(/[éÉ]/g, 'e').replace(/[íÍ]/g, 'i').replace(/[óÓ]/g, 'o').replace(/[úÚ]/g, 'u').replace(/[äÄ]/g, 'a')
        .replace(/[ëË]/g, 'e').replace(/[ïÏ]/g, 'i').replace(/[öÖ]/g, 'o').replace(/[üÜ]/g, 'u').replace(/[àÀ]/g, 'a')
        .replace(/[èÈ]/g, 'e').replace(/[ìÌ]/g, 'i').replace(/[òÒ]/g, 'o').replace(/[ùÙ]/g, 'u').replace(/[âÂ]/g, 'a')
        .replace(/[êÊ]/g, 'e').replace(/[îÎ]/g, 'i').replace(/[ôÔ]/g, 'o').replace(/[ûÛ]/g, 'u').replace(/[Şş]/g, 's')
        .replace(/ß/g, 'ss')
}

$(document).on('select2:open', () => {
    document.querySelector('.select2-search__field').focus();
});

