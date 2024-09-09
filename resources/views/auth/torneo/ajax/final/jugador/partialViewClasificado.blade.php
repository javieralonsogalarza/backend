<link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/select2/css/select2.min.css') }}">
<div class="modal fade" id="modal{{$ViewName}}JugadorChange" role="dialog"  data-backdrop="static" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Remplazar Jugador Clasificado</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('auth.'.strtolower($ViewName).'.jugador'.'.classification.change') }}" id="frm{{$ViewName}}" enctype="multipart/form-data" method="POST"
                  data-ajax="true" class="form" data-ajax-loading="#loading"  data-ajax-success="OnSuccess{{$ViewName}}"
                  data-ajax-failure="OnFailure{{$ViewName}}">
                @csrf
                <input type="hidden" id="torneo_id" name="torneo_id" value="{{ $TorneoCategoria->torneo_id }}">
                <input type="hidden" id="multiple" name="multiple" value="{{ $TorneoCategoria->multiple }}">
                <input type="hidden" id="categoria_id" name="torneo_categoria_id" value="{{ $TorneoCategoria->id }}">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <p class="text-danger">
                                <strong>Nota:</strong> Esta acción remplazará {{ $TorneoCategoria->multiple ? " a los jugadores clasificados " : " al jugador clasificado " }}
                                que escoga en el "Mapa del campeonato".
                            </p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <label for="jugador_remplazar_id">{{ $TorneoCategoria->multiple ? "Jugadores Clasificados" : "Jugador Clasificado" }} a Reemplazar</label>
                            <select name="jugador_remplazar_id" id="jugador_remplazar_id" class="form-control" required style="width: 100% !important;"></select>
                            <span data-valmsg-for="jugador_remplazar_id"></span>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label for="jugador_remplazo_id">{{ $TorneoCategoria->multiple ? "Jugadores" : "Jugador" }} de Reemplazo </label>
                            <select name="jugador_remplazo_id" id="jugador_remplazo_id" class="form-control" required style="width: 100% !important;"></select>
                            <span data-valmsg-for="jugador_remplazo_id"></span>
                        </div>
                    </div>
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
        const $jugador_remplazar_id = $("#jugador_remplazar_id");
        const $jugador_remplazo_id = $("#jugador_remplazo_id");

        $jugador_remplazar_id.select2({
            placeholder: "Buscar...",
            allowClear: true,
            ajax: {
                url: "/auth/torneo/jugador/available/classification/list-json", dataType: "json", type: "GET", delay: 250,
                data: function(params) {
                    return {
                        nombre: params.term,
                        torneo_id : {{ $TorneoCategoria->torneo_id }},
                        torneo_categoria_id : {{ $TorneoCategoria->id }},
                    };
                },
                processResults: function(data) {
                    return { results: data.data };
                },
                cache: true
            }
        });

        $jugador_remplazo_id.select2({
            placeholder: "Buscar...",
            allowClear: true,
            ajax: {
                url: "/auth/torneo/jugador/available/not-classification/list-json", dataType: "json", type: "GET", delay: 250,
                data: function(params) {
                    return {
                        nombre: params.term,
                        torneo_id : {{ $TorneoCategoria->torneo_id }},
                        torneo_categoria_id : {{ $TorneoCategoria->id }},
                    };
                },
                processResults: function(data) {
                    return { results: data.data };
                },
                cache: true
            }
        });

        OnSuccess{{$ViewName}} = (data) => onSuccessForm(data, $("form#frm{{$ViewName}}"), $modal);
        OnFailure{{$ViewName}} = () => onFailureForm();
    })
</script>

