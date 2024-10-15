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
                $Puntuaciones = Puntuacion::where('comunidad_id', Auth::guard('web')->user()->comunidad_id)->where('type',0)->get();

                foreach ($Puntuaciones as $key => $q)
                {
                    $q->puntos = $request->puntos[$key];
                    $q->save();
                }

                
                // Guardar los valores configurables (tipo 1)
                    $config = [
                        ['type' => 1, 'nombre' => 'puntos_ganador_2_0', 'puntos' => $request->puntos_ganador_2_0],
                        ['type' => 1, 'nombre' => 'puntos_perdedor_2_0', 'puntos' => $request->puntos_perdedor_2_0],
                        ['type' => 1, 'nombre' => 'puntos_ganador_2_1', 'puntos' => $request->puntos_ganador_2_1],
                        ['type' => 1, 'nombre' => 'puntos_perdedor_2_1', 'puntos' => $request->puntos_perdedor_2_1],
                        ['type' => 1, 'nombre' => 'puntos_ganador_wo', 'puntos' => $request->puntos_ganador_wo],
                        ['type' => 1, 'nombre' => 'puntos_perdedor_wo', 'puntos' => $request->puntos_perdedor_wo],
                    ];
    
                    foreach ($config as $conf) {
                        Puntuacion::updateOrCreate(
                            [
                                'type' => 1,
                                'nombre' => $conf['nombre'],
                                'comunidad_id' => Auth::guard('web')->user()->comunidad_id
                            ],
                            [
                                'puntos' => $conf['puntos']
                            ]
                        );
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
