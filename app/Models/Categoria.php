<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categoria extends Model
{
    use SoftDeletes;
    protected $fillable = ['comunidad_id', 'nombre', 'dupla', 'orden', 'visible', 'user_create_id', 'user_update_id'];
    protected $dates = ['deleted_at'];
}
