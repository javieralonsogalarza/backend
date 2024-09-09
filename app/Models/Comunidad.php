<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comunidad extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'imagen_path', 'nombre', 'slug', 'principal', 'color_navegacion', 'color_primario', 'color_secundario', 'color_alternativo', 'titulo_fuente',
        'parrafo_fuente', 'telefono', 'email', 'facebook', 'twitter', 'instagram', 'user_create_id', 'user_update_id'];

    protected $appends = ['url'];

    protected $dates = ['deleted_at'];

    /*public function getUrlAttribute()
    {
        return (empty($_SERVER['HTTPS']) ? 'http' : 'https')."://".$_SERVER['HTTP_HOST']."/".$this->slug;
    }*/

    public function getUrlAttribute()
    {
        return (empty($_SERVER['HTTPS']) ? 'http' : 'https')."://".$_SERVER['HTTP_HOST'];
    }

    public function getCreatedAtAttribute($value)
    {
        return now()->parse($value)->timezone(config('app.timezone'))->format('d M Y, H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return now()->parse($value)->timezone(config('app.timezone'))->diffForHumans();
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
