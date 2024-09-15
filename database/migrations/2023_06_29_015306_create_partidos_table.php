<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partidos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('comunidad_id');
            $table->foreign('comunidad_id')->references('id')->on('comunidads');
            $table->unsignedBigInteger('torneo_id');
            $table->foreign('torneo_id')->references('id')->on('torneos');
            $table->unsignedBigInteger('torneo_categoria_id');
            $table->foreign('torneo_categoria_id')->references('id')->on('torneo_categorias');
            $table->unsignedBigInteger('grupo_id')->nullable();
            $table->foreign('grupo_id')->references('id')->on('grupos');
            $table->boolean('multiple')->default(false);
            $table->unsignedBigInteger('jugador_local_uno_id')->nullable();
            $table->foreign('jugador_local_uno_id')->references('id')->on('jugadors');
            $table->unsignedBigInteger('jugador_local_dos_id')->nullable();
            $table->foreign('jugador_local_dos_id')->references('id')->on('jugadors');
            $table->tinyInteger('jugador_local_set')->nullable();
            $table->tinyInteger('jugador_local_juego')->nullable();
            $table->unsignedBigInteger('jugador_rival_uno_id')->nullable();
            $table->foreign('jugador_rival_uno_id')->references('id')->on('jugadors');
            $table->unsignedBigInteger('jugador_rival_dos_id')->nullable();
            $table->foreign('jugador_rival_dos_id')->references('id')->on('jugadors');
            $table->tinyInteger('jugador_rival_set')->nullable();
            $table->tinyInteger('jugador_rival_juego')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_final')->nullable();
            $table->time('hora_inicio')->nullable();
            $table->time('hora_final')->nullable();
            $table->tinyInteger('bloque')->nullable();
            $table->tinyInteger('position')->nullable();
            $table->string('bracket', 10)->nullable();
            $table->tinyInteger('fase')->nullable();
            $table->boolean('buy')->default(false);
            $table->boolean('buy_all')->default(false);
            $table->string('resultado', 50)->nullable();
            $table->unsignedTinyInteger('estado_id');
            $table->foreign('estado_id')->references('id')->on('estados');
            $table->actionusers();
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
        Schema::dropIfExists('partidos');
    }
}
