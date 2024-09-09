<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Comunidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PerfilController extends Controller
{
    protected $viewName = 'perfil';

    public function index()
    {
        return view('auth'.'.'.$this->viewName.'.index', ['ViewName' => ucfirst($this->viewName)]);
    }

    public function partialView()
    {
        $entity = Comunidad::find(Auth::guard('web')->user()->comunidad->id);
        return view('auth.'.$this->viewName.'.ajax.partialView', ['Model' => $entity, 'ViewName' => ucfirst($this->viewName)]);
    }

    public function store(Request $request)
    {
        $entity = null; $imagen_path = null;

        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try {

            DB::beginTransaction();

            $Validator = Validator::make($request->all(), [
                'nombre' => 'required|max:150|unique:comunidads,nombre,'.(Auth::guard('web')->user()->comunidad->id).',id,deleted_at,NULL',
                'color_primario' => 'required|max:15',
                'color_secundario' => 'required|max:15',
                'color_alternativo' => 'required|max:15',
                'titulo_fuente' => 'required|max:50',
                'parrafo_fuente' => 'required|max:50',
                'telefono' => 'nullable|max:15',
                'email' => 'required|email|max:150',
                'facebook' => 'nullable|max:150',
                'twitter' => 'nullable|max:150',
                'instagram' => 'nullable|max:150'
            ]);

            if (!$Validator->fails()){

                if($request->id != 0) $entity = Comunidad::find(Auth::guard('web')->user()->comunidad->id);

                if($entity != null)
                {
                    if($request->file('imagen')) $imagen_path = $request->imagen->store('public/uploads/comunidad');

                    $request->merge([
                        'slug' => Str::slug($request->nombre),
                        'imagen_path' => $entity != null ? ($imagen_path ?? $entity->imagen_path) : ($imagen_path ?? null)
                    ]);

                    $request->merge(['user_update_id' =>  Auth::guard('web')->user()->id]);
                    $entity->update($request->all());

                    DB::commit();

                    $Result->Success = true;

                }else{
                    $Result->Message = "El modelo que intenta actualizar no existe.";
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

}
