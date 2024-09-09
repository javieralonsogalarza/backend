@inject('Pagina', 'App\Models\Pagina')

@extends('app.layout.app')

@section('content')
    <div>
        <section id="inicio">
            @if($Portadas != null && count($Portadas) > 0)
                <div class="tp-banner-container">
                    <div class="tp-banner">
                        <ul>
                            @foreach($Portadas as $key => $value)
                                <li data-transition="fade" data-slotamount="{{ $key + 1 }}" data-masterspeed="1000" >
                                    <img src="{{ asset('img/'.$value->imagen_path) }}"  alt="{{ $value->titulo }}"  data-bgfit="cover" data-bgposition="left top" data-bgrepeat="no-repeat">
                                    <div class="tp-caption tp-banner-title skewfromrightshort fadeout"
                                         data-x="85"
                                         data-y="154"
                                         data-speed="500"
                                         data-start="1200"
                                         data-easing="Power4.easeOut">{{ $value->titulo_uno }}
                                    </div>
                                    <div class="tp-caption tp-banner-title skewfromrightshort fadeout"
                                         data-x="85"
                                         data-y="214"
                                         data-speed="500"
                                         data-start="1200"
                                         data-easing="Power4.easeOut">{{ $value->titulo_dos }}
                                    </div>
                                    <div class="tp-caption tp-banner-text skewfromrightshort fadeout"
                                         data-x="85"
                                         data-y="284"
                                         data-speed="700"
                                         data-start="1200"
                                         data-easing="Power4.easeOut">{{ $value->parrafo_uno }}
                                    </div>
                                    <div class="tp-caption tp-banner-text skewfromrightshort fadeout"
                                         data-x="85"
                                         data-y="304"
                                         data-speed="700"
                                         data-start="1200"
                                         data-easing="Power4.easeOut">{{ $value->parrafo_dos }}
                                    </div>
                                    <div class="tp-caption tp-banner-text skewfromrightshort fadeout"
                                         data-x="85"
                                         data-y="324"
                                         data-speed="700"
                                         data-start="1200"
                                         data-easing="Power4.easeOut">{{ $value->parrafo_tres }}
                                    </div>
                                    <div class="tp-caption tp-banner-button skewfromrightshort fadeout"
                                         data-x="85"
                                         data-y="354"
                                         data-speed="90"
                                         data-start="1200"
                                         data-easing="Power4.easeOut">
                                        <a href="#contactanos" class="btn btn-primary">Contactanos</a>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </section>
        <div class="container">
            <section id="nosotros">
                @if($Pagina::firstSeccion() != null)
                    <div class="content-bg" style="background-image: url({{ asset('/img/'.$Pagina::firstSeccion()->imagen_path) }})">
                        <div class="content-text">
                            <div>
                                <h2>{{ $Pagina::firstSeccion()->titulo }}</h2>
                                <p>{{ $Pagina::firstSeccion()->descripcion }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </section>
            <section id="fotografias">
                <div>
                    @if($Pagina::secondSeccion() != null)
                        <h2>{{ $Pagina::secondSeccion()->titulo }}</h2>
                        <div class="content-text">
                            <p>{{ $Pagina::secondSeccion()->descripcion }}</p>
                        </div>
                    @endif
                    <div class="content-gallery">
                        <div class="owl-carousel">
                            @if($Galerias != null && count($Galerias) > 0)
                                @foreach($Galerias as $q)
                                    <div>
                                        <div>
                                            <a href="javascript:void(0)" title="{{ $q->nombre }}">
                                                <img src="{{ asset('img/'.$q->imagen_path) }}" alt="{{ $q->nombre }}">
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </section>
            <section id="contactanos">
                <div>
                    @if($Pagina::threeSeccion() != null)
                        <h2>{{ $Pagina::threeSeccion()->titulo }}</h2>
                        <div class="content-text">
                            <p>{{ $Pagina::threeSeccion()->descripcion }}</p>
                        </div>
                    @endif
                </div>
                <div class="grid grid-column-2 mt-4">
                    <div>
                        <div class="fb-page" data-href="https://www.facebook.com/profile.php?id=100089379933699" data-tabs="timeline" data-width="" data-height="" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true"><blockquote cite="https://www.facebook.com/profile.php?id=100089379933699" class="fb-xfbml-parse-ignore"><a href="https://www.facebook.com/profile.php?id=100089379933699">La Confraternidad del Tenis</a></blockquote></div>
                    </div>
                    <div class="form-content">
                        <form method="POST">
                            <div class="grid grid-column-50">
                                <div class="form-group">
                                    <label for="nombres">Nombres: <span class="text-danger">(*)</span></label>
                                    <input type="text" id="nombres" name="nombres" class="form-control" required>
                                    <span class="validate text-danger validate-nombres"></span>
                                </div>
                                <div class="form-group">
                                    <label for="apellidos">Apellidos: <span class="text-danger">(*)</span></label>
                                    <input type="text" id="apellidos" name="apellidos" class="form-control" required>
                                    <span class="validate text-danger validate-apellidos"></span>
                                </div>
                            </div>
                            <div class="grid grid-column-50">
                                <div class="form-group">
                                    <label for="email">E-mail: <span class="text-danger">(*)</span></label>
                                    <input type="email" id="email" name="email" class="form-control" required>
                                    <span class="validate text-danger validate-email"></span>
                                </div>
                                <div class="form-group">
                                    <label for="celular">Celular: <span class="text-danger">(*)</span></label>
                                    <input type="text" id="celular" name="celular" class="form-control">
                                    <span class="validate text-danger validate-celular"></span>
                                </div>
                            </div>
                            <div class="grid">
                                <div class="form-group">
                                    <label for="mensaje">Mensaje: <span class="text-danger">(*)</span></label>
                                    <textarea name="mensaje" id="mensaje" class="form-control" rows="5" required></textarea>
                                    <span class="validate text-danger validate-mensaje"></span>
                                </div>
                            </div>
                            <div class="grid submit">
                                <button type="button" class="g-recaptcha btn btn-primary" data-sitekey="6LdOFgMpAAAAAItgMx4rZpy7bXPnWD5Bjf-8M0GI" data-callback='onSubmit' data-action='submit'>
                                    Enviar
                                </button>
                            </div>
                        </form>
                        <div class="form_content_success hidden">
                            <h2 class="text-white">¡Gracias, Mensaje Enviado!</h2>
                            <p class="text-white">Pronto nos pondremos en comunicación contigo <br> para brindarte más información.</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- RevolutionSlider -->
    <script type="text/javascript" src="{{ asset('plugins/revolution-slider/jquery.themepunch.plugins.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('plugins/revolution-slider/jquery.themepunch.revolution.min.js') }}"></script>
    <script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery('.tp-banner').revolution(
                {
                    delay:9000,
                    startwidth:1170,
                    startheight:500,
                    hideThumbs:10
                });
        });
    </script>
    <!-- OwlCarousel -->
    <script type="text/javascript" src="{{ asset('plugins/owlcarousel/owl.carousel.min.js') }}"></script>
    <script type="text/javascript">
        jQuery(document).ready(function(){
            jQuery(".owl-carousel").owlCarousel({
                loop:true,
                autoplay : true,
                margin:10,
                slideTransition: 'linear',
                autoplayTimeout:5000,
                //autoplaySpeed : 10000,
                mouseDrag: true,
                dots: false,
                nav: false,
                autoplayHoverPause: true,
                responsiveClass:true,
                responsive:{
                    0:{items:1, nav:false, loop:true},
                    600:{items:3, nav:false, loop:true},
                    1000:{items:4, nav:false, loop:true}
                }
            });
        });
    </script>
    <script type="text/javascript" src="{{ asset('js/index.min.js?v='.\Carbon\Carbon::now()->toDateTimeString()) }}"></script>
@endsection
