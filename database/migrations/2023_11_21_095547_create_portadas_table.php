<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePortadasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('portadas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('comunidad_id');
            $table->foreign('comunidad_id')->references('id')->on('comunidads');
            $table->string('titulo_uno', 150);
            $table->string('titulo_dos', 150)->nullable();
            $table->string('descripcion', 500);
            $table->string('imagen_path', 150);
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
        Schema::dropIfExists('portadas');
    }
}
