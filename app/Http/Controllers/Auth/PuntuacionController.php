<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Comunidad;
use App\Models\Puntuacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PuntuacionController extends Controller
{
    protected $viewName = 'puntuacion';

    public function index()
    {
        $Puntuaciones = Puntuacion::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->get();
        return view('auth'.'.'.$this->viewName.'.index', ['Puntuaciones' => $Puntuaciones, 'ViewName' => ucfirst($this->viewName)]);
    }

    public function store(Request $request)
    {
        $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

        try {

            DB::beginTransaction();

            $Validator = Validator::make($request->all(), [
                'puntos' => 'required|array'
            ]);

            if (!$Validator->fails())
            {
                $Puntuaciones = Puntuacion::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->get();

                foreach ($Puntuaciones as $key => $q)
                {
                    $q->puntos = $request->puntos[$key];
                    $q->save();
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
