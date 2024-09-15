@extends('app.layout.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/select2/css/select2.min.css') }}">
@endsection

@section('content')
    <div class="container">
        <div class="box box-filter">
            <div class="card">
                <div class="card-header">
                    <div><h3><i class="fa fa-file"></i> Reporte de Jugador</h3></div>
                </div>
                <div class="card-body">
                    <div class="grid grid-column-1">
                        <div>
                            <label for="filter_jugador">Buscar Jugador:</label>
                            <select name="filter_jugador" id="filter_jugador" class="form-control" style="width: 100% !important;">
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
    <script src="{{ asset('auth/adminlte3/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('auth/adminlte3/plugins/select2/js/select2.js') }}"></script>
    <script type="text/javascript">
        const $slug = "{{ $Model->slug }}";
    </script>
    <script type="text/javascript" src="{{ asset('js/jugadores.min.js?v='.\Carbon\Carbon::now()->toDateTimeString()) }}"></script>
@endsection
