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
use Illuminate\Support\Facades\Log;
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
            'finales' => $finales,
                        'imagen_comunidad' => url('auth/public-imagen/' . Auth::guard('web')->user()->comunidad_id . '/reportes')

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


public function faseFinalPartidoStore(Request $request)
{
    $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];
    
    Log::info('=== INICIO faseFinalPartidoStore ===', [
        'file' => __FILE__,
        'line' => __LINE__,
        'request_data' => $request->all(),
        'user_id' => Auth::guard('web')->user()->id ?? 'no_auth'
    ]);

    try {
        DB::beginTransaction();
        Log::info('DB Transaction iniciada', ['file' => __FILE__, 'line' => __LINE__]);
        
        // Validate double WO not allowed in finals
        if ($request->resultado === '-' && $this->isDoubleWOInFinal($request)) {
            Log::warning('Doble WO no permitido en final', [
                'file' => __FILE__, 
                'line' => __LINE__,
                'partido_id' => $request->id
            ]);
            $Result->Message = "No se permite doble WO en la final. Por favor, ingrese un resultado válido.";
            DB::rollBack();
            return response()->json($Result);
        }

        $request->merge(['fecha_actual' => Carbon::now()->toDateString()]);
        Log::info('Fecha actual agregada al request', [
            'file' => __FILE__, 
            'line' => __LINE__,
            'fecha_actual' => Carbon::now()->toDateString()
        ]);

        // Validate input
        $validator = $this->validatePartidoInput($request);
        if ($validator->fails()) {
            Log::error('Validación falló', [
                'file' => __FILE__, 
                'line' => __LINE__,
                'errors' => $validator->errors()
            ]);
            $Result->Errors = $validator->errors();
            return response()->json($Result);
        }
        
        Log::info('Validación exitosa', ['file' => __FILE__, 'line' => __LINE__]);

        // Process based on result type
        if ($request->resultado !== '-') {
            Log::info('Procesando resultado normal', ['file' => __FILE__, 'line' => __LINE__]);
            $result = $this->processNormalResult($request);
        } else {
            Log::info('Procesando walkover', ['file' => __FILE__, 'line' => __LINE__]);
            $result = $this->processWalkoverResult($request);
        }

        if ($result['success']) {
            DB::commit();
            Log::info('Proceso exitoso - DB commit', [
                'file' => __FILE__, 
                'line' => __LINE__,
                'result' => $result
            ]);
            $Result->Success = true;
        } else {
            DB::rollBack();
            Log::error('Proceso falló - DB rollback', [
                'file' => __FILE__, 
                'line' => __LINE__,
                'result' => $result
            ]);
            $Result->Message = $result['message'];
        }

    } catch (\Exception $e) {
        $Result->Message = $e->getMessage();
        DB::rollBack();
        Log::error('Exception en faseFinalPartidoStore', [
            'file' => __FILE__, 
            'line' => __LINE__,
            'exception' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }

    Log::info('=== FIN faseFinalPartidoStore ===', [
        'file' => __FILE__, 
        'line' => __LINE__,
        'result' => $Result
    ]);
    
    return response()->json($Result);
}

/**
 * Check if it's a double WO in final
 */
private function isDoubleWOInFinal(Request $request): bool
{
    $partido = Partido::where('id', $request->id)
        ->where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
        ->first();
        
    return $partido && $partido->fase == 1;
}

/**
 * Validate partido input based on result type
 */
private function validatePartidoInput(Request $request): \Illuminate\Validation\Validator
{
    $validationRules = [
        'id' => 'required|integer|min:1',
        'fecha_inicio' => 'required|date|date_format:Y-m-d',
        'fecha_final' => 'required|date|date_format:Y-m-d|after_or_equal:fecha_inicio',
        'resultado' => 'required',
    ];

    // Additional validations for non-walkover results
    if ($request->resultado !== '-') {
        $validationRules = array_merge($validationRules, [
            'jugador_local_set' => 'required|numeric|min:0',
            'jugador_local_juego' => 'required|numeric|min:0',
            'jugador_rival_set' => 'required|numeric|min:0',
            'jugador_rival_juego' => 'required|numeric|min:0',
            'estado_id' => 'required|integer'
        ]);
    }

    return Validator::make($request->all(), $validationRules);
}

/**
 * Process normal match result (not walkover)
 */
private function processNormalResult(Request $request): array
{
    Log::info('=== INICIO processNormalResult ===', [
        'file' => __FILE__, 
        'line' => __LINE__,
        'request_data' => $request->all()
    ]);

    // Business logic validations
    // Validate that sets are consistent (one team must have more sets than the other)
    if ($request->jugador_local_set == $request->jugador_rival_set) {
        Log::warning('Sets iguales - no hay ganador claro', [
            'file' => __FILE__, 
            'line' => __LINE__,
            'local_sets' => $request->jugador_local_set,
            'rival_sets' => $request->jugador_rival_set
        ]);
        return ['success' => false, 'message' => "Debe haber un ganador claro. Los sets no pueden ser iguales."];
    }
    
    // Validate that the winner has more sets
    $localGano = $request->jugador_local_set > $request->jugador_rival_set;
    $rivalGano = $request->jugador_rival_set > $request->jugador_local_set;
    
    Log::info('Determinando ganador', [
        'file' => __FILE__, 
        'line' => __LINE__,
        'local_sets' => $request->jugador_local_set,
        'rival_sets' => $request->jugador_rival_set,
        'local_gano' => $localGano,
        'rival_gano' => $rivalGano
    ]);
    
    if (!$localGano && !$rivalGano) {
        Log::error('No hay ganador claro', ['file' => __FILE__, 'line' => __LINE__]);
        return ['success' => false, 'message' => "Debe haber un ganador claro."];
    }

    if ($request->id != 0) {
        Log::info('Buscando partido', [
            'file' => __FILE__, 
            'line' => __LINE__,
            'partido_id' => $request->id
        ]);
        
        $partido = $this->findPartido($request->id);
        if (!$partido) {
            Log::error('Partido no encontrado', [
                'file' => __FILE__, 
                'line' => __LINE__,
                'partido_id' => $request->id
            ]);
            return ['success' => false, 'message' => "Partido no encontrado"];
        }

        Log::info('Partido encontrado', [
            'file' => __FILE__, 
            'line' => __LINE__,
            'partido_id' => $partido->id,
            'fase' => $partido->fase,
            'bloque' => $partido->bloque,
            'jugador_local_uno_id' => $partido->jugador_local_uno_id,
            'jugador_rival_uno_id' => $partido->jugador_rival_uno_id
        ]);

        $this->updatePartidoWithResult($partido, $request);
        
        if ($partido->save()) {
            Log::info('Partido guardado exitosamente', [
                'file' => __FILE__, 
                'line' => __LINE__,
                'partido_id' => $partido->id,
                'ganador_uno_id' => $partido->jugador_ganador_uno_id,
                'ganador_dos_id' => $partido->jugador_ganador_dos_id,
                'estado_id' => $partido->estado_id
            ]);
            
            if ($partido->estado_id == App::$ESTADO_FINALIZADO && $partido->fase > 1) {
                Log::info('Procesando siguiente ronda', [
                    'file' => __FILE__, 
                    'line' => __LINE__,
                    'partido_id' => $partido->id,
                    'fase_actual' => $partido->fase
                ]);
                $this->processNextRound($partido, $request);
            }
            
            Log::info('=== FIN processNormalResult - EXITOSO ===', ['file' => __FILE__, 'line' => __LINE__]);
            return ['success' => true, 'message' => null];
        } else {
            Log::error('Error al guardar partido', ['file' => __FILE__, 'line' => __LINE__]);
            return ['success' => false, 'message' => "Algo salió mal, hubo un error al guardar."];
        }
    }

    Log::error('ID de partido inválido', [
        'file' => __FILE__, 
        'line' => __LINE__,
        'partido_id' => $request->id
    ]);
    return ['success' => false, 'message' => "ID de partido inválido"];
}

/**
 * Process walkover result
 */
private function processWalkoverResult(Request $request): array
{
    if ($request->id != 0) {
        $partido = $this->findPartido($request->id);
        if (!$partido) {
            return ['success' => false, 'message' => "Partido no encontrado"];
        }

        $this->updatePartidoWithWalkover($partido, $request);
        
        if ($partido->save()) {
            if ($partido->estado_id == App::$ESTADO_FINALIZADO && $partido->fase > 1) {
                $this->processWalkoverNextRound($partido, $request);
            }
            return ['success' => true, 'message' => null];
        } else {
            return ['success' => false, 'message' => "Algo salió mal, hubo un error al guardar."];
        }
    }

    return ['success' => false, 'message' => "ID de partido inválido"];
}

/**
 * Find partido by ID and community
 */
private function findPartido(int $id): ?Partido
{
    return Partido::where('id', $id)
        ->where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
        ->first();
}

/**
 * Update partido with normal result
 */
private function updatePartidoWithResult(Partido $partido, Request $request): void
{
    Log::info('=== INICIO updatePartidoWithResult ===', [
        'file' => __FILE__, 
        'line' => __LINE__,
        'partido_id' => $partido->id,
        'local_sets' => $request->jugador_local_set,
        'rival_sets' => $request->jugador_rival_set
    ]);

    // Determine the winner based on who has more sets
    $localGano = $request->jugador_local_set > $request->jugador_rival_set;
    
    Log::info('Determinando ganador del partido', [
        'file' => __FILE__, 
        'line' => __LINE__,
        'local_gano' => $localGano,
        'jugador_local_uno_id_antes' => $partido->jugador_local_uno_id,
        'jugador_rival_uno_id_antes' => $partido->jugador_rival_uno_id
    ]);
    
    if ($localGano) {
        // Local team won
        $partido->jugador_ganador_uno_id = $partido->jugador_local_uno_id;
        $partido->jugador_ganador_dos_id = $partido->jugador_local_dos_id;
        
        Log::info('LOCAL GANÓ - Asignando ganador', [
            'file' => __FILE__, 
            'line' => __LINE__,
            'ganador_uno_id' => $partido->jugador_ganador_uno_id,
            'ganador_dos_id' => $partido->jugador_ganador_dos_id
        ]);
    } else {
        // Rival team won
        $partido->jugador_ganador_uno_id = $partido->jugador_rival_uno_id;
        $partido->jugador_ganador_dos_id = $partido->jugador_rival_dos_id;
        
        Log::info('RIVAL GANÓ - Asignando ganador', [
            'file' => __FILE__, 
            'line' => __LINE__,
            'ganador_uno_id' => $partido->jugador_ganador_uno_id,
            'ganador_dos_id' => $partido->jugador_ganador_dos_id
        ]);
    }
    
    $partido->jugador_local_set = $request->jugador_local_set;
    $partido->jugador_local_juego = $request->jugador_local_juego;
    $partido->jugador_rival_set = $request->jugador_rival_set;
    $partido->jugador_rival_juego = $request->jugador_rival_juego;
    $partido->fecha_inicio = $request->fecha_inicio;
    $partido->fecha_final = $request->fecha_final;
    $partido->resultado = $request->resultado;
    $partido->estado_id = $request->estado_id;
    $partido->user_update_id = Auth::guard('web')->user()->id;
    
    Log::info('=== FIN updatePartidoWithResult ===', [
        'file' => __FILE__, 
        'line' => __LINE__,
        'partido_actualizado' => [
            'id' => $partido->id,
            'ganador_uno_id' => $partido->jugador_ganador_uno_id,
            'ganador_dos_id' => $partido->jugador_ganador_dos_id,
            'local_set' => $partido->jugador_local_set,
            'rival_set' => $partido->jugador_rival_set,
            'estado_id' => $partido->estado_id
        ]
    ]);
}

/**
 * Update partido with walkover result
 */
private function updatePartidoWithWalkover(Partido $partido, Request $request): void
{
    $partido->fecha_inicio = $request->fecha_inicio;
    $partido->fecha_final = $request->fecha_final;
    $partido->resultado = $request->resultado;
    $partido->estado_id = $request->estado_id;
    $partido->user_update_id = Auth::guard('web')->user()->id;
    
    // Set walkover values
    $partido->jugador_ganador_uno_id = null;
    $partido->jugador_ganador_dos_id = null;
    $partido->jugador_local_set = 0;
    $partido->jugador_local_juego = 0;
    $partido->jugador_rival_set = 0;
    $partido->jugador_rival_juego = 0;
}

/**
 * Get next phase configuration for phase 32
 */
private function getPhase32NextConfig(Partido $partido): ?array
{
    $bracketMap = [
        // Upper Bracket - First Block
        '1_1_upper' => ['nextFase' => 16, 'nextBloque' => 1, 'nextBracket' => 'upper', 'position' => 1],
        '1_2_upper' => ['nextFase' => 16, 'nextBloque' => 1, 'nextBracket' => 'upper', 'position' => 1],
        '1_1_lower' => ['nextFase' => 16, 'nextBloque' => 1, 'nextBracket' => 'upper', 'position' => 2],
        '1_2_lower' => ['nextFase' => 16, 'nextBloque' => 1, 'nextBracket' => 'upper', 'position' => 2],
        
        // Additional mappings...
        '3_1_upper' => ['nextFase' => 16, 'nextBloque' => 1, 'nextBracket' => 'lower', 'position' => 1],
        '3_2_upper' => ['nextFase' => 16, 'nextBloque' => 1, 'nextBracket' => 'lower', 'position' => 1],
        '3_1_lower' => ['nextFase' => 16, 'nextBloque' => 1, 'nextBracket' => 'lower', 'position' => 2],
        '3_2_lower' => ['nextFase' => 16, 'nextBloque' => 1, 'nextBracket' => 'lower', 'position' => 2],
        
        // Continue with other mappings as needed...
    ];

    $bracketKey = $partido->bloque . '_' . $partido->position . '_' . $partido->bracket;
    return $bracketMap[$bracketKey] ?? null;
}

/**
 * Calculate next phase
 */
private function calculateNextPhase(int $currentPhase): int
{
    return (int)($currentPhase / 2);
}

/**
 * Determine next block based on current phase and block
 */
private function determineNextBlock(int $nextPhase, int $currentPhase, int $currentBlock): int
{
    if ($nextPhase == 1) {
        return 1;
    }
    
    if ($nextPhase == 2) {
        return in_array($currentBlock, [1, 3]) ? 1 : 2;
    }
    
    if (in_array($currentPhase, [16, 8])) {
        return $currentBlock;
    }
    
    return in_array($currentBlock, [1, 3]) ? 1 : 2;
}

/**
 * Process next round for normal results
 */
private function processNextRound(Partido $partido, Request $request): void
{
    Log::info('=== INICIO processNextRound ===', [
        'file' => __FILE__, 
        'line' => __LINE__,
        'partido_id' => $partido->id,
        'fase_actual' => $partido->fase,
        'bloque_actual' => $partido->bloque,
        'ganador_uno_id' => $partido->jugador_ganador_uno_id
    ]);

    $nextPhase = $this->calculateNextPhase($partido->fase);
    
    Log::info('Siguiente fase calculada', [
        'file' => __FILE__, 
        'line' => __LINE__,
        'fase_actual' => $partido->fase,
        'siguiente_fase' => $nextPhase
    ]);
    
    if ($partido->fase == 32) {
        Log::info('Procesando fase 32', ['file' => __FILE__, 'line' => __LINE__]);
        $nextConfig = $this->getPhase32NextConfig($partido);
        if (!$nextConfig) {
            Log::warning('No se encontró configuración para fase 32', ['file' => __FILE__, 'line' => __LINE__]);
            return;
        }
        
        $nextPartido = $this->findOrCreateNextPartido32($partido, $nextConfig);
    } else {
        Log::info('Procesando fase normal', ['file' => __FILE__, 'line' => __LINE__]);
        $nextPartido = $this->findOrCreateNextPartido($partido, $nextPhase);
    }

    if ($nextPartido) {
        Log::info('Partido siguiente encontrado/creado', [
            'file' => __FILE__, 
            'line' => __LINE__,
            'next_partido_id' => $nextPartido->id,
            'next_fase' => $nextPartido->fase,
            'next_bloque' => $nextPartido->bloque
        ]);
        
        $this->assignWinnerBasedOnPhase($nextPartido, $partido);
        $nextPartido->save();
        
        Log::info('Ganador asignado y partido guardado', [
            'file' => __FILE__, 
            'line' => __LINE__,
            'next_partido_id' => $nextPartido->id,
            'next_local_uno_id' => $nextPartido->jugador_local_uno_id,
            'next_rival_uno_id' => $nextPartido->jugador_rival_uno_id
        ]);
        
        $this->handleByeLogic($nextPartido);
        
        Log::info('=== FIN processNextRound ===', ['file' => __FILE__, 'line' => __LINE__]);
    } else {
        Log::error('No se pudo crear/encontrar partido siguiente', ['file' => __FILE__, 'line' => __LINE__]);
    }
}

/**
 * Process next round for walkover results
 */
private function processWalkoverNextRound(Partido $partido, Request $request): void
{
    $nextPhase = $this->calculateNextPhase($partido->fase);
    $nextPartido = $this->findOrCreateNextPartido($partido, $nextPhase);
    
    if ($nextPartido) {
        // Use the same position determination logic as normal results for BYE placement
        $correctPosition = $this->determineCorrectPosition($partido, $nextPartido->fase);
        $this->assignByeToNextPartidoByPosition($nextPartido, $correctPosition);
        $nextPartido->save();
        
        $this->handleByeLogic($nextPartido);
    }
}

/**
 * Assign bye to next partido using correct position determination
 */
private function assignByeToNextPartidoByPosition(Partido $nextPartido, int $correctPosition): void
{
    if ($correctPosition == 1) {
        $nextPartido->jugador_local_uno_id = null;
        $nextPartido->jugador_local_dos_id = null;
        $nextPartido->buy_all = true;
    } else {
        $nextPartido->jugador_rival_uno_id = null;
        $nextPartido->jugador_rival_dos_id = null;
        $nextPartido->buy = true;
    }
}

/**
 * Assign bye to next partido for walkover
 */
private function assignByeToNextPartido(Partido $nextPartido, int $position): void
{
    // Use the same position determination logic as normal results
    if ($position == 1) {
        $nextPartido->jugador_local_uno_id = null;
        $nextPartido->jugador_local_dos_id = null;
        $nextPartido->buy_all = true;
    } else {
        $nextPartido->jugador_rival_uno_id = null;
        $nextPartido->jugador_rival_dos_id = null;
        $nextPartido->buy = true;
    }
}

/**
 * Find or create next partido for phase 32
 */
private function findOrCreateNextPartido32(Partido $partido, array $nextConfig): ?Partido
{
    $nextPartido = Partido::where('torneo_id', $partido->torneo_id)
        ->where('torneo_categoria_id', $partido->torneo_categoria_id)
        ->where('fase', $nextConfig['nextFase'])
        ->where('bloque', $nextConfig['nextBloque'])
        ->where('bracket', $nextConfig['nextBracket'])
        ->where('position', $nextConfig['position'])
        ->where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
        ->first();

    if (!$nextPartido) {
        $nextPartido = $this->createNewPartido($partido, $nextConfig['nextFase'], $nextConfig['nextBloque'], $nextConfig['position']);
        $nextPartido->bracket = $nextConfig['nextBracket'];
    } else {
        $nextPartido->user_update_id = Auth::guard('web')->user()->id;
    }

    return $nextPartido;
}

/**
 * Find or create next partido for regular phases
 */
private function findOrCreateNextPartido(Partido $partido, int $nextPhase): ?Partido
{
    $nextBlock = $this->determineNextBlock($nextPhase, $partido->fase, $partido->bloque);
    
    $nextPartido = Partido::where('torneo_id', $partido->torneo_id)
        ->where('torneo_categoria_id', $partido->torneo_categoria_id)
        ->where('fase', $nextPhase)
        ->where('bloque', $nextBlock)
        ->where(function ($q) use($partido) {
            if ($partido->fase == 16) {
                $q->where('position', $partido->bracket == "upper" ? 1 : 2);
            }
        })
        ->where('comunidad_id', Auth::guard('web')->user()->comunidad_id)
        ->first();

    if (!$nextPartido) {
        $position = ($partido->fase == 16) ? ($partido->bracket == "upper" ? 1 : 2) : $partido->position;
        $nextPartido = $this->createNewPartido($partido, $nextPhase, $nextBlock, $position);
    } else {
        $nextPartido->user_update_id = Auth::guard('web')->user()->id;
    }

    return $nextPartido;
}

/**
 * Create new partido
 */
private function createNewPartido(Partido $basePartido, int $fase, int $bloque, int $position): Partido
{
    $newPartido = new Partido();
    $newPartido->torneo_id = $basePartido->torneo_id;
    $newPartido->torneo_categoria_id = $basePartido->torneo_categoria_id;
    $newPartido->estado_id = App::$ESTADO_PENDIENTE;
    $newPartido->multiple = $basePartido->multiple;
    $newPartido->fecha_inicio = Carbon::parse($basePartido->fecha_final)->addDays(1);
    $newPartido->fecha_final = Carbon::parse($basePartido->fecha_final)->addDays(6);
    $newPartido->user_create_id = Auth::guard('web')->user()->id;
    $newPartido->fase = $fase;
    $newPartido->bloque = $bloque;
    $newPartido->position = $position;
    $newPartido->comunidad_id = Auth::guard('web')->user()->comunidad_id;
    
    return $newPartido;
}

/**
 * Assign winner to next partido
 */
private function assignWinnerBasedOnPhase(Partido $nextPartido, Partido $currentPartido): void
{
    Log::info('=== INICIO assignWinnerBasedOnPhase ===', [
        'file' => __FILE__, 
        'line' => __LINE__,
        'current_partido_id' => $currentPartido->id,
        'current_posicion' => $currentPartido->posicion ?? 'null',
        'current_bloque' => $currentPartido->bloque ?? 'null',
        'next_partido_id' => $nextPartido->id,
        'next_fase' => $nextPartido->fase,
        'ganador_uno_id' => $currentPartido->jugador_ganador_uno_id,
        'ganador_dos_id' => $currentPartido->jugador_ganador_dos_id
    ]);

    // Use the same position determination logic
    $correctPosition = $this->determineCorrectPosition($currentPartido, $nextPartido->fase);
    
    Log::info('Posición determinada para el ganador', [
        'file' => __FILE__, 
        'line' => __LINE__,
        'ganador_uno_id' => $currentPartido->jugador_ganador_uno_id,
        'ganador_dos_id' => $currentPartido->jugador_ganador_dos_id,
        'posicion_correcta' => $correctPosition
    ]);

    // Antes de asignar - estado actual
    Log::info('Estado del partido siguiente ANTES de asignación', [
        'file' => __FILE__, 
        'line' => __LINE__,
        'next_local_uno_id' => $nextPartido->jugador_local_uno_id,
        'next_local_dos_id' => $nextPartido->jugador_local_dos_id,
        'next_rival_uno_id' => $nextPartido->jugador_rival_uno_id,
        'next_rival_dos_id' => $nextPartido->jugador_rival_dos_id
    ]);
    
    if ($correctPosition == 1) {
        $nextPartido->jugador_local_uno_id = $currentPartido->jugador_ganador_uno_id;
        $nextPartido->jugador_local_dos_id = $currentPartido->jugador_ganador_dos_id;
        Log::info('Ganador asignado como LOCAL', [
            'file' => __FILE__, 
            'line' => __LINE__,
            'ganador_uno_id' => $currentPartido->jugador_ganador_uno_id,
            'ganador_dos_id' => $currentPartido->jugador_ganador_dos_id
        ]);
    } else {
        $nextPartido->jugador_rival_uno_id = $currentPartido->jugador_ganador_uno_id;
        $nextPartido->jugador_rival_dos_id = $currentPartido->jugador_ganador_dos_id;
        Log::info('Ganador asignado como RIVAL', [
            'file' => __FILE__, 
            'line' => __LINE__,
            'ganador_uno_id' => $currentPartido->jugador_ganador_uno_id,
            'ganador_dos_id' => $currentPartido->jugador_ganador_dos_id
        ]);
    }

    // Después de asignar - estado final
    Log::info('Estado del partido siguiente DESPUÉS de asignación', [
        'file' => __FILE__, 
        'line' => __LINE__,
        'next_local_uno_id' => $nextPartido->jugador_local_uno_id,
        'next_local_dos_id' => $nextPartido->jugador_local_dos_id,
        'next_rival_uno_id' => $nextPartido->jugador_rival_uno_id,
        'next_rival_dos_id' => $nextPartido->jugador_rival_dos_id
    ]);

    Log::info('=== FIN assignWinnerBasedOnPhase ===', ['file' => __FILE__, 'line' => __LINE__]);
}
/**
 * Determine correct position in next round based on bracket structure
 */
private function determineCorrectPosition(Partido $currentPartido, int $nextPhase): int
{
    Log::info('=== INICIO determineCorrectPosition ===', [
        'file' => __FILE__, 
        'line' => __LINE__,
        'current_partido_id' => $currentPartido->id,
        'current_fase' => $currentPartido->fase,
        'current_bloque' => $currentPartido->bloque,
        'current_posicion' => $currentPartido->posicion ?? 'null',
        'next_phase' => $nextPhase
    ]);

    $position = null;

    // For final (phase 1)
    if ($nextPhase == 1) {
        // Semifinal winners: bloque 1 goes to local (top), bloque 2 goes to rival (bottom)
        $position = ($currentPartido->bloque == 1) ? 1 : 2;
        Log::info('Determinando posición para FINAL', [
            'file' => __FILE__, 
            'line' => __LINE__,
            'bloque_actual' => $currentPartido->bloque,
            'posicion_asignada' => $position,
            'logica' => 'bloque 1 -> local(1), bloque 2 -> rival(2)'
        ]);
        return $position;
    }
    
    // For semifinals (phase 2) 
    if ($nextPhase == 2) {
        // From cuartos (phase 4): bloques 1,2 go to local, bloques 3,4 go to rival
        $position = (in_array($currentPartido->bloque, [1, 2])) ? 1 : 2;
        Log::info('Determinando posición para SEMIFINAL', [
            'file' => __FILE__, 
            'line' => __LINE__,
            'bloque_actual' => $currentPartido->bloque,
            'posicion_asignada' => $position,
            'logica' => 'bloques 1,2 -> local(1), bloques 3,4 -> rival(2)'
        ]);
        return $position;
    }
    
    // For cuartos (phase 4)
    if ($nextPhase == 4) {
        // From octavos (phase 8): specific mapping based on bracket structure
        $position = $this->determinePositionFromOctavos($currentPartido);
        Log::info('Determinando posición para CUARTOS', [
            'file' => __FILE__, 
            'line' => __LINE__,
            'bloque_actual' => $currentPartido->bloque,
            'posicion_asignada' => $position,
            'logica' => 'usando determinePositionFromOctavos'
        ]);
        return $position;
    }
    
    // For octavos (phase 8) 
    if ($nextPhase == 8) {
        // From round of 16: bloques 1,3,5,7 go to local, bloques 2,4,6,8 go to rival
        $position = ($currentPartido->bloque % 2 == 1) ? 1 : 2;
        Log::info('Determinando posición para OCTAVOS', [
            'file' => __FILE__, 
            'line' => __LINE__,
            'bloque_actual' => $currentPartido->bloque,
            'es_impar' => ($currentPartido->bloque % 2 == 1),
            'posicion_asignada' => $position,
            'logica' => 'bloques impares -> local(1), bloques pares -> rival(2)'
        ]);
        return $position;
    }
    
    // Default fallback
    $position = ($currentPartido->bloque % 2 == 1) ? 1 : 2;
    Log::info('Usando lógica DEFAULT (fallback)', [
        'file' => __FILE__, 
        'line' => __LINE__,
        'bloque_actual' => $currentPartido->bloque,
        'es_impar' => ($currentPartido->bloque % 2 == 1),
        'posicion_asignada' => $position,
        'logica' => 'bloques impares -> local(1), bloques pares -> rival(2)'
    ]);

    Log::info('=== FIN determineCorrectPosition ===', [
        'file' => __FILE__, 
        'line' => __LINE__,
        'posicion_final' => $position
    ]);

    return $position;
}
private function determinePositionFromOctavos(Partido $currentPartido): int
{
    // Standard bracket structure for octavos to cuartos
    // This ensures proper positioning in third round (cuartos) based on bracket flow
    $octavosToQuartosMap = [
        1 => 1, // Octavo bloque 1 -> Cuarto bloque 1 posición local
        2 => 1, // Octavo bloque 2 -> Cuarto bloque 1 posición rival  
        3 => 2, // Octavo bloque 3 -> Cuarto bloque 2 posición local
        4 => 2, // Octavo bloque 4 -> Cuarto bloque 2 posición rival
        5 => 3, // Octavo bloque 5 -> Cuarto bloque 3 posición local
        6 => 3, // Octavo bloque 6 -> Cuarto bloque 3 posición rival
        7 => 4, // Octavo bloque 7 -> Cuarto bloque 4 posición local  
        8 => 4, // Octavo bloque 8 -> Cuarto bloque 4 posición rival
    ];
    
    // Position within each cuarto: odd blocks go to local (1), even blocks go to rival (2)
    // This is crucial for proper BYE placement in third round
    return ($currentPartido->bloque % 2 == 1) ? 1 : 2;
}

/**
 * Handle bye logic for automatic advancement
 */
private function handleByeLogic(Partido $partido): void
{
    Log::info('=== INICIO handleByeLogic ===', [
        'file' => __FILE__, 
        'line' => __LINE__,
        'partido_id' => $partido->id,
        'buy' => $partido->buy,
        'buy_all' => $partido->buy_all,
        'fase' => $partido->fase
    ]);

    if (!($partido->buy || $partido->buy_all)) {
        Log::info('No es partido BYE, saltando lógica', ['file' => __FILE__, 'line' => __LINE__]);
        return;
    }

    // Check if it's a single bye (one player present)
    $hasLocalPlayer = $partido->jugador_local_uno_id != null;
    $hasRivalPlayer = $partido->jugador_rival_uno_id != null;
    
    Log::info('Verificando jugadores presentes', [
        'file' => __FILE__, 
        'line' => __LINE__,
        'has_local_player' => $hasLocalPlayer,
        'has_rival_player' => $hasRivalPlayer,
        'local_uno_id' => $partido->jugador_local_uno_id,
        'rival_uno_id' => $partido->jugador_rival_uno_id
    ]);
    
    if (($hasLocalPlayer && !$hasRivalPlayer) || (!$hasLocalPlayer && $hasRivalPlayer)) {
        Log::info('Partido BYE detectado - auto finalizando', ['file' => __FILE__, 'line' => __LINE__]);
        $this->autoFinishByeMatch($partido);
        
        if ($partido->fase > 1) {
            Log::info('Creando siguiente ronda desde BYE', [
                'file' => __FILE__, 
                'line' => __LINE__,
                'fase_actual' => $partido->fase
            ]);
            $this->createNextRoundFromBye($partido);
        } else {
            Log::info('Es FINAL - no se crea siguiente ronda', ['file' => __FILE__, 'line' => __LINE__]);
        }
    } else {
        Log::info('No es BYE individual - ambos jugadores presentes o ambos ausentes', [
            'file' => __FILE__, 
            'line' => __LINE__
        ]);
    }

    Log::info('=== FIN handleByeLogic ===', ['file' => __FILE__, 'line' => __LINE__]);
}

/**
 * Automatically finish match with bye
 */
private function autoFinishByeMatch(Partido $partido): void
{
    Log::info('=== INICIO autoFinishByeMatch ===', [
        'file' => __FILE__, 
        'line' => __LINE__,
        'partido_id' => $partido->id,
        'local_uno_id' => $partido->jugador_local_uno_id,
        'rival_uno_id' => $partido->jugador_rival_uno_id
    ]);

    $partido->estado_id = App::$ESTADO_FINALIZADO;
    $partido->resultado = "-";
    $partido->fecha_inicio = Carbon::now()->toDateString();
    $partido->fecha_final = Carbon::now()->toDateString();
    
    if ($partido->jugador_local_uno_id != null) {
        $partido->jugador_ganador_uno_id = $partido->jugador_local_uno_id;
        $partido->jugador_ganador_dos_id = $partido->jugador_local_dos_id;
        $partido->jugador_local_set = 2;
        $partido->jugador_local_juego = 12;
        $partido->jugador_rival_set = 0;
        $partido->jugador_rival_juego = 0;
        
        Log::info('Jugador LOCAL gana por BYE', [
            'file' => __FILE__, 
            'line' => __LINE__,
            'ganador_uno_id' => $partido->jugador_ganador_uno_id,
            'ganador_dos_id' => $partido->jugador_ganador_dos_id
        ]);
    } else {
        $partido->jugador_ganador_uno_id = $partido->jugador_rival_uno_id;
        $partido->jugador_ganador_dos_id = $partido->jugador_rival_dos_id;
        $partido->jugador_local_set = 0;
        $partido->jugador_local_juego = 0;
        $partido->jugador_rival_set = 2;
        $partido->jugador_rival_juego = 12;
        
        Log::info('Jugador RIVAL gana por BYE', [
            'file' => __FILE__, 
            'line' => __LINE__,
            'ganador_uno_id' => $partido->jugador_ganador_uno_id,
            'ganador_dos_id' => $partido->jugador_ganador_dos_id
        ]);
    }
    
    $partido->save();
    
    Log::info('Partido BYE finalizado y guardado', [
        'file' => __FILE__, 
        'line' => __LINE__,
        'estado_id' => $partido->estado_id,
        'resultado' => $partido->resultado
    ]);

    Log::info('=== FIN autoFinishByeMatch ===', ['file' => __FILE__, 'line' => __LINE__]);
}

/**
 * Automatically finish match with double bye
 */
private function autoFinishDoubleByeMatch(Partido $partido): void
{
    $partido->estado_id = App::$ESTADO_FINALIZADO;
    $partido->resultado = "-";
    $partido->fecha_inicio = Carbon::now()->toDateString();
    $partido->fecha_final = Carbon::now()->toDateString();
    $partido->jugador_ganador_uno_id = null;
    $partido->jugador_ganador_dos_id = null;
    $partido->jugador_local_set = 0;
    $partido->jugador_local_juego = 0;
    $partido->jugador_rival_set = 0;
    $partido->jugador_rival_juego = 0;
    
    $partido->save();
}

/**
 * Create next round from bye
 */
private function createNextRoundFromBye(Partido $partido): void
{
    $nextPhase = $this->calculateNextPhase($partido->fase);
    $nextPartido = $this->findOrCreateNextPartido($partido, $nextPhase);
    
    if ($nextPartido) {
        $this->assignWinnerBasedOnPhase($nextPartido, $partido);
        $nextPartido->save();
        
        // Recursive bye handling
        $this->handleByeLogic($nextPartido);
    }
}

/**
 * Create next round from double bye
 */
private function createNextRoundFromDoubleBye(Partido $partido): void
{
    $nextPhase = $this->calculateNextPhase($partido->fase);
    $nextPartido = $this->findOrCreateNextPartido($partido, $nextPhase);
    
    if ($nextPartido) {
        $this->assignDoubleByeBasedOnPhase($nextPartido, $partido);
        $nextPartido->save();
    }
}



/**
 * Assign double bye based on phase and block structure
 */
private function assignDoubleByeBasedOnPhase(Partido $nextPartido, Partido $currentPartido): void
{
    // Use the same position determination logic as normal results
    $correctPosition = $this->determineCorrectPosition($currentPartido, $nextPartido->fase);
    
    if ($currentPartido->fase == 2) {
        // Final - both positions become bye
        $nextPartido->jugador_local_uno_id = null;
        $nextPartido->jugador_local_dos_id = null;
        $nextPartido->jugador_rival_uno_id = null;
        $nextPartido->jugador_rival_dos_id = null;
        $nextPartido->buy = true;
        $nextPartido->buy_all = true;
    } else {
        // Use the correct position determination for other phases
        if ($correctPosition == 1) {
            $nextPartido->jugador_local_uno_id = null;
            $nextPartido->jugador_local_dos_id = null;
            $nextPartido->buy_all = true;
        } else {
            $nextPartido->jugador_rival_uno_id = null;
            $nextPartido->jugador_rival_dos_id = null;
            $nextPartido->buy = true;
        }
    }
}

}