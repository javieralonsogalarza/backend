<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Formato extends Model
{
    use SoftDeletes;
    protected $fillable = ['nombre', 'user_create_id', 'user_update_id'];
    protected $dates = ['deleted_at'];
}
