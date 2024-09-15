<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Categoria;
use App\Models\Comunidad;
use App\Models\Jugador;
use App\Models\TipoDocumento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PerfilController extends Controller
{
    protected $viewName = 'perfil';

    public function index()
    {
        $Auth = Auth::guard('players')->user();
        $Comunidad = Auth::guard('players')->user()->comunidad;
        $TipoDocumentos = TipoDocumento::all();
        $Categorias = Categoria::where('comunidad_id', Auth::guard('players')->user()->comunidad_id)->get();

        return $Auth != null && $Auth->isFirstSession ? redirect(route('app.showResetPassword')) : view('app'.'.'.$this->viewName.'.index', ['ViewName' => ucfirst($this->viewName), 'TipoDocumentos' => $TipoDocumentos, 'Categorias' => $Categorias, 'Comunidad' => $Comunidad]);
    }

    public function showResetPassword()
    {
        $Auth = Auth::guard('players')->user();
        $Model = Comunidad::where('principal', true)->first();

        return $Auth != null && $Auth->isFirstSession ? view('app.password', ['Model' => $Model]) : redirect(route('app.perfil.index'));
    }

    public function resetPassword(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try
        {
            $Auth = Auth::guard('players')->user();

            if($Auth != null && $Auth->isFirstSession)
            {
                $validator = Validator::make($request->all(), [
                    'password' => 'required|confirmed|min:6',
                    'password_confirmation' => 'required|min:6',
                ]);

                if ($validator->fails())
                    return redirect(route('app.showResetPassword'))->withErrors($validator)->withInput();

                $Player = Jugador::find($Auth->id);
                $Player->password = Hash::make($request->password);
                $Player->isFirstSession = false;
                if($Player->save()) $Result->Success = true;

            }else {
                return redirect(route('index'));
            }

        }catch (\Exception $e)
        {
            $Result->Message = $e->getMessage();
        }

        return $Result->Success ? redirect(route('app.perfil.index')) : redirect(route('app.showResetPassword'))->with(['Message' => $Result->Message]);
    }

    public function store(Request $request)
    {
        $imagen_path = null;

        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try {

            DB::beginTransaction();

            $Validator = Validator::make($request->all(), [
                'nombres' => 'required|max:250',
                'apellidos' => 'required|max:250',
                'tipo_documento_id' => 'required',
                'nro_documento' => 'required|'.($request->tipo_documento_id == App::$TIPO_DOCUMENTO_DNI ? 'digits:8|numeric' : ($request->tipo_documento_id == App::$TIPO_DOCUMENTO_PASAPORTE  ? 'digits:9|numeric' : 'digits:12') ).'|unique:jugadors,nro_documento,'.Auth::guard('players')->user()->id.',id,comunidad_id,'.Auth::guard('players')->user()->comunidad_id.',deleted_at,NULL',
                'edad' => 'nullable|numeric|digits_between:1,3',
                'sexo' => 'required|string|in:M,F',
                'telefono' => 'nullable|max:15',
                'celular' => 'nullable|max:15',
                'altura' => 'nullable|numeric',
                'peso' => 'nullable|numeric',
                'password' => 'required|confirmed|min:6',
                'password_confirmation' => 'required|min:6'
            ], [
                'sexo.in' => 'El campo sexo acepta solo valores M o F.',
                'tipo_documento_id.required' => 'El campo tipo documento es obligatorio.',
                'nro_documento.required' => 'El campo número de documento para '.($request->tipo_documento_id == App::$TIPO_DOCUMENTO_DNI ? "DNI" : ($request->tipo_documento_id == App::$TIPO_DOCUMENTO_PASAPORTE ? 'PASAPORTE' : 'CARNET DE EXTRANJERÍA')).' es obligatorio.',
                'nro_documento.digits' => 'El campo número de documento para '.($request->tipo_documento_id == App::$TIPO_DOCUMENTO_DNI ? "DNI debe ser númerico y" : ($request->tipo_documento_id == App::$TIPO_DOCUMENTO_PASAPORTE ? 'PASAPORTE debe ser númerico y' : 'CARNET DE EXTRANJERÍA')).' debe terner :digits digitos.',
                'nro_documento.regex' => 'El formato para '.($request->tipo_documento_id == App::$TIPO_DOCUMENTO_DNI ? 'DNI' : ($request->tipo_documento_id == App::$TIPO_DOCUMENTO_PASAPORTE ? 'PASAPORTE' : 'CARNET DE EXTRANJERÍA').' no es válido.')
            ]);

            if (!$Validator->fails()){

                $JugadorExistenteNombresCompletos = Jugador::where('id', '!=', Auth::guard('players')->user()->id)->where(DB::raw("CONCAT(jugadors.nombres,' ',jugadors.apellidos)"), trim($request->nombres).' '.trim($request->apellidos))->first();
                if($JugadorExistenteNombresCompletos != null)
                {
                    $Result->Message = "Ya existe un jugador registrado con nombres y apellidos '".$JugadorExistenteNombresCompletos->nombre_completo."' ";

                }else{

                    if($request->file('imagen')) $imagen_path = $request->imagen->store('public/uploads/jugador');

                    $entity = Jugador::find(Auth::guard('players')->user()->id);

                    $request->merge([
                        'imagen_path' => $entity != null ? ($imagen_path ?? $entity->imagen_path) : ($imagen_path ?? null),
                        'password' => $entity != null && $request->password == "************" ? $entity->password : Hash::make($request->password)
                    ]);

                    if($entity != null) {
                        $entity->update($request->only('categoria_id',  'imagen_path', 'nombres', 'apellidos', 'tipo_documento_id', 'nro_documento', 'edad', 'sexo', 'telefono', 'celular', 'altura', 'peso', 'password'));
                    }

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
}
