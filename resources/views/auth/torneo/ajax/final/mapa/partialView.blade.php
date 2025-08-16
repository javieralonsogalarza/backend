@inject('App', 'App\Models\App')
@inject('Auth', '\Illuminate\Support\Facades\Auth')

<style type="text/css">
    .grid-mapa-content table {
        {{ $TorneoFaseFinal->TorneoCategoria->multiple ? "height: 60px !important;" : "height: 50px !important;" }}
    }

    .grid-mapa-content table td {
        line-height: 1 !important;
        {{ $TorneoFaseFinal->TorneoCategoria->multiple ? "height: 30px !important;font-size:11px !important;" : "height: 30px !important;font-size:11px !important;" }}
    }

    @media print {

        body,
        * {
            -webkit-print-color-adjust: exact !important;
            /* Chrome, Safari 6 – 15.3, Edge */
            color-adjust: exact !important;
            /* Firefox 48 – 96 */
            print-color-adjust: exact !important;
            /* Firefox 97+, Safari 15.4+ */
            margin: 0 !important;
            padding: 0 !important;
        }

        html,
        body {
            height: 100%;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden;
        }

        .grid-mapa-content {
            height: 100vh !important;
            align-items: center !important;
            justify-content: center
        }

        .grid-mapa-content table {
            height: 50px !important;
            margin: 0 auto !important;
        }

        .grid-mapa-content table.table-striped td {
            height: 50px !important;
            font-weight: 500;
            font-size: 17px !important;
            padding: 0.5rem;
        }

        .grid-mapa-content p strong {
            font-size: 20px !important;
            margin: 0 !important;
        }

        small {
            font-size: 20px !important;
        }

        .report-not-view {
            display: none;
        }

        body,
        .container-fluid,
        .card,
        .card-body,
        .mt-4,
        .mt-3,
        .mt-2,
        .mb-2,
        .mt-4,
        .p-4,
        .p-3,
        #main-content-wrapper {
            padding: 0 !important;
            margin: 0 !important;
        }

        .has-map-bg {
            background-image: url('{{ $TorneoFaseFinal->TorneoCategoria->imagen }}');
            background-size: 200%;
            position: relative;
            background-repeat: no-repeat;
        }

        .has-map-bg.is-half-left {
            background-position: center left !important;
        }

        .grid-mapa-content.is-half-left {
            background-image: none !important;
            position: relative;
            left: 92px;
        }

        @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 16)
            .grid-mapa-content.is-half-left {
                grid-template-columns: 80% 10% !important;
                justify-content: end;
                height: 100%;
                align-items: center !important;
            }

        @else .grid-mapa-content.is-half-left {
                grid-template-columns: 60% 10% !important;
                justify-content: end;
                height: 100%;
                align-items: center !important;
            }

        @endif .grid-mapa-content.is-half-left img {
            width: 100%;
            max-width: 250px;
            display: block;
            margin: 0 auto
        }

        .grid-mapa-content.is-half-left table.table-striped {
            width: 250px !important;
            margin: 0 auto;
        }

        .grid-mapa-content.is-half-left table.table-striped td {
            height: 50px !important;
            font-size: 17px !important;
            font-weight: 500;
            padding: 0.5rem
        }

        .grid-mapa-content.is-half-left .way-b {
            display: none !important;
        }

        .has-map-bg.is-half-right {
            background-position: center right !important;
        }

        .grid-mapa-content.is-half-right {
            background-image: none !important;
            position: relative;
            left: -95px;
        }

        @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 16)
            .grid-mapa-content.is-half-right {
                grid-template-columns: 10% 75% !important;
                justify-content: start;
                height: 100%;
                align-items: center !important;
            }

        @else .grid-mapa-content.is-half-right {
                grid-template-columns: 10% 60% !important;
                justify-content: start;
                height: 100%;
                align-items: center !important;
            }

        @endif .grid-mapa-content.is-half-right img {
            width: 100%;
            max-width: 250px;
            display: block;
            margin: 0 auto
        }

        .grid-mapa-content.is-half-right table.table-striped {
            width: 250px !important;
            margin: 0 auto;
        }

        .grid-mapa-content.is-half-right table.table-striped td {
            height: 50px !important;
            font-size: 17px !important;
            font-weight: 500;
            padding: 0.5rem
        }

        .grid-mapa-content.is-half-right .way-a {
            display: none !important;
        }
    }

    small {
        font-size: .875rem
    }

    .text-center.color-participantes {
        background-color: #ffffff;
    }

    .color-rotulos {
        color:
            {{ ($TorneoFaseFinal->TorneoCategoria->color_rotulos != null && $TorneoFaseFinal->TorneoCategoria->color_rotulos != "" ? $TorneoFaseFinal->TorneoCategoria->color_rotulos : "#000000") }}
    }

    .color-participantes {
        color:
            {{ ($TorneoFaseFinal->TorneoCategoria->color_participantes != null && $TorneoFaseFinal->TorneoCategoria->color_participantes != "" ? $TorneoFaseFinal->TorneoCategoria->color_participantes : "#000000") }}
    }

    @page {
        margin: 0;
        padding: 0
    }
</style>

@if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', $MaxFase)->whereNotNull('jugador_local_uno_id')->where('estado_id', $App::$ESTADO_PENDIENTE)) > 0 && $TorneoFaseFinal->TorneoCategoria->torneo->formato_id != 8)
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
        <div>
            <h5>Cuadro Principal del Campeonato</h5>
        </div>
    </div>
    <?php    $bloque1A = 1;
        $bloque2A = 1;
        $bloque3A = 1;
        $bloque4A = 1;
        $bloque5A = 1;
        $bloque6A = 1;
        $bloque7A = 1;
        $bloque8A = 1;

            ?>
    <div class="grid grid-mapa-content"
        style="display: grid;grid-template-columns: 40% 10% 40%;gap: 5%;align-items: center;background-image: url('{{ $TorneoFaseFinal->TorneoCategoria->imagen }}');background-size: cover;background-position: center;background-repeat: no-repeat;padding: 10px;">
        <!-- Lado A -->
        <div class="grid way-a" style="display: grid;justify-content: start;align-items: center;height: 100%;{{ 
                $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 4 ?
            "grid-template-columns:45% 45%;gap: 10%" :
            ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 8 ?
                "grid-template-columns:30% 30% 30%;gap: 3%" :
                ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 16 ?
                    "grid-template-columns:24% 24% 24% 24%;gap: 1%" :
                    ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 32 ?
                        "grid-template-columns:20% 20% 20% 20% 20%;gap: 0.5%" :
                        ""
                    )))
            }}">
            <!-- Dieciseisavo de Final -->
            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 32)
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 mb-1 position-absolute w-100 color-rotulos" style="top: 0;"><strong>RONDA
                            DE 64</strong></p>
                    <div style="display: grid;align-items: center; height: 100%;position: relative;top: 20px;">
                        @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 32)->where('bloque', 1) as $q)
                            <div class="text-center position-relative">
                                <table
                                    class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                    data-position="{{ ($bloque1A++ % 2) == 0 ? "2" : "1"  }}"
                                    data-bracket="{{ in_array($bloque1A, [2, 3]) ? "upper" : "lower" }}"
                                    style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                    data-id="{{ $q->id }}">
                                    <tr>
                                        <td class="text-center color-participantes numero-1">
                                            {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes numero-2">
                                            {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a
                                        href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                        target="_blank">{{ $q->resultado }}</a></small>
                            </div>
                            <div class="mt-6"></div>
                        @endforeach
                    </div>
                    <div style="display: grid;align-items: center; height: 100%;position: relative;top: 20px;">
                        @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 32)->where('bloque', 3) as $q)
                            <div class="text-center position-relative">
                                <table
                                    class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                    data-position="{{ ($bloque3A++ % 2) == 0 ? "2" : "1"  }}"
                                    data-bracket="{{ in_array($bloque3A, [2, 3]) ? "upper" : "lower" }}"
                                    style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                    data-id="{{ $q->id }}">
                                    <tr>
                                        <td class="text-center color-participantes numero-3">
                                            {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes numero-4">
                                            {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a
                                        href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                        target="_blank">{{ $q->resultado }}</a></small>
                            </div>
                            <div class="mt-6"></div>
                        @endforeach
                    </div>
                    <div style="display: grid;align-items: center; height: 100%;position: relative;top: 20px;">
                        @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 32)->where('bloque', 5) as $q)
                            <div class="text-center position-relative">
                                <table
                                    class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                    data-position="{{ ($bloque5A++ % 2) == 0 ? "2" : "1"  }}"
                                    data-bracket="{{ in_array($bloque5A, [2, 3]) ? "upper" : "lower" }}"
                                    style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                    data-id="{{ $q->id }}">
                                    <tr>
                                        <td class="text-center color-participantes numero-5">
                                            {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes numero-6">
                                            {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a
                                        href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                        target="_blank">{{ $q->resultado }}</a></small>
                            </div>
                            <div class="mt-6"></div>
                        @endforeach
                    </div>
                    <div style="display: grid;align-items: center; height: 100%;position: relative;top: 20px;">
                        @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 32)->where('bloque', 7) as $q)
                            <div class="text-center position-relative">
                                <table
                                    class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                    data-position="{{ ($bloque7A++ % 2) == 0 ? "2" : "1"  }}"
                                    data-bracket="{{ in_array($bloque7A, [2, 3]) ? "upper" : "lower" }}"
                                    style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                    data-id="{{ $q->id }}">
                                    <tr>
                                        <td class="text-center color-participantes numero-7">
                                            {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes numero-8">
                                            {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a
                                        href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                        target="_blank">{{ $q->resultado }}</a></small>
                            </div>
                            <div class="mt-6"></div>
                        @endforeach
                    </div>
                </div>

                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 mb-1 position-absolute w-100 color-rotulos" style="top: 0;"><strong>RONDA
                            DE 32</strong></p>
                    @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 1)) > 0)
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 1)->where('position', 1)->where('bracket', 'upper')) > 0)
                            <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">

                                @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 1)->where('position', 1)->where('bracket', 'upper') as $q)
                                    <div class="text-center position-relative">
                                        <table
                                            class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                            data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                            data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                            data-position="{{ ($bloque1A++ % 2) == 0 ? "2" : "1"  }}"
                                            data-bracket="{{ in_array($bloque1A, [2, 3]) ? "upper" : "lower" }}"
                                            style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                            data-id="{{ $q->id }}">
                                            <tr>
                                                <td class="text-center color-participantes numero-9">
                                                    {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-center color-participantes numero-10">
                                                    {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                        <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a
                                                href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                                target="_blank">{{ $q->resultado }}</a></small>
                                    </div>


                                @endforeach
                            </div>
                        @else
                        <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes numero-11">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes numero-12">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                        @endif

                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 1)->where('position', 2)->where('bracket', 'upper')) > 0)
                            <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">

                                @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 1)->where('position', 2)->where('bracket', 'upper') as $q)
                                    <div class="text-center position-relative">
                                        <table
                                            class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                            data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                            data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                            data-position="{{ ($bloque1A++ % 2) == 0 ? "2" : "1"  }}"
                                            data-bracket="{{ in_array($bloque1A, [2, 3]) ? "upper" : "lower" }}"
                                            style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                            data-id="{{ $q->id }}">
                                            <tr>
                                                <td class="text-center color-participantes numero-13">
                                                    {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-center color-participantes numero-14">
                                                    {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                        <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a
                                                href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                                target="_blank">{{ $q->resultado }}</a></small>
                                    </div>

                                @endforeach
                            </div>
                        @else
                        <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes numero-15">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes numero-16">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                        @endif

                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 1)->where('position', 1)->where('bracket', 'lower')) > 0)
                            <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">

                                @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 1)->where('position', 1)->where('bracket', 'lower') as $q)
                                    <div class="text-center position-relative">
                                        <table
                                            class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                            data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                            data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                            data-position="{{ ($bloque1A++ % 2) == 0 ? "2" : "1"  }}"
                                            data-bracket="{{ in_array($bloque1A, [2, 3]) ? "upper" : "lower" }}"
                                            style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                            data-id="{{ $q->id }}">
                                            <tr>
                                                <td class="text-center color-participantes numero-17">
                                                    {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-center color-participantes numero-18">
                                                    {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                        <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a
                                                href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                                target="_blank">{{ $q->resultado }}</a></small>
                                    </div>
                                @endforeach
                            </div>
                        @else
                        <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes numero-19">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes numero-20">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                        @endif

                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 1)->where('position', 2)->where('bracket', 'lower')) > 0)
                            <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
    
                            @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 1)->where('position', 2)->where('bracket', 'lower') as $q)
                                    <div class="text-center position-relative">
                                        <table
                                            class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                            data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                            data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                            data-position="{{ ($bloque1A++ % 2) == 0 ? "2" : "1"  }}"
                                            data-bracket="{{ in_array($bloque1A, [2, 3]) ? "upper" : "lower" }}"
                                            style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                            data-id="{{ $q->id }}">
                                            <tr>
                                                <td class="text-center color-participantes numero-21">
                                                    {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-center color-participantes numero-22">
                                                    {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                        <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a
                                                href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                                target="_blank">{{ $q->resultado }}</a></small>
                                    </div>

                                @endforeach
                                </div>
                            @else
                            <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes b">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes c">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                            @endif
                    
                    @else
                    <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes d">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes e">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                        <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes f">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes g">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                        <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes h">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes i">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                        <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes j">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes k">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                    @endif
                    @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 3)) > 0)
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 3)->where('position', 1)->where('bracket', 'upper')) > 0)
                            <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">

                                @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 3)->where('position', 1)->where('bracket', 'upper') as $q)
                                    <div class="text-center position-relative">
                                        <table
                                            class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                            data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                            data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                            data-position="{{ ($bloque3A++ % 2) == 0 ? "2" : "1"  }}"
                                            data-bracket="{{ in_array($bloque3A, [2, 3]) ? "upper" : "lower" }}"
                                            style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                            data-id="{{ $q->id }}">
                                            <tr>
                                                <td class="text-center color-participantes numero-23">
                                                    {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-center color-participantes numero-24">
                                                    {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                        <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a
                                                href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                                target="_blank">{{ $q->resultado }}</a></small>
                                    </div>
                                @endforeach
                            </div>
                        @else
                        <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes numero-25">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes numero-26">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                        @endif

                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 3)->where('position', 2)->where('bracket', 'upper')) > 0)
                            <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">

                                @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 3)->where('position', 2)->where('bracket', 'upper') as $q)
                                    <div class="text-center position-relative">
                                        <table
                                            class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                            data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                            data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                            data-position="{{ ($bloque3A++ % 2) == 0 ? "2" : "1"  }}"
                                            data-bracket="{{ in_array($bloque3A, [2, 3]) ? "upper" : "lower" }}"
                                            style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                            data-id="{{ $q->id }}">
                                            <tr>
                                                <td class="text-center color-participantes numero-27">
                                                    {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-center color-participantes numero-28">
                                                    {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                        <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a
                                                href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                                target="_blank">{{ $q->resultado }}</a></small>
                                    </div>
                                @endforeach
                            </div>
                        @else
                        <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes numero-29">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes numero-30">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                        @endif

                        <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 3)->where('position', 1)->where('bracket', 'lower')) > 0)
                                @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 3)->where('position', 1)->where('bracket', 'lower') as $q)
                                    <div class="text-center position-relative">
                                        <table
                                            class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                            data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                            data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                            data-position="{{ ($bloque3A++ % 2) == 0 ? "2" : "1"  }}"
                                            data-bracket="{{ in_array($bloque3A, [2, 3]) ? "upper" : "lower" }}"
                                            style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                            data-id="{{ $q->id }}">
                                            <tr>
                                                <td class="text-center color-participantes numero-31">
                                                    {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-center color-participantes numero-32">
                                                    {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                        <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a
                                                href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                                target="_blank">{{ $q->resultado }}</a></small>
                                    </div>
                                @endforeach
                            @else
                            <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes numero-33">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes numero-34">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>

                            @endif
                        </div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 3)->where('position', 2)->where('bracket', 'lower')) > 0)
                            <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">

                                @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 3)->where('position', 2)->where('bracket', 'lower') as $q)
                                    <div class="text-center position-relative">
                                        <table
                                            class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                            data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                            data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                            data-position="{{ ($bloque3A++ % 2) == 0 ? "2" : "1"  }}"
                                            data-bracket="{{ in_array($bloque3A, [2, 3]) ? "upper" : "lower" }}"
                                            style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                            data-id="{{ $q->id }}">
                                            <tr>
                                                <td class="text-center color-participantes 11">
                                                    {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-center color-participantes 12">
                                                    {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                        <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a
                                                href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                                target="_blank">{{ $q->resultado }}</a></small>
                                    </div>
                                @endforeach
                            </div>
                        @else

                        <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes 13">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes 14">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>

                        @endif

                    @else
                    <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes 15">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes 16">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                            <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes 17">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes 18">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                            <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes 19">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwqw">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                            <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwqww">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwdqww">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                    @endif

                </div>
                <div style="height: 100%;display: grid;align-items: center;">
                    <div style="height: 100%;display: grid;align-items: center;position: relative;">
                        <p class="text-center text-xs m-0 mb-1 position-absolute w-100 color-rotulos" style="top: 0">
                            <strong>OCTAVOS DE FINAL</strong>
                        </p>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)) > 0)
                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first() != null)
                                <div class="text-center position-relative">
                                    <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer"
                                        data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                        data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="1"
                                        data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->id }}">
                                        <tr>
                                            <td class="text-center color-participantes qwdsqww">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}

                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugador_local_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes qwsdsqww">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugador_rival_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif

                                            </td>
                                        </tr>
                                    </table>
                                    <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                        <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->id }}"
                                            target="_blank">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->resultado }}
                                        </a>
                                    </small>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr>
                                            <td class="text-center color-participantes qwscdsqww">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes qwsecdsqww">-</td>
                                        </tr>
                                    </table>
                                </div>
                            @endif
                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first() != null)
                                <div class="text-center position-relative">
                                    <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer"
                                        data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                        data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="2"
                                        data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->id }}">
                                        <tr>
                                            <td class="text-center color-participantes qwscecdsqww">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugador_local_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif

                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes qwsccecdsqww">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}

                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugador_rival_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif

                                            </td>
                                        </tr>
                                    </table>
                                    <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                        <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->id }}"
                                            target="_blank">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->resultado }}
                                        </a>
                                    </small>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr>
                                            <td class="text-center color-participantes qwsdccecdsqww">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes qwsddccecdsqww">-</td>
                                        </tr>
                                    </table>
                                </div>
                            @endif
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwvsddccecdsqww">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwrvsddccecdsqww">-</td>
                                    </tr>
                                </table>
                            </div>
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwrvbsddccecdsqww">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwrvcbsddccecdsqww">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div style="height: 100%;display: grid;align-items: center;position: relative;">
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)) > 0)
                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first() != null)
                                <div class="text-center position-relative">
                                    <table class="table table-bordered table-striped mb-0 table-game"
                                        data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                        data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                        data-position="1"
                                        data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->id }}">
                                        <tr>
                                            <td class="text-center color-participantes qwrvvcbsddccecdsqww">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugador_local_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes qwxrvvcbsddccecdsqww">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-")  }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugador_rival_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                    <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                        <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->id }}"
                                            target="_blank">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->resultado }}
                                        </a>
                                    </small>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr>
                                            <td class="text-center color-participantes qwxarvvcbsddccecdsqww">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes qwxbarvvcbsddccecdsqww">-</td>
                                        </tr>
                                    </table>
                                </div>
                            @endif
                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first() != null)
                                <div class="text-center position-relative">
                                    <table class="table table-bordered table-striped mb-0 table-game"
                                        data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                        data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                        data-position="2"
                                        data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->id }}">
                                        <tr>
                                            <td class="text-center color-participantes qwcxbarvvcbsddccecdsqww">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugador_local_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes qwwcxbarvvcbsddccecdsqww">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugador_rival_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                    <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                        <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->id }}"
                                            target="_blank">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->resultado }}
                                        </a>
                                    </small>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr>
                                            <td class="text-center color-participantes qwwccxbarvvcbsddccecdsqww">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes qwwnccxbarvvcbsddccecdsqww">-</td>
                                        </tr>
                                    </table>
                                </div>
                            @endif
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwwdnccxbarvvcbsddccecdsqww">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwewdnccxbarvvcbsddccecdsqww">-</td>
                                    </tr>
                                </table>
                            </div>
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qweqwdnccxbarvvcbsddccecdsqww">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwecqwdnccxbarvvcbsddccecdsqww">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;">
                    <div style="height: 100%;display: grid;align-items: center;position: relative;">
                        <p class="text-center text-xs m-0 mb-1 position-absolute w-100 color-rotulos" style="top: 0">
                            <strong>CUARTOS DE FINAL</strong>
                        </p>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                    data-position="1"
                                    data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->id }}">
                                    <tr>
                                        <td class="text-center color-participantes qwvecqwdnccxbarvvcbsddccecdsqww">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwdvecqwdnccxbarvvcbsddccecdsqww">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->id }}"
                                        target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwdwvecqwdnccxbarvvcbsddccecdsqww">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwddwvecqwdnccxbarvvcbsddccecdsqww">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div style="height: 100%;display: grid;align-items: center;position: relative;">
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                    data-position="2"
                                    data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->id }}">
                                    <tr>
                                        <td class="text-center color-participantes qwdddwvecqwdnccxbarvvcbsddccecdsqww">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwddddwvecqwdnccxbarvvcbsddccecdsqww">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->id }}"
                                        target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwdddddwvecqwdnccxbarvvcbsddccecdsqww">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwddddddwvecqwdnccxbarvvcbsddccecdsqww">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;position: relative;padding-top: 20px;">
                    <p class="text-center text-xs m-0 mb-1 position-absolute w-100 color-rotulos" style="top: 0">
                        <strong>SEMIFINAL</strong>
                    </p>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                    data-position="1"
                                    data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->id }}">
                                    <tr>
                                        <td class="text-center color-participantes qwdddddddwvecqwdnccxbarvvcbsddccecdsqww">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwddddddddwvecqwdnccxbarvvcbsddccecdsqww">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->id }}"
                                        target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwddcddddddwvecqwdnccxbarvvcbsddccecdsqww">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwcddcddddddwvecqwdnccxbarvvcbsddccecdsqww">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>



            @elseif($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 16)
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 mb-1 position-absolute w-100 color-rotulos" style="top: 0;"><strong>RONDA
                            DE 32</strong></p>
                    <div style="display: grid;align-items: center; height: 100%;position: relative;top: 20px;">
                        @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 1) as $q)
                            <div class="text-center position-relative">
                                <table
                                    class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                    data-position="{{ ($bloque1A++ % 2) == 0 ? "2" : "1"  }}"
                                    data-bracket="{{ in_array($bloque1A, [2, 3]) ? "upper" : "lower" }}"
                                    style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                    data-id="{{ $q->id }}">
                                    <tr>
                                        <td class="text-center color-participantes qwcdvdcddddddwvecqwdnccxbarvvcbsddccecdsqww">
                                            {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwcadvdcddddddwvecqwdnccxbarvvcbsddccecdsqww">
                                            {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a
                                        href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                        target="_blank">{{ $q->resultado }}</a></small>
                            </div>
                            <div class="mt-6"></div>
                        @endforeach
                    </div>
                    <div style="display: grid;align-items: center; height: 100%;position: relative;top: 20px;">
                        @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 3) as $q)
                            <div class="text-center position-relative">
                                <table
                                    class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                    data-position="{{ ($bloque3A++ % 2) == 0 ? "2" : "1"  }}"
                                    data-bracket="{{ in_array($bloque3A, [2, 3]) ? "upper" : "lower" }}"
                                    style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                    data-id="{{ $q->id }}">
                                    <tr>
                                        <td class="text-center color-participantes qwcaAdvdcddddddwvecqwdnccxbarvvcbsddccecdsqww">
                                            {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwcaDAdvdcddddddwvecqwdnccxbarvvcbsddccecdsqww">
                                            {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a
                                        href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                        target="_blank">{{ $q->resultado }}</a></small>
                            </div>
                            <div class="mt-6"></div>
                        @endforeach
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;">
                    <div style="height: 100%;display: grid;align-items: center;position: relative;">
                        <p class="text-center text-xs m-0 mb-1 position-absolute w-100 color-rotulos" style="top: 0">
                            <strong>OCTAVOS DE FINAL</strong>
                        </p>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)) > 0)
                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first() != null)
                                <div class="text-center position-relative">
                                    <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer"
                                        data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                        data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="1"
                                        data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->id }}">
                                        <tr>
                                            <td class="text-center color-participantes qwcaEDAdvdcddddddwvecqwdnccxbarvvcbsddccecdsqww">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}

                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugador_local_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes qwcaCEDAdvdcddddddwvecqwdnccxbarvvcbsddccecdsqww">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugador_rival_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif

                                            </td>
                                        </tr>
                                    </table>
                                    <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                        <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->id }}"
                                            target="_blank">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->resultado }}
                                        </a>
                                    </small>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr>
                                            <td class="text-center color-participantes qwcaCCEDAdvdcddddddwvecqwdnccxbarvvcbsddccecdsqww">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes qwcCaCCEDAdvdcddddddwvecqwdnccxbarvvcbsddccecdsqww">-</td>
                                        </tr>
                                    </table>
                                </div>
                            @endif
                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first() != null)
                                <div class="text-center position-relative">
                                    <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer"
                                        data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                        data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="2"
                                        data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->id }}">
                                        <tr>
                                            <td class="text-center color-participantes qwcCCaCCEDAdvdcddddddwvecqwdnccxbarvvcbsddccecdsqww">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugador_local_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif

                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes qwcCC2aCCEDAdvdcddddddwvecqwdnccxbarvvcbsddccecdsqww">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}

                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugador_rival_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif

                                            </td>
                                        </tr>
                                    </table>
                                    <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                        <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->id }}"
                                            target="_blank">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->resultado }}
                                        </a>
                                    </small>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr>
                                            <td class="text-center color-participantes qwcCCD2aCCEDAdvdcddddddwvecqwdnccxbarvvcbsddccecdsqww">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes qwcCCCD2aCCEDAdvdcddddddwvecqwdnccxbarvvcbsddccecdsqww">-</td>
                                        </tr>
                                    </table>
                                </div>
                            @endif
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwcCDCCD2aCCEDAdvdcddddddwvecqwdnccxbarvvcbsddccecdsqww">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwcCDCCCD2aCCEDAdvdcddddddwvecqwdnccxbarvvcbsddccecdsqww">-</td>
                                    </tr>
                                </table>
                            </div>
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwcCSDCCCD2aCCEDAdvdcddddddwvecqwdnccxbarvvcbsddccecdsqww">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwcSCSDCCCD2aCCEDAdvdcddddddwvecqwdnccxbarvvcbsddccecdsqww">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div style="height: 100%;display: grid;align-items: center;position: relative;">
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)) > 0)
                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first() != null)
                                <div class="text-center position-relative">
                                    <table class="table table-bordered table-striped mb-0 table-game"
                                        data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                        data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                        data-position="1"
                                        data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->id }}">
                                        <tr>
                                            <td class="text-center color-participantes qwcSFCSDCCCD2aCCEDAdvdcddddddwvecqwdnccxbarvvcbsddccecdsqww">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugador_local_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes qwcSDFCSDCCCD2aCCEDAdvdcddddddwvecqwdnccxbarvvcbsddccecdsqww">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-")  }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugador_rival_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                    <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                        <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->id }}"
                                            target="_blank">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->resultado }}
                                        </a>
                                    </small>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr>
                                            <td class="text-center color-participantes qwcSDSFCSDCCCD2aCCEDAdvdcddddddwvecqwdnccxbarvvcbsddccecdsqww">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes qwcSDSDFCSDCCCD2aCCEDAdvdcddddddwvecqwdnccxbarvvcbsddccecdsqww">-</td>
                                        </tr>
                                    </table>
                                </div>
                            @endif
                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first() != null)
                                <div class="text-center position-relative">
                                    <table class="table table-bordered table-striped mb-0 table-game"
                                        data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                        data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                        data-position="2"
                                        data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->id }}">
                                        <tr>
                                            <td class="text-center color-participantes qwcSDDSDFCSDCCCD2aCCEDAdvdcddddddwvecqwdnccxbarvvcbsddccecdsqww">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugador_local_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes qwcSDDDSDFCSDCCCD2aCCEDAdvdcddddddwvecqwdnccxbarvvcbsddccecdsqww">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugador_rival_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                    <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                        <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->id }}"
                                            target="_blank">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->resultado }}
                                        </a>
                                    </small>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr>
                                            <td class="text-center color-participantes qwcSDDDDSDFCSDCCCD2aCCEDAdvdcddddddwvecqwdnccxbarvvcbsddccecdsqww">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes qwcSDDDDDSDFCSDCCCD2aCCEDAdvdcddddddwvecqwdnccxbarvvcbsddccecdsqww">-</td>
                                        </tr>
                                    </table>
                                </div>
                            @endif
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwcSDDDDDDSDFCSDCCCD2aCCEDAdvdcddddddwvecqwdnccxbarvvcbsddccecdsqww">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwcSDSDDDDDSDFCSDCCCD2aCCEDAdvdcddddddwvecqwdnccxbarvvcbsddccecdsqww">-</td>
                                    </tr>
                                </table>
                            </div>
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwcSDDSDDDDDSDFCSDCCCD2aCCEDAdvdcddddddwvecqwdnccxbarvvcbsddccecdsqww">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes Qcdsqww">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;">
                    <div style="height: 100%;display: grid;align-items: center;position: relative;">
                        <p class="text-center text-xs m-0 mb-1 position-absolute w-100 color-rotulos" style="top: 0">
                            <strong>CUARTOS DE FINAL</strong>
                        </p>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                    data-position="1"
                                    data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->id }}">
                                    <tr>
                                        <td class="text-center color-participantes QWcdsqww">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes QWEcdsqww">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->id }}"
                                        target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes QWERcdsqww">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes QWERTcdsqww">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div style="height: 100%;display: grid;align-items: center;position: relative;">
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                    data-position="2"
                                    data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->id }}">
                                    <tr>
                                        <td class="text-center color-participantes QWERTYcdsqww">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes QWERTYUcdsqww">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->id }}"
                                        target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes QWERTIYUcdsqww">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes QWERTIOYUcdsqww">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;position: relative;padding-top: 20px;">
                    <p class="text-center text-xs m-0 mb-1 position-absolute w-100 color-rotulos" style="top: 0">
                        <strong>SEMIFINAL</strong>
                    </p>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                    data-position="1"
                                    data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->id }}">
                                    <tr>
                                        <td class="text-center color-participantes QWERTPIOYUcdsqww">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes QWEQRTPIOYUcdsqww">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->id }}"
                                        target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes QWEQRTSPIOYUcdsqww">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes QWEQRTASPIOYUcdsqww">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @elseif($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 8)
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 mb-1 position-absolute w-100 color-rotulos" style="top: 0"><strong>OCTAVOS
                            DE FINAL</strong></p>
                    <div style="display: grid;align-items: center; height: 100%;position: relative;">
                        @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1) as $q)
                            <div class="text-center position-relative">
                                <table
                                    class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                    data-position="{{ ($bloque1A++ % 2) == 0 ? "2" : "1"  }}"
                                    style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                    data-id="{{ $q->id }}">
                                    <tr>
                                        <td class="text-center color-participantes QWEQRDTASPIOYUcdsqww">
                                            {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes QWEQRDQTASPIOYUcdsqww">
                                            {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a
                                        href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                        target="_blank">{{ $q->resultado }}</a></small>
                            </div>
                        @endforeach
                    </div>
                    <div style="display: grid;align-items: center; height: 100%;position: relative;">
                        @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3) as $q)
                            <div class="text-center position-relative">
                                <table
                                    class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                    data-position="{{ ($bloque3A++ % 2) == 0 ? "2" : "1"  }}"
                                    style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                    data-id="{{ $q->id }}">
                                    <tr>
                                        <td class="text-center color-participantes QWEQWRDQTASPIOYUcdsqww">
                                            {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes QWEQWWRDQTASPIOYUcdsqww">
                                            {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a
                                        href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                        target="_blank">{{ $q->resultado }}</a></small>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 position-absolute mb-1 w-100 color-rotulos" style="top: 0"><strong>CUARTOS
                            DE FINAL</strong></p>
                    <div style="display: grid;align-items: center; height: 100%;position: relative;">
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                    data-position="1"
                                    data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->id }}">
                                    <tr>
                                        <td class="text-center color-participantes QWEQQWWRDQTASPIOYUcdsqww">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes QWEQQFWWRDQTASPIOYUcdsqww">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->id }}"
                                        target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes QWEQcQFWWRDQTASPIOYUcdsqww">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes QWEsQcQFWWRDQTASPIOYUcdsqww">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div style="display: grid;align-items: center; height: 100%;position: relative;">
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                    data-position="2"
                                    data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->id }}">
                                    <tr>
                                        <td class="text-center color-participantes QWEsQwcQFWWRDQTASPIOYUcdsqww">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes QWEsdQwcQFWWRDQTASPIOYUcdsqww">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->id }}"
                                        target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes QWEsdsQwcQFWWRDQTASPIOYUcdsqww">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes QWEsdssQwcQFWWRDQTASPIOYUcdsqww">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 position-absolute mb-1 w-100 color-rotulos" style="top: 0">
                        <strong>SEMIFINAL</strong>
                    </p>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="1"
                                    style="cursor: pointer"
                                    data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->id }}">
                                    <tr>
                                        <td class="text-center color-participantes QWEsdsssQwcQFWWRDQTASPIOYUcdsqww">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes QWEsdssssQwcQFWWRDQTASPIOYUcdsqww">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->id }}"
                                        target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes QWEsdsssssQwcQFWWRDQTASPIOYUcdsqww">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes QWEsdssssssQwcQFWWRDQTASPIOYUcdsqww">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @elseif($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 4)
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 position-absolute mb-1 w-100 color-rotulos" style="top: 0"><strong>CUARTOS
                            DE FINAL</strong></p>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                    data-position="1"
                                    data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->id  }}">
                                    <tr>
                                        <td class="text-center color-participantes QWEsdsssssssQwcQFWWRDQTASPIOYUcdsqww">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->multiple ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno != null ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalDos != null ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : "-") : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno != null ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo_temporal : "-")) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes QWEsdssssssssQwcQFWWRDQTASPIOYUcdsqww">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-")  }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->id }}"
                                        target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->resultado }}</small>
                                </a>
                            </div>
                            <div class="mt-6"></div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes QWEsdsssssssssQwcQFWWRDQTASPIOYUcdsqww">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes QWEssdsssssssssQwcQFWWRDQTASPIOYUcdsqww">-</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="mt-6"></div>
                        @endif
                    </div>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                    data-position="2"
                                    data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->id  }}">
                                    <tr>
                                        <td class="text-center color-participantes QWEsssdsssssssssQwcQFWWRDQTASPIOYUcdsqww">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->multiple ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno != null ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalDos != null ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalDos->nombre_completo : "-") : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno != null ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo_temporal : "-")) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes QWEssssdsssssssssQwcQFWWRDQTASPIOYUcdsqww">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo_temporal . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalDos->nombre_completo_temporal : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo_temporal) : "-")  }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->id }}"
                                        target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes QWEsssssdsssssssssQwcQFWWRDQTASPIOYUcdsqww">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes QWEscssssdsssssssssQwcQFWWRDQTASPIOYUcdsqww">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 position-absolute mb-1 w-100 color-rotulos" style="top: 0">
                        <strong>SEMIFINAL</strong>
                    </p>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="1"
                                    style="cursor: pointer"
                                    data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->id }}">
                                    <tr>
                                        <td class="text-center color-participantes QWEsccssssdsssssssssQwcQFWWRDQTASPIOYUcdsqww">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes QWEscccssssdsssssssssQwcQFWWRDQTASPIOYUcdsqww">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->id }}"
                                        target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes QWEsccccssssdsssssssssQwcQFWWRDQTASPIOYUcdsqww">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes QWEscccccssssdsssssssssQwcQFWWRDQTASPIOYUcdsqww">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @elseif($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 2)
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 position-absolute mb-1 w-100 color-rotulos" style="top: 0">
                        <strong>SEMIFINAL</strong>
                    </p>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="1"
                                    style="cursor: pointer"
                                    data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->id }}">
                                    <tr>
                                        <td class="text-center color-participantes QWEsccscccssssdsssssssssQwcQFWWRDQTASPIOYUcdsqww">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes QWEscccscccssssdsssssssssQwcQFWWRDQTASPIOYUcdsqww">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->id }}"
                                        target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes QWEsvzcccscccssssdsssssssssQwcQFWWRDQTASPIOYUcdsqww">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes 21">-</td>
                                    </tr>
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
                    <h3 class="color-rotulos" style="font-size: 30px !important;">Torneo
                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->nombre }}
                    </h3>
                    <p style="font-size: 20px !important;margin-bottom: 0.5rem" class="color-rotulos"><b>Desde</b>:
                        {{ \Carbon\Carbon::parse($TorneoFaseFinal->TorneoCategoria->torneo->fecha_inicio)->format('d M Y') }}
                        - <b>Hasta</b>:
                        {{ \Carbon\Carbon::parse($TorneoFaseFinal->TorneoCategoria->torneo->fecha_final)->format('d M Y') }}
                    </p>
                    <p style="font-size: 20px !important;margin-bottom: 0.5rem" class="color-rotulos"><b>Formato</b>:
                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->formato != null ? $TorneoFaseFinal->TorneoCategoria->torneo->formato->nombre : "-" }}
                    </p>
                </div>
            </div>

            @if(in_array($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase, [1, 2, 4, 8]))
                <p class="text-center text-xs m-0 mt-1 position-absolute w-100 p-final color-rotulos"
                    style="top: 0;margin-top: 0;"><strong>FINAL</strong></p>
            @else
                <p class="text-center text-xs m-0 mb-1 position-absolute w-100 color-rotulos" style="top: 0">
                    <strong>FINAL</strong>
                </p>
            @endif
            <div class="text-center position-relative d-block">
                <div style="display: grid;align-items: center; height: 100%;">
                    @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first() != null)
                        <p class="text-center text-sm m-0 mt-5">
                            <strong>{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first()->multiple ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first()->jugadorGanadorUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first()->jugadorGanadorDos->nombre_completo_temporal) : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first()->jugadorGanadorUno->nombre_completo_temporal }}</strong>
                        </p>
                    @else
                        <p class="text-center m-0 mt-5"><strong></strong></p>
                    @endif
                    @if($comunidad != null && $comunidad->imagen_path != null && $comunidad->imagen_path != "")
                        <img src="{{ asset('/img/' . $comunidad->imagen_path) }}" class="img-logo" width="100%"
                            style="margin: 0 auto" alt="AdminLogo">
                    @endif
                    <img src="{{ asset('/upload/image/trofeo.png') }}" class="img-cup" width="100%" style="margin: 0 auto"
                        alt="Trofeo">
                    <div class="position-relative">
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)) > 0)
                            <table class="table table-bordered table-striped mb-0 table-game"
                                data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="1"
                                style="cursor: pointer"
                                data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->id }}">
                                <tr>
                                    <td class="text-center color-participantes a">
                                        {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                        @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugador_local_uno_id != null)
                                            <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center color-participantes asd">
                                        {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                        @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugador_rival_uno_id != null)
                                            <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                            <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->id }}"
                                    target="_blank">
                                    {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->resultado }}
                                </a>
                            </small>
                        @else
                            <table class="table table-bordered table-striped mb-0">
                                <tr>
                                    <td class="text-center color-participantes ccc">-</td>
                                </tr>
                                <tr>
                                    <td class="text-center color-participantes ccsc">-</td>
                                </tr>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <!-- Lado B -->
        <div class="grid way-b" style="display: grid;justify-content: end;align-items: center;height: 100%;{{ 
            $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 4 ?
            "grid-template-columns:45% 45%;gap: 10%" :
            ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 8 ?
                "grid-template-columns:30% 30% 30%;gap: 3%" :
                ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 16 ?
                    "grid-template-columns:24% 24% 24% 24%;gap: 1%" :
                    ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 32 ?
                        "grid-template-columns:20% 20% 20% 20% 20%;gap: 0.5%" :
                        ""
                    )))
        }}">
            <!-- Dieciseisavo de Final -->
            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 32)
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 mb-1 position-absolute w-100 color-rotulos" style="top: 0">
                        <strong>SEMIFINAL</strong>
                    </p>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                    data-position="2"
                                    data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->id }}">
                                    <tr>
                                        <td class="text-center color-participantes cccsc">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes csccsc">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->id }}"
                                        target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qcsccsc">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qcssccsc">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 mb-1 position-absolute w-100 color-rotulos" style="top: 0"><strong>CUARTOS
                            DE FINAL</strong></p>
                    <div style="height: 100%;display: grid;align-items: center;">
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                    data-position="1"
                                    data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->id }}">
                                    <tr>
                                        <td class="text-center color-participantes qccssccsc">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qcscssccsc">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->id }}"
                                        target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qacscssccsc">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qascscssccsc">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div style="height: 100%;display: grid;align-items: center;">
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                    data-position="2"
                                    data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->id }}">
                                    <tr>
                                        <td class="text-center color-participantes qsascscssccsc">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qsaascscssccsc">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->id }}"
                                        target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qssaascscssccsc">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qassaascscssccsc">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 mb-1 position-absolute w-100 color-rotulos" style="top: 0"><strong>OCTAVOS
                            DE FINAL</strong></p>
                    <div style="height: 100%;display: grid;align-items: center;">
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)) > 0)
                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first() != null)
                                <div class="text-center position-relative">
                                    <table class="table table-bordered table-striped mb-0 table-game"
                                        data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                        data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                        data-position="1"
                                        data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->id }}">
                                        <tr>
                                            <td class="text-center color-participantes qsassaascscssccsc">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugador_local_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes qsaassaascscssccsc">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugador_rival_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                    <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                        <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->id }}"
                                            target="_blank">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->resultado }}
                                        </a>
                                    </small>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr>
                                            <td class="text-center color-participantes qssaassaascscssccsc">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes qsscaassaascscssccsc">-</td>
                                        </tr>
                                    </table>
                                </div>
                            @endif
                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first() != null)
                                <div class="text-center position-relative">
                                    <table class="table table-bordered table-striped mb-0 table-game"
                                        data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                        data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                        data-position="2"
                                        data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->id }}">
                                        <tr>
                                            <td class="text-center color-participantes qssscaassaascscssccsc">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugador_local_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes qscsscaassaascscssccsc">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugador_rival_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                    <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                        <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->id }}"
                                            target="_blank">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->resultado }}
                                        </a>
                                    </small>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr>
                                            <td class="text-center color-participantes qsscsscaassaascscssccsc">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes qcsscsscaassaascscssccsc">-</td>
                                        </tr>
                                    </table>
                                </div>
                            @endif
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qcwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qcqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                </table>
                            </div>
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qcsqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qcsqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div style="height: 100%;display: grid;align-items: center;">
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)) > 0)
                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first() != null)
                                <div class="text-center position-relative">
                                    <table class="table table-bordered table-striped mb-0 table-game"
                                        data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                        data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                        data-position="1"
                                        data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->id }}">
                                        <tr>
                                            <td class="text-center color-participantes qcsdqqwsscsscaassaascscssccsc">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugador_local_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes qcsdcqqwsscsscaassaascscssccsc">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugador_rival_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                    <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                        <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->id }}"
                                            target="_blank">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->resultado }}
                                        </a>
                                    </small>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr>
                                            <td class="text-center color-participantes qcsdvcqqwsscsscaassaascscssccsc">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes qcsdzvcqqwsscsscaassaascscssccsc">-</td>
                                        </tr>
                                    </table>
                                </div>
                            @endif
                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first() != null)
                                <div class="text-center position-relative">
                                    <table class="table table-bordered table-striped mb-0 table-game"
                                        data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                        data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                        data-position="2"
                                        data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->id }}">
                                        <tr>
                                            <td class="text-center color-participantes qcsdszvcqqwsscsscaassaascscssccsc">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugador_local_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes qqcsdszvcqqwsscsscaassaascscssccsc">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugador_rival_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                    <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                        <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->id }}"
                                            target="_blank">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->resultado }}
                                        </a>
                                    </small>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr>
                                            <td class="text-center color-participantes qqwcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes qwqwcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                        </tr>
                                    </table>
                                </div>
                            @endif
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwqswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwqwswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                </table>
                            </div>
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwqqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 mb-1 position-absolute w-100 color-rotulos" style="top: 0;"><strong>RONDA
                            DE 32</strong></p>
                    @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 2)) > 0)
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 2)->where('position', 1)->where('bracket', 'upper')) > 0)
                            <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">

                                @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 2)->where('position', 1)->where('bracket', 'upper') as $q)
                                    <div class="text-center position-relative">
                                        <table
                                            class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                            data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                            data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                            data-position="{{ ($bloque2A++ % 2) == 0 ? "2" : "1"  }}"
                                            data-bracket="{{ in_array($bloque2A, [2, 3]) ? "upper" : "lower" }}"
                                            style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                            data-id="{{ $q->id }}">
                                            <tr>
                                                <td class="text-center color-participantes qwqcqswswcsdszvcqqwsscsscaassaascscssccsc">
                                                    {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-center color-participantes qwzqcqswswcsdszvcqqwsscsscaassaascscssccsc">
                                                    {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                        <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a
                                                href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                                target="_blank">{{ $q->resultado }}</a></small>
                                    </div>


                                @endforeach
                            </div>
                        @else
                        <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwzwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwszwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                        @endif

                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 2)->where('position', 2)->where('bracket', 'upper')) > 0)
                            <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">

                                @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 2)->where('position', 2)->where('bracket', 'upper') as $q)
                                    <div class="text-center position-relative">
                                        <table
                                            class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                            data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                            data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                            data-position="{{ ($bloque2A++ % 2) == 0 ? "2" : "1"  }}"
                                            data-bracket="{{ in_array($bloque2A, [2, 3]) ? "upper" : "lower" }}"
                                            style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                            data-id="{{ $q->id }}">
                                            <tr>
                                                <td class="text-center color-participantes qwsazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">
                                                    {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-center color-participantes qwsfrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">
                                                    {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                        <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a
                                                href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                                target="_blank">{{ $q->resultado }}</a></small>
                                    </div>

                                @endforeach
                            </div>
                        @else
                        <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwsfrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwsfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                        @endif

                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 2)->where('position', 1)->where('bracket', 'lower')) > 0)
                            <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">

                                @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 2)->where('position', 1)->where('bracket', 'lower') as $q)
                                    <div class="text-center position-relative">
                                        <table
                                            class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                            data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                            data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                            data-position="{{ ($bloque2A++ % 2) == 0 ? "2" : "1"  }}"
                                            data-bracket="{{ in_array( $bloque2A, [2, 3]) ? "upper" : "lower" }}"
                                            style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                            data-id="{{ $q->id }}">
                                            <tr>
                                                <td class="text-center color-participantes qwsrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">
                                                    {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-center color-participantes qwsrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">
                                                    {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                        <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a
                                                href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                                target="_blank">{{ $q->resultado }}</a></small>
                                    </div>
                                @endforeach
                            </div>
                        @else
                        <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwsrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwsrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                        @endif

                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 2)->where('position', 2)->where('bracket', 'lower')) > 0)
                            <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
    
                            @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 2)->where('position', 2)->where('bracket', 'lower') as $q)
                                    <div class="text-center position-relative">
                                        <table
                                            class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                            data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                            data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                            data-position="{{ ($bloque2A++ % 2) == 0 ? "2" : "1"  }}"
                                            data-bracket="{{ in_array($bloque2A, [2, 3]) ? "upper" : "lower" }}"
                                            style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                            data-id="{{ $q->id }}">
                                            <tr>
                                                <td class="text-center color-participantes qwsrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">
                                                    {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-center color-participantes qwsfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">
                                                    {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                        <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a
                                                href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                                target="_blank">{{ $q->resultado }}</a></small>
                                    </div>

                                @endforeach
                                </div>
                            @else
                            <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwsfrrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwsfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                            @endif
                    
                    @else
                    <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwsdfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwsrdfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                        <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwsrddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwsrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                        <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwdssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                        <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwsdssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                    @endif
                    @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 4)) > 0)
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 4)->where('position', 1)->where('bracket', 'upper')) > 0)
                            <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">

                                @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 4)->where('position', 1)->where('bracket', 'upper') as $q)
                                    <div class="text-center position-relative">
                                        <table
                                            class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                            data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                            data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                            data-position="{{ ($bloque4A++ % 2) == 0 ? "2" : "1"  }}"
                                            data-bracket="{{ in_array($bloque4A, [2, 3]) ? "upper" : "lower" }}"
                                            style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                            data-id="{{ $q->id }}">
                                            <tr>
                                                <td class="text-center color-participantes qwssddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">
                                                    {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-center color-participantes qwsdsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">
                                                    {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                        <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a
                                                href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                                target="_blank">{{ $q->resultado }}</a></small>
                                    </div>
                                @endforeach
                            </div>
                        @else
                        <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwsddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwsvddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                        @endif

                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 4)->where('position', 2)->where('bracket', 'upper')) > 0)
                            <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">

                                @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 4)->where('position', 2)->where('bracket', 'upper') as $q)
                                    <div class="text-center position-relative">
                                        <table
                                            class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                            data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                            data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                            data-position="{{ ($bloque4A++ % 2) == 0 ? "2" : "1"  }}"
                                            data-bracket="{{ in_array($bloque4A, [2, 3]) ? "upper" : "lower" }}"
                                            style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                            data-id="{{ $q->id }}">
                                            <tr>
                                                <td class="text-center color-participantes qwsvxddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">
                                                    {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-center color-participantes qwsvaxddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">
                                                    {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                        <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a
                                                href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                                target="_blank">{{ $q->resultado }}</a></small>
                                    </div>
                                @endforeach
                            </div>
                        @else
                        <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwsvaexddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwsvasexddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                        @endif

                        <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 4)->where('position', 1)->where('bracket', 'lower')) > 0)
                                @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 4)->where('position', 1)->where('bracket', 'lower') as $q)
                                    <div class="text-center position-relative">
                                        <table
                                            class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                            data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                            data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                            data-position="{{ ($bloque4A++ % 2) == 0 ? "2" : "1"  }}"
                                            data-bracket="{{ in_array($bloque4A, [2, 3]) ? "upper" : "lower" }}"
                                            style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                            data-id="{{ $q->id }}">
                                            <tr>
                                                <td class="text-center color-participantes qwsvaasexddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">
                                                    {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-center color-participantes qwsvasasexddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">
                                                    {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                        <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a
                                                href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                                target="_blank">{{ $q->resultado }}</a></small>
                                    </div>
                                @endforeach
                            @else
                            <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwsavasasexddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwsaavasasexddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>

                            @endif
                        </div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 4)->where('position', 2)->where('bracket', 'lower')) > 0)
                            <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">

                                @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 4)->where('position', 2)->where('bracket', 'lower') as $q)
                                    <div class="text-center position-relative">
                                        <table
                                            class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                            data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                            data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                            data-position="{{ ($bloque4A++ % 2) == 0 ? "2" : "1"  }}"
                                            data-bracket="{{ in_array($bloque4A, [2, 3]) ? "upper" : "lower" }}"
                                            style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                            data-id="{{ $q->id }}">
                                            <tr>
                                                <td class="text-center color-participantes qwsaaavasasexddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">
                                                    {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-center color-participantes qwsaaaavasasexddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">
                                                    {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                                    @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                        <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                        <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0"><a
                                                href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                                target="_blank">{{ $q->resultado }}</a></small>
                                    </div>
                                @endforeach
                            </div>
                        @else

                        <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwsaaaaavasasexddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwsaaaaavasasexddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>

                        @endif

                    @else
                    <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwsaaaaaavasasexddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwsaaaaaaavasasexddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                            <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwsaaaaaaaavasasexddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwsaaaaaaaaavasasexddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                            <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwsaaaaaaaaaavasasexddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwsaaaaaaaaaaavasasexddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                            <div style="display: grid;align-items: center; height: 100%;position: relative;top: 10px;">
                        <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes qwsaaaaaaaaaaaavasasexddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwsaawaaaaaaaaaavasasexddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">-</td>
                                    </tr>
                                </table>
                        </div>
                            </div>
                    @endif

                </div>

                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 position-absolute mb-1 w-100 color-rotulos" style="top: 0"><strong>RONDA
                            DE 64</strong></p>
                    <div style="display: grid;align-items: center; height: 100%;position: relative;top: 20px;">
                        @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 32)->where('bloque', 2) as $q)
                            <div class="text-center position-relative">
                                <table
                                    class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                    style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                    data-position="{{ ($bloque2A++ % 2) == 0 ? "1" : "2"  }}"
                                    data-bracket="{{ in_array($bloque2A, [2, 3]) ? "upper" : "lower" }}" data-id="{{ $q->id }}">
                                    <tr>
                                        <td class="text-center color-participantes qwsaaswaaaaaaaaaavasasexddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">
                                            {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                        @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                        @endif
                                            </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qwsasaswaaaaaaaaaavasasexddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">
                                            {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
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
                        @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 32)->where('bloque', 4) as $q)
                            <div class="text-center position-relative">
                                <table
                                    class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                    style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                    data-position="{{ ($bloque4A++ % 2) == 0 ? "2" : "1"  }}"
                                    data-bracket="{{ in_array($bloque4A, [2, 3]) ? "upper" : "lower" }}" data-id="{{ $q->id }}">
                                    <tr>
                                        <td class="text-center color-participantes qswsasaswaaaaaaaaaavasasexddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">
                                            {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                        @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                        @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qsawsasaswaaaaaaaaaavasasexddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">
                                            {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
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
                        @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 32)->where('bloque', 6) as $q)
                            <div class="text-center position-relative">
                                <table
                                    class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                    style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                    data-position="{{ ($bloque6A++ % 2) == 0 ? "2" : "1"  }}"
                                    data-bracket="{{ in_array($bloque6A, [2, 3]) ? "upper" : "lower" }}" data-id="{{ $q->id }}">
                                    <tr>
                                        <td class="text-center color-participantes qsaawsasaswaaaaaaaaaavasasexddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">
                                            {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                        @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                        @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes qsaawasasaswaaaaaaaaaavasasexddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">
                                            {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
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
                        @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 32)->where('bloque', 8) as $q)
                            <div class="text-center position-relative">
                                <table
                                    class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                    style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                    data-position="{{ ($bloque8A++ % 2) == 0 ? "2" : "1"  }}"
                                    data-bracket="{{ in_array($bloque8A, [2, 3]) ? "upper" : "lower" }}" data-id="{{ $q->id }}">
                                    <tr>
                                        <td class="text-center color-participantes qsaacwasasaswaaaaaaaaaavasasexddsddssrdddfrfrrrrrfrrrazwqcqswswcsdszvcqqwsscsscaassaascscssccsc">
                                            {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes">
                                            {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                        target="_blank">{{ $q->resultado }}</a>
                                </small>
                            </div>
                            <div class="mt-6"></div>
                        @endforeach
                    </div>
                </div>
            @elseif($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 16)
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 mb-1 position-absolute w-100 color-rotulos" style="top: 0">
                        <strong>SEMIFINAL</strong>
                    </p>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                    data-position="2"
                                    data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->id }}">
                                    <tr>
                                        <td class="text-center color-participantes m">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes mm">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->id }}"
                                        target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes mmm">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes mmmm">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 mb-1 position-absolute w-100 color-rotulos" style="top: 0"><strong>CUARTOS
                            DE FINAL</strong></p>
                    <div style="height: 100%;display: grid;align-items: center;">
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                    data-position="1"
                                    data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->id }}">
                                    <tr>
                                        <td class="text-center color-participantes mmmnm">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes mmbmnm">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->id }}"
                                        target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes mbmbmnm">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes mbmvbmnm">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div style="height: 100%;display: grid;align-items: center;">
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                    data-position="2"
                                    data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->id }}">
                                    <tr>
                                        <td class="text-center color-participantes mbmbvbmnm">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes mbvmbvbmnm">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->id }}"
                                        target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes mbvbmbvbmnm">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes mbvbvmbvbmnm">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 mb-1 position-absolute w-100 color-rotulos" style="top: 0"><strong>OCTAVOS
                            DE FINAL</strong></p>
                    <div style="height: 100%;display: grid;align-items: center;">
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)) > 0)
                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first() != null)
                                <div class="text-center position-relative">
                                    <table class="table table-bordered table-striped mb-0 table-game"
                                        data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                        data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                        data-position="1"
                                        data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->id }}">
                                        <tr>
                                            <td class="text-center color-participantes mbvhbvmbvbmnm">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugador_local_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes mbvghbvmbvbmnm">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugador_rival_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                    <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                        <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->id }}"
                                            target="_blank">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->resultado }}
                                        </a>
                                    </small>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr>
                                            <td class="text-center color-participantes mfbvghbvmbvbmnm">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes mfbtvghbvmbvbmnm">-</td>
                                        </tr>
                                    </table>
                                </div>
                            @endif
                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first() != null)
                                <div class="text-center position-relative">
                                    <table class="table table-bordered table-striped mb-0 table-game"
                                        data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                        data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                        data-position="2"
                                        data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->id }}">
                                        <tr>
                                            <td class="text-center color-participantes mfbdtvghbvmbvbmnm">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugador_local_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes mfbcvdtvghbvmbvbmnm">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugador_rival_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                    <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                        <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->id }}"
                                            target="_blank">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->resultado }}
                                        </a>
                                    </small>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr>
                                            <td class="text-center color-participantes mfbvcvdtvghbvmbvbmnm">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes mfbvvcvdtvghbvmbvbmnm">-</td>
                                        </tr>
                                    </table>
                                </div>
                            @endif
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes mfbvvvcvdtvghbvmbvbmnm">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes mfbvvvvcvdtvghbvmbvbmnm">-</td>
                                    </tr>
                                </table>
                            </div>
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes mfbvvvvvcvdtvghbvmbvbmnm">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes mfbvvcvvvcvdtvghbvmbvbmnm">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div style="height: 100%;display: grid;align-items: center;">
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)) > 0)
                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first() != null)
                                <div class="text-center position-relative">
                                    <table class="table table-bordered table-striped mb-0 table-game"
                                        data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                        data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                        data-position="1"
                                        data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->id }}">
                                        <tr>
                                            <td class="text-center color-participantes mfbgvvcvvvcvdtvghbvmbvbmnm">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugador_local_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes mfbxgvvcvvvcvdtvghbvmbvbmnm">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugador_rival_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                    <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                        <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->id }}"
                                            target="_blank">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->resultado }}
                                        </a>
                                    </small>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr>
                                            <td class="text-center color-participantes mfbxgzvvcvvvcvdtvghbvmbvbmnm">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes mfbxcgzvvcvvvcvdtvghbvmbvbmnm">-</td>
                                        </tr>
                                    </table>
                                </div>
                            @endif
                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first() != null)
                                <div class="text-center position-relative">
                                    <table class="table table-bordered table-striped mb-0 table-game"
                                        data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                        data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                        data-position="2"
                                        data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->id }}">
                                        <tr>
                                            <td class="text-center color-participantes mfbxacgzvvcvvvcvdtvghbvmbvbmnm">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugador_local_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes mfbxxacgzvvcvvvcvdtvghbvmbvbmnm">
                                                {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugador_rival_uno_id != null)
                                                    <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                    <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                        <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->id }}"
                                            target="_blank">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->resultado }}
                                        </a>
                                    </small>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr>
                                            <td class="text-center color-participantes mfbxxzacgzvvcvvvcvdtvghbvmbvbmnm">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center color-participantes mfbxzxzacgzvvcvvvcvdtvghbvmbvbmnm">-</td>
                                        </tr>
                                    </table>
                                </div>
                            @endif
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes mfbxzxxzacgzvvcvvvcvdtvghbvmbvbmnm">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes mfbaxzxxzacgzvvcvvvcvdtvghbvmbvbmnm">-</td>
                                    </tr>
                                </table>
                            </div>
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes mfbxaxzxxzacgzvvcvvvcvdtvghbvmbvbmnm">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes mfbxaaxzxxzacgzvvcvvvcvdtvghbvmbvbmnm">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 position-absolute mb-1 w-100 color-rotulos" style="top: 0"><strong>RONDA
                            DE 32</strong></p>
                    <div style="display: grid;align-items: center; height: 100%;position: relative;top: 20px;">
                        @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 2) as $q)
                            <div class="text-center position-relative">
                                <table
                                    class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                    style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                    data-position="{{ ($bloque2A++ % 2) == 0 ? "2" : "1"  }}"
                                    data-bracket="{{ in_array($bloque2A, [2, 3]) ? "upper" : "lower" }}" data-id="{{ $q->id }}">
                                    <tr>
                                        <td class="text-center color-participantes mfbexaaxzxxzacgzvvcvvvcvdtvghbvmbvbmnm">
                                            {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                        @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                        @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes mfbqexaaxzxxzacgzvvcvvvcvdtvghbvmbvbmnm">
                                            {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
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
                                <table
                                    class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                    style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                    data-position="{{ ($bloque4A++ % 2) == 0 ? "2" : "1"  }}"
                                    data-bracket="{{ in_array($bloque4A, [2, 3]) ? "upper" : "lower" }}" data-id="{{ $q->id }}">
                                    <tr>
                                        <td class="text-center color-participantes mfsbqexaaxzxxzacgzvvcvvvcvdtvghbvmbvbmnm">
                                            {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes mfsbxqexaaxzxxzacgzvvcvvvcvdtvghbvmbvbmnm">
                                            {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                        target="_blank">{{ $q->resultado }}</a>
                                </small>
                            </div>
                            <div class="mt-6"></div>
                        @endforeach
                    </div>
                </div>
            @elseif($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 8)
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 position-absolute mb-1 w-100 color-rotulos" style="top: 0">
                        <strong>SEMIFINAL</strong>
                    </p>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="2"
                                    style="cursor: pointer"
                                    data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->id }}">
                                    <tr>
                                        <td class="text-center color-participantes mfsbzxqexaaxzxxzacgzvvcvvvcvdtvghbvmbvbmnm">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes mfsbazxqexaaxzxxzacgzvvcvvvcvdtvghbvmbvbmnm">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->id }}"
                                        target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes mfsxbazxqexaaxzxxzacgzvvcvvvcvdtvghbvmbvbmnm">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes mfcsxbazxqexaaxzxxzacgzvvcvvvcvdtvghbvmbvbmnm">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 position-absolute mb-1 w-100 color-rotulos" style="top: 0"><strong>CUARTOS
                            DE FINAL</strong></p>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                    data-position="1"
                                    data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->id }}">
                                    <tr>
                                        <td class="text-center color-participantes mfczsxbazxqexaaxzxxzacgzvvcvvvcvdtvghbvmbvbmnm">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes mfczvsxbazxqexaaxzxxzacgzvvcvvvcvdtvghbvmbvbmnm">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($q->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-")  }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->id }}"
                                        target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                            <div class="mt-6"></div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes mfczevsxbazxqexaaxzxxzacgzvvcvvvcvdtvghbvmbvbmnm">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes mfczzevsxbazxqexaaxzxxzacgzvvcvvvcvdtvghbvmbvbmnm">-</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="mt-6"></div>
                        @endif
                    </div>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" style="cursor: pointer"
                                    data-position="2"
                                    data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->id }}">
                                    <tr>
                                        <td class="text-center color-participantes mfczxzevsxbazxqexaaxzxxzacgzvvcvvvcvdtvghbvmbvbmnm">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes mfcazxzevsxbazxqexaaxzxxzacgzvvcvvvcvdtvghbvmbvbmnm">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno != null ? ($q->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo_temporal) : "-")  }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->id }}"
                                        target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes mfcazaxzevsxbazxqexaaxzxxzacgzvvcvvvcvdtvghbvmbvbmnm">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes mfcaazaxzevsxbazxqexaaxzxxzacgzvvcvvvcvdtvghbvmbvbmnm">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs position-absolute m-0 mb-1 w-100 color-rotulos" style="top: 0"><strong>OCTAVOS
                            DE FINAL</strong></p>
                    <div style="height: 100%;display: grid;align-items: center">
                        @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2) as $q)
                            <div class="text-center position-relative">
                                <table
                                    class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                    style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                    data-position="{{ ($bloque2A++ % 2) == 0 ? "2" : "1"  }}" data-id="{{ $q->id }}">
                                    <tr>
                                        <td class="text-center color-participantes mfcasazaxzevsxbazxqexaaxzxxzacgzvvcvvvcvdtvghbvmbvbmnm">
                                            {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes mfcasdazaxzevsxbazxqexaaxzxxzacgzvvcvvvcvdtvghbvmbvbmnm">
                                            {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                        target="_blank">{{ $q->resultado }}</a>
                                </small>
                            </div>
                        @endforeach
                    </div>
                    <div style="height: 100%;display: grid;align-items: center">
                        @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4) as $q)
                            <div class="text-center position-relative">
                                <table
                                    class="table table-bordered table-striped mb-0 {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "" : "table-game" }}"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}"
                                    style="cursor: {{ $q->buy && !$TorneoFaseFinal->TorneoCategoria->manual ? "auto" : "pointer" }}"
                                    data-position="{{ ($bloque4A++ % 2) == 0 ? "2" : "1"  }}" data-id="{{ $q->id }}">
                                    <tr>
                                        <td class="text-center color-participantes mfcarsdazaxzevsxbazxqexaaxzxxzacgzvvcvvvcvdtvghbvmbvbmnm">
                                            {{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_local_uno_id && $q->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes mfcasrsdazaxzevsxbazxqexaaxzxxzacgzvvcvvvcvdtvghbvmbvbmnm">
                                            {{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-") }}
                                            @if($q->jugador_ganador_uno_id == $q->jugador_rival_uno_id && $q->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $q->id }}"
                                        target="_blank">{{ $q->resultado }}</a>
                                </small>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 4)
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 position-absolute mb-1 w-100 color-rotulos" style="top: 0">
                        <strong>SEMIFINAL</strong>
                    </p>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="2"
                                    style="cursor: pointer"
                                    data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->id }}">
                                    <tr>
                                        <td class="text-center color-participantes mfcasxrsdazaxzevsxbazxqexaaxzxxzacgzvvcvvvcvdtvghbvmbvbmnm">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes mfcaszxrsdazaxzevsxbazxqexaaxzxxzacgzvvcvvvcvdtvghbvmbvbmnm">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->id }}"
                                        target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes mfcavszxrsdazaxzevsxbazxqexaaxzxxzacgzvvcvvvcvdtvghbvmbvbmnm">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes zz">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 position-absolute mb-1 w-100 color-rotulos" style="top: 0"><strong>CUARTOS
                            DE FINAL</strong></p>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="1"
                                    style="cursor: pointer"
                                    data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->id }}">
                                    <tr>
                                        <td class="text-center color-participantes zxz">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes zxdz">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->id }}"
                                        target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                            <div class="mt-6"></div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes zxsdz">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes zxesdz">-</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="mt-6"></div>
                        @endif
                    </div>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0  table-game"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="2"
                                    style="cursor: pointer"
                                    data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->id }}">
                                    <tr>
                                        <td class="text-center color-participantes zxxesdz">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes zcxxesdz">
                                            {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo_temporal) : "-")  }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->id }}"
                                        target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes zcxxxesdz">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes zcvxxxesdz">-</td>
                                    </tr>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @elseif($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 2)
                <div style="height: 100%;display: grid;align-items: center;position: relative;">
                    <p class="text-center text-xs m-0 position-absolute mb-1 w-100 color-rotulos" style="top: 0">
                        <strong>SEMIFINAL</strong>
                    </p>
                    <div>
                        @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)) > 0)
                            <div class="text-center position-relative">
                                <table class="table table-bordered table-striped mb-0 table-game"
                                    data-category="{{ $TorneoFaseFinal->TorneoCategoria->id }}"
                                    data-manual="{{ $TorneoFaseFinal->TorneoCategoria->manual }}" data-position="2"
                                    style="cursor: pointer"
                                    data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->id }}">
                                    <tr>
                                        <td class="text-center color-participantes zcfvxxxesdz">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_local_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_local_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes zcxfvxxxesdz">
                                            {{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-") }}
                                            @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_ganador_uno_id == $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_rival_uno_id && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_rival_uno_id != null)
                                                <i class="fas fa-check text-success" style="margin-left: 9px;"></i>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <small class="text-bold color-rotulos position-absolute w-100" style="bottom:auto;left:0">
                                    <a href="/auth/torneo/partido/export/json?id={{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->id }}"
                                        target="_blank">
                                        {{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->resultado }}
                                    </a>
                                </small>
                            </div>
                            <div class="mt-6"></div>
                        @else
                            <div>
                                <table class="table table-bordered table-striped mb-0">
                                    <tr>
                                        <td class="text-center color-participantes zfcxfvxxxesdz">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center color-participantes">-</td>
                                    </tr>
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

                        <li class="mr-1"><button type="button" class="btn btn-success btn-download-cup"
                                data-category="{{ ($TorneoFaseFinal->TorneoCategoria->categoriaSimple->nombre) . "" . ($TorneoFaseFinal->TorneoCategoria->multiple ? " (Doble) " : "") }}"
                                data-random="{{ $TorneoFaseFinal->TorneoCategoria->aleatorio }}"
                                data-id="{{ $TorneoFaseFinal->TorneoCategoria->id  }}"><i class="fa fa-image"></i> Descargar
                                Llaves</button></li>
                        <li class="mr-1"><button type="button" class="btn btn-primary btn-download-cup-cuartos"
                                data-category="{{ ($TorneoFaseFinal->TorneoCategoria->categoriaSimple->nombre) . "" . ($TorneoFaseFinal->TorneoCategoria->multiple ? " (Doble) " : "") }}"
                                data-random="{{ $TorneoFaseFinal->TorneoCategoria->aleatorio }}"
                                data-id="{{ $TorneoFaseFinal->TorneoCategoria->id  }}"><i class="fa fa-image"></i> Descargar
                                Reporte 1/4 de final</button></li>
                        <li class="mr-1"><button type="button" class="btn btn-primary btn-finish-keys-final"
                                data-random="{{ $TorneoFaseFinal->TorneoCategoria->aleatorio }}"
                                data-id="{{ $TorneoFaseFinal->TorneoCategoria->id  }}"><i class="fa fa-save"></i> Finalizar
                                llaves</button></li>
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

                        <li class="mr-1"><button type="button" class="btn btn-primary btn-download-cup-cuartos"
                                data-category="{{ ($TorneoFaseFinal->TorneoCategoria->categoriaSimple->nombre) . "" . ($TorneoFaseFinal->TorneoCategoria->multiple ? " (Doble) " : "") }}"
                                data-random="{{ $TorneoFaseFinal->TorneoCategoria->aleatorio }}"
                                data-id="{{ $TorneoFaseFinal->TorneoCategoria->id  }}"><i class="fa fa-image"></i> Descargar
                                Reporte 1/4 de final</button></li>
                        <li class="mr-1"><button type="button" class="btn btn-success btn-download-cup"
                                data-category="{{ ($TorneoFaseFinal->TorneoCategoria->categoriaSimple->nombre) . "" . ($TorneoFaseFinal->TorneoCategoria->multiple ? " (Doble) " : "") }}"
                                data-random="{{ $TorneoFaseFinal->TorneoCategoria->aleatorio }}"
                                data-id="{{ $TorneoFaseFinal->TorneoCategoria->id  }}"><i class="fa fa-image"></i> Descargar
                                Llaves</button></li>
                                                        <li class="mr-1"><button type="button" class="btn btn-info btn-reporte-finales"
                                data-torneo-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->id }}"><i class="fa fa-file-text"></i> Reporte Finales</button></li>

                    </ul>
                </div>
            </div>
        @endif
    @endif
@endif


<script type="text/javascript">
    $(function () {
        // Function to refresh the tournament map
        function refrescarMapa(){
            invocarVista(`/auth/{{strtolower($ViewName)}}/fase-final/mapa/partialView/{{ $TorneoFaseFinal->TorneoCategoria->torneo->id }}/{{ $TorneoFaseFinal->TorneoCategoria->id }}/{{ $landing }}`, function(data){
                $("#mapaCampeonato{{ $TorneoFaseFinal->TorneoCategoria->id }}").html(data);
            });
        }

        // Existing code for player changes
        const $btnChangePlayerClass = $(".btn-change-player-class");
        $btnChangePlayerClass.on("click", function () {
            invocarModal(`/auth/{{strtolower($ViewName)}}/fase-final/players/changes/{{$TorneoFaseFinal->TorneoCategoria->torneo->id}}/{{$TorneoFaseFinal->TorneoCategoria->id}}`,
                function ($modal) {
                    if ($modal.attr("data-reload") === "true") refrescarMapa();
                });
        });
        
                // Handle Reporte Finales button click
        const $btnReporteFinales = $(".btn-reporte-finales");
        $btnReporteFinales.on("click", function () {
            const torneoId = $(this).attr("data-torneo-id");
            const url = `/auth/torneo/getTorneoFinales?torneo_id=${torneoId}`;
            
            // Open in a new tab/window
            window.open(url, '_blank');
        });
    });
</script>