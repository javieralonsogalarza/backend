<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jugador extends Authenticatable
{
    use SoftDeletes;

    protected $guard = 'players';

    protected $fillable = ['comunidad_id', 'categoria_id', 'imagen_path', 'nombres', 'apellidos', 'tipo_documento_id', 'nro_documento',
    'email', 'password', 'isAccount', 'isFirstSession', 'edad', 'sexo', 'telefono', 'celular', 'altura', 'peso', 'user_create_id', 'user_update_id'];

    protected $appends = ['nombre_completo', 'sexo_completo'];

    protected $dates = ['deleted_at'];

    public function getCreatedAtAttribute($value)
    {
        return now()->parse($value)->timezone(config('app.timezone'))->format('d M Y, H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return now()->parse($value)->timezone(config('app.timezone'))->diffForHumans();
    }

    public function getNombreCompletoAttribute()
    {
        return $this->nombres." ".$this->apellidos;
    }

    public function getSexoCompletoAttribute()
    {
        return $this->sexo != null ? ($this->sexo == "F" ? "Femenino" : "Masculino") : "Ninguno";
    }

    public function comunidad()
    {
        return $this->belongsTo(Comunidad::class);
    }

    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

}
