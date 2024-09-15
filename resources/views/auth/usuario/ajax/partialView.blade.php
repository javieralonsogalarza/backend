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
                        <div class="col-sm-12">
                            <label for="nombre">Nombre Completo: <span class="text-danger">(*)</span></label>
                            <input type="text" name="nombre" id="nombre" class="form-control" value="{{ $Model != null ? $Model->nombre : "" }}" required autocomplete="off" >
                            <span data-valmsg-for="nombre"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <label for="email">E-mail: <span class="text-danger">(*)</span></label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ $Model != null ? $Model->email : "" }}" required autocomplete="off" >
                            <span data-valmsg-for="email"></span>
                        </div>
                        <div class="col-sm-6">
                            <label for="telefono">Teléfono: </label>
                            <input type="text" name="telefono" id="telefono" maxlength="15" class="form-control" value="{{ $Model != null ? $Model->telefono : "" }}" autocomplete="off" >
                            <span data-valmsg-for="telefono"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="password">Contraseña: <span class="text-danger">(*)</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password" id="password" value="{{ $Model != null ? "************" : "" }}" {{ $Model != null ? "" : "required" }} autocomplete="off">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-show-password btn-default" title="Ver Contraseña"><i class="fas fa-eye fa-fw"></i></button>
                                    <button type="button" class="btn btn-generate-key btn-primary " title="Generar Contraseña"><i class="fas fa-key fa-fw"></i></button>
                                </div>
                            </div>
                            <span data-valmsg-for="password"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation">Repita Contraseña: <span class="text-danger">(*)</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" value="{{ $Model != null ? "************" : "" }}" {{ $Model != null ? "" : "required" }} autocomplete="off">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-show-password btn-default" title="Ver Contraseña"><i class="fas fa-eye fa-fw"></i></button>
                                </div>
                            </div>
                            <span data-valmsg-for="password_confirmation"></span>
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

