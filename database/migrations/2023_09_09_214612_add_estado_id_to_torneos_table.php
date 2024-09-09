<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEstadoIdToTorneosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('torneos', function (Blueprint $table) {
            $table->unsignedTinyInteger('estado_id')->default(\App\Models\App::$ESTADO_PENDIENTE)->after('fecha_final');
            $table->foreign('estado_id')->references('id')->on('estados');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('torneos', function (Blueprint $table) {
            //
        });
    }
}
