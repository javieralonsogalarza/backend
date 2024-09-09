<link rel="stylesheet" href="{{ asset('auth/plugins/file-input/css/fileinput.min.css') }}">
<div class="modal fade" id="modal{{$ViewName}}"  role="dialog" tabindex="-1" data-backdrop="static" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Fondo y Textos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('auth.'.strtolower($ViewName).'.updateBackground') }}" id="frm{{$ViewName}}" enctype="multipart/form-data" method="POST"
                  data-ajax="true" class="form" data-ajax-loading="#loading" data-ajax-success="OnSuccess{{$ViewName}}"
                  data-ajax-failure="OnFailure{{$ViewName}}">
                @csrf
                <input type="hidden" id="id" name="id" value="{{ $Model != null ? $Model->id : 0 }}">
                <input type="hidden" id="remove_file" name="remove_file" value="0">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <label for="color_rotulos" class="form-label col-form-label">Color Rótulos:</label>
                            <input type="color" name="color_rotulos" class="form-control" value="{{ $Model != null ? $Model->color_rotulos : "" }}" id="color_rotulos">
                        </div>
                        <div class="col-sm-6">
                            <label for="color_participantes" class="form-label col-form-label">Color Participantes:</label>
                            <input type="color" name="color_participantes" class="form-control" value="{{ $Model != null ? $Model->color_participantes : "" }}" id="color_participantes">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <label for="imagen" class="form-label col-form-label">Imagen de Fondo: <span class="text-danger">1300px x 770px</span></label>
                                    <div class="file-loading">
                                        <input id="imagen" name="imagen" data-preview="{{ $Model != null ? $Model->imagen : ""}}"  type="file">
                                    </div>
                                    <div id="kartik-file-errors"></div>
                                </div>
                            </div>
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
<script src="{{ asset('auth/plugins/file-input/js/fileinput.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('auth/plugins/file-input/js/locales/es.js') }}" type="text/javascript"></script>
<script type="text/javascript">
    $(function(){
        const $image = $("#imagen");
        const $inputRemoveFile = $("#remove_file");
        $image.fileinput({
            showPreview: false,
            showUpload: false,
            language: "es",
            initialPreviewAsData: true,
            initialPreviewFileType: 'image',
            elErrorContainer: '#kartik-file-errors', allowedFileExtensions: ["jpg", "png", "gif"],
            initialPreview:  $image.attr('data-preview') !== "" ? [$image.attr('data-preview')] : [],
            initialPreviewConfig: [{caption: "Imagen de Fondo"}]
        });
        $("body").on("click", "button.fileinput-remove", function (){
            $inputRemoveFile.val(1);
        });
        OnSuccess{{$ViewName}} = (data) => onSuccessForm(data, $("form#frm{{$ViewName}}"), $("#modal{{$ViewName}}"));
        OnFailure{{$ViewName}} = () => onFailureForm();
    })
</script>

