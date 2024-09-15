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
            --color-navegacion: {{ $Auth::guard('players')->user()->comunidad != null ? $Auth::guard('players')->user()->comunidad->color_navegacion : "#000000" }};
            --color-cabecera: {{ $Auth::guard('players')->user()->comunidad != null ? $Auth::guard('players')->user()->comunidad->color_primario : "#000000" }};
            --color-botones: {{ $Auth::guard('players')->user()->comunidad != null ? $Auth::guard('players')->user()->comunidad->color_secundario : "#000000" }};
            --color-pestana: {{ $Auth::guard('players')->user()->comunidad != null ? $Auth::guard('players')->user()->comunidad->color_alternativo : "#000000" }};
            --font-family-title: {{ $Auth::guard('players')->user()->comunidad != null ? $Auth::guard('players')->user()->comunidad->titulo_fuente : "Anton" }};
            --font-family-text: {{ $Auth::guard('players')->user()->comunidad != null ? $Auth::guard('players')->user()->comunidad->parrafo_fuente : "LeagueSpartan" }};
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
    <nav class="main-header navbar navbar-expand navbar-light m-0">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item dropdown">
                <div class="user-panel-content">
                    <div class="image">
                        <a data-toggle="dropdown" href="javascript:void(0)">
                            Bienvenido, {{ $Auth::guard('players')->user()->nombre_completo }}.
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a href="javascript:void(0);" onclick="event.preventDefault();document.getElementById('logout-form').submit();" class="dropdown-item dropdown-footer"><i class="fa fa-power-off"></i> {{ __('Cerrar Sesión') }}</a>
                            <form id="logout-form" action="{{ route('app.logout') }}" method="POST" style="display: none;">
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
                <a class="nav-link" href="javascript:void(0);" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                <i class="fa fa-door-open"></i> {{ __('Cerrar sesión') }}</a>
                <form id="logout-form" action="{{ route('app.logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Content Wrapper. Contains page content -->
    <div id="main-content-wrapper" class="content-wrapper m-0">
        <div class="container">
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
                if ('/app/' + controller + action === $(location).attr('pathname')) {
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
