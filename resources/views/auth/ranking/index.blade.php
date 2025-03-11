@extends('auth.layout.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/select2/css/select2.min.css') }}">
    <style>
        .footer-rankings {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: #f8f9fa;
            padding: 10px 0;
            z-index: 1000;
            box-shadow: 0 -2px 5px rgba(0,0,0,0.1);
        }
        .footer-rankings .btn-group {
            display: flex;
            justify-content: center;
            width: 100%;
        }
        #main {
            padding-bottom: 70px; /* Adjust based on footer height */
        }
        .btn-disabled {
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
                        <h3 class="card-title"><i class="fas fa-star fa-1x"></i> Rankings</h3>
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
                            <label for="filter_tournaments">Torneos:</label>
                            <select name="filter_tournaments[]" id="filter_tournaments" class="form-control" multiple="multiple">
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
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
    <div class="footer-rankings">
        <div class="container">
            <div class="btn-group" role="group" aria-label="Rankings Options">
                <button type="button" class="btn btn-outline-primary btn-disabled" id="btnTopTen" disabled>
                    <i class="fa fa-trophy"></i> Top Ten
                </button>
                <button type="button" class="btn btn-outline-secondary btn-disabled" id="btnListaRanking" disabled>
                    <i class="fa fa-list"></i> Lista Ranking
                </button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript" src="{{ asset('auth/adminlte3/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('auth/pages/'.strtolower($ViewName).'/index.min.js?v='.\Carbon\Carbon::now()->toDateTimeString()) }}"></script>
    <script>
        const $filter_anio = $("#filter_anio"), $filter_torneo = $("#filter_torneo"), $filter_category = $("#filter_category");
        const $partialView = $("#partialView"), $btnBuscar = $("#btnBuscar");
        const $filter_tournaments = $("#filter_tournaments");
        const $btnTopTen = $("#btnTopTen");
        const $btnListaRanking = $("#btnListaRanking");

        // Inicializar select2 para torneos (inicialmente deshabilitado)
        $('#filter_tournaments').select2({
            placeholder: "Primero seleccione una categoría",
            allowClear: true,
            disabled: true
        });

        // Función para validar y habilitar/deshabilitar botones
        function validateRankingButtons() {
            const categoriaSeleccionada = $filter_category.val();
            const torneosSeleccionados = $filter_tournaments.val();

            if (categoriaSeleccionada && torneosSeleccionados && torneosSeleccionados.length > 0) {
                $btnTopTen.removeClass('btn-disabled').prop('disabled', false);
                $btnListaRanking.removeClass('btn-disabled').prop('disabled', false);
            } else {
                $btnTopTen.addClass('btn-disabled').prop('disabled', true);
                $btnListaRanking.addClass('btn-disabled').prop('disabled', true);
            }
        }

        // Cuando se selecciona una categoría
        $('#filter_category').on('change', function() {
            var categoriaId = $(this).val();
            
            // Habilitar y limpiar select de torneos
            $('#filter_tournaments').prop('disabled', false)
                .val(null)
                .trigger('change');

            // Cargar torneos para la categoría seleccionada
            $('#filter_tournaments').select2({
                placeholder: "Seleccione torneos",
                multiple: true,
                ajax: {
                    url: "{{ route('rankings.torneos-por-categoria') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            categoria_id: categoriaId,
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
        });

        // Validar botones cuando cambian categoría o torneos
        $filter_category.on('change', validateRankingButtons);
        $filter_tournaments.on('change', validateRankingButtons);

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

        $('#btnListaRanking').on('click', function() {
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