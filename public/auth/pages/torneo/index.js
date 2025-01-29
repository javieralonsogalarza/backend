$(function(){

    const $viewName = getData().ViewName; 

    const $host = window.location.href;
    const $list_cards_content = $("#list-cards-content");
    const $body = $("body"), $reportrange = $('#reportrange');

    const $btnRegistrar = $(`#btnRegistrar${$viewName.charAt(0).toUpperCase()+$viewName.slice(1)}`);

    const $main = $("#main"), $info = $("#info");

    //let $startDate = moment().startOf('month').format("YYYY-MM-DD");
    //let $endDate = moment().endOf('month').format("YYYY-MM-DD");

    $(window).scroll(function(){
        if(window.screenY > 100)listPagination(false);
    });


    const $filter_anio = $("#filter_anio");
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

    listPagination(true);

    $filter_anio.on("change", function(){
        listPagination(true);
    });

    function listPagination(reset){

        const $Page = reset ? $host + "/?page=1" : $list_cards_content.data('next-page');
        //const $Filters = `fecha_inicio=${$startDate}&fecha_final=${$endDate}`;
        const $Filters = `filter_anio=${$filter_anio.val()}`;

        if($Page != null)
        {
            if(reset)
            {
                console.log("loading..");
                $.get($Page+"&"+$Filters, function(data){
                    $list_cards_content.html("").append(data.lists);
                    $list_cards_content.data('next-page', data.next_page);
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
                                $list_cards_content.append(data.lists);
                                $list_cards_content.data('next-page', data.next_page);
                            }).done(function(){
                                console.log("finish2..");
                            });
                        }
                    }));
                }, 350));
            }
        }
    }

    /*function cb(start, end) {
        $reportrange.find('span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        $startDate = moment($reportrange.data('daterangepicker').startDate._d).format("YYYY-MM-DD");
        $endDate = moment($reportrange.data('daterangepicker').endDate._d).format("YYYY-MM-DD");
        listPagination(true);
    }

    $reportrange.daterangepicker({
        autoUpdateInput: false,
        startDate: moment().startOf('month'),
        endDate: moment().endOf('month'),
        ranges: {
            //'Todos': [moment(new Date("2023-01-01")), moment()],
            //'Hoy': [moment(), moment()],
            //'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            //'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
            //'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
            'Este Mes': [moment().startOf('month'), moment().endOf('month')],
            'Mes Anterior': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'Todo el Año': [moment().subtract(1, 'month').startOf('year'), moment().subtract(1, 'month').endOf('year')]
        }
    }, cb);*/

    //cb(moment().startOf('month'), moment().endOf('month'));

    $body.on("click", ".list-cards-content .card .btn-delete",  function () {
        const $this = $(this).closest('.card');
        const id = $this.attr("data-id");
        const nombre =  $this.attr("data-info");
        const formData = new FormData();
        formData.append('_token', $("meta[name=csrf-token]").attr("content"));
        formData.append('id', id);
        confirmAjax(`/auth/${$viewName}/delete`, formData, "POST", `¿Está seguro de eliminar el registro ${nombre} ?`, null, function () {
            listPagination(true);
        });
    });

    $btnRegistrar.on("click", function () {
        invocarModalView();
    });

    $body.on("click", ".list-cards-content .card .btn-update",  function () {
        const $this = $(this).closest('.card');
        const id = $this.attr("data-id");
        invocarModalView(id);
    });

    $body.on("click", ".list-cards-content .card .btn-detail",  function () {
        const $this = $(this).closest('.card');
        const id = $this.attr("data-id");
        invocarVista(`/auth/${$viewName}/grupo/${id ? id : 0}`, function(data){
            $body.addClass("sidebar-collapse");
            $main.addClass("hidden");
            $info.removeClass("hidden").html("").append(data);
        });
    });

    invocarModalView = (id) => {
        invocarModal(`/auth/${$viewName}/partialView/${id ? id : 0}`, function ($modal) {
            if ($modal.attr("data-reload") === "true") listPagination(true);
        });
    }
    
});
