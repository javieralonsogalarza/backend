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

}
