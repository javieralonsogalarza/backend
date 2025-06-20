@inject('Auth', '\Illuminate\Support\Facades\Auth')
@inject('App', 'App\Models\App')
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reporte Completo de Jugador</title>    <link rel="stylesheet" href="{{ asset('https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css') }}" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style type="text/css">
        thead th{ background-color: #00b7f1; color: #ffffff; }
        thead th, tbody td{ font-size: 11px !important; padding: 5px !important; text-align: center; }
        h5{ margin-bottom: 20px }
        table > thead:first-of-type > tr:last-child {page-break-after: avoid;}
        .page-break {page-break-before: always;}
        .hidden{ display: none}
        .player-photo img {
            width: 130px;
            height: 130px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #dee2e6;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <div class="row">
        <div class="col-md-12 text-center">
            <h3>Reporte Completo de Jugador: {{ $Jugador->nombre_completo }}</h3>
        </div>
    </div>
    
    <div class="row mt-2">
        <div class="col-md-8">
            <ul style="list-style: none; padding: 0;">
                <li><strong>Tipo Documento:</strong> {{ $Jugador->tipoDocumento != null ? $Jugador->tipoDocumento->nombre : "-" }}</li>
                <li><strong>N° Documento:</strong> {{ $Jugador->nro_documento }}</li>
                <li><strong>Celular:</strong> {{ $Jugador->celular != null && $Jugador->celular != "" ? $Jugador->celular : "-" }}</li>
                <li><strong>Edad:</strong> {{ $Jugador->edad != null && $Jugador->edad != "" ? $Jugador->edad." años" : "-"}}</li>
                <li><strong>Altura:</strong> {{ $Jugador->altura != null && $Jugador->altura != "" ? $Jugador->altura."m" : "-"}}</li>
                <li><strong>Peso:</strong> {{ $Jugador->peso != null && $Jugador->peso != "" ? $Jugador->peso."kg" : "-"}}</li>
            </ul>
        </div>
        <div class="col-md-4 text-center">
            <div class="player-photo">
                @php
                    $fotoJugador = public_path("upload/image/default.png"); // Imagen por defecto
                    if($Jugador != null) {
                        // Verificar en storage/app/public/uploads/img/
                        $rutaStorage = storage_path("app/public/uploads/img/jugador_{$Jugador->id}.png");
                        if(file_exists($rutaStorage)) {
                            $fotoJugador = $rutaStorage;
                        } else {
                            // Verificar en public/uploads/img/ como alternativa
                            $rutaPublic = public_path("uploads/img/jugador_{$Jugador->id}.png");
                            if(file_exists($rutaPublic)) {
                                $fotoJugador = $rutaPublic;
                            }
                        }
                    }
                @endphp
                <img src="{{ $fotoJugador }}" alt="{{ $Jugador != null ? $Jugador->nombre_completo : 'Jugador' }}">
            </div>
        </div>
    </div>

    @if($HistorialTorneos != null && count($HistorialTorneos) > 0)
        <div class="mt-3">
            <h5>Estadísticas Generales - Total de Torneos: {{ count($HistorialTorneos) }}</h5>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th class="align-middle text-center">Torneo</th>
                        <th class="align-middle text-center">Periodo</th>
                        <th class="align-middle text-center">Categoría</th>
                        <th class="align-middle text-center">Fase</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($HistorialTorneos as $torneo)
                        <tr>
                            <td class="text-center">{{ $torneo['Torneo'] }}</td>
                            <td class="text-center">{{ $torneo['Periodo'] }}</td>
                            <td class="text-center">{{ $torneo['Categoria'] }}</td>
                            <td class="text-center">{{ $torneo['Fase'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>        @foreach($TorneoDetalles as $index => $detalle)
            @if($index > 0)
                <div class="page-break"></div>
            @endif
            
            <div class="mt-3">
                <h5>{{ $detalle['Torneo']->nombre }}, Categoría {{ $detalle['Categoria']->nombre }}</h5>
            </div>
            
            @if(count($detalle['Partidos']->whereNull('fase')) > 0)
                <div class="mt-3">
                    <h5>Fase de Grupos</h5>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th class="align-middle text-center" rowspan="2">Partidos</th>
                                <th colspan="2" class="align-middle text-center">Plazo de Juego</th>
                                <th rowspan="2" class="align-middle text-center">Resultado</th>
                                <th colspan="3" class="align-middle text-center">Ganador</th>
                                <th colspan="3" class="align-middle text-center">Rival</th>
                            </tr>
                            <tr>
                                <th class="align-middle text-center">Fecha Inicio</th>
                                <th class="align-middle text-center">Fecha Final</th>
                                <th class="align-middle text-center">Jugador</th>
                                <th width="30" class="align-middle text-center">Sets</th>
                                <th width="30" class="align-middle text-center">Games</th>
                                <th class="align-middle text-center">Jugador</th>
                                <th width="30" class="align-middle text-center">Sets</th>
                                <th width="30" class="align-middle text-center">Games</th>
                            </tr>
                        </thead>                        <tbody>
                            @foreach($detalle['Partidos']->whereNull('fase') as $p)
                                <tr>
                                    <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }} vs {{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                                    <td class="text-center">{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                                    <td class="text-center">{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                                    <td class="text-center">{{ $p->resultado }}</td>

                                    @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                        <td class="text-center">-</td>
                                    @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                                        <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }}</td>
                                    @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                                        <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
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
                                            <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo }}</td>
                                        @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                                            <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo }}</td>
                                        @else
                                            <td class="text-center">-</td>
                                        @endif
                                    @endif

                                    <td width="50" class="text-center">{{ $p->jugador_rival_set }}</td>
                                    <td width="50" class="text-center">{{ $p->jugador_rival_juego }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif            @if(count($detalle['Partidos']->where('fase', 16)) > 0)
                <div class="mt-3">
                    <h5>Ronda 32</h5>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th class="align-middle text-center" rowspan="2">Partidos</th>
                                <th colspan="2" class="align-middle text-center">Plazo de Juego</th>
                                <th rowspan="2" class="align-middle text-center">Resultado</th>
                                <th colspan="3" class="align-middle text-center">Ganador</th>
                                <th colspan="3" class="align-middle text-center">Rival</th>
                            </tr>
                            <tr>
                                <th class="align-middle text-center">Fecha Inicio</th>
                                <th class="align-middle text-center">Fecha Final</th>
                                <th class="align-middle text-center">Jugador</th>
                                <th width="30" class="align-middle text-center">Sets</th>
                                <th width="30" class="align-middle text-center">Games</th>
                                <th class="align-middle text-center">Jugador</th>
                                <th width="30" class="align-middle text-center">Sets</th>
                                <th width="30" class="align-middle text-center">Games</th>
                            </tr>
                        </thead>                        <tbody>
                            @foreach($detalle['Partidos']->where('fase', 16) as $p)
                                <tr>
                                    <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }} vs {{ $p->buy ? "BYE" : ($detalle['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-")) }}</td>
                                    <td class="text-center">{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                                    <td class="text-center">{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                                    <td class="text-center">{{ $p->resultado }}</td>

                                    @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                        <td class="text-center">-</td>
                                    @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                                        <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }}</td>
                                    @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                                        <td class="text-center">{{ $p->buy ? "BYE" :  ($detalle['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-")) }}</td>
                                    @else
                                        <td class="text-center">-</td>
                                    @endif

                                    <td width="50" class="text-center">{{ $p->jugador_local_set }}</td>
                                    <td width="50" class="text-center">{{ $p->jugador_local_juego }}</td>

                                    @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                        <td class="text-center">-</td>
                                    @elseif($p->jugador_local_uno_id != $p->jugador_ganador_uno_id)
                                        <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo }}</td>
                                    @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                                        <td class="text-center">{{ $p->buy ? "BYE" : ($detalle['TorneoCategoria']->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo) }}</td>
                                    @else
                                        <td class="text-center">-</td>
                                    @endif

                                    <td width="50" class="text-center">{{ $p->jugador_rival_set }}</td>
                                    <td width="50" class="text-center">{{ $p->jugador_rival_juego }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif            @if(count($detalle['Partidos']->where('fase', 8)) > 0)
                <div class="mt-3">
                    <h5>Octavos de Final</h5>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th class="align-middle text-center" rowspan="2">Partidos</th>
                                <th colspan="2" class="align-middle text-center">Plazo de Juego</th>
                                <th rowspan="2" class="align-middle text-center">Resultado</th>
                                <th colspan="3" class="align-middle text-center">Ganador</th>
                                <th colspan="3" class="align-middle text-center">Rival</th>
                            </tr>
                            <tr>
                                <th class="align-middle text-center">Fecha Inicio</th>
                                <th class="align-middle text-center">Fecha Final</th>
                                <th class="align-middle text-center">Jugador</th>
                                <th width="30" class="align-middle text-center">Sets</th>
                                <th width="30" class="align-middle text-center">Games</th>
                                <th class="align-middle text-center">Jugador</th>
                                <th width="30" class="align-middle text-center">Sets</th>
                                <th width="30" class="align-middle text-center">Games</th>
                            </tr>
                        </thead>                        <tbody>
                            @foreach($detalle['Partidos']->where('fase', 8) as $p)
                                <tr>
                                    <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }} vs {{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                                    <td class="text-center">{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                                    <td class="text-center">{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                                    <td class="text-center">{{ $p->resultado }}</td>

                                    @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                        <td class="text-center">-</td>
                                    @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                                        <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }}</td>
                                    @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                                        <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                                    @else
                                        <td class="text-center">-</td>
                                    @endif

                                    <td width="50" class="text-center">{{ $p->jugador_local_set }}</td>
                                    <td width="50" class="text-center">{{ $p->jugador_local_juego }}</td>

                                    @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                        <td class="text-center">-</td>
                                    @elseif($p->jugador_local_uno_id != $p->jugador_ganador_uno_id)
                                        <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo }}</td>
                                    @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                                        <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo }}</td>
                                    @else
                                        <td class="text-center">-</td>
                                    @endif

                                    <td width="50" class="text-center">{{ $p->jugador_rival_set }}</td>
                                    <td width="50" class="text-center">{{ $p->jugador_rival_juego }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if(count($detalle['Partidos']->where('fase', 4)) > 0)
                <div class="mt-3">
                    <h5>Cuartos de Final</h5>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th class="align-middle text-center" rowspan="2">Partidos</th>
                                <th colspan="2" class="align-middle text-center">Plazo de Juego</th>
                                <th rowspan="2" class="align-middle text-center">Resultado</th>
                                <th colspan="3" class="align-middle text-center">Ganador</th>
                                <th colspan="3" class="align-middle text-center">Rival</th>
                            </tr>
                            <tr>
                                <th class="align-middle text-center">Fecha Inicio</th>
                                <th class="align-middle text-center">Fecha Final</th>
                                <th class="align-middle text-center">Jugador</th>
                                <th width="30" class="align-middle text-center">Sets</th>
                                <th width="30" class="align-middle text-center">Games</th>
                                <th class="align-middle text-center">Jugador</th>
                                <th width="30" class="align-middle text-center">Sets</th>
                                <th width="30" class="align-middle text-center">Games</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detalle['Partidos']->where('fase', 4) as $p)
                                <tr>
                                    <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }} vs {{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                                    <td class="text-center">{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                                    <td class="text-center">{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                                    <td class="text-center">{{ $p->resultado }}</td>

                                    @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                        <td class="text-center">-</td>
                                    @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                                        <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }}</td>
                                    @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                                        <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                                    @else
                                        <td class="text-center">-</td>
                                    @endif

                                    <td width="50" class="text-center">{{ $p->jugador_local_set }}</td>
                                    <td width="50" class="text-center">{{ $p->jugador_local_juego }}</td>

                                    @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                        <td class="text-center">-</td>
                                    @elseif($p->jugador_local_uno_id != $p->jugador_ganador_uno_id)
                                        <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo }}</td>
                                    @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                                        <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo }}</td>
                                    @else
                                        <td class="text-center">-</td>
                                    @endif

                                    <td width="50" class="text-center">{{ $p->jugador_rival_set }}</td>
                                    <td width="50" class="text-center">{{ $p->jugador_rival_juego }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if(count($detalle['Partidos']->where('fase', 2)) > 0)
                <div class="mt-3">
                    <h5>Semifinal</h5>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th class="align-middle text-center" rowspan="2">Partidos</th>
                                <th colspan="2" class="align-middle text-center">Plazo de Juego</th>
                                <th rowspan="2" class="align-middle text-center">Resultado</th>
                                <th colspan="3" class="align-middle text-center">Ganador</th>
                                <th colspan="3" class="align-middle text-center">Rival</th>
                            </tr>
                            <tr>
                                <th class="align-middle text-center">Fecha Inicio</th>
                                <th class="align-middle text-center">Fecha Final</th>
                                <th class="align-middle text-center">Jugador</th>
                                <th width="30" class="align-middle text-center">Sets</th>
                                <th width="30" class="align-middle text-center">Games</th>
                                <th class="align-middle text-center">Jugador</th>
                                <th width="30" class="align-middle text-center">Sets</th>
                                <th width="30" class="align-middle text-center">Games</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detalle['Partidos']->where('fase', 2) as $p)
                                <tr>
                                    <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }} vs {{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                                    <td class="text-center">{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                                    <td class="text-center">{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                                    <td class="text-center">{{ $p->resultado }}</td>

                                    @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                        <td class="text-center">-</td>
                                    @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                                        <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }}</td>
                                    @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                                        <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                                    @else
                                        <td class="text-center">-</td>
                                    @endif

                                    <td width="50" class="text-center">{{ $p->jugador_local_set }}</td>
                                    <td width="50" class="text-center">{{ $p->jugador_local_juego }}</td>

                                    @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                        <td class="text-center">-</td>
                                    @elseif($p->jugador_local_uno_id != $p->jugador_ganador_uno_id)
                                        <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo }}</td>
                                    @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                                        <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo }}</td>
                                    @else
                                        <td class="text-center">-</td>
                                    @endif

                                    <td width="50" class="text-center">{{ $p->jugador_rival_set }}</td>
                                    <td width="50" class="text-center">{{ $p->jugador_rival_juego }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if(count($detalle['Partidos']->where('fase', 1)) > 0)
                <div class="mt-3">
                    <h5>Final</h5>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th class="align-middle text-center" rowspan="2">Partidos</th>
                                <th colspan="2" class="align-middle text-center">Plazo de Juego</th>
                                <th rowspan="2" class="align-middle text-center">Resultado</th>
                                <th colspan="3" class="align-middle text-center">Ganador</th>
                                <th colspan="3" class="align-middle text-center">Rival</th>
                            </tr>
                            <tr>
                                <th class="align-middle text-center">Fecha Inicio</th>
                                <th class="align-middle text-center">Fecha Final</th>
                                <th class="align-middle text-center">Jugador</th>
                                <th width="30" class="align-middle text-center">Sets</th>
                                <th width="30" class="align-middle text-center">Games</th>
                                <th class="align-middle text-center">Jugador</th>
                                <th width="30" class="align-middle text-center">Sets</th>
                                <th width="30" class="align-middle text-center">Games</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detalle['Partidos']->where('fase', 1) as $p)
                                <tr>
                                    <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }} vs {{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                                    <td class="text-center">{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                                    <td class="text-center">{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                                    <td class="text-center">{{ $p->resultado }}</td>

                                    @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                        <td class="text-center">-</td>
                                    @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                                        <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }}</td>
                                    @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                                        <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                                    @else
                                        <td class="text-center">-</td>
                                    @endif

                                    <td width="50" class="text-center">{{ $p->jugador_local_set }}</td>
                                    <td width="50" class="text-center">{{ $p->jugador_local_juego }}</td>

                                    @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                        <td class="text-center">-</td>
                                    @elseif($p->jugador_local_uno_id != $p->jugador_ganador_uno_id)
                                        <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo }}</td>
                                    @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                                        <td class="text-center">{{ $detalle['TorneoCategoria']->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo }}</td>
                                    @else
                                        <td class="text-center">-</td>
                                    @endif

                                    <td width="50" class="text-center">{{ $p->jugador_rival_set }}</td>
                                    <td width="50" class="text-center">{{ $p->jugador_rival_juego }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if(!$detalle['Partidos'] || count($detalle['Partidos']) == 0)
                <div class="text-center mt-4" style="font-style: italic; color: #6c757d;">
                    No se encontraron partidos para este jugador en este torneo.
                </div>
            @endif
        @endforeach
    @else
        <div class="text-center mt-4" style="font-style: italic; color: #6c757d;">
            No se encontraron torneos para este jugador.
        </div>
    @endif    <div class="text-center mt-4" style="font-size: 10px; color: #6c757d;">
        Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
