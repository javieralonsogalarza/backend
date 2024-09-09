<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Portada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PortadaController extends Controller
{
    protected $viewName = 'portada';

    public function index()
    {
        return view('auth'.'.'.$this->viewName.'.index', ['ViewName' => ucfirst($this->viewName)]);
    }

    public function listJson(Request $request)
    {
        return response()->json(['data' => Portada::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->orderBy('id', 'desc')->get()]);
    }

    public function partialView($id)
    {
        $entity = null;

        if($id != 0) $entity = Portada::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->where('id', $id)->first();

        return view('auth'.'.'.$this->viewName.'.ajax.partialView', ['Model' => $entity, 'ViewName' => ucfirst($this->viewName)]);
    }

    public function store(Request $request)
    {
        $entity = null;

        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try {

            DB::beginTransaction();

            $Validator = Validator::make($request->all(), [
                'titulo_uno' => 'required|max:150',
                'titulo_dos' => 'nullable|max:150',
                'descripcion' => 'required|max:500'
            ]);

            if (!$Validator->fails())
            {
                $imagen_path = null;

                if($request->file('imagen')) $imagen_path = $request->imagen->store('public/uploads/portadas');

                if($request->id != 0) $entity = Portada::find($request->id);

                $request->merge(['comunidad_id' => Auth::guard('web')->user()->comunidad_id]);

                if($entity != null) {
                    $request->merge([
                        'imagen_path' => $imagen_path != null ? $imagen_path : $entity->imagen_path,
                        'user_update_id' =>  Auth::guard('web')->user()->id,
                    ]);
                    $entity->update($request->all());
                }else{
                    if($imagen_path == null)
                    {
                        $Result->Message = "Por favor, ingrese una imagen que se verá como portada.";
                        return response()->json($Result);
                    }
                    $request->merge([
                        'imagen_path' => $imagen_path != null ? $imagen_path : null,
                        'user_create_id' =>  Auth::guard('web')->user()->id
                    ]);
                    Portada::create($request->all());
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
            $entity = Portada::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->where('id', $request->id)->first();
            $entity->user_update_id = Auth::guard('web')->user()->id;
            if($entity->save()){
                if ($entity->delete()) $Result->Success = true;
            }
        }catch (\Exception $e){
            $Result->Message = $e->getMessage();
        }

        return response()->json($Result);
    }
}
