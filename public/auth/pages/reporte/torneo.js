$(function(){

    const $filter_torneo = $("#filter_torneo"); $filter_torneo.select2({ placeholder: 'Seleccione', allowClear: true });
    const $filter_categoria = $("#filter_categoria"); $filter_categoria.select2({ placeholder: 'Seleccione', allowClear: true });

    const $btnBuscar = $("#btnBuscar"), $iFrame = $("iframe#iframeReporte");

    $btnBuscar.on("click", function(){
        if($filter_torneo.val() == null || $filter_torneo.val() == ""){
            Toast.fire({icon: 'error', title: 'Por favor, seleccione un Torneo.'});     
        }else if($filter_categoria.val() == null || $filter_categoria.val() == ""){
            Toast.fire({icon: 'error', title: 'Por favor, seleccione una Categoría.'});     
        }else{
            $("#loading").show();
            $iFrame.attr("src", `/auth/reporte/torneoPartialView?torneo=${$filter_torneo.val()}&categoria=${$filter_categoria.val()}`);
        }
    });

    $iFrame.on("load", function () {
        $("#loading").hide();
    });

    $filter_torneo.on("change", function(){ 
        const $this = $(this);
        cascadingDropDownLoad($filter_categoria, `/auth/torneo/categorias?torneo=${$this.val()}`, null, false);
        validateFields(); 
    });

    $filter_categoria.on("change", function(){ validateFields(); });


    function validateFields()
    {
        if(($filter_torneo.val() != null && $filter_torneo.val() != "") && ($filter_categoria.val() != null && $filter_categoria.val() != "")){
             $btnBuscar.prop("disabled", false);
        }else{
            $btnBuscar.prop("disabled", true);
        }
    }

});