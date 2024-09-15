<div class="card">
    <div class="card-body">
        <div class="p-2">

            @if($RankingsResultYear != null)
                <div class="row">
                    <div class="col-sm-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        @foreach($Anios as $q)
                                            <th class="text-center">{{ $q }}</th>
                                        @endforeach
                                        <th class="text-center">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($Categorias as $q)
                                    <tr>
                                        <td></td>
                                        @if($filterCategoria == null)
                                            <td class="text-center text-bold text-lg">{{ $q->nombre }}</td>
                                        @else
                                            <td class="text-center"></td>
                                        @endif
                                        @foreach($Anios as $q2)
                                            <td class="text-center"></td>
                                        @endforeach
                                        <td class="text-center"></td>
                                    </tr>
                                    @foreach($RankingsResultYear as $q2)
                                        @if($q->id == $q2->categoria_id)
                                                <?php $countSingle = 0; $countRepeat = 1; $pointBefore = 0; $next = false; ?>
                                            @foreach(\App\Models\App::multiPropertySort(collect($q2->jugadores), [['column' => 'puntos', 'order' => 'desc']]) as $key => $q3)
                                                @if($q3['puntos'] > 0)
                                                        <?php $countSingle += 1; $pointBefore = $q3['puntos']; ?>
                                                    <tr>
                                                        <td class="text-center"> {{ $countRepeat = $next ? $countRepeat : $countSingle }} </td>
                                                        <td>{{ $q3['nombre'] }}</td>
                                                        @foreach($Anios as $q4)
                                                            @if(collect($q3['anios'])->where('anio', $q4)->first() != null)
                                                                @if(collect($q3['anios'])->where('anio', $q4)->first()->puntos > 0)
                                                                    <td class="text-center">
                                                                        {{ collect($q3['anios'])->where('anio', $q4)->first()->puntos  }}
                                                                    </td>
                                                                @else
                                                                    <td></td>
                                                                @endif
                                                            @else
                                                                <td></td>
                                                            @endif
                                                        @endforeach

                                                        <td class="text-center">{{ $q3['puntos'] }}</td>
                                                            <?php
                                                            if(count(collect($q2->jugadores)->where('puntos', '>', '0')) > ($key+1)) {
                                                                if($q3['puntos'] != \App\Models\App::multiPropertySort(collect($q2->jugadores), [['column' => 'puntos', 'order' => 'desc']])[$key+1]['puntos']) {
                                                                    $countRepeat += 1; $next = false;
                                                                }else{
                                                                    $next = true;
                                                                }
                                                            }
                                                            ?>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @endif
                                    @endforeach
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="row">
                    <div class="col-sm-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th></th>
                                    @foreach($Torneos as $q)
                                        <th class="text-center">{{ $q->nombre }}</th>
                                    @endforeach
                                    <th class="text-center">Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($Categorias as $q)
                                    <tr>
                                        <td></td>
                                        @if($filterCategoria == null)
                                            <td class="text-center text-bold text-lg">{{ $q->nombre }}</td>
                                        @else
                                            <td class="text-center"></td>
                                        @endif
                                        @foreach($Torneos as $q2)
                                            <td class="text-center"></td>
                                        @endforeach
                                        <td class="text-center"></td>
                                    </tr>
                                    @foreach($Rankings as $q2)
                                        @if($q->id == $q2->categoria_id)
                                                <?php $countSingle = 0; $countRepeat = 1; $pointBefore = 0; $next = false; ?>
                                            @foreach(\App\Models\App::multiPropertySort(collect($q2->jugadores), [['column' => 'puntos', 'order' => 'desc']]) as $key => $q3)
                                                @if($q3['puntos'] > 0)
                                                        <?php $countSingle += 1; $pointBefore = $q3['puntos']; ?>
                                                    <tr>
                                                        <td class="text-center"> {{ $countRepeat = $next ? $countRepeat : $countSingle }} </td>
                                                        <td>{{ $q3['nombre'] }}</td>
                                                        @foreach($Torneos as $q4)
                                                            @if($Landing)
                                                                @if(collect($q3['torneos'])->where('id', $q4->id)->first() != null && count(collect($q3['torneos'])->where('id', $q4->id)->first()->categorias) > 0)
                                                                    @if(collect($q3['torneos'])->where('id', $q4->id)->first()->categorias[0]->puntos > 0)
                                                                        <td class="text-center">
                                                                            {{ collect($q3['torneos'])->where('id', $q4->id)->first()->categorias[0]->puntos  }}
                                                                        </td>
                                                                    @else
                                                                        <td></td>
                                                                    @endif
                                                                @else
                                                                    <td></td>
                                                                @endif
                                                            @else
                                                                @if(collect($q3['torneos'])->where('id', $q4->id)->first() != null)
                                                                    @if(collect($q3['torneos'])->where('id', $q4->id)->first()->puntos > 0)
                                                                        <td class="text-center">
                                                                            {{ collect($q3['torneos'])->where('id', $q4->id)->first()->puntos }}
                                                                        </td>
                                                                    @else
                                                                        <td></td>
                                                                    @endif
                                                                @else
                                                                    <td></td>
                                                                @endif
                                                            @endif
                                                        @endforeach

                                                        <td class="text-center">{{ $q3['puntos'] }}</td>
                                                            <?php
                                                            if(count(collect($q2->jugadores)->where('puntos', '>', '0')) > ($key+1)) {
                                                                if($q3['puntos'] != \App\Models\App::multiPropertySort(collect($q2->jugadores), [['column' => 'puntos', 'order' => 'desc']])[$key+1]['puntos']) {
                                                                    $countRepeat += 1; $next = false;
                                                                }else{
                                                                    $next = true;
                                                                }
                                                            }
                                                            ?>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @endif
                                    @endforeach
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>

