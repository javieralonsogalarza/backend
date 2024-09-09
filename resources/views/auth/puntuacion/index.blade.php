@extends('auth.layout.app')

@section('main')
    <div class="box">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="fas fa-edit fa-1x"></i> Puntuaciones </h3>
                </div>
            </div>
            <form action="{{ route('auth.'.strtolower($ViewName).'.store') }}" id="frm{{$ViewName}}" enctype="multipart/form-data" method="POST"
                  data-ajax="true" class="form" data-ajax-loading="#loading" data-ajax-success="OnSuccess{{$ViewName}}"
                  data-ajax-failure="OnFailure{{$ViewName}}">
                <div class="card-body">
                    @csrf
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Nombre</th>
                                <th width="100">Puntos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($Puntuaciones as $key => $q)
                                <tr>
                                    <td align="center" class="text-center" width="50">{{ $key+1 }}</td>
                                    <td>{{ $q->nombre }}</td>
                                    <td width="50"><input class="numeric form-input" type="text" name="puntos[]" id="punto_{{$q->id}}" value="{{ $q->puntos }}"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary" id="btnActualizar{{$ViewName}}">
                        Modificar
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $(function (){
            $("input.numeric").inputmask("numeric", { min: 0, digits: 0, removeMaskOnSubmit: true, groupSeparator: ",", groupSize: 3 });
            OnSuccess{{$ViewName}} = (data) => onSuccessForm(data, $("form#frm{{$ViewName}}"), null, null, true);
            OnFailure{{$ViewName}} = () => onFailureForm();
        });
    </script>
@endsection
