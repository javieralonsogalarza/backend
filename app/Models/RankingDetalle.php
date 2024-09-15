<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RankingDetalle extends Model
{
    protected $fillable = ['ranking_id', 'jugador_simple_id', 'jugador_dupla_id', 'puntos'];

    public function ranking()
    {
        return $this->belongsTo(Ranking::class);
    }

    public function jugadorSimple()
    {
        return $this->belongsTo(Jugador::class);
    }

    public function jugadorDupla()
    {
        return $this->belongsTo(Jugador::class);
    }
}
