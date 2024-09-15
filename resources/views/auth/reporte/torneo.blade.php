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
                        <h3 class="card-title"><i class="fas fa-file-pdf fa-1x"></i> Reporte de Torneo </h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="filter_torneo">Torneo:</label>
                                <select name="filter_torneo" id="filter_torneo" class="form-control" style="width: 100% !important;">
                                    <option value="">Seleccione</option>
                                    @foreach($Torneos as $q)
                                        <option value="{{ $q->id }}">{{ $q->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="filter_categoria">Categoría:</label>
                                <select name="filter_categoria" id="filter_categoria" class="form-control" style="width: 100% !important;"></select>
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

        <div class="box">
            <div class="card">
                <div class="card-body">
                    <iframe id="iframeReporte" style="width: 100%; height: 600px;border: 1px solid #000000"></iframe>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script type="text/javascript" src="{{ asset('auth/adminlte3/plugins/select2/js/select2.js') }}"></script>
    <script src="{{ asset('auth/pages/'.strtolower($ViewName).'/torneo.min.js?v='.\Carbon\Carbon::now()->toDateTimeString()) }}"></script>
@endsection
