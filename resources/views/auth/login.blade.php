<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ str_replace("_", " ", env('APP_NAME')) }} | Log in</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('auth/adminlte3/dist/css/adminlte.css') }}">
    <link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/fontawesome-free/css/all.css') }}">
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="login-logo mb-4">
        <a href="{{ route('login') }}">
            Administ<b>rador</b>
        </a>
    </div>
    <!-- /.login-logo -->
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">Inicia sesión para administrar</p>
            <form action="{{ route('login.post') }}" method="post">
                @csrf
                <div class="input-group mb-3">
                    <input type="email" class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}"
                           name="email" placeholder="E-mail" value="{{ old('email') }}" required autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                    @if ($errors->has('email'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}" placeholder="Contraseña" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                    @if ($errors->has('password'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="row">
                    <div class="col-7">
                        <div class="icheck-primary">
                            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label for="remember">
                                Recordarme
                            </label>
                        </div>
                    </div>
                    <!-- /.col -->
                    <div class="col-5">
                        <button type="submit" class="btn btn-primary btn-block">Iniciar sesión</button>
                    </div>
                    <!-- /.col -->
                </div>
                <div class="row mt-3">
                    <div class="col">
                        <div class="col-12 text-center">
                            <a href="{{ route('index') }}" class="btn btn-sm btn-default"><i class="fa fa-home"></i> Regresar al Inicio</a>
                        </div>
                    </div>
                </div>
            </form>
            <!-- /.social-auth-links -->
        </div>
        <!-- /.login-card-body -->
    </div>
</div>
<!-- /.login-box -->

<script src="{{ asset('/auth/adminlte3/plugins/jquery/jquery.js') }}"></script>
<script src="{{ asset('/auth/adminlte3/plugins/bootstrap/js/bootstrap.js') }}"></script>
</body>
</html>
