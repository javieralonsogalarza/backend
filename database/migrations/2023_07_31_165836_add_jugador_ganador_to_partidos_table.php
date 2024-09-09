<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJugadorGanadorToPartidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partidos', function (Blueprint $table) {
            $table->unsignedBigInteger('jugador_ganador_uno_id')->nullable()->after('jugador_rival_juego');
            $table->foreign('jugador_ganador_uno_id')->references('id')->on('jugadors');

            $table->unsignedBigInteger('jugador_ganador_dos_id')->nullable()->after('jugador_ganador_uno_id');
            $table->foreign('jugador_ganador_dos_id')->references('id')->on('jugadors');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('partidos', function (Blueprint $table) {
            //
        });
    }
}
