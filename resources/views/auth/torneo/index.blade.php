@extends('auth.layout.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/select2/css/select2.min.css') }}">
    <style type="text/css">
        @media print
        {
            @page { size: landscape;}
            .no-print, .no-print * {display: none !important;}
            aside{display: none !important;}
            ol.nav.nav-tabs, ul.nav.nav-tabs{ display: none !important;}
            .row.mt-1{display: none !important;}
            h5, footer, button{display: none !important;}
            .card-body, .p-3, .p-4, .pt-3, .pb-3{ padding: 0!important;}
            td{font-size: 11px !important;padding: 2px !important;}
            #main-content-wrapper, .mt-1, .mt-2, .mt-3 { margin: 0 !important;}
            h3{ font-size: 15px !important; margin-bottom: 0.5rem !important;}
            p{ font-size: 12px !important;margin-bottom: 2rem !important; }
            .mt-6{ margin-top: 1.5rem !important;}
            .swal2-container{ display: none !important; }
            .html-view{display: none !important;}
            .report-view{ display: block !important; }
            a[href]:after { content: none !important; }
            .table-players-view{ display: none !important; }
             p{margin-bottom: 0.2rem !important;}
             p.text-center.text-xs.m-0.mb-1.position-absolute.w-100{font-size: 10px !important;}
            .grid-mapa-content small.text-sm.text-bold.position-absolute.w-100{ font-size: 10px !important; }
        }
    </style>
@endsection

@section('main')
    <div id="main" class="show">
        <div class="box">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title"><i class="fas fa-search fa-1x"></i> Búsqueda por filtro </h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="filter_anio">Año:</label>
                            <select name="filter_anio" id="filter_anio" class="form-control">
                                <option value="">--Todos--</option>
                                <option value="{{ $Anio }}" selected>{{ $Anio }}</option>
                            </select>
                        </div>
                    </div>
                    <!--<div class="row">
                        <div class="col-md-4">
                            <label for="reportrange">Rango de Fecha:</label>
                            <div id="reportrange" class="text-capitalize">
                                <i class="fa fa-calendar"></i>&nbsp; <span></span> <i class="fa fa-angle-down"></i>
                            </div>
                        </div>
                    </div>-->
                </div>
                <div class="card-footer text-right">
                    <button type="button" class="btn btn-primary pull-right" id="btnRegistrar{{$ViewName}}">
                        <i class="fa fa-plus"></i> Agregar {{$ViewName}}
                    </button>
                </div>
            </div>
        </div>

        <div class="box">
            <div>
                <div id="list-cards-content" class="list-cards-content grid grid-column-3" data-next-page></div>
            </div>
        </div>
    </div>

    <div id="info" class="hidden"></div>

@endsection

@section('scripts')
    <!-- Torneo -->
    <script type="text/javascript" src="{{ asset('auth/adminlte3/plugins/daterangepicker/daterangepicker.js') }}"></script>
    <script type="text/javascript" src="{{ asset('auth/adminlte3/plugins/select2/js/select2.js') }}"></script>
    <script type="text/javascript">
        $(function (){
            getData = () => {
                const obj = {};
                obj.ViewName = "{{ strtolower($ViewName) }}";
                return obj;
            }
        });
    </script>
    <script src="{{ asset('auth/pages/'.strtolower($ViewName).'/index.min.js?v='.\Carbon\Carbon::now()->toDateTimeString()) }}"></script>
@endsection
