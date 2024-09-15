<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Galeria extends Model
{
    use SoftDeletes;
    protected $fillable = ['comunidad_id', 'nombre', 'imagen_path', 'user_create_id', 'user_update_id'];
    protected $dates = ['deleted_at'];
}
