<?php

namespace App\Http\Controllers\Auth;

use App\Exports\TorneoJugadorExport;
use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Categoria;
use App\Models\Comunidad;
use App\Models\Formato;
use App\Models\Grupo;
use App\Models\Jugador;
use App\Models\Partido;
use App\Models\Puntuacion;
use App\Models\Ranking;
use App\Models\RankingDetalle;
use App\Models\Torneo;
use App\Models\TorneoCategoria;
use App\Models\TorneoGrupo;
use App\Models\TorneoJugador;
use App\Models\TorneoZona;
use App\Models\Zona;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
/**
 * This is a modified version of the getTorneoFinales function that properly handles both singles and doubles matches.
 * 
 * @param \Illuminate\Http\Request $request
 * @return \Illuminate\Http\JsonResponse
 */


class NuevoController extends Controller
{
public function getTorneoFinales(Request $request)
{
    $Result = (object)['Success' => false, 'Message' => null, 'Data' => [], 'statistics' => null];

    try {
        // Get tournament ID from request
        $torneoId = $request->input('torneo_id');
        
        if (!$torneoId) {
            $Result->Message = "El ID del torneo es requerido.";
            return response()->json($Result);
        }
        
        // Verify tournament exists and belongs to user's community
        $torneo = Torneo::where('id', $torneoId)
            ->where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
            ->first();
            
        if (!$torneo) {
            $Result->Message = "El torneo no existe o no tienes acceso.";
            return response()->json($Result);
        }

        // Get all categories in the tournament
        $categorias = TorneoCategoria::where('torneo_id', $torneoId)->get();
        
        if ($categorias->isEmpty()) {
            $Result->Message = "El torneo no tiene categorías.";
            return response()->json($Result);
        }

        $finales = [];
        $folderPath = 'uploads/img';
        
        // For each category, get the final match (phase 1)
        foreach ($categorias as $categoria) {
            $partidoFinal = Partido::where('torneo_id', $torneoId)
                ->where('torneo_categoria_id', $categoria->id)
                ->where('fase', 1)  // Phase 1 represents the final match
                ->with(['jugadorLocalUno', 'jugadorLocalDos', 'jugadorRivalUno', 'jugadorRivalDos', 'torneoCategoria.categoriaSimple'])
                ->first();
                
            if ($partidoFinal) {
                $finalData = [
                    'categoria' => [
                        'id' => $categoria->id,
                        'nombre' => $categoria->categoriaSimple->nombre,
                        'multiple' => $categoria->multiple
                    ],
                    'partido_id' => $partidoFinal->id,
                    'finalizado' => $partidoFinal->estado_id == App::$ESTADO_FINALIZADO,
                    'resultado' => $partidoFinal->resultado,
                    'resultado_detalle' => [
                        'sets_local' => $partidoFinal->jugador_local_set,
                        'sets_rival' => $partidoFinal->jugador_rival_set,
                        'games_detail' => $partidoFinal->resultado ? json_decode($partidoFinal->resultado) : null,
                        'wo' => $partidoFinal->wo,
                        'fecha_inicio' => $partidoFinal->fecha_inicio,
                        'fecha_final' => $partidoFinal->fecha_final,
                        'resultado_timestamp' => $partidoFinal->resultado_timestamp
                    ],
                    'jugadores' => [
                        'equipo1' => [],
                        'equipo2' => []
                    ]
                ];
                  // Add data for team/player 1
                if ($partidoFinal->jugadorLocalUno) {
                    $jugador_local_uno_imagen = null;
                    
                    // Comprobar si la imagen existe en Storage
                    if (Storage::disk('public')->exists("{$folderPath}/jugador_{$partidoFinal->jugadorLocalUno->id}.png")) {
                        $jugador_local_uno_imagen = Storage::url("{$folderPath}/jugador_{$partidoFinal->jugadorLocalUno->id}.png");
                    } else {
                        // Asignar imagen por defecto según el sexo del jugador
                        if($partidoFinal->jugadorLocalUno->sexo == 'M') {
                            $jugador_local_uno_imagen = "images/hombre.png";
                        } else if($partidoFinal->jugadorLocalUno->sexo == 'F') {
                            $jugador_local_uno_imagen = "images/mujer.png";
                        } else {
                            $jugador_local_uno_imagen = "images/incognito.png";
                        }
                    }

                    $finalData['jugadores']['equipo1'][] = [
                        'id' => $partidoFinal->jugadorLocalUno->id,
                        'nombre' => $partidoFinal->jugadorLocalUno->nombre_completo_temporal,
                        'imagen' => $jugador_local_uno_imagen,
                        'mano_habil' => $partidoFinal->jugadorLocalUno->mano_habil,
                        'edad' => Carbon::parse($partidoFinal->jugadorLocalUno->fecha_nacimiento)->age,
                        'altura' => $partidoFinal->jugadorLocalUno->altura,
                        'peso' => $partidoFinal->jugadorLocalUno->peso
                    ];
                }
                  // Add second player for team 1 if it's a doubles match
                if ($categoria->multiple && $partidoFinal->jugadorLocalDos) {
                    $jugador_local_dos_imagen = null;
                    
                    if (Storage::disk('public')->exists("{$folderPath}/jugador_{$partidoFinal->jugadorLocalDos->id}.png")) {
                        $jugador_local_dos_imagen = Storage::url("{$folderPath}/jugador_{$partidoFinal->jugadorLocalDos->id}.png");
                    } else {
                        // Asignar imagen por defecto según el sexo del jugador
                        if($partidoFinal->jugadorLocalDos->sexo == 'M') {
                            $jugador_local_dos_imagen = "images/hombre.png";
                        } else if($partidoFinal->jugadorLocalDos->sexo == 'F') {
                            $jugador_local_dos_imagen = "images/mujer.png";
                        } else {
                            $jugador_local_dos_imagen = "images/incognito.png";
                        }
                    }

                    $finalData['jugadores']['equipo1'][] = [
                        'id' => $partidoFinal->jugadorLocalDos->id,
                        'nombre' => $partidoFinal->jugadorLocalDos->nombre_completo_temporal,
                        'imagen' => $jugador_local_dos_imagen,
                        'mano_habil' => $partidoFinal->jugadorLocalDos->mano_habil,
                        'edad' => Carbon::parse($partidoFinal->jugadorLocalDos->fecha_nacimiento)->age,
                        'altura' => $partidoFinal->jugadorLocalDos->altura,
                        'peso' => $partidoFinal->jugadorLocalDos->peso
                    ];
                }
                  // Add data for team/player 2
                if ($partidoFinal->jugadorRivalUno) {
                    $jugador_rival_uno_imagen = null;
                    
                    if (Storage::disk('public')->exists("{$folderPath}/jugador_{$partidoFinal->jugadorRivalUno->id}.png")) {
                        $jugador_rival_uno_imagen = Storage::url("{$folderPath}/jugador_{$partidoFinal->jugadorRivalUno->id}.png");
                    } else {
                        // Asignar imagen por defecto según el sexo del jugador
                        if($partidoFinal->jugadorRivalUno->sexo == 'M') {
                            $jugador_rival_uno_imagen = "images/hombre.png";
                        } else if($partidoFinal->jugadorRivalUno->sexo == 'F') {
                            $jugador_rival_uno_imagen = "images/mujer.png";
                        } else {
                            $jugador_rival_uno_imagen = "images/incognito.png";
                        }
                    }

                    $finalData['jugadores']['equipo2'][] = [
                        'id' => $partidoFinal->jugadorRivalUno->id,
                        'nombre' => $partidoFinal->jugadorRivalUno->nombre_completo_temporal,
                        'imagen' => $jugador_rival_uno_imagen,
                        'mano_habil' => $partidoFinal->jugadorRivalUno->mano_habil,
                        'edad' => Carbon::parse($partidoFinal->jugadorRivalUno->fecha_nacimiento)->age,
                        'altura' => $partidoFinal->jugadorRivalUno->altura,
                        'peso' => $partidoFinal->jugadorRivalUno->peso
                    ];
                }
                  // Add second player for team 2 if it's a doubles match
                if ($categoria->multiple && $partidoFinal->jugadorRivalDos) {
                    $jugador_rival_dos_imagen = null;
                    
                    if (Storage::disk('public')->exists("{$folderPath}/jugador_{$partidoFinal->jugadorRivalDos->id}.png")) {
                        $jugador_rival_dos_imagen = Storage::url("{$folderPath}/jugador_{$partidoFinal->jugadorRivalDos->id}.png");
                    } else {
                        // Asignar imagen por defecto según el sexo del jugador
                        if($partidoFinal->jugadorRivalDos->sexo == 'M') {
                            $jugador_rival_dos_imagen = "images/hombre.png";
                        } else if($partidoFinal->jugadorRivalDos->sexo == 'F') {
                            $jugador_rival_dos_imagen = "images/mujer.png";
                        } else {
                            $jugador_rival_dos_imagen = "images/incognito.png";
                        }
                    }

                    $finalData['jugadores']['equipo2'][] = [
                        'id' => $partidoFinal->jugadorRivalDos->id,
                        'nombre' => $partidoFinal->jugadorRivalDos->nombre_completo_temporal,
                        'imagen' => $jugador_rival_dos_imagen,
                        'mano_habil' => $partidoFinal->jugadorRivalDos->mano_habil,
                        'edad' => Carbon::parse($partidoFinal->jugadorRivalDos->fecha_nacimiento)->age,
                        'altura' => $partidoFinal->jugadorRivalDos->altura,
                        'peso' => $partidoFinal->jugadorRivalDos->peso
                    ];
                }
                
                // Add winner information if match is finished
                if ($partidoFinal->estado_id == App::$ESTADO_FINALIZADO && $partidoFinal->jugador_ganador_uno_id) {
                    $esLocalGanador = $partidoFinal->jugador_ganador_uno_id == $partidoFinal->jugador_local_uno_id;
                    $finalData['ganador'] = $esLocalGanador ? 'equipo1' : 'equipo2';
                }
                
                $finales[] = $finalData;
            }
        }
          $Result->Success = true;
        $Result->Data = [
            'titulo' => $torneo->nombre,
            'finales' => $finales
        ];
        
        // Statistics
        $Result->statistics = [
            'total_categorias' => $categorias->count(),
            'total_finales' => count($finales),
            'finales_completadas' => collect($finales)->where('finalizado', true)->count(),
            'finales_pendientes' => collect($finales)->where('finalizado', false)->count(),
        ];
        
        // Include request information if debugging is enabled
        if ($request->has('debug')) {
            $Result->request = [
                'method' => $request->method(),
                'url' => $request->url(),
                'query' => $request->query(),
                'torneo_id' => $torneoId,
                'user' => Auth::guard('web')->user()->id
            ];
        }
        
    } catch (\Exception $e) {
        $Result->Message = "Error al recuperar los partidos finales: " . $e->getMessage();
    }
    
        $content = json_encode($Result);
        
    

       
        Storage::disk('public')->put('public/uploads/finales_vs/json.txt', $content);
        
        return redirect(App::$URL_JSON_FINALES_VS.'?json='.env('APP_URL').'/storage/public/uploads/finales_vs/json.txt');

}

}