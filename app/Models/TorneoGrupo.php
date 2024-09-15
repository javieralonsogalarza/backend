<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TorneoGrupo extends Model
{
    use SoftDeletes;

    protected $fillable = ['torneo_id', 'torneo_categoria_id', 'jugador_simple_id', 'jugador_dupla_id', 'grupo_id', 'nombre_grupo', 'user_create_id', 'user_update_id'];

    protected $dates = ['deleted_at'];

    public function torneo()
    {
        return $this->belongsTo(Torneo::class);
    }

    public function torneoCategoria()
    {
        return $this->belongsTo(TorneoCategoria::class);
    }

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }

    public function jugadorSimple()
    {
        return $this->belongsTo(Jugador::class);
    }

    public function jugadorDupla()
    {
        return $this->belongsTo(Jugador::class);
    }

    public function torneoJugador()
    {
        return $this->hasMany(TorneoJugador::class);
    }

}
