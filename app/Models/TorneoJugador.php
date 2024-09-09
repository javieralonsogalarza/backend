<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TorneoJugador extends Model
{
    use SoftDeletes;

    protected $fillable = ['torneo_id', 'torneo_categoria_id', 'jugador_simple_id', 'jugador_dupla_id', 'after', 'zona_id', 'pago', 'monto', 'user_create_id', 'user_update_id'];

    protected $dates = ['deleted_at'];

    public function zona()
    {
        return $this->belongsTo(Zona::class);
    }

    public function torneo()
    {
        return $this->belongsTo(Torneo::class);
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
