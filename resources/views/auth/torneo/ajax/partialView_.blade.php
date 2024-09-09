<link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/select2/css/select2.min.css') }}">
<div class="modal fade" id="modal{{$ViewName}}LLave" role="dialog" data-backdrop="static" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">LLaves por Sembrado</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('auth.'.strtolower($ViewName).'.grupo'.'.store') }}" id="frm{{$ViewName}}" enctype="multipart/form-data" method="POST"
                  data-ajax="true" class="form" data-ajax-loading="#loading" data-ajax-success="OnSuccess{{$ViewName}}"
                  data-ajax-failure="OnFailure{{$ViewName}}">
                @csrf
                <input type="hidden" id="torneo_id" name="torneo_id" value="{{ $Torneo }}">
                <input type="hidden" id="torneo_categoria_id" name="torneo_categoria_id" value="{{ $Categoria }}">
                <input type="hidden" id="tipo_grupo_id" name="tipo_grupo_id" value="{{ $TipoGrupo }}">
                <input type="hidden" id="tipo" name="tipo" value="select">
                <div class="modal-body">
                    @for($i = 0; $i <= (count($Grupos)/4); $i++)
                        <table class="mt-4 table table-bordered table-striped">
                            <thead>
                            <tr>
                                @foreach($Grupos as $key => $q)
                                    @if($key >= ($i == 0 ? 0 : ($i*4))  && $key <= ($i == 0 ? 3 : (($i+1)*4)-1))
                                     <th class="align-middle text-center" width="25%">{{ $q->nombre }}</th>
                                    @endif
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                @foreach($Grupos as $key => $q)
                                    @if($key >= ($i == 0 ? 0 : ($i*4))  && $key <= ($i == 0 ? 3 : (($i+1)*4)-1))
                                    <td class="align-middle text-center" width="25%">
                                        <input type="hidden" name="grupo_id[]" id="grupo_{{$q->id}}" value="{{ $q->id }}" readonly>
                                        <select name="jugador_id[]" id="jugador_{{$q->id}}" class="form-control" style="width: 100% !important;"></select>
                                    </td>
                                    @endif
                                @endforeach
                            </tr>
                            </tbody>
                        </table>
                    @endfor
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary pull-right">Generar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript" src="{{ asset('auth/adminlte3/plugins/select2/js/select2.js') }}"></script>
<script type="text/javascript">
    $(function(){
        const $modal = $("#modal{{$ViewName}}LLave");
        const $table = $modal.find("table");

        $table.find("tr td").each(function(i, v){
            $(v).find("select").select2({
                placeholder: "Seleccione Jugador",
                allowClear: true,
                ajax: {
                    url: "/auth/{{strtolower($ViewName)}}/jugador/list-json", dataType: "json", type: "GET", delay: 250,
                    data: function(params) {
                        return {
                            nombre: params.term,
                            torneo_id : {{ $Torneo }},
                            jugador_selected_id: JSON.stringify(playersSelected()),
                            torneo_categoria_id : {{ $Categoria }},
                        };
                    },
                    processResults: function(data) {
                        return { results: data.data };
                    },
                    cache: true
                }
            });
        });

        $table.find("select").on("change", function (){
            playersSelected()
        });

        function playersSelected(){
            let $arreglo = [];
            $table.find("tr td").each(function(i, v){ if($(v).find("select").val() != null && !isNaN(parseInt($(v).find("select").val()))){ $arreglo.push(parseInt($(v).find("select").val())); } });
            return $arreglo;
        }

        OnSuccess{{$ViewName}} = (data) => onSuccessForm(data, $("form#frm{{$ViewName}}"), $modal, function (data){
            if(data.Repeat){
                Swal.fire({icon: 'warning', title: 'Algunos jugadores que acaba de asignar ya se enfrentaron con anterioridad en el torneo anterior en la fase de grupos.'});
            }
        });
        OnFailure{{$ViewName}} = () => onFailureForm();
    });
</script>

