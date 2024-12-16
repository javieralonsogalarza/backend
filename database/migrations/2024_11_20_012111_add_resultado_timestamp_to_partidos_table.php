<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResultadoTimestampToPartidosTable extends Migration
{
    public function up()
    {
        Schema::table('partidos', function (Blueprint $table) {
            $table->timestamp('resultado_timestamp')->nullable();
        });
    }

    public function down()
    {
        Schema::table('partidos', function (Blueprint $table) {
            $table->dropColumn('resultado_timestamp');
        });
    }
}