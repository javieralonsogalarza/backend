<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTorneosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('torneos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('comunidad_id')->nullable();
            $table->foreign('comunidad_id')->references('id')->on('comunidads');
            $table->string('imagen_path', 150)->nullable();
            $table->string('nombre', 150);
            $table->integer('valor_set');
            $table->unsignedBigInteger('formato_id');
            $table->foreign('formato_id')->references('id')->on('formatos');
            $table->boolean('rankeado')->default(true);
            $table->date('fecha_inicio');
            $table->date('fecha_final');
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
        Schema::dropIfExists('torneos');
    }
}
