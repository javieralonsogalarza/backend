@inject('App', 'App\Models\App')

@if($TorneoCategoria != null && $TablePositions != null &&  count($TablePositions) > 0 &&
($TorneoCategoria->solo_ranking || count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('estado_id', $App::$ESTADO_FINALIZADO)->where('fase', 1)) > 0))
    <div class="row mt-1">
        <div class="col-md-12">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="50"></th>
                        <th>Nombre del Jugador</th>
                        <th width="50" align="center" class="align-middle text-center">Puntos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($TablePositions as $key => $q)
                        <tr>
                            <td width="50" align="center" class="align-middle text-center">{{ ($key+1) }}</td>
                            <td>{{ $q['nombres'] }}</td>
                            <td width="50">
                                <input class="form-control" type="hidden" name="ids[]" id="id_{{$key}}" value="{{ ($key+1) }}" readonly>
                                <input class="form-control" type="hidden" name="jugador_simple[]" id="jugador_simple_id_{{$key}}" value="{{ $q['jugador_simple_id'] }}" readonly>
                                <input class="form-control" type="hidden" name="jugador_dupla[]" id="jugador_dupla_id_{{$key}}" value="{{ $q['jugador_dupla_id']}}" readonly>
                                <input class="numeric form-input" type="text" name="puntos[]" id="punto_{{$key}}" value="{{ $q['puntos'] }}">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @if($TorneoCategoria->estado_id == \App\Models\App::$ESTADO_PENDIENTE && count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('estado_id', \App\Models\App::$ESTADO_PENDIENTE)->whereNotNull('fase')) == 0)
        <div class="row">
            <div class="col-md-12 text-right">
                <button type="button" class="btn btn-finish-torneo btn-primary">Generar Rankings </button>
            </div>
        </div>
    @endif
@else
    <div class="d-flex text-center align-items-center">
        <div><p>Los rakings aún no estan disponibles.</p></div>
    </div>
@endif


<script type="text/javascript">
    $(function (){

        @if($TorneoCategoria->estado_id == \App\Models\App::$ESTADO_FINALIZADO)
          $("#custom-tabs-{{ $TorneoCategoria->id }}").find("input, select, textarea").prop("disabled", true)
        @endif

        $("input.numeric").inputmask("numeric", { min: 0, digits: 0, removeMaskOnSubmit: true, groupSeparator: ",", groupSize: 3 });
        $(".btn-finish-torneo").on("click", function (){
            const $this = $(this);
            const formData = new FormData();
            formData.append('_token', $("meta[name=csrf-token]").attr("content"));
            formData.append('torneo_id', {{ $TorneoCategoria->torneo->id }});
            formData.append('torneo_categoria_id', {{ $TorneoCategoria->id }});
            formData.append('rakings', JSON.stringify(rankings($this)));
            confirmAjax(`/auth/{{ strtolower($ViewName)}}/finish`, formData, "POST", `¿Está seguro de finalizar el torneo de {{ $TorneoCategoria->multiple && ($TorneoCategoria->categoriaSimple->id !== $TorneoCategoria->categoriaDupla->id) ? ($TorneoCategoria->categoriaSimple->nombre." + ".$TorneoCategoria->categoriaDupla->nombre) : ($TorneoCategoria->categoriaSimple->nombre)."".($TorneoCategoria->multiple ? " (Doble) " : "") }} categoría?`, null, function (){
                invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $TorneoCategoria->torneo->id }}/{{ $TorneoCategoria->id }}/3`, function(data){
                    $("#main").addClass("hidden");$("#info").removeClass("hidden").html("").append(data);
                    invocarVista(`/auth/{{strtolower($ViewName)}}/ranking/{{ $TorneoCategoria->torneo->id }}/{{ $TorneoCategoria->id }}`, function(data){
                        $("#partialViewRanking{{ $TorneoCategoria->id }}").html("").append(data);
                    });
                });
            });
        });

        function rankings($button){
            let Rankings = [];
            $button.closest("#custom-tabs-fase-three-{{ $TorneoCategoria->id }}").find("input[name='ids[]']").map( function(key){
                const obj = {};
                obj.id = parseInt($(this).val());
                $button.closest("#custom-tabs-fase-three-{{ $TorneoCategoria->id }}").find("input[name='jugador_simple[]']").map( function(key1){if(key === key1) obj.jugador_simple_id = parseInt($(this).val());});
                $button.closest("#custom-tabs-fase-three-{{ $TorneoCategoria->id }}").find("input[name='jugador_dupla[]']").map( function(key2){if(key === key2) obj.jugador_dupla_id = parseInt($(this).val());});
                $button.closest("#custom-tabs-fase-three-{{ $TorneoCategoria->id }}").find("input[name='puntos[]']").map( function(key3){if(key === key3) obj.puntos = parseInt($(this).val().replaceAll(",",""));});
                Rankings.push(obj);
            });
            return Rankings;
        }
    });
</script>
