<?php

namespace App\Http\Controllers\Auth;

use App\Exports\TorneoJugadorExport;
use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Categoria;
use App\Models\Comunidad;
use App\Models\Formato;
use App\Models\Grupo;
use App\Models\Jugador;
use App\Models\Partido;
use App\Models\Puntuacion;
use App\Models\Ranking;
use App\Models\RankingDetalle;
use App\Models\Torneo;
use App\Models\TorneoCategoria;
use App\Models\TorneoGrupo;
use App\Models\TorneoJugador;
use App\Models\TorneoZona;
use App\Models\Zona;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class TorneoController extends Controller
{
    protected $viewName = 'torneo';

    protected $lits_for_page = 50;

    /*TORNEO*/
    public function index(Request $request)
    {
        $Comunidad = $request->landing ? Comunidad::where('principal', true)->first() : Auth::guard('web')->user()->comunidad;

        if($request->ajax())
        {
            $list = Torneo::where('comunidad_id', $Comunidad->id)
                ->where(function ($q) use ($request){
                    if($request->filter_anio){
                        $q->where(DB::raw('YEAR(fecha_inicio)'), '=', $request->filter_anio);
                    }
                })

                /*->where(function ($q) use ($request){
                $q->where(function ($q2) use ($request){
                    $q2->whereDate('fecha_inicio', '>=', $request->fecha_inicio)->whereDate('fecha_inicio', '<=' ,$request->fecha_final);
                })
                /*->orWhere(function ($q2) use ($request){
                    $q2->whereDate('fecha_final', '>=', $request->fecha_inicio)->whereDate('fecha_final', '<=' ,$request->fecha_final);
                })*/
                /*->orWhere(function ($q2) use ($request){
                    $q2->whereDate('fecha_final', '>=', $request->fecha_inicio)->whereDate('fecha_final', '<=' ,$request->fecha_final);
                });
            })*/
            ->orderBy('created_at', 'desc')
            ->paginate($this->lits_for_page);

            return [
                'lists' => view('auth.'.$this->viewName.'.ajax.listado')->with(['lists' => $list, 'i' => ($this->lits_for_page*($list->currentPage()-1)+1), 'landing' => $request->landing ])->render(),
                'next_page' => $list->nextPageUrl()
            ];
        }

        $Torneos = Torneo::where('comunidad_id', $Comunidad->id)->orderBy('fecha_final', 'desc')->pluck('fecha_inicio')->map(function ($q){return Carbon::parse($q)->format('Y');})->toArray();
        $Anio = $Torneos != null && count($Torneos) > 0 ? $Torneos[0] : null;

        return view('auth'.'.'.$this->viewName.'.index', ['ViewName' => ucfirst($this->viewName), 'Anio' => $Anio]);
    }

    public function partialView($id)
    {
        $entity = null;

        if($id != 0) $entity = Torneo::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->where('id', $id)->first();

        $Categorias = Categoria::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->get();

        $Formatos = Formato::all();

        $Zonas = Zona::all();

        return view('auth'.'.'.$this->viewName.'.ajax.partialView', ['Model' => $entity, 'Zonas' => $Zonas, 'Categorias' => $Categorias, 'Formatos' => $Formatos, 'ViewName' => ucfirst($this->viewName)]);
    }

    public function partialViewBackground($id, $torneo_category)
    {
        $TorneoCategory = TorneoCategoria::where('id', $torneo_category)->where('torneo_id', $id)->first();
        return view('auth'.'.'.$this->viewName.'.ajax.partialViewBackground', ['Model' => $TorneoCategory, 'ViewName' => ucfirst($this->viewName)]);
    }

    public function updateBackground(Request $request)
    {
        $entity = null; $imagen_path = null;

        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try {

            DB::beginTransaction();

            if($request->file('imagen')) $imagen_path = $request->imagen->store('public/uploads/torneos/categorias');

            if($request->id != 0) $entity = TorneoCategoria::where('id', $request->id)->first();

            $request->merge([
                'imagen_path' => $entity != null ? ($imagen_path == null && filter_var($request->remove_file, FILTER_VALIDATE_BOOL) ? null : ($imagen_path != null ? $imagen_path : $entity->imagen_path)) : ($imagen_path ?? null),
                'user_update_id' =>  Auth::guard('web')->user()->id
            ]);

            if($entity != null) {
                $entity->update($request->only('color_rotulos', 'color_participantes', 'imagen_path', 'user_update_id'));
                DB::commit();
                $Result->Success = true;
            }else{
                $Result->Message = "El torneo categoría que intenta modificar, ya no se encuentra disponible";
            }

        }catch (\Exception $e)
        {
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }

        return response()->json($Result);
    }

    public function store(Request $request)
    {
        $entity = null; $imagen_path = null;

        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try {

            DB::beginTransaction();

            $request->merge([
                //'fecha_actual' => Carbon::now()->toDateString(),
                'valor_set' => 1,
                'rankeado' => filter_var($request->rankeado, FILTER_VALIDATE_BOOLEAN)
            ]);

            $Validator = Validator::make($request->all(), [
                'nombre' => 'required|max:150|unique:torneos,nombre,'.($request->id != 0 ? $request->id : "NULL").',id,comunidad_id,'.Auth::guard('web')->user()->comunidad_id.',deleted_at,NULL',
                'valor_set' => 'required|numeric',
                'formato_id' => 'required|max:150',
                //'fecha_inicio' => 'required|date|date_format:Y-m-d|after_or_equal:fecha_actual',
                'fecha_inicio' => 'required|date|date_format:Y-m-d',
                'fecha_final' => 'required|date|date_format:Y-m-d|after_or_equal:fecha_inicio',
                'categorias' => 'required|array',
                'zonas_id' => 'required|array'
            ], [
                'formato_id.required' => 'El campo formato  es obligatorio',
                'categorias.required' => 'Por favor, agregue al menos una categoría al torneo.',
                'zonas_id.required' => 'Por favor, agregue al menos una zona al torneo.'
            ]);

            if (!$Validator->fails()){

                if($request->categorias == null){
                    $Result->Message = "Por favor, seleccione al menos una categoría.";
                    return response()->json($Result);
                }

                if($request->file('imagen')) $imagen_path = $request->imagen->store('public/uploads/torneos');

                if($request->id != 0) $entity = Torneo::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->where('id', $request->id)->first();

                $request->merge([
                    'comunidad_id' => Auth::guard('web')->user()->comunidad_id,
                    'imagen_path' => $entity != null ? ($imagen_path ?? $entity->imagen_path) : ($imagen_path ?? null)
                ]);

                if($entity != null)
                {
                    $request->merge(['user_update_id' =>  Auth::guard('web')->user()->id]);
                    $entity->update($request->all());

                    if(count($entity->torneoCategorias->whereIn('estado_id', [App::$ESTADO_PENDIENTE, App::$ESTADO_CANCELADO])) == 0)
                        $entity->update(['estado_id' => App::$ESTADO_FINALIZADO]);

                    TorneoZona::where('torneo_id', $entity->id)->delete();

                    $ToneoCategorias = []; $i = $entity->torneoCategorias()->orderBy('id', 'desc')->first() != null ? $entity->torneoCategorias()->orderBy('id', 'desc')->first()->sector + 1 : 1;

                    $Categorias = $entity->torneoCategorias;

                    if(count($Categorias) > 0)
                    {
                        foreach ($Categorias as $q)
                        {
                            if($q->multiple){
                                if(!in_array($q->categoria_simple_id."-".$q->categoria_dupla_id, $request->categorias)) $entity->torneoCategorias()->where('id', $q->id)->update(['user_update_id' =>  Auth::guard('web')->user()->id, 'deleted_at' => Carbon::now()]);
                            }else{
                                if(!in_array($q->categoria_simple_id, $request->categorias)) $entity->torneoCategorias()->where('id', $q->id)->update(['user_update_id' =>  Auth::guard('web')->user()->id, 'deleted_at' => Carbon::now()]);
                            }
                        }

                        $Categorias = $Categorias->map(function($obj, $key) use ($request){return $obj->multiple ? ($obj->categoria_simple_id."-".$obj->categoria_dupla_id) : $obj->categoria_simple_id;})->toArray();

                        foreach ($request->categorias as $q){
                            if(!in_array($q, $Categorias)){
                                $categorias = explode("-", $q);
                                if(count($categorias) == 1){ $ToneoCategorias[] = ['torneo_id' => $entity->id, 'categoria_simple_id' => $categorias[0], 'categoria_dupla_id' => null, 'sector' => $i, 'multiple' => false, 'user_create_id' => Auth::guard('web')->user()->id];}
                                else{ $ToneoCategorias[] = ['torneo_id' => $entity->id, 'categoria_simple_id' => $categorias[0], 'categoria_dupla_id' => $categorias[1], 'sector' => $i, 'multiple' => true, 'user_create_id' => Auth::guard('web')->user()->id];}
                                $i++;
                            }
                        }

                    }else{
                        foreach ($request->categorias as $q){
                            $categorias = explode("-", $q);
                            if(count($categorias) == 1){ $ToneoCategorias[] = ['torneo_id' => $entity->id, 'categoria_simple_id' => $categorias[0], 'categoria_dupla_id' => null, 'sector' => $i, 'multiple' => false, 'user_create_id' => Auth::guard('web')->user()->id];}
                            else{ $ToneoCategorias[] = ['torneo_id' => $entity->id, 'categoria_simple_id' => $categorias[0], 'categoria_dupla_id' => $categorias[1], 'sector' => $i, 'multiple' => true, 'user_create_id' => Auth::guard('web')->user()->id];}
                            $i++;
                        }
                    }

                }else{

                    $request->merge(['user_create_id' =>  Auth::guard('web')->user()->id]);
                    $entity = Torneo::create($request->all());

                    $ToneoCategorias = [];  $i = 1;

                    if($request->categorias != null)
                    {
                        foreach ($request->categorias as $key => $q) {
                            $categorias = explode("-", $q);
                            if(count($categorias) == 1){$ToneoCategorias[] = ['torneo_id' => $entity->id, 'categoria_simple_id' => $categorias[0], 'categoria_dupla_id' => null, 'orden' => $key, 'sector' => $i, 'multiple' => false, 'user_create_id' => Auth::guard('web')->user()->id];}
                            else{$ToneoCategorias[] = ['torneo_id' => $entity->id, 'categoria_simple_id' => $categorias[0], 'categoria_dupla_id' => $categorias[1], 'orden' => $key, 'sector' => $i, 'multiple' => true, 'user_create_id' => Auth::guard('web')->user()->id];}
                            $i++;
                        }
                    }
                }

                if(count($ToneoCategorias) > 0) TorneoCategoria::insert($ToneoCategorias);

                if(count($request->zonas_id) > 0){foreach ($request->zonas_id as $q) TorneoZona::create(['torneo_id' => $entity->id, 'zona_id' => $q]); }

                DB::commit();

                $Result->Success = true;
            }

            $Result->Errors = $Validator->errors();

        }catch (\Exception $e)
        {
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }

        return response()->json($Result);
    }

    public function delete(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null];

        try
        {
            DB::beginTransaction();

            Torneo::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->where('id', $request->id)
            ->update(['user_update_id' => Auth::guard('web')->user()->id, 'deleted_at' => Carbon::now()]);

            DB::commit();

            $Result->Success = true;

        }catch (\Exception $e){
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }

        return response()->json($Result);
    }

    public function finish(Request $request)
    {
        $entity = null;

        $Result = (object)['Success' => false, 'Message' => null];

        try {

            DB::beginTransaction();

            $TorneoCategoria = TorneoCategoria::where('id', $request->torneo_categoria_id)->where('torneo_id', $request->torneo_id)
            ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})->first();

            if($TorneoCategoria != null)
            {
                $Rankings = collect(array_values(collect(json_decode($request->rakings))->sortBy('id')->toArray()));

                $Ranking = Ranking::where('torneo_id', $TorneoCategoria->torneo_id)->where('torneo_categoria_id', $request->torneo_categoria_id)
                ->where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->first();

                if($Ranking != null){
                    $entity = $Ranking;
                    $entity->user_update_id = Auth::guard('web')->user()->id;
                    RankingDetalle::where('ranking_id', $entity->id)->delete();
                }else{
                    $entity = new Ranking();
                    $entity->torneo_id = $TorneoCategoria->torneo_id;
                    $entity->torneo_categoria_id = $TorneoCategoria->id;
                    $entity->multiple = $TorneoCategoria->multiple;
                    $entity->comunidad_id = Auth::guard('web')->user()->comunidad_id;
                    $entity->user_create_id = Auth::guard('web')->user()->id;
                }

                $TorneoCategoria->estado_id = App::$ESTADO_FINALIZADO;

                $RankingDetalle = [];

                if($entity->save() && $TorneoCategoria->save())
                {
                    $Torneo = Torneo::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
                    ->where('id', $TorneoCategoria->torneo_id)->first();

                    if(count($Torneo->torneoCategorias->whereIn('estado_id', [App::$ESTADO_PENDIENTE, App::$ESTADO_CANCELADO])) == 0)
                        $TorneoCategoria->torneo->update(['estado_id' => App::$ESTADO_FINALIZADO]);

                    if($Rankings != null && count($Rankings) > 0)
                    {
                        foreach($Rankings as $q)
                        {
                            $RankingDetalle[] = [
                                'ranking_id' => $entity->id,
                                'jugador_simple_id' => $q->jugador_simple_id,
                                'jugador_dupla_id' => $TorneoCategoria->multiple ? $q->jugador_dupla_id : null,
                                'puntos' => $q->puntos != null ? $q->puntos : 0,
                            ];
                        }

                        if(count($RankingDetalle) > 0) RankingDetalle::insert($RankingDetalle);
                    }

                    DB::commit();
                    $Result->Success = true;
                }

            }else{
                $Result->Message = "El torneo categoria que intenta modificar, ya no se encuentra disponible";
            }

        }catch (\Exception $e){
            $Result->Message = $e->getMessage();
        }

        return response()->json($Result);
    }

    public function categorias(Request $request)
    {
        $Categorias = TorneoCategoria::where('torneo_id', $request->torneo)
        ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
        ->get()->pluck('categoriaSimple')->map(function ($q){return ['id' => $q->id, 'nombre' => $q->nombre];});

        return response()->json(['data' => $Categorias]);
    }

    public function categoriaStore(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null];

        try {
            $TorneoCategoria = TorneoCategoria::where('id', $request->torneo_categoria_id)
            ->where('torneo_id', $request->torneo_id)->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})->first();

            $TorneoCategoria->clasificados = $request->clasificados;

            if($request->clasificados != 3) $TorneoCategoria->clasificados_terceros = 0;
            else{

                $TorneoGrupos = $TorneoCategoria->torneo->torneoGrupos()->where('torneo_categoria_id', $TorneoCategoria->id)->select('grupo_id')->groupBy('grupo_id')->count();

                if($request->clasificados_terceros > $TorneoGrupos){
                    $Result->Message = "La cantidad ingresada no puede superar a la cantidad de grupos establecidos";
                    return response()->json($Result);
                }

                $TorneoCategoria->clasificados_terceros = $request->clasificados_terceros;
            }

            if($TorneoCategoria->save()) $Result->Success = true;

        }catch (\Exception $e){
            $Result->Message = $e->getMessage();
        }

        return response()->json($Result);
    }

    public function cambiarOrdenPartialView($torneo_id)
    {
        $TorneoCategorias = TorneoCategoria::where('torneo_id', $torneo_id)
        ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
        ->orderBy('orden', 'asc')->get();

        return view('auth'.'.'.$this->viewName.'.ajax.categoria.partialViewChangeOrder', ['TorneoId' => $torneo_id, 'TorneoCategorias' => $TorneoCategorias, 'ViewName' => ucfirst($this->viewName)]);
    }

    public function cambiarOrdenStore(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null];

        try {

            DB::beginTransaction();

            $Torneo = Torneo::where('id', $request->id)->where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->first();
            if ($Torneo != null)
            {
                $TorneoCategorias = json_decode($request->jsonString);

                if($TorneoCategorias != null && count($TorneoCategorias) > 0)
                {
                    for ($i = 0; $i < count($TorneoCategorias); $i++){
                        TorneoCategoria::where('torneo_id', $Torneo->id)->where('id', $TorneoCategorias[$i])->update(['orden' => ($i + 1)]);
                    }
                }

                DB::commit();

                $Result->Success = true;
            }

        } catch (\Exception $e) {
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }

        return response()->json($Result);
    }

    public function faseFinalPlayerTerceros($torneo, $torneo_categoria_id)
    {
        $TorneoCategoria = TorneoCategoria::where('id', $torneo_categoria_id)->where('torneo_id', $torneo)
        ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})->first();

        if($TorneoCategoria != null)
        {
            $TorneoGrupos = TorneoGrupo::where('torneo_categoria_id', $TorneoCategoria->id)->select(['nombre_grupo', 'grupo_id'])->groupBy(['nombre_grupo', 'grupo_id'])
            ->orderBy(DB::raw('LENGTH(nombre_grupo)'))->orderBy('nombre_grupo')->get();

            return view('auth'.'.'.$this->viewName.'.ajax.final.jugador.partialView', ['TorneoCategoria' => $TorneoCategoria, 'Terceros' => $TorneoGrupos->count(), 'ViewName' => ucfirst($this->viewName)]);
        }

        return null;
    }

    public function faseFinalPlayerTercerosStore(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null];

        try {

            DB::beginTransaction();

            if($request->jugadores_terceros % 2 == 0)
            {
                $TorneoCategoria = TorneoCategoria::where('id', $request->torneo_categoria_id)->where('torneo_id', $request->torneo_id)
                ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})->first();

                if($TorneoCategoria != null)
                {
                    $TorneoGrupos = TorneoGrupo::where('torneo_categoria_id', $TorneoCategoria->id)->select(['nombre_grupo', 'grupo_id'])->groupBy(['nombre_grupo', 'grupo_id'])
                    ->orderBy(DB::raw('LENGTH(nombre_grupo)'))->orderBy('nombre_grupo')->get();

                    if($TorneoGrupos->count() >= $request->jugadores_terceros)
                    {
                        $TorneoCategoria->clasificados_terceros = $request->jugadores_terceros;
                        if($TorneoCategoria->save())
                        {
                            DB::commit();
                            $Result->Success = true;
                        }

                    }else{
                        $Result->Message = "Por favor, ingrese una cantidad de válida, solo puede ingresar máximo ".$TorneoGrupos->count()." terceros.";
                    }
                }else{
                    $Result->Message = "El torneo categoria que intenta modificar, ya no se encuentra disponible.";
                }

            }else{
                $Result->Message = "Por favor, ingrese una cantidad de jugadores par.";
            }

        }catch (\Exception $e)
        {
            DB::rollBack();
            $Result->Message = $e->getMessage();
        }

        return response()->json($Result);
    }


    /*TORNEO FASE FINAL - PRIMERA ETAPA*/
    public function faseFinalFirstStore(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null];

        try {

            DB::beginTransaction();

            $TorneoCategoria = TorneoCategoria::where('id', $request->torneo_categoria_id)->where('torneo_id', $request->torneo_id)
            ->whereHas('torneo', function ($q) {$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})->first();

            if($TorneoCategoria != null)
            {
                $reload = filter_var($request->reload, FILTER_VALIDATE_BOOLEAN);

                $Jugadores = TorneoJugador::where('torneo_categoria_id', $TorneoCategoria->id)->get()
                ->map(function ($q) use ($TorneoCategoria){
                    return [
                        'jugador_simple_id' => $q->jugadorSimple->id,
                        'jugador_dupla_id' => $TorneoCategoria->multiple ? $q->jugadorDupla->id : null,
                        'nombres' => $TorneoCategoria->multiple ? ($q->jugadorSimple->nombre_completo." + ".$q->jugadorDupla->nombre_completo) : $q->jugadorSimple->nombre_completo
                    ];
                });

                if(count($Jugadores) % 2 == 0)
                {
                    if(count($Jugadores) < App::$PARTICIPANTES_MINIMOS_FIRST_FINAL_POR_TORNEO){
                        $Result->Message = "Por favor, registre al menos ".(App::$PARTICIPANTES_MINIMOS_FIRST_FINAL_POR_TORNEO - count($Jugadores))." jugadores más para generar las llaves";
                        return response()->json($Result);
                    }

                    if(count($Jugadores) > 32){
                        $Result->Message = "Por favor, solo puede registrar como máximo 32 jugadores para generar las llaves";
                        return response()->json($Result);
                    }

                    $BuyGenerar = 0;

                    if(!in_array(count($Jugadores), [4, 8, 16, 32]))
                    {
                        if(count($Jugadores) < 16) $BuyGenerar = (16 - count($Jugadores));
                        else if(count($Jugadores) < 32) $BuyGenerar = (32 - count($Jugadores));
                    }

                    $Bloques = count($Jugadores) == 4 ? 2 : 4;
                    $CantidadPorBloques = (((count($Jugadores)+$BuyGenerar)/2)/$Bloques);

                    //$CantidadPorBloques = (((count($Jugadores)+$BuyGenerar)/2)/4);

                    $Partidos = [];

                    for ($i = 1; $i <= $Bloques; $i++)
                    {
                        $Position = 1;
                        for ($j= 0; $j < $CantidadPorBloques; $j++)
                        {
                            $Partidos[] = [
                                'comunidad_id' => Auth::guard('web')->user()->comunidad_id,
                                'torneo_id' => $TorneoCategoria->torneo->id,
                                'torneo_categoria_id' => $TorneoCategoria->id,
                                'grupo_id' => null,
                                'multiple' => $TorneoCategoria->multiple,
                                'jugador_local_uno_id' => null,
                                'jugador_local_dos_id' => null,
                                'jugador_rival_uno_id' => null,
                                'jugador_rival_dos_id' => null,
                                'fecha_inicio' => Carbon::parse($TorneoCategoria->torneo->fecha_inicio),
                                'fecha_final' => Carbon::parse($TorneoCategoria->torneo->fecha_inicio)->addDay(6),
                                'hora_final' => null,
                                'resultado' => null,
                                'estado_id' => App::$ESTADO_PENDIENTE,
                                'buy' => false,
                                'bloque' => ($i),
                                'position' => $Position,
                                'fase' => ((count($Jugadores)+$BuyGenerar)/2),
                                'user_create_id' => Auth::guard('web')->user()->id,
                            ];
                            $Position++;
                        }
                    }

                    TorneoCategoria::where('id', $TorneoCategoria->id)->where('torneo_id', $TorneoCategoria->torneo->id)
                    ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
                    ->update(['manual' => true, 'first_final' => true]);

                    if($reload)
                    {
                        Partido::where('torneo_categoria_id', $TorneoCategoria->id)->where('torneo_id', $TorneoCategoria->torneo_id)
                        ->whereNotNull('fase')->delete();
                    }

                    Partido::insert($Partidos);

                    DB::commit();

                    $Result->Success = true;

                }else{
                    $Result->Message = 'Por favor, necesita agregar '.(4-(count($Jugadores) % 4)).' '.((4-(count($Jugadores) % 4)) == 1 ? 'jugador' : 'jugadores'). ' más para generar las llaves';

                    //$Result->Message = "Por favosssr, necesita agregar ".(2-(count($Jugadores) % 2))." ".((2-(count($Jugadores) % 2)) == 1 ? "jugador" : "jugadores"). " más para generar las llaves";
                }
            }

        }catch (\Exception $e) {
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }

        return response()->json($Result);
    }

    /*TORNEO FASE FINAL*/
    public function faseFinal($torneo, $torneo_categoria_id, $landing=false)
    {
        $ComunidadId = $landing ? Comunidad::where('principal', true)->first()->id : Auth::guard('web')->user()->comunidad_id;

        $TorneoCategoria = TorneoCategoria::where('id', $torneo_categoria_id)->where('torneo_id', $torneo)
        ->whereHas('torneo', function ($q) use ($ComunidadId){
           $q->where('comunidad_id', $ComunidadId);
        })->first();

        if($TorneoCategoria != null)
        {
            $TorneoGrupos = $TorneoCategoria->torneo->torneoGrupos()->where('torneo_categoria_id', $TorneoCategoria->id)->select('grupo_id')->groupBy('grupo_id')->get();

            $JugadoresClasificados = [];

            $Clasifican = $TorneoCategoria->clasificados_terceros > 0 ? 3 : $TorneoCategoria->clasificados;

            foreach ($TorneoGrupos as $key => $q)
            {
                //JUGADORES DEL GRUPO
                $Jugadores = $TorneoCategoria->torneo->torneoGrupos()->where('torneo_categoria_id', $TorneoCategoria->id)
                    ->where('grupo_id', $q->grupo_id)->get()->map(function ($q) use ($TorneoCategoria){
                        return [
                            'jugador_simple_id' => $q->jugadorSimple->id,
                            'jugador_dupla_id' => $TorneoCategoria->multiple ? $q->jugadorDupla->id : null,
                            'nombres' => $TorneoCategoria->multiple ? ($q->jugadorSimple->nombre_completo." + ".$q->jugadorDupla->nombre_completo) : $q->jugadorSimple->nombre_completo
                        ];
                    });

                //JUGADORES CALISIFICADOS POR GRUPO
                $TablePositions = [];
                foreach ($Jugadores as $key2 => $q2)
                {
                    if($TorneoCategoria->multiple)
                    {
                        $PartidosComoLocal = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                        ->where('grupo_id', $q->grupo_id)->where('jugador_local_uno_id', $q2['jugador_simple_id'])
                        ->where('jugador_local_dos_id', $q2['jugador_dupla_id'])->whereNull('fase')->get());

                        $PartidosComoRival = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                        ->where('grupo_id', $q->grupo_id)->where('jugador_rival_uno_id', $q2['jugador_simple_id'])
                        ->where('jugador_rival_dos_id', $q2['jugador_dupla_id'])->whereNull('fase')->get());

                        $SetsGanados = 0; $SetsPerdidos = 0; $GamesGanados = 0; $GamesPerdidos = 0; $Puntos = 0;

                        foreach ($PartidosComoLocal as $p){
                           if($p->jugador_ganador_uno_id == $q2['jugador_simple_id'])
                           {   //NO Rival
                               $SetsGanados += $p->jugador_local_set; $SetsPerdidos += $p->jugador_rival_set;
                               $GamesGanados += $p->jugador_local_juego; $GamesPerdidos += $p->jugador_rival_juego;
                               $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                           }else{
                               //Rival
                               $SetsGanados += $p->jugador_rival_set; $SetsPerdidos += $p->jugador_local_set;
                               $GamesGanados += $p->jugador_rival_juego; $GamesPerdidos += $p->jugador_local_juego;
                               $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                           }
                       }

                        foreach ($PartidosComoRival as $p){
                            if($p->jugador_ganador_uno_id == $q2['jugador_simple_id'])
                            {   //NO Rival
                                $SetsGanados += $p->jugador_local_set; $SetsPerdidos += $p->jugador_rival_set;
                                $GamesGanados += $p->jugador_local_juego; $GamesPerdidos += $p->jugador_rival_juego;
                                $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                            }else{
                                //Rival
                                $SetsGanados += $p->jugador_rival_set; $SetsPerdidos += $p->jugador_local_set;
                                $GamesGanados += $p->jugador_rival_juego; $GamesPerdidos += $p->jugador_local_juego;
                                $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                            }
                        }

                        $SetsDiferencias = $SetsGanados - $SetsPerdidos;

                        $GamesDiferencias = $GamesGanados - $GamesPerdidos;

                        $Puntos = $Puntos * $TorneoCategoria->torneo->valor_set;

                        $TablePositions[] = [
                            'key' => ($key.'-'.$key2),
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

                    }else{

                        $PartidosComoLocal = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                        ->where('grupo_id', $q->grupo_id)->where('jugador_local_uno_id', $q2['jugador_simple_id'])->whereNull('fase')->get());

                        $PartidosComoRival = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                        ->where('grupo_id', $q->grupo_id)->where('jugador_rival_uno_id', $q2['jugador_simple_id'])->whereNull('fase')->get());

                        $SetsGanados = 0; $SetsPerdidos = 0; $GamesGanados = 0; $GamesPerdidos = 0; $Puntos = 0;

                        foreach ($PartidosComoLocal as $p){
                            if($p->jugador_ganador_uno_id == $q2['jugador_simple_id'])
                            {   //NO Rival
                                $SetsGanados += $p->jugador_local_set; $SetsPerdidos += $p->jugador_rival_set;
                                $GamesGanados += $p->jugador_local_juego; $GamesPerdidos += $p->jugador_rival_juego;
                                $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                            }else{
                                //Rival
                                $SetsGanados += $p->jugador_rival_set; $SetsPerdidos += $p->jugador_local_set;
                                $GamesGanados += $p->jugador_rival_juego; $GamesPerdidos += $p->jugador_local_juego;
                                $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                            }
                        }

                        foreach ($PartidosComoRival as $p){
                            if($p->jugador_ganador_uno_id == $q2['jugador_simple_id'])
                            {   //NO Rival
                                $SetsGanados += $p->jugador_local_set; $SetsPerdidos += $p->jugador_rival_set;
                                $GamesGanados += $p->jugador_local_juego; $GamesPerdidos += $p->jugador_rival_juego;
                                $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                            }else{
                                //Rival
                                $SetsGanados += $p->jugador_rival_set; $SetsPerdidos += $p->jugador_local_set;
                                $GamesGanados += $p->jugador_rival_juego; $GamesPerdidos += $p->jugador_local_juego;
                                $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                            }
                        }

                        $SetsDiferencias = $SetsGanados - $SetsPerdidos;

                        $GamesDiferencias = $GamesGanados - $GamesPerdidos;

                        $Puntos = $Puntos * $TorneoCategoria->torneo->valor_set;

                        $TablePositions[] = [
                            'key' => ($key.'-'.$key2),
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
                $JugadoresClasificados[] = ['Grupo' => $q->grupo->nombre, 'Clasificados' => App::multiPropertySort(collect($TablePositions), [ ['column' => 'puntos', 'order' => 'desc'],['column' => 'setsDiferencias', 'order' => 'desc'], ['column' => 'gamesDiferencias', 'order' => 'desc'], ['column' => 'setsGanados', 'order' => 'desc'], ['column' => 'gamesGanados', 'order' => 'desc']])->take($Clasifican)];
            }

            //CLASIFICADOS POR CÁLCULO
            $PrimerosLugares = [];  $SegundoLugares = [];  $TercerosLugares = [];

            foreach ($JugadoresClasificados as $key => $value)
            {
                if($Clasifican == 1) $PrimerosLugares[] = $value['Clasificados']->first();
                else if($Clasifican == 2) {
                    $PrimerosLugares[] = $value['Clasificados']->first();
                    $SegundoLugares[] = $value['Clasificados']->last();
                }else{
                    $PrimerosLugares[] = $value['Clasificados']->first();
                    $TercerosLugares[] = $value['Clasificados']->last();
                }
            }

            if($Clasifican == 3)
            {
                $Clasificados = array_merge(collect($PrimerosLugares)->pluck('key')->toArray(), collect($TercerosLugares)->pluck('key')->toArray());
                foreach (collect($JugadoresClasificados)->pluck('Clasificados') as $key => $value){
                    foreach ($value as $ke2 => $value2){
                        if(!in_array($value2['key'], $Clasificados)) $SegundoLugares[] = $value2;
                    }
                }
                $TercerosLugares = App::multiPropertySort(collect($TercerosLugares), [ ['column' => 'puntos', 'order' => 'desc'],['column' => 'setsDiferencias', 'order' => 'desc'], ['column' => 'gamesDiferencias', 'order' => 'desc'], ['column' => 'setsGanados', 'order' => 'desc'], ['column' => 'gamesGanados', 'order' => 'desc']])->take($TorneoCategoria->clasificados_terceros)->toArray();
            }

            $PrimerosLugares = App::multiPropertySort(collect($PrimerosLugares), [ ['column' => 'puntos', 'order' => 'desc'],['column' => 'setsDiferencias', 'order' => 'desc'], ['column' => 'gamesDiferencias', 'order' => 'desc'], ['column' => 'setsGanados', 'order' => 'desc'], ['column' => 'gamesGanados', 'order' => 'desc']]);
            $SegundoLugares = App::multiPropertySort(collect($SegundoLugares), [ ['column' => 'puntos', 'order' => 'desc'],['column' => 'setsDiferencias', 'order' => 'desc'], ['column' => 'gamesDiferencias', 'order' => 'desc'], ['column' => 'setsGanados', 'order' => 'desc'], ['column' => 'gamesGanados', 'order' => 'desc']]);
            $TercerosLugares = App::multiPropertySort(collect($TercerosLugares), [ ['column' => 'puntos', 'order' => 'desc'],['column' => 'setsDiferencias', 'order' => 'desc'], ['column' => 'gamesDiferencias', 'order' => 'desc'], ['column' => 'setsGanados', 'order' => 'desc'], ['column' => 'gamesGanados', 'order' => 'desc']]);

            $JugadoresClasificadosMerge = $PrimerosLugares->merge($SegundoLugares)->merge($TercerosLugares);

            $TorneoFaseFinal = (object)['TorneoCategoria' => $TorneoCategoria, 'JugadoresClasificados' => App::multiPropertySort(collect($JugadoresClasificadosMerge), [['column' => 'puntos', 'order' => 'desc'], ['column' => 'setsDiferencias', 'order' => 'desc'], ['column' => 'gamesDiferencias', 'order' => 'desc'], ['column' => 'setsGanados', 'order' => 'desc'], ['column' => 'gamesGanados', 'order' => 'desc']])];

            return view('auth'.'.'.$this->viewName.'.ajax.final.index', ['TorneoFaseFinal' => $TorneoFaseFinal, 'ViewName' => ucfirst($this->viewName), 'landing' => filter_var($landing, FILTER_VALIDATE_BOOLEAN)]);
        }

        return null;
    }

    public function faseFinalStore(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null];

        try {

            DB::beginTransaction();

            $TorneoCategoria = TorneoCategoria::where('id', $request->torneo_categoria_id)->where('torneo_id', $request->torneo_id)
            ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})->first();

            if($TorneoCategoria != null)
            {
                $reload = filter_var($request->reload, FILTER_VALIDATE_BOOLEAN);

                if(!$reload && count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)
                ->where('estado_id', App::$ESTADO_PENDIENTE)) > 0)
                {
                    $Result->Message = "No puede generar las llaves de la segunda fase, porque aún existen partidos no finalizados.";
                    return response()->json($Result);
                }

                $TorneoGrupos = $TorneoCategoria->torneo->torneoGrupos()->where('torneo_categoria_id', $TorneoCategoria->id)->select('grupo_id')->groupBy('grupo_id')->get();

                $JugadoresClasificados = [];

                $Clasifican = $TorneoCategoria->clasificados_terceros > 0 ? 3 :$TorneoCategoria->clasificados;

                foreach ($TorneoGrupos as $key => $q)
                {
                    //TODOS LOS JUGADORES DEL GRUPO
                    $Jugadores = $TorneoCategoria->torneo->torneoGrupos()->where('torneo_categoria_id', $TorneoCategoria->id)
                    ->where('grupo_id', $q->grupo_id)->get()->map(function ($q) use ($TorneoCategoria){
                        return [
                            'jugador_simple_id' => $q->jugadorSimple->id,
                            'jugador_dupla_id' => $TorneoCategoria->multiple ? $q->jugadorDupla->id : null,
                            'nombres' => $TorneoCategoria->multiple ? ($q->jugadorSimple->nombre_completo." + ".$q->jugadorDupla->nombre_completo) : $q->jugadorSimple->nombre_completo
                        ];
                    });

                    //SOLO LOS JUGADORES CLASISIFICADOS POR GRUPO
                    $TablePositions = [];
                    foreach ($Jugadores as $key2 => $q2)
                    {
                        if($TorneoCategoria->multiple)
                        {
                            $PartidosComoLocal = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                                ->where('grupo_id', $q->grupo_id)->where('jugador_local_uno_id', $q2['jugador_simple_id'])
                                ->where('jugador_local_dos_id', $q2['jugador_dupla_id'])->whereNull('fase')->get());

                            $PartidosComoRival = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                                ->where('grupo_id', $q->grupo_id)->where('jugador_rival_uno_id', $q2['jugador_simple_id'])
                                ->where('jugador_rival_dos_id', $q2['jugador_dupla_id'])->whereNull('fase')->get());

                            $SetsGanados = 0; $SetsPerdidos = 0; $GamesGanados = 0; $GamesPerdidos = 0; $Puntos = 0;

                            foreach ($PartidosComoLocal as $p){
                                if($p->jugador_ganador_uno_id == $q2['jugador_simple_id'])
                                {   //NO Rival
                                    $SetsGanados += $p->jugador_local_set; $SetsPerdidos += $p->jugador_rival_set;
                                    $GamesGanados += $p->jugador_local_juego; $GamesPerdidos += $p->jugador_rival_juego;
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                                }else{
                                    //Rival
                                    $SetsGanados += $p->jugador_rival_set; $SetsPerdidos += $p->jugador_local_set;
                                    $GamesGanados += $p->jugador_rival_juego; $GamesPerdidos += $p->jugador_local_juego;
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                                }
                            }

                            foreach ($PartidosComoRival as $p){
                                if($p->jugador_ganador_uno_id == $q2['jugador_simple_id'])
                                {   //NO Rival
                                    $SetsGanados += $p->jugador_local_set; $SetsPerdidos += $p->jugador_rival_set;
                                    $GamesGanados += $p->jugador_local_juego; $GamesPerdidos += $p->jugador_rival_juego;
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                                }else{
                                    //Rival
                                    $SetsGanados += $p->jugador_rival_set; $SetsPerdidos += $p->jugador_local_set;
                                    $GamesGanados += $p->jugador_rival_juego; $GamesPerdidos += $p->jugador_local_juego;
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                                }
                            }

                            $SetsDiferencias = $SetsGanados - $SetsPerdidos;

                            $GamesDiferencias = $GamesGanados - $GamesPerdidos;

                            $Puntos = $Puntos * $TorneoCategoria->torneo->valor_set;

                            $TablePositions[] = [
                                'key' => ($q2['jugador_simple_id'] . '-' . $q2['jugador_dupla_id']),
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


                            /*$PartidosComoLocal = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                            ->where('grupo_id', $q->grupo_id)->where('jugador_local_uno_id', $q2['jugador_simple_id'])
                            ->where('jugador_local_dos_id', $q2['jugador_dupla_id'])->whereNull('fase')->get());

                            $PartidosComoRival = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                            ->where('grupo_id', $q->grupo_id)->where('jugador_rival_uno_id', $q2['jugador_simple_id'])
                            ->where('jugador_rival_dos_id', $q2['jugador_dupla_id'])->whereNull('fase')->get());

                            $Puntos = 0;

                            foreach ($PartidosComoLocal as $p){
                                if($p->jugador_ganador_uno_id == $q2['jugador_simple_id']){
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                                }else{
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                                }
                            }

                            foreach ($PartidosComoRival as $p){
                                if($p->jugador_ganador_uno_id == $q2['jugador_simple_id']){
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                                }else{
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                                }
                            }

                            $Puntos = $Puntos * $TorneoCategoria->torneo->valor_set;

                            $TablePositions[] = [
                                'key' => ($key.'-'.$key2),
                                'grupo_id' => $q->grupo->id,
                                'grupo' => $q->grupo->nombre,
                                'jugador_simple_id' => $q2['jugador_simple_id'],
                                'jugador_dupla_id' => $q2['jugador_dupla_id'],
                                'nombres' => $q2['nombres'],
                                'puntos' => $Puntos
                            ];*/

                        }else{

                            $PartidosComoLocal = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                                ->where('grupo_id', $q->grupo_id)->where('jugador_local_uno_id', $q2['jugador_simple_id'])->whereNull('fase')->get());

                            $PartidosComoRival = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                                ->where('grupo_id', $q->grupo_id)->where('jugador_rival_uno_id', $q2['jugador_simple_id'])->whereNull('fase')->get());

                            $SetsGanados = 0; $SetsPerdidos = 0; $GamesGanados = 0; $GamesPerdidos = 0; $Puntos = 0;

                            foreach ($PartidosComoLocal as $p){
                                if($p->jugador_ganador_uno_id == $q2['jugador_simple_id'])
                                {   //NO Rival
                                    $SetsGanados += $p->jugador_local_set; $SetsPerdidos += $p->jugador_rival_set;
                                    $GamesGanados += $p->jugador_local_juego; $GamesPerdidos += $p->jugador_rival_juego;
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                                }else{
                                    //Rival
                                    $SetsGanados += $p->jugador_rival_set; $SetsPerdidos += $p->jugador_local_set;
                                    $GamesGanados += $p->jugador_rival_juego; $GamesPerdidos += $p->jugador_local_juego;
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                                }
                            }

                            foreach ($PartidosComoRival as $p){
                                if($p->jugador_ganador_uno_id == $q2['jugador_simple_id'])
                                {   //NO Rival
                                    $SetsGanados += $p->jugador_local_set; $SetsPerdidos += $p->jugador_rival_set;
                                    $GamesGanados += $p->jugador_local_juego; $GamesPerdidos += $p->jugador_rival_juego;
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                                }else{
                                    //Rival
                                    $SetsGanados += $p->jugador_rival_set; $SetsPerdidos += $p->jugador_local_set;
                                    $GamesGanados += $p->jugador_rival_juego; $GamesPerdidos += $p->jugador_local_juego;
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                                }
                            }

                            $SetsDiferencias = $SetsGanados - $SetsPerdidos;

                            $GamesDiferencias = $GamesGanados - $GamesPerdidos;

                            $Puntos = $Puntos * $TorneoCategoria->torneo->valor_set;

                            $TablePositions[] = [
                                'key' => $q2['jugador_simple_id'],
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
                                'puntos' => $Puntos
                            ];

                            /*$PartidosComoLocal = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                            ->where('grupo_id', $q->grupo_id)->where('jugador_local_uno_id', $q2['jugador_simple_id'])->whereNull('fase')->get());

                            $PartidosComoRival = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                            ->where('grupo_id', $q->grupo_id)->where('jugador_rival_uno_id', $q2['jugador_simple_id'])->whereNull('fase')->get());

                            $Puntos = 0;

                            foreach ($PartidosComoLocal as $p){
                                if($p->jugador_ganador_uno_id == $q2['jugador_simple_id']){
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                                }else{
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                                }
                            }

                            foreach ($PartidosComoRival as $p){
                                if($p->jugador_ganador_uno_id == $q2['jugador_simple_id']){
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                                }else{
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                                }
                            }

                            $Puntos = $Puntos * $TorneoCategoria->torneo->valor_set;

                            $TablePositions[] = [
                                'key' => ($key.'-'.$key2),
                                'grupo_id' => $q->grupo->id,
                                'grupo' => $q->grupo->nombre,
                                'jugador_simple_id' => $q2['jugador_simple_id'],
                                'jugador_dupla_id' => null,
                                'nombres' => $q2['nombres'],
                                'puntos' => $Puntos
                            ];*/

                        }
                    }
                    $JugadoresClasificados[] = ['Grupo' => $q->grupo->nombre, 'Clasificados' => App::multiPropertySort(collect($TablePositions), [ ['column' => 'puntos', 'order' => 'desc'],['column' => 'setsDiferencias', 'order' => 'desc'], ['column' => 'gamesDiferencias', 'order' => 'desc'], ['column' => 'setsGanados', 'order' => 'desc'], ['column' => 'gamesGanados', 'order' => 'desc']])->take($Clasifican)];
                }

                //SOLO JUGADORES CLASIFICADOS POR CANTIDAD DE CLASIFICADOS PERMIDOS
                $PrimerosLugares = [];  $SegundoLugares = [];  $TercerosLugares = [];
                foreach ($JugadoresClasificados as $key => $value)
                {
                    if($Clasifican == 1) $PrimerosLugares[] = $value['Clasificados']->first();
                    else if($Clasifican == 2) {
                        $PrimerosLugares[] = $value['Clasificados']->first();
                        $SegundoLugares[] = $value['Clasificados']->last();
                    }else{
                        $PrimerosLugares[] = $value['Clasificados']->first();
                        $TercerosLugares[] = $value['Clasificados']->last();
                    }
                }

                if($Clasifican == 3)
                {
                    $Clasificados = array_merge(collect($PrimerosLugares)->pluck('key')->toArray(), collect($TercerosLugares)->pluck('key')->toArray());
                    foreach (collect($JugadoresClasificados)->pluck('Clasificados') as $key => $value){
                        foreach ($value as $ke2 => $value2){
                            if(!in_array($value2['key'], $Clasificados)) $SegundoLugares[] = $value2;
                        }
                    }
                    $TercerosLugares = App::multiPropertySort(collect($TercerosLugares), [ ['column' => 'puntos', 'order' => 'desc'],['column' => 'setsDiferencias', 'order' => 'desc'], ['column' => 'gamesDiferencias', 'order' => 'desc'], ['column' => 'setsGanados', 'order' => 'desc'], ['column' => 'gamesGanados', 'order' => 'desc']])->take($TorneoCategoria->clasificados_terceros)->toArray();
                }

                //$PrimerosLugares = App::multiPropertySort(collect($PrimerosLugares), [ ['column' => 'puntos', 'order' => 'desc'],['column' => 'setsDiferencias', 'order' => 'desc'], ['column' => 'gamesDiferencias', 'order' => 'desc'], ['column' => 'setsGanados', 'order' => 'desc'], ['column' => 'gamesGanados', 'order' => 'desc']])->toArray();
                //$SegundoLugares = App::multiPropertySort(collect($SegundoLugares), [ ['column' => 'puntos', 'order' => 'desc'],['column' => 'setsDiferencias', 'order' => 'desc'], ['column' => 'gamesDiferencias', 'order' => 'desc'], ['column' => 'setsGanados', 'order' => 'desc'], ['column' => 'gamesGanados', 'order' => 'desc']])->toArray();

                $JugadoresClasificadosMerge = array_filter(array_merge($PrimerosLugares, $SegundoLugares, $TercerosLugares));

                if(count($JugadoresClasificadosMerge) > 32){
                    $JugadoresClasificadosMerge = App::multiPropertySort(collect($JugadoresClasificadosMerge), [ ['column' => 'puntos', 'order' => 'desc'],['column' => 'setsDiferencias', 'order' => 'desc'], ['column' => 'gamesDiferencias', 'order' => 'desc'], ['column' => 'setsGanados', 'order' => 'desc'], ['column' => 'gamesGanados', 'order' => 'desc']])->take(32);
                }else{
                    $JugadoresClasificadosMerge = App::multiPropertySort(collect($JugadoresClasificadosMerge), [ ['column' => 'puntos', 'order' => 'desc'],['column' => 'setsDiferencias', 'order' => 'desc'], ['column' => 'gamesDiferencias', 'order' => 'desc'], ['column' => 'setsGanados', 'order' => 'desc'], ['column' => 'gamesGanados', 'order' => 'desc']]);
                }

                $PartidosFaseFinal = [];

                //CREACIÓN DE JUGADORES BUYS POR FALTA DE JUGADORES CLASIFICADOS DE 16 O 32 JUGADORES
                $JugadoresKeysDefinidos = [];
                if(!in_array(count($JugadoresClasificadosMerge), [4, 8, 16, 32]))
                {
                    $BuyGenerar = 0;
                    if(count($JugadoresClasificadosMerge) < 16) $BuyGenerar = (16 - count($JugadoresClasificadosMerge));
                    else if(count($JugadoresClasificadosMerge) < 32) $BuyGenerar = (32 - count($JugadoresClasificadosMerge));

                    $BuysGenerados = [];
                    for ($i=0; $i < $BuyGenerar; $i++)
                    {
                        $BuysGenerados[] = [
                            'key' => ('B-'.$i),
                            'grupo_id' => null,
                            'grupo' => null,
                            'jugador_simple_id' => null,
                            'jugador_dupla_id' => null,
                            'nombres' => 'BYE',
                            'puntos' => 0
                        ];
                    }

                    $JugadoresBuys = array_values(collect($JugadoresClasificadosMerge)->sortByDesc('puntos')->take(count($BuysGenerados))->toArray());

                    for ($i=0; $i < count($JugadoresBuys); $i++)
                    {
                        $PartidosFaseFinal[] = [
                            'buy' => true, 'key' => $JugadoresBuys[$i]['key'],
                            'jugador_local' => ['jugador_simple_id' => $JugadoresBuys[$i]['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $JugadoresBuys[$i]['jugador_dupla_id'] : null, 'grupo_id' =>  $JugadoresBuys[$i]['grupo_id'], 'grupo' => $JugadoresBuys[$i]['grupo'], 'nombres' => $JugadoresBuys[$i]['nombres']],
                            'jugador_rival' => ['jugador_simple_id' => $BuysGenerados[$i]['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $BuysGenerados[$i]['jugador_dupla_id'] : null, 'grupo_id' =>  $BuysGenerados[$i]['grupo_id'], 'grupo' => $BuysGenerados[$i]['grupo'], 'nombres' => $BuysGenerados[$i]['nombres']]
                        ];
                        $JugadoresKeysDefinidos[] = $JugadoresBuys[$i]['key'];
                    }
                }

                //JUGADORES QUE AÚN NO TIENEN PARTIDOS PROGRAMADOS EN LA FASE FINAL
                $x = 0;
                while ($x <= 10)
                {
                    $JugadoresNoDefinidos = $request->tipo == "random" ? collect($JugadoresClasificadosMerge)->whereNotIn('key', $JugadoresKeysDefinidos)->shuffle() : collect($JugadoresClasificadosMerge)->whereNotIn('key', $JugadoresKeysDefinidos)->sortByDesc('puntos');
                    if(count($JugadoresNoDefinidos) > 0)
                    {
                        foreach ($JugadoresNoDefinidos as $q)
                        {
                            if(!in_array($q['key'], $JugadoresKeysDefinidos))
                            {
                                $JugadorRivales = collect($JugadoresClasificadosMerge)->where('key', '!=', $q['key'])->where('grupo_id','!=',$q['grupo_id'])->whereNotIn('key', $JugadoresKeysDefinidos)->sortByDesc('puntos');

                                $JugadorRival = $request->tipo == "random" && count($JugadorRivales) > 0 ? $JugadorRivales->random() : $JugadorRivales->last();

                                if($JugadorRival != null)
                                {
                                    $JugadoresKeysDefinidos[] = $q['key']; $JugadoresKeysDefinidos[] = $JugadorRival['key'];
                                    $PartidosFaseFinal[] = [
                                        'buy' => false,
                                        'key' => $q['key'].'-vs-'.$JugadorRival['key'],
                                        'jugador_local' => ['jugador_simple_id' => $q['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_dupla_id'] : null, 'grupo_id' =>  $q['grupo_id'], 'grupo' => $q['grupo'], 'nombres' => $q['nombres']],
                                        'jugador_rival' => ['jugador_simple_id' => $JugadorRival['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $JugadorRival['jugador_dupla_id'] : null, 'grupo_id' =>  $JugadorRival['grupo_id'], 'grupo' => $JugadorRival['grupo'], 'nombres' => $JugadorRival['nombres']]
                                    ];
                                }
                            }
                        }
                    }
                    $x++;
                }

                //POSIBLES JUGADORES QUE NO TIENEN PARTIDOS JUGADORES PORQUE SON DEL MISMO GRUPO
                $JugadoresNoDefinidos = collect($JugadoresClasificadosMerge)->whereNotIn('key', $JugadoresKeysDefinidos)->sortByDesc('puntos');
                if(count($JugadoresNoDefinidos) > 0)
                {
                    foreach ($JugadoresNoDefinidos as $q)
                    {
                        if(!in_array($q['key'], $JugadoresKeysDefinidos))
                        {
                            $JugadorRival = collect($JugadoresClasificadosMerge)->where('key', '!=', $q['key'])->whereNotIn('key', $JugadoresKeysDefinidos)->first();

                            if($JugadorRival != null)
                            {
                                $JugadoresKeysDefinidos[] = $q['key']; $JugadoresKeysDefinidos[] = $JugadorRival['key'];
                                $PartidosFaseFinal[] = [
                                    'buy' => false,
                                    'key' => $q['key'].'-vs-'.$JugadorRival['key'],
                                    'jugador_local' => ['jugador_simple_id' => $q['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_dupla_id'] : null, 'grupo_id' =>  $q['grupo_id'], 'grupo' => $q['grupo'], 'nombres' => $q['nombres']],
                                    'jugador_rival' => ['jugador_simple_id' => $JugadorRival['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $JugadorRival['jugador_dupla_id'] : null, 'grupo_id' =>  $JugadorRival['grupo_id'], 'grupo' => $JugadorRival['grupo'], 'nombres' => $JugadorRival['nombres']]
                                ];
                            }
                        }
                    }
                }

                if($request->tipo == "random") $PartidosFaseFinal = collect($PartidosFaseFinal)->shuffle();

                //DEFINIENDO LOS BLOQUES EN EL CUADRO DE FASE FINAL
                $CantidadPorBloques = (count($PartidosFaseFinal)/4);

                $PrimerBloque = []; $SegundoBloque = []; $TercerBloque = []; $CuartoBloque = []; $PartidosKeysDefinidos = [];

                $i = 100;
                while($i <= 100)
                {
                    $PartidosKeysDefinidos = []; $y = 0;
                    while ($y <= 10){
                        $PartidosPorDefinir = collect($PartidosFaseFinal)->whereNotIn('key', $PartidosKeysDefinidos);
                        if(count($PartidosPorDefinir) > 0)
                        {
                            foreach ($PartidosPorDefinir as $q)
                            {
                                if(!in_array($q['key'], $PartidosKeysDefinidos))
                                {
                                    $Grupo = [$q['jugador_local']['grupo_id'], $q['jugador_rival']['grupo_id']];
                                    if(count($PrimerBloque) < $CantidadPorBloques && collect($PrimerBloque)->whereIn('jugador_local.grupo_id', $Grupo)->whereIn('jugador_rival.grupo_id', $Grupo)->first() == null)
                                    {
                                        $PrimerBloque[] = [
                                            'buy' => $q['buy'], 'key' => $q['key'],
                                            'jugador_local' => ['jugador_simple_id' => $q['jugador_local']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_local']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_local']['grupo_id'], 'grupo' => $q['jugador_local']['grupo'], 'nombres' => $q['jugador_local']['nombres']],
                                            'jugador_rival' => ['jugador_simple_id' => $q['jugador_rival']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_rival']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_rival']['grupo_id'], 'grupo' => $q['jugador_rival']['grupo'], 'nombres' => $q['jugador_rival']['nombres']]
                                        ];
                                        $PartidosKeysDefinidos[] = $q['key'];

                                    }else if(count($SegundoBloque) < $CantidadPorBloques && collect($SegundoBloque)->whereIn('jugador_local.grupo_id', $Grupo)->whereIn('jugador_rival.grupo_id', $Grupo)->first() == null)
                                    {
                                        $SegundoBloque[] = [
                                            'buy' => $q['buy'], 'key' => $q['key'],
                                            'jugador_local' => ['jugador_simple_id' => $q['jugador_local']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_local']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_local']['grupo_id'], 'grupo' => $q['jugador_local']['grupo'], 'nombres' => $q['jugador_local']['nombres']],
                                            'jugador_rival' => ['jugador_simple_id' => $q['jugador_rival']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_rival']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_rival']['grupo_id'], 'grupo' => $q['jugador_rival']['grupo'], 'nombres' => $q['jugador_rival']['nombres']]
                                        ];
                                        $PartidosKeysDefinidos[] = $q['key'];

                                    }else if(count($TercerBloque) < $CantidadPorBloques && collect($TercerBloque)->whereIn('jugador_local.grupo_id', $Grupo)->whereIn('jugador_rival.grupo_id', $Grupo)->first() == null)
                                    {
                                        $TercerBloque[] = [
                                            'buy' => $q['buy'], 'key' => $q['key'],
                                            'jugador_local' => ['jugador_simple_id' => $q['jugador_local']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_local']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_local']['grupo_id'], 'grupo' => $q['jugador_local']['grupo'], 'nombres' => $q['jugador_local']['nombres']],
                                            'jugador_rival' => ['jugador_simple_id' => $q['jugador_rival']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_rival']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_rival']['grupo_id'], 'grupo' => $q['jugador_rival']['grupo'], 'nombres' => $q['jugador_rival']['nombres']]
                                        ];
                                        $PartidosKeysDefinidos[] = $q['key'];

                                    }else if(count($CuartoBloque) < $CantidadPorBloques && collect($CuartoBloque)->whereIn('jugador_local.grupo_id', $Grupo)->whereIn('jugador_rival.grupo_id', $Grupo)->first() == null)
                                    {
                                        $CuartoBloque[] = [
                                            'buy' => $q['buy'], 'key' => $q['key'],
                                            'jugador_local' => ['jugador_simple_id' => $q['jugador_local']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_local']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_local']['grupo_id'], 'grupo' => $q['jugador_local']['grupo'], 'nombres' => $q['jugador_local']['nombres']],
                                            'jugador_rival' => ['jugador_simple_id' => $q['jugador_rival']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_rival']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_rival']['grupo_id'], 'grupo' => $q['jugador_rival']['grupo'], 'nombres' => $q['jugador_rival']['nombres']]
                                        ];
                                        $PartidosKeysDefinidos[] = $q['key'];
                                    }
                                }
                            }
                        }
                        $y++;
                    }

                    if(count(collect($PartidosFaseFinal)->whereNotIn('key', $PartidosKeysDefinidos)) == 0){ break; }
                    else{ $i++; }
                }

                $PartidosNoDefinidos = collect($PartidosFaseFinal)->whereNotIn('key', $PartidosKeysDefinidos);
                foreach ($PartidosNoDefinidos as $q)
                {
                    if(!in_array($q['key'], $PartidosKeysDefinidos))
                    {
                        $Grupo = [$q['jugador_local']['grupo_id'], $q['jugador_rival']['grupo_id']];
                        if(count($PrimerBloque) < $CantidadPorBloques)
                        {
                            $PrimerBloque[] = [
                                'buy' => $q['buy'], 'key' => $q['key'],
                                'jugador_local' => ['jugador_simple_id' => $q['jugador_local']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_local']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_local']['grupo_id'], 'grupo' => $q['jugador_local']['grupo'], 'nombres' => $q['jugador_local']['nombres']],
                                'jugador_rival' => ['jugador_simple_id' => $q['jugador_rival']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_rival']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_rival']['grupo_id'], 'grupo' => $q['jugador_rival']['grupo'], 'nombres' => $q['jugador_rival']['nombres']]
                            ];
                            $PartidosKeysDefinidos[] = $q['key'];

                        }else if(count($SegundoBloque) < $CantidadPorBloques)
                        {
                            $SegundoBloque[] = [
                                'buy' => $q['buy'], 'key' => $q['key'],
                                'jugador_local' => ['jugador_simple_id' => $q['jugador_local']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_local']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_local']['grupo_id'], 'grupo' => $q['jugador_local']['grupo'], 'nombres' => $q['jugador_local']['nombres']],
                                'jugador_rival' => ['jugador_simple_id' => $q['jugador_rival']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_rival']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_rival']['grupo_id'], 'grupo' => $q['jugador_rival']['grupo'], 'nombres' => $q['jugador_rival']['nombres']]
                            ];
                            $PartidosKeysDefinidos[] = $q['key'];

                        }else if(count($TercerBloque) < $CantidadPorBloques)
                        {
                            $TercerBloque[] = [
                                'buy' => $q['buy'], 'key' => $q['key'],
                                'jugador_local' => ['jugador_simple_id' => $q['jugador_local']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_local']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_local']['grupo_id'], 'grupo' => $q['jugador_local']['grupo'], 'nombres' => $q['jugador_local']['nombres']],
                                'jugador_rival' => ['jugador_simple_id' => $q['jugador_rival']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_rival']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_rival']['grupo_id'], 'grupo' => $q['jugador_rival']['grupo'], 'nombres' => $q['jugador_rival']['nombres']]
                            ];
                            $PartidosKeysDefinidos[] = $q['key'];

                        }else if(count($CuartoBloque) < $CantidadPorBloques)
                        {
                            $CuartoBloque[] = [
                                'buy' => $q['buy'], 'key' => $q['key'],
                                'jugador_local' => ['jugador_simple_id' => $q['jugador_local']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_local']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_local']['grupo_id'], 'grupo' => $q['jugador_local']['grupo'], 'nombres' => $q['jugador_local']['nombres']],
                                'jugador_rival' => ['jugador_simple_id' => $q['jugador_rival']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_rival']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_rival']['grupo_id'], 'grupo' => $q['jugador_rival']['grupo'], 'nombres' => $q['jugador_rival']['nombres']]
                            ];
                            $PartidosKeysDefinidos[] = $q['key'];
                        }
                    }
                }

                //DEFINICIÓN DE LOS 4 BLOQUES
                $BloquesFaseFinal = [$PrimerBloque, $SegundoBloque, $TercerBloque, $CuartoBloque];  $Partidos = [];

                $Partido = Partido::where('torneo_id', $TorneoCategoria->torneo_id)->where('torneo_categoria_id', $TorneoCategoria->id)->whereNull('fase')
                ->where('estado_id', App::$ESTADO_FINALIZADO)->orderBy('id', 'desc')->first();

                $fechaInicioPartido = Carbon::parse($TorneoCategoria->torneo->fecha_inicio);
                $fechaFinalPartido = Carbon::parse($TorneoCategoria->torneo->fecha_inicio)->addDay(6);

                if($Partido != null){
                    $fechaInicioPartido = Carbon::parse($Partido->fecha_final)->addDay(1);
                    $fechaFinalPartido = Carbon::parse($fechaInicioPartido)->addDay(6);
                }

                foreach ($BloquesFaseFinal as $key => $bloque)
                {
                    $Position = 1;
                    foreach ($bloque as $q) {
                        $Partidos[] = [
                            'comunidad_id' => Auth::guard('web')->user()->comunidad_id,
                            'torneo_id' => $TorneoCategoria->torneo->id,
                            'torneo_categoria_id' => $TorneoCategoria->id,
                            'grupo_id' => null,
                            'multiple' => $TorneoCategoria->multiple,
                            'jugador_local_uno_id' => $q['jugador_local']['jugador_simple_id'],
                            'jugador_local_dos_id' => $TorneoCategoria->multiple ? $q['jugador_local']['jugador_dupla_id'] : null,
                            'jugador_rival_uno_id' => $q['jugador_rival']['jugador_simple_id'],
                            'jugador_rival_dos_id' => $TorneoCategoria->multiple ? $q['jugador_rival']['jugador_dupla_id'] : null,
                            'fecha_inicio' => $fechaInicioPartido, 'fecha_final' => $fechaFinalPartido, 'hora_inicio' => null,
                            'hora_final' => null, 'resultado' => null,
                            'estado_id' => filter_var($q['buy'], FILTER_VALIDATE_BOOLEAN) ? App::$ESTADO_FINALIZADO: App::$ESTADO_PENDIENTE,
                            'buy' => $q['buy'],
                            'bloque' => ($key + 1),
                            'position' => $Position,
                            'fase' => count($PartidosFaseFinal),
                            'user_create_id' => Auth::guard('web')->user()->id,
                        ];
                        $Position++;
                    }
                }

                $PartidosBuysPrimeraEtapa = collect($Partidos)->where('buy', true);
                if(count($PartidosBuysPrimeraEtapa) > 0)
                {
                    $PartidosPrimeraEtapaPrimerBloque = array_values($PartidosBuysPrimeraEtapa->where('bloque', 1)->toArray());
                    $PartidosPrimeraEtapaSegundBloque = array_values($PartidosBuysPrimeraEtapa->where('bloque', 2)->toArray());
                    $PartidosPrimeraEtapaTercerBloque = array_values($PartidosBuysPrimeraEtapa->where('bloque', 3)->toArray());
                    $PartidosPrimeraEtapaCuartoBloque = array_values($PartidosBuysPrimeraEtapa->where('bloque', 4)->toArray());

                    $PartidosPrimeraEtapaFaseFinal = [$PartidosPrimeraEtapaPrimerBloque, $PartidosPrimeraEtapaSegundBloque, $PartidosPrimeraEtapaTercerBloque, $PartidosPrimeraEtapaCuartoBloque];

                    foreach ($PartidosPrimeraEtapaFaseFinal as $key => $q)
                    {
                        if(count($q) > 0) {
                            switch (count($q))
                            {
                                case 1:
                                    $Partidos[] = [
                                        'comunidad_id' => Auth::guard('web')->user()->comunidad_id,
                                        'torneo_id' => $TorneoCategoria->torneo->id, 'torneo_categoria_id' => $TorneoCategoria->id,
                                        'grupo_id' => null, 'multiple' => $TorneoCategoria->multiple,
                                        'jugador_local_uno_id' => $q[0]['position'] == 1 ? $q[0]['jugador_local_uno_id'] : null,
                                        'jugador_local_dos_id' => $q[0]['position'] == 1  && $TorneoCategoria->multiple ? $q[0]['jugador_local_dos_id'] : null,
                                        'jugador_rival_uno_id' => $q[0]['position'] == 2 ? $q[0]['jugador_local_uno_id'] : null,
                                        'jugador_rival_dos_id' => $q[0]['position'] == 2  && $TorneoCategoria->multiple ? $q[0]['jugador_local_dos_id'] : null,
                                        'fecha_inicio' => $fechaInicioPartido, 'fecha_final' => $fechaFinalPartido, 'hora_inicio' => null,
                                        'hora_final' => null, 'resultado' => null, 'estado_id' => App::$ESTADO_PENDIENTE, 'buy' => false,
                                        'bloque' => ($key+1), 'position' => $q[0]['position'], 'fase' => count($PartidosFaseFinal)/2,
                                        'user_create_id' => Auth::guard('web')->user()->id,
                                    ];
                                    break;
                                case 2:
                                    $Partidos[] = [
                                        'comunidad_id' => Auth::guard('web')->user()->comunidad_id,
                                        'torneo_id' => $TorneoCategoria->torneo->id, 'torneo_categoria_id' => $TorneoCategoria->id,
                                        'grupo_id' => null, 'multiple' => $TorneoCategoria->multiple,
                                        'jugador_local_uno_id' => $q[0]['jugador_local_uno_id'],
                                        'jugador_local_dos_id' => $TorneoCategoria->multiple ? $q[0]['jugador_local_dos_id'] : null,
                                        'jugador_rival_uno_id' => $q[1]['jugador_local_uno_id'],
                                        'jugador_rival_dos_id' => $TorneoCategoria->multiple ? $q[1]['jugador_local_dos_id'] : null,
                                        'fecha_inicio' => $fechaInicioPartido, 'fecha_final' => $fechaFinalPartido, 'hora_inicio' => null,
                                        'hora_final' => null, 'resultado' => null, 'estado_id' => App::$ESTADO_PENDIENTE, 'buy' => false,
                                        'bloque' => ($key+1), 'position' => $q[0]['position'], 'fase' => count($PartidosFaseFinal)/2,
                                        'user_create_id' => Auth::guard('web')->user()->id,
                                    ];
                                    break;
                                case 3:
                                    $Partidos[] = [
                                        'comunidad_id' => Auth::guard('web')->user()->comunidad_id,
                                        'torneo_id' => $TorneoCategoria->torneo->id, 'torneo_categoria_id' => $TorneoCategoria->id,
                                        'grupo_id' => null, 'multiple' => $TorneoCategoria->multiple,
                                        'jugador_local_uno_id' => $q[0]['jugador_local_uno_id'],
                                        'jugador_local_dos_id' => $TorneoCategoria->multiple ? $q[0]['jugador_local_dos_id'] : null,
                                        'jugador_rival_uno_id' => $q[1]['jugador_local_uno_id'],
                                        'jugador_rival_dos_id' => $TorneoCategoria->multiple ? $q[1]['jugador_local_dos_id'] : null,
                                        'fecha_inicio' => $fechaInicioPartido, 'fecha_final' => $fechaFinalPartido, 'hora_inicio' => null,
                                        'hora_final' => null, 'resultado' => null, 'estado_id' => App::$ESTADO_PENDIENTE, 'buy' => false,
                                        'bloque' => ($key+1), 'position' => $q[0]['position'], 'fase' => count($PartidosFaseFinal)/2,
                                        'user_create_id' => Auth::guard('web')->user()->id,
                                    ];

                                    $Partidos[] = [
                                        'comunidad_id' => Auth::guard('web')->user()->comunidad_id,
                                        'torneo_id' => $TorneoCategoria->torneo->id, 'torneo_categoria_id' => $TorneoCategoria->id,
                                        'grupo_id' => null, 'multiple' => $TorneoCategoria->multiple,
                                        'jugador_local_uno_id' => $q[2]['position'] == 1 ? $q[2]['jugador_local_uno_id'] : null,
                                        'jugador_local_dos_id' => $q[2]['position'] == 1 && $TorneoCategoria->multiple ? $q[2]['jugador_local_dos_id'] : null,
                                        'jugador_rival_uno_id' => $q[2]['position'] == 2 ? $q[2]['jugador_local_uno_id'] : null,
                                        'jugador_rival_dos_id' => $q[2]['position'] == 2 && $TorneoCategoria->multiple ? $q[2]['jugador_local_dos_id'] : null,
                                        'fecha_inicio' => $fechaInicioPartido, 'fecha_final' => $fechaFinalPartido, 'hora_inicio' => null,
                                        'hora_final' => null, 'resultado' => null, 'estado_id' => App::$ESTADO_PENDIENTE, 'buy' => false,
                                        'bloque' => ($key+1), 'position' => $q[2]['position'], 'fase' => count($PartidosFaseFinal)/2,
                                        'user_create_id' => Auth::guard('web')->user()->id,
                                    ];
                                    break;
                                case 4:
                                    $Partidos[] = [
                                        'comunidad_id' => Auth::guard('web')->user()->comunidad_id,
                                        'torneo_id' => $TorneoCategoria->torneo->id, 'torneo_categoria_id' => $TorneoCategoria->id,
                                        'grupo_id' => null, 'multiple' => $TorneoCategoria->multiple,
                                        'jugador_local_uno_id' => $q[0]['jugador_local_uno_id'],
                                        'jugador_local_dos_id' => $TorneoCategoria->multiple ? $q[0]['jugador_local_dos_id'] : null,
                                        'jugador_rival_uno_id' => $q[1]['jugador_local_uno_id'],
                                        'jugador_rival_dos_id' => $TorneoCategoria->multiple ? $q[1]['jugador_local_dos_id'] : null,
                                        'fecha_inicio' => $fechaInicioPartido, 'fecha_final' => $fechaFinalPartido, 'hora_inicio' => null,
                                        'hora_final' => null, 'resultado' => null, 'estado_id' => App::$ESTADO_PENDIENTE, 'buy' => false,
                                        'bloque' => ($key+1), 'position' => $q[0]['position'], 'fase' => count($PartidosFaseFinal)/2,
                                        'user_create_id' => Auth::guard('web')->user()->id,
                                    ];

                                    $Partidos[] = [
                                        'comunidad_id' => Auth::guard('web')->user()->comunidad_id,
                                        'torneo_id' => $TorneoCategoria->torneo->id, 'torneo_categoria_id' => $TorneoCategoria->id,
                                        'grupo_id' => null, 'multiple' => $TorneoCategoria->multiple,
                                        'jugador_local_uno_id' => $q[2]['jugador_local_uno_id'],
                                        'jugador_local_dos_id' => $TorneoCategoria->multiple ? $q[2]['jugador_local_dos_id'] : null,
                                        'jugador_rival_uno_id' => $q[3]['jugador_local_uno_id'],
                                        'jugador_rival_dos_id' => $TorneoCategoria->multiple ? $q[3]['jugador_local_dos_id'] : null,
                                        'fecha_inicio' => $fechaInicioPartido, 'fecha_final' => $fechaFinalPartido, 'hora_inicio' => null,
                                        'hora_final' => null, 'resultado' => null, 'estado_id' => App::$ESTADO_PENDIENTE, 'buy' => false,
                                        'bloque' => ($key+1), 'position' => $q[2]['position'], 'fase' => count($PartidosFaseFinal)/2,
                                        'user_create_id' => Auth::guard('web')->user()->id,
                                    ];
                                    break;
                            }
                        }
                    }
                }

                /*TorneoCategoria::where('id', $request->torneo_categoria_id)->where('torneo_id', $request->torneo_id)
                ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
                ->update(['first_final' => false]);*/

                //INSERTANDO LOS PARTIDOS FINALES GENERADOS
                if($request->tipo == "manual")
                {
                    $PartidosManuales = [];
                    foreach (collect($Partidos)->where('fase', count($PartidosFaseFinal)) as $q)
                    {
                        $q['jugador_local_uno_id'] = null;
                        $q['jugador_local_dos_id'] = null;
                        $q['jugador_rival_uno_id'] = null;
                        $q['jugador_rival_dos_id'] = null;
                        $q['estado_id'] = App::$ESTADO_PENDIENTE;
                        $q['buy'] = false;
                        //$q['manual'] = true;
                        $PartidosManuales[] = $q;
                    }

                    TorneoCategoria::where('id', $request->torneo_categoria_id)->where('torneo_id', $request->torneo_id)
                    ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
                    ->update(['manual' => true]);

                    if($reload)
                    {
                        Partido::where('torneo_categoria_id', $TorneoCategoria->id)
                        ->where('torneo_id', $TorneoCategoria->torneo_id)->whereNotNull('fase')->delete();
                    }

                    //dd($PartidosManuales);

                    Partido::insert($PartidosManuales);

                }else{
                    Partido::insert($Partidos);
                }

                if($request->tipo == "random")
                {
                    TorneoCategoria::where('id', $request->torneo_categoria_id)->where('torneo_id', $request->torneo_id)
                    ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
                    ->update(['aleatorio' => true]);
                }

                DB::commit();

                $Result->Success = true;

            }else{
                $Result->Message = "El torneo categoria que intenta modificar, ya no se encuentra disponible";
            }
        } catch (\Exception $e) {
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }

        return response()->json($Result);
    }

    public function faseFinalPrePartidoJugadorListJson(Request $request)
    {
        $TorneoCategoria = TorneoCategoria::where('id', $request->torneo_categoria_id)->where('torneo_id', $request->torneo_id)
        ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})->first();

        $JugadoresClasificados = [];

        if($TorneoCategoria != null)
        {
            if(!$TorneoCategoria->first_final)
            {
                $TorneoGrupos = $TorneoCategoria->torneo->torneoGrupos()->where('torneo_categoria_id', $TorneoCategoria->id)->select('grupo_id')->groupBy('grupo_id')->get();

                $Clasifican = $TorneoCategoria->clasificados_terceros > 0 ? 3 : $TorneoCategoria->clasificados;

                foreach ($TorneoGrupos as $key => $q)
                {
                    //TODOS LOS JUGADORES DEL GRUPO
                    $Jugadores = $TorneoCategoria->torneo->torneoGrupos()->where('torneo_categoria_id', $TorneoCategoria->id)
                        ->where('grupo_id', $q->grupo_id)->get()->map(function ($q) use ($TorneoCategoria) {
                            return [
                                'jugador_simple_id' => $q->jugadorSimple->id,
                                'jugador_dupla_id' => $TorneoCategoria->multiple ? $q->jugadorDupla->id : null,
                                'nombres' => $TorneoCategoria->multiple ? ($q->jugadorSimple->nombre_completo . " + " . $q->jugadorDupla->nombre_completo) : $q->jugadorSimple->nombre_completo
                            ];
                        });

                    //SOLO LOS JUGADORES CLASISIFICADOS POR GRUPO
                    $TablePositions = [];
                    foreach ($Jugadores as $key2 => $q2)
                    {
                        if ($TorneoCategoria->multiple)
                        {
                            $PartidosComoLocal = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                                ->where('grupo_id', $q->grupo_id)->where('jugador_local_uno_id', $q2['jugador_simple_id'])
                                ->where('jugador_local_dos_id', $q2['jugador_dupla_id'])->whereNull('fase')->get());

                            $PartidosComoRival = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                                ->where('grupo_id', $q->grupo_id)->where('jugador_rival_uno_id', $q2['jugador_simple_id'])
                                ->where('jugador_rival_dos_id', $q2['jugador_dupla_id'])->whereNull('fase')->get());

                            $SetsGanados = 0; $SetsPerdidos = 0; $GamesGanados = 0; $GamesPerdidos = 0; $Puntos = 0;

                            foreach ($PartidosComoLocal as $p){
                                if($p->jugador_ganador_uno_id == $q2['jugador_simple_id'])
                                {   //NO Rival
                                    $SetsGanados += $p->jugador_local_set; $SetsPerdidos += $p->jugador_rival_set;
                                    $GamesGanados += $p->jugador_local_juego; $GamesPerdidos += $p->jugador_rival_juego;
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                                }else{
                                    //Rival
                                    $SetsGanados += $p->jugador_rival_set; $SetsPerdidos += $p->jugador_local_set;
                                    $GamesGanados += $p->jugador_rival_juego; $GamesPerdidos += $p->jugador_local_juego;
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                                }
                            }

                            foreach ($PartidosComoRival as $p){
                                if($p->jugador_ganador_uno_id == $q2['jugador_simple_id'])
                                {   //NO Rival
                                    $SetsGanados += $p->jugador_local_set; $SetsPerdidos += $p->jugador_rival_set;
                                    $GamesGanados += $p->jugador_local_juego; $GamesPerdidos += $p->jugador_rival_juego;
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                                }else{
                                    //Rival
                                    $SetsGanados += $p->jugador_rival_set; $SetsPerdidos += $p->jugador_local_set;
                                    $GamesGanados += $p->jugador_rival_juego; $GamesPerdidos += $p->jugador_local_juego;
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                                }
                            }

                            $SetsDiferencias = $SetsGanados - $SetsPerdidos;

                            $GamesDiferencias = $GamesGanados - $GamesPerdidos;

                            $Puntos = $Puntos * $TorneoCategoria->torneo->valor_set;

                            $TablePositions[] = [
                                'key' => ($q2['jugador_simple_id'] . '-' . $q2['jugador_dupla_id']),
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

                            $SetsGanados = 0; $SetsPerdidos = 0; $GamesGanados = 0; $GamesPerdidos = 0; $Puntos = 0;

                            foreach ($PartidosComoLocal as $p){
                                if($p->jugador_ganador_uno_id == $q2['jugador_simple_id'])
                                {   //NO Rival
                                    $SetsGanados += $p->jugador_local_set; $SetsPerdidos += $p->jugador_rival_set;
                                    $GamesGanados += $p->jugador_local_juego; $GamesPerdidos += $p->jugador_rival_juego;
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                                }else{
                                    //Rival
                                    $SetsGanados += $p->jugador_rival_set; $SetsPerdidos += $p->jugador_local_set;
                                    $GamesGanados += $p->jugador_rival_juego; $GamesPerdidos += $p->jugador_local_juego;
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                                }
                            }

                            foreach ($PartidosComoRival as $p){
                                if($p->jugador_ganador_uno_id == $q2['jugador_simple_id'])
                                {   //NO Rival
                                    $SetsGanados += $p->jugador_local_set; $SetsPerdidos += $p->jugador_rival_set;
                                    $GamesGanados += $p->jugador_local_juego; $GamesPerdidos += $p->jugador_rival_juego;
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                                }else{
                                    //Rival
                                    $SetsGanados += $p->jugador_rival_set; $SetsPerdidos += $p->jugador_local_set;
                                    $GamesGanados += $p->jugador_rival_juego; $GamesPerdidos += $p->jugador_local_juego;
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                                }
                            }

                            $SetsDiferencias = $SetsGanados - $SetsPerdidos;

                            $GamesDiferencias = $GamesGanados - $GamesPerdidos;

                            $Puntos = $Puntos * $TorneoCategoria->torneo->valor_set;

                            $TablePositions[] = [
                                'key' => $q2['jugador_simple_id'],
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
                                'puntos' => $Puntos
                            ];
                        }
                    }

                    $JugadoresClasificados[] = ['Clasificados' => App::multiPropertySort(collect($TablePositions), [ ['column' => 'puntos', 'order' => 'desc'],['column' => 'setsDiferencias', 'order' => 'desc'], ['column' => 'gamesDiferencias', 'order' => 'desc'], ['column' => 'setsGanados', 'order' => 'desc'], ['column' => 'gamesGanados', 'order' => 'desc']])->take($Clasifican)];
                    //$JugadoresClasificados = array_merge($JugadoresClasificados, App::multiPropertySort(collect($TablePositions), [ ['column' => 'puntos', 'order' => 'desc'], ['column' => 'setsDiferencias', 'order' => 'desc'], ['column' => 'gamesDiferencias', 'order' => 'desc'], ['column' => 'setsGanados', 'order' => 'desc'], ['column' => 'gamesGanados', 'order' => 'desc']])->take($Clasifican)->toArray());
                }

                //CLASIFICADOS POR CÁLCULO
                $PrimerosLugares = [];  $SegundoLugares = [];  $TercerosLugares = [];

                foreach ($JugadoresClasificados as $key => $value)
                {
                    if($Clasifican == 1) $PrimerosLugares[] = $value['Clasificados']->first();
                    else if($Clasifican == 2) {
                        $PrimerosLugares[] = $value['Clasificados']->first();
                        $SegundoLugares[] = $value['Clasificados']->last();
                    }else{
                        $PrimerosLugares[] = $value['Clasificados']->first();
                        $TercerosLugares[] = $value['Clasificados']->last();
                    }
                }

                if($Clasifican == 3)
                {
                    $Clasificados = array_merge(collect($PrimerosLugares)->pluck('key')->toArray(), collect($TercerosLugares)->pluck('key')->toArray());
                    foreach (collect($JugadoresClasificados)->pluck('Clasificados') as $key => $value){
                        foreach ($value as $ke2 => $value2){
                            if(!in_array($value2['key'], $Clasificados)) $SegundoLugares[] = $value2;
                        }
                    }
                    $TercerosLugares = App::multiPropertySort(collect($TercerosLugares), [ ['column' => 'puntos', 'order' => 'desc'],['column' => 'setsDiferencias', 'order' => 'desc'], ['column' => 'gamesDiferencias', 'order' => 'desc'], ['column' => 'setsGanados', 'order' => 'desc'], ['column' => 'gamesGanados', 'order' => 'desc']])->take($TorneoCategoria->clasificados_terceros)->toArray();
                }

                $PrimerosLugares = App::multiPropertySort(collect($PrimerosLugares), [ ['column' => 'puntos', 'order' => 'desc'],['column' => 'setsDiferencias', 'order' => 'desc'], ['column' => 'gamesDiferencias', 'order' => 'desc'], ['column' => 'setsGanados', 'order' => 'desc'], ['column' => 'gamesGanados', 'order' => 'desc']]);
                $SegundoLugares = App::multiPropertySort(collect($SegundoLugares), [ ['column' => 'puntos', 'order' => 'desc'],['column' => 'setsDiferencias', 'order' => 'desc'], ['column' => 'gamesDiferencias', 'order' => 'desc'], ['column' => 'setsGanados', 'order' => 'desc'], ['column' => 'gamesGanados', 'order' => 'desc']]);
                $TercerosLugares = App::multiPropertySort(collect($TercerosLugares), [ ['column' => 'puntos', 'order' => 'desc'],['column' => 'setsDiferencias', 'order' => 'desc'], ['column' => 'gamesDiferencias', 'order' => 'desc'], ['column' => 'setsGanados', 'order' => 'desc'], ['column' => 'gamesGanados', 'order' => 'desc']]);

                $JugadoresClasificadosMerge = $PrimerosLugares->merge($SegundoLugares)->merge($TercerosLugares);

                $Partidos = Partido::where('torneo_categoria_id', $request->torneo_categoria_id)->where('torneo_id', $request->torneo_id)
                ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
                ->where(function ($q) use ($request){
                    if($request->partido_id != null && intval($request->partido_id)> 0) $q->where('id', '!=', $request->partido_id);
                })->whereNotNull('fase')->get();

                $JugadoresNoDisponibles = [];

                if($TorneoCategoria->multiple)
                {
                    foreach ($Partidos as $q){
                        if($q->jugador_local_uno_id != null && $q->jugador_local_dos_id) $JugadoresNoDisponibles[] = $q->jugador_local_uno_id."-".$q->jugador_local_dos_id;
                        if($q->jugador_rival_uno_id != null && $q->jugador_rival_dos_id) $JugadoresNoDisponibles[] = $q->jugador_rival_uno_id."-".$q->jugador_rival_dos_id;
                    }
                }else
                {
                    $JugadoresNoDisponibles = array_merge($JugadoresNoDisponibles, $Partidos->pluck('jugador_local_uno_id')->toArray());
                    $JugadoresNoDisponibles = array_merge($JugadoresNoDisponibles, $Partidos->pluck('jugador_rival_uno_id')->toArray());
                    $JugadoresNoDisponibles = array_merge(array_filter($JugadoresNoDisponibles), [$request->jugador_selected_id]);
                }

                $JugadoresClasificados = $JugadoresClasificadosMerge->where('key', '!=', $request->jugador_selected_id)
                ->whereNotIn('key', array_filter($JugadoresNoDisponibles))
                ->filter(function($q) use ($request){ return str_contains(App::Unaccent(strtolower($q['nombres'])), App::Unaccent(strtolower($request->nombre))); })
                ->map(function ($q){return ['id' => $q['key'], 'text' => $q['nombres']]; })->toArray();

            }else{

                $Partidos = Partido::where('torneo_categoria_id', $request->torneo_categoria_id)->where('torneo_id', $request->torneo_id)
                ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
                ->where(function ($q) use ($request){
                    if($request->partido_id != null && intval($request->partido_id)> 0) $q->where('id', '!=', $request->partido_id);
                })->whereNotNull('fase')->get();

                $JugadoresNoDisponibles = [];

                if($TorneoCategoria->multiple)
                {
                    foreach ($Partidos as $q){
                        if($q->jugador_local_uno_id != null && $q->jugador_local_dos_id) $JugadoresNoDisponibles[] = $q->jugador_local_uno_id."-".$q->jugador_local_dos_id;
                        if($q->jugador_rival_uno_id != null && $q->jugador_rival_dos_id) $JugadoresNoDisponibles[] = $q->jugador_rival_uno_id."-".$q->jugador_rival_dos_id;
                    }
                }else
                {
                    $JugadoresNoDisponibles = array_merge($JugadoresNoDisponibles, $Partidos->pluck('jugador_local_uno_id')->toArray());
                    $JugadoresNoDisponibles = array_merge($JugadoresNoDisponibles, $Partidos->pluck('jugador_rival_uno_id')->toArray());
                    $JugadoresNoDisponibles = array_merge(array_filter($JugadoresNoDisponibles), [$request->jugador_selected_id]);
                }

                $Jugadores = TorneoJugador::where('torneo_categoria_id', $TorneoCategoria->id)->get()
                ->map(function ($q) use ($TorneoCategoria){
                    return [
                        'key' => $TorneoCategoria->multiple ? ($q->jugador_simple_id.'-'.$q->jugador_dupla_id) : $q->jugador_simple_id,
                        'jugador_simple_id' => $q->jugadorSimple->id,
                        'jugador_dupla_id' => $TorneoCategoria->multiple ? $q->jugadorDupla->id : null,
                        'nombres' => $TorneoCategoria->multiple ? ($q->jugadorSimple->nombre_completo." + ".$q->jugadorDupla->nombre_completo) : $q->jugadorSimple->nombre_completo
                    ];
                });

                $JugadoresClasificados = $Jugadores->where('key', '!=', $request->jugador_selected_id)
                ->whereNotIn('key', array_filter($JugadoresNoDisponibles))
                ->filter(function($q) use ($request){ return str_contains(App::Unaccent(strtolower($q['nombres'])), App::Unaccent(strtolower($request->nombre))); })
                ->map(function ($q){return ['id' => $q['key'], 'text' => $q['nombres']]; })->toArray();
            }
        }

        return response()->json(['data' => array_values($JugadoresClasificados)]);
    }

    public function faseFinalPrePartidoStore(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null, 'Repeat' => null];

        try {

            DB::beginTransaction();

            $request->merge([
                'buy_all' => filter_var($request->buy_all, FILTER_VALIDATE_BOOLEAN),
                'buy' => $request->buy_all ? true : filter_var($request->buy, FILTER_VALIDATE_BOOLEAN),
            ]);

            $Validator = Validator::make($request->all(), [
                'id' => 'required|numeric',
                'position' => 'required|numeric',
                'bracket' => 'required',
                'buy' => 'required|boolean',
                'buy_all' => 'required|boolean',
                'jugador_local_id' => $request->buy_all ? 'nullable' : 'required',
                'jugador_rival_id' => $request->buy ? 'nullable' : 'required',
            ],
            [
                'jugador_local_id.required' => 'El jugador local es obligatorio.',
                'jugador_rival_id.required' => 'El jugador rival es obligatorio.',
            ]);

            if (!$Validator->fails())
            {
               $entity = Partido::where('id', $request->id)->whereHas('torneo', function ($q){
               $q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})->first();

               if($entity != null)
               {
                   $JugadorLocalUno = null; $JugadorLocalDos = null;
                   $JugadorRivalUno = null; $JugadorRivalDos = null;

                   $JugadorLocalUno = $request->jugador_local_id != null ? ($entity->torneoCategoria->multiple ? explode("-", $request->jugador_local_id)[0] : $request->jugador_local_id) : null;
                   $JugadorLocalDos = $request->jugador_local_id != null ? ($entity->torneoCategoria->multiple ? explode("-", $request->jugador_local_id)[1] : null) : null;

                   $JugadorRivalUno = $request->jugador_rival_id != null ? ($entity->torneoCategoria->multiple ? explode("-", $request->jugador_rival_id)[0] : $request->jugador_rival_id) : null;
                   $JugadorRivalDos = $request->jugador_rival_id != null ? ($entity->torneoCategoria->multiple ? explode("-", $request->jugador_rival_id)[1] : null) : null;

                   /*$entity->jugador_local_uno_id = $request->jugador_local_id != null ? ($entity->torneoCategoria->multiple ? explode("-", $request->jugador_local_id)[0] : $request->jugador_local_id) : null;
                   $entity->jugador_local_dos_id = $request->jugador_local_id != null ? ($entity->torneoCategoria->multiple ? explode("-", $request->jugador_local_id)[1] : null) : null;
                   $entity->jugador_rival_uno_id = $request->jugador_rival_id != null ? ($entity->torneoCategoria->multiple ? explode("-", $request->jugador_rival_id)[0] : $request->jugador_rival_id) : null;
                   $entity->jugador_rival_dos_id = $request->jugador_rival_id != null ? ($entity->torneoCategoria->multiple ? explode("-", $request->jugador_rival_id)[1] : null) : null;*/

                   $entity->jugador_local_uno_id = $JugadorLocalUno;
                   $entity->jugador_local_dos_id = $JugadorLocalDos;
                   $entity->jugador_rival_uno_id = $JugadorRivalUno;
                   $entity->jugador_rival_dos_id = $JugadorRivalDos;

                   $entity->position = $request->position;
                   $entity->bracket = $request->bracket;
                   $entity->buy = $request->buy;
                   $entity->buy_all = $request->buy_all;

                   if(!$entity->buy && !$entity->buy_all)
                   {
                       $Result->Repeat = Partido::where('torneo_categoria_id', $entity->torneo_categoria_id)
                       ->where('torneo_id', $entity->torneo_id)->whereNull('fase')
                       ->where(function ($q) use ($JugadorLocalUno, $JugadorRivalUno){
                           $q->where(function ($q2) use ($JugadorLocalUno, $JugadorRivalUno){ $q2->where('jugador_local_uno_id', $JugadorLocalUno)->where('jugador_rival_uno_id', $JugadorRivalUno);})
                           ->orWhere(function ($q2) use ($JugadorLocalUno, $JugadorRivalUno){ $q2->where('jugador_local_uno_id', $JugadorRivalUno)->where('jugador_rival_uno_id', $JugadorLocalUno); });
                       })->first();
                   }

                   if($entity->save())
                   {
                       DB::commit();
                       $Result->Success = true;
                   }
               }
            }

            $Result->Errors = $Validator->errors();

        }catch (\Exception $e){
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }

        return response()->json($Result);
    }

    public function faseFinalPrePartidoFinish(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try {

            DB::beginTransaction();

            $TorneoCategoria = TorneoCategoria::where('id', $request->torneo_categoria_id)->where('torneo_id', $request->torneo_id)
            ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})->first();

            if($TorneoCategoria != null)
            {
                $Partidos = Partido::where('torneo_categoria_id', $request->torneo_categoria_id)
                ->where('torneo_id', $request->torneo_id)
                ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
                ->whereNotNull('fase')->get();

                if($Partidos->whereNull('jugador_local_uno_id')->whereNull('jugador_rival_uno_id')->where('buy_all', false)->count() == 0)
                {
                    $Partidos = $Partidos->where('buy', true);

                    if ($Partidos != null && count($Partidos) > 0)
                    {
                        $PartidosDobleBuy = $Partidos;

                        do{
                            $PartidosDobleBuyTemp = [];

                            foreach ($PartidosDobleBuy as $q)
                            {
                                $SiguienteFase = ($q->fase/2);

                                $PartidoNext = Partido::where('torneo_id', $q->torneo_id)
                                    ->where('torneo_categoria_id', $q->torneo_categoria_id)->where('fase', $SiguienteFase)
                                    ->where(function ($query) use ($q){if($q->fase == 16){ $query->where('bracket', $q->bracket); }})
                                    ->whereIn('bloque', $SiguienteFase == 1 ? [1] : ($SiguienteFase == 2 ? (in_array($q->bloque, [1, 3]) ? [1] : [2]) : [$q->bloque]))
                                    ->where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->first();

                                if($PartidoNext == null)
                                {
                                    $PartidoNext = new Partido();
                                    $PartidoNext->comunidad_id = Auth::guard('web')->user()->comunidad_id;
                                    $PartidoNext->torneo_id = $q->torneo_id;
                                    $PartidoNext->torneo_categoria_id = $q->torneo_categoria_id;
                                    $PartidoNext->estado_id = App::$ESTADO_PENDIENTE;
                                    $PartidoNext->multiple = $q->multiple;
                                    $PartidoNext->bracket = $q->bracket;

                                    $PartidoNext->buy = filter_var($q->buy_all, FILTER_VALIDATE_BOOLEAN);
                                    if($PartidoNext->buy){ $PartidosDobleBuyTemp[] = $PartidoNext;}

                                    if($q->fase == 16){
                                        $PartidoNext->position = $q->bracket == "upper" ? 1 : 2;
                                    }else{
                                        $PartidoNext->position = $q->position;
                                    }
                                    $PartidoNext->fecha_inicio = Carbon::parse($q->fecha_final)->addDay(1);
                                    $PartidoNext->fecha_final = Carbon::parse($q->fecha_final)->addDay(7);
                                    $PartidoNext->user_create_id = Auth::guard('web')->user()->id;
                                    $PartidoNext->fase = $SiguienteFase;
                                    $PartidoNext->bloque = $SiguienteFase == 1 ? 1 : (in_array($q->fase, [16, 8]) ? $q->bloque : (in_array($q->bloque, [1, 3]) ? 1 : 2));
                                }else{
                                    $PartidoNext->user_update_id = Auth::guard('web')->user()->id;
                                    if(filter_var($q->buy_all, FILTER_VALIDATE_BOOLEAN))
                                    {
                                        if($PartidoNext->buy) $PartidoNext->buy_all = true;
                                        else $PartidoNext->buy = true;

                                        $PartidosDobleBuyTemp[] = $PartidoNext;
                                    }
                                }

                                if($q->position == 1){
                                    $PartidoNext->jugador_local_uno_id = $q->jugador_local_uno_id;
                                    $PartidoNext->jugador_local_dos_id = $q->jugador_local_dos_id;
                                }else{
                                    $PartidoNext->jugador_rival_uno_id = $q->jugador_local_uno_id;
                                    $PartidoNext->jugador_rival_dos_id = $q->jugador_local_dos_id;
                                }


                                if($PartidoNext->save())
                                {
                                    $Partido = Partido::find($q->id);
                                    $Partido->jugador_ganador_uno_id = $q->jugador_local_uno_id;
                                    $Partido->jugador_ganador_dos_id = $TorneoCategoria->multiple ? $q->jugador_local_dos_id : null;
                                    $Partido->resultado = "";
                                    $Partido->jugador_local_set = filter_var($q->buy_all, FILTER_VALIDATE_BOOLEAN) && $PartidoNext->buy ? 0 : 2;
                                    $Partido->jugador_local_juego =  filter_var($q->buy_all, FILTER_VALIDATE_BOOLEAN) && $PartidoNext->buy ? 0 : 12;
                                    $Partido->jugador_rival_set = 0;
                                    $Partido->jugador_rival_juego = 0;
                                    $Partido->estado_id = App::$ESTADO_FINALIZADO;
                                    $Partido->save();
                                }
                            }

                            $PartidosDobleBuy = $PartidosDobleBuyTemp;

                        }while(count($PartidosDobleBuy));





                        /*$PartidosDobleBuy2 = [];
                        if(count($PartidosDobleBuy))
                        {
                            foreach ($PartidosDobleBuy as $q)
                            {
                                $SiguienteFase = ($q->fase/2);

                                $PartidoNext = Partido::where('torneo_id', $q->torneo_id)
                                    ->where('torneo_categoria_id', $q->torneo_categoria_id)->where('fase', $SiguienteFase)
                                    ->where(function ($query) use ($q){if($q->fase == 16){ $query->where('bracket', $q->bracket); }})
                                    ->whereIn('bloque', $SiguienteFase == 1 ? [1] : ($SiguienteFase == 2 ? (in_array($q->bloque, [1, 3]) ? [1] : [2]) : [$q->bloque]))
                                    ->where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->first();

                                if($PartidoNext == null)
                                {
                                    $PartidoNext = new Partido();
                                    $PartidoNext->comunidad_id = Auth::guard('web')->user()->comunidad_id;
                                    $PartidoNext->torneo_id = $q->torneo_id;
                                    $PartidoNext->torneo_categoria_id = $q->torneo_categoria_id;
                                    $PartidoNext->estado_id = App::$ESTADO_PENDIENTE;
                                    $PartidoNext->multiple = $q->multiple;
                                    $PartidoNext->bracket = $q->bracket;

                                    $PartidoNext->buy = filter_var($q->buy_all, FILTER_VALIDATE_BOOLEAN);

                                    if($q->fase == 16){
                                        $PartidoNext->position = $q->bracket == "upper" ? 1 : 2;
                                    }else{
                                        $PartidoNext->position = $q->position;
                                    }
                                    $PartidoNext->fecha_inicio = Carbon::parse($q->fecha_final)->addDay(1);
                                    $PartidoNext->fecha_final = Carbon::parse($q->fecha_final)->addDay(7);
                                    $PartidoNext->user_create_id = Auth::guard('web')->user()->id;
                                    $PartidoNext->fase = $SiguienteFase;
                                    $PartidoNext->bloque = $SiguienteFase == 1 ? 1 : (in_array($q->fase, [16, 8]) ? $q->bloque : (in_array($q->bloque, [1, 3]) ? 1 : 2));
                                }else{
                                    $PartidoNext->user_update_id = Auth::guard('web')->user()->id;
                                    if(filter_var($q->buy_all, FILTER_VALIDATE_BOOLEAN) && $PartidoNext->buy)
                                    {
                                        $PartidoNext->buy_all = true;
                                        $PartidosDobleBuy2[] = $PartidoNext;
                                    }
                                }

                                if($q->position == 1){
                                    $PartidoNext->jugador_local_uno_id = $q->jugador_local_uno_id;
                                    $PartidoNext->jugador_local_dos_id = $q->jugador_local_dos_id;
                                }else{
                                    $PartidoNext->jugador_rival_uno_id = $q->jugador_local_uno_id;
                                    $PartidoNext->jugador_rival_dos_id = $q->jugador_local_dos_id;
                                }

                                if($PartidoNext->save())
                                {
                                    $Partido = Partido::find($q->id);
                                    $Partido->jugador_ganador_uno_id = $q->jugador_local_uno_id;
                                    $Partido->jugador_ganador_dos_id = $TorneoCategoria->multiple ? $q->jugador_local_dos_id : null;
                                    $Partido->resultado = "";
                                    $Partido->jugador_local_set = filter_var($q->buy_all, FILTER_VALIDATE_BOOLEAN) && $PartidoNext->buy ? 0 : 2;
                                    $Partido->jugador_local_juego =  filter_var($q->buy_all, FILTER_VALIDATE_BOOLEAN) && $PartidoNext->buy ? 0 : 12;
                                    $Partido->jugador_rival_set = 0;
                                    $Partido->jugador_rival_juego = 0;
                                    $Partido->estado_id = App::$ESTADO_FINALIZADO;
                                    $Partido->save();
                                }
                            }
                        }*/

                        //dd($PartidosDobleBuy2);
                    }

                    $TorneoCategoria->manual = false;
                    if($TorneoCategoria->save())
                    {
                        DB::commit();
                        $Result->Success = true;
                    }

                }else{
                    $Result->Message = "No se pudo finalizar las keys generadas porque aún existen cuadros vacíos.";
                }
            }else{
                $Result->Message = "Las llaves generadas que intenta modificar, ya no se encuentra disponible";
            }

        }catch (\Exception $e){
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }

        return response()->json($Result);
    }

    public function faseFinalPrePartidoDelete(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try {

            DB::beginTransaction();

            $entity = Partido::where('id', $request->partido_id)->whereHas('torneo', function ($q){
            $q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})->first();

            if($entity != null)
            {
                $entity->jugador_local_uno_id = null;
                $entity->jugador_local_dos_id = null;
                $entity->jugador_rival_uno_id = null;
                $entity->jugador_rival_dos_id = null;
                //$entity->manual = true;
                $entity->buy = false;

                if($entity->save())
                {
                    DB::commit();
                    $Result->Success = true;
                }

            }else{
                $Result->Message = "La llaves generada que intenta modificar, ya no se encuentra disponible";
            }

        }catch (\Exception $e){
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }

        return response()->json($Result);
    }

    public function faseFinalPrePartidoPartialView($torneo_id, $torneo_categoria_id, $id, $position, $bracket)
    {
        $TorneoCategoria = TorneoCategoria::where('id', $torneo_categoria_id)->where('torneo_id', $torneo_id)
        ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})->first();

        $Partido = Partido::where('id', $id)->where('torneo_categoria_id', $torneo_categoria_id)->where('torneo_id', $torneo_id)
        ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})->first();

        if($TorneoCategoria != null) return view('auth'.'.'.$this->viewName.'.ajax.final.prepartido.partialView', ['TorneoCategoria' => $TorneoCategoria, 'Partido' => $Partido, 'Position' => $position, 'Bracket' => $bracket, 'ViewName' => ucfirst($this->viewName)]);

        return  null;
    }

    public function faseFinalPrePartidoJugadorInfo(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try
        {
            $ComunidadId = Auth::guard('web')->user()->comunidad_id;

            $TorneoCategoria = TorneoCategoria::where('id', $request->torneo_categoria_id)->where('torneo_id', $request->torneo_id)
                ->whereHas('torneo', function ($q) use ($ComunidadId) {
                    $q->where('comunidad_id', $ComunidadId);
                })->first();

            if ($TorneoCategoria != null)
            {
                $Grupo = TorneoGrupo::where('torneo_id', $request->torneo_id)->where('torneo_categoria_id', $request->torneo_categoria_id)
                ->where(function ($q) use ($request, $TorneoCategoria) {
                    if($TorneoCategoria->multiple){
                        $keys = explode('-', $request->jugador_id);
                        $q->where('jugador_simple_id', $keys[0])->orWhere('jugador_dupla_id', $keys[0])->orWhere('jugador_simple_id', $keys[1])->orWhere('jugador_dupla_id', $keys[1]);
                    }else{
                        $q->where('jugador_simple_id', $request->jugador_id)->orWhere('jugador_dupla_id', $request->jugador_id);
                    }
                })->first();

                if($Grupo != null)
                {
                    //JUGADORES DEL GRUPO
                    $Jugadores = TorneoGrupo::where('torneo_categoria_id', $request->torneo_categoria_id)
                        ->where('grupo_id', $Grupo->grupo_id)->get()->map(function ($q) use ($TorneoCategoria) {
                            return [
                                'jugador_simple_id' => $q->jugadorSimple->id,
                                'jugador_dupla_id' => $TorneoCategoria->multiple ? $q->jugadorDupla->id : null,
                                'nombres' => $TorneoCategoria->multiple ? ($q->jugadorSimple->nombre_completo . " + " . $q->jugadorDupla->nombre_completo) : $q->jugadorSimple->nombre_completo
                            ];
                        });


                    //JUGADORES POSICIONES
                    $TablePositions = [];

                    foreach ($Jugadores as $key2 => $q2) {
                        if ($TorneoCategoria->multiple) {
                            $PartidosComoLocal = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                                ->where('grupo_id', $Grupo->grupo_id)->where('jugador_local_uno_id', $q2['jugador_simple_id'])
                                ->where('jugador_local_dos_id', $q2['jugador_dupla_id'])->whereNull('fase')->get());

                            $PartidosComoRival = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                                ->where('grupo_id', $Grupo->grupo_id)->where('jugador_rival_uno_id', $q2['jugador_simple_id'])
                                ->where('jugador_rival_dos_id', $q2['jugador_dupla_id'])->whereNull('fase')->get());

                            $PartidosJugados = 0;
                            $SetsGanados = 0;
                            $SetsPerdidos = 0;
                            $GamesGanados = 0;
                            $GamesPerdidos = 0;
                            $Puntos = 0;

                            foreach ($PartidosComoLocal as $p) {
                                if ($p->estado_id == App::$ESTADO_FINALIZADO) $PartidosJugados += 1;

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
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                                }
                            }

                            foreach ($PartidosComoRival as $p) {
                                if ($p->estado_id == App::$ESTADO_FINALIZADO) $PartidosJugados += 1;

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
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                                }
                            }

                            $SetsDiferencias = $SetsGanados - $SetsPerdidos;

                            $GamesDiferencias = $GamesGanados - $GamesPerdidos;

                            $Puntos = $Puntos * $TorneoCategoria->torneo->valor_set;

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
                                'puntos' => $Puntos
                            ];

                        } else {

                            $PartidosComoLocal = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                                ->where('grupo_id', $Grupo->grupo_id)->where('jugador_local_uno_id', $q2['jugador_simple_id'])->whereNull('fase')->get());

                            $PartidosComoRival = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                                ->where('grupo_id', $Grupo->grupo_id)->where('jugador_rival_uno_id', $q2['jugador_simple_id'])->whereNull('fase')->get());

                            $PartidosJugados = 0;
                            $SetsGanados = 0;
                            $SetsPerdidos = 0;
                            $GamesGanados = 0;
                            $GamesPerdidos = 0;
                            $Puntos = 0;

                            foreach ($PartidosComoLocal as $p) {
                                if ($p->estado_id == App::$ESTADO_FINALIZADO) $PartidosJugados += 1;

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
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                                }
                            }

                            foreach ($PartidosComoRival as $p) {
                                if ($p->estado_id == App::$ESTADO_FINALIZADO) $PartidosJugados += 1;

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
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                                }
                            }

                            $SetsDiferencias = $SetsGanados - $SetsPerdidos;

                            $GamesDiferencias = $GamesGanados - $GamesPerdidos;

                            $Puntos = $Puntos * $TorneoCategoria->torneo->valor_set;

                            $TablePositions[] = [
                                'jugador_simple_id' => $q2['jugador_simple_id'],
                                'jugador_dupla_id' => null,
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
                    }

                    $JugadoresPositions = App::multiPropertySort(collect($TablePositions), [
                        ['column' => 'puntos', 'order' => 'desc'], ['column' => 'setsDiferencias', 'order' => 'desc'], ['column' => 'gamesDiferencias', 'order' => 'desc'], ['column' => 'setsGanados', 'order' => 'desc'], ['column' => 'gamesGanados', 'order' => 'desc']]);

                    for ($i=0; $i < count($JugadoresPositions); $i++)
                    {
                        if ($request->jugador_id == $JugadoresPositions[$i]['jugador_simple_id'] || $request->jugador_id == $JugadoresPositions[$i]['jugador_dupla_id']) {
                            $Result->Success = true;
                            $Result->Message = $Grupo->nombre_grupo . ", Posición " . ($i+1);
                            break;
                        }
                    }
                }
            }

        }catch (\Exception $e){
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }

        return response()->json($Result);
    }

    public function faseFinalPartidoValidatePartialView(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        if($request->id != 0)
        {
            $entity = Partido::with('jugadorLocalUno')
            ->with('jugadorRivalUno')->where('torneo_id', $request->torneo_id)
            ->where('torneo_categoria_id', $request->torneo_categoria_id)
            ->where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
            ->where('id', $request->id)->first();
            if($entity != null)
            {
                if($entity->jugador_local_uno_id != null && $entity->jugador_rival_uno_id != null){
                    $Result->Success = true;
                } else if($entity->buy){
                    $Result->Message = "Este partido no tiene detalle porque la clasificación fue por Bye";
                }
            }else{
                $Result->Message = "No se encontró el partido que está buscando.";
            }
        }

        return response()->json($Result);
    }

    public function faseFinalPartidoPartialView($id, $position)
    {
        $entity = null;

        if($id != 0){
            $entity = Partido::with('jugadorLocalUno')->with('jugadorRivalUno')->where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->where('id', $id)->first();
            if(!$entity->buy && ($entity->jugador_local_uno_id == null || $entity->jugador_rival_uno_id == null)) return null;
        }

        return view('auth'.'.'.$this->viewName.'.ajax.final.partialView', ['Model' => $entity, 'Position' => $position, 'ViewName' => ucfirst($this->viewName)]);
    }

    public function faseFinalPartidoStore(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try {

            DB::beginTransaction();

            $request->merge([
                'fecha_actual' => Carbon::now()->toDateString(),
            ]);

            $Validator = Validator::make($request->all(), [
                'fecha_inicio' => 'required|date|date_format:Y-m-d',
                'fecha_final' => 'required|date|date_format:Y-m-d|after_or_equal:fecha_inicio',
                'resultado' => 'required',
                'jugador_local_id' => 'required',
                'jugador_local_set' => 'required|numeric',
                'jugador_local_juego' => 'required|numeric',
                'jugador_rival_id' => 'required',
                'jugador_rival_set' => 'required|numeric',
                'jugador_rival_juego' => 'required|numeric'
            ]);

            if (!$Validator->fails())
            {
                if($request->jugador_local_id == $request->jugador_rival_id){
                    $Result->Message = "El jugador ganador no puede ser el mismo al jugador rival";
                }else if($request->jugador_rival_set > $request->jugador_local_set) {
                    $Result->Message = "El jugador ganador no puede tener menor sets que el jugador rival";
                }else{
                    if($request->id != 0)
                    {
                        $Partido = Partido::where('id', $request->id)->where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->first();

                        if($Partido != null)
                        {
                            $Partido->jugador_ganador_uno_id = $Partido->multiple ? explode("-", $request->jugador_local_id)[0] : $request->jugador_local_id;
                            $Partido->jugador_ganador_dos_id = $Partido->multiple ? explode("-", $request->jugador_local_id)[1] : null;
                            $Partido->jugador_local_set = $request->jugador_local_set;
                            $Partido->jugador_local_juego = $request->jugador_local_juego;
                            $Partido->jugador_rival_set = $request->jugador_rival_set;
                            $Partido->jugador_rival_juego = $request->jugador_rival_juego;
                            $Partido->fecha_inicio = $request->fecha_inicio;
                            $Partido->fecha_final = $request->fecha_final;
                            $Partido->resultado = $request->resultado;
                            $Partido->estado_id = $request->estado_id;
                            $Partido->user_update_id  = Auth::guard('web')->user()->id;

                            if ($Partido->save())
                            {
                                if($Partido->estado_id == App::$ESTADO_FINALIZADO && $Partido->fase > 1)
                                {
                                    $PartidoNextWiouthBuy = null;

                                    $SiguienteFase = ($Partido->fase/2);

                                    $PartidoNext = Partido::where('torneo_id', $Partido->torneo_id)
                                    ->where('torneo_categoria_id', $Partido->torneo_categoria_id)->where('fase', $SiguienteFase)
                                    ->whereIn('bloque', $SiguienteFase == 1 ? [1] : ($SiguienteFase == 2 ? (in_array($Partido->bloque, [1, 3]) ? [1] : [2]) : [$Partido->bloque]))
                                    ->where(function ($q) use($Partido){
                                        if($Partido->fase == 16){
                                            $q->where('position', $Partido->bracket == "upper" ? 1 : 2);
                                        }
                                    })
                                    ->where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->first();

                                    if($PartidoNext == null)
                                    {
                                        $PartidoNext = new Partido();
                                        $PartidoNext->torneo_id = $Partido->torneo_id;
                                        $PartidoNext->torneo_categoria_id = $Partido->torneo_categoria_id;
                                        $PartidoNext->estado_id = App::$ESTADO_PENDIENTE;
                                        $PartidoNext->multiple = $Partido->multiple;
                                        $PartidoNext->fecha_inicio = Carbon::parse($Partido->fecha_final)->addDay(1);
                                        $PartidoNext->fecha_final = Carbon::parse($Partido->fecha_final)->addDay(6);
                                        $PartidoNext->user_create_id = Auth::guard('web')->user()->id;
                                        $PartidoNext->fase = $SiguienteFase;
                                        if($Partido->fase == 16){
                                            $PartidoNext->position = $Partido->bracket == "upper" ? 1 : 2;
                                        }else{
                                            $PartidoNext->position = $Partido->position;
                                        }
                                        //$PartidoNext->bracket = $Partido->bracket;
                                        $PartidoNext->bloque = $SiguienteFase == 1 ? 1 : (in_array($Partido->fase, [16, 8]) ? $Partido->bloque : (in_array($Partido->bloque, [1, 3]) ? 1 : 2));
                                    }else{
                                        $PartidoNext->user_update_id = Auth::guard('web')->user()->id;
                                    }

                                    $PartidoNext->comunidad_id = Auth::guard('web')->user()->comunidad_id;
                                    if($request->position == 1)
                                    {
                                        $PartidoNext->jugador_local_uno_id = $Partido->jugador_ganador_uno_id;
                                        $PartidoNext->jugador_local_dos_id = $Partido->jugador_ganador_dos_id;
                                    }else{
                                        $PartidoNext->jugador_rival_uno_id = $Partido->jugador_ganador_uno_id;
                                        $PartidoNext->jugador_rival_dos_id = $Partido->jugador_ganador_dos_id;
                                    }

                                    if($PartidoNext->buy) $PartidoNextWiouthBuy = $PartidoNext;

                                    if($PartidoNext->save())
                                    {
                                        if($PartidoNextWiouthBuy != null)
                                        {
                                            $SiguienteFase = ($PartidoNext->fase/2);

                                            $PartidoNext2Existente = Partido::where('torneo_id', $PartidoNextWiouthBuy->torneo_id)
                                                ->where('torneo_categoria_id', $PartidoNextWiouthBuy->torneo_categoria_id)->where('fase', $SiguienteFase)
                                                ->whereIn('bloque', $SiguienteFase == 1 ? [1] : ($SiguienteFase == 2 ? (in_array($PartidoNextWiouthBuy->bloque, [1, 3]) ? [1] : [2]) : [$PartidoNextWiouthBuy->bloque]))
                                                ->where(function ($q) use($PartidoNextWiouthBuy){
                                                    if($PartidoNextWiouthBuy->fase == 16){
                                                        $q->where('position', $PartidoNextWiouthBuy->bracket == "upper" ? 1 : 2);
                                                    }
                                                })
                                                ->where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->first();

                                            if($PartidoNext2Existente == null){
                                                $PartidoNext2 = new Partido();
                                            }else{
                                                $PartidoNext2 = $PartidoNext2Existente;
                                                if($PartidoNext2->buy){
                                                    $PartidoNext->estado_id = App::$ESTADO_FINALIZADO;
                                                }
                                            }

                                            $PartidoNext2->torneo_id = $PartidoNextWiouthBuy->torneo_id;
                                            $PartidoNext2->torneo_categoria_id = $PartidoNextWiouthBuy->torneo_categoria_id;
                                            $PartidoNext2->estado_id = App::$ESTADO_PENDIENTE;
                                            $PartidoNext2->multiple = $PartidoNextWiouthBuy->multiple;
                                            $PartidoNext2->fecha_inicio = Carbon::parse($PartidoNextWiouthBuy->fecha_final)->addDay(1);
                                            $PartidoNext2->fecha_final = Carbon::parse($PartidoNextWiouthBuy->fecha_final)->addDay(6);
                                            $PartidoNext2->user_create_id = Auth::guard('web')->user()->id;
                                            $PartidoNext2->fase = $SiguienteFase;
                                            $PartidoNext2->position = $PartidoNextWiouthBuy->position;
                                            $PartidoNext2->bloque = $SiguienteFase == 1 ? 1 : (in_array($PartidoNextWiouthBuy->fase, [16, 8]) ? $PartidoNextWiouthBuy->bloque : (in_array($PartidoNextWiouthBuy->bloque, [1, 3]) ? 1 : 2));
                                            $PartidoNext2->comunidad_id = Auth::guard('web')->user()->comunidad_id;

                                            if($PartidoNext2->jugador_local_uno_id != null)
                                            {
                                                $PartidoNext2->jugador_rival_uno_id = $PartidoNextWiouthBuy->jugador_local_uno_id;
                                                $PartidoNext2->jugador_rival_dos_id = $PartidoNextWiouthBuy->jugador_local_dos_id;
                                            }else{
                                                $PartidoNext2->jugador_local_uno_id = $PartidoNextWiouthBuy->jugador_local_uno_id;
                                                $PartidoNext2->jugador_local_dos_id = $PartidoNextWiouthBuy->jugador_local_dos_id;
                                            }


                                            /*if($PartidoNext2->position == 1){
                                                $PartidoNext2->jugador_local_uno_id = $PartidoNextWiouthBuy->jugador_local_uno_id;
                                                $PartidoNext2->jugador_local_dos_id = $PartidoNextWiouthBuy->jugador_local_dos_id;
                                            }else{
                                                $PartidoNext2->jugador_rival_uno_id = $PartidoNextWiouthBuy->jugador_rival_uno_id;
                                                $PartidoNext2->jugador_rival_dos_id = $PartidoNextWiouthBuy->jugador_rival_dos_id;
                                            }*/

                                            //dd($PartidoNext2);

                                            if($PartidoNext2->save())
                                            {
                                                DB::commit();
                                                $Result->Success = true;
                                            }

                                        }else{
                                            DB::commit();
                                            $Result->Success = true;
                                        }
                                    }


                                }else{
                                    DB::commit();
                                    $Result->Success = true;
                                }
                            } else {
                                $Result->Message = "Algo salió mal, hubo un error al guardar.";
                            }
                        }
                    }

                    DB::commit();

                    $Result->Success = true;
                }

            }

            $Result->Errors = $Validator->errors();

        }catch (\Exception $e){
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }

        return response()->json($Result);
    }

    public function faseFinalReload(Request $request)
    {

        $Result = (object)['Success' => false, 'Message' => null];

        try {

            DB::beginTransaction();

            $TorneoCategoria = TorneoCategoria::where('torneo_id', $request->torneo_id)
                ->where('id', $request->torneo_categoria_id)
                ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})->first();

            if($TorneoCategoria != null)
            {
                Partido::where('torneo_categoria_id', $TorneoCategoria->id)->where('torneo_id', $TorneoCategoria->torneo_id)->whereNotNull('fase')->delete();

                if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('estado_id', App::$ESTADO_PENDIENTE)) > 0)
                {
                    $Result->Message = "No puede generar las llaves de la segunda fase, porque aún existen partidos no finalizados.";
                    return response()->json($Result);
                }

                $TorneoGrupos = $TorneoCategoria->torneo->torneoGrupos()->where('torneo_categoria_id', $TorneoCategoria->id)->select('grupo_id')->groupBy('grupo_id')->get();

                $JugadoresClasificados = [];

                $Clasifican = $TorneoCategoria->clasificados_terceros > 0 ? 3 : $TorneoCategoria->clasificados;

                foreach ($TorneoGrupos as $key => $q)
                {
                    //TODOS LOS JUGADORES DEL GRUPO
                    $Jugadores = $TorneoCategoria->torneo->torneoGrupos()->where('torneo_categoria_id', $TorneoCategoria->id)
                        ->where('grupo_id', $q->grupo_id)->get()->map(function ($q) use ($TorneoCategoria){
                            return [
                                'jugador_simple_id' => $q->jugadorSimple->id,
                                'jugador_dupla_id' => $TorneoCategoria->multiple ? $q->jugadorDupla->id : null,
                                'nombres' => $TorneoCategoria->multiple ? ($q->jugadorSimple->nombre_completo." + ".$q->jugadorDupla->nombre_completo) : $q->jugadorSimple->nombre_completo
                            ];
                        });

                    //SOLO LOS JUGADORES CLASISIFICADOS POR GRUPO
                    $TablePositions = [];
                    foreach ($Jugadores as $key2 => $q2)
                    {
                        if($TorneoCategoria->multiple)
                        {
                            $PartidosComoLocal = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                                ->where('grupo_id', $q->grupo_id)->where('jugador_local_uno_id', $q2['jugador_simple_id'])
                                ->where('jugador_local_dos_id', $q2['jugador_dupla_id'])->whereNull('fase')->get());

                            $PartidosComoRival = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                                ->where('grupo_id', $q->grupo_id)->where('jugador_rival_uno_id', $q2['jugador_simple_id'])
                                ->where('jugador_rival_dos_id', $q2['jugador_dupla_id'])->whereNull('fase')->get());

                            $Puntos = 0;

                            foreach ($PartidosComoLocal as $p){
                                if($p->jugador_ganador_uno_id == $q2['jugador_simple_id']){
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                                }else{
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                                }
                            }

                            foreach ($PartidosComoRival as $p){
                                if($p->jugador_ganador_uno_id == $q2['jugador_simple_id']){
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                                }else{
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                                }
                            }

                            $Puntos = $Puntos * $TorneoCategoria->torneo->valor_set;

                            $TablePositions[] = [
                                'key' => ($key.'-'.$key2),
                                'grupo_id' => $q->grupo->id,
                                'grupo' => $q->grupo->nombre,
                                'jugador_simple_id' => $q2['jugador_simple_id'],
                                'jugador_dupla_id' => $q2['jugador_dupla_id'],
                                'nombres' => $q2['nombres'],
                                'puntos' => $Puntos
                            ];

                        }else{
                            $PartidosComoLocal = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                                ->where('grupo_id', $q->grupo_id)->where('jugador_local_uno_id', $q2['jugador_simple_id'])->whereNull('fase')->get());

                            $PartidosComoRival = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                                ->where('grupo_id', $q->grupo_id)->where('jugador_rival_uno_id', $q2['jugador_simple_id'])->whereNull('fase')->get());

                            $Puntos = 0;

                            foreach ($PartidosComoLocal as $p){
                                if($p->jugador_ganador_uno_id == $q2['jugador_simple_id']){
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                                }else{
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                                }
                            }

                            foreach ($PartidosComoRival as $p){
                                if($p->jugador_ganador_uno_id == $q2['jugador_simple_id']){
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                                }else{
                                    $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                                }
                            }

                            $Puntos = $Puntos * $TorneoCategoria->torneo->valor_set;

                            $TablePositions[] = [
                                'key' => ($key.'-'.$key2),
                                'grupo_id' => $q->grupo->id,
                                'grupo' => $q->grupo->nombre,
                                'jugador_simple_id' => $q2['jugador_simple_id'],
                                'jugador_dupla_id' => null,
                                'nombres' => $q2['nombres'],
                                'puntos' => $Puntos
                            ];
                        }
                    }
                    $JugadoresClasificados[] = ['Grupo' => $q->grupo->nombre, 'Clasificados' => App::multiPropertySort(collect($TablePositions), [ ['column' => 'puntos', 'order' => 'desc'],['column' => 'setsDiferencias', 'order' => 'desc'], ['column' => 'gamesDiferencias', 'order' => 'desc'], ['column' => 'setsGanados', 'order' => 'desc'], ['column' => 'gamesGanados', 'order' => 'desc']])->take($Clasifican)];
                }

                //SOLO JUGADORES CLASIFICADOS POR CANTIDAD DE CLASIFICADOS PERMIDOS
                $PrimerosLugares = [];  $SegundoLugares = [];  $TercerosLugares = [];
                foreach ($JugadoresClasificados as $key => $value)
                {
                    if($Clasifican == 1) $PrimerosLugares[] = $value['Clasificados']->first();
                    else if($Clasifican == 2) {
                        $PrimerosLugares[] = $value['Clasificados']->first();
                        $SegundoLugares[] = $value['Clasificados']->last();
                    }else{
                        $PrimerosLugares[] = $value['Clasificados']->first();
                        $TercerosLugares[] = $value['Clasificados']->last();
                    }
                }
                if($Clasifican == 3)
                {
                    $Clasificados = array_merge(collect($PrimerosLugares)->pluck('key')->toArray(), collect($TercerosLugares)->pluck('key')->toArray());
                    foreach (collect($JugadoresClasificados)->pluck('Clasificados') as $key => $value){
                        foreach ($value as $ke2 => $value2){
                            if(!in_array($value2['key'], $Clasificados)) $SegundoLugares[] = $value2;
                        }
                    }
                    $TercerosLugares = App::multiPropertySort(collect($TercerosLugares), [ ['column' => 'puntos', 'order' => 'desc'],['column' => 'setsDiferencias', 'order' => 'desc'], ['column' => 'gamesDiferencias', 'order' => 'desc'], ['column' => 'setsGanados', 'order' => 'desc'], ['column' => 'gamesGanados', 'order' => 'desc']])->take($TorneoCategoria->clasificados_terceros)->toArray();
                }
                $JugadoresClasificadosMerge = array_filter(array_merge($PrimerosLugares, $SegundoLugares, $TercerosLugares));

                $PartidosFaseFinal = [];

                //CREACIÓN DE JUGADORES BUYS POR FALTA DE JUGADORES CLASIFICADOS DE 16 O 32 JUGADORES
                $JugadoresBuys = [];  $JugadoresKeysDefinidos = [];
                if(!in_array(count($JugadoresClasificadosMerge), [4, 8, 16, 32]))
                {
                    $BuyGenerar = 0;
                    if(count($JugadoresClasificadosMerge) < 16) $BuyGenerar = (16 - count($JugadoresClasificadosMerge));
                    else if(count($JugadoresClasificadosMerge) < 32) $BuyGenerar = (32 - count($JugadoresClasificadosMerge));

                    $BuysGenerados = [];
                    for ($i=0; $i < $BuyGenerar; $i++)
                    {
                        $BuysGenerados[] = [
                            'key' => ('B-'.$i),
                            'grupo_id' => null,
                            'grupo' => null,
                            'jugador_simple_id' => null,
                            'jugador_dupla_id' => null,
                            'nombres' => 'BYE',
                            'puntos' => 0
                        ];
                    }
                    $JugadoresBuys = array_values(collect($JugadoresClasificadosMerge)->sortByDesc('puntos')->take(count($BuysGenerados))->toArray());
                    for ($i=0; $i < count($JugadoresBuys); $i++)
                    {
                        $PartidosFaseFinal[] = [
                            'buy' => true, 'key' => $JugadoresBuys[$i]['key'],
                            'jugador_local' => ['jugador_simple_id' => $JugadoresBuys[$i]['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $JugadoresBuys[$i]['jugador_dupla_id'] : null, 'grupo_id' =>  $JugadoresBuys[$i]['grupo_id'], 'grupo' => $JugadoresBuys[$i]['grupo'], 'nombres' => $JugadoresBuys[$i]['nombres']],
                            'jugador_rival' => ['jugador_simple_id' => $BuysGenerados[$i]['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $BuysGenerados[$i]['jugador_dupla_id'] : null, 'grupo_id' =>  $BuysGenerados[$i]['grupo_id'], 'grupo' => $BuysGenerados[$i]['grupo'], 'nombres' => $BuysGenerados[$i]['nombres']]
                        ];
                        $JugadoresKeysDefinidos[] = $JugadoresBuys[$i]['key'];
                    }
                }

                //JUGADORES QUE AÚN NO TIENEN PARTIDOS PROGRAMADOS EN LA FASE FINAL
                $x = 0;
                while ($x <= 10)
                {
                    $JugadoresNoDefinidos = $request->tipo == "random" ? collect($JugadoresClasificadosMerge)->whereNotIn('key', $JugadoresKeysDefinidos)->shuffle() : collect($JugadoresClasificadosMerge)->whereNotIn('key', $JugadoresKeysDefinidos)->sortByDesc('puntos');
                    if(count($JugadoresNoDefinidos) > 0)
                    {
                        foreach ($JugadoresNoDefinidos as $q)
                        {
                            if(!in_array($q['key'], $JugadoresKeysDefinidos))
                            {
                                $JugadorRivales = collect($JugadoresClasificadosMerge)->where('key', '!=', $q['key'])->where('grupo_id','!=',$q['grupo_id'])->whereNotIn('key', $JugadoresKeysDefinidos)->sortByDesc('puntos');

                                $JugadorRival = $request->tipo == "random" && count($JugadorRivales) > 0 ? $JugadorRivales->random() : $JugadorRivales->last();

                                if($JugadorRival != null)
                                {
                                    $JugadoresKeysDefinidos[] = $q['key']; $JugadoresKeysDefinidos[] = $JugadorRival['key'];
                                    $PartidosFaseFinal[] = [
                                        'buy' => false,
                                        'key' => $q['key'].'-vs-'.$JugadorRival['key'],
                                        'jugador_local' => ['jugador_simple_id' => $q['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_dupla_id'] : null, 'grupo_id' =>  $q['grupo_id'], 'grupo' => $q['grupo'], 'nombres' => $q['nombres']],
                                        'jugador_rival' => ['jugador_simple_id' => $JugadorRival['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $JugadorRival['jugador_dupla_id'] : null, 'grupo_id' =>  $JugadorRival['grupo_id'], 'grupo' => $JugadorRival['grupo'], 'nombres' => $JugadorRival['nombres']]
                                    ];
                                }
                            }
                        }
                    }
                    $x++;
                }

                //POSIBLES JUGADORES QUE NO TIENEN PARTIDOS JUGADORES PORQUE SON DEL MISMO GRUPO
                $JugadoresNoDefinidos = collect($JugadoresClasificadosMerge)->whereNotIn('key', $JugadoresKeysDefinidos)->sortByDesc('puntos');
                if(count($JugadoresNoDefinidos) > 0)
                {
                    foreach ($JugadoresNoDefinidos as $q)
                    {
                        if(!in_array($q['key'], $JugadoresKeysDefinidos))
                        {
                            $JugadorRival = collect($JugadoresClasificadosMerge)->where('key', '!=', $q['key'])->whereNotIn('key', $JugadoresKeysDefinidos)->first();

                            if($JugadorRival != null)
                            {
                                $JugadoresKeysDefinidos[] = $q['key']; $JugadoresKeysDefinidos[] = $JugadorRival['key'];
                                $PartidosFaseFinal[] = [
                                    'buy' => false,
                                    'key' => $q['key'].'-vs-'.$JugadorRival['key'],
                                    'jugador_local' => ['jugador_simple_id' => $q['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_dupla_id'] : null, 'grupo_id' =>  $q['grupo_id'], 'grupo' => $q['grupo'], 'nombres' => $q['nombres']],
                                    'jugador_rival' => ['jugador_simple_id' => $JugadorRival['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $JugadorRival['jugador_dupla_id'] : null, 'grupo_id' =>  $JugadorRival['grupo_id'], 'grupo' => $JugadorRival['grupo'], 'nombres' => $JugadorRival['nombres']]
                                ];
                            }
                        }
                    }
                }

                if($request->tipo == "random") $PartidosFaseFinal = collect($PartidosFaseFinal)->shuffle();

                //DEFINIENDO LOS BLOQUES EN EL CUADRO DE FASE FINAL
                $CantidadPorBloques = (count($PartidosFaseFinal)/4);

                $PrimerBloque = []; $SegundoBloque = []; $TercerBloque = []; $CuartoBloque = []; $PartidosKeysDefinidos = [];

                $i = 100;
                while($i <= 100)
                {
                    $PartidosKeysDefinidos = []; $y = 0;
                    while ($y <= 10){
                        $PartidosPorDefinir = collect($PartidosFaseFinal)->whereNotIn('key', $PartidosKeysDefinidos);
                        if(count($PartidosPorDefinir) > 0)
                        {
                            foreach ($PartidosPorDefinir as $q)
                            {
                                if(!in_array($q['key'], $PartidosKeysDefinidos))
                                {
                                    $Grupo = [$q['jugador_local']['grupo_id'], $q['jugador_rival']['grupo_id']];
                                    if(count($PrimerBloque) < $CantidadPorBloques && collect($PrimerBloque)->whereIn('jugador_local.grupo_id', $Grupo)->whereIn('jugador_rival.grupo_id', $Grupo)->first() == null)
                                    {
                                        $PrimerBloque[] = [
                                            'buy' => $q['buy'], 'key' => $q['key'],
                                            'jugador_local' => ['jugador_simple_id' => $q['jugador_local']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_local']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_local']['grupo_id'], 'grupo' => $q['jugador_local']['grupo'], 'nombres' => $q['jugador_local']['nombres']],
                                            'jugador_rival' => ['jugador_simple_id' => $q['jugador_rival']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_rival']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_rival']['grupo_id'], 'grupo' => $q['jugador_rival']['grupo'], 'nombres' => $q['jugador_rival']['nombres']]
                                        ];
                                        $PartidosKeysDefinidos[] = $q['key'];

                                    }else if(count($SegundoBloque) < $CantidadPorBloques && collect($SegundoBloque)->whereIn('jugador_local.grupo_id', $Grupo)->whereIn('jugador_rival.grupo_id', $Grupo)->first() == null)
                                    {
                                        $SegundoBloque[] = [
                                            'buy' => $q['buy'], 'key' => $q['key'],
                                            'jugador_local' => ['jugador_simple_id' => $q['jugador_local']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_local']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_local']['grupo_id'], 'grupo' => $q['jugador_local']['grupo'], 'nombres' => $q['jugador_local']['nombres']],
                                            'jugador_rival' => ['jugador_simple_id' => $q['jugador_rival']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_rival']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_rival']['grupo_id'], 'grupo' => $q['jugador_rival']['grupo'], 'nombres' => $q['jugador_rival']['nombres']]
                                        ];
                                        $PartidosKeysDefinidos[] = $q['key'];

                                    }else if(count($TercerBloque) < $CantidadPorBloques && collect($TercerBloque)->whereIn('jugador_local.grupo_id', $Grupo)->whereIn('jugador_rival.grupo_id', $Grupo)->first() == null)
                                    {
                                        $TercerBloque[] = [
                                            'buy' => $q['buy'], 'key' => $q['key'],
                                            'jugador_local' => ['jugador_simple_id' => $q['jugador_local']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_local']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_local']['grupo_id'], 'grupo' => $q['jugador_local']['grupo'], 'nombres' => $q['jugador_local']['nombres']],
                                            'jugador_rival' => ['jugador_simple_id' => $q['jugador_rival']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_rival']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_rival']['grupo_id'], 'grupo' => $q['jugador_rival']['grupo'], 'nombres' => $q['jugador_rival']['nombres']]
                                        ];
                                        $PartidosKeysDefinidos[] = $q['key'];

                                    }else if(count($CuartoBloque) < $CantidadPorBloques && collect($CuartoBloque)->whereIn('jugador_local.grupo_id', $Grupo)->whereIn('jugador_rival.grupo_id', $Grupo)->first() == null)
                                    {
                                        $CuartoBloque[] = [
                                            'buy' => $q['buy'], 'key' => $q['key'],
                                            'jugador_local' => ['jugador_simple_id' => $q['jugador_local']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_local']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_local']['grupo_id'], 'grupo' => $q['jugador_local']['grupo'], 'nombres' => $q['jugador_local']['nombres']],
                                            'jugador_rival' => ['jugador_simple_id' => $q['jugador_rival']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_rival']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_rival']['grupo_id'], 'grupo' => $q['jugador_rival']['grupo'], 'nombres' => $q['jugador_rival']['nombres']]
                                        ];
                                        $PartidosKeysDefinidos[] = $q['key'];
                                    }
                                }
                            }
                        }
                        $y++;
                    }

                    if(count(collect($PartidosFaseFinal)->whereNotIn('key', $PartidosKeysDefinidos)) == 0){ break; }
                    else{ $i++; }
                }

                $PartidosNoDefinidos = collect($PartidosFaseFinal)->whereNotIn('key', $PartidosKeysDefinidos);
                foreach ($PartidosNoDefinidos as $q)
                {
                    if(!in_array($q['key'], $PartidosKeysDefinidos))
                    {
                        $Grupo = [$q['jugador_local']['grupo_id'], $q['jugador_rival']['grupo_id']];
                        if(count($PrimerBloque) < $CantidadPorBloques)
                        {
                            $PrimerBloque[] = [
                                'buy' => $q['buy'], 'key' => $q['key'],
                                'jugador_local' => ['jugador_simple_id' => $q['jugador_local']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_local']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_local']['grupo_id'], 'grupo' => $q['jugador_local']['grupo'], 'nombres' => $q['jugador_local']['nombres']],
                                'jugador_rival' => ['jugador_simple_id' => $q['jugador_rival']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_rival']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_rival']['grupo_id'], 'grupo' => $q['jugador_rival']['grupo'], 'nombres' => $q['jugador_rival']['nombres']]
                            ];
                            $PartidosKeysDefinidos[] = $q['key'];

                        }else if(count($SegundoBloque) < $CantidadPorBloques)
                        {
                            $SegundoBloque[] = [
                                'buy' => $q['buy'], 'key' => $q['key'],
                                'jugador_local' => ['jugador_simple_id' => $q['jugador_local']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_local']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_local']['grupo_id'], 'grupo' => $q['jugador_local']['grupo'], 'nombres' => $q['jugador_local']['nombres']],
                                'jugador_rival' => ['jugador_simple_id' => $q['jugador_rival']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_rival']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_rival']['grupo_id'], 'grupo' => $q['jugador_rival']['grupo'], 'nombres' => $q['jugador_rival']['nombres']]
                            ];
                            $PartidosKeysDefinidos[] = $q['key'];

                        }else if(count($TercerBloque) < $CantidadPorBloques)
                        {
                            $TercerBloque[] = [
                                'buy' => $q['buy'], 'key' => $q['key'],
                                'jugador_local' => ['jugador_simple_id' => $q['jugador_local']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_local']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_local']['grupo_id'], 'grupo' => $q['jugador_local']['grupo'], 'nombres' => $q['jugador_local']['nombres']],
                                'jugador_rival' => ['jugador_simple_id' => $q['jugador_rival']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_rival']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_rival']['grupo_id'], 'grupo' => $q['jugador_rival']['grupo'], 'nombres' => $q['jugador_rival']['nombres']]
                            ];
                            $PartidosKeysDefinidos[] = $q['key'];

                        }else if(count($CuartoBloque) < $CantidadPorBloques)
                        {
                            $CuartoBloque[] = [
                                'buy' => $q['buy'], 'key' => $q['key'],
                                'jugador_local' => ['jugador_simple_id' => $q['jugador_local']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_local']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_local']['grupo_id'], 'grupo' => $q['jugador_local']['grupo'], 'nombres' => $q['jugador_local']['nombres']],
                                'jugador_rival' => ['jugador_simple_id' => $q['jugador_rival']['jugador_simple_id'], 'jugador_dupla_id' => $TorneoCategoria->multiple ? $q['jugador_rival']['jugador_dupla_id'] : null, 'grupo_id' =>  $q['jugador_rival']['grupo_id'], 'grupo' => $q['jugador_rival']['grupo'], 'nombres' => $q['jugador_rival']['nombres']]
                            ];
                            $PartidosKeysDefinidos[] = $q['key'];
                        }
                    }
                }

                //DEFINICIÓN DE LOS 4 BLOQUES
                $BloquesFaseFinal = [$PrimerBloque, $SegundoBloque, $TercerBloque, $CuartoBloque];  $Partidos = [];

                //OBTENIENDO LA FECHA DEL ULTIMO PARTIDO
                $Partido = Partido::where('torneo_categoria_id', $TorneoCategoria->id)->whereNull('fase')
                ->where('estado_id', App::$ESTADO_FINALIZADO)->orderBy('id', 'desc')->first();

                $fechaInicioPartido = Carbon::parse($Partido->fecha_final)->addDay(1);
                $fechaFinalPartido = Carbon::parse($fechaInicioPartido)->addDay(6);

                foreach ($BloquesFaseFinal as $key => $bloque)
                {
                    $Position = 1;
                    foreach ($bloque as $q) {
                        $Partidos[] = [
                            'comunidad_id' => Auth::guard('web')->user()->comunidad_id,
                            'torneo_id' => $TorneoCategoria->torneo->id,
                            'torneo_categoria_id' => $TorneoCategoria->id,
                            'grupo_id' => null,
                            'multiple' => $TorneoCategoria->multiple,
                            'jugador_local_uno_id' => $q['jugador_local']['jugador_simple_id'],
                            'jugador_local_dos_id' => $TorneoCategoria->multiple ? $q['jugador_local']['jugador_dupla_id'] : null,
                            'jugador_rival_uno_id' => $q['jugador_rival']['jugador_simple_id'],
                            'jugador_rival_dos_id' => $TorneoCategoria->multiple ? $q['jugador_rival']['jugador_dupla_id'] : null,
                            'fecha_inicio' => $fechaInicioPartido, 'fecha_final' => $fechaFinalPartido, 'hora_inicio' => null,
                            'hora_final' => null, 'resultado' => null,
                            'estado_id' => filter_var($q['buy'], FILTER_VALIDATE_BOOLEAN) ? App::$ESTADO_FINALIZADO: App::$ESTADO_PENDIENTE,
                            'buy' => $q['buy'],
                            'bloque' => ($key + 1),
                            'position' => $Position,
                            'fase' => count($PartidosFaseFinal),
                            'user_create_id' => Auth::guard('web')->user()->id,
                        ];
                        $Position++;
                    }
                }

                $PartidosBuysPrimeraEtapa = collect($Partidos)->where('buy', true);
                if(count($PartidosBuysPrimeraEtapa) > 0)
                {
                    $PartidosPrimeraEtapaPrimerBloque = array_values($PartidosBuysPrimeraEtapa->where('bloque', 1)->toArray());
                    $PartidosPrimeraEtapaSegundBloque = array_values($PartidosBuysPrimeraEtapa->where('bloque', 2)->toArray());
                    $PartidosPrimeraEtapaTercerBloque = array_values($PartidosBuysPrimeraEtapa->where('bloque', 3)->toArray());
                    $PartidosPrimeraEtapaCuartoBloque = array_values($PartidosBuysPrimeraEtapa->where('bloque', 4)->toArray());

                    $PartidosPrimeraEtapaFaseFinal = [$PartidosPrimeraEtapaPrimerBloque, $PartidosPrimeraEtapaSegundBloque, $PartidosPrimeraEtapaTercerBloque, $PartidosPrimeraEtapaCuartoBloque];

                    foreach ($PartidosPrimeraEtapaFaseFinal as $key => $q)
                    {
                        if(count($q) > 0) {
                            switch (count($q))
                            {
                                case 1:
                                    $Partidos[] = [
                                        'comunidad_id' => Auth::guard('web')->user()->comunidad_id,
                                        'torneo_id' => $TorneoCategoria->torneo->id, 'torneo_categoria_id' => $TorneoCategoria->id,
                                        'grupo_id' => null, 'multiple' => $TorneoCategoria->multiple,
                                        'jugador_local_uno_id' => $q[0]['position'] == 1 ? $q[0]['jugador_local_uno_id'] : null,
                                        'jugador_local_dos_id' => $q[0]['position'] == 1  && $TorneoCategoria->multiple ? $q[0]['jugador_local_dos_id'] : null,
                                        'jugador_rival_uno_id' => $q[0]['position'] == 2 ? $q[0]['jugador_local_uno_id'] : null,
                                        'jugador_rival_dos_id' => $q[0]['position'] == 2  && $TorneoCategoria->multiple ? $q[0]['jugador_local_dos_id'] : null,
                                        'fecha_inicio' => $fechaInicioPartido, 'fecha_final' => $fechaFinalPartido, 'hora_inicio' => null,
                                        'hora_final' => null, 'resultado' => null, 'estado_id' => App::$ESTADO_PENDIENTE, 'buy' => false,
                                        'bloque' => ($key+1), 'position' => $q[0]['position'], 'fase' => count($PartidosFaseFinal)/2,
                                        'user_create_id' => Auth::guard('web')->user()->id,
                                    ];
                                    break;
                                case 2:
                                    $Partidos[] = [
                                        'comunidad_id' => Auth::guard('web')->user()->comunidad_id,
                                        'torneo_id' => $TorneoCategoria->torneo->id, 'torneo_categoria_id' => $TorneoCategoria->id,
                                        'grupo_id' => null, 'multiple' => $TorneoCategoria->multiple,
                                        'jugador_local_uno_id' => $q[0]['jugador_local_uno_id'],
                                        'jugador_local_dos_id' => $TorneoCategoria->multiple ? $q[0]['jugador_local_dos_id'] : null,
                                        'jugador_rival_uno_id' => $q[1]['jugador_local_uno_id'],
                                        'jugador_rival_dos_id' => $TorneoCategoria->multiple ? $q[1]['jugador_local_dos_id'] : null,
                                        'fecha_inicio' => $fechaInicioPartido, 'fecha_final' => $fechaFinalPartido, 'hora_inicio' => null,
                                        'hora_final' => null, 'resultado' => null, 'estado_id' => App::$ESTADO_PENDIENTE, 'buy' => false,
                                        'bloque' => ($key+1), 'position' => $q[0]['position'], 'fase' => count($PartidosFaseFinal)/2,
                                        'user_create_id' => Auth::guard('web')->user()->id,
                                    ];
                                    break;
                                case 3:
                                    $Partidos[] = [
                                        'comunidad_id' => Auth::guard('web')->user()->comunidad_id,
                                        'torneo_id' => $TorneoCategoria->torneo->id, 'torneo_categoria_id' => $TorneoCategoria->id,
                                        'grupo_id' => null, 'multiple' => $TorneoCategoria->multiple,
                                        'jugador_local_uno_id' => $q[0]['jugador_local_uno_id'],
                                        'jugador_local_dos_id' => $TorneoCategoria->multiple ? $q[0]['jugador_local_dos_id'] : null,
                                        'jugador_rival_uno_id' => $q[1]['jugador_local_uno_id'],
                                        'jugador_rival_dos_id' => $TorneoCategoria->multiple ? $q[1]['jugador_local_dos_id'] : null,
                                        'fecha_inicio' => $fechaInicioPartido, 'fecha_final' => $fechaFinalPartido, 'hora_inicio' => null,
                                        'hora_final' => null, 'resultado' => null, 'estado_id' => App::$ESTADO_PENDIENTE, 'buy' => false,
                                        'bloque' => ($key+1), 'position' => $q[0]['position'], 'fase' => count($PartidosFaseFinal)/2,
                                        'user_create_id' => Auth::guard('web')->user()->id,
                                    ];

                                    $Partidos[] = [
                                        'comunidad_id' => Auth::guard('web')->user()->comunidad_id,
                                        'torneo_id' => $TorneoCategoria->torneo->id, 'torneo_categoria_id' => $TorneoCategoria->id,
                                        'grupo_id' => null, 'multiple' => $TorneoCategoria->multiple,
                                        'jugador_local_uno_id' => $q[2]['position'] == 1 ? $q[2]['jugador_local_uno_id'] : null,
                                        'jugador_local_dos_id' => $q[2]['position'] == 1 && $TorneoCategoria->multiple ? $q[2]['jugador_local_dos_id'] : null,
                                        'jugador_rival_uno_id' => $q[2]['position'] == 2 ? $q[2]['jugador_local_uno_id'] : null,
                                        'jugador_rival_dos_id' => $q[2]['position'] == 2 && $TorneoCategoria->multiple ? $q[2]['jugador_local_dos_id'] : null,
                                        'fecha_inicio' => $fechaInicioPartido, 'fecha_final' => $fechaFinalPartido, 'hora_inicio' => null,
                                        'hora_final' => null, 'resultado' => null, 'estado_id' => App::$ESTADO_PENDIENTE, 'buy' => false,
                                        'bloque' => ($key+1), 'position' => $q[2]['position'], 'fase' => count($PartidosFaseFinal)/2,
                                        'user_create_id' => Auth::guard('web')->user()->id,
                                    ];
                                    break;
                                case 4:
                                    $Partidos[] = [
                                        'comunidad_id' => Auth::guard('web')->user()->comunidad_id,
                                        'torneo_id' => $TorneoCategoria->torneo->id, 'torneo_categoria_id' => $TorneoCategoria->id,
                                        'grupo_id' => null, 'multiple' => $TorneoCategoria->multiple,
                                        'jugador_local_uno_id' => $q[0]['jugador_local_uno_id'],
                                        'jugador_local_dos_id' => $TorneoCategoria->multiple ? $q[0]['jugador_local_dos_id'] : null,
                                        'jugador_rival_uno_id' => $q[1]['jugador_local_uno_id'],
                                        'jugador_rival_dos_id' => $TorneoCategoria->multiple ? $q[1]['jugador_local_dos_id'] : null,
                                        'fecha_inicio' => $fechaInicioPartido, 'fecha_final' => $fechaFinalPartido, 'hora_inicio' => null,
                                        'hora_final' => null, 'resultado' => null, 'estado_id' => App::$ESTADO_PENDIENTE, 'buy' => false,
                                        'bloque' => ($key+1), 'position' => $q[0]['position'], 'fase' => count($PartidosFaseFinal)/2,
                                        'user_create_id' => Auth::guard('web')->user()->id,
                                    ];

                                    $Partidos[] = [
                                        'comunidad_id' => Auth::guard('web')->user()->comunidad_id,
                                        'torneo_id' => $TorneoCategoria->torneo->id, 'torneo_categoria_id' => $TorneoCategoria->id,
                                        'grupo_id' => null, 'multiple' => $TorneoCategoria->multiple,
                                        'jugador_local_uno_id' => $q[2]['jugador_local_uno_id'],
                                        'jugador_local_dos_id' => $TorneoCategoria->multiple ? $q[2]['jugador_local_dos_id'] : null,
                                        'jugador_rival_uno_id' => $q[3]['jugador_local_uno_id'],
                                        'jugador_rival_dos_id' => $TorneoCategoria->multiple ? $q[3]['jugador_local_dos_id'] : null,
                                        'fecha_inicio' => $fechaInicioPartido, 'fecha_final' => $fechaFinalPartido, 'hora_inicio' => null,
                                        'hora_final' => null, 'resultado' => null, 'estado_id' => App::$ESTADO_PENDIENTE, 'buy' => false,
                                        'bloque' => ($key+1), 'position' => $q[2]['position'], 'fase' => count($PartidosFaseFinal)/2,
                                        'user_create_id' => Auth::guard('web')->user()->id,
                                    ];
                                    break;
                            }
                        }
                    }
                }

                //INSERTANDO LOS PARTIDOS FINALES GENERADOS
                Partido::insert($Partidos);

                DB::commit();
                $Result->Success = true;

            }else{
                $Result->Message = "Las llaves generadas que intenta modificar, ya no se encuentra disponible";
            }

        }catch (\Exception $e){
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }

        return response()->json($Result);
    }

    public function faseFinalMapaPartialView($torneo, $torneo_categoria_id, $landing=false)
    {
        $Comunidad = $landing ? Comunidad::where('principal', true)->first() : Auth::guard('web')->user()->comunidad;

        $ComunidadId = $Comunidad->id;

        $TorneoCategoria = TorneoCategoria::where('id', $torneo_categoria_id)->where('torneo_id', $torneo)
            ->whereHas('torneo', function ($q) use ($ComunidadId){
                $q->where('comunidad_id', $ComunidadId);
            })->first();

        if($TorneoCategoria != null)
        {
            $TorneoFaseFinal = (object)['TorneoCategoria' => $TorneoCategoria];
            return view('auth'.'.'.$this->viewName.'.ajax.final.mapa.partialView', ['TorneoFaseFinal' => $TorneoFaseFinal, 'ViewName' => ucfirst($this->viewName), 'comunidad' => $Comunidad, 'landing' => filter_var($landing, FILTER_VALIDATE_BOOLEAN)]);
        }

        return null;
    }

    public function faseFinalDelete(Request $request)
    {

        $Result = (object)['Success' => false, 'Message' => null];

        try {

            DB::beginTransaction();

            $TorneoCategoria = TorneoCategoria::where('torneo_id', $request->torneo_id)
                ->where('id', $request->torneo_categoria_id)
                ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})->first();

            if($TorneoCategoria != null)
            {
                $TorneoCategoria->manual = false;
                if($TorneoCategoria->save())
                {
                    Partido::where('torneo_categoria_id', $TorneoCategoria->id)->where('torneo_id', $TorneoCategoria->torneo_id)->whereNotNull('fase')->delete();
                    DB::commit();
                    $Result->Success = true;
                }
            }else{
                $Result->Message = "Las llaves generadas que intenta eliminar, ya no se encuentra disponible";
            }

        }catch (\Exception $e){
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }

        return response()->json($Result);
    }

    public function obtenerLlave($LlavesRestantes, $GrupoLocal, $GrupoRival)
    {
        $Seleccionado = collect($LlavesRestantes)->where('jugador_local.grupo_id', '!=', $GrupoLocal)
            ->where('jugador_dupla_id.grupo_id', '!=', $GrupoRival)->first();

        if($Seleccionado == null)
        {
            $Seleccionado = collect($LlavesRestantes)->where('jugador_local.grupo_id', '!=', $GrupoLocal)
            ->where('jugador_dupla_id.grupo_id', '!=', $GrupoRival);

            if(count($Seleccionado) > 0) $Seleccionado = $Seleccionado->random();
            else$Seleccionado = collect($LlavesRestantes)->first();
        }

        return $Seleccionado;
    }

    /*TORNEO GRUPO*/
    public function grupo($id, $torneo_categoria_id = null, $fase = null, $landing = false)
    {
        $entity = null;

        $ComunidadId = $landing ? Comunidad::where('principal', true)->first()->id : Auth::guard('web')->user()->comunidad_id;

        if($id != 0){
            $entity = Torneo::where('comunidad_id', $ComunidadId)->where('id', $id)->first();
            if($torneo_categoria_id == null || $torneo_categoria_id == 0) $torneo_categoria_id = $entity->torneoCategorias()->where('torneo_id', $id)->orderBy('orden')->first()->id;
        }

        return view('auth'.'.'.$this->viewName.'.ajax.grupo.index', ['Model' => $entity, 'TorneoCategoriaId' => $torneo_categoria_id, 'ViewName' => ucfirst($this->viewName), 'Fase' => ($fase == null || $fase == 0 ? null : $fase) ,'landing' => filter_var($landing, FILTER_VALIDATE_BOOLEAN)]);
    }

    public function grupoPartialView($torneo_id, $categoria_id, $tipo = null)
    {
        $Grupos = [];

        $Jugadores = TorneoJugador::whereHas('torneo',
            function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
            ->where('torneo_id', $torneo_id)->where('torneo_categoria_id', $categoria_id)->get();

        $TipoGrupo = $tipo != null ? $tipo : App::$TIPO_GRUPO_LETRA;

        if((count($Jugadores) % 4) == 0)
        {
            $CantidadGrupos = (count($Jugadores) / 4);

            $Grupos = Grupo::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->orderBy('nombre', 'asc')
            ->take($CantidadGrupos)->get()->map(function ($item, $key) use ($TipoGrupo){
                return (object)['id' => $item->id, 'nombre' => $TipoGrupo == App::$TIPO_GRUPO_LETRA ? $item->nombre : ('Grupo '.($key + 1))];
            });
        }

        return view('auth'.'.'.$this->viewName.'.ajax.grupo.partialView',
        ['Torneo' => $torneo_id, 'Categoria' => $categoria_id, 'Grupos' => collect($Grupos), 'TipoGrupo' => $TipoGrupo, 'ViewName' => ucfirst($this->viewName)]);
    }

    public function grupoTablaPartialView($id, $torneo_categoria_id, $grupo_id, $landing=false)
    {
        $ComunidadId = $landing ? Comunidad::where('principal', true)->first()->id : Auth::guard('web')->user()->comunidad_id;

        $TorneoCategoria = TorneoCategoria::where('id', $torneo_categoria_id)->where('torneo_id', $id)
        ->whereHas('torneo', function ($q) use($ComunidadId){$q->where('comunidad_id', $ComunidadId);})->first();

        if($TorneoCategoria != null)
        {
            //JUGADORES DEL GRUPO
            $Jugadores = TorneoGrupo::where('torneo_categoria_id', $torneo_categoria_id)
            ->where('grupo_id', $grupo_id)->get()->map(function ($q) use ($TorneoCategoria){
                return [
                    'jugador_simple_id' => $q->jugadorSimple->id,
                    'jugador_dupla_id' => $TorneoCategoria->multiple ? $q->jugadorDupla->id : null,
                    'nombres' => $TorneoCategoria->multiple ? ($q->jugadorSimple->nombre_completo." + ".$q->jugadorDupla->nombre_completo) : $q->jugadorSimple->nombre_completo
                ];
            });

            //JUGADORES POSICIONES
            $TablePositions = [];

            foreach ($Jugadores as $key2 => $q2)
            {
                if($TorneoCategoria->multiple)
                {
                    $PartidosComoLocal = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                        ->where('grupo_id', $grupo_id)->where('jugador_local_uno_id', $q2['jugador_simple_id'])
                        ->where('jugador_local_dos_id', $q2['jugador_dupla_id'])->whereNull('fase')->get());

                    $PartidosComoRival = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                        ->where('grupo_id', $grupo_id)->where('jugador_rival_uno_id', $q2['jugador_simple_id'])
                        ->where('jugador_rival_dos_id', $q2['jugador_dupla_id'])->whereNull('fase')->get());

                    $PartidosJugados = 0; $SetsGanados = 0; $SetsPerdidos = 0; $GamesGanados = 0; $GamesPerdidos = 0; $Puntos = 0;

                    foreach ($PartidosComoLocal as $p)
                    {
                        if($p->estado_id == App::$ESTADO_FINALIZADO) $PartidosJugados += 1;

                        if($p->jugador_ganador_uno_id == $q2['jugador_simple_id'])
                        {   //NO Rival
                            $SetsGanados += $p->jugador_local_set; $SetsPerdidos += $p->jugador_rival_set;
                            $GamesGanados += $p->jugador_local_juego; $GamesPerdidos += $p->jugador_rival_juego;
                            $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                        }else{
                            //Rival
                            $SetsGanados += $p->jugador_rival_set; $SetsPerdidos += $p->jugador_local_set;
                            $GamesGanados += $p->jugador_rival_juego; $GamesPerdidos += $p->jugador_local_juego;
                            $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                        }
                    }

                    foreach ($PartidosComoRival as $p)
                    {
                        if($p->estado_id == App::$ESTADO_FINALIZADO) $PartidosJugados += 1;

                        if($p->jugador_ganador_uno_id == $q2['jugador_simple_id'])
                        {   //NO Rival
                            $SetsGanados += $p->jugador_local_set; $SetsPerdidos += $p->jugador_rival_set;
                            $GamesGanados += $p->jugador_local_juego; $GamesPerdidos += $p->jugador_rival_juego;
                            $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                        }else{
                            //Rival
                            $SetsGanados += $p->jugador_rival_set; $SetsPerdidos += $p->jugador_local_set;
                            $GamesGanados += $p->jugador_rival_juego; $GamesPerdidos += $p->jugador_local_juego;
                            $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                        }
                    }

                    $SetsDiferencias = $SetsGanados - $SetsPerdidos;

                    $GamesDiferencias = $GamesGanados - $GamesPerdidos;

                    $Puntos = $Puntos * $TorneoCategoria->torneo->valor_set;

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
                        'puntos' => $Puntos
                    ];

                }else{

                    $PartidosComoLocal = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                    ->where('grupo_id', $grupo_id)->where('jugador_local_uno_id', $q2['jugador_simple_id'])->whereNull('fase')->get());

                    $PartidosComoRival = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                    ->where('grupo_id', $grupo_id)->where('jugador_rival_uno_id', $q2['jugador_simple_id'])->whereNull('fase')->get());

                    $PartidosJugados = 0; $SetsGanados = 0; $SetsPerdidos = 0; $GamesGanados = 0; $GamesPerdidos = 0; $Puntos = 0;

                    foreach ($PartidosComoLocal as $p)
                    {
                        if($p->estado_id == App::$ESTADO_FINALIZADO) $PartidosJugados += 1;

                        if($p->jugador_ganador_uno_id == $q2['jugador_simple_id'])
                        {   //NO Rival
                            $SetsGanados += $p->jugador_local_set; $SetsPerdidos += $p->jugador_rival_set;
                            $GamesGanados += $p->jugador_local_juego; $GamesPerdidos += $p->jugador_rival_juego;
                            $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                        }else{
                            //Rival
                            $SetsGanados += $p->jugador_rival_set; $SetsPerdidos += $p->jugador_local_set;
                            $GamesGanados += $p->jugador_rival_juego; $GamesPerdidos += $p->jugador_local_juego;
                            $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                        }
                    }

                    foreach ($PartidosComoRival as $p)
                    {
                        if($p->estado_id == App::$ESTADO_FINALIZADO) $PartidosJugados += 1;

                        if($p->jugador_ganador_uno_id == $q2['jugador_simple_id'])
                        {   //NO Rival
                            $SetsGanados += $p->jugador_local_set; $SetsPerdidos += $p->jugador_rival_set;
                            $GamesGanados += $p->jugador_local_juego; $GamesPerdidos += $p->jugador_rival_juego;
                            $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                        }else{
                            //Rival
                            $SetsGanados += $p->jugador_rival_set; $SetsPerdidos += $p->jugador_local_set;
                            $GamesGanados += $p->jugador_rival_juego; $GamesPerdidos += $p->jugador_local_juego;
                            $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                        }
                    }

                    $SetsDiferencias = $SetsGanados - $SetsPerdidos;

                    $GamesDiferencias = $GamesGanados - $GamesPerdidos;

                    $Puntos = $Puntos * $TorneoCategoria->torneo->valor_set;

                    $TablePositions[] = [
                        'jugador_simple_id' => $q2['jugador_simple_id'],
                        'jugador_dupla_id' => null,
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
            }

            return view('auth'.'.'.$this->viewName.'.ajax.grupo.tabla.partialView', ['TablaPosiciones' => App::multiPropertySort(collect($TablePositions), [
                ['column' => 'puntos', 'order' => 'desc'], ['column' => 'setsDiferencias', 'order' => 'desc'], ['column' => 'gamesDiferencias', 'order' => 'desc'], ['column' => 'setsGanados', 'order' => 'desc'], ['column' => 'gamesGanados', 'order' => 'desc']]), 'ViewName' => ucfirst($this->viewName)]);
        }

        return null;
    }

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

            if (!$Validator->fails())
            {
                $TorneoCategoria = TorneoCategoria::where('id', $request->torneo_categoria_id)
                ->where('torneo_id', $request->torneo_id)->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})->first();

                $Torneo = $TorneoCategoria->torneo;

                if($Torneo != null)
                {
                    if(count($Torneo->partidos->where('torneo_categoria_id', $request->torneo_categoria_id)) <= 0)
                    {
                        $Jugadores = $Torneo->torneoJugadors->where('torneo_categoria_id', $request->torneo_categoria_id);

                        if(count($Jugadores) < App::$PARTICIPANTES_MINIMOS_POR_TORNEO){
                            $Result->Message = "Por favor, registre al menos ".(App::$PARTICIPANTES_MINIMOS_POR_TORNEO - count($Jugadores))." jugadores más para generar las llaves";
                            return response()->json($Result);
                        }

                        if(count($Jugadores) > App::$PARTICIPANTES_MAXIMOS_POR_TORNEO){
                            $Result->Message = "Por favor, solo puede registrar como máximo ".App::$PARTICIPANTES_MAXIMOS_POR_TORNEO." jugadores para generar las llaves";
                            return response()->json($Result);
                        }

                        $CantidadGrupos = (count($Jugadores) / 4);

                        $GruposDisponibles = Grupo::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->orderBy('nombre', 'asc')->get();

                        if(count($GruposDisponibles) < $CantidadGrupos){
                            $Result->Message = "La cantidad de ".$CantidadGrupos." grupos generados, supera a la cantidad de ".count($GruposDisponibles)." grupos disponibles";
                            return response()->json($Result);
                        }

                        if((count($Jugadores) % 4) == 0)
                        {
                            if($request->tipo == "select")
                            {
                                if(count($request->grupo_id == null ? [] : $request->grupo_id) != count($request->jugador_id == null ? [] : $request->jugador_id)){
                                    $Result->Message = "Por favor, complete todos los campos vacíos";
                                    return response()->json($Result);
                                }else{
                                    $Grupos = Grupo::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
                                        ->orderBy('nombre', 'asc')->whereIn('id', $request->grupo_id)->get();

                                    /*$Jugadores = TorneoJugador::whereHas('torneo', function ($q){ $q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
                                    ->where('torneo_id', $request->torneo_id)->where('categoria_id', $request->categoria_id)
                                    ->whereNotIn('jugador_id', $request->jugador_id)->inRandomOrder()->get();*/

                                    $Jugadores = []; $TorneoCategoriaEscogidos = [];

                                    if($TorneoCategoria->multiple)
                                    {
                                        $Jugadores = $Torneo->torneoJugadors()->where('torneo_categoria_id', $request->torneo_categoria_id)
                                        ->whereNotIn('id', $request->jugador_id)
                                        ->inRandomOrder()->get();

                                        $TorneoCategoriaEscogidos = $Torneo->torneoJugadors()->where('torneo_categoria_id', $request->torneo_categoria_id)
                                        ->whereIn('id', $request->jugador_id)->get();

                                    }else{
                                        $Jugadores = $Torneo->torneoJugadors()->where('torneo_categoria_id', $request->torneo_categoria_id)
                                        ->whereNotIn('jugador_simple_id', $request->jugador_id)
                                        ->inRandomOrder()->get();
                                    }

                                    $JugadoresCollect = collect($Jugadores);

                                    //CREACIÓN GRUPOS
                                    foreach ($Grupos as $key => $q)
                                    {
                                        TorneoGrupo::create([
                                            'torneo_id' => $request->torneo_id,
                                            'torneo_categoria_id' => $request->torneo_categoria_id,
                                            'jugador_simple_id' => $TorneoCategoria->multiple ? $TorneoCategoriaEscogidos[$key]->jugador_simple_id : $request->jugador_id[$key],
                                            'jugador_dupla_id' => $TorneoCategoria->multiple ? $TorneoCategoriaEscogidos[$key]->jugador_dupla_id : null,
                                            'grupo_id' => $q->id,
                                            'nombre_grupo' => $request->tipo_grupo_id != null && $request->tipo_grupo_id == App::$TIPO_GRUPO_NUMERO ? ('Grupo '.($key + 1)) : $q->nombre,
                                            'user_create_id' => Auth::guard('web')->user()->id
                                        ]);

                                        $start = ($key * 3) + 1; $end = ($key + 1) * 3;

                                        for ($i = $start; $i <= $end; $i++)
                                        {
                                            TorneoGrupo::create([
                                                'torneo_id' => $request->torneo_id,
                                                'torneo_categoria_id' => $request->torneo_categoria_id,
                                                'jugador_simple_id' => $JugadoresCollect->get($i-1)->jugador_simple_id,
                                                'jugador_dupla_id' => $TorneoCategoria->multiple ? $JugadoresCollect->get($i-1)->jugador_dupla_id : null,
                                                'grupo_id' => $q->id,
                                                'nombre_grupo' =>  $request->tipo_grupo_id != null && $request->tipo_grupo_id == App::$TIPO_GRUPO_NUMERO ? ('Grupo '.($key + 1)) : $q->nombre,
                                                'user_create_id' => Auth::guard('web')->user()->id
                                            ]);
                                        }
                                    }
                                }
                            }else if($request->tipo == "random")
                            {
                                $Jugadores = $Torneo->torneoJugadors()->where('torneo_categoria_id', $request->torneo_categoria_id)->inRandomOrder()->get();

                                $CantidadGrupos = (count($Jugadores) / 4);

                                $GruposDisponibles = Grupo::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->orderBy('nombre', 'asc')->get();

                                if(count($GruposDisponibles) < $CantidadGrupos){
                                    $Result->Message = "Por favor, registre ".($CantidadGrupos-count($GruposDisponibles))." grupos más para generar las llaves";
                                    return response()->json($Result);
                                }

                                $Grupos = Grupo::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->orderBy('nombre', 'asc')->take($CantidadGrupos)->get();

                                $JugadoresCollect = collect($Jugadores);

                                //CREACIÓN GRUPOS
                                foreach ($Grupos as $key => $q)
                                {
                                    $start = ($key * 4) + 1; $end = ($key + 1) * 4;

                                    for ($i = $start; $i <= $end; $i++)
                                    {
                                        TorneoGrupo::create([
                                            'torneo_id' => $request->torneo_id,
                                            'torneo_categoria_id' => $request->torneo_categoria_id,
                                            'jugador_simple_id' => $JugadoresCollect->get($i-1)->jugador_simple_id,
                                            'jugador_dupla_id' => $TorneoCategoria->multiple ? $JugadoresCollect->get($i-1)->jugador_dupla_id : null,
                                            'grupo_id' => $q->id,
                                            'nombre_grupo' => $request->tipo_grupo_id != null && $request->tipo_grupo_id == App::$TIPO_GRUPO_NUMERO ? ('Grupo '.($key + 1)) : $q->nombre,
                                            'user_create_id' => Auth::guard('web')->user()->id
                                        ]);
                                    }
                                }

                            }else{
                                $Result->Message = "El tipo ingresado no es vàlido, por favor seleccione uno válido";
                                return response()->json($Result);
                            }

                            $OriginalOrder = $Torneo->torneoGrupos->where('grupo_id', $Grupos->first()->id)->pluck('id')->toArray();
                            $RandomOrder = TorneoGrupo::where('torneo_id', $TorneoCategoria->torneo_id)->where('torneo_categoria_id', $TorneoCategoria->id)->where('grupo_id', $Grupos->first()->id)->inRandomOrder()->pluck('id')->toArray();

                            $Positions = [];

                            foreach ($OriginalOrder as $q) {
                                foreach ($RandomOrder as $key2 => $q2) {if($q == $q2) $Positions[] = $key2;}
                            }

                            //CREACIÓN PARTIDOS
                            foreach ($Grupos as $q)
                            {
                                $TorneoGrupos = TorneoGrupo::where('torneo_id', $TorneoCategoria->torneo_id)
                                ->where('torneo_categoria_id', $TorneoCategoria->id)
                                ->where('grupo_id', $q->id)->get();

                                $CantidadPartidos = 0;

                                for ($i = (count($TorneoGrupos)-1); $i >= 1; $i--){$CantidadPartidos += $i;}

                                if($CantidadPartidos > 0)
                                {
                                    $GruposCollect = collect($TorneoGrupos);

                                    $ArregloPartidos = [];

                                    foreach ($Positions as $key){
                                        foreach ($GruposCollect as $key2 => $q2){
                                            if($key == $key2){
                                                foreach ($GruposCollect as $key3 => $q3){
                                                    if($q2->jugador_simple_id != $q3->jugador_simple_id && $key3 > $key2){
                                                        $ArregloPartidos[] = [
                                                            'key' => $TorneoCategoria->multiple ? (($q2->jugador_simple_id.'-'.$q2->jugador_dupla_id).'-'.($q3->jugador_simple_id.'-'.$q3->jugador_dupla_id)) : ($q2->jugador_simple_id.'-'.$q3->jugador_simple_id),
                                                            'JugadorLocal' => $q2->jugador_simple_id,
                                                            'JugadorLocalDupla' => $q2->jugador_dupla_id,
                                                            'JugadorRival' => $q3->jugador_simple_id,
                                                            'JugadorRivalDupla' => $q3->jugador_dupla_id
                                                        ];
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    $ArregloPartidosCollect = collect($ArregloPartidos);

                                    $MaximoPartidoPorJugador = max(array_count_values(collect($ArregloPartidos)->pluck('JugadorLocal')->toArray()));

                                    $PartidosModel = [];

                                    $PartidosModel[] = (object)$ArregloPartidosCollect->get(1);
                                    $PartidosModel[] = (object)$ArregloPartidosCollect->get($MaximoPartidoPorJugador+1);
                                    $PartidosModel[] = (object)$ArregloPartidosCollect->get(0);
                                    $PartidosModel[] = (object)$ArregloPartidosCollect->last();
                                    $PartidosModel[] = (object)$ArregloPartidosCollect->get(2);
                                    $PartidosModel[] = (object)$ArregloPartidosCollect->get($MaximoPartidoPorJugador);

                                    if(count($PartidosModel) > 0)
                                    {
                                        $PartidosModelCollect = collect($PartidosModel)->shuffle();

                                        $PartidosModelWithDates = [];
                                        $fechaInicioPartido = Carbon::parse($Torneo->fecha_inicio);
                                        $fechaFinalPartido = Carbon::parse($Torneo->fecha_inicio)->addDay(6);

                                        for ($i = 0; $i < ($CantidadPartidos/2); $i ++)
                                        {
                                            if($i >= 1)
                                            {
                                                $fechaInicioPartido = Carbon::parse($fechaFinalPartido)->addDay(1);
                                                $fechaFinalPartido = Carbon::parse($fechaInicioPartido)->addDay(6);
                                            }

                                            $FirstRoundMatchOne = $PartidosModelCollect->whereNotIn('key', collect($PartidosModelWithDates)->pluck('key')->toArray())->first();

                                            $PartidosModelWithDates[] = [
                                                'key' => $TorneoCategoria->multiple ? (($FirstRoundMatchOne->JugadorLocal.'-'.$FirstRoundMatchOne->JugadorLocalDupla).'-'.($FirstRoundMatchOne->JugadorRival.'-'.$FirstRoundMatchOne->JugadorRivalDupla)) : ($FirstRoundMatchOne->JugadorLocal.'-'.$FirstRoundMatchOne->JugadorRival),
                                                'JugadorLocal' => $FirstRoundMatchOne->JugadorLocal,
                                                'JugadorLocalDupla' => $FirstRoundMatchOne->JugadorLocalDupla,
                                                'JugadorRival' => $FirstRoundMatchOne->JugadorRival,
                                                'JugadorRivalDupla' => $FirstRoundMatchOne->JugadorRivalDupla,
                                                'FechaInicio' => $fechaInicioPartido->toDateString(),
                                                'FechaFinal' => $fechaFinalPartido->toDateString(),
                                            ];

                                            foreach ($PartidosModelCollect->whereNotIn('key', collect($PartidosModelWithDates)->pluck('key')->toArray()) as $q2)
                                            {
                                                if(!in_array($FirstRoundMatchOne->JugadorLocal, [$q2->JugadorLocal, $q2->JugadorRival]) &&
                                                    !in_array($FirstRoundMatchOne->JugadorRival, [$q2->JugadorLocal, $q2->JugadorRival])){

                                                    $PartidosModelWithDates[] = [
                                                        'key' => $TorneoCategoria->multiple ? (($q2->JugadorLocal.'-'.$q2->JugadorLocalDupla).'-'.($q2->JugadorRival.'-'.$q2->JugadorRivalDupla)) : ($q2->JugadorLocal.'-'.$q2->JugadorRival),
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

                                        foreach ($PartidosModelWithDates as $q2)
                                        {
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
                                        if(count($PartidosModelWithDates) > 0)
                                        {
                                            $UltimosTorneos = Torneo::orderBy('fecha_final', 'desc')->get()->map(function ($q){
                                                return (object)['id' => $q->id, 'fecha_final' => $q->fecha_final, 'dias' =>  (Carbon::now()->diffInDays(Carbon::parse($q->fecha_final), false))];
                                            })->toArray();

                                            $UltimoTorneo = collect($UltimosTorneos)->where('dias', '<', 0)->sortByDesc('dias')->first();

                                            if($UltimoTorneo != null && $UltimoTorneo->id != $Torneo->id) {
                                                $PartidosPasados = Partido::where('torneo_id', $UltimoTorneo->id)->whereNull('fase')->get()->map(function ($q){
                                                    return  [
                                                        'JugadorLocal' => $q->jugador_local_uno_id,
                                                        'JugadorLocalDupla' => $q->multiple ? $q->jugador_local_dos_id : null,
                                                        'JugadorRival' => $q->multiple ? $q->jugador_rival_uno_id : null,
                                                        'JugadorRivalDupla' => $q->jugador_rival_dos_id
                                                    ];
                                                });

                                                if(count($PartidosPasados) > 0)
                                                {
                                                    foreach ($PartidosPasados as $k){
                                                        $FirstOldPartido = ['JugadorLocal' => $k['JugadorLocal'], 'JugadorLocalDupla' => $k['JugadorLocalDupla'], 'JugadorRival' => $k['JugadorRival'], 'JugadorRivalDupla' => $k['JugadorRivalDupla']];
                                                        foreach ($PartidosModelWithDates as $k2){
                                                            $FirstNewPartido = ['JugadorLocal' => $k2['JugadorLocal'], 'JugadorLocalDupla' => $k2['JugadorLocalDupla'], 'JugadorRival' => $k2['JugadorRival'], 'JugadorRivalDupla' => $k2['JugadorRivalDupla']];
                                                            if(count(array_diff($FirstOldPartido, $FirstNewPartido)) > 0) {$Result->Repeat = true; break;}
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        TorneoCategoria::where('id', $TorneoCategoria->id)->where('torneo_id', $TorneoCategoria->torneo_id)
                                        ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
                                        ->update(['first_final' => false]);

                                        TorneoJugador::where('torneo_id', $TorneoCategoria->torneo_id)
                                        ->where('torneo_categoria_id', $TorneoCategoria->id)->update(['after' => false]);

                                        $Result->Success = true;

                                        DB::commit();
                                    }
                                }
                            }

                        }else{
                            $Result->Message = "Por favor, necesita agregar ".(4-(count($Jugadores) % 4))." ".((4-(count($Jugadores) % 4)) == 1 ? "jugador" : "jugadores"). " más para generar las llaves";
                        }
                    }else{
                      //  $Result->Message = "Lo sentimos, las llaves ya fuerón asignadas y no puede volver a generarlas";
                    }
                }else{
                    $Result->Message = "El Torneo que intenta asignar grupos y partidos ya no se encuentra disponible";
                }
            }

            $Result->Errors = $Validator->errors();

        }catch (\Exception $e){
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }
        return response()->json($Result);
    }

    public function grupoCambiarNombre(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null];

        try{

            DB::beginTransaction();

            TorneoGrupo::whereHas('torneo',
                function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
                ->where('torneo_id', $request->torneo_id)->where('grupo_id', $request->grupo_id)
                ->where('torneo_categoria_id', $request->torneo_category_id_)->update(['nombre_grupo' => $request->value]);

            $Result->Success = true;

            DB::commit();

        }catch (\Exception $e){
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }

        return response()->json($Result);
    }

    public function grupoValidacionGrupo(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        $Jugadores = TorneoJugador::whereHas('torneo',
            function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
            ->where('torneo_id', $request->torneo_id)->where('torneo_categoria_id', $request->categoria_id)->get();

        if((count($Jugadores) % 4) == 0){
            $Result->Success = true;
        }else{
            $Result->Message = 'Por favor, necesita agregar '.(4-(count($Jugadores) % 4)).' '.((4-(count($Jugadores) % 4)) == 1 ? 'jugador' : 'jugadores'). ' más para generar las llaves';
        }

        return response()->json($Result);
    }

    public function grupoManualPartialView($torneo_id, $categoria_id, $tipo = null)
    {
        $Grupos = [];

        $Jugadores = TorneoJugador::whereHas('torneo',
        function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
        ->where('torneo_id', $torneo_id)->where('torneo_categoria_id', $categoria_id)->get();

        $TipoGrupo = $tipo != null ? $tipo : App::$TIPO_GRUPO_LETRA;

        if((count($Jugadores) % 4) == 0)
        {
            $CantidadGrupos = (count($Jugadores) / 4);

            $Grupos = Grupo::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->orderBy('nombre', 'asc')
            ->take($CantidadGrupos)->get()->map(function ($item, $key) use ($TipoGrupo){
                return (object)['id' => $item->id, 'nombre' => $TipoGrupo == App::$TIPO_GRUPO_LETRA ? $item->nombre : ('Grupo '.($key + 1))];
            });
        }

        return view('auth'.'.'.$this->viewName.'.ajax.grupo.manual.partialView',
            ['Torneo' => $torneo_id, 'Categoria' => $categoria_id, 'Grupos' => $Grupos, 'TipoGrupo' => $TipoGrupo, 'ViewName' => ucfirst($this->viewName)]);
    }

    public function grupoManualStore(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null, 'Repeat' => false];

        try {

            DB::beginTransaction();

            $Validator = Validator::make($request->all(), [
                'torneo_id' => 'required',
                'torneo_categoria_id' => 'required'
            ]);

            $request->merge(['agregar' => filter_var($request->agregar, FILTER_VALIDATE_BOOLEAN)]);

            if (!$Validator->fails())
            {
                $TorneoCategoria = TorneoCategoria::where('id', $request->torneo_categoria_id)
                ->where('torneo_id', $request->torneo_id)->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})->first();

                $Torneo = $TorneoCategoria->torneo;

                if($Torneo != null)
                {
                    if(count($Torneo->partidos->where('torneo_categoria_id', $request->torneo_categoria_id)) <= 0 || $request->agregar)
                    {
                        $Jugadores = $Torneo->torneoJugadors->where('torneo_categoria_id', $request->torneo_categoria_id);

                        if(count($Jugadores) < App::$PARTICIPANTES_MINIMOS_POR_TORNEO){
                            $Result->Message = "Por favor, registre al menos ".(App::$PARTICIPANTES_MINIMOS_POR_TORNEO - count($Jugadores))." jugadores más para generar las llaves";
                            return response()->json($Result);
                        }

                        if(count($Jugadores) > App::$PARTICIPANTES_MAXIMOS_POR_TORNEO){
                            $Result->Message = "Por favor, solo puede registrar como máximo ".App::$PARTICIPANTES_MAXIMOS_POR_TORNEO." jugadores para generar las llaves";
                            return response()->json($Result);
                        }

                        $CantidadGrupos = (count($Jugadores) / 4);

                        $GruposDisponibles = Grupo::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->orderBy('nombre', 'asc')->get();

                        if(count($GruposDisponibles) < $CantidadGrupos){
                            $Result->Message = "La cantidad de ".$CantidadGrupos." grupos generados, supera a la cantidad de ".count($GruposDisponibles)." grupos disponibles";
                            return response()->json($Result);
                        }

                        if((count($Jugadores) % 4) == 0)
                        {
                            if((count($request->grupo_id == null ? [] : $request->grupo_id)*4) != (count($request->jugador_uno_id == null ? [] : $request->jugador_uno_id) + count($request->jugador_dos_id == null ? [] : $request->jugador_dos_id) + count($request->jugador_tres_id == null ? [] : $request->jugador_tres_id) + count($request->jugador_cuatro_id == null ? [] : $request->jugador_cuatro_id))){
                                $Result->Message = "Por favor, complete todos los campos vacíos";
                                return response()->json($Result);

                            }else{

                                $Grupos = Grupo::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
                                ->orderBy('nombre', 'asc')->whereIn('id', $request->grupo_id)->get();

                                $LasPositionGrupo = 0;
                                if($request->agregar)
                                {
                                    $TorneoGruposExistentes = TorneoGrupo::whereHas('torneo',
                                        function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
                                        ->where('torneo_id', $Torneo->id)->where('torneo_categoria_id', $TorneoCategoria->id)->get();

                                    $GruposExistentesFinal =  array_values(array_unique($TorneoGruposExistentes->pluck('nombre_grupo')->map(function($q){
                                        return intval(str_replace('Grupo', '', $q));
                                    })->toArray()));

                                    $LasPositionGrupo = $GruposExistentesFinal[count($GruposExistentesFinal)-1];
                                }

                                for ($i = 0; $i < 4; $i++)
                                {
                                    $TorneoCategoriaEscogidos = [];
                                    $jugador_id = $i == 0 ? $request->jugador_uno_id : ($i == 1 ? $request->jugador_dos_id : ($i == 2 ? $request->jugador_tres_id : $request->jugador_cuatro_id));

                                    if ($TorneoCategoria->multiple) {
                                        for ($i2 = 0; $i2 < count($jugador_id); $i2++) {
                                            $result = $Torneo->torneoJugadors()->where('torneo_categoria_id', $request->torneo_categoria_id)
                                                ->where('id', $jugador_id[$i2])->first();

                                            if ($result != null) $TorneoCategoriaEscogidos[] = $result;
                                        }

                                        /*$TorneoCategoriaEscogidos[] = $Torneo->torneoJugadors()->where('torneo_categoria_id', $request->torneo_categoria_id)
                                        ->where('id', $jugador_id[0])->first();

                                        if(count($jugador_id) > 1)
                                        {
                                            $TorneoCategoriaEscogidos[] = $Torneo->torneoJugadors()->where('torneo_categoria_id', $request->torneo_categoria_id)
                                            ->where('id', $jugador_id[1])->first();
                                        }*/
                                    }

                                    foreach ($Grupos as $key => $q)
                                    {
                                       TorneoGrupo::create([
                                            'torneo_id' => $request->torneo_id,
                                            'torneo_categoria_id' => $request->torneo_categoria_id,
                                            'jugador_simple_id' => $TorneoCategoria->multiple ? $TorneoCategoriaEscogidos[$key]->jugador_simple_id : $jugador_id[$key],
                                            'jugador_dupla_id' => $TorneoCategoria->multiple ? $TorneoCategoriaEscogidos[$key]->jugador_dupla_id : null,
                                            'grupo_id' => $q->id,
                                            'nombre_grupo' => $request->tipo_grupo_id != null && $request->tipo_grupo_id == App::$TIPO_GRUPO_NUMERO ? ('Grupo '.($LasPositionGrupo + $key + 1)) : $q->nombre,
                                            'user_create_id' => Auth::guard('web')->user()->id
                                        ]);
                                    }
                                }

                                $OriginalOrder = $Torneo->torneoGrupos->where('grupo_id', $Grupos->first()->id)->pluck('id')->toArray();
                                $RandomOrder = TorneoGrupo::where('torneo_id', $TorneoCategoria->torneo_id)->where('torneo_categoria_id', $TorneoCategoria->id)->where('grupo_id', $Grupos->first()->id)->inRandomOrder()->pluck('id')->toArray();

                                $Positions = [];

                                foreach ($OriginalOrder as $q) {
                                    foreach ($RandomOrder as $key2 => $q2) {if($q == $q2) $Positions[] = $key2;}
                                }

                                //CREACIÓN PARTIDOS
                                foreach ($Grupos as $q)
                                {
                                    $TorneoGrupos = TorneoGrupo::where('torneo_id', $TorneoCategoria->torneo_id)
                                    ->where('torneo_categoria_id', $TorneoCategoria->id)
                                    ->where('grupo_id', $q->id)->get();

                                    $CantidadPartidos = 0;

                                    for ($i = (count($TorneoGrupos)-1); $i >= 1; $i--){$CantidadPartidos += $i;}

                                    if($CantidadPartidos > 0)
                                    {
                                        $GruposCollect = collect($TorneoGrupos);

                                        $ArregloPartidos = [];

                                        foreach ($Positions as $key){
                                            foreach ($GruposCollect as $key2 => $q2){
                                                if($key == $key2){
                                                    foreach ($GruposCollect as $key3 => $q3){
                                                        if($q2->jugador_simple_id != $q3->jugador_simple_id && $key3 > $key2){
                                                            $ArregloPartidos[] = [
                                                                'key' => $TorneoCategoria->multiple ? (($q2->jugador_simple_id.'-'.$q2->jugador_dupla_id).'-'.($q3->jugador_simple_id.'-'.$q3->jugador_dupla_id)) : ($q2->jugador_simple_id.'-'.$q3->jugador_simple_id),
                                                                'JugadorLocal' => $q2->jugador_simple_id,
                                                                'JugadorLocalDupla' => $q2->jugador_dupla_id,
                                                                'JugadorRival' => $q3->jugador_simple_id,
                                                                'JugadorRivalDupla' => $q3->jugador_dupla_id
                                                            ];
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        $ArregloPartidosCollect = collect($ArregloPartidos);

                                        $MaximoPartidoPorJugador = max(array_count_values(collect($ArregloPartidos)->pluck('JugadorLocal')->toArray()));

                                        $PartidosModel = [];

                                        $PartidosModel[] = (object)$ArregloPartidosCollect->get(1);
                                        $PartidosModel[] = (object)$ArregloPartidosCollect->get($MaximoPartidoPorJugador+1);
                                        $PartidosModel[] = (object)$ArregloPartidosCollect->get(0);
                                        $PartidosModel[] = (object)$ArregloPartidosCollect->last();
                                        $PartidosModel[] = (object)$ArregloPartidosCollect->get(2);
                                        $PartidosModel[] = (object)$ArregloPartidosCollect->get($MaximoPartidoPorJugador);

                                        if(count($PartidosModel) > 0)
                                        {
                                            $PartidosModelCollect = collect($PartidosModel)->shuffle();

                                            $PartidosModelWithDates = [];
                                            $fechaInicioPartido = Carbon::parse($Torneo->fecha_inicio);
                                            $fechaFinalPartido = Carbon::parse($Torneo->fecha_inicio)->addDay(6);

                                            for ($i = 0; $i < ($CantidadPartidos/2); $i ++)
                                            {
                                                if($i >= 1)
                                                {
                                                    $fechaInicioPartido = Carbon::parse($fechaFinalPartido)->addDay(1);
                                                    $fechaFinalPartido = Carbon::parse($fechaInicioPartido)->addDay(6);
                                                }

                                                $FirstRoundMatchOne = $PartidosModelCollect->whereNotIn('key', collect($PartidosModelWithDates)->pluck('key')->toArray())->first();

                                                $PartidosModelWithDates[] = [
                                                    'key' => $TorneoCategoria->multiple ? (($FirstRoundMatchOne->JugadorLocal.'-'.$FirstRoundMatchOne->JugadorLocalDupla).'-'.($FirstRoundMatchOne->JugadorRival.'-'.$FirstRoundMatchOne->JugadorRivalDupla)) : ($FirstRoundMatchOne->JugadorLocal.'-'.$FirstRoundMatchOne->JugadorRival),
                                                    'JugadorLocal' => $FirstRoundMatchOne->JugadorLocal,
                                                    'JugadorLocalDupla' => $FirstRoundMatchOne->JugadorLocalDupla,
                                                    'JugadorRival' => $FirstRoundMatchOne->JugadorRival,
                                                    'JugadorRivalDupla' => $FirstRoundMatchOne->JugadorRivalDupla,
                                                    'FechaInicio' => $fechaInicioPartido->toDateString(),
                                                    'FechaFinal' => $fechaFinalPartido->toDateString(),
                                                ];

                                                foreach ($PartidosModelCollect->whereNotIn('key', collect($PartidosModelWithDates)->pluck('key')->toArray()) as $q2)
                                                {
                                                    if(!in_array($FirstRoundMatchOne->JugadorLocal, [$q2->JugadorLocal, $q2->JugadorRival]) &&
                                                        !in_array($FirstRoundMatchOne->JugadorRival, [$q2->JugadorLocal, $q2->JugadorRival])){

                                                        $PartidosModelWithDates[] = [
                                                            'key' => $TorneoCategoria->multiple ? (($q2->JugadorLocal.'-'.$q2->JugadorLocalDupla).'-'.($q2->JugadorRival.'-'.$q2->JugadorRivalDupla)) : ($q2->JugadorLocal.'-'.$q2->JugadorRival),
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

                                            foreach ($PartidosModelWithDates as $q2)
                                            {
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
                                            if(count($PartidosModelWithDates) > 0)
                                            {
                                                $UltimosTorneos = Torneo::orderBy('fecha_final', 'desc')->get()->map(function ($q){
                                                    return (object)['id' => $q->id, 'fecha_final' => $q->fecha_final, 'dias' =>  (Carbon::now()->diffInDays(Carbon::parse($q->fecha_final), false))];
                                                })->toArray();

                                                $UltimoTorneo = collect($UltimosTorneos)->where('dias', '<', 0)->sortByDesc('dias')->first();
                                                if($UltimoTorneo != null && $UltimoTorneo->id != $Torneo->id) {
                                                    $PartidosPasados = Partido::where('torneo_id', $UltimoTorneo->id)->whereNull('fase')->get()->map(function ($q){
                                                        return  [
                                                            'JugadorLocal' => $q->jugador_local_uno_id,
                                                            'JugadorLocalDupla' => $q->multiple ? $q->jugador_local_dos_id : null,
                                                            'JugadorRival' => $q->multiple ? $q->jugador_rival_uno_id : null,
                                                            'JugadorRivalDupla' => $q->jugador_rival_dos_id
                                                        ];
                                                    });

                                                    if(count($PartidosPasados) > 0)
                                                    {
                                                        foreach ($PartidosPasados as $k){
                                                            $FirstOldPartido = ['JugadorLocal' => $k['JugadorLocal'], 'JugadorLocalDupla' => $k['JugadorLocalDupla'], 'JugadorRival' => $k['JugadorRival'], 'JugadorRivalDupla' => $k['JugadorRivalDupla']];
                                                            foreach ($PartidosModelWithDates as $k2){
                                                                $FirstNewPartido = ['JugadorLocal' => $k2['JugadorLocal'], 'JugadorLocalDupla' => $k2['JugadorLocalDupla'], 'JugadorRival' => $k2['JugadorRival'], 'JugadorRivalDupla' => $k2['JugadorRivalDupla']];
                                                                if(count(array_diff($FirstOldPartido, $FirstNewPartido)) > 0) {$Result->Repeat = true; break;}
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                            $Torneo->torneoJugadors()->where('torneo_categoria_id', $request->torneo_categoria_id)->update(['after' => false]);

                                            TorneoCategoria::where('id', $TorneoCategoria->id)->where('torneo_id', $TorneoCategoria->torneo_id)
                                            ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
                                            ->update(['first_final' => false]);

                                            TorneoJugador::where('torneo_id', $TorneoCategoria->torneo_id)
                                            ->where('torneo_categoria_id', $TorneoCategoria->id)->update(['after' => false]);

                                            $Result->Success = true;

                                            DB::commit();
                                        }
                                    }
                                }
                            }

                        }else{
                            $Result->Message = "Por favor, necesita agregar ".(4 - (count($Jugadores) % 4))." jugadores más para generar las llaves";
                        }
                    }else{
                        $Result->Message = "Lo sentimos, las llaves ya fuerón asignadas y no puede volver a generarlas";
                    }
                }else{
                    $Result->Message = "El Torneo que intenta asignar grupos y partidos ya no se encuentra disponible";
                }
            }

            $Result->Errors = $Validator->errors();

        }catch (\Exception $e){
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }
        return response()->json($Result);
    }

    public function grupoAgregarPartialView($torneo_id, $categoria_id)
    {
        $Grupos = [];

        $Jugadores = TorneoJugador::whereHas('torneo',
        function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
        ->where('after', true)->where('torneo_id', $torneo_id)->where('torneo_categoria_id', $categoria_id)->get();

        if((count($Jugadores) % 4) == 0)
        {
            $TorneoGrupos = TorneoGrupo::whereHas('torneo',
                function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
                ->where('torneo_id', $torneo_id)->where('torneo_categoria_id', $categoria_id)->get();

            $ArrayNumbersoLetters = array_values(array_unique($TorneoGrupos->pluck('nombre_grupo')->map(function($q){
                return intval(str_replace('Grupo', '', $q));
            })->toArray()));

            $TipoGrupo = $ArrayNumbersoLetters[0] >= 1 ? App::$TIPO_GRUPO_NUMERO : App::$TIPO_GRUPO_LETRA;

            $GruposExistentes = array_unique($TorneoGrupos->pluck('grupo_id')->toArray());

            $CantidadGrupos = (count($Jugadores) / 4);

            $Grupos = Grupo::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
            ->whereNotIn('id', $GruposExistentes)->orderBy('nombre', 'asc')->take($CantidadGrupos)
            ->get()->map(function ($item, $key) use ($TipoGrupo, $ArrayNumbersoLetters){
                return (object)['id' => $item->id, 'nombre' => $TipoGrupo == App::$TIPO_GRUPO_LETRA ? $item->nombre : ('Grupo '.($ArrayNumbersoLetters[count($ArrayNumbersoLetters)-1] + ($key + 1)))];
            });
        }

        return view('auth'.'.'.$this->viewName.'.ajax.grupo.agregar.partialView',
            ['Torneo' => $torneo_id, 'Categoria' => $categoria_id, 'TipoGrupo' => $TipoGrupo, 'Grupos' => $Grupos, 'ViewName' => ucfirst($this->viewName)]);
    }

    /*TORNEO JUGADOR*/
    public function jugadorListJson(Request $request)
    {
        $Jugadores = [];

        $TorneoCategoria = TorneoCategoria::where('id', $request->torneo_categoria_id)
        ->where('torneo_id', $request->torneo_id)
        ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
        ->first();

        $TorneoGrupo = TorneoGrupo::whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
        ->where('torneo_id', $request->torneo_id)->where('torneo_categoria_id', $request->torneo_categoria_id)->get();

        $JugadoresNoDisponibles = $TorneoCategoria->multiple ? $TorneoGrupo->pluck('jugador_simple_id')->toArray() : array_merge($TorneoGrupo->pluck('jugador_simple_id')->toArray(), $TorneoGrupo->pluck('jugador_dupla_id')->toArray());
        $JugadoresNoDisponibles = array_filter(array_unique($JugadoresNoDisponibles));

        if($TorneoCategoria != null)
        {
            $Torneo = $TorneoCategoria->torneo;

            if($TorneoCategoria->multiple){

                $Jugadores = $Torneo->torneoJugadors()->where('torneo_categoria_id', $request->torneo_categoria_id)
                    ->whereNotIn('id', json_decode($request->jugador_selected_id))
                    ->whereNotIn('jugador_simple_id', $JugadoresNoDisponibles)
                    ->whereNotIn('jugador_dupla_id', $JugadoresNoDisponibles)
                    ->where(function ($q) use ($request){
                        $q->whereHas('jugadorSimple', function ($q) use($request){
                            if($request->nombre){ $q->where(DB::raw("CONCAT(jugadors.nombres,' ',jugadors.apellidos)"), 'like', '%'.$request->nombre.'%'); }
                        })->orWhereHas('jugadorDupla', function ($q) use($request){
                            if($request->nombre){ $q->where(DB::raw("CONCAT(jugadors.nombres,' ',jugadors.apellidos)"), 'like', '%'.$request->nombre.'%'); }
                        });
                    })
                    ->get()
                    ->map(function ($q){return ['id' => $q->id, 'text' => ($q->jugadorSimple->nombre_completo.' + '.$q->jugadorDupla->nombre_completo)];});

            }else{
                $Jugadores = $Torneo->torneoJugadors()->where('torneo_categoria_id', $request->torneo_categoria_id)
                ->whereNotIn('jugador_simple_id', json_decode($request->jugador_selected_id))
                ->whereNotIn('jugador_simple_id', $JugadoresNoDisponibles)
                ->whereHas('jugadorSimple', function ($q) use($request){
                    if($request->nombre){ $q->where(DB::raw("CONCAT(jugadors.nombres,' ',jugadors.apellidos)"), 'like', '%'.$request->nombre.'%'); }
                })->get()->map(function ($q){return ['id' => $q->jugador_simple_id, 'text' => $q->jugadorSimple->nombre_completo];});
            }
        }

        return response()->json(['data' => $Jugadores]);
    }

    public function jugadorPartialView($torneo_categoria)
    {
        $TorneoCategoria = TorneoCategoria::where('id', $torneo_categoria)->whereHas('torneo', function ($q){
            $q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);
        })->first();

        return view('auth.'.$this->viewName.'.ajax.jugador.partialView', ['ViewName' => ucfirst($this->viewName), 'TorneoCategoria' => $TorneoCategoria]);
    }

    public function jugadorStore(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null];

        try {

            DB::beginTransaction();

            $TorneoCategoria = TorneoCategoria::where('id', $request->torneo_categoria_id)
            ->whereHas('torneo', function ($q){
                $q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);
            })->first();

            $Partidos = Partido::where('torneo_categoria_id', $request->torneo_categoria_id)
            ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);
            })->count();

            if($TorneoCategoria != null)
            {
                if($request->jugadores)
                {
                    $TorneoJugadores = collect(json_decode($request->jugadores))->map(function ($obj, $key) use ($TorneoCategoria, $Partidos){
                        return [
                            'torneo_id' => $TorneoCategoria->torneo_id,
                            'torneo_categoria_id' => $TorneoCategoria->id,
                            'jugador_simple_id' => explode("-", $obj->key)[0],
                            'jugador_dupla_id' => explode("-", $obj->key)[1],
                            'user_create_id' =>  Auth::guard('web')->user()->id,
                            'after' => $Partidos > 0,
                            'created_at' => Carbon::now()
                        ];
                    })->toArray();

                }else{
                    $TorneoJugadores = Jugador::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
                    ->whereIn('id', json_decode($request->ids))->get()
                    ->map(function($obj, $key) use ($TorneoCategoria, $Partidos){
                        return [
                            'torneo_id' => $TorneoCategoria->torneo_id,
                            'torneo_categoria_id' => $TorneoCategoria->id,
                            'jugador_simple_id' => $obj->id,
                            'jugador_dupla_id' => null,
                            'user_create_id' =>  Auth::guard('web')->user()->id,
                            'after' => $Partidos > 0,
                            'created_at' => Carbon::now()
                        ];
                    })->toArray();
                }

                TorneoJugador::insert($TorneoJugadores);

                DB::commit();

                $Result->Success = true;

            }else
                $Result->Message = "El Torneo donde está intentando asignar, ya no se encuentra disponible.";

        }catch (\Exception $e){
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }

        return response()->json($Result);
    }

    public function jugadorDelete(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null];

        try {

            $entity = TorneoJugador::where('id', $request->id)->where('torneo_id', $request->torneo_id)
            ->where('torneo_categoria_id', $request->torneo_category_id)
            ->whereHas('torneo', function ($q){
                $q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);
            })->first();

            $entity->user_update_id = Auth::guard('web')->user()->id;

            if($entity->save())
            {
                $TorneoCategoria = TorneoCategoria::where('id', $request->torneo_category_id)
                ->where('first_final', true)->whereHas('torneo', function ($q){
                    $q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);
                })->first();

                if($TorneoCategoria != null)
                {
                    if($TorneoCategoria->multiple)
                    {
                        Partido::where('torneo_id', $TorneoCategoria->torneo_id)->where('torneo_categoria_id', $TorneoCategoria->id)
                        ->whereNotNull('fase')->where('jugador_local_uno_id', $entity->jugador_simple_id)
                        ->where('jugador_local_dos_id', $entity->jugador_dupla_id)
                        ->update(['jugador_local_uno_id' => null, 'jugador_local_dos_id' => null]);

                        Partido::where('torneo_id', $TorneoCategoria->torneo_id)->where('torneo_categoria_id', $TorneoCategoria->id)
                        ->whereNotNull('fase')->where('jugador_rival_uno_id', $entity->jugador_simple_id)
                        ->where('jugador_rival_dos_id', $entity->jugador_dupla_id)
                        ->update(['jugador_local_uno_id' => null, 'jugador_local_dos_id' => null]);

                    }else{
                        Partido::where('torneo_id', $TorneoCategoria->torneo_id)->where('torneo_categoria_id', $TorneoCategoria->id)
                        ->whereNotNull('fase')->where('jugador_local_uno_id', $entity->jugador_simple_id)->update(['jugador_local_uno_id' => null]);

                        Partido::where('torneo_id', $TorneoCategoria->torneo_id)->where('torneo_categoria_id', $TorneoCategoria->id)
                        ->whereNotNull('fase')->where('jugador_rival_uno_id', $entity->jugador_simple_id)->update(['jugador_rival_uno_id' => null]);
                    }
                }

                if ($entity->delete()) $Result->Success = true;
            }
        }catch (\Exception $e){
            $Result->Message = $e->getMessage();
        }

        return response()->json($Result);
    }

    public function jugadorDeleteMasivo(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null];

        try {

            TorneoJugador::where('torneo_id', $request->torneo_id)
            ->where('torneo_categoria_id', $request->torneo_category_id)
            ->whereHas('torneo', function ($q){
                $q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);
            })->update(['user_update_id' => Auth::guard('web')->user()->id, 'deleted_at' => Carbon::now()]);

            $Result->Success = true;

            /*$entity->user_update_id = Auth::guard('web')->user()->id;
            if($entity->save()){
                if ($entity->delete()) $Result->Success = true;
            }*/
        }catch (\Exception $e){
            $Result->Message = $e->getMessage();
        }

        return response()->json($Result);
    }

    public function jugadorPartialViewMultipleZona($torneo, $torneo_categoria)
    {
        $ToneosZonas = TorneoZona::where('torneo_id', $torneo)->get()->map(function ($q) {
            return $q->zona != null ? (object)['id' => $q->zona->id, 'nombre' => $q->zona->nombre] : null;
        })->toArray();

        $Zonas = collect(array_values(array_filter($ToneosZonas)));

        return view('auth'.'.'.$this->viewName.'.ajax.jugador.partialViewMultipleZona', ['TorneoCategoria' => $torneo_categoria, 'Zonas' => $Zonas, 'ViewName' => ucfirst($this->viewName)]);
    }

    public function jugadorPartialViewZona($id)
    {
        $Entity = TorneoJugador::where('id', $id)->whereHas('torneo', function ($q){
            $q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);
        })->first();

        $ToneosZonas = TorneoZona::where('torneo_id', $Entity->torneo_id)->get()->map(function ($q) {
            return $q->zona != null ? (object)['id' => $q->zona->id, 'nombre' => $q->zona->nombre] : null;
        })->toArray();

        $Zonas = collect(array_values(array_filter($ToneosZonas)));

        return view('auth'.'.'.$this->viewName.'.ajax.jugador.partialViewZona', ['Model' => $Entity, 'Zonas' => $Zonas, 'ViewName' => ucfirst($this->viewName)]);
    }

    public function jugadorZonaStore(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try {

            DB::beginTransaction();

            $request->merge([
                'pago' => filter_var($request->pago, FILTER_VALIDATE_BOOLEAN),
                'monto' => $request->pago ? $request->monto : null
            ]);

            $Validator = Validator::make($request->all(), [
                'monto' =>  ($request->pago ? 'required' : 'nullable').'|numeric:10,2',
            ], [
                'monto.required' => 'El campo monto es obligatorio.'
            ]);

            if (!$Validator->fails()) {

                $entity = null;

                if ($request->id != 0) $entity = TorneoJugador::find($request->id);

                if ($entity != null) {
                    $request->merge(['user_update_id' => Auth::guard('web')->user()->id]);
                    $entity->update($request->only('zona_id', 'pago', 'monto', 'user_update_id'));

                    DB::commit();
                    $Result->Success = true;

                } else {
                    $Result->Message = "No se puede actualizar el torneo jugador porque ya no se encuentra disponible.";
                }
            }

            $Result->Errors = $Validator->errors();

            /*$Validator = Validator::make($request->all(), [
                'zona_id' => 'required',
                'pago' => 'required',
            ], [
                'zona_id.required' => 'El campo zona es obligatorio.'
            ]);

            if (!$Validator->fails()){

                if($request->id != 0) $entity = TorneoJugador::find($request->id);

                if($entity != null)
                {
                    $request->merge(['user_update_id' =>  Auth::guard('web')->user()->id]);
                    $entity->update($request->only('zona_id', 'pago', 'user_update_id'));

                    DB::commit();
                    $Result->Success = true;

                }else{
                    $Result->Message = "No se puede actualizar el torneo jugador porque ya no se encuentra disponible.";
                }
            }

            $Result->Errors = $Validator->errors();*/

        }catch (\Exception $e)
        {
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }

        return response()->json($Result);
    }

    public function jugadorReporte($tipo, $torneo, $toneo_categoria)
    {
        return Excel::download(new TorneoJugadorExport($torneo, $toneo_categoria, $tipo), $tipo == 'localizacion' ? 'Reporte de jugadores por localización.xlsx' : 'Reporte de jugadores por pagos.xlsx');
    }

    public function jugadorAvailableListJson(Request $request)
    {
        $TorneoCategoria = TorneoCategoria::where('id', $request->torneo_categoria_id)
        ->where('torneo_id', $request->torneo_id)->whereHas('torneo', function ($q){
            $q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);
        })->first();

        $JugadoresNoDisponiblesSimples = $TorneoCategoria->torneo->torneoJugadors()
            ->where('torneo_categoria_id', $TorneoCategoria->id)->pluck('jugador_simple_id')->toArray();

        $JugadoresNoDisponiblesDuplas = $TorneoCategoria->torneo->torneoJugadors()
            ->where('torneo_categoria_id', $TorneoCategoria->id)->pluck('jugador_dupla_id')->toArray();

        $JugadoresNoDisponibles = array_unique(array_filter(array_merge($JugadoresNoDisponiblesSimples, $JugadoresNoDisponiblesDuplas, json_decode($request->jugador_selected_id))));

        $JugadoresDisponibles = Jugador::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
        ->where(function ($q) use($request){
            if($request->nombre){ $q->where(DB::raw("CONCAT(jugadors.nombres,' ',jugadors.apellidos)"), 'like', '%'.$request->nombre.'%'); }
        })
        ->whereNotIn('id', $JugadoresNoDisponibles)->get()
        ->map(function ($q){return ['id' => $q->id, 'text' => $q->nombre_completo];});

        return response()->json(['data' => $JugadoresDisponibles]);
    }

    public function jugadorPartialViewChange($torneo, $torneo_categoria, $torneoJugador)
    {
        $TorneoCategoria = TorneoCategoria::where('id', $torneo_categoria)->where('torneo_id', $torneo)
        ->whereHas('torneo', function ($q){
            $q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);
        })->first();

        $JugadoresNoDisponiblesSimples = $TorneoCategoria->torneo->torneoJugadors()
        ->where('torneo_categoria_id', $TorneoCategoria->id)->pluck('jugador_simple_id')->toArray();

        $JugadoresNoDisponiblesDuplas = $TorneoCategoria->torneo->torneoJugadors()
        ->where('torneo_categoria_id', $TorneoCategoria->id)->pluck('jugador_dupla_id')->toArray();

        $JugadoresNoDisponibles = array_unique(array_filter(array_merge($JugadoresNoDisponiblesSimples, $JugadoresNoDisponiblesDuplas)));

        $JugadoresDisponibles = Jugador::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
        ->whereNotIn('id', $JugadoresNoDisponibles)->get();

        /*$JugadoresDisponiblesDobles =  Jugador::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
        ->whereNotIn('id', $JugadoresNoDisponibles)->get();*/

        $TorneoJugador = $TorneoCategoria->torneo->torneoJugadors()
        ->where('torneo_categoria_id', $TorneoCategoria->id)->where('id', $torneoJugador)->first();

        $Jugador = $TorneoCategoria->multiple ? [$TorneoJugador->jugador_simple_id, $TorneoJugador->jugador_dupla_id] : [$TorneoJugador->jugador_simple_id];

        $JugadorSimple = Jugador::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->where('id' ,$Jugador[0])->first();
        $JugadorDupla = $TorneoCategoria->multiple ? Jugador::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->where('id' ,$Jugador[1])->first() : 0;

        return view('auth'.'.'.$this->viewName.'.ajax.jugador.partialViewChange', ['TorneoCategoria' =>  $TorneoCategoria, 'JugadorSimple' => $JugadorSimple, 'JugadorDupla' => $JugadorDupla, 'Jugadores' => $JugadoresDisponibles, 'ViewName' => ucfirst($this->viewName)]);
    }

    public function jugadorChange(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try {

            DB::beginTransaction();

            $Validator = Validator::make($request->all(), [
                'torneo_id' => 'required',
                'jugador_simple_id' => 'required',
                'jugador_dupla_id' => filter_var($request->multiple, FILTER_VALIDATE_BOOLEAN) ? 'required' : 'nullable',
                'torneo_categoria_id' => 'required',
                'jugador_remplazo_id' => filter_var($request->multiple, FILTER_VALIDATE_BOOLEAN) ? ($request->jugador_remplazo_id == null && $request->jugador_remplazo_dupla_id == null ? 'required' : 'nullable')  : 'required',
                'jugador_remplazo_dupla_id' => filter_var($request->multiple, FILTER_VALIDATE_BOOLEAN) ? ($request->jugador_remplazo_id == null && $request->jugador_remplazo_dupla_id == null ? 'required' : 'nullable')  : 'nullable',
            ]);

            if (!$Validator->fails()) {

                $TorneoCategoria = TorneoCategoria::where('id', $request->torneo_categoria_id)
                ->where('torneo_id', $request->torneo_id)
                ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
                ->first();

                $Torneo = $TorneoCategoria->torneo;

                if($TorneoCategoria->multiple)
                {
                    //Simple - 1, Dupla - 2, Ambos - 3
                    $Update = 0;
                    if($request->jugador_remplazo_id != null && $request->jugador_remplazo_dupla_id == null) $Update = 1;
                    else if($request->jugador_remplazo_id == null && $request->jugador_remplazo_dupla_id != null) $Update = 2;
                    else if($request->jugador_remplazo_id != null && $request->jugador_remplazo_dupla_id != null) $Update = 3;

                    if ($Update != 0)
                    {
                        //TorneoJugadores
                        $Torneo->torneoJugadors()->where('torneo_categoria_id', $TorneoCategoria->id)
                        ->where('jugador_simple_id', $request->jugador_simple_id)->where('jugador_dupla_id', $request->jugador_dupla_id)
                        ->update($Update == 1 ? ['jugador_simple_id' => $request->jugador_remplazo_id] : ($Update == 2 ? ['jugador_dupla_id' => $request->jugador_remplazo_dupla_id] : ['jugador_simple_id' => $request->jugador_remplazo_id, 'jugador_dupla_id' => $request->jugador_remplazo_dupla_id]));

                        //TorneoGrupos
                        $Torneo->torneoGrupos()->where('torneo_categoria_id', $TorneoCategoria->id)
                        ->where('jugador_simple_id', $request->jugador_simple_id)->where('jugador_dupla_id', $request->jugador_dupla_id)
                        ->update($Update == 1 ? ['jugador_simple_id' => $request->jugador_remplazo_id] : ($Update == 2 ? ['jugador_dupla_id' => $request->jugador_remplazo_dupla_id] : ['jugador_simple_id' => $request->jugador_remplazo_id, 'jugador_dupla_id' => $request->jugador_remplazo_dupla_id]));

                        //TorneoPartidos
                        $Torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                        ->where('jugador_local_uno_id', $request->jugador_simple_id)->where('jugador_local_dos_id', $request->jugador_dupla_id)
                        ->update($Update == 1 ? ['jugador_local_uno_id' => $request->jugador_remplazo_id] : ($Update == 2 ? ['jugador_local_dos_id' => $request->jugador_remplazo_dupla_id] : ['jugador_local_uno_id' => $request->jugador_remplazo_id, 'jugador_local_dos_id' => $request->jugador_remplazo_dupla_id] ));

                        $Torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                        ->where('jugador_local_dos_id', $request->jugador_simple_id)->where('jugador_local_uno_id', $request->jugador_dupla_id)
                        ->update($Update == 1 ? ['jugador_local_dos_id' => $request->jugador_simple_id] : ($Update == 2 ? ['jugador_local_uno_id' => $request->jugador_dupla_id] : ['jugador_local_dos_id' => $request->jugador_simple_id, 'jugador_local_uno_id' => $request->jugador_dupla_id]));

                        $Torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                        ->where('jugador_rival_uno_id', $request->jugador_simple_id)->where('jugador_rival_dos_id', $request->jugador_dupla_id)
                        ->update($Update == 1 ? ['jugador_rival_uno_id' => $request->jugador_remplazo_id] : ($Update == 2 ? ['jugador_rival_dos_id' => $request->jugador_remplazo_dupla_id] : ['jugador_rival_uno_id' => $request->jugador_remplazo_id, 'jugador_rival_dos_id' => $request->jugador_remplazo_dupla_id]));

                        $Torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                        ->where('jugador_rival_dos_id', $request->jugador_simple_id)->where('jugador_rival_uno_id', $request->jugador_dupla_id)
                        ->update($Update == 1 ? ['jugador_rival_dos_id' => $request->jugador_remplazo_id] : ($Update == 2 ? ['jugador_rival_uno_id' => $request->jugador_remplazo_dupla_id] : ['jugador_rival_dos_id' => $request->jugador_remplazo_id, 'jugador_rival_uno_id' => $request->jugador_remplazo_dupla_id]));

                        DB::commit();

                        $Result->Success = true;

                    }else{
                        $Result->Message = "Por favor, seleccione al menos un jugador a remplazar.";
                    }

                }else{

                    //TorneoJugadores
                    $Torneo->torneoJugadors()->where('torneo_categoria_id', $TorneoCategoria->id)->where('jugador_simple_id', $request->jugador_simple_id)
                    ->update(['jugador_simple_id' => $request->jugador_remplazo_id]);

                    //TorneoGrupos
                    $Torneo->torneoGrupos()->where('torneo_categoria_id', $TorneoCategoria->id)->where('jugador_simple_id', $request->jugador_simple_id)
                    ->update(['jugador_simple_id' => $request->jugador_remplazo_id]);

                    //TorneoPartidos
                    $Torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)->where('jugador_local_uno_id', $request->jugador_simple_id)
                    ->update(['jugador_local_uno_id' => $request->jugador_remplazo_id]);

                    $Torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)->where('jugador_rival_uno_id', $request->jugador_simple_id)
                    ->update(['jugador_rival_uno_id' => $request->jugador_remplazo_id]);

                    DB::commit();

                    $Result->Success = true;
                }
            }

            $Result->Errors = $Validator->errors();

        }catch (\Exception $e)
        {
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }

        return response()->json($Result);
    }

    /*public function grupoPartialView($id, $torneo_categoria_id)
    {
        $entity = null;

        $TorneoCategoria = TorneoCategoria::where('id', $torneo_categoria_id)->whereHas('torneo', function ($q){
            $q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id); })
        ->first();

        if($TorneoCategoria != null)
        {
            if($id != 0)
            {
                $entity = TorneoGrupo::where('id', $id)
                    ->where('torneo_categoria_id', $TorneoCategoria->id)
                    ->whereHas('torneoCategoria.torneo', function ($q){
                        $q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);
                    })->first();
            }

            $JugadoresNoDisponibles = TorneoJugador::whereHas('torneoGrupo', function ($q) use ($TorneoCategoria, $entity){
                $q->where('torneo_categoria_id', $TorneoCategoria->id);
                if($entity != null){ $q->whereNotIn('jugador_id', $entity->torneoJugador->pluck('jugador_id')->toArray()); }
            })->pluck('jugador_id')->toArray();

            $GruposNoDisponibles = TorneoGrupo::where(function ($q) use ($TorneoCategoria, $entity){
                $q->where('torneo_categoria_id', $TorneoCategoria->id);
                if($entity != null){ $q->where('id', '!=', $entity->id); }
            })->pluck('grupo_id')->toArray();

            $Grupos = Grupo::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
                ->whereNotIn('id', $GruposNoDisponibles)->orderBy('nombre', 'asc')->get();

            $Jugadores = Jugador::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
                ->where('categoria_id', $TorneoCategoria->categoria_id)
                ->whereNotIn('id', $JugadoresNoDisponibles)->orderBy('nombres', 'asc')
                ->get();
        }

        return view('auth'.'.'.$this->viewName.'.ajax.grupo.partialView', ['Model' => $entity, 'Grupos' => $Grupos, 'Jugadores' => $Jugadores, 'TorneoCategoria' => $TorneoCategoria, 'ViewName' => ucfirst($this->viewName)]);
    }*/

    /*public function grupoStore(Request $request)
    {
        $entity = null;

        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try {

            DB::beginTransaction();

            $Validator = Validator::make($request->all(), [
                'grupo_id' => 'required',
                'jugador_id' => 'required|array'
            ]);

            if (!$Validator->fails())
            {
                if($request->id != 0)
                {
                    $entity = TorneoGrupo::where('id', $request->id)->where('torneo_categoria_id', $request->torneo_categoria_id)
                        ->whereHas('torneoCategoria.torneo', function ($q){
                            $q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);
                        })->first();

                    if($entity != null)
                    {
                        $request->merge(['user_update_id' =>  Auth::guard('web')->user()->id]);
                        $entity->update($request->all());
                        $entity->torneoJugador()->whereNotIn('jugador_id', $request->jugador_id)
                        ->update(['user_update_id' =>  Auth::guard('web')->user()->id, 'deleted_at' => Carbon::now()]);
                    }

                }else{
                    $request->merge(['user_create_id' =>  Auth::guard('web')->user()->id]);
                    $entity = TorneoGrupo::create($request->all());
                }

                if($entity != null)
                {
                    $jugadores = [];
                    foreach ($request->jugador_id as $q){
                        if(!in_array($q, $entity->torneoJugador->pluck('jugador_id')->toArray())){
                            $jugadores[] = ['torneo_grupo_id' => $entity->id, 'jugador_id' => $q, 'user_create_id' => Auth::guard('web')->user()->id, 'created_at' => Carbon::now()];
                        }
                    }
                    if(count($jugadores) > 0 ) TorneoJugador::insert($jugadores);
                }

                DB::commit();

                $Result->Success = true;
            }

            $Result->Errors = $Validator->errors();

        }catch (\Exception $e){
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }
        return response()->json($Result);
    }*/

    public function grupoDelete(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null];

        try {

            DB::beginTransaction();

            $TorneoCategoria = TorneoCategoria::where('torneo_id', $request->torneo_id)
            ->where('id', $request->torneo_categoria_id)
            ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})->first();

            if($TorneoCategoria != null)
            {
                TorneoGrupo::where('torneo_categoria_id', $TorneoCategoria->id)->where('torneo_id', $TorneoCategoria->torneo_id)->delete();
                Partido::where('torneo_categoria_id', $TorneoCategoria->id)->where('torneo_id', $TorneoCategoria->torneo_id)->delete();

                DB::commit();
                $Result->Success = true;
            }else{
                $Result->Message = "Las llaves generadas que intenta eliminar, ya no se encuentra disponible";
            }

        }catch (\Exception $e){
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }

        return response()->json($Result);

        /*$Result = (object)['Success' => false, 'Message' => null];

        try {

            $entity = TorneoGrupo::where('id', $request->id)
                ->where('torneo_categoria_id', $request->torneo_categoria_id)
                ->whereHas('torneoCategoria.torneo', function ($q){
                    $q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);
                })->first();

            $entity->user_update_id = Auth::guard('web')->user()->id;
            if($entity->save()){
                if ($entity->delete()) $Result->Success = true;
            }
        }catch (\Exception $e){
            $Result->Message = $e->getMessage();
        }

        return response()->json($Result);*/
    }

    public function grupoPartidoPartialView($id, $torneo_categoria_id, $torneo_grupo_id)
    {
        $entity = null;

        $TorneoCategoria = TorneoCategoria::where('id', $torneo_categoria_id)->whereHas('torneo', function ($q){
            $q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id); })
            ->first();

        if($TorneoCategoria != null)
        {
            if($id != 0)
            {

            }
        }

        return view('auth'.'.'.$this->viewName.'.ajax.partido.partialView', ['Model' => $entity, 'torneo_grupo_id' => $torneo_grupo_id, 'TorneoCategoria' => $TorneoCategoria, 'ViewName' => ucfirst($this->viewName)]);
    }

    public function grupoPartidoStore(Request $request)
    {
        $entity = null;

        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try {

            DB::beginTransaction();

            $request->merge([
                'comunidad_id', Auth::guard('web')->user()->comunidad_id,
                'fecha_actual' => Carbon::now()->toDateString(),
            ]);

            $Validator = Validator::make($request->all(), [
                'torneo_grupo_id' => 'required',
                'fecha_inicio' => 'required|date|date_format:Y-m-d|after_or_equal:fecha_actual',
                'fecha_final' => 'required|date|date_format:Y-m-d|after_or_equal:fecha_inicio',
                'jugador_uno_id' => 'required',
                'jugador_dos_id' => 'required'
            ]);

            if (!$Validator->fails())
            {
                if($request->id != 0)
                {
                    $entity = Partido::where('id', $request->id)->where('torneo_grupo_id', $request->torneo_grupo_id)
                        ->whereHas('torneoGrupo.torneoCategoria.torneo', function ($q){
                            $q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);
                        })->first();

                    if($entity != null)
                    {
                        $request->merge(['user_update_id' =>  Auth::guard('web')->user()->id]);
                        $entity->update($request->all());
                    }

                }else{
                    $request->merge(['user_create_id' =>  Auth::guard('web')->user()->id, 'estado_id' => App::$ESTADO_PENDIENTE]);
                    Partido::create($request->all());
                }

                DB::commit();

                $Result->Success = true;
            }

            $Result->Errors = $Validator->errors();

        }catch (\Exception $e){
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }

        return response()->json($Result);
    }

    public function partidoStore(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try {

            DB::beginTransaction();

            //$request->merge(['fecha_actual' => Carbon::now()->toDateString()]);

            $request->merge([
                'doubleWo' => trim($request->resultado) == "-",
                'fase_inicial' => filter_var($request->fase_inicial, FILTER_VALIDATE_BOOLEAN)
            ]);

            $Validator = Validator::make($request->all(), [
                'fecha_inicio' => 'required|date|date_format:Y-m-d',
                'fecha_final' => 'required|date|date_format:Y-m-d|after_or_equal:fecha_inicio',
                'resultado' => 'required',
                'jugador_local_id' => $request->doubleWo ? 'nullable' : 'required',
                'jugador_local_set' => 'required|numeric',
                'jugador_local_juego' => 'required|numeric',
                'jugador_rival_id' => $request->doubleWo ? 'nullable' : 'required',
                'jugador_rival_set' => 'required|numeric',
                'jugador_rival_juego' => 'required|numeric'
            ], [
                'jugador_local_id.required' => 'El jugador local es obligatorio.',
                'jugador_rival_id.required' => 'El jugador rival es obligatorio.'
            ]);

            if (!$Validator->fails())
            {
                if(!$request->doubleWo && $request->jugador_local_id == $request->jugador_rival_id) {
                    $Result->Message = "El jugador ganador no puede ser el mismo al jugador rival";
                }else if(!$request->doubleWo && $request->jugador_rival_set > $request->jugador_local_set) {
                    $Result->Message = "El jugador ganador no puede tener menor sets que el jugador rival";
                }else{

                    $TorneoCategoria = TorneoCategoria::where('id', $request->torneo_categoria_id)
                    ->where('torneo_id', $request->torneo_id)->whereHas('torneo', function ($q){
                        $q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id); })
                    ->first();

                    $Partido = Partido::where('torneo_categoria_id', $request->torneo_categoria_id)
                    ->where('torneo_id', $request->torneo_id)->where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
                    ->where('id', $request->partido_id)->first();

                    if($Partido != null)
                    {
                      $Partido->multiple = $TorneoCategoria->multiple;
                      $Partido->jugador_ganador_uno_id = $request->doubleWo ? null : ($TorneoCategoria->multiple ? explode("-",$request->jugador_local_id)[0] : $request->jugador_local_id);
                      $Partido->jugador_ganador_dos_id  = $request->doubleWo ? null : ($TorneoCategoria->multiple ? explode("-",$request->jugador_local_id)[1] : null);
                      $Partido->jugador_local_set = $request->jugador_local_set;
                      $Partido->jugador_local_juego = $request->jugador_local_juego;
                      $Partido->jugador_rival_set = $request->jugador_rival_set;
                      $Partido->jugador_rival_juego = $request->jugador_rival_juego;
                      $Partido->fecha_inicio = $request->fecha_inicio;
                      $Partido->fecha_final = $request->fecha_final;
                      $Partido->resultado = $request->resultado;
                      $Partido->estado_id = App::$ESTADO_FINALIZADO;

                      if($Partido->save())
                      {
                          DB::commit();
                          $Result->Success = true;

                          if(filter_var($request->fase_inicial, FILTER_VALIDATE_BOOLEAN))
                          {
                              if(Partido::where('torneo_categoria_id', $TorneoCategoria->id)->whereNotNull('fase')->count() > 0)
                              {
                                  $Result->Message = "Se ha realizado la modificación del partido seleccionado, por favor valide que las llaves esten correctamente agrupadas.";
                              }
                          }

                      }else{
                          $Result->Message = "Algo salió mal, hubo un error al guardar.";
                      }

                    }else{
                        $Result->Message = "El partido que intenta modificar no se encuentra disponible";
                    }
                }
            }

            $Result->Errors = $Validator->errors();

        }catch (\Exception $e)
        {
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }

        return response()->json($Result);
    }

    public function partidoReset(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try {

            DB::beginTransaction();

            $Partido = Partido::where('torneo_id', $request->torneo_id)
            ->where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
            ->where('id', $request->partido_id)->first();

            if($Partido != null)
            {
                $Partido->jugador_ganador_uno_id = null;
                $Partido->jugador_ganador_dos_id  = null;
                $Partido->jugador_local_set = null;
                $Partido->jugador_local_juego = null;
                $Partido->jugador_rival_set = null;
                $Partido->jugador_rival_juego = null;
                $Partido->resultado = null;
                $Partido->estado_id = App::$ESTADO_PENDIENTE;
                if($Partido->save())
                {
                /*Eliminar la fase siguiente*/

                 if($Partido->fase != null)
                    {
                        $PartidoNext = Partido::where('comunidad_id', $Partido->comunidad_id)->where('torneo_categoria_id', $Partido->torneo_categoria_id)
                        ->where('torneo_id', $Partido->torneo_id)->where('fase', ($Partido->fase/2))->where('estado_id', App::$ESTADO_PENDIENTE)
                        ->where(function ($q) use ($Partido){
                            $q->where('jugador_local_uno_id', $Partido->jugador_local_uno_id)->orWhere('jugador_local_dos_id', $Partido->jugador_local_uno_id)
                            ->orWhere('jugador_rival_uno_id', $Partido->jugador_local_uno_id)->orWhere('jugador_rival_uno_id', $Partido->jugador_local_uno_id)
                            ->orWhere('jugador_local_uno_id', $Partido->jugador_rival_uno_id)->orWhere('jugador_local_dos_id', $Partido->jugador_rival_uno_id)
                            ->orWhere('jugador_rival_uno_id', $Partido->jugador_rival_uno_id)->orWhere('jugador_rival_uno_id', $Partido->jugador_rival_uno_id);
                        })->first();

                        if($PartidoNext != null)
                        {
                            if(in_array($PartidoNext->jugador_local_uno_id, [$Partido->jugador_local_uno_id, $Partido->jugador_rival_uno_id]))
                            {
                                Partido::where('id', $PartidoNext->id)->update(['jugador_local_uno_id' => null, 'jugador_local_dos_id' => null]);

                            }else if(in_array($PartidoNext->jugador_rival_uno_id, [$Partido->jugador_local_uno_id, $Partido->jugador_rival_uno_id]))
                            {
                                Partido::where('id', $PartidoNext->id)->update(['jugador_rival_uno_id' => null, 'jugador_rival_dos_id' => null]);
                            }
                        }
                    }

                    DB::commit();
                    $Result->Success = true;

                }else{
                    $Result->Message = "Algo salió mal, hubo un error al guardar.";
                }

            }else{
                $Result->Message = "El partido que intenta modificar no se encuentra disponible";
            }

        }catch (\Exception $e)
        {
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }

        return response()->json($Result);
    }

    public function partidoStoreMultiple(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => [], 'Updates' => 0,];

        try {

            DB::beginTransaction();

            $request->merge(['partidos' => json_decode($request->partidos)]);

            $Validator = Validator::make($request->all(), [
                'torneo_id' => 'required',
                'grupo_id' => 'required',
                'torneo_categoria_id' => 'required',
                'partidos' => 'required|array'
            ]);

            if (!$Validator->fails()){

                foreach ($request->partidos as $key => $q)
                {
                    $Validator = Validator::make((array)$q, [
                        'fecha_inicio' => 'required|date|date_format:Y-m-d',
                        'fecha_final' => 'required|date|date_format:Y-m-d|after_or_equal:fecha_inicio',
                        'resultado' => 'required',
                        'jugador_local_id' => 'required',
                        'jugador_local_set' => 'required|numeric',
                        'jugador_local_juego' => 'required|numeric',
                        'jugador_rival_id' => 'required',
                        'jugador_rival_set' => 'required|numeric',
                        'jugador_rival_juego' => 'required|numeric'
                    ], [
                        'jugador_local_id.required' => 'El jugador local es obligatorio.',
                        'jugador_rival_id.required' => 'El jugador rival es obligatorio.'
                    ]);

                    if (!$Validator->fails())
                    {
                        if($q->jugador_local_id == $q->jugador_rival_id) {
                            $Result->Errors[] = ['key' => ($q->players), 'Message' => 'No se pudo finalizar el partido porque : ', 'error' => [(object)["error" => "El jugador ganador no puede ser el mismo al jugador rival"]]];
                        }else if($q->jugador_rival_set > $q->jugador_local_set) {
                            $Result->Errors[] = ['key' => ($q->players), 'Message' => 'No se pudo finalizar el partido porque : ', 'error' => [(object)["error" => "El jugador ganador no puede tener menor sets que el jugador rival"]]];
                        }else {

                            $TorneoCategoria = TorneoCategoria::where('id', $q->torneo_categoria_id)
                            ->where('torneo_id', $q->torneo_id)->whereHas('torneo', function ($query) {
                                $query->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);
                            })->first();

                            $Partido = Partido::where('torneo_categoria_id', $q->torneo_categoria_id)
                            ->where('torneo_id', $q->torneo_id)->where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
                            ->where('id', $q->partido_id)->first();

                            if ($Partido != null) {
                                $Partido->multiple = $TorneoCategoria->multiple;
                                $Partido->jugador_ganador_uno_id = $TorneoCategoria->multiple ? explode("-", $q->jugador_local_id)[0] : $q->jugador_local_id;
                                $Partido->jugador_ganador_dos_id = $TorneoCategoria->multiple ? explode("-", $q->jugador_local_id)[1] : null;
                                $Partido->jugador_local_set = $q->jugador_local_set;
                                $Partido->jugador_local_juego = $q->jugador_local_juego;
                                $Partido->jugador_rival_set = $q->jugador_rival_set;
                                $Partido->jugador_rival_juego = $q->jugador_rival_juego;
                                $Partido->fecha_inicio = $q->fecha_inicio;
                                $Partido->fecha_final = $q->fecha_final;
                                $Partido->resultado = $q->resultado;
                                $Partido->estado_id = App::$ESTADO_FINALIZADO;
                                if ($Partido->save()) {
                                    DB::commit();
                                    $Result->Updates++;
                                } else {
                                    $Result->Errors[] = ['key' => ($q->players), 'Message' => 'No se pudo finalizar el partido porque : ', 'error' => [(object)["error" => "Algo salió mal, hubo un error al guardar."]]];
                                }
                            } else {
                                $Result->Errors[] = ['key' => ($q->players), 'Message' => 'No se pudo finalizar el partido porque : ', 'error' => [(object)["error" => "El partido que intenta modificar no se encuentra disponible"]]];
                            }
                        }
                    }else{
                        $errors = [];
                        foreach ($Validator->errors()->messages() as $messages) {foreach ($messages as $error){ $errors[] = ['error' => $error];}}
                        $Result->Errors[] = ['key' => ($q->players), 'Message' => 'No se pudo finalizar el partido porque : ', 'error' => $errors];
                    }
                }
            }else{
                $Result->Errors = $Validator->errors();
            }
        }catch (\Exception $e)
        {
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }

        $Result->Success = count($Result->Errors) == 0;

        return response()->json($Result);
    }


    /* TORNEO RANKING */
    public function ranking($torneo, $torneo_categoria_id, $landing=false)
    {
        $PuntuacionesResult = [];

        $ComunidadId = $landing ? Comunidad::where('principal', true)->first()->id : Auth::guard('web')->user()->comunidad_id;

        $TorneoCategoria = TorneoCategoria::where('id', $torneo_categoria_id)->where('torneo_id', $torneo)
        ->whereHas('torneo', function ($q) use ($ComunidadId){ $q->where('comunidad_id', $ComunidadId);})->first();

        if($TorneoCategoria != null)
        {
            $Ranking = Ranking::where('torneo_id', $TorneoCategoria->torneo_id)->where('torneo_categoria_id', $TorneoCategoria->id)
            ->where('comunidad_id', $ComunidadId)->first();

            if($Ranking != null)
            {
                if($Ranking->detalles != null && count($Ranking->detalles) > 0)
                {
                    foreach ($Ranking->detalles as $q)
                    {
                        $PuntuacionesResult[] = [
                            'jugador_simple_id' => $q->jugadorSimple->id,
                            'jugador_dupla_id' => $TorneoCategoria->multiple ? $q->jugadorDupla->id : null,
                            'nombres' => $TorneoCategoria->multiple ? ($q->jugadorSimple->nombre_completo." + ".$q->jugadorDupla->nombre_completo) : $q->jugadorSimple->nombre_completo,
                            'puntos' => $q->puntos
                        ];
                    }
                }

                if(count($PuntuacionesResult) > 0){
                    $PuntuacionesResult = App::multiPropertySort(collect($PuntuacionesResult), [['column' => 'puntos', 'order' => 'desc']])->toArray();
                }

            }else{

                if($TorneoCategoria->first_final)
                {
                    $Jugadores = TorneoJugador::where('torneo_categoria_id', $TorneoCategoria->id)->get()
                    ->map(function ($q) use ($TorneoCategoria){
                        return [
                            'jugador_simple_id' => $q->jugadorSimple->id,
                            'jugador_dupla_id' => $TorneoCategoria->multiple ? $q->jugadorDupla->id : null,
                            'nombres' => $TorneoCategoria->multiple ? ($q->jugadorSimple->nombre_completo." + ".$q->jugadorDupla->nombre_completo) : $q->jugadorSimple->nombre_completo
                        ];
                    });

                }else{
                    $Jugadores = $TorneoCategoria->torneo->torneoGrupos()->where('torneo_categoria_id', $TorneoCategoria->id)
                    ->get()->map(function ($q) use ($TorneoCategoria){
                        return [
                            'jugador_simple_id' => $q->jugadorSimple->id,
                            'jugador_dupla_id' => $TorneoCategoria->multiple ? $q->jugadorDupla->id : null,
                            'nombres' => $TorneoCategoria->multiple ? ($q->jugadorSimple->nombre_completo." + ".$q->jugadorDupla->nombre_completo) : $q->jugadorSimple->nombre_completo
                        ];
                    });
                }

                $TablePositions = [];

                foreach ($Jugadores as $q2)
                {
                    if($TorneoCategoria->multiple)
                    {
                        $PartidosComoLocal = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                        ->whereNotNull('fase')
                        ->where('jugador_local_uno_id', $q2['jugador_simple_id'])
                        ->where('jugador_local_dos_id', $q2['jugador_dupla_id'])->get());

                        $PartidosComoRival = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                        ->whereNotNull('fase')
                        ->where('jugador_rival_uno_id', $q2['jugador_simple_id'])
                        ->where('jugador_rival_dos_id', $q2['jugador_dupla_id'])->get());

                        $PartidosJugados = 0; $SetsGanados = 0; $SetsPerdidos = 0; $GamesGanados = 0; $GamesPerdidos = 0; $Puntos = 0;

                        foreach ($PartidosComoLocal as $p)
                        {
                            if($p->estado_id == App::$ESTADO_FINALIZADO) $PartidosJugados += 1;

                            if($p->jugador_ganador_uno_id == $q2['jugador_simple_id'])
                            {   //NO Rival
                                $SetsGanados += $p->jugador_local_set; $SetsPerdidos += $p->jugador_rival_set;
                                $GamesGanados += $p->jugador_local_juego; $GamesPerdidos += $p->jugador_rival_juego;
                                $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                            }else{
                                //Rival
                                $SetsGanados += $p->jugador_rival_set; $SetsPerdidos += $p->jugador_local_set;
                                $GamesGanados += $p->jugador_rival_juego; $GamesPerdidos += $p->jugador_local_juego;
                                $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                            }
                        }

                        foreach ($PartidosComoRival as $p)
                        {
                            if($p->estado_id == App::$ESTADO_FINALIZADO) $PartidosJugados += 1;

                            if($p->jugador_ganador_uno_id == $q2['jugador_simple_id'])
                            {   //NO Rival
                                $SetsGanados += $p->jugador_local_set; $SetsPerdidos += $p->jugador_rival_set;
                                $GamesGanados += $p->jugador_local_juego; $GamesPerdidos += $p->jugador_rival_juego;
                                $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                            }else{
                                //Rival
                                $SetsGanados += $p->jugador_rival_set; $SetsPerdidos += $p->jugador_local_set;
                                $GamesGanados += $p->jugador_rival_juego; $GamesPerdidos += $p->jugador_local_juego;
                                $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                            }
                        }

                        $SetsDiferencias = $SetsGanados - $SetsPerdidos;

                        $GamesDiferencias = $GamesGanados - $GamesPerdidos;

                        $Puntos = $Puntos * $TorneoCategoria->torneo->valor_set;

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
                            'puntos' => $Puntos
                        ];

                    }else{

                        $PartidosComoLocal = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                        ->whereNotNull('fase')
                        ->where('jugador_local_uno_id', $q2['jugador_simple_id'])->get());

                        $PartidosComoRival = collect($TorneoCategoria->torneo->partidos()->where('torneo_categoria_id', $TorneoCategoria->id)
                        ->whereNotNull('fase')
                        ->where('jugador_rival_uno_id', $q2['jugador_simple_id'])->get());

                        $PartidosJugados = 0; $SetsGanados = 0; $SetsPerdidos = 0; $GamesGanados = 0; $GamesPerdidos = 0; $Puntos = 0;

                        foreach ($PartidosComoLocal as $p)
                        {
                            if($p->estado_id == App::$ESTADO_FINALIZADO) $PartidosJugados += 1;

                            if($p->jugador_ganador_uno_id == $q2['jugador_simple_id'])
                            {   //NO Rival
                                $SetsGanados += $p->jugador_local_set; $SetsPerdidos += $p->jugador_rival_set;
                                $GamesGanados += $p->jugador_local_juego; $GamesPerdidos += $p->jugador_rival_juego;
                                $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                            }else{
                                //Rival
                                $SetsGanados += $p->jugador_rival_set; $SetsPerdidos += $p->jugador_local_set;
                                $GamesGanados += $p->jugador_rival_juego; $GamesPerdidos += $p->jugador_local_juego;
                                $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                            }
                        }

                        foreach ($PartidosComoRival as $p)
                        {
                            if($p->estado_id == App::$ESTADO_FINALIZADO) $PartidosJugados += 1;

                            if($p->jugador_ganador_uno_id == $q2['jugador_simple_id'])
                            {   //NO Rival
                                $SetsGanados += $p->jugador_local_set; $SetsPerdidos += $p->jugador_rival_set;
                                $GamesGanados += $p->jugador_local_juego; $GamesPerdidos += $p->jugador_rival_juego;
                                $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : ($p->jugador_rival_set <= 0 ? 5 : 4));
                            }else{
                                //Rival
                                $SetsGanados += $p->jugador_rival_set; $SetsPerdidos += $p->jugador_local_set;
                                $GamesGanados += $p->jugador_rival_juego; $GamesPerdidos += $p->jugador_local_juego;
                                $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
                            }
                        }

                        $SetsDiferencias = $SetsGanados - $SetsPerdidos;

                        $GamesDiferencias = $GamesGanados - $GamesPerdidos;

                        $Puntos = $Puntos * $TorneoCategoria->torneo->valor_set;

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
                            'puntos' => $Puntos
                        ];

                    }
                }

                $TablePositions = App::multiPropertySort(collect($TablePositions), [
                    ['column' => 'partidosJugados', 'order' => 'desc'], ['column' => 'puntos', 'order' => 'desc'], ['column' => 'setsDiferencias', 'order' => 'desc'], ['column' => 'gamesDiferencias', 'order' => 'desc'], ['column' => 'setsGanados', 'order' => 'desc'], ['column' => 'gamesGanados', 'order' => 'desc']])->toArray();
                $Puntuaciones = Puntuacion::where('comunidad_id', $ComunidadId)->get()->toArray();

                if(count($TablePositions) > count($Puntuaciones)){
                    array_splice($TablePositions, -(count($TablePositions) - count($Puntuaciones)));
                }

                $i = 0;

                foreach ($TablePositions as $key => $q)
                {
                    $ranking = $TablePositions[$key];
                    $PuntuacionesResult[] = ['jugador_simple_id' => $ranking['jugador_simple_id'], 'jugador_dupla_id' => $ranking['jugador_dupla_id'], 'nombres' => $ranking['nombres'], 'puntos' => $Puntuaciones[$i]['puntos']];
                    $i++;
                }
            }
        }

        return view('auth'.'.'.$this->viewName.'.ajax.ranking.index', ['TorneoCategoria' => $TorneoCategoria, 'TablePositions' => collect($PuntuacionesResult), 'ViewName' => ucfirst($this->viewName)]);
    }


    public function exportMapaJson(Request $request)
    {
        $TorneoCategoria = TorneoCategoria::where('id', $request->categoria)->where('torneo_id', $request->torneo)
        ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
        ->first();

        $content = "";

        if($TorneoCategoria != null && $TorneoCategoria->torneo != null)
        {
            $model = (object)[
                'titulo' => 'La Confraternidad del Tenis',
                'torneo' => $TorneoCategoria->torneo->nombre,
                'formato' => $TorneoCategoria->torneo->formato != null ? $TorneoCategoria->torneo->formato->nombre : null,
                'ronda' => $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->whereNotNull('fase')->first()->fase * 2,
                'multiple' => $TorneoCategoria->multiple ? true : false,
                'damas' => str_contains(strtolower(($TorneoCategoria->multiple && ($TorneoCategoria->categoriaSimple->id !== $TorneoCategoria->categoriaDupla->id) ? ($TorneoCategoria->categoriaSimple->nombre." + ".$TorneoCategoria->categoriaDupla->nombre) : ($TorneoCategoria->categoriaSimple->nombre)."".($TorneoCategoria->multiple ? " (Doble) " : ""))), 'damas') ? true : false,
                'categoria' => $TorneoCategoria->multiple && ($TorneoCategoria->categoriaSimple->id !== $TorneoCategoria->categoriaDupla->id) ? ($TorneoCategoria->categoriaSimple->nombre." + ".$TorneoCategoria->categoriaDupla->nombre) : ($TorneoCategoria->categoriaSimple->nombre)."".($TorneoCategoria->multiple ? " (Doble) " : ""),
                'fecha_inicio' => Carbon::parse($TorneoCategoria->torneo->fecha_inicio)->format('Y-m-d'),
                'fecha_final' => Carbon::parse($TorneoCategoria->torneo->fecha_final)->format('Y-m-d'),
                'ganador' => $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first() != null ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first()->multiple ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first()->jugadorGanadorUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first()->jugadorGanadorDos->nombre_completo) : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first()->jugadorGanadorUno->nombre_completo : null,
                'llaves' => []
            ];

            if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->whereNotNull('fase')) > 0)
            {
                if($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 16)
                {
                    $model->llaves['ronda32'] = [];
                    $model->llaves['ronda32']['bloque_uno'] = [];
                    $model->llaves['ronda32']['bloque_dos'] = [];
                    $model->llaves['ronda32']['bloque_tres'] = [];
                    $model->llaves['ronda32']['bloque_cuatro'] = [];

                    foreach($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 16)->where('bloque', 1) as $q)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-")) : ($q->buy_all ? "BYE" : "-");
                        $bloque['jugador_rival'] = $q->jugadorRivalUno != null ? (!$TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-"))) : ($q->buy ? "BYE" : "-");
                        $bloque['resultado'] = $q->resultado;
                        $model->llaves['ronda32']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }

                    foreach($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 16)->where('bloque', 2) as $q)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-")) : ($q->buy_all ? "BYE" : "-");
                        $bloque['jugador_rival'] = $q->jugadorRivalUno != null ? (!$TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-"))) : ($q->buy ? "BYE" : "-");
                        $bloque['resultado'] = $q->resultado;
                        $model->llaves['ronda32']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }

                    foreach($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 16)->where('bloque', 3) as $q)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-")) : ($q->buy_all ? "BYE" : "-");
                        $bloque['jugador_rival'] = $q->jugadorRivalUno != null ? (!$TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-"))) : ($q->buy ? "BYE" : "-");
                        $bloque['resultado'] = $q->resultado;
                        $model->llaves['ronda32']['bloque_tres'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }

                    foreach($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 16)->where('bloque', 4) as $q)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-")) : ($q->buy_all ? "BYE" : "-");
                        $bloque['jugador_rival'] = $q->jugadorRivalUno != null ? (!$TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-"))) : ($q->buy ? "BYE" : "-");
                        $bloque['resultado'] = $q->resultado;
                        $model->llaves['ronda32']['bloque_cuatro'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }

                    $model->llaves['octavos'] = [];
                    $model->llaves['octavos']['bloque_uno'] = [];
                    $model->llaves['octavos']['bloque_dos'] = [];
                    $model->llaves['octavos']['bloque_tres'] = [];
                    $model->llaves['octavos']['bloque_cuatro'] = [];

                    if($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first() != null){
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->resultado ;
                        $model->llaves['octavos']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                        $model->llaves['octavos']['bloque_uno'] = null;
                    }

                    if($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first() != null){
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->resultado ;
                        $model->llaves['octavos']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                        $model->llaves['octavos']['bloque_uno'] = null;
                    }

                    if($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first() != null){
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->resultado ;
                        $model->llaves['octavos']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                        $model->llaves['octavos']['bloque_dos'] = null;
                    }

                    if($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first() != null){
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->resultado ;
                        $model->llaves['octavos']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                        $model->llaves['octavos']['bloque_dos'] = null;
                    }

                    if($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first() != null){
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->resultado ;
                        $model->llaves['octavos']['bloque_tres'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                        $model->llaves['octavos']['bloque_tres'] = null;
                    }

                    if($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first() != null){
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->resultado ;
                        $model->llaves['octavos']['bloque_tres'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                        $model->llaves['octavos']['bloque_tres'] = null;
                    }

                    if($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first() != null){
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->resultado ;
                        $model->llaves['octavos']['bloque_cuatro'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                        $model->llaves['octavos']['bloque_cuatro'] = null;
                    }

                    if($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first() != null){
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->resultado ;
                        $model->llaves['octavos']['bloque_cuatro'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                        $model->llaves['octavos']['bloque_cuatro'] = null;
                    }

                    $model->llaves['cuartos'] = [];
                    $model->llaves['cuartos']['bloque_uno'] = [];
                    $model->llaves['cuartos']['bloque_dos'] = [];
                    $model->llaves['cuartos']['bloque_tres'] = [];
                    $model->llaves['cuartos']['bloque_cuatro'] = [];

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->resultado;
                        $model->llaves['cuartos']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                        $model->llaves['cuartos']['bloque_uno'] = null;
                    }

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->resultado;
                        $model->llaves['cuartos']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                        $model->llaves['cuartos']['bloque_dos'] = null;
                    }

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->resultado;
                        $model->llaves['cuartos']['bloque_tres'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                        $model->llaves['cuartos']['bloque_tres'] = null;
                    }

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->resultado;
                        $model->llaves['cuartos']['bloque_cuatro'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                        $model->llaves['cuartos']['bloque_cuatro'] = null;
                    }

                    $model->llaves['semifinal'] = [];
                    $model->llaves['semifinal']['bloque_uno'] = [];
                    $model->llaves['semifinal']['bloque_dos'] = [];

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->resultado;
                        $model->llaves['semifinal']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                        $model->llaves['semifinal']['bloque_uno'] = null;
                    }

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->resultado;
                        $model->llaves['semifinal']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                        $model->llaves['semifinal']['bloque_dos'] = null;
                    }

                    $model->llaves['final'] = [];

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->resultado;
                        $model->llaves['final'] = (object)$bloque;
                    }else{
                        $model->llaves['final'] = null;
                    }

                }else if($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 8)
                {
                    $model->llaves['octavos'] = [];
                    $model->llaves['octavos']['bloque_uno'] = [];
                    $model->llaves['octavos']['bloque_dos'] = [];
                    $model->llaves['octavos']['bloque_tres'] = [];
                    $model->llaves['octavos']['bloque_cuatro'] = [];

                    foreach($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1) as $q)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-")) : ($q->buy_all ? "BYE" : "-");
                        $bloque['jugador_rival'] = $q->jugadorRivalUno != null ? (!$TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-"))) : ($q->buy ? "BYE" : "-");
                        $bloque['resultado'] = $q->resultado;
                        $model->llaves['octavos']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }

                    foreach($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2) as $q)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-")) : ($q->buy_all ? "BYE" : "-");
                        $bloque['jugador_rival'] = $q->jugadorRivalUno != null ? (!$TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-"))) : ($q->buy ? "BYE" : "-");
                        $bloque['resultado'] = $q->resultado;
                        $model->llaves['octavos']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }

                    foreach($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3) as $q)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-")) : ($q->buy_all ? "BYE" : "-");
                        $bloque['jugador_rival'] = $q->jugadorRivalUno != null ? (!$TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-"))) : ($q->buy ? "BYE" : "-");
                        $bloque['resultado'] = $q->resultado;
                        $model->llaves['octavos']['bloque_tres'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }

                    foreach($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4) as $q)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-")) : ($q->buy_all ? "BYE" : "-");
                        $bloque['jugador_rival'] = $q->jugadorRivalUno != null ? (!$TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-"))) : ($q->buy ? "BYE" : "-");
                        $bloque['resultado'] = $q->resultado;
                        $model->llaves['octavos']['bloque_cuatro'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }

                    $model->llaves['cuartos'] = [];
                    $model->llaves['cuartos']['bloque_uno'] = [];
                    $model->llaves['cuartos']['bloque_dos'] = [];
                    $model->llaves['cuartos']['bloque_tres'] = [];
                    $model->llaves['cuartos']['bloque_cuatro'] = [];

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->resultado;
                        $model->llaves['cuartos']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                        $model->llaves['cuartos']['bloque_uno'] = null;
                    }

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->resultado;
                        $model->llaves['cuartos']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                        $model->llaves['cuartos']['bloque_dos'] = null;
                    }

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->resultado;
                        $model->llaves['cuartos']['bloque_tres'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                        $model->llaves['cuartos']['bloque_tres'] = null;
                    }

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->resultado;
                        $model->llaves['cuartos']['bloque_cuatro'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                        $model->llaves['cuartos']['bloque_cuatro'] = null;
                    }

                    $model->llaves['semifinal'] = [];
                    $model->llaves['semifinal']['bloque_uno'] = [];
                    $model->llaves['semifinal']['bloque_dos'] = [];

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->resultado;
                        $model->llaves['semifinal']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                        $model->llaves['semifinal']['bloque_uno'] = null;
                    }

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->resultado;
                        $model->llaves['semifinal']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                        $model->llaves['semifinal']['bloque_dos'] = null;
                    }

                    $model->llaves['final'] = [];

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->resultado;
                        $model->llaves['final'] = (object)$bloque;
                    }else{
                        $model->llaves['final'] = null;
                    }

                }else if($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 4)
                {
                    $model->llaves['cuartos'] = [];
                    $model->llaves['cuartos']['bloque_uno'] = [];
                    $model->llaves['cuartos']['bloque_dos'] = [];
                    $model->llaves['cuartos']['bloque_tres'] = [];
                    $model->llaves['cuartos']['bloque_cuatro'] = [];

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->resultado;
                        $model->llaves['cuartos']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                        $model->llaves['cuartos']['bloque_uno'] = null;
                    }

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->resultado;
                        $model->llaves['cuartos']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                        $model->llaves['cuartos']['bloque_dos'] = null;
                    }

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->resultado;
                        $model->llaves['cuartos']['bloque_tres'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                        $model->llaves['cuartos']['bloque_tres'] = null;
                    }

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->resultado;
                        $model->llaves['cuartos']['bloque_cuatro'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                        $model->llaves['cuartos']['bloque_cuatro'] = null;
                    }

                    $model->llaves['semifinal'] = [];
                    $model->llaves['semifinal']['bloque_uno'] = [];
                    $model->llaves['semifinal']['bloque_dos'] = [];

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->resultado;
                        $model->llaves['semifinal']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                        $model->llaves['semifinal']['bloque_uno'] = null;
                    }

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->resultado;
                        $model->llaves['semifinal']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                        $model->llaves['semifinal']['bloque_dos'] = null;
                    }

                    $model->llaves['final'] = [];

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->resultado;
                        $model->llaves['final'] = (object)$bloque;
                    }else{
                        $model->llaves['final'] = null;
                    }

                }else if($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 2)
                {
                    $model->llaves['semifinal'] = [];
                    $model->llaves['semifinal']['bloque_uno'] = [];
                    $model->llaves['semifinal']['bloque_dos'] = [];

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->resultado;
                        $model->llaves['semifinal']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                        $model->llaves['semifinal']['bloque_uno'] = null;
                    }

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->resultado;
                        $model->llaves['semifinal']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                        $model->llaves['semifinal']['bloque_dos'] = null;
                    }

                    $model->llaves['final'] = [];

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->resultado;
                        $model->llaves['final'] = (object)$bloque;
                    }else{
                        $model->llaves['final'] = null;
                    }
                }
            }
            $content = json_encode($model);
        }

        Storage::disk('public')->put('public/uploads/keys/json.txt', $content);

        return redirect('https://jaggernog.com/?json='.env('APP_URL').'/storage/public/uploads/keys/json.txt');

        /*
        // file name that will be used in the download
        $fileName = "mapa-final.txt";
        // use headers in order to generate the download
        $headers = [
            'Content-type' => 'text/plain',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $fileName),
            //'Content-Length' => sizeof($content)
        ];
        // make a response, with the content, a 200 response code and the headers
        return response($content)->withHeaders($headers);*/
    }
}
