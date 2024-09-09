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
                                <h3 class="mb-4">Cambiar Contraseña</h3>
                            </div>
                        </div>
                        <form action="{{ route('resetPassword') }}" method="post" class="signin-form">
                            @csrf
                            <div class="form-group mb-3">
                                <label class="label" for="password">Contraseña</label>
                                <input type="password" id="password" name="password" class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}" required>
                                @if ($errors->has('password'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="form-group mb-3">
                                <label class="label" for="password_confirmation">Confirmar Contraseña</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control {{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}" required>
                                @if ($errors->has('password_confirmation'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="form-group">
                                <button type="submit" class="form-control btn btn-primary submit px-3">Continuar</button>
                                <a href="javascript:void(0);" onclick="event.preventDefault();document.getElementById('logout-form').submit();" class="form-control btn btn-cancel btn-default mt-3" style="line-height: 2">
                                    Cerrar Sesión
                                </a>
                            </div>
                        </form>
                        <form id="logout-form" action="{{ route('app.logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $(function (){
           $(document).on("click", ".btn-cancel", function (){

           });
        });
    </script>
@endsection
