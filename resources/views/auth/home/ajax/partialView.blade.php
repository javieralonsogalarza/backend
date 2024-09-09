<div class="modal fade" id="modal{{$ViewName}}"  role="dialog" tabindex="-1" data-backdrop="static" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ ($Model != null ? "Modificar" : "Registrar")." Comunidad" }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('auth.'.strtolower($ViewName).'.store') }}" id="frm{{$ViewName}}" enctype="multipart/form-data" method="POST"
                  data-ajax="true" class="form" data-ajax-loading="#loading" data-ajax-success="OnSuccess{{$ViewName}}"
                  data-ajax-failure="OnFailure{{$ViewName}}" novalidate>
                @csrf
                <input type="hidden" id="id" name="id" value="{{ $Model != null ? $Model->id : 0 }}">
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="custom-tabs-one-tab" data-toggle="pill" href="#custom-tabs-one" role="tab" aria-controls="custom-tabs-one" aria-selected="true">
                                Formulario Comunidad
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="custom-tabs-two-tab" data-toggle="pill" href="#custom-tabs-two" role="tab" aria-controls="custom-tabs-two" aria-selected="true">
                                Formulario Administrador
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content" id="custom-tabs-one-tabContent">
                        <div class="tab-pane p-4 fade show active" id="custom-tabs-one" role="tabpanel" aria-labelledby="custom-tabs-one-tab">
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
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <label for="email">E-mail: </label>
                                            <input type="email" name="email" id="email" class="form-control" value="{{ $Model != null ? $Model->email : "" }}" autocomplete="off" required>
                                            <span data-valmsg-for="email"></span>
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
                                <div class="col-sm-4">
                                    <label for="telefono">Teléfono: </label>
                                    <input type="text" name="telefono" id="telefono" maxlength="15" class="form-control" value="{{ $Model != null ? $Model->telefono : "" }}" autocomplete="off" >
                                    <span data-valmsg-for="telefono"></span>
                                </div>
                                <div class="col-sm-4">
                                    <label for="titulo_fuente">Fuente para Titulos: <span class="text-danger">(*)</span></label>
                                    <select name="titulo_fuente" id="titulo_fuente" class="form-control" required>
                                        <option value="Anton" {{ $Model != null && $Model->titulo_fuente == "Anton" ? "Selected" : "" }}>Anton</option>
                                        <option value="LeagueSpartan" {{ $Model != null && $Model->titulo_fuente == "LeagueSpartan" ? "Selected" : "" }}>LeagueSpartan</option>
                                    </select>
                                    <span data-valmsg-for="titulo_fuente"></span>
                                </div>
                                <div class="col-sm-4">
                                    <label for="parrafo_fuente">Fuente para Parrafos: <span class="text-danger">(*)</span></label>
                                    <select name="parrafo_fuente" id="parrafo_fuente" class="form-control" required>
                                        <option value="Anton" {{ $Model != null && $Model->parrafo_fuente == "Anton" ? "Selected" : "" }}>Anton</option>
                                        <option value="LeagueSpartan" {{ $Model != null && $Model->parrafo_fuente == "LeagueSpartan" ? "Selected" : "" }}>LeagueSpartan</option>
                                    </select>
                                    <span data-valmsg-for="parrafo_fuente"></span>
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
                        <div class="tab-pane p-4 fade" id="custom-tabs-two" role="tabpanel" aria-labelledby="custom-tabs-two-tab">
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <label for="nombres">Nombre Completo: <span class="text-danger">(*)</span></label>
                                    <input type="email" name="nombres" id="nombres" class="form-control" value="{{ $Model != null && $Model->users->where('principal', true)->first() != null ? $Model->users->where('principal', true)->first()->nombre : "" }}" autocomplete="off" required>
                                    <span data-valmsg-for="nombres"></span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <label for="emails">E-mail: <span class="text-danger">(*)</span></label>
                                    <input type="email" name="emails" id="emails" class="form-control" value="{{ $Model != null && $Model->users->where('principal', true)->first() != null ? $Model->users->where('principal', true)->first()->email : "" }}" autocomplete="off" required>
                                    <span data-valmsg-for="emails"></span>
                                </div>
                                <div class="col-sm-6">
                                    <label for="telefonos">Teléfono: </label>
                                    <input type="text" name="telefonos" id="telefonos" maxlength="15" class="form-control" value="{{ $Model != null && $Model->users->where('principal', true)->first() != null ? $Model->users->where('principal', true)->first()->telefono : "" }}" autocomplete="off" >
                                    <span data-valmsg-for="telefonos"></span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <label for="password">Contraseña: <span class="text-danger">(*)</span></label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="password" class="form-control" value="{{ $Model != null && $Model->users->where('principal', true)->first() != null ? "************" : "" }}" autocomplete="off" required>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-show-password btn-default" title="Ver Contraseña"><i class="fas fa-eye fa-fw"></i></button>
                                            <button type="button" class="btn btn-generate-key btn-primary " title="Generar Contraseña"><i class="fas fa-key fa-fw"></i></button>
                                        </div>
                                    </div>
                                    <span data-valmsg-for="password"></span>
                                </div>
                                <div class="col-sm-6">
                                    <label for="password_confirmation">Repita Contraseña: <span class="text-danger">(*)</span></label>
                                    <div class="input-group">
                                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" value="{{ $Model != null && $Model->users->where('principal', true)->first() != null ? "************" : "" }}" autocomplete="off" required>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-show-password btn-default" title="Ver Contraseña"><i class="fas fa-eye fa-fw"></i></button>
                                        </div>
                                    </div>
                                    <span data-valmsg-for="password_confirmation"></span>
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

        const $slug = $("#slug");
        $modal.on("change", "#nombre", function (){
            const $this = $(this);
            const formData = new FormData();
            formData.append('_token', $("meta[name=csrf-token]").attr("content"));
            formData.append("nombre", $this.val());
            actionAjax(`/auth/{{strtolower($ViewName)}}/get-slug`, formData, "POST", function(data){$slug.val(data);});
        });

        const $password = $("#password"), $password_confirmartion = $("#password_confirmation");
        $modal.on("click", "button.btn-generate-key", function (){
            const formData = new FormData();
            formData.append('_token', $("meta[name=csrf-token]").attr("content"));
            actionAjax(`/auth/{{strtolower($ViewName)}}/get-password`, formData, "POST", function(data) {$password.val(data); $password_confirmartion.val(data);});
        });

        $modal.on("click", "button.btn-show-password", function (){
            const $this = $(this);
            $this.find("i").toggleClass("fa-eye fa-eye-slash");
            const type = $this.find("i").hasClass("fa-eye") ? "password" : "text";
            $this.closest(".input-group").find("input").attr("type", type);
        });

        setTimeout(function (){ $modal.find("form").find("input[type=text]").first().focus().select(); }, 500);
        OnSuccess{{$ViewName}} = (data) => onSuccessForm(data, $("form#frm{{$ViewName}}"), $modal);
        OnFailure{{$ViewName}} = () => onFailureForm();
    })
</script>

