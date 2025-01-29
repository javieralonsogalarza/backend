$(function(){

    const $viewName = getData().ViewName; 

    const $table = $(`#table${$viewName.charAt(0).toUpperCase()+$viewName.slice(1)}`);

    const $btnRegistrar = $(`#btnRegistrar${$viewName.charAt(0).toUpperCase()+$viewName.slice(1)}`);

    const $btnImportarMasivo = $("#btnImportarMasivo"), $btnEliminarMasivo = $("#btnEliminarMasivo");

    const $dataTable = $table.DataTable({
        //"responsive": true, 
        "lengthChange": true, "autoWidth": false, "ordering": true,
        "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, 'Todos']],
        "language": { url: `${window.location.origin}/auth/json/dataTable/lang/es.json` },
        "ajax": {
            url: `/auth/${$viewName}/list-json`,
            data: function(param){
                param.filter_categoria = $("#filter_categoria_id").val();
                param.filter_sexo = $("#filter_sexo_id").val();
            }
        },
        "order": [[1, 'asc']],
        "columns": [
            {
                width: "20px",
                title: `<label class='container-label m-0'><input type='checkbox' id='selectAll'/><span class='checkmark'></span></label>`,
                className: "text-center",
                data: null,
                render: function (data) {
                    return `<label class='container-label m-0'><input type='checkbox' value='${data.id}' data-info='${data.nombre_completo}'><span class='checkmark'></span></label>`;
                },
                orderable: false,
            },
            { title: "Nombre Completo", data: null, className: "text-left", render: function(data){
                return  (data.isAccount ? '<i class="fa fa-user"></i> ' : '') + data.nombre_completo;
            } },
            { title: "Categoría", data: "categoria", className: "text-left", render: function(data){
                return data != null ? data.nombre : "Ninguno";
            } },
            { title: "Sexo", data: "sexo_completo", className: "text-left" },
            { title: "Nº Documento", data: "nro_documento", className: "text-left" },
            { title: "Celular", data: "celular", className: "text-left" },
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

    const $filter_categoria = $("#filter_categoria_id");
    $filter_categoria.select2({
        placeholder: "--Todos--",
        allowClear: true,
        ajax: {
            url: "/auth/categoria/list-json", 
            dataType: "json",
            type: "GET", 
            delay: 250,
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

    $("#filter_categoria_id, #filter_sexo_id").on("change", function(){
        $dataTable.ajax.reload(null, false);
    });

    const $filter_sexo = $("#filter_sexo_id");
    $filter_sexo.select2({ placeholder: "--Todos--",  allowClear: true});

    //Create Custom Searching
    $(document).on("keyup", ".datable-txt-search", function (e) {
        $dataTable.search($.fn.DataTable.ext.type.search.string(removeAccents(this.value))).draw();
    });

    $(document).on('search', ".datable-txt-search",  function (e) {
        if($(this).val() === "") $dataTable.search($.fn.DataTable.ext.type.search.string("")).draw();
    });

    $btnImportarMasivo.on("click", function(){
        invocarModal(`/auth/${$viewName}/partialViewimportExcel`, function ($modal) {
            $dataTable.ajax.reload(null, false);
        });
    });

    $("body").on("click", "#selectAll", function () {
        if ($(this).is(":checked")) {
            $dataTable.rows().nodes().to$().find('input[type="checkbox"]').each(function () {
                const $this = $(this); $this.prop('checked', true);
            });
        } else {
            $dataTable.rows().nodes().to$().find('input[type="checkbox"]').each(function () {
                const $this = $(this); $this.prop('checked', false);
            });
        }
    });

    $btnEliminarMasivo.on("click", function () {
        const ids = [];
        const nombres = [];
        const validadores = [];
        $dataTable.rows().nodes().to$().find('input[type="checkbox"]:checked').each(function (i) {
            const rowData = $dataTable.row($(this).parents("tr")).data();
            ids[i] = parseInt($(this).val());
            nombres[i] = rowData.nombre_completo;
            validadores[i] = rowData.en_torneo_jugadors;
        });
    
        if (ids.length > 0) {
            // Verificar si alguno de los jugadores tiene registros en torneos
            const tieneRegistros = validadores.some(validador => validador);
    
            if (tieneRegistros) {
                Toast.fire({icon: 'error', title: 'No se puede eliminar uno o más jugadores, ya que tienen registros en torneos.'});
                return;
            }
    
            const formData = new FormData();
            formData.append('_token', $("meta[name=csrf-token]").attr("content"));
            formData.append('ids', JSON.stringify(ids));
            confirmAjax(`/auth/${$viewName}/delete/masivo`, formData, "POST", `¿Está seguro de eliminar los jugadores seleccionados?`, null, function () {
                $dataTable.ajax.reload(null, false);
            });
        } else {
            Toast.fire({icon: 'error', title: 'No se ha seleccionado ningún jugador a eliminar.'});
        }
    });

    $table.on("click", ".btn-delete", function () {
        const id = $dataTable.row($(this).parents("tr")).data().id;
        const nombre = $dataTable.row($(this).parents("tr")).data().nombre_completo;
        const validador = $dataTable.row($(this).parents("tr")).data().en_torneo_jugadors;
        const formData = new FormData();
        formData.append('_token', $("meta[name=csrf-token]").attr("content"));
        formData.append('id', id);
        if(validador){
            Toast.fire({icon: 'error', title: 'No se puede eliminar el jugador, ya que tiene registros en torneos.'});
            return;
        }
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
