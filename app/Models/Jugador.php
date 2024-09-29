<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jugador extends Authenticatable
{
    use SoftDeletes;

    protected $guard = 'players';

    protected $fillable = ['comunidad_id', 'categoria_id', 'imagen_path', 'nombres', 'apellidos', 'tipo_documento_id', 'nro_documento',
    'email', 'password', 'isAccount', 'isFirstSession', 'edad', 'sexo', 'telefono', 'celular', 'altura', 'peso', 'user_create_id', 'user_update_id','nombre_completo_temporal'
    , 'mano_habil', 'fecha_nacimiento', 'marca_raqueta'];

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
    // Mutador para nombre_completo
    public function setNombreCompletoAttribute($value)
    {
        $names = explode(' ', $value, 2);
        $this->attributes['nombres'] = $names[0];
        $this->attributes['apellidos'] = isset($names[1]) ? $names[1] : '';
    }

   // M�todo adicional para construir el nombre completo con datos adicionales sin modificar la base de datos
   public function setNombreCompletoConDatosAdicionales($datosAdicionales = [])
   {
       $this->attributes['nombre_completo_temporal'] = $this->nombres . " " . $this->apellidos;

       foreach ($datosAdicionales as $dato) {
           $this->attributes['nombre_completo_temporal'] .= " ({$dato})";
       }
   }

   // Accesor para nombre_completo_temporal
   public function getNombreCompletoTemporalAttribute()
   {
       return $this->attributes['nombre_completo_temporal'] ?? $this->nombre_completo;
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
