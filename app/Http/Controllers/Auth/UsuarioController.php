<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Puntuacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UsuarioController extends Controller
{
    protected $viewName = 'usuario';

    public function index()
    {
        return view('auth'.'.'.$this->viewName.'.index', ['ViewName' => ucfirst($this->viewName)]);
    }

    public function listJson(Request $request)
    {
        return response()->json(['data' => User::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->get()]);
    }

    public function getPassword(Request $request)
    {
        return response()->json(Str::random(8));
    }

    public function partialView($id)
    {
        $entity = null;

        if($id != 0) $entity = User::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->where('id', $id)->first();

        return view('auth'.'.'.$this->viewName.'.ajax.partialView', ['Model' => $entity, 'ViewName' => ucfirst($this->viewName)]);
    }

    public function store(Request $request)
    {
        $entity = null;

        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try {

            DB::beginTransaction();

            $Validator = Validator::make($request->all(), [
                'nombre' => 'required|max:150',
                'email' => 'required|email|max:150|unique:users,email,'.($request->id != 0 ? $request->id : "NULL").',id,deleted_at,NULL',
                //'password' => 'required|confirmed|min:6|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',
                'telefono' => 'nullable|max:15',
                'password' => 'required|confirmed|min:6',
                'password_confirmation' => 'required|min:6'
            ],
            [
                'telefono.max' => 'El campo teléfono debe tener como máximo 15 dígitos.',
            ]
            );

            if (!$Validator->fails()){

                if($request->id != 0) $entity = User::find($request->id);

                $request->merge([
                    'perfil_id' => Auth::guard('web')->user()->perfil_id,
                    'comunidad_id' => Auth::guard('web')->user()->comunidad_id,
                    'password' => $entity != null && $request->password == "************" ? $entity->password : Hash::make($request->password)
                ]);

                if($entity != null) {
                    $request->merge(['user_update_id' =>  Auth::guard('web')->user()->id]);
                    $entity->update($request->all());
                }else{
                    $request->merge(['user_create_id' =>  Auth::guard('web')->user()->id]);
                    User::create($request->all());
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
            $entity = User::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->where('id', $request->id)->first();
            if($entity != null && !filter_var($entity->principal, FILTER_VALIDATE_BOOLEAN))
            {
                $entity->user_update_id = Auth::guard('web')->user()->id;
                if($entity->save()){
                    if ($entity->delete()) $Result->Success = true;
                }
            }else{
                $Result->Message = "No se puede eliminar el usuario principal";
            }
        }catch (\Exception $e){
            $Result->Message = $e->getMessage();
        }

        return response()->json($Result);
    }
}
