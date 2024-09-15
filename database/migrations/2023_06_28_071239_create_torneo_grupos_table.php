<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTorneoGruposTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('torneo_grupos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('torneo_id');
            $table->foreign('torneo_id')->references('id')->on('torneos');
            $table->unsignedBigInteger('torneo_categoria_id');
            $table->foreign('torneo_categoria_id')->references('id')->on('torneo_categorias');
            $table->unsignedBigInteger('jugador_simple_id');
            $table->foreign('jugador_simple_id')->references('id')->on('jugadors');
            $table->unsignedBigInteger('jugador_dupla_id')->nullable();
            $table->foreign('jugador_dupla_id')->references('id')->on('jugadors');
            $table->unsignedBigInteger('grupo_id');
            $table->foreign('grupo_id')->references('id')->on('grupos');
            $table->string('nombre_grupo', 50);
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
        Schema::dropIfExists('torneo_grupos');
    }
}
