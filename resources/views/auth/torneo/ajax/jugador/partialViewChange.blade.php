<link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/select2/css/select2.min.css') }}">
<div class="modal fade" id="modal{{$ViewName}}JugadorChange" role="dialog"  data-backdrop="static" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Remplazar Jugador</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('auth.'.strtolower($ViewName).'.jugador'.'.change') }}" id="frm{{$ViewName}}" enctype="multipart/form-data" method="POST"
                  data-ajax="true" class="form" data-ajax-loading="#loading"  data-ajax-success="OnSuccess{{$ViewName}}"
                  data-ajax-failure="OnFailure{{$ViewName}}">
                @csrf
                <input type="hidden" id="torneo_id" name="torneo_id" value="{{ $TorneoCategoria->torneo_id }}">
                <input type="hidden" id="multiple" name="multiple" value="{{ $TorneoCategoria->multiple }}">
                <input type="hidden" id="categoria_id" name="torneo_categoria_id" value="{{ $TorneoCategoria->id }}">
                <input type="hidden" id="jugador_simple_id" name="jugador_simple_id" value="{{ $JugadorSimple->id }}">
                <input type="hidden" id="jugador_dupla_id" name="jugador_dupla_id" value="{{ $TorneoCategoria->multiple ? $JugadorDupla->id : 0 }}">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 text-right mb-3">
                            <button id="btnAgregarJugador" type="button" class="btn btn-md btn-primary pull-right"><i class="fa fa-plus"></i> Agregar Jugador</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <p class="text-danger">
                                <strong>Nota:</strong> Esta acción remplazará {{ $TorneoCategoria->multiple ? " a los jugadores: " : " al jugador: " }}
                                <br>
                                @if($TorneoCategoria->multiple)
                                    Jugador(a) de reemplazo 1 {{ $JugadorSimple->nombre_completo }} (si no requiere reemplazar a este jugador no realice acción alguna en la lista 1)
                                    <br>
                                    Jugador(a) de reemplazo 2 {{ $JugadorDupla->nombre_completo }} (si no requiere reemplazar a este jugador no realice acción alguna en la lista 2)
                                @else
                                    Jugador(a) de reemplazo {{ $JugadorSimple->nombre_completo }} (si no requiere reemplazar a este jugador no realice acción alguna en la lista)
                                @endif
                                <br>
                                En todos los partidos y grupos asignados por el jugador que seleccione.
                            </p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <label for="jugador_remplazo_id">Jugador de Reemplazo {{ $TorneoCategoria->multiple ? "1" : "" }}</label>
                            <select name="jugador_remplazo_id" id="jugador_remplazo_id" class="form-control" {{ $TorneoCategoria->multiple ? "" : "required" }} style="width: 100% !important;"></select>
                            <span data-valmsg-for="jugador_remplazo_id"></span>
                        </div>
                    </div>
                    @if($TorneoCategoria->multiple)
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label for="jugador_remplazo_dupla_id">Jugador de Reemplazo 2</label>
                                <select name="jugador_remplazo_dupla_id" id="jugador_remplazo_dupla_id" class="form-control" style="width: 100% !important;"></select>
                                <span data-valmsg-for="jugador_remplazo_dupla_id"></span>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary pull-right">Remplazar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript" src="{{ asset('auth/adminlte3/plugins/select2/js/select2.js') }}"></script>
<script type="text/javascript">
    $(function(){
        const $modal = $("#modal{{$ViewName}}JugadorChange");
        const $jugador_remplazo_id = $("#jugador_remplazo_id");
        const $jugador_remplazo_dupla_id = $("#jugador_remplazo_dupla_id");

        //$jugador_remplazo_id.select2({minimumInputLength: 2});
        //$jugador_remplazo_dupla_id.select2({minimumInputLength: 2});

        $jugador_remplazo_id.select2({
            minimumInputLength: 2,
            placeholder: "Buscar...",
            allowClear: true,
            ajax: {
                url: "/auth/torneo/jugador/available/list-json", dataType: "json", type: "GET", delay: 250,
                data: function(params) {
                    return {
                        nombre: params.term,
                        torneo_id : {{ $TorneoCategoria->torneo_id }},
                        jugador_selected_id: JSON.stringify(isNaN(parseInt($jugador_remplazo_dupla_id.val())) ? [] : [parseInt($jugador_remplazo_dupla_id.val())]),
                        torneo_categoria_id : {{ $TorneoCategoria->id }},
                    };
                },
                processResults: function(data) {
                    return { results: data.data };
                },
                cache: true
            }
        });

        $jugador_remplazo_dupla_id.select2({
            minimumInputLength: 2,
            placeholder: "Buscar...",
            allowClear: true,
            ajax: {
                url: "/auth/torneo/jugador/available/list-json", dataType: "json", type: "GET", delay: 250,
                data: function(params) {
                    return {
                        nombre: params.term,
                        torneo_id : {{ $TorneoCategoria->torneo_id }},
                        jugador_selected_id: JSON.stringify(isNaN(parseInt($jugador_remplazo_id.val())) ? [] : [parseInt($jugador_remplazo_id.val())]),
                        torneo_categoria_id : {{ $TorneoCategoria->id }},
                    };
                },
                processResults: function(data) {
                    return { results: data.data };
                },
                cache: true
            }
        });

        /*$jugador_remplazo_id.on("change", function (){
            if(parseInt($jugador_remplazo_dupla_id.val()) === parseInt($(this).val()))
            {
                Toast.fire({icon: 'error', title: 'No puede escoger el mismo jugador, por favor escoga otro'});
                $jugador_remplazo_id.val("").trigger("change");
            }
        });

        $jugador_remplazo_dupla_id.on("change", function (){
            if(parseInt($jugador_remplazo_id.val()) === parseInt($(this).val()))
            {
                Toast.fire({icon: 'error', title: 'No puede escoger el mismo jugador, por favor escoga otro'});
                $jugador_remplazo_dupla_id.val("").trigger("change");
            }
        });*/

        const $btnAgregarJugador = $("#btnAgregarJugador");
        $btnAgregarJugador.on("click", function (){
            invocarModal(`/auth/jugador/partialView/0`, function ($modal) {
                if ($modal.attr("data-reload") === "true") {
                    //$jugador_remplazo_id.val("").trigger("change");
                    //$jugador_remplazo_dupla_id.val("").trigger("change");
                }
            });
        });

        OnSuccess{{$ViewName}} = (data) => onSuccessForm(data, $("form#frm{{$ViewName}}"), $modal);
        OnFailure{{$ViewName}} = () => onFailureForm();
    })
</script>

