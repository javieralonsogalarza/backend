<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaginasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paginas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('comunidad_id');
            $table->foreign('comunidad_id')->references('id')->on('comunidads');
            $table->string('titulo', 150);
            $table->longText('descripcion');
            $table->string('imagen_path', 150)->nullable();
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
        Schema::dropIfExists('paginas');
    }
}
