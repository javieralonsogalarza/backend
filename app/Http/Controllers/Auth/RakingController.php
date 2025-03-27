<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Categoria;
use App\Models\Jugador;
use App\Models\Ranking;
use App\Models\RankingDetalle;
use App\Models\Torneo;
use App\Models\TorneoCategoria;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RakingController extends Controller
{
    protected $viewName = 'ranking';

    public function index()
    {
        $Torneos = Torneo::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->get();

        $TorneoAnioss = Torneo::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->orderBy('fecha_final', 'desc')->pluck('fecha_inicio')->map(function ($q){return Carbon::parse($q)->format('Y');})->toArray();
        $Anio = $TorneoAnioss != null && count($TorneoAnioss) > 0 ? $TorneoAnioss[0] : null;

        return view('auth'.'.'.$this->viewName.'.index', ['Anio' => $Anio, 'Torneos' => $Torneos, 'ViewName' => ucfirst($this->viewName)]);
    }

    public function salon()
    {
        $Torneos = Torneo::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->get();

        $TorneoAnioss = Torneo::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->orderBy('fecha_final', 'desc')->pluck('fecha_inicio')->map(function ($q){return Carbon::parse($q)->format('Y');})->toArray();
        $Anio = $TorneoAnioss != null && count($TorneoAnioss) > 0 ? $TorneoAnioss[0] : null;

        return view('auth'.'.'.$this->viewName.'.salon', ['Anio' => $Anio, 'Torneos' => $Torneos, 'ViewName' => ucfirst($this->viewName)]);
    }

    public function filtroTorneoCategorias(Request $request)
    {
        dd($request->all());
    }

    public function partialView(Request $request)
    {
        $Rankings = Ranking::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
        ->where(function ($q) use ($request){
            if($request->filter_torneo){ $q->where('torneo_id', $request->filter_torneo); }
        })->whereHas('torneo', function ($q) use($request){
            if($request->filter_anio){ $q->whereYear('fecha_inicio', $request->filter_anio); }
        })->get();

        $Torneos = Torneo::whereIn('id', array_values(array_unique(array_filter($Rankings->pluck('torneo_id')->toArray()))))->orderBy('id', 'desc')->get();
        $TorneoCategorias = TorneoCategoria::whereIn('id', array_values(array_unique(array_filter($Rankings->pluck('torneo_categoria_id')->toArray()))))->orderBy('id', 'desc')->get();
        $Categorias = Categoria::whereIn('id', array_values(array_unique(array_filter($TorneoCategorias->pluck('categoria_simple_id')->toArray()))))->where('visible', true)->where('visible', true)->where('id', '!=', 3)->where('orden', '>', '0')->orderBy('id', 'desc')->get();

        $RankingsResult = [];
        foreach ($Categorias as $q)
        {
            $Object = []; $JugadoresIds = [];
            $TorneoCategoria = TorneoCategoria::where('categoria_simple_id', $q->id)->get();

            $Object['categoria_id'] = $q->id;
            $Object['multiple'] = $q->dupla;

            foreach ($TorneoCategoria as $q2)
            {
                foreach($Rankings->where('torneo_categoria_id', $q2->id) as $q3)
                {
                    if($q3->detalles != null && count($q3->detalles) > 0)
                    {
                        foreach($q3->detalles as $q4)
                        {
                            $Id = $q->dupla ? ($q4->jugadorSimple->id.'-'.$q4->jugadorDupla->id) : $q4->jugadorSimple->id;
                            if(!in_array($Id, $JugadoresIds))
                            {
                                $ObjectJugador = []; $Puntos = 0;
                                $ObjectJugador['id'] = $Id;
                                $ObjectJugador['nombre'] = $q->dupla ? ($q4->jugadorSimple->nombre_completo.' + '.$q4->jugadorDupla->nombre_completo) : $q4->jugadorSimple->nombre_completo;

                                foreach($Torneos as $q5)
                                {
                                    $rankingDetalle =  RankingDetalle::whereHas('ranking', function ($query) use ($q5){
                                        $query->where('torneo_id', $q5->id);
                                    })->where(function ($query) use ($q, $q4){
                                        $query->where('jugador_simple_id', $q4->jugadorSimple->id);
                                        if($q->dupla){$query->where('jugador_dupla_id', $q4->jugadorDupla->id);}
                                    })->first();

                                    $Puntos += $rankingDetalle != null ? $rankingDetalle->puntos : 0;

                                    $ObjectTorneo = [];
                                    $ObjectTorneo['id'] = $q5->id;
                                    $ObjectTorneo['nombre'] = $q5->nombre;
                                    $ObjectTorneo['puntos'] = $rankingDetalle != null ? $rankingDetalle->puntos : 0;
                                    $ObjectJugador['torneos'][] = (object)$ObjectTorneo;
                                }
                                $ObjectJugador['puntos'] = $Puntos;
                                $Object['jugadores'][] = $ObjectJugador;

                                $JugadoresIds[] = $Id;
                            }
                        }
                    }
                }
            }
            $RankingsResult[] = (object)$Object;
        }

        return view('auth'.'.'.$this->viewName.'.ajax.partialView', [
            'Torneos' => $Torneos,
            'Landing' => false,
            'Rankings' => collect($RankingsResult),
            'RankingsResultYear' => null,
            'TorneoCategorias' => $TorneoCategorias,
            'Categorias' => $Categorias,
            'filterCategoria' => null]);
    }
    
    
public function getTorneosByCategorias(Request $request)
{
    // Validar que se haya enviado una categoría
    if (!$request->has('categoria_id')) {
        return response()->json([]);
    }

    // Si se solicitan solo torneos con carrera
    if ($request->has('carrera')) {
        // Obtener los 4 torneos más recientes con carrera para la categoría
        $torneos = Torneo::join('torneo_categorias', 'torneos.id', '=', 'torneo_categorias.torneo_id')
            ->where('torneo_categorias.categoria_simple_id', $request->categoria_id)
           ->where('torneos.fecha_inicio', '<=', Carbon::now()->format('Y-m-d'))
            ->whereNull('torneos.deleted_at')
            ->whereNull('torneo_categorias.deleted_at')
            ->whereIn('torneo_categorias.estado_id', [1, 2])
            ->where('torneos.rankeado', true)
            ->select(
                'torneo_categorias.id', 
                'torneos.nombre', 
                'torneos.fecha_inicio', 
                'torneos.fecha_final',
                'torneos.carrera'
            )
            ->orderBy('torneos.fecha_inicio', 'desc')
            ->limit(4)
            ->get()
            ->groupBy(function($torneo) {
                return Carbon::parse($torneo->fecha_inicio)->format('Y');
            });
    } else {
        // Consulta para torneos normales
        $torneos = TorneoCategoria::join('torneos', 'torneo_categorias.torneo_id', '=', 'torneos.id')
            ->where('torneo_categorias.categoria_simple_id', $request->categoria_id)
             ->where('torneos.fecha_inicio', '<=', Carbon::now()->format('Y-m-d'))
            ->whereNull('torneos.deleted_at')
            ->whereNull('torneo_categorias.deleted_at')
            ->where('torneos.rankeado', true)
            ->whereIn('torneo_categorias.estado_id', [1, 2])
            ->select(
                'torneo_categorias.id', 
                'torneos.nombre', 
                'torneos.fecha_inicio', 
                'torneos.fecha_final',
                'torneos.carrera'
            )
            ->orderBy('torneos.fecha_inicio', 'desc')
            ->get()
            ->groupBy(function($torneo) {
                return Carbon::parse($torneo->fecha_inicio)->format('Y');
            });
    }

    // Formatear los torneos para el select2
    $formattedTorneos = [];
    foreach ($torneos as $year => $torneosPorAnio) {
        $yearGroup = [
            'text' => $year,
            'children' => $torneosPorAnio->map(function($torneo) {
                return [
                    'id' => $torneo->id,
                    'text' => $torneo->nombre,
                    'carrera' => $torneo->carrera == 1 ? true : false,
                ];
            })->toArray()
        ];
        $formattedTorneos[] = $yearGroup;
    }

    return response()->json($formattedTorneos);
}

public function updateRankingConsideration(Request $request)
{
    // Validar la solicitud
    $request->validate([
        'ranking_ids' => 'required',
        'categoria_id' => 'required',
        'considerado' => 'required',
        'player_id' => 'required'
    ]);

    try {
        // Convertir los ranking_ids en un array
        $rankingIds = explode(',', $request->ranking_ids);

        // Buscar y actualizar los registros de ranking_detalle
        $updated = RankingDetalle::whereIn('ranking_id', $rankingIds)
            ->where(function($query) use ($request) {
                // Añadir condiciones adicionales si es necesario
                $query->where('jugador_simple_id', $request->player_id)
                      ->orWhere('jugador_dupla_id', $request->player_id);
            })
->update([
    'considerado_ranking' => $request->considerado === 'true' ? 1 : 0
]);

        // Depuración: Log de registros actualizados
        \Log::info('Registros actualizados: ' . $updated);

        return response()->json([
            'success' => true, 
            'message' => 'Consideración de ranking actualizada',
            'updated' => $updated
        ]);

    } catch (\Exception $e) {
        // Log del error
        \Log::error('Error al actualizar consideración de ranking: ' . $e->getMessage());

        return response()->json([
            'success' => false, 
            'message' => 'Error al actualizar consideración',
            'error' => $e->getMessage()
        ], 500);
    }
}

}
