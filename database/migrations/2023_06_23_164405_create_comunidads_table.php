<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComunidadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comunidads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('imagen_path', 150)->nullable();
            $table->string('nombre', 150);
            $table->string('slug', 150);
            $table->boolean('principal')->default(false);
            $table->string('color_navegacion', 15);
            $table->string('color_primario', 15);
            $table->string('color_secundario', 15);
            $table->string('color_alternativo', 15);
            $table->string('titulo_fuente', 50);
            $table->string('parrafo_fuente', 50);
            $table->string('telefono', 15)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('facebook', 150)->nullable();
            $table->string('twitter', 150)->nullable();
            $table->string('instagram', 150)->nullable();
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
        Schema::dropIfExists('comunidads');
    }
}
