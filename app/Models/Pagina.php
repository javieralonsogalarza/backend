<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pagina extends Model
{
    use SoftDeletes;
    protected $fillable = ['comunidad_id', 'titulo', 'descripcion', 'imagen_path', 'user_create_id', 'user_update_id'];
    protected $appends = ['descripcion_breve'];
    protected $dates = ['deleted_at'];

    public function getDescripcionBreveAttribute()
    {
        return strlen($this->descripcion) > 100 ? substr($this->descripcion, 0, 100).'...' : $this->descripcion;
    }
    public static function firstSeccion()
    {
        return Pagina::where('id', 1)->first();
    }
    public static function secondSeccion()
    {
        return Pagina::where('id', 2)->first();
    }
    public static function threeSeccion()
    {
        return Pagina::where('id', 3)->first();
    }
}
