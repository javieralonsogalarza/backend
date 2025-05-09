<div class="card">
    <div class="card-body">
        <div class="p-2">
            <div class="row">
                <div class="col-sm-12">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th></th>
                                    @foreach($Torneos as $q)
                                        <th class="text-center">{{ $q->nombre }}</th>
                                    @endforeach
                                    <th class="text-center">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($Categorias as $q)
                                    <tr>
                                        <td></td>
                                        @if($filterCategoria == null)
                                            <td class="text-center text-bold text-lg">{{ $q->nombre }}</td>
                                        @else
                                            <td class="text-center"></td>
                                        @endif
                                        @foreach($Torneos as $q2)
                                            <td class="text-center"></td>
                                        @endforeach
                                        <td class="text-center"></td>
                                    </tr>
                                    @foreach($Rankings as $q2)
                                        @if($q->id == $q2->categoria_id)
                                            <?php 
                                            // Ordenar jugadores por puntos
                                            $jugadoresOrdenados = collect($q2->jugadores)
                                            ->sortBy([
                                                ['puntos', 'desc'],
                                                ['nombre', 'asc']
                                            ]);
                                            
                                            // Calcular posiciones considerando empates
                                            $posiciones = [];
                                            $posicionActual = 1;
                                            $ultimoPuntaje = null;
                                            $empate = 0;

                                            foreach ($jugadoresOrdenados as $index => $jugador) {
                                                if ($ultimoPuntaje === null || $jugador['puntos'] != $ultimoPuntaje) {
                                                    $posicionActual += $empate;
                                                    $empate = 0;
                                                }
                                                
                                                $posiciones[$index] = $posicionActual;
                                                $ultimoPuntaje = $jugador['puntos'];
                                                $empate++;
                                            }
                                            ?>
                                            @foreach($jugadoresOrdenados as $key => $q3)
                                                <tr>
                                                    <td class="text-center">
                                                        {{ $posiciones[$key] }}
                                                    </td>
                                                    <td>{{ $q3['nombre'] }}</td>
                                                    @foreach($Torneos as $q4)
                                                        @php
                                                        $torneoJugador = collect($q3['torneos'])->where('id', $q4->id)->first();
                                                        $puntosTorneo = $torneoJugador 
                                                            ? (isset($torneoJugador['categorias'][0]) 
                                                                ? $torneoJugador['categorias'][0]['puntos'] 
                                                                : (isset($torneoJugador['puntos']) ? $torneoJugador['puntos'] : 0)
                                                            ) 
                                                            : 0;
                                                        @endphp
                                                        <td class="text-center">
                                                            {{ $puntosTorneo > 0 ? $puntosTorneo : '' }}
                                                        </td>
                                                    @endforeach

                                                    <td class="text-center">{{ $q3['puntos'] }}</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>