@extends('auth.layout.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/select2/css/select2.min.css') }}">
    <style>
         .radio-group {
            margin-bottom: 15px;
        }
        .footer-rankings {
            width: 100%;
            padding: 10px 0px 10px 0px;
            z-index: 1000;
                background-color: rgba(0, 0, 0, .03);
        }
        .footer-rankings .group {
               display: flex;
               align-items: center;
               justify-content: flex-end;
               gap: 20px;
               margin-right: 50px;
        }
        #main {
            padding-bottom: 70px; /* Adjust based on footer height */
        }        .btn-disabled {
            opacity: 0.5;
            pointer-events: none;
        }

    </style>
@endsection

@section('main')
    <div id="main" class="show">
        <div class="box">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title"><i class="fas fa-medal fa-1x"></i> Rankings</h3>
                    </div>
                </div>
                <div class="card-body">
                <div class="row">
    <div class="col-md-5">
        <label for="filter_category">Categoría:</label>
        <select name="filter_category" id="filter_category" class="form-control">
            <option value="">--Todos--</option>
        </select>
    </div>
    <div class="col-md-7">
        <div class="row align-items-center">
            <div class="col-md-2 d-flex flex-column">
                <label class="mb-2">Tipo:</label>
                <div class="form-group mb-0">
                <div class="custom-control custom-radio">
                        <input type="radio" id="carrera_maestros_radio" name="tournament_type" class="custom-control-input" value="carrera_maestros" checked>
                        <label class="custom-control-label" for="carrera_maestros_radio">Carrera hacia el torneo de maestros</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input type="radio" id="torneos_radio" name="tournament_type" class="custom-control-input" value="torneos" >
                        <label class="custom-control-label" for="torneos_radio">Torneos por separado</label>
                    </div>
                   
                </div>
            </div>           <div class="col-md-8">
                 <div class="invalid-feedback">
        Por favor seleccione al menos un torneo
    </div>
    <select name="filter_tournaments[]" id="filter_tournaments" class="form-control" required>
        <option value="">Seleccione un torneo</option>
    </select>
    
    <div class="form-group mt-3">
        <label for="filter_jugadores">Filtrar por Jugador (Opcional):</label>
        <select name="filter_jugadores" id="filter_jugadores" class="form-control">
            <option value="">--Todos los jugadores--</option>
        </select>
    </div>
  
</div>
        </div>
    </div>
</div>                </div>                <div class="card-footer text-right">
                    <button type="button" class="btn btn-success mr-2" id="btnAllTen" disabled>
                        <i class="fa fa-trophy"></i> All Ten
                    </button>
                    <button type="button" class="btn btn-primary pull-right" id="btnBuscar">
                        <i class="fa fa-search"></i> Realizar Búsqueda
                    </button>
                </div>
            </div>
        </div>

        <div class="box">
            <div id="partialView"></div>
           
        </div>
    </div>

    {{-- Footer with static buttons --}}
   
@endsection

@section('scripts')
    <script type="text/javascript" src="{{ asset('auth/adminlte3/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('auth/pages/'.strtolower($ViewName).'/index.min.js?v='.\Carbon\Carbon::now()->toDateTimeString()) }}"></script>
    <script>        const $filter_anio = $("#filter_anio"), $filter_torneo = $("#filter_torneo"), $filter_category = $("#filter_category");
        const $partialView = $("#partialView"), $btnBuscar = $("#btnBuscar");
        const $filter_tournaments = $("#filter_tournaments");
        const $filter_jugadores = $("#filter_jugadores");
        const $btnTopTen = $("#btnTopTen");
        const $btnAllTen = $("#btnAllTen");
        const $btnListaRanking = $("#btnListaRanking");
        const $tournamentTypeRadio = $("input[name='tournament_type']");

        // Deshabilitar radio buttons al inicio
        $tournamentTypeRadio.prop('disabled', true);        // Inicializar select2 para torneos (inicialmente deshabilitado)
        $('#filter_tournaments').select2({
            placeholder: "Primero seleccione una categoría",
            allowClear: true,
            disabled: true
        });
        
        // Inicializar select2 para jugadores (inicialmente deshabilitado)
        $('#filter_jugadores').select2({
            placeholder: "Primero seleccione categoría y torneos",
            allowClear: true,
            disabled: true
        });        // Función para validar y habilitar/deshabilitar botones
        function validateRankingButtons() {
            const categoriaSeleccionada = $filter_category.val();
            const torneosSeleccionados = $filter_tournaments.val();
            const jugadorSeleccionado = $filter_jugadores.val();

            if (categoriaSeleccionada && torneosSeleccionados && torneosSeleccionados.length > 0) {
                // Todos los botones se habilitan cuando hay categoría y torneos seleccionados
                $btnTopTen.removeClass('btn-disabled').prop('disabled', false);
                $btnListaRanking.removeClass('btn-disabled').prop('disabled', false);
                $btnAllTen.removeClass('btn-disabled').prop('disabled', false);
            } else {
                $btnTopTen.addClass('btn-disabled').prop('disabled', true);
                $btnListaRanking.addClass('btn-disabled').prop('disabled', true);
                $btnAllTen.addClass('btn-disabled').prop('disabled', true);
            }
        }// Cuando se selecciona una categoría
        $('#filter_category').on('change', function() {
            var categoriaId = $(this).val();
            
            if (categoriaId) {
                // Habilitar radio buttons
                $tournamentTypeRadio.prop('disabled', false);
                
                // Habilitar y limpiar select de torneos
                $('#filter_tournaments')
                    .prop('disabled', false)
                    .val(null)
                    .trigger('change');
                
                // Limpiar y deshabilitar select de jugadores
                $('#filter_jugadores')
                    .prop('disabled', true)
                    .val(null)
                    .trigger('change');

                // Simular cambio de radio button para recargar torneos
                $tournamentTypeRadio.filter(':checked').trigger('change');
            } else {
                // Deshabilitar radio buttons
                $tournamentTypeRadio.prop('disabled', true);
                
                // Deshabilitar select de torneos
                $('#filter_tournaments')
                    .prop('disabled', true)
                    .val(null)
                    .trigger('change');
                    
                // Deshabilitar select de jugadores
                $('#filter_jugadores')
                    .prop('disabled', true)
                    .val(null)
                    .trigger('change');
            }

            // Validar botones de ranking
            validateRankingButtons();
        });

        // Manejar cambio de radio button
            // Manejar cambio de radio button
// Manejar cambio de radio button
$tournamentTypeRadio.on('change', function() {
    const selectedType = $(this).val();
    
    if (selectedType === 'carrera_maestros') {
        $('#filter_tournaments')
            .prop('disabled', true)
            .val(null)
            .trigger('change');

        // Cargar torneos con carrera directamente
        $.ajax({
            url: "{{ route('rankings.torneos-por-categoria') }}",
            method: 'GET',
            data: {
                categoria_id: $filter_category.val(),
                carrera: true
            },
            success: function(data) {
                // Filtrar y aplanar los torneos con carrera
                const carreraTorneos = data.flatMap(year => 
                    year.children.filter(torneo => torneo.carrera === true ||  torneo.carrera === false)
                );
                
                // Limpiar y popular el select
                $('#filter_tournaments').empty();
                
                // Crear un select2 con los datos filtrados
                $('#filter_tournaments').select2({
                    data: carreraTorneos,
                    placeholder: "Torneos con carrera",
                    multiple: true,
                    disabled: true
                });

                // Seleccionar todos los torneos con carrera
                const torneoIds = carreraTorneos.map(torneo => torneo.id);
                $('#filter_tournaments').val(torneoIds).trigger('change');
                
                // Validar botones de ranking
                validateRankingButtons();
            },
            error: function() {
                console.error('Error al cargar torneos con carrera');
            }
        });
    } else {
        // Restaurar select de torneos para selección normal
        $('#filter_tournaments')
            .prop('disabled', false)
            .val(null)
            .trigger('change');
         // Limpiar y popular el select
          $('#filter_tournaments').empty();
        $('#filter_tournaments').select2({
            placeholder: "Seleccione torneos",
            multiple: false,
            ajax: {
                url: "{{ route('rankings.torneos-por-categoria') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        categoria_id: $filter_category.val(),
                        search: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });
    }
});        // Validar botones cuando cambian categoría o torneos
        $filter_category.on('change', validateRankingButtons);
        $filter_tournaments.on('change', function() {
            validateRankingButtons();
            cargarJugadores();
        });

        // Función para cargar jugadores
        function cargarJugadores() {
            const categoriaId = $filter_category.val();
            const torneos = $filter_tournaments.val();
            
            if (categoriaId && torneos && torneos.length > 0) {
                // Habilitar select de jugadores y mostrar loading
                $('#filter_jugadores')
                    .prop('disabled', false)
                    .empty()
                    .append('<option value="">Cargando jugadores...</option>');
                
                $.ajax({
                    url: "{{ route('rankings.lista-jugadores') }}",
                    method: 'GET',
                    data: {
                        filter_categoria: categoriaId,
                        torneos: torneos
                    },
                    success: function(data) {
                        // Limpiar y popular el select
                        $('#filter_jugadores').empty();
                        $('#filter_jugadores').append('<option value="">--Todos los jugadores--</option>');
                        
                        if (data.length > 0) {
                            data.forEach(function(jugador) {
                                $('#filter_jugadores').append(
                                    '<option value="' + jugador.id + '">' + 
                                    jugador.nombre  +
                                    '</option>'
                                );
                            });
                        } else {
                            $('#filter_jugadores').append('<option value="">No hay jugadores disponibles</option>');
                        }
                    },
                    error: function() {
                        $('#filter_jugadores').empty();
                        $('#filter_jugadores').append('<option value="">Error al cargar jugadores</option>');
                    }
                });
            } else {
                // Deshabilitar y limpiar select de jugadores
                $('#filter_jugadores')
                    .prop('disabled', true)
                    .val(null)
                    .trigger('change');            }
        }

        // Evento change para el select de jugadores
        $('#filter_jugadores').on('change', function() {
            validateRankingButtons();
        });

        // Manejadores de eventos para los botones de rankings
        $('#btnTopTen').on('click', function() {
            var torneos = $filter_tournaments.val();
            
            var url = `/auth/rankings/botones?` + $.param({
                type: 'top_ten',
                filter_anio: $filter_anio.val(),
                filter_categoria: $filter_category.val(),
                torneos: torneos
            });
            
            window.open(url, '_blank');
        });
        
        $('#btnAllTen').on('click', function() {
            var torneos = $filter_tournaments.val();
            var jugadorId = $filter_jugadores.val();
              var maestros = true;
         if (!Array.isArray(torneos) && !isNaN(torneos)) {
        torneos = [torneos];
        maestros =false;
        
    }
            
            var url = `/auth/rankings/botones?` + $.param({
                type: 'top_ten',
                filter_anio: $filter_anio.val(),
                filter_categoria: $filter_category.val(),
                torneos: torneos,
                filter_jugador: jugadorId,
                all: true,
                maestros
            });
            
            window.open(url, '_blank');
        });$('#btnListaRanking').on('click', function() {
            var torneos = $filter_tournaments.val();
            
            var url = `/auth/rankings/botones?` + $.param({
                type: 'lista_ranking',
                filter_anio: $filter_anio.val(),
                filter_categoria: $filter_category.val(),
                torneos: torneos
            });
            
            window.open(url, '_blank');
        });
    </script>
    <style>
    .custom-select {
        background-color: #007bff !important;
        color: white; /* Optional: to change the text color */
    }

    .select2-selection__choice {
        background-color: #007bff !important;
        color: white !important;
    }

    .select2-selection__choice__remove {
        color: white !important;
    }
    </style>
@endsection