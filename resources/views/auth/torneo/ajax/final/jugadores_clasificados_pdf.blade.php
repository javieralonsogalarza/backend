@inject('Auth', '\\Illuminate\\Support\\Facades\\Auth')
@inject('App', 'App\\Models\\App')
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jugadores Clasificados y No Clasificados</title>
    <link rel="stylesheet" href="{{ asset('https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css') }}" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style>
        thead th{ background-color: {{ $Auth::guard('web')->user()->comunidad != null ? $Auth::guard('web')->user()->comunidad->color_primario : "#000000" }}; color: #ffffff; }
        thead th, tbody td{ font-size: {{ $TorneoFaseFinal->TorneoCategoria->multiple ? "9px" : "11px"}} !important; padding: 5px !important; text-align: center; }
        .page-break { page-break-after: always; }
        .bg-blue { background-color: #0101be !important; color: #fff !important; }
        .bg-red { background-color: #c40a0a !important; color: #fff !important; }
    </style>
</head>
<body>
    <header class="mb-2">
        <h3 class="text-center mb-1">{{ $TorneoFaseFinal->TorneoCategoria->torneo->nombre }}</h3>
        <div class="text-center">
            <strong>Categoría:</strong> {{ $TorneoFaseFinal->TorneoCategoria->categoriaSimple->nombre }}
            &nbsp;|&nbsp;
            <strong>Fecha:</strong> {{ date('d/m/Y') }}
        </div>
    </header>

    <h5>Jugadores Clasificados</h5>
    <table class="table table-bordered table-striped mb-0">
        <thead>
            <tr>
                <th class="align-middle text-center" align="center"></th>
                <th class="align-middle text-center" align="center">Jugadores</th>
                <th class="align-middle text-center" align="center">Set Ganados</th>
                <th class="align-middle text-center" align="center">Set Perdidos</th>
                <th class="align-middle text-center bg-blue" align="center">Diferencia Sets</th>
                <th class="align-middle text-center" align="center">Games Ganados</th>
                <th class="align-middle text-center" align="center">Games Perdidos</th>
                <th class="align-middle text-center bg-blue" align="center">Diferencia Games</th>
                <th class="align-middle text-center bg-red" align="center">Puntos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($TorneoFaseFinal->JugadoresClasificados as $i => $q)
                <tr>
                    <td class="align-middle text-center" align="center">{{ $i+1 }}</td>
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

    <div class="page-break"></div>

    <header class="mb-2"></header>
        <h3 class="text-center mb-1">{{ $TorneoFaseFinal->TorneoCategoria->torneo->nombre }}</h3>
        <div class="text-center">
            <strong>Categoría:</strong> {{ $TorneoFaseFinal->TorneoCategoria->categoriaSimple->nombre }}
            &nbsp;|&nbsp;
            <strong>Fecha:</strong> {{ date('d/m/Y') }}
        </div>
    </header>

    <h5>Jugadores No Clasificados</h5>
    <table class="table table-bordered table-striped mb-0">
        <thead>
            <tr>
                <th class="align-middle text-center" align="center"></th>
                <th class="align-middle text-center" align="center">Jugadores</th>
                <th class="align-middle text-center" align="center">Set Ganados</th>
                <th class="align-middle text-center" align="center">Set Perdidos</th>
                <th class="align-middle text-center bg-blue" align="center">Diferencia Sets</th>
                <th class="align-middle text-center" align="center">Games Ganados</th>
                <th class="align-middle text-center" align="center">Games Perdidos</th>
                <th class="align-middle text-center bg-blue" align="center">Diferencia Games</th>
                <th class="align-middle text-center bg-red" align="center">Puntos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($TorneoFaseFinal->JugadoresNoClasificados as $i => $q)
                <tr>
                    <td class="align-middle text-center" align="center">{{ $i+1 }}</td>
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
</body>
</html>
