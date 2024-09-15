@extends('auth.layout.app')

@section('main')
    <div class="box">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="fas fa-edit fa-1x"></i> Búsqueda por filtro </h3>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label for="reportrange">Rango de Fecha:</label>
                        <div id="reportrange" class="text-capitalize">
                            <i class="fa fa-calendar"></i>&nbsp; <span></span> <i class="fa fa-angle-down"></i>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="nombre_filter">Nombre:</label>
                        <div class="input-group">
                            <input id="nombre_filter" type="text" class="form-control" placeholder="Escribe la comunidad aquí">
                            <div class="input-group-append">
                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-right">
                <button type="button" class="btn btn-primary" id="btnRegistrar{{$ViewName}}">
                    <i class="fa fa-plus"></i> Agregar Comunidad
                </button>
            </div>

        </div>
    </div>
    <div class="box">
        <div>
            <div id="list-cards-content" class="list-cards-content grid grid-column-3" data-next-page></div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Categoría -->
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
