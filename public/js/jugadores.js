$(function(){
    const $filter_jugador = $("#filter_jugador");
    const $filter_anio = $("#filter_anio"), $filter_torneo = $("#filter_torneo");
    const $partialView = $("#partialView"), $btnBuscar = $("#btnBuscar");

    $filter_jugador.select2({             
        placeholder: "Buscar a un jugador",
        allowClear: true, 
    });


    $(".btn-nav").on("click", function(){
        const ul = $(".navigation ul");
        if(ul.hasClass("show")){
            ul.removeClass("show");
        }else{
            ul.addClass("show");
        }
    });
    
    $btnBuscar.on("click", function()
    {
        $.ajax({
            url: `/jugadorPartialView?landing=${$slug}&filter_jugador=${$filter_jugador.val()}&filter_anio=${$filter_anio.val()}&filter_torneo=${$filter_torneo.val()}`,
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

    $(document).on("click", "table tbody tr[data-id]", function(){
        const $this = $(this);
        $.ajax({
            url: `/jugadorPartidosPartialView?landing=${$slug}&filter_torneo=${$this.attr('data-id')}&filter_category=${$this.attr('data-category')}&filter_jugador=${$this.attr('data-player')}`,
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
    });
  
});