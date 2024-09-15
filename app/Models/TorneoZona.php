<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TorneoZona extends Model
{
    protected $fillable = ['torneo_id', 'zona_id'];

    public $timestamps = false;

    public function zona()
    {
        return $this->belongsTo(Zona::class);
    }
}
