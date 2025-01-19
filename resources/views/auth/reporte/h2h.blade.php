@extends('auth.layout.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/select2/css/select2.min.css') }}">
@endsection

@section('main')
    <div id="main" class="show">
        <div class="box">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title"><i class="fas fa-file-pdf fa-1x"></i> Reporte H2H </h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="torneo">Torneo:</label>
                                <select name="torneo" id="torneo" class="form-control select2" style="width: 100% !important;">
                                    <option value="">Seleccione</option>
                                    @foreach($Torneos as $torneo)
                                    <option value="{{ $torneo->id }}">{{ $torneo->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="categoria">Categoría:</label>
                                <select name="categoria" id="categoria" class="form-control select2" style="width: 100% !important;">
                                    <option value="">Seleccione</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="jugador1">Jugador 1:</label>
                                <select name="jugador1" id="jugador1" class="form-control select2" style="width: 100% !important;">
                                    <option value="">Seleccione</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="jugador2">Jugador 2:</label>
                                <select name="jugador2" id="jugador2" class="form-control select2" style="width: 100% !important;">
                                    <option value="">Seleccione</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="button" class="btn btn-primary pull-right" id="btnBuscar" disabled>
                        <i class="fa fa-search"></i> Realizar Búsqueda
                    </button>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script type="text/javascript" src="{{ asset('auth/adminlte3/plugins/select2/js/select2.js') }}"></script>
    <script src="{{ asset('auth/pages/'.strtolower($ViewName).'/torneo.min.js?v='.\Carbon\Carbon::now()->toDateTimeString()) }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();

            function validateSelection() {
                var jugador1 = $('#jugador1').val();
                var jugador2 = $('#jugador2').val();

                if (jugador1 && jugador2 && jugador1 === jugador2) {
                    alert('No puedes seleccionar el mismo jugador en ambos lados.');
                    $('#btnBuscar').prop('disabled', true);
                } else if (jugador1 && jugador2) {
                    $('#btnBuscar').prop('disabled', false);
                } else {
                    $('#btnBuscar').prop('disabled', true);
                }

                // Deshabilitar la opción seleccionada en el otro selector
                $('#jugador2 option').prop('disabled', false);
                $('#jugador1 option').prop('disabled', false);

                if (jugador1) {
                    $('#jugador2 option[value="' + jugador1 + '"]').prop('disabled', true);
                }

                if (jugador2) {
                    $('#jugador1 option[value="' + jugador2 + '"]').prop('disabled', true);
                }

                $('#jugador1').select2();
                $('#jugador2').select2();
            }

            $('#torneo').on('change', function() {
                var torneoId = $(this).val();
                $('#categoria').empty().append('<option value="">Seleccione</option>');
                $('#jugador1').empty().append('<option value="">Seleccione</option>');
                $('#jugador2').empty().append('<option value="">Seleccione</option>');
                if (torneoId) {
                    $.ajax({
                        url: '{{ "getCategoriasByTorneo" }}',
                        type: 'GET',
                        data: { torneo_id: torneoId },
                        success: function(data) {
                            $.each(data, function(key, value) {
                                $('#categoria').append('<option value="'+ key +'">'+ value +'</option>');
                            });
                        }
                    });
                }
            });

            $('#categoria').on('change', function() {
                var torneoId = $('#torneo').val();
                var categoriaId = $(this).val();
                $('#jugador1').empty().append('<option value="">Seleccione</option>');
                $('#jugador2').empty().append('<option value="">Seleccione</option>');
                if (torneoId && categoriaId) {
                    $.ajax({
                        url: '{{ "getJugadoresByTorneoCategoria" }}',
                        type: 'GET',
                        data: { torneo_id: torneoId, categoria_id: categoriaId },
                        success: function(data) {
                            $.each(data, function(key, value) {
                                $('#jugador1').append('<option value="'+ key +'">'+ value +'</option>');
                                $('#jugador2').append('<option value="'+ key +'">'+ value +'</option>');
                            });
                        }
                    });
                }
            });

            $('#jugador1, #jugador2').on('change', function() {
                validateSelection();
            });

            $('#btnBuscar').on('click', function() {
                var jugador1 = $('#jugador1').val();
                var jugador2 = $('#jugador2').val();
                var categoriaId = $('#categoria').val();

                if (jugador1 && jugador2 && categoriaId) {
                    // Construir la URL con los parámetros
                    var url = `/auth/torneo/h2h/${jugador1}/${jugador2}/${categoriaId}/json`;

                    // Redirigir a la URL
                    window.open(url, '_blank');
                } else {
                    alert('Por favor, selecciona ambos jugadores y una categoría.');
                }
            });

            // Inicializar la validación al cargar la página
            validateSelection();
        });
    </script>
@endsection