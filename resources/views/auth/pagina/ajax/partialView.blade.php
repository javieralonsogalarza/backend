<div class="modal fade" id="modal{{$ViewName}}"  role="dialog" tabindex="-1" data-backdrop="static" aria-labelledby="myModalLabel">
    <div class="modal-dialog {{ ($Model != null && $Model->imagen_path != null && $Model->imagen_path != "") ? 'modal-xl' : 'modal-md' }} modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ ($Model != null ? "Modificar" : "Registrar")." Contenido" }}</h5>
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
                    <div class="row">
                        @if($Model != null && $Model->imagen_path != null && $Model->imagen_path != "")
                        <div class="col-md-6">
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <label for="imagen">Imagen<span class="text-danger text-small">(1170px × 515px)</span></label>
                                    <div class="image_preview_content" style="height: 280px">
                                        <div class="image_preview">
                                            <img src="{{ asset('/img/'.$Model->imagen_path) }}" alt="Logo">
                                        </div>
                                    </div>
                                    <input type="file" class="preview form-control" name="imagen" id="imagen" accept="image/jpeg, image/png">
                                    <span data-valmsg-for="imagen_path"></span>
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="{{ ($Model != null && $Model->imagen_path != null && $Model->imagen_path != "") ? 'col-sm-6' : 'col-sm-12' }}">
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <label for="titulo">Título: <span class="text-danger">(*)</span></label>
                                    <input type="text" name="titulo" id="titulo" class="form-control" value="{{ $Model != null ? $Model->titulo : "" }}" required autocomplete="off" >
                                    <span data-valmsg-for="titulo"></span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <label for="descripcion">Descripción: <span class="text-danger">(*)</span></label>
                                    <textarea name="descripcion" id="descripcion" class="form-control" rows="{{ ($Model != null && $Model->imagen_path != null && $Model->imagen_path != "") ? '9' : '3' }}">{{ $Model != null ? $Model->descripcion : "" }}</textarea>
                                    <span data-valmsg-for="descripcion"></span>
                                </div>
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
        const $input_image = $('input#imagen');
        $input_image.change(function(){readImage(this, $(".image_preview > img"));});
        setTimeout(function (){ $modal.find("form").find("input[type=text]").first().focus().select(); }, 500);
        OnSuccess{{$ViewName}} = (data) => onSuccessForm(data, $("form#frm{{$ViewName}}"), $modal);
        OnFailure{{$ViewName}} = () => onFailureForm();
    })
</script>

