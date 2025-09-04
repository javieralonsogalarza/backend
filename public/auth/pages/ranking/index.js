$(function(){
    const $filter_anio = $("#filter_anio"), $filter_torneo = $("#filter_torneo"), $filter_category = $("#filter_category");
    const $partialView = $("#partialView"), $btnBuscar = $("#btnBuscar");

    $filter_anio.select2({
        placeholder: "--Todos--",
        allowClear: true,
        ajax: {
            url: "/torneos/anios", dataType: "json", type: "GET", delay: 250,
            data: function(params) {
                return {nombre: params.term};
            },
            processResults: function(data) {
                return { results: data.data };
            },
            cache: true
        }
    });

    $filter_torneo.select2({
        placeholder: "--Todos--",
        allowClear: true
    });

    $filter_torneo.on("change", function(){
        $filter_category.val("").trigger("change");
    });

    $filter_category.select2({
        placeholder: "--Todos--",
        allowClear: true,
        ajax: {
            url: "/rankings/categorias", dataType: "json", type: "GET", delay: 250,
            data: function(params) {
                return {
                    select2: true,
                    nombre: params.term
                };
            },
            processResults: function(data) {
                return { results: data.data };
            },
            cache: true
        }
    });

    $btnBuscar.on("click", function() {
        var torneos = $filter_tournaments.val();
        
        $.ajax({
            url: `/rankings/partialView`,
            type: "GET",
            data: {
                filter_anio: $filter_anio.val(),
                filter_categoria: $filter_category.val(),
                torneos: torneos
            },
            dataType: "html",
            cache: false,
            success: function (data) {
                $partialView.html("").append(data);
            },
            beforeSend: function () {
                $("#loading").show();
            },
            complete: function () {
                $("#loading").hide();
            }
        });
    });
  
});