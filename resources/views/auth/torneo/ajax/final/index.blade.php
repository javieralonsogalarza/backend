@inject('App', 'App\Models\App')

@if($TorneoFaseFinal != null && count($TorneoFaseFinal->JugadoresClasificados) > 0 &&
(
(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('estado_id', $App::$ESTADO_PENDIENTE)) == 0 &&
count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')) == 0) ||
(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('estado_id', $App::$ESTADO_PENDIENTE)->whereNotNull('fase')) > 0) ||
(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('estado_id', $App::$ESTADO_FINALIZADO)->where('fase', 1)) > 0)
)
)
    <div class="d-flex justify-content-between align-items-center">
        <div><h5>Jugadores Clasificados</h5></div>
    </div>
    <div class="row mt-1">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="mt-2 table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th class="align-middle text-center" align="center"></th>
                        <th class="align-middle text-center" align="center">Jugadores</th>
                        <th class="align-middle text-center" align="center">Set Ganados</th>
                        <th class="align-middle text-center" align="center">Set Perdidos</th>
                        <th class="align-middle text-center" align="center" style="background-color: #0101be !important;">Diferencia Sets</th>
                        <th class="align-middle text-center" align="center">Games Ganados</th>
                        <th class="align-middle text-center" align="center">Games Perdidos</th>
                        <th class="align-middle text-center" align="center" style="background-color: #0101be !important;">Diferencia Games</th>
                        <th class="align-middle text-center" align="center" style="background-color: #c40a0a !important;">Puntos</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($TorneoFaseFinal->JugadoresClasificados as $key => $q)
                        <tr>
                            <td class="align-middle text-center" align="center">{{ ($key + 1) }}</td>
                            <td class="align-middle text-center" align="center">{{ $q['nombres'] }}</td>
                            <td class="align-middle text-center" align="center">{{ $q['setsGanados'] }}</td>
                            <td class="align-middle text-center" align="center">{{ $q['setsPerdidos'] }}</td>
                            <td class="align-middle text-center" align="center">{{ $q['setsDiferencias'] }}</td>
                            <td class="align-middle text-center" align="center">{{ $q['gamesGanados'] }}</td>
                            <td class="align-middle text-center" align="center">{{ $q['gamesPerdidos'] }}</td>
                            <td class="align-middle text-center" align="center">{{ $q['gamesDiferencias'] }}</td>
                            <td class="align-middle text-center" align="center">{{ $q['puntos'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if(!$landing)
        @if(
        count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('estado_id', $App::$ESTADO_PENDIENTE)) == 0 &&
        count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')) == 0)
            <div class="row mt-3">
                <ul class="w-100 d-flex align-content-center justify-content-end list-unstyled p-0">
                    <li class="mr-1"><button type="button" class="btn btn-primary btn-add-three-players-final" data-id="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-reload="0"><i class="fa fa-users"></i> ¿Agregar Mejores 3ros?</button></li>
                    <li class="mr-1"><button type="button" class="btn btn-primary btn-generate-keys-manual-final" data-id="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-reload="0"><i class="fa fa-key"></i> Generar llaves</button></li>
                    <!--<li class="mr-1"><button type="button" class="btn btn-primary btn-generate-keys-random-final" data-id="{{ $TorneoFaseFinal->TorneoCategoria->id }}"><i class="fa fa-key"></i> Keys aleatorias</button></li>
                    <li class="mr-1"><button type="button" class="btn btn-primary btn-generate-keys-final" data-id="{{ $TorneoFaseFinal->TorneoCategoria->id }}"><i class="fa fa-key"></i> Keys por puesto</button></li>-->
                </ul>
            </div>
        @elseif($TorneoFaseFinal->TorneoCategoria->manual)
            <div class="row mt-3">
                <ul class="w-100 d-flex align-content-center justify-content-end list-unstyled p-0">
                    <li class="mr-1"><button type="button" class="btn btn-primary btn-add-three-players-final" data-id="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-reload="0"><i class="fa fa-users"></i> ¿Agregar Mejores 3ros?</button></li>
                    <li class="mr-1"><button type="button" class="btn btn-primary btn-generate-keys-manual-final" data-id="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-reload="1"><i class="fa fa-sync"></i> Volver a generar llaves</button></li>
                </ul>
            </div>
        @elseif($TorneoFaseFinal->TorneoCategoria->aleatorio && count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('estado_id', \App\Models\App::$ESTADO_FINALIZADO)->whereNotNull('fase')) <= 0)
            <div class="row mt-3">
                <ul class="w-100 d-flex align-content-center justify-content-end list-unstyled p-0">
                    <li class="mr-1"><button type="button" class="btn btn-primary btn-reload-keys-final" data-random="{{ $TorneoFaseFinal->TorneoCategoria->aleatorio }}" data-id="{{ $TorneoFaseFinal->TorneoCategoria->id  }}"><i class="fa fa-sync"></i> Volver a generar llaves aleatorias</button></li>
                </ul>
            </div>
        @endif
    @endif

    <div id="mapaCampeonato{{ $TorneoFaseFinal->TorneoCategoria->id }}" class="has-map-bg"></div>

@else
    <div class="d-flex text-center align-items-center">
        <div><p>La segunda fase aún no esta disponible.</p></div>
    </div>
@endif

<script type="text/javascript">
    $(function (){

        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')) > 0)
            refrescarMapa();
        @endif

        const $btnGenerateKeysRandomFinal = $(".btn-generate-keys-random-final");
        $btnGenerateKeysRandomFinal.on("click", function (){
            const formData = new FormData();
            formData.append('_token', $("meta[name=csrf-token]").attr("content"));
            formData.append('torneo_id', {{ $TorneoFaseFinal->TorneoCategoria->torneo->id }});
            formData.append('torneo_categoria_id', {{ $TorneoFaseFinal->TorneoCategoria->id }});
            formData.append('tipo', 'random');
            confirmAjax(`/auth/{{strtolower($ViewName)}}/fase-final/store`, formData, `POST`,
                `¿Está seguro de generar las llaves de manera aleatoria para la segunda fase ?`, null, function (data){
                    if(data.Success){
                        invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $TorneoFaseFinal->TorneoCategoria->torneo->id }}/{{ $TorneoFaseFinal->TorneoCategoria->id }}/2`, function(data){
                            $("#main").addClass("hidden");$("#info").removeClass("hidden").html("").append(data);
                            invocarVista(`/auth/{{strtolower($ViewName)}}/fase-final-final/{{ $TorneoFaseFinal->TorneoCategoria->torneo->id }}/{{ $TorneoFaseFinal->TorneoCategoria->id }}`, function(data){
                                $("#partialViewFinal{{ $TorneoFaseFinal->TorneoCategoria->id }}").html("").append(data);
                            });
                        });
                    }else Toast.fire({icon: 'error', title: 'Algo salió mal, hubo un error al guardar.'});
                });
        });

        const $btnGenerateKeysFinal = $(".btn-generate-keys-final");
        $btnGenerateKeysFinal.on("click", function (){
            const formData = new FormData();
            formData.append('_token', $("meta[name=csrf-token]").attr("content"));
            formData.append('torneo_id', {{ $TorneoFaseFinal->TorneoCategoria->torneo->id }});
            formData.append('torneo_categoria_id', {{ $TorneoFaseFinal->TorneoCategoria->id }});
            formData.append('tipo', 'points');
            confirmAjax(`/auth/{{strtolower($ViewName)}}/fase-final/store`, formData, `POST`,
                `¿Está seguro de generar las llaves de por orden de puesto para la segunda fase ?`, null, function (data){
                    if(data.Success){
                        invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $TorneoFaseFinal->TorneoCategoria->torneo->id }}/{{ $TorneoFaseFinal->TorneoCategoria->id }}/2`, function(data){
                            $("#main").addClass("hidden");$("#info").removeClass("hidden").html("").append(data);
                            invocarVista(`/auth/{{strtolower($ViewName)}}/fase-final-final/{{ $TorneoFaseFinal->TorneoCategoria->torneo->id }}/{{ $TorneoFaseFinal->TorneoCategoria->id }}`, function(data){
                                $("#partialViewFinal{{ $TorneoFaseFinal->TorneoCategoria->id }}").html("").append(data);
                            });
                        });
                    }else Toast.fire({icon: 'error', title: 'Algo salió mal, hubo un error al guardar.'});
                });
        });

        const $btnReloadKeysFinal = $(".btn-reload-keys-final")
        $btnReloadKeysFinal.on("click", function (){
            const formData = new FormData();
            formData.append('_token', $("meta[name=csrf-token]").attr("content"));
            formData.append('torneo_id', {{ $TorneoFaseFinal->TorneoCategoria->torneo->id }});
            formData.append('torneo_categoria_id', {{ $TorneoFaseFinal->TorneoCategoria->id }});
            formData.append('tipo', 'random');
            confirmAjax(`/auth/{{strtolower($ViewName)}}/fase-final/reload`, formData, `POST`, `¿Está seguro de volver a generar las llaves de manera aleatoria ?`, null, function (data){
                if(data.Success){
                    invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $TorneoFaseFinal->TorneoCategoria->torneo->id }}/{{ $TorneoFaseFinal->TorneoCategoria->id }}/2`, function(data){
                        $("#main").addClass("hidden");$("#info").removeClass("hidden").html("").append(data);
                        invocarVista(`/auth/{{strtolower($ViewName)}}/fase-final-final/{{ $TorneoFaseFinal->TorneoCategoria->torneo->id }}/{{ $TorneoFaseFinal->TorneoCategoria->id }}`, function(data){
                            $("#partialViewFinal{{ $TorneoFaseFinal->TorneoCategoria->id }}").html("").append(data);
                        });
                    });
                }else Toast.fire({icon: 'error', title: 'Algo salió mal, hubo un error al guardar.'});
            });
        });



        const $btnAddThreePlayersFinal = $(".btn-add-three-players-final");
        $btnAddThreePlayersFinal.on("click", function (){
            invocarModal(`/auth/{{strtolower($ViewName)}}/fase-final/players/terceros/{{$TorneoFaseFinal->TorneoCategoria->torneo->id}}/{{$TorneoFaseFinal->TorneoCategoria->id}}`,
                function ($modal) {
                if ($modal.attr("data-reload") === "true"){
                    invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $TorneoFaseFinal->TorneoCategoria->torneo->id }}/{{ $TorneoFaseFinal->TorneoCategoria->id }}/2`, function(data){
                        $("#main").addClass("hidden");$("#info").removeClass("hidden").html("").append(data);
                        invocarVista(`/auth/{{strtolower($ViewName)}}/fase-final-final/{{ $TorneoFaseFinal->TorneoCategoria->torneo->id }}/{{ $TorneoFaseFinal->TorneoCategoria->id }}`, function(data){
                            $("#partialViewFinal{{ $TorneoFaseFinal->TorneoCategoria->id }}").html("").append(data);
                        });
                    });
                }
            });
        });

        const $btnGenerateKeysManualFinal = $(".btn-generate-keys-manual-final");
        $btnGenerateKeysManualFinal.on("click", function (){
            const $this = $(this);
            const reload = parseInt($this.attr("data-reload"));
            const formData = new FormData();
            formData.append('_token', $("meta[name=csrf-token]").attr("content"));
            formData.append('torneo_id', {{ $TorneoFaseFinal->TorneoCategoria->torneo->id }});
            formData.append('torneo_categoria_id', {{ $TorneoFaseFinal->TorneoCategoria->id }});
            formData.append('tipo', 'manual');
            formData.append('reload', reload);
            confirmAjax(`/auth/{{strtolower($ViewName)}}/fase-final/store`, formData, `POST`,
                (reload === 1 ? `¿Está seguro de volver a generar las llaves de manera manual para la segunda fase?. Ten en cuenta que los datos guardados previamente serán borrados` :
                    `¿Está seguro de generar las llaves de manera manual para la segunda fase?`), null, function (data){
                    if(data.Success){
                        if(reload === 1){
                            refrescarMapa();
                        }else{
                            invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $TorneoFaseFinal->TorneoCategoria->torneo->id }}/{{ $TorneoFaseFinal->TorneoCategoria->id }}/2`, function(data){
                                $("#main").addClass("hidden");$("#info").removeClass("hidden").html("").append(data);
                                invocarVista(`/auth/{{strtolower($ViewName)}}/fase-final-final/{{ $TorneoFaseFinal->TorneoCategoria->torneo->id }}/{{ $TorneoFaseFinal->TorneoCategoria->id }}`, function(data){
                                    $("#partialViewFinal{{ $TorneoFaseFinal->TorneoCategoria->id }}").html("").append(data);
                                });
                            });
                        }
                    }else Toast.fire({icon: 'error', title: 'Algo salió mal, hubo un error al guardar.'});
                });
        });


        $("#mapaCampeonato{{ $TorneoFaseFinal->TorneoCategoria->id }}").on("click", ".btn-export-pdf-cup", function (){
            const $this = $(this);
            $("#textCategoria").text(', Categoría "'+$this.attr("data-category")+'"');
            $(".has-map-bg").removeClass("is-half-left").removeClass("is-half-right");
            $(".grid.grid-mapa-content").removeClass("is-half-left").removeClass("is-half-right");
            window.print();
        });

        $("#mapaCampeonato{{ $TorneoFaseFinal->TorneoCategoria->id }}").on("click", ".btn-download-cup", function (){
            window.open(`/auth/{{strtolower($ViewName)}}/export/mapa/json?type=full&torneo={{ $TorneoFaseFinal->TorneoCategoria->torneo->id  }}&categoria={{ $TorneoFaseFinal->TorneoCategoria->id }}`);
        });

        $("#mapaCampeonato{{ $TorneoFaseFinal->TorneoCategoria->id }}").on("click", ".btn-export-pdf-cup-left", function (){
            /*const $this = $(this);
            $("#textCategoria").text(', Categoría "'+$this.attr("data-category")+'"');
            $(".has-map-bg").removeClass("is-half-right").addClass("is-half-left");
            $(".grid.grid-mapa-content").removeClass("is-half-right").addClass("is-half-left");
            window.print();*/
            window.open(`/auth/{{strtolower($ViewName)}}/export/mapa/json?type=left&torneo={{ $TorneoFaseFinal->TorneoCategoria->torneo->id  }}&categoria={{ $TorneoFaseFinal->TorneoCategoria->id }}`);
        });


        $("#mapaCampeonato{{ $TorneoFaseFinal->TorneoCategoria->id }}").on("click", ".btn-export-pdf-cup-right", function (){
            /*const $this = $(this);
            $("#textCategoria").text(', Categoría "'+$this.attr("data-category")+'"');
            $(".has-map-bg").removeClass("is-half-left").addClass("is-half-right");
            $(".grid.grid-mapa-content").removeClass("is-half-left").addClass("is-half-right");
            window.print();*/
            window.open(`/auth/{{strtolower($ViewName)}}/export/mapa/json?type=right&torneo={{ $TorneoFaseFinal->TorneoCategoria->torneo->id  }}&categoria={{ $TorneoFaseFinal->TorneoCategoria->id }}`);
        });


        $("#mapaCampeonato{{ $TorneoFaseFinal->TorneoCategoria->id }}").on("click", ".btn-finish-keys-final", function (){
            const formData = new FormData();
            formData.append('_token', $("meta[name=csrf-token]").attr("content"));
            formData.append('torneo_id', {{ $TorneoFaseFinal->TorneoCategoria->torneo->id }});
            formData.append('torneo_categoria_id', {{ $TorneoFaseFinal->TorneoCategoria->id }});
            confirmAjax(`/auth/{{strtolower($ViewName)}}/fase-final/prepartido/finish`, formData, `POST`, `¿Está seguro de finalizar las llaves generadas ?`, null, function (data){
                if(data.Success){
                    invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $TorneoFaseFinal->TorneoCategoria->torneo->id }}/{{ $TorneoFaseFinal->TorneoCategoria->id }}/2`, function(data){
                        $("#main").addClass("hidden");$("#info").removeClass("hidden").html("").append(data);
                        invocarVista(`/auth/{{strtolower($ViewName)}}/fase-final-final/{{ $TorneoFaseFinal->TorneoCategoria->torneo->id }}/{{ $TorneoFaseFinal->TorneoCategoria->id }}`, function(data){
                            $("#partialViewFinal{{ $TorneoFaseFinal->TorneoCategoria->id }}").html("").append(data);
                        });
                    });
                }
            });
        });

        @if(!$landing)

            $("#mapaCampeonato{{ $TorneoFaseFinal->TorneoCategoria->id }}").on("click", "table.table-game", function (){
                const $this = $(this);
                const id = $this.attr("data-id");
                const position = $this.attr("data-position");
                const bracket =  $this.attr("data-bracket");
                @if($TorneoFaseFinal->TorneoCategoria->manual)
                    invocarModal(`/auth/{{strtolower($ViewName)}}/fase-final/prepartido/partialView/{{$TorneoFaseFinal->TorneoCategoria->torneo->id}}/{{$TorneoFaseFinal->TorneoCategoria->id}}/${id ? id : 0}/${position}/${bracket}`, function ($modal) {
                        if ($modal.attr("data-reload") === "true"){
                            refrescarMapa();
                        }
                    });
                @else
                    const formData = new FormData();
                    formData.append('_token', $("meta[name=csrf-token]").attr("content"));
                    formData.append('id', id);
                    formData.append('torneo_id', {{ $TorneoFaseFinal->TorneoCategoria->torneo->id }});
                    formData.append('torneo_categoria_id', {{ $TorneoFaseFinal->TorneoCategoria->id }});
                    actionAjax(`/auth/{{strtolower($ViewName)}}/fase-final/partido/validate/partialView`, formData, "POST", function(data) {
                        if(data.Success)
                        {
                            invocarModal(`/auth/{{strtolower($ViewName)}}/fase-final/partido/partialView/${id ? id : 0}/${position}`, function ($modal) {
                                if ($modal.attr("data-reload") === "true"){
                                    invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $TorneoFaseFinal->TorneoCategoria->torneo->id }}/{{ $TorneoFaseFinal->TorneoCategoria->id }}/2`, function(data){
                                        refrescarMapa()
                                    });
                                }
                            });
                        }else{
                            Toast.fire({icon: 'error', title: data.Message ? data.Message : 'El partido aún no se encuentra disponible porque falta 1 jugador.'});
                        }
                    });
                @endif
            });
        @endif

        $("#mapaCampeonato{{ $TorneoFaseFinal->TorneoCategoria->id }}").on("click", ".btn-delete-keys-final", function (){
            const formData = new FormData();
            formData.append('_token', $("meta[name=csrf-token]").attr("content"));
            formData.append('torneo_id', {{ $TorneoFaseFinal->TorneoCategoria->torneo->id }});
            formData.append('torneo_categoria_id', {{ $TorneoFaseFinal->TorneoCategoria->id }});
            confirmAjax(`/auth/{{strtolower($ViewName)}}/fase-final/delete`, formData, `POST`, `¿Está seguro de eliminar las llaves generadas ?`, null, function (data){
                if(data.Success){
                    invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $TorneoFaseFinal->TorneoCategoria->torneo->id }}/{{ $TorneoFaseFinal->TorneoCategoria->id }}/2`, function(data){
                        $("#main").addClass("hidden");$("#info").removeClass("hidden").html("").append(data);
                        invocarVista(`/auth/{{strtolower($ViewName)}}/fase-final-final/{{ $TorneoFaseFinal->TorneoCategoria->torneo->id }}/{{ $TorneoFaseFinal->TorneoCategoria->id }}`, function(data){
                            $("#partialViewFinal{{ $TorneoFaseFinal->TorneoCategoria->id }}").html("").append(data);
                        });
                    });
                }else Toast.fire({icon: 'error', title: 'Algo salió mal, hubo un error al guardar.'});
            });
        });

        $("#mapaCampeonato{{ $TorneoFaseFinal->TorneoCategoria->id }}").on("click", ".btn-export-pdf-fase-final", function(){
            const $this = $(this);
            window.open(`/auth/reporte/{{strtolower($ViewName)}}/fase-final/exportar/pdf/{{ $TorneoFaseFinal->TorneoCategoria->torneo->id }}/${$this.attr("data-id")}`, '_blank');
        });

        function refrescarMapa(){
            invocarVista(`/auth/{{strtolower($ViewName)}}/fase-final/mapa/partialView/{{ $TorneoFaseFinal->TorneoCategoria->torneo->id }}/{{ $TorneoFaseFinal->TorneoCategoria->id }}/{{ $landing }}`, function(data){
                $("#mapaCampeonato{{ $TorneoFaseFinal->TorneoCategoria->id }}").html(data);
            });
        }

    });
</script>
