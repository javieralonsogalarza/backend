<?php

public function faseFinalPartidoStore(Request $request)
{
    $Result = (object)['Success' => false, 'Message' => null, 'Errors' => null];

    try {
        DB::beginTransaction();
        
        // Validate double WO not allowed in finals
        if ($request->resultado === '-' && $this->isDoubleWOInFinal($request)) {
            $Result->Message = "No se permite doble WO en la final. Por favor, ingrese un resultado válido.";
            DB::rollBack();
            return response()->json($Result);
        }

        $request->merge(['fecha_actual' => Carbon::now()->toDateString()]);

        // Validate input
        $validator = $this->validatePartidoInput($request);
        if ($validator->fails()) {
            $Result->Errors = $validator->errors();
            return response()->json($Result);
        }

        // Process based on result type
        if ($request->resultado !== '-') {
            $result = $this->processNormalResult($request);
        } else {
            $result = $this->processWalkoverResult($request);
        }

        if ($result['success']) {
            DB::commit();
            $Result->Success = true;
        } else {
            DB::rollBack();
            $Result->Message = $result['message'];
        }

    } catch (\Exception $e) {
        $Result->Message = $e->getMessage();
        DB::rollBack();
    }

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
        'fecha_inicio' => 'required|date|date_format:Y-m-d',
        'fecha_final' => 'required|date|date_format:Y-m-d|after_or_equal:fecha_inicio',
        'resultado' => 'required',
    ];

    // Additional validations for non-walkover results
    if ($request->resultado !== '-') {
        $validationRules = array_merge($validationRules, [
            'jugador_local_id' => 'required',
            'jugador_local_set' => 'required|numeric',
            'jugador_local_juego' => 'required|numeric',
            'jugador_rival_id' => 'required',
            'jugador_rival_set' => 'required|numeric',
            'jugador_rival_juego' => 'required|numeric'
        ]);
    }

    return Validator::make($request->all(), $validationRules);
}

/**
 * Process normal match result (not walkover)
 */
private function processNormalResult(Request $request): array
{
    // Business logic validations
    if ($request->jugador_local_id == $request->jugador_rival_id) {
        return ['success' => false, 'message' => "El jugador ganador no puede ser el mismo al jugador rival"];
    }
    
    if ($request->jugador_rival_set > $request->jugador_local_set) {
        return ['success' => false, 'message' => "El jugador ganador no puede tener menor sets que el jugador rival"];
    }

    if ($request->id != 0) {
        $partido = $this->findPartido($request->id);
        if (!$partido) {
            return ['success' => false, 'message' => "Partido no encontrado"];
        }

        $this->updatePartidoWithResult($partido, $request);
        
        if ($partido->save()) {
            if ($partido->estado_id == App::$ESTADO_FINALIZADO && $partido->fase > 1) {
                $this->processNextRound($partido, $request);
            }
            return ['success' => true, 'message' => null];
        } else {
            return ['success' => false, 'message' => "Algo salió mal, hubo un error al guardar."];
        }
    }

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
    $partido->jugador_ganador_uno_id = $partido->multiple ? explode("-", $request->jugador_local_id)[0] : $request->jugador_local_id;
    $partido->jugador_ganador_dos_id = $partido->multiple ? explode("-", $request->jugador_local_id)[1] : null;
    $partido->jugador_local_set = $request->jugador_local_set;
    $partido->jugador_local_juego = $request->jugador_local_juego;
    $partido->jugador_rival_set = $request->jugador_rival_set;
    $partido->jugador_rival_juego = $request->jugador_rival_juego;
    $partido->fecha_inicio = $request->fecha_inicio;
    $partido->fecha_final = $request->fecha_final;
    $partido->resultado = $request->resultado;
    $partido->estado_id = $request->estado_id;
    $partido->user_update_id = Auth::guard('web')->user()->id;
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
    $nextPhase = $this->calculateNextPhase($partido->fase);
    
    if ($partido->fase == 32) {
        $nextConfig = $this->getPhase32NextConfig($partido);
        if (!$nextConfig) {
            return;
        }
        
        $nextPartido = $this->findOrCreateNextPartido32($partido, $nextConfig);
    } else {
        $nextPartido = $this->findOrCreateNextPartido($partido, $nextPhase);
    }

    if ($nextPartido) {
        $this->assignWinnerToNextPartido($nextPartido, $partido, $request->position);
        $nextPartido->save();
        
        $this->handleByeLogic($nextPartido);
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
        $this->assignByeToNextPartido($nextPartido, $request->position);
        $nextPartido->save();
        
        $this->handleByeLogic($nextPartido);
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
    $newPartido->fecha_inicio = Carbon::parse($basePartido->fecha_final)->addDay(1);
    $newPartido->fecha_final = Carbon::parse($basePartido->fecha_final)->addDay(6);
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
private function assignWinnerToNextPartido(Partido $nextPartido, Partido $currentPartido, int $position): void
{
    // Determine correct position based on bracket structure and phase
    $correctPosition = $this->determineCorrectPosition($currentPartido, $nextPartido->fase);
    
    if ($correctPosition == 1) {
        // Assign to local position (top)
        if ($nextPartido->jugador_local_uno_id == null && $nextPartido->jugador_local_dos_id == null) {
            $nextPartido->jugador_local_uno_id = $currentPartido->jugador_ganador_uno_id;
            $nextPartido->jugador_local_dos_id = $currentPartido->jugador_ganador_dos_id;
        }
    } else {
        // Assign to rival position (bottom)
        if ($nextPartido->jugador_rival_uno_id == null && $nextPartido->jugador_rival_dos_id == null) {
            $nextPartido->jugador_rival_uno_id = $currentPartido->jugador_ganador_uno_id;
            $nextPartido->jugador_rival_dos_id = $currentPartido->jugador_ganador_dos_id;
        }
    }
}

/**
 * Determine correct position in next round based on bracket structure
 */
private function determineCorrectPosition(Partido $currentPartido, int $nextPhase): int
{
    // For final (phase 1)
    if ($nextPhase == 1) {
        // Semifinal winners: bloque 1 goes to local (top), bloque 2 goes to rival (bottom)
        return ($currentPartido->bloque == 1) ? 1 : 2;
    }
    
    // For semifinals (phase 2) 
    if ($nextPhase == 2) {
        // From cuartos (phase 4): bloques 1,2 go to local, bloques 3,4 go to rival
        return (in_array($currentPartido->bloque, [1, 2])) ? 1 : 2;
    }
    
    // For cuartos (phase 4)
    if ($nextPhase == 4) {
        // From octavos (phase 8): specific mapping based on bracket structure
        return $this->determinePositionFromOctavos($currentPartido);
    }
    
    // For octavos (phase 8) 
    if ($nextPhase == 8) {
        // From round of 16: bloques 1,3,5,7 go to local, bloques 2,4,6,8 go to rival
        return ($currentPartido->bloque % 2 == 1) ? 1 : 2;
    }
    
    // Default fallback
    return ($currentPartido->bloque % 2 == 1) ? 1 : 2;
}

/**
 * Determine position when advancing from octavos to cuartos
 */
private function determinePositionFromOctavos(Partido $currentPartido): int
{
    // Standard bracket structure for octavos to cuartos
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
    return ($currentPartido->bloque % 2 == 1) ? 1 : 2;
}

/**
 * Handle bye logic for automatic advancement
 */
private function handleByeLogic(Partido $partido): void
{
    if (!($partido->buy || $partido->buy_all)) {
        return;
    }

    // Check if it's a single bye (one player present)
    $hasLocalPlayer = $partido->jugador_local_uno_id != null;
    $hasRivalPlayer = $partido->jugador_rival_uno_id != null;
    
    if (($hasLocalPlayer && !$hasRivalPlayer) || (!$hasLocalPlayer && $hasRivalPlayer)) {
        $this->autoFinishByeMatch($partido);
        
        if ($partido->fase > 1) {
            $this->createNextRoundFromBye($partido);
        }
    }
    // Handle double bye case
    else if ($partido->buy && $partido->buy_all) {
        $this->autoFinishDoubleByeMatch($partido);
        
        if ($partido->fase > 1) {
            $this->createNextRoundFromDoubleBye($partido);
        }
    }
}

/**
 * Automatically finish match with bye
 */
private function autoFinishByeMatch(Partido $partido): void
{
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
    } else {
        $partido->jugador_ganador_uno_id = $partido->jugador_rival_uno_id;
        $partido->jugador_ganador_dos_id = $partido->jugador_rival_dos_id;
        $partido->jugador_local_set = 0;
        $partido->jugador_local_juego = 0;
        $partido->jugador_rival_set = 2;
        $partido->jugador_rival_juego = 12;
    }
    
    $partido->save();
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
 * Assign winner based on phase and block structure
 */
private function assignWinnerBasedOnPhase(Partido $nextPartido, Partido $currentPartido): void
{
    // Use the same position determination logic
    $correctPosition = $this->determineCorrectPosition($currentPartido, $nextPartido->fase);
    
    if ($correctPosition == 1) {
        $nextPartido->jugador_local_uno_id = $currentPartido->jugador_ganador_uno_id;
        $nextPartido->jugador_local_dos_id = $currentPartido->jugador_ganador_dos_id;
    } else {
        $nextPartido->jugador_rival_uno_id = $currentPartido->jugador_ganador_uno_id;
        $nextPartido->jugador_rival_dos_id = $currentPartido->jugador_ganador_dos_id;
    }
}

/**
 * Assign double bye based on phase and block structure
 */
private function assignDoubleByeBasedOnPhase(Partido $nextPartido, Partido $currentPartido): void
{
    if ($currentPartido->fase == 2) {
        // Final - both positions become bye
        $nextPartido->jugador_local_uno_id = null;
        $nextPartido->jugador_local_dos_id = null;
        $nextPartido->jugador_rival_uno_id = null;
        $nextPartido->jugador_rival_dos_id = null;
        $nextPartido->buy = true;
        $nextPartido->buy_all = true;
    } else {
        // Other phases
        if (in_array($currentPartido->bloque, [1, 2])) {
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