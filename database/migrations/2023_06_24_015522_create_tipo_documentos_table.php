<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTipoDocumentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tipo_documentos', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('nombre', 50);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('tipo_documentos')->insert(
            array(
                ['nombre' => 'DNI'],
                ['nombre' => 'PASAPORTE'],
                ['nombre' => 'CARNET DE EXTRANJERIA']
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tipo_documentos');
    }
}
