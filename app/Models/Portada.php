<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Portada extends Model
{
    use SoftDeletes;
    protected $fillable = ['comunidad_id', 'titulo_uno', 'titulo_dos', 'descripcion', 'imagen_path', 'user_create_id', 'user_update_id'];
    protected $appends = ['titulo', 'parrafo_uno', 'parrafo_dos', 'parrafo_tres', 'descripcion_breve'];
    protected $dates = ['deleted_at'];

    public function getTituloAttribute()
    {
        return $this->titulo_uno. " " .$this->titulo_dos;
    }

    public function getDescripcionBreveAttribute()
    {
        return strlen($this->descripcion) > 100 ? substr($this->descripcion, 0, 100).'...' : $this->descripcion;
    }

    public function getParrafoUnoAttribute()
    {
        return strlen($this->descripcion) > 75 ? substr($this->descripcion, 0, 75) : $this->descripcion;
    }

    public function getParrafoDosAttribute()
    {
        return strlen($this->descripcion) > 75 ? substr($this->descripcion, 75, 75) : substr($this->descripcion, 75, -1);
    }

    public function getParrafoTresAttribute()
    {
        return strlen($this->descripcion) > 149 ? substr($this->descripcion, 149, -1) : "";
    }
}
