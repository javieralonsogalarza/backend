<link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/select2/css/select2.min.css') }}">
<div class="modal fade" id="modal{{$ViewName}}"  role="dialog" data-backdrop="static" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modificar Orden de Categorías</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="frm{{$ViewName}}" enctype="multipart/form-data" method="POST" class="form">
                @csrf
                <input type="hidden" id="id" name="id" value="{{ $TorneoId }}">
                <div class="modal-body">
                    <div class="form-group row">
                         <div class="col-md-12">
                             <ol class="serialization vertical">
                                 @foreach($TorneoCategorias as $q)
                                     <li data-id="{{ $q->id }}" data-name="{{ $q->multiple && ($q->categoriaSimple->id !== $q->categoriaDupla->id) ? ($q->categoriaSimple->nombre." + ".$q->categoriaDupla->nombre) : ($q->categoriaSimple->nombre)."".($q->multiple ? " (Doble) " : "") }}">
                                         {{ $q->multiple && ($q->categoriaSimple->id !== $q->categoriaDupla->id) ? ($q->categoriaSimple->nombre." + ".$q->categoriaDupla->nombre) : ($q->categoriaSimple->nombre)."".($q->multiple ? " (Doble) " : "") }}
                                     </li>
                                 @endforeach
                             </ol>
                         </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary pull-right">Modificar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="{{ asset('plugins/sortable/jquery-sortable.js') }}"></script>
<script type="text/javascript">
    $(function(){

        const $form = $("form#frm{{$ViewName}}");
        const $modal = $("#modal{{$ViewName}}");

        var jsonString = null;
        var group = $modal.find("ol.serialization").sortable({
            group: 'serialization',
            isValidTarget: function ($item, container) {
                if ($item.parent("ol")[0] === container.el[0]) return true;
                return false;
            },
            onDrop: function ($item, container, _super) {
                var data = group.sortable("serialize").get();
                jsonString = JSON.stringify(data[0]);
                _super($item, container);
            }
        });

        $form.on("submit", function (e) {
            e.preventDefault();
            const formData = new FormData($(this)[0]);
            formData.append("jsonString", jsonString);
            confirmAjax(`/auth/torneo/categoria/cambiarOrdenStore`, formData, 'POST', '¿Está seguro de establecer el orden de las categorías?', null, function (data){
                if(data.Success){
                    $modal.attr("data-reload", "true");
                    $modal.modal("hide");
                }
            });
        });
    })
</script>

