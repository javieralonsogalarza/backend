<?php
use App\Models\TorneoJugador;
use App\Models\Zona;
use App\Models\Grupo;
use App\Models\TorneoGrupo;
use App\Models\Partido;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

// ...existing code...

public function grupoStore(Request $request)
{
    $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null, 'Repeat' => false];

    try {
        DB::beginTransaction();

        $Validator = Validator::make($request->all(), [
            'torneo_id' => 'required',
            'torneo_categoria_id' => 'required',
            'tipo' => 'required'
        ]);

        if (!$Validator->fails()) {
            $TorneoCategoria = TorneoCategoria::where('id', $request->torneo_categoria_id)
                ->where('torneo_id', $request->torneo_id)
                ->whereHas('torneo', function ($q) {
                    $q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);
                })->first();

            $Torneo = $TorneoCategoria->torneo;

            if ($Torneo != null) {
                if (count($Torneo->partidos->where('torneo_categoria_id', $request->torneo_categoria_id)) <= 0) {
                    $Jugadores = $Torneo->torneoJugadors->where('torneo_categoria_id', $request->torneo_categoria_id);

                    if (count($Jugadores) < App::$PARTICIPANTES_MINIMOS_POR_TORNEO) {
                        $Result->Message = "Por favor, registre al menos " . (App::$PARTICIPANTES_MINIMOS_POR_TORNEO - count($Jugadores)) . " jugadores más para generar las llaves";
                        return response()->json($Result);
                    }

                    if (count($Jugadores) > App::$PARTICIPANTES_MAXIMOS_POR_TORNEO) {
                        $Result->Message = "Por favor, solo puede registrar como máximo " . App::$PARTICIPANTES_MAXIMOS_POR_TORNEO . " jugadores para generar las llaves";
                        return response()->json($Result);
                    }

                    $CantidadGrupos = (count($Jugadores) / 4);

                    $GruposDisponibles = Grupo::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->orderBy('nombre', 'asc')->get();

                    if (count($GruposDisponibles) < $CantidadGrupos) {
                        $Result->Message = "La cantidad de " . $CantidadGrupos . " grupos generados, supera a la cantidad de " . count($GruposDisponibles) . " grupos disponibles";
                        return response()->json($Result);
                    }

                    if ((count($Jugadores) % 4) == 0) {
                        if ($request->tipo == "select") {
                            // ...existing code for "select" type...
                        } else if ($request->tipo == "random") {
                            // ...existing code for "random" type...
                        } else if ($request->tipo == "zonas") {
                            // Obtener todos los jugadores y sus zonas
                            $Jugadores = $Torneo->torneoJugadors()->where('torneo_categoria_id', $request->torneo_categoria_id)->with('zonas')->get();

                            // Agrupar jugadores por zonas
                            $jugadoresPorZona = [];
                            foreach ($Jugadores as $jugador) {
                                foreach ($jugador->zonas as $zona) {
                                    $jugadoresPorZona[$zona->id][] = $jugador;
                                }
                            }

                            // Aplanar los jugadores agrupados por zonas en un solo array
                            $jugadoresOrdenados = [];
                            foreach ($jugadoresPorZona as $zonaId => $jugadores) {
                                $jugadoresOrdenados = array_merge($jugadoresOrdenados, $jugadores);
                            }

                            // Dividir los jugadores en grupos de 4
                            $gruposDeCuatro = array_chunk($jugadoresOrdenados, 4);

                            $Grupos = Grupo::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->orderBy('nombre', 'asc')->take(count($gruposDeCuatro))->get();

                            // CREACIÓN GRUPOS
                            foreach ($Grupos as $key => $q) {
                                foreach ($gruposDeCuatro[$key] as $jugador) {
                                    TorneoGrupo::create([
                                        'torneo_id' => $request->torneo_id,
                                        'torneo_categoria_id' => $request->torneo_categoria_id,
                                        'jugador_simple_id' => $jugador->jugador_simple_id,
                                        'jugador_dupla_id' => $TorneoCategoria->multiple ? $jugador->jugador_dupla_id : null,
                                        'grupo_id' => $q->id,
                                        'nombre_grupo' => $request->tipo_grupo_id != null && $request->tipo_grupo_id == App::$TIPO_GRUPO_NUMERO ? ('Grupo ' . ($key + 1)) : $q->nombre,
                                        'user_create_id' => Auth::guard('web')->user()->id
                                    ]);
                                }
                            }

                            // CREACIÓN PARTIDOS
                            foreach ($Grupos as $q) {
                                $TorneoGrupos = TorneoGrupo::where('torneo_id', $TorneoCategoria->torneo_id)
                                    ->where('torneo_categoria_id', $TorneoCategoria->id)
                                    ->where('grupo_id', $q->id)->get();

                                $CantidadPartidos = 0;

                                for ($i = (count($TorneoGrupos) - 1); $i >= 1; $i--) {
                                    $CantidadPartidos += $i;
                                }

                                if ($CantidadPartidos > 0) {
                                    $GruposCollect = collect($TorneoGrupos);

                                    $ArregloPartidos = [];

                                    foreach ($GruposCollect as $key2 => $q2) {
                                        foreach ($GruposCollect as $key3 => $q3) {
                                            if ($q2->jugador_simple_id != $q3->jugador_simple_id && $key3 > $key2) {
                                                $ArregloPartidos[] = [
                                                    'key' => $TorneoCategoria->multiple ? (($q2->jugador_simple_id . '-' . $q2->jugador_dupla_id) . '-' . ($q3->jugador_simple_id . '-' . $q3->jugador_dupla_id)) : ($q2->jugador_simple_id . '-' . $q3->jugador_simple_id),
                                                    'JugadorLocal' => $q2->jugador_simple_id,
                                                    'JugadorLocalDupla' => $q2->jugador_dupla_id,
                                                    'JugadorRival' => $q3->jugador_simple_id,
                                                    'JugadorRivalDupla' => $q3->jugador_dupla_id
                                                ];
                                            }
                                        }
                                    }

                                    $ArregloPartidosCollect = collect($ArregloPartidos);

                                    $MaximoPartidoPorJugador = max(array_count_values(collect($ArregloPartidos)->pluck('JugadorLocal')->toArray()));

                                    $PartidosModel = [];

                                    $PartidosModel[] = (object)$ArregloPartidosCollect->get(1);
                                    $PartidosModel[] = (object)$ArregloPartidosCollect->get($MaximoPartidoPorJugador + 1);
                                    $PartidosModel[] = (object)$ArregloPartidosCollect->get(0);
                                    $PartidosModel[] = (object)$ArregloPartidosCollect->last();
                                    $PartidosModel[] = (object)$ArregloPartidosCollect->get(2);
                                    $PartidosModel[] = (object)$ArregloPartidosCollect->get($MaximoPartidoPorJugador);

                                    if (count($PartidosModel) > 0) {
                                        $PartidosModelCollect = collect($PartidosModel)->shuffle();

                                        $PartidosModelWithDates = [];
                                        $fechaInicioPartido = Carbon::parse($Torneo->fecha_inicio);
                                        $fechaFinalPartido = Carbon::parse($Torneo->fecha_inicio)->addDay(6);

                                        for ($i = 0; $i < ($CantidadPartidos / 2); $i++) {
                                            if ($i >= 1) {
                                                $fechaInicioPartido = Carbon::parse($fechaFinalPartido)->addDay(1);
                                                $fechaFinalPartido = Carbon::parse($fechaInicioPartido)->addDay(6);
                                            }

                                            $FirstRoundMatchOne = $PartidosModelCollect->whereNotIn('key', collect($PartidosModelWithDates)->pluck('key')->toArray())->first();

                                            $PartidosModelWithDates[] = [
                                                'key' => $TorneoCategoria->multiple ? (($FirstRoundMatchOne->JugadorLocal . '-' . $FirstRoundMatchOne->JugadorLocalDupla) . '-' . ($FirstRoundMatchOne->JugadorRival . '-' . $FirstRoundMatchOne->JugadorRivalDupla)) : ($FirstRoundMatchOne->JugadorLocal . '-' . $FirstRoundMatchOne->JugadorRival),
                                                'JugadorLocal' => $FirstRoundMatchOne->JugadorLocal,
                                                'JugadorLocalDupla' => $FirstRoundMatchOne->JugadorLocalDupla,
                                                'JugadorRival' => $FirstRoundMatchOne->JugadorRival,
                                                'JugadorRivalDupla' => $FirstRoundMatchOne->JugadorRivalDupla,
                                                'FechaInicio' => $fechaInicioPartido->toDateString(),
                                                'FechaFinal' => $fechaFinalPartido->toDateString(),
                                            ];

                                            foreach ($PartidosModelCollect->whereNotIn('key', collect($PartidosModelWithDates)->pluck('key')->toArray()) as $q2) {
                                                if (!in_array($FirstRoundMatchOne->JugadorLocal, [$q2->JugadorLocal, $q2->JugadorRival]) &&
                                                    !in_array($FirstRoundMatchOne->JugadorRival, [$q2->JugadorLocal, $q2->JugadorRival])) {

                                                    $PartidosModelWithDates[] = [
                                                        'key' => $TorneoCategoria->multiple ? (($q2->JugadorLocal . '-' . $q2->JugadorLocalDupla) . '-' . ($q2->JugadorRival . '-' . $q2->JugadorRivalDupla)) : ($q2->JugadorLocal . '-' . $q2->JugadorRival),
                                                        'JugadorLocal' => $q2->JugadorLocal,
                                                        'JugadorLocalDupla' => $q2->JugadorLocalDupla,
                                                        'JugadorRival' => $q2->JugadorRival,
                                                        'JugadorRivalDupla' => $q2->JugadorRivalDupla,
                                                        'FechaInicio' => $fechaInicioPartido->toDateString(),
                                                        'FechaFinal' => $fechaFinalPartido->toDateString(),
                                                    ];
                                                    break;
                                                }
                                            }
                                        }

                                        foreach ($PartidosModelWithDates as $q2) {
                                            Partido::create([
                                                'comunidad_id' => Auth::guard('web')->user()->comunidad_id,
                                                'torneo_id' => $TorneoCategoria->torneo_id,
                                                'torneo_categoria_id' => $TorneoCategoria->id,
                                                'grupo_id' => $q->id,
                                                'jugador_local_uno_id' => $q2['JugadorLocal'],
                                                'jugador_local_dos_id' => $q2['JugadorLocalDupla'],
                                                'jugador_rival_uno_id' => $q2['JugadorRival'],
                                                'jugador_rival_dos_id' => $q2['JugadorRivalDupla'],
                                                'fecha_inicio' => $q2['FechaInicio'],
                                                'fecha_final' => $q2['FechaFinal'],
                                                'estado_id' => App::$ESTADO_PENDIENTE,
                                                'user_create_id' => Auth::guard('web')->user()->id,
                                            ]);
                                        }

                                        //Ultimo Torneo Jugado Para Comparar
                                        if (count($PartidosModelWithDates) > 0) {
                                            $UltimosTorneos = Torneo::orderBy('fecha_final', 'desc')->get()->map(function ($q) {
                                                return (object)['id' => $q->id, 'fecha_final' => $q->fecha_final, 'dias' => (Carbon::now()->diffInDays(Carbon::parse($q->fecha_final), false))];
                                            })->toArray();

                                            $UltimoTorneo = collect($UltimosTorneos)->where('dias', '<', 0)->sortByDesc('dias')->first();

                                            if ($UltimoTorneo != null && $UltimoTorneo->id != $Torneo->id) {
                                                $PartidosPasados = Partido::where('torneo_id', $UltimoTorneo->id)->whereNull('fase')->get()->map(function ($q) {
                                                    return [
                                                        'JugadorLocal' => $q->jugador_local_uno_id,
                                                        'JugadorLocalDupla' => $q->multiple ? $q->jugador_local_dos_id : null,
                                                        'JugadorRival' => $q->multiple ? $q->jugador_rival_uno_id : null,
                                                        'JugadorRivalDupla' => $q->jugador_rival_dos_id
                                                    ];
                                                });

                                                if (count($PartidosPasados) > 0) {
                                                    foreach ($PartidosPasados as $k) {
                                                        $FirstOldPartido = ['JugadorLocal' => $k['JugadorLocal'], 'JugadorLocalDupla' => $k['JugadorLocalDupla'], 'JugadorRival' => $k['JugadorRival'], 'JugadorRivalDupla' => $k['JugadorRivalDupla']];
                                                        foreach ($PartidosModelWithDates as $k2) {
                                                            $FirstNewPartido = ['JugadorLocal' => $k2['JugadorLocal'], 'JugadorLocalDupla' => $k2['JugadorLocalDupla'], 'JugadorRival' => $k2['JugadorRival'], 'JugadorRivalDupla' => $k2['JugadorRivalDupla']];
                                                            if (count(array_diff($FirstOldPartido, $FirstNewPartido)) > 0) {
                                                                $Result->Repeat = true;
                                                                break;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        TorneoCategoria::where('id', $TorneoCategoria->id)->where('torneo_id', $TorneoCategoria->torneo_id)
                                            ->whereHas('torneo', function ($q) {
                                                $q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);
                                            })
                                            ->update(['first_final' => false]);

                                        TorneoJugador::where('torneo_id', $TorneoCategoria->torneo_id)
                                            ->where('torneo_categoria_id', $TorneoCategoria->id)->update(['after' => false]);

                                        $Result->Success = true;

                                        DB::commit();
                                    }
                                }
                            }
                        } else {
                            $Result->Message = "Por favor, necesita agregar " . (4 - (count($Jugadores) % 4)) . " " . ((4 - (count($Jugadores) % 4)) == 1 ? "jugador" : "jugadores") . " más para generar las llaves";
                        }
                    } else {
                        //  $Result->Message = "Lo sentimos, las llaves ya fuerón asignadas y no puede volver a generarlas";
                    }
                } else {
                    $Result->Message = "El Torneo que intenta asignar grupos y partidos ya no se encuentra disponible";
                }
            }

            $Result->Errors = $Validator->errors();
        } catch (\Exception $e) {
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }
        return response()->json($Result);
    }