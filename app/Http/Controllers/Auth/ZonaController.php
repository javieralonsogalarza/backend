<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Zona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ZonaController extends Controller
{
    protected $viewName = 'zona';

    public function index()
    {
        return view('auth'.'.'.$this->viewName.'.index', ['ViewName' => ucfirst($this->viewName)]);
    }

    public function listJson(Request $request)
    {
        $list = Zona::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->get();
        if($request->select2) $list = $list->map(function ($q){return ['id' => $q->id, 'text' => $q->nombre];});

        return response()->json(['data' => $list]);
    }

    public function partialView($id)
    {
        $entity = null;

        if($id != 0) $entity = Zona::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->where('id', $id)->first();

        return view('auth'.'.'.$this->viewName.'.ajax.partialView', ['Model' => $entity, 'ViewName' => ucfirst($this->viewName)]);
    }

    public function store(Request $request)
    {
        $entity = null;

        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try {

            DB::beginTransaction();

            $Validator = Validator::make($request->all(), [
                'nombre' => 'required|max:150|unique:zonas,nombre,'.($request->id != 0 ? $request->id : "NULL").',id,comunidad_id,'.Auth::guard('web')->user()->comunidad_id.',deleted_at,NULL',
            ]);

            if (!$Validator->fails()){

                if($request->id != 0) $entity = Zona::find($request->id);

                $request->merge(['comunidad_id' => Auth::guard('web')->user()->comunidad_id]);

                if($entity != null) {
                    $request->merge(['user_update_id' =>  Auth::guard('web')->user()->id]);
                    $entity->update($request->all());
                }else{
                    $request->merge(['user_create_id' =>  Auth::guard('web')->user()->id]);
                    Zona::create($request->all());
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
            $entity = Zona::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->where('id', $request->id)->first();
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
