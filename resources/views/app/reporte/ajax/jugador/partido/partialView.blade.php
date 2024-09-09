@inject('Auth', '\Illuminate\Support\Facades\Auth')
@inject('App', 'App\Models\App')

<style type="text/css">
    .modal td{ font-size: 12px !important; text-align: center }
</style>

<div class="modal fade" role="dialog" tabindex="-1" data-backdrop="static" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ $Data != null ? "Partidos del Torneo ".$Data->Torneo->nombre.", Categoría ".$Data->Categoria->nombre : "Jugador no encontrado" }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group row">
                    <div class="col-md-12 text-center">
                        <h5>Jugador "{{ $Data->Jugador->nombre_completo }}"</h5>
                    </div>
                </div>

                @if(count($Data->Partidos->whereNull('fase')) > 0)
                <div class="form-group row">
                    <div class="col-md-12">
                        <h5 class="text-md">Fase de Grupos</h5>
                        <div class="table-responsive">
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
                                @foreach($Data->Partidos->whereNull('fase') as $p)
                                    <tr>
                                        <td class="text-center">{{ $Data->TorneoCategoria->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }} vs {{ $Data->TorneoCategoria->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                                        <td class="text-center">{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                                        <td class="text-center">{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                                        <td class="text-center">{{ $p->resultado }}</td>

                                        @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                            <td class="text-center">-</td>
                                        @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                                            <td class="text-center">{{ $Data->TorneoCategoria->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }}</td>
                                        @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                                            <td class="text-center">{{ $Data->TorneoCategoria->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
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
                                                <td class="text-center">{{ $Data->TorneoCategoria->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo }}</td>
                                            @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                                                <td class="text-center">{{ $Data->TorneoCategoria->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo }}</td>
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
                    </div>
                </div>
                @endif

                @if(count($Data->Partidos->where('fase', 16)) > 0)
                    <div class="mt-3">
                        <h5 class="text-md">Ronda de 32</h5>
                        <div class="table-responsive">
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
                                @foreach($Data->Partidos->where('fase', 16) as $p)
                                    <tr>
                                        <td class="text-center">{{ $Data->TorneoCategoria->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }} vs {{ $p->buy ? "BYE" : ($Data->TorneoCategoria->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-")) }}</td>
                                        <td class="text-center">{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                                        <td class="text-center">{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                                        <td class="text-center">{{ $p->resultado }}</td>

                                        @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                            <td></td>
                                        @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                                            <td>{{ $Data->TorneoCategoria->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }}</td>
                                        @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                                            <td>{{ $p->buy ? "BYE" :  ($Data->TorneoCategoria->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-")) }}</td>
                                        @else
                                            <td></td>
                                        @endif

                                        <td width="50" class="text-center">{{ $p->jugador_local_set }}</td>
                                        <td width="50" class="text-center">{{ $p->jugador_local_juego }}</td>

                                        @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                            <td></td>
                                        @elseif($p->jugador_local_uno_id != $p->jugador_ganador_uno_id)
                                            <td>{{ $Data->TorneoCategoria->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo }}</td>
                                        @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                                            <td>{{ $p->buy ? "BYE" : ($Data->TorneoCategoria->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo) }}</td>
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
                    </div>
                @endif

                @if(count($Data->Partidos->where('fase', 8)) > 0)
                    <div class="mt-3">
                        <h5 class="text-md">Octavos de final</h5>
                        <div class="table-responsive">
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
                                @foreach($Data->Partidos->where('fase', 8) as $p)
                                    <tr>
                                        <td class="text-center">{{ $Data->TorneoCategoria->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }} vs {{ $Data->TorneoCategoria->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                                        <td class="text-center">{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                                        <td class="text-center">{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                                        <td class="text-center">{{ $p->resultado }}</td>

                                        @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                            <td></td>
                                        @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                                            <td>{{ $Data->TorneoCategoria->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }}</td>
                                        @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                                            <td>{{ $p->buy ? "BYE" : ($Data->TorneoCategoria->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-")) }}</td>
                                        @else
                                            <td></td>
                                        @endif

                                        <td width="50" class="text-center">{{ $p->jugador_local_set }}</td>
                                        <td width="50" class="text-center">{{ $p->jugador_local_juego }}</td>

                                        @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                            <td></td>
                                        @elseif($p->jugador_local_uno_id != $p->jugador_ganador_uno_id)
                                            <td>{{ $Data->TorneoCategoria->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo }}</td>
                                        @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                                            <td>{{ $p->buy ? "BYE" : ($Data->TorneoCategoria->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo) }}</td>
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
                    </div>
                @endif

                @if(count($Data->Partidos->where('fase', 4)) > 0)
                    <div class="mt-3">
                        <h5 class="text-md">Cuartos de final</h5>
                        <div class="table-responsive">
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
                                @foreach($Data->Partidos->where('fase', 4) as $p)
                                    <tr>
                                        <td class="text-center">{{ $Data->TorneoCategoria->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }} vs {{ $p->buy ? "BYE" : ($Data->TorneoCategoria->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-")) }}</td>
                                        <td class="text-center">{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                                        <td class="text-center">{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                                        <td class="text-center">{{ $p->resultado }}</td>

                                        @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                            <td></td>
                                        @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                                            <td>{{ $Data->TorneoCategoria->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }}</td>
                                        @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                                            <td>{{ $p->buy ? "BYE" : ($Data->TorneoCategoria->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-")) }}</td>
                                        @else
                                            <td></td>
                                        @endif

                                        <td width="50" class="text-center">{{ $p->jugador_local_set }}</td>
                                        <td width="50" class="text-center">{{ $p->jugador_local_juego }}</td>

                                        @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                            <td></td>
                                        @elseif($p->jugador_local_uno_id != $p->jugador_ganador_uno_id)
                                            <td>{{ $Data->TorneoCategoria->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo }}</td>
                                        @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                                            <td>{{ $p->buy ? "BYE" : ($Data->TorneoCategoria->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo) }}</td>
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
                    </div>
                @endif

                @if(count($Data->Partidos->where('fase', 2)) > 0)
                    <div class="mt-3">
                        <h5 class="text-md">Semifinal</h5>
                        <div class="table-responsive">
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
                                @foreach($Data->Partidos->where('fase', 2) as $p)
                                    <tr>
                                        <td class="text-center">{{ $Data->TorneoCategoria->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }} vs {{ $Data->TorneoCategoria->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                                        <td class="text-center">{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                                        <td class="text-center">{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                                        <td class="text-center">{{ $p->resultado }}</td>

                                        @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                            <td></td>
                                        @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                                            <td>{{ $Data->TorneoCategoria->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }}</td>
                                        @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                                            <td>{{ $Data->TorneoCategoria->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                                        @else
                                            <td></td>
                                        @endif

                                        <td width="50" class="text-center">{{ $p->jugador_local_set }}</td>
                                        <td width="50" class="text-center">{{ $p->jugador_local_juego }}</td>

                                        @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                            <td></td>
                                        @elseif($p->jugador_local_uno_id != $p->jugador_ganador_uno_id)
                                            <td>{{ $Data->TorneoCategoria->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo }}</td>
                                        @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                                            <td>{{ $Data->TorneoCategoria->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo }}</td>
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
                    </div>
                @endif

                @if(count($Data->Partidos->where('fase', 1)) > 0)
                    <div class="mt-3">
                        <h5 class="text-md">Final</h5>
                        <div class="table-responsive">
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
                                @foreach($Data->Partidos->where('fase', 1) as $p)
                                    <tr>
                                        <td class="text-center">{{ $Data->TorneoCategoria->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }} vs {{ $Data->TorneoCategoria->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                                        <td class="text-center">{{ $p->fecha_inicio != null ? \Carbon\Carbon::parse($p->fecha_inicio)->format('Y-m-d') : "-" }}</td>
                                        <td class="text-center">{{ $p->fecha_final != null ? \Carbon\Carbon::parse($p->fecha_final)->format('Y-m-d') : "-" }}</td>
                                        <td class="text-center">{{ $p->resultado }}</td>

                                        @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                            <td></td>
                                        @elseif($p->jugador_local_uno_id == $p->jugador_ganador_uno_id)
                                            <td>{{ $Data->TorneoCategoria->multiple ? (($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-").' + '.($p->jugadorLocalDos != null ? $p->jugadorLocalDos->nombre_completo : "-")) : ($p->jugadorLocalUno != null ? $p->jugadorLocalUno->nombre_completo : "-") }}</td>
                                        @elseif($p->jugador_rival_uno_id == $p->jugador_ganador_uno_id)
                                            <td>{{ $Data->TorneoCategoria->multiple ? (($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-").' + '.($p->jugadorRivalDos != null ? $p->jugadorRivalDos->nombre_completo : "-")) : ($p->jugadorRivalUno != null ? $p->jugadorRivalUno->nombre_completo : "-") }}</td>
                                        @else
                                            <td></td>
                                        @endif

                                        <td width="50" class="text-center">{{ $p->jugador_local_set }}</td>
                                        <td width="50" class="text-center">{{ $p->jugador_local_juego }}</td>

                                        @if($p->estado_id == $App::$ESTADO_PENDIENTE)
                                            <td></td>
                                        @elseif($p->jugador_local_uno_id != $p->jugador_ganador_uno_id)
                                            <td>{{ $Data->TorneoCategoria->multiple ? ($p->jugadorLocalUno->nombre_completo.' + '.$p->jugadorLocalDos->nombre_completo) : $p->jugadorLocalUno->nombre_completo }}</td>
                                        @elseif($p->jugador_rival_uno_id != $p->jugador_ganador_uno_id)
                                            <td>{{ $Data->TorneoCategoria->multiple ? ($p->jugadorRivalUno->nombre_completo.' + '.$p->jugadorRivalDos->nombre_completo) : $p->jugadorRivalUno->nombre_completo }}</td>
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
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
