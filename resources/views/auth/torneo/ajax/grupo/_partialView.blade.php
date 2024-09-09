<link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/select2/css/select2.min.css') }}">
<div class="modal fade" id="modal{{$ViewName}}"  role="dialog" data-backdrop="static" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ ($Model != null ? "Modificar" : "Registrar")." ".$ViewName. " Grupo" }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('auth.'.strtolower($ViewName).'.grupo'.'.store') }}" id="frm{{$ViewName}}" enctype="multipart/form-data" method="POST"
                  data-ajax="true" class="form" data-ajax-loading="#loading" data-ajax-success="OnSuccess{{$ViewName}}"
                  data-ajax-failure="OnFailure{{$ViewName}}">
                @csrf
                <input type="hidden" id="id" name="id" value="{{ $Model != null ? $Model->id : 0 }}">
                <input type="hidden" id="torneo_categoria_id" name="torneo_categoria_id" value="{{ $TorneoCategoria != null ? $TorneoCategoria->id : 0 }}">
                <div class="modal-body">
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <label for="grupo_id">Grupo: </label>
                            <select name="grupo_id" id="grupo_id" class="form-control" required>
                                @foreach($Grupos as $q)
                                    <option value="{{ $q->id }}" {{ $Model != null && $Model->grupo_id == $q->id ? "selected" : "" }}>{{ $q->nombre }}</option>
                                @endforeach
                            </select>
                            <span data-valmsg-for="grupo_id"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <label for="jugador_id">Jugadores: </label>
                            <div class="select2-primary">
                                <select name="jugador_id[]" id="jugador_id" required="required" class="select2" multiple="multiple" data-placeholder="Jugador(es)" required data-initial="{{ ($Model != null && count($Model->torneoJugador->pluck('jugador_id')->toArray()) > 0 ) ?  implode(",", $Model->torneoJugador->pluck('jugador_id')->toArray())  : "" }}"  style="width: 100%;">
                                    @foreach($Jugadores as $q)
                                        <option value="{{ $q->id }}">{{ $q->nombre_completo }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <span data-valmsg-for="jugador_id"></span>
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
        const $modal = $("#modal{{$ViewName}}"); const $jugador_id = $("#jugador_id");
        setTimeout(function (){ $modal.find("form").find("input[type=text]").first().focus().select(); }, 500);

        $jugador_id.select2();

        if ($jugador_id.attr("data-initial").length !== 0) {
            $jugador_id.val($jugador_id.attr("data-initial").split(",")).trigger("change");
        }

        OnSuccess{{$ViewName}} = (data) => onSuccessForm(data, $("form#frm{{$ViewName}}"), $modal);
        OnFailure{{$ViewName}} = () => onFailureForm();
    })
</script>

