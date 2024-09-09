@extends('app.layout.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/login.min.css') }}">
@endsection

@section('content')
    <div class="container p-lg-v7-h3">
        <div class="row justify-content-center">
            <div class="col-md-12 col-lg-10">
                <div class="wrap d-md-flex">
                    <div class="text-wrap p-4 p-lg-5 text-center d-flex align-items-center order-md-last"></div>
                    <div class="login-wrap p-4 p-lg-5">
                        <div class="d-flex">
                            <div class="w-100">
                                <h3 class="mb-4">Iniciar Sesión</h3>
                            </div>
                        </div>
                        <form action="{{ route('app.login.post') }}" method="post" class="signin-form">
                            @csrf
                            <div class="form-group mb-3">
                                <label class="label" for="email">E-mail</label>
                                <input type="email" id="email" name="email" class="form-control" {{ $errors->has('email') ? ' is-invalid' : '' }} required>
                                @if ($errors->has('email'))
                                    <span class="invalid-feedback-error" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="form-group mb-3">
                                <label class="label" for="password">Contraseña</label>
                                <input type="password" id="password" name="password" class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}" required>
                                @if ($errors->has('password'))
                                    <span class="invalid-feedback-error" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="form-group">
                                <button type="submit" class="form-control btn btn-primary submit px-3">Iniciar Sesión</button>
                            </div>
                            <div class="form-group d-md-flex">
                                <div class="w-50 text-left">
                                    <label class="checkbox-wrap checkbox-primary mb-0" {{ old('remember') ? 'checked' : '' }}> Recuerdame
                                        <input type="checkbox" checked>
                                        <span class="checkmark"></span>
                                    </label>
                                </div>
                                <div class="w-50 text-md-right">
                                    <a href="https://api.whatsapp.com/send?phone=51{{ $Model->telefono }}&text=¡Hola,%20quisiera%20ayuda%20en%20la%20confraternidad%20del%20tenis!" target="_blank">
                                        <i class="fa fa-question-circle"></i> Ayuda
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
