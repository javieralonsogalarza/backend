<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Categoria;
use App\Models\Comunidad;
use App\Models\Galeria;
use App\Models\Jugador;
use App\Models\Pagina;
use App\Models\Partido;
use App\Models\Portada;
use App\Models\Ranking;
use App\Models\RankingDetalle;
use App\Models\Torneo;
use App\Models\TorneoCategoria;
use Carbon\Carbon;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    public function index($landing = null)
    {
        $Model = Comunidad::where('principal', true)->first();
        return view('app.index', ['Model' => $Model, 'Galerias' => Galeria::orderBy('id', 'desc')->get(), 'Portadas' => Portada::orderBy('id', 'desc')->get()]);
    }

    public function torneos($landing = null)
    {
        $Model = Comunidad::where('principal', true)->first();
        if($Model != null)
        {
            $Torneos = Torneo::where('comunidad_id', $Model->id)->orderBy('fecha_final', 'desc')->pluck('fecha_inicio')->map(function ($q){return Carbon::parse($q)->format('Y');})->toArray();
            $Anio = $Torneos != null && count($Torneos) > 0 ? $Torneos[0] : null;
            return view('app.torneos', ['Model' => $Model, 'Anio' => $Anio]);
        }else{
            abort(404);
        }
    }

    public function torneosAnios(Request $request)
    {
        $Model = Comunidad::where('principal', true)->first();

        $list = Torneo::where('comunidad_id', $Model->id)
        ->where(function ($q) use ($request){
            if($request->nombre){ $q->where(DB::raw('YEAR(fecha_inicio)'), 'like', '%'.$request->nombre.'%'); }
        })
        ->orderBy('fecha_inicio', 'desc')->pluck('fecha_inicio')->map(function ($q){return Carbon::parse($q)->format('Y');})->toArray();

        $list = collect(array_values(array_unique($list)))->map(function ($q){return ['id' => $q, 'text' => $q];});

        return response()->json(['data' => $list]);
    }

    public function torneoTodos(Request $request)
    {
        $Model = Comunidad::where('principal', true)->first();

        $list = Torneo::where('comunidad_id', $Model->id)
            ->where(function ($q) use ($request){
                if($request->filter_anio){ $q->where(DB::raw('YEAR(fecha_inicio)'), '=', $request->filter_anio); }
                if($request->nombre){ $q->where('nombre', 'like', '%'.$request->nombre.'%'); }
            })
            ->orderBy('fecha_final', 'desc')->get();

        $list = $list->map(function ($q){return ['id' => $q->id, 'text' => $q->nombre];});

        return response()->json(['data' => $list]);
    }

    public function torneoMejores5(Request $request)
    {
        $Model = Comunidad::where('principal', true)->first();

        $list = Torneo::where('comunidad_id', $Model->id)
        ->where(DB::raw('YEAR(fecha_inicio)'), '=', $request->filter_anio)
        ->where(function ($q) use ($request){
            if($request->nombre){ $q->where('nombre', 'like', '%'.$request->nombre.'%'); }
        })
        ->orderBy('fecha_final', 'desc')->take(5)->get();

        $list = $list->map(function ($q){return ['id' => $q->id, 'text' => $q->nombre];});

        return response()->json(['data' => $list]);
    }

    public function rankings($landing = null)
    {
        $Model = Comunidad::where('principal', true)->first();
        if($Model != null)
        {
            $Torneos = Torneo::where('comunidad_id', $Model->id)->orderBy('fecha_final', 'desc')->pluck('fecha_inicio')->map(function ($q){return Carbon::parse($q)->format('Y');})->toArray();
            $Anio = $Torneos != null && count($Torneos) > 0 ? $Torneos[0] : null;

            $Anios = App::arregloAnios();
            $Torneos = Torneo::where('comunidad_id', $Model->id)->get();
            return view('app.rankings', ['Model' => $Model, 'Anio' => $Anio, 'Anios' => $Anios, 'Torneos' => $Torneos]);
        }else{
            abort(404);
        }
    }


    public function rankingsCategorias(Request $request)
    {
        $list = [];

        $listWithOrden = Categoria::where('visible', true)->where('orden', '>', '0')
        ->where(function($q) use ($request){ if($request->nombre){ $q->where('nombre', 'like',  '%'.$request->nombre.'%'); }})
        ->orderBy('orden', 'asc')->get();

        $listWithOutOrden = Categoria::where('visible', true)->where('orden', '=', '0')
        ->where(function($q) use ($request){ if($request->nombre){ $q->where('nombre', 'like',  '%'.$request->nombre.'%'); }})
        ->orderBy('nombre', 'asc')->get();

        foreach ($listWithOrden as $item) $list[] = (object)['id' => $item->id, 'nombre' => $item->nombre, 'orden' => $item->orden];
        foreach ($listWithOutOrden as $item) $list[] = (object)['id' => $item->id, 'nombre' => $item->nombre, 'orden' => $item->orden];

        $list = collect($list)->map(function ($q){return ['id' => $q->id, 'text' => $q->nombre];});

        return response()->json(['data' => $list]);
    }

    public function rankingsPartialView(Request $request)
    {
        $Model = Comunidad::where('principal', true)->first();

        if($Model != null)
        {
            $Rankings = Ranking::where('comunidad_id', $Model->id)->get();

            $Torneos = Torneo::whereIn('id', array_values(array_unique(array_filter($Rankings->pluck('torneo_id')->toArray()))))
            ->where(function ($q) use ($request){
                if($request->filter_anio){ $q->where(DB::raw('YEAR(fecha_inicio)'), '=', $request->filter_anio); }
            })->where('rankeado', true)->orderBy('fecha_final', 'desc')->get();

            $Anios = $request->filter_anio == null ? array_values(array_unique($Torneos->pluck('fecha_inicio')->map(function ($date){ return Carbon::parse($date)->format('Y'); })->toArray())) : [];

            $TorneoCategorias = TorneoCategoria::whereIn('id', array_values(array_unique(array_filter($Rankings->pluck('torneo_categoria_id')->toArray()))))->orderBy('id', 'desc')->get();
            $Categorias = Categoria::whereIn('id', array_values(array_unique(array_filter($TorneoCategorias->pluck('categoria_simple_id')->toArray()))))
            ->where('visible', true)->where('id', '!=', 3)->where('orden', '>', '0')
            ->where(function ($q) use ($request){
                if($request->filter_categoria){ $q->where('id', $request->filter_categoria); }
            })->orderBy('id', 'desc')->get();

            $RankingsResult = [];
            foreach ($Categorias as $q)
            {
                $Object = []; $JugadoresIds = [];
                $TorneoCategoria = TorneoCategoria::where('categoria_simple_id', $q->id)->get();

                $Object['categoria_id'] = $q->id;
                $Object['multiple'] = $q->dupla;
                foreach ($TorneoCategoria as $q2)
                {
                    foreach ($Rankings->where('torneo_categoria_id', $q2->id) as $q3)
                    {
                        if ($q3->detalles != null && count($q3->detalles) > 0)
                        {
                            foreach ($q3->detalles as $q4)
                            {
                                $Id = $q->dupla ? ($q4->jugadorSimple->id . '-' . $q4->jugadorDupla->id) : $q4->jugadorSimple->id;
                                if (!in_array($Id, $JugadoresIds))
                                {
                                    $ObjectJugador = [];
                                    $Puntos = 0;
                                    $ObjectJugador['id'] = $Id;
                                    $ObjectJugador['nombre'] = $q->dupla ? ($q4->jugadorSimple->nombre_completo . ' + ' . $q4->jugadorDupla->nombre_completo) : $q4->jugadorSimple->nombre_completo;

                                    foreach ($Torneos as $q5)
                                    {
                                        $ObjectTorneo = [];
                                        $ObjectTorneo['id'] = $q5->id;
                                        $ObjectTorneo['anio'] = Carbon::parse($q5->fecha_inicio)->format('Y');
                                        $ObjectTorneo['nombre'] = $q5->nombre;

                                        if(count($TorneoCategoria->where('torneo_id', $q5->id)) > 0)
                                        {
                                            foreach ($TorneoCategoria->where('torneo_id', $q5->id) as $q9)
                                            {
                                                $ObjectTorneoCategoria = [];

                                                $rankingDetalle = RankingDetalle::whereHas('ranking', function ($query) use ($q9, $q5) {
                                                    $query->where('torneo_id', $q5->id);
                                                    $query->where('torneo_categoria_id', $q9->id);
                                                })->where(function ($query) use ($q, $q4) {
                                                    $query->where('jugador_simple_id', $q4->jugador_simple_id);
                                                    if ($q->dupla) { $query->where('jugador_dupla_id', $q4->jugadorDupla->id);}
                                                })->first();

                                                $Puntos += $rankingDetalle != null ? $rankingDetalle->puntos : 0;

                                                $ObjectTorneoCategoria['torneo_categoria_id'] = $q9->id;
                                                $ObjectTorneoCategoria['multiple'] = $q9->multiple;
                                                $ObjectTorneoCategoria['categoria_simple_id'] = $q9->categoria_simple_id;
                                                $ObjectTorneoCategoria['categoria_dupla_id'] = $q9->categoria_dupla_id;

                                                $ObjectTorneoCategoria['ranking_id'] = $rankingDetalle != null ? $rankingDetalle->ranking_id : null;
                                                $ObjectTorneoCategoria['puntos'] = $rankingDetalle != null ? $rankingDetalle->puntos : 0;

                                                $ObjectTorneo['categorias'][]  = (object)$ObjectTorneoCategoria;
                                            }
                                        }else{
                                            $ObjectTorneo['categorias'] = [];
                                        }

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

            $RankingsResultYear = null;

            if($request->filter_anio == null)
            {
                $RankingsResultYear = [];

                foreach ($RankingsResult as $q)
                {
                    $ResultYear = [];
                    $ResultYear['categoria_id'] = $q->categoria_id;
                    $ResultYear['multiple'] = $q->multiple;

                    foreach ($q->jugadores as $q2)
                    {
                        $ResultYearJugador = []; $Puntos = 0;
                        $ResultYearJugador['nombre'] = $q2['nombre'];

                        foreach ($Anios as $q3)
                        {
                            $ResultYearJugadorAnio = [];
                            $ResultYearJugadorAnio['anio'] = $q3;
                            $ResultYearJugadorAnio['puntos'] = 0;

                            $TorneosPuntos = collect($q2['torneos'])->where('anio', $q3)->whereNotNull('categorias')->pluck('categorias');

                            foreach ($TorneosPuntos as $q4){
                                $ResultYearJugadorAnio['puntos'] += count($q4) > 0 ? $q4[0]->puntos : 0;
                            }

                            $ResultYearJugador['anios'][] = (object)$ResultYearJugadorAnio;

                            $Puntos += $ResultYearJugadorAnio['puntos'];
                        }

                        $ResultYearJugador['puntos'] = $Puntos;

                        $ResultYear['jugadores'][] = $ResultYearJugador;
                    }

                    $RankingsResultYear[] = (object)$ResultYear;
                }
            }

            return view('auth.ranking.ajax.partialView', ['Torneos' => $Torneos,
            'Rankings' => collect($RankingsResult), 'Anios' => $Anios,
            'Landing' => true,
            'RankingsResultYear' => $RankingsResultYear != null ? collect($RankingsResultYear) : null,
            'TorneoCategorias' => $TorneoCategorias,
            'Categorias' => $Categorias,
            'filterCategoria' => $request->filter_categoria]);

        }else{
            abort(404);
        }
    }


    public function jugadores($landing = null)
    {
        $Model = Comunidad::where('principal', true)->first();
        if($Model != null)
        {
            $Jugadores = Jugador::where('comunidad_id', $Model->id)->orderBy('nombres')->get();
            return view('app.jugadores', ['Model' => $Model, 'Jugadores' => $Jugadores]);
        }else{
            abort(404);
        }
    }

    public function jugadorPartialView(Request $request)
    {
        $Model = Comunidad::where('principal', true)->first();

        $Jugador = Jugador::where('id', $request->filter_jugador)->where('comunidad_id', $Model->id)->first();

        $Torneos = Torneo::where('comunidad_id', $Model->id)->get();

        $TorneoCategorias = TorneoCategoria::all();

        $HistorialTorneos = [];

        if($Jugador != null && count($Torneos) > 0)
        {
            foreach ($Torneos as $q)
            {
                foreach ($TorneoCategorias->where('torneo_id', $q->id) as $q2)
                {
                    //FaseDelJugador
                    $Partido =  Partido::where('comunidad_id', $Model->id)->where('torneo_id', $q->id)
                        ->where('torneo_categoria_id', $q2->id)
                        ->where(function ($q) use ($Jugador){$q->where('jugador_local_uno_id', $Jugador->id)->orWhere('jugador_local_dos_id', $Jugador->id)->orWhere('jugador_local_dos_id', $Jugador->id)->orWhere('jugador_rival_uno_id', $Jugador->id)->orWhere('jugador_rival_dos_id', $Jugador->id);})
                        ->orderBy('id', 'desc')->first();

                    if($Partido != null)
                    {
                        $PartidoFaseNext = $Partido->fase == null ? null :  ($Partido->fase == 1 ? 1 : ($Partido->fase/2));

                        $PartidoNext = null;

                        if($Partido->estado_id == App::$ESTADO_FINALIZADO)
                        {
                            $PartidoNext = Partido::where('comunidad_id', $Model->id)->where('torneo_id', $q->id)
                                ->where('torneo_categoria_id', $q2->id)->where('fase', $PartidoFaseNext)
                                //->where(function ($q) use ($Jugador){$q->where('jugador_local_uno_id', $Jugador->id)
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
                            'Estado' => $q->estado_texto,
                            'estado_id' => $q->estado_id,
                            'TorneoCategoria' => $Partido->torneoCategoria,
                            'fecha_final' => $q->fecha_final,
                            'Periodo' => ($q->fecha_inicio_texto." - ".$q->fecha_final_texto),
                            'Categoria' => $q->multiple && ($Partido->torneoCategoria->categoria_simple_id !== $Partido->torneoCategoria->categoria_dupla_id) ? (($Partido->torneoCategoria->categoriaSimple != null ? $Partido->torneoCategoria->categoriaSimple->nombre : "-")." + ".($Partido->torneoCategoria->categoriaDupla != null ? $Partido->torneoCategoria->categoriaDupla->nombre : "-")) : ($Partido->torneoCategoria->categoriaSimple != null ? $Partido->torneoCategoria->categoriaSimple->nombre : "-")."".($q->multiple ? " (Doble) " : ""),
                            'Fase' => $Partido->fase == null ? "Fase de Grupos" : ($Partido->fase == 16 ? "Ronda de 32" : ($Partido->fase == 8 ? "Octavos de Final" : ($Partido->fase == 4 ? "Cuartos de Final" : ($Partido->fase == 2 ? "Semifinal" : ($Partido->fase == 1 ? ((in_array($Jugador->id, [$Partido->jugador_ganador_uno_id, $Partido->jugador_ganador_dos_id]) ? "Campeón" : "Finalista") ) : "-"))))),
                            'Participacion' => in_array($q->estado_id, [App::$ESTADO_CANCELADO, App::$ESTADO_FINALIZADO]) ? 'Participación terminada' : ($Partido->estado_id == App::$ESTADO_PENDIENTE ? 'Participación en curso' : ($PartidoNext == null ? 'Participación terminada' : ($PartidoNext->estado_id == App::$ESTADO_PENDIENTE ? 'Participación en curso' : 'Participación terminada') ))
                        ];
                    }
                }
            }
        }

        return view('app.reporte.ajax.jugador.partialView', ['Jugador' => $Jugador,
            'HistorialTorneos' => App::multiPropertySort(collect($HistorialTorneos), [['column' => 'estado_id', 'order' => 'asc'], ['column' => 'fecha_final', 'order' => 'desc']])->toArray() ]);
    }

    public function jugadorPartidosPartialView(Request $request)
    {
        $Data = null;

        $Model = Comunidad::where('principal', true)->first();

        $TorneoCategoria = TorneoCategoria::where('torneo_id', $request->filter_torneo)
            ->whereHas('torneo', function ($q) use($Model) {$q->where('comunidad_id', $Model->id);})
            ->where('id', $request->filter_category)->first();

        if($TorneoCategoria != null)
        {
            $Jugador = Jugador::where('id', $request->filter_jugador)->where('comunidad_id', $Model->id)->first();

            if($Jugador != null)
            {
                $Partidos = Partido::where('comunidad_id', $Model->id)
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

        return view('app.reporte.ajax.jugador.partido.partialView', ['Data' => $Data]);
    }


    public function contactanos(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try {

            $Model = Comunidad::where('principal', true)->first();

            $Validator = Validator::make($request->all(), [
                'g-recaptcha-response' => 'required',
                'nombres' => 'required',
                'apellidos' => 'required',
                'celular' => 'required|min:6|max:15',
                'email' => 'required|email',
                'mensaje' => 'required'
            ]);

            if (!$Validator->fails())
            {
                $ResultApi = App::ApiServiceCaptcha($request->get('g-recaptcha-response'));

                if($ResultApi->Success)
                {
                    $modelEmail = [
                        'to' =>  $Model->email,
                        'subject' => 'Contáctanos',
                        'email' => $request->email,
                        'nombres' => $request->nombres,
                        'apellidos' => $request->apellidos,
                        'celular' => $request->celular,
                        'mensaje' => $request->mensaje
                    ];

                    Mail::send('app.email.contacto', $modelEmail, function($message) use($modelEmail){
                        $message->to($modelEmail['to'])->subject($modelEmail['subject']);
                    });

                    $Result->Success = true;

                }else{
                    $Result->Message = "Inválido Captcha, por favor vuelva a intentarlo.";
                }
            }

            $Result->Errors = $Validator->errors();

        }catch (\Exception $e){
            $Result->Message = $e->getMessage();
        }

        return response()->json($Result);
    }


}
