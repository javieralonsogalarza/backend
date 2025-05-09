

<div class="card">
      <div class="card-body">
        <div class="p-2">
 @if($RankingsResultYear != null)
    <div class="row">
        <div class="col-sm-12">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th class="text-center">Puesto</th>
                            <th>Nombre del jugador</th>
                            <th class="text-center">Puntos</th>
                            @if($carrera == "true")
                                <th class="text-center" style="width: 50px;">
                                    <span class="text-nowrap">Incluir</span>
                                </th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($Categorias as $q)
                        @foreach($RankingsResultYear as $q2)
                            @if($q->id == $q2->categoria_id)
                                <?php 
                                // Ordenar todos los jugadores
                                $todosJugadores = \App\Models\App::multiPropertySort(collect($q2->jugadores), [['column' => 'puntos', 'order' => 'desc']]);
                                
                                $countSingle = 0; 
                                $countRepeat = 1; 
                                $pointBefore = 0; 
                                $next = false; 
                                $posicionesConsideradas = [];
                                ?>
                                @foreach($todosJugadores as $key => $q3)
                                    @if($carrera == "true" && ($q3['puntos'] > 0) || $carrera == "false")
                                        <?php 
                                        // Lógica de cálculo de puesto diferente según modo
                                        if ($carrera == "false") {
                                            // Modo normal: calcular puesto para todos
                                            $countSingle += 1; 
                                            $pointBefore = $q3['puntos']; 
                                            $posicionActual = $countRepeat = $next ? $countRepeat : $countSingle;
                                        } else {
                                            // Modo carrera: calcular puesto solo para considerados
                                            if ($q3['considerado_ranking'] ?? true) {
                                                $countSingle += 1; 
                                                $pointBefore = $q3['puntos']; 
                                                $posicionActual = $countRepeat = $next ? $countRepeat : $countSingle;
                                                $posicionesConsideradas[$q3['id']] = $posicionActual;
                                            }
                                        }
                                        ?>
                                        <tr>
                                            <td class="text-center"> 
                                                @if($carrera == "true")
                                                    @if($q3['considerado_ranking'] ?? true)
                                                        {{ $posicionesConsideradas[$q3['id']] ?? '' }}
                                                    @endif
                                                @else
                                                    {{ $countRepeat = $next ? $countRepeat : $countSingle }}
                                                @endif
                                            </td>
                                            <td>{{ $q3['nombre'] }}</td>
                                            <td class="text-center">
                                                {{ $q3['puntos'] }}
                                            </td>
                                            @if($carrera == "true")
                                                <td class="text-center">
                                              <div class="icheck-primary d-inline">
    <input type="checkbox" 
           class="player-checkbox"
           id="player-{{ $q3['id'] }}"
           data-player-id="{{ $q3['id'] }}"
           data-points="{{ $q3['puntos'] }}"
           data-categoria-id="{{ $q->id }}"
           data-ranking-ids="{{ implode(',', $RankingIds) }}"
           {{ ($q3['considerado_ranking'] ?? true) ? 'checked' : '' }}
    >
    <label for="player-{{ $q3['id'] }}"></label>
</div>
                                                </td>
                                            @endif
                                            <?php
                                            if(count($todosJugadores->where('puntos', '<', '10000')) > ($key+1)) {
                                                if($q3['puntos'] != \App\Models\App::multiPropertySort($todosJugadores, [['column' => 'puntos', 'order' => 'desc']])[$key+1]['puntos']) {
                                                    $countRepeat += 1; $next = false;
                                                } else {
                                                    $next = true;
                                                }
                                            }
                                            ?>
                                        </tr>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
        </div>
    </div>
    
    <!-- Mantener el footer existente -->
    <div class="footer-rankings">
        <div class="">
            <div class="group" role="group" aria-label="Rankings Options">
                <button type="button" class="btn btn-primary pull-right btn-disabled" id="btnTopTen" disabled>
                    <i class="fa fa-trophy"></i> Top Ten
                </button>
                <button type="button" class="btn btn-primary pull-right btn-disabled" id="btnListaRanking" disabled>
                    <i class="fa fa-list"></i> Lista Ranking
                </button>
            </div>
        </div>
    </div>
</div>
<script>// Access elements from parent document
$(document).ready(function() {
    console.log("Script initialized");
    
    // Get references from parent window using jQuery
    var $filter_category = $(window.parent.document.getElementById('filter_category'));
    var $filter_tournaments = $(window.parent.document.getElementById('filter_tournaments'));
    var $filter_anio = $(window.parent.document.getElementById('filter_anio'));
    
    var $btnTopTen = $('#btnTopTen');
    var $btnListaRanking = $('#btnListaRanking');
    
    // Function to validate and enable/disable buttons
    function validateRankingButtons() {
        var categoriaSeleccionada = $filter_category.val();
        var torneosSeleccionados = $filter_tournaments.val();
        
        if (categoriaSeleccionada && torneosSeleccionados && torneosSeleccionados.length > 0) {
            $btnTopTen.removeClass('btn-disabled').prop('disabled', false);
            $btnListaRanking.removeClass('btn-disabled').prop('disabled', false);
        } else {
            $btnTopTen.addClass('btn-disabled').prop('disabled', true);
            $btnListaRanking.addClass('btn-disabled').prop('disabled', true);
        }
    }
    
    // Add event listeners if elements exist
    if ($filter_category.length) {
        $filter_category.on('change', validateRankingButtons);
    }
    
    if ($filter_tournaments.length) {
        $filter_tournaments.on('change', validateRankingButtons);
    }
    
    // Initial validation
    validateRankingButtons();
    
    // Button handlers
    $btnTopTen.on('click', function() {
        var torneos = $filter_tournaments.val();
        var maestros = true;
         if (!Array.isArray(torneos) && !isNaN(torneos)) {
        torneos = [torneos];
        maestros =false;
        
    }
        console.log('Selected tournaments:', torneos);
        
        var url = '/auth/rankings/botones?' + $.param({
            type: 'top_ten',
            filter_anio: $filter_anio.val(),
            filter_categoria: $filter_category.val(),
            torneos: torneos,
            maestros: maestros,
        });
        
        window.open(url, '_blank');
    });
    
    $btnListaRanking.on('click', function() {
        var torneos = $filter_tournaments.val();
        var maestros =true;
         if (!Array.isArray(torneos) && !isNaN(torneos)) {
        torneos = [torneos];
        maestros =false;
    }
        console.log('Selected tournaments:', torneos);
        
        var url = '/auth/rankings/botones?' + $.param({
            type: 'lista_ranking',
            filter_anio: $filter_anio.val(),
            filter_categoria: $filter_category.val(),
            torneos: torneos,
            maestros: maestros
        });
        
        window.open(url, '_blank');
    });
$('.player-checkbox').change(function() {
    var $checkbox = $(this);
    var rankingIds = $checkbox.data('ranking-ids');
    var categoriaId = $checkbox.data('categoria-id');
    var considerado = $checkbox.is(':checked');
    var playerId = $checkbox.data('player-id');
    
    $.ajax({
        url: '/auth/rankings/update-player-ranking-consideration',
        method: 'POST',
        data: {
            ranking_ids: rankingIds,
            categoria_id: categoriaId,
            considerado: considerado ? true :false,
            player_id: playerId,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
                                recalcularPuestos();


            console.log('Consideración de jugador actualizada');
            // Opcional: mostrar mensaje de éxito
        },
        error: function(xhr, status, error) {
            console.error('Error al actualizar consideración:', error);
            // Revertir el checkbox si hay un error
            $checkbox.prop('checked', !considerado);
        }
    });
});
function recalcularPuestos() {
    // Iterar por cada categoría
    $('table').each(function() {
        var $tabla = $(this);
        var esCarrera = '{{ $carrera }}' === 'true';
        
        // Resetear contadores
        var countSingle = 0;
        var countRepeat = 1;
        var pointBefore = 0;
        var next = false;
        
        // Obtener todas las filas
        var $filas = $tabla.find('tbody tr');
        
        // Filtrar y ordenar filas por puntos
        var filasOrdenadas = $filas.get().sort(function(a, b) {
            var puntosA = parseFloat($(a).find('td:nth-child(3)').text()) || 0;
            var puntosB = parseFloat($(b).find('td:nth-child(3)').text()) || 0;
            return puntosB - puntosA;
        });
        
        // Recalcular puestos
        $(filasOrdenadas).each(function(index) {
            var $fila = $(this);
            var $celdaPuesto = $fila.find('td:first-child');
            var $checkbox = $fila.find('.player-checkbox');
            
            // Solo calcular para jugadores considerados en modo carrera
            if (!esCarrera || ($checkbox.length === 0 || $checkbox.is(':checked'))) {
                countSingle++;
                
                // Lógica para manejar empates
                var puesto = next ? countRepeat : countSingle;
                $celdaPuesto.text(puesto);
                
                // Preparar para siguiente iteración
                var puntosActuales = parseFloat($fila.find('td:nth-child(3)').text()) || 0;
                
                if (index < filasOrdenadas.length - 1) {
                    var puntosSiguiente = parseFloat($(filasOrdenadas[index + 1]).find('td:nth-child(3)').text()) || 0;
                    
                    if (puntosActuales !== puntosSiguiente) {
                        countRepeat = countSingle + 1;
                        next = false;
                    } else {
                        next = true;
                    }
                }
            } else {
                // Para jugadores no considerados en modo carrera
                $celdaPuesto.text('');
            }
        });
    });
}
});


</script>

