<div class="modal fade" id="modal{{$ViewName}}"  role="dialog" tabindex="-1" data-backdrop="static" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ ($Model != null ? "Modificar" : "Registrar")." ".$ViewName }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('auth.'.strtolower($ViewName).'.store') }}" id="frm{{$ViewName}}" enctype="multipart/form-data" method="POST"
                  data-ajax="true" class="form" data-ajax-loading="#loading" data-ajax-success="OnSuccess{{$ViewName}}"
                  data-ajax-failure="OnFailure{{$ViewName}}">
                @csrf
                <input type="hidden" id="id" name="id" value="{{ $Model != null ? $Model->id : 0 }}">
                <div class="modal-body">
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <label for="nombre">Nombre: <span class="text-danger">(*)</span></label>
                            <input type="text" name="nombre" id="nombre" class="form-control" value="{{ $Model != null ? $Model->nombre : "" }}" required autocomplete="off" >
                            <span data-valmsg-for="nombre"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-12">
                            <label for="orden">Orden: <span class="text-danger">(*)</span></label>
                            <input type="text" name="orden" id="orden" class="form-control numeric" value="{{ $Model != null && $Model->orden != null ? $Model->orden : $Orden }}" required autocomplete="off" >
                            <span data-valmsg-for="orden"></span>
                        </div>
                    </div>
                    <div class="form-group row mt-3">
                        <div class="col-sm-6">
                            <div class="icheck-primary d-inline">
                                <input type="checkbox" name="dupla" id="dupla" value="1" {{ $Model != null && $Model->dupla ? "checked" : "" }}>
                                <label for="dupla">¿Es Dupla?</label>
                            </div>
                        </div>
                        <div class="col-md-6 ">
                            <div class="icheck-primary d-inline">
                                <input type="checkbox" name="visible" id="visible" value="1" {{ $Model != null && $Model->visible ? "checked" : "" }}>
                                <label for="visible">¿Visible Ranking?</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary pull-right">{{ ($Model != null ? "Modificar" : "Registrar") }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function(){
        const $modal = $("#modal{{$ViewName}}");
        $("input.numeric").inputmask("numeric", { min: 1, max: 9999, digits: 0, removeMaskOnSubmit: false, groupSeparator: ",", groupSize: 3 });
        setTimeout(function (){ $modal.find("form").find("input[type=text]").first().focus().select(); }, 500);
        OnSuccess{{$ViewName}} = (data) => onSuccessForm(data, $("form#frm{{$ViewName}}"), $modal);
        OnFailure{{$ViewName}} = () => onFailureForm();
    })
</script>

