<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEstadoIdToTorneoCategoriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('torneo_categorias', function (Blueprint $table) {
            $table->unsignedTinyInteger('estado_id')->default(\App\Models\App::$ESTADO_PENDIENTE)->after('clasificados_terceros');
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
        Schema::table('torneo_categorias', function (Blueprint $table) {
            //
        });
    }
}
