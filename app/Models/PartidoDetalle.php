<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartidoDetalle extends Model
{
    use SoftDeletes;
    protected $fillable = ['partido_id', 'nombre', 'jugador_ganador_id', 'jugador_ganador_score', 'jugador_oponente_score'];
    protected $dates = ['deleted_at'];
}
