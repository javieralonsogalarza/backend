@inject('App', 'App\Models\App')
@inject('Auth', '\Illuminate\Support\Facades\Auth')

<style type="text/css">
    .grid-mapa-content table{ {{ $TorneoFaseFinal->TorneoCategoria->multiple ? "height: 60px !important;" : "height: 50px !important;" }} }
    .grid-mapa-content table td{ line-height: 1 !important; {{ $TorneoFaseFinal->TorneoCategoria->multiple ? "height: 30px !important;font-size:11px !important;" : "height: 30px !important;font-size:11px !important;" }}  }
    @media print
    {
        body, * {
            -webkit-print-color-adjust: exact !important;   /* Chrome, Safari 6 – 15.3, Edge */
            color-adjust: exact !important;                 /* Firefox 48 – 96 */
            print-color-adjust: exact !important;           /* Firefox 97+, Safari 15.4+ */
            margin: 0 !important;
            padding: 0 !important;
        }

        html, body {
            height:100%;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden;
        }

        .grid-mapa-content{height: 100vh !important; align-items: center !important;justify-content: center}
        .grid-mapa-content table{ height: 50px !important;margin: 0 auto !important; }
        .grid-mapa-content table.table-striped td{ height: 50px !important;font-weight: 500;font-size:17px !important;padding: 0.5rem;}
        .grid-mapa-content p strong{ font-size: 20px !important;margin: 0 !important;}
        small{font-size: 20px !important; }

        .report-not-view{display: none;}
        body, .container-fluid, .card, .card-body, .mt-4, .mt-3, .mt-2, .mb-2,.mt-4, .p-4, .p-3, #main-content-wrapper{padding: 0 !important;margin: 0 !important;}

        .has-map-bg{ background-image: url('{{ $TorneoFaseFinal->TorneoCategoria->imagen }}');background-size: 200%;position: relative;background-repeat: no-repeat;  }

        .has-map-bg.is-half-left{ background-position: center left !important;}
        .grid-mapa-content.is-half-left{ background-image: none !important;position: relative; left: 92px;}
        @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 16)
            .grid-mapa-content.is-half-left{grid-template-columns: 80% 10% !important; justify-content: end;height: 100%; align-items: center !important;}
        @else
            .grid-mapa-content.is-half-left{grid-template-columns: 60% 10% !important; justify-content: end;height: 100%; align-items: center !important;}
        @endif
        .grid-mapa-content.is-half-left img{width: 100%; max-width: 250px;display: block;margin: 0 auto}
        .grid-mapa-content.is-half-left table.table-striped{width: 250px !important;margin: 0 auto; }
        .grid-mapa-content.is-half-left table.table-striped td{height: 50px !important;font-size:17px !important;font-weight: 500;padding: 0.5rem }
        .grid-mapa-content.is-half-left .way-b{ display: none !important; }

        .has-map-bg.is-half-right{ background-position: center right !important; }
        .grid-mapa-content.is-half-right{ background-image: none !important;position: relative; left: -95px; }
        @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 16)
             .grid-mapa-content.is-half-right{grid-template-columns: 10% 75% !important;justify-content: start;height: 100%;align-items: center !important;}
        @else
             .grid-mapa-content.is-half-right{grid-template-columns: 10% 60% !important;justify-content: start;height: 100%;align-items: center !important;}
        @endif
        .grid-mapa-content.is-half-right img{width: 100%; max-width: 250px;display: block;margin: 0 auto}
        .grid-mapa-content.is-half-right table.table-striped{width: 250px !important;margin: 0 auto; }
        .grid-mapa-content.is-half-right table.table-striped td{height: 50px !important;font-size:17px !important;font-weight: 500;padding: 0.5rem }
        .grid-mapa-content.is-half-right .way-a{ display: none !important;}
    }

    small {font-size: .875rem }
    .text-center.color-participantes{ background-color: #ffffff; }

    .color-rotulos{ color: {{ ($TorneoFaseFinal->TorneoCategoria->color_rotulos != null &&  $TorneoFaseFinal->TorneoCategoria->color_rotulos != "" ? $TorneoFaseFinal->TorneoCategoria->color_rotulos : "#000000") }} }
    .color-participantes{ color: {{ ($TorneoFaseFinal->TorneoCategoria->color_participantes != null &&  $TorneoFaseFinal->TorneoCategoria->color_participantes != "" ? $TorneoFaseFinal->TorneoCategoria->color_participantes : "#000000") }} }

    @page { margin: 0;padding: 0 }

</style>

@if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', $MaxFase)->whereNotNull('jugador_local_uno_id')->where('estado_id', $App::$ESTADO_PENDIENTE)) > 0)
    <div class="row mt-3">
        <ul class="w-100 d-flex align-content-center justify-content-end list-unstyled p-0">
            <li><button type="button" class="btn btn-primary btn-change-player-class">
                    <i class="fa fa-recycle" aria-hidden="true"></i> Cambiar Jugador Clasificado</button>
            </li>
        </ul>
    </div>
@endif

@if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')) > 0)
    <div class="d-flex align-items-center justify-content-between mb-2">
        <div><h5>Mapa del Campeonato</h5></div>
    </div>
        <?php $bloque1A = 1; $bloque2A = 1; $bloque3A = 1; $bloque4A = 1; $bloque1B = 1; $bloque2B = 1; $bloque3B = 1; $bloque4B = 1; ?>
    <div class="grid grid-mapa-content"
         style="display: grid;grid-template-columns: 40% 10% 40%;gap: 5%;align-items: center;background-image: url('{{ $TorneoFaseFinal->TorneoCategoria->imagen }}');background-size: cover;background-position: center;background-repeat: no-repeat;padding: 10px;">
        <!-- Lado A -->
        <div class="grid way-a" style="display: grid;justify-content: start;align-items: center;height: 100%;{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 4 ? "grid-template-columns:45% 45%;gap: 10%" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 8 ? "grid-template-columns:30% 30% 30%;gap: 3%" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 16 ? "grid-template-columns:24% 24% 24% 24%;gap: 1%" : "")) }}">
            <!-- Dieciseisavo de Final -->
            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 16)
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 mb-1 position-absolute w-100 color-rotulos" style="top: 0;"><strong>RONDA DE 32</strong></p>
                    <div style="display: grid;align-items: center; height: 100%;position: relative;top: 20px;">
                        @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 1) as $q)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="{{ ($bloque1A++ % 2 ) == 0 ? "2" : "1"  }}" data-bracket="{{ in_array($bloque1A, [2,3]) ? "upper" : "lower" }}" style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}" data-id="{{ $q->id }}">
                                    <tr><td class="text-center color-participantes">{{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-")) : ($q->buy_all ? "BYE" : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-"))) : ($q->buy ? "BYE" : "-") }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a href="/auth/torneo/partido/export/json?id={{ $q->id }}" target="_blank">{{ $q->resultado }}</a></small>
                            </div>
                            <div class="mt-6"></div>
                        @endforeach
                    </div>
                    <div style="display: grid;align-items: center; height: 100%;position: relative;top: 20px;">
                        @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 3) as $q)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="{{ ($bloque3A++ % 2 ) == 0 ? "2" : "1"  }}" data-bracket="{{ in_array($bloque3A, [2,3]) ? "upper" : "lower" }}" style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}" data-id="{{ $q->id }}">
                                    <tr><td class="text-center color-participantes">{{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-")) : ($q->buy_all ? "BYE" : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-"))) : ($q->buy ? "BYE" : "-") }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a href="/auth/torneo/partido/export/json?id={{ $q->id }}" target="_blank">{{ $q->resultado }}</a></small>
                            </div>
                            <div class="mt-6"></div>
                        @endforeach
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;">
                    <div style="height: 100%;display: grid;align-items: center;position: relative;">
                        <p class="text-center text-xs m-0 mb-1 position-absolute w-100 color-rotulos" style="top: 0"><strong>OCTAVOS DE FINAL</strong></p>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)) > 0)
                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first() != null)
                                <div class="text-center position-relative">
                                    <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="1" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->id }}">
                                        <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorLocalUno->nombre_completo) : "-") }}</td></tr>
                                        <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalUno->nombre_completo) : "-") }}</td></tr>
                                    </table>
                                    <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                        <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->id }}" target="_blank">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->resultado }}
                                        </a>
                                    </small>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center color-participantes">-</td></tr>
                                        <tr><td class="text-center color-participantes">-</td></tr>
                                    </table>
                                </div>
                            @endif
                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first() != null)
                                <div class="text-center position-relative">
                                    <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="2" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->id }}">
                                        <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorLocalUno->nombre_completo) : "-") }}</td></tr>
                                        <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalUno->nombre_completo) : "-") }}</td></tr>
                                    </table>
                                    <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                        <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->id }}" target="_blank">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->resultado }}
                                        </a>
                                    </small>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center color-participantes">-</td></tr>
                                        <tr><td class="text-center color-participantes">-</td></tr>
                                    </table>
                                </div>
                            @endif
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div style="height: 100%;display: grid;align-items: center;position: relative;">
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)) > 0)
                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first() != null)
                                <div class="text-center position-relative">
                                    <table class="table table-bordered table-striped mb-0 table-game" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer" data-position="1" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->id }}">
                                        <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorLocalUno->nombre_completo) : "-") }}</td></tr>
                                        <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                    </table>
                                    <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                        <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->id }}" target="_blank">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->resultado }}
                                        </a>
                                    </small>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center color-participantes">-</td></tr>
                                        <tr><td class="text-center color-participantes">-</td></tr>
                                    </table>
                                </div>
                            @endif
                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first() != null)
                                <div class="text-center position-relative">
                                    <table class="table table-bordered table-striped mb-0 table-game" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer" data-position="2" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->id }}">
                                        <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorLocalUno->nombre_completo) : "-") }}</td></tr>
                                        <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalUno->nombre_completo) : "-") }}</td></tr>
                                    </table>
                                    <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                        <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->id }}" target="_blank">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->resultado }}
                                        </a>
                                    </small>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center color-participantes">-</td></tr>
                                        <tr><td class="text-center color-participantes">-</td></tr>
                                    </table>
                                </div>
                            @endif
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;">
                    <div style="height: 100%;display: grid;align-items: center;position: relative;">
                        <p class="text-center text-xs m-0 mb-1 position-absolute w-100 color-rotulos" style="top: 0"><strong>CUARTOS DE FINAL</strong></p>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer" data-position="1" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->id }}">
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo) : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo) : "-") }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->id }}" target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div style="height: 100%;display: grid;align-items: center;position: relative;">
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer" data-position="2" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->id }}">
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo) : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo) : "-") }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->id }}" target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;position: relative;padding-top: 20px;">
                    <p class="text-center text-xs m-0 mb-1 position-absolute w-100 color-rotulos" style="top: 0"><strong>SEMIFINAL</strong></p>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer" data-position="1" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->id }}">
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo) : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo) : "-") }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->id }}" target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @elseif($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 8)
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 mb-1 position-absolute w-100 color-rotulos" style="top: 0"><strong>OCTAVOS DE FINAL</strong></p>
                    <div style="display: grid;align-items: center; height: 100%;position: relative;">
                        @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1) as $q)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="{{ ($bloque1A++ % 2 ) == 0 ? "2" : "1"  }}" style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}" data-id="{{ $q->id }}">
                                    <tr><td class="text-center color-participantes">{{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-")) : ($q->buy_all ? "BYE" : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-"))) : ($q->buy ? "BYE" : "-") }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a href="/auth/torneo/partido/export/json?id={{ $q->id }}" target="_blank">{{ $q->resultado }}</a></small>
                            </div>
                        @endforeach
                    </div>
                    <div style="display: grid;align-items: center; height: 100%;position: relative;">
                        @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3) as $q)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="{{ ($bloque3A++ % 2 ) == 0 ? "2" : "1"  }}" style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}" data-id="{{ $q->id }}">
                                    <tr><td class="text-center color-participantes">{{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-")) : ($q->buy_all ? "BYE" : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-"))) : ($q->buy ? "BYE" : "-") }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a href="/auth/torneo/partido/export/json?id={{ $q->id }}" target="_blank">{{ $q->resultado }}</a></small>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 position-absolute mb-1 w-100 color-rotulos" style="top: 0"><strong>CUARTOS DE FINAL</strong></p>
                    <div style="display: grid;align-items: center; height: 100%;position: relative;">
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer" data-position="1" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->id }}">
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo) : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo) : "-") }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->id }}" target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div style="display: grid;align-items: center; height: 100%;position: relative;">
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer" data-position="2" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->id }}">
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo) : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo) : "-") }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->id }}" target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 position-absolute mb-1 w-100 color-rotulos" style="top: 0"><strong>SEMIFINAL</strong></p>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="1" style="cursor: pointer" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->id }}">
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo) : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo) : "-") }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->id }}" target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @elseif($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 4)
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 position-absolute mb-1 w-100 color-rotulos" style="top: 0"><strong>CUARTOS DE FINAL</strong></p>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer" data-position="1" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->id  }}">
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->multiple ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno != null ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo : "-").' + '.($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalDos != null ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : "-") : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno != null ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo : "-")) : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->id }}" target="_blank">
                                    {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->resultado }}</small>
                                </a>
                            </div>
                            <div class="mt-6"></div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                            <div class="mt-6"></div>
                        @endif
                    </div>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer" data-position="2" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->id  }}">
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno != null ?  ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->multiple ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno != null ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo : "-").' + '.($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalDos != null ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalDos->nombre_completo : "-") : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno != null ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo : "-")) : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->id }}" target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 position-absolute mb-1 w-100 color-rotulos" style="top: 0"><strong>SEMIFINAL</strong></p>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="1" style="cursor: pointer" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->id }}">
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo) : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo) : "-") }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->id }}" target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @elseif($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 2)
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 position-absolute mb-1 w-100 color-rotulos" style="top: 0"><strong>SEMIFINAL</strong></p>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="1" style="cursor: pointer" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->id }}">
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo) : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo) : "-") }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->id }}" target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
        <!-- Final -->
        <div style="height: 100%;display: grid;align-items: center;position: relative;padding-top: 20px;">

            <div class="report-view hidden row position-absolute" style="top: 70px;left: -250px;width: 700px !important;">
                <div class="col-md-12 text-center">
                    <h3 class="color-rotulos" style="font-size: 30px !important;">Torneo {{ $TorneoFaseFinal->TorneoCategoria->torneo->nombre }}</h3>
                    <p style="font-size: 20px !important;margin-bottom: 0.5rem" class="color-rotulos"><b>Desde</b>: {{ \Carbon\Carbon::parse($TorneoFaseFinal->TorneoCategoria->torneo->fecha_inicio)->format('d M Y') }} - <b>Hasta</b>: {{ \Carbon\Carbon::parse($TorneoFaseFinal->TorneoCategoria->torneo->fecha_final)->format('d M Y') }}</p>
                    <p style="font-size: 20px !important;margin-bottom: 0.5rem" class="color-rotulos"><b>Formato</b>: {{ $TorneoFaseFinal->TorneoCategoria->torneo->formato != null ? $TorneoFaseFinal->TorneoCategoria->torneo->formato->nombre : "-" }}</p>
                </div>
            </div>

            @if(in_array($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase, [1,2,4,8]))
                <p class="text-center text-xs m-0 mt-1 position-absolute w-100 p-final color-rotulos" style="top: 0;margin-top: 0;"><strong>FINAL</strong></p>
            @else
                <p class="text-center text-xs m-0 mb-1 position-absolute w-100 color-rotulos" style="top: 0"><strong>FINAL</strong></p>
            @endif
            <div class="text-center position-relative d-block">
                <div style="display: grid;align-items: center; height: 100%;">
                    @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first() != null)
                        <p class="text-center text-sm m-0 mt-5"><strong>{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first()->multiple ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first()->jugadorGanadorUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first()->jugadorGanadorDos->nombre_completo) : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first()->jugadorGanadorUno->nombre_completo }}</strong></p>
                    @else
                        <p class="text-center m-0 mt-5"><strong></strong></p>
                    @endif
                    @if($comunidad != null && $comunidad->imagen_path != null && $comunidad->imagen_path != "")
                        <img src="{{ asset('/img/'.$comunidad->imagen_path) }}" class="img-logo" width="100%" style="margin: 0 auto" alt="AdminLogo">
                    @endif
                    <img src="{{ asset('/upload/image/trofeo.png') }}" class="img-cup" width="100%" style="margin: 0 auto" alt="Trofeo">
                    <div class="position-relative">
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)) > 0)
                            <table class="table table-bordered table-striped mb-0 table-game" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="1" style="cursor: pointer" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->id }}">
                                <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo) : "-") }}</td></tr>
                                <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo) : "-") }}</td></tr>
                            </table>
                            <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->id }}" target="_blank">
                                    {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->resultado }}
                                </a>
                            </small>
                        @else
                            <table class="table table-bordered table-striped mb-0">
                                <tr><td class="text-center color-participantes">-</td></tr>
                                <tr><td class="text-center color-participantes">-</td></tr>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <!-- Lado B -->
        <div class="grid way-b" style="display: grid;justify-content: end;align-items: center;height: 100%;{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 4 ? "grid-template-columns:45% 45%;gap: 10%" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 8 ? "grid-template-columns:30% 30% 30%;gap: 3%" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 16 ? "grid-template-columns:24% 24% 24% 24%;gap: 1%" : "")) }}">
            <!-- Dieciseisavo de Final -->
            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 16)
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 mb-1 position-absolute w-100 color-rotulos" style="top: 0"><strong>SEMIFINAL</strong></p>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer" data-position="2" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->id }}">
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo) : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo) : "-") }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->id }}" target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 mb-1 position-absolute w-100 color-rotulos" style="top: 0"><strong>CUARTOS DE FINAL</strong></p>
                    <div style="height: 100%;display: grid;align-items: center;">
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer" data-position="1" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->id }}">
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo) : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo) : "-") }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->id }}" target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div style="height: 100%;display: grid;align-items: center;">
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer" data-position="2" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->id }}">
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo) : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo) : "-") }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->id }}" target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 mb-1 position-absolute w-100 color-rotulos" style="top: 0"><strong>OCTAVOS DE FINAL</strong></p>
                    <div style="height: 100%;display: grid;align-items: center;">
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)) > 0)
                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first() != null)
                                <div class="text-center position-relative">
                                    <table class="table table-bordered table-striped mb-0 table-game" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer" data-position="1" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->id }}">
                                        <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorLocalUno->nombre_completo) : "-") }}</td></tr>
                                        <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalUno->nombre_completo) : "-") }}</td></tr>
                                    </table>
                                    <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                        <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->id }}" target="_blank">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->resultado }}
                                        </a>
                                    </small>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center color-participantes">-</td></tr>
                                        <tr><td class="text-center color-participantes">-</td></tr>
                                    </table>
                                </div>
                            @endif
                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first() != null)
                                <div class="text-center position-relative">
                                    <table class="table table-bordered table-striped mb-0 table-game" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer" data-position="2" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->id }}">
                                        <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorLocalUno->nombre_completo) : "-") }}</td></tr>
                                        <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalUno->nombre_completo) : "-") }}</td></tr>
                                    </table>
                                    <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                        <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->id }}" target="_blank">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->resultado }}
                                        </a>
                                    </small>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center color-participantes">-</td></tr>
                                        <tr><td class="text-center color-participantes">-</td></tr>
                                    </table>
                                </div>
                            @endif
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div style="height: 100%;display: grid;align-items: center;">
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)) > 0)
                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first() != null)
                                <div class="text-center position-relative">
                                    <table class="table table-bordered table-striped mb-0 table-game" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer" data-position="1" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->id }}">
                                        <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorLocalUno->nombre_completo) : "-") }}</td></tr>
                                        <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalUno->nombre_completo) : "-") }}</td></tr>
                                    </table>
                                    <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                        <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->id }}" target="_blank">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->resultado }}
                                        </a>
                                    </small>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center color-participantes">-</td></tr>
                                        <tr><td class="text-center color-participantes">-</td></tr>
                                    </table>
                                </div>
                            @endif
                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first() != null)
                                <div class="text-center position-relative">
                                    <table class="table table-bordered table-striped mb-0 table-game" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer" data-position="2" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->id }}">
                                        <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorLocalUno->nombre_completo) : "-") }}</td></tr>
                                        <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalUno->nombre_completo) : "-") }}</td></tr>
                                    </table>
                                    <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                        <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->id }}" target="_blank">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->resultado }}
                                        </a>
                                    </small>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center color-participantes">-</td></tr>
                                        <tr><td class="text-center color-participantes">-</td></tr>
                                    </table>
                                </div>
                            @endif
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 position-absolute mb-1 w-100 color-rotulos" style="top: 0"><strong>RONDA DE 32</strong></p>
                    <div style="display: grid;align-items: center; height: 100%;position: relative;top: 20px;">
                        @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 2) as $q)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}" data-position="{{ ($bloque2A++ % 2 ) == 0 ? "2" : "1"  }}" data-bracket="{{ in_array($bloque2A, [2,3]) ? "upper" : "lower" }}" data-id="{{ $q->id }}">
                                    <tr><td class="text-center color-participantes">{{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-")) : ($q->buy_all ? "BYE" : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-"))) : ($q->buy ? "BYE" : "-") }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $q->id }}" target="_blank">
                                        {{ $q->resultado }}
                                    </a>
                                </small>
                            </div>
                            <div class="mt-6"></div>
                        @endforeach
                    </div>
                    <div style="display: grid;align-items: center; height: 100%;position: relative;top: 20px;">
                        @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 4) as $q)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}" data-position="{{ ($bloque4A++ % 2 ) == 0 ? "2" : "1"  }}" data-bracket="{{ in_array($bloque4A, [2,3]) ? "upper" : "lower" }}" data-id="{{ $q->id }}">
                                    <tr><td class="text-center color-participantes">{{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-")) : ($q->buy_all ? "BYE" : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-"))) : ($q->buy ? "BYE" : "-") }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $q->id }}" target="_blank">{{ $q->resultado }}</a>
                                </small>
                            </div>
                            <div class="mt-6"></div>
                        @endforeach
                    </div>
                </div>
            @elseif($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 8)
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 position-absolute mb-1 w-100 color-rotulos" style="top: 0"><strong>SEMIFINAL</strong></p>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="2" style="cursor: pointer" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->id }}">
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo) : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo) : "-") }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->resultado }}" target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 position-absolute mb-1 w-100 color-rotulos" style="top: 0"><strong>CUARTOS DE FINAL</strong></p>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer" data-position="1" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->id }}">
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo) : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($q->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->id }}" target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                            <div class="mt-6"></div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                            <div class="mt-6"></div>
                        @endif
                    </div>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer" data-position="2" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->id }}">
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo) : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno != null ? ($q->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->id }}" target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs position-absolute m-0 mb-1 w-100 color-rotulos" style="top: 0"><strong>OCTAVOS DE FINAL</strong></p>
                    <div style="height: 100%;display: grid;align-items: center">
                        @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2) as $q)
                            <div  class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}" data-position="{{ ($bloque2A++ % 2 ) == 0 ? "2" : "1"  }}" data-id="{{ $q->id }}">
                                    <tr><td class="text-center color-participantes">{{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-")) : ($q->buy_all ? "BYE" : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-"))) : ($q->buy ? "BYE" : "-") }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $q->id }}" target="_blank">{{ $q->resultado }}</a>
                                </small>
                            </div>
                        @endforeach
                    </div>
                    <div style="height: 100%;display: grid;align-items: center">
                        @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4) as $q)
                            <div  class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}" data-position="{{ ($bloque4A++ % 2 ) == 0 ? "2" : "1"  }}" data-id="{{ $q->id }}">
                                    <tr><td class="text-center color-participantes">{{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-")) : ($q->buy_all ? "BYE" : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-"))) : ($q->buy ? "BYE" : "-") }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $q->id }}" target="_blank">{{ $q->resultado }}</a>
                                </small>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 4)
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 position-absolute mb-1 w-100 color-rotulos" style="top: 0"><strong>SEMIFINAL</strong></p>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="2" style="cursor: pointer" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->id }}">
                                    <tr><td class="text-center color-participantes">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo) : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo) : "-") }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->id }}" target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 position-absolute mb-1 w-100 color-rotulos" style="top: 0"><strong>CUARTOS DE FINAL</strong></p>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="1" style="cursor: pointer" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->id }}">
                                    <tr><td class="text-center color-participantes">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo) : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo) : "-") }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->id }}" target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                            <div class="mt-6"></div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                            <div class="mt-6"></div>
                        @endif
                    </div>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0  table-game" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="2" style="cursor: pointer" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->id }}">
                                    <tr><td class="text-center color-participantes">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo) : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->id }}" target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @elseif($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 2)
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 position-absolute mb-1 w-100 color-rotulos" style="top: 0"><strong>SEMIFINAL</strong></p>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game" data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="2" style="cursor: pointer" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->id }}">
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo) : "-") }}</td></tr>
                                    <tr><td class="text-center color-participantes">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo) : "-") }}</td></tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->id }}" target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                            <div class="mt-6"></div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                    <tr><td class="text-center color-participantes">-</td></tr>
                                </table>
                            </div>
                            <div class="mt-6"></div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if(!$landing)
        @if($TorneoFaseFinal->TorneoCategoria->manual)
            <div class="row mt-3">
                <div class="col-md-12 content-buttons-final text-right">
                    <ul class="w-100 d-flex align-content-center justify-content-end list-unstyled p-0">
                        <!--<li class="mr-1"><button data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" type="button" class="btn btnSubirFondo btn-success"><i class="fa fa-edit"></i> Editar Fondo y Textos</button></li>-->
                        <!--<li class="mr-1"><button type="button" class="btn btn-danger btn-export-pdf-cup-left" data-category="{{ ($TorneoFaseFinal->TorneoCategoria->categoriaSimple->nombre)."".($TorneoFaseFinal->TorneoCategoria->multiple ? " (Doble) " : "") }}" data-random="{{ $TorneoFaseFinal->TorneoCategoria->aleatorio }}" data-id="{{ $TorneoFaseFinal->TorneoCategoria->id  }}"><i class="fa fa-file-pdf"></i> Exportar A</button></li>
                        <li class="mr-1"><button type="button" class="btn btn-danger btn-export-pdf-cup-right" data-category="{{ ($TorneoFaseFinal->TorneoCategoria->categoriaSimple->nombre)."".($TorneoFaseFinal->TorneoCategoria->multiple ? " (Doble) " : "") }}" data-random="{{ $TorneoFaseFinal->TorneoCategoria->aleatorio }}" data-id="{{ $TorneoFaseFinal->TorneoCategoria->id  }}"><i class="fa fa-file-pdf"></i> Exportar B</button></li>-->
                        <li class="mr-1"><button type="button" class="btn btn-danger btn-export-pdf-cup" data-category="{{ ($TorneoFaseFinal->TorneoCategoria->categoriaSimple->nombre)."".($TorneoFaseFinal->TorneoCategoria->multiple ? " (Doble) " : "") }}" data-random="{{ $TorneoFaseFinal->TorneoCategoria->aleatorio }}" data-id="{{ $TorneoFaseFinal->TorneoCategoria->id  }}"><i class="fa fa-file-pdf"></i> Exportar PDF</button></li>
                        <li class="mr-1"><button type="button" class="btn btn-success btn-download-cup" data-category="{{ ($TorneoFaseFinal->TorneoCategoria->categoriaSimple->nombre)."".($TorneoFaseFinal->TorneoCategoria->multiple ? " (Doble) " : "") }}" data-random="{{ $TorneoFaseFinal->TorneoCategoria->aleatorio }}" data-id="{{ $TorneoFaseFinal->TorneoCategoria->id  }}"><i class="fa fa-image"></i> Descargar Llaves</button></li>
                        <li class="mr-1"><button type="button" class="btn btn-primary btn-finish-keys-final" data-random="{{ $TorneoFaseFinal->TorneoCategoria->aleatorio }}" data-id="{{ $TorneoFaseFinal->TorneoCategoria->id  }}"><i class="fa fa-save"></i> Finalizar llaves</button></li>
                    </ul>
                </div>
            </div>
        @else
            <div class="row mt-3">
                <div class="col-md-12 content-buttons-final text-right">
                    <ul class="w-100 d-flex align-content-center justify-content-end list-unstyled p-0">
                        <!--<li class="mr-1"><button data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}" type="button" class="btn btnSubirFondo btn-success"><i class="fa fa-edit"></i> Editar Fondo y Textos</button></li>-->
                        <!--<li class="mr-1"><button type="button" class="btn btn-danger btn-export-pdf-cup-left" data-category="{{ ($TorneoFaseFinal->TorneoCategoria->categoriaSimple->nombre)."".($TorneoFaseFinal->TorneoCategoria->multiple ? " (Doble) " : "") }}" data-random="{{ $TorneoFaseFinal->TorneoCategoria->aleatorio }}" data-id="{{ $TorneoFaseFinal->TorneoCategoria->id  }}"><i class="fa fa-file-pdf"></i> Exportar A</button></li>
                        <li class="mr-1"><button type="button" class="btn btn-danger btn-export-pdf-cup-right" data-category="{{ ($TorneoFaseFinal->TorneoCategoria->categoriaSimple->nombre)."".($TorneoFaseFinal->TorneoCategoria->multiple ? " (Doble) " : "") }}" data-random="{{ $TorneoFaseFinal->TorneoCategoria->aleatorio }}" data-id="{{ $TorneoFaseFinal->TorneoCategoria->id  }}"><i class="fa fa-file-pdf"></i> Exportar B</button></li>-->
                        <li class="mr-1"><button type="button" class="btn btn-danger btn-export-pdf-cup" data-category="{{ ($TorneoFaseFinal->TorneoCategoria->categoriaSimple->nombre)."".($TorneoFaseFinal->TorneoCategoria->multiple ? " (Doble) " : "") }}" data-random="{{ $TorneoFaseFinal->TorneoCategoria->aleatorio }}" data-id="{{ $TorneoFaseFinal->TorneoCategoria->id  }}"><i class="fa fa-file-pdf"></i> Exportar PDF</button></li>
                        <li class="mr-1"><button type="button" class="btn btn-success btn-download-cup" data-category="{{ ($TorneoFaseFinal->TorneoCategoria->categoriaSimple->nombre)."".($TorneoFaseFinal->TorneoCategoria->multiple ? " (Doble) " : "") }}" data-random="{{ $TorneoFaseFinal->TorneoCategoria->aleatorio }}" data-id="{{ $TorneoFaseFinal->TorneoCategoria->id  }}"><i class="fa fa-image"></i> Descargar Llaves</button></li>
                    </ul>
                </div>
            </div>
        @endif
    @endif
@endif


<script type="text/javascript">
    $(function (){
        const $btnChangePlayerClass = $(".btn-change-player-class");
        $btnChangePlayerClass.on("click", function (){
            invocarModal(`/auth/{{strtolower($ViewName)}}/fase-final/players/changes/{{$TorneoFaseFinal->TorneoCategoria->torneo->id}}/{{$TorneoFaseFinal->TorneoCategoria->id}}`,
                function ($modal){
                    if ($modal.attr("data-reload") === "true") refrescarMapa();
                });
        });
    });
</script>
