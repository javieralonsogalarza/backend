<link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/select2/css/select2.min.css') }}">
<div class="modal fade" id="modal{{$ViewName}}" role="dialog" tabindex="-1" data-backdrop="static" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ ($Model != null ? "Modificar" : "Registrar")." ".$ViewName. " Partido" }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('auth.'.strtolower($ViewName).'.grupo.partido'.'.store') }}" id="frm{{$ViewName}}" enctype="multipart/form-data" method="POST"
                  data-ajax="true" class="form" data-ajax-loading="#loading" data-ajax-success="OnSuccess{{$ViewName}}"
                  data-ajax-failure="OnFailure{{$ViewName}}">
                @csrf
                <input type="hidden" id="id" name="id" value="{{ $Model != null ? $Model->id : 0 }}">
                <input type="hidden" id="torneo_grupo_id" name="torneo_grupo_id" value="{{ $torneo_grupo_id }}">
                <input type="hidden" id="torneo_categoria_id" name="torneo_categoria_id" value="{{ $TorneoCategoria != null ? $TorneoCategoria->id : 0 }}">
                <div class="modal-body">
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <label for="fecha_inicio">Fecha Inicio:</label>
                            <input type="date" id="fecha_inicio" class="form-control" name="fecha_inicio" value="{{ $Model != null ? $Model->fecha_inicio : "" }}" required>
                            <span data-valmsg-for="fecha_inicio"></span>
                        </div>
                        <div class="col-sm-6">
                            <label for="fecha_final">Fecha Final:</label>
                            <input type="date" id="fecha_final" class="form-control" name="fecha_final" value="{{ $Model != null ? $Model->fecha_final : "" }}" required>
                            <span data-valmsg-for="fecha_final"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <label for="jugador_uno_id">Jugador 1: </label>
                            <div class="select2-primary">
                                <select name="jugador_uno_id" id="jugador_uno_id" class="select2" data-placeholder="Jugador 1" required style="width: 100%;"></select>
                            </div>
                            <span data-valmsg-for="jugador_id"></span>
                        </div>
                        <div class="col-sm-6">
                            <label for="jugador_dos_id">Jugador 2: </label>
                            <div class="select2-primary">
                                <select name="jugador_dos_id" id="jugador_dos_id" class="select2" data-placeholder="Jugador 2" required style="width: 100%;"></select>
                            </div>
                            <span data-valmsg-for="jugador_dos_id"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary pull-right">{{ ($Model != null ? "Modificar" : "Registrar") }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript" src="{{ asset('auth/adminlte3/plugins/select2/js/select2.js') }}"></script>
<script type="text/javascript">
    $(function(){
        const $modal = $("#modal{{$ViewName}}");
        const $jugador_uno_id = $("#jugador_uno_id"), $jugador_dos_id = $("#jugador_dos_id");
        setTimeout(function (){ $modal.find("form").find("input[type=text]").first().focus().select(); }, 500);

        $jugador_uno_id.select2({
            ajax: {
                url: "/auth/jugador/list-json", dataType: "json", type: "GET", delay: 250,
                data: function(params) {
                    return {
                        select2: true, nombre: params.term,
                        jugador_selected_id : isNaN(parseInt($jugador_dos_id.val())) ? 0 : parseInt($jugador_dos_id.val()),
                        torneo_grupo_id: {{ $torneo_grupo_id }}, torneo_categoria_id: {{ $TorneoCategoria != null ? $TorneoCategoria->id : 0 }}
                    };
                },
                processResults: function(data) {
                    return { results: data.data };
                },
                cache: true
            }
        });

        $jugador_dos_id.select2({
            ajax: {
                url: "/auth/jugador/list-json", dataType: "json", type: "GET", delay: 250,
                data: function(params) {
                    return {
                        select2: true, nombre: params.term,
                        jugador_selected_id : isNaN(parseInt($jugador_uno_id.val())) ? 0 : parseInt($jugador_uno_id.val()),
                        torneo_grupo_id: {{ $torneo_grupo_id }}, torneo_categoria_id: {{ $TorneoCategoria != null ? $TorneoCategoria->id : 0 }}
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

