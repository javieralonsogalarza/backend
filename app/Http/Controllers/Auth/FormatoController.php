<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Formato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FormatoController extends Controller
{
    protected $viewName = 'formato';

    public function index()
    {
        return view('auth'.'.'.$this->viewName.'.index', ['ViewName' => ucfirst($this->viewName)]);
    }

    public function listJson(Request $request)
    {
        return response()->json(['data' => Formato::all()]);
    }

    public function partialView($id)
    {
        $entity = null;

        if($id != 0) $entity = Formato::find($id)->first();

        return view('auth'.'.'.$this->viewName.'.ajax.partialView', ['Model' => $entity, 'ViewName' => ucfirst($this->viewName)]);
    }

    public function store(Request $request)
    {
        $entity = null;

        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try {

            DB::beginTransaction();

            $Validator = Validator::make($request->all(), [
                'nombre' => 'required|max:50|unique:formatos,nombre,'.($request->id != 0 ? $request->id : "NULL").',id,deleted_at,NULL',
            ]);

            if (!$Validator->fails()){

                if($request->id != 0) $entity = Formato::find($request->id);

                if($entity != null) {
                    $request->merge(['user_update_id' =>  Auth::guard('web')->user()->id]);
                    $entity->update($request->all());
                }else{
                    $request->merge(['user_create_id' =>  Auth::guard('web')->user()->id]);
                    Formato::create($request->all());
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
            $entity = Formato::where('id', $request->id)->first();
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
