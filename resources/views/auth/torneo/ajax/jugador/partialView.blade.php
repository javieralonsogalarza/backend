<div class="modal fade" style="overflow-y: scroll" id="modal{{$ViewName}}Jugador" role="dialog" tabindex="-1" data-backdrop="static" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Listado de Jugadores</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="{{ $TorneoCategoria->multiple ? "col-md-5" : "col-md-12"}}">
                        <div class="text-right mb-3">
                            @if(!$TorneoCategoria->multiple)
                                <button type="button" class="btn btn-primary pull-right" id="btnRegistrar{{$ViewName}}Jugador">
                                    <i class="fa fa-plus"></i> Agregar Jugador
                                </button>
                            @endif
                        </div>
                        <div class="{{ $TorneoCategoria->multiple ? "col-md-12" : "col-md-6" }}">
                           <div class="form-group row content-datable-txt-search">
                               <label for="txtSearch" class="col- col-form-label">Buscar:</label>
                               <div class="col-sm-10">
                                   <input id="txtSearch" type="search" class="datable-txt-search form-control">
                               </div>
                           </div>
                        </div>
                        <table id="table{{$ViewName}}" class="table table-bordered table-striped"></table>
                    </div>
                    @if($TorneoCategoria->multiple)
                        <div class="col-md-2 align-items-center justify-content-center d-flex">
                            <div class="form-group">
                                <button id="btnAgregarJugadores" type="button" class="btn btn btn-md btn-primary"><i class="fa fa-random"></i> Agrupar</button>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <!--<strong>Lista de Jugadores Dobles</strong>-->
                                </div>
                                <div class="col-sm-6 text-right">
                                    <button type="button" class="btn btn-primary pull-right" id="btnRegistrar{{$ViewName}}Jugador">
                                        <i class="fa fa-plus"></i> Agregar Jugador
                                    </button>
                                </div>
                            </div>
                            <table id="table{{$ViewName}}Dupla" class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th>Nombre Completo</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                @if($TorneoCategoria->multiple)
                    <button type="button" class="btn btn-primary pull-right" id="btnSeleccionar{{$ViewName}}Jugadores">Agregar Duplas</button>
                @else
                    <button type="button" class="btn btn-primary pull-right" id="btnSeleccionar{{$ViewName}}Jugador">Agregar</button>
                @endif
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function(){
        const $table = $("#table{{$ViewName}}"), $buttonRegistrarJugador = $("#btnRegistrar{{$ViewName}}Jugador");
        const $modal = $("#modal{{$ViewName}}Jugador");
        const $isMultiple = {{ $TorneoCategoria->multiple }};

        var $jugadoresNoDisponibles = [];

        const $dataTable = $table.DataTable({
            "responsive": true, "lengthChange": ($isMultiple === 1 ? false : true), "autoWidth": false, "ordering": true,
            "info": ($isMultiple === 1 ? false : true),
            "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, 'Todos']],
            "language": { url: `${window.location.origin}/auth/json/dataTable/lang/es.json` },
            "ajax": {
                url: `/auth/jugador/list-json`,
                data: function (x) {
                    x.torneo_id = {{ $TorneoCategoria->torneo_id != null ? $TorneoCategoria->torneo_id : 0 }};
                    x.torneo_categoria_id = {{ $TorneoCategoria->id }};
                    x.jugadores_no_disponibles = JSON.stringify($jugadoresNoDisponibles);
                },
                beforeSend: function () {$("#loading").show();},
                complete: function (){ $("#loading").hide(); reselectRows(); }
            },
            "order": [[1, 'asc']],
            "columns": [
                {
                    width: "20px",
                    title: `${$isMultiple ? "" : "<label class='container-label m-0'><input type='checkbox' id='selectAll'/><span class='checkmark'></span></label>"}`,
                    className: "text-center",
                    data: null,
                    render: function (data) {
                        return `<label class='container-label m-0'><input type='checkbox' value='${data.id}' data-info='${data.nombre_completo}'><span class='checkmark'></span></label>`;
                    },
                    orderable: false,
                },
                { title: "Nombre Completo", data: "nombre_completo", className: "text-left" },
                {
                    data: null,
                    defaultContent:
                        "<button type='button' class='btn btn-primary btn-xs btn-update' data-toggle='tooltip' title='Actualizar'><i class='fa fa-edit'></i></button>",
                    "orderable": false,
                    "searchable": false,
                    "width": "26px"
                },
            ]
        });

        //Create Custom Searching
        $modal.on("keyup", ".datable-txt-search", function (e) {
            $dataTable.search($.fn.DataTable.ext.type.search.string(removeAccents(this.value))).draw();
        });

        $modal.on('search', ".datable-txt-search",  function (e) {
            if($(this).val() === "") $dataTable.search($.fn.DataTable.ext.type.search.string("")).draw();
        });

        /*Categorias Simples*/
        let jugadoresSeleccionados = [];
        $table.on("click", "input[type=checkbox]", function (){
            const $this = $(this);
            if($this.is(":checked")) jugadoresSeleccionados.push(parseInt($this.val()));
            else jugadoresSeleccionados = jugadoresSeleccionados.filter(function(item) {return item !== parseInt($this.val())});
            $(".datable-txt-search").select();
        });

        $modal.on("keyup", ".datable-txt-search", function (e){
            if(e.keyCode === 13 && $table.find("tbody tr").length === 1){
                $table.find("tbody tr td:first-child input").click();
            }
        })

        $("body").on("click", "#selectAll", function () {
            if ($(this).is(":checked")) {
                $dataTable.rows().nodes().to$().find('input[type="checkbox"]').each(function () {
                    const $this = $(this); $this.prop('checked', true);
                    jugadoresSeleccionados.push(parseInt($this.val()));
                });
            } else {
                $dataTable.rows().nodes().to$().find('input[type="checkbox"]').each(function () {
                    const $this = $(this); $this.prop('checked', false);
                    jugadoresSeleccionados = jugadoresSeleccionados.filter(function(item) {return item !== parseInt($this.val())});
                });
            }
        });

        const $btnSeleccionarJugador = $("#btnSeleccionar{{$ViewName}}Jugador");
        $btnSeleccionarJugador.on("click", function(){
            if(jugadoresSeleccionados.length > 0){
                if(jugadoresSeleccionados.length === 1 && isNaN(jugadoresSeleccionados[0])) Toast.fire({icon: 'error', title: 'Por favor, seleccione al menos un jugador'});
                else {
                    const formData = new FormData();
                    formData.append('_token', $("meta[name=csrf-token]").attr("content"));
                    formData.append('ids', JSON.stringify(jugadoresSeleccionados));
                    formData.append('torneo_categoria_id', {{ $TorneoCategoria != null ? $TorneoCategoria->id : 0 }});
                    confirmAjax(`/auth/{{strtolower($ViewName)}}/jugador/store`, formData, "POST", "¿ Está seguro de agregar los jugadores seleccionados a la categoría {{ ($TorneoCategoria->categoriaSimple != null ? $TorneoCategoria->categoriaSimple->nombre : "-") }} ?", null, function (data) {
                        if(data.Success){
                            $modal.attr("data-reload", "true");
                            $modal.modal("hide");
                            //invocarModal(`/auth/torneo/jugador/partialViewMultipleZona/{{ $TorneoCategoria->torneo_id }}/{{ $TorneoCategoria->id }}`);
                        }else{ Toast.fire({icon: 'error', title: data.Message ? data.Message : 'Algo salió mal, hubo un error al guardar.'}); }
                    });
                }
            }else Toast.fire({icon: 'error', title: 'Por favor, seleccione al menos un jugador'});
        });

        $buttonRegistrarJugador.on("click", function(){
            invocarModalViewJugador();
        });

        $table.on("click", ".btn-update", function () {
            const id = $dataTable.row($(this).parents("tr")).data().id;
            invocarModalViewJugador(id);
        });

        $table.on("click", ".btn-delete", function () {
            const id = $dataTable.row($(this).parents("tr")).data().id;
            const nombre = $dataTable.row($(this).parents("tr")).data().nombre_completo;
            const formData = new FormData();
            formData.append('_token', $("meta[name=csrf-token]").attr("content"));
            formData.append('id', id);
            confirmAjax(`/auth/jugador/delete`, formData, "POST", `¿Está seguro de eliminar el registro ${nombre} ?`, null, function () {
                $dataTable.ajax.reload(null, false);
                jugadoresSeleccionados = jugadoresSeleccionados.filter(function(item) {return item !== parseInt(id)});
            });
        });

        invocarModalViewJugador = (id) => {
            invocarModal(`/auth/jugador/partialView/${id ? id : 0}`, function ($modal) {
                if ($modal.attr("data-reload") === "true") $dataTable.ajax.reload(null, false);
            });
        }

        reselectRows = () => {
            $dataTable.rows().nodes().to$().each(function (i, v){
                const $this = $(v);
                if(jugadoresSeleccionados.includes(parseInt($this.find('input[type="checkbox"]').val()))){
                    $this.find('input[type="checkbox"]').prop("checked", true);
                }
            });
        }

        /*Categorias Duplas*/

        const $tableDuplas = $("#table{{$ViewName}}Dupla");
        const $btnAgregarJugadores = $("#btnAgregarJugadores");

        let $arrayJugadores = [];

        $btnAgregarJugadores.on("click", function (){
            const $checkeds = $dataTable.rows().nodes().to$().find('input[type="checkbox"]:checked');
            if($checkeds.length === 2)
            {
                let $array = [];
                $.each($checkeds, function (i,v){
                    const $item = $(v);
                    $array.push({ key: parseInt($item.val()), value: $item.attr("data-info") });
                    $jugadoresNoDisponibles.push(parseInt($item.val()));
                });

                if($array.length > 0)
                {
                    const $keys  =  $array.map(u => u.key).join('-');
                    const $names =  $array.map(u => u.value).join(' + ');
                    if($arrayJugadores.length === 0){
                        $tableDuplas.append(`<tr><td><input type="hidden" name="jugadores[]" value="${$keys}">${$names}</td><td width="50" class="text-center"><button type="button" class="btn btn-danger btn-delete btn-xs"><i class="fa fa-trash"></i></button></td></tr>`);
                        $arrayJugadores.push({key: $keys, value: $names});
                        $.each($checkeds, function (i,v){$(v).prop("checked", false);});
                    }else{
                        if(!$arrayJugadores.some(x => x.key === $keys))
                        {
                            $tableDuplas.append(`<tr><td><input type="hidden" name="jugadores[]" value="${$keys}">${$names}</td><td width="50" class="text-center"><button type="button" class="btn btn-danger btn-delete btn-xs"><i class="fa fa-trash"></i></button></td></tr>`);
                            $arrayJugadores.push({key: $keys, value: $names});
                            $.each($checkeds, function (i,v){$(v).prop("checked", false);});
                        }else Toast.fire({icon: 'error', title: 'Esta dupla de jugadores, ya ha sido agregada anteriormente.'});
                    }
                    $dataTable.ajax.reload(null, false);
                }
            }else Toast.fire({icon: 'error', title: 'Por favor, seleccione una dupla vàlida.'});

            jugadoresSeleccionados = [];
        });

        $tableDuplas.on("click", ".btn-delete", function (){
            const $tr = $(this).closest("tr");
            const $key = $tr.find("input[type=hidden]").val();
            $arrayJugadores = $arrayJugadores.filter(x => { return x.key !== $key});
            $.each($key.split('-'), function (i2, v2){
                $jugadoresNoDisponibles = $jugadoresNoDisponibles.filter(x => { return x !== parseInt(v2); });
            });
            $tr.remove();
            $dataTable.ajax.reload(null, false);
        });

        const $btnSeleccionarJugadores = $("#btnSeleccionar{{$ViewName}}Jugadores");
        $btnSeleccionarJugadores.on("click", function(){
            if($arrayJugadores.length > 0){
                const formData = new FormData();
                formData.append('_token', $("meta[name=csrf-token]").attr("content"));
                formData.append('jugadores', JSON.stringify($arrayJugadores));
                formData.append('torneo_categoria_id', {{ $TorneoCategoria != null ? $TorneoCategoria->id : 0 }});
                confirmAjax(`/auth/{{strtolower($ViewName)}}/jugador/store`, formData, "POST", "¿ Está seguro de agregar los jugadores seleccionados a la categoría {{ ($TorneoCategoria->categoriaSimple != null ? $TorneoCategoria->categoriaSimple->nombre : "-") }} ?", null, function (data) {
                    if(data.Success){
                        $modal.attr("data-reload", "true");
                        $modal.modal("hide");
                        //invocarModal(`/auth/torneo/jugador/partialViewMultipleZona/{{ $TorneoCategoria->torneo_id }}/{{ $TorneoCategoria->id }}`);
                    }else{ Toast.fire({icon: 'error', title: data.Message ? data.Message : 'Algo salió mal, hubo un error al guardar.'});}
                });
            }else Toast.fire({icon: 'error', title: 'Por favor, seleccione al menos un jugador'});
        });

    })
</script>

