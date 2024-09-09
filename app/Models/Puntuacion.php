<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Puntuacion extends Model
{
    use SoftDeletes;
    protected $fillable = ['comunidad_id', 'nombre', 'puntos'];
    protected $dates = ['deleted_at'];
}
