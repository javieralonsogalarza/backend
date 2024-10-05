<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJugadoresCuartosToYourTableNameTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      
        Schema::table('torneo_categorias', function (Blueprint $table) {
            $table->string('clasificados_cuartos')->nullable(); // Agrega la columna jugadores_cuartos

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
            $table->dropColumn('jugadores_cuartos'); // Elimina la columna jugadores_cuartos
        });
    }
}
