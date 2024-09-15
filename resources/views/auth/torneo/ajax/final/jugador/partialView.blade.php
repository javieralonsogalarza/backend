@inject('App', 'App\Models\App')
<div class="modal fade" id="modal{{$ViewName}}FinalPartidoJugar" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mejores Terceros</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('auth.'.strtolower($ViewName).'.faseFinalPlayerTercerosStore') }}" id="frm{{$ViewName}}FinalPartidoJugar" enctype="multipart/form-data" method="POST"
                  data-ajax="true" class="form" data-ajax-loading="#loading" data-ajax-success="OnSuccess{{$ViewName}}" autocomplete="off"
                  data-ajax-failure="OnFailure{{$ViewName}}">
                @csrf
                <input type="hidden" name="torneo_id" value="{{ $TorneoCategoria->torneo->id }}">
                <input type="hidden" name="torneo_categoria_id" value="{{ $TorneoCategoria->id }}">
                <div class="modal-body">
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <label for="jugadores_terceros">Cantidad: <span class="text-danger">(*)</span></label>
                            <input type="text" name="jugadores_terceros" id="jugadores_terceros" class="form-control numeric-game" value="{{ $TorneoCategoria->clasificados_terceros }}" required>
                            <span data-valmsg-for="jugadores_terceros"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary pull-right">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function(){

        $("input.numeric-game").inputmask("numeric", { min: 0, max : {{ $Terceros }}, digits: 0, removeMaskOnSubmit: true, groupSeparator: ",", groupSize: 3, placeholder: "" });

        const $modal = $("#modal{{$ViewName}}FinalPartidoJugar");
        OnSuccess{{$ViewName}} = (data) => onSuccessForm(data, $("form#frm{{$ViewName}}FinalPartidoJugar"), $modal);
        OnFailure{{$ViewName}} = () => onFailureForm();
    })
</script>
