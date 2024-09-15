<div class="modal fade" id="modal{{$ViewName}}"  role="dialog" tabindex="-1" data-backdrop="static" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
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
                        <div class="col-md-4">
                            <label for="imagen">Foto<span class="text-danger text-small">(180px × 160px)</span></label>
                            <div class="image_preview_content" style="height: 230px">
                                <div class="image_preview">
                                    <img src="{{ ($Model != null && $Model->imagen_path != null && $Model->imagen_path != "") ? ('/img/'.$Model->imagen_path) : "/upload/image/default.png" }}" alt="Logo">
                                </div>
                            </div>
                            <input type="file" class="preview form-control" name="imagen" id="imagen" accept="image/jpeg, image/png">
                            <span data-valmsg-for="imagen_path"></span>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <label for="nombres">Nombres: <span class="text-danger">(*)</span></label>
                                    <input type="text" name="nombres" id="nombres" class="form-control" value="{{ $Model != null ? $Model->nombres : "" }}" required autocomplete="off" >
                                    <span data-valmsg-for="nombres"></span>
                                </div>
                                <div class="col-sm-6">
                                    <label for="apellidos">Apellidos: <span class="text-danger">(*)</span></label>
                                    <input type="text" name="apellidos" id="apellidos" class="form-control" value="{{ $Model != null ? $Model->apellidos : "" }}" required autocomplete="off" >
                                    <span data-valmsg-for="apellidos"></span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <label for="categoria_id">Categoría Base: </label>
                                    <div class="input-group">
                                        <select name="categoria_id" id="categoria_id" class="form-control">
                                            <option value="">Ninguno</option>
                                            @foreach($Categorias as $q)
                                                <option value="{{ $q->id }}" {{ $Model != null && $Model->categoria_id == $q->id ? "selected" : "" }}>{{ $q->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <span class="input-group-append">
                                    <button type="button" id="btnRegistrarCategoria" class="btn btn-primary"><i class="fa fa-plus"></i></button>
                                </span>
                                    </div>
                                    <span data-valmsg-for="categoria_id"></span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label for="tipo_documento_id">Tipo documento: <span class="text-danger">(*)</span></label>
                                    <select name="tipo_documento_id" id="tipo_documento_id" class="form-control" required>
                                        <option value="">Ninguno</option>
                                        @foreach($TipoDocumentos as $q)
                                            <option value="{{ $q->id }}" {{ $Model != null && $Model->tipo_documento_id == $q->id ? "selected" : "" }}>{{ $q->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <span data-valmsg-for="tipo_documento_id"></span>
                                </div>
                                <div class="col-sm-6">
                                    <label for="nro_documento">Nº documento: <span class="text-danger">(*)</span></label>
                                    <input type="text" name="nro_documento" id="nro_documento" class="form-control" value="{{ $Model != null ? $Model->nro_documento : "" }}" autocomplete="off" required>
                                    <span data-valmsg-for="nro_documento"></span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <label for="email">E-mail: <span class="text-danger">(*)</span></label>
                                    <div class="input-group">
                                        <input type="email" name="email" id="email" class="form-control" value="{{ $Model != null ? $Model->email : "" }}" autocomplete="off">
                                        @if($Model != null)
                                            <span class="input-group-append">
                                                <button type="button" id="btnCrearCuenta" class="btn btn-success" title="{{ $Model->isAccount ? '¿Actualizar Cuenta?' : '¿Crear Cuenta?'  }}">
                                                    @if($Model->isAccount)
                                                        <i class="fa fa-user-edit"></i>
                                                    @else
                                                        <i class="fa fa-user-plus"></i>
                                                    @endif
                                                </button>
                                                @if($Model->isAccount)
                                                    <button type="button" id="btnEliminarCuenta" class="btn btn-danger" title="Eliminar Cuenta"><i class="fa fa-ban"></i></button>
                                                @endif
                                            </span>
                                        @endif
                                    </div>
                                    <span data-valmsg-for="email"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label for="sexo">Sexo: <span class="text-danger">(*)</span></label>
                            <select name="sexo" id="sexo" class="form-control" required>
                                <option value="" {{ $Model != null && $Model->sexo == null ? "selected" : "" }}>Ninguno</option>
                                <option value="M" {{ $Model != null && $Model->sexo == "M" ? "selected" : "" }}>Masculino</option>
                                <option value="F" {{ $Model != null && $Model->sexo == "F" ? "selected" : "" }}>Femenino</option>
                            </select>
                            <span data-valmsg-for="sexo"></span>
                        </div>
                        <!--<div class="col-sm-4">
                            <label for="telefono">Teléfono: </label>
                            <input type="text" name="telefono" id="telefono" maxlength="15" class="form-control" value="{{ $Model != null ? $Model->telefono : "" }}" autocomplete="off" >
                            <span data-valmsg-for="telefono"></span>
                        </div>-->
                        <div class="col-sm-4">
                            <label for="celular">Celular: </label>
                            <input type="text" name="celular" id="celular" maxlength="15" class="form-control" value="{{ $Model != null ? $Model->celular : "" }}" autocomplete="off" >
                            <span data-valmsg-for="celular"></span>
                        </div>
                        <div class="col-sm-4">
                            <label for="edad">Edad: </label>
                            <input type="text" name="edad" id="edad" class="form-control numeric" value="{{ $Model != null ? $Model->edad : "" }}" autocomplete="off" >
                            <span data-valmsg-for="edad"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-4">
                            <label for="altura">Altura (m): </label>
                            <input type="text" name="altura" id="altura" class="form-control decimal-height" value="{{ $Model != null ? $Model->altura : "" }}" autocomplete="off" >
                            <span data-valmsg-for="altura"></span>
                        </div>
                        <div class="col-sm-4">
                            <label for="peso">Peso (kg): </label>
                            <input type="text" name="peso" id="peso" class="form-control decimal-weight" value="{{ $Model != null ? $Model->peso : "" }}" autocomplete="off" >
                            <span data-valmsg-for="peso"></span>
                        </div>
                        @if($Model == null)
                            <div class="col-sm-4 d-flex align-items-end justify-content-center">
                                <div class="icheck-primary d-inline">
                                    <input type="checkbox" id="isAccount" name="isAccount" value="1" checked>
                                    <label for="isAccount">¿Crear Cuenta?</label>
                                </div>
                            </div>
                        @endif
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
        $("input.decimal-height").inputmask("decimal", { min: 0, rightAlign: true, removeMaskOnSubmit: false, mask: "9[.99]", digits: 2});
        $("input.decimal-weight").inputmask("decimal", { min: 0, max: 999.99, rightAlign: true, groupSeparator: ".", removeMaskOnSubmit: false, digits: 2, autoGroup: true});
        $("input.numeric").inputmask("numeric", { min: 1, max: 99, digits: 0, removeMaskOnSubmit: false, groupSeparator: ",", groupSize: 3 });
        const $modal = $("#modal{{$ViewName}}");
        const $input_image = $('input#imagen');
        $input_image.change(function(){readImage(this, $(".image_preview > img"));});
        setTimeout(function (){ $modal.find("form").find("input[type=text]").first().focus().select(); }, 500);
        const $btnRegistrarCategoria = $("#btnRegistrarCategoria");
        const $categoria_id = $("#categoria_id");
        $btnRegistrarCategoria.on("click", function (){
            invocarModal(`/auth/categoria/partialView/0`, function ($modal) {
                if ($modal.attr("data-reload") === "true"){
                    cascadingDropDownLoad($categoria_id,`/auth/categoria/list-json/`, null, true, function (){
                        setTimeout(function (){ $modal.find("form").find("input[type=text]").first().focus().select(); }, 500);
                    });
                }
            });
        });

        @if($Model != null)
            const $form = $("#frm{{$ViewName}}");
            const $btnCrearCuenta = $("#btnCrearCuenta");
            $btnCrearCuenta.on("click", function (){
                const formData = new FormData();
                formData.append('_token', $("meta[name=csrf-token]").attr("content"));
                formData.append('id', {{ $Model->id }});
                formData.append('email', $("#email").val());
                confirmAjax(`/auth/jugador/account`, formData, "POST", `¿Está seguro de crear una cuenta para el jugar {{ $Model->nombre_completo }}?`, null, function () {
                    $modal.attr("data-reload", "true");
                    $modal.modal("hide");
                }, function (data){
                    Toast.fire({icon: 'error', title: data.Message});
                    if (data.Errors) {
                        $.each(data.Errors,
                            function (i, item) {
                                if($form != null) {
                                    if ($form.find("span[data-valmsg-for=" + i + "]").length !== 0)
                                        $form.find("span[data-valmsg-for=" + i + "]").text(item[0]);
                                }
                            });
                    }
                });
            });

            @if($Model->isAccount)
                const $btnEliminarCuenta = $("#btnEliminarCuenta");
                $btnEliminarCuenta.on("click", function (){
                    const formData = new FormData();
                    formData.append('_token', $("meta[name=csrf-token]").attr("content"));
                    formData.append('id', {{ $Model->id }});
                    formData.append('email', $("#email").val());
                    confirmAjax(`/auth/jugador/account/delete`, formData, "POST", `¿Está seguro de eliminar la cuenta para el jugar {{ $Model->nombre_completo }}?`, null, function () {
                        $modal.attr("data-reload", "true");
                        $modal.modal("hide");
                    }, function (data){
                        Toast.fire({icon: 'error', title: data.Message});
                        if (data.Errors) {
                            $.each(data.Errors,
                                function (i, item) {
                                    if($form != null) {
                                        if ($form.find("span[data-valmsg-for=" + i + "]").length !== 0)
                                            $form.find("span[data-valmsg-for=" + i + "]").text(item[0]);
                                    }
                                });
                        }
                    });
                });
           @endif
        @endif

        OnSuccess{{$ViewName}} = (data) => onSuccessForm(data, $("form#frm{{$ViewName}}"), $modal);
        OnFailure{{$ViewName}} = () => onFailureForm();
    })
</script>

