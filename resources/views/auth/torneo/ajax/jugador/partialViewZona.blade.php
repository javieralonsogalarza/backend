<link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/select2/css/select2.min.css') }}">
<div class="modal fade" id="modal{{$ViewName}}JugadorZona" role="dialog"  data-backdrop="static" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Información
                {{ ($Model->jugador_dupla_id != null ? " de los Jugadores: " : "del Jugador: ") }}
                {{ $Model->jugadorSimple != null && $Model->jugadorDupla != null ?
                    ($Model->jugadorSimple->nombre_completo.' + '.$Model->jugadorDupla->nombre_completo) :
                    ($Model->jugadorSimple != null ? $Model->jugadorSimple->nombre_completo : "" ) }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('auth.'.strtolower($ViewName).'.jugador'.'.zona.'.'store') }}" id="frm{{$ViewName}}" enctype="multipart/form-data" method="POST"
                  data-ajax="true" class="form" data-ajax-loading="#loading"  data-ajax-success="OnSuccess{{$ViewName}}"
                  data-ajax-failure="OnFailure{{$ViewName}}">
                @csrf
                <input type="hidden" id="id" name="id" value="{{ $Model != null ? $Model->id : 0 }}">
                <div class="modal-body">
                    <div class="form-group row">
                        <div class="col-md-12">
                            <label for="zona_id">Zonas: </label>
                            <select name="zona_id" id="zona_id" class="form-control" style="width: 100% !important;">
                                <option value="">Seleccione</option>
                                @foreach($Zonas as $q)
                                    <option value="{{$q->id}}" data-info="{{ $q->nombre }}" {{ $Model != null && $Model->zona_id == $q->id ? "selected" : "" }}>{{ $q->nombre }}</option>
                                @endforeach
                            </select>
                            <span data-valmsg-for="zona_id"></span>
                        </div>
                    </div>
                    <div class="form-group row mt-3 align-items-center">
                        <div class="col-sm-6">
                            <div class="icheck-primary d-inline">
                                <input type="checkbox" name="pago" id="pago" value="1" {{ $Model != null && $Model->pago ? "checked" : "" }}>
                                <label for="pago">¿Se realizó el pago?</label>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label for="monto">Monto: </label>
                            <input type="text" name="monto" id="monto" class="form-control decimal" value=" {{ $Model != null ? $Model->monto : "" }}" {{ $Model != null && $Model->pago ? "" : "disabled" }}>
                            <span data-valmsg-for="monto"></span>
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

<script type="text/javascript" src="{{ asset('auth/adminlte3/plugins/select2/js/select2.js') }}"></script>
<script type="text/javascript">
    $(function(){
        const $modal = $("#modal{{$ViewName}}JugadorZona"); const $zona_id = $("#zona_id");
        $("input.decimal").inputmask("decimal", { min: 0, max: 9999.99, rightAlign: true, groupSeparator: ".", removeMaskOnSubmit: false, digits: 2, autoGroup: true});
        $zona_id.select2({ allowClear : true, placeholder: 'Buscar...' });

        const $pago = $("#pago"); const $monto = $("#monto");
        $pago.on("change", function (){
            const $this = $(this);
            if($this.is(":checked")){
                $monto.prop('disabled', false).prop('required', true);
            }else{
                $monto.val("").prop("disabled", true).prop('required', false);
            }
        });

        OnSuccess{{$ViewName}} = (data) => onSuccessForm(data, $("form#frm{{$ViewName}}"), $modal);
        OnFailure{{$ViewName}} = () => onFailureForm();
    })
</script>

