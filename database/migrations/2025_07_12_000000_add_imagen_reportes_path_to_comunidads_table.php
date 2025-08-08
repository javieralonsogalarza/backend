<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImagenReportesPathToComunidadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('comunidads', function (Blueprint $table) {
            $table->string('imagen_reportes_path', 150)->nullable()->after('imagen_path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('comunidads', function (Blueprint $table) {
            $table->dropColumn('imagen_reportes_path');
        });
    }
}
