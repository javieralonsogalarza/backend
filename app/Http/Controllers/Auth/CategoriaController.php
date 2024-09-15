<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoriaController extends Controller
{
    protected $viewName = 'categoria';

    public function index()
    {
        return view('auth'.'.'.$this->viewName.'.index', ['ViewName' => ucfirst($this->viewName)]);
    }

    public function listJson(Request $request)
    {
        $list = Categoria::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->get();
        if($request->select2) $list = $list->map(function ($q){return ['id' => $q->id, 'text' => $q->nombre];});
        return response()->json(['data' => $list]);
    }

    public function partialView($id)
    {
        $entity = null;

        $lastOrden = Categoria::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->orderby('orden', 'desc')->first();

        if($id != 0) $entity = Categoria::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->where('id', $id)->first();

        $orden = $entity == null ? ($lastOrden != null && $lastOrden->orden != null && $lastOrden->orden > 0 ? ($lastOrden->orden + 1) : 1) : ($entity->orden == null ? ($lastOrden != null && $lastOrden->orden != null && $lastOrden->orden > 0 ? ($lastOrden->orden + 1) : 1) : $entity->orden);



        return view('auth'.'.'.$this->viewName.'.ajax.partialView', ['Model' => $entity, 'Orden' => $orden, 'ViewName' => ucfirst($this->viewName)]);
    }

    public function store(Request $request)
    {
        $entity = null;

        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try {

            DB::beginTransaction();

            $request->merge([
                'dupla' => filter_var($request->dupla, FILTER_VALIDATE_BOOLEAN),
                'visible' => filter_var($request->visible, FILTER_VALIDATE_BOOLEAN),
            ]);

            $Validator = Validator::make($request->all(), [
                'nombre' => 'required|max:50|unique:categorias,nombre,'.($request->id != 0 ? $request->id : "NULL").',id,comunidad_id,'.Auth::guard('web')->user()->comunidad_id.',deleted_at,NULL',
                'dupla' => 'required',
                'orden' => 'required|numeric',
                'visible' => 'required'
            ]);

            if (!$Validator->fails()){

                if($request->id != 0) $entity = Categoria::find($request->id);

                $request->merge(['comunidad_id' => Auth::guard('web')->user()->comunidad_id]);

                if($entity != null) {
                    $request->merge(['user_update_id' =>  Auth::guard('web')->user()->id]);
                    $entity->update($request->all());
                }else{
                    $request->merge(['user_create_id' =>  Auth::guard('web')->user()->id]);
                    Categoria::create($request->all());
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
            $entity = Categoria::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->where('id', $request->id)->first();
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
