@inject('App', 'App\Models\App')
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title>Reporte</title>
    <link rel="stylesheet" href="{{ asset('https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css') }}" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style>
    .d-flex{ display: flex !important; }
    .justify-content-between{ justify-content: space-between; }
    .align-items-center{ align-items: center; }
    .mt-3{ margin-top: 1rem !important; }
    .mb-3{ margin-bottom: 1rem !important; }
    .mt-2{ margin-top: .5rem !important;}
    .mt-6{ margin-top: 2rem !important; }
    .grid{ display: grid !important; }
    .text-center{text-align: center;}
    .table{width: 100% !important;}
    td{font-size: 10px !important;padding: 2px !important;}
    h5{ font-size: 1.25rem;font-weight: 600 !important;}
    @media print
    {
        @page {size: landscape}

        .no-print, .no-print *
        {
            display: none !important;
        }
    }
</style>
</head>
<body>

<div id="container">
    @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')) > 0)
        <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
            <div style="width: 100%; text-align: center"><h5>Mapa del Campeonato</h5></div>
        </div>
            <?php $bloque1A = 1; $bloque2A = 1; $bloque3A = 1; $bloque4A = 1; $bloque1B = 1; $bloque2B = 1; $bloque3B = 1; $bloque4B = 1; ?>
        <div class="grid mt-2" style="display: grid !important;grid-template-columns: 40% 10% 40%;gap: 5%;align-items: center">
            <!-- Lado A -->
            <div class="grid mt-2" style="display: grid;align-items: center;{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 4 ? "grid-template-columns:45% 45%;gap: 10%" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 8 ? "grid-template-columns:30% 30% 30%;gap: 3%" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 16 ? "grid-template-columns:24% 24% 24% 24%;gap: 1%" : "")) }}">
                <!-- Dieciseisavo de Final -->
                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 16)
                    <div style="height: 100%;display: grid;align-items: center;">
                        <div>
                            @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 1) as $q)
                                <div>
                                    <table class="table table-bordered table-striped mb-0 table-game" data-position="{{ ($bloque1A++ % 2 ) == 0 ? "2" : "1"  }}" data-bracket="{{ in_array($bloque1A, [2,3]) ? "upper" : "lower" }}" style="cursor: pointer" data-id="{{ $q->id }}">
                                        <tr><td class="text-center">{{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-")) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-"))) : "-" }}</td></tr>
                                    </table>
                                </div>
                                <div class="mt-6"></div>
                            @endforeach
                        </div>
                        <div>
                            @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 3) as $q)
                                <div>
                                    <table class="table table-bordered table-striped mb-0 table-game" data-position="{{ ($bloque3A++ % 2 ) == 0 ? "2" : "1"  }}" data-bracket="{{ in_array($bloque3A, [2,3]) ? "upper" : "lower" }}" style="cursor: pointer" data-id="{{ $q->id }}">
                                        <tr><td class="text-center">{{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-")) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-"))) : "-" }}</td></tr>
                                    </table>
                                </div>
                                <div class="mt-6"></div>
                            @endforeach
                        </div>
                    </div>
                    <div style="height: 100%;display: grid;align-items: center">
                        <div style="height: 100%;display: grid;align-items: center;">
                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)) > 0)
                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first() != null)
                                    <div>
                                        <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer" data-position="1" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->id }}">
                                            <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorLocalUno->nombre_completo) : "-" }}</td></tr>
                                            <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 1)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                        </table>
                                    </div>
                                @else
                                    <div>
                                        <table class="table table-bordered table-striped mb-0">
                                            <tr><td class="text-center">-</td></tr>
                                            <tr><td class="text-center">-</td></tr>
                                        </table>
                                    </div>
                                @endif
                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first() != null)
                                    <div>
                                        <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer" data-position="2" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->id }}">
                                            <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorLocalUno->nombre_completo) : "-" }}</td></tr>
                                            <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1)->where('position', 2)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                        </table>
                                    </div>
                                @else
                                    <div>
                                        <table class="table table-bordered table-striped mb-0">
                                            <tr><td class="text-center">-</td></tr>
                                            <tr><td class="text-center">-</td></tr>
                                        </table>
                                    </div>
                                @endif
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                            @endif
                        </div>
                        <div style="height: 100%;display: grid;align-items: center;">
                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)) > 0)
                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first() != null)
                                    <div>
                                        <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer" data-position="1" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->id }}">
                                            <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorLocalUno->nombre_completo) : "-" }}</td></tr>
                                            <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 1)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                        </table>
                                    </div>
                                @else
                                    <div>
                                        <table class="table table-bordered table-striped mb-0">
                                            <tr><td class="text-center">-</td></tr>
                                            <tr><td class="text-center">-</td></tr>
                                        </table>
                                    </div>
                                @endif
                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first() != null)
                                    <div>
                                        <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer" data-position="2" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->id }}">
                                            <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorLocalUno->nombre_completo) : "-" }}</td></tr>
                                            <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3)->where('position', 2)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                        </table>
                                    </div>
                                @else
                                    <div>
                                        <table class="table table-bordered table-striped mb-0">
                                            <tr><td class="text-center">-</td></tr>
                                            <tr><td class="text-center">-</td></tr>
                                        </table>
                                    </div>
                                @endif
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div style="height: 100%;display: grid;align-items: center">
                        <div style="height: 100%;display: grid;align-items: center;">
                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)) > 0)
                                <div>
                                    <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer" data-position="1" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->id }}">
                                        <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                    </table>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                            @endif
                        </div>
                        <div style="height: 100%;display: grid;align-items: center;">
                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)) > 0)
                                <div>
                                    <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer" data-position="2" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->id }}">
                                        <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                    </table>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div style="height: 100%;display: grid;align-items: center">
                        <div>
                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)) > 0)
                                <div>
                                    <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer" data-position="1" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->id }}">
                                        <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                    </table>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                @elseif($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 8)
                    <div style="height: 100%;display: grid;align-items: center">
                        <div style="height: 100%;display: grid;align-items: center">
                            @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 1) as $q)
                                <div>
                                    <table class="table table-bordered table-striped mb-0 table-game" data-position="{{ ($bloque1A++ % 2 ) == 0 ? "2" : "1"  }}" style="cursor: pointer" data-id="{{ $q->id }}">
                                        <tr><td class="text-center">{{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-")) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-"))) : "-" }}</td></tr>
                                    </table>
                                </div>
                                <div class="mt-6"></div>
                            @endforeach
                        </div>
                        <div style="height: 100%;display: grid;align-items: center">
                            @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 3) as $q)
                                <div>
                                    <table class="table table-bordered table-striped mb-0 table-game" data-position="{{ ($bloque3A++ % 2 ) == 0 ? "2" : "1"  }}" style="cursor: pointer" data-id="{{ $q->id }}">
                                        <tr><td class="text-center">{{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-")) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-"))) : "-" }}</td></tr>
                                    </table>
                                </div>
                                <div class="mt-6"></div>
                            @endforeach
                        </div>
                    </div>
                    <div style="height: 100%;display: grid;align-items: center">
                        <div>
                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)) > 0)
                                <div>
                                    <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer" data-position="1" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->id }}">
                                        <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                    </table>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                            @endif
                        </div>
                        <div>
                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)) > 0)
                                <div>
                                    <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer" data-position="2" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->id }}">
                                        <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                    </table>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div style="height: 100%;display: grid;align-items: center">
                        <div>
                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)) > 0)
                                <div>
                                    <table class="table table-bordered table-striped mb-0 table-game" data-position="1" style="cursor: pointer" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->id }}">
                                        <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                    </table>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                @elseif($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 4)
                    <div style="height: 100%;display: grid;align-items: center">
                        <div>
                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)) > 0)
                                <div>
                                    <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer" data-position="1" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->id  }}">
                                        <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->multiple ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno != null ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo : "-").' + '.($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalDos != null ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : "-") : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno != null ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo : "-")) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                    </table>
                                </div>
                                <div class="mt-6"></div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                                <div class="mt-6"></div>
                            @endif
                        </div>
                        <div>
                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)) > 0)
                                <div>
                                    <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer" data-position="2" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->id  }}">
                                        <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno != null ?  ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->multiple ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno != null ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo : "-").' + '.($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalDos != null ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalDos->nombre_completo : "-") : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno != null ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorLocalUno->nombre_completo : "-")) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 3)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                    </table>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div style="height: 100%;display: grid;align-items: center">
                        <div>
                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)) > 0)
                                <div>
                                    <table class="table table-bordered table-striped mb-0 table-game" data-position="1" style="cursor: pointer" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->id }}">
                                        <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                    </table>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                @elseif($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 2)
                    <div style="height: 100%;display: grid;align-items: center">
                        <div>
                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)) > 0)
                                <div>
                                    <table class="table table-bordered table-striped mb-0 table-game" data-position="1" style="cursor: pointer" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->id }}">
                                        <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorLocalUno->nombre_completo) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 1)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                    </table>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
            <!-- Final -->
            <div style="height: 100%;display: grid;align-items: center;">
                <div style="text-align: center">
                    @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first() != null)
                        <p class="text-center text-sm m-0"><strong>{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first()->multiple ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first()->jugadorGanadorUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first()->jugadorGanadorDos->nombre_completo) : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->whereNotNull('jugador_ganador_uno_id')->first()->jugadorGanadorUno->nombre_completo }}</strong></p>
                    @else
                        <p class="text-center m-0"><strong>Final</strong></p>
                    @endif

                    @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)) > 0)
                        <table class="table table-bordered table-striped mb-0 table-game" data-position="1" style="cursor: pointer" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->id }}">
                            <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugadorLocalUno->nombre_completo) : "-" }}</td></tr>
                            <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 1)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                        </table>
                    @else
                        <table class="table table-bordered table-striped mb-0">
                            <tr><td class="text-center">-</td></tr>
                            <tr><td class="text-center">-</td></tr>
                        </table>
                    @endif
                </div>
            </div>


            <!-- Lado B -->
            <div class="grid mt-2" style="display: grid;align-items: center;{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 4 ? "grid-template-columns:45% 45%;gap: 10%" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 8 ? "grid-template-columns:30% 30% 30%;gap: 3%" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 16 ? "grid-template-columns:24% 24% 24% 24%;gap: 1%" : "")) }}">
                <!-- Dieciseisavo de Final -->
                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 16)
                    <div style="height: 100%;display: grid;align-items: center">
                        <div>
                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)) > 0)
                                <div>
                                    <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer" data-position="2" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->id }}">
                                        <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                    </table>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div style="height: 100%;display: grid;align-items: center">
                        <div style="height: 100%;display: grid;align-items: center;">
                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)) > 0)
                                <div>
                                    <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer" data-position="1" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->id }}">
                                        <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                    </table>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                            @endif
                        </div>
                        <div style="height: 100%;display: grid;align-items: center;">
                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)) > 0)
                                <div>
                                    <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer" data-position="2" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->id }}">
                                        <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                    </table>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div style="height: 100%;display: grid;align-items: center">
                        <div style="height: 100%;display: grid;align-items: center;">
                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)) > 0)
                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first() != null)
                                    <div>
                                        <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer" data-position="1" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->id }}">
                                            <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorLocalUno->nombre_completo) : "-" }}</td></tr>
                                            <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 1)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                        </table>
                                    </div>
                                @else
                                    <div>
                                        <table class="table table-bordered table-striped mb-0">
                                            <tr><td class="text-center">-</td></tr>
                                            <tr><td class="text-center">-</td></tr>
                                        </table>
                                    </div>
                                @endif
                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first() != null)
                                    <div>
                                        <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer" data-position="2" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->id }}">
                                            <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorLocalUno->nombre_completo) : "-" }}</td></tr>
                                            <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2)->where('position', 2)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                        </table>
                                    </div>
                                @else
                                    <div>
                                        <table class="table table-bordered table-striped mb-0">
                                            <tr><td class="text-center">-</td></tr>
                                            <tr><td class="text-center">-</td></tr>
                                        </table>
                                    </div>
                                @endif
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                            @endif
                        </div>
                        <div style="height: 100%;display: grid;align-items: center;">
                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)) > 0)
                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first() != null)
                                    <div>
                                        <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer" data-position="1" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->id }}">
                                            <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorLocalUno->nombre_completo) : "-" }}</td></tr>
                                            <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 1)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                        </table>
                                    </div>
                                @else
                                    <div>
                                        <table class="table table-bordered table-striped mb-0">
                                            <tr><td class="text-center">-</td></tr>
                                            <tr><td class="text-center">-</td></tr>
                                        </table>
                                    </div>
                                @endif
                                @if($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first() != null)
                                    <div>
                                        <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer" data-position="2" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->id }}">
                                            <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorLocalUno->nombre_completo) : "-" }}</td></tr>
                                            <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4)->where('position', 2)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                        </table>
                                    </div>
                                @else
                                    <div>
                                        <table class="table table-bordered table-striped mb-0">
                                            <tr><td class="text-center">-</td></tr>
                                            <tr><td class="text-center">-</td></tr>
                                        </table>
                                    </div>
                                @endif
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div style="height: 100%;display: grid;align-items: center">
                        <div>
                            @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 2) as $q)
                                <div>
                                    <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer" data-position="{{ ($bloque2A++ % 2 ) == 0 ? "2" : "1"  }}" data-bracket="{{ in_array($bloque2A, [2,3]) ? "upper" : "lower" }}" data-id="{{ $q->id }}">
                                        <tr><td class="text-center">{{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-")) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BnYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-"))) : "-" }}</td></tr>
                                    </table>
                                </div>
                                <div class="mt-6"></div>
                            @endforeach
                        </div>
                        <div>
                            @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 16)->where('bloque', 4) as $q)
                                <div>
                                    <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer" data-position="{{ ($bloque4A++ % 2 ) == 0 ? "2" : "1"  }}" data-bracket="{{ in_array($bloque4A, [2,3]) ? "upper" : "lower" }}" data-id="{{ $q->id }}">
                                        <tr><td class="text-center">{{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-")) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-"))) : "-" }}</td></tr>
                                    </table>
                                </div>
                                <div class="mt-6"></div>
                            @endforeach
                        </div>
                    </div>
                @elseif($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 8)
                    <div style="height: 100%;display: grid;align-items: center">
                        <div>
                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)) > 0)
                                <div>
                                    <table class="table table-bordered table-striped mb-0 table-game" data-position="2" style="cursor: pointer" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->id }}">
                                        <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy ? "BnYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                    </table>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div style="height: 100%;display: grid;align-items: center">
                        <div>
                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)) > 0)
                                <div>
                                    <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer" data-position="1" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->id }}">
                                        <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($q->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                    </table>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                            @endif
                        </div>
                        <div>
                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)) > 0)
                                <div>
                                    <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer" data-position="2" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->id }}">
                                        <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno != null ? ($q->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                    </table>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div style="height: 100%;display: grid;align-items: center">
                        <div style="height: 100%;display: grid;align-items: center">
                            @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 2) as $q)
                                <div>
                                    <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer" data-position="{{ ($bloque2A++ % 2 ) == 0 ? "2" : "1"  }}" data-id="{{ $q->id }}">
                                        <tr><td class="text-center">{{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-")) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-"))) : "-" }}</td></tr>
                                    </table>
                                </div>
                            @endforeach
                        </div>
                        <div style="height: 100%;display: grid;align-items: center">
                            @foreach($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 8)->where('bloque', 4) as $q)
                                <div>
                                    <table class="table table-bordered table-striped mb-0 table-game" style="cursor: pointer" data-position="{{ ($bloque4A++ % 2 ) == 0 ? "2" : "1"  }}" data-id="{{ $q->id }}">
                                        <tr><td class="text-center">{{ $q->jugadorLocalUno != null ? ($q->multiple ? ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-").' + '.($q->jugadorLocalDos != null ? $q->jugadorLocalDos->nombre_completo : "-") : ($q->jugadorLocalUno != null ? $q->jugadorLocalUno->nombre_completo : "-")) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ $q->jugadorRivalUno != null ? (!$TorneoFaseFinal->TorneoCategoria->manual && $q->buy ? "BYE" : ($q->multiple ? ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-").' + '.($q->jugadorRivalDos != null ? $q->jugadorRivalDos->nombre_completo : "-") : ($q->jugadorRivalUno != null ? $q->jugadorRivalUno->nombre_completo : "-"))) : "-" }}</td></tr>
                                    </table>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @elseif($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 4)
                    <div style="height: 100%;display: grid;align-items: center">
                        <div>
                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)) > 0)
                                <div>
                                    <table class="table table-bordered table-striped mb-0 table-game" data-position="2" style="cursor: pointer" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->id }}">
                                        <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                    </table>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div style="height: 100%;display: grid;align-items: center">
                        <div>
                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)) > 0)
                                <div>
                                    <table class="table table-bordered table-striped mb-0 table-game" data-position="1" style="cursor: pointer" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->id }}">
                                        <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                    </table>
                                </div>
                                <div class="mt-6"></div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                                <div class="mt-6"></div>
                            @endif
                        </div>
                        <div>
                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)) > 0)
                                <div>
                                    <table class="table table-bordered table-striped mb-0  table-game" data-position="2" style="cursor: pointer" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->id }}">
                                        <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorLocalUno->nombre_completo) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 4)->where('bloque', 4)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                    </table>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                @elseif($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->whereNotNull('fase')->first()->fase == 2)
                    <div style="height: 100%;display: grid;align-items: center">
                        <div>
                            @if(count($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)) > 0)
                                <div>
                                    <table class="table table-bordered table-striped mb-0 table-game" data-position="2" style="cursor: pointer" data-id="{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->id }}">
                                        <tr><td class="text-center">{{ $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorLocalUno->nombre_completo) : "-" }}</td></tr>
                                        <tr><td class="text-center">{{ !$TorneoFaseFinal->TorneoCategoria->manual && $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->buy ? "BYE" : ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno != null ? ($TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->multiple ? $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo.' + '.$TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalDos->nombre_completo : $TorneoFaseFinal->TorneoCategoria->torneo->partidos->where('torneo_categoria_id', $TorneoFaseFinal->TorneoCategoria->id)->where('fase', 2)->where('bloque', 2)->first()->jugadorRivalUno->nombre_completo) : "-")  }}</td></tr>
                                    </table>
                                </div>
                            @else
                                <div>
                                    <table class="table table-bordered table-striped mb-0">
                                        <tr><td class="text-center">-</td></tr>
                                        <tr><td class="text-center">-</td></tr>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

<button id="cmd">generate PDF</button>


<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script><script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js"></script>
<script type="text/javascript" src="https://html2canvas.hertzen.com/dist/html2canvas.js"></script>

<script type="text/javascript">
   $(function(){
       var doc = new jsPDF();
       var specialElementHandlers = {
           '#editor': function (element, renderer) {
               return true;
           }
       };

       $('#cmd').click(function () {
           doc.fromHTML($('#container').html(), 15, 15, {
               'width': 170,
               'elementHandlers': specialElementHandlers
           });
           doc.save('sample-file.pdf');
       });
   })
</script>
</body>
</html>
