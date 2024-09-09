@extends('auth.layout.app')

@section('main')
    <div class="box">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="fas fa-edit fa-1x"></i> Listado de Grupos </h3>
                </div>
                <div class="card-settings">
                    <button type="button" class="btn btn-primary" id="btnRegistrar{{$ViewName}}">
                        <i class="fa fa-plus"></i> Agregar {{$ViewName}}
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table id="table{{$ViewName}}" class="table table-bordered table-striped"></table>
            </div>
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
