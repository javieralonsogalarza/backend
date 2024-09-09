<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTorneoJugadorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('torneo_jugadors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('torneo_id');
            $table->foreign('torneo_id')->references('id')->on('torneos');
            $table->unsignedBigInteger('torneo_categoria_id');
            $table->foreign('torneo_categoria_id')->references('id')->on('torneo_categorias');
            $table->unsignedBigInteger('jugador_simple_id');
            $table->foreign('jugador_simple_id')->references('id')->on('jugadors');
            $table->unsignedBigInteger('jugador_dupla_id')->nullable();
            $table->foreign('jugador_dupla_id')->references('id')->on('jugadors');
            $table->boolean('after')->default(false);
            $table->unsignedBigInteger('zona_id')->nullable();
            $table->foreign('zona_id')->references('id')->on('zonas');
            $table->boolean('pago')->default(false);
            $table->decimal('monto', 10,2)->nullable();
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
        Schema::dropIfExists('torneo_jugadors');
    }
}
