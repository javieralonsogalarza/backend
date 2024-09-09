<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title"><i class="fas fa-edit fa-1x"></i> Actualizar Personalización </h3>
        </div>
    </div>
    <form action="{{ route('auth.'.strtolower($ViewName).'.store') }}" id="frm{{$ViewName}}" enctype="multipart/form-data" method="POST"
          data-ajax="true" class="form" data-ajax-loading="#loading" data-ajax-success="OnSuccess{{$ViewName}}"
          data-ajax-failure="OnFailure{{$ViewName}}">
        <div class="card-body">
            @csrf
            <input type="hidden" id="id" name="id" value="{{ $Model != null ? $Model->id : 0 }}">
            <div class="modal-body">
                <div class="form-group row row-center-vertical">
                    <div class="col-md-6">
                        <label for="imagen">Logo<span class="text-danger text-small">(185px × 50px)</span></label>
                        <div class="image_preview_content">
                            <div class="image_preview">
                                <img src="{{ ($Model != null && $Model->imagen_path != null && $Model->imagen_path != "") ? ('/img/'.$Model->imagen_path) : "/upload/image/default.png" }}" alt="Logo">
                            </div>
                        </div>
                        <input type="file" class="preview form-control" name="imagen" id="imagen" accept="image/jpeg, image/png">
                        <span data-valmsg-for="imagen_path"></span>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <label for="nombre">Nombre: <span class="text-danger">(*)</span></label>
                                <input type="text" name="nombre" id="nombre" class="form-control" value="{{ $Model != null ? $Model->nombre : "" }}" required autocomplete="off" >
                                <span data-valmsg-for="nombre"></span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <label for="slug">URL: </label>
                                <input type="text" name="slug" id="slug" class="form-control" value="{{ $Model != null ? $Model->url : "" }}" disabled autocomplete="off" >
                                <span data-valmsg-for="slug"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-3">
                        <label for="color_navegacion">Panel Navegación: <span class="text-danger">(*)</span></label>
                        <input type="color" name="color_navegacion" id="color_navegacion" class="form-control" value="{{ $Model != null ? $Model->color_navegacion : "" }}" required autocomplete="off" >
                        <span data-valmsg-for="color_navegacion"></span>
                    </div>
                    <div class="col-md-3">
                        <label for="color_primario">Color Cabecera: <span class="text-danger">(*)</span></label>
                        <input type="color" name="color_primario" id="color_primario" class="form-control" value="{{ $Model != null ? $Model->color_primario : "" }}" required autocomplete="off" >
                        <span data-valmsg-for="color_primario"></span>
                    </div>
                    <div class="col-md-3">
                        <label for="color_secundario">Color Botones: <span class="text-danger">(*)</span></label>
                        <input type="color" name="color_secundario" id="color_secundario" class="form-control" value="{{ $Model != null ? $Model->color_secundario : "" }}" required autocomplete="off" >
                        <span data-valmsg-for="color_secundario"></span>
                    </div>
                    <div class="col-md-3">
                        <label for="color_alternativo">Color Pestañas: <span class="text-danger">(*)</span></label>
                        <input type="color" name="color_alternativo" id="color_alternativo" class="form-control" value="{{ $Model != null ? $Model->color_alternativo : "" }}" required autocomplete="off" >
                        <span data-valmsg-for="color_alternativo"></span>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-sm-6">
                        <label for="titulo_fuente">Fuente para Titulos: <span class="text-danger">(*)</span></label>
                        <select name="titulo_fuente" id="titulo_fuente" class="form-control">
                            <option value="Anton" {{ $Model != null && $Model->titulo_fuente == "Anton" ? "Selected" : "" }}>Anton</option>
                            <option value="LeagueSpartan" {{ $Model != null && $Model->titulo_fuente == "LeagueSpartan" ? "Selected" : "" }}>LeagueSpartan</option>
                        </select>
                        <span data-valmsg-for="titulo_fuente"></span>
                    </div>
                    <div class="col-sm-6">
                        <label for="parrafo_fuente">Fuente para Parrafos: <span class="text-danger">(*)</span></label>
                        <select name="parrafo_fuente" id="parrafo_fuente" class="form-control">
                            <option value="Anton" {{ $Model != null && $Model->parrafo_fuente == "Anton" ? "Selected" : "" }}>Anton</option>
                            <option value="LeagueSpartan" {{ $Model != null && $Model->parrafo_fuente == "LeagueSpartan" ? "Selected" : "" }}>LeagueSpartan</option>
                        </select>
                        <span data-valmsg-for="parrafo_fuente"></span>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-sm-6">
                        <label for="email">E-mail: <span class="text-danger">(*)</span></label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ $Model != null ? $Model->email : "" }}" autocomplete="off" required>
                        <span data-valmsg-for="email"></span>
                    </div>
                    <div class="col-sm-6">
                        <label for="telefono">Teléfono: </label>
                        <input type="text" name="telefono" id="telefono" maxlength="15" class="form-control" value="{{ $Model != null ? $Model->telefono : "" }}" autocomplete="off" >
                        <span data-valmsg-for="telefono"></span>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-sm-6">
                        <label for="facebook">Facebook: </label>
                        <input type="text" name="facebook" id="facebook" class="form-control" value="{{ $Model != null ? $Model->facebook : "" }}" autocomplete="off" >
                        <span data-valmsg-for="facebook"></span>
                    </div>
                    <div class="col-sm-6">
                        <label for="instagram">Instagram: </label>
                        <input type="text" name="instagram" id="instagram" class="form-control" value="{{ $Model != null ? $Model->instagram : "" }}" autocomplete="off" >
                        <span data-valmsg-for="instagram"></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            <button type="submit" class="btn btn-primary" id="btnRegistrar{{$ViewName}}">
                Modificar
            </button>
        </div>
    </form>
</div>

<script type="text/javascript">
    $(function (){
        const $input_image = $('input#imagen');
        $input_image.change(function(){readImage(this, $(".image_preview > img"));});
        setTimeout(function (){ $("form").find("input[type=text]").first().focus().select(); }, 500);

        const $slug = $("#slug");
        $("form#frm{{$ViewName}}").on("change", "#nombre", function (){
            const $this = $(this);
            const formData = new FormData();
            formData.append('_token', $("meta[name=csrf-token]").attr("content"));
            formData.append("nombre", $this.val());
            actionAjax(`/auth/{{strtolower($ViewName)}}/get-slug`, formData, "POST", function(data){$slug.val(data);});
        });

        OnSuccess{{$ViewName}} = (data) => onSuccessForm(data, $("form#frm{{$ViewName}}"), null, function (data){
            if(data.Success)
                invocarVista(`/auth/perfil/partialView`, function (data){ $("#contentView").html("").append(data);});
            else
                Toast.fire({icon: 'error', title: data.Message ? data.Message : 'Algo salió mal, hubo un error al guardar.'});
        }, true);
        OnFailure{{$ViewName}} = () => onFailureForm();
    });
</script>
