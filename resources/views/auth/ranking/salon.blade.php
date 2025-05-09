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
        .footer-rankings .group {
              display: flex
;
    align-items: center;
    justify-content: center;
    gap: 20px;
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
                        <h3 class="card-title"><i class="fas fa-star fa-1x"></i> Salón de la Fama</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="filter_category">Categoría:</label>
                            <select name="filter_category" id="filter_category" class="form-control">
                                <option value="">--Seleccione Categoría--</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="button" class="btn btn-primary pull-right" id="btnBuscarSalon">
                        <i class="fa fa-search"></i> Realizar Búsqueda
                    </button>
                </div>
            </div>
        </div>

        <div class="box">
            <div id="partialViewSalon"></div>
        </div>
    </div>

    {{-- Footer with static buttons --}}
    <div class="footer-rankings">
        <div class="container">
            <div class="group" role="group" aria-label="Rankings Options">
                <button type="button" class="btn btn-primary pull-right btn-disabled" id="btnSalonFama" disabled>
                    <i class="fa fa-star"></i> Salón de la Fama
                </button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript" src="{{ asset('auth/adminlte3/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('auth/pages/'.strtolower($ViewName).'/index.min.js?v='.\Carbon\Carbon::now()->toDateTimeString()) }}"></script>
    <script>
        const $filter_category = $("#filter_category");
        const $btnSalonFama = $("#btnSalonFama");
        const $partialView = $("#partialViewSalon");
        const $btnBuscar = $("#btnBuscarSalon");

        // Función para validar y habilitar/deshabilitar botón
        function validateSalonFamaButton() {
            const categoriaSeleccionada = $filter_category.val();

            if (categoriaSeleccionada) {
                $btnSalonFama.removeClass('btn-disabled').prop('disabled', false);
            } else {
                $btnSalonFama.addClass('btn-disabled').prop('disabled', true);
            }
        }

        // Validar botón cuando cambia categoría
        $filter_category.on('change', validateSalonFamaButton);

        // Manejador de evento para el botón de Buscar
        $btnBuscar.on("click", function() {
            $.ajax({
                url: `/rankings/partialViewSalon`,
                type: "GET",
                data: {
                    filter_categoria: $filter_category.val()
                },
                dataType: "html",
                cache: false,
                success: function (data) {
                    $partialView.html("").append(data);
                },
                beforeSend: function () {
                    $("#loading").show();
                },
                complete: function () {
                    $("#loading").hide();
                }
            });
        });

        // Manejador de evento para el botón de Salón de la Fama
        $('#btnSalonFama').on('click', function() {
            var url = `/auth/rankings/fama?` + $.param({
                type: 'salon_fama',
                filter_categoria: $filter_category.val()
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