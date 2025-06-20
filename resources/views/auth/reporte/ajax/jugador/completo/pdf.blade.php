@inject('Auth', '\Illuminate\Support\Facades\Auth')
@inject('App', 'App\Models\App')
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reporte Completo de Jugador</title>
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
        .player-details {
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .player-details ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .player-details li {
            margin-bottom: 5px;
        }
        .section-title {
            background-color: #f5f5f5;
            padding: 8px;
            font-weight: bold;
            font-size: 12px;
            border-left: 4px solid #007bff;
            margin: 15px 0 10px 0;
        }
        .tournament-title {
            background-color: #e9ecef;
            padding: 8px;
            font-weight: bold;
            font-size: 11px;
            border-left: 4px solid #28a745;
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
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 8px;
        }
        .summary-table th {
            background-color: #007bff;
            color: white;
            font-size: 9px;
        }
        .summary-table td {
            font-size: 8px;
        }
        .tournament-summary {
            background-color: #f8f9fa;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #28a745;
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
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Reporte Completo de Jugador</h2>
    </div>
    
    <div class="player-info">
        <h3>{{ $Jugador->nombre_completo }}</h3>
    </div>

    <div class="player-details">
        <ul>
            <li><strong>Tipo Documento:</strong> {{ $Jugador->tipoDocumento != null ? $Jugador->tipoDocumento->nombre : "-" }}</li>
            <li><strong>N° Documento:</strong> {{ $Jugador->nro_documento }}</li>
            <li><strong>Celular:</strong> {{ $Jugador->celular != null && $Jugador->celular != "" ? $Jugador->celular : "-" }}</li>
            <li><strong>Edad:</strong> {{ $Jugador->edad != null && $Jugador->edad != "" ? $Jugador->edad." años" : "-"}}</li>
            <li><strong>Altura:</strong> {{ $Jugador->altura != null && $Jugador->altura != "" ? $Jugador->altura."m" : "-"}}</li>
            <li><strong>Peso:</strong> {{ $Jugador->peso != null && $Jugador->peso != "" ? $Jugador->peso."kg" : "-"}}</li>
        </ul>
    </div>

    @if($HistorialTorneos != null && count($HistorialTorneos) > 0)
        <div class="section-title">Estadísticas Generales</div>
        <strong>Total de Torneos:</strong> {{ count($HistorialTorneos) }}
        <div class="section-title">Resumen de Torneos</div>
        <table class="summary-table">
            <thead>
                <tr>
                    <th>Torneo</th>
                    <th>Periodo</th>
                    <th>Categoría</th>
                    <th>Fase</th>
                </tr>
            </thead>
            <tbody>
                @foreach($HistorialTorneos as $torneo)
                    <tr>
                        <td>{{ $torneo['Torneo'] }}</td>
                        <td>{{ $torneo['Periodo'] }}</td>
                        <td>{{ $torneo['Categoria'] }}</td>
                        <td>{{ $torneo['Fase'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @foreach($TorneoDetalles as $index => $detalle)
            @if($index > 0)
                <div class="page-break"></div>
            @endif
            
            <div class="tournament-title">{{ $detalle['Torneo']->nombre }}, Categoría {{ $detalle['Categoria']->nombre }}</div>
            
            @if(count($detalle['Partidos']->whereNull('fase')) > 0)
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
                        @foreach($detalle['Partidos']->whereNull('fase') as $p)
                            <tr>
                                <td>{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }} vs {{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                                <td>{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                                <td>{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                                <td>{{ $p->resultado }}</td>

                                @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                    <td>-</td>
                                @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                                    <td>{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }}</td>
                                @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                                    <td>{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
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
                                        <td>{{ $detalle['TorneoCategoria']->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo }}</td>
                                    @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                                        <td>{{ $detalle['TorneoCategoria']->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo }}</td>
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

            @if(count($detalle['Partidos']->where('fase', 16)) > 0)
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
                        @foreach($detalle['Partidos']->where('fase', 16) as $p)
                            <tr>
                                <td>{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }} vs {{ $p->buy ? "BYE" : ($detalle['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-")) }}</td>
                                <td>{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                                <td>{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                                <td>{{ $p->resultado }}</td>

                                @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                    <td>-</td>
                                @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                                    <td>{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }}</td>
                                @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                                    <td>{{ $p->buy ? "BYE" :  ($detalle['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-")) }}</td>
                                @else
                                    <td>-</td>
                                @endif

                                <td>{{ $p->jugador_local_set }}</td>
                                <td>{{ $p->jugador_local_juego }}</td>

                                @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                    <td>-</td>
                                @elseif($p->jugador_local_uno_id != $p->jugador_ganador_uno_id)
                                    <td>{{ $detalle['TorneoCategoria']->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo }}</td>
                                @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                                    <td>{{ $p->buy ? "BYE" : ($detalle['TorneoCategoria']->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo) }}</td>
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

            @if(count($detalle['Partidos']->where('fase', 8)) > 0)
                <div class="section-title">Octavos de Final</div>
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
                        @foreach($detalle['Partidos']->where('fase', 8) as $p)
                            <tr>
                                <td>{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }} vs {{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                                <td>{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                                <td>{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                                <td>{{ $p->resultado }}</td>

                                @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                    <td>-</td>
                                @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                                    <td>{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }}</td>
                                @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                                    <td>{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                                @else
                                    <td>-</td>
                                @endif

                                <td>{{ $p->jugador_local_set }}</td>
                                <td>{{ $p->jugador_local_juego }}</td>

                                @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                    <td>-</td>
                                @elseif($p->jugador_local_uno_id != $p->jugador_ganador_uno_id)
                                    <td>{{ $detalle['TorneoCategoria']->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo }}</td>
                                @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                                    <td>{{ $detalle['TorneoCategoria']->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo }}</td>
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

            @if(count($detalle['Partidos']->where('fase', 4)) > 0)
                <div class="section-title">Cuartos de Final</div>
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
                        @foreach($detalle['Partidos']->where('fase', 4) as $p)
                            <tr>
                                <td>{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }} vs {{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                                <td>{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                                <td>{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                                <td>{{ $p->resultado }}</td>

                                @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                    <td>-</td>
                                @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                                    <td>{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }}</td>
                                @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                                    <td>{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                                @else
                                    <td>-</td>
                                @endif

                                <td>{{ $p->jugador_local_set }}</td>
                                <td>{{ $p->jugador_local_juego }}</td>

                                @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                    <td>-</td>
                                @elseif($p->jugador_local_uno_id != $p->jugador_ganador_uno_id)
                                    <td>{{ $detalle['TorneoCategoria']->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo }}</td>
                                @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                                    <td>{{ $detalle['TorneoCategoria']->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo }}</td>
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

            @if(count($detalle['Partidos']->where('fase', 2)) > 0)
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
                        @foreach($detalle['Partidos']->where('fase', 2) as $p)
                            <tr>
                                <td>{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }} vs {{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                                <td>{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                                <td>{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                                <td>{{ $p->resultado }}</td>

                                @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                    <td>-</td>
                                @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                                    <td>{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }}</td>
                                @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                                    <td>{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                                @else
                                    <td>-</td>
                                @endif

                                <td>{{ $p->jugador_local_set }}</td>
                                <td>{{ $p->jugador_local_juego }}</td>

                                @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                    <td>-</td>
                                @elseif($p->jugador_local_uno_id != $p->jugador_ganador_uno_id)
                                    <td>{{ $detalle['TorneoCategoria']->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo }}</td>
                                @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                                    <td>{{ $detalle['TorneoCategoria']->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo }}</td>
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

            @if(count($detalle['Partidos']->where('fase', 1)) > 0)
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
                        @foreach($detalle['Partidos']->where('fase', 1) as $p)
                            <tr>
                                <td>{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }} vs {{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                                <td>{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                                <td>{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                                <td>{{ $p->resultado }}</td>

                                @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                    <td>-</td>
                                @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                                    <td>{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }}</td>
                                @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                                    <td>{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                                @else
                                    <td>-</td>
                                @endif

                                <td>{{ $p->jugador_local_set }}</td>
                                <td>{{ $p->jugador_local_juego }}</td>

                                @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                    <td>-</td>
                                @elseif($p->jugador_local_uno_id != $p->jugador_ganador_uno_id)
                                    <td>{{ $detalle['TorneoCategoria']->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo }}</td>
                                @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                                    <td>{{ $detalle['TorneoCategoria']->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo }}</td>
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

            @if(!$detalle['Partidos'] || count($detalle['Partidos']) == 0)
                <div class="no-data">
                    No se encontraron partidos para este jugador en este torneo.
                </div>
            @endif
        @endforeach
    @else
        <div class="no-data">
            No se encontraron torneos para este jugador.
        </div>
    @endif

    <div class="footer">
        Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
