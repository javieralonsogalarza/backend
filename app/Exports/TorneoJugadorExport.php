<?php

namespace App\Exports;

use App\Models\TorneoCategoria;
use App\Models\TorneoJugador;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class TorneoJugadorExport implements FromView
{
    protected $toneo, $torneo_categoria, $tipo;
    public function __construct($toneo, $torneo_categoria, $tipo)
    {
        $this->toneo = $toneo;
        $this->torneo_categoria = $torneo_categoria;
        $this->tipo = $tipo;
    }

    public function view(): View
    {
        $torneoCategoria = TorneoCategoria::where('torneo_id', $this->toneo)->where('id', $this->torneo_categoria)->first();

        $list = TorneoJugador::with('zona')->with('jugadorSimple')->with('jugadorDupla')
        ->whereHas('jugadorSimple')
        ->where('torneo_id', $this->toneo)->where('torneo_categoria_id', $this->torneo_categoria)
        ->get();

        return view('auth.torneo.ajax.jugador.export.reporte', ['Tipo' => $this->tipo, 'TorneoCategoria' => $torneoCategoria, 'list' => $list]);
    }
}
