<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partido extends Model
{
    use SoftDeletes;

    protected $fillable = ['comunidad_id', 'torneo_id', 'torneo_categoria_id', 'grupo_id', 'multiple',
    'jugador_local_uno_id', 'jugador_local_dos_id', 'jugador_local_set', 'jugador_local_juego',
    'jugador_rival_uno_id', 'jugador_rival_dos_id', 'jugador_rival_set', 'jugador_rival_juego',
    'jugador_ganador_uno_id', 'jugador_ganador_dos_id',
    'fecha_inicio', 'fecha_final', 'hora_inicio', 'hora_final', 'bloque', 'position', 'bracket', 'fase',
    'buy', 'buy_all', 'resultado', 'perdio', 'estado_id', 'user_create_id', 'user_update_id'];

    protected $appends = ['permitir_edicion'];

    protected $dates = ['deleted_at'];

    public function torneo()
    {
        return $this->belongsTo(Torneo::class);
    }

    public function torneoCategoria()
    {
        return $this->belongsTo(TorneoCategoria::class);
    }

    public function jugadorLocalUno()
    {
        return $this->belongsTo(Jugador::class)->withTrashed();
    }

    public function jugadorLocalDos()
    {
        return $this->belongsTo(Jugador::class)->withTrashed();
    }

    public function jugadorRivalUno()
    {
        return $this->belongsTo(Jugador::class)->withTrashed();
    }

    public function jugadorRivalDos()
    {
        return $this->belongsTo(Jugador::class)->withTrashed();
    }

    public function jugadorGanadorUno()
    {
        return $this->belongsTo(Jugador::class)->withTrashed();
    }

    public function jugadorGanadorDos()
    {
        return $this->belongsTo(Jugador::class)->withTrashed();
    }

    public function getPermitirEdicionAttribute()
    {
        return $this->fase != null && $this->fase > 1 && $this->estado_id == App::$ESTADO_FINALIZADO ?
            (count(Partido::where('comunidad_id', $this->comunidad_id)->where('torneo_categoria_id', $this->torneo_categoria_id)
            ->where('torneo_id', $this->torneo_id)->where('fase', ($this->fase/2))->where('estado_id', App::$ESTADO_FINALIZADO)
            ->where(function ($q) {
                $q->where('jugador_local_uno_id', $this->jugador_local_uno_id)->orWhere('jugador_local_dos_id', $this->jugador_local_uno_id)
                ->orWhere('jugador_rival_uno_id', $this->jugador_local_uno_id)->orWhere('jugador_rival_uno_id', $this->jugador_local_uno_id)
                ->orWhere('jugador_local_uno_id', $this->jugador_rival_uno_id)->orWhere('jugador_local_dos_id', $this->jugador_rival_uno_id)
                ->orWhere('jugador_rival_uno_id', $this->jugador_rival_uno_id)->orWhere('jugador_rival_uno_id', $this->jugador_rival_uno_id);
            })->get()) > 0) : false;
    }
}
