<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRankingDetallesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ranking_detalles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ranking_id');
            $table->foreign('ranking_id')->references('id')->on('rankings');
            $table->unsignedBigInteger('jugador_simple_id');
            $table->foreign('jugador_simple_id')->references('id')->on('jugadors');
            $table->unsignedBigInteger('jugador_dupla_id')->nullable();
            $table->foreign('jugador_dupla_id')->references('id')->on('jugadors');
            $table->smallInteger('puntos')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ranking_detalles');
    }
}
