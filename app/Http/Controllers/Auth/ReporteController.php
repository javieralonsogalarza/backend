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

        if($Jugador != null && count($Torneos) > 0)
        {
            foreach ($Torneos as $q)
            {
                $Partido =  Partido::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->where('torneo_id', $q->id)
                ->where(function ($q) use ($Jugador){$q->where('jugador_local_uno_id', $Jugador->id)->orWhere('jugador_local_dos_id', $Jugador->id)->orWhere('jugador_local_dos_id', $Jugador->id)->orWhere('jugador_rival_uno_id', $Jugador->id)->orWhere('jugador_rival_dos_id', $Jugador->id);})
                ->orderBy('id', 'desc')->first();

                if($Partido != null)
                {
                    $HistorialTorneos[] = [
                        'id' => $q->id,
                        'Torneo' => $q->nombre,
                        'TorneoCategoria' => $Partido->torneoCategoria,
                        'Periodo' => ($q->fecha_inicio_texto." - ".$q->fecha_final_texto),
                        'Categoria' => $q->multiple && ($Partido->torneoCategoria->categoria_simple_id !== $Partido->torneoCategoria->categoria_dupla_id) ? (($Partido->torneoCategoria->categoriaSimple != null ? $Partido->torneoCategoria->categoriaSimple->nombre : "-")." + ".($Partido->torneoCategoria->categoriaDupla != null ? $Partido->torneoCategoria->categoriaDupla->nombre : "-")) : ($Partido->torneoCategoria->categoriaSimple != null ? $Partido->torneoCategoria->categoriaSimple->nombre : "-")."".($q->multiple ? " (Doble) " : ""),
                        'Fase' => $Partido->fase == null ? "Fase de Grupos" : ($Partido->fase == 16 ? "Deciseisavo de Final" : ($Partido->fase == 8 ? "Octavos de Final" : ($Partido->fase == 4 ? "Cuartos de Final" : ($Partido->fase == 2 ? "Semifinal" : ($Partido->fase == 1 ? ((in_array($Jugador->id, [$Partido->jugador_ganador_uno_id, $Partido->jugador_ganador_dos_id]) ? "Campeón" : "Finalista") ) : "-"))))),
                        'Estado' => $q->estado_texto
                    ];
                }
            }
        }
        return view('auth'.'.'.$this->viewName.'.ajax.jugador.partialView', ['Jugador' => $Jugador, 'HistorialTorneos' => $HistorialTorneos]);
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
        ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
        ->where('id', $categoria)->first();

        if($TorneoCategoria != null)
        {
            $Partidos = Partido::where('comunidad_id',Auth::guard('web')->user()->comunidad_id)
            ->where('torneo_id', $torneo)->where('torneo_categoria_id', $TorneoCategoria->id)->whereNull('fase')->get();

            $Data = array(
                'Torneo' => $TorneoCategoria->torneo,
                'TorneoCategoria' => $TorneoCategoria,
                'Categoria' => $TorneoCategoria->categoriaSimple,
                'Grupos' => TorneoGrupo::where('torneo_categoria_id', $TorneoCategoria->id)->select(['nombre_grupo', 'grupo_id'])->groupBy(['nombre_grupo', 'grupo_id'])
                ->orderBy(DB::raw('LENGTH(nombre_grupo)'))->orderBy('nombre_grupo')->get(),
                'Partidos' => $Partidos,
                'Count' => 0
            );

            $pdf = Pdf::loadView('auth'.'.'.$this->viewName.'.ajax.torneo.partialView', $Data)->setPaper('a4', 'landscape');
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

            //CLASIFICADOS POR CÁLCULO
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

            $Data = array(
                'Torneo' => $TorneoCategoria->torneo,
                'TorneoCategoria' => $TorneoCategoria,
                'Categoria' => $TorneoCategoria->categoriaSimple,
                'Grupos' => TorneoGrupo::where('torneo_categoria_id', $TorneoCategoria->id)->select(['nombre_grupo', 'grupo_id'])->groupBy(['nombre_grupo', 'grupo_id'])
                ->orderBy(DB::raw('LENGTH(nombre_grupo)'))->orderBy('nombre_grupo')->get(),
                'Partidos' => $Partidos,
                'Count' => 0
            );


            $pdf = Pdf::loadView('auth'.'.'.$this->viewName.'.ajax.torneo.partialView', $Data)->setPaper('a4', 'landscape');
            return $pdf->stream("ReporteTorneo.pdf");
        }

        return null;
    }



}
