<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Zona extends Model
{
    use SoftDeletes;
    protected $fillable = ['comunidad_id', 'nombre'];
    protected $dates = ['deleted_at'];

}
