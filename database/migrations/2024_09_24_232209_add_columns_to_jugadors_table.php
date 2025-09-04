<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToJugadorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
 
        Schema::table('jugadors', function (Blueprint $table) {
            $table->string('mano_habil')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('marca_raqueta')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jugadors', function (Blueprint $table) {
            $table->dropColumn('mano_habil');
            $table->dropColumn('fecha_nacimiento');
            $table->dropColumn('marca_raqueta');

        });
    }
}
