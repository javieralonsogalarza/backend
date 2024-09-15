@extends('auth.layout.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/select2/css/select2.min.css') }}">
@endsection

@section('main')
    <div class="box">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="fas fa-edit fa-1x"></i> Listado de Jugadores </h3>
                </div>
                <div class="card-settings">
                    <button type="button" class="btn btn-primary" id="btnRegistrar{{$ViewName}}"><i class="fa fa-plus"></i> Agregar {{$ViewName}}</button>
                    <button type="button" class="btn btn-success" id="btnImportarMasivo"><i class="fa fa-file-excel"></i> Importar Jugadores</button>
                    <button type="button" class="btn btn-danger" id="btnEliminarMasivo"><i class="fa fa-trash"></i> Eliminar Jugadores</button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <!--<div class="form-group row content-datable-txt-search">
                            <label for="txtSearch" class="col- col-form-label">Buscar:</label>
                            <div class="col-sm-10">
                                <input id="txtSearch" type="search" class="datable-txt-search form-control">
                            </div>
                        </div>-->
                        <label for="txtSearch" class="col- col-form-label">Buscar:</label>
                        <input id="txtSearch" type="search" class="datable-txt-search form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="filter_categoria_id" class="col- col-form-label">Categoría:</label>
                        <select name="filter_categoria_id" id="filter_categoria_id" class="form-control">
                            <option value="">Todos</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filter_sexo_id" class="col- col-form-label">Sexo:</label>
                        <select name="filter_sexo_id" id="filter_sexo_id" class="form-control">
                            <option value="">Todos</option>
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="table{{$ViewName}}" class="table table-bordered table-striped"></table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $(function (){
            getData = () => {
                const obj = {};
                obj.ViewName = "{{ strtolower($ViewName) }}";
                return obj;
            }
        });
    </script>
    <script type="text/javascript" src="{{ asset('auth/adminlte3/plugins/select2/js/select2.js') }}"></script>
    <script type="text/javascript" src="{{ asset('auth/pages/'.strtolower($ViewName).'/index.min.js?v='.\Carbon\Carbon::now()->toDateTimeString()) }}"></script>
@endsection
