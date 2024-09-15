@inject('App', 'App\Models\App')
<div class="modal fade" id="modal{{$ViewName}}FinalPartido" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ ($Model->multiple ? (($Model->jugadorLocalUno != null ? $Model->jugadorLocalUno->nombre_completo : "-")." + ".($Model->jugadorLocalDos != null ? $Model->jugadorLocalDos->nombre_completo : "-")) : ($Model->jugadorLocalUno != null ? $Model->jugadorLocalUno->nombre_completo : "-"))." vs ".($Model->multiple ? ( ($Model->jugadorRivalUno != null ? $Model->jugadorRivalUno->nombre_completo : (!$Model->torneoCategoria->manual && $Model->buy ? "Bye" : "-" )). " + ".($Model->jugadorRivalDos != null ? $Model->jugadorRivalDos->nombre_completo : (!$Model->torneoCategoria->manual && $Model->buy ? "Bye" : "-" ))  ) : ($Model->jugadorRivalUno != null ? $Model->jugadorRivalUno->nombre_completo : (!$Model->torneoCategoria->manual && $Model->buy ? "Bye" : "-" )) ) }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('auth.'.strtolower($ViewName).'.faseFinal.partido'.'.store') }}" id="frm{{$ViewName}}FinalPartido" enctype="multipart/form-data" method="POST"
                  data-ajax="true" class="form" data-ajax-loading="#loading" data-ajax-success="OnSuccess{{$ViewName}}" autocomplete="off"
                  data-ajax-failure="OnFailure{{$ViewName}}">
                @csrf
                <input type="hidden" id="id" name="id" value="{{ $Model != null ? $Model->id : 0 }}" readonly>
                <input type="hidden" id="estado_id" name="estado_id" value="{{ $Model->estado_id }}" readonly>
                <input type="hidden" id="position" name="position" value="{{ $Position }}" readonly>
                <input type="hidden" value="{{ $Model->fase }}">
                <div class="modal-body">
                    <div class="form-group row">
                        <div class="col-sm-4">
                            <label for="fecha_inicio">Fecha Inicio: <span class="text-danger text-sm">(*)</span></label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ $Model->fecha_inicio }}" required>
                            <span data-valmsg-for="fecha_inicio"></span>
                        </div>
                        <div class="col-sm-4">
                            <label for="fecha_final">Fecha Final: <span class="text-danger">(*)</span></label>
                            <input type="date" name="fecha_final" id="fecha_final" class="form-control" value="{{ $Model->fecha_final }}" required>
                            <span data-valmsg-for="fecha_final"></span>
                        </div>
                        <div class="col-sm-4">
                            <label for="resultado">Resultado: <span class="text-danger">(*)</span></label>
                            <input type="text" name="resultado" id="resultado" class="form-control" value="{{ $Model->resultado }}" required>
                            <span data-valmsg-for="resultado"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <label for="jugador_local_id">Jugador Ganador: <span class="text-danger">(*)</span></label>
                            <select name="jugador_local_id" id="jugador_local_id" class="form-control">
                                <option value="" {{ $Model->estado_id == $App::$ESTADO_PENDIENTE ? "selected" : "" }}>Seleccione</option>
                                @if($Model->jugadorLocalUno != null)
                                <option value="{{ $Model->multiple ? ($Model->jugadorLocalUno->id.'-'.$Model->jugadorLocalDos->id) : $Model->jugadorLocalUno->id }}" {{ $Model->estado_id == $App::$ESTADO_PENDIENTE ? "" : ($Model->multiple ? (($Model->jugador_local_uno_id.'-'.$Model->jugador_local_dos_id) == ($Model->jugador_ganador_uno_id.'-'.$Model->jugador_ganador_dos_id) ? "selected" : "") : ($Model->jugador_local_uno_id == $Model->jugador_ganador_uno_id ? "selected" : "")) }}>{{ $Model->multiple ? ($Model->jugadorLocalUno->nombre_completo_temporal.' + '.$Model->jugadorLocalDos->nombre_completo_temporal) : $Model->jugadorLocalUno->nombre_completo_temporal }}</option>
                                @endif
                                @if($Model->jugadorRivalUno != null)
                                <option value="{{ $Model->multiple ? ($Model->jugadorRivalUno->id.'-'.$Model->jugadorRivalDos->id) : $Model->jugadorRivalUno->id }}" {{ $Model->estado_id == $App::$ESTADO_PENDIENTE ? "" : ($Model->multiple ? (($Model->jugador_rival_uno_id.'-'.$Model->jugador_rival_dos_id) == ($Model->jugador_ganador_uno_id.'-'.$Model->jugador_ganador_dos_id) ? "selected" : "") : ($Model->jugador_rival_uno_id == $Model->jugador_ganador_uno_id ? "selected" : "")) }}>{{ $Model->multiple ? ($Model->jugadorRivalUno->nombre_completo_temporal.' + '.$Model->jugadorRivalDos->nombre_completo_temporal) : $Model->jugadorRivalUno->nombre_completo_temporal }}</option>
                                @endif
                            </select>
                            <span data-valmsg-for="jugador_local_id"></span>
                        </div>
                        <div class="col-sm-3">
                            <label for="jugador_local_set">Set: <span class="text-danger">(*)</span></label>
                            <input type="text" name="jugador_local_set" id="jugador_local_set" class="form-control numeric-set" value="{{ $Model->jugador_local_set }}" required>
                            <span data-valmsg-for="jugador_local_set"></span>
                        </div>
                        <div class="col-sm-3">
                            <label for="jugador_local_juego">Games: <span class="text-danger">(*)</span></label>
                            <input type="text" name="jugador_local_juego" id="jugador_local_juego" class="form-control numeric-game" value="{{ $Model->jugador_local_juego }}" required>
                            <span data-valmsg-for="jugador_local_juego"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <label for="jugador_rival_id">Jugador Rival: <span class="text-danger">(*)</span></label>
                            <select name="jugador_rival_id" id="jugador_rival_id" class="form-control">
                                <option value="" {{ $Model->estado_id == $App::$ESTADO_PENDIENTE ? "selected" : "" }}>Seleccione</option>
                                @if($Model->jugadorLocalUno != null)
                                <option value="{{ $Model->multiple ? ($Model->jugadorLocalUno->id.'-'.$Model->jugadorLocalDos->id) : $Model->jugadorLocalUno->id }}" {{ $Model->estado_id == $App::$ESTADO_PENDIENTE ? "" : ($Model->multiple ? (($Model->jugador_local_uno_id.'-'.$Model->jugador_local_dos_id) != ($Model->jugador_ganador_uno_id.'-'.$Model->jugador_ganador_dos_id) ? "selected" : "") : ($Model->jugador_local_uno_id != $Model->jugador_ganador_uno_id ? "selected" : "")) }}>{{ $Model->multiple ? ($Model->jugadorLocalUno->nombre_completo_temporal.' + '.$Model->jugadorLocalDos->nombre_completo_temporal) : $Model->jugadorLocalUno->nombre_completo_temporal }}</option>
                                @endif
                                @if($Model->jugadorRivalUno != null)
                                <option value="{{ $Model->multiple ? ($Model->jugadorRivalUno->id.'-'.$Model->jugadorRivalDos->id) : $Model->jugadorRivalUno->id }}" {{ $Model->estado_id == $App::$ESTADO_PENDIENTE ? "" : ($Model->multiple ? (($Model->jugador_rival_uno_id.'-'.$Model->jugador_rival_dos_id) != ($Model->jugador_ganador_uno_id.'-'.$Model->jugador_ganador_dos_id) ? "selected" : "") : ($Model->jugador_rival_uno_id != $Model->jugador_ganador_uno_id ? "selected" : "")) }}>{{ $Model->multiple ? ($Model->jugadorRivalUno->nombre_completo_temporal.' + '.$Model->jugadorRivalDos->nombre_completo_temporal) : $Model->jugadorRivalUno->nombre_completo_temporal }}</option>
                                @endif
                            </select>
                            <span data-valmsg-for="jugador_rival_id"></span>
                        </div>
                        <div class="col-sm-3">
                            <label for="jugador_rival_set">Set: <span class="text-danger">(*)</span></label>
                            <input type="text" name="jugador_rival_set" id="jugador_rival_set" class="form-control numeric-set" value="{{ $Model->jugador_rival_set }}" autocomplete="off" required>
                            <span data-valmsg-for="jugador_rival_set"></span>
                        </div>
                        <div class="col-sm-3">
                            <label for="jugador_rival_juego">Games: <span class="text-danger">(*)</span></label>
                            <input type="text" name="jugador_rival_juego" id="jugador_rival_juego" class="form-control numeric-game" value="{{ $Model->jugador_rival_juego }}" autocomplete="off" required>
                            <span data-valmsg-for="jugador_rival_juego"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                    @if($Model->estado_id == $App::$ESTADO_PENDIENTE)
                        <button type="submit" class="btn btn-primary pull-right" data-id="{{ $App::$ESTADO_FINALIZADO }}">Finalizar</button>
                    @elseif($Model->estado_id == $App::$ESTADO_FINALIZADO && !$Model->permitir_edicion && $Model->fase > 1)
                        <button type="submit" class="btn btn-primary pull-right" data-id="{{ $App::$ESTADO_FINALIZADO }}">Modificar</button>
                    @endif

                    @if($Model->estado_id == $App::$ESTADO_FINALIZADO)
                        <button type="button" class="btn btn-primary btn-generate-json pull-right">Generar Json</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function(){
        const $modal = $("#modal{{$ViewName}}FinalPartido");

        $modal.find("input, select").prop("disabled", {{ $Model->estado_id == $App::$ESTADO_FINALIZADO && ($Model->permitir_edicion || $Model->fase == 1) }});

        $("input.numeric").inputmask("numeric", { min: 1, digits: 0, removeMaskOnSubmit: true, groupSeparator: ",", groupSize: 3 });

        $("input.numeric-set").inputmask("numeric", { min: 0, max : 3, digits: 0, removeMaskOnSubmit: true, groupSeparator: ",", groupSize: 3, placeholder: "" });
        $("input.numeric-game").inputmask("numeric", { min: 0, max : 99, digits: 0, removeMaskOnSubmit: true, groupSeparator: ",", groupSize: 3, placeholder: "" });

        const $resultado = $("#resultado");
        const $jugador_local_set = $("#jugador_local_set"), $jugador_local_juego = $("#jugador_local_juego");
        const $jugador_rival_set = $("#jugador_rival_set"), $jugador_rival_juego = $("#jugador_rival_juego");

        $(".btn-generate-json").on("click", function (){
            window.open(`/auth/{{strtolower($ViewName)}}/partido/export/json?id={{ $Model->id }}`);
        })

        $resultado.on("change", function (){
            const $this = $(this);
            if(["wo", "w.o", "WO", "W.O"].includes($this.val())){
                $jugador_local_set.val(2);
                $jugador_local_juego.val(12);
                $jugador_rival_set.val(0);
                $jugador_rival_juego.val(0);
            }else if(["0"].includes($this.val())){
                const formData = new FormData();
                formData.append('_token', $("meta[name=csrf-token]").attr("content"));
                formData.append("torneo_id", {{ $Model->torneo_id }});
                formData.append("partido_id", {{ $Model->id }});
                actionAjax(`/auth/{{strtolower($ViewName)}}/partido/reset`, formData, 'POST', function (data){
                    if(data.Success){
                        $modal.find("select").val("");
                        $jugador_local_set.val("");$jugador_local_juego.val("");
                        $jugador_rival_set.val("");$jugador_rival_juego.val("");
                        $modal.attr("data-reload", "true");
                        $modal.modal("hide");
                    }
                });
            }else {
                const sets = $this.val().split('/');
                if(sets.length > 0){
                    let setsLocal = 0; let gamesLocal = 0; let setsRival = 0; let gamesRival = 0;
                    $.each(sets, function (i, v){
                        const games = v.split('-');
                        const $GameLeft = parseInt(games[0].match(/\d+/)[0]);
                        const $GameRight = parseInt(games[1].match(/\d+/)[0]);
                        if(i <= 1){
                            gamesLocal += $GameLeft;
                            gamesRival += $GameRight;
                        }
                        if($GameLeft > $GameRight) setsLocal+=1;
                        else if($GameRight > $GameLeft)  setsRival+=1;
                    });
                    $jugador_local_set.val(setsLocal); $jugador_local_juego.val(gamesLocal);
                    $jugador_rival_set.val(setsRival); $jugador_rival_juego.val(gamesRival);
                }
            }
        });

        $("button[type=submit]").on("click", function (){ const $this = $(this); $("#estado_id").val($this.attr("data-id"));  })

        OnSuccess{{$ViewName}} = (data) => onSuccessForm(data, $("form#frm{{$ViewName}}FinalPartido"), $modal);
        OnFailure{{$ViewName}} = () => onFailureForm();
    })
</script>
