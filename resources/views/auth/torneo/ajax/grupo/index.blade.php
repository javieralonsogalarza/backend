@inject('App', 'App\Models\App')

@if($landing)
    <style type="text/css">
        select, input, textarea{ background-color: transparent !important; }
        select::-ms-expand {display: none;}
        select{ -webkit-appearance: none;-moz-appearance: none;text-indent: 1px;text-overflow: '';}
        a.nav-link{ color: black !important; }
        a.nav-link.active{ background-color: var(--color-cabecera) !important; border-color: var(--color-cabecera) !important; color: white !important;}
        .bg-json-generado { background-color: #b2d235 !important; }
    </style>
@else
    <style type="text/css">
        .bg-json-generado { background-color: #b2d235 !important; }
    </style>
@endif

<div class="card">
    <div class="card-body">

        <div class="row report-not-view">
            <div class="col-md-12 text-center pt-3 pb-3">
                <h3 class="html-view" style="color: black !important;"> {{ $Model->nombre }}</h3>
                <h3 class="report-view hidden"> {{ $Model->nombre }}<span id="textCategoria"></span></h3>
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
                                <a class="nav-link {{ ($TorneoCategoriaId != $q->id || $Fase == null ) ? "active" : "" }}" id="custom-tabs-fase-one-tab-{{$q->id}}" data-toggle="pill" href="#custom-tabs-fase-one-{{$q->id}}" role="tab" aria-controls="custom-tabs-fase-one-{{$q->id}}" aria-selected="true">Primera Fase</a>
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
                                                        <li><button type="button" onclick="abrirModalDistribucion({{ $q->id }})" class="btn btn-primary mr-2"><i class="fa fa-map-marker"></i> Distribución por Zonas</button></li>
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
                                            @elseif(count($Model->partidos->where('torneo_categoria_id', $q->id)->where('estado_id', $App::$ESTADO_FINALIZADO)) >= 0)
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
                                                            <th align="center" class="align-middle text-center"></th>

                                                        @endif
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($Model->torneoJugadors()->where('torneo_id', $Model->id)->whereHas('jugadorSimple')->where('torneo_categoria_id', $q->id)->get() as $q3)
                                                        @if($initial >= ($i == 0 ? 0 : ($i*8))  && $initial <= ($i == 0 ? 7 : (($i+1)*8)-1))
                                                            @if($q3->jugadorSimple != null)
                                                            @php
                                                                $grupo = $q3->torneoGrupos()->where('torneo_categoria_id', $q->id)->first();
                                                            @endphp
                                                                <tr>
                                                                    <td align="center" class="align-middle text-left td-jugador-info" data-category="{{ $q->id }}" style="cursor: pointer !important;" data-jugador-info="{{ $q3->id }}">
                                                                        {{ $q->multiple ? ($q3->jugadorSimple->nombre_completo." + ".($q3->jugadorDupla != null ? $q3->jugadorDupla->nombre_completo : "-")) : $q3->jugadorSimple->nombre_completo_temporal }}

                                                                    </td>
                                                                   <td width="40">
                                                                   @if($grupo)
                        <i class="fa fa-eye ml-2 player-link" data-target="#custom-tabs-grupo-{{ $q->id }}-{{ $grupo->grupo_id }}" title="Ver Grupo" style="cursor: pointer;"></i>
                    @endif
                                                                    </td>
                                                                    @if(!$landing)
                                                                        <td width="40" align="center" data-jugador-info="{{ $q3->id }}">
                                                                            @if($q3->pago)
                                                                                <i class="fa fa-coins" title="Si pagó" style="color: #dc8306; cursor: pointer"></i>
                                                                            @endif
                                                                        </td>
                                                                        <td width="40" align="center" data-jugador-info="{{ $q3->id }}">
                                                                        @if($q3->zonas != null && count($q3->zonas) > 0)
                                                                            <i class="fa fa-map-marker" title="{{ implode(', ', $q3->zonas->pluck('nombre')->toArray()) }}" style="color: #b61616;cursor: pointer"></i>
                                                                        @endif
                                                                        </td>
                                                                        <td width="40" align="center" class="align-middle text-center">
                                                                            @if(count($Model->partidos()->where('torneo_categoria_id', $q->id)->where('buy', false)->where('estado_id', $App::$ESTADO_FINALIZADO)->where(function($o) use($q3){
                                                                            $o->where('jugador_local_uno_id', $q3->jugador_simple_id)->orWhere('jugador_local_dos_id', $q3->jugador_simple_id)->orWhere('jugador_rival_uno_id', $q3->jugador_simple_id)->orWhere('jugador_rival_dos_id', $q3->jugador_simple_id); })
                                                                            ->where(function($o) use($q3){ $o->where('jugador_local_uno_id', $q3->jugador_dupla_id)->orWhere('jugador_local_dos_id', $q3->jugador_dupla_id)->orWhere('jugador_rival_uno_id', $q3->jugador_dupla_id)->orWhere('jugador_rival_dos_id', $q3->jugador_dupla_id); })->get()) <= 0)

                                                                                @if(count($Model->partidos->where('torneo_categoria_id', $q->id)->where('buy', false)->where('estado_id', $App::$ESTADO_PENDIENTE)) > 0 && !$q3->after)
                                                                                    <button id="btnChangePlayer_{{$q->id}}_{{$q3->jugador_simple_id}}_{{$q3->jugador_dupla_id}}" type="button" class="btn btn-default btn-change-player btn-xs" title="Remplazar Jugador" data-id="{{ $q3->id }}" data-info="{{ $q->multiple ? ($q3->jugadorSimple->nombre_completo." + ".($q3->jugadorDupla != null ? $q3->jugadorDupla->nombre_completo : "-")) : $q3->jugadorSimple->nombre_completo_temporal }}" data-category="{{ $q->id }}" data-category-name="{{ $q->categoriaSimple != null ? $q->categoriaSimple->nombre : "-" }}">
                                                                                        <img src="{{ asset('/images/icon_exchange.png') }}" width="20" alt="Remplazar Jugador">
                                                                                    </button>
                                                                                @else
                                                                                    <button type="button" class="btn btn-danger btn-delete-player btn-xs" title="Eliminar Jugador" data-id="{{ $q3->id }}" data-info="{{ $q->multiple ? ($q3->jugadorSimple->nombre_completo." + ".($q3->jugadorDupla != null ? $q3->jugadorDupla->nombre_completo : "-")) : $q3->jugadorSimple->nombre_completo_temporal }}" data-category="{{ $q->id  }}" data-category-name="{{ $q->categoriaSimple != null ? $q->categoriaSimple->nombre : "-" }}"><i class="fa fa-trash"></i></button>
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
                                                 @if($Model->formato->nombre=='Eliminación Directa Flexible')
    <li class="mr-1">
        <button type="button" class="btn btn-primary btn-generate-keys-final" data-reload="0" data-id="{{ $q->id }}">
            <i class="fa fa-key"></i> Keys de Eliminación
        </button>
    </li>
@else
                                                <li class="mr-1"><button type="button" class="btn btn-primary btn-generate-keys-zonas" data-id="{{ $q->id }}"><i class="fa fa-key"></i> Grupos por Zonas</button></li>
                                                <li class="mr-1"><button type="button" class="btn btn-primary btn-generate-keys-random" data-id="{{ $q->id }}"><i class="fa fa-key"></i> Grupos Aleatorias</button></li>
                                                <li class="mr-1"><button type="button" class="btn btn-primary btn-generate-keys" data-id="{{ $q->id }}"><i class="fa fa-key"></i> Grupos con Siembra</button></li>
                                                <li class="mr-1"><button type="button" class="btn btn-primary btn-manual-keys" data-id="{{ $q->id }}"><i class="fa fa-key"></i> Grupos Manuales</button></li>
                                                @endif
                                            </ul>
                                        </div>
                                    @elseif(count($Model->partidos->where('torneo_categoria_id', $q->id)->where('estado_id', $App::$ESTADO_FINALIZADO)) <= 0)
                 <div class="row mt-3">
                                       

                                            <ul class="w-100 d-flex align-content-center justify-content-end list-unstyled p-0">
                                            @if($Model->formato->nombre!='Eliminación Directa Flexible')
                                              <div class="col-md-12 d-flex flex-wrap align-items-center">
                                        <div>
                                        <h5 class="mb-0 mr-3">Listado de Grupos</h5>
                                        </div>
                                        <div class="search-bar flex-grow-1 ">
                                        <select class="form-control search-grupos-dropdown" style="width: 300px;"   >
    <option value="">Buscar jugador...</option>
    @php
        $jugadores = $Model->torneoJugadors()
            ->where('torneo_id', $Model->id)
            ->whereHas('jugadorSimple')
            ->where('torneo_categoria_id', $q->id)
            ->get()
            ->sortBy(function($jugador) {
                return $jugador->jugadorSimple->nombre_completo;
            });
    @endphp
    @foreach($jugadores as $q3)
        <option value="{{ $q3->jugadorSimple->nombre_completo }}">{{ $q3->jugadorSimple->nombre_completo }}</option>
    @endforeach
</select>

                                        
                                    </div>
                                            
                                            @endif
                                            @if($Model->formato->nombre!='Eliminación Directa Flexible')

                                                    <li class="mr-2"><button type="button" class="btn btn-primary btn-add-groups" data-id="{{ $q->id }}"><i class="fa fa-users"></i> Agregar grupos</button></li>
                                                    @endif  
                                                @if($q->first_final)
                                                    <li class="mr-1"><button type="button" class="btn btn-primary btn-generate-keys-final" data-id="{{ $q->id }}" data-reload="1"><i class="fa fa-sync"></i> Volver a generar llaves</button></li>
                                                @endif
                                                <li class="mr-1"><button type="button" class="btn btn-danger btn-delete-keys" data-id="{{ $q->id }}"><i class="fa fa-key"></i> Eliminar keys generados</button></li>
                                                @if($Model->formato->nombre!='Eliminación Directa Flexible')
                                                <li class="mr-1"><button type="button" class="btn btn-primary btn-generate-json-grupo" data-id="{{ $q->id }}" data-category="{{ $q->id }}><i class="fa fa-key"></i> Generar Json</button></li>
                                                @endif
                                                 <button type="button" class="btn btn-primary btn-generate-json-grupo-vs" data-category="{{ $q->id }}">Generar Json Vs</button>
                                            </ul>
                                        </div>
                                    @endif
                                @endif

                                @if(count($Model->partidos->where('torneo_categoria_id', $q->id)->where('estado_id', $App::$ESTADO_FINALIZADO)) > 0 && $Model->torneoGrupos()->where('torneo_categoria_id', $q->id)->count() > 0)
                                 
                                    <div class="col-md-12 d-flex flex-wrap align-items-center">
                                        <div>
                                        <h5 class="mb-0 mr-3">Listado de Grupos</h5>
                                        </div>
                                        <div class="search-bar flex-grow-1 ">
                                        <select  class="form-control search-grupos-dropdown" style="width: 300px;"   >
    <option value="">Buscar jugador...</option>
    @php
        $jugadores = $Model->torneoJugadors()
            ->where('torneo_id', $Model->id)
            ->whereHas('jugadorSimple')
            ->where('torneo_categoria_id', $q->id)
            ->get()
            ->sortBy(function($jugador) {
                return $jugador->jugadorSimple->nombre_completo;
            });
    @endphp
    @foreach($jugadores as $q3)
        <option value="{{ $q3->jugadorSimple->nombre_completo }}">{{ $q3->jugadorSimple->nombre_completo }}</option>
    @endforeach
</select>

                                        
                                    </div>
                                   
                                    <ul class="w-100 d-flex align-content-center justify-content-end list-unstyled p-0">
                                    @if(count($Model->torneoJugadors->where('torneo_categoria_id', $q->id)->where('after', true)) )
                                                    <li class="mr-2"><button type="button" class="btn btn-primary btn-add-groups" data-id="{{ $q->id }}"><i class="fa fa-users"></i> Agregar grupos</button></li>
                                         @endif
                                            <button type="button" class="btn btn-primary btn-generate-json-grupo" data-category="{{ $q->id }}">Generar Json</button>
                                          <button style="margin-left: 10px" type="button" class="btn btn-primary btn-generate-json-grupo-vs" data-category="{{ $q->id }}">Generar Json Vs</button>
                                          
                                                <li class="ml-2"><button type="button" class="btn btn-danger btn-delete-keys" data-id="{{ $q->id }}"><i class="fa fa-key"></i> Eliminar keys generados</button></li>
                                        </ul>
                                    </div>
                                @endif
                               
    <div class="col-md-12">
        <ul class="nav nav-tabs navs-groups" id="custom-tabs-one-tab-{{ $q->id }}" role="tablist">
            @foreach($Model->torneoGrupos()->where('torneo_categoria_id', $q->id)->select(['nombre_grupo', 'grupo_id'])->groupBy(['nombre_grupo', 'grupo_id'])->orderBy(DB::raw('LENGTH(nombre_grupo)'))->orderBy('nombre_grupo')->get() as $key4 => $q4)
                @php
                
                   $grupo_id = $q4->id;
                    // Obtener nombres de jugadores para el grupo actual
                    $playerNames = $Model->torneoGrupos()
        ->where('torneo_categoria_id', $q->id)
        ->whereHas('jugadorSimple')
        ->where('grupo_id', $q4->grupo_id)
        ->with('jugadorSimple') // Asegura que la relación esté cargada
        ->get()
        ->map(function($torneoGrupo) {
            return $torneoGrupo->jugadorSimple->nombre_completo; // Reemplaza 'nombres' por 'nombre' si es correcto
        })
        ->toArray();                    $playerNamesString = implode(',', $playerNames);
                @endphp
                <li class="nav-item grupo-item" data-players="{{ strtolower($playerNamesString) }}" data-grupo="{{ $q4->grupo_id }}" data-categoria="{{ $q->id }}" >
                    <a class="nav-link {{ $key4 == 0 ? "active" : "" }}" data-category="{{ $q->id }}" data-id="{{ $q4->grupo_id }}" id="custom-tabs-{{ $q->id }}-{{ $q4->grupo_id }}-grupo-tab" data-toggle="pill" href="#custom-tabs-grupo-{{ $q->id }}-{{ $q4->grupo_id }}" role="tab" aria-controls="custom-tabs-grupo-{{ $q->id }}-{{ $q4->grupo_id }}" aria-selected="{{ $key4 == 0 ? 'true' : 'false' }}">
                        <span>{{ $q4->nombre_grupo }}</span>
                        <input type="hidden" class="form-control input-sm" data-category="{{ $q->id }}" data-id="{{ $q4->grupo_id }}" value="{{ $q4->nombre_grupo }}" readonly>
                    </a>
                </li>
            @endforeach
        </ul>
    </div> 
                               
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="tab-content" id="custom-tabs-one-tabContent-grupo-{{ $q->id }}">
                                            @foreach($Model->torneoGrupos()->where('torneo_categoria_id', $q->id)->select(['nombre_grupo', 'grupo_id'])->groupBy(['nombre_grupo', 'grupo_id'])->orderBy(DB::raw('LENGTH(nombre_grupo)'))->orderBy('nombre_grupo')->get() as $key4 => $q4)
                                                <div class="tab-pane p-2 fade {{ $key4 == 0 ? "show active" : "" }}" id="custom-tabs-grupo-{{ $q->id }}-{{ $q4->grupo_id }}" role="tabpanel" aria-labelledby="custom-tabs-grupo-{{ $q->id }}-{{ $q4->grupo_id }}-tab">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div><h5>Jugadores</h5>
                                                        
                                                        </div>
                                                                                                                <button style="margin-left: 10px" type="button"  class="btn btn-primary btn-delete-group-unique" data-category="{{ $q->id }}" data-group="{{ $q4->grupo_id }}">Eliminar grupo</button>

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
                                                                            <td >{{ $q->multiple ? ($q5->jugadorSimple->nombre_completo." + ".$q5->jugadorDupla->nombre_completo) : $q5->jugadorSimple->nombre_completo_temporal }}
                                                                          
                                                                           
                                                                                @php
    // Obtener el ID del jugador actual
    $playerId = $q5->jugadorSimple->id;

    // Verificar si el jugador tiene algún partido con resultados en la categoría y grupo específicos
    $hasResultados = $Model->partidos
        ->where('torneo_categoria_id', $q->id)
        ->where('grupo_id', $q4->grupo_id)
        ->filter(function ($partido) use ($playerId) {
            return $partido->jugador_local_uno_id === $playerId || $partido->jugador_rival_uno_id === $playerId;
        })
        ->whereNotNull('resultado')
        ->isNotEmpty();
@endphp
                                                                            @if(!$hasResultados) <!-- Asegúrate de tener una condición para verificar si se han creado resultados -->
                                                                          
                                                                            <button type="button" class="btn btn-default btn-xs" title="Remplazar Jugador" >
                                                                            <img class="edit-player" data-player-id="{{ $q5->jugadorSimple->id }}" data-torneo-categoria-id="{{ $q->id }}" data-torneo-id="{{ $Model->id }}" data-toggle="modal" data-target="#editPlayerModal" title="Reemplazar Jugador" style="cursor: pointer;" src="{{ asset('/images/icon_exchange.png') }}" width="20" alt="Remplazar Jugador">
                                                                                    </button>

                                                                            @endif
                                                                            </td>
                                                                 
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
                                                                    <tr class="content-tr-{{ $q6->id }} {{ $q6->estado_id == $App::$ESTADO_FINALIZADO ? "disable" : "enable" }} {{ $q6->reporte_json_generado ? "bg-json-generado" : "" }}" data-id="{{ $q6->id }}" data-category="{{ $q->id }}" data-group="{{ $q4->grupo_id }}">                                                                            
                                                                              <td class="text-center td-jugador-info-h2h" 
                                                                            data-jugador-local-id="{{ $q6->jugador_local_uno_id }}" 
                                                                            data-jugador-rival-id="{{ $q6->jugador_rival_uno_id }}" 
                                                                            data-torneo-categoria-id="{{ $q->id }}" 
                                                                            style="cursor: pointer;">
                                                                            {{ $q->multiple ? $q6->jugadorLocalUno->nombre_completo.' + '.$q6->jugadorLocalDos->nombre_completo : $q6->jugadorLocalUno->nombre_completo_temporal }} 
                                                                            vs 
                                                                            {{ $q->multiple ? $q6->jugadorRivalUno->nombre_completo.' + '.$q6->jugadorRivalDos->nombre_completo : $q6->jugadorRivalUno->nombre_completo_temporal }}
                                                                        </td>  
                                                                            
                                                                            <td><input type="date" value="{{ \Carbon\Carbon::parse($q6->fecha_inicio)->format('Y-m-d') }}" class="form-input" id="fecha_inicio_{{$q6->id}}" name="fecha_inicio_{{$q6->id}}" {{ $q6->estado_id == $App::$ESTADO_FINALIZADO ? "disabled" : "" }}></td>
                                                                            <td><input type="date" value="{{ \Carbon\Carbon::parse($q6->fecha_final)->format('Y-m-d') }}" class="form-input" id="fecha_final_{{$q6->id}}" name="fecha_final_{{$q6->id}}" {{ $q6->estado_id == $App::$ESTADO_FINALIZADO ? "disabled" : "" }}></td>
                                                                            <td width="100"><input value="{{ $q6->resultado }}" type="text" id="resultado_{{$q6->id}}" name="resultado_{{$q6->id}}" class="form-input result-input" {{ $q6->estado_id == $App::$ESTADO_FINALIZADO ? "disabled" : "" }} data-category="{{ $q->id }}" data-target="#custom-tabs-grupo-{{ $q->id }}-{{ $q4->grupo_id }}"  data-local="{{$q6->jugadorLocalUno->id}}" data-rival="{{$q6->jugadorRivalUno->id}}"  data-multiple="{{$q->multiple}}"></td>
                                                                            <td>
                                                                                <select id="jugador_local_id_{{$q6->id}}" name="jugador_local_id_{{$q6->id}}" class="form-input" {{ $q6->estado_id == $App::$ESTADO_FINALIZADO ? "disabled" : "" }}>
                                                                                    <option value="" {{ $q6->estado_id == $App::$ESTADO_PENDIENTE ? "selected" : "" }}> {{ $landing ? "" : "Seleccione" }} </option>
                                                                                    <option value="{{ $q->multiple ? ($q6->jugadorLocalUno->id.'-'.$q6->jugadorLocalDos->id) : $q6->jugadorLocalUno->id }}" {{ $q6->estado_id == $App::$ESTADO_PENDIENTE ? "" : ($q->multiple ? (($q6->jugador_local_uno_id.'-'.$q6->jugador_local_dos_id) == ($q6->jugador_ganador_uno_id.'-'.$q6->jugador_ganador_dos_id) ? "selected" : "") : ($q6->jugador_local_uno_id == $q6->jugador_ganador_uno_id ? "selected" : "")) }}>{{ $q->multiple ? ($q6->jugadorLocalUno->nombre_completo.' + '.$q6->jugadorLocalDos->nombre_completo) : $q6->jugadorLocalUno->nombre_completo_temporal }}</option>
                                                                                    <option value="{{ $q->multiple ? ($q6->jugadorRivalUno->id.'-'.$q6->jugadorRivalDos->id) : $q6->jugadorRivalUno->id }}" {{ $q6->estado_id == $App::$ESTADO_PENDIENTE ? "" : ($q->multiple ? (($q6->jugador_rival_uno_id.'-'.$q6->jugador_rival_dos_id) == ($q6->jugador_ganador_uno_id.'-'.$q6->jugador_ganador_dos_id) ? "selected" : "") : ($q6->jugador_rival_uno_id == $q6->jugador_ganador_uno_id ? "selected" : "")) }}>{{ $q->multiple ? ($q6->jugadorRivalUno->nombre_completo.' + '.$q6->jugadorRivalDos->nombre_completo) : $q6->jugadorRivalUno->nombre_completo_temporal }}</option>
                                                                                </select>
                                                                            </td>
                                                                            <td width="57"><input value="{{ $q6->jugador_local_set }}" id="jugador_local_set_{{$q6->id}}" name="jugador_local_set_{{$q6->id}}" type="text" class="numeric-set set-local form-input" {{ $q6->estado_id == $App::$ESTADO_FINALIZADO ? "disabled" : "" }}></td>
                                                                            <td width="57"><input value="{{ $q6->jugador_local_juego }}" id="jugador_local_juego_{{$q6->id}}" name="jugador_local_juego_{{$q6->id}}" type="text" class="numeric-game game-local form-input" {{ $q6->estado_id == $App::$ESTADO_FINALIZADO ? "disabled" : "" }}></td>
                                                                            <td>
                                                                                <select id="jugador_rival_id_{{$q6->id}}" name="jugador_rival_id_{{$q6->id}}" class="form-input" {{ $q6->estado_id == $App::$ESTADO_FINALIZADO ? "disabled" : "" }}>
                                                                                    <option value="" {{ trim($q6->resultado) === "-" || $q6->estado_id == $App::$ESTADO_PENDIENTE ? "selected" : "" }}>{{ $landing ? "" : "Seleccione" }}</option>
                                                                                    <option value="{{ $q->multiple ? ($q6->jugadorLocalUno->id.'-'.$q6->jugadorLocalDos->id) : $q6->jugadorLocalUno->id }}" {{ trim($q6->resultado) === "-" || $q6->estado_id == $App::$ESTADO_PENDIENTE ? "" : ($q->multiple ? (($q6->jugador_local_uno_id.'-'.$q6->jugador_local_dos_id) != ($q6->jugador_ganador_uno_id.'-'.$q6->jugador_ganador_dos_id) ? "selected" : "") : ($q6->jugador_local_uno_id != $q6->jugador_ganador_uno_id ? "selected" : "")) }}>{{ $q->multiple ? ($q6->jugadorLocalUno->nombre_completo.' + '.$q6->jugadorLocalDos->nombre_completo) : $q6->jugadorLocalUno->nombre_completo_temporal }}</option>
                                                                                    <option value="{{ $q->multiple ? ($q6->jugadorRivalUno->id.'-'.$q6->jugadorRivalDos->id) : $q6->jugadorRivalUno->id }}" {{ trim($q6->resultado) === "-" || $q6->estado_id == $App::$ESTADO_PENDIENTE ? "" : ($q->multiple ? (($q6->jugador_rival_uno_id.'-'.$q6->jugador_rival_dos_id) != ($q6->jugador_ganador_uno_id.'-'.$q6->jugador_ganador_dos_id) ? "selected" : "") : ($q6->jugador_rival_uno_id != $q6->jugador_ganador_uno_id ? "selected" : "")) }}>{{ $q->multiple ? ($q6->jugadorRivalUno->nombre_completo.' + '.$q6->jugadorRivalDos->nombre_completo) : $q6->jugadorRivalUno->nombre_completo_temporal }}</option>
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
                                                                                <button type="button" data-id="{{ $q6->id }}" data-category="{{ $q->id }}" data-multiple="{{ $q->multiple }}" data-group="{{ $q4->grupo_id }}" data-manual="{{ $q->manual }}" data-hasFase="{{ $hasFase }}" data-target="#custom-tabs-grupo-{{ $q->id }}-{{ $q4->grupo_id }}"
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
                                                                 <button class="btn btn-primary btn-copiar-tabla" data-category="{{ $q->id }}" data-id="{{ $q4->grupo_id }}">Copiar Tabla</button>
                                                                <button type="button" data-id="{{ $q4->grupo_id }}" data-name="{{ $q4->nombre_grupo }}" data-category="{{ $q->id }}" class="btn btn-primary btn-export-pdf pull-right"><i class="fa fa-file-pdf"></i> Exportar PDF </button>
                                                                <!--<button type="button" data-id="{{ $q4->grupo_id }}" data-name="{{ $q4->nombre_grupo }}" data-category="{{ $q->id }}" class="btn btn-primary btn-finish-all-plays pull-right"><i class="fa fa-save"></i> Finalizar Partidos </button>-->
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="row mt-2">
                                                            <div class="col-md-12 text-right">
                                                                        <button class="btn btn-primary btn-copiar-tabla" data-category="{{ $q->id }}" data-id="{{ $q4->grupo_id }}">Copiar Tabla</button>


                                                                <button type="button" data-id="{{ $q4->grupo_id }}" data-name="{{ $q4->nombre_grupo }}" data-category="{{ $q->id }}" class="btn btn-primary btn-export-pdf pull-right"><i class="fa fa-file-pdf"></i> Exportar PDF </button>
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
<!-- Modal -->
<div class="modal fade" id="editPlayerModal" role="dialog" tabindex="-1" data-backdrop="static" aria-labelledby="editPlayerModalLabel">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPlayerModalLabel">Reemplazar Jugador</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
            <div class="row">
                        <div class="col-md-12">
                            <p class="text-danger">
                                <strong>Nota:</strong> Esta acción remplazará al jugador seleccionado.
                                <div></div>
                                En todos los partidos y grupos asignados por el jugador que seleccione.
                            </p>
                        </div>
                    </div>
            <form id="editPlayerForm" data-torneo-categoria-id="">
            <div class="form-group">
                        <label for="playerSelect">Jugador de reemplazo</label>
                        <select class="form-control select2" id="playerSelect" name="player_id" style="width: 100%;">
                            <!-- Opciones se cargarán dinámicamente -->
                        </select>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">Reemplazar</button>
                    </div>                </form>
            </div>
        </div>
    </div>
</div>

    <script type="text/javascript" src="{{ asset('auth/adminlte3/plugins/select2/js/select2.full.min.js') }}"></script>

<script src="{{ asset('/plugins/sortable/1.15.0/sortable.min.js') }}"></script>

<script type="text/javascript">
// Function to open the distribution modal
function abrirModalDistribucion(categoriaId) {
    // Asegurar que el modal está cargado antes de mostrarlo
    if ($('#zonasDistribucionModal').length === 0) {
        // Si el modal no existe, cárgalo primero
        $.ajax({
            url: '/auth/torneo/get-distribution-modal',
            type: 'GET',
            async: false, // Importante: hacer esto síncrono para asegurar que se cargue antes de continuar
            success: function(response) {
                $('body').append(response);
                
                // Una vez añadido el modal, mostrarlo y cargar los datos
                $('#zonasDistribucionModal').modal('show');
                cargarDistribucionZonas(categoriaId);
            },
            error: function(xhr, status, error) {
                console.error('Error loading distribution modal:', error);
                alert('Error al cargar el modal de distribución por zonas');
            }
        });
    } else {
        // Si el modal ya existe, simplemente mostrarlo y cargar los datos
        $('#zonasDistribucionModal').modal('show');
        cargarDistribucionZonas(categoriaId);
    }
}

// Make sure cargarDistribucionZonas is properly defined and works correctly
$(document).ready(function() {
    // Precargar el modal al inicio para asegurar que esté disponible cuando se necesite
    if ($('#zonasDistribucionModal').length === 0) {
        $.ajax({
            url: '/auth/torneo/get-distribution-modal',
            type: 'GET',
            success: function(response) {
                $('body').append(response);
            },
            error: function() {
                console.error('Error loading distribution modal');
            }
        });
    }
});
</script>

<script type="text/javascript">
    
    $(function (){
        setTimeout(function (){ $("form").find("input[type=text]").first().focus().select(); }, 500);


        $(document).ready(function () {
            const $this = $(this);
            const $TorneoCategoriaIdData = {{ $TorneoCategoriaId  }};

            const generateKeysData = localStorage.getItem(`generateKeysData_${$TorneoCategoriaIdData}`);

                if (generateKeysData) {
                    const parsedData = JSON.parse(generateKeysData);
                    const id = parsedData.id; // Extraer el id de los datos guardados en localStorage
                    const viewName = parsedData.viewName;
                    const modelId = parsedData.modelId;
                    const tipoGrupo = parsedData.tipoGrupo;
                    const data = parsedData.data; // Extraer el data de los datos guardados en localStorage

                   
                    invocarVista(`/auth/${viewName}/grupo/manual/partialView/${modelId}/${id ? id : 0}/${tipoGrupo}`, function (data) {
                        $("#partialViewManual" + id).html("").append(data);
                    });
                  
                }
                
                      $('.td-jugador-info-h2h').on('click', function(event) {
            event.preventDefault(); // Prevenir la acción por defecto del enlace

            // Obtener los datos del atributo data
            var jugadorLocalId = $(this).data('jugador-local-id');
            var jugadorRivalId = $(this).data('jugador-rival-id');
            var torneoCategoriaId = $(this).data('torneo-categoria-id');

            // Construir la URL con los parámetros
            var url = `/auth/torneo/h2h/${jugadorLocalId}/${jugadorRivalId}/${torneoCategoriaId}/null/null/json`;

            // Redirigir a la URL
            window.open(url, '_blank');
        });
            });

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
        
        
$(".btn-generate-json-grupo-vs").on("click", function (){
    const $this = $(this);
    const $category_id = $this.attr('data-category');
    
    // Filtrar el elemento que coincide con data-category
    const $activeTab = $('.nav-tabs.navs-groups .nav-link.active').filter(function() {
        return $(this).data('category') == $category_id;
    });
    
    // Extraer el grupo del ID de la pestaña
    const $grupo_id = $activeTab.attr('id').split('-')[3];
    
    console.log('Grupo ID:', $grupo_id);
    console.log('Categoria ID:', $category_id);
    
    window.open(`/auth/{{strtolower($ViewName)}}/grupos/vs/export/json?torneo={{ $Model->id }}&categoria=${$category_id}&grupo=${$grupo_id}`);
});
        let $btnChangePlayerLocal, $btnChangePlayerRival;
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
                   
                        let category = $this.closest("tr").attr("data-category");
                        const targetId = $this.attr('data-target');
                        const tab = document.querySelector(targetId);
                    invocarVista(`/auth/{{ strtolower($ViewName) }}/grupo/{{ $Model->id }}/${category}`, function (data) {
                        $("#main").addClass("hidden");
                        $("#info").removeClass("hidden").html("").append(data);
                        if (tab) {
                    // Activar el tab
                    const tabPane = new bootstrap.Tab(tab);
                    tabPane.show();

                    // Activar el enlace del tab
                    const tabLink = document.querySelector(`a[href="${targetId}"]`);
                    console.log(tabLink,'tabLink');
                    if (tabLink) {
                        const tabLinkPane = new bootstrap.Tab(tabLink);
                        tabLinkPane.show();
                    }
                }
                    });
                             
                    }
                });
            }
            else if ($this.val().toLowerCase().includes("ret")) {
                const cleanedValue = $this.val().toLowerCase().replace(/(\(ret\)|ret)/gi, "").trim();
                const sets = cleanedValue.split('/');
                let setsLocalnew = 0; let gamesLocal = 0; let setsRivalew = 0; let gamesRival = 0;
                const valoresPermitidos = ["6-0", "6-1", "6-2", "6-3", "6-4", "7-5", "7-6"];

                    if (!valoresPermitidos.includes(sets[0])) {
                        // Modificar los sets según sea necesario
                        const games = sets[0].split('-').map(Number);
                        if (games.length === 2) {
                            const [left, right] = games;

                            // Buscar el valor permitido que tenga el mismo right
                            const valorPermitido = valoresPermitidos.find(valor => {
                                const [permitidoLeft, permitidoRight] = valor.split('-').map(Number);
                                return permitidoRight === right;
                            });

                            // Si se encuentra un valor permitido, modificar el set
                            if (valorPermitido) {
                                sets[0] = valorPermitido;
                                sets[1] = "6-0"; // Ejemplo de modificación por defecto
                            } else {
                                sets[0] = "6-0"; // Ejemplo de modificación por defecto
                            }
                        }
                    } 

                if(sets.length == 1){
                    sets[1] = "6-0"; // Ejemplo de modificación por defecto
                }
                    

                if(sets.length > 0){

                        // aqui cambie
                    $.each(sets, function (i, v){
                        console.log(v,"v")
                        console.log(i,"i")
                        const games = v.split('-');
                        let $GameLeft = parseInt(games[0].match(/\d+/)[0]);
                        let $GameRight = parseInt(games[1].match(/\d+/)[0]);
                        if(i <= 1){
                            console.log("sets.lengthmmccc",sets.length)
                            if(i == 1 && sets.length != 3){
                            if (games.includes('6') && sets[0] == '6-0') 
                            {
                                console.log("entro",games)
                                $GameLeft = 7
                            }else{
                                $GameLeft = 6
                            }
                              
                            }
                            gamesLocal += $GameLeft;
                            gamesRival += $GameRight;
                        }
                        if($GameLeft > $GameRight) setsLocalnew+=1;
                        else if($GameRight > $GameLeft)  setsRivalew+=1;
                    });
                  

                }
                $this.closest("tr").find("input.set-local").val(setsLocalnew);
                $this.closest("tr").find("input.game-local").val(gamesLocal);
                $this.closest("tr").find("input.set-rival").val(setsRivalew);
                $this.closest("tr").find("input.game-rival").val(gamesRival);
            }
            else{
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
        $btnManualKeys = $(".btn-manual-keys"), $btnDeleteKeys = $(".btn-delete-keys"), $btnGenerateKeysZonas = $(".btn-generate-keys-zonas"),$btnDeleteGroups = $(".btn-delete-group-unique");

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
                                      
                                        const id = $this.attr("data-id");
                                    const viewName = '{{ strtolower($ViewName) }}';
                                    const modelId = '{{ $Model->id }}';
                                    const tipoGrupo = $("#tipo_grupo").val();
                                    console.log(id)
                                    console.log(viewName)
                                    console.log(modelId)
                                    console.log(tipoGrupo)
          
                                    // Asegúrate de que 'data' está definido en el contexto

                                    // Guardar datos en localStorage

                                localStorage.setItem(`generateKeysData_${id}`, JSON.stringify({
                                    id: id,
                                    viewName: viewName,
                                    modelId: modelId,
                                    tipoGrupo: tipoGrupo,
                                    data: data
                                }));


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

                }
            }else Toast.fire({icon: 'error', title: 'No existen jugadores disponibles para generar las llaves'});
        });

        $btnGenerateKeysZonas.on("click", function (){
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
                        formData.append('tipo', 'zonas');
                        formData.append('tipo_grupo_id', $("#tipo_grupo").val());
                        actionAjax(`/auth/{{strtolower($ViewName)}}/grupo/store`, formData, `POST`, function (data){
                            if(data.Success){
                               
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
                                      
                                        const id = $this.attr("data-id");
                                    const viewName = '{{ strtolower($ViewName) }}';
                                    const modelId = '{{ $Model->id }}';
                                    const tipoGrupo = $("#tipo_grupo").val();
                                    console.log(id)
                                    console.log(viewName)
                                    console.log(modelId)
                                    console.log(tipoGrupo)
          
                                    // Asegúrate de que 'data' está definido en el contexto

                                    // Guardar datos en localStorage

                                localStorage.setItem(`generateKeysData_${id}`, JSON.stringify({
                                    id: id,
                                    viewName: viewName,
                                    modelId: modelId,
                                    tipoGrupo: tipoGrupo,
                                    data: data
                                }));


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

        $btnDeleteGroups.on("click", function (){
            const $this = $(this);
            const id = $this.attr("data-category");
            const group_id = $this.attr("data-group");
            const formData = new FormData();
            formData.append('_token', $("meta[name=csrf-token]").attr("content"));
            formData.append('torneo_id', {{ $Model->id }});
            formData.append('torneo_categoria_id', id);
            formData.append('group_id', group_id);
            confirmAjax(`/auth/{{strtolower($ViewName)}}/grupo/unique/delete`, formData, `POST`, `¿Está seguro de eliminar el grupo?`, null, function (data){
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
$btnFinishPlay.on("click", function () {
    const $this = $(this);
    const id = $this.attr("data-id");
    const formData = new FormData();
    const category = $(event.target).attr("data-category");
    formData.append('_token', $("meta[name=csrf-token]").attr("content"));
    formData.append('torneo_id', {{ $Model->id }});
    formData.append('torneo_categoria_id', $this.attr("data-category"));
    formData.append('partido_id', id);
    formData.append('fecha_inicio', $("#fecha_inicio_" + id).val());
    formData.append('fecha_final', $("#fecha_final_" + id).val());
    formData.append('resultado', $("#resultado_" + id).val());
    formData.append('jugador_local_id', $("#jugador_local_id_" + id).val());
    formData.append('jugador_local_set', $("#jugador_local_set_" + id).val());
    formData.append('jugador_local_juego', $("#jugador_local_juego_" + id).val());
    formData.append('jugador_rival_id', $("#jugador_rival_id_" + id).val());
    formData.append('jugador_rival_set', $("#jugador_rival_set_" + id).val());
    formData.append('jugador_rival_juego', $("#jugador_rival_juego_" + id).val());
    formData.append('fase_inicial', '1');
    const targetId = $this.attr('data-target');
    const tab = document.querySelector(targetId);

    const handleResponse = function(data) {
        if (data.Success) {
            // Muestra mensaje de éxito
            Toast.fire({ 
                icon: data.Message ? 'warning' : 'success', 
                title: data.Message ? data.Message : 'Proceso realizado Correctamente' 
            });
            
            // Actualiza la UI para este partido específico
            $this.addClass("hidden");
            $this.closest("td").find("button.btn-edit-play").closest('div.btn-group').removeClass("hidden");
            $this.closest("tr").find("input, select").prop("disabled", true);
            $this.closest("tr").addClass("disable").removeClass("enable");

            // Obtiene IDs de jugadores
            const $idLocal = parseInt($this.attr("data-multiple")) === 0 ? 
                [$this.attr("data-local")] : 
                [$this.attr("data-local"), $this.attr("data-local-multiple")];
                
            const $idRival = parseInt($this.attr("data-multiple")) === 0 ? 
                [$this.attr("data-rival")] : 
                [$this.attr("data-rival"), $this.attr("data-rival-multiple")];

            // Oculta los botones de cambio de jugador para los jugadores involucrados en este partido
            $("button#btnChangePlayer_" + $this.attr("data-category") + "_" + $idLocal[0] + "_" + ($idLocal.length > 1 ? $idLocal[1] : "")).hide();
            $("button#btnChangePlayer_" + $this.attr("data-category") + "_" + $idRival[0] + "_" + ($idRival.length > 1 ? $idRival[1] : "")).hide();

            // Oculta los botones de reemplazo en toda la tabla para estos jugadores
            $(`.edit-player[data-player-id="${$idLocal[0]}"]`).hide();
            if ($idLocal.length > 1 && $idLocal[1]) {
                $(`.edit-player[data-player-id="${$idLocal[1]}"]`).hide();
            }
            
            $(`.edit-player[data-player-id="${$idRival[0]}"]`).hide();
            if ($idRival.length > 1 && $idRival[1]) {
                $(`.edit-player[data-player-id="${$idRival[1]}"]`).hide();
            }

            if (["-"].includes($("#resultado_" + id).val())) {
                $this.closest("tr").find("select").val("");
            }
            
            // Actualiza tabla de posiciones con AJAX
            refrescarTablaPosiciones($this.attr("data-category"), $this.attr("data-group"));
            
            // Actualizar ranking en un solo request combinado con la operación principal
            // en lugar de hacer otro request separado
            if (data.rankingUpdated) {
                console.log('Ranking actualizado correctamente como parte del request principal');
            }
        } else {
            if (data.Errors) {
                const $arregloErros = [];
                $.each(data.Errors, function (i, v) { $arregloErros.push(v); });
                if ($arregloErros.length > 0) Toast.fire({ icon: 'error', title: $arregloErros[0] });
                else Toast.fire({ icon: 'error', title: data.Message != null ? data.Message : "Algo salió mal, por favor verifique los campos ingresados." });
            } else {
                Toast.fire({ icon: 'error', title: data.Message != null ? data.Message : "Algo salió mal, por favor verifique los campos ingresados." });
            }
        }
    };
           
    const torneoId = {{ $Model->id }};
    const torneoCategoriaId = $this.attr("data-category");
    
    // Incluir flag para actualizar ranking en el mismo request
    formData.append('update_ranking', '1');
    
    actionAjax(`/auth/torneo/jugador/list-json-validate?torneo_id=${torneoId}&torneo_categoria_id=${torneoCategoriaId}&landing=false&completo=true`, null, "GET", function (data) {
        const jugador_local_id = $("#jugador_local_id_" + id).val();
        const jugador_rival_id = $("#jugador_rival_id_" + id).val();

        // Verificar si el ID del jugador en data.data coincide con el ID del jugador local o del rival
        const jugadorEnData = data.data.some(jugador => jugador.jugador_simple_id == jugador_local_id || jugador.jugador_simple_id == jugador_rival_id);

        if (jugadorEnData) {
            if ($this.attr("data-manual") === '0' && $this.attr("data-hasFase")) {
                formData.append('tipo', 'manual');
                formData.append('reload', 0); // Cambiado a 0 para no forzar recarga
                formData.append('grupo_id', $this.attr("data-group"));
                actionAjax(`/auth/{{ strtolower($ViewName) }}/partido/store`, formData, 'POST', handleResponse);
            } else {
                actionAjax(`/auth/{{ strtolower($ViewName) }}/partido/store`, formData, 'POST', handleResponse);
            }
        } else {
            actionAjax(`/auth/{{ strtolower($ViewName) }}/partido/store`, formData, 'POST', handleResponse);
        }
    });
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

             // Obtener el valor de $Fase desde Blade
             const fase =  {{ $Fase ?  $Fase : 0 }};

        // Verificar si la fase es igual a 2 y ejecutar la función automáticamente
        if (fase === 2) {
            $btnPhaseFinal.each(function() {
                const $this = $(this);
                    const $toneo_category = $this.attr("data-id");
                    const $partialView = $("#partialViewFinal"+$toneo_category);
                    invocarVista(`/auth/{{strtolower($ViewName)}}/fase-final-final/{{ $Model->id }}/${$toneo_category ? $toneo_category : 0}/{{ $landing }}`, function(data){
                        $partialView.html("").append(data);
                    });
            });
        }
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
            if(parseInt($first_final) === 1){ 
            invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/{{ $Model->id }}/${$toneo_category}/0/{{ $landing }}`, function(data){
                        $("#main").addClass("hidden");
                        $("#info").removeClass("hidden").html("").append(data);
                    });
                
                refrescarMapaFaseOne($toneo_category);
            }
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
        
         $firstPanel.on("click", ".btn-download-cup-cuartos", function (){
            const $this = $(this);
            window.open(`/auth/{{strtolower($ViewName)}}/export/mapa/figuras/json?type=full&torneo={{ $Model->id  }}&categoria=${$this.attr("data-id")}`);
        });

        $firstPanel.on("click", ".btn-export-pdf-cup-left", function (){
            const $this = $(this);
            $("#textCategoria").text(', Categoría "'+$this.attr("data-category")+'"');
            $(".has-map-bg").removeClass("is-half-right").addClass("is-half-left");
            $(".grid.grid-mapa-content").removeClass("is-half-right").addClass("is-half-left");
            window.print();
        });

      $firstPanel.on("click", ".btn-copiar-tabla", function () {
    const categoryId = this.getAttribute("data-category");
    const groupId = this.getAttribute("data-id");
    const tableId = `partialViewTablaGrupo${categoryId}${groupId}`;
    const tableElement = document.getElementById(tableId);

    if (tableElement) {
        html2canvas(tableElement).then(canvas => {
            const link = document.createElement("a");
            link.href = canvas.toDataURL("image/png");
            link.download = `tabla_${categoryId}_${groupId}.png`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            console.log("Imagen PNG descargada exitosamente.");
        }).catch(err => {
            console.error("Error al renderizar el elemento con html2canvas: ", err);
        });
    } else {
        console.error("No se encontró el elemento HTML especificado.");
    }
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
        
       function refrescarMapaTorneo(torneo_category){
            invocarVista(`/auth/torneo/fase-final/mapa/partialView/{{$Model->id}}/${torneo_category}/{{ $landing }}`, function(data){
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
        

        $(document).ready(function() {
            $(document).on("change", "select[id^='jugador_local_id_']", function () {
                const selectedValue = $(this).val();
                const rivalSelect = $(this).closest('tr').find("select[id^='jugador_rival_id_']");
                rivalSelect.find("option").each(function () {
                    if ($(this).val() !== selectedValue) {
                        $(this).prop("selected", true);
                    } else {
                        $(this).prop("selected", false);
                    }
                });
            });
            $(document).on("change", "select[id^='jugador_rival_id_']", function () {
                const selectedValue = $(this).val();
                const localSelect = $(this).closest('tr').find("select[id^='jugador_local_id_']");
                localSelect.find("option").each(function () {
                    if ($(this).val() !== selectedValue) {
                        $(this).prop("selected", true);
                    } else {
                        $(this).prop("selected", false);
                    }
                });
            });
        });

        OnSuccess{{$ViewName}} = (data) => onSuccessForm(data, $("form#frm{{$ViewName}}"));
        OnFailure{{$ViewName}} = () => onFailureForm();
    });


        $(document).ready(function() {
    const editIcons = document.querySelectorAll('.edit-player');
    
    editIcons.forEach(icon => {
        icon.addEventListener('click', function() {
            const playerId = this.getAttribute('data-player-id');
            const torneoCategoriaId = this.getAttribute('data-torneo-categoria-id');
            const torneoId = this.getAttribute('data-torneo-id');
            const form = document.getElementById('editPlayerForm');

            // Set the player ID on the form
            document.getElementById('editPlayerForm').setAttribute('data-player-id', playerId);
            form.setAttribute('data-torneo-categoria-id', torneoCategoriaId);
            form.setAttribute('data-player-id', playerId);
            form.setAttribute('data-torneo-id', torneoId);
            // Initialize Select2
            $('#playerSelect').select2({
                dropdownParent: $('#editPlayerModal'),
                ajax: {
                    url: '/auth/torneo/jugador/available/list-json-all',
                    dataType: 'json',
                    type: 'GET',
                    delay: 250,
                    data: function(params) {
                        return {
                            select2: true,
                            q: params.term,
                            torneo_categoria_id: torneoCategoriaId,
                            torneo_id: torneoId
                        };
                    },
                    processResults: function(data) {
                        // Ordenar los resultados alfabéticamente por el texto
                        const sortedData = data.data.sort((a, b) => {
                            return a.text.localeCompare(b.text, 'es', {
                                sensitivity: 'base',
                                ignorePunctuation: true
                            });
                        });
                        
                        return {
                            results: sortedData
                        };
                    },
                    sorter: function(data) {
                    // Ordenar también cuando se filtran los resultados localmente
                    return data.sort((a, b) => {
                        return a.text.localeCompare(b.text, 'es', {
                            sensitivity: 'base',
                            ignorePunctuation: true
                        });
                    });
                },
                    cache: true
                },
                placeholder: 'Seleccione un jugador',
                allowClear: true
            });

            // Clear the select when opening the modal
            $('#playerSelect').val(null).trigger('change');
            
            // Focus on the select2 search field when modal opens
            $('#editPlayerModal').on('shown.bs.modal', function() {
                $('#playerSelect').select2('focus');
            });
        });
    });

    // Form submission handler
    $('#editPlayerForm').on('submit', function(event) {
        event.preventDefault();
        const torneoCategoriaId =  this.getAttribute('data-torneo-categoria-id');
        const playerId = this.getAttribute('data-player-id');
        const newPlayerId = $('#playerSelect').val();
        const torneoId = this.getAttribute('data-torneo-id');
        if (!newPlayerId) {
            alert('Por favor seleccione un jugador');
            return;
        }

        // Show loading state
        const submitButton = $(this).find('button[type="submit"]');
        const originalButtonText = submitButton.html();
        submitButton.html('<i class="fas fa-spinner fa-spin"></i> Actualizando...').prop('disabled', true);

        // Make the AJAX call to update
        $.ajax({
            url: '/auth/torneo/update-jugador-simple',  // Ajusta esta URL según tu ruta
            type: 'POST',
            data: {
                torneo_categoria_id: torneoCategoriaId,
                jugador_simple_id: playerId,
                nuevo_jugador_simple_id: newPlayerId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // Show success message
                Swal.fire({
                    title: '¡Éxito!',
                    text: response.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Reload the page or update the UI as needed
                  // Actualizar solo la categoría específica
                  invocarVista(`/auth/torneo/grupo/${torneoId}/${torneoCategoriaId}`, function(data) {  
                    $("#main").addClass("hidden");$("#info").removeClass("hidden").html("").append(data);

                });
                });
            },
            error: function(xhr) {
                // Show error message
                let errorMessage = 'Ocurrió un error al actualizar';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    text: errorMessage,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });
            },
            complete: function() {
                // Restore button state
                submitButton.html(originalButtonText).prop('disabled', false);
                $('#editPlayerModal').modal('hide');
            }
        });
    });

    $(document).on('click', '.player-link', function(e) {
    e.preventDefault();
    const targetId = $(this).data('target');
    
    // Extract category and group from the target ID
    const parts = targetId.split('-');
    const category = parts[3];
    const group = parts[4];
    
    // First activate the tab
    $(`a[href="${targetId}"]`).tab('show');
    
    // Use the extracted category and group variables
    invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/tabla/partialView/{{ $Model->id }}/${category}/${group}/{{ $landing }}`, function(data){
        $(`#partialViewTablaGrupo${category}${group}`).html("").append(data);
    });
});
});


    $(document).ready(function(){

               $('.search-grupos-dropdown').select2({
            placeholder: "Buscar jugador...",
            allowClear: true
        });
    $('#search-grupos, #search-gruposv2, .search-grupos-dropdown').on('change', function(){
        var searchValue = $(this).val().toLowerCase().trim();
            console.log(searchValue);
        
        // Iterate over each group item
        $('.grupo-item').each(function(){
            var players = $(this).data('players').toString().toLowerCase();
            var categoria = $(this).data('categoria').toString().toLowerCase();
            var grupo = $(this).data('grupo').toString().toLowerCase();
            
    
            
            // Show all items if search is empty
            if(searchValue === "") {
                $(this).show();
            } else {
                // Check if players string contains the search value
                if(players.indexOf(searchValue) > -1) {
                    $(this).show();
                    // Load the partial view if it's not already loaded

                    invocarVista(`/auth/{{strtolower($ViewName)}}/grupo/tabla/partialView/{{ $Model->id }}/${categoria}/${grupo}/{{ $landing }}`, function(data){
                $(`#partialViewTablaGrupo${categoria}${grupo}`).html("").append(data);
                 });
                        } else {
                    $(this).hide();
                }
            }
        });
        
        // If active tab is hidden, switch to first visible tab
        var activeTab = $('.nav-link.active');
        if(activeTab.parent('.grupo-item').is(':hidden')) {
            var firstVisibleTab = $('.grupo-item:visible .nav-link').first();
            if(firstVisibleTab.length) {
                firstVisibleTab.tab('show');
            }
        }
    });
});

</script>



<!-- Incluir html2canvas -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<link rel="stylesheet" href="{{ asset('auth/adminlte3/plugins/select2/css/select2.min.css') }}">
<!-- Tus otros CSS -->

<!-- Al final del documento -->
<script src="{{ asset('auth/adminlte3/plugins/select2/js/select2.js') }}"></script>