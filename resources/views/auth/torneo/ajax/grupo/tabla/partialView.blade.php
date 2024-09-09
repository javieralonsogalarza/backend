<div class="d-flex justify-content-between align-items-center">
    <div><h5>Tabla de Posiciones</h5></div>
</div>
<div class="row mt-1">
    <div class="col-md-12">
        <div class="table-responsive">
            <table class="mt-2 table table-bordered table-partidos-score table-striped">
                <thead>
                <tr>
                    <th class="align-middle text-center" align="center">Jugadores</th>
                    <th class="align-middle text-center" align="center">Partidos Jugados</th>
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
                @foreach($TablaPosiciones as $key => $q)
                    <tr>
                        <td class="align-middle text-center" align="center">{{ $q['nombres'] }}</td>
                        <td class="align-middle text-center" align="center">{{ $q['partidosJugados'] }}</td>
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
