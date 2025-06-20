<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Jugador;
use App\Models\Partido;
use App\Models\Torneo;
use App\Models\TorneoCategoria;
use App\Models\TorneoGrupo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Comunidad;
use App\Models\Categoria;
use App\Models\Ranking;
use App\Models\RankingDetalle;
use App\Models\TorneoJugador;


class ReporteController extends Controller
{
    protected $viewName = 'reporte';

    public function jugador()
    {
        $Jugadores = Jugador::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->orderBy('nombres')->get();
        return view('auth'.'.'.$this->viewName.'.jugador', ['Jugadores' => $Jugadores, 'ViewName' => ucfirst($this->viewName)]);
    }


public function jugadorPartialView(Request $request)
{
    $Jugador = Jugador::where('id', $request->filter_jugador)
    ->where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->first();

    $Torneos = Torneo::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->get();

    $HistorialTorneos = [];
    $categoriaSimpleIds = [];
    $categoriaSimpleComplete = [];
    
    if($Jugador != null && count($Torneos) > 0)
    {       
        foreach ($Torneos as $q) {
            $inscrito = TorneoJugador::where('torneo_id', $q->id)
                ->where('jugador_simple_id', $Jugador->id)
                ->count();
                
            $Partidos = Partido::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
                ->where('torneo_id', $q->id)
                ->where(function ($query) use ($Jugador) {
                    $query->where('jugador_local_uno_id', $Jugador->id)
                        ->orWhere('jugador_local_dos_id', $Jugador->id)
                        ->orWhere('jugador_rival_uno_id', $Jugador->id)
                        ->orWhere('jugador_rival_dos_id', $Jugador->id);
                })
                ->orderBy('id', 'desc')
                ->get();
        
            // Si tiene partidos, procesar como antes
            if(count($Partidos) > 0) {
                foreach ($Partidos as $Partido) {
                    $PartidoFaseNext = $Partido->fase == null ? null :  ($Partido->fase == 1 ? 1 : ($Partido->fase/2));

                    $PartidoNext = null;

                    if($Partido->estado_id == App::$ESTADO_FINALIZADO)
                    {
                        $PartidoNext = Partido::where('comunidad_id',Auth::guard('web')->user()->comunidad_id)->where
                        ('torneo_id', $q->id)
                            ->where('torneo_categoria_id', $Partido->torneo_categoria_id)->where('fase', $PartidoFaseNext)
                            ->where('estado_id', App::$ESTADO_PENDIENTE)
                            ->where(function ($q) use ($Jugador){
                                $q->where('jugador_local_uno_id', $Jugador->id)
                                ->orWhere('jugador_local_dos_id', $Jugador->id)
                                ->orWhere('jugador_rival_uno_id', $Jugador->id)
                                ->orWhere('jugador_rival_dos_id', $Jugador->id);
                            })
                            ->orderBy('id', 'desc')->first();
                    }
                    
                    $HistorialTorneos[] = [
                        'id' => $q->id,
                        'Torneo' => $q->nombre,
                        'TorneoCategoria' => $Partido->torneoCategoria,
                        'Periodo' => ($q->fecha_inicio_texto . " - " . $q->fecha_final_texto),
                        'Categoria' => $q->multiple && ($Partido->torneoCategoria->categoria_simple_id !== $Partido->torneoCategoria->categoria_dupla_id) ? (($Partido->torneoCategoria->categoriaSimple != null ? $Partido->torneoCategoria->categoriaSimple->nombre : "-") . " + " . ($Partido->torneoCategoria->categoriaDupla != null ? $Partido->torneoCategoria->categoriaDupla->nombre : "-")) : ($Partido->torneoCategoria->categoriaSimple != null ? $Partido->torneoCategoria->categoriaSimple->nombre : "-") . "" . ($q->multiple ? " (Doble) " : ""),
                        'Fase' => $Partido->fase == null ? "Fase de Grupos" : ($Partido->fase == 16 ? "Deciseisavo de Final" : ($Partido->fase == 8 ? "Octavos de Final" : ($Partido->fase == 4 ? "Cuartos de Final" : ($Partido->fase == 2 ? "Semifinal" : ($Partido->fase == 1 ? ((in_array($Jugador->id, [$Partido->jugador_ganador_uno_id, $Partido->jugador_ganador_dos_id]) ? "Campeón" : "Finalista")) : "-"))))),
                        'Estado' => in_array($q->estado_id, [App::$ESTADO_CANCELADO, App::$ESTADO_FINALIZADO]) ? 'Participación terminada' : ($Partido->estado_id == App::$ESTADO_PENDIENTE ? 'Participación en curso' : ($PartidoNext == null ? 'Participación terminada' : ($PartidoNext->estado_id == App::$ESTADO_PENDIENTE ? 'Participación curso' : 'Participación terminada') ))
                    ];
            
                    if (isset($Partido->torneoCategoria->categoria_simple_id)) {
                        if (!in_array($Partido->torneoCategoria->categoria_simple_id, $categoriaSimpleIds)) {
                            $categoriaSimpleIds[] = $Partido->torneoCategoria->categoria_simple_id;
                            $categoriaSimpleComplete[] = $Partido->torneoCategoria->categoriaSimple;
                        }
                    }
                }
            }
            // Si está inscrito pero no tiene partidos, agregar al historial
            else if($inscrito > 0) {
                // Obtener las categorías del torneo en las que está inscrito
                $torneoJugadores = TorneoJugador::where('torneo_id', $q->id)
                    ->where('jugador_simple_id', $Jugador->id)
                    ->with('torneoCategoria.categoriaSimple', 'torneoCategoria.categoriaDupla')
                    ->get();
                
                foreach($torneoJugadores as $torneoJugador) {
                    $HistorialTorneos[] = [
                        'id' => $q->id,
                        'Torneo' => $q->nombre,
                        'TorneoCategoria' => $torneoJugador->torneoCategoria,
                        'Periodo' => ($q->fecha_inicio_texto . " - " . $q->fecha_final_texto),
                        'Categoria' => $q->multiple && ($torneoJugador->torneoCategoria->categoria_simple_id !== $torneoJugador->torneoCategoria->categoria_dupla_id) ? (($torneoJugador->torneoCategoria->categoriaSimple != null ? $torneoJugador->torneoCategoria->categoriaSimple->nombre : "-") . " + " . ($torneoJugador->torneoCategoria->categoriaDupla != null ? $torneoJugador->torneoCategoria->categoriaDupla->nombre : "-")) : ($torneoJugador->torneoCategoria->categoriaSimple != null ? $torneoJugador->torneoCategoria->categoriaSimple->nombre : "-") . "" . ($q->multiple ? " (Doble) " : ""),
                        'Fase' => "Sin partidos jugados",
                        'Estado' => in_array($q->estado_id, [App::$ESTADO_CANCELADO, App::$ESTADO_FINALIZADO]) ? 'Inscrito (Torneo terminado)' : 'Inscrito'
                    ];
                    
                    // Agregar categoría si no existe
                    if (isset($torneoJugador->torneoCategoria->categoria_simple_id)) {
                        if (!in_array($torneoJugador->torneoCategoria->categoria_simple_id, $categoriaSimpleIds)) {
                            $categoriaSimpleIds[] = $torneoJugador->torneoCategoria->categoria_simple_id;
                            $categoriaSimpleComplete[] = $torneoJugador->torneoCategoria->categoriaSimple;
                        }
                    }
                }
            }
        }   
        
        $ultimosPartidosPorCategoria = [];

        foreach ($HistorialTorneos as $partido) {
            $categoriaId = $partido['TorneoCategoria']['id'];
            if (!isset($ultimosPartidosPorCategoria[$categoriaId]) || strtotime($partido['Periodo']) > strtotime($ultimosPartidosPorCategoria[$categoriaId]['Periodo'])) {
                $ultimosPartidosPorCategoria[$categoriaId] = $partido;
            }
        }

        // Convertir el array asociativo a un array indexado
        $ultimosPartidosPorCategoria = array_values($ultimosPartidosPorCategoria);
        $HistorialTorneos = $ultimosPartidosPorCategoria;
    }

    $rankingByCategoryAndPlayerTotal= [];    
    if ($request->filter_jugador) {
        $rankingByCategoryAndPlayer =[ ];
        foreach ($categoriaSimpleComplete as $q) {
            $rankings = $this->rankingsByCategoryId($q['id']);
         
            if (!empty($rankings)) {
                $rankings = $rankings['Rankings'] ?? [];
        
                $rankingsCollection = collect($rankings);
        
                $rankingByCategoryAndPlayer = $rankingsCollection->firstWhere('id', $request->filter_jugador);
        
                if ($rankingByCategoryAndPlayer) {
                    $rankingByCategoryAndPlayerTotal[] = [
                        'id' => $q['id'],
                        'categoria_name' => $q['nombre'],
                        'countRepeat' => $rankingByCategoryAndPlayer['countRepeat'],
                    ];
                }
            }
        }
      return view('auth' . '.' . $this->viewName . '.ajax.jugador.partialView', [
            'Jugador' => $Jugador,
            'HistorialTorneos' => $HistorialTorneos,
            'categoriasYRankings' => $rankingByCategoryAndPlayerTotal
        ]);
    }
    // return $Jugador;
    return view('auth' . '.' . $this->viewName . '.ajax.jugador.partialView', ['Jugador' => $Jugador, 'HistorialTorneos' => $HistorialTorneos]);
}



    public function rankingsByCategoryId($filter_categoria)
    {
        $filter_anio = Carbon::now()->year;

        $Model = Comunidad::where('principal', true)->first();

        if ($Model != null) {
            $Rankings = Ranking::where('comunidad_id', $Model->id)->get();

            $Torneos = Torneo::whereIn('id', array_values(array_unique(array_filter($Rankings->pluck('torneo_id')->toArray()))))
                ->where(function ($q) use ($filter_anio) {
                    if ($filter_anio) {
                        $q->where(DB::raw('YEAR(fecha_inicio)'), '=', $filter_anio);
                    }
                })->where('rankeado', true)->orderBy('fecha_final', 'desc')->get();

            $Anios = $filter_anio == null ? array_values(array_unique($Torneos->pluck('fecha_inicio')->map(function ($date) {
                return Carbon::parse($date)->format('Y');
            })->toArray())) : [];

            $TorneoCategorias = TorneoCategoria::whereIn('id', array_values(array_unique(array_filter($Rankings->pluck('torneo_categoria_id')->toArray()))))->orderBy('id', 'desc')->get();
           
           
            $Categorias = Categoria::whereIn('id', array_values(array_unique(array_filter($TorneoCategorias->pluck('categoria_simple_id')->toArray()))))
                ->where('visible', true)->where('id', '!=', 3)->where('orden', '>', '0')
                ->where(function ($q) use ($filter_categoria) {
                    if ($filter_categoria) {
                        $q->where('id', $filter_categoria);
                    }
                })->orderBy('id', 'desc')->get();
                

            $RankingsResult = [];
            foreach ($Categorias as $q) {
                $Object = [];
                $JugadoresIds = [];
                $TorneoCategoria = TorneoCategoria::where('categoria_simple_id', $q->id)->get();

                $Object['categoria_id'] = $q->id;
                $Object['multiple'] = $q->dupla;
                
           
                
              
               
                foreach ($TorneoCategoria as $q2) {
                    foreach ($Rankings->where('torneo_categoria_id', $q2->id) as $q3) {
                        if ($q3->detalles != null && count($q3->detalles) > 0) {
                            foreach ($q3->detalles as $q4) {
                                     
                                $Id = $q->dupla == 1 ? ($q4->jugadorSimple->id . '-'  ) : $q4->jugadorSimple->id;
                                if (!in_array($Id, $JugadoresIds)) {
                                    $ObjectJugador = [];
                                    $Puntos = 0;
                                    $ObjectJugador['id'] = $Id;
                                    $ObjectJugador['nombre'] = $q->dupla ==1 ? ($q4->jugadorSimple->nombre_completo . ' + '  ) : $q4->jugadorSimple->nombre_completo;

                                    foreach ($Torneos as $q5) {
                                        $ObjectTorneo = [];
                                        $ObjectTorneo['id'] = $q5->id;
                                        $ObjectTorneo['anio'] = Carbon::parse($q5->fecha_inicio)->format('Y');
                                        $ObjectTorneo['nombre'] = $q5->nombre;

                                        if (count($TorneoCategoria->where('torneo_id', $q5->id)) > 0) {
                                            foreach ($TorneoCategoria->where('torneo_id', $q5->id) as $q9) {
                                                $ObjectTorneoCategoria = [];

                                                $rankingDetalle = RankingDetalle::whereHas('ranking', function ($query) use ($q9, $q5) {
                                                    $query->where('torneo_id', $q5->id);
                                                    $query->where('torneo_categoria_id', $q9->id);
                                                })->where(function ($query) use ($q, $q4) {
                                                    $query->where('jugador_simple_id', $q4->jugador_simple_id);
                                                    if ($q->dupla) {
                                                        $query->where('jugador_dupla_id', $q4->jugadorDupla->id);
                                                    }
                                                })->first();

                                                $Puntos += $rankingDetalle != null ? $rankingDetalle->puntos : 0;

                                                $ObjectTorneoCategoria['torneo_categoria_id'] = $q9->id;
                                                $ObjectTorneoCategoria['multiple'] = $q9->multiple;
                                                $ObjectTorneoCategoria['categoria_simple_id'] = $q9->categoria_simple_id;
                                                $ObjectTorneoCategoria['categoria_dupla_id'] = $q9->categoria_dupla_id;

                                                $ObjectTorneoCategoria['ranking_id'] = $rankingDetalle != null ? $rankingDetalle->ranking_id : null;
                                                $ObjectTorneoCategoria['puntos'] = $rankingDetalle != null ? $rankingDetalle->puntos : 0;

                                                $ObjectTorneo['categorias'][] = (object) $ObjectTorneoCategoria;
                                            }
                                        } else {
                                            $ObjectTorneo['categorias'] = [];
                                        }

                                        $ObjectJugador['torneos'][] = (object) $ObjectTorneo;
                                    }

                                    $ObjectJugador['puntos'] = $Puntos;
                                    $Object['jugadores'][] = $ObjectJugador;

                                    $JugadoresIds[] = $Id;
                                }
                            }
                        }
                    }
                }
                $RankingsResult[] = (object) $Object;
            }

            $RankingsResultYear = null;

            if ($filter_anio == null) {
                $RankingsResultYear = [];

                foreach ($RankingsResult as $q) {
                    $ResultYear = [];
                    $ResultYear['categoria_id'] = $q->categoria_id;
                    $ResultYear['multiple'] = $q->multiple;

                    foreach ($q->jugadores as $q2) {
                        $ResultYearJugador = [];
                        $Puntos = 0;
                        $ResultYearJugador['nombre'] = $q2['nombre'];

                        foreach ($Anios as $q3) {
                            $ResultYearJugadorAnio = [];
                            $ResultYearJugadorAnio['anio'] = $q3;
                            $ResultYearJugadorAnio['puntos'] = 0;

                            $TorneosPuntos = collect($q2['torneos'])->where('anio', $q3)->whereNotNull('categorias')->pluck('categorias');

                            foreach ($TorneosPuntos as $q4) {
                                $ResultYearJugadorAnio['puntos'] += count($q4) > 0 ? $q4[0]->puntos : 0;
                            }

                            $ResultYearJugador['anios'][] = (object) $ResultYearJugadorAnio;

                            $Puntos += $ResultYearJugadorAnio['puntos'];
                        }

                        $ResultYearJugador['puntos'] = $Puntos;

                        $ResultYear['jugadores'][] = $ResultYearJugador;
                    }

                    $RankingsResultYear[] = (object) $ResultYear;
                }
            }

            $result = [];

            

            foreach ($RankingsResult as $q2) {
                $countSingle = 0;
                $countRepeat = 1;
                $pointBefore = 0;
                $next = false;
        
                $jugadoresOrdenados = App::multiPropertySort(collect($q2->jugadores), [['column' => 'puntos', 'order' => 'desc']]);
        
                foreach ($jugadoresOrdenados as $key => $q3) {
                    if ($q3['puntos'] > 0) {
                        $countSingle += 1;
                        $pointBefore = $q3['puntos'];
                        $countRepeat = $next ? $countRepeat : $countSingle;
                        
                        
        
                        $result[] = [
                            'countRepeat' => $countRepeat,
                            'nombre' => $q3['nombre'],
                            'puntos' => $q3['puntos'],
                            'id' => $q3['id']
                        ];
        
                        if (count(collect($q2->jugadores)->where('puntos', '>', '0')) > ($key + 1)) {
                            if ($q3['puntos'] != $jugadoresOrdenados[$key + 1]['puntos']) {
                                $countRepeat += 1;
                                $next = false;
                            } else {
                                $next = true;
                            }
                        }
                    }
                }
            }
        

            return [
                'Rankings' => collect( $result),

            ];

        } else {
            abort(404);
        }
    }

    public function jugadorPartidosPartialView(Request $request)
    {
        $Data = null;

        $TorneoCategoria = TorneoCategoria::where('torneo_id', $request->filter_torneo)
        ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
        ->where('id', $request->filter_category)->first();

        if($TorneoCategoria != null)
        {
            $Jugador = Jugador::where('id', $request->filter_jugador)
            ->where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->first();

            if($Jugador != null)
            {
                $Partidos = Partido::where('comunidad_id',Auth::guard('web')->user()->comunidad_id)
                ->where('torneo_id', $request->filter_torneo)->where('torneo_categoria_id', $request->filter_category)
                ->where(function ($q) use ($Jugador){
                    $q->where('jugador_local_uno_id', $Jugador->id)->orWhere('jugador_local_dos_id', $Jugador->id)
                    ->orWhere('jugador_rival_uno_id', $Jugador->id)->orWhere('jugador_rival_dos_id', $Jugador->id);
                })->get();

                $Data = (object)[
                    'Jugador' => $Jugador,
                    'Torneo' => $TorneoCategoria->torneo,
                    'TorneoCategoria' => $TorneoCategoria,
                    'Categoria' => $TorneoCategoria->categoriaSimple,
                    'Partidos' => $Partidos,
                ];
            }
        }

        return view('auth'.'.'.$this->viewName.'.ajax.jugador.partido.partialView', ['Data' => $Data]);
    }




    public function torneo()
    {
        $Torneos = Torneo::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->get();
        return view('auth'.'.'.$this->viewName.'.torneo', ['Torneos' => $Torneos, 'ViewName' => ucfirst($this->viewName)]);
    }

    public function torneoExportarPdf($torneo, $categoria)
{
    $TorneoCategoria = TorneoCategoria::where('torneo_id', $torneo)
        ->whereHas('torneo', function ($q) {
            $q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);
        })
        ->where('id', $categoria)->first();

    if ($TorneoCategoria != null) {
        $Partidos = Partido::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
            ->where('torneo_id', $torneo)
            ->where('torneo_categoria_id', $TorneoCategoria->id)
            ->whereNull('fase')
            ->get();

        $Grupos = TorneoGrupo::where('torneo_categoria_id', $TorneoCategoria->id)
            ->select(['nombre_grupo', 'grupo_id'])
            ->groupBy(['nombre_grupo', 'grupo_id'])
            ->orderBy(DB::raw('LENGTH(nombre_grupo)'))
            ->orderBy('nombre_grupo')
            ->get();

        $TablePositionsPorGrupo = [];

        foreach ($Grupos as $grupo) {
            $grupo_id = $grupo->grupo_id;

            // JUGADORES DEL GRUPO
            $Jugadores = TorneoGrupo::where('torneo_categoria_id', $TorneoCategoria->id)
                ->where('grupo_id', $grupo_id)
                ->get()
                ->map(function ($q) use ($TorneoCategoria) {
                    return [
                        'jugador_simple_id' => $q->jugadorSimple->id,
                        'jugador_dupla_id' => $TorneoCategoria->multiple ? $q->jugadorDupla->id : null,
                        'nombres' => $TorneoCategoria->multiple ? ($q->jugadorSimple->nombre_completo . " + " . $q->jugadorDupla->nombre_completo) : $q->jugadorSimple->nombre_completo,
                    ];
                });

            // JUGADORES POSICIONES
            $TablePositions = [];

            foreach ($Jugadores as $q2) {
                $PartidosComoLocal = collect($TorneoCategoria->torneo->partidos()
                    ->where('torneo_categoria_id', $TorneoCategoria->id)
                    ->where('grupo_id', $grupo_id)
                    ->where('jugador_local_uno_id', $q2['jugador_simple_id'])
                    ->when($TorneoCategoria->multiple, function ($query) use ($q2) {
                        $query->where('jugador_local_dos_id', $q2['jugador_dupla_id']);
                    })
                    ->whereNull('fase')
                    ->get());

                $PartidosComoRival = collect($TorneoCategoria->torneo->partidos()
                    ->where('torneo_categoria_id', $TorneoCategoria->id)
                    ->where('grupo_id', $grupo_id)
                    ->where('jugador_rival_uno_id', $q2['jugador_simple_id'])
                    ->when($TorneoCategoria->multiple, function ($query) use ($q2) {
                        $query->where('jugador_rival_dos_id', $q2['jugador_dupla_id']);
                    })
                    ->whereNull('fase')
                    ->get());

                $PartidosJugados = 0;
                $SetsGanados = 0;
                $SetsPerdidos = 0;
                $GamesGanados = 0;
                $GamesPerdidos = 0;
                $Puntos = 0;

                // Procesar partidos como local y como rival
                foreach (array_merge($PartidosComoLocal->toArray(), $PartidosComoRival->toArray()) as $p) {
                    if ($p['estado_id'] == App::$ESTADO_FINALIZADO) $PartidosJugados += 1;

                    if ($p['jugador_ganador_uno_id'] == $q2['jugador_simple_id']) {
                        $SetsGanados += $p['jugador_local_set'];
                        $SetsPerdidos += $p['jugador_rival_set'];
                        $GamesGanados += $p['jugador_local_juego'];
                        $GamesPerdidos += $p['jugador_rival_juego'];
                        $Puntos += ($p['jugador_local_set'] == 0 && $p['jugador_rival_set'] == 0 ? 0 : ($p['jugador_rival_set'] <= 0 ? 5 : 4));
                    } else {
                        $SetsGanados += $p['jugador_rival_set'];
                        $SetsPerdidos += $p['jugador_local_set'];
                        $GamesGanados += $p['jugador_rival_juego'];
                        $GamesPerdidos += $p['jugador_local_juego'];
                        $Puntos += ($p['jugador_local_set'] == 0 && $p['jugador_rival_set'] == 0 ? 0 : ($p['jugador_rival_set'] == 0 ? 1 : 2));
                    }
                }

                $SetsDiferencias = $SetsGanados - $SetsPerdidos;
                $GamesDiferencias = $GamesGanados - $GamesPerdidos;
                $Puntos *= $TorneoCategoria->torneo->valor_set;

                $TablePositions[] = [
                    'jugador_simple_id' => $q2['jugador_simple_id'],
                    'jugador_dupla_id' => $q2['jugador_dupla_id'],
                    'nombres' => $q2['nombres'],
                    'partidosJugados' => $PartidosJugados,
                    'setsGanados' => $SetsGanados,
                    'setsPerdidos' => $SetsPerdidos,
                    'setsDiferencias' => $SetsDiferencias,
                    'gamesGanados' => $GamesGanados,
                    'gamesPerdidos' => $GamesPerdidos,
                    'gamesDiferencias' => $GamesDiferencias,
                    'puntos' => $Puntos,
                ];
            }

            $TablePositionsPorGrupo[$grupo->nombre_grupo] = App::multiPropertySort(collect($TablePositions), [
                ['column' => 'puntos', 'order' => 'desc'],
                ['column' => 'setsDiferencias', 'order' => 'desc'],
                ['column' => 'gamesDiferencias', 'order' => 'desc'],
                ['column' => 'setsGanados', 'order' => 'desc'],
                ['column' => 'gamesGanados', 'order' => 'desc']
            ]);
        }

        $Data = [
            'Torneo' => $TorneoCategoria->torneo,
            'TorneoCategoria' => $TorneoCategoria,
            'Categoria' => $TorneoCategoria->categoriaSimple,
            'Grupos' => $Grupos,
            'Partidos' => $Partidos,
            'Count' => 0,
            'TablePositionsPorGrupo' => $TablePositionsPorGrupo,
        ];
        
       

        $pdf = Pdf::loadView('auth' . '.' . $this->viewName . '.ajax.torneo.partialView', $Data)->setPaper('a4', 'landscape');
        return $pdf->stream("ReporteTorneo.pdf");
    }

    return null;
}


    public function torneoFaseFinalExportarPdf($torneo, $categoria)
    {
        $TorneoCategoria = TorneoCategoria::where('id', $categoria)->where('torneo_id', $torneo)
        ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})->first();

        if($TorneoCategoria != null)
        {
            $TorneoGrupos = $TorneoCategoria->torneo->torneoGrupos()->where('torneo_categoria_id', $TorneoCategoria->id)
            ->select('grupo_id')->groupBy('grupo_id')->get();

            $JugadoresClasificados = [];

            $Clasifican = $TorneoCategoria->clasificados;

            foreach ($TorneoGrupos as $key => $q) {
                //JUGADORES DEL GRUPO
                $Jugadores = $TorneoCategoria->torneo->torneoGrupos()->where('torneo_categoria_id', $TorneoCategoria->id)
                    ->where('grupo_id', $q->grupo_id)->get()->map(function ($q) use ($TorneoCategoria) {
                        return [
                            'jugador_simple_id' => $q->jugadorSimple->id,
                            'jugador_dupla_id' => $TorneoCategoria->multiple ? $q->jugadorDupla->id : null,
                            'nombres' => $TorneoCategoria->multiple ? ($q->jugadorSimple->nombre_completo . " + " . $q->jugadorDupla->nombre_completo) : $q->jugadorSimple->nombre_completo
                        ];
                    });

                //JUGADORES CALISIFICADOS POR GRUPO
                $TablePositions = [];
                foreach ($Jugadores as $key2 => $q2) {
                    if ($TorneoCategoria->multiple) {
                        $PartidosComoLocal = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                            ->where('grupo_id', $q->grupo_id)->where('jugador_local_uno_id', $q2['jugador_simple_id'])
                            ->where('jugador_local_dos_id', $q2['jugador_dupla_id'])->whereNull('fase')->get());

                        $PartidosComoRival = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                            ->where('grupo_id', $q->grupo_id)->where('jugador_rival_uno_id', $q2['jugador_simple_id'])
                            ->where('jugador_rival_dos_id', $q2['jugador_dupla_id'])->whereNull('fase')->get());

                        $SetsGanados = 0;
                        $SetsPerdidos = 0;
                        $GamesGanados = 0;
                        $GamesPerdidos = 0;
                        $Puntos = 0;

                        foreach ($PartidosComoLocal as $p) {
                            if ($p->jugador_ganador_uno_id == $q2['jugador_simple_id']) {   //NO Rival
                                $SetsGanados += $p->jugador_local_set;
                                $SetsPerdidos += $p->jugador_rival_set;
                                $GamesGanados += $p->jugador_local_juego;
                                $GamesPerdidos += $p->jugador_rival_juego;
                                $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                            } else {
                                //Rival
                                $SetsGanados += $p->jugador_rival_set;
                                $SetsPerdidos += $p->jugador_local_set;
                                $GamesGanados += $p->jugador_rival_juego;
                                $GamesPerdidos += $p->jugador_local_juego;
                                $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2));
                            }
                        }

                        foreach ($PartidosComoRival as $p) {
                            if ($p->jugador_ganador_uno_id == $q2['jugador_simple_id']) {   //NO Rival
                                $SetsGanados += $p->jugador_local_set;
                                $SetsPerdidos += $p->jugador_rival_set;
                                $GamesGanados += $p->jugador_local_juego;
                                $GamesPerdidos += $p->jugador_rival_juego;
                                $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                            } else {
                                //Rival
                                $SetsGanados += $p->jugador_rival_set;
                                $SetsPerdidos += $p->jugador_local_set;
                                $GamesGanados += $p->jugador_rival_juego;
                                $GamesPerdidos += $p->jugador_local_juego;
                                $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2));
                            }
                        }

                        $SetsDiferencias = $SetsGanados - $SetsPerdidos;

                        $GamesDiferencias = $GamesGanados - $GamesPerdidos;

                        $Puntos = $Puntos * $TorneoCategoria->torneo->valor_set;

                        $TablePositions[] = [
                            'key' => ($key . '-' . $key2),
                            'grupo_id' => $q->grupo->id,
                            'grupo' => $q->grupo->nombre,
                            'jugador_simple_id' => $q2['jugador_simple_id'],
                            'jugador_dupla_id' => $q2['jugador_dupla_id'],
                            'nombres' => $q2['nombres'],
                            'setsGanados' => $SetsGanados,
                            'setsPerdidos' => $SetsPerdidos,
                            'setsDiferencias' => $SetsDiferencias,
                            'gamesGanados' => $GamesGanados,
                            'gamesPerdidos' => $GamesPerdidos,
                            'gamesDiferencias' => $GamesDiferencias,
                            'puntos' => $Puntos
                        ];

                    } else {

                        $PartidosComoLocal = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                            ->where('grupo_id', $q->grupo_id)->where('jugador_local_uno_id', $q2['jugador_simple_id'])->whereNull('fase')->get());

                        $PartidosComoRival = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                            ->where('grupo_id', $q->grupo_id)->where('jugador_rival_uno_id', $q2['jugador_simple_id'])->whereNull('fase')->get());

                        $SetsGanados = 0;
                        $SetsPerdidos = 0;
                        $GamesGanados = 0;
                        $GamesPerdidos = 0;
                        $Puntos = 0;

                        foreach ($PartidosComoLocal as $p) {
                            if ($p->jugador_ganador_uno_id == $q2['jugador_simple_id']) {   //NO Rival
                                $SetsGanados += $p->jugador_local_set;
                                $SetsPerdidos += $p->jugador_rival_set;
                                $GamesGanados += $p->jugador_local_juego;
                                $GamesPerdidos += $p->jugador_rival_juego;
                                $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                            } else {
                                //Rival
                                $SetsGanados += $p->jugador_rival_set;
                                $SetsPerdidos += $p->jugador_local_set;
                                $GamesGanados += $p->jugador_rival_juego;
                                $GamesPerdidos += $p->jugador_local_juego;
                                $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2));
                            }
                        }

                        foreach ($PartidosComoRival as $p) {
                            if ($p->jugador_ganador_uno_id == $q2['jugador_simple_id']) {   //NO Rival
                                $SetsGanados += $p->jugador_local_set;
                                $SetsPerdidos += $p->jugador_rival_set;
                                $GamesGanados += $p->jugador_local_juego;
                                $GamesPerdidos += $p->jugador_rival_juego;
                                $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                            } else {
                                //Rival
                                $SetsGanados += $p->jugador_rival_set;
                                $SetsPerdidos += $p->jugador_local_set;
                                $GamesGanados += $p->jugador_rival_juego;
                                $GamesPerdidos += $p->jugador_local_juego;
                                $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2));
                            }
                        }

                        $SetsDiferencias = $SetsGanados - $SetsPerdidos;

                        $GamesDiferencias = $GamesGanados - $GamesPerdidos;

                        $Puntos = $Puntos * $TorneoCategoria->torneo->valor_set;

                        $TablePositions[] = [
                            'key' => ($key . '-' . $key2),
                            'grupo_id' => $q->grupo->id,
                            'grupo' => $q->grupo->nombre,
                            'jugador_simple_id' => $q2['jugador_simple_id'],
                            'jugador_dupla_id' => null,
                            'nombres' => $q2['nombres'],
                            'setsGanados' => $SetsGanados,
                            'setsPerdidos' => $SetsPerdidos,
                            'setsDiferencias' => $SetsDiferencias,
                            'gamesGanados' => $GamesGanados,
                            'gamesPerdidos' => $GamesPerdidos,
                            'gamesDiferencias' => $GamesDiferencias,
                            'puntos' => $Puntos,
                        ];
                    }
                }
                $JugadoresClasificados[] = ['Grupo' => $q->grupo->nombre, 'Clasificados' => App::multiPropertySort(collect($TablePositions), [['column' => 'puntos', 'order' => 'desc'], ['column' => 'setsDiferencias', 'order' => 'desc'], ['column' => 'gamesDiferencias', 'order' => 'desc'], ['column' => 'setsGanados', 'order' => 'desc'], ['column' => 'gamesGanados', 'order' => 'desc']])->take($Clasifican)];
            }

            //CLASIFICADOS POR C�0�9LCULO
            $PrimerosLugares = [];
            $SegundoLugares = [];
            $TercerosLugares = [];

            foreach ($JugadoresClasificados as $key => $value) {
                if ($Clasifican == 1) $PrimerosLugares[] = $value['Clasificados']->first();
                else if ($Clasifican == 2) {
                    $PrimerosLugares[] = $value['Clasificados']->first();
                    $SegundoLugares[] = $value['Clasificados']->last();
                } else {
                    $PrimerosLugares[] = $value['Clasificados']->first();
                    $TercerosLugares[] = $value['Clasificados']->last();
                }
            }

            if ($Clasifican == 3) {
                $Clasificados = array_merge(collect($PrimerosLugares)->pluck('key')->toArray(), collect($TercerosLugares)->pluck('key')->toArray());
                foreach (collect($JugadoresClasificados)->pluck('Clasificados') as $key => $value) {
                    foreach ($value as $ke2 => $value2) {
                        if (!in_array($value2['key'], $Clasificados)) $SegundoLugares[] = $value2;
                    }
                }
                $TercerosLugares = collect($TercerosLugares)->sortByDesc('puntos')->take($TorneoCategoria->clasificados_terceros)->toArray();
            }

            $PrimerosLugares = collect($PrimerosLugares)->sortByDesc('puntos');
            $SegundoLugares = collect($SegundoLugares)->sortByDesc('puntos');
            $TercerosLugares = collect($TercerosLugares)->sortByDesc('puntos');

            $JugadoresClasificadosMerge = $PrimerosLugares->merge($SegundoLugares)->merge($TercerosLugares);

            $TorneoFaseFinal = (object)['TorneoCategoria' => $TorneoCategoria, 'JugadoresClasificados' => App::multiPropertySort(collect($JugadoresClasificadosMerge), [['column' => 'puntos', 'order' => 'desc'], ['column' => 'gamesDiferencias', 'order' => 'desc']])];

            return view('auth' . '.' . $this->viewName . '.ajax.torneo.final.partialView', ['TorneoFaseFinal' => $TorneoFaseFinal]);

            //$pdf = Pdf::loadView('auth' . '.' . $this->viewName . '.ajax.torneo.final.partialView', ['TorneoFaseFinal' => $TorneoFaseFinal])->setPaper('a4', 'landscape');
            //return $pdf->stream("ReporteTorneoFaseFinal.pdf");

        }

        return null;
    }

    public function torneoPartialView(Request $request)
    {
        $TorneoCategoria = TorneoCategoria::where('torneo_id', $request->torneo)
        ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
        ->where('categoria_simple_id', $request->categoria)->first();

        if($TorneoCategoria != null)
        {
            $Partidos = Partido::where('comunidad_id',Auth::guard('web')->user()->comunidad_id)
            ->where('torneo_id', $request->torneo)->where('torneo_categoria_id', $TorneoCategoria->id)
            ->get();
            
            

        $Grupos = TorneoGrupo::where('torneo_categoria_id', $TorneoCategoria->id)
            ->select(['nombre_grupo', 'grupo_id'])
            ->groupBy(['nombre_grupo', 'grupo_id'])
            ->orderBy(DB::raw('LENGTH(nombre_grupo)'))
            ->orderBy('nombre_grupo')
            ->get();

        $TablePositionsPorGrupo = [];

        foreach ($Grupos as $grupo) {
            $grupo_id = $grupo->grupo_id;

            // JUGADORES DEL GRUPO
            $Jugadores = TorneoGrupo::where('torneo_categoria_id', $TorneoCategoria->id)
                ->where('grupo_id', $grupo_id)
                ->get()
                ->map(function ($q) use ($TorneoCategoria) {
                    return [
                        'jugador_simple_id' => $q->jugadorSimple->id,
                        'jugador_dupla_id' => $TorneoCategoria->multiple ? $q->jugadorDupla->id : null,
                        'nombres' => $TorneoCategoria->multiple ? ($q->jugadorSimple->nombre_completo . " + " . $q->jugadorDupla->nombre_completo) : $q->jugadorSimple->nombre_completo,
                    ];
                });

            // JUGADORES POSICIONES
            $TablePositions = [];

            foreach ($Jugadores as $q2) {
                $PartidosComoLocal = collect($TorneoCategoria->torneo->partidos()
                    ->where('torneo_categoria_id', $TorneoCategoria->id)
                    ->where('grupo_id', $grupo_id)
                    ->where('jugador_local_uno_id', $q2['jugador_simple_id'])
                    ->when($TorneoCategoria->multiple, function ($query) use ($q2) {
                        $query->where('jugador_local_dos_id', $q2['jugador_dupla_id']);
                    })
                    ->whereNull('fase')
                    ->get());

                $PartidosComoRival = collect($TorneoCategoria->torneo->partidos()
                    ->where('torneo_categoria_id', $TorneoCategoria->id)
                    ->where('grupo_id', $grupo_id)
                    ->where('jugador_rival_uno_id', $q2['jugador_simple_id'])
                    ->when($TorneoCategoria->multiple, function ($query) use ($q2) {
                        $query->where('jugador_rival_dos_id', $q2['jugador_dupla_id']);
                    })
                    ->whereNull('fase')
                    ->get());

                $PartidosJugados = 0;
                $SetsGanados = 0;
                $SetsPerdidos = 0;
                $GamesGanados = 0;
                $GamesPerdidos = 0;
                $Puntos = 0;

                // Procesar partidos como local y como rival
                foreach (array_merge($PartidosComoLocal->toArray(), $PartidosComoRival->toArray()) as $p) {
                    if ($p['estado_id'] == App::$ESTADO_FINALIZADO) $PartidosJugados += 1;

                    if ($p['jugador_ganador_uno_id'] == $q2['jugador_simple_id']) {
                        $SetsGanados += $p['jugador_local_set'];
                        $SetsPerdidos += $p['jugador_rival_set'];
                        $GamesGanados += $p['jugador_local_juego'];
                        $GamesPerdidos += $p['jugador_rival_juego'];
                        $Puntos += ($p['jugador_local_set'] == 0 && $p['jugador_rival_set'] == 0 ? 0 : ($p['jugador_rival_set'] <= 0 ? 5 : 4));
                    } else {
                        $SetsGanados += $p['jugador_rival_set'];
                        $SetsPerdidos += $p['jugador_local_set'];
                        $GamesGanados += $p['jugador_rival_juego'];
                        $GamesPerdidos += $p['jugador_local_juego'];
                        $Puntos += ($p['jugador_local_set'] == 0 && $p['jugador_rival_set'] == 0 ? 0 : ($p['jugador_rival_set'] == 0 ? 1 : 2));
                    }
                }

                $SetsDiferencias = $SetsGanados - $SetsPerdidos;
                $GamesDiferencias = $GamesGanados - $GamesPerdidos;
                $Puntos *= $TorneoCategoria->torneo->valor_set;

                $TablePositions[] = [
                    'jugador_simple_id' => $q2['jugador_simple_id'],
                    'jugador_dupla_id' => $q2['jugador_dupla_id'],
                    'nombres' => $q2['nombres'],
                    'partidosJugados' => $PartidosJugados,
                    'setsGanados' => $SetsGanados,
                    'setsPerdidos' => $SetsPerdidos,
                    'setsDiferencias' => $SetsDiferencias,
                    'gamesGanados' => $GamesGanados,
                    'gamesPerdidos' => $GamesPerdidos,
                    'gamesDiferencias' => $GamesDiferencias,
                    'puntos' => $Puntos,
                ];
            }

            $TablePositionsPorGrupo[$grupo->nombre_grupo] = App::multiPropertySort(collect($TablePositions), [
                ['column' => 'puntos', 'order' => 'desc'],
                ['column' => 'setsDiferencias', 'order' => 'desc'],
                ['column' => 'gamesDiferencias', 'order' => 'desc'],
                ['column' => 'setsGanados', 'order' => 'desc'],
                ['column' => 'gamesGanados', 'order' => 'desc']
            ]);
        }
            $Data = array(
                'Torneo' => $TorneoCategoria->torneo,
                'TorneoCategoria' => $TorneoCategoria,
                'Categoria' => $TorneoCategoria->categoriaSimple,
                'Grupos' => TorneoGrupo::where('torneo_categoria_id', $TorneoCategoria->id)->select(['nombre_grupo', 'grupo_id'])->groupBy(['nombre_grupo', 'grupo_id'])
                ->orderBy(DB::raw('LENGTH(nombre_grupo)'))->orderBy('nombre_grupo')->get(),
                'Partidos' => $Partidos,
                'Count' => 0,
                'TablePositionsPorGrupo' => $TablePositionsPorGrupo,

            );


            $pdf = Pdf::loadView('auth'.'.'.$this->viewName.'.ajax.torneo.partialView', $Data)->setPaper('a4', 'landscape');
            return $pdf->stream("ReporteTorneo.pdf");
        }

        return null;
    }



    public function h2h(Request $request)
    {
        $Torneos = Torneo::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->orderByDesc('fecha_inicio')->get();
       $Jugadores = Jugador::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->orderBy('nombres')->select(DB::raw("CONCAT(jugadors.nombres, ' ', jugadors.apellidos) as nombre_completo"), 'jugadors.id')->pluck('nombre_completo', 'jugadors.id');
        $Categorias = Categoria::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
                           ->where('dupla', 0)
                           ->where('visible', 1)
                           ->orderBy('orden')
                           ->get();

        if ($request->has('jugador1') && $request->has('jugador2')) {
            $jugador1 = Jugador::find($request->jugador1);
            $jugador2 = Jugador::find($request->jugador2);

            // Aqu�� puedes agregar la l��gica para generar el reporte H2H entre jugador1 y jugador2
            // Por ejemplo, podr��as pasar los datos de los jugadores a la vista o generar un PDF, etc.
        } else {
            // Obtener todos los jugadores si no se ha seleccionado ning��n jugador espec��fico
            $Jugadores = Jugador::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->orderBy('nombres')->get();
        }

        return view('auth.'.$this->viewName.'.h2h', [
            'Torneos' => $Torneos,
            'Jugadores' => $Jugadores,
            'Categorias' => $Categorias,
            'ViewName' => ucfirst($this->viewName)
        ]);
    }

    public function getCategoriasByTorneo(Request $request)
    {
        $categorias = TorneoCategoria::join('categorias', 'torneo_categorias.categoria_simple_id', '=', 'categorias.id')
            ->where('torneo_categorias.torneo_id', $request->torneo_id)
            ->where('dupla', 0)
            ->pluck('categorias.nombre', 'torneo_categorias.id');

        return response()->json($categorias);
    }

    public function getJugadoresByTorneoCategoria(Request $request)
    {
        $jugadores = TorneoJugador::join('jugadors', 'torneo_jugadors.jugador_simple_id', '=', 'jugadors.id')
        ->where('torneo_jugadors.torneo_id', $request->torneo_id)
        ->where('torneo_jugadors.torneo_categoria_id', $request->categoria_id)
        ->select(DB::raw("CONCAT(jugadors.nombres, ' ', jugadors.apellidos) as nombre_completo"), 'jugadors.id')
        ->pluck('nombre_completo', 'jugadors.id');

    return response()->json($jugadores);
    }

    public function getCategorias(Request $request)
    {
        $categorias = Categoria::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->get();

        return response()->json($categorias);
    }
    
    public function getJugadoresActivos(Request $request)
    {
        $jugadores = Jugador::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->get();
    
        return response()->json($jugadores);
    }
    
    
    public function getJugadoresByCategoria(Request $request)
    {
    $categoriaId = $request->input('categoria_id');

   $jugadores = DB::table('torneo_categorias as tc')
    ->join('categorias as c', function ($join) {
        $join->on('tc.categoria_simple_id', '=', 'c.id')
            ->where('c.dupla', 0)
            ->where('c.visible', 1)
            ->whereNull('c.deleted_at');
    })
    ->join('torneo_jugadors as tj', 'tj.torneo_categoria_id', '=', 'tc.id')
    ->join('jugadors as j', function ($join) {
        $join->on('j.id', '=', 'tj.jugador_simple_id')
            ->whereNull('j.deleted_at');
    })
    ->where('tc.categoria_simple_id', $categoriaId)
    ->whereNull('tc.deleted_at')
    ->whereNull('tj.deleted_at')
    ->groupBy('tj.jugador_simple_id', 'j.nombres', 'j.apellidos')
    ->select('tj.jugador_simple_id as id', DB::raw("CONCAT_WS(' ', j.nombres, j.apellidos) as nombre_completo"))
    ->orderBy('nombre_completo')
    ->get();

    $jugadores = $jugadores->pluck('nombre_completo', 'id');
    
    return response()->json($jugadores);
}


public function getJugadoresByTorneo(Request $request)
    {
    $torneoId = $request->input('torneo_id');

   $jugadores = DB::table('torneo_categorias as tc')
    ->join('categorias as c', function ($join) {
        $join->on('tc.categoria_simple_id', '=', 'c.id')
            ->where('c.dupla', 0)
            ->where('c.visible', 1)
            ->whereNull('c.deleted_at');
    })
    ->join('torneo_jugadors as tj', 'tj.torneo_categoria_id', '=', 'tc.id')
    ->join('jugadors as j', function ($join) {
        $join->on('j.id', '=', 'tj.jugador_simple_id')
            ->whereNull('j.deleted_at');
    })
    ->where('tc.torneo_id', $torneoId)
    ->whereNull('tc.deleted_at')
    ->whereNull('tj.deleted_at')
    ->groupBy('tj.jugador_simple_id', 'j.nombres', 'j.apellidos')
    ->select('tj.jugador_simple_id as id', DB::raw("CONCAT_WS(' ', j.nombres, j.apellidos) as nombre_completo"))
    ->orderBy('nombre_completo')
    ->get();

    $jugadores = $jugadores->pluck('nombre_completo', 'id');
    
    return response()->json($jugadores);
}


    public function jugadorPartidosExportarPdf($torneo, $categoria, $jugador)
    {
        $Data = null;
        
        $TorneoCategoria = TorneoCategoria::where('torneo_id', $torneo)
            ->whereHas('torneo', function ($q) {
                $q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);
            })
            ->where('id', $categoria)->first();

        if($TorneoCategoria != null) {
            $Jugador = Jugador::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
                ->where('id', $jugador)->first();

            if($Jugador != null) {
                $Partidos = Partido::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
                    ->where('torneo_id', $torneo)
                    ->where('torneo_categoria_id', $categoria)
                    ->where(function ($q) use ($Jugador){
                        $q->where('jugador_local_uno_id', $Jugador->id)->orWhere('jugador_local_dos_id', $Jugador->id)
                        ->orWhere('jugador_rival_uno_id', $Jugador->id)->orWhere('jugador_rival_dos_id', $Jugador->id);
                    })->get();

                $Data = [
                    'Jugador' => $Jugador,
                    'Torneo' => $TorneoCategoria->torneo,
                    'TorneoCategoria' => $TorneoCategoria,
                    'Categoria' => $TorneoCategoria->categoriaSimple,
                    'Partidos' => $Partidos,
                ];

                $pdf = Pdf::loadView('auth'.'.'.$this->viewName.'.ajax.jugador.partido.pdf', ['Data' => $Data])->setPaper('a4', 'landscape');
                
                
                return $pdf->stream("ReporteJugadorPartidos_{$Jugador->nombre_completo}.pdf");
            }
        }

        return null;
    }
    
    
     public function jugadorCompletoExportarPdf($jugador)
    {
        $Jugador = Jugador::where('id', $jugador)
            ->where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->first();

        if($Jugador == null) {
            abort(404, 'Jugador no encontrado');
        }

        $Torneos = Torneo::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
            ->orderByDesc('fecha_inicio')->get();

        $HistorialTorneos = [];
        $TorneoDetalles = [];
        
        if(count($Torneos) > 0) {
            foreach ($Torneos as $torneo) {
                $Partidos = Partido::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
                    ->where('torneo_id', $torneo->id)
                    ->where(function ($q) use ($Jugador) {
                        $q->where('jugador_local_uno_id', $Jugador->id)
                          ->orWhere('jugador_local_dos_id', $Jugador->id)
                          ->orWhere('jugador_rival_uno_id', $Jugador->id)
                          ->orWhere('jugador_rival_dos_id', $Jugador->id);
                    })->first();

                if($Partidos != null) {
                    $TorneoCategoria = TorneoCategoria::find($Partidos->torneo_categoria_id);
                    
                    if($TorneoCategoria != null && $TorneoCategoria->categoriaSimple != null) {
                        // Obtener todos los partidos del jugador en este torneo
                        $PartidosCompletos = Partido::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
                            ->where('torneo_id', $torneo->id)
                            ->where('torneo_categoria_id', $TorneoCategoria->id)
                            ->where(function ($q) use ($Jugador) {
                                $q->where('jugador_local_uno_id', $Jugador->id)
                                  ->orWhere('jugador_local_dos_id', $Jugador->id)
                                  ->orWhere('jugador_rival_uno_id', $Jugador->id)
                                  ->orWhere('jugador_rival_dos_id', $Jugador->id);
                            })->orderBy('fase', 'desc')->get();

                        // Determinar la fase m��s alta alcanzada
                        $FaseAlcanzada = "Fase de Grupos";
                        $PartidoFinal = null;
                        
                        // Buscar el partido de mayor fase donde el jugador gan��
                        foreach($PartidosCompletos as $partido) {
                            if($partido->fase != null && in_array($Jugador->id, [$partido->jugador_ganador_uno_id, $partido->jugador_ganador_dos_id])) {
                                $PartidoFinal = $partido;
                                break;
                            }
                        }
                        
                        // Si no gan�� ning��n partido de eliminaci��n, buscar el ��ltimo partido que perdi��
                        if($PartidoFinal == null) {
                            $PartidoFinal = $PartidosCompletos->whereNotNull('fase')->first();
                        }

                        if($PartidoFinal && $PartidoFinal->fase != null) {
                            $esGanador = in_array($Jugador->id, [$PartidoFinal->jugador_ganador_uno_id, $PartidoFinal->jugador_ganador_dos_id]);
                            switch($PartidoFinal->fase) {
                                case 16: $FaseAlcanzada = $esGanador ? "Clasificacón Octavos" : "Eliminado en Dieciseisavos"; break;
                                case 8: $FaseAlcanzada = $esGanador ? "Clasificación Cuartos" : "Eliminado en Octavos"; break;
                                case 4: $FaseAlcanzada = $esGanador ? "Clasificación Semifinal" : "Eliminado en Cuartos"; break;
                                case 2: $FaseAlcanzada = $esGanador ? "Clasificación Final" : "Eliminado en Semifinal"; break;
                                case 1: $FaseAlcanzada = $esGanador ? "Campeón" : "Finalista"; break;
                            }
                        }

                        $HistorialTorneos[] = [
                            'id' => $torneo->id,
                            'Torneo' => $torneo->nombre,
                            'Periodo' => \Carbon\Carbon::parse($torneo->fecha_inicio)->format('Y'),
                            'Categoria' => $TorneoCategoria->categoriaSimple->nombre,
                            'Fase' => $FaseAlcanzada,
                            'Estado' => $torneo->estado_texto,
                            'TorneoCategoria' => $TorneoCategoria
                        ];

                        $TorneoDetalles[] = [
                            'Torneo' => $torneo,
                            'TorneoCategoria' => $TorneoCategoria,
                            'Categoria' => $TorneoCategoria->categoriaSimple,
                            'Partidos' => $PartidosCompletos
                        ];
                    }
                }
            }
        }

        if(count($HistorialTorneos) == 0) {
            abort(404, 'No se encontraron torneos para este jugador');
        }

        $Data = [
            'Jugador' => $Jugador,
            'HistorialTorneos' => $HistorialTorneos,
            'TorneoDetalles' => $TorneoDetalles
        ];

        $pdf = Pdf::loadView('auth'.'.'.$this->viewName.'.ajax.jugador.completo.pdf', $Data)->setPaper('a4', 'landscape');
        return $pdf->stream("ReporteCompletoJugador_{$Jugador->nombre_completo}.pdf");
    }



}
