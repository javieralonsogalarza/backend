<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Comunidad;
use App\Models\Grupo;
use App\Models\Puntuacion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ComunidadController extends Controller
{
    protected $viewName = 'home';

    protected $lits_for_page = 50;

    public function index(Request $request)
    {
        if($request->ajax())
        {
            $list = Comunidad::where(function ($q) use ($request){
                if($request->nombre){ $q->where('nombre', 'like', '%'.$request->nombre.'%'); }
                if($request->fecha_inicio){ $q->whereDate('created_at', '>=', $request->fecha_inicio); }
                if($request->fecha_final){ $q->whereDate('created_at', '<=', $request->fecha_final); }
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->lits_for_page);

            return [
                'lists' => view('auth.'.$this->viewName.'.ajax.listado')->with(['lists' => $list, 'i' => ($this->lits_for_page*($list->currentPage()-1)+1) ])->render(),
                'next_page' => $list->nextPageUrl()
            ];
        }

        return view('auth.'.$this->viewName.'.index', ['ViewName' => ucfirst($this->viewName)]);
    }

    public function listJson(Request $request)
    {
        return response()->json(['data' => Comunidad::all()]);
    }

    public function partialView($id)
    {
        $entity = null;

        if($id != 0) $entity = Comunidad::find($id);

        return view('auth.'.$this->viewName.'.ajax.partialView', ['Model' => $entity, 'ViewName' => ucfirst($this->viewName)]);
    }

    public function getSlug(Request $request)
    {
        return response()->json((empty($_SERVER['HTTPS']) ? 'http' : 'https')."://".$_SERVER['HTTP_HOST']."/".Str::slug($request->nombre));
    }

    public function getPassword(Request $request)
    {
        return response()->json(Str::random(8));
    }

    public function store(Request $request)
    {
        $entity = null; $imagen_path = null;

        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try {

            DB::beginTransaction();

            if($request->id != 0) $entity = Comunidad::find($request->id);

            $Validator = Validator::make($request->all(), [
                'nombres' => 'required|max:150',
                'nombre' => 'required|max:150|unique:comunidads,nombre,'.($request->id != 0 ? $request->id : "NULL").',id,deleted_at,NULL',
                'color_navegacion' => 'required|max:15',
                'color_primario' => 'required|max:15',
                'color_secundario' => 'required|max:15',
                'color_alternativo' => 'required|max:15',
                'titulo_fuente' => 'required|max:50',
                'parrafo_fuente' => 'required|max:50',
                'telefono' => 'nullable|max:15',
                'telefonos' => 'nullable|max:15',
                'emails' => 'required|email|max:150|unique:users,email,'.($entity != null && $entity->users->where('principal', true)->first() != null ? $entity->users->where('principal', true)->first()->id : "NULL").',id,deleted_at,NULL',
                'password' => 'required|confirmed|min:6',
                'password_confirmation' => 'required|min:6',
                'facebook' => 'nullable|max:150',
                'twitter' => 'nullable|max:150',
                'instagram' => 'nullable|max:150'
            ],
            [
                'nombres' => 'El campo nombre completo es obligatorio.',
                'email' => 'El campo email completo es obligatorio.',
                'telefonos.max' => 'El campo teléfono debe tener como máximo 15 dígitos.',
            ]
            );

            if (!$Validator->fails()){

                if($request->file('imagen')) $imagen_path = $request->imagen->store('public/uploads/comunidad');

                $request->merge([
                    'perfil_id' => App::$PERFIL_COMUNIDAD,
                    'slug' => Str::slug($request->nombre),
                    'password' => $entity != null && $request->password == "************" ? null : Hash::make($request->password),
                    'imagen_path' => $entity != null ? ($imagen_path ?? $entity->imagen_path) : ($imagen_path ?? null)
                ]);

                if($entity != null) {
                    $request->merge(['user_update_id' =>  Auth::guard('web')->user()->id]);
                    $entity->update($request->all());

                    $User = User::where('comunidad_id', $entity->id)->where('principal', true)->first();
                    if($User != null)
                    {
                        $User->nombre = $request->nombres;
                        $User->email = $request->emails;
                        $User->telefono = $request->telefonos;
                        if($request->password != null) $User->password = $request->password;
                        $User->save();
                    }
                }else{
                    $request->merge(['user_create_id' =>  Auth::guard('web')->user()->id]);
                    $entity = Comunidad::create($request->all());

                    $request->merge(['nombre' => $request->nombres, 'email' => $request->emails, 'telefono' => $request->telefonos, 'comunidad_id' =>  $entity->id, 'principal' => true]);
                    User::create($request->all());

                    $Grupos = [];
                    for ($i = 0; $i < 17; $i++){ $Grupos[] = ['comunidad_id' => $entity->id, 'nombre' => 'Grupo '.App::arregloAbecedario()[$i], 'created_at' => Carbon::now(), 'user_create_id' => Auth::guard('web')->user()->id];}
                    Grupo::insert($Grupos);

                    $Puntuaciones = [];
                    for($i = 1; $i <= 32; $i++){ $Puntuaciones[] = ['comunidad_id' => $entity->id, 'nombre' => 'Puesto '.$i, 'puntos' => 0, 'created_at' => Carbon::now()];}
                    Puntuacion::insert($Puntuaciones);
                }

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

        try {
            $entity = Comunidad::find($request->id);
            $entity->user_update_id = Auth::guard('web')->user()->id;
            if($entity->save()){
                User::where('comunidad_id', $request->id)->delete();
                if ($entity->delete()) $Result->Success = true;
            }
        }catch (\Exception $e){
            $Result->Message = $e->getMessage();
        }

        return response()->json($Result);
    }
}
