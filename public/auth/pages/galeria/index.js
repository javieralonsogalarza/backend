$(function(){

    const $viewName = getData().ViewName; 

    const $table = $(`#table${$viewName.charAt(0).toUpperCase()+$viewName.slice(1)}`);

    const $btnRegistrar = $(`#btnRegistrar${$viewName.charAt(0).toUpperCase()+$viewName.slice(1)}`);

    const $dataTable = $table.DataTable({
        //"responsive": true, 
        "lengthChange": true, "autoWidth": false, "ordering": true,
        "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, 'Todos']],
        "language": { url: `${window.location.origin}/auth/json/dataTable/lang/es.json` },
        "ajax": {
            url: `/auth/${$viewName}/list-json`
        },
        "columns": [
            { title: "Nombre", data: "nombre", className: "text-left" },
            {
                data: null,
                defaultContent:
                    "<button type='button' class='btn btn-primary btn-sm btn-update' data-toggle='tooltip' title='Actualizar'><i class='fa fa-edit'></i></button>",
                "orderable": false,
                "searchable": false,
                "width": "26px"
            },
            {
                data: null,
                defaultContent:
                    "<button type='button' class='btn btn-danger btn-sm btn-delete' data-toggle='tooltip' title='Eliminar'><i class='fa fa-trash'></i></button>",
                "orderable": false,
                "searchable": false,
                "width": "26px"
            }
        ]
    });

    //Create Custom Searching
    $(document).on("keyup", ".datable-txt-search", function (e) {
        $dataTable.search($.fn.DataTable.ext.type.search.string(removeAccents(this.value))).draw();
    });

    $(document).on('search', ".datable-txt-search",  function (e) {
        if($(this).val() === "") $dataTable.search($.fn.DataTable.ext.type.search.string("")).draw();
    });
    

    $table.on("click", ".btn-delete", function () {
        const id = $dataTable.row($(this).parents("tr")).data().id;
        const nombre = $dataTable.row($(this).parents("tr")).data().nombre;
        const formData = new FormData();
        formData.append('_token', $("meta[name=csrf-token]").attr("content"));
        formData.append('id', id);
        confirmAjax(`/auth/${$viewName}/delete`, formData, "POST", `¿Está seguro de eliminar el registro ${nombre} ?`, null, function () {
            $dataTable.ajax.reload(null, false);
        });
    });

    $btnRegistrar.on("click", function () {
        invocarModalView();
    });

    $table.on("click", ".btn-update", function () {
        const id = $dataTable.row($(this).parents("tr")).data().id;
        invocarModalView(id);
    });

    invocarModalView = (id) => {
        invocarModal(`/auth/${$viewName}/partialView/${id ? id : 0}`, function ($modal) {
            if ($modal.attr("data-reload") === "true") $dataTable.ajax.reload(null, false);
        });
    }
});
