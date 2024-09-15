<div class="card">
    <div class="card-body">
        <div class="p-5">
            @if($Jugador != null)
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3>{{ $Jugador->nombre_completo }}</h3>
                        <ul class="" style="list-style: none;padding: 0">
                            <li><strong>Tipo Documento:</strong> {{ $Jugador->tipoDocumento != null ? $Jugador->tipoDocumento->nombre : "-" }}</li>
                            <li><strong>N° Documento:</strong> {{ $Jugador->nro_documento }}</li>
                            <li><strong>Celular:</strong> {{ $Jugador->celular != null && $Jugador->celular != "" ? $Jugador->celular : "-" }}</li>
                            <li><strong>Edad:</strong> {{ $Jugador->edad != null && $Jugador->edad != "" ? $Jugador->edad." años" : "-"}}</li>
                            <li><strong>Altura:</strong> {{ $Jugador->altura != null && $Jugador->altura != "" ? $Jugador->altura."m" : "-"}}</li>
                            <li><strong>Peso:</strong> {{ $Jugador->peso != null && $Jugador->peso != "" ? $Jugador->peso."kg" : "-"}}</li>
                        </ul>
                    </div>
                    <div class="col-md-4 text-right">
                        <img src="{{ ($Jugador != null && $Jugador->imagen_path != null && $Jugador->imagen_path != "") ? ('/img/'.$Jugador->imagen_path) : "/upload/image/default.png" }}" style="width: 200px;height: 170px;object-fit: cover" alt="{{ $Jugador->nombre_completo }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mt-4">
                        <h5>Historial Torneos</h5>
                    </div>
                    @if($HistorialTorneos != null && count($HistorialTorneos) > 0)
                        <div class="col-md-12">
                            <table class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th class="text-center">Torneo</th>
                                    <th class="text-center">Periodo</th>
                                    <th class="text-center">Categoría</th>
                                    <th class="text-center">Fase</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($HistorialTorneos as $q)
                                    <tr style="cursor: pointer" data-id="{{ $q['id'] }}" data-player="{{ $Jugador != null ? $Jugador->id : 0 }}" data-category="{{ $q['TorneoCategoria']->id }}" >
                                        <td class="text-center">{{ $q['Torneo'] }}</td>
                                        <td class="text-center">{{ $q['Periodo'] }}</td>
                                        <td class="text-center">{{ $q['Categoria'] }}</td>
                                        <td class="text-center">{{ $q['Fase'] }}</td>
                                        <td class="text-center btn-view {{ $q['Estado'] == "En transcurso" ? "bg-warning" : ($q['Estado'] == "Cancelado" ? "bg-dander" : ($q['Estado'] == "Finalizado" ? "bg-success" : "")) }}">
                                            {{ $q['Estado'] }}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="col-md-12 text-center">
                            <p class="m-0">Este jugador aún no presenta historial de torneos jugados.</p>
                        </div>
                    @endif
                </div>
                <div class="row">
                    <div class="col-md-12 mt-4">
                        <h5>Categorías y Rankings</h5>
                    </div>
                    @if($categoriasYRankings != null && count($categoriasYRankings) > 0)
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                    <tr>
                                        <th class="text-center">Categoría</th>
                                        <th class="text-center">Ranking</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($categoriasYRankings as $categoria)
                                        <tr>
                                            <td class="text-center">{{ $categoria['categoria_name'] }}</td>
                                            <td class="text-center">{{ $categoria['countRepeat'] }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="col-md-12 text-center">
                            <p class="m-0">Este jugador aún no presenta categorías y rankings.</p>
                        </div>
                    @endif
                </div>
            @else
                <div class="row">
                    <div class="col-md-12 text-center">
                        <p class="m-0">No existe información disponible para este jugador.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

