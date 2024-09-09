<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartidoDetallesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partido_detalles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('partido_id');
            $table->foreign('partido_id')->references('id')->on('partidos');
            $table->string('nombre', 150);
            $table->unsignedBigInteger('jugador_ganador_id');
            $table->foreign('jugador_ganador_id')->references('id')->on('jugadors');
            $table->string('jugador_ganador_score', 50);
            $table->string('jugador_oponente_score', 50);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partido_detalles');
    }
}
