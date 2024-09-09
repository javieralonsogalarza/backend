@inject('App', 'App\Models\App')

@if($landing)
    <style type="text/css">
        select, input, textarea{ background-color: transparent !important; }
        select::-ms-expand {display: none;}
        select{ -webkit-appearance: none;-moz-appearance: none;text-indent: 1px;text-overflow: '';}
        a.nav-link{ color: black !important; }
        a.nav-link.active{ background-color: var(--color-cabecera) !important; border-color: var(--color-cabecera) !important; color: white !important;}
    </style>
@endif

<div class="card">
    <div class="card-body">

        <div class="row report-not-view">
            <div class="col-md-12 text-center pt-3 pb-3">
                <h3 class="html-view" style="color: black !important;">Torneo {{ $Model->nombre }}</h3>
                <h3 class="report-view hidden">Torneo {{ $Model->nombre }}<span id="textCategoria"></span></h3>
                <p style="font-size: 17px;margin-bottom: 0.5rem">Desde: {{ \Carbon\Carbon::parse($Model->fecha_inicio)->format('d M Y') }} - Hasta: {{ \Carbon\Carbon::parse($Model->fecha_final)->format('d M Y') }}</p>
                <p style="font-size: 17px;margin-bottom: 0.5rem">Formato: {{ $Model->formato != null ? $Model->formato->nombre : "-" }}</p>
                <button type="button" class="close close-view" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
        <ol class="nav nav-tabs serialization horizontal" id="custom-tabs-one-tab" role="tablist">
            @foreach($Model->torneoCategorias()->where('torneo_id', $Model->id)->orderBy('orden')->get() as $key => $q)
                <li class="nav-item" data-id="{{ $q->id }}" data-name="{{ $q->multiple && ($q->categoriaSimple->id !== $q->categoriaDupla->id) ? ($q->categoriaSimple->nombre." + ".$q->categoriaDupla->nombre) : ($q->categoriaSimple->nombre)."".($q->multiple ? " (Doble) " : "") }}">
                    <a class="nav-link tab-category {{ $TorneoCategoriaId == $q->id ? "active" : "" }}" data-id="{{ $q->id }}" data-ranking="{{ $q->solo_ranking }}" data-mapa="{{ $q->first_final }}" id="custom-tabs-{{ $q->id }}-tab" data-toggle="pill" href="#custom-tabs-{{ $q->id }}" role="tab" aria-controls="custom-tabs-{{ $q->id }}" aria-selected="true">
                        {{ $q->multiple && ($q->categoriaSimple->id !== $q->categoriaDupla->id) ? ($q->categoriaSimple->nombre." + ".$q->categoriaDupla->nombre) : ($q->categoriaSimple->nombre)."".($q->multiple ? " (Doble) " : "") }}
                    </a>
                </li>
            @endforeach
        </ol>
        <div class="tab-content" id="custom-tabs-one-tabContent">
            @foreach($Model->torneoCategorias()->where('torneo_id', $Model->id)->orderBy('orden')->get() as $key => $q)
                <div class="tab-pane p-4 fade {{ $TorneoCategoriaId == $q->id ? "show active" : "" }}" id="custom-tabs-{{ $q->id }}" role="tabpanel" aria-labelledby="custom-tabs-{{ $q->id }}-tab">
                    <ul class="nav nav-tabs" id="custom-tabs-two-tab" role="tablist">
                        @if($q->solo_ranking)
                            <li class="nav-item">
                                <a class="nav-link tab-ranking active" id="custom-tabs-fase-three-tab-{{$q->id}}" data-id="{{ $q->id }}" data-toggle="pill" href="#custom-tabs-fase-three-{{$q->id}}" role="tab" aria-controls="custom-tabs-fase-three-{{$q->id}}" aria-selected="true">Ranking</a>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link {{ ($TorneoCategoriaId != $q->id || $Fase == null) ? "active" : "" }}" id="custom-tabs-fase-one-tab-{{$q->id}}" data-toggle="pill" href="#custom-tabs-fase-one-{{$q->id}}" role="tab" aria-controls="custom-tabs-fase-one-{{$q->id}}" aria-selected="true">Primera Fase</a>
                            </li>
                            @if(!$q->first_final)
                                <li class="nav-item">
                                    <a class="nav-link tab-phase-final {{ $TorneoCategoriaId == $q->id && $Fase == 2 ? "active" : "" }}" id="custom-tabs-fase-two-tab-{{$q->id}}" data-id="{{ $q->id }}" data-toggle="pill" href="#custom-tabs-fase-two-{{$q->id}}" role="tab" aria-controls="custom-tabs-fase-two-{{$q->id}}" aria-selected="true">Segunda Fase</a>
                                </li>
                            @endif
                            <li class="nav-item">
                                <a class="nav-link tab-ranking {{ $TorneoCategoriaId == $q->id && $Fase == 3 ? "active" : "" }}" id="custom-tabs-fase-three-tab-{{$q->id}}" data-id="{{ $q->id }}" data-toggle="pill" href="#custom-tabs-fase-three-{{$q->id}}" role="tab" aria-controls="custom-tabs-fase-three-{{$q->id}}" aria-selected="true">Ranking</a>
                            </li>
                        @endif
                    </ul>
                    <div class="tab-content" id="custom-tabs-two-tabContent-{{ $q->id }}">
                        @if($q->solo_ranking)
                            <div class="tab-pane pt-3 fade show active" id="custom-tabs-fase-three-{{$q->id}}" role="tabpanel" aria-labelledby="custom-tabs-fase-three-tab-{{$q->id}}">
                                <div>
                                    <div id="partialViewRanking{{$q->id}}"></div>
                                </div>
                            </div>
                        @else
                            <div class="tab-pane first-pane pt-3 fade {{ ($TorneoCategoriaId != $q->id || $Fase == null) ? "show active" : "" }}" id="custom-tabs-fase-one-{{$q->id}}" role="tabpanel" aria-labelledby="custom-tabs-fase-one-tab-{{$q->id}}">
                                <div class="d-flex justify-content-between align-items-end">
                                    <div><h5>Listado de Jugadores</h5></div>
                                    <div>
                                        <input type="hidden" name="clasificados_{{ $q->id }}" id="clasificados_{{ $q->id }}" value="2">

                                        <div class="row mt-3">
                                            @if($Model->torneoJugadors != null && count($Model->torneoJugadors()->where('torneo_id', $Model->id)->whereHas('jugadorSimple')->where('torneo_categoria_id', $q->id)->get()) > 0)
                                                <div>
                                                    <ul class="d-flex list-unstyled m-0">
                                                        <li><button type="button" data-category="{{ $q->id }}" class="btn btnReporteLocalizacion btn-success mr-2"><i class="fa fa-file-excel"></i> Reporte Localización</button></li>
                                                        <li><button type="button" data-category="{{ $q->id }}" class="btn btnReportePagos btn-success mr-2"><i class="fa fa-file-excel"></i> Reporte Pagos</button></li>
                                                    </ul>
                                                </div>
                                            @endif
                                            @if(count($Model->partidos->where('torneo_categoria_id', $q->id)) == 0)
                                                <div class="d-flex justify-content-end align-items-center">
                                                    @if($Model->torneoJugadors != null && count($Model->torneoJugadors->where('torneo_categoria_id', $q->id)) > 0)
                                                        <div class="mr-2"><button type="button" class="btn btn-danger btn-delete-players" data-id="{{ $q->id }}" data-category-name="{{ $q->categoriaSimple != null ? $q->categoriaSimple->nombre : "-" }}"><i class="fa fa-trash"></i> Eliminar Jugadores Asignados</button></div>
                                                    @endif
                                                    <div><button type="button" class="btn btn-primary btn-players" data-id="{{ $q->id }}"><i class="fa fa-users"></i> Asignar Jugadores</button></div>
                                                </div>
                                            @elseif(count($Model->partidos->where('torneo_categoria_id', $q->id)->where('estado_id', $App::$ESTADO_FINALIZADO)) <= 0)
                                                <div class="d-flex justify-content-end align-items-center">
                                                    <div><button type="button" class="btn btn-primary btn-players" data-id="{{ $q->id }}"><i class="fa fa-users"></i> Asignar Jugadores</button></div>
                                                </div>
                                            @endif
                                            @if($Model->torneoJugadors != null && count($Model->torneoJugadors->where('torneo_categoria_id', $q->id)) > 0)
                                                <div class="ml-2"><button type="button" class="btn btn-primary btn-generate-json-players" data-category="{{ $q->id }}"> Generar Json</button></div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @if($Model->torneoJugadors != null && count($Model->torneoJugadors()->where('torneo_id', $Model->id)->whereHas('jugadorSimple')->where('torneo_categoria_id', $q->id)->get()) > 0)
                                    <div class="row mt-3">
                                        @for($i = 0; $i < intval(ceil(count($Model->torneoJugadors()->where('torneo_id', $Model->id)->whereHas('jugadorSimple')->where('torneo_categoria_id', $q->id)->get())/8)); $i++)
                                                <?php $initial = 0; ?>
                                            <div class="{{ $q->multiple ? "col-md-4": "col-md-3" }}">
                                                <table id="table{{ $q->id }}" class="mt-3 table table-bordered table-striped table-players-view table-players-{{ $q->id }}">
                                                    <thead>
                                                    <tr>
                                                        <th align="center" class="align-middle text-left">Nombre Completo</th>
                                                        @if(!$landing)
                                                            <th align="center" class="align-middle text-center"></th>
                                                            <th align="center" class="align-middle text-center"></th>
                                                            <th align="center" class="align-middle text-center"></th>
                                                        @endif
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($Model->torneoJugadors()->where('torneo_id', $Model->id)->whereHas('jugadorSimple')->where('torneo_categoria_id', $q->id)->get() as $q3)
                                                        @if($initial >= ($i == 0 ? 0 : ($i*8))  && $initial <= ($i == 0 ? 7 : (($i+1)*8)-1))
                                                            @if($q3->jugadorSimple != null)
                                                                <tr>
                                                                    <td align="center" class="align-middle text-left td-jugador-info" data-category="{{ $q->id }}" style="cursor: pointer !important;" data-jugador-info="{{ $q3->id }}">
                                                                        {{ $q->multiple ? ($q3->jugadorSimple->nombre_completo." + ".($q3->jugadorDupla != null ? $q3->jugadorDupla->nombre_completo : "-")) : $q3->jugadorSimple->nombre_completo }}
                                                                    </td>
                                                                    @if(!$landing)
                                                                        <td width="40" align="center" data-jugador-info="{{ $q3->id }}">
                                                                            @if($q3->pago)
                                                                                <i class="fa fa-coins" title="Si pagó" style="color: #dc8306; cursor: pointer"></i>
                                                                            @endif
                                                                        </td>
                                                                        <td width="40" align="center" data-jugador-info="{{ $q3->id }}">
                                                                            @if($q3->zona != null)
                                                                                <i class="fa fa-map-marker" title="{{ $q3->zona->nombre }}" style="color: #b61616;cursor: pointer"></i>
                                                                            @endif
                                                                        </td>
                                                                        <td width="40" align="center" class="align-middle text-center">
                                                                            @if(count($Model->partidos()->where('torneo_categoria_id', $q->id)->where('buy', false)->where('estado_id', $App::$ESTADO_FINALIZADO)->where(function($o) use($q3){
                                                                            $o->where('jugador_local_uno_id', $q3->jugador_simple_id)->orWhere('jugador_local_dos_id', $q3->jugador_simple_id)->orWhere('jugador_rival_uno_id', $q3->jugador_simple_id)->orWhere('jugador_rival_dos_id', $q3->jugador_simple_id); })
                                                                            ->where(function($o) use($q3){ $o->where('jugador_local_uno_id', $q3->jugador_dupla_id)->orWhere('jugador_local_dos_id', $q3->jugador_dupla_id)->orWhere('jugador_rival_uno_id', $q3->jugador_dupla_id)->orWhere('jugador_rival_dos_id', $q3->jugador_dupla_id); })->get()) <= 0)

                                                                                @if(count($Model->partidos->where('torneo_categoria_id', $q->id)->where('buy', false)->where('estado_id', $App::$ESTADO_PENDIENTE)) > 0 && !$q3->after)
                                                                                    <button id="btnChangePlayer_{{$q->id}}_{{$q3->jugador_simple_id}}_{{$q3->jugador_dupla_id}}" type="button" class="btn btn-default btn-change-player btn-xs" title="Remplazar Jugador" data-id="{{ $q3->id }}" data-info="{{ $q->multiple ? ($q3->jugadorSimple->nombre_completo." + ".($q3->jugadorDupla != null ? $q3->jugadorDupla->nombre_completo : "-")) : $q3->jugadorSimple->nombre_completo }}" data-category="{{ $q->id }}" data-category-name="{{ $q->categoriaSimple != null ? $q->categoriaSimple->nombre : "-" }}">
                                                                                        <img src="{{ asset('/images/icon_exchange.png') }}" width="20" alt="Remplazar Jugador">
                                                                                    </button>
                                                                                @else
                                                                                    <button type="button" class="btn btn-danger btn-delete-player btn-xs" title="Eliminar Jugador" data-id="{{ $q3->id }}" data-info="{{ $q->multiple ? ($q3->jugadorSimple->nombre_completo." + ".($q3->jugadorDupla != null ? $q3->jugadorDupla->nombre_completo : "-")) : $q3->jugadorSimple->nombre_completo }}" data-category="{{ $q->id  }}" data-category-name="{{ $q->categoriaSimple != null ? $q->categoriaSimple->nombre : "-" }}"><i class="fa fa-trash"></i></button>
                                                                                @endif

                                                                            @endif
                                                                        </td>
                                                                    @endif
                                                                </tr>
                                                            @endif
                                                        @endif
                                                            <?php $initial++; ?>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endfor
                                    </div>
                                    @if(count($Model->partidos->where('torneo_categoria_id', $q->id)) <= 0)
                                        <div class="row mt-3">
                                            <ul class="w-100 content-button-first-fase d-flex align-content-center justify-content-end list-unstyled p-0">
                                                <li class="mr-1"><button type="button" class="btn btn-primary btn-generate-keys-final" data-reload="0" data-id="{{ $q->id }}"><i class="fa fa-key"></i> Keys de Eliminación</button></li>
                                                <li class="mr-1"><button type="button" class="btn btn-primary btn-generate-keys-random" data-id="{{ $q->id }}"><i class="fa fa-key"></i> Grupos Aleatorias</button></li>
                                                <li class="mr-1"><button type="button" class="btn btn-primary btn-generate-keys" data-id="{{ $q->id }}"><i class="fa fa-key"></i> Grupos con Siembra</button></li>
                                                <li class="mr-1"><button type="button" class="btn btn-primary btn-manual-keys" data-id="{{ $q->id }}"><i class="fa fa-key"></i> Grupos Manuales</button></li>
                                            </ul>
                                        </div>
                                    @elseif(count($Model->partidos->where('torneo_categoria_id', $q->id)->where('estado_id', $App::$ESTADO_FINALIZADO)) <= 0)
                                        <div class="row mt-3">
                                            <ul class="w-100 d-flex align-content-center justify-content-end list-unstyled p-0">
                                                @if(count($Model->torneoJugadors->where('torneo_categoria_id', $q->id)->where('after', true)) && !$q->first_final)
                                                    <li class="mr-2"><button type="button" class="btn btn-primary btn-add-groups" data-id="{{ $q->id }}"><i class="fa fa-users"></i> Agregar grupos</button></li>
                                                @endif
                                                @if($q->first_final)
                                                    <li class="mr-1"><button type="button" class="btn btn-primary btn-generate-keys-final" data-id="{{ $q->id }}" data-reload="1"><i class="fa fa-sync"></i> Volver a generar llaves</button></li>
                                                @endif
                                                <li class="mr-1"><button type="button" class="btn btn-danger btn-delete-keys" data-id="{{ $q->id }}"><i class="fa fa-key"></i> Eliminar keys generados</button></li>
                                                <li class="mr-1"><button type="button" class="btn btn-primary btn-generate-json-grupo" data-id="{{ $q->id }}"><i class="fa fa-key"></i> Generar Json</button></li>
                                            </ul>
                                        </div>
                                    @endif
                                @endif

                                @if(count($Model->partidos->where('torneo_categoria_id', $q->id)->where('estado_id', $App::$ESTADO_FINALIZADO)) > 0 && $Model->torneoGrupos()->where('torneo_categoria_id', $q->id)->count() > 0)
                                    <div class="row mt-3">
                                        <div class="col-md-12 text-right">
                                            <button type="button" class="btn btn-primary btn-generate-json-grupo" data-category="{{ $q->id }}">Generar Json</button>
                                        </div>
                                    </div>
                                @endif

                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <ul class="nav nav-tabs navs-groups" id="custom-tabs-one-tab-{{ $q->id }}" role="tablist">
                                            @foreach($Model->torneoGrupos()->where('torneo_categoria_id', $q->id)->select(['nombre_grupo', 'grupo_id'])->groupBy(['nombre_grupo', 'grupo_id'])->orderBy(DB::raw('LENGTH(nombre_grupo)'))->orderBy('nombre_grupo')->get() as $key4 => $q4)
                                                <li class="nav-item">
                                                    <a class="nav-link {{ $key4 == 0 ? "active" : "" }}" data-category="{{ $q->id }}" data-id="{{ $q4->grupo_id }}" id="custom-tabs-{{ $q->id }}-{{ $q4->grupo_id }}-grupo-tab" data-toggle="pill" href="#custom-tabs-grupo-{{ $q->id }}-{{ $q4->grupo_id }}" role="tab" aria-controls="custom-tabs-grupo-{{ $q->id }}-{{ $q4->grupo_id }}" aria-selected="true">
                                                        <span>{{ $q4->nombre_grupo }}</span>
                                                        <input type="hidden" class="form-control input-sm" data-category="{{ $q->id }}" data-id="{{ $q4->grupo_id }}" value="{{ $q4->nombre_grupo }}" readonly>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="tab-content" id="custom-tabs-one-tabContent-grupo-{{ $q->id }}">
                                            @foreach($Model->torneoGrupos()->where('torneo_categoria_id', $q->id)->select(['nombre_grupo', 'grupo_id'])->groupBy(['nombre_grupo', 'grupo_id'])->orderBy(DB::raw('LENGTH(nombre_grupo)'))->orderBy('nombre_grupo')->get() as $key4 => $q4)
                                                <div class="tab-pane p-2 fade {{ $key4 == 0 ? "show active" : "" }}" id="custom-tabs-grupo-{{ $q->id }}-{{ $q4->grupo_id }}" role="tabpanel" aria-labelledby="custom-tabs-grupo-{{ $q->id }}-{{ $q4->grupo_id }}-tab">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div><h5>Jugadores</h5></div>
                                                    </div>
                                                    <div class="row mt-1">
                                                        <div class="col-md-12">
                                                            <div class="table-responsive">
                                                                <table id="tableCategoria{{ $q->id }}Grupo{{ $q4->grupo_id }}Players" class="table table-bordered table-striped">
                                                                    <thead>
                                                                    <tr>
                                                                        <th>Nombre Completo</th>
                                                                    </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    @foreach($Model->torneoGrupos()->where('torneo_categoria_id', $q->id)->whereHas('jugadorSimple')->where('grupo_id', $q4->grupo_id)->get() as $q5)
                                                                        <tr>
                                                                            <td>{{ $q->multiple ? ($q5->jugadorSimple->nombre_completo." + ".$q5->jugadorDupla->nombre_completo) : $q5->jugadorSimple->nombre_completo }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                                        <div><h5>Partidos Programados</h5></div>
                                                    </div>
                                                    <div class="row mt-2">
                                                        <div class="col-md-12">
                                                            <div class="table-responsive">
                                                                <table id="tableCategoria{{ $q->id }}Grupo{{ $q4->grupo_id }}" class="table table-partidos-score table-bordered table-striped">
                                                                    <thead>
                                                                    <tr>
                                                                        <th class="align-middle text-center" rowspan="2" align="center">Partidos {{ $q4->nombre_grupo }}</th>
                                                                        <th colspan="2" class="align-middle text-center" rowspan="1" align="center">Plazo de Juego</th>
                                                                        <th width="100" rowspan="2" class="align-middle text-center" align="center">Resultado</th>
                                                                        <th colspan="3" class="align-middle text-center" rowspan="1" align="center">Ganador</th>
                                                                        <th colspan="3" class="align-middle text-center" rowspan="1" align="center">Rival</th>
                                                                        @if(count($Model->partidos->where('torneo_categoria_id', $q->id)->where('estado_id', \App\Models\App::$ESTADO_PENDIENTE)->whereNull('fase')) > 0)
                                                                            <!-- Aqui se ponia lo de los th-->
                                                                        @endif
                                                                        <th class="align-middle text-center" rowspan="2"></th>
                                                                    </tr>
                                                                    <tr role="row">
                                                                        <th colspan="1" class="align-middle text-center" align="center">Fecha Inicio</th>
                                                                        <th colspan="1" class="align-middle text-center" align="center">Fecha Final</th>

                                                                        <th colspan="1" class="align-middle text-center" align="center">Jugador</th>
                                                                        <th width="57" colspan="1" class="align-middle text-center" align="center">Sets</th>
                                                                        <th width="57" colspan="1" class="align-middle text-center" align="center">Games</th>

                                                                        <th colspan="1" class="align-middle text-center" align="center">Jugador</th>
                                                                        <th width="57" colspan="1" class="align-middle text-center" align="center">Sets</th>
                                                                        <th width="57" colspan="1" class="align-middle text-center" align="center">Games</th>
                                                                    </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    @foreach($Model->partidos->where('torneo_categoria_id', $q->id)->where('grupo_id', $q4->grupo_id) as $q6)
                                                                        <tr class="content-tr-{{ $q6->id }} {{ $q6->estado_id == $App::$ESTADO_FINALIZADO ? "disable" : "enable" }}" data-id="{{ $q6->id }}" data-category="{{ $q->id }}" data-group="{{ $q4->grupo_id }}">
                                                                            <td class="text-center">{{ $q->multiple ? $q6->jugadorLocalUno->nombre_completo.' + '.$q6->jugadorLocalDos->nombre_completo : $q6->jugadorLocalUno->nombre_completo }} vs {{ $q->multiple ? $q6->jugadorRivalUno->nombre_completo.' + '.$q6->jugadorRivalDos->nombre_completo : $q6->jugadorRivalUno->nombre_completo }}</td>
                                                                            <td><input type="date" value="{{ \Carbon\Carbon::parse($q6->fecha_inicio)->format('Y-m-d') }}" class="form-input" id="fecha_inicio_{{$q6->id}}" name="fecha_inicio_{{$q6->id}}" {{ $q6->estado_id == $App::$ESTADO_FINALIZADO ? "disabled" : "" }}></td>
                                                                            <td><input type="date" value="{{ \Carbon\Carbon::parse($q6->fecha_final)->format('Y-m-d') }}" class="form-input" id="fecha_final_{{$q6->id}}" name="fecha_final_{{$q6->id}}" {{ $q6->estado_id == $App::$ESTADO_FINALIZADO ? "disabled" : "" }}></td>
                                                                            <td width="100"><input value="{{ $q6->resultado }}" type="text" id="resultado_{{$q6->id}}" name="resultado_{{$q6->id}}" class="form-input result-input" {{ $q6->estado_id == $App::$ESTADO_FINALIZADO ? "disabled" : "" }}></td>
                                                                            <td>
                                                                                <select id="jugador_local_id_{{$q6->id}}" name="jugador_local_id_{{$q6->id}}" class="form-input" {{ $q6->estado_id == $App::$ESTADO_FINALIZADO ? "disabled" : "" }}>
                                                                                    <option value="" {{ $q6->estado_id == $App::$ESTADO_PENDIENTE ? "selected" : "" }}> {{ $landing ? "" : "Seleccione" }} </option>
                                                                                    <option value="{{ $q->multiple ? ($q6->jugadorLocalUno->id.'-'.$q6->jugadorLocalDos->id) : $q6->jugadorLocalUno->id }}" {{ $q6->estado_id == $App::$ESTADO_PENDIENTE ? "" : ($q->multiple ? (($q6->jugador_local_uno_id.'-'.$q6->jugador_local_dos_id) == ($q6->jugador_ganador_uno_id.'-'.$q6->jugador_ganador_dos_id) ? "selected" : "") : ($q6->jugador_local_uno_id == $q6->jugador_ganador_uno_id ? "selected" : "")) }}>{{ $q->multiple ? ($q6->jugadorLocalUno->nombre_completo.' + '.$q6->jugadorLocalDos->nombre_completo) : $q6->jugadorLocalUno->nombre_completo }}</option>
                                                                                    <option value="{{ $q->multiple ? ($q6->jugadorRivalUno->id.'-'.$q6->jugadorRivalDos->id) : $q6->jugadorRivalUno->id }}" {{ $q6->estado_id == $App::$ESTADO_PENDIENTE ? "" : ($q->multiple ? (($q6->jugador_rival_uno_id.'-'.$q6->jugador_rival_dos_id) == ($q6->jugador_ganador_uno_id.'-'.$q6->jugador_ganador_dos_id) ? "selected" : "") : ($q6->jugador_rival_uno_id == $q6->jugador_ganador_uno_id ? "selected" : "")) }}>{{ $q->multiple ? ($q6->jugadorRivalUno->nombre_completo.' + '.$q6->jugadorRivalDos->nombre_completo) : $q6->jugadorRivalUno->nombre_completo }}</option>
                                                                                </select>
                                                                            </td>
                                                                            <td width="57"><input value="{{ $q6->jugador_local_set }}" id="jugador_local_set_{{$q6->id}}" name="jugador_local_set_{{$q6->id}}" type="text" class="numeric-set set-local form-input" {{ $q6->estado_id == $App::$ESTADO_FINALIZADO ? "disabled" : "" }}></td>
                                                                            <td width="57"><input value="{{ $q6->jugador_local_juego }}" id="jugador_local_juego_{{$q6->id}}" name="jugador_local_juego_{{$q6->id}}" type="text" class="numeric-game game-local form-input" {{ $q6->estado_id == $App::$ESTADO_FINALIZADO ? "disabled" : "" }}></td>
                                                                            <td>
                                                                                <select id="jugador_rival_id_{{$q6->id}}" name="jugador_rival_id_{{$q6->id}}" class="form-input" {{ $q6->estado_id == $App::$ESTADO_FINALIZADO ? "disabled" : "" }}>
                                                                                    <option value="" {{ trim($q6->resultado) === "-" || $q6->estado_id == $App::$ESTADO_PENDIENTE ? "selected" : "" }}>{{ $landing ? "" : "Seleccione" }}</option>
                                                                                    <option value="{{ $q->multiple ? ($q6->jugadorLocalUno->id.'-'.$q6->jugadorLocalDos->id) : $q6->jugadorLocalUno->id }}" {{ trim($q6->resultado) === "-" || $q6->estado_id == $App::$ESTADO_PENDIENTE ? "" : ($q->multiple ? (($q6->jugador_local_uno_id.'-'.$q6->jugador_local_dos_id) != ($q6->jugador_ganador_uno_id.'-'.$q6->jugador_ganador_dos_id) ? "selected" : "") : ($q6->jugador_local_uno_id != $q6->jugador_ganador_uno_id ? "selected" : "")) }}>{{ $q->multiple ? ($q6->jugadorLocalUno->nombre_completo.' + '.$q6->jugadorLocalDos->nombre_completo) : $q6->jugadorLocalUno->nombre_completo }}</option>
                                                                                    <option value="{{ $q->multiple ? ($q6->jugadorRivalUno->id.'-'.$q6->jugadorRivalDos->id) : $q6->jugadorRivalUno->id }}" {{ trim($q6->resultado) === "-" || $q6->estado_id == $App::$ESTADO_PENDIENTE ? "" : ($q->multiple ? (($q6->jugador_rival_uno_id.'-'.$q6->jugador_rival_dos_id) != ($q6->jugador_ganador_uno_id.'-'.$q6->jugador_ganador_dos_id) ? "selected" : "") : ($q6->jugador_rival_uno_id != $q6->jugador_ganador_uno_id ? "selected" : "")) }}>{{ $q->multiple ? ($q6->jugadorRivalUno->nombre_completo.' + '.$q6->jugadorRivalDos->nombre_completo) : $q6->jugadorRivalUno->nombre_completo }}</option>
                                                                                </select>
                                                                            </td>
                                                                            <td width="57"><input value="{{ $q6->jugador_rival_set }}" id="jugador_rival_set_{{$q6->id}}" name="jugador_rival_set_{{$q6->id}}" type="text" class="numeric-set set-rival form-input" {{ $q6->estado_id == $App::$ESTADO_FINALIZADO ? "disabled" : "" }}></td>
                                                                            <td width="57"><input value="{{ $q6->jugador_rival_juego }}" id="jugador_rival_juego_{{$q6->id}}" name="jugador_rival_juego_{{$q6->id}}" type="text" class="numeric-game game-rival form-input" {{ $q6->estado_id == $App::$ESTADO_FINALIZADO ? "disabled" : "" }}></td>
                                                                            @if(count($Model->partidos->where('torneo_categoria_id', $q->id)->where('estado_id', $App::$ESTADO_PENDIENTE)->whereNull('fase')) > 0)
                                                                                <!-- Aqui se ponia lo de botones-->
                                                                            @endif
                                                                            <td width="50">
                                                                                <div class="btn-group {{ $q6->estado_id == $App::$ESTADO_FINALIZADO ? "" : "hidden" }}">
                                                                                <button type="button" data-id="{{ $q6->id }}" data-category="{{ $q->id }}" data-multiple="{{ $q->multiple }}" data-group="{{ $q4->grupo_id }}" class="btn btn-primary btn-edit-play btn-xs w-100">
                                                                                    Editar
                                                                                </button>
                                                                                    <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown">
                                                                                        <span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                                                                                    </button>
                                                                                    <ul class="dropdown-menu" role="menu">
                                                                                        <li><a href="javascript:void(0);" data-id="{{ $q6->id }}" data-category="{{ $q->id }}" class="dropdown-item btn-generate-json dropdown-item-send">Generar Json</a></li>
                                                                                    </ul>
                                                                                </div>
                                                                                <button type="button" data-id="{{ $q6->id }}" data-category="{{ $q->id }}" data-multiple="{{ $q->multiple }}" data-group="{{ $q4->grupo_id }}"
                                                                                data-local="{{ $q6->jugador_local_uno_id }}" data-local-multiple="{{ $q6->jugador_local_dos_id }}"
                                                                                data-rival="{{ $q6->jugador_rival_uno_id }}" data-rival-multiple="{{ $q6->jugador_rival_dos_id }}" class="btn btn-primary btn-finish-play btn-xs w-100 mt-1 {{ $q6->estado_id == $App::$ESTADO_PENDIENTE ? "" : "hidden" }}">Finalizar</button>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row mt-2">
                                                        <div class="col-md-12">
                                                            <div id="partialViewTablaGrupo{{$q->id}}{{$q4->grupo_id}}"></div>
                                                        </div>
                                                    </div>
                                                    @if(count($Model->partidos->where('torneo_categoria_id', $q->id)->where('grupo_id', $q4->grupo_id)->where('estado_id', $App::$ESTADO_PENDIENTE)->whereNull('fase')) > 0)
                                                        <div class="row mt-2">
                                                            <div class="col-md-12 text-right">
                                                                <button type="button" data-id="{{ $q4->grupo_id }}" data-name="{{ $q4->nombre_grupo }}" data-category="{{ $q->id }}" class="btn btn-primary btn-export-pdf pull-right"><i class="fa fa-file-pdf"></i> Exportar PDF </button>
                                                                <!--<button type="button" data-id="{{ $q4->grupo_id }}" data-name="{{ $q4->nombre_grupo }}" data-category="{{ $q->id }}" class="btn btn-primary btn-finish-all-plays pull-right"><i class="fa fa-save"></i> Finalizar Partidos </button>-->
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="row mt-2">
                                                            <div class="col-md-12 text-right">
                                                                <button type="button" data-id="{{ $q4->grupo_id }}" data-name="{{ $q4->nombre_grupo }}" data-category="{{ $q->id }}" class="btn btn-danger btn-export-pdf pull-right"><i class="fa fa-file-pdf"></i> Exportar PDF </button>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div id="partialViewManual{{$q->id}}"></div>

                                @if($q->first_final)
                                    <div id="mapaCampeonato{{$q->id}}" class="has-map-bg"></div>
                                @endif

                            </div>
                            @if(!$q->first_final)
                                <div class="tab-pane tab-phase-final-content pt-3 fade {{ $TorneoCategoriaId == $q->id && $Fase == 2 ? "show active" : "" }}" id="custom-tabs-fase-two-{{$q->id}}" role="tabpanel" aria-labelledby="custom-tabs-fase-two-tab-{{$q->id}}">
                                    <div>
                                        <div id="partialViewFinal{{$q->id}}"></div>
                                    </div>
                                </div>
                            @endif
                            <div class="tab-pane pt-3 fade {{ $TorneoCategoriaId == $q->id && $Fase == 3 ? "show active" : "" }}" id="custom-tabs-fase-three-{{$q->id}}" role="tabpanel" aria-labelledby="custom-tabs-fase-three-tab-{{$q->id}}">
                                <div>
                                    <div id="partialViewRanking{{$q->id}}"></div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>



<script src="{{ asset('/plugins/sortable/1.15.0/sortable.min.js') }}"></script>
<script type="text/javascript">
    $(function (){
        setTimeout(function (){ $("form").find("input[type=text]").first().focus().select(); }, 500);

        /*$('.result-input').on('change', function (){
            const $this = $(this);
            const $tr = $this.closest('tr');
            const $setsGanador = $tr.find('td:eq(5) > input');
            const $gamesGanador = $tr.find('td:eq(6) > input');
            const $setsPerdedor = $tr.find('td:eq(8) > input');
            const $gamesPerdedor = $tr.find('td:eq(9) > input');

            $this.val()

        });*/

        @if($landing)
            $("#partialView").find("input, select, textarea").prop("disabled", true);
            $("#partialView").find("input, select, textarea").css({
               "border": "0", "background-color": "transparent !important", "text-align": "center"
            });
            $("#partialView").find("button:not(.close-view)").remove();
        @endif

        @if(!$landing)
            $("input.numeric").inputmask("numeric", { min: 1, digits: 0, removeMaskOnSubmit: true, groupSeparator: ",", groupSize: 3, placeholder: "" });
            $("input.numeric-set").inputmask("numeric", { min: 0, max : 3, digits: 0, removeMaskOnSubmit: true, groupSeparator: ",", groupSize: 3, placeholder: "" });
            $("input.numeric-game").inputmask("numeric", { min: 0, max : 99, digits: 0, removeMaskOnSubmit: true, groupSeparator: ",", groupSize: 3, placeholder: "" });

            var jsonString = null;

            $('ol.serialization').sortable({
                swapThreshold: 0.26,
                animation: 150,
                group: 'serialization',
                update: function (e){
                    jsonString = $('ol.serialization').sortable('toArray', {attribute: "data-id"});
                    refreshOrder();
                }
            });
        @endif

        $(document).mouseup(function(e) {
            const container = $("ul.navs-groups .nav-item .nav-link.active");
            if (!container.is(e.target) && container.has(e.target).length === 0){
                $("ul.navs-groups .nav-item .nav-link").each(function (i, v){
                    $(v).find("span").removeClass("hidden");
                    $(v).find("input").attr("type", "hidden").prop("readonly", true);
                });
            }
        });

        const $gruposActivos = $("ul.navs-groups .nav-item .nav-link.active");
        $gruposActivos.each(function(i, v){
            refrescarTablaPosiciones($(v).attr("data-category"), $(v).attr("data-id"));
        })

        $("ul.navs-groups .nav-item .nav-link").on("click", function (){
            const $this = $(this);
            refrescarTablaPosiciones($this.attr("data-category"), $this.attr("data-id"));
        });

        function refrescarTablaPosiciones($torneo_categoria, $grupo)
        {
            invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/tabla/partialView/{{ $Model->id }}/${$torneo_categoria}/${$grupo}/{{ $landing }}`, function(data){
                $(`#partialViewTablaGrupo${$torneo_categoria}${$grupo}`).html("").append(data);
            });
        }

        $("ul.navs-groups .nav-item .nav-link").on("dblclick", function (){
           const $this = $(this);
           $this.find("span").addClass("hidden");
           $this.find("input").attr("type", "text").prop("readonly", false).select();
        });

        $("ul.navs-groups .nav-item .nav-link input").on("change", function (){
            changeNameOrder($(this));
        });

        $(".btn-generate-json-players").on("click", function (){
            const $this = $(this);
            const $category_id = $this.attr('data-category');
            window.open(`/auth/{{strtolower($ViewName)}}/jugador/export/json?torneo={{ $Model->id  }}&categoria=${$category_id}`);
        });

        $(".btn-generate-json-grupo").on("click", function (){
            const $this = $(this);
            const $category_id = $this.attr('data-category');
            window.open(`/auth/{{strtolower($ViewName)}}/grupos/export/json?torneo={{ $Model->id  }}&categoria=${$category_id}`);
        });

        $("input.result-input").on("change", function (){
           const $this = $(this);
            if(["-"].includes($this.val())){
                $this.closest("tr").find("input.set-local").val(0);
                $this.closest("tr").find("input.game-local").val(0);
                $this.closest("tr").find("input.set-rival").val(0);
                $this.closest("tr").find("input.game-rival").val(0);
                $this.closest("tr").find("select").val("");
            }else if(["wo", "w.o", "WO", "W.O"].includes($this.val())) {
                $this.closest("tr").find("input.set-local").val(2);
                $this.closest("tr").find("input.game-local").val(12);
                $this.closest("tr").find("input.set-rival").val(0);
                $this.closest("tr").find("input.game-rival").val(0);
            }else if(["0"].includes($this.val())){
                const formData = new FormData();
                formData.append('_token', $("meta[name=csrf-token]").attr("content"));
                formData.append("torneo_id", {{ $Model->id }});
                formData.append("partido_id", $this.closest("tr").attr("data-id"));
                actionAjax(`/auth/{{strtolower($ViewName)}}/partido/reset`, formData, 'POST', function (data){
                    if(data.Success){
                        $this.closest("tr").find("select").val("");
                        $this.val("");
                        $this.closest("tr").find("input.set-local").val("");
                        $this.closest("tr").find("input.game-local").val("");
                        $this.closest("tr").find("input.set-rival").val("");
                        $this.closest("tr").find("input.game-rival").val("");
                        refrescarTablaPosiciones($this.closest("tr").attr("data-category"), $this.closest("tr").attr("data-group"));
                    }
                });
            }else{
                const sets = $this.val().split('/');
                if(sets.length > 0){
                    let setsLocal = 0; let gamesLocal = 0; let setsRival = 0; let gamesRival = 0;
                    $.each(sets, function (i, v){
                        const games = v.split('-');
                        const $GameLeft = parseInt(games[0].match(/\d+/)[0]);
                        const $GameRight = parseInt(games[1].match(/\d+/)[0]);
                        if(i <= 1){
                            gamesLocal += $GameLeft;
                            gamesRival += $GameRight;
                        }
                        if($GameLeft > $GameRight) setsLocal+=1;
                        else if($GameRight > $GameLeft)  setsRival+=1;
                    });
                    $this.closest("tr").find("input.set-local").val(setsLocal);
                    $this.closest("tr").find("input.game-local").val(gamesLocal);
                    $this.closest("tr").find("input.set-rival").val(setsRival);
                    $this.closest("tr").find("input.game-rival").val(gamesRival);
                }
            }
        });

        function changeNameOrder($this){
            const formData = new FormData();
            formData.append('_token', $("meta[name=csrf-token]").attr("content"));
            formData.append("torneo_id", {{ $Model->id }});
            formData.append("grupo_id", $this.attr("data-id"));
            formData.append("torneo_category_id ", $this.attr("data-category"));
            formData.append("value", $this.val());
            actionAjax(`/auth/torneo/grupo/cambiarNombre`, formData, 'POST', function (data){
                if(data.Success){
                    Toast.fire({icon: 'success', title: 'Proceso realizado Correctamente'});
                    $this.closest(".nav-link").find("span").text($this.val()).removeClass("hidden");
                    $this.closest(".nav-link").find("input").attr("type", "hidden").prop("readonly", true);
                }
            });
        }

        function refreshOrder()
        {
            const formData = new FormData();
            formData.append('_token', $("meta[name=csrf-token]").attr("content"));
            formData.append("id", {{ $Model->id }});
            formData.append("jsonString", JSON.stringify(jsonString));
            actionAjax(`/auth/torneo/categoria/cambiarOrdenStore`, formData, 'POST', function (data){
                if(data.Success){
                    invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $Model->id }}`, function(data){
                        $("#main").addClass("hidden");$("#info").removeClass("hidden").html("").append(data);
                    });
                }
            });
        }

        const $btnEstablecerOrdenCategorias = $("#btnEstablecerOrdenCategorias");
        $btnEstablecerOrdenCategorias.on("click", function (){
            const $this = $(this);
            const id = $this.attr("data-id");
            invocarModal(`/auth/{{strtolower($ViewName)}}/categoria/cambiarOrdenPartialView/${id ? id : 0}`, function ($modal) {
                if ($modal.attr("data-reload") === "true"){
                    invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $Model->id }}`, function(data){
                        $("#main").addClass("hidden");$("#info").removeClass("hidden").html("").append(data);
                    });
                }
            });
        });

        const $btnPlayers = $("button.btn-players");
        $btnPlayers.on("click", function (){
            const $this = $(this);
            const id = $this.attr("data-id");
            invocarModal(`/auth/{{strtolower($ViewName)}}/jugador/partialView/${id ? id : 0}`, function ($modal) {
                if ($modal.attr("data-reload") === "true"){
                    invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $Model->id }}/${id}`, function(data){
                        $("#main").addClass("hidden");$("#info").removeClass("hidden").html("").append(data);
                    });
                }
            });
        });

        const $btnDeletePayer = $("button.btn-delete-player");
        $btnDeletePayer.on("click", function (){
            const $this = $(this);
            const id = $this.attr("data-id");
            const torneo_category_id = $this.attr("data-category");
            const name = $this.attr("data-info");
            const category_name = $this.attr("data-category-name");
            const formData = new FormData();
            formData.append('_token', $("meta[name=csrf-token]").attr("content"));
            formData.append('torneo_id', {{ $Model->id }});
            formData.append('torneo_category_id', torneo_category_id);
            formData.append('id', id);
            confirmAjax(`/auth/{{strtolower($ViewName)}}/jugador/delete`, formData, `POST`,
                `¿Está seguro de eliminar al jugador ${name} de la categoría ${category_name} ?`, null, function (data){
                    if(data.Success){
                        invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $Model->id }}/${torneo_category_id}`, function(data){
                            $("#main").addClass("hidden");$("#info").removeClass("hidden").html("").append(data);
                        });
                    }else Toast.fire({icon: 'error', title: 'Algo salió mal, hubo un error al guardar.'});
                });
        });

        const $btnDeletePayers = $("button.btn-delete-players");
        $btnDeletePayers.on("click", function (){
            const $this = $(this);
            const torneo_category_id = $this.attr("data-id");
            const category_name = $this.attr("data-category-name");
            const formData = new FormData();
            formData.append('_token', $("meta[name=csrf-token]").attr("content"));
            formData.append('torneo_id', {{ $Model->id }});
            formData.append('torneo_category_id', torneo_category_id);
            confirmAjax(`/auth/{{strtolower($ViewName)}}/jugador/delete/masivo`, formData, `POST`,
                `¿Está seguro de eliminar los jugadores asignados de la categoría ${category_name} ?`, null, function (data){
                    if(data.Success){
                        invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $Model->id }}/${torneo_category_id}`, function(data){
                            $("#main").addClass("hidden");$("#info").removeClass("hidden").html("").append(data);
                        });
                    }else Toast.fire({icon: 'error', title: 'Algo salió mal, hubo un error al guardar.'});
                });
        });

        const $btnChangePlayer = $("button.btn-change-player");
        $btnChangePlayer.on("click", function(){
            const $this = $(this);
            const id = $this.attr("data-id");
            const torneo_category_id = $this.attr("data-category")
            invocarModal(`/auth/{{strtolower($ViewName)}}/jugador/partialViewChange/{{ $Model->id }}/${torneo_category_id ? torneo_category_id : 0}/${id ? id : 0}`, function ($modal) {
                if ($modal.attr("data-reload") === "true") {
                    invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $Model->id }}/${torneo_category_id}`, function(data){
                        $("#main").addClass("hidden");$("#info").removeClass("hidden").html("").append(data);
                    });
                }
            });
        });

        const $inputsAction = $("div.inputs-action");
        $inputsAction.on("change", "select, input", function (){
            const $this = $(this);
            const torneo_category_id = $this.parents("div.inputs-action").attr("data-id");
            const formData = new FormData();
            formData.append('_token', $("meta[name=csrf-token]").attr("content"));
            formData.append('torneo_id', {{ $Model->id }});
            formData.append('torneo_categoria_id', torneo_category_id);
            formData.append('clasificados', $("#clasificados_"+torneo_category_id).val());
            formData.append('clasificados_terceros', isNaN(parseInt($("#clasificados_terceros_"+torneo_category_id).val())) ? "0" : $("#clasificados_terceros_"+torneo_category_id).val());
            actionAjax(`/auth/{{strtolower($ViewName)}}/categoria/store`, formData, `POST`, function (data){
                if(data.Success){
                    invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $Model->id }}/${torneo_category_id}`, function(data){
                        $("#main").addClass("hidden");$("#info").removeClass("hidden").html("").append(data);
                    });
                }else{
                    $this.parents("div.inputs-action").find("input[type=text]").val("0"); Toast.fire({icon: 'error', title: data.Message ? data.Message : 'Algo salió mal, hubo un error al guardar.'})
                }
            });
        });

        const $btnGenerateKeys = $(".btn-generate-keys"), $btnGenerateKeysRandom = $(".btn-generate-keys-random"),
        $btnManualKeys = $(".btn-manual-keys"), $btnDeleteKeys = $(".btn-delete-keys");

        $btnGenerateKeys.on("click", function (){
            const $this = $(this);
            const id = $this.attr("data-id");
            const $table = $(`table.table-players-${id}`);
            if($table.find("tbody tr").length > 0) {
                if ($table.find("tbody tr").length >= 8 && $table.find("tbody tr").length <= 64) {
                    const formData = new FormData();
                    formData.append('_token', $("meta[name=csrf-token]").attr("content"));
                    formData.append('torneo_id', {{ $Model->id }});
                    formData.append('categoria_id', id);
                    actionAjax(`/auth/{{strtolower($ViewName)}}/grupo/validacionGrupo`, formData, 'POST', function (data) {
                        if (data.Success) {
                            Swal.fire({
                                icon: 'question', title: "Confirmación",
                                html: "<label for='tipo_grupo' style='font-weight: 400'>¿Cómo desea genear los grupos?</label>" +
                                "<select id='tipo_grupo' class='form-control'>" +
                                  "<option value='1'>Letras</option>" +
                                  "<option value='2'>Números</option>" +
                                "</select>",
                                confirmButtonColor: '#d33', confirmButtonText: 'Si, Confirmar', cancelButtonText: 'Cancelar',
                                showCancelButton: true, closeOnConfirm: false, showLoaderOnConfirm: true
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    invocarModal(`/auth/{{strtolower($ViewName)}}/grupo/partialView/{{ $Model->id }}/${id ? id : 0}/${$("#tipo_grupo").val()}`, function ($modal) {
                                        if ($modal.attr("data-reload") === "true") {
                                            invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $Model->id }}/${id}`, function (data) {
                                                $("#main").addClass("hidden");
                                                $("#info").removeClass("hidden").html("").append(data);
                                            });
                                        }
                                    });
                                }
                            });
                        }else{
                            Toast.fire({icon: 'error', title: data.Message});
                        }
                    });

                } else {
                    if ($table.find("tbody tr").length < 8) Toast.fire({icon: 'error', title: `Por favor, registre al menos ${(8 - $table.find("tbody tr").length)} jugadores más para generar las llaves`});
                    else if ($table.find("tbody tr").length > 64) Toast.fire({icon: 'error', title: `Por favor, solo puede registrar como máximo 64 jugadores para generar las llaves`});
                }
            }else Toast.fire({icon: 'error', title: 'No existen jugadores disponibles para generar las llaves'});
        });

        $btnGenerateKeysRandom.on("click", function (){
            const $this = $(this);
            const id = $this.attr("data-id");
            const $table = $(`table.table-players-${id}`);
            if($table.find("tbody tr").length > 0){
                Swal.fire({
                    icon: 'question', title: "Confirmación",
                    html: `<label for='tipo_grupo' style='font-weight: 400'>¿Está seguro de generar las llaves de manera aleatoria para los ${$table.find("tbody tr").length} jugadores ?. ¿Cómo desea genear los grupos?</label>` +
                        "<select id='tipo_grupo' class='form-control'>" +
                        "<option value='1'>Letras</option>" +
                        "<option value='2'>Números</option>" +
                        "</select>",
                    confirmButtonColor: '#d33', confirmButtonText: 'Si, Confirmar', cancelButtonText: 'Cancelar',
                    showCancelButton: true, closeOnConfirm: false, showLoaderOnConfirm: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('_token', $("meta[name=csrf-token]").attr("content"));
                        formData.append('torneo_id', {{ $Model->id }});
                        formData.append('torneo_categoria_id', id);
                        formData.append('tipo', 'random');
                        formData.append('tipo_grupo_id', $("#tipo_grupo").val());
                        actionAjax(`/auth/{{strtolower($ViewName)}}/grupo/store`, formData, `POST`, function (data){
                            if(data.Success){
                                if(data.Repeat){
                                    Swal.fire({icon: 'warning', title: 'Algunos jugadores que acaba de asignar ya se enfrentaron con anterioridad en el torneo anterior en la fase de grupos.'});
                                }
                                invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $Model->id }}/${id}`, function(data){
                                    $("#main").addClass("hidden");$("#info").removeClass("hidden").html("").append(data);
                                });
                            }else Toast.fire({icon: 'error', title: 'Algo salió mal, hubo un error al guardar.'});
                        }, true);



                        /*confirmAjax(`/auth/{{strtolower($ViewName)}}/grupo/store`, formData, `POST`,
                        `¿Está seguro de generar las llaves de manera aleatoria para los ${$table.find("tbody tr").length} jugadores ?`, null, function (data){

                        });*/
                    }
                });
            }else Toast.fire({icon: 'error', title: 'No existen jugadores disponibles para generar las llaves'});
        });

        $btnManualKeys.on("click", function (){
            const $this = $(this);
            const id = $this.attr("data-id");
            const $table = $(`table.table-players-${id}`);
            if($table.find("tbody tr").length > 0) {
                if ($table.find("tbody tr").length >= 8 && $table.find("tbody tr").length <= 64) { //72
                    const formData = new FormData();
                    formData.append('_token', $("meta[name=csrf-token]").attr("content"));
                    formData.append('torneo_id', {{ $Model->id }});
                    formData.append('categoria_id', id);
                    actionAjax(`/auth/{{strtolower($ViewName)}}/grupo/validacionGrupo`, formData, 'POST', function (data){
                        if(data.Success){
                            Swal.fire({
                                icon: 'question', title: "Confirmación",
                                html: "<label for='tipo_grupo' style='font-weight: 400'>¿Cómo desea genear los grupos?</label>" +
                                    "<select id='tipo_grupo' class='form-control'>" +
                                    "<option value='1'>Letras</option>" +
                                    "<option value='2'>Números</option>" +
                                    "</select>",
                                confirmButtonColor: '#d33', confirmButtonText: 'Si, Confirmar', cancelButtonText: 'Cancelar',
                                showCancelButton: true, closeOnConfirm: false, showLoaderOnConfirm: true
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/manual/partialView/{{ $Model->id }}/${id ? id : 0}/${$("#tipo_grupo").val()}`, function (data){
                                        $("#partialViewManual"+$this.attr("data-id")).html("").append(data);
                                    });
                                }
                            });
                        }else{
                            Toast.fire({icon: 'error', title: data.Message});
                        }
                    });
                } else {
                    if ($table.find("tbody tr").length < 8) Toast.fire({icon: 'error', title: `Por favor, registre al menos ${(8 - $table.find("tbody tr").length)} jugadores más para generar las llaves`});
                    else if ($table.find("tbody tr").length > 64) Toast.fire({icon: 'error', title: `Por favor, solo puede registrar como máximo 64 jugadores para generar las llaves`});
                }
            }else Toast.fire({icon: 'error', title: 'No existen jugadores disponibles para generar las llaves'});
        });

        $btnDeleteKeys.on("click", function (){
            const $this = $(this);
            const id = $this.attr("data-id");
            const formData = new FormData();
            formData.append('_token', $("meta[name=csrf-token]").attr("content"));
            formData.append('torneo_id', {{ $Model->id }});
            formData.append('torneo_categoria_id', id);
            confirmAjax(`/auth/{{strtolower($ViewName)}}/grupo/delete`, formData, `POST`, `¿Está seguro de eliminar las llaves generadas ?`, null, function (data){
                if(data.Success){
                    invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $Model->id }}/${id}`, function(data){
                        $("#main").addClass("hidden");$("#info").removeClass("hidden").html("").append(data);
                    });
                }else Toast.fire({icon: 'error', title: 'Algo salió mal, hubo un error al guardar.'});
            });
        });

        const $btnAddGroups = $(".btn-add-groups")
        $btnAddGroups.on("click", function (){
            const $this = $(this);
            const id = $this.attr("data-id");
            const $table = $(`table.table-players-${id}`);
            if($table.find("tbody tr").length > 0) {
                if ($table.find("tbody tr").length >= 8 && $table.find("tbody tr").length <= 64) {
                    const formData = new FormData();
                    formData.append('_token', $("meta[name=csrf-token]").attr("content"));
                    formData.append('torneo_id', {{ $Model->id }});
                    formData.append('categoria_id', id);
                    actionAjax(`/auth/{{strtolower($ViewName)}}/grupo/validacionGrupo`, formData, 'POST', function (data){
                        if(data.Success){
                            invocarModal(`/auth/{{strtolower($ViewName)}}/grupos/agregar/partialView/{{ $Model->id }}/${id ? id : 0}`, function ($modal) {
                                if ($modal.attr("data-reload") === "true") {
                                    invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $Model->id }}/${id}`, function (data) {
                                        $("#main").addClass("hidden");
                                        $("#info").removeClass("hidden").html("").append(data);
                                    });
                                }
                            });
                        }else{
                            Toast.fire({icon: 'error', title: data.Message});
                        }
                    });
                } else {
                    if ($table.find("tbody tr").length < 8) Toast.fire({icon: 'error', title: `Por favor, registre al menos ${(8 - $table.find("tbody tr").length)} jugadores más para generar las llaves`});
                    else if ($table.find("tbody tr").length > 64) Toast.fire({icon: 'error', title: `Por favor, solo puede registrar como máximo 64 jugadores para generar las llaves`});
                }
            }else Toast.fire({icon: 'error', title: 'No existen jugadores disponibles para generar las llaves'});
        });

        const $btnFinishAllPlays = $(".btn-finish-all-plays");
        $btnFinishAllPlays.on("click", function () {
            const $this = $(this); const $arrayPartidos = [];
            const $table = $(`#tableCategoria${$this.attr("data-category")}Grupo${$this.attr("data-id")}`);
            $table.find("tbody tr.enable").each(function (i, v){
                const id = $(v).attr("data-id");
                const objPartido = {};
                objPartido.partido_id = id;
                objPartido.players = $(v).find("td:eq(0)")[0].innerText;
                objPartido.torneo_id = {{ $Model->id }};
                objPartido.torneo_categoria_id = $this.attr("data-category");
                objPartido.fecha_inicio = $("#fecha_inicio_"+id).val();
                objPartido.fecha_final = $("#fecha_final_"+id).val();
                objPartido.resultado = $("#resultado_"+id).val();
                objPartido.jugador_local_id = $("#jugador_local_id_"+id).val();
                objPartido.jugador_local_set = $("#jugador_local_set_"+id).val();
                objPartido.jugador_local_juego = $("#jugador_local_juego_"+id).val();
                objPartido.jugador_rival_id = $("#jugador_rival_id_"+id).val();
                objPartido.jugador_rival_set = $("#jugador_rival_set_"+id).val();
                objPartido.jugador_rival_juego = $("#jugador_rival_juego_"+id).val();
                $arrayPartidos.push(objPartido);
            });
            const formData = new FormData();
            formData.append('_token', $("meta[name=csrf-token]").attr("content"));
            formData.append('torneo_id', {{ $Model->id }});
            formData.append('grupo_id', $this.attr("data-id"));
            formData.append('torneo_categoria_id', $this.attr("data-category"));
            formData.append('partidos', JSON.stringify($arrayPartidos));
            confirmAjax(`/auth/{{strtolower($ViewName)}}/partido/storeMultiple`, formData, `POST`, `¿Está seguro de finalizar los partidos del ${$this.attr("data-name")} ?`, null, function (data){
                if(data.Success){
                    invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $Model->id }}/${$this.attr("data-category")}`, function(data){
                        $("#main").addClass("hidden");$("#info").removeClass("hidden").html("").append(data);
                    });
                }else Toast.fire({icon: 'error', title: 'Algo salió mal, hubo un error al guardar.'});
            }, function (data){
                if (data.Errors) {
                    var html = `<ul style='padding:0;text-align:left;overflow-y: scroll;height: 270px;display: grid;align-items: center;'>`;
                    $.each(data.Errors, function (i, v){
                        html += `<li style='margin-top:10px'> Error en la fila ${v.key}: ${v.Message} <ul style='text-align:left;margin-top:10px'>`;
                        $.each(v.error, function (i2, v2){html +=  `<li class='mtb-5'>${v2.error}</li>`;});
                        html += `</ul></li>`;
                    });
                    html += `</ul>`;
                    if(data.Updates > 0){
                        invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $Model->id }}/${$this.attr("data-category")}`, function(data){
                            $("#main").addClass("hidden");$("#info").removeClass("hidden").html("").append(data);
                        });
                    }
                    Swal.fire({title: data.Updates === 0 ? ('Error al finalizar los partidos del ' + $this.attr("data-name")) : ' Se ' + (data.Updates === 1 ? 'finalizó ' : 'finalizarón ') + data.Updates + (data.Updates === 1 ? ' partido' : ' partidos') + ', excepto ',icon: data.Updates === 0 ? 'error' : 'warning',html: html, confirmButtonColor: '#3085d6'});
                }else{
                    Toast.fire({icon: 'error', title: data.Message != null ? data.Message: "Algo salió mal, por favor verifique los campos ingresados."});
                }
            });
        });

        const $btnExportPdf = $(".btn-export-pdf");
        $btnExportPdf.on("click", function (){
            const $this = $(this);
            window.open(`/auth/reporte/{{strtolower($ViewName)}}/exportar/pdf/{{ $Model->id }}/${$this.attr("data-category")}`, '_blank');
        });

        const $closeView = $("button.close-view");
        $closeView.on("click", function (){ $("body").removeClass("sidebar-collapse"); $("#main").removeClass("hidden");$("#info").html("").addClass("hidden");});

        const $btnFinishPlay = $("button.btn-finish-play");
        $btnFinishPlay.on("click", function (){
            const $this = $(this);
            const id = $this.attr("data-id");
            const formData = new FormData();
            formData.append('_token', $("meta[name=csrf-token]").attr("content"));
            formData.append('torneo_id', {{ $Model->id }});
            formData.append('torneo_categoria_id', $this.attr("data-category"));
            formData.append('partido_id', id);
            formData.append('fecha_inicio', $("#fecha_inicio_"+id).val());
            formData.append('fecha_final', $("#fecha_final_"+id).val());
            formData.append('resultado', $("#resultado_"+id).val());
            formData.append('jugador_local_id', $("#jugador_local_id_"+id).val());
            formData.append('jugador_local_set', $("#jugador_local_set_"+id).val());
            formData.append('jugador_local_juego', $("#jugador_local_juego_"+id).val());
            formData.append('jugador_rival_id', $("#jugador_rival_id_"+id).val());
            formData.append('jugador_rival_set', $("#jugador_rival_set_"+id).val());
            formData.append('jugador_rival_juego', $("#jugador_rival_juego_"+id).val());
            formData.append('fase_inicial', '1');
            actionAjax(`/auth/{{strtolower($ViewName)}}/partido/store`, formData, `POST`, function (data){
                if(data.Success){
                    Toast.fire({icon: data.Message ? 'warning' : 'success', title: data.Message ? data.Message : 'Proceso realizado Correctamente'});
                    $this.addClass("hidden");
                    $this.closest("td").find("button.btn-edit-play").closest('div.btn-group').removeClass("hidden");
                    $this.closest("tr").find("input, select").prop("disabled", true);
                    $this.closest("tr").addClass("disable").removeClass("enable");

                    const $idLocal = parseInt($this.attr("data-multiple")) === 0 ? [$this.attr("data-local")] : [$this.attr("data-local"), $this.attr("data-local-multiple")];
                    const $idRival = parseInt($this.attr("data-multiple")) === 0 ? [$this.attr("data-rival")] : [$this.attr("data-rival"), $this.attr("data-rival-multiple")];

                    $("button#btnChangePlayer_"+$this.attr("data-category")+"_"+$idLocal[0]+"_"+($idLocal.length > 1 ? $idLocal[1] : "")).remove();
                    $("button#btnChangePlayer_"+$this.attr("data-category")+"_"+$idRival[0]+"_"+($idRival.length > 1 ? $idRival[1] : "")).remove();

                    if(["-"].includes($("#resultado_"+id).val())){
                        $this.closest("tr").find("select").val("");
                    }

                    refrescarTablaPosiciones($this.attr("data-category"), $this.attr("data-group"));
                }else{
                    if (data.Errors) {
                        const $arregloErros = [];
                        $.each(data.Errors, function (i, v){$arregloErros.push(v);});
                        if($arregloErros.length > 0) Toast.fire({icon: 'error', title: $arregloErros[0]});
                        else Toast.fire({icon: 'error', title: data.Message != null ? data.Message: "Algo salió mal, por favor verifique los campos ingresados."});
                    }else{
                        Toast.fire({icon: 'error', title: data.Message != null ? data.Message: "Algo salió mal, por favor verifique los campos ingresados."});
                    }
                }
            });

            /*confirmAjax(`/auth/{{strtolower($ViewName)}}/partido/store`, formData, `POST`, `¿Está seguro de finalizar este partido ?`, null, function (data){
                if(data.Success){
                    invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $Model->id }}/${$this.attr("data-category")}`, function(data){
                        $("#main").addClass("hidden");$("#info").removeClass("hidden").html("").append(data);
                    });
                }else Toast.fire({icon: 'error', title: 'Algo salió mal, hubo un error al guardar.'});
            }, function (data){
                if (data.Errors) {
                    const $arregloErros = [];
                    $.each(data.Errors, function (i, v){$arregloErros.push(v);});
                    if($arregloErros.length > 0) Toast.fire({icon: 'error', title: $arregloErros[0]});
                    else Toast.fire({icon: 'error', title: data.Message != null ? data.Message: "Algo salió mal, por favor verifique los campos ingresados."});
                }else{
                    Toast.fire({icon: 'error', title: data.Message != null ? data.Message: "Algo salió mal, por favor verifique los campos ingresados."});
                }
            });*/


        });

        const $btnEditPlay = $("button.btn-edit-play");
        $btnEditPlay.on("click", function (){
            const $this = $(this);
            const $tr = $("tr.content-tr-"+$this.attr("data-id"));
            $tr.removeClass("disable").addClass("enable");
            $tr.find("td input, td select").prop("disabled", false);
            $tr.find("td button.btn-finish-play").removeClass("hidden");
            $this.closest('div.btn-group').addClass("hidden");
        });

        const $btnGenerateJson = $(".btn-generate-json");
        $btnGenerateJson.on("click", function (){
            const $this = $(this);
            window.open(`/auth/{{strtolower($ViewName)}}/partido/export/json?id=${$this.attr("data-id")}`);
        });

        const $btnPhaseFinal = $("a.nav-link.tab-phase-final");
        $btnPhaseFinal.on("click", function (){
            const $this = $(this);
            const $toneo_category = $this.attr("data-id");
            const $partialView = $("#partialViewFinal"+$toneo_category);
            invocarVista(`/auth/{{strtolower($ViewName)}}/fase-final-final/{{ $Model->id }}/${$toneo_category ? $toneo_category : 0}/{{ $landing }}`, function(data){
                $partialView.html("").append(data);
            });
        });

        const $btnRankings = $("a.nav-link.tab-ranking");
        $btnRankings.on("click", function (){
            const $this = $(this);
            const $toneo_category = $this.attr("data-id");
            const $partialView = $("#partialViewRanking"+$toneo_category);
            invocarVista(`/auth/{{strtolower($ViewName)}}/ranking/{{ $Model->id }}/${$toneo_category ? $toneo_category : 0}/{{ $landing }}`, function(data){
                $partialView.html("").append(data);
            });
        });

        const $customTabs = $("#custom-tabs-one-tab");
        const $toneoCategory = $customTabs.find("li.nav-item:first-child > a.nav-link");
        const $toneoCategoryId = $toneoCategory.attr('data-id');
        const $isRanking = $toneoCategory.attr('data-ranking')
        if(parseInt($isRanking) === 1) {
            invocarVista(`/auth/{{strtolower($ViewName)}}/ranking/{{ $Model->id }}/${$toneoCategoryId ? $toneoCategoryId : 0}/{{ $landing }}`, function(data){
                $("#partialViewRanking"+$toneoCategoryId).html("").append(data);
            });
        }

        const $btnPhaseCategory = $("a.nav-link.tab-category");
        $btnPhaseCategory.on("click", function (){
            const $this = $(this);
            const $toneo_category = $this.attr("data-id");
            const $first_final = $this.attr("data-mapa");
            const $ranking = $this.attr("data-ranking");
            if(parseInt($first_final) === 1) refrescarMapaFaseOne($toneo_category);
            else{
                if(parseInt($ranking) === 1){
                    const $partialView = $("#partialViewRanking"+$toneo_category);
                    invocarVista(`/auth/{{strtolower($ViewName)}}/ranking/{{ $Model->id }}/${$toneo_category ? $toneo_category : 0}/{{ $landing }}`, function(data){
                        $partialView.html("").append(data);
                    });
                }else{
                    invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $Model->id }}/${$toneo_category}/0/{{ $landing }}`, function(data){
                        $("#main").addClass("hidden");
                        $("#info").removeClass("hidden").html("").append(data);
                    });
                }
            }
        });

        const $tabCategoryActive = $("a.nav-link.tab-category.active");
        if(parseInt($tabCategoryActive.attr("data-mapa")) === 1) refrescarMapaFaseOne($tabCategoryActive.attr("data-id"));

        const $btnGenerateKeysFinal = $(".btn-generate-keys-final");
        $btnGenerateKeysFinal.on("click", function (){
            const $this = $(this);
            const reload = parseInt($this.attr("data-reload"));
            const torneo_category_id = $this.attr("data-id");
            const formData = new FormData();
            formData.append('_token', $("meta[name=csrf-token]").attr("content"));
            formData.append('torneo_id', {{ $Model->id }});
            formData.append('torneo_categoria_id', torneo_category_id);
            formData.append('tipo', 'manual');
            formData.append('reload', reload);
            confirmAjax(`/auth/{{strtolower($ViewName)}}/fase-final-first/store`, formData, `POST`,
                (reload === 1 ? `¿Está seguro de volver a generar las llaves de la etapa final?. Ten en cuenta que los datos guardados previamente serán borrados` :
                    `¿Está seguro de generar las llaves de la etapa final?`), null, function (data){
                    if(data.Success){
                        if(reload === 1){
                            refrescarMapaFaseOne(torneo_category_id);
                        }else{
                            invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $Model->id }}/${torneo_category_id}`, function(data){
                                $("#main").addClass("hidden");$("#info").removeClass("hidden").html("").append(data);
                            });
                        }
                    }else Toast.fire({icon: 'error', title: 'Algo salió mal, hubo un error al guardar.'});
                });
        });

        const $firstPanel = $(".first-pane ");

        $firstPanel.on("click", ".btn-export-pdf-cup", function (){
            const $this = $(this);
            $("#textCategoria").text(', Categoría "'+$this.attr("data-category")+'"');
            $(".has-map-bg").removeClass("is-half-left").removeClass("is-half-right");
            $(".grid.grid-mapa-content").removeClass("is-half-left").removeClass("is-half-right");
            window.print();
        });

        $firstPanel.on("click", ".btn-download-cup", function (){
            const $this = $(this);
            window.open(`/auth/{{strtolower($ViewName)}}/export/mapa/json?type=full&torneo={{ $Model->id  }}&categoria=${$this.attr("data-id")}`);
        });

        $firstPanel.on("click", ".btn-export-pdf-cup-left", function (){
            const $this = $(this);
            $("#textCategoria").text(', Categoría "'+$this.attr("data-category")+'"');
            $(".has-map-bg").removeClass("is-half-right").addClass("is-half-left");
            $(".grid.grid-mapa-content").removeClass("is-half-right").addClass("is-half-left");
            window.print();
        });

        $firstPanel.on("click", ".btn-export-pdf-cup-right", function (){
            const $this = $(this);
            $("#textCategoria").text(', Categoría "'+$this.attr("data-category")+'"');
            $(".has-map-bg").removeClass("is-half-left").addClass("is-half-right");
            $(".grid.grid-mapa-content").removeClass("is-half-left").addClass("is-half-right");
            window.print();
        });

        $firstPanel.on("click", ".btn-finish-keys-final", function (){
            const $this = $(this);
            const formData = new FormData();
            formData.append('_token', $("meta[name=csrf-token]").attr("content"));
            formData.append('torneo_id', {{ $Model->id }});
            formData.append('torneo_categoria_id', $this.attr("data-id"));
            confirmAjax(`/auth/{{strtolower($ViewName)}}/fase-final/prepartido/finish`, formData, `POST`, `¿Está seguro de finalizar las llaves generadas ?`, null, function (data){
                if(data.Success){
                    refrescarMapaFaseOne($this.attr("data-id"));
                }
            });
        });

        @if(!$landing)
            $firstPanel.on("click", "table.table-game", function (){
                const $this = $(this);
                const id = $this.attr("data-id");
                const position = $this.attr("data-position");
                const bracket =  $this.attr("data-bracket");
                const category =  $this.attr("data-category");
                const manual =  $this.attr("data-manual");
                if(parseInt(manual) === 1){
                    invocarModal(`/auth/{{strtolower($ViewName)}}/fase-final/prepartido/partialView/{{$Model->id}}/${category}/${id ? id : 0}/${position}/${bracket}`, function ($modal) {
                        if ($modal.attr("data-reload") === "true") refrescarMapaFaseOne(category);
                    });
                }else{
                    const formData = new FormData();
                    formData.append('_token', $("meta[name=csrf-token]").attr("content"));
                    formData.append('id', id);
                    formData.append('torneo_id', {{ $Model->id }});
                    formData.append('torneo_categoria_id', category);
                    actionAjax(`/auth/{{strtolower($ViewName)}}/fase-final/partido/validate/partialView`, formData, "POST", function(data) {
                        if(data.Success){
                            invocarModal(`/auth/{{strtolower($ViewName)}}/fase-final/partido/partialView/${id ? id : 0}/${position}`, function ($modal) {
                                if ($modal.attr("data-reload") === "true"){
                                    /*invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $Model->id }}/${category}`, function(data){
                                        $("#main").addClass("hidden");$("#info").removeClass("hidden").html("").append(data);
                                    });*/
                                    refrescarMapaFaseOne(category);
                                }
                            });
                        }
                        else Toast.fire({icon: 'error', title: data.Message ? data.Message : 'El partido aún no se encuentra disponible porque falta 1 jugador.'});
                    });
                }
            });
        @endif

        function refrescarMapaFaseOne(torneo_category){
            invocarVista(`/auth/{{strtolower($ViewName)}}/fase-final/mapa/partialView/{{ $Model->id }}/${torneo_category}/{{ $landing }}`, function(data){
                $(`#mapaCampeonato${torneo_category}`).html(data);
            });
        }

        @if(!$landing)
            const $tdJugadorInfo = $("td.td-jugador-info");
            $tdJugadorInfo.on("click", function (){
                const $this = $(this);
                invocarModal(`/auth/{{strtolower($ViewName)}}/jugador/partialViewZona/${$this.attr("data-jugador-info")}`, function ($modal){
                    if($modal.attr("data-reload") === "true"){
                        invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $Model->id }}/${$this.attr("data-category")}`, function(data){
                            $("#main").addClass("hidden");$("#info").removeClass("hidden").html("").append(data);
                        });
                    }
                });
            });

            const $btnReporteLocalizacion = $("button.btnReporteLocalizacion");
            $btnReporteLocalizacion.on("click", function (){
                const $this = $(this);
                window.open(`/auth/{{strtolower($ViewName)}}/jugador/reporte/localizacion/{{ $Model->id }}/${$this.attr('data-category')}`, `_blank`);
            });

            const $btnReportePagos = $("button.btnReportePagos");
            $btnReportePagos.on("click", function (){
                const $this = $(this);
                window.open(`/auth/{{strtolower($ViewName)}}/jugador/reporte/pagos/{{ $Model->id }}/${$this.attr('data-category')}`, `_blank`);
            });

            $firstPanel.on("click", "button.btnSubirFondo", function () {
                const $this = $(this);
                invocarModal(`/auth/{{strtolower($ViewName)}}/partialViewBackground/{{ $Model->id }}/${$this.attr('data-category')}`, function ($modal){
                    if($modal.attr("data-reload") === "true"){
                        refrescarMapaFaseOne($this.attr('data-category'));
                    }
                });
            });

            const $phaseFinal = $(".tab-phase-final-content");
            $phaseFinal.on("click", "button.btnSubirFondo", function () {
                const $this = $(this);
                invocarModal(`/auth/{{strtolower($ViewName)}}/partialViewBackground/{{ $Model->id }}/${$this.attr('data-category')}`, function ($modal){
                    if($modal.attr("data-reload") === "true"){
                        refrescarMapaFaseOne($this.attr('data-category'));
                    }
                });
            });

        @endif

        OnSuccess{{$ViewName}} = (data) => onSuccessForm(data, $("form#frm{{$ViewName}}"));
        OnFailure{{$ViewName}} = () => onFailureForm();
    });
</script>
