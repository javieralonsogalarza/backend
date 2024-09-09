@inject('Auth', '\Illuminate\Support\Facades\Auth')
@inject('App', 'App\Models\App')
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reporte</title>
    <link rel="stylesheet" href="{{ asset('https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css') }}" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style type="text/css">
        thead th{ background-color: {{ $Auth::guard('web')->user()->comunidad != null ? $Auth::guard('web')->user()->comunidad->color_primario : "#000000" }};color: #ffffff; }
        thead th, tbody td{ font-size: {{ $TorneoCategoria->multiple ? "9px" : "11px"}} !important; padding: 5px !important; text-align: center; }
        h5{ margin-bottom: 20px }
        table > thead:first-of-type > tr:last-child {page-break-after: avoid;}
        .page-break {page-break-before: always;}
        .hidden{ display: none}
    </style>
</head>
<body>
    <div class="row">
        <div class="col-md-12 text-center">
            <h3>Reporte del Torneo "{{ $Torneo->nombre }}", Categoría "{{ $Categoria->nombre }}"</h3>
        </div>
    </div>
    <div class="row mt-2">
        @if(count($Grupos) > 0)
            @foreach($Grupos as $key => $g)
                <div class="hidden">{{ $Count++ }}</div>
                <div class="mt-3">
                    <h5>{{ $g->nombre_grupo }}</h5>
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th class="align-middle text-center" rowspan="2" align="center">Partidos</th>
                            <th colspan="2" class="align-middle text-center" rowspan="1" align="center">Plazo de Juego</th>
                            <th width="100" rowspan="2" class="align-middle text-center" align="center">Resultado</th>
                            <th colspan="3" class="align-middle text-center" rowspan="1" align="center">Ganador</th>
                            <th colspan="3" class="align-middle text-center" rowspan="1" align="center">Rival</th>
                        </tr>
                        <tr role="row">
                            <th colspan="1" class="align-middle text-center" align="center">Fecha Inicio</th>
                            <th colspan="1" class="align-middle text-center" align="center">Fecha Final</th>

                            <th colspan="1" class="align-middle text-center" align="center">Jugador</th>
                            <th width="30" colspan="1" class="align-middle text-center" align="center">Sets</th>
                            <th width="30" colspan="1" class="align-middle text-center" align="center">Games</th>

                            <th colspan="1" class="align-middle text-center" align="center">Jugador</th>
                            <th width="30" colspan="1" class="align-middle text-center" align="center">Sets</th>
                            <th width="30" colspan="1" class="align-middle text-center" align="center">Games</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(count($Partidos->where('grupo_id', $g->grupo_id)->whereNull('fase')) > 0)
                            @foreach($Partidos->where('grupo_id', $g->grupo_id)->whereNull('fase') as $p)
                                <tr>
                                    <td class="text-center">{{ $TorneoCategoria->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }} vs {{ $TorneoCategoria->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                                    <td class="text-center">{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                                    <td class="text-center">{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                                    <td class="text-center">{{ $p->resultado }}</td>

                                    @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                        <td class="text-center">-</td>
                                    @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                                        <td class="text-center">{{ $TorneoCategoria->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }}</td>
                                    @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                                        <td class="text-center">{{ $TorneoCategoria->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                                    @else
                                        <td class="text-center">-</td>
                                    @endif

                                    <td width="50" class="text-center">{{ $p->jugador_local_set }}</td>
                                    <td width="50" class="text-center">{{ $p->jugador_local_juego }}</td>

                                    @if(trim($p->resultado) === "-")
                                        <td class="text-center">-</td>
                                    @else
                                        @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                            <td class="text-center">-</td>
                                        @elseif($p->jugador_local_uno_id != $p->jugador_ganador_uno_id)
                                            <td class="text-center">{{ $TorneoCategoria->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo }}</td>
                                        @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                                            <td class="text-center">{{ $TorneoCategoria->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo }}</td>
                                        @else
                                            <td class="text-center">-</td>
                                        @endif
                                    @endif

                                    <td width="50" class="text-center">{{ $p->jugador_rival_set }}</td>
                                    <td width="50" class="text-center">{{ $p->jugador_rival_juego }}</td>
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
                @if($Count % 2 == 0 && count($Grupos) > ($key+1))
                    <div class="page-break"></div> <!-- Page break -->
                @endif
            @endforeach
        @endif

        @if(count($Partidos->where('fase', 16)) > 0)
            <div class="hidden">{{ $Count++ }}</div>
            <div class="mt-3">
                <h5>Ronda 32</h5>
                <table class="table table-bordered table-striped mb-3">
                    <thead>
                    <tr>
                        <th class="align-middle text-center" rowspan="2" align="center">Partidos</th>
                        <th colspan="2" class="align-middle text-center" rowspan="1" align="center">Plazo de Juego</th>
                        <th width="100" rowspan="2" class="align-middle text-center" align="center">Resultado</th>
                        <th colspan="3" class="align-middle text-center" rowspan="1" align="center">Ganador</th>
                        <th colspan="3" class="align-middle text-center" rowspan="1" align="center">Rival</th>
                    </tr>
                    <tr role="row">
                        <th colspan="1" class="align-middle text-center" align="center">Fecha Inicio</th>
                        <th colspan="1" class="align-middle text-center" align="center">Fecha Final</th>

                        <th colspan="1" class="align-middle text-center" align="center">Jugador</th>
                        <th width="30" colspan="1" class="align-middle text-center" align="center">Sets</th>
                        <th width="30" colspan="1" class="align-middle text-center" align="center">Games</th>

                        <th colspan="1" class="align-middle text-center" align="center">Jugador</th>
                        <th width="30" colspan="1" class="align-middle text-center" align="center">Sets</th>
                        <th width="30" colspan="1" class="align-middle text-center" align="center">Games</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($Partidos->where('fase', 16) as $p)
                        <tr>
                            <td class="text-center">{{ $p->buy_all ? "BYE" : ($TorneoCategoria->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-")) }} vs {{ $p->buy ? "BYE" : ($TorneoCategoria->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-")) }}</td>
                            <td class="text-center">{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                            <td class="text-center">{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                            <td class="text-center">{{ $p->resultado }}</td>

                            @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                <td></td>
                            @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                                <td>{{ $p->buy_all ? "BYE" : ($TorneoCategoria->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-")) }}</td>
                            @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                                <td>{{ $p->buy ? "BYE" : ($TorneoCategoria->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-")) }}</td>
                            @else
                                <td></td>
                            @endif

                            <td width="50" class="text-center">{{ $p->jugador_local_set }}</td>
                            <td width="50" class="text-center">{{ $p->jugador_local_juego }}</td>

                            @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                <td></td>
                            @elseif($p->jugador_local_uno_id != $p->jugador_ganador_uno_id)
                                <td>{{ $p->buy_all ? "BYE" : ($TorneoCategoria->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo) }}</td>
                            @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                                <td>{{ $p->buy ? "BYE" : ($TorneoCategoria->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo) }}</td>
                            @else
                                <td></td>
                            @endif

                            <td width="50" class="text-center">{{ $p->jugador_rival_set }}</td>
                            <td width="50" class="text-center">{{ $p->jugador_rival_juego }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @if($Count % 2 == 0)
                <div class="page-break"></div> <!-- Page break -->
            @endif
        @endif

        @if(count($Partidos->where('fase', 8)) > 0)
            <div class="hidden">{{ $Count++ }}</div>
            <div class="mt-3">
                <h5>Octavos de final</h5>
                <table class="table table-bordered table-striped mb-3">
                    <thead>
                    <tr>
                        <th class="align-middle text-center" rowspan="2" align="center">Partidos</th>
                        <th colspan="2" class="align-middle text-center" rowspan="1" align="center">Plazo de Juego</th>
                        <th width="100" rowspan="2" class="align-middle text-center" align="center">Resultado</th>
                        <th colspan="3" class="align-middle text-center" rowspan="1" align="center">Ganador</th>
                        <th colspan="3" class="align-middle text-center" rowspan="1" align="center">Rival</th>
                    </tr>
                    <tr role="row">
                        <th colspan="1" class="align-middle text-center" align="center">Fecha Inicio</th>
                        <th colspan="1" class="align-middle text-center" align="center">Fecha Final</th>

                        <th colspan="1" class="align-middle text-center" align="center">Jugador</th>
                        <th width="30" colspan="1" class="align-middle text-center" align="center">Sets</th>
                        <th width="30" colspan="1" class="align-middle text-center" align="center">Games</th>

                        <th colspan="1" class="align-middle text-center" align="center">Jugador</th>
                        <th width="30" colspan="1" class="align-middle text-center" align="center">Sets</th>
                        <th width="30" colspan="1" class="align-middle text-center" align="center">Games</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($Partidos->where('fase', 8) as $p)
                        <tr>
                            <td class="text-center">{{ $p->buy_all ? "BYE" : ($TorneoCategoria->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-")) }} vs {{ $p->buy ? "BYE" : ($TorneoCategoria->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-")) }}</td>
                            <td class="text-center">{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                            <td class="text-center">{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                            <td class="text-center">{{ $p->resultado }}</td>

                            @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                <td></td>
                            @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                                <td>{{ $p->buy_all ? "BYE" : ($TorneoCategoria->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-")) }}</td>
                            @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                                <td>{{ $p->buy ? "BYE" : ($TorneoCategoria->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-")) }}</td>
                            @else
                                <td></td>
                            @endif

                            <td width="50" class="text-center">{{ $p->jugador_local_set }}</td>
                            <td width="50" class="text-center">{{ $p->jugador_local_juego }}</td>

                            @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                <td></td>
                            @elseif($p->jugador_local_uno_id != $p->jugador_ganador_uno_id)
                                <td>{{ $p->buy_all ? "BYE" : ($TorneoCategoria->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo) }}</td>
                            @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                                <td>{{ $p->buy ? "BYE" : ($TorneoCategoria->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo) }}</td>
                            @else
                                <td></td>
                            @endif

                            <td width="50" class="text-center">{{ $p->jugador_rival_set }}</td>
                            <td width="50" class="text-center">{{ $p->jugador_rival_juego }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @if($Count % 2 == 0)
                <div class="page-break"></div> <!-- Page break -->
            @endif
        @endif

        @if(count($Partidos->where('fase', 4)) > 0)
            <div class="hidden">{{ $Count++ }}</div>
            <div class="mt-3">
                <h5>Cuartos de final</h5>
                <table class="table table-bordered table-striped mb-3">
                    <thead>
                    <tr>
                        <th class="align-middle text-center" rowspan="2" align="center">Partidos</th>
                        <th colspan="2" class="align-middle text-center" rowspan="1" align="center">Plazo de Juego</th>
                        <th width="100" rowspan="2" class="align-middle text-center" align="center">Resultado</th>
                        <th colspan="3" class="align-middle text-center" rowspan="1" align="center">Ganador</th>
                        <th colspan="3" class="align-middle text-center" rowspan="1" align="center">Rival</th>
                    </tr>
                    <tr role="row">
                        <th colspan="1" class="align-middle text-center" align="center">Fecha Inicio</th>
                        <th colspan="1" class="align-middle text-center" align="center">Fecha Final</th>

                        <th colspan="1" class="align-middle text-center" align="center">Jugador</th>
                        <th width="30" colspan="1" class="align-middle text-center" align="center">Sets</th>
                        <th width="30" colspan="1" class="align-middle text-center" align="center">Games</th>

                        <th colspan="1" class="align-middle text-center" align="center">Jugador</th>
                        <th width="30" colspan="1" class="align-middle text-center" align="center">Sets</th>
                        <th width="30" colspan="1" class="align-middle text-center" align="center">Games</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($Partidos->where('fase', 4) as $p)
                        <tr>
                            <td class="text-center">{{ $p->buy_all ? "BYE" : ($TorneoCategoria->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-")) }} vs {{ $p->buy ? "BYE" : ($TorneoCategoria->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-")) }}</td>
                            <td class="text-center">{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                            <td class="text-center">{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                            <td class="text-center">{{ $p->resultado }}</td>

                            @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                <td></td>
                            @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                                <td>{{ $p->buy_all ? "BYE" : ($TorneoCategoria->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-")) }}</td>
                            @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                                <td>{{ $p->buy ? "BYE" : ($TorneoCategoria->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-")) }}</td>
                            @else
                                <td></td>
                            @endif

                            <td width="50" class="text-center">{{ $p->jugador_local_set }}</td>
                            <td width="50" class="text-center">{{ $p->jugador_local_juego }}</td>

                            @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                <td></td>
                            @elseif($p->jugador_local_uno_id != $p->jugador_ganador_uno_id)
                                <td>{{ $p->buy_all ? "BYE" : ($TorneoCategoria->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo) }}</td>
                            @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                                <td>{{ $p->buy ? "BYE" : ($TorneoCategoria->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo) }}</td>
                            @else
                                <td></td>
                            @endif

                            <td width="50" class="text-center">{{ $p->jugador_rival_set }}</td>
                            <td width="50" class="text-center">{{ $p->jugador_rival_juego }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @if($Count % 2 == 0)
                <div class="page-break"></div> <!-- Page break -->
            @endif
        @endif

        @if(count($Partidos->where('fase', 2)) > 0)
            <div class="hidden">{{ $Count++ }}</div>
            <div class="mt-3">
                <h5>Semifinal</h5>
                <table class="table table-bordered table-striped mb-3">
                    <thead>
                    <tr>
                        <th class="align-middle text-center" rowspan="2" align="center">Partidos</th>
                        <th colspan="2" class="align-middle text-center" rowspan="1" align="center">Plazo de Juego</th>
                        <th width="100" rowspan="2" class="align-middle text-center" align="center">Resultado</th>
                        <th colspan="3" class="align-middle text-center" rowspan="1" align="center">Ganador</th>
                        <th colspan="3" class="align-middle text-center" rowspan="1" align="center">Rival</th>
                    </tr>
                    <tr role="row">
                        <th colspan="1" class="align-middle text-center" align="center">Fecha Inicio</th>
                        <th colspan="1" class="align-middle text-center" align="center">Fecha Final</th>

                        <th colspan="1" class="align-middle text-center" align="center">Jugador</th>
                        <th width="30" colspan="1" class="align-middle text-center" align="center">Sets</th>
                        <th width="30" colspan="1" class="align-middle text-center" align="center">Games</th>

                        <th colspan="1" class="align-middle text-center" align="center">Jugador</th>
                        <th width="30" colspan="1" class="align-middle text-center" align="center">Sets</th>
                        <th width="30" colspan="1" class="align-middle text-center" align="center">Games</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($Partidos->where('fase', 2) as $p)
                        <tr>
                            <td class="text-center">{{ $p->buy_all ? "BYE" : ($TorneoCategoria->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-")) }} vs {{ $TorneoCategoria->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                            <td class="text-center">{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                            <td class="text-center">{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                            <td class="text-center">{{ $p->resultado }}</td>

                            @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                <td></td>
                            @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                                <td>{{ $TorneoCategoria->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }}</td>
                            @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                                <td>{{ $TorneoCategoria->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                            @else
                                <td></td>
                            @endif

                            <td width="50" class="text-center">{{ $p->jugador_local_set }}</td>
                            <td width="50" class="text-center">{{ $p->jugador_local_juego }}</td>

                            @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                <td></td>
                            @elseif($p->jugador_local_uno_id != $p->jugador_ganador_uno_id)
                                <td>{{ $TorneoCategoria->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo }}</td>
                            @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                                <td>{{ $TorneoCategoria->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo }}</td>
                            @else
                                <td></td>
                            @endif

                            <td width="50" class="text-center">{{ $p->jugador_rival_set }}</td>
                            <td width="50" class="text-center">{{ $p->jugador_rival_juego }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @if($Count % 2 == 0)
                <div class="page-break"></div> <!-- Page break -->
            @endif
        @endif

        @if(count($Partidos->where('fase', 1)) > 0)
            <div class="mt-3">
                <h5>Final</h5>
                <table class="table table-bordered table-striped mb-3">
                    <thead>
                    <tr>
                        <th class="align-middle text-center" rowspan="2" align="center">Partidos</th>
                        <th colspan="2" class="align-middle text-center" rowspan="1" align="center">Plazo de Juego</th>
                        <th width="100" rowspan="2" class="align-middle text-center" align="center">Resultado</th>
                        <th colspan="3" class="align-middle text-center" rowspan="1" align="center">Ganador</th>
                        <th colspan="3" class="align-middle text-center" rowspan="1" align="center">Rival</th>
                    </tr>
                    <tr role="row">
                        <th colspan="1" class="align-middle text-center" align="center">Fecha Inicio</th>
                        <th colspan="1" class="align-middle text-center" align="center">Fecha Final</th>

                        <th colspan="1" class="align-middle text-center" align="center">Jugador</th>
                        <th width="30" colspan="1" class="align-middle text-center" align="center">Sets</th>
                        <th width="30" colspan="1" class="align-middle text-center" align="center">Games</th>

                        <th colspan="1" class="align-middle text-center" align="center">Jugador</th>
                        <th width="30" colspan="1" class="align-middle text-center" align="center">Sets</th>
                        <th width="30" colspan="1" class="align-middle text-center" align="center">Games</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($Partidos->where('fase', 1) as $p)
                        <tr>
                            <td class="text-center">{{ $p->buy_all ? "BYE" : ($TorneoCategoria->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-")) }} vs {{ $TorneoCategoria->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                            <td class="text-center">{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                            <td class="text-center">{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                            <td class="text-center">{{ $p->resultado }}</td>

                            @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                <td></td>
                            @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                                <td>{{ $TorneoCategoria->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }}</td>
                            @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                                <td>{{ $TorneoCategoria->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                            @else
                                <td></td>
                            @endif

                            <td width="50" class="text-center">{{ $p->jugador_local_set }}</td>
                            <td width="50" class="text-center">{{ $p->jugador_local_juego }}</td>

                            @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                <td></td>
                            @elseif($p->jugador_local_uno_id != $p->jugador_ganador_uno_id)
                                <td>{{ $TorneoCategoria->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo }}</td>
                            @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                                <td>{{ $TorneoCategoria->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo }}</td>
                            @else
                                <td></td>
                            @endif

                            <td width="50" class="text-center">{{ $p->jugador_rival_set }}</td>
                            <td width="50" class="text-center">{{ $p->jugador_rival_juego }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</body>
</html>
