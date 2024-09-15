<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJugadorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jugadors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('comunidad_id');
            $table->foreign('comunidad_id')->references('id')->on('comunidads');
            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->foreign('categoria_id')->references('id')->on('categorias');
            $table->string('imagen_path', 150)->nullable();
            $table->string('nombres', 250);
            $table->string('apellidos', 250);
            $table->unsignedTinyInteger('tipo_documento_id')->nullable();
            $table->foreign('tipo_documento_id')->references('id')->on('tipo_documentos');
            $table->string('nro_documento', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('password', 150)->nullable();
            $table->boolean('isAccount')->default(false);
            $table->boolean('isFirstSession')->default(true);
            $table->tinyInteger('edad')->nullable();
            $table->char('sexo', 1);
            $table->string('telefono', 15)->nullable();
            $table->string('celular', 15)->nullable();
            $table->decimal('altura', 3, 2)->nullable();
            $table->decimal('peso', 5, 2)->nullable();
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
        Schema::dropIfExists('jugadors');
    }
}
