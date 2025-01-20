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
use Illuminate\Database\Eloquent\Model;
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
                    $fechaInicio = $entity->fecha_inicio;

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

                    //Modificar Fechas
                    if($fechaInicio != $request->fecha_inicio)
                    {
                        $TorneoCategorias = TorneoCategoria::where('torneo_id', $entity->id)->get();
                        if(count($TorneoCategorias) > 0)
                        {
                            foreach ($TorneoCategorias as $q)
                            {
                                $TorneoGrupos = TorneoGrupo::where('torneo_id', $entity->id)->where('torneo_categoria_id', $q->id)
                                    ->select(['nombre_grupo', 'grupo_id'])->groupBy(['nombre_grupo', 'grupo_id'])->orderBy(DB::raw('LENGTH(nombre_grupo)'))->orderBy('nombre_grupo')->get();

                                if(count($TorneoGrupos) > 0)
                                {
                                    foreach ($TorneoGrupos as $q2)
                                    {
                                        $fechaInicioPartido = Carbon::parse($entity->fecha_inicio);
                                        $fechaFinalPartido = Carbon::parse($entity->fecha_inicio)->addDay(6);

                                        $Partidos = Partido::where('torneo_categoria_id', $q->id)->whereNull('fase')->where('grupo_id', $q2->grupo_id)->orderBy('id', 'asc')->get();

                                        $Partidos->get(0)->update(['fecha_inicio' => $fechaInicioPartido, 'fecha_final' => $fechaFinalPartido]);
                                        $Partidos->get(1)->update(['fecha_inicio' => $fechaInicioPartido, 'fecha_final' => $fechaFinalPartido]);

                                        $fechaInicioPartido = Carbon::parse($fechaFinalPartido)->addDays(1);
                                        $fechaFinalPartido = Carbon::parse($fechaInicioPartido)->addDay(6);

                                        $Partidos->get(2)->update(['fecha_inicio' => $fechaInicioPartido, 'fecha_final' => $fechaFinalPartido]);
                                        $Partidos->get(3)->update(['fecha_inicio' => $fechaInicioPartido, 'fecha_final' => $fechaFinalPartido]);

                                        $fechaInicioPartido = Carbon::parse($fechaFinalPartido)->addDays(1);
                                        $fechaFinalPartido = Carbon::parse($fechaInicioPartido)->addDay(6);

                                        $Partidos->get(4)->update(['fecha_inicio' => $fechaInicioPartido, 'fecha_final' => $fechaFinalPartido]);
                                        $Partidos->get(5)->update(['fecha_inicio' => $fechaInicioPartido, 'fecha_final' => $fechaFinalPartido]);
                                    }
                                }
                            }
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


    public function faseFinalPlayerCuartos($torneo, $torneo_categoria_id)
    {
        $TorneoCategoria = TorneoCategoria::where('id', $torneo_categoria_id)->where('torneo_id', $torneo)
        ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})->first();

        if($TorneoCategoria != null)
        {
            $TorneoGrupos = TorneoGrupo::where('torneo_categoria_id', $TorneoCategoria->id)->select(['nombre_grupo', 'grupo_id'])->groupBy(['nombre_grupo', 'grupo_id'])
            ->orderBy(DB::raw('LENGTH(nombre_grupo)'))->orderBy('nombre_grupo')->get();

            return view('auth'.'.'.$this->viewName.'.ajax.final.jugador.partialViewCuartos', ['TorneoCategoria' => $TorneoCategoria, 'Cuartos' => $TorneoGrupos->count(), 'ViewName' => ucfirst($this->viewName)]);
        }

        return null;
    }
    public function faseFinalPlayersChanges($torneo, $torneo_categoria_id)
    {
        $TorneoCategoria = TorneoCategoria::where('id', $torneo_categoria_id)->where('torneo_id', $torneo)
            ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})->first();

        if($TorneoCategoria != null)
        {

            return view('auth'.'.'.$this->viewName.'.ajax.final.jugador.partialViewClasificado', ['TorneoCategoria' => $TorneoCategoria, 'ViewName' => ucfirst($this->viewName)]);
        }

        return null;
    }

    public function faseFinalPlayerTercerosStore(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null];

        try {

            DB::beginTransaction();
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

        

        }catch (\Exception $e)
        {
            DB::rollBack();
            $Result->Message = $e->getMessage();
        }

        return response()->json($Result);
    }


    public function faseFinalPlayerCuartosStore(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null];

        try {

            DB::beginTransaction();
            $TorneoCategoria = TorneoCategoria::where('id', $request->torneo_categoria_id)->where('torneo_id', $request->torneo_id)
            ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})->first();

      

                if($TorneoCategoria != null)
                {
                    $TorneoGrupos = TorneoGrupo::where('torneo_categoria_id', $TorneoCategoria->id)->select(['nombre_grupo', 'grupo_id'])->groupBy(['nombre_grupo', 'grupo_id'])
                    ->orderBy(DB::raw('LENGTH(nombre_grupo)'))->orderBy('nombre_grupo')->get();

                    if($TorneoGrupos->count() >= $request->jugadores_cuartos)
                    {
                        $TorneoCategoria->clasificados_cuartos = $request->jugadores_cuartos;
                        if($TorneoCategoria->save())
                        {
                            DB::commit();
                            $Result->Success = true;
                        }

            }else{
                        $Result->Message = "Por favor, ingrese una cantidad de válida, solo puede ingresar máximo ".$TorneoGrupos->count()." cuartos.";
                    }
                }else{
                    $Result->Message = "El torneo categoria que intenta modificar, ya no se encuentra disponible.";
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

            if ($TorneoCategoria->clasificados_cuartos > 0) {
                $Clasifican = 4;
            } elseif ($TorneoCategoria->clasificados_terceros > 0) {
                $Clasifican = 3;
            } else {
                $Clasifican = $TorneoCategoria->clasificados;
            }

            foreach ($TorneoGrupos as $key => $q)
            {
                //JUGADORES DEL GRUPO
                $Jugadores = $TorneoCategoria->torneo->torneoGrupos()->where('torneo_categoria_id', $TorneoCategoria->id)
                    ->where('grupo_id', $q->grupo_id)->get()->map(function ($q) use ($TorneoCategoria){
                        return [
                            'jugador_simple_id' => $q->jugadorSimple->id,
                            'jugador_dupla_id' => $TorneoCategoria->multiple ? $q->jugadorDupla->id : null,
                            'nombres' => $TorneoCategoria->multiple ? ($q->jugadorSimple->nombre_completo." + ".$q->jugadorDupla->nombre_completo) : $q->jugadorSimple->nombre_completo_temporal
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
           // Inicializar arrays para almacenar los lugares
$PrimerosLugares = [];
$SegundoLugares = [];
$TercerosLugares = [];
$CuartosLugares = [];

// Clasificar jugadores en primeros, segundos, terceros y cuartos lugares
foreach ($JugadoresClasificados as $key => $value) {
    if ($Clasifican == 1) {
        $PrimerosLugares[] = $value['Clasificados']->first();
    } elseif ($Clasifican == 2) {
                    $PrimerosLugares[] = $value['Clasificados']->first();
                    $SegundoLugares[] = $value['Clasificados']->last();
    } elseif ($Clasifican == 3) {
                    $PrimerosLugares[] = $value['Clasificados']->first();
        if (isset($value['Clasificados'][1])) {
            $SegundoLugares[] = $value['Clasificados'][1];
        }
                    $TercerosLugares[] = $value['Clasificados']->last();
    } elseif ($Clasifican == 4) {
        $PrimerosLugares[] = $value['Clasificados']->first();
        if (isset($value['Clasificados'][1])) {
            $SegundoLugares[] = $value['Clasificados'][1];
                }
        if (isset($value['Clasificados'][2])) {
            $TercerosLugares[] = $value['Clasificados'][2];
            }
        $CuartosLugares[] = $value['Clasificados']->last();
                    }
                }




// Eliminar duplicados
$PrimerosLugares = collect($PrimerosLugares)->unique('key')->values()->all();
$SegundoLugares = collect($SegundoLugares)->unique('key')->values()->all();
$TercerosLugares = collect($TercerosLugares)->unique('key')->values()->all();
$CuartosLugares = collect($CuartosLugares)->unique('key')->values()->all();


    $TercerosLugares = App::multiPropertySort(
        collect($TercerosLugares),
        [
            ['column' => 'puntos', 'order' => 'desc'],
            ['column' => 'setsDiferencias', 'order' => 'desc'],
            ['column' => 'gamesDiferencias', 'order' => 'desc'],
            ['column' => 'setsGanados', 'order' => 'desc'],
            ['column' => 'gamesGanados', 'order' => 'desc']
        ]
    )->take($TorneoCategoria->clasificados_terceros)->toArray();





    $CuartosLugares = App::multiPropertySort(
        collect($CuartosLugares),
        [
            ['column' => 'puntos', 'order' => 'desc'],
            ['column' => 'setsDiferencias', 'order' => 'desc'],
            ['column' => 'gamesDiferencias', 'order' => 'desc'],
            ['column' => 'setsGanados', 'order' => 'desc'],
            ['column' => 'gamesGanados', 'order' => 'desc']
        ]
    )->take($TorneoCategoria->clasificados_cuartos)->toArray();




// Ordenar los lugares
$PrimerosLugares = App::multiPropertySort(
    collect($PrimerosLugares),
    [
        ['column' => 'puntos', 'order' => 'desc'],
        ['column' => 'setsDiferencias', 'order' => 'desc'],
        ['column' => 'gamesDiferencias', 'order' => 'desc'],
        ['column' => 'setsGanados', 'order' => 'desc'],
        ['column' => 'gamesGanados', 'order' => 'desc']
    ]
);
$SegundoLugares = App::multiPropertySort(
    collect($SegundoLugares),
    [
        ['column' => 'puntos', 'order' => 'desc'],
        ['column' => 'setsDiferencias', 'order' => 'desc'],
        ['column' => 'gamesDiferencias', 'order' => 'desc'],
        ['column' => 'setsGanados', 'order' => 'desc'],
        ['column' => 'gamesGanados', 'order' => 'desc']
    ]
);
$TercerosLugares = App::multiPropertySort(
    collect($TercerosLugares),
    [
        ['column' => 'puntos', 'order' => 'desc'],
        ['column' => 'setsDiferencias', 'order' => 'desc'],
        ['column' => 'gamesDiferencias', 'order' => 'desc'],
        ['column' => 'setsGanados', 'order' => 'desc'],
        ['column' => 'gamesGanados', 'order' => 'desc']
    ]
);
$CuartosLugares = App::multiPropertySort(
    collect($CuartosLugares),
    [
        ['column' => 'puntos', 'order' => 'desc'],
        ['column' => 'setsDiferencias', 'order' => 'desc'],
        ['column' => 'gamesDiferencias', 'order' => 'desc'],
        ['column' => 'setsGanados', 'order' => 'desc'],
        ['column' => 'gamesGanados', 'order' => 'desc']
    ]
);

// Combinar todos los lugares
$JugadoresClasificadosMerge = collect($PrimerosLugares)
    ->merge($SegundoLugares)
    ->merge($TercerosLugares)
    ->merge($CuartosLugares)
    ->unique('key')
    ->values()
    ->all();

// Crear el objeto TorneoFaseFinal
$TorneoFaseFinal = (object)[
    'TorneoCategoria' => $TorneoCategoria,
    'JugadoresClasificados' => App::multiPropertySort(
        collect($JugadoresClasificadosMerge),
        [
            ['column' => 'puntos', 'order' => 'desc'],
            ['column' => 'setsDiferencias', 'order' => 'desc'],
            ['column' => 'gamesDiferencias', 'order' => 'desc'],
            ['column' => 'setsGanados', 'order' => 'desc'],
            ['column' => 'gamesGanados', 'order' => 'desc']
        ]
    )
];

// Retornar la vista con los datos del TorneoFaseFinal
return view('auth' . '.' . $this->viewName . '.ajax.final.index', [
    'TorneoFaseFinal' => $TorneoFaseFinal,
    'ViewName' => ucfirst($this->viewName),
    'landing' => filter_var($landing, FILTER_VALIDATE_BOOLEAN)
]);
        }
    }

    public function faseFinalStore(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null];

        try {

            DB::beginTransaction();

            $TorneoCategoria = TorneoCategoria::where('id', $request->torneo_categoria_id)->where('torneo_id', $request->torneo_id)
            ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})->first();

            if(($TorneoCategoria->clasificados_cuartos+$TorneoCategoria->clasificados_terceros) % 2 == 0) {

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

                if ($TorneoCategoria->clasificados_cuartos > 0) {
                    $Clasifican = 4;
                } elseif ($TorneoCategoria->clasificados_terceros > 0) {
                    $Clasifican = 3;
                } else {
                    $Clasifican = $TorneoCategoria->clasificados;
                }
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

                // cambie esto
                //SOLO JUGADORES CLASIFICADOS POR CANTIDAD DE CLASIFICADOS PERMIDOS
       //CLASIFICADOS POR CÁLCULO
           // Inicializar arrays para almacenar los lugares
                    $PrimerosLugares = [];
                    $SegundoLugares = [];
                    $TercerosLugares = [];
                    $CuartosLugares = [];

                    // Clasificar jugadores en primeros, segundos, terceros y cuartos lugares
                    foreach ($JugadoresClasificados as $key => $value) {
                        if ($Clasifican == 1) {
                            $PrimerosLugares[] = $value['Clasificados']->first();
                        } elseif ($Clasifican == 2) {
                        $PrimerosLugares[] = $value['Clasificados']->first();
                        $SegundoLugares[] = $value['Clasificados']->last();
                        } elseif ($Clasifican == 3) {
                        $PrimerosLugares[] = $value['Clasificados']->first();
                            if (isset($value['Clasificados'][1])) {
                                $SegundoLugares[] = $value['Clasificados'][1];
                            }
                        $TercerosLugares[] = $value['Clasificados']->last();
                        } elseif ($Clasifican == 4) {
                            $PrimerosLugares[] = $value['Clasificados']->first();
                            if (isset($value['Clasificados'][1])) {
                                $SegundoLugares[] = $value['Clasificados'][1];
                    }
                            if (isset($value['Clasificados'][2])) {
                                $TercerosLugares[] = $value['Clasificados'][2];
                }
                            $CuartosLugares[] = $value['Clasificados']->last();
                        }
                    }




                    // Eliminar duplicados
                    $PrimerosLugares = collect($PrimerosLugares)->unique('key')->values()->all();
                    $SegundoLugares = collect($SegundoLugares)->unique('key')->values()->all();
                    $TercerosLugares = collect($TercerosLugares)->unique('key')->values()->all();
                    $CuartosLugares = collect($CuartosLugares)->unique('key')->values()->all();


                        $TercerosLugares = App::multiPropertySort(
                            collect($TercerosLugares),
                            [
                                ['column' => 'puntos', 'order' => 'desc'],
                                ['column' => 'setsDiferencias', 'order' => 'desc'],
                                ['column' => 'gamesDiferencias', 'order' => 'desc'],
                                ['column' => 'setsGanados', 'order' => 'desc'],
                                ['column' => 'gamesGanados', 'order' => 'desc']
                            ]
                        )->take($TorneoCategoria->clasificados_terceros)->toArray();





                        $CuartosLugares = App::multiPropertySort(
                            collect($CuartosLugares),
                            [
                                ['column' => 'puntos', 'order' => 'desc'],
                                ['column' => 'setsDiferencias', 'order' => 'desc'],
                                ['column' => 'gamesDiferencias', 'order' => 'desc'],
                                ['column' => 'setsGanados', 'order' => 'desc'],
                                ['column' => 'gamesGanados', 'order' => 'desc']
                            ]
                        )->take($TorneoCategoria->clasificados_cuartos)->toArray();




                    // Ordenar los lugares
                    $PrimerosLugares = App::multiPropertySort(
                        collect($PrimerosLugares),
                        [
                            ['column' => 'puntos', 'order' => 'desc'],
                            ['column' => 'setsDiferencias', 'order' => 'desc'],
                            ['column' => 'gamesDiferencias', 'order' => 'desc'],
                            ['column' => 'setsGanados', 'order' => 'desc'],
                            ['column' => 'gamesGanados', 'order' => 'desc']
                        ]
                    );
                    $SegundoLugares = App::multiPropertySort(
                        collect($SegundoLugares),
                        [
                            ['column' => 'puntos', 'order' => 'desc'],
                            ['column' => 'setsDiferencias', 'order' => 'desc'],
                            ['column' => 'gamesDiferencias', 'order' => 'desc'],
                            ['column' => 'setsGanados', 'order' => 'desc'],
                            ['column' => 'gamesGanados', 'order' => 'desc']
                        ]
                    );
                    $TercerosLugares = App::multiPropertySort(
                        collect($TercerosLugares),
                        [
                            ['column' => 'puntos', 'order' => 'desc'],
                            ['column' => 'setsDiferencias', 'order' => 'desc'],
                            ['column' => 'gamesDiferencias', 'order' => 'desc'],
                            ['column' => 'setsGanados', 'order' => 'desc'],
                            ['column' => 'gamesGanados', 'order' => 'desc']
                        ]
                    );
                    $CuartosLugares = App::multiPropertySort(
                        collect($CuartosLugares),
                        [
                            ['column' => 'puntos', 'order' => 'desc'],
                            ['column' => 'setsDiferencias', 'order' => 'desc'],
                            ['column' => 'gamesDiferencias', 'order' => 'desc'],
                            ['column' => 'setsGanados', 'order' => 'desc'],
                            ['column' => 'gamesGanados', 'order' => 'desc']
                        ]
                    );

                    // Combinar todos los lugares
                    $JugadoresClasificadosMerge = collect($PrimerosLugares)
                        ->merge($SegundoLugares)
                        ->merge($TercerosLugares)
                        ->merge($CuartosLugares)
                        ->unique('key')
                        ->values()
                        ->all();



                ///acacaca

                if(count($JugadoresClasificadosMerge) > 32){
                    $JugadoresClasificadosMerge = App::multiPropertySort(collect($JugadoresClasificadosMerge), [ ['column' => 'puntos', 'order' => 'desc'],['column' => 'setsDiferencias', 'order' => 'desc'], ['column' => 'gamesDiferencias', 'order' => 'desc'], ['column' => 'setsGanados', 'order' => 'desc'], ['column' => 'gamesGanados', 'order' => 'desc']])->take(32);
                }else{
                    $JugadoresClasificadosMerge = App::multiPropertySort(collect($JugadoresClasificadosMerge), [ ['column' => 'puntos', 'order' => 'desc'],['column' => 'setsDiferencias', 'order' => 'desc'], ['column' => 'gamesDiferencias', 'order' => 'desc'], ['column' => 'setsGanados', 'order' => 'desc'], ['column' => 'gamesGanados', 'order' => 'desc']]);
                }


                //cambie esto para arriba

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
            }else{
                $Result->Message = "Por favor, ingrese una cantidad de jugadores par.";
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

                if ($TorneoCategoria->clasificados_cuartos > 0) {
                    $Clasifican = 4;
                } elseif ($TorneoCategoria->clasificados_terceros > 0) {
                    $Clasifican = 3;
                } else {
                    $Clasifican = $TorneoCategoria->clasificados;
                }
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
                //aca/aca
                $PrimerosLugares = [];
                $SegundoLugares = [];
                $TercerosLugares = [];
                $CuartosLugares = [];

                // Clasificar jugadores en primeros, segundos, terceros y cuartos lugares
                foreach ($JugadoresClasificados as $key => $value) {
                    if ($Clasifican == 1) {
                        $PrimerosLugares[] = $value['Clasificados']->first();
                    } elseif ($Clasifican == 2) {
                        $PrimerosLugares[] = $value['Clasificados']->first();
                        $SegundoLugares[] = $value['Clasificados']->last();
                    } elseif ($Clasifican == 3) {
                        $PrimerosLugares[] = $value['Clasificados']->first();
                        if (isset($value['Clasificados'][1])) {
                            $SegundoLugares[] = $value['Clasificados'][1];
                        }
                        $TercerosLugares[] = $value['Clasificados']->last();
                    } elseif ($Clasifican == 4) {
                        $PrimerosLugares[] = $value['Clasificados']->first();
                        if (isset($value['Clasificados'][1])) {
                            $SegundoLugares[] = $value['Clasificados'][1];
                    }
                        if (isset($value['Clasificados'][2])) {
                            $TercerosLugares[] = $value['Clasificados'][2];
                }
                        $CuartosLugares[] = $value['Clasificados']->last();
                        }
                    }




                // Eliminar duplicados
                $PrimerosLugares = collect($PrimerosLugares)->unique('key')->values()->all();
                $SegundoLugares = collect($SegundoLugares)->unique('key')->values()->all();
                $TercerosLugares = collect($TercerosLugares)->unique('key')->values()->all();
                $CuartosLugares = collect($CuartosLugares)->unique('key')->values()->all();


                    $TercerosLugares = App::multiPropertySort(
                        collect($TercerosLugares),
                        [
                            ['column' => 'puntos', 'order' => 'desc'],
                            ['column' => 'setsDiferencias', 'order' => 'desc'],
                            ['column' => 'gamesDiferencias', 'order' => 'desc'],
                            ['column' => 'setsGanados', 'order' => 'desc'],
                            ['column' => 'gamesGanados', 'order' => 'desc']
                        ]
                    )->take($TorneoCategoria->clasificados_terceros)->toArray();





                    $CuartosLugares = App::multiPropertySort(
                        collect($CuartosLugares),
                        [
                            ['column' => 'puntos', 'order' => 'desc'],
                            ['column' => 'setsDiferencias', 'order' => 'desc'],
                            ['column' => 'gamesDiferencias', 'order' => 'desc'],
                            ['column' => 'setsGanados', 'order' => 'desc'],
                            ['column' => 'gamesGanados', 'order' => 'desc']
                        ]
                    )->take($TorneoCategoria->clasificados_cuartos)->toArray();




                // Ordenar los lugares
                $PrimerosLugares = App::multiPropertySort(
                    collect($PrimerosLugares),
                    [
                        ['column' => 'puntos', 'order' => 'desc'],
                        ['column' => 'setsDiferencias', 'order' => 'desc'],
                        ['column' => 'gamesDiferencias', 'order' => 'desc'],
                        ['column' => 'setsGanados', 'order' => 'desc'],
                        ['column' => 'gamesGanados', 'order' => 'desc']
                    ]
                );
                $SegundoLugares = App::multiPropertySort(
                    collect($SegundoLugares),
                    [
                        ['column' => 'puntos', 'order' => 'desc'],
                        ['column' => 'setsDiferencias', 'order' => 'desc'],
                        ['column' => 'gamesDiferencias', 'order' => 'desc'],
                        ['column' => 'setsGanados', 'order' => 'desc'],
                        ['column' => 'gamesGanados', 'order' => 'desc']
                    ]
                );
                $TercerosLugares = App::multiPropertySort(
                    collect($TercerosLugares),
                    [
                        ['column' => 'puntos', 'order' => 'desc'],
                        ['column' => 'setsDiferencias', 'order' => 'desc'],
                        ['column' => 'gamesDiferencias', 'order' => 'desc'],
                        ['column' => 'setsGanados', 'order' => 'desc'],
                        ['column' => 'gamesGanados', 'order' => 'desc']
                    ]
                );
                $CuartosLugares = App::multiPropertySort(
                    collect($CuartosLugares),
                    [
                        ['column' => 'puntos', 'order' => 'desc'],
                        ['column' => 'setsDiferencias', 'order' => 'desc'],
                        ['column' => 'gamesDiferencias', 'order' => 'desc'],
                        ['column' => 'setsGanados', 'order' => 'desc'],
                        ['column' => 'gamesGanados', 'order' => 'desc']
                    ]
                );

                // Combinar todos los lugares
                $JugadoresClasificadosMerge = $PrimerosLugares->merge($SegundoLugares)->merge($TercerosLugares)->merge($CuartosLugares);




                /// aca

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


        if(!$TorneoCategoria->multiple){

        // Obtener todos los rankings por categoría
        $rankings = $this->rankingsByCategoryId($TorneoCategoria->categoria_simple_id);
        $nombreCompletoTemporal = [];
        $rankings = $rankings['Rankings']->toArray() ?? [];

        $rankingsAssociativeArray = array_combine(
            array_column($rankings, 'id'),
            array_column($rankings, 'countRepeat')
        );

        // 241
        // Obtener el grupo del jugador local
        $grupos = DB::table('torneo_grupos')
        ->where('torneo_categoria_id', $request->torneo_categoria_id)
        ->select('grupo_id', 'nombre_grupo', 'torneo_id', 'jugador_simple_id')
        ->get();
    
        $gruposAssociativeArray = [];
        
        foreach ($grupos as $grupo) {
            $jugadorId = $grupo->jugador_simple_id;
            $grupoId = $grupo->grupo_id;
            $nombreGrupo = $grupo->nombre_grupo;
        
            $gruposAssociativeArray[$jugadorId] = [
                'grupo_id' => $grupoId,
                'nombre_grupo' => $nombreGrupo,
                'torneo_id' => $grupo->torneo_id
            ];
        }
        foreach ($JugadoresClasificados as $key => $value) {
          
            $JugadoresClasificados[$key]['grupo_nombre'] = $gruposAssociativeArray[$value['id']]['nombre_grupo'];
            $JugadoresClasificados[$key]['grupo_id'] = $gruposAssociativeArray[$value['id']]['grupo_id'];
            $JugadoresClasificados[$key]['torneo_id'] = $gruposAssociativeArray[$value['id']]['torneo_id'];
            // Llamada a la función grupoTablaPosicion
            $resultado = $this->grupoTablaPosicion($JugadoresClasificados[$key]['torneo_id'], $request->torneo_categoria_id, $JugadoresClasificados[$key]['grupo_id']);

            // ID específico que deseas buscar
            $idEspecifico = $value['id'];
            // Filtrar el resultado para obtener el objeto donde jugador_simple_id sea igual a idEspecifico
            $jugadorFiltradoKeys = array_keys(array_filter($resultado->toArray(), function($jugador) use ($idEspecifico) {
                return $jugador['jugador_simple_id'] == $idEspecifico;
            }));

            // Obtener el primer elemento del resultado filtrado
            $jugadorFiltrado = reset($jugadorFiltradoKeys);

            $JugadoresClasificados[$key]['posicion'] =  $jugadorFiltrado+1;

            if (!empty($rankingsAssociativeArray) && isset($rankingsAssociativeArray[$value['id']])) {

                // Modificar el texto solo si el id está presente en el arreglo asociativo
                $JugadoresClasificados[$key]['text'] = $value['text'] . ' (' . $rankingsAssociativeArray[$value['id']] . ')';
              
            }
            // agregar al texto el grupo y la posicion

            $JugadoresClasificados[$key]['text'] = $JugadoresClasificados[$key]['text'] . ' - ' . $JugadoresClasificados[$key]['grupo_nombre'] . ' - Posición: ' . $this->numeroOrdinal($JugadoresClasificados[$key]['posicion']);

        }   
        }
        
        if($TorneoCategoria->multiple){

            
            // Obtener el grupo del jugador local
            $grupos = DB::table('torneo_grupos')
            ->where('torneo_categoria_id', $request->torneo_categoria_id)
            ->select('grupo_id', 'nombre_grupo', 'torneo_id', 'jugador_simple_id')
            ->get();
        
            $gruposAssociativeArray = [];
            
            foreach ($grupos as $grupo) {
                $jugadorId = $grupo->jugador_simple_id;
                $grupoId = $grupo->grupo_id;
                $nombreGrupo = $grupo->nombre_grupo;
            
                $gruposAssociativeArray[$jugadorId] = [
                    'grupo_id' => $grupoId,
                    'nombre_grupo' => $nombreGrupo,
                    'torneo_id' => $grupo->torneo_id
                ];
            }

            foreach ($JugadoresClasificados as $key => $value) {

                $idEspecifico = $value['id'];
                list($id1, $id2) = explode('-', $idEspecifico);

                $JugadoresClasificados[$key]['grupo_nombre'] = $gruposAssociativeArray[$id1]['nombre_grupo'] ?? '';
                $JugadoresClasificados[$key]['grupo_nombre2'] = $gruposAssociativeArray[$id2]['nombre_grupo'] ?? '';


                $JugadoresClasificados[$key]['grupo_id'] = $gruposAssociativeArray[$id1]['grupo_id'] ?? '';
                $JugadoresClasificados[$key]['grupo_id2'] = $gruposAssociativeArray[$id2]['grupo_id'] ?? '';

                $JugadoresClasificados[$key]['torneo_id'] = $gruposAssociativeArray[$id1]['torneo_id'] ?? '';
                $JugadoresClasificados[$key]['torneo_id2'] = $gruposAssociativeArray[$id2]['torneo_id'] ?? '';
                // Llamada a la función grupoTablaPosicion
                $resultado = $this->grupoTablaPosicion($JugadoresClasificados[$key]['torneo_id'], $request->torneo_categoria_id, $JugadoresClasificados[$key]['grupo_id']);          

                // Filtrar el resultado para obtener el objeto donde jugador_simple_id sea igual a idEspecifico
                $jugadorFiltradoKeys = array_keys(array_filter($resultado->toArray(), function($jugador) use ($id1) {
                    return $jugador['jugador_simple_id'] == $id1;
                }));

                $jugadorFiltradoKeys2 = array_keys(array_filter($resultado->toArray(), function($jugador) use ($id2) {
                    return $jugador['jugador_simple_id'] == $id2;
                }));
    
                // Obtener el primer elemento del resultado filtrado
                $jugadorFiltrado = reset($jugadorFiltradoKeys);
                $jugadorFiltrado2 = reset($jugadorFiltradoKeys2);

    
                $JugadoresClasificados[$key]['posicion'] =  $jugadorFiltrado+1;
                $JugadoresClasificados[$key]['posicion1'] =  $jugadorFiltrado2+1;
    
                $JugadoresClasificados[$key]['text'] = $JugadoresClasificados[$key]['text'] . ' - ' . $JugadoresClasificados[$key]['grupo_nombre'] . ' - Posición: ' . $this->numeroOrdinal($JugadoresClasificados[$key]['posicion']);
    
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
        //observado
        
        
      
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
                        if($TorneoCategoria->multiple)
                        {
                            $keys = explode('-', $request->jugador_id);
                            if ( ($keys[0] == $JugadoresPositions[$i]['jugador_simple_id'] && $keys[1] == $JugadoresPositions[$i]['jugador_dupla_id']) ||
                                 ($keys[1] == $JugadoresPositions[$i]['jugador_simple_id'] && $keys[0] == $JugadoresPositions[$i]['jugador_dupla_id'])
                            ) {
                                $Result->Success = true;
                                $Result->Message = $Grupo->nombre_grupo . ", Posición " . ($i+1);
                                break;
                            }
                        }else{
                            if ($request->jugador_id == $JugadoresPositions[$i]['jugador_simple_id'] || $request->jugador_id == $JugadoresPositions[$i]['jugador_dupla_id']) {
                                $Result->Success = true;
                                $Result->Message = $Grupo->nombre_grupo . ", Posición " . ($i+1);
                                break;
                            }
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
    function numeroOrdinal($numero) {
        $sufijos = ['ro', 'do', 'ro', 'to', 'to', 'to', 'mo', 'mo', 'no', 'no'];
        $ultimoDigito = $numero % 10;
        $sufijo = ($numero >= 11 && $numero <= 13) ? 'ro' : $sufijos[$ultimoDigito - 1];
        return $numero . $sufijo;
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
        //modificado
        $entity = null;
    
        if($id != 0){
            $entity = Partido::with('jugadorLocalUno')->with('jugadorRivalUno')->with('torneoCategoria')->where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->where('id', $id)->first();


            if (!$entity->buy && ($entity->jugador_local_uno_id == null || $entity->jugador_rival_uno_id == null))
                return null;


            // Obtener todos los rankings por categoría
            $rankings = $this->rankingsByCategoryId($entity->torneoCategoria->categoria_simple_id);
            $nombreCompletoTemporalLocal = [];
            $nombreCompletoTemporalRival = [];
            if (!empty($rankings)) {
                $rankings = $rankings['Rankings'] ?? [];

                // Convertir rankings a una colección para usar firstWhere
                $rankingsCollection = collect($rankings);

                // Filtrar los rankings específicos para los jugadores locales y rivales
                $rankingLocal = $rankingsCollection->firstWhere('id', $entity->jugador_local_uno_id);
                $rankingRival = $rankingsCollection->firstWhere('id', $entity->jugador_rival_uno_id);

                // Verificar que los rankings existan antes de acceder a sus propiedades
                if ($rankingLocal) {
                    $nombreCompletoTemporalLocal[] = $rankingLocal['countRepeat'];
                }

                if ($rankingRival) {
                    $nombreCompletoTemporalRival[] = $rankingRival['countRepeat'];
                }
            }
          
            $entity->jugadorLocalUno->setNombreCompletoConDatosAdicionales($nombreCompletoTemporalLocal);
            $entity->jugadorRivalUno->setNombreCompletoConDatosAdicionales($nombreCompletoTemporalRival);
        }



            
        return view('auth'.'.'.$this->viewName.'.ajax.final.partialView', ['Model' => $entity, 'Position' => $position, 'ViewName' => ucfirst($this->viewName)]);
    }

    public function rankingsByCategoryId($filter_categoria,$changeAll = false)
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
                                $Id = $q->dupla ? ($q4->jugadorSimple->id . '-' . $q4->jugadorDupla->id) : $q4->jugadorSimple->id;
                                if (!in_array($Id, $JugadoresIds)) {
                                    $ObjectJugador = [];
                                    $Puntos = 0;
                                    $ObjectJugador['id'] = $Id;
                                    $ObjectJugador['nombre'] = $q->dupla ? ($q4->jugadorSimple->nombre_completo . ' + ' . $q4->jugadorDupla->nombre_completo) : $q4->jugadorSimple->nombre_completo;

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


            foreach ($result as $key => $q) {

                // Obtener el modelo Jugador
                $jugador = Jugador::find($q['id']);

                if ($jugador) {

                    // Llamar al método en el modelo Jugador
                    $jugador->setNombreCompletoConDatosAdicionales([$result[$key]['countRepeat']]);
                    $jugador->ranking_temporal = $result[$key]['countRepeat'];
                    $jugador->save();
                }

            }
            
           
        

            return [
                'Rankings' => collect( $result),

            ];

        } else {
            abort(404);
        }
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

            if(($TorneoCategoria->clasificados_cuartos+$TorneoCategoria->clasificados_terceros) % 2 == 0) {
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

                if ($TorneoCategoria->clasificados_cuartos > 0) {
                    $Clasifican = 4;
                } elseif ($TorneoCategoria->clasificados_terceros > 0) {
                    $Clasifican = 3;
                } else {
                    $Clasifican = $TorneoCategoria->clasificados;
                }
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
                //aca
                $PrimerosLugares = [];
                $SegundoLugares = [];
                $TercerosLugares = [];
                $CuartosLugares = [];

                // Clasificar jugadores en primeros, segundos, terceros y cuartos lugares
                foreach ($JugadoresClasificados as $key => $value) {
                    if ($Clasifican == 1) {
                        $PrimerosLugares[] = $value['Clasificados']->first();
                    } elseif ($Clasifican == 2) {
                        $PrimerosLugares[] = $value['Clasificados']->first();
                        $SegundoLugares[] = $value['Clasificados']->last();
                    } elseif ($Clasifican == 3) {
                        $PrimerosLugares[] = $value['Clasificados']->first();
                        if (isset($value['Clasificados'][1])) {
                            $SegundoLugares[] = $value['Clasificados'][1];
                        }
                        $TercerosLugares[] = $value['Clasificados']->last();
                    } elseif ($Clasifican == 4) {
                        $PrimerosLugares[] = $value['Clasificados']->first();
                        if (isset($value['Clasificados'][1])) {
                            $SegundoLugares[] = $value['Clasificados'][1];
                    }
                        if (isset($value['Clasificados'][2])) {
                            $TercerosLugares[] = $value['Clasificados'][2];
                }
                        $CuartosLugares[] = $value['Clasificados']->last();
                        }
                    }




                // Eliminar duplicados
                $PrimerosLugares = collect($PrimerosLugares)->unique('key')->values()->all();
                $SegundoLugares = collect($SegundoLugares)->unique('key')->values()->all();
                $TercerosLugares = collect($TercerosLugares)->unique('key')->values()->all();
                $CuartosLugares = collect($CuartosLugares)->unique('key')->values()->all();


                    $TercerosLugares = App::multiPropertySort(
                        collect($TercerosLugares),
                        [
                            ['column' => 'puntos', 'order' => 'desc'],
                            ['column' => 'setsDiferencias', 'order' => 'desc'],
                            ['column' => 'gamesDiferencias', 'order' => 'desc'],
                            ['column' => 'setsGanados', 'order' => 'desc'],
                            ['column' => 'gamesGanados', 'order' => 'desc']
                        ]
                    )->take($TorneoCategoria->clasificados_terceros)->toArray();





                    $CuartosLugares = App::multiPropertySort(
                        collect($CuartosLugares),
                        [
                            ['column' => 'puntos', 'order' => 'desc'],
                            ['column' => 'setsDiferencias', 'order' => 'desc'],
                            ['column' => 'gamesDiferencias', 'order' => 'desc'],
                            ['column' => 'setsGanados', 'order' => 'desc'],
                            ['column' => 'gamesGanados', 'order' => 'desc']
                        ]
                    )->take($TorneoCategoria->clasificados_cuartos)->toArray();




                // Ordenar los lugares
                $PrimerosLugares = App::multiPropertySort(
                    collect($PrimerosLugares),
                    [
                        ['column' => 'puntos', 'order' => 'desc'],
                        ['column' => 'setsDiferencias', 'order' => 'desc'],
                        ['column' => 'gamesDiferencias', 'order' => 'desc'],
                        ['column' => 'setsGanados', 'order' => 'desc'],
                        ['column' => 'gamesGanados', 'order' => 'desc']
                    ]
                );
                $SegundoLugares = App::multiPropertySort(
                    collect($SegundoLugares),
                    [
                        ['column' => 'puntos', 'order' => 'desc'],
                        ['column' => 'setsDiferencias', 'order' => 'desc'],
                        ['column' => 'gamesDiferencias', 'order' => 'desc'],
                        ['column' => 'setsGanados', 'order' => 'desc'],
                        ['column' => 'gamesGanados', 'order' => 'desc']
                    ]
                );
                $TercerosLugares = App::multiPropertySort(
                    collect($TercerosLugares),
                    [
                        ['column' => 'puntos', 'order' => 'desc'],
                        ['column' => 'setsDiferencias', 'order' => 'desc'],
                        ['column' => 'gamesDiferencias', 'order' => 'desc'],
                        ['column' => 'setsGanados', 'order' => 'desc'],
                        ['column' => 'gamesGanados', 'order' => 'desc']
                    ]
                );
                $CuartosLugares = App::multiPropertySort(
                    collect($CuartosLugares),
                    [
                        ['column' => 'puntos', 'order' => 'desc'],
                        ['column' => 'setsDiferencias', 'order' => 'desc'],
                        ['column' => 'gamesDiferencias', 'order' => 'desc'],
                        ['column' => 'setsGanados', 'order' => 'desc'],
                        ['column' => 'gamesGanados', 'order' => 'desc']
                    ]
                );

                // Combinar todos los lugares
                $JugadoresClasificadosMerge = collect($PrimerosLugares)
                    ->merge($SegundoLugares)
                    ->merge($TercerosLugares)
                    ->merge($CuartosLugares)
                    ->unique('key')
                    ->values()
                    ->all();


            //aca

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
            }
            else{
                $Result->Message = "Por favor, ingrese una cantidad de jugadores par.";
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
            $Partido = Partido::where('torneo_id', $torneo)->where('torneo_categoria_id', $torneo_categoria_id)->whereNotNull('fase')->first();

            $maxFase = $Partido != null ? $Partido->fase : 0;

            $TorneoFaseFinal = (object)['TorneoCategoria' => $TorneoCategoria];
            return view('auth'.'.'.$this->viewName.'.ajax.final.mapa.partialView', ['TorneoFaseFinal' => $TorneoFaseFinal, 'MaxFase' => $maxFase, 'ViewName' => ucfirst($this->viewName), 'comunidad' => $Comunidad, 'landing' => filter_var($landing, FILTER_VALIDATE_BOOLEAN)]);
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
        $torneoCategoria = $entity->torneoCategorias()->where('id', operator: $torneo_categoria_id)->with('categoriaSimple')->orderBy('orden')->first();
       
        //actualizar todos lo jugadores la columna nombre_completo_temporal

        $affectedRows = Jugador::query()->update(values: ['nombre_completo_temporal' => DB::raw('NULL')]);

         $this->rankingsByCategoryId($torneoCategoria->categoria_simple_id,true);

   
        $hasFase = Partido::where('torneo_categoria_id', $torneoCategoria->id)->whereNotNull('fase')->count() > 0;

        return view('auth'.'.'.$this->viewName.'.ajax.grupo.index', ['Model' => $entity, 'TorneoCategoriaId' => $torneo_categoria_id, 'ViewName' => ucfirst($this->viewName), 'Fase' => ($fase == null || $fase == 0 ? null : $fase) ,'landing' => filter_var($landing, FILTER_VALIDATE_BOOLEAN), 'hasFase' => $hasFase]);
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
                    'nombres' => $TorneoCategoria->multiple ? ($q->jugadorSimple->nombre_completo." + ".$q->jugadorDupla->nombre_completo) : $q->jugadorSimple->nombre_completo_temporal
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


    public function grupoTablaPosicion($id, $torneo_categoria_id, $grupo_id, $landing=false)
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

            return App::multiPropertySort(collect($TablePositions), [
                ['column' => 'puntos', 'order' => 'desc'],
                ['column' => 'setsDiferencias', 'order' => 'desc'],
                ['column' => 'gamesDiferencias', 'order' => 'desc'],
                ['column' => 'setsGanados', 'order' => 'desc'],
                ['column' => 'gamesGanados', 'order' => 'desc']
            ]);

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

                            }
                            else if($request->tipo =='zonas'){

                            // Obtener los jugadores junto con sus zonas desde la tabla torneo_jugador_zona
$Jugadores = $Torneo->torneoJugadors()
->where('torneo_categoria_id', $request->torneo_categoria_id)
->leftJoin('torneo_jugador_zona', 'torneo_jugador_zona.torneo_jugador_id', '=', 'torneo_jugadors.id')
->leftJoin('zonas', 'zonas.id', '=', 'torneo_jugador_zona.zona_id')
->select('torneo_jugadors.*', 'zonas.nombre as zona_nombre','torneo_jugador_zona.zona_id', 'torneo_jugador_zona.torneo_jugador_id')
->orderBy('torneo_jugador_zona.zona_id', 'asc') // Asegurar que se ordenen por zona
->get()
->groupBy('zona_id'); // Agrupar por zona_id

// Primero, validamos que todos los jugadores tengan una zona asignada
foreach ($Jugadores as $zonaId => $jugadoresZona) {
    // Validar que todos los jugadores en esta zona tengan zona asignada
    foreach ($jugadoresZona as $jugador) {
        if (empty($jugador->zona_id)) {
            $Result->Message = "El jugador {$jugador->jugador_simple_id} no tiene zona asignada.";
            return response()->json($Result);
        }
    }

    // Luego validamos que el número de jugadores en cada zona sea divisible por 4
    if ($jugadoresZona->count() % 4 !== 0) {
        // Obtener el nombre de la zona desde el primer jugador de la zona
        $zonaNombre = $jugadoresZona->first()->zona_nombre;
        $Result->Message = "La zona {$zonaNombre} tiene {$jugadoresZona->count()} jugadores, que no es un número divisible por 4. Por favor, ajuste la cantidad de jugadores en esta zona.";
        return response()->json($Result);
    }
}

// Calcular la cantidad de grupos necesarios
$CantidadGrupos = ceil($Jugadores->flatten(1)->count() / 4);

// Obtener los grupos disponibles
$GruposDisponibles = Grupo::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
->orderBy('nombre', 'asc')
->get();

if ($GruposDisponibles->count() < $CantidadGrupos) {
    $Result->Message = "Por favor, registre " . ($CantidadGrupos - $GruposDisponibles->count()) . " grupos más para generar las llaves.";
    return response()->json($Result);

}

// Seleccionar los grupos necesarios
$Grupos = $GruposDisponibles->take($CantidadGrupos);

// Lista para rastrear jugadores ya asignados
$JugadoresAsignados = collect();

// Índice del grupo actual
$indexGrupo = 0;

// Distribuir los jugadores por zonas en los grupos
foreach ($Jugadores as $zonaId => $jugadoresZona) {
while ($jugadoresZona->isNotEmpty()) {
    // Obtener el grupo actual
    $grupo = $Grupos[$indexGrupo];

    // Sacar exactamente 4 jugadores de la zona
    $jugadoresParaGrupo = $jugadoresZona->splice(0, 4);

    // Asignar los jugadores al grupo
    foreach ($jugadoresParaGrupo as $jugador) {
        if ($JugadoresAsignados->contains($jugador->jugador_simple_id)) {
            continue; // Si ya fue asignado, saltar este jugador
        }
        TorneoGrupo::create([
            'torneo_id' => $request->torneo_id,
            'torneo_categoria_id' => $request->torneo_categoria_id,
            'jugador_simple_id' => $jugador->jugador_simple_id,
            'grupo_id' => $grupo->id,
            'nombre_grupo' => $grupo->nombre,
            'user_create_id' => Auth::id(),
        ]);

        // Registrar el jugador como asignado
        $JugadoresAsignados->push($jugador->jugador_simple_id);
    }

    // Avanzar al siguiente grupo
    $indexGrupo++;
    if ($indexGrupo >= $Grupos->count()) {
        $indexGrupo = 0; // Reiniciar si se usan todos los grupos disponibles
    }
}
}





                            


                            }
                            else{
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
                })->get()->map(function ($q){return ['id' => $q->jugador_simple_id, 'text' => $q->jugadorSimple->nombre_completo_temporal];});
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

        $selectedZonas = $Entity->zonas ? $Entity->zonas->pluck('id')->toArray() : [];

        return view('auth'.'.'.$this->viewName.'.ajax.jugador.partialViewZona', ['Model' => $Entity, 'Zonas' => $Zonas, 'ViewName' => ucfirst($this->viewName), 'selectedZonas' => $selectedZonas]);
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
                    $entity->update($request->only('pago', 'monto', 'user_update_id'));

                    // Sync the zonas
                    $entity->zonas()->sync($request->zonas);

            

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

    public function jugadorAvailableClassificationListJson(Request $request)
    {
        $JugadoresNoJugaron = [];

        $ComunidadId = Auth::guard('web')->user()->comunidad_id;

        $TorneoCategoria = TorneoCategoria::where('id', $request->torneo_categoria_id)
        ->where('torneo_id', $request->torneo_id)->whereHas('torneo', function ($q) use ($ComunidadId) {
            $q->where('comunidad_id', $ComunidadId);
        })->first();

        if ($TorneoCategoria != null)
        {
            $PartidosFaseFinalPendientes = Partido::where('torneo_categoria_id', $TorneoCategoria->id)
            ->where('torneo_id', $TorneoCategoria->torneo_id)->whereNotNull('fase')
            ->where('estado_id', App::$ESTADO_PENDIENTE)->get();

            $PartidosFaseFinalJugados = Partido::where('torneo_categoria_id', $TorneoCategoria->id)
                ->where('torneo_id', $TorneoCategoria->torneo_id)->whereNotNull('fase')
                ->where('estado_id', App::$ESTADO_FINALIZADO)->get();

            if($TorneoCategoria->multiple)
            {
                $JugadoresLocalesJugados = $PartidosFaseFinalJugados->map(function ($q){  return ($q->jugador_local_uno_id.'-'.$q->jugador_local_dos_id); })->toArray();
                $JugadoresRivalesJugados = $PartidosFaseFinalJugados->map(function ($q){  return ($q->jugador_rival_uno_id.'-'.$q->jugador_rival_dos_id); })->toArray();

                $JugadoresLocalesPendientes = $PartidosFaseFinalPendientes->map(function ($q){  return ($q->jugador_local_uno_id.'-'.$q->jugador_local_dos_id); })->toArray();
                $JugadoresRivalesPendientes = $PartidosFaseFinalPendientes->map(function ($q){  return ($q->jugador_rival_uno_id.'-'.$q->jugador_rival_dos_id); })->toArray();

                $JugadoresIdsJugaron = array_values(array_filter(array_unique(array_merge($JugadoresLocalesJugados, $JugadoresRivalesJugados))));
                $JugadoresIdsPendientes = array_values(array_filter(array_unique(array_merge($JugadoresLocalesPendientes, $JugadoresRivalesPendientes))));

                $JugadoresIdsNoJugaron = array_values(array_filter(collect($JugadoresIdsPendientes)->map(function ($q) use ($JugadoresIdsJugaron){ return !in_array($q, $JugadoresIdsJugaron) ? $q : null; })->toArray()));

                foreach ($JugadoresIdsNoJugaron as $q)
                {
                    $JugadorUno = Jugador::find(explode('-', $q)[0]);
                    $JugadorDos = Jugador::find(explode('-', $q)[1]);

                    $JugadoresNoJugaron[] = (object)['id' => ($JugadorUno->id).'-'.($JugadorDos->id), 'nombre_completo' => ($JugadorUno->nombre_completo.' + '.$JugadorDos->nombre_completo)];
                }

                $JugadoresNoJugaron = collect($JugadoresNoJugaron);
            }else{

                $JugadoresIdsJugaron = array_values(array_filter(array_unique(array_merge($PartidosFaseFinalJugados->pluck('jugador_local_uno_id')->toArray(), $PartidosFaseFinalJugados->pluck('jugador_rival_uno_id')->toArray()))));
                $JugadoresIdsPendientes = array_values(array_filter(array_unique(array_merge($PartidosFaseFinalPendientes->pluck('jugador_local_uno_id')->toArray(), $PartidosFaseFinalPendientes->pluck('jugador_rival_uno_id')->toArray()))));

                $JugadoresIdsNoJugaron = array_values(array_filter(collect($JugadoresIdsPendientes)->map(function ($q) use ($JugadoresIdsJugaron){ return !in_array($q, $JugadoresIdsJugaron) ? $q : null; })->toArray()));

                $JugadoresNoJugaron = Jugador::whereIn('id', $JugadoresIdsNoJugaron)->get()->map(function ($q){  return (object)['id' => $q->id, 'nombre_completo' => $q->nombre_completo]; })->toArray();

                $JugadoresNoJugaron = collect($JugadoresNoJugaron);
            }
        }

        return response()->json(['data' => $JugadoresNoJugaron->map(function ($q){return ['id' => $q->id, 'text' => $q->nombre_completo]; })]);
    }

    public function jugadorAvailableNotClassificationListJson(Request $request)
    {
        $JugadoresNoJugaron = [];

        $ComunidadId = Auth::guard('web')->user()->comunidad_id;

        $TorneoCategoria = TorneoCategoria::where('id', $request->torneo_categoria_id)
            ->where('torneo_id', $request->torneo_id)->whereHas('torneo', function ($q) use ($ComunidadId) {
                $q->where('comunidad_id', $ComunidadId);
            })->first();

        if ($TorneoCategoria != null)
        {
            $PartidosFaseFinalPendientes = Partido::where('torneo_categoria_id', $TorneoCategoria->id)
                ->where('torneo_id', $TorneoCategoria->torneo_id)->whereNotNull('fase')
                ->where('estado_id', App::$ESTADO_PENDIENTE)->get();

            $PartidosFaseFinalJugados = Partido::where('torneo_categoria_id', $TorneoCategoria->id)
                ->where('torneo_id', $TorneoCategoria->torneo_id)->whereNotNull('fase')
                ->where('estado_id', App::$ESTADO_FINALIZADO)->get();

            if($TorneoCategoria->multiple)
            {
                $JugadoresLocalesJugados = $PartidosFaseFinalJugados->map(function ($q){  return ($q->jugador_local_uno_id.'-'.$q->jugador_local_dos_id); })->toArray();
                $JugadoresRivalesJugados = $PartidosFaseFinalJugados->map(function ($q){  return ($q->jugador_rival_uno_id.'-'.$q->jugador_rival_dos_id); })->toArray();

                $JugadoresLocalesPendientes = $PartidosFaseFinalPendientes->map(function ($q){  return ($q->jugador_local_uno_id.'-'.$q->jugador_local_dos_id); })->toArray();
                $JugadoresRivalesPendientes = $PartidosFaseFinalPendientes->map(function ($q){  return ($q->jugador_rival_uno_id.'-'.$q->jugador_rival_dos_id); })->toArray();

                $JugadoresIdsJugaron = array_values(array_filter(array_unique(array_merge($JugadoresLocalesJugados, $JugadoresRivalesJugados))));
                $JugadoresIdsPendientes = array_values(array_filter(array_unique(array_merge($JugadoresLocalesPendientes, $JugadoresRivalesPendientes))));

                $JugadoresIdsNoJugaron = array_values(array_filter(collect($JugadoresIdsPendientes)->map(function ($q) use ($JugadoresIdsJugaron){ return !in_array($q, $JugadoresIdsJugaron) ? $q : null; })->toArray()));

                foreach ($JugadoresIdsNoJugaron as $q)
                {
                    $JugadorUno = Jugador::find(explode('-', $q)[0]);
                    $JugadorDos = Jugador::find(explode('-', $q)[1]);

                    $JugadoresNoJugaron[] = (object)['id' => ($JugadorUno->id).'-'.($JugadorDos->id), 'nombre_completo' => ($JugadorUno->nombre_completo.' + '.$JugadorDos->nombre_completo)];
                }

                $JugadoresNoJugaron = collect($JugadoresNoJugaron);
            }else{

                $JugadoresIdsJugaron = array_values(array_filter(array_unique(array_merge($PartidosFaseFinalJugados->pluck('jugador_local_uno_id')->toArray(), $PartidosFaseFinalJugados->pluck('jugador_rival_uno_id')->toArray()))));
                $JugadoresIdsPendientes = array_values(array_filter(array_unique(array_merge($PartidosFaseFinalPendientes->pluck('jugador_local_uno_id')->toArray(), $PartidosFaseFinalPendientes->pluck('jugador_rival_uno_id')->toArray()))));

                $JugadoresIdsNoJugaron = array_values(array_filter(collect($JugadoresIdsPendientes)->map(function ($q) use ($JugadoresIdsJugaron){ return !in_array($q, $JugadoresIdsJugaron) ? $q : null; })->toArray()));

                $JugadoresNoJugaron = Jugador::whereIn('id', $JugadoresIdsNoJugaron)->get()->map(function ($q){  return (object)['id' => $q->id, 'nombre_completo' => $q->nombre_completo]; })->toArray();

                $JugadoresNoJugaron = collect($JugadoresNoJugaron);
            }
        }

        $JugadoresNoClasificados = $TorneoCategoria->torneo->torneoGrupos()->where('torneo_categoria_id', $TorneoCategoria->id)
            ->where(function ($q) use ($JugadoresNoJugaron, $TorneoCategoria){
                if($TorneoCategoria->multiple){
                    $q->whereNotIn('jugador_simple_id', $JugadoresNoJugaron->pluck('id')->map(function ($q2){ return explode('-', $q2)[0]; })->toArray());
                    $q->whereNotIn('jugador_dupla_id', $JugadoresNoJugaron->pluck('id')->map(function ($q2){ return explode('-', $q2)[1]; })->toArray());
                }else{
                    $q->whereNotIn('jugador_simple_id', $JugadoresNoJugaron->pluck('id')->toArray());
                }
            })->get()->map(function ($q) use ($TorneoCategoria) {
                return [
                    'id' => $q->jugadorSimple->id.'-'.($TorneoCategoria->multiple ? $q->jugadorDupla->id : '0'),
                    'jugador_simple_id' => $q->jugadorSimple->id,
                    'jugador_dupla_id' => $TorneoCategoria->multiple ? $q->jugadorDupla->id : null,
                    'nombres' => $TorneoCategoria->multiple ? ($q->jugadorSimple->nombre_completo . " + " . $q->jugadorDupla->nombre_completo) : $q->jugadorSimple->nombre_completo
                ];
            });

        return response()->json(['data' => collect($JugadoresNoClasificados)->map(function ($q){return ['id' => $q['id'], 'text' => $q['nombres']]; })]);
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

    public function jugadorClassificationChange(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try{

            DB::beginTransaction();

            $TorneoCategoria = TorneoCategoria::where('id', $request->torneo_categoria_id)
                ->where('torneo_id', $request->torneo_id)->whereHas('torneo', function ($q){
                    $q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);
                })->first();

            if($TorneoCategoria != null)
            {
                $JugadorRemplazarUno = explode('-', $request->jugador_remplazar_id)[0];
                $JugadorRemplazarDos = $TorneoCategoria->multiple ? explode('-', $request->jugador_remplazar_id)[1] : null;

                $JugadorRemplazoUno = explode('-', $request->jugador_remplazo_id)[0];
                $JugadorRemplazoDos = $TorneoCategoria->multiple ? explode('-', $request->jugador_remplazo_id)[1] : null;

                $Partido = Partido::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
                    ->where('torneo_id', $TorneoCategoria->torneo_id)
                    ->where('torneo_categoria_id', $TorneoCategoria->id)
                    ->whereNotNull('fase')
                    ->where(function ($q) use ($JugadorRemplazarUno, $JugadorRemplazarDos){
                        $q->where(function($q2) use ($JugadorRemplazarUno, $JugadorRemplazarDos){
                            $q2->where('jugador_local_uno_id', $JugadorRemplazarUno);
                            if($JugadorRemplazarDos != null){ $q2->where('jugador_local_dos_id', $JugadorRemplazarDos); }
                        })->orWhere(function($q2) use ($JugadorRemplazarUno, $JugadorRemplazarDos){
                            $q2->where('jugador_rival_uno_id', $JugadorRemplazarUno);
                            if($JugadorRemplazarDos != null){ $q2->where('jugador_rival_dos_id', $JugadorRemplazarDos); }
                        });
                    })
                    ->where('estado_id', App::$ESTADO_PENDIENTE)
                    ->orderBy('id', 'desc')->first();

                if($Partido != null)
                {
                    if(in_array($JugadorRemplazarUno, [$Partido->jugador_local_uno_id, $Partido->jugador_local_dos_id]) ||
                        ($JugadorRemplazarDos != null && in_array($JugadorRemplazarDos, [$Partido->jugador_local_uno_id, $Partido->jugador_local_dos_id])))
                    {
                        $Partido->jugador_local_uno_id = $JugadorRemplazoUno;
                        $Partido->jugador_local_dos_id = $JugadorRemplazoDos;
                    }else if(in_array($JugadorRemplazarUno, [$Partido->jugador_rival_uno_id, $Partido->jugador_rival_dos_id]) ||
                        ($JugadorRemplazarDos != null && in_array($JugadorRemplazarDos, [$Partido->jugador_rival_uno_id, $Partido->jugador_rival_dos_id])))
                    {
                        $Partido->jugador_rival_uno_id = $JugadorRemplazoUno;
                        $Partido->jugador_rival_dos_id = $JugadorRemplazoDos;
                    }

                    $Partido->save();

                    DB::commit();

                    $Result->Success = true;
                }
            }

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
                         
                          $Result->Success = true;

                          if(filter_var($request->fase_inicial, FILTER_VALIDATE_BOOLEAN))
                          {
                              if(Partido::where('torneo_categoria_id', $TorneoCategoria->id)->whereNotNull('fase')->count() > 0)
                              {
                                  
                                                                  if(TorneoCategoria::where('torneo_id', $request->torneo_id)->where('comunidad_id', Auth::guard('web')->user()->comunidad_id, 'manual',1)){
                                    $partidosAsociadosAlJugador = Partido::where('torneo_id', $request->torneo_id)
                                    ->where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
                                    ->where('torneo_categoria_id', $request->torneo_categoria_id)
                                    ->where('estado_id', App::$ESTADO_PENDIENTE)
                                    ->where(function($query) use ($request) {
                                        $query->where('jugador_local_uno_id', $request->jugador_local_id)
                                            ->orWhere('jugador_rival_uno_id', $request->jugador_local_id)
                                            ->orWhere('jugador_local_uno_id', $request->jugador_rival_id)
                                            ->orWhere('jugador_rival_uno_id', $request->jugador_rival_id);
                                    })
                                    ->update(['jugador_local_uno_id' => null, 'jugador_rival_uno_id' => null]);
                                } 
                                  $Result->Message = "Se ha realizado la modificación del partido seleccionado, por favor valide que las llaves esten correctamente agrupadas.";
                              }
                          }
                           DB::commit();

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

    public function partidoGenerateJson(Request $request)
    {
        $content = "";

        $Partido = Partido::find($request->id);
        

        if($Partido != null)
        {
            $TorneoGrupo = TorneoGrupo::where('torneo_categoria_id', $Partido->torneo_categoria_id)
                ->where('grupo_id', $Partido->grupo_id)->first();

            $ResultadoGanador = $Partido->resultado; $ResultadoPerdedor = '';
            if(!in_array($Partido->resultado, ["wo", "w.o", "WO", "W.O"]))
            {
                $ResultadoLeft = []; $ResultadoRight = [];

                $Resultado = explode('/', $Partido->resultado);
                foreach ($Resultado as $item){
                    $set = explode('-', $item);
                    $ResultadoLeft[] = $set[0]; $ResultadoRight[] = $set[1];
                }

                $ResultadoGanador = join('/', $ResultadoLeft);
                $ResultadoPerdedor = join('/', $ResultadoRight);
            }

            $model = (object)[
                'titulo' => 'La Confraternidad del Tenis',
                'torneo' => $Partido->torneoCategoria->torneo->nombre,
                'formato' => $Partido->torneoCategoria->torneo->formato != null ? $Partido->torneoCategoria->torneo->formato->nombre : null,
                'grupo' => $TorneoGrupo != null ? $TorneoGrupo->nombre_grupo : null,
                'ronda' => $Partido->fase ?? 'Fase de grupos',
                'categoria' => $Partido->multiple && ($Partido->torneoCategoria->categoriaSimple->id !== $Partido->torneoCategoria->categoriaDupla->id) ? ($Partido->torneoCategoria->categoriaSimple->nombre." + ".$Partido->torneoCategoria->categoriaDupla->nombre) : ($Partido->torneoCategoria->categoriaSimple->nombre),
                'jugador_ganador_uno' => $Partido->jugadorGanadorUno->nombre_completo_temporal,
                'jugador_ganador_dos' => $Partido->multiple ? ($Partido->jugadorGanadorDos->nombre_completo) : null,
                'jugador_rival_uno'  => $Partido->jugador_local_uno_id == $Partido->jugador_ganador_uno_id ? $Partido->jugadorRivalUno->nombre_completo_temporal : $Partido->jugadorLocalUno->nombre_completo_temporal,
                'jugador_rival_dos'  => $Partido->multiple ? ($Partido->jugador_local_dos_id == $Partido->jugador_ganador_dos_id ? $Partido->jugadorRivalDos->nombre_completo : $Partido->jugadorLocalDos->nombre_completo) : null,
                'resultado_ganador'  => $ResultadoGanador,
                'resultado_rival' => $ResultadoPerdedor,
            ];

            $content = json_encode($model);
        }

        Storage::disk('public')->put('public/uploads/matches/json.txt', $content);

  


        return redirect(App::$URL_JSON_MATCHES.'?json='.env('APP_URL').'/storage/public/uploads/matches/json.txt');
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
                            'nombres' => $TorneoCategoria->multiple ? ($q->jugadorSimple->nombre_completo." + ".$q->jugadorDupla->nombre_completo) : $q->jugadorSimple->nombre_completo_temporal,
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
                $Puntuaciones = Puntuacion::where('comunidad_id', $ComunidadId)->where('type',0)->get()->toArray();

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
            //traerme torneocategoria 
                $Puntuacionesv2 = Puntuacion::where('comunidad_id', $ComunidadId)->where('type',1)->get()->toArray();
                $puntosMap = [];
                foreach ($Puntuacionesv2 as $puntuacion) {
                    $puntosMap[$puntuacion['nombre']] = $puntuacion['puntos'];
                }
                $partidos=  Partido::where('torneo_categoria_id', $TorneoCategoria->id)->whereNull(columns: 'deleted_at')->where(['estado_id' => App::$ESTADO_FINALIZADO])->get();

                foreach ($partidos as $partido) {
                    // Asumiendo que el jugador ganador es el jugador local si 'jugador_ganador_uno_id' coincide con 'jugador_local_uno_id'
                    if ($partido->jugador_ganador_uno_id == $partido->jugador_local_uno_id) {
                        $ganadorId = $partido->jugador_local_uno_id;
                        $perdedorId = $partido->jugador_rival_uno_id;
                        $setsGanador = $partido->jugador_local_set;
                        $setsPerdedor = $partido->jugador_rival_set;
                        $resultado = $partido->resultado;
                    } else {
                        $ganadorId = $partido->jugador_rival_uno_id;
                        $perdedorId = $partido->jugador_local_uno_id;
                        $setsGanador = $partido->jugador_local_set;
                        $setsPerdedor = $partido->jugador_rival_set;
                        $resultado = $partido->resultado;
                    }
                
                    $resultados[] = [
                        'ganador_id' => $ganadorId,
                        'perdedor_id' => $perdedorId,
                        'sets_ganador' => $setsGanador,
                        'sets_perdedor' => $setsPerdedor,
                        'resultado' => $resultado
                    ];
                }

         

                //hacer un for del arreglo TablePositions donde sumarle puntos si es que cumple las conficiones 2-0 o 2-1 respectivamente
                //segun los sets ganador y perdedor tendrias que mezclar deonde este el id coincidente del nombre
// Iterar sobre los resultados de los partidos
       //     return $resultados;
            foreach ($resultados as $resultado) {
                $ganadorId = $resultado['ganador_id'];
                $perdedorId = $resultado['perdedor_id'];
                $setsGanador = $resultado['sets_ganador'];
                $setsPerdedor = $resultado['sets_perdedor'];
                $resultado = $resultado['resultado'];

                // Determinar los puntos a sumar según las condiciones de sets ganados y perdidos
                if ($setsGanador == 2 && $setsPerdedor == 0 && $resultado != 'wo' && $resultado != 'w.o' && $resultado != 'WO' && $resultado != 'W.O') {
                    $puntosGanador = $puntosMap['puntos_ganador_2_0'];
                    $puntosPerdedor = $puntosMap['puntos_perdedor_2_0'];
                } elseif ($setsGanador == 2 && $setsPerdedor == 1 && $resultado != 'wo' && $resultado != 'w.o' && $resultado != 'WO' && $resultado != 'W.O') {
                    $puntosGanador = $puntosMap['puntos_ganador_2_1'];
                    $puntosPerdedor = $puntosMap['puntos_perdedor_2_1'];
                } elseif ($resultado == 'wo' || $resultado == 'w.o' || $resultado == 'WO' || $resultado == 'W.O') {
                    $puntosGanador = $puntosMap['puntos_ganador_wo'];
                    $puntosPerdedor = $puntosMap['puntos_perdedor_wo'];
                }
                 else {
                    $puntosGanador = 0;
                    $puntosPerdedor = 0;
                }

                
                // Iterar sobre el arreglo TablePositions
                foreach ($PuntuacionesResult as &$posicion) {
             
                    // Sumar puntos al ganador
                    if ($posicion['jugador_simple_id'] == $ganadorId) {
                        
                        $posicion['puntos'] += $puntosGanador;
                    }

                    // Sumar puntos al perdedor
                    if ($posicion['jugador_simple_id'] == $perdedorId) {
                        $posicion['puntos'] += $puntosPerdedor;
                    }
                }

                unset($posicion);

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
                'damas' => str_contains(strtolower(($TorneoCategoria->multiple && ($TorneoCategoria->categoriaSimple->id !== $TorneoCategoria->categoriaDupla->id) ? ($TorneoCategoria->categoriaSimple->nombre." + ".$TorneoCategoria->categoriaDupla->nombre) : ($TorneoCategoria->categoriaSimple->nombre)."".($TorneoCategoria->multiple ? "" : ""))), 'damas') ? true : false,
                'categoria' => $TorneoCategoria->multiple && ($TorneoCategoria->categoriaSimple->id !== $TorneoCategoria->categoriaDupla->id) ? ($TorneoCategoria->categoriaSimple->nombre." + ".$TorneoCategoria->categoriaDupla->nombre) : ($TorneoCategoria->categoriaSimple->nombre)."".($TorneoCategoria->multiple ? "" : ""),
                'fecha_inicio' => Carbon::parse($TorneoCategoria->torneo->fecha_inicio)->format('Y-m-d'),
                'fecha_final' => Carbon::parse($TorneoCategoria->torneo->fecha_final)->format('Y-m-d'),
                'ganador' => $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first() != null ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first()->multiple ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first()->jugadorGanadorUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first()->jugadorGanadorDos->nombre_completo) : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first()->jugadorGanadorUno->nombre_completo_temporal : null,
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
                        $bloque['jugador_local'] = $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo_temporal : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-");
                        $bloque['jugador_rival'] = $q->jugadorRivalUno != null ? (!$TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-");
                        $bloque['resultado'] = $q->resultado;
                        $model->llaves['ronda32']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }

                    foreach($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 16)->where('bloque', 2) as $q)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo_temporal : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-");
                        $bloque['jugador_rival'] = $q->jugadorRivalUno != null ? (!$TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-");
                        $bloque['resultado'] = $q->resultado;
                        $model->llaves['ronda32']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }

                    foreach($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 16)->where('bloque', 3) as $q)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo_temporal : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-");
                        $bloque['jugador_rival'] = $q->jugadorRivalUno != null ? (!$TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-");
                        $bloque['resultado'] = $q->resultado;
                        $model->llaves['ronda32']['bloque_tres'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }

                    foreach($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 16)->where('bloque', 4) as $q)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo_temporal : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-");
                        $bloque['jugador_rival'] = $q->jugadorRivalUno != null ? (!$TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-");
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
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->resultado ;
                        $model->llaves['octavos']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                    $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['octavos']['bloque_uno'][] = (object)$bloque;
                    }

                    if($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first() != null){
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->resultado ;
                        $model->llaves['octavos']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                        $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['octavos']['bloque_uno'][] = (object)$bloque;
                    }

                    if($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first() != null){
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->resultado ;
                        $model->llaves['octavos']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                         $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['octavos']['bloque_dos'][] = (object)$bloque;
                    }

                    if($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first() != null){
                        $bloque = [];
                       $partido = $TorneoCategoria->torneo->partidos
                        ->where('torneo_categoria_id', $TorneoCategoria->id)
                        ->where('fase', 8)
                        ->where('bloque', 2)
                        ->where('position', 2)
                        ->first();
                                                $bloque['jugador_local'] = !$TorneoCategoria->manual && $partido->buy_all ? "BYE" : (
                            $partido->jugadorLocalUno != null ? (
                                $partido->multiple ? 
                                    $partido->jugadorLocalUno->nombre_completo_temporal . ' + ' . 
                                    ($partido->jugadorLocalDos ? $partido->jugadorLocalDos->nombre_completo : '-') 
                                    : $partido->jugadorLocalUno->nombre_completo_temporal
                            ) : "-"
                        );
                      //  $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorLocalUno->nombre_completo_temporal.' + ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorLocalDos != null ? . '$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorLocalDos->nombre_completo_temporal: '-') : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->resultado ;
                        $model->llaves['octavos']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                        
                           $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['octavos']['bloque_dos'][] = (object)$bloque;
                    }

                    if($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first() != null){
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->resultado ;
                        $model->llaves['octavos']['bloque_tres'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                    $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['octavos']['bloque_tres'][] = (object)$bloque;
                    }

                    if($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first() != null){
                        $bloque = [];
                      //  $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                    
                    $partido = $TorneoCategoria->torneo->partidos
    ->where('torneo_categoria_id', $TorneoCategoria->id)
    ->where('fase', 8)
    ->where('bloque', 3)
    ->where('position', 2)
    ->first();

if (!$TorneoCategoria->manual && $partido->buy_all) {
    $bloque['jugador_local'] = "BYE";
} else {
    $jugadorLocalUno = $partido->jugadorLocalUno;
    $jugadorLocalDos = $partido->jugadorLocalDos;

    if ($jugadorLocalUno != null) {
        if ($partido->multiple && $jugadorLocalDos != null) {
            $bloque['jugador_local'] = $jugadorLocalUno->nombre_completo_temporal . ' + ' . $jugadorLocalDos->nombre_completo_temporal;
        } else {
            $bloque['jugador_local'] = $jugadorLocalUno->nombre_completo_temporal;
        }
    } else {
        $bloque['jugador_local'] = "-";
    }
}
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->resultado ;
                        $model->llaves['octavos']['bloque_tres'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                    
                      $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                    
                        $model->llaves['octavos']['bloque_tres'][] =(object)$bloque;
                    }

                    if($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first() != null){
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->resultado ;
                        $model->llaves['octavos']['bloque_cuatro'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                          $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['octavos']['bloque_cuatro'][] = (object)$bloque;
                    }

                    if($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first() != null){
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->resultado ;
                        $model->llaves['octavos']['bloque_cuatro'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                          $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['octavos']['bloque_cuatro'][] = (object)$bloque;
                    }

                    $model->llaves['cuartos'] = [];
                    $model->llaves['cuartos']['bloque_uno'] = [];
                    $model->llaves['cuartos']['bloque_dos'] = [];
                    $model->llaves['cuartos']['bloque_tres'] = [];
                    $model->llaves['cuartos']['bloque_cuatro'] = [];

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->resultado;
                        $model->llaves['cuartos']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                          $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['cuartos']['bloque_uno'][] = (object)$bloque;
                    }

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->resultado;
                        $model->llaves['cuartos']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                          $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['cuartos']['bloque_dos'][] = (object)$bloque;
                    }

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->resultado;
                        $model->llaves['cuartos']['bloque_tres'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                          $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['cuartos']['bloque_tres'][] = (object)$bloque;
                    }

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->resultado;
                        $model->llaves['cuartos']['bloque_cuatro'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                          $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['cuartos']['bloque_cuatro'][] = (object)$bloque;
                    }

                    $model->llaves['semifinal'] = [];
                    $model->llaves['semifinal']['bloque_uno'] = [];
                    $model->llaves['semifinal']['bloque_dos'] = [];

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->resultado;
                        $model->llaves['semifinal']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                          $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['semifinal']['bloque_uno'][] = (object)$bloque;
                    }

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->resultado;
                        $model->llaves['semifinal']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                          $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['semifinal']['bloque_dos'][] = (object)$bloque;
                    }

                    $model->llaves['final'] = [];

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->resultado;
                        $model->llaves['final'] = (object)$bloque;
                    }else{
                          $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['final'][] = (object)$bloque;
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
                        $bloque['jugador_local'] = $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-");
                        $bloque['jugador_rival'] = $q->jugadorRivalUno != null ? (!$TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-");
                        $bloque['resultado'] = $q->resultado;
                        $model->llaves['octavos']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }

                    foreach($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2) as $q)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-");
                        $bloque['jugador_rival'] = $q->jugadorRivalUno != null ? (!$TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-");
                        $bloque['resultado'] = $q->resultado;
                        $model->llaves['octavos']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }

                    foreach($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3) as $q)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-");
                        $bloque['jugador_rival'] = $q->jugadorRivalUno != null ? (!$TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-");
                        $bloque['resultado'] = $q->resultado;
                        $model->llaves['octavos']['bloque_tres'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }

                    foreach($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4) as $q)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-");
                        $bloque['jugador_rival'] = $q->jugadorRivalUno != null ? (!$TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-");
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
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->resultado;
                        $model->llaves['cuartos']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                          $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['cuartos']['bloque_uno'][] = (object)$bloque;
                    }

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->resultado;
                        $model->llaves['cuartos']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                          $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['cuartos']['bloque_dos'][] = (object)$bloque;
                    }

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->resultado;
                        $model->llaves['cuartos']['bloque_tres'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                          $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['cuartos']['bloque_tres'][] = (object)$bloque;
                    }

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->resultado;
                        $model->llaves['cuartos']['bloque_cuatro'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                          $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['cuartos']['bloque_cuatro'][] = (object)$bloque;
                    }

                    $model->llaves['semifinal'] = [];
                    $model->llaves['semifinal']['bloque_uno'] = [];
                    $model->llaves['semifinal']['bloque_dos'] = [];

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->resultado;
                        $model->llaves['semifinal']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                          $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['semifinal']['bloque_uno'][] = (object)$bloque;
                    }

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->resultado;
                        $model->llaves['semifinal']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                          $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['semifinal']['bloque_dos'][] = (object)$bloque;
                    }

                    $model->llaves['final'] = [];

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->resultado;
                        $model->llaves['final'] = (object)$bloque;
                    }else{
                          $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['final'][] = (object)$bloque;
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
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->resultado;
                        $model->llaves['cuartos']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                          $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['cuartos']['bloque_uno'][] = (object)$bloque;
                    }

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->resultado;
                        $model->llaves['cuartos']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                          $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['cuartos']['bloque_dos'][] = (object)$bloque;
                    }

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->resultado;
                        $model->llaves['cuartos']['bloque_tres'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                          $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['cuartos']['bloque_tres'][] = (object)$bloque;
                    }

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->resultado;
                        $model->llaves['cuartos']['bloque_cuatro'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                          $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['cuartos']['bloque_cuatro'][] = (object)$bloque;
                    }

                    $model->llaves['semifinal'] = [];
                    $model->llaves['semifinal']['bloque_uno'] = [];
                    $model->llaves['semifinal']['bloque_dos'] = [];

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->resultado;
                        $model->llaves['semifinal']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                          $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['semifinal']['bloque_uno'][] = (object)$bloque;
                    }

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->resultado;
                        $model->llaves['semifinal']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                          $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['semifinal']['bloque_dos'][] = (object)$bloque;
                    }

                    $model->llaves['final'] = [];

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->resultado;
                        $model->llaves['final'] = (object)$bloque;
                    }else{
                          $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['final'][] = (object)$bloque;
                    }

                }else if($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 2)
                {
                    $model->llaves['semifinal'] = [];
                    $model->llaves['semifinal']['bloque_uno'] = [];
                    $model->llaves['semifinal']['bloque_dos'] = [];

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->resultado;
                        $model->llaves['semifinal']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object)$bloque : null;
                    }else{
                          $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['semifinal']['bloque_uno'][] = (object)$bloque;
                    }

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->resultado;
                        $model->llaves['semifinal']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object)$bloque : null;
                    }else{
                          $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['semifinal']['bloque_dos'][] = (object)$bloque;
                    }

                    $model->llaves['final'] = [];

                    if(count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)) > 0)
                    {
                        $bloque = [];
                        $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                        $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                        $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->resultado;
                        $model->llaves['final'] = (object)$bloque;
                    }else{
                          $bloque = [
                    'jugador_local' => '',
                    'jugador_rival' => '',
                    'resultado' => null
                    ];
                        $model->llaves['final'][] = (object)$bloque;
                    }
                }
            }
            
            
            
          
            $content = json_decode(json_encode($model), true);
            
            
           
            $content = $this->reorderBlocks($content);
            $content = json_encode($content);
        }

        Storage::disk('public')->put('public/uploads/keys/json.txt', $content);

        return redirect(App::$URL_JSON.'?json='.env('APP_URL').'/storage/public/uploads/keys/json.txt');


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
    
    
    
    
    
    
    
function reorderBlocks($data) {
    $order = ['bloque_uno', 'bloque_tres', 'bloque_dos', 'bloque_cuatro'];
    foreach ($data['llaves'] as $round => $blocks) {
        // Inicializar bloques nulos como un array vacío
        if ($blocks === null) {
            $blocks = [];
        }

        $reorderedBlocks = [];
        foreach ($order as $block) {
            if (isset($blocks[$block])) {
                $reorderedBlocks[$block] = $blocks[$block];
            } else {
                // Asegurar que el bloque esté presente aunque esté vacío
                $reorderedBlocks[$block] = [];
            }
        }

        // Agregar todos los bloques que no están en el orden y no son 'final'
        foreach ($blocks as $key => $value) {
            if (!in_array($key, $order) && $key !== 'final') {
                $reorderedBlocks[$key] = $value;
            }
        }

        // Agregar el bloque 'final' si existe
        if (isset($blocks['final'])) {
            $reorderedBlocks['final'] = $blocks['final'];
        }

        $data['llaves'][$round] = $reorderedBlocks;
    }
    return $data;
}



      
  public function exportMapaJsonFiguras(Request $request)
  {
      $TorneoCategoria = TorneoCategoria::where('id', $request->categoria)->where('torneo_id', $request->torneo)
          ->whereHas('torneo', function ($q) {
              $q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);
          })
          ->first();

      $content = "";

      if ($TorneoCategoria != null && $TorneoCategoria->torneo != null) {
          $model = (object) [
              'titulo' => 'La Confraternidad del Tenis',
              'torneo' => $TorneoCategoria->torneo->nombre,
              'formato' => $TorneoCategoria->torneo->formato != null ? $TorneoCategoria->torneo->formato->nombre : null,
              'ronda' => $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->whereNotNull('fase')->first()->fase * 2,
              'multiple' => $TorneoCategoria->multiple ? true : false,
              'damas' => str_contains(strtolower(($TorneoCategoria->multiple && ($TorneoCategoria->categoriaSimple->id !== $TorneoCategoria->categoriaDupla->id) ? ($TorneoCategoria->categoriaSimple->nombre . " + " . $TorneoCategoria->categoriaDupla->nombre) : ($TorneoCategoria->categoriaSimple->nombre) . "" . ($TorneoCategoria->multiple ? "" : ""))), 'damas') ? true : false,
              'categoria' => $TorneoCategoria->multiple && ($TorneoCategoria->categoriaSimple->id !== $TorneoCategoria->categoriaDupla->id) ? ($TorneoCategoria->categoriaSimple->nombre . " + " . $TorneoCategoria->categoriaDupla->nombre) : ($TorneoCategoria->categoriaSimple->nombre) . "" . ($TorneoCategoria->multiple ? "" : ""),
              'fecha_inicio' => Carbon::parse($TorneoCategoria->torneo->fecha_inicio)->format('Y-m-d'),
              'fecha_final' => Carbon::parse($TorneoCategoria->torneo->fecha_final)->format('Y-m-d'),
              'ganador' => $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first() != null ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first()->multiple ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first()->jugadorGanadorUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first()->jugadorGanadorDos->nombre_completo) : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first()->jugadorGanadorUno->nombre_completo_temporal : null,
              'llaves' => []
          ];

          if (count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->whereNotNull('fase')) > 0) {
              if ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 16) {
                  $model->llaves['ronda32'] = [];
                  $model->llaves['ronda32']['bloque_uno'] = [];
                  $model->llaves['ronda32']['bloque_dos'] = [];
                  $model->llaves['ronda32']['bloque_tres'] = [];
                  $model->llaves['ronda32']['bloque_cuatro'] = [];

                  foreach ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 16)->where('bloque', 1) as $q) {
                      $bloque = [];
                      $bloque['jugador_local'] = $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo_temporal : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-");
                      $bloque['jugador_rival'] = $q->jugadorRivalUno != null ? (!$TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-");
                      $bloque['resultado'] = $q->resultado;


                      $jugador_local_uno_id = $q->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $q->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      // Comprobar si las imágenes existen en Storage
                      $jugador_ganador_uno_id = $q->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;

                      



                      $model->llaves['ronda32']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object) $bloque : null;
                  }

                  foreach ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 16)->where('bloque', 2) as $q) {
                      $bloque = [];
                      $bloque['jugador_local'] = $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo_temporal : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-");
                      $bloque['jugador_rival'] = $q->jugadorRivalUno != null ? (!$TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-");
                      $bloque['resultado'] = $q->resultado;

                      $jugador_local_uno_id = $q->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $q->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      // Comprobar si las imágenes existen en Storage
                      $jugador_ganador_uno_id = $q->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;
                      $model->llaves['ronda32']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object) $bloque : null;
                  }

                  foreach ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 16)->where('bloque', 3) as $q) {
                      $bloque = [];
                      $bloque['jugador_local'] = $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo_temporal : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-");
                      $bloque['jugador_rival'] = $q->jugadorRivalUno != null ? (!$TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-");
                      $bloque['resultado'] = $q->resultado;

                      $jugador_local_uno_id = $q->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $q->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      // Comprobar si las imágenes existen en Storage
                      $jugador_ganador_uno_id = $q->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;
                      $model->llaves['ronda32']['bloque_tres'][] = ($request->type == 'full' || $request->type == 'left') ? (object) $bloque : null;
                  }

                  foreach ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 16)->where('bloque', 4) as $q) {
                      $bloque = [];
                      $bloque['jugador_local'] = $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo_temporal : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-");
                      $bloque['jugador_rival'] = $q->jugadorRivalUno != null ? (!$TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-");
                      $bloque['resultado'] = $q->resultado;

                      $jugador_local_uno_id = $q->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $q->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      // Comprobar si las imágenes existen en Storage
                      $jugador_ganador_uno_id = $q->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;
                      $model->llaves['ronda32']['bloque_cuatro'][] = ($request->type == 'full' || $request->type == 'right') ? (object) $bloque : null;
                  }

                  $model->llaves['octavos'] = [];
                  $model->llaves['octavos']['bloque_uno'] = [];
                  $model->llaves['octavos']['bloque_dos'] = [];
                  $model->llaves['octavos']['bloque_tres'] = [];
                  $model->llaves['octavos']['bloque_cuatro'] = [];

                  if ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first() != null) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->resultado;
                      
                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;

                      
                      // Comprobar si las imágenes existen en Storage
                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;
                      $model->llaves['octavos']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object) $bloque : null;
                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['octavos']['bloque_uno'][] = (object) $bloque;
                  }

                  if ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first() != null) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->resultado;


                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;

                      $model->llaves['octavos']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object) $bloque : null;
                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['octavos']['bloque_uno'][] = (object) $bloque;
                  }

                  if ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first() != null) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->resultado;


                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = Storage::url("imagenes/hombre.png");
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;
                      $model->llaves['octavos']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object) $bloque : null;


                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['octavos']['bloque_dos'][] = (object) $bloque;
                  }

                  if ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first() != null) {
                      $bloque = [];
                      $partido = $TorneoCategoria->torneo->partidos
                          ->where('torneo_categoria_id', $TorneoCategoria->id)
                          ->where('fase', 8)
                          ->where('bloque', 2)
                          ->where('position', 2)
                          ->first();
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $partido->buy_all ? "BYE" : (
                          $partido->jugadorLocalUno != null ? (
                              $partido->multiple ?
                              $partido->jugadorLocalUno->nombre_completo_temporal . ' + ' .
                              ($partido->jugadorLocalDos ? $partido->jugadorLocalDos->nombre_completo : '-')
                              : $partido->jugadorLocalUno->nombre_completo_temporal
                          ) : "-"
                      );
                      //  $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorLocalUno->nombre_completo_temporal.' + ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorLocalDos != null ? . '$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorLocalDos->nombre_completo_temporal: '-') : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->resultado;
                     
                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;

                     
                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;

                      $model->llaves['octavos']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object) $bloque : null;
                  } else {

                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['octavos']['bloque_dos'][] = (object) $bloque;
                  }

                  if ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first() != null) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->resultado;


                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;

                      $model->llaves['octavos']['bloque_tres'][] = ($request->type == 'full' || $request->type == 'left') ? (object) $bloque : null;
                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['octavos']['bloque_tres'][] = (object) $bloque;
                  }

                  if ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first() != null) {
                      $bloque = [];
                      //  $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");

                      $partido = $TorneoCategoria->torneo->partidos
                          ->where('torneo_categoria_id', $TorneoCategoria->id)
                          ->where('fase', 8)
                          ->where('bloque', 3)
                          ->where('position', 2)
                          ->first();

                      if (!$TorneoCategoria->manual && $partido->buy_all) {
                          $bloque['jugador_local'] = "BYE";
                      } else {
                          $jugadorLocalUno = $partido->jugadorLocalUno;
                          $jugadorLocalDos = $partido->jugadorLocalDos;

                          if ($jugadorLocalUno != null) {
                              if ($partido->multiple && $jugadorLocalDos != null) {
                                  $bloque['jugador_local'] = $jugadorLocalUno->nombre_completo_temporal . ' + ' . $jugadorLocalDos->nombre_completo_temporal;
                              } else {
                                  $bloque['jugador_local'] = $jugadorLocalUno->nombre_completo_temporal;
                              }
                          } else {
                              $bloque['jugador_local'] = "-";
                          }
                      }
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->resultado;


                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;
                      $model->llaves['octavos']['bloque_tres'][] = ($request->type == 'full' || $request->type == 'left') ? (object) $bloque : null;
                  } else {

                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];

                      $model->llaves['octavos']['bloque_tres'][] = (object) $bloque;
                  }

                  if ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first() != null) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->resultado;


                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;
                      $model->llaves['octavos']['bloque_cuatro'][] = ($request->type == 'full' || $request->type == 'right') ? (object) $bloque : null;
                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['octavos']['bloque_cuatro'][] = (object) $bloque;
                  }

                  if ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first() != null) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->resultado;


                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;

                      $model->llaves['octavos']['bloque_cuatro'][] = ($request->type == 'full' || $request->type == 'right') ? (object) $bloque : null;
                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['octavos']['bloque_cuatro'][] = (object) $bloque;
                  }

                  $model->llaves['cuartos'] = [];
                  $model->llaves['cuartos']['bloque_uno'] = [];
                  $model->llaves['cuartos']['bloque_dos'] = [];
                  $model->llaves['cuartos']['bloque_tres'] = [];
                  $model->llaves['cuartos']['bloque_cuatro'] = [];

                  if (count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)) > 0) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->resultado;


                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;

                      $model->llaves['cuartos']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object) $bloque : null;
                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['cuartos']['bloque_uno'][] = (object) $bloque;
                  }

                  if (count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)) > 0) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->resultado;


                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;

                      $model->llaves['cuartos']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object) $bloque : null;
                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['cuartos']['bloque_dos'][] = (object) $bloque;
                  }

                  if (count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)) > 0) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->resultado;
                     
                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;

                      $model->llaves['cuartos']['bloque_tres'][] = ($request->type == 'full' || $request->type == 'left') ? (object) $bloque : null;
                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['cuartos']['bloque_tres'][] = (object) $bloque;
                  }

                  if (count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)) > 0) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->resultado;


                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;

                      $model->llaves['cuartos']['bloque_cuatro'][] = ($request->type == 'full' || $request->type == 'right') ? (object) $bloque : null;
                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['cuartos']['bloque_cuatro'][] = (object) $bloque;
                  }

                  $model->llaves['semifinal'] = [];
                  $model->llaves['semifinal']['bloque_uno'] = [];
                  $model->llaves['semifinal']['bloque_dos'] = [];

                  if (count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)) > 0) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->resultado;
                 
                      $bloque['resultado_anterior_bloque1'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->resultado;
                      $bloque['resultado_anterior_bloque2'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->resultado;
                      $bloque['resultado_anterior_bloque3'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->resultado;
                      $bloque['resultado_anterior_bloque4'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->resultado;

                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;

                      $bloque['resultado_anterior_bloque1'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->resultado;
                      $bloque['resultado_anterior_bloque2'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->resultado;
                      $bloque['resultado_anterior_bloque3'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->resultado;
                      $bloque['resultado_anterior_bloque4'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->resultado;


                      $model->llaves['semifinal']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object) $bloque : null;
                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['semifinal']['bloque_uno'][] = (object) $bloque;
                  }

                  if (count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)) > 0) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->resultado;
                      $bloque['resultado_anterior_bloque1'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->resultado;
                      $bloque['resultado_anterior_bloque3'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->resultado;

                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;

                      $model->llaves['semifinal']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object) $bloque : null;
                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['semifinal']['bloque_dos'][] = (object) $bloque;
                  }

                  $model->llaves['final'] = [];

                  if (count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)) > 0) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->resultado;
                      $bloque['resultado_anterior_bloque1'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->resultado;                      
                      $bloque['resultado_anterior_bloque2'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->resultado;  



                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;

                      $model->llaves['final'] = (object) $bloque;
                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['final'][] = (object) $bloque;
                  }

              } else if ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 8) {
                  $model->llaves['octavos'] = [];
                  $model->llaves['octavos']['bloque_uno'] = [];
                  $model->llaves['octavos']['bloque_dos'] = [];
                  $model->llaves['octavos']['bloque_tres'] = [];
                  $model->llaves['octavos']['bloque_cuatro'] = [];

                  foreach ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 1) as $q) {
                      $bloque = [];
                      $bloque['jugador_local'] = $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-");
                      $bloque['jugador_rival'] = $q->jugadorRivalUno != null ? (!$TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-");
                      $bloque['resultado'] = $q->resultado;

                      $jugador_local_uno_id = $q->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $q->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $q->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;

                      $model->llaves['octavos']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object) $bloque : null;
                  }

                  foreach ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 2) as $q) {
                      $bloque = [];
                      $bloque['jugador_local'] = $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-");
                      $bloque['jugador_rival'] = $q->jugadorRivalUno != null ? (!$TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-");
                      $bloque['resultado'] = $q->resultado;

                      $jugador_local_uno_id = $q->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $q->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $q->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;
                      $model->llaves['octavos']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object) $bloque : null;
                  }

                  foreach ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 3) as $q) {
                      $bloque = [];
                      $bloque['jugador_local'] = $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-");
                      $bloque['jugador_rival'] = $q->jugadorRivalUno != null ? (!$TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-");
                      $bloque['resultado'] = $q->resultado;


                      $jugador_local_uno_id = $q->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $q->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $q->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;
                      $model->llaves['octavos']['bloque_tres'][] = ($request->type == 'full' || $request->type == 'left') ? (object) $bloque : null;
                  }

                  foreach ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 8)->where('bloque', 4) as $q) {
                      $bloque = [];
                      $bloque['jugador_local'] = $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-") . ' + ' . ($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo_temporal : "-")) : ($q->buy_all ? "BYE" : "-");
                      $bloque['jugador_rival'] = $q->jugadorRivalUno != null ? (!$TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-") . ' + ' . ($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo_temporal : "-"))) : ($q->buy ? "BYE" : "-");
                      $bloque['resultado'] = $q->resultado;


                      $jugador_local_uno_id = $q->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $q->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $q->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;
                      $model->llaves['octavos']['bloque_cuatro'][] = ($request->type == 'full' || $request->type == 'right') ? (object) $bloque : null;
                  }

                  $model->llaves['cuartos'] = [];
                  $model->llaves['cuartos']['bloque_uno'] = [];
                  $model->llaves['cuartos']['bloque_dos'] = [];
                  $model->llaves['cuartos']['bloque_tres'] = [];
                  $model->llaves['cuartos']['bloque_cuatro'] = [];

                  if (count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)) > 0) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->resultado;


                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;
                      $model->llaves['cuartos']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object) $bloque : null;
                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['cuartos']['bloque_uno'][] = (object) $bloque;
                  }

                  if (count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)) > 0) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->resultado;


                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;

                      $model->llaves['cuartos']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object) $bloque : null;
                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['cuartos']['bloque_dos'][] = (object) $bloque;
                  }

                  if (count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)) > 0) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->resultado;
                     
                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;

                     
                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;
                      $model->llaves['cuartos']['bloque_tres'][] = ($request->type == 'full' || $request->type == 'left') ? (object) $bloque : null;


                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['cuartos']['bloque_tres'][] = (object) $bloque;
                  }

                  if (count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)) > 0) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->resultado;


                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;

                      $model->llaves['cuartos']['bloque_cuatro'][] = ($request->type == 'full' || $request->type == 'right') ? (object) $bloque : null;
                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['cuartos']['bloque_cuatro'][] = (object) $bloque;
                  }

                  $model->llaves['semifinal'] = [];
                  $model->llaves['semifinal']['bloque_uno'] = [];
                  $model->llaves['semifinal']['bloque_dos'] = [];

                  if (count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)) > 0) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->resultado;

                      $bloque['resultado_anterior_bloque1'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->resultado;
                      $bloque['resultado_anterior_bloque2'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->resultado;
                      $bloque['resultado_anterior_bloque3'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->resultado;
                      $bloque['resultado_anterior_bloque4'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->resultado;

                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;

                      $model->llaves['semifinal']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object) $bloque : null;
                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['semifinal']['bloque_uno'][] = (object) $bloque;
                  }

                  if (count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)) > 0) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->resultado;
                      $bloque['resultado_anterior_bloque1'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->resultado;
                      $bloque['resultado_anterior_bloque3'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->resultado;
                   

                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;

                      $model->llaves['semifinal']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object) $bloque : null;
                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['semifinal']['bloque_dos'][] = (object) $bloque;
                  }

                  $model->llaves['final'] = [];

                  if (count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)) > 0) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->resultado;
                      $bloque['resultado_anterior_bloque1'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->resultado;                      
                      $bloque['resultado_anterior_bloque2'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->resultado;  

                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;

                      $model->llaves['final'] = (object) $bloque;
                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['final'][] = (object) $bloque;
                  }

              } else if ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 4) {
                  $model->llaves['cuartos'] = [];
                  $model->llaves['cuartos']['bloque_uno'] = [];
                  $model->llaves['cuartos']['bloque_dos'] = [];
                  $model->llaves['cuartos']['bloque_tres'] = [];
                  $model->llaves['cuartos']['bloque_cuatro'] = [];

                  if (count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)) > 0) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->resultado;


                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;

                      $model->llaves['cuartos']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object) $bloque : null;
                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['cuartos']['bloque_uno'][] = (object) $bloque;
                  }

                  if (count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)) > 0) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->resultado;


                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;
                      $model->llaves['cuartos']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object) $bloque : null;

                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['cuartos']['bloque_dos'][] = (object) $bloque;
                  }

                  if (count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)) > 0) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->resultado;


                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;

                      $model->llaves['cuartos']['bloque_tres'][] = ($request->type == 'full' || $request->type == 'left') ? (object) $bloque : null;
                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['cuartos']['bloque_tres'][] = (object) $bloque;
                  }

                  if (count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)) > 0) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->resultado;


                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;

                      $model->llaves['cuartos']['bloque_cuatro'][] = ($request->type == 'full' || $request->type == 'right') ? (object) $bloque : null;
                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['cuartos']['bloque_cuatro'][] = (object) $bloque;
                  }

                  $model->llaves['semifinal'] = [];
                  $model->llaves['semifinal']['bloque_uno'] = [];
                  $model->llaves['semifinal']['bloque_dos'] = [];

                  if (count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)) > 0) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->resultado;

                      $bloque['resultado_anterior_bloque1'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->resultado;
                      $bloque['resultado_anterior_bloque2'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->resultado;
                      $bloque['resultado_anterior_bloque3'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->resultado;
                      $bloque['resultado_anterior_bloque4'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->resultado;

                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;

                      $model->llaves['semifinal']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object) $bloque : null;

                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['semifinal']['bloque_uno'][] = (object) $bloque;
                  }

                  if (count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)) > 0) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->resultado;
                      $bloque['resultado_anterior_bloque1'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->resultado;
                      $bloque['resultado_anterior_bloque3'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->resultado;
                   

                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;
                      $model->llaves['semifinal']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object) $bloque : null;

                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['semifinal']['bloque_dos'][] = (object) $bloque;
                  }

                  $model->llaves['final'] = [];

                  if (count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)) > 0) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->resultado;
                      $bloque['resultado_anterior_bloque1'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->resultado;                      
                      $bloque['resultado_anterior_bloque2'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->resultado;  


                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;

                      $model->llaves['final'] = (object) $bloque;

                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['final'][] = (object) $bloque;
                  }

              } else if ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 2) {
                  $model->llaves['semifinal'] = [];
                  $model->llaves['semifinal']['bloque_uno'] = [];
                  $model->llaves['semifinal']['bloque_dos'] = [];

                  if (count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)) > 0) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->resultado;

                      $bloque['resultado_anterior_bloque1'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->resultado;
                      $bloque['resultado_anterior_bloque2'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->resultado;
                      $bloque['resultado_anterior_bloque3'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->resultado;
                      $bloque['resultado_anterior_bloque4'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->resultado;

                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;

                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;
                      $model->llaves['semifinal']['bloque_uno'][] = ($request->type == 'full' || $request->type == 'left') ? (object) $bloque : null;

                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['semifinal']['bloque_uno'][] = (object) $bloque;
                  }

                  if (count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)) > 0) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->resultado;
                      $bloque['resultado_anterior_bloque1'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->resultado;
                      $bloque['resultado_anterior_bloque3'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->resultado;
                   

                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;

                      $model->llaves['semifinal']['bloque_dos'][] = ($request->type == 'full' || $request->type == 'right') ? (object) $bloque : null;
                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['semifinal']['bloque_dos'][] = (object) $bloque;
                  }

                  $model->llaves['final'] = [];

                  if (count($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)) > 0) {
                      $bloque = [];
                      $bloque['jugador_local'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->buy_all ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo_temporal) : "-");
                      $bloque['jugador_rival'] = !$TorneoCategoria->manual && $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->buy ? "BYE" : ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno != null ? ($TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo . ' + ' . $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo_temporal) : "-");
                      $bloque['resultado'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->resultado;
                      $bloque['resultado_anterior_bloque1'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->resultado;                      
                      $bloque['resultado_anterior_bloque2'] = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->resultado;  

                      $jugador_local_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugador_local_uno_id;
                      $jugador_local_imagen = "";
                      if($jugador_local_uno_id) {
                          $jugador = Jugador::find($jugador_local_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_local_uno_id}.png")) {
                                  $jugador_local_imagen = Storage::url("uploads/img/jugador_{$jugador_local_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_local_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_local_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_local_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_local_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_local_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_local_imagen'] = $jugador_local_imagen;
                      $bloque['jugador_local_uno_id'] = $jugador_local_uno_id;

                      $jugador_rival_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugador_rival_uno_id;
                      $jugador_rival_imagen = "";
                      if($jugador_rival_uno_id) {
                          $jugador = Jugador::find($jugador_rival_uno_id);
                          if($jugador) {
                              if(Storage::disk('public')->exists("uploads/img/jugador_{$jugador_rival_uno_id}.png")) {
                                  $jugador_rival_imagen = Storage::url("uploads/img/jugador_{$jugador_rival_uno_id}.png");
                              } else {
                                  if($jugador->sexo == 'M') {
                                      $jugador_rival_imagen = "images/hombre.png";
                                  } else if($jugador->sexo == 'F') {
                                      $jugador_rival_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_rival_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_rival_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_rival_imagen = "images/incognito.png";
                      }

                      $bloque['jugador_rival_imagen'] = $jugador_rival_imagen;
                      $bloque['jugador_rival_uno_id'] = $jugador_rival_uno_id;


                      $jugador_ganador_uno_id = $TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoCategoria->id)->where('fase', 1)->first()->jugador_ganador_uno_id;
                      $jugador_ganador_imagen = "";
                      if ($jugador_ganador_uno_id) {
                          $jugador = Jugador::find($jugador_ganador_uno_id);
                          if ($jugador) {
                              if (Storage::disk('public')->exists("uploads/img/jugador_{$jugador_ganador_uno_id}.png")) {
                                  $jugador_ganador_imagen = Storage::url("uploads/img/jugador_{$jugador_ganador_uno_id}.png");
                              } else {
                                  if ($jugador->sexo == 'M') {
                                      $jugador_ganador_imagen = "images/hombre.png";
                                  } else if ($jugador->sexo == 'F') {
                                      $jugador_ganador_imagen = "images/mujer.png";
                                  } else {
                                      $jugador_ganador_imagen = "images/incognito.png";
                                  }
                              }
                          } else {
                              $jugador_ganador_imagen = "images/incognito.png";
                          }
                      } else {
                          $jugador_ganador_imagen = "images/incognito.png";
                      }
                      $bloque['jugador_ganador_imagen'] = $jugador_ganador_imagen;
                      $bloque['jugador_ganador_uno_id'] = $jugador_ganador_uno_id;

                      $model->llaves['final'] = (object) $bloque;
                  } else {
                      $bloque = [
                          'jugador_local' => '',
                          'jugador_rival' => '',
                          'resultado' => null
                      ];
                      $model->llaves['final'][] = (object) $bloque;
                  }
              }
          }




          $content = json_decode(json_encode($model), true);



          $content = $this->reorderBlocks($content);
          $content = json_encode($content);
      }

      Storage::disk('public')->put('public/uploads/keys/json.txt', $content);

      return redirect(App::$URL_JSON_MAP_FIGURAS . '?json=' . env('APP_URL') . '/storage/public/uploads/keys/json.txt');

  }
    


    

   public function exportJugadorJson(Request $request)
    {
        $TorneoCategoria = TorneoCategoria::where('id', $request->categoria)->where('torneo_id', $request->torneo)
            ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
            ->first();

        $content = "";

        if($TorneoCategoria != null)
        {
            $rankings = $this->rankingsByCategoryId($TorneoCategoria->categoria_simple_id);

            $TorneoJugadores = TorneoJugador::where('torneo_categoria_id', $request->categoria)->where('torneo_id', $request->torneo)
                ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
                ->get();

            if($TorneoJugadores != null && count($TorneoJugadores) > 0)
            {
                $model = (object)[
                    'titulo' => 'La Confraternidad del Tenis',
                    'torneo' => $TorneoCategoria->torneo->nombre,
                    'formato' => $TorneoCategoria->torneo->formato != null ? $TorneoCategoria->torneo->formato->nombre : null,
                    'multiple' => $TorneoCategoria->multiple ? true : false,
                    'categoria' => $TorneoCategoria->multiple && ($TorneoCategoria->categoriaSimple->id !== $TorneoCategoria->categoriaDupla->id) ? ($TorneoCategoria->categoriaSimple->nombre . " + " . $TorneoCategoria->categoriaDupla->nombre) : ($TorneoCategoria->categoriaSimple->nombre) . "" . ($TorneoCategoria->multiple ? "" : ""),
                    'fecha_inicio' => Carbon::parse($TorneoCategoria->torneo->fecha_inicio)->format('Y-m-d'),
                    'fecha_final' => Carbon::parse($TorneoCategoria->torneo->fecha_final)->format('Y-m-d'),
                    'jugadores' => []
                ];

                foreach ($TorneoJugadores as $q)
                {
                    //$model->jugadores[] = $TorneoCategoria->multiple ? ($q->jugadorSimple->nombre_completo_temporal.' + '.$q->jugadorDupla->nombre_completo_temporal) : $q->jugadorSimple->nombre_completo_temporal;

                    $Jugadores['nombresv1'] = $TorneoCategoria->multiple ? ($q->jugadorSimple->nombres): $q->jugadorSimple->nombres;
                    $Jugadores['apellidosv1'] = $TorneoCategoria->multiple ? ($q->jugadorSimple->apellidos): $q->jugadorSimple->apellidos . (preg_match('/\(([^)]+)\)/', $q->jugadorSimple->nombre_completo_temporal, $matches) ? ' (' . $matches[1] . ')' : '');
                    $Jugadores['nombresv2'] = $TorneoCategoria->multiple ? ($q->jugadorDupla->nombres) : null;
                    $Jugadores['apellidosv2'] = $TorneoCategoria->multiple ? ($q->jugadorDupla->apellidos) : null;

                    $model->jugadores[] =  $Jugadores;
                }

                $content = json_encode($model);
            }
        }

        Storage::disk('public')->put('public/uploads/players/json.txt', $content);

        return redirect(App::$URL_JSON_PLAYERS.'?json='.env('APP_URL').'/storage/public/uploads/players/json.txt');
    }

    public function exportGrupoJson(Request $request)
    {
        
           
        $TorneoCategoria = TorneoCategoria::where('id', $request->categoria)->where('torneo_id', $request->torneo)
            ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
            ->first();
        
        $content = "";

        if($TorneoCategoria != null)
        {
            $rankings = $this->rankingsByCategoryId($TorneoCategoria->categoria_simple_id);
            $TorneoGruposUnicos = TorneoGrupo::where('torneo_categoria_id', $request->categoria)->where('torneo_id', $request->torneo)
                ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
                ->select(['nombre_grupo', 'grupo_id'])->groupBy(['nombre_grupo', 'grupo_id'])
                ->orderBy(DB::raw('LENGTH(nombre_grupo)'))->orderBy('nombre_grupo')->get();

            if($TorneoGruposUnicos != null && count($TorneoGruposUnicos) > 0)
            {
                $model = (object)[
                    'titulo' => 'La Confraternidad del Tenis',
                    'torneo' => $TorneoCategoria->torneo->nombre,
                    'formato' => $TorneoCategoria->torneo->formato != null ? $TorneoCategoria->torneo->formato->nombre : null,
                    'multiple' => $TorneoCategoria->multiple ? true : false,
                    'categoria' => $TorneoCategoria->multiple && ($TorneoCategoria->categoriaSimple->id !== $TorneoCategoria->categoriaDupla->id) ? ($TorneoCategoria->categoriaSimple->nombre . " + " . $TorneoCategoria->categoriaDupla->nombre) : ($TorneoCategoria->categoriaSimple->nombre) . "" . ($TorneoCategoria->multiple ? "" : ""),
                    'fecha_inicio' => Carbon::parse($TorneoCategoria->torneo->fecha_inicio)->format('Y-m-d'),
                    'fecha_final' => Carbon::parse($TorneoCategoria->torneo->fecha_final)->format('Y-m-d'),
                    'grupos' => []
                ];

                $TorneoGrupos = TorneoGrupo::where('torneo_categoria_id', $request->categoria)->where('torneo_id', $request->torneo)
                ->whereHas('torneo', function ($q){$q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);})
                ->get();

                foreach ($TorneoGruposUnicos as $key => $q)
                {
                    $model->grupos[$key]['nombre'] = $q->nombre_grupo;
                    foreach ($TorneoGrupos->where('grupo_id', $q->grupo_id) as $q2){

                        $Jugadores['nombresv1'] = $TorneoCategoria->multiple ? ($q2->jugadorSimple->nombres) : $q2->jugadorSimple->nombres;
                   //     $Jugadores['apellidosv1'] = $TorneoCategoria->multiple ? ($q->jugadorSimple->apellidos): $q->jugadorSimple->apellidos . (preg_match('/\(([^)]+)\)/', $q->jugadorSimple->nombre_completo_temporal, $matches) ? ' ' . $matches[1] : '');
                        $Jugadores['apellidosv1'] = $TorneoCategoria->multiple ? ($q2->jugadorSimple->apellidos) : $q2->jugadorSimple->apellidos . (preg_match('/\(([^)]+)\)/', $q2->jugadorSimple->nombre_completo_temporal, $matches) ? ' (' . $matches[1] . ')' : '');
                        $Jugadores['nombresv2'] = $TorneoCategoria->multiple ? ($q2->jugadorDupla->nombres) :null;
                        $Jugadores['apellidosv2'] = $TorneoCategoria->multiple ? ($q2->jugadorDupla->apellidos) : null;


                        $model->grupos[$key]['jugadores'][] =  $Jugadores;

                        //$model->grupos[$key]['jugadores'][] = $TorneoCategoria->multiple ? ($q2->jugadorSimple->nombre_completo.' + '.$q2->jugadorDupla->nombre_completo) : $q2->jugadorSimple->nombre_completo;
                    }
                }

                $content = json_encode($model);
            }
        }
    
    
        Storage::disk('public')->put('public/uploads/groups/json.txt', $content);

        return redirect(App::$URL_JSON_GROUPS.'?json='.env('APP_URL').'/storage/public/uploads/groups/json.txt');
    }
    
    
    public function getFase($numero)
    {
        if (is_null($numero)) {
            return 'Fase de grupos';
        }

        $fases = [
            1 => 'Final',
            2 => 'Semifinal',
            4 => 'Cuartos de final',
            8 => 'Octavos de final',
            16 => '1/16 de final'
        ];

        return $fases[$numero] ?? 'Fase desconocida';
    }
    
    
    public function h2h(Request $request){

      $jugador_local_uno_id = $request->jugador_local_uno_id;
      $jugador_rival_uno_id = $request->jugador_rival_uno_id;
      $torneo_categoria_id = $request->torneo_categoria_id;
      $jugadorDatosLocal = Jugador::where('id', $jugador_local_uno_id)->first();
      $jugadorDatosRival = Jugador::where('id', $jugador_rival_uno_id)->first();
      $categoria_simple_id = TorneoCategoria::where('id', $torneo_categoria_id)->first()->categoria_simple_id;
      $jugador_local_edad = Carbon::parse($jugadorDatosLocal->fecha_nacimiento)->age;
      $jugador_rival_edad = Carbon::parse($jugadorDatosRival->fecha_nacimiento)->age;
      $torneo = Torneo::where('id', TorneoCategoria::where('id', $torneo_categoria_id)->first()->torneo_id)->first()->nombre;
      $categoria = Categoria::where('id', $categoria_simple_id)->first()->nombre;

      
    // Ruta de imágenes
    $folderPath = 'uploads/img';
    $jugador_local_imagen = null;
    $jugador_rival_imagen = null;

    // Comprobar si las imágenes existen en Storage
    if (Storage::disk('public')->exists("{$folderPath}/jugador_{$jugador_local_uno_id}.png")) {
        $jugador_local_imagen = Storage::url("{$folderPath}/jugador_{$jugador_local_uno_id}.png");
    }

    if (Storage::disk('public')->exists("{$folderPath}/jugador_{$jugador_rival_uno_id}.png")) {
        $jugador_rival_imagen = Storage::url("{$folderPath}/jugador_{$jugador_rival_uno_id}.png");
    }
      

      $resultados_vs =[];

      $Partidos = Partido::where(function ($query) use ($jugador_local_uno_id) {
        $query->where('jugador_local_uno_id', $jugador_local_uno_id)
              ->orWhere('jugador_rival_uno_id', $jugador_local_uno_id);
    })
    ->join('jugadors as jugador_local', 'partidos.jugador_local_uno_id', '=', 'jugador_local.id')
    ->join('jugadors as jugador_rival', 'partidos.jugador_rival_uno_id', '=', 'jugador_rival.id')
    ->join('torneos', 'partidos.torneo_id', '=', 'torneos.id')
    ->join('torneo_categorias', 'partidos.torneo_categoria_id', '=', 'torneo_categorias.id') // Join con la tabla torneo_categoria
    ->join('categorias', 'torneo_categorias.categoria_simple_id', '=', 'categorias.id') // Join con la tabla categorias
    ->select(
        'partidos.*',
        DB::raw("CONCAT(jugador_local.nombres, ' ', jugador_local.apellidos) as nombre_local"),
        DB::raw("CONCAT(jugador_rival.nombres, ' ', jugador_rival.apellidos) as nombre_rival"),
        'torneos.nombre as nombre_torneo',
        'torneos.fecha_inicio as fecha_inicio_torneo',
        'torneos.fecha_final as fecha_final_torneo',
        'categorias.nombre as nombre_categoria', // Seleccionar el nombre de la categoría
        'categorias.id as categoria_id', // Seleccionar el id de la categoría
        'jugador_local.mano_habil as mano_habil_local',
        'jugador_rival.mano_habil as mano_habil_rival',
        DB::raw("TIMESTAMPDIFF(YEAR, jugador_local.fecha_nacimiento, CURDATE()) as edad_local"),
        DB::raw("TIMESTAMPDIFF(YEAR, jugador_rival.fecha_nacimiento, CURDATE()) as edad_rival"),
        'jugador_local.altura as tamano_local',
        'jugador_rival.altura as tamano_rival',

        
    )
    ->whereNotNull('partidos.resultado')
    ->where(function ($query) {
       $query->whereRaw("LOWER(partidos.resultado) NOT LIKE '%wo%'");
    })   // ->where('torneo_categorias.id',$torneo_categoria_id)
    ->where('partidos.multiple',0)
    ->where('resultado', '<>', '-')
    ->orderBy('partidos.fecha_final', 'desc')
    ->take(5)
    ->get();


    $Partidos_rival = Partido::where(function ($query) use ($jugador_rival_uno_id) {
        $query->where('jugador_local_uno_id', $jugador_rival_uno_id)
              ->orWhere('jugador_rival_uno_id', $jugador_rival_uno_id);
    })
    ->join('jugadors as jugador_local', 'partidos.jugador_local_uno_id', '=', 'jugador_local.id')
    ->join('jugadors as jugador_rival', 'partidos.jugador_rival_uno_id', '=', 'jugador_rival.id')
    ->join('torneos', 'partidos.torneo_id', '=', 'torneos.id')
    ->join('torneo_categorias', 'partidos.torneo_categoria_id', '=', 'torneo_categorias.id') // Join con la tabla torneo_categoria
    ->join('categorias', 'torneo_categorias.categoria_simple_id', '=', 'categorias.id') // Join con la tabla categorias
    ->select(
        'partidos.*',
        DB::raw("CONCAT(jugador_local.nombres, ' ', jugador_local.apellidos) as nombre_local"),
        DB::raw("CONCAT(jugador_rival.nombres, ' ', jugador_rival.apellidos) as nombre_rival"),
        'torneos.nombre as nombre_torneo',
        'torneos.fecha_inicio as fecha_inicio_torneo',
        'torneos.fecha_final as fecha_final_torneo',
        'categorias.nombre as nombre_categoria', // Seleccionar el nombre de la categoría
        'categorias.id as categoria_id', // Seleccionar el id de la categoría
        'jugador_local.mano_habil as mano_habil_local',
        'jugador_rival.mano_habil as mano_habil_rival',
        DB::raw("TIMESTAMPDIFF(YEAR, jugador_local.fecha_nacimiento, CURDATE()) as edad_local"),
        DB::raw("TIMESTAMPDIFF(YEAR, jugador_rival.fecha_nacimiento, CURDATE()) as edad_rival"),
        'jugador_local.altura as tamano_local',
        'jugador_rival.altura as tamano_rival',

        
    )
    ->whereNotNull('partidos.resultado')
    ->where(function ($query) {
        $query->whereRaw("LOWER(partidos.resultado) NOT LIKE '%wo%'");
    })
    ->where('partidos.multiple',0)
    ->where('resultado', '<>', '-')
  //  ->where('torneo_categorias.id',$torneo_categoria_id)
    ->orderBy('partidos.fecha_final', 'desc')
    ->take(5)
    ->get();



    
    
    $Partidos_vs = Partido::where(function ($query) use ($jugador_local_uno_id, $jugador_rival_uno_id) {
        $query->where(function ($query) use ($jugador_local_uno_id, $jugador_rival_uno_id) {
            $query->where('jugador_local_uno_id', $jugador_local_uno_id)
                  ->where('jugador_rival_uno_id', $jugador_rival_uno_id);
        })->orWhere(function ($query) use ($jugador_local_uno_id, $jugador_rival_uno_id) {
            $query->where('jugador_local_uno_id', $jugador_rival_uno_id)
                  ->where('jugador_rival_uno_id', $jugador_local_uno_id);
        });
    })
    ->join('jugadors as jugador_local', 'partidos.jugador_local_uno_id', '=', 'jugador_local.id')
    ->join('jugadors as jugador_rival', 'partidos.jugador_rival_uno_id', '=', 'jugador_rival.id')
    ->join('torneos', 'partidos.torneo_id', '=', 'torneos.id')
    ->join('torneo_categorias', 'partidos.torneo_categoria_id', '=', 'torneo_categorias.id') // Join con la tabla torneo_categoria
    ->join('categorias', 'torneo_categorias.categoria_simple_id', '=', 'categorias.id') // Join con la tabla categorias
    ->select(
        'partidos.*',
        DB::raw("CONCAT(jugador_local.nombres, ' ', jugador_local.apellidos) as nombre_local"),
        DB::raw("CONCAT(jugador_rival.nombres, ' ', jugador_rival.apellidos) as nombre_rival"),
        'torneos.nombre as nombre_torneo',
        'torneos.fecha_inicio as fecha_inicio_torneo',
        'torneos.fecha_final as fecha_final_torneo',
        'categorias.nombre as nombre_categoria', // Seleccionar el nombre de la categoría
        'categorias.id as categoria_id', // Seleccionar el id de la categoría
        'jugador_local.mano_habil as mano_habil_local',
        'jugador_rival.mano_habil as mano_habil_rival',
        DB::raw("TIMESTAMPDIFF(YEAR, jugador_local.fecha_nacimiento, CURDATE()) as edad_local"),
        DB::raw("TIMESTAMPDIFF(YEAR, jugador_rival.fecha_nacimiento, CURDATE()) as edad_rival"),
        'jugador_local.altura as tamano_local',
        'jugador_rival.altura as tamano_rival',

        
    )
    ->whereNotNull('partidos.resultado')
    ->where('resultado', '<>', '-')
    ->where('partidos.multiple',0)
   // ->where('torneo_categorias.id',$torneo_categoria_id)
    ->orderBy('partidos.fecha_final', 'desc')
    ->get();
    
    $victorias_local_vs = 0;
    $victorias_rival_vs = 0;

    $this->rankingsByCategoryId($categoria_simple_id,true);

        $content = "";

        if($Partidos != null)
        {
            foreach ($Partidos as $partido) {
                $resultado = [
                    'jugador_local' => $partido->jugador_ganador_uno_id == $partido->jugador_local_uno_id ? $partido->nombre_local : $partido->nombre_rival,
                    'jugador_rival' => $partido->jugador_ganador_uno_id != $partido->jugador_rival_uno_id ? $partido->nombre_rival : $partido->nombre_local,
                    'jugador_local_id' => $partido->jugador_local_uno_id,
                    'jugador_rival_id' => $partido->jugador_rival_uno_id,
                    'torneo' => $partido->nombre_torneo,
                    'fecha' => $partido->fecha_inicio_torneo . ' - ' . $partido->fecha_final_torneo,
                    'fecha_final'   => $partido->fecha_final_torneo,
                    'fecha_inicio'   => $partido->fecha_inicio_torneo,
                    'resultado' => $partido->resultado, // Asumiendo que hay un campo resultado en la tabla partidos
                    'jugador_local_set' => $partido->jugador_local_set,
                    'jugador_rival_set' => $partido->jugador_rival_set,
                    'gano' => $partido->jugador_ganador_uno_id == $jugador_local_uno_id ? 'Ganó' : 'Perdió',
                    'categoria' => $partido->nombre_categoria,
                    'jugador_local_mano_habil' => $partido->mano_habil_local,
                    'jugador_rival_mano_habil' => $partido->mano_habil_rival,
                    'jugador_local_edad' => $partido->edad_local,
                    'jugador_rival_edad' => $partido->edad_rival,
                    'jugador_local_tamano' => $partido->tamano_local,
                    'jugador_rival_tamano' => $partido->tamano_rival,
                ];
        
                $resultados[] = $resultado;
            }

            foreach ($Partidos_vs as $partido) {
                $resultado_vs = [
                  'jugador_local' => $partido->jugador_ganador_uno_id == $partido->jugador_local_uno_id ? $partido->nombre_local : $partido->nombre_rival,
                    'jugador_rival' => $partido->jugador_ganador_uno_id != $partido->jugador_rival_uno_id ? $partido->nombre_rival : $partido->nombre_local,
                    'jugador_local_id' => $partido->jugador_local_uno_id,
                    'jugador_rival_id' => $partido->jugador_rival_uno_id,
                    'torneo' => $partido->nombre_torneo,
                    'fecha' => $partido->fecha_inicio_torneo . ' - ' . $partido->fecha_final_torneo,
                    'fecha_final'   => $partido->fecha_final_torneo,
                    'fecha_inicio'   => $partido->fecha_inicio_torneo,
                    'resultado' => $partido->resultado, // Asumiendo que hay un campo resultado en la tabla partidos
                    'jugador_local_set' => $partido->jugador_local_set,
                    'jugador_rival_set' => $partido->jugador_rival_set,
                    'gano' => $partido->jugador_ganador_uno_id == $jugador_local_uno_id ? 'Ganó' : 'Perdió',
                    'categoria' => $partido->nombre_categoria,
                    'jugador_local_mano_habil' => $partido->mano_habil_local,
                    'jugador_rival_mano_habil' => $partido->mano_habil_rival,
                    'jugador_local_edad' => $partido->edad_local,
                    'jugador_rival_edad' => $partido->edad_rival,
                    'jugador_local_tamano' => $partido->tamano_local,
                    'jugador_rival_tamano' => $partido->tamano_rival,
                    'fase'=> $this->getFase($partido->fase),
                ];
        
                    // Contar las victorias del jugador local y del jugador rival
                    if ($partido->jugador_ganador_uno_id == $jugador_local_uno_id) {
                        $victorias_local_vs++;
                    } elseif ($partido->jugador_ganador_uno_id == $jugador_rival_uno_id) {
                        $victorias_rival_vs++;
                    }

                $resultados_vs[] = $resultado_vs;
            }

            foreach ($Partidos_rival as $partido) {
                $resultado_rival = [
                    'jugador_local' => $partido->jugador_ganador_uno_id == $partido->jugador_local_uno_id ? $partido->nombre_local : $partido->nombre_rival,
                    'jugador_rival' => $partido->jugador_ganador_uno_id != $partido->jugador_rival_uno_id ? $partido->nombre_rival : $partido->nombre_local,
                    'jugador_local_id' => $partido->jugador_local_uno_id,
                    'jugador_rival_id' => $partido->jugador_rival_uno_id,
                    'torneo' => $partido->nombre_torneo,
                    'fecha' => $partido->fecha_inicio_torneo . ' - ' . $partido->fecha_final_torneo,
                    'fecha_final'   => $partido->fecha_final_torneo,
                    'fecha_inicio'   => $partido->fecha_inicio_torneo,
                    'resultado' => $partido->resultado, // Asumiendo que hay un campo resultado en la tabla partidos
                    'jugador_local_set' => $partido->jugador_local_set,
                    'jugador_rival_set' => $partido->jugador_rival_set,
                    'gano' => $partido->jugador_ganador_uno_id == $jugador_rival_uno_id ? 'Ganó' : 'Perdió',
                    'categoria' => $partido->nombre_categoria,
                    'jugador_local_mano_habil' => $partido->mano_habil_local,
                    'jugador_rival_mano_habil' => $partido->mano_habil_rival,
                    'jugador_local_edad' => $partido->edad_local,
                    'jugador_rival_edad' => $partido->edad_rival,
                    'jugador_local_tamano' => $partido->tamano_local,
                    'jugador_rival_tamano' => $partido->tamano_rival,
                ];
        
                $resultados_rival[] = $resultado_rival;
            }
        
            $content = [
                'jugador_local' => $jugadorDatosLocal->nombres . ' ' . $jugadorDatosLocal->apellidos,
                'jugador_rival' => $jugadorDatosRival->nombres . ' ' . $jugadorDatosRival->apellidos,
                'jugador_local_id' => $jugadorDatosLocal->id,
                'jugador_rival_id' => $jugadorDatosRival->id,
                'ranking_local' => $jugadorDatosLocal->ranking_temporal,
                'ranking_rival' => $jugadorDatosRival->ranking_temporal,
                 'jugador_local_mano_habil' => $jugadorDatosLocal->mano_habil,
                'jugador_rival_mano_habil' => $jugadorDatosRival->mano_habil,
                'jugador_local_edad' => $jugador_local_edad,
                'jugador_rival_edad' => $jugador_rival_edad,
                'jugador_local_tamano' => $jugadorDatosLocal->altura,
                'jugador_rival_tamano' => $jugadorDatosRival->altura,
                'jugador_local_peso' => $jugadorDatosLocal->peso,
                'jugador_rival_peso' => $jugadorDatosRival->peso,
                'jugador_local_sexo' => $jugadorDatosLocal->sexo,
                'jugador_rival_sexo' => $jugadorDatosRival->sexo,
                'victorias_local_vs' => $victorias_local_vs,
                'victorias_rival_vs' => $victorias_rival_vs,
                'jugador_local_imagen' => $jugador_local_imagen,
                'jugador_rival_imagen' => $jugador_rival_imagen,
                'torneo' => $torneo,
                'categoria' => $categoria,
                'partidos_local' => $resultados,
                'partidos_rival' => $resultados_rival,
                'partido_vs' => $resultados_vs,

            ];
        }

            $content = json_encode($content);

       
        Storage::disk('public')->put('public/uploads/h2h/json.txt', $content);
        
        return redirect(App::$URL_JSON_VS.'?json='.env('APP_URL').'/storage/public/uploads/h2h/json.txt');
    }
    
    
    public function jugadorListJsonValidate(Request $request)
    {

        $torneo = $request->torneo_id;
        $torneo_categoria_id = $request->torneo_categoria_id;
        $landing = false;
        $ComunidadId = $landing ? Comunidad::where('principal', true)->first()->id : Auth::guard('web')->user()->comunidad_id;

        $TorneoCategoria = TorneoCategoria::where('id', $torneo_categoria_id)->where('torneo_id', $torneo)
            ->whereHas('torneo', function ($q) use ($ComunidadId) {
                $q->where('comunidad_id', $ComunidadId);
            })->first();

        if ($TorneoCategoria != null) {
            $TorneoGrupos = $TorneoCategoria->torneo->torneoGrupos()->where('torneo_categoria_id', $TorneoCategoria->id)->select('grupo_id')->groupBy('grupo_id')->get();

            $JugadoresClasificados = [];

            if ($TorneoCategoria->clasificados_cuartos > 0) {
                $Clasifican = 4;
            } elseif ($TorneoCategoria->clasificados_terceros > 0) {
                $Clasifican = 3;
            } else {
                $Clasifican = $TorneoCategoria->clasificados;
            }

            foreach ($TorneoGrupos as $key => $q) {
                //JUGADORES DEL GRUPO
                $Jugadores = $TorneoCategoria->torneo->torneoGrupos()->where('torneo_categoria_id', $TorneoCategoria->id)
                    ->where('grupo_id', $q->grupo_id)->get()->map(function ($q) use ($TorneoCategoria) {
                        return [
                            'jugador_simple_id' => $q->jugadorSimple->id,
                            'jugador_dupla_id' => $TorneoCategoria->multiple ? $q->jugadorDupla->id : null,
                            'nombres' => $TorneoCategoria->multiple ? ($q->jugadorSimple->nombre_completo . " + " . $q->jugadorDupla->nombre_completo) : $q->jugadorSimple->nombre_completo_temporal
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
                                $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
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
                                $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
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
                                $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
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
                                $Puntos += ($p->jugador_local_set == 0 && $p->jugador_rival_set == 0 ? 0 : (in_array($p->resultado, ["wo", "w.o", "WO", "W.O"]) ? 0 : ($p->jugador_rival_set == 0 ? 1 : 2)));
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
            // Inicializar arrays para almacenar los lugares
            $PrimerosLugares = [];
            $SegundoLugares = [];
            $TercerosLugares = [];
            $CuartosLugares = [];

            // Clasificar jugadores en primeros, segundos, terceros y cuartos lugares
            foreach ($JugadoresClasificados as $key => $value) {
                if ($Clasifican == 1) {
                    $PrimerosLugares[] = $value['Clasificados']->first();
                } elseif ($Clasifican == 2) {
                    $PrimerosLugares[] = $value['Clasificados']->first();
                    $SegundoLugares[] = $value['Clasificados']->last();
                } elseif ($Clasifican == 3) {
                    $PrimerosLugares[] = $value['Clasificados']->first();
                    if (isset($value['Clasificados'][1])) {
                        $SegundoLugares[] = $value['Clasificados'][1];
                    }
                    $TercerosLugares[] = $value['Clasificados']->last();
                } elseif ($Clasifican == 4) {
                    $PrimerosLugares[] = $value['Clasificados']->first();
                    if (isset($value['Clasificados'][1])) {
                        $SegundoLugares[] = $value['Clasificados'][1];
                    }
                    if (isset($value['Clasificados'][2])) {
                        $TercerosLugares[] = $value['Clasificados'][2];
                    }
                    $CuartosLugares[] = $value['Clasificados']->last();
                }
            }




            // Eliminar duplicados
            $PrimerosLugares = collect($PrimerosLugares)->unique('key')->values()->all();
            $SegundoLugares = collect($SegundoLugares)->unique('key')->values()->all();
            $TercerosLugares = collect($TercerosLugares)->unique('key')->values()->all();
            $CuartosLugares = collect($CuartosLugares)->unique('key')->values()->all();


            $TercerosLugares = App::multiPropertySort(
                collect($TercerosLugares),
                [
                    ['column' => 'puntos', 'order' => 'desc'],
                    ['column' => 'setsDiferencias', 'order' => 'desc'],
                    ['column' => 'gamesDiferencias', 'order' => 'desc'],
                    ['column' => 'setsGanados', 'order' => 'desc'],
                    ['column' => 'gamesGanados', 'order' => 'desc']
                ]
            )->take($TorneoCategoria->clasificados_terceros)->toArray();





            $CuartosLugares = App::multiPropertySort(
                collect($CuartosLugares),
                [
                    ['column' => 'puntos', 'order' => 'desc'],
                    ['column' => 'setsDiferencias', 'order' => 'desc'],
                    ['column' => 'gamesDiferencias', 'order' => 'desc'],
                    ['column' => 'setsGanados', 'order' => 'desc'],
                    ['column' => 'gamesGanados', 'order' => 'desc']
                ]
            )->take($TorneoCategoria->clasificados_cuartos)->toArray();




            // Ordenar los lugares
            $PrimerosLugares = App::multiPropertySort(
                collect($PrimerosLugares),
                [
                    ['column' => 'puntos', 'order' => 'desc'],
                    ['column' => 'setsDiferencias', 'order' => 'desc'],
                    ['column' => 'gamesDiferencias', 'order' => 'desc'],
                    ['column' => 'setsGanados', 'order' => 'desc'],
                    ['column' => 'gamesGanados', 'order' => 'desc']
                ]
            );
            $SegundoLugares = App::multiPropertySort(
                collect($SegundoLugares),
                [
                    ['column' => 'puntos', 'order' => 'desc'],
                    ['column' => 'setsDiferencias', 'order' => 'desc'],
                    ['column' => 'gamesDiferencias', 'order' => 'desc'],
                    ['column' => 'setsGanados', 'order' => 'desc'],
                    ['column' => 'gamesGanados', 'order' => 'desc']
                ]
            );
            $TercerosLugares = App::multiPropertySort(
                collect($TercerosLugares),
                [
                    ['column' => 'puntos', 'order' => 'desc'],
                    ['column' => 'setsDiferencias', 'order' => 'desc'],
                    ['column' => 'gamesDiferencias', 'order' => 'desc'],
                    ['column' => 'setsGanados', 'order' => 'desc'],
                    ['column' => 'gamesGanados', 'order' => 'desc']
                ]
            );
            $CuartosLugares = App::multiPropertySort(
                collect($CuartosLugares),
                [
                    ['column' => 'puntos', 'order' => 'desc'],
                    ['column' => 'setsDiferencias', 'order' => 'desc'],
                    ['column' => 'gamesDiferencias', 'order' => 'desc'],
                    ['column' => 'setsGanados', 'order' => 'desc'],
                    ['column' => 'gamesGanados', 'order' => 'desc']
                ]
            );

            // Combinar todos los lugares
            $JugadoresClasificadosMerge = collect($PrimerosLugares)
                ->merge($SegundoLugares)
                ->merge($TercerosLugares)
                ->merge($CuartosLugares)
                ->unique('key')
                ->values()
                ->all();

            // Crear el objeto TorneoFaseFinal
            $TorneoFaseFinal = (object) [
                'TorneoCategoria' => $TorneoCategoria,
                'JugadoresClasificados' => App::multiPropertySort(
                    collect($JugadoresClasificadosMerge),
                    [
                        ['column' => 'puntos', 'order' => 'desc'],
                        ['column' => 'setsDiferencias', 'order' => 'desc'],
                        ['column' => 'gamesDiferencias', 'order' => 'desc'],
                        ['column' => 'setsGanados', 'order' => 'desc'],
                        ['column' => 'gamesGanados', 'order' => 'desc']
                    ]
                )
            ];


            $data = App::multiPropertySort(
                collect($JugadoresClasificadosMerge),
                [
                    ['column' => 'puntos', 'order' => 'desc'],
                    ['column' => 'setsDiferencias', 'order' => 'desc'],
                    ['column' => 'gamesDiferencias', 'order' => 'desc'],
                    ['column' => 'setsGanados', 'order' => 'desc'],
                    ['column' => 'gamesGanados', 'order' => 'desc']
                ]
            );


            return response()->json(['data' => $data]);
        }
    }
    
   
}
