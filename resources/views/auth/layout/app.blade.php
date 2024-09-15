@inject('Carbon','Carbon\Carbon')
@inject('App', 'App\Models\App')
@inject('Auth', '\Illuminate\Support\Facades\Auth')
@inject('Route', '\Illuminate\Support\Facades\Route')
<!DOCTYPE html>
<html lang="lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ str_replace("_", " ", env('APP_NAME')) }}</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- AdminLTE Plugins -->
    <link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/fontawesome-free/css/all.css') }}">
    <link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/daterangepicker/daterangepicker.css') }}">
    <!-- SweetAlert -->
    <link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/sweetalert2/sweetalert2.min.css') }}">
    <!-- iCheck for checkboxes and radio inputs -->
    <link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
   <link rel="stylesheet" href="{{ asset('auth/adminlte3//plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="{{ asset('auth/adminlte3/dist/css/adminlte.min.css') }}">
    <!-- Layout -->
    <style type="text/css">
        :root{
            --color-navegacion: {{ $Auth::guard('web')->user()->comunidad != null ? $Auth::guard('web')->user()->comunidad->color_navegacion : "#000000" }};
            --color-cabecera: {{ $Auth::guard('web')->user()->comunidad != null ? $Auth::guard('web')->user()->comunidad->color_primario : "#000000" }};
            --color-botones: {{ $Auth::guard('web')->user()->comunidad != null ? $Auth::guard('web')->user()->comunidad->color_secundario : "#000000" }};
            --color-pestana: {{ $Auth::guard('web')->user()->comunidad != null ? $Auth::guard('web')->user()->comunidad->color_alternativo : "#000000" }};
            --font-family-title: {{ $Auth::guard('web')->user()->comunidad != null ? $Auth::guard('web')->user()->comunidad->titulo_fuente : "Anton" }};
            --font-family-text: {{ $Auth::guard('web')->user()->comunidad != null ? $Auth::guard('web')->user()->comunidad->parrafo_fuente : "LeagueSpartan" }};
        }
    </style>
    <link rel="stylesheet" href="{{ asset('auth/css/layout.min.css') }}">
    <!-- Pages -->
    @yield('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">

<div id="loading">
    <i class="fas fa-sync-alt fa-spin" aria-hidden="true"></i>
</div>

<div class="wrapper">
    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="{{ asset('images/loading.gif') }}" alt="{{ str_replace("_", " ", env('APP_NAME')) }} Logo" width="400">
    </div>
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item dropdown">
                <div class="user-panel-content">
                    <div class="image">
                        <a data-toggle="dropdown" href="javascript:void(0)">
                            Bienvenido, {{ $Auth::guard('web')->user()->nombre }}.
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a href="javascript:void(0);" onclick="event.preventDefault();localStorage.setItem('cliente_id','');document.getElementById('logout-form').submit();" class="dropdown-item dropdown-footer"><i class="fa fa-power-off"></i> {{ __('Cerrar Sesión') }}</a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="javascript:void(0);" onclick="event.preventDefault();localStorage.setItem('cliente_id','');document.getElementById('logout-form').submit();">
                <i class="fa fa-door-open"></i> {{ __('Cerrar sesión') }}</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <div class="header-sidebar">
            <div class="logo">
                @if($Auth::guard('web')->user()->perfil_id == $App::$PERFIL_ADMINISTRADOR)
                <a href="{{ route('auth.home.index') }}" class="brand-link">
                    Administrador Master
                </a>
                @else
                    <a href="{{ route('index') }}" class="brand-link">
                        <img src="{{ ($Auth::guard('web')->user()->comunidad != null ? ($Auth::guard('web')->user()->comunidad->imagen_path != null && $Auth::guard('web')->user()->comunidad->imagen_path != "" ? '/img/'.$Auth::guard('web')->user()->comunidad->imagen_path : "/upload/image/default.png") : "/upload/image/default.png") }}" alt="{{ $Auth::guard('web')->user()->nombre }}Logo" class="brand-image">
                    </a>
                @endif
            </div>
        </div>
        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar Menu -->
            <nav class="mt-4">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                    @if($Auth::guard('web')->user()->perfil_id == $App::$PERFIL_ADMINISTRADOR)
                    <li class="nav-header">PRINCIPAL</li>
                    <li class="nav-item">
                        <a href="{{ route('auth.home.index') }}" class="nav-link">
                            <i class="nav-icon fa fa-home"></i> <p>Inicio</p>
                        </a>
                    </li>
                    @endif

                    @if($Auth::guard('web')->user()->perfil_id == $App::$PERFIL_COMUNIDAD)
                    <li class="nav-item">
                        <a href="{{ route('auth.rankings.index') }}" class="nav-link">
                            <i class="nav-icon fa fa-star"></i>
                            <p>Rankings</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('auth.torneo.index') }}" class="nav-link">
                            <i class="nav-icon fa fa-trophy"></i>
                            <p>Gestión de Torneos</p>
                        </a>
                    </li>
                    <li class="nav-header">MANTENIMIENTOS</li>
                    <li class="nav-item">
                        <a href="{{ route('auth.jugador.index') }}" class="nav-link">
                            <i class="fa fa-users nav-icon"></i> <p>Gestión de Jugadores</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('auth.categoria.index') }}" class="nav-link">
                            <i class="fa fa-cubes nav-icon"></i> <p>Gestión de Categoría</p>
                        </a>
                    </li>
                    @endif

                    <li class="nav-header">SEGURIDAD</li>
                    <li class="nav-item">
                        <a href="{{ route('auth.usuario.index') }}" class="nav-link">
                            <i class="fa fa-user-cog nav-icon"></i> <p>Administradores</p>
                        </a>
                    </li>

                    @if($Auth::guard('web')->user()->perfil_id == $App::$PERFIL_COMUNIDAD)
                        <li class="nav-header">CONFIGURACIÓN</li>
                        <li class="nav-item">
                            <a href="{{ route('auth.perfil.index') }}" class="nav-link">
                                <i class="fa fa-palette nav-icon"></i> <p>Personalización</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('auth.puntuacion.index') }}" class="nav-link">
                                <i class="fa fa-coins nav-icon"></i> <p>Puntuaciones</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('auth.formato.index') }}" class="nav-link">
                                <i class="fa fa-file nav-icon"></i> <p>Torneo Formatos</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('auth.zona.index') }}" class="nav-link">
                                <i class="fa fa-map-marker nav-icon"></i> <p>Zonas</p>
                            </a>
                        </li>
                        <li class="nav-header">REPORTE</li>
                        <li class="nav-item">
                            <a href="{{ route('auth.reporte.torneo') }}" class="nav-link">
                                <i class="fa fa-trophy nav-icon"></i> <p>Torneo</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('auth.reporte.jugador') }}" class="nav-link">
                                <i class="fa fa-address-card nav-icon"></i> <p>Jugador</p>
                            </a>
                        </li>
                        <li class="nav-header">PÁGINA WEB</li>
                        <li class="nav-item">
                            <a href="{{ route('auth.portada.index') }}" class="nav-link">
                                <i class="fa fa-images nav-icon"></i><p>Portadas</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('auth.pagina.index') }}" class="nav-link">
                                <i class="fa fa-pager nav-icon"></i><p>Contenidos</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('auth.galeria.index') }}" class="nav-link">
                                <i class="fa fa-camera nav-icon"></i> <p>Galerías</p>
                            </a>
                        </li>
                    @endif
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div id="main-content-wrapper" class="content-wrapper">
        <div class="container-fluid">
            <div class="p-3">
                @yield('main')
            </div>
        </div>
    </div>

    <!-- /.content-wrapper -->
    <footer class="main-footer">
        <strong>Copyright &copy; {{ $Carbon::now()->year }} {{ str_replace("_", " ", env('APP_NAME')) }}.</strong>
        todos los derechos reservados.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> {{ $App::$APP_VERSION }}
        </div>
    </footer>
</div>
<!-- ./wrapper -->

<!-- JQuery -->
<script src="{{ asset('auth/adminlte3/plugins/jquery/jquery.min.js') }}"></script>
<!-- jQuery UI 1.11.4 -->
<script src="{{ asset('auth/adminlte3/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('auth/adminlte3/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- daterangepicker -->
<script src="{{ asset('auth/adminlte3/plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('auth/adminlte3/plugins/daterangepicker/daterangepicker.js') }}"></script>
<!-- overlayScrollbars -->
<script src="{{ asset('auth/adminlte3/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
<!-- AdminLTE -->
<script src="{{ asset('auth/adminlte3/dist/js/adminlte.js') }}"></script>
<!-- SweetAlert -->
<script src="{{ asset('auth/adminlte3/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<!-- Toastr -->
<script src="{{ asset('auth/adminlte3/plugins/toastr/toastr.min.js') }}"></script>
<!-- InputMask -->
<script src="{{ asset('auth/adminlte3/plugins/inputmask/jquery.inputmask.min.js') }}"></script>
<script src="{{ asset('auth/adminlte3/plugins/inputmask/inputmask.min.js') }}"></script>
<!-- Moment -->
<script src="{{ asset('auth/adminlte3/plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('auth/adminlte3/plugins/moment/moment-with-locales.min.js') }}"></script>
<script src="{{ asset('auth/adminlte3/plugins/moment/locale/es.js') }}"></script>
<!-- DataTables -->
<script src="{{ asset('auth/adminlte3/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('auth/adminlte3/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('auth/adminlte3/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('auth/adminlte3/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('auth/adminlte3/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('auth/adminlte3/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>

<script type="text/javascript">
    $(document).ready(function () {
        $("ul.nav-sidebar").find("a").each(function (i, e) {
            if ($(e).attr('href') !== "#") {
                const fragments = $(e).attr("href").split('/');
                const controller = $(e).attr("href").split('/')[4];
                const action = fragments.length === 6 ? '/' + $(e).attr("href").split('/')[5] : "";
                if ('/auth/' + controller + action === $(location).attr('pathname')) {
                    $(e).parent('li').find('a').addClass("active");
                    $(e).parents('.treeview-menu').addClass("menu-open");
                    $(e).parents('.treeview').addClass("active");
                }
            }
        });
    });
</script>
<!-- Layout -->
<script src="{{ asset('auth/layout.js') }}"></script>

@yield('scripts')

</body>
</html>
