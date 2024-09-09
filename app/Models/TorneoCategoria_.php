<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TorneoCategoria extends Model
{
    use SoftDeletes;

    protected $fillable = ['torneo_id', 'categoria_simple_id', 'categoria_dupla_id', 'imagen_path', 'color_rotulos', 'color_participantes', 'sector', 'orden', 'multiple', 'aleatorio', 'manual', 'first_final',  'clasificados', 'clasificados_terceros', 'estado_id', 'user_create_id', 'user_update_id'];

    protected $appends = ['imagen'];

    protected $dates = ['deleted_at'];

    public function getImagenAttribute()
    {
        return $this->imagen_path != null ? str_replace('public', '/storage', $this->imagen_path) : null;
    }

    public function torneo()
    {
        return $this->belongsTo(Torneo::class)->withTrashed();
    }

    public function categoriaSimple()
    {
        return $this->belongsTo(Categoria::class)->withTrashed();
    }

    public function categoriaDupla()
    {
        return $this->belongsTo(Categoria::class)->withTrashed();
    }

}
