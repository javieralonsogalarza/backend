<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ranking extends Model
{
    use SoftDeletes;

    protected $fillable = ['comunidad_id', 'torneo_id', 'torneo_categoria_id', 'multiple', 'user_create_id', 'user_update_id'];

    protected $dates = ['deleted_at'];

    public function torneo()
    {
        return $this->belongsTo(Torneo::class);
    }

    public function torneoCategoria()
    {
        return $this->belongsTo(TorneoCategoria::class);
    }

    public function detalles()
    {
        return $this->hasMany(RankingDetalle::class);
    }
}
