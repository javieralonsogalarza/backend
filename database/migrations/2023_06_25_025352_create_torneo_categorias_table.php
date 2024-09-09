<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTorneoCategoriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('torneo_categorias', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('torneo_id');
            $table->foreign('torneo_id')->references('id')->on('torneos');
            $table->string('imagen_path', 150)->nullable();
            $table->string('color_rotulos', 15)->nullable();
            $table->string('color_participantes', 15)->nullable();
            $table->unsignedBigInteger('categoria_simple_id');
            $table->foreign('categoria_simple_id')->references('id')->on('categorias');
            $table->unsignedBigInteger('categoria_dupla_id')->nullable();
            $table->foreign('categoria_dupla_id')->references('id')->on('categorias');
            $table->tinyInteger('orden')->default(1);
            $table->tinyInteger('sector');
            $table->boolean('multiple')->default(false);
            $table->boolean('aleatorio')->default(false);
            $table->boolean('manual')->default(false);
            $table->boolean('first_final')->default(false);
            $table->tinyInteger('clasificados')->default(2);
            $table->tinyInteger('clasificados_terceros')->default(0);
            $table->boolean('solo_ranking')->default(false);
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
        Schema::dropIfExists('torneo_categorias');
    }
}
