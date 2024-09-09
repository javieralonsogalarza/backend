@extends('app.layout.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('auth/css/layout.min.css') }}">
@endsection

@section('content')
    <div class="container">
        <div class="box box-filter">
            <div class="card">
                <div class="card-header">
                    <div><h3 style="color: black !important;"><i class="fa fa-trophy"></i> Torneos</h3></div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="filter_anio">Año</label>
                            <select name="filter_anio" class="form-control" id="filter_anio">
                                <option value="{{ $Anio }}">{{ $Anio }}</option>
                            </select>
                        </div>
                        <!--<div class="col-md-8">
                            <label for="filter_torneo">Torneo</label>
                            <select name="filter_torneo" class="form-control" id="filter_torneo">
                                <option value="">Seleccione</option>
                            </select>
                        </div>-->
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="button" class="btn btn-primary pull-right" id="btnBuscar">
                        <i class="fa fa-search"></i> Realizar Búsqueda
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="box">
            <div id="list-cards-content" class="list-cards-content grid grid-column-3" data-next-page></div>
            <div id="partialView" class="hidden"></div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('auth/adminlte3/plugins/inputmask/jquery.inputmask.min.js') }}"></script>
    <script src="{{ asset('auth/adminlte3/plugins/inputmask/inputmask.min.js') }}"></script>
    <script src="{{ asset('auth/adminlte3/plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('auth/adminlte3/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('auth/adminlte3/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('auth/adminlte3/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('auth/adminlte3/plugins/select2/js/select2.js') }}"></script>
    <script type="text/javascript">
        const $slug = "{{ $Model->slug }}";
    </script>
    <script type="text/javascript" src="{{ asset('auth/layout.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/torneos.min.js?v='.\Carbon\Carbon::now()->toDateTimeString()) }}"></script>
@endsection
