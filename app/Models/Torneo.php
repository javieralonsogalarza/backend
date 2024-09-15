<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Torneo extends Model
{
    use SoftDeletes;

    protected $fillable = ['comunidad_id', 'imagen_path', 'nombre', 'valor_set', 'formato_id', 'rankeado', 'fecha_inicio', 'fecha_final', 'estado_id', 'user_create_id', 'user_update_id'];

    protected $appends = ['fecha_inicio_texto', 'fecha_final_texto', 'estado_texto', 'imagen'];

    protected $dates = ['deleted_at'];

    public function getFechaInicioTextoAttribute()
    {
        return now()->parse($this->fecha_inicio)->timezone(config('app.timezone'))->format('d M Y');
    }

    public function getFechaFinalTextoAttribute()
    {
        return now()->parse($this->fecha_final)->timezone(config('app.timezone'))->format('d M Y');
    }

    public function getEstadoTextoAttribute()
    {
        return Carbon::parse($this->fecha_inicio) < Carbon::now() ? ($this->estado_id == App::$ESTADO_PENDIENTE ? "En transcurso" : ($this->estado_id == App::$ESTADO_CANCELADO ? "Cancelado" : ($this->estado_id == App::$ESTADO_FINALIZADO ? "Finalizado" : ""))) : "Próximamente";
    }

    public function getImagenAttribute()
    {
        return $this->imagen_path != null ? str_replace('public', '/storage', $this->imagen_path) : null;
    }

    public function formato()
    {
        return $this->belongsTo(Formato::class);
    }

    public function torneoCategorias()
    {
        return $this->hasMany(TorneoCategoria::class);
    }

    public function torneoJugadors()
    {
        return $this->hasMany(TorneoJugador::class);
    }

    public function torneoGrupos()
    {
        return $this->hasMany(TorneoGrupo::class);
    }

    public function partidos()
    {
        return $this->hasMany(Partido::class);
    }

    public function zonas()
    {
        return $this->hasMany(TorneoZona::class);
    }
}
