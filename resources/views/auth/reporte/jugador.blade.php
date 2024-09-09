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
                        <h3 class="card-title"><i class="fas fa-file-pdf fa-1x"></i> Reporte de Jugador </h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label for="filter_jugador">Buscar Jugador:</label>
                            <select name="filter_jugador" id="filter_jugador" class="form-control" style="width: 100% !important;" required>
                                <option value="">Buscar a un jugador</option>
                                @foreach($Jugadores as $q)
                                    <option value="{{ $q->id }}">{{ $q->nombre_completo }}</option>
                                @endforeach
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
@endsection

@section('scripts')
    <script type="text/javascript" src="{{ asset('auth/adminlte3/plugins/select2/js/select2.js') }}"></script>
    <script src="{{ asset('auth/pages/'.strtolower($ViewName).'/index.min.js?v='.\Carbon\Carbon::now()->toDateTimeString()) }}"></script>
@endsection
