@inject('Carbon','Carbon\Carbon')
@inject('App', 'App\Models\App')
@inject('Auth', '\Illuminate\Support\Facades\Auth')
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ str_replace("_", " ", env('APP_NAME')) }}</title>
    <!-- OwlCarousel -->
    <link rel="stylesheet" href="{{ asset('plugins/owlcarousel/assets/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/owlcarousel/assets/owl.theme.default.min.css') }}">
    <!-- RevolutionSlider -->
    <link rel="stylesheet" href="{{ asset('plugins/revolution-slider/jquery.themepunch.revolution.css') }}">
    <!-- Layout -->
    <style type="text/css">
        :root{
            --color-navegacion: {{ $Model != null ? $Model->color_navegacion : "#000000" }};
            --color-cabecera: {{ $Model != null ? $Model->color_primario : "#000000" }};
            --color-botones: {{ $Model != null ? $Model->color_secundario : "#000000" }};
            --color-pestana: {{ $Model != null ? $Model->color_alternativo : "#000000" }};

            --color-primary: {{ $Model != null ? $Model->color_primario : "#000000"}};
            --color-secundary: {{ $Model != null ? $Model->color_secundario : "#000000" }};
            --color-alternative: {{ $Model != null ? $Model->color_alternativo : "#000000" }};
            --font-family-title: {{ $Model != null ? $Model->titulo_fuente : "Anton" }};
            --font-family-text: {{ $Model != null ? $Model->parrafo_fuente : "LeagueSpartan" }};
        }
    </style>
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('auth/adminlte3//plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="{{ asset('auth/adminlte3/dist/css/adminlte.css') }}">

    @yield('styles')

    <link rel="stylesheet" href="{{ asset('css/app.min.css') }}">
</head>
<body>

    <div id="fb-root"></div>
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/es_LA/sdk.js#xfbml=1&version=v18.0" nonce="ljIQ6DqK"></script>

    <div id="loading">
        <i class="fas fa-sync-alt fa-spin" aria-hidden="true"></i>
    </div>

    <header>
        <div class="nav-redes">
           <div class="container">
               <div>
                   <ul>
                       <li><a href="javascript:sendMessage()" title="Whatsapp"><i class="fa fa-whatsapp fa-lg"></i></a></li>
                       <li><a href="mailto:{{ $Model->email }}" title="E-mail"><i class="fa fa-envelope fa-md"></i></a></li>
                       <li><a href="{{ $Model->facebook }}" target="_blank" title="Facebook"><i class="fa fa-facebook fa-md"></i></a></li>
                       <li><a href="{{ $Model->instagram }}" target="_blank" title="Instagram"><i class="fa fa-instagram fa-lg"></i></a></li>
                   </ul>
               </div>
               <div>
                   <ul class="account">
                       @if($Auth::guard('web')->check())
                            <li><a href="{{ $Auth::guard('web')->user()->perfil_id == $App::$PERFIL_ADMINISTRADOR ? route('auth.home.index') : route('auth.rankings.index') }}" title="{{ $Auth::guard('web')->user()->nombre }}"><i class="fa fa-user"></i> Bienvenido, {{ $Auth::guard('web')->user()->nombre }}</a></li>
                       @elseif($Auth::guard('players')->check())
                           <li><a href="{{ route('app.perfil.index') }}" title="{{ $Auth::guard('players')->user()->nombre_completo }}"><i class="fa fa-user"></i> Bienvenido, {{ $Auth::guard('players')->user()->nombre_completo }}</a></li>
                       @else
                           <li><a href="{{ route('app.login') }}" title="Iniciar Sesión"><i class="fa fa-user fa-lg"></i> Iniciar Sesión</a></li>
                       @endif
                   </ul>
               </div>
           </div>
        </div>
        <div class="nav-item">
            <div class="container">
                <div>
                    <a href="{{ route('index', ['landing' => $Model->slug]) }}" title="{{ str_replace("_", " ", env('APP_NAME')) }} Logo">
                        <img src="{{ asset('/img/'.$Model->imagen_path) }}" width="250" class="logo" alt="{{ $Model->nombre }} Logo">
                    </a>
                </div>
                <nav class="navigation">
                    <ul>
                        <li><a href="{{ Route::currentRouteName() == "index" ? "" : ("/") }}#inicio" title="Inicio">Inicio</a></li>
                        <li><a href="{{ Route::currentRouteName() == "index" ? "" : ("/") }}#nosotros" title="Nosotros">Nosotros</a></li>
                        <li><a href="{{ Route::currentRouteName() == "index" ? "" : ("/") }}#fotografias" title="Fotografías">Fotografías</a></li>
                        <li class="{{ Route::currentRouteName() == "torneos" ? "active" : "" }}"><a href="{{ route('torneos', ['landing' => $Model->slug]) }}" title="Torneos">Torneos</a></li>
                        <li class="{{ Route::currentRouteName() == "rankings" ? "active" : "" }}"><a href="{{ route('rankings', ['landing' => $Model->slug]) }}" title="Rankings">Rankings</a></li>
                        <li class="{{ Route::currentRouteName() == "jugadores" ? "active" : "" }}"><a href="{{ route('jugadores', ['landing' => $Model->slug]) }}" title="Jugadores">Jugadores</a></li>
                        <li><a href="{{ Route::currentRouteName() == "index" ? "" : ("/") }}#contactanos" title="Contactanos">Contáctanos</a></li>
                    </ul>
                    <button type="button" class="btn-nav"><i class="fa fa-bars fa-2x"></i></button>
                </nav>
            </div>
        </div>
    </header>

    @yield('content')


    <footer>
        <div class="content-footer">
            <div class="container">
                <strong>Copyright &copy; {{ $Carbon::now()->year }} {{ str_replace("_", " ", env('APP_NAME')) }}.</strong> todos los derechos reservados.
            </div>
        </div>

    </footer>

    <!-- JQuery 1.10.1 -->
    <script type="text/javascript" src="{{ asset('plugins/jquery/1.10.1/jquery.js') }}"></script>
    <!-- FontAwesome -->
    <script type="text/javascript" src="{{ asset('https://use.fontawesome.com/02adc562b1.js') }}"></script>

    <script src="https://www.google.com/recaptcha/api.js"></script>

    <script type="text/javascript">
        const $slug = "{{ $Model->slug }}"
        function sendMessage(){
            window.open("https://api.whatsapp.com/send?phone=51{{ $Model->telefono }}&text=¡Hola,%20quisiera%20más%20información%20sobre%20la%20confraternidad%20del%20tenis!");
        }
    </script>
    <!-- Scripts -->
    @yield('scripts')
</body>
</html>
