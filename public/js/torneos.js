$(function()
{    
    const $host = "/auth/torneo/view?landing=confraternidad-del-tenis";
    const $filter_anio = $("#filter_anio");
    const $filter_torneo = $("#filter_torneo"); const $btnBuscar = $("#btnBuscar");
    const $body = $("body"), $main = $("#list-cards-content"), $info = $("#partialView");

    $(".btn-nav").on("click", function(){
        const ul = $(".navigation ul");
        if(ul.hasClass("show")){
            ul.removeClass("show");
        }else{
            ul.addClass("show");
        }
    });

    $filter_anio.on("change", function(){
        $filter_torneo.val("").trigger("change");
    });
    
    $filter_anio.select2({
        ajax: {
            url: "/torneos/anios", dataType: "json", type: "GET", delay: 250,
            data: function(params) {
                return {
                    nombre: params.term
                };
            },
            processResults: function(data) {
                return { results: data.data };
            },
            cache: true
        }
    });

    $btnBuscar.on("click", function(){    
        $main.removeClass("hidden");$info.html("").addClass("hidden");
        listPagination(true);
    });

    function listPagination(reset){

        const $Page = reset ? $host + "?page=1" : $main.data('next-page');
        //const $Filters = `fecha_inicio=${$startDate}&fecha_final=${$endDate}`;
        const $Filters = `filter_anio=${$filter_anio.val()}`;

        if($Page != null)
        {
            if(reset)
            {
                $.get($Page+"&"+$Filters, function(data){
                    $main.html("").append(data.lists);
                    $main.data('next-page', data.next_page);
                }).always(function(){
                    console.log("finish..");
                });
            }else{
                clearTimeout( $.data(this, "scrollCheck") );

                $.data( this, "scrollCheck", setTimeout( function(){
                    $.data( this, "scrollCheck", setTimeout(function(){
                        var scroll_postion_for_load = $(window).height() + $(window).scrollTop() + 100;
                        if(scroll_postion_for_load >= $(document).height() && $Page != null)
                        {
                            console.log("loading2..");
                            $.get($Page+"&"+$Filters, function(data){
                                $main.append(data.lists);
                                $main.data('next-page', data.next_page);
                            }).done(function(){
                                console.log("finish2..");
                            });
                        }
                    }));
                }, 350));
            }
        }
    }

    $body.on("click", "button.close-view", function (){ $main.removeClass("hidden");$info.html("").addClass("hidden");});

    $body.on("click", ".list-cards-content .card .btn-detail",  function () {
        const $this = $(this).closest('.card');
        const id = $this.attr("data-id");
        invocarVista(`/auth/torneo/grupo/${id}/0/0/true`, function(data){
            $body.addClass("sidebar-collapse");
            $main.addClass("hidden");
            $info.removeClass("hidden").html("").append(data);
        });
    });
    
    /*$filter_torneo.select2({
        ajax: {
            url: "/torneos/mejores5", dataType: "json", type: "GET", delay: 250,
            data: function(params) {
                return {
                    filter_anio: $("#filter_anio").val(),
                    nombre: params.term
                };
            },
            processResults: function(data) {
                return { results: data.data };
            },
            cache: true
        }
    });

    $filter_torneo.on("change", function()
    {
        const $this = $(this);
        if($this.val() != "")
        {
            $.ajax({
                url: `/auth/torneo/grupo/${$this.val()}/0/0/true`,
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
        }else{
            $partialView.html("");
        }  
    });*/

});
