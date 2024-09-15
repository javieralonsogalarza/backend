@extends('app.layout.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/select2/css/select2.min.css') }}">
@endsection

@section('content')
    <div class="container">
        <div class="box box-filter">
            <div class="card">
                <div class="card-header">
                    <div><h3><i class="fa fa-star"></i> Rankings</h3></div>
                </div>
                <div class="card-body">
                    <div class="grid grid-column-2">
                        <div>
                            <label for="filter_anio">Año:</label>
                            <select name="filter_anio" id="filter_anio" class="form-control">
                                <option value="">--Todos--</option>
                            </select>
                        </div>
                        <div>
                            <label for="filter_category">Categoría:</label>
                            <select name="filter_category" id="filter_category" class="form-control">
                                <option value="">--Todos--</option>
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
    <script type="text/javascript">
        const $slug = "{{ $Model->slug }}";
    </script>
    <script type="text/javascript" src="{{ asset('js/rankings.min.js?v='.\Carbon\Carbon::now()->toDateTimeString()) }}"></script>
@endsection
