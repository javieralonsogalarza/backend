<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Imports\JugadorsImport;
use App\Models\App;
use App\Models\Categoria;
use App\Models\Jugador;
use App\Models\TipoDocumento;
use App\Models\TorneoCategoria;
use App\Models\TorneoJugador;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Mail\ConfirmationEmail;

class JugadorController extends Controller
{
    protected $viewName = 'jugador';

    public function index()
    {
        return view('auth'.'.'.$this->viewName.'.index', ['ViewName' => ucfirst($this->viewName)]);
    }

    public function listJson(Request $request)
    {
        $JugadoresNoDisponibles = [];

        if($request->torneo_categoria_id)
        {
            $JugadoresNoDisponiblesSimples = TorneoJugador::whereHas('torneo', function ($q) use ($request){
                $q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);
            })->where('torneo_categoria_id', $request->torneo_categoria_id)->pluck('jugador_simple_id')->toArray();

            $JugadoresNoDisponiblesDuplas = TorneoJugador::whereHas('torneo', function ($q) use ($request){
                $q->where('comunidad_id', Auth::guard('web')->user()->comunidad_id);
            })->where('torneo_categoria_id', $request->torneo_categoria_id)->pluck('jugador_dupla_id')->toArray();

            $JugadoresNoDisponibles = array_unique(array_filter(array_merge($JugadoresNoDisponiblesSimples, $JugadoresNoDisponiblesDuplas)));
            $JugadoresNoDisponibles = array_unique(array_filter(array_merge($JugadoresNoDisponibles, json_decode($request->jugadores_no_disponibles))));
        }

        $data = Jugador::with('categoria')
        ->where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
        ->where(function ($q) use ($request){
            if($request->filter_categoria){ $q->where('categoria_id', $request->filter_categoria); }
            if($request->filter_sexo){ $q->where('sexo', $request->filter_sexo); }
        })
        ->whereNotIn('id', $JugadoresNoDisponibles)
        ->get();

        return response()->json(['data' => $data]);
    }

    public function partialView($id)
    {
        $entity = null;

        if($id != 0) $entity = Jugador::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->where('id', $id)->first();

        $Categorias = Categoria::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->get();
        $TipoDocumentos = TipoDocumento::all();

        return view('auth'.'.'.$this->viewName.'.ajax.partialView', ['Model' => $entity, 'Categorias' => $Categorias, 'TipoDocumentos' => $TipoDocumentos, 'ViewName' => ucfirst($this->viewName)]);
    }

    public function store(Request $request)
    {
        $entity = null; $imagen_path = null;

        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try {

            DB::beginTransaction();

            $request->merge(['isAccount' => filter_var($request->isAccount, FILTER_VALIDATE_BOOLEAN)]);

            $Validator = Validator::make($request->all(), [
                'categoria_id' => 'nullable',
                'nombres' => 'required|max:250',
                'apellidos' => 'required|max:250',
                'tipo_documento_id' => 'required',
                'nro_documento' => 'required|'.($request->tipo_documento_id == App::$TIPO_DOCUMENTO_DNI ? 'digits:8|numeric' : ($request->tipo_documento_id == App::$TIPO_DOCUMENTO_PASAPORTE  ? 'digits:9|numeric' : 'digits:12') ).'|unique:jugadors,nro_documento,'.($request->id != 0 ? $request->id : "NULL").',id,comunidad_id,'.Auth::guard('web')->user()->comunidad_id.',deleted_at,NULL',
                'edad' => 'nullable|numeric|digits_between:1,3',
                'sexo' => 'required|string|in:M,F',
                'telefono' => 'nullable|max:15',
                'celular' => 'nullable|max:15',
                'altura' => 'nullable|numeric',
                'peso' => 'nullable|numeric',
                'email' => $request->isAccount && $request->id == 0 ? 'required|unique:jugadors,email,'.($request->id != 0 ? $request->id : "NULL").',id,deleted_at,NULL' : 'nullable',
            ], [
                'sexo.in' => 'El campo sexo acepta solo valores M o F.',
                'tipo_documento_id.required' => 'El campo tipo documento es obligatorio.',
                'nro_documento.required' => 'El campo número de documento para '.($request->tipo_documento_id == App::$TIPO_DOCUMENTO_DNI ? "DNI" : ($request->tipo_documento_id == App::$TIPO_DOCUMENTO_PASAPORTE ? 'PASAPORTE' : 'CARNET DE EXTRANJERÍA')).' es obligatorio.',
                'nro_documento.digits' => 'El campo número de documento para '.($request->tipo_documento_id == App::$TIPO_DOCUMENTO_DNI ? "DNI debe ser númerico y" : ($request->tipo_documento_id == App::$TIPO_DOCUMENTO_PASAPORTE ? 'PASAPORTE debe ser númerico y' : 'CARNET DE EXTRANJERÍA')).' debe terner :digits digitos.',
                'nro_documento.regex' => 'El formato para '.($request->tipo_documento_id == App::$TIPO_DOCUMENTO_DNI ? 'DNI' : ($request->tipo_documento_id == App::$TIPO_DOCUMENTO_PASAPORTE ? 'PASAPORTE' : 'CARNET DE EXTRANJERÍA').' no es válido.')
            ]);

            if (!$Validator->fails()){

                $JugadorExistenteNombresCompletos = Jugador::where('id', '!=', $request->id)->where(DB::raw("CONCAT(jugadors.nombres,' ',jugadors.apellidos)"), trim($request->nombres).' '.trim($request->apellidos))->first();
                if($JugadorExistenteNombresCompletos != null)
                {
                    $Result->Message = "Ya existe un jugador registrado con nombres y apellidos '".$JugadorExistenteNombresCompletos->nombre_completo."' ";

                }else{

                    if($request->file('imagen')) $imagen_path = $request->imagen->store('public/uploads/jugador');

                    if($request->id != 0) $entity = Jugador::find($request->id);

                    $request->merge([
                        'comunidad_id' => Auth::guard('web')->user()->comunidad_id,
                        'imagen_path' => $entity != null ? ($imagen_path ?? $entity->imagen_path) : ($imagen_path ?? null)
                    ]);

                    if($entity != null) {
                        $request->merge(['user_update_id' =>  Auth::guard('web')->user()->id]);
                        $entity->update($request->only('categoria_id', 'imagen_path', 'nombres', 'apellidos', 'tipo_documento_id', 'nro_documento', 'sexo', 'altura', 'peso', 'mano_habil', 'fecha_nacimiento', 'marca_raqueta'));
                    }else{
                        $request->merge(['email' => $request->isAccount ? $request->email : null,  'user_create_id' =>  Auth::guard('web')->user()->id]);
                        Jugador::create($request->all());
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


    private function sendConfirmationEmail($entity)
    {
        Mail::to($entity['email'])->send(new ConfirmationEmail($entity));
    }
    public function account(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try {

            DB::beginTransaction();

            $Validator = Validator::make($request->all(), [
                'id' => 'required',
                'email' => 'required|unique:jugadors,email,'.($request->id != 0 ? $request->id : "NULL").',id,deleted_at,NULL',
            ]);

            if (!$Validator->fails()){

                $password = Str::random(8);

                $entity = Jugador::find($request->id);

                if(strtolower(trim($request->email)) == strtolower(trim($entity->email)))
                {
                    $Result->Message = "El usuario ya tiene una cuenta con el email ".$request->email.".";
                    return response()->json($Result);
                }

                $entity->email = $request->email;
                $entity->password = Hash::make($password);
                $entity->isAccount = true;
                $entity->isFirstSession = true;
                $entity->save();

                $modelEmail = [
                    'to' => $request->email,
                    'subject' => 'Cuenta de acceso - '.env('APP_NAME'),
                    'nombres' => $entity->nombre_completo,
                    'cuenta' => $request->email,
                    'password' => $password,
                    'email' => $request->email,
                ];


                $this->sendConfirmationEmail($modelEmail);

                DB::commit();

                $Result->Success = true;
            }else{
                $Result->Message = "Para crear una cuenta, por favor ingrese un email";
            }

            $Result->Errors = $Validator->errors();


        }catch (\Exception $e)
        {
            $Result->Message = $e->getMessage();
            DB::rollBack();
        }

        return response()->json($Result);
    }

    public function accountDelete(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try {

            DB::beginTransaction();

            $Validator = Validator::make($request->all(), [
                'id' => 'required'
            ]);

            if (!$Validator->fails()){

                $entity = Jugador::find($request->id);
                $entity->email = null;
                $entity->password = null;
                $entity->isAccount = null;
                $entity->isFirstSession = null;
                $entity->save();

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
            $entity = Jugador::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->where('id', $request->id)->first();
            $entity->user_update_id = Auth::guard('web')->user()->id;
            if($entity->save()){
                if ($entity->delete()) $Result->Success = true;
            }
        }catch (\Exception $e){
            $Result->Message = $e->getMessage();
        }

        return response()->json($Result);
    }

    public function deleteMasivo(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null];

        try {

            if($request->ids != null && count(json_decode($request->ids)) > 0)
            {
                Jugador::whereIn('id', json_decode($request->ids))
                ->where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
                ->update(['user_update_id' => Auth::guard('web')->user()->id, 'deleted_at' => Carbon::now()]);

                $Result->Success = true;

            }else $Result->Message = "No se ha seleccionado ningun jugador a eliminar.";

        }catch (\Exception $e){
            $Result->Message = $e->getMessage();
        }

        return response()->json($Result);
    }

    public function partialViewImportExcel()
    {
        return view('auth'.'.'.$this->viewName.'.ajax.partialViewImportExcel', ['ViewName' => ucfirst($this->viewName)]);
    }

    public function importarExcel(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null, 'Registers' => 0];

        try {

            if($request->file('file_excel') != null && $request->file('file_excel')->getClientOriginalExtension() == "xlsx")
            {
                $import = new JugadorsImport();
                $import->import($request->file('file_excel'));
                $Result = $import->result();
            }else{
                $Result->Message = "Por favor, seleccione un archivo .xlsx válido.";
            }
        }catch (\Exception $e){
            $Result->Message = $e->getMessage();
        }

        return response()->json($Result);
    }
}
