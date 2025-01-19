<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTorneoJugadorZonaTable extends Migration
{
    public function up()
    {
        Schema::create('torneo_jugador_zona', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('torneo_jugador_id');
            $table->unsignedBigInteger('zona_id');
            $table->timestamps();

            $table->foreign('torneo_jugador_id')->references('id')->on('torneo_jugadors')->onDelete('cascade');
            $table->foreign('zona_id')->references('id')->on('zonas')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('torneo_jugador_zona');
    }
}