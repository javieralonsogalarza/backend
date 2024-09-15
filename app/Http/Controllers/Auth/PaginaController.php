<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pagina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaginaController extends Controller
{
    protected $viewName = 'pagina';

    public function index()
    {
        return view('auth'.'.'.$this->viewName.'.index', ['ViewName' => ucfirst($this->viewName)]);
    }

    public function listJson(Request $request)
    {
        return response()->json(['data' => Pagina::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->get()]);
    }

    public function partialView($id)
    {
        $entity = null;

        if($id != 0) $entity = Pagina::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->where('id', $id)->first();

        return view('auth'.'.'.$this->viewName.'.ajax.partialView', ['Model' => $entity, 'ViewName' => ucfirst($this->viewName)]);
    }

    public function store(Request $request)
    {
        $entity = null;

        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try {

            DB::beginTransaction();

            $Validator = Validator::make($request->all(), [
                'titulo' => 'required|max:150',
                'descripcion' => 'required',
            ]);

            if (!$Validator->fails())
            {
                $imagen_path = null;

                if($request->file('imagen')) $imagen_path = $request->imagen->store('public/uploads/paginas');

                if($request->id != 0) $entity = Pagina::find($request->id);

                $request->merge(['comunidad_id' => Auth::guard('web')->user()->comunidad_id]);

                if($entity != null) {
                    $request->merge([
                        'imagen_path' => $imagen_path != null ? $imagen_path : $entity->imagen_path,
                        'user_update_id' =>  Auth::guard('web')->user()->id,
                    ]);
                    $entity->update($request->all());
                }else{
                    $request->merge([
                        'imagen_path' => $imagen_path != null ? $imagen_path : null,
                        'user_create_id' =>  Auth::guard('web')->user()->id
                    ]);
                    Pagina::create($request->all());
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
}
