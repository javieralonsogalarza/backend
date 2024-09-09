
<?php $styleTh = "border: 2px solid #000000;font-weight: bold;background-color: #000000;color: #FFFFFF;text-align: center;"; ?>

<table>
    <thead>
    <tr>
        <th style="{{ $styleTh }}" width="300px">{{ $TorneoCategoria->multiple ? "Jugadores" : "Jugador" }}</th>
        <th style="{{ $styleTh }}" width="100px">{{ $Tipo == 'localizacion' ? 'Localización' : '¿Realizó pago?' }}</th>
        @if($Tipo != 'localizacion')
            <th style="{{ $styleTh }}" width="100px">Monto</th>
        @endif
    </tr>
    </thead>
    @if($list != null && count($list) > 0)
        <tbody>
        @foreach($list as $q)
            <tr>
                <td>{{ $TorneoCategoria->multiple ? ($q->jugadorSimple->nombre_completo.' + '. $q->jugadorDupla->nombre_completo) : $q->jugadorSimple->nombre_completo }}</td>
                <td style="text-align: center">{{ $Tipo == 'localizacion' ? ($q->zona != null ? $q->zona->nombre : "No Presenta") : ($q->pago ? "Si" : "No") }}</td>
                @if($Tipo != 'localizacion')
                    <th style="text-align: center" width="100px">{{ 'S/.'. number_format($q->monto, 2) }}</th>
                @endif
            </tr>
        @endforeach
        </tbody>
    @endif
</table>
