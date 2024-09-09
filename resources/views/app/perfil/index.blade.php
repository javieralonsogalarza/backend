@inject('Auth', '\Illuminate\Support\Facades\Auth')

@extends('app.layout.app.app')

@section('main')
    <div class="box">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="fas fa-edit fa-1x"></i> Modificar mi Información </h3>
                </div>
            </div>
            <form action="{{ route('app.perfil.store') }}" id="frmPerfil" enctype="multipart/form-data" method="POST"
                  data-ajax="true" class="form" data-ajax-loading="#loading" data-ajax-success="OnSuccessPerfil"
                  data-ajax-failure="OnFailurePerfil">
                <div class="card-body">
                    @csrf
                    <div class="form-group row">
                        <div class="col-md-3">
                            <label for="imagen">Foto<span class="text-danger text-small">(180px × 160px)</span></label>
                            <div class="image_preview_content" style="height: 230px">
                                <div class="image_preview">
                                    <img src="{{ ($Auth::guard('players')->user()->imagen_path != null && $Auth::guard('players')->user()->imagen_path != "") ? ('/img/'.$Auth::guard('players')->user()->imagen_path) : "/upload/image/default.png" }}" alt="Logo">
                                </div>
                            </div>
                            <input type="file" class="preview form-control" name="imagen" id="imagen" accept="image/jpeg, image/png">
                            <span data-valmsg-for="imagen_path"></span>
                        </div>
                        <div class="col-md-9">
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label for="nombres">Nombres: <span class="text-danger">(*)</span></label>
                                    <input type="text" name="nombres" id="nombres" class="form-control" value="{{ $Auth::guard('players')->user()->nombres }}" required autocomplete="off" >
                                    <span data-valmsg-for="nombres"></span>
                                </div>
                                <div class="col-md-6">
                                    <label for="apellidos">Apellidos: <span class="text-danger">(*)</span></label>
                                    <input type="text" name="apellidos" id="apellidos" class="form-control" value="{{ $Auth::guard('players')->user()->apellidos }}" required autocomplete="off" >
                                    <span data-valmsg-for="apellidos"></span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label for="categoria_id">Categoría Base: </label>
                                    <select name="categoria_id" id="categoria_id" class="form-control">
                                        <option value="">Ninguno</option>
                                        @foreach($Categorias as $q)
                                            <option value="{{ $q->id }}" {{ $Auth::guard('players')->user()->categoria_id == $q->id ? "selected" : "" }}>{{ $q->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <span data-valmsg-for="categoria_id"></span>
                                </div>
                                <div class="col-md-6">
                                    <label for="email">Email: <span class="text-danger">(*)</span></label>
                                    <input type="email" name="email" id="email"  class="form-control" value="{{ $Auth::guard('players')->user()->email }}" disabled>
                                    <span data-valmsg-for="email"></span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label for="tipo_documento_id">Tipo documento: <span class="text-danger">(*)</span></label>
                                    <select name="tipo_documento_id" id="tipo_documento_id" class="form-control" required>
                                        <option value="">Ninguno</option>
                                        @foreach($TipoDocumentos as $q)
                                            <option value="{{ $q->id }}" {{ $Auth::guard('players')->user()->tipo_documento_id == $q->id ? "selected" : "" }}>{{ $q->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <span data-valmsg-for="tipo_documento_id"></span>
                                </div>
                                <div class="col-md-6">
                                    <label for="nro_documento">Nº documento: <span class="text-danger">(*)</span></label>
                                    <input type="text" name="nro_documento" id="nro_documento" class="form-control" value="{{ $Auth::guard('players')->user()->nro_documento }}" autocomplete="off" required>
                                    <span data-valmsg-for="nro_documento"></span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label for="password">Contraseña: <span class="text-danger">(*)</span></label>
                                    <input type="text" name="password" id="password" class="form-control" value="************" autocomplete="off" required>
                                    <span data-valmsg-for="password"></span>
                                </div>
                                <div class="col-md-6">
                                    <label for="password_confirmation">Repita Contraseña: <span class="text-danger">(*)</span></label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" value="************" autocomplete="off" required>
                                    <span data-valmsg-for="password_confirmation"></span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-4">
                                    <label for="sexo">Sexo: <span class="text-danger">(*)</span></label>
                                    <select name="sexo" id="sexo" class="form-control" required>
                                        <option value="" {{ $Auth::guard('players')->user()->sexo == null ? "selected" : "" }}>Ninguno</option>
                                        <option value="M" {{ $Auth::guard('players')->user()->sexo == "M" ? "selected" : "" }}>Masculino</option>
                                        <option value="F" {{ $Auth::guard('players')->user()->sexo == "F" ? "selected" : "" }}>Femenino</option>
                                    </select>
                                    <span data-valmsg-for="sexo"></span>
                                </div>
                                <div class="col-md-4">
                                    <label for="celular">Celular: </label>
                                    <input type="text" name="celular" id="celular" maxlength="15" class="form-control" value="{{ $Auth::guard('players')->user()->celular }}" autocomplete="off" >
                                    <span data-valmsg-for="celular"></span>
                                </div>
                                <div class="col-md-4">
                                    <label for="edad">Edad: </label>
                                    <input type="text" name="edad" id="edad" class="form-control numeric" value="{{ $Auth::guard('players')->user()->edad }}" autocomplete="off" >
                                    <span data-valmsg-for="edad"></span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-4">
                                    <label for="altura">Altura (m): </label>
                                    <input type="text" name="altura" id="altura" class="form-control decimal-height" value="{{ $Auth::guard('players')->user()->altura }}" autocomplete="off" >
                                    <span data-valmsg-for="altura"></span>
                                </div>
                                <div class="col-md-4">
                                    <label for="peso">Peso (kg): </label>
                                    <input type="text" name="peso" id="peso" class="form-control decimal-weight" value="{{ $Auth::guard('players')->user()->peso }}" autocomplete="off" >
                                    <span data-valmsg-for="peso"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <ul class="list-unstyled d-flex align-items-center justify-content-end">
                        <li class="mr-2"><a href="{{ route('index') }}" class="btn btn-default"><i class="fa fa-home"></i> Regresar al Inicio</a></li>
                        <li><button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Modificar</button></li>
                    </ul>

                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')

    <script type="text/javascript">
        $(function() {
            $("input.decimal-height").inputmask("decimal", {
                min: 0,
                rightAlign: true,
                removeMaskOnSubmit: false,
                mask: "9[.99]",
                digits: 2
            });
            $("input.decimal-weight").inputmask("decimal", {
                min: 0,
                max: 999.99,
                rightAlign: true,
                groupSeparator: ".",
                removeMaskOnSubmit: false,
                digits: 2,
                autoGroup: true
            });
            $("input.numeric").inputmask("numeric", {
                min: 1,
                max: 99,
                digits: 0,
                removeMaskOnSubmit: false,
                groupSeparator: ",",
                groupSize: 3
            });
            const $input_image = $('input#imagen');
            $input_image.change(function(){readImage(this, $(".image_preview > img"));});
            setTimeout(function (){ $("form#frmPerfil").find("form").find("input[type=text]").first().focus().select(); }, 500);
            OnSuccessPerfil = (data) => onSuccessForm(data, $("form#frmPerfil"), null, null, true);
            OnFailurePerfil = () => onFailureForm();
        });
    </script>

@endsection
