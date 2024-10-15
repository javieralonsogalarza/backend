<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddTypeToPuntuacionesTable extends Migration
{
    public function up()
    {
        Schema::table('puntuacions', function (Blueprint $table) {
            $table->string('type')->after('puntos');
        });
        DB::table('puntuacions')->update(['type' => '0']);

    }

    public function down()
    {
        Schema::table('puntuacions', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}