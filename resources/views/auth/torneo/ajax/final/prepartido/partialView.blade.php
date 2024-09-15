<link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/select2/css/select2.min.css') }}">
<div class="modal fade" id="modal{{$ViewName}}Jugador" role="dialog"  data-backdrop="static" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asignar Jugadores</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('auth.'.strtolower($ViewName).'.faseFinal'.'.prepartido'.'.store') }}" id="frm{{$ViewName}}" enctype="multipart/form-data" method="POST"
                  data-ajax="true" class="form" data-ajax-loading="#loading"  data-ajax-success="OnSuccess{{$ViewName}}"
                  data-ajax-failure="OnFailure{{$ViewName}}">
                @csrf
                <input type="hidden" id="id" name="id" value="{{ $Partido->id }}">
                <input type="hidden" id="position" name="position" value="{{ $Position }}">
                <input type="hidden" id="bracket" name="bracket" value="{{ $Bracket }}">
                <div class="modal-body">
                    <div class="row content-buy-all {{ $Partido->buy_all ? "hidden" : "" }}">
                        <div class="col-md-12">
                            <label for="jugador_local_id">{{ $TorneoCategoria->multiple ? "Jugadores Locales" : "Jugador Local" }}
                                <span id="jugador_local_info_id" style="color: red;font-size: 14px;"></span>
                            </label>
                            <select name="jugador_local_id" id="jugador_local_id" class="form-control" required style="width: 100% !important;"></select>
                            <span data-valmsg-for="jugador_local_id"></span>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-sm-6 mt-1 content-buy-all {{ $Partido->buy_all ? "hidden" : "" }}">
                            <div class="icheck-primary d-inline">
                                <input type="checkbox" name="buy" id="buy" value="1" {{ !$Partido->buy_all && $Partido->buy ? "checked" : "" }}>
                                <label for="buy">¿El Jugador Rival es Bye?</label>
                            </div>
                        </div>
                        <div class="col-sm-6 mt-1">
                            <div class="icheck-primary d-inline">
                                <input type="checkbox" name="buy_all" id="buy_all" value="1" {{ $Partido->buy_all ? "checked" : "" }}>
                                <label for="buy_all">¿Ambos jugadores son Bye?</label>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3 content-buy content-buy-all {{ $Partido->buy_all || $Partido->buy ? "hidden" : "" }}">
                        <div class="col-md-12">
                            <label for="jugador_rival_id">{{ $TorneoCategoria->multiple ? "Jugadores Rivales" : "Jugador Rival" }}
                                <span id="jugador_rival_info_id" style="color: red;font-size: 14px;"></span>
                            </label>
                            <select name="jugador_rival_id" id="jugador_rival_id" class="form-control" {{ $Partido->jugadorLocalUno != null && $Partido->buy ? "" : "required" }} style="width: 100% !important;"></select>
                            <span data-valmsg-for="jugador_rival_id"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                    @if($Partido != null && ($Partido->jugadorLocalUno != null || $Partido->jugadorRivalUno != null))
                        <button type="button" class="btn btn-danger btn-delete pull-right">Eliminar</button>
                    @endif
                    <button type="submit" class="btn btn-primary pull-right">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript" src="{{ asset('auth/adminlte3/plugins/select2/js/select2.js') }}"></script>
<script type="text/javascript">
    $(function(){
        const $modal = $("#modal{{$ViewName}}Jugador");
        const $jugador_local_id = $("#jugador_local_id");
        const $jugador_rival_id = $("#jugador_rival_id");

        const $buy = $("#buy"), $contentBuy = $(".content-buy");
        $buy.on("change", function (){
           if($(this).is(":checked")){
               $contentBuy.addClass("hidden");
               $contentBuy.find("select").val("").trigger("change");
               $contentBuy.find("select").prop("required", false);
           }else{
               $contentBuy.removeClass("hidden").prop("required", true);
           }
        });

        const $buy_all = $("#buy_all"), $contentBuyAll = $(".content-buy-all");
        $buy_all.on("change", function (){
            if($(this).is(":checked")){
                $contentBuyAll.addClass("hidden");
                $contentBuyAll.find("select").val("").trigger("change");
                $contentBuyAll.find("select").prop("required", false);
                $contentBuyAll.find("input[type=checkbox]").prop("checked", false);
            }else{
                $contentBuyAll.removeClass("hidden").prop("required", true);
            }
        });

        $jugador_local_id.select2({
            //minimumInputLength: 2,
            placeholder: "Buscar...",
            allowClear: true,
            ajax: {
                url: "/auth/torneo/fase-final/prepartido/jugador/list-json", dataType: "json", type: "GET", delay: 250,
                data: function(params) {
                    return {
                        nombre: params.term,
                        partido_id: {{ $Partido != null ? $Partido->id : 0 }},
                        torneo_id : {{ $TorneoCategoria->torneo_id }},
                        jugador_selected_id: $jugador_rival_id.val(),
                        torneo_categoria_id : {{ $TorneoCategoria->id }},
                    };
                },
                processResults: function(data) {
                    return { results: data.data };
                },
                cache: true
            }
        });

        $jugador_local_id.on("change", function (){
            $("#jugador_local_info_id").text("");
            const $this = $(this);
            if($this.val() != "" && $this.val() != null){
                const formData = new FormData();
                formData.append('_token', $("meta[name=csrf-token]").attr("content"));
                formData.append('torneo_id', {{ $TorneoCategoria->torneo_id }});
                formData.append('torneo_categoria_id', {{ $TorneoCategoria->id }});
                formData.append('jugador_id', $this.val());
                actionAjax(`/auth/torneo/fase-final/prepartido/jugadorInfo`, formData, "POST", function (data){
                    if(data.Success){
                        $("#jugador_local_info_id").text(' - ' + data.Message);
                    }
                });
            }else{
                $("#jugador_local_info_id").text("");
            }
        });

        $jugador_rival_id.on("change", function (){
            $("#jugador_rival_info_id").text("");
            const $this = $(this);
            if($this.val() != "" && $this.val() != null){
                const formData = new FormData();
                formData.append('_token', $("meta[name=csrf-token]").attr("content"));
                formData.append('torneo_id', {{ $TorneoCategoria->torneo_id }});
                formData.append('torneo_categoria_id', {{ $TorneoCategoria->id }});
                formData.append('jugador_id', $this.val());
                actionAjax(`/auth/torneo/fase-final/prepartido/jugadorInfo`, formData, "POST", function (data){
                    if(data.Success){
                        $("#jugador_rival_info_id").text(' - ' + data.Message);
                    }
                });
            }else{
                $("#jugador_rival_info_id").text("");
            }
        });

        @if($Partido != null && $Partido->jugadorLocalUno != null)
            const JugadorLocalOption = new Option('{{ $TorneoCategoria->multiple ? ($Partido->jugadorLocalUno->nombre_completo.' + '.$Partido->jugadorLocalDos->nombre_completo) : $Partido->jugadorLocalUno->nombre_completo}}', "{{ $TorneoCategoria->multiple ? ($Partido->jugador_local_uno_id.'-'.$Partido->jugador_local_dos_id) : $Partido->jugador_local_uno_id }}", true, true);
            $jugador_local_id.append(JugadorLocalOption).trigger("change");
        @endif

        $jugador_rival_id.select2({
            //minimumInputLength: 2,
            placeholder: "Buscar...",
            allowClear: true,
            ajax: {
                url: "/auth/torneo/fase-final/prepartido/jugador/list-json", dataType: "json", type: "GET", delay: 250,
                data: function(params) {
                    return {
                        nombre: params.term,
                        partido_id: {{ $Partido != null ? $Partido->id : 0 }},
                        torneo_id : {{ $TorneoCategoria->torneo_id }},
                        jugador_selected_id: $jugador_local_id.val(),
                        torneo_categoria_id : {{ $TorneoCategoria->id }},
                    };
                },
                processResults: function(data) {
                    return { results: data.data };
                },
                cache: true
            }
        });

        @if($Partido != null && $Partido->jugadorRivalUno != null)
            const JugadorRivalOption = new Option('{{ $TorneoCategoria->multiple ? ($Partido->jugadorRivalUno->nombre_completo.' + '.$Partido->jugadorRivalDos->nombre_completo) : $Partido->jugadorRivalUno->nombre_completo}}', "{{ $TorneoCategoria->multiple ? ($Partido->jugador_rival_uno_id.'-'.$Partido->jugador_rival_dos_id) : $Partido->jugador_rival_uno_id }}", true, true);
            $jugador_rival_id.append(JugadorRivalOption).trigger("change");
        @endif

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

        const $btnDelete = $("button.btn-delete");
        $btnDelete.on("click", function (){
            const formData = new FormData();
            formData.append('_token', $("meta[name=csrf-token]").attr("content"));
            formData.append('partido_id', {{ $Partido->id }});
            formData.append('torneo_id', {{ $TorneoCategoria->torneo->id }});
            formData.append('torneo_categoria_id', {{ $TorneoCategoria->id }});
            confirmAjax(`/auth/{{strtolower($ViewName)}}/fase-final/prepartido/delete`, formData, `POST`, `¿Está seguro de eliminar la llave generada ?`, null, function (data){
                if(data.Success){
                    $modal.attr("data-reload", "true");
                    $modal.modal("hide");
                }
            });
        });

        OnSuccess{{$ViewName}} = (data) => onSuccessForm(data, $("form#frm{{$ViewName}}"), $modal, function (data){
            if(data.Repeat != null){ Swal.fire({icon: 'warning', title: 'Los jugadores que acaba de asignar ya se enfrentaron con anterioridad.'}); }
        });
        OnFailure{{$ViewName}} = () => onFailureForm();
    })
</script>

