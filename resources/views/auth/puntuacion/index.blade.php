@extends('auth.layout.app')

@section('main')
    <div class="box">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="fas fa-edit fa-1x"></i> Puntuaciones</h3>
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
                        @foreach($Puntuaciones->where('type', 0) as $key => $q)
                                <tr>
                                    <td align="center" class="text-center" width="50">{{ $key+1 }}</td>
                                    <td>{{ $q->nombre }}</td>
                                    <td width="50"><input class="numeric form-input" type="text" name="puntos[]" id="punto_{{$q->id}}" value="{{ $q->puntos }}"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @php
                        $configuraciones = $Puntuaciones->where('type', 1)->keyBy('nombre');
                    @endphp
                    <div >
                        <h3 style="font-size: 1.1rem; margin-top: 20px;">Configurar puntos Ranking por resultado en cada partido</h3>
                        <div style="width: 22.5%;">
                        <div class="form-group" style="display: flex; justify-content: space-between;">
                            <label for="puntos_ganador_2_0">Puntos Ganador (2-0):</label>
                            <input class="numeric form-input" type="text" name="puntos_ganador_2_0" id="puntos_ganador_2_0" value="{{ $configuraciones['puntos_ganador_2_0']->puntos ?? 15 }}" style="width: 50px !important;">
                        </div>
                        <div class="form-group" style="display: flex; justify-content: space-between;">
                            <label for="puntos_perdedor_2_0">Puntos Perdedor (2-0):</label>
                            <input class="numeric form-input" type="text" name="puntos_perdedor_2_0" id="puntos_perdedor_2_0" value="{{ $configuraciones['puntos_perdedor_2_0']->puntos ?? 0 }}" style="width: 50px !important;">
                        </div>
                        <div class="form-group" style="display: flex; justify-content: space-between;">
                            <label for="puntos_ganador_2_1">Puntos Ganador (2-1):</label>
                            <input class="numeric form-input" type="text" name="puntos_ganador_2_1" id="puntos_ganador_2_1" value="{{ $configuraciones['puntos_ganador_2_1']->puntos ?? 10 }}" style="width: 50px !important;">
                        </div>
                        <div class="form-group" style="display: flex; justify-content: space-between;">
                            <label for="puntos_perdedor_2_1">Puntos Perdedor (2-1):</label>
                            <input class="numeric form-input" type="text" name="puntos_perdedor_2_1" id="puntos_perdedor_2_1" value="{{ $configuraciones['puntos_perdedor_2_1']->puntos ?? 5 }}" style="width: 50px !important;">
                        </div>
                        <div class="form-group" style="display: flex; justify-content: space-between;">
                            <label for="puntos_ganador_wo">Puntos Ganador (WO):</label>
                            <input class="numeric form-input" type="text" name="puntos_ganador_wo" id="puntos_ganador_wo" value="{{ $configuraciones['puntos_ganador_wo']->puntos ?? 3 }}" style="width: 50px !important;">
                        </div>
                        <div class="form-group" style="display: flex; justify-content: space-between;">
                            <label for="puntos_perdedor_wo">Puntos Perdedor (WO):</label>
                            <input class="numeric form-input" type="text" name="puntos_perdedor_wo" id="puntos_perdedor_wo" value="{{ $configuraciones['puntos_perdedor_wo']->puntos ?? 0 }}" style="width: 50px !important;">
                        </div>
                        </div>
                    </div>  
                </div>
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
