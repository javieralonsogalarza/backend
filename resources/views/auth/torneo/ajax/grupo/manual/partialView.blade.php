<link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/select2/css/select2.min.css') }}">

<div class="row">
    <div class="col-md-12">
        <h5 class="card-title">LLaves Manuales</h5>
    </div>
</div>
<form action="{{ route('auth.'.strtolower($ViewName).'.grupo'.'.manualStore') }}" id="frm{{$ViewName}}{{ $Categoria }}"
      enctype="multipart/form-data" method="POST" data-ajax-confirm="¿Está seguro de generar las llaves manuales?"
      data-ajax="true" class="form" data-ajax-loading="#loading" data-ajax-success="OnSuccess{{$ViewName}}{{ $Categoria }}"
      data-ajax-failure="OnFailure{{$ViewName}}{{ $Categoria }}">
    <input type="hidden" id="torneo_id" name="torneo_id" value="{{ $Torneo }}">
    <input type="hidden" id="torneo_categoria_id" name="torneo_categoria_id" value="{{ $Categoria }}">
    <input type="hidden" id="tipo_grupo_id" name="tipo_grupo_id" value="{{ $TipoGrupo }}">
    @csrf
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                @for($i = 0; $i <= (count($Grupos)/4); $i++)
                    <table id="table{{ $Categoria }}" class="mt-4 table table-bordered table-striped">
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
                                        <select name="jugador_uno_id[]" id="jugador_uno_{{$q->id}}" class="form-control" style="width: 100% !important;"></select>
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($Grupos as $key => $q)
                                @if($key >= ($i == 0 ? 0 : ($i*4))  && $key <= ($i == 0 ? 3 : (($i+1)*4)-1))
                                    <td class="align-middle text-center" width="25%">
                                        <select name="jugador_dos_id[]" id="jugador_dos_{{$q->id}}" class="form-control" style="width: 100% !important;"></select>
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($Grupos as $key => $q)
                                @if($key >= ($i == 0 ? 0 : ($i*4))  && $key <= ($i == 0 ? 3 : (($i+1)*4)-1))
                                    <td class="align-middle text-center" width="25%">
                                        <select name="jugador_tres_id[]" id="jugador_tres_{{$q->id}}" class="form-control" style="width: 100% !important;"></select>
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($Grupos as $key => $q)
                                @if($key >= ($i == 0 ? 0 : ($i*4))  && $key <= ($i == 0 ? 3 : (($i+1)*4)-1))
                                    <td class="align-middle text-center" width="25%">
                                        <select name="jugador_cuatro_id[]" id="jugador_cuatro_{{$q->id}}" class="form-control" style="width: 100% !important;"></select>
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                        </tbody>
                    </table>
                @endfor
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 text-right">
            <button type="button" class="btn btn-default btn-close-view pull-left">Cancelar</button>
            <button type="submit" class="btn btn-primary pull-right">Generar</button>
        </div>
    </div>
</form>

<script type="text/javascript" src="{{ asset('auth/adminlte3/plugins/select2/js/select2.js') }}"></script>
<script type="text/javascript">
    $(function (){
        const $form = $("#frm{{$ViewName}}{{$Categoria}}");
        const $table = $form.find("table#table{{ $Categoria }}");

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

        $form.on("click", ".btn-close-view", function (){
            $("#partialViewManual{{$Categoria}}").html("");
        });

        OnSuccess{{$ViewName}}{{$Categoria}} = (data) => onSuccessForm(data, $("form#frm{{$ViewName}}{{$Categoria}}"), null, function (data){
            if(data.Repeat){
                Swal.fire({icon: 'warning', title: 'Algunos jugadores que acaba de asignar ya se enfrentaron con anterioridad en el torneo anterior en la fase de grupos.'});
            }
            invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $Torneo }}/{{ $Categoria }}`, function (data) {
                $("#main").addClass("hidden");
                $("#info").removeClass("hidden").html("").append(data);
            });
        });
        OnFailure{{$ViewName}}{{$Categoria}} = () => onFailureForm();
    });
</script>
