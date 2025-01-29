$(function()
{    
    const $filter_anio = $("#filter_anio"), $filter_torneo = $("#filter_torneo"), $filter_category = $("#filter_category");
    const $partialView = $("#partialView"), $btnBuscar = $("#btnBuscar");

    $(".btn-nav").on("click", function(){
        const ul = $(".navigation ul");
        if(ul.hasClass("show")){
            ul.removeClass("show");
        }else{
            ul.addClass("show");
        }
    });

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


    $btnBuscar.on("click", function()
    {
        $.ajax({
            url: `/rankings/partialView?landing=${$slug}&filter_anio=${$filter_anio.val()}&filter_categoria=${$filter_category.val()}`,
            type: "GET",
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
