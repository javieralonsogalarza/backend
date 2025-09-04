@inject('Auth', '\Illuminate\Support\Facades\Auth')
@inject('App', 'App\Models\App')
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reporte Partidos de Jugador</title>
    <style type="text/css">
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .player-info {
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .section-title {
            background-color: #f5f5f5;
            padding: 8px;
            font-weight: bold;
            font-size: 12px;
            border-left: 4px solid #007bff;
            margin: 15px 0 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }        th {
            background-color: #00b7f1;
            color: #ffffff;
            font-weight: bold;
            font-size: 8px;
        }
        .no-data {
            text-align: center;
            font-style: italic;
            color: #666;
            padding: 20px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding: 5px;
        }
        @page {
            margin: 1cm;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $Data['Torneo']->nombre }}, Categoría {{ $Data['Categoria']->nombre }}</h2>
    </div>
    
    <div class="player-info">
        <h3>Jugador {{ $Data['Jugador']->nombre_completo }}</h3>
    </div>

    @if(count($Data['Partidos']->whereNull('fase')) > 0)
    <div class="section-title">Fase de Grupos</div>
        <table>
            <thead>
                <tr>
                    <th rowspan="2">Partidos</th>
                    <th colspan="2">Plazo de Juego</th>
                    <th rowspan="2">Resultado</th>
                    <th colspan="3">Ganador</th>
                    <th colspan="3">Rival</th>
                </tr>
                <tr>
                    <th>Fecha Inicio</th>
                    <th>Fecha Final</th>
                    <th>Jugador</th>
                    <th>Sets</th>
                    <th>Games</th>
                    <th>Jugador</th>
                    <th>Sets</th>
                    <th>Games</th>
                </tr>
            </thead>
            <tbody>
                @foreach($Data['Partidos']->whereNull('fase') as $p)
                    <tr>
                        <td>{{ $Data['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }} vs {{ $Data['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                        <td>{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                        <td>{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                        <td>{{ $p->resultado }}</td>

                        @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                            <td>-</td>
                        @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                            <td>{{ $Data['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }}</td>
                        @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                            <td>{{ $Data['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                        @else
                            <td>-</td>
                        @endif

                        <td>{{ $p->jugador_local_set }}</td>
                        <td>{{ $p->jugador_local_juego }}</td>

                        @if(trim($p->resultado) === "-")
                            <td>-</td>
                        @else
                            @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                <td>-</td>
                            @elseif($p->jugador_local_uno_id != $p->jugador_ganador_uno_id)
                                <td>{{ $Data['TorneoCategoria']->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo }}</td>
                            @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                                <td>{{ $Data['TorneoCategoria']->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo }}</td>
                            @else
                                <td>-</td>
                            @endif
                        @endif

                        <td>{{ $p->jugador_rival_set }}</td>
                        <td>{{ $p->jugador_rival_juego }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(count($Data['Partidos']->where('fase', 16)) > 0)
        <div class="section-title">Ronda 32</div>
        <table>
            <thead>
                <tr>
                    <th rowspan="2">Partidos</th>
                    <th colspan="2">Plazo de Juego</th>
                    <th rowspan="2">Resultado</th>
                    <th colspan="3">Ganador</th>
                    <th colspan="3">Rival</th>
                </tr>
                <tr>
                    <th>Fecha Inicio</th>
                    <th>Fecha Final</th>
                    <th>Jugador</th>
                    <th>Sets</th>
                    <th>Games</th>
                    <th>Jugador</th>
                    <th>Sets</th>
                    <th>Games</th>
                </tr>
            </thead>
            <tbody>
                @foreach($Data['Partidos']->where('fase', 16) as $p)
                    <tr>
                        <td>{{ $Data['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }} vs {{ $p->buy ? "BYE" : ($Data['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-")) }}</td>
                        <td>{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                        <td>{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                        <td>{{ $p->resultado }}</td>

                        @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                            <td>-</td>
                        @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                            <td>{{ $Data['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }}</td>
                        @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                            <td>{{ $p->buy ? "BYE" :  ($Data['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-")) }}</td>
                        @else
                            <td>-</td>
                        @endif

                        <td>{{ $p->jugador_local_set }}</td>
                        <td>{{ $p->jugador_local_juego }}</td>

                        @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                            <td>-</td>
                        @elseif($p->jugador_local_uno_id != $p->jugador_ganador_uno_id)
                            <td>{{ $Data['TorneoCategoria']->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo }}</td>
                        @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                            <td>{{ $p->buy ? "BYE" : ($Data['TorneoCategoria']->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo) }}</td>
                        @else
                            <td>-</td>
                        @endif

                        <td>{{ $p->jugador_rival_set }}</td>
                        <td>{{ $p->jugador_rival_juego }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(count($Data['Partidos']->where('fase', 8)) > 0)
        <div class="section-title">Octavos de final</div>
        <table>
            <thead>
                <tr>
                    <th rowspan="2">Partidos</th>
                    <th colspan="2">Plazo de Juego</th>
                    <th rowspan="2">Resultado</th>
                    <th colspan="3">Ganador</th>
                    <th colspan="3">Rival</th>
                </tr>
                <tr>
                    <th>Fecha Inicio</th>
                    <th>Fecha Final</th>
                    <th>Jugador</th>
                    <th>Sets</th>
                    <th>Games</th>
                    <th>Jugador</th>
                    <th>Sets</th>
                    <th>Games</th>
                </tr>
            </thead>
            <tbody>
                @foreach($Data['Partidos']->where('fase', 8) as $p)
                    <tr>
                        <td>{{ $Data['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }} vs {{ $Data['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                        <td>{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                        <td>{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                        <td>{{ $p->resultado }}</td>

                        @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                            <td>-</td>
                        @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                            <td>{{ $Data['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }}</td>
                        @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                            <td>{{ $p->buy ? "BYE" : ($Data['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-")) }}</td>
                        @else
                            <td>-</td>
                        @endif

                        <td>{{ $p->jugador_local_set }}</td>
                        <td>{{ $p->jugador_local_juego }}</td>

                        @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                            <td>-</td>
                        @elseif($p->jugador_local_uno_id != $p->jugador_ganador_uno_id)
                            <td>{{ $Data['TorneoCategoria']->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo }}</td>
                        @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                            <td>{{ $p->buy ? "BYE" : ($Data['TorneoCategoria']->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo) }}</td>
                        @else
                            <td>-</td>
                        @endif

                        <td>{{ $p->jugador_rival_set }}</td>
                        <td>{{ $p->jugador_rival_juego }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(count($Data['Partidos']->where('fase', 4)) > 0)
        <div class="section-title">Cuartos de final</div>
        <table>
            <thead>
                <tr>
                    <th rowspan="2">Partidos</th>
                    <th colspan="2">Plazo de Juego</th>
                    <th rowspan="2">Resultado</th>
                    <th colspan="3">Ganador</th>
                    <th colspan="3">Rival</th>
                </tr>
                <tr>
                    <th>Fecha Inicio</th>
                    <th>Fecha Final</th>
                    <th>Jugador</th>
                    <th>Sets</th>
                    <th>Games</th>
                    <th>Jugador</th>
                    <th>Sets</th>
                    <th>Games</th>
                </tr>
            </thead>
            <tbody>
                @foreach($Data['Partidos']->where('fase', 4) as $p)
                    <tr>
                        <td>{{ $Data['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }} vs {{ $p->buy ? "BYE" : ($Data['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-")) }}</td>
                        <td>{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                        <td>{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                        <td>{{ $p->resultado }}</td>

                        @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                            <td>-</td>
                        @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                            <td>{{ $Data['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }}</td>
                        @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                            <td>{{ $p->buy ? "BYE" : ($Data['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-")) }}</td>
                        @else
                            <td>-</td>
                        @endif

                        <td>{{ $p->jugador_local_set }}</td>
                        <td>{{ $p->jugador_local_juego }}</td>

                        @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                            <td>-</td>
                        @elseif($p->jugador_local_uno_id != $p->jugador_ganador_uno_id)
                            <td>{{ $Data['TorneoCategoria']->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo }}</td>
                        @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                            <td>{{ $p->buy ? "BYE" : ($Data['TorneoCategoria']->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo) }}</td>
                        @else
                            <td>-</td>
                        @endif

                        <td>{{ $p->jugador_rival_set }}</td>
                        <td>{{ $p->jugador_rival_juego }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(count($Data['Partidos']->where('fase', 2)) > 0)
        <div class="section-title">Semifinal</div>
        <table>
            <thead>
                <tr>
                    <th rowspan="2">Partidos</th>
                    <th colspan="2">Plazo de Juego</th>
                    <th rowspan="2">Resultado</th>
                    <th colspan="3">Ganador</th>
                    <th colspan="3">Rival</th>
                </tr>
                <tr>
                    <th>Fecha Inicio</th>
                    <th>Fecha Final</th>
                    <th>Jugador</th>
                    <th>Sets</th>
                    <th>Games</th>
                    <th>Jugador</th>
                    <th>Sets</th>
                    <th>Games</th>
                </tr>
            </thead>
            <tbody>
                @foreach($Data['Partidos']->where('fase', 2) as $p)
                    <tr>
                        <td>{{ $Data['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }} vs {{ $Data['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                        <td>{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                        <td>{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                        <td>{{ $p->resultado }}</td>

                        @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                            <td>-</td>
                        @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                            <td>{{ $Data['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }}</td>
                        @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                            <td>{{ $Data['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                        @else
                            <td>-</td>
                        @endif

                        <td>{{ $p->jugador_local_set }}</td>
                        <td>{{ $p->jugador_local_juego }}</td>

                        @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                            <td>-</td>
                        @elseif($p->jugador_local_uno_id != $p->jugador_ganador_uno_id)
                            <td>{{ $Data['TorneoCategoria']->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo }}</td>
                        @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                            <td>{{ $Data['TorneoCategoria']->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo }}</td>
                        @else
                            <td>-</td>
                        @endif

                        <td>{{ $p->jugador_rival_set }}</td>
                        <td>{{ $p->jugador_rival_juego }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(count($Data['Partidos']->where('fase', 1)) > 0)
        <div class="section-title">Final</div>
        <table>
            <thead>
                <tr>
                    <th rowspan="2">Partidos</th>
                    <th colspan="2">Plazo de Juego</th>
                    <th rowspan="2">Resultado</th>
                    <th colspan="3">Ganador</th>
                    <th colspan="3">Rival</th>
                </tr>
                <tr>
                    <th>Fecha Inicio</th>
                    <th>Fecha Final</th>
                    <th>Jugador</th>
                    <th>Sets</th>
                    <th>Games</th>
                    <th>Jugador</th>
                    <th>Sets</th>
                    <th>Games</th>
                </tr>
            </thead>
            <tbody>
                @foreach($Data['Partidos']->where('fase', 1) as $p)
                    <tr>
                        <td>{{ $Data['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }} vs {{ $Data['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                        <td>{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                        <td>{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                        <td>{{ $p->resultado }}</td>

                        @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                            <td>-</td>
                        @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                            <td>{{ $Data['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }}</td>
                        @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                            <td>{{ $Data['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                        @else
                            <td>-</td>
                        @endif

                        <td>{{ $p->jugador_local_set }}</td>
                        <td>{{ $p->jugador_local_juego }}</td>

                        @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                            <td>-</td>
                        @elseif($p->jugador_local_uno_id != $p->jugador_ganador_uno_id)
                            <td>{{ $Data['TorneoCategoria']->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo }}</td>
                        @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                            <td>{{ $Data['TorneoCategoria']->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo }}</td>
                        @else
                            <td>-</td>
                        @endif

                        <td>{{ $p->jugador_rival_set }}</td>
                        <td>{{ $p->jugador_rival_juego }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(!$Data['Partidos'] || count($Data['Partidos']) == 0)
        <div class="no-data">
            No se encontraron partidos para este jugador en el torneo seleccionado.
        </div>
    @endif

    <div class="footer">
        Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
